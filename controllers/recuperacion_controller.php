<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../dao/RecuperacionDAO.php';

$opcion           = $_POST['opcion'] ?? '';
$correo           = trim($_POST['correo'] ?? '');
$codigo           = trim($_POST['codigo'] ?? '');
$nuevaContrasenia = $_POST['nueva_contrasenia'] ?? '';

$dao = new RecuperacionDAO();

if ($opcion === "solicitarCodigo" || $opcion === "reenviarCodigo") {
    if (empty($correo)) {
        echo json_encode([
            "estado"  => false,
            "mensaje" => "Por favor ingresa tu correo electrónico."
        ]);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            "estado"  => false,
            "mensaje" => "El formato del correo electrónico no es válido."
        ]);
        exit;
    }

    $resultado = $dao->solicitarCodigoRecuperacion($correo, 15);
    echo json_encode($resultado);
    exit;
}

if ($opcion === "restablecer") {
    if (empty($correo) || empty($codigo) || empty($nuevaContrasenia)) {
        echo json_encode([
            "estado"  => false,
            "mensaje" => "Todos los campos son obligatorios."
        ]);
        exit;
    }

    if (!preg_match('/^\d{6}$/', $codigo)) {
        echo json_encode([
            "estado"  => false,
            "mensaje" => "El código debe contener exactamente 6 dígitos numéricos."
        ]);
        exit;
    }

    $regexContrasenia = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s]).{8,}$/';
    if (!preg_match($regexContrasenia, $nuevaContrasenia)) {
        echo json_encode([
            "estado"  => false,
            "mensaje" => "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo."
        ]);
        exit;
    }

    $resultado = $dao->restablecerContrasenia($correo, $codigo, $nuevaContrasenia);
    echo json_encode($resultado);
    exit;
}

echo json_encode([
    "estado"  => false,
    "mensaje" => "Opción no válida o no especificada."
]);
exit;