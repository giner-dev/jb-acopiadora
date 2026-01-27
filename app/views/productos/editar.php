<div class="page-header">
    <div>
        <h1><i class="fas fa-edit"></i> Editar Producto</h1>
        <p>Actualiza la información del producto</p>
    </div>
    <div>
        <a href="<?php echo url('productos'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('productos/actualizar/' . $producto->id_producto); ?>" method="POST" id="formProducto">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="codigo">
                        <i class="fas fa-barcode"></i>
                        Código
                    </label>
                    <input 
                        type="text" 
                        id="codigo" 
                        name="codigo" 
                        class="form-control" 
                        value="<?php echo e($producto->codigo); ?>">
                </div>
                
                <div class="form-group col-md-4">
                    <label for="nombre">
                        <i class="fas fa-tag"></i>
                        Nombre del Producto
                        <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        class="form-control" 
                        value="<?php echo e($producto->nombre); ?>"
                        required>
                </div>
                
                <div class="form-group col-md-4">
                    <label for="estado">
                        <i class="fas fa-toggle-on"></i>
                        Estado
                        <span class="text-danger">*</span>
                    </label>
                    <select id="estado" name="estado" class="form-control" required>
                        <option value="activo" <?php echo $producto->estado === 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo $producto->estado === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="categoria_id">
                        <i class="fas fa-th-large"></i>
                        Categoría
                    </label>
                    <select id="categoria_id" name="categoria_id" class="form-control">
                        <option value="">Sin categoría</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo $categoria['id_categoria']; ?>"
                                    <?php echo $producto->categoria_id == $categoria['id_categoria'] ? 'selected' : ''; ?>>
                                <?php echo e($categoria['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                            <option value="<?php echo $unidad['id_unidad']; ?>"
                                    <?php echo $producto->unidad_id == $unidad['id_unidad'] ? 'selected' : ''; ?>>
                                <?php echo e($unidad['nombre']); ?> (<?php echo e($unidad['codigo']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="precio_venta">
                        <i class="fas fa-dollar-sign"></i>
                        Precio de Venta (Bs)
                        <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="precio_venta" 
                        name="precio_venta" 
                        class="form-control" 
                        step="0.01"
                        min="0"
                        value="<?php echo $producto->precio_venta; ?>"
                        required>
                </div>
                
                <div class="form-group col-md-4">
                    <label for="stock_actual">
                        <i class="fas fa-boxes"></i>
                        Stock Actual
                    </label>
                    <input 
                        type="text" 
                        class="form-control" 
                        value="<?php echo number_format($producto->stock_actual, 2); ?> <?php echo e($producto->unidad_codigo ?? ''); ?>"
                        disabled>
                    <small>El stock se actualiza desde entradas y salidas de inventario</small>
                </div>
                
                <div class="form-group col-md-4">
                    <label for="stock_minimo">
                        <i class="fas fa-exclamation-triangle"></i>
                        Stock Mínimo
                    </label>
                    <input 
                        type="number" 
                        id="stock_minimo" 
                        name="stock_minimo" 
                        class="form-control" 
                        step="0.01"
                        min="0"
                        value="<?php echo $producto->stock_minimo; ?>">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Actualizar Producto
                </button>
                <a href="<?php echo url('productos'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>