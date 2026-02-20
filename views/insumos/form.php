<?php /** Insumos — Formulario crear/editar */ ?>

<div class="breadcrumb">
    <a href="<?= url('insumos') ?>">Insumos</a>
    <span class="separator">›</span>
    <span class="current"><?= $isEdit ? 'Editar' : 'Nuevo' ?></span>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? url('insumos/edit/' . e($item['ID'])) : url('insumos/create') ?>">
            <?= csrfField() ?>

            <div class="form-group">
                <label>Nombre <span class="required">*</span></label>
                <input type="text" name="Nombre" class="form-control <?= !empty($errors['Nombre']) ? 'form-control--error' : '' ?>"
                       value="<?= e($item['Nombre'] ?? '') ?>"
                       maxlength="255" required data-uppercase
                       placeholder="Nombre del insumo">
                <?php if (!empty($errors['Nombre'])): ?>
                    <div class="form-error"><?= e($errors['Nombre']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Tipo de Producto <span class="required">*</span></label>
                    <select name="TipoProducto" class="form-control <?= !empty($errors['TipoProducto']) ? 'form-control--error' : '' ?>" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($tipos as $t): ?>
                            <option value="<?= e($t['ID']) ?>" <?= ($item['TipoProducto'] ?? '') === $t['ID'] ? 'selected' : '' ?>>
                                <?= e($t['Descripcion']) ?> (<?= e($t['ID']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['TipoProducto'])): ?>
                        <div class="form-error"><?= e($errors['TipoProducto']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Unidad de Medida <span class="required">*</span></label>
                    <select name="MedidaInsumo" class="form-control <?= !empty($errors['MedidaInsumo']) ? 'form-control--error' : '' ?>" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($medidas as $m): ?>
                            <option value="<?= e($m['ID']) ?>" <?= ($item['MedidaInsumo'] ?? '') === $m['ID'] ? 'selected' : '' ?>>
                                <?= e($m['Descripcion']) ?> (<?= e($m['ID']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['MedidaInsumo'])): ?>
                        <div class="form-error"><?= e($errors['MedidaInsumo']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Mínimos <span class="required">*</span></label>
                    <input type="number" name="Minimos" class="form-control <?= !empty($errors['Minimos']) ? 'form-control--error' : '' ?>"
                           value="<?= e($item['Minimos'] ?? 0) ?>"
                           min="0" required>
                    <?php if (!empty($errors['Minimos'])): ?>
                        <div class="form-error"><?= e($errors['Minimos']) ?></div>
                    <?php endif; ?>
                    <div class="form-help">Stock mínimo de alerta</div>
                </div>

                <div class="form-group">
                    <label>Máximos <span class="required">*</span></label>
                    <input type="number" name="Maximos" class="form-control <?= !empty($errors['Maximos']) ? 'form-control--error' : '' ?>"
                           value="<?= e($item['Maximos'] ?? 0) ?>"
                           min="0" required>
                    <?php if (!empty($errors['Maximos'])): ?>
                        <div class="form-error"><?= e($errors['Maximos']) ?></div>
                    <?php endif; ?>
                    <div class="form-help">Debe ser ≥ al mínimo</div>
                </div>
            </div>

            <?php if ($isEdit): ?>
            <div class="form-group">
                <label>Existencia Actual</label>
                <input type="text" class="form-control" value="<?= number_format((float)($item['Existencia'] ?? 0), 2) ?>" readonly>
                <div class="form-help">Solo se modifica mediante registro de movimientos.</div>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" name="Caducidad" id="Caducidad" value="1"
                           <?= !empty($item['Caducidad']) ? 'checked' : '' ?>>
                    <label for="Caducidad">Este insumo tiene fecha de caducidad</label>
                </div>
            </div>

            <div class="form-group" id="dias-aviso-group" style="display: <?= !empty($item['Caducidad']) ? 'block' : 'none' ?>;">
                <label>Días Previos de Aviso <span class="required">*</span></label>
                <input type="number" name="DiasPreviosAviso" class="form-control <?= !empty($errors['DiasPreviosAviso']) ? 'form-control--error' : '' ?>"
                       value="<?= e($item['DiasPreviosAviso'] ?? '') ?>"
                       min="1" placeholder="Ej: 30">
                <?php if (!empty($errors['DiasPreviosAviso'])): ?>
                    <div class="form-error"><?= e($errors['DiasPreviosAviso']) ?></div>
                <?php endif; ?>
                <div class="form-help">Días de anticipación para generar alerta de vencimiento</div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Actualizar' : 'Crear Insumo' ?></button>
                <a href="<?= url('insumos') ?>" class="btn btn--secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
