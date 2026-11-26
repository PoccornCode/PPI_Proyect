document.querySelectorAll(".form-eliminar").forEach(form => {
    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        if (!confirm("¿Seguro que quieres eliminar este producto?")) return;
        const columna = form.closest(".col-md-6, .col-lg-4, .col-xl-3") || form.closest(".producto-card");

        try {
            const datos = new FormData(form);
            const respuesta = await fetch(form.action, { method: "POST", body: datos });
            const resultado = await respuesta.json();

            if (resultado.estado) {
                if (columna) columna.remove();
                mostrarToast("success", "Producto eliminado", resultado.mensaje, 3000);
                if (document.querySelectorAll(".producto-card").length === 0) {
                    location.reload();
                }
            } else {
                mostrarToast("error", "No se pudo eliminar", resultado.mensaje, 3000);
            }
        } catch (error) {
            console.error(error);
            mostrarToast("error", "Error", "Ocurrió un error al intentar eliminar el producto.", 3000);
        }
    });
});