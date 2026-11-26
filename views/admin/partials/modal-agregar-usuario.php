<?php
$rolesListaModal = $rolesAdmin ?? [];
$rolPredeterminado = 2;
foreach ($rolesListaModal as $rolItem) {
    if ((int)$rolItem['id_rol'] === 2) {
        $rolPredeterminado = 2;
        break;
    }
}
?>
<div class="modal fade adm-modal" id="modalAgregarUsuario" tabindex="-1" aria-labelledby="modalAgregarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered adm-user-detail-dialog">
        <div class="modal-content">
            <div class="modal-header align-items-start">
                <div>
                    <h5 class="modal-title fw-bold text-white m-0" id="modalAgregarUsuarioLabel">
                        Agregar usuario
                    </h5>
                    <p class="adm-user-add-subtitle mb-0">Registra una cuenta nueva y define su rol de acceso en Gavel.net.</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formAgregarUsuario" autocomplete="off" novalidate>
                <div class="modal-body">
                    <div class="adm-user-detail-body">
                        <div class="adm-user-edit-section">
                            <span class="mod-detail-desc-label mb-0">Datos personales y contacto</span>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="admUserAddNombre" class="form-label small text-secondary mb-1">Nombre <span class="adm-req-star">*</span></label>
                                    <input type="text" class="form-control" id="admUserAddNombre" name="nombre" maxlength="100" placeholder="Ingresa el nombre" autocomplete="off" required>
                                    <div class="adm-field-error" data-field-error="nombre"></div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="admUserAddApellido" class="form-label small text-secondary mb-1">Apellido <span class="adm-req-star">*</span></label>
                                    <input type="text" class="form-control" id="admUserAddApellido" name="apellido" maxlength="100" placeholder="Ingresa el apellido" autocomplete="off" required>
                                    <div class="adm-field-error" data-field-error="apellido"></div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="admUserAddCorreo" class="form-label small text-secondary mb-1">Correo electrónico <span class="adm-req-star">*</span></label>
                                    <input type="email" class="form-control" id="admUserAddCorreo" name="correo" maxlength="100" placeholder="correo@ejemplo.com" autocomplete="off" required>
                                    <div class="adm-field-error" data-field-error="correo"></div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="admUserAddTelefono" class="form-label small text-secondary mb-1">Teléfono</label>
                                    <input type="tel" class="form-control" id="admUserAddTelefono" name="telefono" maxlength="9" inputmode="numeric" placeholder="Ej. 7000-0000" autocomplete="off">
                                    <div class="adm-field-error" data-field-error="telefono"></div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="admUserAddUbicacion" class="form-label small text-secondary mb-1">Ubicación</label>
                                    <input type="text" class="form-control" id="admUserAddUbicacion" name="ubicacion" maxlength="150" placeholder="Ej. San Salvador, El Salvador" autocomplete="off">
                                    <div class="adm-field-error" data-field-error="ubicacion"></div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="admUserAddUsername" class="form-label small text-secondary mb-1">Nombre de usuario <span class="adm-req-star">*</span></label>
                                    <input type="text" class="form-control" id="admUserAddUsername" name="adm_new_user_alias" maxlength="50" placeholder="Elige un usuario único" autocomplete="off" spellcheck="false" autocapitalize="off" autocorrect="off" required>
                                    <div class="adm-field-error" data-field-error="username"></div>
                                </div>
                            </div>
                        </div>

                        <div class="adm-user-detail-divider"></div>

                        <div class="adm-user-edit-section">
                            <span class="mod-detail-desc-label mb-0">Permisos y acceso</span>

                            <div class="row g-3 align-items-start">
                                <div class="col-sm-6">
                                    <label for="admUserAddRol" class="form-label small text-secondary mb-1">Rol <span class="adm-req-star">*</span></label>
                                    <select class="form-select" id="admUserAddRol" name="id_rol" required>
                                        <?php foreach ($rolesListaModal as $rolOpt): ?>
                                            <option value="<?= (int)$rolOpt['id_rol'] ?>" <?= (int)$rolOpt['id_rol'] === $rolPredeterminado ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($rolOpt['etiqueta']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="adm-field-error" data-field-error="id_rol"></div>
                                </div>
                                <div class="col-sm-6">
                                    <span class="form-label small text-secondary d-block mb-1">Estado inicial</span>
                                    <div class="adm-user-edit-status-inline">
                                        <label class="adm-switch mb-0">
                                            <input type="checkbox" id="admUserAddEstado" name="estado" value="1" checked>
                                            <span class="adm-switch-slider"></span>
                                        </label>
                                        <div class="adm-user-edit-status-copy">
                                            <label for="admUserAddEstado" class="fw-semibold text-white small mb-0" id="admUserAddEstadoLabel">Cuenta activa</label>
                                            <span id="admUserAddEstadoHelp">Podrá iniciar sesión de inmediato en la plataforma.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="adm-user-detail-divider"></div>

                        <div class="adm-user-edit-section">
                            <div class="adm-user-add-sec-head">
                                <span class="mod-detail-desc-label mb-0">Seguridad</span>
                                <button type="button" class="adm-btn-ghost-xs" id="btnGenerarClaveUsuario" title="Generar una contraseña segura automáticamente">
                                    <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i>
                                    <span>Generar clave</span>
                                </button>
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="admUserAddPass" class="form-label small text-secondary mb-1">Contraseña <span class="adm-req-star">*</span></label>
                                    <div class="adm-pass-wrap">
                                        <input type="text" class="form-control adm-pass-masked" id="admUserAddPass" name="adm_new_user_pass" minlength="8" placeholder="Mínimo 8 caracteres" autocomplete="off" spellcheck="false" autocapitalize="off" autocorrect="off" required>
                                        <button type="button" class="adm-pass-toggle" data-toggle-pass="admUserAddPass" aria-label="Mostrar contraseña" aria-pressed="false">
                                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <div class="adm-field-error" data-field-error="contrasenia"></div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="admUserAddPassConf" class="form-label small text-secondary mb-1">Confirmar contraseña <span class="adm-req-star">*</span></label>
                                    <div class="adm-pass-wrap">
                                        <input type="text" class="form-control adm-pass-masked" id="admUserAddPassConf" name="adm_new_user_pass_conf" minlength="8" placeholder="Repite la contraseña" autocomplete="off" spellcheck="false" autocapitalize="off" autocorrect="off" required>
                                        <button type="button" class="adm-pass-toggle" data-toggle-pass="admUserAddPassConf" aria-label="Mostrar confirmación de contraseña" aria-pressed="false">
                                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <div class="adm-field-error" data-field-error="confirmar_contrasenia"></div>
                                </div>
                            </div>

                            <div class="adm-pass-rules" id="admPassRules" aria-live="polite">
                                <span class="adm-pass-rule" data-pass-rule="len"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> 8+ caracteres</span>
                                <span class="adm-pass-rule" data-pass-rule="case"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Mayúscula y minúscula</span>
                                <span class="adm-pass-rule" data-pass-rule="num"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Número</span>
                                <span class="adm-pass-rule" data-pass-rule="sym"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Símbolo</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-end gap-2">
                    <button type="button" class="mod-btn-sm mod-btn-cancel px-3" style="height:36px;" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="adm-btn-primary" id="btnGuardarAgregarUsuario" style="height:36px; font-size:0.86rem;">
                        <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                        <span id="btnGuardarAgregarUsuarioText">Crear usuario</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade adm-modal" id="modalConfirmarAgregarUsuario" tabindex="-1" aria-labelledby="modalConfirmarAgregarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered adm-user-confirm-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-white m-0" id="modalConfirmarAgregarUsuarioLabel">
                    Confirmar nuevo usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="adm-user-confirm-intro mb-3" id="admUserAddConfirmIntro">
                    Revisa los datos antes de registrar la nueva cuenta.
                </p>
                <div class="adm-user-confirm-list" id="admUserAddConfirmList"></div>
            </div>
            <div class="modal-footer justify-content-end gap-2">
                <button type="button" class="mod-btn-sm mod-btn-cancel px-3" id="btnVolverAgregarUsuario" style="height:36px;">
                    Volver a editar
                </button>
                <button type="button" class="adm-btn-primary" id="btnConfirmarAgregarUsuario" style="height:36px; font-size:0.86rem;">
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                    <span id="btnConfirmarAgregarUsuarioText">Confirmar y crear</span>
                </button>
            </div>
        </div>
    </div>
</div>
