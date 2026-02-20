<?php /** Insumos — Gestión de Códigos de Barra */ ?>

<div class="breadcrumb">
    <a href="<?= url('insumos') ?>">Insumos</a>
    <span class="separator">›</span>
    <a href="<?= url('insumos/show/' . $insumo['ID']) ?>"><?= e($insumo['Nombre']) ?></a>
    <span class="separator">›</span>
    <span class="current">Códigos de Barra</span>
</div>

<!-- Formulario para agregar nuevo código -->
<div class="card" style="max-width: 700px;">
    <div class="card-header">
        <h3>Agregar Código de Barra</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= url('codigo-barra/create/' . $insumo['ID']) ?>">
            <?= csrfField() ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Código de Barra <span class="required">*</span></label>
                    <input type="text" name="CodigoBarra" class="form-control <?= !empty($errors['CodigoBarra']) ? 'form-control--error' : '' ?>"
                           value="<?= e($newCodigo['CodigoBarra'] ?? '') ?>"
                           maxlength="100" required placeholder="Escanear o ingresar código">
                    <?php if (!empty($errors['CodigoBarra'])): ?>
                        <div class="form-error"><?= e($errors['CodigoBarra']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Tipo de Código <span class="required">*</span></label>
                    <select name="TipoCodigo" class="form-control" required>
                        <?php foreach (['INTERNO','EAN13','EAN8','UPC_A','CODE128','CODE39','QR'] as $tipo): ?>
                            <option value="<?= $tipo ?>" <?= ($newCodigo['TipoCodigo'] ?? 'INTERNO') === $tipo ? 'selected' : '' ?>>
                                <?= $tipo ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <input type="text" name="Descripcion" class="form-control"
                           value="<?= e($newCodigo['Descripcion'] ?? '') ?>"
                           placeholder="Opcional">
                </div>
            </div>
            <button type="submit" class="btn btn--primary btn--sm">Agregar Código</button>
        </form>
    </div>
</div>

<!-- Lista de códigos existentes -->
<div class="card" style="max-width: 700px;">
    <div class="card-header">
        <h3>Códigos Registrados</h3>
    </div>
    <div class="card-body card-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($codigos)): ?>
                        <tr><td colspan="5" class="table-empty">No hay códigos registrados</td></tr>
                    <?php else: ?>
                        <?php foreach ($codigos as $cb): ?>
                        <tr>
                            <td class="text-bold" style="font-family: var(--font-mono);"><?= e($cb['CodigoBarra']) ?></td>
                            <td><span class="badge badge--info"><?= e($cb['TipoCodigo']) ?></span></td>
                            <td class="text-muted"><?= e($cb['Descripcion'] ?: '—') ?></td>
                            <td class="text-center">
                                <span class="badge badge--<?= $cb['Activo'] ? 'activo' : 'inactivo' ?>">
                                    <?= $cb['Activo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <form method="POST" action="<?= url('codigo-barra/delete/' . $cb['ID']) ?>" style="display:inline">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn--sm <?= $cb['Activo'] ? 'btn--danger' : 'btn--success' ?>"
                                            data-confirm="<?= $cb['Activo'] ? '¿Inactivar este código?' : '¿Reactivar este código?' ?>">
                                        <?= $cb['Activo'] ? 'Inactivar' : 'Reactivar' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
