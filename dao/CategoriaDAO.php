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

    public function listarCategorias(bool $soloActivas = true): array
    {
        $categorias = [];
        if (!$this->conexion || $this->conexion->connect_errno) {
            return $categorias;
        }

        $where = $soloActivas ? "WHERE estado = 1" : "";
        $sql = "SELECT id_categoria, nombre, descripcion, imagen
                FROM sb_categoria
                {$where}
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

    public function existeNombreCategoria(string $nombre, int $excluirId = 0): bool
    {
        if (!$this->conexion || $this->conexion->connect_errno) {
            return false;
        }

        $nombreLimpio = trim($nombre);
        if ($nombreLimpio === '') {
            return false;
        }

        if ($excluirId > 0) {
            $stmt = $this->conexion->prepare(
                "SELECT id_categoria FROM sb_categoria WHERE LOWER(nombre) = LOWER(?) AND id_categoria <> ? LIMIT 1"
            );
            if (!$stmt) return false;
            $stmt->bind_param('si', $nombreLimpio, $excluirId);
        } else {
            $stmt = $this->conexion->prepare(
                "SELECT id_categoria FROM sb_categoria WHERE LOWER(nombre) = LOWER(?) LIMIT 1"
            );
            if (!$stmt) return false;
            $stmt->bind_param('s', $nombreLimpio);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $existe = ($res && $res->num_rows > 0);
        $stmt->close();

        return $existe;
    }

    public function validarDatosCategoria(string $nombre, string $descripcion, int $excluirId = 0): array
    {
        $nombreLimpio = trim($nombre);
        $descLimpia = trim($descripcion);

        if ($nombreLimpio === '') {
            return [
                'estado'  => false,
                'campo'   => 'nombre',
                'mensaje' => 'El nombre de la categoría es obligatorio.',
            ];
        }

        if (mb_strlen($nombreLimpio, 'UTF-8') > 50) {
            return [
                'estado'  => false,
                'campo'   => 'nombre',
                'mensaje' => 'El nombre no puede superar los 50 caracteres.',
            ];
        }

        if (mb_strlen($descLimpia, 'UTF-8') > 255) {
            return [
                'estado'  => false,
                'campo'   => 'descripcion',
                'mensaje' => 'La descripción no puede superar los 255 caracteres.',
            ];
        }

        if ($this->existeNombreCategoria($nombreLimpio, $excluirId)) {
            return [
                'estado'  => false,
                'campo'   => 'nombre',
                'mensaje' => 'Ya existe una categoría con el nombre "' . $nombreLimpio . '".',
            ];
        }

        return [
            'estado'      => true,
            'nombre'      => $nombreLimpio,
            'descripcion' => $descLimpia,
        ];
    }

    public function crearCategoria(string $nombre, string $descripcion, string $imagen, int $estado = 1): array
    {
        if (!$this->conexion || $this->conexion->connect_errno) {
            return [
                'estado'  => false,
                'mensaje' => 'No hay conexión disponible con la base de datos.',
            ];
        }

        $validacion = $this->validarDatosCategoria($nombre, $descripcion);
        if (empty($validacion['estado'])) {
            return $validacion;
        }

        $nombreLimpio = $validacion['nombre'];
        $descLimpia = $validacion['descripcion'];
        $estadoFinal = $estado === 1 ? 1 : 0;
        $imagenFinal = trim($imagen) !== '' ? trim($imagen) : 'assets/img/cat-coleccionables.jpg';

        $descParam = $descLimpia !== '' ? $descLimpia : null;
        $stmt = $this->conexion->prepare(
            "INSERT INTO sb_categoria (nombre, descripcion, imagen, estado) VALUES (?, ?, ?, ?)"
        );
        if (!$stmt) {
            return [
                'estado'  => false,
                'mensaje' => 'No se pudo preparar el registro de la categoría.',
            ];
        }

        $stmt->bind_param('sssi', $nombreLimpio, $descParam, $imagenFinal, $estadoFinal);
        $ok = $stmt->execute();
        $nuevoId = $ok ? (int)$stmt->insert_id : 0;
        $stmt->close();

        if (!$ok || $nuevoId <= 0) {
            return [
                'estado'  => false,
                'mensaje' => 'Ocurrió un error al guardar la categoría en la base de datos.',
            ];
        }

        return [
            'estado'    => true,
            'mensaje'   => $estadoFinal === 1
                ? ('Categoría "' . $nombreLimpio . '" creada y publicada.')
                : ('Categoría "' . $nombreLimpio . '" creada como desactivada.'),
            'categoria' => [
                'id_categoria'    => $nuevoId,
                'nombre'          => $nombreLimpio,
                'descripcion'     => $descLimpia,
                'imagen'          => $imagenFinal,
                'estado'          => $estadoFinal,
                'total_subastas'  => 0,
                'total_productos' => 0,
            ],
        ];
    }

    public function obtenerCategoriaPorId(int $idCategoria): ?array
    {
        if (!$this->conexion || $this->conexion->connect_errno || $idCategoria <= 0) {
            return null;
        }

        $stmt = $this->conexion->prepare(
            "SELECT id_categoria, nombre, descripcion, imagen, estado
             FROM sb_categoria
             WHERE id_categoria = ?
             LIMIT 1"
        );
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $idCategoria);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$row) {
            return null;
        }

        return [
            'id_categoria' => (int)$row['id_categoria'],
            'nombre'       => (string)$row['nombre'],
            'descripcion'  => (string)($row['descripcion'] ?? ''),
            'imagen'       => !empty($row['imagen']) ? (string)$row['imagen'] : 'assets/img/cat-coleccionables.jpg',
            'estado'       => (int)($row['estado'] ?? 1),
        ];
    }

    private function verificarBloqueoDesactivacion(int $idCategoria, string $nombre): ?array
    {
        $stmtCheck = $this->conexion->prepare(
            "SELECT
                SUM(CASE WHEN LOWER(s.estado) = 'activa' THEN 1 ELSE 0 END) AS activas,
                SUM(CASE WHEN LOWER(s.estado) = 'pendiente' THEN 1 ELSE 0 END) AS pendientes
             FROM sb_subasta s
             INNER JOIN sb_producto p ON p.id_producto = s.id_producto
             WHERE p.id_categoria = ?
               AND LOWER(s.estado) IN ('activa', 'pendiente')"
        );
        if (!$stmtCheck) {
            return [
                'estado'  => false,
                'mensaje' => 'No se pudieron verificar las subastas asociadas a la categoría.',
            ];
        }

        $stmtCheck->bind_param('i', $idCategoria);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();
        $conteo = $resCheck ? $resCheck->fetch_assoc() : null;
        $stmtCheck->close();

        $activas = (int)($conteo['activas'] ?? 0);
        $pendientes = (int)($conteo['pendientes'] ?? 0);

        if (($activas + $pendientes) > 0) {
            $partes = [];
            if ($activas > 0) {
                $partes[] = $activas === 1 ? '1 subasta activa' : ($activas . ' subastas activas');
            }
            if ($pendientes > 0) {
                $partes[] = $pendientes === 1 ? '1 subasta pendiente' : ($pendientes . ' subastas pendientes');
            }

            return [
                'estado'              => false,
                'bloqueado'           => true,
                'subastas_activas'    => $activas,
                'subastas_pendientes' => $pendientes,
                'mensaje'             => 'No puedes desactivar "' . $nombre . '" porque tiene ' . implode(' y ', $partes) . '.',
            ];
        }

        return null;
    }

    public function actualizarCategoria(int $idCategoria, string $nombre, string $descripcion, ?string $nuevaImagen, int $estado): array
    {
        if (!$this->conexion || $this->conexion->connect_errno) {
            return [
                'estado'  => false,
                'mensaje' => 'No hay conexión disponible con la base de datos.',
            ];
        }

        if ($idCategoria <= 0) {
            return [
                'estado'  => false,
                'mensaje' => 'ID de categoría no válido.',
            ];
        }

        $actual = $this->obtenerCategoriaPorId($idCategoria);
        if (!$actual) {
            return [
                'estado'  => false,
                'mensaje' => 'No se encontró la categoría solicitada.',
            ];
        }

        $validacion = $this->validarDatosCategoria($nombre, $descripcion, $idCategoria);
        if (empty($validacion['estado'])) {
            return $validacion;
        }

        $nombreLimpio = $validacion['nombre'];
        $descLimpia = $validacion['descripcion'];
        $estadoFinal = $estado === 1 ? 1 : 0;

        if ($estadoFinal === 0 && (int)$actual['estado'] === 1) {
            $bloqueo = $this->verificarBloqueoDesactivacion($idCategoria, $nombreLimpio);
            if ($bloqueo !== null) {
                return $bloqueo;
            }
        }

        $imagenAnterior = (string)$actual['imagen'];
        $imagenFinal = ($nuevaImagen !== null && trim($nuevaImagen) !== '')
            ? trim($nuevaImagen)
            : $imagenAnterior;

        $descParam = $descLimpia !== '' ? $descLimpia : null;
        $stmt = $this->conexion->prepare(
            "UPDATE sb_categoria
             SET nombre = ?, descripcion = ?, imagen = ?, estado = ?
             WHERE id_categoria = ?"
        );
        if (!$stmt) {
            return [
                'estado'  => false,
                'mensaje' => 'No se pudo preparar la actualización de la categoría.',
            ];
        }

        $stmt->bind_param('sssii', $nombreLimpio, $descParam, $imagenFinal, $estadoFinal, $idCategoria);
        $ok = $stmt->execute();
        $stmt->close();

        if (!$ok) {
            return [
                'estado'  => false,
                'mensaje' => 'Ocurrió un error al actualizar la categoría.',
            ];
        }

        return [
            'estado'          => true,
            'mensaje'         => 'Categoría "' . $nombreLimpio . '" actualizada correctamente.',
            'imagen_anterior' => $imagenAnterior,
            'categoria'       => [
                'id_categoria' => $idCategoria,
                'nombre'       => $nombreLimpio,
                'descripcion'  => $descLimpia,
                'imagen'       => $imagenFinal,
                'estado'       => $estadoFinal,
            ],
        ];
    }

    public function cambiarEstadoCategoria(int $idCategoria, ?int $nuevoEstado = null): array
    {
        if (!$this->conexion || $this->conexion->connect_errno) {
            return [
                'estado'  => false,
                'mensaje' => 'No hay conexión disponible con la base de datos.',
            ];
        }

        if ($idCategoria <= 0) {
            return [
                'estado'  => false,
                'mensaje' => 'ID de categoría no válido.',
            ];
        }

        $stmtSel = $this->conexion->prepare("SELECT id_categoria, nombre, estado FROM sb_categoria WHERE id_categoria = ? LIMIT 1");
        if (!$stmtSel) {
            return [
                'estado'  => false,
                'mensaje' => 'No se pudo consultar la categoría.',
            ];
        }

        $stmtSel->bind_param('i', $idCategoria);
        $stmtSel->execute();
        $resSel = $stmtSel->get_result();
        $cat = $resSel ? $resSel->fetch_assoc() : null;
        $stmtSel->close();

        if (!$cat) {
            return [
                'estado'  => false,
                'mensaje' => 'No se encontró la categoría solicitada.',
            ];
        }

        $estadoActual = (int)$cat['estado'];
        $estadoFinal = ($nuevoEstado === null)
            ? ($estadoActual === 1 ? 0 : 1)
            : ($nuevoEstado === 1 ? 1 : 0);

        $nombre = (string)$cat['nombre'];

        if ($estadoFinal === 0) {
            $bloqueo = $this->verificarBloqueoDesactivacion($idCategoria, $nombre);
            if ($bloqueo !== null) {
                return $bloqueo;
            }
        }

        if ($estadoFinal !== $estadoActual) {
            $stmtUpd = $this->conexion->prepare("UPDATE sb_categoria SET estado = ? WHERE id_categoria = ?");
            if (!$stmtUpd) {
                return [
                    'estado'  => false,
                    'mensaje' => 'No se pudo actualizar el estado de la categoría.',
                ];
            }

            $stmtUpd->bind_param('ii', $estadoFinal, $idCategoria);
            $ok = $stmtUpd->execute();
            $stmtUpd->close();

            if (!$ok) {
                return [
                    'estado'  => false,
                    'mensaje' => 'Ocurrió un error al guardar el nuevo estado.',
                ];
            }
        }

        return [
            'estado'    => true,
            'mensaje'   => $estadoFinal === 1
                ? ('La categoría "' . $nombre . '" fue publicada.')
                : ('La categoría "' . $nombre . '" fue desactivada.'),
            'categoria' => [
                'id_categoria' => (int)$cat['id_categoria'],
                'nombre'       => $nombre,
                'estado'       => $estadoFinal,
            ],
        ];
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
                    WHERE c.estado = 1
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
