<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once __DIR__ . '/../dao/detalleSubastaDAO.php';
require_once __DIR__ . '/../dao/SubastaDAO.php';

$id_subasta = trim($_POST['id_subasta'] ?? 0);
$precio_inicial = trim($_POST['precio_inicial'] ?? 0);
$incremento_minimo = trim($_POST['$incremento_minimo'] ?? 0);
$fecha_inicial = trim($_POST['fecha_inicial'] ?? '');
$fecha_final = trim($_POST['fecha_final'] ?? '');
$estado = trim($_POST['estado'] ?? 0);
$id_producto = trim($_POST['id_producto'] ?? 0);
$id_usuario = trim($_POST['id_usuario'] ?? '0');
$option = trim($_POST['opcion'] ?? '');
$monto = trim($_POST['monto_puja'] ?? '0');

$dao = new subastaDetalleDao();

if ($option == 'consultar') {
    try {
        (new SubastaDAO())->finalizarSubastasVencidas((int)$id_subasta);
    } catch (Throwable $e) {
        error_log('[DetalleSubasta] Error al finalizar subasta vencida: ' . $e->getMessage());
    }
    $respuesta = $dao->getSubastaDao($id_subasta);
    echo json_encode($respuesta);
}
if ($option == 'consultarSesion') {
    echo json_encode(
        [
            "idUsuario" => $_SESSION['id_usuario'] ?? 0
        ]
    );
}
if ($option == 'guardarPuja') {
    $respuesta = $dao->guardarPuja($monto, $id_usuario, $id_subasta);
    echo json_encode($respuesta);
}
if ($option == "consultarPujas") {
    $respuesta = $dao->getPujas($id_subasta);
    echo json_encode($respuesta);
}
?>