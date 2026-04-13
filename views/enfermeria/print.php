<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo <?= $entrega['TipoOperacion'] === 'ENTREGA' ? 'Entrega' : 'Devolución' ?> #<?= e($entrega['ID']) ?> — SER Inventarios</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ── Recibo Imprimible — SER Inventarios ── */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            font-size: 12px;
            color: #1C2B30;
            line-height: 1.5;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .recibo-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #2D83A0;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .recibo-header .logo-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .recibo-header .logo-section img {
            width: 50px;
            height: auto;
        }
        .recibo-header .logo-section h1 {
            font-size: 16px;
            color: #1A4D5E;
            margin: 0;
        }
        .recibo-header .logo-section small {
            font-size: 10px;
            color: #4A7080;
            display: block;
        }
        .recibo-header .folio-section {
            text-align: right;
        }
        .recibo-header .folio-section .folio {
            font-size: 20px;
            font-weight: 800;
            color: #2D83A0;
        }
        .recibo-header .folio-section .tipo-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }
        .tipo-badge.entrega {
            background: #e8f8ef;
            color: #27AE60;
        }
        .tipo-badge.devolucion {
            background: #fff3e0;
            color: #F39C12;
        }
        .recibo-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
            background: #F2FAFB;
            padding: 14px 16px;
            border-radius: 6px;
            border: 1px solid #D9E8ED;
        }
        .recibo-info .info-item {
            font-size: 11px;
        }
        .recibo-info .info-item .label {
            font-weight: 600;
            color: #4A7080;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 2px;
        }
        .recibo-info .info-item .value {
            font-weight: 600;
            color: #1C2B30;
        }
        .recibo-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .recibo-table th {
            background: #E0F4F8;
            color: #1A4D5E;
            font-weight: 600;
            text-align: left;
            padding: 8px 12px;
            border-bottom: 2px solid #D9E8ED;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .recibo-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #D9E8ED;
            font-size: 11px;
        }
        .recibo-table tbody tr:nth-child(even) {
            background: #F2FAFB;
        }
        .recibo-table .text-center { text-align: center; }
        .recibo-table .text-bold { font-weight: 700; }
        .recibo-total {
            text-align: right;
            font-size: 13px;
            font-weight: 700;
            color: #1A4D5E;
            padding: 10px 12px;
            background: #E0F4F8;
            border-radius: 6px;
            margin-bottom: 40px;
        }
        .firmas {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            margin-top: 60px;
            padding-top: 20px;
        }
        .firma-box {
            text-align: center;
        }
        .firma-box .linea {
            border-top: 1px solid #1C2B30;
            margin-bottom: 6px;
            width: 100%;
        }
        .firma-box .nombre {
            font-weight: 700;
            font-size: 11px;
            color: #1C2B30;
        }
        .firma-box .rol {
            font-size: 9px;
            color: #4A7080;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .recibo-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9px;
            color: #8FA8B2;
            border-top: 1px solid #D9E8ED;
            padding-top: 12px;
        }

        /* ── Print Styles ── */
        @media print {
            body { padding: 0; }
            @page { margin: 15mm; }
            .no-print { display: none !important; }
        }

        /* ── Screen: Print Button ── */
        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: #2D83A0;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 20px;
            transition: background 0.2s;
        }
        .print-btn:hover { background: #1A4D5E; }
    </style>
</head>
<body>
    <!-- Print button (visible on screen, hidden on print) -->
    <div class="no-print" style="text-align: right; margin-bottom: 16px;">
        <button class="print-btn" onclick="window.print()">🖨️ Imprimir Recibo</button>
    </div>

    <!-- Header -->
    <div class="recibo-header">
        <div class="logo-section">
            <img src="<?= url('Design/logo.png') ?>" alt="SER Logo">
            <div>
                <h1>SER Inventarios</h1>
                <small><?= e(APP_CLINIC_NAME) ?></small>
            </div>
        </div>
        <div class="folio-section">
            <div class="folio">#<?= e($entrega['ID']) ?></div>
            <div class="tipo-badge <?= strtolower($entrega['TipoOperacion']) === 'entrega' ? 'entrega' : 'devolucion' ?>">
                <?= $entrega['TipoOperacion'] === 'ENTREGA' ? 'ENTREGA' : 'DEVOLUCIÓN' ?>
            </div>
        </div>
    </div>

    <!-- Info -->
    <div class="recibo-info">
        <div class="info-item">
            <span class="label">Fecha</span>
            <span class="value"><?= formatDateTime($entrega['FechaEntrega']) ?></span>
        </div>
        <div class="info-item">
            <span class="label">Folio</span>
            <span class="value">#<?= e($entrega['ID']) ?></span>
        </div>
        <div class="info-item">
            <span class="label">Entregado por</span>
            <span class="value"><?= e($entrega['EntregadoPor']) ?></span>
        </div>
        <div class="info-item">
            <span class="label">Recibido por</span>
            <span class="value"><?= e($entrega['RecibidoPor']) ?></span>
        </div>
        <?php if (!empty($entrega['Observaciones'])): ?>
        <div class="info-item" style="grid-column: 1 / -1;">
            <span class="label">Observaciones</span>
            <span class="value"><?= e($entrega['Observaciones']) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Detail Table -->
    <table class="recibo-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Insumo</th>
                <th>Medida</th>
                <th class="text-center">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalCantidad = 0;
            foreach ($detalles as $i => $det):
                $totalCantidad += (float)$det['Cantidad'];
            ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td class="text-bold"><?= e($det['InsumoNombre']) ?></td>
                <td><?= e($det['MedidaDescripcion']) ?></td>
                <td class="text-center text-bold"><?= number_format((float)$det['Cantidad'], 0) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="recibo-total">
        Total de artículos: <?= number_format($totalCantidad, 0) ?>
    </div>

    <!-- Signatures -->
    <div class="firmas">
        <div class="firma-box">
            <div class="linea"></div>
            <div class="nombre"><?= e($entrega['EntregadoPor']) ?></div>
            <div class="rol">Entregó</div>
        </div>
        <div class="firma-box">
            <div class="linea"></div>
            <div class="nombre"><?= e($entrega['RecibidoPor']) ?></div>
            <div class="rol">Recibió</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="recibo-footer">
        <?= e(APP_CLINIC_NAME) ?> · SER Inventarios v<?= e(APP_VERSION) ?> ·
        Generado el <?= date('d/m/Y H:i') ?>
    </div>
</body>
</html>
