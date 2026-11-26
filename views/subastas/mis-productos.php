<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../dao/ProductoDAO.php';

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['redirect_after_login'] = BASE_URL . 'views/subastas/mis-productos.php';
    $_SESSION['flash_toast'] = [
        'tipo' => 'warning',
        'titulo' => 'Acceso restringido',
        'mensaje' => 'Debes iniciar sesión para consultar tus productos.'
    ];
    header("Location: " . BASE_URL . "views/auth/login.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$titulo = "Mis Productos";
$pagina = "mis-productos";

$productos = obtenerProductosPorUsuario($conexion, $id_usuario);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> | Gavel.net</title>
    <?php include dirname(__DIR__, 2) . '/includes/meta_tags.php'; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@100;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/main.css?v=14" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/forms.css?v=14" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/productos.css?v=5" rel="stylesheet">
</head>

<body class="bg-dark-ppi d-flex flex-column min-vh-100">

    <?php include __DIR__ . "/../NavBar/nav.php"; ?>

    <main class="flex-grow-1 py-5">
        <div class="container py-2">

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4 pb-2 border-bottom border-dark">
                <div>
                    <h2 class="text-main-ppi fw-bold mb-1">Mis Productos</h2>
                    <p class="text-second-ppi small mb-0">Gestiona tus productos registrados y publícalos en subasta.</p>
                </div>
                <a href="<?= BASE_URL ?>views/subastas/registro-producto.php" class="btn btn-outline-publish btn-sm px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                    <i class="fas fa-plus"></i> Registrar Nuevo Producto
                </a>
            </div>

            <?php if (empty($productos)): ?>
                <div class="empty-state-box">
                    <div class="mb-3">
                        <i class="fas fa-box-open fs-1 text-second-ppi opacity-50"></i>
                    </div>
                    <h4 class="text-main-ppi fw-bold mb-2">Aún no tienes productos registrados</h4>
                    <p class="text-second-ppi small mb-4 mx-auto" style="max-width: 480px;">
                        Comienza publicando los detalles de tu producto (fotografías, descripción y condición). Luego podrás programar su subasta y enviarla a revisión.
                    </p>
                    <a href="<?= BASE_URL ?>views/subastas/registro-producto.php" class="btn btn-primary px-4 py-2 fw-semibold">
                        <i class="fas fa-plus me-1"></i> Publicar mi primer producto
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($productos as $item): ?>
                        <?php
                        $imgRuta = !empty($item['imagen_principal'])
                            ? BASE_URL . 'uploads/productos/' . htmlspecialchars($item['imagen_principal'])
                            : BASE_URL . 'assets/img/hero-img.jpg';

                        $estadoSubasta = $item['estado_subasta'] ?? null;
                        ?>
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="producto-card">
                                <div class="producto-img-wrapper">
                                    <img src="<?= $imgRuta ?>" alt="<?= htmlspecialchars($item['nombre']) ?>" onerror="this.onerror=null; this.src='<?= BASE_URL ?>assets/img/hero-img.jpg';">
                                    <?php /* if (!empty($item['categoria'])): ?>
                                        <span class="producto-badge-cat">
                                            <i class="fas fa-tag me-1"></i><?= htmlspecialchars($item['categoria']) ?>
                                        </span>
                                    <?php endif; */ ?>
                                </div>

                                <div class="p-3 d-flex flex-column flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                        <span class="badge-condicion">
                                            <?= htmlspecialchars($item['condicion']) ?>
                                        </span>

                                        <?php if (empty($estadoSubasta)): ?>
                                            <span class="status-badge status-none">
                                                <span class="status-dot"></span> Sin subasta
                                            </span>
                                        <?php elseif (strtolower($estadoSubasta) === 'pendiente' || strtolower($estadoSubasta) === 'en revision'): ?>
                                            <span class="status-badge status-revision">
                                                <span class="status-dot"></span> En revisión
                                            </span>
                                        <?php elseif (strtolower($estadoSubasta) === 'activa' || strtolower($estadoSubasta) === 'abierta'): ?>
                                            <span class="status-badge status-activa">
                                                <span class="status-dot"></span> En subasta
                                            </span>
                                        <?php elseif (strtolower($estadoSubasta) === 'sin_ofertas'): ?>
                                            <span class="status-badge status-finalizada">
                                                <span class="status-dot"></span> Sin ofertas
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-finalizada">
                                                <span class="status-dot"></span> <?= htmlspecialchars(ucfirst($estadoSubasta)) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <h5 class="text-main-ppi fw-bold mb-2 text-truncate" title="<?= htmlspecialchars($item['nombre']) ?>">
                                        <?= htmlspecialchars($item['nombre']) ?>
                                    </h5>

                                    <p class="text-second-ppi small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?= htmlspecialchars($item['descripcion']) ?>
                                    </p>

                                    <div class="d-flex gap-2 mt-2">
                                        <a href="<?= BASE_URL ?>views/subastas/registro-producto.php?id=<?= (int)$item['id_producto'] ?>" class="btn btn-modificar-producto btn-sm flex-grow-1">
                                            <i class="fas fa-edit"></i> Modificar
                                        </a>

                                        <form action="<?= BASE_URL ?>controllers/ControllerProducto.php?id=<?= (int)$item['id_producto'] ?>" method="POST" class="flex-grow-1 form-eliminar">
                                            <input type="hidden" name="opcion" value="eliminar">
                                            <button type="submit" class="btn btn-eliminar-producto btn-sm w-100">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    </div>

                                    <div class="d-flex gap-2 mt-2">
                                        <?php if (empty($estadoSubasta)): ?>
                                            <a href="<?= BASE_URL ?>views/subastas/crear-subastas.php?id=<?= (int)$item['id_producto'] ?>" class="btn btn-primary btn-sm flex-grow-1">
                                                <i class="fas fa-gavel"></i> Subastar
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <?php include __DIR__ . "/../Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/toast-ppi.js?v=2"></script>
    <script src="<?= BASE_URL ?>assets/js/mis-productos.js?v=1"></script>

    <?php if (isset($_SESSION['flash_toast'])): ?>
        <?php
        $flash = $_SESSION['flash_toast'];
        unset($_SESSION['flash_toast']);
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                mostrarToast(
                    <?= json_encode($flash['tipo'] ?? 'success') ?>,
                    <?= json_encode($flash['titulo'] ?? '') ?>,
                    <?= json_encode($flash['mensaje'] ?? '') ?>,
                    5000
                );
            });
        </script>
    <?php endif; ?>

</body>

</html>
