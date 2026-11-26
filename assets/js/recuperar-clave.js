const REGEX_PASSWORD = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s]).{8,}$/;

document.addEventListener("DOMContentLoaded", () => {
    const pasoSolicitar = document.getElementById("pasoSolicitar");
    const pasoRestablecer = document.getElementById("pasoRestablecer");

    const formSolicitar = document.getElementById("formSolicitarCodigo");
    const formRestablecer = document.getElementById("formRestablecerClave");

    const inputCorreo = document.getElementById("correo");
    const lblCorreoDestino = document.getElementById("lblCorreoDestino");
    const btnEnviarCodigo = document.getElementById("btnEnviarCodigo");
    const btnRestablecer = document.getElementById("btnRestablecer");
    const btnReenviar = document.getElementById("btnReenviar");
    const btnReenviarTexto = document.getElementById("btnReenviarTexto");
    const btnCambiarCorreo = document.getElementById("btnCambiarCorreo");

    const otpInputs = document.querySelectorAll(".otp-input");
    const nuevaContrasenia = document.getElementById("nueva_contrasenia");
    const confirmarContrasenia = document.getElementById("confirmar_contrasenia");
    const overlayRecuperacion = document.getElementById("overlayRecuperacion");

    let correoActual = "";
    let countdownInterval = null;

    const urlParams = new URLSearchParams(window.location.search);
    const correoParam = (urlParams.get("correo") || "").trim();
    const codigoParam = (urlParams.get("codigo") || "").trim().replace(/\D/g, "");

    if (correoParam && /^\d{6}$/.test(codigoParam) && pasoSolicitar && pasoRestablecer) {
        correoActual = correoParam;
        if (inputCorreo) inputCorreo.value = correoParam;
        if (lblCorreoDestino) lblCorreoDestino.textContent = correoParam;

        otpInputs.forEach((inp, idx) => {
            inp.value = codigoParam[idx] || "";
        });

        pasoSolicitar.classList.add("d-none");
        pasoRestablecer.classList.remove("d-none");
        if (nuevaContrasenia) nuevaContrasenia.focus();
    }

    // Manejo de casillas OTP (auto-avance, retroceso y pegado)
    otpInputs.forEach((input, index) => {
        input.addEventListener("input", (e) => {
            const val = e.target.value.replace(/\D/g, "");
            e.target.value = val;
            if (val.length === 1 && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
            otpInputs.forEach(inp => inp.classList.remove("is-invalid"));
        });

        input.addEventListener("keydown", (e) => {
            if (e.key === "Backspace" && !e.target.value && index > 0) {
                otpInputs[index - 1].focus();
            }
        });

        input.addEventListener("paste", (e) => {
            e.preventDefault();
            const pasteData = (e.clipboardData || window.clipboardData).getData("text").trim().replace(/\D/g, "");
            if (pasteData.length > 0) {
                otpInputs.forEach((inp, i) => {
                    inp.value = pasteData[i] || "";
                });
                const nextFocus = Math.min(pasteData.length, otpInputs.length - 1);
                otpInputs[nextFocus].focus();
            }
        });
    });

    function obtenerCodigoCompleto() {
        let code = "";
        otpInputs.forEach(inp => code += inp.value.trim());
        return code;
    }

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

    // Mostrar / ocultar contraseñas
    document.querySelectorAll(".btn-toggle-eye").forEach(btn => {
        btn.addEventListener("click", () => {
            const targetInput = document.getElementById(btn.getAttribute("data-target"));
            const icon = btn.querySelector("i");
            if (!targetInput) return;

            if (targetInput.type === "password") {
                targetInput.type = "text";
                icon.classList.replace("bi-eye", "bi-eye-slash");
            } else {
                targetInput.type = "password";
                icon.classList.replace("bi-eye-slash", "bi-eye");
            }
        });
    });

    // PASO 1: Solicitar código al correo
    if (formSolicitar) {
        formSolicitar.addEventListener("submit", async (e) => {
            e.preventDefault();
            const correo = inputCorreo.value.trim();

            if (!correo) {
                inputCorreo.classList.add("is-invalid");
                mostrarToast("warning", "Campo requerido", "Ingresa tu correo electrónico.");
                return;
            }

            try {
                if (overlayRecuperacion) overlayRecuperacion.classList.remove("d-none");
                btnEnviarCodigo.disabled = true;
                btnEnviarCodigo.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando código...';

                const datos = new FormData();
                datos.append("opcion", "solicitarCodigo");
                datos.append("correo", correo);

                const resp = await fetch(formSolicitar.action, { method: "POST", body: datos });
                const res = await resp.json();

                if (res.estado) {
                    correoActual = correo;
                    lblCorreoDestino.textContent = correoActual;
                    pasoSolicitar.classList.add("d-none");
                    pasoRestablecer.classList.remove("d-none");
                    otpInputs[0].focus();
                    iniciarCooldownReenvio(60);
                    mostrarToast("success", "Código enviado", res.mensaje || "Revisa tu bandeja de entrada.");
                } else {
                    inputCorreo.classList.add("is-invalid");
                    mostrarToast("error", "No se pudo enviar", res.mensaje || "Verifica el correo ingresado.");
                }
            } catch (err) {
                mostrarToast("error", "Error de conexión", "No se pudo comunicar con el servidor.");
            } finally {
                if (overlayRecuperacion) overlayRecuperacion.classList.add("d-none");
                btnEnviarCodigo.disabled = false;
                btnEnviarCodigo.textContent = "Enviar código de recuperación";
            }
        });
    }

    // PASO 2: Verificar OTP y guardar nueva contraseña
    if (formRestablecer) {
        formRestablecer.addEventListener("submit", async (e) => {
            e.preventDefault();
            otpInputs.forEach(inp => inp.classList.remove("is-invalid"));
            nuevaContrasenia.classList.remove("is-invalid");
            confirmarContrasenia.classList.remove("is-invalid");

            const codigo = obtenerCodigoCompleto();

            if (codigo.length !== 6) {
                otpInputs.forEach(inp => inp.classList.add("is-invalid"));
                mostrarToast("warning", "Código incompleto", "Ingresa los 6 dígitos enviados a tu correo.");
                return;
            }

            if (!REGEX_PASSWORD.test(nuevaContrasenia.value)) {
                nuevaContrasenia.classList.add("is-invalid");
                mostrarToast("warning", "Contraseña débil", "Debe tener al menos 8 caracteres, mayúscula, minúscula, número y símbolo.");
                nuevaContrasenia.focus();
                return;
            }

            if (nuevaContrasenia.value !== confirmarContrasenia.value) {
                confirmarContrasenia.classList.add("is-invalid");
                mostrarToast("warning", "No coinciden", "Las contraseñas ingresadas no son iguales.");
                confirmarContrasenia.focus();
                return;
            }

            try {
                btnRestablecer.disabled = true;
                btnRestablecer.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Actualizando...';

                const datos = new FormData();
                datos.append("opcion", "restablecer");
                datos.append("correo", correoActual);
                datos.append("codigo", codigo);
                datos.append("nueva_contrasenia", nuevaContrasenia.value);

                const resp = await fetch(formRestablecer.action, { method: "POST", body: datos });
                const res = await resp.json();

                if (res.estado) {
                    mostrarToast("success", "¡Contraseña actualizada!", res.mensaje);
                    setTimeout(() => {
                        window.location.href = "login.php?recuperado=1";
                    }, 1300);
                } else {
                    otpInputs.forEach(inp => inp.classList.add("is-invalid"));
                    mostrarToast("error", "Error", res.mensaje || "No se pudo restablecer la contraseña.");
                }
            } catch (err) {
                mostrarToast("error", "Error de conexión", "No se pudo comunicar con el servidor.");
            } finally {
                btnRestablecer.disabled = false;
                btnRestablecer.textContent = "Cambiar contraseña";
            }
        });
    }

    // Reenviar código
    if (btnReenviar) {
        btnReenviar.addEventListener("click", async () => {
            if (!correoActual) return;

            try {
                if (overlayRecuperacion) overlayRecuperacion.classList.remove("d-none");
                btnReenviar.disabled = true;
                btnReenviarTexto.textContent = "Enviando...";

                const datos = new FormData();
                datos.append("opcion", "reenviarCodigo");
                datos.append("correo", correoActual);

                const resp = await fetch(formSolicitar.action, { method: "POST", body: datos });
                const res = await resp.json();

                if (res.estado) {
                    mostrarToast("success", "Nuevo código enviado", res.mensaje);
                    iniciarCooldownReenvio(60);
                } else {
                    mostrarToast("error", "Error", res.mensaje);
                    btnReenviar.disabled = false;
                    btnReenviarTexto.textContent = "Reenviar código";
                }
            } catch (err) {
                mostrarToast("error", "Error", "No se pudo reenviar el código.");
                btnReenviar.disabled = false;
                btnReenviarTexto.textContent = "Reenviar código";
            } finally {
                if (overlayRecuperacion) overlayRecuperacion.classList.add("d-none");
            }
        });
    }

    // Volver al paso 1 para corregir el correo
    if (btnCambiarCorreo) {
        btnCambiarCorreo.addEventListener("click", () => {
            clearInterval(countdownInterval);
            otpInputs.forEach(inp => { inp.value = ""; inp.classList.remove("is-invalid"); });
            nuevaContrasenia.value = "";
            confirmarContrasenia.value = "";
            pasoRestablecer.classList.add("d-none");
            pasoSolicitar.classList.remove("d-none");
            inputCorreo.focus();
        });
    }
});