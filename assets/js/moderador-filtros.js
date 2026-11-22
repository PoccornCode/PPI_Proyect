(function () {
    'use strict';

    const globalSearch = document.getElementById('modGlobalSearch');
    const approvalSearch = document.getElementById('modApprovalSearch');
    const approvalCatFilter = document.getElementById('modApprovalCatFilter');
    const approvalTbody = document.getElementById('modApprovalTbody');
    const approvalCount = document.getElementById('modApprovalCount');
    const emptyRow = document.getElementById('modApprovalEmptyRow');
    const clearBtn = document.getElementById('modClearFiltersBtn');

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

    let activeSearchEl = approvalSearch || globalSearch;

    function filtrar() {
        if (!approvalTbody) return;

        const rawTerm = activeSearchEl?.value || '';
        const term = normalizarTexto(rawTerm);
        const tokens = term ? term.split(/\s+/).filter(Boolean) : [];
        const selectedCat = normalizarTexto(approvalCatFilter?.value || '');

        const rows = obtenerFilasDatos();
        const total = rows.length;
        let visible = 0;

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
            const match = matchesText && matchesCat;

            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        if (emptyRow) {
            emptyRow.classList.toggle('d-none', visible > 0 || total === 0);
        }

        if (approvalCount) {
            if (term || selectedCat) {
                approvalCount.textContent = `Mostrando ${visible} de ${total} ${total === 1 ? 'publicación' : 'publicaciones'}`;
            } else {
                approvalCount.textContent = `Mostrando ${visible} ${visible === 1 ? 'publicación' : 'publicaciones'}`;
            }
        }
    }

    function manejarBusqueda(origen, otro) {
        activeSearchEl = origen;
        if (otro && origen.value && otro.value) {
            otro.value = '';
        }
        filtrar();
    }

    function limpiar() {
        if (globalSearch) globalSearch.value = '';
        if (approvalSearch) approvalSearch.value = '';
        if (approvalCatFilter) approvalCatFilter.value = '';
        filtrar();
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
        approvalCatFilter.addEventListener('change', filtrar);
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', limpiar);
    }

    window.ModeradorFiltros = { filtrar, limpiar };
})();
