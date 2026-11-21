<?php
    session_start();
    header("Content-Type: application/json; charset=UTF-8");

    require_once __DIR__ . '/../dao/ProductoDAO.php';
    require_once __DIR__ . '/../config/conexion.php';

    $nombre= trim($_POST['nombre']??'');
    $id_categoria= (int)($_POST['id_categoria']??0);
    $condicion= trim($_POST['condicion']??'');
    $descripcion= trim($_POST['descripcion']??'');
    $imagenes=$_FILES['imagenes']?? null;
    $opcion= $_POST['opcion']??'';
    $id_usuario = (int)($_SESSION['id_usuario'] ?? 0);
    $id_producto = (int)($_POST['id_producto'] ?? $_GET['id'] ?? 0);
    $idrutasEliminar = array_values(  // vuelve a numerar los índices desde 0
        array_unique( // quita los valores repetidos
            array_map('intval', // convierte cada valor a número entero
                $_POST['idrutasEliminar'] ?? []// toma la lista del formulario, o vacía si no llegó nada
            )
        )
    );

    function subirImagenes(?array $archivos): array{ // que la funcion siempre devuelve y el array ? es que puede ser null 
        $carpeta = __DIR__ . '/../uploads/productos/';
        $nombres = [];
        if (!$archivos || empty($archivos['name'][0])) {
            return $nombres;
        }

        if (count($archivos['name']) > 5) {
            throw new RuntimeException('Puedes subir máximo 5 imágenes');
        }

        if (!is_dir($carpeta)) { //Crear carpta si no existe
            mkdir($carpeta, 0755, true); //0755 son los permisos y el true es que se cree de forma recursiva las carpetas
        }

        try {
            foreach ($archivos['name'] as $i => $nombreOriginal) {
                if ($archivos['error'][$i] !== UPLOAD_ERR_OK) {
                    throw new RuntimeException('Uno de los archivos no se pudo subir');
                }

                $tmp = $archivos['tmp_name'][$i]; //'tmp_name' ruta temporal de los archivos guardada en xamp/tmp
                if ($archivos['size'][$i] > 5 * 1024 * 1024) {
                    throw new RuntimeException('Cada imagen debe pesar menos de 5 MB');
                }

                $info = getimagesize($tmp);
                if ($info === false) {
                    throw new RuntimeException('Uno de los archivos no es una imagen');
                }

                $extPermitidas = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                if (!isset($extPermitidas[$info['mime']])) {
                    throw new RuntimeException('Formato de imagen no permitido');
                }
                $ext = $extPermitidas[$info['mime']];
                $nuevo = bin2hex(random_bytes(8)) . '.' . $ext; //genera un nombre aleatorio

                if (!move_uploaded_file($tmp, $carpeta . $nuevo)) { //move_uploaded_file mueve el archivo temporal a la carpeta de productos, con el nombre nuevo.
                    throw new RuntimeException('No se pudo mover el archivo');
                }

                $nombres[] = $nuevo;
            }
        } catch (Throwable $e) {
            foreach ($nombres as $nombreBorrar) {
                @unlink($carpeta . $nombreBorrar);
            }
            throw $e;
        }
        return $nombres;
    }

    if ($id_usuario === 0) {
        echo json_encode(['estado' => false, 'mensaje' => 'Debes iniciar sesión']);
        exit;
    }

    if ($opcion === "obtener") {
        $producto = obtenerProductoPorId($conexion, $id_producto, $id_usuario);

        if (!$producto) {
            echo json_encode(['estado' => false, 'mensaje' => 'Ese producto no existe o no es tuyo.']);
            exit;
        }

        $fotos = obtenerImagenesProducto($conexion, $id_producto);
        echo json_encode(['estado' => true, 'producto' => $producto, 'imagenes' => $fotos]);
        exit;
    }

    if ($opcion === "eliminar") {
        $resultado = eliminar($conexion, $id_producto, $id_usuario);
        echo json_encode($resultado);
        exit;
    }

    if ($nombre === '' || $id_categoria === 0 || $condicion === '') {
        echo json_encode(['estado' => false, 'mensaje' => 'Faltan datos obligatorios']);
        exit;
    }

    if($opcion==="guardar"){
        try {
            $rutas = subirImagenes($imagenes);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            echo json_encode(['estado' => false, 'mensaje' => $e->getMessage()]);
            exit;
        }

        $resultado= insertarProducto($conexion, $nombre, $descripcion, 
        $condicion, $id_categoria, $id_usuario, $rutas);

        if ($resultado['estado']) {
            $_SESSION['flash_toast'] = [
                'tipo' => 'success',
                'titulo' => '¡Producto registrado con éxito!',
                'mensaje' => 'Tu producto ya está en tu lista de productos listo para asignarle una subasta.'
            ];
        }

        echo json_encode($resultado);
    }

    if($opcion==="modificar"){
        try {
            $rutasNuevas = subirImagenes($imagenes);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            echo json_encode(['estado' => false, 'mensaje' => $e->getMessage()]);
            exit;
        }
        $resultado=modificarProducto($conexion, $id_producto, $nombre, $descripcion, $condicion, $id_categoria, 
        $id_usuario, $rutasNuevas, $idrutasEliminar);
        if ($resultado['estado']) {
            $_SESSION['flash_toast'] = [
                'tipo' => 'success',
                'titulo' => '¡Producto modificado con éxito!',
                'mensaje' => 'Tu producto ya está actualizado en tu lista de productos listo para asignarle una subasta.'
            ];
        }
        echo json_encode($resultado);
    }

   
    
        
