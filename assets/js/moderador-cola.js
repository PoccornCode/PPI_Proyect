(function () {
    'use strict';

    const baseUrl = window.MOD_BASE_URL || '';
    const endpoint = `${baseUrl}controllers/moderador_controller.php`;

    const approvalTbody = document.getElementById('modApprovalTbody');
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

    const confirmModalEl = document.getElementById('modalModConfirmar');
    const confirmTitle = document.getElementById('modalModConfirmarLabel');
    const confirmMessage = document.getElementById('modConfirmMessage');
    const confirmMotivoWrap = document.getElementById('modConfirmMotivoWrap');
    const confirmMotivoInput = document.getElementById('modConfirmMotivo');
    const confirmSubmitBtn = document.getElementById('modConfirmSubmitBtn');

    let activeDetailRow = null;
    let pendingConfirmRow = null;
    let pendingConfirmDecision = null;
    let galleryImages = [];
    let galleryIndex = 0;

    function escHtml(valor) {
        return String(valor ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function construirFilaPendiente(item) {
        const cantFotos = Number(item.fotos || 0);
        const textoFotos = cantFotos === 1 ? '1 foto subida' : `${cantFotos} fotos subidas`;
        const meta = `${item.categoria || ''} · ${item.vendedor || ''} · ${item.precio || ''}`;
        const specsJson = JSON.stringify(item.specs || []);
        const imgsJson = JSON.stringify(item.imagenes || []);

        const tr = document.createElement('tr');
        tr.dataset.id = item.id || '';
        tr.dataset.subastaId = String(item.id_subasta || 0);
        tr.dataset.title = item.titulo || '';
        tr.dataset.vendor = item.vendedor || '';
        tr.dataset.category = item.categoria || '';
        tr.dataset.status = 'Pendiente';

        tr.innerHTML = `
            <td class="mod-col-eye">
                <button
                    type="button"
                    class="adm-action-btn adm-action-btn--edit"
                    title="Ver detalle de la publicación"
                    data-mod-detail
                    data-detail-id="${escHtml(item.id)}"
                    data-detail-title="${escHtml(item.titulo)}"
                    data-detail-meta="${escHtml(meta)}"
                    data-detail-body="${escHtml(item.detalle)}"
                    data-detail-specs="${escHtml(specsJson)}"
                    data-detail-images="${escHtml(imgsJson)}">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </td>
            <td>
                <div class="adm-cell-item">
                    <div class="adm-item-thumb">
                        <img src="${escHtml(item.img)}" alt="${escHtml(item.titulo)}">
                    </div>
                    <div>
                        <div class="adm-item-name">${escHtml(item.titulo)}</div>
                        <div class="mod-sub-meta">
                            Condición: ${escHtml(item.condicion)} · ${escHtml(textoFotos)}
                        </div>
                    </div>
                </div>
            </td>
            <td>${escHtml(item.categoria)}</td>
            <td>${escHtml(item.vendedor)}</td>
            <td class="fw-semibold">${escHtml(item.precio)}</td>
            <td>
                <span class="adm-badge adm-badge--pendiente mod-row-status">Pendiente</span>
            </td>
            <td>
                <div class="adm-action-group">
                    <button
                        type="button"
                        class="mod-btn-sm mod-btn-approve"
                        data-mod-decision="aprobar"
                        data-subasta-id="${Number(item.id_subasta || 0)}"
                        data-item="${escHtml(item.titulo)}">
                        <i class="fa-solid fa-check"></i> Aprobar
                    </button>
                    <button
                        type="button"
                        class="mod-btn-sm mod-btn-reject"
                        data-mod-decision="rechazar"
                        data-subasta-id="${Number(item.id_subasta || 0)}"
                        data-item="${escHtml(item.titulo)}">
                        <i class="fa-solid fa-xmark"></i> Rechazar
                    </button>
                </div>
            </td>
        `;
        return tr;
    }

    let recargandoPendientes = false;

    async function recargarPendientes() {
        if (!approvalTbody || recargandoPendientes) return;
        // Evita reemplazar filas mientras una animación de aprobación/rechazo está en curso
        if (approvalTbody.querySelector('tr[data-processing="1"]')) return;

        recargandoPendientes = true;
        try {
            const resp = await fetch(`${endpoint}?opcion=pendientes&_t=${Date.now()}`, {
                headers: { Accept: 'application/json' },
                cache: 'no-store'
            });

            const data = await resp.json().catch(() => ({}));
            if (resp.status === 401 || data?.expired || data?.auth_required) {
                window.location.href = data?.redirect || `${baseUrl}views/auth/login.php${data?.expired ? '?reason=inactivity' : ''}`;
                return;
            }
            if (!resp.ok || !data?.estado || !Array.isArray(data.pendientes)) return;

            const emptyRow = document.getElementById('modApprovalEmptyRow');
            approvalTbody.querySelectorAll('tr[data-id]').forEach((tr) => tr.remove());

            const frag = document.createDocumentFragment();
            data.pendientes.forEach((item) => {
                frag.appendChild(construirFilaPendiente(item));
            });

            if (emptyRow) {
                approvalTbody.insertBefore(frag, emptyRow);
            } else {
                approvalTbody.appendChild(frag);
            }

            if (data.kpis && window.ModeradorKpis?.renderKpis) {
                window.ModeradorKpis.renderKpis(data.kpis);
            }
            window.ModeradorFiltros?.filtrar(false);
        } catch (err) {
            console.error('Error al sincronizar cola pendiente:', err);
        } finally {
            recargandoPendientes = false;
        }
    }

    function showToast(tipo, titulo, mensaje) {
        if (typeof mostrarToast === 'function') {
            mostrarToast(tipo, titulo, mensaje, 3500);
        }
    }

    async function procesarDecision(row, decision) {
        if (!row || row.dataset.processing === '1') return;

        const idSubasta = Number(row.dataset.subastaId || 0);
        const itemName = row.dataset.title || 'Publicación';
        if (!idSubasta) return;

        row.dataset.processing = '1';
        const buttons = row.querySelectorAll('[data-mod-decision]');
        buttons.forEach((b) => {
            b.disabled = true;
        });

        try {
            const formData = new FormData();
            formData.append('opcion', 'moderar');
            formData.append('id_subasta', String(idSubasta));
            formData.append('decision', decision);

            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData,
                headers: { Accept: 'application/json' },
            });

            const data = await response.json().catch(() => ({}));

            if (response.status === 401 || data?.expired || data?.auth_required) {
                window.location.href = data?.redirect || `${baseUrl}views/auth/login.php${data?.expired ? '?reason=inactivity' : ''}`;
                return;
            }

            if (!response.ok || !data || !data.estado) {
                showToast('error', 'No se pudo procesar', data?.mensaje || 'Intenta nuevamente.');
                row.dataset.processing = '0';
                buttons.forEach((b) => {
                    b.disabled = false;
                });
                return;
            }

            const badge = row.querySelector('.mod-row-status');
            if (badge) {
                badge.classList.remove('adm-badge--pendiente', 'adm-badge--activa', 'mod-badge-danger');
                if (decision === 'aprobar') {
                    row.dataset.status = 'Activa';
                    badge.classList.add('adm-badge--activa');
                    badge.textContent = 'Activa';
                } else {
                    row.dataset.status = 'Rechazada';
                    badge.classList.add('mod-badge-danger');
                    badge.textContent = 'Rechazada';
                }
            }

            if (decision === 'aprobar') {
                showToast('success', 'Subasta aprobada', `${itemName} ahora está activa.`);
            } else {
                showToast('error', 'Subasta rechazada', `${itemName} fue rechazada.`);
            }

            window.ModeradorAdministrar?.sincronizarEstadoLocal(
                idSubasta,
                decision === 'aprobar' ? 'activa' : 'rechazada'
            );

            if (data.kpis && window.ModeradorKpis?.renderKpis) {
                window.ModeradorKpis.renderKpis(data.kpis);
            } else {
                window.ModeradorKpis?.actualizar();
            }

            setTimeout(() => {
                row.classList.add('mod-row-leaving');
                setTimeout(() => {
                    const visiblesAntes = new Set(
                        Array.from(approvalTbody.querySelectorAll('tr[data-id]'))
                            .filter((tr) => tr !== row && tr.style.display !== 'none')
                    );

                    row.remove();
                    window.ModeradorFiltros?.filtrar(false);

                    Array.from(approvalTbody.querySelectorAll('tr[data-id]')).forEach((tr) => {
                        if (tr.style.display !== 'none' && !visiblesAntes.has(tr)) {
                            tr.classList.remove('mod-row-entering');
                            void tr.offsetWidth;
                            tr.classList.add('mod-row-entering');
                            tr.addEventListener(
                                'animationend',
                                () => tr.classList.remove('mod-row-entering'),
                                { once: true }
                            );
                        }
                    });
                }, 620);
            }, 650);
        } catch (err) {
            console.error('Error al moderar subasta:', err);
            showToast('error', 'Error de red', 'No se pudo conectar con el servidor.');
            row.dataset.processing = '0';
            buttons.forEach((b) => {
                b.disabled = false;
            });
        }
    }

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

    function abrirModalDetalle(btn) {
        if (!detailModalEl || typeof bootstrap === 'undefined') return;
        activeDetailRow = btn.closest('tr');
        const esFilaAprobacion = Boolean(activeDetailRow && approvalTbody && approvalTbody.contains(activeDetailRow));
        const isPending = Boolean(esFilaAprobacion && activeDetailRow.dataset.status === 'Pendiente');

        if (modalApprovalActions) {
            modalApprovalActions.classList.toggle('d-none', !isPending);
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
    }

    function abrirModalConfirmar(row, decision) {
        if (!row || row.dataset.processing === '1') return;
        if (!confirmModalEl || typeof bootstrap === 'undefined') {
            procesarDecision(row, decision);
            return;
        }

        pendingConfirmRow = row;
        pendingConfirmDecision = decision;

        const itemName = row.dataset.title || 'esta subasta';
        const esAprobar = decision === 'aprobar';

        if (confirmTitle) {
            confirmTitle.textContent = esAprobar ? 'Confirmar aprobación' : 'Confirmar rechazo';
        }

        if (confirmMessage) {
            confirmMessage.innerHTML = '';
            const textoPrevio = document.createTextNode(
                esAprobar
                    ? '¿Estás seguro de que deseas aprobar la subasta '
                    : '¿Estás seguro de que deseas rechazar la subasta '
            );
            const strong = document.createElement('strong');
            strong.className = 'text-white';
            strong.textContent = itemName;
            const textoFinal = document.createTextNode(
                esAprobar
                    ? '? Al confirmarlo se publicará en el catálogo.'
                    : '?'
            );
            confirmMessage.append(textoPrevio, strong, textoFinal);
        }

        if (confirmMotivoWrap) {
            confirmMotivoWrap.classList.toggle('d-none', esAprobar);
        }
        if (confirmMotivoInput) {
            confirmMotivoInput.value = '';
        }

        if (confirmSubmitBtn) {
            confirmSubmitBtn.className = `mod-btn-sm ${esAprobar ? 'mod-btn-approve' : 'mod-btn-reject'}`;
            confirmSubmitBtn.innerHTML = esAprobar
                ? '<i class="fa-solid fa-check"></i> Sí, aprobar'
                : '<i class="fa-solid fa-xmark"></i> Sí, rechazar';
        }

        bootstrap.Modal.getOrCreateInstance(confirmModalEl).show();
    }

    if (approvalTbody) {
        approvalTbody.addEventListener('click', (e) => {
            const decisionBtn = e.target.closest('[data-mod-decision]');
            if (decisionBtn) {
                const row = decisionBtn.closest('tr');
                abrirModalConfirmar(row, decisionBtn.dataset.modDecision);
            }
        });
    }

    document.addEventListener('click', (e) => {
        const detailBtn = e.target.closest('[data-mod-detail]');
        if (detailBtn) {
            abrirModalDetalle(detailBtn);
        }
    });

    if (galleryPrev) {
        galleryPrev.addEventListener('click', () => renderGalleryImage(galleryIndex - 1));
    }
    if (galleryNext) {
        galleryNext.addEventListener('click', () => renderGalleryImage(galleryIndex + 1));
    }

    function triggerModalDecision(decision) {
        if (!activeDetailRow) return;
        const row = activeDetailRow;
        if (detailModalEl && typeof bootstrap !== 'undefined' && detailModalEl.classList.contains('show')) {
            detailModalEl.addEventListener('hidden.bs.modal', () => {
                abrirModalConfirmar(row, decision);
            }, { once: true });
            bootstrap.Modal.getOrCreateInstance(detailModalEl).hide();
            return;
        }
        abrirModalConfirmar(row, decision);
    }

    if (modalApproveBtn) {
        modalApproveBtn.addEventListener('click', () => triggerModalDecision('aprobar'));
    }
    if (modalRejectBtn) {
        modalRejectBtn.addEventListener('click', () => triggerModalDecision('rechazar'));
    }

    if (confirmSubmitBtn) {
        confirmSubmitBtn.addEventListener('click', () => {
            if (!pendingConfirmRow || !pendingConfirmDecision) return;
            const row = pendingConfirmRow;
            const decision = pendingConfirmDecision;
            pendingConfirmRow = null;
            pendingConfirmDecision = null;
            if (confirmModalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(confirmModalEl).hide();
            }
            procesarDecision(row, decision);
        });
    }

    if (confirmModalEl) {
        confirmModalEl.addEventListener('hidden.bs.modal', () => {
            pendingConfirmRow = null;
            pendingConfirmDecision = null;
        });
    }

    window.ModeradorCola = { procesarDecision, recargarPendientes };
})();
