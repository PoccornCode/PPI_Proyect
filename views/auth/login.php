<?php
session_start();

$titulo = "Iniciar Sesión";
$pagina = "login";

// Si ya hay sesión activa, redirige al inicio
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
    <title><?= $titulo ?></title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <link href="../../assets/css/forms.css?v=2" rel="stylesheet">
</head>

<body class="bg-dark-ppi">

    <div class="container-fluid py-5">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card shadow p-4 p-md-5">
                        <h3 class="mb-2 text-center text-main-ppi fw-bold">Iniciar sesión</h3>
                        <p class="mb-4 text-center text-second-ppi fs-6">Ingresa tus credenciales para continuar</p>

                        <form id="formLogin" name="formLogin" method="POST" action="../../controllers/login_controller.php" autocomplete="off">
                            <div class="row g-3">

                                <div class="col-12">
                                    <label for="username" class="form-label text-main-ppi fw-bold">Nombre de usuario</label>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Ingresa tu usuario" maxlength="50" required>
                                </div>

                                <div class="col-12">
                                    <label for="contrasenia" class="form-label text-main-ppi fw-bold">Contraseña</label>
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

                            </div>
                        </form>

                        <p class="mt-4 mb-2 text-center text-second-ppi">¿No tienes cuenta? <a href="registro.php" class="link-ppi fw-bold d-inline p-0">Regístrate aquí</a></p>
                        <p class="mb-0 text-center"><a href="../../index.php" class="text-second-ppi text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>Volver al inicio</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../assets/js/login.js?v=1"></script>
</body>

</html>