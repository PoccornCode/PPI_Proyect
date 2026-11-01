<?php
if (!file_exists(__DIR__ . '/env.php')) {
    die("Error: No se encontró el archivo env.php con las credenciales.");
}

require_once __DIR__ . '/env.php';

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
