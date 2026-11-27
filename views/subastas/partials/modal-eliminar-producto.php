<!-- Modal Confirmar Eliminación de Producto -->
<div class="modal fade" id="modalEliminarProducto" tabindex="-1" aria-labelledby="modalEliminarProductoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-ppi">
            <div class="modal-header modal-header-ppi">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="modalEliminarProductoTitulo">
                    <i class="fas fa-trash-alt" style="color: #b55663;"></i>
                    <span>Eliminar producto</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-main-ppi mb-2">
                    ¿Estás seguro de que deseas eliminar <strong id="eliminarNombreProducto">este producto</strong>?
                </p>
                <p class="text-second-ppi small mb-0">
                    Esta acción no se puede deshacer y se eliminarán también las fotografías asociadas.
                </p>
            </div>
            <div class="modal-footer modal-footer-ppi d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-modificar-producto btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-eliminar-producto btn-sm" id="btnConfirmarEliminarProducto">
                    <i class="fas fa-trash me-1"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>
