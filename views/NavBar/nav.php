<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
if ($host === 'localhost') {
    $base_url = $protocol . $host . '/PPI_Proyect/';
} else {
    $base_url = $protocol . $host . '/';
}
?>

<div class="container-fluid sticky-top px-0 nav-ppi-1">
    <div class="container-fluid bg-dark-ppi">

        <div class="container px-0">

            <nav class="navbar bg-dark-ppi navbar-expand-xl row">

                <a href="<?php echo $base_url; ?>index.php" class="col-7 col-xl-3 py-2 text-decoration-none d-flex align-items-baseline gap-1">
                    <span class="text-main-ppi fw-bold" style="font-size:2rem; line-height:1; font-family:'Raleway',sans-serif;">Gavel</span>
                    <span class="text-third-ppi fw-bold" style="font-size:1.6rem; line-height:1; font-family:'Raleway',sans-serif;">.net</span>
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
                        <a href="404.html" class="nav-item link-ppi">Como funciona</a>
                        <a href="contact.html" class="nav-item link-ppi">Contacto</a>
                        <a href="<?php echo $base_url; ?>views/subastas/registro-subasta.php" class="nav-item link-ppi fw-bold" style="color:#94aa34;">+ Subastar</a>
                    </div>

                    <div class="row col-xl-4 col-12">
                        <div class="col-6">
                            <a href="<?php echo $base_url; ?>views/auth/registro.php">
                                <button type="button" class="btn btn-primary rounded w-100 m-2">Registrarse</button>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?php echo $base_url; ?>views/auth/login.php">
                                <button type="button" class="btn btn-secondary rounded w-100 m-2">Iniciar Sesion</button>
                            </a>
                        </div>
                    </div>
                </div>

            </nav>
        </div>
    </div>
</div>