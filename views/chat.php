<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../dao/ChatDAO.php';

$id_subasta = (int)($_GET['subasta'] ?? 0);
$urlChat = BASE_URL . 'views/chat.php?subasta=' . $id_subasta;

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['redirect_after_login'] = $urlChat;
    $_SESSION['flash_toast'] = [
        'tipo' => 'warning',
        'titulo' => 'Acceso restringido',
        'mensaje' => 'Debes iniciar sesión para ver el chat.'
    ];
    header("Location: " . BASE_URL . "views/auth/login.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$chat = puedeChatear($conexion, $id_subasta, $id_usuario);

$urlVolver = ($chat && $chat['rol'] === 'vendedor')
    ? BASE_URL . 'views/subastas/mis-productos.php'
    : BASE_URL . 'index.php';
$imagen = ($chat && !empty($chat['imagen']))
    ? BASE_URL . 'uploads/productos/' . $chat['imagen']
    : BASE_URL . 'assets/img/hero-img.jpg';

$titulo = "Chat";
$pagina = "chat";
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
    <link href="<?= BASE_URL ?>assets/css/forms.css?v=14" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/chat.css?v=1" rel="stylesheet">
</head>

<body class="bg-dark-ppi d-flex flex-column min-vh-100">

    <?php include __DIR__ . "/NavBar/nav.php"; ?>

    <main class="flex-grow-1 py-4">
        <div class="container chat-contenedor">

            <?php if (!$chat): ?>
                <div class="chat-caja chat-sin-acceso">
                    <i class="fas fa-lock"></i>
                    <h5 class="text-main-ppi fw-bold mb-2">No puedes abrir este chat</h5>
                    <p class="text-second-ppi small mb-3">El chat solo está disponible para el vendedor y el ganador de una subasta finalizada.</p>
                    <a href="<?= BASE_URL ?>index.php" class="btn btn-primary btn-sm px-4">Volver al inicio</a>
                </div>
            <?php else: ?>
                <div class="chat-caja">

                    <div class="chat-cabecera">
                        <a href="<?= $urlVolver ?>" class="chat-btn-volver" title="Volver" aria-label="Volver">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <img src="<?= htmlspecialchars($imagen) ?>" alt="" class="chat-cabecera-img" onerror="this.onerror=null; this.src='<?= BASE_URL ?>assets/img/hero-img.jpg';">
                        <div class="chat-cabecera-texto">
                            <div class="chat-cabecera-nombre text-truncate"><?= htmlspecialchars($chat['otro_nombre']) ?></div>
                            <div class="chat-cabecera-producto text-truncate">
                                <?= $chat['rol'] === 'vendedor' ? 'Ganador de' : 'Vendedor de' ?>: <?= htmlspecialchars($chat['producto']) ?>
                            </div>
                        </div>
                    </div>

                    <div class="chat-mensajes" id="chatMensajes"
                        data-id-subasta="<?= $id_subasta ?>"
                        data-url="<?= BASE_URL ?>controllers/chat_controller.php"></div>

                    <form class="chat-form" id="formMensaje" autocomplete="off">
                        <textarea class="form-control chat-input" name="mensaje" id="inputMensaje" rows="1" maxlength="1000" placeholder="Escribe un mensaje..." required></textarea>
                        <button type="submit" class="btn btn-primary chat-btn-enviar" id="btnEnviar" aria-label="Enviar mensaje">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>

                </div>
            <?php endif; ?>

        </div>
    </main>

    <?php include __DIR__ . "/Footer/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/toast-ppi.js?v=2"></script>

    <?php if ($chat): ?>
        <script src="<?= BASE_URL ?>assets/js/chat.js?v=1"></script>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_toast'])): ?>
        <?php
        $flash = $_SESSION['flash_toast'];
        unset($_SESSION['flash_toast']);
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                mostrarToast(
                    <?= json_encode($flash['tipo'] ?? 'success') ?>,
                    <?= json_encode($flash['titulo'] ?? '') ?>,
                    <?= json_encode($flash['mensaje'] ?? '') ?>,
                    5000
                );
            });
        </script>
    <?php endif; ?>

</body>

</html>