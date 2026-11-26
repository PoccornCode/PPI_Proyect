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
            $opcion="guardar";

            insertarImagenes($conexion, $id_producto, $rutas, $opcion);
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

    function insertarImagenes($conexion, $id_producto, $rutas, $opcion){
        $sql= "INSERT INTO sb_imagen_producto (id_producto, principal, ruta) 
        VALUES (?, ?, ?)";
        $stmtImg=$conexion->prepare($sql); 
        $ruta="";
        $principal=0;
        $stmtImg->bind_param("iis", $id_producto, $principal, $ruta);

        if($opcion==="guardar"){
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
        }else{
            foreach ($rutas as $index => $r) {
                $ruta= $r;     
                if (!$stmtImg->execute()) {
                    $stmtImg->close();
                    throw new RuntimeException('Error al guardar una imágen: ' . $stmtImg->error);
                }
            }
        }

        $stmtImg->close();
    }

    function obtenerProductosPorUsuario($conexion, $id_usuario) {
        $sql = "SELECT p.id_producto, p.nombre, p.descripcion, p.condicion, p.estado,
                    c.nombre AS categoria,
                    img.ruta AS imagen_principal,
                    s.id_subasta, s.estado AS estado_subasta, s.precio_inicial, s.fecha_inicial, s.fecha_final, s.motivo_rechazo,
                    (SELECT COUNT(*) FROM sb_puja pu WHERE pu.id_subasta = s.id_subasta) AS total_pujas
                FROM sb_producto p
                LEFT JOIN sb_categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN sb_imagen_producto img ON p.id_producto = img.id_producto AND img.principal = 1
                LEFT JOIN (
                    SELECT id_producto, MAX(id_subasta) AS id_ultima
                    FROM sb_subasta
                    GROUP BY id_producto
                ) u ON u.id_producto = p.id_producto
                LEFT JOIN sb_subasta s ON s.id_subasta = u.id_ultima
                WHERE p.id_usuario = ? AND p.estado= 1
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

    function obtenerProductoPorId($conexion, $id_producto, $id_usuario){
        $sql = "SELECT p.id_producto, p.nombre, p.descripcion, p.condicion, p.estado, p.id_categoria, img.ruta AS imagen_principal
        FROM sb_producto p LEFT JOIN sb_imagen_producto img ON img.id_producto = p.id_producto AND img.principal = 1
        WHERE p.id_producto = ? AND p.id_usuario = ? AND p.estado = 1
        LIMIT 1;";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $id_producto, $id_usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $producto = $resultado->fetch_assoc();

        $stmt->close();
        return $producto;
    }

    function obtenerImagenesProducto($conexion, $id_producto){
        $sql = "SELECT id_imagen_producto, id_producto, principal, ruta
            FROM sb_imagen_producto
            WHERE id_producto = ?
            ORDER BY principal DESC, id_imagen_producto ASC";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_producto);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $imagenes = [];
        while ($row = $resultado->fetch_assoc()) {
            $imagenes[] = $row;
        }

        $stmt->close();
        return $imagenes;
    }


    function modificarProducto($conexion, $id_producto, $nombre, $descripcion, $condicion, $id_categoria, 
        $id_usuario, $rutasNuevas, $idrutasEliminar)
        {
        try{
            $conexion->begin_transaction();

            $sqlVerificar= "SELECT 1 FROM sb_producto WHERE id_producto = ? AND id_usuario=? AND estado = 1 FOR UPDATE;";
            $stmt= $conexion->prepare($sqlVerificar);
            $stmt->bind_param("ii", $id_producto, $id_usuario);

            if (!$stmt->execute()) {
                throw new RuntimeException('Error al verificar el producto: ' . $stmt->error);
            }

            $resultado = $stmt->get_result();
            $existe = $resultado && $resultado->num_rows > 0;

            if(!$existe){
                $stmt->close();
                $conexion->rollback();
                foreach ($rutasNuevas as $ruta) {
                    @unlink(__DIR__ . '/../uploads/productos/' . $ruta);
                }
                return ['estado' => false, 'mensaje' => 'El producto no existe o no pertenece al usuario'];
            }

            $stmt->close(); 
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

            //Actualizar imagenes 
            $rutasBorradas=actualizarImagenes($conexion, $id_producto, $rutasNuevas, $idrutasEliminar);
            $conexion->commit();

            foreach ($rutasBorradas as $ruta) {
                @unlink(__DIR__ . '/../uploads/productos/' . $ruta);      
            }
            return ['estado' => true, 'mensaje' => 'Producto actualizado exitosamente']; 

        }catch(Throwable $e){
            $conexion->rollback();
            foreach ($rutasNuevas as $ruta) {
                @unlink(__DIR__ . '/../uploads/productos/' . $ruta);
            }
            error_log($e->getMessage());  
            return ['estado' => false, 'mensaje' => 'No se pudo actualizar el producto'];
        }
    }

    function actualizarImagenes($conexion, $id_producto, $rutasNuevas, $idrutasEliminar){
        $sqlSelect="SELECT ruta, principal
        FROM sb_imagen_producto
        WHERE id_producto = ? AND id_imagen_producto= ?
        FOR UPDATE;";
        
        $stmt = $conexion->prepare($sqlSelect);
        $idrutaEliminar=0;
        $stmt->bind_param("ii", $id_producto, $idrutaEliminar);
        $rutas = [];

        foreach ($idrutasEliminar as $idrutaEliminar) {
            if (!$stmt->execute()) {
                throw new RuntimeException('Error al cargar la ruta la imágen: ' . $stmt->error);
            }
            $result = $stmt->get_result()->fetch_assoc();

            if (!$result) {
                throw new RuntimeException('La lista de fotos cambió. Recarga la página.');
            }

            $rutas[] = $result['ruta'];
        }

        $stmt->close();
        
        //Eliminar las rutas de la base de datos
        $sqlEliminar="DELETE FROM sb_imagen_producto
        WHERE id_producto = ? AND id_imagen_producto = ?;";

        $stmt = $conexion->prepare($sqlEliminar);
        $idImgEliminar=0;
        $stmt->bind_param("ii", $id_producto, $idImgEliminar);

        foreach ($idrutasEliminar as $idImgEliminar) {
            if (!$stmt->execute()) {
                throw new RuntimeException('Error al borrar la imagen: ' . $stmt->error);
            }
            if ($stmt->affected_rows === 0) {
                throw new RuntimeException('La imagen no existe o no pertenece al producto');
            }
       }

        $stmt->close();   
        $opcion="modificar";
        if(!empty($rutasNuevas)){
            insertarImagenes($conexion, $id_producto, $rutasNuevas, $opcion);
        }

        $sqlHay = "SELECT COUNT(*) AS total FROM sb_imagen_producto
        WHERE id_producto = ? AND principal = 1;";
        $stmt = $conexion->prepare($sqlHay);
        $stmt->bind_param("i", $id_producto);
        $stmt->execute();
        $hayPrincipal = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ((int)$hayPrincipal['total'] === 0){ //SI entonces guardo la primera imagen como principal 1 (Si es 1 fue pq el aparecio en idRutasEliminar)
            $sqlPrimera = "UPDATE sb_imagen_producto SET principal = 1
            WHERE id_producto = ?
            ORDER BY id_imagen_producto ASC
            LIMIT 1;";

            $stmt = $conexion->prepare($sqlPrimera);
            $stmt->bind_param("i", $id_producto);

            if (!$stmt->execute()) {
                throw new RuntimeException('Error al asignar la principal: ' . $stmt->error);
            }
            $stmt->close();
        }
        return $rutas;  
    }

    function eliminar($conexion, $id_producto, $id_usuario) {
        try {
            $conexion->begin_transaction();
            $sql="SELECT 1 FROM sb_producto WHERE id_producto=? AND id_usuario=?";
            $stmt=$conexion->prepare($sql);
            $stmt->bind_param("ii", $id_producto, $id_usuario);
            if(!$stmt->execute()){
                throw new RuntimeException('Error al eliminar el producto: ' . $stmt->error);
            }    
            
           $resultado = $stmt->get_result();
            if ($resultado->num_rows === 0) {
                throw new RuntimeException('El producto no existe o no pertenece al usuario');
            }

            $stmt->close();

            $imagenes=obtenerImagenesProducto($conexion, $id_producto);
            eliminarImagenes($conexion, $id_producto);

            $sqlEliminar = "UPDATE sb_producto SET estado = 0 WHERE id_producto = ? AND id_usuario = ?;";
            $stmt = $conexion->prepare($sqlEliminar);
            $stmt->bind_param("ii", $id_producto, $id_usuario);

            if (!$stmt->execute()) {
                throw new RuntimeException('Error al eliminar el producto: ' . $stmt->error);
            }

            if ($stmt->affected_rows === 0) {
                throw new RuntimeException('El producto ya estaba eliminado');
            }

            $stmt->close();
            $conexion->commit();

            foreach ($imagenes as $imagen) {
                @unlink(__DIR__ . '/../uploads/productos/' . $imagen['ruta']);
            }
            return ['estado' => true, 'mensaje' => 'Producto eliminado exitosamente'];

        } catch (\Throwable $e) {
            $conexion->rollback();
            error_log($e->getMessage());  
            return ['estado' => false, 'mensaje' => 'No se pudo eliminar el producto'];
        }
        
    }

    function eliminarImagenes($conexion, $id_producto){
        $sqlEliminarImg="DELETE FROM sb_imagen_producto WHERE id_producto = ?";
        $stmt=$conexion->prepare($sqlEliminarImg);
        $stmt->bind_param("i", $id_producto);
        
        if(!$stmt->execute()){
            throw new RuntimeException('Error al eliminar el producto: ' . $stmt->error);
        }  
        $stmt->close();
    }

    function cancelarSolicitud($conexion, $id_subasta, $id_usuario){
        try {
            $conexion->begin_transaction();
            $sql= "UPDATE sb_subasta s
            INNER JOIN sb_producto p ON p.id_producto = s.id_producto
            SET s.estado = 'cancelada'
            WHERE s.id_subasta = ? AND p.id_usuario = ? AND p.estado = 1
            AND s.estado = 'pendiente'
            AND NOT EXISTS (SELECT 1 FROM sb_puja pu WHERE pu.id_subasta = s.id_subasta);";
            $stmt=$conexion->prepare($sql);
            $stmt->bind_param("ii", $id_subasta, $id_usuario);

            if(!$stmt->execute()){
                throw new RuntimeException('Error al cancelar la subasta: ' . $stmt->error);
            }  
            $filasAfectadas=$stmt->affected_rows;
            $stmt->close();
            
            if ($filasAfectadas === 0) {
                throw new RuntimeException('Esta solicitud ya no se puede cancelar');
            }

            
            $conexion->commit();
            return ['estado' => true, 'mensaje' => 'Solicitud cancelada exitosamente'];
        } catch (\Throwable $th) {
            $conexion->rollback();
            error_log($th->getMessage());
             return ['estado' => false, 'mensaje' => 'No se pudo cancelar la subasta'];
        }


    }