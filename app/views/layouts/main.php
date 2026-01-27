<?php include __DIR__ . '/header.php'; ?>

<div class="main-wrapper">
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <div class="main-content">
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="topbar-right">
                <a href="<?php echo url('cambiar-password'); ?>" class="topbar-link">
                    <i class="fas fa-key"></i>
                    <span>Cambiar Contraseña</span>
                </a>
                <a href="<?php echo url('logout'); ?>" class="topbar-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </div>
        </header>
        
        <main class="content">
            <?php $success = flash('success'); ?>
            <?php if ($success): ?>
            <div class="alert alert-success" id="flashMessage">
                <i class="fas fa-check-circle"></i>
                <?php echo e($success); ?>
            </div>
            <?php endif; ?>
            
            <?php $error = flash('error'); ?>
            <?php if ($error): ?>
            <div class="alert alert-error" id="flashMessage">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo e($error); ?>
            </div>
            <?php endif; ?>
            
            <?php $warning = flash('warning'); ?>
            <?php if ($warning): ?>
            <div class="alert alert-warning" id="flashMessage">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo e($warning); ?>
            </div>
            <?php endif; ?>
            
            <?php $info = flash('info'); ?>
            <?php if ($info): ?>
            <div class="alert alert-info" id="flashMessage">
                <i class="fas fa-info-circle"></i>
                <?php echo e($info); ?>
            </div>
            <?php endif; ?>
            
            <?php echo $content; ?>
        </main>
    </div>
</div>

<script>
    window.PHP_BASE_URL = '<?php echo url(); ?>';
</script>

<script src="<?php echo asset('js/main.js?v2'); ?>"></script>
<script src="<?php echo asset('js/dashboard.js'); ?>"></script>
<script src="<?php echo asset('js/clientes.js'); ?>"></script>

</body>
</html>