(function () {
    'use strict';

    const REFRESH_INTERVAL_MS = 20000;
    const baseUrl = window.ADM_BASE_URL || '';
    const endpoint = `${baseUrl}controllers/admin_controller.php`;

    const sectionEl = document.getElementById('sec-subastas');
    const searchInput = document.getElementById('admAuctionSearch');
    const catFilter = document.getElementById('admAuctionCatFilter');
    const statusFilter = document.getElementById('admAuctionStatusFilter');
    const pageSizeSelect = document.getElementById('admAuctionsPageSize');
    const tbody = document.getElementById('admAuctionsTbody');
    const countEl = document.getElementById('admAuctionsCount');
    const paginationEl = document.getElementById('admAuctionsPagination');
    const emptyRow = document.getElementById('admAuctionsEmptyRow');
    const emptyIcon = document.getElementById('admAuctionsEmptyIcon');
    const emptyText = document.getElementById('admAuctionsEmptyText');
    const clearBtn = document.getElementById('admAuctionsClearFiltersBtn');

    if (!sectionEl || !tbody) return;

    const BADGE_MAP = {
        pendiente: { clase: 'adm-badge--pendiente', texto: 'Pendiente' },
        activa: { clase: 'adm-badge--activa', texto: 'Activa' },
        finalizada: { clase: 'adm-badge--finalizada', texto: 'Finalizada' },
        sin_ofertas: { clase: 'adm-badge--sin-ofertas', texto: 'Sin ofertas' },
        rechazada: { clase: 'adm-badge--rechazada', texto: 'Rechazada' },
        cancelada: { clase: 'adm-badge--cancelada', texto: 'Cancelada' }
    };

    let pageSize = Number(pageSizeSelect?.value) || 5;
    let currentPage = 1;
    let recargando = false;

    function normalizarTexto(texto) {
        return String(texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function escHtml(valor) {
        return String(valor ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function obtenerFilas() {
        return Array.from(tbody.querySelectorAll('tr[data-id]'));
    }

    function renderPaginacion(totalPages) {
        if (!paginationEl || !window.Paginacion) return;
        window.Paginacion.render(paginationEl, {
            currentPage,
            totalPages,
            maxVisible: 5,
            onPageChange: (nextPage) => {
                currentPage = nextPage;
                filtrar(false);
            }
        });
    }

    function actualizarEstadoVacio(total, coincidencias, hayFiltros) {
        if (!emptyRow) return;

        if (coincidencias > 0) {
            emptyRow.classList.add('d-none');
            return;
        }

        emptyRow.classList.remove('d-none');

        if (total === 0 && !hayFiltros) {
            if (emptyIcon) emptyIcon.className = 'fa-solid fa-box-open mb-2 d-block';
            if (emptyText) emptyText.textContent = 'No hay subastas registradas en este momento.';
            if (clearBtn) clearBtn.classList.add('d-none');
        } else {
            if (emptyIcon) emptyIcon.className = 'fa-solid fa-magnifying-glass mb-2 d-block';
            if (emptyText) emptyText.textContent = 'No se encontraron subastas que coincidan con tu búsqueda.';
            if (clearBtn) clearBtn.classList.remove('d-none');
        }
    }

    function filtrar(resetPage = true) {
        if (resetPage) currentPage = 1;

        const term = normalizarTexto(searchInput?.value || '');
        const tokens = term ? term.split(/\s+/).filter(Boolean) : [];
        const selectedCat = normalizarTexto(catFilter?.value || '');
        const selectedStatus = normalizarTexto(statusFilter?.value || '');
        const hayFiltros = Boolean(term || selectedCat || selectedStatus);

        const rows = obtenerFilas();
        const matchingRows = [];

        rows.forEach((row) => {
            const rawStatus = row.dataset.status || '';
            const statusLabel = BADGE_MAP[rawStatus]?.texto || rawStatus.replace(/_/g, ' ');
            const searchable = normalizarTexto([
                row.dataset.id,
                row.dataset.subastaId,
                row.dataset.title,
                row.dataset.vendor,
                row.dataset.category,
                rawStatus,
                statusLabel
            ].join(' '));

            const rowCat = normalizarTexto(row.dataset.category || '');
            const rowStatus = normalizarTexto(rawStatus);

            const matchesText = !tokens.length || tokens.every((tk) => searchable.includes(tk));
            const matchesCat = !selectedCat || rowCat === selectedCat;
            const matchesStatus = !selectedStatus || rowStatus === selectedStatus;

            if (matchesText && matchesCat && matchesStatus) {
                matchingRows.push(row);
            } else {
                row.classList.add('d-none');
            }
        });

        const totalMatching = matchingRows.length;
        const totalPages = Math.max(1, Math.ceil(totalMatching / pageSize));
        if (currentPage > totalPages) currentPage = totalPages;

        const startIdx = (currentPage - 1) * pageSize;
        const endIdx = startIdx + pageSize;

        matchingRows.forEach((row, index) => {
            row.classList.toggle('d-none', index < startIdx || index >= endIdx);
        });

        const visibles = matchingRows.slice(startIdx, endIdx).length;
        if (countEl) {
            if (totalMatching === 0) {
                countEl.textContent = 'Mostrando 0 subastas';
            } else if (totalMatching === 1) {
                countEl.textContent = 'Mostrando 1 subasta';
            } else {
                const desde = startIdx + 1;
                const hasta = startIdx + visibles;
                countEl.textContent = `Mostrando ${desde}–${hasta} de ${totalMatching} subastas`;
            }
        }

        renderPaginacion(totalPages);
        actualizarEstadoVacio(rows.length, totalMatching, hayFiltros);
    }

    function construirFila(item) {
        const estadoRaw = String(item.estado_raw || 'pendiente').toLowerCase();
        const esFinalizada = estadoRaw === 'finalizada';
        const badgeInfo = BADGE_MAP[estadoRaw] || { clase: 'adm-badge--pendiente', texto: estadoRaw };
        const titulo = item.titulo || '';
        const vendedor = item.vendedor || '';
        const categoria = item.categoria || 'General';
        const codigo = item.codigo || '';
        const idSubasta = Number(item.id_subasta || 0);

        const tr = document.createElement('tr');
        tr.dataset.id = codigo;
        tr.dataset.subastaId = String(idSubasta);
        tr.dataset.title = titulo;
        tr.dataset.vendor = vendedor;
        tr.dataset.category = categoria;
        tr.dataset.status = estadoRaw;

        tr.innerHTML = `
            <td>
                <div class="adm-cell-item">
                    <div class="adm-item-thumb">
                        <img src="${escHtml(item.img || '')}" alt="${escHtml(titulo)}">
                    </div>
                    <div>
                        <div class="adm-item-name">${escHtml(titulo)}</div>
                        <div class="mod-sub-meta">${escHtml(codigo)} · ${escHtml(vendedor)}</div>
                    </div>
                </div>
            </td>
            <td>${escHtml(categoria)}</td>
            <td>
                <div class="fw-semibold">${escHtml(item.precio_actual || '$0.00')}</div>
                <div class="mod-sub-meta">Base: ${escHtml(item.precio_base || '$0.00')}</div>
            </td>
            <td>${escHtml(item.tiempo_restante || '—')}</td>
            <td>
                <span class="adm-badge ${escHtml(item.estado_clase || badgeInfo.clase)}">
                    ${escHtml(item.estado_texto || badgeInfo.texto)}
                </span>
            </td>
            <td>
                <div class="adm-action-group">
                    <button type="button" class="adm-action-btn adm-action-btn--edit" title="Ver detalles"
                        aria-label="Ver detalles de ${escHtml(titulo)}"
                        data-adm-auction-action="ver" data-subasta-id="${idSubasta}">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                    <button type="button" class="adm-action-btn adm-action-btn--edit"
                        title="${esFinalizada ? 'Las subastas finalizadas no se pueden modificar' : 'Cambiar estado'}"
                        aria-label="${esFinalizada ? 'Subasta finalizada' : 'Cambiar estado de ' + escHtml(titulo)}"
                        ${esFinalizada ? 'disabled' : ''}
                        data-adm-auction-action="editar" data-subasta-id="${idSubasta}">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                </div>
            </td>
        `;
        return tr;
    }

    async function recargar(esFondo = false) {
        if (recargando) return;
        recargando = true;

        try {
            const params = new URLSearchParams({
                opcion: 'subastas',
                _t: String(Date.now())
            });
            if (esFondo === true) {
                params.set('bg', '1');
            }

            const resp = await fetch(`${endpoint}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
                cache: 'no-store'
            });

            const data = await resp.json().catch(() => ({}));
            if (resp.status === 401 || data?.expired || data?.auth_required) {
                window.location.href = data?.redirect || `${baseUrl}views/auth/login.php${data?.expired ? '?reason=inactivity' : ''}`;
                return;
            }
            if (!resp.ok || !data?.estado || !Array.isArray(data.subastas)) return;

            tbody.querySelectorAll('tr[data-id]').forEach((tr) => tr.remove());

            const frag = document.createDocumentFragment();
            data.subastas.forEach((item) => {
                frag.appendChild(construirFila(item));
            });

            if (emptyRow) {
                tbody.insertBefore(frag, emptyRow);
            } else {
                tbody.appendChild(frag);
            }

            filtrar(false);
        } catch (err) {
            console.error('Error al sincronizar listado de subastas:', err);
        } finally {
            recargando = false;
        }
    }

    function limpiar() {
        if (searchInput) searchInput.value = '';
        if (catFilter) catFilter.value = '';
        if (statusFilter) statusFilter.value = '';
        filtrar(true);
    }

    searchInput?.addEventListener('input', () => filtrar(true));
    searchInput?.addEventListener('search', () => filtrar(true));
    catFilter?.addEventListener('change', () => filtrar(true));
    statusFilter?.addEventListener('change', () => filtrar(true));

    pageSizeSelect?.addEventListener('change', () => {
        pageSize = Math.max(1, Number(pageSizeSelect.value) || 5);
        filtrar(true);
    });

    clearBtn?.addEventListener('click', limpiar);

    document.addEventListener('DOMContentLoaded', () => {
        filtrar(true);

        setInterval(() => {
            if (!document.hidden && sectionEl.classList.contains('active')) {
                recargar(true);
            }
        }, REFRESH_INTERVAL_MS);
    });

    window.AdminSubastas = {
        filtrar,
        limpiar,
        recargar
    };
})();
