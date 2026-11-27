<?php
require_once __DIR__ . '/../config/conexion.php';

class ProductoAdminDAO
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

    public function listarProductosSinSubasta(string $baseUrl = ''): array
    {
        if (!$this->conexion || $this->conexion->connect_errno) {
            return [];
        }

        $sql = "SELECT
                    p.id_producto,
                    p.nombre,
                    p.descripcion,
                    p.condicion,
                    p.estado,
                    COALESCE(c.nombre, 'General') AS categoria,
                    COALESCE(u.username, '') AS username,
                    (
                        SELECT img.ruta
                        FROM sb_imagen_producto img
                        WHERE img.id_producto = p.id_producto
                        ORDER BY img.principal DESC, img.id_imagen_producto ASC
                        LIMIT 1
                    ) AS imagen_principal
                FROM sb_producto p
                LEFT JOIN sb_subasta s ON s.id_producto = p.id_producto
                LEFT JOIN sb_categoria c ON c.id_categoria = p.id_categoria
                LEFT JOIN sb_usuario u ON u.id_usuario = p.id_usuario
                WHERE s.id_subasta IS NULL
                ORDER BY p.id_producto DESC";

        $resultado = $this->conexion->query($sql);
        if (!$resultado) {
            return [];
        }

        $productos = [];
        while ($row = $resultado->fetch_assoc()) {
            $idProducto = (int)($row['id_producto'] ?? 0);
            $estadoNum = (int)($row['estado'] ?? 1);
            $activo = ($estadoNum === 1);
            $username = trim((string)($row['username'] ?? ''));

            $productos[] = [
                'id_producto'  => $idProducto,
                'codigo'       => 'PROD-' . str_pad((string)$idProducto, 3, '0', STR_PAD_LEFT),
                'nombre'       => (string)($row['nombre'] ?? ''),
                'descripcion'  => (string)($row['descripcion'] ?? ''),
                'condicion'    => (string)($row['condicion'] ?? 'Usado'),
                'categoria'    => (string)($row['categoria'] ?? 'General'),
                'vendedor'     => $username !== '' ? '@' . $username : '@usuario',
                'img'          => $this->resolverUrlImagen((string)($row['imagen_principal'] ?? ''), $baseUrl),
                'estado_raw'   => $activo ? '1' : '0',
                'estado_texto' => $activo ? 'Disponible' : 'Inactivo',
                'estado_clase' => $activo ? 'adm-badge--activa' : 'adm-badge--cancelada',
            ];
        }

        $resultado->free();
        return $productos;
    }

    private function resolverUrlImagen(string $ruta, string $baseUrl): string
    {
        $ruta = trim($ruta);
        $prefijo = rtrim($baseUrl, '/');
        $prefijo = $prefijo !== '' ? $prefijo . '/' : '';

        if ($ruta === '') {
            return $prefijo . 'assets/img/placeholder.png';
        }

        if (preg_match('#^https?://#i', $ruta)) {
            return $ruta;
        }

        $rutaLimpia = ltrim($ruta, '/');
        if (strpos($rutaLimpia, 'uploads/') === 0 || strpos($rutaLimpia, 'assets/') === 0) {
            return $prefijo . $rutaLimpia;
        }

        return $prefijo . 'uploads/productos/' . $rutaLimpia;
    }
}
