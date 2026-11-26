<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../dao/ContactoDAO.php';

$opcion   = $_POST['opcion'] ?? '';
$nombre   = trim($_POST['nombre'] ?? '');
$correo   = trim($_POST['correo'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$asunto   = trim($_POST['asunto'] ?? '');
$mensaje  = trim($_POST['mensaje'] ?? '');

if ($opcion === "enviar") {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rlFile = sys_get_temp_dir() . '/ppi_contact_rl_' . md5($ip) . '.json';
    $ahora = time();
    $ventana = 600;
    $maxIntentos = 5;

    $intentos = [];
    if (file_exists($rlFile)) {
        $data = json_decode((string)@file_get_contents($rlFile), true);
        if (is_array($data)) {
            $intentos = array_values(array_filter($data, fn($ts) => is_int($ts) && ($ahora - $ts) < $ventana));
        }
    }

    if (count($intentos) >= $maxIntentos) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Has enviado demasiados mensajes en poco tiempo. Por favor espera unos minutos e intenta nuevamente."
        ]);
        exit;
    }

    if (empty($nombre) || empty($correo) || empty($telefono) || empty($asunto) || empty($mensaje)) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Por favor completa todos los campos obligatorios."
        ]);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "El formato del correo electrónico no es válido."
        ]);
        exit;
    }

    if (mb_strlen($asunto) > 150) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "El asunto no puede superar los 150 caracteres."
        ]);
        exit;
    }

    if (mb_strlen($mensaje) < 10) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Por favor escribe un mensaje más detallado (mínimo 10 caracteres)."
        ]);
        exit;
    }

    $intentos[] = $ahora;
    @file_put_contents($rlFile, json_encode($intentos), LOCK_EX);

    $id_usuario = !empty($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : null;

    $dao = new ContactoDAO();
    $resultado = $dao->registrarMensaje($nombre, $correo, $telefono, $asunto, $mensaje, $id_usuario);
    echo json_encode($resultado);
    exit;
}

echo json_encode([
    "estado" => false,
    "mensaje" => "Opción no válida o no especificada."
]);
exit;
