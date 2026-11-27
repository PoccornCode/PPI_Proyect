<!-- Sección: Reportes y estadísticas -->
<section class="adm-section" id="sec-reportes">
    <div class="adm-section-head adm-rep-head">
        <div class="adm-title-group">
            <span class="adm-title-accent" aria-hidden="true"></span>
            <div>
                <h1>Reportes</h1>
                <p id="admRepPeriodSubtitle">Resumen del 24 de oct. al 23 de nov. de 2026</p>
            </div>
        </div>

        <div class="adm-rep-controls">
            <label class="adm-pill-select adm-rep-period-select">
                <i class="fa-regular fa-calendar"></i>
                <select id="admRepPeriod" aria-label="Periodo del reporte">
                    <option value="7d">Últimos 7 días</option>
                    <option value="30d" selected>Últimos 30 días</option>
                    <option value="90d">Últimos 90 días</option>
                </select>
            </label>
            <button type="button" class="adm-rep-btn adm-rep-btn--ghost" id="admRepPrintBtn" title="Imprimir o guardar como PDF">
                <i class="fa-solid fa-print"></i>
                <span>Imprimir / PDF</span>
            </button>
            <button type="button" class="adm-rep-btn adm-rep-btn--export" id="admRepCsvBtn">
                <i class="fa-solid fa-download"></i>
                <span>Exportar CSV</span>
            </button>
        </div>
    </div>

    <!-- Navegación interna de reportes -->
    <div class="adm-rep-nav-wrap">
        <div class="adm-rep-tabs" role="tablist" aria-label="Categorías de reportes">
            <button type="button" class="adm-rep-tab active" role="tab" aria-selected="true" aria-controls="rep-panel-subastas" id="rep-tab-subastas" data-rep-tab="subastas">
                Subastas
            </button>
            <button type="button" class="adm-rep-tab" role="tab" aria-selected="false" aria-controls="rep-panel-categorias" id="rep-tab-categorias" data-rep-tab="categorias">
                Categorías
            </button>
            <button type="button" class="adm-rep-tab" role="tab" aria-selected="false" aria-controls="rep-panel-usuarios" id="rep-tab-usuarios" data-rep-tab="usuarios">
                Usuarios
            </button>
            <button type="button" class="adm-rep-tab" role="tab" aria-selected="false" aria-controls="rep-panel-moderacion" id="rep-tab-moderacion" data-rep-tab="moderacion">
                Moderación y contacto
            </button>
        </div>
        <span class="adm-rep-updated">Actualizado hoy · 10:00 p. m.</span>
    </div>

    <!-- Franja unificada de métricas del periodo -->
    <div class="adm-rep-strip" id="admRepStrip" aria-live="polite"></div>

    <!-- PESTAÑA 1: SUBASTAS -->
    <div class="adm-rep-pane active" id="rep-panel-subastas" role="tabpanel" aria-labelledby="rep-tab-subastas">
        <div class="adm-rep-grid adm-rep-grid--wide-left">
            <article class="adm-rep-card">
                <header class="adm-rep-card-head">
                    <div>
                        <h2 class="adm-rep-card-title">Monto vendido por tramo</h2>
                        <p class="adm-rep-card-sub">Suma de subastas cerradas con ganador</p>
                    </div>
                    <span class="adm-rep-unit">USD ($)</span>
                </header>
                <div class="adm-rep-card-body">
                    <div class="adm-chart-wrap">
                        <svg id="admRepAuctionChart" class="adm-svg-chart" viewBox="0 0 560 210" role="img" aria-label="Monto vendido en subastas"></svg>
                        <div id="admRepAuctionTooltip" class="adm-chart-tooltip"></div>
                    </div>
                </div>
            </article>

            <article class="adm-rep-card">
                <header class="adm-rep-card-head">
                    <div>
                        <h2 class="adm-rep-card-title">Estado de las subastas</h2>
                        <p class="adm-rep-card-sub">Distribución según cierre y revisión</p>
                    </div>
                </header>
                <div class="adm-rep-card-body">
                    <div class="adm-rep-stackbar" id="admRepStatusBar" aria-hidden="true"></div>
                    <ul class="adm-rep-stat-list" id="admRepStatusList"></ul>
                </div>
            </article>
        </div>

        <article class="adm-rep-card adm-rep-table-wrap">
            <header class="adm-rep-card-head adm-rep-card-head--wrap">
                <div>
                    <h2 class="adm-rep-card-title">Detalle de subastas</h2>
                    <p class="adm-rep-card-sub" id="admRepAuctionTableSub">Mostrando subastas del periodo seleccionado</p>
                </div>
                <div class="adm-rep-filter-group" role="group" aria-label="Filtrar tabla por estado">
                    <button type="button" class="adm-rep-chip active" data-rep-status="todas">Todas</button>
                    <button type="button" class="adm-rep-chip" data-rep-status="finalizada">Finalizadas</button>
                    <button type="button" class="adm-rep-chip" data-rep-status="activa">Activas</button>
                    <button type="button" class="adm-rep-chip" data-rep-status="sin_ofertas">Sin ofertas</button>
                    <button type="button" class="adm-rep-chip" data-rep-status="pendiente">Pendientes</button>
                </div>
            </header>
            <div class="adm-table-responsive">
                <table class="adm-rep-table" id="admRepAuctionTable">
                    <thead>
                        <tr>
                            <th>Artículo</th>
                            <th>Categoría</th>
                            <th>Vendedor</th>
                            <th class="is-num">Precio inicial</th>
                            <th class="is-num">Puja final</th>
                            <th class="is-num">Pujas</th>
                            <th>Estado</th>
                            <th>Cierre</th>
                        </tr>
                    </thead>
                    <tbody id="admRepAuctionTbody"></tbody>
                </table>
            </div>
        </article>
    </div>

    <!-- PESTAÑA 2: CATEGORÍAS -->
    <div class="adm-rep-pane" id="rep-panel-categorias" role="tabpanel" aria-labelledby="rep-tab-categorias">
        <div class="adm-rep-grid adm-rep-grid--wide-left">
            <article class="adm-rep-card">
                <header class="adm-rep-card-head">
                    <div>
                        <h2 class="adm-rep-card-title">Participación por categoría</h2>
                        <p class="adm-rep-card-sub">Ordenado por monto vendido en el periodo</p>
                    </div>
                </header>
                <div class="adm-rep-card-body">
                    <div class="adm-rep-bars" id="admRepCatBars"></div>
                </div>
            </article>

            <article class="adm-rep-card">
                <header class="adm-rep-card-head">
                    <div>
                        <h2 class="adm-rep-card-title">Lectura del periodo</h2>
                        <p class="adm-rep-card-sub">Puntos destacados del catálogo</p>
                    </div>
                </header>
                <div class="adm-rep-card-body">
                    <ul class="adm-rep-notes" id="admRepCatNotes"></ul>
                </div>
            </article>
        </div>

        <article class="adm-rep-card adm-rep-table-wrap">
            <header class="adm-rep-card-head">
                <div>
                    <h2 class="adm-rep-card-title">Comparativa por categoría</h2>
                    <p class="adm-rep-card-sub">Rendimiento consolidado de subastas y pujas</p>
                </div>
            </header>
            <div class="adm-table-responsive">
                <table class="adm-rep-table">
                    <thead>
                        <tr>
                            <th>Categoría</th>
                            <th class="is-num">Subastas</th>
                            <th class="is-num">Cerradas</th>
                            <th class="is-num">Pujas</th>
                            <th class="is-num">Monto vendido</th>
                            <th class="is-num">Promedio / subasta</th>
                        </tr>
                    </thead>
                    <tbody id="admRepCatTbody"></tbody>
                </table>
            </div>
        </article>
    </div>

    <!-- PESTAÑA 3: USUARIOS -->
    <div class="adm-rep-pane" id="rep-panel-usuarios" role="tabpanel" aria-labelledby="rep-tab-usuarios">
        <div class="adm-rep-grid adm-rep-grid--wide-left">
            <article class="adm-rep-card">
                <header class="adm-rep-card-head">
                    <div>
                        <h2 class="adm-rep-card-title">Nuevos registros</h2>
                        <p class="adm-rep-card-sub">Altas de cuentas verificadas en el periodo</p>
                    </div>
                    <span class="adm-rep-unit">Cuentas</span>
                </header>
                <div class="adm-rep-card-body">
                    <div class="adm-chart-wrap">
                        <svg id="admRepUsersChart" class="adm-svg-chart" viewBox="0 0 560 210" role="img" aria-label="Evolución de nuevos registros"></svg>
                        <div id="admRepUsersTooltip" class="adm-chart-tooltip"></div>
                    </div>
                </div>
            </article>

            <article class="adm-rep-card">
                <header class="adm-rep-card-head">
                    <div>
                        <h2 class="adm-rep-card-title">Composición de cuentas</h2>
                        <p class="adm-rep-card-sub">Roles registrados y estado actual</p>
                    </div>
                </header>
                <div class="adm-rep-card-body">
                    <ul class="adm-rep-stat-list" id="admRepRoleList"></ul>
                    <hr class="adm-rep-inner-divider">
                    <div class="adm-rep-mini-pair" id="admRepUserHealth"></div>
                </div>
            </article>
        </div>

        <article class="adm-rep-card adm-rep-table-wrap">
            <header class="adm-rep-card-head">
                <div>
                    <h2 class="adm-rep-card-title">Usuarios con mayor actividad</h2>
                    <p class="adm-rep-card-sub">Combinando subastas publicadas y pujas realizadas</p>
                </div>
            </header>
            <div class="adm-table-responsive">
                <table class="adm-rep-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Ubicación</th>
                            <th class="is-num">Subastas publicadas</th>
                            <th class="is-num">Pujas realizadas</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="admRepUsersTbody"></tbody>
                </table>
            </div>
        </article>
    </div>

    <!-- PESTAÑA 4: MODERACIÓN Y CONTACTO -->
    <div class="adm-rep-pane" id="rep-panel-moderacion" role="tabpanel" aria-labelledby="rep-tab-moderacion">
        <div class="adm-rep-grid adm-rep-grid--equal">
            <article class="adm-rep-card">
                <header class="adm-rep-card-head">
                    <div>
                        <h2 class="adm-rep-card-title">Revisión de subastas</h2>
                        <p class="adm-rep-card-sub">Resoluciones del equipo de moderación</p>
                    </div>
                    <a href="<?= $base_url ?>views/moderador/dashboard.php" class="adm-rep-subtle-link">
                        Ir a moderación <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </header>
                <div class="adm-rep-card-body">
                    <div class="adm-rep-stackbar" id="admRepModBar" aria-hidden="true"></div>
                    <ul class="adm-rep-stat-list" id="admRepModList"></ul>
                </div>
            </article>

            <article class="adm-rep-card">
                <header class="adm-rep-card-head">
                    <div>
                        <h2 class="adm-rep-card-title">Bandeja de contacto</h2>
                        <p class="adm-rep-card-sub">Consultas recibidas desde el formulario público</p>
                    </div>
                </header>
                <div class="adm-rep-card-body">
                    <div class="adm-rep-mini-pair" id="admRepContactPair"></div>
                    <hr class="adm-rep-inner-divider">
                    <ul class="adm-rep-contact-list" id="admRepContactList"></ul>
                </div>
            </article>
        </div>
    </div>
</section>
