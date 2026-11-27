<!-- MODAL CONFIRMAR CAMBIOS DE CATEGORÍA (ADMIN) -->
<div class="modal fade adm-modal" id="modalConfirmarEditarCategoria" tabindex="-1" aria-labelledby="modalConfirmarEditarCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered adm-user-confirm-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-white m-0" id="modalConfirmarEditarCategoriaLabel">
                    Confirmar cambios
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="adm-user-confirm-intro mb-3" id="admCatConfirmIntro"></p>
                <div class="adm-user-confirm-list" id="admCatConfirmList"></div>
            </div>
            <div class="modal-footer justify-content-end gap-2">
                <button type="button" class="mod-btn-sm mod-btn-cancel px-3" id="admCatConfirmBackBtn" style="height:36px; font-size:0.86rem;">
                    Volver a editar
                </button>
                <button type="button" class="adm-btn-primary" id="admCatConfirmSaveBtn" style="height:36px; font-size:0.86rem;">
                    <i class="fa-solid fa-check"></i>
                    <span>Confirmar y guardar</span>
                </button>
            </div>
        </div>
    </div>
</div>
