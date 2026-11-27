<?php
require_once __DIR__ . '/../config/conexion.php';


class subastaDetalleDao
{
    private $conexion;
    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }
    public function getPujas($id)
    {
        $sql3 = "SELECT sbp.monto, sbp.fecha, sbp.id_usuario,
                sbu.username
                FROM sb_puja sbp
                JOIN sb_usuario sbu ON sbu.id_usuario = sbp.id_usuario
                WHERE sbp.id_subasta = ?
                ORDER BY sbp.monto DESC";
        $stmt3 = $this->conexion->prepare($sql3);
        $stmt3->bind_param("i", $id);
        $stmt3->execute();
        $res3 = $stmt3->get_result();
        $datos3 = $res3->fetch_all(MYSQLI_NUM);
        $stmt3->close();

        $sql4 = "SELECT COUNT(DISTINCT id_usuario) FROM sb_puja 
                WHERE id_subasta = ?";
        $stmt4 = $this->conexion->prepare($sql4);
        $stmt4->bind_param("i", $id);
        $stmt4->execute();
        $res4 = $stmt4->get_result();
        $row4 = $res4->fetch_row();
        $stmt4->close();

        return [
            "pujas" => $datos3,
            "postores" => (int) ($row4[0] ?? 0)
        ];
    }
    public function getSubastaDao($id)
    {
        $sql = "SELECT sb.precio_inicial,sb.incremento_minimo,sb.fecha_inicial,sb.fecha_final,
                sb.estado, sbp.nombre,sbp.descripcion,sbp.condicion,
                sbu.username as nombreU, sbc.nombre as nombreC,sbp.id_producto
                FROM sb_subasta sb
                JOIN sb_producto sbp ON sb.id_producto = sbp.id_producto
                JOIN sb_usuario sbu ON sbu.id_usuario = sbp.id_usuario
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
                "rutasImg" => $rutas
            ];
        }
        $this->conexion->close();
    }

    public function guardarPuja($monto, $idUsuario, $idSubasta)
    {
        $begin = $this->conexion->begin_transaction();
        try {
            $sql = "SELECT sb.precio_inicial,sb.incremento_minimo,sb.fecha_inicial,
                    sb.fecha_final,sb.estado
                    FROM sb_subasta sb
                    WHERE sb.id_subasta = ? FOR UPDATE";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $idSubasta);
            $stmt->execute();
            $res = $stmt->get_result();
            $datos = $res->fetch_assoc();
            $stmt->close();

            if ($datos["estado"] === "activa") {
                $sql = "SELECT MAX(monto) as monto FROM sb_puja WHERE id_subasta = ?";
                $st = $this->conexion->prepare($sql);
                $st->bind_param("i", $idSubasta);
                $st->execute();
                $res2 = $st->get_result();
                $resultado = $res2->fetch_assoc();

                if ($resultado["monto"] > $monto) {
                    return [
                        "estado" => false,
                        "tipo" => "danger",
                        "titulo" => "¡Te superaron!",
                        "mensaje" => "hay una puja nueva que supera la ingresas"
                    ];
                } else {
                    $sql2 = "INSERT INTO sb_puja (monto,fecha,id_subasta,id_usuario)
                            VALUES (?,NOW(),?,?)";
                    $st2 = $this->conexion->prepare($sql2);
                    $st2->bind_param("dii", $monto, $idSubasta, $idUsuario);
                    if ($st2->execute()) {
                        $this->conexion->commit();
                        return [
                            "estado" => true,
                            "tipo" => "success",
                            "titulo" => "¡Hecho!",
                            "mensaje" => "Puja realizada correctamente"
                        ];
                    } else {
                        $this->conexion->rollback();
                        return [
                            "estado" => false,
                            "tipo" => "error",
                            "titulo" => "¡Error!",
                            "mensaje" => "No se pudo realizar la puja"
                        ];
                    }
                }
            }
        } catch (mysqli_sql_exception $e) {
            $this->conexion->rollback();
            return [
                "estado" => false,
                "tipo" => "error",
                "titulo" => "¡Alerta!",
                "mensaje" => $e->getMessage()
            ];
        }
        return [
            "estado" => false,
            "tipo" => "error",
            "titulo" => "!Error¡",
            "mensaje" => "¡Algo ha sucedido! Por favor recarga la pagina"
        ];
    }
}

?>