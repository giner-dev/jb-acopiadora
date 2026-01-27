<div class="page-header">
    <div>
        <h1><i class="fas fa-user"></i> Detalle del Cliente</h1>
        <p>Información completa del cliente</p>
    </div>
    <div>
        <a href="<?php echo url('clientes/editar/' . $cliente->id_cliente); ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i>
            Editar
        </a>
        <a href="<?php echo url('clientes'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Información Personal</h2>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-id-card"></i>
                            CI:
                        </span>
                        <span class="detail-value"><?php echo e($cliente->ci); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-toggle-on"></i>
                            Estado:
                        </span>
                        <span class="detail-value">
                            <?php if ($cliente->estado === 'activo'): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactivo</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-user"></i>
                            Nombres:
                        </span>
                        <span class="detail-value"><?php echo e($cliente->nombres); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-user"></i>
                            Apellidos:
                        </span>
                        <span class="detail-value"><?php echo e($cliente->apellidos); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Comunidad:
                        </span>
                        <span class="detail-value"><?php echo e($cliente->comunidad ?? '-'); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-phone"></i>
                            Teléfono:
                        </span>
                        <span class="detail-value"><?php echo e($cliente->telefono ?? '-'); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-calendar"></i>
                            Fecha de Registro:
                        </span>
                        <span class="detail-value"><?php echo formatDate($cliente->fecha_creacion); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-wallet"></i> Cuenta Corriente</h2>
            </div>
            <div class="card-body">
                <div class="saldo-info">
                    <div class="saldo-item">
                        <span class="saldo-label">Debe (Facturas):</span>
                        <span class="saldo-value text-danger">
                            <?php echo formatMoney($saldo['debe']); ?>
                        </span>
                    </div>
                    
                    <div class="saldo-item">
                        <span class="saldo-label">Haber (Acopios):</span>
                        <span class="saldo-value text-success">
                            <?php echo formatMoney($saldo['haber']); ?>
                        </span>
                    </div>
                    
                    <hr>
                    
                    <div class="saldo-item saldo-total">
                        <span class="saldo-label">Saldo Total:</span>
                        <span class="saldo-value <?php echo $saldo['saldo'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo formatMoney($saldo['saldo']); ?>
                        </span>
                    </div>
                    
                    <?php if ($saldo['saldo'] > 0): ?>
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-check-circle"></i>
                            El cliente tiene saldo a favor
                        </div>
                    <?php elseif ($saldo['saldo'] < 0): ?>
                        <div class="alert alert-danger mt-3">
                            <i class="fas fa-exclamation-triangle"></i>
                            El cliente tiene deuda pendiente
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle"></i>
                            La cuenta está saldada
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-3">
                    <a href="<?php echo url('cuenta-corriente?cliente=' . $cliente->id_cliente); ?>" class="btn btn-primary btn-block">
                        <i class="fas fa-list"></i>
                        Ver Movimientos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>