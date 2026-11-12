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

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formRegistroSubasta');
    const inputNombre = document.getElementById('nombre');
    const selectCategoria = document.getElementById('id_categoria');
    const selectCondicion = document.getElementById('condicion');
    const textareaDescripcion = document.getElementById('descripcion');
    const inputImagenes = document.getElementById('imagenes');
    const inputPrecioInicial = document.getElementById('precio_inicial');
    const inputIncremento = document.getElementById('incremento_minimo');
    const inputFechaInicial = document.getElementById('fecha_inicial');
    const inputFechaFinal = document.getElementById('fecha_final');

    inicializarFechasPorDefecto(inputFechaInicial, inputFechaFinal);
    inicializarDropzone(inputImagenes);

    const camposValidables = [
        inputNombre,
        selectCategoria,
        selectCondicion,
        textareaDescripcion,
        inputPrecioInicial,
        inputIncremento,
        inputFechaInicial,
        inputFechaFinal
    ];

    camposValidables.forEach(campo => {
        if (!campo) return;
        campo.addEventListener('blur', () => validarCampoIndividual(campo));
        campo.addEventListener('input', () => {
            if (campo.classList.contains('is-invalid')) {
                validarCampoIndividual(campo);
            }
        });
        campo.addEventListener('change', () => {
            if (campo.classList.contains('is-invalid')) {
                validarCampoIndividual(campo);
            }
        });
    });


    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            let hayErrores = false;
            let primerError = null;

            camposValidables.forEach(campo => {
                const esValido = validarCampoIndividual(campo);
                if (!esValido) {
                    hayErrores = true;
                    if (!primerError) primerError = campo;
                }
            });

            if (!inputImagenes || !inputImagenes.files || inputImagenes.files.length === 0) {
                const dropzone = document.getElementById('dropzoneImagenes');
                if (dropzone) dropzone.style.borderColor = '#dc3545';

                mostrarModal('warning', 'Imágenes requeridas', 'Debes subir al menos una fotografía del artículo para la subasta.');
                return;
            }

            const precioInicial = parseFloat(inputPrecioInicial.value);
            const incremento = parseFloat(inputIncremento.value);

            if (isNaN(precioInicial) || precioInicial <= 0) {
                marcarInvalido(inputPrecioInicial);
                mostrarModal('warning', 'Precio inicial inválido', 'El precio inicial debe ser un monto mayor a $0.00 USD.');
                inputPrecioInicial.focus();
                return;
            }

            if (isNaN(incremento) || incremento <= 0) {
                marcarInvalido(inputIncremento);
                mostrarModal('warning', 'Incremento mínimo inválido', 'El incremento mínimo de puja debe ser un valor positivo mayor a $0.00 USD.');
                inputIncremento.focus();
                return;
            }

            const fechaInicio = new Date(inputFechaInicial.value);
            const fechaFin = new Date(inputFechaFinal.value);
            const fechaActual = new Date();

            if (isNaN(fechaInicio.getTime())) {
                marcarInvalido(inputFechaInicial);
                mostrarModal('warning', 'Fecha de inicio requerida', 'Por favor define una fecha y hora de inicio válida.');
                inputFechaInicial.focus();
                return;
            }

            if (isNaN(fechaFin.getTime())) {
                marcarInvalido(inputFechaFinal);
                mostrarModal('warning', 'Fecha de cierre requerida', 'Por favor define una fecha y hora de cierre válida.');
                inputFechaFinal.focus();
                return;
            }

            if (fechaFin <= fechaInicio) {
                marcarInvalido(inputFechaFinal);
                mostrarModal('warning', 'Rango de fechas inválido', 'La fecha y hora de cierre debe ser posterior a la fecha de inicio.');
                inputFechaFinal.focus();
                return;
            }

            if (hayErrores && primerError) {
                mostrarModal('warning', 'Campos incompletos', 'Por favor completa todos los campos requeridos marcados en el formulario.');
                primerError.focus();
                return;
            }

            form.submit();
        });
    }
});

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
