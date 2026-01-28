<div class="page-header">
    <div>
        <h1><i class="fas fa-file-invoice"></i> Gestión de Facturas</h1>
        <p>Administra las facturas de venta</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo url('facturas/excel?' . http_build_query(compact('search', 'estado', 'fecha_desde', 'fecha_hasta'))); ?>" 
           class="btn btn-success">
            <i class="fas fa-file-excel"></i>
            Exportar Excel
        </a>
        <a href="<?php echo url('facturas/crear'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nueva Factura
        </a>
    </div>
</div>

<div class="stats-mini-grid">
    <div class="stat-mini-card">
        <div class="stat-mini-icon">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="stat-mini-info">
            <span class="stat-mini-label">Total</span>
            <span class="stat-mini-value"><?php echo $totalFacturas; ?></span>
        </div>
    </div>
    
    <div class="stat-mini-card stat-warning">
        <div class="stat-mini-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-mini-info">
            <span class="stat-mini-label">Pendientes</span>
            <span class="stat-mini-value"><?php echo $totalPendientes; ?></span>
        </div>
    </div>
    
    <div class="stat-mini-card stat-success">
        <div class="stat-mini-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-mini-info">
            <span class="stat-mini-label">Pagadas</span>
            <span class="stat-mini-value"><?php echo $totalPagadas; ?></span>
        </div>
    </div>
    
    <div class="stat-mini-card stat-danger">
        <div class="stat-mini-icon">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-mini-info">
            <span class="stat-mini-label">Anuladas</span>
            <span class="stat-mini-value"><?php echo $totalAnuladas; ?></span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Listado de Facturas</h2>
    </div>
    <div class="card-body">
        <div class="table-controls">
            <form method="GET" action="<?php echo url('facturas'); ?>" class="search-form">
                <div class="search-group">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Buscar por código, cliente o CI..."
                        value="<?php echo e($search); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Buscar
                    </button>
                </div>
                
                <div class="filter-group">
                    <input 
                        type="date" 
                        name="fecha_desde" 
                        class="form-control" 
                        placeholder="Desde"
                        value="<?php echo e($fecha_desde); ?>">
                </div>
                
                <div class="filter-group">
                    <input 
                        type="date" 
                        name="fecha_hasta" 
                        class="form-control" 
                        placeholder="Hasta"
                        value="<?php echo e($fecha_hasta); ?>">
                </div>
                
                <div class="filter-group">
                    <select name="estado" class="form-control" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <option value="PENDIENTE" <?php echo $estado === 'PENDIENTE' ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="PAGO_PARCIAL" <?php echo $estado === 'PAGO_PARCIAL' ? 'selected' : ''; ?>>Pago Parcial</option>
                        <option value="PAGADA" <?php echo $estado === 'PAGADA' ? 'selected' : ''; ?>>Pagada</option>
                        <option value="ANULADA" <?php echo $estado === 'ANULADA' ? 'selected' : ''; ?>>Anulada</option>
                    </select>
                </div>
            </form>
        </div>

        <?php if (empty($facturas)): ?>
            <div class="empty-state">
                <i class="fas fa-file-invoice"></i>
                <p>No se encontraron facturas</p>
                <a href="<?php echo url('facturas/crear'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Crear Primera Factura
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Adelanto</th>
                            <th>Saldo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($facturas as $factura): ?>
                        <tr>
                            <td><strong><?php echo e($factura->codigo); ?></strong></td>
                            <td><?php echo formatDate($factura->fecha); ?></td>
                            <td>
                                <?php echo e($factura->getClienteNombreCompleto()); ?>
                                <br>
                                <small class="text-muted">CI: <?php echo e($factura->cliente_ci); ?></small>
                            </td>
                            <td><?php echo formatMoney($factura->total); ?></td>
                            <td><?php echo formatMoney($factura->adelanto); ?></td>
                            <td>
                                <strong class="<?php echo $factura->saldo > 0 ? 'text-danger' : 'text-success'; ?>">
                                    <?php echo formatMoney($factura->saldo); ?>
                                </strong>
                            </td>
                            <td>
                                <span class="badge <?php echo $factura->getEstadoBadgeClass(); ?>">
                                    <?php echo $factura->getEstadoTexto(); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo url('facturas/ver/' . $factura->id_factura); ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo url('facturas/pdf/' . $factura->id_factura); ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="Descargar PDF"
                                       target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <a href="<?php echo url('facturas/imprimir/' . $factura->id_factura); ?>" 
                                       class="btn btn-sm btn-secondary" 
                                       title="Imprimir"
                                       target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <?php if (!$factura->isAnulada()): ?>
                                    <button type="button" 
                                            class="btn btn-sm btn-warning" 
                                            onclick="anularFactura(<?php echo $factura->id_factura; ?>, '<?php echo e($factura->codigo); ?>')"
                                            title="Anular">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1): ?>
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Mostrando <?php echo (($page - 1) * $perPage) + 1; ?> - 
                    <?php echo min($page * $perPage, $totalFacturas); ?> 
                    de <?php echo $totalFacturas; ?> facturas
                </div>
                
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo url('facturas?page=' . ($page - 1) . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '') . ($fecha_desde ? '&fecha_desde=' . $fecha_desde : '') . ($fecha_hasta ? '&fecha_hasta=' . $fecha_hasta : '')); ?>" 
                           class="pagination-link">
                            <i class="fas fa-chevron-left"></i>
                            Anterior
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1): ?>
                        <a href="<?php echo url('facturas?page=1' . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '') . ($fecha_desde ? '&fecha_desde=' . $fecha_desde : '') . ($fecha_hasta ? '&fecha_hasta=' . $fecha_hasta : '')); ?>" 
                           class="pagination-link">1</a>
                        <?php if ($startPage > 2): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="<?php echo url('facturas?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '') . ($fecha_desde ? '&fecha_desde=' . $fecha_desde : '') . ($fecha_hasta ? '&fecha_hasta=' . $fecha_hasta : '')); ?>" 
                           class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                        <a href="<?php echo url('facturas?page=' . $totalPages . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '') . ($fecha_desde ? '&fecha_desde=' . $fecha_desde : '') . ($fecha_hasta ? '&fecha_hasta=' . $fecha_hasta : '')); ?>" 
                           class="pagination-link"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo url('facturas?page=' . ($page + 1) . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '') . ($fecha_desde ? '&fecha_desde=' . $fecha_desde : '') . ($fecha_hasta ? '&fecha_hasta=' . $fecha_hasta : '')); ?>" 
                           class="pagination-link">
                            Siguiente
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div id="modalAnular" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Anular Factura</h3>
            <button type="button" class="modal-close" onclick="cerrarModalAnular()">&times;</button>
        </div>
        <div class="modal-body">
            <p>¿Está seguro que desea anular la factura <strong id="facturaCodigoAnular"></strong>?</p>
            <p class="text-danger"><strong>Esta acción devolverá el stock y eliminará el registro de cuenta corriente.</strong></p>
            
            <div class="form-group">
                <label for="motivoAnulacion">
                    <i class="fas fa-comment"></i>
                    Motivo de anulación
                    <span class="text-danger">*</span>
                </label>
                <textarea 
                    id="motivoAnulacion" 
                    class="form-control" 
                    rows="3" 
                    placeholder="Escriba el motivo de la anulación..."
                    required></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="cerrarModalAnular()">Cancelar</button>
            <button type="button" class="btn btn-danger" onclick="confirmarAnulacion()">Anular Factura</button>
        </div>
    </div>
</div>