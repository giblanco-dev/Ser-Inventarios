<div class="page-header">
    <div class="page-title-group">
        <span class="page-icon">💉</span>
        <div>
            <h1 class="page-title"><?= e($pageTitle) ?></h1>
            <p class="page-subtitle">Catálogo de sueros sincronizado desde el ERP y sus insumos asociados.</p>
        </div>
    </div>
    <div class="page-actions">
        <form action="<?= url('catalogo-sueros/sync') ?>" method="POST" style="display:inline">
            <?= csrfField() ?>
            <button type="submit" class="btn btn--primary">
                <span class="icon">🔄</span> Sincronizar con ERP
            </button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($sueros)): ?>
            <div class="empty-state">
                <span class="empty-icon">📂</span>
                <p>No hay sueros sincronizados todavía.</p>
                <p class="text-sm text-gray">Usa "Sincronizar con ERP" para importar el catálogo desde <code>ser.sueros</code>.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID ERP</th>
                            <th>Nombre del Suero</th>
                            <th>Última Sincronización</th>
                            <th style="width: 120px;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sueros as $suero): ?>
                            <tr>
                                <td><span class="badge badge--info">#<?= e($suero['id_suero']) ?></span></td>
                                <td><strong><?= e($suero['nom_suero']) ?></strong></td>
                                <td>
                                    <div class="text-sm text-gray">
                                        <?= date('d/m/Y H:i', strtotime($suero['FechaActualiza'])) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <a href="<?= url('catalogo-sueros/edit/' . $suero['id_suero']) ?>" class="btn btn--icon btn--ghost" title="Editar Insumos">
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
