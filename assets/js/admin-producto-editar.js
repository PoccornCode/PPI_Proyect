(function () {
    'use strict';

    const baseUrl = window.ADM_BASE_URL || '';
    const endpoint = `${baseUrl}controllers/producto_admin_controller.php`;

    const modalEl = document.getElementById('modalEditarProducto');
    const formEl = document.getElementById('admProdEditForm');
    if (!modalEl || !formEl) return;

    const subEl = document.getElementById('admProdEditSub');
    const loadingEl = document.getElementById('admProdEditLoading');
    const errorEl = document.getElementById('admProdEditError');
    const errorTextEl = document.getElementById('admProdEditErrorText');
    const fieldsEl = document.getElementById('admProdEditFields');
    const saveBtn = document.getElementById('admProdEditSaveBtn');

    const idInput = document.getElementById('admProdEditId');
    const nombreInput = document.getElementById('admProdEditNombre');
    const catSelect = document.getElementById('admProdEditCategoria');
    const condSelect = document.getElementById('admProdEditCondicion');
    const descInput = document.getElementById('admProdEditDescripcion');
    const estadoCheck = document.getElementById('admProdEditEstado');
    const estadoTitleEl = document.getElementById('admProdEditEstadoTitle');
    const estadoHelpEl = document.getElementById('admProdEditEstadoHelp');

    let guardando = false;

    function notificar(tipo, titulo, mensaje) {
        if (typeof window.mostrarToast === 'function') {
            window.mostrarToast(tipo, titulo, mensaje, 3400);
        }
    }

    function mostrarEstado(estado) {
        if (loadingEl) loadingEl.classList.toggle('d-none', estado !== 'loading');
        if (errorEl) errorEl.classList.toggle('d-none', estado !== 'error');
        if (fieldsEl) fieldsEl.classList.toggle('d-none', estado !== 'form');
        if (saveBtn) saveBtn.disabled = (estado !== 'form');
    }

    function actualizarTextoEstado() {
        const activo = Boolean(estadoCheck?.checked);
        if (estadoTitleEl) {
            estadoTitleEl.textContent = activo ? 'Producto disponible' : 'Producto inactivo';
        }
        if (estadoHelpEl) {
            estadoHelpEl.textContent = activo
                ? 'Visible y habilitado para que el vendedor pueda gestionarlo.'
                : 'Oculto e inhabilitado temporalmente por administración.';
        }
    }

    async function abrirEditarProducto(idProducto, codigoFallback) {
        if (!idProducto) return;

        if (subEl) subEl.textContent = codigoFallback || '';
        mostrarEstado('loading');

        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();

        try {
            const url = `${endpoint}?opcion=detalle&id_producto=${encodeURIComponent(idProducto)}`;
            const resp = await fetch(url, { credentials: 'same-origin' });
            const data = await resp.json();

            if (!resp.ok || !data || !data.estado || !data.producto) {
                if (errorTextEl) {
                    errorTextEl.textContent = data?.mensaje || 'No se pudo cargar el producto.';
                }
                mostrarEstado('error');
                return;
            }

            const p = data.producto;
            if (subEl) subEl.textContent = `${p.codigo || ''} · ${p.vendedor || ''}`;
            if (idInput) idInput.value = String(p.id_producto || 0);
            if (nombreInput) nombreInput.value = p.nombre || '';
            if (catSelect && p.id_categoria) catSelect.value = String(p.id_categoria);
            if (condSelect && p.condicion) condSelect.value = p.condicion;
            if (descInput) {
                const desc = p.descripcion === 'Sin descripción registrada.' ? '' : (p.descripcion || '');
                descInput.value = desc;
            }
            if (estadoCheck) {
                estadoCheck.checked = String(p.estado_raw) === '1';
                actualizarTextoEstado();
            }

            mostrarEstado('form');
        } catch (err) {
            if (errorTextEl) errorTextEl.textContent = 'Error de conexión al cargar el producto.';
            mostrarEstado('error');
        }
    }

    estadoCheck?.addEventListener('change', actualizarTextoEstado);

    formEl.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (guardando) return;

        const nombre = (nombreInput?.value || '').trim();
        if (!nombre) {
            notificar('warning', 'Campos requeridos', 'Ingresa el nombre del producto.');
            nombreInput?.focus();
            return;
        }

        guardando = true;
        if (saveBtn) saveBtn.disabled = true;

        try {
            const fd = new FormData();
            fd.append('opcion', 'actualizar');
            fd.append('id_producto', idInput?.value || '0');
            fd.append('nombre', nombre);
            fd.append('id_categoria', catSelect?.value || '0');
            fd.append('condicion', condSelect?.value || '');
            fd.append('descripcion', (descInput?.value || '').trim());
            fd.append('estado', estadoCheck?.checked ? '1' : '0');

            const resp = await fetch(endpoint, {
                method: 'POST',
                body: fd,
                credentials: 'same-origin'
            });
            const data = await resp.json();

            if (!resp.ok || !data || !data.estado) {
                notificar('error', 'Error', data?.mensaje || 'No se pudo actualizar el producto.');
                return;
            }

            notificar('success', 'Producto actualizado', data.mensaje || 'Cambios guardados correctamente.');
            bootstrap.Modal.getInstance(modalEl)?.hide();
            window.AdminProductos?.recargar();
        } catch (err) {
            notificar('error', 'Error', 'Ocurrió un error al guardar los cambios.');
        } finally {
            guardando = false;
            if (saveBtn) saveBtn.disabled = false;
        }
    });

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-adm-product-action="editar"]');
        if (!btn) return;
        const idProducto = Number(btn.dataset.productoId || 0);
        const row = btn.closest('tr');
        const codigo = row?.dataset.id || '';
        abrirEditarProducto(idProducto, codigo);
    });

    window.AdminProductoEditar = {
        abrir: abrirEditarProducto
    };
})();
