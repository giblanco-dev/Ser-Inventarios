<?php /** Insumos — Vista de detalle */ ?>

<div class="breadcrumb">
    <a href="<?= url('insumos') ?>">Insumos</a>
    <span class="separator">›</span>
    <span class="current"><?= e($item['Nombre']) ?></span>
</div>

<?php $badge = stockBadge($item['Existencia'], $item['Minimos']); ?>

<div class="card" style="max-width: 700px;">
    <div class="card-header">
        <h3><?= e($item['Nombre']) ?></h3>
        <div class="actions">
            <a href="<?= url('insumos/edit/' . $item['ID']) ?>" class="btn btn--sm btn--primary">Editar</a>
            <a href="<?= url('codigo-barra/index/' . $item['ID']) ?>" class="btn btn--sm btn--secondary">Códigos de Barra</a>
        </div>
    </div>
    <div class="card-body">
        <table class="data-table" style="max-width: 500px;">
            <tr>
                <td class="text-muted" style="width: 40%;">ID</td>
                <td class="text-bold"><?= e($item['ID']) ?></td>
            </tr>
            <tr>
                <td class="text-muted">Tipo de Producto</td>
                <td><?= e($item['TipoDescripcion']) ?> <span class="text-xs text-muted">(<?= e($item['TipoProducto']) ?>)</span></td>
            </tr>
            <tr>
                <td class="text-muted">Unidad de Medida</td>
                <td><?= e($item['MedidaDescripcion']) ?> <span class="text-xs text-muted">(<?= e($item['MedidaInsumo']) ?>)</span></td>
            </tr>
            <tr>
                <td class="text-muted">Existencia</td>
                <td>
                    <span class="text-bold"><?= number_format((float)$item['Existencia'], 0) ?></span>
                    <span class="badge badge--<?= $badge ?>" style="margin-left: 8px;"><?= stockLabel($badge) ?></span>
                </td>
            </tr>
            <tr>
                <td class="text-muted">Stock Mínimo</td>
                <td><?= (int)$item['Minimos'] ?></td>
            </tr>
            <tr>
                <td class="text-muted">Stock Máximo</td>
                <td><?= (int)$item['Maximos'] ?></td>
            </tr>
            <tr>
                <td class="text-muted">Aplica Caducidad</td>
                <td><?= $item['Caducidad'] ? 'Sí — Aviso a ' . (int)$item['DiasPreviosAviso'] . ' días' : 'No' ?></td>
            </tr>
            <tr>
                <td class="text-muted">Estado</td>
                <td>
                    <span class="badge badge--<?= $item['Activo'] ? 'activo' : 'inactivo' ?>">
                        <?= $item['Activo'] ? 'Activo' : 'Inactivo' ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td class="text-muted">Fecha de Registro</td>
                <td><?= formatDateTime($item['CreatedAt']) ?></td>
            </tr>
            <tr>
                <td class="text-muted">Última Actualización</td>
                <td><?= formatDateTime($item['UpdatedAt']) ?></td>
            </tr>
        </table>
    </div>
</div>
