
<?php
// ============================================================
// catalogo.php - Catálogo de subastas Gavel.net
// ============================================================

// Si $base_url ya se define en otro archivo (config), quita esta línea.
$base_url = $base_url ?? '/';

// ------------------------------------------------------------
// Helpers
// ------------------------------------------------------------
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function tiempoRestante(string $fechaFin): string
{
    $seg = strtotime($fechaFin) - time();
    if ($seg <= 0) {
        return 'Finalizada';
    }
    $d = intdiv($seg, 86400);
    $h = intdiv($seg % 86400, 3600);
    $m = intdiv($seg % 3600, 60);
    if ($d > 0) {
        return "{$d}d {$h}h";
    }
    return sprintf('%02d:%02d', $h, $m);
}

function urlConParams(array $cambios): string
{
    return '?' . http_build_query(array_merge($_GET, $cambios));
}

// ------------------------------------------------------------
// Datos
// Reemplaza esta función por tu consulta real a la base de datos.
// Debe devolver un array de filas con estas claves:
//   id, titulo, categoria, condicion, precio_actual, num_pujas,
//   estado (none|revision|activa|finalizada), imagen, fecha_fin
//
// Ejemplo con PDO:
//   $stmt = $pdo->prepare("SELECT ... FROM productos p JOIN subastas s ...");
//   $stmt->execute();
//   return $stmt->fetchAll(PDO::FETCH_ASSOC);
// ------------------------------------------------------------
function obtenerSubastas(): array
{
    return [
        ['id' => 1, 'titulo' => 'Reloj de bolsillo antiguo',       'categoria' => 'Coleccionables', 'condicion' => 'Usado',  'precio_actual' => 180.00, 'num_pujas' => 12, 'estado' => 'activa',     'imagen' => '', 'fecha_fin' => date('Y-m-d H:i:s', strtotime('+2 hours'))],
        ['id' => 2, 'titulo' => 'Laptop reacondicionada 14"',      'categoria' => 'Tecnología',     'condicion' => 'Usado',  'precio_actual' => 320.00, 'num_pujas' => 8,  'estado' => 'activa',     'imagen' => '', 'fecha_fin' => date('Y-m-d H:i:s', strtotime('+1 day 4 hours'))],
        ['id' => 3, 'titulo' => 'Óleo sobre lienzo, paisaje',      'categoria' => 'Arte',           'condicion' => 'Nuevo',  'precio_actual' => 450.00, 'num_pujas' => 21, 'estado' => 'activa',     'imagen' => '', 'fecha_fin' => date('Y-m-d H:i:s', strtotime('+3 days'))],
        ['id' => 4, 'titulo' => 'Anillo de plata 925',             'categoria' => 'Joyería',        'condicion' => 'Nuevo',  'precio_actual' => 95.00,  'num_pujas' => 5,  'estado' => 'revision',   'imagen' => '', 'fecha_fin' => date('Y-m-d H:i:s', strtotime('+2 days'))],
        ['id' => 5, 'titulo' => 'Bicicleta de montaña',            'categoria' => 'Deportes',       'condicion' => 'Usado',  'precio_actual' => 260.00, 'num_pujas' => 9,  'estado' => 'activa',     'imagen' => '', 'fecha_fin' => date('Y-m-d H:i:s', strtotime('+3 days 2 hours'))],
        ['id' => 6, 'titulo' => 'Cámara análoga 35 mm',            'categoria' => 'Tecnología',     'condicion' => 'Usado',  'precio_actual' => 140.00, 'num_pujas' => 14, 'estado' => 'finalizada', 'imagen' => '', 'fecha_fin' => date('Y-m-d H:i:s', strtotime('-1 day'))],
    ];
}

// ------------------------------------------------------------
// Filtros (GET)
// ------------------------------------------------------------
$estadosLabel = [
    'none'       => 'Sin subasta',
    'revision'   => 'En revisión',
    'activa'     => 'Subasta activa',
    'finalizada' => 'Finalizada',
];
$ordenes = [
    'finalizan' => 'Finalizan pronto',
    'mayor'     => 'Mayor puja',
    'pujas'     => 'Más pujas',
];

$q      = trim($_GET['q'] ?? '');
$cat    = $_GET['categoria'] ?? '';
$estado = $_GET['estado'] ?? '';
$pmin   = ($_GET['pmin'] ?? '') !== '' ? (float) $_GET['pmin'] : null;
$pmax   = ($_GET['pmax'] ?? '') !== '' ? (float) $_GET['pmax'] : null;
$orden  = array_key_exists($_GET['orden'] ?? '', $ordenes) ? $_GET['orden'] : 'finalizan';

$todas      = obtenerSubastas();
$categorias = array_values(array_unique(array_column($todas, 'categoria')));
sort($categorias);

// Si usas SQL, mueve este filtrado a la consulta con parámetros preparados.
$items = array_filter($todas, function ($it) use ($q, $cat, $estado, $pmin, $pmax) {
    if ($q !== '' && mb_stripos($it['titulo'] . ' ' . $it['categoria'], $q) === false) return false;
    if ($cat !== '' && $it['categoria'] !== $cat) return false;
    if ($estado !== '' && $it['estado'] !== $estado) return false;
    if ($pmin !== null && $it['precio_actual'] < $pmin) return false;
    if ($pmax !== null && $it['precio_actual'] > $pmax) return false;
    return true;
});

usort($items, function ($a, $b) use ($orden) {
    if ($orden === 'mayor') return $b['precio_actual'] <=> $a['precio_actual'];
    if ($orden === 'pujas') return $b['num_pujas'] <=> $a['num_pujas'];
    return strtotime($a['fecha_fin']) <=> strtotime($b['fecha_fin']);
});

// ------------------------------------------------------------
// Paginación
// ------------------------------------------------------------
$porPagina = 6;
$total     = count($items);
$paginas   = max(1, (int) ceil($total / $porPagina));
$pagina    = min(max(1, (int) ($_GET['pagina'] ?? 1)), $paginas);
$items     = array_slice($items, ($pagina - 1) * $porPagina, $porPagina);
?>
<!DOCTYPE html>
<html lang="es" style="min-height:100%">

<head>
    <meta charset="utf-8">
    <title>Catálogo de subastas - Gavel.net</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@100;600;800&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body class="bg-dark-ppi" style="display: flex; flex-direction: column;">

    <!-- Spinner Start -->
    <div id="spinner"
        class="show w-100 vh-100 bg-white position-fixed translate-middle top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary" role="status"></div>
    </div>
    <!-- Spinner End -->

    <!-- Navbar start -->
    <?php include "forms/NavBar/nav.php"; ?>
    <!-- Navbar End -->

    <main style="flex:1;">

        <!-- Encabezado -->
        <section class="py-5">
            <div class="container">
                <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
                    <div>
                        <div class="fw-bold mb-2" style="font-size:.75rem; letter-spacing:2px; text-transform:uppercase; color:#869c30;">
                            Catálogo de subastas
                        </div>
                        <h1 class="text-main-ppi fw-bold mb-2">
                            Encuentra tu próxima <span style="color:#94aa34;">puja</span>
                        </h1>
                        <p class="text-second-ppi mb-0">
                            Explora las subastas activas, filtra por categoría y puja antes de que termine el tiempo.
                        </p>
                    </div>
                    <!-- Ajusta la ruta a tu vista de registro de producto -->
                    <a href="<?= e($base_url) ?>views/productos/registro.php" class="btn btn-crear-subasta">
                        <i class="fa fa-plus me-2"></i>Publicar producto
                    </a>
                </div>
            </div>
        </section>

        <section class="pb-5">
            <div class="container">
                <div class="row g-4">

                    <!-- Filtros -->
                    <aside class="col-12 col-lg-3">
                        <form method="get" class="card-gavel p-4">
                            <h2 class="section-title">Filtros</h2>

                            <div class="mb-3">
                                <label for="q" class="text-main-ppi mb-2 fw-bold">Buscar</label>
                                <input type="text" id="q" name="q" class="form-control"
                                    placeholder="Producto o categoría" value="<?= e($q) ?>">
                            </div>

                            <div class="mb-3">
                                <label for="categoria" class="text-main-ppi mb-2 fw-bold">Categoría</label>
                                <select id="categoria" name="categoria" class="form-select select-gavel">
                                    <option value="">Todas</option>
                                    <?php foreach ($categorias as $c): ?>
                                        <option value="<?= e($c) ?>" <?= $cat === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="estado" class="text-main-ppi mb-2 fw-bold">Estado</label>
                                <select id="estado" name="estado" class="form-select select-gavel">
                                    <option value="">Todos</option>
                                    <?php foreach ($estadosLabel as $clave => $label): ?>
                                        <option value="<?= e($clave) ?>" <?= $estado === $clave ? 'selected' : '' ?>><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <span class="text-main-ppi mb-2 fw-bold d-block">Rango de precio</span>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label for="pmin" class="visually-hidden">Precio mínimo</label>
                                        <div class="input-group">
                                            <span class="input-group-text addon-gavel">$</span>
                                            <input type="number" min="0" step="0.01" id="pmin" name="pmin"
                                                class="form-control" placeholder="Mín"
                                                value="<?= $pmin !== null ? e($pmin) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label for="pmax" class="visually-hidden">Precio máximo</label>
                                        <div class="input-group">
                                            <span class="input-group-text addon-gavel">$</span>
                                            <input type="number" min="0" step="0.01" id="pmax" name="pmax"
                                                class="form-control" placeholder="Máx"
                                                value="<?= $pmax !== null ? e($pmax) : '' ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="orden" value="<?= e($orden) ?>">

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary fw-bold">Aplicar filtros</button>
                                <a href="catalogo.php" class="btn btn-secondary fw-bold">Limpiar filtros</a>
                            </div>
                        </form>
                    </aside>

                    <!-- Resultados -->
                    <section class="col-12 col-lg-9" aria-label="Resultados">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                            <h2 class="text-main-ppi fw-bold mb-0 h4">
                                Subastas
                                <small class="text-second-ppi fw-normal fs-6">(<?= $total ?> resultado<?= $total === 1 ? '' : 's' ?>)</small>
                            </h2>

                            <form method="get" class="d-flex align-items-center gap-2">
                                <?php foreach (['q', 'categoria', 'estado', 'pmin', 'pmax'] as $campo): ?>
                                    <?php if (($_GET[$campo] ?? '') !== ''): ?>
                                        <input type="hidden" name="<?= $campo ?>" value="<?= e($_GET[$campo]) ?>">
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <label for="orden" class="text-second-ppi">Ordenar por</label>
                                <select id="orden" name="orden" class="form-select select-gavel w-auto" onchange="this.form.submit()">
                                    <?php foreach ($ordenes as $clave => $label): ?>
                                        <option value="<?= e($clave) ?>" <?= $orden === $clave ? 'selected' : '' ?>><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>

                        <?php if (empty($items)): ?>
                            <div class="empty-state-box">
                                <i class="bi bi-search fs-1 text-second-ppi"></i>
                                <h3 class="text-main-ppi mt-3">No encontramos subastas</h3>
                                <p class="text-second-ppi">Prueba con otros filtros o limpia la búsqueda.</p>
                                <a href="catalogo.php" class="btn btn-crear-subasta">Limpiar filtros</a>
                            </div>
                        <?php else: ?>
                            <div class="row g-4">
                                <?php foreach ($items as $it): ?>
                                    <?php
                                    $claseEstado = 'status-' . e($it['estado']);
                                    $labelEstado = $estadosLabel[$it['estado']] ?? $estadosLabel['none'];
                                    $esActiva    = $it['estado'] === 'activa';
                                    ?>
                                    <div class="col-12 col-md-6 col-xl-4">
                                        <article class="producto-card">
                                            <div class="producto-img-wrapper d-flex align-items-center justify-content-center">
                                                <?php if (!empty($it['imagen'])): ?>
                                                    <img src="<?= e($base_url . $it['imagen']) ?>" alt="<?= e($it['titulo']) ?>">
                                                <?php else: ?>
                                                    <i class="bi bi-image" style="font-size:2.5rem; color:#3f4538;"></i>
                                                <?php endif; ?>
                                                <span class="producto-badge-cat"><?= e($it['categoria']) ?></span>
                                            </div>

                                            <div class="p-3 d-flex flex-column flex-grow-1">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="status-badge <?= $claseEstado ?>">
                                                        <span class="status-dot"></span><?= e($labelEstado) ?>
                                                    </span>
                                                    <span class="badge-condicion"><?= e($it['condicion']) ?></span>
                                                </div>

                                                <h3 class="h6 text-main-ppi fw-bold mb-3"><?= e($it['titulo']) ?></h3>

                                                <div class="d-flex align-items-end justify-content-between mb-3">
                                                    <div>
                                                        <div class="text-second-ppi small"><?= $esActiva ? 'Puja actual' : 'Precio' ?></div>
                                                        <div class="fw-bold" style="font-family:'Raleway',sans-serif; font-size:1.5rem; color:#94aa34;">
                                                            $ <?= number_format($it['precio_actual'], 2) ?>
                                                        </div>
                                                    </div>
                                                    <div class="text-second-ppi small text-end">
                                                        <div><i class="fas fa-gavel me-1"></i><?= (int) $it['num_pujas'] ?> pujas</div>
                                                        <?php if ($esActiva): ?>
                                                            <div class="mt-1"><i class="far fa-clock me-1"></i><?= e(tiempoRestante($it['fecha_fin'])) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="d-flex gap-2 mt-auto">
                                                    <?php if ($esActiva): ?>
                                                        <a href="<?= e($base_url) ?>views/subastas/detalle.php?id=<?= (int) $it['id'] ?>#pujar"
                                                            class="btn btn-primary fw-bold flex-grow-1">Pujar ahora</a>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-primary fw-bold flex-grow-1" disabled>Pujar ahora</button>
                                                    <?php endif; ?>
                                                    <a href="<?= e($base_url) ?>views/subastas/detalle.php?id=<?= (int) $it['id'] ?>"
                                                        class="btn btn-secondary fw-bold">Ver detalle</a>
                                                </div>
                                            </div>
                                        </article>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($paginas > 1): ?>
                                <nav aria-label="Paginación" class="d-flex flex-wrap justify-content-center gap-2 mt-5">
                                    <?php if ($pagina > 1): ?>
                                        <a href="<?= e(urlConParams(['pagina' => $pagina - 1])) ?>" class="btn btn-secondary" aria-label="Página anterior">
                                            <i class="fa fa-chevron-left"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $paginas; $i++): ?>
                                        <a href="<?= e(urlConParams(['pagina' => $i])) ?>"
                                            class="btn <?= $i === $pagina ? 'btn-primary' : 'btn-secondary' ?> fw-bold"
                                            <?= $i === $pagina ? 'aria-current="page"' : '' ?>><?= $i ?></a>
                                    <?php endfor; ?>

                                    <?php if ($pagina < $paginas): ?>
                                        <a href="<?= e(urlConParams(['pagina' => $pagina + 1])) ?>" class="btn btn-secondary" aria-label="Página siguiente">
                                            <i class="fa fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        </section>
    </main>

    <?php include "forms/Footer/footer.php"; ?>

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary border-2 border-white rounded-circle back-to-top"><i class="fa fa-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>