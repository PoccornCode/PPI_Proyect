<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__, 2) . '/config/env.php';
$titulo = "Contacto";
$pagina = "contacto";

$nombrePre = '';
$correoPre = '';
$telefonoPre = '';

if (!empty($_SESSION['id_usuario'])) {
    require_once dirname(__DIR__, 2) . '/dao/ContactoDAO.php';
    $contactoDao = new ContactoDAO();
    $datosUsuario = $contactoDao->obtenerDatosUsuario((int)$_SESSION['id_usuario']);
    if ($datosUsuario) {
        $nombreCompleto = trim(($datosUsuario['nombre'] ?? '') . ' ' . ($datosUsuario['apellido'] ?? ''));
        $nombrePre = mb_convert_case($nombreCompleto, MB_CASE_TITLE, 'UTF-8');
        $correoPre = $datosUsuario['correo'] ?? '';
        $telefonoPre = $datosUsuario['telefono'] ?? '';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title><?= $titulo ?> | Gavel.net</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <?php include dirname(__DIR__, 2) . '/includes/meta_tags.php'; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@100;600;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/main.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/forms.css?v=47" rel="stylesheet">
</head>

<body class="bg-dark-ppi">

    <?php include "../NavBar/nav.php"; ?>

    <div class="container-fluid py-5">
        <div class="container py-4">
            <div class="card shadow p-4 p-md-5" style="background-color: #0f110e; border: 1px solid #23271f;">
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div>
                            <h2 class="mb-4 text-main-ppi fw-bold">Atención al Cliente y Soporte Técnico</h2>
                            <p class="mb-4 text-second-ppi">¿Tienes alguna duda sobre tus subastas, pagos o tu cuenta? Completa el formulario o contáctanos directamente por nuestros canales de atención.</p>
                            <div class="rounded overflow-hidden" style="border: 1px solid #23271f;">
                                <iframe class="w-100" 
                                style="height: 425px; border: 0;" src="https://maps.google.com/maps?q=Apastepeque,San%20Vicente,El%20Salvador&t=&z=14&ie=UTF8&iwloc=&output=embed" 
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <form id="formContacto" method="POST" action="<?= BASE_URL ?>controllers/contacto_controller.php" class="mb-4" autocomplete="off" novalidate>
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <label for="nombre" class="form-label text-main-ppi fw-bold">Nombre <span class="req-star">*</span></label>
                                    <input type="text" class="w-100 form-control py-3" id="nombre" name="nombre" value="<?= htmlspecialchars($nombrePre, ENT_QUOTES, 'UTF-8') ?>" placeholder="Tu Nombre" maxlength="120" required>
                                </div>
                                <div class="col-lg-6">
                                    <label for="correo" class="form-label text-main-ppi fw-bold">Correo Electrónico <span class="req-star">*</span></label>
                                    <input type="email" class="w-100 form-control py-3" id="correo" name="correo" value="<?= htmlspecialchars($correoPre, ENT_QUOTES, 'UTF-8') ?>" placeholder="correo@ejemplo.com" maxlength="120" required>
                                </div>
                                <div class="col-lg-6">
                                    <label for="telefono" class="form-label text-main-ppi fw-bold">Teléfono <span class="req-star">*</span></label>
                                    <input type="tel" class="w-100 form-control py-3" id="telefono" name="telefono" value="<?= htmlspecialchars($telefonoPre, ENT_QUOTES, 'UTF-8') ?>" placeholder="Ej. 7000-0000" maxlength="30" required>
                                </div>
                                <div class="col-lg-6">
                                    <label for="asunto" class="form-label text-main-ppi fw-bold">Asunto <span class="req-star">*</span></label>
                                    <input type="text" class="w-100 form-control py-3" id="asunto" name="asunto" placeholder="Asunto de tu consulta" maxlength="150" required>
                                </div>
                                <div class="col-12">
                                    <label for="mensaje" class="form-label text-main-ppi fw-bold">Mensaje <span class="req-star">*</span></label>
                                    <textarea class="w-100 form-control" id="mensaje" name="mensaje" rows="5" cols="10" placeholder="Escribe tu mensaje (mínimo 10 caracteres)..." minlength="10" required></textarea>
                                </div>
                                <div class="col-12 mt-4">
                                    <button class="w-100 btn btn-primary py-3 fw-bold" type="submit" id="btnEnviarContacto">Enviar Mensaje</button>
                                </div>
                            </div>
                        </form>
                        <div class="row g-4">
                            <div class="col-xl-6">
                                <div class="d-flex p-4 rounded align-items-center" style="background-color: #131711; border: 1px solid #23271f;">
                                    <i class="fas fa-map-marker-alt fa-2x text-third-ppi me-4"></i>
                                    <div>
                                        <h4 class="text-main-ppi fw-bold mb-1">Dirección</h4>
                                        <p class="mb-0 text-second-ppi">Apastepeque, San Vicente</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="d-flex p-4 rounded align-items-center" style="background-color: #131711; border: 1px solid #23271f;">
                                    <i class="fas fa-envelope fa-2x text-third-ppi me-4"></i>
                                    <div class="min-w-0">
                                        <h4 class="text-main-ppi fw-bold mb-1">Correo</h4>
                                        <p class="mb-0 text-second-ppi text-break">soportegavelsubastas@gmail.com</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="d-flex p-4 rounded align-items-center" style="background-color: #131711; border: 1px solid #23271f;">
                                    <i class="fa fa-phone-alt fa-2x text-third-ppi me-4"></i>
                                    <div>
                                        <h4 class="text-main-ppi fw-bold mb-1">Teléfono</h4>
                                        <p class="mb-0 text-second-ppi">(+503) 6767-6757</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="d-flex p-4 rounded align-items-center" style="background-color: #131711; border: 1px solid #23271f;">
                                    <i class="fa fa-share-alt fa-2x text-third-ppi me-4"></i>
                                    <div>
                                        <h4 class="text-main-ppi fw-bold mb-1">Redes Sociales</h4>
                                        <div class="d-flex">
                                            <a class="me-3" href="https://twitter.com" target="_blank" rel="noopener noreferrer" aria-label="Twitter"><i class="fab fa-twitter text-second-ppi"></i></a>
                                            <a class="me-3" href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook-f text-second-ppi"></i></a>
                                            <a class="me-3" href="https://instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram text-second-ppi"></i></a>
                                            <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><i class="fab fa-linkedin-in text-second-ppi"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="overlayContacto" class="registro-loading-overlay d-none" role="status" aria-live="polite">
        <div class="registro-loading-card">
            <div class="spinner-border registro-loading-spinner mb-3" aria-hidden="true"></div>
            <h5 class="text-white fw-bold mb-2">Enviando mensaje...</h5>
            <p class="text-second-ppi small mb-0">Estamos procesando tu consulta y enviando la confirmación. Esto tomará solo unos segundos.</p>
        </div>
    </div>

    <?php include "../Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/toast-ppi.js"></script>
    <script src="<?= BASE_URL ?>assets/js/contacto.js?v=2"></script>
</body>

</html>

