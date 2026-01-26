<div class="page-header">
    <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
    <p>Bienvenido al sistema de gestión JB Acopiadora</p>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-icon icon-primary">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3>Clientes</h3>
            <p class="stat-number">0</p>
            <span class="stat-label">Total registrados</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon icon-secondary">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-info">
            <h3>Productos</h3>
            <p class="stat-number">0</p>
            <span class="stat-label">En inventario</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon icon-primary">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="stat-info">
            <h3>Facturas Pendientes</h3>
            <p class="stat-number">0</p>
            <span class="stat-label">Por cobrar</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon icon-secondary">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-info">
            <h3>Saldo Total</h3>
            <p class="stat-number">Bs 0.00</p>
            <span class="stat-label">Cuentas por cobrar</span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-rocket"></i> Accesos Rápidos</h2>
    </div>
    <div class="card-body">
        <div class="quick-actions">
            <a href="<?php echo url('facturas/crear'); ?>" class="quick-action-btn">
                <i class="fas fa-file-invoice"></i>
                <span>Nueva Factura</span>
            </a>
            
            <a href="<?php echo url('acopios/crear'); ?>" class="quick-action-btn">
                <i class="fas fa-seedling"></i>
                <span>Nuevo Acopio</span>
            </a>
            
            <a href="<?php echo url('clientes/crear'); ?>" class="quick-action-btn">
                <i class="fas fa-user-plus"></i>
                <span>Nuevo Cliente</span>
            </a>
            
            <a href="<?php echo url('productos/crear'); ?>" class="quick-action-btn">
                <i class="fas fa-box-open"></i>
                <span>Nuevo Producto</span>
            </a>
        </div>
    </div>
</div>