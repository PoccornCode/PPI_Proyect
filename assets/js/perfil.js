// Misma regla que el registro (assets/js/registro.js) y la recuperación de contraseña
const REGEX_PASSWORD = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s]).{8,}$/;
const MSG_PASSWORD = "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo.";

let modalAlertaInstance = null;
let callbackModalAlerta = null;

function mostrarModal(tipo, titulo, mensaje, onCerrar = null) {
    const modalEl = document.getElementById("modalPPI");
    const tituloEl = document.getElementById("modalPPITitulo");
    const cuerpoEl = document.getElementById("modalPPICuerpo");
    const btnAccion = document.getElementById("btnModalPPIAccion");

    if (!modalEl || !tituloEl || !cuerpoEl || !btnAccion) return;

    callbackModalAlerta = onCerrar;

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

    if (!modalAlertaInstance) {
        modalAlertaInstance = new bootstrap.Modal(modalEl);
        modalEl.addEventListener("hidden.bs.modal", () => {
            if (typeof callbackModalAlerta === "function") {
                const cb = callbackModalAlerta;
                callbackModalAlerta = null;
                cb();
            }
        });
    }

    modalAlertaInstance.show();
}

document.addEventListener("DOMContentLoaded", () => {
    // Temporizador y actualización en vivo para "Mis subastas activas"
    const contadoresSubasta = Array.from(document.querySelectorAll("[data-segundos-restantes]")).map((el) => {
        const card = el.closest("[data-subasta-id]");
        const seg = parseInt(el.dataset.segundosRestantes, 10);
        return {
            id: card ? String(card.dataset.subastaId) : null,
            el,
            fin: Date.now() + (Number.isFinite(seg) && seg > 0 ? seg * 1000 : 0)
        };
    });

    if (contadoresSubasta.length > 0) {
        const pad = (n) => String(n).padStart(2, "0");
        const formatearRestante = (seg) => {
            if (seg <= 0) return "Finalizada";
            const dias = Math.floor(seg / 86400);
            const horas = Math.floor((seg % 86400) / 3600);
            const minutos = Math.floor((seg % 3600) / 60);
            const segundos = seg % 60;
            if (dias > 0) return `Termina en ${dias} d ${pad(horas)} h`;
            if (horas > 0) return `Termina en ${pad(horas)} h ${pad(minutos)} min`;
            return `Termina en ${pad(minutos)} min ${pad(segundos)} s`;
        };

        const actualizarContadores = () => {
            const ahora = Date.now();
            let activos = 0;
            contadoresSubasta.forEach((item) => {
                const rest = Math.max(0, Math.floor((item.fin - ahora) / 1000));
                item.el.textContent = formatearRestante(rest);
                if (rest > 0) activos++;
            });
            return activos > 0;
        };

        if (actualizarContadores()) {
            const timerId = setInterval(() => {
                if (!actualizarContadores()) {
                    clearInterval(timerId);
                }
            }, 1000);
        }

        // Refrescar precio y pujas cada 10 segundos sin recargar
        const seccionSubastas = document.getElementById("seccionMisSubastas");
        const urlSubastas = seccionSubastas ? seccionSubastas.dataset.subastasUrl : "";
        if (urlSubastas) {
            let consultando = false;
            const refrescarPujas = async () => {
                if (document.hidden || consultando) return;
                consultando = true;
                try {
                    const fd = new FormData();
                    fd.append("opcion", "subastasActivas");
                    const resp = await fetch(urlSubastas, { method: "POST", body: fd });
                    const data = await resp.json().catch(() => null);
                    if (!data || data.auth_required) {
                        clearInterval(pollId);
                        return;
                    }
                    if (!data.estado || !Array.isArray(data.subastas)) return;

                    const ahora = Date.now();
                    data.subastas.forEach((s) => {
                        const card = seccionSubastas.querySelector(`[data-subasta-id="${s.id_subasta}"]`);
                        if (!card) return;

                        const precioEl = card.querySelector("[data-subasta-precio]");
                        const nuevoPrecio = "$" + Number(s.precio_actual).toLocaleString("en-US", {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        if (precioEl && precioEl.textContent !== nuevoPrecio) {
                            precioEl.textContent = nuevoPrecio;
                            if (typeof precioEl.animate === "function") {
                                precioEl.animate(
                                    [{ color: "#94aa34", opacity: 0.7 }, { opacity: 1 }],
                                    { duration: 600, easing: "ease-out" }
                                );
                            }
                        }

                        const pujasEl = card.querySelector("[data-subasta-pujas]");
                        if (pujasEl) {
                            const n = Number(s.total_pujas) || 0;
                            pujasEl.textContent = `${n} ${n === 1 ? "puja" : "pujas"}`;
                        }

                        const itemTimer = contadoresSubasta.find((t) => t.id === String(s.id_subasta));
                        if (itemTimer) {
                            itemTimer.fin = ahora + Math.max(0, Number(s.segundos_restantes) || 0) * 1000;
                        }
                    });
                } catch (_) {
                    // Silencioso para no interrumpir al usuario si hay un corte momentáneo
                } finally {
                    consultando = false;
                }
            };

            const pollId = setInterval(refrescarPujas, 10000);
        }
    }

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

    let pausadoPorAlerta = false;
    let campoParaEnfocar = null;

    const getModalClave = () => bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);

    function abrirAlertaSobreModal(tipo, titulo, mensaje, campoFocus = null) {
        pausadoPorAlerta = true;
        campoParaEnfocar = campoFocus;
        const instancia = getModalClave();
        modalEl.addEventListener("hidden.bs.modal", () => {
            mostrarModal(tipo, titulo, mensaje, () => {
                getModalClave().show();
            });
        }, { once: true });
        instancia.hide();
    }

    // limpiar cajas de contras
    modalEl.addEventListener("hidden.bs.modal", () => {
        if (pausadoPorAlerta) return;
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

    modalEl.addEventListener("shown.bs.modal", () => {
        if (pausadoPorAlerta) {
            pausadoPorAlerta = false;
            const objetivo = campoParaEnfocar || actual;
            campoParaEnfocar = null;
            objetivo.focus();
            return;
        }
        actual.focus();
    });

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const okActual = validarActual();
        const okNueva = validarNueva();
        const okConfirmar = validarConfirmar();

        if (!okActual || !okNueva || !okConfirmar) {
            let mensaje = "Revisa los campos marcados en rojo.";
            let foco = null;
            if (!okActual) {
                mensaje = "Escribe tu contraseña actual.";
                foco = actual;
            } else if (!okNueva) {
                mensaje = MSG_PASSWORD;
                foco = nueva;
            } else if (!okConfirmar) {
                mensaje = "Las contraseñas nuevas no coinciden.";
                foco = confirmar;
            }
            abrirAlertaSobreModal("warning", "Revisa el formulario", mensaje, foco);
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
                let foco = null;
                if (resultado.campo === "actual") {
                    marcar(actual, false);
                    foco = actual;
                } else if (resultado.campo === "nueva") {
                    marcar(nueva, false);
                    foco = nueva;
                } else if (resultado.campo === "confirmar") {
                    marcar(confirmar, false);
                    foco = confirmar;
                }
                abrirAlertaSobreModal("error", "No se pudo cambiar", resultado.mensaje || "Inténtalo de nuevo.", foco);
            }
        } catch (error) {
            abrirAlertaSobreModal("error", "Error de conexión", error.message || "No se pudo comunicar con el servidor.");
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.textContent = "Guardar contraseña";
        }
    });
});

// editar datos personales
const REGEX_TELEFONO = /^\d{4}-\d{4}$/;
 
document.addEventListener("DOMContentLoaded", () => {
    const modalEl = document.getElementById("modalEditarPerfil");
    const form = document.getElementById("formEditarPerfil");
    const btnGuardar = document.getElementById("btnGuardarDatos");
    if (!modalEl || !form) return;
 
    const nombre = document.getElementById("edit_nombre");
    const apellido = document.getElementById("edit_apellido");
    const telefono = document.getElementById("edit_telefono");
    const ubicacion = document.getElementById("edit_ubicacion");
    const campos = [nombre, apellido, telefono, ubicacion];
 
    function marcar(campo, ok) {
        campo.classList.toggle("is-invalid", !ok);
        campo.classList.toggle("is-valid", ok);
        return ok;
    }
 
    function validar(campo) {
        if (campo === telefono) return marcar(campo, REGEX_TELEFONO.test(campo.value.trim()));
        return marcar(campo, campo.value.trim() !== "");
    }
 
    // el telefono se va formateando solo: 7000-0000
    telefono.addEventListener("input", () => {
        let valor = telefono.value.replace(/\D/g, "").substring(0, 8);
        if (valor.length > 4) valor = valor.substring(0, 4) + "-" + valor.substring(4);
        telefono.value = valor;
    });
 
    campos.forEach((campo) => {
        campo.addEventListener("blur", () => validar(campo));
        campo.addEventListener("input", () => {
            if (campo.classList.contains("is-invalid")) validar(campo);
        });
    });
 
    modalEl.addEventListener("hidden.bs.modal", () => {
        form.reset();
        campos.forEach((campo) => campo.classList.remove("is-valid", "is-invalid"));
    });
 
    modalEl.addEventListener("shown.bs.modal", () => nombre.focus());
 
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
 
        let todoOk = true;
        campos.forEach((campo) => {
            if (!validar(campo)) todoOk = false;
        });
        if (!todoOk) {
            mostrarToast("warning", "Revisa el formulario", "Completa los campos marcados en rojo.");
            return;
        }
 
        try {
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
 
            const datos = new FormData(form);
            datos.append("opcion", "actualizarDatos");
 
            const respuesta = await fetch(form.action, { method: "POST", body: datos });
            const resultado = await respuesta.json().catch(() => null);
 
            if (resultado && resultado.auth_required) {
                window.location.href = resultado.redirect;
                return;
            }
            if (!resultado) throw new Error("Respuesta inesperada del servidor (" + respuesta.status + ").");
 
            if (resultado.estado) {
                bootstrap.Modal.getInstance(modalEl).hide();
                mostrarToast("success", "Datos actualizados", resultado.mensaje);
                // se recarga para que el nombre del perfil y del menu se vean actualizados
                setTimeout(() => window.location.reload(), 1200);
            } else {
                if (resultado.campo === "telefono") marcar(telefono, false);
                if (resultado.campo === "ubicacion") marcar(ubicacion, false);
                mostrarToast("error", "No se pudo guardar", resultado.mensaje || "Inténtalo de nuevo.");
            }
        } catch (error) {
            mostrarToast("error", "Error de conexión", error.message || "No se pudo comunicar con el servidor.");
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.textContent = "Guardar cambios";
        }
    });
});