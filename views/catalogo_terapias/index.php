<div class="page-header">
    <div class="page-title-group">
        <span class="page-icon">🧪</span>
        <div>
            <h1 class="page-title"><?= e($pageTitle) ?></h1>
            <p class="page-subtitle">Gestiona el catálogo de terapias disponibles y sus insumos asociados.</p>
        </div>
    </div>
    <div class="page-actions">
        <a href="<?= url('catalogo-terapias/create') ?>" class="btn btn--primary">
            <span class="icon">➕</span> Nueva Terapia
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($terapias)): ?>
            <div class="empty-state">
                <span class="empty-icon">📂</span>
                <p>No hay terapias registradas o activas.</p>
                <a href="<?= url('catalogo-terapias/create') ?>" class="btn btn--secondary mt-md">Crear la primera terapia</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID INV</th>
                            <th style="width: 80px;">ID ERP</th>
                            <th>Nombre de la Terapia</th>
                            <th>Usuario Creación</th>
                            <th>Fecha Registro</th>
                            <th style="width: 120px;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($terapias as $terapia): ?>
                            <tr>
                                <td><span class="badge badge--info">#<?= e($terapia['id_terapiaINV']) ?></span></td>
                                <td><?= e($terapia['id_terapiaERP']) ?></td>
                                <td><strong><?= e($terapia['nom_terapia']) ?></strong></td>
                                <td>
                                    <div class="text-sm">
                                        <?= e($terapia['UsuarioCreacion']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-sm text-gray">
                                        <?= date('d/m/Y H:i', strtotime($terapia['FechaCreacion'])) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <a href="<?= url('catalogo-terapias/edit/' . $terapia['id_terapiaINV']) ?>" class="btn btn--icon btn--ghost" title="Editar Terapia">
                                            ✏️
                                        </a>
                                        <form action="<?= url('catalogo-terapias/delete/' . $terapia['id_terapiaINV']) ?>" method="POST" class="inline-form" onsubmit="return confirm('¿Estás seguro de que deseas desactivar esta terapia?');" style="display:inline;">
                                            <button type="submit" class="btn btn--icon btn--ghost text-danger" title="Desactivar">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
