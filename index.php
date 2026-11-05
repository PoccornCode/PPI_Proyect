<?php
$titulo = "Inicio";
$pagina = "inicio";

if (!function_exists('e')) {
    // Escapa texto antes de imprimirlo en el HTML
    function e($texto)
    {
        return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
    }
}

//datso de ejemplo 
$categorias = [
    ['nombre' => 'Arte',           'icono' => 'fa-palette'],
    ['nombre' => 'Tecnología',     'icono' => 'fa-laptop'],
    ['nombre' => 'Coleccionables', 'icono' => 'fa-star'],
    ['nombre' => 'Hogar',          'icono' => 'fa-home'],
    ['nombre' => 'Vehículos',      'icono' => 'fa-car'],
    ['nombre' => 'Joyería',        'icono' => 'fa-gem'],
];

$destacada = [
    'id'          => 1,
    'categoria'   => 'Coleccionables',
    'titulo'      => 'Reloj de bolsillo de latón, siglo XIX',
    'descripcion' => 'Pieza en buen estado de funcionamiento, con cadena original y estuche. Sin precio de reserva.',
    'precio'      => '185.00',
    'tiempo'      => '02:14:09',
    'imagen'      => 'assets/img/news-1.jpg',
];

$pronto = [
    ['id' => 2, 'titulo' => 'Cámara de película de 35 mm',    'precio' => '64.00',  'tiempo' => '00:42:10', 'imagen' => 'assets/img/news-2.jpg'],
    ['id' => 3, 'titulo' => 'Lámpara de mesa de bronce',      'precio' => '38.50',  'tiempo' => '01:05:33', 'imagen' => 'assets/img/news-3.jpg'],
    ['id' => 4, 'titulo' => 'Colección de monedas antiguas',  'precio' => '120.00', 'tiempo' => '01:48:02', 'imagen' => 'assets/img/news-4.jpg'],
    ['id' => 5, 'titulo' => 'Guitarra acústica de estudio',   'precio' => '210.00', 'tiempo' => '02:20:47', 'imagen' => 'assets/img/news-5.jpg'],
];

$nuevas = [
    ['id' => 6, 'categoria' => 'Arte',       'titulo' => 'Óleo sobre lienzo, paisaje',      'precio' => '95.00', 'tiempo' => '6 d 04 h', 'imagen' => 'assets/img/news-6.jpg'],
    ['id' => 7, 'categoria' => 'Tecnología', 'titulo' => 'Consola portátil retro',          'precio' => '72.00', 'tiempo' => '5 d 18 h', 'imagen' => 'assets/img/news-7.jpg'],
    ['id' => 8, 'categoria' => 'Hogar',      'titulo' => 'Juego de vajilla de cerámica',    'precio' => '45.00', 'tiempo' => '4 d 09 h', 'imagen' => 'assets/img/news-8.jpg'],
    ['id' => 9, 'categoria' => 'Joyería',    'titulo' => 'Anillo de plata con ópalo',       'precio' => '88.00', 'tiempo' => '3 d 22 h', 'imagen' => 'assets/img/lifestyle-1.jpg'],
];

// Vista de detalle de una subasta AUN NO EXISTE, OJO CAMBIAR CUANDO YA EXISTA
$urlSubasta = 'views/subasta.php?id=';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title><?= e($titulo) ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@100;600;800&display=swap" rel="stylesheet">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />

    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="bg-dark-ppi">

    <!--HEADER CALIDAD ADAN-->
    <?php
    include "views/NavBar/nav.php";
    ?>


    <main class="container py-4">

        <!-- Banner nosotros -->
        <section class="banner-ppi mt-5 p-4 p-md-5 d-flex flex-wrap align-items-center justify-content-between gap-4" aria-labelledby="titulo-vender">
            <div class="col-lg-7">
                <h2 id="titulo-vender" class="fw-bold text-main-ppi">Subastas</h2>
                <p class="mb-0 text-second-ppi">lero lero lero lero lero lero lero lero lero lero lero lero lero lero lero</p>
            </div>
            <a href="views/auth/registro.php" class="btn btn-primary py-3 px-5 fw-bold">Sobre nosotros</a>
        </section>

        <!-- Buscador con filtros -->
        <form action="views/catalogo.php" method="GET" class="row g-3 mt-2" role="search">
            <div class="col-lg">
                <label for="q" class="visually-hidden">Buscar subasta</label>
                <input type="search" class="form-control py-3" id="q" name="q" placeholder="Busca un artículo, marca o palabra clave">
            </div>
            <div class="col-sm-6 col-lg-3">
                <label for="categoria" class="visually-hidden">Categoría</label>
                <select class="form-select py-3" id="categoria" name="categoria">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $cat) { ?>
                        <option value="<?= e($cat['nombre']) ?>"><?= e($cat['nombre']) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label for="estado" class="visually-hidden">Estado</label>
                <select class="form-select py-3" id="estado" name="estado">
                    <option value="activas">Activas</option>
                    <option value="hoy">Terminan hoy</option>
                    <option value="proximas">Próximas</option>
                </select>
            </div>
            <div class="col-12 col-lg-auto">
                <button type="submit" class="w-100 btn btn-primary py-3 px-5 fw-bold">Buscar</button>
            </div>
        </form>

        <!-- Subasta destacada + terminan pronto -->
        <section class="row g-4 mt-4" aria-labelledby="titulo-destacada">

            <div class="col-lg-8">
                <article class="card h-100 overflow-hidden">
                    <div class="position-relative">
                        <img src="<?= e($destacada['imagen']) ?>" class="foto-lote foto-lote-destacada" alt="<?= e($destacada['titulo']) ?>">
                        <span class="etiqueta-ppi position-absolute top-0 start-0 m-3">Subasta destacada</span>
                    </div>
                    <div class="p-4">
                        <div class="row g-4 align-items-center">
                            <div class="col-md-7">
                                <p class="mb-1 small text-uppercase text-second-ppi"><?= e($destacada['categoria']) ?></p>
                                <h2 id="titulo-destacada" class="fs-3 fw-bold text-main-ppi"><?= e($destacada['titulo']) ?></h2>
                                <p class="mb-0 text-second-ppi"><?= e($destacada['descripcion']) ?></p>
                            </div>
                            <div class="col-md-5">
                                <span class="d-block small text-second-ppi">Puja actual</span>
                                <span class="d-block display-6 precio-ppi">$<?= e($destacada['precio']) ?></span>
                                <span class="d-block mb-3 tiempo-ppi"><i class="far fa-clock me-2"></i>Termina en <?= e($destacada['tiempo']) ?></span>
                                <a href="<?= e($urlSubasta . $destacada['id']) ?>" class="w-100 btn btn-primary py-2 fw-bold">Pujar ahora</a>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <div class="col-lg-4">
                <aside class="card h-100 p-4" aria-labelledby="titulo-pronto">
                    <h3 id="titulo-pronto" class="fs-5 fw-bold text-main-ppi mb-3">Terminan pronto</h3>
                    <?php foreach ($pronto as $item) { ?>
                        <a href="<?= e($urlSubasta . $item['id']) ?>" class="d-flex align-items-center gap-3 py-3 separador-ppi text-decoration-none text-main-ppi">
                            <img src="<?= e($item['imagen']) ?>" class="foto-lote-mini" alt="">
                            <span class="d-flex flex-column">
                                <span class="fw-bold"><?= e($item['titulo']) ?></span>
                                <span class="small text-second-ppi">$<?= e($item['precio']) ?></span>
                                <span class="small tiempo-ppi">Termina en <?= e($item['tiempo']) ?></span>
                            </span>
                        </a>
                    <?php } ?>
                    <a href="views/catalogo.php?estado=hoy" class="link-ppi d-inline p-0 mt-3 fw-bold small">Ver todas las que terminan hoy &rarr;</a>
                </aside>
            </div>

        </section>

        <!-- Categorías -->
        <section class="mt-5 pt-4" aria-labelledby="titulo-categorias">
            <h2 id="titulo-categorias" class="fw-bold text-main-ppi mb-1">Explora por categoría</h2>
            <p class="text-second-ppi mb-4">Encuentra lo que buscas y puja desde cualquier lugar.</p>
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-4 text-center">
                <?php foreach ($categorias as $cat) { ?>
                    <div class="col">
                        <a href="views/catalogo.php?categoria=<?= urlencode($cat['nombre']) ?>" class="d-block text-decoration-none">
                            <span class="circulo-categoria"><i class="fas <?= e($cat['icono']) ?> fa-2x" aria-hidden="true"></i></span>
                            <span class="d-block mt-3 fw-bold text-main-ppi"><?= e($cat['nombre']) ?></span>
                        </a>
                    </div>
                <?php } ?>
            </div>
        </section>

        <!-- Nuevas subastas -->
        <section class="mt-5 pt-4" aria-labelledby="titulo-nuevas">
            <div class="d-flex flex-wrap justify-content-between align-items-baseline gap-2 mb-4">
                <h2 id="titulo-nuevas" class="fw-bold text-main-ppi mb-0">Nuevas subastas</h2>
                <a href="views/catalogo.php" class="link-ppi d-inline p-0 fw-bold">Ver el catálogo completo &rarr;</a>
            </div>
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
                                    <span class="small tiempo-ppi"><?= e($item['tiempo']) ?></span>
                                </div>
                                <a href="<?= e($urlSubasta . $item['id']) ?>" class="btn btn-contorno-ppi fw-bold">Ver subasta</a>
                            </div>
                        </article>
                    </div>
                <?php } ?>
            </div>
        </section>

        <!-- Banner subastas -->
        <section class="banner-ppi mt-5 p-4 p-md-5 d-flex flex-wrap align-items-center justify-content-between gap-4" aria-labelledby="titulo-vender">
            <div class="col-lg-7">
                <h2 id="titulo-vender" class="fw-bold text-main-ppi">¿Tienes algo que vender?</h2>
                <p class="mb-0 text-second-ppi">Crea tu subasta, publica tu artículo y deja que los postores hagan el resto.</p>
            </div>
            <a href="views/auth/registro.php" class="btn btn-primary py-3 px-5 fw-bold">Crea tu subasta</a>
        </section>

    </main>

    <!-- FOOTER CALIDAD ADAN -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>