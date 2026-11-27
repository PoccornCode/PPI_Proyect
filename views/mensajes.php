<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['redirect_after_login'] = BASE_URL . 'views/mensajes.php';
    $_SESSION['flash_toast'] = [
        'tipo' => 'warning',
        'titulo' => 'Acceso restringido',
        'mensaje' => 'Debes iniciar sesión para ver tus mensajes.'
    ];
    header("Location: " . BASE_URL . "views/auth/login.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_chat = (int)($_GET['chat'] ?? 0);
$id_subasta = (int)($_GET['subasta'] ?? 0);
$titulo = "Mensajes";
$pagina = "mensajes";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> | Gavel.net</title>
    <?php include dirname(__DIR__) . '/includes/meta_tags.php'; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@100;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />

    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/main.css?v=49" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/forms.css?v=14" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/mensajes.css?v=1" rel="stylesheet">
</head>

<body class="bg-dark-ppi d-flex flex-column min-vh-100">

    <?php include __DIR__ . "/NavBar/nav.php"; ?>

    <main class="flex-grow-1 py-4">
        <div class="container">

            <h2 class="text-main-ppi fw-bold mb-3">Mensajes</h2>

            <div class="chat-layout" id="chatLayout">

                <aside class="chat-panel chat-lista-panel">
                    <div class="chat-panel-header">Conversaciones</div>
                    <div class="chat-lista" id="chatLista">
                        <p class="chat-vacio">No tienes conversaciones.</p>
                    </div>
                </aside>

                <section class="chat-panel chat-conversacion-panel">

                    <div class="chat-sin-seleccion" id="chatSinSeleccion">
                        <i class="fas fa-comments"></i>
                        <p>Selecciona una conversación para ver los mensajes.</p>
                    </div>

                    <div class="chat-conversacion d-none" id="chatConversacion">
                        <div class="chat-panel-header chat-cabecera">
                            <button type="button" class="chat-btn-volver" id="btnChatVolver" aria-label="Volver a las conversaciones">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <img src="" alt="" class="chat-cabecera-img" id="chatCabeceraImg">
                            <div class="chat-cabecera-texto">
                                <div class="chat-cabecera-nombre text-truncate" id="chatCabeceraNombre"></div>
                                <div class="chat-cabecera-producto text-truncate" id="chatCabeceraProducto"></div>
                            </div>
                        </div>

                        <div class="chat-mensajes" id="chatMensajes">
                            <button type="button" class="chat-btn-anteriores d-none" id="btnCargarAnteriores">Cargar mensajes anteriores</button>
                        </div>

                        <form class="chat-form" id="formMensaje" autocomplete="off">
                            <input type="hidden" name="opcion" value="enviar">
                            <input type="hidden" name="id_chat" id="inputIdChat" value="<?= $id_chat ?>">
                            <textarea class="form-control chat-input" name="mensaje" id="inputMensaje" rows="1" maxlength="1000" placeholder="Escribe un mensaje..." required></textarea>
                            <button type="submit" class="btn btn-primary chat-btn-enviar" id="btnEnviarMensaje" aria-label="Enviar mensaje">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>

                </section>
            </div>

        </div>
    </main>

    <?php include __DIR__ . "/Footer/footer.php"; ?>

    <script>
        window.CHAT_CONFIG = {
            idUsuario: <?= $id_usuario ?>,
            idChat: <?= $id_chat ?>,
            idSubasta: <?= $id_subasta ?>,
            urlController: <?= json_encode(BASE_URL . 'controllers/chat_controller.php') ?>,
            urlImagenes: <?= json_encode(BASE_URL . 'uploads/productos/') ?>,
            imgRespaldo: <?= json_encode(BASE_URL . 'assets/img/hero-img.jpg') ?>
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/toast-ppi.js?v=2"></script>
    <script src="<?= BASE_URL ?>assets/js/mensajes.js?v=1"></script>
</body>

</html>
