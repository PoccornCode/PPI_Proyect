<?php
require_once __DIR__ . '/../dao/ModeradorDAO.php';
require_once __DIR__ . '/../dao/CategoriaDAO.php';
require_once __DIR__ . '/../helpers/sesion_inactividad.php';
require_once __DIR__ . '/../helpers/kpi_helper.php';

function obtenerKpisModerador(): array
{
    $dao = new ModeradorDAO();
    $inactivadas = $dao->inactivarSubastasVencidas();
    $resumen     = $dao->obtenerResumenKpis();

    return [
        'inactivadas'          => $inactivadas,
        'activas'              => $resumen['activas'],
        'pendientes'           => $resumen['pendientes'],
        'ultima_activa'        => formatearFechaKpiEs($resumen['ultima_activa']),
        'ultima_pendiente'     => formatearFechaKpiEs($resumen['ultima_pendiente']),
        'tendencia_activas'    => calcularTendenciaKpi($resumen['activas_ultimos_7d'], $resumen['activas_7d_anteriores']),
        'tendencia_pendientes' => calcularTendenciaKpi($resumen['pendientes_ultimos_7d'], $resumen['pendientes_7d_anteriores']),
        'barras_activas'       => construirBarrasSparkline($resumen['serie_activas']),
        'barras_pendientes'    => construirBarrasSparkline($resumen['serie_pendientes']),
    ];
}

function obtenerCategoriasModerador(): array
{
    $dao = new CategoriaDAO();
    return $dao->listarCategorias(false);
}

function obtenerPerfilModeradorActual(): ?array
{
    $idSesion = (int)($_SESSION['id_usuario'] ?? 0);
    if ($idSesion <= 0) {
        return null;
    }

    $dao = new ModeradorDAO();
    $perfil = $dao->obtenerPerfilModerador($idSesion);
    if (!$perfil) {
        return null;
    }

    $nombreCompleto = trim(($perfil['nombre'] ?? '') . ' ' . ($perfil['apellido'] ?? ''));
    $primerNombre = explode(' ', $nombreCompleto)[0] ?: ($perfil['username'] ?? '');
    $inicial = $nombreCompleto !== '' ? strtoupper(mb_substr($nombreCompleto, 0, 1, 'UTF-8')) : '';

    return [
        'id_usuario'      => (int)$perfil['id_usuario'],
        'nombre_completo' => $nombreCompleto,
        'primer_nombre'   => $primerNombre,
        'username'        => (string)($perfil['username'] ?? ''),
        'rol_nombre'      => (string)($perfil['rol_nombre'] ?? ''),
        'id_rol'          => (int)$perfil['id_rol'],
        'inicial'         => $inicial,
    ];
}

function requerirModeradorAutenticado(bool $esApiJson = false, bool $renovarActividad = true): array
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
                'mensaje'       => 'Debes iniciar sesión para acceder al panel de moderación.',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        $_SESSION['redirect_after_login'] = BASE_URL . 'views/moderador/dashboard.php';
        $_SESSION['flash_toast'] = [
            'tipo'    => 'warning',
            'titulo'  => 'Acceso restringido',
            'mensaje' => 'Inicia sesión con una cuenta de moderador para acceder al panel.',
        ];
        header('Location: ' . BASE_URL . 'views/auth/login.php');
        exit;
    }

    $perfil = obtenerPerfilModeradorActual();
    if (!$perfil) {
        if ($esApiJson) {
            http_response_code(403);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'estado'  => false,
                'mensaje' => 'No tienes permisos para acceder al panel de moderación.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }

    $_SESSION['id_rol'] = $perfil['id_rol'];
    return $perfil;
}

function normalizarRutaImagenModerador(string $ruta, string $baseUrl = ''): string
{
    $ruta = trim($ruta);
    if ($ruta === '') {
        return '';
    }
    if (strpos($ruta, 'http://') === 0 || strpos($ruta, 'https://') === 0) {
        return $ruta;
    }
    if (strpos($ruta, 'uploads/') === 0 || strpos($ruta, 'assets/') === 0) {
        return $baseUrl . $ruta;
    }
    return $baseUrl . 'uploads/productos/' . ltrim($ruta, '/');
}

function formatearSubastaPendiente(array $row, string $baseUrl = ''): array
{
    $idSubasta  = (int)($row['id_subasta'] ?? 0);
    $codigo     = 'SUB-' . str_pad((string)$idSubasta, 3, '0', STR_PAD_LEFT);
    $titulo     = (string)($row['titulo'] ?? '');
    $condicion  = (string)($row['condicion'] ?? '');
    $categoria  = (string)($row['categoria'] ?? '');
    $username   = (string)($row['username'] ?? '');
    $vendedor   = $username !== '' ? '@' . ltrim($username, '@') : '';
    $precio     = '$' . number_format((float)($row['precio_inicial'] ?? 0), 2, '.', ',');
    $incremento = '$' . number_format((float)($row['incremento_minimo'] ?? 0), 2, '.', ',');

    $rutasOriginales = is_array($row['imagenes'] ?? null) ? $row['imagenes'] : [];
    $imagenesUrls = [];
    foreach ($rutasOriginales as $ruta) {
        $url = normalizarRutaImagenModerador((string)$ruta, $baseUrl);
        if ($url !== '') {
            $imagenesUrls[] = $url;
        }
    }

    $totalFotos = count($imagenesUrls);
    $imagenMiniatura = $imagenesUrls[0] ?? normalizarRutaImagenModerador((string)($row['categoria_imagen'] ?? ''), $baseUrl);
    $etiquetaFotos = $totalFotos === 1 ? '1 foto' : $totalFotos . ' fotos';

    $tipoSolicitud = strtolower(trim((string)($row['tipo_solicitud'] ?? 'nueva')));
    $notaVendedor = trim((string)($row['nota_vendedor'] ?? ''));
    $motivoRechazo = trim((string)($row['motivo_rechazo'] ?? ''));

    $specs = [
        ['label' => 'Categoría', 'value' => $categoria],
        ['label' => 'Vendedor', 'value' => $vendedor],
        ['label' => 'Precio base', 'value' => $precio . ' (inc. ' . $incremento . ')'],
        ['label' => 'Condición', 'value' => $condicion . ' (' . $etiquetaFotos . ')'],
    ];
    if ($tipoSolicitud === 'reenvio') {
        $specs[] = ['label' => 'Tipo de solicitud', 'value' => 'Reenvío corregido'];
    } elseif ($tipoSolicitud === 'apelacion') {
        $specs[] = ['label' => 'Tipo de solicitud', 'value' => 'Apelación'];
    }
    if ($notaVendedor !== '') {
        $specs[] = ['label' => 'Nota del vendedor', 'value' => $notaVendedor];
    }
    if ($motivoRechazo !== '') {
        $specs[] = ['label' => 'Motivo de rechazo', 'value' => $motivoRechazo];
    }

    return [
        'id_subasta'     => $idSubasta,
        'id'             => $codigo,
        'titulo'         => $titulo,
        'condicion'      => $condicion,
        'fotos'          => $totalFotos,
        'categoria'      => $categoria,
        'vendedor'       => $vendedor,
        'precio'         => $precio,
        'incremento'     => $incremento,
        'estado'         => ucfirst(strtolower((string)($row['estado'] ?? 'pendiente'))),
        'tipo_solicitud' => $tipoSolicitud,
        'nota_vendedor'  => $notaVendedor,
        'motivo_rechazo' => $motivoRechazo,
        'img'            => $imagenMiniatura,
        'imagenes'       => $imagenesUrls,
        'detalle'        => (string)($row['descripcion'] ?? ''),
        'specs'          => $specs,
    ];
}

function obtenerColaSubastasPendientes(string $baseUrl = ''): array
{
    $dao = new ModeradorDAO();
    $filas = $dao->listarSubastasPendientes();
    $lista = [];

    foreach ($filas as $fila) {
        $lista[] = formatearSubastaPendiente($fila, $baseUrl);
    }

    return $lista;
}

function moderarSubastaPendiente(int $idSubasta, string $decision, ?string $motivo = null): array
{
    $dao = new ModeradorDAO();
    return $dao->moderarSubasta($idSubasta, $decision, $motivo);
}

function obtenerTodasSubastasModerador(string $baseUrl = ''): array
{
    $dao = new ModeradorDAO();
    $dao->inactivarSubastasVencidas();
    $filas = $dao->listarTodasSubastas();
    $lista = [];

    foreach ($filas as $fila) {
        $item = formatearSubastaPendiente($fila, $baseUrl);
        $item['estado_raw'] = strtolower((string)($fila['estado'] ?? 'pendiente'));
        $lista[] = $item;
    }

    return $lista;
}

function cambiarEstadoSubastaModerador(int $idSubasta, string $nuevoEstado, ?string $estadoEsperado = null, ?string $motivo = null): array
{
    $dao = new ModeradorDAO();
    return $dao->cambiarEstadoSubasta($idSubasta, $nuevoEstado, $estadoEsperado, $motivo);
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
    requerirModeradorAutenticado(true, !$esPollFondo);

    $opcion = $_POST['opcion'] ?? $_GET['opcion'] ?? 'kpis';

    if ($opcion === 'kpis') {
        echo json_encode([
            'estado' => true,
            'kpis'   => obtenerKpisModerador(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($opcion === 'categorias') {
        echo json_encode([
            'estado'     => true,
            'categorias' => obtenerCategoriasModerador(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($opcion === 'pendientes') {
        echo json_encode([
            'estado'     => true,
            'pendientes' => obtenerColaSubastasPendientes(BASE_URL),
            'kpis'       => obtenerKpisModerador(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($opcion === 'moderar') {
        $idSubasta = (int)($_POST['id_subasta'] ?? 0);
        $decision  = (string)($_POST['decision'] ?? '');
        $motivo    = isset($_POST['motivo']) ? (string)$_POST['motivo'] : null;
        $resultado = moderarSubastaPendiente($idSubasta, $decision, $motivo);

        if ($resultado['estado']) {
            $resultado['kpis'] = obtenerKpisModerador();
        }

        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($opcion === 'todas') {
        echo json_encode([
            'estado'   => true,
            'subastas' => obtenerTodasSubastasModerador(BASE_URL),
            'kpis'     => obtenerKpisModerador(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($opcion === 'cambiar_estado') {
        $idSubasta      = (int)($_POST['id_subasta'] ?? 0);
        $nuevoEstado    = (string)($_POST['nuevo_estado'] ?? '');
        $estadoEsperado = isset($_POST['estado_esperado']) ? (string)$_POST['estado_esperado'] : null;
        $motivo         = isset($_POST['motivo']) ? (string)$_POST['motivo'] : null;
        $resultado      = cambiarEstadoSubastaModerador($idSubasta, $nuevoEstado, $estadoEsperado, $motivo);

        if ($resultado['estado'] || !empty($resultado['conflicto'])) {
            $resultado['kpis'] = obtenerKpisModerador();
        }

        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['estado' => false, 'mensaje' => 'Opción no válida'], JSON_UNESCAPED_UNICODE);
    exit;
}
