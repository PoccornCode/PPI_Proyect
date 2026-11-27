<?php
$productosLista = $productosAdminInicial ?? [];
$categoriasLista = $categoriasAdmin ?? [];
$totalProductos = count($productosLista);
?>
<section class="adm-section" id="sec-productos">
    <div class="adm-section-head">
        <div class="adm-title-group">
            <span class="adm-title-accent" aria-hidden="true"></span>
            <div>
                <h1>Gestión de productos</h1>
                <p>Consulta y administra los productos registrados que aún no tienen subasta.</p>
            </div>
        </div>
    </div>
    <hr class="adm-section-divider">

    <div class="adm-toolbar">
        <div class="adm-toolbar-left">
            <div class="adm-input-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span class="adm-search-divider" aria-hidden="true"></span>
                <input type="search" id="admProductSearch" placeholder="Buscar producto, vendedor o ID..." aria-label="Buscar producto">
            </div>
            <select class="adm-select-filter" id="admProductCatFilter" aria-label="Filtrar por categoría">
                <option value="">Todas las categorías</option>
                <?php foreach ($categoriasLista as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['nombre']) ?>">
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select class="adm-select-filter" id="admProductCondFilter" aria-label="Filtrar por condición">
                <option value="">Todas las condiciones</option>
                <option value="nuevo">Nuevo</option>
                <option value="como_nuevo">Como nuevo</option>
                <option value="usado">Usado</option>
            </select>
            <select class="adm-select-filter" id="admProductStatusFilter" aria-label="Filtrar por estado">
                <option value="">Todos los estados</option>
                <option value="1">Disponible</option>
                <option value="0">Inactivo</option>
            </select>
        </div>
    </div>

    <div class="adm-table-card">
        <div class="adm-table-responsive">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th><i class="fa-solid fa-box-open"></i> Producto</th>
                        <th>Categoría</th>
                        <th>Condición</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="admProductsTbody">
                    <?php foreach ($productosLista as $p): ?>
                        <?php
                        $estadoRaw = (string)($p['estado_raw'] ?? '1');
                        $titulo = (string)($p['nombre'] ?? '');
                        $vendedor = (string)($p['vendedor'] ?? '');
                        $categoria = (string)($p['categoria'] ?? 'General');
                        $condicion = (string)($p['condicion'] ?? 'Usado');
                        $condicionRaw = strtolower(str_replace(' ', '_', $condicion));
                        ?>
                        <tr
                            data-id="<?= htmlspecialchars((string)($p['codigo'] ?? '')) ?>"
                            data-producto-id="<?= (int)($p['id_producto'] ?? 0) ?>"
                            data-title="<?= htmlspecialchars($titulo) ?>"
                            data-vendor="<?= htmlspecialchars($vendedor) ?>"
                            data-category="<?= htmlspecialchars($categoria) ?>"
                            data-condition="<?= htmlspecialchars($condicionRaw) ?>"
                            data-status="<?= htmlspecialchars($estadoRaw) ?>">
                            <td>
                                <div class="adm-cell-item">
                                    <div class="adm-item-thumb">
                                        <img src="<?= htmlspecialchars((string)($p['img'] ?? '')) ?>" alt="<?= htmlspecialchars($titulo) ?>">
                                    </div>
                                    <div>
                                        <div class="adm-item-name"><?= htmlspecialchars($titulo) ?></div>
                                        <div class="mod-sub-meta">
                                            <?= htmlspecialchars((string)($p['codigo'] ?? '')) ?> · <?= htmlspecialchars($vendedor) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($categoria) ?></td>
                            <td><?= htmlspecialchars($condicion) ?></td>
                            <td>
                                <span class="adm-badge <?= htmlspecialchars((string)($p['estado_clase'] ?? 'adm-badge--activa')) ?>">
                                    <?= htmlspecialchars((string)($p['estado_texto'] ?? 'Disponible')) ?>
                                </span>
                            </td>
                            <td>
                                <div class="adm-action-group">
                                    <button type="button" class="adm-action-btn adm-action-btn--edit" title="Ver detalles"
                                        aria-label="Ver detalles de <?= htmlspecialchars($titulo) ?>"
                                        data-adm-product-action="ver" data-producto-id="<?= (int)($p['id_producto'] ?? 0) ?>">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                    <button type="button" class="adm-action-btn adm-action-btn--edit" title="Editar producto"
                                        aria-label="Editar <?= htmlspecialchars($titulo) ?>"
                                        data-adm-product-action="editar" data-producto-id="<?= (int)($p['id_producto'] ?? 0) ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="admProductsEmptyRow" class="<?= $totalProductos === 0 ? '' : 'd-none' ?>">
                        <td colspan="5" class="text-center py-4">
                            <div class="adm-cat-empty-icon mx-auto"><i class="fa-solid fa-box-open" id="admProductsEmptyIcon" aria-hidden="true"></i></div>
                            <h3 class="h6 text-white fw-bold mb-1">No se encontraron productos</h3>
                            <p class="small mb-3" style="color:#9aa392;" id="admProductsEmptyText">
                                <?= $totalProductos === 0 ? 'No hay productos sin subasta registrados en este momento.' : 'Prueba con otro término de búsqueda o cambia los filtros seleccionados.' ?>
                            </p>
                            <button type="button" class="mod-btn-sm mod-btn-cancel <?= $totalProductos === 0 ? 'd-none' : '' ?>" id="admProductsClearFiltersBtn">
                                <i class="fa-solid fa-rotate-left"></i> Limpiar filtros
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mod-table-footer">
            <div class="mod-table-footer-info">
                <span class="adm-table-count" id="admProductsCount">
                    Mostrando <?= $totalProductos ?> <?= $totalProductos === 1 ? 'producto' : 'productos' ?>
                </span>
            </div>
            <div class="mod-table-footer-controls">
                <label class="mod-page-size-wrap" for="admProductsPageSize">
                    <span>Por página</span>
                    <select class="mod-page-size-select" id="admProductsPageSize" aria-label="Productos por página">
                        <option value="5" selected>5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </label>
                <nav class="mod-pagination" id="admProductsPagination" aria-label="Paginación de productos"></nav>
            </div>
        </div>
    </div>
</section>
