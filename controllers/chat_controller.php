<?php
    session_start();
    header("Content-Type: application/json; charset=UTF-8");

    require_once __DIR__ . '/../config/env.php';
    require_once __DIR__ . '/../config/conexion.php';
    require_once __DIR__ . '/../dao/ChatDAO.php';

    $id_usuario = (int)($_SESSION['id_usuario'] ?? 0);
    session_write_close();

    $opcion     = $_POST['opcion'] ?? '';
    $id_subasta = (int)($_POST['id_subasta'] ?? 0);
    $texto      = $_POST['mensaje'] ?? '';

    if ($id_usuario === 0) {
        echo json_encode(['estado' => false, 'mensaje' => 'Debes iniciar sesión']);
        exit;
    }

    if (!puedeChatear($conexion, $id_subasta, $id_usuario)) {
        echo json_encode(['estado' => false, 'mensaje' => 'No tienes acceso a este chat']);
        exit;
    }

    if ($opcion == "enviar") {
        $resultado = enviarMensaje($conexion, $id_subasta, $id_usuario, $texto);
        echo json_encode($resultado);
        exit;
    }

    if ($opcion == "mensajes") {
        $lista = [];
        foreach (obtenerMensajes($conexion, $id_subasta) as $m) {
            $lista[] = [
                'mensaje' => $m['mensaje'],
                'fecha'   => date('d/m H:i', strtotime($m['fecha'])),
                'es_mio'  => (int)$m['id_usuario'] === $id_usuario
            ];
        }
        echo json_encode(['estado' => true, 'mensajes' => $lista]);
        exit;
    }

    echo json_encode(['estado' => false, 'mensaje' => 'Opción no válida']);