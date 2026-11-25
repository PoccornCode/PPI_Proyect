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

    public function obtenerActividadPlataforma(string $rango = '7d'): array
    {
        $rangoValido = in_array($rango, ['7d', '30d', '90d'], true) ? $rango : '7d';
        $diasMapa = ['7d' => 7, '30d' => 30, '90d' => 90];
        $dias = $diasMapa[$rangoValido];

        $diasCortos = ['Dom.', 'Lun.', 'Mar.', 'Mié.', 'Jue.', 'Vie.', 'Sáb.'];
        $mesesCortos = [1 => 'ene.', 2 => 'feb.', 3 => 'mar.', 4 => 'abr.', 5 => 'may.', 6 => 'jun.', 7 => 'jul.', 8 => 'ago.', 9 => 'sep.', 10 => 'oct.', 11 => 'nov.', 12 => 'dic.'];

        $labels = [];
        $values = array_fill(0, 7, 0);

        if ($rangoValido === '7d') {
            $inicioTs = strtotime('today -6 days');
            for ($i = 0; $i < 7; $i++) {
                $ts = strtotime("+{$i} days", $inicioTs);
                $labels[] = $diasCortos[(int)date('w', $ts)];
            }
            $duracionTramo = 86400;
        } else {
            $ahora = time();
            $inicioTs = strtotime('today -' . ($dias - 1) . ' days');
            $duracionTramo = max(1, ($ahora - $inicioTs) / 7);
            for ($i = 0; $i < 7; $i++) {
                $finTramoTs = (int)round($inicioTs + (($i + 1) * $duracionTramo));
                $labels[] = (int)date('j', $finTramoTs) . ' ' . ($mesesCortos[(int)date('n', $finTramoTs)] ?? '');
            }
        }

        if (!$this->conexion || $this->conexion->connect_errno) {
            return ['rango' => $rangoValido, 'labels' => $labels, 'values' => $values];
        }

        $inicioSql = date('Y-m-d 00:00:00', $inicioTs);
        $sql = "SELECT fecha_registro AS fecha_evento FROM sb_usuario WHERE fecha_registro >= '{$inicioSql}'
                UNION ALL
                SELECT fecha_inicial AS fecha_evento FROM sb_subasta WHERE fecha_inicial >= '{$inicioSql}'
                UNION ALL
                SELECT fecha AS fecha_evento FROM sb_puja WHERE fecha >= '{$inicioSql}'";

        if ($res = $this->conexion->query($sql)) {
            while ($row = $res->fetch_assoc()) {
                $ts = strtotime((string)$row['fecha_evento']);
                if ($ts === false || $ts < $inicioTs) {
                    continue;
                }
                $idx = (int)floor(($ts - $inicioTs) / $duracionTramo);
                if ($idx >= 7) {
                    $idx = 6;
                } elseif ($idx < 0) {
                    $idx = 0;
                }
                $values[$idx]++;
            }
        }

        return [
            'rango'  => $rangoValido,
            'labels' => $labels,
            'values' => $values,
        ];
    }

    public function obtenerDistribucionSubastas(): array
    {
        $conteos = [
            'activas'     => 0,
            'pendientes'  => 0,
            'finalizadas' => 0,
            'canceladas'  => 0,
        ];

        if ($this->conexion && !$this->conexion->connect_errno) {
            $sql = "SELECT
                        SUM(CASE WHEN LOWER(estado) = 'activa' AND fecha_final > NOW() THEN 1 ELSE 0 END) AS activas,
                        SUM(CASE WHEN LOWER(estado) = 'pendiente' THEN 1 ELSE 0 END) AS pendientes,
                        SUM(CASE WHEN LOWER(estado) IN ('finalizada', 'sin_ofertas') THEN 1 ELSE 0 END) AS finalizadas,
                        SUM(CASE WHEN LOWER(estado) IN ('cancelada', 'rechazada') THEN 1 ELSE 0 END) AS canceladas
                    FROM sb_subasta";
            if ($res = $this->conexion->query($sql)) {
                if ($row = $res->fetch_assoc()) {
                    $conteos['activas']     = (int)($row['activas'] ?? 0);
                    $conteos['pendientes']  = (int)($row['pendientes'] ?? 0);
                    $conteos['finalizadas'] = (int)($row['finalizadas'] ?? 0);
                    $conteos['canceladas']  = (int)($row['canceladas'] ?? 0);
                }
            }
        }

        $total = array_sum($conteos);
        $definicion = [
            ['key' => 'activas',     'label' => 'Activas',     'color' => '#75865d'],
            ['key' => 'pendientes',  'label' => 'Pendientes',  'color' => '#e29b8e'],
            ['key' => 'finalizadas', 'label' => 'Finalizadas', 'color' => '#4c91a3'],
            ['key' => 'canceladas',  'label' => 'Canceladas',  'color' => '#c8c45e'],
        ];

        $items = [];
        foreach ($definicion as $def) {
            $count = $conteos[$def['key']];
            $pct = $total > 0 ? (int)round(($count / $total) * 100) : 0;
            $items[] = [
                'key'        => $def['key'],
                'label'      => $def['label'],
                'count'      => $count,
                'porcentaje' => $pct,
                'color'      => $def['color'],
            ];
        }

        return [
            'total' => $total,
            'items' => $items,
        ];
    }

    public function obtenerUsuariosRecientes(int $limite = 3): array
    {
        $usuarios = [];
        if (!$this->conexion || $this->conexion->connect_errno) {
            return $usuarios;
        }

        $limite = max(1, min(10, $limite));
        $sql = "SELECT u.id_usuario,
                       u.nombre,
                       u.apellido,
                       u.username,
                       u.fecha_registro,
                       u.id_rol,
                       COALESCE(r.nombre, 'Usuario') AS rol_nombre,
                       (SELECT cu.correo
                        FROM sb_correo_usuario cu
                        WHERE cu.id_usuario = u.id_usuario
                        ORDER BY cu.correo ASC
                        LIMIT 1) AS correo
                FROM sb_usuario u
                LEFT JOIN sb_rol r ON u.id_rol = r.id_rol
                ORDER BY u.fecha_registro DESC, u.id_usuario DESC
                LIMIT {$limite}";

        $res = $this->conexion->query($sql);
        if (!$res) {
            return $usuarios;
        }

        while ($row = $res->fetch_assoc()) {
            $rolMapeado = $this->mapearRolUsuario((int)($row['id_rol'] ?? 0), (string)($row['rol_nombre'] ?? 'Usuario'));

            $correo = trim((string)($row['correo'] ?? ''));
            $username = trim((string)($row['username'] ?? ''));
            $etiqueta = $correo !== '' ? $correo : ($username !== '' ? '@' . ltrim($username, '@') : 'Usuario #' . (int)$row['id_usuario']);

            $usuarios[] = [
                'id_usuario'      => (int)$row['id_usuario'],
                'etiqueta'        => $etiqueta,
                'correo'          => $correo,
                'username'        => $username,
                'nombre_completo' => trim(($row['nombre'] ?? '') . ' ' . ($row['apellido'] ?? '')),
                'rol'             => $rolMapeado['etiqueta'],
                'rol_class'       => $rolMapeado['clase'],
                'fecha_registro'  => (string)($row['fecha_registro'] ?? ''),
            ];
        }

        return $usuarios;
    }

    private function mapearRolUsuario(int $idRol, string $rolNombre): array
    {
        $rolLower = mb_strtolower(trim($rolNombre), 'UTF-8');
        if ($rolLower === 'administrador' || $idRol === 1) {
            return ['etiqueta' => 'Administrador', 'clase' => 'administrador'];
        }
        if ($rolLower === 'moderador' || $idRol === 3) {
            return ['etiqueta' => 'Moderador', 'clase' => 'moderador'];
        }
        if ($rolLower === 'vendedor') {
            return ['etiqueta' => 'Subastador', 'clase' => 'vendedor'];
        }
        if ($rolLower === 'postor') {
            return ['etiqueta' => 'Pujador', 'clase' => 'postor'];
        }

        return ['etiqueta' => 'Pujador / Subastador', 'clase' => 'usuario'];
    }

    public function obtenerRolesFiltro(): array
    {
        $roles = [];
        if (!$this->conexion || $this->conexion->connect_errno) {
            return $roles;
        }

        $sql = "SELECT id_rol, nombre FROM sb_rol ORDER BY id_rol ASC";
        $res = $this->conexion->query($sql);
        if (!$res) {
            return $roles;
        }

        while ($row = $res->fetch_assoc()) {
            $idRol = (int)$row['id_rol'];
            $nombre = trim((string)($row['nombre'] ?? ''));
            $mapeado = $this->mapearRolUsuario($idRol, $nombre);
            $roles[] = [
                'id_rol'   => $idRol,
                'nombre'   => $nombre,
                'etiqueta' => $mapeado['etiqueta'],
                'clase'    => $mapeado['clase'],
            ];
        }

        return $roles;
    }

    public function listarUsuariosAdmin(string $busqueda = '', int $idRol = 0, int $pagina = 1, int $porPagina = 10, string $estadoFiltro = 'todos'): array
    {
        $pagina = max(1, $pagina);
        $porPagina = max(1, min(50, $porPagina));

        $resultado = [
            'usuarios'      => [],
            'total'         => 0,
            'pagina'        => $pagina,
            'por_pagina'    => $porPagina,
            'total_paginas' => 1,
            'desde'         => 0,
            'hasta'         => 0,
        ];

        if (!$this->conexion || $this->conexion->connect_errno) {
            return $resultado;
        }

        $where = [];
        $types = '';
        $params = [];

        if ($idRol > 0) {
            $where[] = 'u.id_rol = ?';
            $types .= 'i';
            $params[] = $idRol;
        }

        $estadoNorm = strtolower(trim($estadoFiltro));
        if ($estadoNorm === 'activo' || $estadoNorm === '1') {
            $where[] = 'u.estado = 1';
        } elseif ($estadoNorm === 'suspendido' || $estadoNorm === '0') {
            $where[] = 'u.estado = 0';
        }

        $busquedaLimpia = trim($busqueda);
        if ($busquedaLimpia !== '') {
            $like = '%' . $busquedaLimpia . '%';
            $where[] = "(u.nombre LIKE ?
                         OR u.apellido LIKE ?
                         OR CONCAT(u.nombre, ' ', u.apellido) LIKE ?
                         OR u.username LIKE ?
                         OR CAST(u.id_usuario AS CHAR) LIKE ?
                         OR EXISTS (
                             SELECT 1
                             FROM sb_correo_usuario cu2
                             WHERE cu2.id_usuario = u.id_usuario
                               AND cu2.correo LIKE ?
                         ))";
            $types .= 'ssssss';
            array_push($params, $like, $like, $like, $like, $like, $like);
        }

        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $sqlCount = "SELECT COUNT(*) AS total FROM sb_usuario u {$whereSql}";
        $stmtCount = $this->conexion->prepare($sqlCount);
        if (!$stmtCount) {
            return $resultado;
        }
        if ($types !== '') {
            $stmtCount->bind_param($types, ...$params);
        }
        $stmtCount->execute();
        $rowCount = $stmtCount->get_result()->fetch_assoc();
        $stmtCount->close();

        $total = (int)($rowCount['total'] ?? 0);
        $totalPaginas = max(1, (int)ceil($total / $porPagina));
        if ($pagina > $totalPaginas) {
            $pagina = $totalPaginas;
        }

        $resultado['total'] = $total;
        $resultado['pagina'] = $pagina;
        $resultado['total_paginas'] = $totalPaginas;

        if ($total === 0) {
            return $resultado;
        }

        $offset = ($pagina - 1) * $porPagina;
        $sqlData = "SELECT u.id_usuario,
                           u.nombre,
                           u.apellido,
                           u.username,
                           u.estado,
                           u.fecha_registro,
                           u.id_rol,
                           COALESCE(r.nombre, 'Usuario') AS rol_nombre,
                           (SELECT cu.correo
                            FROM sb_correo_usuario cu
                            WHERE cu.id_usuario = u.id_usuario
                            ORDER BY cu.correo ASC
                            LIMIT 1) AS correo
                    FROM sb_usuario u
                    LEFT JOIN sb_rol r ON u.id_rol = r.id_rol
                    {$whereSql}
                    ORDER BY u.fecha_registro DESC, u.id_usuario DESC
                    LIMIT ? OFFSET ?";

        $stmtData = $this->conexion->prepare($sqlData);
        if (!$stmtData) {
            return $resultado;
        }

        $typesData = $types . 'ii';
        $paramsData = array_merge($params, [$porPagina, $offset]);
        $stmtData->bind_param($typesData, ...$paramsData);
        $stmtData->execute();
        $res = $stmtData->get_result();

        $usuarios = [];
        while ($row = $res->fetch_assoc()) {
            $rolMapeado = $this->mapearRolUsuario((int)($row['id_rol'] ?? 0), (string)($row['rol_nombre'] ?? 'Usuario'));
            $correo = trim((string)($row['correo'] ?? ''));
            $username = trim((string)($row['username'] ?? ''));
            $nombreCompleto = trim(($row['nombre'] ?? '') . ' ' . ($row['apellido'] ?? ''));
            $etiqueta = $correo !== '' ? $correo : ($username !== '' ? '@' . ltrim($username, '@') : 'Usuario #' . (int)$row['id_usuario']);
            $activo = (int)($row['estado'] ?? 0) === 1;
            $fechaRaw = (string)($row['fecha_registro'] ?? '');
            $ts = $fechaRaw !== '' ? strtotime($fechaRaw) : false;
            $registroFmt = $ts !== false ? date('d/m/y', $ts) : '—';

            $usuarios[] = [
                'id_usuario'      => (int)$row['id_usuario'],
                'etiqueta'        => $etiqueta,
                'correo'          => $correo,
                'username'        => $username,
                'nombre_completo' => $nombreCompleto,
                'id_rol'          => (int)$row['id_rol'],
                'rol'             => $rolMapeado['etiqueta'],
                'rol_class'       => $rolMapeado['clase'],
                'activo'          => $activo,
                'estado'          => $activo ? 'Activo' : 'Suspendido',
                'registro'        => $registroFmt,
                'fecha_registro'  => $fechaRaw,
            ];
        }
        $stmtData->close();

        $resultado['usuarios'] = $usuarios;
        $resultado['desde'] = $offset + 1;
        $resultado['hasta'] = $offset + count($usuarios);

        return $resultado;
    }

    public function obtenerDetalleUsuarioAdmin(int $idUsuario, int $idAdminActual = 0): ?array
    {
        if ($idUsuario <= 0 || !$this->conexion || $this->conexion->connect_errno) {
            return null;
        }

        $sql = "SELECT u.id_usuario,
                       u.nombre,
                       u.apellido,
                       u.username,
                       u.ubicacion,
                       u.estado,
                       u.fecha_registro,
                       u.id_rol,
                       COALESCE(r.nombre, 'Usuario') AS rol_nombre
                FROM sb_usuario u
                LEFT JOIN sb_rol r ON u.id_rol = r.id_rol
                WHERE u.id_usuario = ?
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }

        $correos = [];
        $stmtCorreo = $this->conexion->prepare("SELECT correo FROM sb_correo_usuario WHERE id_usuario = ? ORDER BY correo ASC");
        if ($stmtCorreo) {
            $stmtCorreo->bind_param('i', $idUsuario);
            $stmtCorreo->execute();
            $resCorreo = $stmtCorreo->get_result();
            while ($c = $resCorreo->fetch_assoc()) {
                $val = trim((string)($c['correo'] ?? ''));
                if ($val !== '') {
                    $correos[] = $val;
                }
            }
            $stmtCorreo->close();
        }

        $telefonos = [];
        $stmtTel = $this->conexion->prepare("SELECT telefono FROM sb_telefono_usuario WHERE id_usuario = ? ORDER BY telefono ASC");
        if ($stmtTel) {
            $stmtTel->bind_param('i', $idUsuario);
            $stmtTel->execute();
            $resTel = $stmtTel->get_result();
            while ($t = $resTel->fetch_assoc()) {
                $val = trim((string)($t['telefono'] ?? ''));
                if ($val !== '') {
                    $telefonos[] = $val;
                }
            }
            $stmtTel->close();
        }

        $actividad = [
            'productos'            => 0,
            'subastas_total'       => 0,
            'subastas_activas'     => 0,
            'subastas_pendientes'  => 0,
            'subastas_finalizadas' => 0,
            'subastas_sin_ofertas' => 0,
            'subastas_canceladas'  => 0,
            'subastas_rechazadas'  => 0,
            'pujas_total'          => 0,
            'ultima_puja'          => null,
        ];

        $sqlSub = "SELECT COUNT(DISTINCT p.id_producto) AS productos,
                          COUNT(DISTINCT s.id_subasta) AS subastas_total,
                          COUNT(DISTINCT CASE WHEN LOWER(s.estado) = 'activa' AND s.fecha_final > NOW() THEN s.id_subasta END) AS subastas_activas,
                          COUNT(DISTINCT CASE WHEN LOWER(s.estado) = 'pendiente' THEN s.id_subasta END) AS subastas_pendientes,
                          COUNT(DISTINCT CASE WHEN LOWER(s.estado) = 'finalizada' THEN s.id_subasta END) AS subastas_finalizadas,
                          COUNT(DISTINCT CASE WHEN LOWER(s.estado) = 'sin_ofertas' THEN s.id_subasta END) AS subastas_sin_ofertas,
                          COUNT(DISTINCT CASE WHEN LOWER(s.estado) = 'cancelada' THEN s.id_subasta END) AS subastas_canceladas,
                          COUNT(DISTINCT CASE WHEN LOWER(s.estado) = 'rechazada' THEN s.id_subasta END) AS subastas_rechazadas
                   FROM sb_producto p
                   LEFT JOIN sb_subasta s ON s.id_producto = p.id_producto
                   WHERE p.id_usuario = ?";
        $stmtSub = $this->conexion->prepare($sqlSub);
        if ($stmtSub) {
            $stmtSub->bind_param('i', $idUsuario);
            $stmtSub->execute();
            $resSub = $stmtSub->get_result()->fetch_assoc();
            $stmtSub->close();
            if ($resSub) {
                $actividad['productos']            = (int)($resSub['productos'] ?? 0);
                $actividad['subastas_total']       = (int)($resSub['subastas_total'] ?? 0);
                $actividad['subastas_activas']     = (int)($resSub['subastas_activas'] ?? 0);
                $actividad['subastas_pendientes']  = (int)($resSub['subastas_pendientes'] ?? 0);
                $actividad['subastas_finalizadas'] = (int)($resSub['subastas_finalizadas'] ?? 0);
                $actividad['subastas_sin_ofertas'] = (int)($resSub['subastas_sin_ofertas'] ?? 0);
                $actividad['subastas_canceladas']  = (int)($resSub['subastas_canceladas'] ?? 0);
                $actividad['subastas_rechazadas']  = (int)($resSub['subastas_rechazadas'] ?? 0);
            }
        }

        $sqlPujas = "SELECT COUNT(*) AS pujas_total, MAX(fecha) AS ultima_puja
                     FROM sb_puja
                     WHERE id_usuario = ?";
        $stmtPujas = $this->conexion->prepare($sqlPujas);
        if ($stmtPujas) {
            $stmtPujas->bind_param('i', $idUsuario);
            $stmtPujas->execute();
            $resPujas = $stmtPujas->get_result()->fetch_assoc();
            $stmtPujas->close();
            if ($resPujas) {
                $actividad['pujas_total'] = (int)($resPujas['pujas_total'] ?? 0);
                $actividad['ultima_puja'] = !empty($resPujas['ultima_puja']) ? (string)$resPujas['ultima_puja'] : null;
            }
        }

        $rolMapeado = $this->mapearRolUsuario((int)($row['id_rol'] ?? 0), (string)($row['rol_nombre'] ?? 'Usuario'));
        $nombre = trim((string)($row['nombre'] ?? ''));
        $apellido = trim((string)($row['apellido'] ?? ''));
        $nombreCompleto = trim($nombre . ' ' . $apellido);
        $username = ltrim(trim((string)($row['username'] ?? '')), '@');
        $ubicacion = trim((string)($row['ubicacion'] ?? ''));
        $activo = (int)($row['estado'] ?? 0) === 1;
        $correoPrincipal = $correos[0] ?? '';
        $telefonoPrincipal = $telefonos[0] ?? '';
        $inicialBase = $nombreCompleto !== '' ? $nombreCompleto : ($username !== '' ? $username : $correoPrincipal);
        $inicial = $inicialBase !== '' ? mb_strtoupper(mb_substr($inicialBase, 0, 1, 'UTF-8'), 'UTF-8') : 'U';

        $fechaRegistroRaw = (string)($row['fecha_registro'] ?? '');
        $fechaRegistroFmt = $this->formatearFechaDetalleEs($fechaRegistroRaw);
        $ultimaPujaFmt = $actividad['ultima_puja'] ? $this->formatearFechaDetalleEs($actividad['ultima_puja']) : 'Sin pujas';

        $esUltimoAdmin = ((int)$row['id_rol'] === 1 && $activo && $this->contarAdministradoresActivos() <= 1);

        return [
            'id_usuario'           => (int)$row['id_usuario'],
            'nombre'               => $nombre,
            'apellido'             => $apellido,
            'nombre_completo'      => $nombreCompleto !== '' ? $nombreCompleto : ($username !== '' ? '@' . $username : 'Usuario #' . (int)$row['id_usuario']),
            'username'             => $username,
            'inicial'              => $inicial,
            'correo'               => $correoPrincipal,
            'correos'              => $correos,
            'telefono'             => $telefonoPrincipal,
            'telefonos'            => $telefonos,
            'ubicacion'            => $ubicacion,
            'id_rol'               => (int)$row['id_rol'],
            'rol'                  => $rolMapeado['etiqueta'],
            'rol_class'            => $rolMapeado['clase'],
            'activo'               => $activo,
            'estado'               => $activo ? 'Activo' : 'Suspendido',
            'fecha_registro'       => $fechaRegistroRaw,
            'fecha_registro_fmt'   => $fechaRegistroFmt,
            'es_propio'            => ($idAdminActual > 0 && (int)$row['id_usuario'] === $idAdminActual),
            'es_ultimo_admin'      => $esUltimoAdmin,
            'productos'            => $actividad['productos'],
            'subastas_total'       => $actividad['subastas_total'],
            'subastas_activas'     => $actividad['subastas_activas'],
            'subastas_pendientes'  => $actividad['subastas_pendientes'],
            'subastas_finalizadas' => $actividad['subastas_finalizadas'],
            'subastas_sin_ofertas' => $actividad['subastas_sin_ofertas'],
            'subastas_canceladas'  => $actividad['subastas_canceladas'],
            'subastas_rechazadas'  => $actividad['subastas_rechazadas'],
            'pujas_total'          => $actividad['pujas_total'],
            'ultima_puja'          => $actividad['ultima_puja'],
            'ultima_puja_fmt'      => $ultimaPujaFmt,
        ];
    }

    private function contarAdministradoresActivos(): int
    {
        if (!$this->conexion || $this->conexion->connect_errno) {
            return 0;
        }
        $res = $this->conexion->query("SELECT COUNT(*) AS total FROM sb_usuario WHERE id_rol = 1 AND estado = 1");
        if ($res && ($row = $res->fetch_assoc())) {
            return (int)($row['total'] ?? 0);
        }
        return 0;
    }

    private function existeRol(int $idRol): bool
    {
        $stmt = $this->conexion->prepare("SELECT id_rol FROM sb_rol WHERE id_rol = ? LIMIT 1");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('i', $idRol);
        $stmt->execute();
        $existe = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $existe;
    }

    private function existeUsernameEnOtro(string $username, int $excluirId): bool
    {
        $stmt = $this->conexion->prepare("SELECT id_usuario FROM sb_usuario WHERE username = ? AND id_usuario <> ? LIMIT 1");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('si', $username, $excluirId);
        $stmt->execute();
        $existe = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $existe;
    }

    private function existeCorreoEnOtro(string $correo, int $excluirId): bool
    {
        $stmt = $this->conexion->prepare("SELECT id_usuario FROM sb_correo_usuario WHERE correo = ? AND id_usuario <> ? LIMIT 1");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('si', $correo, $excluirId);
        $stmt->execute();
        $existe = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $existe;
    }

    public function validarActualizacionUsuarioAdmin(int $idUsuario, array $datos, int $idAdminActual): array
    {
        if ($idUsuario <= 0 || !$this->conexion || $this->conexion->connect_errno) {
            return ['estado' => false, 'mensaje' => 'Usuario no válido.'];
        }

        $actual = $this->obtenerDetalleUsuarioAdmin($idUsuario, $idAdminActual);
        if (!$actual) {
            return ['estado' => false, 'mensaje' => 'El usuario solicitado no existe.'];
        }

        $nombre = trim((string)($datos['nombre'] ?? ''));
        $apellido = trim((string)($datos['apellido'] ?? ''));
        $username = ltrim(trim((string)($datos['username'] ?? '')), '@');
        $correo = strtolower(trim((string)($datos['correo'] ?? '')));
        $telefono = trim((string)($datos['telefono'] ?? ''));
        $ubicacion = trim((string)($datos['ubicacion'] ?? ''));
        $idRol = (int)($datos['id_rol'] ?? $actual['id_rol']);
        $estado = ((int)($datos['estado'] ?? ($actual['activo'] ? 1 : 0))) === 1 ? 1 : 0;

        if ($nombre === '') {
            return ['estado' => false, 'campo' => 'nombre', 'mensaje' => 'El nombre es obligatorio.'];
        }
        if (mb_strlen($nombre, 'UTF-8') < 2 || mb_strlen($nombre, 'UTF-8') > 100 || !preg_match('/^[\p{L}\s\'\-]+$/u', $nombre)) {
            return ['estado' => false, 'campo' => 'nombre', 'mensaje' => 'Ingresa un nombre válido (solo letras, mínimo 2 caracteres).'];
        }
        if ($apellido === '') {
            return ['estado' => false, 'campo' => 'apellido', 'mensaje' => 'El apellido es obligatorio.'];
        }
        if (mb_strlen($apellido, 'UTF-8') < 2 || mb_strlen($apellido, 'UTF-8') > 100 || !preg_match('/^[\p{L}\s\'\-]+$/u', $apellido)) {
            return ['estado' => false, 'campo' => 'apellido', 'mensaje' => 'Ingresa un apellido válido (solo letras, mínimo 2 caracteres).'];
        }
        if ($username === '' || strlen($username) < 3 || strlen($username) > 50 || !preg_match('/^[A-Za-z0-9._-]+$/', $username)) {
            return ['estado' => false, 'campo' => 'username', 'mensaje' => 'Usa entre 3 y 50 caracteres válidos (letras, números, punto o guion).'];
        }
        if ($correo === '' || strlen($correo) > 100 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return ['estado' => false, 'campo' => 'correo', 'mensaje' => 'Ingresa un correo electrónico válido.'];
        }
        if ($telefono !== '' && !preg_match('/^\d{4}-\d{4}$/', $telefono)) {
            return ['estado' => false, 'campo' => 'telefono', 'mensaje' => 'Usa el formato de 8 dígitos: 0000-0000.'];
        }
        if ($ubicacion !== '' && (mb_strlen($ubicacion, 'UTF-8') < 3 || mb_strlen($ubicacion, 'UTF-8') > 150 || !preg_match('/\p{L}/u', $ubicacion))) {
            return ['estado' => false, 'campo' => 'ubicacion', 'mensaje' => 'Ingresa una ubicación válida (mínimo 3 caracteres).'];
        }
        if (!$this->existeRol($idRol)) {
            return ['estado' => false, 'campo' => 'id_rol', 'mensaje' => 'El rol seleccionado no es válido.'];
        }

        // Impedir que el administrador autenticado se quite el rol o se suspenda a sí mismo
        if ($idUsuario === $idAdminActual) {
            if ($idRol !== (int)$actual['id_rol']) {
                return ['estado' => false, 'mensaje' => 'No puedes cambiar el rol de tu propia cuenta.'];
            }
            if ($estado !== 1) {
                return ['estado' => false, 'mensaje' => 'No puedes suspender tu propia cuenta mientras tienes la sesión activa.'];
            }
        }

        // Impedir dejar la plataforma sin administradores activos
        $eraAdminActivo = ((int)$actual['id_rol'] === 1 && !empty($actual['activo']));
        $dejaDeSerAdminActivo = ($idRol !== 1 || $estado !== 1);
        if ($eraAdminActivo && $dejaDeSerAdminActivo && $this->contarAdministradoresActivos() <= 1) {
            return ['estado' => false, 'mensaje' => 'Debe permanecer al menos un administrador activo en la plataforma.'];
        }

        if ($this->existeUsernameEnOtro($username, $idUsuario)) {
            return ['estado' => false, 'campo' => 'username', 'mensaje' => 'Ese nombre de usuario ya pertenece a otra cuenta.'];
        }
        if ($this->existeCorreoEnOtro($correo, $idUsuario)) {
            return ['estado' => false, 'campo' => 'correo', 'mensaje' => 'Ese correo electrónico ya pertenece a otra cuenta.'];
        }

        return [
            'estado'      => true,
            'actual'      => $actual,
            'normalizado' => [
                'nombre'    => $nombre,
                'apellido'  => $apellido,
                'username'  => $username,
                'correo'    => $correo,
                'telefono'  => $telefono,
                'ubicacion' => $ubicacion,
                'id_rol'    => $idRol,
                'estado'    => $estado,
            ],
        ];
    }

    public function actualizarUsuarioAdmin(int $idUsuario, array $datos, int $idAdminActual): array
    {
        $validacion = $this->validarActualizacionUsuarioAdmin($idUsuario, $datos, $idAdminActual);
        if (empty($validacion['estado'])) {
            return $validacion;
        }

        $actual = $validacion['actual'];
        $norm = $validacion['normalizado'];
        $nombre = $norm['nombre'];
        $apellido = $norm['apellido'];
        $username = $norm['username'];
        $correo = $norm['correo'];
        $telefono = $norm['telefono'];
        $ubicacion = $norm['ubicacion'];
        $idRol = $norm['id_rol'];
        $estado = $norm['estado'];

        $this->conexion->begin_transaction();
        try {
            $sqlUser = "UPDATE sb_usuario
                        SET nombre = ?, apellido = ?, username = ?, ubicacion = ?, estado = ?, id_rol = ?
                        WHERE id_usuario = ?";
            $stmtUser = $this->conexion->prepare($sqlUser);
            if (!$stmtUser) {
                throw new Exception('No se pudo preparar la actualización del usuario.');
            }
            $stmtUser->bind_param('ssssiii', $nombre, $apellido, $username, $ubicacion, $estado, $idRol, $idUsuario);
            if (!$stmtUser->execute()) {
                throw new Exception('Error al actualizar los datos del usuario.');
            }
            $stmtUser->close();

            $correoAnterior = (string)($actual['correo'] ?? '');
            if ($correo !== $correoAnterior) {
                if ($correoAnterior !== '') {
                    $stmtCorreo = $this->conexion->prepare("UPDATE sb_correo_usuario SET correo = ? WHERE id_usuario = ? AND correo = ?");
                    if (!$stmtCorreo) {
                        throw new Exception('No se pudo preparar la actualización del correo.');
                    }
                    $stmtCorreo->bind_param('sis', $correo, $idUsuario, $correoAnterior);
                } else {
                    $stmtCorreo = $this->conexion->prepare("INSERT INTO sb_correo_usuario (id_usuario, correo) VALUES (?, ?)");
                    if (!$stmtCorreo) {
                        throw new Exception('No se pudo preparar el registro del correo.');
                    }
                    $stmtCorreo->bind_param('is', $idUsuario, $correo);
                }
                if (!$stmtCorreo->execute()) {
                    throw new Exception('Error al guardar el correo electrónico.');
                }
                $stmtCorreo->close();
            }

            $telefonoAnterior = (string)($actual['telefono'] ?? '');
            if ($telefono !== $telefonoAnterior) {
                if ($telefono === '' && $telefonoAnterior !== '') {
                    $stmtTel = $this->conexion->prepare("DELETE FROM sb_telefono_usuario WHERE id_usuario = ? AND telefono = ?");
                    if ($stmtTel) {
                        $stmtTel->bind_param('is', $idUsuario, $telefonoAnterior);
                        $stmtTel->execute();
                        $stmtTel->close();
                    }
                } elseif ($telefono !== '' && $telefonoAnterior !== '') {
                    $stmtTel = $this->conexion->prepare("UPDATE sb_telefono_usuario SET telefono = ? WHERE id_usuario = ? AND telefono = ?");
                    if (!$stmtTel) {
                        throw new Exception('No se pudo preparar la actualización del teléfono.');
                    }
                    $stmtTel->bind_param('sis', $telefono, $idUsuario, $telefonoAnterior);
                    if (!$stmtTel->execute()) {
                        throw new Exception('Error al actualizar el teléfono.');
                    }
                    $stmtTel->close();
                } elseif ($telefono !== '') {
                    $stmtTel = $this->conexion->prepare("INSERT INTO sb_telefono_usuario (id_usuario, telefono) VALUES (?, ?)");
                    if (!$stmtTel) {
                        throw new Exception('No se pudo preparar el registro del teléfono.');
                    }
                    $stmtTel->bind_param('is', $idUsuario, $telefono);
                    if (!$stmtTel->execute()) {
                        throw new Exception('Error al guardar el teléfono.');
                    }
                    $stmtTel->close();
                }
            }

            $this->conexion->commit();
            return [
                'estado'  => true,
                'mensaje' => 'Usuario actualizado correctamente.',
                'usuario' => $this->obtenerDetalleUsuarioAdmin($idUsuario, $idAdminActual),
            ];
        } catch (Exception $e) {
            $this->conexion->rollback();
            return ['estado' => false, 'mensaje' => $e->getMessage()];
        }
    }

    public function validarCreacionUsuarioAdmin(array $datos): array
    {
        if (!$this->conexion || $this->conexion->connect_errno) {
            return ['estado' => false, 'mensaje' => 'No hay conexión disponible con la base de datos.'];
        }

        $nombre = trim((string)($datos['nombre'] ?? ''));
        $apellido = trim((string)($datos['apellido'] ?? ''));
        $username = ltrim(trim((string)($datos['username'] ?? '')), '@');
        $correo = strtolower(trim((string)($datos['correo'] ?? '')));
        $telefono = trim((string)($datos['telefono'] ?? ''));
        $ubicacion = trim((string)($datos['ubicacion'] ?? ''));
        $idRol = (int)($datos['id_rol'] ?? 2);
        $estado = ((int)($datos['estado'] ?? 1)) === 1 ? 1 : 0;
        $contrasenia = (string)($datos['contrasenia'] ?? '');
        $confirmar = (string)($datos['confirmar_contrasenia'] ?? '');

        if ($nombre === '') {
            return ['estado' => false, 'campo' => 'nombre', 'mensaje' => 'El nombre es obligatorio.'];
        }
        if (mb_strlen($nombre, 'UTF-8') < 2 || mb_strlen($nombre, 'UTF-8') > 100 || !preg_match('/^[\p{L}\s\'\-]+$/u', $nombre)) {
            return ['estado' => false, 'campo' => 'nombre', 'mensaje' => 'Ingresa un nombre válido (solo letras, mínimo 2 caracteres).'];
        }
        if ($apellido === '') {
            return ['estado' => false, 'campo' => 'apellido', 'mensaje' => 'El apellido es obligatorio.'];
        }
        if (mb_strlen($apellido, 'UTF-8') < 2 || mb_strlen($apellido, 'UTF-8') > 100 || !preg_match('/^[\p{L}\s\'\-]+$/u', $apellido)) {
            return ['estado' => false, 'campo' => 'apellido', 'mensaje' => 'Ingresa un apellido válido (solo letras, mínimo 2 caracteres).'];
        }
        if ($correo === '' || strlen($correo) > 100 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return ['estado' => false, 'campo' => 'correo', 'mensaje' => 'Ingresa un correo electrónico válido.'];
        }
        if ($telefono !== '' && !preg_match('/^\d{4}-\d{4}$/', $telefono)) {
            return ['estado' => false, 'campo' => 'telefono', 'mensaje' => 'Usa el formato de 8 dígitos: 0000-0000.'];
        }
        if ($ubicacion !== '' && (mb_strlen($ubicacion, 'UTF-8') < 3 || mb_strlen($ubicacion, 'UTF-8') > 150 || !preg_match('/\p{L}/u', $ubicacion))) {
            return ['estado' => false, 'campo' => 'ubicacion', 'mensaje' => 'Ingresa una ubicación válida (mínimo 3 caracteres).'];
        }
        if ($username === '' || strlen($username) < 3 || strlen($username) > 50 || !preg_match('/^[A-Za-z0-9._-]+$/', $username)) {
            return ['estado' => false, 'campo' => 'username', 'mensaje' => 'Usa entre 3 y 50 caracteres válidos (letras, números, punto o guion).'];
        }
        if (!$this->existeRol($idRol)) {
            return ['estado' => false, 'campo' => 'id_rol', 'mensaje' => 'El rol seleccionado no es válido.'];
        }
        if ($contrasenia === '' || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s]).{8,}$/', $contrasenia)) {
            return ['estado' => false, 'campo' => 'contrasenia', 'mensaje' => 'Debe tener mínimo 8 caracteres, mayúscula, minúscula, número y símbolo.'];
        }
        if ($confirmar === '' || $contrasenia !== $confirmar) {
            return ['estado' => false, 'campo' => 'confirmar_contrasenia', 'mensaje' => 'Las contraseñas no coinciden.'];
        }
        if ($this->existeCorreoEnOtro($correo, 0)) {
            return ['estado' => false, 'campo' => 'correo', 'mensaje' => 'Ese correo electrónico ya está registrado.'];
        }
        if ($this->existeUsernameEnOtro($username, 0)) {
            return ['estado' => false, 'campo' => 'username', 'mensaje' => 'Ese nombre de usuario ya está en uso.'];
        }

        return [
            'estado'      => true,
            'normalizado' => [
                'nombre'      => $nombre,
                'apellido'    => $apellido,
                'username'    => $username,
                'correo'      => $correo,
                'telefono'    => $telefono,
                'ubicacion'   => $ubicacion,
                'id_rol'      => $idRol,
                'estado'      => $estado,
                'contrasenia' => $contrasenia,
            ],
        ];
    }

    public function crearUsuarioAdmin(array $datos, int $idAdminActual = 0): array
    {
        $validacion = $this->validarCreacionUsuarioAdmin($datos);
        if (empty($validacion['estado'])) {
            return $validacion;
        }

        $norm = $validacion['normalizado'];
        $passHash = password_hash($norm['contrasenia'], PASSWORD_DEFAULT);

        $this->conexion->begin_transaction();
        try {
            $sqlUser = "INSERT INTO sb_usuario (nombre, apellido, username, contrasenia, ubicacion, estado, id_rol)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtUser = $this->conexion->prepare($sqlUser);
            if (!$stmtUser) {
                throw new Exception('No se pudo preparar el registro del usuario.');
            }
            $stmtUser->bind_param(
                'sssssii',
                $norm['nombre'],
                $norm['apellido'],
                $norm['username'],
                $passHash,
                $norm['ubicacion'],
                $norm['estado'],
                $norm['id_rol']
            );
            if (!$stmtUser->execute()) {
                throw new Exception('Error al registrar el usuario.');
            }
            $idUsuario = (int)$this->conexion->insert_id;
            $stmtUser->close();

            $stmtCorreo = $this->conexion->prepare("INSERT INTO sb_correo_usuario (id_usuario, correo) VALUES (?, ?)");
            if (!$stmtCorreo) {
                throw new Exception('No se pudo preparar el registro del correo.');
            }
            $stmtCorreo->bind_param('is', $idUsuario, $norm['correo']);
            if (!$stmtCorreo->execute()) {
                throw new Exception('Error al guardar el correo electrónico.');
            }
            $stmtCorreo->close();

            if ($norm['telefono'] !== '') {
                $stmtTel = $this->conexion->prepare("INSERT INTO sb_telefono_usuario (id_usuario, telefono) VALUES (?, ?)");
                if (!$stmtTel) {
                    throw new Exception('No se pudo preparar el registro del teléfono.');
                }
                $stmtTel->bind_param('is', $idUsuario, $norm['telefono']);
                if (!$stmtTel->execute()) {
                    throw new Exception('Error al guardar el teléfono.');
                }
                $stmtTel->close();
            }

            $this->conexion->commit();
            return [
                'estado'  => true,
                'mensaje' => 'Usuario creado correctamente.',
                'usuario' => $this->obtenerDetalleUsuarioAdmin($idUsuario, $idAdminActual),
            ];
        } catch (Exception $e) {
            $this->conexion->rollback();
            return ['estado' => false, 'mensaje' => $e->getMessage()];
        }
    }

    private function formatearFechaDetalleEs(string $fechaRaw): string
    {
        if (trim($fechaRaw) === '') {
            return '—';
        }
        $ts = strtotime($fechaRaw);
        if ($ts === false) {
            return $fechaRaw;
        }
        $meses = [
            1 => 'ene.', 2 => 'feb.', 3 => 'mar.', 4 => 'abr.',
            5 => 'may.', 6 => 'jun.', 7 => 'jul.', 8 => 'ago.',
            9 => 'sep.', 10 => 'oct.', 11 => 'nov.', 12 => 'dic.',
        ];
        $dia = (int)date('j', $ts);
        $mes = $meses[(int)date('n', $ts)] ?? '';
        $anio = date('Y', $ts);
        $hora = date('h:i', $ts);
        $ampm = date('a', $ts) === 'pm' ? 'p. m.' : 'a. m.';

        return "{$dia} {$mes} {$anio} · {$hora} {$ampm}";
    }

    public function obtenerSubastasDestacadas(int $limite = 3, string $baseUrl = ''): array
    {
        $subastas = [];
        if (!$this->conexion || $this->conexion->connect_errno) {
            return $subastas;
        }

        $limite = max(1, min(10, $limite));
        $sql = "SELECT s.id_subasta,
                       s.precio_inicial,
                       s.fecha_inicial,
                       s.fecha_final,
                       TIMESTAMPDIFF(SECOND, NOW(), s.fecha_final) AS segundos_restantes,
                       p.id_producto,
                       p.nombre AS titulo,
                       COALESCE(c.nombre, 'General') AS categoria,
                       c.imagen AS categoria_imagen,
                       (SELECT img.ruta
                        FROM sb_imagen_producto img
                        WHERE img.id_producto = p.id_producto
                        ORDER BY img.principal DESC, img.id_imagen_producto ASC
                        LIMIT 1) AS imagen_ruta,
                       COUNT(pu.id_puja) AS total_pujas,
                       COALESCE(MAX(pu.monto), s.precio_inicial) AS precio_actual
                FROM sb_subasta s
                INNER JOIN sb_producto p ON s.id_producto = p.id_producto
                LEFT JOIN sb_categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN sb_puja pu ON s.id_subasta = pu.id_subasta
                WHERE LOWER(s.estado) = 'activa'
                  AND s.fecha_final > NOW()
                GROUP BY s.id_subasta, s.precio_inicial, s.fecha_inicial, s.fecha_final,
                         p.id_producto, p.nombre, c.nombre, c.imagen
                ORDER BY total_pujas DESC, precio_actual DESC, s.fecha_final ASC
                LIMIT {$limite}";

        $res = $this->conexion->query($sql);
        if (!$res) {
            return $subastas;
        }

        while ($row = $res->fetch_assoc()) {
            $segundos = max(0, (int)($row['segundos_restantes'] ?? 0));
            $totalPujas = (int)($row['total_pujas'] ?? 0);
            $precioActual = (float)($row['precio_actual'] ?? 0);

            $rutaImagen = trim((string)($row['imagen_ruta'] ?? ''));
            if ($rutaImagen === '') {
                $rutaImagen = trim((string)($row['categoria_imagen'] ?? ''));
            }
            if ($rutaImagen === '') {
                $rutaImagen = 'assets/img/cat-coleccionables.jpg';
            }

            if (strpos($rutaImagen, 'http://') === 0 || strpos($rutaImagen, 'https://') === 0) {
                $imagenUrl = $rutaImagen;
            } elseif (strpos($rutaImagen, 'uploads/') === 0 || strpos($rutaImagen, 'assets/') === 0) {
                $imagenUrl = $baseUrl . ltrim($rutaImagen, '/');
            } else {
                $imagenUrl = $baseUrl . 'uploads/productos/' . ltrim($rutaImagen, '/');
            }

            $subastas[] = [
                'id_subasta'      => (int)$row['id_subasta'],
                'titulo'          => (string)($row['titulo'] ?? ''),
                'categoria'       => (string)($row['categoria'] ?? 'General'),
                'total_pujas'     => $totalPujas,
                'precio'          => '$' . number_format($precioActual, 2, '.', ','),
                'precio_label'    => $totalPujas > 0 ? 'Puja actual' : 'Precio inicial',
                'tiempo'          => $this->formatearTiempoRestante($segundos),
                'fecha_final_iso' => date('c', time() + $segundos),
                'imagen'          => $imagenUrl,
            ];
        }

        return $subastas;
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
}
