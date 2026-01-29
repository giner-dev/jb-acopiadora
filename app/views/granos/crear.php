<div class="page-header">
    <div>
        <h1><i class="fas fa-wheat-awn"></i> Nuevo Grano</h1>
        <p>Registra un nuevo tipo de grano en el catálogo</p>
    </div>
    <div>
        <a href="<?php echo url('granos'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('granos/guardar'); ?>" method="POST" id="formGrano">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="nombre">
                        <i class="fas fa-tag"></i>
                        Nombre del Grano
                        <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        class="form-control" 
                        placeholder="Ej: Maíz, Quinua, Trigo"
                        required
                        autofocus>
                </div>
                
                <div class="form-group col-md-6">
                    <label for="unidad_id">
                        <i class="fas fa-balance-scale"></i>
                        Unidad de Medida
                        <span class="text-danger">*</span>
                    </label>
                    <select id="unidad_id" name="unidad_id" class="form-control" required>
                        <option value="">Seleccione una unidad</option>
                        <?php foreach ($unidades as $unidad): ?>
                            <option value="<?php echo $unidad['id_unidad']; ?>">
                                <?php echo e($unidad['nombre']); ?> (<?php echo e($unidad['codigo']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="descripcion">
                    <i class="fas fa-align-left"></i>
                    Descripción
                </label>
                <textarea 
                    id="descripcion" 
                    name="descripcion" 
                    class="form-control" 
                    rows="3"
                    placeholder="Descripción opcional del grano"></textarea>
                <small>Puede incluir información adicional como variedades, características, etc.</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Guardar Grano
                </button>
                <a href="<?php echo url('granos'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>