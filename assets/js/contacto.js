const REGEX_EMAIL_CONTACTO = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const REGEX_TELEFONO_CONTACTO = /^\d{4}-\d{4}$/;

function validarCampoContacto(campo) {
    if (!campo) return true;
    const valor = campo.value.trim();
    let valido = valor !== "";

    if (valido && campo.id === "correo") {
        valido = REGEX_EMAIL_CONTACTO.test(valor);
    } else if (valido && campo.id === "telefono") {
        valido = REGEX_TELEFONO_CONTACTO.test(valor);
    } else if (valido && campo.id === "mensaje") {
        valido = valor.length >= 10;
    }

    campo.classList.toggle("is-invalid", !valido);
    campo.classList.toggle("is-valid", valido);
    return valido;
}

document.addEventListener("DOMContentLoaded", () => {
    const formulario = document.getElementById("formContacto");
    const btnEnviar = document.getElementById("btnEnviarContacto");
    const overlayContacto = document.getElementById("overlayContacto");

    const nombre = document.getElementById("nombre");
    const correo = document.getElementById("correo");
    const telefono = document.getElementById("telefono");
    const asunto = document.getElementById("asunto");
    const mensaje = document.getElementById("mensaje");

    const camposObligatorios = [nombre, correo, telefono, asunto, mensaje];

    camposObligatorios.forEach((campo) => {
        if (campo) {
            campo.addEventListener("blur", () => validarCampoContacto(campo));
            campo.addEventListener("input", () => {
                if (campo.classList.contains("is-invalid")) {
                    validarCampoContacto(campo);
                }
            });
        }
    });

    if (telefono) {
        telefono.addEventListener("input", () => {
            let valor = telefono.value.replace(/\D/g, "");
            valor = valor.substring(0, 8);
            if (valor.length > 4) {
                valor = valor.substring(0, 4) + "-" + valor.substring(4);
            }
            telefono.value = valor;
        });
    }

    if (formulario) {
        formulario.addEventListener("submit", async (e) => {
            e.preventDefault();

            let todosValidos = true;
            camposObligatorios.forEach((campo) => {
                if (!validarCampoContacto(campo)) {
                    todosValidos = false;
                }
            });

            if (!todosValidos) {
                mostrarToast("warning", "Campos incompletos", "Por favor completa todos los campos obligatorios.");
                return;
            }

            const emailVal = correo.value.trim();
            if (!REGEX_EMAIL_CONTACTO.test(emailVal)) {
                correo.classList.add("is-invalid");
                correo.classList.remove("is-valid");
                mostrarToast("warning", "Correo inválido", "Por favor ingresa un correo electrónico válido.");
                correo.focus();
                return;
            }

            if (!REGEX_TELEFONO_CONTACTO.test(telefono.value.trim())) {
                telefono.classList.add("is-invalid");
                telefono.classList.remove("is-valid");
                mostrarToast("warning", "Teléfono inválido", "Ingresa un número de teléfono válido de 8 dígitos (Ej. 7000-0000).");
                telefono.focus();
                return;
            }

            if (mensaje.value.trim().length < 10) {
                mensaje.classList.add("is-invalid");
                mensaje.classList.remove("is-valid");
                mostrarToast("warning", "Mensaje muy corto", "Por favor escribe un mensaje de al menos 10 caracteres.");
                mensaje.focus();
                return;
            }

            let redirigiendo = false;

            try {
                const datos = new FormData(formulario);
                datos.append("opcion", "enviar");

                if (overlayContacto) {
                    overlayContacto.classList.remove("d-none");
                }
                if (btnEnviar) {
                    btnEnviar.disabled = true;
                    btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
                }

                const respuesta = await fetch(formulario.action, {
                    method: "POST",
                    body: datos
                });

                if (!respuesta.ok) {
                    throw new Error("Error HTTP: " + respuesta.status);
                }

                const resultado = await respuesta.json();

                if (resultado.estado) {
                    redirigiendo = true;
                    mostrarToast("success", "¡Mensaje enviado!", resultado.mensaje || "Tu mensaje ha sido enviado.");
                    setTimeout(() => {
                        window.location.href = "gracias.php";
                    }, 900);
                } else {
                    mostrarToast("error", "No se pudo enviar", resultado.mensaje || "Ocurrió un error al enviar tu mensaje.");
                }
            } catch (error) {
                mostrarToast("error", "Error de conexión", error.message || "No se pudo comunicar con el servidor.");
            } finally {
                if (!redirigiendo) {
                    if (overlayContacto) {
                        overlayContacto.classList.add("d-none");
                    }
                    if (btnEnviar) {
                        btnEnviar.disabled = false;
                        btnEnviar.textContent = "Enviar Mensaje";
                    }
                }
            }
        });
    }
});
