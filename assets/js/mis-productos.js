(function () {
    "use strict";

    const baseUrl = window.BASE_URL_APP || "../../";

    function esc(texto) {
        return String(texto ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    function formatearEstado(estado) {
        const e = String(estado || "").toLowerCase();
        const mapa = {
            pendiente: "En revisión",
            activa: "En subasta",
            abierta: "En subasta",
            rechazada: "Rechazada",
            cancelada: "Cancelada",
            sin_ofertas: "Sin ofertas",
            finalizada: "Finalizada"
        };
        return mapa[e] || (estado || "Sin subasta");
    }

    // Modal de detalle (ojito)
    const modalDetalleEl = document.getElementById("modalDetalleProducto");
    const detalleBody = document.getElementById("detalleProductoBody");
    const modalDetalle = modalDetalleEl ? new bootstrap.Modal(modalDetalleEl) : null;

    document.querySelectorAll(".btn-ver-detalle").forEach((btn) => {
        btn.addEventListener("click", function () {
            if (!modalDetalle || !detalleBody) return;

            let d = {};
            try {
                d = JSON.parse(this.dataset.detalle || "{}");
            } catch (err) {
                d = {};
            }

            // Bloque por defecto cuando el producto aún no tiene subasta
            const imgPrincipal = d.imagen || `${baseUrl}assets/img/hero-img.jpg`;
            let subastaHtml = `
                <div class="detalle-subasta-box mt-3">
                    <div class="text-second-ppi small">Este producto aún no tiene una subasta asociada.</div>
                </div>`;

            // Si tiene subasta, arma el resumen con estado, precio, fechas y motivo de rechazo
            if (d.estado_subasta) {
                const precioInicial = d.precio_inicial !== null && d.precio_inicial !== undefined
                    ? `$${Number(d.precio_inicial).toFixed(2)}`
                    : "—";
                subastaHtml = `
                    <div class="detalle-subasta-box mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-main-ppi fw-bold small">Información de la subasta</span>
                            <span class="badge-condicion">${esc(formatearEstado(d.estado_subasta))}</span>
                        </div>
                        <div class="row g-2 small">
                            <div class="col-6"><span class="text-second-ppi">Precio inicial:</span> <span class="text-main-ppi">${esc(precioInicial)}</span></div>
                            <div class="col-6"><span class="text-second-ppi">Inicio:</span> <span class="text-main-ppi">${esc(d.fecha_inicial || "—")}</span></div>
                            <div class="col-6"><span class="text-second-ppi">Cierre:</span> <span class="text-main-ppi">${esc(d.fecha_final || "—")}</span></div>
                        </div>
                        ${d.motivo_rechazo ? `
                            <div class="small mt-2">
                                <span class="text-second-ppi">Motivo de rechazo:</span> <span class="text-main-ppi">${esc(d.motivo_rechazo)}</span>
                            </div>` : ""}
                    </div>`;
            }

            // Inyecta la foto y los datos del producto dentro del cuerpo del modal
            detalleBody.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-5">
                        <img src="${esc(imgPrincipal)}" id="detalleImgPrincipal" class="detalle-galeria-principal" alt="${esc(d.nombre)}" onerror="this.onerror=null;this.src='${baseUrl}assets/img/hero-img.jpg';">
                    </div>
                    <div class="col-md-7">
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <span class="badge-condicion">${esc(d.categoria || "Sin categoría")}</span>
                            <span class="badge-condicion">${esc(d.condicion || "")}</span>
                        </div>
                        <h5 class="text-main-ppi fw-bold mb-2">${esc(d.nombre || "Producto")}</h5>
                        <p class="text-second-ppi small mb-0" style="white-space: pre-line;">${esc(d.descripcion || "")}</p>
                        ${subastaHtml}
                    </div>
                </div>`;

            modalDetalle.show();
        });
    });

    // Filtro por estado de la subasta
    const filtroEstado = document.getElementById("filtroEstado");
    const sinResultadosFiltro = document.getElementById("sinResultadosFiltro");

    if (filtroEstado) {
        filtroEstado.addEventListener("change", function () {
            const valor = this.value;
            let visibles = 0;

            document.querySelectorAll(".item-producto").forEach((item) => {
                const mostrar = valor === "todos" || item.dataset.estado === valor;
                item.classList.toggle("d-none", !mostrar);
                if (mostrar) visibles++;
            });

            if (sinResultadosFiltro) {
                sinResultadosFiltro.classList.toggle("d-none", visibles > 0);
            }
        });
    }

    // Modal publicar / reenviar / apelar subasta
    const modalSubastaEl = document.getElementById("modalPublicarSubasta");
    const formSubasta = document.getElementById("formPublicarSubasta");
    const modalSubasta = modalSubastaEl ? new bootstrap.Modal(modalSubastaEl) : null;

    const inputIdProducto = document.getElementById("subastaIdProducto");
    const tituloModalSubasta = document.getElementById("modalSubastaTituloTexto");
    const nombreProductoEl = document.getElementById("subastaNombreProducto");
    const motivoWrap = document.getElementById("subastaMotivoAnteriorWrap");
    const motivoTexto = document.getElementById("subastaMotivoAnteriorTexto");
    const tipoWrap = document.getElementById("subastaTipoWrap");
    const tipoSelect = document.getElementById("subastaTipoSolicitud");
    const notaLabel = document.getElementById("subastaNotaLabel");
    const notaInput = document.getElementById("subastaNotaVendedor");
    const notaWrap = document.getElementById("subastaNotaWrap");

    function actualizarLabelNota() {
        if (!tipoSelect || !notaLabel || !notaInput) return;
        const esApelacion = !tipoWrap.classList.contains("d-none") && tipoSelect.value === "apelacion";
        notaLabel.textContent = esApelacion
            ? "Motivo de tu apelación (obligatorio)"
            : "Nota para el moderador (opcional)";
        notaInput.required = esApelacion;
    }

    if (tipoSelect) {
        tipoSelect.addEventListener("change", actualizarLabelNota);
    }

    document.querySelectorAll(".btn-abrir-subasta").forEach((btn) => {
        btn.addEventListener("click", function () {
            if (!modalSubasta || !formSubasta) return;
            formSubasta.reset();

            const idProd = this.dataset.idProducto;
            const nombre = this.dataset.nombre || "";
            const estadoAnterior = this.dataset.estadoAnterior || "";
            const motivoAnterior = this.dataset.motivoRechazo || "";

            inputIdProducto.value = idProd;
            nombreProductoEl.textContent = nombre;

            if (estadoAnterior === "rechazada") {
                tituloModalSubasta.textContent = "Corregir o apelar subasta";
                tipoWrap.classList.remove("d-none");
                tipoSelect.value = "reenvio";
                if (motivoAnterior.trim() !== "") {
                    motivoWrap.classList.remove("d-none");
                    motivoTexto.textContent = motivoAnterior;
                } else {
                    motivoWrap.classList.add("d-none");
                }
            } else {
                tituloModalSubasta.textContent = estadoAnterior !== "" ? "Volver a subastar" : "Publicar en subasta";
                tipoWrap.classList.add("d-none");
                motivoWrap.classList.add("d-none");
                tipoSelect.value = estadoAnterior !== "" ? "reenvio" : "nueva";
            }

            actualizarLabelNota();
            notaWrap.classList.toggle("d-none", estadoAnterior !== "rechazada");
            modalSubasta.show();
        });
    });

    function mascaraMoneda(campo) {
        if (!campo) return;
        campo.addEventListener("input", () => {
            const digitos = campo.value.replace(/\D/g, "").slice(0, 10);
            const centavos = Number(digitos || "0");
            campo.value = (centavos / 100).toFixed(2);
        });
    }

    const inputPrecioInicial = document.getElementById("subastaPrecioInicial");
    const inputIncremento = document.getElementById("subastaIncremento");
    mascaraMoneda(inputPrecioInicial);
    mascaraMoneda(inputIncremento);

    if (formSubasta) {
        const btnEnviarSubasta = document.getElementById("btnEnviarSubasta");
        const textoBotonSubasta = btnEnviarSubasta.textContent;
        let enviandoSubasta = false;

        formSubasta.addEventListener("submit", async function (e) {
            e.preventDefault();
            if (enviandoSubasta) return;

            if (Number(inputPrecioInicial.value) <= 0 || Number(inputIncremento.value) <= 0) {
                mostrarToast("warning", "Campos incompletos", "El precio inicial y el incremento deben ser mayores a 0.", 4000);
                return;
            }

            enviandoSubasta = true;
            btnEnviarSubasta.disabled = true;
            btnEnviarSubasta.textContent = "Procesando...";

            try {
                const datosForm = new FormData(formSubasta);
                const respuesta = await fetch(formSubasta.action, { method: "POST", body: datosForm });

                if (!respuesta.ok) {
                    throw new Error("Error HTTP: " + respuesta.status);
                }

                const resultado = await respuesta.json();

                if (resultado.estado) {
                    if (modalSubasta) modalSubasta.hide();
                    mostrarToast("success", "¡Subasta enviada!", "Tu subasta quedó en revisión.", 2000);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                    return;
                }

                mostrarToast("error", "No se pudo enviar", resultado.mensaje, 4000);
            } catch (error) {
                console.error(error);
                mostrarToast("error", "Error de conexión", "No se pudo completar la operación. Intenta de nuevo.", 4000);
            }

            enviandoSubasta = false;
            btnEnviarSubasta.disabled = false;
            btnEnviarSubasta.textContent = textoBotonSubasta;
        });
    }

    // Cancelar solicitud pendiente (con modal de confirmación)
    const modalCancelarEl = document.getElementById("modalCancelarSolicitud");
    const modalCancelar = modalCancelarEl ? new bootstrap.Modal(modalCancelarEl) : null;
    const cancelarNombreEl = document.getElementById("cancelarNombreProducto");
    const btnConfirmarCancelar = document.getElementById("btnConfirmarCancelarSolicitud");

    let btnCancelarActual = null;
    document.querySelectorAll(".btn-cancelar-solicitud").forEach((btn) => {
        btn.addEventListener("click", function () {
            if (!modalCancelar) return;
            btnCancelarActual = btn;
            const nombre = (btn.dataset.nombre || "").trim();
            if (cancelarNombreEl) {
                cancelarNombreEl.textContent = nombre !== "" ? `"${nombre}"` : "este producto";
            }
            modalCancelar.show();
        });
    });

    if (btnConfirmarCancelar) {
        btnConfirmarCancelar.addEventListener("click", async function () {
            if (!btnCancelarActual) return;
            btnConfirmarCancelar.disabled = true;

            try {
                const datosForm = new FormData();
                datosForm.append("opcion", "cancelarSubasta");
                datosForm.append("id_subasta", btnCancelarActual.dataset.idSubasta);
                const respuesta = await fetch(btnCancelarActual.dataset.url, { method: "POST", body: datosForm });

                if (!respuesta.ok) {
                    throw new Error("Error HTTP: " + respuesta.status);
                }

                const resultado = await respuesta.json();
                if (modalCancelar) modalCancelar.hide();

                if (resultado.estado) {
                    mostrarToast("success", "¡Solicitud cancelada!", resultado.mensaje, 2000);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                    return;
                }

                mostrarToast("error", "No se pudo cancelar", resultado.mensaje, 4000);
            } catch (error) {
                console.error(error);
                mostrarToast("error", "Error de conexión", "No se pudo completar la operación. Intenta de nuevo.", 4000);
            }

            btnConfirmarCancelar.disabled = false;
        });
    }

    // Eliminar producto (solo sin subasta)
    const modalEliminarEl = document.getElementById("modalEliminarProducto");
    const modalEliminar = modalEliminarEl ? new bootstrap.Modal(modalEliminarEl) : null;
    const eliminarNombreEl = document.getElementById("eliminarNombreProducto");
    const btnConfirmarEliminar = document.getElementById("btnConfirmarEliminarProducto");

    let formEliminarActual = null;

    document.querySelectorAll(".form-eliminar").forEach((form) => {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            if (!modalEliminar) return;
            formEliminarActual = form;
            const nombre = (form.dataset.nombre || "").trim();
            if (eliminarNombreEl) {
                eliminarNombreEl.textContent = nombre !== "" ? `"${nombre}"` : "este producto";
            }
            modalEliminar.show();
        });
    });

    if (btnConfirmarEliminar) {
        btnConfirmarEliminar.addEventListener("click", async function () {
            if (!formEliminarActual) return;
            btnConfirmarEliminar.disabled = true;

            try {
                const datosForm = new FormData(formEliminarActual);
                const respuesta = await fetch(formEliminarActual.action, { method: "POST", body: datosForm });

                if (!respuesta.ok) {
                    throw new Error("Error HTTP: " + respuesta.status);
                }

                const resultado = await respuesta.json();
                if (modalEliminar) modalEliminar.hide();

                if (resultado.estado) {
                    mostrarToast("success", "¡Producto eliminado!", resultado.mensaje, 2000);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                    return;
                }

                mostrarToast("error", "No se pudo eliminar", resultado.mensaje, 4000);
            } catch (error) {
                console.error(error);
                mostrarToast("error", "Error de conexión", "No se pudo completar la operación. Intenta de nuevo.", 4000);
            }

            btnConfirmarEliminar.disabled = false;
        });
    }
})();