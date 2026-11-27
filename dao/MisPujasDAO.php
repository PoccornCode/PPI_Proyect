<?php
require_once __DIR__ . '/../config/conexion.php';

class MisPujasDAO
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

    // una fila por cada subasta en la que el usuario pujo con mi puja mas alta y la mas alta de todos
    public function obtenerPorUsuario(int $id_usuario): array
    {
        $lista = [];

        $sql = "SELECT s.id_subasta, s.estado, s.fecha_final,
                       TIMESTAMPDIFF(SECOND, NOW(), s.fecha_final) AS segundos_restantes,
                       p.nombre AS titulo,
                       COALESCE(c.nombre, 'General') AS categoria,
                       img.ruta AS imagen_ruta,
                       mi.mi_maxima, mi.mis_pujas, mi.ultima,
                       top.maxima
                FROM (SELECT id_subasta, MAX(monto) AS mi_maxima, COUNT(*) AS mis_pujas, MAX(fecha) AS ultima
                      FROM sb_puja WHERE id_usuario = ? GROUP BY id_subasta) mi
                INNER JOIN sb_subasta s ON s.id_subasta = mi.id_subasta
                INNER JOIN sb_producto p ON p.id_producto = s.id_producto
                INNER JOIN (SELECT id_subasta, MAX(monto) AS maxima
                            FROM sb_puja GROUP BY id_subasta) top ON top.id_subasta = s.id_subasta
                LEFT JOIN sb_categoria c ON c.id_categoria = p.id_categoria
                LEFT JOIN sb_imagen_producto img ON img.id_imagen_producto = (
                    SELECT MIN(i.id_imagen_producto) FROM sb_imagen_producto i
                    WHERE i.id_producto = p.id_producto AND i.principal = 1)
                ORDER BY (LOWER(s.estado) = 'activa' AND s.fecha_final > NOW()) DESC, s.fecha_final ASC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($f = $res->fetch_assoc()) {
            $segundos = (int)$f['segundos_restantes'];
            $abierta = strtolower($f['estado']) === 'activa' && $segundos > 0;
            $voyGanando = (float)$f['mi_maxima'] >= (float)$f['maxima'];

            // resultado de mi puja segun si la subasta sigue abierta o ya cerro
            if ($abierta) {
                $resultado = $voyGanando ? 'ganando' : 'superada';
            } elseif (strtolower($f['estado']) === 'cancelada') {
                $resultado = 'cancelada';
            } else {
                $resultado = $voyGanando ? 'ganada' : 'perdida';
            }

            $lista[] = [
                'id'              => (int)$f['id_subasta'],
                'titulo'          => $f['titulo'],
                'categoria'       => $f['categoria'],
                'imagen'          => $this->rutaImagen($f['imagen_ruta']),
                'mi_puja'         => number_format((float)$f['mi_maxima'], 2, '.', ','),
                'puja_actual'     => number_format((float)$f['maxima'], 2, '.', ','),
                'mis_pujas'       => (int)$f['mis_pujas'],
                'ultima'          => $f['ultima'] ? date('d/m/Y H:i', strtotime($f['ultima'])) : '',
                'abierta'         => $abierta,
                'resultado'       => $resultado,
                'fecha_final_iso' => date('c', time() + max(0, $segundos)),
                'tiempo'          => $this->textoTiempo($segundos),
            ];
        }
        $stmt->close();

        return $lista;
    }

    // en cuantas subastas pujo y en cuantas va ganando trajeta del perfil
    public function resumen(int $id_usuario): array
    {
        $sql = "SELECT COUNT(*) AS total,
                       COALESCE(SUM(LOWER(s.estado) = 'activa' AND s.fecha_final > NOW() AND mi.maxima >= top.maxima), 0) AS ganando
                FROM (SELECT id_subasta, MAX(monto) AS maxima
                      FROM sb_puja WHERE id_usuario = ? GROUP BY id_subasta) mi
                INNER JOIN sb_subasta s ON s.id_subasta = mi.id_subasta
                INNER JOIN (SELECT id_subasta, MAX(monto) AS maxima
                            FROM sb_puja GROUP BY id_subasta) top ON top.id_subasta = mi.id_subasta";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $f = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return [
            'subastas' => (int)($f['total'] ?? 0),
            'ganando'  => (int)($f['ganando'] ?? 0),
        ];
    }

    private function rutaImagen($ruta): string
    {
        if (empty($ruta)) {
            return 'assets/img/news-1.jpg';
        }
        if (strpos($ruta, 'uploads/') === 0 || strpos($ruta, 'assets/') === 0 || strpos($ruta, 'http') === 0) {
            return $ruta;
        }
        return 'uploads/productos/' . ltrim($ruta, '/');
    }

    private function textoTiempo(int $segundos): string
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
}
