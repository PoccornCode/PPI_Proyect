<!-- MODAL DETALLE DE SUBASTA (ADMIN) -->
<div class="modal fade adm-modal" id="modalDetalleSubasta" tabindex="-1" aria-labelledby="modalDetalleSubastaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h5 class="modal-title fw-bold text-white m-0" id="modalDetalleSubastaLabel">
                        <span id="admAuctionDetailCode">Detalle</span>
                    </h5>
                    <span class="adm-badge adm-badge--pendiente d-none" id="admAuctionDetailBadge">Pendiente</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="adm-user-detail-state" id="admAuctionDetailLoading">
                    <div class="spinner-border spinner-border-sm" style="color: var(--adm-accent);" role="status" aria-hidden="true"></div>
                    <span>Cargando información de la subasta...</span>
                </div>

                <div class="adm-user-detail-state adm-user-detail-state--error d-none" id="admAuctionDetailError">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                    <span id="admAuctionDetailErrorText">No se pudo cargar la información de la subasta.</span>
                </div>

                <div class="row g-4 align-items-start d-none" id="admAuctionDetailContent">
                    <div id="admAuctionGalleryWrap" class="col-md-6 mod-gallery d-none">
                        <div class="mod-gallery-stage">
                            <img id="admAuctionGalleryMainImg" src="" alt="Vista previa del artículo">
                            <button type="button" class="mod-gallery-nav mod-gallery-nav--prev" id="admAuctionGalleryPrev" aria-label="Imagen anterior">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <button type="button" class="mod-gallery-nav mod-gallery-nav--next" id="admAuctionGalleryNext" aria-label="Imagen siguiente">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                            <span class="mod-gallery-counter" id="admAuctionGalleryCounter">1 / 1</span>
                        </div>
                        <div class="mod-gallery-thumbs" id="admAuctionGalleryThumbs"></div>
                    </div>
                    <div class="col mod-detail-info">
                        <div>
                            <h4 class="text-white fw-bold mb-1" id="admAuctionDetailTitle"></h4>
                        </div>
                        <div id="admAuctionDetailSpecs" class="mod-detail-specs d-none"></div>
                        <div class="mod-detail-desc-box">
                            <div class="mod-detail-desc-label">Descripción</div>
                            <p class="mod-detail-desc" id="admAuctionDetailDesc"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between flex-wrap gap-2">
                <div id="admAuctionModalApprovalActions" class="d-flex align-items-center gap-2 d-none">
                    <button type="button" class="mod-btn-sm mod-btn-approve" style="height:36px; font-size:0.86rem;" id="admAuctionModalApproveBtn">
                        <i class="fa-solid fa-check"></i> Aprobar
                    </button>
                    <button type="button" class="mod-btn-sm mod-btn-reject" style="height:36px; font-size:0.86rem;" id="admAuctionModalRejectBtn">
                        <i class="fa-solid fa-xmark"></i> Rechazar
                    </button>
                </div>
                <button type="button" class="adm-btn-primary ms-auto" style="height:36px; font-size:0.86rem;" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
