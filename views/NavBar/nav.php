<?php
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__, 2) . '/config/env.php';
}
$base_url = BASE_URL;
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Raleway:wght@600;700;800&display=swap" rel="stylesheet">

<header class="sticky-top navbar-ppi-wrapper">
    <nav class="navbar navbar-expand-xl navbar-dark bg-dark-ppi py-2 py-lg-3">
        <div class="container px-3 px-lg-4">
            
            <a href="<?php echo $base_url; ?>index.php" class="navbar-brand d-flex align-items-baseline gap-1 gavel-brand py-0 me-3 me-xl-4">
                <span class="brand-name text-main-ppi fw-bold" style="font-size: 1.85rem; line-height: 1; font-family: 'Raleway', sans-serif;">Gavel</span>
                <span class="brand-tld text-third-ppi fw-bold" style="font-size: 1.45rem; line-height: 1; font-family: 'Raleway', sans-serif;">.net</span>
            </a>

            <button class="navbar-toggler border-0 p-2 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="text-main-ppi">
                    <i class="fas fa-bars fs-4"></i>
                </span>
            </button>

            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav mx-auto mb-2 mb-xl-0 align-items-xl-center gap-xl-1">
                    <li class="nav-item">
                        <a href="<?php echo $base_url; ?>index.php" class="nav-link link-ppi">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $base_url; ?>views/catalogo.php" class="nav-link link-ppi">Subastas</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $base_url; ?>views/catalogo.php" class="nav-link link-ppi">Categorías</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $base_url; ?>404.php" class="nav-link link-ppi">Cómo funciona</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $base_url; ?>views/contacto/contacto.php" class="nav-link link-ppi">Contacto</a>
                    </li>
                </ul>

                <div class="d-flex flex-column flex-xl-row align-items-stretch align-items-xl-center gap-2 pt-3 pt-xl-0 border-top-mobile">
                    <a href="<?php echo $base_url; ?>views/subastas/registro-producto.php" class="btn btn-outline-publish btn-sm px-3 py-2 fw-semibold d-inline-flex align-items-center justify-content-center">
                        <i class="fas fa-plus me-1 small"></i>Publicar artículo
                    </a>
                    <a href="<?php echo $base_url; ?>views/auth/login.php" class="btn btn-secondary btn-sm px-3 py-2 fw-semibold">
                        Iniciar Sesión
                    </a>
                    <a href="<?php echo $base_url; ?>views/auth/registro.php" class="btn btn-primary btn-sm px-3 py-2 fw-semibold">
                        Registrarse
                    </a>
                </div>
            </div>

        </div>
    </nav>
</header>
