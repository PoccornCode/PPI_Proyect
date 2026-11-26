<?php
$opcionesEstadoModal = [
    'activa' => [
        'titulo' => 'Activa',
        'desc'   => 'Aprobar o reactivar una subasta',
        'tono'   => 'activa',
    ],
    'pendiente' => [
        'titulo' => 'Pendiente',
        'desc'   => 'Devolver a cola de validación',
        'tono'   => 'pendiente',
    ],
    'rechazada' => [
        'titulo' => 'Rechazada',
        'desc'   => 'No cumple requisitos',
        'tono'   => 'rechazada',
    ],
    'cancelada' => [
        'titulo' => 'Cancelada',
        'desc'   => 'Retirar del catálogo por reporte o incumplimiento',
        'tono'   => 'cancelada',
    ],
];
?>
<div class="modal fade adm-modal" id="modalAdministrarEstado" tabindex="-1" aria-labelledby="modalAdministrarEstadoTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold text-white m-0" id="modalAdministrarEstadoTitle">
                        Cambiar estado
                    </h5>
                    <div class="mod-status-modal-sub" id="modManageModalItemName"></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-2 pb-3">
                <div class="mod-status-list" id="modManageStatusList" role="radiogroup" aria-label="Seleccionar nuevo estado">
                    <?php foreach ($opcionesEstadoModal as $clave => $opt): ?>
                        <label class="mod-status-row" data-status-row="<?= htmlspecialchars($clave) ?>">
                            <input
                                type="radio"
                                class="mod-status-radio"
                                name="modManageStatusRadio"
                                value="<?= htmlspecialchars($clave) ?>">
                            <span class="mod-status-control" aria-hidden="true"></span>
                            <span class="mod-status-row-text">
                                <span class="mod-status-row-title">
                                    <?= htmlspecialchars($opt['titulo']) ?>
                                </span>
                                <span class="mod-status-row-desc"><?= htmlspecialchars($opt['desc']) ?></span>
                            </span>
                            <span class="mod-status-current-label">Actual</span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div id="modManageMotivoWrap" class="mt-3 d-none">
                    <label for="modManageMotivo" class="mod-detail-desc-label d-block mb-2" id="modManageMotivoLabel">
                        Motivo (opcional)
                    </label>
                    <textarea
                        id="modManageMotivo"
                        class="mod-confirm-textarea"
                        rows="3"
                        placeholder="Describe brevemente el motivo..."></textarea>
                    <div class="mod-sub-meta mt-1">
                        Este motivo se enviará al vendedor en una próxima actualización.
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end gap-2">
                <button type="button" class="mod-btn-sm mod-btn-cancel" style="height:36px; font-size:0.86rem;" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="mod-btn-sm mod-btn-approve" style="height:36px; font-size:0.86rem;" id="modManageModalSaveBtn" disabled>
                    Guardar cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAR CAMBIO DE ESTADO (MODERADOR) -->
<div class="modal fade adm-modal" id="modalConfirmarAdministrarEstado" tabindex="-1" aria-labelledby="modalConfirmarAdministrarEstadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered adm-user-confirm-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-white m-0" id="modalConfirmarAdministrarEstadoLabel">
                    Confirmar cambio de estado
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="adm-user-confirm-intro mb-3" id="modManageConfirmIntro">
                    Revisa el cambio antes de actualizar la subasta.
                </p>
                <div class="adm-user-confirm-list" id="modManageConfirmList"></div>
            </div>
            <div class="modal-footer justify-content-end gap-2">
                <button type="button" class="mod-btn-sm mod-btn-cancel px-3" id="modManageConfirmBackBtn" style="height:36px; font-size:0.86rem;">
                    Volver a editar
                </button>
                <button type="button" class="adm-btn-primary" id="modManageConfirmSaveBtn" style="height:36px; font-size:0.86rem;">
                    <i class="fa-solid fa-check"></i>
                    <span id="modManageConfirmSaveBtnText">Confirmar y guardar</span>
                </button>
            </div>
        </div>
    </div>
</div>
