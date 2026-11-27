(function () {
    'use strict';

    const baseUrl = window.ADM_BASE_URL || '';
    const controllerUrl = `${baseUrl}controllers/admin_controller.php`;

    const sectionEl = document.getElementById('sec-categorias');
    const gridEl = document.getElementById('admCatGrid');
    const emptyEl = document.getElementById('admCatEmpty');
    const emptyIcon = document.getElementById('admCatEmptyIcon');
    const emptyText = document.getElementById('admCatEmptyText');
    const searchInput = document.getElementById('admCatSearch');
    const statusSelect = document.getElementById('admCatStatusFilter');
    const resetBtn = document.getElementById('btnCatResetFilters');

    if (!sectionEl || !gridEl) return;

    function notify(tipo, mensaje) {
        if (typeof window.mostrarToast === 'function') {
            window.mostrarToast(tipo, 'Categorías', mensaje, 3200);
        } else {
            window.AdminDashboard?.notify(mensaje, tipo, 'Categorías');
        }
    }

    function normalizarTexto(texto) {
        return String(texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function filtrarCategorias() {
        const termino = normalizarTexto(searchInput?.value || '');
        const estadoFiltro = (statusSelect?.value || 'todas').toLowerCase();
        const tarjetas = Array.from(gridEl.querySelectorAll('.adm-cat-card-v2'));
        let visibles = 0;

        tarjetas.forEach((card) => {
            const nombre = normalizarTexto(card.dataset.nombre || '');
            const descripcion = normalizarTexto(card.dataset.descripcion || '');
            const estado = (card.dataset.estado || '').toLowerCase();

            const coincideTexto = termino === '' || nombre.includes(termino) || descripcion.includes(termino);
            const coincideEstado = estadoFiltro === 'todas' || estado === estadoFiltro;
            const mostrar = coincideTexto && coincideEstado;

            card.classList.toggle('d-none', !mostrar);
            if (mostrar) visibles++;
        });

        gridEl.classList.toggle('d-none', visibles === 0);
        if (emptyEl) {
            emptyEl.classList.toggle('d-none', visibles > 0);
            if (visibles === 0) {
                const hayFiltros = termino !== '' || estadoFiltro !== 'todas';
                if (tarjetas.length === 0 && !hayFiltros) {
                    if (emptyIcon) emptyIcon.className = 'fa-regular fa-folder-open';
                    if (emptyText) emptyText.textContent = 'No hay categorías registradas en este momento.';
                    if (resetBtn) resetBtn.classList.add('d-none');
                } else {
                    if (emptyIcon) emptyIcon.className = 'fa-regular fa-folder-open';
                    if (emptyText) emptyText.textContent = 'Prueba con otro término de búsqueda o cambia el filtro de estado.';
                    if (resetBtn) resetBtn.classList.remove('d-none');
                }
            }
        }
    }

    function actualizarEstadoTarjeta(card, btn, esActiva) {
        card.classList.toggle('is-inactive', !esActiva);
        card.dataset.estado = esActiva ? 'publicadas' : 'desactivadas';

        const statusBadge = card.querySelector('.adm-cat-status');
        if (statusBadge) {
            statusBadge.classList.toggle('is-published', esActiva);
            statusBadge.classList.toggle('is-draft', !esActiva);
            statusBadge.innerHTML = `<span class="adm-cat-status-dot"></span> ${esActiva ? 'Publicada' : 'Desactivada'}`;
        }

        if (btn) {
            btn.classList.toggle('is-deactivate', esActiva);
            btn.classList.toggle('is-publish', !esActiva);
            btn.innerHTML = `<i class="fa-solid ${esActiva ? 'fa-eye-slash' : 'fa-globe'}"></i> <span>${esActiva ? 'Desactivar' : 'Publicar'}</span>`;
        }
    }

    async function cambiarEstadoCategoria(card, btn) {
        if (!card || !btn || btn.disabled) return;

        const idCategoria = Number(btn.dataset.id || card.dataset.id || 0);
        if (idCategoria <= 0) return;

        const actualmenteActiva = (card.dataset.estado || '') === 'publicadas';
        const nuevoEstado = actualmenteActiva ? 0 : 1;
        const htmlOriginal = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> <span>${nuevoEstado === 1 ? 'Publicando...' : 'Desactivando...'}</span>`;

        try {
            const body = new URLSearchParams();
            body.set('opcion', 'categoria_cambiar_estado');
            body.set('id_categoria', String(idCategoria));
            body.set('estado', String(nuevoEstado));

            const response = await fetch(controllerUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    Accept: 'application/json'
                },
                body: body.toString(),
                cache: 'no-store'
            });

            if (response.status === 401) {
                const errData = await response.json().catch(() => ({}));
                window.location.href = errData.redirect || `${baseUrl}views/auth/login.php${errData.expired ? '?reason=inactivity' : ''}`;
                return;
            }

            const data = await response.json().catch(() => null);
            if (!response.ok || !data?.estado || !data.categoria) {
                btn.innerHTML = htmlOriginal;
                const tipoToast = data?.bloqueado ? 'warning' : 'error';
                notify(tipoToast, data?.mensaje || 'No se pudo actualizar el estado de la categoría.');
                return;
            }

            const esActiva = Number(data.categoria.estado) === 1;
            actualizarEstadoTarjeta(card, btn, esActiva);
            filtrarCategorias();
            notify('success', data.mensaje || (esActiva ? 'Categoría publicada.' : 'Categoría desactivada.'));
        } catch (err) {
            btn.innerHTML = htmlOriginal;
            notify('error', err?.message || 'Error de conexión al cambiar el estado de la categoría.');
        } finally {
            btn.disabled = false;
        }
    }

    gridEl.addEventListener('click', (event) => {
        const toggleBtn = event.target.closest('[data-cat-action="toggle"]');
        if (!toggleBtn) return;

        const card = toggleBtn.closest('.adm-cat-card-v2');
        cambiarEstadoCategoria(card, toggleBtn);
    });

    if (searchInput) {
        searchInput.addEventListener('input', filtrarCategorias);
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', filtrarCategorias);
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (statusSelect) statusSelect.value = 'todas';
            filtrarCategorias();
            searchInput?.focus();
        });
    }

    window.AdminCategorias = {
        filtrar: filtrarCategorias
    };
})();
