(function () {
    'use strict';

    const REFRESH_INTERVAL_MS = 20000;
    const DEBOUNCE_MS = 220;
    const POR_PAGINA = 10;

    const baseUrl = window.ADM_BASE_URL || '';
    const endpoint = `${baseUrl}controllers/admin_controller.php`;

    const sectionEl = document.getElementById('sec-usuarios');
    const searchInput = document.getElementById('admUserSearch');
    const roleFilter = document.getElementById('admUserRoleFilter');
    const tbodyEl = document.getElementById('admUsersTbody');
    const countEl = document.getElementById('admUsersCount');
    const paginationEl = document.getElementById('admUsersPagination');

    if (!sectionEl || !tbodyEl) return;

    const estado = {
        q: '',
        idRol: 0,
        pagina: 1,
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
        const totalPaginas = Math.max(1, Number(data.total_paginas || 1));

        if (total === 0) return 'Mostrando 0 usuarios';
        if (total === 1) return 'Mostrando 1 usuario';
        if (totalPaginas > 1) return `Mostrando ${desde}–${hasta} de ${total} usuarios`;
        return `Mostrando ${total} usuarios`;
    }

    function renderTabla(usuarios) {
        if (!Array.isArray(usuarios) || usuarios.length === 0) {
            const hayFiltros = estado.q !== '' || estado.idRol > 0;
            const mensaje = hayFiltros
                ? 'No se encontraron usuarios que coincidan con la búsqueda o el filtro.'
                : 'No se encontraron usuarios registrados.';
            tbodyEl.innerHTML = `
                <tr>
                    <td colspan="5" class="adm-table-empty">
                        <i class="fa-solid fa-users-slash" aria-hidden="true"></i>
                        <span>${escHtml(mensaje)}</span>
                    </td>
                </tr>
            `;
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
                            <div swqsweclass="adm-user-avatar"><i class="fa-solid fa-user"></i></div>
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
                                data-adm-demo-action="Ver detalles: ${etiqueta} (referencia)">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                            <button type="button" class="adm-action-btn adm-action-btn--edit" title="Editar usuario"
                                data-adm-demo-action="Editar ${etiqueta} (referencia)">
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
            btnClass: 'adm-page-btn',
            navBtnClass: '',
            groupClass: '',
            showEnds: false,
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
                pagina: String(estado.pagina),
                por_pagina: String(POR_PAGINA),
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
