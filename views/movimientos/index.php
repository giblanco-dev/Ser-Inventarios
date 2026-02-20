<?php /** Movimientos — Historial con filtros */ ?>

<div class="toolbar">
    <div class="toolbar-left">
        <form class="filter-bar" method="GET" action="<?= url('movimientos') ?>">
            <div class="form-group">
                <label>Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="<?= e($filters['fecha_desde'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="<?= e($filters['fecha_hasta'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Concepto</label>
                <select name="concepto" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($conceptos as $c): ?>
                        <option value="<?= e($c['ID']) ?>" <?= ($filters['concepto'] ?? '') === $c['ID'] ? 'selected' : '' ?>>
                            <?= e($c['Descripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Tipo</label>
                <select name="tipo_operacion" class="form-control">
                    <option value="">Todos</option>
                    <option value="ENTRADA" <?= ($filters['tipo_operacion'] ?? '') === 'ENTRADA' ? 'selected' : '' ?>>Entrada</option>
                    <option value="SALIDA" <?= ($filters['tipo_operacion'] ?? '') === 'SALIDA' ? 'selected' : '' ?>>Salida</option>
                </select>
            </div>
            <div class="form-group" style="align-self: flex-end;">
                <button type="submit" class="btn btn--secondary btn--sm">Filtrar</button>
            </div>
        </form>
    </div>
    <div class="toolbar-right">
        <a href="<?= url('movimientos/create') ?>" class="btn btn--primary">+ Nuevo Movimiento</a>
    </div>
</div>

<div class="card">
    <div class="card-body card-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th class="text-center">Tipo</th>
                        <th>Referencia</th>
                        <th class="text-center">Líneas</th>
                        <th class="text-center">Cant. Total</th>
                        <th class="text-center">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="8" class="table-empty">No hay movimientos registrados</td></tr>
                    <?php else: ?>
                        <?php foreach ($items as $mov): ?>
                        <tr>
                            <td class="text-bold">#<?= e($mov['ID']) ?></td>
                            <td class="text-nowrap"><?= formatDateTime($mov['FechaMovimiento']) ?></td>
                            <td><?= e($mov['ConceptoDescripcion']) ?></td>
                            <td class="text-center">
                                <span class="badge badge--<?= strtolower($mov['TipoOperacion']) === 'entrada' ? 'entrada' : 'salida' ?>">
                                    <?= e($mov['TipoOperacion']) ?>
                                </span>
                            </td>
                            <td class="text-muted"><?= e($mov['Referencia'] ?: '—') ?></td>
                            <td class="text-center"><?= (int)$mov['TotalLineas'] ?></td>
                            <td class="text-center text-bold"><?= number_format((float)$mov['TotalCantidad'], 0) ?></td>
                            <td class="text-center">
                                <a href="<?= url('movimientos/show/' . $mov['ID']) ?>" class="btn btn--sm btn--secondary">Ver</a>
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
