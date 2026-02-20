<?php /** Insumos — Lista con filtros y paginación */ ?>

<div class="toolbar">
    <div class="toolbar-left">
        <form class="filter-bar" method="GET" action="<?= url('insumos') ?>">
            <div class="form-group">
                <label>Buscar</label>
                <input type="text" name="busqueda" class="form-control" 
                       value="<?= e($filters['busqueda'] ?? '') ?>" 
                       placeholder="Nombre del insumo...">
            </div>
            <div class="form-group">
                <label>Tipo</label>
                <select name="tipo" class="form-control" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <?php foreach ($tipos as $t): ?>
                        <option value="<?= e($t['ID']) ?>" <?= ($filters['tipo'] ?? '') === $t['ID'] ? 'selected' : '' ?>>
                            <?= e($t['Descripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Medida</label>
                <select name="medida" class="form-control" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    <?php foreach ($medidas as $m): ?>
                        <option value="<?= e($m['ID']) ?>" <?= ($filters['medida'] ?? '') === $m['ID'] ? 'selected' : '' ?>>
                            <?= e($m['Descripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Estado Stock</label>
                <select name="estado_stock" class="form-control" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="OK" <?= ($filters['estado_stock'] ?? '') === 'OK' ? 'selected' : '' ?>>OK</option>
                    <option value="BAJO" <?= ($filters['estado_stock'] ?? '') === 'BAJO' ? 'selected' : '' ?>>Bajo</option>
                    <option value="CRITICO" <?= ($filters['estado_stock'] ?? '') === 'CRITICO' ? 'selected' : '' ?>>Crítico</option>
                </select>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="activo" class="form-control" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="1" <?= ($filters['activo'] ?? '') === '1' ? 'selected' : '' ?>>Activos</option>
                    <option value="0" <?= ($filters['activo'] ?? '') === '0' ? 'selected' : '' ?>>Inactivos</option>
                </select>
            </div>
            <div class="form-group" style="align-self: flex-end;">
                <button type="submit" class="btn btn--secondary btn--sm">Filtrar</button>
            </div>
        </form>
    </div>
    <div class="toolbar-right">
        <a href="<?= url('insumos/create') ?>" class="btn btn--primary">+ Nuevo Insumo</a>
    </div>
</div>

<div class="card">
    <div class="card-body card-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Unidad</th>
                        <th class="text-center">Existencia</th>
                        <th class="text-center">Mín</th>
                        <th class="text-center">Máx</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Caduca</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="10" class="table-empty">No se encontraron insumos</td></tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): 
                            $badge = stockBadge($item['Existencia'], $item['Minimos']);
                        ?>
                        <tr>
                            <td class="text-muted"><?= e($item['ID']) ?></td>
                            <td class="text-bold"><?= e($item['Nombre']) ?></td>
                            <td class="text-sm"><?= e($item['TipoDescripcion']) ?></td>
                            <td class="text-sm"><?= e($item['MedidaDescripcion']) ?></td>
                            <td class="text-center text-bold"><?= number_format((float)$item['Existencia'], 0) ?></td>
                            <td class="text-center text-muted"><?= (int)$item['Minimos'] ?></td>
                            <td class="text-center text-muted"><?= (int)$item['Maximos'] ?></td>
                            <td class="text-center">
                                <span class="badge badge--<?= $badge ?>">
                                    <?= stockLabel($badge) ?>
                                </span>
                            </td>
                            <td class="text-center"><?= $item['Caducidad'] ? '✓' : '—' ?></td>
                            <td class="text-center">
                                <div class="actions" style="justify-content:center">
                                    <a href="<?= url('insumos/show/' . $item['ID']) ?>" class="btn btn--sm btn--secondary" title="Detalle">👁</a>
                                    <a href="<?= url('insumos/edit/' . $item['ID']) ?>" class="btn btn--sm btn--secondary" title="Editar">✏️</a>
                                    <a href="<?= url('codigo-barra/index/' . $item['ID']) ?>" class="btn btn--sm btn--secondary" title="Códigos de barra">🏷️</a>
                                    <form method="POST" action="<?= url('insumos/delete/' . $item['ID']) ?>" style="display:inline">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn--sm <?= $item['Activo'] ? 'btn--danger' : 'btn--success' ?>"
                                                data-confirm="<?= $item['Activo'] ? '¿Inactivar este insumo?' : '¿Reactivar este insumo?' ?>"
                                                title="<?= $item['Activo'] ? 'Inactivar' : 'Reactivar' ?>">
                                            <?= $item['Activo'] ? '🚫' : '✅' ?>
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
