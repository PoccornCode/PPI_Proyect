<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__, 2) . '/config/env.php';
}
require_once dirname(__DIR__, 2) . '/controllers/admin_controller.php';
require_once dirname(__DIR__, 2) . '/controllers/producto_admin_controller.php';

$base_url = BASE_URL;
$perfilAdmin = requerirAdministradorAutenticado(false);
$alertasTopbar = obtenerAlertasTopbarAdmin();
$kpisAdmin = obtenerKpisAdmin('30d');
$graficosInicio = obtenerGraficosInicioAdmin('7d');
$resumenInferiorInicio = obtenerResumenInferiorInicioAdmin($base_url);
$rolesAdmin = obtenerRolesAdmin();
$usuariosAdminInicial = obtenerUsuariosAdmin('', 0, 1, 5);
$categoriasAdmin = obtenerCategoriasAdmin();
$productosAdminInicial = obtenerProductosSinSubastaAdmin($base_url);
$subastasAdminInicial = obtenerSubastasAdmin($base_url);

$adm_nombre_completo = $perfilAdmin['nombre_completo'];
$adm_primer_nombre = $perfilAdmin['primer_nombre'];
$adm_username = $perfilAdmin['username'];
$adm_rol_nombre = $perfilAdmin['rol_nombre'];
$adm_inicial = $perfilAdmin['inicial'];

$categorias_admin = $categoriasAdmin;

$resumen_categorias = [
    'total'        => count($categorias_admin),
    'publicadas'   => count(array_filter($categorias_admin, fn($c) => (int)$c['estado'] === 1)),
    'desactivadas' => count(array_filter($categorias_admin, fn($c) => (int)$c['estado'] === 0)),
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración | Gavel.net</title>
    <link rel="icon" type="image/svg+xml" href="<?= $base_url ?>assets/img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Raleway:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/admin-dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/css/admin-dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/moderador-dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/css/moderador-dashboard.css') ?>">
</head>

<body class="admin-body">

    <?php include __DIR__ . '/partials/topbar.php'; ?>

    <!-- SIDEBAR -->
    <div class="adm-sidebar-overlay" id="admSidebarOverlay"></div>
    <aside class="adm-sidebar" id="admSidebar" aria-label="Navegación de administración">
        <div class="mod-sidebar-toggle-row">
            <span class="mod-sidebar-label">Menú</span>
            <button type="button" class="mod-collapse-btn" id="admSidebarCollapseBtn" title="Contraer menú" aria-label="Contraer menú" aria-expanded="true">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="16" rx="3"></rect>
                    <line x1="9" y1="4" x2="9" y2="20"></line>
                    <polyline class="mod-collapse-arrow" points="15 9 12 12 15 15"></polyline>
                </svg>
            </button>
        </div>
        <nav>
            <ul class="adm-nav-list">
                <li>
                    <button type="button" class="adm-nav-btn active" data-adm-tab="inicio" title="Inicio">
                        <i class="fa-solid fa-house"></i>
                        <span>Inicio</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="adm-nav-btn" data-adm-tab="usuarios" title="Usuarios">
                        <i class="fa-solid fa-users"></i>
                        <span>Usuarios</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="adm-nav-btn" data-adm-tab="productos" title="Productos">
                        <i class="fa-solid fa-box-open"></i>
                        <span>Productos</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="adm-nav-btn" data-adm-tab="subastas" title="Subastas">
                        <i class="fa-solid fa-gavel"></i>
                        <span>Subastas</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="adm-nav-btn" data-adm-tab="categorias" title="Categorías">
                        <i class="fa-solid fa-table-cells-large"></i>
                        <span>Categorías</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="adm-nav-btn" data-adm-tab="reportes" title="Reportes">
                        <i class="fa-solid fa-chart-simple"></i>
                        <span>Reportes</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="adm-nav-btn" data-adm-tab="configuracion" title="Configuración">
                        <i class="fa-solid fa-gear"></i>
                        <span>Configuración</span>
                    </button>
                </li>
            </ul>
        </nav>

        <div class="mod-sidebar-footer">
            <a href="<?= $base_url ?>views/moderador/dashboard.php" class="mod-sidebar-link" title="Ir al panel de moderación">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Moderación</span>
            </a>
            <a href="<?= $base_url ?>index.php" class="mod-sidebar-link" title="Ir al sitio público">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>Sitio público</span>
            </a>
            <a href="#" class="mod-sidebar-link mod-sidebar-link--logout" title="Cerrar sesión" data-bs-toggle="modal" data-bs-target="#modalConfirmLogout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Cerrar sesión</span>
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="adm-main">

        <!-- Sección: Inicio -->
        <section class="adm-section active" id="sec-inicio">
            <div class="adm-section-head">
                <div class="adm-title-group">
                    <span class="adm-title-accent" aria-hidden="true"></span>
                    <div>
                        <h1>¡Bienvenido de nuevo!</h1>
                        <p>Aquí tienes un resumen del estado de Gavel<span class="brand-inline">.net</span>.</p>
                    </div>
                </div>
                <label class="adm-pill-select">
                    <i class="fa-regular fa-calendar"></i>
                    <select id="admKpiRange" aria-label="Rango de fechas del resumen">
                        <option value="30d" selected>Últimos 30 días</option>
                        <option value="7d">Últimos 7 días</option>
                        <option value="90d">Últimos 90 días</option>
                    </select>
                </label>
            </div>

            <?php include __DIR__ . '/partials/kpi-resumen.php'; ?>
            <?php include __DIR__ . '/partials/graficos-inicio.php'; ?>
            <?php include __DIR__ . '/partials/resumen-inferior-inicio.php'; ?>
        </section>

        <?php include __DIR__ . '/partials/gestion-usuarios.php'; ?>
        <?php include __DIR__ . '/partials/gestion-productos.php'; ?>
        <?php include __DIR__ . '/partials/gestion-subastas.php'; ?>
        <?php include __DIR__ . '/partials/gestion-categorias.php'; ?>
        <?php include __DIR__ . '/partials/gestion-reportes.php'; ?>
        <?php include __DIR__ . '/partials/gestion-configuracion.php'; ?>
    </main>

    <?php include __DIR__ . '/partials/modal-categoria-crud.php'; ?>
    <?php include __DIR__ . '/partials/modal-confirmar-categoria.php'; ?>
    <?php include __DIR__ . '/partials/modal-agregar-usuario.php'; ?>
    <?php include __DIR__ . '/partials/modal-detalle-usuario.php'; ?>
    <?php include __DIR__ . '/partials/modal-editar-usuario.php'; ?>
    <?php include __DIR__ . '/partials/modal-detalle-producto.php'; ?>
    <?php include __DIR__ . '/partials/modal-editar-producto.php'; ?>
    <?php include __DIR__ . '/partials/modal-detalle-subasta.php'; ?>
    <?php include __DIR__ . '/partials/modal-confirmar-subasta.php'; ?>
    <?php include __DIR__ . '/partials/modal-editar-estado-subasta.php'; ?>
    <?php include dirname(__DIR__) . '/moderador/partials/modal-logout.php'; ?>
    <?php include dirname(__DIR__, 2) . '/includes/modal-sesion-inactiva.php'; ?>

    <script>
        window.ADM_BASE_URL = <?= json_encode($base_url, JSON_UNESCAPED_SLASHES) ?>;
        window.ADM_GRAFICOS_INICIO = <?= json_encode($graficosInicio, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        window.ADM_USUARIOS_INICIAL = <?= json_encode($usuariosAdminInicial, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $base_url ?>assets/js/sesion-inactividad.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/sesion-inactividad.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/toast-ppi.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/toast-ppi.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-kpis.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-kpis.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-graficos.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-graficos.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-resumen-inicio.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-resumen-inicio.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/paginacion.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/paginacion.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-usuarios.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-usuarios.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-usuario-agregar.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-usuario-agregar.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-productos.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-productos.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-producto-detalle.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-producto-detalle.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-producto-editar.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-producto-editar.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-subastas.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-subastas.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-subasta-detalle.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-subasta-detalle.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-subasta-estado.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-subasta-estado.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-categorias.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-categorias.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-categoria-agregar.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-categoria-agregar.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-categoria-editar.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-categoria-editar.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-dashboard.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-dashboard.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-dashboard-referencia.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-dashboard-referencia.js') ?>"></script>
</body>

</html>
