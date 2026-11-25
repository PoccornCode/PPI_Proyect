<?php
$titulo = "Detalles";
$pagina = "detalles";

$dirnameMain = dirname(__DIR__, 2);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(404);
    exit('Subasta no válida');
}

require_once $dirnameMain . '/config/env.php';

if (!function_exists('e')) {
    function e($texto)
    {
        return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <base href="<?= BASE_URL ?>">
    <title><?= e($titulo) ?> | Gavel.net</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php include $dirnameMain . '/includes/meta_tags.php'; ?>
    <!-- 
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@100;600;800&display=swap"
        rel="stylesheet"> -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.2/font/bootstrap-icons.min.css">
    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/main.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/forms.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/productos.css?v=1" rel="stylesheet">
</head>

<body class="bg-dark-ppi">

    <?php include dirname(__DIR__, 1) . "/NavBar/nav.php"; ?>

    <main class="py-4">
        <div class="container-fluid px-4 px-lg-5">

            <div class="row g-4">

                <!-- Producto -->
                <div class="col-lg-8">
                    <section class="card-gavel detalle-panel h-100 p-3">
                        <div class="text-center mt-2 mb-4">
                            <h3 class="text-main-ppi">Detalles de la subasta</h3>
                        </div>


                        <div class="detalle-foto m-2">
                            <img id="foto-principal" alt="">
                            <!-- <div class="p-3 text-center aria-hidden">
                                <i class="bi bi-image d-block" style="font-size: 5rem;"></i>
                                <span class="small text-second-ppi">Foto del producto</span>
                            </div> -->
                        </div>

                        <div id="miniaturas" class="mt-4 m-2"
                            style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 12px;">
                        </div>




                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 m-2"
                        id="subasta-categoria-estado">
                            
                            
                        </div>

                        <h1 id="subasta-producto-nombre" class="detalle-titulo text-main-ppi m-2">Nombre del Producto</h1>
                        <div class="mb-3 m-2">
                            <span class="badge-condicion">Condición: <span id="subasta-condicion">—</span></span>
                        </div>
                        <p id="subasta-descripcion" class="text-main-ppi mb-4 m-2">Descripcion</p>

                        <div class="detalle-vendedor mb-4">
                            <div id="vendedor-avatar" class="avatar-ppi" aria-hidden="true">?</div>
                            <div class="flex-grow-1">
                                <div class="small text-second-ppi">Vendedor</div>
                                <div id="vendedor-nombre" class="fw-bold text-main-ppi">—</div>
                            </div>
                            <!-- <div class="small text-second-ppi">
                                <i class="fas fa-map-marker-alt me-1"></i><span id="vendedor-ubicacion">—</span>
                            </div> -->
                        </div>

                        <h2 class="section-title">Detalles de la subasta</h2>
                        <div class="row g-3">
                            <div class="col-6 col-md-4">
                                <div class="detalle-dato">
                                    <div class="detalle-dato-label"><i class="fas fa-dollar-sign"></i>Monto inicial
                                    </div>
                                    <label id="dato-monto-inicial" class="detalle-dato-valor">—</label>
                                    <div class="detalle-dato-sub">Precio de salida</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="detalle-dato">
                                    <div class="detalle-dato-label"><i class="fas fa-plus-circle"></i>Incremento mínimo
                                    </div>
                                    <label id="dato-incremento" class="detalle-dato-valor">—</label>
                                    <div class="detalle-dato-sub">Sobre la puja actual</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="detalle-dato destacado">
                                    <div class="detalle-dato-label"><i class="fas fa-gavel"></i>Puja actual</div>
                                    <label id="dato-puja-actual" class="detalle-dato-valor">—</label>
                                    <div id="dato-lider" class="detalle-dato-sub">Puja reciente</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="detalle-dato">
                                    <div class="detalle-dato-label"><i class="far fa-calendar-alt"></i>Fecha de inicio
                                    </div>
                                    <div id="dato-inicio" class="detalle-dato-valor">—</div>
                                    <div id="dato-inicio-hora" class="detalle-dato-sub">—</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="detalle-dato">
                                    <div class="detalle-dato-label"><i class="far fa-calendar-check"></i>Fecha de cierre
                                    </div>
                                    <div id="dato-cierre" class="detalle-dato-valor">—</div>
                                    <div id="dato-cierre-hora" class="detalle-dato-sub">—</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="detalle-dato">
                                    <div class="detalle-dato-label"><i class="fas fa-users"></i>Postores</div>
                                    <div id="dato-postores" class="detalle-dato-valor">—</div>
                                    <div id="dato-total-pujas" class="detalle-dato-sub">Cantidad de interesados</div>
                                </div>
                            </div>
                        </div>

                    </section>
                </div>

                <div class="col-lg-4">
                    <section class="card-gavel chat-panel h-100" aria-label="Pujas">

                        <div class="chat-header">
                            <div>
                                <h2 class="h5 text-main-ppi fw-bold mb-0">Pujas realizadas</h2>
                                <small id="chat-resumen" class="text-second-ppi">0 pujas · 0 postores</small>
                            </div>
                            <span class="status-badge status-activa">
                                <span class="status-dot"></span>En vivo
                            </span>
                        </div>

                        <div id="chat-feed" class="chat-feed" role="log" aria-live="polite"
                            aria-label="Historial de pujas">
                        </div>

                        <form id="formPuja" class="chat-composer" method="POST" autocomplete="off" novalidate>
                            <div class="d-flex flex-wrap align-items-baseline justify-content-between gap-2 mb-2">
                                <label for="monto" class="form-label text-main-ppi fw-bold mb-0">Tu puja</label>
                            </div>

                            <div class="input-group">
                                <span class="input-group-text addon-gavel">$</span>
                                <input type="number" class="form-control" id="monto-input" name="monto" step="0.01" min="0"
                                    inputmode="decimal" placeholder="0.00" required>
                                <button type="button" id="btnPujar" class="btn btn-primary fw-bold px-4">
                                    <i class="fas fa-gavel me-2"></i>Pujar
                                </button>
                            </div>

                            <p id="puja-ayuda" class="small text-second-ppi mt-3 mb-0">
                                <i class="fas fa-info-circle me-1"></i>Tu puja debe superar la puja actual por al menos
                                el
                                incremento mínimo. Las pujas no se pueden retirar.
                            </p>
                        </form>

                    </section>
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


    <?php include dirname(__DIR__, 1) . "/Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const id_subasta = <?= (int) $id ?>,
            ruta_controller = "<?= BASE_URL ?>controllers/detalleSubastaController.php",
            ruta_base_img = "<?php BASE_URL?>uploads/productos/"

    </script>
    <script src="<?=BASE_URL?>assets/js/toast-ppi.js?v=2"></script>
    <script src="<?=BASE_URL?>assets/js/detalleSubasta.js"></script>
</body>

</html>