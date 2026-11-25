<?php
session_start();
require_once dirname(__DIR__, 2) . '/config/env.php';

$titulo = "Iniciar Sesión";
$pagina = "login";

if (!empty($_SESSION['id_usuario'])) {
    $rolSesion = (int)($_SESSION['id_rol'] ?? 0);
    if ($rolSesion === 1) {
        $destino = BASE_URL . "views/admin/dashboard.php";
    } elseif ($rolSesion === 3) {
        $destino = BASE_URL . "views/moderador/dashboard.php";
    } else {
        $destino = BASE_URL . "index.php";
    }
    header("Location: " . $destino);
    exit;
}

$flashToast = $_SESSION['flash_toast'] ?? null;
if ($flashToast !== null) {
    unset($_SESSION['flash_toast']);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> | Gavel.net</title>
    <?php include dirname(__DIR__, 2) . '/includes/meta_tags.php'; ?>

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@100;600;700;800&display=swap" rel="stylesheet">

    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/main.css?v=30" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/forms.css?v=49" rel="stylesheet">
</head>

<body class="bg-dark-ppi">

    <?php include "../NavBar/nav.php"; ?>

    <div class="container-fluid auth-page-wrapper py-5">
        <div class="container auth-container-sm py-3 py-lg-4">
            <div class="row align-items-center justify-content-center g-4 g-lg-5">

                <div class="col-lg-6 auth-showcase pe-lg-3">
                    <span class="auth-eyebrow">Acceso a tu cuenta</span>
                    <h1 class="auth-hero-title">¡Bienvenido de <span class="text-accent-ppi">nuevo!</span></h1>
                    <p class="auth-hero-lead">Accede a tu cuenta para gestionar tus subastas, revisar tus pujas activas o actualizar tus datos personales.</p>
                    <p class="auth-hero-desc">Tu espacio en Gavel.net te está esperando.</p>

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
                        <h3 class="mb-2 text-center text-main-ppi fw-bold">Iniciar sesión</h3>
                        <p class="mb-4 text-center text-second-ppi fs-6">Ingresa tus credenciales para continuar</p>

                        <?php if (($_GET['reason'] ?? '') === 'inactivity'): ?>
                            <div class="auth-session-notice mb-4" id="authSessionNotice" role="alert">
                                <i class="fas fa-clock auth-session-notice-icon"></i>
                                <div>
                                    <div class="fw-semibold text-white mb-1">Sesión cerrada por inactividad</div>
                                    <div>Tu sesión expiró automáticamente por seguridad. Inicia sesión nuevamente para continuar.</div>
                                </div>
                                <button type="button" class="auth-session-notice-close" aria-label="Cerrar aviso" onclick="document.getElementById('authSessionNotice').remove()">&times;</button>
                            </div>
                        <?php endif; ?>

                        <form id="formLogin" name="formLogin" method="POST" action="<?= BASE_URL ?>controllers/login_controller.php" autocomplete="off">
                            <input type="hidden" name="redirect" id="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                            <div class="row g-3">

                                <div class="col-12">
                                    <label for="username" class="form-label text-main-ppi fw-bold">Nombre de usuario <span class="req-star">*</span></label>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Ingresa tu usuario" maxlength="50" spellcheck="false" autocapitalize="off" autocorrect="off" required>
                                </div>

                                <div class="col-12">
                                    <label for="contrasenia" class="form-label text-main-ppi fw-bold">Contraseña <span class="req-star">*</span></label>
                                    <div class="password-field-wrapper">
                                        <input type="password" class="form-control" id="contrasenia" name="contrasenia" placeholder="Ingresa tu contraseña" required>
                                        <button type="button" class="btn-toggle-eye" data-target="contrasenia" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <button class="w-100 btn btn-primary py-2 fw-bold" type="submit" id="btnLogin">Iniciar sesión</button>
                                </div>

                                <div class="col-12 text-center mt-3">
                                    <a href="recuperar-clave.php" class="link-ppi link-coral-hover small text-decoration-none">¿Olvidaste tu contraseña?</a>
                                </div>

                            </div>
                        </form>

                        <div class="mt-4 text-center">
                            <a href="registro.php" class="btn btn-auth-secondary w-100 py-2 fw-semibold">Crear cuenta nueva</a>
                        </div>

                        <p class="mt-4 mb-0 text-center"><a href="<?= BASE_URL ?>index.php" class="auth-back-link"><i class="bi bi-arrow-left"></i>Volver al inicio</a></p>
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
    <script src="<?= BASE_URL ?>assets/js/toast-ppi.js"></script>
    <script src="<?= BASE_URL ?>assets/js/login.js?v=7"></script>
    <?php if (isset($_GET['verificado']) && $_GET['verificado'] == '1'): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                mostrarToast("success", "Cuenta Verificada", "¡Tu cuenta ha sido activada exitosamente! Ya puedes iniciar sesión.");
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        </script>
    <?php endif; ?>
    <?php if (isset($_GET['recuperado']) && $_GET['recuperado'] == '1'): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                mostrarToast("success", "Contraseña Actualizada", "Tu contraseña se restableció correctamente. Inicia sesión con tu nueva clave.");
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        </script>
    <?php endif; ?>
    <?php if (($_GET['reason'] ?? '') === 'inactivity'): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                mostrarToast("warning", "Sesión expirada", "Tu sesión se cerró automáticamente por inactividad. Inicia sesión nuevamente para continuar.", 6000);
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        </script>
    <?php endif; ?>
    <?php if (is_array($flashToast) && !empty($flashToast['mensaje'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                mostrarToast(
                    <?= json_encode((string)($flashToast['tipo'] ?? 'warning'), JSON_UNESCAPED_UNICODE) ?>,
                    <?= json_encode((string)($flashToast['titulo'] ?? 'Aviso'), JSON_UNESCAPED_UNICODE) ?>,
                    <?= json_encode((string)$flashToast['mensaje'], JSON_UNESCAPED_UNICODE) ?>,
                    5000
                );
            });
        </script>
    <?php endif; ?>
</body>

</html>