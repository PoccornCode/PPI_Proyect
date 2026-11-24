(function () {
    'use strict';

    const REFRESH_INTERVAL_MS = 20000;
    const CIRCUMFERENCE = 402.12;
    const baseUrl = window.ADM_BASE_URL || '';
    const rangeSelect = document.getElementById('admActivityRange');

    function renderActivityChart(actividad) {
        const svg = document.getElementById('admActivitySvg');
        const tooltip = document.getElementById('admActivityTooltip');
        if (!svg || !actividad) return;

        const labels = Array.isArray(actividad.labels) ? actividad.labels : [];
        const values = Array.isArray(actividad.values) ? actividad.values.map(Number) : [];
        if (labels.length === 0 || values.length === 0) return;

        const W = 560;
        const H = 220;
        const padL = 38;
        const padR = 20;
        const padT = 16;
        const padB = 34;
        const plotW = W - padL - padR;
        const plotH = H - padT - padB;

        const rawMax = Math.max(5, ...values);
        const step = Math.max(1, Math.ceil(rawMax / 5));
        const maxVal = step * 5;
        let html = '';

        for (let i = 0; i <= 5; i++) {
            const tick = step * i;
            const y = padT + plotH - (tick / maxVal) * plotH;
            html += `<line x1="${padL}" y1="${y}" x2="${W - padR}" y2="${y}" stroke="#272e21" stroke-width="1" />`;
            html += `<text x="${padL - 10}" y="${y + 4}" text-anchor="end" fill="#9aa392" font-size="11">${tick}</text>`;
        }

        const coords = values.map((val, idx) => {
            const x = padL + (values.length > 1 ? (idx / (values.length - 1)) * plotW : plotW / 2);
            const y = padT + plotH - (val / maxVal) * plotH;
            return { x, y, val, label: labels[idx] || '' };
        });

        const areaPath =
            `M ${coords[0].x} ${padT + plotH} ` +
            coords.map((p) => `L ${p.x} ${p.y}`).join(' ') +
            ` L ${coords[coords.length - 1].x} ${padT + plotH} Z`;

        html += `
            <defs>
                <linearGradient id="admLineGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#a7cc3b" stop-opacity="0.22" />
                    <stop offset="100%" stop-color="#a7cc3b" stop-opacity="0" />
                </linearGradient>
            </defs>
            <path d="${areaPath}" fill="url(#admLineGrad)" />
        `;

        const linePoints = coords.map((p) => `${p.x},${p.y}`).join(' ');
        html += `<polyline points="${linePoints}" fill="none" stroke="#a7cc3b" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round" />`;

        coords.forEach((p) => {
            html += `<text x="${p.x}" y="${H - 8}" text-anchor="middle" fill="#c7cec0" font-size="11.5">${p.label}</text>`;
            html += `
                <circle cx="${p.x}" cy="${p.y}" r="4" fill="#a7cc3b" stroke="#161913" stroke-width="2"
                    class="adm-chart-dot" data-label="${p.label}" data-val="${p.val}" style="cursor:pointer;" />
            `;
        });

        svg.innerHTML = html;

        if (tooltip) {
            svg.querySelectorAll('.adm-chart-dot').forEach((dot) => {
                dot.addEventListener('mouseenter', () => {
                    const rect = svg.getBoundingClientRect();
                    const cx = parseFloat(dot.getAttribute('cx'));
                    const cy = parseFloat(dot.getAttribute('cy'));
                    const val = Number(dot.dataset.val || 0);
                    tooltip.textContent = `${dot.dataset.label}: ${val} ${val === 1 ? 'evento' : 'eventos'}`;
                    tooltip.style.left = `${(cx / W) * rect.width}px`;
                    tooltip.style.top = `${(cy / H) * rect.height}px`;
                    tooltip.style.opacity = '1';
                });
                dot.addEventListener('mouseleave', () => {
                    tooltip.style.opacity = '0';
                });
            });
        }
    }

    function renderDistribucionDonut(distribucion) {
        const svg = document.getElementById('admDistribucionSvg');
        const legend = document.getElementById('admDistribucionLegend');
        if (!svg || !legend || !distribucion) return;

        const total = Number(distribucion.total || 0);
        const items = Array.isArray(distribucion.items) ? distribucion.items : [];

        let circlesHtml = '<circle cx="90" cy="90" r="64" fill="none" stroke="#252c1f" stroke-width="26" />';
        if (total > 0) {
            let offset = 0;
            items.forEach((item) => {
                const count = Number(item.count || 0);
                if (count <= 0) return;
                const seg = (count / total) * CIRCUMFERENCE;
                circlesHtml += `
                    <circle cx="90" cy="90" r="64" fill="none"
                        stroke="${item.color}"
                        stroke-width="26"
                        stroke-dasharray="${seg.toFixed(2)} ${CIRCUMFERENCE}"
                        stroke-dashoffset="${(-offset).toFixed(2)}"
                        transform="rotate(-90 90 90)" />
                `;
                offset += seg;
            });
        }
        svg.innerHTML = circlesHtml;

        legend.innerHTML = items.map((item) => `
            <li>
                <span class="adm-legend-label">
                    <span class="adm-legend-dot" style="background:${item.color};"></span>
                    ${item.label}
                </span>
                <strong title="${Number(item.count || 0)} subastas">${Number(item.porcentaje || 0)}%</strong>
            </li>
        `).join('');
    }

    async function actualizar() {
        try {
            const rango = encodeURIComponent(rangeSelect?.value || '7d');
            const url = `${baseUrl}controllers/admin_controller.php?opcion=graficos&rango=${rango}&_t=${Date.now()}`;
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
            if (data?.estado && data.graficos) {
                renderActivityChart(data.graficos.actividad);
                renderDistribucionDonut(data.graficos.distribucion);
                return data.graficos;
            }
        } catch (err) {
            console.error('Error al actualizar gráficos de administración:', err);
        }
        return null;
    }

    if (window.ADM_GRAFICOS_INICIO?.actividad) {
        renderActivityChart(window.ADM_GRAFICOS_INICIO.actividad);
    }

    rangeSelect?.addEventListener('change', actualizar);

    document.addEventListener('DOMContentLoaded', () => {
        setInterval(() => {
            if (!document.hidden) {
                actualizar();
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

    window.AdminGraficos = { actualizar, renderActivityChart, renderDistribucionDonut };
})();
