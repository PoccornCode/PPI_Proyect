<?php
$kpiActivas     = (int)($kpisModerador['activas'] ?? 0);
$kpiPendientes  = (int)($kpisModerador['pendientes'] ?? 0);
$trendActivas   = $kpisModerador['tendencia_activas'] ?? ['texto' => '0%', 'icono' => 'fa-solid fa-minus', 'clase' => ''];
$trendPend      = $kpisModerador['tendencia_pendientes'] ?? ['texto' => '0%', 'icono' => 'fa-solid fa-minus', 'clase' => ''];
$fechaActivas   = $kpisModerador['ultima_activa'] ?? 'Sin registros recientes';
$fechaPend      = $kpisModerador['ultima_pendiente'] ?? 'Sin registros recientes';
$barrasActivas  = $kpisModerador['barras_activas'] ?? [];
$barrasPend     = $kpisModerador['barras_pendientes'] ?? [];
?>
<div class="adm-kpi-grid mod-kpi-grid-2">
    <article class="adm-kpi-card">
        <div class="adm-kpi-top">
            <div class="adm-kpi-icon"><i class="fa-solid fa-gavel"></i></div>
            <div>
                <div class="adm-kpi-value" id="modKpiActivas"><?= $kpiActivas ?></div>
                <div class="adm-kpi-label">Subastas activas</div>
                <div class="adm-kpi-trend <?= htmlspecialchars($trendActivas['clase']) ?>" id="modKpiActivasTrend">
                    <i class="<?= htmlspecialchars($trendActivas['icono']) ?>"></i>
                    <?= htmlspecialchars($trendActivas['texto']) ?>
                </div>
            </div>
        </div>
        <div class="adm-kpi-footer">
            <span class="adm-kpi-time" id="modKpiActivasFecha">
                <i class="fa-regular fa-clock"></i> <?= htmlspecialchars($fechaActivas) ?>
            </span>
            <svg class="adm-sparkline" id="modKpiActivasSparkline" viewBox="0 0 68 36" aria-hidden="true">
                <?php foreach ($barrasActivas as $barra): ?>
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

    <article class="adm-kpi-card">
        <div class="adm-kpi-top">
            <div class="adm-kpi-icon"><i class="fa-solid fa-stopwatch"></i></div>
            <div>
                <div class="adm-kpi-value" id="modKpiPendientes"><?= $kpiPendientes ?></div>
                <div class="adm-kpi-label">Subastas pendientes</div>
                <div class="adm-kpi-trend <?= htmlspecialchars($trendPend['clase']) ?>" id="modKpiPendientesTrend">
                    <i class="<?= htmlspecialchars($trendPend['icono']) ?>"></i>
                    <?= htmlspecialchars($trendPend['texto']) ?>
                </div>
            </div>
        </div>
        <div class="adm-kpi-footer">
            <span class="adm-kpi-time" id="modKpiPendientesFecha">
                <i class="fa-regular fa-clock"></i> <?= htmlspecialchars($fechaPend) ?>
            </span>
            <svg class="adm-sparkline" id="modKpiPendientesSparkline" viewBox="0 0 68 36" aria-hidden="true">
                <?php foreach ($barrasPend as $barra): ?>
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
</div>