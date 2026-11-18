<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$titulo = "Registrar Producto";

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['redirect_after_login'] = BASE_URL . 'views/subastas/registro-producto.php';
    $_SESSION['flash_toast'] = [
        'tipo' => 'warning',
        'titulo' => 'Acceso restringido',
        'mensaje' => 'Debes iniciar sesión para registrar productos.'
    ];
    header("Location: " . BASE_URL . "views/auth/login.php");
    exit();
}

$titulo = "Registrar Producto";
$pagina = "registro-producto";

//Agregar a dao o controller despues
$categorias = [];
$catQuery = $conexion->query("SELECT id_categoria, nombre FROM sb_categoria ORDER BY nombre ASC");
if ($catQuery) {
    while ($cat = $catQuery->fetch_assoc()) {
        $categorias[] = $cat;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>

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
    <link href="<?= BASE_URL ?>assets/css/productos.css?v=1" rel="stylesheet">
</head>

<body class="bg-dark-ppi">


    <?php include __DIR__ . "/../NavBar/nav.php"; ?>

    <main class="py-5">
        <div class="container-fluid px-4 px-lg-5 py-2">
            <div class="row justify-content-center">
                <div class="col-lg-9 col-xl-7">

                    <div class="d-flex justify-content-between align-items-end mb-4">
                        <div>
                            <h3 class="text-main-ppi fw-bold mb-1">Registrar Producto</h3>
                            <p class="text-second-ppi small mb-0">Completa la información del producto.</p>
                        </div>
                        <a href="<?= BASE_URL ?>index.php" class="btn btn-secondary btn-sm px-3">
                            Volver al inicio
                        </a>
                    </div>

                    <form id="formRegistroSubasta" action="<?= BASE_URL ?>controllers/ControllerProducto.php" method="POST" enctype="multipart/form-data" autocomplete="off">

                        <div class="card card-gavel p-4 mb-4 shadow">
                            <h5 class="section-title">Información del Producto</h5>

                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label for="nombre" class="form-label text-main-ppi fw-bold small">Nombre del Producto</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" placeholder="Ej. Tarjeta Gráfica RTX 3080 10GB" required>
                                </div>

                                <div class="col-md-4">
                                    <label for="id_categoria" class="form-label text-main-ppi fw-bold small">Categoría</label>
                                    <select class="form-select select-gavel" id="id_categoria" name="id_categoria" required>
                                        <option value="" disabled selected>Selecciona una categoría...</option>
                                        //Agregas a dao o controller despues
                                        <?php if (!empty($categorias)): ?>
                                            <?php foreach ($categorias as $cat): ?>
                                                <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="" disabled>No hay categorías registradas en la base de datos</option>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="condicion" class="form-label text-main-ppi fw-bold small">Condición</label>
                                    <select class="form-select select-gavel" id="condicion" name="condicion" required>
                                        <option value="" disabled selected>Selecciona la condición del producto...</option>
                                        <option value="Nuevo">Nuevo (Sellado en empaque original)</option>
                                        <option value="Como nuevo">Como nuevo (Sin marcas de uso o defectos)</option>
                                        <option value="Excelente">Excelente (Mínimo uso, excelente estado funcional)</option>
                                        <option value="Buen estado">Buen estado (Signos leves de uso normal)</option>
                                        <option value="Aceptable">Aceptable (Marcas visibles de desgaste, funcional)</option>
                                        <option value="Para repuestos o restauración">Para repuestos o restauración</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="descripcion" class="form-label text-main-ppi fw-bold small">Descripción Detallada</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4" placeholder="Especificaciones, detalles técnicos, estado cosmético, accesorios..." required></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card card-gavel p-4 mb-4 shadow">
                            <h5 class="section-title">Fotografías del Artículo</h5>

                            <div class="upload-dropzone" id="dropzoneImagenes">
                                <i class="bi bi-images fs-2 text-second-ppi mb-2 d-block"></i>
                                <p class="text-main-ppi mb-1 small fw-bold">Seleccionar imágenes del producto</p>
                                <p class="text-second-ppi small mb-0">Formatos admitidos: JPG, PNG, WEBP (La primera foto se utilizará como imagen principal)</p>
                                <input type="file" class="d-none" id="imagenes" name="imagenes[]" multiple accept="image/*">
                            </div>

                            <div id="imagePreviewContainer" class="d-flex flex-wrap gap-2 mt-3"></div>
                        </div>

                        <!-- ACCIONES -->
                        <div class="d-flex justify-content-end gap-3 pb-4">
                            <a href="<?= BASE_URL ?>index.php" class="btn btn-secondary px-4 py-2">Cancelar</a>
                            <button type="submit" id="btnGuardar" class="btn btn-primary px-5 py-2 fw-bold">Guardar Producto</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="modalPPI" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-ppi">
                <div class="modal-header modal-header-ppi">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="modalPPITitulo">
                        Aviso
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body py-4" id="modalPPICuerpo">
                    Mensaje
                </div>
                <div class="modal-footer modal-footer-ppi">
                    <button type="button" class="btn btn-ppi-modal btn-sm" id="btnModalPPIAccion" data-bs-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const RUTA_MIS_PRODUCTOS = "<?= BASE_URL ?>views/subastas/mis-productos.php";
    </script>
    <script src="<?= BASE_URL ?>assets/js/toast-ppi.js?v=2"></script>
    <script src="<?= BASE_URL ?>assets/js/registro-producto.js?v=4"></script>
</body>

</html>