<?php
if (!file_exists(__DIR__ . '/env.php')) {
    die("Error: No se encontró el archivo env.php con las credenciales.");
}

require_once __DIR__ . '/env.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conexion->connect_error) {
        throw new Exception($conexion->connect_error);
    }

    $conexion->set_charset("utf8mb4");
} catch (Throwable $e) {
    include dirname(__DIR__) . '/500.php';
    exit;
}
