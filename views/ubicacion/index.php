<?php /** Catálogo: Lista de Ubicaciones */ ?>

<div class="toolbar">
    <div class="toolbar-left">
        <form class="filter-bar" method="GET" action="<?= url('ubicacion') ?>">
            <div class="form-group">
                <label>Estado</label>
                <select name="activo" class="form-control" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="1" <?= ($filters['activo'] ?? '') === '1' ? 'selected' : '' ?>>Activos</option>
                    <option value="0" <?= ($filters['activo'] ?? '') === '0' ? 'selected' : '' ?>>Inactivos</option>
                </select>
            </div>
        </form>
    </div>
    <div class="toolbar-right">
        <a href="<?= url('ubicacion/create') ?>" class="btn btn--primary">+ Nueva Ubicación</a>
    </div>
</div>

<div class="card">
    <div class="card-body card-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Descripción</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="4" class="table-empty">No hay registros</td></tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="text-muted"><?= e($item['ID']) ?></td>
                            <td><?= e($item['Descripcion']) ?></td>
                            <td class="text-center">
                                <span class="badge badge--<?= $item['Activo'] ? 'activo' : 'inactivo' ?>">
                                    <?= $item['Activo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="actions" style="justify-content:center">
                                    <a href="<?= url('ubicacion/edit/' . $item['ID']) ?>" class="btn btn--sm btn--secondary">Editar</a>
                                    <form method="POST" action="<?= url('ubicacion/delete/' . $item['ID']) ?>" style="display:inline">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn--sm <?= $item['Activo'] ? 'btn--danger' : 'btn--success' ?>"
                                                data-confirm="<?= $item['Activo'] ? '¿Inactivar esta ubicación?' : '¿Reactivar esta ubicación?' ?>">
                                            <?= $item['Activo'] ? 'Inactivar' : 'Reactivar' ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/partials/_pagination.php'; ?>
