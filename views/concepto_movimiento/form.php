<?php /** Catálogo: Formulario de Concepto de Movimiento */ ?>

<div class="breadcrumb">
    <a href="<?= url('concepto-movimiento') ?>">Conceptos de Movimiento</a>
    <span class="separator">›</span>
    <span class="current"><?= $isEdit ? 'Editar' : 'Nuevo' ?></span>
</div>

<div class="card" style="max-width: 500px;">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? url('concepto-movimiento/edit/' . e($item['ID'])) : url('concepto-movimiento/create') ?>">
            <?= csrfField() ?>

            <div class="form-group">
                <label>Clave <span class="required">*</span></label>
                <input type="text" name="ID" class="form-control <?= !empty($errors['ID']) ? 'form-control--error' : '' ?>"
                       value="<?= e($item['ID'] ?? '') ?>"
                       maxlength="15" data-uppercase
                       <?= $isEdit ? 'readonly' : 'required' ?>
                       placeholder="Ej: COMPRA, VENTA, MERMA">
                <?php if (!empty($errors['ID'])): ?>
                    <div class="form-error"><?= e($errors['ID']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Descripción <span class="required">*</span></label>
                <input type="text" name="Descripcion" class="form-control <?= !empty($errors['Descripcion']) ? 'form-control--error' : '' ?>"
                       value="<?= e($item['Descripcion'] ?? '') ?>"
                       maxlength="100" required
                       placeholder="Descripción del concepto">
                <?php if (!empty($errors['Descripcion'])): ?>
                    <div class="form-error"><?= e($errors['Descripcion']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Tipo de Operación <span class="required">*</span></label>
                <?php if ($isEdit): ?>
                    <input type="text" class="form-control" value="<?= e($item['TipoOperacion'] ?? '') ?>" readonly>
                    <div class="form-help">El tipo de operación no se puede cambiar después de crear.</div>
                <?php else: ?>
                    <select name="TipoOperacion" class="form-control <?= !empty($errors['TipoOperacion']) ? 'form-control--error' : '' ?>" required>
                        <option value="">Seleccionar...</option>
                        <option value="ENTRADA" <?= ($item['TipoOperacion'] ?? '') === 'ENTRADA' ? 'selected' : '' ?>>ENTRADA (suma stock)</option>
                        <option value="SALIDA" <?= ($item['TipoOperacion'] ?? '') === 'SALIDA' ? 'selected' : '' ?>>SALIDA (resta stock)</option>
                    </select>
                    <?php if (!empty($errors['TipoOperacion'])): ?>
                        <div class="form-error"><?= e($errors['TipoOperacion']) ?></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Actualizar' : 'Crear' ?></button>
                <a href="<?= url('concepto-movimiento') ?>" class="btn btn--secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
