(function () {
    'use strict';

    const sectionEl = document.getElementById('sec-categorias');
    const gridEl = document.getElementById('admCatGrid');
    const emptyEl = document.getElementById('admCatEmpty');
    const emptyIcon = document.getElementById('admCatEmptyIcon');
    const emptyText = document.getElementById('admCatEmptyText');
    const searchInput = document.getElementById('admCatSearch');
    const statusSelect = document.getElementById('admCatStatusFilter');
    const resetBtn = document.getElementById('btnCatResetFilters');

    if (!sectionEl || !gridEl) return;

    function normalizarTexto(texto) {
        return String(texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function filtrarCategorias() {
        const termino = normalizarTexto(searchInput?.value || '');
        const estadoFiltro = (statusSelect?.value || 'todas').toLowerCase();
        const tarjetas = Array.from(gridEl.querySelectorAll('.adm-cat-card-v2'));
        let visibles = 0;

        tarjetas.forEach((card) => {
            const nombre = normalizarTexto(card.dataset.nombre || '');
            const descripcion = normalizarTexto(card.dataset.descripcion || '');
            const estado = (card.dataset.estado || '').toLowerCase();

            const coincideTexto = termino === '' || nombre.includes(termino) || descripcion.includes(termino);
            const coincideEstado = estadoFiltro === 'todas' || estado === estadoFiltro;
            const mostrar = coincideTexto && coincideEstado;

            card.classList.toggle('d-none', !mostrar);
            if (mostrar) visibles++;
        });

        gridEl.classList.toggle('d-none', visibles === 0);
        if (emptyEl) {
            emptyEl.classList.toggle('d-none', visibles > 0);
            if (visibles === 0) {
                const hayFiltros = termino !== '' || estadoFiltro !== 'todas';
                if (tarjetas.length === 0 && !hayFiltros) {
                    if (emptyIcon) emptyIcon.className = 'fa-regular fa-folder-open';
                    if (emptyText) emptyText.textContent = 'No hay categorías registradas en este momento.';
                    if (resetBtn) resetBtn.classList.add('d-none');
                } else {
                    if (emptyIcon) emptyIcon.className = 'fa-regular fa-folder-open';
                    if (emptyText) emptyText.textContent = 'Prueba con otro término de búsqueda o cambia el filtro de estado.';
                    if (resetBtn) resetBtn.classList.remove('d-none');
                }
            }
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filtrarCategorias);
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', filtrarCategorias);
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (statusSelect) statusSelect.value = 'todas';
            filtrarCategorias();
            searchInput?.focus();
        });
    }

    window.AdminCategorias = {
        filtrar: filtrarCategorias
    };
})();
