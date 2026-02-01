<?php $saldo = floatval($saldoCliente['saldo'] ?? 0); ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-money-bill-wave"></i> Detalle de Pago</h1>
        <p><?php echo e($pago->codigo); ?></p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo url('pagos/recibo/' . $pago->id_pago); ?>" class="btn btn-danger" target="_blank">
            <i class="fas fa-file-pdf"></i> Recibo PDF
        </a>
        <?php if ($pago->isCompletado()): ?>
            <a href="<?php echo url('pagos/anular/' . $pago->id_pago); ?>" class="btn btn-outline-danger"
                 onclick="return confirm('Anular este pago revierte automáticamente el movimiento en cuenta corriente. ¿Estás seguro?')">
                <i class="fas fa-ban"></i> Anular
            </a>
        <?php endif; ?>
        <a href="<?php echo url('pagos'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<?php if ($pago->isAnulado()): ?>
    <div class="pago-alerta-anulado">
        <i class="fas fa-ban"></i> Este pago fue anulado. El movimiento correspondiente en cuenta corriente fue revertido automáticamente.
    </div>
<?php endif; ?>

<div class="row">
    <!-- DATOS PRINCIPALES -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-file-alt"></i> Información del Pago</h2>
            </div>
            <div class="card-body">
                <div class="pago-detalle-grid">
                    <div class="pago-detalle-item">
                        <span class="pago-detalle-label">Código:</span>
                        <span class="pago-detalle-value"><strong><?php echo e($pago->codigo); ?></strong></span>
                    </div>
                    <div class="pago-detalle-item">
                        <span class="pago-detalle-label">Cliente:</span>
                        <span class="pago-detalle-value"><?php echo e($pago->getClienteNombreCompleto()); ?></span>
                    </div>
                    <div class="pago-detalle-item">
                        <span class="pago-detalle-label">CI:</span>
                        <span class="pago-detalle-value"><?php echo e($pago->cliente_ci); ?></span>
                    </div>
                    <?php if (!empty($pago->cliente_comunidad)): ?>
                    <div class="pago-detalle-item">
                        <span class="pago-detalle-label">Comunidad:</span>
                        <span class="pago-detalle-value"><?php echo e($pago->cliente_comunidad); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="pago-detalle-item">
                        <span class="pago-detalle-label">Fecha:</span>
                        <span class="pago-detalle-value"><?php echo formatDate($pago->fecha); ?></span>
                    </div>
                    <div class="pago-detalle-item">
                        <span class="pago-detalle-label">Tipo:</span>
                        <span class="pago-detalle-value">
                            <span class="badge <?php echo $pago->getTipoBadgeClass(); ?>">
                                <?php echo $pago->getTipoTexto(); ?>
                            </span>
                        </span>
                    </div>
                    <div class="pago-detalle-item">
                        <span class="pago-detalle-label">Método:</span>
                        <span class="pago-detalle-value"><?php echo $pago->getMetodoPagoTexto(); ?></span>
                    </div>
                    <?php if (!empty($pago->referencia_operacion)): ?>
                    <div class="pago-detalle-item">
                        <span class="pago-detalle-label">Referencia:</span>
                        <span class="pago-detalle-value"><?php echo e($pago->referencia_operacion); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($pago->concepto)): ?>
                    <div class="pago-detalle-item">
                        <span class="pago-detalle-label">Concepto:</span>
                        <span class="pago-detalle-value"><?php echo e($pago->concepto); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="pago-detalle-item">
                        <span class="pago-detalle-label">Estado:</span>
                        <span class="pago-detalle-value">
                            <?php if ($pago->isAnulado()): ?>
                                <span class="badge badge-danger">Anulado</span>
                            <?php else: ?>
                                <span class="badge badge-success">Completado</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="pago-detalle-item">
                        <span class="pago-detalle-label">Registrado por:</span>
                        <span class="pago-detalle-value"><?php echo e($pago->usuario_nombre ?? 'Sistema'); ?></span>
                    </div>
                </div>

                <!-- MONTO DESTACADO -->
                <div class="pago-monto-box">
                    <span class="pago-monto-etiqueta"><?php echo $pago->getTipoTexto(); ?></span>
                    <span class="pago-monto-valor">Bs <?php echo number_format($pago->monto, 2); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- PANEL LATERAL: SALDO CLIENTE -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-balance-scale"></i> Saldo Cliente</h2>
            </div>
            <div class="card-body">
                <div class="pago-saldo-actual">
                    <?php if ($saldo > 0): ?>
                        <div class="pago-saldo-etiqueta pago-etiqueta-debe">Cliente debe a JB</div>
                        <div class="pago-saldo-monto pago-valor-positivo">Bs <?php echo number_format($saldo, 2); ?></div>
                    <?php elseif ($saldo < 0): ?>
                        <div class="pago-saldo-etiqueta pago-etiqueta-haber">JB debe a Cliente</div>
                        <div class="pago-saldo-monto pago-valor-negativo">Bs <?php echo number_format(abs($saldo), 2); ?></div>
                    <?php else: ?>
                        <div class="pago-saldo-etiqueta pago-etiqueta-cero">Saldo en Cero</div>
                        <div class="pago-saldo-monto">Bs 0.00</div>
                    <?php endif; ?>
                </div>

                <div class="pago-acciones-cliente">
                    <a href="<?php echo url('cuenta-corriente/ver-cliente/' . $pago->cliente_id); ?>" class="btn btn-info btn-block">
                        <i class="fas fa-balance-scale"></i> Ver Cuenta Corriente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>