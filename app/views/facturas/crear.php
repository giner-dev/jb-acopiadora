<div class="page-header">
    <div>
        <h1><i class="fas fa-file-invoice-dollar"></i> Nueva Factura</h1>
        <p>Registra una nueva factura de venta</p>
    </div>
    <div>
        <a href="<?php echo url('facturas'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<form action="<?php echo url('facturas/guardar'); ?>" method="POST" id="formFactura">
    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
    <input type="hidden" name="detalles_json" id="detalles_json">
    <input type="hidden" name="cliente_id" id="cliente_id_hidden">
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-info-circle"></i> Datos de la Factura</h2>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="codigo_manual">
                                <i class="fas fa-barcode"></i>
                                Número de Factura (Opcional)
                            </label>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input 
                                    type="text" 
                                    id="codigo_manual" 
                                    name="codigo_manual" 
                                    class="form-control" 
                                    placeholder="<?php echo $proximoNumero; ?>"
                                    style="flex: 1;"
                                    pattern="[0-9]*"
                                    title="Solo números">
                            </div>
                            <small class="text-muted">
                                Ingresa solo el número (ej: <?php echo $proximoNumero; ?> o <?php echo $proximoNumero + 1; ?>). 
                                Se generará automáticamente: FAC<?php echo str_pad($proximoNumero, 6, '0', STR_PAD_LEFT); ?>
                            </small>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="cliente_btn">
                                <i class="fas fa-user"></i>
                                Cliente
                                <span class="text-danger">*</span>
                            </label>
                            <button type="button" class="btn btn-secondary btn-block" onclick="abrirModalClientes()">
                                <i class="fas fa-search"></i>
                                Buscar Cliente
                            </button>
                            <div id="cliente_display">
                                <span id="cliente_seleccionado"></span>
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
                                value="<?php echo date('Y-m-d'); ?>"
                                required>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-box"></i> Productos</h2>
                </div>
                <div class="card-body facturas">
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
                                        No hay productos agregados
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
                    <div class="resumen-item">
                        <span class="resumen-label">Subtotal:</span>
                        <span class="resumen-value" id="subtotalDisplay">Bs 0.00</span>
                    </div>
                    
                    <div class="resumen-item resumen-total">
                        <span class="resumen-label">TOTAL:</span>
                        <span class="resumen-value" id="totalDisplay">Bs 0.00</span>
                    </div>
                    
                    <hr>
                    
                    <div class="form-group">
                        <label for="adelanto">
                            <i class="fas fa-money-bill-wave"></i>
                            Adelanto / Pago Parcial
                        </label>
                        <input 
                            type="number" 
                            id="adelanto" 
                            name="adelanto" 
                            class="form-control" 
                            step="0.01"
                            min="0"
                            value="0"
                            placeholder="0.00">
                        <small>Si el adelanto es igual o mayor al total, la factura se marcará como PAGADA</small>
                    </div>
                    
                    <div class="resumen-item">
                        <span class="resumen-label">Saldo Pendiente:</span>
                        <span class="resumen-value text-danger" id="saldoDisplay">Bs 0.00</span>
                    </div>
                    
                    <hr>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-lg" id="btnGuardar">
                        <i class="fas fa-save"></i>
                        Guardar Factura
                    </button>
                    
                    <a href="<?php echo url('facturas'); ?>" class="btn btn-secondary btn-block">
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