document.addEventListener("DOMContentLoaded", () => {
    // cambiar la foto principal al dar clic en una miniatura
    const principal = document.getElementById("imgPrincipal");
    const miniaturas = document.querySelectorAll(".subasta-mini");

    miniaturas.forEach((mini) => {
        mini.addEventListener("click", () => {
            if (!principal) return;
            principal.src = mini.dataset.img;
            miniaturas.forEach((m) => m.classList.remove("activa"));
            mini.classList.add("activa");
        });
    });

    // el boton de pujar t toca adan
    document.querySelectorAll("[data-proximamente]").forEach((btn) => {
        btn.addEventListener("click", () => {
            mostrarToast("info", btn.dataset.proximamente, "Esta función estará disponible próximamente.");
        });
    });
});
