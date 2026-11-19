<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../helpers/Mailer.php';

class ContactoDAO
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }

    public function obtenerDatosUsuario(int $id_usuario): ?array
    {
        $sql = "SELECT u.nombre, u.apellido,
                       (SELECT c.correo FROM sb_correo_usuario c WHERE c.id_usuario = u.id_usuario LIMIT 1) AS correo,
                       (SELECT t.telefono FROM sb_telefono_usuario t WHERE t.id_usuario = u.id_usuario LIMIT 1) AS telefono
                FROM sb_usuario u
                WHERE u.id_usuario = ?
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $stmt->close();

        return $data ?: null;
    }

    public function registrarMensaje(string $nombre, string $correo, string $telefono, string $asunto, string $mensaje, ?int $id_usuario = null): array
    {
        try {
            $sql = "INSERT INTO sb_contacto (id_usuario, nombre, correo, telefono, asunto, mensaje, estado)
                    VALUES (?, ?, ?, ?, ?, ?, 0)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("isssss", $id_usuario, $nombre, $correo, $telefono, $asunto, $mensaje);

            if (!$stmt->execute()) {
                throw new Exception("Error al guardar el mensaje: " . $stmt->error);
            }

            $id_contacto = $this->conexion->insert_id;
            $stmt->close();

            $envioSoporte = Mailer::enviarMensajeContacto($nombre, $correo, $telefono, $asunto, $mensaje);
            $envioConfirmacion = Mailer::enviarConfirmacionContacto($correo, $nombre, $asunto);

            if (empty($envioSoporte['exito']) || empty($envioConfirmacion['exito'])) {
                error_log("[ContactoDAO] Mensaje #{$id_contacto} guardado en BD con advertencia en envío de correo.");
            }

            return [
                "estado" => true,
                "mensaje" => "¡Mensaje enviado con éxito! Nuestro equipo te responderá pronto.",
                "id_contacto" => $id_contacto
            ];
        } catch (Throwable $e) {
            error_log("[ContactoDAO] Error al registrar mensaje de contacto: " . $e->getMessage());
            return [
                "estado" => false,
                "mensaje" => "Ocurrió un error al enviar tu mensaje. Por favor intenta nuevamente."
            ];
        }
    }
}
