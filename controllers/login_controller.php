<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../dao/LoginDAO.php';

$opcion = $_POST['opcion'] ?? '';
$username = trim($_POST['username'] ?? '');
$contrasenia = $_POST['contrasenia'] ?? '';
$redirect_param = trim($_POST['redirect'] ?? '');

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
        $_SESSION['id_usuario'] = (int)$u['id_usuario'];
        $_SESSION['nombre'] = $u['nombre'];
        $_SESSION['apellido'] = $u['apellido'];
        $_SESSION['username'] = $u['username'];
        $_SESSION['id_rol'] = (int)$u['id_rol'];
        $_SESSION['last_activity'] = time();

        if (!empty($_SESSION['redirect_after_login'])) {
            $redirect = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
        } elseif ((int)$u['id_rol'] === 1) {
            $redirect = BASE_URL . "views/admin/dashboard.php";
        } elseif ((int)$u['id_rol'] === 3) {
            $redirect = BASE_URL . "views/moderador/dashboard.php";
        } else {
            $redirect = BASE_URL . "index.php";
        }

        // Redirige a la raíz del proyecto;
        if (!empty($redirect_param)) {
            if (strpos($redirect_param, 'http://') === false && strpos($redirect_param, 'https://') === false) {
                $redirect = (strpos($redirect_param, BASE_URL) === 0) ? $redirect_param : BASE_URL . ltrim($redirect_param, '/');
            } elseif (strpos($redirect_param, BASE_URL) === 0) {
                $redirect = $redirect_param;
            }
        }

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
