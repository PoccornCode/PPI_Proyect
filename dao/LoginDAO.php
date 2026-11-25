<?php
require_once __DIR__ . '/../config/conexion.php';

class LoginDAO
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }

    public function obtenerPorUsername($username)
    {
        $sql = "SELECT id_usuario, nombre, apellido, username, contrasenia, estado, id_rol
                FROM sb_usuario
                WHERE username = ?
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $usuario = $res->fetch_assoc();
        $stmt->close();
        return $usuario ?: null;
    }

    private function obtenerVerificacionPendiente(int $idUsuario): ?string
    {
        $sql = "SELECT c.correo
                FROM sb_codigo_verificacion cv
                INNER JOIN sb_correo_usuario c ON c.id_usuario = cv.id_usuario
                WHERE cv.id_usuario = ? AND cv.tipo = 'registro' AND cv.utilizado = 0
                ORDER BY cv.id_codigo DESC
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $fila = $res->fetch_assoc();
        $stmt->close();
        return $fila['correo'] ?? null;
    }

    public function autenticar($username, $contrasenia)
    {
        try {
            $usuario = $this->obtenerPorUsername($username);

            // Mensaje genérico para no revelar si el usuario existe o no
            if (!$usuario || !password_verify($contrasenia, $usuario['contrasenia'])) {
                return [
                    "estado" => false,
                    "mensaje" => "Usuario o contraseña incorrectos."
                ];
            }

            if ((int)$usuario['estado'] !== 1) {
                $correoPendiente = $this->obtenerVerificacionPendiente((int)$usuario['id_usuario']);
                if ($correoPendiente !== null) {
                    return [
                        "estado" => false,
                        "requiere_verificacion" => true,
                        "correo" => $correoPendiente,
                        "mensaje" => "Tu cuenta aún no está verificada. Te llevaremos a ingresar o reenviar tu código de verificación."
                    ];
                }

                return [
                    "estado" => false,
                    "mensaje" => "Tu cuenta está inactiva. Contacta al administrador."
                ];
            }

            //  hash si el algoritmo por defecto cambio
            if (password_needs_rehash($usuario['contrasenia'], PASSWORD_DEFAULT)) {
                $nuevoHash = password_hash($contrasenia, PASSWORD_DEFAULT);
                $sql = "UPDATE sb_usuario SET contrasenia = ? WHERE id_usuario = ?";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bind_param("si", $nuevoHash, $usuario['id_usuario']);
                $stmt->execute();
                $stmt->close();
            }

            unset($usuario['contrasenia']);

            return [
                "estado" => true,
                "mensaje" => "Inicio de sesión exitoso.",
                "usuario" => $usuario
            ];
        } catch (Throwable $e) {
            return [
                "estado" => false,
                "mensaje" => "Error en la base de datos: " . $e->getMessage()
            ];
        }
    }
}