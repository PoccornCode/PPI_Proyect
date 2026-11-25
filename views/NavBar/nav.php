<?php
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__, 2) . '/config/env.php';
}
$base_url = BASE_URL;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__, 2) . '/helpers/sesion_inactividad.php';
verificarInactividadSesion();
$usuario_logueado = isset($_SESSION['id_usuario']);
$url_perfil = $usuario_logueado ? ($base_url . "views/perfil.php") : ($base_url . "views/auth/login.php");

require_once dirname(__DIR__, 2) . '/controllers/categoria_controller.php';
$categorias_nav = obtenerCategoriasPopularesNav(6);
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Raleway:wght@600;700;800&display=swap" rel="stylesheet">
<link href="<?php echo $base_url; ?>assets/css/main.css?v=46" rel="stylesheet">

<header class="sticky-top navbar-ppi-wrapper" style="background-color: #0c0f0a !important; border-bottom: 2px solid #658807 !important; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.7) !important;">
    <nav class="navbar navbar-expand-xl navbar-dark py-2 py-lg-3" style="background-color: transparent !important;">
        <div class="container-fluid px-3">

            <a href="<?php echo $base_url; ?>index.php" class="navbar-brand d-flex align-items-center gap-2 gavel-brand py-0 me-3 me-xl-4">
                <i class="fas fa-gavel brand-icon text-third-ppi" style="font-size: 1.55rem; line-height: 1;" aria-hidden="true"></i>
                <span class="d-flex align-items-baseline gap-1">
                    <span class="brand-name text-main-ppi fw-bold" style="font-size: 1.85rem; line-height: 1; font-family: 'Raleway', sans-serif;">Gavel</span>
                    <span class="brand-tld text-third-ppi fw-bold" style="font-size: 1.45rem; line-height: 1; font-family: 'Raleway', sans-serif;">.net</span>
                </span>
            </a>

            <button class="navbar-toggler border-0 p-2 shadow-none" type="button" id="navDrawerToggler" aria-controls="navDrawer" aria-expanded="false" aria-label="Abrir menú de navegación">
                <span class="text-main-ppi">
                    <i class="fas fa-bars fs-4"></i>
                </span>
            </button>

            <div class="nav-drawer-backdrop d-xl-none" id="navDrawerBackdrop" aria-hidden="true"></div>

            <div class="nav-drawer-panel" id="navDrawer" role="dialog" aria-modal="true" aria-label="Menú de navegación">
                <div class="nav-drawer-header d-flex d-xl-none align-items-center justify-content-between">
                    <a href="<?php echo $base_url; ?>index.php" class="d-flex align-items-center gap-2 gavel-brand text-decoration-none">
                        <i class="fas fa-gavel brand-icon text-third-ppi" style="font-size: 1.25rem; line-height: 1;" aria-hidden="true"></i>
                        <span class="d-flex align-items-baseline gap-1">
                            <span class="brand-name text-main-ppi fw-bold" style="font-size: 1.35rem; line-height: 1; font-family: 'Raleway', sans-serif;">Gavel</span>
                            <span class="brand-tld text-third-ppi fw-bold" style="font-size: 1.1rem; line-height: 1; font-family: 'Raleway', sans-serif;">.net</span>
                        </span>
                    </a>
                    <button type="button" class="nav-drawer-close" id="navDrawerClose" aria-label="Cerrar menú">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <ul class="navbar-nav mx-auto mb-2 mb-xl-0 align-items-xl-center gap-xl-1 nav-drawer-links">
                    <li class="nav-item">
                        <a href="<?php echo $base_url; ?>index.php" class="nav-link link-ppi">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $base_url; ?>views/catalogo.php" class="nav-link link-ppi">Subastas</a>
                    </li>
                    <li class="nav-item dropdown d-none d-xl-block">
                        <a href="<?php echo $base_url; ?>views/categorias.php" class="nav-link link-ppi dropdown-toggle" id="dropdownCategorias" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Categorías
                        </a>
                        <ul class="dropdown-menu menu-perfil-ppi" aria-labelledby="dropdownCategorias">
                            <?php foreach ($categorias_nav as $catNav): ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $base_url; ?>views/catalogo.php?categoria=<?= urlencode($catNav['nombre']) ?>">
                                        <?= htmlspecialchars($catNav['nombre']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item fw-semibold text-third-ppi" href="<?php echo $base_url; ?>views/categorias.php">
                                    Ver todas las categorías &rarr;
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item d-xl-none">
                        <button type="button" class="nav-link link-ppi nav-drawer-accordion-btn" id="navDrawerCatToggle" aria-expanded="false" aria-controls="navDrawerCategorias">
                            <span>Categorías</span>
                            <i class="fas fa-chevron-down nav-drawer-accordion-caret"></i>
                        </button>
                        <div class="nav-drawer-sublist" id="navDrawerCategorias" hidden>
                            <?php foreach ($categorias_nav as $catNav): ?>
                                <a class="nav-drawer-sublink" href="<?php echo $base_url; ?>views/catalogo.php?categoria=<?= urlencode($catNav['nombre']) ?>">
                                    <?= htmlspecialchars($catNav['nombre']) ?>
                                </a>
                            <?php endforeach; ?>
                            <a class="nav-drawer-sublink nav-drawer-sublink--all" href="<?php echo $base_url; ?>views/categorias.php">
                                Ver todas las categorías &rarr;
                            </a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $base_url; ?>views/como-funciona.php" class="nav-link link-ppi">Cómo funciona</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $base_url; ?>views/contacto/contacto.php" class="nav-link link-ppi">Contacto</a>
                    </li>
                </ul>

                <div class="nav-drawer-actions d-flex flex-column flex-xl-row align-items-stretch align-items-xl-center gap-2 pt-3 pt-xl-0 border-top-mobile">
                    <a href="<?php echo $base_url; ?>views/subastas/registro-producto.php" class="btn btn-outline-publish btn-sm px-3 py-2 fw-semibold d-inline-flex align-items-center justify-content-center">
                        <i class="fas fa-plus me-1 small"></i>Publicar producto
                    </a>
                    <?php if ($usuario_logueado): ?>
                        <?php
                        $nav_primer_nombre = trim(explode(' ', trim($_SESSION['nombre'] ?? 'Usuario'))[0]);
                        $nav_inicial = strtoupper(mb_substr($nav_primer_nombre !== '' ? $nav_primer_nombre : 'U', 0, 1, 'UTF-8'));
                        ?>
                        <div class="dropdown d-none d-xl-block">
                            <button class="btn-nav-profile-pill d-inline-flex align-items-center" type="button" id="dropdownPerfil" data-bs-toggle="dropdown" aria-expanded="false" title="Mi Cuenta" aria-label="Menú de cuenta de usuario">
                                <span class="nav-avatar-circle"><?= htmlspecialchars($nav_inicial) ?></span>
                                <span class="nav-profile-name"><?= htmlspecialchars($nav_primer_nombre) ?></span>
                                <i class="fas fa-chevron-down nav-profile-caret"></i>
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
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="<?php echo $base_url; ?>views/subastas/mis-productos.php">
                                        <i class="fas fa-box-open"></i> Mis Productos
                                    </a>
                                </li>
                                <?php if ((int)($_SESSION['id_rol'] ?? 0) === 1): ?>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="<?php echo $base_url; ?>views/admin/dashboard.php">
                                            <i class="fas fa-crown"></i> Panel de Administración
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (in_array((int)($_SESSION['id_rol'] ?? 0), [1, 3], true)): ?>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="<?php echo $base_url; ?>views/moderador/dashboard.php">
                                            <i class="fas fa-shield-alt"></i> Panel de Moderación
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item item-logout d-flex align-items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#modalConfirmLogout">
                                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="nav-drawer-user d-xl-none">
                            <div class="nav-drawer-user-card">
                                <span class="nav-avatar-circle"><?= htmlspecialchars($nav_inicial) ?></span>
                                <div class="min-w-0">
                                    <div class="menu-perfil-name text-truncate">
                                        <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?> <?= htmlspecialchars($_SESSION['apellido'] ?? '') ?>
                                    </div>
                                    <div class="menu-perfil-user text-truncate">@<?= htmlspecialchars($_SESSION['username'] ?? 'usuario') ?></div>
                                </div>
                            </div>
                            <div class="nav-drawer-user-links">
                                <a class="nav-drawer-user-link" href="<?php echo $base_url; ?>views/perfil.php">
                                    <i class="fas fa-user"></i> Mi Perfil
                                </a>
                                <a class="nav-drawer-user-link" href="<?php echo $base_url; ?>views/subastas/mis-productos.php">
                                    <i class="fas fa-box-open"></i> Mis Productos
                                </a>
                                <?php if ((int)($_SESSION['id_rol'] ?? 0) === 1): ?>
                                    <a class="nav-drawer-user-link" href="<?php echo $base_url; ?>views/admin/dashboard.php">
                                        <i class="fas fa-crown"></i> Panel de Administración
                                    </a>
                                <?php endif; ?>
                                <?php if (in_array((int)($_SESSION['id_rol'] ?? 0), [1, 3], true)): ?>
                                    <a class="nav-drawer-user-link" href="<?php echo $base_url; ?>views/moderador/dashboard.php">
                                        <i class="fas fa-shield-alt"></i> Panel de Moderación
                                    </a>
                                <?php endif; ?>
                                <button type="button" class="nav-drawer-user-link nav-drawer-user-link--logout" data-bs-toggle="modal" data-bs-target="#modalConfirmLogout">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="nav-drawer-auth-grid">
                            <a href="<?php echo $base_url; ?>views/auth/login.php" class="btn btn-secondary btn-sm px-3 py-2 fw-semibold">
                                Iniciar Sesión
                            </a>
                            <a href="<?php echo $base_url; ?>views/auth/registro.php" class="btn btn-primary btn-sm px-3 py-2 fw-semibold">
                                Registrarse
                            </a>
                        </div>
                        <a href="<?php echo $url_perfil; ?>" class="btn-nav-profile d-none d-xl-inline-flex align-items-center justify-content-center" title="Iniciar sesión" aria-label="Acceder a cuenta o perfil">
                            <i class="fas fa-user-circle"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </nav>
</header>

<script>
    (function () {
        const toggler = document.getElementById('navDrawerToggler');
        const drawer = document.getElementById('navDrawer');
        const backdrop = document.getElementById('navDrawerBackdrop');
        const closeBtn = document.getElementById('navDrawerClose');
        const catToggle = document.getElementById('navDrawerCatToggle');
        const catList = document.getElementById('navDrawerCategorias');

        if (!toggler || !drawer || !backdrop) return;

        function setDrawerOpen(open) {
            drawer.classList.toggle('is-open', open);
            backdrop.classList.toggle('is-open', open);
            toggler.setAttribute('aria-expanded', open ? 'true' : 'false');
            document.body.style.overflow = open ? 'hidden' : '';
        }

        toggler.addEventListener('click', function () {
            setDrawerOpen(!drawer.classList.contains('is-open'));
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                setDrawerOpen(false);
            });
        }

        backdrop.addEventListener('click', function () {
            setDrawerOpen(false);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && drawer.classList.contains('is-open')) {
                setDrawerOpen(false);
            }
        });

        if (catToggle && catList) {
            catToggle.addEventListener('click', function () {
                const expanded = catToggle.getAttribute('aria-expanded') === 'true';
                catToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                catList.hidden = expanded;
            });
        }

        drawer.querySelectorAll('a, [data-bs-toggle="modal"]').forEach(function (el) {
            el.addEventListener('click', function () {
                if (window.innerWidth < 1200) {
                    setDrawerOpen(false);
                }
            });
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1200 && drawer.classList.contains('is-open')) {
                setDrawerOpen(false);
            }
        });
    })();
</script>

<?php if ($usuario_logueado): ?>
    <div class="modal fade" id="modalConfirmLogout" tabindex="-1" aria-labelledby="modalConfirmLogoutLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-white m-0" id="modalConfirmLogoutLabel">
                        Cerrar sesión
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body py-2">
                    <p class="mb-0" style="color: #d6ddd0; font-size: 0.93rem; line-height: 1.55;">
                        ¿Estás seguro de que deseas salir de tu cuenta de <strong class="text-white">Gavel.net</strong>?
                    </p>
                </div>
                <div class="modal-footer justify-content-end gap-2">
                    <button type="button" class="btn-logout-modal btn-logout-cancel" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <a href="<?php echo $base_url; ?>controllers/logout.php" class="btn-logout-modal btn-logout-confirm text-decoration-none">
                        <i class="fas fa-sign-out-alt"></i> Sí, cerrar sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>