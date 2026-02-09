<div class="page-header">
    <div>
        <h1><i class="fas fa-edit"></i> Editar Factura <?php echo e($factura->codigo); ?></h1>
        <p>Modifica los datos de la factura</p>
    </div>
    <div>
        <a href="<?php echo url('facturas/ver/' . $factura->id_factura); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<?php if ($factura->isAnulada()): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>Advertencia:</strong> No se puede editar una factura anulada.
</div>
<?php else: ?>

<form action="<?php echo url('facturas/actualizar/' . $factura->id_factura); ?>" method="POST" id="formFactura">
    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
    <input type="hidden" name="detalles_json" id="detalles_json">
    <input type="hidden" name="cliente_id" id="cliente_id_hidden" value="<?php echo $factura->cliente_id; ?>">
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-info-circle"></i> Datos de la Factura</h2>
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
                                value="<?php echo e($factura->codigo); ?>"
                                disabled>
                            <small class="text-muted">El código no se puede modificar</small>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="cliente_btn">
                                <i class="fas fa-user"></i>
                                Cliente
                                <span class="text-danger">*</span>
                            </label>
                            <button type="button" class="btn btn-secondary btn-block" onclick="abrirModalClientes()">
                                <i class="fas fa-search"></i>
                                Cambiar Cliente
                            </button>
                            <div id="cliente_display" style="display: block;">
                                <span id="cliente_seleccionado"><?php echo e($factura->getClienteNombreCompleto() . ' - CI: ' . $factura->cliente_ci); ?></span>
                                <button type="button" class="btn-limpiar-cliente" onclick="limpiarCliente()">
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
                                value="<?php echo $factura->fecha; ?>"
                                required>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-box"></i> Productos</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <button type="button" class="btn btn-primary btn-block" onclick="abrirModalProductos()">
                                <i class="fas fa-search"></i>
                                Buscar y Agregar Producto
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table" id="tablaProductos">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="detallesBody">
                                <tr id="emptyRow">
                                    <td colspan="5" class="text-center text-muted">
                                        Cargando productos...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- ADELANTOS-->
            <div class="card">
                <div class="card-header">
                    <h2>
                        <i class="fas fa-hand-holding-usd"></i> 
                        Adelantos Registrados
                    </h2>
                    <button type="button" class="btn btn-sm btn-primary" onclick="abrirModalAgregarAdelanto()">
                        <i class="fas fa-plus"></i>
                        Agregar Adelanto
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($factura->adelantos)): ?>
                        <div class="empty-state-small">
                            <i class="fas fa-hand-holding-usd"></i>
                            <p>No hay adelantos registrados</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive adelantos">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Fecha</th>
                                        <th>Monto</th>
                                        <th>Descripción</th>
                                        <th>Registrado por</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $num = 1; ?>
                                    <?php foreach ($factura->adelantos as $adelanto): ?>
                                    <tr>
                                        <td><?php echo $num++; ?></td>
                                        <td><?php echo formatDate($adelanto->fecha); ?></td>
                                        <td><strong class="text-warning"><?php echo formatMoney($adelanto->monto); ?></strong></td>
                                        <td><?php echo e($adelanto->descripcion ?: '-'); ?></td>
                                        <td><?php echo e($adelanto->usuario_nombre ?? 'N/A'); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" 
                                                        class="btn btn-sm btn-warning" 
                                                        onclick="editarAdelanto(<?php echo $adelanto->id_factura_adelanto; ?>, <?php echo $adelanto->monto; ?>, '<?php echo $adelanto->fecha; ?>', '<?php echo addslashes($adelanto->descripcion); ?>')"
                                                        title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="eliminarAdelanto(<?php echo $adelanto->id_factura_adelanto; ?>)"
                                                        title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <strong>TOTAL ADELANTOS: </strong>
                                            <strong class="text-warning"><?php echo formatMoney($factura->adelanto); ?></strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <?php if ($totalPagesAdelantos > 1): ?>
                        <div class="pagination-wrapper" style="margin-top: 15px;">
                            <div class="pagination-info">
                                Mostrando <?php echo (($pageAdelantos - 1) * $perPageAdelantos) + 1; ?> - 
                                <?php echo min($pageAdelantos * $perPageAdelantos, $factura->total_adelantos); ?> 
                                de <?php echo $factura->total_adelantos; ?> adelantos
                            </div>
                                                
                            <div class="pagination">
                                <?php if ($pageAdelantos > 1): ?>
                                    <a href="<?php echo url('facturas/editar/' . $factura->id_factura . '?page_adelantos=' . ($pageAdelantos - 1)); ?>" 
                                       class="pagination-link">
                                        <i class="fas fa-chevron-left"></i>
                                        Anterior
                                    </a>
                                <?php endif; ?>
                                
                                <?php
                                $startPage = max(1, $pageAdelantos - 2);
                                $endPage = min($totalPagesAdelantos, $pageAdelantos + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <a href="<?php echo url('facturas/editar/' . $factura->id_factura . '?page_adelantos=' . $i); ?>" 
                                       class="pagination-link <?php echo $i === $pageAdelantos ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($pageAdelantos < $totalPagesAdelantos): ?>
                                    <a href="<?php echo url('facturas/editar/' . $factura->id_factura . '?page_adelantos=' . ($pageAdelantos + 1)); ?>" 
                                       class="pagination-link">
                                        Siguiente
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-calculator"></i> Resumen</h2>
                </div>
                <div class="card-body">
                    <div class="resumen-item">
                        <span class="resumen-label">Subtotal Productos:</span>
                        <span class="resumen-value" id="subtotalDisplay">Bs 0.00</span>
                    </div>
                    
                    <div class="resumen-item resumen-total">
                        <span class="resumen-label">TOTAL PRODUCTOS:</span>
                        <span class="resumen-value" id="totalDisplay">Bs 0.00</span>
                    </div>
                    
                    <hr>
                    
                    <div class="resumen-item">
                        <span class="resumen-label">Adelantos Actuales:</span>
                        <span class="resumen-value text-warning"><?php echo formatMoney($factura->adelanto); ?></span>
                    </div>
                    
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Los adelantos se editan desde la vista de detalle
                    </small>
                    
                    <hr>
                    
                    <div class="resumen-item">
                        <span class="resumen-label">DEUDA TOTAL:</span>
                        <span class="resumen-value text-danger" id="saldoDisplay">Bs <?php echo number_format($factura->saldo, 2); ?></span>
                    </div>
                    
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Se recalculará al guardar cambios
                    </small>
                    
                    <hr>
                    
                    <button type="submit" class="btn btn-success btn-block btn-lg" id="btnGuardar">
                        <i class="fas fa-save"></i>
                        Guardar Cambios
                    </button>
                    
                    <a href="<?php echo url('facturas/ver/' . $factura->id_factura); ?>" class="btn btn-secondary btn-block">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- MODAL BUSCAR CLIENTES -->
<div id="modalClientes" class="modal-facturas">
    <div class="modal-facturas-content">
        <div class="modal-facturas-header">
            <h3><i class="fas fa-users"></i> Seleccionar Cliente</h3>
            <button type="button" class="modal-facturas-close" onclick="cerrarModalClientes()">&times;</button>
        </div>
        <div class="modal-facturas-search">
            <input 
                type="text" 
                id="searchCliente" 
                class="modal-search-input" 
                placeholder="Buscar por CI, nombre o comunidad..."
                onkeyup="buscarClientes()">
        </div>
        <div class="modal-facturas-body">
            <table class="modal-table">
                <thead>
                    <tr>
                        <th>CI</th>
                        <th>Nombre Completo</th>
                        <th>Comunidad</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="clientesTableBody">
                    <tr>
                        <td colspan="4" class="text-center">Cargando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL BUSCAR Y AGREGAR PRODUCTOS -->
<div id="modalProductos" class="modal-facturas">
    <div class="modal-facturas-content">
        <div class="modal-facturas-header">
            <h3><i class="fas fa-box"></i> Seleccionar Producto</h3>
            <button type="button" class="modal-facturas-close" onclick="cerrarModalProductos()">&times;</button>
        </div>
        <div class="modal-facturas-search">
            <input 
                type="text" 
                id="searchProducto" 
                class="modal-search-input" 
                placeholder="Buscar por código o nombre..."
                onkeyup="buscarProductos()">
        </div>
        <div class="modal-facturas-body">
            <table class="modal-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Stock</th>
                        <th>Precio</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="productosTableBody">
                    <tr>
                        <td colspan="5" class="text-center">Cargando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL AGREGAR CANTIDAD -->
<div id="modalCantidad" class="modal-facturas">
    <div class="modal-facturas-content" style="max-width: 500px;">
        <div class="modal-facturas-header">
            <h3><i class="fas fa-plus-circle"></i> Agregar Producto</h3>
            <button type="button" class="modal-facturas-close" onclick="cerrarModalCantidad()">&times;</button>
        </div>
        <div class="modal-facturas-body">
            <div class="form-group">
                <label><strong>Producto:</strong></label>
                <p id="producto_seleccionado_nombre" style="font-size: 16px; color: #433F4E;"></p>
            </div>
            
            <div class="form-group">
                <label><strong>Stock Disponible:</strong></label>
                <p id="producto_seleccionado_stock" style="font-size: 16px; color: #28a745;"></p>
            </div>
            
            <div class="form-group">
                <label for="modal_cantidad">
                    <i class="fas fa-boxes"></i>
                    Cantidad
                    <span class="text-danger">*</span>
                </label>
                <input 
                    type="number" 
                    id="modal_cantidad" 
                    class="form-control" 
                    step="0.01"
                    min="0.01"
                    placeholder="Ej: 10.50"
                    autofocus>
            </div>
            
            <div class="form-group">
                <label for="modal_precio">
                    <i class="fas fa-dollar-sign"></i>
                    Precio Unitario (Bs)
                    <span class="text-danger">*</span>
                </label>
                <input 
                    type="number" 
                    id="modal_precio" 
                    class="form-control" 
                    step="0.01"
                    min="0.01"
                    placeholder="Ej: 25.00">
            </div>
            
            <input type="hidden" id="producto_temp_id">
            <input type="hidden" id="producto_temp_nombre">
            <input type="hidden" id="producto_temp_stock">
            <input type="hidden" id="producto_temp_unidad">
            <input type="hidden" id="producto_temp_ilimitado">
            
            <div class="form-actions" style="margin-top: 20px;">
                <button type="button" class="btn btn-success btn-block" onclick="confirmarAgregarProducto()">
                    <i class="fas fa-check"></i>
                    Agregar a Factura
                </button>
                <button type="button" class="btn btn-secondary btn-block" onclick="cerrarModalCantidad()">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Cargar productos existentes al cargar la página
window.addEventListener('DOMContentLoaded', function() {
    const detallesOriginales = <?php echo json_encode($factura->detalles); ?>;
    
    detallesOriginales.forEach(detalle => {
        facturasDetalles.push({
            producto_id: parseInt(detalle.producto_id),
            nombre: detalle.producto_nombre,
            cantidad: parseFloat(detalle.cantidad),
            precio_unitario: parseFloat(detalle.precio_unitario),
            unidad: detalle.unidad_codigo || '',
            subtotal: parseFloat(detalle.subtotal)
        });
    });
    
    actualizarTablaProductos();
    actualizarTotalesFactura();
});
</script>

<!-- MODAL AGREGAR ADELANTO -->
<div id="modalAgregarAdelanto" class="modal-facturas" style="display: none;">
    <div class="modal-facturas-content" style="max-width: 500px;">
        <div class="modal-facturas-header">
            <h3><i class="fas fa-plus"></i> Agregar Adelanto</h3>
            <button type="button" class="modal-facturas-close" onclick="cerrarModalAgregarAdelanto()">&times;</button>
        </div>
        <div class="modal-facturas-body">
            <form id="formAgregarAdelanto">
                <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                
                <div class="form-group">
                    <label for="adelanto_monto">
                        <i class="fas fa-dollar-sign"></i>
                        Monto (Bs)
                        <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="adelanto_monto" 
                        name="monto" 
                        class="form-control" 
                        step="0.01"
                        min="0.01"
                        placeholder="Ej: 500.00"
                        required>
                </div>
                
                <div class="form-group">
                    <label for="adelanto_fecha">
                        <i class="fas fa-calendar"></i>
                        Fecha
                        <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="adelanto_fecha" 
                        name="fecha" 
                        class="form-control" 
                        value="<?php echo date('Y-m-d'); ?>"
                        required>
                </div>
                
                <div class="form-group">
                    <label for="adelanto_descripcion_modal">
                        <i class="fas fa-comment"></i>
                        Descripción
                    </label>
                    <textarea 
                        id="adelanto_descripcion_modal" 
                        name="descripcion" 
                        class="form-control" 
                        rows="3"
                        placeholder="Ej: Adelanto para acopio de quinua"></textarea>
                </div>
                
                <div class="form-actions" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-save"></i>
                        Guardar Adelanto
                    </button>
                    <button type="button" class="btn btn-secondary btn-block" onclick="cerrarModalAgregarAdelanto()">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDITAR ADELANTO -->
<div id="modalEditarAdelanto" class="modal-facturas" style="display: none;">
    <div class="modal-facturas-content" style="max-width: 500px;">
        <div class="modal-facturas-header">
            <h3><i class="fas fa-edit"></i> Editar Adelanto</h3>
            <button type="button" class="modal-facturas-close" onclick="cerrarModalEditarAdelanto()">&times;</button>
        </div>
        <div class="modal-facturas-body">
            <form id="formEditarAdelanto">
                <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                <input type="hidden" id="editar_adelanto_id">
                
                <div class="form-group">
                    <label for="editar_adelanto_monto">
                        <i class="fas fa-dollar-sign"></i>
                        Monto (Bs)
                        <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="editar_adelanto_monto" 
                        name="monto" 
                        class="form-control" 
                        step="0.01"
                        min="0.01"
                        required>
                </div>
                
                <div class="form-group">
                    <label for="editar_adelanto_fecha">
                        <i class="fas fa-calendar"></i>
                        Fecha
                        <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="editar_adelanto_fecha" 
                        name="fecha" 
                        class="form-control" 
                        required>
                </div>
                
                <div class="form-group">
                    <label for="editar_adelanto_descripcion">
                        <i class="fas fa-comment"></i>
                        Descripción
                    </label>
                    <textarea 
                        id="editar_adelanto_descripcion" 
                        name="descripcion" 
                        class="form-control" 
                        rows="3"></textarea>
                </div>
                
                <div class="form-actions" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-save"></i>
                        Guardar Cambios
                    </button>
                    <button type="button" class="btn btn-secondary btn-block" onclick="cerrarModalEditarAdelanto()">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php endif; ?>