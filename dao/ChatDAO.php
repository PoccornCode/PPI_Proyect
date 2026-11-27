<?php

    function puedeChatear($conexion, $id_subasta, $id_usuario) {
        $sql = "SELECT s.estado, p.id_usuario AS id_vendedor, p.nombre AS producto, img.ruta AS imagen
        FROM sb_subasta s
        INNER JOIN sb_producto p ON p.id_producto = s.id_producto
        LEFT JOIN sb_imagen_producto img ON img.id_producto = p.id_producto AND img.principal = 1
        WHERE s.id_subasta = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_subasta);
        $stmt->execute();
        $subasta = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$subasta || strtolower(trim($subasta['estado'])) !== 'finalizada') {
            return null;
        }

        $id_vendedor = (int)$subasta['id_vendedor'];

        $sql = "SELECT id_usuario FROM sb_puja
        WHERE id_subasta = ?
        ORDER BY monto DESC, fecha ASC LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_subasta);
        $stmt->execute();
        $puja = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$puja) {
            return null;
        }

        $id_ganador = (int)$puja['id_usuario'];

        if ($id_usuario !== $id_vendedor && $id_usuario !== $id_ganador) {
            return null;
        }

        $id_otro = 0;

        if ($id_usuario === $id_vendedor) {
            $id_otro = $id_ganador;
        } else {
            $id_otro = $id_vendedor;
        }

        $sql = "SELECT nombre, apellido FROM sb_usuario WHERE id_usuario = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_otro);
        $stmt->execute();
        $otro = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return [
            'rol'=> $id_usuario === $id_vendedor ? 'vendedor' : 'ganador',
            'producto'=> $subasta['producto'],
            'imagen'=> $subasta['imagen'],
            'otro_nombre'=> $otro ? trim($otro['nombre'] . ' ' . $otro['apellido']) : 'Usuario'
        ];
    }

    function obtenerMensajes($conexion, $id_subasta) {
        $sql = "SELECT id_usuario, mensaje, fecha
        FROM sb_mensaje
        WHERE id_subasta = ?
        ORDER BY id_mensaje ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_subasta);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $mensajes = [];
        while ($row = $resultado->fetch_assoc()) {
            $mensajes[] = $row;
        }
        $stmt->close();
        return $mensajes;
    }

    function enviarMensaje($conexion, $id_subasta, $id_usuario, $texto) {
        $texto = trim($texto);
        if ($texto === '') {
            return ['estado' => false, 'mensaje' => 'El mensaje está vacío.'];
        }
        if (mb_strlen($texto, 'UTF-8') > 1000) {
            return ['estado' => false, 'mensaje' => 'El mensaje puede tener máximo 1000 caracteres.'];
        }
        try {
            $sql = "INSERT INTO sb_mensaje (id_subasta, id_usuario, mensaje) VALUES (?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("iis", $id_subasta, $id_usuario, $texto);
            $stmt->execute();
            $stmt->close();
            return ['estado' => true];
        } catch (Throwable $e) {
            error_log($e->getMessage());
            return ['estado' => false, 'mensaje' => 'Hubo un error, no se pudo enviar el mensaje'];
        }
    }
