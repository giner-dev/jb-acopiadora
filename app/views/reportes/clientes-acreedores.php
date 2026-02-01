<div class="page-header">
    <div>
        <h1><i class="fas fa-arrow-up"></i> Clientes Acreedores</h1>
        <p>Clientes que JB les debe dinero</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo url('reportes'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="rpt-stats-grid">
    <div class="rpt-stat-card">
        <div class="rpt-stat-icon rpt-icon-total"><i class="fas fa-users"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Clientes Acreedores</span>
            <span class="rpt-stat-value"><?php echo $totales['cantidad_clientes'] ?? 0; ?></span>
        </div>
    </div>
    <div class="rpt-stat-card rpt-stat-monto-haber">
        <div class="rpt-stat-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Monto Total que JB Debe</span>
            <span class="rpt-stat-value">Bs <?php echo number_format($totales['monto_total'] ?? 0, 2); ?></span>
        </div>
    </div>
    <div class="rpt-stat-card">
        <div class="rpt-stat-icon rpt-icon-promedio"><i class="fas fa-calculator"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Promedio por Cliente</span>
            <span class="rpt-stat-value">Bs <?php 
                echo number_format(($totales['cantidad_clientes'] ?? 0) > 0 
                    ? ($totales['monto_total'] ?? 0) / $totales['cantidad_clientes'] 
                    : 0, 2); 
            ?></span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Lista de Clientes</h2>
    </div>
    <div class="card-body">
        <?php if (empty($clientes)): ?>
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <p>No hay clientes acreedores</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>CI</th>
                            <th>Cliente</th>
                            <th>Comunidad</th>
                            <th>Facturas</th>
                            <th>Acopios</th>
                            <th>Último Mov.</th>
                            <th>JB Debe</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $i => $cliente): ?>
                        <tr>
                            <td><?php echo (($page - 1) * $perPage) + $i + 1; ?></td>
                            <td><?php echo e($cliente['ci']); ?></td>
                            <td><strong><?php echo e($cliente['nombres'] . ' ' . $cliente['apellidos']); ?></strong></td>
                            <td><?php echo e($cliente['comunidad'] ?? '-'); ?></td>
                            <td class="text-center"><?php echo $cliente['total_facturas']; ?></td>
                            <td class="text-center"><?php echo $cliente['total_acopios']; ?></td>
                            <td><?php echo $cliente['ultimo_movimiento'] ? date('d/m/Y', strtotime($cliente['ultimo_movimiento'])) : '-'; ?></td>
                            <td class="rpt-valor-haber"><strong>Bs <?php echo number_format($cliente['saldo'], 2); ?></strong></td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo url('cuenta-corriente/ver-cliente/' . $cliente['id_cliente']); ?>" class="btn btn-sm btn-info" title="Ver cuenta corriente">
                                        <i class="fas fa-balance-scale"></i>
                                    </a>
                                    <a href="<?php echo url('pagos/crear?cliente_id=' . $cliente['id_cliente']); ?>" class="btn btn-sm btn-success" title="Registrar pago">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </a>
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
                    Mostrando <?php echo (($page - 1) * $perPage) + 1; ?> - <?php echo min($page * $perPage, $total); ?> de <?php echo $total; ?> clientes
                </div>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo url('reportes/clientes-acreedores?page=' . ($page - 1)); ?>" class="pagination-link"><i class="fas fa-chevron-left"></i> Anterior</a>
                    <?php endif; ?>
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage   = min($totalPages, $page + 2);
                    if ($startPage > 1): ?>
                        <a href="<?php echo url('reportes/clientes-acreedores?page=1'); ?>" class="pagination-link">1</a>
                        <?php if ($startPage > 2): ?><span class="pagination-dots">...</span><?php endif; ?>
                    <?php endif; ?>
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="<?php echo url('reportes/clientes-acreedores?page=' . $i); ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?><span class="pagination-dots">...</span><?php endif; ?>
                        <a href="<?php echo url('reportes/clientes-acreedores?page=' . $totalPages); ?>" class="pagination-link"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo url('reportes/clientes-acreedores?page=' . ($page + 1)); ?>" class="pagination-link">Siguiente <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>