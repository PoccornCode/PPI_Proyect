<?php
$distribucion = $graficosInicio['distribucion'] ?? ['total' => 0, 'items' => []];
$distTotal = max(1, (int)($distribucion['total'] ?? 0));
$distItems = $distribucion['items'] ?? [];
$circunferencia = 402.12;
$offsetAcumulado = 0.0;
?>
<div class="adm-charts-row">
    <div class="adm-panel">
        <div class="adm-panel-header">
            <h2 class="adm-panel-title">
                <i class="fa-solid fa-chart-line"></i> Actividad de la plataforma
            </h2>
            <label class="adm-pill-select adm-pill-select--sm">
                <i class="fa-regular fa-calendar"></i>
                <select id="admActivityRange" aria-label="Periodo de actividad">
                    <option value="7d" selected>Últimos 7 días</option>
                    <option value="30d">Últimos 30 días</option>
                    <option value="90d">Últimos 90 días</option>
                </select>
            </label>
        </div>
        <div class="adm-panel-body">
            <div class="adm-chart-wrap">
                <svg id="admActivitySvg" class="adm-svg-chart" viewBox="0 0 560 220" role="img" aria-label="Gráfico de actividad de la plataforma"></svg>
                <div id="admActivityTooltip" class="adm-chart-tooltip"></div>
            </div>
        </div>
    </div>

    <div class="adm-panel">
        <div class="adm-panel-header">
            <h2 class="adm-panel-title">
                <i class="fa-solid fa-chart-pie"></i> Distribución de subastas
            </h2>
        </div>
        <div class="adm-panel-body">
            <div class="adm-donut-layout">
                <svg id="admDistribucionSvg" width="168" height="168" viewBox="0 0 180 180" role="img" aria-label="Distribución de subastas por estado">
                    <circle cx="90" cy="90" r="64" fill="none" stroke="#252c1f" stroke-width="26" />
                    <?php if ((int)($distribucion['total'] ?? 0) > 0): ?>
                        <?php foreach ($distItems as $item): ?>
                            <?php
                            $count = (int)($item['count'] ?? 0);
                            if ($count <= 0) {
                                continue;
                            }
                            $segmento = ($count / $distTotal) * $circunferencia;
                            $dashoffset = -$offsetAcumulado;
                            $offsetAcumulado += $segmento;
                            ?>
                            <circle cx="90" cy="90" r="64" fill="none"
                                stroke="<?= htmlspecialchars($item['color']) ?>"
                                stroke-width="26"
                                stroke-dasharray="<?= number_format($segmento, 2, '.', '') ?> <?= $circunferencia ?>"
                                stroke-dashoffset="<?= number_format($dashoffset, 2, '.', '') ?>"
                                transform="rotate(-90 90 90)" />
                        <?php endforeach; ?>
                    <?php endif; ?>
                </svg>

                <ul class="adm-donut-legend" id="admDistribucionLegend">
                    <?php foreach ($distItems as $item): ?>
                        <li>
                            <span class="adm-legend-label">
                                <span class="adm-legend-dot" style="background:<?= htmlspecialchars($item['color']) ?>;"></span>
                                <?= htmlspecialchars($item['label']) ?>
                            </span>
                            <strong title="<?= (int)$item['count'] ?> subastas"><?= (int)$item['porcentaje'] ?>%</strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
