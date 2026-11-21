<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso no autorizado — <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/png" href="<?= url('Design/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="card" style="max-width: 480px; margin: 80px auto; text-align: center;">
    <div class="card-body">
        <div style="font-size: 4rem; margin-bottom: 16px; opacity: 0.3;">🔒</div>
        <h2 style="color: var(--color-primary-dark); margin-bottom: 8px;">Acceso no autorizado</h2>
        <p class="text-muted" style="margin-bottom: 24px;">
            El enlace de acceso es inválido, expiró o ya fue utilizado. Cierra esta pestaña y, desde el ERP, intenta ingresar nuevamente al módulo de Inventarios.
        </p>
        <button type="button" class="btn btn--primary" onclick="window.close()">Cerrar</button>
    </div>
</div>
</body>
</html>
