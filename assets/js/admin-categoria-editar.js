(function () {
    'use strict';

    const baseUrl = window.ADM_BASE_URL || '';
    const controllerUrl = `${baseUrl}controllers/admin_controller.php`;

    const gridEl = document.getElementById('admCatGrid');
    const modalCrudEl = document.getElementById('modalCategoriaCrud');
    const formCrud = document.getElementById('formCategoriaCrud');
    const modalTitle = document.getElementById('catModalTitleText');
    const inputId = document.getElementById('catFormId');
    const inputNombre = document.getElementById('catFormNombre');
    const inputDesc = document.getElementById('catFormDescripcion');
    const descCounter = document.getElementById('catDescCounter');
    const inputEstado = document.getElementById('catFormEstado');
    const estadoTitle = document.getElementById('catEstadoTitle');
    const estadoHelp = document.getElementById('catEstadoHelp');
    const btnGuardar = document.getElementById('btnGuardarCategoria');
    const btnGuardarText = document.getElementById('btnGuardarCategoriaText');

    const inputImagen = document.getElementById('catFormImagen');
    const previewWrap = document.getElementById('catImagePreviewWrap');
    const previewImg = document.getElementById('catImagePreview');
    const placeholderWrap = document.getElementById('catImagePlaceholder');

    if (!gridEl || !formCrud || !modalCrudEl) return;

    const notify = (tipo, mensaje) => {
        if (window.AdminCategorias?.notify) {
            window.AdminCategorias.notify(tipo, mensaje);
        } else if (typeof window.mostrarToast === 'function') {
            window.mostrarToast(tipo, 'Categorías', mensaje, 3200);
        }
    };

    const getCrudModal = () => window.bootstrap
        ? window.bootstrap.Modal.getOrCreateInstance(modalCrudEl)
        : null;

    function setPreview(src) {
        if (!previewImg) return;
        if (src) {
            previewImg.src = src;
            previewWrap?.classList.remove('d-none');
            placeholderWrap?.classList.add('d-none');
        } else {
            previewImg.src = '';
            previewWrap?.classList.add('d-none');
            placeholderWrap?.classList.remove('d-none');
        }
    }

    function validarArchivoImagen(file) {
        if (!file) return true;
        const tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
        if (!tiposPermitidos.includes(file.type)) {
            notify('warning', 'Formato no permitido. Selecciona una imagen JPG, PNG o WEBP.');
            return false;
        }
        if (file.size > 5 * 1024 * 1024) {
            notify('warning', 'La imagen supera el máximo permitido de 5 MB.');
            return false;
        }
        return true;
    }

    function abrirModalEdicion(card) {
        if (!card) return;

        const idCategoria = Number(card.dataset.id || 0);
        if (idCategoria <= 0) return;

        const nombre = card.dataset.nombre || '';
        const descRaw = card.dataset.descripcion || '';
        const descripcion = descRaw === 'Sin descripción registrada.' ? '' : descRaw;
        const esActiva = (card.dataset.estado || '') === 'publicadas';
        const imgSrc = card.querySelector('.adm-cat-cover img')?.getAttribute('src') || '';

        formCrud.reset();
        if (inputImagen) inputImagen.value = '';
        if (inputId) inputId.value = String(idCategoria);
        if (inputNombre) inputNombre.value = nombre;
        if (inputDesc) inputDesc.value = descripcion;
        if (descCounter) descCounter.textContent = `${descripcion.length}/255`;
        if (inputEstado) inputEstado.checked = esActiva;
        if (modalTitle) modalTitle.textContent = 'Editar categoría';
        if (estadoTitle) {
            estadoTitle.textContent = esActiva ? 'Publicar en el sitio público' : 'Guardar como desactivada';
        }
        if (estadoHelp) {
            estadoHelp.textContent = esActiva
                ? 'Visible en el catálogo, menú y registro de productos.'
                : 'Oculta en el sitio público hasta que decidas publicarla.';
        }
        if (btnGuardarText) btnGuardarText.textContent = 'Guardar cambios';

        setPreview(imgSrc);
        getCrudModal()?.show();
        setTimeout(() => inputNombre?.focus(), 180);
    }

    gridEl.addEventListener('click', (event) => {
        const editBtn = event.target.closest('[data-cat-action="edit"]');
        if (!editBtn) return;

        const card = editBtn.closest('.adm-cat-card-v2');
        abrirModalEdicion(card);
    });

    formCrud.addEventListener('submit', async (e) => {
        const idCategoria = Number(inputId?.value || 0);
        if (idCategoria <= 0) return;

        e.preventDefault();

        const nombre = (inputNombre?.value || '').trim();
        const descripcion = (inputDesc?.value || '').trim();
        const estado = inputEstado?.checked ? '1' : '0';

        if (!nombre) {
            notify('warning', 'Ingresa el nombre de la categoría.');
            inputNombre?.focus();
            return;
        }

        const archivo = inputImagen?.files?.[0] || null;
        if (archivo && !validarArchivoImagen(archivo)) {
            return;
        }

        const formData = new FormData();
        formData.append('opcion', 'categoria_actualizar');
        formData.append('id_categoria', String(idCategoria));
        formData.append('nombre', nombre);
        formData.append('descripcion', descripcion);
        formData.append('estado', estado);
        if (archivo) {
            formData.append('imagen', archivo);
        }

        const htmlOriginal = btnGuardar ? btnGuardar.innerHTML : '';
        if (btnGuardar) {
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Guardando...</span>';
        }

        try {
            const response = await fetch(controllerUrl, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: formData,
                cache: 'no-store'
            });

            if (response.status === 401) {
                const errData = await response.json().catch(() => ({}));
                window.location.href = errData.redirect || `${baseUrl}views/auth/login.php${errData.expired ? '?reason=inactivity' : ''}`;
                return;
            }

            const data = await response.json().catch(() => null);
            if (!response.ok || !data?.estado || !data.categoria) {
                const tipoToast = data?.bloqueado ? 'warning' : (response.status === 422 ? 'warning' : 'error');
                notify(tipoToast, data?.mensaje || 'No se pudo actualizar la categoría.');
                if (data?.campo === 'nombre') inputNombre?.focus();
                if (data?.campo === 'descripcion') inputDesc?.focus();
                return;
            }

            window.AdminCategorias?.actualizarTarjeta(data.categoria);
            getCrudModal()?.hide();
            notify('success', data.mensaje || 'Categoría actualizada correctamente.');
        } catch (err) {
            notify('error', err?.message || 'Error de conexión al actualizar la categoría.');
        } finally {
            if (btnGuardar) {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = htmlOriginal;
            }
        }
    });
})();
