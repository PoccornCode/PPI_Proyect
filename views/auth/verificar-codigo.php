<?php
require_once dirname(__DIR__, 2) . '/config/env.php';
$titulo = "Verificar Cuenta";
$pagina = "verificar-cuenta";

$correoUrl = isset($_GET['correo']) ? trim($_GET['correo']) : '';
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
    <link href="<?= BASE_URL ?>assets/css/forms.css?v=40" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/main.css?v=35" rel="stylesheet">
    <style>
        .otp-inputs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0 25px 0;
        }

        .otp-input {
            width: 44px;
            height: 48px;
            font-size: 20px;
            font-weight: 600;
            text-align: center;
            border-radius: 6px;
            border: 1px solid #2b3323;
            background-color: #121511;
            color: #e2e6dc;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .otp-input:focus {
            outline: none;
            border-color: #658807;
            box-shadow: 0 0 0 0.25rem rgba(101, 136, 7, 0.25);
            background-color: #161a15;
            color: #f0f3ec;
        }

        .otp-input.is-invalid {
            border-color: #e88277 !important;
            box-shadow: 0 0 0 0.2rem rgba(232, 130, 119, 0.2) !important;
        }

        .dev-badge-alert {
            background: rgba(94, 115, 37, 0.08);
            border: 1px dashed rgba(101, 136, 7, 0.4);
            color: #b5baa9;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
        }
    </style>
</head>

<body class="bg-dark-ppi">

    <?php include "../NavBar/nav.php"; ?>

    <div class="container-fluid auth-page-wrapper py-5">
        <div class="container auth-container-sm py-3 py-lg-4">
            <div class="row align-items-center justify-content-center g-4 g-lg-5">

                <div class="col-lg-6 pe-lg-3 auth-showcase">
                    <span class="auth-eyebrow">Seguridad de la Cuenta</span>
                    <h1 class="auth-hero-title">Verifica tu <span class="text-accent-ppi">correo</span></h1>
                    <p class="auth-hero-lead">Para proteger las pujas y garantizar subastas seguras, necesitamos confirmar tu identidad.</p>
                    <p class="auth-hero-desc">Solo te tomará unos segundos. Ingresa los 6 dígitos que enviamos a tu correo para activar tu cuenta de inmediato.</p>

                    <div class="auth-meta-list pt-1">
                        <div class="auth-meta-item">
                            <i class="bi bi-shield-check text-accent-ppi"></i>
                            <span>Activación inmediata para pujar y vender</span>
                        </div>
                        <div class="auth-meta-item">
                            <i class="bi bi-clock-history text-coral-ppi"></i>
                            <span>El código expira en 15 minutos</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 ps-lg-3">
                    <div class="card auth-card shadow p-4 p-md-5">

                        <h3 class="mb-2 text-center text-main-ppi fw-bold">Verifica tu correo</h3>
                        <p class="mb-4 text-center text-second-ppi fs-6">
                            Ingresa el código de 6 dígitos que enviamos a <br>
                            <span class="text-accent-ppi fw-bold" id="lblCorreoDestino"><?= htmlspecialchars($correoUrl, ENT_QUOTES, 'UTF-8') ?: 'tu correo registrado' ?></span>
                        </p>

                        <form id="formVerificarCodigo" autocomplete="off">
                            <input type="hidden" id="inputCorreo" name="correo" value="<?= htmlspecialchars($correoUrl, ENT_QUOTES, 'UTF-8') ?>">

                            <div class="otp-inputs">
                                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*" autofocus>
                                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" id="btnVerificar" class="w-100 btn btn-primary py-2 fw-bold">Verificar y continuar</button>
                            </div>
                        </form>

                        <div class="mt-4 pt-1 text-center">
                            <p class="text-second-ppi fs-6 mb-3">¿No recibiste el correo? Revisa tu carpeta de spam o solicita uno nuevo:</p>
                            <div>
                                <button type="button" id="btnReenviar" class="btn btn-outline-publish btn-sm px-4 py-2">
                                    <i class="bi bi-arrow-repeat me-1"></i> <span id="btnReenviarTexto">Reenviar código</span>
                                </button>
                            </div>
                        </div>

                        <p class="mt-4 mb-0 text-center">
                            <a href="<?= BASE_URL ?>views/auth/login.php" class="auth-back-link">
                                <i class="bi bi-arrow-left"></i>Volver a Iniciar Sesión
                            </a>
                        </p>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include "../Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/toast-ppi.js"></script>
    <script src="<?= BASE_URL ?>assets/js/verificar-codigo.js?v=3"></script>
</body>

</html>