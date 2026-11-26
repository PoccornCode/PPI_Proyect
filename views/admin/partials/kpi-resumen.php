<?php
$tarjetasKpi = [
    [
        'id'     => 'Usuarios',
        'icono'  => 'fa-solid fa-users',
        'valor'  => number_format((int)($kpisAdmin['usuarios'] ?? 0)),
        'label'  => 'Usuarios registrados',
        'trend'  => $kpisAdmin['tendencia_usuarios'] ?? ['texto' => '0%', 'icono' => 'fa-solid fa-minus', 'clase' => ''],
        'fecha'  => $kpisAdmin['ultimo_usuario'] ?? 'Sin registros recientes',
        'barras' => $kpisAdmin['barras_usuarios'] ?? [],
    ],
    [
        'id'     => 'Pujas',
        'icono'  => 'fa-solid fa-arrow-trend-up',
        'valor'  => number_format((int)($kpisAdmin['pujas'] ?? 0)),
        'label'  => 'Pujas registradas',
        'trend'  => $kpisAdmin['tendencia_pujas'] ?? ['texto' => '0%', 'icono' => 'fa-solid fa-minus', 'clase' => ''],
        'fecha'  => $kpisAdmin['ultima_puja'] ?? 'Sin registros recientes',
        'barras' => $kpisAdmin['barras_pujas'] ?? [],
    ],
    [
        'id'     => 'Activas',
        'icono'  => 'fa-solid fa-gavel',
        'valor'  => number_format((int)($kpisAdmin['activas'] ?? 0)),
        'label'  => 'Subastas activas',
        'trend'  => $kpisAdmin['tendencia_activas'] ?? ['texto' => '0%', 'icono' => 'fa-solid fa-minus', 'clase' => ''],
        'fecha'  => $kpisAdmin['ultima_activa'] ?? 'Sin registros recientes',
        'barras' => $kpisAdmin['barras_activas'] ?? [],
    ],
    [
        'id'     => 'Pendientes',
        'icono'  => 'fa-solid fa-stopwatch',
        'valor'  => number_format((int)($kpisAdmin['pendientes'] ?? 0)),
        'label'  => 'Subastas pendientes',
        'trend'  => $kpisAdmin['tendencia_pendientes'] ?? ['texto' => '0%', 'icono' => 'fa-solid fa-minus', 'clase' => ''],
        'fecha'  => $kpisAdmin['ultima_pendiente'] ?? 'Sin registros recientes',
        'barras' => $kpisAdmin['barras_pendientes'] ?? [],
    ],
];
?>
<div class="adm-kpi-grid">
    <?php foreach ($tarjetasKpi as $kpi): ?>
        <article class="adm-kpi-card">
            <div class="adm-kpi-top">
                <div class="adm-kpi-icon"><i class="<?= $kpi['icono'] ?>"></i></div>
                <div>
                    <div class="adm-kpi-value" id="admKpi<?= $kpi['id'] ?>"><?= $kpi['valor'] ?></div>
                    <div class="adm-kpi-label"><?= $kpi['label'] ?></div>
                    <div class="adm-kpi-trend <?= htmlspecialchars($kpi['trend']['clase']) ?>" id="admKpi<?= $kpi['id'] ?>Trend">
                        <i class="<?= htmlspecialchars($kpi['trend']['icono']) ?>"></i>
                        <?= htmlspecialchars($kpi['trend']['texto']) ?>
                    </div>
                </div>
            </div>
            <div class="adm-kpi-footer">
                <span class="adm-kpi-time" id="admKpi<?= $kpi['id'] ?>Fecha">
                    <i class="fa-regular fa-clock"></i> <?= htmlspecialchars($kpi['fecha']) ?>
                </span>
                <svg class="adm-sparkline" id="admKpi<?= $kpi['id'] ?>Sparkline" viewBox="0 0 68 36" aria-hidden="true">
                    <?php foreach ($kpi['barras'] as $barra): ?>
                        <rect
                            x="<?= (int)$barra['x'] ?>"
                            y="<?= (int)$barra['y'] ?>"
                            width="6"
                            height="<?= (int)$barra['height'] ?>"
                            fill="<?= htmlspecialchars($barra['fill']) ?>"
                            rx="1" />
                    <?php endforeach; ?>
                </svg>
            </div>
        </article>
    <?php endforeach; ?>
</div>
