(function () {
    'use strict';

    const baseUrl = window.ADM_BASE_URL || '';
    const endpoint = `${baseUrl}controllers/producto_admin_controller.php`;

    const modalEl = document.getElementById('modalDetalleProducto');
    if (!modalEl) return;

    const codeEl = document.getElementById('admProductDetailCode');
    const badgeEl = document.getElementById('admProductDetailBadge');
    const loadingEl = document.getElementById('admProductDetailLoading');
    const errorEl = document.getElementById('admProductDetailError');
    const errorTextEl = document.getElementById('admProductDetailErrorText');
    const contentEl = document.getElementById('admProductDetailContent');

    const galleryWrap = document.getElementById('admProductGalleryWrap');
    const galleryMainImg = document.getElementById('admProductGalleryMainImg');
    const galleryPrev = document.getElementById('admProductGalleryPrev');
    const galleryNext = document.getElementById('admProductGalleryNext');
    const galleryCounter = document.getElementById('admProductGalleryCounter');
    const galleryThumbs = document.getElementById('admProductGalleryThumbs');

    const titleEl = document.getElementById('admProductDetailTitle');
    const specsEl = document.getElementById('admProductDetailSpecs');
    const descEl = document.getElementById('admProductDetailDesc');

    let galleryImages = [];
    let galleryIndex = 0;
    let solicitudSeq = 0;
    let activeAbortController = null;

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
                thumbBtn.setAttribute('aria-label', `Ver imagen ${idx + 1} de ${titulo || 'producto'}`);

                const img = document.createElement('img');
                img.src = src;
                img.alt = `${titulo || 'Producto'} - foto ${idx + 1}`;

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

    function poblarDetalle(producto) {
        const codigo = producto.codigo || `PROD-${String(producto.id_producto || 0).padStart(3, '0')}`;
        if (codeEl) codeEl.textContent = `Detalle ${codigo}`;

        if (badgeEl) {
            badgeEl.className = `adm-badge ${producto.estado_clase || 'adm-badge--activa'}`;
            badgeEl.textContent = producto.estado_texto || 'Disponible';
        }

        if (titleEl) titleEl.textContent = producto.nombre || 'Sin nombre';
        if (descEl) descEl.textContent = producto.descripcion || 'Sin descripción registrada.';

        renderSpecs(producto.specs || []);
        setupGallery(producto.imagenes || [], producto.nombre || '');
        mostrarEstado('content');
    }

    async function abrirDetalleProducto(idProducto, codigoFallback) {
        if (!idProducto) return;

        if (activeAbortController) {
            activeAbortController.abort();
        }
        activeAbortController = new AbortController();
        const seq = ++solicitudSeq;

        if (codeEl) codeEl.textContent = codigoFallback ? `Detalle ${codigoFallback}` : 'Detalle';
        if (badgeEl) badgeEl.classList.add('d-none');
        mostrarEstado('loading');

        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();

        try {
            const url = `${endpoint}?opcion=detalle&id_producto=${encodeURIComponent(idProducto)}`;
            const resp = await fetch(url, {
                credentials: 'same-origin',
                signal: activeAbortController.signal
            });
            const data = await resp.json();

            if (seq !== solicitudSeq) return;

            if (!resp.ok || !data || !data.estado || !data.producto) {
                if (errorTextEl) {
                    errorTextEl.textContent = data?.mensaje || 'No se pudo cargar la información del producto.';
                }
                mostrarEstado('error');
                return;
            }

            poblarDetalle(data.producto);
        } catch (err) {
            if (err && err.name === 'AbortError') return;
            if (seq !== solicitudSeq) return;
            if (errorTextEl) {
                errorTextEl.textContent = 'Ocurrió un error al consultar el detalle del producto.';
            }
            mostrarEstado('error');
        }
    }

    galleryPrev?.addEventListener('click', () => renderGalleryImage(galleryIndex - 1));
    galleryNext?.addEventListener('click', () => renderGalleryImage(galleryIndex + 1));

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-adm-product-action="ver"]');
        if (!btn) return;
        const idProducto = Number(btn.dataset.productoId || 0);
        const row = btn.closest('tr');
        const codigo = row?.dataset.id || '';
        abrirDetalleProducto(idProducto, codigo);
    });

    window.AdminProductoDetalle = {
        abrir: abrirDetalleProducto
    };
})();
