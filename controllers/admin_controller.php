<?php
require_once __DIR__ . '/../dao/AdminDAO.php';
require_once __DIR__ . '/../helpers/sesion_inactividad.php';

function obtenerPerfilAdministradorActual(): ?array
{
    $idSesion = (int)($_SESSION['id_usuario'] ?? 0);
    if ($idSesion <= 0) {
        return null;
    }

    $dao = new AdminDAO();
    $perfil = $dao->obtenerPerfilAdministrador($idSesion);
    if (!$perfil) {
        return null;
    }

    $nombreCompleto = trim(($perfil['nombre'] ?? '') . ' ' . ($perfil['apellido'] ?? ''));
    $primerNombre = explode(' ', $nombreCompleto)[0] ?: ($perfil['username'] ?? '');
    $inicial = $nombreCompleto !== '' ? strtoupper(mb_substr($nombreCompleto, 0, 1, 'UTF-8')) : 'A';

    return [
        'id_usuario'      => (int)$perfil['id_usuario'],
        'nombre'          => (string)($perfil['nombre'] ?? ''),
        'apellido'        => (string)($perfil['apellido'] ?? ''),
        'nombre_completo' => $nombreCompleto !== '' ? $nombreCompleto : 'Administrador',
        'primer_nombre'   => $primerNombre,
        'username'        => (string)($perfil['username'] ?? ''),
        'rol_nombre'      => (string)($perfil['rol_nombre'] ?? 'Administrador'),
        'id_rol'          => (int)$perfil['id_rol'],
        'inicial'         => $inicial,
    ];
}

function requerirAdministradorAutenticado(bool $esApiJson = false): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!defined('BASE_URL')) {
        require_once __DIR__ . '/../config/env.php';
    }

    verificarInactividadSesion($esApiJson);

    $idSesion = (int)($_SESSION['id_usuario'] ?? 0);
    if ($idSesion <= 0) {
        if ($esApiJson) {
            http_response_code(401);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'estado'        => false,
                'auth_required' => true,
                'redirect'      => BASE_URL . 'views/auth/login.php',
                'mensaje'       => 'Debes iniciar sesión para acceder al panel de administración.',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        $_SESSION['redirect_after_login'] = BASE_URL . 'views/admin/dashboard.php';
        $_SESSION['flash_toast'] = [
            'tipo'    => 'warning',
            'titulo'  => 'Acceso restringido',
            'mensaje' => 'Inicia sesión con una cuenta de administrador para acceder al panel.',
        ];
        header('Location: ' . BASE_URL . 'views/auth/login.php');
        exit;
    }

    $perfil = obtenerPerfilAdministradorActual();
    if (!$perfil) {
        if ($esApiJson) {
            http_response_code(403);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'estado'  => false,
                'mensaje' => 'No tienes permisos para acceder al panel de administración.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $destino = ((int)($_SESSION['id_rol'] ?? 0) === 3)
            ? (BASE_URL . 'views/moderador/dashboard.php')
            : (BASE_URL . 'index.php');
        header('Location: ' . $destino);
        exit;
    }

    $_SESSION['id_rol'] = $perfil['id_rol'];
    $_SESSION['nombre'] = $perfil['nombre'];
    $_SESSION['apellido'] = $perfil['apellido'];
    $_SESSION['username'] = $perfil['username'];

    return $perfil;
}

function obtenerAlertasTopbarAdmin(): array
{
    $dao = new AdminDAO();
    return $dao->obtenerResumenAlertasTopbar();
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

    requerirAdministradorAutenticado(true);

    $opcion = $_POST['opcion'] ?? $_GET['opcion'] ?? 'alertas';

    if ($opcion === 'alertas') {
        echo json_encode([
            'estado'  => true,
            'alertas' => obtenerAlertasTopbarAdmin(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['estado' => false, 'mensaje' => 'Opción no válida'], JSON_UNESCAPED_UNICODE);
    exit;
}
