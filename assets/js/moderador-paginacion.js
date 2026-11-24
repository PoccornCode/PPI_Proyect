(function () {
    'use strict';

    function calcularVentanaPaginas(currentPage, totalPages, maxVisible) {
        if (totalPages <= maxVisible) {
            return { start: 1, end: totalPages };
        }
        const half = Math.floor(maxVisible / 2);
        let start = Math.max(1, currentPage - half);
        let end = start + maxVisible - 1;
        if (end > totalPages) {
            end = totalPages;
            start = Math.max(1, end - maxVisible + 1);
        }
        return { start, end };
    }

    function crearBoton({ html, label, title, active = false, disabled = false, isNav = false, onClick }) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = [
            'mod-page-btn',
            isNav ? 'mod-page-btn--nav' : '',
            active ? 'active' : ''
        ].filter(Boolean).join(' ');
        btn.innerHTML = html;
        btn.disabled = disabled;
        btn.setAttribute('aria-label', label);
        if (title) btn.title = title;
        if (active) btn.setAttribute('aria-current', 'page');
        if (!disabled && typeof onClick === 'function') {
            btn.addEventListener('click', onClick);
        }
        return btn;
    }

    function render(container, options) {
        if (!container) return;

        const totalPages = Math.max(1, Number(options.totalPages) || 1);
        const currentPage = Math.min(totalPages, Math.max(1, Number(options.currentPage) || 1));
        const maxVisible = Number(options.maxVisible) || 5;
        const onPageChange = options.onPageChange;

        container.innerHTML = '';

        const irA = (pagina) => {
            if (pagina >= 1 && pagina <= totalPages && pagina !== currentPage) {
                onPageChange(pagina);
            }
        };

        const navGroup = document.createElement('div');
        navGroup.className = 'mod-pagination-group';

        navGroup.appendChild(crearBoton({
            html: '<i class="fa-solid fa-angles-left" aria-hidden="true"></i>',
            label: 'Primera página',
            title: 'Primera',
            disabled: currentPage <= 1,
            isNav: true,
            onClick: () => irA(1)
        }));

        navGroup.appendChild(crearBoton({
            html: '<i class="fa-solid fa-angle-left" aria-hidden="true"></i>',
            label: 'Página anterior',
            title: 'Anterior',
            disabled: currentPage <= 1,
            isNav: true,
            onClick: () => irA(currentPage - 1)
        }));

        const { start, end } = calcularVentanaPaginas(currentPage, totalPages, maxVisible);
        for (let i = start; i <= end; i++) {
            navGroup.appendChild(crearBoton({
                html: String(i),
                label: `Página ${i}`,
                title: `Ir a página ${i}`,
                active: i === currentPage,
                onClick: () => irA(i)
            }));
        }

        navGroup.appendChild(crearBoton({
            html: '<i class="fa-solid fa-angle-right" aria-hidden="true"></i>',
            label: 'Página siguiente',
            title: 'Siguiente',
            disabled: currentPage >= totalPages,
            isNav: true,
            onClick: () => irA(currentPage + 1)
        }));

        navGroup.appendChild(crearBoton({
            html: '<i class="fa-solid fa-angles-right" aria-hidden="true"></i>',
            label: 'Última página',
            title: 'Última',
            disabled: currentPage >= totalPages,
            isNav: true,
            onClick: () => irA(totalPages)
        }));

        container.appendChild(navGroup);
    }

    window.ModPaginacion = { render };
})();
