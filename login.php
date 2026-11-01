<?php
$titulo = "Iniciar sesión";
$pagina = "login";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos personalizados del equipo -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-dark-ppi">

<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <!-- Tarjeta aplicando las clases del equipo -->
                <div class="card shadow p-4 p-md-5">
                    <h3 class="mb-2 text-center text-main-ppi">Iniciar sesión</h3>
                    <p class="mb-4 text-center text-second-ppi fs-6">Ingresa con tus credenciales</p>

                    <?php if (!empty($error)) { ?>
                        <div class="alert alert-danger" role="alert"><?= $error ?></div>
                    <?php } ?>

                    <form id="formulario" name="formulario" method="POST" action="login.php" autocomplete="off">
                        <div class="row g-4">
                            <div class="col-12">
                                <label for="correo" class="form-label text-main-ppi fw-bold">Usuario</label>
                                <input type="email" class="form-control" id="correo" name="correo" placeholder="Ingresa tu correo electrónico" required>
                            </div>
                            <div class="col-12">
                                <label for="clave" class="form-label text-main-ppi fw-bold">Contraseña</label>
                                <input type="password" class="form-control" id="clave" name="clave" placeholder="Ingresa tu contraseña" required>
                            </div>
                            <div class="col-12 mt-4">
                                <button class="w-100 btn btn-primary-ppi py-2 fw-bold" type="submit" id="btnIngresar">Iniciar sesión</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>