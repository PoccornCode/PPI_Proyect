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
        return ['estado' => true, 'mensaje' => 'Producto guardado exitosamente', 'id_producto' => $id_producto];
    } catch (Throwable $e) {
        $conexion->rollback();
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

function obtenerProductosPorUsuario($conexion, $id_usuario) {
    $sql = "SELECT p.id_producto, p.nombre, p.descripcion, p.condicion, p.estado,
                   c.nombre AS categoria,
                   img.ruta AS imagen_principal,
                   s.id_subasta, s.estado AS estado_subasta, s.precio_inicial, s.fecha_inicial, s.fecha_final
            FROM sb_producto p
            LEFT JOIN sb_categoria c ON p.id_categoria = c.id_categoria
            LEFT JOIN sb_imagen_producto img ON p.id_producto = img.id_producto AND img.principal = 1
            LEFT JOIN sb_subasta s ON p.id_producto = s.id_producto
            WHERE p.id_usuario = ?
            ORDER BY p.id_producto DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $productos = [];
    while ($row = $resultado->fetch_assoc()) {
        $productos[] = $row;
    }
    $stmt->close();
    return $productos;
}

function modificarProductos($conexion, $id_producto, $nombre, $descripcion, $condicion, $id_categoria, $id_usuario){
    $sqlVerificar= "SELECT 1 FROM SB_PRODUCTO WHERE id_producto = ? AND id_usuario=?;";
    $stmt= $conexion->prepare($sqlVerificar);
    $stmt->bind_param("ii", $id_producto, $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $existe = $resultado && $resultado->num_rows > 0;
    if(!$existe){
        return ['estado' => false, 'mensaje' => 'El producto no existe o no pertenece al usuario'];
    }

    $stmt->close();

    try{
        $conexion->begin_transaction();
        $sql= "UPDATE sb_producto
        SET nombre = ?, descripcion = ?, condicion = ?, id_categoria = ?
        WHERE id_producto = ? AND id_usuario = ?;";
        $stmt=$conexion->prepare($sql);
        $stmt->bind_param("sssiii", $nombre, $descripcion, 
        $condicion, $id_categoria, $id_producto, $id_usuario);

        if (!$stmt->execute()) {
            throw new RuntimeException('Error al actualizar el producto: ' . $stmt->error);
        }
        $stmt->close();
        $conexion->commit     ();
        return ['estado' => true, 'mensaje' => 'Producto actualizado exitosamente']; 
            
    }catch(Throwable $e){
        $conexion->rollback();
        error_log($e->getMessage());  
        return ['estado' => false, 'mensaje' => 'No se pudo actualizar el producto'];
    }

    
}