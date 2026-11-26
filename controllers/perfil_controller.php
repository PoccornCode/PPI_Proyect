<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../helpers/sesion_inactividad.php';
require_once __DIR__ . '/../dao/PerfilDAO.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["estado" => false, "mensaje" => "Método no permitido."]);
    exit;
}

$opcion = $_POST['opcion'] ?? '';

//sesion vencio por inactividad responde con JSON (no redirige); el polling no renueva actividad
verificarInactividadSesion(true, $opcion !== 'subastasActivas');

if (empty($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode([
        "estado" => false,
        "auth_required" => true,
        "redirect" => BASE_URL . "views/auth/login.php",
        "mensaje" => "Debes iniciar sesión para continuar."
    ]);
    exit;
}

$id_usuario = (int)$_SESSION['id_usuario'];

if ($opcion === "subastasActivas") {
    $dao = new PerfilDAO();
    $subastas = $dao->obtenerSubastasActivas($id_usuario, 3);
    echo json_encode([
        "estado" => true,
        "subastas" => array_map(fn($s) => [
            "id_subasta" => (int)$s['id_subasta'],
            "precio_actual" => (float)$s['precio_actual'],
            "total_pujas" => (int)$s['total_pujas'],
            "segundos_restantes" => max(0, (int)$s['segundos_restantes'])
        ], $subastas)
    ]);
    exit;
}

if ($opcion === "cambiarContrasenia") {
    // Token de la página de perfil: evita que otro sitio envíe este formulario por el usuario
    $tokenSesion = $_SESSION['csrf_perfil'] ?? '';
    $tokenEnviado = $_POST['csrf'] ?? '';
    if ($tokenSesion === '' || !is_string($tokenEnviado) || !hash_equals($tokenSesion, $tokenEnviado)) {
        http_response_code(403);
        echo json_encode([
            "estado" => false,
            "mensaje" => "Tu sesión de formulario venció. Recarga la página e inténtalo de nuevo."
        ]);
        exit;
    }

    // 5 fallos = 5 minutos de espera
    $maxIntentos = 5;
    $esperaSegundos = 300;
    $bloqueadoHasta = (int)($_SESSION['clave_bloqueo_hasta'] ?? 0);
    if ($bloqueadoHasta > time()) {
        $minutos = (int)ceil(($bloqueadoHasta - time()) / 60);
        echo json_encode([
            "estado" => false,
            "mensaje" => "Demasiados intentos fallidos. Espera {$minutos} minuto(s) antes de intentar de nuevo."
        ]);
        exit;
    }

    $actual = $_POST['contrasenia_actual'] ?? '';
    $nueva = $_POST['nueva_contrasenia'] ?? '';
    $confirmar = $_POST['confirmar_contrasenia'] ?? '';

    if ($actual === '' || $nueva === '' || $confirmar === '') {
        echo json_encode(["estado" => false, "mensaje" => "Por favor completa los tres campos."]);
        exit;
    }

    // contra
    $regexContrasenia = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s]).{8,}$/';
    if (!preg_match($regexContrasenia, $nueva)) {
        echo json_encode([
            "estado" => false,
            "campo" => "nueva",
            "mensaje" => "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo."
        ]);
        exit;
    }

    if ($nueva !== $confirmar) {
        echo json_encode(["estado" => false, "campo" => "confirmar", "mensaje" => "Las contraseñas no coinciden."]);
        exit;
    }

    $dao = new PerfilDAO();
    $resultado = $dao->cambiarContrasenia($id_usuario, $actual, $nueva);

    if (!empty($resultado['estado'])) {
        unset($_SESSION['clave_intentos'], $_SESSION['clave_bloqueo_hasta']);
        // La contraseña cambió: se renueva el identificador de sesión
        session_regenerate_id(true);
        $_SESSION['last_activity'] = time();
    } elseif (($resultado['campo'] ?? '') === 'actual') {
        $_SESSION['clave_intentos'] = (int)($_SESSION['clave_intentos'] ?? 0) + 1;
        if ($_SESSION['clave_intentos'] >= $maxIntentos) {
            $_SESSION['clave_bloqueo_hasta'] = time() + $esperaSegundos;
            $_SESSION['clave_intentos'] = 0;
            $resultado['mensaje'] = "Demasiados intentos fallidos. Espera 5 minutos antes de intentar de nuevo.";
        }
    }

    echo json_encode($resultado);
    exit;
}

if ($opcion === "actualizarDatos") {
    // mismo token csrf del formulario de la contraseña
    $tokenSesion = $_SESSION['csrf_perfil'] ?? '';
    $tokenEnviado = $_POST['csrf'] ?? '';
    if ($tokenSesion === '' || !is_string($tokenEnviado) || !hash_equals($tokenSesion, $tokenEnviado)) {
        http_response_code(403);
        echo json_encode(["estado" => false, "mensaje" => "Tu sesión de formulario venció. Recarga la página e inténtalo de nuevo."]);
        exit;
    }
 
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
 
    if ($nombre === '' || $apellido === '' || $telefono === '' || $ubicacion === '') {
        echo json_encode(["estado" => false, "mensaje" => "Por favor completa todos los campos."]);
        exit;
    }
 
    if (mb_strlen($nombre) > 50) {
        echo json_encode(["estado" => false, "campo" => "nombre", "mensaje" => "El nombre puede tener máximo 50 caracteres."]);
        exit;
    }

    if (mb_strlen($apellido) > 50) {
        echo json_encode(["estado" => false, "campo" => "apellido", "mensaje" => "El apellido puede tener máximo 50 caracteres."]);
        exit;
    }

    if (!preg_match('/^\p{L}+(?:[\s\'-]\p{L}+)*$/u', $nombre)) {
        echo json_encode(["estado" => false, "campo" => "nombre", "mensaje" => "El nombre solo puede contener letras y espacios."]);
        exit;
    }

    if (!preg_match('/^\p{L}+(?:[\s\'-]\p{L}+)*$/u', $apellido)) {
        echo json_encode(["estado" => false, "campo" => "apellido", "mensaje" => "El apellido solo puede contener letras y espacios."]);
        exit;
    }
 
    if (mb_strlen($ubicacion) > 100) {
        echo json_encode(["estado" => false, "campo" => "ubicacion", "mensaje" => "La ubicación puede tener máximo 100 caracteres."]);
        exit;
    }
 
    // mismo formato del registro: 7000-0000
    if (!preg_match('/^\d{4}-\d{4}$/', $telefono)) {
        echo json_encode(["estado" => false, "campo" => "telefono", "mensaje" => "El teléfono debe tener el formato 7000-0000."]);
        exit;
    }
 
    $dao = new PerfilDAO();
    $resultado = $dao->actualizarDatos($id_usuario, $nombre, $apellido, $ubicacion, $telefono);
 
    if (!empty($resultado['estado'])) {
        // para que el nombre del menu se actualice
        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellido'] = $apellido;
    }
 
    echo json_encode($resultado);
    exit;
}

echo json_encode(["estado" => false, "mensaje" => "Opción no válida o no especificada."]);
exit;
