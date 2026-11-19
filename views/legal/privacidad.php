<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__, 2) . '/config/env.php';
$titulo = "Política de Privacidad";
$pagina = "privacidad";
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

<body class="bg-dark-ppi d-flex flex-column min-vh-100">

    <?php include dirname(__DIR__) . "/NavBar/nav.php"; ?>

    <main class="flex-grow-1 py-5">
        <div class="legal-article-container">

            <h1 class="legal-header-title">Política de Privacidad</h1>
            <p class="legal-lead">
                Tratamiento responsable y protección integral de tus datos en cada puja y transacción dentro de Gavel.net.
            </p>

            <article>
                <h2 class="legal-section-title">1. Responsable del Tratamiento</h2>
                <p class="legal-text">
                    La plataforma Gavel.net es un entorno web desarrollado para la gestión de subastas de bienes en línea. Los datos recopilados se procesan exclusivamente para gestionar el registro de usuarios, registrar ofertas en tiempo real y permitir la comunicación directa al momento del cierre de cada lote.
                </p>

                <h2 class="legal-section-title">2. Datos que Recopilamos</h2>
                <p class="legal-text">
                    Para el correcto funcionamiento del sistema, recopilamos únicamente la información necesaria:
                </p>
                <ul class="legal-list">
                    <li><strong>Datos de cuenta:</strong> Nombre, apellidos, usuario y correo electrónico verificado.</li>
                    <li><strong>Datos de contacto:</strong> Teléfono y dirección de entrega para la coordinación con el vendedor.</li>
                    <li><strong>Actividad de subasta:</strong> Historial de lotes creados, ofertas emitidas y adjudicaciones ganadas.</li>
                    <li><strong>Registros de sistema:</strong> Dirección IP, fecha y hora exacta de pujas y cookies esenciales para mantener tu sesión activa.</li>
                </ul>
                <p class="legal-text">
                    <span class="legal-important">Seguridad de credenciales:</span> Las contraseñas <strong class="legal-important">nunca se guardan en texto plano</strong>; son procesadas con algoritmos de dispersión criptográfica unidireccional segura.
                </p>

                <h2 class="legal-section-title">3. Uso de la Información</h2>
                <p class="legal-text">
                    Utilizamos tus datos para:
                </p>
                <ul class="legal-list">
                    <li>Autenticar tu acceso a la plataforma.</li>
                    <li>Validar y procesar pujas de forma equitativa.</li>
                    <li>Enviar notificaciones inmediatas sobre el estado de tus subastas u ofertas superadas.</li>
                    <li>Prevenir fraudes, cuentas duplicadas o conductas contrarias a las reglas de la plataforma.</li>
                </ul>

                <h2 class="legal-section-title">4. Visibilidad Pública en Subastas</h2>
                <p class="legal-text">
                    Para asegurar la transparencia del proceso de subasta, el historial de ofertas de cada lote muestra públicamente el nombre de usuario y el monto ofrecido. <span class="legal-important">Tu correo electrónico, teléfono y dirección personal permanecen estrictamente privados y protegidos</span> en todo momento frente a otros postores.
                </p>

                <h2 class="legal-section-title">5. Tus Derechos</h2>
                <p class="legal-text">
                    Tienes derecho a consultar, corregir o solicitar la eliminación de tus datos en cualquier momento. Puedes editar tu perfil directamente desde tu panel de usuario o comunicarte con nuestro equipo si necesitas asistencia técnica o dar de baja tu cuenta.
                </p>
                <div class="pt-3 pb-5">
                    <a href="<?= $base_url ?>views/contacto/contacto.php" class="btn btn-outline-publish px-4 py-2 fw-semibold me-2">
                        Contactar con soporte
                    </a>
                    <a href="<?= $base_url ?>views/legal/terminos.php" class="btn btn-dark border-secondary px-4 py-2 text-light">
                        Ver Términos y Condiciones
                    </a>
                </div>
            </article>

        </div>
    </main>

    <?php include dirname(__DIR__) . "/Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
