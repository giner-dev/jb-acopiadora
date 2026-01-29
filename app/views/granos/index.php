<div class="page-header">
    <div>
        <h1><i class="fas fa-wheat-awn"></i> Gestión de Granos</h1>
        <p>Administra el catálogo de granos acopiables</p>
    </div>
    <div>
        <a href="<?php echo url('granos/crear'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nuevo Grano
        </a>
    </div>
</div>

<div class="stats-mini-grid">
    <div class="stat-mini-card">
        <div class="stat-mini-icon">
            <i class="fas fa-wheat-awn"></i>
        </div>
        <div class="stat-mini-info">
            <span class="stat-mini-label">Total</span>
            <span class="stat-mini-value"><?php echo $totalGranos; ?></span>
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
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Listado de Granos</h2>
    </div>
    <div class="card-body">
        <div class="table-controls">
            <form method="GET" action="<?php echo url('granos'); ?>" class="search-form">
                <div class="search-group">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Buscar por nombre..."
                        value="<?php echo e($search); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Buscar
                    </button>
                </div>
                
                <div class="filter-group">
                    <select name="estado" class="form-control" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <option value="activo" <?php echo $estado === 'activo' ? 'selected' : ''; ?>>Activos</option>
                        <option value="inactivo" <?php echo $estado === 'inactivo' ? 'selected' : ''; ?>>Inactivos</option>
                    </select>
                </div>
            </form>
        </div>

        <?php if (empty($granos)): ?>
            <div class="empty-state">
                <i class="fas fa-wheat-awn"></i>
                <p>No se encontraron granos</p>
                <a href="<?php echo url('granos/crear'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Registrar Primer Grano
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Unidad</th>
                            <th>Precio Actual</th>
                            <th>Vigencia</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($granos as $grano): ?>
                        <tr>
                            <td><strong><?php echo e($grano->nombre); ?></strong></td>
                            <td><?php echo e($grano->unidad_nombre ?? 'Sin unidad'); ?></td>
                            <td>
                                <?php if ($grano->tienePrecioActual()): ?>
                                    <span class="precio-badge">
                                        <?php echo $grano->getPrecioActualFormateado(); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Sin precio</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($grano->tienePrecioActual()): ?>
                                    <small class="text-muted">
                                        <?php echo $grano->getPrecioVigencia(); ?>
                                    </small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($grano->estado === 'activo'): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo url('granos/ver/' . $grano->id_grano); ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-success" 
                                            onclick="abrirModalPrecio(<?php echo $grano->id_grano; ?>, '<?php echo e($grano->nombre); ?>')"
                                            title="Registrar precio">
                                        <i class="fas fa-dollar-sign"></i>
                                    </button>
                                    <a href="<?php echo url('granos/editar/' . $grano->id_grano); ?>" 
                                       class="btn btn-sm btn-warning" 
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="eliminarGrano(<?php echo $grano->id_grano; ?>, '<?php echo e($grano->nombre); ?>')"
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
                    <?php echo min($page * $perPage, $totalGranos); ?> 
                    de <?php echo $totalGranos; ?> granos
                </div>
                
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo url('granos?page=' . ($page - 1) . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '')); ?>" 
                           class="pagination-link">
                            <i class="fas fa-chevron-left"></i>
                            Anterior
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1): ?>
                        <a href="<?php echo url('granos?page=1' . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '')); ?>" 
                           class="pagination-link">1</a>
                        <?php if ($startPage > 2): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="<?php echo url('granos?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '')); ?>" 
                           class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                        <a href="<?php echo url('granos?page=' . $totalPages . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '')); ?>" 
                           class="pagination-link"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo url('granos?page=' . ($page + 1) . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '')); ?>" 
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

<!-- MODAL REGISTRAR PRECIO -->
<div id="modalPrecio" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="fas fa-dollar-sign"></i> Registrar Precio</h3>
            <button type="button" class="modal-close" onclick="cerrarModalPrecio()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Registrar precio para: <strong id="granoNombrePrecio"></strong></p>
            
            <form id="formRegistrarPrecio">
                <input type="hidden" id="grano_id_precio">
                
                <div class="form-group">
                    <label for="fecha_precio">
                        <i class="fas fa-calendar"></i>
                        Fecha
                        <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="fecha_precio" 
                        name="fecha" 
                        class="form-control" 
                        value="<?php echo date('Y-m-d'); ?>"
                        required>
                </div>
                
                <div class="form-group">
                    <label for="precio_valor">
                        <i class="fas fa-dollar-sign"></i>
                        Precio (Bs)
                        <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="precio_valor" 
                        name="precio" 
                        class="form-control" 
                        step="0.01"
                        min="0.01"
                        placeholder="0.00"
                        required
                        autofocus>
                    <small>Precio por unidad de medida</small>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="cerrarModalPrecio()">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="confirmarRegistroPrecio()">
                <i class="fas fa-save"></i>
                Registrar Precio
            </button>
        </div>
    </div>
</div>