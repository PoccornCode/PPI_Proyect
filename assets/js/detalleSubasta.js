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
    chatResumen = document.getElementById("chat-resumen")

let resultadoSesion = null, resultadoPujas = null

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
        datosSesion.append("opcion", "consultarSesion")
        const respuestaSesion = await fetch(ruta_controller, {
            method: "POST",
            body: datosSesion
        })
        resultadoSesion = await respuestaSesion.json()

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
            estadoDato = resultado.datos.estado

        refrescarPujas()
        let estadoClass = new Map()
        estadoClass.set("activa", { class: "status-activa", text: "Subasta Activa" })
        estadoClass.set("pendiente", { class: "status-none", text: "Pendiente" })
        estadoClass.set("rechazada", { class: "status-none", text: "Rechazada" })
        estadoClass.set("finalizada", { class: "status-none", text: "Finalizada" })
        estadoClass.set("sin-ofertas", { class: "status-none", text: "Sin Ofertas" })
        estadoClass.set("cancelada", { class: "status-none", text: "Cancelada" })

        montoInicial.textContent = "$ " + resultado.datos.precio_inicial
        datoIncremento.textContent = "$ " + resultado.datos.dato_incremento
        //datos para llenar la fecha y hora inicial
        datoFechaInicio.textContent = `${fechaInicioComp.fecha.dia}-${fechaInicioComp.fecha.mes}-${fechaInicioComp.fecha.anio}`
        datoHoraInicio.textContent = `${fechaInicioComp.hora.hora}:${fechaInicioComp.hora.minutos}`
        //datos para llenar la fecha y hora final
        datoFechaCierre.textContent = `${fechaFinalComp.fecha.dia}-${fechaFinalComp.fecha.mes}-${fechaFinalComp.fecha.anio}`
        datoHoraCierre.textContent = `${fechaFinalComp.hora.hora}:${fechaFinalComp.hora.minutos}`
        montoInput.setAttribute("step", resultado.datos.dato_incremento)
        estado.innerHTML = `
            <span id="subasta-categoria" class="detalle-categoria">${resultado.datos.nombreC}</span>
            <span id="subasta-estado" class="status-badge ${estadoClass.get(estadoDato).class}">
                <span class="status-dot"></span><span id="subasta-estado-texto"
                    style="font-size: 0.9rem;">${estadoClass.get(estadoDato).text}</span>
            </span>`

        nombre.textContent = resultado.datos.nombre
        condicion.textContent = resultado.datos.condicion
        descripcion.textContent = resultado.datos.descripcion
        vendedorName.textContent = resultado.datos.nombreU
        vendedorAvatar.textContent = String(resultado.datos.nombreU).slice(0, 2)
        // posicionamiento de fotografias
        imagenPrincipal.src = ruta_base_img + resultado.rutasImg[0]

        rutasImagenes = resultado.rutasImg
        if (resultado.rutasImg.length > 1) {
            resultado.rutasImg.forEach((i, index) => {

                const item = document.createElement("button"),
                    itemImage = document.createElement("img")
                item.classList.add("btn-miniaturas")
                item.style.backgroundImage = `url(${(ruta_base_img + i)})`
                item.style.backgroundSize = "cover"
                item.style.backgroundPosition = "center"
                item.style.opacity = 0.8
                imagenesMiniaturas.append(item)

                item.addEventListener("click", () => {
                    imagenPrincipal.src = ruta_base_img + resultado.rutasImg[index]
                })

            })

        }

    } catch (error) {
        // Swal.close();

    }
}
)

btnPujar.addEventListener("click", async function () {

    let datos = new FormData()
    datos.append("opcion", "guardarPuja")
    datos.append("id_subasta", id_subasta)
    datos.append("id_usuario", resultadoSesion.idUsuario)
    datos.append("monto_puja", montoInput.value)
    const respuesta = await fetch(ruta_controller, {
        method: "POST",
        body: datos
    })
    resultado = await respuesta.json()
    dibujarPujas(true)
    mostrarModal(resultado.tipo, resultado.titulo, resultado.mensaje);
})

let ultimasPujsa = 1
async function refrescarPujas() {
    try {
        // alert("se refresco")
        let datos = new FormData();
        datos.append("opcion", "consultarPujas")
        datos.append("id_subasta", id_subasta)
        const respuestaSesion = await fetch(ruta_controller, {
            method: "POST",
            body: datos
        })
        resultadoPujas = await respuestaSesion.json()

        if (resultadoPujas.length != ultimasPujsa) {
            ultimasPujsa = resultadoPujas.length
            dibujarPujas(false)
        }
    }
    finally {
        setTimeout(refrescarPujas, 2000)
    }
}


async function dibujarPujas(consultar) {

    if (consultar) {
        let datos = new FormData();
        datos.append("opcion", "consultarPujas")
        datos.append("id_subasta", id_subasta)
        const respuestaSesion = await fetch(ruta_controller, {
            method: "POST",
            body: datos
        })
        resultadoPujas = await respuestaSesion.json()
    }

    if (resultadoPujas.pujas.length > 0) {
        datoPujaActual.textContent = "$ " + resultadoPujas.pujas[0][0]
        datoPostores.textContent = resultadoPujas.postores
        chatResumen.textContent = `${resultadoPujas.pujas.length} pujas . ${resultadoPujas.postores} postores`
        chatFeed.innerHTML = ""
        resultadoPujas.pujas.forEach((puja, index) => {
            // alert(resultado.pujas.length)
            // alert(puja.length)
            // alert(chatFeed.id)
            const msg_card = document.createElement("div"),
                avatar = document.createElement("div"),
                chatBurbuja = document.createElement("div"),
                chatMeta = document.createElement("div"),
                chatUsuario = document.createElement("span"),
                chatMonto = document.createElement("span"),
                chatTagLider = document.createElement("span")

            msg_card.classList.add("chat-msg")
            if (puja[2] == resultadoSesion.idUsuario) {
                msg_card.classList.add("chat-msg-mio")
                chatUsuario.textContent = "Tú"
            }
            else {
                avatar.classList.add("avatar-ppi")
                msg_card.append(avatar)
                avatar.textContent = String(puja[3]).slice(0, 2)
                chatUsuario.textContent = puja[3]
            }
            if (index == 0) {
                chatTagLider.textContent = "Puja Lider"
                chatTagLider.classList.add("span-puja-lider")
                chatMeta.appendChild(chatTagLider)
            }
            chatBurbuja.classList.add("chat-burbuja")
            chatMeta.append(chatUsuario)
            // avatar.classList.add("avatar-chat")
            msg_card.append(chatBurbuja)
            chatMeta.classList.add("chat-meta")
            chatBurbuja.append(chatMeta)
            chatUsuario.classList.add("chat-usuario")
            chatMonto.textContent = "$  " + puja[0]
            chatMonto.classList.add("chat-monto")
            chatBurbuja.append(chatMonto)
            chatFeed.appendChild(msg_card)

        })
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
                cb();
            }
        });
    }

    modalInstance.show();
}