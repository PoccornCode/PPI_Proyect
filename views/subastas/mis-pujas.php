<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../dao/MisPujasDAO.php';

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['redirect_after_login'] = BASE_URL . 'views/subastas/mis-pujas.php';
    $_SESSION['flash_toast'] = [
        'tipo' => 'warning',
        'titulo' => 'Acceso restringido',
        'mensaje' => 'Debes iniciar sesión para ver tus pujas.'
    ];
    header("Location: " . BASE_URL . "views/auth/login.php");
    exit();
}

if (!function_exists('e')) {
    function e($texto)
    {
        return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
    }
}

$id_usuario = (int)$_SESSION['id_usuario'];
$titulo = "Mis Pujas";
$pagina = "mis-pujas";

$dao = new MisPujasDAO();
$pujas = $dao->obtenerPorUsuario($id_usuario);

// texto y color de cada resultado
$resultados = [
    'ganando'   => ['Vas ganando', 'ok'],
    'superada'  => ['Superada', 'alerta'],
    'ganada'    => ['Ganada', 'ok'],
    'perdida'   => ['No ganaste', 'neutro'],
    'cancelada' => ['Cancelada', 'neutro'],
];

// cuantas hay de cada resultado para las pesnanas
$conteo = array_count_values(array_column($pujas, 'resultado'));
$filtro = $_GET['estado'] ?? '';
if (!isset($resultados[$filtro])) {
    $filtro = '';
}
$mostrar = $filtro === '' ? $pujas : array_filter($pujas, function ($p) use ($filtro) {
    return $p['resultado'] === $filtro;
});
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> | Gavel.net</title>
    <?php include dirname(__DIR__, 2) . '/includes/meta_tags.php'; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@100;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/main.css?v=40" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/mis-pujas.css?v=4" rel="stylesheet">
</head>

<body class="bg-dark-ppi d-flex flex-column min-vh-100">

    <?php include __DIR__ . "/../NavBar/nav.php"; ?>

    <main class="flex-grow-1 py-5">
        <div class="container py-2">

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4 pb-2 border-bottom border-dark">
                <div>
                    <h1 class="fs-2 text-main-ppi fw-bold mb-1">Mis Pujas</h1>
                    <p class="text-second-ppi small mb-0">Sigue las subastas en las que has ofertado y cómo te va en cada una.</p>
                </div>
                <a href="<?= BASE_URL ?>views/catalogo.php" class="btn btn-outline-publish btn-sm px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                    <i class="fas fa-search"></i> Explorar subastas
                </a>
            </div>

            <?php if (empty($pujas)): ?>
                <div class="empty-state-box text-center">
                    <div class="mb-3">
                        <i class="fas fa-gavel fs-1 text-second-ppi opacity-50"></i>
                    </div>
                    <h2 class="fs-4 text-main-ppi fw-bold mb-2">Aún no has hecho ninguna puja</h2>
                    <p class="text-second-ppi small mb-4 mx-auto mis-pujas-vacio-texto">
                        Cuando ofertes en una subasta, aparecerá aquí para que veas si vas ganando o si alguien superó tu puja.
                    </p>
                    <a href="<?= BASE_URL ?>views/catalogo.php" class="btn btn-primary px-4 py-2 fw-semibold">Ver subastas activas</a>
                </div>
            <?php else: ?>

                <!-- pestanas por resultado -->
                <nav class="mis-pujas-tabs d-flex flex-wrap align-items-center gap-2 mb-4" aria-label="Filtrar pujas">
                    <a href="<?= BASE_URL ?>views/subastas/mis-pujas.php" class="mis-pujas-tab <?= $filtro === '' ? 'activa' : '' ?>">Todas <span data-tab-count="todas"><?= count($pujas) ?></span></a>
                    <?php foreach ($resultados as $clave => $info): ?>
                        <?php if (!empty($conteo[$clave])): ?>
                            <a href="<?= BASE_URL ?>views/subastas/mis-pujas.php?estado=<?= e($clave) ?>" class="mis-pujas-tab <?= $filtro === $clave ? 'activa' : '' ?>"><?= e($info[0]) ?> <span data-tab-count="<?= e($clave) ?>"><?= $conteo[$clave] ?></span></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <a href="<?= BASE_URL ?>views/perfil.php" class="mis-pujas-tab ms-auto d-inline-flex align-items-center gap-2">
                        <i class="fas fa-arrow-left small"></i> Volver al perfil
                    </a>
                </nav>

                <?php if (empty($mostrar)): ?>
                    <p class="text-second-ppi">No tienes pujas con ese resultado.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3" data-mis-pujas-url="<?= BASE_URL ?>controllers/mis_pujas_controller.php">
                        <?php foreach ($mostrar as $p): ?>
                            <?php
                            [$textoResultado, $colorResultado] = $resultados[$p['resultado']];
                            $imgSrc = preg_match('#^https?://#i', $p['imagen']) ? $p['imagen'] : BASE_URL . $p['imagen'];
                            ?>
                            <article class="mis-pujas-item" data-subasta-id="<?= (int)$p['id'] ?>">
                                <a href="<?= BASE_URL ?>views/subasta.php?id=<?= (int)$p['id'] ?>" class="mis-pujas-foto" tabindex="-1" aria-hidden="true">
                                    <img src="<?= e($imgSrc) ?>" alt="" loading="lazy" onerror="this.onerror=null; this.src='<?= BASE_URL ?>assets/img/news-1.jpg';">
                                </a>

                                <div class="mis-pujas-info">
                                    <span class="small text-uppercase text-second-ppi"><?= e($p['categoria']) ?></span>
                                    <h2 class="fs-6 fw-bold mb-1">
                                        <a href="<?= BASE_URL ?>views/subasta.php?id=<?= (int)$p['id'] ?>" class="text-main-ppi text-decoration-none"><?= e($p['titulo']) ?></a>
                                    </h2>
                                    <span class="small text-second-ppi" data-meta-pujas>
                                        <?= $p['mis_pujas'] ?> <?= $p['mis_pujas'] === 1 ? 'puja tuya' : 'pujas tuyas' ?>
                                        <?php if ($p['ultima'] !== ''): ?> &middot; última el <?= e($p['ultima']) ?><?php endif; ?>
                                    </span>
                                </div>

                                <div class="mis-pujas-montos">
                                    <div>
                                        <span class="d-block small text-second-ppi">Tu puja</span>
                                        <span class="fw-bold text-main-ppi" data-mi-puja>$<?= e($p['mi_puja']) ?></span>
                                    </div>
                                    <div>
                                        <span class="d-block small text-second-ppi" data-label-actual><?= $p['abierta'] ? 'Puja actual' : 'Puja final' ?></span>
                                        <span class="fw-bold precio-ppi" data-puja-actual>$<?= e($p['puja_actual']) ?></span>
                                    </div>
                                </div>

                                <div class="mis-pujas-estado">
                                    <span class="mis-pujas-chip mis-pujas-chip-<?= e($colorResultado) ?>" data-chip-resultado><?= e($textoResultado) ?></span>
                                    <?php if ($p['abierta']): ?>
                                        <span class="small tiempo-ppi" data-estado-tiempo data-countdown="<?= e($p['fecha_final_iso']) ?>" data-countdown-prefix="Termina en "><?= e($p['tiempo']) ?></span>
                                    <?php else: ?>
                                        <span class="small text-second-ppi" data-estado-tiempo>Subasta cerrada</span>
                                    <?php endif; ?>
                                </div>

                                <div class="mis-pujas-accion d-flex flex-wrap gap-2">
                                    <?php if ($p['resultado'] === 'ganada'): ?>
                                        <a href="<?= BASE_URL ?>views/chat.php?subasta=<?= (int)$p['id'] ?>" class="btn btn-primary btn-sm fw-bold px-3">
                                            <i class="fas fa-comment-dots me-1"></i> Ir al chat
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>views/subasta.php?id=<?= (int)$p['id'] ?>" class="btn btn-contorno-ppi btn-sm fw-bold px-3" data-btn-accion>
                                        <?= $p['resultado'] === 'superada' ? 'Volver a pujar' : 'Ver subasta' ?>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

        </div>
    </main>

    <?php include __DIR__ . "/../Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/temporizador-subastas.js" defer></script>
    <script src="<?= BASE_URL ?>assets/js/mis-pujas.js?v=1" defer></script>
</body>

</html>
