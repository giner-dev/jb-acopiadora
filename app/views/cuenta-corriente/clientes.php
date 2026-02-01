<div class="page-header">
    <div>
        <h1><i class="fas fa-users"></i> Clientes - Cuenta Corriente</h1>
        <p>Estado de saldo por cliente</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo url('cuenta-corriente'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<!-- RESUMEN RAPIDO -->
<?php
$totalDeudores = 0;
$totalAcreedores = 0;
$sumDeuda = 0;
$sumAcreedor = 0;

foreach ($clientes as $c) {
    $saldo = floatval($c['saldo']);
    if ($saldo > 0) {
        $totalDeudores++;
        $sumDeuda += $saldo;
    } elseif ($saldo < 0) {
        $totalAcreedores++;
        $sumAcreedor += abs($saldo);
    }
}
?>

<div class="cc-stats-grid">
    <div class="cc-stat-card">
        <div class="cc-stat-icon cc-icon-total">
            <i class="fas fa-users"></i>
        </div>
        <div class="cc-stat-info">
            <span class="cc-stat-label">Total Clientes</span>
            <span class="cc-stat-value"><?php echo count($clientes); ?></span>
        </div>
    </div>
    
    <div class="cc-stat-card cc-stat-debe">
        <div class="cc-stat-icon">
            <i class="fas fa-user-times"></i>
        </div>
        <div class="cc-stat-info">
            <span class="cc-stat-label">Deudores (<?php echo $totalDeudores; ?>)</span>
            <span class="cc-stat-value">Bs <?php echo number_format($sumDeuda, 2); ?></span>
        </div>
    </div>
    
    <div class="cc-stat-card cc-stat-haber">
        <div class="cc-stat-icon">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="cc-stat-info">
            <span class="cc-stat-label">Acreedores (<?php echo $totalAcreedores; ?>)</span>
            <span class="cc-stat-value">Bs <?php echo number_format($sumAcreedor, 2); ?></span>
        </div>
    </div>
    
    <div class="cc-stat-card cc-stat-saldo">
        <div class="cc-stat-icon">
            <i class="fas fa-balance-scale"></i>
        </div>
        <div class="cc-stat-info">
            <span class="cc-stat-label">Balance Neto</span>
            <span class="cc-stat-value <?php echo ($sumDeuda - $sumAcreedor) >= 0 ? 'cc-valor-debe' : 'cc-valor-haber'; ?>">
                Bs <?php echo number_format(abs($sumDeuda - $sumAcreedor), 2); ?>
            </span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Lista de Clientes con Saldo</h2>
    </div>
    <div class="card-body">
        <?php if (empty($clientes)): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <p>Todos los clientes tienen saldo en cero</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>CI</th>
                            <th>Cliente</th>
                            <th>Comunidad</th>
                            <th>Saldo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                        <?php $saldo = floatval($cliente['saldo']); ?>
                        <tr>
                            <td><?php echo e($cliente['ci']); ?></td>
                            <td><strong><?php echo e($cliente['nombres'] . ' ' . $cliente['apellidos']); ?></strong></td>
                            <td><?php echo e($cliente['comunidad'] ?? '-'); ?></td>
                            <td class="<?php echo $saldo > 0 ? 'cc-valor-debe' : 'cc-valor-haber'; ?>">
                                <strong>Bs <?php echo number_format(abs($saldo), 2); ?></strong>
                            </td>
                            <td>
                                <?php if ($saldo > 0): ?>
                                    <span class="badge badge-danger">Cliente debe</span>
                                <?php else: ?>
                                    <span class="badge badge-success">JB debe</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo url('cuenta-corriente/ver-cliente/' . $cliente['id_cliente']); ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="Ver estado de cuenta">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo url('cuenta-corriente/pdf/' . $cliente['id_cliente']); ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="PDF"
                                       target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>