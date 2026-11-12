<?php
require_once __DIR__ . '/../dao/ProductoDAO.php';
require_once __DIR__ . '/../config/conexion.php';

$nombre= trim($_POST['nombre']??'');
$id_categoria= (int)($_POST['id_categoria']??0);
$id_usuario= (int)($_POST['id_usuario']??0);
$condicion= trim($_POST['condicion']??'');
$descripcion= trim($_POST['descripcion']??'');
$imagenes=$_FILES['imagenes']?? null;
$opcion= $_POST['opcion']??'';

if($opcion==="guardar"){
    $rutas = insertarImagenes($imagenes);
    
    $resultado= insertarProducto($nombre, $descripcion, $condicion, $id_categoria, $rutas);
}

    
