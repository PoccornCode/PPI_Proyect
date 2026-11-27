<?php
require_once __DIR__ . '/../dao/ProductoAdminDAO.php';
require_once __DIR__ . '/admin_controller.php';

function obtenerProductosSinSubastaAdmin(string $baseUrl = ''): array
{
    $dao = new ProductoAdminDAO();
    return $dao->listarProductosSinSubasta($baseUrl);
}

function obtenerDetalleProductoAdmin(int $idProducto, string $baseUrl = ''): ?array
{
    $dao = new ProductoAdminDAO();
    return $dao->obtenerDetalleProducto($idProducto, $baseUrl);
}

function actualizarProductoAdmin(
    int $idProducto,
    string $nombre,
    string $descripcion,
    string $condicion,
    int $idCategoria,
    int $estado
): array {
    $dao = new ProductoAdminDAO();
    return $dao->actualizarProducto($idProducto, $nombre, $descripcion, $condicion, $idCategoria, $estado);
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

    if ($opcion === 'detalle') {
        $idProducto = (int)($_GET['id_producto'] ?? $_POST['id_producto'] ?? 0);
        if ($idProducto <= 0) {
            http_response_code(400);
            echo json_encode([
                'estado'  => false,
                'mensaje' => 'ID de producto inválido.',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        $producto = obtenerDetalleProductoAdmin($idProducto, BASE_URL);
        if (!$producto) {
            http_response_code(404);
            echo json_encode([
                'estado'  => false,
                'mensaje' => 'No se encontró el producto solicitado.',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        echo json_encode([
            'estado'   => true,
            'producto' => $producto,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($opcion === 'actualizar') {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'estado'  => false,
                'mensaje' => 'Método no permitido.',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        $idProducto = (int)($_POST['id_producto'] ?? 0);
        $nombre = (string)($_POST['nombre'] ?? '');
        $descripcion = (string)($_POST['descripcion'] ?? '');
        $condicion = (string)($_POST['condicion'] ?? '');
        $idCategoria = (int)($_POST['id_categoria'] ?? 0);
        $estado = (int)($_POST['estado'] ?? 0);

        $resultado = actualizarProductoAdmin($idProducto, $nombre, $descripcion, $condicion, $idCategoria, $estado);
        if (!$resultado['estado']) {
            http_response_code(400);
        }
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    http_response_code(400);
    echo json_encode([
        'estado'  => false,
        'mensaje' => 'Opción no válida.',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
