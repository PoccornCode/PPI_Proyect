(function () {
    'use strict';

    const baseUrl = window.ADM_BASE_URL || '';
    const controllerUrl = `${baseUrl}controllers/admin_controller.php`;

    const btnNueva = document.getElementById('btnNuevaCategoria');
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

    const dropzone = document.getElementById('catImageDropzone');
    const inputImagen = document.getElementById('catFormImagen');
    const previewWrap = document.getElementById('catImagePreviewWrap');
    const previewImg = document.getElementById('catImagePreview');
    const placeholderWrap = document.getElementById('catImagePlaceholder');

    if (!formCrud || !modalCrudEl) return;

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

    function syncModalEstado() {
        const isEdit = Number(inputId?.value || 0) > 0;
        const pub = Boolean(inputEstado?.checked);
        if (estadoTitle) {
            estadoTitle.textContent = pub ? 'Publicar en el sitio público' : 'Guardar como desactivada';
        }
        if (estadoHelp) {
            estadoHelp.textContent = pub
                ? 'Visible en el catálogo, menú y registro de productos.'
                : 'Oculta en el sitio público hasta que decidas publicarla.';
        }
        if (btnGuardarText) {
            btnGuardarText.textContent = isEdit
                ? 'Guardar cambios'
                : (pub ? 'Crear y publicar' : 'Guardar desactivada');
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

    function mostrarPreviewArchivo(file) {
        if (!file) return;
        if (!validarArchivoImagen(file)) {
            if (inputImagen) inputImagen.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = (ev) => setPreview(String(ev.target?.result || ''));
        reader.readAsDataURL(file);
    }

    btnNueva?.addEventListener('click', () => {
        formCrud.reset();
        if (inputImagen) inputImagen.value = '';
        setPreview('');
        if (inputId) inputId.value = '0';
        if (inputEstado) inputEstado.checked = true;
        if (modalTitle) modalTitle.textContent = 'Nueva categoría';
        if (descCounter) descCounter.textContent = '0/255';
        syncModalEstado();
        getCrudModal()?.show();
        setTimeout(() => inputNombre?.focus(), 180);
    });

    dropzone?.addEventListener('click', () => inputImagen?.click());
    dropzone?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            inputImagen?.click();
        }
    });
    dropzone?.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('is-dragover');
    });
    dropzone?.addEventListener('dragleave', () => {
        dropzone.classList.remove('is-dragover');
    });
    dropzone?.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('is-dragover');
        const file = e.dataTransfer?.files?.[0];
        if (!file || !validarArchivoImagen(file)) return;
        if (inputImagen && typeof DataTransfer !== 'undefined') {
            const dt = new DataTransfer();
            dt.items.add(file);
            inputImagen.files = dt.files;
        }
        mostrarPreviewArchivo(file);
    });

    inputImagen?.addEventListener('change', () => {
        const file = inputImagen.files?.[0];
        if (file) mostrarPreviewArchivo(file);
    });

    inputDesc?.addEventListener('input', () => {
        if (descCounter) descCounter.textContent = `${inputDesc.value.length}/255`;
    });

    inputEstado?.addEventListener('change', syncModalEstado);

    formCrud.addEventListener('submit', async (e) => {
        e.preventDefault();
        const idCategoria = Number(inputId?.value || 0);
        if (idCategoria > 0) return;

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
        formData.append('opcion', 'categoria_crear');
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
                notify('warning', data?.mensaje || 'No se pudo crear la categoría.');
                if (data?.campo === 'nombre') inputNombre?.focus();
                if (data?.campo === 'descripcion') inputDesc?.focus();
                return;
            }

            window.AdminCategorias?.insertarTarjeta(data.categoria);
            getCrudModal()?.hide();
            notify('success', data.mensaje || 'Categoría creada correctamente.');
        } catch (err) {
            notify('error', err?.message || 'Error de conexión al crear la categoría.');
        } finally {
            if (btnGuardar) {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = htmlOriginal;
            }
        }
    });
})();
