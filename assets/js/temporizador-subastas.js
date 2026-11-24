(function () {
    let actualizandoHome = false;
    let ultimaFirmaHome = '';
    const preciosPrevios = new Map();

    function registrarPrecios(destacada, pronto, nuevas, animar) {
        const cambiados = new Set();
        const revisar = (item) => {
            if (!item) return;
            const prev = preciosPrevios.get(item.id);
            if (animar && prev !== undefined && prev !== item.precio) {
                cambiados.add(String(item.id));
            }
            preciosPrevios.set(item.id, item.precio);
        };
        revisar(destacada);
        if (Array.isArray(pronto)) pronto.forEach(revisar);
        if (Array.isArray(nuevas)) nuevas.forEach(revisar);
        return cambiados;
    }

    function resaltarPreciosCambiados(cambiados) {
        if (!cambiados.size) return;
        cambiados.forEach((id) => {
            document.querySelectorAll(`[data-precio-subasta="${id}"]`).forEach((el) => {
                if (typeof el.animate === 'function') {
                    el.animate(
                        [
                            { color: '#8fa870', opacity: 0.75 },
                            { opacity: 1 }
                        ],
                        { duration: 650, easing: 'ease-out' }
                    );
                }
            });
        });
    }

    function obtenerFirmaHome(destacada, pronto, nuevas) {
        const dFirma = destacada ? `${destacada.id}:${destacada.precio}` : 'none';
        const pFirma = Array.isArray(pronto)
            ? pronto.map((i) => `${i.id}:${i.precio}`).join('|')
            : '';
        const nFirma = Array.isArray(nuevas)
            ? nuevas.map((i) => `${i.id}:${i.precio}`).join('|')
            : '';
        return `${dFirma}__${pFirma}__${nFirma}`;
    }

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function escaparHtml(texto) {
        const div = document.createElement('div');
        div.textContent = texto ?? '';
        return div.innerHTML;
    }

    function renderDestacada(d) {
        const cont = document.getElementById('contenedor-destacada');
        if (!cont) return;

        if (!d) {
            cont.innerHTML = `
                <article class="card h-100 p-5 d-flex flex-column justify-content-center align-items-center text-center">
                    <i class="fas fa-gavel fa-2x text-second-ppi mb-3"></i>
                    <h2 id="titulo-destacada" class="fs-4 fw-bold text-main-ppi mb-2">No hay subastas activas en este momento</h2>
                    <p class="text-second-ppi mb-4" style="max-width: 420px;">Sé el primero en publicar un producto y abrir una nueva subasta en la plataforma.</p>
                    <a href="views/subastas/registro-producto.php" class="btn btn-primary px-4 py-2 fw-bold">Publicar producto</a>
                </article>`;
            return;
        }

        cont.innerHTML = `
            <article class="card h-100 overflow-hidden">
                <img src="${escaparHtml(d.imagen)}" class="foto-lote foto-lote-destacada" alt="${escaparHtml(d.titulo)}">
                <div class="p-4">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-7">
                            <p class="mb-1 small text-uppercase text-second-ppi">
                                <span class="text-third-ppi fw-bold">Destacada</span> &middot; ${escaparHtml(d.categoria)}
                            </p>
                            <h2 id="titulo-destacada" class="fs-3 fw-bold text-main-ppi">${escaparHtml(d.titulo)}</h2>
                            <p class="mb-0 text-second-ppi">${escaparHtml(d.descripcion)}</p>
                        </div>
                        <div class="col-md-5">
                            <span class="d-block small text-second-ppi">Puja actual</span>
                            <span class="d-block display-6 precio-ppi" data-precio-subasta="${escaparHtml(String(d.id))}">$${escaparHtml(d.precio)}</span>
                            <span class="d-block mb-3 tiempo-ppi"><i class="far fa-clock me-2"></i><span data-countdown="${escaparHtml(d.fecha_final_iso)}" data-countdown-prefix="Termina en ">Termina en ${escaparHtml(d.tiempo)}</span></span>
                            <a href="views/subasta.php?id=${encodeURIComponent(d.id)}" class="w-100 btn btn-primary py-2 fw-bold">Pujar ahora</a>
                        </div>
                    </div>
                </div>
            </article>`;
    }

    function renderPronto(lista) {
        const cont = document.getElementById('contenedor-pronto');
        if (!cont) return;

        if (!Array.isArray(lista) || !lista.length) {
            cont.innerHTML = `
                <aside class="card h-100 p-4 d-flex flex-column" aria-labelledby="titulo-pronto">
                    <h3 id="titulo-pronto" class="fs-5 fw-bold text-main-ppi mb-3">Terminan pronto</h3>
                    <div class="d-flex flex-column justify-content-center align-items-center text-center my-auto py-4">
                        <i class="far fa-clock fa-2x text-second-ppi mb-3"></i>
                        <p class="fw-bold text-main-ppi mb-1">Sin cierres próximos</p>
                        <p class="text-second-ppi small mb-0" style="max-width: 240px;">Aquí aparecerán las subastas activas que estén por finalizar.</p>
                    </div>
                </aside>`;
            return;
        }

        const itemsHtml = lista.map((item) => `
            <a href="views/subasta.php?id=${encodeURIComponent(item.id)}" class="d-flex align-items-center gap-3 py-3 separador-ppi text-decoration-none text-main-ppi">
                <img src="${escaparHtml(item.imagen)}" class="foto-lote-mini" alt="">
                <span class="d-flex flex-column">
                    <span class="fw-bold">${escaparHtml(item.titulo)}</span>
                    <span class="small text-second-ppi" data-precio-subasta="${escaparHtml(String(item.id))}">$${escaparHtml(item.precio)}</span>
                    <span class="small tiempo-ppi" data-countdown="${escaparHtml(item.fecha_final_iso)}" data-countdown-prefix="Termina en ">Termina en ${escaparHtml(item.tiempo)}</span>
                </span>
            </a>`).join('');

        cont.innerHTML = `
            <aside class="card h-100 p-4 d-flex flex-column" aria-labelledby="titulo-pronto">
                <h3 id="titulo-pronto" class="fs-5 fw-bold text-main-ppi mb-3">Terminan pronto</h3>
                ${itemsHtml}
                <a href="views/catalogo.php?estado=hoy" class="link-ppi d-inline p-0 mt-auto pt-3 fw-bold small">Ver todas las que terminan hoy &rarr;</a>
            </aside>`;
    }

    function renderNuevas(lista) {
        const cont = document.getElementById('contenedor-nuevas');
        if (!cont) return;

        if (!Array.isArray(lista) || !lista.length) {
            cont.innerHTML = `
                <div class="card p-4 text-center">
                    <p class="text-second-ppi mb-0">Aún no hay nuevas subastas activas.</p>
                </div>`;
            return;
        }

        const cardsHtml = lista.map((item) => `
            <div class="col">
                <article class="card h-100 overflow-hidden">
                    <img src="${escaparHtml(item.imagen)}" class="foto-lote" alt="${escaparHtml(item.titulo)}">
                    <div class="p-3 d-flex flex-column flex-grow-1">
                        <span class="small text-uppercase text-second-ppi">${escaparHtml(item.categoria)}</span>
                        <h3 class="fs-6 fw-bold text-main-ppi mt-1">${escaparHtml(item.titulo)}</h3>
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2 mb-3">
                            <span class="h5 mb-0 precio-ppi" data-precio-subasta="${escaparHtml(String(item.id))}">$${escaparHtml(item.precio)}</span>
                            <span class="small tiempo-ppi" data-countdown="${escaparHtml(item.fecha_final_iso)}">${escaparHtml(item.tiempo)}</span>
                        </div>
                        <a href="views/subasta.php?id=${encodeURIComponent(item.id)}" class="btn btn-contorno-ppi fw-bold">Ver subasta</a>
                    </div>
                </article>
            </div>`).join('');

        cont.innerHTML = `<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">${cardsHtml}</div>`;
    }

    async function refrescarSubastasHome() {
        if (actualizandoHome) return;
        if (!document.getElementById('contenedor-destacada')) return;

        actualizandoHome = true;
        try {
            const resp = await fetch(`controllers/subasta_controller.php?opcion=home&_t=${Date.now()}`, { cache: 'no-store' });
            if (!resp.ok) return;
            const data = await resp.json();
            if (data && data.estado) {
                const primeraVez = ultimaFirmaHome === '';
                const cambiados = registrarPrecios(data.destacada, data.pronto, data.nuevas, !primeraVez);
                const nuevaFirma = obtenerFirmaHome(data.destacada, data.pronto, data.nuevas);
                if (nuevaFirma !== ultimaFirmaHome) {
                    ultimaFirmaHome = nuevaFirma;
                    renderDestacada(data.destacada);
                    renderPronto(data.pronto);
                    renderNuevas(data.nuevas);
                    actualizarContadores();
                    resaltarPreciosCambiados(cambiados);
                }
            }
        } catch (err) {
            console.error('Error al actualizar subastas:', err);
        } finally {
            actualizandoHome = false;
        }
    }

    function actualizarContadores() {
        const elementos = document.querySelectorAll('[data-countdown]');
        if (!elementos.length) return;

        const ahora = Date.now();
        let algunaFinalizo = false;

        elementos.forEach((el) => {
            const fin = Date.parse(el.dataset.countdown);
            if (Number.isNaN(fin)) return;

            const diffSeg = Math.floor((fin - ahora) / 1000);
            const prefijo = el.dataset.countdownPrefix || '';

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
                el.textContent = `${prefijo}${dias} d ${pad(horas)} h`;
            } else {
                el.textContent = `${prefijo}${pad(horas)}:${pad(minutos)}:${pad(segundos)}`;
            }
        });

        if (algunaFinalizo) {
            setTimeout(refrescarSubastasHome, 1500);
        }
    }

    actualizarContadores();
    setInterval(actualizarContadores, 1000);

    // Sincronización automática cada 2s (solo si la pestaña está visible y solo redibuja si hubo cambios)
    setInterval(() => {
        if (!document.hidden) {
            refrescarSubastasHome();
        }
    }, 2000);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            refrescarSubastasHome();
        }
    });
})();
