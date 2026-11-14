<?php
// Usar BASE_URL de env.php (auto-detecta el subdirectorio en cualquier entorno)
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__, 2) . '/config/env.php';
}
$base_url = BASE_URL;
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@600;700;800&display=swap" rel="stylesheet">

<div class="container-fluid sticky-top px-0 nav-ppi-1">
    <div class="container-fluid bg-dark-ppi">

        <div class="container px-0">

            <nav class="navbar bg-dark-ppi navbar-expand-xl row">
                <a href="<?php echo $base_url; ?>index.php" class="col-7 col-xl-3 py-2 gavel-brand d-flex align-items-baseline gap-1">
                    <span class="brand-name text-main-ppi fw-bold" style="font-size:2rem; line-height:1; font-family:'Raleway',sans-serif;">Gavel</span>
                    <span class="brand-tld text-third-ppi fw-bold" style="font-size:1.6rem; line-height:1; font-family:'Raleway',sans-serif;">.net</span>
                </a>

                <button class="navbar-toggler py-2 px-2 col-2 m-3" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarCollapse">
                    <span class="text-main-ppi p-1">
                        <i class="fas fa-bars fs-2"></i>
                    </span>
                </button>

                <div class="collapse navbar-collapse nbg-dark-ppi py-1 col-2" id="navbarCollapse">

                    <div class="navbar-nav mx-auto">
                        <a href="<?php echo $base_url; ?>index.php" class="nav-item link-ppi">Inicio</a>
                        <a href="<?php echo $base_url; ?>views/catalogo.php" class="nav-item link-ppi">Subastas</a>
                        <a href="<?php echo $base_url; ?>views/catalogo.php" class="nav-item link-ppi">Categorias</a>
                        <a href="<?php echo $base_url; ?>404.php" class="nav-item link-ppi">Como funciona</a>
                        <a href="<?php echo $base_url; ?>views/contacto/contacto.php" class="nav-item link-ppi">Contacto</a>
                        <a href="<?php echo $base_url; ?>views/subastas/registro-producto.php" class="nav-item link-ppi fw-bold" style="color:#94aa34;">+ Subastar</a>
                    </div>

                        <div class="row col-xl-4 col-12">
                            <div class="col-6">
                                <a href="<?php echo $base_url; ?>views/auth/registro.php" class="btn btn-primary rounded w-100 m-2">Registrarse</a>
                            </div>
                            <div class="col-6">
                                <a href="<?php echo $base_url; ?>views/auth/login.php" class="btn btn-secondary rounded w-100 m-2">Iniciar Sesion</a>
                            </div>
                        </div>
                    </div>

            </nav>
        </div>
    </div>
</div>