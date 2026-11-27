<?php
require_once __DIR__ . '/../config/conexion.php';

class PerfilDAO
{
    const ROL_ADMIN = 1;
    const ROL_USUARIO = 2;
    const ROL_MODERADOR = 3;

    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }

    /** Datos user sb_usuario + su rol + su correo + su tel Devuelve null si el usuario no existe. */
    public function obtenerPerfil($id_usuario)
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.username, u.ubicacion, u.estado, u.id_rol,
                       COALESCE(r.nombre, '') AS rol_nombre,
                       (SELECT c.correo FROM sb_correo_usuario c WHERE c.id_usuario = u.id_usuario LIMIT 1) AS correo,
                       (SELECT t.telefono FROM sb_telefono_usuario t WHERE t.id_usuario = u.id_usuario LIMIT 1) AS telefono
                FROM sb_usuario u
                LEFT JOIN sb_rol r ON r.id_rol = u.id_rol
                WHERE u.id_usuario = ?
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $perfil = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $perfil ?: null;
    }

   
    //productos registrados-subastas activas-subastas pendientes de revision
    public function obtenerResumenUsuario($id_usuario)
    {
        $sql = "SELECT COUNT(DISTINCT p.id_producto) AS productos,
                       COUNT(DISTINCT CASE WHEN LOWER(s.estado) = 'activa' AND s.fecha_final > NOW() THEN s.id_subasta END) AS activas,
                       COUNT(DISTINCT CASE WHEN LOWER(s.estado) = 'pendiente' THEN s.id_subasta END) AS pendientes
                FROM sb_producto p
                LEFT JOIN sb_subasta s ON s.id_producto = p.id_producto
                WHERE p.id_usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return [
            'productos' => (int)($res['productos'] ?? 0),
            'activas' => (int)($res['activas'] ?? 0),
            'pendientes' => (int)($res['pendientes'] ?? 0)
        ];
    }

    //subastas activas del usuario que terminan primero con la puja ms alta
     
    public function obtenerSubastasActivas($id_usuario, $limite = 3)
    {
        $limite = max(1, (int)$limite);
        $sql = "SELECT p.id_producto, p.nombre, img.ruta AS imagen_principal,
                       s.id_subasta, s.precio_inicial, s.fecha_final,
                       COUNT(pu.id_puja) AS total_pujas,
                       COALESCE(MAX(pu.monto), s.precio_inicial) AS precio_actual,
                       TIMESTAMPDIFF(SECOND, NOW(), s.fecha_final) AS segundos_restantes
                FROM sb_producto p
                INNER JOIN sb_subasta s ON s.id_producto = p.id_producto
                LEFT JOIN sb_imagen_producto img ON img.id_producto = p.id_producto AND img.principal = 1
                LEFT JOIN sb_puja pu ON pu.id_subasta = s.id_subasta
                WHERE p.id_usuario = ? AND LOWER(s.estado) = 'activa' AND s.fecha_final > NOW()
                GROUP BY p.id_producto, p.nombre, img.ruta, s.id_subasta, s.precio_inicial, s.fecha_final
                ORDER BY s.fecha_final ASC
                LIMIT {$limite}";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $subastas = [];
        while ($fila = $res->fetch_assoc()) {
            $subastas[] = $fila;
        }
        $stmt->close();

        return $subastas;
    }


    // MODERADOR Y ADMINISTRADOR
       
    //estado subastas

    public function obtenerResumenModeracion()
    {
        $sql = "SELECT
                    COALESCE(SUM(LOWER(estado) = 'pendiente'), 0) AS pendientes,
                    COALESCE(SUM(LOWER(estado) = 'activa' AND fecha_final > NOW()), 0) AS activas,
                    COALESCE(SUM(LOWER(estado) IN ('finalizada', 'sin_ofertas')), 0) AS cerradas
                FROM sb_subasta";
        $res = $this->conexion->query($sql);
        $fila = $res ? $res->fetch_assoc() : [];

        return [
            'pendientes' => (int)($fila['pendientes'] ?? 0),
            'activas' => (int)($fila['activas'] ?? 0),
            'cerradas' => (int)($fila['cerradas'] ?? 0)
        ];
    }

    //users muestra al admin

    public function obtenerResumenUsuariosAdmin()
    {
        $sql = "SELECT COUNT(*) AS total,
                       COALESCE(SUM(estado = 1), 0) AS verificados,
                       COALESCE(SUM(estado = 0), 0) AS sin_verificar,
                       COALESCE(SUM(id_rol = " . self::ROL_MODERADOR . "), 0) AS moderadores
                FROM sb_usuario";
        $res = $this->conexion->query($sql);
        $fila = $res ? $res->fetch_assoc() : [];

        return [
            'total' => (int)($fila['total'] ?? 0),
            'verificados' => (int)($fila['verificados'] ?? 0),
            'sin_verificar' => (int)($fila['sin_verificar'] ?? 0),
            'moderadores' => (int)($fila['moderadores'] ?? 0)
        ];
    }

    public function existeUsernameEnOtro($username, $id_usuario)
    {
        $stmt = $this->conexion->prepare("SELECT id_usuario FROM sb_usuario WHERE username = ? AND id_usuario <> ? LIMIT 1");
        $stmt->bind_param("si", $username, $id_usuario);
        $stmt->execute();
        $existe = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $existe;
    }

    // actualizar modificaciones 
    public function actualizarDatos($id_usuario, $nombre, $apellido, $ubicacion, $telefono, $username)
    {
        $this->conexion->begin_transaction();
        try {
            $sql = "UPDATE sb_usuario SET nombre = ?, apellido = ?, ubicacion = ?, username = ? WHERE id_usuario = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("ssssi", $nombre, $apellido, $ubicacion, $username, $id_usuario);
            $stmt->execute();
            $stmt->close();
 
            // el telefono esta en otra tabla, si no tenia uno se inserta
            $sqlTel = "SELECT id_usuario FROM sb_telefono_usuario WHERE id_usuario = ? LIMIT 1";
            $stmtTel = $this->conexion->prepare($sqlTel);
            $stmtTel->bind_param("i", $id_usuario);
            $stmtTel->execute();
            $existe = $stmtTel->get_result()->fetch_assoc();
            $stmtTel->close();
 
            if ($existe) {
                $sqlUp = "UPDATE sb_telefono_usuario SET telefono = ? WHERE id_usuario = ?";
            } else {
                $sqlUp = "INSERT INTO sb_telefono_usuario (telefono, id_usuario) VALUES (?, ?)";
            }
            $stmtUp = $this->conexion->prepare($sqlUp);
            $stmtUp->bind_param("si", $telefono, $id_usuario);
            $stmtUp->execute();
            $stmtUp->close();
 
            $this->conexion->commit();
            return ["estado" => true, "mensaje" => "Tus datos se actualizaron correctamente."];
        } catch (Throwable $e) {
            $this->conexion->rollback();
            if ((int)$e->getCode() === 1062 || (int)$this->conexion->errno === 1062) {
                return ["estado" => false, "campo" => "username", "mensaje" => "Este nombre de usuario ya está en uso."];
            }
            error_log("[PerfilDAO] Error al actualizar datos: " . $e->getMessage());
            return ["estado" => false, "mensaje" => "No se pudieron guardar los cambios. Inténtalo de nuevo."];
        }
    }


    //Contra
    public function cambiarContrasenia($id_usuario, $contraseniaActual, $contraseniaNueva)
    {
        $sql = "SELECT contrasenia FROM sb_usuario WHERE id_usuario = ? AND estado = 1 LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$fila) {
            return ["estado" => false, "mensaje" => "No se encontró tu cuenta o está inactiva."];
        }

        if (!password_verify($contraseniaActual, $fila['contrasenia'])) {
            return ["estado" => false, "campo" => "actual", "mensaje" => "La contraseña actual no es correcta."];
        }

        if (password_verify($contraseniaNueva, $fila['contrasenia'])) {
            return ["estado" => false, "campo" => "nueva", "mensaje" => "La contraseña nueva debe ser distinta a la actual."];
        }

        $hash = password_hash($contraseniaNueva, PASSWORD_DEFAULT);
        $sqlUp = "UPDATE sb_usuario SET contrasenia = ? WHERE id_usuario = ?";
        $stmtUp = $this->conexion->prepare($sqlUp);
        $stmtUp->bind_param("si", $hash, $id_usuario);
        $ok = $stmtUp->execute();
        $stmtUp->close();

        if (!$ok) {
            return ["estado" => false, "mensaje" => "No se pudo actualizar la contraseña. Inténtalo de nuevo."];
        }

        // codigos no sirven pasado el tiempo
        $sqlCod = "UPDATE sb_codigo_verificacion SET utilizado = 1
                   WHERE id_usuario = ? AND tipo = 'recuperacion' AND utilizado = 0";
        $stmtCod = $this->conexion->prepare($sqlCod);
        if ($stmtCod) {
            $stmtCod->bind_param("i", $id_usuario);
            $stmtCod->execute();
            $stmtCod->close();
        }

        return ["estado" => true, "mensaje" => "Tu contraseña se actualizó correctamente."];
    }
}
