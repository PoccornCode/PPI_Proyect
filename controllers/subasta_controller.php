<?php
require_once __DIR__ . '/../dao/SubastaDAO.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$precio_inicial= (float)($_POST['precio_inicial'] ?? 0);
$incremento_minimo= (float)($_POST['incremento_minimo'] ?? 0);
$dias=(int)($_POST['dias']??0);
$id_producto = (int)($_POST['id_producto'] ?? 0);
$id_usuario = (int)($_SESSION['id_usuario'] ?? 0);
$tipo_solicitud = $_POST['tipo_solicitud'] ?? 'nueva';
$nota_vendedor  = trim($_POST['nota_vendedor'] ?? '');
if (!in_array($tipo_solicitud, ['nueva', 'reenvio', 'apelacion'], true)) {
    $tipo_solicitud = 'nueva';
}

$nota_vendedor = $nota_vendedor === '' ? null : $nota_vendedor;

function obtenerSubastaDestacadaHome(): ?array
{
    $dao = new SubastaDAO();
    return $dao->obtenerDestacada();
}

function obtenerSubastasTerminanProntoHome(int $limite = 4, ?int $excluirId = null): array
{
    $dao = new SubastaDAO();
    return $dao->obtenerTerminanPronto($limite, $excluirId);
}

function obtenerNuevasSubastasHome(int $limite = 4): array
{
    $dao = new SubastaDAO();
    return $dao->obtenerNuevas($limite);
}

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    header("Content-Type: application/json; charset=UTF-8");
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    $opcion = $_POST['opcion'] ?? $_GET['opcion'] ?? 'home';

    if ($opcion === 'home') {
        $destacada = obtenerSubastaDestacadaHome();
        echo json_encode([
            'estado'    => true,
            'destacada' => $destacada,
            'pronto'    => obtenerSubastasTerminanProntoHome(5, $destacada['id'] ?? null),
            'nuevas'    => obtenerNuevasSubastasHome(4),
        ]);
        exit;
    }

    if ($opcion === "insertar") {
        if ($id_usuario === 0) {
            echo json_encode(['estado' => false, 'mensaje' => 'Debes iniciar sesión para subastar.']);
            exit;
        }
        
        if ($precio_inicial <= 0 || $incremento_minimo <= 0 || $dias < 1 || $dias > 30) {
            echo json_encode(['estado' => false, 'mensaje' => 'Revisa los precios y la duración.']);
            exit;
        }
        if ($tipo_solicitud === 'apelacion' && $nota_vendedor === null) {
            echo json_encode(['estado' => false, 'mensaje' => 'Debes explicar el motivo de tu apelación.']);
            exit;
        }
        
        echo json_encode((new SubastaDAO())->insertarSubasta($precio_inicial, $incremento_minimo, $dias, $id_producto, $id_usuario, $tipo_solicitud, $nota_vendedor));
        exit;
    }
    echo json_encode(['estado' => false, 'mensaje' => 'Opción no válida']);
    exit;
}




