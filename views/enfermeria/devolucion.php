<?php
/**
 * Enfermería — Formulario de Devolución
 * Variables extras: $extraScripts = ['enfermeria.js']
 */
$extraScripts = ['enfermeria.js'];
?>

<div class="breadcrumb">
    <a href="<?= url('insumos-enfermeria') ?>">Enfermería</a>
    <span class="separator">›</span>
    <span class="current">Registrar Devolución</span>
</div>

<?php if (!empty($errors['general'])): ?>
    <div class="flash-message flash-message--error">
        <span><?= e($errors['general']) ?></span>
        <button class="flash-close" type="button">&times;</button>
    </div>
<?php endif; ?>

<form method="POST" action="<?= url('insumos-enfermeria/devolucion') ?>" id="enfermeria-form" data-tipo="devolucion">
    <?= csrfField() ?>
    <input type="hidden" name="TipoOperacion" value="DEVOLUCION">

    <div class="card">
        <div class="card-header">
            <h3>Datos de la Devolución</h3>
            <span class="badge badge--devolucion" style="font-size: 0.85rem;">DEVOLUCIÓN</span>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Entregado por <span class="required">*</span></label>
                    <input type="text" name="EntregadoPor"
                           class="form-control <?= !empty($errors['EntregadoPor']) ? 'form-control--error' : '' ?>"
                           value="<?= e($cabecera['EntregadoPor'] ?? '') ?>"
                           placeholder="Nombre de quien devuelve" required>
                    <?php if (!empty($errors['EntregadoPor'])): ?>
                        <div class="form-error"><?= e($errors['EntregadoPor']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Recibido por <span class="required">*</span></label>
                    <input type="text" name="RecibidoPor"
                           class="form-control <?= !empty($errors['RecibidoPor']) ? 'form-control--error' : '' ?>"
                           value="<?= e($cabecera['RecibidoPor'] ?? '') ?>"
                           placeholder="Nombre de quien recibe" required>
                    <?php if (!empty($errors['RecibidoPor'])): ?>
                        <div class="form-error"><?= e($errors['RecibidoPor']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="Observaciones" class="form-control" rows="2"
                          placeholder="Observaciones generales (opcional)"><?= e($cabecera['Observaciones'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Insumos a Devolver</h3>
            <div class="actions">
                <div class="search-box" style="min-width: 250px;">
                    <input type="text" id="insumo-search" class="form-control" placeholder="🔍 Buscar insumo en enfermería...">
                    <div class="search-results" id="search-results"></div>
                </div>
                <button type="button" id="btn-add-line" class="btn btn--sm btn--secondary">+ Línea manual</button>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($errors['detalles'])): ?>
                <div class="flash-message flash-message--error mb-16">
                    <span><?= e($errors['detalles']) ?></span>
                </div>
            <?php endif; ?>

            <div id="detail-lines-container" class="detail-lines enfermeria-lines">
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
                            <input type="number" name="Cantidad[]" class="form-control" min="1" step="1" value="<?= e($det['Cantidad']) ?>" required>
                            <?php if (!empty($errors["detalle_{$i}"])): ?>
                                <div class="form-error"><?= e($errors["detalle_{$i}"]) ?></div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn-remove-line" onclick="this.closest('.detail-line').remove()" title="Quitar">✕</button>
                    </div>
                    <?php endforeach;
                endif; ?>
            </div>

            <p class="text-sm text-muted" style="margin-top: 12px;">
                Busca insumos que enfermería tiene en stock. Se mostrará la existencia disponible en enfermería.
            </p>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn--warning">↩ Registrar Devolución</button>
        <a href="<?= url('insumos-enfermeria') ?>" class="btn btn--secondary">Cancelar</a>
    </div>
</form>
