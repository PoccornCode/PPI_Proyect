const SwalPPI = Swal.mixin({
    customClass: {
        popup: "swal-ppi-popup",
        title: "swal-ppi-title",
        htmlContainer: "swal-ppi-html",
        confirmButton: "btn swal-ppi-confirm-btn"
    },
    buttonsStyling: false
});

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
                SwalPPI.fire({
                    icon: "warning",
                    title: "Campos incompletos",
                    text: "Por favor ingresa tu usuario y contraseña."
                });
                return;
            }

            try {
                const datos = new FormData(formulario);
                datos.append("opcion", "login");

                if (btnLogin) {
                    btnLogin.disabled = true;
                    btnLogin.textContent = "Procesando...";
                }

                SwalPPI.fire({
                    title: "Espere...",
                    text: "Verificando credenciales",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });

                const respuesta = await fetch(formulario.action, {
                    method: "POST",
                    body: datos
                });

                if (!respuesta.ok) {
                    throw new Error("Error HTTP: " + respuesta.status);
                }

                const resultado = await respuesta.json();
                SwalPPI.close();

                if (resultado.estado) {
                    SwalPPI.fire({
                        position: "center",
                        icon: "success",
                        title: "¡Bienvenido!",
                        text: resultado.mensaje || "Inicio de sesión exitoso.",
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = resultado.redirect || "../../index.php";
                    });
                } else {
                    contrasenia.value = "";
                    SwalPPI.fire({
                        position: "center",
                        icon: "error",
                        title: "Error",
                        text: resultado.mensaje || "No se pudo iniciar sesión.",
                        showConfirmButton: true
                    });
                }
            } catch (error) {
                SwalPPI.close();
                SwalPPI.fire({
                    icon: "error",
                    title: "Error",
                    text: error.message || "Error al procesar la solicitud con el servidor."
                });
            } finally {
                if (btnLogin) {
                    btnLogin.disabled = false;
                    btnLogin.textContent = "Iniciar sesión";
                }
            }
        });
    }
});