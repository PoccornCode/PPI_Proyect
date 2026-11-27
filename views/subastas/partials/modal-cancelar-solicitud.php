<!-- Modal Confirmar Cancelación de Solicitud -->
<div class="modal fade" id="modalCancelarSolicitud" tabindex="-1" aria-labelledby="modalCancelarSolicitudTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-ppi">
            <div class="modal-header modal-header-ppi">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="modalCancelarSolicitudTitulo">
                    <i class="fas fa-times-circle" style="color: #b55663;"></i>
                    <span>Cancelar solicitud</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-main-ppi mb-2">
                    ¿Seguro que deseas cancelar la solicitud de subasta de <strong id="cancelarNombreProducto">este producto</strong>?
                </p>
                <p class="text-second-ppi small mb-0">
                    La subasta quedará como cancelada. Podrás volver a subastar el producto cuando quieras.
                </p>
            </div>
            <div class="modal-footer modal-footer-ppi d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-modificar-producto btn-sm" data-bs-dismiss="modal">Volver</button>
                <button type="button" class="btn btn-eliminar-producto btn-sm" id="btnConfirmarCancelarSolicitud">
                    <i class="fas fa-times me-1"></i> Cancelar solicitud
                </button>
            </div>
        </div>
    </div>
</div>