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
    <link href="<?= BASE_URL ?>assets/css/productos.css?v=11" rel="stylesheet">
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
                <div class="d-flex justify-content-end mb-3">
                    <select id="filtroEstado" class="form-select select-gavel w-auto" aria-label="Filtrar por estado de la subasta">
                        <option value="todos" selected>Todos los productos</option>
                        <option value="sin_subasta">Sin subasta</option>
                        <option value="pendiente">En revisión</option>
                        <option value="activa">En subasta</option>
                        <option value="rechazada">Rechazada</option>
                        <option value="finalizada">Finalizada</option>
                        <option value="sin_ofertas">Sin ofertas</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>

                <div class="row g-4">
                    <?php foreach ($productos as $item): ?>
                        <?php
                        $imgRuta = !empty($item['imagen_principal'])
                            ? BASE_URL . 'uploads/productos/' . htmlspecialchars($item['imagen_principal'])
                            : BASE_URL . 'assets/img/hero-img.jpg';

                        // Normaliza el estado de la subasta para decidir qué badge y botones mostrar en la tarjeta
                        $estadoSubasta = $item['estado_subasta'] ?? null;
                        $estadoNorm = $estadoSubasta !== null ? strtolower(trim((string)$estadoSubasta)) : '';
                        // Datos de la subasta actual (si existen en la consulta del DAO)
                        $idSubasta = (int)($item['id_subasta'] ?? 0);
                        $totalPujas = (int)($item['total_pujas'] ?? 0);
                        $motivoRechazo = (string)($item['motivo_rechazo'] ?? '');
                        // Texto de respaldo mientras el DAO aún no devuelve s.motivo_rechazo
                        if ($estadoNorm === 'rechazada' && $motivoRechazo === '') {
                            $motivoRechazo = 'Aquí va el motivo real de rechazo. En dao/ProductoDAO.php, agrega s.motivo_rechazo (de la última subasta del producto) para mostrar el texto guardado por el moderador.';
                        }
                        // Empaqueta los datos del producto y su subasta en JSON seguro para abrir el modal "Ver detalles"
                        $detalleJson = htmlspecialchars(json_encode([
                            'nombre' => (string)($item['nombre'] ?? ''),
                            'descripcion' => (string)($item['descripcion'] ?? ''),
                            'condicion' => (string)($item['condicion'] ?? ''),
                            'categoria' => (string)($item['categoria'] ?? 'Sin categoría'),
                            'imagen' => $imgRuta,
                            'estado_subasta' => $estadoNorm,
                            'precio_inicial' => $item['precio_inicial'] ?? null,
                            'fecha_inicial' => $item['fecha_inicial'] ?? null,
                            'fecha_final' => $item['fecha_final'] ?? null,
                            'motivo_rechazo' => $motivoRechazo,
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
                        ?>
                        <div class="col-md-6 col-lg-4 col-xl-3 item-producto" data-estado="<?= $estadoNorm === '' ? 'sin_subasta' : htmlspecialchars($estadoNorm) ?>">
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

                                        <?php if ($estadoNorm === ''): ?>
                                            <span class="status-badge status-none">
                                                <span class="status-dot"></span> Sin subasta
                                            </span>
                                        <?php elseif ($estadoNorm === 'pendiente' || $estadoNorm === 'en revision'): ?>
                                            <span class="status-badge status-revision">
                                                <span class="status-dot"></span> En revisión
                                            </span>
                                        <?php elseif ($estadoNorm === 'activa' || $estadoNorm === 'abierta'): ?>
                                            <span class="status-badge status-activa">
                                                <span class="status-dot"></span> En subasta
                                            </span>
                                        <?php elseif ($estadoNorm === 'sin_ofertas'): ?>
                                            <span class="status-badge status-finalizada">
                                                <span class="status-dot"></span> Sin ofertas
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-finalizada">
                                                <span class="status-dot"></span> <?= htmlspecialchars(ucfirst($estadoNorm)) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <h5 class="text-main-ppi fw-bold mb-2 text-truncate" title="<?= htmlspecialchars($item['nombre']) ?>">
                                        <?= htmlspecialchars($item['nombre']) ?>
                                    </h5>

                                    <p class="text-second-ppi small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?= htmlspecialchars($item['descripcion']) ?>
                                    </p>

                                    <?php // Acciones según el estado actual de la subasta ?>
                                    <div class="d-flex gap-2 mt-auto pt-2">
                                        <?php if ($estadoNorm === ''): // Sin subasta: subastar, ver detalles, modificar y eliminar ?>
                                            <button
                                                type="button"
                                                class="btn btn-subastar-producto btn-sm flex-grow-1 btn-abrir-subasta"
                                                data-id-producto="<?= (int)$item['id_producto'] ?>"
                                                data-nombre="<?= htmlspecialchars($item['nombre']) ?>"
                                                data-estado-anterior=""
                                                data-motivo-rechazo="">
                                                <i class="fas fa-gavel"></i> Subastar
                                            </button>
                                            <button type="button" class="btn btn-modificar-producto btn-sm btn-accion-icon btn-ver-detalle" data-id-producto="<?= (int)$item['id_producto'] ?>" data-detalle="<?= $detalleJson ?>" title="Ver detalles" aria-label="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="<?= BASE_URL ?>views/subastas/registro-producto.php?id=<?= (int)$item['id_producto'] ?>" class="btn btn-modificar-producto btn-sm btn-accion-icon" title="Modificar" aria-label="Modificar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="<?= BASE_URL ?>controllers/ControllerProducto.php?id=<?= (int)$item['id_producto'] ?>" method="POST" class="form-eliminar" data-nombre="<?= htmlspecialchars($item['nombre']) ?>">
                                                <input type="hidden" name="opcion" value="eliminar">
                                                <button type="submit" class="btn btn-eliminar-producto btn-sm btn-accion-icon" title="Eliminar" aria-label="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php elseif ($estadoNorm === 'pendiente' || $estadoNorm === 'en revision'): // En revisión: ver detalles y cancelar solicitud si no tiene pujas ?>
                                            <button type="button" class="btn btn-modificar-producto btn-sm <?= $totalPujas === 0 ? 'btn-accion-icon' : 'flex-grow-1' ?> btn-ver-detalle" data-id-producto="<?= (int)$item['id_producto'] ?>" data-detalle="<?= $detalleJson ?>" title="Ver detalles" aria-label="Ver detalles">
                                                <i class="fas fa-eye"></i><?= $totalPujas === 0 ? '' : ' Ver detalles' ?>
                                            </button>
                                            <?php if ($totalPujas === 0): ?>
                                                <button type="button" class="btn btn-eliminar-producto btn-sm flex-grow-1 btn-cancelar-solicitud" data-id-subasta="<?= $idSubasta ?>" data-url="<?= BASE_URL ?>controllers/ControllerProducto.php" data-nombre="<?= htmlspecialchars($item['nombre']) ?>">
                                                    <i class="fas fa-times"></i> Cancelar solicitud
                                                </button>
                                            <?php endif; ?>
                                        <?php elseif ($estadoNorm === 'activa' || $estadoNorm === 'abierta'): // Activa: ver subasta pública y ver detalles (sin editar ni eliminar) ?>
                                            <a href="<?= BASE_URL ?>views/subasta.php?id=<?= $idSubasta ?>" class="btn btn-subastar-producto btn-sm flex-grow-1">
                                                <i class="fas fa-gavel"></i> Ver subasta
                                            </a>
                                            <button type="button" class="btn btn-modificar-producto btn-sm btn-accion-icon btn-ver-detalle" data-id-producto="<?= (int)$item['id_producto'] ?>" data-detalle="<?= $detalleJson ?>" title="Ver detalles" aria-label="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        <?php elseif (in_array($estadoNorm, ['rechazada', 'cancelada', 'sin_ofertas'], true)): // Rechazada/Cancelada/Sin ofertas: reenviar o resubastar, ver detalles y modificar ?>
                                            <button
                                                type="button"
                                                class="btn btn-subastar-producto btn-sm flex-grow-1 btn-abrir-subasta"
                                                data-id-producto="<?= (int)$item['id_producto'] ?>"
                                                data-nombre="<?= htmlspecialchars($item['nombre']) ?>"
                                                data-estado-anterior="<?= htmlspecialchars($estadoNorm) ?>"
                                                data-motivo-rechazo="<?= htmlspecialchars($motivoRechazo) ?>">
                                                <i class="fas fa-redo"></i> <?= $estadoNorm === 'rechazada' ? 'Reenviar' : 'Resubastar' ?>
                                            </button>
                                            <button type="button" class="btn btn-modificar-producto btn-sm btn-accion-icon btn-ver-detalle" data-id-producto="<?= (int)$item['id_producto'] ?>" data-detalle="<?= $detalleJson ?>" title="Ver detalles" aria-label="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="<?= BASE_URL ?>views/subastas/registro-producto.php?id=<?= (int)$item['id_producto'] ?>" class="btn btn-modificar-producto btn-sm btn-accion-icon" title="Modificar" aria-label="Modificar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php else: // Finalizada u otro estado cerrado: solo ver detalles ?>
                                            <button type="button" class="btn btn-modificar-producto btn-sm flex-grow-1 btn-ver-detalle" data-id-producto="<?= (int)$item['id_producto'] ?>" data-detalle="<?= $detalleJson ?>">
                                                <i class="fas fa-eye"></i> Ver detalles
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <p id="sinResultadosFiltro" class="text-second-ppi text-center py-5 mb-0 d-none">
                    No tienes productos con este estado.
                </p>
            <?php endif; ?>

        </div>
    </main>

    <?php include __DIR__ . "/partials/modal-detalle-producto.php"; ?>
    <?php include __DIR__ . "/partials/modal-publicar-subasta.php"; ?>
    <?php include __DIR__ . "/partials/modal-eliminar-producto"; ?>
    <?php include __DIR__ . "/partials/modal-cancelar-solicitud.php"; ?>
    <?php include __DIR__ . "/../Footer/footer.php"; ?>

    <script>
        window.BASE_URL_APP = <?= json_encode(BASE_URL) ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/toast-ppi.js?v=2"></script>
    <script src="<?= BASE_URL ?>assets/js/mis-productos.js?v=14"></script>

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