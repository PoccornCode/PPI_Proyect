<?php
require_once __DIR__ . '/../config/conexion.php';

class AdminDAO
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

    public function obtenerPerfilAdministrador(int $idUsuario): ?array
    {
        if ($idUsuario <= 0 || !$this->conexion || $this->conexion->connect_errno) {
            return null;
        }

        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.username, u.id_rol,
                       COALESCE(r.nombre, '') AS rol_nombre
                FROM sb_usuario u
                INNER JOIN sb_rol r ON u.id_rol = r.id_rol
                WHERE u.id_usuario = ?
                  AND u.estado = 1
                  AND u.id_rol = 1
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }

    public function obtenerResumenAlertasTopbar(): array
    {
        $resumen = [
            'subastas_pendientes'  => 0,
            'contactos_pendientes' => 0,
            'total'                => 0,
        ];

        if (!$this->conexion || $this->conexion->connect_errno) {
            return $resumen;
        }

        $sql = "SELECT
                    (SELECT COUNT(*) FROM sb_subasta WHERE LOWER(estado) = 'pendiente') AS subastas_pendientes,
                    (SELECT COUNT(*) FROM sb_contacto WHERE estado = 0) AS contactos_pendientes";
        $res = $this->conexion->query($sql);
        if ($res && ($row = $res->fetch_assoc())) {
            $subastas = (int)($row['subastas_pendientes'] ?? 0);
            $contactos = (int)($row['contactos_pendientes'] ?? 0);
            $resumen['subastas_pendientes'] = $subastas;
            $resumen['contactos_pendientes'] = $contactos;
            $resumen['total'] = $subastas + $contactos;
        }

        return $resumen;
    }

    public function obtenerResumenKpis(string $rango = '30d'): array
    {
        $diasMapa = ['7d' => 7, '30d' => 30, '90d' => 90];
        $dias = $diasMapa[$rango] ?? 30;
        $dobleDias = $dias * 2;

        $resumen = [
            'usuarios'                => 0,
            'pujas'                   => 0,
            'activas'                 => 0,
            'pendientes'              => 0,
            'ultimo_usuario'          => null,
            'ultima_puja'             => null,
            'ultima_activa'           => null,
            'ultima_pendiente'        => null,
            'usuarios_actual'         => 0,
            'usuarios_anterior'       => 0,
            'pujas_actual'            => 0,
            'pujas_anterior'          => 0,
            'activas_actual'          => 0,
            'activas_anterior'        => 0,
            'pendientes_actual'       => 0,
            'pendientes_anterior'     => 0,
            'serie_usuarios'          => array_fill(0, 7, 0),
            'serie_pujas'             => array_fill(0, 7, 0),
            'serie_activas'           => array_fill(0, 7, 0),
            'serie_pendientes'        => array_fill(0, 7, 0),
        ];

        if (!$this->conexion || $this->conexion->connect_errno) {
            return $resumen;
        }

        $sqlUsuarios = "SELECT
                COUNT(*) AS total,
                MAX(fecha_registro) AS ultimo,
                SUM(CASE WHEN fecha_registro >= DATE_SUB(NOW(), INTERVAL {$dias} DAY) THEN 1 ELSE 0 END) AS actual,
                SUM(CASE WHEN fecha_registro >= DATE_SUB(NOW(), INTERVAL {$dobleDias} DAY)
                          AND fecha_registro < DATE_SUB(NOW(), INTERVAL {$dias} DAY) THEN 1 ELSE 0 END) AS anterior
            FROM sb_usuario";
        if ($res = $this->conexion->query($sqlUsuarios)) {
            if ($row = $res->fetch_assoc()) {
                $resumen['usuarios'] = (int)($row['total'] ?? 0);
                $resumen['ultimo_usuario'] = $row['ultimo'] ?: null;
                $resumen['usuarios_actual'] = (int)($row['actual'] ?? 0);
                $resumen['usuarios_anterior'] = (int)($row['anterior'] ?? 0);
            }
        }

        $sqlPujas = "SELECT
                COUNT(*) AS total,
                MAX(fecha) AS ultimo,
                SUM(CASE WHEN fecha >= DATE_SUB(NOW(), INTERVAL {$dias} DAY) THEN 1 ELSE 0 END) AS actual,
                SUM(CASE WHEN fecha >= DATE_SUB(NOW(), INTERVAL {$dobleDias} DAY)
                          AND fecha < DATE_SUB(NOW(), INTERVAL {$dias} DAY) THEN 1 ELSE 0 END) AS anterior
            FROM sb_puja";
        if ($res = $this->conexion->query($sqlPujas)) {
            if ($row = $res->fetch_assoc()) {
                $resumen['pujas'] = (int)($row['total'] ?? 0);
                $resumen['ultima_puja'] = $row['ultimo'] ?: null;
                $resumen['pujas_actual'] = (int)($row['actual'] ?? 0);
                $resumen['pujas_anterior'] = (int)($row['anterior'] ?? 0);
            }
        }

        $sqlSubastas = "SELECT
                SUM(CASE WHEN LOWER(estado) = 'activa' AND fecha_final > NOW() THEN 1 ELSE 0 END) AS activas,
                SUM(CASE WHEN LOWER(estado) = 'pendiente' THEN 1 ELSE 0 END) AS pendientes,
                MAX(CASE WHEN LOWER(estado) = 'activa' AND fecha_final > NOW() THEN fecha_inicial ELSE NULL END) AS ultima_activa,
                MAX(CASE WHEN LOWER(estado) = 'pendiente' THEN fecha_inicial ELSE NULL END) AS ultima_pendiente,
                SUM(CASE WHEN LOWER(estado) = 'activa' AND fecha_final > NOW()
                              AND fecha_inicial >= DATE_SUB(NOW(), INTERVAL {$dias} DAY) THEN 1 ELSE 0 END) AS activas_actual,
                SUM(CASE WHEN LOWER(estado) = 'activa'
                              AND fecha_inicial >= DATE_SUB(NOW(), INTERVAL {$dobleDias} DAY)
                              AND fecha_inicial < DATE_SUB(NOW(), INTERVAL {$dias} DAY) THEN 1 ELSE 0 END) AS activas_anterior,
                SUM(CASE WHEN LOWER(estado) = 'pendiente'
                              AND fecha_inicial >= DATE_SUB(NOW(), INTERVAL {$dias} DAY) THEN 1 ELSE 0 END) AS pendientes_actual,
                SUM(CASE WHEN LOWER(estado) = 'pendiente'
                              AND fecha_inicial >= DATE_SUB(NOW(), INTERVAL {$dobleDias} DAY)
                              AND fecha_inicial < DATE_SUB(NOW(), INTERVAL {$dias} DAY) THEN 1 ELSE 0 END) AS pendientes_anterior
            FROM sb_subasta";
        if ($res = $this->conexion->query($sqlSubastas)) {
            if ($row = $res->fetch_assoc()) {
                $resumen['activas'] = (int)($row['activas'] ?? 0);
                $resumen['pendientes'] = (int)($row['pendientes'] ?? 0);
                $resumen['ultima_activa'] = $row['ultima_activa'] ?: null;
                $resumen['ultima_pendiente'] = $row['ultima_pendiente'] ?: null;
                $resumen['activas_actual'] = (int)($row['activas_actual'] ?? 0);
                $resumen['activas_anterior'] = (int)($row['activas_anterior'] ?? 0);
                $resumen['pendientes_actual'] = (int)($row['pendientes_actual'] ?? 0);
                $resumen['pendientes_anterior'] = (int)($row['pendientes_anterior'] ?? 0);
            }
        }

        $resumen['serie_usuarios']   = $this->obtenerSerieEn7Tramos('sb_usuario', 'fecha_registro', $dias);
        $resumen['serie_pujas']      = $this->obtenerSerieEn7Tramos('sb_puja', 'fecha', $dias);
        $resumen['serie_activas']    = $this->obtenerSerieEn7Tramos('sb_subasta', 'fecha_inicial', $dias, "LOWER(estado) = 'activa' AND fecha_final > NOW()");
        $resumen['serie_pendientes'] = $this->obtenerSerieEn7Tramos('sb_subasta', 'fecha_inicial', $dias, "LOWER(estado) = 'pendiente'");

        return $resumen;
    }

    // Divide el rango de días en 7 tramos iguales para dibujar las barras del sparkline
    private function obtenerSerieEn7Tramos(string $tabla, string $columnaFecha, int $dias, string $filtroExtra = ''): array
    {
        $serie = array_fill(0, 7, 0);
        $ahora = time();
        $inicioTs = $ahora - ($dias * 86400);
        $duracionTramo = max(1, ($dias * 86400) / 7);
        $inicioSql = date('Y-m-d H:i:s', $inicioTs);

        $whereExtra = $filtroExtra !== '' ? " AND {$filtroExtra}" : '';
        $sql = "SELECT {$columnaFecha} AS fecha_evento
                FROM {$tabla}
                WHERE {$columnaFecha} >= '{$inicioSql}'{$whereExtra}";

        $res = $this->conexion->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $ts = strtotime((string)$row['fecha_evento']);
                if ($ts === false || $ts < $inicioTs) {
                    continue;
                }
                $indice = (int)floor(($ts - $inicioTs) / $duracionTramo);
                if ($indice >= 7) {
                    $indice = 6;
                } elseif ($indice < 0) {
                    $indice = 0;
                }
                $serie[$indice]++;
            }
        }

        return $serie;
    }
}
