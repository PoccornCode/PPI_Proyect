<?php
require_once __DIR__ . '/../config/conexion.php';

class SubastaDAO
{
    private $conexion;

    public function __construct($conexion = null)
    {
        if ($conexion !== null) {
            $this->conexion = $conexion;
        } else {
            global $conexion;
            $this->conexion = $conexion;
        }
    }

    public function obtenerDestacada(): ?array
    {
        if (!$this->conexion || $this->conexion->connect_errno) {
            return null;
        }

        $sql = "SELECT s.id_subasta, s.precio_inicial, s.fecha_inicial, s.fecha_final, s.estado,
                       TIMESTAMPDIFF(SECOND, NOW(), s.fecha_final) AS segundos_restantes,
                       p.id_producto, p.nombre AS titulo, p.descripcion,
                       COALESCE(c.nombre, 'General') AS categoria,
                       img.ruta AS imagen_ruta,
                       COUNT(pu.id_puja) AS total_pujas,
                       COALESCE(MAX(pu.monto), s.precio_inicial) AS precio_actual
                FROM sb_subasta s
                INNER JOIN sb_producto p ON s.id_producto = p.id_producto
                LEFT JOIN sb_categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN sb_imagen_producto img ON p.id_producto = img.id_producto AND img.principal = 1
                LEFT JOIN sb_puja pu ON s.id_subasta = pu.id_subasta
                WHERE s.fecha_final > NOW()
                  AND LOWER(s.estado) = 'activa'
                GROUP BY s.id_subasta, s.precio_inicial, s.fecha_inicial, s.fecha_final, s.estado,
                         p.id_producto, p.nombre, p.descripcion, c.nombre, img.ruta
                ORDER BY total_pujas DESC, precio_actual DESC, s.fecha_final ASC
                LIMIT 1";

        $res = $this->conexion->query($sql);
        if ($res && ($row = $res->fetch_assoc())) {
            return $this->formatearSubasta($row, 'assets/img/news-1.jpg');
        }

        return null;
    }

    /*Obtiene las subastas activas más próximas a terminar.*/
    public function obtenerTerminanPronto(int $limite = 4, ?int $excluirId = null): array
    {
        $lista = [];
        if (!$this->conexion || $this->conexion->connect_errno) {
            return $lista;
        }

        $limite = max(1, (int)$limite);
        $condicionExcluir = $excluirId !== null ? " AND s.id_subasta != " . (int)$excluirId : "";

        $sql = "SELECT s.id_subasta, s.precio_inicial, s.fecha_inicial, s.fecha_final, s.estado,
                       TIMESTAMPDIFF(SECOND, NOW(), s.fecha_final) AS segundos_restantes,
                       p.id_producto, p.nombre AS titulo, p.descripcion,
                       COALESCE(c.nombre, 'General') AS categoria,
                       img.ruta AS imagen_ruta,
                       COALESCE(MAX(pu.monto), s.precio_inicial) AS precio_actual
                FROM sb_subasta s
                INNER JOIN sb_producto p ON s.id_producto = p.id_producto
                LEFT JOIN sb_categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN sb_imagen_producto img ON p.id_producto = img.id_producto AND img.principal = 1
                LEFT JOIN sb_puja pu ON s.id_subasta = pu.id_subasta
                WHERE s.fecha_final > NOW()
                  AND LOWER(s.estado) = 'activa'
                  {$condicionExcluir}
                GROUP BY s.id_subasta, s.precio_inicial, s.fecha_inicial, s.fecha_final, s.estado,
                         p.id_producto, p.nombre, p.descripcion, c.nombre, img.ruta
                ORDER BY s.fecha_final ASC
                LIMIT {$limite}";

        $res = $this->conexion->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $lista[] = $this->formatearSubasta($row, 'assets/img/news-2.jpg');
            }
        }

        return $lista;
    }

    /*Obtiene las subastas activas más recientes.*/
    public function obtenerNuevas(int $limite = 4): array
    {
        $lista = [];
        if (!$this->conexion || $this->conexion->connect_errno) {
            return $lista;
        }

        $limite = max(1, (int)$limite);

        $sql = "SELECT s.id_subasta, s.precio_inicial, s.fecha_inicial, s.fecha_final, s.estado,
                       TIMESTAMPDIFF(SECOND, NOW(), s.fecha_final) AS segundos_restantes,
                       p.id_producto, p.nombre AS titulo, p.descripcion,
                       COALESCE(c.nombre, 'General') AS categoria,
                       img.ruta AS imagen_ruta,
                       COALESCE(MAX(pu.monto), s.precio_inicial) AS precio_actual
                FROM sb_subasta s
                INNER JOIN sb_producto p ON s.id_producto = p.id_producto
                LEFT JOIN sb_categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN sb_imagen_producto img ON p.id_producto = img.id_producto AND img.principal = 1
                LEFT JOIN sb_puja pu ON s.id_subasta = pu.id_subasta
                WHERE s.fecha_final > NOW()
                  AND LOWER(s.estado) = 'activa'
                GROUP BY s.id_subasta, s.precio_inicial, s.fecha_inicial, s.fecha_final, s.estado,
                         p.id_producto, p.nombre, p.descripcion, c.nombre, img.ruta
                ORDER BY s.fecha_inicial DESC, s.id_subasta DESC
                LIMIT {$limite}";

        $res = $this->conexion->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $lista[] = $this->formatearSubasta($row, 'assets/img/news-6.jpg');
            }
        }

        return $lista;
    }

    private function formatearSubasta(array $row, string $imagenFallback): array
    {
        $imagen = $imagenFallback;
        if (!empty($row['imagen_ruta'])) {
            $ruta = $row['imagen_ruta'];
            $imagen = (strpos($ruta, 'uploads/') === 0 || strpos($ruta, 'assets/') === 0 || strpos($ruta, 'http') === 0)
                ? $ruta
                : 'uploads/productos/' . ltrim($ruta, '/');
        }

        $segundosRestantes = isset($row['segundos_restantes'])
            ? max(0, (int)$row['segundos_restantes'])
            : max(0, strtotime($row['fecha_final']) - time());

        return [
            'id'              => (int)$row['id_subasta'],
            'id_producto'     => (int)$row['id_producto'],
            'categoria'       => $row['categoria'],
            'titulo'          => $row['titulo'],
            'descripcion'     => $row['descripcion'] ?? '',
            'precio'          => number_format((float)$row['precio_actual'], 2, '.', ','),
            'tiempo'          => $this->formatearTiempoRestante($segundosRestantes),
            'fecha_final_iso' => date('c', time() + $segundosRestantes),
            'imagen'          => $imagen,
        ];
    }

    private function formatearTiempoRestante(int $segundos): string
    {
        if ($segundos <= 0) {
            return 'Finalizada';
        }

        $dias = intdiv($segundos, 86400);
        $horas = intdiv($segundos % 86400, 3600);
        $minutos = intdiv($segundos % 3600, 60);
        $segs = $segundos % 60;

        if ($dias > 0) {
            return sprintf('%d d %02d h', $dias, $horas);
        }

        return sprintf('%02d:%02d:%02d', $horas, $minutos, $segs);
    }

    public function insertarSubasta($precio_inicial, $incremento_minimo, $dias, $id_producto, $id_usuario, $tipo_solicitud = 'nueva', $nota_vendedor = null){
        try {
            $this->conexion->begin_transaction();
            $sqlVerficar="SELECT p.id_producto FROM sb_producto p
            WHERE p.id_producto = ? AND p.id_usuario = ? AND p.estado=1
            FOR UPDATE;";
            $stmt=$this->conexion->prepare($sqlVerficar);
            $stmt->bind_param("ii", $id_producto, $id_usuario);
            if (!$stmt->execute()) {
                throw new RuntimeException('Error al verificar el producto para insertar subasta: ' . $stmt->error);
            }
     

            $resultado = $stmt->get_result();
            $existe = $resultado && $resultado->num_rows > 0;
            $stmt->close();
            if(!$existe){
                $this->conexion->rollback();
                return ['estado' => false, 'mensaje' => 'El producto no existe o no pertenece al usuario'];
            }

            //Por si un usuario mañoso abre 2 pestañas ytrata de insertar 2 subastasas
            $sqlAbierta = "SELECT 1 FROM sb_subasta WHERE id_producto = ? AND estado IN ('pendiente', 'activa', 'finalizada') LIMIT 1;";
            $stmt = $this->conexion->prepare($sqlAbierta);
            $stmt->bind_param("i", $id_producto);
            $stmt->execute();
            $yaTiene = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if ($yaTiene) {
                $this->conexion->rollback();
                return ['estado' => false, 'mensaje' => 'Este producto ya tiene una subasta en revisión, activa o finalizada.'];
            }

            $sqlUltima = "SELECT estado FROM sb_subasta WHERE id_producto = ? ORDER BY id_subasta DESC LIMIT 1;";
            $stmt = $this->conexion->prepare($sqlUltima);
            $stmt->bind_param("i", $id_producto);
            $stmt->execute();
            $ultima = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $estadoAnterior = $ultima['estado'] ?? null;

            if ($estadoAnterior === null) {
                $tipo_solicitud = 'nueva';
            } elseif ($estadoAnterior === 'rechazada') {
                if ($tipo_solicitud !== 'apelacion') {
                    $tipo_solicitud = 'reenvio';
                }
            } else {
                $tipo_solicitud = 'reenvio';
            }

            $sql="INSERT INTO sb_subasta (precio_inicial, incremento_minimo, fecha_inicial, fecha_final, id_producto, tipo_solicitud, nota_vendedor)
            VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), ?, ?, ?);";
            $stmt=$this->conexion->prepare($sql);
            $stmt->bind_param("ddiiss", $precio_inicial, $incremento_minimo, $dias, $id_producto, $tipo_solicitud, $nota_vendedor);
            if (!$stmt->execute()) {
                throw new RuntimeException('Error al crear la subasta: ' . $stmt->error);
            }
            $stmt->close();
            $this->conexion->commit();
            return ['estado' => true, 'mensaje' => 'Subasta creada con éxito'];
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->conexion->rollback();
            return ['estado' => false, 'mensaje' => 'No se pudo crear la subasta'];
        }
    }

}
