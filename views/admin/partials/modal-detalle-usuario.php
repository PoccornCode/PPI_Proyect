<!-- MODAL DETALLE DE USUARIO (ADMIN) -->
<div class="modal fade adm-modal" id="modalDetalleUsuario" tabindex="-1" aria-labelledby="modalDetalleUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered adm-user-detail-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="modal-title fw-bold text-white m-0" id="modalDetalleUsuarioLabel">
                        Detalle de usuario
                    </h5>
                    <span class="adm-user-detail-id" id="admUserDetailId">#0</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="adm-user-detail-state" id="admUserDetailLoading">
                    <div class="spinner-border spinner-border-sm" style="color: var(--adm-accent);" role="status" aria-hidden="true"></div>
                    <span>Cargando información del usuario...</span>
                </div>

                <div class="adm-user-detail-state adm-user-detail-state--error d-none" id="admUserDetailError">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                    <span id="admUserDetailErrorText">No se pudo cargar la información del usuario.</span>
                </div>

                <div class="adm-user-detail-body d-none" id="admUserDetailContent">
                    <div class="adm-user-detail-hero">
                        <div class="adm-user-detail-avatar" id="admUserDetailAvatar" aria-hidden="true">U</div>
                        <div class="adm-user-detail-identity">
                            <h4 class="text-white fw-bold mb-0" id="admUserDetailName">—</h4>
                            <div class="adm-user-detail-username" id="admUserDetailUsername">@usuario</div>
                            <div class="adm-user-detail-badges">
                                <span class="adm-badge adm-badge--usuario" id="admUserDetailRole">Pujador / Subastador</span>
                                <span class="adm-status" id="admUserDetailStatusWrap">
                                    <span class="adm-status-dot adm-status-dot--active" id="admUserDetailStatusDot"></span>
                                    <span id="admUserDetailStatus">Activo</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="adm-user-detail-divider"></div>

                    <div class="mod-detail-specs adm-user-detail-specs">
                        <div class="mod-detail-spec">
                            <div class="mod-detail-spec-label">Correo electrónico</div>
                            <div class="mod-detail-spec-val" id="admUserDetailEmail">—</div>
                        </div>
                        <div class="mod-detail-spec">
                            <div class="mod-detail-spec-label">Teléfono</div>
                            <div class="mod-detail-spec-val" id="admUserDetailPhone">—</div>
                        </div>
                        <div class="mod-detail-spec">
                            <div class="mod-detail-spec-label">Ubicación</div>
                            <div class="mod-detail-spec-val" id="admUserDetailLocation">—</div>
                        </div>
                        <div class="mod-detail-spec">
                            <div class="mod-detail-spec-label">Fecha de registro</div>
                            <div class="mod-detail-spec-val" id="admUserDetailRegistered">—</div>
                        </div>
                    </div>

                    <div class="adm-user-detail-divider"></div>

                    <div class="adm-user-detail-activity">
                        <div class="adm-user-detail-activity-head">
                            <span class="mod-detail-desc-label mb-0">Actividad en la plataforma</span>
                            <span class="adm-user-detail-last-bid" id="admUserDetailLastBid">Última puja: Sin pujas</span>
                        </div>
                        <div class="adm-user-detail-stats">
                            <div class="adm-user-detail-stat">
                                <span class="adm-user-detail-stat-val" id="admUserDetailStatProductos">0</span>
                                <span class="adm-user-detail-stat-lbl">Productos</span>
                            </div>
                            <div class="adm-user-detail-stat">
                                <span class="adm-user-detail-stat-val" id="admUserDetailStatActivas">0</span>
                                <span class="adm-user-detail-stat-lbl">Activas</span>
                            </div>
                            <div class="adm-user-detail-stat">
                                <span class="adm-user-detail-stat-val" id="admUserDetailStatPendientes">0</span>
                                <span class="adm-user-detail-stat-lbl">Pendientes</span>
                            </div>
                            <div class="adm-user-detail-stat">
                                <span class="adm-user-detail-stat-val" id="admUserDetailStatPujas">0</span>
                                <span class="adm-user-detail-stat-lbl">Pujas</span>
                            </div>
                            <div class="adm-user-detail-stat">
                                <span class="adm-user-detail-stat-val" id="admUserDetailStatFinalizadas">0</span>
                                <span class="adm-user-detail-stat-lbl">Finalizadas</span>
                            </div>
                            <div class="adm-user-detail-stat">
                                <span class="adm-user-detail-stat-val" id="admUserDetailStatSinOfertas">0</span>
                                <span class="adm-user-detail-stat-lbl">Sin ofertas</span>
                            </div>
                            <div class="adm-user-detail-stat">
                                <span class="adm-user-detail-stat-val" id="admUserDetailStatCanceladas">0</span>
                                <span class="adm-user-detail-stat-lbl">Canceladas</span>
                            </div>
                            <div class="adm-user-detail-stat">
                                <span class="adm-user-detail-stat-val" id="admUserDetailStatRechazadas">0</span>
                                <span class="adm-user-detail-stat-lbl">Rechazadas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="mod-btn-sm mod-btn-cancel px-3 d-none" id="admUserDetailEditShortcut" style="height:36px;">
                    <i class="fa-solid fa-pen-to-square"></i> Editar usuario
                </button>
                <button type="button" class="adm-btn-primary ms-auto" style="height:36px; font-size:0.86rem;" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>