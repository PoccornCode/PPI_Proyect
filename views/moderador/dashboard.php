<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__, 2) . '/config/env.php';
}
require_once dirname(__DIR__, 2) . '/controllers/moderador_controller.php';

$base_url = BASE_URL;
$kpisModerador = obtenerKpisModerador();
$categoriasModerador = obtenerCategoriasModerador();
$totalPendientesKpi = (int)($kpisModerador['pendientes'] ?? 0);

$mod_nombre_completo = trim(($_SESSION['nombre'] ?? 'Mod') . ' ' . ($_SESSION['apellido'] ?? 'Gavel'));
$mod_username = $_SESSION['username'] ?? 'moderador';
$mod_inicial = strtoupper(mb_substr($mod_nombre_completo, 0, 1, 'UTF-8'));

// Datos de referencia visual (mockup del panel de Moderador)
$pendientes_demo = [
    [
        'id'        => 'SUB-204',
        'titulo'    => 'Cámara Réflex Canon EOS 80D',
        'condicion' => 'Como nuevo',
        'fotos'     => 4,
        'categoria' => 'Tecnología',
        'vendedor'  => '@usuario_vendedor1',
        'precio'    => '$350.00',
        'estado'    => 'Pendiente',
        'img'       => $base_url . 'assets/img/cat-tecnologia.jpg',
        'imagenes'  => [
            $base_url . 'assets/img/cat-tecnologia.jpg',
            $base_url . 'assets/img/features-technology.jpg',
            $base_url . 'assets/img/news-1.jpg',
            $base_url . 'assets/img/news-2.jpg',
        ],
        'detalle'   => 'Incluye lente 18-55mm, batería original, cargador y correa. Sin rayones en el sensor.',
    ],
    [
        'id'        => 'SUB-205',
        'titulo'    => 'Reloj Vintage Automático 1970',
        'condicion' => 'Buen estado',
        'fotos'     => 2,
        'categoria' => 'Joyería',
        'vendedor'  => '@coleccionista_sv',
        'precio'    => '$120.00',
        'estado'    => 'Pendiente',
        'img'       => $base_url . 'assets/img/cat-joyeria.jpg',
        'imagenes'  => [
            $base_url . 'assets/img/cat-joyeria.jpg',
            $base_url . 'assets/img/features-fashion.jpg',
        ],
        'detalle'   => 'Movimiento mecánico automático funcional, caja de acero de 36mm con desgaste leve propio de la época.',
    ],
    [
        'id'        => 'SUB-206',
        'titulo'    => 'Guitarra Acústica de Estudio',
        'condicion' => 'Excelente',
        'fotos'     => 5,
        'categoria' => 'Instrumentos Musicales',
        'vendedor'  => '@music_store_sv',
        'precio'    => '$165.00',
        'estado'    => 'Pendiente',
        'img'       => $base_url . 'assets/img/cat-instrumentos.jpg',
        'imagenes'  => [
            $base_url . 'assets/img/cat-instrumentos.jpg',
            $base_url . 'assets/img/lifestyle-1.jpg',
            $base_url . 'assets/img/lifestyle-2.jpg',
            $base_url . 'assets/img/news-3.jpg',
            $base_url . 'assets/img/news-4.jpg',
        ],
        'detalle'   => 'Tapa de abeto macizo con estuche rígido incluido. Cuerdas recién cambiadas.',
    ],
    [
        'id'        => 'SUB-207',
        'titulo'    => 'Colección de Monedas Antiguas (18 piezas)',
        'condicion' => 'Excelente',
        'fotos'     => 6,
        'categoria' => 'Coleccionables',
        'vendedor'  => '@numismatica_latam',
        'precio'    => '$140.00',
        'estado'    => 'Pendiente',
        'img'       => $base_url . 'assets/img/cat-coleccionables.jpg',
        'imagenes'  => [
            $base_url . 'assets/img/cat-coleccionables.jpg',
            $base_url . 'assets/img/cat-arte.jpg',
            $base_url . 'assets/img/cat-hogar.jpg',
            $base_url . 'assets/img/news-5.jpg',
            $base_url . 'assets/img/news-6.jpg',
            $base_url . 'assets/img/news-7.jpg',
        ],
        'detalle'   => 'Lote de 18 monedas históricas de plata y cobre en álbum protector certificado.',
    ],
];
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
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/admin-dashboard.css?v=1">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/moderador-dashboard.css?v=14">
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
                    <span class="fw-semibold d-none d-sm-inline"><?= htmlspecialchars(explode(' ', $mod_nombre_completo)[0]) ?></span>
                    <i class="fa-solid fa-chevron-down" style="font-size:0.75rem; color:#9aa392;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end adm-dropdown-menu menu-perfil-ppi">
                    <li class="menu-perfil-header">
                        <div class="menu-perfil-name"><?= htmlspecialchars($mod_nombre_completo) ?></div>
                        <div class="menu-perfil-user">@<?= htmlspecialchars($mod_username) ?> · Moderador</div>
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
                    <li>
                        <a class="dropdown-item" href="<?= $base_url ?>views/admin/dashboard.php">
                            <i class="fa-solid fa-crown"></i> Panel de Administración
                        </a>
                    </li>
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
            </ul>
        </nav>

        <div class="mod-sidebar-footer">
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

        <!-- Sección principal: cola de publicaciones pendientes -->
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

            <!-- KPI Cards (Subastas activas y pendientes - datos reales BD) -->
            <?php include __DIR__ . '/partials/kpi-subastas.php'; ?>

            <div class="adm-toolbar">
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

            <div class="adm-table-card">
                <div class="adm-table-responsive">
                    <table class="adm-table mod-table">
                        <thead>
                            <tr>
                                <th class="mod-col-eye" aria-label="Ver detalle"></th>
                                <th><i class="fa-solid fa-cube"></i> Artículo</th>
                                <th>Categoría</th>
                                <th>Vendedor</th>
                                <th>Precio base</th>
                                <th>Estado</th>
                                <th>Acción de moderación</th>
                            </tr>
                        </thead>
                        <tbody id="modApprovalTbody">
                            <?php foreach ($pendientes_demo as $item): ?>
                                <tr
                                    data-id="<?= htmlspecialchars($item['id']) ?>"
                                    data-title="<?= htmlspecialchars($item['titulo']) ?>"
                                    data-vendor="<?= htmlspecialchars($item['vendedor']) ?>"
                                    data-category="<?= htmlspecialchars($item['categoria']) ?>"
                                    data-status="Pendiente">
                                    <td class="mod-col-eye">
                                        <button
                                            type="button"
                                            class="adm-action-btn adm-action-btn--edit"
                                            title="Ver detalle de la publicación"
                                            data-mod-detail
                                            data-detail-id="<?= htmlspecialchars($item['id']) ?>"
                                            data-detail-title="<?= htmlspecialchars($item['titulo']) ?>"
                                            data-detail-meta="<?= htmlspecialchars($item['categoria'] . ' · ' . $item['vendedor'] . ' · ' . $item['precio']) ?>"
                                            data-detail-body="<?= htmlspecialchars($item['detalle']) ?>"
                                            data-detail-specs="<?= htmlspecialchars(json_encode([
                                                                    ['label' => 'Categoría', 'value' => $item['categoria']],
                                                                    ['label' => 'Vendedor', 'value' => $item['vendedor']],
                                                                    ['label' => 'Precio base', 'value' => $item['precio']],
                                                                    ['label' => 'Condición', 'value' => $item['condicion'] . ' (' . (int)$item['fotos'] . ' fotos)'],
                                                                ], JSON_UNESCAPED_UNICODE)) ?>"
                                            data-detail-images="<?= htmlspecialchars(json_encode($item['imagenes'], JSON_UNESCAPED_SLASHES)) ?>">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <div class="adm-cell-item">
                                            <div class="adm-item-thumb">
                                                <img src="<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['titulo']) ?>">
                                            </div>
                                            <div>
                                                <div class="adm-item-name"><?= htmlspecialchars($item['titulo']) ?></div>
                                                <div class="mod-sub-meta">
                                                    Condición: <?= htmlspecialchars($item['condicion']) ?> · <?= (int)$item['fotos'] ?> fotos subidas
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($item['categoria']) ?></td>
                                    <td><?= htmlspecialchars($item['vendedor']) ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($item['precio']) ?></td>
                                    <td>
                                        <span class="adm-badge adm-badge--pendiente mod-row-status">Pendiente</span>
                                    </td>
                                    <td>
                                        <div class="adm-action-group">
                                            <button type="button" class="mod-btn-sm mod-btn-approve" data-mod-decision="aprobar" data-item="<?= htmlspecialchars($item['titulo']) ?>">
                                                <i class="fa-solid fa-check"></i> Aprobar
                                            </button>
                                            <button type="button" class="mod-btn-sm mod-btn-reject" data-mod-decision="rechazar" data-item="<?= htmlspecialchars($item['titulo']) ?>">
                                                <i class="fa-solid fa-xmark"></i> Rechazar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr id="modApprovalEmptyRow" class="d-none">
                                <td colspan="7" class="text-center py-4" style="color: #9aa392;">
                                    <i class="fa-solid fa-magnifying-glass mb-2 d-block" style="font-size: 1.25rem; opacity: 0.7;"></i>
                                    No se encontraron subastas que coincidan con tu búsqueda.
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-2" id="modClearFiltersBtn" style="color: var(--adm-accent); text-decoration: none;">
                                        Limpiar filtros
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="adm-table-footer">
                    <span class="adm-table-count" id="modApprovalCount">Mostrando <?= count($pendientes_demo) ?> publicaciones</span>
                    <nav class="adm-pagination" aria-label="Paginación de publicaciones pendientes">
                        <button type="button" class="adm-page-btn active">1</button>
                    </nav>
                </div>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/partials/modal-detalle.php'; ?>
    <?php include __DIR__ . '/partials/modal-logout.php'; ?>

    <script>
        window.MOD_BASE_URL = <?= json_encode($base_url, JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $base_url ?>assets/js/toast-ppi.js?v=2"></script>
    <script src="<?= $base_url ?>assets/js/moderador-kpis.js?v=2"></script>
    <script src="<?= $base_url ?>assets/js/moderador-filtros.js?v=2"></script>
    <script src="<?= $base_url ?>assets/js/moderador-dashboard.js?v=4"></script>
</body>

</html>