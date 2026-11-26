(function () {
    'use strict';

    const baseUrl = window.ADM_BASE_URL || '';
    const endpoint = `${baseUrl}controllers/admin_controller.php`;

    const modalEl = document.getElementById('modalDetalleSubasta');
    if (!modalEl) return;

    const codeEl = document.getElementById('admAuctionDetailCode');
    const badgeEl = document.getElementById('admAuctionDetailBadge');
    const loadingEl = document.getElementById('admAuctionDetailLoading');
    const errorEl = document.getElementById('admAuctionDetailError');
    const errorTextEl = document.getElementById('admAuctionDetailErrorText');
    const contentEl = document.getElementById('admAuctionDetailContent');

    const galleryWrap = document.getElementById('admAuctionGalleryWrap');
    const galleryMainImg = document.getElementById('admAuctionGalleryMainImg');
    const galleryPrev = document.getElementById('admAuctionGalleryPrev');
    const galleryNext = document.getElementById('admAuctionGalleryNext');
    const galleryCounter = document.getElementById('admAuctionGalleryCounter');
    const galleryThumbs = document.getElementById('admAuctionGalleryThumbs');

    const titleEl = document.getElementById('admAuctionDetailTitle');
    const specsEl = document.getElementById('admAuctionDetailSpecs');
    const descEl = document.getElementById('admAuctionDetailDesc');

    const approvalActionsEl = document.getElementById('admAuctionModalApprovalActions');
    const approveBtn = document.getElementById('admAuctionModalApproveBtn');
    const rejectBtn = document.getElementById('admAuctionModalRejectBtn');

    const confirmModalEl = document.getElementById('modalConfirmarSubasta');
    const confirmTitleEl = document.getElementById('modalConfirmarSubastaLabel');
    const confirmMessageEl = document.getElementById('admAuctionConfirmMessage');
    const confirmMotivoWrapEl = document.getElementById('admAuctionConfirmMotivoWrap');
    const confirmMotivoInputEl = document.getElementById('admAuctionConfirmMotivo');
    const confirmSubmitBtn = document.getElementById('admAuctionConfirmSubmitBtn');

    let galleryImages = [];
    let galleryIndex = 0;
    let solicitudSeq = 0;
    let activeAbortController = null;
    let currentSubasta = null;
    let pendingDecision = null;
    let procesandoDecision = false;

    function notificarToast(tipo, titulo, mensaje) {
        if (typeof window.mostrarToast === 'function') {
            window.mostrarToast(tipo, titulo, mensaje, 3800);
        }
    }

    function mostrarEstado(estado) {
        if (loadingEl) loadingEl.classList.toggle('d-none', estado !== 'loading');
        if (errorEl) errorEl.classList.toggle('d-none', estado !== 'error');
        if (contentEl) contentEl.classList.toggle('d-none', estado !== 'content');
    }

    function renderGalleryImage(index) {
        if (!galleryImages.length || !galleryMainImg) return;
        const total = galleryImages.length;
        galleryIndex = ((index % total) + total) % total;
        galleryMainImg.src = galleryImages[galleryIndex];
        if (galleryCounter) {
            galleryCounter.textContent = `${galleryIndex + 1} / ${total}`;
        }
        if (galleryThumbs) {
            galleryThumbs.querySelectorAll('.mod-gallery-thumb').forEach((thumb, idx) => {
                thumb.classList.toggle('active', idx === galleryIndex);
            });
        }
    }

    function setupGallery(images, titulo) {
        galleryImages = Array.isArray(images) ? images.filter(Boolean) : [];
        galleryIndex = 0;
        if (galleryThumbs) galleryThumbs.innerHTML = '';

        if (!galleryWrap || !galleryImages.length) {
            if (galleryWrap) galleryWrap.classList.add('d-none');
            return;
        }

        galleryWrap.classList.remove('d-none');
        const hasMultiple = galleryImages.length > 1;
        if (galleryPrev) galleryPrev.classList.toggle('d-none', !hasMultiple);
        if (galleryNext) galleryNext.classList.toggle('d-none', !hasMultiple);
        if (galleryThumbs) galleryThumbs.classList.toggle('d-none', !hasMultiple);

        if (galleryThumbs) {
            galleryImages.forEach((src, idx) => {
                const thumbBtn = document.createElement('button');
                thumbBtn.type = 'button';
                thumbBtn.className = 'mod-gallery-thumb';
                thumbBtn.setAttribute('aria-label', `Ver imagen ${idx + 1} de ${titulo || 'artículo'}`);

                const img = document.createElement('img');
                img.src = src;
                img.alt = `${titulo || 'Artículo'} - foto ${idx + 1}`;

                thumbBtn.appendChild(img);
                thumbBtn.addEventListener('click', () => renderGalleryImage(idx));
                galleryThumbs.appendChild(thumbBtn);
            });
        }

        renderGalleryImage(0);
    }

    function renderSpecs(specs) {
        if (!specsEl) return;
        specsEl.innerHTML = '';

        if (!Array.isArray(specs) || !specs.length) {
            specsEl.classList.add('d-none');
            return;
        }

        specs.forEach((item) => {
            const card = document.createElement('div');
            card.className = 'mod-detail-spec';

            const lbl = document.createElement('div');
            lbl.className = 'mod-detail-spec-label';
            lbl.textContent = item.label || '';

            const val = document.createElement('div');
            val.className = 'mod-detail-spec-val';
            val.textContent = item.value || '';

            card.append(lbl, val);
            specsEl.appendChild(card);
        });
        specsEl.classList.remove('d-none');
    }

    function poblarDetalle(subasta) {
        currentSubasta = subasta;
        const codigo = subasta.codigo || `SUB-${String(subasta.id_subasta || 0).padStart(3, '0')}`;
        if (codeEl) codeEl.textContent = `Detalle ${codigo}`;

        if (badgeEl) {
            badgeEl.className = `adm-badge ${subasta.estado_clase || 'adm-badge--pendiente'}`;
            badgeEl.textContent = subasta.estado_texto || 'Pendiente';
        }

        if (approvalActionsEl) {
            const esPendiente = String(subasta.estado_raw || '').toLowerCase() === 'pendiente';
            approvalActionsEl.classList.toggle('d-none', !esPendiente);
        }

        if (titleEl) titleEl.textContent = subasta.titulo || 'Sin título';
        if (descEl) descEl.textContent = subasta.descripcion || 'Sin descripción registrada.';

        renderSpecs(subasta.specs || []);
        setupGallery(subasta.imagenes || [], subasta.titulo || '');
        mostrarEstado('content');
    }

    async function abrirDetalleSubasta(idSubasta, codigoFallback) {
        if (!idSubasta || typeof bootstrap === 'undefined') return;

        const seq = ++solicitudSeq;
        currentSubasta = null;
        if (activeAbortController) {
            activeAbortController.abort();
        }
        activeAbortController = new AbortController();

        if (codeEl) {
            codeEl.textContent = codigoFallback ? `Detalle ${codigoFallback}` : 'Detalle';
        }
        if (badgeEl) {
            badgeEl.classList.add('d-none');
        }
        if (approvalActionsEl) {
            approvalActionsEl.classList.add('d-none');
        }
        mostrarEstado('loading');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();

        try {
            const params = new URLSearchParams({
                opcion: 'subasta_detalle',
                id_subasta: String(idSubasta),
                _t: String(Date.now())
            });

            const resp = await fetch(`${endpoint}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
                signal: activeAbortController.signal
            });

            const data = await resp.json().catch(() => ({}));
            if (seq !== solicitudSeq) return;

            if (resp.status === 401 || data?.expired || data?.auth_required) {
                window.location.href = data?.redirect || `${baseUrl}views/auth/login.php${data?.expired ? '?reason=inactivity' : ''}`;
                return;
            }

            if (!resp.ok || !data?.estado || !data?.subasta) {
                throw new Error(data?.mensaje || 'No se pudo obtener el detalle de la subasta.');
            }

            poblarDetalle(data.subasta);
        } catch (err) {
            if (err?.name === 'AbortError' || seq !== solicitudSeq) return;
            if (errorTextEl) {
                errorTextEl.textContent = err?.message || 'No se pudo cargar la información de la subasta.';
            }
            mostrarEstado('error');
        }
    }

    document.addEventListener('click', (e) => {
        const verBtn = e.target.closest('[data-adm-auction-action="ver"]');
        if (!verBtn) return;

        const idSubasta = Number(verBtn.dataset.subastaId || 0);
        const row = verBtn.closest('tr');
        const codigoFallback = row?.dataset.id || '';
        abrirDetalleSubasta(idSubasta, codigoFallback);
    });

    galleryPrev?.addEventListener('click', () => renderGalleryImage(galleryIndex - 1));
    galleryNext?.addEventListener('click', () => renderGalleryImage(galleryIndex + 1));

    modalEl.addEventListener('keydown', (e) => {
        if (galleryImages.length <= 1) return;
        if (e.key === 'ArrowLeft') {
            renderGalleryImage(galleryIndex - 1);
        } else if (e.key === 'ArrowRight') {
            renderGalleryImage(galleryIndex + 1);
        }
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        if (activeAbortController) {
            activeAbortController.abort();
            activeAbortController = null;
        }
    });

    function abrirModalConfirmar(decision) {
        if (!currentSubasta || procesandoDecision || typeof bootstrap === 'undefined') return;
        if (!confirmModalEl) return;

        pendingDecision = decision;
        const esAprobar = decision === 'aprobar';
        const titulo = currentSubasta.titulo || 'esta subasta';

        if (confirmTitleEl) {
            confirmTitleEl.textContent = esAprobar ? 'Confirmar aprobación' : 'Confirmar rechazo';
        }

        if (confirmMessageEl) {
            confirmMessageEl.innerHTML = '';
            const textoPrevio = document.createTextNode(
                esAprobar
                    ? '¿Estás seguro de que deseas aprobar la subasta '
                    : '¿Estás seguro de que deseas rechazar la subasta '
            );
            const strong = document.createElement('strong');
            strong.className = 'text-white';
            strong.textContent = titulo;
            const textoFinal = document.createTextNode(
                esAprobar ? '? Al confirmarlo se publicará en el catálogo.' : '?'
            );
            confirmMessageEl.append(textoPrevio, strong, textoFinal);
        }

        if (confirmMotivoWrapEl) {
            confirmMotivoWrapEl.classList.toggle('d-none', esAprobar);
        }
        if (confirmMotivoInputEl) {
            confirmMotivoInputEl.value = '';
        }

        if (confirmSubmitBtn) {
            confirmSubmitBtn.disabled = false;
            confirmSubmitBtn.className = `mod-btn-sm ${esAprobar ? 'mod-btn-approve' : 'mod-btn-reject'}`;
            confirmSubmitBtn.innerHTML = esAprobar
                ? '<i class="fa-solid fa-check"></i> Sí, aprobar'
                : '<i class="fa-solid fa-xmark"></i> Sí, rechazar';
        }

        bootstrap.Modal.getOrCreateInstance(confirmModalEl).show();
    }

    function solicitarDecision(decision) {
        if (!currentSubasta || typeof bootstrap === 'undefined') return;
        if (modalEl.classList.contains('show')) {
            modalEl.addEventListener('hidden.bs.modal', () => {
                abrirModalConfirmar(decision);
            }, { once: true });
            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        } else {
            abrirModalConfirmar(decision);
        }
    }

    async function enviarDecision() {
        if (!currentSubasta || !pendingDecision || procesandoDecision) return;

        procesandoDecision = true;
        if (confirmSubmitBtn) confirmSubmitBtn.disabled = true;

        const formData = new FormData();
        formData.append('opcion', 'subasta_moderar');
        formData.append('id_subasta', String(currentSubasta.id_subasta));
        formData.append('decision', pendingDecision);
        if (pendingDecision === 'rechazar' && confirmMotivoInputEl) {
            formData.append('motivo', confirmMotivoInputEl.value.trim());
        }

        try {
            const resp = await fetch(endpoint, {
                method: 'POST',
                body: formData,
                headers: { Accept: 'application/json' },
                cache: 'no-store'
            });

            const data = await resp.json().catch(() => ({}));
            if (resp.status === 401 || data?.expired || data?.auth_required) {
                window.location.href = data?.redirect || `${baseUrl}views/auth/login.php${data?.expired ? '?reason=inactivity' : ''}`;
                return;
            }

            if (!resp.ok || !data?.estado) {
                notificarToast('error', 'No se pudo procesar', data?.mensaje || 'Intenta de nuevo.');
                return;
            }

            if (confirmModalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(confirmModalEl).hide();
            }

            const esAprobar = pendingDecision === 'aprobar';
            notificarToast(
                esAprobar ? 'success' : 'warning',
                esAprobar ? 'Subasta aprobada' : 'Subasta rechazada',
                data.mensaje || (esAprobar ? 'La subasta se publicó en el catálogo.' : 'La subasta fue rechazada.')
            );

            await window.AdminSubastas?.recargar?.(false);
        } catch (err) {
            console.error('Error al moderar subasta:', err);
            notificarToast('error', 'Error de red', 'No se pudo conectar con el servidor.');
        } finally {
            procesandoDecision = false;
            if (confirmSubmitBtn) confirmSubmitBtn.disabled = false;
        }
    }

    approveBtn?.addEventListener('click', () => solicitarDecision('aprobar'));
    rejectBtn?.addEventListener('click', () => solicitarDecision('rechazar'));
    confirmSubmitBtn?.addEventListener('click', enviarDecision);
})();
