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

    const confirmModalEl = document.getElementById('modalConfirmarEditarProducto');
    const confirmIntroEl = document.getElementById('admProdConfirmIntro');
    const confirmListEl = document.getElementById('admProdConfirmList');
    const confirmBackBtn = document.getElementById('admProdConfirmBackBtn');
    const confirmSaveBtn = document.getElementById('admProdConfirmSaveBtn');

    let guardando = false;
    let datosIniciales = null;
    let payloadPendiente = null;

    function escHtml(valor) {
        return String(valor ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function resumirTexto(texto, max = 42) {
        const limpio = String(texto || '').trim();
        if (!limpio) return 'Vacío';
        return limpio.length > max ? `${limpio.slice(0, max)}…` : limpio;
    }

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
            const descInicial = p.descripcion === 'Sin descripción registrada.' ? '' : (p.descripcion || '').trim();
            if (descInput) {
                descInput.value = descInicial;
            }
            const estadoInicial = String(p.estado_raw) === '1' ? '1' : '0';
            if (estadoCheck) {
                estadoCheck.checked = estadoInicial === '1';
                actualizarTextoEstado();
            }

            datosIniciales = {
                nombre: (p.nombre || '').trim(),
                id_categoria: String(catSelect?.value || ''),
                categoria_texto: catSelect?.selectedOptions?.[0]?.textContent?.trim() || p.categoria || '',
                condicion: String(condSelect?.value || ''),
                descripcion: descInicial,
                estado: estadoInicial
            };

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
        const idCategoria = String(catSelect?.value || '');
        const condicion = String(condSelect?.value || '');
        const descripcion = (descInput?.value || '').trim();
        const estado = estadoCheck?.checked ? '1' : '0';

        if (!nombre) {
            notificar('warning', 'Campos requeridos', 'Ingresa el nombre del producto.');
            nombreInput?.focus();
            return;
        }

        if (
            datosIniciales &&
            datosIniciales.nombre === nombre &&
            datosIniciales.id_categoria === idCategoria &&
            datosIniciales.condicion === condicion &&
            datosIniciales.descripcion === descripcion &&
            datosIniciales.estado === estado
        ) {
            notificar('info', 'Sin cambios', 'No se detectaron modificaciones en los datos del producto.');
            return;
        }

        const cambios = [];
        const categoriaTexto = catSelect?.selectedOptions?.[0]?.textContent?.trim() || '';
        if (datosIniciales) {
            if (datosIniciales.nombre !== nombre) {
                cambios.push({ etiqueta: 'Nombre', antes: resumirTexto(datosIniciales.nombre), despues: resumirTexto(nombre) });
            }
            if (datosIniciales.id_categoria !== idCategoria) {
                cambios.push({ etiqueta: 'Categoría', antes: datosIniciales.categoria_texto, despues: categoriaTexto });
            }
            if (datosIniciales.condicion !== condicion) {
                cambios.push({ etiqueta: 'Condición', antes: datosIniciales.condicion, despues: condicion });
            }
            if (datosIniciales.descripcion !== descripcion) {
                cambios.push({ etiqueta: 'Descripción', antes: resumirTexto(datosIniciales.descripcion), despues: resumirTexto(descripcion) });
            }
            if (datosIniciales.estado !== estado) {
                cambios.push({
                    etiqueta: 'Estado',
                    antes: datosIniciales.estado === '1' ? 'Disponible' : 'Inactivo',
                    despues: estado === '1' ? 'Disponible' : 'Inactivo'
                });
            }
        }

        payloadPendiente = {
            id_producto: idInput?.value || '0',
            nombre,
            id_categoria: idCategoria,
            condicion,
            descripcion,
            estado
        };

        if (confirmModalEl && confirmListEl) {
            if (confirmIntroEl) {
                confirmIntroEl.innerHTML = `¿Deseas aplicar los siguientes cambios en <strong>${escHtml(datosIniciales?.nombre || nombre)}</strong>?`;
            }
            confirmListEl.innerHTML = cambios.map((c) => `
                <div class="adm-user-confirm-row">
                    <span class="adm-user-confirm-label">${escHtml(c.etiqueta)}</span>
                    <div class="adm-user-confirm-values">
                        <span class="adm-user-confirm-old">${escHtml(c.antes)}</span>
                        <i class="fa-solid fa-arrow-right adm-user-confirm-arrow" aria-hidden="true"></i>
                        <span class="adm-user-confirm-new">${escHtml(c.despues)}</span>
                    </div>
                </div>
            `).join('');

            bootstrap.Modal.getInstance(modalEl)?.hide();
            bootstrap.Modal.getOrCreateInstance(confirmModalEl).show();
            return;
        }

        await ejecutarGuardadoProducto();
    });

    async function ejecutarGuardadoProducto() {
        if (guardando || !payloadPendiente) return;

        guardando = true;
        if (saveBtn) saveBtn.disabled = true;
        if (confirmSaveBtn) confirmSaveBtn.disabled = true;

        try {
            const fd = new FormData();
            fd.append('opcion', 'actualizar');
            fd.append('id_producto', payloadPendiente.id_producto);
            fd.append('nombre', payloadPendiente.nombre);
            fd.append('id_categoria', payloadPendiente.id_categoria);
            fd.append('condicion', payloadPendiente.condicion);
            fd.append('descripcion', payloadPendiente.descripcion);
            fd.append('estado', payloadPendiente.estado);

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
            bootstrap.Modal.getInstance(confirmModalEl)?.hide();
            bootstrap.Modal.getInstance(modalEl)?.hide();
            payloadPendiente = null;
            window.AdminProductos?.recargar();
        } catch (err) {
            notificar('error', 'Error', 'Ocurrió un error al guardar los cambios.');
        } finally {
            guardando = false;
            if (saveBtn) saveBtn.disabled = false;
            if (confirmSaveBtn) confirmSaveBtn.disabled = false;
        }
    }

    confirmBackBtn?.addEventListener('click', () => {
        bootstrap.Modal.getInstance(confirmModalEl)?.hide();
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    confirmSaveBtn?.addEventListener('click', () => {
        ejecutarGuardadoProducto();
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
