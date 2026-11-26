<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/config/env.php';
require_once dirname(__DIR__) . '/controllers/categoria_controller.php';
require_once dirname(__DIR__) . '/controllers/catalogo_controller.php';

$titulo = "Catálogo de subastas";
$pagina = "catalogo";
$base_url = BASE_URL;

if (!function_exists('e')) {
    function e($texto)
    {
        return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
    }
}

$filtros = obtenerFiltrosCatalogo();
$paginaActual = max(1, (int)($_GET['pagina'] ?? 1));
$catalogo = obtenerCatalogo($filtros, $paginaActual);
$categorias = obtenerCategoriasCatalogo();

// dsc de la categoria elegida
$descripcionCategoria = '';
foreach ($categorias as $cat) {
    if (strcasecmp($cat['nombre'], $filtros['categoria']) === 0) {
        $filtros['categoria'] = $cat['nombre'];
        $descripcionCategoria = $cat['descripcion'];
    }
}

// arma un link del catalogo segun filtros 
function urlCatalogo(array $filtros, array $cambios = []): string
{
    $datos = array_merge($filtros, $cambios);
    if (($datos['orden'] ?? '') === 'pronto') {
        unset($datos['orden']);
    }
    $datos = array_filter($datos, function ($v) {
        return $v !== '' && $v !== null && $v !== 1;
    });
    return BASE_URL . 'views/catalogo.php' . ($datos ? '?' . http_build_query($datos) : '');
}

$ordenes = [
    'pronto'      => 'Terminan primero',
    'reciente'    => 'Más recientes',
    'precio_asc'  => 'Precio: menor a mayor',
    'precio_desc' => 'Precio: mayor a menor',
    'pujas'       => 'Más pujas',
];

$hayFiltros = $filtros['categoria'] !== '' || $filtros['q'] !== '' || $filtros['estado'] !== '';
$total = $catalogo['total'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title><?= e($filtros['categoria'] !== '' ? $filtros['categoria'] . ' | ' : '') ?><?= e($titulo) ?> | Gavel.net</title>
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
    <link href="<?= $base_url ?>assets/css/catalogo.css?v=1" rel="stylesheet">
</head>

<body class="bg-dark-ppi text-second-ppi d-flex flex-column min-vh-100">

    <?php include __DIR__ . "/NavBar/nav.php"; ?>

    <section class="hero-ppi hero-catalogo text-center" aria-labelledby="catalogo-titulo">
        <div class="container-fluid px-4 px-lg-5">
            <p class="hero-label mb-3 d-inline-block">Catálogo · Gavel.net</p>
            <h1 id="catalogo-titulo" class="hero-titulo text-main-ppi mb-3">
                <?php if ($filtros['categoria'] !== ''): ?>
                    Subastas de <span class="text-third-ppi"><?= e($filtros['categoria']) ?></span>
                <?php else: ?>
                    Catálogo de <span class="text-third-ppi">subastas</span>
                <?php endif; ?>
            </h1>
            <p class="text-second-ppi mb-0 mx-auto hero-catalogo-sub">
                <?= $descripcionCategoria !== '' ? e($descripcionCategoria) : 'Encuentra artículos únicos y puja antes de que se acabe el tiempo.' ?>
            </p>
        </div>
    </section>

    <main class="container-fluid px-4 px-lg-5 py-5 flex-grow-1">
        <div class="container catalogo-container">

            <!-- filtros -->
            <form action="<?= $base_url ?>views/catalogo.php" method="GET" class="catalogo-filtros row g-3 align-items-end" role="search">
                <?php if ($filtros['estado'] !== ''): ?>
                    <input type="hidden" name="estado" value="<?= e($filtros['estado']) ?>">
                <?php endif; ?>
                <div class="col-12 col-lg-5">
                    <label for="q" class="form-label small text-second-ppi mb-1">Buscar</label>
                    <input type="search" class="form-control" id="q" name="q" maxlength="80" placeholder="Producto o palabra clave" value="<?= e($filtros['q']) ?>">
                </div>
                <div class="col-6 col-lg-3">
                    <label for="categoria" class="form-label small text-second-ppi mb-1">Categoría</label>
                    <select class="form-select" id="categoria" name="categoria">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= e($cat['nombre']) ?>" <?= $cat['nombre'] === $filtros['categoria'] ? 'selected' : '' ?>><?= e($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <label for="orden" class="form-label small text-second-ppi mb-1">Ordenar por</label>
                    <select class="form-select" id="orden" name="orden">
                        <?php foreach ($ordenes as $valor => $texto): ?>
                            <option value="<?= e($valor) ?>" <?= $valor === $filtros['orden'] ? 'selected' : '' ?>><?= e($texto) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-lg-2 d-grid">
                    <button type="submit" class="btn btn-primary fw-semibold py-2"><i class="fas fa-search me-2 small"></i>Filtrar</button>
                </div>
            </form>

            <!-- categorias rapidas -->
            <nav class="catalogo-pills d-flex flex-wrap gap-2 my-4" aria-label="Categorías">
                <a href="<?= e(urlCatalogo($filtros, ['categoria' => '', 'pagina' => 1])) ?>" class="catalogo-pill <?= $filtros['categoria'] === '' ? 'activa' : '' ?>">Todas</a>
                <?php foreach ($categorias as $cat): ?>
                    <a href="<?= e(urlCatalogo($filtros, ['categoria' => $cat['nombre'], 'pagina' => 1])) ?>" class="catalogo-pill <?= $cat['nombre'] === $filtros['categoria'] ? 'activa' : '' ?>"><?= e($cat['nombre']) ?></a>
                <?php endforeach; ?>
            </nav>

            <!-- resultados -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <p class="mb-0 text-second-ppi" aria-live="polite">
                    <strong class="text-main-ppi"><?= $total ?></strong> <?= $total === 1 ? 'subasta activa' : 'subastas activas' ?>
                    <?php if ($filtros['estado'] === 'hoy'): ?>
                        <span class="catalogo-chip">Terminan hoy <a href="<?= e(urlCatalogo($filtros, ['estado' => '', 'pagina' => 1])) ?>" aria-label="Quitar filtro">&times;</a></span>
                    <?php endif; ?>
                    <?php if ($filtros['q'] !== ''): ?>
                        <span class="catalogo-chip">“<?= e($filtros['q']) ?>” <a href="<?= e(urlCatalogo($filtros, ['q' => '', 'pagina' => 1])) ?>" aria-label="Quitar búsqueda">&times;</a></span>
                    <?php endif; ?>
                </p>
                <?php if ($hayFiltros): ?>
                    <a href="<?= $base_url ?>views/catalogo.php" class="link-ppi d-inline p-0 small fw-bold">Limpiar filtros</a>
                <?php endif; ?>
            </div>

            <?php if (empty($catalogo['items'])): ?>
                <div class="catalogo-vacio text-center">
                    <i class="fas fa-gavel fs-1 text-second-ppi opacity-50 mb-3"></i>
                    <h2 class="fs-4 fw-bold text-main-ppi mb-2">No encontramos subastas</h2>
                    <p class="text-second-ppi mb-4">
                        <?= $hayFiltros ? 'Prueba con otra categoría o quita algunos filtros.' : 'Todavía no hay subastas activas. ¡Sé el primero en publicar!' ?>
                    </p>
                    <?php if ($hayFiltros): ?>
                        <a href="<?= $base_url ?>views/catalogo.php" class="btn btn-contorno-ppi px-4 py-2 fw-semibold">Ver todo el catálogo</a>
                    <?php else: ?>
                        <a href="<?= $base_url ?>views/subastas/registro-producto.php" class="btn btn-primary px-4 py-2 fw-semibold">Publicar producto</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4">
                    <?php foreach ($catalogo['items'] as $item): ?>
                        <div class="col">
                            <article class="card h-100 overflow-hidden catalogo-card">
                                <a href="<?= $base_url ?>views/subasta.php?id=<?= (int)$item['id'] ?>" class="text-decoration-none" tabindex="-1" aria-hidden="true">
                                    <img src="<?= $base_url . e($item['imagen']) ?>" class="foto-lote" alt="" loading="lazy" onerror="this.onerror=null; this.src='<?= $base_url ?>assets/img/news-1.jpg';">
                                </a>
                                <div class="p-3 d-flex flex-column flex-grow-1">
                                    <span class="small text-uppercase text-second-ppi"><?= e($item['categoria']) ?></span>
                                    <h2 class="fs-6 fw-bold text-main-ppi mt-1 catalogo-card-titulo"><?= e($item['titulo']) ?></h2>
                                    <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                        <span class="h5 mb-0 precio-ppi">$<?= e($item['precio']) ?></span>
                                        <span class="small tiempo-ppi" data-countdown="<?= e($item['fecha_final_iso']) ?>"><?= e($item['tiempo']) ?></span>
                                    </div>
                                    <div class="small text-second-ppi mt-1 mb-3"><?= $item['pujas'] ?> <?= $item['pujas'] === 1 ? 'puja' : 'pujas' ?></div>
                                    <a href="<?= $base_url ?>views/subasta.php?id=<?= (int)$item['id'] ?>" class="btn btn-contorno-ppi fw-bold">Ver subasta</a>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- paginacion -->
                <?php if ($catalogo['paginas'] > 1): ?>
                    <?php
                    $actual = $catalogo['pagina'];
                    $ultima = $catalogo['paginas'];
                    $desde = max(1, $actual - 2);
                    $hasta = min($ultima, $actual + 2);
                    ?>
                    <nav class="mt-5" aria-label="Páginas del catálogo">
                        <ul class="pagination catalogo-paginacion justify-content-center flex-wrap mb-0">
                            <li class="page-item <?= $actual <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= e(urlCatalogo($filtros, ['pagina' => $actual - 1])) ?>" aria-label="Anterior">&laquo;</a>
                            </li>
                            <?php for ($i = $desde; $i <= $hasta; $i++): ?>
                                <li class="page-item <?= $i === $actual ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= e(urlCatalogo($filtros, ['pagina' => $i])) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $actual >= $ultima ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= e(urlCatalogo($filtros, ['pagina' => $actual + 1])) ?>" aria-label="Siguiente">&raquo;</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </main>

    <?php include __DIR__ . "/Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $base_url ?>assets/js/temporizador-subastas.js" defer></script>
</body>

</html>

