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

$base_url = BASE_URL;
$perfilAdmin = requerirAdministradorAutenticado(false);
$alertasTopbar = obtenerAlertasTopbarAdmin();
$kpisAdmin = obtenerKpisAdmin('30d');
$graficosInicio = obtenerGraficosInicioAdmin('7d');
$resumenInferiorInicio = obtenerResumenInferiorInicioAdmin($base_url);
$rolesAdmin = obtenerRolesAdmin();
$usuariosAdminInicial = obtenerUsuariosAdmin('', 0, 1, 5);
$categoriasAdmin = obtenerCategoriasAdmin();
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

            <!-- Bottom Row: Usuarios recientes & Subastas destacadas -->
            <?php include __DIR__ . '/partials/resumen-inferior-inicio.php'; ?>
        </section>

        <?php include __DIR__ . '/partials/gestion-usuarios.php'; ?>

        <?php include __DIR__ . '/partials/gestion-subastas.php'; ?>

        <!-- Sección: Categorías -->
        <section class="adm-section" id="sec-categorias">
            <div class="adm-section-head">
                <div class="adm-title-group">
                    <span class="adm-title-accent" aria-hidden="true"></span>
                    <div>
                        <h1>Categorías</h1>
                        <p>Crea, edita, publica o desactiva las categorías visibles en Gavel<span class="brand-inline">.net</span>.</p>
                    </div>
                </div>
                <button type="button" class="adm-btn-primary" id="btnNuevaCategoria">
                    <i class="fa-solid fa-plus"></i> Nueva categoría
                </button>
            </div>
            <hr class="adm-section-divider">

            <div class="adm-toolbar adm-cat-toolbar">
                <div class="adm-toolbar-left">
                    <div class="adm-input-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span class="adm-search-divider" aria-hidden="true"></span>
                        <input type="search" id="admCatSearch" placeholder="Buscar categoría por nombre o descripción..." aria-label="Buscar categoría">
                    </div>
                    <select class="adm-select-filter" id="admCatStatusFilter" aria-label="Filtrar categorías por estado">
                        <option value="todas">Todos los estados</option>
                        <option value="publicadas">Publicadas</option>
                        <option value="desactivadas">Desactivadas</option>
                    </select>
                </div>
            </div>

            <div class="adm-cat-grid-v2" id="admCatGrid">
                <?php foreach ($categorias_admin as $cat):
                    $esActiva = (int)$cat['estado'] === 1;
                    $imgUrl = $base_url . ltrim((string)$cat['imagen'], '/');
                    $descTexto = trim((string)$cat['descripcion']) !== ''
                        ? (string)$cat['descripcion']
                        : 'Sin descripción registrada.';
                    $totalSub = (int)$cat['total_subastas'];
                ?>
                    <article class="adm-cat-card-v2 <?= $esActiva ? '' : 'is-inactive' ?>"
                        data-id="<?= (int)$cat['id_categoria'] ?>"
                        data-nombre="<?= htmlspecialchars($cat['nombre']) ?>"
                        data-descripcion="<?= htmlspecialchars($descTexto) ?>"
                        data-estado="<?= $esActiva ? 'publicadas' : 'desactivadas' ?>">
                        <div class="adm-cat-cover">
                            <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($cat['nombre']) ?>" loading="lazy">
                        </div>
                        <div class="adm-cat-body">
                            <div class="adm-cat-meta-row">
                                <span class="adm-cat-status <?= $esActiva ? 'is-published' : 'is-draft' ?>">
                                    <span class="adm-cat-status-dot"></span>
                                    <?= $esActiva ? 'Publicada' : 'Desactivada' ?>
                                </span>
                                <span class="adm-cat-auctions-count">
                                    <?= $totalSub ?> <?= $totalSub === 1 ? 'subasta' : 'subastas' ?>
                                </span>
                            </div>
                            <h3 class="adm-cat-title"><?= htmlspecialchars($cat['nombre']) ?></h3>
                            <p class="adm-cat-desc"><?= htmlspecialchars($descTexto) ?></p>
                        </div>
                        <div class="adm-cat-footer">
                            <button type="button"
                                class="adm-cat-btn-toggle <?= $esActiva ? 'is-deactivate' : 'is-publish' ?>"
                                data-cat-action="toggle"
                                data-id="<?= (int)$cat['id_categoria'] ?>">
                                <i class="fa-solid <?= $esActiva ? 'fa-eye-slash' : 'fa-globe' ?>"></i>
                                <span><?= $esActiva ? 'Desactivar' : 'Publicar' ?></span>
                            </button>
                            <div class="adm-cat-icon-actions">
                                <button type="button"
                                    class="adm-cat-icon-btn adm-cat-icon-btn--edit"
                                    title="Editar categoría"
                                    aria-label="Editar <?= htmlspecialchars($cat['nombre']) ?>"
                                    data-cat-action="edit"
                                    data-id="<?= (int)$cat['id_categoria'] ?>">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button type="button"
                                    class="adm-cat-icon-btn adm-cat-icon-btn--delete"
                                    title="<?= (int)$cat['total_productos'] > 0 ? 'Tiene productos asociados (desactívala en su lugar)' : 'Eliminar categoría' ?>"
                                    aria-label="Eliminar <?= htmlspecialchars($cat['nombre']) ?>"
                                    data-cat-action="delete"
                                    data-id="<?= (int)$cat['id_categoria'] ?>">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="adm-cat-empty <?= empty($categorias_admin) ? '' : 'd-none' ?>" id="admCatEmpty">
                <div class="adm-cat-empty-icon"><i class="fa-regular fa-folder-open" id="admCatEmptyIcon"></i></div>
                <h3 class="h6 text-white fw-bold mb-1" id="admCatEmptyTitle">No se encontraron categorías</h3>
                <p class="small mb-3" style="color:#9aa392;" id="admCatEmptyText">Prueba con otro término de búsqueda o cambia el filtro de estado.</p>
                <button type="button" class="mod-btn-sm mod-btn-cancel <?= empty($categorias_admin) ? 'd-none' : '' ?>" id="btnCatResetFilters">
                    <i class="fa-solid fa-rotate-left"></i> Limpiar filtros
                </button>
            </div>
        </section>

        <!-- Sección: Reportes y estadísticas -->
        <section class="adm-section" id="sec-reportes">
            <div class="adm-section-head adm-rep-head">
                <div class="adm-title-group">
                    <span class="adm-title-accent" aria-hidden="true"></span>
                    <div>
                        <h1>Reportes</h1>
                        <p id="admRepPeriodSubtitle">Resumen del 24 de oct. al 23 de nov. de 2026</p>
                    </div>
                </div>

                <div class="adm-rep-controls">
                    <label class="adm-pill-select adm-rep-period-select">
                        <i class="fa-regular fa-calendar"></i>
                        <select id="admRepPeriod" aria-label="Periodo del reporte">
                            <option value="7d">Últimos 7 días</option>
                            <option value="30d" selected>Últimos 30 días</option>
                            <option value="90d">Últimos 90 días</option>
                        </select>
                    </label>
                    <button type="button" class="adm-rep-btn adm-rep-btn--ghost" id="admRepPrintBtn" title="Imprimir o guardar como PDF">
                        <i class="fa-solid fa-print"></i>
                        <span>Imprimir / PDF</span>
                    </button>
                    <button type="button" class="adm-rep-btn adm-rep-btn--export" id="admRepCsvBtn">
                        <i class="fa-solid fa-download"></i>
                        <span>Exportar CSV</span>
                    </button>
                </div>
            </div>

            <!-- Navegación interna de reportes -->
            <div class="adm-rep-nav-wrap">
                <div class="adm-rep-tabs" role="tablist" aria-label="Categorías de reportes">
                    <button type="button" class="adm-rep-tab active" role="tab" aria-selected="true" aria-controls="rep-panel-subastas" id="rep-tab-subastas" data-rep-tab="subastas">
                        Subastas
                    </button>
                    <button type="button" class="adm-rep-tab" role="tab" aria-selected="false" aria-controls="rep-panel-categorias" id="rep-tab-categorias" data-rep-tab="categorias">
                        Categorías
                    </button>
                    <button type="button" class="adm-rep-tab" role="tab" aria-selected="false" aria-controls="rep-panel-usuarios" id="rep-tab-usuarios" data-rep-tab="usuarios">
                        Usuarios
                    </button>
                    <button type="button" class="adm-rep-tab" role="tab" aria-selected="false" aria-controls="rep-panel-moderacion" id="rep-tab-moderacion" data-rep-tab="moderacion">
                        Moderación y contacto
                    </button>
                </div>
                <span class="adm-rep-updated">Actualizado hoy · 10:00 p. m.</span>
            </div>

            <!-- Franja unificada de métricas del periodo -->
            <div class="adm-rep-strip" id="admRepStrip" aria-live="polite"></div>

            <!-- PESTAÑA 1: SUBASTAS -->
            <div class="adm-rep-pane active" id="rep-panel-subastas" role="tabpanel" aria-labelledby="rep-tab-subastas">
                <div class="adm-rep-grid adm-rep-grid--wide-left">
                    <article class="adm-rep-card">
                        <header class="adm-rep-card-head">
                            <div>
                                <h2 class="adm-rep-card-title">Monto vendido por tramo</h2>
                                <p class="adm-rep-card-sub">Suma de subastas cerradas con ganador</p>
                            </div>
                            <span class="adm-rep-unit">USD ($)</span>
                        </header>
                        <div class="adm-rep-card-body">
                            <div class="adm-chart-wrap">
                                <svg id="admRepAuctionChart" class="adm-svg-chart" viewBox="0 0 560 210" role="img" aria-label="Monto vendido en subastas"></svg>
                                <div id="admRepAuctionTooltip" class="adm-chart-tooltip"></div>
                            </div>
                        </div>
                    </article>

                    <article class="adm-rep-card">
                        <header class="adm-rep-card-head">
                            <div>
                                <h2 class="adm-rep-card-title">Estado de las subastas</h2>
                                <p class="adm-rep-card-sub">Distribución según cierre y revisión</p>
                            </div>
                        </header>
                        <div class="adm-rep-card-body">
                            <div class="adm-rep-stackbar" id="admRepStatusBar" aria-hidden="true"></div>
                            <ul class="adm-rep-stat-list" id="admRepStatusList"></ul>
                        </div>
                    </article>
                </div>

                <article class="adm-rep-card adm-rep-table-wrap">
                    <header class="adm-rep-card-head adm-rep-card-head--wrap">
                        <div>
                            <h2 class="adm-rep-card-title">Detalle de subastas</h2>
                            <p class="adm-rep-card-sub" id="admRepAuctionTableSub">Mostrando subastas del periodo seleccionado</p>
                        </div>
                        <div class="adm-rep-filter-group" role="group" aria-label="Filtrar tabla por estado">
                            <button type="button" class="adm-rep-chip active" data-rep-status="todas">Todas</button>
                            <button type="button" class="adm-rep-chip" data-rep-status="finalizada">Finalizadas</button>
                            <button type="button" class="adm-rep-chip" data-rep-status="activa">Activas</button>
                            <button type="button" class="adm-rep-chip" data-rep-status="sin_ofertas">Sin ofertas</button>
                            <button type="button" class="adm-rep-chip" data-rep-status="pendiente">Pendientes</button>
                        </div>
                    </header>
                    <div class="adm-table-responsive">
                        <table class="adm-rep-table" id="admRepAuctionTable">
                            <thead>
                                <tr>
                                    <th>Artículo</th>
                                    <th>Categoría</th>
                                    <th>Vendedor</th>
                                    <th class="is-num">Precio inicial</th>
                                    <th class="is-num">Puja final</th>
                                    <th class="is-num">Pujas</th>
                                    <th>Estado</th>
                                    <th>Cierre</th>
                                </tr>
                            </thead>
                            <tbody id="admRepAuctionTbody"></tbody>
                        </table>
                    </div>
                </article>
            </div>

            <!-- PESTAÑA 2: CATEGORÍAS -->
            <div class="adm-rep-pane" id="rep-panel-categorias" role="tabpanel" aria-labelledby="rep-tab-categorias">
                <div class="adm-rep-grid adm-rep-grid--wide-left">
                    <article class="adm-rep-card">
                        <header class="adm-rep-card-head">
                            <div>
                                <h2 class="adm-rep-card-title">Participación por categoría</h2>
                                <p class="adm-rep-card-sub">Ordenado por monto vendido en el periodo</p>
                            </div>
                        </header>
                        <div class="adm-rep-card-body">
                            <div class="adm-rep-bars" id="admRepCatBars"></div>
                        </div>
                    </article>

                    <article class="adm-rep-card">
                        <header class="adm-rep-card-head">
                            <div>
                                <h2 class="adm-rep-card-title">Lectura del periodo</h2>
                                <p class="adm-rep-card-sub">Puntos destacados del catálogo</p>
                            </div>
                        </header>
                        <div class="adm-rep-card-body">
                            <ul class="adm-rep-notes" id="admRepCatNotes"></ul>
                        </div>
                    </article>
                </div>

                <article class="adm-rep-card adm-rep-table-wrap">
                    <header class="adm-rep-card-head">
                        <div>
                            <h2 class="adm-rep-card-title">Comparativa por categoría</h2>
                            <p class="adm-rep-card-sub">Rendimiento consolidado de subastas y pujas</p>
                        </div>
                    </header>
                    <div class="adm-table-responsive">
                        <table class="adm-rep-table">
                            <thead>
                                <tr>
                                    <th>Categoría</th>
                                    <th class="is-num">Subastas</th>
                                    <th class="is-num">Cerradas</th>
                                    <th class="is-num">Pujas</th>
                                    <th class="is-num">Monto vendido</th>
                                    <th class="is-num">Promedio / subasta</th>
                                </tr>
                            </thead>
                            <tbody id="admRepCatTbody"></tbody>
                        </table>
                    </div>
                </article>
            </div>

            <!-- PESTAÑA 3: USUARIOS -->
            <div class="adm-rep-pane" id="rep-panel-usuarios" role="tabpanel" aria-labelledby="rep-tab-usuarios">
                <div class="adm-rep-grid adm-rep-grid--wide-left">
                    <article class="adm-rep-card">
                        <header class="adm-rep-card-head">
                            <div>
                                <h2 class="adm-rep-card-title">Nuevos registros</h2>
                                <p class="adm-rep-card-sub">Altas de cuentas verificadas en el periodo</p>
                            </div>
                            <span class="adm-rep-unit">Cuentas</span>
                        </header>
                        <div class="adm-rep-card-body">
                            <div class="adm-chart-wrap">
                                <svg id="admRepUsersChart" class="adm-svg-chart" viewBox="0 0 560 210" role="img" aria-label="Evolución de nuevos registros"></svg>
                                <div id="admRepUsersTooltip" class="adm-chart-tooltip"></div>
                            </div>
                        </div>
                    </article>

                    <article class="adm-rep-card">
                        <header class="adm-rep-card-head">
                            <div>
                                <h2 class="adm-rep-card-title">Composición de cuentas</h2>
                                <p class="adm-rep-card-sub">Roles registrados y estado actual</p>
                            </div>
                        </header>
                        <div class="adm-rep-card-body">
                            <ul class="adm-rep-stat-list" id="admRepRoleList"></ul>
                            <hr class="adm-rep-inner-divider">
                            <div class="adm-rep-mini-pair" id="admRepUserHealth"></div>
                        </div>
                    </article>
                </div>

                <article class="adm-rep-card adm-rep-table-wrap">
                    <header class="adm-rep-card-head">
                        <div>
                            <h2 class="adm-rep-card-title">Usuarios con mayor actividad</h2>
                            <p class="adm-rep-card-sub">Combinando subastas publicadas y pujas realizadas</p>
                        </div>
                    </header>
                    <div class="adm-table-responsive">
                        <table class="adm-rep-table">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Ubicación</th>
                                    <th class="is-num">Subastas publicadas</th>
                                    <th class="is-num">Pujas realizadas</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="admRepUsersTbody"></tbody>
                        </table>
                    </div>
                </article>
            </div>

            <!-- PESTAÑA 4: MODERACIÓN Y CONTACTO -->
            <div class="adm-rep-pane" id="rep-panel-moderacion" role="tabpanel" aria-labelledby="rep-tab-moderacion">
                <div class="adm-rep-grid adm-rep-grid--equal">
                    <article class="adm-rep-card">
                        <header class="adm-rep-card-head">
                            <div>
                                <h2 class="adm-rep-card-title">Revisión de subastas</h2>
                                <p class="adm-rep-card-sub">Resoluciones del equipo de moderación</p>
                            </div>
                            <a href="<?= $base_url ?>views/moderador/dashboard.php" class="adm-rep-subtle-link">
                                Ir a moderación <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </header>
                        <div class="adm-rep-card-body">
                            <div class="adm-rep-stackbar" id="admRepModBar" aria-hidden="true"></div>
                            <ul class="adm-rep-stat-list" id="admRepModList"></ul>
                        </div>
                    </article>

                    <article class="adm-rep-card">
                        <header class="adm-rep-card-head">
                            <div>
                                <h2 class="adm-rep-card-title">Bandeja de contacto</h2>
                                <p class="adm-rep-card-sub">Consultas recibidas desde el formulario público</p>
                            </div>
                        </header>
                        <div class="adm-rep-card-body">
                            <div class="adm-rep-mini-pair" id="admRepContactPair"></div>
                            <hr class="adm-rep-inner-divider">
                            <ul class="adm-rep-contact-list" id="admRepContactList"></ul>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- Sección: Configuración del sistema -->
        <section class="adm-section" id="sec-configuracion">
            <div class="adm-section-head adm-rep-head">
                <div class="adm-title-group">
                    <span class="adm-title-accent" aria-hidden="true"></span>
                    <div>
                        <h1>Configuración</h1>
                        <p>Ajusta las reglas de subastas, seguridad y operación de Gavel<span class="brand-inline">.net</span>.</p>
                    </div>
                </div>

                <div class="adm-rep-controls">
                    <span class="adm-cfg-status" id="admCfgStatus">Sin cambios pendientes</span>
                    <button type="button" class="adm-rep-btn adm-rep-btn--ghost" id="admCfgResetBtn" disabled>
                        Descartar
                    </button>
                    <button type="button" class="adm-rep-btn adm-rep-btn--export" id="admCfgSaveBtn" disabled>
                        <i class="fa-solid fa-check"></i>
                        <span>Guardar cambios</span>
                    </button>
                </div>
            </div>

            <!-- Pestañas de configuración -->
            <div class="adm-rep-nav-wrap">
                <div class="adm-rep-tabs" role="tablist" aria-label="Grupos de configuración">
                    <button type="button" class="adm-rep-tab active" role="tab" aria-selected="true" aria-controls="cfg-panel-subastas" id="cfg-tab-subastas" data-cfg-tab="subastas">
                        Subastas y pujas
                    </button>
                    <button type="button" class="adm-rep-tab" role="tab" aria-selected="false" aria-controls="cfg-panel-seguridad" id="cfg-tab-seguridad" data-cfg-tab="seguridad">
                        Seguridad y cuentas
                    </button>
                    <button type="button" class="adm-rep-tab" role="tab" aria-selected="false" aria-controls="cfg-panel-correo" id="cfg-tab-correo" data-cfg-tab="correo">
                        Correo y avisos
                    </button>
                    <button type="button" class="adm-rep-tab" role="tab" aria-selected="false" aria-controls="cfg-panel-operacion" id="cfg-tab-operacion" data-cfg-tab="operacion">
                        Operación
                    </button>
                </div>
            </div>

            <form id="admCfgForm" novalidate>
                <!-- PESTAÑA 1: SUBASTAS Y PUJAS -->
                <div class="adm-cfg-pane active" id="cfg-panel-subastas" role="tabpanel" aria-labelledby="cfg-tab-subastas">
                    <div class="adm-cfg-group">
                        <div class="adm-cfg-aside">
                            <h2>Publicación y límites</h2>
                            <p>Define las condiciones base que deben cumplir los artículos antes de salir al catálogo público.</p>
                        </div>
                        <div class="adm-cfg-card">
                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgRevisionPrevia" class="adm-cfg-label">Revisión previa de moderación</label>
                                    <span class="adm-cfg-help">Requiere que un moderador apruebe cada publicación antes de activarse.</span>
                                </div>
                                <label class="adm-switch">
                                    <input type="checkbox" id="cfgRevisionPrevia" name="revision_previa" checked>
                                    <span class="adm-switch-slider"></span>
                                </label>
                            </div>

                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgPrecioMin" class="adm-cfg-label">Precio inicial mínimo</label>
                                    <span class="adm-cfg-help">Monto base más bajo permitido al crear una nueva subasta.</span>
                                </div>
                                <div class="adm-cfg-input-prefix">
                                    <span>$</span>
                                    <input type="number" id="cfgPrecioMin" name="precio_minimo" value="5.00" min="1" step="0.50">
                                </div>
                            </div>

                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgIncMin" class="adm-cfg-label">Incremento mínimo sugerido</label>
                                    <span class="adm-cfg-help">Valor predeterminado entre una puja y la siguiente.</span>
                                </div>
                                <div class="adm-cfg-input-prefix">
                                    <span>$</span>
                                    <input type="number" id="cfgIncMin" name="incremento_minimo" value="2.00" min="0.50" step="0.50">
                                </div>
                            </div>

                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgDuracionMax" class="adm-cfg-label">Duración máxima permitida</label>
                                    <span class="adm-cfg-help">Tiempo tope desde la apertura hasta el cierre automático.</span>
                                </div>
                                <select class="adm-cfg-select" id="cfgDuracionMax" name="duracion_maxima">
                                    <option value="3">3 días</option>
                                    <option value="7" selected>7 días</option>
                                    <option value="14">14 días</option>
                                    <option value="30">30 días</option>
                                </select>
                            </div>

                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgMaxFotos" class="adm-cfg-label">Imágenes máximas por artículo</label>
                                    <span class="adm-cfg-help">Cantidad de fotografías que puede subir el vendedor por publicación.</span>
                                </div>
                                <select class="adm-cfg-select" id="cfgMaxFotos" name="max_imagenes">
                                    <option value="3">Hasta 3 fotos</option>
                                    <option value="5" selected>Hasta 5 fotos</option>
                                    <option value="8">Hasta 8 fotos</option>
                                    <option value="10">Hasta 10 fotos</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="adm-cfg-group">
                        <div class="adm-cfg-aside">
                            <h2>Dinámica de cierre</h2>
                            <p>Reglas para mantener pujas justas en los últimos minutos y controlar cancelaciones.</p>
                        </div>
                        <div class="adm-cfg-card">
                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgAntiSniping" class="adm-cfg-label">Extensión de cierre (anti-sniping)</label>
                                    <span class="adm-cfg-help">Añade 2 minutos extra si entra una puja durante el último minuto.</span>
                                </div>
                                <label class="adm-switch">
                                    <input type="checkbox" id="cfgAntiSniping" name="anti_sniping" checked>
                                    <span class="adm-switch-slider"></span>
                                </label>
                            </div>

                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgCancelSinPujas" class="adm-cfg-label">Cancelación por vendedor sin ofertas</label>
                                    <span class="adm-cfg-help">Permite retirar una subasta activa únicamente si todavía no tiene pujas.</span>
                                </div>
                                <label class="adm-switch">
                                    <input type="checkbox" id="cfgCancelSinPujas" name="cancelar_sin_pujas" checked>
                                    <span class="adm-switch-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PESTAÑA 2: SEGURIDAD Y CUENTAS -->
                <div class="adm-cfg-pane" id="cfg-panel-seguridad" role="tabpanel" aria-labelledby="cfg-tab-seguridad">
                    <div class="adm-cfg-group">
                        <div class="adm-cfg-aside">
                            <h2>Registro y verificación</h2>
                            <p>Controla cómo se validan las cuentas nuevas y los códigos enviados por correo.</p>
                        </div>
                        <div class="adm-cfg-card">
                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgRegistroPublico" class="adm-cfg-label">Permitir nuevos registros</label>
                                    <span class="adm-cfg-help">Habilita o pausa la creación de cuentas desde el sitio público.</span>
                                </div>
                                <label class="adm-switch">
                                    <input type="checkbox" id="cfgRegistroPublico" name="registro_publico" checked>
                                    <span class="adm-switch-slider"></span>
                                </label>
                            </div>

                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgVerifCorreo" class="adm-cfg-label">Verificación obligatoria de correo</label>
                                    <span class="adm-cfg-help">Solicita confirmar un código de 6 dígitos antes de activar la cuenta.</span>
                                </div>
                                <label class="adm-switch">
                                    <input type="checkbox" id="cfgVerifCorreo" name="verificacion_correo" checked>
                                    <span class="adm-switch-slider"></span>
                                </label>
                            </div>

                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgExpiraCodigo" class="adm-cfg-label">Vigencia del código de verificación</label>
                                    <span class="adm-cfg-help">Tiempo antes de que expire un código de registro o recuperación.</span>
                                </div>
                                <select class="adm-cfg-select" id="cfgExpiraCodigo" name="expira_codigo">
                                    <option value="5">5 minutos</option>
                                    <option value="10">10 minutos</option>
                                    <option value="15" selected>15 minutos</option>
                                    <option value="30">30 minutos</option>
                                </select>
                            </div>

                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgMaxIntentos" class="adm-cfg-label">Intentos máximos por código</label>
                                    <span class="adm-cfg-help">Invalida el código automáticamente al superar este límite.</span>
                                </div>
                                <select class="adm-cfg-select" id="cfgMaxIntentos" name="max_intentos">
                                    <option value="3">3 intentos</option>
                                    <option value="5" selected>5 intentos</option>
                                    <option value="8">8 intentos</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="adm-cfg-group">
                        <div class="adm-cfg-aside">
                            <h2>Sesiones activas</h2>
                            <p>Define cuánto tiempo permanece abierta una sesión sin actividad.</p>
                        </div>
                        <div class="adm-cfg-card">
                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgInactividad" class="adm-cfg-label">Cierre por inactividad</label>
                                    <span class="adm-cfg-help">Muestra el aviso de extensión y cierra la sesión al vencer el tiempo.</span>
                                </div>
                                <select class="adm-cfg-select" id="cfgInactividad" name="tiempo_inactividad">
                                    <option value="15">15 minutos</option>
                                    <option value="30" selected>30 minutos</option>
                                    <option value="60">60 minutos</option>
                                    <option value="0">Sin límite</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PESTAÑA 3: CORREO Y AVISOS -->
                <div class="adm-cfg-pane" id="cfg-panel-correo" role="tabpanel" aria-labelledby="cfg-tab-correo">
                    <div class="adm-cfg-group">
                        <div class="adm-cfg-aside">
                            <h2>Identidad de envío</h2>
                            <p>Datos visibles en los correos automáticos y en el formulario de contacto.</p>
                        </div>
                        <div class="adm-cfg-card">
                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgRemitente" class="adm-cfg-label">Nombre del remitente</label>
                                    <span class="adm-cfg-help">Nombre que ven los usuarios en su bandeja de entrada.</span>
                                </div>
                                <input type="text" class="adm-cfg-input" id="cfgRemitente" name="nombre_remitente" value="Gavel.net">
                            </div>

                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgCorreoSoporte" class="adm-cfg-label">Correo de soporte</label>
                                    <span class="adm-cfg-help">Dirección de respuesta para dudas y mensajes del sitio.</span>
                                </div>
                                <input type="email" class="adm-cfg-input" id="cfgCorreoSoporte" name="correo_soporte" value="soporte@gavel.net">
                            </div>
                        </div>
                    </div>

                    <div class="adm-cfg-group">
                        <div class="adm-cfg-aside">
                            <h2>Notificaciones automáticas</h2>
                            <p>Elige qué eventos disparan un aviso por correo electrónico.</p>
                        </div>
                        <div class="adm-cfg-card">
                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgAvisoModeracion" class="adm-cfg-label">Resultado de moderación al vendedor</label>
                                    <span class="adm-cfg-help">Notificar por correo cuando una subasta es aprobada o rechazada.</span>
                                </div>
                                <label class="adm-switch">
                                    <input type="checkbox" id="cfgAvisoModeracion" name="aviso_moderacion" checked>
                                    <span class="adm-switch-slider"></span>
                                </label>
                            </div>

                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgAvisoContacto" class="adm-cfg-label">Alerta de nuevos mensajes de contacto</label>
                                    <span class="adm-cfg-help">Enviar copia al correo de soporte cuando alguien escribe desde Contacto.</span>
                                </div>
                                <label class="adm-switch">
                                    <input type="checkbox" id="cfgAvisoContacto" name="aviso_contacto" checked>
                                    <span class="adm-switch-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PESTAÑA 4: OPERACIÓN -->
                <div class="adm-cfg-pane" id="cfg-panel-operacion" role="tabpanel" aria-labelledby="cfg-tab-operacion">
                    <div class="adm-cfg-group">
                        <div class="adm-cfg-aside">
                            <h2>Aviso en portada</h2>
                            <p>Muestra una franja breve en el sitio público para comunicados puntuales.</p>
                        </div>
                        <div class="adm-cfg-card">
                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgBannerActivo" class="adm-cfg-label">Mostrar aviso global</label>
                                    <span class="adm-cfg-help">Visible en la parte superior del catálogo para todos los visitantes.</span>
                                </div>
                                <label class="adm-switch">
                                    <input type="checkbox" id="cfgBannerActivo" name="banner_activo">
                                    <span class="adm-switch-slider"></span>
                                </label>
                            </div>
                            <div class="adm-cfg-row adm-cfg-row--stacked is-collapsed" id="cfgBannerTextRow">
                                <label for="cfgBannerTexto" class="adm-cfg-label">Texto del aviso</label>
                                <input type="text" class="adm-cfg-input adm-cfg-input--full" id="cfgBannerTexto" name="banner_texto" maxlength="140" value="Nuevas subastas verificadas disponibles este fin de semana." placeholder="Escribe un mensaje breve...">
                            </div>
                        </div>
                    </div>

                    <div class="adm-cfg-group">
                        <div class="adm-cfg-aside">
                            <h2>Estado del servicio</h2>
                            <p>Úsalo solo cuando necesites pausar la plataforma para mantenimiento planificado.</p>
                        </div>
                        <div class="adm-cfg-card adm-cfg-card--delicate" id="cfgMaintCard">
                            <div class="adm-cfg-row">
                                <div class="adm-cfg-row-info">
                                    <label for="cfgMantenimiento" class="adm-cfg-label">Modo mantenimiento</label>
                                    <span class="adm-cfg-help">Pausa temporalmente nuevas pujas y publicaciones; los administradores mantienen acceso.</span>
                                </div>
                                <label class="adm-switch adm-switch--salmon">
                                    <input type="checkbox" id="cfgMantenimiento" name="modo_mantenimiento">
                                    <span class="adm-switch-slider"></span>
                                </label>
                            </div>
                            <div class="adm-cfg-row adm-cfg-row--stacked is-collapsed" id="cfgMaintMsgRow">
                                <label for="cfgMaintMsg" class="adm-cfg-label">Mensaje para visitantes</label>
                                <textarea class="adm-cfg-textarea" id="cfgMaintMsg" name="mensaje_mantenimiento" rows="2" maxlength="220">Estamos realizando mejoras en Gavel.net. Volvemos en unos minutos.</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </main>

    <!-- MODAL DEMO: NUEVA SUBASTA -->
    <div class="modal fade adm-modal" id="modalNuevaSubasta" tabindex="-1" aria-labelledby="modalNuevaSubastaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form data-adm-demo-form="Subasta de prueba creada (solo simulación visual).">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="modalNuevaSubastaLabel">
                            <i class="fa-solid fa-gavel me-2" style="color:#a7cc3b;"></i>Nueva subasta
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body d-flex flex-column gap-3">
                        <div>
                            <label class="form-label small text-secondary">Título del artículo</label>
                            <input type="text" class="form-control" placeholder="Ej. Nintendo 64 Edición Dorada" required>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small text-secondary">Categoría</label>
                                <select class="form-select">
                                    <option>Coleccionables</option>
                                    <option>Videojuegos</option>
                                    <option>Electrónica</option>
                                    <option>Música</option>
                                    <option>Libros y cómics</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-secondary">Precio inicial ($)</label>
                                <input type="number" class="form-control" min="1" step="0.01" placeholder="100.00" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="adm-btn-primary" style="height:36px; font-size:0.86rem;">Publicar (demo)</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL CRUD: CREAR / EDITAR CATEGORÍA -->
    <div class="modal fade adm-modal" id="modalCategoriaCrud" tabindex="-1" aria-labelledby="modalCategoriaCrudLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formCategoriaCrud" enctype="multipart/form-data" novalidate>
                    <input type="hidden" id="catFormId" name="id_categoria" value="0">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalCategoriaCrudLabel">
                            <i class="fa-solid fa-layer-group" style="color:var(--adm-accent);"></i>
                            <span id="catModalTitleText">Nueva categoría</span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body d-flex flex-column gap-3">
                        <!-- Imagen de portada -->
                        <div>
                            <label class="form-label small text-secondary mb-2">Imagen de portada</label>
                            <div class="adm-cat-dropzone" id="catImageDropzone" role="button" tabindex="0" aria-label="Seleccionar imagen de categoría">
                                <input type="file" id="catFormImagen" name="imagen" accept="image/jpeg,image/png,image/webp" class="d-none">
                                <div class="adm-cat-dropzone-preview d-none" id="catImagePreviewWrap">
                                    <img id="catImagePreview" src="" alt="Vista previa de categoría">
                                    <div class="adm-cat-dropzone-overlay">
                                        <span><i class="fa-solid fa-camera me-1"></i> Cambiar imagen</span>
                                    </div>
                                </div>
                                <div class="adm-cat-dropzone-placeholder" id="catImagePlaceholder">
                                    <div class="adm-cat-dropzone-icon">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                    </div>
                                    <div class="fw-semibold text-white small">Arrastra una imagen o haz clic para subir</div>
                                    <div style="font-size:0.76rem; color:#8d9685;">JPG, PNG o WEBP · Máximo 5 MB</div>
                                </div>
                            </div>
                        </div>

                        <!-- Nombre -->
                        <div>
                            <label for="catFormNombre" class="form-label small text-secondary">Nombre de la categoría <span style="color:#d8685d;">*</span></label>
                            <input type="text" class="form-control" id="catFormNombre" name="nombre" maxlength="50" placeholder="Ej. Relojes de lujo, Vinilos, Antigüedades..." required>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="catFormDescripcion" class="form-label small text-secondary mb-0">Descripción</label>
                                <span style="font-size:0.74rem; color:#8d9685;" id="catDescCounter">0/255</span>
                            </div>
                            <textarea class="form-control" id="catFormDescripcion" name="descripcion" rows="3" maxlength="255" placeholder="Describe brevemente qué tipo de artículos pertenecen a esta categoría..."></textarea>
                        </div>

                        <!-- Publicar / Estado -->
                        <div class="adm-cat-publish-box">
                            <div>
                                <div class="fw-semibold text-white small" id="catEstadoTitle">Publicar en el sitio público</div>
                                <div style="font-size:0.76rem; color:#9aa392;" id="catEstadoHelp">Visible en el catálogo, menú y registro de productos.</div>
                            </div>
                            <label class="adm-switch mb-0">
                                <input type="checkbox" id="catFormEstado" name="estado" value="1" checked>
                                <span class="adm-switch-slider"></span>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="mod-btn-sm mod-btn-cancel px-3" style="height:38px;" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="adm-btn-primary" id="btnGuardarCategoria" style="height:38px; font-size:0.86rem;">
                            <i class="fa-solid fa-check"></i>
                            <span id="btnGuardarCategoriaText">Crear y publicar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL CONFIRMAR ELIMINACIÓN DE CATEGORÍA -->
    <div class="modal fade adm-modal" id="modalEliminarCategoria" tabindex="-1" aria-labelledby="modalEliminarCategoriaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold fs-6" id="modalEliminarCategoriaLabel">
                        <i class="fa-solid fa-triangle-exclamation me-2" style="color:#d8685d;"></i>Eliminar categoría
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="small mb-2" style="color:#d6ddd0;">
                        ¿Seguro que deseas eliminar permanentemente la categoría <strong class="text-white" id="catDeleteName"></strong>?
                    </p>
                    <p class="mb-0" style="font-size:0.78rem; color:#9aa392;">
                        Esta acción no se puede deshacer. Si solo quieres ocultarla temporalmente, usa <strong>Desactivar</strong>.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="mod-btn-sm mod-btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="mod-btn-sm mod-btn-reject" id="btnConfirmarEliminarCat">
                        <i class="fa-solid fa-trash-can"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/partials/modal-agregar-usuario.php'; ?>
    <?php include __DIR__ . '/partials/modal-detalle-usuario.php'; ?>
    <?php include __DIR__ . '/partials/modal-editar-usuario.php'; ?>
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
    <script src="<?= $base_url ?>assets/js/admin-subastas.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-subastas.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-subasta-detalle.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-subasta-detalle.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-subasta-estado.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-subasta-estado.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-categorias.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-categorias.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-dashboard.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-dashboard.js') ?>"></script>
    <script src="<?= $base_url ?>assets/js/admin-dashboard-referencia.js?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/js/admin-dashboard-referencia.js') ?>"></script>
</body>

</html>