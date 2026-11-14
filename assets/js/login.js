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
        icon = '<i class="fas fa-times-circle text-danger fs-5"></i>';
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
    const vacio = valor === "";
    campo.classList.toggle("is-invalid", vacio);
    campo.classList.toggle("is-valid", !vacio);
    return !vacio;
}

document.addEventListener("DOMContentLoaded", () => {
    const formulario = document.getElementById("formLogin");
    const btnLogin = document.getElementById("btnLogin");

    const username = document.getElementById("username");
    const contrasenia = document.getElementById("contrasenia");

    const camposObligatorios = [username, contrasenia];

    camposObligatorios.forEach(function (campo) {
        if (campo) {
            campo.addEventListener("blur", function () {
                validarCampo(campo);
            });
            campo.addEventListener("input", function () {
                if (campo.classList.contains("is-invalid")) {
                    validarCampo(campo);
                }
            });
        }
    });

    const toggleButtons = document.querySelectorAll(".btn-toggle-eye");
    toggleButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            const targetId = btn.getAttribute("data-target");
            const targetInput = document.getElementById(targetId);
            const icon = btn.querySelector("i");

            if (targetInput) {
                if (targetInput.type === "password") {
                    targetInput.type = "text";
                    icon.classList.remove("bi-eye");
                    icon.classList.add("bi-eye-slash");
                } else {
                    targetInput.type = "password";
                    icon.classList.remove("bi-eye-slash");
                    icon.classList.add("bi-eye");
                }
            }
        });
    });

    if (formulario) {
        formulario.addEventListener("submit", async function (e) {
            e.preventDefault();

            let todosValidos = true;
            camposObligatorios.forEach(function (campo) {
                if (!validarCampo(campo)) {
                    todosValidos = false;
                }
            });

            if (!todosValidos) {
                mostrarModal("warning", "Campos incompletos", "Por favor ingresa tu usuario y contraseña.");
                return;
            }

            try {
                const datos = new FormData(formulario);
                datos.append("opcion", "login");

                if (btnLogin) {
                    btnLogin.disabled = true;
                    btnLogin.textContent = "Procesando...";
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
                    mostrarModal("success", "¡Bienvenido!", resultado.mensaje || "Inicio de sesión exitoso.", () => {
                        window.location.href = resultado.redirect || "../../index.php";
                    });
                } else {
                    contrasenia.value = "";
                    contrasenia.classList.remove("is-valid");
                    mostrarModal("danger", "Error", resultado.mensaje || "No se pudo iniciar sesión.");
                }
            } catch (error) {
                mostrarModal("danger", "Error", error.message || "Error al procesar la solicitud con el servidor.");
            } finally {
                if (btnLogin) {
                    btnLogin.disabled = false;
                    btnLogin.textContent = "Iniciar sesión";
                }
            }
        });
    }
});
