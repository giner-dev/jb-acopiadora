<div class="page-header">
    <div>
        <h1><i class="fas fa-chart-line"></i> Rentabilidad por Cliente</h1>
        <p>Ventas vs Acopios por cliente en el período</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo url('reportes'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- FILTRO DE FECHAS -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="<?php echo url('reportes/rentabilidad-por-cliente'); ?>" class="rpt-filtros-form">
            <div class="rpt-filtros-row">
                <div class="rpt-filtro-grupo">
                    <label>Desde</label>
                    <input type="date" name="fecha_desde" class="form-control" value="<?php echo e($fecha_desde); ?>">
                </div>
                <div class="rpt-filtro-grupo">
                    <label>Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control" value="<?php echo e($fecha_hasta); ?>">
                </div>
                <div class="rpt-filtro-grupo rpt-filtro-acciones">
                    <label>&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
                        <button type="button" class="btn btn-secondary rpt-btn-periodo" data-periodo="mes">Este Mes</button>
                        <button type="button" class="btn btn-secondary rpt-btn-periodo" data-periodo="trimestre">Trimestre</button>
                        <button type="button" class="btn btn-secondary rpt-btn-periodo" data-periodo="anio">Año</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- STATS -->
<div class="rpt-stats-grid">
    <div class="rpt-stat-card rpt-stat-monto-debe">
        <div class="rpt-stat-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Total Ventas</span>
            <span class="rpt-stat-value">Bs <?php echo number_format($totales['total_ventas'] ?? 0, 2); ?></span>
        </div>
    </div>
    <div class="rpt-stat-card rpt-stat-monto-haber">
        <div class="rpt-stat-icon"><i class="fas fa-wheat-awn"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Total Acopios</span>
            <span class="rpt-stat-value">Bs <?php echo number_format($totales['total_acopios'] ?? 0, 2); ?></span>
        </div>
    </div>
    <div class="rpt-stat-card <?php echo floatval($totales['rentabilidad_total'] ?? 0) >= 0 ? 'rpt-stat-monto-debe' : 'rpt-stat-monto-haber'; ?>">
        <div class="rpt-stat-icon"><i class="fas fa-chart-line"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Rentabilidad Total</span>
            <span class="rpt-stat-value">Bs <?php echo number_format($totales['rentabilidad_total'] ?? 0, 2); ?></span>
        </div>
    </div>
    <div class="rpt-stat-card">
        <div class="rpt-stat-icon rpt-icon-total"><i class="fas fa-users"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Clientes Activos</span>
            <span class="rpt-stat-value"><?php echo $total; ?></span>
        </div>
    </div>
</div>

<!-- TABLA -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Rentabilidad por Cliente</h2>
    </div>
    <div class="card-body">
        <?php if (empty($clientes)): ?>
            <div class="empty-state">
                <i class="fas fa-chart-line"></i>
                <p>No hay datos de rentabilidad en este período</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Comunidad</th>
                            <th>Total Ventas</th>
                            <th>Total Acopios</th>
                            <th>Rentabilidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $i => $cliente): ?>
                        <?php $rentabilidad = floatval($cliente['rentabilidad']); ?>
                        <tr>
                            <td><?php echo (($page - 1) * $perPage) + $i + 1; ?></td>
                            <td>
                                <strong><?php echo e($cliente['nombres'] . ' ' . $cliente['apellidos']); ?></strong>
                                <br><small class="text-muted">CI: <?php echo e($cliente['ci']); ?></small>
                            </td>
                            <td><?php echo e($cliente['comunidad'] ?? '-'); ?></td>
                            <td class="rpt-valor-debe">Bs <?php echo number_format($cliente['total_ventas'], 2); ?></td>
                            <td class="rpt-valor-haber">Bs <?php echo number_format($cliente['total_acopios'], 2); ?></td>
                            <td class="<?php echo $rentabilidad >= 0 ? 'rpt-valor-debe' : 'rpt-valor-haber'; ?>">
                                <strong>Bs <?php echo number_format($rentabilidad, 2); ?></strong>
                                <?php if ($rentabilidad >= 0): ?>
                                    <small class="d-block">Ganancia</small>
                                <?php else: ?>
                                    <small class="d-block">Pérdida</small>
                                <?php endif; ?>
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
                <?php $qBase = 'fecha_desde=' . urlencode($fecha_desde) . '&fecha_hasta=' . urlencode($fecha_hasta); ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo url('reportes/rentabilidad-por-cliente?' . $qBase . '&page=' . ($page - 1)); ?>" class="pagination-link"><i class="fas fa-chevron-left"></i> Anterior</a>
                    <?php endif; ?>
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage   = min($totalPages, $page + 2);
                    if ($startPage > 1): ?>
                        <a href="<?php echo url('reportes/rentabilidad-por-cliente?' . $qBase . '&page=1'); ?>" class="pagination-link">1</a>
                        <?php if ($startPage > 2): ?><span class="pagination-dots">...</span><?php endif; ?>
                    <?php endif; ?>
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="<?php echo url('reportes/rentabilidad-por-cliente?' . $qBase . '&page=' . $i); ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?><span class="pagination-dots">...</span><?php endif; ?>
                        <a href="<?php echo url('reportes/rentabilidad-por-cliente?' . $qBase . '&page=' . $totalPages); ?>" class="pagination-link"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo url('reportes/rentabilidad-por-cliente?' . $qBase . '&page=' . ($page + 1)); ?>" class="pagination-link">Siguiente <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>