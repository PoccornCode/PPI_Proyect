const montoInicial = document.getElementById("dato-monto-inicial"),
    datoIncremento = document.getElementById("dato-incremento"),
    datoPujaActual = document.getElementById("dato-puja-actual"),
    datoFechaInicio = document.getElementById("dato-inicio"),
    datoHoraInicio = document.getElementById("dato-inicio-hora"),
    datoFechaCierre = document.getElementById("dato-cierre"),
    datoHoraCierre = document.getElementById("dato-cierre-hora"),
    estado = document.getElementById("subasta-categoria-estado"),
    nombre = document.getElementById("subasta-producto-nombre"),
    condicion = document.getElementById("subasta-condicion"),
    descripcion = document.getElementById("subasta-descripcion"),
    vendedorName = document.getElementById("vendedor-nombre"),
    vendedorAvatar = document.getElementById("vendedor-avatar"),
    imagenPrincipal = document.getElementById("foto-principal"),
    datoPostores = document.getElementById("dato-postores"),
    imagenesMiniaturas = document.getElementById("miniaturas"),
    chatFeed = document.getElementById("chat-feed"),
    montoInput = document.getElementById("monto-input"),
    btnPujar = document.getElementById("btnPujar"),
    chatResumen = document.getElementById("chat-resumen");

let resultadoSesion = null,
    resultadoPujas = null,
    precioInicialNum = 0,
    incrementoMinimoNum = 0,
    resultadoMain = null

document.addEventListener("DOMContentLoaded", async () => {
    try {
        const datos = new FormData();
        datos.append("opcion", "consultar");
        datos.append("id_subasta", id_subasta);
        const respuesta = await fetch(ruta_controller, {
            method: "POST",
            body: datos
        });

        let datosSesion = new FormData();
        datosSesion.append("opcion", "consultarSesion");
        const respuestaSesion = await fetch(ruta_controller, {
            method: "POST",
            body: datosSesion
        });
        resultadoSesion = await respuestaSesion.json();

        if (!respuesta.ok) {
            console.error(respuesta.status);
            throw new Error("Error HTTP: " + respuesta.status);
        }

        const resultado = await respuesta.json(),
            fechaInicioObjeto = new Date(resultado.datos.fecha_inicio),
            fechaInicioComp = {
                fecha: {
                    anio: fechaInicioObjeto.getFullYear(),
                    mes: String(fechaInicioObjeto.getMonth() + 1).padStart(2, '0'),
                    dia: String(fechaInicioObjeto.getDate()).padStart(2, '0')
                },
                hora: {
                    hora: String(fechaInicioObjeto.getHours()).padStart(2, '0'),
                    minutos: String(fechaInicioObjeto.getMinutes()).padStart(2, '0'),
                    segundos: String(fechaInicioObjeto.getSeconds()).padStart(2, '0')
                }
            },
            fechaFinalObjeto = new Date(resultado.datos.fecha_cierre),
            fechaFinalComp = {
                fecha: {
                    anio: fechaFinalObjeto.getFullYear(),
                    mes: String(fechaFinalObjeto.getMonth() + 1).padStart(2, '0'),
                    dia: String(fechaFinalObjeto.getDate()).padStart(2, '0')
                },
                hora: {
                    hora: String(fechaFinalObjeto.getHours()).padStart(2, '0'),
                    minutos: String(fechaFinalObjeto.getMinutes()).padStart(2, '0'),
                    segundos: String(fechaFinalObjeto.getSeconds()).padStart(2, '0')
                }
            },
            estadoDato = resultado.datos.estado;

        // Guardamos los valores numéricos para validar las pujas
        precioInicialNum = parseFloat(resultado.datos.precio_inicial) || 0;
        incrementoMinimoNum = parseFloat(resultado.datos.dato_incremento) || 0;
        resultadoMain = resultado
        refrescarPujas();

        let estadoClass = new Map();
        estadoClass.set("activa", { class: "status-activa", text: "Subasta Activa" });
        estadoClass.set("pendiente", { class: "status-none", text: "Pendiente" });
        estadoClass.set("rechazada", { class: "status-none", text: "Rechazada" });
        estadoClass.set("finalizada", { class: "status-none", text: "Finalizada" });
        estadoClass.set("sin-ofertas", { class: "status-none", text: "Sin Ofertas" });
        estadoClass.set("cancelada", { class: "status-none", text: "Cancelada" });

        montoInicial.textContent = "$ " + resultado.datos.precio_inicial;
        datoIncremento.textContent = "$ " + resultado.datos.dato_incremento;

        datoFechaInicio.textContent = `${fechaInicioComp.fecha.dia}-${fechaInicioComp.fecha.mes}-${fechaInicioComp.fecha.anio}`;
        datoHoraInicio.textContent = `${fechaInicioComp.hora.hora}:${fechaInicioComp.hora.minutos}`;

        datoFechaCierre.textContent = `${fechaFinalComp.fecha.dia}-${fechaFinalComp.fecha.mes}-${fechaFinalComp.fecha.anio}`;
        datoHoraCierre.textContent = `${fechaFinalComp.hora.hora}:${fechaFinalComp.hora.minutos}`;

        const detalleTiempoRestante = document.getElementById("detalle-tiempo-restante");
        const detalleCountdown = document.getElementById("detalle-countdown");
        const detalleCierreTexto = document.getElementById("detalle-cierre-texto");

        if (detalleCierreTexto) {
            detalleCierreTexto.textContent = `Cierra el ${fechaFinalComp.fecha.dia}/${fechaFinalComp.fecha.mes}/${fechaFinalComp.fecha.anio} ${fechaFinalComp.hora.hora}:${fechaFinalComp.hora.minutos}`;
        }
        if (detalleTiempoRestante && detalleCountdown) {
            if (estadoDato !== "activa" || fechaFinalObjeto <= new Date()) {
                detalleTiempoRestante.textContent = "Finalizada";
            } else {
                detalleCountdown.setAttribute("data-countdown", fechaFinalObjeto.toISOString());
            }
        }
        montoInput.setAttribute("step", resultado.datos.dato_incremento);

        estado.innerHTML = `
            <span id="subasta-categoria" class="detalle-categoria">${resultado.datos.nombreC}</span>
            <span id="subasta-estado" class="status-badge ${estadoClass.get(estadoDato).class}">
                <span class="status-dot"></span><span id="subasta-estado-texto"
                    style="font-size: 0.9rem;">${estadoClass.get(estadoDato).text}</span>
            </span>`;

        nombre.textContent = resultado.datos.nombre;
        condicion.textContent = resultado.datos.condicion;
        descripcion.textContent = resultado.datos.descripcion;
        vendedorName.textContent = resultado.datos.nombreU;
        vendedorAvatar.textContent = String(resultado.datos.nombreU).slice(0, 2);

        
        imagenPrincipal.src = ruta_base_img + resultado.rutasImg[0];
        rutasImagenes = resultado.rutasImg;
        
        const contadorFotos = document.getElementById("contador-fotos");
        const totalFotos = resultado.rutasImg.length;
        if (contadorFotos && totalFotos > 0) {
            contadorFotos.textContent = `1 / ${totalFotos}`;
            contadorFotos.classList.remove("d-none");
        }

        if (totalFotos > 1) {
            resultado.rutasImg.forEach((i, index) => {
                const item = document.createElement("button");
                item.type = "button";
                item.classList.add("btn-miniaturas");
                item.style.backgroundImage = `url(${(ruta_base_img + i)})`;
                item.style.backgroundSize = "cover";
                item.style.backgroundPosition = "center";
                item.style.opacity = index === 0 ? 1 : 0.7;
                imagenesMiniaturas.append(item);

                item.addEventListener("click", () => {
                    imagenPrincipal.src = ruta_base_img + resultado.rutasImg[index];

                    if (contadorFotos) {
                        contadorFotos.textContent = `${index + 1} / ${totalFotos}`;
                    }

                    // Resaltar miniatura activa
                    imagenesMiniaturas.querySelectorAll(".btn-miniaturas").forEach(btn => {
                        btn.style.opacity = 0.7;
                    });
                    item.style.opacity = 1;
                });
            });
        }
    } catch (error) {
        // mostrarModal("error","¡UPS!","Algo ha sucecido, por favor recarga la pagina")
        console.error(error)
    }
});

btnPujar.addEventListener("click", async function (e) {
    e.preventDefault();

    if (!resultadoSesion || !resultadoSesion.idUsuario) {
        mostrarModal("error", "¡Ojo!", "Debes iniciar sesión para poder pujar.");
        return;
    }
    if(resultadoMain.datos.estado !== "activa"){
        mostrarModal(
            "warning",
            "¡Lo sentimos!",
            "Esta subasta ya no acepta ofertas nuevas"
        )
        return
    }

    const montoOfertado = parseFloat(montoInput.value);
    if (isNaN(montoOfertado) || montoOfertado <= 0) {
        mostrarModal("warning", "Monto Inválido", "Ingresa un monto válido mayor a $0.00.");
        return;
    }

    const hayPujas = resultadoPujas && resultadoPujas.pujas && resultadoPujas.pujas.length > 0;
    const pujaActualNum = hayPujas ? parseFloat(resultadoPujas.pujas[0][0]) : 0;
    const minimoPermitido = hayPujas ? (pujaActualNum + incrementoMinimoNum) : precioInicialNum;

    if (montoOfertado < minimoPermitido) {
        mostrarModal(
            "warning",
            "Oferta insuficiente",
            `El monto mínimo para pujar en este momento es de <strong>$ ${minimoPermitido.toFixed(2)}</strong>.`
        );
        return;
    }

    const confirmado = await mostrarModalConfirmar(
        "Confirmar puja",
        `¿Estás seguro de que deseas ofertar <strong class="text-white">$ ${montoOfertado.toFixed(2)}</strong> en esta subasta?`,
        "Sí, pujar"
    );

    if (!confirmado) return;

    let datos = new FormData();
    datos.append("opcion", "guardarPuja");
    datos.append("id_subasta", id_subasta);
    datos.append("id_usuario", resultadoSesion.idUsuario);
    datos.append("monto_puja", montoOfertado.toFixed(2));

    const respuesta = await fetch(ruta_controller, {
        method: "POST",
        body: datos
    });
    const resultado = await respuesta.json();

    if (resultado.tipo === "success") {
        montoInput.value = "";
        await dibujarPujas(true);
    }

    mostrarModal(resultado.tipo, resultado.titulo, resultado.mensaje);
});

let ultimasPujas = -1;
async function refrescarPujas() {
    try {
        let datos = new FormData();
        datos.append("opcion", "consultarPujas");
        datos.append("id_subasta", id_subasta);
        const respuestaPujas = await fetch(ruta_controller, {
            method: "POST",
            body: datos
        });
        resultadoPujas = await respuestaPujas.json();

        const totalPujas = resultadoPujas.pujas ? resultadoPujas.pujas.length : 0;
        if (totalPujas !== ultimasPujas) {
            ultimasPujas = totalPujas;
            dibujarPujas(false);
        }
    } finally {
        setTimeout(refrescarPujas, 2000);
    }
}

async function dibujarPujas(consultar) {
    if (consultar) {
        let datos = new FormData();
        datos.append("opcion", "consultarPujas");
        datos.append("id_subasta", id_subasta);
        const respuestaPujas = await fetch(ruta_controller, {
            method: "POST",
            body: datos
        });
        resultadoPujas = await respuestaPujas.json();
        ultimasPujas = resultadoPujas.pujas ? resultadoPujas.pujas.length : 0;
    }

    if (resultadoPujas && resultadoPujas.pujas.length > 0) {
        datoPujaActual.textContent = "$ " + resultadoPujas.pujas[0][0];
        datoPostores.textContent = resultadoPujas.postores;
        chatResumen.textContent = `${resultadoPujas.pujas.length} pujas · ${resultadoPujas.postores} postores`;
        chatFeed.innerHTML = "";

        resultadoPujas.pujas.forEach((puja, index) => {
            const msg_card = document.createElement("div"),
                avatar = document.createElement("div"),
                chatBurbuja = document.createElement("div"),
                chatMeta = document.createElement("div"),
                chatUsuario = document.createElement("span"),
                chatMonto = document.createElement("span"),
                chatTagLider = document.createElement("span");

            msg_card.classList.add("chat-msg");
            if (resultadoSesion && puja[2] == resultadoSesion.idUsuario) {
                msg_card.classList.add("chat-msg-mio");
                chatUsuario.textContent = "Tú";
            } else {
                avatar.classList.add("avatar-ppi");
                avatar.innerHTML = '<i class="fas fa-user"></i>';
                msg_card.append(avatar);
                chatUsuario.textContent = "Postor";
            }

            if (index == 0) {
                chatTagLider.textContent = "Puja Líder";
                chatTagLider.classList.add("span-puja-lider");
                chatMeta.appendChild(chatTagLider);
                chatBurbuja.classList.add("chat-tag-lider");
            }

            chatBurbuja.classList.add("chat-burbuja");
            chatMeta.append(chatUsuario);
            msg_card.append(chatBurbuja);
            chatMeta.classList.add("chat-meta");
            chatBurbuja.append(chatMeta);
            chatUsuario.classList.add("chat-usuario");
            chatMonto.textContent = "$ " + puja[0];
            chatMonto.classList.add("chat-monto");
            chatBurbuja.append(chatMonto);
            chatFeed.appendChild(msg_card);
        });
    } else {
        datoPujaActual.textContent = "$ " + precioInicialNum.toFixed(2);
        datoPostores.textContent = "0";
        chatResumen.textContent = "0 pujas · 0 postores";
    }
}

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
                // cb();
            }
        });
    }

    modalInstance.show();
}

let modalConfirmarInstance = null;
let resolverConfirmacion = null;

function mostrarModalConfirmar(titulo, mensajeHtml, textoBoton = "Confirmar") {
    return new Promise((resolve) => {
        const modalEl = document.getElementById("modalConfirmar");
        const tituloEl = document.getElementById("modalConfirmarLabel");
        const mensajeEl = document.getElementById("modalConfirmarMensaje");
        const btnAceptar = document.getElementById("btnConfirmarAceptar");
        const btnCancelar = document.getElementById("btnConfirmarCancelar");

        if (!modalEl || !tituloEl || !mensajeEl || !btnAceptar || !btnCancelar) {
            resolve(false);
            return;
        }

        resolverConfirmacion = resolve;

        tituloEl.textContent = titulo;
        mensajeEl.innerHTML = mensajeHtml;
        btnAceptar.innerHTML = `<i class="fas fa-check"></i> ${textoBoton}`;

        if (!modalConfirmarInstance) {
            modalConfirmarInstance = new bootstrap.Modal(modalEl);

            btnAceptar.addEventListener("click", () => {
                if (resolverConfirmacion) {
                    const res = resolverConfirmacion;
                    resolverConfirmacion = null;
                    modalConfirmarInstance.hide();
                    res(true);
                }
            });

            btnCancelar.addEventListener("click", () => {
                if (resolverConfirmacion) {
                    const res = resolverConfirmacion;
                    resolverConfirmacion = null;
                    res(false);
                }
            });

            modalEl.addEventListener("hidden.bs.modal", () => {
                if (resolverConfirmacion) {
                    const res = resolverConfirmacion;
                    resolverConfirmacion = null;
                    res(false);
                }
            });
        }

        modalConfirmarInstance.show();
    });
}