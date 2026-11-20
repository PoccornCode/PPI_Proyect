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
    //  sesion usuario que ya no existe
    header("Location: " . BASE_URL . "controllers/logout.php");
    exit();
}

$resumen = $dao->obtenerResumen($id_usuario);
$subastas = $dao->obtenerSubastasActivas($id_usuario, 3);

if (!function_exists('e')) {
    // escapa texto antes de imprimirlo
    function e($texto)
    {
        return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
    }
}

$iniciales = mb_strtoupper(mb_substr($perfil['nombre'] ?? '', 0, 1) . mb_substr($perfil['apellido'] ?? '', 0, 1));
if ($iniciales === '') {
    $iniciales = mb_strtoupper(mb_substr($perfil['username'] ?? 'U', 0, 1));
}

$roles = [1 => 'Administrador', 2 => 'Postor / Subastador', 3=> 'Moderador'];
$rolTexto = $roles[(int)$perfil['id_rol']] ?? 'Usuario';

$verificada = (int)$perfil['estado'] === 1;

// txt segundos que le faltan a la subasta
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

$datos = [
    'Nombre completo' => trim(($perfil['nombre'] ?? '') . ' ' . ($perfil['apellido'] ?? '')),
    'Nombre de usuario' => '@' . $perfil['username'],
    'Correo electrónico' => $perfil['correo'] ?: 'Sin registrar',
    'Teléfono' => $perfil['telefono'] ?: 'Sin registrar',
    'Ubicación' => $perfil['ubicacion'] ?: 'Sin registrar',
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> | Gavel.net</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/main.css?v=30" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/forms.css?v=41" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/perfil.css?v=1" rel="stylesheet">
</head>

<body class="bg-dark-ppi d-flex flex-column min-vh-100">

    <?php include "NavBar/nav.php"; ?>

    <!-- Encabezado vv -->
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
                        <span class="perfil-chip"><?= e($rolTexto) ?></span>
                    </div>
                </div>

                <button type="button" class="btn btn-perfil-salir" data-bs-toggle="modal" data-bs-target="#modalConfirmLogout">
                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                </button>
            </div>
        </div>
    </section>

    <main class="flex-grow-1 py-5">
        <div class="container d-flex flex-column gap-5">

            <!-- mi actividad -->
            <section aria-labelledby="titulo-actividad">
                <h2 id="titulo-actividad" class="fs-4 fw-bold text-main-ppi mb-3">Mi actividad</h2>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <a href="<?= BASE_URL ?>views/subastas/mis-productos.php" class="perfil-acceso">
                            <span class="perfil-acceso-valor"><?= $resumen['productos'] ?></span>
                            <span class="perfil-acceso-titulo">Mis productos</span>
                            <span class="perfil-acceso-detalle">Registra y publica en subasta</span>
                        </a>
                    </div>
                    <div class="col-12 col-md-4">
                        <a href="#" class="perfil-acceso" data-proximamente="Mis pujas">
                            <span class="perfil-acceso-valor">—</span>
                            <span class="perfil-acceso-titulo">Mis pujas</span>
                            <span class="perfil-acceso-detalle">Sigue tus ofertas y resultados</span>
                        </a>
                    </div>
                    <div class="col-12 col-md-4">
                        <a href="#" class="perfil-acceso" data-proximamente="Mis envíos">
                            <span class="perfil-acceso-valor">—</span>
                            <span class="perfil-acceso-titulo">Mis envíos</span>
                            <span class="perfil-acceso-detalle">Estado de tus paquetes</span>
                        </a>
                    </div>
                </div>
            </section>

            <!-- Dts personales y seguridad -->
            <div class="row g-4">
                <section class="col-lg-6" aria-labelledby="titulo-datos">
                    <div class="perfil-card h-100">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <h2 id="titulo-datos" class="fs-5 fw-bold text-main-ppi mb-0">Datos personales</h2>
                            <a href="#" class="link-ppi fw-bold d-inline p-0 small" data-proximamente="Editar datos personales">Editar</a>
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
                                    <div class="text-second-ppi small">Protege tu cuenta y tus pujas</div>
                                </div>
                                <a href="#" class="btn btn-outline-publish btn-sm px-3 py-2 fw-semibold" data-proximamente="Cambiar contraseña">Cambiar</a>
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
                        </ul>
                    </div>
                </section>
            </div>

            <!-- subastas activas -->
            <section aria-labelledby="titulo-subastas">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                    <h2 id="titulo-subastas" class="fs-4 fw-bold text-main-ppi mb-0">Mis subastas activas</h2>
                    <a href="<?= BASE_URL ?>views/subastas/mis-productos.php" class="link-ppi fw-bold d-inline p-0 small">Ver mis productos</a>
                </div>

                <?php if (empty($subastas)): ?>
                    <div class="empty-state-box">
                        <i class="fas fa-gavel fs-1 text-second-ppi opacity-50 mb-3"></i>
                        <h3 class="fs-5 fw-bold text-main-ppi mb-2">No tienes subastas activas</h3>
                        <p class="text-second-ppi small mb-4">Cuando publiques un producto en subasta, aparecerá aquí.</p>
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
                            ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <article class="perfil-subasta">
                                    <img src="<?= $imgRuta ?>" alt="<?= e($s['nombre']) ?>" class="perfil-subasta-img" onerror="this.onerror=null; this.src='<?= BASE_URL ?>assets/img/hero-img.jpg';">
                                    <div class="p-3">
                                        <h3 class="fs-6 fw-bold text-main-ppi text-truncate mb-2" title="<?= e($s['nombre']) ?>"><?= e($s['nombre']) ?></h3>
                                        <div class="d-flex justify-content-between align-items-baseline gap-2">
                                            <span class="precio-ppi fs-5" title="Precio inicial">$<?= number_format((float)$s['precio_inicial'], 2) ?></span>
                                            <span class="tiempo-ppi small"><?= e(tiempoRestante($s['segundos_restantes'])) ?></span>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

        </div>
    </main>

    <?php include "Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/toast-ppi.js"></script>
    <script src="<?= BASE_URL ?>assets/js/perfil.js?v=1"></script>
</body>

</html>
