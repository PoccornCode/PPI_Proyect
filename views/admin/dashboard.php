<?php
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__, 2) . '/config/env.php';
}
$base_url = BASE_URL;

// Datos de referencia visual (mockup del panel Admin)
$usuarios_demo = [
    ['email' => 'ricardo.villalta@gmail.com', 'rol' => 'Moderador', 'rol_class' => 'moderador', 'estado' => 'Activo', 'activo' => true, 'registro' => '12/10/25'],
    ['email' => 'usuariotal1@gmail.com',      'rol' => 'Postor',    'rol_class' => 'postor',    'estado' => 'Activo', 'activo' => true, 'registro' => '12/10/25'],
    ['email' => 'jhonatan.perdomo@gmail.com', 'rol' => 'Postor',    'rol_class' => 'postor',    'estado' => 'Suspendido', 'activo' => false, 'registro' => '12/10/25'],
    ['email' => 'usuariotal2@gmail.com',      'rol' => 'Vendedor',  'rol_class' => 'vendedor',  'estado' => 'Activo', 'activo' => true, 'registro' => '12/10/25'],
    ['email' => 'nathaly.nose@gmail.com',     'rol' => 'Postor',    'rol_class' => 'postor',    'estado' => 'Activo', 'activo' => true, 'registro' => '12/10/25'],
];

$subastas_demo = [
    [
        'titulo'    => 'Charizard 1st Edition Base Set',
        'categoria' => 'Coleccionables',
        'precio'    => '$8,750',
        'tiempo'    => '01:12:45',
        'estado'    => 'Activa',
        'img'       => $base_url . 'assets/img/cat-coleccionables.jpg',
    ],
    [
        'titulo'    => 'The Legend of Zelda: Ocarina of Time Gold Cartridge',
        'categoria' => 'Videojuegos',
        'precio'    => '$500',
        'tiempo'    => '02:30:20',
        'estado'    => 'Activa',
        'img'       => $base_url . 'assets/img/cat-arte.jpg',
    ],
    [
        'titulo'    => 'Cámara digital Sony ZV-E10 II ZV-E10M2K con Lente intercamb...',
        'categoria' => 'Electrónica',
        'precio'    => '$950',
        'tiempo'    => '03:15:10',
        'estado'    => 'Activa',
        'img'       => $base_url . 'assets/img/cat-tecnologia.jpg',
    ],
    [
        'titulo'    => 'Clairo - Immunity (Digipak, CD)',
        'categoria' => 'Música',
        'precio'    => '$500',
        'tiempo'    => 'Dos días',
        'estado'    => 'Activa',
        'img'       => $base_url . 'assets/img/cat-instrumentos.jpg',
    ],
    [
        'titulo'    => 'Pretty Guardian Sailor Moon - Eternal Edition 01',
        'categoria' => 'Libros y cómics',
        'precio'    => '$20.83',
        'tiempo'    => 'Un día',
        'estado'    => 'Activa',
        'img'       => $base_url . 'assets/img/cat-libros.jpg',
    ],
];

$categorias_demo = [
    ['nombre' => 'Coleccionables',  'subastas' => 804, 'icon' => 'fa-crown'],
    ['nombre' => 'Videojuegos',     'subastas' => 616, 'icon' => 'fa-gamepad'],
    ['nombre' => 'Electrónica',     'subastas' => 468, 'icon' => 'fa-desktop'],
    ['nombre' => 'Moda',            'subastas' => 277, 'icon' => 'fa-shirt'],
    ['nombre' => 'Música',          'subastas' => 143, 'icon' => 'fa-headphones'],
    ['nombre' => 'Libros y cómics', 'subastas' => 192, 'icon' => 'fa-book-open'],
    ['nombre' => 'Arte',            'subastas' => 96,  'icon' => 'fa-masks-theater'],
    ['nombre' => 'Entretenimiento', 'subastas' => 223, 'icon' => 'fa-film'],
    ['nombre' => 'Deportes',        'subastas' => 258, 'icon' => 'fa-table-tennis-paddle-ball'],
    ['nombre' => 'Otro',            'subastas' => 296, 'icon' => 'fa-ellipsis'],
];

$transacciones_demo = [
    ['titulo' => 'Pago recibido de usuario', 'codigo' => '6D1605', 'monto' => '+$120.00', 'pos' => true,  'hace' => 'Hace 10 min'],
    ['titulo' => 'Retiro procesado a vendedor', 'codigo' => '6D1605', 'monto' => '-$200.00', 'pos' => false, 'hace' => 'Hace 30 min'],
    ['titulo' => 'Pago recibido de usuario', 'codigo' => '6D1605', 'monto' => '+$616.00', 'pos' => true,  'hace' => 'Hace 1 h'],
    ['titulo' => 'Reembolso de puja',        'codigo' => '6D1605', 'monto' => '-$85.00',  'pos' => false, 'hace' => 'Hace 2 h'],
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
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/admin-dashboard.css?v=1">
</head>
<body class="admin-body">

    <!-- TOPBAR -->
    <header class="adm-topbar">
        <div class="adm-topbar-brand">
            <button type="button" class="adm-mobile-toggle" id="admMobileToggle" aria-label="Abrir menú lateral">
                <i class="fa-solid fa-bars"></i>
            </button>
            <a href="<?= $base_url ?>index.php" class="adm-brand-link" title="Volver al sitio principal">
                <i class="fa-solid fa-gavel adm-brand-icon"></i>
                <span class="adm-brand-text">Gavel<span>.net</span></span>
            </a>
        </div>

        <div class="adm-topbar-search">
            <div class="adm-search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span class="adm-search-divider" aria-hidden="true"></span>
                <input type="search" id="admGlobalSearch" placeholder="Buscar usuarios, subastas..." aria-label="Buscar usuarios o subastas">
            </div>
        </div>

        <div class="adm-topbar-actions">
            <!-- Notificaciones -->
            <div class="dropdown">
                <button class="adm-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notificaciones">
                    <i class="fa-regular fa-bell"></i>
                    <span class="adm-notif-badge">1</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end adm-dropdown-menu">
                    <div class="px-2 py-1 d-flex justify-content-between align-items-center">
                        <strong class="text-white small">Notificaciones</strong>
                        <span class="badge rounded-pill" style="background:#2d3624; color:#a7cc3b;">1 nueva</span>
                    </div>
                    <hr class="dropdown-divider my-1">
                    <button type="button" class="dropdown-item" data-adm-tab="subastas">
                        <i class="fa-solid fa-gavel" style="color:#a7cc3b;"></i>
                        <div>
                            <div class="small text-white">Subasta pendiente de revisión</div>
                            <div style="font-size:0.74rem; color:#9aa392;">Hace 5 min</div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Perfil Admin -->
            <div class="dropdown">
                <button class="adm-profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="adm-avatar-circle">A</span>
                    <span class="fw-semibold d-none d-sm-inline">Admin</span>
                    <i class="fa-solid fa-chevron-down" style="font-size:0.75rem; color:#9aa392;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end adm-dropdown-menu">
                    <li class="px-2 py-1">
                        <div class="small fw-bold text-white">Administrador</div>
                        <div style="font-size:0.75rem; color:#9aa392;">Prototipo de referencia</div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="<?= $base_url ?>index.php">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Ir al sitio público
                        </a>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item" data-adm-tab="configuracion">
                            <i class="fa-solid fa-gear"></i> Configuración
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- SIDEBAR -->
    <div class="adm-sidebar-overlay" id="admSidebarOverlay"></div>
    <aside class="adm-sidebar" id="admSidebar" aria-label="Navegación de administración">
        <nav>
            <ul class="adm-nav-list">
                <li>
                    <button type="button" class="adm-nav-btn active" data-adm-tab="inicio">
                        <i class="fa-solid fa-house"></i>
                        <span>Inicio</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="adm-nav-btn" data-adm-tab="usuarios">
                        <i class="fa-solid fa-users"></i>
                        <span>Usuarios</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="adm-nav-btn" data-adm-tab="subastas">
                        <i class="fa-solid fa-gavel"></i>
                        <span>Subastas</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="adm-nav-btn" data-adm-tab="finanzas">
                        <i class="fa-solid fa-circle-dollar-to-slot"></i>
                        <span>Finanzas</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="adm-nav-btn" data-adm-tab="categorias">
                        <i class="fa-solid fa-table-cells-large"></i>
                        <span>Categorías</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="adm-nav-btn" data-adm-tab="reportes">
                        <i class="fa-solid fa-chart-simple"></i>
                        <span>Reportes</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="adm-nav-btn" data-adm-tab="configuracion">
                        <i class="fa-solid fa-gear"></i>
                        <span>Configuración</span>
                    </button>
                </li>
            </ul>
        </nav>

        <div class="adm-sidebar-greeting">
            <strong>¡Hola, Admin!</strong>
            <span>Todo en orden por ahora.</span>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="adm-main">

        <!-- ===============================================================
             SECCIÓN 1: INICIO (DASHBOARD)
             =============================================================== -->
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
                    <select aria-label="Rango de fechas del resumen">
                        <option value="30d" selected>Últimos 30 días</option>
                        <option value="7d">Últimos 7 días</option>
                        <option value="90d">Últimos 90 días</option>
                    </select>
                </label>
            </div>

            <!-- 4 KPI Cards -->
            <div class="adm-kpi-grid">
                <article class="adm-kpi-card">
                    <div class="adm-kpi-top">
                        <div class="adm-kpi-icon"><i class="fa-solid fa-users"></i></div>
                        <div>
                            <div class="adm-kpi-value">616,123</div>
                            <div class="adm-kpi-label">Usuarios registrados</div>
                            <div class="adm-kpi-trend"><i class="fa-solid fa-angles-up"></i> +61%</div>
                        </div>
                    </div>
                    <div class="adm-kpi-footer">
                        <span class="adm-kpi-time"><i class="fa-regular fa-clock"></i> Viernes 20 nov., 11:11</span>
                        <svg class="adm-sparkline" viewBox="0 0 68 36" aria-hidden="true">
                            <rect x="2"  y="24" width="6" height="12" fill="#4f5c3d" rx="1"/>
                            <rect x="11" y="21" width="6" height="15" fill="#5a6845" rx="1"/>
                            <rect x="20" y="23" width="6" height="13" fill="#5a6845" rx="1"/>
                            <rect x="29" y="18" width="6" height="18" fill="#65754d" rx="1"/>
                            <rect x="38" y="14" width="6" height="22" fill="#6f8054" rx="1"/>
                            <rect x="47" y="10" width="6" height="26" fill="#7a8d5c" rx="1"/>
                            <rect x="56" y="4"  width="6" height="32" fill="#8fa769" rx="1"/>
                        </svg>
                    </div>
                </article>

                <article class="adm-kpi-card">
                    <div class="adm-kpi-top">
                        <div class="adm-kpi-icon"><i class="fa-solid fa-gavel"></i></div>
                        <div>
                            <div class="adm-kpi-value">67</div>
                            <div class="adm-kpi-label">Subastas activas</div>
                            <div class="adm-kpi-trend"><i class="fa-solid fa-angles-up"></i> +7%</div>
                        </div>
                    </div>
                    <div class="adm-kpi-footer">
                        <span class="adm-kpi-time"><i class="fa-regular fa-clock"></i> Viernes 20 nov., 16:01</span>
                        <svg class="adm-sparkline" viewBox="0 0 68 36" aria-hidden="true">
                            <rect x="2"  y="26" width="6" height="10" fill="#4f5c3d" rx="1"/>
                            <rect x="11" y="22" width="6" height="14" fill="#5a6845" rx="1"/>
                            <rect x="20" y="20" width="6" height="16" fill="#5a6845" rx="1"/>
                            <rect x="29" y="17" width="6" height="19" fill="#65754d" rx="1"/>
                            <rect x="38" y="15" width="6" height="21" fill="#6f8054" rx="1"/>
                            <rect x="47" y="11" width="6" height="25" fill="#7a8d5c" rx="1"/>
                            <rect x="56" y="5"  width="6" height="31" fill="#8fa769" rx="1"/>
                        </svg>
                    </div>
                </article>

                <article class="adm-kpi-card">
                    <div class="adm-kpi-top">
                        <div class="adm-kpi-icon"><i class="fa-solid fa-stopwatch"></i></div>
                        <div>
                            <div class="adm-kpi-value">127</div>
                            <div class="adm-kpi-label">Subastas pendientes</div>
                            <div class="adm-kpi-trend"><i class="fa-solid fa-angles-up"></i> +5%</div>
                        </div>
                    </div>
                    <div class="adm-kpi-footer">
                        <span class="adm-kpi-time"><i class="fa-regular fa-clock"></i> Viernes 20 nov., 18:04</span>
                        <svg class="adm-sparkline" viewBox="0 0 68 36" aria-hidden="true">
                            <rect x="2"  y="27" width="6" height="9"  fill="#4f5c3d" rx="1"/>
                            <rect x="11" y="24" width="6" height="12" fill="#5a6845" rx="1"/>
                            <rect x="20" y="21" width="6" height="15" fill="#5a6845" rx="1"/>
                            <rect x="29" y="19" width="6" height="17" fill="#65754d" rx="1"/>
                            <rect x="38" y="14" width="6" height="22" fill="#6f8054" rx="1"/>
                            <rect x="47" y="9"  width="6" height="27" fill="#7a8d5c" rx="1"/>
                            <rect x="56" y="4"  width="6" height="32" fill="#8fa769" rx="1"/>
                        </svg>
                    </div>
                </article>

                <article class="adm-kpi-card">
                    <div class="adm-kpi-top">
                        <div class="adm-kpi-icon"><i class="fa-solid fa-dollar-sign"></i></div>
                        <div>
                            <div class="adm-kpi-value">$11,804</div>
                            <div class="adm-kpi-label">Ingresos del mes</div>
                            <div class="adm-kpi-trend"><i class="fa-solid fa-angles-up"></i> +67%</div>
                        </div>
                    </div>
                    <div class="adm-kpi-footer">
                        <span class="adm-kpi-time"><i class="fa-regular fa-clock"></i> Viernes 20 nov., 11:11</span>
                        <svg class="adm-sparkline" viewBox="0 0 68 36" aria-hidden="true">
                            <rect x="2"  y="25" width="6" height="11" fill="#4f5c3d" rx="1"/>
                            <rect x="11" y="23" width="6" height="13" fill="#5a6845" rx="1"/>
                            <rect x="20" y="19" width="6" height="17" fill="#5a6845" rx="1"/>
                            <rect x="29" y="16" width="6" height="20" fill="#65754d" rx="1"/>
                            <rect x="38" y="13" width="6" height="23" fill="#6f8054" rx="1"/>
                            <rect x="47" y="8"  width="6" height="28" fill="#7a8d5c" rx="1"/>
                            <rect x="56" y="3"  width="6" height="33" fill="#8fa769" rx="1"/>
                        </svg>
                    </div>
                </article>
            </div>

            <!-- Charts Row -->
            <div class="adm-charts-row">
                <div class="adm-panel">
                    <div class="adm-panel-header">
                        <h2 class="adm-panel-title">
                            <i class="fa-solid fa-chart-line"></i> Actividad de la plataforma
                        </h2>
                        <label class="adm-pill-select adm-pill-select--sm">
                            <i class="fa-regular fa-calendar"></i>
                            <select id="admActivityRange" aria-label="Periodo de actividad">
                                <option value="7d" selected>Últimos 7 días</option>
                                <option value="30d">Últimos 30 días</option>
                                <option value="90d">Últimos 90 días</option>
                            </select>
                        </label>
                    </div>
                    <div class="adm-panel-body">
                        <div class="adm-chart-wrap">
                            <svg id="admActivitySvg" class="adm-svg-chart" viewBox="0 0 560 220" role="img" aria-label="Gráfico de actividad semanal"></svg>
                            <div id="admActivityTooltip" class="adm-chart-tooltip"></div>
                        </div>
                    </div>
                </div>

                <div class="adm-panel">
                    <div class="adm-panel-header">
                        <h2 class="adm-panel-title">
                            <i class="fa-solid fa-chart-pie"></i> Distribución de subastas
                        </h2>
                    </div>
                    <div class="adm-panel-body">
                        <div class="adm-donut-layout">
                            <svg width="168" height="168" viewBox="0 0 180 180" role="img" aria-label="Distribución de subastas: 67% activas, 11% finalizadas, 16% canceladas">
                                <!-- Circumference of r=64 is ~402.12 -->
                                <circle cx="90" cy="90" r="64" fill="none" stroke="#252c1f" stroke-width="26" />
                                <!-- Activas ~69% -->
                                <circle cx="90" cy="90" r="64" fill="none" stroke="#75865d" stroke-width="26"
                                    stroke-dasharray="282 402.12" stroke-dashoffset="0"
                                    transform="rotate(-90 90 90)" />
                                <!-- Finalizadas ~13% -->
                                <circle cx="90" cy="90" r="64" fill="none" stroke="#4c91a3" stroke-width="26"
                                    stroke-dasharray="52 402.12" stroke-dashoffset="-282"
                                    transform="rotate(-90 90 90)" />
                                <!-- Canceladas ~17% -->
                                <circle cx="90" cy="90" r="64" fill="none" stroke="#c8c45e" stroke-width="26"
                                    stroke-dasharray="68.12 402.12" stroke-dashoffset="-334"
                                    transform="rotate(-90 90 90)" />
                            </svg>

                            <ul class="adm-donut-legend">
                                <li>
                                    <span class="adm-legend-label">
                                        <span class="adm-legend-dot" style="background:#75865d;"></span> Activas
                                    </span>
                                    <strong>67%</strong>
                                </li>
                                <li>
                                    <span class="adm-legend-label">
                                        <span class="adm-legend-dot" style="background:#4c91a3;"></span> Finalizadas
                                    </span>
                                    <strong>11%</strong>
                                </li>
                                <li>
                                    <span class="adm-legend-label">
                                        <span class="adm-legend-dot" style="background:#c8c45e;"></span> Canceladas
                                    </span>
                                    <strong>16%</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Usuarios recientes & Subastas destacadas -->
            <div class="adm-bottom-row">
                <div class="adm-panel">
                    <div class="adm-panel-header">
                        <h2 class="adm-panel-title">
                            <i class="fa-solid fa-users"></i> Usuarios recientes
                        </h2>
                        <button type="button" class="adm-panel-link" data-adm-tab="usuarios">
                            Ver todos <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                    <div class="adm-panel-body py-2">
                        <div class="adm-recent-list">
                            <div class="adm-recent-item">
                                <div class="adm-user-avatar"><i class="fa-solid fa-user"></i></div>
                                <div class="adm-recent-email">usuariotal1@gmail.com</div>
                                <div class="adm-recent-time">Hace 11 min</div>
                                <span class="adm-badge adm-badge--vendedor">Vendedor</span>
                            </div>
                            <div class="adm-recent-item">
                                <div class="adm-user-avatar"><i class="fa-solid fa-user"></i></div>
                                <div class="adm-recent-email">usuariotal2@gmail.com</div>
                                <div class="adm-recent-time">Hace 4 min</div>
                                <span class="adm-badge adm-badge--postor">Postor</span>
                            </div>
                            <div class="adm-recent-item">
                                <div class="adm-user-avatar"><i class="fa-solid fa-user"></i></div>
                                <div class="adm-recent-email">usuariotal3@gmail.com</div>
                                <div class="adm-recent-time">Hace 6 min</div>
                                <span class="adm-badge adm-badge--vendedor">Vendedor</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="adm-panel">
                    <div class="adm-panel-header">
                        <h2 class="adm-panel-title">
                            <i class="fa-solid fa-star"></i> Subastas destacadas
                        </h2>
                        <button type="button" class="adm-panel-link" data-adm-tab="subastas">
                            Ver todas <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                    <div class="adm-panel-body">
                        <div class="adm-featured-grid">
                            <article class="adm-featured-card">
                                <div class="adm-featured-thumb">
                                    <img src="<?= $base_url ?>assets/img/cat-coleccionables.jpg" alt="Pokémon Charizard Holo">
                                    <span class="adm-featured-timer"><i class="fa-regular fa-clock"></i> 02:15:30</span>
                                </div>
                                <div class="adm-featured-title">1999 Pokémon 1st Edition Base Set Charizard Holo</div>
                                <div class="adm-featured-price">$8,750.00</div>
                            </article>
                            <article class="adm-featured-card">
                                <div class="adm-featured-thumb">
                                    <img src="<?= $base_url ?>assets/img/cat-tecnologia.jpg" alt="Cámara Sony ZV-E10 II">
                                    <span class="adm-featured-timer"><i class="fa-regular fa-clock"></i> 05:45:20</span>
                                </div>
                                <div class="adm-featured-title">Cámara digital Sony ZV-E10 II ZV-E10M2K con Lente int...</div>
                                <div class="adm-featured-price">$950.00</div>
                            </article>
                            <article class="adm-featured-card">
                                <div class="adm-featured-thumb">
                                    <img src="<?= $base_url ?>assets/img/cat-arte.jpg" alt="Zelda Ocarina of Time Gold">
                                    <span class="adm-featured-timer"><i class="fa-regular fa-clock"></i> 03:10:40</span>
                                </div>
                                <div class="adm-featured-title">Zelda: Ocarina of Time Gold Cartridge, Nintendo 64 (N64)</div>
                                <div class="adm-featured-price">$500.00</div>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===============================================================
             SECCIÓN 2: GESTIÓN DE USUARIOS
             =============================================================== -->
        <section class="adm-section" id="sec-usuarios">
            <div class="adm-section-head">
                <div class="adm-title-group">
                    <span class="adm-title-accent" aria-hidden="true"></span>
                    <div>
                        <h1>Gestión de usuarios</h1>
                        <p>Administra los usuarios registrados en la plataforma.</p>
                    </div>
                </div>
            </div>
            <hr class="adm-section-divider">

            <div class="adm-toolbar">
                <div class="adm-toolbar-left">
                    <div class="adm-input-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span class="adm-search-divider" aria-hidden="true"></span>
                        <input type="search" id="admUserSearch" placeholder="Buscar usuario..." aria-label="Buscar usuario">
                    </div>
                    <select class="adm-select-filter" id="admUserRoleFilter" aria-label="Filtrar por rol">
                        <option value="">Todos los roles</option>
                        <option value="Moderador">Moderador</option>
                        <option value="Postor">Postor</option>
                        <option value="Vendedor">Vendedor</option>
                    </select>
                </div>
                <button type="button" class="adm-btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarUsuario">
                    <i class="fa-solid fa-plus"></i> Agregar usuario
                </button>
            </div>

            <div class="adm-table-card">
                <div class="adm-table-responsive">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th><i class="fa-solid fa-users"></i> Usuarios</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="admUsersTbody">
                            <?php foreach ($usuarios_demo as $u): ?>
                                <tr data-email="<?= htmlspecialchars($u['email']) ?>" data-role="<?= htmlspecialchars($u['rol']) ?>">
                                    <td>
                                        <div class="adm-cell-user">
                                            <div class="adm-user-avatar"><i class="fa-solid fa-user"></i></div>
                                            <span><?= htmlspecialchars($u['email']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="adm-badge adm-badge--<?= $u['rol_class'] ?>">
                                            <?= htmlspecialchars($u['rol']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="adm-status">
                                            <span class="adm-status-dot <?= $u['activo'] ? 'adm-status-dot--active' : 'adm-status-dot--suspended' ?>"></span>
                                            <?= htmlspecialchars($u['estado']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($u['registro']) ?></td>
                                    <td>
                                        <div class="adm-action-group">
                                            <button type="button" class="adm-action-btn adm-action-btn--edit" title="Editar usuario"
                                                data-adm-demo-action="Editar <?= htmlspecialchars($u['email']) ?> (referencia)">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button type="button" class="adm-action-btn adm-action-btn--delete" title="Eliminar usuario"
                                                data-adm-demo-action="Eliminar <?= htmlspecialchars($u['email']) ?> (referencia)">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="adm-table-footer">
                    <span class="adm-table-count" id="admUsersCount">Mostrando <?= count($usuarios_demo) ?> usuarios</span>
                    <nav class="adm-pagination" aria-label="Paginación de usuarios">
                        <button type="button" class="adm-page-btn active">1</button>
                        <button type="button" class="adm-page-btn" data-adm-demo-action="Página 2 (datos de referencia)">2</button>
                        <button type="button" class="adm-page-btn" data-adm-demo-action="Página 3 (datos de referencia)">3</button>
                        <button type="button" class="adm-page-btn" aria-label="Siguiente" data-adm-demo-action="Siguiente página (referencia)">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </nav>
                </div>
            </div>
        </section>

        <!-- ===============================================================
             SECCIÓN 3: GESTIÓN DE SUBASTAS
             =============================================================== -->
        <section class="adm-section" id="sec-subastas">
            <div class="adm-section-head">
                <div class="adm-title-group">
                    <span class="adm-title-accent" aria-hidden="true"></span>
                    <div>
                        <h1>Gestión de subastas</h1>
                        <p>Consulta y administra todas las subastas publicadas.</p>
                    </div>
                </div>
            </div>
            <hr class="adm-section-divider">

            <div class="adm-toolbar">
                <div class="adm-toolbar-left">
                    <div class="adm-input-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span class="adm-search-divider" aria-hidden="true"></span>
                        <input type="search" id="admAuctionSearch" placeholder="Buscar subasta o artículo..." aria-label="Buscar subasta">
                    </div>
                    <select class="adm-select-filter" id="admAuctionCatFilter" aria-label="Filtrar por categoría">
                        <option value="">Todas las categorías</option>
                        <option value="Coleccionables">Coleccionables</option>
                        <option value="Videojuegos">Videojuegos</option>
                        <option value="Electrónica">Electrónica</option>
                        <option value="Música">Música</option>
                        <option value="Libros y cómics">Libros y cómics</option>
                    </select>
                </div>
                <button type="button" class="adm-btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaSubasta">
                    <i class="fa-solid fa-plus"></i> Nueva subasta
                </button>
            </div>

            <div class="adm-table-card">
                <div class="adm-table-responsive">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th><i class="fa-solid fa-cube"></i> Artículo</th>
                                <th>Categoría</th>
                                <th>Precio actual</th>
                                <th>Tiempo restante</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="admAuctionsTbody">
                            <?php foreach ($subastas_demo as $s): ?>
                                <tr data-title="<?= htmlspecialchars($s['titulo']) ?>" data-category="<?= htmlspecialchars($s['categoria']) ?>">
                                    <td>
                                        <div class="adm-cell-item">
                                            <div class="adm-item-thumb">
                                                <img src="<?= htmlspecialchars($s['img']) ?>" alt="<?= htmlspecialchars($s['titulo']) ?>">
                                            </div>
                                            <span class="adm-item-name"><?= htmlspecialchars($s['titulo']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($s['categoria']) ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($s['precio']) ?></td>
                                    <td><?= htmlspecialchars($s['tiempo']) ?></td>
                                    <td><span class="adm-badge adm-badge--activa"><?= htmlspecialchars($s['estado']) ?></span></td>
                                    <td>
                                        <div class="adm-action-group">
                                            <button type="button" class="adm-action-btn adm-action-btn--edit" title="Editar subasta"
                                                data-adm-demo-action="Editar subasta: <?= htmlspecialchars($s['titulo']) ?> (referencia)">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button type="button" class="adm-action-btn adm-action-btn--delete" title="Eliminar subasta"
                                                data-adm-demo-action="Eliminar subasta: <?= htmlspecialchars($s['titulo']) ?> (referencia)">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="adm-table-footer">
                    <span class="adm-table-count" id="admAuctionsCount">Mostrando <?= count($subastas_demo) ?> subastas</span>
                    <nav class="adm-pagination" aria-label="Paginación de subastas">
                        <button type="button" class="adm-page-btn active">1</button>
                        <button type="button" class="adm-page-btn" data-adm-demo-action="Página 2 (datos de referencia)">2</button>
                        <button type="button" class="adm-page-btn" data-adm-demo-action="Página 3 (datos de referencia)">3</button>
                        <button type="button" class="adm-page-btn" aria-label="Siguiente" data-adm-demo-action="Siguiente página (referencia)">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </nav>
                </div>
            </div>
        </section>

        <!-- ===============================================================
             SECCIÓN 4: FINANZAS (RESUMEN FINANCIERO)
             =============================================================== -->
        <section class="adm-section" id="sec-finanzas">
            <div class="adm-section-head">
                <div class="adm-title-group">
                    <span class="adm-title-accent" aria-hidden="true"></span>
                    <div>
                        <h1>Resumen financiero</h1>
                        <p>Seguimiento de ingresos y transacciones.</p>
                    </div>
                </div>
            </div>
            <hr class="adm-section-divider">

            <div class="adm-kpi-grid">
                <article class="adm-kpi-card">
                    <div class="adm-kpi-top">
                        <div class="adm-kpi-icon"><i class="fa-regular fa-credit-card"></i></div>
                        <div>
                            <div class="adm-kpi-value">$10,820</div>
                            <div class="adm-kpi-label">Pagos procesados</div>
                            <div class="adm-kpi-trend"><i class="fa-solid fa-angles-up"></i> +12%</div>
                        </div>
                    </div>
                    <div class="adm-kpi-footer">
                        <span class="adm-kpi-time"><i class="fa-regular fa-clock"></i> Viernes 20 nov., 18:04</span>
                    </div>
                </article>

                <article class="adm-kpi-card">
                    <div class="adm-kpi-top">
                        <div class="adm-kpi-icon"><i class="fa-regular fa-credit-card"></i></div>
                        <div>
                            <div class="adm-kpi-value">$420</div>
                            <div class="adm-kpi-label">Retiros pendientes</div>
                            <div class="adm-kpi-trend"><i class="fa-solid fa-angles-up"></i> +3%</div>
                        </div>
                    </div>
                    <div class="adm-kpi-footer">
                        <span class="adm-kpi-time"><i class="fa-regular fa-clock"></i> Viernes 20 nov., 18:04</span>
                    </div>
                </article>

                <article class="adm-kpi-card">
                    <div class="adm-kpi-top">
                        <div class="adm-kpi-icon"><i class="fa-solid fa-dollar-sign"></i></div>
                        <div>
                            <div class="adm-kpi-value">$48,320</div>
                            <div class="adm-kpi-label">Ingresos totales</div>
                            <div class="adm-kpi-trend"><i class="fa-solid fa-angles-up"></i> +5%</div>
                        </div>
                    </div>
                    <div class="adm-kpi-footer">
                        <span class="adm-kpi-time"><i class="fa-regular fa-clock"></i> Viernes 20 nov., 18:04</span>
                    </div>
                </article>

                <article class="adm-kpi-card">
                    <div class="adm-kpi-top">
                        <div class="adm-kpi-icon"><i class="fa-solid fa-percent"></i></div>
                        <div>
                            <div class="adm-kpi-value">$1,240</div>
                            <div class="adm-kpi-label">Comisiones</div>
                            <div class="adm-kpi-trend"><i class="fa-solid fa-angles-up"></i> +5%</div>
                        </div>
                    </div>
                    <div class="adm-kpi-footer">
                        <span class="adm-kpi-time"><i class="fa-regular fa-clock"></i> Viernes 20 nov., 18:04</span>
                    </div>
                </article>
            </div>

            <div class="adm-finance-row">
                <div class="adm-panel">
                    <div class="adm-panel-header">
                        <h2 class="adm-panel-title">Ingresos por mes</h2>
                    </div>
                    <div class="adm-panel-body">
                        <svg class="adm-svg-chart" viewBox="0 0 360 250" role="img" aria-label="Gráfico de barras de ingresos por mes">
                            <!-- Gridlines 0..500 -->
                            <line x1="42" y1="20"  x2="340" y2="20"  stroke="#272e21" stroke-width="1" />
                            <line x1="42" y1="60"  x2="340" y2="60"  stroke="#272e21" stroke-width="1" />
                            <line x1="42" y1="100" x2="340" y2="100" stroke="#272e21" stroke-width="1" />
                            <line x1="42" y1="140" x2="340" y2="140" stroke="#272e21" stroke-width="1" />
                            <line x1="42" y1="180" x2="340" y2="180" stroke="#272e21" stroke-width="1" />
                            <line x1="42" y1="220" x2="340" y2="220" stroke="#36402e" stroke-width="1" />

                            <text x="34" y="24"  text-anchor="end" fill="#9aa392" font-size="11">500</text>
                            <text x="34" y="64"  text-anchor="end" fill="#9aa392" font-size="11">400</text>
                            <text x="34" y="104" text-anchor="end" fill="#9aa392" font-size="11">300</text>
                            <text x="34" y="144" text-anchor="end" fill="#9aa392" font-size="11">200</text>
                            <text x="34" y="184" text-anchor="end" fill="#9aa392" font-size="11">100</text>
                            <text x="34" y="224" text-anchor="end" fill="#9aa392" font-size="11">0</text>

                            <!-- Bars -->
                            <rect x="64"  y="132" width="48" height="88"  rx="8" fill="#a6d8b3" />
                            <rect x="134" y="100" width="48" height="120" rx="8" fill="#4c91a3" />
                            <rect x="204" y="40"  width="48" height="180" rx="8" fill="#c8c45e" />
                            <rect x="274" y="120" width="48" height="100" rx="8" fill="#728259" />

                            <text x="88"  y="240" text-anchor="middle" fill="#c7cec0" font-size="11.5">Sept.</text>
                            <text x="158" y="240" text-anchor="middle" fill="#c7cec0" font-size="11.5">Oct.</text>
                            <text x="228" y="240" text-anchor="middle" fill="#c7cec0" font-size="11.5">Nov.</text>
                            <text x="298" y="240" text-anchor="middle" fill="#c7cec0" font-size="11.5">Dic.</text>
                        </svg>
                    </div>
                </div>

                <div class="adm-panel">
                    <div class="adm-panel-header">
                        <h2 class="adm-panel-title">Transacciones recientes</h2>
                        <button type="button" class="adm-panel-link" data-adm-demo-action="Listado completo de transacciones (referencia)">
                            Ver todas <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                    <div class="adm-panel-body py-2">
                        <div class="adm-tx-list">
                            <?php foreach ($transacciones_demo as $tx): ?>
                                <div class="adm-tx-item">
                                    <div class="adm-tx-left">
                                        <div class="adm-user-avatar"><i class="fa-solid fa-user"></i></div>
                                        <div>
                                            <div class="adm-tx-title"><?= htmlspecialchars($tx['titulo']) ?></div>
                                            <div class="adm-tx-code"><?= htmlspecialchars($tx['codigo']) ?></div>
                                        </div>
                                    </div>
                                    <div class="adm-tx-right">
                                        <div class="adm-tx-amount <?= $tx['pos'] ? 'adm-tx-amount--pos' : 'adm-tx-amount--neg' ?>">
                                            <?= htmlspecialchars($tx['monto']) ?>
                                        </div>
                                        <div class="adm-tx-time"><?= htmlspecialchars($tx['hace']) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===============================================================
             SECCIÓN 5: CATEGORÍAS
             =============================================================== -->
        <section class="adm-section" id="sec-categorias">
            <div class="adm-section-head">
                <div class="adm-title-group">
                    <span class="adm-title-accent" aria-hidden="true"></span>
                    <div>
                        <h1>Categorías</h1>
                        <p>Explora y administra las categorías disponibles en Gavel<span class="brand-inline">.net</span>.</p>
                    </div>
                </div>
            </div>
            <hr class="adm-section-divider">

            <div class="adm-cat-grid">
                <?php foreach ($categorias_demo as $cat): ?>
                    <article class="adm-cat-card" data-category="<?= htmlspecialchars($cat['nombre']) ?>">
                        <div class="adm-cat-ring">
                            <div class="adm-cat-circle">
                                <i class="fa-solid <?= htmlspecialchars($cat['icon']) ?>"></i>
                            </div>
                        </div>
                        <div class="adm-cat-name"><?= htmlspecialchars($cat['nombre']) ?></div>
                        <div class="adm-cat-count"><?= (int)$cat['subastas'] ?> subastas</div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ===============================================================
             SECCIÓN 6: REPORTES Y ESTADÍSTICAS (EN DEFINICIÓN)
             =============================================================== -->
        <section class="adm-section" id="sec-reportes">
            <div class="adm-section-head">
                <div class="adm-title-group">
                    <span class="adm-title-accent" aria-hidden="true"></span>
                    <div>
                        <h1>Reportes y estadísticas</h1>
                        <p>Analiza el rendimiento de la plataforma.</p>
                    </div>
                </div>
            </div>
            <hr class="adm-section-divider">

            <div class="adm-placeholder-banner">
                <i class="fa-solid fa-chart-column"></i>
                <div>
                    <strong class="d-block text-white mb-1">Sección en definición (referencia visual)</strong>
                    <span style="color:#9aa392;">
                        Aquí se integrarán únicamente las métricas y reportes que decidan conservar para el proyecto (por ejemplo: subastas cerradas por mes, actividad de usuarios y exportación básica).
                    </span>
                </div>
            </div>

            <div class="adm-placeholder-grid">
                <div class="adm-panel">
                    <div class="adm-panel-body">
                        <h3 class="h6 text-white fw-bold mb-1"><i class="fa-solid fa-file-csv me-2" style="color:#a7cc3b;"></i>Reporte de subastas</h3>
                        <p class="small mb-3" style="color:#9aa392;">Resumen de lotes publicados, activos y finalizados.</p>
                        <button type="button" class="adm-btn-primary" style="height:36px; font-size:0.84rem;" data-adm-demo-action="Descarga de reporte de subastas (referencia)">
                            Previsualizar
                        </button>
                    </div>
                </div>
                <div class="adm-panel">
                    <div class="adm-panel-body">
                        <h3 class="h6 text-white fw-bold mb-1"><i class="fa-solid fa-user-check me-2" style="color:#a7cc3b;"></i>Crecimiento de usuarios</h3>
                        <p class="small mb-3" style="color:#9aa392;">Altas por rol y cuentas verificadas en el periodo.</p>
                        <button type="button" class="adm-btn-primary" style="height:36px; font-size:0.84rem;" data-adm-demo-action="Previsualización de crecimiento (referencia)">
                            Previsualizar
                        </button>
                    </div>
                </div>
                <div class="adm-panel">
                    <div class="adm-panel-body">
                        <h3 class="h6 text-white fw-bold mb-1"><i class="fa-solid fa-shield-halved me-2" style="color:#a7cc3b;"></i>Incidencias y moderación</h3>
                        <p class="small mb-3" style="color:#9aa392;">Espacio reservado para enlazar con la futura vista de Moderador.</p>
                        <button type="button" class="adm-btn-primary" style="height:36px; font-size:0.84rem;" data-adm-demo-action="Módulo enlazado con vista de Moderador (pendiente)">
                            Previsualizar
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===============================================================
             SECCIÓN 7: CONFIGURACIÓN DEL SISTEMA (EN DEFINICIÓN)
             =============================================================== -->
        <section class="adm-section" id="sec-configuracion">
            <div class="adm-section-head">
                <div class="adm-title-group">
                    <span class="adm-title-accent" aria-hidden="true"></span>
                    <div>
                        <h1>Configuración del sistema</h1>
                        <p>Personaliza el funcionamiento de Gavel<span class="brand-inline">.net</span>.</p>
                    </div>
                </div>
            </div>
            <hr class="adm-section-divider">

            <div class="adm-panel" style="max-width: 780px;">
                <div class="adm-panel-header">
                    <h2 class="adm-panel-title"><i class="fa-solid fa-sliders"></i> Parámetros generales (referencia)</h2>
                </div>
                <div>
                    <div class="adm-setting-row">
                        <div>
                            <div class="fw-semibold text-white">Revisión previa de subastas</div>
                            <div class="small" style="color:#9aa392;">Requerir aprobación de un moderador antes de publicar un lote.</div>
                        </div>
                        <label class="adm-switch">
                            <input type="checkbox" checked>
                            <span class="adm-switch-slider"></span>
                        </label>
                    </div>
                    <div class="adm-setting-row">
                        <div>
                            <div class="fw-semibold text-white">Verificación obligatoria de correo</div>
                            <div class="small" style="color:#9aa392;">Solicitar código de verificación al registrar nuevas cuentas.</div>
                        </div>
                        <label class="adm-switch">
                            <input type="checkbox" checked>
                            <span class="adm-switch-slider"></span>
                        </label>
                    </div>
                    <div class="adm-setting-row">
                        <div>
                            <div class="fw-semibold text-white">Modo mantenimiento</div>
                            <div class="small" style="color:#9aa392;">Pausa temporalmente nuevas pujas y publicaciones.</div>
                        </div>
                        <label class="adm-switch">
                            <input type="checkbox">
                            <span class="adm-switch-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- MODAL DEMO: AGREGAR USUARIO -->
    <div class="modal fade adm-modal" id="modalAgregarUsuario" tabindex="-1" aria-labelledby="modalAgregarUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form data-adm-demo-form="Usuario de prueba registrado (solo simulación visual).">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="modalAgregarUsuarioLabel">
                            <i class="fa-solid fa-user-plus me-2" style="color:#a7cc3b;"></i>Agregar usuario
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body d-flex flex-column gap-3">
                        <div>
                            <label class="form-label small text-secondary">Correo electrónico</label>
                            <input type="email" class="form-control" placeholder="nuevo.usuario@gmail.com" required>
                        </div>
                        <div>
                            <label class="form-label small text-secondary">Rol</label>
                            <select class="form-select">
                                <option value="Postor">Postor</option>
                                <option value="Vendedor">Vendedor</option>
                                <option value="Moderador">Moderador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="adm-btn-primary" style="height:36px; font-size:0.86rem;">Guardar (demo)</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

    <!-- TOAST FEEDBACK -->
    <div class="adm-toast" id="admToast" role="status" aria-live="polite">
        <i class="fa-solid fa-circle-info"></i>
        <span id="admToastMsg"></span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $base_url ?>assets/js/admin-dashboard.js?v=1"></script>
</body>
</html>
