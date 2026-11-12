let modalInstance = null;
let callbackModal = null;

let form, btnGuardar, textoBotonOriginal;
let inputNombre, selectCategoria, selectCondicion, textareaDescripcion;
let inputImagenes, inputPrecioInicial, inputIncremento, inputFechaInicial, inputFechaFinal;
let camposValidables = [];

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
        icon = '<i class="bi bi-check-circle-fill text-success fs-5"></i>';
        btnAccion.textContent = "Continuar";
    } else if (tipo === "danger" || tipo === "error") {
        icon = '<i class="bi bi-x-circle-fill text-danger fs-5"></i>';
        btnAccion.className = "btn btn-ppi-modal btn-danger btn-sm";
        btnAccion.textContent = "Entendido";
    } else {
        icon = '<i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>';
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
    if (!campo) return true;
    const valor = campo.value.trim();
    const esValido = valor !== '' && valor !== '0';

    if (esValido) {
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
    } else {
        campo.classList.remove('is-valid');
        campo.classList.add('is-invalid');
    }
    return esValido;
}

function marcarInvalido(campo) {
    if (!campo) return;
    campo.classList.remove('is-valid');
    campo.classList.add('is-invalid');
}


function inicializarFechasPorDefecto(inputInicio, inputFin) {
    if (inputInicio && !inputInicio.value) {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        inputInicio.value = now.toISOString().slice(0, 16);
    }

    if (inputFin && !inputFin.value) {
        const closingDate = new Date();
        closingDate.setDate(closingDate.getDate() + 3);
        closingDate.setMinutes(closingDate.getMinutes() - closingDate.getTimezoneOffset());
        inputFin.value = closingDate.toISOString().slice(0, 16);
    }
}


function inicializarDropzone(inputImagenes) {
    const dropzone = document.getElementById('dropzoneImagenes');
    const container = document.getElementById('imagePreviewContainer');

    if (dropzone && inputImagenes) {
        dropzone.addEventListener('click', () => inputImagenes.click());
    }

    if (!inputImagenes || !container) return;

    inputImagenes.addEventListener('change', function (event) {
        container.innerHTML = '';
        const files = event.target.files;

        if (!files || files.length === 0) return;

        if (dropzone) dropzone.style.borderColor = '#23271f';

        Array.from(files).forEach((file, index) => {
            if (!file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                const box = document.createElement('div');
                box.className = 'preview-img-box';

                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = file.name;
                box.appendChild(img);

                if (index === 0) {
                    const badge = document.createElement('span');
                    badge.className = 'badge-principal';
                    badge.textContent = 'Principal';
                    box.appendChild(badge);
                }

                container.appendChild(box);
            };
            reader.readAsDataURL(file);
        });
    });
}

function limpiarFormulario() {
    form.reset();
    camposValidables.forEach(campo => campo.classList.remove('is-valid', 'is-invalid'));
    document.getElementById('imagePreviewContainer').innerHTML = '';
    inicializarFechasPorDefecto(inputFechaInicial, inputFechaFinal);
}

function validarFormulario() {
    const validos = camposValidables.map(campo => validarCampoIndividual(campo));
    if (validos.includes(false)) {
        mostrarModal("warning", "Campos incompletos", "Completa los campos requeridos marcados en el formulario.");
        return false;
    }

    if (inputImagenes.files.length === 0) {
        document.getElementById("dropzoneImagenes").style.borderColor = "#dc3545";
        mostrarModal("warning", "Imágenes requeridas", "Debes subir al menos una fotografía del artículo.");
        return false;
    }

    const precio = parseFloat(inputPrecioInicial.value);
    if (isNaN(precio) || precio <= 0) {
        marcarInvalido(inputPrecioInicial);
        mostrarModal("warning", "Precio inicial inválido", "El precio inicial debe ser mayor a $0.00 USD.");
        return false;
    }

    const incremento = parseFloat(inputIncremento.value);
    if (isNaN(incremento) || incremento <= 0) {
        marcarInvalido(inputIncremento);
        mostrarModal("warning", "Incremento mínimo inválido", "El incremento debe ser mayor a $0.00 USD.");
        return false;
    }

    const fechaInicio = new Date(inputFechaInicial.value);
    const fechaFin = new Date(inputFechaFinal.value);
    if (isNaN(fechaInicio.getTime()) || isNaN(fechaFin.getTime()) || fechaFin <= fechaInicio) {
        marcarInvalido(inputFechaFinal);
        mostrarModal("warning", "Rango de fechas inválido", "La fecha de cierre debe ser posterior a la de apertura.");
        return false;
    }

    return true;
} 

document.addEventListener("DOMContentLoaded", function () {
    form = document.getElementById("formRegistroSubasta");
    btnGuardar = document.getElementById("btnGuardar");
    textoBotonOriginal = btnGuardar.textContent.trim();

    inputNombre = document.getElementById("nombre");
    selectCategoria = document.getElementById("id_categoria");
    selectCondicion = document.getElementById("condicion");
    textareaDescripcion = document.getElementById("descripcion");
    inputImagenes = document.getElementById("imagenes");
    inputPrecioInicial = document.getElementById("precio_inicial");
    inputIncremento = document.getElementById("incremento_minimo");
    inputFechaInicial = document.getElementById("fecha_inicial");
    inputFechaFinal = document.getElementById("fecha_final");

    camposValidables = [
        inputNombre,
        selectCategoria,
        selectCondicion,
        textareaDescripcion,
        inputPrecioInicial,
        inputIncremento,
        inputFechaInicial,
        inputFechaFinal
    ];

    inicializarFechasPorDefecto(inputFechaInicial, inputFechaFinal);
    inicializarDropzone(inputImagenes);

    camposValidables.forEach(campo => {
        campo.addEventListener("blur", () => validarCampoIndividual(campo));
        campo.addEventListener("input", () => {
            if (campo.classList.contains("is-invalid")) validarCampoIndividual(campo);
        });
        campo.addEventListener("change", () => {
            if (campo.classList.contains("is-invalid")) validarCampoIndividual(campo);
        });
    });

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        if (btnGuardar.textContent.trim() !== textoBotonOriginal) return;
        if (!validarFormulario()) return;

        try {
            const datos = new FormData(form);
            datos.append("opcion", "guardar");

            btnGuardar.disabled = true;
            btnGuardar.textContent = "Procesando...";

            const respuesta = await fetch(form.action, { method: "POST", body: datos });
            if (!respuesta.ok) throw new Error("Error HTTP: " + respuesta.status);

            const resultado = await respuesta.json();

            if (resultado.estado) {
                mostrarModal("success", "Subasta publicada", resultado.mensaje, limpiarFormulario);
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