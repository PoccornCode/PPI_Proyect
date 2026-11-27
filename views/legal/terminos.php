<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__, 2) . '/config/env.php';
$titulo = "Términos y Condiciones";
$pagina = "terminos";
$base_url = BASE_URL;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($titulo) ?> | Gavel.net</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <?php include dirname(__DIR__, 2) . '/includes/meta_tags.php'; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Raleway:wght@400;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="<?= $base_url ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $base_url ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= $base_url ?>assets/css/main.css?v=33" rel="stylesheet">
    <link href="<?= $base_url ?>assets/css/legal.css" rel="stylesheet">
</head>

<body style="background-color: #080708; color: #cfd2ca; min-height: 100vh; display: flex; flex-direction: column;">

    <?php include dirname(__DIR__) . "/NavBar/nav.php"; ?>

    <main class="flex-grow-1 py-5">
        <div class="legal-article-container">

            <h1 class="legal-header-title">Términos y Condiciones</h1>
            <p class="legal-lead">
                Reglas operativas y compromisos de uso para compradores y vendedores en la plataforma Gavel.net.
            </p>

            <article>
                <h2 class="legal-section-title">1. Aceptación de las Normas</h2>
                <p class="legal-text">
                    Al registrarte o participar en subastas en Gavel.net, <span class="legal-important">aceptas de manera vinculante cumplir con los presentes términos</span>. Si no estás de acuerdo con alguna de estas directrices, debes abstenerte de utilizar nuestros servicios.
                </p>

                <h2 class="legal-section-title">2. Cuentas y Responsabilidad</h2>
                <p class="legal-text">
                    Cada participante es responsable del resguardo y uso de sus credenciales. <span class="legal-important">Queda estrictamente prohibida la creación de cuentas múltiples</span> con fines especulativos, el uso de identidades falsas o el empleo de scripts automáticos para alterar el ritmo natural de las pujas.
                </p>

                <h2 class="legal-section-title">3. Reglas de Subasta y Ofertas</h2>
                <p class="legal-text">
                    Toda oferta confirmada en el sistema se considera seria y formal:
                </p>
                <ul class="legal-list">
                    <li><strong class="legal-important">Ofertas irrevocables:</strong> Una puja realizada no puede ser cancelada unilateralmente por el postor bajo ninguna circunstancia.</li>
                    <li><strong>Incrementos válidos:</strong> Cada oferta debe superar el monto actual según las reglas fijadas para el lote.</li>
                    <li><strong class="legal-important">Cierre definitivo:</strong> Al finalizar el contador regresivo, el usuario con la oferta más alta registrada se convierte automáticamente en el ganador legítimo.</li>
                </ul>
                <p class="legal-text">
                    <span class="legal-important">Compromiso obligatorio:</span> El vendedor se compromete a entregar el artículo tal como se describió en la publicación, y el ganador a concretar el pago y la entrega en el tiempo convenido.
                </p>

                <h2 class="legal-section-title">4. Artículos No Permitidos</h2>
                <p class="legal-text">
                    <span class="legal-important">No se autoriza bajo ninguna excepción</span> la publicación de artículos prohibidos por la ley, réplicas no declaradas, bienes de procedencia dudosa o productos que vulneren derechos de autor o propiedad intelectual.
                </p>

                <h2 class="legal-section-title">5. Limitación de Responsabilidad</h2>
                <p class="legal-text">
                    Gavel.net opera como punto de encuentro tecnológico para la gestión de subastas. No asume responsabilidad directa por acuerdos externos realizados fuera de las herramientas del sistema o por fallos en servicios de conectividad ajenos a la plataforma.
                </p>
                <div class="pt-3 pb-5 d-flex flex-wrap gap-3">
                    <a href="<?= $base_url ?>views/legal/privacidad.php" class="btn btn-outline-publish px-4 py-2 fw-semibold">
                        Ver Política de Privacidad
                    </a>
                    <a href="<?= $base_url ?>views/como-funciona.php" class="btn btn-dark border-secondary px-4 py-2 text-light">
                        Guía Cómo Funciona
                    </a>
                </div>
            </article>

        </div>
    </main>

    <?php include dirname(__DIR__) . "/Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
