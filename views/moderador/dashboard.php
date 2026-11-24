<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__, 2) . '/config/env.php';
}
require_once dirname(__DIR__, 2) . '/controllers/moderador_controller.php';

$base_url = BASE_URL;
$perfilMod = requerirModeradorAutenticado(false);

$kpisModerador = obtenerKpisModerador();
$categoriasModerador = obtenerCategoriasModerador();
$subastasPendientes = obtenerColaSubastasPendientes($base_url);
$todasSubastas = obtenerTodasSubastasModerador($base_url);
$totalPendientesKpi = (int)($kpisModerador['pendientes'] ?? count($subastasPendientes));

$mod_nombre_completo = $perfilMod['nombre_completo'];
$mod_primer_nombre = $perfilMod['primer_nombre'];
$mod_username = $perfilMod['username'];
$mod_rol_nombre = $perfilMod['rol_nombre'];
$mod_id_rol = (int)($perfilMod['id_rol'] ?? 0);
$mod_inicial = $perfilMod['inicial'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Moderador | Gavel.net</title>
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

    <!-- TOPBAR -->
    <header class="adm-topbar">
        <div class="adm-topbar-brand">
            <button type="button" class="adm-mobile-toggle" id="admMobileToggle" aria-label="Abrir menú lateral">
                <i class="fa-solid fa-bars"></i>
            </button>
            <a href="<?= $base_url ?>index.php" class="adm-brand-link d-flex align-items-center gap-2 text-decoration-none" title="Volver al sitio principal" style="font-family: 'Raleway', sans-serif;">
                <i class="fa-solid fa-gavel" style="font-size: 1.45rem; line-height: 1; color: #62803f;" aria-hidden="true"></i>
                <span class="d-flex flex-column" style="line-height: 1;">
                    <span class="d-flex align-items-baseline gap-1">
                        <span class="fw-bold" style="font-size: 1.55rem; line-height: 1; color: #c9c9c9; font-family: 'Raleway', sans-serif;">Gavel</span>
                        <span class="fw-bold" style="font-size: 1.25rem; line-height: 1; color: #62803f; font-family: 'Raleway', sans-serif;">.net</span>
                    </span>
                    <span style="font-family: 'Open Sans', sans-serif; font-size: 0.62rem; font-weight: 600; letter-spacing: 0.14em; text-transform: uppercase; color: #7c8575; margin-top: 3px;">Moderación</span>
                </span>
            </a>
        </div>

        <div class="adm-topbar-search">
            <div class="adm-search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span class="adm-search-divider" aria-hidden="true"></span>
                <input type="search" id="modGlobalSearch" placeholder="Buscar subastas pendientes..." aria-label="Buscar subastas pendientes">
            </div>
        </div>

        <div class="adm-topbar-actions">
            <!-- Notificaciones -->
            <div class="dropdown">
                <button class="adm-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notificaciones">
                    <i class="fa-regular fa-bell"></i>
                    <span class="adm-notif-badge" id="modNotifCount"><?= $totalPendientesKpi ?></span>
                </button>
                <div class="dropdown-menu dropdown-menu-end adm-dropdown-menu">
                    <div class="px-2 py-1 d-flex justify-content-between align-items-center">
                        <strong class="text-white small">Alertas de Moderación</strong>
                        <span class="badge rounded-pill" id="modNotifBadgePill" style="background:#2d3624; color:var(--adm-accent);"><?= $totalPendientesKpi === 1 ? '1 pendiente' : $totalPendientesKpi . ' pendientes' ?></span>
                    </div>
                    <hr class="dropdown-divider my-1">
                    <button type="button" class="dropdown-item" data-mod-tab="aprobar">
                        <i class="fa-solid fa-clock" style="color:#c8c45e;"></i>
                        <div>
                            <div class="small text-white" id="modNotifPendingText"><?= $totalPendientesKpi ?> subastas por validar</div>
                            <div style="font-size:0.74rem; color:#9aa392;">Cola de aprobación</div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Perfil Moderador -->
            <div class="dropdown">
                <button class="adm-profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menú de usuario">
                    <span class="adm-avatar-circle"><?= htmlspecialchars($mod_inicial) ?></span>
                    <span class="fw-semibold d-none d-sm-inline"><?= htmlspecialchars($mod_primer_nombre) ?></span>
                    <i class="fa-solid fa-chevron-down" style="font-size:0.75rem; color:#9aa392;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end adm-dropdown-menu menu-perfil-ppi">
                    <li class="menu-perfil-header">
                        <div class="menu-perfil-name"><?= htmlspecialchars($mod_nombre_completo) ?></div>
                        <div class="menu-perfil-user">@<?= htmlspecialchars($mod_username) ?><?= $mod_rol_nombre !== '' ? ' · ' . htmlspecialchars($mod_rol_nombre) : '' ?></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= $base_url ?>views/perfil.php">
                            <i class="fa-solid fa-user"></i> Mi Perfil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= $base_url ?>index.php">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Ir al sitio público
                        </a>
                    </li>
                    <?php if ($mod_id_rol === 1): ?>
                        <li>
                            <a class="dropdown-item" href="<?= $base_url ?>views/admin/dashboard.php">
                                <i class="fa-solid fa-crown"></i> Panel de Administración
                            </a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item item-logout" href="#" data-bs-toggle="modal" data-bs-target="#modalConfirmLogout">
                            <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- SIDEBAR -->
    <div class="adm-sidebar-overlay" id="admSidebarOverlay"></div>
    <aside class="adm-sidebar" id="admSidebar" aria-label="Navegación de moderación">
        <div class="mod-sidebar-toggle-row">
            <span class="mod-sidebar-label">Menú</span>
            <button type="button" class="mod-collapse-btn" id="modSidebarCollapseBtn" title="Contraer menú" aria-label="Contraer menú" aria-expanded="true">
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
                    <button type="button" class="adm-nav-btn active" data-mod-tab="aprobar" title="Aprobar subastas">
                        <i class="fa-solid fa-clipboard-check"></i>
                        <span>Aprobar subastas</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="adm-nav-btn" data-mod-tab="administrar" title="Administrar subastas">
                        <i class="fa-solid fa-sliders"></i>
                        <span>Administrar subastas</span>
                    </button>
                </li>
            </ul>
        </nav>

        <div class="mod-sidebar-footer">
            <?php if ($mod_id_rol === 1): ?>
                <a href="<?= $base_url ?>views/admin/dashboard.php" class="mod-sidebar-link" title="Ir al panel de administración">
                    <i class="fa-solid fa-crown"></i>
                    <span>Administración</span>
                </a>
            <?php endif; ?>
            <a href="<?= $base_url ?>index.php" class="mod-sidebar-link" title="Ir al sitio público">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>Sitio público</span>
            </a>
            <a href="#" class="mod-sidebar-link mod-sidebar-link--logout" data-bs-toggle="modal" data-bs-target="#modalConfirmLogout" title="Cerrar sesión">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Cerrar sesión</span>
            </a>
        </div>
    </aside>

    <main class="adm-main">

        <section class="adm-section active" id="sec-aprobar">
            <div class="adm-section-head">
                <div class="adm-title-group">
                    <span class="adm-title-accent" aria-hidden="true"></span>
                    <div>
                        <h1>Aprobar subastas</h1>
                        <p>Valida fotos, descripción y categoría antes de que cada publicación sea visible.</p>
                    </div>
                </div>
                <div class="mod-summary-pills">
                    <span class="adm-badge adm-badge--pendiente" id="modPendingBadge">
                        <i class="fa-regular fa-clock me-1"></i> <?= $totalPendientesKpi ?> Por validar
                    </span>
                </div>
            </div>
            <hr class="adm-section-divider">

            <?php include __DIR__ . '/partials/kpi-subastas.php'; ?>

            <div class="adm-toolbar mod-approval-toolbar">
                <div class="adm-toolbar-left">
                    <div class="adm-input-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span class="adm-search-divider" aria-hidden="true"></span>
                        <input type="search" id="modApprovalSearch" placeholder="Buscar artículo o vendedor..." aria-label="Buscar publicación pendiente">
                    </div>
                    <select class="adm-select-filter" id="modApprovalCatFilter" aria-label="Filtrar por categoría">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categoriasModerador as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['nombre']) ?>">
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php include __DIR__ . '/partials/tabla-pendientes.php'; ?>
        </section>

        <section class="adm-section" id="sec-administrar">
            <div class="adm-section-head">
                <div class="adm-title-group">
                    <span class="adm-title-accent" aria-hidden="true"></span>
                    <div>
                        <h1>Administrar subastas</h1>
                        <p>Consulta todas las subastas y gestiona su estado en cualquier etapa del ciclo.</p>
                    </div>
                </div>
                <div class="mod-summary-pills">
                    <span class="adm-badge adm-badge--activa" id="modManageTotalBadge">
                        <i class="fa-solid fa-layer-group me-1"></i> <?= count($todasSubastas) ?> Subastas
                    </span>
                </div>
            </div>
            <hr class="adm-section-divider">

            <div class="adm-toolbar mod-approval-toolbar">
                <div class="adm-toolbar-left">
                    <div class="adm-input-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span class="adm-search-divider" aria-hidden="true"></span>
                        <input type="search" id="modManageSearch" placeholder="Buscar artículo, vendedor o ID..." aria-label="Buscar subasta">
                    </div>
                    <select class="adm-select-filter" id="modManageCatFilter" aria-label="Filtrar por categoría">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categoriasModerador as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['nombre']) ?>">
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select class="adm-select-filter" id="modManageStatusFilter" aria-label="Filtrar por estado">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="activa">Activa</option>
                        <option value="finalizada">Finalizada</option>
                        <option value="sin_ofertas">Sin ofertas</option>
                        <option value="rechazada">Rechazada</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
            </div>

            <?php include __DIR__ . '/partials/tabla-administrar-subastas.php'; ?>
        </section>
    </main>

    <?php include __DIR__ . '/partials/modal-detalle.php'; ?>
    <?php include __DIR__ . '/partials/modal-confirmar-decision.php'; ?>
    <?php include __DIR__ . '/partials/modal-administrar-estado.php'; ?>
    <?php include __DIR__ . '/partials/modal-logout.php'; ?>
    <?php include dirname(__DIR__, 2) . '/includes/modal-sesion-inactiva.php'; ?>

    <script>
        window.MOD_BASE_URL = <?= json_encode($base_url, JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $base_url ?>assets/js/sesion-inactividad.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/sesion-inactividad.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/toast-ppi.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/toast-ppi.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/moderador-kpis.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/moderador-kpis.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/moderador-paginacion.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/moderador-paginacion.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/moderador-filtros.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/moderador-filtros.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/moderador-cola.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/moderador-cola.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/moderador-administrar.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/moderador-administrar.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/moderador-dashboard.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/moderador-dashboard.js') ?>"></script>
</body>

</html>