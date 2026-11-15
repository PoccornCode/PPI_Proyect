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
        $usuario = $this->obtenerUsuarioPorCorreo($correo);
        if (!$usuario) {
            return [
                "estado" => false,
                "mensaje" => "No se encontró ninguna cuenta asociada a este correo electrónico."
            ];
        }

        if ((int)$usuario['estado'] !== 1) {
            return [
                "estado" => false,
                "mensaje" => "La cuenta está inactiva o pendiente de activación."
            ];
        }

        $id_usuario = (int)$usuario['id_usuario'];

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
        $stmt->close();

        // Envío de correo mediante el helper Mailer
        $envio = Mailer::enviarCodigoRecuperacion($correo, $usuario['nombre'], $codigo);

        return [
            "estado" => true,
            "mensaje" => "Hemos enviado un código de recuperación a tu correo.",
            "correo" => $correo,
            "modo_mail" => $envio['modo'] ?? 'smtp',
            "codigo_dev" => ($envio['modo'] === 'dev' || empty(SMTP_USER) || SMTP_PASS === 'tu_contraseña_aqui') ? $codigo : null
        ];
    }

    public function restablecerContrasenia($correo, $codigo, $nuevaContrasenia)
    {
        $usuario = $this->obtenerUsuarioPorCorreo($correo);
        if (!$usuario) {
            return [
                "estado" => false,
                "mensaje" => "Usuario no encontrado."
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
            return [
                "estado" => false,
                "mensaje" => "Error al actualizar la contraseña: " . $e->getMessage()
            ];
        }
    }
}
