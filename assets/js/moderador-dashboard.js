/**
 * Gavel.net — Panel de Moderador (Aprobar subastas y Reportes)
 */
(function () {
    'use strict';

    const VALID_TABS = ['aprobar', 'reportes'];

    const navButtons = document.querySelectorAll('[data-mod-tab]');
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
        const target = VALID_TABS.includes(tabName) ? tabName : 'aprobar';

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

    // =========================================================================
    // FILTROS EN VIVO — APROBAR SUBASTAS Y REPORTES
    // =========================================================================
    const approvalSearch = document.getElementById('modApprovalSearch');
    const approvalCatFilter = document.getElementById('modApprovalCatFilter');
    const approvalTbody = document.getElementById('modApprovalTbody');
    const approvalCount = document.getElementById('modApprovalCount');

    function filterApprovals() {
        if (!approvalTbody) return;
        const term = (approvalSearch?.value || '').trim().toLowerCase();
        const cat = approvalCatFilter?.value || '';
        let visible = 0;

        approvalTbody.querySelectorAll('tr').forEach((row) => {
            const title = (row.dataset.title || '').toLowerCase();
            const vendor = (row.dataset.vendor || '').toLowerCase();
            const rowCat = row.dataset.category || '';
            const match = (!term || title.includes(term) || vendor.includes(term)) && (!cat || rowCat === cat);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        if (approvalCount) {
            approvalCount.textContent = `Mostrando ${visible} ${visible === 1 ? 'publicación' : 'publicaciones'}`;
        }
    }

    if (approvalSearch) approvalSearch.addEventListener('input', filterApprovals);
    if (approvalCatFilter) approvalCatFilter.addEventListener('change', filterApprovals);

    const reportSearch = document.getElementById('modReportSearch');
    const reportTypeFilter = document.getElementById('modReportTypeFilter');
    const reportsTbody = document.getElementById('modReportsTbody');
    const reportsCount = document.getElementById('modReportsCount');

    function filterReports() {
        if (!reportsTbody) return;
        const term = (reportSearch?.value || '').trim().toLowerCase();
        const type = reportTypeFilter?.value || '';
        let visible = 0;

        reportsTbody.querySelectorAll('tr').forEach((row) => {
            const id = (row.dataset.id || '').toLowerCase();
            const reason = (row.dataset.reason || '').toLowerCase();
            const auction = (row.dataset.auction || '').toLowerCase();
            const rowType = row.dataset.type || '';
            const match = (!term || id.includes(term) || reason.includes(term) || auction.includes(term)) && (!type || rowType === type);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        if (reportsCount) {
            reportsCount.textContent = `Mostrando ${visible} ${visible === 1 ? 'reporte' : 'reportes'}`;
        }
    }

    if (reportSearch) reportSearch.addEventListener('input', filterReports);
    if (reportTypeFilter) reportTypeFilter.addEventListener('change', filterReports);

    // Buscador global de la topbar sincronizado con la pestaña activa
    const globalSearch = document.getElementById('modGlobalSearch');
    if (globalSearch) {
        globalSearch.addEventListener('input', () => {
            const activeSection = document.querySelector('.adm-section.active');
            if (activeSection && activeSection.id === 'sec-reportes') {
                if (reportSearch) {
                    reportSearch.value = globalSearch.value;
                    filterReports();
                }
            } else if (approvalSearch) {
                approvalSearch.value = globalSearch.value;
                filterApprovals();
            }
        });
    }

    // =========================================================================
    // ACCIONES DE MODERACIÓN (DEMO INTERACTIVO)
    // =========================================================================
    const pendingBadge = document.getElementById('modPendingBadge');

    function updatePendingCounter() {
        if (!approvalTbody || !pendingBadge) return;
        const remaining = Array.from(approvalTbody.querySelectorAll('tr')).filter(
            (r) => r.dataset.status === 'Pendiente'
        ).length;
        pendingBadge.innerHTML = `<i class="fa-regular fa-clock me-1"></i> ${remaining} Por validar`;
    }

    document.querySelectorAll('[data-mod-decision]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const row = btn.closest('tr');
            const badge = row?.querySelector('.mod-row-status');
            const decision = btn.dataset.modDecision;
            const itemName = btn.dataset.item || 'Publicación';

            if (row && badge) {
                badge.classList.remove('adm-badge--pendiente', 'adm-badge--activa', 'mod-badge-danger');
                if (decision === 'aprobar') {
                    row.dataset.status = 'Aprobada';
                    badge.classList.add('adm-badge--activa');
                    badge.textContent = 'Aprobada';
                    showToast(`Subasta aprobada: ${itemName} (demo)`);
                } else {
                    row.dataset.status = 'Rechazada';
                    badge.classList.add('mod-badge-danger');
                    badge.textContent = 'Rechazada';
                    showToast(`Subasta rechazada: ${itemName} (demo)`);
                }
                updatePendingCounter();
            }
        });
    });

    document.querySelectorAll('[data-mod-report-action]').forEach((btn) => {
        btn.addEventListener('click', () => {
            showToast(`${btn.dataset.modReportAction} (demo)`);
        });
    });

    // Modal de detalle
    const detailModalEl = document.getElementById('modalModDetalle');
    const detailCode = document.getElementById('modDetailCode');
    const detailTitle = document.getElementById('modDetailTitle');
    const detailMeta = document.getElementById('modDetailMeta');
    const detailBody = document.getElementById('modDetailBody');

    document.querySelectorAll('[data-mod-detail]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (!detailModalEl || typeof bootstrap === 'undefined') return;
            if (detailCode) detailCode.textContent = `Detalle ${btn.dataset.detailId || ''}`;
            if (detailTitle) detailTitle.textContent = btn.dataset.detailTitle || '';
            if (detailMeta) detailMeta.textContent = btn.dataset.detailMeta || '';
            if (detailBody) detailBody.textContent = btn.dataset.detailBody || '';
            bootstrap.Modal.getOrCreateInstance(detailModalEl).show();
        });
    });
})();
