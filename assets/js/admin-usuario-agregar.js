(function () {
    'use strict';

    const baseUrl = window.ADM_BASE_URL || '';
    const endpoint = `${baseUrl}controllers/admin_controller.php`;

    const modalEl = document.getElementById('modalAgregarUsuario');
    const formEl = document.getElementById('formAgregarUsuario');
    if (!modalEl || !formEl) return;

    const nombreInput = document.getElementById('admUserAddNombre');
    const apellidoInput = document.getElementById('admUserAddApellido');
    const correoInput = document.getElementById('admUserAddCorreo');
    const telefonoInput = document.getElementById('admUserAddTelefono');
    const ubicacionInput = document.getElementById('admUserAddUbicacion');
    const usernameInput = document.getElementById('admUserAddUsername');
    const rolSelect = document.getElementById('admUserAddRol');
    const estadoCheck = document.getElementById('admUserAddEstado');
    const estadoLabelEl = document.getElementById('admUserAddEstadoLabel');
    const estadoHelpEl = document.getElementById('admUserAddEstadoHelp');
    const passInput = document.getElementById('admUserAddPass');
    const passConfInput = document.getElementById('admUserAddPassConf');
    const btnGenerarClave = document.getElementById('btnGenerarClaveUsuario');
    const btnGuardar = document.getElementById('btnGuardarAgregarUsuario');
    const btnGuardarText = document.getElementById('btnGuardarAgregarUsuarioText');

    const modalConfirmarEl = document.getElementById('modalConfirmarAgregarUsuario');
    const confirmIntroEl = document.getElementById('admUserAddConfirmIntro');
    const confirmListEl = document.getElementById('admUserAddConfirmList');
    const btnVolverEl = document.getElementById('btnVolverAgregarUsuario');
    const btnConfirmarEl = document.getElementById('btnConfirmarAgregarUsuario');
    const btnConfirmarTextEl = document.getElementById('btnConfirmarAgregarUsuarioText');

    const passRulesEls = {
        len: formEl.querySelector('[data-pass-rule="len"]'),
        case: formEl.querySelector('[data-pass-rule="case"]'),
        num: formEl.querySelector('[data-pass-rule="num"]'),
        sym: formEl.querySelector('[data-pass-rule="sym"]')
    };

    const fieldInputs = {
        nombre: nombreInput,
        apellido: apellidoInput,
        correo: correoInput,
        telefono: telefonoInput,
        ubicacion: ubicacionInput,
        username: usernameInput,
        id_rol: rolSelect,
        contrasenia: passInput,
        confirmar_contrasenia: passConfInput
    };

    const REGEX_NOMBRE = /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s'-]{2,100}$/;
    const REGEX_USERNAME = /^[A-Za-z0-9._-]{3,50}$/;
    const REGEX_EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const REGEX_TELEFONO = /^\d{4}-\d{4}$/;
    const REGEX_PASSWORD = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s]).{8,}$/;

    let enviando = false;
    let validando = false;
    let datosPendientesCrear = null;
    let volverAlModalAgregar = false;
    let mantenerFormularioAlAbrir = false;

    function escHtml(valor) {
        return String(valor ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function getModal() {
        if (!window.bootstrap) return null;
        return window.bootstrap.Modal.getOrCreateInstance(modalEl);
    }

    function getConfirmarModal() {
        if (!modalConfirmarEl || !window.bootstrap) return null;
        return window.bootstrap.Modal.getOrCreateInstance(modalConfirmarEl);
    }

    function obtenerEtiquetaRol(idRol) {
        if (!rolSelect) return String(idRol);
        const opt = Array.from(rolSelect.options).find((o) => Number(o.value) === Number(idRol));
        return opt ? opt.textContent.trim() : String(idRol);
    }

    function notificar(tipo, titulo, mensaje, duracionMs = 4500) {
        if (typeof window.mostrarToast === 'function') {
            window.mostrarToast(tipo, titulo, mensaje, duracionMs);
        } else {
            window.AdminDashboard?.notify(mensaje, tipo, titulo);
        }
    }

    function limpiarErrores() {
        Object.values(fieldInputs).forEach((el) => el?.classList.remove('is-invalid'));
        formEl.querySelectorAll('[data-field-error]').forEach((el) => {
            el.textContent = '';
        });
    }

    function limpiarErrorCampo(campo) {
        fieldInputs[campo]?.classList.remove('is-invalid');
        const errEl = formEl.querySelector(`[data-field-error="${campo}"]`);
        if (errEl) errEl.textContent = '';
    }

    function mostrarError(mensaje, campo = '') {
        limpiarErrores();
        if (campo && fieldInputs[campo]) {
            const input = fieldInputs[campo];
            input.classList.add('is-invalid');
            const errEl = formEl.querySelector(`[data-field-error="${campo}"]`);
            if (errEl) errEl.textContent = mensaje;
            input.focus();
            return;
        }
        notificar('error', 'No se pudo crear', mensaje || 'Verifica los datos ingresados.', 5000);
    }

    function sincronizarEstado() {
        const activo = Boolean(estadoCheck?.checked);
        if (estadoLabelEl) {
            estadoLabelEl.textContent = activo ? 'Cuenta activa' : 'Cuenta suspendida';
        }
        if (estadoHelpEl) {
            estadoHelpEl.textContent = activo
                ? 'Podrá iniciar sesión de inmediato en la plataforma.'
                : 'Se creará bloqueada hasta que un administrador la active.';
        }
    }

    function actualizarReglasPassword(valor) {
        const pass = String(valor || '');
        passRulesEls.len?.classList.toggle('is-met', pass.length >= 8);
        passRulesEls.case?.classList.toggle('is-met', /[a-z]/.test(pass) && /[A-Z]/.test(pass));
        passRulesEls.num?.classList.toggle('is-met', /\d/.test(pass));
        passRulesEls.sym?.classList.toggle('is-met', /[^A-Za-z0-9\s]/.test(pass));
    }

    function setVisibilidadPassword(input, btn, mostrar) {
        if (!input || !btn) return;
        input.classList.toggle('adm-pass-masked', !mostrar);
        btn.setAttribute('aria-pressed', mostrar ? 'true' : 'false');
        btn.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
        const icon = btn.querySelector('i');
        if (icon) {
            icon.classList.toggle('fa-eye', !mostrar);
            icon.classList.toggle('fa-eye-slash', mostrar);
        }
    }

    function generarClaveSegura() {
        const minus = 'abcdefghijkmnopqrstuvwxyz';
        const mayus = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        const nums = '23456789';
        const syms = '@#$%&*!?._-';
        const todos = minus + mayus + nums + syms;

        const randomIndex = (max) => {
            const buf = new Uint32Array(1);
            window.crypto.getRandomValues(buf);
            return buf[0] % max;
        };

        const chars = [
            minus[randomIndex(minus.length)],
            mayus[randomIndex(mayus.length)],
            nums[randomIndex(nums.length)],
            syms[randomIndex(syms.length)]
        ];
        while (chars.length < 12) {
            chars.push(todos[randomIndex(todos.length)]);
        }
        for (let i = chars.length - 1; i > 0; i--) {
            const j = randomIndex(i + 1);
            [chars[i], chars[j]] = [chars[j], chars[i]];
        }
        return chars.join('');
    }

    function reiniciarFormulario() {
        formEl.reset();
        limpiarErrores();
        if (rolSelect) {
            const optUsuario = Array.from(rolSelect.options).find((o) => Number(o.value) === 2);
            if (optUsuario) rolSelect.value = '2';
        }
        if (estadoCheck) estadoCheck.checked = true;
        sincronizarEstado();
        actualizarReglasPassword('');

        formEl.querySelectorAll('[data-toggle-pass]').forEach((btn) => {
            const target = document.getElementById(btn.dataset.togglePass);
            setVisibilidadPassword(target, btn, false);
        });
    }

    Object.entries(fieldInputs).forEach(([campo, input]) => {
        if (!input) return;
        const evento = input.tagName === 'SELECT' ? 'change' : 'input';
        input.addEventListener(evento, () => limpiarErrorCampo(campo));
    });

    telefonoInput?.addEventListener('input', () => {
        let valor = telefonoInput.value.replace(/\D/g, '').substring(0, 8);
        if (valor.length > 4) {
            valor = `${valor.substring(0, 4)}-${valor.substring(4)}`;
        }
        telefonoInput.value = valor;
    });

    passInput?.addEventListener('input', () => {
        actualizarReglasPassword(passInput.value);
    });

    estadoCheck?.addEventListener('change', sincronizarEstado);

    formEl.querySelectorAll('[data-toggle-pass]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const target = document.getElementById(btn.dataset.togglePass);
            if (!target) return;
            setVisibilidadPassword(target, btn, target.classList.contains('adm-pass-masked'));
        });
    });

    btnGenerarClave?.addEventListener('click', () => {
        const clave = generarClaveSegura();
        if (passInput) passInput.value = clave;
        if (passConfInput) passConfInput.value = clave;
        actualizarReglasPassword(clave);
        limpiarErrorCampo('contrasenia');
        limpiarErrorCampo('confirmar_contrasenia');

        // Mostrar temporalmente la clave generada para que el administrador pueda copiarla
        formEl.querySelectorAll('[data-toggle-pass]').forEach((btn) => {
            const target = document.getElementById(btn.dataset.togglePass);
            setVisibilidadPassword(target, btn, true);
        });
    });

    modalEl.addEventListener('show.bs.modal', () => {
        if (mantenerFormularioAlAbrir) {
            mantenerFormularioAlAbrir = false;
            return;
        }
        reiniciarFormulario();
    });
    modalEl.addEventListener('shown.bs.modal', () => nombreInput?.focus());

    function validarCamposCliente() {
        const nombre = (nombreInput?.value || '').trim();
        const apellido = (apellidoInput?.value || '').trim();
        const correo = (correoInput?.value || '').trim();
        const telefono = (telefonoInput?.value || '').trim();
        const ubicacion = (ubicacionInput?.value || '').trim();
        const username = (usernameInput?.value || '').trim().replace(/^@+/, '');
        const contrasenia = passInput?.value || '';
        const confirmar = passConfInput?.value || '';

        if (!nombre) {
            mostrarError('El nombre es obligatorio.', 'nombre');
            return null;
        }
        if (!REGEX_NOMBRE.test(nombre)) {
            mostrarError('Ingresa un nombre válido (solo letras, mínimo 2 caracteres).', 'nombre');
            return null;
        }
        if (!apellido) {
            mostrarError('El apellido es obligatorio.', 'apellido');
            return null;
        }
        if (!REGEX_NOMBRE.test(apellido)) {
            mostrarError('Ingresa un apellido válido (solo letras, mínimo 2 caracteres).', 'apellido');
            return null;
        }
        if (!correo || correo.length > 100 || !REGEX_EMAIL.test(correo)) {
            mostrarError('Ingresa un correo electrónico válido.', 'correo');
            return null;
        }
        if (telefono && !REGEX_TELEFONO.test(telefono)) {
            mostrarError('Usa el formato de 8 dígitos: 0000-0000.', 'telefono');
            return null;
        }
        if (ubicacion && (ubicacion.length < 3 || ubicacion.length > 150 || !/[A-Za-zÁÉÍÓÚáéíóúÑñÜü]/.test(ubicacion))) {
            mostrarError('Ingresa una ubicación válida (mínimo 3 caracteres).', 'ubicacion');
            return null;
        }
        if (!REGEX_USERNAME.test(username)) {
            mostrarError('Usa entre 3 y 50 caracteres válidos (letras, números, punto o guion).', 'username');
            return null;
        }
        if (!REGEX_PASSWORD.test(contrasenia)) {
            mostrarError('Debe tener mínimo 8 caracteres, mayúscula, minúscula, número y símbolo.', 'contrasenia');
            return null;
        }
        if (contrasenia !== confirmar) {
            mostrarError('Las contraseñas no coinciden.', 'confirmar_contrasenia');
            return null;
        }

        return {
            nombre,
            apellido,
            correo,
            telefono,
            ubicacion,
            username,
            id_rol: String(Number(rolSelect?.value || 2)),
            estado: estadoCheck?.checked ? '1' : '0',
            contrasenia,
            confirmar_contrasenia: confirmar
        };
    }

    function abrirConfirmacionCreacion(datos) {
        datosPendientesCrear = datos;
        volverAlModalAgregar = false;

        const nombreCompleto = `${datos.nombre} ${datos.apellido}`.trim();
        if (confirmIntroEl) {
            confirmIntroEl.innerHTML = `¿Deseas registrar la cuenta de <strong class="text-white">${escHtml(nombreCompleto)}</strong> con los siguientes datos?`;
        }

        const filas = [
            { etiqueta: 'Nombre completo', valor: nombreCompleto },
            { etiqueta: 'Usuario', valor: `@${datos.username}` },
            { etiqueta: 'Correo', valor: datos.correo },
            { etiqueta: 'Rol', valor: obtenerEtiquetaRol(datos.id_rol) },
            { etiqueta: 'Estado inicial', valor: datos.estado === '1' ? 'Cuenta activa' : 'Cuenta suspendida' }
        ];
        if (datos.telefono) {
            filas.push({ etiqueta: 'Teléfono', valor: datos.telefono });
        }
        if (datos.ubicacion) {
            filas.push({ etiqueta: 'Ubicación', valor: datos.ubicacion });
        }

        if (confirmListEl) {
            confirmListEl.innerHTML = filas.map((f) => `
                <div class="adm-user-confirm-row">
                    <span class="adm-user-confirm-label">${escHtml(f.etiqueta)}</span>
                    <div class="adm-user-confirm-values">
                        <span class="adm-user-confirm-new">${escHtml(f.valor)}</span>
                    </div>
                </div>
            `).join('');
        }

        const agregarModal = getModal();
        const confirmModal = getConfirmarModal();
        if (agregarModal && confirmModal) {
            modalEl.addEventListener('hidden.bs.modal', () => {
                confirmModal.show();
            }, { once: true });
            agregarModal.hide();
        } else {
            confirmModal?.show();
        }
    }

    async function ejecutarCreacionUsuario() {
        if (enviando || !datosPendientesCrear) return;

        const datos = datosPendientesCrear;
        enviando = true;
        if (btnConfirmarEl) btnConfirmarEl.disabled = true;
        if (btnVolverEl) btnVolverEl.disabled = true;
        if (btnConfirmarTextEl) btnConfirmarTextEl.textContent = 'Creando...';

        try {
            const formData = new FormData();
            formData.append('opcion', 'usuario_crear');
            Object.entries(datos).forEach(([clave, valor]) => formData.append(clave, valor));

            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData,
                headers: { Accept: 'application/json' },
                cache: 'no-store'
            });

            if (response.status === 401) {
                const errData = await response.json().catch(() => ({}));
                window.location.href = errData.redirect || `${baseUrl}views/auth/login.php${errData.expired ? '?reason=inactivity' : ''}`;
                return;
            }

            const payload = await response.json().catch(() => null);
            if (!response.ok || !payload?.estado) {
                const msg = payload?.mensaje || 'No se pudo registrar el usuario.';
                const campo = payload?.campo || '';
                const confirmModal = getConfirmarModal();
                if (modalConfirmarEl && confirmModal) {
                    modalConfirmarEl.addEventListener('hidden.bs.modal', () => {
                        mantenerFormularioAlAbrir = true;
                        getModal()?.show();
                        setTimeout(() => mostrarError(msg, campo), 150);
                    }, { once: true });
                    confirmModal.hide();
                } else {
                    mostrarError(msg, campo);
                }
                return;
            }

            datosPendientesCrear = null;
            getConfirmarModal()?.hide();
            notificar('success', 'Panel de Administración', payload.mensaje || 'Usuario creado correctamente.', 4500);
            window.AdminUsuarios?.recargar();
            window.AdminKpis?.actualizar?.();
            window.AdminResumenInicio?.actualizar?.();
        } catch (err) {
            console.error('Error al crear usuario:', err);
            notificar('error', 'Error de conexión', 'No se pudo completar el registro. Intenta nuevamente.', 5000);
        } finally {
            enviando = false;
            if (btnConfirmarEl) btnConfirmarEl.disabled = false;
            if (btnVolverEl) btnVolverEl.disabled = false;
            if (btnConfirmarTextEl) btnConfirmarTextEl.textContent = 'Confirmar y crear';
        }
    }

    btnVolverEl?.addEventListener('click', () => {
        volverAlModalAgregar = true;
        getConfirmarModal()?.hide();
    });

    modalConfirmarEl?.addEventListener('hidden.bs.modal', () => {
        if (volverAlModalAgregar) {
            volverAlModalAgregar = false;
            mantenerFormularioAlAbrir = true;
            getModal()?.show();
        }
    });

    btnConfirmarEl?.addEventListener('click', ejecutarCreacionUsuario);

    formEl.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (enviando || validando) return;

        const datos = validarCamposCliente();
        if (!datos) return;

        limpiarErrores();
        validando = true;
        if (btnGuardar) btnGuardar.disabled = true;
        if (btnGuardarText) btnGuardarText.textContent = 'Validando...';

        try {
            const formData = new FormData();
            formData.append('opcion', 'usuario_validar_creacion');
            Object.entries(datos).forEach(([clave, valor]) => formData.append(clave, valor));

            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData,
                headers: { Accept: 'application/json' },
                cache: 'no-store'
            });

            if (response.status === 401) {
                const errData = await response.json().catch(() => ({}));
                window.location.href = errData.redirect || `${baseUrl}views/auth/login.php${errData.expired ? '?reason=inactivity' : ''}`;
                return;
            }

            const payload = await response.json().catch(() => null);
            if (!response.ok || !payload?.estado) {
                mostrarError(payload?.mensaje || 'Revisa los datos ingresados.', payload?.campo || '');
                return;
            }

            abrirConfirmacionCreacion(datos);
        } catch (err) {
            console.error('Error al validar creación de usuario:', err);
            notificar('error', 'Error de conexión', 'No se pudieron validar los datos. Intenta nuevamente.', 5000);
        } finally {
            validando = false;
            if (btnGuardar) btnGuardar.disabled = false;
            if (btnGuardarText) btnGuardarText.textContent = 'Crear usuario';
        }
    });
})();
