<?php
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__, 2) . '/config/env.php';
}
$base_url = BASE_URL;

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
        'detalle'   => 'Incluye lente 18-55mm, batería original, cargador y correa. Sin rayones en el sensor.',
    ],
    [
        'id'        => 'SUB-205',
        'titulo'    => 'Reloj Vintage Automático 1970',
        'condicion' => 'Usado',
        'fotos'     => 2,
        'categoria' => 'Joyería',
        'vendedor'  => '@coleccionista_sv',
        'precio'    => '$120.00',
        'estado'    => 'Pendiente',
        'img'       => $base_url . 'assets/img/cat-joyeria.jpg',
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
        'detalle'   => 'Lote de 18 monedas históricas de plata y cobre en álbum protector certificado.',
    ],
];

$reportes_demo = [
    [
        'id'          => '#REP-104',
        'motivo'      => 'Posible Réplica / Falsificación',
        'motivo_tipo' => 'danger',
        'subasta'     => 'Tarjeta Gráfica RTX 4090 ($80 base)',
        'reporto'     => '@gamer_pro',
        'fecha'       => 'Hace 2h',
        'estado'      => 'Activo',
        'tipo_accion' => 'replica',
        'detalle'     => 'El usuario señala que el precio base ($80) es sospechosamente bajo y las fotos no muestran el número de serie.',
    ],
    [
        'id'          => '#REP-102',
        'motivo'      => 'Ganador no realiza pago',
        'motivo_tipo' => 'warning',
        'subasta'     => 'Lote Monedas Antiguas',
        'reporto'     => '@antiguedades_sv',
        'fecha'       => 'Ayer',
        'estado'      => 'Activo',
        'tipo_accion' => 'impago',
        'detalle'     => 'Han transcurrido 48 horas desde el cierre de la subasta sin confirmación de pago por parte del postor ganador.',
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
    <style>
        .mod-sub-meta {
            font-size: 0.78rem;
            color: var(--adm-text-muted);
            margin-top: 2px;
        }

        .mod-cat-pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
            background-color: #1f251a;
            color: #d6ddd0;
            border: 1px solid var(--adm-border);
        }

        .mod-badge-danger {
            background-color: rgba(217, 72, 72, 0.2);
            color: #ff8e8e;
            border: 1px solid rgba(217, 72, 72, 0.45);
        }

        .mod-badge-warning {
            background-color: rgba(200, 196, 94, 0.2);
            color: #ebe67a;
            border: 1px solid rgba(200, 196, 94, 0.45);
        }

        .mod-btn-sm {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 32px;
            padding: 0 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: background-color 0.15s ease, transform 0.12s ease, border-color 0.15s ease;
            white-space: nowrap;
        }

        .mod-btn-sm:active {
            transform: scale(0.98);
        }

        .mod-btn-approve {
            background-color: rgba(146, 200, 62, 0.18);
            color: #b9de4b;
            border-color: rgba(146, 200, 62, 0.45);
        }
        .mod-btn-approve:hover {
            background-color: rgba(146, 200, 62, 0.3);
        }

        .mod-btn-reject {
            background-color: rgba(217, 72, 72, 0.16);
            color: #ff8b8b;
            border-color: rgba(217, 72, 72, 0.4);
        }
        .mod-btn-reject:hover {
            background-color: rgba(217, 72, 72, 0.28);
        }

        .mod-btn-pause {
            background-color: rgba(200, 196, 94, 0.2);
            color: #f0ea7d;
            border-color: rgba(200, 196, 94, 0.45);
        }
        .mod-btn-pause:hover {
            background-color: rgba(200, 196, 94, 0.32);
        }

        .mod-btn-info {
            background-color: rgba(76, 145, 163, 0.2);
            color: #82d7ed;
            border-color: rgba(76, 145, 163, 0.45);
        }
        .mod-btn-info:hover {
            background-color: rgba(76, 145, 163, 0.32);
        }

        .mod-btn-outline {
            background-color: #1a1f16;
            color: #d8ded3;
            border-color: var(--adm-border);
        }
        .mod-btn-outline:hover {
            background-color: #252c1f;
            color: #ffffff;
        }

        .mod-summary-pills {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
    </style>
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
                <input type="search" id="modGlobalSearch" placeholder="Buscar subastas pendientes o reportes..." aria-label="Buscar subastas o reportes">
            </div>
        </div>

        <div class="adm-topbar-actions">
            <!-- Notificaciones -->
            <div class="dropdown">
                <button class="adm-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notificaciones">
                    <i class="fa-regular fa-bell"></i>
                    <span class="adm-notif-badge" id="modNotifCount">2</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end adm-dropdown-menu">
                    <div class="px-2 py-1 d-flex justify-content-between align-items-center">
                        <strong class="text-white small">Alertas de Moderación</strong>
                        <span class="badge rounded-pill" style="background:#2d3624; color:#a7cc3b;">2 activas</span>
                    </div>
                    <hr class="dropdown-divider my-1">
                    <button type="button" class="dropdown-item" data-mod-tab="aprobar">
                        <i class="fa-solid fa-clock" style="color:#c8c45e;"></i>
                        <div>
                            <div class="small text-white"><?= count($pendientes_demo) ?> subastas por validar</div>
                            <div style="font-size:0.74rem; color:#9aa392;">Cola de aprobación</div>
                        </div>
                    </button>
                    <button type="button" class="dropdown-item" data-mod-tab="reportes">
                        <i class="fa-solid fa-flag" style="color:#e29ca5;"></i>
                        <div>
                            <div class="small text-white"><?= count($reportes_demo) ?> reportes activos</div>
                            <div style="font-size:0.74rem; color:#9aa392;">Requieren resolución</div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Perfil Moderador -->
            <div class="dropdown">
                <button class="adm-profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="adm-avatar-circle">M</span>
                    <span class="fw-semibold d-none d-sm-inline">Moderador</span>
                    <i class="fa-solid fa-chevron-down" style="font-size:0.75rem; color:#9aa392;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end adm-dropdown-menu">
                    <li class="px-2 py-1">
                        <div class="small fw-bold text-white">Moderador</div>
                        <div style="font-size:0.75rem; color:#9aa392;">Centro operativo</div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
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
                </ul>
            </div>
        </div>
    </header>

    <!-- SIDEBAR (Solo 2 pestañas: Aprobar subastas y Reportes) -->
    <div class="adm-sidebar-overlay" id="admSidebarOverlay"></div>
    <aside class="adm-sidebar" id="admSidebar" aria-label="Navegación de moderación">
        <nav>
            <ul class="adm-nav-list">
                <li>
                    <button type="button" class="adm-nav-btn active" data-mod-tab="aprobar">
                        <i class="fa-solid fa-clipboard-check"></i>
                        <span>Aprobar subastas</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="adm-nav-btn" data-mod-tab="reportes">
                        <i class="fa-solid fa-flag"></i>
                        <span>Reportes</span>
                    </button>
                </li>
            </ul>
        </nav>

        <div class="adm-sidebar-greeting">
            <strong>¡Hola, Moderador!</strong>
            <span>Todo en orden por ahora.</span>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="adm-main">

        <!-- ===============================================================
             SECCIÓN 1: APROBAR SUBASTAS (COLA DE PUBLICACIONES PENDIENTES)
             =============================================================== -->
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
                        <i class="fa-regular fa-clock me-1"></i> <?= count($pendientes_demo) ?> Por validar
                    </span>
                </div>
            </div>
            <hr class="adm-section-divider">

            <div class="adm-toolbar">
                <div class="adm-toolbar-left">
                    <div class="adm-input-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span class="adm-search-divider" aria-hidden="true"></span>
                        <input type="search" id="modApprovalSearch" placeholder="Buscar artículo o vendedor..." aria-label="Buscar publicación pendiente">
                    </div>
                    <select class="adm-select-filter" id="modApprovalCatFilter" aria-label="Filtrar por categoría">
                        <option value="">Todas las categorías</option>
                        <option value="Tecnología">Tecnología</option>
                        <option value="Joyería">Joyería</option>
                        <option value="Instrumentos Musicales">Instrumentos Musicales</option>
                        <option value="Coleccionables">Coleccionables</option>
                    </select>
                </div>
            </div>

            <div class="adm-table-card">
                <div class="adm-table-responsive">
                    <table class="adm-table">
                        <thead>
                            <tr>
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
                                    data-status="Pendiente"
                                >
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
                                    <td><span class="mod-cat-pill"><?= htmlspecialchars($item['categoria']) ?></span></td>
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
                                            <button
                                                type="button"
                                                class="adm-action-btn adm-action-btn--edit"
                                                title="Ver detalle de la publicación"
                                                data-mod-detail
                                                data-detail-id="<?= htmlspecialchars($item['id']) ?>"
                                                data-detail-title="<?= htmlspecialchars($item['titulo']) ?>"
                                                data-detail-meta="<?= htmlspecialchars($item['categoria'] . ' · ' . $item['vendedor'] . ' · ' . $item['precio']) ?>"
                                                data-detail-body="<?= htmlspecialchars($item['detalle']) ?>"
                                            >
                                                <i class="fa-regular fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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

        <!-- ===============================================================
             SECCIÓN 2: REPORTES DE USUARIOS Y DISPUTAS
             =============================================================== -->
        <section class="adm-section" id="sec-reportes">
            <div class="adm-section-head">
                <div class="adm-title-group">
                    <span class="adm-title-accent" aria-hidden="true"></span>
                    <div>
                        <h1>Reportes</h1>
                        <p>Reportes de usuarios, posibles falsificaciones y disputas de pago.</p>
                    </div>
                </div>
                <div class="mod-summary-pills">
                    <span class="adm-badge mod-badge-danger" id="modReportsBadge">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= count($reportes_demo) ?> casos activos
                    </span>
                </div>
            </div>
            <hr class="adm-section-divider">

            <div class="adm-toolbar">
                <div class="adm-toolbar-left">
                    <div class="adm-input-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span class="adm-search-divider" aria-hidden="true"></span>
                        <input type="search" id="modReportSearch" placeholder="Buscar por ID, motivo o subasta..." aria-label="Buscar reporte">
                    </div>
                    <select class="adm-select-filter" id="modReportTypeFilter" aria-label="Filtrar por tipo de reporte">
                        <option value="">Todos los motivos</option>
                        <option value="danger">Posible falsificación</option>
                        <option value="warning">Incumplimiento de pago</option>
                    </select>
                </div>
            </div>

            <div class="adm-table-card">
                <div class="adm-table-responsive">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Motivo del reporte</th>
                                <th>Subasta afectada</th>
                                <th>Reportó</th>
                                <th>Fecha</th>
                                <th>Resolución de moderador</th>
                            </tr>
                        </thead>
                        <tbody id="modReportsTbody">
                            <?php foreach ($reportes_demo as $rep): ?>
                                <tr
                                    data-id="<?= htmlspecialchars($rep['id']) ?>"
                                    data-reason="<?= htmlspecialchars($rep['motivo']) ?>"
                                    data-auction="<?= htmlspecialchars($rep['subasta']) ?>"
                                    data-type="<?= htmlspecialchars($rep['motivo_tipo']) ?>"
                                >
                                    <td class="fw-bold" style="color:#a7cc3b;"><?= htmlspecialchars($rep['id']) ?></td>
                                    <td>
                                        <span class="adm-badge <?= $rep['motivo_tipo'] === 'danger' ? 'mod-badge-danger' : 'mod-badge-warning' ?>">
                                            <?= htmlspecialchars($rep['motivo']) ?>
                                        </span>
                                    </td>
                                    <td class="fw-semibold"><?= htmlspecialchars($rep['subasta']) ?></td>
                                    <td><?= htmlspecialchars($rep['reporto']) ?></td>
                                    <td style="color:#9aa392;"><?= htmlspecialchars($rep['fecha']) ?></td>
                                    <td>
                                        <div class="adm-action-group">
                                            <?php if ($rep['tipo_accion'] === 'replica'): ?>
                                                <button type="button" class="mod-btn-sm mod-btn-pause" data-mod-report-action="Subasta pausada preventivamente para <?= htmlspecialchars($rep['id']) ?>">
                                                    <i class="fa-solid fa-pause"></i> Pausar subasta
                                                </button>
                                                <button type="button" class="mod-btn-sm mod-btn-outline" data-mod-report-action="Mensaje enviado al vendedor del caso <?= htmlspecialchars($rep['id']) ?>">
                                                    <i class="fa-solid fa-envelope"></i> Contactar vendedor
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="mod-btn-sm mod-btn-info" data-mod-report-action="Adjudicación al 2do postor iniciada para <?= htmlspecialchars($rep['id']) ?>">
                                                    <i class="fa-solid fa-rotate-left"></i> Adjudicar a 2do postor
                                                </button>
                                            <?php endif; ?>
                                            <button
                                                type="button"
                                                class="adm-action-btn adm-action-btn--edit"
                                                title="Ver detalle del reporte"
                                                data-mod-detail
                                                data-detail-id="<?= htmlspecialchars($rep['id']) ?>"
                                                data-detail-title="<?= htmlspecialchars($rep['subasta']) ?>"
                                                data-detail-meta="<?= htmlspecialchars($rep['motivo'] . ' · Reportado por ' . $rep['reporto'] . ' (' . $rep['fecha'] . ')') ?>"
                                                data-detail-body="<?= htmlspecialchars($rep['detalle']) ?>"
                                            >
                                                <i class="fa-regular fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="adm-table-footer">
                    <span class="adm-table-count" id="modReportsCount">Mostrando <?= count($reportes_demo) ?> reportes</span>
                    <nav class="adm-pagination" aria-label="Paginación de reportes">
                        <button type="button" class="adm-page-btn active">1</button>
                    </nav>
                </div>
            </div>
        </section>
    </main>

    <!-- MODAL DETALLE (SUBASTA O REPORTE) -->
    <div class="modal fade adm-modal" id="modalModDetalle" tabindex="-1" aria-labelledby="modalModDetalleLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalModDetalleLabel">
                        <i class="fa-solid fa-shield-halved me-2" style="color:#a7cc3b;"></i><span id="modDetailCode">Detalle</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-2">
                    <h6 class="text-white fw-bold mb-0" id="modDetailTitle"></h6>
                    <div class="small" style="color:#a7cc3b;" id="modDetailMeta"></div>
                    <p class="small mb-0 mt-1" style="color:#d6ddd0;" id="modDetailBody"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="adm-btn-primary" style="height:36px; font-size:0.86rem;" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- TOAST FEEDBACK -->
    <div class="adm-toast" id="admToast" role="status" aria-live="polite">
        <i class="fa-solid fa-circle-info"></i>
        <span id="admToastMsg"></span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $base_url ?>assets/js/moderador-dashboard.js?v=1"></script>
</body>
</html>
