<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__, 2) . '/config/env.php';
$titulo = "Política de Cookies";
$pagina = "cookies";
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

            <h1 class="legal-header-title">Política de Cookies</h1>
            <p class="legal-lead">
                Información transparente sobre el uso de cookies técnicas y de preferencias en Gavel.net.
            </p>

            <article>
                <h2 class="legal-section-title">1. ¿Qué son las Cookies?</h2>
                <p class="legal-text">
                    Las cookies son pequeños ficheros que se guardan en tu navegador web. Permiten reconocer tu sesión entre diferentes páginas para que no tengas que iniciar sesión continuamente mientras exploras y participas en las subastas.
                </p>

                <h2 class="legal-section-title">2. ¿Qué Cookies Utilizamos?</h2>
                <p class="legal-text">
                    En Gavel.net <span class="legal-important">no empleamos cookies publicitarias ni de seguimiento cruzado invasivo</span>. Únicamente utilizamos cookies esenciales:
                </p>
                <ul class="legal-list">
                    <li><strong class="legal-important">Cookies técnicas de sesión:</strong> Indispensables para autenticar tu cuenta, asegurar las peticiones de pujas y mantener activa tu navegación de forma segura.</li>
                    <li><strong class="legal-important">Cookies de preferencias:</strong> Guardan de forma local tu decisión frente al aviso informativo de cookies para no incomodarte en visitas posteriores.</li>
                </ul>

                <h2 class="legal-section-title">3. Cookies Activas en la Plataforma</h2>
                <div class="table-responsive">
                    <table class="table table-cookies">
                        <thead>
                            <tr>
                                <th>Cookie</th>
                                <th>Tipo</th>
                                <th>Finalidad</th>
                                <th>Duración</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code class="text-third-ppi fs-6">PHPSESSID</code></td>
                                <td><span class="legal-important">Esencial</span></td>
                                <td>Identificador de sesión PHP del usuario autenticado.</td>
                                <td>Sesión activa</td>
                            </tr>
                            <tr>
                                <td><code class="text-third-ppi fs-6">gavel_cookie_consent</code></td>
                                <td>Preferencia</td>
                                <td>Almacena la confirmación del aviso de cookies.</td>
                                <td>1 año</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h2 class="legal-section-title">4. Control y Configuración</h2>
                <p class="legal-text">
                    Puedes borrar o bloquear las cookies desde las opciones de configuración de tu navegador en cualquier momento. <span class="legal-important">Ten en cuenta que si desactivas las cookies de sesión, no podrás iniciar sesión ni enviar ofertas en vivo</span>.
                </p>
                <div class="pt-3 pb-5">
                    <button type="button" class="btn btn-outline-publish px-4 py-2 fw-semibold me-2" onclick="if(window.resetCookieConsent){ window.resetCookieConsent(); } else { alert('Configuración reiniciada'); }">
                        Restablecer aviso de cookies
                    </button>
                    <a href="<?= $base_url ?>views/legal/privacidad.php" class="btn btn-dark border-secondary px-4 py-2 text-light">
                        Ver Política de Privacidad
                    </a>
                </div>
            </article>

        </div>
    </main>

    <?php include dirname(__DIR__) . "/Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
