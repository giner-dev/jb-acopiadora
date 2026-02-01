<?php $saldo = floatval($cliente['saldo']); ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-balance-scale"></i> Estado de Cuenta</h1>
        <p><?php echo e($cliente['nombres'] . ' ' . $cliente['apellidos']); ?></p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo url('cuenta-corriente/pdf/' . $cliente['id_cliente']); ?>" 
           class="btn btn-danger"
           target="_blank">
            <i class="fas fa-file-pdf"></i>
            PDF
        </a>
        <a href="<?php echo url('cuenta-corriente/clientes'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- DATOS DEL CLIENTE + SALDO -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-user"></i> Cliente</h2>
            </div>
            <div class="card-body">
                <div class="cc-detail-grid">
                    <div class="cc-detail-item">
                        <span class="cc-detail-label">Nombre:</span>
                        <span class="cc-detail-value"><?php echo e($cliente['nombres'] . ' ' . $cliente['apellidos']); ?></span>
                    </div>
                    <div class="cc-detail-item">
                        <span class="cc-detail-label">CI:</span>
                        <span class="cc-detail-value"><?php echo e($cliente['ci']); ?></span>
                    </div>
                    <?php if (!empty($cliente['comunidad'])): ?>
                    <div class="cc-detail-item">
                        <span class="cc-detail-label">Comunidad:</span>
                        <span class="cc-detail-value"><?php echo e($cliente['comunidad']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-calculator"></i> Saldo Actual</h2>
            </div>
            <div class="card-body">
                <div class="cc-saldo-principal">
                    <?php if ($saldo > 0): ?>
                        <div class="cc-saldo-etiqueta cc-etiqueta-debe">Cliente debe a JB</div>
                        <div class="cc-saldo-monto cc-valor-debe">Bs <?php echo number_format($saldo, 2); ?></div>
                    <?php elseif ($saldo < 0): ?>
                        <div class="cc-saldo-etiqueta cc-etiqueta-haber">JB debe a Cliente</div>
                        <div class="cc-saldo-monto cc-valor-haber">Bs <?php echo number_format(abs($saldo), 2); ?></div>
                    <?php else: ?>
                        <div class="cc-saldo-etiqueta cc-etiqueta-cero">Saldo en Cero</div>
                        <div class="cc-saldo-monto">Bs 0.00</div>
                    <?php endif; ?>
                </div>

                <?php
                $totalDebe = 0;
                $totalHaber = 0;
                foreach ($movimientos as $mov) {
                    $totalDebe += floatval($mov->debe);
                    $totalHaber += floatval($mov->haber);
                }
                ?>

                <div class="cc-saldo-resumen">
                    <div class="cc-saldo-fila">
                        <span>Total Debe:</span>
                        <span class="cc-valor-debe">Bs <?php echo number_format($totalDebe, 2); ?></span>
                    </div>
                    <div class="cc-saldo-fila">
                        <span>Total Haber:</span>
                        <span class="cc-valor-haber">Bs <?php echo number_format($totalHaber, 2); ?></span>
                    </div>
                    <div class="cc-saldo-fila cc-saldo-fila-total">
                        <span>Saldo:</span>
                        <span class="<?php echo $saldo > 0 ? 'cc-valor-debe' : ($saldo < 0 ? 'cc-valor-haber' : ''); ?>">
                            Bs <?php echo number_format(abs($saldo), 2); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA DE MOVIMIENTOS -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list"></i> Historial de Movimientos</h2>
            </div>
            <div class="card-body">
                <?php if (empty($movimientos)): ?>
                    <div class="empty-state">
                        <i class="fas fa-balance-scale"></i>
                        <p>No hay movimientos registrados</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Debe</th>
                                    <th>Haber</th>
                                    <th>Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $saldoAcumulado = 0;
                                // Invertir para mostrar más antiguo primero
                                $movOrden = array_reverse($movimientos);

                                foreach ($movOrden as $mov):
                                    $debe = floatval($mov->debe);
                                    $haber = floatval($mov->haber);
                                    $saldoAcumulado += ($debe - $haber);
                                ?>
                                <tr>
                                    <td><?php echo formatDate($mov->fecha); ?></td>
                                    <td>
                                        <span class="badge <?php echo $mov->getTipoBadgeClass(); ?>">
                                            <?php echo $mov->getTipoTexto(); ?>
                                        </span>
                                    </td>
                                    <td><small><?php echo e($mov->descripcion); ?></small></td>
                                    <td class="cc-valor-debe">
                                        <?php echo $debe > 0 ? 'Bs ' . number_format($debe, 2) : '-'; ?>
                                    </td>
                                    <td class="cc-valor-haber">
                                        <?php echo $haber > 0 ? 'Bs ' . number_format($haber, 2) : '-'; ?>
                                    </td>
                                    <td class="<?php echo $saldoAcumulado > 0 ? 'cc-valor-debe' : ($saldoAcumulado < 0 ? 'cc-valor-haber' : ''); ?>">
                                        <strong>Bs <?php echo number_format(abs($saldoAcumulado), 2); ?></strong>
                                        <?php if ($saldoAcumulado > 0): ?>
                                            <small class="d-block">Cliente debe</small>
                                        <?php elseif ($saldoAcumulado < 0): ?>
                                            <small class="d-block">JB debe</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?> 
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>