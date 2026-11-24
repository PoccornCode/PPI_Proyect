<?php
require_once __DIR__ . '/../config/conexion.php';

class AdminDAO
{
    private $conexion;

    public function __construct($conexion = null)
    {
        if ($conexion !== null) {
            $this->conexion = $conexion;
        } else {
            global $conexion;
            $this->conexion = $conexion;
        }
    }

    public function obtenerPerfilAdministrador(int $idUsuario): ?array
    {
        if ($idUsuario <= 0 || !$this->conexion || $this->conexion->connect_errno) {
            return null;
        }

        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.username, u.id_rol,
                       COALESCE(r.nombre, '') AS rol_nombre
                FROM sb_usuario u
                INNER JOIN sb_rol r ON u.id_rol = r.id_rol
                WHERE u.id_usuario = ?
                  AND u.estado = 1
                  AND u.id_rol = 1
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }

    public function obtenerResumenAlertasTopbar(): array
    {
        $resumen = [
            'subastas_pendientes'  => 0,
            'contactos_pendientes' => 0,
            'total'                => 0,
        ];

        if (!$this->conexion || $this->conexion->connect_errno) {
            return $resumen;
        }

        $sql = "SELECT
                    (SELECT COUNT(*) FROM sb_subasta WHERE LOWER(estado) = 'pendiente') AS subastas_pendientes,
                    (SELECT COUNT(*) FROM sb_contacto WHERE estado = 0) AS contactos_pendientes";
        $res = $this->conexion->query($sql);
        if ($res && ($row = $res->fetch_assoc())) {
            $subastas = (int)($row['subastas_pendientes'] ?? 0);
            $contactos = (int)($row['contactos_pendientes'] ?? 0);
            $resumen['subastas_pendientes'] = $subastas;
            $resumen['contactos_pendientes'] = $contactos;
            $resumen['total'] = $subastas + $contactos;
        }

        return $resumen;
    }
}
