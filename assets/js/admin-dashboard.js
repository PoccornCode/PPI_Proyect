(function () {
    'use strict';

    const VALID_TABS = [
        'inicio',
        'usuarios',
        'productos',
        'subastas',
        'categorias',
        'reportes',
        'configuracion'
    ];

    const ALERTAS_REFRESH_MS = 20000;
    const baseUrl = window.ADM_BASE_URL || '';
    const alertasEndpoint = `${baseUrl}controllers/admin_controller.php?opcion=alertas`;

    const navButtons = document.querySelectorAll('[data-adm-tab]');
    const sections = document.querySelectorAll('.adm-section');
    const sidebar = document.getElementById('admSidebar');
    const overlay = document.getElementById('admSidebarOverlay');
    const mobileToggle = document.getElementById('admMobileToggle');
    const collapseBtn = document.getElementById('admSidebarCollapseBtn');

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
        const savedCollapsed = localStorage.getItem('adm_sidebar_collapsed') === '1';
        applySidebarCollapsed(savedCollapsed);

        collapseBtn.addEventListener('click', () => {
            const next = !document.body.classList.contains('mod-sidebar-collapsed');
            applySidebarCollapsed(next);
            localStorage.setItem('adm_sidebar_collapsed', next ? '1' : '0');
        });
    }

    function closeMobileSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
    }

    function notify(message, type = 'info', title = 'Panel de Administración') {
        if (typeof window.mostrarToast === 'function') {
            window.mostrarToast(type, title, message, 2800);
        }
    }

    function activateTab(tabName, pushHash = true) {
        const requested = VALID_TABS.includes(tabName) ? tabName : 'inicio';
        const target = document.getElementById(`sec-${requested}`) ? requested : 'inicio';

        navButtons.forEach((btn) => {
            if (btn.classList.contains('adm-nav-btn')) {
                const isActive = btn.dataset.admTab === target;
                btn.classList.toggle('active', isActive);
                btn.setAttribute('aria-current', isActive ? 'page' : 'false');
            }
        });

        sections.forEach((sec) => {
            sec.classList.toggle('active', sec.id === `sec-${target}`);
        });

        if (pushHash && window.location.hash !== `#${target}`) {
            history.replaceState(null, '', `#${target}`);
        }

        if (pushHash && target === 'usuarios') {
            window.AdminUsuarios?.recargar();
        }
        if (pushHash && target === 'productos') {
            window.AdminProductos?.recargar();
        }
        if (pushHash && target === 'subastas') {
            window.AdminSubastas?.recargar();
        }

        closeMobileSidebar();
        window.scrollTo(0, 0);
    }

    navButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            activateTab(btn.dataset.admTab);

            const repSubtab = btn.dataset.admRepSubtab;
            if (repSubtab) {
                const subBtn = document.querySelector(`#sec-reportes [data-rep-tab="${repSubtab}"]`);
                subBtn?.click();
            }
        });
    });

    if (mobileToggle) {
        mobileToggle.addEventListener('click', () => {
            sidebar?.classList.toggle('open');
            overlay?.classList.toggle('open');
        });
    }
    if (overlay) {
        overlay.addEventListener('click', closeMobileSidebar);
    }

    window.addEventListener('hashchange', () => {
        const hashTab = window.location.hash.replace('#', '').toLowerCase();
        if (hashTab) {
            activateTab(hashTab, false);
        }
    });

    const initialHash = window.location.hash.replace('#', '').toLowerCase();
    activateTab(initialHash || 'inicio', false);

    function renderAlertasTopbar(alertas) {
        if (!alertas) return;

        const total = Number(alertas.total ?? 0);
        const subastas = Number(alertas.subastas_pendientes ?? 0);
        const contactos = Number(alertas.contactos_pendientes ?? 0);

        const countEl = document.getElementById('admNotifCount');
        const pillEl = document.getElementById('admNotifBadgePill');
        const subastasEl = document.getElementById('admNotifSubastasText');
        const contactosEl = document.getElementById('admNotifContactosText');

        if (countEl) {
            countEl.textContent = String(total);
        }
        if (pillEl) {
            pillEl.textContent = total === 1 ? '1 pendiente' : `${total} pendientes`;
        }
        if (subastasEl) {
            subastasEl.textContent = subastas === 1 ? '1 subasta por validar' : `${subastas} subastas por validar`;
        }
        if (contactosEl) {
            contactosEl.textContent = contactos === 1 ? '1 mensaje de contacto' : `${contactos} mensajes de contacto`;
        }
    }

    // Sincroniza contadores del menú de alertas y detecta expiración de sesión (401)
    async function actualizarAlertas(esFondo = false) {
        try {
            const bgParam = esFondo ? '&bg=1' : '';
            const response = await fetch(`${alertasEndpoint}${bgParam}&_t=${Date.now()}`, {
                headers: { Accept: 'application/json' },
                cache: 'no-store'
            });

            if (response.status === 401) {
                const errData = await response.json().catch(() => ({}));
                window.location.href = errData.redirect || `${baseUrl}views/auth/login.php${errData.expired ? '?reason=inactivity' : ''}`;
                return null;
            }
            if (!response.ok) return null;

            const data = await response.json();
            if (data?.estado && data.alertas) {
                renderAlertasTopbar(data.alertas);
                return data.alertas;
            }
        } catch (err) {
            console.error('Error al actualizar alertas de administración:', err);
        }
        return null;
    }

    document.addEventListener('DOMContentLoaded', () => {
        setInterval(() => {
            if (!document.hidden) {
                actualizarAlertas(true);
            }
        }, ALERTAS_REFRESH_MS);

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                actualizarAlertas();
            }
        });

        window.addEventListener('focus', () => {
            actualizarAlertas();
        });

        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                actualizarAlertas();
            }
        });
    });

    window.AdminDashboard = {
        activateTab,
        notify,
        actualizarAlertas,
        renderAlertasTopbar
    };
})();
