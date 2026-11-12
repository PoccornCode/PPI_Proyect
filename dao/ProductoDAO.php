<?php

function insertarProducto($conexion, $nombre, $descripcion, $condicion, $id_categoria, $id_usuario, $rutas) {
    
    try {
        $conexion->begin_transaction();

        $sql= "INSERT INTO sb_producto (nombre, descripcion, 
        condicion, id_categoria, id_usuario)
        VALUES (?, ?, ?, ?, ?);";

        $stmt=$conexion->prepare($sql);
        $stmt->bind_param("sssii", $nombre, 
        $descripcion, $condicion, $id_categoria, $id_usuario);

        if (!$stmt->execute()) {
            throw new RuntimeException('Error al guardar el producto: ' . $stmt->error);
        }

        $id_producto= $conexion->insert_id;
        $stmt->close();

        insertarImagenes($conexion, $id_producto, $rutas);
        $conexion->commit();
        return ['estado' => true, 'mensaje' => 'Producto guardado exitosamente'];
    } catch (Throwable $e) {
        $conexion->rollback();

        // Segun, esto borra los archivos en la carpeta, pues si hay rollback ya nadie va a ocupar esas img haciendo bulto
        foreach ($rutas as $ruta) {
            @unlink(__DIR__ . '/../uploads/productos/' . $ruta);
        }

        error_log($e->getMessage());  
        return ['estado' => false, 'mensaje' => 'No se pudo guardar el producto'];
    } 
}

function insertarImagenes($conexion, $id_producto, $rutas){
    $sql= "INSERT INTO sb_imagen_producto (id_producto, principal, ruta) 
    VALUES (?, ?, ?)";
    $stmtImg=$conexion->prepare($sql);
        
    $ruta="";
    $principal=0;
    $stmtImg->bind_param("iis", $id_producto, $principal, $ruta);

    foreach ($rutas as $index => $r) {
        $ruta= $r;

        if($index===0){
            $principal=1;
        }else{
            $principal=0;
        }

        if (!$stmtImg->execute()) {
            $stmtImg->close();
            throw new RuntimeException('Error al guardar una imágen: ' . $stmtImg->error);
        }
    }
    $stmtImg->close();
}
