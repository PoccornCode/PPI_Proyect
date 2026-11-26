<?php
$rolesListaModal = $rolesAdmin ?? [];
?>
<div class="modal fade adm-modal" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered adm-user-detail-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="modal-title fw-bold text-white m-0" id="modalEditarUsuarioLabel">
                        Editar usuario
                    </h5>
                    <span class="adm-user-detail-id" id="admUserEditIdBadge">#0</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formEditarUsuario" novalidate>
                <input type="hidden" id="admUserEditId" name="id_usuario" value="0">

                <div class="modal-body">
                    <div class="adm-user-detail-state" id="admUserEditLoading">
                        <div class="spinner-border spinner-border-sm" style="color: var(--adm-accent);" role="status" aria-hidden="true"></div>
                        <span>Cargando datos del usuario...</span>
                    </div>

                    <div class="adm-user-detail-state adm-user-detail-state--error d-none" id="admUserEditErrorLoad">
                        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                        <span id="admUserEditErrorLoadText">No se pudo cargar la información del usuario.</span>
                    </div>

                    <div class="adm-user-detail-body d-none" id="admUserEditContent">
                        <div class="adm-user-detail-hero">
                            <div class="adm-user-detail-avatar" id="admUserEditAvatar" aria-hidden="true">U</div>
                            <div class="adm-user-detail-identity">
                                <h4 class="text-white fw-bold mb-0" id="admUserEditHeaderName">—</h4>
                                <div class="adm-user-edit-subline" id="admUserEditHeaderSub">@usuario · Registrado —</div>
                            </div>
                        </div>

                        <div class="adm-user-detail-divider"></div>

                        <div class="adm-user-edit-section">
                            <span class="mod-detail-desc-label mb-0">Permisos y acceso</span>

                            <div class="row g-3 align-items-start">
                                <div class="col-sm-6">
                                    <label for="admUserEditRol" class="form-label small text-secondary mb-1">Rol</label>
                                    <select class="form-select" id="admUserEditRol" name="id_rol" required>
                                        <?php foreach ($rolesListaModal as $rolOpt): ?>
                                            <option value="<?= (int)$rolOpt['id_rol'] ?>"><?= htmlspecialchars($rolOpt['etiqueta']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="adm-field-error" data-field-error="id_rol"></div>
                                </div>
                                <div class="col-sm-6">
                                    <span class="form-label small text-secondary d-block mb-1">Estado de la cuenta</span>
                                    <div class="adm-user-edit-status-inline">
                                        <label class="adm-switch mb-0">
                                            <input type="checkbox" id="admUserEditEstado" name="estado" value="1" checked>
                                            <span class="adm-switch-slider"></span>
                                        </label>
                                        <div class="adm-user-edit-status-copy">
                                            <label for="admUserEditEstado" class="fw-semibold text-white small mb-0" id="admUserEditEstadoLabel">Cuenta activa</label>
                                            <span id="admUserEditEstadoHelp">Puede iniciar sesión y operar con normalidad.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="adm-user-edit-lock-note d-none" id="admUserEditLockNote">
                                <span id="admUserEditLockNoteText"></span>
                            </div>
                        </div>

                        <div class="adm-user-detail-divider"></div>

                        <div class="adm-user-edit-section">
                            <span class="mod-detail-desc-label mb-0">Datos del usuario</span>

                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <label for="admUserEditNombre" class="form-label small text-secondary mb-1">Nombre <span class="adm-req-star">*</span></label>
                                    <input type="text" class="form-control" id="admUserEditNombre" name="nombre" maxlength="100" required>
                                    <div class="adm-field-error" data-field-error="nombre"></div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="admUserEditApellido" class="form-label small text-secondary mb-1">Apellido <span class="adm-req-star">*</span></label>
                                    <input type="text" class="form-control" id="admUserEditApellido" name="apellido" maxlength="100" required>
                                    <div class="adm-field-error" data-field-error="apellido"></div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="admUserEditUsername" class="form-label small text-secondary mb-1">Nombre de usuario <span class="adm-req-star">*</span></label>
                                    <input type="text" class="form-control" id="admUserEditUsername" name="username" maxlength="50" required>
                                    <div class="adm-field-error" data-field-error="username"></div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="admUserEditCorreo" class="form-label small text-secondary mb-1">Correo electrónico <span class="adm-req-star">*</span></label>
                                    <input type="email" class="form-control" id="admUserEditCorreo" name="correo" maxlength="100" required>
                                    <div class="adm-field-error" data-field-error="correo"></div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="admUserEditTelefono" class="form-label small text-secondary mb-1">Teléfono</label>
                                    <input type="tel" class="form-control" id="admUserEditTelefono" name="telefono" maxlength="9" inputmode="numeric" placeholder="Ej. 7000-0000">
                                    <div class="adm-field-error" data-field-error="telefono"></div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="admUserEditUbicacion" class="form-label small text-secondary mb-1">Ubicación</label>
                                    <input type="text" class="form-control" id="admUserEditUbicacion" name="ubicacion" maxlength="150" placeholder="Ej. San Salvador, El Salvador">
                                    <div class="adm-field-error" data-field-error="ubicacion"></div>
                                </div>
                            </div>
                        </div>

                        <div class="adm-user-detail-divider"></div>

                        <div class="adm-user-recovery-row">
                            <div class="adm-user-recovery-copy">
                                <div class="adm-user-recovery-title">
                                    Restablecer contraseña
                                </div>
                                <p class="adm-user-recovery-desc">
                                    Envía un correo con un enlace directo al usuario para que defina una nueva clave.
                                </p>
                            </div>
                            <button type="button" class="adm-user-recovery-btn" id="btnEnviarCodigoRecuperacion">
                                <i class="fa-regular fa-paper-plane"></i>
                                <span id="btnEnviarCodigoRecText">Enviar enlace</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-end gap-2">
                    <button type="button" class="mod-btn-sm mod-btn-cancel px-3" style="height:36px;" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="adm-btn-primary" id="btnGuardarEditarUsuario" style="height:36px; font-size:0.86rem;">
                        <i class="fa-solid fa-check"></i>
                        <span id="btnGuardarEditarUsuarioText">Guardar cambios</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade adm-modal" id="modalConfirmarEditarUsuario" tabindex="-1" aria-labelledby="modalConfirmarEditarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered adm-user-confirm-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-white m-0" id="modalConfirmarEditarUsuarioLabel">
                    Confirmar cambios
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="adm-user-confirm-intro mb-3" id="admUserConfirmIntro">
                    Revisa los cambios antes de actualizar la cuenta.
                </p>
                <div class="adm-user-confirm-list" id="admUserConfirmList"></div>
            </div>
            <div class="modal-footer justify-content-end gap-2">
                <button type="button" class="mod-btn-sm mod-btn-cancel px-3" id="btnVolverEditarUsuario" style="height:36px;">
                    Volver a editar
                </button>
                <button type="button" class="adm-btn-primary" id="btnConfirmarEditarUsuario" style="height:36px; font-size:0.86rem;">
                    <i class="fa-solid fa-check"></i>
                    <span id="btnConfirmarEditarUsuarioText">Confirmar y guardar</span>
                </button>
            </div>
        </div>
    </div>
</div>
