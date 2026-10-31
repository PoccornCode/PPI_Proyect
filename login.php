<?php
$titulo = "Iniciar sesión";
$pagina = "login";


if (file_exists(__DIR__ . "/includes/cabecera.php")) {
    include "includes/cabecera.php";
} else {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php
}
?>

        <div class="container-fluid py-5">
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-5">
                        <div class="bg-light rounded p-5">
                            <h1 class="mb-2 text-center">Iniciar sesión</h1>
                            <p class="mb-4 text-center">Ingresa con tu cuenta para participar en las subastas.</p>

                            <?php if (!empty($error)) { ?>
                                <div class="alert alert-danger" role="alert"><?= $error ?></div>
                            <?php } ?>

                            <form id="formulario" name="formulario" method="POST" action="login.php" autocomplete="off">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <label for="correo" class="form-label">Correo electronico</label>
                                        <input type="email" class="w-100 form-control border-0 py-3" id="correo" name="correo" placeholder="correo@ejemplo.com" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="clave" class="form-label">Contrasena</label>
                                        <input type="password" class="w-100 form-control border-0 py-3" id="clave" name="clave" placeholder="Tu contraseña" required>
                                    </div>
                                    <div class="col-12">
                                        <button class="w-100 btn btn-primary text-white py-3" type="submit" id="btnIngresar">Ingresar</button>
                                    </div>
                                </div>
                            </form>

                            <p class="mt-4 mb-0 text-center">¿No tienes cuenta? <a href="registro.php" class="text-primary">Regístrate aquí</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<?php

if (file_exists(__DIR__ . "/includes/pie.php")) {
    include "includes/pie.php";
} else {
?>
</body>
</html>
<?php
}
?>