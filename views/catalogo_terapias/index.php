<div class="page-header">
    <div class="page-title-group">
        <span class="page-icon">🧪</span>
        <div>
            <h1 class="page-title"><?= e($pageTitle) ?></h1>
            <p class="page-subtitle">Catálogo de terapias sincronizado desde el ERP y sus insumos asociados.</p>
        </div>
    </div>
    <div class="page-actions">
        <form action="<?= url('catalogo-terapias/sync') ?>" method="POST" style="display:inline">
            <?= csrfField() ?>
            <button type="submit" class="btn btn--primary">
                <span class="icon">🔄</span> Sincronizar con ERP
            </button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($terapias)): ?>
            <div class="empty-state">
                <span class="empty-icon">📂</span>
                <p>No hay terapias sincronizadas todavía.</p>
                <p class="text-sm text-gray">Usa "Sincronizar con ERP" para importar el catálogo desde <code>ser.terapias</code>.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID ERP</th>
                            <th>Nombre de la Terapia</th>
                            <th>Última Sincronización</th>
                            <th style="width: 120px;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($terapias as $terapia): ?>
                            <tr>
                                <td><span class="badge badge--info">#<?= e($terapia['id_terapiaINV']) ?></span></td>
                                <td><strong><?= e($terapia['nom_terapia']) ?></strong></td>
                                <td>
                                    <div class="text-sm text-gray">
                                        <?= date('d/m/Y H:i', strtotime($terapia['FechaActualiza'])) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <a href="<?= url('catalogo-terapias/edit/' . $terapia['id_terapiaINV']) ?>" class="btn btn--icon btn--ghost" title="Editar Insumos">
                                            ✏️
                                        </a>
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
