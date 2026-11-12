<?php
$titulo = "Página no encontrada";
$pagina = "404";
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

    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="assets/css/forms.css" rel="stylesheet">
</head>

<body class="bg-dark-ppi">

    <?php include "views/NavBar/nav.php"; ?>

    <div class="container-fluid py-5">
        <div class="container py-5 text-center">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <i class="bi bi-exclamation-triangle display-1 text-third-ppi mb-3 d-inline-block"></i>
                    <h1 class="display-1 fw-bold text-main-ppi mb-2" style="font-size: 5.5rem;">404</h1>
                    <h2 class="mb-3 text-main-ppi fw-bold">Página no encontrada</h2>
                    <p class="mb-4 text-second-ppi fs-6 mx-auto" style="max-width: 500px;">Lo sentimos, la página que estás buscando no existe o fue trasladada. Puedes volver a la página principal para continuar navegando.</p>
                    <div class="pt-2">
                        <a class="btn btn-primary py-3 px-5 fw-bold" href="index.php">
                            <i class="bi bi-arrow-left me-2"></i>Volver al Inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "views/Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

