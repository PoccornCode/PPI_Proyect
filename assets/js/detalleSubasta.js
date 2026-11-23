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
    imagenPrincipal = document.getElementById("foto-principal")

let rutasImagenes = ""

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
        estadoClass.set("activa",{class:"status-activa",text:"Subasta Activa"})
        estadoClass.set("pendiente",{class:"status-none",text:"Pendiente"})
        estadoClass.set("rechazada",{class:"status-none",text:"Rechazada"})
        estadoClass.set("finalizada",{class:"status-none",text:"Finalizada"})
        estadoClass.set("sin-ofertas",{class:"status-none",text:"Sin Ofertas"})
        estadoClass.set("cancelada",{class:"status-none",text:"Cancelada"})

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
        vendedorAvatar.textContent = String(resultado.datos.nombreU).slice(0,2)

        alert(ruta_base_img+resultado.rutasImg[0])
        // posicionamiento de fotografias
        imagenPrincipal.src = ruta_base_img+resultado.rutasImg[0]
    } catch (error) {
        // Swal.close();
    }
}
)