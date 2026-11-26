(function () {
    'use strict';

    const baseUrl = window.ADM_BASE_URL || '';
    const endpoint = `${baseUrl}controllers/admin_controller.php`;

    const modalEl = document.getElementById('modalEditarEstadoSubasta');
    if (!modalEl) return;

    const itemNameEl = document.getElementById('admAuctionStatusItemName');
    const listEl = document.getElementById('admAuctionStatusList');
    const motivoWrapEl = document.getElementById('admAuctionStatusMotivoWrap');
    const motivoLabelEl = document.getElementById('admAuctionStatusMotivoLabel');
    const motivoInputEl = document.getElementById('admAuctionStatusMotivo');
    const saveBtn = document.getElementById('admAuctionStatusSaveBtn');

    const confirmModalEl = document.getElementById('modalConfirmarEstadoSubasta');
    const confirmIntroEl = document.getElementById('admAuctionStatusConfirmIntro');
    const confirmListEl = document.getElementById('admAuctionStatusConfirmList');
    const confirmBackBtn = document.getElementById('admAuctionStatusBackBtn');
    const confirmSubmitBtn = document.getElementById('admAuctionStatusConfirmBtn');
    const confirmSubmitTextEl = document.getElementById('admAuctionStatusConfirmBtnText');

    const ETIQUETAS_ESTADO = {
        pendiente: 'Pendiente',
        activa: 'Activa',
        finalizada: 'Finalizada',
        sin_ofertas: 'Sin ofertas',
        rechazada: 'Rechazada',
        cancelada: 'Cancelada'
    };

    let activeSubastaId = 0;
    let activeCurrentStatus = '';
    let activeItemTitle = '';
    let guardando = false;

    function escHtml(valor) {
        return String(valor ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function notificarToast(tipo, titulo, mensaje) {
        if (typeof window.mostrarToast === 'function') {
            window.mostrarToast(tipo, titulo, mensaje, 3800);
        }
    }

    function obtenerEstadoSeleccionado() {
        const checked = listEl?.querySelector('input[name="admAuctionStatusRadio"]:checked');
        return checked ? String(checked.value || '').trim().toLowerCase() : '';
    }

    function sincronizarModal() {
        if (!listEl) return;
        const seleccionado = obtenerEstadoSeleccionado();

        listEl.querySelectorAll('.mod-status-row').forEach((row) => {
            const val = row.dataset.statusRow || '';
            row.classList.toggle('is-selected', val === seleccionado);
            row.classList.toggle('is-current', val === activeCurrentStatus);
        });

        const requiereMotivo = (seleccionado === 'rechazada' || seleccionado === 'cancelada') && seleccionado !== activeCurrentStatus;
        if (motivoWrapEl) {
            motivoWrapEl.classList.toggle('d-none', !requiereMotivo);
        }
        if (requiereMotivo && motivoLabelEl && motivoInputEl) {
            const esCancel = seleccionado === 'cancelada';
            motivoLabelEl.textContent = esCancel ? 'Motivo de cancelación (opcional)' : 'Motivo de rechazo (opcional)';
            motivoInputEl.placeholder = esCancel
                ? 'Describe brevemente el motivo de la cancelación...'
                : 'Describe brevemente el motivo del rechazo...';
        }

        if (saveBtn) {
            saveBtn.disabled = guardando || !seleccionado || seleccionado === activeCurrentStatus;
        }
    }

    function abrirModalEstado(btn) {
        if (!btn || btn.disabled || typeof bootstrap === 'undefined') return;
        const row = btn.closest('tr');
        const idSubasta = Number(btn.dataset.subastaId || row?.dataset.subastaId || 0);
        const estadoActual = String(row?.dataset.status || 'pendiente').toLowerCase();
        const titulo = row?.dataset.title || '';

        if (!idSubasta || estadoActual === 'finalizada') return;

        activeSubastaId = idSubasta;
        activeCurrentStatus = estadoActual;
        activeItemTitle = titulo;

        if (itemNameEl) {
            const etiqueta = ETIQUETAS_ESTADO[estadoActual];
            itemNameEl.textContent = etiqueta && estadoActual === 'sin_ofertas'
                ? `${titulo} · Estado actual: ${etiqueta}`
                : titulo;
        }

        listEl?.querySelectorAll('input[name="admAuctionStatusRadio"]').forEach((input) => {
            input.checked = input.value === activeCurrentStatus;
        });
        if (motivoInputEl) motivoInputEl.value = '';
        sincronizarModal();

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function abrirConfirmacionCambio() {
        const nuevoEstado = obtenerEstadoSeleccionado();
        if (!activeSubastaId || !nuevoEstado || nuevoEstado === activeCurrentStatus || guardando) return;
        if (!confirmModalEl || typeof bootstrap === 'undefined') {
            ejecutarCambioEstado();
            return;
        }

        const antesTxt = ETIQUETAS_ESTADO[activeCurrentStatus] || activeCurrentStatus;
        const despuesTxt = ETIQUETAS_ESTADO[nuevoEstado] || nuevoEstado;
        const motivo = (nuevoEstado === 'rechazada' || nuevoEstado === 'cancelada')
            ? (motivoInputEl?.value || '').trim()
            : '';

        if (confirmIntroEl) {
            confirmIntroEl.innerHTML = `¿Deseas aplicar el siguiente cambio en la subasta <strong class="text-white">${escHtml(activeItemTitle || `#${activeSubastaId}`)}</strong>?`;
        }

        if (confirmListEl) {
            let html = `
                <div class="adm-user-confirm-row">
                    <span class="adm-user-confirm-label">Estado</span>
                    <div class="adm-user-confirm-values">
                        <span class="adm-user-confirm-old">${escHtml(antesTxt)}</span>
                        <i class="fa-solid fa-arrow-right adm-user-confirm-arrow" aria-hidden="true"></i>
                        <span class="adm-user-confirm-new">${escHtml(despuesTxt)}</span>
                    </div>
                </div>
            `;
            if (motivo) {
                html += `
                    <div class="adm-user-confirm-row">
                        <span class="adm-user-confirm-label">Motivo</span>
                        <div class="adm-user-confirm-values">
                            <span class="adm-user-confirm-new">${escHtml(motivo)}</span>
                        </div>
                    </div>
                `;
            }
            confirmListEl.innerHTML = html;
        }

        if (confirmSubmitBtn) confirmSubmitBtn.disabled = false;
        if (confirmBackBtn) confirmBackBtn.disabled = false;
        if (confirmSubmitTextEl) confirmSubmitTextEl.textContent = 'Confirmar y guardar';

        if (modalEl.classList.contains('show')) {
            modalEl.addEventListener('hidden.bs.modal', () => {
                bootstrap.Modal.getOrCreateInstance(confirmModalEl).show();
            }, { once: true });
            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        } else {
            bootstrap.Modal.getOrCreateInstance(confirmModalEl).show();
        }
    }

    function volverAEditarEstado() {
        if (guardando || !confirmModalEl || typeof bootstrap === 'undefined') return;
        if (confirmModalEl.classList.contains('show')) {
            confirmModalEl.addEventListener('hidden.bs.modal', () => {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }, { once: true });
            bootstrap.Modal.getOrCreateInstance(confirmModalEl).hide();
        } else {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    async function ejecutarCambioEstado() {
        const nuevoEstado = obtenerEstadoSeleccionado();
        if (!activeSubastaId || !nuevoEstado || nuevoEstado === activeCurrentStatus || guardando) return;

        guardando = true;
        if (saveBtn) saveBtn.disabled = true;
        if (confirmSubmitBtn) confirmSubmitBtn.disabled = true;
        if (confirmBackBtn) confirmBackBtn.disabled = true;
        if (confirmSubmitTextEl) confirmSubmitTextEl.textContent = 'Guardando...';

        const motivo = (nuevoEstado === 'rechazada' || nuevoEstado === 'cancelada')
            ? (motivoInputEl?.value || '').trim()
            : '';

        const body = new URLSearchParams({
            opcion: 'subasta_cambiar_estado',
            id_subasta: String(activeSubastaId),
            nuevo_estado: nuevoEstado,
            estado_esperado: activeCurrentStatus,
            motivo
        });

        try {
            const resp = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    Accept: 'application/json'
                },
                body: body.toString(),
                cache: 'no-store'
            });

            const data = await resp.json().catch(() => ({}));
            if (resp.status === 401 || data?.expired || data?.auth_required) {
                window.location.href = data?.redirect || `${baseUrl}views/auth/login.php${data?.expired ? '?reason=inactivity' : ''}`;
                return;
            }

            if (!resp.ok || !data?.estado) {
                if (data?.conflicto && data?.estado_actual) {
                    activeCurrentStatus = String(data.estado_actual).toLowerCase();
                    if (confirmModalEl && typeof bootstrap !== 'undefined') {
                        if (activeCurrentStatus !== 'finalizada') {
                            confirmModalEl.addEventListener('hidden.bs.modal', () => {
                                listEl?.querySelectorAll('input[name="admAuctionStatusRadio"]').forEach((input) => {
                                    input.checked = input.value === activeCurrentStatus;
                                });
                                sincronizarModal();
                                bootstrap.Modal.getOrCreateInstance(modalEl).show();
                            }, { once: true });
                        }
                        bootstrap.Modal.getOrCreateInstance(confirmModalEl).hide();
                    }
                    await window.AdminSubastas?.recargar?.(false);
                    notificarToast('warning', 'Conflicto de edición', data.mensaje || 'Esta subasta fue modificada recientemente.');
                    return;
                }

                notificarToast('error', 'No se pudo actualizar', data?.mensaje || 'Intenta nuevamente.');
                return;
            }

            if (confirmModalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(confirmModalEl).hide();
            } else if (typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }
            notificarToast('success', 'Estado actualizado', data.mensaje || 'El estado se actualizó correctamente.');
            await window.AdminSubastas?.recargar?.(false);
        } catch (err) {
            console.error('Error al cambiar estado de subasta:', err);
            notificarToast('error', 'Error de conexión', 'Error de comunicación al actualizar la subasta.');
        } finally {
            guardando = false;
            if (confirmSubmitBtn) confirmSubmitBtn.disabled = false;
            if (confirmBackBtn) confirmBackBtn.disabled = false;
            if (confirmSubmitTextEl) confirmSubmitTextEl.textContent = 'Confirmar y guardar';
            sincronizarModal();
        }
    }

    document.addEventListener('click', (e) => {
        const editBtn = e.target.closest('[data-adm-auction-action="editar"]');
        if (editBtn) {
            abrirModalEstado(editBtn);
        }
    });

    listEl?.addEventListener('change', sincronizarModal);
    saveBtn?.addEventListener('click', abrirConfirmacionCambio);
    confirmBackBtn?.addEventListener('click', volverAEditarEstado);
    confirmSubmitBtn?.addEventListener('click', ejecutarCambioEstado);
})();
