<?php
$subastasPendientes = $subastasPendientes ?? [];
$totalListaPendientes = count($subastasPendientes);
?>
<div class="adm-table-card">
    <div class="adm-table-responsive">
        <table class="adm-table mod-table">
            <thead>
                <tr>
                    <th class="mod-col-eye" aria-label="Ver detalle"></th>
                    <th><i class="fa-solid fa-cube"></i> Artículo</th>
                    <th>Categoría</th>
                    <th>Vendedor</th>
                    <th>Precio base</th>
                    <th>Estado</th>
                    <th>Acción de moderación</th>
                </tr>
            </thead>
            <tbody id="modApprovalTbody">
                <?php foreach ($subastasPendientes as $item): ?>
                    <?php
                    $cantFotos = (int)($item['fotos'] ?? 0);
                    $textoFotos = $cantFotos === 1 ? '1 foto subida' : $cantFotos . ' fotos subidas';
                    ?>
                    <tr
                        data-id="<?= htmlspecialchars($item['id']) ?>"
                        data-subasta-id="<?= (int)$item['id_subasta'] ?>"
                        data-title="<?= htmlspecialchars($item['titulo']) ?>"
                        data-vendor="<?= htmlspecialchars($item['vendedor']) ?>"
                        data-category="<?= htmlspecialchars($item['categoria']) ?>"
                        data-status="Pendiente">
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
                            <span class="adm-badge adm-badge--pendiente mod-row-status">Pendiente</span>
                        </td>
                        <td>
                            <div class="adm-action-group">
                                <button
                                    type="button"
                                    class="mod-btn-sm mod-btn-approve"
                                    data-mod-decision="aprobar"
                                    data-subasta-id="<?= (int)$item['id_subasta'] ?>"
                                    data-item="<?= htmlspecialchars($item['titulo']) ?>">
                                    <i class="fa-solid fa-check"></i> Aprobar
                                </button>
                                <button
                                    type="button"
                                    class="mod-btn-sm mod-btn-reject"
                                    data-mod-decision="rechazar"
                                    data-subasta-id="<?= (int)$item['id_subasta'] ?>"
                                    data-item="<?= htmlspecialchars($item['titulo']) ?>">
                                    <i class="fa-solid fa-xmark"></i> Rechazar
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr id="modApprovalEmptyRow" class="<?= $totalListaPendientes === 0 ? '' : 'd-none' ?>">
                    <td colspan="7" class="text-center py-4" style="color: #9aa392;">
                        <i class="fa-solid <?= $totalListaPendientes === 0 ? 'fa-clipboard-check' : 'fa-magnifying-glass' ?> mb-2 d-block" id="modEmptyRowIcon" style="font-size: 1.25rem; opacity: 0.7;"></i>
                        <span id="modEmptyRowText">
                            <?= $totalListaPendientes === 0 ? 'No hay subastas pendientes por validar en este momento.' : 'No se encontraron subastas que coincidan con tu búsqueda.' ?>
                        </span>
                        <button type="button" class="btn btn-link btn-sm p-0 ms-2 <?= $totalListaPendientes === 0 ? 'd-none' : '' ?>" id="modClearFiltersBtn" style="color: var(--adm-accent); text-decoration: none;">
                            Limpiar filtros
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="mod-table-footer">
        <div class="mod-table-footer-info">
            <span class="adm-table-count" id="modApprovalCount">
                Mostrando <?= $totalListaPendientes ?> <?= $totalListaPendientes === 1 ? 'registro' : 'registros' ?>
            </span>
        </div>
        <div class="mod-table-footer-controls">
            <label class="mod-page-size-wrap" for="modApprovalPageSize">
                <span>Por página</span>
                <select class="mod-page-size-select" id="modApprovalPageSize" aria-label="Registros por página">
                    <option value="4" selected>4</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
            </label>
            <nav class="mod-pagination" id="modApprovalPagination" aria-label="Paginación de subastas pendientes"></nav>
        </div>
    </div>
</div>