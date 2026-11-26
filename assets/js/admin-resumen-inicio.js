(function () {
    'use strict';

    const REFRESH_INTERVAL_MS = 20000;
    const baseUrl = window.ADM_BASE_URL || '';
    const endpoint = `${baseUrl}controllers/admin_controller.php?opcion=resumen_inicio`;

    const usersListEl = document.getElementById('admRecentUsersList');
    const featuredListEl = document.getElementById('admFeaturedAuctionsList');

    let actualizando = false;

    function escHtml(valor) {
        return String(valor ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function renderUsuariosRecientes(usuarios) {
        if (!usersListEl) return;

        if (!Array.isArray(usuarios) || usuarios.length === 0) {
            usersListEl.innerHTML = `
                <div class="adm-empty-inline">
                    <i class="fa-solid fa-users-slash" aria-hidden="true"></i>
                    <span>No hay usuarios registrados aún.</span>
                </div>
            `;
            return;
        }

        usersListEl.innerHTML = usuarios.map((u) => {
            const etiqueta = escHtml(u.etiqueta || u.correo || '');
            const rol = escHtml(u.rol || 'Pujador / Subastador');
            const rolClass = escHtml(u.rol_class || 'usuario');
            return `
                <div class="adm-recent-item">
                    <div class="adm-user-avatar" aria-hidden="true"><i class="fa-solid fa-user"></i></div>
                    <div class="adm-recent-email" title="${etiqueta}">${etiqueta}</div>
                    <span class="adm-badge adm-badge--${rolClass}">${rol}</span>
                </div>
            `;
        }).join('');
    }

    function renderSubastasDestacadas(subastas) {
        if (!featuredListEl) return;

        if (!Array.isArray(subastas) || subastas.length === 0) {
            featuredListEl.innerHTML = `
                <div class="adm-empty-inline">
                    <i class="fa-solid fa-gavel" aria-hidden="true"></i>
                    <span>No hay subastas activas en este momento.</span>
                </div>
            `;
            return;
        }

        featuredListEl.innerHTML = subastas.map((s) => {
            const totalPujas = Number(s.total_pujas || 0);
            const pujasTxt = totalPujas === 1 ? '1 puja' : `${totalPujas} pujas`;
            const titulo = escHtml(s.titulo || '');
            const categoria = escHtml(s.categoria || 'General');
            const imagen = escHtml(s.imagen || '');
            const precio = escHtml(s.precio || '$0.00');
            const precioLabel = escHtml(s.precio_label || (totalPujas > 0 ? 'Puja actual' : 'Precio inicial'));
            const tiempo = escHtml(s.tiempo || '00:00:00');
            const fechaIso = escHtml(s.fecha_final_iso || '');
            const idSubasta = Number(s.id_subasta || 0);

            return `
                <article class="adm-featured-row" data-adm-featured-row data-subasta-id="${idSubasta}" role="button" tabindex="0">
                    <div class="adm-featured-thumb">
                        <img src="${imagen}" alt="${titulo}" loading="lazy">
                    </div>
                    <div class="adm-featured-info">
                        <div class="adm-featured-title" title="${titulo}">${titulo}</div>
                        <div class="adm-featured-meta">
                            <span>${categoria}</span>
                            <span class="adm-meta-dot" aria-hidden="true">·</span>
                            <span>${escHtml(pujasTxt)}</span>
                            <span class="adm-meta-dot" aria-hidden="true">·</span>
                            <span class="adm-featured-time">
                                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                <span data-adm-countdown="${fechaIso}">${tiempo}</span>
                            </span>
                        </div>
                    </div>
                    <div class="adm-featured-aside">
                        <div class="adm-featured-price">${precio}</div>
                        <div class="adm-featured-sub">${precioLabel}</div>
                    </div>
                </article>
            `;
        }).join('');

        actualizarContadores();
    }

    function actualizarContadores() {
        if (!featuredListEl) return;
        const elementos = featuredListEl.querySelectorAll('[data-adm-countdown]');
        if (!elementos.length) return;

        const ahora = Date.now();
        let algunaFinalizo = false;

        elementos.forEach((el) => {
            const fin = Date.parse(el.dataset.admCountdown || '');
            if (Number.isNaN(fin)) return;

            const diffSeg = Math.floor((fin - ahora) / 1000);
            if (diffSeg <= 0) {
                el.textContent = 'Finalizada';
                if (!el.dataset.finalizadaProcesada) {
                    el.dataset.finalizadaProcesada = '1';
                    algunaFinalizo = true;
                }
                return;
            }

            const dias = Math.floor(diffSeg / 86400);
            const horas = Math.floor((diffSeg % 86400) / 3600);
            const minutos = Math.floor((diffSeg % 3600) / 60);
            const segundos = diffSeg % 60;

            if (dias > 0) {
                el.textContent = `${dias} d ${pad(horas)} h`;
            } else {
                el.textContent = `${pad(horas)}:${pad(minutos)}:${pad(segundos)}`;
            }
        });

        if (algunaFinalizo) {
            setTimeout(() => actualizar(true), 1500);
        }
    }

    async function actualizar(esFondo = false) {
        if (actualizando || (!usersListEl && !featuredListEl)) return null;

        actualizando = true;
        try {
            const bgParam = esFondo === true ? '&bg=1' : '';
            const response = await fetch(`${endpoint}${bgParam}&_t=${Date.now()}`, {
                headers: { Accept: 'application/json' },
                cache: 'no-store'
            });

            if (response.status === 401) {
                const errData = await response.json().catch(() => ({}));
                window.location.href = errData.redirect || `${baseUrl}views/auth/login.php${errData.expired ? '?reason=inactivity' : ''}`;
                return null;
            }
            if (!response.ok) return null;

            const data = await response.json();
            if (data?.estado && data.resumen) {
                renderUsuariosRecientes(data.resumen.usuarios_recientes);
                renderSubastasDestacadas(data.resumen.subastas_destacadas);
                return data.resumen;
            }
        } catch (err) {
            console.error('Error al actualizar resumen inferior de administración:', err);
        } finally {
            actualizando = false;
        }
        return null;
    }

    if (featuredListEl) {
        const irASubastas = (event) => {
            const row = event.target.closest('[data-adm-featured-row]');
            if (!row) return;
            window.AdminDashboard?.activateTab('subastas');
        };

        featuredListEl.addEventListener('click', irASubastas);
        featuredListEl.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                const row = event.target.closest('[data-adm-featured-row]');
                if (!row) return;
                event.preventDefault();
                window.AdminDashboard?.activateTab('subastas');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        actualizarContadores();
        setInterval(actualizarContadores, 1000);

        setInterval(() => {
            if (!document.hidden) {
                actualizar(true);
            }
        }, REFRESH_INTERVAL_MS);

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                actualizar();
            }
        });

        window.addEventListener('focus', () => {
            actualizar();
        });

        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                actualizar();
            }
        });
    });

    window.AdminResumenInicio = {
        actualizar,
        renderUsuariosRecientes,
        renderSubastasDestacadas
    };
})();
