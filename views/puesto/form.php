<?php /** Catálogo: Formulario de Puesto (crear/editar) */ ?>

<div class="breadcrumb">
    <a href="<?= url('puesto') ?>">Puestos de Trabajo</a>
    <span class="separator">›</span>
    <span class="current"><?= $isEdit ? 'Editar' : 'Nuevo' ?></span>
</div>

<div class="card" style="max-width:500px">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? url('puesto/edit/' . e($item['ID'])) : url('puesto/create') ?>">
            <?= csrfField() ?>

            <div class="form-group">
                <label>Descripción <span class="required">*</span></label>
                <input type="text" name="Descripcion"
                       class="form-control <?= !empty($errors['Descripcion']) ? 'form-control--error' : '' ?>"
                       value="<?= e($item['Descripcion'] ?? '') ?>"
                       maxlength="100" required
                       placeholder="Nombre del puesto de trabajo">
                <?php if (!empty($errors['Descripcion'])): ?>
                    <div class="form-error"><?= e($errors['Descripcion']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Actualizar' : 'Crear' ?></button>
                <a href="<?= url('puesto') ?>" class="btn btn--secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
