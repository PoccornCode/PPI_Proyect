(function () {
    'use strict';

    const baseUrl = window.ADM_BASE_URL || '';
    const endpoint = `${baseUrl}controllers/producto_admin_controller.php`;

    const sectionEl = document.getElementById('sec-productos');
    const searchInput = document.getElementById('admProductSearch');
    const catFilter = document.getElementById('admProductCatFilter');
    const condFilter = document.getElementById('admProductCondFilter');
    const statusFilter = document.getElementById('admProductStatusFilter');
    const pageSizeSelect = document.getElementById('admProductsPageSize');
    const tbody = document.getElementById('admProductsTbody');
    const countEl = document.getElementById('admProductsCount');
    const paginationEl = document.getElementById('admProductsPagination');
    const emptyRow = document.getElementById('admProductsEmptyRow');
    const emptyIcon = document.getElementById('admProductsEmptyIcon');
    const emptyText = document.getElementById('admProductsEmptyText');
    const clearBtn = document.getElementById('admProductsClearFiltersBtn');

    if (!sectionEl || !tbody) return;

    let pageSize = Number(pageSizeSelect?.value) || 5;
    let currentPage = 1;
    let recargando = false;

    function normalizarTexto(texto) {
        return String(texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function escHtml(valor) {
        return String(valor ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function obtenerFilas() {
        return Array.from(tbody.querySelectorAll('tr[data-id]'));
    }

    function renderPaginacion(totalPages) {
        if (!paginationEl || !window.Paginacion) return;
        window.Paginacion.render(paginationEl, {
            currentPage,
            totalPages,
            maxVisible: 5,
            onPageChange: (nextPage) => {
                currentPage = nextPage;
                filtrar(false);
            }
        });
    }

    function actualizarEstadoVacio(total, coincidencias, hayFiltros) {
        if (!emptyRow) return;

        if (coincidencias > 0) {
            emptyRow.classList.add('d-none');
            return;
        }

        emptyRow.classList.remove('d-none');

        if (total === 0 && !hayFiltros) {
            if (emptyIcon) emptyIcon.className = 'fa-solid fa-box-open';
            if (emptyText) emptyText.textContent = 'No hay productos sin subasta registrados en este momento.';
            if (clearBtn) clearBtn.classList.add('d-none');
        } else {
            if (emptyIcon) emptyIcon.className = 'fa-solid fa-magnifying-glass';
            if (emptyText) emptyText.textContent = 'Prueba con otro término de búsqueda o cambia los filtros seleccionados.';
            if (clearBtn) clearBtn.classList.remove('d-none');
        }
    }

    function filtrar(resetPage = true) {
        if (resetPage) currentPage = 1;

        const term = normalizarTexto(searchInput?.value || '');
        const tokens = term ? term.split(/\s+/).filter(Boolean) : [];
        const selectedCat = normalizarTexto(catFilter?.value || '');
        const selectedCond = normalizarTexto(condFilter?.value || '');
        const selectedStatus = String(statusFilter?.value || '').trim();
        const hayFiltros = Boolean(term || selectedCat || selectedCond || selectedStatus);

        const rows = obtenerFilas();
        const matchingRows = [];

        rows.forEach((row) => {
            const searchable = normalizarTexto([
                row.dataset.id,
                row.dataset.productoId,
                row.dataset.title,
                row.dataset.vendor,
                row.dataset.category,
                row.dataset.condition
            ].join(' '));

            const rowCat = normalizarTexto(row.dataset.category || '');
            const rowCond = normalizarTexto(row.dataset.condition || '');
            const rowStatus = String(row.dataset.status || '');

            const matchesText = !tokens.length || tokens.every((tk) => searchable.includes(tk));
            const matchesCat = !selectedCat || rowCat === selectedCat;
            const matchesCond = !selectedCond || rowCond === selectedCond;
            const matchesStatus = !selectedStatus || rowStatus === selectedStatus;

            if (matchesText && matchesCat && matchesCond && matchesStatus) {
                matchingRows.push(row);
            } else {
                row.style.display = 'none';
            }
        });

        const totalMatches = matchingRows.length;
        const totalPages = Math.max(1, Math.ceil(totalMatches / pageSize));
        if (currentPage > totalPages) currentPage = totalPages;

        const startIdx = (currentPage - 1) * pageSize;
        const endIdx = startIdx + pageSize;

        matchingRows.forEach((row, idx) => {
            row.style.display = (idx >= startIdx && idx < endIdx) ? '' : 'none';
        });

        if (countEl) {
            if (totalMatches === 0) {
                countEl.textContent = 'Mostrando 0 productos';
            } else {
                const desde = startIdx + 1;
                const hasta = Math.min(endIdx, totalMatches);
                const sufijo = totalMatches === 1 ? 'producto' : 'productos';
                countEl.textContent = `Mostrando ${desde}–${hasta} de ${totalMatches} ${sufijo}`;
            }
        }

        actualizarEstadoVacio(rows.length, totalMatches, hayFiltros);
        renderPaginacion(totalPages);
    }

    function construirFilaHtml(p) {
        const idProducto = Number(p.id_producto) || 0;
        const codigo = escHtml(p.codigo || '');
        const titulo = escHtml(p.nombre || '');
        const vendedor = escHtml(p.vendedor || '');
        const categoria = escHtml(p.categoria || 'General');
        const condicion = escHtml(p.condicion || 'Usado');
        const condicionRaw = escHtml(String(p.condicion || 'usado').toLowerCase().replace(/\s+/g, '_'));
        const estadoRaw = escHtml(String(p.estado_raw ?? '1'));
        const estadoTexto = escHtml(p.estado_texto || 'Disponible');
        const estadoClase = escHtml(p.estado_clase || 'adm-badge--activa');
        const img = escHtml(p.img || '');

        return `
            <tr
                data-id="${codigo}"
                data-producto-id="${idProducto}"
                data-title="${titulo}"
                data-vendor="${vendedor}"
                data-category="${categoria}"
                data-condition="${condicionRaw}"
                data-status="${estadoRaw}">
                <td>
                    <div class="adm-cell-item">
                        <div class="adm-item-thumb">
                            <img src="${img}" alt="${titulo}">
                        </div>
                        <div>
                            <div class="adm-item-name">${titulo}</div>
                            <div class="mod-sub-meta">${codigo} · ${vendedor}</div>
                        </div>
                    </div>
                </td>
                <td>${categoria}</td>
                <td>${condicion}</td>
                <td>
                    <span class="adm-badge ${estadoClase}">${estadoTexto}</span>
                </td>
                <td>
                    <div class="adm-action-group">
                        <button type="button" class="adm-action-btn adm-action-btn--edit" title="Ver detalles"
                            aria-label="Ver detalles de ${titulo}"
                            data-adm-product-action="ver" data-producto-id="${idProducto}">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                        <button type="button" class="adm-action-btn adm-action-btn--edit" title="Editar producto"
                            aria-label="Editar ${titulo}"
                            data-adm-product-action="editar" data-producto-id="${idProducto}">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    async function recargar(enFondo = false) {
        if (recargando) return;
        recargando = true;
        try {
            const url = `${endpoint}?opcion=listar${enFondo ? '&bg=1' : ''}`;
            const resp = await fetch(url, { credentials: 'same-origin' });
            if (!resp.ok) return;
            const data = await resp.json();
            if (!data || !data.estado || !Array.isArray(data.productos)) return;

            obtenerFilas().forEach((tr) => tr.remove());
            const html = data.productos.map(construirFilaHtml).join('');
            if (html && emptyRow) {
                emptyRow.insertAdjacentHTML('beforebegin', html);
            }
            filtrar(false);
        } catch (err) {
            console.error('Error al recargar productos admin:', err);
        } finally {
            recargando = false;
        }
    }

    searchInput?.addEventListener('input', () => filtrar(true));
    catFilter?.addEventListener('change', () => filtrar(true));
    condFilter?.addEventListener('change', () => filtrar(true));
    statusFilter?.addEventListener('change', () => filtrar(true));
    pageSizeSelect?.addEventListener('change', () => {
        pageSize = Number(pageSizeSelect.value) || 5;
        filtrar(true);
    });
    clearBtn?.addEventListener('click', () => {
        if (searchInput) searchInput.value = '';
        if (catFilter) catFilter.value = '';
        if (condFilter) condFilter.value = '';
        if (statusFilter) statusFilter.value = '';
        filtrar(true);
    });

    filtrar(true);

    window.AdminProductos = {
        recargar,
        filtrar
    };
})();
