<?php
/**
 * Módulo: Formulario de Activo Electrónico (Alta / Edición)
 * En edición: NoSerie se muestra como solo lectura.
 */
$extraScripts = ['insumos_electronicos.js'];
?>

<div class="breadcrumb">
    <a href="<?= url('insumos-electronicos') ?>">Activos Electrónicos</a>
    <span class="separator">›</span>
    <span class="current"><?= $isEdit ? 'Editar: ' . e($item['NoSerie'] ?? '') : 'Nuevo Activo' ?></span>
</div>

<?php if (!empty($errors['general'])): ?>
    <div class="flash-message flash-message--error">
        <span><?= e($errors['general']) ?></span>
    </div>
<?php endif; ?>

<form method="POST"
      action="<?= $isEdit ? url('insumos-electronicos/edit/' . e($item['ID'])) : url('insumos-electronicos/create') ?>"
      id="form-activo" novalidate>
    <?= csrfField() ?>

    <!-- ══ SECCIÓN: Identificación ══════════════════════════ -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">🖥️ Identificación del Activo</h3>
        </div>
        <div class="card-body">
            <div class="form-grid form-grid--3col">

                <!-- No. de Serie -->
                <div class="form-group">
                    <label for="NoSerie">No. de Serie <span class="required">*</span></label>
                    <?php if ($isEdit): ?>
                        <div class="input-readonly">
                            <span class="input-icon-left">📷</span>
                            <input type="text" id="NoSerie" class="form-control form-control--readonly"
                                   value="<?= e($item['NoSerie'] ?? '') ?>" readonly
                                   title="El No. de Serie no puede modificarse después del alta">
                        </div>
                        <div class="form-help">El No. de Serie es inmutable una vez registrado.</div>
                    <?php else: ?>
                        <div class="input-with-icon">
                            <span class="input-icon-left">📷</span>
                            <input type="text" id="NoSerie" name="NoSerie"
                                   class="form-control <?= !empty($errors['NoSerie']) ? 'form-control--error' : '' ?>"
                                   value="<?= e($item['NoSerie'] ?? '') ?>"
                                   maxlength="100"
                                   autofocus
                                   data-uppercase
                                   placeholder="Escanee o escriba el No. de Serie">
                        </div>
                        <div class="form-help">Use el lector de código de barras y presione Enter para avanzar.</div>
                        <?php if (!empty($errors['NoSerie'])): ?>
                            <div class="form-error"><?= e($errors['NoSerie']) ?></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Modelo -->
                <div class="form-group">
                    <label for="Modelo">Modelo <span class="required">*</span></label>
                    <input type="text" id="Modelo" name="Modelo"
                           class="form-control <?= !empty($errors['Modelo']) ? 'form-control--error' : '' ?>"
                           value="<?= e($item['Modelo'] ?? '') ?>"
                           maxlength="200" required data-uppercase
                           placeholder="Ej: INSPIRON 20 MODEL 3043">
                    <?php if (!empty($errors['Modelo'])): ?>
                        <div class="form-error"><?= e($errors['Modelo']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Marca -->
                <div class="form-group">
                    <label for="Marca">Marca <span class="required">*</span></label>
                    <input type="text" id="Marca" name="Marca"
                           class="form-control <?= !empty($errors['Marca']) ? 'form-control--error' : '' ?>"
                           value="<?= e($item['Marca'] ?? '') ?>"
                           maxlength="100" required data-uppercase
                           placeholder="Ej: DELL, HP, LENOVO">
                    <?php if (!empty($errors['Marca'])): ?>
                        <div class="form-error"><?= e($errors['Marca']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Tipo de Dispositivo -->
                <div class="form-group">
                    <label for="TipoDispositivoID">Tipo de Dispositivo</label>
                    <select id="TipoDispositivoID" name="TipoDispositivoID" class="form-control">
                        <option value="">— Sin clasificar —</option>
                        <?php foreach ($catalogos['tipos'] as $t): ?>
                            <option value="<?= $t['ID'] ?>"
                                <?= ((int)($item['TipoDispositivoID'] ?? 0)) === (int)$t['ID'] ? 'selected' : '' ?>>
                                <?= e($t['Descripcion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Descripción -->
                <div class="form-group form-grid--span2">
                    <label for="Descripcion">Descripción / Notas</label>
                    <input type="text" id="Descripcion" name="Descripcion"
                           class="form-control"
                           value="<?= e($item['Descripcion'] ?? '') ?>"
                           maxlength="255"
                           placeholder="Observaciones adicionales sobre el equipo">
                </div>

            </div>
        </div>
    </div>

    <!-- ══ SECCIÓN: Especificaciones Técnicas ═══════════════ -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">⚙️ Especificaciones Técnicas</h3>
        </div>
        <div class="card-body">
            <div class="form-grid form-grid--3col">

                <div class="form-group">
                    <label for="Procesador">Procesador</label>
                    <input type="text" id="Procesador" name="Procesador"
                           class="form-control"
                           value="<?= e($item['Procesador'] ?? '') ?>"
                           maxlength="200" data-uppercase
                           placeholder="Ej: INTEL CORE i7">
                </div>

                <div class="form-group">
                    <label for="RAM">RAM (GB)</label>
                    <input type="number" id="RAM" name="RAM" step="0.01" min="0"
                           class="form-control"
                           value="<?= e($item['RAM'] ?? '') ?>"
                           placeholder="Ej: 8">
                </div>

                <div class="form-group">
                    <label for="Almacenamiento">Almacenamiento (GB)</label>
                    <input type="number" id="Almacenamiento" name="Almacenamiento" step="0.01" min="0"
                           class="form-control"
                           value="<?= e($item['Almacenamiento'] ?? '') ?>"
                           placeholder="Ej: 512">
                </div>

                <div class="form-group">
                    <label for="SO">Sistema Operativo</label>
                    <input type="text" id="SO" name="SO"
                           class="form-control"
                           value="<?= e($item['SO'] ?? '') ?>"
                           maxlength="100"
                           placeholder="Ej: W10 SINGLE L">
                </div>

            </div>
        </div>
    </div>

    <!-- ══ SECCIÓN: Asignación ══════════════════════════════ -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">📍 Asignación</h3>
        </div>
        <div class="card-body">
            <div class="form-grid form-grid--3col">

                <!-- Ubicación (requerida) -->
                <div class="form-group">
                    <label for="UbicacionID">Ubicación <span class="required">*</span></label>
                    <select id="UbicacionID" name="UbicacionID"
                            class="form-control <?= !empty($errors['UbicacionID']) ? 'form-control--error' : '' ?>"
                            required>
                        <option value="">— Seleccione una ubicación —</option>
                        <?php foreach ($catalogos['ubicaciones'] as $u): ?>
                            <option value="<?= $u['ID'] ?>"
                                <?= ((int)($item['UbicacionID'] ?? 0)) === (int)$u['ID'] ? 'selected' : '' ?>>
                                <?= e($u['Descripcion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['UbicacionID'])): ?>
                        <div class="form-error"><?= e($errors['UbicacionID']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Responsable (opcional) -->
                <div class="form-group">
                    <label for="ResponsableID">Responsable</label>
                    <select id="ResponsableID" name="ResponsableID" class="form-control">
                        <option value="">— Sin asignar —</option>
                        <?php foreach ($catalogos['responsables'] as $r): ?>
                            <option value="<?= $r['ID'] ?>"
                                <?= ((int)($item['ResponsableID'] ?? 0)) === (int)$r['ID'] ? 'selected' : '' ?>>
                                <?= e($r['NombreResponsable']) ?> — <?= e($r['Puesto']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>
        </div>
    </div>

    <!-- ══ SECCIÓN: Acceso Remoto ════════════════════════════ -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">🌐 Acceso Remoto y Credenciales</h3>
        </div>
        <div class="card-body">
            <div class="form-grid form-grid--3col">

                <!-- Conexión Remota -->
                <div class="form-group">
                    <label for="ConexionRemota">Conexión Remota <span class="required">*</span></label>
                    <select id="ConexionRemota" name="ConexionRemota"
                            class="form-control <?= !empty($errors['ConexionRemota']) ? 'form-control--error' : '' ?>">
                        <option value="PENDIENTE" <?= ($item['ConexionRemota'] ?? 'PENDIENTE') === 'PENDIENTE' ? 'selected' : '' ?>>PENDIENTE</option>
                        <option value="SI"        <?= ($item['ConexionRemota'] ?? '') === 'SI'        ? 'selected' : '' ?>>SI</option>
                        <option value="NO"        <?= ($item['ConexionRemota'] ?? '') === 'NO'        ? 'selected' : '' ?>>NO</option>
                    </select>
                </div>

                <!-- ID de Conexión -->
                <div class="form-group">
                    <label for="ID_Conexion">ID / IP de Conexión</label>
                    <input type="text" id="ID_Conexion" name="ID_Conexion"
                           class="form-control"
                           value="<?= e($item['ID_Conexion'] ?? '') ?>"
                           maxlength="50"
                           placeholder="ID o IP para acceso remoto">
                </div>

                <!-- Contraseña Conexión Remota -->
                <div class="form-group">
                    <label for="PassConexionRemota">Pass. Conexión Remota</label>
                    <div class="input-password-wrapper">
                        <input type="password" id="PassConexionRemota" name="PassConexionRemota"
                               class="form-control"
                               value=""
                               maxlength="255"
                               autocomplete="new-password"
                               placeholder="<?= $isEdit ? 'Dejar vacío para no modificar' : 'Contraseña de conexión remota' ?>">
                        <button type="button" class="btn-toggle-pass" data-target="PassConexionRemota" title="Mostrar/ocultar">👁</button>
                    </div>
                </div>

                <!-- Separador visual -->
                <div class="form-group form-grid--span3">
                    <hr class="section-divider">
                    <p class="text-muted text-sm">Credenciales del sistema operativo</p>
                </div>

                <!-- Usuario SO -->
                <div class="form-group">
                    <label for="Usuario">Usuario SO</label>
                    <input type="text" id="Usuario" name="Usuario"
                           class="form-control"
                           value="<?= e($item['Usuario'] ?? '') ?>"
                           maxlength="100"
                           placeholder="Usuario del sistema operativo">
                </div>

                <!-- Contraseña SO -->
                <div class="form-group">
                    <label for="Pass">Contraseña SO</label>
                    <div class="input-password-wrapper">
                        <input type="password" id="Pass" name="Pass"
                               class="form-control"
                               value=""
                               maxlength="255"
                               autocomplete="new-password"
                               placeholder="<?= $isEdit ? 'Dejar vacío para no modificar' : 'Contraseña del SO' ?>">
                        <button type="button" class="btn-toggle-pass" data-target="Pass" title="Mostrar/ocultar">👁</button>
                    </div>
                </div>

                <!-- Contraseña Admin -->
                <div class="form-group">
                    <label for="PassAdmin">Contraseña Admin</label>
                    <div class="input-password-wrapper">
                        <input type="password" id="PassAdmin" name="PassAdmin"
                               class="form-control"
                               value=""
                               maxlength="255"
                               autocomplete="new-password"
                               placeholder="<?= $isEdit ? 'Dejar vacío para no modificar' : 'Contraseña administrador' ?>">
                        <button type="button" class="btn-toggle-pass" data-target="PassAdmin" title="Mostrar/ocultar">👁</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ══ Acciones ══════════════════════════════════════════ -->
    <div class="form-actions">
        <button type="submit" class="btn btn--primary btn--lg">
            <?= $isEdit ? '💾 Guardar Cambios' : '✅ Registrar Activo' ?>
        </button>
        <a href="<?= $isEdit ? url('insumos-electronicos/show/' . e($item['ID'])) : url('insumos-electronicos') ?>"
           class="btn btn--secondary btn--lg">Cancelar</a>
    </div>

</form>
