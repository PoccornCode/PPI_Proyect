(function () {
    'use strict';

    const REFRESH_INTERVAL_MS = 20000;
    const DEBOUNCE_MS = 220;

    const baseUrl = window.ADM_BASE_URL || '';
    const endpoint = `${baseUrl}controllers/admin_controller.php`;

    const sectionEl = document.getElementById('sec-usuarios');
    const searchInput = document.getElementById('admUserSearch');
    const roleFilter = document.getElementById('admUserRoleFilter');
    const statusFilter = document.getElementById('admUserStatusFilter');
    const tbodyEl = document.getElementById('admUsersTbody');
    const countEl = document.getElementById('admUsersCount');
    const pageSizeSelect = document.getElementById('admUsersPageSize');
    const paginationEl = document.getElementById('admUsersPagination');

    if (!sectionEl || !tbodyEl) return;

    const estado = {
        q: '',
        idRol: 0,
        estadoFiltro: 'todos',
        pagina: 1,
        porPagina: Number(pageSizeSelect?.value || 5),
        totalPaginas: 1,
        total: 0,
        cargando: false,
        solicitudSeq: 0,
        debounceTimer: null
    };

    function escHtml(valor) {
        return String(valor ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function construirTextoConteo(data) {
        const total = Number(data.total || 0);
        const desde = Number(data.desde || 0);
        const hasta = Number(data.hasta || 0);

        if (total === 0) return 'Mostrando 0 usuarios';
        if (total === 1) return 'Mostrando 1 usuario';
        return `Mostrando ${desde}–${hasta} de ${total} usuarios`;
    }

    function renderTabla(usuarios) {
        if (!Array.isArray(usuarios) || usuarios.length === 0) {
            const hayFiltros = estado.q !== '' || estado.idRol > 0 || estado.estadoFiltro !== 'todos';
            const subtitulo = hayFiltros
                ? 'Prueba con otro término de búsqueda o cambia los filtros seleccionados.'
                : 'No hay usuarios registrados en este momento.';
            const btnLimpiar = hayFiltros
                ? `<button type="button" class="mod-btn-sm mod-btn-cancel" id="admUsersClearFiltersBtn"><i class="fa-solid fa-rotate-left"></i> Limpiar filtros</button>`
                : '';
            tbodyEl.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <div class="adm-cat-empty-icon mx-auto"><i class="fa-solid fa-users-slash" aria-hidden="true"></i></div>
                        <h3 class="h6 text-white fw-bold mb-1">No se encontraron usuarios</h3>
                        <p class="small ${hayFiltros ? 'mb-3' : 'mb-0'}" style="color:#9aa392;">${escHtml(subtitulo)}</p>
                        ${btnLimpiar}
                    </td>
                </tr>
            `;
            const clearBtn = document.getElementById('admUsersClearFiltersBtn');
            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    if (searchInput) searchInput.value = '';
                    if (roleFilter) roleFilter.value = '0';
                    if (statusFilter) statusFilter.value = 'todos';
                    estado.q = '';
                    estado.idRol = 0;
                    estado.estadoFiltro = 'todos';
                    estado.pagina = 1;
                    window.AdminUsuarios?.recargar();
                    searchInput?.focus();
                });
            }
            return;
        }

        tbodyEl.innerHTML = usuarios.map((u) => {
            const idUsuario = Number(u.id_usuario || 0);
            const idRol = Number(u.id_rol || 0);
            const etiqueta = escHtml(u.etiqueta || u.correo || `Usuario #${idUsuario}`);
            const nombreCompleto = String(u.nombre_completo || '').trim();
            const username = String(u.username || '').trim().replace(/^@+/, '');
            const partesSub = [];
            if (nombreCompleto) partesSub.push(nombreCompleto);
            if (username) partesSub.push(`@${username}`);
            const subInfo = partesSub.join(' · ');

            const rol = escHtml(u.rol || 'Pujador / Subastador');
            const rolClass = escHtml(u.rol_class || 'usuario');
            const activo = Boolean(u.activo);
            const estadoTxt = escHtml(u.estado || (activo ? 'Activo' : 'Suspendido'));
            const dotClass = activo ? 'adm-status-dot--active' : 'adm-status-dot--suspended';
            const registro = escHtml(u.registro || '—');
            const fechaRaw = escHtml(u.fecha_registro || '');

            return `
                <tr data-user-id="${idUsuario}" data-role-id="${idRol}">
                    <td>
                        <div class="adm-cell-user">
                            <div class="adm-user-avatar"><i class="fa-solid fa-user"></i></div>
                            <div class="adm-cell-user-meta">
                                <span class="adm-cell-user-main" title="${etiqueta}">${etiqueta}</span>
                                ${subInfo ? `<span class="adm-cell-user-sub">${escHtml(subInfo)}</span>` : ''}
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="adm-badge adm-badge--${rolClass}">${rol}</span>
                    </td>
                    <td>
                        <span class="adm-status">
                            <span class="adm-status-dot ${dotClass}"></span>
                            ${estadoTxt}
                        </span>
                    </td>
                    <td title="${fechaRaw}">${registro}</td>
                    <td>
                        <div class="adm-action-group">
                            <button type="button" class="adm-action-btn adm-action-btn--edit" title="Ver detalles"
                                aria-label="Ver detalles de ${etiqueta}"
                                data-adm-user-action="ver" data-user-id="${idUsuario}">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                            <button type="button" class="adm-action-btn adm-action-btn--edit" title="Editar usuario"
                                aria-label="Editar usuario ${etiqueta}"
                                data-adm-user-action="editar" data-user-id="${idUsuario}">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderPaginacion(paginaActual, totalPaginas) {
        if (!paginationEl || !window.Paginacion) return;
        window.Paginacion.render(paginationEl, {
            currentPage: paginaActual,
            totalPages: totalPaginas,
            maxVisible: 5,
            onPageChange: (pagina) => {
                estado.pagina = pagina;
                cargarUsuarios(false);
            }
        });
    }

    function aplicarDatos(data) {
        if (!data) return;
        estado.pagina = Number(data.pagina || 1);
        estado.totalPaginas = Math.max(1, Number(data.total_paginas || 1));
        estado.total = Number(data.total || 0);

        renderTabla(data.usuarios || []);
        if (countEl) {
            countEl.textContent = construirTextoConteo(data);
        }
        renderPaginacion(estado.pagina, estado.totalPaginas);
    }

    async function cargarUsuarios(esFondo = false) {
        const seq = ++estado.solicitudSeq;
        estado.cargando = true;

        try {
            const params = new URLSearchParams({
                opcion: 'usuarios',
                q: estado.q,
                id_rol: String(estado.idRol),
                estado: estado.estadoFiltro,
                pagina: String(estado.pagina),
                por_pagina: String(estado.porPagina),
                _t: String(Date.now())
            });
            if (esFondo === true) {
                params.set('bg', '1');
            }

            const response = await fetch(`${endpoint}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
                cache: 'no-store'
            });

            if (response.status === 401) {
                const errData = await response.json().catch(() => ({}));
                window.location.href = errData.redirect || `${baseUrl}views/auth/login.php${errData.expired ? '?reason=inactivity' : ''}`;
                return null;
            }
            if (!response.ok) return null;

            const payload = await response.json();
            if (seq !== estado.solicitudSeq) return null;

            if (payload?.estado && payload.data) {
                aplicarDatos(payload.data);
                return payload.data;
            }
        } catch (err) {
            console.error('Error al cargar usuarios en administración:', err);
        } finally {
            if (seq === estado.solicitudSeq) {
                estado.cargando = false;
            }
        }
        return null;
    }

    searchInput?.addEventListener('input', () => {
        const nextQ = (searchInput.value || '').trim();
        if (estado.debounceTimer) {
            clearTimeout(estado.debounceTimer);
        }
        estado.debounceTimer = setTimeout(() => {
            estado.q = nextQ;
            estado.pagina = 1;
            cargarUsuarios(false);
        }, DEBOUNCE_MS);
    });

    roleFilter?.addEventListener('change', () => {
        estado.idRol = Number(roleFilter.value || 0);
        estado.pagina = 1;
        cargarUsuarios(false);
    });

    statusFilter?.addEventListener('change', () => {
        estado.estadoFiltro = statusFilter.value || 'todos';
        estado.pagina = 1;
        cargarUsuarios(false);
    });

    pageSizeSelect?.addEventListener('change', () => {
        estado.porPagina = Math.max(1, Number(pageSizeSelect.value || 5));
        estado.pagina = 1;
        cargarUsuarios(false);
    });

    // Modal detalle de usuario
    const modalDetalleEl = document.getElementById('modalDetalleUsuario');
    const detailLoadingEl = document.getElementById('admUserDetailLoading');
    const detailErrorEl = document.getElementById('admUserDetailError');
    const detailErrorTextEl = document.getElementById('admUserDetailErrorText');
    const detailContentEl = document.getElementById('admUserDetailContent');

    const detailIdEl = document.getElementById('admUserDetailId');
    const detailAvatarEl = document.getElementById('admUserDetailAvatar');
    const detailNameEl = document.getElementById('admUserDetailName');
    const detailUsernameEl = document.getElementById('admUserDetailUsername');
    const detailRoleEl = document.getElementById('admUserDetailRole');
    const detailStatusDotEl = document.getElementById('admUserDetailStatusDot');
    const detailStatusEl = document.getElementById('admUserDetailStatus');
    const detailEmailEl = document.getElementById('admUserDetailEmail');
    const detailPhoneEl = document.getElementById('admUserDetailPhone');
    const detailLocationEl = document.getElementById('admUserDetailLocation');
    const detailRegisteredEl = document.getElementById('admUserDetailRegistered');
    const detailLastBidEl = document.getElementById('admUserDetailLastBid');
    const detailStatProductosEl = document.getElementById('admUserDetailStatProductos');
    const detailStatActivasEl = document.getElementById('admUserDetailStatActivas');
    const detailStatPendientesEl = document.getElementById('admUserDetailStatPendientes');
    const detailStatPujasEl = document.getElementById('admUserDetailStatPujas');
    const detailStatFinalizadasEl = document.getElementById('admUserDetailStatFinalizadas');
    const detailStatSinOfertasEl = document.getElementById('admUserDetailStatSinOfertas');
    const detailStatCanceladasEl = document.getElementById('admUserDetailStatCanceladas');
    const detailStatRechazadasEl = document.getElementById('admUserDetailStatRechazadas');
    const detailEditShortcutBtn = document.getElementById('admUserDetailEditShortcut');

    let detalleSeq = 0;
    let detalleUsuarioActualId = 0;

    function getDetalleModal() {
        if (!modalDetalleEl || !window.bootstrap) return null;
        return window.bootstrap.Modal.getOrCreateInstance(modalDetalleEl);
    }

    function setDetalleVista(vista, mensajeError = '') {
        detailLoadingEl?.classList.toggle('d-none', vista !== 'loading');
        detailErrorEl?.classList.toggle('d-none', vista !== 'error');
        detailContentEl?.classList.toggle('d-none', vista !== 'content');
        detailEditShortcutBtn?.classList.toggle('d-none', vista !== 'content');
        if (vista === 'error' && detailErrorTextEl) {
            detailErrorTextEl.textContent = mensajeError || 'No se pudo cargar la información del usuario.';
        }
    }

    function poblarDetalleUsuario(u) {
        if (detailIdEl) detailIdEl.textContent = `#${Number(u.id_usuario || 0)}`;
        if (detailAvatarEl) detailAvatarEl.textContent = u.inicial || 'U';
        if (detailNameEl) detailNameEl.textContent = u.nombre_completo || `Usuario #${u.id_usuario}`;
        if (detailUsernameEl) {
            const uname = String(u.username || '').trim().replace(/^@+/, '');
            detailUsernameEl.textContent = uname ? `@${uname}` : 'Sin nombre de usuario';
        }

        if (detailRoleEl) {
            detailRoleEl.className = `adm-badge adm-badge--${u.rol_class || 'usuario'}`;
            detailRoleEl.textContent = u.rol || 'Pujador / Subastador';
        }

        const activo = Boolean(u.activo);
        if (detailStatusDotEl) {
            detailStatusDotEl.className = `adm-status-dot ${activo ? 'adm-status-dot--active' : 'adm-status-dot--suspended'}`;
        }
        if (detailStatusEl) {
            detailStatusEl.textContent = u.estado || (activo ? 'Activo' : 'Suspendido');
        }

        const correos = Array.isArray(u.correos) && u.correos.length > 0 ? u.correos.join(', ') : (u.correo || '');
        const telefonos = Array.isArray(u.telefonos) && u.telefonos.length > 0 ? u.telefonos.join(', ') : (u.telefono || '');

        if (detailEmailEl) detailEmailEl.textContent = correos || 'No registrado';
        if (detailPhoneEl) detailPhoneEl.textContent = telefonos || 'No registrado';
        if (detailLocationEl) detailLocationEl.textContent = (u.ubicacion || '').trim() || 'No registrada';
        if (detailRegisteredEl) detailRegisteredEl.textContent = u.fecha_registro_fmt || '—';
        if (detailLastBidEl) detailLastBidEl.textContent = `Última puja: ${u.ultima_puja_fmt || 'Sin pujas'}`;

        if (detailStatProductosEl) detailStatProductosEl.textContent = String(Number(u.productos || 0));
        if (detailStatActivasEl) detailStatActivasEl.textContent = String(Number(u.subastas_activas || 0));
        if (detailStatPendientesEl) detailStatPendientesEl.textContent = String(Number(u.subastas_pendientes || 0));
        if (detailStatPujasEl) detailStatPujasEl.textContent = String(Number(u.pujas_total || 0));
        if (detailStatFinalizadasEl) detailStatFinalizadasEl.textContent = String(Number(u.subastas_finalizadas || 0));
        if (detailStatSinOfertasEl) detailStatSinOfertasEl.textContent = String(Number(u.subastas_sin_ofertas || 0));
        if (detailStatCanceladasEl) detailStatCanceladasEl.textContent = String(Number(u.subastas_canceladas || 0));
        if (detailStatRechazadasEl) detailStatRechazadasEl.textContent = String(Number(u.subastas_rechazadas || 0));
    }

    async function abrirDetalleUsuario(idUsuario) {
        const id = Number(idUsuario || 0);
        if (id <= 0) return;

        const seq = ++detalleSeq;
        if (detailIdEl) detailIdEl.textContent = `#${id}`;
        setDetalleVista('loading');
        getDetalleModal()?.show();

        try {
            const params = new URLSearchParams({
                opcion: 'usuario_detalle',
                id_usuario: String(id),
                _t: String(Date.now())
            });
            const response = await fetch(`${endpoint}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
                cache: 'no-store'
            });

            if (response.status === 401) {
                const errData = await response.json().catch(() => ({}));
                window.location.href = errData.redirect || `${baseUrl}views/auth/login.php${errData.expired ? '?reason=inactivity' : ''}`;
                return;
            }

            const payload = await response.json().catch(() => null);
            if (seq !== detalleSeq) return;

            if (!response.ok || !payload?.estado || !payload.usuario) {
                setDetalleVista('error', payload?.mensaje || 'No se pudo cargar la información del usuario.');
                return;
            }

            poblarDetalleUsuario(payload.usuario);
            detalleUsuarioActualId = Number(payload.usuario.id_usuario || id);
            setDetalleVista('content');
        } catch (err) {
            if (seq !== detalleSeq) return;
            console.error('Error al cargar detalle de usuario:', err);
            setDetalleVista('error', 'Error de conexión al consultar el detalle del usuario.');
        }
    }

    // Modal editar usuario
    const modalEditarEl = document.getElementById('modalEditarUsuario');
    const formEditarEl = document.getElementById('formEditarUsuario');
    const editLoadingEl = document.getElementById('admUserEditLoading');
    const editErrorLoadEl = document.getElementById('admUserEditErrorLoad');
    const editErrorLoadTextEl = document.getElementById('admUserEditErrorLoadText');
    const editContentEl = document.getElementById('admUserEditContent');
    const editIdBadgeEl = document.getElementById('admUserEditIdBadge');
    const editAvatarEl = document.getElementById('admUserEditAvatar');
    const editHeaderNameEl = document.getElementById('admUserEditHeaderName');
    const editHeaderSubEl = document.getElementById('admUserEditHeaderSub');
    const editLockNoteEl = document.getElementById('admUserEditLockNote');
    const editLockNoteTextEl = document.getElementById('admUserEditLockNoteText');

    const editIdInput = document.getElementById('admUserEditId');
    const editRolSelect = document.getElementById('admUserEditRol');
    const editEstadoCheck = document.getElementById('admUserEditEstado');
    const editEstadoLabelEl = document.getElementById('admUserEditEstadoLabel');
    const editEstadoHelpEl = document.getElementById('admUserEditEstadoHelp');
    const editNombreInput = document.getElementById('admUserEditNombre');
    const editApellidoInput = document.getElementById('admUserEditApellido');
    const editUsernameInput = document.getElementById('admUserEditUsername');
    const editCorreoInput = document.getElementById('admUserEditCorreo');
    const editTelefonoInput = document.getElementById('admUserEditTelefono');
    const editUbicacionInput = document.getElementById('admUserEditUbicacion');
    const btnGuardarEditarEl = document.getElementById('btnGuardarEditarUsuario');
    const btnGuardarEditarTextEl = document.getElementById('btnGuardarEditarUsuarioText');
    const btnEnviarCodigoRecEl = document.getElementById('btnEnviarCodigoRecuperacion');

    const modalConfirmarEditarEl = document.getElementById('modalConfirmarEditarUsuario');
    const confirmIntroEl = document.getElementById('admUserConfirmIntro');
    const confirmListEl = document.getElementById('admUserConfirmList');
    const btnVolverEditarEl = document.getElementById('btnVolverEditarUsuario');
    const btnConfirmarEditarEl = document.getElementById('btnConfirmarEditarUsuario');
    const btnConfirmarEditarTextEl = document.getElementById('btnConfirmarEditarUsuarioText');

    const editFieldInputs = {
        nombre: editNombreInput,
        apellido: editApellidoInput,
        username: editUsernameInput,
        correo: editCorreoInput,
        telefono: editTelefonoInput,
        ubicacion: editUbicacionInput,
        id_rol: editRolSelect
    };

    let editarSeq = 0;
    let guardandoEdicion = false;
    let editPermisosBloqueados = false;
    let editUsuarioCargado = null;
    let datosPendientesGuardar = null;
    let volverAlModalEditar = false;

    function notificarToast(tipo, titulo, mensaje, duracionMs = 4500) {
        if (typeof window.mostrarToast === 'function') {
            window.mostrarToast(tipo, titulo, mensaje, duracionMs);
        } else {
            window.AdminDashboard?.notify(mensaje, tipo, titulo);
        }
    }

    function getEditarModal() {
        if (!modalEditarEl || !window.bootstrap) return null;
        return window.bootstrap.Modal.getOrCreateInstance(modalEditarEl);
    }

    function getConfirmarEditarModal() {
        if (!modalConfirmarEditarEl || !window.bootstrap) return null;
        return window.bootstrap.Modal.getOrCreateInstance(modalConfirmarEditarEl);
    }

    function setEditarVista(vista, mensajeError = '') {
        editLoadingEl?.classList.toggle('d-none', vista !== 'loading');
        editErrorLoadEl?.classList.toggle('d-none', vista !== 'error');
        editContentEl?.classList.toggle('d-none', vista !== 'content');
        if (btnGuardarEditarEl) {
            btnGuardarEditarEl.disabled = vista !== 'content' || guardandoEdicion;
        }
        if (vista === 'error' && editErrorLoadTextEl) {
            editErrorLoadTextEl.textContent = mensajeError || 'No se pudo cargar la información del usuario.';
        }
    }

    function limpiarErroresEditar() {
        Object.values(editFieldInputs).forEach((el) => el?.classList.remove('is-invalid'));
        formEditarEl?.querySelectorAll('[data-field-error]').forEach((el) => {
            el.textContent = '';
        });
    }

    function limpiarErrorCampo(campo) {
        editFieldInputs[campo]?.classList.remove('is-invalid');
        const errEl = formEditarEl?.querySelector(`[data-field-error="${campo}"]`);
        if (errEl) errEl.textContent = '';
    }

    function mostrarErrorEditar(mensaje, campo = '') {
        limpiarErroresEditar();
        if (campo && editFieldInputs[campo]) {
            const input = editFieldInputs[campo];
            input.classList.add('is-invalid');
            const errEl = formEditarEl?.querySelector(`[data-field-error="${campo}"]`);
            if (errEl) errEl.textContent = mensaje;
            input.focus();
            return;
        }
        notificarToast('error', 'No se pudo actualizar', mensaje || 'No se pudieron guardar los cambios.', 5000);
    }

    const REGEX_NOMBRE = /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s'-]{2,100}$/;
    const REGEX_USERNAME = /^[A-Za-z0-9._-]{3,50}$/;
    const REGEX_EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const REGEX_TELEFONO = /^\d{4}-\d{4}$/;

    Object.entries(editFieldInputs).forEach(([campo, input]) => {
        if (!input) return;
        const evento = input.tagName === 'SELECT' ? 'change' : 'input';
        input.addEventListener(evento, () => limpiarErrorCampo(campo));
    });

    // Máscara de teléfono 0000-0000 
    editTelefonoInput?.addEventListener('input', () => {
        let valor = editTelefonoInput.value.replace(/\D/g, '').substring(0, 8);
        if (valor.length > 4) {
            valor = `${valor.substring(0, 4)}-${valor.substring(4)}`;
        }
        editTelefonoInput.value = valor;
    });

    function sincronizarTextoEstadoEditar() {
        const activo = Boolean(editEstadoCheck?.checked);
        if (editEstadoLabelEl) {
            editEstadoLabelEl.textContent = activo ? 'Cuenta activa' : 'Cuenta suspendida';
        }
        if (editEstadoHelpEl) {
            editEstadoHelpEl.textContent = activo
                ? 'Puede iniciar sesión y operar con normalidad.'
                : 'No podrá iniciar sesión ni participar en subastas.';
        }
    }

    function poblarFormularioEditar(u) {
        editUsuarioCargado = u;
        if (editIdBadgeEl) editIdBadgeEl.textContent = `#${Number(u.id_usuario || 0)}`;
        if (editIdInput) editIdInput.value = String(Number(u.id_usuario || 0));
        if (editAvatarEl) editAvatarEl.textContent = u.inicial || 'U';
        if (editHeaderNameEl) editHeaderNameEl.textContent = u.nombre_completo || `Usuario #${u.id_usuario}`;
        if (editHeaderSubEl) {
            const uname = String(u.username || '').trim().replace(/^@+/, '');
            const reg = u.fecha_registro_fmt || '—';
            editHeaderSubEl.textContent = `${uname ? '@' + uname : 'Sin usuario'} · Registrado ${reg}`;
        }

        if (editNombreInput) editNombreInput.value = u.nombre || '';
        if (editApellidoInput) editApellidoInput.value = u.apellido || '';
        if (editUsernameInput) editUsernameInput.value = u.username || '';
        if (editCorreoInput) editCorreoInput.value = u.correo || '';
        if (editTelefonoInput) editTelefonoInput.value = u.telefono || '';
        if (editUbicacionInput) editUbicacionInput.value = u.ubicacion || '';
        if (editRolSelect) editRolSelect.value = String(Number(u.id_rol || 2));
        if (editEstadoCheck) editEstadoCheck.checked = Boolean(u.activo);

        const esPropio = Boolean(u.es_propio);
        const esUltimoAdmin = Boolean(u.es_ultimo_admin);
        editPermisosBloqueados = esPropio || esUltimoAdmin;

        if (editRolSelect) editRolSelect.disabled = editPermisosBloqueados;
        if (editEstadoCheck) editEstadoCheck.disabled = editPermisosBloqueados;

        if (editLockNoteEl && editLockNoteTextEl) {
            if (esPropio) {
                editLockNoteTextEl.textContent = 'Estás editando tu propia cuenta: el rol y el estado están protegidos.';
                editLockNoteEl.classList.remove('d-none');
            } else if (esUltimoAdmin) {
                editLockNoteTextEl.textContent = 'Es el único administrador activo: no se puede cambiar su rol ni suspenderlo.';
                editLockNoteEl.classList.remove('d-none');
            } else {
                editLockNoteEl.classList.add('d-none');
            }
        }

        sincronizarTextoEstadoEditar();
        limpiarErroresEditar();
    }

    async function abrirEditarUsuario(idUsuario) {
        const id = Number(idUsuario || 0);
        if (id <= 0) return;

        const seq = ++editarSeq;
        editUsuarioCargado = null;
        limpiarErroresEditar();
        if (editIdBadgeEl) editIdBadgeEl.textContent = `#${id}`;
        setEditarVista('loading');
        getEditarModal()?.show();

        try {
            const params = new URLSearchParams({
                opcion: 'usuario_detalle',
                id_usuario: String(id),
                _t: String(Date.now())
            });
            const response = await fetch(`${endpoint}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
                cache: 'no-store'
            });

            if (response.status === 401) {
                const errData = await response.json().catch(() => ({}));
                window.location.href = errData.redirect || `${baseUrl}views/auth/login.php${errData.expired ? '?reason=inactivity' : ''}`;
                return;
            }

            const payload = await response.json().catch(() => null);
            if (seq !== editarSeq) return;

            if (!response.ok || !payload?.estado || !payload.usuario) {
                setEditarVista('error', payload?.mensaje || 'No se pudo cargar la información del usuario.');
                return;
            }

            poblarFormularioEditar(payload.usuario);
            setEditarVista('content');
        } catch (err) {
            if (seq !== editarSeq) return;
            console.error('Error al cargar usuario para edición:', err);
            setEditarVista('error', 'Error de conexión al consultar los datos del usuario.');
        }
    }

    editEstadoCheck?.addEventListener('change', sincronizarTextoEstadoEditar);

    btnEnviarCodigoRecEl?.addEventListener('click', async () => {
        const idUsuario = Number(editUsuarioCargado?.id_usuario || editIdInput?.value || 0);
        if (idUsuario <= 0) return;

        const correoActualInput = (editCorreoInput?.value || '').trim();
        const correoGuardado = (editUsuarioCargado?.correo || '').trim();

        if (correoActualInput !== correoGuardado) {
            notificarToast(
                'warning',
                'Correo modificado',
                'Guarda primero los cambios del correo antes de enviar el enlace de restablecimiento.',
                4500
            );
            return;
        }

        if (!editEstadoCheck?.checked) {
            notificarToast(
                'warning',
                'Cuenta suspendida',
                'Activa la cuenta antes de enviar el enlace de restablecimiento.',
                4500
            );
            return;
        }

        const btnText = document.getElementById('btnEnviarCodigoRecText');
        const textoOriginal = btnText ? btnText.textContent : 'Enviar enlace';

        btnEnviarCodigoRecEl.disabled = true;
        if (btnText) btnText.textContent = 'Enviando...';

        try {
            const formData = new FormData();
            formData.append('opcion', 'usuario_enviar_recuperacion');
            formData.append('id_usuario', String(idUsuario));

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: formData
            });

            if (response.status === 401) {
                const errData = await response.json().catch(() => ({}));
                window.location.href = errData.redirect || `${baseUrl}views/auth/login.php${errData.expired ? '?reason=inactivity' : ''}`;
                return;
            }

            const payload = await response.json().catch(() => null);
            if (!response.ok || !payload?.estado) {
                notificarToast(
                    'error',
                    'No se pudo enviar',
                    payload?.mensaje || 'No se pudo enviar el enlace de restablecimiento.',
                    4500
                );
                return;
            }

            notificarToast(
                'success',
                'Enlace enviado',
                payload.mensaje || 'Se envió el enlace de restablecimiento al correo del usuario.',
                4500
            );
        } catch (err) {
            console.error('Error al enviar enlace de recuperación:', err);
            notificarToast('error', 'Error de conexión', 'No se pudo comunicar con el servidor.');
        } finally {
            btnEnviarCodigoRecEl.disabled = false;
            if (btnText) btnText.textContent = textoOriginal;
        }
    });

    detailEditShortcutBtn?.addEventListener('click', () => {
        const id = detalleUsuarioActualId;
        if (id <= 0) return;
        const detalleModal = getDetalleModal();
        if (modalDetalleEl && detalleModal) {
            modalDetalleEl.addEventListener('hidden.bs.modal', () => {
                abrirEditarUsuario(id);
            }, { once: true });
            detalleModal.hide();
        } else {
            abrirEditarUsuario(id);
        }
    });

    function obtenerEtiquetaRolSelect(idRol) {
        if (!editRolSelect) return String(idRol);
        const opt = Array.from(editRolSelect.options).find((o) => Number(o.value) === Number(idRol));
        return opt ? opt.textContent.trim() : String(idRol);
    }

    function construirCambiosEdicion(nuevos) {
        const orig = editUsuarioCargado;
        if (!orig) return [];

        const cambios = [];
        const agregar = (etiqueta, antes, despues) => {
            const a = String(antes ?? '').trim();
            const d = String(despues ?? '').trim();
            if (a !== d) {
                cambios.push({
                    etiqueta,
                    antes: a || 'Sin especificar',
                    despues: d || 'Sin especificar'
                });
            }
        };

        if (Number(orig.id_rol) !== Number(nuevos.id_rol)) {
            agregar('Rol', orig.rol || obtenerEtiquetaRolSelect(orig.id_rol), obtenerEtiquetaRolSelect(nuevos.id_rol));
        }
        const estadoOrig = orig.activo ? 'Activo' : 'Suspendido';
        const estadoNuevo = nuevos.estado === '1' ? 'Activo' : 'Suspendido';
        if (estadoOrig !== estadoNuevo) {
            agregar('Estado', estadoOrig, estadoNuevo);
        }
        agregar('Nombre', orig.nombre, nuevos.nombre);
        agregar('Apellido', orig.apellido, nuevos.apellido);
        agregar('Usuario', orig.username ? `@${orig.username}` : '', nuevos.username ? `@${nuevos.username}` : '');
        agregar('Correo', orig.correo, nuevos.correo);
        agregar('Teléfono', orig.telefono, nuevos.telefono);
        agregar('Ubicación', orig.ubicacion, nuevos.ubicacion);

        return cambios;
    }

    function abrirConfirmacionEdicion(datos, cambios) {
        datosPendientesGuardar = datos;
        volverAlModalEditar = false;

        if (confirmIntroEl) {
            const nombreCuenta = editUsuarioCargado?.nombre_completo || `Usuario #${datos.id_usuario}`;
            confirmIntroEl.innerHTML = `¿Deseas aplicar los siguientes cambios en la cuenta de <strong class="text-white">${escHtml(nombreCuenta)}</strong>?`;
        }

        if (confirmListEl) {
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
        }

        const editarModal = getEditarModal();
        const confirmModal = getConfirmarEditarModal();
        if (modalEditarEl && editarModal && confirmModal) {
            modalEditarEl.addEventListener('hidden.bs.modal', () => {
                confirmModal.show();
            }, { once: true });
            editarModal.hide();
        } else {
            confirmModal?.show();
        }
    }

    async function ejecutarGuardadoEdicion() {
        if (guardandoEdicion || !datosPendientesGuardar) return;

        const datos = datosPendientesGuardar;
        guardandoEdicion = true;
        if (btnConfirmarEditarEl) btnConfirmarEditarEl.disabled = true;
        if (btnVolverEditarEl) btnVolverEditarEl.disabled = true;
        if (btnConfirmarEditarTextEl) btnConfirmarEditarTextEl.textContent = 'Guardando...';

        try {
            const formData = new FormData();
            formData.append('opcion', 'usuario_actualizar');
            formData.append('id_usuario', String(datos.id_usuario));
            formData.append('nombre', datos.nombre);
            formData.append('apellido', datos.apellido);
            formData.append('username', datos.username);
            formData.append('correo', datos.correo);
            formData.append('telefono', datos.telefono);
            formData.append('ubicacion', datos.ubicacion);
            formData.append('id_rol', String(datos.id_rol));
            formData.append('estado', String(datos.estado));

            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData,
                headers: { Accept: 'application/json' },
                cache: 'no-store'
            });

            if (response.status === 401) {
                const errData = await response.json().catch(() => ({}));
                window.location.href = errData.redirect || `${baseUrl}views/auth/login.php${errData.expired ? '?reason=inactivity' : ''}`;
                return;
            }

            const payload = await response.json().catch(() => null);
            if (!response.ok || !payload?.estado) {
                const msg = payload?.mensaje || 'No se pudieron guardar los cambios.';
                const campo = payload?.campo || '';
                const confirmModal = getConfirmarEditarModal();
                if (modalConfirmarEditarEl && confirmModal) {
                    modalConfirmarEditarEl.addEventListener('hidden.bs.modal', () => {
                        getEditarModal()?.show();
                        setTimeout(() => mostrarErrorEditar(msg, campo), 150);
                    }, { once: true });
                    confirmModal.hide();
                } else {
                    mostrarErrorEditar(msg, campo);
                }
                return;
            }

            datosPendientesGuardar = null;
            getConfirmarEditarModal()?.hide();
            notificarToast('success', 'Panel de Administración', payload.mensaje || 'Usuario actualizado correctamente.', 4500);
            cargarUsuarios(false);
        } catch (err) {
            console.error('Error al actualizar usuario:', err);
            notificarToast('error', 'No se pudo actualizar', 'Error de conexión al guardar los cambios.', 5000);
        } finally {
            guardandoEdicion = false;
            if (btnConfirmarEditarEl) btnConfirmarEditarEl.disabled = false;
            if (btnVolverEditarEl) btnVolverEditarEl.disabled = false;
            if (btnConfirmarEditarTextEl) btnConfirmarEditarTextEl.textContent = 'Confirmar y guardar';
        }
    }

    btnVolverEditarEl?.addEventListener('click', () => {
        volverAlModalEditar = true;
        getConfirmarEditarModal()?.hide();
    });

    modalConfirmarEditarEl?.addEventListener('hidden.bs.modal', () => {
        if (volverAlModalEditar) {
            volverAlModalEditar = false;
            getEditarModal()?.show();
        }
    });

    btnConfirmarEditarEl?.addEventListener('click', ejecutarGuardadoEdicion);

    let validandoEdicion = false;

    formEditarEl?.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (guardandoEdicion || validandoEdicion) return;

        const idUsuario = Number(editIdInput?.value || 0);
        if (idUsuario <= 0) return;

        const nombre = (editNombreInput?.value || '').trim();
        const apellido = (editApellidoInput?.value || '').trim();
        const username = (editUsernameInput?.value || '').trim().replace(/^@+/, '');
        const correo = (editCorreoInput?.value || '').trim();
        const telefono = (editTelefonoInput?.value || '').trim();
        const ubicacion = (editUbicacionInput?.value || '').trim();

        if (!nombre) {
            mostrarErrorEditar('El nombre es obligatorio.', 'nombre');
            return;
        }
        if (!REGEX_NOMBRE.test(nombre)) {
            mostrarErrorEditar('Ingresa un nombre válido (solo letras, mínimo 2 caracteres).', 'nombre');
            return;
        }
        if (!apellido) {
            mostrarErrorEditar('El apellido es obligatorio.', 'apellido');
            return;
        }
        if (!REGEX_NOMBRE.test(apellido)) {
            mostrarErrorEditar('Ingresa un apellido válido (solo letras, mínimo 2 caracteres).', 'apellido');
            return;
        }
        if (!REGEX_USERNAME.test(username)) {
            mostrarErrorEditar('Usa entre 3 y 50 caracteres válidos (letras, números, punto o guion).', 'username');
            return;
        }
        if (!correo || correo.length > 100 || !REGEX_EMAIL.test(correo)) {
            mostrarErrorEditar('Ingresa un correo electrónico válido.', 'correo');
            return;
        }
        if (telefono && !REGEX_TELEFONO.test(telefono)) {
            mostrarErrorEditar('Usa el formato de 8 dígitos: 0000-0000.', 'telefono');
            return;
        }
        if (ubicacion && (ubicacion.length < 3 || ubicacion.length > 150 || !/[A-Za-zÁÉÍÓÚáéíóúÑñÜü]/.test(ubicacion))) {
            mostrarErrorEditar('Ingresa una ubicación válida (mínimo 3 caracteres).', 'ubicacion');
            return;
        }

        const idRolValor = editPermisosBloqueados && editUsuarioCargado
            ? String(Number(editUsuarioCargado.id_rol || 2))
            : String(Number(editRolSelect?.value || 2));
        const estadoValor = editPermisosBloqueados && editUsuarioCargado
            ? (editUsuarioCargado.activo ? '1' : '0')
            : (editEstadoCheck?.checked ? '1' : '0');

        const datos = {
            id_usuario: idUsuario,
            nombre,
            apellido,
            username,
            correo,
            telefono,
            ubicacion,
            id_rol: idRolValor,
            estado: estadoValor
        };

        const cambios = construirCambiosEdicion(datos);
        if (cambios.length === 0) {
            notificarToast('info', 'Sin cambios', 'No se detectaron modificaciones en los datos del usuario.', 3800);
            return;
        }

        limpiarErroresEditar();
        validandoEdicion = true;
        if (btnGuardarEditarEl) btnGuardarEditarEl.disabled = true;
        if (btnGuardarEditarTextEl) btnGuardarEditarTextEl.textContent = 'Validando...';

        try {
            const formData = new FormData();
            formData.append('opcion', 'usuario_validar_edicion');
            formData.append('id_usuario', String(datos.id_usuario));
            formData.append('nombre', datos.nombre);
            formData.append('apellido', datos.apellido);
            formData.append('username', datos.username);
            formData.append('correo', datos.correo);
            formData.append('telefono', datos.telefono);
            formData.append('ubicacion', datos.ubicacion);
            formData.append('id_rol', String(datos.id_rol));
            formData.append('estado', String(datos.estado));

            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData,
                headers: { Accept: 'application/json' },
                cache: 'no-store'
            });

            if (response.status === 401) {
                const errData = await response.json().catch(() => ({}));
                window.location.href = errData.redirect || `${baseUrl}views/auth/login.php${errData.expired ? '?reason=inactivity' : ''}`;
                return;
            }

            const payload = await response.json().catch(() => null);
            if (!response.ok || !payload?.estado) {
                mostrarErrorEditar(payload?.mensaje || 'Revisa los datos ingresados.', payload?.campo || '');
                return;
            }

            abrirConfirmacionEdicion(datos, cambios);
        } catch (err) {
            console.error('Error al validar edición de usuario:', err);
            notificarToast('error', 'Error de conexión', 'No se pudieron validar los datos. Intenta nuevamente.', 5000);
        } finally {
            validandoEdicion = false;
            if (btnGuardarEditarEl) btnGuardarEditarEl.disabled = false;
            if (btnGuardarEditarTextEl) btnGuardarEditarTextEl.textContent = 'Guardar cambios';
        }
    });

    tbodyEl.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-adm-user-action]');
        if (!btn) return;
        const accion = btn.dataset.admUserAction;
        const idUsuario = Number(btn.dataset.userId || btn.closest('tr')?.dataset.userId || 0);
        if (idUsuario <= 0) return;

        if (accion === 'ver') {
            abrirDetalleUsuario(idUsuario);
        } else if (accion === 'editar') {
            abrirEditarUsuario(idUsuario);
        }
    });

    if (window.ADM_USUARIOS_INICIAL) {
        aplicarDatos(window.ADM_USUARIOS_INICIAL);
    } else {
        cargarUsuarios(false);
    }

    document.addEventListener('DOMContentLoaded', () => {
        setInterval(() => {
            if (!document.hidden && sectionEl.classList.contains('active')) {
                cargarUsuarios(true);
            }
        }, REFRESH_INTERVAL_MS);

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && sectionEl.classList.contains('active')) {
                cargarUsuarios(false);
            }
        });

        window.addEventListener('focus', () => {
            if (sectionEl.classList.contains('active')) {
                cargarUsuarios(false);
            }
        });
    });

    window.AdminUsuarios = {
        recargar: () => cargarUsuarios(false),
        cargarUsuarios
    };
})();
