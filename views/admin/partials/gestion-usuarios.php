<?php
$usuariosLista = $usuariosAdminInicial['usuarios'] ?? [];
$rolesLista = $rolesAdmin ?? [];
$totalUsuarios = (int)($usuariosAdminInicial['total'] ?? 0);
$desdeUsuarios = (int)($usuariosAdminInicial['desde'] ?? 0);
$hastaUsuarios = (int)($usuariosAdminInicial['hasta'] ?? 0);
$totalPaginas = max(1, (int)($usuariosAdminInicial['total_paginas'] ?? 1));

if ($totalUsuarios === 0) {
    $textoConteoUsuarios = 'Mostrando 0 usuarios';
} elseif ($totalUsuarios === 1) {
    $textoConteoUsuarios = 'Mostrando 1 usuario';
} elseif ($totalPaginas > 1) {
    $textoConteoUsuarios = "Mostrando {$desdeUsuarios}–{$hastaUsuarios} de {$totalUsuarios} usuarios";
} else {
    $textoConteoUsuarios = "Mostrando {$totalUsuarios} usuarios";
}
?>
<section class="adm-section" id="sec-usuarios">
    <div class="adm-section-head">
        <div class="adm-title-group">
            <span class="adm-title-accent" aria-hidden="true"></span>
            <div>
                <h1>Gestión de usuarios</h1>
                <p>Administra los usuarios registrados en la plataforma.</p>
            </div>
        </div>
    </div>
    <hr class="adm-section-divider">

    <div class="adm-toolbar">
        <div class="adm-toolbar-left">
            <div class="adm-input-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span class="adm-search-divider" aria-hidden="true"></span>
                <input type="search" id="admUserSearch" placeholder="Buscar por correo, nombre o usuario..." aria-label="Buscar usuario">
            </div>
            <select class="adm-select-filter" id="admUserRoleFilter" aria-label="Filtrar por rol">
                <option value="0">Todos los roles</option>
                <?php foreach ($rolesLista as $rolOpt): ?>
                    <option value="<?= (int)$rolOpt['id_rol'] ?>"><?= htmlspecialchars($rolOpt['etiqueta']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="button" class="adm-btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarUsuario">
            <i class="fa-solid fa-plus"></i> Agregar usuario
        </button>
    </div>

    <div class="adm-table-card">
        <div class="adm-table-responsive">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th><i class="fa-solid fa-users"></i> Usuarios</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="admUsersTbody">
                    <?php if (empty($usuariosLista)): ?>
                        <tr>
                            <td colspan="5" class="adm-table-empty">
                                <i class="fa-solid fa-users-slash" aria-hidden="true"></i>
                                <span>No se encontraron usuarios registrados.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuariosLista as $u): ?>
                            <?php
                            $etiqueta = (string)($u['etiqueta'] ?? '');
                            $nombreCompleto = (string)($u['nombre_completo'] ?? '');
                            $username = (string)($u['username'] ?? '');
                            $subInfo = trim($nombreCompleto . ($username !== '' ? ' · @' . ltrim($username, '@') : ''), ' ·');
                            ?>
                            <tr data-user-id="<?= (int)$u['id_usuario'] ?>" data-role-id="<?= (int)$u['id_rol'] ?>">
                                <td>
                                    <div class="adm-cell-user">
                                        <div class="adm-user-avatar"><i class="fa-solid fa-user"></i></div>
                                        <div class="adm-cell-user-meta">
                                            <span class="adm-cell-user-main" title="<?= htmlspecialchars($etiqueta) ?>"><?= htmlspecialchars($etiqueta) ?></span>
                                            <?php if ($subInfo !== ''): ?>
                                                <span class="adm-cell-user-sub"><?= htmlspecialchars($subInfo) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="adm-badge adm-badge--<?= htmlspecialchars($u['rol_class']) ?>">
                                        <?= htmlspecialchars($u['rol']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="adm-status">
                                        <span class="adm-status-dot <?= !empty($u['activo']) ? 'adm-status-dot--active' : 'adm-status-dot--suspended' ?>"></span>
                                        <?= htmlspecialchars($u['estado']) ?>
                                    </span>
                                </td>
                                <td title="<?= htmlspecialchars((string)($u['fecha_registro'] ?? '')) ?>"><?= htmlspecialchars($u['registro']) ?></td>
                                <td>
                                    <div class="adm-action-group">
                                        <button type="button" class="adm-action-btn adm-action-btn--edit" title="Ver detalles"
                                            data-adm-demo-action="Ver detalles: <?= htmlspecialchars($etiqueta) ?> (referencia)">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                        <button type="button" class="adm-action-btn adm-action-btn--edit" title="Editar usuario"
                                            data-adm-demo-action="Editar <?= htmlspecialchars($etiqueta) ?> (referencia)">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="adm-table-footer">
            <span class="adm-table-count" id="admUsersCount"><?= htmlspecialchars($textoConteoUsuarios) ?></span>
            <nav class="adm-pagination" id="admUsersPagination" aria-label="Paginación de usuarios"></nav>
        </div>
    </div>
</section>
