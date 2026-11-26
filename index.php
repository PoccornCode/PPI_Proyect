<?php
$titulo = "Inicio";
$pagina = "inicio";

require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/controllers/categoria_controller.php';
require_once __DIR__ . '/controllers/subasta_controller.php';

if (!function_exists('e')) {
    function e($texto)
    {
        return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
    }
}

//Iconos Font Awesome para las categorías en las píldoras del Hero
$iconosCategoria = [
    'Arte'                   => 'fa-palette',
    'Coleccionables'         => 'fa-star',
    'Deportes'               => 'fa-running',
    'Hogar'                  => 'fa-home',
    'Instrumentos Musicales' => 'fa-guitar',
    'Joyería'                => 'fa-gem',
    'Libros'                 => 'fa-book',
    'Moda'                   => 'fa-tshirt',
    'Tecnología'             => 'fa-laptop',
    'Vehículos'              => 'fa-car',
];

// Categorías populares reales desde la BD (ordenadas por cantidad de productos)
$categorias = obtenerCategoriasPopularesHome(6);

foreach ($categorias as &$catItem) {
    $catItem['icono'] = $iconosCategoria[$catItem['nombre']] ?? 'fa-tag';
}
unset($catItem);

// Categorías desde el controlador/DAO en orden alfabético para el select del buscador
$categoriasSelect = obtenerCategoriasCatalogo();

$destacada = obtenerSubastaDestacadaHome();
$pronto    = obtenerSubastasTerminanProntoHome(5, $destacada['id'] ?? null);
$nuevas    = obtenerNuevasSubastasHome(4);

//  cambiar a la vista de detalle real cuando exista
$urlSubasta = 'views/subasta.php?id=';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title><?= e($titulo) ?> | Gavel.net</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php include __DIR__ . '/includes/meta_tags.php'; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@100;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />

    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/main.css?v=45" rel="stylesheet">
</head>

<body class="bg-dark-ppi">

    <?php include "views/NavBar/nav.php"; ?>

    <main>

        <section class="hero-ppi" aria-labelledby="hero-titulo">
            <div class="container-fluid px-4 px-lg-5">
                <div class="row justify-content-center">
                    <div class="col-12 text-center">
                        <p class="hero-label mb-4 d-inline-block">Subastas en línea · El Salvador</p>
                        <h1 id="hero-titulo" class="hero-titulo text-main-ppi mb-4">
                            Compra, vende y <span class="text-third-ppi">puja</span> en línea.
                        </h1>
                        <p class="text-second-ppi mb-5 mx-auto" style="max-width:620px; font-size:1.15rem; line-height:1.7;">
                            Encuentra productos únicos o publica lo que ya no necesitas.
                            Miles de subastas activas esperan tu oferta.
                        </p>
                        <div class="d-flex flex-wrap justify-content-center gap-3 mb-5">
                            <a href="views/catalogo.php" class="btn btn-primary px-5 py-3 fw-bold">Explorar subastas</a>
                            <a href="views/subastas/registro-producto.php" class="btn btn-secondary px-5 py-3 fw-bold">Publicar producto</a>
                        </div>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <?php foreach ($categorias as $cat): ?>
                                <a href="views/catalogo.php?categoria=<?= urlencode($cat['nombre']) ?>" class="hero-cat-pill">
                                    <i class="fa <?= e($cat['icono']) ?>"></i> <?= e($cat['nombre']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-4" aria-label="Buscador de subastas">
            <div class="container-fluid px-4 px-lg-5">
                <form action="views/catalogo.php" method="GET" class="row g-3" role="search">
                    <div class="col-lg">
                        <input type="search" class="form-control py-3" id="q" name="q" placeholder="Busca un producto, marca o palabra clave" aria-label="Buscar subasta">
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <select class="form-select py-3" id="categoria" name="categoria" aria-label="Categoría">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categoriasSelect as $cat) { ?>
                                <option value="<?= e($cat['nombre']) ?>"><?= e($cat['nombre']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <select class="form-select py-3" id="estado" name="estado" aria-label="Estado">
                            <option value="activas">Activas</option>
                            <option value="hoy">Terminan hoy</option>
                            <option value="proximas">Próximas</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-auto">
                        <button type="submit" class="w-100 btn btn-primary py-3 px-5 fw-bold">Buscar</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="py-4" aria-labelledby="titulo-destacada">
            <div class="container-fluid px-4 px-lg-5">
                <div class="row g-4">

                    <div class="col-lg-8" id="contenedor-destacada">
                        <?php if ($destacada): ?>
                            <article class="card h-100 overflow-hidden">
                                <img src="<?= e($destacada['imagen']) ?>" class="foto-lote foto-lote-destacada" alt="<?= e($destacada['titulo']) ?>">
                                <div class="p-4">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-7">
                                            <p class="mb-1 small text-uppercase text-second-ppi">
                                                <span class="text-third-ppi fw-bold">Destacada</span> &middot; <?= e($destacada['categoria']) ?>
                                            </p>
                                            <h2 id="titulo-destacada" class="fs-3 fw-bold text-main-ppi"><?= e($destacada['titulo']) ?></h2>
                                            <p class="mb-0 text-second-ppi"><?= e($destacada['descripcion']) ?></p>
                                        </div>
                                        <div class="col-md-5">
                                            <span class="d-block small text-second-ppi">Puja actual</span>
                                            <span class="d-block display-6 precio-ppi">$<?= e($destacada['precio']) ?></span>
                                            <span class="d-block mb-3 tiempo-ppi"><i class="far fa-clock me-2"></i><span data-countdown="<?= e($destacada['fecha_final_iso']) ?>" data-countdown-prefix="Termina en ">Termina en <?= e($destacada['tiempo']) ?></span></span>
                                            <a href="<?= e($urlSubasta . $destacada['id']) ?>" class="w-100 btn btn-primary py-2 fw-bold">Pujar ahora</a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php else: ?>
                            <article class="card h-100 p-5 d-flex flex-column justify-content-center align-items-center text-center">
                                <i class="fas fa-gavel fa-2x text-second-ppi mb-3"></i>
                                <h2 id="titulo-destacada" class="fs-4 fw-bold text-main-ppi mb-2">No hay subastas activas en este momento</h2>
                                <p class="text-second-ppi mb-4" style="max-width: 420px;">Sé el primero en publicar un producto y abrir una nueva subasta en la plataforma.</p>
                                <a href="views/subastas/registro-producto.php" class="btn btn-primary px-4 py-2 fw-bold">Publicar producto</a>
                            </article>
                        <?php endif; ?>
                    </div>

                    <!-- Terminan pronto -->
                    <div class="col-lg-4" id="contenedor-pronto">
                        <aside class="card h-100 p-4 d-flex flex-column" aria-labelledby="titulo-pronto">
                            <h3 id="titulo-pronto" class="fs-5 fw-bold text-main-ppi mb-3">Terminan pronto</h3>
                            <?php if (!empty($pronto)): ?>
                                <?php foreach ($pronto as $item) { ?>
                                    <a href="<?= e($urlSubasta . $item['id']) ?>" class="d-flex align-items-center gap-3 py-3 separador-ppi text-decoration-none text-main-ppi">
                                        <img src="<?= e($item['imagen']) ?>" class="foto-lote-mini" alt="">
                                        <span class="d-flex flex-column">
                                            <span class="fw-bold"><?= e($item['titulo']) ?></span>
                                            <span class="small text-second-ppi">$<?= e($item['precio']) ?></span>
                                            <span class="small tiempo-ppi" data-countdown="<?= e($item['fecha_final_iso']) ?>" data-countdown-prefix="Termina en ">Termina en <?= e($item['tiempo']) ?></span>
                                        </span>
                                    </a>
                                <?php } ?>
                                <a href="views/catalogo.php?estado=hoy" class="link-ppi d-inline p-0 mt-auto pt-3 fw-bold small">Ver todas las que terminan hoy &rarr;</a>
                            <?php else: ?>
                                <p class="text-second-ppi small my-auto">No hay subastas próximas a finalizar.</p>
                            <?php endif; ?>
                        </aside>
                    </div>

                </div>
            </div>
        </section>

        <section class="pt-4 pb-5" aria-labelledby="titulo-nuevas">
            <div class="container-fluid px-4 px-lg-5">
                <div class="d-flex justify-content-between align-items-baseline mb-4 flex-wrap gap-2">
                    <h2 id="titulo-nuevas" class="fw-bold text-main-ppi mb-0">Nuevas subastas</h2>
                    <a href="views/catalogo.php" class="link-ppi d-inline p-0 fw-bold small">Ver catálogo completo &rarr;</a>
                </div>
                <div id="contenedor-nuevas">
                    <?php if (!empty($nuevas)): ?>
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
                            <?php foreach ($nuevas as $item) { ?>
                                <div class="col">
                                    <article class="card h-100 overflow-hidden">
                                        <img src="<?= e($item['imagen']) ?>" class="foto-lote" alt="<?= e($item['titulo']) ?>">
                                        <div class="p-3 d-flex flex-column flex-grow-1">
                                            <span class="small text-uppercase text-second-ppi"><?= e($item['categoria']) ?></span>
                                            <h3 class="fs-6 fw-bold text-main-ppi mt-1"><?= e($item['titulo']) ?></h3>
                                            <div class="d-flex justify-content-between align-items-center mt-auto pt-2 mb-3">
                                                <span class="h5 mb-0 precio-ppi">$<?= e($item['precio']) ?></span>
                                                <span class="small tiempo-ppi" data-countdown="<?= e($item['fecha_final_iso']) ?>"><?= e($item['tiempo']) ?></span>
                                            </div>
                                            <a href="<?= e($urlSubasta . $item['id']) ?>" class="btn btn-contorno-ppi fw-bold">Ver subasta</a>
                                        </div>
                                    </article>
                                </div>
                            <?php } ?>
                        </div>
                    <?php else: ?>
                        <div class="card p-4 text-center">
                            <p class="text-second-ppi mb-0">Aún no hay nuevas subastas activas.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php if (!empty($categorias)): ?>
            <section class="pt-5 pb-6" aria-labelledby="titulo-categorias" style="border-top: 1px solid #191c17; padding-bottom: 5rem !important;">
                <div class="container-fluid px-4 px-lg-5">
                    <div class="d-flex justify-content-between align-items-baseline mb-4 flex-wrap gap-2">
                        <div>
                            <span class="hero-label d-inline-block mb-1">Explorar por rubro</span>
                            <h2 id="titulo-categorias" class="fw-bold text-main-ppi mb-0">Categorías populares</h2>
                        </div>
                        <a href="views/categorias.php" class="link-ppi d-inline p-0 fw-bold small">Ver todas las categorías &rarr;</a>
                    </div>

                    <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-6 g-3 g-lg-4">
                        <?php foreach ($categorias as $cat) { ?>
                            <div class="col">
                                <a href="views/catalogo.php?categoria=<?= urlencode($cat['nombre']) ?>" class="card h-100 overflow-hidden text-decoration-none cat-card-ppi border-0 position-relative">
                                    <div class="cat-img-wrapper position-relative">
                                        <img src="<?= e($cat['imagen']) ?>" class="cat-card-img w-100" alt="<?= e($cat['nombre']) ?>" loading="lazy" decoding="async">
                                        <div class="cat-card-overlay d-flex flex-column justify-content-end p-3">
                                            <h3 class="fs-6 fw-bold text-white mb-0"><?= e($cat['nombre']) ?></h3>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

    </main>

    <?php include "views/Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/temporizador-subastas.js?v=<?= filemtime(__DIR__ . '/assets/js/temporizador-subastas.js') ?>" defer></script>
</body>

</html>