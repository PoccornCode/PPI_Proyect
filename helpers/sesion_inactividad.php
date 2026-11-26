<?php
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/env.php';
}

// Tiempo total de inactividad permitido antes de cerrar sesión (15 minutos = 900 segundos)
if (!defined('SESSION_INACTIVITY_TIMEOUT')) {
    define('SESSION_INACTIVITY_TIMEOUT', 900);
}

// Tiempo de advertencia previa antes de cerrar sesión (1 minuto = 60 segundos, avisa al minuto 14)
if (!defined('SESSION_WARNING_BEFORE')) {
    define('SESSION_WARNING_BEFORE', 60);
}

if (!function_exists('destruirSesionActual')) {
    function destruirSesionActual(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}

if (!function_exists('verificarInactividadSesion')) {
    /**
     * Verifica si la sesión del usuario autenticado ha expirado por inactividad.
     * Si expiró, destruye la sesión y redirige al login .
     */
    function verificarInactividadSesion(bool $esApiJson = false, bool $renovarActividad = true): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['id_usuario'])) {
            return;
        }

        $ahora = time();
        $ultimaActividad = $_SESSION['last_activity'] ?? $ahora;

        if (($ahora - $ultimaActividad) > SESSION_INACTIVITY_TIMEOUT) {
            destruirSesionActual();

            if ($esApiJson) {
                http_response_code(401);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'ok' => false,
                    'estado' => false,
                    'expired' => true,
                    'mensaje' => 'Tu sesión ha expirado por inactividad.'
                ]);
                exit;
            }

            if (!headers_sent()) {
                header('Location: ' . BASE_URL . 'views/auth/login.php?reason=inactivity');
                exit;
            }
            return;
        }

        if ($renovarActividad) {
            $_SESSION['last_activity'] = $ahora;
        }
    }
}
