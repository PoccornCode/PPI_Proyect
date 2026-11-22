<!-- MODAL CONFIRMAR CIERRE DE SESIÓN -->
<div class="modal fade adm-modal" id="modalConfirmLogout" tabindex="-1" aria-labelledby="modalConfirmLogoutLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-white m-0" id="modalConfirmLogoutLabel">
                    Cerrar sesión
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-2">
                <p class="mb-0" style="color:#d6ddd0; font-size:0.93rem; line-height:1.55;">
                    ¿Estás seguro de que deseas salir de tu cuenta de <strong class="text-white">Gavel.net</strong>?
                </p>
            </div>
            <div class="modal-footer justify-content-end gap-2">
                <button type="button" class="mod-btn-sm mod-btn-cancel" style="height:36px; font-size:0.86rem;" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <a href="<?= $base_url ?>controllers/logout.php" class="mod-btn-sm mod-btn-reject text-decoration-none" style="height:36px; font-size:0.86rem;">
                    <i class="fa-solid fa-right-from-bracket"></i> Sí, cerrar sesión
                </a>
            </div>
        </div>
    </div>
</div>
