<div class="page-header">
    <div>
        <h1><i class="fas fa-boxes"></i> Productos Más Vendidos</h1>
        <p>Ranking de productos por cantidad vendida</p>
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
        <form method="GET" action="<?php echo url('reportes/productos-mas-vendidos'); ?>" class="rpt-filtros-form">
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

<!-- TABLA -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-trophy"></i> Ranking de Productos</h2>
    </div>
    <div class="card-body">
        <?php if (empty($productos)): ?>
            <div class="empty-state">
                <i class="fas fa-boxes"></i>
                <p>No hay datos de ventas en este período</p>
            </div>
        <?php else: ?>
            <?php
            // Calcular máximo para barra de progreso
            $maxCantidad = 0;
            foreach ($productos as $p) {
                if (floatval($p['total_cantidad']) > $maxCantidad) $maxCantidad = floatval($p['total_cantidad']);
            }
            ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th>Unidad</th>
                            <th>Cantidad Vendida</th>
                            <th>Precio Promedio</th>
                            <th>Facturas</th>
                            <th>Total Ingresado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $i => $producto): ?>
                        <?php $numero = (($page - 1) * $perPage) + $i + 1; ?>
                        <tr>
                            <td>
                                <?php if ($numero <= 3): ?>
                                    <span class="rpt-badge-ranking rpt-ranking-<?php echo $numero; ?>"><?php echo $numero; ?></span>
                                <?php else: ?>
                                    <?php echo $numero; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo e($producto['producto_nombre']); ?></strong>
                                <?php if (!empty($producto['producto_codigo'])): ?>
                                    <br><small class="text-muted"><?php echo e($producto['producto_codigo']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($producto['unidad'] ?? '-'); ?></td>
                            <td>
                                <?php echo number_format($producto['total_cantidad'], 2); ?>
                                <div class="rpt-barra-progreso">
                                    <div class="rpt-barra-progreso-fill" style="width: <?php echo $maxCantidad > 0 ? ($producto['total_cantidad'] / $maxCantidad) * 100 : 0; ?>%"></div>
                                </div>
                            </td>
                            <td>Bs <?php echo number_format($producto['precio_promedio'], 2); ?></td>
                            <td class="text-center"><?php echo $producto['cantidad_facturas']; ?></td>
                            <td class="rpt-valor-debe"><strong>Bs <?php echo number_format($producto['total_monto'], 2); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Mostrando <?php echo (($page - 1) * $perPage) + 1; ?> - <?php echo min($page * $perPage, $total); ?> de <?php echo $total; ?> productos
                </div>
                <?php $qBase = 'fecha_desde=' . urlencode($fecha_desde) . '&fecha_hasta=' . urlencode($fecha_hasta); ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo url('reportes/productos-mas-vendidos?' . $qBase . '&page=' . ($page - 1)); ?>" class="pagination-link"><i class="fas fa-chevron-left"></i> Anterior</a>
                    <?php endif; ?>
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage   = min($totalPages, $page + 2);
                    if ($startPage > 1): ?>
                        <a href="<?php echo url('reportes/productos-mas-vendidos?' . $qBase . '&page=1'); ?>" class="pagination-link">1</a>
                        <?php if ($startPage > 2): ?><span class="pagination-dots">...</span><?php endif; ?>
                    <?php endif; ?>
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="<?php echo url('reportes/productos-mas-vendidos?' . $qBase . '&page=' . $i); ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?><span class="pagination-dots">...</span><?php endif; ?>
                        <a href="<?php echo url('reportes/productos-mas-vendidos?' . $qBase . '&page=' . $totalPages); ?>" class="pagination-link"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo url('reportes/productos-mas-vendidos?' . $qBase . '&page=' . ($page + 1)); ?>" class="pagination-link">Siguiente <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>