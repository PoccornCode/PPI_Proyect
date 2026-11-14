<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../dao/LoginDAO.php';

$opcion = $_POST['opcion'] ?? '';
$username = trim($_POST['username'] ?? '');
$contrasenia = $_POST['contrasenia'] ?? '';

$dao = new LoginDAO();

if ($opcion === "login") {
    if (empty($username) || empty($contrasenia)) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Por favor ingresa tu usuario y contraseña."
        ]);
        exit;
    }

    $resultado = $dao->autenticar($username, $contrasenia);

    if ($resultado['estado']) {
        session_regenerate_id(true);

        $u = $resultado['usuario'];
        $_SESSION['id_usuario'] = $u['id_usuario'];
        $_SESSION['nombre'] = $u['nombre'];
        $_SESSION['apellido'] = $u['apellido'];
        $_SESSION['username'] = $u['username'];
        $_SESSION['id_rol'] = $u['id_rol'];

        // Redirige a la raíz del proyecto (auto-detectada)
        $redirect = BASE_URL . "index.php";

        echo json_encode([
            "estado" => true,
            "mensaje" => $resultado['mensaje'],
            "redirect" => $redirect
        ]);
        exit;
    }

    echo json_encode($resultado);
    exit;
}

if ($opcion === "logout") {
    $_SESSION = [];
    session_destroy();
    echo json_encode([
        "estado" => true,
        "mensaje" => "Sesión cerrada correctamente."
    ]);
    exit;
}

echo json_encode([
    "estado" => false,
    "mensaje" => "Opción no válida o no especificada."
]);
exit;