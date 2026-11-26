# Actualización en Tiempo Real y Animación de Precios

Documentación breve sobre cómo se refrescan los precios y KPIs sin recargar la página, y dónde está el código que hace el destello suave cuando cambia una puja.

## Dónde se encuentra

- **Inicio público (`index.php`)**
  - `assets/js/temporizador-subastas.js`: cuenta regresiva cada 1 s, refresco de subastas cada 2 s y animación de cambio de precio.
  - `controllers/subasta_controller.php`: endpoint `opcion=home` que devuelve en JSON la destacada, las que terminan pronto y las nuevas (con cabeceras `no-store` para evitar caché).
  - `index.php`: carga `temporizador-subastas.js` con `?v=filemtime(...)` para invalidar caché al editar el archivo.

- **Paneles de Administración y Moderación**
  - `assets/js/admin-kpis.js`: actualiza las tarjetas KPI del administrador cada 2 s desde `controllers/admin_controller.php?opcion=kpis`.
  - `assets/js/moderador-kpis.js`: actualiza los KPIs y contadores de pendientes del moderador cada 2 s desde `controllers/moderador_controller.php?opcion=kpis`.
  - `views/admin/dashboard.php` y `views/moderador/dashboard.php`: cargan ambos scripts con `?v=filemtime(...)`.

## Cómo funciona

### 1. Refresco periódico (polling cada 2 segundos)
En lugar de abrir WebSockets, los scripts consultan su controlador con `fetch(..., { cache: 'no-store' })` y un parámetro `&_t=Date.now()` cada 2 segundos.

Para no sobrecargar el servidor local ni generar ruido:
- Si la pestaña no está visible (`document.hidden`), no se hacen peticiones. Al volver a la pestaña (`visibilitychange`), se dispara una consulta inmediata.
- Se usa una bandera (`actualizandoHome` / `enCurso`) para no lanzar una petición nueva si la anterior sigue pendiente.
- En `temporizador-subastas.js`, `obtenerFirmaHome()` compara IDs y precios contra el último estado; solo vuelve a renderizar el DOM si hubo algún cambio real.

### 2. Destello al cambiar un precio (Web Animations API)
El efecto visual en los precios del Inicio no usa librerías externas ni reglas extra en CSS; utiliza `el.animate()` de la **Web Animations API**, que viene integrada de forma nativa en los navegadores modernos.

Flujo dentro de `assets/js/temporizador-subastas.js`:
1. `preciosPrevios` (un `Map`) guarda el último precio visto de cada `id_subasta`.
2. `registrarPrecios()` compara cada precio entrante con el guardado y recolecta los IDs que cambiaron (se omite en la primera carga para no animar todo al abrir la página).
3. Cada `<span>` de precio se renderiza con el atributo `data-precio-subasta="<id>"` (en Destacada, Terminan pronto y Nuevas subastas).
4. Después de actualizar el HTML, `resaltarPreciosCambiados()` busca esos elementos y ejecuta una transición corta con `el.animate()` usando el tono verde salvia `#8fa870` y opacidad `0.75 -> 1` durante `650 ms`.

```js
if (typeof el.animate === 'function') {
    el.animate(
        [
            { color: '#8fa870', opacity: 0.75 },
            { opacity: 1 }
        ],
        { duration: 650, easing: 'ease-out' }
    );
}
```

La comprobación `typeof el.animate === 'function'` asegura que, si un navegador viejo no soporta la API, el precio se actualice normalmente sin errores en consola.

## Ajustes rápidos

- **Cambiar la frecuencia de consulta:** modificar el intervalo (`2000`) en `temporizador-subastas.js`, `admin-kpis.js` o `moderador-kpis.js`.
- **Cambiar o quitar el destello:** editar los colores/duración dentro de `resaltarPreciosCambiados()` en `assets/js/temporizador-subastas.js`, o eliminar su llamada dentro de `refrescarSubastasHome()`.
