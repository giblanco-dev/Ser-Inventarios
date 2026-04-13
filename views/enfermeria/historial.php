<?php /** Enfermería — Historial de Entregas y Devoluciones */ ?>

<div class="breadcrumb">
    <a href="<?= url('insumos-enfermeria') ?>">Enfermería</a>
    <span class="separator">›</span>
    <span class="current">Historial</span>
</div>

<div class="toolbar">
    <div class="toolbar-left">
        <form class="filter-bar" method="GET" action="<?= url('insumos-enfermeria/historial') ?>">
            <div class="form-group">
                <label>Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="<?= e($filters['fecha_desde'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="<?= e($filters['fecha_hasta'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Tipo</label>
                <select name="tipo_operacion" class="form-control">
                    <option value="">Todos</option>
                    <option value="ENTREGA" <?= ($filters['tipo_operacion'] ?? '') === 'ENTREGA' ? 'selected' : '' ?>>Entrega</option>
                    <option value="DEVOLUCION" <?= ($filters['tipo_operacion'] ?? '') === 'DEVOLUCION' ? 'selected' : '' ?>>Devolución</option>
                </select>
            </div>
            <div class="form-group" style="align-self: flex-end;">
                <button type="submit" class="btn btn--secondary btn--sm">Filtrar</button>
            </div>
        </form>
    </div>
    <div class="toolbar-right">
        <a href="<?= url('insumos-enfermeria/create') ?>" class="btn btn--primary">+ Nueva Entrega</a>
        <a href="<?= url('insumos-enfermeria/devolucion') ?>" class="btn btn--warning">↩ Devolución</a>
    </div>
</div>

<div class="card">
    <div class="card-body card-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th class="text-center">Tipo</th>
                        <th>Entregó</th>
                        <th>Recibió</th>
                        <th class="text-center">Líneas</th>
                        <th class="text-center">Cant. Total</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="8" class="table-empty">No hay registros de entregas o devoluciones</td></tr>
                    <?php else: ?>
                        <?php foreach ($items as $reg): ?>
                        <tr>
                            <td class="text-bold">#<?= e($reg['ID']) ?></td>
                            <td class="text-nowrap"><?= formatDateTime($reg['FechaEntrega']) ?></td>
                            <td class="text-center">
                                <span class="badge badge--<?= strtolower($reg['TipoOperacion']) === 'entrega' ? 'entrega' : 'devolucion' ?>">
                                    <?= $reg['TipoOperacion'] === 'ENTREGA' ? 'ENTREGA' : 'DEVOLUCIÓN' ?>
                                </span>
                            </td>
                            <td><?= e($reg['EntregadoPor']) ?></td>
                            <td><?= e($reg['RecibidoPor']) ?></td>
                            <td class="text-center"><?= (int)$reg['TotalLineas'] ?></td>
                            <td class="text-center text-bold"><?= number_format((float)$reg['TotalCantidad'], 0) ?></td>
                            <td class="text-center">
                                <div class="actions" style="justify-content: center;">
                                    <a href="<?= url('insumos-enfermeria/show/' . $reg['ID']) ?>" class="btn btn--sm btn--secondary">Ver</a>
                                    <a href="<?= url('insumos-enfermeria/print/' . $reg['ID']) ?>" class="btn btn--sm btn--outline" target="_blank" title="Imprimir">🖨️</a>
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
