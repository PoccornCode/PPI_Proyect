<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/config/env.php';
require_once dirname(__DIR__) . '/controllers/catalogo_controller.php';

$base_url = BASE_URL;
$pagina = "subasta";

if (!function_exists('e')) {
    function e($texto)
    {
        return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
    }
}

$id_subasta = (int)($_GET['id'] ?? 0);
$id_usuario = (int)($_SESSION['id_usuario'] ?? 0);
$id_rol = (int)($_SESSION['id_rol'] ?? 0);

$s = $id_subasta > 0 ? obtenerDetalleSubasta($id_subasta, $id_usuario, $id_rol) : null;
if (!$s) {
    // no existe o no se puede ver
    require dirname(__DIR__) . '/404.php';
    exit;
}

$titulo = $s['titulo'];
$sinSesion = $id_usuario === 0;

$estadoTextos = [
    'finalizada'  => 'Subasta finalizada',
    'sin_ofertas' => 'Finalizó sin ofertas',
    'pendiente'   => 'En revisión por moderación',
    'rechazada'   => 'No fue aprobada',
    'cancelada'   => 'Subasta cancelada',
];
$avisoEstado = $estadoTextos[$s['estado']] ?? ($s['terminada'] ? 'Subasta finalizada' : '');

$imagenes = !empty($s['imagenes']) ? $s['imagenes'] : ['assets/img/news-1.jpg'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title><?= e($titulo) ?> | Gavel.net</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <?php include dirname(__DIR__) . '/includes/meta_tags.php'; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Raleway:wght@400;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="<?= $base_url ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $base_url ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= $base_url ?>assets/css/main.css?v=49" rel="stylesheet">
    <link href="<?= $base_url ?>assets/css/catalogo.css?v=10" rel="stylesheet">
</head>

<body class="bg-dark-ppi text-second-ppi d-flex flex-column min-vh-100">

    <?php include __DIR__ . "/NavBar/nav.php"; ?>

    <main class="container-fluid px-4 px-lg-5 py-4 flex-grow-1">
        <div class="container catalogo-container">

            <div class="row g-4 g-xl-5">

                <!-- fotos -->
                <div class="col-lg-7">
                    <div class="subasta-galeria">
                        <img src="<?= $base_url . e($imagenes[0]) ?>" id="imgPrincipal" class="subasta-img-principal" alt="<?= e($s['titulo']) ?>" onerror="this.onerror=null; this.src='<?= $base_url ?>assets/img/news-1.jpg';">
                    </div>
                    <?php if (count($imagenes) > 1): ?>
                        <div class="subasta-miniaturas d-flex flex-wrap gap-2 mt-3">
                            <?php foreach ($imagenes as $i => $img): ?>
                                <button type="button" class="subasta-mini <?= $i === 0 ? 'activa' : '' ?>" data-img="<?= $base_url . e($img) ?>" aria-label="Ver foto <?= $i + 1 ?>">
                                    <img src="<?= $base_url . e($img) ?>" alt="" loading="lazy">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- datos y puja -->
                <div class="col-lg-5">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <p class="small text-uppercase text-second-ppi mb-0">
                            <a href="<?= $base_url ?>views/catalogo.php?categoria=<?= urlencode($s['categoria']) ?>" class="text-third-ppi fw-bold text-decoration-none"><?= e($s['categoria']) ?></a>
                            <?php if ($s['condicion'] !== ''): ?>
                                &middot; <?= e($s['condicion']) ?>
                            <?php endif; ?>
                        </p>
                        <a href="<?= $base_url ?>views/catalogo.php" class="subasta-volver" onclick="if (document.referrer && document.referrer.indexOf(location.origin) === 0) { history.back(); return false; }">
                            <i class="fas fa-arrow-left me-2 small"></i>Volver
                        </a>
                    </div>
                    <h1 class="fs-2 fw-bold text-main-ppi mb-3"><?= e($s['titulo']) ?></h1>

                    <div class="subasta-panel">
                        <?php if ($avisoEstado !== ''): ?>
                            <span class="etiqueta-ppi mb-3"><?= e($avisoEstado) ?></span>
                        <?php endif; ?>

                        <div class="row g-3">
                            <div class="col-6">
                                <span class="d-block small text-second-ppi"><?= $s['terminada'] && $s['pujas'] > 0 ? 'Precio final' : 'Puja actual' ?></span>
                                <span class="d-block h2 mb-0 precio-ppi">$<?= e($s['precio']) ?></span>
                                <span class="small text-second-ppi"><?= $s['pujas'] ?> <?= $s['pujas'] === 1 ? 'puja' : 'pujas' ?></span>
                            </div>
                            <div class="col-6">
                                <span class="d-block small text-second-ppi">Tiempo restante</span>
                                <?php if ($s['terminada']): ?>
                                    <span class="d-block h4 mb-0 tiempo-ppi">Finalizada</span>
                                <?php else: ?>
                                    <span class="d-block h4 mb-0 tiempo-ppi"><i class="far fa-clock me-2"></i><span data-countdown="<?= e($s['fecha_final_iso']) ?>"><?= e($s['tiempo']) ?></span></span>
                                <?php endif; ?>
                                <span class="small text-second-ppi">Cierra el <?= e($s['cierre']) ?></span>
                            </div>
                        </div>

                        <div class="mt-3">
                        <?php if ($s['terminada']): ?>
                            <p class="mb-0 small text-second-ppi">Esta subasta ya no acepta pujas.</p>
                        <?php elseif ($s['es_dueno']): ?>
                            <p class="mb-0 small text-second-ppi"><i class="fas fa-info-circle me-1"></i>Esta es tu subasta, no puedes pujar en ella.</p>
                        <?php elseif ($sinSesion): ?>
                            <a href="<?= $base_url ?>views/auth/login.php?redirect=<?= urlencode('views/subasta.php?id=' . $s['id']) ?>" class="btn btn-primary w-100 py-2 fw-bold">Inicia sesión para pujar</a>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary w-100 py-2 fw-bold" data-proximamente="Pujar">Pujar</button>
                        <?php endif; ?>
                        </div>
                    </div>

                    <!-- datos de la subasta -->
                    <dl class="subasta-datos mb-0 mt-4">
                        <div><dt>Precio inicial</dt><dd>$<?= e($s['precio_inicial']) ?></dd></div>
                        <div><dt>Incremento mínimo</dt><dd>$<?= e($s['incremento']) ?></dd></div>
                        <?php if ($s['inicio'] !== ''): ?>
                            <div><dt>Inició</dt><dd><?= e($s['inicio']) ?></dd></div>
                        <?php endif; ?>
                        <?php if ($s['vendedor'] !== ''): ?>
                            <div><dt>Vendedor</dt><dd>@<?= e($s['vendedor']) ?></dd></div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <!-- descripcion -->
            <div class="row mt-4">
                <div class="col-12">
                    <section class="subasta-bloque" aria-labelledby="titulo-descripcion">
                        <h2 id="titulo-descripcion" class="fs-5 fw-bold text-main-ppi mb-3">Descripción</h2>
                        <p class="mb-0 subasta-descripcion"><?= nl2br(e($s['descripcion'] !== '' ? $s['descripcion'] : 'El vendedor no agregó una descripción.')) ?></p>
                    </section>
                </div>
            </div>

            <div class="mt-4">
                <a href="<?= $base_url ?>views/catalogo.php?categoria=<?= urlencode($s['categoria']) ?>" class="link-ppi d-inline p-0 fw-bold small">&larr; Volver a <?= e($s['categoria']) ?></a>
            </div>

        </div>
    </main>

    <?php include __DIR__ . "/Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $base_url ?>assets/js/toast-ppi.js"></script>
    <script src="<?= $base_url ?>assets/js/subasta.js?v=2"></script>
    <script src="<?= $base_url ?>assets/js/temporizador-subastas.js?v=5" defer></script>
</body>

</html>