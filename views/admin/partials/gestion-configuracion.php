<!-- Sección: Configuración del sistema -->
<section class="adm-section" id="sec-configuracion">
    <div class="adm-section-head adm-rep-head">
        <div class="adm-title-group">
            <span class="adm-title-accent" aria-hidden="true"></span>
            <div>
                <h1>Configuración</h1>
                <p>Ajusta las reglas de subastas, seguridad y operación de Gavel<span class="brand-inline">.net</span>.</p>
            </div>
        </div>

        <div class="adm-rep-controls">
            <span class="adm-cfg-status" id="admCfgStatus">Sin cambios pendientes</span>
            <button type="button" class="adm-rep-btn adm-rep-btn--ghost" id="admCfgResetBtn" disabled>
                Descartar
            </button>
            <button type="button" class="adm-rep-btn adm-rep-btn--export" id="admCfgSaveBtn" disabled>
                <i class="fa-solid fa-check"></i>
                <span>Guardar cambios</span>
            </button>
        </div>
    </div>

    <!-- Pestañas de configuración -->
    <div class="adm-rep-nav-wrap">
        <div class="adm-rep-tabs" role="tablist" aria-label="Grupos de configuración">
            <button type="button" class="adm-rep-tab active" role="tab" aria-selected="true" aria-controls="cfg-panel-subastas" id="cfg-tab-subastas" data-cfg-tab="subastas">
                Subastas y pujas
            </button>
            <button type="button" class="adm-rep-tab" role="tab" aria-selected="false" aria-controls="cfg-panel-seguridad" id="cfg-tab-seguridad" data-cfg-tab="seguridad">
                Seguridad y cuentas
            </button>
            <button type="button" class="adm-rep-tab" role="tab" aria-selected="false" aria-controls="cfg-panel-correo" id="cfg-tab-correo" data-cfg-tab="correo">
                Correo y avisos
            </button>
            <button type="button" class="adm-rep-tab" role="tab" aria-selected="false" aria-controls="cfg-panel-operacion" id="cfg-tab-operacion" data-cfg-tab="operacion">
                Operación
            </button>
        </div>
    </div>

    <form id="admCfgForm" novalidate>
        <!-- PESTAÑA 1: SUBASTAS Y PUJAS -->
        <div class="adm-cfg-pane active" id="cfg-panel-subastas" role="tabpanel" aria-labelledby="cfg-tab-subastas">
            <div class="adm-cfg-group">
                <div class="adm-cfg-aside">
                    <h2>Publicación y límites</h2>
                    <p>Define las condiciones base que deben cumplir los artículos antes de salir al catálogo público.</p>
                </div>
                <div class="adm-cfg-card">
                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgRevisionPrevia" class="adm-cfg-label">Revisión previa de moderación</label>
                            <span class="adm-cfg-help">Requiere que un moderador apruebe cada publicación antes de activarse.</span>
                        </div>
                        <label class="adm-switch">
                            <input type="checkbox" id="cfgRevisionPrevia" name="revision_previa" checked>
                            <span class="adm-switch-slider"></span>
                        </label>
                    </div>

                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgPrecioMin" class="adm-cfg-label">Precio inicial mínimo</label>
                            <span class="adm-cfg-help">Monto base más bajo permitido al crear una nueva subasta.</span>
                        </div>
                        <div class="adm-cfg-input-prefix">
                            <span>$</span>
                            <input type="number" id="cfgPrecioMin" name="precio_minimo" value="5.00" min="1" step="0.50">
                        </div>
                    </div>

                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgIncMin" class="adm-cfg-label">Incremento mínimo sugerido</label>
                            <span class="adm-cfg-help">Valor predeterminado entre una puja y la siguiente.</span>
                        </div>
                        <div class="adm-cfg-input-prefix">
                            <span>$</span>
                            <input type="number" id="cfgIncMin" name="incremento_minimo" value="2.00" min="0.50" step="0.50">
                        </div>
                    </div>

                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgDuracionMax" class="adm-cfg-label">Duración máxima permitida</label>
                            <span class="adm-cfg-help">Tiempo tope desde la apertura hasta el cierre automático.</span>
                        </div>
                        <select class="adm-cfg-select" id="cfgDuracionMax" name="duracion_maxima">
                            <option value="3">3 días</option>
                            <option value="7" selected>7 días</option>
                            <option value="14">14 días</option>
                            <option value="30">30 días</option>
                        </select>
                    </div>

                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgMaxFotos" class="adm-cfg-label">Imágenes máximas por artículo</label>
                            <span class="adm-cfg-help">Cantidad de fotografías que puede subir el vendedor por publicación.</span>
                        </div>
                        <select class="adm-cfg-select" id="cfgMaxFotos" name="max_imagenes">
                            <option value="3">Hasta 3 fotos</option>
                            <option value="5" selected>Hasta 5 fotos</option>
                            <option value="8">Hasta 8 fotos</option>
                            <option value="10">Hasta 10 fotos</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="adm-cfg-group">
                <div class="adm-cfg-aside">
                    <h2>Dinámica de cierre</h2>
                    <p>Reglas para mantener pujas justas en los últimos minutos y controlar cancelaciones.</p>
                </div>
                <div class="adm-cfg-card">
                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgAntiSniping" class="adm-cfg-label">Extensión de cierre (anti-sniping)</label>
                            <span class="adm-cfg-help">Añade 2 minutos extra si entra una puja durante el último minuto.</span>
                        </div>
                        <label class="adm-switch">
                            <input type="checkbox" id="cfgAntiSniping" name="anti_sniping" checked>
                            <span class="adm-switch-slider"></span>
                        </label>
                    </div>

                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgCancelSinPujas" class="adm-cfg-label">Cancelación por vendedor sin ofertas</label>
                            <span class="adm-cfg-help">Permite retirar una subasta activa únicamente si todavía no tiene pujas.</span>
                        </div>
                        <label class="adm-switch">
                            <input type="checkbox" id="cfgCancelSinPujas" name="cancelar_sin_pujas" checked>
                            <span class="adm-switch-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- PESTAÑA 2: SEGURIDAD Y CUENTAS -->
        <div class="adm-cfg-pane" id="cfg-panel-seguridad" role="tabpanel" aria-labelledby="cfg-tab-seguridad">
            <div class="adm-cfg-group">
                <div class="adm-cfg-aside">
                    <h2>Registro y verificación</h2>
                    <p>Controla cómo se validan las cuentas nuevas y los códigos enviados por correo.</p>
                </div>
                <div class="adm-cfg-card">
                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgRegistroPublico" class="adm-cfg-label">Permitir nuevos registros</label>
                            <span class="adm-cfg-help">Habilita o pausa la creación de cuentas desde el sitio público.</span>
                        </div>
                        <label class="adm-switch">
                            <input type="checkbox" id="cfgRegistroPublico" name="registro_publico" checked>
                            <span class="adm-switch-slider"></span>
                        </label>
                    </div>

                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgVerifCorreo" class="adm-cfg-label">Verificación obligatoria de correo</label>
                            <span class="adm-cfg-help">Solicita confirmar un código de 6 dígitos antes de activar la cuenta.</span>
                        </div>
                        <label class="adm-switch">
                            <input type="checkbox" id="cfgVerifCorreo" name="verificacion_correo" checked>
                            <span class="adm-switch-slider"></span>
                        </label>
                    </div>

                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgExpiraCodigo" class="adm-cfg-label">Vigencia del código de verificación</label>
                            <span class="adm-cfg-help">Tiempo antes de que expire un código de registro o recuperación.</span>
                        </div>
                        <select class="adm-cfg-select" id="cfgExpiraCodigo" name="expira_codigo">
                            <option value="5">5 minutos</option>
                            <option value="10">10 minutos</option>
                            <option value="15" selected>15 minutos</option>
                            <option value="30">30 minutos</option>
                        </select>
                    </div>

                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgMaxIntentos" class="adm-cfg-label">Intentos máximos por código</label>
                            <span class="adm-cfg-help">Invalida el código automáticamente al superar este límite.</span>
                        </div>
                        <select class="adm-cfg-select" id="cfgMaxIntentos" name="max_intentos">
                            <option value="3">3 intentos</option>
                            <option value="5" selected>5 intentos</option>
                            <option value="8">8 intentos</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="adm-cfg-group">
                <div class="adm-cfg-aside">
                    <h2>Sesiones activas</h2>
                    <p>Define cuánto tiempo permanece abierta una sesión sin actividad.</p>
                </div>
                <div class="adm-cfg-card">
                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgInactividad" class="adm-cfg-label">Cierre por inactividad</label>
                            <span class="adm-cfg-help">Muestra el aviso de extensión y cierra la sesión al vencer el tiempo.</span>
                        </div>
                        <select class="adm-cfg-select" id="cfgInactividad" name="tiempo_inactividad">
                            <option value="15">15 minutos</option>
                            <option value="30" selected>30 minutos</option>
                            <option value="60">60 minutos</option>
                            <option value="0">Sin límite</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- PESTAÑA 3: CORREO Y AVISOS -->
        <div class="adm-cfg-pane" id="cfg-panel-correo" role="tabpanel" aria-labelledby="cfg-tab-correo">
            <div class="adm-cfg-group">
                <div class="adm-cfg-aside">
                    <h2>Identidad de envío</h2>
                    <p>Datos visibles en los correos automáticos y en el formulario de contacto.</p>
                </div>
                <div class="adm-cfg-card">
                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgRemitente" class="adm-cfg-label">Nombre del remitente</label>
                            <span class="adm-cfg-help">Nombre que ven los usuarios en su bandeja de entrada.</span>
                        </div>
                        <input type="text" class="adm-cfg-input" id="cfgRemitente" name="nombre_remitente" value="Gavel.net">
                    </div>

                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgCorreoSoporte" class="adm-cfg-label">Correo de soporte</label>
                            <span class="adm-cfg-help">Dirección de respuesta para dudas y mensajes del sitio.</span>
                        </div>
                        <input type="email" class="adm-cfg-input" id="cfgCorreoSoporte" name="correo_soporte" value="soporte@gavel.net">
                    </div>
                </div>
            </div>

            <div class="adm-cfg-group">
                <div class="adm-cfg-aside">
                    <h2>Notificaciones automáticas</h2>
                    <p>Elige qué eventos disparan un aviso por correo electrónico.</p>
                </div>
                <div class="adm-cfg-card">
                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgAvisoModeracion" class="adm-cfg-label">Resultado de moderación al vendedor</label>
                            <span class="adm-cfg-help">Notificar por correo cuando una subasta es aprobada o rechazada.</span>
                        </div>
                        <label class="adm-switch">
                            <input type="checkbox" id="cfgAvisoModeracion" name="aviso_moderacion" checked>
                            <span class="adm-switch-slider"></span>
                        </label>
                    </div>

                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgAvisoContacto" class="adm-cfg-label">Alerta de nuevos mensajes de contacto</label>
                            <span class="adm-cfg-help">Enviar copia al correo de soporte cuando alguien escribe desde Contacto.</span>
                        </div>
                        <label class="adm-switch">
                            <input type="checkbox" id="cfgAvisoContacto" name="aviso_contacto" checked>
                            <span class="adm-switch-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- PESTAÑA 4: OPERACIÓN -->
        <div class="adm-cfg-pane" id="cfg-panel-operacion" role="tabpanel" aria-labelledby="cfg-tab-operacion">
            <div class="adm-cfg-group">
                <div class="adm-cfg-aside">
                    <h2>Aviso en portada</h2>
                    <p>Muestra una franja breve en el sitio público para comunicados puntuales.</p>
                </div>
                <div class="adm-cfg-card">
                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgBannerActivo" class="adm-cfg-label">Mostrar aviso global</label>
                            <span class="adm-cfg-help">Visible en la parte superior del catálogo para todos los visitantes.</span>
                        </div>
                        <label class="adm-switch">
                            <input type="checkbox" id="cfgBannerActivo" name="banner_activo">
                            <span class="adm-switch-slider"></span>
                        </label>
                    </div>
                    <div class="adm-cfg-row adm-cfg-row--stacked is-collapsed" id="cfgBannerTextRow">
                        <label for="cfgBannerTexto" class="adm-cfg-label">Texto del aviso</label>
                        <input type="text" class="adm-cfg-input adm-cfg-input--full" id="cfgBannerTexto" name="banner_texto" maxlength="140" value="Nuevas subastas verificadas disponibles este fin de semana." placeholder="Escribe un mensaje breve...">
                    </div>
                </div>
            </div>

            <div class="adm-cfg-group">
                <div class="adm-cfg-aside">
                    <h2>Estado del servicio</h2>
                    <p>Úsalo solo cuando necesites pausar la plataforma para mantenimiento planificado.</p>
                </div>
                <div class="adm-cfg-card adm-cfg-card--delicate" id="cfgMaintCard">
                    <div class="adm-cfg-row">
                        <div class="adm-cfg-row-info">
                            <label for="cfgMantenimiento" class="adm-cfg-label">Modo mantenimiento</label>
                            <span class="adm-cfg-help">Pausa temporalmente nuevas pujas y publicaciones; los administradores mantienen acceso.</span>
                        </div>
                        <label class="adm-switch adm-switch--salmon">
                            <input type="checkbox" id="cfgMantenimiento" name="modo_mantenimiento">
                            <span class="adm-switch-slider"></span>
                        </label>
                    </div>
                    <div class="adm-cfg-row adm-cfg-row--stacked is-collapsed" id="cfgMaintMsgRow">
                        <label for="cfgMaintMsg" class="adm-cfg-label">Mensaje para visitantes</label>
                        <textarea class="adm-cfg-textarea" id="cfgMaintMsg" name="mensaje_mantenimiento" rows="2" maxlength="220">Estamos realizando mejoras en Gavel.net. Volvemos en unos minutos.</textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
