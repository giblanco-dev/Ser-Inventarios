<?php /** Catálogo: Formulario de Unidad de Medida (crear/editar) */ ?>

<div class="breadcrumb">
    <a href="<?= url('medida-insumo') ?>">Unidades de Medida</a>
    <span class="separator">›</span>
    <span class="current"><?= $isEdit ? 'Editar' : 'Nueva' ?></span>
</div>

<div class="card" style="max-width: 500px;">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? url('medida-insumo/edit/' . e($item['ID'])) : url('medida-insumo/create') ?>">
            <?= csrfField() ?>

            <div class="form-group">
                <label>Clave <span class="required">*</span></label>
                <input type="text" name="ID" class="form-control <?= !empty($errors['ID']) ? 'form-control--error' : '' ?>"
                       value="<?= e($item['ID'] ?? '') ?>"
                       maxlength="10"
                       data-uppercase
                       <?= $isEdit ? 'readonly' : 'required' ?>
                       placeholder="Ej: PZA, ML, LT">
                <?php if (!empty($errors['ID'])): ?>
                    <div class="form-error"><?= e($errors['ID']) ?></div>
                <?php endif; ?>
                <?php if (!$isEdit): ?>
                    <div class="form-help">Máximo 10 caracteres. Inmutable después de crear.</div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Descripción <span class="required">*</span></label>
                <input type="text" name="Descripcion" class="form-control <?= !empty($errors['Descripcion']) ? 'form-control--error' : '' ?>"
                       value="<?= e($item['Descripcion'] ?? '') ?>"
                       maxlength="100" required
                       placeholder="Nombre de la unidad">
                <?php if (!empty($errors['Descripcion'])): ?>
                    <div class="form-error"><?= e($errors['Descripcion']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Actualizar' : 'Crear' ?></button>
                <a href="<?= url('medida-insumo') ?>" class="btn btn--secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
