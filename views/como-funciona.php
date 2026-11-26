<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/config/env.php';
$titulo = "Cómo Funciona";
$pagina = "como-funciona";
$base_url = BASE_URL;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($titulo) ?> | Gavel.net</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <?php include dirname(__DIR__) . '/includes/meta_tags.php'; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Raleway:wght@400;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="<?= $base_url ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $base_url ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= $base_url ?>assets/css/main.css?v=33" rel="stylesheet">

    <style>
        .section-header-title {
            color: #ffffff !important;
            font-size: 1.4rem;
            font-weight: 700;
            font-family: 'Raleway', sans-serif;
        }

        .section-header-sub {
            color: #a7a59d !important;
        }

        .step-title {
            color: #ffffff !important;
            font-size: 1.12rem;
            font-weight: 700;
            font-family: 'Raleway', sans-serif;
        }

        .step-desc {
            color: #a7a59d !important;
            line-height: 1.6;
        }

        .hero-como-funciona {
            padding: 85px 0 45px !important;
        }

        .role-switch-container {
            display: inline-flex;
            background-color: #0b0d09;
            border: 1px solid #1c2217;
            border-radius: 50px;
            padding: 4px;
        }

        .role-switch-btn {
            background-color: transparent;
            color: #a7a59d;
            border: 1px solid transparent;
            border-radius: 50px;
            font-size: 0.92rem;
            font-weight: 600;
            padding: 8px 24px;
            transition: all 0.25s ease;
        }

        .role-switch-btn:hover {
            color: #c9c9c9;
        }

        .role-switch-btn.active {
            background-color: #161e12;
            color: #ffffff;
            border-color: #5e7325;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.4);
        }

        @media (max-width: 575.98px) {
            .role-switch-container {
                display: flex;
                width: 100%;
                max-width: 340px;
                margin-left: auto;
                margin-right: auto;
                flex-wrap: nowrap;
            }

            .role-switch-btn {
                flex: 1 1 0;
                padding: 8px 12px;
                font-size: 0.85rem;
                white-space: nowrap;
                text-align: center;
            }
        }

        .step-card {
            background-color: #0f110e;
            border: 1px solid #1c2217;
            border-radius: 12px;
            padding: 2.2rem 1.6rem;
            height: 100%;
            transition: border-color 0.2s ease, transform 0.2s ease;
        }

        .step-card:hover {
            border-color: #364222;
            transform: translateY(-2px);
        }

        .step-num {
            font-family: 'Raleway', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            color: #44541d;
            line-height: 1;
            margin-bottom: 1.1rem;
            display: block;
            transition: color 0.2s ease;
        }

        .step-card:hover .step-num {
            color: #62803f;
        }

        .step-card-title {
            color: #ffffff !important;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .step-card-desc {
            color: #a7a59d !important;
            font-size: 0.9rem;
            line-height: 1.7;
            margin-bottom: 0;
        }

        .guarantee-card {
            background-color: #0f110e;
            border: 1px solid #1c2217;
            border-radius: 12px;
            padding: 2.2rem 1.75rem;
            height: 100%;
            transition: border-color 0.2s ease;
        }

        .guarantee-card:hover {
            border-color: #364222;
        }

        .faq-accordion .accordion-item {
            background-color: #0f110e;
            border: 1px solid #1c2217;
            border-radius: 10px !important;
            margin-bottom: 0.75rem;
            overflow: hidden;
        }

        .faq-accordion .accordion-button {
            background-color: #0f110e;
            color: #ffffff !important;
            font-weight: 600;
            font-size: 0.98rem;
            padding: 1.15rem 1.35rem;
            box-shadow: none !important;
            border: none;
        }

        .faq-accordion .accordion-button:not(.collapsed) {
            background-color: #131710;
            color: #ffffff !important;
            border-bottom: 1px solid #1c2217;
        }

        .faq-accordion .accordion-button::after {
            filter: brightness(0) invert(0.7);
        }

        .faq-accordion .accordion-body {
            background-color: #0c0e0b;
            color: #a7a59d;
            font-size: 0.92rem;
            line-height: 1.65;
            padding: 1.25rem 1.35rem;
        }
    </style>
</head>

<body class="bg-dark-ppi text-second-ppi">

    <?php include __DIR__ . "/NavBar/nav.php"; ?>

    <section class="hero-ppi hero-como-funciona text-center" aria-labelledby="como-funciona-titulo">
        <div class="container-fluid px-4 px-lg-5">
            <div class="row justify-content-center">
                <div class="col-12">
                    <p class="hero-label mb-3 d-inline-block">Guía oficial · Gavel.net</p>
                    <h1 id="como-funciona-titulo" class="hero-titulo text-main-ppi mb-3">
                        ¿Cómo funciona <span class="text-third-ppi">Gavel.net</span>?
                    </h1>
                    <p class="text-second-ppi mb-4 mx-auto" style="max-width: 660px; font-size: 1.15rem; line-height: 1.7;">
                        Conoce el funcionamiento de nuestra plataforma de subastas en vivo.
                        Aprende cómo realizar ofertas transparentes o subastar tus artículos al mejor postor.
                    </p>

                    <div class="role-switch-container">
                        <ul class="nav nav-pills border-0 p-0 m-0" id="roleTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="role-switch-btn active" id="tab-comprador" data-bs-toggle="pill" data-bs-target="#content-comprador" type="button" role="tab" aria-controls="content-comprador" aria-selected="true">
                                    <i class="fas fa-gavel me-2 text-third-ppi"></i>Quiero Comprar
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="role-switch-btn" id="tab-vendedor" data-bs-toggle="pill" data-bs-target="#content-vendedor" type="button" role="tab" aria-controls="content-vendedor" aria-selected="false">
                                    <i class="fas fa-tags me-2 text-third-ppi"></i>Quiero Vender
                                </button>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <main class="container-fluid px-4 px-lg-5 py-5">
        <div class="container py-2" style="max-width: 1200px;">

            <div class="tab-content" id="roleTabsContent">

                <div class="tab-pane fade show active" id="content-comprador" role="tabpanel" aria-labelledby="tab-comprador">
                    <div class="text-center mb-5">
                        <h2 class="section-header-title mb-2">Pasos para comprar y pujar</h2>
                        <p class="section-header-sub small mb-0">Compite de manera justa y transparente por artículos únicos</p>
                    </div>

                    <div class="row g-4 mb-5">
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="step-card">
                                <span class="step-num">01</span>
                                <h3 class="step-card-title">
                                    <i class="fas fa-search text-third-ppi"></i>
                                    Explora Artículos
                                </h3>
                                <p class="step-card-desc">
                                    Filtra por categorías como Arte, Tecnología o Joyería y encuentra artículos que te apasionen.
                                </p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="step-card">
                                <span class="step-num">02</span>
                                <h3 class="step-card-title">
                                    <i class="fas fa-user-plus text-third-ppi"></i>
                                    Crea tu Cuenta
                                </h3>
                                <p class="step-card-desc">
                                    El registro es gratis. Inicia sesión para que tus pujas queden formalmente registradas.
                                </p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="step-card">
                                <span class="step-num">03</span>
                                <h3 class="step-card-title">
                                    <i class="fas fa-hand-holding-usd text-third-ppi"></i>
                                    Envía tu Oferta
                                </h3>
                                <p class="step-card-desc">
                                    Ingresa un valor superior a la puja actual respetando el incremento mínimo del artículo.
                                </p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="step-card">
                                <span class="step-num">04</span>
                                <h3 class="step-card-title">
                                    <i class="fas fa-trophy text-third-ppi"></i>
                                    Gana la Subasta
                                </h3>
                                <p class="step-card-desc">
                                    Si mantienes la puja más alta al cerrarse el cronómetro, la pieza es tuya y coordinas el pago.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3 mb-5">
                        <a href="<?= $base_url ?>views/catalogo.php" class="btn btn-primary px-4 py-2 fw-semibold">
                            <i class="fas fa-search me-2"></i>Explorar subastas activas
                        </a>
                    </div>
                </div>

                <div class="tab-pane fade" id="content-vendedor" role="tabpanel" aria-labelledby="tab-vendedor">
                    <div class="text-center mb-5">
                        <h2 class="section-header-title mb-2">Pasos para publicar y vender</h2>
                        <p class="section-header-sub small mb-0">Muestra tus artículos a cientos de compradores interesados</p>
                    </div>

                    <div class="row g-4 mb-5">
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="step-card">
                                <span class="step-num">01</span>
                                <h3 class="step-card-title">
                                    <i class="fas fa-box-open text-third-ppi"></i>
                                    Sube tu Producto
                                </h3>
                                <p class="step-card-desc">
                                    Carga fotografías claras del producto y describe sus características en el formulario.
                                </p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="step-card">
                                <span class="step-num">02</span>
                                <h3 class="step-card-title">
                                    <i class="fas fa-sliders-h text-third-ppi"></i>
                                    Fija Condiciones
                                </h3>
                                <p class="step-card-desc">
                                    Establece el precio base inicial, la fecha de inicio y la duración que tendrá tu subasta.
                                </p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="step-card">
                                <span class="step-num">03</span>
                                <h3 class="step-card-title">
                                    <i class="fas fa-check-circle text-third-ppi"></i>
                                    Espera Validación
                                </h3>
                                <p class="step-card-desc">
                                    Tu subasta se envía a revisión para verificar su contenido antes de ser publicada oficialmente.
                                </p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="step-card">
                                <span class="step-num">04</span>
                                <h3 class="step-card-title">
                                    <i class="fas fa-gavel text-third-ppi"></i>
                                    Recibe Pujas
                                </h3>
                                <p class="step-card-desc">
                                    Observa cómo compiten los postores en vivo y concreta la venta con el ganador al finalizar.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3 mb-5">
                        <a href="<?= $base_url ?>views/subastas/registro-producto.php" class="btn btn-outline-publish px-4 py-2 fw-semibold">
                            <i class="fas fa-plus me-2"></i>Publicar mi primer artículo
                        </a>
                    </div>
                </div>

            </div>

            <div class="py-5 my-3" style="border-top: 1px solid #191c17;">
                <div class="text-center mb-5">
                    <p class="hero-label mb-2 d-inline-block">Seguridad · Confianza</p>
                    <h2 class="section-header-title mb-2">Garantías de la Plataforma</h2>
                    <p class="section-header-sub small mb-0">Un entorno transparente y protegido para todas tus operaciones</p>
                </div>

                <div class="row g-4">
                    <div class="col-12 col-md-4">
                        <div class="guarantee-card">
                            <h3 class="step-card-title mb-2">
                                <i class="fas fa-shield-alt text-third-ppi"></i>
                                Ofertas Públicas
                            </h3>
                            <p class="step-card-desc">
                                Cada puja queda registrada con su fecha, hora exacta y postor de forma verificable. Sin manipulaciones ni pujas ocultas.
                            </p>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="guarantee-card">
                            <h3 class="step-card-title mb-2">
                                <i class="fas fa-clock text-third-ppi"></i>
                                Cierre Sincronizado
                            </h3>
                            <p class="step-card-desc">
                                Los cronómetros están vinculados con el reloj del servidor para garantizar igualdad de condiciones hasta el último segundo.
                            </p>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="guarantee-card">
                            <h3 class="step-card-title mb-2">
                                <i class="fas fa-user-check text-third-ppi"></i>
                                Comunidad Verificada
                            </h3>
                            <p class="step-card-desc">
                                Supervisión activa de artículos publicados y canales de soporte directo ante cualquier duda o eventualidad.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="py-5 my-3" style="border-top: 1px solid #191c17;">
                <div class="text-center mb-5">
                    <p class="hero-label mb-2 d-inline-block">Dudas frecuentes</p>
                    <h2 class="section-header-title mb-2">Preguntas y Respuestas</h2>
                </div>

                <div class="row justify-content-center">
                    <div class="col-12 col-lg-10">
                        <div class="accordion faq-accordion" id="faqAccordion">

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                        ¿Tiene algún costo registrarse o participar en una subasta?
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        No. Registrarte, explorar el catálogo y pujar en Gavel.net es <strong>completamente gratuito</strong>. Solo abonas el importe correspondiente si ganas formalmente la subasta.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        ¿Puedo cancelar una puja después de confirmarla?
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        No. Para preservar la transparencia y seriedad de cada subasta, <strong>las pujas son definitivas</strong> una vez registradas en el sistema.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        ¿Cómo se coordina el pago y la entrega del producto?
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Al concluir la subasta, tanto el comprador ganador como el vendedor tienen acceso a los datos de contacto necesarios para coordinar el método de pago preferido y la forma de entrega o envío.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        ¿Cómo se garantiza la transparencia de las pujas y el tiempo restante?
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Cada puja queda registrada públicamente con fecha, hora exacta y postor de forma verificable. Asimismo, el temporizador de cierre está sincronizado con el servidor para evitar discrepancias de horario.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFive">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                        ¿Qué ocurre si nadie puja por un artículo publicado?
                                    </button>
                                </h2>
                                <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Si la subasta llega a su fin sin recibir ofertas, el artículo se marcará como cerrado sin ganador y el vendedor podrá republicarlo ajustando el precio base o la descripción cuando lo considere oportuno.
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center pt-5 pb-3 mt-4" style="border-top: 1px solid #191c17;">
                <h2 class="section-header-title mb-2" style="color: #ffffff !important;">¿Listo para empezar?</h2>
                <p class="section-header-sub small mb-4 mx-auto" style="max-width: 520px;">
                    Encuentra piezas únicas con ofertas en tiempo real o publica tus artículos al mejor postor.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="<?= $base_url ?>views/catalogo.php" class="btn btn-primary px-4 py-2 fw-semibold">
                        Explorar subastas
                    </a>
                    <a href="<?= $base_url ?>views/subastas/registro-producto.php" class="btn btn-outline-publish px-4 py-2 fw-semibold">
                        Publicar artículo
                    </a>
                </div>
            </div>

        </div>
    </main>

    <?php include __DIR__ . "/Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
