<div class="page-header">
    <div>
        <h1><i class="fas fa-edit"></i> Editar Acopio <?php echo e($acopio->codigo); ?></h1>
        <p>Modifica los datos del acopio</p>
    </div>
    <div>
        <a href="<?php echo url('acopios/ver/' . $acopio->id_acopio); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<?php if ($acopio->isAnulado()): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>Advertencia:</strong> No se puede editar un acopio anulado.
</div>
<?php else: ?>

<form action="<?php echo url('acopios/actualizar/' . $acopio->id_acopio); ?>" method="POST" id="formAcopio">
    <input type="hidden" name="detalles_json" id="acopio_detalles_json">
    <input type="hidden" name="cliente_id" id="acopio_cliente_id_hidden" value="<?php echo $acopio->cliente_id; ?>">
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-info-circle"></i> Datos del Acopio</h2>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="codigo">
                                <i class="fas fa-barcode"></i>
                                Código
                            </label>
                            <input 
                                type="text" 
                                id="codigo" 
                                class="form-control" 
                                value="<?php echo e($acopio->codigo); ?>"
                                disabled>
                            <small class="text-muted">El código no se puede modificar</small>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="cliente_btn">
                                <i class="fas fa-user"></i>
                                Cliente
                                <span class="text-danger">*</span>
                            </label>
                            <button type="button" class="btn btn-secondary btn-block" onclick="AcopioModule.abrirModalClientes()">
                                <i class="fas fa-search"></i>
                                Cambiar Cliente
                            </button>
                            <div id="acopio_cliente_display" style="display: block;">
                                <span id="acopio_cliente_seleccionado"><?php echo e($acopio->getClienteNombreCompleto() . ' - CI: ' . $acopio->cliente_ci); ?></span>
                                <button type="button" class="acopio-btn-limpiar-cliente" onclick="AcopioModule.limpiarCliente()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label for="fecha">
                                <i class="fas fa-calendar"></i>
                                Fecha
                                <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="date" 
                                id="fecha" 
                                name="fecha" 
                                class="form-control" 
                                value="<?php echo $acopio->fecha; ?>"
                                required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="observaciones">
                            <i class="fas fa-comment"></i>
                            Observaciones
                        </label>
                        <textarea 
                            id="observaciones" 
                            name="observaciones" 
                            class="form-control" 
                            rows="2"
                            placeholder="Observaciones adicionales (opcional)"><?php echo e($acopio->observaciones); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-wheat-awn"></i> Granos</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <button type="button" class="btn btn-primary btn-block" onclick="AcopioModule.abrirModalGranos()">
                                <i class="fas fa-search"></i>
                                Buscar y Agregar Grano
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table" id="acopio_tablaGranos">
                            <thead>
                                <tr>
                                    <th>Grano</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="acopio_detallesBody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        Cargando granos...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-calculator"></i> Resumen</h2>
                </div>
                <div class="card-body">
                    <div class="acopio-resumen-item">
                        <span class="acopio-resumen-label">Subtotal:</span>
                        <span class="acopio-resumen-value" id="acopio_subtotalDisplay">Bs 0.00</span>
                    </div>
                    
                    <div class="acopio-resumen-item acopio-resumen-total">
                        <span class="acopio-resumen-label">TOTAL A PAGAR:</span>
                        <span class="acopio-resumen-value text-success" id="acopio_totalDisplay">Bs 0.00</span>
                    </div>
                    
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Este monto se abonará automáticamente a la cuenta corriente del cliente
                    </small>
                    
                    <hr>
                    
                    <button type="submit" class="btn btn-success btn-block btn-lg">
                        <i class="fas fa-save"></i>
                        Guardar Cambios
                    </button>
                    
                    <a href="<?php echo url('acopios/ver/' . $acopio->id_acopio); ?>" class="btn btn-secondary btn-block">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Modal Clientes -->
<div id="acopio_modalClientes" class="modal-acopios">
    <div class="modal-acopios-content">
        <div class="modal-acopios-header">
            <h3><i class="fas fa-users"></i> Seleccionar Cliente</h3>
            <button type="button" class="modal-acopios-close" onclick="AcopioModule.cerrarModalClientes()">&times;</button>
        </div>
        <div class="modal-acopios-search">
            <input 
                type="text" 
                id="acopio_searchCliente" 
                class="acopio-modal-search-input" 
                placeholder="Buscar por CI, nombre o comunidad..."
                onkeyup="AcopioModule.buscarClientes()">
        </div>
        <div class="modal-acopios-body">
            <table class="acopio-modal-table">
                <thead>
                    <tr>
                        <th>CI</th>
                        <th>Nombre Completo</th>
                        <th>Comunidad</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="acopio_clientesTableBody">
                    <tr>
                        <td colspan="4" class="text-center">Cargando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Granos -->
<div id="acopio_modalGranos" class="modal-acopios">
    <div class="modal-acopios-content">
        <div class="modal-acopios-header">
            <h3><i class="fas fa-wheat-awn"></i> Seleccionar Grano</h3>
            <button type="button" class="modal-acopios-close" onclick="AcopioModule.cerrarModalGranos()">&times;</button>
        </div>
        <div class="modal-acopios-search">
            <input 
                type="text" 
                id="acopio_searchGrano" 
                class="acopio-modal-search-input" 
                placeholder="Buscar por nombre..."
                onkeyup="AcopioModule.buscarGranos()">
        </div>
        <div class="modal-acopios-body">
            <table class="acopio-modal-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Precio Actual</th>
                        <th>Vigencia</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="acopio_granosTableBody">
                    <tr>
                        <td colspan="4" class="text-center">Cargando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Cantidad -->
<div id="acopio_modalCantidad" class="modal-acopios">
    <div class="modal-acopios-content" style="max-width: 500px;">
        <div class="modal-acopios-header">
            <h3><i class="fas fa-plus-circle"></i> Agregar Grano</h3>
            <button type="button" class="modal-acopios-close" onclick="AcopioModule.cerrarModalCantidad()">&times;</button>
        </div>
        <div class="modal-acopios-body">
            <div class="form-group">
                <label><strong>Grano:</strong></label>
                <p id="acopio_grano_seleccionado_nombre" style="font-size: 16px; color: #433F4E;"></p>
            </div>
            
            <div class="form-group">
                <label><strong>Precio Actual:</strong></label>
                <p id="acopio_grano_seleccionado_precio" style="font-size: 16px; color: #28a745;"></p>
            </div>
            
            <div class="alert alert-info" style="font-size: 13px;">
                <i class="fas fa-info-circle"></i>
                Puedes modificar el precio. Si lo haces, se actualizará automáticamente el precio del grano para hoy.
            </div>
            
            <div class="form-group">
                <label for="acopio_modal_cantidad">
                    <i class="fas fa-balance-scale"></i>
                    Cantidad
                    <span class="text-danger">*</span>
                </label>
                <input 
                    type="number" 
                    id="acopio_modal_cantidad" 
                    class="form-control" 
                    step="0.01"
                    min="0.01"
                    placeholder="Ej: 500.00"
                    autofocus>
            </div>
            
            <div class="form-group">
                <label for="acopio_modal_precio">
                    <i class="fas fa-dollar-sign"></i>
                    Precio Unitario (Bs)
                    <span class="text-danger">*</span>
                </label>
                <input 
                    type="number" 
                    id="acopio_modal_precio" 
                    class="form-control" 
                    step="0.01"
                    min="0.01"
                    placeholder="Ej: 8.50">
                <small>Si cambias el precio, se actualizará para el grano</small>
            </div>
            
            <input type="hidden" id="acopio_grano_temp_id">
            <input type="hidden" id="acopio_grano_temp_nombre">
            <input type="hidden" id="acopio_grano_temp_precio_original">
            <input type="hidden" id="acopio_grano_temp_unidad">
            
            <div class="form-actions" style="margin-top: 20px;">
                <button type="button" class="btn btn-success btn-block" onclick="AcopioModule.confirmarAgregarGrano()">
                    <i class="fas fa-check"></i>
                    Agregar a Acopio
                </button>
                <button type="button" class="btn btn-secondary btn-block" onclick="AcopioModule.cerrarModalCantidad()">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.addEventListener('DOMContentLoaded', function() {
    const detallesOriginales = <?php echo json_encode($acopio->detalles); ?>;
    AcopioModule.cargarDetallesExistentes(detallesOriginales);
});
</script>

<?php endif; ?>