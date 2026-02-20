<?php /** Movimientos — Vista de detalle de un movimiento */ ?>

<div class="breadcrumb">
    <a href="<?= url('movimientos') ?>">Movimientos</a>
    <span class="separator">›</span>
    <span class="current">Movimiento #<?= e($movimiento['ID']) ?></span>
</div>

<div class="card" style="max-width: 800px;">
    <div class="card-header">
        <h3>Movimiento #<?= e($movimiento['ID']) ?></h3>
        <span class="badge badge--<?= strtolower($concepto['TipoOperacion']) === 'entrada' ? 'entrada' : 'salida' ?>" style="font-size: 0.85rem;">
            <?= e($concepto['TipoOperacion']) ?>
        </span>
    </div>
    <div class="card-body">
        <table class="data-table" style="max-width: 500px; margin-bottom: 24px;">
            <tr>
                <td class="text-muted" style="width: 40%;">Concepto</td>
                <td class="text-bold"><?= e($concepto['Descripcion']) ?> <span class="text-xs text-muted">(<?= e($concepto['ID']) ?>)</span></td>
            </tr>
            <tr>
                <td class="text-muted">Fecha</td>
                <td><?= formatDateTime($movimiento['FechaMovimiento']) ?></td>
            </tr>
            <tr>
                <td class="text-muted">Referencia</td>
                <td><?= e($movimiento['Referencia'] ?: '—') ?></td>
            </tr>
            <tr>
                <td class="text-muted">Observaciones</td>
                <td><?= e($movimiento['Observaciones'] ?: '—') ?></td>
            </tr>
            <tr>
                <td class="text-muted">Registrado por</td>
                <td><?= e($movimiento['UsuarioRegistra'] ?: 'Sistema') ?></td>
            </tr>
        </table>

        <h3 class="mb-16" style="color: var(--color-primary-dark);">Líneas de Detalle</h3>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Insumo</th>
                        <th>Medida</th>
                        <th class="text-center">Cantidad</th>
                        <th>Nº Lote</th>
                        <th>Vencimiento</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalles as $i => $det): ?>
                    <tr>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td class="text-bold"><?= e($det['InsumoNombre']) ?></td>
                        <td class="text-sm"><?= e($det['MedidaDescripcion']) ?></td>
                        <td class="text-center text-bold"><?= number_format((float)$det['Cantidad'], 0) ?></td>
                        <td><?= e($det['NumeroLote'] ?: '—') ?></td>
                        <td><?= $det['FechaVencimiento'] ? formatDate($det['FechaVencimiento']) : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top: 16px;">
    <a href="<?= url('movimientos') ?>" class="btn btn--secondary">← Volver al Historial</a>
</div>
