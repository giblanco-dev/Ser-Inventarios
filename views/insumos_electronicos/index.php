<?php /** Módulo: Lista de Activos Electrónicos / TI */
$extraScripts = ['insumos_electronicos.js'];
?>

<div class="toolbar">
    <div class="toolbar-left">
        <form class="filter-bar" method="GET" action="<?= url('insumos-electronicos') ?>" id="form-filtros">
            <div class="form-group">
                <input type="text" name="buscar" class="form-control" placeholder="Buscar No.Serie, Modelo, Marca…"
                       value="<?= e($_GET['buscar'] ?? '') ?>" style="min-width:220px">
            </div>
            <div class="form-group">
                <select name="TipoDispositivoID" class="form-control" onchange="this.form.submit()">
                    <option value="">Tipo de dispositivo</option>
                    <?php foreach ($catalogos['tipos'] as $t): ?>
                        <option value="<?= $t['ID'] ?>" <?= ((int)($_GET['TipoDispositivoID'] ?? 0)) === (int)$t['ID'] ? 'selected' : '' ?>>
                            <?= e($t['Descripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <select name="UbicacionID" class="form-control" onchange="this.form.submit()">
                    <option value="">Ubicación</option>
                    <?php foreach ($catalogos['ubicaciones'] as $u): ?>
                        <option value="<?= $u['ID'] ?>" <?= ((int)($_GET['UbicacionID'] ?? 0)) === (int)$u['ID'] ? 'selected' : '' ?>>
                            <?= e($u['Descripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <select name="Activo" class="form-control" onchange="this.form.submit()">
                    <option value="1"  <?= ($_GET['Activo'] ?? '1') === '1' ? 'selected' : '' ?>>Activos</option>
                    <option value="0"  <?= ($_GET['Activo'] ?? '') === '0' ? 'selected' : '' ?>>Dados de baja</option>
                    <option value=""   <?= ($_GET['Activo'] ?? '') === '' && isset($_GET['Activo']) ? 'selected' : '' ?>>Todos</option>
                </select>
            </div>
            <button type="submit" class="btn btn--secondary">Filtrar</button>
            <?php if (!empty($_GET['buscar']) || !empty($_GET['TipoDispositivoID']) || !empty($_GET['UbicacionID'])): ?>
                <a href="<?= url('insumos-electronicos') ?>" class="btn btn--secondary">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="toolbar-right">
        <a href="<?= url('insumos-electronicos/create') ?>" class="btn btn--primary">+ Nuevo Activo</a>
    </div>
</div>

<div class="card">
    <div class="card-body card-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Serie</th>
                        <th>Modelo / Marca</th>
                        <th>Tipo</th>
                        <th>Ubicación</th>
                        <th>Responsable</th>
                        <th class="text-center">Conexión Remota</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="8" class="table-empty">No hay activos registrados</td></tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                        <tr class="<?= $item['Activo'] ? '' : 'row--inactive' ?>">
                            <td class="text-bold text-mono"><?= e($item['NoSerie']) ?></td>
                            <td>
                                <div><?= e($item['Modelo']) ?></div>
                                <div class="text-muted text-sm"><?= e($item['Marca']) ?></div>
                            </td>
                            <td class="text-muted"><?= e($item['TipoDispositivo'] ?? '—') ?></td>
                            <td><?= e($item['Ubicacion']) ?></td>
                            <td class="text-sm"><?= e($item['Responsable'] ?? '—') ?></td>
                            <td class="text-center">
                                <span class="badge badge--conexion-<?= strtolower($item['ConexionRemota']) ?>">
                                    <?= e($item['ConexionRemota']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($item['Activo']): ?>
                                    <span class="badge badge--activo">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge--inactivo">Baja</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="actions" style="justify-content:center">
                                    <a href="<?= url('insumos-electronicos/show/' . $item['ID']) ?>"
                                       class="btn btn--sm btn--secondary">Ver</a>
                                    <?php if ($item['Activo']): ?>
                                        <a href="<?= url('insumos-electronicos/edit/' . $item['ID']) ?>"
                                           class="btn btn--sm btn--primary">Editar</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/partials/_pagination.php'; ?>

<!-- Modal de Baja (global en esta vista) -->
<div class="modal-overlay" id="modal-baja" style="display:none">
    <div class="modal">
        <div class="modal-header">
            <h3>Dar de Baja Activo</h3>
            <button class="modal-close" type="button" onclick="cerrarModalBaja()">&times;</button>
        </div>
        <form id="form-baja" method="POST" action="">
            <?= csrfField() ?>
            <div class="modal-body">
                <p>Está a punto de dar de baja el activo <strong id="baja-noserie"></strong>.</p>
                <div class="form-group">
                    <label>Motivo de la baja <span class="required">*</span></label>
                    <textarea name="comentarios_baja" id="input-comentario-baja"
                              class="form-control" rows="3" required
                              placeholder="Indique el motivo o razón de la baja…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--secondary" onclick="cerrarModalBaja()">Cancelar</button>
                <button type="submit" class="btn btn--danger">Confirmar Baja</button>
            </div>
        </form>
    </div>
</div>
