(function () {
    let actualizandoHome = false;
    let ultimaFirmaHome = '';
    let actualizandoCatalogo = false;
    let ultimaFirmaCatalogo = '';
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
                            <button type="button" class="w-100 btn btn-primary py-2 fw-bold" data-puja-adan>Pujar ahora</button>
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

    function renderCatalogo(items, baseUrl, hayFiltros) {
        const cont = document.getElementById('contenedor-catalogo');
        if (!cont) return;

        if (!Array.isArray(items) || !items.length) {
            cont.innerHTML = `
                <div class="catalogo-vacio text-center">
                    <i class="fas fa-gavel fs-1 text-second-ppi opacity-50 mb-3"></i>
                    <h2 class="fs-4 fw-bold text-main-ppi mb-2">No encontramos subastas</h2>
                    <p class="text-second-ppi mb-4">
                        ${hayFiltros ? 'Prueba con otra categoría o quita algunos filtros.' : 'Todavía no hay subastas activas. ¡Sé el primero en publicar!'}
                    </p>
                    ${hayFiltros
                        ? `<a href="${escaparHtml(baseUrl)}views/catalogo.php" class="btn btn-contorno-ppi px-4 py-2 fw-semibold">Ver todo el catálogo</a>`
                        : `<a href="${escaparHtml(baseUrl)}views/subastas/registro-producto.php" class="btn btn-primary px-4 py-2 fw-semibold">Publicar producto</a>`}
                </div>`;
            return;
        }

        const cardsHtml = items.map((item) => {
            const pujas = Number(item.pujas) || 0;
            const condHtml = item.condicion
                ? `<span aria-hidden="true">&middot;</span><span class="catalogo-condicion">${escaparHtml(item.condicion)}</span>`
                : '';
            return `
                <div class="col">
                    <article class="catalogo-card h-100 d-flex flex-column">
                        <a href="${escaparHtml(baseUrl)}views/subasta.php?id=${encodeURIComponent(item.id)}" class="catalogo-card-thumb d-block text-decoration-none" tabindex="-1" aria-hidden="true">
                            <img src="${escaparHtml(baseUrl + item.imagen)}" alt="" loading="lazy" onerror="this.onerror=null; this.src='${escaparHtml(baseUrl)}assets/img/news-1.jpg';">
                        </a>
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <div class="d-flex align-items-center flex-wrap gap-2 mb-1 small text-second-ppi">
                                <span class="catalogo-categoria-label">${escaparHtml(item.categoria)}</span>
                                ${condHtml}
                            </div>
                            <h2 class="fs-5 fw-bold text-main-ppi mt-1 mb-2 catalogo-card-titulo">${escaparHtml(item.titulo)}</h2>
                            <div class="d-flex justify-content-between align-items-end gap-2 mt-auto pt-2">
                                <div>
                                    <span class="catalogo-meta-label d-block">${pujas > 0 ? 'Puja actual' : 'Precio inicial'}</span>
                                    <span class="h5 mb-0 precio-ppi" data-precio-subasta="${escaparHtml(String(item.id))}">$${escaparHtml(item.precio)}</span>
                                </div>
                                <div class="text-end">
                                    <span class="catalogo-meta-label d-block">Tiempo restante</span>
                                    <span class="small tiempo-ppi">
                                        <i class="far fa-clock me-1"></i><span data-countdown="${escaparHtml(item.fecha_final_iso)}">${escaparHtml(item.tiempo)}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="small text-second-ppi mt-1 mb-3">
                                ${pujas} ${pujas === 1 ? 'puja registrada' : 'pujas registradas'}
                            </div>
                            <a href="${escaparHtml(baseUrl)}views/subasta.php?id=${encodeURIComponent(item.id)}" class="btn btn-contorno-ppi w-100 fw-semibold py-2 mt-auto">
                                Ver subasta <i class="fas fa-arrow-right ms-1 small"></i>
                            </a>
                        </div>
                    </article>
                </div>`;
        }).join('');

        cont.innerHTML = `<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">${cardsHtml}</div>`;
    }

    function obtenerParamsCatalogo(reiniciarPagina) {
        const form = document.getElementById('form-catalogo-filtros');
        const params = new URLSearchParams();
        if (form) {
            const fd = new FormData(form);
            fd.forEach((val, key) => {
                const limpio = String(val).trim();
                if (limpio !== '' && !(key === 'orden' && limpio === 'pronto')) {
                    params.set(key, limpio);
                }
            });
        } else {
            new URLSearchParams(window.location.search).forEach((val, key) => {
                params.set(key, val);
            });
        }
        if (!reiniciarPagina) {
            const actual = new URLSearchParams(window.location.search).get('pagina');
            if (actual && actual !== '1') {
                params.set('pagina', actual);
            }
        }
        return params;
    }

    async function refrescarCatalogo(forzar = false) {
        if (actualizandoCatalogo && !forzar) return;
        const cont = document.getElementById('contenedor-catalogo');
        if (!cont) return;

        const baseUrl = cont.dataset.baseUrl || '../';
        const params = obtenerParamsCatalogo(forzar);
        const hayFiltros = Boolean(params.get('q') || params.get('categoria') || params.get('estado'));
        cont.dataset.hayFiltros = hayFiltros ? '1' : '0';

        if (forzar) {
            const nuevaUrl = `${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
            window.history.replaceState(null, '', nuevaUrl);
        }

        const fetchParams = new URLSearchParams(params);
        fetchParams.set('opcion', 'listar');
        fetchParams.set('_t', String(Date.now()));

        actualizandoCatalogo = true;
        try {
            const resp = await fetch(`${baseUrl}controllers/catalogo_controller.php?${fetchParams.toString()}`, { cache: 'no-store' });
            if (!resp.ok) return;
            const data = await resp.json();
            if (data && data.estado) {
                const items = Array.isArray(data.items) ? data.items : [];
                const primeraVez = ultimaFirmaCatalogo === '';
                const cambiados = registrarPrecios(null, items, [], !primeraVez && !forzar);
                const nuevaFirma = `${params.toString()}__${data.total}__${items.map((i) => `${i.id}:${i.precio}:${i.pujas}`).join('|')}`;

                if (forzar || nuevaFirma !== ultimaFirmaCatalogo) {
                    ultimaFirmaCatalogo = nuevaFirma;
                    const totalEl = document.getElementById('catalogo-total');
                    const totalTxtEl = document.getElementById('catalogo-total-texto');
                    if (totalEl) totalEl.textContent = String(data.total);
                    if (totalTxtEl) totalTxtEl.textContent = data.total === 1 ? 'subasta activa' : 'subastas activas';

                    if (forzar || !primeraVez) {
                        renderCatalogo(items, baseUrl, hayFiltros);
                        actualizarContadores();
                        if (!forzar) {
                            resaltarPreciosCambiados(cambiados);
                        }
                    }
                }
            }
        } catch (err) {
            console.error('Error al actualizar catálogo:', err);
        } finally {
            actualizandoCatalogo = false;
        }
    }

    function iniciarFiltrosCatalogo() {
        const form = document.getElementById('form-catalogo-filtros');
        if (!form) return;

        let debounceTimer = null;
        const inputQ = form.querySelector('#q');
        const selectCat = form.querySelector('#categoria');
        const selectOrden = form.querySelector('#orden');

        if (inputQ) {
            inputQ.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => refrescarCatalogo(true), 250);
            });
        }
        if (selectCat) {
            selectCat.addEventListener('change', () => refrescarCatalogo(true));
        }
        if (selectOrden) {
            selectOrden.addEventListener('change', () => refrescarCatalogo(true));
        }
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            clearTimeout(debounceTimer);
            refrescarCatalogo(true);
        });
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
            setTimeout(() => {
                refrescarSubastasHome();
                refrescarCatalogo();
            }, 1500);
        }
    }

    actualizarContadores();
    iniciarFiltrosCatalogo();
    refrescarCatalogo();
    setInterval(actualizarContadores, 1000);

    // Sincronización automática cada 2s (solo si la pestaña está visible y solo redibuja si hubo cambios)
    setInterval(() => {
        if (!document.hidden) {
            refrescarSubastasHome();
            refrescarCatalogo();
        }
    }, 2000);

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-puja-adan]');
        if (btn && typeof mostrarToast === 'function') {
            mostrarToast('info', 'Pujar ahora', 'Al pujar se enviará a la lógica de pujas de Adán.', 7000);
        }
    });

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            refrescarSubastasHome();
            refrescarCatalogo();
        }
    });
})();
