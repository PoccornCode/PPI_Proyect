<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id_usuario'])) {
    return;
}
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/env.php';
}
require_once dirname(__DIR__) . '/helpers/sesion_inactividad.php';
?>
<div class="modal fade" id="modalSessionTimeout" tabindex="-1" aria-labelledby="modalSessionTimeoutLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content modal-content-ppi">
            <div class="modal-header modal-header-ppi d-flex align-items-center justify-content-between">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2 m-0" id="modalSessionTimeoutLabel">
                    <i class="fas fa-clock text-third-ppi fs-5"></i>
                    <span>Sesión por expirar</span>
                </h5>
            </div>
            <div class="modal-body py-3">
                <p class="mb-2 text-second-ppi">
                    Hemos detectado inactividad en tu cuenta de <strong>Gavel.net</strong>. Por tu seguridad, tu sesión se cerrará automáticamente en:
                </p>
                <div class="text-center my-3">
                    <span id="sessionTimeoutCountdown" class="fw-bold text-third-ppi" style="font-size: 2rem; font-family: 'Raleway', sans-serif; letter-spacing: 1px;">
                        01:00
                    </span>
                </div>
                <div class="progress session-timeout-progress">
                    <div id="sessionTimeoutProgress" class="progress-bar" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="small text-muted mt-3 mb-0">
                    Haz clic en <strong>Extender sesión</strong> si deseas continuar conectado.
                </p>
            </div>
            <div class="modal-footer modal-footer-ppi d-flex justify-content-end gap-2">
                <button type="button" id="btnCerrarSesionInactividad" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold">
                    Cerrar sesión
                </button>
                <button type="button" id="btnExtenderSesion" class="btn btn-primary btn-sm px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                    <i class="fas fa-sync-alt"></i>
                    <span>Extender sesión</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.GAVEL_SESSION_CONFIG = {
    timeoutSeconds: <?= (int) SESSION_INACTIVITY_TIMEOUT ?>,
    warningBeforeSeconds: <?= (int) SESSION_WARNING_BEFORE ?>,
    keepaliveUrl: <?= json_encode(BASE_URL . 'controllers/extender_sesion.php') ?>,
    logoutUrl: <?= json_encode(BASE_URL . 'controllers/logout.php?reason=inactivity') ?>
};
</script>
