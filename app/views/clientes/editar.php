<div class="page-header">
    <div>
        <h1><i class="fas fa-user-edit"></i> Editar Cliente</h1>
        <p>Actualiza la información del cliente</p>
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
        <form action="<?php echo url('clientes/actualizar/' . $cliente->id_cliente); ?>" method="POST" id="formCliente">
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
                        value="<?php echo e($cliente->ci); ?>"
                        required>
                    <small>Solo números, sin guiones ni espacios</small>
                </div>
                
                <div class="form-group col-md-6">
                    <label for="estado">
                        <i class="fas fa-toggle-on"></i>
                        Estado
                        <span class="text-danger">*</span>
                    </label>
                    <select id="estado" name="estado" class="form-control" required>
                        <option value="activo" <?php echo $cliente->estado === 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo $cliente->estado === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
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
                        value="<?php echo e($cliente->nombres); ?>"
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
                        value="<?php echo e($cliente->apellidos); ?>"
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
                        value="<?php echo e($cliente->comunidad); ?>">
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
                        value="<?php echo e($cliente->telefono); ?>">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Actualizar Cliente
                </button>
                <a href="<?php echo url('clientes'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>