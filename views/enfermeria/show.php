<?php /** Enfermería — Detalle de Entrega / Devolución */ ?>

<div class="breadcrumb">
    <a href="<?= url('insumos-enfermeria') ?>">Enfermería</a>
    <span class="separator">›</span>
    <a href="<?= url('insumos-enfermeria/historial') ?>">Historial</a>
    <span class="separator">›</span>
    <span class="current"><?= $entrega['TipoOperacion'] === 'ENTREGA' ? 'Entrega' : 'Devolución' ?> #<?= e($entrega['ID']) ?></span>
</div>

<div class="card" style="max-width: 800px;">
    <div class="card-header">
        <h3><?= $entrega['TipoOperacion'] === 'ENTREGA' ? 'Entrega' : 'Devolución' ?> #<?= e($entrega['ID']) ?></h3>
        <span class="badge badge--<?= strtolower($entrega['TipoOperacion']) === 'entrega' ? 'entrega' : 'devolucion' ?>" style="font-size: 0.85rem;">
            <?= $entrega['TipoOperacion'] === 'ENTREGA' ? 'ENTREGA' : 'DEVOLUCIÓN' ?>
        </span>
    </div>
    <div class="card-body">
        <table class="data-table" style="max-width: 500px; margin-bottom: 24px;">
            <tr>
                <td class="text-muted" style="width: 40%;">Folio</td>
                <td class="text-bold">#<?= e($entrega['ID']) ?></td>
            </tr>
            <tr>
                <td class="text-muted">Fecha</td>
                <td><?= formatDateTime($entrega['FechaEntrega']) ?></td>
            </tr>
            <tr>
                <td class="text-muted">Entregado por</td>
                <td class="text-bold"><?= e($entrega['EntregadoPor']) ?></td>
            </tr>
            <tr>
                <td class="text-muted">Recibido por</td>
                <td class="text-bold"><?= e($entrega['RecibidoPor']) ?></td>
            </tr>
            <tr>
                <td class="text-muted">Observaciones</td>
                <td><?= e($entrega['Observaciones'] ?: '—') ?></td>
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalles as $i => $det): ?>
                    <tr>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td class="text-bold"><?= e($det['InsumoNombre']) ?></td>
                        <td class="text-sm"><?= e($det['MedidaDescripcion']) ?></td>
                        <td class="text-center text-bold"><?= number_format((float)$det['Cantidad'], 0) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top: 16px; display: flex; gap: 10px;">
    <a href="<?= url('insumos-enfermeria/print/' . $entrega['ID']) ?>" class="btn btn--primary" target="_blank">🖨️ Imprimir Recibo</a>
    <a href="<?= url('insumos-enfermeria/historial') ?>" class="btn btn--secondary">← Volver al Historial</a>
</div>
