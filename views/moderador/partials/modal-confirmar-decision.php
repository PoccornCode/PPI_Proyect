<!-- MODAL CONFIRMAR APROBACIÓN O RECHAZO -->
<div class="modal fade adm-modal" id="modalModConfirmar" tabindex="-1" aria-labelledby="modalModConfirmarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-white m-0" id="modalModConfirmarLabel">
                    Confirmar acción
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-2">
                <p class="mb-0" id="modConfirmMessage" style="color:#d6ddd0; font-size:0.93rem; line-height:1.55;"></p>

                <div id="modConfirmMotivoWrap" class="mt-3 d-none">
                    <label for="modConfirmMotivo" class="mod-detail-desc-label d-block mb-2">
                        Motivo de rechazo (opcional)
                    </label>
                    <textarea
                        id="modConfirmMotivo"
                        class="mod-confirm-textarea"
                        rows="3"
                        placeholder="Describe brevemente el motivo del rechazo..."></textarea>
                    <div class="mod-sub-meta mt-1">
                        Este motivo se enviará al vendedor en una próxima actualización.
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end gap-2">
                <button type="button" class="mod-btn-sm mod-btn-cancel" style="height:36px; font-size:0.86rem;" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="mod-btn-sm mod-btn-approve" style="height:36px; font-size:0.86rem;" id="modConfirmSubmitBtn">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
