# Estados de Subasta (`sb_subasta.estado`)

Valores permitidos para la columna `estado` en `sb_subasta`:

- **`pendiente`**
  El vendedor envió la solicitud de subasta y espera revisión del moderador. No aparece en Inicio ni Catálogo.

- **`activa`**
  Aprobada por moderación. Visible en Inicio y Catálogo; acepta pujas hasta `fecha_final`.

- **`rechazada`**
  No aprobada por moderación. No se publica; el vendedor puede corregir y volver a enviarla.

- **`finalizada`**
  Llegó a `fecha_final` con al menos una puja (tiene ganador). Ya no acepta pujas.

- **`sin_ofertas`**
  Llegó a `fecha_final` sin pujas. Ya no acepta pujas.

- **`cancelada`**
  Anulada antes de finalizar (por el vendedor, moderación o administración).

## Flujo

1. El vendedor solicita la subasta -> entra como `pendiente` (valor por defecto en BD).
2. Moderación revisa -> pasa a `activa` o `rechazada`.
3. Al vencer `fecha_final` -> pasa a `finalizada` (si hubo pujas) o `sin_ofertas` (si no hubo).
4. Si se anula antes de cerrar -> pasa a `cancelada`.

## Base de datos

- **Columna:** `sb_subasta.estado` (`VARCHAR(25) NOT NULL DEFAULT 'pendiente'`).
- **Restricción:** `ck_subasta_estado` valida que solo se guarden los 6 valores permitidos.
- **Nota:** `sb_producto.estado` es independiente (`TINYINT(1) NOT NULL DEFAULT 1`, donde `1` = activo y `0` = inactivo).

### Script SQL (local / producción)

```sql
UPDATE sb_subasta
SET estado = LOWER(TRIM(estado));

ALTER TABLE sb_subasta
  MODIFY COLUMN estado VARCHAR(25) NOT NULL DEFAULT 'pendiente',
  ADD CONSTRAINT ck_subasta_estado CHECK (
    estado IN ('pendiente', 'activa', 'rechazada', 'finalizada', 'sin_ofertas', 'cancelada')
  );
```

## Uso en el código

- `dao/SubastaDAO.php`: consulta únicamente subastas con `LOWER(s.estado) = 'activa'` y `s.fecha_final > NOW()` para Inicio y Catálogo.
- `views/subastas/mis-productos.php`: muestra el estado actual de cada producto del vendedor (`En revisión` para `pendiente`, `Activa` para `activa`, etc.) y controla las acciones disponibles.
