(function () {
    'use strict';

    const globalSearch = document.getElementById('modGlobalSearch');
    const approvalSearch = document.getElementById('modApprovalSearch');
    const approvalCatFilter = document.getElementById('modApprovalCatFilter');
    const pageSizeSelect = document.getElementById('modApprovalPageSize');
    const approvalTbody = document.getElementById('modApprovalTbody');
    const approvalCount = document.getElementById('modApprovalCount');
    const paginationEl = document.getElementById('modApprovalPagination');
    const emptyRow = document.getElementById('modApprovalEmptyRow');
    const emptyRowIcon = document.getElementById('modEmptyRowIcon');
    const emptyRowText = document.getElementById('modEmptyRowText');
    const clearBtn = document.getElementById('modClearFiltersBtn');

    let activeSearchEl = approvalSearch || globalSearch;
    let pageSize = Number(pageSizeSelect?.value) || 4;
    let currentPage = 1;

    function normalizarTexto(texto) {
        return String(texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function obtenerFilasDatos() {
        if (!approvalTbody) return [];
        return Array.from(approvalTbody.querySelectorAll('tr[data-id]'));
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
            if (emptyRowIcon) emptyRowIcon.className = 'fa-solid fa-clipboard-check';
            if (emptyRowText) emptyRowText.textContent = 'No hay subastas pendientes por validar en este momento.';
            if (clearBtn) clearBtn.classList.add('d-none');
        } else {
            if (emptyRowIcon) emptyRowIcon.className = 'fa-solid fa-gavel';
            if (emptyRowText) emptyRowText.textContent = 'Prueba con otro término de búsqueda o cambia los filtros seleccionados.';
            if (clearBtn) clearBtn.classList.remove('d-none');
        }
    }

    function filtrar(resetPage = true) {
        if (!approvalTbody) return;

        if (resetPage) {
            currentPage = 1;
        }

        const rawTerm = activeSearchEl?.value || '';
        const term = normalizarTexto(rawTerm);
        const tokens = term ? term.split(/\s+/).filter(Boolean) : [];
        const selectedCat = normalizarTexto(approvalCatFilter?.value || '');
        const hayFiltros = Boolean(term || selectedCat);

        const rows = obtenerFilasDatos();
        const total = rows.length;
        const matchingRows = [];

        rows.forEach((row) => {
            const searchable = normalizarTexto([
                row.dataset.id,
                row.dataset.title,
                row.dataset.vendor,
                row.dataset.category,
                row.dataset.status,
            ].join(' '));

            const rowCat = normalizarTexto(row.dataset.category || '');
            const matchesText = !tokens.length || tokens.every((tk) => searchable.includes(tk));
            const matchesCat = !selectedCat || rowCat === selectedCat;

            if (matchesText && matchesCat) {
                matchingRows.push(row);
            } else {
                row.style.display = 'none';
            }
        });

        const totalMatching = matchingRows.length;
        const totalPages = Math.max(1, Math.ceil(totalMatching / pageSize));
        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        const startIdx = (currentPage - 1) * pageSize;
        const endIdx = startIdx + pageSize;
        let visibleOnPage = 0;

        matchingRows.forEach((row, idx) => {
            const inPage = idx >= startIdx && idx < endIdx;
            row.style.display = inPage ? '' : 'none';
            if (inPage) visibleOnPage++;
        });

        actualizarEstadoVacio(total, totalMatching, hayFiltros);
        renderPaginacion(totalPages);

        if (approvalCount) {
            if (totalMatching === 0) {
                approvalCount.textContent = '0 registros';
            } else {
                const desde = startIdx + 1;
                const hasta = startIdx + visibleOnPage;
                const etiqueta = totalMatching === 1 ? 'registro' : 'registros';
                approvalCount.textContent = `Mostrando ${desde}–${hasta} de ${totalMatching} ${etiqueta}`;
            }
        }
    }

    function manejarBusqueda(origen, otro) {
        activeSearchEl = origen;
        if (otro && origen.value && otro.value) {
            otro.value = '';
        }
        filtrar(true);
    }

    function limpiar() {
        if (globalSearch) globalSearch.value = '';
        if (approvalSearch) approvalSearch.value = '';
        if (approvalCatFilter) approvalCatFilter.value = '';
        filtrar(true);
    }

    if (approvalSearch) {
        approvalSearch.addEventListener('input', () => manejarBusqueda(approvalSearch, globalSearch));
        approvalSearch.addEventListener('search', () => manejarBusqueda(approvalSearch, globalSearch));
    }

    const approvalSection = document.getElementById('sec-aprobar');

    if (globalSearch) {
        const onGlobalApprovalSearch = () => {
            if (approvalSection && !approvalSection.classList.contains('active')) return;
            manejarBusqueda(globalSearch, approvalSearch);
        };
        globalSearch.addEventListener('input', onGlobalApprovalSearch);
        globalSearch.addEventListener('search', onGlobalApprovalSearch);
        globalSearch.addEventListener('keydown', (e) => {
            if (approvalSection && !approvalSection.classList.contains('active')) return;
            if (e.key === 'Enter') {
                e.preventDefault();
                onGlobalApprovalSearch();
                approvalTbody?.closest('.adm-table-card')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else if (e.key === 'Escape') {
                globalSearch.value = '';
                onGlobalApprovalSearch();
            }
        });
    }

    if (approvalCatFilter) {
        approvalCatFilter.addEventListener('change', () => filtrar(true));
    }

    if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', () => {
            pageSize = Number(pageSizeSelect.value) || 4;
            filtrar(true);
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', limpiar);
    }

    document.addEventListener('DOMContentLoaded', () => filtrar(true));

    window.ModeradorFiltros = { filtrar, limpiar };
})();
