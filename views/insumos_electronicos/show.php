<?php
/**
 * Módulo: Ficha detalle de Activo Electrónico
 * Las contraseñas se muestran ocultas con toggle de visibilidad.
 */
$extraScripts = ['insumos_electronicos.js'];
?>

<div class="breadcrumb">
    <a href="<?= url('insumos-electronicos') ?>">Activos Electrónicos</a>
    <span class="separator">›</span>
    <span class="current"><?= e($item['NoSerie']) ?></span>
</div>

<!-- Banner de baja -->
<?php if (!$item['Activo']): ?>
<div class="alert alert--danger mb-4">
    <strong>⚠️ Activo dado de baja</strong><br>
    Fecha: <strong><?= formatDateTime($item['fecha_baja']) ?></strong><br>
    Motivo: <?= e($item['comentarios_baja'] ?? '—') ?>
</div>
<?php endif; ?>

<!-- Acciones -->
<div class="toolbar mb-4">
    <div class="toolbar-left">
        <?php if ($item['Activo']): ?>
            <a href="<?= url('insumos-electronicos/edit/' . $item['ID']) ?>" class="btn btn--primary">✏️ Editar</a>
            <button type="button" class="btn btn--danger" id="btn-abrir-baja"
                    data-id="<?= $item['ID'] ?>"
                    data-noserie="<?= e($item['NoSerie']) ?>">
                🗑️ Dar de Baja
            </button>
        <?php endif; ?>
        <a href="<?= url('insumos-electronicos') ?>" class="btn btn--secondary">← Volver</a>
    </div>
</div>

<div class="detail-grid">

    <!-- ── Identificación ──────────────────────────────── -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">🖥️ Identificación</h3></div>
        <div class="card-body">
            <dl class="detail-list">
                <dt>No. de Serie</dt>
                <dd class="text-bold text-mono text-lg"><?= e($item['NoSerie']) ?></dd>

                <dt>Modelo</dt>
                <dd><?= e($item['Modelo']) ?></dd>

                <dt>Marca</dt>
                <dd><?= e($item['Marca']) ?></dd>

                <dt>Tipo de Dispositivo</dt>
                <dd><?= e($item['TipoDispositivo'] ?? '—') ?></dd>

                <dt>Descripción</dt>
                <dd><?= e($item['Descripcion'] ?? '—') ?></dd>
            </dl>
        </div>
    </div>

    <!-- ── Especificaciones ────────────────────────────── -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">⚙️ Especificaciones</h3></div>
        <div class="card-body">
            <dl class="detail-list">
                <dt>Procesador</dt>
                <dd><?= e($item['Procesador'] ?? '—') ?></dd>

                <dt>RAM</dt>
                <dd><?= $item['RAM'] ? e($item['RAM']) . ' GB' : '—' ?></dd>

                <dt>Almacenamiento</dt>
                <dd><?= $item['Almacenamiento'] ? e($item['Almacenamiento']) . ' GB' : '—' ?></dd>

                <dt>Sistema Operativo</dt>
                <dd><?= e($item['SO'] ?? '—') ?></dd>
            </dl>
        </div>
    </div>

    <!-- ── Asignación ──────────────────────────────────── -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">📍 Asignación</h3></div>
        <div class="card-body">
            <dl class="detail-list">
                <dt>Ubicación</dt>
                <dd><?= e($item['Ubicacion'] ?? '—') ?></dd>

                <dt>Responsable</dt>
                <dd><?= e($item['Responsable'] ?? '—') ?></dd>

                <dt>Puesto</dt>
                <dd class="text-muted"><?= e($item['Puesto'] ?? '—') ?></dd>
            </dl>
        </div>
    </div>

    <!-- ── Acceso Remoto ───────────────────────────────── -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">🌐 Acceso Remoto</h3></div>
        <div class="card-body">
            <dl class="detail-list">
                <dt>Conexión Remota</dt>
                <dd>
                    <span class="badge badge--conexion-<?= strtolower($item['ConexionRemota']) ?>">
                        <?= e($item['ConexionRemota']) ?>
                    </span>
                </dd>

                <dt>ID / IP de Conexión</dt>
                <dd><?= e($item['ID_Conexion'] ?? '—') ?></dd>

                <dt>Pass. Conexión Remota</dt>
                <dd class="field-password">
                    <span class="pass-hidden" id="show-PassConexionRemota">••••••••</span>
                    <span class="pass-value" id="val-PassConexionRemota" style="display:none">
                        <?= $item['PassConexionRemota'] ? e($item['PassConexionRemota']) : '—' ?>
                    </span>
                    <?php if ($item['PassConexionRemota']): ?>
                        <button type="button" class="btn-toggle-pass-show"
                                data-target="PassConexionRemota" title="Mostrar contraseña">👁</button>
                    <?php endif; ?>
                </dd>
            </dl>
        </div>
    </div>

    <!-- ── Credenciales SO ─────────────────────────────── -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">🔑 Credenciales SO</h3></div>
        <div class="card-body">
            <dl class="detail-list">
                <dt>Usuario SO</dt>
                <dd><?= e($item['Usuario'] ?? '—') ?></dd>

                <dt>Contraseña SO</dt>
                <dd class="field-password">
                    <span class="pass-hidden" id="show-Pass">••••••••</span>
                    <span class="pass-value" id="val-Pass" style="display:none">
                        <?= $item['Pass'] ? e($item['Pass']) : '—' ?>
                    </span>
                    <?php if ($item['Pass']): ?>
                        <button type="button" class="btn-toggle-pass-show"
                                data-target="Pass" title="Mostrar contraseña">👁</button>
                    <?php endif; ?>
                </dd>

                <dt>Contraseña Admin</dt>
                <dd class="field-password">
                    <span class="pass-hidden" id="show-PassAdmin">••••••••</span>
                    <span class="pass-value" id="val-PassAdmin" style="display:none">
                        <?= $item['PassAdmin'] ? e($item['PassAdmin']) : '—' ?>
                    </span>
                    <?php if ($item['PassAdmin']): ?>
                        <button type="button" class="btn-toggle-pass-show"
                                data-target="PassAdmin" title="Mostrar contraseña">👁</button>
                    <?php endif; ?>
                </dd>
            </dl>
        </div>
    </div>

    <!-- ── Auditoría ───────────────────────────────────── -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">📋 Auditoría</h3></div>
        <div class="card-body">
            <dl class="detail-list">
                <dt>Creado</dt>
                <dd><?= formatDateTime($item['FechaCreacion']) ?></dd>

                <dt>Última actualización</dt>
                <dd><?= formatDateTime($item['FechaActualiza']) ?></dd>

                <?php if (!$item['Activo']): ?>
                    <dt>Fecha de Baja</dt>
                    <dd><?= formatDateTime($item['fecha_baja']) ?></dd>

                    <dt>Comentario de Baja</dt>
                    <dd><?= e($item['comentarios_baja'] ?? '—') ?></dd>
                <?php endif; ?>
            </dl>
        </div>
    </div>

</div><!-- /.detail-grid -->

<!-- Modal de Baja -->
<?php if ($item['Activo']): ?>
<div class="modal-overlay" id="modal-baja" style="display:none">
    <div class="modal">
        <div class="modal-header">
            <h3>Dar de Baja Activo</h3>
            <button class="modal-close" type="button" id="btn-cerrar-baja">&times;</button>
        </div>
        <form method="POST" action="<?= url('insumos-electronicos/baja/' . $item['ID']) ?>">
            <?= csrfField() ?>
            <div class="modal-body">
                <p>Está a punto de dar de baja el activo
                   <strong><?= e($item['Modelo']) ?> — <?= e($item['NoSerie']) ?></strong>.
                   Esta acción no se puede revertir desde la interfaz.</p>
                <div class="form-group">
                    <label>Motivo de la baja <span class="required">*</span></label>
                    <textarea name="comentarios_baja" class="form-control" rows="3" required
                              placeholder="Indique el motivo o razón de la baja…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--secondary" id="btn-cancelar-baja">Cancelar</button>
                <button type="submit" class="btn btn--danger">Confirmar Baja</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
