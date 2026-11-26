<?php
$subastasLista = $subastasAdminInicial ?? [];
$categoriasLista = $categoriasAdmin ?? [];
$totalSubastas = count($subastasLista);
?>
<section class="adm-section" id="sec-subastas">
    <div class="adm-section-head">
        <div class="adm-title-group">
            <span class="adm-title-accent" aria-hidden="true"></span>
            <div>
                <h1>Gestión de subastas</h1>
                <p>Consulta y administra todas las subastas publicadas.</p>
            </div>
        </div>
    </div>
    <hr class="adm-section-divider">

    <div class="adm-toolbar">
        <div class="adm-toolbar-left">
            <div class="adm-input-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span class="adm-search-divider" aria-hidden="true"></span>
                <input type="search" id="admAuctionSearch" placeholder="Buscar subasta, vendedor o ID..." aria-label="Buscar subasta">
            </div>
            <select class="adm-select-filter" id="admAuctionCatFilter" aria-label="Filtrar por categoría">
                <option value="">Todas las categorías</option>
                <?php foreach ($categoriasLista as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['nombre']) ?>">
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select class="adm-select-filter" id="admAuctionStatusFilter" aria-label="Filtrar por estado">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="activa">Activa</option>
                <option value="finalizada">Finalizada</option>
                <option value="sin_ofertas">Sin ofertas</option>
                <option value="rechazada">Rechazada</option>
                <option value="cancelada">Cancelada</option>
            </select>
        </div>
        <button type="button" class="adm-btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaSubasta">
            <i class="fa-solid fa-plus"></i> Nueva subasta
        </button>
    </div>

    <div class="adm-table-card">
        <div class="adm-table-responsive">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th><i class="fa-solid fa-cube"></i> Artículo</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Tiempo restante</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="admAuctionsTbody">
                    <?php foreach ($subastasLista as $s): ?>
                        <?php
                        $estadoRaw = strtolower((string)($s['estado_raw'] ?? 'pendiente'));
                        $titulo = (string)($s['titulo'] ?? '');
                        $vendedor = (string)($s['vendedor'] ?? '');
                        $categoria = (string)($s['categoria'] ?? 'General');
                        ?>
                        <tr
                            data-id="<?= htmlspecialchars((string)($s['codigo'] ?? '')) ?>"
                            data-subasta-id="<?= (int)($s['id_subasta'] ?? 0) ?>"
                            data-title="<?= htmlspecialchars($titulo) ?>"
                            data-vendor="<?= htmlspecialchars($vendedor) ?>"
                            data-category="<?= htmlspecialchars($categoria) ?>"
                            data-status="<?= htmlspecialchars($estadoRaw) ?>">
                            <td>
                                <div class="adm-cell-item">
                                    <div class="adm-item-thumb">
                                        <img src="<?= htmlspecialchars((string)($s['img'] ?? '')) ?>" alt="<?= htmlspecialchars($titulo) ?>">
                                    </div>
                                    <div>
                                        <div class="adm-item-name"><?= htmlspecialchars($titulo) ?></div>
                                        <div class="mod-sub-meta">
                                            <?= htmlspecialchars((string)($s['codigo'] ?? '')) ?> · <?= htmlspecialchars($vendedor) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($categoria) ?></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars((string)($s['precio_actual'] ?? '$0.00')) ?></div>
                                <div class="mod-sub-meta">Base: <?= htmlspecialchars((string)($s['precio_base'] ?? '$0.00')) ?></div>
                            </td>
                            <td><?= htmlspecialchars((string)($s['tiempo_restante'] ?? '—')) ?></td>
                            <td>
                                <span class="adm-badge <?= htmlspecialchars((string)($s['estado_clase'] ?? 'adm-badge--pendiente')) ?>">
                                    <?= htmlspecialchars((string)($s['estado_texto'] ?? 'Pendiente')) ?>
                                </span>
                            </td>
                            <td>
                                <div class="adm-action-group">
                                    <button type="button" class="adm-action-btn adm-action-btn--edit" title="Ver detalles"
                                        aria-label="Ver detalles de <?= htmlspecialchars($titulo) ?>"
                                        data-adm-auction-action="ver" data-subasta-id="<?= (int)($s['id_subasta'] ?? 0) ?>">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                    <?php $esFinalizada = ($estadoRaw === 'finalizada'); ?>
                                    <button type="button" class="adm-action-btn adm-action-btn--edit"
                                        title="<?= $esFinalizada ? 'Las subastas finalizadas no se pueden modificar' : 'Cambiar estado' ?>"
                                        aria-label="<?= $esFinalizada ? 'Subasta finalizada' : 'Cambiar estado de ' . htmlspecialchars($titulo) ?>"
                                        <?= $esFinalizada ? 'disabled' : '' ?>
                                        data-adm-auction-action="editar" data-subasta-id="<?= (int)($s['id_subasta'] ?? 0) ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="admAuctionsEmptyRow" class="<?= $totalSubastas === 0 ? '' : 'd-none' ?>">
                        <td colspan="6" class="text-center py-4">
                            <div class="adm-cat-empty-icon mx-auto"><i class="fa-solid fa-gavel" id="admAuctionsEmptyIcon" aria-hidden="true"></i></div>
                            <h3 class="h6 text-white fw-bold mb-1">No se encontraron subastas</h3>
                            <p class="small mb-3" style="color:#9aa392;" id="admAuctionsEmptyText">
                                <?= $totalSubastas === 0 ? 'No hay subastas registradas en este momento.' : 'Prueba con otro término de búsqueda o cambia los filtros seleccionados.' ?>
                            </p>
                            <button type="button" class="mod-btn-sm mod-btn-cancel <?= $totalSubastas === 0 ? 'd-none' : '' ?>" id="admAuctionsClearFiltersBtn">
                                <i class="fa-solid fa-rotate-left"></i> Limpiar filtros
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mod-table-footer">
            <div class="mod-table-footer-info">
                <span class="adm-table-count" id="admAuctionsCount">
                    Mostrando <?= $totalSubastas ?> <?= $totalSubastas === 1 ? 'subasta' : 'subastas' ?>
                </span>
            </div>
            <div class="mod-table-footer-controls">
                <label class="mod-page-size-wrap" for="admAuctionsPageSize">
                    <span>Por página</span>
                    <select class="mod-page-size-select" id="admAuctionsPageSize" aria-label="Subastas por página">
                        <option value="5" selected>5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </label>
                <nav class="mod-pagination" id="admAuctionsPagination" aria-label="Paginación de subastas"></nav>
            </div>
        </div>
    </div>
</section>
