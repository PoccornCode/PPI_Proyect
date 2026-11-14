<?php
require_once dirname(__DIR__) . '/config/env.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: " . BASE_URL . "views/auth/login.php");
    exit();
}

$titulo = "Mi Perfil";
$pagina = "perfil";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> | Gavel.net</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/main.css?v=12" rel="stylesheet">
</head>

<body class="bg-dark-ppi d-flex flex-column min-vh-100">

    <?php include "NavBar/nav.php"; ?>

    <main class="flex-grow-1 py-5">
        <div class="container py-4">
            <div class="card p-4 p-md-5" style="background-color: #0f110e; border: 1px solid #1c2217; border-radius: 12px;">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background-color: #141811; border: 2px solid #5e7325;">
                        <i class="fas fa-user fs-3 text-third-ppi"></i>
                    </div>
                    <div>
                        <h1 class="h3 fw-bold text-main-ppi mb-1"><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?> <?= htmlspecialchars($_SESSION['apellido'] ?? '') ?></h1>
                        <p class="text-second-ppi mb-0 small">@<?= htmlspecialchars($_SESSION['username'] ?? 'usuario') ?></p>
                    <div class="ms-auto">
                        <a href="<?= BASE_URL ?>controllers/logout.php" class="btn btn-outline-danger btn-sm px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
                <p class="text-second-ppi">Esta sección de perfil está en desarrollo.</p>
            </div>
        </div>
    </main>

    <?php include "Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
