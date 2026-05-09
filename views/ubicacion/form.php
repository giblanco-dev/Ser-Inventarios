<?php /** Catálogo: Formulario de Ubicación (crear/editar) */ ?>

<div class="breadcrumb">
    <a href="<?= url('ubicacion') ?>">Ubicaciones</a>
    <span class="separator">›</span>
    <span class="current"><?= $isEdit ? 'Editar' : 'Nueva' ?></span>
</div>

<div class="card" style="max-width:500px">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? url('ubicacion/edit/' . e($item['ID'])) : url('ubicacion/create') ?>">
            <?= csrfField() ?>

            <div class="form-group">
                <label>Descripción <span class="required">*</span></label>
                <input type="text" name="Descripcion"
                       class="form-control <?= !empty($errors['Descripcion']) ? 'form-control--error' : '' ?>"
                       value="<?= e($item['Descripcion'] ?? '') ?>"
                       maxlength="100" required
                       placeholder="Nombre del área o espacio físico">
                <?php if (!empty($errors['Descripcion'])): ?>
                    <div class="form-error"><?= e($errors['Descripcion']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Actualizar' : 'Crear' ?></button>
                <a href="<?= url('ubicacion') ?>" class="btn btn--secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
