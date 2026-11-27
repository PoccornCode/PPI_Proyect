<?php
$categoriasModalProd = $categoriasAdmin ?? [];
$condicionesModalProd = [
    'Nuevo',
    'Como nuevo',
    'Excelente',
    'Buen estado',
    'Aceptable',
    'Para repuestos o restauración',
];
?>
<!-- MODAL EDITAR PRODUCTO (ADMIN) -->
<div class="modal fade adm-modal" id="modalEditarProducto" tabindex="-1" aria-labelledby="modalEditarProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 540px;">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold text-white m-0" id="modalEditarProductoLabel">Editar producto</h5>
                    <div class="mod-status-modal-sub" id="admProdEditSub"></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="admProdEditForm" novalidate>
                <input type="hidden" id="admProdEditId" name="id_producto" value="0">
                <div class="modal-body">
                    <div class="adm-user-detail-state" id="admProdEditLoading">
                        <div class="spinner-border spinner-border-sm" style="color: var(--adm-accent);" role="status" aria-hidden="true"></div>
                        <span>Cargando datos del producto...</span>
                    </div>

                    <div class="adm-user-detail-state adm-user-detail-state--error d-none" id="admProdEditError">
                        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                        <span id="admProdEditErrorText">No se pudo cargar el producto.</span>
                    </div>

                    <div class="d-flex flex-column gap-3 d-none" id="admProdEditFields">
                        <div>
                            <label for="admProdEditNombre" class="form-label small text-secondary">Nombre del producto <span style="color:#d8685d;">*</span></label>
                            <input type="text" class="form-control" id="admProdEditNombre" name="nombre" maxlength="150" required>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="admProdEditCategoria" class="form-label small text-secondary">Categoría <span style="color:#d8685d;">*</span></label>
                                <select class="form-select" id="admProdEditCategoria" name="id_categoria" required>
                                    <?php foreach ($categoriasModalProd as $cat): ?>
                                        <option value="<?= (int)$cat['id_categoria'] ?>">
                                            <?= htmlspecialchars($cat['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="admProdEditCondicion" class="form-label small text-secondary">Condición <span style="color:#d8685d;">*</span></label>
                                <select class="form-select" id="admProdEditCondicion" name="condicion" required>
                                    <?php foreach ($condicionesModalProd as $cond): ?>
                                        <option value="<?= htmlspecialchars($cond) ?>">
                                            <?= htmlspecialchars($cond) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="admProdEditDescripcion" class="form-label small text-secondary">Descripción</label>
                            <textarea class="form-control" id="admProdEditDescripcion" name="descripcion" rows="3" maxlength="1000"></textarea>
                        </div>

                        <div class="adm-cat-publish-box">
                            <div>
                                <div class="fw-semibold text-white small" id="admProdEditEstadoTitle">Producto disponible</div>
                                <div style="font-size:0.76rem; color:#9aa392;" id="admProdEditEstadoHelp">Visible y habilitado para que el vendedor pueda gestionarlo.</div>
                            </div>
                            <label class="adm-switch mb-0">
                                <input type="checkbox" id="admProdEditEstado" name="estado" value="1" checked>
                                <span class="adm-switch-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-end gap-2">
                    <button type="button" class="mod-btn-sm mod-btn-cancel px-3" style="height:36px; font-size:0.86rem;" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="adm-btn-primary" id="admProdEditSaveBtn" style="height:36px; font-size:0.86rem;" disabled>
                        <i class="fa-solid fa-check"></i>
                        <span>Guardar cambios</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAR CAMBIOS DE PRODUCTO (ADMIN) -->
<div class="modal fade adm-modal" id="modalConfirmarEditarProducto" tabindex="-1" aria-labelledby="modalConfirmarEditarProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered adm-user-confirm-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-white m-0" id="modalConfirmarEditarProductoLabel">
                    Confirmar cambios
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="adm-user-confirm-intro mb-3" id="admProdConfirmIntro"></p>
                <div class="adm-user-confirm-list" id="admProdConfirmList"></div>
            </div>
            <div class="modal-footer justify-content-end gap-2">
                <button type="button" class="mod-btn-sm mod-btn-cancel px-3" id="admProdConfirmBackBtn" style="height:36px; font-size:0.86rem;">
                    Volver a editar
                </button>
                <button type="button" class="adm-btn-primary" id="admProdConfirmSaveBtn" style="height:36px; font-size:0.86rem;">
                    <i class="fa-solid fa-check"></i>
                    <span>Confirmar y guardar</span>
                </button>
            </div>
        </div>
    </div>
</div>
