<div class="page-header">
    <div>
        <h1><i class="fas fa-user-plus"></i> Nuevo Cliente</h1>
        <p>Registra un nuevo cliente en el sistema</p>
    </div>
    <div>
        <a href="<?php echo url('clientes'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('clientes/guardar'); ?>" method="POST" id="formCliente">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="ci">
                        <i class="fas fa-id-card"></i>
                        Cédula de Identidad (CI)
                        <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="ci" 
                        name="ci" 
                        class="form-control" 
                        placeholder="Ej: 12345678"
                        required
                        autofocus>
                    <small>Solo números, sin guiones ni espacios</small>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="nombres">
                        <i class="fas fa-user"></i>
                        Nombres
                        <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="nombres" 
                        name="nombres" 
                        class="form-control" 
                        placeholder="Ej: Juan Carlos"
                        required>
                </div>
                
                <div class="form-group col-md-6">
                    <label for="apellidos">
                        <i class="fas fa-user"></i>
                        Apellidos
                        <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="apellidos" 
                        name="apellidos" 
                        class="form-control" 
                        placeholder="Ej: Pérez López"
                        required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="comunidad">
                        <i class="fas fa-map-marker-alt"></i>
                        Comunidad
                    </label>
                    <input 
                        type="text" 
                        id="comunidad" 
                        name="comunidad" 
                        class="form-control" 
                        placeholder="Ej: San Juan">
                </div>
                
                <div class="form-group col-md-6">
                    <label for="telefono">
                        <i class="fas fa-phone"></i>
                        Teléfono
                    </label>
                    <input 
                        type="text" 
                        id="telefono" 
                        name="telefono" 
                        class="form-control" 
                        placeholder="Ej: 77123456">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Guardar Cliente
                </button>
                <a href="<?php echo url('clientes'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>