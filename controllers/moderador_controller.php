<?php
require_once __DIR__ . '/../dao/ModeradorDAO.php';
require_once __DIR__ . '/../dao/CategoriaDAO.php';

function formatearFechaKpiEs(?string $fecha): string
{
    if (empty($fecha)) {
        return 'Sin registros recientes';
    }

    $timestamp = strtotime($fecha);
    if ($timestamp === false) {
        return 'Sin registros recientes';
    }

    $dias = [
        0 => 'Domingo',
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
    ];

    $meses = [
        1  => 'ene.',
        2  => 'feb.',
        3  => 'mar.',
        4  => 'abr.',
        5  => 'may.',
        6  => 'jun.',
        7  => 'jul.',
        8  => 'ago.',
        9  => 'sep.',
        10 => 'oct.',
        11 => 'nov.',
        12 => 'dic.',
    ];

    $diaSemana = $dias[(int)date('w', $timestamp)] ?? '';
    $diaMes    = (int)date('j', $timestamp);
    $mes       = $meses[(int)date('n', $timestamp)] ?? '';
    $hora      = date('H:i', $timestamp);

    return "{$diaSemana} {$diaMes} {$mes}, {$hora}";
}

function calcularTendenciaKpi(int $actual, int $anterior): array
{
    if ($anterior === 0) {
        if ($actual === 0) {
            return [
                'texto'   => '0%',
                'icono'   => 'fa-solid fa-minus',
                'clase'   => '',
            ];
        }

        return [
            'texto' => '+100%',
            'icono' => 'fa-solid fa-angles-up',
            'clase' => '',
        ];
    }

    $porcentaje = (int)round((($actual - $anterior) / $anterior) * 100);

    if ($porcentaje > 0) {
        return [
            'texto' => "+{$porcentaje}%",
            'icono' => 'fa-solid fa-angles-up',
            'clase' => '',
        ];
    }

    if ($porcentaje < 0) {
        return [
            'texto' => "{$porcentaje}%",
            'icono' => 'fa-solid fa-angles-down',
            'clase' => 'adm-kpi-trend--down',
        ];
    }

    return [
        'texto' => '0%',
        'icono' => 'fa-solid fa-minus',
        'clase' => '',
    ];
}

function construirBarrasSparkline(array $serie): array
{
    $colores = ['#4f5c3d', '#5a6845', '#5a6845', '#65754d', '#6f8054', '#7a8d5c', '#8fa769'];
    $maximo  = max(1, ...array_map('intval', $serie));
    $barras  = [];

    for ($i = 0; $i < 7; $i++) {
        $valor  = (int)($serie[$i] ?? 0);
        $altura = $valor > 0 ? max(6, (int)round(($valor / $maximo) * 32)) : 4;
        $barras[] = [
            'x'      => 2 + ($i * 9),
            'y'      => 36 - $altura,
            'height' => $altura,
            'fill'   => $colores[$i],
            'valor'  => $valor,
        ];
    }

    return $barras;
}

function obtenerKpisModerador(): array
{
    $dao = new ModeradorDAO();
    $inactivadas = $dao->inactivarSubastasVencidas();
    $resumen     = $dao->obtenerResumenKpis();

    return [
        'inactivadas'          => $inactivadas,
        'activas'              => $resumen['activas'],
        'pendientes'           => $resumen['pendientes'],
        'ultima_activa'        => formatearFechaKpiEs($resumen['ultima_activa']),
        'ultima_pendiente'     => formatearFechaKpiEs($resumen['ultima_pendiente']),
        'tendencia_activas'    => calcularTendenciaKpi($resumen['activas_ultimos_7d'], $resumen['activas_7d_anteriores']),
        'tendencia_pendientes' => calcularTendenciaKpi($resumen['pendientes_ultimos_7d'], $resumen['pendientes_7d_anteriores']),
        'barras_activas'       => construirBarrasSparkline($resumen['serie_activas']),
        'barras_pendientes'    => construirBarrasSparkline($resumen['serie_pendientes']),
    ];
}

function obtenerCategoriasModerador(): array
{
    $dao = new CategoriaDAO();
    return $dao->listarCategorias();
}

function obtenerPerfilModeradorActual(): array
{
    $idSesion = (int)($_SESSION['id_usuario'] ?? 0);
    $dao = new ModeradorDAO();
    $perfil = $dao->obtenerPerfilModerador($idSesion);

    $nombreCompleto = trim(($perfil['nombre'] ?? ($_SESSION['nombre'] ?? '')) . ' ' . ($perfil['apellido'] ?? ($_SESSION['apellido'] ?? '')));
    $primerNombre = explode(' ', $nombreCompleto)[0] ?: ($perfil['username'] ?? '');
    $inicial = $nombreCompleto !== '' ? strtoupper(mb_substr($nombreCompleto, 0, 1, 'UTF-8')) : '';

    return [
        'id_usuario'      => (int)($perfil['id_usuario'] ?? $idSesion),
        'nombre_completo' => $nombreCompleto,
        'primer_nombre'   => $primerNombre,
        'username'        => (string)($perfil['username'] ?? ($_SESSION['username'] ?? '')),
        'rol_nombre'      => (string)($perfil['rol_nombre'] ?? ''),
        'id_rol'          => (int)($perfil['id_rol'] ?? ($_SESSION['id_rol'] ?? 0)),
        'inicial'         => $inicial,
    ];
}

function normalizarRutaImagenModerador(string $ruta, string $baseUrl = ''): string
{
    $ruta = trim($ruta);
    if ($ruta === '') {
        return '';
    }
    if (strpos($ruta, 'http://') === 0 || strpos($ruta, 'https://') === 0) {
        return $ruta;
    }
    if (strpos($ruta, 'uploads/') === 0 || strpos($ruta, 'assets/') === 0) {
        return $baseUrl . $ruta;
    }
    return $baseUrl . 'uploads/productos/' . ltrim($ruta, '/');
}

function formatearSubastaPendiente(array $row, string $baseUrl = ''): array
{
    $idSubasta  = (int)($row['id_subasta'] ?? 0);
    $codigo     = 'SUB-' . str_pad((string)$idSubasta, 3, '0', STR_PAD_LEFT);
    $titulo     = (string)($row['titulo'] ?? '');
    $condicion  = (string)($row['condicion'] ?? '');
    $categoria  = (string)($row['categoria'] ?? '');
    $username   = (string)($row['username'] ?? '');
    $vendedor   = $username !== '' ? '@' . ltrim($username, '@') : '';
    $precio     = '$' . number_format((float)($row['precio_inicial'] ?? 0), 2, '.', ',');
    $incremento = '$' . number_format((float)($row['incremento_minimo'] ?? 0), 2, '.', ',');

    $rutasOriginales = is_array($row['imagenes'] ?? null) ? $row['imagenes'] : [];
    $imagenesUrls = [];
    foreach ($rutasOriginales as $ruta) {
        $url = normalizarRutaImagenModerador((string)$ruta, $baseUrl);
        if ($url !== '') {
            $imagenesUrls[] = $url;
        }
    }

    $totalFotos = count($imagenesUrls);
    $imagenMiniatura = $imagenesUrls[0] ?? normalizarRutaImagenModerador((string)($row['categoria_imagen'] ?? ''), $baseUrl);
    $etiquetaFotos = $totalFotos === 1 ? '1 foto' : $totalFotos . ' fotos';

    return [
        'id_subasta' => $idSubasta,
        'id'         => $codigo,
        'titulo'     => $titulo,
        'condicion'  => $condicion,
        'fotos'      => $totalFotos,
        'categoria'  => $categoria,
        'vendedor'   => $vendedor,
        'precio'     => $precio,
        'incremento' => $incremento,
        'estado'     => ucfirst(strtolower((string)($row['estado'] ?? 'pendiente'))),
        'img'        => $imagenMiniatura,
        'imagenes'   => $imagenesUrls,
        'detalle'    => (string)($row['descripcion'] ?? ''),
        'specs'      => [
            ['label' => 'Categoría', 'value' => $categoria],
            ['label' => 'Vendedor', 'value' => $vendedor],
            ['label' => 'Precio base', 'value' => $precio . ' (inc. ' . $incremento . ')'],
            ['label' => 'Condición', 'value' => $condicion . ' (' . $etiquetaFotos . ')'],
        ],
    ];
}

function obtenerColaSubastasPendientes(string $baseUrl = ''): array
{
    $dao = new ModeradorDAO();
    $filas = $dao->listarSubastasPendientes();
    $lista = [];

    foreach ($filas as $fila) {
        $lista[] = formatearSubastaPendiente($fila, $baseUrl);
    }

    return $lista;
}

function moderarSubastaPendiente(int $idSubasta, string $decision): array
{
    $dao = new ModeradorDAO();
    return $dao->moderarSubasta($idSubasta, $decision);
}

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!defined('BASE_URL')) {
        require_once __DIR__ . '/../config/env.php';
    }

    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    $opcion = $_POST['opcion'] ?? $_GET['opcion'] ?? 'kpis';

    if ($opcion === 'kpis') {
        echo json_encode([
            'estado' => true,
            'kpis'   => obtenerKpisModerador(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($opcion === 'categorias') {
        echo json_encode([
            'estado'     => true,
            'categorias' => obtenerCategoriasModerador(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($opcion === 'pendientes') {
        echo json_encode([
            'estado'     => true,
            'pendientes' => obtenerColaSubastasPendientes(BASE_URL),
            'kpis'       => obtenerKpisModerador(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($opcion === 'moderar') {
        $idSubasta = (int)($_POST['id_subasta'] ?? 0);
        $decision  = (string)($_POST['decision'] ?? '');
        $resultado = moderarSubastaPendiente($idSubasta, $decision);

        if ($resultado['estado']) {
            $resultado['kpis'] = obtenerKpisModerador();
        }

        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['estado' => false, 'mensaje' => 'Opción no válida'], JSON_UNESCAPED_UNICODE);
    exit;
}
