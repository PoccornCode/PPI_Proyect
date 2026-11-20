document.addEventListener("DOMContentLoaded", () => {
    // Secciones que todavía no existen: aviso en lugar de un enlace roto
    document.querySelectorAll("[data-proximamente]").forEach((enlace) => {
        enlace.addEventListener("click", (evento) => {
            evento.preventDefault();
            mostrarToast("info", enlace.dataset.proximamente, "Esta sección estará disponible próximamente.");
        });
    });
});
