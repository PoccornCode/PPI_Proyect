(function () {
    'use strict';

    const REFRESH_INTERVAL_MS = 15000;
    const baseUrl = window.MOD_BASE_URL || '';
    const endpoint = `${baseUrl}controllers/moderador_controller.php?opcion=kpis`;

    function renderSparkline(svgEl, barras) {
        if (!svgEl || !Array.isArray(barras)) return;
        const svgNS = 'http://www.w3.org/2000/svg';
        svgEl.innerHTML = '';

        barras.forEach((barra) => {
            const rect = document.createElementNS(svgNS, 'rect');
            rect.setAttribute('x', String(barra.x ?? 0));
            rect.setAttribute('y', String(barra.y ?? 32));
            rect.setAttribute('width', '6');
            rect.setAttribute('height', String(barra.height ?? 4));
            rect.setAttribute('fill', String(barra.fill ?? '#6f8054'));
            rect.setAttribute('rx', '1');
            svgEl.appendChild(rect);
        });
    }

    function renderTrend(el, trend) {
        if (!el || !trend) return;
        el.className = `adm-kpi-trend ${trend.clase || ''}`.trim();
        el.innerHTML = '';

        const icon = document.createElement('i');
        icon.className = trend.icono || 'fa-solid fa-minus';
        el.appendChild(icon);
        el.appendChild(document.createTextNode(` ${trend.texto ?? '0%'}`));
    }

    function renderKpis(kpis) {
        if (!kpis) return;

        const activas = Number(kpis.activas ?? 0);
        const pendientes = Number(kpis.pendientes ?? 0);

        const elActivas = document.getElementById('modKpiActivas');
        const elPendientes = document.getElementById('modKpiPendientes');
        const elActivasTrend = document.getElementById('modKpiActivasTrend');
        const elPendTrend = document.getElementById('modKpiPendientesTrend');
        const elActivasFecha = document.getElementById('modKpiActivasFecha');
        const elPendFecha = document.getElementById('modKpiPendientesFecha');
        const elActivasSpark = document.getElementById('modKpiActivasSparkline');
        const elPendSpark = document.getElementById('modKpiPendientesSparkline');
        const elPendingBadge = document.getElementById('modPendingBadge');
        const elNotifCount = document.getElementById('modNotifCount');
        const elNotifPill = document.getElementById('modNotifBadgePill');
        const elNotifText = document.getElementById('modNotifPendingText');

        if (elActivas) elActivas.textContent = String(activas);
        if (elPendientes) elPendientes.textContent = String(pendientes);

        renderTrend(elActivasTrend, kpis.tendencia_activas);
        renderTrend(elPendTrend, kpis.tendencia_pendientes);

        if (elActivasFecha) {
            elActivasFecha.innerHTML = `<i class="fa-regular fa-clock"></i> ${kpis.ultima_activa || 'Sin registros recientes'}`;
        }
        if (elPendFecha) {
            elPendFecha.innerHTML = `<i class="fa-regular fa-clock"></i> ${kpis.ultima_pendiente || 'Sin registros recientes'}`;
        }

        renderSparkline(elActivasSpark, kpis.barras_activas);
        renderSparkline(elPendSpark, kpis.barras_pendientes);

        if (elPendingBadge) {
            elPendingBadge.innerHTML = `<i class="fa-regular fa-clock me-1"></i> ${pendientes} Por validar`;
        }
        if (elNotifCount) {
            elNotifCount.textContent = String(pendientes);
        }
        if (elNotifPill) {
            elNotifPill.textContent = pendientes === 1 ? '1 pendiente' : `${pendientes} pendientes`;
        }
        if (elNotifText) {
            elNotifText.textContent = `${pendientes} subastas por validar`;
        }
    }

    async function actualizar() {
        try {
            const url = `${endpoint}&_t=${Date.now()}`;
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
            });
            if (response.status === 401) {
                const errData = await response.json().catch(() => ({}));
                window.location.href = errData.redirect || `${baseUrl}views/auth/login.php${errData.expired ? '?reason=inactivity' : ''}`;
                return null;
            }
            if (!response.ok) return null;

            const data = await response.json();
            if (data && data.estado && data.kpis) {
                renderKpis(data.kpis);
                return data.kpis;
            }
        } catch (err) {
            console.error('Error al actualizar KPIs:', err);
        }
        return null;
    }

    window.ModeradorKpis = { actualizar, renderKpis };

    document.addEventListener('DOMContentLoaded', () => {
        setInterval(() => {
            if (document.hidden) return;
            actualizar();
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
})();
