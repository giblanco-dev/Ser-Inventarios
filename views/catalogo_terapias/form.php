<div class="page-header">
    <div class="page-title-group">
        <span class="page-icon">🧪</span>
        <div>
            <h1 class="page-title"><?= e($pageTitle) ?></h1>
            <p class="page-subtitle"><?= isset($terapia) ? 'Modificar datos de la terapia y sus insumos.' : 'Registrar una nueva terapia y sus componentes.' ?></p>
        </div>
    </div>
    <div class="page-actions">
        <a href="<?= url('catalogo-terapias') ?>" class="btn btn--secondary">
            <span class="icon">⬅️</span> Volver al listado
        </a>
    </div>
</div>

<form action="<?= $action ?>" method="POST" id="terapia-form">
    
    <div class="card mb-lg">
        <div class="card-header">
            <h3>📖 Datos de la Terapia</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nom_terapia" class="form-label">Nombre de la Terapia <span class="text-danger">*</span></label>
                    <input type="text" id="nom_terapia" name="nom_terapia" class="form-control" 
                           value="<?= isset($terapia['nom_terapia']) ? e($terapia['nom_terapia']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="id_terapiaERP" class="form-label">ID ERP (Externo) <span class="text-danger">*</span></label>
                    <input type="number" id="id_terapiaERP" name="id_terapiaERP" class="form-control" 
                           value="<?= isset($terapia['id_terapiaERP']) ? e($terapia['id_terapiaERP']) : '' ?>" required>
                    <small class="text-gray text-xs mt-sm d-block">Identificador proveniente del sistema externo (ERP).</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-lg">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3>📦 Insumos Requeridos (Receta)</h3>
            <button type="button" class="btn btn--secondary btn--sm" id="btn-add-insumo">
                ➕ Añadir Insumo
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table" id="insumos-table">
                    <thead>
                        <tr>
                            <th>Insumo (Solo INTE)</th>
                            <th style="width: 200px;">Cantidad <small>(Mayor a 0)</small></th>
                            <th style="width: 80px;" class="text-center">Quitar</th>
                        </tr>
                    </thead>
                    <tbody id="insumos-body">
                        <?php if(!empty($detalles)): ?>
                            <?php foreach($detalles as $det): ?>
                                <tr>
                                    <td>
                                        <select name="insumo_id[]" class="form-control" required>
                                            <option value="">Seleccione insumo...</option>
                                            <?php foreach ($insumosDisponibles as $insumo): ?>
                                                <option value="<?= $insumo['ID'] ?>" <?= ($insumo['ID'] == $det['idInsumo']) ? 'selected' : '' ?>>
                                                    <?= e($insumo['Nombre']) ?> (Stock: <?= e($insumo['Existencia']) ?> <?= e($insumo['MedidaDescripcion']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="cantidad[]" class="form-control" step="0.01" min="0.01" value="<?= e($det['CantidadInsumo']) ?>" required>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn--icon btn--ghost text-danger btn-remove-row" title="Quitar">
                                            🗑️
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if(empty($detalles)): ?>
                    <div id="no-insumos-msg" class="text-center text-gray p-md">
                        No hay insumos añadidos. Haga clic en "Añadir Insumo" para empezar.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="form-actions text-right">
        <button type="submit" class="btn btn--primary">
            <span class="icon">💾</span> Guardar Terapia
        </button>
    </div>
</form>

<!-- Template Row para JS -->
<template id="insumo-row-template">
    <tr>
        <td>
            <select name="insumo_id[]" class="form-control" required>
                <option value="">Seleccione insumo...</option>
                <?php foreach ($insumosDisponibles as $insumo): ?>
                    <option value="<?= $insumo['ID'] ?>">
                        <?= e($insumo['Nombre']) ?> (Stock: <?= e($insumo['Existencia']) ?> <?= e($insumo['MedidaDescripcion']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <input type="number" name="cantidad[]" class="form-control" step="0.01" min="0.01" value="1.00" required>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn--icon btn--ghost text-danger btn-remove-row" title="Quitar">
                🗑️
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnAdd = document.getElementById('btn-add-insumo');
    const tbody = document.getElementById('insumos-body');
    const template = document.getElementById('insumo-row-template');
    const noInsumosMsg = document.getElementById('no-insumos-msg');

    function checkEmpty() {
        if (noInsumosMsg) {
            if (tbody.children.length === 0) {
                noInsumosMsg.style.display = 'block';
            } else {
                noInsumosMsg.style.display = 'none';
            }
        }
    }

    btnAdd.addEventListener('click', () => {
        const clone = template.content.cloneNode(true);
        tbody.appendChild(clone);
        checkEmpty();
    });

    tbody.addEventListener('click', (e) => {
        if (e.target.closest('.btn-remove-row')) {
            e.target.closest('tr').remove();
            checkEmpty();
        }
    });

    // Validar antes de enviar que las cantidades sean > 0 y no haya insumos repetidos
    document.getElementById('terapia-form').addEventListener('submit', (e) => {
        const selects = document.querySelectorAll('select[name="insumo_id[]"]');
        const ids = [];
        let hasDuplicates = false;
        
        selects.forEach(select => {
            if (select.value) {
                if (ids.includes(select.value)) {
                    hasDuplicates = true;
                }
                ids.push(select.value);
            }
        });

        if (hasDuplicates) {
            e.preventDefault();
            alert('Existe al menos un insumo duplicado en la lista. Por favor consolídalo o elimina el duplicado.');
        }
        
        // Cantidades ya son validadas por HTML5 (min="0.01")
    });
});
</script>
