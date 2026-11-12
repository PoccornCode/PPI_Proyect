const SwalPPI = Swal.mixin({
    customClass: {
        popup: "swal-ppi-popup",
        title: "swal-ppi-title",
        htmlContainer: "swal-ppi-html",
        confirmButton: "btn swal-ppi-confirm-btn"
    },
    buttonsStyling: false
});

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

                SwalPPI.fire({
                    icon: 'warning',
                    title: 'Imágenes requeridas',
                    text: 'Debes subir al menos una fotografía del artículo para la subasta.'
                });
                return;
            }

            const precioInicial = parseFloat(inputPrecioInicial.value);
            const incremento = parseFloat(inputIncremento.value);

            if (isNaN(precioInicial) || precioInicial <= 0) {
                marcarInvalido(inputPrecioInicial);
                SwalPPI.fire({
                    icon: 'warning',
                    title: 'Precio inicial inválido',
                    text: 'El precio inicial debe ser un monto mayor a $0.00 USD.'
                });
                inputPrecioInicial.focus();
                return;
            }

            if (isNaN(incremento) || incremento <= 0) {
                marcarInvalido(inputIncremento);
                SwalPPI.fire({
                    icon: 'warning',
                    title: 'Incremento mínimo inválido',
                    text: 'El incremento mínimo de puja debe ser un valor positivo mayor a $0.00 USD.'
                });
                inputIncremento.focus();
                return;
            }

            const fechaInicio = new Date(inputFechaInicial.value);
            const fechaFin = new Date(inputFechaFinal.value);
            const fechaActual = new Date();

            if (isNaN(fechaInicio.getTime())) {
                marcarInvalido(inputFechaInicial);
                SwalPPI.fire({
                    icon: 'warning',
                    title: 'Fecha de inicio requerida',
                    text: 'Por favor define una fecha y hora de inicio válida.'
                });
                inputFechaInicial.focus();
                return;
            }

            if (isNaN(fechaFin.getTime())) {
                marcarInvalido(inputFechaFinal);
                SwalPPI.fire({
                    icon: 'warning',
                    title: 'Fecha de cierre requerida',
                    text: 'Por favor define una fecha y hora de cierre válida.'
                });
                inputFechaFinal.focus();
                return;
            }

            if (fechaFin <= fechaInicio) {
                marcarInvalido(inputFechaFinal);
                SwalPPI.fire({
                    icon: 'warning',
                    title: 'Rango de fechas inválido',
                    text: 'La fecha y hora de cierre debe ser posterior a la fecha de inicio.'
                });
                inputFechaFinal.focus();
                return;
            }

            if (hayErrores && primerError) {
                SwalPPI.fire({
                    icon: 'warning',
                    title: 'Campos incompletos',
                    text: 'Por favor completa todos los campos requeridos marcados en el formulario.'
                });
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
