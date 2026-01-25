<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - JB Acopiadora</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo asset('css/login.css'); ?>">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="<?php echo asset('images/logo_jb_acopiadora.png'); ?>" alt="JB Acopiadora" class="login-logo">
                <p>Sistema de Gestión</p>
            </div>
            
            <?php if (isset($expired) && $expired): ?>
            <div class="alert alert-warning">
                <i class="fas fa-clock"></i>
                Su sesión ha expirado por inactividad. Por favor inicie sesión nuevamente.
            </div>
            <?php endif; ?>
            
            <?php $success = flash('success'); ?>
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo e($success); ?>
            </div>
            <?php endif; ?>
            
            <?php $error = flash('error'); ?>
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo e($error); ?>
            </div>
            <?php endif; ?>
            
            <form action="<?php echo url('login'); ?>" method="POST" class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Usuario
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control" 
                        placeholder="Ingrese su usuario"
                        required
                        autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Contraseña
                    </label>
                    <div class="password-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="Ingrese su contraseña"
                            required>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar Sesión
                </button>
            </form>
            
            <div class="login-footer">
                <p>&copy; <?php echo date('Y'); ?> Todos los derechos reservados JB Acopiadora</p>
                <p class="des">Desarrollado por <span>G.E.M</span></p>
            </div>
        </div>
    </div>
    
    <script src="<?php echo asset('js/login.js'); ?>"></script>
</body>
</html>