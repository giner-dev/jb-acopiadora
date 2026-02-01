<div class="page-header">
    <div>
        <h1><i class="fas fa-cubes"></i> Estado de Inventario</h1>
        <p>Stock actual de todos los productos activos</p>
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
        <div class="rpt-stat-icon rpt-icon-total"><i class="fas fa-cubes"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Total Productos</span>
            <span class="rpt-stat-value"><?php echo $totales['total_productos'] ?? 0; ?></span>
        </div>
    </div>
    <div class="rpt-stat-card rpt-stat-sinstock">
        <div class="rpt-stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Sin Stock</span>
            <span class="rpt-stat-value"><?php echo $totales['sin_stock'] ?? 0; ?></span>
        </div>
    </div>
    <div class="rpt-stat-card rpt-stat-bajo">
        <div class="rpt-stat-icon"><i class="fas fa-arrow-down"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Stock Bajo</span>
            <span class="rpt-stat-value"><?php echo $totales['stock_bajo'] ?? 0; ?></span>
        </div>
    </div>
    <div class="rpt-stat-card">
        <div class="rpt-stat-icon rpt-icon-valor"><i class="fas fa-dollar-sign"></i></div>
        <div class="rpt-stat-info">
            <span class="rpt-stat-label">Valor Total Inventario</span>
            <span class="rpt-stat-value">Bs <?php echo number_format($totales['valor_total'] ?? 0, 2); ?></span>
        </div>
    </div>
</div>

<!-- FILTROS -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="<?php echo url('reportes/estado-inventario'); ?>" class="rpt-filtros-form">
            <div class="rpt-filtros-row">
                <div class="rpt-filtro-grupo">
                    <input type="text" name="search" class="form-control" placeholder="Buscar producto..." value="<?php echo e($search); ?>">
                </div>
                <div class="rpt-filtro-grupo">
                    <select name="categoria_id" class="form-control">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id_categoria']; ?>" <?php echo $categoria_id == $cat['id_categoria'] ? 'selected' : ''; ?>>
                                <?php echo e($cat['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="rpt-filtro-grupo">
                    <select name="estado_stock" class="form-control">
                        <option value="">Todo el stock</option>
                        <option value="sin_stock" <?php echo $estado_stock === 'sin_stock' ? 'selected' : ''; ?>>Sin Stock</option>
                        <option value="bajo"      <?php echo $estado_stock === 'bajo'      ? 'selected' : ''; ?>>Stock Bajo</option>
                        <option value="normal"    <?php echo $estado_stock === 'normal'    ? 'selected' : ''; ?>>Stock Normal</option>
                        <option value="ilimitado" <?php echo $estado_stock === 'ilimitado' ? 'selected' : ''; ?>>Ilimitado</option>
                    </select>
                </div>
                <div class="rpt-filtro-grupo rpt-filtro-acciones">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
                    <a href="<?php echo url('reportes/estado-inventario'); ?>" class="btn btn-secondary"><i class="fas fa-times"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- TABLA -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Productos</h2>
    </div>
    <div class="card-body">
        <?php if (empty($productos)): ?>
            <div class="empty-state">
                <i class="fas fa-cubes"></i>
                <p>No se encontraron productos</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Unidad</th>
                            <th>Precio Venta</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Estado</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                        <tr class="<?php
                            switch ($producto['estado_stock']) {
                                case 'sin_stock': echo 'rpt-fila-sinstock'; break;
                                case 'bajo':      echo 'rpt-fila-bajo';    break;
                                default:          echo '';                  break;
                            }
                        ?>">
                            <td><?php echo e($producto['codigo'] ?? '-'); ?></td>
                            <td><strong><?php echo e($producto['nombre']); ?></strong></td>
                            <td><?php echo e($producto['categoria'] ?? '-'); ?></td>
                            <td><?php echo e($producto['unidad'] ?? '-'); ?></td>
                            <td>Bs <?php echo number_format($producto['precio_venta'], 2); ?></td>
                            <td class="<?php
                                switch ($producto['estado_stock']) {
                                    case 'sin_stock': echo 'rpt-valor-sinstock'; break;
                                    case 'bajo':      echo 'rpt-valor-bajo';    break;
                                    default:          echo '';                  break;
                                }
                            ?>">
                                <?php echo $producto['stock_ilimitado'] ? '∞' : number_format($producto['stock_actual'], 2); ?>
                            </td>
                            <td><?php echo $producto['stock_ilimitado'] ? '-' : number_format($producto['stock_minimo'], 2); ?></td>
                            <td>
                                <?php
                                $etiquetas = [
                                    'sin_stock' => ['badge-danger',  'Sin Stock'],
                                    'bajo'      => ['badge-warning', 'Stock Bajo'],
                                    'normal'    => ['badge-success', 'Normal'],
                                    'ilimitado' => ['badge-info',    'Ilimitado']
                                ];
                                $info = $etiquetas[$producto['estado_stock']] ?? ['badge-secondary', 'Desconocido'];
                                ?>
                                <span class="badge <?php echo $info[0]; ?>"><?php echo $info[1]; ?></span>
                            </td>
                            <td>
                                <?php echo $producto['stock_ilimitado'] ? '-' : 'Bs ' . number_format($producto['valor_inventario'], 2); ?>
                            </td>
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
                <?php $qp = array_filter(compact('search', 'categoria_id', 'estado_stock')); $qStr = !empty($qp) ? '?' . http_build_query($qp) . '&page=' : '?page='; ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo url('reportes/estado-inventario' . $qStr . ($page - 1)); ?>" class="pagination-link"><i class="fas fa-chevron-left"></i> Anterior</a>
                    <?php endif; ?>
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage   = min($totalPages, $page + 2);
                    if ($startPage > 1): ?>
                        <a href="<?php echo url('reportes/estado-inventario' . $qStr . 1); ?>" class="pagination-link">1</a>
                        <?php if ($startPage > 2): ?><span class="pagination-dots">...</span><?php endif; ?>
                    <?php endif; ?>
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="<?php echo url('reportes/estado-inventario' . $qStr . $i); ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?><span class="pagination-dots">...</span><?php endif; ?>
                        <a href="<?php echo url('reportes/estado-inventario' . $qStr . $totalPages); ?>" class="pagination-link"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo url('reportes/estado-inventario' . $qStr . ($page + 1)); ?>" class="pagination-link">Siguiente <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>