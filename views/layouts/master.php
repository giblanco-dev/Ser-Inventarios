<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Gestión de Inventarios — Consultorio de Medicina Alternativa Ser S.C.">
    <title><?= e($pageTitle ?? 'Dashboard') ?> — <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/png" href="<?= url('Design/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="app-wrapper">

    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="<?= url('Design/logo.png') ?>" alt="SER Logo">
            <div class="sidebar-brand-text">
                <h1>SER</h1>
                <small>Inventarios</small>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section">Principal</div>
            <a href="<?= url('') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
                <span class="icon">📊</span> Dashboard
            </a>

            <div class="sidebar-section">Inventario</div>
            <a href="<?= url('insumos') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'insumos' ? 'active' : '' ?>">
                <span class="icon">📦</span> Insumos
            </a>
            <a href="<?= url('movimientos') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'movimientos' ? 'active' : '' ?>">
                <span class="icon">🔄</span> Movimientos
            </a>

            <div class="sidebar-section">Enfermería</div>
            <a href="<?= url('insumos-enfermeria') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'insumos-enfermeria' ? 'active' : '' ?>">
                <span class="icon">🏥</span> Insumos Enfermería
            </a>

            <div class="sidebar-section">TI / Electrónicos</div>
            <a href="<?= url('insumos-electronicos') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'insumos-electronicos' ? 'active' : '' ?>">
                <span class="icon">🖥️</span> Activos TI
            </a>

            <div class="sidebar-section">Catálogos</div>
            <a href="<?= url('catalogo-terapias') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'catalogo-terapias' ? 'active' : '' ?>">
                <span class="icon">🧪</span> Terapias
            </a>
            <a href="<?= url('catalogo-sueros') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'catalogo-sueros' ? 'active' : '' ?>">
                <span class="icon">💉</span> Sueros
            </a>
            <a href="<?= url('medida-insumo') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'medida-insumo' ? 'active' : '' ?>">
                <span class="icon">📏</span> Unidades de Medida
            </a>
            <a href="<?= url('tipo-insumo') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'tipo-insumo' ? 'active' : '' ?>">
                <span class="icon">🏷️</span> Tipos de Insumo
            </a>
            <a href="<?= url('concepto-movimiento') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'concepto-movimiento' ? 'active' : '' ?>">
                <span class="icon">📋</span> Conceptos de Mov.
            </a>
            <a href="<?= url('ubicacion') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'ubicacion' ? 'active' : '' ?>">
                <span class="icon">📍</span> Ubicaciones
            </a>
            <a href="<?= url('tipo-dispositivo') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'tipo-dispositivo' ? 'active' : '' ?>">
                <span class="icon">💾</span> Tipos de Dispositivo
            </a>
            <a href="<?= url('responsable') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'responsable' ? 'active' : '' ?>">
                <span class="icon">👤</span> Responsables
            </a>
            <a href="<?= url('puesto') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'puesto' ? 'active' : '' ?>">
                <span class="icon">💼</span> Puestos
            </a>
        </nav>

        <div class="sidebar-footer">
            <?= e(APP_CLINIC_NAME) ?><br>
            <span class="text-xs">v<?= e(APP_VERSION) ?></span>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="topbar-toggle" id="sidebar-toggle" aria-label="Menú">☰</button>
                <h2><?= e($pageTitle ?? 'Dashboard') ?></h2>
            </div>
            <div class="topbar-right">
                <span><?= date('d/m/Y') ?></span>
                <?php if ($user = currentUser()): ?>
                    <span class="text-muted">Sesión: <?= e($user['nombre']) ?></span>
                    <form method="POST" action="<?= url('auth/logout') ?>" style="display:inline">
                        <?= csrfField() ?>
                        <button type="submit" class="btn btn--sm btn--secondary">Cerrar sesión</button>
                    </form>
                <?php endif; ?>
            </div>
        </header>

        <!-- Page Content -->
        <main class="page-content">
            <?php
            // Flash Messages
            $flash = getFlash();
            if ($flash): ?>
                <div class="flash-message flash-message--<?= e($flash['type']) ?>">
                    <span><?= e($flash['message']) ?></span>
                    <button class="flash-close" type="button">&times;</button>
                </div>
            <?php endif; ?>

            <?php
            // Render the specific page content
            if (isset($content) && file_exists($content)) {
                require $content;
            }
            ?>
        </main>
    </div>
</div>

<!-- Confirm Modal (global) -->
<div class="modal-overlay" id="confirm-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>Confirmar acción</h3>
            <button class="modal-close modal-cancel" type="button">&times;</button>
        </div>
        <div class="modal-body">
            <p class="modal-message">¿Estás seguro de realizar esta acción?</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn--secondary modal-cancel" type="button">Cancelar</button>
            <button class="btn btn--danger modal-confirm-btn" type="button">Confirmar</button>
        </div>
    </div>
</div>

<script>window.BASE_URL = '<?= rtrim(BASE_URL, '/') . '/' ?>';</script>
<script src="<?= asset('js/app.js') ?>"></script>
<?php if (isset($extraScripts)): ?>
    <?php foreach ((array)$extraScripts as $script): ?>
        <script src="<?= asset('js/' . $script) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
