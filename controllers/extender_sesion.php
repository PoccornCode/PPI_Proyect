<?php
header('Content-Type: application/json; charset=UTF-8');

require_once dirname(__DIR__) . '/config/env.php';
require_once dirname(__DIR__) . '/helpers/sesion_inactividad.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Método no permitido.'
    ]);
    exit;
}

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode([
        'ok' => false,
        'expired' => true,
        'mensaje' => 'No hay una sesión activa.'
    ]);
    exit;
}

// Verifica si ya venció en servidor; si sigue vigente, renueva $_SESSION['last_activity']
verificarInactividadSesion(true);

echo json_encode([
    'ok' => true,
    'lastActivity' => $_SESSION['last_activity'],
    'timeout' => SESSION_INACTIVITY_TIMEOUT,
    'warningBefore' => SESSION_WARNING_BEFORE
]);
exit;
