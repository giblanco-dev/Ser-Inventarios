<?php /** Enfermería — Dashboard con KPIs y stock actual */ ?>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card kpi-card--primary">
        <span class="kpi-icon">🏥</span>
        <div class="kpi-value"><?= (int)$kpis['total'] ?></div>
        <div class="kpi-label">Insumos en Enfermería</div>
    </div>
    <div class="kpi-card kpi-card--danger">
        <span class="kpi-icon">🚨</span>
        <div class="kpi-value"><?= (int)$kpis['critico'] ?></div>
        <div class="kpi-label">Stock Crítico</div>
    </div>
    <div class="kpi-card kpi-card--warning">
        <span class="kpi-icon">⚠️</span>
        <div class="kpi-value"><?= (int)$kpis['bajo'] ?></div>
        <div class="kpi-label">Stock Bajo</div>
    </div>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <div class="toolbar-left">
        <form class="filter-bar" method="GET" action="<?= url('insumos-enfermeria') ?>">
            <div class="form-group">
                <label>Buscar</label>
                <input type="text" name="buscar" class="form-control"
                       value="<?= e($filters['buscar'] ?? '') ?>"
                       placeholder="Nombre del insumo...">
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="estado" class="form-control">
                    <option value="">Todos</option>
                    <option value="OK" <?= ($filters['estado'] ?? '') === 'OK' ? 'selected' : '' ?>>OK</option>
                    <option value="BAJO" <?= ($filters['estado'] ?? '') === 'BAJO' ? 'selected' : '' ?>>Bajo</option>
                    <option value="CRITICO" <?= ($filters['estado'] ?? '') === 'CRITICO' ? 'selected' : '' ?>>Crítico</option>
                </select>
            </div>
            <div class="form-group" style="align-self: flex-end;">
                <button type="submit" class="btn btn--secondary btn--sm">Filtrar</button>
            </div>
        </form>
    </div>
    <div class="toolbar-right">
        <a href="<?= url('insumos-enfermeria/historial') ?>" class="btn btn--secondary">📋 Historial</a>
        <a href="<?= url('insumos-enfermeria/devolucion') ?>" class="btn btn--warning">↩ Devolución</a>
        <a href="<?= url('insumos-enfermeria/create') ?>" class="btn btn--primary">+ Nueva Entrega</a>
    </div>
</div>

<!-- Stock Table -->
<div class="card">
    <div class="card-body card-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Insumo</th>
                        <th>Medida</th>
                        <th class="text-center">Existencia (Enf.)</th>
                        <th class="text-center">Mín</th>
                        <th class="text-center">Máx</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="6" class="table-empty">No hay insumos registrados en enfermería</td></tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="text-bold"><?= e($item['Nombre']) ?></td>
                            <td class="text-sm text-muted"><?= e($item['MedidaDescripcion']) ?></td>
                            <td class="text-center text-bold"><?= number_format((float)$item['Existencia'], 0) ?></td>
                            <td class="text-center"><?= (int)$item['Minimos'] ?></td>
                            <td class="text-center"><?= (int)$item['Maximos'] ?></td>
                            <td class="text-center">
                                <span class="badge badge--<?= strtolower($item['EstadoStock']) ?>">
                                    <?= e($item['EstadoStock']) ?>
                                </span>
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
