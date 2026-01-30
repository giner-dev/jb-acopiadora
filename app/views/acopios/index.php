<div class="page-header">
    <div>
        <h1><i class="fas fa-seedling"></i> Gestión de Acopios</h1>
        <p>Administra los acopios de cosecha</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo url('acopios/excel?' . http_build_query(compact('search', 'estado', 'fecha_desde', 'fecha_hasta'))); ?>" 
           class="btn btn-success">
            <i class="fas fa-file-excel"></i>
            Exportar Excel
        </a>
        <a href="<?php echo url('acopios/crear'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nuevo Acopio
        </a>
    </div>
</div>

<div class="stats-mini-grid">
    <div class="stat-mini-card">
        <div class="stat-mini-icon">
            <i class="fas fa-seedling"></i>
        </div>
        <div class="stat-mini-info">
            <span class="stat-mini-label">Total</span>
            <span class="stat-mini-value"><?php echo $totalAcopios; ?></span>
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
            <span class="stat-mini-label">Anulados</span>
            <span class="stat-mini-value"><?php echo $totalAnulados; ?></span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Listado de Acopios</h2>
    </div>
    <div class="card-body">
        <div class="table-controls">
            <form method="GET" action="<?php echo url('acopios'); ?>" class="search-form">
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
                        <option value="ACTIVO" <?php echo $estado === 'ACTIVO' ? 'selected' : ''; ?>>Activo</option>
                        <option value="ANULADO" <?php echo $estado === 'ANULADO' ? 'selected' : ''; ?>>Anulado</option>
                    </select>
                </div>
            </form>
        </div>

        <?php if (empty($acopios)): ?>
            <div class="empty-state">
                <i class="fas fa-seedling"></i>
                <p>No se encontraron acopios</p>
                <a href="<?php echo url('acopios/crear'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Crear Primer Acopio
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
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($acopios as $acopio): ?>
                        <tr>
                            <td><strong><?php echo e($acopio->codigo); ?></strong></td>
                            <td><?php echo formatDate($acopio->fecha); ?></td>
                            <td>
                                <?php echo e($acopio->getClienteNombreCompleto()); ?>
                                <br>
                                <small class="text-muted">CI: <?php echo e($acopio->cliente_ci); ?></small>
                            </td>
                            <td><strong class="text-success"><?php echo formatMoney($acopio->total); ?></strong></td>
                            <td>
                                <span class="badge <?php echo $acopio->getEstadoBadgeClass(); ?>">
                                    <?php echo $acopio->getEstadoTexto(); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo url('acopios/ver/' . $acopio->id_acopio); ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (!$acopio->isAnulado()): ?>
                                    <a href="<?php echo url('acopios/editar/' . $acopio->id_acopio); ?>" 
                                       class="btn btn-sm btn-warning" 
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?php echo url('acopios/pdf/' . $acopio->id_acopio); ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="Descargar PDF"
                                       target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <a href="<?php echo url('acopios/pdf/' . $acopio->id_acopio); ?>" 
                                       class="btn btn-sm btn-secondary" 
                                       title="Imprimir"
                                       target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <?php if (!$acopio->isAnulado()): ?>
                                    <button type="button" 
                                            class="btn btn-sm btn-warning" 
                                            onclick="AcopioModule.anularAcopio(<?php echo $acopio->id_acopio; ?>, '<?php echo e($acopio->codigo); ?>')"
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
                    <?php echo min($page * $perPage, $totalAcopios); ?> 
                    de <?php echo $totalAcopios; ?> acopios
                </div>
                
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo url('acopios?page=' . ($page - 1) . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '') . ($fecha_desde ? '&fecha_desde=' . $fecha_desde : '') . ($fecha_hasta ? '&fecha_hasta=' . $fecha_hasta : '')); ?>" 
                           class="pagination-link">
                            <i class="fas fa-chevron-left"></i>
                            Anterior
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1): ?>
                        <a href="<?php echo url('acopios?page=1' . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '') . ($fecha_desde ? '&fecha_desde=' . $fecha_desde : '') . ($fecha_hasta ? '&fecha_hasta=' . $fecha_hasta : '')); ?>" 
                           class="pagination-link">1</a>
                        <?php if ($startPage > 2): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="<?php echo url('acopios?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '') . ($fecha_desde ? '&fecha_desde=' . $fecha_desde : '') . ($fecha_hasta ? '&fecha_hasta=' . $fecha_hasta : '')); ?>" 
                           class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                        <a href="<?php echo url('acopios?page=' . $totalPages . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '') . ($fecha_desde ? '&fecha_desde=' . $fecha_desde : '') . ($fecha_hasta ? '&fecha_hasta=' . $fecha_hasta : '')); ?>" 
                           class="pagination-link"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo url('acopios?page=' . ($page + 1) . ($search ? '&search=' . urlencode($search) : '') . ($estado ? '&estado=' . $estado : '') . ($fecha_desde ? '&fecha_desde=' . $fecha_desde : '') . ($fecha_hasta ? '&fecha_hasta=' . $fecha_hasta : '')); ?>" 
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

<div id="acopio_modalAnular" class="modal-acopios" style="display: none;">
    <div class="modal-acopios-content" style="max-width: 500px;">
        <div class="modal-acopios-header">
            <h3><i class="fas fa-ban"></i> Anular Acopio</h3>
            <button type="button" class="modal-acopios-close" onclick="AcopioModule.cerrarModalAnular()">&times;</button>
        </div>
        <div class="modal-acopios-body">
            <p>¿Está seguro que desea anular el acopio <strong id="acopio_codigoAnular"></strong>?</p>
            <p class="text-danger"><strong>Esta acción eliminará el registro de cuenta corriente.</strong></p>
            
            <div class="form-group">
                <label for="acopio_motivoAnulacion">
                    <i class="fas fa-comment"></i>
                    Motivo de anulación
                    <span class="text-danger">*</span>
                </label>
                <textarea 
                    id="acopio_motivoAnulacion" 
                    class="form-control" 
                    rows="3" 
                    placeholder="Escriba el motivo de la anulación..."
                    required></textarea>
            </div>
        </div>
        <div class="modal-acopios-footer">
            <button type="button" class="btn btn-secondary" onclick="AcopioModule.cerrarModalAnular()">
                <i class="fas fa-times"></i>
                Cancelar
            </button>
            <button type="button" class="btn btn-danger" onclick="AcopioModule.confirmarAnulacion()">
                <i class="fas fa-ban"></i>
                Anular Acopio
            </button>
        </div>
    </div>
</div>