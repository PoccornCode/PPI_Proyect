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
        'inactivadas'        => $inactivadas,
        'activas'            => $resumen['activas'],
        'pendientes'         => $resumen['pendientes'],
        'ultima_activa'      => formatearFechaKpiEs($resumen['ultima_activa']),
        'ultima_pendiente'   => formatearFechaKpiEs($resumen['ultima_pendiente']),
        'tendencia_activas'  => calcularTendenciaKpi($resumen['activas_ultimos_7d'], $resumen['activas_7d_anteriores']),
        'tendencia_pendientes' => calcularTendenciaKpi($resumen['pendientes_ultimos_7d'], $resumen['pendientes_7d_anteriores']),
        'barras_activas'     => construirBarrasSparkline($resumen['serie_activas']),
        'barras_pendientes'  => construirBarrasSparkline($resumen['serie_pendientes']),
    ];
}

function obtenerCategoriasModerador(): array
{
    $dao = new CategoriaDAO();
    return $dao->listarCategorias();
}

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    header('Content-Type: application/json; charset=UTF-8');
    $opcion = $_GET['opcion'] ?? 'kpis';

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

    echo json_encode(['estado' => false, 'mensaje' => 'Opción no válida'], JSON_UNESCAPED_UNICODE);
    exit;
}
