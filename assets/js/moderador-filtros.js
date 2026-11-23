(function () {
    'use strict';

    const PAGE_SIZE = 5;
    const globalSearch = document.getElementById('modGlobalSearch');
    const approvalSearch = document.getElementById('modApprovalSearch');
    const approvalCatFilter = document.getElementById('modApprovalCatFilter');
    const approvalTbody = document.getElementById('modApprovalTbody');
    const approvalCount = document.getElementById('modApprovalCount');
    const paginationEl = document.getElementById('modApprovalPagination');
    const emptyRow = document.getElementById('modApprovalEmptyRow');
    const emptyRowIcon = document.getElementById('modEmptyRowIcon');
    const emptyRowText = document.getElementById('modEmptyRowText');
    const clearBtn = document.getElementById('modClearFiltersBtn');

    let activeSearchEl = approvalSearch || globalSearch;
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
        if (!paginationEl) return;
        paginationEl.innerHTML = '';

        const paginas = Math.max(1, totalPages);
        for (let i = 1; i <= paginas; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `adm-page-btn ${i === currentPage ? 'active' : ''}`.trim();
            btn.textContent = String(i);
            btn.addEventListener('click', () => {
                if (currentPage !== i) {
                    currentPage = i;
                    filtrar(false);
                }
            });
            paginationEl.appendChild(btn);
        }
    }

    function actualizarEstadoVacio(total, coincidencias, hayFiltros) {
        if (!emptyRow) return;

        if (coincidencias > 0) {
            emptyRow.classList.add('d-none');
            return;
        }

        emptyRow.classList.remove('d-none');

        if (total === 0 && !hayFiltros) {
            if (emptyRowIcon) emptyRowIcon.className = 'fa-solid fa-clipboard-check mb-2 d-block';
            if (emptyRowText) emptyRowText.textContent = 'No hay subastas pendientes por validar en este momento.';
            if (clearBtn) clearBtn.classList.add('d-none');
        } else {
            if (emptyRowIcon) emptyRowIcon.className = 'fa-solid fa-magnifying-glass mb-2 d-block';
            if (emptyRowText) emptyRowText.textContent = 'No se encontraron subastas que coincidan con tu búsqueda.';
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
        const totalPages = Math.max(1, Math.ceil(totalMatching / PAGE_SIZE));
        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        const startIdx = (currentPage - 1) * PAGE_SIZE;
        const endIdx = startIdx + PAGE_SIZE;
        let visibleOnPage = 0;

        matchingRows.forEach((row, idx) => {
            const inPage = idx >= startIdx && idx < endIdx;
            row.style.display = inPage ? '' : 'none';
            if (inPage) visibleOnPage++;
        });

        actualizarEstadoVacio(total, totalMatching, hayFiltros);
        renderPaginacion(totalPages);

        if (approvalCount) {
            if (hayFiltros) {
                approvalCount.textContent = `Mostrando ${visibleOnPage} de ${totalMatching} ${totalMatching === 1 ? 'subasta' : 'subastas'}`;
            } else if (totalMatching > PAGE_SIZE) {
                approvalCount.textContent = `Mostrando ${visibleOnPage} de ${total} ${total === 1 ? 'subasta' : 'subastas'}`;
            } else {
                approvalCount.textContent = `Mostrando ${totalMatching} ${totalMatching === 1 ? 'subasta' : 'subastas'}`;
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

    if (globalSearch) {
        globalSearch.addEventListener('input', () => manejarBusqueda(globalSearch, approvalSearch));
        globalSearch.addEventListener('search', () => manejarBusqueda(globalSearch, approvalSearch));
        globalSearch.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                manejarBusqueda(globalSearch, approvalSearch);
                approvalTbody?.closest('.adm-table-card')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else if (e.key === 'Escape') {
                globalSearch.value = '';
                manejarBusqueda(globalSearch, approvalSearch);
            }
        });
    }

    if (approvalCatFilter) {
        approvalCatFilter.addEventListener('change', () => filtrar(true));
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', limpiar);
    }

    document.addEventListener('DOMContentLoaded', () => filtrar(true));

    window.ModeradorFiltros = { filtrar, limpiar };
})();
