<!-- MODAL CRUD: CREAR / EDITAR CATEGORÍA -->
<div class="modal fade adm-modal" id="modalCategoriaCrud" tabindex="-1" aria-labelledby="modalCategoriaCrudLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formCategoriaCrud" enctype="multipart/form-data" novalidate>
                <input type="hidden" id="catFormId" name="id_categoria" value="0">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalCategoriaCrudLabel">
                        <i class="fa-solid fa-layer-group" style="color:var(--adm-accent);"></i>
                        <span id="catModalTitleText">Nueva categoría</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-3">
                    <!-- Imagen de portada -->
                    <div>
                        <label class="form-label small text-secondary mb-2">Imagen de portada</label>
                        <div class="adm-cat-dropzone" id="catImageDropzone" role="button" tabindex="0" aria-label="Seleccionar imagen de categoría">
                            <input type="file" id="catFormImagen" name="imagen" accept="image/jpeg,image/png,image/webp" class="d-none">
                            <div class="adm-cat-dropzone-preview d-none" id="catImagePreviewWrap">
                                <img id="catImagePreview" src="" alt="Vista previa de categoría">
                                <div class="adm-cat-dropzone-overlay">
                                    <span><i class="fa-solid fa-camera me-1"></i> Cambiar imagen</span>
                                </div>
                            </div>
                            <div class="adm-cat-dropzone-placeholder" id="catImagePlaceholder">
                                <div class="adm-cat-dropzone-icon">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <div class="fw-semibold text-white small">Arrastra una imagen o haz clic para subir</div>
                                <div style="font-size:0.76rem; color:#8d9685;">JPG, PNG o WEBP · Máximo 5 MB</div>
                            </div>
                        </div>
                    </div>

                    <!-- Nombre -->
                    <div>
                        <label for="catFormNombre" class="form-label small text-secondary">Nombre de la categoría <span style="color:#d8685d;">*</span></label>
                        <input type="text" class="form-control" id="catFormNombre" name="nombre" maxlength="50" placeholder="Ej. Relojes de lujo, Vinilos, Antigüedades..." required>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="catFormDescripcion" class="form-label small text-secondary mb-0">Descripción</label>
                            <span style="font-size:0.74rem; color:#8d9685;" id="catDescCounter">0/255</span>
                        </div>
                        <textarea class="form-control" id="catFormDescripcion" name="descripcion" rows="3" maxlength="255" placeholder="Describe brevemente qué tipo de artículos pertenecen a esta categoría..."></textarea>
                    </div>

                    <!-- Publicar / Estado -->
                    <div class="adm-cat-publish-box">
                        <div>
                            <div class="fw-semibold text-white small" id="catEstadoTitle">Publicar en el sitio público</div>
                            <div style="font-size:0.76rem; color:#9aa392;" id="catEstadoHelp">Visible en el catálogo, menú y registro de productos.</div>
                        </div>
                        <label class="adm-switch mb-0">
                            <input type="checkbox" id="catFormEstado" name="estado" value="1" checked>
                            <span class="adm-switch-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="mod-btn-sm mod-btn-cancel px-3" style="height:38px;" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="adm-btn-primary" id="btnGuardarCategoria" style="height:38px; font-size:0.86rem;">
                        <i class="fa-solid fa-check"></i>
                        <span id="btnGuardarCategoriaText">Crear y publicar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
