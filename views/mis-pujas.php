<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dirnameMain = dirname(__DIR__, 1);
require_once $dirnameMain . '/config/env.php';
require_once $dirnameMain . '/dao/CatalogoDAO.php';

$base_url = BASE_URL;

// Si no ha iniciado sesión, redirigir al login
$id_usuario = (int)($_SESSION['id_usuario'] ?? 0);
if ($id_usuario <= 0) {
    header('Location: ' . $base_url . 'views/auth/login.php?redirect=' . urlencode('views/subastas/mis-pujas.php'));
    exit;
}

if (!function_exists('e')) {
    function e($texto)
    {
        return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
    }
}

$titulo = "Mis pujas";
$pagina = "mis-pujas";

$filtrosValidos = ['todas', 'activas', 'ganadas', 'finalizadas'];
$filtroActual = $_GET['filtro'] ?? 'todas';
if (!in_array($filtroActual, $filtrosValidos, true)) {
    $filtroActual = 'todas';
}

$dao = new CatalogoDAO();
$todasLasPujas = $dao->obtenerSubastasParticipadas($id_usuario, 'todas');

// Contadores por pestaña
$contadores = [
    'todas'       => count($todasLasPujas),
    'activas'     => 0,
    'ganadas'     => 0,
    'finalizadas' => 0,
];

foreach ($todasLasPujas as $p) {
    if (in_array($p['estado_usuario'], ['ganando', 'superado'], true)) {
        $contadores['activas']++;
    } else {
        $contadores['finalizadas']++;
        if ($p['estado_usuario'] === 'ganada') {
            $contadores['ganadas']++;
        }
    }
}

// Filtrar lista a mostrar según la pestaña activa
$items = array_values(array_filter($todasLasPujas, function ($item) use ($filtroActual) {
    if ($filtroActual === 'activas') {
        return in_array($item['estado_usuario'], ['ganando', 'superado'], true);
    }
    if ($filtroActual === 'ganadas') {
        return $item['estado_usuario'] === 'ganada';
    }
    if ($filtroActual === 'finalizadas') {
        return in_array($item['estado_usuario'], ['ganada', 'perdida'], true);
    }
    return true;
}));

$badgesEstado = [
    'ganando'  => ['texto' => 'Vas ganando',      'icono' => 'fa-arrow-up',   'bg' => '#658807', 'color' => '#ffffff'],
    'superado' => ['texto' => 'Te superaron',     'icono' => 'fa-exclamation-circle', 'bg' => '#b45309', 'color' => '#ffffff'],
    'ganada'   => ['texto' => '¡Subasta ganada!', 'icono' => 'fa-trophy',     'bg' => '#658807', 'color' => '#ffffff'],
    'perdida'  => ['texto' => 'Finalizada',       'icono' => 'fa-flag-checkered', 'bg' => '#374151', 'color' => '#d6ddd0'],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title><?= e($titulo) ?> | Gavel.net</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <?php include $dirnameMain . '/includes/meta_tags.php'; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Raleway:wght@400;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="<?= $base_url ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $base_url ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= $base_url ?>assets/css/main.css?v=47" rel="stylesheet">
    <link href="<?= $base_url ?>assets/css/catalogo.css?v=10" rel="stylesheet">
</head>

<body class="bg-dark-ppi text-second-ppi d-flex flex-column min-vh-100">

    <?php include "NavBar/nav.php"; ?>

    <section class="hero-ppi hero-catalogo text-center" aria-labelledby="mis-pujas-titulo">
        <div class="container-fluid px-4 px-lg-5">
            <p class="hero-label mb-3 d-inline-block">Actividad · Gavel.net</p>
            <h1 id="mis-pujas-titulo" class="hero-titulo text-main-ppi mb-3">
                Mis <span class="text-third-ppi">pujas y subastas</span>
            </h1>
            <p class="text-second-ppi mb-0 mx-auto hero-catalogo-sub">
                Haz seguimiento a las subastas en las que participas y consulta tus artículos ganados.
            </p>
        </div>
    </section>

    <main class="container-fluid px-3 px-lg-5 py-5 flex-grow-1">
        <div class="container catalogo-container">

            <!-- Pestañas de filtro -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= $base_url ?>views/subastas/mis-pujas.php?filtro=todas"
                       class="btn btn-sm fw-semibold px-3 py-2 <?= $filtroActual === 'todas' ? 'btn-primary' : 'btn-contorno-ppi' ?>">
                        Todas (<?= $contadores['todas'] ?>)
                    </a>
                    <a href="<?= $base_url ?>views/subastas/mis-pujas.php?filtro=activas"
                       class="btn btn-sm fw-semibold px-3 py-2 <?= $filtroActual === 'activas' ? 'btn-primary' : 'btn-contorno-ppi' ?>">
                        En curso (<?= $contadores['activas'] ?>)
                    </a>
                    <a href="<?= $base_url ?>views/subastas/mis-pujas.php?filtro=ganadas"
                       class="btn btn-sm fw-semibold px-3 py-2 <?= $filtroActual === 'ganadas' ? 'btn-primary' : 'btn-contorno-ppi' ?>">
                        <i class="fas fa-trophy me-1"></i>Ganadas (<?= $contadores['ganadas'] ?>)
                    </a>
                    <a href="<?= $base_url ?>views/subastas/mis-pujas.php?filtro=finalizadas"
                       class="btn btn-sm fw-semibold px-3 py-2 <?= $filtroActual === 'finalizadas' ? 'btn-primary' : 'btn-contorno-ppi' ?>">
                        Finalizadas (<?= $contadores['finalizadas'] ?>)
                    </a>
                </div>

                <a href="<?= $base_url ?>views/catalogo.php" class="link-ppi d-inline p-0 small fw-bold">
                    Explorar catálogo <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>

            <?php if (empty($items)): ?>
                <div class="catalogo-vacio text-center">
                    <i class="fas fa-gavel fs-1 text-second-ppi opacity-50 mb-3"></i>
                    <h2 class="fs-4 fw-bold text-main-ppi mb-2">
                        <?= $filtroActual === 'todas' ? 'Aún no has participado en ninguna subasta' : 'No hay subastas en esta categoría' ?>
                    </h2>
                    <p class="text-second-ppi mb-4">
                        <?= $filtroActual === 'todas'
                            ? 'Explora los artículos disponibles y realiza tu primera puja.'
                            : 'Cambia de pestaña o sigue pujando en el catálogo activo.' ?>
                    </p>
                    <a href="<?= $base_url ?>views/catalogo.php" class="btn btn-primary px-4 py-2 fw-semibold">
                        Ir al catálogo
                    </a>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($items as $item): ?>
                        <?php
                        $est = $item['estado_usuario'];
                        $badge = $badgesEstado[$est] ?? $badgesEstado['perdida'];
                        $esActiva = in_array($est, ['ganando', 'superado'], true);
                        $esGanada = ($est === 'ganada');
                        ?>
                        <div class="col">
                            <article class="catalogo-card h-100 d-flex flex-column position-relative"
                                     style="<?= $esGanada ? 'border: 1px solid #658807;' : '' ?>">
                                
                                <!-- Badge de estado del usuario -->
                                <span style="
                                    position: absolute;
                                    top: 12px;
                                    left: 12px;
                                    background-color: <?= e($badge['bg']) ?>;
                                    color: <?= e($badge['color']) ?>;
                                    padding: 4px 12px;
                                    border-radius: 999px;
                                    font-size: 0.78rem;
                                    font-weight: 700;
                                    z-index: 5;
                                    box-shadow: 0 2px 6px rgba(0,0,0,0.45);
                                ">
                                    <i class="fas <?= e($badge['icono']) ?> me-1"></i><?= e($badge['texto']) ?>
                                </span>

                                <a href="<?= $base_url ?>views/subastas/detalle-subasta.php?id=<?= (int)$item['id'] ?>"
                                   class="catalogo-card-thumb d-block text-decoration-none" tabindex="-1" aria-hidden="true">
                                    <img src="<?= $base_url . e($item['imagen']) ?>" alt="" loading="lazy"
                                         onerror="this.onerror=null; this.src='<?= $base_url ?>assets/img/news-1.jpg';">
                                </a>

                                <div class="p-4 d-flex flex-column flex-grow-1">
                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-1 small text-second-ppi">
                                        <span class="catalogo-categoria-label"><?= e($item['categoria']) ?></span>
                                        <span aria-hidden="true">&middot;</span>
                                        <span>Vendedor: @<?= e($item['vendedor']) ?></span>
                                    </div>

                                    <h2 class="fs-5 fw-bold text-main-ppi mt-1 mb-3 catalogo-card-titulo">
                                        <?= e($item['titulo']) ?>
                                    </h2>

                                    <!-- Comparativa de pujas -->
                                    <div class="d-flex justify-content-between align-items-end gap-2 mt-auto pt-2 border-top border-secondary border-opacity-25">
                                        <div>
                                            <span class="catalogo-meta-label d-block">Tu puja máxima</span>
                                            <span class="h5 mb-0 text-main-ppi fw-bold">$<?= e($item['mi_puja']) ?></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="catalogo-meta-label d-block">
                                                <?= $esActiva ? 'Puja actual' : 'Precio final' ?>
                                            </span>
                                            <span class="h5 mb-0 precio-ppi">$<?= e($item['puja_actual']) ?></span>
                                        </div>
                                    </div>

                                    <div class="small text-second-ppi mt-2 mb-3">
                                        <?php if ($esActiva): ?>
                                            <span class="tiempo-ppi">
                                                <i class="far fa-clock me-1"></i>
                                                <span data-countdown="<?= e($item['fecha_final_iso']) ?>"><?= e($item['tiempo']) ?></span>
                                            </span>
                                        <?php else: ?>
                                            <span><i class="far fa-calendar-check me-1"></i>Subasta cerrada</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Botones de acción -->
                                    <div class="mt-auto d-grid gap-2">
                                        <?php if ($esGanada): ?>
                                            <button type="button"
                                                    class="btn btn-primary w-100 fw-semibold py-2 btn-ver-contacto"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalContactoVendedor"
                                                    data-titulo="<?= e($item['titulo']) ?>"
                                                    data-monto="<?= e($item['puja_actual']) ?>"
                                                    data-vendedor="<?= e($item['vendedor']) ?>"
                                                    data-email="<?= e($item['vendedor_email'] ?? 'No disponible') ?>"
                                                    data-telefono="<?= e($item['vendedor_telefono'] ?? 'No disponible') ?>">
                                                <i class="fas fa-handshake me-1"></i> Contactar vendedor
                                            </button>
                                            <a href="<?= $base_url ?>views/subastas/detalle-subasta.php?id=<?= (int)$item['id'] ?>"
                                               class="btn btn-contorno-ppi w-100 fw-semibold py-2 btn-sm">
                                                Ver historial de pujas
                                            </a>
                                        <?php elseif ($est === 'superado'): ?>
                                            <a href="<?= $base_url ?>views/subastas/detalle-subasta.php?id=<?= (int)$item['id'] ?>"
                                               class="btn btn-primary w-100 fw-semibold py-2">
                                                <i class="fas fa-gavel me-1"></i> Subir puja
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= $base_url ?>views/subastas/detalle-subasta.php?id=<?= (int)$item['id'] ?>"
                                               class="btn btn-contorno-ppi w-100 fw-semibold py-2">
                                                Ver subasta <i class="fas fa-arrow-right ms-1 small"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- Modal de contacto con el vendedor (solo subastas ganadas) -->
    <div class="modal fade" id="modalContactoVendedor" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-ppi" style="background-color: #141911; border: 1px solid #658807;">
                <div class="modal-header modal-header-ppi">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="fas fa-trophy text-warning me-2"></i>¡Felicidades por tu compra!
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body py-4" style="color: #d6ddd0;">
                    <p class="mb-3">
                        Ganaste <strong id="contacto-titulo" class="text-white">—</strong> por
                        <strong id="contacto-monto" style="color: #8ab80a;">$0.00</strong>.
                        Comunícate con el vendedor para coordinar el pago y la entrega:
                    </p>
                    <div class="p-3 rounded" style="background-color: rgba(255,255,255,0.05);">
                        <div class="mb-2">
                            <span class="small text-second-ppi d-block">Usuario vendedor</span>
                            <strong id="contacto-vendedor" class="text-white">—</strong>
                        </div>
                        <div class="mb-2">
                            <span class="small text-second-ppi d-block">Correo electrónico</span>
                            <strong id="contacto-email" class="text-white">—</strong>
                        </div>
                        <div>
                            <span class="small text-second-ppi d-block">Teléfono</span>
                            <strong id="contacto-telefono" class="text-white">—</strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-ppi">
                    <button type="button" class="btn btn-primary btn-sm px-4" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include "Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $base_url ?>assets/js/temporizador-subastas.js?v=6" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalContacto = document.getElementById('modalContactoVendedor');
            if (modalContacto) {
                modalContacto.addEventListener('show.bs.modal', (event) => {
                    const btn = event.relatedTarget;
                    if (!btn) return;
                    document.getElementById('contacto-titulo').textContent = btn.getAttribute('data-titulo') || '—';
                    document.getElementById('contacto-monto').textContent = '$' + (btn.getAttribute('data-monto') || '0.00');
                    document.getElementById('contacto-vendedor').textContent = '@' + (btn.getAttribute('data-vendedor') || '—');
                    document.getElementById('contacto-email').textContent = btn.getAttribute('data-email') || 'No disponible';
                    document.getElementById('contacto-telefono').textContent = btn.getAttribute('data-telefono') || 'No disponible';
                });
            }
        });
    </script>
</body>

</html>