<?php
require_once __DIR__ . '/../config/conexion.php';

class ModeradorDAO
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

    // Cierra subastas activas vencidas (finalizada si tiene pujas, sin_ofertas si no)
    public function inactivarSubastasVencidas(): int
    {
        if (!$this->conexion || $this->conexion->connect_errno) {
            return 0;
        }

        $sql = "UPDATE sb_subasta s
                SET s.estado = CASE
                    WHEN EXISTS (
                        SELECT 1 FROM sb_puja p WHERE p.id_subasta = s.id_subasta
                    ) THEN 'finalizada'
                    ELSE 'sin_ofertas'
                END
                WHERE LOWER(s.estado) = 'activa'
                  AND s.fecha_final <= NOW()";

        if ($this->conexion->query($sql)) {
            return (int)$this->conexion->affected_rows;
        }

        return 0;
    }

    // Totales, última fecha y comparativa semanal para los KPIs del moderador
    public function obtenerResumenKpis(): array
    {
        $resumen = [
            'activas'                  => 0,
            'pendientes'               => 0,
            'ultima_activa'            => null,
            'ultima_pendiente'         => null,
            'activas_ultimos_7d'       => 0,
            'activas_7d_anteriores'    => 0,
            'pendientes_ultimos_7d'    => 0,
            'pendientes_7d_anteriores' => 0,
            'serie_activas'            => array_fill(0, 7, 0),
            'serie_pendientes'         => array_fill(0, 7, 0),
        ];

        if (!$this->conexion || $this->conexion->connect_errno) {
            return $resumen;
        }

        $sqlTotales = "SELECT
                SUM(CASE WHEN LOWER(estado) = 'activa' AND fecha_final > NOW() THEN 1 ELSE 0 END) AS activas,
                SUM(CASE WHEN LOWER(estado) = 'pendiente' THEN 1 ELSE 0 END) AS pendientes,
                MAX(CASE WHEN LOWER(estado) = 'activa' AND fecha_final > NOW() THEN fecha_inicial ELSE NULL END) AS ultima_activa,
                MAX(CASE WHEN LOWER(estado) = 'pendiente' THEN fecha_inicial ELSE NULL END) AS ultima_pendiente,
                SUM(CASE WHEN LOWER(estado) = 'activa' AND fecha_final > NOW()
                              AND fecha_inicial >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS activas_ultimos_7d,
                SUM(CASE WHEN LOWER(estado) = 'activa'
                              AND fecha_inicial >= DATE_SUB(NOW(), INTERVAL 14 DAY)
                              AND fecha_inicial < DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS activas_7d_anteriores,
                SUM(CASE WHEN LOWER(estado) = 'pendiente'
                              AND fecha_inicial >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS pendientes_ultimos_7d,
                SUM(CASE WHEN LOWER(estado) = 'pendiente'
                              AND fecha_inicial >= DATE_SUB(NOW(), INTERVAL 14 DAY)
                              AND fecha_inicial < DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS pendientes_7d_anteriores
            FROM sb_subasta";

        $res = $this->conexion->query($sqlTotales);
        if ($res && ($row = $res->fetch_assoc())) {
            $resumen['activas']                  = (int)($row['activas'] ?? 0);
            $resumen['pendientes']               = (int)($row['pendientes'] ?? 0);
            $resumen['ultima_activa']            = $row['ultima_activa'] ?: null;
            $resumen['ultima_pendiente']         = $row['ultima_pendiente'] ?: null;
            $resumen['activas_ultimos_7d']       = (int)($row['activas_ultimos_7d'] ?? 0);
            $resumen['activas_7d_anteriores']    = (int)($row['activas_7d_anteriores'] ?? 0);
            $resumen['pendientes_ultimos_7d']    = (int)($row['pendientes_ultimos_7d'] ?? 0);
            $resumen['pendientes_7d_anteriores'] = (int)($row['pendientes_7d_anteriores'] ?? 0);
        }

        $resumen['serie_activas']    = $this->obtenerSerieUltimos7Dias('activa', true);
        $resumen['serie_pendientes'] = $this->obtenerSerieUltimos7Dias('pendiente', false);

        return $resumen;
    }

    // Cantidad de subastas por día en los últimos 7 días para la minigráfica
    private function obtenerSerieUltimos7Dias(string $estado, bool $soloVigentes): array
    {
        $fechas = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = date('Y-m-d', strtotime("-{$i} days"));
            $fechas[$dia] = 0;
        }

        $filtroVigencia = $soloVigentes ? " AND fecha_final > NOW()" : "";
        $sql = "SELECT DATE(fecha_inicial) AS dia, COUNT(*) AS total
                FROM sb_subasta
                WHERE LOWER(estado) = ?
                  {$filtroVigencia}
                  AND fecha_inicial >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(fecha_inicial)";

        $stmt = $this->conexion->prepare($sql);
        if (!$stmt) {
            return array_values($fechas);
        }

        $estadoLower = strtolower($estado);
        $stmt->bind_param('s', $estadoLower);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $dia = $row['dia'];
                if (isset($fechas[$dia])) {
                    $fechas[$dia] = (int)$row['total'];
                }
            }
        }

        $stmt->close();

        return array_values($fechas);
    }
}
