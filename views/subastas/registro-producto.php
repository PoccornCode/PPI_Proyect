<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/conexion.php';

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
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link href="../../assets/css/forms.css?v=8" rel="stylesheet">

    <style>
        body.bg-dark-ppi {
            background-color: #080708 !important;
        }

        main {
            background-image: none !important;
            background-color: #080708 !important;
        }

        .card-gavel {
            background-color: #0f110e;
            border: 1px solid #191c17;
            border-radius: 12px;
        }

        .section-title {
            color: #c9c9c9;
            font-size: 1.15rem;
            font-weight: 700;
            border-bottom: 1px solid #191c17;
            padding-bottom: 0.6rem;
            margin-bottom: 1.25rem;
        }

        .form-select.select-gavel {
            background-color: #080708;
            color: #a7a59d;
            border: 2px solid #191c17;
            border-radius: 10px;
        }

        .form-select.select-gavel:focus {
            border-color: #658807;
            color: #c9c9c9;
            box-shadow: 0 0 0 .25rem rgba(101, 136, 7, .25);
        }

        .input-group-text.addon-gavel {
            background-color: #191c17;
            border: 2px solid #191c17;
            color: #c9c9c9;
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
        }

        .upload-dropzone {
            border: 2px dashed #23271f;
            background: #080708;
            border-radius: 10px;
            padding: 2rem 1.25rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        .upload-dropzone:hover {
            border-color: #658807;
            background: rgba(101, 136, 7, 0.05);
        }

        .preview-img-box {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #23271f;
            background: #080708;
        }

        .preview-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-img-box .badge-principal {
            position: absolute;
            top: 4px;
            left: 4px;
            background: #5e7325;
            color: #fff;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
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
                        <a href="../../index.php" class="btn btn-secondary btn-sm px-3">
                            Volver al inicio
                        </a>
                    </div>

                    <form id="formRegistroSubasta" action="../../controllers/crear_producto.php" method="POST" enctype="multipart/form-data" autocomplete="off">

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
                            <a href="../../index.php" class="btn btn-secondary px-4 py-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">Guardar Producto</button>
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
    <script src="../../assets/js/registro-subasta.js?v=2"></script>
</body>

</html>