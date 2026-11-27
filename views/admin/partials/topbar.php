<?php
$baseUrlTopbar = (string)($base_url ?? '');
$totalAlertasAdmin = (int)($alertasTopbar['total'] ?? 0);
$subastasPorValidar = (int)($alertasTopbar['subastas_pendientes'] ?? 0);
$mensajesPendientes = (int)($alertasTopbar['contactos_pendientes'] ?? 0);
$inicialTopbar = (string)($adm_inicial ?? 'A');
$primerNombreTopbar = (string)($adm_primer_nombre ?? 'Admin');
$nombreCompletoTopbar = (string)($adm_nombre_completo ?? 'Administrador');
$usernameTopbar = (string)($adm_username ?? '');
$rolNombreTopbar = (string)($adm_rol_nombre ?? '');
?>
<header class="adm-topbar">
    <div class="adm-topbar-brand">
        <button type="button" class="adm-mobile-toggle" id="admMobileToggle" aria-label="Abrir menú lateral">
            <i class="fa-solid fa-bars"></i>
        </button>
        <a href="<?= $baseUrlTopbar ?>index.php" class="adm-brand-link d-flex align-items-center gap-2 text-decoration-none" title="Volver al sitio principal" style="font-family: 'Raleway', sans-serif;">
            <i class="fa-solid fa-gavel" style="font-size: 1.45rem; line-height: 1; color: #62803f;" aria-hidden="true"></i>
            <span class="d-flex flex-column" style="line-height: 1;">
                <span class="d-flex align-items-baseline gap-1">
                    <span class="fw-bold" style="font-size: 1.55rem; line-height: 1; color: #c9c9c9; font-family: 'Raleway', sans-serif;">Gavel</span>
                    <span class="fw-bold" style="font-size: 1.25rem; line-height: 1; color: #62803f; font-family: 'Raleway', sans-serif;">.net</span>
                </span>
                <span style="font-family: 'Open Sans', sans-serif; font-size: 0.62rem; font-weight: 600; letter-spacing: 0.14em; text-transform: uppercase; color: #7c8575; margin-top: 3px;">Administración</span>
            </span>
        </a>
    </div>

    <div class="adm-topbar-actions">
        <div class="dropdown">
            <button class="adm-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notificaciones">
                <i class="fa-regular fa-bell"></i>
                <span class="adm-notif-badge" id="admNotifCount"><?= $totalAlertasAdmin ?></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end adm-dropdown-menu" style="min-width: 270px;">
                <div class="px-2 py-1 d-flex justify-content-between align-items-center gap-2">
                    <strong class="text-white small">Alertas de Administración</strong>
                    <span class="badge rounded-pill" id="admNotifBadgePill" style="background:rgba(226, 155, 142, 0.14); color:#e8a599;">
                        <?= $totalAlertasAdmin === 1 ? '1 pendiente' : $totalAlertasAdmin . ' pendientes' ?>
                    </span>
                </div>
                <hr class="dropdown-divider my-1">
                <a class="dropdown-item" href="<?= $baseUrlTopbar ?>views/moderador/dashboard.php">
                    <i class="fa-solid fa-clock" style="color:#e29b8e;"></i>
                    <div>
                        <div class="small text-white" id="admNotifSubastasText">
                            <?= $subastasPorValidar === 1 ? '1 subasta por validar' : $subastasPorValidar . ' subastas por validar' ?>
                        </div>
                        <div style="font-size:0.74rem; color:#9aa392;">Cola de aprobación</div>
                    </div>
                </a>
                <?php /* Oculto temporalmente: mensajes de contacto
                <button type="button" class="dropdown-item" data-adm-tab="reportes" data-adm-rep-subtab="moderacion">
                    <i class="fa-regular fa-envelope" style="color:#8ea958;"></i>
                    <div>
                        <div class="small text-white" id="admNotifContactosText">
                            <?= $mensajesPendientes === 1 ? '1 mensaje de contacto' : $mensajesPendientes . ' mensajes de contacto' ?>
                        </div>
                        <div style="font-size:0.74rem; color:#9aa392;">Pendientes de respuesta</div>
                    </div>
                </button>
                */ ?>
            </div>
        </div>

        <div class="dropdown">
            <button class="adm-profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menú de usuario">
                <span class="adm-avatar-circle"><?= htmlspecialchars($inicialTopbar) ?></span>
                <span class="fw-semibold d-none d-sm-inline"><?= htmlspecialchars($primerNombreTopbar) ?></span>
                <i class="fa-solid fa-chevron-down" style="font-size:0.75rem; color:#9aa392;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end adm-dropdown-menu menu-perfil-ppi">
                <li class="menu-perfil-header">
                    <div class="menu-perfil-name"><?= htmlspecialchars($nombreCompletoTopbar) ?></div>
                    <div class="menu-perfil-user">@<?= htmlspecialchars($usernameTopbar) ?><?= $rolNombreTopbar !== '' ? ' · ' . htmlspecialchars($rolNombreTopbar) : '' ?></div>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= $baseUrlTopbar ?>views/perfil.php">
                        <i class="fa-solid fa-user"></i> Mi Perfil
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= $baseUrlTopbar ?>index.php">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Ir al sitio público
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= $baseUrlTopbar ?>views/moderador/dashboard.php">
                        <i class="fa-solid fa-shield-halved"></i> Panel de Moderación
                    </a>
                </li>
                <?php /* Oculto temporalmente: Configuración
                <li>
                    <button type="button" class="dropdown-item" data-adm-tab="configuracion">
                        <i class="fa-solid fa-gear"></i> Configuración
                    </button>
                </li>
                */ ?>
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
