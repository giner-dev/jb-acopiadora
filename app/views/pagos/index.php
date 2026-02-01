<div class="page-header">
    <div>
        <h1><i class="fas fa-money-bill-wave"></i> Pagos</h1>
        <p>Registro de pagos y compensaciones</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo url('pagos/crear'); ?>" class="btn btn-success">
            <i class="fas fa-plus"></i>
            Nuevo Pago
        </a>
    </div>
</div>

<!-- STATS -->
<div class="pago-stats-grid">
    <div class="pago-stat-card">
        <div class="pago-stat-icon pago-icon-total">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="pago-stat-info">
            <span class="pago-stat-label">Total Pagos</span>
            <span class="pago-stat-value"><?php echo $totales['total_pagos'] ?? 0; ?></span>
        </div>
    </div>

    <div class="pago-stat-card pago-stat-cobrados">
        <div class="pago-stat-icon">
            <i class="fas fa-arrow-down"></i>
        </div>
        <div class="pago-stat-info">
            <span class="pago-stat-label">Cobrados de Clientes</span>
            <span class="pago-stat-value">Bs <?php echo number_format($totales['total_cobrados'] ?? 0, 2); ?></span>
        </div>
    </div>

    <div class="pago-stat-card pago-stat-pagados">
        <div class="pago-stat-icon">
            <i class="fas fa-arrow-up"></i>
        </div>
        <div class="pago-stat-info">
            <span class="pago-stat-label">Pagados a Clientes</span>
            <span class="pago-stat-value">Bs <?php echo number_format($totales['total_pagados'] ?? 0, 2); ?></span>
        </div>
    </div>

    <div class="pago-stat-card pago-stat-balance">
        <div class="pago-stat-icon">
            <i class="fas fa-balance-scale"></i>
        </div>
        <div class="pago-stat-info">
            <span class="pago-stat-label">Balance Neto</span>
            <?php $balance = floatval($totales['total_cobrados'] ?? 0) - floatval($totales['total_pagados'] ?? 0); ?>
            <span class="pago-stat-value <?php echo $balance >= 0 ? 'pago-valor-positivo' : 'pago-valor-negativo'; ?>">
                Bs <?php echo number_format(abs($balance), 2); ?>
            </span>
        </div>
    </div>
</div>

<!-- FILTROS -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="<?php echo url('pagos'); ?>" class="pago-filtros-form">
            <div class="pago-filtros-row">
                <div class="pago-filtro-grupo">
                    <input type="text" name="search" class="form-control" placeholder="Buscar cliente o código..."
                           value="<?php echo e($search); ?>">
                </div>

                <div class="pago-filtro-grupo">
                    <select name="tipo" class="form-control">
                        <option value="">Todos los tipos</option>
                        <option value="PAGO_CLIENTE" <?php echo $tipo === 'PAGO_CLIENTE' ? 'selected' : ''; ?>>Cliente paga a JB</option>
                        <option value="PAGO_JB"      <?php echo $tipo === 'PAGO_JB'      ? 'selected' : ''; ?>>JB paga a Cliente</option>
                    </select>
                </div>

                <div class="pago-filtro-grupo">
                    <select name="metodo_pago" class="form-control">
                        <option value="">Todos los métodos</option>
                        <option value="EFECTIVO"      <?php echo $metodo_pago === 'EFECTIVO'      ? 'selected' : ''; ?>>Efectivo</option>
                        <option value="TRANSFERENCIA" <?php echo $metodo_pago === 'TRANSFERENCIA' ? 'selected' : ''; ?>>Transferencia</option>
                        <option value="CHEQUE"        <?php echo $metodo_pago === 'CHEQUE'        ? 'selected' : ''; ?>>Cheque</option>
                        <option value="DEPOSITO"      <?php echo $metodo_pago === 'DEPOSITO'      ? 'selected' : ''; ?>>Depósito</option>
                        <option value="OTRO"          <?php echo $metodo_pago === 'OTRO'          ? 'selected' : ''; ?>>Otro</option>
                    </select>
                </div>

                <div class="pago-filtro-grupo">
                    <select name="estado" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="COMPLETADO" <?php echo $estado === 'COMPLETADO' ? 'selected' : ''; ?>>Completado</option>
                        <option value="ANULADO"    <?php echo $estado === 'ANULADO'    ? 'selected' : ''; ?>>Anulado</option>
                    </select>
                </div>

                <div class="pago-filtro-grupo">
                    <input type="date" name="fecha_desde" class="form-control" value="<?php echo e($fecha_desde); ?>">
                </div>

                <div class="pago-filtro-grupo">
                    <input type="date" name="fecha_hasta" class="form-control" value="<?php echo e($fecha_hasta); ?>">
                </div>

                <div class="pago-filtro-grupo pago-filtro-acciones">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="<?php echo url('pagos'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- TABLA -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Lista de Pagos</h2>
    </div>
    <div class="card-body">
        <?php if (empty($pagos)): ?>
            <div class="empty-state">
                <i class="fas fa-money-bill-wave"></i>
                <p>No se encontraron pagos</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>Método</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagos as $pago): ?>
                        <tr <?php echo $pago->isAnulado() ? 'class="pago-fila-anulado"' : ''; ?>>
                            <td><strong><?php echo e($pago->codigo); ?></strong></td>
                            <td><?php echo formatDate($pago->fecha); ?></td>
                            <td>
                                <?php echo e($pago->getClienteNombreCompleto()); ?>
                                <br><small class="text-muted">CI: <?php echo e($pago->cliente_ci); ?></small>
                            </td>
                            <td>
                                <span class="badge <?php echo $pago->getTipoBadgeClass(); ?>">
                                    <?php echo $pago->getTipoTexto(); ?>
                                </span>
                            </td>
                            <td><?php echo $pago->getMetodoPagoTexto(); ?></td>
                            <td class="<?php echo $pago->tipo === 'PAGO_CLIENTE' ? 'pago-valor-positivo' : 'pago-valor-negativo'; ?>">
                                <strong>Bs <?php echo number_format($pago->monto, 2); ?></strong>
                            </td>
                            <td>
                                <?php if ($pago->isAnulado()): ?>
                                    <span class="badge badge-danger">Anulado</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Completado</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo url('pagos/ver/' . $pago->id_pago); ?>" class="btn btn-sm btn-info" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo url('pagos/recibo/' . $pago->id_pago); ?>" class="btn btn-sm btn-danger" title="Recibo PDF" target="_blank">
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
                    Mostrando <?php echo (($page - 1) * $perPage) + 1; ?> - <?php echo min($page * $perPage, $totalPagos); ?> de <?php echo $totalPagos; ?> pagos
                </div>

                <?php
                $qp = array_filter(compact('search', 'tipo', 'metodo_pago', 'estado', 'fecha_desde', 'fecha_hasta'));
                $qStr = !empty($qp) ? '?' . http_build_query($qp) . '&page=' : '?page=';
                ?>

                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo url('pagos' . $qStr . ($page - 1)); ?>" class="pagination-link">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage   = min($totalPages, $page + 2);

                    if ($startPage > 1): ?>
                        <a href="<?php echo url('pagos' . $qStr . 1); ?>" class="pagination-link">1</a>
                        <?php if ($startPage > 2): ?><span class="pagination-dots">...</span><?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="<?php echo url('pagos' . $qStr . $i); ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?><span class="pagination-dots">...</span><?php endif; ?>
                        <a href="<?php echo url('pagos' . $qStr . $totalPages); ?>" class="pagination-link"><?php echo $totalPages; ?></a>
                    <?php endif; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo url('pagos' . $qStr . ($page + 1)); ?>" class="pagination-link">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>