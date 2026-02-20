<?php
/**
 * Movimientos — Formulario de Registro
 * Variables extras: $extraScripts = ['movimientos.js']
 */
$extraScripts = ['movimientos.js'];
?>

<div class="breadcrumb">
    <a href="<?= url('movimientos') ?>">Movimientos</a>
    <span class="separator">›</span>
    <span class="current">Registrar Movimiento</span>
</div>

<?php if (!empty($errors['general'])): ?>
    <div class="flash-message flash-message--error">
        <span><?= e($errors['general']) ?></span>
        <button class="flash-close" type="button">&times;</button>
    </div>
<?php endif; ?>

<form method="POST" action="<?= url('movimientos/create') ?>" id="movimiento-form">
    <?= csrfField() ?>

    <div class="card">
        <div class="card-header">
            <h3>Datos del Movimiento</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Concepto de Movimiento <span class="required">*</span></label>
                    <select name="ConceptoID" class="form-control <?= !empty($errors['ConceptoID']) ? 'form-control--error' : '' ?>" required id="concepto-select">
                        <option value="">Seleccionar concepto...</option>
                        <?php foreach ($conceptos as $c): ?>
                            <option value="<?= e($c['ID']) ?>"
                                    data-tipo="<?= e($c['TipoOperacion']) ?>"
                                    <?= ($cabecera['ConceptoID'] ?? '') === $c['ID'] ? 'selected' : '' ?>>
                                <?= e($c['Descripcion']) ?> (<?= e($c['TipoOperacion']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['ConceptoID'])): ?>
                        <div class="form-error"><?= e($errors['ConceptoID']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Referencia Externa</label>
                    <input type="text" name="Referencia" class="form-control"
                           value="<?= e($cabecera['Referencia'] ?? '') ?>"
                           placeholder="Folio de factura, orden, etc.">
                </div>
            </div>

            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="Observaciones" class="form-control" rows="2"
                          placeholder="Observaciones generales del movimiento (opcional)"><?= e($cabecera['Observaciones'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Detalle de Insumos</h3>
            <div class="actions">
                <div class="search-box" style="min-width: 250px;">
                    <input type="text" id="insumo-search" class="form-control" placeholder="🔍 Buscar insumo por nombre...">
                    <div class="search-results" id="search-results"></div>
                </div>
                <input type="text" id="barcode-search" class="form-control" placeholder="📷 Escanear código..." style="max-width: 200px;">
                <button type="button" id="btn-add-line" class="btn btn--sm btn--secondary">+ Línea manual</button>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($errors['detalles'])): ?>
                <div class="flash-message flash-message--error mb-16">
                    <span><?= e($errors['detalles']) ?></span>
                </div>
            <?php endif; ?>

            <div id="detail-lines-container" class="detail-lines">
                <?php if (!empty($detalles)):
                    foreach ($detalles as $i => $det): ?>
                    <div class="detail-line" data-index="<?= $i ?>">
                        <div class="form-group">
                            <label>Insumo <span class="required">*</span></label>
                            <input type="text" class="form-control insumo-display" value="Insumo #<?= e($det['InsumoID']) ?>" readonly>
                            <input type="hidden" name="InsumoID[]" value="<?= e($det['InsumoID']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Cantidad <span class="required">*</span></label>
                            <input type="number" name="Cantidad[]" class="form-control" min="1" value="<?= e($det['Cantidad']) ?>" required>
                            <?php if (!empty($errors["detalle_{$i}"])): ?>
                                <div class="form-error"><?= e($errors["detalle_{$i}"]) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group lote-group">
                            <label>Nº Lote</label>
                            <input type="text" name="NumeroLote[]" class="form-control" value="<?= e($det['NumeroLote'] ?? '') ?>">
                            <?php if (!empty($errors["lote_{$i}"])): ?>
                                <div class="form-error"><?= e($errors["lote_{$i}"]) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group vencimiento-group">
                            <label>Vencimiento</label>
                            <input type="date" name="FechaVencimiento[]" class="form-control" value="<?= e($det['FechaVencimiento'] ?? '') ?>">
                            <?php if (!empty($errors["vencimiento_{$i}"])): ?>
                                <div class="form-error"><?= e($errors["vencimiento_{$i}"]) ?></div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn-remove-line" onclick="this.closest('.detail-line').remove()" title="Quitar">✕</button>
                    </div>
                    <?php endforeach;
                endif; ?>
            </div>

            <p class="text-sm text-muted" style="margin-top: 12px;">
                Busca insumos por nombre o escanea un código de barra para agregar líneas de detalle.
            </p>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn--primary">✓ Registrar Movimiento</button>
        <a href="<?= url('movimientos') ?>" class="btn btn--secondary">Cancelar</a>
    </div>
</form>
