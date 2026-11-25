<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../dao/detalleSubastaDAO.php';

$id_subasta = trim($_POST['id_subasta'] ?? 0);
$precio_inicial = trim($_POST['precio_inicial'] ?? 0);
$incremento_minimo = trim($_POST['$incremento_minimo'] ?? 0);
$fecha_inicial = trim($_POST['fecha_inicial'] ?? '');
$fecha_final = trim($_POST['fecha_final'] ?? '');
$estado = trim($_POST['estado'] ?? 0);
$id_producto = trim($_POST['id_producto'] ?? 0);
$option = trim($_POST['opcion']??'');

$dao = new subastaDao();

if ($option == 'consultar') {
    $respuesta = $dao->getSubastaDao($id_subasta);
    echo json_encode($respuesta);
}
if($option == 'consultarSesion'){
    echo json_encode(
        [
            "idUsuario"=>$_SESSION['id_usuario']??0
        ]
    );
}

?>