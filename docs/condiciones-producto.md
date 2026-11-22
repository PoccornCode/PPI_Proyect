# Condiciones de Producto (`sb_producto.condicion`)

Valores permitidos para la columna `condicion` en `sb_producto` (ordenados de mejor a peor estado físico):

- **`Nuevo`**
  Etiqueta en el formulario: Nuevo (Sellado en empaque original)

- **`Como nuevo`**
  Etiqueta en el formulario: Como nuevo (Sin marcas de uso o defectos)

- **`Excelente`**
  Etiqueta en el formulario: Excelente (Mínimo uso, excelente estado funcional)

- **`Buen estado`**
  Etiqueta en el formulario: Buen estado (Signos leves de uso normal)

- **`Aceptable`**
  Etiqueta en el formulario: Aceptable (Marcas visibles de desgaste, funcional)

- **`Para repuestos o restauración`**
  Etiqueta en el formulario: Para repuestos o restauración

## Base de datos

- **Columna:** `sb_producto.condicion` (`VARCHAR(40) NOT NULL`).
- **Restricción:** `ck_producto_condicion` valida que solo se guarden los 6 valores permitidos.
- En la BD se guarda únicamente el texto corto del `value`, no la descripción entre paréntesis.

### Script SQL (local / producción)

```sql
UPDATE sb_producto
SET condicion = TRIM(condicion);

ALTER TABLE sb_producto
  MODIFY COLUMN condicion VARCHAR(40) NOT NULL,
  ADD CONSTRAINT ck_producto_condicion CHECK (
    condicion IN (
      'Nuevo',
      'Como nuevo',
      'Excelente',
      'Buen estado',
      'Aceptable',
      'Para repuestos o restauración'
    )
  );
```

## Uso en el código

- `views/subastas/registro-producto.php`: `<select id="condicion" name="condicion">` con las 6 opciones.
- `controllers/ControllerProducto.php`: recibe `$_POST['condicion']` y lo envía al DAO.
- `dao/ProductoDAO.php`: inserta, consulta y actualiza `condicion` en `sb_producto`.
