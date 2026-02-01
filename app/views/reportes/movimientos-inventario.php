<div class="page-header">
    <div>
        <h1><i class="fas fa-arrows-alt"></i> Movimientos de Inventario</h1>
        <p>Historial de entradas y salidas de stock</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo url('reportes'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- STATS -->
<div class="rpt-stats-grid">
    <div class="rpt-stat-card">
        <div class="rpt-stat-icon rpt-icon-total"><i class="fas fa-arrows-alt"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Total Movimientos</span>
            <span class="rpt-stat-value"><?php echo $totales['total_movimientos'] ?? 0; ?></span>
        </div>
    </div>
    <div class="rpt-stat-card rpt-stat-monto-haber">
        <div class="rpt-stat-icon"><i class="fas fa-arrow-down"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Total Entradas</span>
            <span class="rpt-stat-value"><?php echo number_format($totales['total_entradas'] ?? 0, 2); ?></span>
        </div>
    </div>
    <div class="rpt-stat-card rpt-stat-monto-debe">
        <div class="rpt-stat-icon"><i class="fas fa-arrow-up"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Total Salidas</span>
            <span class="rpt-stat-value"><?php echo number_format($totales['total_salidas'] ?? 0, 2); ?></span>
        </div>
    </div>
    <div class="rpt-stat-card">
        <div class="rpt-stat-icon rpt-icon-balance"><i class="fas fa-balance-scale"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Balance Neto</span>
            <?php $balanceNeto = floatval($totales['total_entradas'] ?? 0) - floatval($totales['total_salidas'] ?? 0); ?>
            <span class="rpt-stat-value <?php echo $balanceNeto >= 0 ? 'rpt-valor-haber' : 'rpt-valor-debe'; ?>">
                <?php echo number_format($balanceNeto, 2); ?>
            </span>
        </div>
    </div>
</div>

<!-- FILTROS -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="<?php echo url('reportes/movimientos-inventario'); ?>" class="rpt-filtros-form">
            <div class="rpt-filtros-row">
                <div class="rpt-filtro-grupo">
                    <input type="text" name="search" class="form-control" placeholder="Buscar producto..." value="<?php echo e($search); ?>">
                </div>
                <div class="rpt-filtro-grupo">
                    <select name="tipo" class="form-control">
                        <option value="">Todos los tipos</option>
                        <option value="ENTRADA_COMPRA"        <?php echo $tipo === 'ENTRADA_COMPRA'        ? 'selected' : ''; ?>>Entrada por Compra</option>
                        <option value="SALIDA_VENTA"           <?php echo $tipo === 'SALIDA_VENTA'           ? 'selected' : ''; ?>>Salida por Venta</option>
                        <option value="DEVOLUCION_CLIENTE"    <?php echo $tipo === 'DEVOLUCION_CLIENTE'    ? 'selected' : ''; ?>>Devolución Cliente</option>
                        <option value="DEVOLUCION_PROVEEDOR"  <?php echo $tipo === 'DEVOLUCION_PROVEEDOR'  ? 'selected' : ''; ?>>Devolución Proveedor</option>
                        <option value="AJUSTE_INVENTARIO"     <?php echo $tipo === 'AJUSTE_INVENTARIO'     ? 'selected' : ''; ?>>Ajuste Inventario</option>
                        <option value="MERMA_DESPERDICIO"     <?php echo $tipo === 'MERMA_DESPERDICIO'     ? 'selected' : ''; ?>>Merma</option>
                        <option value="CONSUMO_INTERNO"       <?php echo $tipo === 'CONSUMO_INTERNO'       ? 'selected' : ''; ?>>Consumo Interno</option>
                    </select>
                </div>
                <div class="rpt-filtro-grupo">
                    <input type="date" name="fecha_desde" class="form-control" value="<?php echo e($fecha_desde); ?>">
                </div>
                <div class="rpt-filtro-grupo">
                    <input type="date" name="fecha_hasta" class="form-control" value="<?php echo e($fecha_hasta); ?>">
                </div>
                <div class="rpt-filtro-grupo rpt-filtro-acciones">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
                    <a href="<?php echo url('reportes/movimientos-inventario'); ?>" class="btn btn-secondary"><i class="fas fa-times"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- TABLA -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Historial de Movimientos</h2>
    </div>
    <div class="card-body">
        <?php if (empty($movimientos)): ?>
            <div class="empty-state">
                <i class="fas fa-arrows-alt"></i>
                <p>No se encontraron movimientos en este período</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Saldo Anterior</th>
                            <th>Saldo Actual</th>
                            <th>Referencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $tiposEntrada = ['ENTRADA_COMPRA', 'DEVOLUCION_CLIENTE'];
                        $tiposTexto = [
                            'ENTRADA_COMPRA'       => 'Entrada Compra',
                            'SALIDA_VENTA'         => 'Salida Venta',
                            'DEVOLUCION_CLIENTE'   => 'Dev. Cliente',
                            'DEVOLUCION_PROVEEDOR' => 'Dev. Proveedor',
                            'AJUSTE_INVENTARIO'    => 'Ajuste',
                            'MERMA_DESPERDICIO'    => 'Merma',
                            'TRASPASO'             => 'Traspaso',
                            'CONSUMO_INTERNO'      => 'Consumo Interno'
                        ];
                        $tiposBadge = [
                            'ENTRADA_COMPRA'       => 'badge-success',
                            'SALIDA_VENTA'         => 'badge-danger',
                            'DEVOLUCION_CLIENTE'   => 'badge-success',
                            'DEVOLUCION_PROVEEDOR' => 'badge-danger',
                            'AJUSTE_INVENTARIO'    => 'badge-warning',
                            'MERMA_DESPERDICIO'    => 'badge-danger',
                            'TRASPASO'             => 'badge-info',
                            'CONSUMO_INTERNO'      => 'badge-warning'
                        ];
                        ?>
                        <?php foreach ($movimientos as $mov): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($mov['fecha'])); ?></td>
                            <td>
                                <strong><?php echo e($mov['producto_nombre']); ?></strong>
                                <?php if (!empty($mov['producto_codigo'])): ?>
                                    <br><small class="text-muted"><?php echo e($mov['producto_codigo']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo $tiposBadge[$mov['tipo']] ?? 'badge-secondary'; ?>">
                                    <?php echo $tiposTexto[$mov['tipo']] ?? $mov['tipo']; ?>
                                </span>
                            </td>
                            <td class="<?php echo in_array($mov['tipo'], $tiposEntrada) ? 'rpt-valor-haber' : 'rpt-valor-debe'; ?>">
                                <strong><?php echo in_array($mov['tipo'], $tiposEntrada) ? '+' : '-'; ?><?php echo number_format($mov['cantidad'], 2); ?></strong>
                                <?php if (!empty($mov['unidad'])): ?>
                                    <small class="text-muted"><?php echo $mov['unidad']; ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo number_format($mov['saldo_anterior'], 2); ?></td>
                            <td><strong><?php echo number_format($mov['saldo_actual'], 2); ?></strong></td>
                            <td>
                                <?php if (!empty($mov['referencia_tipo']) && !empty($mov['referencia_id'])): ?>
                                    <small class="text-muted"><?php echo ucfirst($mov['referencia_tipo']); ?> #<?php echo $mov['referencia_id']; ?></small>
                                <?php else: ?>
                                    -
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
                    Mostrando <?php echo (($page - 1) * $perPage) + 1; ?> - <?php echo min($page * $perPage, $total); ?> de <?php echo $total; ?> movimientos
                </div>
                <?php $qp = array_filter(compact('search', 'tipo', 'fecha_desde', 'fecha_hasta')); $qStr = !empty($qp) ? '?' . http_build_query($qp) . '&page=' : '?page='; ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo url('reportes/movimientos-inventario' . $qStr . ($page - 1)); ?>" class="pagination-link"><i class="fas fa-chevron-left"></i> Anterior</a>
                    <?php endif; ?>
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage   = min($totalPages, $page + 2);
                    if ($startPage > 1): ?>
                        <a href="<?php echo url('reportes/movimientos-inventario' . $qStr . 1); ?>" class="pagination-link">1</a>
                        <?php if ($startPage > 2): ?><span class="pagination-dots">...</span><?php endif; ?>
                    <?php endif; ?>
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="<?php echo url('reportes/movimientos-inventario' . $qStr . $i); ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?><span class="pagination-dots">...</span><?php endif; ?>
                        <a href="<?php echo url('reportes/movimientos-inventario' . $qStr . $totalPages); ?>" class="pagination-link"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo url('reportes/movimientos-inventario' . $qStr . ($page + 1)); ?>" class="pagination-link">Siguiente <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>