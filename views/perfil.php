<?php
require_once dirname(__DIR__) . '/config/env.php';
require_once dirname(__DIR__) . '/dao/PerfilDAO.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: " . BASE_URL . "views/auth/login.php");
    exit();
}

$titulo = "Mi Perfil";
$pagina = "perfil";

$id_usuario = (int)$_SESSION['id_usuario'];
$dao = new PerfilDAO();
$perfil = $dao->obtenerPerfil($id_usuario);

if (!$perfil) {
    header("Location: " . BASE_URL . "controllers/logout.php");
    exit();
}

if (!function_exists('e')) {
    // escapa texto antes de imprimirlo en html
    function e($texto)
    {
        return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
    }
}

function tiempoRestante($segundos)
{
    $segundos = (int)$segundos;
    if ($segundos <= 0) {
        return 'Finalizando';
    }
    $dias = intdiv($segundos, 86400);
    $horas = intdiv($segundos % 86400, 3600);
    $minutos = intdiv($segundos % 3600, 60);
    if ($dias > 0) {
        return sprintf('Termina en %d d %02d h', $dias, $horas);
    }
    return sprintf('Termina en %02d h %02d min', $horas, $minutos);
}

$rol = (int)$perfil['id_rol'];
$_SESSION['id_rol'] = $rol;

$esAdmin = $rol === PerfilDAO::ROL_ADMIN;
$esModerador = $rol === PerfilDAO::ROL_MODERADOR;
$esPersonal = $esAdmin || $esModerador; // personal 
$esUsuario = !$esPersonal;

$rolTextos = [
    PerfilDAO::ROL_ADMIN => 'Administrador',
    PerfilDAO::ROL_MODERADOR => 'Moderador',
    PerfilDAO::ROL_USUARIO => 'Postor / Subastador',
];
$rolTexto = $rolTextos[$rol] ?? ($perfil['rol_nombre'] !== '' ? $perfil['rol_nombre'] : 'Usuario');

$verificada = (int)$perfil['estado'] === 1;

// iniciales para el avatar
$iniciales = mb_strtoupper(mb_substr($perfil['nombre'] ?? '', 0, 1) . mb_substr($perfil['apellido'] ?? '', 0, 1));
if ($iniciales === '') {
    $iniciales = mb_strtoupper(mb_substr($perfil['username'] ?? 'U', 0, 1));
}


// dts segun rol
$resumenUsuario = $esUsuario ? $dao->obtenerResumenUsuario($id_usuario) : null;
$subastas = $esUsuario ? $dao->obtenerSubastasActivas($id_usuario, 3) : [];
$moderacion = $esPersonal ? $dao->obtenerResumenModeracion() : null;
$usuariosAdmin = $esAdmin ? $dao->obtenerResumenUsuariosAdmin() : null;
$resumenPujas = (new MisPujasDAO())->resumen((int)$_SESSION['id_usuario']);

$datos = [
    'Nombre completo' => trim(($perfil['nombre'] ?? '') . ' ' . ($perfil['apellido'] ?? '')),
    'Nombre de usuario' => '@' . $perfil['username'],
    'Correo electrónico' => $perfil['correo'] ?: 'Sin registrar',
    'Teléfono' => $perfil['telefono'] ?: 'Sin registrar',
    'Ubicación' => $perfil['ubicacion'] ?: 'Sin registrar',
];
if ($esPersonal) {
    $datos['Rol en la plataforma'] = $rolTexto;
}

// token para el formulario de cambio de contra
if (empty($_SESSION['csrf_perfil'])) {
    $_SESSION['csrf_perfil'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_perfil'];

$urlPanelModeracion = BASE_URL . 'views/moderador/dashboard.php';
$urlPanelAdmin = BASE_URL . 'views/admin/dashboard.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> | Gavel.net</title>
    <?php include dirname(__DIR__) . '/includes/meta_tags.php'; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/main.css?v=30" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/forms.css?v=41" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/perfil.css?v=6" rel="stylesheet">
</head>

<body class="bg-dark-ppi d-flex flex-column min-vh-100">

    <?php include "NavBar/nav.php"; ?>

    <!-- encabezado-->
    <section class="perfil-hero" aria-label="Tu perfil">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center gap-4">
                <div class="perfil-avatar" aria-hidden="true"><?= e($iniciales) ?></div>

                <div class="flex-grow-1 perfil-hero-texto">
                    <h1 class="perfil-nombre fs-1 fw-bold text-main-ppi mb-1"><?= e($datos['Nombre completo']) ?></h1>
                    <p class="text-second-ppi mb-2">
                        @<?= e($perfil['username']) ?>
                        <?php if (!empty($perfil['ubicacion'])): ?> &middot; <?= e($perfil['ubicacion']) ?><?php endif; ?>
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <?php if ($verificada): ?>
                            <span class="perfil-chip perfil-chip-ok"><i class="bi bi-check-lg"></i> Cuenta verificada</span>
                        <?php else: ?>
                            <span class="perfil-chip"><i class="bi bi-hourglass-split"></i> Pendiente de verificación</span>
                        <?php endif; ?>
                        <span class="perfil-chip <?= $esPersonal ? 'perfil-chip-rol' : '' ?>">
                            <?php if ($esAdmin): ?><i class="fas fa-crown"></i><?php elseif ($esModerador): ?><i class="fas fa-shield-alt"></i><?php endif; ?>
                            <?= e($rolTexto) ?>
                        </span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-perfil-salir" data-bs-toggle="modal" data-bs-target="#modalConfirmLogout">
                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                    </button>
                </div>
            </div>
        </div>
    </section>

    <main class="flex-grow-1 py-5">
        <div class="container d-flex flex-column gap-5">

            <?php if ($esUsuario): ?>
                <!-- actividad user -->
                <section aria-labelledby="titulo-actividad">
                    <h2 id="titulo-actividad" class="fs-4 fw-bold text-main-ppi mb-3">Mi actividad</h2>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <a href="<?= BASE_URL ?>views/subastas/mis-productos.php" class="perfil-acceso">
                                <span class="perfil-acceso-valor"><?= $resumenUsuario['productos'] ?></span>
                                <span class="perfil-acceso-titulo">Mis productos</span>
                                <span class="perfil-acceso-detalle">
                                    <?php if ($resumenUsuario['pendientes'] > 0): ?>
                                        <?= $resumenUsuario['pendientes'] ?> en revisión por moderación
                                    <?php else: ?>
                                        Registra y publica en subasta
                                    <?php endif; ?>
                                </span>
                            </a>
                        </div>
                        <div class="col-12 col-md-6">
                            <a href="<?= BASE_URL ?>views/subastas/mis-pujas.php" class="perfil-acceso">
                                <span class="perfil-acceso-valor"><?= $resumenPujas['subastas'] ?></span>
                                <span class="perfil-acceso-titulo">Mis pujas</span>
                                <span class="perfil-acceso-detalle">
                                    <?php if ($resumenPujas['ganando'] > 0): ?>
                                        Vas ganando en <?= $resumenPujas['ganando'] ?> <?= $resumenPujas['ganando'] === 1 ? 'subasta' : 'subastas' ?>
                                    <?php else: ?>
                                        Sigue tus ofertas y resultados
                                    <?php endif; ?>
                                </span>
                            </a>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($esAdmin): ?>
                <!-- admin: resumen + accesos rapidos al dashboard,,, se abren con el #hash -->
                <section aria-labelledby="titulo-admin">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h2 id="titulo-admin" class="fs-4 fw-bold text-main-ppi mb-0">Administración</h2>
                        <a href="<?= e($urlPanelAdmin) ?>" class="btn btn-outline-publish btn-sm px-3 py-2 fw-semibold">
                            <i class="fas fa-crown me-2"></i>Panel de administración
                        </a>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <a href="<?= e($urlPanelAdmin) ?>#usuarios" class="perfil-acceso">
                                <span class="perfil-acceso-valor"><?= $usuariosAdmin['total'] ?></span>
                                <span class="perfil-acceso-titulo d-flex justify-content-between align-items-center">Usuarios <i class="fas fa-arrow-right perfil-acceso-ir"></i></span>
                                <span class="perfil-acceso-detalle">Registrados en la plataforma</span>
                            </a>
                        </div>
                        <div class="col-12 col-md-4">
                            <a href="<?= e($urlPanelAdmin) ?>#usuarios" class="perfil-acceso">
                                <span class="perfil-acceso-valor"><?= $usuariosAdmin['moderadores'] ?></span>
                                <span class="perfil-acceso-titulo d-flex justify-content-between align-items-center">Moderadores <i class="fas fa-arrow-right perfil-acceso-ir"></i></span>
                                <span class="perfil-acceso-detalle">Con acceso al panel</span>
                            </a>
                        </div>
                        <div class="col-12 col-md-4">
                            <a href="<?= e($urlPanelAdmin) ?>#usuarios" class="perfil-acceso <?= $usuariosAdmin['sin_verificar'] > 0 ? 'perfil-acceso-alerta' : '' ?>">
                                <span class="perfil-acceso-valor"><?= $usuariosAdmin['sin_verificar'] ?></span>
                                <span class="perfil-acceso-titulo d-flex justify-content-between align-items-center">Sin verificar <i class="fas fa-arrow-right perfil-acceso-ir"></i></span>
                                <span class="perfil-acceso-detalle">Cuentas pendientes de código</span>
                            </a>
                        </div>
                    </div>

                    <p class="perfil-rapidos-titulo">Accesos rápidos</p>
                    <div class="perfil-rapidos">
                        <a href="<?= e($urlPanelAdmin) ?>#usuarios" class="perfil-rapido"><i class="fas fa-users-cog"></i>Gestionar usuarios</a>
                        <a href="<?= e($urlPanelModeracion) ?>" class="perfil-rapido">
                            <i class="fas fa-clipboard-check"></i>Revisar pendientes
                            <?php if ($moderacion['pendientes'] > 0): ?><span class="perfil-rapido-badge"><?= $moderacion['pendientes'] ?></span><?php endif; ?>
                        </a>
                        <a href="<?= e($urlPanelAdmin) ?>#categorias" class="perfil-rapido"><i class="fas fa-th-large"></i>Categorías</a>
                        <a href="<?= e($urlPanelAdmin) ?>#reportes" class="perfil-rapido"><i class="fas fa-chart-bar"></i>Reportes</a>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($esPersonal): ?>
                <!-- mod y admin-->
                <section aria-labelledby="titulo-moderacion">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h2 id="titulo-moderacion" class="fs-4 fw-bold text-main-ppi mb-0">Moderación de subastas</h2>
                        <a href="<?= e($urlPanelModeracion) ?>" class="btn btn-outline-publish btn-sm px-3 py-2 fw-semibold">
                            <i class="fas fa-shield-alt me-2"></i>Ir al panel de moderación
                        </a>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <a href="<?= e($urlPanelModeracion) ?>" class="perfil-acceso <?= $moderacion['pendientes'] > 0 ? 'perfil-acceso-alerta' : '' ?>"  
                            title="Abrir panel de moderación">
                                <span class="perfil-acceso-valor"><?= $moderacion['pendientes'] ?></span>
                                <span class="perfil-acceso-titulo d-flex justify-content-between align-items-center">Por revisar 
                                    <i class="fas fa-arrow-right perfil-acceso-ir"></i></span>
                                <span class="perfil-acceso-detalle">Solicitudes esperando decisión</span>
                            </a>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="perfil-acceso">
                                <span class="perfil-acceso-valor"><?= $moderacion['activas'] ?></span>
                                <span class="perfil-acceso-titulo">Subastas activas</span>
                                <span class="perfil-acceso-detalle">Publicadas y recibiendo pujas</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="perfil-acceso">
                                <span class="perfil-acceso-valor"><?= $moderacion['cerradas'] ?></span>
                                <span class="perfil-acceso-titulo">Subastas cerradas</span>
                                <span class="perfil-acceso-detalle">Finalizadas o sin ofertas</span>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- dts personales-->
            <div class="row g-4">
                <section class="col-lg-6" aria-labelledby="titulo-datos">
                    <div class="perfil-card h-100">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <h2 id="titulo-datos" class="fs-5 fw-bold text-main-ppi mb-0">Datos personales</h2>
                            <a href="#" class="link-ppi fw-bold d-inline p-0 small" data-bs-toggle="modal" data-bs-target="#modalEditarPerfil">Editar</a>
                        </div>
                        <dl class="mb-0">
                            <?php foreach ($datos as $campo => $valor): ?>
                                <div class="perfil-dato">
                                    <dt class="text-second-ppi fw-normal small"><?= e($campo) ?></dt>
                                    <dd class="text-main-ppi fw-bold mb-0"><?= e($valor) ?></dd>
                                </div>
                            <?php endforeach; ?>
                        </dl>
                    </div>
                </section>

                <section class="col-lg-6" aria-labelledby="titulo-seguridad">
                    <div class="perfil-card h-100">
                        <h2 id="titulo-seguridad" class="fs-5 fw-bold text-main-ppi mb-2">Seguridad</h2>
                        <ul class="list-unstyled mb-0">
                            <li class="perfil-dato">
                                <div>
                                    <div class="text-main-ppi fw-bold">Contraseña</div>
                                    <div class="text-second-ppi small">Cámbiala cada cierto tiempo</div>
                                </div>
                                <button type="button" class="btn btn-outline-publish btn-sm px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalCambiarClave">
                                    Cambiar
                                </button>
                            </li>
                            <li class="perfil-dato">
                                <div>
                                    <div class="text-main-ppi fw-bold">Correo electrónico</div>
                                    <div class="text-second-ppi small"><?= e($datos['Correo electrónico']) ?></div>
                                </div>
                                <?php if ($verificada): ?>
                                    <span class="perfil-chip perfil-chip-ok">Verificado</span>
                                <?php else: ?>
                                    <span class="perfil-chip">Pendiente</span>
                                <?php endif; ?>
                            </li>
                            <?php if ($esPersonal): ?>
                                <li class="perfil-dato">
                                    <div>
                                        <div class="text-main-ppi fw-bold">Cuenta con privilegios</div>
                                        <div class="text-second-ppi small">Tu cuenta puede moderar subastas. Usa una contraseña única y no la compartas.</div>
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </section>
            </div>

            <?php if ($esUsuario): ?>
                <!-- USUARIO COMÚN: sus subastas en curso -->
                <section id="seccionMisSubastas" aria-labelledby="titulo-subastas" data-subastas-url="<?= BASE_URL ?>controllers/perfil_controller.php">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <h2 id="titulo-subastas" class="fs-4 fw-bold text-main-ppi mb-0">Mis subastas activas</h2>
                        <a href="<?= BASE_URL ?>views/subastas/mis-productos.php" class="link-ppi fw-bold d-inline p-0 small">Ver mis productos</a>
                    </div>

                    <?php if (empty($subastas)): ?>
                        <div class="empty-state-box">
                            <i class="fas fa-gavel fs-1 text-second-ppi opacity-50 mb-3"></i>
                            <h3 class="fs-5 fw-bold text-main-ppi mb-2">No tienes subastas activas</h3>
                            <p class="text-second-ppi small mb-4">Cuando publiques un producto en subasta y sea aprobado, aparecerá aquí.</p>
                            <a href="<?= BASE_URL ?>views/subastas/registro-producto.php" class="btn btn-primary px-4 py-2 fw-semibold">
                                <i class="fas fa-plus me-1"></i> Registrar un producto
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($subastas as $s): ?>
                                <?php
                                $imgRuta = !empty($s['imagen_principal'])
                                    ? BASE_URL . 'uploads/productos/' . e($s['imagen_principal'])
                                    : BASE_URL . 'assets/img/hero-img.jpg';
                                $pujas = (int)$s['total_pujas'];
                                ?>
                                <div class="col-12 col-md-6 col-lg-4">
                                    <a href="<?= BASE_URL ?>views/subastas/mis-productos.php" class="perfil-subasta d-block text-decoration-none" data-subasta-id="<?= (int)$s['id_subasta'] ?>" title="Ver en mis productos">
                                        <img src="<?= $imgRuta ?>" alt="<?= e($s['nombre']) ?>" class="perfil-subasta-img" onerror="this.onerror=null; this.src='<?= BASE_URL ?>assets/img/hero-img.jpg';">
                                        <div class="p-3">
                                            <h3 class="fs-6 fw-bold text-main-ppi text-truncate mb-2" title="<?= e($s['nombre']) ?>"><?= e($s['nombre']) ?></h3>
                                            <div class="d-flex justify-content-between align-items-baseline gap-2">
                                                <span class="precio-ppi fs-5" data-subasta-precio title="Puja más alta">$<?= number_format((float)$s['precio_actual'], 2) ?></span>
                                                <span class="tiempo-ppi small" data-segundos-restantes="<?= max(0, (int)$s['segundos_restantes']) ?>"><?= e(tiempoRestante($s['segundos_restantes'])) ?></span>
                                            </div>
                                            <div class="text-second-ppi small mt-1" data-subasta-pujas><?= $pujas ?> <?= $pujas === 1 ? 'puja' : 'pujas' ?></div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

        </div>
    </main>

    <!-- Cambiar contra -->
    <div class="modal fade" id="modalCambiarClave" tabindex="-1" aria-labelledby="modalCambiarClaveTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-ppi">
                <div class="modal-header modal-header-ppi">
                    <h5 class="modal-title fw-bold text-white" id="modalCambiarClaveTitulo">
                        Cambiar contraseña
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body py-4">
                    <form id="formCambiarClave" method="POST" action="<?= BASE_URL ?>controllers/perfil_controller.php" autocomplete="off" novalidate>
                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="contrasenia_actual" class="form-label text-main-ppi fw-bold">Contraseña actual <span class="req-star">*</span></label>
                                <div class="password-field-wrapper">
                                    <input type="password" class="form-control" id="contrasenia_actual" name="contrasenia_actual" placeholder="Tu contraseña actual" autocomplete="current-password" required>
                                    <button type="button" class="btn-toggle-eye" data-target="contrasenia_actual" tabindex="-1" aria-label="Mostrar u ocultar contraseña"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="nueva_contrasenia" class="form-label text-main-ppi fw-bold">Contraseña nueva <span class="req-star">*</span></label>
                                <div class="password-field-wrapper">
                                    <input type="password" class="form-control" id="nueva_contrasenia" name="nueva_contrasenia" placeholder="Mínimo 8 caracteres" autocomplete="new-password" required>
                                    <button type="button" class="btn-toggle-eye" data-target="nueva_contrasenia" tabindex="-1" aria-label="Mostrar u ocultar contraseña"><i class="bi bi-eye"></i></button>
                                </div>
                                <div class="form-text text-second-ppi">Al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo.</div>
                            </div>
                            <div class="col-12">
                                <label for="confirmar_contrasenia" class="form-label text-main-ppi fw-bold">Confirmar contraseña nueva <span class="req-star">*</span></label>
                                <div class="password-field-wrapper">
                                    <input type="password" class="form-control" id="confirmar_contrasenia" name="confirmar_contrasenia" placeholder="Repite la contraseña nueva" autocomplete="new-password" required>
                                    <button type="button" class="btn-toggle-eye" data-target="confirmar_contrasenia" tabindex="-1" aria-label="Mostrar u ocultar contraseña"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-ppi d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formCambiarClave" class="btn btn-primary btn-sm px-3 py-2 fw-semibold" id="btnGuardarClave">Guardar contraseña</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPPI" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-ppi">
                <div class="modal-header modal-header-ppi">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="modalPPITitulo">
                        Aviso
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body py-4" id="modalPPICuerpo">
                    Mensaje
                </div>
                <div class="modal-footer modal-footer-ppi">
                    <button type="button" class="btn btn-ppi-modal btn-sm" id="btnModalPPIAccion" data-bs-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <!-- modal editar datos -->
    <div class="modal fade" id="modalEditarPerfil" tabindex="-1" aria-labelledby="modalEditarPerfilTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-ppi">
                <div class="modal-header modal-header-ppi">
                    <h5 class="modal-title fw-bold text-white" id="modalEditarPerfilTitulo">
                        Editar datos personales
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body py-4">
                    <form id="formEditarPerfil" method="POST" action="<?= BASE_URL ?>controllers/perfil_controller.php" autocomplete="off" novalidate>
                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_nombre" class="form-label text-main-ppi fw-bold">Nombre <span class="req-star">*</span></label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" maxlength="50" value="<?= e($perfil['nombre']) ?>" required>
                                <div class="invalid-feedback" id="err_edit_nombre">Ingresa tu nombre.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_apellido" class="form-label text-main-ppi fw-bold">Apellido <span class="req-star">*</span></label>
                                <input type="text" class="form-control" id="edit_apellido" name="apellido" maxlength="50" value="<?= e($perfil['apellido']) ?>" required>
                                <div class="invalid-feedback" id="err_edit_apellido">Ingresa tu apellido.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_telefono" class="form-label text-main-ppi fw-bold">Teléfono <span class="req-star">*</span></label>
                                <input type="tel" class="form-control" id="edit_telefono" name="telefono" placeholder="Ej. 7000-0000" maxlength="9" value="<?= e($perfil['telefono']) ?>" required>
                                <div class="invalid-feedback" id="err_edit_telefono">Usa el formato 7000-0000.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_ubicacion" class="form-label text-main-ppi fw-bold">Ubicación <span class="req-star">*</span></label>
                                <input type="text" class="form-control" id="edit_ubicacion" name="ubicacion" placeholder="Ciudad, Departamento" maxlength="100" value="<?= e($perfil['ubicacion']) ?>" required>
                                <div class="invalid-feedback" id="err_edit_ubicacion">Ingresa tu ubicación.</div>
                            </div>
                            <div class="col-12">
                                <div class="form-text text-second-ppi">El correo y el nombre de usuario no se pueden cambiar desde aquí.</div>
                            </div>
                            <div class="col-12">
                                <label for="edit_username" class="form-label text-main-ppi fw-bold">Nombre de usuario <span class="req-star">*</span></label>
                                <input type="text" class="form-control" id="edit_username" name="username" maxlength="50" value="<?= e($perfil['username']) ?>" required>
                                <div class="invalid-feedback" id="err_edit_username">Ingresa tu nombre de usuario.</div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-ppi d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formEditarPerfil" class="btn btn-primary btn-sm px-3 py-2 fw-semibold" id="btnGuardarDatos">Guardar cambios</button>
                </div>
            </div>
        </div>
    </div>

    <?php include "Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/toast-ppi.js"></script>
    <script src="<?= BASE_URL ?>assets/js/perfil.js?v=7"></script>
</body>

</html>
