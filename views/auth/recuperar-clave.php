<?php
session_start();
require_once dirname(__DIR__, 2) . '/config/env.php';

$titulo = "Recuperar contraseña";
$pagina = "recuperar-clave";

if (!empty($_SESSION['id_usuario'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> | Gavel.net</title>
    <?php include dirname(__DIR__, 2) . '/includes/meta_tags.php'; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@100;600;700;800&display=swap" rel="stylesheet">

    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/main.css?v=35" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/forms.css?v=46" rel="stylesheet">
</head>

<body class="bg-dark-ppi">

    <?php include "../NavBar/nav.php"; ?>

    <div class="container-fluid auth-page-wrapper py-5">
        <div class="container auth-container-sm py-3 py-lg-4">
            <div class="row align-items-center justify-content-center g-4 g-lg-5">

                <div class="col-lg-6 pe-lg-3 auth-showcase">
                    <span class="auth-eyebrow">Seguridad de la Cuenta</span>
                    <h1 class="auth-hero-title">Recupera tu <span class="text-accent-ppi">acceso</span></h1>
                    <p class="auth-hero-lead">Te enviaremos un código de verificación de 6 dígitos a tu correo registrado para restablecer tu contraseña.</p>
                    <p class="auth-hero-desc">El código es válido durante 15 minutos y solo puede utilizarse una vez.</p>

                    <div class="auth-meta-list pt-1">
                        <div class="auth-meta-item">
                            <i class="bi bi-envelope-check text-accent-ppi"></i>
                            <span>Verificación rápida por correo electrónico</span>
                        </div>
                        <div class="auth-meta-item">
                            <i class="bi bi-clock-history text-coral-ppi"></i>
                            <span>Vigencia de 15 minutos por código</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 ps-lg-3">
                    <div class="card auth-card shadow p-4 p-md-5">

                        <!-- PASO 1: SOLICITAR CÓDIGO -->
                        <div id="pasoSolicitar">
                            <h3 class="mb-2 text-center text-main-ppi fw-bold">Recuperar contraseña</h3>
                            <p class="mb-4 text-center text-second-ppi fs-6">Ingresa el correo asociado a tu cuenta</p>

                            <form id="formSolicitarCodigo" method="POST" action="<?= BASE_URL ?>controllers/recuperacion_controller.php" autocomplete="off">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="correo" class="form-label text-main-ppi fw-bold">Correo electrónico <span class="req-star">*</span></label>
                                        <input type="email" class="form-control" id="correo" name="correo" placeholder="correo@ejemplo.com" maxlength="100" required>
                                    </div>

                                    <div class="col-12 mt-4">
                                        <button class="w-100 btn btn-primary py-2 fw-bold" type="submit" id="btnEnviarCodigo">
                                            Enviar código de recuperación
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- PASO 2: VERIFICAR CÓDIGO Y CAMBIAR CONTRASEÑA -->
                        <div id="pasoRestablecer" class="d-none">
                            <h3 class="mb-2 text-center text-main-ppi fw-bold">Restablecer contraseña</h3>
                            <p class="mb-3 text-center text-second-ppi fs-6">
                                Ingresa el código enviado a <br>
                                <span class="text-accent-ppi fw-bold" id="lblCorreoDestino"></span>
                            </p>

                            <form id="formRestablecerClave" method="POST" action="<?= BASE_URL ?>controllers/recuperacion_controller.php" autocomplete="off">
                                <div class="d-flex justify-content-center gap-2 my-3">
                                    <input type="text" class="form-control btn-md-square text-center fw-bold fs-5 px-0 otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                                    <input type="text" class="form-control btn-md-square text-center fw-bold fs-5 px-0 otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                                    <input type="text" class="form-control btn-md-square text-center fw-bold fs-5 px-0 otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                                    <input type="text" class="form-control btn-md-square text-center fw-bold fs-5 px-0 otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                                    <input type="text" class="form-control btn-md-square text-center fw-bold fs-5 px-0 otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                                    <input type="text" class="form-control btn-md-square text-center fw-bold fs-5 px-0 otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="nueva_contrasenia" class="form-label text-main-ppi fw-bold">Nueva contraseña <span class="req-star">*</span></label>
                                        <div class="password-field-wrapper">
                                            <input type="password" class="form-control" id="nueva_contrasenia" name="nueva_contrasenia" placeholder="Mínimo 8 caracteres" minlength="8" required>
                                            <button type="button" class="btn-toggle-eye" data-target="nueva_contrasenia" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="confirmar_contrasenia" class="form-label text-main-ppi fw-bold">Confirmar contraseña <span class="req-star">*</span></label>
                                        <div class="password-field-wrapper">
                                            <input type="password" class="form-control" id="confirmar_contrasenia" name="confirmar_contrasenia" placeholder="Repite tu nueva contraseña" minlength="8" required>
                                            <button type="button" class="btn-toggle-eye" data-target="confirmar_contrasenia" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-4">
                                        <button class="w-100 btn btn-primary py-2 fw-bold" type="submit" id="btnRestablecer">
                                            Cambiar contraseña
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="mt-4 pt-1 text-center">
                                <p class="text-second-ppi small mb-2">¿No recibiste el correo?</p>
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    <button type="button" id="btnReenviar" class="btn btn-outline-publish btn-sm px-3 py-2">
                                        <i class="bi bi-arrow-repeat me-1"></i> <span id="btnReenviarTexto">Reenviar código</span>
                                    </button>
                                    <button type="button" id="btnCambiarCorreo" class="btn btn-auth-secondary btn-sm px-3 py-2">
                                        Cambiar correo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <p class="mb-2 text-second-ppi">¿Recordaste tu contraseña? <a href="login.php" class="link-ppi link-coral-hover fw-bold d-inline p-0">Inicia sesión aquí</a></p>
                            <p class="mb-0"><a href="<?= BASE_URL ?>index.php" class="auth-back-link"><i class="bi bi-arrow-left"></i>Volver al inicio</a></p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="overlayRecuperacion" class="registro-loading-overlay d-none" role="status" aria-live="polite">
        <div class="registro-loading-card">
            <div class="spinner-border registro-loading-spinner mb-3" aria-hidden="true"></div>
            <h5 class="text-white fw-bold mb-2">Enviando código...</h5>
            <p class="text-second-ppi small mb-0">Estamos enviando el código de recuperación a tu correo. Esto tomará solo unos segundos.</p>
        </div>
    </div>

    <?php include "../Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/toast-ppi.js"></script>
    <script src="<?= BASE_URL ?>assets/js/recuperar-clave.js?v=2"></script>
</body>

</html>