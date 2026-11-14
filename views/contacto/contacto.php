<?php
require_once dirname(__DIR__, 2) . '/config/env.php';
$titulo = "Contacto";
$pagina = "contacto";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title><?= $titulo ?> | Gavel.net</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@100;600;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/main.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/forms.css?v=17" rel="stylesheet">
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
                                style="height: 425px; border: 0;" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387191.33750346623!2d-73.97968099999999!3d40.6974881!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2sbd!4v1694259649153!5m2!1sen!2sbd" 
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <form action="" class="mb-4" autocomplete="off">
                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <input type="text" class="w-100 form-control py-3" name="name" placeholder="Tu Nombre">
                                </div>
                                <div class="col-lg-6">
                                    <input type="email" class="w-100 form-control py-3" name="email" placeholder="Tu Correo Electrónico">
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" class="w-100 form-control py-3" name="phone" placeholder="Tu Teléfono">
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" class="w-100 form-control py-3" name="subject" placeholder="Asunto">
                                </div>
                                <div class="col-12">
                                    <textarea class="w-100 form-control" rows="6" cols="10" placeholder="Escribe tu mensaje..."></textarea>
                                </div>
                                <div class="col-12">
                                    <button class="w-100 btn btn-primary py-3 fw-bold" type="submit">Enviar Mensaje</button>
                                </div>
                            </div>
                        </form>
                        <div class="row g-4">
                            <div class="col-xl-6">
                                <div class="d-flex p-4 rounded align-items-center" style="background-color: #131711; border: 1px solid #23271f;">
                                    <i class="fas fa-map-marker-alt fa-2x text-third-ppi me-4"></i>
                                    <div>
                                        <h4 class="text-main-ppi fw-bold mb-1">Dirección</h4>
                                        <p class="mb-0 text-second-ppi">San Salvador, El Salvador</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="d-flex p-4 rounded align-items-center" style="background-color: #131711; border: 1px solid #23271f;">
                                    <i class="fas fa-envelope fa-2x text-third-ppi me-4"></i>
                                    <div>
                                        <h4 class="text-main-ppi fw-bold mb-1">Correo</h4>
                                        <p class="mb-0 text-second-ppi">soporte@gavel.net</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="d-flex p-4 rounded align-items-center" style="background-color: #131711; border: 1px solid #23271f;">
                                    <i class="fa fa-phone-alt fa-2x text-third-ppi me-4"></i>
                                    <div>
                                        <h4 class="text-main-ppi fw-bold mb-1">Teléfono</h4>
                                        <p class="mb-0 text-second-ppi">(+503) 2200-0000</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="d-flex p-4 rounded align-items-center" style="background-color: #131711; border: 1px solid #23271f;">
                                    <i class="fa fa-share-alt fa-2x text-third-ppi me-4"></i>
                                    <div>
                                        <h4 class="text-main-ppi fw-bold mb-1">Redes Sociales</h4>
                                        <div class="d-flex">
                                            <a class="me-3" href="#"><i class="fab fa-twitter text-second-ppi"></i></a>
                                            <a class="me-3" href="#"><i class="fab fa-facebook-f text-second-ppi"></i></a>
                                            <a class="me-3" href="#"><i class="fab fa-instagram text-second-ppi"></i></a>
                                            <a href="#"><i class="fab fa-linkedin-in text-second-ppi"></i></a>
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

    <?php include "../Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

