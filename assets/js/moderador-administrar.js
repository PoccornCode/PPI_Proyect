(function () {
    'use strict';

    const baseUrl = window.MOD_BASE_URL || '';
    const searchInput = document.getElementById('modManageSearch');
    const catFilter = document.getElementById('modManageCatFilter');
    const statusFilter = document.getElementById('modManageStatusFilter');
    const pageSizeSelect = document.getElementById('modManagePageSize');
    const tbody = document.getElementById('modManageTbody');
    const countEl = document.getElementById('modManageCount');
    const paginationEl = document.getElementById('modManagePagination');
    const emptyRow = document.getElementById('modManageEmptyRow');
    const emptyIcon = document.getElementById('modManageEmptyIcon');
    const emptyText = document.getElementById('modManageEmptyText');
    const clearBtn = document.getElementById('modManageClearFiltersBtn');

    if (!tbody) return;

    const BADGE_MAP = {
        pendiente: { clase: 'adm-badge--pendiente', texto: 'Pendiente' },
        activa: { clase: 'adm-badge--activa', texto: 'Activa' },
        finalizada: { clase: 'adm-badge--finalizada', texto: 'Finalizada' },
        sin_ofertas: { clase: 'adm-badge--sin-ofertas', texto: 'Sin ofertas' },
        rechazada: { clase: 'adm-badge--rechazada', texto: 'Rechazada' },
        cancelada: { clase: 'adm-badge--cancelada', texto: 'Cancelada' }
    };

    let pageSize = Number(pageSizeSelect?.value) || 4;
    let currentPage = 1;

    function normalizarTexto(texto) {
        return String(texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function obtenerFilas() {
        return Array.from(tbody.querySelectorAll('tr[data-id]'));
    }

    function renderPaginacion(totalPages) {
        if (!paginationEl || !window.ModPaginacion) return;
        window.ModPaginacion.render(paginationEl, {
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
            const searchable = normalizarTexto([
                row.dataset.id,
                row.dataset.title,
                row.dataset.vendor,
                row.dataset.category,
                row.dataset.status
            ].join(' '));

            const rowCat = normalizarTexto(row.dataset.category || '');
            const rowStatus = normalizarTexto(row.dataset.status || '');

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
                countEl.textContent = '0 registros';
            } else {
                const desde = startIdx + 1;
                const hasta = startIdx + visibles;
                const etiqueta = totalMatching === 1 ? 'registro' : 'registros';
                countEl.textContent = `Mostrando ${desde}–${hasta} de ${totalMatching} ${etiqueta}`;
            }
        }

        renderPaginacion(totalPages);
        actualizarEstadoVacio(rows.length, totalMatching, hayFiltros);
    }

    function notificar(mensaje, tipo = 'success') {
        if (typeof window.showToastPpi === 'function') {
            window.showToastPpi(mensaje, tipo);
        }
    }

    async function actualizarEstadoSubasta(idSubasta, nuevoEstado, triggerBtn) {
        const row = tbody.querySelector(`tr[data-subasta-id="${idSubasta}"]`);
        if (!row || !idSubasta || !nuevoEstado) return false;

        if (triggerBtn) triggerBtn.disabled = true;

        try {
            const body = new URLSearchParams({
                opcion: 'cambiar_estado',
                id_subasta: String(idSubasta),
                nuevo_estado: nuevoEstado
            });

            const resp = await fetch(`${baseUrl}controllers/moderador_controller.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: body.toString()
            });

            const data = await resp.json();
            if (!data.estado) {
                notificar(data.mensaje || 'No se pudo actualizar el estado.', 'danger');
                return false;
            }

            row.dataset.status = nuevoEstado;
            const editBtn = row.querySelector('.mod-btn-edit-status');
            if (editBtn) editBtn.dataset.currentStatus = nuevoEstado;

            const badge = row.querySelector('.mod-manage-badge');
            const info = BADGE_MAP[nuevoEstado] || { clase: 'adm-badge--pendiente', texto: nuevoEstado };
            if (badge) {
                badge.className = `adm-badge ${info.clase} mod-manage-badge`;
                badge.textContent = info.texto;
            }

            if (data.kpis && window.modKpis && typeof window.modKpis.actualizar === 'function') {
                window.modKpis.actualizar(data.kpis);
            }

            notificar(data.mensaje || 'Estado actualizado correctamente.', 'success');
            filtrar(false);
            return true;
        } catch (err) {
            notificar('Error de comunicación al actualizar la subasta.', 'danger');
            return false;
        } finally {
            if (triggerBtn) triggerBtn.disabled = false;
        }
    }

    const modalEl = document.getElementById('modalAdministrarEstado');
    const modalItemName = document.getElementById('modManageModalItemName');
    const modalList = document.getElementById('modManageStatusList');
    const modalSaveBtn = document.getElementById('modManageModalSaveBtn');
    const modalMotivoWrap = document.getElementById('modManageMotivoWrap');
    const modalMotivoLabel = document.getElementById('modManageMotivoLabel');
    const modalMotivoInput = document.getElementById('modManageMotivo');
    const modalInstance = modalEl && window.bootstrap ? new window.bootstrap.Modal(modalEl) : null;
    let activeSubastaId = 0;
    let activeCurrentStatus = '';

    function obtenerEstadoSeleccionadoModal() {
        const checked = modalList?.querySelector('input[name="modManageStatusRadio"]:checked');
        return checked ? String(checked.value || '').trim().toLowerCase() : '';
    }

    function sincronizarEstadoModal() {
        if (!modalList) return;
        const seleccionado = obtenerEstadoSeleccionadoModal();
        modalList.querySelectorAll('.mod-status-row').forEach((row) => {
            const val = row.dataset.statusRow || '';
            row.classList.toggle('is-selected', val === seleccionado);
            row.classList.toggle('is-current', val === activeCurrentStatus);
        });

        const requiereMotivo = (seleccionado === 'rechazada' || seleccionado === 'cancelada') && seleccionado !== activeCurrentStatus;
        if (modalMotivoWrap) {
            modalMotivoWrap.classList.toggle('d-none', !requiereMotivo);
        }
        if (requiereMotivo && modalMotivoLabel && modalMotivoInput) {
            const esCancel = seleccionado === 'cancelada';
            modalMotivoLabel.textContent = esCancel ? 'Motivo de cancelación (opcional)' : 'Motivo de rechazo (opcional)';
            modalMotivoInput.placeholder = esCancel
                ? 'Describe brevemente el motivo de la cancelación...'
                : 'Describe brevemente el motivo del rechazo...';
        }

        if (modalSaveBtn) {
            modalSaveBtn.disabled = !seleccionado || seleccionado === activeCurrentStatus;
        }
    }

    modalList?.addEventListener('change', sincronizarEstadoModal);

    modalSaveBtn?.addEventListener('click', async () => {
        const nuevoEstado = obtenerEstadoSeleccionadoModal();
        if (!activeSubastaId || !nuevoEstado || nuevoEstado === activeCurrentStatus) return;
        const ok = await actualizarEstadoSubasta(activeSubastaId, nuevoEstado, modalSaveBtn);
        if (ok && modalInstance) {
            modalInstance.hide();
        }
    });

    searchInput?.addEventListener('input', () => filtrar(true));
    catFilter?.addEventListener('change', () => filtrar(true));
    statusFilter?.addEventListener('change', () => filtrar(true));
    pageSizeSelect?.addEventListener('change', () => {
        pageSize = Number(pageSizeSelect.value) || 4;
        filtrar(true);
    });

    clearBtn?.addEventListener('click', () => {
        if (searchInput) searchInput.value = '';
        if (catFilter) catFilter.value = '';
        if (statusFilter) statusFilter.value = '';
        filtrar(true);
    });

    tbody.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.mod-btn-edit-status');
        if (editBtn && !editBtn.disabled) {
            activeSubastaId = Number(editBtn.dataset.subastaId || 0);
            activeCurrentStatus = String(editBtn.dataset.currentStatus || 'pendiente').toLowerCase();
            if (activeCurrentStatus === 'finalizada') return;

            const badgeInfo = BADGE_MAP[activeCurrentStatus];
            const itemTitle = editBtn.dataset.item || '';
            if (modalItemName) {
                modalItemName.textContent = badgeInfo && activeCurrentStatus === 'sin_ofertas'
                    ? `${itemTitle} · Estado actual: ${badgeInfo.texto}`
                    : itemTitle;
            }

            modalList?.querySelectorAll('input[name="modManageStatusRadio"]').forEach((input) => {
                input.checked = input.value === activeCurrentStatus;
            });
            if (modalMotivoInput) modalMotivoInput.value = '';
            sincronizarEstadoModal();
            modalInstance?.show();
        }
    });

    filtrar(true);
})();
