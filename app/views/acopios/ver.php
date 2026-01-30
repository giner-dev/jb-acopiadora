<div class="page-header">
    <div>
        <h1><i class="fas fa-seedling"></i> Acopio <?php echo e($acopio->codigo); ?></h1>
        <p>Detalle completo del acopio</p>
    </div>
    <div class="page-header-actions">
        <?php if (!$acopio->isAnulado()): ?>
        <a href="<?php echo url('acopios/editar/' . $acopio->id_acopio); ?>" 
           class="btn btn-warning">
            <i class="fas fa-edit"></i>
            Editar
        </a>
        <?php endif; ?>
        <a href="<?php echo url('acopios/pdf/' . $acopio->id_acopio); ?>" 
           class="btn btn-danger"
           target="_blank">
            <i class="fas fa-file-pdf"></i>
            Descargar PDF
        </a>
        <a href="<?php echo url('acopios/pdf/' . $acopio->id_acopio); ?>" 
           class="btn btn-secondary"
           target="_blank">
            <i class="fas fa-print"></i>
            Imprimir
        </a>
        <a href="<?php echo url('acopios'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Información del Acopio</h2>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-barcode"></i>
                            Código:
                        </span>
                        <span class="detail-value"><?php echo e($acopio->codigo); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-calendar"></i>
                            Fecha:
                        </span>
                        <span class="detail-value"><?php echo formatDate($acopio->fecha); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-user"></i>
                            Cliente:
                        </span>
                        <span class="detail-value"><?php echo e($acopio->getClienteNombreCompleto()); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-id-card"></i>
                            CI:
                        </span>
                        <span class="detail-value"><?php echo e($acopio->cliente_ci); ?></span>
                    </div>
                    
                    <?php if (!empty($acopio->cliente_comunidad)): ?>
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Comunidad:
                        </span>
                        <span class="detail-value"><?php echo e($acopio->cliente_comunidad); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($acopio->cliente_telefono)): ?>
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-phone"></i>
                            Teléfono:
                        </span>
                        <span class="detail-value"><?php echo e($acopio->cliente_telefono); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-toggle-on"></i>
                            Estado:
                        </span>
                        <span class="detail-value">
                            <span class="badge <?php echo $acopio->getEstadoBadgeClass(); ?>">
                                <?php echo $acopio->getEstadoTexto(); ?>
                            </span>
                        </span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-user-shield"></i>
                            Registrado por:
                        </span>
                        <span class="detail-value"><?php echo e($acopio->usuario_nombre ?? 'N/A'); ?></span>
                    </div>
                    
                    <?php if (!empty($acopio->observaciones)): ?>
                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <span class="detail-label">
                            <i class="fas fa-comment"></i>
                            Observaciones:
                        </span>
                        <span class="detail-value"><?php echo nl2br(e($acopio->observaciones)); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($acopio->isAnulado() && !empty($acopio->motivo_anulacion)): ?>
                <div class="alert alert-danger mt-3">
                    <strong><i class="fas fa-ban"></i> Motivo de Anulación:</strong>
                    <p><?php echo e($acopio->motivo_anulacion); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-wheat-awn"></i> Detalle de Granos</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Grano</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $num = 1; ?>
                            <?php foreach ($acopio->detalles as $detalle): ?>
                            <tr>
                                <td><?php echo $num++; ?></td>
                                <td><strong><?php echo e($detalle['grano_nombre']); ?></strong></td>
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
                <div class="acopio-resumen-acopio">
                    <div class="acopio-resumen-item">
                        <span class="acopio-resumen-label">Subtotal:</span>
                        <span class="acopio-resumen-value"><?php echo formatMoney($acopio->subtotal); ?></span>
                    </div>

                    <div class="acopio-resumen-item acopio-resumen-total">
                        <span class="acopio-resumen-label">TOTAL PAGADO:</span>
                        <span class="acopio-resumen-value text-success">
                            <strong><?php echo formatMoney($acopio->total); ?></strong>
                        </span>
                    </div>
                </div>
                
                <div class="alert alert-success mt-3">
                    <i class="fas fa-check-circle"></i>
                    Este monto fue abonado a la cuenta corriente del cliente
                </div>
                
                <?php if (!$acopio->isAnulado()): ?>
                <hr>
                <button type="button" 
                        class="btn btn-warning btn-block"
                        onclick="AcopioModule.anularAcopio(<?php echo $acopio->id_acopio; ?>, '<?php echo e($acopio->codigo); ?>')">
                    <i class="fas fa-ban"></i>
                    Anular Acopio
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>