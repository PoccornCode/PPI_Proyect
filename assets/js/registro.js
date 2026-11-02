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
    const vacio = valor === "" || valor === "0";
    campo.classList.toggle("is-invalid", vacio);
    campo.classList.toggle("is-valid", !vacio);
    return !vacio;
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
                if (campo.classList.contains("is-invalid")) {
                    validarCampo(campo);
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
                        SwalPPI.fire({
                            position: "center",
                            icon: "warning",
                            title: "Usuario no disponible",
                            text: res.mensaje,
                            showConfirmButton: true
                        });
                    }
                } catch (e) {
                    console.error("Error al validar username", e);
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
                        SwalPPI.fire({
                            position: "center",
                            icon: "warning",
                            title: "Correo no disponible",
                            text: res.mensaje,
                            showConfirmButton: true
                        });
                    }
                } catch (e) {
                    console.error("Error al validar correo", e);
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
                    text: "Por favor completa todos los campos obligatorios."
                });
                return;
            }

            if (contrasenia.value.length < 8) {
                contrasenia.classList.add("is-invalid");
                SwalPPI.fire({
                    icon: "warning",
                    title: "Contraseña corta",
                    text: "La contraseña debe tener un mínimo de 8 caracteres."
                });
                contrasenia.focus();
                return;
            }

            if (contrasenia.value !== confirmarContrasenia.value) {
                confirmarContrasenia.classList.add("is-invalid");
                SwalPPI.fire({
                    icon: "warning",
                    title: "Contraseñas no coinciden",
                    text: "Las contraseñas ingresadas no son iguales. Por favor verifica."
                });
                confirmarContrasenia.focus();
                return;
            }

            try {
                const datos = new FormData(formulario);
                datos.append("opcion", "guardar");

                if (btnRegistrar) {
                    btnRegistrar.disabled = true;
                    btnRegistrar.textContent = "Procesando...";
                }

                SwalPPI.fire({
                    title: "Espere...",
                    text: "Registrando usuario",
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
                        title: "¡Registro exitoso!",
                        text: resultado.mensaje || "Tu cuenta ha sido creada exitosamente.",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.href = "login.php";
                    });
                } else {
                    SwalPPI.fire({
                        position: "center",
                        icon: "error",
                        title: "Error",
                        text: resultado.mensaje || "No se pudo completar el registro.",
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
                if (btnRegistrar) {
                    btnRegistrar.disabled = false;
                    btnRegistrar.textContent = "Registrarse";
                }
            }
        });
    }
});
