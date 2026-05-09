<?php /** Catálogo: Formulario de Responsable (crear/editar) */ ?>

<div class="breadcrumb">
    <a href="<?= url('responsable') ?>">Responsables</a>
    <span class="separator">›</span>
    <span class="current"><?= $isEdit ? 'Editar' : 'Nuevo' ?></span>
</div>

<div class="card" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? url('responsable/edit/' . e($item['ID'])) : url('responsable/create') ?>">
            <?= csrfField() ?>

            <div class="form-group">
                <label>Nombre Completo <span class="required">*</span></label>
                <input type="text" name="NombreResponsable"
                       class="form-control <?= !empty($errors['NombreResponsable']) ? 'form-control--error' : '' ?>"
                       value="<?= e($item['NombreResponsable'] ?? '') ?>"
                       maxlength="200" required
                       data-uppercase
                       placeholder="Nombre completo del responsable">
                <?php if (!empty($errors['NombreResponsable'])): ?>
                    <div class="form-error"><?= e($errors['NombreResponsable']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Puesto <span class="required">*</span></label>
                <select name="Puesto"
                        class="form-control <?= !empty($errors['Puesto']) ? 'form-control--error' : '' ?>"
                        required>
                    <option value="">— Seleccione un puesto —</option>
                    <?php foreach ($puestos as $puesto): ?>
                        <option value="<?= e($puesto['Descripcion']) ?>"
                            <?= ($item['Puesto'] ?? '') === $puesto['Descripcion'] ? 'selected' : '' ?>>
                            <?= e($puesto['Descripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['Puesto'])): ?>
                    <div class="form-error"><?= e($errors['Puesto']) ?></div>
                <?php endif; ?>
                <div class="form-help">
                    Gestione los puestos desde <a href="<?= url('puesto') ?>" target="_blank">Catálogo de Puestos</a>.
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Actualizar' : 'Crear' ?></button>
                <a href="<?= url('responsable') ?>" class="btn btn--secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
