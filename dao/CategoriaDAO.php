<?php
require_once __DIR__ . '/../config/conexion.php';

class CategoriaDAO
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

    public function listarCategorias(): array
    {
        $categorias = [];
        if (!$this->conexion || $this->conexion->connect_errno) {
            return $categorias;
        }

        $sql = "SELECT id_categoria, nombre, descripcion, imagen
                FROM sb_categoria
                ORDER BY nombre ASC";
        $res = $this->conexion->query($sql);

        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $categorias[] = [
                    'id_categoria' => (int)$row['id_categoria'],
                    'nombre'       => $row['nombre'],
                    'imagen'       => !empty($row['imagen']) ? $row['imagen'] : 'assets/img/cat-coleccionables.jpg',
                    'descripcion'  => !empty($row['descripcion'])
                        ? $row['descripcion']
                        : ('Explora los artículos y subastas disponibles en la categoría de ' . $row['nombre'] . '.'),
                ];
            }
        }

        return $categorias;
    }

    public function listarCategoriasAdmin(): array
    {
        $categorias = [];
        if (!$this->conexion || $this->conexion->connect_errno) {
            return $categorias;
        }

        $sql = "SELECT
                    c.id_categoria,
                    c.nombre,
                    c.descripcion,
                    c.imagen,
                    c.estado,
                    COUNT(DISTINCT p.id_producto) AS total_productos,
                    COUNT(DISTINCT s.id_subasta) AS total_subastas
                FROM sb_categoria c
                LEFT JOIN sb_producto p ON p.id_categoria = c.id_categoria
                LEFT JOIN sb_subasta s ON s.id_producto = p.id_producto
                GROUP BY c.id_categoria, c.nombre, c.descripcion, c.imagen, c.estado
                ORDER BY c.nombre ASC";
        $res = $this->conexion->query($sql);

        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $categorias[] = [
                    'id_categoria'    => (int)$row['id_categoria'],
                    'nombre'          => (string)$row['nombre'],
                    'descripcion'     => (string)($row['descripcion'] ?? ''),
                    'imagen'          => !empty($row['imagen']) ? (string)$row['imagen'] : 'assets/img/cat-coleccionables.jpg',
                    'estado'          => (int)($row['estado'] ?? 1),
                    'total_subastas'  => (int)($row['total_subastas'] ?? 0),
                    'total_productos' => (int)($row['total_productos'] ?? 0),
                ];
            }
        }

        return $categorias;
    }

    public function listarPopulares(int $limite = 6, bool $ordenAlfabetico = true): array
    {
        $categorias = [];
        if (!$this->conexion || $this->conexion->connect_errno) {
            return $categorias;
        }

        $limite = max(1, (int)$limite);
        $orderFinal = $ordenAlfabetico ? "t.nombre ASC" : "t.total DESC, t.nombre ASC";
        $sql = "SELECT t.id_categoria, t.nombre, t.descripcion, t.imagen, t.total
                FROM (
                    SELECT c.id_categoria, c.nombre, c.descripcion, c.imagen, COUNT(p.id_producto) AS total
                    FROM sb_categoria c
                    LEFT JOIN sb_producto p ON c.id_categoria = p.id_categoria
                    GROUP BY c.id_categoria, c.nombre, c.descripcion, c.imagen
                    ORDER BY total DESC, c.nombre ASC
                    LIMIT {$limite}
                ) AS t
                ORDER BY {$orderFinal}";

        $res = $this->conexion->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $categorias[] = [
                    'id_categoria'    => (int)$row['id_categoria'],
                    'nombre'          => $row['nombre'],
                    'imagen'          => !empty($row['imagen']) ? $row['imagen'] : 'assets/img/cat-coleccionables.jpg',
                    'descripcion'     => !empty($row['descripcion']) ? $row['descripcion'] : '',
                    'total_productos' => (int)$row['total'],
                ];
            }
        }

        return $categorias;
    }
}
