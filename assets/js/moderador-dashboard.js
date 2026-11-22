/**
 * Gavel.net — Panel de Moderador (Aprobar subastas)
 */
(function () {
    'use strict';

    const VALID_TABS = ['aprobar'];

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

    function showToast(tipo, titulo, mensaje) {
        if (typeof mostrarToast === 'function') {
            mostrarToast(tipo, titulo, mensaje, 3500);
        }
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

    // Filtros en vivo para aprobar subastas
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

    // Buscador global de la topbar sincronizado con la tabla de aprobaciones
    const globalSearch = document.getElementById('modGlobalSearch');
    if (globalSearch) {
        globalSearch.addEventListener('input', () => {
            if (approvalSearch) {
                approvalSearch.value = globalSearch.value;
                filterApprovals();
            }
        });
    }

    // Acciones de moderación (demo interactivo)
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
                    row.dataset.status = 'Activa';
                    badge.classList.add('adm-badge--activa');
                    badge.textContent = 'Activa';
                    showToast('success', 'Subasta activada', `${itemName} (demo)`);
                } else {
                    row.dataset.status = 'Rechazada';
                    badge.classList.add('mod-badge-danger');
                    badge.textContent = 'Rechazada';
                    showToast('error', 'Subasta rechazada', `${itemName} (demo)`);
                }
                updatePendingCounter();
            }
        });
    });

    // Modal de detalle
    const detailModalEl = document.getElementById('modalModDetalle');
    const detailCode = document.getElementById('modDetailCode');
    const detailTitle = document.getElementById('modDetailTitle');
    const detailMeta = document.getElementById('modDetailMeta');
    const detailSpecs = document.getElementById('modDetailSpecs');
    const detailDescLabel = document.getElementById('modDetailDescLabel');
    const detailBody = document.getElementById('modDetailBody');
    const modalApprovalActions = document.getElementById('modModalApprovalActions');
    const modalApproveBtn = document.getElementById('modModalApproveBtn');
    const modalRejectBtn = document.getElementById('modModalRejectBtn');
    const galleryWrap = document.getElementById('modGalleryWrap');
    const galleryMainImg = document.getElementById('modGalleryMainImg');
    const galleryThumbs = document.getElementById('modGalleryThumbs');
    const galleryCounter = document.getElementById('modGalleryCounter');
    const galleryPrev = document.getElementById('modGalleryPrev');
    const galleryNext = document.getElementById('modGalleryNext');
    let activeDetailRow = null;
    let galleryImages = [];
    let galleryIndex = 0;

    function renderGalleryImage(index) {
        if (!galleryImages.length || !galleryMainImg) return;
        galleryIndex = (index + galleryImages.length) % galleryImages.length;
        galleryMainImg.src = galleryImages[galleryIndex];
        if (galleryCounter) {
            galleryCounter.textContent = `${galleryIndex + 1} / ${galleryImages.length}`;
        }
        if (galleryThumbs) {
            galleryThumbs.querySelectorAll('.mod-gallery-thumb').forEach((thumb, idx) => {
                thumb.classList.toggle('active', idx === galleryIndex);
            });
        }
    }

    function setupGallery(images, title) {
        if (!galleryWrap || !galleryThumbs) return;
        galleryImages = Array.isArray(images) ? images : [];
        galleryIndex = 0;
        galleryThumbs.innerHTML = '';

        if (!galleryImages.length) {
            galleryWrap.classList.add('d-none');
            return;
        }

        galleryWrap.classList.remove('d-none');
        const hasMultiple = galleryImages.length > 1;
        if (galleryPrev) galleryPrev.classList.toggle('d-none', !hasMultiple);
        if (galleryNext) galleryNext.classList.toggle('d-none', !hasMultiple);
        galleryThumbs.classList.toggle('d-none', !hasMultiple);

        galleryImages.forEach((src, idx) => {
            const thumbBtn = document.createElement('button');
            thumbBtn.type = 'button';
            thumbBtn.className = 'mod-gallery-thumb';
            thumbBtn.setAttribute('aria-label', `Ver imagen ${idx + 1} de ${title || 'artículo'}`);
            const img = document.createElement('img');
            img.src = src;
            img.alt = `${title || 'Artículo'} - foto ${idx + 1}`;
            thumbBtn.appendChild(img);
            thumbBtn.addEventListener('click', () => renderGalleryImage(idx));
            galleryThumbs.appendChild(thumbBtn);
        });

        renderGalleryImage(0);
    }

    if (galleryPrev) {
        galleryPrev.addEventListener('click', () => renderGalleryImage(galleryIndex - 1));
    }
    if (galleryNext) {
        galleryNext.addEventListener('click', () => renderGalleryImage(galleryIndex + 1));
    }

    document.querySelectorAll('[data-mod-detail]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (!detailModalEl || typeof bootstrap === 'undefined') return;
            activeDetailRow = btn.closest('tr');
            const isApprovalItem = Boolean(activeDetailRow && activeDetailRow.closest('#modApprovalTbody'));

            if (modalApprovalActions) {
                modalApprovalActions.classList.toggle('d-none', !isApprovalItem);
            }

            let parsedImages = [];
            if (btn.dataset.detailImages) {
                try {
                    parsedImages = JSON.parse(btn.dataset.detailImages);
                } catch (e) {
                    parsedImages = [];
                }
            }

            let parsedSpecs = [];
            if (btn.dataset.detailSpecs) {
                try {
                    parsedSpecs = JSON.parse(btn.dataset.detailSpecs);
                } catch (e) {
                    parsedSpecs = [];
                }
            }

            if (detailSpecs) {
                detailSpecs.innerHTML = '';
                if (Array.isArray(parsedSpecs) && parsedSpecs.length) {
                    parsedSpecs.forEach((item) => {
                        const card = document.createElement('div');
                        card.className = 'mod-detail-spec';
                        const lbl = document.createElement('div');
                        lbl.className = 'mod-detail-spec-label';
                        lbl.textContent = item.label || '';
                        const val = document.createElement('div');
                        val.className = 'mod-detail-spec-val';
                        val.textContent = item.value || '';
                        card.append(lbl, val);
                        detailSpecs.appendChild(card);
                    });
                    detailSpecs.classList.remove('d-none');
                    if (detailMeta) detailMeta.classList.add('d-none');
                } else {
                    detailSpecs.classList.add('d-none');
                    if (detailMeta) detailMeta.classList.remove('d-none');
                }
            }

            if (detailDescLabel) {
                detailDescLabel.textContent = 'Descripción';
            }

            setupGallery(parsedImages, btn.dataset.detailTitle || '');
            if (detailCode) detailCode.textContent = `Detalle ${btn.dataset.detailId || ''}`;
            if (detailTitle) detailTitle.textContent = btn.dataset.detailTitle || '';
            if (detailMeta) detailMeta.textContent = btn.dataset.detailMeta || '';
            if (detailBody) detailBody.textContent = btn.dataset.detailBody || '';
            bootstrap.Modal.getOrCreateInstance(detailModalEl).show();
        });
    });

    function triggerModalDecision(decision) {
        if (!activeDetailRow) return;
        const targetBtn = activeDetailRow.querySelector(`[data-mod-decision="${decision}"]`);
        if (targetBtn) {
            targetBtn.click();
        }
        if (detailModalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(detailModalEl).hide();
        }
    }

    if (modalApproveBtn) {
        modalApproveBtn.addEventListener('click', () => triggerModalDecision('aprobar'));
    }
    if (modalRejectBtn) {
        modalRejectBtn.addEventListener('click', () => triggerModalDecision('rechazar'));
    }
})();
