const REGEX_PASSWORD = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s]).{8,}$/;
const REGEX_EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const REGEX_TELEFONO = /^\d{4}-\d{4}$/;

let modalInstance = null;
let callbackModal = null;

function mostrarModal(tipo, titulo, mensaje, onCerrar = null) {
    const modalEl = document.getElementById("modalPPI");
    const tituloEl = document.getElementById("modalPPITitulo");
    const cuerpoEl = document.getElementById("modalPPICuerpo");
    const btnAccion = document.getElementById("btnModalPPIAccion");

    if (!modalEl || !tituloEl || !cuerpoEl || !btnAccion) return;

    callbackModal = onCerrar;

    let icon = "";
    btnAccion.className = "btn btn-ppi-modal btn-sm";

    if (tipo === "success") {
        icon = '<i class="fas fa-check-circle fs-5" style="color: #658807;"></i>';
        btnAccion.textContent = "Continuar";
    } else if (tipo === "danger" || tipo === "error") {
        icon = '<i class="fas fa-times-circle fs-5" style="color: #d8685d;"></i>';
        btnAccion.className = "btn btn-ppi-modal btn-danger btn-sm";
        btnAccion.textContent = "Entendido";
    } else {
        icon = '<i class="fas fa-exclamation-triangle text-warning fs-5"></i>';
        btnAccion.className = "btn btn-ppi-modal btn-warning btn-sm";
        btnAccion.textContent = "Entendido";
    }

    tituloEl.innerHTML = `${icon} <span>${titulo}</span>`;
    cuerpoEl.innerHTML = `<p class="mb-0 text-second-ppi">${mensaje}</p>`;

    if (!modalInstance) {
        modalInstance = new bootstrap.Modal(modalEl);
        modalEl.addEventListener("hidden.bs.modal", () => {
            if (typeof callbackModal === "function") {
                const cb = callbackModal;
                callbackModal = null;
                cb();
            }
        });
    }

    modalInstance.show();
}

function validarCampo(campo) {
    if (!campo) return true;
    const valor = campo.value.trim();
    let valido = valor !== "" && valor !== "0";

    if (valido && campo.id === "correo") {
        valido = REGEX_EMAIL.test(valor);
    } else if (valido && campo.id === "telefono") {
        valido = REGEX_TELEFONO.test(valor);
    } else if (valido && campo.id === "contrasenia") {
        valido = REGEX_PASSWORD.test(campo.value);
    } else if (valido && campo.id === "confirmar_contrasenia") {
        const pass = document.getElementById("contrasenia");
        valido = pass ? campo.value === pass.value : true;
    }

    campo.classList.toggle("is-invalid", !valido);
    campo.classList.toggle("is-valid", valido);
    return valido;
}

document.addEventListener("DOMContentLoaded", () => {
    const formulario = document.getElementById("formRegistro");
    const btnRegistrar = document.getElementById("btnRegistrar");

    const nombre = document.getElementById("nombre");
    const apellido = document.getElementById("apellido");
    const correo = document.getElementById("correo");
    const telefono = document.getElementById("telefono");
    const ubicacion = document.getElementById("ubicacion");
    const username = document.getElementById("username");
    const contrasenia = document.getElementById("contrasenia");
    const confirmarContrasenia = document.getElementById("confirmar_contrasenia");

    const camposObligatorios = [nombre, apellido, correo, telefono, ubicacion, username, contrasenia, confirmarContrasenia];

    camposObligatorios.forEach(function (campo) {
        if (campo) {
            campo.addEventListener("blur", function () {
                validarCampo(campo);
            });
            campo.addEventListener("input", function () {
                if (campo.classList.contains("is-invalid") || campo.classList.contains("is-valid")) {
                    validarCampo(campo);
                }
                if (campo === contrasenia && confirmarContrasenia && confirmarContrasenia.value !== "") {
                    validarCampo(confirmarContrasenia);
                }
            });
        }
    });

    const camposMayusculas = [nombre, apellido];
    camposMayusculas.forEach(function (campo) {
        if (campo) {
            campo.addEventListener("input", function () {
                campo.value = campo.value.toUpperCase();
            });
        }
    });

    if (telefono) {
        telefono.addEventListener("input", function () {
            let valor = telefono.value.replace(/\D/g, "");
            valor = valor.substring(0, 8);
            if (valor.length > 4) {
                valor = valor.substring(0, 4) + "-" + valor.substring(4);
            }
            telefono.value = valor;
        });
    }

    if (username) {
        username.addEventListener("change", async function () {
            const valor = username.value.trim();
            if (valor.length >= 3) {
                try {
                    const datos = new FormData();
                    datos.append("opcion", "consultarUsername");
                    datos.append("username", valor);

                    const respuesta = await fetch(formulario.action, {
                        method: "POST",
                        body: datos
                    });
                    const res = await respuesta.json();
                    if (res.estado) {
                        username.classList.add("is-invalid");
                        username.classList.remove("is-valid");
                        mostrarModal("warning", "Usuario no disponible", res.mensaje || "Este nombre de usuario ya está en uso");
                    }
                } catch (e) {
                    console.error(e);
                }
            }
        });
    }

    if (correo) {
        correo.addEventListener("change", async function () {
            const valor = correo.value.trim();
            if (valor.includes("@") && valor.includes(".")) {
                try {
                    const datos = new FormData();
                    datos.append("opcion", "consultarCorreo");
                    datos.append("correo", valor);

                    const respuesta = await fetch(formulario.action, {
                        method: "POST",
                        body: datos
                    });
                    const res = await respuesta.json();
                    if (res.estado) {
                        correo.classList.add("is-invalid");
                        correo.classList.remove("is-valid");
                        mostrarModal("warning", "Correo no disponible", res.mensaje || "Este correo electrónico ya está registrado");
                    }
                } catch (e) {
                    console.error(e);
                }
            }
        });
    }

    const toggleButtons = document.querySelectorAll(".btn-toggle-eye");
    toggleButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            const targetId = btn.getAttribute("data-target");
            const targetInput = document.getElementById(targetId);
            const icon = btn.querySelector("i");

            if (targetInput) {
                const estaOculto = targetInput.classList.contains("font-password-masked");
                if (estaOculto) {
                    targetInput.classList.remove("font-password-masked");
                    icon.classList.remove("bi-eye");
                    icon.classList.add("bi-eye-slash");
                } else {
                    targetInput.classList.add("font-password-masked");
                    icon.classList.remove("bi-eye-slash");
                    icon.classList.add("bi-eye");
                }
            }
        });
    });

    if (formulario) {
        formulario.addEventListener("submit", async function (e) {
            e.preventDefault();

            let primerVacio = null;
            camposObligatorios.forEach(function (campo) {
                if (!campo) return;
                const valor = campo.value.trim();
                if (valor === "" || valor === "0") {
                    campo.classList.add("is-invalid");
                    campo.classList.remove("is-valid");
                    if (!primerVacio) primerVacio = campo;
                }
            });

            if (primerVacio) {
                mostrarModal("warning", "Campos incompletos", "Por favor completa todos los campos obligatorios.");
                primerVacio.focus();
                return;
            }

            if (!REGEX_EMAIL.test(correo.value.trim())) {
                correo.classList.add("is-invalid");
                correo.classList.remove("is-valid");
                mostrarModal("warning", "Correo inválido", "Por favor ingresa un correo electrónico válido (ejemplo: nombre@correo.com).");
                correo.focus();
                return;
            }

            if (!REGEX_TELEFONO.test(telefono.value.trim())) {
                telefono.classList.add("is-invalid");
                telefono.classList.remove("is-valid");
                mostrarModal("warning", "Teléfono inválido", "El teléfono debe tener el formato 0000-0000 (8 dígitos).");
                telefono.focus();
                return;
            }

            if (!REGEX_PASSWORD.test(contrasenia.value)) {
                contrasenia.classList.add("is-invalid");
                contrasenia.classList.remove("is-valid");
                mostrarModal("warning", "Contraseña débil", "La contraseña debe tener al menos 8 caracteres, incluir mayúscula, minúscula, número y algún símbolo (como ., :, -, _, @, $, etc.).");
                contrasenia.focus();
                return;
            }

            if (contrasenia.value !== confirmarContrasenia.value) {
                confirmarContrasenia.classList.add("is-invalid");
                confirmarContrasenia.classList.remove("is-valid");
                mostrarModal("warning", "Contraseñas no coinciden", "Las contraseñas ingresadas no son iguales. Por favor verifica.");
                confirmarContrasenia.focus();
                return;
            }

            let redirigiendo = false;
            const overlayRegistro = document.getElementById("overlayRegistro");

            try {
                const datos = new FormData(formulario);
                datos.append("opcion", "guardar");

                if (overlayRegistro) {
                    overlayRegistro.classList.remove("d-none");
                }
                if (btnRegistrar) {
                    btnRegistrar.disabled = true;
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
                    const emailDestino = encodeURIComponent(resultado.correo || correo.value.trim());
                    window.location.href = `verificar-codigo.php?correo=${emailDestino}`;

                } else {
                    mostrarModal("danger", "Error", resultado.mensaje || "No se pudo completar el registro.");
                }
            } catch (error) {
                const esErrorRed = !navigator.onLine
                    || error instanceof TypeError
                    || /failed to fetch|networkerror|load failed/i.test(error?.message || "");
                const tituloError = esErrorRed ? "Sin conexión" : "Error";
                const mensajeError = esErrorRed
                    ? "No se pudo conectar con el servidor. Verifica tu conexión a internet e intenta de nuevo."
                    : "Ocurrió un problema al procesar tu solicitud. Intenta de nuevo en unos momentos.";
                mostrarModal("danger", tituloError, mensajeError);
            } finally {
                if (!redirigiendo) {
                    if (overlayRegistro) {
                        overlayRegistro.classList.add("d-none");
                    }
                    if (btnRegistrar) {
                        btnRegistrar.disabled = false;
                    }
                }
            }
        });
    }
});
