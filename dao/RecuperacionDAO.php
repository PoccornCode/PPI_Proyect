<?php

//para nath basado en lo mio.
//saludos a tus conejos 

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../helpers/Mailer.php';

class RecuperacionDAO
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
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
        $usuario = $res->fetch_assoc();
        $stmt->close();

        return $usuario ?: null;
    }

    public function solicitarCodigoRecuperacion($correo, $minutosValidez = 15)
    {
        $mensajeGenerico = "Si el correo está registrado y activo, recibirás un código de recuperación.";

        $usuario = $this->obtenerUsuarioPorCorreo($correo);
        if (!$usuario || (int)$usuario['estado'] !== 1) {
            return [
                "estado" => true,
                "mensaje" => $mensajeGenerico,
                "correo" => $correo
            ];
        }

        $id_usuario = (int)$usuario['id_usuario'];

        // Rate limit en backend: mínimo 60 segundos entre solicitudes
        $sqlLast = "SELECT expira_en
                    FROM sb_codigo_verificacion
                    WHERE id_usuario = ? AND tipo = 'recuperacion'
                    ORDER BY id_codigo DESC
                    LIMIT 1";
        $stmtLast = $this->conexion->prepare($sqlLast);
        $stmtLast->bind_param("i", $id_usuario);
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

        // Invalidar códigos anteriores no utilizados de tipo recuperacion
        $sqlInv = "UPDATE sb_codigo_verificacion 
                   SET utilizado = 1 
                   WHERE id_usuario = ? AND tipo = 'recuperacion' AND utilizado = 0";
        $stmtInv = $this->conexion->prepare($sqlInv);
        $stmtInv->bind_param("i", $id_usuario);
        $stmtInv->execute();
        $stmtInv->close();

        // Generar código numérico de 6 dígitos
        $codigo = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expiraEn = date('Y-m-d H:i:s', strtotime("+{$minutosValidez} minutes"));

        $sql = "INSERT INTO sb_codigo_verificacion (id_usuario, codigo, tipo, expira_en, utilizado, intentos)
                VALUES (?, ?, 'recuperacion', ?, 0, 0)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("iss", $id_usuario, $codigo, $expiraEn);
        $stmt->execute();
        $id_codigo = $this->conexion->insert_id;
        $stmt->close();

        // Envío de correo mediante el helper Mailer
        $envio = Mailer::enviarCodigoRecuperacion($correo, $usuario['nombre'], $codigo);

        if (empty($envio['exito'])) {
            $sqlFail = "UPDATE sb_codigo_verificacion SET utilizado = 1 WHERE id_codigo = ?";
            $stmtFail = $this->conexion->prepare($sqlFail);
            $stmtFail->bind_param("i", $id_codigo);
            $stmtFail->execute();
            $stmtFail->close();

            return [
                "estado" => false,
                "mensaje" => "No se pudo enviar el correo de recuperación. Intenta nuevamente más tarde."
            ];
        }

        return [
            "estado" => true,
            "mensaje" => $mensajeGenerico,
            "correo" => $correo
        ];
    }

    public function restablecerContrasenia($correo, $codigo, $nuevaContrasenia)
    {
        $usuario = $this->obtenerUsuarioPorCorreo($correo);
        if (!$usuario || (int)$usuario['estado'] !== 1) {
            return [
                "estado" => false,
                "mensaje" => "El código es incorrecto o ha expirado."
            ];
        }

        $id_usuario = (int)$usuario['id_usuario'];

        $sql = "SELECT id_codigo, codigo, expira_en, utilizado, intentos
                FROM sb_codigo_verificacion
                WHERE id_usuario = ? AND tipo = 'recuperacion' AND utilizado = 0
                ORDER BY id_codigo DESC
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $reg = $res->fetch_assoc();
        $stmt->close();

        if (!$reg) {
            return [
                "estado" => false,
                "mensaje" => "No hay ningún código de recuperación activo. Solicita uno nuevo."
            ];
        }

        if ((int)$reg['intentos'] >= 5) {
            return [
                "estado" => false,
                "mensaje" => "Has superado el límite de intentos permitidos. Solicita un nuevo código."
            ];
        }

        if (strtotime($reg['expira_en']) < time()) {
            return [
                "estado" => false,
                "mensaje" => "El código ha expirado. Solicita un nuevo código."
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
                "mensaje" => "Código incorrecto. Intentos restantes: {$restantes}."
            ];
        }

        $this->conexion->begin_transaction();
        try {
            // Marcar código como utilizado
            $sqlUse = "UPDATE sb_codigo_verificacion SET utilizado = 1 WHERE id_codigo = ?";
            $stmtUse = $this->conexion->prepare($sqlUse);
            $stmtUse->bind_param("i", $reg['id_codigo']);
            $stmtUse->execute();
            $stmtUse->close();

            // Actualizar contraseña con hash seguro
            $hash = password_hash($nuevaContrasenia, PASSWORD_DEFAULT);
            $sqlPass = "UPDATE sb_usuario SET contrasenia = ? WHERE id_usuario = ?";
            $stmtPass = $this->conexion->prepare($sqlPass);
            $stmtPass->bind_param("si", $hash, $id_usuario);
            $stmtPass->execute();
            $stmtPass->close();

            $this->conexion->commit();

            return [
                "estado" => true,
                "mensaje" => "Tu contraseña ha sido restablecida exitosamente. Ya puedes iniciar sesión."
            ];
        } catch (Throwable $e) {
            $this->conexion->rollback();
            error_log("[RecuperacionDAO] Error al actualizar contraseña: " . $e->getMessage());
            return [
                "estado" => false,
                "mensaje" => "Ocurrió un error al actualizar la contraseña. Intenta nuevamente."
            ];
        }
    }
}
