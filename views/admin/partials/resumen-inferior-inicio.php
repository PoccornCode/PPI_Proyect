<?php
$usuariosRecientes = $resumenInferiorInicio['usuarios_recientes'] ?? [];
$subastasDestacadas = $resumenInferiorInicio['subastas_destacadas'] ?? [];
?>
<div class="adm-bottom-row">
    <div class="adm-panel">
        <div class="adm-panel-header">
            <h2 class="adm-panel-title">
                <i class="fa-solid fa-users"></i> Usuarios recientes
            </h2>
            <button type="button" class="adm-panel-link" data-adm-tab="usuarios">
                Ver todos <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
        <div class="adm-panel-body py-2">
            <div class="adm-recent-list" id="admRecentUsersList">
                <?php if (empty($usuariosRecientes)): ?>
                    <div class="adm-empty-inline">
                        <i class="fa-solid fa-users-slash" aria-hidden="true"></i>
                        <span>No hay usuarios registrados aún.</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($usuariosRecientes as $u): ?>
                        <div class="adm-recent-item">
                            <div class="adm-user-avatar" aria-hidden="true"><i class="fa-solid fa-user"></i></div>
                            <div class="adm-recent-email" title="<?= htmlspecialchars($u['etiqueta']) ?>">
                                <?= htmlspecialchars($u['etiqueta']) ?>
                            </div>
                            <span class="adm-badge adm-badge--<?= htmlspecialchars($u['rol_class']) ?>">
                                <?= htmlspecialchars($u['rol']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="adm-panel">
        <div class="adm-panel-header">
            <h2 class="adm-panel-title">
                <i class="fa-solid fa-star"></i> Subastas destacadas
            </h2>
            <button type="button" class="adm-panel-link" data-adm-tab="subastas">
                Ver todas <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
        <div class="adm-panel-body py-2">
            <div class="adm-featured-list" id="admFeaturedAuctionsList">
                <?php if (empty($subastasDestacadas)): ?>
                    <div class="adm-empty-inline">
                        <i class="fa-solid fa-gavel" aria-hidden="true"></i>
                        <span>No hay subastas activas en este momento.</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($subastasDestacadas as $s): ?>
                        <?php $pujasTxt = (int)$s['total_pujas'] === 1 ? '1 puja' : ((int)$s['total_pujas'] . ' pujas'); ?>
                        <article class="adm-featured-row" data-adm-featured-row data-subasta-id="<?= (int)$s['id_subasta'] ?>" role="button" tabindex="0">
                            <div class="adm-featured-thumb">
                                <img src="<?= htmlspecialchars($s['imagen']) ?>" alt="<?= htmlspecialchars($s['titulo']) ?>" loading="lazy">
                            </div>
                            <div class="adm-featured-info">
                                <div class="adm-featured-title" title="<?= htmlspecialchars($s['titulo']) ?>">
                                    <?= htmlspecialchars($s['titulo']) ?>
                                </div>
                                <div class="adm-featured-meta">
                                    <span><?= htmlspecialchars($s['categoria']) ?></span>
                                    <span class="adm-meta-dot" aria-hidden="true">·</span>
                                    <span><?= htmlspecialchars($pujasTxt) ?></span>
                                    <span class="adm-meta-dot" aria-hidden="true">·</span>
                                    <span class="adm-featured-time">
                                        <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                        <span data-adm-countdown="<?= htmlspecialchars($s['fecha_final_iso']) ?>"><?= htmlspecialchars($s['tiempo']) ?></span>
                                    </span>
                                </div>
                            </div>
                            <div class="adm-featured-aside">
                                <div class="adm-featured-price"><?= htmlspecialchars($s['precio']) ?></div>
                                <div class="adm-featured-sub"><?= htmlspecialchars($s['precio_label']) ?></div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
