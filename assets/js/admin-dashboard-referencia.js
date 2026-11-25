/**
 * Gavel.net — Panel de Administración (Interacciones de Referencia)
 */
(function () {
    'use strict';

    const activateTab = (tab) => window.AdminDashboard?.activateTab(tab);
    const showToast = (msg) => window.AdminDashboard?.notify(msg);

    // =========================================================================
    // FILTROS EN VIVO — SUBASTAS (REFERENCIA)
    // =========================================================================
    const auctionSearchInput = document.getElementById('admAuctionSearch');
    const auctionCatFilter = document.getElementById('admAuctionCatFilter');
    const auctionsTableBody = document.getElementById('admAuctionsTbody');
    const auctionsCountLabel = document.getElementById('admAuctionsCount');

    function filterAuctionsTable() {
        if (!auctionsTableBody) return;
        const q = (auctionSearchInput?.value || '').trim().toLowerCase();
        const cat = (auctionCatFilter?.value || '').toLowerCase();
        let visible = 0;

        auctionsTableBody.querySelectorAll('tr').forEach((row) => {
            const title = (row.dataset.title || '').toLowerCase();
            const rowCat = (row.dataset.category || '').toLowerCase();
            const matchText = !q || title.includes(q) || rowCat.includes(q);
            const matchCat = !cat || rowCat === cat;
            const show = matchText && matchCat;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (auctionsCountLabel) {
            auctionsCountLabel.textContent = `Mostrando ${visible} subasta${visible === 1 ? '' : 's'}`;
        }
    }

    auctionSearchInput?.addEventListener('input', filterAuctionsTable);
    auctionCatFilter?.addEventListener('change', filterAuctionsTable);

    // =========================================================================
    // CATEGORÍAS — INTERACCIONES DE DISEÑO / REFERENCIA (SIN BACKEND)
    // =========================================================================
    const catCfg = window.ADM_CATEGORIES_DEMO;
    if (catCfg && document.getElementById('admCatGrid')) {
        const baseUrl = catCfg.baseUrl || '';
        let cats = Array.isArray(catCfg.categorias) ? catCfg.categorias.slice() : [];
        let catFilter = 'todas';
        let catSearch = '';
        let pendingDeleteId = 0;
        let previewDataUrl = '';

        const gridEl = document.getElementById('admCatGrid');
        const emptyEl = document.getElementById('admCatEmpty');
        const searchEl = document.getElementById('admCatSearch');
        const filterBtns = document.querySelectorAll('[data-cat-filter]');
        const resetBtn = document.getElementById('btnCatResetFilters');
        const btnNueva = document.getElementById('btnNuevaCategoria');

        const cTodas = document.getElementById('catCountTodas');
        const cPub = document.getElementById('catCountPublicadas');
        const cOff = document.getElementById('catCountDesactivadas');

        const modalCrudEl = document.getElementById('modalCategoriaCrud');
        const formCrud = document.getElementById('formCategoriaCrud');
        const modalTitle = document.getElementById('catModalTitleText');
        const inputId = document.getElementById('catFormId');
        const inputNombre = document.getElementById('catFormNombre');
        const inputDesc = document.getElementById('catFormDescripcion');
        const descCounter = document.getElementById('catDescCounter');
        const inputEstado = document.getElementById('catFormEstado');
        const estadoTitle = document.getElementById('catEstadoTitle');
        const estadoHelp = document.getElementById('catEstadoHelp');
        const btnGuardarText = document.getElementById('btnGuardarCategoriaText');

        const dropzone = document.getElementById('catImageDropzone');
        const inputImagen = document.getElementById('catFormImagen');
        const previewWrap = document.getElementById('catImagePreviewWrap');
        const previewImg = document.getElementById('catImagePreview');
        const placeholderWrap = document.getElementById('catImagePlaceholder');

        const modalDeleteEl = document.getElementById('modalEliminarCategoria');
        const deleteNameEl = document.getElementById('catDeleteName');
        const btnConfirmDelete = document.getElementById('btnConfirmarEliminarCat');

        const getCrudModal = () => modalCrudEl && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(modalCrudEl) : null;
        const getDeleteModal = () => modalDeleteEl && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(modalDeleteEl) : null;

        const esc = (s) => String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        const imgSrc = (r) => !r ? baseUrl + 'assets/img/cat-coleccionables.jpg' : (/^(https?:|data:)/i.test(r) ? r : baseUrl + r.replace(/^\/+/, ''));

        function syncCounters() {
            const pub = cats.filter((c) => Number(c.estado) === 1).length;
            if (cTodas) cTodas.textContent = cats.length;
            if (cPub) cPub.textContent = pub;
            if (cOff) cOff.textContent = cats.length - pub;
        }

        function renderCats() {
            syncCounters();
            const q = catSearch.trim().toLowerCase();
            const list = cats.filter((c) => {
                const pub = Number(c.estado) === 1;
                if (catFilter === 'publicadas' && !pub) return false;
                if (catFilter === 'desactivadas' && pub) return false;
                if (!q) return true;
                return (c.nombre || '').toLowerCase().includes(q) || (c.descripcion || '').toLowerCase().includes(q);
            });

            if (list.length === 0) {
                gridEl.innerHTML = '';
                gridEl.classList.add('d-none');
                emptyEl?.classList.remove('d-none');
                return;
            }
            emptyEl?.classList.add('d-none');
            gridEl.classList.remove('d-none');

            gridEl.innerHTML = list.map((c) => {
                const pub = Number(c.estado) === 1;
                const sub = Number(c.total_subastas || 0);
                return `
                    <article class="adm-cat-card-v2 ${pub ? '' : 'is-inactive'}" data-id="${c.id_categoria}">
                        <div class="adm-cat-cover">
                            <img src="${esc(imgSrc(c.imagen))}" alt="${esc(c.nombre)}" loading="lazy">
                        </div>
                        <div class="adm-cat-body">
                            <div class="adm-cat-meta-row">
                                <span class="adm-cat-status ${pub ? 'is-published' : 'is-draft'}">
                                    <span class="adm-cat-status-dot"></span>
                                    ${pub ? 'Publicada' : 'Desactivada'}
                                </span>
                                <span class="adm-cat-auctions-count">${sub} ${sub === 1 ? 'subasta' : 'subastas'}</span>
                            </div>
                            <h3 class="adm-cat-title">${esc(c.nombre)}</h3>
                            <p class="adm-cat-desc">${esc((c.descripcion || '').trim() || 'Sin descripción registrada.')}</p>
                        </div>
                        <div class="adm-cat-footer">
                            <button type="button" class="adm-cat-btn-toggle ${pub ? 'is-deactivate' : 'is-publish'}" data-cat-action="toggle" data-id="${c.id_categoria}">
                                <i class="fa-solid ${pub ? 'fa-eye-slash' : 'fa-globe'}"></i>
                                <span>${pub ? 'Desactivar' : 'Publicar'}</span>
                            </button>
                            <div class="adm-cat-icon-actions">
                                <button type="button" class="adm-cat-icon-btn adm-cat-icon-btn--edit" title="Editar categoría" data-cat-action="edit" data-id="${c.id_categoria}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button type="button" class="adm-cat-icon-btn adm-cat-icon-btn--delete" title="Eliminar categoría" data-cat-action="delete" data-id="${c.id_categoria}">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </article>
                `;
            }).join('');
        }

        function setPreview(src) {
            previewDataUrl = src || '';
            if (src) {
                previewImg.src = src;
                previewWrap?.classList.remove('d-none');
                placeholderWrap?.classList.add('d-none');
            } else {
                previewImg.src = '';
                previewWrap?.classList.add('d-none');
                placeholderWrap?.classList.remove('d-none');
            }
        }

        function syncModalEstado() {
            const isEdit = Number(inputId?.value || 0) > 0;
            const pub = Boolean(inputEstado?.checked);
            if (estadoTitle) estadoTitle.textContent = pub ? 'Publicar en el sitio público' : 'Guardar como desactivada';
            if (estadoHelp) estadoHelp.textContent = pub ? 'Visible en el catálogo, menú y registro de productos.' : 'Oculta en el sitio público hasta que decidas publicarla.';
            if (btnGuardarText) btnGuardarText.textContent = isEdit ? 'Guardar cambios (demo)' : (pub ? 'Crear y publicar (demo)' : 'Guardar borrador (demo)');
        }

        btnNueva?.addEventListener('click', () => {
            formCrud?.reset();
            if (inputImagen) inputImagen.value = '';
            setPreview('');
            if (inputId) inputId.value = '0';
            if (inputEstado) inputEstado.checked = true;
            if (modalTitle) modalTitle.textContent = 'Nueva categoría';
            if (descCounter) descCounter.textContent = '0/255';
            syncModalEstado();
            getCrudModal()?.show();
        });

        gridEl.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-cat-action]');
            if (!btn) return;
            const id = Number(btn.dataset.id || 0);
            const cat = cats.find((x) => Number(x.id_categoria) === id);
            if (!cat) return;

            if (btn.dataset.catAction === 'toggle') {
                cat.estado = Number(cat.estado) === 1 ? 0 : 1;
                renderCats();
                showToast(cat.estado === 1 ? `"${cat.nombre}" publicada (vista de referencia).` : `"${cat.nombre}" desactivada (vista de referencia).`);
            } else if (btn.dataset.catAction === 'edit') {
                if (inputId) inputId.value = String(cat.id_categoria);
                if (inputNombre) inputNombre.value = cat.nombre || '';
                if (inputDesc) inputDesc.value = cat.descripcion || '';
                if (inputEstado) inputEstado.checked = Number(cat.estado) === 1;
                if (modalTitle) modalTitle.textContent = 'Editar categoría';
                if (descCounter) descCounter.textContent = `${(cat.descripcion || '').length}/255`;
                setPreview(imgSrc(cat.imagen));
                syncModalEstado();
                getCrudModal()?.show();
            } else if (btn.dataset.catAction === 'delete') {
                pendingDeleteId = id;
                if (deleteNameEl) deleteNameEl.textContent = cat.nombre;
                getDeleteModal()?.show();
            }
        });

        formCrud?.addEventListener('submit', (e) => {
            e.preventDefault();
            const id = Number(inputId?.value || 0);
            const nombre = (inputNombre?.value || '').trim();
            const descripcion = (inputDesc?.value || '').trim();
            const estado = inputEstado?.checked ? 1 : 0;
            if (!nombre) return;

            if (id > 0) {
                const cat = cats.find((x) => Number(x.id_categoria) === id);
                if (cat) {
                    cat.nombre = nombre;
                    cat.descripcion = descripcion;
                    cat.estado = estado;
                    if (previewDataUrl) cat.imagen = previewDataUrl;
                }
                showToast(`"${nombre}" actualizada (solo diseño de referencia).`);
            } else {
                const nextId = cats.reduce((m, c) => Math.max(m, Number(c.id_categoria)), 0) + 1;
                cats.unshift({
                    id_categoria: nextId,
                    nombre,
                    descripcion,
                    imagen: previewDataUrl || 'assets/img/cat-coleccionables.jpg',
                    estado,
                    total_subastas: 0,
                    total_productos: 0
                });
                showToast(`"${nombre}" agregada (solo diseño de referencia).`);
            }
            renderCats();
            getCrudModal()?.hide();
        });

        btnConfirmDelete?.addEventListener('click', () => {
            cats = cats.filter((c) => Number(c.id_categoria) !== pendingDeleteId);
            pendingDeleteId = 0;
            renderCats();
            getDeleteModal()?.hide();
            showToast('Categoría eliminada (solo diseño de referencia).');
        });

        searchEl?.addEventListener('input', () => { catSearch = searchEl.value || ''; renderCats(); });
        filterBtns.forEach((b) => b.addEventListener('click', () => {
            catFilter = b.dataset.catFilter || 'todas';
            filterBtns.forEach((x) => { x.classList.toggle('active', x === b); x.setAttribute('aria-selected', x === b ? 'true' : 'false'); });
            renderCats();
        }));
        resetBtn?.addEventListener('click', () => {
            catSearch = '';
            if (searchEl) searchEl.value = '';
            catFilter = 'todas';
            filterBtns.forEach((x) => { const a = x.dataset.catFilter === 'todas'; x.classList.toggle('active', a); x.setAttribute('aria-selected', a ? 'true' : 'false'); });
            renderCats();
        });

        dropzone?.addEventListener('click', () => inputImagen?.click());
        inputImagen?.addEventListener('change', () => {
            const f = inputImagen.files?.[0];
            if (!f) return;
            const reader = new FileReader();
            reader.onload = (ev) => setPreview(ev.target?.result || '');
            reader.readAsDataURL(f);
        });
        inputDesc?.addEventListener('input', () => { if (descCounter) descCounter.textContent = `${inputDesc.value.length}/255`; });
        inputEstado?.addEventListener('change', syncModalEstado);
    }

    // =========================================================================
    // REPORTES — DISEÑO INTERACTIVO DE REFERENCIA (SIN BACKEND)
    // =========================================================================
    const repSection = document.getElementById('sec-reportes');
    if (repSection) {
        const repPeriodSelect = document.getElementById('admRepPeriod');
        const repPeriodSubtitle = document.getElementById('admRepPeriodSubtitle');
        const repStripEl = document.getElementById('admRepStrip');
        const repTabs = repSection.querySelectorAll('[data-rep-tab]');
        const repPanes = repSection.querySelectorAll('.adm-rep-pane');
        const repStatusChips = repSection.querySelectorAll('[data-rep-status]');
        const repPrintBtn = document.getElementById('admRepPrintBtn');
        const repCsvBtn = document.getElementById('admRepCsvBtn');

        let activeRepTab = 'subastas';
        let activeRepPeriod = '30d';
        let activeAuctionFilter = 'todas';

        const PERIOD_META = {
            '7d':  { label: 'Resumen del 17 al 23 de nov. de 2026', factor: 0.28 },
            '30d': { label: 'Resumen del 24 de oct. al 23 de nov. de 2026', factor: 1 },
            '90d': { label: 'Resumen del 26 de ago. al 23 de nov. de 2026', factor: 2.75 }
        };

        const AUCTIONS_DATA = [
            { lote: 'Reloj de bolsillo de latón, siglo XIX', cat: 'Coleccionables', vendedor: 'carlos_m', inicio: 185, final: 310, pujas: 14, estado: 'finalizada', cierre: '21 nov. 2026' },
            { lote: 'Cámara Réflex Canon EOS 80D (Cuerpo)', cat: 'Tecnología', vendedor: 'andrea_sv', inicio: 350, final: 495, pujas: 11, estado: 'finalizada', cierre: '19 nov. 2026' },
            { lote: 'Guitarra Acústica Walden D310e', cat: 'Instrumentos Musicales', vendedor: 'diego_r', inicio: 165, final: 240, pujas: 9, estado: 'activa', cierre: 'En 18 h' },
            { lote: 'Anillo de plata 925 con ópalo natural', cat: 'Joyería', vendedor: 'sofia_l', inicio: 88, final: 152, pujas: 12, estado: 'finalizada', cierre: '16 nov. 2026' },
            { lote: 'Colección de 4 Libros Antiguos en Piel', cat: 'Libros', vendedor: 'usuario_demo', inicio: 92, final: 140, pujas: 7, estado: 'activa', cierre: 'En 2 días' },
            { lote: 'Lámpara de mesa de bronce vintage', cat: 'Hogar', vendedor: 'elena_v', inicio: 38.5, final: 38.5, pujas: 0, estado: 'sin_ofertas', cierre: '14 nov. 2026' },
            { lote: 'Álbum de Monedas Antiguas (24 piezas)', cat: 'Coleccionables', vendedor: 'carlos_m', inicio: 140, final: 140, pujas: 0, estado: 'pendiente', cierre: 'En revisión' },
            { lote: 'Óleo sobre lienzo, paisaje firmado', cat: 'Arte', vendedor: 'mateo_g', inicio: 95, final: 175, pujas: 8, estado: 'finalizada', cierre: '11 nov. 2026' }
        ];

        const CATEGORIES_DATA = [
            { nombre: 'Coleccionables', lotes: 14, cerrados: 11, pujas: 98, volumen: 2840 },
            { nombre: 'Tecnología', lotes: 11, cerrados: 8, pujas: 74, volumen: 2310 },
            { nombre: 'Joyería', lotes: 9, cerrados: 7, pujas: 61, volumen: 1690 },
            { nombre: 'Instrumentos Musicales', lotes: 5, cerrados: 4, pujas: 39, volumen: 1180 },
            { nombre: 'Arte', lotes: 7, cerrados: 5, pujas: 34, volumen: 940 },
            { nombre: 'Libros', lotes: 4, cerrados: 3, pujas: 22, volumen: 420 },
            { nombre: 'Hogar', lotes: 3, cerrados: 2, pujas: 14, volumen: 265 }
        ];

        const USERS_DATA = [
            { usuario: '@carlos_m', rol: 'Usuario', ubicacion: 'San Salvador', lotes: 9, pujas: 34, estado: 'Activo' },
            { usuario: '@andrea_sv', rol: 'Usuario', ubicacion: 'Santa Tecla', lotes: 6, pujas: 28, estado: 'Activo' },
            { usuario: '@sofia_l', rol: 'Usuario', ubicacion: 'Antiguo Cuscatlán', lotes: 5, pujas: 21, estado: 'Activo' },
            { usuario: '@moderador_demo', rol: 'Moderador', ubicacion: 'San Salvador', lotes: 0, pujas: 4, estado: 'Activo' },
            { usuario: '@diego_r', rol: 'Usuario', ubicacion: 'Santa Ana', lotes: 4, pujas: 19, estado: 'Activo' },
            { usuario: '@jhonatan_p', rol: 'Usuario', ubicacion: 'San Miguel', lotes: 1, pujas: 2, estado: 'Suspendido' }
        ];

        const CONTACTS_DATA = [
            { asunto: 'Consulta sobre verificación de artículo antiguo', remitente: 'lucia.mendez@correo.com', fecha: 'Hoy, 2:15 p. m.', atendido: false },
            { asunto: 'Duda con incremento mínimo en subasta #24', remitente: 'roberto.diaz@correo.com', fecha: 'Ayer, 6:40 p. m.', atendido: false },
            { asunto: 'Solicitud para corregir categoría de publicación', remitente: 'andrea_sv@correo.com', fecha: '20 nov.', atendido: true },
            { asunto: 'Confirmación de cierre y contacto con vendedor', remitente: 'carlos_m@correo.com', fecha: '18 nov.', atendido: true }
        ];

        const STATUS_LABELS = {
            finalizada: 'Finalizada',
            activa: 'Activa',
            pendiente: 'Pendiente',
            sin_ofertas: 'Sin ofertas',
            rechazada: 'Rechazada'
        };

        const fmtUsd = (n) => '$' + Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const fmtInt = (n) => Math.round(Number(n || 0)).toLocaleString('en-US');

        function renderRepStrip() {
            if (!repStripEl) return;
            const f = PERIOD_META[activeRepPeriod].factor;
            const strips = {
                subastas: [
                    { label: 'Monto vendido', val: fmtUsd(9645 * f), note: '+14% frente al periodo anterior', cls: 'is-up' },
                    { label: 'Subastas cerradas', val: fmtInt(40 * f), note: `${fmtInt(34 * f)} con ganador · ${fmtInt(6 * f)} sin ofertas`, cls: '' },
                    { label: 'Tasa de cierre exitoso', val: '85%', note: '+4 pts respecto al tramo previo', cls: 'is-up' },
                    { label: 'Pujas promedio por subasta', val: '8.4', note: 'Incremento medio de +42% sobre inicio', cls: '' }
                ],
                categorias: [
                    { label: 'Categorías con movimiento', val: '8 de 10', note: '2 desactivadas en el catálogo', cls: '' },
                    { label: 'Categoría líder', val: 'Coleccionables', note: `${fmtUsd(2840 * f)} en ventas`, cls: 'is-up' },
                    { label: 'Mayor puja promedio', val: 'Tecnología', note: '$288.75 por subasta cerrada', cls: '' },
                    { label: 'Total de pujas', val: fmtInt(342 * f), note: 'Concentradas en 5 categorías principales', cls: '' }
                ],
                usuarios: [
                    { label: 'Nuevas cuentas', val: fmtInt(64 * f), note: '+11% frente al periodo anterior', cls: 'is-up' },
                    { label: 'Cuentas activas', val: '96.4%', note: '3 cuentas suspendidas actualmente', cls: '' },
                    { label: 'Usuarios que pujaron', val: fmtInt(118 * f), note: 'Participación recurrente del 62%', cls: 'is-up' },
                    { label: 'Vendedores activos', val: fmtInt(29 * f), note: 'Con al menos 1 subasta publicada', cls: '' }
                ],
                moderacion: [
                    { label: 'Subastas revisadas', val: fmtInt(52 * f), note: '88% aprobadas · 12% rechazadas', cls: 'is-up' },
                    { label: 'Cola pendiente actual', val: '10', note: 'Esperando validación de moderador', cls: 'is-warn' },
                    { label: 'Tiempo medio de revisión', val: '3 h 20 m', note: 'Dentro del objetivo diario', cls: '' },
                    { label: 'Mensajes sin responder', val: '2', note: 'De 14 consultas recibidas en el periodo', cls: 'is-warn' }
                ]
            };

            const items = strips[activeRepTab] || strips.subastas;
            repStripEl.innerHTML = items.map((m) => `
                <div class="adm-rep-metric">
                    <span class="adm-rep-metric-label">${m.label}</span>
                    <strong class="adm-rep-metric-val">${m.val}</strong>
                    <span class="adm-rep-metric-note ${m.cls}">${m.note}</span>
                </div>
            `).join('');
        }

        function renderAuctionVolumeChart() {
            const svg = document.getElementById('admRepAuctionChart');
            const tooltip = document.getElementById('admRepAuctionTooltip');
            if (!svg) return;

            const seriesByPeriod = {
                '7d':  { labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'], vals: [280, 410, 320, 590, 440, 360, 310] },
                '30d': { labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5'], vals: [1480, 2120, 1890, 2460, 1695] },
                '90d': { labels: ['Sep 1', 'Sep 2', 'Oct 1', 'Oct 2', 'Nov 1', 'Nov 2'], vals: [3100, 3850, 4200, 4680, 5120, 5580] }
            };
            const data = seriesByPeriod[activeRepPeriod] || seriesByPeriod['30d'];
            const W = 560, H = 210, padL = 48, padR = 16, padT = 16, padB = 30;
            const plotW = W - padL - padR;
            const plotH = H - padT - padB;
            const maxVal = Math.max(...data.vals) * 1.15;

            let html = '';
            for (let i = 0; i <= 4; i++) {
                const v = (maxVal / 4) * i;
                const y = padT + plotH - (v / maxVal) * plotH;
                html += `<line x1="${padL}" y1="${y}" x2="${W - padR}" y2="${y}" stroke="#23291e" stroke-width="1" />`;
                html += `<text x="${padL - 8}" y="${y + 4}" text-anchor="end" fill="#7d8676" font-size="10.5">$${Math.round(v)}</text>`;
            }

            const slotW = plotW / data.vals.length;
            const barW = Math.min(44, slotW * 0.46);
            data.vals.forEach((v, idx) => {
                const cx = padL + slotW * idx + slotW / 2;
                const x = cx - barW / 2;
                const h = Math.max(4, (v / maxVal) * plotH);
                const y = padT + plotH - h;
                html += `<rect class="adm-rep-bar-svg" x="${x}" y="${y}" width="${barW}" height="${h}" rx="5" fill="#6f8749" data-label="${data.labels[idx]}" data-val="${fmtUsd(v)}" style="cursor:pointer; transition: fill 0.15s ease;" />`;
                html += `<text x="${cx}" y="${H - 8}" text-anchor="middle" fill="#a8b1a0" font-size="11">${data.labels[idx]}</text>`;
            });

            svg.innerHTML = html;
            if (tooltip) {
                svg.querySelectorAll('.adm-rep-bar-svg').forEach((bar) => {
                    bar.addEventListener('mouseenter', () => {
                        bar.setAttribute('fill', '#93b161');
                        const rect = svg.getBoundingClientRect();
                        const bx = parseFloat(bar.getAttribute('x')) + parseFloat(bar.getAttribute('width')) / 2;
                        const by = parseFloat(bar.getAttribute('y'));
                        tooltip.textContent = `${bar.dataset.label}: ${bar.dataset.val}`;
                        tooltip.style.left = `${(bx / W) * rect.width}px`;
                        tooltip.style.top = `${(by / H) * rect.height}px`;
                        tooltip.style.opacity = '1';
                    });
                    bar.addEventListener('mouseleave', () => {
                        bar.setAttribute('fill', '#6f8749');
                        tooltip.style.opacity = '0';
                    });
                });
            }
        }

        function renderStatusSummary() {
            const barEl = document.getElementById('admRepStatusBar');
            const listEl = document.getElementById('admRepStatusList');
            if (!barEl || !listEl) return;
            const f = PERIOD_META[activeRepPeriod].factor;
            const rows = [
                { name: 'Finalizadas con ganador', count: Math.max(1, Math.round(34 * f)), color: '#849f55' },
                { name: 'Activas en curso', count: 17, color: '#5691a1' },
                { name: 'Pendientes de revisión', count: 10, color: '#df9588' },
                { name: 'Cerradas sin ofertas', count: Math.max(1, Math.round(6 * f)), color: '#697263' },
                { name: 'Rechazadas / canceladas', count: Math.max(1, Math.round(5 * f)), color: '#a95f6c' }
            ];
            const total = rows.reduce((s, r) => s + r.count, 0) || 1;
            barEl.innerHTML = rows.map((r) => `<span style="width:${(r.count / total * 100).toFixed(1)}%; background:${r.color};"></span>`).join('');
            listEl.innerHTML = rows.map((r) => {
                const pct = Math.round((r.count / total) * 100);
                return `
                    <li>
                        <span class="adm-rep-stat-name"><span class="adm-rep-dot" style="background:${r.color};"></span>${r.name}</span>
                        <span class="adm-rep-stat-nums"><strong>${r.count}</strong><span>${pct}%</span></span>
                    </li>
                `;
            }).join('');
        }

        function renderAuctionTable() {
            const tbody = document.getElementById('admRepAuctionTbody');
            const sub = document.getElementById('admRepAuctionTableSub');
            if (!tbody) return;
            const list = AUCTIONS_DATA.filter((a) => activeAuctionFilter === 'todas' || a.estado === activeAuctionFilter);
            if (sub) {
                sub.textContent = `Mostrando ${list.length} ${list.length === 1 ? 'subasta' : 'subastas'} · ${PERIOD_META[activeRepPeriod].label}`;
            }
            tbody.innerHTML = list.map((a) => `
                <tr>
                    <td><div class="adm-rep-lot-title" title="${a.lote}">${a.lote}</div></td>
                    <td class="adm-rep-muted">${a.cat}</td>
                    <td class="adm-rep-muted">@${a.vendedor}</td>
                    <td class="is-num adm-rep-muted">${fmtUsd(a.inicio)}</td>
                    <td class="is-num"><strong>${fmtUsd(a.final)}</strong></td>
                    <td class="is-num">${a.pujas}</td>
                    <td><span class="adm-rep-tag adm-rep-tag--${a.estado}">${STATUS_LABELS[a.estado] || a.estado}</span></td>
                    <td class="adm-rep-muted">${a.cierre}</td>
                </tr>
            `).join('');
        }

        function renderCategoriesReport() {
            const barsEl = document.getElementById('admRepCatBars');
            const notesEl = document.getElementById('admRepCatNotes');
            const tbody = document.getElementById('admRepCatTbody');
            const f = PERIOD_META[activeRepPeriod].factor;
            const scaled = CATEGORIES_DATA.map((c) => ({
                ...c,
                lotes: Math.max(1, Math.round(c.lotes * f)),
                cerrados: Math.max(1, Math.round(c.cerrados * f)),
                pujas: Math.max(1, Math.round(c.pujas * f)),
                volumen: Math.round(c.volumen * f)
            }));
            const maxVol = Math.max(...scaled.map((c) => c.volumen), 1);

            if (barsEl) {
                barsEl.innerHTML = scaled.map((c) => `
                    <div class="adm-rep-bar-row">
                        <div class="adm-rep-bar-top">
                            <span class="adm-rep-bar-label">${c.nombre}</span>
                            <span class="adm-rep-bar-meta">${c.pujas} pujas <strong>${fmtUsd(c.volumen)}</strong></span>
                        </div>
                        <div class="adm-rep-bar-track">
                            <div class="adm-rep-bar-fill" style="width:${(c.volumen / maxVol * 100).toFixed(1)}%;"></div>
                        </div>
                    </div>
                `).join('');
            }

            if (notesEl) {
                notesEl.innerHTML = `
                    <li>
                        <strong>Coleccionables lidera en ventas y pujas</strong>
                        <span>Concentra casi el 30% del monto vendido y mantiene una competencia alta por subasta.</span>
                    </li>
                    <li>
                        <strong>Tecnología tiene el ticket promedio más alto</strong>
                        <span>Cada subasta cerrada promedia ${fmtUsd(scaled[1].volumen / scaled[1].cerrados)}, impulsado por cámaras y equipos retro.</span>
                    </li>
                    <li>
                        <strong>Dos categorías sin actividad reciente</strong>
                        <span>Deportes y Vehículos permanecen desactivadas; conviene evaluarlas antes de la siguiente campaña.</span>
                    </li>
                `;
            }

            if (tbody) {
                tbody.innerHTML = scaled.map((c) => `
                    <tr>
                        <td><strong>${c.nombre}</strong></td>
                        <td class="is-num">${c.lotes}</td>
                        <td class="is-num">${c.cerrados}</td>
                        <td class="is-num">${c.pujas}</td>
                        <td class="is-num"><strong>${fmtUsd(c.volumen)}</strong></td>
                        <td class="is-num adm-rep-muted">${fmtUsd(c.volumen / c.cerrados)}</td>
                    </tr>
                `).join('');
            }
        }

        function renderUsersReport() {
            const svg = document.getElementById('admRepUsersChart');
            const tooltip = document.getElementById('admRepUsersTooltip');
            const roleList = document.getElementById('admRepRoleList');
            const healthEl = document.getElementById('admRepUserHealth');
            const tbody = document.getElementById('admRepUsersTbody');

            if (svg) {
                const series = {
                    '7d':  { labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'], vals: [2, 4, 3, 5, 2, 3, 1] },
                    '30d': { labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5'], vals: [10, 14, 12, 17, 11] },
                    '90d': { labels: ['Sep 1', 'Sep 2', 'Oct 1', 'Oct 2', 'Nov 1', 'Nov 2'], vals: [22, 26, 29, 34, 31, 36] }
                }[activeRepPeriod];
                const W = 560, H = 210, padL = 36, padR = 18, padT = 16, padB = 30;
                const plotW = W - padL - padR, plotH = H - padT - padB;
                const maxVal = Math.max(...series.vals) * 1.2;

                let html = '';
                for (let i = 0; i <= 4; i++) {
                    const v = Math.round((maxVal / 4) * i);
                    const y = padT + plotH - (v / maxVal) * plotH;
                    html += `<line x1="${padL}" y1="${y}" x2="${W - padR}" y2="${y}" stroke="#23291e" stroke-width="1" />`;
                    html += `<text x="${padL - 8}" y="${y + 4}" text-anchor="end" fill="#7d8676" font-size="10.5">${v}</text>`;
                }

                const pts = series.vals.map((v, i) => ({
                    x: padL + (i / (series.vals.length - 1)) * plotW,
                    y: padT + plotH - (v / maxVal) * plotH,
                    v, label: series.labels[i]
                }));
                const area = `M ${pts[0].x} ${padT + plotH} ` + pts.map((p) => `L ${p.x} ${p.y}`).join(' ') + ` L ${pts[pts.length - 1].x} ${padT + plotH} Z`;
                html += `<defs><linearGradient id="repUserGrad" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#849f55" stop-opacity="0.22"/><stop offset="100%" stop-color="#849f55" stop-opacity="0"/></linearGradient></defs>`;
                html += `<path d="${area}" fill="url(#repUserGrad)" />`;
                html += `<polyline points="${pts.map((p) => `${p.x},${p.y}`).join(' ')}" fill="none" stroke="#8ea958" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />`;
                pts.forEach((p) => {
                    html += `<text x="${p.x}" y="${H - 8}" text-anchor="middle" fill="#a8b1a0" font-size="11">${p.label}</text>`;
                    html += `<circle class="adm-rep-udot" cx="${p.x}" cy="${p.y}" r="4" fill="#9bb869" stroke="#161913" stroke-width="2" data-label="${p.label}" data-val="${p.v} cuentas" style="cursor:pointer;" />`;
                });
                svg.innerHTML = html;

                if (tooltip) {
                    svg.querySelectorAll('.adm-rep-udot').forEach((dot) => {
                        dot.addEventListener('mouseenter', () => {
                            const rect = svg.getBoundingClientRect();
                            tooltip.textContent = `${dot.dataset.label}: ${dot.dataset.val}`;
                            tooltip.style.left = `${(parseFloat(dot.getAttribute('cx')) / W) * rect.width}px`;
                            tooltip.style.top = `${(parseFloat(dot.getAttribute('cy')) / H) * rect.height}px`;
                            tooltip.style.opacity = '1';
                        });
                        dot.addEventListener('mouseleave', () => { tooltip.style.opacity = '0'; });
                    });
                }
            }

            if (roleList) {
                roleList.innerHTML = `
                    <li><span class="adm-rep-stat-name"><span class="adm-rep-dot" style="background:#849f55;"></span>Usuario</span><span class="adm-rep-stat-nums"><strong>178</strong><span>95%</span></span></li>
                    <li><span class="adm-rep-stat-name"><span class="adm-rep-dot" style="background:#5691a1;"></span>Moderador</span><span class="adm-rep-stat-nums"><strong>6</strong><span>3%</span></span></li>
                    <li><span class="adm-rep-stat-name"><span class="adm-rep-dot" style="background:#df9588;"></span>Administrador</span><span class="adm-rep-stat-nums"><strong>3</strong><span>2%</span></span></li>
                `;
            }

            if (healthEl) {
                healthEl.innerHTML = `
                    <div class="adm-rep-mini-box"><span>Cuentas activas</span><strong>184</strong></div>
                    <div class="adm-rep-mini-box"><span>Suspendidas</span><strong>3</strong></div>
                `;
            }

            if (tbody) {
                tbody.innerHTML = USERS_DATA.map((u) => `
                    <tr>
                        <td><strong>${u.usuario}</strong></td>
                        <td class="adm-rep-muted">${u.rol}</td>
                        <td class="adm-rep-muted">${u.ubicacion}</td>
                        <td class="is-num">${u.lotes}</td>
                        <td class="is-num"><strong>${u.pujas}</strong></td>
                        <td><span class="adm-rep-tag ${u.estado === 'Activo' ? 'adm-rep-tag--finalizada' : 'adm-rep-tag--rechazada'}">${u.estado}</span></td>
                    </tr>
                `).join('');
            }
        }

        function renderModerationReport() {
            const barEl = document.getElementById('admRepModBar');
            const listEl = document.getElementById('admRepModList');
            const pairEl = document.getElementById('admRepContactPair');
            const contactEl = document.getElementById('admRepContactList');
            const f = PERIOD_META[activeRepPeriod].factor;

            const approved = Math.max(1, Math.round(46 * f));
            const rejected = Math.max(1, Math.round(6 * f));
            const pending = 10;
            const total = approved + rejected + pending;

            if (barEl) {
                barEl.innerHTML = `
                    <span style="width:${(approved / total * 100).toFixed(1)}%; background:#849f55;"></span>
                    <span style="width:${(pending / total * 100).toFixed(1)}%; background:#df9588;"></span>
                    <span style="width:${(rejected / total * 100).toFixed(1)}%; background:#a95f6c;"></span>
                `;
            }
            if (listEl) {
                listEl.innerHTML = `
                    <li><span class="adm-rep-stat-name"><span class="adm-rep-dot" style="background:#849f55;"></span>Aprobadas y publicadas</span><span class="adm-rep-stat-nums"><strong>${approved}</strong><span>${Math.round(approved / total * 100)}%</span></span></li>
                    <li><span class="adm-rep-stat-name"><span class="adm-rep-dot" style="background:#df9588;"></span>Pendientes en cola</span><span class="adm-rep-stat-nums"><strong>${pending}</strong><span>${Math.round(pending / total * 100)}%</span></span></li>
                    <li><span class="adm-rep-stat-name"><span class="adm-rep-dot" style="background:#a95f6c;"></span>Rechazadas por moderación</span><span class="adm-rep-stat-nums"><strong>${rejected}</strong><span>${Math.round(rejected / total * 100)}%</span></span></li>
                `;
            }
            if (pairEl) {
                pairEl.innerHTML = `
                    <div class="adm-rep-mini-box"><span>Mensajes atendidos</span><strong>${Math.max(2, Math.round(12 * f))}</strong></div>
                    <div class="adm-rep-mini-box"><span>Pendientes de respuesta</span><strong>2</strong></div>
                `;
            }
            if (contactEl) {
                contactEl.innerHTML = CONTACTS_DATA.map((c) => `
                    <li>
                        <div>
                            <div class="adm-rep-contact-subj">${c.asunto}</div>
                            <div class="adm-rep-contact-meta">${c.remitente} · ${c.fecha}</div>
                        </div>
                        <span class="adm-rep-tag ${c.atendido ? 'adm-rep-tag--finalizada' : 'adm-rep-tag--pendiente'}">
                            ${c.atendido ? 'Atendido' : 'Pendiente'}
                        </span>
                    </li>
                `).join('');
            }
        }

        function refreshReports() {
            if (repPeriodSubtitle) {
                repPeriodSubtitle.textContent = PERIOD_META[activeRepPeriod].label;
            }
            renderRepStrip();
            renderAuctionVolumeChart();
            renderStatusSummary();
            renderAuctionTable();
            renderCategoriesReport();
            renderUsersReport();
            renderModerationReport();
        }

        repTabs.forEach((btn) => {
            btn.addEventListener('click', () => {
                activeRepTab = btn.dataset.repTab || 'subastas';
                repTabs.forEach((t) => {
                    const isAct = t === btn;
                    t.classList.toggle('active', isAct);
                    t.setAttribute('aria-selected', isAct ? 'true' : 'false');
                });
                repPanes.forEach((p) => {
                    p.classList.toggle('active', p.id === `rep-panel-${activeRepTab}`);
                });
                renderRepStrip();
            });
        });

        repPeriodSelect?.addEventListener('change', () => {
            activeRepPeriod = repPeriodSelect.value || '30d';
            refreshReports();
        });

        repStatusChips.forEach((chip) => {
            chip.addEventListener('click', () => {
                activeAuctionFilter = chip.dataset.repStatus || 'todas';
                repStatusChips.forEach((c) => c.classList.toggle('active', c === chip));
                renderAuctionTable();
            });
        });

        repPrintBtn?.addEventListener('click', () => {
            window.print();
        });

        repCsvBtn?.addEventListener('click', () => {
            const activePane = document.getElementById(`rep-panel-${activeRepTab}`);
            const table = activePane?.querySelector('table');
            if (!table) {
                showToast('Reporte de moderación listo para exportar (vista de diseño).');
                return;
            }
            const rows = Array.from(table.querySelectorAll('tr')).map((tr) =>
                Array.from(tr.querySelectorAll('th, td'))
                    .map((cell) => `"${cell.textContent.trim().replace(/"/g, '""')}"`)
                    .join(',')
            );
            const blob = new Blob(['\uFEFF' + rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `gavel-reporte-${activeRepTab}-${activeRepPeriod}.csv`;
            a.click();
            URL.revokeObjectURL(url);
            showToast(`Archivo CSV de ${activeRepTab} descargado (datos de referencia).`);
        });

        refreshReports();
    }

    // =========================================================================
    // CONFIGURACIÓN — INTERACCIONES DE DISEÑO (SIN BACKEND)
    // =========================================================================
    const cfgSection = document.getElementById('sec-configuracion');
    if (cfgSection) {
        const cfgForm = document.getElementById('admCfgForm');
        const cfgTabs = cfgSection.querySelectorAll('[data-cfg-tab]');
        const cfgPanes = cfgSection.querySelectorAll('.adm-cfg-pane');
        const cfgStatus = document.getElementById('admCfgStatus');
        const cfgSaveBtn = document.getElementById('admCfgSaveBtn');
        const cfgResetBtn = document.getElementById('admCfgResetBtn');

        const bannerCheck = document.getElementById('cfgBannerActivo');
        const bannerTextRow = document.getElementById('cfgBannerTextRow');
        const maintCheck = document.getElementById('cfgMantenimiento');
        const maintMsgRow = document.getElementById('cfgMaintMsgRow');
        const maintCard = document.getElementById('cfgMaintCard');

        const getFields = () => cfgForm ? Array.from(cfgForm.querySelectorAll('input, select, textarea')) : [];

        const takeSnapshot = () => getFields().map((el) => ({
            el,
            val: el.type === 'checkbox' ? el.checked : el.value
        }));

        let savedSnapshot = takeSnapshot();

        function syncConditionalRows() {
            bannerTextRow?.classList.toggle('is-collapsed', !bannerCheck?.checked);
            const maintOn = Boolean(maintCheck?.checked);
            maintMsgRow?.classList.toggle('is-collapsed', !maintOn);
            maintCard?.classList.toggle('is-active', maintOn);
        }

        function syncDirtyState() {
            const dirty = savedSnapshot.some((item) =>
                (item.el.type === 'checkbox' ? item.el.checked : item.el.value) !== item.val
            );
            if (cfgStatus) {
                cfgStatus.textContent = dirty ? 'Cambios sin guardar' : 'Sin cambios pendientes';
                cfgStatus.classList.toggle('is-dirty', dirty);
            }
            if (cfgSaveBtn) cfgSaveBtn.disabled = !dirty;
            if (cfgResetBtn) cfgResetBtn.disabled = !dirty;
        }

        cfgTabs.forEach((btn) => {
            btn.addEventListener('click', () => {
                const target = btn.dataset.cfgTab || 'subastas';
                cfgTabs.forEach((t) => {
                    const isAct = t === btn;
                    t.classList.toggle('active', isAct);
                    t.setAttribute('aria-selected', isAct ? 'true' : 'false');
                });
                cfgPanes.forEach((p) => {
                    p.classList.toggle('active', p.id === `cfg-panel-${target}`);
                });
            });
        });

        cfgForm?.addEventListener('input', () => {
            syncConditionalRows();
            syncDirtyState();
        });
        cfgForm?.addEventListener('change', () => {
            syncConditionalRows();
            syncDirtyState();
        });

        cfgResetBtn?.addEventListener('click', () => {
            savedSnapshot.forEach((item) => {
                if (item.el.type === 'checkbox') item.el.checked = item.val;
                else item.el.value = item.val;
            });
            syncConditionalRows();
            syncDirtyState();
            showToast('Cambios descartados.');
        });

        cfgSaveBtn?.addEventListener('click', () => {
            savedSnapshot = takeSnapshot();
            syncDirtyState();
            showToast('Configuración guardada (vista de diseño).');
        });

        syncConditionalRows();
        syncDirtyState();
    }

    // Demo action buttons (edit/delete) — delegated so dynamically rendered rows work too
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-adm-demo-action]');
        if (!btn) return;
        showToast(btn.dataset.admDemoAction);
    });

    // Demo form submit inside modals
    document.querySelectorAll('[data-adm-demo-form]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const msg = form.dataset.admDemoForm || 'Acción simulada en modo referencia.';
            const modalEl = form.closest('.modal');
            if (modalEl && window.bootstrap) {
                const instance = window.bootstrap.Modal.getInstance(modalEl);
                instance?.hide();
            }
            form.reset();
            showToast(msg);
        });
    });
})();
