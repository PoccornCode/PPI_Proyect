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
                <div class="row col-2 p-1">
                    <div class="col-7">
                        <p class="text-main-ppi display-5" style="line-height: 0;">Gavel</p>
                    </div>
                    <div class="col-5">
                        <p class="text-second-ppi display-6 mb-2" style="line-height: 0;">.net</p>
                    </div>
                </div>
                <button class="navbar-toggler py-2 px-3 col-6" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarCollapse">
                    <span class="fa fa-bars text-primary"></span>
                </button>

                <div class="collapse navbar-collapse nbg-dark-ppi py-3 col-1" id="navbarCollapse">
                    <div class="navbar-nav mx-auto border-top">
                        <a href="404.html" class="nav-item link-ppi">Inicio</a>
                        <a href="404.html" class="nav-item link-ppi">Subastas</a>
                        <a href="404.html" class="nav-item link-ppi active">Categorias<a>
                        <a href="404.html" class="nav-item link-ppi active">Como funciona<a>
                        <!-- <div class="nav-item dropdown">
                            <a href="#" class="link-ppi dropdown-toggle" data-bs-toggle="dropdown">Dropdown</a>
                            <div class="dropdown-menu m-0 bg-secondary rounded-0 ">
                                <a href="#" class="dropdown-item-ppi ">Dropdown 1</a>
                                <a href="#" class="dropdown-item-ppi">Dropdown 2</a>
                                <a href="#" class="dropdown-item-ppi">Dropdown 3</a>
                                <a href="#" class="dropdown-item-ppi">Dropdown 4</a>
                            </div>
                        </div> -->
                        <a href="contact.html" class="nav-item link-ppi">Contacto</a>
                    </div>
                </div>

                <div class="row col-4">
                    <div class="col-6">
                        <a href="<?php echo $base_url; ?>views/auth/registro.php">
                            <button type="submit" class="btn btn-primary rounded w-100 m-2">Registrarse</button>
                        </a>

                    </div>
                    <div class="col-6">
                        <a href="<?php echo $base_url; ?>views/auth/login.php">
                            <button type="submit" class="btn btn-secondary rounded w-100 m-2">Iniciar Sesion</button>
                        </a>

                    </div>
                </div>

            </nav>
        </div>
    </div>
</div>