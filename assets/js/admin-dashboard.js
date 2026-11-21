/**
 * Gavel.net — Panel de Administración (Interacciones de Referencia)
 */
(function () {
    'use strict';

    const VALID_TABS = [
        'inicio',
        'usuarios',
        'subastas',
        'finanzas',
        'categorias',
        'reportes',
        'configuracion'
    ];

    const navButtons = document.querySelectorAll('[data-adm-tab]');
    const sections = document.querySelectorAll('.adm-section');
    const sidebar = document.getElementById('admSidebar');
    const overlay = document.getElementById('admSidebarOverlay');
    const mobileToggle = document.getElementById('admMobileToggle');
    const toastEl = document.getElementById('admToast');
    const toastMsgEl = document.getElementById('admToastMsg');

    let toastTimer = null;

    function showToast(message) {
        if (!toastEl || !toastMsgEl) return;
        toastMsgEl.textContent = message;
        toastEl.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toastEl.classList.remove('show');
        }, 2800);
    }

    function closeMobileSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
    }

    function activateTab(tabName, pushHash = true) {
        const target = VALID_TABS.includes(tabName) ? tabName : 'inicio';

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

        closeMobileSidebar();
    }

    // Bind navigation triggers (sidebar + "Ver todos" links)
    navButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            activateTab(btn.dataset.admTab);
        });
    });

    // Mobile drawer
    if (mobileToggle) {
        mobileToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        });
    }
    if (overlay) {
        overlay.addEventListener('click', closeMobileSidebar);
    }

    // Initial tab from URL hash
    const initialHash = window.location.hash.replace('#', '').toLowerCase();
    activateTab(initialHash || 'inicio', false);

    // =========================================================================
    // GRÁFICO DE LÍNEA — ACTIVIDAD DE LA PLATAFORMA
    // =========================================================================
    const activitySeries = {
        '7d': {
            labels: ['Lun.', 'Mar.', 'Mié.', 'Jue.', 'Vie.', 'Sáb.', 'Dom.'],
            values: [8, 18, 20, 33, 16, 20, 18]
        },
        '30d': {
            labels: ['Sem. 1', 'Sem. 2', 'Sem. 3', 'Sem. 4', 'Sem. 5', 'Sem. 6', 'Sem. 7'],
            values: [14, 22, 19, 28, 31, 25, 34]
        },
        '90d': {
            labels: ['Sep.', 'Oct. 1', 'Oct. 2', 'Nov. 1', 'Nov. 2', 'Dic. 1', 'Dic. 2'],
            values: [11, 15, 24, 21, 29, 27, 32]
        }
    };

    function renderActivityChart(rangeKey) {
        const svg = document.getElementById('admActivitySvg');
        const tooltip = document.getElementById('admActivityTooltip');
        if (!svg) return;

        const data = activitySeries[rangeKey] || activitySeries['7d'];
        const W = 560;
        const H = 220;
        const padL = 38;
        const padR = 20;
        const padT = 16;
        const padB = 34;
        const plotW = W - padL - padR;
        const plotH = H - padT - padB;
        const maxVal = 35;

        const yTicks = [0, 5, 10, 15, 20, 25, 30, 35];
        let html = '';

        // Gridlines & Y labels
        yTicks.forEach((tick) => {
            const y = padT + plotH - (tick / maxVal) * plotH;
            html += `<line x1="${padL}" y1="${y}" x2="${W - padR}" y2="${y}" stroke="#272e21" stroke-width="1" />`;
            html += `<text x="${padL - 10}" y="${y + 4}" text-anchor="end" fill="#9aa392" font-size="11">${tick}</text>`;
        });

        // Points
        const coords = data.values.map((val, idx) => {
            const x = padL + (idx / (data.values.length - 1)) * plotW;
            const y = padT + plotH - (val / maxVal) * plotH;
            return { x, y, val, label: data.labels[idx] };
        });

        // Subtle area gradient
        const areaPath =
            `M ${coords[0].x} ${padT + plotH} ` +
            coords.map((p) => `L ${p.x} ${p.y}`).join(' ') +
            ` L ${coords[coords.length - 1].x} ${padT + plotH} Z`;

        html += `
            <defs>
                <linearGradient id="admLineGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#a7cc3b" stop-opacity="0.22" />
                    <stop offset="100%" stop-color="#a7cc3b" stop-opacity="0" />
                </linearGradient>
            </defs>
            <path d="${areaPath}" fill="url(#admLineGrad)" />
        `;

        // Polyline
        const linePoints = coords.map((p) => `${p.x},${p.y}`).join(' ');
        html += `<polyline points="${linePoints}" fill="none" stroke="#a7cc3b" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round" />`;

        // X labels + interactive dots
        coords.forEach((p) => {
            html += `<text x="${p.x}" y="${H - 8}" text-anchor="middle" fill="#c7cec0" font-size="11.5">${p.label}</text>`;
            html += `
                <circle cx="${p.x}" cy="${p.y}" r="4" fill="#a7cc3b" stroke="#161913" stroke-width="2"
                    class="adm-chart-dot" data-label="${p.label}" data-val="${p.val}" style="cursor:pointer;" />
            `;
        });

        svg.innerHTML = html;

        if (tooltip) {
            svg.querySelectorAll('.adm-chart-dot').forEach((dot) => {
                dot.addEventListener('mouseenter', () => {
                    const rect = svg.getBoundingClientRect();
                    const cx = parseFloat(dot.getAttribute('cx'));
                    const cy = parseFloat(dot.getAttribute('cy'));
                    tooltip.textContent = `${dot.dataset.label}: ${dot.dataset.val} eventos`;
                    tooltip.style.left = `${(cx / W) * rect.width}px`;
                    tooltip.style.top = `${(cy / H) * rect.height}px`;
                    tooltip.style.opacity = '1';
                });
                dot.addEventListener('mouseleave', () => {
                    tooltip.style.opacity = '0';
                });
            });
        }
    }

    const activityRangeSelect = document.getElementById('admActivityRange');
    if (activityRangeSelect) {
        activityRangeSelect.addEventListener('change', (e) => {
            renderActivityChart(e.target.value);
        });
    }
    renderActivityChart('7d');

    // =========================================================================
    // FILTROS EN VIVO — USUARIOS Y SUBASTAS
    // =========================================================================
    const userSearchInput = document.getElementById('admUserSearch');
    const userRoleFilter = document.getElementById('admUserRoleFilter');
    const usersTableBody = document.getElementById('admUsersTbody');
    const usersCountLabel = document.getElementById('admUsersCount');

    function filterUsersTable() {
        if (!usersTableBody) return;
        const q = (userSearchInput?.value || '').trim().toLowerCase();
        const role = (userRoleFilter?.value || '').toLowerCase();
        let visible = 0;

        usersTableBody.querySelectorAll('tr').forEach((row) => {
            const email = (row.dataset.email || '').toLowerCase();
            const rowRole = (row.dataset.role || '').toLowerCase();
            const matchText = !q || email.includes(q);
            const matchRole = !role || rowRole === role;
            const show = matchText && matchRole;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (usersCountLabel) {
            usersCountLabel.textContent = `Mostrando ${visible} usuario${visible === 1 ? '' : 's'}`;
        }
    }

    userSearchInput?.addEventListener('input', filterUsersTable);
    userRoleFilter?.addEventListener('change', filterUsersTable);

    const auctionSearchInput = document.getElementById('admAuctionSearch');
    const auctionCatFilter = document.getElementById('admAuctionCatFilter');
    const auctionsTableBody = document.getElementById('admAuctionsTbody');
    const auctionsCountLabel = document.getElementById('admAuctionsCount');

    function filterAuctionsTable() {
        if (!auctionsTableBody) return;
        const q = (auctionSearchInput?.value || '').trim().toLowerCase();
        const cat = (auctionCatFilter?.value || '').toLowerCase();
        let visible = 0;

        auctionsTableBody.querySelectorAll('tr').forEach((row) => {
            const title = (row.dataset.title || '').toLowerCase();
            const rowCat = (row.dataset.category || '').toLowerCase();
            const matchText = !q || title.includes(q) || rowCat.includes(q);
            const matchCat = !cat || rowCat === cat;
            const show = matchText && matchCat;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (auctionsCountLabel) {
            auctionsCountLabel.textContent = `Mostrando ${visible} subasta${visible === 1 ? '' : 's'}`;
        }
    }

    auctionSearchInput?.addEventListener('input', filterAuctionsTable);
    auctionCatFilter?.addEventListener('change', filterAuctionsTable);

    // Global topbar search jumps to Usuarios or Subastas if user presses Enter
    const globalSearch = document.getElementById('admGlobalSearch');
    if (globalSearch) {
        globalSearch.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const term = globalSearch.value.trim();
                if (!term) return;
                if (term.includes('@')) {
                    activateTab('usuarios');
                    if (userSearchInput) {
                        userSearchInput.value = term;
                        filterUsersTable();
                    }
                } else {
                    activateTab('subastas');
                    if (auctionSearchInput) {
                        auctionSearchInput.value = term;
                        filterAuctionsTable();
                    }
                }
            }
        });
    }

    // Category cards quick-filter jump to Subastas
    document.querySelectorAll('.adm-cat-card[data-category]').forEach((card) => {
        card.addEventListener('click', () => {
            const cat = card.dataset.category;
            activateTab('subastas');
            if (auctionCatFilter) {
                const optionExists = Array.from(auctionCatFilter.options).some(
                    (opt) => opt.value.toLowerCase() === cat.toLowerCase()
                );
                auctionCatFilter.value = optionExists ? cat : '';
                filterAuctionsTable();
            }
            showToast(`Categoría seleccionada: ${cat} (vista de referencia)`);
        });
    });

    // Demo action buttons (edit/delete)
    document.querySelectorAll('[data-adm-demo-action]').forEach((btn) => {
        btn.addEventListener('click', () => {
            showToast(btn.dataset.admDemoAction);
        });
    });

    // Demo form submit inside modals
    document.querySelectorAll('[data-adm-demo-form]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const msg = form.dataset.admDemoForm || 'Acción simulada en modo referencia.';
            const modalEl = form.closest('.modal');
            if (modalEl && window.bootstrap) {
                const instance = window.bootstrap.Modal.getInstance(modalEl);
                instance?.hide();
            }
            form.reset();
            showToast(msg);
        });
    });
})();
