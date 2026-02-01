// ============================================
// MÓDULO PAGOS - AISLADO
// ============================================

const PagoModule = (function() {
    let clientesPage = 1;
    let clientesTotalPages = 1;

    function init() {
        const formPago = document.getElementById('formPago');
        if (formPago) {
            initFormPago();
        }

        // Si hay cliente preseleccionado, cargar su saldo
        const clienteIdHidden = document.getElementById('pago_cliente_id_hidden');
        if (clienteIdHidden && clienteIdHidden.value) {
            cargarSaldoCliente(clienteIdHidden.value);
        }
    }

    function initFormPago() {
        const formPago = document.getElementById('formPago');

        formPago.addEventListener('submit', function(e) {
            const clienteId = document.getElementById('pago_cliente_id_hidden').value;
            if (!clienteId) {
                e.preventDefault();
                mostrarNotificacion('Debe seleccionar un cliente', 'error');
                return;
            }

            const monto = document.getElementById('pago_monto').value;
            if (!monto || parseFloat(monto) <= 0) {
                e.preventDefault();
                mostrarNotificacion('El monto debe ser mayor a 0', 'error');
                return;
            }
        });
    }

    // ============================
    // MODAL DE CLIENTES
    // ============================

    function abrirModalClientes() {
        clientesPage = 1;
        document.getElementById('pago_modalClientes').style.display = 'flex';
        document.getElementById('pago_searchCliente').value = '';
        document.getElementById('pago_searchCliente').focus();
        buscarClientes();
    }

    function cerrarModalClientes() {
        document.getElementById('pago_modalClientes').style.display = 'none';
    }

    function buscarClientes(page) {
        if (page !== undefined) {
            clientesPage = page;
        }

        const search = document.getElementById('pago_searchCliente').value;
        const url = window.PHP_BASE_URL + '/pagos/buscar-clientes?search=' + encodeURIComponent(search) + '&page=' + clientesPage;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('pago_clientesTableBody');
                tbody.innerHTML = '';

                if (data.clientes && data.clientes.length > 0) {
                    clientesTotalPages = data.totalPages || 1;

                    data.clientes.forEach(function(cliente) {
                        const nombreCompleto = cliente.nombres + ' ' + cliente.apellidos;
                        const saldo = parseFloat(cliente.saldo || 0);

                        let saldoHTML = '';
                        if (saldo > 0) {
                            saldoHTML = '<span class="pago-valor-positivo">Cliente debe: Bs ' + saldo.toFixed(2) + '</span>';
                        } else if (saldo < 0) {
                            saldoHTML = '<span class="pago-valor-negativo">JB debe: Bs ' + Math.abs(saldo).toFixed(2) + '</span>';
                        } else {
                            saldoHTML = '<span class="text-muted">Saldo en cero</span>';
                        }

                        const tr = document.createElement('tr');
                        tr.innerHTML =
                            '<td>' + cliente.ci + '</td>' +
                            '<td><strong>' + nombreCompleto + '</strong></td>' +
                            '<td>' + (cliente.comunidad || '-') + '</td>' +
                            '<td>' + saldoHTML + '</td>' +
                            '<td><button type="button" class="btn btn-sm btn-primary" onclick="PagoModule.seleccionarCliente(' + cliente.id_cliente + ', \'' + nombreCompleto + '\', \'' + cliente.ci + '\')"><i class="fas fa-check"></i> Seleccionar</button></td>';

                        tbody.appendChild(tr);
                    });

                    actualizarPaginacion(data.totalPages || 1);
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No se encontraron clientes</td></tr>';
                    actualizarPaginacion(1);
                }
            })
            .catch(function(error) {
                console.error('Error buscando clientes:', error);
                mostrarNotificacion('Error al buscar clientes', 'error');
            });
    }

    function actualizarPaginacion(totalPages) {
        clientesTotalPages = totalPages;
        const footerEl = document.getElementById('pago_paginacionClientes');

        let html = '<div class="pago-modal-pagination">';

        html += '<button type="button" onclick="PagoModule.buscarClientes(' + (clientesPage - 1) + ')" ' +
                (clientesPage <= 1 ? 'disabled' : '') + '>' +
                '<i class="fas fa-chevron-left"></i> Anterior</button>';

        html += '<span class="pago-page-info">Página ' + clientesPage + ' de ' + totalPages + '</span>';

        html += '<button type="button" onclick="PagoModule.buscarClientes(' + (clientesPage + 1) + ')" ' +
                (clientesPage >= totalPages ? 'disabled' : '') + '>' +
                'Siguiente <i class="fas fa-chevron-right"></i></button>';

        html += '</div>';
        footerEl.innerHTML = html;
    }

    function seleccionarCliente(id, nombre, ci) {
        document.getElementById('pago_cliente_id_hidden').value = id;
        document.getElementById('pago_cliente_seleccionado').textContent = nombre + ' - CI: ' + ci;
        document.getElementById('pago_cliente_display').style.display = 'block';
        cerrarModalClientes();

        // Cargar saldo en tiempo real
        cargarSaldoCliente(id);
    }

    function limpiarCliente() {
        document.getElementById('pago_cliente_id_hidden').value = '';
        document.getElementById('pago_cliente_seleccionado').textContent = '';
        document.getElementById('pago_cliente_display').style.display = 'none';
        document.getElementById('pago_saldo_info').style.display = 'none';
    }

    // ============================
    // SALDO DEL CLIENTE
    // ============================

    function cargarSaldoCliente(clienteId) {
        const url = window.PHP_BASE_URL + '/pagos/saldo-cliente?cliente_id=' + clienteId;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.error) return;
                mostrarInfoSaldo(data.saldo);
            })
            .catch(function(error) {
                console.error('Error cargando saldo:', error);
            });
    }

    function mostrarInfoSaldo(saldo) {
        const infoBox = document.getElementById('pago_saldo_info');
        if (!infoBox) return;

        const saldoNum = parseFloat(saldo || 0);
        let texto = '';

        if (saldoNum > 0) {
            texto = '<i class="fas fa-info-circle"></i> Este cliente debe <strong>Bs ' + saldoNum.toFixed(2) + '</strong> a JB. Seleccione "Cliente paga a JB".';
        } else if (saldoNum < 0) {
            texto = '<i class="fas fa-info-circle"></i> JB debe <strong>Bs ' + Math.abs(saldoNum).toFixed(2) + '</strong> a este cliente. Seleccione "JB paga a Cliente".';
        } else {
            texto = '<i class="fas fa-info-circle"></i> Este cliente tiene saldo en cero. No hay deudas pendientes.';
        }

        infoBox.innerHTML = texto;
        infoBox.style.display = 'block';
    }

    // Cerrar modal al clickear fuera
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('pago_modalClientes');
        if (modal && event.target === modal) {
            cerrarModalClientes();
        }
    });

    function mostrarNotificacion(mensaje, tipo) {
        if (typeof showNotification === 'function') {
            showNotification(mensaje, tipo);
        } else {
            alert(mensaje);
        }
    }

    // Funciones públicas expuestas
    return {
        init: init,
        abrirModalClientes: abrirModalClientes,
        cerrarModalClientes: cerrarModalClientes,
        buscarClientes: buscarClientes,
        seleccionarCliente: seleccionarCliente,
        limpiarCliente: limpiarCliente
    };
})();

document.addEventListener('DOMContentLoaded', PagoModule.init);