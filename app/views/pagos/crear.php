<div class="page-header">
    <div>
        <h1><i class="fas fa-money-bill-wave"></i> Registrar Pago</h1>
        <p>Nuevo registro de pago</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo url('pagos'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <form action="<?php echo url('pagos/guardar'); ?>" method="POST" id="formPago">
            <input type="hidden" name="cliente_id" id="pago_cliente_id_hidden" value="<?php echo e($clientePreseleccionado ?? ''); ?>">

            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-user"></i> Cliente</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <button type="button" class="btn btn-secondary" onclick="PagoModule.abrirModalClientes()">
                            <i class="fas fa-search"></i> Buscar Cliente
                        </button>
                        <div id="pago_cliente_display" class="pago-cliente-display" style="<?php echo !empty($clientePreseleccionado) ? 'display:block' : 'display:none'; ?>">
                            <span id="pago_cliente_seleccionado"><?php echo e($clienteNombre ?? ''); ?></span>
                            <button type="button" class="pago-btn-limpiar" onclick="PagoModule.limpiarCliente()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- INFO SALDO: se muestra cuando hay cliente seleccionado -->
                    <div id="pago_saldo_info" class="pago-saldo-info" style="<?php echo !empty($saldoCliente) ? 'display:block' : 'display:none'; ?>">
                        <?php if (!empty($saldoCliente)): ?>
                            <?php $s = floatval($saldoCliente['saldo']); ?>
                            <?php if ($s > 0): ?>
                                <i class="fas fa-info-circle"></i>
                                Este cliente debe <strong>Bs <?php echo number_format($s, 2); ?></strong> a JB. Seleccione "Cliente paga a JB".
                            <?php elseif ($s < 0): ?>
                                <i class="fas fa-info-circle"></i>
                                JB debe <strong>Bs <?php echo number_format(abs($s), 2); ?></strong> a este cliente. Seleccione "JB paga a Cliente".
                            <?php else: ?>
                                <i class="fas fa-info-circle"></i>
                                Este cliente tiene saldo en cero. No hay deudas pendientes.
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-info-circle"></i> Datos del Pago</h2>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="pago_fecha">Fecha <span class="text-danger">*</span></label>
                            <input type="date" id="pago_fecha" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="pago_tipo">Tipo de Pago <span class="text-danger">*</span></label>
                            <select id="pago_tipo" name="tipo" class="form-control" required>
                                <option value="" disabled selected>Seleccionar tipo</option>
                                <option value="PAGO_CLIENTE">Cliente paga a JB</option>
                                <option value="PAGO_JB">JB paga a Cliente</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="pago_metodo">Método de Pago <span class="text-danger">*</span></label>
                            <select id="pago_metodo" name="metodo_pago" class="form-control" required>
                                <option value="" disabled selected>Seleccionar método</option>
                                <option value="EFECTIVO">Efectivo</option>
                                <option value="TRANSFERENCIA">Transferencia</option>
                                <option value="CHEQUE">Cheque</option>
                                <option value="DEPOSITO">Depósito</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="pago_monto">Monto <span class="text-danger">*</span></label>
                            <input type="number" id="pago_monto" name="monto" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="pago_referencia">Referencia de Operación</label>
                            <input type="text" id="pago_referencia" name="referencia_operacion" class="form-control"
                                   placeholder="Nro. transacción, cheque, etc.">
                            <small class="text-muted">Se requiere cuando el método no es efectivo</small>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="pago_concepto">Concepto</label>
                            <input type="text" id="pago_concepto" name="concepto" class="form-control"
                                   placeholder="Descripción breve del pago">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-success btn-lg btn-block">
                        <i class="fas fa-save"></i> Registrar Pago
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- PANEL LATERAL INFORMATIVO -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Guía</h2>
            </div>
            <div class="card-body">
                <div class="pago-info-box">
                    <h4><i class="fas fa-arrow-right"></i> Cliente paga a JB</h4>
                    <p>Cuando el cliente tiene deuda con JB (saldo positivo). El pago reduce esa deuda.</p>
                </div>
                <div class="pago-info-box">
                    <h4><i class="fas fa-arrow-left"></i> JB paga a Cliente</h4>
                    <p>Cuando JB debe dinero al cliente (saldo negativo). El pago reduce la deuda de JB.</p>
                </div>
                <div class="pago-info-box">
                    <h4><i class="fas fa-sync-alt"></i> Cuenta Corriente</h4>
                    <p>Al guardar el pago, automáticamente se actualiza la cuenta corriente del cliente.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL BUSCAR CLIENTES -->
<div id="pago_modalClientes" class="pago-modal">
    <div class="pago-modal-content">
        <div class="pago-modal-header">
            <h3><i class="fas fa-users"></i> Seleccionar Cliente</h3>
            <button type="button" class="pago-modal-close" onclick="PagoModule.cerrarModalClientes()">&times;</button>
        </div>
        <div class="pago-modal-search">
            <input type="text" id="pago_searchCliente" class="pago-modal-search-input"
                   placeholder="Buscar por CI, nombre o comunidad..."
                   onkeyup="PagoModule.buscarClientes()">
        </div>
        <div class="pago-modal-body">
            <table class="pago-modal-table">
                <thead>
                    <tr>
                        <th>CI</th>
                        <th>Nombre</th>
                        <th>Comunidad</th>
                        <th>Saldo</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="pago_clientesTableBody">
                    <tr><td colspan="5" class="text-center">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
        <div id="pago_paginacionClientes" class="pago-modal-footer"></div>
    </div>
</div>