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
    chatFeed = document.getElementById("chat-feed")

document.addEventListener("DOMContentLoaded", async () => {
    try {
        const datos = new FormData();
        datos.append("opcion", "consultar");
        datos.append("id_subasta", id_subasta);
        const respuesta = await fetch(ruta_controller, {
            method: "POST",
            body: datos
        });
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
                    dia: String(fechaInicioObjeto.getDay()).padStart(2, '0')
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
                    dia: String(fechaFinalObjeto.getDay()).padStart(2, '0')
                },
                hora: {
                    hora: String(fechaFinalObjeto.getHours()).padStart(2, '0'),
                    minutos: String(fechaFinalObjeto.getMinutes()).padStart(2, '0'),
                    segundos: String(fechaFinalObjeto.getSeconds()).padStart(2, '0')
                }
            },
            estadoDato = resultado.datos.estado

        let estadoClass = new Map()
        estadoClass.set("activa", { class: "status-activa", text: "Subasta Activa" })
        estadoClass.set("pendiente", { class: "status-none", text: "Pendiente" })
        estadoClass.set("rechazada", { class: "status-none", text: "Rechazada" })
        estadoClass.set("finalizada", { class: "status-none", text: "Finalizada" })
        estadoClass.set("sin-ofertas", { class: "status-none", text: "Sin Ofertas" })
        estadoClass.set("cancelada", { class: "status-none", text: "Cancelada" })

        montoInicial.textContent = "$ " + resultado.datos.precio_inicial
        datoIncremento.textContent = "$ " + resultado.datos.dato_incremento
        datoPujaActual.textContent = "$ " + resultado.datos.dato_puja_actual
        //datos para llenar la fecha y hora inicial
        datoFechaInicio.textContent = `${fechaInicioComp.fecha.dia}-${fechaInicioComp.fecha.mes}-${fechaInicioComp.fecha.anio}`
        datoHoraInicio.textContent = `${fechaInicioComp.hora.hora}:${fechaInicioComp.hora.minutos}`
        //datos para llenar la fecha y hora final
        datoFechaCierre.textContent = `${fechaFinalComp.fecha.dia}-${fechaFinalComp.fecha.mes}-${fechaFinalComp.fecha.anio}`
        datoHoraCierre.textContent = `${fechaFinalComp.hora.hora}:${fechaFinalComp.hora.minutos}`

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
        datoPostores.textContent = resultado.datos.datoPostores
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

        alert(resultado.pujas.length)
        alert(chatFeed.id)
        if (resultado.pujas.length > 0) {

            resultado.pujas.forEach(puja => {

                const msg_card = document.createElement("div"),
                    avatar = document.createElement("div")
                msg_card.classList.add("chat-msg")
                avatar.classList.add(["avatar-ppi","avatar-char"])
                msg_card.append(avatar)

                chatFeed.append(msg_card)
                // let msg = document.createElement("div"),
                //     user = document.createElement("div")
                // msg.classList.add(["chat-msg","chat-burbuja"])
                // user.style.margin = "10px"
                // msg.append(user)

                // <div class="chat-msg">
                //     <div class="avatar-ppi avatar-chat">MA</div>
                //     <div class="chat-burbuja">
                //         <div class="chat-meta">
                //             <span class="chat-usuario">maria_sv</span>
                //             <span class="chat-hora">15:02</span>
                //         </div>
                //         <span class="chat-monto">$ 185.00</span>
                //     </div>
                // </div>

            })
        }
    } catch (error) {
        // Swal.close();
    }
}
)