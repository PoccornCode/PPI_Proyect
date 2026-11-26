<?php
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/env.php';
}
?>
<div id="gavel-cookie-banner" class="gavel-cookie-banner" style="display: none;" role="region" aria-label="Aviso sobre cookies">
    <div class="gavel-cookie-content container">
        <div class="row align-items-center g-3">
            <div class="col-12 col-lg-8">
                <div class="d-flex align-items-start gap-3">
                    <div class="gavel-cookie-icon d-none d-sm-flex align-items-center justify-content-center flex-shrink-0">
                        <i class="fas fa-cookie-bite text-third-ppi"></i>
                    </div>
                    <div>
                        <h6 class="text-white fw-bold mb-1" style="font-family: 'Raleway', sans-serif; font-size: 0.98rem;">
                            Utilizamos cookies para garantizar la mejor experiencia
                        </h6>
                        <p class="mb-0 text-second-ppi small" style="line-height: 1.5;">
                            En <strong>Gavel.net</strong> usamos cookies técnicas esenciales para mantener tu sesión de subastas segura y recordar tus preferencias. Puedes consultar más detalles en nuestra
                            <a href="<?= BASE_URL ?>views/legal/cookies.php" class="text-third-ppi text-decoration-underline">Política de Cookies</a>
                            y
                            <a href="<?= BASE_URL ?>views/legal/privacidad.php" class="text-third-ppi text-decoration-underline">Privacidad</a>.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4 text-lg-end">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end align-items-center">
                    <button type="button" id="btn-cookie-essential" class="btn btn-sm btn-outline-secondary text-light px-3 py-2 fw-semibold">
                        Solo necesarias
                    </button>
                    <button type="button" id="btn-cookie-accept-all" class="btn btn-sm btn-primary px-3 py-2 fw-semibold">
                        Aceptar todas
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>