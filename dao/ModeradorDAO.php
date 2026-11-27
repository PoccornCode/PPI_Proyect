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

    public function
    inactivarSubastasVencidas(): int
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
                SUM(CASE WHEN LOWER(s.estado) = 'activa' AND s.fecha_final > NOW() THEN 1 ELSE 0 END) AS activas,
                SUM(CASE WHEN LOWER(s.estado) = 'pendiente' THEN 1 ELSE 0 END) AS pendientes,
                MAX(CASE WHEN LOWER(s.estado) = 'activa' AND s.fecha_final > NOW() THEN s.fecha_inicial ELSE NULL END) AS ultima_activa,
                MAX(CASE WHEN LOWER(s.estado) = 'pendiente' THEN s.fecha_inicial ELSE NULL END) AS ultima_pendiente,
                SUM(CASE WHEN LOWER(s.estado) = 'activa' AND s.fecha_final > NOW()
                              AND s.fecha_inicial >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS activas_ultimos_7d,
                SUM(CASE WHEN LOWER(s.estado) = 'activa'
                              AND s.fecha_inicial >= DATE_SUB(NOW(), INTERVAL 14 DAY)
                              AND s.fecha_inicial < DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS activas_7d_anteriores,
                SUM(CASE WHEN LOWER(s.estado) = 'pendiente'
                              AND s.fecha_inicial >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS pendientes_ultimos_7d,
                SUM(CASE WHEN LOWER(s.estado) = 'pendiente'
                              AND s.fecha_inicial >= DATE_SUB(NOW(), INTERVAL 14 DAY)
                              AND s.fecha_inicial < DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS pendientes_7d_anteriores
            FROM sb_subasta s
            INNER JOIN (
                SELECT MAX(id_subasta) AS max_id
                FROM sb_subasta
                GROUP BY id_producto
            ) ult ON s.id_subasta = ult.max_id";

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

    private function obtenerSerieUltimos7Dias(string $estado, bool $soloVigentes): array
    {
        $fechas = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = date('Y-m-d', strtotime("-{$i} days"));
            $fechas[$dia] = 0;
        }

        $filtroVigencia = $soloVigentes ? " AND s.fecha_final > NOW()" : "";
        $sql = "SELECT DATE(s.fecha_inicial) AS dia, COUNT(*) AS total
                FROM sb_subasta s
                INNER JOIN (
                    SELECT MAX(id_subasta) AS max_id
                    FROM sb_subasta
                    GROUP BY id_producto
                ) ult ON s.id_subasta = ult.max_id
                WHERE LOWER(s.estado) = ?
                  {$filtroVigencia}
                  AND s.fecha_inicial >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(s.fecha_inicial)";

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

    public function obtenerPerfilModerador(int $idUsuario): ?array
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
                  AND u.id_rol IN (1, 3)
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

    public function listarSubastasPendientes(): array
    {
        $pendientes = [];
        if (!$this->conexion || $this->conexion->connect_errno) {
            return $pendientes;
        }

        $sql = "SELECT s.id_subasta, s.precio_inicial, s.incremento_minimo,
                       s.fecha_inicial, s.fecha_final, s.estado,
                       s.tipo_solicitud, s.motivo_rechazo, s.nota_vendedor,
                       p.id_producto, p.nombre AS titulo, p.descripcion, p.condicion,
                       c.nombre AS categoria, c.imagen AS categoria_imagen,
                       u.username, u.nombre AS vendedor_nombre, u.apellido AS vendedor_apellido
                FROM sb_subasta s
                INNER JOIN (
                    SELECT MAX(id_subasta) AS max_id
                    FROM sb_subasta
                    GROUP BY id_producto
                ) ult ON s.id_subasta = ult.max_id
                INNER JOIN sb_producto p ON s.id_producto = p.id_producto
                LEFT JOIN sb_categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN sb_usuario u ON p.id_usuario = u.id_usuario
                WHERE LOWER(s.estado) = 'pendiente'
                ORDER BY s.id_subasta ASC";

        $res = $this->conexion->query($sql);
        if (!$res) {
            return $pendientes;
        }

        $idsProductos = [];
        while ($row = $res->fetch_assoc()) {
            $idProd = (int)$row['id_producto'];
            $idsProductos[] = $idProd;
            $row['imagenes'] = [];
            $pendientes[] = $row;
        }

        if (!empty($idsProductos)) {
            $mapaImagenes = $this->obtenerImagenesPorProductos($idsProductos);
            foreach ($pendientes as &$item) {
                $idProd = (int)$item['id_producto'];
                $item['imagenes'] = $mapaImagenes[$idProd] ?? [];
            }
            unset($item);
        }

        return $pendientes;
    }

    private function obtenerImagenesPorProductos(array $idsProductos): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $idsProductos))));
        if (empty($ids)) {
            return [];
        }

        $listaIds = implode(',', $ids);
        $sql = "SELECT id_producto, ruta
                FROM sb_imagen_producto
                WHERE id_producto IN ({$listaIds})
                ORDER BY principal DESC, id_imagen_producto ASC";

        $mapa = [];
        $res = $this->conexion->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $idProd = (int)$row['id_producto'];
                if (!empty($row['ruta'])) {
                    $mapa[$idProd][] = $row['ruta'];
                }
            }
        }

        return $mapa;
    }

    public function moderarSubasta(int $idSubasta, string $decision, ?string $motivo = null): array
    {
        if (!$this->conexion || $this->conexion->connect_errno) {
            return ['estado' => false, 'mensaje' => 'Error de conexión con la base de datos.'];
        }

        $decision = strtolower(trim($decision));
        if ($idSubasta <= 0 || !in_array($decision, ['aprobar', 'rechazar'], true)) {
            return ['estado' => false, 'mensaje' => 'Parámetros de moderación no válidos.'];
        }

        try {
            $this->conexion->begin_transaction();

            $sqlSelect = "SELECT s.id_subasta, s.id_producto, s.estado, s.fecha_inicial, s.fecha_final, p.nombre AS titulo,
                                 (SELECT MAX(s2.id_subasta) FROM sb_subasta s2 WHERE s2.id_producto = s.id_producto) AS max_id_subasta
                          FROM sb_subasta s
                          INNER JOIN sb_producto p ON s.id_producto = p.id_producto
                          WHERE s.id_subasta = ?
                          FOR UPDATE";
            $stmt = $this->conexion->prepare($sqlSelect);
            $stmt->bind_param('i', $idSubasta);
            $stmt->execute();
            $subasta = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$subasta) {
                $this->conexion->rollback();
                return ['estado' => false, 'mensaje' => 'La subasta seleccionada no existe.'];
            }

            if ((int)($subasta['max_id_subasta'] ?? 0) > $idSubasta) {
                $this->conexion->rollback();
                return ['estado' => false, 'conflicto' => true, 'mensaje' => 'Esta subasta fue reemplazada por un reenvío más reciente y no puede modificarse.'];
            }

            if (strtolower((string)$subasta['estado']) !== 'pendiente') {
                $this->conexion->rollback();
                return ['estado' => false, 'mensaje' => 'Esta subasta ya fue moderada.'];
            }

            if ($decision === 'aprobar') {
                $inicioTs = strtotime((string)$subasta['fecha_inicial']) ?: time();
                $finTs = strtotime((string)$subasta['fecha_final']) ?: ($inicioTs + 86400);
                $duracionSeg = max(86400, $finTs - $inicioTs);

                $sqlUpdate = "UPDATE sb_subasta
                              SET estado = 'activa',
                                  fecha_inicial = NOW(),
                                  fecha_final = DATE_ADD(NOW(), INTERVAL ? SECOND)
                              WHERE id_subasta = ?";
                $stmtUp = $this->conexion->prepare($sqlUpdate);
                $stmtUp->bind_param('ii', $duracionSeg, $idSubasta);
                $stmtUp->execute();
                $stmtUp->close();
                $nuevoEstado = 'Activa';
            } else {
                $motivoLimpio = $motivo !== null ? trim($motivo) : null;
                if ($motivoLimpio === '') {
                    $motivoLimpio = null;
                }
                $sqlUpdate = "UPDATE sb_subasta SET estado = 'rechazada', motivo_rechazo = ? WHERE id_subasta = ?";
                $stmtUp = $this->conexion->prepare($sqlUpdate);
                $stmtUp->bind_param('si', $motivoLimpio, $idSubasta);
                $stmtUp->execute();
                $stmtUp->close();
                $nuevoEstado = 'Rechazada';
            }

            $this->conexion->commit();

            return [
                'estado'       => true,
                'id_subasta'   => $idSubasta,
                'titulo'       => $subasta['titulo'],
                'nuevo_estado' => $nuevoEstado,
                'mensaje'      => $decision === 'aprobar'
                    ? 'La subasta fue aprobada y publicada en el catálogo.'
                    : 'La subasta fue rechazada.',
            ];
        } catch (Throwable $e) {
            $this->conexion->rollback();
            error_log($e->getMessage());
            return ['estado' => false, 'mensaje' => 'No se pudo procesar la acción de moderación.'];
        }
    }

    public function listarTodasSubastas(): array
    {
        $subastas = [];
        if (!$this->conexion || $this->conexion->connect_errno) {
            return $subastas;
        }

        $sql = "SELECT s.id_subasta, s.precio_inicial, s.incremento_minimo,
                       s.fecha_inicial, s.fecha_final, s.estado,
                       s.tipo_solicitud, s.motivo_rechazo, s.nota_vendedor,
                       p.id_producto, p.nombre AS titulo, p.descripcion, p.condicion,
                       c.nombre AS categoria, c.imagen AS categoria_imagen,
                       u.username, u.nombre AS vendedor_nombre, u.apellido AS vendedor_apellido
                FROM sb_subasta s
                INNER JOIN (
                    SELECT MAX(id_subasta) AS max_id
                    FROM sb_subasta
                    GROUP BY id_producto
                ) ult ON s.id_subasta = ult.max_id
                INNER JOIN sb_producto p ON s.id_producto = p.id_producto
                LEFT JOIN sb_categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN sb_usuario u ON p.id_usuario = u.id_usuario
                ORDER BY s.id_subasta DESC";

        $res = $this->conexion->query($sql);
        if (!$res) {
            return $subastas;
        }

        $idsProductos = [];
        while ($row = $res->fetch_assoc()) {
            $idProd = (int)$row['id_producto'];
            $idsProductos[] = $idProd;
            $row['imagenes'] = [];
            $subastas[] = $row;
        }

        if (!empty($idsProductos)) {
            $mapaImagenes = $this->obtenerImagenesPorProductos($idsProductos);
            foreach ($subastas as &$item) {
                $idProd = (int)$item['id_producto'];
                $item['imagenes'] = $mapaImagenes[$idProd] ?? [];
            }
            unset($item);
        }

        return $subastas;
    }

    public function cambiarEstadoSubasta(int $idSubasta, string $nuevoEstado, ?string $estadoEsperado = null, ?string $motivo = null): array
    {
        if (!$this->conexion || $this->conexion->connect_errno) {
            return ['estado' => false, 'mensaje' => 'Error de conexión con la base de datos.'];
        }

        $nuevoEstado = strtolower(trim($nuevoEstado));
        $estadoEsperadoNorm = $estadoEsperado !== null ? strtolower(trim($estadoEsperado)) : '';
        $estadosPermitidos = ['pendiente', 'activa', 'rechazada', 'cancelada'];
        if ($idSubasta <= 0 || !in_array($nuevoEstado, $estadosPermitidos, true)) {
            return ['estado' => false, 'mensaje' => 'Estado o subasta no válidos.'];
        }

        $etiquetasEstado = [
            'pendiente'   => 'Pendiente',
            'activa'      => 'Activa',
            'finalizada'  => 'Finalizada',
            'sin_ofertas' => 'Sin ofertas',
            'rechazada'   => 'Rechazada',
            'cancelada'   => 'Cancelada',
        ];

        try {
            $this->conexion->begin_transaction();

            $sqlSelect = "SELECT s.id_subasta, s.id_producto, s.estado, s.fecha_inicial, s.fecha_final, p.nombre AS titulo,
                                 (SELECT MAX(s2.id_subasta) FROM sb_subasta s2 WHERE s2.id_producto = s.id_producto) AS max_id_subasta
                          FROM sb_subasta s
                          INNER JOIN sb_producto p ON s.id_producto = p.id_producto
                          WHERE s.id_subasta = ?
                          FOR UPDATE";
            $stmt = $this->conexion->prepare($sqlSelect);
            $stmt->bind_param('i', $idSubasta);
            $stmt->execute();
            $subasta = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$subasta) {
                $this->conexion->rollback();
                return ['estado' => false, 'mensaje' => 'La subasta seleccionada no existe.'];
            }

            if ((int)($subasta['max_id_subasta'] ?? 0) > $idSubasta) {
                $this->conexion->rollback();
                return ['estado' => false, 'conflicto' => true, 'mensaje' => 'Esta subasta fue reemplazada por un reenvío más reciente y no puede modificarse.'];
            }

            $estadoActual = strtolower((string)$subasta['estado']);

            if ($estadoActual === 'finalizada') {
                $this->conexion->rollback();
                return [
                    'estado'        => false,
                    'conflicto'     => true,
                    'estado_actual' => $estadoActual,
                    'mensaje'       => 'Esta subasta ya finalizó y no puede cambiar de estado.',
                ];
            }

            if ($estadoEsperadoNorm !== '' && $estadoActual !== $estadoEsperadoNorm) {
                $this->conexion->rollback();
                $nombreActual = $etiquetasEstado[$estadoActual] ?? ucfirst($estadoActual);
                return [
                    'estado'        => false,
                    'conflicto'     => true,
                    'estado_actual' => $estadoActual,
                    'mensaje'       => "Otro moderador ya cambió esta subasta a \"{$nombreActual}\". Se actualizó la vista.",
                ];
            }

            if ($estadoActual === $nuevoEstado) {
                $this->conexion->rollback();
                return [
                    'estado'        => false,
                    'conflicto'     => false,
                    'estado_actual' => $estadoActual,
                    'mensaje'       => 'La subasta ya se encuentra en ese estado.',
                ];
            }

            if ($nuevoEstado === 'activa') {
                $finTs = strtotime((string)$subasta['fecha_final']) ?: 0;
                if ($finTs <= time()) {
                    $inicioTs = strtotime((string)$subasta['fecha_inicial']) ?: time();
                    $duracionSeg = max(86400, ($finTs > $inicioTs) ? ($finTs - $inicioTs) : 86400);
                    $sqlUpdate = "UPDATE sb_subasta
                                  SET estado = 'activa',
                                      motivo_rechazo = NULL,
                                      fecha_inicial = NOW(),
                                      fecha_final = DATE_ADD(NOW(), INTERVAL ? SECOND)
                                  WHERE id_subasta = ?";
                    $stmtUp = $this->conexion->prepare($sqlUpdate);
                    $stmtUp->bind_param('ii', $duracionSeg, $idSubasta);
                } else {
                    $sqlUpdate = "UPDATE sb_subasta
                                  SET estado = 'activa',
                                      motivo_rechazo = NULL,
                                      fecha_inicial = NOW()
                                  WHERE id_subasta = ?";
                    $stmtUp = $this->conexion->prepare($sqlUpdate);
                    $stmtUp->bind_param('i', $idSubasta);
                }
            } elseif ($nuevoEstado === 'pendiente') {
                $finTs = strtotime((string)$subasta['fecha_final']) ?: 0;
                if ($finTs <= time()) {
                    $inicioTs = strtotime((string)$subasta['fecha_inicial']) ?: time();
                    $duracionSeg = max(86400, ($finTs > $inicioTs) ? ($finTs - $inicioTs) : 86400);
                    $sqlUpdate = "UPDATE sb_subasta
                                  SET estado = 'pendiente',
                                      fecha_inicial = NOW(),
                                      fecha_final = DATE_ADD(NOW(), INTERVAL ? SECOND)
                                  WHERE id_subasta = ?";
                    $stmtUp = $this->conexion->prepare($sqlUpdate);
                    $stmtUp->bind_param('ii', $duracionSeg, $idSubasta);
                } else {
                    $sqlUpdate = "UPDATE sb_subasta
                                  SET estado = 'pendiente',
                                      fecha_inicial = NOW()
                                  WHERE id_subasta = ?";
                    $stmtUp = $this->conexion->prepare($sqlUpdate);
                    $stmtUp->bind_param('i', $idSubasta);
                }
            } else {
                $motivoLimpio = $motivo !== null ? trim($motivo) : null;
                if ($motivoLimpio === '') {
                    $motivoLimpio = null;
                }
                $sqlUpdate = "UPDATE sb_subasta SET estado = ?, motivo_rechazo = ? WHERE id_subasta = ?";
                $stmtUp = $this->conexion->prepare($sqlUpdate);
                $stmtUp->bind_param('ssi', $nuevoEstado, $motivoLimpio, $idSubasta);
            }

            $stmtUp->execute();
            $stmtUp->close();
            $this->conexion->commit();

            return [
                'estado'       => true,
                'id_subasta'   => $idSubasta,
                'titulo'       => $subasta['titulo'],
                'nuevo_estado' => $nuevoEstado,
                'mensaje'      => 'El estado de la subasta se actualizó correctamente.',
            ];
        } catch (Throwable $e) {
            $this->conexion->rollback();
            error_log($e->getMessage());
            return ['estado' => false, 'mensaje' => 'No se pudo actualizar el estado de la subasta.'];
        }
    }
}
