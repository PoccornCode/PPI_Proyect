<?php
require_once __DIR__ . '/../config/conexion.php';


class subastaDao
{
    private $conexion;
    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }

    public function getSubastaDao($id)
    {
        $sql = "SELECT sb.precio_inicial,sb.incremento_minimo,sb.fecha_inicial,sb.fecha_final,
                sb.estado, sbp.nombre,sbp.descripcion,sbp.condicion,
                sbu.username as nombreU, sbc.nombre as nombreC,sbp.id_producto
                FROM sb_subasta sb
                JOIN sb_producto sbp ON sb.id_producto = sbp.id_producto
                JOIN sb_usuario sbU ON sbu.id_usuario = sbp.id_usuario
                JOIN sb_categoria sbc ON sbc.id_categoria = sbp.id_categoria
                WHERE sb.id_subasta = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $datos = $res->fetch_assoc();
        $stmt->close();

        $sql2 = "SELECT ruta FROM sb_imagen_producto WHERE id_producto = ?;";
        $stmt2 = $this->conexion->prepare($sql2);
        $stmt2->bind_param("i", $datos["id_producto"]);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        $datos2 = $res2->fetch_all(MYSQLI_ASSOC);
        $rutas = array_column($datos2, "ruta");
        $stmt2->close();

        $sql3 = "SELECT sbp.monto, sbp.fecha, sbp.id_usuario,
                sbu.username
                FROM sb_puja sbp
                JOIN sb_usuario sbu ON sbu.id_usuario = sbp.id_usuario
                WHERE sbp.id_subasta = ?";
        $stmt3 = $this->conexion->prepare($sql3);
        $stmt3->bind_param("i", $id);
        $stmt3->execute();
        $res3 = $stmt3->get_result();
        $datos3 = $res3->fetch_all(MYSQLI_ASSOC);
        $pujas = array_column($datos3, "monto");
        $stmt3->close();

        if (1 > 0) {
            return [
                "estado" => true,
                "datos" => [
                    "precio_inicial" => $datos["precio_inicial"],
                    "dato_incremento" => $datos["incremento_minimo"],
                    "fecha_inicio" => $datos["fecha_inicial"],
                    "fecha_cierre" => $datos["fecha_final"],
                    "estado" => $datos["estado"],
                    "nombre" => $datos["nombre"],
                    "condicion" => $datos["condicion"],
                    "descripcion" => $datos["descripcion"],
                    "nombreU" => $datos["nombreU"],
                    "nombreC" => $datos["nombreC"],
                    "datoPostores" => $datos["nombreC"]
                ],
                "pujas" => $pujas,
                "rutasImg" => $rutas
            ];
        }
        $this->conexion->close();
    }
}

?>