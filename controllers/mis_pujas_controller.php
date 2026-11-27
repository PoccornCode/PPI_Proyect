<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['estado' => false, 'mensaje' => 'Sesión no iniciada.']);
    exit;
}

require_once __DIR__ . '/../dao/MisPujasDAO.php';

try {
    $dao = new MisPujasDAO();
    $pujas = $dao->obtenerPorUsuario((int)$_SESSION['id_usuario']);
    echo json_encode(['estado' => true, 'pujas' => $pujas], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[mis_pujas_controller] Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['estado' => false, 'mensaje' => 'No se pudieron actualizar las pujas.']);
}
