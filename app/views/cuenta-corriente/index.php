<div class="page-header">
    <div>
        <h1><i class="fas fa-balance-scale"></i> Cuenta Corriente</h1>
        <p>Movimientos económicos entre JB y clientes</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo url('cuenta-corriente/clientes'); ?>" class="btn btn-info">
            <i class="fas fa-users"></i>
            Clientes
        </a>
        <a href="<?php echo url('cuenta-corriente/excel?' . http_build_query(compact('search', 'cliente_id', 'tipo_movimiento', 'fecha_desde', 'fecha_hasta'))); ?>" 
           class="btn btn-success">
            <i class="fas fa-file-excel"></i>
            Excel
        </a>
    </div>
</div>

<!-- RESUMEN SUPERIOR -->
<div class="cc-stats-grid">
    <div class="cc-stat-card">
        <div class="cc-stat-icon cc-icon-total">
            <i class="fas fa-exchange-alt"></i>
        </div>
        <div class="cc-stat-info">
            <span class="cc-stat-label">Total Movimientos</span>
            <span class="cc-stat-value"><?php echo $totalMovimientos; ?></span>
        </div>
    </div>
    
    <div class="cc-stat-card cc-stat-debe">
        <div class="cc-stat-icon">
            <i class="fas fa-arrow-down"></i>
        </div>
        <div class="cc-stat-info">
            <span class="cc-stat-label">Total Debe</span>
            <span class="cc-stat-value">Bs <?php echo number_format($totales['total_debe'] ?? 0, 2); ?></span>
        </div>
    </div>
    
    <div class="cc-stat-card cc-stat-haber">
        <div class="cc-stat-icon">
            <i class="fas fa-arrow-up"></i>
        </div>
        <div class="cc-stat-info">
            <span class="cc-stat-label">Total Haber</span>
            <span class="cc-stat-value">Bs <?php echo number_format($totales['total_haber'] ?? 0, 2); ?></span>
        </div>
    </div>
    
    <div class="cc-stat-card cc-stat-saldo">
        <div class="cc-stat-icon">
            <i class="fas fa-balance-scale"></i>
        </div>
        <div class="cc-stat-info">
            <span class="cc-stat-label">Saldo General</span>
            <span class="cc-stat-value <?php 
                $saldoGen = floatval($totales['saldo_total'] ?? 0);
                echo $saldoGen > 0 ? 'cc-valor-debe' : ($saldoGen < 0 ? 'cc-valor-haber' : '');
            ?>">
                Bs <?php echo number_format(abs($totales['saldo_total'] ?? 0), 2); ?>
                <?php if ($saldoGen > 0): ?>(Clientes deben)<?php elseif ($saldoGen < 0): ?>(JB debe)<?php endif; ?>
            </span>
        </div>
    </div>
</div>

<!-- FILTROS -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="<?php echo url('cuenta-corriente'); ?>" class="cc-filtros-form">
            <div class="cc-filtros-row">
                <div class="cc-filtro-grupo">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Buscar cliente..."
                        value="<?php echo e($search); ?>">
                </div>

                <div class="cc-filtro-grupo">
                    <select name="tipo_movimiento" class="form-control" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        <option value="FACTURA" <?php echo $tipo_movimiento === 'FACTURA' ? 'selected' : ''; ?>>Facturas</option>
                        <option value="ACOPIO" <?php echo $tipo_movimiento === 'ACOPIO' ? 'selected' : ''; ?>>Acopios</option>
                        <option value="PAGO_CLIENTE" <?php echo $tipo_movimiento === 'PAGO_CLIENTE' ? 'selected' : ''; ?>>Pagos de Cliente</option>
                        <option value="PAGO_JB" <?php echo $tipo_movimiento === 'PAGO_JB' ? 'selected' : ''; ?>>Pagos de JB</option>
                        <option value="AJUSTE" <?php echo $tipo_movimiento === 'AJUSTE' ? 'selected' : ''; ?>>Ajustes</option>
                    </select>
                </div>
                
                <div class="cc-filtro-grupo">
                    <input 
                        type="date" 
                        name="fecha_desde" 
                        class="form-control" 
                        value="<?php echo e($fecha_desde); ?>">
                </div>
                
                <div class="cc-filtro-grupo">
                    <input 
                        type="date" 
                        name="fecha_hasta" 
                        class="form-control" 
                        value="<?php echo e($fecha_hasta); ?>">
                </div>
                
                <div class="cc-filtro-grupo cc-filtro-acciones">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Buscar
                    </button>
                    <a href="<?php echo url('cuenta-corriente'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- TABLA DE MOVIMIENTOS -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Movimientos</h2>
    </div>
    <div class="card-body">
        <?php if (empty($movimientos)): ?>
            <div class="empty-state">
                <i class="fas fa-balance-scale"></i>
                <p>No se encontraron movimientos</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Debe</th>
                            <th>Haber</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimientos as $mov): ?>
                        <tr>
                            <td><?php echo formatDate($mov->fecha); ?></td>
                            <td>
                                <?php echo e($mov->getClienteNombreCompleto()); ?>
                                <br>
                                <small class="text-muted">CI: <?php echo e($mov->cliente_ci); ?></small>
                            </td>
                            <td>
                                <span class="badge <?php echo $mov->getTipoBadgeClass(); ?>">
                                    <?php echo $mov->getTipoTexto(); ?>
                                </span>
                            </td>
                            <td><small><?php echo e($mov->descripcion); ?></small></td>
                            <td class="cc-valor-debe">
                                <?php echo floatval($mov->debe) > 0 ? 'Bs ' . number_format($mov->debe, 2) : '-'; ?>
                            </td>
                            <td class="cc-valor-haber">
                                <?php echo floatval($mov->haber) > 0 ? 'Bs ' . number_format($mov->haber, 2) : '-'; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo url('cuenta-corriente/ver-cliente/' . $mov->cliente_id); ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="Ver estado de cuenta">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo url('cuenta-corriente/pdf/' . $mov->cliente_id); ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="PDF Estado de Cuenta"
                                       target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- PAGINACIÓN -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Mostrando <?php echo (($page - 1) * $perPage) + 1; ?> - 
                    <?php echo min($page * $perPage, $totalMovimientos); ?> 
                    de <?php echo $totalMovimientos; ?> movimientos
                </div>
                
                <?php
                $queryParams = http_build_query(array_filter(compact('search', 'cliente_id', 'tipo_movimiento', 'fecha_desde', 'fecha_hasta')));
                $separator = !empty($queryParams) ? '&' : '?';
                if (!empty($queryParams)) $queryParams = '?' . $queryParams . '&page=';
                else $queryParams = '?page=';
                ?>
                
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo url('cuenta-corriente' . $queryParams . ($page - 1)); ?>" class="pagination-link">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1): ?>
                        <a href="<?php echo url('cuenta-corriente' . $queryParams . 1); ?>" class="pagination-link">1</a>
                        <?php if ($startPage > 2): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="<?php echo url('cuenta-corriente' . $queryParams . $i); ?>" 
                           class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                        <a href="<?php echo url('cuenta-corriente' . $queryParams . $totalPages); ?>" class="pagination-link">
                            <?php echo $totalPages; ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo url('cuenta-corriente' . $queryParams . ($page + 1)); ?>" class="pagination-link">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>