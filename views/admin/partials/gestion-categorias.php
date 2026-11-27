<!-- Sección: Categorías -->
<section class="adm-section" id="sec-categorias">
    <div class="adm-section-head">
        <div class="adm-title-group">
            <span class="adm-title-accent" aria-hidden="true"></span>
            <div>
                <h1>Categorías</h1>
                <p>Crea, edita, publica o desactiva las categorías visibles en Gavel<span class="brand-inline">.net</span>.</p>
            </div>
        </div>
        <button type="button" class="adm-btn-primary" id="btnNuevaCategoria">
            <i class="fa-solid fa-plus"></i> Nueva categoría
        </button>
    </div>
    <hr class="adm-section-divider">

    <div class="adm-toolbar adm-cat-toolbar">
        <div class="adm-toolbar-left">
            <div class="adm-input-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span class="adm-search-divider" aria-hidden="true"></span>
                <input type="search" id="admCatSearch" placeholder="Buscar categoría por nombre o descripción..." aria-label="Buscar categoría">
            </div>
            <select class="adm-select-filter" id="admCatStatusFilter" aria-label="Filtrar categorías por estado">
                <option value="todas">Todos los estados</option>
                <option value="publicadas">Publicadas</option>
                <option value="desactivadas">Desactivadas</option>
            </select>
        </div>
    </div>

    <div class="adm-cat-grid-v2" id="admCatGrid">
        <?php foreach ($categorias_admin as $cat):
            $esActiva = (int)$cat['estado'] === 1;
            $imgUrl = $base_url . ltrim((string)$cat['imagen'], '/');
            $descTexto = trim((string)$cat['descripcion']) !== ''
                ? (string)$cat['descripcion']
                : 'Sin descripción registrada.';
            $totalSub = (int)$cat['total_subastas'];
        ?>
            <article class="adm-cat-card-v2 <?= $esActiva ? '' : 'is-inactive' ?>"
                data-id="<?= (int)$cat['id_categoria'] ?>"
                data-nombre="<?= htmlspecialchars($cat['nombre']) ?>"
                data-descripcion="<?= htmlspecialchars($descTexto) ?>"
                data-estado="<?= $esActiva ? 'publicadas' : 'desactivadas' ?>">
                <div class="adm-cat-cover">
                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($cat['nombre']) ?>" loading="lazy">
                </div>
                <div class="adm-cat-body">
                    <div class="adm-cat-meta-row">
                        <span class="adm-cat-status <?= $esActiva ? 'is-published' : 'is-draft' ?>">
                            <span class="adm-cat-status-dot"></span>
                            <?= $esActiva ? 'Publicada' : 'Desactivada' ?>
                        </span>
                        <span class="adm-cat-auctions-count">
                            <?= $totalSub ?> <?= $totalSub === 1 ? 'subasta' : 'subastas' ?>
                        </span>
                    </div>
                    <h3 class="adm-cat-title"><?= htmlspecialchars($cat['nombre']) ?></h3>
                    <p class="adm-cat-desc"><?= htmlspecialchars($descTexto) ?></p>
                </div>
                <div class="adm-cat-footer">
                    <button type="button"
                        class="adm-cat-btn-toggle <?= $esActiva ? 'is-deactivate' : 'is-publish' ?>"
                        data-cat-action="toggle"
                        data-id="<?= (int)$cat['id_categoria'] ?>">
                        <i class="fa-solid <?= $esActiva ? 'fa-eye-slash' : 'fa-globe' ?>"></i>
                        <span><?= $esActiva ? 'Desactivar' : 'Publicar' ?></span>
                    </button>
                    <div class="adm-cat-icon-actions">
                        <button type="button"
                            class="adm-cat-icon-btn adm-cat-icon-btn--edit"
                            title="Editar categoría"
                            aria-label="Editar <?= htmlspecialchars($cat['nombre']) ?>"
                            data-cat-action="edit"
                            data-id="<?= (int)$cat['id_categoria'] ?>">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="adm-cat-empty <?= empty($categorias_admin) ? '' : 'd-none' ?>" id="admCatEmpty">
        <div class="adm-cat-empty-icon"><i class="fa-regular fa-folder-open" id="admCatEmptyIcon"></i></div>
        <h3 class="h6 text-white fw-bold mb-1" id="admCatEmptyTitle">No se encontraron categorías</h3>
        <p class="small mb-3" style="color:#9aa392;" id="admCatEmptyText">Prueba con otro término de búsqueda o cambia el filtro de estado.</p>
        <button type="button" class="mod-btn-sm mod-btn-cancel <?= empty($categorias_admin) ? 'd-none' : '' ?>" id="btnCatResetFilters">
            <i class="fa-solid fa-rotate-left"></i> Limpiar filtros
        </button>
    </div>
</section>
