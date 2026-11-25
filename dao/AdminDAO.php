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

    public function listarUsuariosAdmin(string $busqueda = '', int $idRol = 0, int $pagina = 1, int $porPagina = 10): array
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
