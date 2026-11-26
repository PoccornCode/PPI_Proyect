# Configuración del Sistema (`sb_configuracion`) — Propuesta Opcional

> **Estado:** Funcionalidad futura / opcional (implementar únicamente si queda tiempo en el proyecto).
> **Actualmente:** Solo existe el diseño visual e interacción de referencia en `views/admin/dashboard.php` (`#sec-configuracion`), sin persistencia en base de datos.

## Requisito previo

Si se acepta e implementa esta funcionalidad, **es obligatorio crear primero la tabla `sb_configuracion` en la base de datos** (local y producción) y añadir su estructura y valores iniciales al script `database/restaurar_bd.sql`.

---

## Base de datos propuesta

Se recomienda un esquema **clave–valor** simple para evitar alterar la tabla cada vez que se agregue o quite un parámetro.

### Script SQL (local / producción)

```sql
CREATE TABLE IF NOT EXISTS `sb_configuracion` (
  `clave` varchar(60) NOT NULL,
  `valor` text NOT NULL,
  `grupo` varchar(30) NOT NULL DEFAULT 'general',
  `actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sb_configuracion` (`clave`, `valor`, `grupo`) VALUES
('revision_previa',        '1',                                                           'subastas'),
('precio_minimo',          '5.00',                                                        'subastas'),
('incremento_minimo',      '2.00',                                                        'subastas'),
('duracion_maxima',        '7',                                                           'subastas'),
('max_imagenes',           '5',                                                           'subastas'),
('anti_sniping',           '1',                                                           'subastas'),
('cancelar_sin_pujas',     '1',                                                           'subastas'),
('registro_publico',       '1',                                                           'seguridad'),
('verificacion_correo',    '1',                                                           'seguridad'),
('expira_codigo',          '15',                                                          'seguridad'),
('max_intentos',           '5',                                                           'seguridad'),
('tiempo_inactividad',     '30',                                                          'seguridad'),
('nombre_remitente',       'Gavel.net',                                                   'correo'),
('correo_soporte',         'soporte@gavel.net',                                           'correo'),
('aviso_moderacion',       '1',                                                           'correo'),
('aviso_contacto',         '1',                                                           'correo'),
('banner_activo',          '0',                                                           'operacion'),
('banner_texto',           'Nuevas subastas verificadas disponibles este fin de semana.', 'operacion'),
('modo_mantenimiento',     '0',                                                           'operacion'),
('mensaje_mantenimiento',  'Estamos realizando mejoras en Gavel.net. Volvemos en unos minutos.', 'operacion')
ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`);
```

---

## Parámetros y puntos de integración

### 1. Subastas y pujas
- **`revision_previa`** (`1`/`0`): si es `1`, las subastas nuevas entran como `pendiente`; si es `0`, se publican directo como `activa` (`controllers/ControllerProducto.php`).
- **`precio_minimo`** (`decimal`): monto mínimo permitido para `sb_subasta.precio_inicial` (`views/subastas/registro-producto.php` y `controllers/ControllerProducto.php`).
- **`incremento_minimo`** (`decimal`): incremento mínimo sugerido por defecto al registrar una subasta.
- **`duracion_maxima`** (`int`, días): límite máximo permitido entre `fecha_inicial` y `fecha_final`.
- **`max_imagenes`** (`int`): cantidad máxima de archivos aceptados en `sb_imagen_producto` (`controllers/ControllerProducto.php`).
- **`anti_sniping`** (`1`/`0`): si entra una puja en el último minuto, extiende `fecha_final` en 2 minutos (`controllers/subasta_controller.php`).
- **`cancelar_sin_pujas`** (`1`/`0`): habilita o restringe que el vendedor cancele una subasta activa sin ofertas (`views/subastas/mis-productos.php`).

### 2. Seguridad y cuentas
- **`registro_publico`** (`1`/`0`): permite o pausa altas nuevas en `views/auth/registro.php` y `controllers/registro_controller.php`.
- **`verificacion_correo`** (`1`/`0`): exige confirmar el código de `sb_codigo_verificacion` antes de activar la cuenta.
- **`expira_codigo`** (`int`, minutos): tiempo de expiración al generar registros en `sb_codigo_verificacion` (`dao/RegistroDAO.php` / `dao/RecuperacionDAO.php`).
- **`max_intentos`** (`int`): límite de intentos fallidos antes de invalidar un código.
- **`tiempo_inactividad`** (`int`, minutos; `0` = sin límite): tiempo configurado en `helpers/sesion_inactividad.php`.

### 3. Correo y avisos
- **`nombre_remitente`** (`string`) y **`correo_soporte`** (`email`): valores visibles en `helpers/Mailer.php` y `views/contacto/contacto.php`.
- **`aviso_moderacion`** (`1`/`0`): envía correo al vendedor cuando su subasta es aprobada o rechazada (`controllers/moderador_controller.php`).
- **`aviso_contacto`** (`1`/`0`): envía copia a `correo_soporte` al registrarse un mensaje en `sb_contacto` (`controllers/contacto_controller.php`).

### 4. Operación
- **`banner_activo`** (`1`/`0`) y **`banner_texto`** (`string`): franja opcional de comunicado en la parte superior del sitio público (`views/NavBar/nav.php`).
- **`modo_mantenimiento`** (`1`/`0`) y **`mensaje_mantenimiento`** (`string`): pausa el acceso público y las pujas mostrando el mensaje configurado, manteniendo acceso únicamente para usuarios con `id_rol = 1` (Administrador).

---

## Arquitectura sugerida si se implementa

1. **`dao/ConfiguracionDAO.php`**: `obtenerTodas()` y `guardarMultiples(array $pares)` con consultas preparadas (`INSERT ... ON DUPLICATE KEY UPDATE`).
2. **`helpers/Configuracion.php`**: función `obtenerConfig(string $clave, $default = null)` con caché estática por petición para consultar la BD una sola vez por carga de página.
3. **`controllers/configuracion_controller.php`**: valida sesión de Administrador (`id_rol === 1`), recibe los campos del formulario `#admCfgForm` vía `POST` (JSON/FormData), valida tipos/rangos y persiste mediante `ConfiguracionDAO`.
4. **Vista actual**: `#sec-configuracion` en `views/admin/dashboard.php` (los `name="..."` de los inputs ya coinciden con las claves de `sb_configuracion`).
