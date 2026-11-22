<?php
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__, 2) . '/config/env.php';
}
$base_url = BASE_URL;
?>
<footer class="footer-ppi-wrapper">
    <div class="container-fluid bg-dark-ppi-2 footer-ppi footer-ppi-top">
        <div class="container py-4">
            <div class="row g-4 align-items-start">

                <!-- Columna 1: Marca y descripción -->
                <div class="col-12 col-lg-4">
                    <div class="d-flex align-items-baseline gap-1 gavel-brand mb-2">
                        <span class="brand-name text-main-ppi fw-bold" style="font-size: 2.1rem; line-height: 1; font-family: 'Raleway', sans-serif;">Gavel</span>
                        <span class="brand-tld text-third-ppi fw-bold" style="font-size: 1.6rem; line-height: 1; font-family: 'Raleway', sans-serif;">.net</span>
                    </div>
                    <p class="text-second-ppi pe-lg-3 mb-3" style="line-height: 1.5; font-size: 0.9rem;">
                        Sistema de subastas en línea. Publica, puja y gana con ofertas transparentes, seguras y en tiempo real.
                    </p>
                    <div class="d-flex align-items-center gap-2 footer-social-links">
                        <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" class="btn btn-outline-ppi-social rounded-circle d-inline-flex align-items-center justify-content-center" title="Twitter / X" aria-label="Síguenos en Twitter" style="width: 34px; height: 34px; font-size: 0.85rem;">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="btn btn-outline-ppi-social rounded-circle d-inline-flex align-items-center justify-content-center" title="Facebook" aria-label="Síguenos en Facebook" style="width: 34px; height: 34px; font-size: 0.85rem;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="btn btn-outline-ppi-social rounded-circle d-inline-flex align-items-center justify-content-center" title="Instagram" aria-label="Síguenos en Instagram" style="width: 34px; height: 34px; font-size: 0.85rem;">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" class="btn btn-outline-ppi-social rounded-circle d-inline-flex align-items-center justify-content-center" title="LinkedIn" aria-label="Conéctate con nosotros en LinkedIn" style="width: 34px; height: 34px; font-size: 0.85rem;">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>

                <!-- Columna 2: Explorar -->
                <div class="col-6 col-sm-4 col-lg-2 offset-lg-1">
                    <h6 class="text-main-ppi fw-bold text-uppercase mb-3" style="letter-spacing: 0.8px; font-size: 0.9rem;">
                        <i class="fas fa-compass text-third-ppi me-2"></i>Explorar
                    </h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 mb-0" style="font-size: 0.9rem;">
                        <li>
                            <a href="<?php echo $base_url; ?>views/catalogo.php" class="link-ppi p-0 d-inline-flex align-items-center">
                                <i class="fas fa-angle-right me-2 text-third-ppi" style="font-size: 0.75rem;"></i>Subastas
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>views/categorias.php" class="link-ppi p-0 d-inline-flex align-items-center">
                                <i class="fas fa-angle-right me-2 text-third-ppi" style="font-size: 0.75rem;"></i>Categorías
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>views/como-funciona.php" class="link-ppi p-0 d-inline-flex align-items-center">
                                <i class="fas fa-angle-right me-2 text-third-ppi" style="font-size: 0.75rem;"></i>Cómo funciona
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>views/subastas/registro-producto.php" class="link-ppi p-0 d-inline-flex align-items-center">
                                <i class="fas fa-angle-right me-2 text-third-ppi" style="font-size: 0.75rem;"></i>Registrar producto
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Columna 3: Tu cuenta -->
                <div class="col-6 col-sm-4 col-lg-2">
                    <h6 class="text-main-ppi fw-bold text-uppercase mb-3" style="letter-spacing: 0.8px; font-size: 0.9rem;">
                        <i class="fas fa-user-circle text-third-ppi me-2"></i>Tu cuenta
                    </h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 mb-0" style="font-size: 0.9rem;">
                        <li>
                            <a href="<?php echo $base_url; ?>views/auth/registro.php" class="link-ppi p-0 d-inline-flex align-items-center">
                                <i class="fas fa-angle-right me-2 text-third-ppi" style="font-size: 0.75rem;"></i>Registrarse
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>views/auth/login.php" class="link-ppi p-0 d-inline-flex align-items-center">
                                <i class="fas fa-angle-right me-2 text-third-ppi" style="font-size: 0.75rem;"></i>Iniciar sesión
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>views/contacto/contacto.php" class="link-ppi p-0 d-inline-flex align-items-center">
                                <i class="fas fa-angle-right me-2 text-third-ppi" style="font-size: 0.75rem;"></i>Soporte / Contacto
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Columna 4: Legal y Normativa -->
                <div class="col-12 col-sm-4 col-lg-3">
                    <h6 class="text-main-ppi fw-bold text-uppercase mb-3" style="letter-spacing: 0.8px; font-size: 0.9rem;">
                        <i class="fas fa-balance-scale text-third-ppi me-2"></i>Legal y Normas
                    </h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 mb-0" style="font-size: 0.9rem;">
                        <li>
                            <a href="<?php echo $base_url; ?>views/legal/privacidad.php" class="link-ppi p-0 d-inline-flex align-items-center">
                                <i class="fas fa-angle-right me-2 text-third-ppi" style="font-size: 0.75rem;"></i>Política de Privacidad
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>views/legal/terminos.php" class="link-ppi p-0 d-inline-flex align-items-center">
                                <i class="fas fa-angle-right me-2 text-third-ppi" style="font-size: 0.75rem;"></i>Términos y Condiciones
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>views/legal/cookies.php" class="link-ppi p-0 d-inline-flex align-items-center">
                                <i class="fas fa-angle-right me-2 text-third-ppi" style="font-size: 0.75rem;"></i>Política de Cookies
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" onclick="if(window.resetCookieConsent){window.resetCookieConsent();}else{alert('Ajustes de cookies');}" class="link-ppi p-0 d-inline-flex align-items-center text-muted" style="font-size: 0.84rem;">
                                <i class="fas fa-cookie-bite me-2 text-third-ppi" style="font-size: 0.75rem;"></i>Gestionar cookies
                            </a>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <!-- Barra inferior de Copyright -->
    <div class="container-fluid bg-dark-ppi-2 py-3" style="border-top: 1px solid #1e2417;">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <p class="text-second-ppi mb-0 small text-center text-md-start">
                &copy; <?php echo date('Y'); ?> <span class="text-main-ppi fw-semibold">Gavel.net</span> &bull; Todos los derechos reservados.
                <span class="d-none d-sm-inline">&bull; </span><a href="<?php echo $base_url; ?>views/legal/terminos.php" class="text-second-ppi text-decoration-underline">Aviso Legal</a>
            </p>
            <p class="text-third-ppi mb-0 small text-center text-md-end fw-semibold">
                <i class="fas fa-graduation-cap me-1"></i>Proyecto PPI - Ciclo II-2026
            </p>
        </div>
    </div>
</footer>

<?php
$cookie_banner_file = dirname(__DIR__, 2) . '/includes/cookie-banner.php';
if (file_exists($cookie_banner_file)) {
    include $cookie_banner_file;
}
?>
<script src="<?php echo $base_url; ?>assets/js/cookie-banner.js"></script>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['id_usuario'])) {
    $modal_sesion_inactiva = dirname(__DIR__, 2) . '/includes/modal-sesion-inactiva.php';
    if (file_exists($modal_sesion_inactiva)) {
        include $modal_sesion_inactiva;
    }
    echo '<script src="' . $base_url . 'assets/js/sesion-inactividad.js"></script>';
}
?>