<!-- MODAL CONFIRMAR APROBACIÓN O RECHAZO DE SUBASTA (ADMIN) -->
<div class="modal fade adm-modal" id="modalConfirmarSubasta" tabindex="-1" aria-labelledby="modalConfirmarSubastaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-white m-0" id="modalConfirmarSubastaLabel">
                    Confirmar acción
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-2">
                <p class="mb-0" id="admAuctionConfirmMessage" style="color:#d6ddd0; font-size:0.93rem; line-height:1.55;"></p>

                <div id="admAuctionConfirmMotivoWrap" class="mt-3 d-none">
                    <label for="admAuctionConfirmMotivo" class="mod-detail-desc-label d-block mb-2">
                        Motivo de rechazo (opcional)
                    </label>
                    <textarea
                        id="admAuctionConfirmMotivo"
                        class="mod-confirm-textarea"
                        rows="3"
                        placeholder="Describe brevemente el motivo del rechazo..."></textarea>
                    <div class="mod-sub-meta mt-1">
                        Este motivo se mostrará al vendedor en Mis Productos.
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end gap-2">
                <button type="button" class="mod-btn-sm mod-btn-cancel" style="height:36px; font-size:0.86rem;" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="mod-btn-sm mod-btn-approve" style="height:36px; font-size:0.86rem;" id="admAuctionConfirmSubmitBtn">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
