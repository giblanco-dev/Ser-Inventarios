<?php /** Catálogo: Formulario de Tipo de Dispositivo (crear/editar) */ ?>

<div class="breadcrumb">
    <a href="<?= url('tipo-dispositivo') ?>">Tipos de Dispositivo</a>
    <span class="separator">›</span>
    <span class="current"><?= $isEdit ? 'Editar' : 'Nuevo' ?></span>
</div>

<div class="card" style="max-width:500px">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? url('tipo-dispositivo/edit/' . e($item['ID'])) : url('tipo-dispositivo/create') ?>">
            <?= csrfField() ?>

            <div class="form-group">
                <label>Descripción <span class="required">*</span></label>
                <input type="text" name="Descripcion"
                       class="form-control <?= !empty($errors['Descripcion']) ? 'form-control--error' : '' ?>"
                       value="<?= e($item['Descripcion'] ?? '') ?>"
                       maxlength="100" required
                       placeholder="Ej: Laptop, PC Escritorio, Impresora...">
                <?php if (!empty($errors['Descripcion'])): ?>
                    <div class="form-error"><?= e($errors['Descripcion']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Actualizar' : 'Crear' ?></button>
                <a href="<?= url('tipo-dispositivo') ?>" class="btn btn--secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
