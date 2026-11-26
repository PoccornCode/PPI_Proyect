let modalInstance = null;
let callbackModal = null;

let form, btnGuardar, textoBotonOriginal;
let inputNombre, selectCategoria, selectCondicion, textareaDescripcion, inputImagenes;
let camposValidables = [];

let esEdicion = false;
let idsEliminar = [];
let imagenesNuevas = [];
let enviando = false;

function mostrarModal(tipo, titulo, mensaje, onCerrar = null) {
    const modalEl = document.getElementById("modalPPI");
    const tituloEl = document.getElementById("modalPPITitulo");
    const cuerpoEl = document.getElementById("modalPPICuerpo");
    const btnAccion = document.getElementById("btnModalPPIAccion");

    if (!modalEl || !tituloEl || !cuerpoEl || !btnAccion) return;

    callbackModal = onCerrar;
    btnAccion.className = "btn btn-ppi-modal btn-sm";

    let icon;
    if (tipo === "success") {
        icon = '<i class="fas fa-check-circle fs-5" style="color: #658807;"></i>';
        btnAccion.textContent = "Continuar";
    } else if (tipo === "error") {
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

function validarCampoIndividual(campo) {
    const valor = campo.value.trim();
    const esValido = valor !== "" && valor !== "0";

    campo.classList.toggle("is-invalid", !esValido);
    campo.classList.toggle("is-valid", esValido);
    return esValido;
}

function inicializarDropzone() {
    const dropzone = document.getElementById("dropzoneImagenes");

    dropzone.addEventListener("click", () => inputImagenes.click());
    inputImagenes.addEventListener("change", function (event) {
        const permitidos = ["image/jpeg", "image/png", "image/webp"];
        const elegidos = Array.from(event.target.files);
        const nuevos = elegidos.filter(f => permitidos.includes(f.type));

        if (nuevos.length < elegidos.length) {
            mostrarModal("warning", "Formato no válido", "Solo se permiten imágenes JPG, PNG o WEBP.");
        }

        imagenesNuevas.push(...nuevos);
        inputImagenes.value = "";

        if (imagenesNuevas.length > 0) dropzone.style.borderColor = "#23271f";
        renderNuevas();
    });
}

function renderNuevas() {
    const contenedor = document.getElementById("contenedorImagenNuevas");
    contenedor.innerHTML = "";
    const labelNuevas = document.getElementById("labelNuevas");

    if (imagenesNuevas.length === 0) {
        labelNuevas.textContent = "";
        return;
    }

    labelNuevas.textContent = "Imágenes nuevas";
    imagenesNuevas.forEach((imagen, index) => {
        const div = document.createElement("div");
        div.className = "preview-img-box";
        div.style.cursor = "pointer";
        const img = document.createElement("img");
        img.src = URL.createObjectURL(imagen);
        img.alt = imagen.name;
        div.appendChild(img);

        if (index === 0 && document.querySelectorAll("#contenedorImagenesActuales .preview-img-box").length === 0) {
            const badge = document.createElement("span");
            badge.className = "badge-principal";
            badge.textContent = "Principal";
            div.appendChild(badge);
        }

        const btnQuitar = document.createElement("button");
        btnQuitar.type = "button";
        btnQuitar.className = "btn-quitar";
        btnQuitar.textContent = "X";

        btnQuitar.addEventListener("click", (e) => {
            e.stopPropagation();
            if (totalImagenesVisibles() <= 1) {
                mostrarModal("warning", "Acción no permitida", "El producto debe tener al menos una fotografía.");
                return;
            }
            imagenesNuevas.splice(index, 1);
            renderNuevas();
        });

        div.addEventListener("click", () => abrirVisorImagenes(img.src));
        div.appendChild(btnQuitar);
        contenedor.appendChild(div);
    });
}

function totalImagenesVisibles() {
    return document.querySelectorAll("#contenedorImagenesActuales .preview-img-box").length + imagenesNuevas.length;
}

function abrirVisorImagenes(scr) {
    const fondo = document.createElement("div");
    fondo.className = "visor-fondo";

    const img = document.createElement("img");
    img.className = "visor-img";
    img.src = scr;

    const btnCerrar = document.createElement("button");
    btnCerrar.type = "button";
    btnCerrar.className = "visor-cerrar";
    btnCerrar.textContent = "X";

    fondo.appendChild(img);
    fondo.appendChild(btnCerrar);
    document.body.appendChild(fondo);

    btnCerrar.addEventListener("click", () => fondo.remove());
    fondo.addEventListener("click", e => {
        if (e.target === fondo) fondo.remove();
    });
}

function limpiarFormulario() {
    form.reset();
    camposValidables.forEach(campo => campo.classList.remove("is-valid", "is-invalid"));
    imagenesNuevas = [];
    document.getElementById("contenedorImagenNuevas").innerHTML = "";
    document.getElementById("dropzoneImagenes").style.borderColor = "";
}

function validarFormulario() {
    const validos = camposValidables.map(campo => validarCampoIndividual(campo));
    if (validos.includes(false)) {
        mostrarModal("warning", "Campos incompletos", "Completa los campos requeridos marcados en el formulario.");
        return false;
    }
    return true;
}

async function cargarProductoid(idProduc) {
    try {
        const datos = new FormData();
        datos.append("opcion", "obtener");
        const respuesta = await fetch(form.action + "?id=" + idProduc, { method: "POST", body: datos });
        const resultado = await respuesta.json();
        if (!resultado.estado) {
            mostrarModal("error", "No encontrado", resultado.mensaje, () => {
                window.location.href = RUTA_MIS_PRODUCTOS;
            });
            return;
        }
        inputNombre.value = resultado.producto.nombre;
        textareaDescripcion.value = resultado.producto.descripcion;
        selectCategoria.value = resultado.producto.id_categoria;
        selectCondicion.value = resultado.producto.condicion;

        cargarImagenesActuales(resultado.imagenes);
        imagenes = resultado.imagenes;
    } catch (error) {
        console.error(error);
        mostrarModal("error", "Error de conexión", "No se pudo cargar el producto. Intenta de nuevo.");
    }
}

function cargarImagenesActuales(imagenes) {
    const labelActuales = document.getElementById("labelActuales");

    labelActuales.textContent = "Imágenes actuales";
    const contenedor = document.getElementById("contenedorImagenesActuales");
    contenedor.innerHTML = "";
    imagenes.forEach((imagen) => {
        const div = document.createElement("div");
        div.className = "preview-img-box";
        const img = document.createElement("img");
        img.src = RUTA_IMAGENES + imagen.ruta;
        img.alt = imagen.ruta;
        div.appendChild(img);

        if (imagen.principal == 1) {
            const badge = document.createElement("span");
            badge.className = "badge-principal";
            badge.textContent = "Principal";
            div.appendChild(badge);
        }

        const btnQuitar = document.createElement("button");
        btnQuitar.type = "button";
        btnQuitar.className = "btn-quitar";
        btnQuitar.textContent = "X";

        btnQuitar.addEventListener("click", (e) => {
            e.stopPropagation();
            if (totalImagenesVisibles() <= 1) {
                mostrarModal("warning", "Acción no permitida", "El producto debe tener al menos una fotografía.");
                return;
            }
            idsEliminar.push(imagen.id_imagen_producto);
            div.remove();

            if (imagenes.length === idsEliminar.length) {
                labelActuales.textContent = "";
                return;
            }
        });

        div.addEventListener("click", () => abrirVisorImagenes(img.src));
        div.appendChild(btnQuitar);
        contenedor.appendChild(div);
    });
}

document.addEventListener("DOMContentLoaded", function () {
    form = document.getElementById("formRegistroSubasta");
    btnGuardar = document.getElementById("btnGuardar");
    textoBotonOriginal = btnGuardar.textContent.trim();
    esEdicion = textoBotonOriginal === "Modificar";
    const dropzone = document.getElementById("dropzoneImagenes");

    inputNombre = document.getElementById("nombre");
    selectCategoria = document.getElementById("id_categoria");
    selectCondicion = document.getElementById("condicion");
    textareaDescripcion = document.getElementById("descripcion");
    inputImagenes = document.getElementById("imagenes");

    camposValidables = [inputNombre, selectCategoria, selectCondicion, textareaDescripcion];

    inicializarDropzone();

    camposValidables.forEach(campo => {
        campo.addEventListener("blur", () => validarCampoIndividual(campo));
        campo.addEventListener("change", () => {
            if (campo.classList.contains("is-invalid")) validarCampoIndividual(campo);
        });
        campo.addEventListener("input", () => {
            if (campo.classList.contains("is-invalid")) validarCampoIndividual(campo);
        });
    });

    if (esEdicion) {
        const idProduc = new URLSearchParams(window.location.search).get("id");
        cargarProductoid(idProduc);
    }

    form.addEventListener("submit", async function (e) {
        e.preventDefault();
        if (enviando) return;

        if (btnGuardar.textContent.trim() !== textoBotonOriginal) {
            return;
        }

        if (!validarFormulario()) {
            return;
        }

        if (totalImagenesVisibles() === 0) {
            mostrarModal("warning", "Fotografías requeridas", "Debes agregar al menos una fotografía del producto.");
            return;
        }

        enviando = true;
        btnGuardar.disabled = true;
        inputImagenes.disabled = true;
        dropzone.style.pointerEvents = "none";
        dropzone.style.opacity = "0.5";

        try {
            const datosForm = new FormData(form);
            datosForm.delete("imagenes[]");
            imagenesNuevas.forEach(archivo => datosForm.append("imagenes[]", archivo));
            datosForm.append("opcion", esEdicion ? "modificar" : "guardar");

            if (esEdicion) {
                idsEliminar.forEach(id => datosForm.append("idrutasEliminar[]", id));
            }

            const idProduc = new URLSearchParams(window.location.search).get("id");
            const urlEnvio = esEdicion ? form.action + "?id=" + idProduc : form.action;

            btnGuardar.disabled = true;
            btnGuardar.textContent = "Procesando...";
            inputImagenes.disabled = true;
            dropzone.style.pointerEvents = "none";
            dropzone.style.opacity = "0.5";

            const respuesta = await fetch(urlEnvio, { method: "POST", body: datosForm });

            if (!respuesta.ok) {
                throw new Error("Error HTTP: " + respuesta.status);
            }

            const resultado = await respuesta.json();

            if (resultado.estado) {
                btnGuardar.textContent = "Redirigiendo...";
                const titulo = esEdicion ? "¡Producto modificado!" : "¡Producto registrado!";
                mostrarToast("success", titulo, "Redirigiendo a tu lista de productos...", 2000);
                setTimeout(() => {
                    window.location.href = RUTA_MIS_PRODUCTOS;
                }, 1200);
                return;
            } else {
                enviando = false;
                mostrarModal("error", (esEdicion ? "No se pudo modificar" : "No se pudo guardar"), resultado.mensaje);
                btnGuardar.disabled = false;
                btnGuardar.textContent = textoBotonOriginal;
                inputImagenes.disabled = false;
                dropzone.style.pointerEvents = "";
                dropzone.style.opacity = "";
                if (resultado.errorImagen) {
                    imagenesNuevas = [];
                    renderNuevas();
                }
            }
        } catch (error) {
            console.error(error);
            mostrarModal("error", "Error de conexión", "No se pudo completar la operación. Intenta de nuevo.");
            enviando = false;
            btnGuardar.disabled = false;
            btnGuardar.textContent = textoBotonOriginal;
            inputImagenes.disabled = false;
            dropzone.style.pointerEvents = "";
            dropzone.style.opacity = "";
        }
    });
});