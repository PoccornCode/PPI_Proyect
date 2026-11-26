<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/config/env.php';
require_once dirname(__DIR__) . '/controllers/categoria_controller.php';

$titulo = "Categorías";
$pagina = "categorias";
$base_url = BASE_URL;

if (!function_exists('e')) {
    function e($texto)
    {
        return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
    }
}

$categorias = obtenerCategoriasCatalogo();
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
    <link href="<?= $base_url ?>assets/css/main.css?v=40" rel="stylesheet">
    <link href="<?= $base_url ?>assets/css/categorias.css?v=2" rel="stylesheet">
</head>

<body class="bg-dark-ppi text-second-ppi">

    <?php include __DIR__ . "/NavBar/nav.php"; ?>

    <section class="hero-ppi hero-categorias text-center" aria-labelledby="categorias-titulo">
        <div class="container-fluid px-4 px-lg-5">
            <div class="row justify-content-center">
                <div class="col-12">
                    <p class="hero-label mb-3 d-inline-block">Catálogo de categorías · Gavel.net</p>
                    <h1 id="categorias-titulo" class="hero-titulo text-main-ppi mb-3">
                        Explora todas las <span class="text-third-ppi">categorías</span>
                    </h1>
                    <p class="text-second-ppi mb-0 mx-auto hero-categorias-sub">
                        Selecciona una categoría para descubrir artículos únicos y filtrar las subastas activas.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <main class="container-fluid px-4 px-lg-5 py-5">
        <div class="container py-2 categorias-container">

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
                <?php foreach ($categorias as $cat): ?>
                    <div class="col">
                        <article class="cat-dir-card h-100 d-flex flex-column">
                            <div class="cat-dir-thumb">
                                <img src="<?= $base_url . e($cat['imagen']) ?>" alt="<?= e($cat['nombre']) ?>">
                            </div>

                            <div class="p-4 d-flex flex-column flex-grow-1">
                                <h2 class="fs-5 fw-bold text-main-ppi mb-2"><?= e($cat['nombre']) ?></h2>
                                <p class="small text-second-ppi mb-4 cat-dir-desc">
                                    <?= e($cat['descripcion']) ?>
                                </p>

                                <a href="<?= $base_url ?>views/catalogo.php?categoria=<?= urlencode($cat['nombre']) ?>" class="btn btn-contorno-ppi w-100 fw-semibold py-2 mt-auto">
                                    Explorar <?= e($cat['nombre']) ?> <i class="fas fa-arrow-right ms-1 small"></i>
                                </a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center pt-5 pb-3 mt-4 categorias-cta">
                <h2 class="fs-4 fw-bold text-main-ppi mb-2">¿Tienes un artículo para subastar?</h2>
                <p class="text-second-ppi small mb-4 mx-auto categorias-cta-sub">
                    Regístralo en la categoría correspondiente y recibe ofertas en tiempo real.
                </p>
                <div class="d-flex justify-content-center">
                    <a href="<?= $base_url ?>views/subastas/registro-producto.php" class="btn btn-primary px-4 py-2 fw-semibold">
                        <i class="fas fa-plus me-2 small"></i>Registrar producto
                    </a>
                </div>
            </div>

        </div>
    </main>

    <?php include __DIR__ . "/Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
