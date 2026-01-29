// ============================================
// MÓDULO DE FACTURAS - AISLADO
// ============================================

// Variables globales del módulo (solo para facturas)
let facturasDetalles = [];
let facturaIdParaAnular = null;

// Inicialización solo si estamos en la página de facturas
document.addEventListener('DOMContentLoaded', function() {
    const formFactura = document.getElementById('formFactura');
    if (formFactura) {
        console.log('Módulo de facturas iniciado');
        initFacturasModule();
    }
});

function initFacturasModule() {
    const formFactura = document.getElementById('formFactura');
    
    if (formFactura) {
        console.log('Formulario encontrado, inicializando');
        initFormCrear();
    }
    
    const adelantoInput = document.getElementById('adelanto');
    if (adelantoInput) {
        adelantoInput.addEventListener('input', calcularSaldoFactura);
    }
    
    const modalCantidadInput = document.getElementById('modal_cantidad');
    if (modalCantidadInput) {
        modalCantidadInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('modal_precio').focus();
            }
        });
    }
    
    const modalPrecioInput = document.getElementById('modal_precio');
    if (modalPrecioInput) {
        modalPrecioInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmarAgregarProducto();
            }
        });
    }
}

function initFormCrear() {
    const form = document.getElementById('formFactura');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!validarCodigoFactura()) {
            return false;
        }
        
        console.log('=== SUBMIT DEL FORMULARIO ===');
        console.log('Detalles actuales:', facturasDetalles);
        console.log('Cantidad de productos:', facturasDetalles.length);
        
        if (facturasDetalles.length === 0) {
            if (typeof showNotification === 'function') {
                showNotification('Debe agregar al menos un producto', 'error');
            } else {
                alert('Debe agregar al menos un producto');
            }
            return false;
        }
        
        const clienteIdInput = document.getElementById('cliente_id_hidden');
        console.log('Cliente ID value:', clienteIdInput ? clienteIdInput.value : 'NO ENCONTRADO');
        
        if (!clienteIdInput || !clienteIdInput.value) {
            if (typeof showNotification === 'function') {
                showNotification('Debe seleccionar un cliente', 'error');
            } else {
                alert('Debe seleccionar un cliente');
            }
            return false;
        }
        
        // Limpiar los datos antes de convertir a JSON
        const detallesLimpios = facturasDetalles.map(detalle => ({
            producto_id: parseInt(detalle.producto_id),
            nombre: String(detalle.nombre).trim(),
            cantidad: parseFloat(detalle.cantidad),
            precio_unitario: parseFloat(detalle.precio_unitario),
            unidad: String(detalle.unidad || '').trim(),
            subtotal: parseFloat(detalle.subtotal)
        }));
        
        console.log('Detalles limpios:', detallesLimpios);
        
        try {
            const detallesJson = JSON.stringify(detallesLimpios);
            console.log('JSON generado:', detallesJson);
            console.log('Longitud JSON:', detallesJson.length);
            
            // Validar que el JSON es válido parseándolo
            const testParse = JSON.parse(detallesJson);
            console.log('JSON validado correctamente:', testParse);
            
            const detallesJsonInput = document.getElementById('detalles_json');
            detallesJsonInput.value = detallesJson;
            
            console.log('Valor asignado al input hidden:', detallesJsonInput.value);
            
            const formData = new FormData(form);
            console.log('=== DATOS DEL FORMULARIO ===');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + (pair[0] === 'detalles_json' ? pair[1].substring(0, 100) + '...' : pair[1]));
            }
            
            console.log('Enviando formulario...');
            form.submit();
            
        } catch (error) {
            console.error('Error al generar JSON:', error);
            if (typeof showNotification === 'function') {
                showNotification('Error al preparar los datos: ' + error.message, 'error');
            } else {
                alert('Error al preparar los datos: ' + error.message);
            }
            return false;
        }
    });
}

function validarCodigoFactura() {
    const inputCodigo = document.getElementById('codigo_manual');
    if (!inputCodigo) return true;
    
    const valor = inputCodigo.value.trim();
    
    // Si está vacío, está bien (se generará automático)
    if (valor === '') return true;
    
    // Remover "FAC" si el usuario lo escribió
    const numero = valor.replace(/^FAC/i, '');
    
    // Validar que sea numérico
    if (!/^\d+$/.test(numero)) {
        if (typeof showNotification === 'function') {
            showNotification('El número de factura debe contener solo dígitos', 'error');
        }
        inputCodigo.focus();
        return false;
    }
    
    // Validar que sea positivo
    if (parseInt(numero) <= 0) {
        if (typeof showNotification === 'function') {
            showNotification('El número de factura debe ser mayor a 0', 'error');
        }
        inputCodigo.focus();
        return false;
    }
    
    return true;
}

function abrirModalClientes() {
    document.getElementById('modalClientes').style.display = 'flex';
    document.getElementById('searchCliente').focus();
    buscarClientes();
}

function cerrarModalClientes() {
    document.getElementById('modalClientes').style.display = 'none';
}

function buscarClientes() {
    const search = document.getElementById('searchCliente').value;
    const url = window.PHP_BASE_URL + '/facturas/buscar-clientes?search=' + encodeURIComponent(search);
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('clientesTableBody');
            tbody.innerHTML = '';
            
            if (data.clientes && data.clientes.length > 0) {
                data.clientes.forEach(cliente => {
                    const nombreCompleto = cliente.nombres + ' ' + cliente.apellidos;
                    
                    const tr = document.createElement('tr');
                    const tdCI = document.createElement('td');
                    tdCI.textContent = cliente.ci;
                    
                    const tdNombre = document.createElement('td');
                    tdNombre.textContent = nombreCompleto;
                    
                    const tdComunidad = document.createElement('td');
                    tdComunidad.textContent = cliente.comunidad || '-';
                    
                    const tdAccion = document.createElement('td');
                    const btnSeleccionar = document.createElement('button');
                    btnSeleccionar.type = 'button';
                    btnSeleccionar.className = 'btn btn-sm btn-primary';
                    btnSeleccionar.innerHTML = '<i class="fas fa-check"></i> Seleccionar';
                    btnSeleccionar.onclick = function() {
                        seleccionarCliente(cliente.id_cliente, nombreCompleto, cliente.ci);
                    };
                    tdAccion.appendChild(btnSeleccionar);
                    
                    tr.appendChild(tdCI);
                    tr.appendChild(tdNombre);
                    tr.appendChild(tdComunidad);
                    tr.appendChild(tdAccion);
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">No se encontraron clientes</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error al buscar clientes:', error);
            if (typeof showNotification === 'function') {
                showNotification('Error al buscar clientes', 'error');
            }
        });
}

function seleccionarCliente(id, nombre, ci) {
    console.log('Cliente seleccionado:', id, nombre, ci);
    document.getElementById('cliente_id_hidden').value = id;
    document.getElementById('cliente_seleccionado').textContent = nombre + ' - CI: ' + ci;
    document.getElementById('cliente_display').style.display = 'block';
    cerrarModalClientes();
}

function limpiarCliente() {
    document.getElementById('cliente_id_hidden').value = '';
    document.getElementById('cliente_seleccionado').textContent = '';
    document.getElementById('cliente_display').style.display = 'none';
}

function abrirModalProductos() {
    document.getElementById('modalProductos').style.display = 'flex';
    document.getElementById('searchProducto').focus();
    buscarProductos();
}

function cerrarModalProductos() {
    document.getElementById('modalProductos').style.display = 'none';
}

function buscarProductos() {
    const search = document.getElementById('searchProducto').value;
    const url = window.PHP_BASE_URL + '/facturas/buscar-productos?search=' + encodeURIComponent(search);
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('productosTableBody');
            tbody.innerHTML = '';
            
            if (data.productos && data.productos.length > 0) {
                data.productos.forEach(producto => {
                    const yaAgregado = facturasDetalles.find(d => d.producto_id == producto.id_producto);
                    
                    const tr = document.createElement('tr');
                    
                    const tdCodigo = document.createElement('td');
                    tdCodigo.textContent = producto.codigo || '-';
                    
                    const tdNombre = document.createElement('td');
                    tdNombre.innerHTML = '<strong>' + producto.nombre + '</strong>';
                    
                    const tdStock = document.createElement('td');
                    if (producto.stock_ilimitado == 1) {
                        tdStock.innerHTML = '<span style="color: #0c5460; font-weight: bold;"><i class="fas fa-infinity"></i> Ilimitado</span>';
                    } else {
                        tdStock.textContent = parseFloat(producto.stock_actual).toFixed(2) + ' ' + (producto.unidad_codigo || '');
                    }
                    
                    const tdPrecio = document.createElement('td');
                    tdPrecio.textContent = 'Bs ' + parseFloat(producto.precio_venta).toFixed(2);
                    
                    const tdAccion = document.createElement('td');
                    if (yaAgregado) {
                        const btnAgregarOtraVez = document.createElement('button');
                        btnAgregarOtraVez.type = 'button';
                        btnAgregarOtraVez.className = 'btn btn-sm btn-warning';
                        btnAgregarOtraVez.innerHTML = '<i class="fas fa-plus"></i> Agregar otra vez';
                        btnAgregarOtraVez.onclick = function() {
                            seleccionarProducto(producto);
                        };
                        tdAccion.appendChild(btnAgregarOtraVez);
                    } else {
                        const btnAgregar = document.createElement('button');
                        btnAgregar.type = 'button';
                        btnAgregar.className = 'btn btn-sm btn-primary';
                        btnAgregar.innerHTML = '<i class="fas fa-plus"></i> Agregar';
                        btnAgregar.onclick = function() {
                            seleccionarProducto(producto);
                        };
                        tdAccion.appendChild(btnAgregar);
                    }
                    
                    tr.appendChild(tdCodigo);
                    tr.appendChild(tdNombre);
                    tr.appendChild(tdStock);
                    tr.appendChild(tdPrecio);
                    tr.appendChild(tdAccion);
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No se encontraron productos</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error al buscar productos:', error);
            if (typeof showNotification === 'function') {
                showNotification('Error al buscar productos', 'error');
            }
        });
}

function seleccionarProducto(producto) {
    console.log('Producto seleccionado:', producto);
    cerrarModalProductos();
    
    document.getElementById('producto_temp_id').value = producto.id_producto;
    document.getElementById('producto_temp_nombre').value = producto.nombre;
    document.getElementById('producto_temp_stock').value = producto.stock_actual;
    document.getElementById('producto_temp_unidad').value = producto.unidad_codigo || '';
    
    document.getElementById('producto_temp_ilimitado').value = producto.stock_ilimitado || 0;
    
    document.getElementById('producto_seleccionado_nombre').textContent = producto.nombre;
    
    if (producto.stock_ilimitado == 1) {
        document.getElementById('producto_seleccionado_stock').innerHTML = '<span style="color: #0c5460; font-weight: bold;"><i class="fas fa-infinity"></i> Stock Ilimitado</span>';
    } else {
        document.getElementById('producto_seleccionado_stock').textContent = parseFloat(producto.stock_actual).toFixed(2) + ' ' + (producto.unidad_codigo || 'unidades');
    }
    
    document.getElementById('modal_cantidad').value = '';
    document.getElementById('modal_precio').value = parseFloat(producto.precio_venta).toFixed(2);
    
    document.getElementById('modalCantidad').style.display = 'flex';
    
    setTimeout(() => {
        document.getElementById('modal_cantidad').focus();
    }, 100);
}

function cerrarModalCantidad() {
    document.getElementById('modalCantidad').style.display = 'none';
}

function confirmarAgregarProducto() {
    const productoId = parseInt(document.getElementById('producto_temp_id').value);
    const productoNombre = document.getElementById('producto_temp_nombre').value;
    const cantidad = parseFloat(document.getElementById('modal_cantidad').value);
    const precioUnitario = parseFloat(document.getElementById('modal_precio').value);
    const stock = parseFloat(document.getElementById('producto_temp_stock').value);
    const unidad = document.getElementById('producto_temp_unidad').value;
    const stockIlimitado = parseInt(document.getElementById('producto_temp_ilimitado').value);
    
    console.log('=== AGREGANDO PRODUCTO ===');
    console.log('ID:', productoId, 'Nombre:', productoNombre);
    console.log('Cantidad:', cantidad, 'Precio:', precioUnitario);
    console.log('Stock ilimitado:', stockIlimitado);
    
    if (!cantidad || cantidad <= 0 || isNaN(cantidad)) {
        if (typeof showNotification === 'function') {
            showNotification('La cantidad debe ser mayor a 0', 'error');
        } else {
            alert('La cantidad debe ser mayor a 0');
        }
        document.getElementById('modal_cantidad').focus();
        return;
    }
    
    if (!precioUnitario || precioUnitario <= 0 || isNaN(precioUnitario)) {
        if (typeof showNotification === 'function') {
            showNotification('El precio debe ser mayor a 0', 'error');
        } else {
            alert('El precio debe ser mayor a 0');
        }
        document.getElementById('modal_precio').focus();
        return;
    }
    
    if (stockIlimitado != 1 && cantidad > stock) {
        if (typeof showNotification === 'function') {
            showNotification('Stock insuficiente. Stock disponible: ' + stock.toFixed(2), 'error');
        } else {
            alert('Stock insuficiente. Stock disponible: ' + stock.toFixed(2));
        }
        document.getElementById('modal_cantidad').focus();
        return;
    }
    
    const nombreLimpio = String(productoNombre)
        .replace(/[\u0000-\u001F\u007F-\u009F]/g, '')
        .replace(/['"]/g, '')
        .trim();
    
    const unidadLimpia = String(unidad || '')
        .replace(/[\u0000-\u001F\u007F-\u009F]/g, '')
        .trim();
    
    const detalle = {
        producto_id: productoId,
        nombre: nombreLimpio,
        cantidad: parseFloat(cantidad.toFixed(2)),
        precio_unitario: parseFloat(precioUnitario.toFixed(2)),
        unidad: unidadLimpia,
        subtotal: parseFloat((cantidad * precioUnitario).toFixed(2))
    };
    
    console.log('Detalle sanitizado a agregar:', detalle);
    
    facturasDetalles.push(detalle);
    
    console.log('Producto agregado exitosamente');
    console.log('Detalles actualizados:', facturasDetalles);
    
    actualizarTablaProductos();
    actualizarTotalesFactura();
    cerrarModalCantidad();
    
    if (typeof showNotification === 'function') {
        showNotification('Producto agregado correctamente', 'success');
    }
}

function eliminarProducto(index) {
    if (confirm('¿Está seguro de eliminar este producto?')) {
        console.log('Eliminando producto en índice:', index);
        facturasDetalles.splice(index, 1);
        console.log('Detalles después de eliminar:', facturasDetalles);
        actualizarTablaProductos();
        actualizarTotalesFactura();
        if (typeof showNotification === 'function') {
            showNotification('Producto eliminado', 'info');
        }
    }
}

function actualizarTablaProductos() {
    const tbody = document.getElementById('detallesBody');
    tbody.innerHTML = '';
    
    if (facturasDetalles.length === 0) {
        tbody.innerHTML = '<tr id="emptyRow"><td colspan="5" class="text-center text-muted">No hay productos agregados</td></tr>';
        return;
    }
    
    facturasDetalles.forEach((detalle, index) => {
        const tr = document.createElement('tr');
        
        const tdNombre = document.createElement('td');
        tdNombre.innerHTML = '<strong>' + detalle.nombre + '</strong>';
        
        const tdCantidad = document.createElement('td');
        tdCantidad.textContent = detalle.cantidad.toFixed(2) + ' ' + detalle.unidad;
        
        const tdPrecio = document.createElement('td');
        tdPrecio.textContent = 'Bs ' + detalle.precio_unitario.toFixed(2);
        
        const tdSubtotal = document.createElement('td');
        tdSubtotal.innerHTML = '<strong>Bs ' + detalle.subtotal.toFixed(2) + '</strong>';
        
        const tdAccion = document.createElement('td');
        const btnEliminar = document.createElement('button');
        btnEliminar.type = 'button';
        btnEliminar.className = 'btn btn-sm btn-danger';
        btnEliminar.title = 'Eliminar';
        btnEliminar.innerHTML = '<i class="fas fa-trash"></i>';
        btnEliminar.onclick = function() {
            eliminarProducto(index);
        };
        tdAccion.appendChild(btnEliminar);
        
        tr.appendChild(tdNombre);
        tr.appendChild(tdCantidad);
        tr.appendChild(tdPrecio);
        tr.appendChild(tdSubtotal);
        tr.appendChild(tdAccion);
        tbody.appendChild(tr);
    });
    
    console.log('Tabla actualizada con', facturasDetalles.length, 'productos');
}

function actualizarTotalesFactura() {
    let subtotal = 0;
    
    facturasDetalles.forEach(detalle => {
        subtotal += detalle.subtotal;
    });
    
    document.getElementById('subtotalDisplay').textContent = 'Bs ' + subtotal.toFixed(2);
    document.getElementById('totalDisplay').textContent = 'Bs ' + subtotal.toFixed(2);
    
    console.log('Totales actualizados. Subtotal:', subtotal.toFixed(2));
    
    calcularSaldoFactura();
}

function calcularSaldoFactura() {
    const subtotalText = document.getElementById('totalDisplay').textContent;
    const total = parseFloat(subtotalText.replace('Bs ', '').replace(',', ''));
    
    const adelantoInput = document.getElementById('adelanto');
    const adelanto = adelantoInput ? parseFloat(adelantoInput.value) || 0 : 0;
    
    const saldo = total - adelanto;
    
    const saldoDisplay = document.getElementById('saldoDisplay');
    if (saldoDisplay) {
        saldoDisplay.textContent = 'Bs ' + saldo.toFixed(2);
        
        if (saldo <= 0) {
            saldoDisplay.classList.remove('text-danger');
            saldoDisplay.classList.add('text-success');
        } else {
            saldoDisplay.classList.remove('text-success');
            saldoDisplay.classList.add('text-danger');
        }
    }
}

function anularFactura(id, codigo) {
    facturaIdParaAnular = id;
    document.getElementById('facturaCodigoAnular').textContent = codigo;
    document.getElementById('motivoAnulacion').value = '';
    
    const modal = document.getElementById('modalAnular');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function cerrarModalAnular() {
    const modal = document.getElementById('modalAnular');
    if (modal) {
        modal.style.display = 'none';
    }
    facturaIdParaAnular = null;
}
function confirmarAnulacion() {
    const motivo = document.getElementById('motivoAnulacion').value.trim();
    
    if (!motivo) {
        if (typeof showNotification === 'function') {
            showNotification('Debe especificar el motivo de anulación', 'error');
        } else {
            alert('Debe especificar el motivo de anulación');
        }
        return;
    }
    
    if (motivo.length < 10) {
        if (typeof showNotification === 'function') {
            showNotification('El motivo debe tener al menos 10 caracteres', 'error');
        } else {
            alert('El motivo debe tener al menos 10 caracteres');
        }
        return;
    }
    
    if (typeof showLoader === 'function') {
        showLoader();
    }
    
    const formData = new FormData();
    formData.append('motivo', motivo);
    
    fetch(window.PHP_BASE_URL + '/facturas/anular/' + facturaIdParaAnular, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (typeof hideLoader === 'function') {
            hideLoader();
        }
        cerrarModalAnular();
        
        if (data.success) {
            if (typeof showNotification === 'function') {
                showNotification(data.message, 'success');
            } else {
                alert(data.message);
            }
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            if (typeof showNotification === 'function') {
                showNotification(data.message, 'error');
            } else {
                alert(data.message);
            }
        }
    })
    .catch(error => {
        if (typeof hideLoader === 'function') {
            hideLoader();
        }
        cerrarModalAnular();
        if (typeof showNotification === 'function') {
            showNotification('Error al anular la factura', 'error');
        } else {
            alert('Error al anular la factura');
        }
        console.error('Error:', error);
    });
}

// Event listeners para cerrar modales al hacer click fuera
window.addEventListener('click', function(event) {
    const modalAnular = document.getElementById('modalAnular');
    if (modalAnular && event.target === modalAnular) {
        cerrarModalAnular();
    }
    
    const modalClientes = document.getElementById('modalClientes');
    if (modalClientes && event.target === modalClientes) {
        cerrarModalClientes();
    }
    
    const modalProductos = document.getElementById('modalProductos');
    if (modalProductos && event.target === modalProductos) {
        cerrarModalProductos();
    }
    
    const modalCantidad = document.getElementById('modalCantidad');
    if (modalCantidad && event.target === modalCantidad) {
        cerrarModalCantidad();
    }
});