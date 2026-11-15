<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../dao/RegistroDAO.php';

$opcion = $_POST['opcion'] ?? '';
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$ubicacion = trim($_POST['ubicacion'] ?? '');
$username = trim($_POST['username'] ?? ($_POST['nuevo_reg_username'] ?? ''));
$contrasenia = $_POST['contrasenia'] ?? ($_POST['nuevo_reg_pass'] ?? '');
$codigo = trim($_POST['codigo'] ?? '');

$dao = new RegistroDAO();

if ($opcion === "guardar") {
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($telefono) || empty($ubicacion) || empty($username) || empty($contrasenia)) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Por favor complete todos los campos obligatorios."
        ]);
        exit;
    }

    $regexContrasenia = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s]).{8,}$/';
    if (!preg_match($regexContrasenia, $contrasenia)) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un símbolo (@$!%*?&#.-_)."
        ]);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "El formato del correo electrónico no es válido."
        ]);
        exit;
    }

    $resultado = $dao->registrar($nombre, $apellido, $correo, $telefono, $ubicacion, $username, $contrasenia);
    echo json_encode($resultado);
    exit;
}

if ($opcion === "verificarCodigo") {
    if (empty($correo) || empty($codigo)) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "El correo y el código son obligatorios."
        ]);
        exit;
    }

    if (!preg_match('/^\d{6}$/', $codigo)) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "El código debe contener exactamente 6 dígitos numéricos."
        ]);
        exit;
    }

    $resultado = $dao->validarCodigo($correo, $codigo, 'registro');
    echo json_encode($resultado);
    exit;
}

if ($opcion === "reenviarCodigo") {
    if (empty($correo)) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "No se ha proporcionado el correo electrónico."
        ]);
        exit;
    }

    $resultado = $dao->reenviarCodigo($correo, 'registro');
    echo json_encode($resultado);
    exit;
}

if ($opcion === "consultarUsername") {
    $existe = $dao->existeUsername($username);
    echo json_encode([
        "estado" => $existe,
        "mensaje" => $existe ? "Este nombre de usuario ya está en uso" : "Disponible"
    ]);
    exit;
}

if ($opcion === "consultarCorreo") {
    $existe = $dao->existeCorreo($correo);
    echo json_encode([
        "estado" => $existe,
        "mensaje" => $existe ? "Este correo electrónico ya está registrado" : "Disponible"
    ]);
    exit;
}

echo json_encode([
    "estado" => false,
    "mensaje" => "Opción no válida o no especificada."
]);
exit;
