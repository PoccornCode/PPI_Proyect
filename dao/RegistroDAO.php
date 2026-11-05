<?php
require_once __DIR__ . '/../config/conexion.php';

class RegistroDAO
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }

    public function existeUsername($username)
    {
        $sql = "SELECT id_usuario FROM sb_usuario WHERE username = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $existe = $res->num_rows > 0;
        $stmt->close();
        return $existe;
    }


    public function existeCorreo($correo)
    {
        $sql = "SELECT id_usuario FROM sb_correo_usuario WHERE correo = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $res = $stmt->get_result();
        $existe = $res->num_rows > 0;
        $stmt->close();
        return $existe;
    }


    public function registrar($nombre, $apellido, $correo, $telefono, $ubicacion, $username, $contrasenia, $id_rol = 2)
    {
        if ($this->existeUsername($username)) {
            return [
                "estado" => false,
                "mensaje" => "El nombre de usuario ya está registrado. Por favor elige otro."
            ];
        }

        if ($this->existeCorreo($correo)) {
            return [
                "estado" => false,
                "mensaje" => "El correo electrónico ya se encuentra registrado."
            ];
        }

        $this->conexion->begin_transaction();

        try {
            $passHash = password_hash($contrasenia, PASSWORD_DEFAULT);
            $sqlUser = "INSERT INTO sb_usuario (nombre, apellido, username, contrasenia, ubicacion, estado, id_rol) VALUES (?, ?, ?, ?, ?, 1, ?)";
            $stmtUser = $this->conexion->prepare($sqlUser);
            $stmtUser->bind_param("sssssi", $nombre, $apellido, $username, $passHash, $ubicacion, $id_rol);

            if (!$stmtUser->execute()) {
                throw new Exception("Error al guardar el usuario: " . $stmtUser->error);
            }

            $id_usuario = $this->conexion->insert_id;
            $stmtUser->close();

            $sqlCorreo = "INSERT INTO sb_correo_usuario (id_usuario, correo) VALUES (?, ?)";
            $stmtCorreo = $this->conexion->prepare($sqlCorreo);
            $stmtCorreo->bind_param("is", $id_usuario, $correo);
            if (!$stmtCorreo->execute()) {
                throw new Exception("Error al asociar el correo electrónico: " . $stmtCorreo->error);
            }
            $stmtCorreo->close();

            $sqlTel = "INSERT INTO sb_telefono_usuario (id_usuario, telefono) VALUES (?, ?)";
            $stmtTel = $this->conexion->prepare($sqlTel);
            $stmtTel->bind_param("is", $id_usuario, $telefono);
            if (!$stmtTel->execute()) {
                throw new Exception("Error al asociar el teléfono: " . $stmtTel->error);
            }
            $stmtTel->close();

            $this->conexion->commit();

            return [
                "estado" => true,
                "mensaje" => "Usuario registrado correctamente."
            ];
        } catch (Throwable $e) {
            $this->conexion->rollback();
            return [
                "estado" => false,
                "mensaje" => "Error en la base de datos: " . $e->getMessage()
            ];
        }
    }
}
