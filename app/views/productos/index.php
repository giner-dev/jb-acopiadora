<div class="page-header">
    <div>
        <h1><i class="fas fa-box"></i> Gestión de Productos</h1>
        <p>Administra el inventario de productos</p>
    </div>
    <div>
        <a href="<?php echo url('productos/crear'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nuevo Producto
        </a>
    </div>
</div>

<div class="stats-mini-grid">
    <div class="stat-mini-card">
        <div class="stat-mini-icon">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-mini-info">
            <span class="stat-mini-label">Total</span>
            <span class="stat-mini-value"><?php echo $totalProductos; ?></span>
        </div>
    </div>
    
    <div class="stat-mini-card stat-success">
        <div class="stat-mini-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-mini-info">
            <span class="stat-mini-label">Activos</span>
            <span class="stat-mini-value"><?php echo $totalActivos; ?></span>
        </div>
    </div>
    
    <div class="stat-mini-card stat-danger">
        <div class="stat-mini-icon">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-mini-info">
            <span class="stat-mini-label">Inactivos</span>
            <span class="stat-mini-value"><?php echo $totalInactivos; ?></span>
        </div>
    </div>
    
    <div class="stat-mini-card stat-warning">
        <div class="stat-mini-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-mini-info">
            <span class="stat-mini-label">Bajo Stock</span>
            <span class="stat-mini-value"><?php echo $totalBajoStock; ?></span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Listado de Productos</h2>
    </div>
    <div class="card-body">
        <div class="table-controls">
            <form method="GET" action="<?php echo url('productos'); ?>" class="search-form">
                <div class="search-group">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Buscar por código o nombre..."
                        value="<?php echo e($search); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Buscar
                    </button>
                </div>
                
                <div class="filter-group">
                    <select name="categoria_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo $categoria['id_categoria']; ?>" 
                                    <?php echo $categoria_id == $categoria['id_categoria'] ? 'selected' : ''; ?>>
                                <?php echo e($categoria['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="estado" class="form-control" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <option value="activo" <?php echo $estado === 'activo' ? 'selected' : ''; ?>>Activos</option>
                        <option value="inactivo" <?php echo $estado === 'inactivo' ? 'selected' : ''; ?>>Inactivos</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="bajo_stock" value="1" 
                               <?php echo $bajo_stock === '1' ? 'checked' : ''; ?>
                               onchange="this.form.submit()">
                        <span>Solo bajo stock</span>
                    </label>
                </div>
            </form>
        </div>

        <?php if (empty($productos)): ?>
            <div class="empty-state">
                <i class="fas fa-box"></i>
                <p>No se encontraron productos</p>
                <a href="<?php echo url('productos/crear'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Registrar Primer Producto
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td><strong><?php echo e($producto->codigo ?? '-'); ?></strong></td>
                            <td><?php echo e($producto->nombre); ?></td>
                            <td><?php echo e($producto->categoria_nombre ?? '-'); ?></td>
                            <td><?php echo formatMoney($producto->precio_venta); ?></td>
                            <td>
                                <span class="stock-badge <?php echo $producto->getStockStatus(); ?>">
                                    <?php echo number_format($producto->stock_actual, 2); ?> 
                                    <?php echo e($producto->unidad_codigo ?? ''); ?>
                                </span>
                                <?php if ($producto->tieneBajoStock()): ?>
                                    <small class="text-muted">
                                        (Mín: <?php echo number_format($producto->stock_minimo, 2); ?>)
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($producto->estado === 'activo'): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo url('productos/ver/' . $producto->id_producto); ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo url('productos/editar/' . $producto->id_producto); ?>" 
                                       class="btn btn-sm btn-warning" 
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="eliminarProducto(<?php echo $producto->id_producto; ?>, '<?php echo e($producto->nombre); ?>')"
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
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
                    <?php echo min($page * $perPage, $totalProductos); ?> 
                    de <?php echo $totalProductos; ?> productos
                </div>
                
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo url('productos?page=' . ($page - 1) . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '') . ($categoria_id ? '&categoria_id=' . $categoria_id : '') . ($bajo_stock ? '&bajo_stock=1' : '')); ?>" 
                           class="pagination-link">
                            <i class="fas fa-chevron-left"></i>
                            Anterior
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1): ?>
                        <a href="<?php echo url('productos?page=1' . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '') . ($categoria_id ? '&categoria_id=' . $categoria_id : '') . ($bajo_stock ? '&bajo_stock=1' : '')); ?>" 
                           class="pagination-link">1</a>
                        <?php if ($startPage > 2): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="<?php echo url('productos?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '') . ($categoria_id ? '&categoria_id=' . $categoria_id : '') . ($bajo_stock ? '&bajo_stock=1' : '')); ?>" 
                           class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                        <a href="<?php echo url('productos?page=' . $totalPages . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '') . ($categoria_id ? '&categoria_id=' . $categoria_id : '') . ($bajo_stock ? '&bajo_stock=1' : '')); ?>" 
                           class="pagination-link"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo url('productos?page=' . ($page + 1) . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '') . ($categoria_id ? '&categoria_id=' . $categoria_id : '') . ($bajo_stock ? '&bajo_stock=1' : '')); ?>" 
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