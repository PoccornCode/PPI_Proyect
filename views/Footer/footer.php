<?php
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__, 2) . '/config/env.php';
}
$base_url = BASE_URL;
?>
<footer class="footer-ppi-wrapper">
    <div class="container-fluid bg-dark-ppi-2 footer-ppi" style="border-top: 3px solid #658807;">
        <div class="container py-5">
            <div class="row g-4 align-items-start">

                <div class="col-12 col-md-6 col-lg-4">
                    <div class="d-flex align-items-baseline gap-1 gavel-brand mb-3">
                        <span class="brand-name text-main-ppi fw-bold" style="font-size: 2.2rem; line-height: 1; font-family: 'Raleway', sans-serif;">Gavel</span>
                        <span class="brand-tld text-third-ppi fw-bold" style="font-size: 1.7rem; line-height: 1; font-family: 'Raleway', sans-serif;">.net</span>
                    </div>
                    <p class="text-second-ppi pe-lg-3 mb-4" style="line-height: 1.6; font-size: 0.95rem;">
                        Sistema de subastas en línea. Publica, puja y gana con ofertas transparentes, seguras y en tiempo real.
                    </p>
                    <div class="d-flex align-items-center gap-2 footer-social-links">
                        <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" class="btn btn-outline-ppi-social rounded-circle d-inline-flex align-items-center justify-content-center" title="Twitter / X" style="width: 38px; height: 38px;">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="btn btn-outline-ppi-social rounded-circle d-inline-flex align-items-center justify-content-center" title="Facebook" style="width: 38px; height: 38px;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="btn btn-outline-ppi-social rounded-circle d-inline-flex align-items-center justify-content-center" title="Instagram" style="width: 38px; height: 38px;">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" class="btn btn-outline-ppi-social rounded-circle d-inline-flex align-items-center justify-content-center" title="LinkedIn" style="width: 38px; height: 38px;">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <h6 class="text-main-ppi fw-bold text-uppercase mb-3" style="letter-spacing: 0.8px; font-size: 0.95rem;">
                        <i class="fas fa-compass text-third-ppi me-2"></i>Explorar
                    </h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
                        <li>
                            <a href="<?php echo $base_url; ?>views/catalogo.php" class="link-ppi p-0 d-inline-flex align-items-center">
                                <i class="fas fa-angle-right me-2 text-third-ppi" style="font-size: 0.75rem;"></i>Subastas
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>views/catalogo.php" class="link-ppi p-0 d-inline-flex align-items-center">
                                <i class="fas fa-angle-right me-2 text-third-ppi" style="font-size: 0.75rem;"></i>Categorías
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>404.php" class="link-ppi p-0 d-inline-flex align-items-center">
                                <i class="fas fa-angle-right me-2 text-third-ppi" style="font-size: 0.75rem;"></i>Cómo funciona
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>views/subastas/registro-producto.php" class="link-ppi p-0 d-inline-flex align-items-center">
                                <i class="fas fa-angle-right me-2 text-third-ppi" style="font-size: 0.75rem;"></i>Vender un lote
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <h6 class="text-main-ppi fw-bold text-uppercase mb-3" style="letter-spacing: 0.8px; font-size: 0.95rem;">
                        <i class="fas fa-user-circle text-third-ppi me-2"></i>Tu cuenta
                    </h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
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
                                <i class="fas fa-angle-right me-2 text-third-ppi" style="font-size: 0.75rem;"></i>Contacto y Soporte
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>404.php" class="link-ppi p-0 d-inline-flex align-items-center">
                                <i class="fas fa-angle-right me-2 text-third-ppi" style="font-size: 0.75rem;"></i>Ayuda y FAQ
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="col-12 col-md-6 col-lg-4">
                    <h6 class="text-main-ppi fw-bold text-uppercase mb-3" style="letter-spacing: 0.8px; font-size: 0.95rem;">
                        <i class="fas fa-gavel text-third-ppi me-2"></i>Lotes y Subastas
                    </h6>
                    <div class="row g-2">
                        <div class="col-4">
                            <a href="<?php echo $base_url; ?>views/catalogo.php" class="d-block overflow-hidden rounded footer-gallery-item" style="border: 1px solid #1e2417;">
                                <img src="<?php echo $base_url; ?>assets/img/footer-1.jpg" class="img-fluid w-100 footer-thumb" alt="Lote en subasta">
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="<?php echo $base_url; ?>views/catalogo.php" class="d-block overflow-hidden rounded footer-gallery-item" style="border: 1px solid #1e2417;">
                                <img src="<?php echo $base_url; ?>assets/img/footer-2.jpg" class="img-fluid w-100 footer-thumb" alt="Lote en subasta">
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="<?php echo $base_url; ?>views/catalogo.php" class="d-block overflow-hidden rounded footer-gallery-item" style="border: 1px solid #1e2417;">
                                <img src="<?php echo $base_url; ?>assets/img/footer-3.jpg" class="img-fluid w-100 footer-thumb" alt="Lote en subasta">
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="<?php echo $base_url; ?>views/catalogo.php" class="d-block overflow-hidden rounded footer-gallery-item" style="border: 1px solid #1e2417;">
                                <img src="<?php echo $base_url; ?>assets/img/footer-4.jpg" class="img-fluid w-100 footer-thumb" alt="Lote en subasta">
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="<?php echo $base_url; ?>views/catalogo.php" class="d-block overflow-hidden rounded footer-gallery-item" style="border: 1px solid #1e2417;">
                                <img src="<?php echo $base_url; ?>assets/img/footer-5.jpg" class="img-fluid w-100 footer-thumb" alt="Lote en subasta">
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="<?php echo $base_url; ?>views/catalogo.php" class="d-block overflow-hidden rounded footer-gallery-item" style="border: 1px solid #1e2417;">
                                <img src="<?php echo $base_url; ?>assets/img/footer-6.jpg" class="img-fluid w-100 footer-thumb" alt="Lote en subasta">
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="container-fluid bg-dark-ppi-2 py-3" style="border-top: 1px solid #1e2417;">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <p class="text-second-ppi mb-0 small text-center text-md-start">
                &copy; <?php echo date('Y'); ?> <span class="text-main-ppi fw-semibold">Gavel.net</span> &bull; Todos los derechos reservados.
            </p>
            <p class="text-third-ppi mb-0 small text-center text-md-end fw-semibold">
                <i class="fas fa-graduation-cap me-1"></i>Proyecto PPI - Ciclo II-2026
            </p>
        </div>
    </div>
</footer>
