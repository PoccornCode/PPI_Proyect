<?php
session_start();

$titulo = "Recuperar contraseña";
$pagina = "recuperar-clave";

if (!empty($_SESSION['id_usuario'])) {
    header("Location: ../../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> | Gavel.net</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@100;600;700;800&display=swap" rel="stylesheet">

    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="../../assets/css/style.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link href="../../assets/css/forms.css?v=8" rel="stylesheet">
</head>

<body class="bg-dark-ppi">

    <?php include "../NavBar/nav.php"; ?>

    <div class="container-fluid py-5">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card shadow p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="rounded-circle bg-light-ppi d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                                <i class="bi bi-shield-lock fs-2 text-main-ppi"></i>
                            </div>
                            <h3 class="mb-2 text-center text-main-ppi fw-bold">Recuperar contraseña</h3>
                            <p class="mb-0 text-center text-second-ppi fs-6">Ingresa tu correo para recibir instrucciones de restablecimiento.</p>
                        </div>

                        <form id="formRecuperarClave" name="formRecuperarClave" method="POST" action="#" autocomplete="off">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="correo" class="form-label text-main-ppi fw-bold">Correo electrónico</label>
                                    <input type="email" class="form-control" id="correo" name="correo" placeholder="correo@ejemplo.com" maxlength="100" required>
                                </div>

                                <div class="col-12 mt-3">
                                    <button class="w-100 btn btn-primary py-2 fw-bold" type="submit" id="btnRecuperarClave">
                                        Enviar codigo de recuperación
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="mt-4 text-center">
                            <p class="mb-2 text-second-ppi">¿Recordaste tu contraseña? <a href="login.php" class="link-ppi fw-bold d-inline p-0">Inicia sesión aquí</a></p>
                            <p class="mb-0"><a href="../../index.php" class="text-second-ppi text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>Volver al inicio</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

    <?php include "../Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/login.js?v=2"></script>
</body>

</html>