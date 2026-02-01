<div class="page-header">
    <div>
        <h1><i class="fas fa-wheat-awn"></i> Acopios por Período</h1>
        <p>Análisis de cosechas recibidas</p>
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
        <form method="GET" action="<?php echo url('reportes/acopios-por-periodo'); ?>" class="rpt-filtros-form">
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
    <div class="rpt-stat-card">
        <div class="rpt-stat-icon rpt-icon-total"><i class="fas fa-file-alt"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Total Acopios</span>
            <span class="rpt-stat-value"><?php echo $totales['total_acopios'] ?? 0; ?></span>
        </div>
    </div>
    <div class="rpt-stat-card rpt-stat-monto-haber">
        <div class="rpt-stat-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Monto Total</span>
            <span class="rpt-stat-value">Bs <?php echo number_format($totales['monto_total'] ?? 0, 2); ?></span>
        </div>
    </div>
    <div class="rpt-stat-card">
        <div class="rpt-stat-icon rpt-icon-promedio"><i class="fas fa-calculator"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Promedio por Acopio</span>
            <span class="rpt-stat-value">Bs <?php echo number_format($totales['promedio_acopio'] ?? 0, 2); ?></span>
        </div>
    </div>
    <div class="rpt-stat-card">
        <div class="rpt-stat-icon rpt-icon-max"><i class="fas fa-arrow-up"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Acopio Máximo</span>
            <span class="rpt-stat-value">Bs <?php echo number_format($totales['acopio_maximo'] ?? 0, 2); ?></span>
        </div>
    </div>
</div>

<!-- GRÁFICO POR MES -->
<?php if (!empty($acopiosMes)): ?>
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-chart-bar"></i> Acopios por Mes</h2>
    </div>
    <div class="card-body">
        <div class="rpt-grafico-container">
            <?php
            $maxMonto = 0;
            foreach ($acopiosMes as $mes) {
                if (floatval($mes['monto_total']) > $maxMonto) $maxMonto = floatval($mes['monto_total']);
            }
            ?>
            <div class="rpt-grafico-barras">
                <?php foreach ($acopiosMes as $mes): ?>
                <?php
                $porcentaje = $maxMonto > 0 ? (floatval($mes['monto_total']) / $maxMonto) * 100 : 0;
                $fechaParts = explode('-', $mes['mes']);
                $mesNombre = date('M', mktime(0, 0, 0, (int)$fechaParts[1], 1)) . ' ' . $fechaParts[0];
                ?>
                <div class="rpt-barra-grupo">
                    <div class="rpt-barra-valor">Bs <?php echo number_format($mes['monto_total'], 0); ?></div>
                    <div class="rpt-barra rpt-barra-haber" style="height: <?php echo max($porcentaje, 5); ?>%"></div>
                    <div class="rpt-barra-etiqueta"><?php echo $mesNombre; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- TABLA -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Acopios del Período</h2>
    </div>
    <div class="card-body">
        <?php if (empty($acopios)): ?>
            <div class="empty-state">
                <i class="fas fa-wheat-awn"></i>
                <p>No hay acopios en este período</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Código</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Granos</th>
                            <th>Estado</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($acopios as $i => $acopio): ?>
                        <tr>
                            <td><?php echo (($page - 1) * $perPage) + $i + 1; ?></td>
                            <td><strong><?php echo e($acopio['codigo']); ?></strong></td>
                            <td><?php echo date('d/m/Y', strtotime($acopio['fecha'])); ?></td>
                            <td>
                                <?php echo e($acopio['cliente_nombres'] . ' ' . $acopio['cliente_apellidos']); ?>
                                <br><small class="text-muted">CI: <?php echo e($acopio['cliente_ci']); ?></small>
                            </td>
                            <td class="text-center"><?php echo $acopio['cantidad_granos']; ?></td>
                            <td>
                                <span class="badge <?php echo $acopio['estado'] === 'activo' ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo ucfirst($acopio['estado']); ?>
                                </span>
                            </td>
                            <td class="rpt-valor-haber"><strong>Bs <?php echo number_format($acopio['total'], 2); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Mostrando <?php echo (($page - 1) * $perPage) + 1; ?> - <?php echo min($page * $perPage, $total); ?> de <?php echo $total; ?> acopios
                </div>
                <?php $qBase = 'fecha_desde=' . urlencode($fecha_desde) . '&fecha_hasta=' . urlencode($fecha_hasta); ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo url('reportes/acopios-por-periodo?' . $qBase . '&page=' . ($page - 1)); ?>" class="pagination-link"><i class="fas fa-chevron-left"></i> Anterior</a>
                    <?php endif; ?>
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage   = min($totalPages, $page + 2);
                    if ($startPage > 1): ?>
                        <a href="<?php echo url('reportes/acopios-por-periodo?' . $qBase . '&page=1'); ?>" class="pagination-link">1</a>
                        <?php if ($startPage > 2): ?><span class="pagination-dots">...</span><?php endif; ?>
                    <?php endif; ?>
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="<?php echo url('reportes/acopios-por-periodo?' . $qBase . '&page=' . $i); ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?><span class="pagination-dots">...</span><?php endif; ?>
                        <a href="<?php echo url('reportes/acopios-por-periodo?' . $qBase . '&page=' . $totalPages); ?>" class="pagination-link"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo url('reportes/acopios-por-periodo?' . $qBase . '&page=' . ($page + 1)); ?>" class="pagination-link">Siguiente <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>