<div class="page-header">
    <h1><i class="fas fa-key"></i> Cambiar Contraseña</h1>
    <p>Actualiza tu contraseña de acceso al sistema</p>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('cambiar-password'); ?>" method="POST" id="changePasswordForm">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="form-group">
                <label for="current_password">
                    <i class="fas fa-lock"></i>
                    Contraseña Actual
                </label>
                <div class="password-wrapper">
                    <input 
                        type="password" 
                        id="current_password" 
                        name="current_password" 
                        class="form-control" 
                        required>
                    <button type="button" class="toggle-password" data-target="current_password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="new_password">
                    <i class="fas fa-key"></i>
                    Nueva Contraseña
                </label>
                <div class="password-wrapper">
                    <input 
                        type="password" 
                        id="new_password" 
                        name="new_password" 
                        class="form-control" 
                        minlength="6"
                        required>
                    <button type="button" class="toggle-password" data-target="new_password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small>Mínimo 6 caracteres</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">
                    <i class="fas fa-check-circle"></i>
                    Confirmar Nueva Contraseña
                </label>
                <div class="password-wrapper">
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-control" 
                        required>
                    <button type="button" class="toggle-password" data-target="confirm_password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Cambiar Contraseña
                </button>
                <a href="<?php echo url('dashboard'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>