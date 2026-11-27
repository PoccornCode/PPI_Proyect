<?php
require_once __DIR__ . '/../config/conexion.php';

class CatalogoDAO
{
    const POR_PAGINA = 12;

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

    // subastas activas del catalogo con los filtros que lleguen
    public function listar(array $filtros, int $limite, int $offset): array
    {
        $lista = [];
        [$where, $tipos, $params] = $this->armarFiltros($filtros);

        $ordenes = [
            'pronto'      => 's.fecha_final ASC',
            'reciente'    => 's.fecha_inicial DESC, s.id_subasta DESC',
            'precio_asc'  => 'precio_actual ASC',
            'precio_desc' => 'precio_actual DESC',
            'pujas'       => 'total_pujas DESC, s.fecha_final ASC',
        ];
        $orden = $ordenes[$filtros['orden'] ?? 'pronto'] ?? $ordenes['pronto'];

        $sql = "SELECT s.id_subasta, s.fecha_final,
                       TIMESTAMPDIFF(SECOND, NOW(), s.fecha_final) AS segundos_restantes,
                       p.nombre AS titulo,
                       p.condicion,
                       COALESCE(c.nombre, 'General') AS categoria,
                       img.ruta AS imagen_ruta,
                       COALESCE(pu.total, 0) AS total_pujas,
                       COALESCE(pu.maximo, s.precio_inicial) AS precio_actual
                FROM sb_subasta s
                INNER JOIN sb_producto p ON p.id_producto = s.id_producto
                LEFT JOIN sb_categoria c ON c.id_categoria = p.id_categoria
                LEFT JOIN sb_imagen_producto img ON img.id_imagen_producto = (
                    SELECT MIN(i.id_imagen_producto) FROM sb_imagen_producto i
                    WHERE i.id_producto = p.id_producto AND i.principal = 1)
                LEFT JOIN (SELECT id_subasta, COUNT(*) AS total, MAX(monto) AS maximo
                           FROM sb_puja GROUP BY id_subasta) pu ON pu.id_subasta = s.id_subasta
                WHERE " . implode(' AND ', $where) . "
                ORDER BY {$orden}
                LIMIT ? OFFSET ?";

        $tipos .= 'ii';
        $params[] = $limite;
        $params[] = $offset;

        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param($tipos, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($fila = $res->fetch_assoc()) {
            $lista[] = [
                'id'              => (int)$fila['id_subasta'],
                'titulo'          => $fila['titulo'],
                'condicion'       => $fila['condicion'] ?? '',
                'categoria'       => $fila['categoria'],
                'imagen'          => $this->rutaImagen($fila['imagen_ruta']),
                'precio'          => number_format((float)$fila['precio_actual'], 2, '.', ','),
                'pujas'           => (int)$fila['total_pujas'],
                'tiempo'          => $this->textoTiempo((int)$fila['segundos_restantes']),
                'fecha_final_iso' => date('c', time() + max(0, (int)$fila['segundos_restantes'])),
            ];
        }
        $stmt->close();

        return $lista;
    }

    // cuantas subastas hay para paginar
    public function contar(array $filtros): int
    {
        [$where, $tipos, $params] = $this->armarFiltros($filtros);

        $sql = "SELECT COUNT(*) AS total
                FROM sb_subasta s
                INNER JOIN sb_producto p ON p.id_producto = s.id_producto
                LEFT JOIN sb_categoria c ON c.id_categoria = p.id_categoria
                WHERE " . implode(' AND ', $where);

        $stmt = $this->conexion->prepare($sql);
        if ($tipos !== '') {
            $stmt->bind_param($tipos, ...$params);
        }
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (int)($fila['total'] ?? 0);
    }

    // una subasta con todos sus datos (para views/subasta.php)
    public function obtenerSubasta(int $id): ?array
    {
        $sql = "SELECT s.id_subasta, s.estado, s.precio_inicial, s.incremento_minimo,
                       s.fecha_inicial, s.fecha_final,
                       TIMESTAMPDIFF(SECOND, NOW(), s.fecha_final) AS segundos_restantes,
                       p.id_producto, p.nombre AS titulo, p.descripcion, p.condicion,
                       p.id_usuario AS id_vendedor,
                       COALESCE(c.nombre, 'General') AS categoria,
                       u.username AS vendedor,
                       COALESCE(pu.total, 0) AS total_pujas,
                       COALESCE(pu.maximo, s.precio_inicial) AS precio_actual
                FROM sb_subasta s
                INNER JOIN sb_producto p ON p.id_producto = s.id_producto
                LEFT JOIN sb_categoria c ON c.id_categoria = p.id_categoria
                LEFT JOIN sb_usuario u ON u.id_usuario = p.id_usuario
                LEFT JOIN (SELECT id_subasta, COUNT(*) AS total, MAX(monto) AS maximo
                           FROM sb_puja GROUP BY id_subasta) pu ON pu.id_subasta = s.id_subasta
                WHERE s.id_subasta = ?
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $f = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$f) {
            return null;
        }

        $segundos = (int)$f['segundos_restantes'];
        $tienePujas = (int)$f['total_pujas'] > 0;
        $incremento = (float)($f['incremento_minimo'] ?? 1);

        return [
            'id'              => (int)$f['id_subasta'],
            'id_producto'     => (int)$f['id_producto'],
            'id_vendedor'     => (int)$f['id_vendedor'],
            'estado'          => strtolower((string)$f['estado']),
            'titulo'          => $f['titulo'],
            'descripcion'     => $f['descripcion'] ?? '',
            'condicion'       => $f['condicion'] ?? '',
            'categoria'       => $f['categoria'],
            'vendedor'        => $f['vendedor'] ?? '',
            'pujas'           => (int)$f['total_pujas'],
            'precio'          => number_format((float)$f['precio_actual'], 2, '.', ','),
            'precio_inicial'  => number_format((float)$f['precio_inicial'], 2, '.', ','),
            'incremento'      => number_format($incremento, 2, '.', ','),
            // si ya hay pujas la minima es la mas alta + el incremento
            'puja_minima'     => number_format($tienePujas ? (float)$f['precio_actual'] + $incremento : (float)$f['precio_inicial'], 2, '.', ','),
            'inicio'          => $f['fecha_inicial'] ? date('d/m/Y', strtotime($f['fecha_inicial'])) : '',
            'cierre'          => $f['fecha_final'] ? date('d/m/Y H:i', strtotime($f['fecha_final'])) : '',
            'terminada'       => $segundos <= 0 || $f['estado'] !== 'activa',
            'tiempo'          => $this->textoTiempo($segundos),
            'fecha_final_iso' => date('c', time() + max(0, $segundos)),
        ];
    }

    //fts producto
    public function obtenerImagenes(int $id_producto): array
    {
        $imagenes = [];
        $sql = "SELECT ruta FROM sb_imagen_producto WHERE id_producto = ? ORDER BY principal DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_producto);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($fila = $res->fetch_assoc()) {
            $imagenes[] = $this->rutaImagen($fila['ruta']);
        }
        $stmt->close();

        return $imagenes;
    }

    // arma las vondiciones 
    private function armarFiltros(array $filtros): array
    {
        $where = ["LOWER(s.estado) = 'activa'", "s.fecha_final > NOW()"];
        $tipos = '';
        $params = [];

        if (!empty($filtros['categoria'])) {
            $where[] = "c.nombre = ?";
            $tipos .= 's';
            $params[] = $filtros['categoria'];
        }

        if (!empty($filtros['q'])) {
            $where[] = "(p.nombre LIKE ? OR p.descripcion LIKE ?)";
            $like = '%' . $filtros['q'] . '%';
            $tipos .= 'ss';
            $params[] = $like;
            $params[] = $like;
        }

        if (($filtros['estado'] ?? '') === 'hoy') {
            $where[] = "DATE(s.fecha_final) = CURDATE()";
        }

        return [$where, $tipos, $params];
    }

    private function rutaImagen($ruta): string
    {
        if (empty($ruta)) {
            return 'assets/img/news-1.jpg';
        }
        if (strpos($ruta, 'uploads/') === 0 || strpos($ruta, 'assets/') === 0 || strpos($ruta, 'http') === 0) {
            return $ruta;
        }
        return 'uploads/productos/' . ltrim($ruta, '/');
    }

    private function textoTiempo(int $segundos): string
    {
        if ($segundos <= 0) {
            return 'Finalizada';
        }
        $dias = intdiv($segundos, 86400);
        $horas = intdiv($segundos % 86400, 3600);
        $minutos = intdiv($segundos % 3600, 60);
        $segs = $segundos % 60;

        if ($dias > 0) {
            return sprintf('%d d %02d h', $dias, $horas);
        }
        return sprintf('%02d:%02d:%02d', $horas, $minutos, $segs);
    }
}