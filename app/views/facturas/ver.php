<div class="page-header">
    <div>
        <h1><i class="fas fa-file-invoice"></i> Factura <?php echo e($factura->codigo); ?></h1>
        <p>Detalle completo de la factura</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo url('facturas/pdf/' . $factura->id_factura); ?>" 
           class="btn btn-danger"
           target="_blank">
            <i class="fas fa-file-pdf"></i>
            Descargar PDF
        </a>
        <a href="<?php echo url('facturas/pdf/' . $factura->id_factura); ?>" 
           class="btn btn-secondary"
           target="_blank">
            <i class="fas fa-print"></i>
            Imprimir
        </a>
        <a href="<?php echo url('facturas'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Información de la Factura</h2>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-barcode"></i>
                            Código:
                        </span>
                        <span class="detail-value"><?php echo e($factura->codigo); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-calendar"></i>
                            Fecha:
                        </span>
                        <span class="detail-value"><?php echo formatDate($factura->fecha); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-user"></i>
                            Cliente:
                        </span>
                        <span class="detail-value"><?php echo e($factura->getClienteNombreCompleto()); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-id-card"></i>
                            CI:
                        </span>
                        <span class="detail-value"><?php echo e($factura->cliente_ci); ?></span>
                    </div>
                    
                    <?php if (!empty($factura->cliente_comunidad)): ?>
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Comunidad:
                        </span>
                        <span class="detail-value"><?php echo e($factura->cliente_comunidad); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($factura->cliente_telefono)): ?>
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-phone"></i>
                            Teléfono:
                        </span>
                        <span class="detail-value"><?php echo e($factura->cliente_telefono); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-toggle-on"></i>
                            Estado:
                        </span>
                        <span class="detail-value">
                            <span class="badge <?php echo $factura->getEstadoBadgeClass(); ?>">
                                <?php echo $factura->getEstadoTexto(); ?>
                            </span>
                        </span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-user-shield"></i>
                            Registrado por:
                        </span>
                        <span class="detail-value"><?php echo e($factura->usuario_nombre ?? 'N/A'); ?></span>
                    </div>
                </div>
                
                <?php if ($factura->isAnulada() && !empty($factura->motivo_anulacion)): ?>
                <div class="alert alert-danger mt-3">
                    <strong><i class="fas fa-ban"></i> Motivo de Anulación:</strong>
                    <p><?php echo e($factura->motivo_anulacion); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-box"></i> Detalle de Productos</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $num = 1; ?>
                            <?php foreach ($factura->detalles as $detalle): ?>
                            <tr>
                                <td><?php echo $num++; ?></td>
                                <td>
                                    <strong><?php echo e($detalle['producto_nombre']); ?></strong>
                                    <?php if (!empty($detalle['producto_codigo'])): ?>
                                        <br><small class="text-muted">Código: <?php echo e($detalle['producto_codigo']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo number_format($detalle['cantidad'], 2); ?> 
                                    <?php echo e($detalle['unidad_codigo'] ?? ''); ?>
                                </td>
                                <td><?php echo formatMoney($detalle['precio_unitario']); ?></td>
                                <td><strong><?php echo formatMoney($detalle['subtotal']); ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-calculator"></i> Totales</h2>
            </div>
            <div class="card-body">
                <div class="resumen-factura">
                    <div class="resumen-item">
                        <span class="resumen-label">Subtotal:</span>
                        <span class="resumen-value"><?php echo formatMoney($factura->subtotal); ?></span>
                    </div>
                    
                    <div class="resumen-item resumen-total">
                        <span class="resumen-label">TOTAL:</span>
                        <span class="resumen-value"><?php echo formatMoney($factura->total); ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="resumen-item">
                        <span class="resumen-label">Adelanto:</span>
                        <span class="resumen-value"><?php echo formatMoney($factura->adelanto); ?></span>
                    </div>
                    
                    <div class="resumen-item">
                        <span class="resumen-label">Saldo Pendiente:</span>
                        <span class="resumen-value <?php echo $factura->saldo > 0 ? 'text-danger' : 'text-success'; ?>">
                            <strong><?php echo formatMoney($factura->saldo); ?></strong>
                        </span>
                    </div>
                    
                    <?php if ($factura->tieneSaldoPendiente()): ?>
                    <div class="progress mt-3">
                        <div class="progress-bar" 
                             role="progressbar" 
                             style="width: <?php echo $factura->getPorcentajePagado(); ?>%">
                            <?php echo number_format($factura->getPorcentajePagado(), 1); ?>% Pagado
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!$factura->isAnulada()): ?>
                <hr>
                <button type="button" 
                        class="btn btn-warning btn-block"
                        onclick="anularFactura(<?php echo $factura->id_factura; ?>, '<?php echo e($factura->codigo); ?>')">
                    <i class="fas fa-ban"></i>
                    Anular Factura
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>