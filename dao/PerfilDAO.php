<?php
require_once __DIR__ . '/../config/conexion.php';

class PerfilDAO
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }

    public function obtenerPerfil($id_usuario)
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.username, u.ubicacion, u.estado, u.id_rol,
                       (SELECT c.correo FROM sb_correo_usuario c WHERE c.id_usuario = u.id_usuario LIMIT 1) AS correo,
                       (SELECT t.telefono FROM sb_telefono_usuario t WHERE t.id_usuario = u.id_usuario LIMIT 1) AS telefono
                FROM sb_usuario u
                WHERE u.id_usuario = ?
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $perfil = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $perfil ?: null;
    }

    /* Cantidad de productos registrados y de subastas activas del user */
    public function obtenerResumen($id_usuario)
    {
        $sql = "SELECT COUNT(DISTINCT p.id_producto) AS productos,
                       COUNT(DISTINCT CASE WHEN LOWER(s.estado) IN ('activa', 'abierta') THEN s.id_subasta END) AS subastas_activas
                FROM sb_producto p
                LEFT JOIN sb_subasta s ON s.id_producto = p.id_producto
                WHERE p.id_usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return [
            'productos' => (int)($res['productos'] ?? 0),
            'subastas_activas' => (int)($res['subastas_activas'] ?? 0)
        ];
    }

    /* Las subastas activas del usuario que terminan primero. */
    public function obtenerSubastasActivas($id_usuario, $limite = 3)
    {
        $limite = max(1, (int)$limite);
        $sql = "SELECT p.id_producto, p.nombre, img.ruta AS imagen_principal,
                       s.id_subasta, s.precio_inicial, s.fecha_final,
                       TIMESTAMPDIFF(SECOND, NOW(), s.fecha_final) AS segundos_restantes
                FROM sb_producto p
                INNER JOIN sb_subasta s ON s.id_producto = p.id_producto
                LEFT JOIN sb_imagen_producto img ON img.id_producto = p.id_producto AND img.principal = 1
                WHERE p.id_usuario = ? AND LOWER(s.estado) IN ('activa', 'abierta')
                ORDER BY s.fecha_final ASC
                LIMIT {$limite}";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $subastas = [];
        while ($fila = $res->fetch_assoc()) {
            $subastas[] = $fila;
        }
        $stmt->close();

        return $subastas;
    }
}