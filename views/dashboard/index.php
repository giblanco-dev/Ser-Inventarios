<?php /** Dashboard — Vista principal con KPIs y alertas */ ?>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card kpi-card--primary">
        <span class="kpi-icon">📦</span>
        <div class="kpi-value"><?= (int)$totalActivos ?></div>
        <div class="kpi-label">Insumos Activos</div>
    </div>
    <div class="kpi-card kpi-card--danger">
        <span class="kpi-icon">🚨</span>
        <div class="kpi-value"><?= (int)$totalCritico ?></div>
        <div class="kpi-label">Stock Crítico</div>
    </div>
    <div class="kpi-card kpi-card--warning">
        <span class="kpi-icon">⚠️</span>
        <div class="kpi-value"><?= (int)$totalBajo ?></div>
        <div class="kpi-label">Stock Bajo</div>
    </div>
    <div class="kpi-card kpi-card--info">
        <span class="kpi-icon">📅</span>
        <div class="kpi-value"><?= (int)$totalVencimientos ?></div>
        <div class="kpi-label">Vencimientos Próximos</div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Stock Crítico / Bajo -->
    <div class="card">
        <div class="card-header">
            <h3>🚨 Insumos con Alerta de Stock</h3>
            <a href="<?= url('insumos?estado_stock=CRITICO') ?>" class="btn btn--sm btn--outline">Ver todos</a>
        </div>
        <div class="card-body card-body--flush">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Insumo</th>
                            <th>Tipo</th>
                            <th class="text-center">Existencia</th>
                            <th class="text-center">Mínimo</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stockCritico)): ?>
                            <tr><td colspan="5" class="table-empty">Sin alertas de stock 🎉</td></tr>
                        <?php else: ?>
                            <?php foreach ($stockCritico as $item): ?>
                            <tr>
                                <td><?= e($item['Nombre']) ?></td>
                                <td class="text-sm text-muted"><?= e($item['TipoDescripcion']) ?></td>
                                <td class="text-center text-bold"><?= (int)$item['Existencia'] ?></td>
                                <td class="text-center"><?= (int)$item['Minimos'] ?></td>
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

    <!-- Vencimientos Próximos -->
    <div class="card">
        <div class="card-header">
            <h3>📅 Vencimientos Próximos</h3>
        </div>
        <div class="card-body card-body--flush">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Insumo</th>
                            <th>Lote</th>
                            <th class="text-center">Vencimiento</th>
                            <th class="text-center">Días Rest.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($alertasVencimiento)): ?>
                            <tr><td colspan="4" class="table-empty">Sin vencimientos próximos</td></tr>
                        <?php else: ?>
                            <?php foreach ($alertasVencimiento as $alerta): ?>
                            <tr>
                                <td><?= e($alerta['Nombre']) ?></td>
                                <td class="text-sm"><?= e($alerta['NumeroLote']) ?></td>
                                <td class="text-center"><?= formatDate($alerta['FechaVencimiento']) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= (int)$alerta['DiasRestantes'] <= 7 ? 'badge--critico' : 'badge--bajo' ?>">
                                        <?= (int)$alerta['DiasRestantes'] ?> días
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

    <!-- Actividad Reciente -->
    <div class="card dashboard-grid--full">
        <div class="card-header">
            <h3>🕐 Actividad Reciente</h3>
            <a href="<?= url('movimientos') ?>" class="btn btn--sm btn--outline">Ver historial</a>
        </div>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($actividadReciente)): ?>
                            <tr><td colspan="6" class="table-empty">Sin movimientos registrados</td></tr>
                        <?php else: ?>
                            <?php foreach ($actividadReciente as $mov): ?>
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
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
