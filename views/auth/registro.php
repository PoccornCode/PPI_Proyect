<?php
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

    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="../../assets/css/style.css" rel="stylesheet">
    <link href="../../assets/css/forms.css?v=3" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
</head>

<body class="bg-dark-ppi">

    <?php
    include "../NavBar/nav.php";
    ?>

    <div class="container-fluid py-5">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-md-9 col-lg-7 col-xl-6">
                    <div class="card shadow p-4 p-md-5">
                        <h3 class="mb-2 text-center text-main-ppi fw-bold">Crear una cuenta</h3>
                        <p class="mb-4 text-center text-second-ppi fs-6">Completa tus datos para registrarte</p>

                        <form id="formRegistro" name="formRegistro" method="POST" action="../../controllers/registro_controller.php" autocomplete="off">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="nombre" class="form-label text-main-ppi fw-bold">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingresa tu nombre" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="apellido" class="form-label text-main-ppi fw-bold">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Ingresa tu apellido" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="correo" class="form-label text-main-ppi fw-bold">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="correo" name="correo" placeholder="correo@ejemplo.com" maxlength="100" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="telefono" class="form-label text-main-ppi fw-bold">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Ej. 7000-0000" maxlength="30" required>
                                </div>

                                <div class="col-12">
                                    <label for="ubicacion" class="form-label text-main-ppi fw-bold">Ubicación</label>
                                    <input type="text" class="form-control" id="ubicacion" name="ubicacion" placeholder="Ciudad, Departamento" required>
                                </div>

                                <div class="col-12">
                                    <label for="username" class="form-label text-main-ppi fw-bold">Nombre de usuario</label>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Elige un usuario único" maxlength="50" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="contrasenia" class="form-label text-main-ppi fw-bold">Contraseña</label>
                                    <div class="password-field-wrapper">
                                        <input type="password" class="form-control" id="contrasenia" name="contrasenia" placeholder="Ingresa tu contraseña" minlength="8" required>
                                        <button type="button" class="btn-toggle-eye" data-target="contrasenia" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="confirmar_contrasenia" class="form-label text-main-ppi fw-bold">Confirmar Contraseña</label>
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

                        <p class="mt-4 mb-2 text-center text-second-ppi">¿Ya tienes cuenta? <a href="login.php" class="link-ppi fw-bold d-inline p-0">Inicia sesión aquí</a></p>
                        <p class="mb-0 text-center"><a href="../../index.php" class="text-second-ppi text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>Volver al inicio</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "../Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../assets/js/registro.js?v=2"></script>
</body>

</html>