<!-- Modal Publicar / Reenviar / Apelar Subasta -->
<div class="modal fade" id="modalPublicarSubasta" tabindex="-1" aria-labelledby="modalPublicarSubastaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-ppi">
            <div class="modal-header modal-header-ppi">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="modalPublicarSubastaTitulo">
                    <i class="fas fa-gavel text-third-ppi"></i>
                    <span id="modalSubastaTituloTexto">Publicar en subasta</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formPublicarSubasta" action="<?= BASE_URL ?>controllers/subasta_controller.php" method="POST" autocomplete="off">
                <div class="modal-body py-3">
                    <input type="hidden" name="opcion" value="insertar">
                    <input type="hidden" name="id_producto" id="subastaIdProducto" value="">

                    <p class="text-main-ppi fw-semibold mb-3" id="subastaNombreProducto"></p>

                    <div id="subastaMotivoAnteriorWrap" class="small mb-3 d-none">
                        <span class="text-second-ppi">Motivo del rechazo anterior:</span>
                        <span class="text-main-ppi" id="subastaMotivoAnteriorTexto"></span>
                    </div>

                    <div id="subastaTipoWrap" class="mb-3 d-none">
                        <label for="subastaTipoSolicitud" class="form-label text-main-ppi fw-bold small">Acción a enviar</label>
                        <select class="form-select select-gavel" id="subastaTipoSolicitud" name="tipo_solicitud">
                            <option value="reenvio">Corregir y reenviar a revisión</option>
                            <option value="apelacion">Apelar decisión del moderador</option>
                            <option value="nueva" hidden>Nueva solicitud</option>
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="subastaPrecioInicial" class="form-label text-main-ppi fw-bold small">Precio inicial ($)</label>
                            <input type="text" class="form-control" id="subastaPrecioInicial" name="precio_inicial" inputmode="numeric" value="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label for="subastaIncremento" class="form-label text-main-ppi fw-bold small">Incremento mínimo ($)</label>
                            <input type="text" class="form-control" id="subastaIncremento" name="incremento_minimo" inputmode="numeric" value="0.00" required>
                        </div>
                        <div class="col-12">
                            <label for="subastaDuracion" class="form-label text-main-ppi fw-bold small">Duración</label>
                            <select class="form-select select-gavel" id="subastaDuracion" name="dias">
                                <option value="1">1 día</option>
                                <option value="3">3 días</option>
                                <option value="5">5 días</option>
                                <option value="7" selected>7 días</option>
                                <option value="10">10 días</option>
                                <option value="14">14 días</option>
                                <option value="30">30 días</option>
                            </select>
                        </div>
                        <div class="col-12" id="subastaNotaWrap">
                            <label for="subastaNotaVendedor" class="form-label text-main-ppi fw-bold small" id="subastaNotaLabel">Nota para el moderador (opcional)</label>
                            <textarea class="form-control" id="subastaNotaVendedor" name="nota_vendedor" rows="2" placeholder="Indica algún detalle o qué corregiste..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-ppi d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-modificar-producto btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-subastar-producto btn-sm" id="btnEnviarSubasta">Enviar a revisión</button>
                </div>
            </form>
        </div>
    </div>
</div>