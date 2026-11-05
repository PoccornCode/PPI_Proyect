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
                
                <div class="row col-7 col-xl-3 py-1 mt-3 mb-1">
                    <div class="col-6">
                        <p class="text-main-ppi display-5" style="line-height: 0;">Gavel</p>
                    </div>
                    <div class="col-6">
                        <p class="text-third-ppi display-6 mb-2" style="line-height: 0;">.net</p>
                    </div>
                </div>

                <button class="navbar-toggler py-2 px-2 col-2 m-3" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarCollapse">
                    <span class="fa fa-bars text-main-ppi p-1">
                        <i class="bi bi-arrow-bar-down fs-2"></i>
                    </span>
                </button>

                <div class="collapse navbar-collapse nbg-dark-ppi py-1 col-2" id="navbarCollapse">

                    <div class="navbar-nav mx-auto">
                        <a href="<?php echo $base_url; ?>404.html" class="nav-item link-ppi">Inicio</a>
                        <a href="404.html" class="nav-item link-ppi">Subastas</a>
                        <a href="404.html" class="nav-item link-ppi active">Categorias<a>
                                <a href="404.html" class="nav-item link-ppi active">Como funciona<a>
                                <a href="contact.html" class="nav-item link-ppi">Contacto</a>
                    </div>

                    <div class="row col-xl-4 col-12">
                        <div class="col-6">
                            <a href="<?php echo $base_url; ?>views/auth/registro.php">
                                <button type="submit" class="btn btn-primary rounded w-100 m-2">Registrarse</button>
                            </a>

                        </div>
                        <div class="col-6">
                            <a href="<?php echo $base_url; ?>views/auth/login.php">
                                <button type="submit" class="btn btn-secondary rounded w-100 m-2">Iniciar
                                    Sesion</button>
                            </a>

                        </div>
                    </div>
                </div>



            </nav>
        </div>
    </div>
</div>