(function () {
    'use strict';

    const REFRESH_INTERVAL_MS = 2000;
    const baseUrl = window.ADM_BASE_URL || '';
    const rangeSelect = document.getElementById('admKpiRange');

    const KPI_MAP = [
        { key: 'usuarios',   id: 'Usuarios',   trend: 'tendencia_usuarios',   fecha: 'ultimo_usuario',   barras: 'barras_usuarios' },
        { key: 'pujas',      id: 'Pujas',      trend: 'tendencia_pujas',      fecha: 'ultima_puja',      barras: 'barras_pujas' },
        { key: 'activas',    id: 'Activas',    trend: 'tendencia_activas',    fecha: 'ultima_activa',    barras: 'barras_activas' },
        { key: 'pendientes', id: 'Pendientes', trend: 'tendencia_pendientes', fecha: 'ultima_pendiente', barras: 'barras_pendientes' }
    ];

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

        KPI_MAP.forEach((item) => {
            const valEl = document.getElementById(`admKpi${item.id}`);
            const trendEl = document.getElementById(`admKpi${item.id}Trend`);
            const fechaEl = document.getElementById(`admKpi${item.id}Fecha`);
            const sparkEl = document.getElementById(`admKpi${item.id}Sparkline`);

            if (valEl) {
                valEl.textContent = Number(kpis[item.key] ?? 0).toLocaleString('en-US');
            }
            renderTrend(trendEl, kpis[item.trend]);
            if (fechaEl) {
                fechaEl.innerHTML = `<i class="fa-regular fa-clock"></i> ${kpis[item.fecha] || 'Sin registros recientes'}`;
            }
            renderSparkline(sparkEl, kpis[item.barras]);
        });
    }

    async function actualizar() {
        try {
            const rango = encodeURIComponent(rangeSelect?.value || '30d');
            const url = `${baseUrl}controllers/admin_controller.php?opcion=kpis&rango=${rango}&_t=${Date.now()}`;
            const response = await fetch(url, {
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
            if (data?.estado && data.kpis) {
                renderKpis(data.kpis);
                return data.kpis;
            }
        } catch (err) {
            console.error('Error al actualizar KPIs de administración:', err);
        }
        return null;
    }

    rangeSelect?.addEventListener('change', actualizar);

    document.addEventListener('DOMContentLoaded', () => {
        let enCurso = false;
        setInterval(async () => {
            if (document.hidden || enCurso) return;
            enCurso = true;
            try { await actualizar(); } finally { enCurso = false; }
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

    window.AdminKpis = { actualizar, renderKpis };
})();
