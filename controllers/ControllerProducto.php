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
$id_producto= $_POST['id_producto']??'';
$id_usuario = (int)($_SESSION['id_usuario'] ?? 0);
$id_producto = (int)($_GET['id'] ?? 0);


function subirImagenes(?array $archivos): array {
    $carpeta = __DIR__ . '/../uploads/productos/';
    $nombres = [];

    if (!$archivos || empty($archivos['name'][0])) {
        return $nombres;
    }

    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0755, true);
    }

    foreach ($archivos['name'] as $i => $nombreOriginal) {
        if ($archivos['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        $tmp = $archivos['tmp_name'][$i];

        if (getimagesize($tmp) === false) {
            throw new RuntimeException('Uno de los archivos no es una imagen');
        }

        $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $nuevo = bin2hex(random_bytes(8)) . '.' . $ext;

        if (!move_uploaded_file($tmp, $carpeta . $nuevo)) {
            throw new RuntimeException('No se pudo mover el archivo');
        }

        $nombres[] = $nuevo;
    }

    return $nombres;
}

if ($id_usuario === 0) {
    echo json_encode(['estado' => false, 'mensaje' => 'Debes iniciar sesión']);
    exit;
}

if ($nombre === '' || $id_categoria === 0 || $condicion === '') {
    echo json_encode(['estado' => false, 'mensaje' => 'Faltan datos obligatorios']);
    exit;
}

if($opcion==="guardar"){
    try {
        $rutas = subirImagenes($imagenes);
    } catch (RuntimeException $e) {
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

if ($opcion === "modificar") {
    $id_producto = (int)($_POST['id_producto'] ?? 0);

    if ($id_producto === 0) {
        echo json_encode(['estado' => false, 'mensaje' => 'Falta el id del producto']);
        exit;
    }

    $resultado = modificarProductos($conexion, $nombre, $descripcion, $condicion, $id_categoria, $id_producto, $id_usuario);

    if ($resultado['estado']) {
        $_SESSION['flash_toast'] = [
            'tipo' => 'success',
            'titulo' => '¡Producto actualizado!',
            'mensaje' => 'Los cambios se guardaron correctamente.'
        ];
    }

    echo json_encode($resultado);
    exit;
}

if ($id_producto > 0) {
    $producto = obtenerProductoPorId($conexion, $id_producto, (int)$_SESSION['id_usuario']);

    if (!$producto) {
        $_SESSION['flash_toast'] = [
            'tipo' => 'warning',
            'titulo' => 'Producto no encontrado',
            'mensaje' => 'No existe o no te pertenece.'
        ];
        header("Location: " . BASE_URL . "views/subastas/mis-productos.php");
        exit();
    }
}

$textoBoton = $producto ? "Modificar" : "Guardar Producto";

$condiciones = [
    "Nuevo" => "Nuevo (Sellado en empaque original)",
    "Como nuevo" => "Como nuevo (Sin marcas de uso o defectos)",
    "Excelente" => "Excelente (Mínimo uso, excelente estado funcional)",
    "Buen estado" => "Buen estado (Signos leves de uso normal)",
    "Aceptable" => "Aceptable (Marcas visibles de desgaste, funcional)",
    "Para repuestos o restauración" => "Para repuestos o restauración"
];
    
