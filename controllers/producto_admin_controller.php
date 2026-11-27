<?php
require_once __DIR__ . '/../dao/ProductoAdminDAO.php';
require_once __DIR__ . '/admin_controller.php';

function obtenerProductosSinSubastaAdmin(string $baseUrl = ''): array
{
    $dao = new ProductoAdminDAO();
    return $dao->listarProductosSinSubasta($baseUrl);
}

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!defined('BASE_URL')) {
        require_once __DIR__ . '/../config/env.php';
    }

    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    $esPollFondo = (($_GET['bg'] ?? '') === '1');
    requerirAdministradorAutenticado(true, !$esPollFondo);

    $opcion = $_POST['opcion'] ?? $_GET['opcion'] ?? 'listar';

    if ($opcion === 'listar') {
        $productos = obtenerProductosSinSubastaAdmin(BASE_URL);
        echo json_encode([
            'estado'    => true,
            'productos' => $productos,
            'total'     => count($productos),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    http_response_code(400);
    echo json_encode([
        'estado'  => false,
        'mensaje' => 'Opción no válida.',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
