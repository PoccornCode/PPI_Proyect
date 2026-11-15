document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("formVerificarCodigo");
    const inputs = document.querySelectorAll(".otp-input");
    const inputCorreo = document.getElementById("inputCorreo");
    const btnVerificar = document.getElementById("btnVerificar");
    const btnReenviar = document.getElementById("btnReenviar");
    const btnReenviarTexto = document.getElementById("btnReenviarTexto");

    inputs.forEach((input, index) => {
        input.addEventListener("input", (e) => {
            const val = e.target.value.replace(/\D/g, "");
            e.target.value = val;

            if (val.length === 1) {
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            }
            inputs.forEach(inp => inp.classList.remove("is-invalid"));
        });

        input.addEventListener("keydown", (e) => {
            if (e.key === "Backspace" && !e.target.value && index > 0) {
                inputs[index - 1].focus();
            }
        });

        input.addEventListener("paste", (e) => {
            e.preventDefault();
            const pasteData = (e.clipboardData || window.clipboardData).getData("text").trim().replace(/\D/g, "");
            if (pasteData.length > 0) {
                inputs.forEach((inp, i) => {
                    inp.value = pasteData[i] || "";
                });
                const nextFocus = Math.min(pasteData.length, inputs.length - 1);
                inputs[nextFocus].focus();
            }
        });
    });

    function obtenerCodigoCompleto() {
        let code = "";
        inputs.forEach(inp => code += inp.value.trim());
        return code;
    }

    let countdownInterval = null;
    function iniciarCooldownReenvio(segundos = 60) {
        if (!btnReenviar || !btnReenviarTexto) return;
        btnReenviar.disabled = true;
        let rest = segundos;

        clearInterval(countdownInterval);
        countdownInterval = setInterval(() => {
            btnReenviarTexto.textContent = `Reenviar en ${rest}s`;
            rest--;
            if (rest < 0) {
                clearInterval(countdownInterval);
                btnReenviar.disabled = false;
                btnReenviarTexto.textContent = "Reenviar código";
            }
        }, 1000);
    }

    // Envio del formulario de verificacion
    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            inputs.forEach(inp => inp.classList.remove("is-invalid"));

            const codigo = obtenerCodigoCompleto();
            const correo = inputCorreo ? inputCorreo.value.trim() : "";

            if (codigo.length !== 6) {
                inputs.forEach(inp => inp.classList.add("is-invalid"));
                if (typeof mostrarToast === "function") {
                    mostrarToast("warning", "Código Incompleto", "Por favor ingresa los 6 dígitos del código de verificación.");
                }
                return;
            }

            if (!correo) {
                if (typeof mostrarToast === "function") {
                    mostrarToast("error", "Error", "No se especificó el correo electrónico para verificar.");
                }
                return;
            }

            try {
                if (btnVerificar) {
                    btnVerificar.disabled = true;
                    btnVerificar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verificando...';
                }

                const formData = new FormData();
                formData.append("opcion", "verificarCodigo");
                formData.append("correo", correo);
                formData.append("codigo", codigo);

                const response = await fetch("../../controllers/registro_controller.php", {
                    method: "POST",
                    body: formData
                });

                const res = await response.json();

                if (res.estado) {
                    inputs.forEach(inp => inp.disabled = true);

                    if (typeof mostrarToast === "function") {
                        mostrarToast("success", "¡Cuenta Verificada!", res.mensaje || "Cuenta activada con éxito.");
                    }

                    setTimeout(() => {
                        window.location.href = "login.php?verificado=1";
                    }, 1200);
                } else {
                    inputs.forEach(inp => inp.classList.add("is-invalid"));
                    if (typeof mostrarToast === "function") {
                        mostrarToast("error", "Código Inválido", res.mensaje || "El código ingresado no es válido.");
                    }
                }
            } catch (err) {
                if (typeof mostrarToast === "function") {
                    mostrarToast("error", "Error de Conexión", "No se pudo comunicar con el servidor.");
                }
            } finally {
                if (btnVerificar) {
                    btnVerificar.disabled = false;
                    btnVerificar.textContent = "Verificar y continuar";
                }
            }
        });
    }

    // Botón de reenvío
    if (btnReenviar) {
        btnReenviar.addEventListener("click", async () => {
            const correo = inputCorreo ? inputCorreo.value.trim() : "";

            if (!correo) {
                if (typeof mostrarToast === "function") {
                    mostrarToast("error", "Error", "No se puede reenviar sin una dirección de correo válida.");
                }
                return;
            }

            try {
                btnReenviar.disabled = true;
                btnReenviarTexto.textContent = "Enviando...";

                const formData = new FormData();
                formData.append("opcion", "reenviarCodigo");
                formData.append("correo", correo);

                const response = await fetch("../../controllers/registro_controller.php", {
                    method: "POST",
                    body: formData
                });

                const res = await response.json();

                if (res.estado) {
                    if (typeof mostrarToast === "function") {
                        mostrarToast("success", "Código Enviado", res.mensaje || "Se ha enviado un nuevo código a tu correo.");
                    }
                    iniciarCooldownReenvio(60);
                } else {
                    if (typeof mostrarToast === "function") {
                        mostrarToast("error", "Error", res.mensaje || "No se pudo reenviar el código.");
                    }
                    btnReenviar.disabled = false;
                    btnReenviarTexto.textContent = "Reenviar código";
                }
            } catch (err) {
                if (typeof mostrarToast === "function") {
                    mostrarToast("error", "Error", "Error de comunicación con el servidor.");
                }
                btnReenviar.disabled = false;
                btnReenviarTexto.textContent = "Reenviar código";
            }
        });
    }
});
