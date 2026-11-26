let esEdicion = false;
let enviando = false;
let formCrearSubasta, btnCrearSubasta, textoBotonOriginal;
let camposValidables = [];

function validarCampoIndividual(campo) {
    const valor = campo.value.trim();
    let esValido = valor !== "" && Number(valor) > 0;

    if (campo.id === "dias") {
        esValido = /^[0-9]+$/.test(valor) && Number(valor) >= 1;
    }

    campo.classList.toggle("is-invalid", !esValido);
    campo.classList.toggle("is-valid", esValido);
    return esValido;
}

function validarFormulario() {
    const validos = camposValidables.map(campo => validarCampoIndividual(campo));
    if (validos.includes(false)) {
        mostrarToast("warning", "Campos incompletos", "Completa los campos marcados en el formulario.", 4000);
        return false;
    }
    return true;
}

function mascaraMoneda(campo) {
    campo.addEventListener("input", () => {
        const digitos = campo.value.replace(/\D/g, "").slice(0, 10);
        const centavos = Number(digitos || "0");
        campo.value = (centavos / 100).toFixed(2);
    });
}

document.addEventListener("DOMContentLoaded", function () {
    formCrearSubasta = document.getElementById("formCrearSubasta");
    if (!formCrearSubasta) return;

    const precio_inicial = document.getElementById("precio_inicial");
    const incremento_minimo = document.getElementById("incremento_minimo");
    const dias = document.getElementById("dias");
    btnCrearSubasta = document.getElementById("btnCrearSubasta");
    textoBotonOriginal = btnCrearSubasta.textContent.trim();
    esEdicion = (textoBotonOriginal === "Modificar");

    mascaraMoneda(precio_inicial);
    mascaraMoneda(incremento_minimo);

    dias.addEventListener("keydown", e => {
        if (["-", "+", "e", "E", ".", ","].includes(e.key)) e.preventDefault();
    });

    camposValidables = [precio_inicial, incremento_minimo, dias];

    camposValidables.forEach(campo => {
        campo.addEventListener("blur", () => validarCampoIndividual(campo));
        campo.addEventListener("change", () => {
            if (campo.classList.contains("is-invalid")) validarCampoIndividual(campo);
        });
        campo.addEventListener("input", () => {
            if (campo.classList.contains("is-invalid")) validarCampoIndividual(campo);
        });
    });

    formCrearSubasta.addEventListener("submit", async function (e) {
        e.preventDefault();
        if (enviando) {
            return;
        }

        if (!validarFormulario()) {
            return;
        }

        enviando = true;
        btnCrearSubasta.disabled = true;
        btnCrearSubasta.textContent = "Procesando...";

        try {
            const datosForm = new FormData(formCrearSubasta);
            const respuesta = await fetch(formCrearSubasta.action, { method: "POST", body: datosForm });

            if (!respuesta.ok) {
                throw new Error("Error HTTP: " + respuesta.status);
            }

            const resultado = await respuesta.json();

            if (resultado.estado) {
                btnCrearSubasta.textContent = "Redirigiendo...";
                const titulo = esEdicion ? "¡Subasta modificada!" : "¡Subasta creada!";
                mostrarToast("success", titulo, "Redirigiendo a tu lista de productos...", 2000);
                setTimeout(() => {
                    window.location.href = RUTA_MIS_PRODUCTOS;
                }, 1200);
                return;
            }

            mostrarToast("error", (esEdicion ? "No se pudo modificar" : "No se pudo guardar"), resultado.mensaje, 4000);
        } catch (error) {
            console.error(error);
            mostrarToast("error", "Error de conexión", "No se pudo completar la operación. Intenta de nuevo.", 4000);
        }

        enviando = false;
        btnCrearSubasta.disabled = false;
        btnCrearSubasta.textContent = textoBotonOriginal;
    });
});