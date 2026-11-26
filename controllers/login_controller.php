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
    // 5 intentos fallidos en 10 minutos = bloqueo de 5 minutos por IP
    $maxIntentos = 5;
    $ventanaSegundos = 600;
    $esperaSegundos = 300;
    $ahora = time();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rlFile = sys_get_temp_dir() . '/ppi_login_rl_' . md5($ip) . '.json';

    $rlData = ['intentos' => [], 'bloqueado_hasta' => 0];
    if (file_exists($rlFile)) {
        $leido = json_decode((string)@file_get_contents($rlFile), true);
        if (is_array($leido)) {
            $rlData['intentos'] = array_values(array_filter(
                $leido['intentos'] ?? [],
                fn($ts) => is_int($ts) && ($ahora - $ts) < $ventanaSegundos
            ));
            $rlData['bloqueado_hasta'] = (int)($leido['bloqueado_hasta'] ?? 0);
        }
    }

    if ($rlData['bloqueado_hasta'] > $ahora) {
        $minutos = (int)ceil(($rlData['bloqueado_hasta'] - $ahora) / 60);
        echo json_encode([
            "estado" => false,
            "mensaje" => "Demasiados intentos fallidos. Espera {$minutos} minuto(s) antes de intentar de nuevo."
        ]);
        exit;
    }

    if (empty($username) || empty($contrasenia)) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Por favor ingresa tu usuario y contraseña."
        ]);
        exit;
    }

    $resultado = $dao->autenticar($username, $contrasenia);

    if ($resultado['estado']) {
        @unlink($rlFile);
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

    if (!empty($resultado['requiere_verificacion'])) {
        echo json_encode($resultado);
        exit;
    }

    $rlData['intentos'][] = $ahora;
    if (count($rlData['intentos']) >= $maxIntentos) {
        $rlData['bloqueado_hasta'] = $ahora + $esperaSegundos;
        $rlData['intentos'] = [];
        $resultado['mensaje'] = "Demasiados intentos fallidos. Espera 5 minutos antes de intentar de nuevo.";
    } else {
        $restantes = $maxIntentos - count($rlData['intentos']);
        $resultado['mensaje'] = ($resultado['mensaje'] ?? 'Usuario o contraseña incorrectos.') . " Te quedan {$restantes} intento(s).";
    }
    @file_put_contents($rlFile, json_encode($rlData), LOCK_EX);

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
