<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../helpers/Mailer.php';

class RegistroDAO
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }

    public function existeUsername($username)
    {
        $sql = "SELECT id_usuario FROM sb_usuario WHERE username = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $existe = $res->num_rows > 0;
        $stmt->close();
        return $existe;
    }

    public function existeCorreo($correo)
    {
        $sql = "SELECT id_usuario FROM sb_correo_usuario WHERE correo = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $res = $stmt->get_result();
        $existe = $res->num_rows > 0;
        $stmt->close();
        return $existe;
    }

    public function obtenerUsuarioPorCorreo($correo)
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.username, u.estado, c.correo
                FROM sb_correo_usuario c
                INNER JOIN sb_usuario u ON c.id_usuario = u.id_usuario
                WHERE c.correo = ?
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $stmt->close();
        return $data ?: null;
    }

    public function generarCodigoVerificacion($id_usuario, $tipo = 'registro', $minutosValidez = 15)
    {
        $sqlInv = "UPDATE sb_codigo_verificacion SET utilizado = 1 WHERE id_usuario = ? AND tipo = ? AND utilizado = 0";
        $stmtInv = $this->conexion->prepare($sqlInv);
        $stmtInv->bind_param("is", $id_usuario, $tipo);
        $stmtInv->execute();
        $stmtInv->close();

        // Generar el code 
        $codigo = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expiraEn = date('Y-m-d H:i:s', strtotime("+{$minutosValidez} minutes"));

        $sql = "INSERT INTO sb_codigo_verificacion (id_usuario, codigo, tipo, expira_en, utilizado, intentos)
                VALUES (?, ?, ?, ?, 0, 0)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("isss", $id_usuario, $codigo, $tipo, $expiraEn);
        $stmt->execute();
        $stmt->close();

        return $codigo;
    }

    public function registrar($nombre, $apellido, $correo, $telefono, $ubicacion, $username, $contrasenia, $id_rol = 2)
    {
        if ($this->existeUsername($username)) {
            return [
                "estado" => false,
                "mensaje" => "El nombre de usuario ya está registrado. Por favor elige otro."
            ];
        }

        if ($this->existeCorreo($correo)) {
            return [
                "estado" => false,
                "mensaje" => "El correo electrónico ya se encuentra registrado."
            ];
        }

        $this->conexion->begin_transaction();

        try {
            $passHash = password_hash($contrasenia, PASSWORD_DEFAULT);

            // Usuario inicia con estado = 0 (pendiente de validación)
            $sqlUser = "INSERT INTO sb_usuario (nombre, apellido, username, contrasenia, ubicacion, estado, id_rol) VALUES (?, ?, ?, ?, ?, 0, ?)";
            $stmtUser = $this->conexion->prepare($sqlUser);
            $stmtUser->bind_param("sssssi", $nombre, $apellido, $username, $passHash, $ubicacion, $id_rol);

            if (!$stmtUser->execute()) {
                throw new Exception("Error al guardar el usuario: " . $stmtUser->error);
            }

            $id_usuario = $this->conexion->insert_id;
            $stmtUser->close();

            $sqlCorreo = "INSERT INTO sb_correo_usuario (id_usuario, correo) VALUES (?, ?)";
            $stmtCorreo = $this->conexion->prepare($sqlCorreo);
            $stmtCorreo->bind_param("is", $id_usuario, $correo);
            if (!$stmtCorreo->execute()) {
                throw new Exception("Error al asociar el correo electrónico: " . $stmtCorreo->error);
            }
            $stmtCorreo->close();

            $sqlTel = "INSERT INTO sb_telefono_usuario (id_usuario, telefono) VALUES (?, ?)";
            $stmtTel = $this->conexion->prepare($sqlTel);
            $stmtTel->bind_param("is", $id_usuario, $telefono);
            if (!$stmtTel->execute()) {
                throw new Exception("Error al asociar el teléfono: " . $stmtTel->error);
            }
            $stmtTel->close();

            $codigo = $this->generarCodigoVerificacion($id_usuario, 'registro', 15);

            $this->conexion->commit();

            $envio = Mailer::enviarCodigoVerificacion($correo, $nombre, $codigo);

            if (empty($envio['exito'])) {
                return [
                    "estado" => true,
                    "mensaje" => "Usuario registrado, pero hubo un problema al enviar el correo. Intenta reenviar el código desde la pantalla de verificación.",
                    "correo" => $correo
                ];
            }

            return [
                "estado" => true,
                "mensaje" => "Usuario registrado. Te enviamos un código de verificación a tu correo.",
                "correo" => $correo
            ];
        } catch (Throwable $e) {
            $this->conexion->rollback();
            error_log("[RegistroDAO] Error al registrar usuario: " . $e->getMessage());
            return [
                "estado" => false,
                "mensaje" => "Ocurrió un error al registrar el usuario. Intenta nuevamente."
            ];
        }
    }

    //Valida el code
    public function validarCodigo($correo, $codigo, $tipo = 'registro')
    {
        $usuario = $this->obtenerUsuarioPorCorreo($correo);
        if (!$usuario) {
            return [
                "estado" => false,
                "mensaje" => "El código ingresado es incorrecto o ha expirado."
            ];
        }

        if ((int)$usuario['estado'] === 1) {
            return [
                "estado" => false,
                "mensaje" => "El código ingresado es incorrecto o ha expirado."
            ];
        }

        $id_usuario = (int)$usuario['id_usuario'];

        $sql = "SELECT id_codigo, codigo, expira_en, utilizado, intentos
                FROM sb_codigo_verificacion
                WHERE id_usuario = ? AND tipo = ? AND utilizado = 0
                ORDER BY id_codigo DESC
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("is", $id_usuario, $tipo);
        $stmt->execute();
        $res = $stmt->get_result();
        $reg = $res->fetch_assoc();
        $stmt->close();

        if (!$reg) {
            return [
                "estado" => false,
                "mensaje" => "No hay ningún código activo. Por favor solicita uno nuevo."
            ];
        }

        if ((int)$reg['intentos'] >= 5) {
            return [
                "estado" => false,
                "mensaje" => "Has superado el número máximo de intentos fallidos. Solicita un nuevo código."
            ];
        }

        if (strtotime($reg['expira_en']) < time()) {
            return [
                "estado" => false,
                "mensaje" => "El código ha expirado. Por favor solicita uno nuevo."
            ];
        }

        if (trim($reg['codigo']) !== trim($codigo)) {
            $sqlUp = "UPDATE sb_codigo_verificacion SET intentos = intentos + 1 WHERE id_codigo = ?";
            $stmtUp = $this->conexion->prepare($sqlUp);
            $stmtUp->bind_param("i", $reg['id_codigo']);
            $stmtUp->execute();
            $stmtUp->close();

            $restantes = 5 - ((int)$reg['intentos'] + 1);
            return [
                "estado" => false,
                "mensaje" => "El código ingresado es incorrecto. Intentos restantes: {$restantes}."
            ];
        }

        $this->conexion->begin_transaction();
        try {
            $sqlUse = "UPDATE sb_codigo_verificacion SET utilizado = 1 WHERE id_codigo = ?";
            $stmtUse = $this->conexion->prepare($sqlUse);
            $stmtUse->bind_param("i", $reg['id_codigo']);
            $stmtUse->execute();
            $stmtUse->close();

            $sqlAct = "UPDATE sb_usuario SET estado = 1 WHERE id_usuario = ?";
            $stmtAct = $this->conexion->prepare($sqlAct);
            $stmtAct->bind_param("i", $id_usuario);
            $stmtAct->execute();
            $stmtAct->close();

            $this->conexion->commit();

            return [
                "estado" => true,
                "mensaje" => "¡Cuenta verificada exitosamente! Ya puedes iniciar sesión y comenzar a pujar."
            ];
        } catch (Throwable $e) {
            $this->conexion->rollback();
            error_log("[RegistroDAO] Error al activar cuenta: " . $e->getMessage());
            return [
                "estado" => false,
                "mensaje" => "Ocurrió un error al activar la cuenta. Intenta nuevamente."
            ];
        }
    }


    public function reenviarCodigo($correo, $tipo = 'registro', $minutosValidez = 15)
    {
        $mensajeGenerico = "Si el correo está registrado y pendiente de verificación, recibirás un nuevo código.";

        $usuario = $this->obtenerUsuarioPorCorreo($correo);
        if (!$usuario || (int)$usuario['estado'] === 1) {
            return [
                "estado" => true,
                "mensaje" => $mensajeGenerico,
                "correo" => $correo
            ];
        }

        $id_usuario = (int)$usuario['id_usuario'];

        // Rate limit en backend: mínimo 60 segundos entre reenvíos
        $sqlLast = "SELECT expira_en
                    FROM sb_codigo_verificacion
                    WHERE id_usuario = ? AND tipo = ?
                    ORDER BY id_codigo DESC
                    LIMIT 1";
        $stmtLast = $this->conexion->prepare($sqlLast);
        $stmtLast->bind_param("is", $id_usuario, $tipo);
        $stmtLast->execute();
        $resLast = $stmtLast->get_result();
        $ultimo = $resLast->fetch_assoc();
        $stmtLast->close();

        if ($ultimo) {
            $creadoEn = strtotime($ultimo['expira_en']) - ($minutosValidez * 60);
            $transcurrido = time() - $creadoEn;
            if ($transcurrido >= 0 && $transcurrido < 60) {
                $espera = 60 - $transcurrido;
                return [
                    "estado" => false,
                    "mensaje" => "Por favor espera {$espera} segundos antes de solicitar un nuevo código."
                ];
            }
        }

        $codigo = $this->generarCodigoVerificacion($id_usuario, $tipo, $minutosValidez);
        $envio = Mailer::enviarCodigoVerificacion($correo, $usuario['nombre'], $codigo);

        if (empty($envio['exito'])) {
            $sqlFail = "UPDATE sb_codigo_verificacion SET utilizado = 1 WHERE id_usuario = ? AND tipo = ? AND utilizado = 0";
            $stmtFail = $this->conexion->prepare($sqlFail);
            $stmtFail->bind_param("is", $id_usuario, $tipo);
            $stmtFail->execute();
            $stmtFail->close();

            return [
                "estado" => false,
                "mensaje" => "No se pudo reenviar el correo de verificación. Intenta nuevamente más tarde."
            ];
        }

        return [
            "estado" => true,
            "mensaje" => $mensajeGenerico,
            "correo" => $correo
        ];
    }
}
