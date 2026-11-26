function mostrarToast(tipo = 'success', titulo = '', mensaje = '', duracionMs = 4500) {
    let container = document.getElementById('toastPPIContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastPPIContainer';
        container.className = 'toast-ppi-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast-ppi toast-${tipo}`;

    let iconClass = 'fas fa-check-circle';
    if (tipo === 'error' || tipo === 'danger') iconClass = 'fas fa-times-circle';
    else if (tipo === 'warning') iconClass = 'fas fa-exclamation-triangle';
    else if (tipo === 'info') iconClass = 'fas fa-info-circle';

    toast.innerHTML = `
        <div class="toast-ppi-icon">
            <i class="${iconClass}"></i>
        </div>
        <div class="toast-ppi-content">
            ${titulo ? `<div class="toast-ppi-title">${titulo}</div>` : ''}
            <p class="toast-ppi-message">${mensaje}</p>
        </div>
        <button type="button" class="toast-ppi-close" aria-label="Cerrar">&times;</button>
        <div class="toast-ppi-progress"></div>
    `;

    container.appendChild(toast);

    // Forzar reflow inicial para que la barra siempre anime desde 100% hasta 0%
    void toast.offsetWidth;

    requestAnimationFrame(() => {
        toast.classList.add('show');
        const progress = toast.querySelector('.toast-ppi-progress');
        if (progress) {
            progress.style.transform = 'scaleX(1)';
            requestAnimationFrame(() => {
                progress.style.transition = `transform ${duracionMs}ms linear`;
                progress.style.transform = 'scaleX(0)';
            });
        }
    });

    let autoCloseTimer = setTimeout(() => cerrar(), duracionMs);

    function cerrar() {
        clearTimeout(autoCloseTimer);
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentElement) toast.remove();
        }, 300);
    }

    const btnCerrar = toast.querySelector('.toast-ppi-close');
    if (btnCerrar) {
        btnCerrar.addEventListener('click', cerrar);
    }
}
