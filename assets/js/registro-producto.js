let modalInstance = null;
let callbackModal = null;

let form, btnGuardar, textoBotonOriginal;
let inputNombre, selectCategoria, selectCondicion, textareaDescripcion, inputImagenes;
let camposValidables = [];

let esEdicion = false;
let idsEliminar = [];
let archivosNuevos = [];

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

// Dibuja las fotos nuevas (las que el usuario eligió en este momento)
function renderNuevas() {
    const container = document.getElementById("imagePreviewContainer");
    container.innerHTML = "";

    archivosNuevos.forEach((file, index) => {
        const box = document.createElement("div");
        box.className = "preview-img-box";

        const img = document.createElement("img");
        img.src = URL.createObjectURL(file);
        img.alt = file.name;
        box.appendChild(img);

        if (index === 0 && document.querySelectorAll("#preview-container .preview-img-box").length === 0) {
            const badge = document.createElement("span");
            badge.className = "badge-principal";
            badge.textContent = "Principal";
            box.appendChild(badge);
        }

        container.appendChild(box);
    });
}

// Dibuja las fotos que ya estaban guardadas en la base
function mostrarImagenesActuales(imagenes) {
    const contenedor = document.getElementById("preview-container");
    contenedor.innerHTML = "";

    imagenes.forEach(img => {
        const caja = document.createElement("div");
        caja.className = "preview-img-box";
        caja.dataset.id = img.id_imagen_producto;

        const foto = document.createElement("img");
        foto.src = RUTA_IMAGENES + img.ruta;
        foto.alt = img.ruta;
        caja.appendChild(foto);

        if (Number(img.principal) === 1) {
            const badge = document.createElement("span");
            badge.className = "badge-principal";
            badge.textContent = "Principal";
            caja.appendChild(badge);
        }

        const btnQuitar = document.createElement("button");
        btnQuitar.type = "button";
        btnQuitar.textContent = "X";
        btnQuitar.addEventListener("click", () => {
            idsEliminar.push(img.id_imagen_producto);
            caja.remove();
        });
        caja.appendChild(btnQuitar);

        contenedor.appendChild(caja);
    });
}

// Pide el producto y sus fotos al controlador y llena el formulario
async function cargarProducto(idProducto) {
    try {
        const datos = new FormData();
        datos.append("opcion", "obtener");

        const respuesta = await fetch(form.action + "?id=" + idProducto, { method: "POST", body: datos });
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

        mostrarImagenesActuales(resultado.imagenes);
    } catch (error) {
        console.error(error);
        mostrarModal("error", "Error de conexión", "No se pudo cargar el producto. Intenta de nuevo.");
    }
}

function inicializarDropzone() {
    const dropzone = document.getElementById("dropzoneImagenes");

    dropzone.addEventListener("click", () => inputImagenes.click());

    inputImagenes.addEventListener("change", function (event) {
        const nuevos = Array.from(event.target.files).filter(f => f.type.startsWith("image/"));
        archivosNuevos.push(...nuevos);
        inputImagenes.value = "";

        if (archivosNuevos.length > 0) dropzone.style.borderColor = "#23271f";
        renderNuevas();
    });
}

function limpiarFormulario() {
    form.reset();
    camposValidables.forEach(campo => campo.classList.remove("is-valid", "is-invalid"));
    archivosNuevos = [];
    document.getElementById("imagePreviewContainer").innerHTML = "";
    document.getElementById("dropzoneImagenes").style.borderColor = "";
}

function validarFormulario() {
    const validos = camposValidables.map(campo => validarCampoIndividual(campo));
    if (validos.includes(false)) {
        mostrarModal("warning", "Campos incompletos", "Completa los campos requeridos marcados en el formulario.");
        return false;
    }

    // Cuenta las fotos actuales que siguen visibles más las nuevas
    const totalFotos = document.querySelectorAll("#preview-container .preview-img-box").length + archivosNuevos.length;
    if (totalFotos === 0) {
        document.getElementById("dropzoneImagenes").style.borderColor = "#dc3545";
        mostrarModal("warning", "Imágenes requeridas", "Debes tener al menos una fotografía del producto.");
        return false;
    }

    return true;
}

document.addEventListener("DOMContentLoaded", function () {
    form = document.getElementById("formRegistroSubasta");
    btnGuardar = document.getElementById("btnGuardar");
    textoBotonOriginal = btnGuardar.textContent.trim();
    esEdicion = textoBotonOriginal === "Modificar";

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
        cargarProducto(new URLSearchParams(window.location.search).get("id"));
    }

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        if (btnGuardar.textContent.trim() !== textoBotonOriginal) return;
        if (!validarFormulario()) return;

        try {
            const datos = new FormData(form);
            datos.delete("imagenes[]");
            archivosNuevos.forEach(file => datos.append("imagenes[]", file));
            datos.append("opcion", esEdicion ? "modificar" : "guardar");
            idsEliminar.forEach(id => datos.append("idrutasEliminar[]", id));

            const idProducto = new URLSearchParams(window.location.search).get("id");
            const urlEnvio = esEdicion ? form.action + "?id=" + idProducto : form.action;

            btnGuardar.disabled = true;
            btnGuardar.textContent = "Procesando...";

            const respuesta = await fetch(urlEnvio, { method: "POST", body: datos });
            if (!respuesta.ok) throw new Error("Error HTTP: " + respuesta.status);

            const resultado = await respuesta.json();

            if (resultado.estado) {
                const titulo = esEdicion ? "¡Producto actualizado!" : "¡Producto registrado!";
                mostrarToast("success", titulo, "Redirigiendo a tu lista de productos...", 2000);
                setTimeout(() => {
                    window.location.href = RUTA_MIS_PRODUCTOS;
                }, 1200);
            } else {
                mostrarModal("error", "No se pudo guardar", resultado.mensaje);
            }
        } catch (error) {
            console.error(error);
            mostrarModal("error", "Error de conexión", "No se pudo completar la operación. Intenta de nuevo.");
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.textContent = textoBotonOriginal;
        }
    });
});