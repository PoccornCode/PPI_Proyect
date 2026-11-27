document.addEventListener("DOMContentLoaded", () => {
    const lista = document.querySelector("[data-mis-pujas-url]");
    if (!lista) return;

    const endpoint = lista.getAttribute("data-mis-pujas-url");
    if (!endpoint) return;

    const RESULTADOS = {
        ganando: ["Vas ganando", "ok"],
        superada: ["Superada", "alerta"],
        ganada: ["Ganada", "ok"],
        perdida: ["No ganaste", "neutro"],
        cancelada: ["Cancelada", "neutro"],
    };

    const INTERVALO_MS = 5000;
    let consultando = false;

    function animarCambio(el) {
        if (!el || typeof el.animate !== "function") return;
        el.animate(
            [{ color: "#94aa34", opacity: 0.7 }, { opacity: 1 }],
            { duration: 600, easing: "ease-out" }
        );
    }

    function actualizarConteosTabs(pujas) {
        const conteo = {};
        pujas.forEach((p) => {
            conteo[p.resultado] = (conteo[p.resultado] || 0) + 1;
        });

        const totalEl = document.querySelector('[data-tab-count="todas"]');
        if (totalEl) totalEl.textContent = String(pujas.length);

        Object.keys(RESULTADOS).forEach((clave) => {
            const el = document.querySelector(`[data-tab-count="${clave}"]`);
            if (el) el.textContent = String(conteo[clave] || 0);
        });
    }

    function actualizarTarjeta(card, p) {
        const miPujaEl = card.querySelector("[data-mi-puja]");
        const actualEl = card.querySelector("[data-puja-actual]");
        const labelActualEl = card.querySelector("[data-label-actual]");
        const chipEl = card.querySelector("[data-chip-resultado]");
        const estadoEl = card.querySelector("[data-estado-tiempo]");
        const metaEl = card.querySelector("[data-meta-pujas]");
        const btnEl = card.querySelector("[data-btn-accion]");

        const nuevoMi = `$${p.mi_puja}`;
        if (miPujaEl && miPujaEl.textContent !== nuevoMi) {
            miPujaEl.textContent = nuevoMi;
            animarCambio(miPujaEl);
        }

        const nuevoActual = `$${p.puja_actual}`;
        if (actualEl && actualEl.textContent !== nuevoActual) {
            actualEl.textContent = nuevoActual;
            animarCambio(actualEl);
        }

        if (labelActualEl) {
            labelActualEl.textContent = p.abierta ? "Puja actual" : "Puja final";
        }

        if (metaEl) {
            const textoPujas = `${p.mis_pujas} ${p.mis_pujas === 1 ? "puja tuya" : "pujas tuyas"}`;
            metaEl.textContent = p.ultima ? `${textoPujas} · última el ${p.ultima}` : textoPujas;
        }

        const info = RESULTADOS[p.resultado];
        if (chipEl && info) {
            const [texto, color] = info;
            if (chipEl.textContent !== texto) {
                chipEl.textContent = texto;
                animarCambio(chipEl);
            }
            chipEl.className = `mis-pujas-chip mis-pujas-chip-${color}`;
        }

        if (estadoEl && !p.abierta && estadoEl.hasAttribute("data-countdown")) {
            estadoEl.removeAttribute("data-countdown");
            estadoEl.removeAttribute("data-countdown-prefix");
            estadoEl.className = "small text-second-ppi";
            estadoEl.textContent = "Subasta cerrada";
        }

        if (btnEl) {
            btnEl.textContent = p.resultado === "superada" ? "Volver a pujar" : "Ver subasta";
        }
    }

    async function refrescar() {
        if (consultando || document.hidden) return;
        consultando = true;
        try {
            const resp = await fetch(endpoint, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
                cache: "no-store",
            });
            if (!resp.ok) return;
            const data = await resp.json();
            if (!data || !data.estado || !Array.isArray(data.pujas)) return;

            actualizarConteosTabs(data.pujas);

            const mapa = new Map(data.pujas.map((p) => [String(p.id), p]));
            lista.querySelectorAll("[data-subasta-id]").forEach((card) => {
                const p = mapa.get(card.getAttribute("data-subasta-id"));
                if (p) actualizarTarjeta(card, p);
            });
        } catch (_) {
            // silencioso ante cortes momentaneos
        } finally {
            consultando = false;
        }
    }

    setInterval(refrescar, INTERVALO_MS);
    document.addEventListener("visibilitychange", () => {
        if (!document.hidden) refrescar();
    });
});
