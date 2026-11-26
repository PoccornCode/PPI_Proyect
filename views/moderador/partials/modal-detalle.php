<!-- MODAL DETALLE (SUBASTA O REPORTE) -->
<div class="modal fade adm-modal" id="modalModDetalle" tabindex="-1" aria-labelledby="modalModDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-white" id="modalModDetalleLabel">
                    <span id="modDetailCode">Detalle</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4 align-items-start">
                    <div id="modGalleryWrap" class="col-md-6 mod-gallery d-none">
                        <div class="mod-gallery-stage">
                            <img id="modGalleryMainImg" src="" alt="Vista previa del artículo">
                            <button type="button" class="mod-gallery-nav mod-gallery-nav--prev" id="modGalleryPrev" aria-label="Imagen anterior">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <button type="button" class="mod-gallery-nav mod-gallery-nav--next" id="modGalleryNext" aria-label="Imagen siguiente">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                            <span class="mod-gallery-counter" id="modGalleryCounter">1 / 1</span>
                        </div>
                        <div class="mod-gallery-thumbs" id="modGalleryThumbs"></div>
                    </div>
                    <div class="col mod-detail-info">
                        <div>
                            <h4 class="text-white fw-bold mb-1" id="modDetailTitle"></h4>
                            <div class="small fw-semibold" style="color:var(--adm-accent);" id="modDetailMeta"></div>
                        </div>
                        <div id="modDetailSpecs" class="mod-detail-specs d-none"></div>
                        <div class="mod-detail-desc-box">
                            <div class="mod-detail-desc-label" id="modDetailDescLabel">Descripción</div>
                            <p class="mod-detail-desc" id="modDetailBody"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between flex-wrap gap-2">
                <div id="modModalApprovalActions" class="d-flex align-items-center gap-2">
                    <button type="button" class="mod-btn-sm mod-btn-approve" style="height:36px; font-size:0.86rem;" id="modModalApproveBtn">
                        <i class="fa-solid fa-check"></i> Aprobar
                    </button>
                    <button type="button" class="mod-btn-sm mod-btn-reject" style="height:36px; font-size:0.86rem;" id="modModalRejectBtn">
                        <i class="fa-solid fa-xmark"></i> Rechazar
                    </button>
                </div>
                <button type="button" class="adm-btn-primary ms-auto" style="height:36px; font-size:0.86rem;" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
