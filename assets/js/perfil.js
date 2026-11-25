// Misma regla que el registro (assets/js/registro.js) y la recuperación de contraseña
const REGEX_PASSWORD = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s]).{8,}$/;
const MSG_PASSWORD = "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo.";

document.addEventListener("DOMContentLoaded", () => {
    // para borradores de prueba gg
    document.querySelectorAll("[data-proximamente]").forEach((enlace) => {
        enlace.addEventListener("click", (evento) => {
            evento.preventDefault();
            mostrarToast("info", enlace.dataset.proximamente, "Esta sección estará disponible próximamente.");
        });
    });

    // cambiar contra
    const modalEl = document.getElementById("modalCambiarClave");
    const form = document.getElementById("formCambiarClave");
    const btnGuardar = document.getElementById("btnGuardarClave");
    if (!modalEl || !form) return;

    const actual = document.getElementById("contrasenia_actual");
    const nueva = document.getElementById("nueva_contrasenia");
    const confirmar = document.getElementById("confirmar_contrasenia");

    // ojito d los campos
    modalEl.querySelectorAll(".btn-toggle-eye").forEach((btn) => {
        btn.addEventListener("click", () => {
            const objetivo = document.getElementById(btn.getAttribute("data-target"));
            const icono = btn.querySelector("i");
            if (!objetivo) return;
            const oculto = objetivo.type === "password";
            objetivo.type = oculto ? "text" : "password";
            icono.classList.toggle("bi-eye", !oculto);
            icono.classList.toggle("bi-eye-slash", oculto);
        });
    });

    function marcar(campo, ok) {
        campo.classList.toggle("is-invalid", !ok);
        campo.classList.toggle("is-valid", ok);
        return ok;
    }

    const validarActual = () => marcar(actual, actual.value !== "");
    const validarNueva = () => marcar(nueva, REGEX_PASSWORD.test(nueva.value));
    const validarConfirmar = () => marcar(confirmar, confirmar.value !== "" && confirmar.value === nueva.value);

    actual.addEventListener("blur", validarActual);
    nueva.addEventListener("blur", validarNueva);
    confirmar.addEventListener("blur", validarConfirmar);
    [[actual, validarActual], [nueva, validarNueva], [confirmar, validarConfirmar]].forEach(([campo, validar]) => {
        campo.addEventListener("input", () => {
            if (campo.classList.contains("is-invalid")) validar();
            if (campo === nueva && confirmar.value !== "") validarConfirmar();
        });
    });

    // limpiar cajas de contras
    modalEl.addEventListener("hidden.bs.modal", () => {
        form.reset();
        [actual, nueva, confirmar].forEach((campo) => {
            campo.classList.remove("is-valid", "is-invalid");
            campo.type = "password";
        });
        modalEl.querySelectorAll(".btn-toggle-eye i").forEach((i) => {
            i.classList.add("bi-eye");
            i.classList.remove("bi-eye-slash");
        });
    });

    modalEl.addEventListener("shown.bs.modal", () => actual.focus());

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const okActual = validarActual();
        const okNueva = validarNueva();
        const okConfirmar = validarConfirmar();

        if (!okActual || !okNueva || !okConfirmar) {
            let mensaje = "Revisa los campos marcados en rojo.";
            if (!okActual) mensaje = "Escribe tu contraseña actual.";
            else if (!okNueva) mensaje = MSG_PASSWORD;
            else if (!okConfirmar) mensaje = "Las contraseñas nuevas no coinciden.";
            mostrarToast("warning", "Revisa el formulario", mensaje);
            return;
        }

        try {
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

            const datos = new FormData(form);
            datos.append("opcion", "cambiarContrasenia");

            const respuesta = await fetch(form.action, { method: "POST", body: datos });
            const resultado = await respuesta.json().catch(() => null);

            if (resultado && resultado.auth_required) {
                window.location.href = resultado.redirect;
                return;
            }
            if (!resultado) throw new Error("Respuesta inesperada del servidor (" + respuesta.status + ").");

            if (resultado.estado) {
                bootstrap.Modal.getInstance(modalEl).hide();
                mostrarToast("success", "Contraseña actualizada", resultado.mensaje);
            } else {
                if (resultado.campo === "actual") {
                    marcar(actual, false);
                    actual.focus();
                } else if (resultado.campo === "nueva") {
                    marcar(nueva, false);
                } else if (resultado.campo === "confirmar") {
                    marcar(confirmar, false);
                }
                mostrarToast("error", "No se pudo cambiar", resultado.mensaje || "Inténtalo de nuevo.");
            }
        } catch (error) {
            mostrarToast("error", "Error de conexión", error.message || "No se pudo comunicar con el servidor.");
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.textContent = "Guardar contraseña";
        }
    });
});
