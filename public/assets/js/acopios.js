const AcopioModule = (function() {
    let detalles = [];
    let idParaAnular = null;

    function init() {
        const formAcopio = document.getElementById('formAcopio');
        if (!formAcopio) return;
        
        console.log('Módulo de acopios iniciado');
        initFormulario();
        initEventListeners();
    }

    function initFormulario() {
        const form = document.getElementById('formAcopio');
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!validarCodigo()) return false;
            
            if (detalles.length === 0) {
                mostrarNotificacion('Debe agregar al menos un grano', 'error');
                return false;
            }
            
            const clienteIdInput = document.getElementById('acopio_cliente_id_hidden');
            if (!clienteIdInput || !clienteIdInput.value) {
                mostrarNotificacion('Debe seleccionar un cliente', 'error');
                return false;
            }
            
            const detallesLimpios = detalles.map(detalle => ({
                grano_id: parseInt(detalle.grano_id),
                nombre: String(detalle.nombre).trim(),
                cantidad: parseFloat(detalle.cantidad),
                precio_unitario: parseFloat(detalle.precio_unitario),
                unidad: String(detalle.unidad || '').trim(),
                subtotal: parseFloat(detalle.subtotal)
            }));
            
            try {
                const detallesJson = JSON.stringify(detallesLimpios);
                document.getElementById('acopio_detalles_json').value = detallesJson;
                form.submit();
            } catch (error) {
                console.error('Error al generar JSON:', error);
                mostrarNotificacion('Error al preparar los datos: ' + error.message, 'error');
                return false;
            }
        });
    }

    function initEventListeners() {
        const modalCantidad = document.getElementById('acopio_modal_cantidad');
        if (modalCantidad) {
            modalCantidad.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('acopio_modal_precio').focus();
                }
            });
        }
        
        const modalPrecio = document.getElementById('acopio_modal_precio');
        if (modalPrecio) {
            modalPrecio.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    confirmarAgregarGrano();
                }
            });
        }

        window.addEventListener('click', function(event) {
            cerrarModalSiClickFuera(event, 'acopio_modalAnular', cerrarModalAnular);
            cerrarModalSiClickFuera(event, 'acopio_modalClientes', cerrarModalClientes);
            cerrarModalSiClickFuera(event, 'acopio_modalGranos', cerrarModalGranos);
            cerrarModalSiClickFuera(event, 'acopio_modalCantidad', cerrarModalCantidad);
        });
    }

    function cerrarModalSiClickFuera(event, modalId, cerrarFn) {
        const modal = document.getElementById(modalId);
        if (modal && event.target === modal) {
            cerrarFn();
        }
    }

    function validarCodigo() {
        const inputCodigo = document.getElementById('acopio_codigo_manual');
        if (!inputCodigo) return true;
        
        const valor = inputCodigo.value.trim();
        if (valor === '') return true;
        
        const numero = valor.replace(/^ACO/i, '');
        
        if (!/^\d+$/.test(numero)) {
            mostrarNotificacion('El número de acopio debe contener solo dígitos', 'error');
            inputCodigo.focus();
            return false;
        }
        
        if (parseInt(numero) <= 0) {
            mostrarNotificacion('El número de acopio debe ser mayor a 0', 'error');
            inputCodigo.focus();
            return false;
        }
        
        return true;
    }

    function abrirModalClientes() {
        document.getElementById('acopio_modalClientes').style.display = 'flex';
        document.getElementById('acopio_searchCliente').focus();
        buscarClientes();
    }

    function cerrarModalClientes() {
        document.getElementById('acopio_modalClientes').style.display = 'none';
    }

    function buscarClientes() {
        const search = document.getElementById('acopio_searchCliente').value;
        const url = window.PHP_BASE_URL + '/acopios/buscar-clientes?search=' + encodeURIComponent(search);
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('acopio_clientesTableBody');
                tbody.innerHTML = '';
                
                if (data.clientes && data.clientes.length > 0) {
                    data.clientes.forEach(cliente => {
                        const nombreCompleto = cliente.nombres + ' ' + cliente.apellidos;
                        
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${cliente.ci}</td>
                            <td>${nombreCompleto}</td>
                            <td>${cliente.comunidad || '-'}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary" onclick="AcopioModule.seleccionarCliente(${cliente.id_cliente}, '${nombreCompleto}', '${cliente.ci}')">
                                    <i class="fas fa-check"></i> Seleccionar
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No se encontraron clientes</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error al buscar clientes:', error);
                mostrarNotificacion('Error al buscar clientes', 'error');
            });
    }

    function seleccionarCliente(id, nombre, ci) {
        document.getElementById('acopio_cliente_id_hidden').value = id;
        document.getElementById('acopio_cliente_seleccionado').textContent = nombre + ' - CI: ' + ci;
        document.getElementById('acopio_cliente_display').style.display = 'block';
        cerrarModalClientes();
    }

    function limpiarCliente() {
        document.getElementById('acopio_cliente_id_hidden').value = '';
        document.getElementById('acopio_cliente_seleccionado').textContent = '';
        document.getElementById('acopio_cliente_display').style.display = 'none';
    }

    function abrirModalGranos() {
        document.getElementById('acopio_modalGranos').style.display = 'flex';
        document.getElementById('acopio_searchGrano').focus();
        buscarGranos();
    }

    function cerrarModalGranos() {
        document.getElementById('acopio_modalGranos').style.display = 'none';
    }

    function buscarGranos() {
        const search = document.getElementById('acopio_searchGrano').value;
        const url = window.PHP_BASE_URL + '/acopios/buscar-granos?search=' + encodeURIComponent(search);
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('acopio_granosTableBody');
                tbody.innerHTML = '';
                
                if (data.granos && data.granos.length > 0) {
                    data.granos.forEach(grano => {
                        const yaAgregado = detalles.find(d => d.grano_id == grano.id_grano);
                        
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td><strong>${grano.nombre}</strong></td>
                            <td>${grano.precio_actual && parseFloat(grano.precio_actual) > 0 ? '<span style="color: #28a745; font-weight: bold;">Bs ' + parseFloat(grano.precio_actual).toFixed(2) + '</span>' : '<span style="color: #dc3545;">Sin precio</span>'}</td>
                            <td>${formatearVigenciaPrecio(grano.fecha_precio)}</td>
                            <td>
                                <button type="button" class="btn btn-sm ${yaAgregado ? 'btn-warning' : 'btn-primary'}" onclick='AcopioModule.seleccionarGrano(${JSON.stringify(grano)})'>
                                    <i class="fas fa-plus"></i> ${yaAgregado ? 'Agregar otra vez' : 'Agregar'}
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No se encontraron granos</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error al buscar granos:', error);
                mostrarNotificacion('Error al buscar granos', 'error');
            });
    }

    function formatearVigenciaPrecio(fechaPrecio) {
        if (!fechaPrecio) return '<small class="text-muted">-</small>';
        
        const fecha = new Date(fechaPrecio);
        const hoy = new Date();
        const diffDays = Math.floor((hoy - fecha) / (1000 * 60 * 60 * 24));
        
        if (diffDays === 0) return '<small class="text-success">Hoy</small>';
        if (diffDays === 1) return '<small class="text-muted">Hace 1 día</small>';
        return '<small class="text-muted">Hace ' + diffDays + ' días</small>';
    }

    function seleccionarGrano(grano) {
        cerrarModalGranos();
        
        document.getElementById('acopio_grano_temp_id').value = grano.id_grano;
        document.getElementById('acopio_grano_temp_nombre').value = grano.nombre;
        document.getElementById('acopio_grano_temp_precio_original').value = grano.precio_actual || 0;
        document.getElementById('acopio_grano_temp_unidad').value = grano.unidad_codigo || '';
        
        document.getElementById('acopio_grano_seleccionado_nombre').textContent = grano.nombre;
        
        if (grano.precio_actual && parseFloat(grano.precio_actual) > 0) {
            document.getElementById('acopio_grano_seleccionado_precio').innerHTML = '<span style="color: #28a745;">Bs ' + parseFloat(grano.precio_actual).toFixed(2) + '</span>';
            document.getElementById('acopio_modal_precio').value = parseFloat(grano.precio_actual).toFixed(2);
        } else {
            document.getElementById('acopio_grano_seleccionado_precio').innerHTML = '<span style="color: #dc3545;">Sin precio registrado</span>';
            document.getElementById('acopio_modal_precio').value = '';
        }
        
        document.getElementById('acopio_modal_cantidad').value = '';
        document.getElementById('acopio_modalCantidad').style.display = 'flex';
        
        setTimeout(() => {
            document.getElementById('acopio_modal_cantidad').focus();
        }, 100);
    }

    function cerrarModalCantidad() {
        document.getElementById('acopio_modalCantidad').style.display = 'none';
    }

    function confirmarAgregarGrano() {
        const granoId = parseInt(document.getElementById('acopio_grano_temp_id').value);
        const granoNombre = document.getElementById('acopio_grano_temp_nombre').value;
        const cantidad = parseFloat(document.getElementById('acopio_modal_cantidad').value);
        const precioUnitario = parseFloat(document.getElementById('acopio_modal_precio').value);
        const precioOriginal = parseFloat(document.getElementById('acopio_grano_temp_precio_original').value);
        const unidad = document.getElementById('acopio_grano_temp_unidad').value;
        
        if (!cantidad || cantidad <= 0 || isNaN(cantidad)) {
            mostrarNotificacion('La cantidad debe ser mayor a 0', 'error');
            document.getElementById('acopio_modal_cantidad').focus();
            return;
        }
        
        if (!precioUnitario || precioUnitario <= 0 || isNaN(precioUnitario)) {
            mostrarNotificacion('El precio debe ser mayor a 0', 'error');
            document.getElementById('acopio_modal_precio').focus();
            return;
        }
        
        if (precioUnitario !== precioOriginal) {
            actualizarPrecioGrano(granoId, precioUnitario);
        }
        
        const detalle = {
            grano_id: granoId,
            nombre: String(granoNombre).replace(/[\u0000-\u001F\u007F-\u009F]/g, '').replace(/['"]/g, '').trim(),
            cantidad: parseFloat(cantidad.toFixed(2)),
            precio_unitario: parseFloat(precioUnitario.toFixed(2)),
            unidad: String(unidad || '').replace(/[\u0000-\u001F\u007F-\u009F]/g, '').trim(),
            subtotal: parseFloat((cantidad * precioUnitario).toFixed(2))
        };
        
        detalles.push(detalle);
        actualizarTablaGranos();
        actualizarTotales();
        cerrarModalCantidad();
        mostrarNotificacion('Grano agregado correctamente', 'success');
    }

    function actualizarPrecioGrano(granoId, precio) {
        const formData = new FormData();
        formData.append('grano_id', granoId);
        formData.append('precio', precio);
        formData.append('fecha', new Date().toISOString().split('T')[0]);
        
        fetch(window.PHP_BASE_URL + '/acopios/actualizar-precio-grano', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Precio actualizado en BD');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function eliminarGrano(index) {
        if (confirm('¿Está seguro de eliminar este grano?')) {
            detalles.splice(index, 1);
            actualizarTablaGranos();
            actualizarTotales();
            mostrarNotificacion('Grano eliminado', 'info');
        }
    }

    function actualizarTablaGranos() {
        const tbody = document.getElementById('acopio_detallesBody');
        tbody.innerHTML = '';
        
        if (detalles.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay granos agregados</td></tr>';
            return;
        }
        
        detalles.forEach((detalle, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>${detalle.nombre}</strong></td>
                <td>${detalle.cantidad.toFixed(2)} ${detalle.unidad}</td>
                <td>Bs ${detalle.precio_unitario.toFixed(2)}</td>
                <td><strong>Bs ${detalle.subtotal.toFixed(2)}</strong></td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="AcopioModule.eliminarGrano(${index})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function actualizarTotales() {
        const subtotal = detalles.reduce((sum, d) => sum + d.subtotal, 0);
        document.getElementById('acopio_subtotalDisplay').textContent = 'Bs ' + subtotal.toFixed(2);
        document.getElementById('acopio_totalDisplay').textContent = 'Bs ' + subtotal.toFixed(2);
    }

    function anularAcopio(id, codigo) {
        idParaAnular = id;
        document.getElementById('acopio_codigoAnular').textContent = codigo;
        document.getElementById('acopio_motivoAnulacion').value = '';
        document.getElementById('acopio_modalAnular').style.display = 'flex';
    }

    function cerrarModalAnular() {
        document.getElementById('acopio_modalAnular').style.display = 'none';
        idParaAnular = null;
    }

    function confirmarAnulacion() {
        const motivo = document.getElementById('acopio_motivoAnulacion').value.trim();
        
        if (!motivo) {
            mostrarNotificacion('Debe especificar el motivo de anulación', 'error');
            return;
        }
        
        if (motivo.length < 10) {
            mostrarNotificacion('El motivo debe tener al menos 10 caracteres', 'error');
            return;
        }
        
        if (typeof showLoader === 'function') showLoader();
        
        const formData = new FormData();
        formData.append('motivo', motivo);
        
        fetch(window.PHP_BASE_URL + '/acopios/anular/' + idParaAnular, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (typeof hideLoader === 'function') hideLoader();
            cerrarModalAnular();
            
            if (data.success) {
                mostrarNotificacion(data.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                mostrarNotificacion(data.message, 'error');
            }
        })
        .catch(error => {
            if (typeof hideLoader === 'function') hideLoader();
            cerrarModalAnular();
            mostrarNotificacion('Error al anular el acopio', 'error');
            console.error('Error:', error);
        });
    }

    function cargarDetallesExistentes(detallesOriginales) {
        detalles = detallesOriginales.map(detalle => ({
            grano_id: parseInt(detalle.grano_id),
            nombre: detalle.grano_nombre,
            cantidad: parseFloat(detalle.cantidad),
            precio_unitario: parseFloat(detalle.precio_unitario),
            unidad: detalle.unidad_codigo || '',
            subtotal: parseFloat(detalle.subtotal)
        }));
        
        actualizarTablaGranos();
        actualizarTotales();
    }

    function mostrarNotificacion(mensaje, tipo) {
        if (typeof showNotification === 'function') {
            showNotification(mensaje, tipo);
        } else {
            alert(mensaje);
        }
    }

    return {
        init: init,
        abrirModalClientes: abrirModalClientes,
        cerrarModalClientes: cerrarModalClientes,
        buscarClientes: buscarClientes,
        seleccionarCliente: seleccionarCliente,
        limpiarCliente: limpiarCliente,
        abrirModalGranos: abrirModalGranos,
        cerrarModalGranos: cerrarModalGranos,
        buscarGranos: buscarGranos,
        seleccionarGrano: seleccionarGrano,
        cerrarModalCantidad: cerrarModalCantidad,
        confirmarAgregarGrano: confirmarAgregarGrano,
        eliminarGrano: eliminarGrano,
        anularAcopio: anularAcopio,
        cerrarModalAnular: cerrarModalAnular,
        confirmarAnulacion: confirmarAnulacion,
        cargarDetallesExistentes: cargarDetallesExistentes
    };
})();

document.addEventListener('DOMContentLoaded', AcopioModule.init);