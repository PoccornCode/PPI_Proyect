<?php
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__, 2) . '/config/env.php';
}
$base_url = BASE_URL;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuario_logueado = isset($_SESSION['id_usuario']);
$url_perfil = $usuario_logueado ? ($base_url . "views/perfil.php") : ($base_url . "views/auth/login.php");
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Raleway:wght@600;700;800&display=swap" rel="stylesheet">
<link href="<?php echo $base_url; ?>assets/css/main.css?v=26" rel="stylesheet">

<header class="sticky-top navbar-ppi-wrapper" style="background-color: #0c0f0a !important; border-bottom: 2px solid #658807 !important; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.7) !important;">
    <nav class="navbar navbar-expand-xl navbar-dark py-2 py-lg-3" style="background-color: transparent !important;">
        <div class="container-fluid px-3 px-lg-5">
            
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
                    <?php if ($usuario_logueado): ?>
                        <div class="dropdown">
                            <button class="btn-nav-profile d-inline-flex align-items-center justify-content-center border-0" type="button" id="dropdownPerfil" data-bs-toggle="dropdown" aria-expanded="false" title="Mi Cuenta">
                                <i class="fas fa-user-circle"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end menu-perfil-ppi" aria-labelledby="dropdownPerfil">
                                <li class="menu-perfil-header">
                                    <div class="menu-perfil-name text-truncate">
                                        <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?> <?= htmlspecialchars($_SESSION['apellido'] ?? '') ?>
                                    </div>
                                    <div class="menu-perfil-user text-truncate">@<?= htmlspecialchars($_SESSION['username'] ?? 'usuario') ?></div>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="<?php echo $base_url; ?>views/perfil.php">
                                        <i class="fas fa-user"></i> Mi Perfil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="<?php echo $base_url; ?>views/subastas/registro-producto.php">
                                        <i class="fas fa-box-open"></i> Mis Artículos
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item item-logout d-flex align-items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#modalConfirmLogout">
                                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>views/auth/login.php" class="btn btn-secondary btn-sm px-3 py-2 fw-semibold">
                            Iniciar Sesión
                        </a>
                        <a href="<?php echo $base_url; ?>views/auth/registro.php" class="btn btn-primary btn-sm px-3 py-2 fw-semibold">
                            Registrarse
                        </a>
                        <a href="<?php echo $url_perfil; ?>" class="btn-nav-profile d-inline-flex align-items-center justify-content-center" title="Iniciar sesión">
                            <i class="fas fa-user-circle"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </nav>
</header>

<?php if ($usuario_logueado): ?>
<!-- Modal Confirmación Cerrar Sesión -->
<div class="modal fade" id="modalConfirmLogout" tabindex="-1" aria-labelledby="modalConfirmLogoutLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content modal-content-ppi">
            <div class="modal-header modal-header-ppi d-flex align-items-center justify-content-between">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2 m-0" id="modalConfirmLogoutLabel">
                    <i class="fas fa-sign-out-alt text-warning fs-5"></i>
                    <span>Cerrar Sesión</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3">
                <p class="mb-0 text-second-ppi">¿Estás seguro de que deseas salir de tu cuenta de <strong>Gavel.net</strong>?</p>
            </div>
            <div class="modal-footer modal-footer-ppi d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                <a href="<?php echo $base_url; ?>controllers/logout.php" class="btn btn-danger btn-sm px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                    <i class="fas fa-sign-out-alt"></i> Sí, cerrar sesión
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

