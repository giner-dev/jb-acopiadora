<div class="page-header">
    <div>
        <h1><i class="fas fa-box"></i> Detalle del Producto</h1>
        <p>Información completa del producto</p>
    </div>
    <div>
        <a href="<?php echo url('productos/editar/' . $producto->id_producto); ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i>
            Editar
        </a>
        <a href="<?php echo url('productos'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Información del Producto</h2>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-barcode"></i>
                            Código:
                        </span>
                        <span class="detail-value"><?php echo e($producto->codigo ?? 'Sin código'); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-toggle-on"></i>
                            Estado:
                        </span>
                        <span class="detail-value">
                            <?php if ($producto->estado === 'activo'): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactivo</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-tag"></i>
                            Nombre:
                        </span>
                        <span class="detail-value"><?php echo e($producto->nombre); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-th-large"></i>
                            Categoría:
                        </span>
                        <span class="detail-value"><?php echo e($producto->categoria_nombre ?? 'Sin categoría'); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-balance-scale"></i>
                            Unidad de Medida:
                        </span>
                        <span class="detail-value">
                            <?php echo e($producto->unidad_nombre ?? 'Sin unidad'); ?>
                            <?php if ($producto->unidad_codigo): ?>
                                (<?php echo e($producto->unidad_codigo); ?>)
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-dollar-sign"></i>
                            Precio de Venta:
                        </span>
                        <span class="detail-value"><?php echo formatMoney($producto->precio_venta); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-calendar"></i>
                            Fecha de Registro:
                        </span>
                        <span class="detail-value"><?php echo formatDate($producto->fecha_creacion); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-calendar-check"></i>
                            Última Actualización:
                        </span>
                        <span class="detail-value"><?php echo formatDate($producto->fecha_actualizacion); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-boxes"></i> Inventario</h2>
            </div>
            <div class="card-body">
                <div class="stock-info">
                    <div class="stock-item">
                        <span class="stock-label">Stock Actual:</span>
                        <span class="stock-value <?php echo $producto->sinStock() ? 'text-danger' : ($producto->tieneBajoStock() ? 'text-warning' : 'text-success'); ?>">
                            <?php echo number_format($producto->stock_actual, 2); ?> 
                            <?php echo e($producto->unidad_codigo ?? ''); ?>
                        </span>
                    </div>
                    
                    <div class="stock-item">
                        <span class="stock-label">Stock Mínimo:</span>
                        <span class="stock-value">
                            <?php echo number_format($producto->stock_minimo, 2); ?> 
                            <?php echo e($producto->unidad_codigo ?? ''); ?>
                        </span>
                    </div>
                    
                    <hr>
                    
                    <?php if ($producto->sinStock()): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i>
                            Sin stock disponible
                        </div>
                    <?php elseif ($producto->tieneBajoStock()): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Stock bajo. Considere reabastecer
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            Stock suficiente
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-3">
                    <a href="<?php echo url('movimientos?producto=' . $producto->id_producto); ?>" class="btn btn-primary btn-block">
                        <i class="fas fa-history"></i>
                        Ver Movimientos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>