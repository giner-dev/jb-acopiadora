<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?php echo e($factura->codigo); ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/facturas.css'); ?>">
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
        
        .factura-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #433F4E;
            padding-bottom: 20px;
        }
        
        .factura-header h1 {
            margin: 0;
            color: #433F4E;
            font-size: 24px;
        }
        
        .factura-header .codigo {
            font-size: 20px;
            font-weight: bold;
            color: #FFD082;
            margin-top: 10px;
        }
        
        .factura-info {
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
        
        .factura-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .factura-table th {
            background-color: #FFD082;
            color: #433F4E;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        .factura-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        
        .factura-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .factura-totales {
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
        }
        
        .factura-footer {
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
    
    <div class="factura-header">
        <h1>JB ACOPIADORA</h1>
        <p>Sistema de Gestión Empresarial</p>
        <div class="codigo">FACTURA DE VENTA</div>
        <div class="codigo"><?php echo e($factura->codigo); ?></div>
    </div>
    
    <div class="factura-info">
        <div class="info-item">
            <span class="info-label">Cliente:</span>
            <span><?php echo e($factura->getClienteNombreCompleto()); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">CI:</span>
            <span><?php echo e($factura->cliente_ci); ?></span>
        </div>
        <?php if (!empty($factura->cliente_comunidad)): ?>
        <div class="info-item">
            <span class="info-label">Comunidad:</span>
            <span><?php echo e($factura->cliente_comunidad); ?></span>
        </div>
        <?php endif; ?>
        <div class="info-item">
            <span class="info-label">Fecha:</span>
            <span><?php echo formatDate($factura->fecha); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Estado:</span>
            <span><?php echo $factura->getEstadoTexto(); ?></span>
        </div>
    </div>
    
    <table class="factura-table">
        <thead>
            <tr>
                <th style="width: 50px;">N°</th>
                <th>Producto</th>
                <th style="width: 100px;">Cantidad</th>
                <th style="width: 120px;">P. Unitario</th>
                <th style="width: 120px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php $num = 1; ?>
            <?php foreach ($factura->detalles as $detalle): ?>
            <tr>
                <td style="text-align: center;"><?php echo $num++; ?></td>
                <td><?php echo e($detalle['producto_nombre']); ?></td>
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
    
    <div class="factura-totales">
        <div class="total-row total-final">
            <span>TOTAL:</span>
            <span><?php echo formatMoney($factura->total); ?></span>
        </div>
        <?php if ($factura->adelanto > 0): ?>
        <div class="total-row">
            <span>Adelanto:</span>
            <span><?php echo formatMoney($factura->adelanto); ?></span>
        </div>
        <div class="total-row">
            <span><strong>Saldo Pendiente:</strong></span>
            <span><strong><?php echo formatMoney($factura->saldo); ?></strong></span>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="factura-footer">
        <p>Generado el <?php echo date('d/m/Y H:i:s'); ?></p>
        <p>Usuario: <?php echo e(authUserName()); ?></p>
        <p>&copy; <?php echo date('Y'); ?> JB Acopiadora - Todos los derechos reservados</p>
    </div>
</body>
</html>