<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acopio <?php echo e($acopio->codigo); ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/acopios.css'); ?>">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 20px;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .acopio-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #433F4E;
            padding-bottom: 20px;
        }
        
        .acopio-header h1 {
            margin: 0;
            color: #433F4E;
            font-size: 24px;
        }
        
        .acopio-header .codigo {
            font-size: 20px;
            font-weight: bold;
            color: #FFD082;
            margin-top: 10px;
        }
        
        .acopio-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .info-item {
            display: flex;
            gap: 10px;
        }
        
        .info-label {
            font-weight: bold;
            color: #433F4E;
        }
        
        .acopio-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .acopio-table th {
            background-color: #FFD082;
            color: #433F4E;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        .acopio-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        
        .acopio-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .acopio-totales {
            margin-top: 20px;
            text-align: right;
        }
        
        .total-row {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            padding: 5px 0;
        }
        
        .total-row.total-final {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #433F4E;
            padding-top: 10px;
            margin-top: 10px;
            color: #28a745;
        }
        
        .acopio-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #433F4E;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .print-button:hover {
            background-color: #FFD082;
            color: #433F4E;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        Imprimir
    </button>
    
    <div class="acopio-header">
        <h1>JB ACOPIADORA</h1>
        <p>Sistema de Gestión Empresarial</p>
        <div class="codigo">COMPROBANTE DE ACOPIO</div>
        <div class="codigo"><?php echo e($acopio->codigo); ?></div>
    </div>
    
    <div class="acopio-info">
        <div class="info-item">
            <span class="info-label">Cliente:</span>
            <span><?php echo e($acopio->getClienteNombreCompleto()); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">CI:</span>
            <span><?php echo e($acopio->cliente_ci); ?></span>
        </div>
        <?php if (!empty($acopio->cliente_comunidad)): ?>
        <div class="info-item">
            <span class="info-label">Comunidad:</span>
            <span><?php echo e($acopio->cliente_comunidad); ?></span>
        </div>
        <?php endif; ?>
        <div class="info-item">
            <span class="info-label">Fecha:</span>
            <span><?php echo formatDate($acopio->fecha); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Estado:</span>
            <span><?php echo $acopio->getEstadoTexto(); ?></span>
        </div>
    </div>
    
    <table class="acopio-table">
        <thead>
            <tr>
                <th style="width: 50px;">N°</th>
                <th>Grano</th>
                <th style="width: 100px;">Cantidad</th>
                <th style="width: 120px;">P. Unitario</th>
                <th style="width: 120px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php $num = 1; ?>
            <?php foreach ($acopio->detalles as $detalle): ?>
            <tr>
                <td style="text-align: center;"><?php echo $num++; ?></td>
                <td><?php echo e($detalle['grano_nombre']); ?></td>
                <td style="text-align: center;">
                    <?php echo number_format($detalle['cantidad'], 2); ?> 
                    <?php echo e($detalle['unidad_codigo'] ?? ''); ?>
                </td>
                <td style="text-align: right;"><?php echo formatMoney($detalle['precio_unitario']); ?></td>
                <td style="text-align: right;"><strong><?php echo formatMoney($detalle['subtotal']); ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="acopio-totales">
        <div class="total-row total-final">
            <span>TOTAL PAGADO:</span>
            <span><?php echo formatMoney($acopio->total); ?></span>
        </div>
    </div>
    
    <?php if (!empty($acopio->observaciones)): ?>
    <div style="margin-top: 20px;">
        <strong>Observaciones:</strong>
        <p><?php echo nl2br(e($acopio->observaciones)); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="acopio-footer">
        <p>Generado el <?php echo date('d/m/Y H:i:s'); ?></p>
        <p>Usuario: <?php echo e(authUserName()); ?></p>
        <p>&copy; <?php echo date('Y'); ?> JB Acopiadora - Todos los derechos reservados</p>
    </div>
</body>
</html>