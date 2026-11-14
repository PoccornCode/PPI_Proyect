<?php
require_once dirname(__DIR__, 2) . '/config/env.php';
$titulo = "Crear Cuenta";
$pagina = "registro";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> | Gavel.net</title>

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@100;600;700;800&display=swap" rel="stylesheet">

    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/forms.css?v=22" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/main.css?v=12" rel="stylesheet">
</head>

<body class="bg-dark-ppi">

    <?php
    include "../NavBar/nav.php";
    ?>

    <div class="container-fluid auth-page-wrapper py-5">
        <div class="container auth-container-md py-3 py-lg-4">
            <div class="row align-items-center justify-content-center g-4 g-lg-5">
                
                <div class="col-lg-6 pe-lg-3 auth-showcase">
                    <span class="auth-eyebrow">Comunidad Gavel.net</span>
                    <h1 class="auth-hero-title">¡Únete a <span class="text-accent-ppi">Gavel.net</span>!</h1>
                    <p class="auth-hero-lead">Forma parte de la plataforma donde las subastas y piezas exclusivas cobran vida.</p>
                    <p class="auth-hero-desc">Participa en pujas en tiempo real, compite con otros compradores o pon en venta tus propios artículos con total transparencia.</p>

                    <div class="auth-meta-list pt-1">
                        <div class="auth-meta-item">
                            <i class="bi bi-geo-alt-fill text-coral-ppi"></i>
                            <span>El Salvador</span>
                        </div>
                        <div class="auth-meta-item">
                            <i class="bi bi-envelope-fill text-accent-ppi"></i>
                            <span>contacto@gavel.net</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 ps-lg-3">
                    <div class="card auth-card shadow p-4 p-md-5">
                        <h3 class="mb-2 text-center text-main-ppi fw-bold">Crear una cuenta</h3>
                        <p class="mb-4 text-center text-second-ppi fs-6">Completa tus datos para registrarte</p>

                        <form id="formRegistro" name="formRegistro" method="POST" action="<?= BASE_URL ?>controllers/registro_controller.php" autocomplete="off">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="nombre" class="form-label text-main-ppi fw-bold">Nombre <span class="req-star">*</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingresa tu nombre" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="apellido" class="form-label text-main-ppi fw-bold">Apellido <span class="req-star">*</span></label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Ingresa tu apellido" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="correo" class="form-label text-main-ppi fw-bold">Correo Electrónico <span class="req-star">*</span></label>
                                    <input type="email" class="form-control" id="correo" name="correo" placeholder="correo@ejemplo.com" maxlength="100" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="telefono" class="form-label text-main-ppi fw-bold">Teléfono <span class="req-star">*</span></label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Ej. 7000-0000" maxlength="30" required>
                                </div>

                                <div class="col-12">
                                    <label for="ubicacion" class="form-label text-main-ppi fw-bold">Ubicación <span class="req-star">*</span></label>
                                    <input type="text" class="form-control" id="ubicacion" name="ubicacion" placeholder="Ciudad, Departamento" required>
                                </div>

                                <div class="col-12">
                                    <label for="username" class="form-label text-main-ppi fw-bold">Nombre de usuario <span class="req-star">*</span></label>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Elige un usuario único" maxlength="50" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="contrasenia" class="form-label text-main-ppi fw-bold">Contraseña <span class="req-star">*</span></label>
                                    <div class="password-field-wrapper">
                                        <input type="password" class="form-control" id="contrasenia" name="contrasenia" placeholder="Ingresa tu contraseña" minlength="8" required>
                                        <button type="button" class="btn-toggle-eye" data-target="contrasenia" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="confirmar_contrasenia" class="form-label text-main-ppi fw-bold">Confirmar Contraseña <span class="req-star">*</span></label>
                                    <div class="password-field-wrapper">
                                        <input type="password" class="form-control" id="confirmar_contrasenia" name="confirmar_contrasenia" placeholder="Repite tu contraseña" minlength="8" required>
                                        <button type="button" class="btn-toggle-eye" data-target="confirmar_contrasenia" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <button class="w-100 btn btn-primary py-2 fw-bold" type="submit" id="btnRegistrar">Registrarse</button>
                                </div>

                            </div>
                        </form>

                        <p class="mt-4 mb-2 text-center text-second-ppi">¿Ya tienes cuenta? <a href="login.php" class="link-ppi link-coral-hover fw-bold d-inline p-0">Inicia sesión aquí</a></p>
                        <p class="mb-0 text-center"><a href="<?= BASE_URL ?>index.php" class="auth-back-link"><i class="bi bi-arrow-left"></i>Volver al inicio</a></p>
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
    <script src="<?= BASE_URL ?>assets/js/registro.js?v=7"></script>
</body>

</html>