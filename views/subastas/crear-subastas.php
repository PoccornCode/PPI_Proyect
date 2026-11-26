<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../dao/ProductoDAO.php';

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['redirect_after_login'] = BASE_URL . 'views/subastas/crear-subasta.php'
        . (isset($_GET['id']) ? '?id=' . (int)$_GET['id'] : '');
    $_SESSION['flash_toast'] = [
        'tipo' => 'warning',
        'titulo' => 'Acceso restringido',
        'mensaje' => 'Debes iniciar sesión para crear una subasta.'
    ];
    header("Location: " . BASE_URL . "views/auth/login.php");
    exit();
}

$pagina = "crear-subasta";
$titulo = "Crear Subasta";
$id_usuario = (int)$_SESSION['id_usuario'];
$id_producto = (int)($_GET['id'] ?? 0);
$producto = null;

$producto= obtenerProductoPorId($conexion, $id_producto, $id_usuario);

$imgRuta = !empty($producto['imagen_principal'])
    ? BASE_URL . 'uploads/productos/' . htmlspecialchars($producto['imagen_principal'])
    : BASE_URL . 'assets/img/hero-img.jpg';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> | Gavel.net</title>
    <?php include dirname(__DIR__, 2) . '/includes/meta_tags.php'; ?>

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@100;600;800&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Customized Bootstrap & Project Stylesheets -->
    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/main.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/forms.css?v=8" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/productos.css?v=3" rel="stylesheet">
</head>

<body class="bg-dark-ppi">

    <?php include __DIR__ . "/../NavBar/nav.php"; ?>

    <main class="py-5">
        <div class="container-fluid px-4 px-lg-5 py-2">
            <div class="row justify-content-center">
                <div class="col-lg-9 col-xl-7">

                    <h3 class="text-main-ppi fw-bold mb-4"><?= htmlspecialchars($titulo) ?></h3>

                    <?php if (!$producto): ?>
                        <div class="card card-gavel p-4 mb-4 shadow text-center">
                            <p class="text-main-ppi fw-bold mb-3">El producto no existe o no te pertenece.</p>
                            <div>
                                <a href="<?= BASE_URL ?>views/subastas/mis-productos.php" class="btn btn-secondary px-4 py-2">Volver a mis productos</a>
                            </div>
                        </div>

                    <?php elseif (!empty($producto['id_subasta'])): ?>
                        <div class="card card-gavel p-4 mb-4 shadow text-center">
                            <p class="text-main-ppi fw-bold mb-3">Este producto ya tiene una subasta.</p>
                            <div>
                                <a href="<?= BASE_URL ?>views/subastas/mis-productos.php" class="btn btn-secondary px-4 py-2">Volver a mis productos</a>
                            </div>
                        </div>

                    <?php else: ?>
                        <form id="formCrearSubasta" action="<?= BASE_URL ?>controllers/subasta_controller.php" method="POST" autocomplete="off">
                            <input type="hidden" name="id_producto" value="<?= (int)$id_producto ?>">
                            <input type="hidden" name="opcion" value="insertar">
                            <div class="card card-gavel p-4 mb-4 shadow">
                                <div class="d-flex gap-3 align-items-center pb-4 mb-4 border-bottom border-dark">
                                    <img src="<?= $imgRuta ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" width="90" height="90" class="rounded" style="object-fit: cover;">
                                    <div>
                                        <p class="text-second-ppi small mb-1">Vas a subastar</p>
                                        <h5 class="text-main-ppi fw-bold mb-1"><?= htmlspecialchars($producto['nombre']) ?></h5>
                                        <p class="text-second-ppi small mb-0">
                                            <?= htmlspecialchars($producto['categoria'] ?? '') ?> · <?= htmlspecialchars($producto['condicion']) ?>
                                        </p>
                                    </div>
                                </div>
                                <h5 class="section-title">Precios</h5>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="precio_inicial" class="form-label text-main-ppi fw-bold small">Precio inicial ($)</label>
                                        <input type="text" class="form-control" id="precio_inicial" name="precio_inicial" inputmode="numeric" value="0.00" required>
                                        <div class="form-text text-second-ppi">La primera puja debe ser al menos este monto.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="incremento_minimo" class="form-label text-main-ppi fw-bold small">Incremento mínimo ($)</label>
                                        <input type="text" class="form-control" id="incremento_minimo" name="incremento_minimo" inputmode="numeric" value="0.00" required>
                                        <div class="form-text text-second-ppi">Lo mínimo que debe subir cada nueva puja.</div>
                                    </div>
                                </div>
                                <h5 class="section-title">Tiempo de duración</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="dias" class="form-label text-main-ppi fw-bold small">Duración en días</label>
                                        <input type="number" class="form-control" id="dias" name="dias" min="1" max="30" step="1" placeholder="0" required>
                                        <div class="form-text text-second-ppi">La subasta durará los días que se establezcan.</div>
                                    </div>
                                </div>

                                <p class="text-second-ppi small mt-4 mb-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Tu subasta quedará pendiente hasta que un moderador la apruebe.
                                </p>
                            </div>

                            <div class="d-flex justify-content-end gap-3 pb-4">
                                <a href="<?= BASE_URL ?>views/subastas/mis-productos.php" class="btn btn-secondary px-4 py-2">Cancelar</a>
                                <button type="submit" id="btnCrearSubasta" class="btn btn-primary px-5 py-2 fw-bold">Crear subasta</button>
                            </div>
                        </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . "/../Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const RUTA_MIS_PRODUCTOS = "<?= BASE_URL ?>views/subastas/mis-productos.php";
    </script>
    <script src="<?= BASE_URL ?>assets/js/toast-ppi.js?v=2"></script>
    <script src="<?= BASE_URL ?>assets/js/crear_subastas.js?v=3"></script>
</body>

</html>