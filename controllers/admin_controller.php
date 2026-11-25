<?php
require_once __DIR__ . '/../dao/AdminDAO.php';
require_once __DIR__ . '/../dao/ModeradorDAO.php';
require_once __DIR__ . '/../helpers/sesion_inactividad.php';
require_once __DIR__ . '/../helpers/kpi_helper.php';

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

function requerirAdministradorAutenticado(bool $esApiJson = false, bool $renovarActividad = true): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!defined('BASE_URL')) {
        require_once __DIR__ . '/../config/env.php';
    }

    verificarInactividadSesion($esApiJson, $renovarActividad);

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

function obtenerKpisAdmin(string $rango = '30d'): array
{
    $rangoValido = in_array($rango, ['7d', '30d', '90d'], true) ? $rango : '30d';

    $modDao = new ModeradorDAO();
    $modDao->inactivarSubastasVencidas();

    $dao = new AdminDAO();
    $resumen = $dao->obtenerResumenKpis($rangoValido);

    return [
        'rango'                => $rangoValido,
        'usuarios'             => $resumen['usuarios'],
        'pujas'                => $resumen['pujas'],
        'activas'              => $resumen['activas'],
        'pendientes'           => $resumen['pendientes'],
        'ultimo_usuario'       => formatearFechaKpiEs($resumen['ultimo_usuario']),
        'ultima_puja'          => formatearFechaKpiEs($resumen['ultima_puja']),
        'ultima_activa'        => formatearFechaKpiEs($resumen['ultima_activa']),
        'ultima_pendiente'     => formatearFechaKpiEs($resumen['ultima_pendiente']),
        'tendencia_usuarios'   => calcularTendenciaKpi($resumen['usuarios_actual'], $resumen['usuarios_anterior']),
        'tendencia_pujas'      => calcularTendenciaKpi($resumen['pujas_actual'], $resumen['pujas_anterior']),
        'tendencia_activas'    => calcularTendenciaKpi($resumen['activas_actual'], $resumen['activas_anterior']),
        'tendencia_pendientes' => calcularTendenciaKpi($resumen['pendientes_actual'], $resumen['pendientes_anterior']),
        'barras_usuarios'      => construirBarrasSparkline($resumen['serie_usuarios']),
        'barras_pujas'         => construirBarrasSparkline($resumen['serie_pujas']),
        'barras_activas'       => construirBarrasSparkline($resumen['serie_activas']),
        'barras_pendientes'    => construirBarrasSparkline($resumen['serie_pendientes']),
    ];
}

function obtenerGraficosInicioAdmin(string $rangoActividad = '7d'): array
{
    $modDao = new ModeradorDAO();
    $modDao->inactivarSubastasVencidas();

    $dao = new AdminDAO();
    return [
        'actividad'    => $dao->obtenerActividadPlataforma($rangoActividad),
        'distribucion' => $dao->obtenerDistribucionSubastas(),
    ];
}

function obtenerResumenInferiorInicioAdmin(string $baseUrl = '', int $limiteUsuarios = 3, int $limiteSubastas = 3): array
{
    $modDao = new ModeradorDAO();
    $modDao->inactivarSubastasVencidas();

    $dao = new AdminDAO();
    return [
        'usuarios_recientes'  => $dao->obtenerUsuariosRecientes($limiteUsuarios),
        'subastas_destacadas' => $dao->obtenerSubastasDestacadas($limiteSubastas, $baseUrl),
    ];
}

function obtenerRolesAdmin(): array
{
    $dao = new AdminDAO();
    return $dao->obtenerRolesFiltro();
}

function obtenerUsuariosAdmin(string $busqueda = '', int $idRol = 0, int $pagina = 1, int $porPagina = 10, string $estadoFiltro = 'todos'): array
{
    $dao = new AdminDAO();
    return $dao->listarUsuariosAdmin($busqueda, $idRol, $pagina, $porPagina, $estadoFiltro);
}

function obtenerDetalleUsuarioAdmin(int $idUsuario, int $idAdminActual = 0): ?array
{
    $dao = new AdminDAO();
    return $dao->obtenerDetalleUsuarioAdmin($idUsuario, $idAdminActual);
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
    $adminActual = requerirAdministradorAutenticado(true, !$esPollFondo);
    $idAdminActual = (int)($adminActual['id_usuario'] ?? 0);

    $opcion = $_POST['opcion'] ?? $_GET['opcion'] ?? 'alertas';

    if ($opcion === 'alertas') {
        echo json_encode([
            'estado'  => true,
            'alertas' => obtenerAlertasTopbarAdmin(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($opcion === 'kpis') {
        $rango = (string)($_GET['rango'] ?? $_POST['rango'] ?? '30d');
        echo json_encode([
            'estado' => true,
            'kpis'   => obtenerKpisAdmin($rango),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($opcion === 'graficos') {
        $rango = (string)($_GET['rango'] ?? $_POST['rango'] ?? '7d');
        echo json_encode([
            'estado'   => true,
            'graficos' => obtenerGraficosInicioAdmin($rango),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($opcion === 'resumen_inicio') {
        echo json_encode([
            'estado'  => true,
            'resumen' => obtenerResumenInferiorInicioAdmin(BASE_URL),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($opcion === 'usuarios') {
        $busqueda = trim((string)($_GET['q'] ?? $_POST['q'] ?? ''));
        $idRol = (int)($_GET['id_rol'] ?? $_POST['id_rol'] ?? 0);
        $estadoFiltro = trim((string)($_GET['estado'] ?? $_POST['estado'] ?? 'todos'));
        $pagina = (int)($_GET['pagina'] ?? $_POST['pagina'] ?? 1);
        $porPagina = (int)($_GET['por_pagina'] ?? $_POST['por_pagina'] ?? 10);
        echo json_encode([
            'estado' => true,
            'data'   => obtenerUsuariosAdmin($busqueda, $idRol, $pagina, $porPagina, $estadoFiltro),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($opcion === 'usuario_detalle') {
        $idUsuario = (int)($_GET['id_usuario'] ?? $_POST['id_usuario'] ?? 0);
        if ($idUsuario <= 0) {
            http_response_code(400);
            echo json_encode([
                'estado'  => false,
                'mensaje' => 'ID de usuario no válido.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $detalle = obtenerDetalleUsuarioAdmin($idUsuario, $idAdminActual);
        if (!$detalle) {
            http_response_code(404);
            echo json_encode([
                'estado'  => false,
                'mensaje' => 'No se encontró el usuario solicitado.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'estado'  => true,
            'usuario' => $detalle,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($opcion === 'usuario_validar_edicion') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'estado'  => false,
                'mensaje' => 'Método no permitido.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $idUsuario = (int)($_POST['id_usuario'] ?? 0);
        $dao = new AdminDAO();
        $res = $dao->validarActualizacionUsuarioAdmin($idUsuario, [
            'nombre'    => $_POST['nombre'] ?? '',
            'apellido'  => $_POST['apellido'] ?? '',
            'username'  => $_POST['username'] ?? '',
            'correo'    => $_POST['correo'] ?? '',
            'telefono'  => $_POST['telefono'] ?? '',
            'ubicacion' => $_POST['ubicacion'] ?? '',
            'id_rol'    => $_POST['id_rol'] ?? 2,
            'estado'    => $_POST['estado'] ?? 1,
        ], $idAdminActual);

        if (empty($res['estado'])) {
            http_response_code(422);
            echo json_encode([
                'estado'  => false,
                'campo'   => $res['campo'] ?? '',
                'mensaje' => $res['mensaje'] ?? 'Los datos ingresados no son válidos.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode(['estado' => true], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($opcion === 'usuario_actualizar') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'estado'  => false,
                'mensaje' => 'Método no permitido.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $idUsuario = (int)($_POST['id_usuario'] ?? 0);
        $dao = new AdminDAO();
        $res = $dao->actualizarUsuarioAdmin($idUsuario, [
            'nombre'    => $_POST['nombre'] ?? '',
            'apellido'  => $_POST['apellido'] ?? '',
            'username'  => $_POST['username'] ?? '',
            'correo'    => $_POST['correo'] ?? '',
            'telefono'  => $_POST['telefono'] ?? '',
            'ubicacion' => $_POST['ubicacion'] ?? '',
            'id_rol'    => $_POST['id_rol'] ?? 2,
            'estado'    => $_POST['estado'] ?? 1,
        ], $idAdminActual);

        if (!empty($res['estado']) && $idUsuario === $idAdminActual && !empty($res['usuario'])) {
            $_SESSION['nombre'] = $res['usuario']['nombre'];
            $_SESSION['apellido'] = $res['usuario']['apellido'];
            $_SESSION['username'] = $res['usuario']['username'];
        }

        if (empty($res['estado'])) {
            http_response_code(422);
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($opcion === 'usuario_validar_creacion') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'estado'  => false,
                'mensaje' => 'Método no permitido.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $dao = new AdminDAO();
        $res = $dao->validarCreacionUsuarioAdmin([
            'nombre'                => $_POST['nombre'] ?? '',
            'apellido'              => $_POST['apellido'] ?? '',
            'username'              => $_POST['username'] ?? '',
            'correo'                => $_POST['correo'] ?? '',
            'telefono'              => $_POST['telefono'] ?? '',
            'ubicacion'             => $_POST['ubicacion'] ?? '',
            'id_rol'                => $_POST['id_rol'] ?? 2,
            'estado'                => $_POST['estado'] ?? 1,
            'contrasenia'           => $_POST['contrasenia'] ?? '',
            'confirmar_contrasenia' => $_POST['confirmar_contrasenia'] ?? '',
        ]);

        if (empty($res['estado'])) {
            http_response_code(422);
            echo json_encode([
                'estado'  => false,
                'campo'   => $res['campo'] ?? '',
                'mensaje' => $res['mensaje'] ?? 'Los datos ingresados no son válidos.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode(['estado' => true], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($opcion === 'usuario_crear') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'estado'  => false,
                'mensaje' => 'Método no permitido.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $dao = new AdminDAO();
        $res = $dao->crearUsuarioAdmin([
            'nombre'                => $_POST['nombre'] ?? '',
            'apellido'              => $_POST['apellido'] ?? '',
            'username'              => $_POST['username'] ?? '',
            'correo'                => $_POST['correo'] ?? '',
            'telefono'              => $_POST['telefono'] ?? '',
            'ubicacion'             => $_POST['ubicacion'] ?? '',
            'id_rol'                => $_POST['id_rol'] ?? 2,
            'estado'                => $_POST['estado'] ?? 1,
            'contrasenia'           => $_POST['contrasenia'] ?? '',
            'confirmar_contrasenia' => $_POST['confirmar_contrasenia'] ?? '',
        ], $idAdminActual);

        if (empty($res['estado'])) {
            http_response_code(422);
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    echo json_encode(['estado' => false, 'mensaje' => 'Opción no válida'], JSON_UNESCAPED_UNICODE);
    exit;
}
