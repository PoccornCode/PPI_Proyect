
const zona = document.getElementById("chatMensajes");
const form = document.getElementById("formMensaje");
const input = document.getElementById("inputMensaje");
const btnEnviar = document.getElementById("btnEnviar");

const idSubasta = zona.dataset.idSubasta;
const url = zona.dataset.url;

let cantidadAnterior = -1;
let enviando = false;

function crearBurbuja(m) {
    const div = document.createElement("div");
    div.className = "chat-burbuja " + (m.es_mio ? "mio" : "otro");
    div.textContent = m.mensaje;

    const hora = document.createElement("span");
    hora.className = "chat-burbuja-hora";
    hora.textContent = m.fecha;

    div.appendChild(hora);
    return div;
}

function dibujar(mensajes) {
    zona.innerHTML = "";

    if (mensajes.length === 0) {
        const vacio = document.createElement("p");
        vacio.className = "chat-vacio";
        vacio.textContent = "Todavía no hay mensajes. ¡Escribe el primero!";
        zona.appendChild(vacio);
        return;
    }

    mensajes.forEach(function (m) {
        zona.appendChild(crearBurbuja(m));
    });
}

async function cargarMensajes() {
    try {
        const formData = new FormData();
        formData.append("opcion", "mensajes");
        formData.append("id_subasta", idSubasta);

        const respuesta = await fetch(url, { method: "POST", body: formData });
        if (!respuesta.ok) {
            throw new Error("Error del servidor: " + respuesta.status);
        }
        const resultado = await respuesta.json();

        if (!resultado.estado) {
            return;
        }

        if (resultado.mensajes.length === cantidadAnterior) {
            return;
        }

        const primeraVez = cantidadAnterior === -1;
        const estabaAbajo = zona.scrollHeight - zona.scrollTop - zona.clientHeight < 80;

        cantidadAnterior = resultado.mensajes.length;
        dibujar(resultado.mensajes);

        if (primeraVez || estabaAbajo) {
            zona.scrollTop = zona.scrollHeight;
        }
    } catch (error) {
        console.error(error);
    }
}

async function enviar() {
    const texto = input.value.trim();

    if (texto === "" || enviando) {
        return;
    }

    enviando = true;
    btnEnviar.disabled = true;

    try {
        const formData = new FormData();
        formData.append("opcion", "enviar");
        formData.append("id_subasta", idSubasta);
        formData.append("mensaje", texto);

        const respuesta = await fetch(url, { method: "POST", body: formData });
        if (!respuesta.ok) {
            throw new Error("Error del servidor: " + respuesta.status);
        }
        const resultado = await respuesta.json();

        if (resultado.estado) {
            input.value = "";
            await cargarMensajes();
            zona.scrollTop = zona.scrollHeight;
        } else {
            mostrarToast("error", "No se pudo enviar", resultado.mensaje, 4000);
        }
    } catch (error) {
        console.error(error);
        mostrarToast("error", "Error de conexión", "No se pudo enviar el mensaje.", 4000);
    }

    enviando = false;
    btnEnviar.disabled = false;
    input.focus();
}

form.addEventListener("submit", function (e) {
    e.preventDefault();
    enviar();
});

input.addEventListener("keydown", function (e) {
    if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        enviar();
    }
});

cargarMensajes();
input.focus();

setInterval(function () {
    if (!document.hidden) {
        cargarMensajes();
    }
}, 5000);