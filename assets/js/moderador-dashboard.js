(function () {
    'use strict';

    const VALID_TABS = ['aprobar', 'administrar'];

    const navButtons = document.querySelectorAll('[data-mod-tab]');
    const sections = document.querySelectorAll('.adm-section');
    const sidebar = document.getElementById('admSidebar');
    const overlay = document.getElementById('admSidebarOverlay');
    const mobileToggle = document.getElementById('admMobileToggle');
    const collapseBtn = document.getElementById('modSidebarCollapseBtn');

    function applySidebarCollapsed(collapsed) {
        document.body.classList.toggle('mod-sidebar-collapsed', collapsed);
        if (collapseBtn) {
            collapseBtn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            const label = collapsed ? 'Expandir menú' : 'Contraer menú';
            collapseBtn.title = label;
            collapseBtn.setAttribute('aria-label', label);
        }
    }

    if (collapseBtn) {
        const savedCollapsed = localStorage.getItem('mod_sidebar_collapsed') === '1';
        applySidebarCollapsed(savedCollapsed);

        collapseBtn.addEventListener('click', () => {
            const next = !document.body.classList.contains('mod-sidebar-collapsed');
            applySidebarCollapsed(next);
            localStorage.setItem('mod_sidebar_collapsed', next ? '1' : '0');
        });
    }

    function closeMobileSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
    }

    function activateTab(tabName, pushHash = true) {
        const requested = VALID_TABS.includes(tabName) ? tabName : 'aprobar';
        const target = document.getElementById(`sec-${requested}`) ? requested : 'aprobar';

        navButtons.forEach((btn) => {
            if (btn.classList.contains('adm-nav-btn')) {
                const isActive = btn.dataset.modTab === target;
                btn.classList.toggle('active', isActive);
                btn.setAttribute('aria-current', isActive ? 'page' : 'false');
            }
        });

        sections.forEach((sec) => {
            sec.classList.toggle('active', sec.id === `sec-${target}`);
        });

        const globalSearch = document.getElementById('modGlobalSearch');
        if (globalSearch) {
            const ph = target === 'administrar'
                ? 'Buscar subasta, vendedor o ID...'
                : 'Buscar subastas pendientes...';
            globalSearch.placeholder = ph;
            globalSearch.setAttribute('aria-label', ph);
            if (globalSearch.value.trim() !== '') {
                globalSearch.dispatchEvent(new Event('input'));
            }
        }

        if (pushHash && window.location.hash !== `#${target}`) {
            history.replaceState(null, '', `#${target}`);
        }

        closeMobileSidebar();
    }

    navButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            activateTab(btn.dataset.modTab);
        });
    });

    if (mobileToggle) {
        mobileToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        });
    }
    if (overlay) {
        overlay.addEventListener('click', closeMobileSidebar);
    }

    const initialHash = window.location.hash.replace('#', '').toLowerCase();
    activateTab(initialHash || 'aprobar', false);
})();
