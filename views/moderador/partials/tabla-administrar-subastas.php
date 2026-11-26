<?php
$todasSubastas = $todasSubastas ?? [];
$totalTodasSubastas = count($todasSubastas);

$mapaBadgesEstado = [
    'pendiente'   => ['clase' => 'adm-badge--pendiente', 'texto' => 'Pendiente'],
    'activa'      => ['clase' => 'adm-badge--activa', 'texto' => 'Activa'],
    'finalizada'  => ['clase' => 'adm-badge--finalizada', 'texto' => 'Finalizada'],
    'sin_ofertas' => ['clase' => 'adm-badge--sin-ofertas', 'texto' => 'Sin ofertas'],
    'rechazada'   => ['clase' => 'adm-badge--rechazada', 'texto' => 'Rechazada'],
    'cancelada'   => ['clase' => 'adm-badge--cancelada', 'texto' => 'Cancelada'],
];
?>
<div class="adm-table-card">
    <div class="adm-table-responsive">
        <table class="adm-table mod-table mod-table--manage">
            <thead>
                <tr>
                    <th class="mod-col-eye" aria-label="Ver detalle"></th>
                    <th><i class="fa-solid fa-cube"></i> Artículo</th>
                    <th>Categoría</th>
                    <th>Vendedor</th>
                    <th>Precio base</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="modManageTbody">
                <?php foreach ($todasSubastas as $item): ?>
                    <?php
                    $cantFotos = (int)($item['fotos'] ?? 0);
                    $textoFotos = $cantFotos === 1 ? '1 foto subida' : $cantFotos . ' fotos subidas';
                    $estadoRaw = strtolower((string)($item['estado_raw'] ?? $item['estado'] ?? 'pendiente'));
                    $badgeInfo = $mapaBadgesEstado[$estadoRaw] ?? ['clase' => 'adm-badge--pendiente', 'texto' => ucfirst($estadoRaw)];
                    ?>
                    <tr
                        data-id="<?= htmlspecialchars($item['id']) ?>"
                        data-subasta-id="<?= (int)$item['id_subasta'] ?>"
                        data-title="<?= htmlspecialchars($item['titulo']) ?>"
                        data-vendor="<?= htmlspecialchars($item['vendedor']) ?>"
                        data-category="<?= htmlspecialchars($item['categoria']) ?>"
                        data-status="<?= htmlspecialchars($estadoRaw) ?>">
                        <td class="mod-col-eye">
                            <button
                                type="button"
                                class="adm-action-btn adm-action-btn--edit"
                                title="Ver detalle de la publicación"
                                data-mod-detail
                                data-detail-id="<?= htmlspecialchars($item['id']) ?>"
                                data-detail-title="<?= htmlspecialchars($item['titulo']) ?>"
                                data-detail-meta="<?= htmlspecialchars($item['categoria'] . ' · ' . $item['vendedor'] . ' · ' . $item['precio']) ?>"
                                data-detail-body="<?= htmlspecialchars($item['detalle']) ?>"
                                data-detail-specs="<?= htmlspecialchars(json_encode($item['specs'], JSON_UNESCAPED_UNICODE)) ?>"
                                data-detail-images="<?= htmlspecialchars(json_encode($item['imagenes'], JSON_UNESCAPED_SLASHES)) ?>">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </td>
                        <td>
                            <div class="adm-cell-item">
                                <div class="adm-item-thumb">
                                    <img src="<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['titulo']) ?>">
                                </div>
                                <div>
                                    <div class="adm-item-name"><?= htmlspecialchars($item['titulo']) ?></div>
                                    <div class="mod-sub-meta">
                                        Condición: <?= htmlspecialchars($item['condicion']) ?> · <?= htmlspecialchars($textoFotos) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($item['categoria']) ?></td>
                        <td><?= htmlspecialchars($item['vendedor']) ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($item['precio']) ?></td>
                        <td>
                            <span class="adm-badge <?= htmlspecialchars($badgeInfo['clase']) ?> mod-manage-badge">
                                <?= htmlspecialchars($badgeInfo['texto']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="adm-action-group">
                                <?php $esFinalizada = ($estadoRaw === 'finalizada'); ?>
                                <button
                                    type="button"
                                    class="adm-action-btn adm-action-btn--edit mod-btn-edit-status"
                                    data-subasta-id="<?= (int)$item['id_subasta'] ?>"
                                    data-item="<?= htmlspecialchars($item['titulo']) ?>"
                                    data-current-status="<?= htmlspecialchars($estadoRaw) ?>"
                                    <?= $esFinalizada ? 'disabled' : '' ?>
                                    title="<?= $esFinalizada ? 'Las subastas finalizadas no se pueden modificar' : 'Cambiar estado' ?>"
                                    aria-label="<?= $esFinalizada ? 'Subasta finalizada' : 'Cambiar estado de ' . htmlspecialchars($item['titulo']) ?>">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr id="modManageEmptyRow" class="<?= $totalTodasSubastas === 0 ? '' : 'd-none' ?>">
                    <td colspan="7" class="text-center py-4" style="color: #9aa392;">
                        <i class="fa-solid <?= $totalTodasSubastas === 0 ? 'fa-box-open' : 'fa-magnifying-glass' ?> mb-2 d-block" id="modManageEmptyIcon" style="font-size: 1.25rem; opacity: 0.7;"></i>
                        <span id="modManageEmptyText">
                            <?= $totalTodasSubastas === 0 ? 'No hay subastas registradas en este momento.' : 'No se encontraron subastas que coincidan con tu búsqueda.' ?>
                        </span>
                        <button type="button" class="btn btn-link btn-sm p-0 ms-2 <?= $totalTodasSubastas === 0 ? 'd-none' : '' ?>" id="modManageClearFiltersBtn" style="color: var(--adm-accent); text-decoration: none;">
                            Limpiar filtros
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="mod-table-footer">
        <div class="mod-table-footer-info">
            <span class="adm-table-count" id="modManageCount">
                Mostrando <?= $totalTodasSubastas ?> <?= $totalTodasSubastas === 1 ? 'subasta' : 'subastas' ?>
            </span>
        </div>
        <div class="mod-table-footer-controls">
            <label class="mod-page-size-wrap" for="modManagePageSize">
                <span>Por página</span>
                <select class="mod-page-size-select" id="modManagePageSize" aria-label="Registros por página">
                    <option value="4" selected>4</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
            </label>
            <nav class="mod-pagination" id="modManagePagination" aria-label="Paginación de subastas"></nav>
        </div>
    </div>
</div>
