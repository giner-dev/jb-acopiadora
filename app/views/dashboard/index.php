<div class="page-header">
    <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
    <p>Bienvenido al sistema de gestión JB Acopiadora</p>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-icon icon-primary">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3>Clientes</h3>
            <p class="stat-number"><?php echo number_format($estadisticas['total_clientes']); ?></p>
            <span class="stat-label">Total activos</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon icon-secondary">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-info">
            <h3>Productos</h3>
            <p class="stat-number"><?php echo number_format($estadisticas['total_productos']); ?></p>
            <span class="stat-label">En inventario</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon icon-warning">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="stat-info">
            <h3>Facturas Pendientes</h3>
            <p class="stat-number"><?php echo number_format($estadisticas['facturas_pendientes']); ?></p>
            <span class="stat-label"><?php echo formatMoney($estadisticas['monto_facturas_pendientes']); ?></span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon <?php echo $estadisticas['saldo_total'] >= 0 ? 'icon-success' : 'icon-danger'; ?>">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-info">
            <h3>Saldo Total</h3>
            <p class="stat-number <?php echo $estadisticas['saldo_total'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                <?php echo formatMoney($estadisticas['saldo_total']); ?>
            </p>
            <span class="stat-label">Cuenta corriente</span>
        </div>
    </div>
</div>

<div class="dashboard-row">
    <div class="dashboard-col-8">
        <?php if (!empty($productosBajoStock)): ?>
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Productos con Stock Bajo</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Stock Actual</th>
                                <th>Stock Mínimo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productosBajoStock as $producto): ?>
                            <tr>
                                <td><strong><?php echo e($producto['nombre']); ?></strong></td>
                                <td>
                                    <?php echo number_format($producto['stock_actual'], 2); ?> 
                                    <?php echo e($producto['unidad'] ?? ''); ?>
                                </td>
                                <td>
                                    <?php echo number_format($producto['stock_minimo'], 2); ?> 
                                    <?php echo e($producto['unidad'] ?? ''); ?>
                                </td>
                                <td>
                                    <?php if ($producto['stock_actual'] <= 0): ?>
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times-circle"></i>
                                            Sin Stock
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Stock Bajo
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer-link">
                    <a href="<?php echo url('productos'); ?>">
                        Ver todos los productos <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-file-invoice-dollar"></i> Últimas Facturas Pendientes</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($ultimasFacturas)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Saldo</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimasFacturas as $factura): ?>
                                <tr>
                                    <td><strong><?php echo e($factura['codigo']); ?></strong></td>
                                    <td><?php echo e($factura['nombres'] . ' ' . $factura['apellidos']); ?></td>
                                    <td><?php echo formatDate($factura['fecha']); ?></td>
                                    <td><?php echo formatMoney($factura['total']); ?></td>
                                    <td class="text-danger"><strong><?php echo formatMoney($factura['saldo']); ?></strong></td>
                                    <td>
                                        <?php if ($factura['estado'] === 'PENDIENTE'): ?>
                                            <span class="badge badge-warning">Pendiente</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">Pago Parcial</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer-link">
                        <a href="<?php echo url('facturas'); ?>">
                            Ver todas las facturas <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="empty-state-small">
                        <i class="fas fa-check-circle"></i>
                        <p>No hay facturas pendientes</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-seedling"></i> Últimos Acopios</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($ultimosAcopios)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimosAcopios as $acopio): ?>
                                <tr>
                                    <td><strong><?php echo e($acopio['codigo']); ?></strong></td>
                                    <td><?php echo e($acopio['nombres'] . ' ' . $acopio['apellidos']); ?></td>
                                    <td><?php echo formatDate($acopio['fecha']); ?></td>
                                    <td class="text-success"><strong><?php echo formatMoney($acopio['total']); ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer-link">
                        <a href="<?php echo url('acopios'); ?>">
                            Ver todos los acopios <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="empty-state-small">
                        <i class="fas fa-info-circle"></i>
                        <p>No hay acopios registrados</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="dashboard-col-4">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-rocket"></i> Accesos Rápidos</h2>
            </div>
            <div class="card-body">
                <div class="quick-actions-vertical">
                    <a href="<?php echo url('facturas/crear'); ?>" class="quick-action-item">
                        <i class="fas fa-file-invoice"></i>
                        <span>Nueva Factura</span>
                    </a>
                    
                    <a href="<?php echo url('acopios/crear'); ?>" class="quick-action-item">
                        <i class="fas fa-seedling"></i>
                        <span>Nuevo Acopio</span>
                    </a>
                    
                    <a href="<?php echo url('clientes/crear'); ?>" class="quick-action-item">
                        <i class="fas fa-user-plus"></i>
                        <span>Nuevo Cliente</span>
                    </a>
                    
                    <a href="<?php echo url('productos/crear'); ?>" class="quick-action-item">
                        <i class="fas fa-box-open"></i>
                        <span>Nuevo Producto</span>
                    </a>
                    
                    <a href="<?php echo url('pagos/registrar'); ?>" class="quick-action-item">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span>Registrar Pago</span>
                    </a>
                </div>
            </div>
        </div>
        
        <?php if (!empty($clientesConDeuda)): ?>
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-exclamation-circle"></i> Clientes con Deuda</h2>
            </div>
            <div class="card-body">
                <div class="deudores-list">
                    <?php foreach ($clientesConDeuda as $cliente): ?>
                    <div class="deudor-item">
                        <div class="deudor-info">
                            <strong><?php echo e($cliente['nombres'] . ' ' . $cliente['apellidos']); ?></strong>
                            <small>CI: <?php echo e($cliente['ci']); ?></small>
                        </div>
                        <div class="deudor-monto text-danger">
                            <?php echo formatMoney(abs($cliente['saldo'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer-link">
                    <a href="<?php echo url('cuenta-corriente'); ?>">
                        Ver cuenta corriente <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>