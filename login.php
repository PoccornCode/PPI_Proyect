<?php
$titulo = "Iniciar sesión";
$pagina = "login";
// $error se llenará cuando se procese el POST (validación contra la base de datos)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?></title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-dark-ppi">

    <!-- AQUÍ IRÁ EL HEADER (includes/cabecera.php) cuando esté listo -->

    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-5">
                    <div class="card shadow p-5">
                        <h1 class="mb-2 text-center text-main-ppi">Iniciar sesión</h1>
                        <p class="mb-4 text-center text-second-ppi">Ingresa con tu cuenta para participar en las subastas.</p>

                        <?php if (!empty($error)) { ?>
                            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
                        <?php } ?>

                        <form id="formulario" name="formulario" method="POST" action="login.php" autocomplete="off">
                            <div class="row g-4">
                                <div class="col-12">
                                    <label for="correo" class="form-label text-main-ppi fw-bold">Nombre de usuario</label>
                                    <input type="text" class="w-100 form-control py-3" id="correo" name="correo" placeholder="Nombre de usuario" required>
                                </div>
                                <div class="col-12">
                                    <label for="clave" class="form-label text-main-ppi fw-bold">Contraseña</label>
                                    <input type="password" class="w-100 form-control py-3" id="clave" name="clave" placeholder="Tu contraseña" required>
                                </div>
                                <div class="col-12">
                                    <button class="w-100 btn btn-primary py-3" type="submit" id="btnIngresar">Ingresar</button>
                                </div>
                            </div>
                        </form>

                        <p class="mt-4 mb-0 text-center text-second-ppi">¿No tienes cuenta? <a href="registro.php" class="link-ppi d-inline p-0">Regístrate aquí</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AQUÍ IRÁ EL FOOTER (includes/pie.php) cuando esté listo -->

</body>
</html>