<?php
/**
 * Vista de error genérica
 */
?>
<div class="card" style="max-width: 500px; margin: 60px auto; text-align: center;">
    <div class="card-body">
        <div style="font-size: 4rem; margin-bottom: 16px; opacity: 0.3;">
            <?= ($errorCode ?? 404) === 404 ? '🔍' : '⚠️' ?>
        </div>
        <h2 style="color: var(--color-primary-dark); margin-bottom: 8px;">
            Error <?= e($errorCode ?? 404) ?>
        </h2>
        <p class="text-muted" style="margin-bottom: 24px;">
            <?= e($errorMessage ?? 'La página solicitada no fue encontrada.') ?>
        </p>
        <a href="<?= url('') ?>" class="btn btn--primary">Volver al Dashboard</a>
    </div>
</div>
