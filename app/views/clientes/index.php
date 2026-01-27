<div class="page-header">
    <div>
        <h1><i class="fas fa-users"></i> Gestión de Clientes</h1>
        <p>Administra la información de tus clientes</p>
    </div>
    <div>
        <a href="<?php echo url('clientes/crear'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nuevo Cliente
        </a>
    </div>
</div>

<div class="stats-mini-grid">
    <div class="stat-mini-card">
        <div class="stat-mini-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-mini-info">
            <span class="stat-mini-label">Total</span>
            <span class="stat-mini-value"><?php echo $totalClientes; ?></span>
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
        <h2><i class="fas fa-list"></i> Listado de Clientes</h2>
    </div>
    <div class="card-body">
        <div class="table-controls">
            <form method="GET" action="<?php echo url('clientes'); ?>" class="search-form">
                <div class="search-group">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Buscar por CI, nombre o comunidad..."
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

        <?php if (empty($clientes)): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <p>No se encontraron clientes</p>
                <a href="<?php echo url('clientes/crear'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Registrar Primer Cliente
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>CI</th>
                            <th>Nombre Completo</th>
                            <th>Comunidad</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><strong><?php echo e($cliente->ci); ?></strong></td>
                            <td><?php echo e($cliente->getNombreCompleto()); ?></td>
                            <td><?php echo e($cliente->comunidad ?? '-'); ?></td>
                            <td><?php echo e($cliente->telefono ?? '-'); ?></td>
                            <td>
                                <?php if ($cliente->estado === 'activo'): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo url('clientes/ver/' . $cliente->id_cliente); ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo url('clientes/editar/' . $cliente->id_cliente); ?>" 
                                       class="btn btn-sm btn-warning" 
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="eliminarCliente(<?php echo $cliente->id_cliente; ?>, '<?php echo e($cliente->getNombreCompleto()); ?>')"
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
        <?php endif; ?>
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Mostrando <?php echo (($page - 1) * $perPage) + 1; ?> - 
                <?php echo min($page * $perPage, $totalClientes); ?> 
                de <?php echo $totalClientes; ?> clientes
            </div>
                
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="<?php echo url('clientes?page=' . ($page - 1) . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '')); ?>" 
                       class="pagination-link">
                        <i class="fas fa-chevron-left"></i>
                        Anterior
                    </a>
                <?php endif; ?>
                
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                if ($startPage > 1): ?>
                    <a href="<?php echo url('clientes?page=1' . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '')); ?>" 
                       class="pagination-link">1</a>
                    <?php if ($startPage > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>
                    
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="<?php echo url('clientes?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '')); ?>" 
                       class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="<?php echo url('clientes?page=' . $totalPages . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '')); ?>" 
                       class="pagination-link"><?php echo $totalPages; ?></a>
                <?php endif; ?>
                    
                <?php if ($page < $totalPages): ?>
                    <a href="<?php echo url('clientes?page=' . ($page + 1) . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '')); ?>" 
                       class="pagination-link">
                        Siguiente
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>