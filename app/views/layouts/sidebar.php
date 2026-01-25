<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <img src="<?php echo asset('images/logo_jb_acopiadora_2.png'); ?>" alt="JB Acopiadora">
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="<?php echo url('dashboard'); ?>" class="nav-link">
                    <i class="fas fa-chart-line"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <li>
                <a href="<?php echo url('clientes'); ?>" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Clientes</span>
                </a>
            </li>
            
            <li>
                <a href="<?php echo url('productos'); ?>" class="nav-link">
                    <i class="fas fa-box"></i>
                    <span class="nav-text">Productos</span>
                </a>
            </li>
            
            <li>
                <a href="<?php echo url('facturas'); ?>" class="nav-link">
                    <i class="fas fa-file-invoice"></i>
                    <span class="nav-text">Facturas</span>
                </a>
            </li>
            
            <li>
                <a href="<?php echo url('acopios'); ?>" class="nav-link">
                    <i class="fas fa-seedling"></i>
                    <span class="nav-text">Acopios</span>
                </a>
            </li>
            
            <li>
                <a href="<?php echo url('cuenta-corriente'); ?>" class="nav-link">
                    <i class="fas fa-money-bill-wave"></i>
                    <span class="nav-text">Cuenta Corriente</span>
                </a>
            </li>
            
            <li>
                <a href="<?php echo url('pagos'); ?>" class="nav-link">
                    <i class="fas fa-credit-card"></i>
                    <span class="nav-text">Pagos</span>
                </a>
            </li>
            
            <li>
                <a href="<?php echo url('reportes'); ?>" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-text">Reportes</span>
                </a>
            </li>
            
            <?php if (hasRole('Administrador')): ?>
            <li>
                <a href="<?php echo url('usuarios'); ?>" class="nav-link">
                    <i class="fas fa-user-shield"></i>
                    <span class="nav-text">Usuarios</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr(authUserFullName(), 0, 1)); ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo e(authUserFullName()); ?></div>
                <div class="user-role"><?php echo e(authUserRole()); ?></div>
            </div>
        </div>
    </div>
</aside>