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
