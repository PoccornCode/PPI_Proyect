<!-- MODAL DETALLE DE PRODUCTO (ADMIN) -->
<div class="modal fade adm-modal" id="modalDetalleProducto" tabindex="-1" aria-labelledby="modalDetalleProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h5 class="modal-title fw-bold text-white m-0" id="modalDetalleProductoLabel">
                        <span id="admProductDetailCode">Detalle</span>
                    </h5>
                    <span class="adm-badge adm-badge--activa d-none" id="admProductDetailBadge">Disponible</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="adm-user-detail-state" id="admProductDetailLoading">
                    <div class="spinner-border spinner-border-sm" style="color: var(--adm-accent);" role="status" aria-hidden="true"></div>
                    <span>Cargando información del producto...</span>
                </div>

                <div class="adm-user-detail-state adm-user-detail-state--error d-none" id="admProductDetailError">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                    <span id="admProductDetailErrorText">No se pudo cargar la información del producto.</span>
                </div>

                <div class="row g-4 align-items-start d-none" id="admProductDetailContent">
                    <div id="admProductGalleryWrap" class="col-md-6 mod-gallery d-none">
                        <div class="mod-gallery-stage">
                            <img id="admProductGalleryMainImg" src="" alt="Vista previa del producto">
                            <button type="button" class="mod-gallery-nav mod-gallery-nav--prev" id="admProductGalleryPrev" aria-label="Imagen anterior">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <button type="button" class="mod-gallery-nav mod-gallery-nav--next" id="admProductGalleryNext" aria-label="Imagen siguiente">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                            <span class="mod-gallery-counter" id="admProductGalleryCounter">1 / 1</span>
                        </div>
                        <div class="mod-gallery-thumbs" id="admProductGalleryThumbs"></div>
                    </div>
                    <div class="col mod-detail-info">
                        <div>
                            <h4 class="text-white fw-bold mb-1" id="admProductDetailTitle"></h4>
                        </div>
                        <div id="admProductDetailSpecs" class="mod-detail-specs d-none"></div>
                        <div class="mod-detail-desc-box">
                            <div class="mod-detail-desc-label">Descripción</div>
                            <p class="mod-detail-desc" id="admProductDetailDesc"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end">
                <button type="button" class="adm-btn-primary" style="height:36px; font-size:0.86rem;" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
