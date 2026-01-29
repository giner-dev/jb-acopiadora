<div class="page-header">
    <div>
        <h1><i class="fas fa-chart-line"></i> Histórico de Precios</h1>
        <p>Histórico completo de precios para: <strong><?php echo e($grano->nombre); ?></strong></p>
    </div>
    <div>
        <button type="button" 
                class="btn btn-success" 
                onclick="abrirModalPrecio(<?php echo $grano->id_grano; ?>, '<?php echo e($grano->nombre); ?>')">
            <i class="fas fa-dollar-sign"></i>
            Registrar Precio
        </button>
        <a href="<?php echo url('granos/ver/' . $grano->id_grano); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Información del Grano</h2>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Nombre:</span>
                        <span class="detail-value"><?php echo e($grano->nombre); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Unidad:</span>
                        <span class="detail-value">
                            <?php echo e($grano->unidad_nombre ?? 'N/A'); ?>
                            <?php if ($grano->unidad_codigo): ?>
                                (<?php echo e($grano->unidad_codigo); ?>)
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-dollar-sign"></i> Precio Actual</h2>
            </div>
            <div class="card-body">
                <?php if ($grano->tienePrecioActual()): ?>
                    <div class="precio-actual-display">
                        <div class="precio-valor">
                            <?php echo $grano->getPrecioActualFormateado(); ?>
                        </div>
                        <div class="precio-unidad">
                            por <?php echo e($grano->unidad_codigo ?? 'unidad'); ?>
                        </div>
                        <div class="precio-vigencia">
                            <i class="fas fa-calendar"></i>
                            <?php echo formatDate($grano->fecha_precio); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Sin precio registrado
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($historialPrecios)): ?>
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-chart-bar"></i> Estadísticas</h2>
            </div>
            <div class="card-body">
                <?php
                $precios = array_column($historialPrecios, 'precio');
                $precioMaximo = !empty($precios) ? max($precios) : 0;
                $precioMinimo = !empty($precios) ? min($precios) : 0;
                $precioPromedio = !empty($precios) ? array_sum($precios) / count($precios) : 0;
                ?>
                
                <div class="estadistica-item">
                    <div class="estadistica-label">
                        <i class="fas fa-arrow-up text-success"></i>
                        Precio Máximo:
                    </div>
                    <div class="estadistica-valor text-success">
                        <?php echo formatMoney($precioMaximo); ?>
                    </div>
                </div>
                
                <div class="estadistica-item">
                    <div class="estadistica-label">
                        <i class="fas fa-arrow-down text-danger"></i>
                        Precio Mínimo:
                    </div>
                    <div class="estadistica-valor text-danger">
                        <?php echo formatMoney($precioMinimo); ?>
                    </div>
                </div>
                
                <div class="estadistica-item">
                    <div class="estadistica-label">
                        <i class="fas fa-calculator text-info"></i>
                        Precio Promedio:
                    </div>
                    <div class="estadistica-valor text-info">
                        <?php echo formatMoney($precioPromedio); ?>
                    </div>
                </div>
                
                <div class="estadistica-item">
                    <div class="estadistica-label">
                        <i class="fas fa-list"></i>
                        Total Registros:
                    </div>
                    <div class="estadistica-valor">
                        <?php echo count($historialPrecios); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> Histórico Completo</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($historialPrecios)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Precio</th>
                                    <th>Variación</th>
                                    <th>Registrado por</th>
                                    <th>Fecha de Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $precioAnterior = null;
                                foreach ($historialPrecios as $precio): 
                                    $variacion = null;
                                    $variacionPorcentaje = null;
                                    
                                    if ($precioAnterior !== null) {
                                        $variacion = $precio['precio'] - $precioAnterior;
                                        $variacionPorcentaje = ($precioAnterior > 0) 
                                            ? (($variacion / $precioAnterior) * 100) 
                                            : 0;
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo formatDate($precio['fecha']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="precio-badge">
                                            <?php echo formatMoney($precio['precio']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($variacion !== null): ?>
                                            <?php if ($variacion > 0): ?>
                                                <span class="badge badge-success">
                                                    <i class="fas fa-arrow-up"></i>
                                                    +<?php echo formatMoney(abs($variacion)); ?>
                                                    <br>
                                                    <small>(+<?php echo number_format(abs($variacionPorcentaje), 2); ?>%)</small>
                                                </span>
                                            <?php elseif ($variacion < 0): ?>
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-arrow-down"></i>
                                                    -<?php echo formatMoney(abs($variacion)); ?>
                                                    <br>
                                                    <small>(-<?php echo number_format(abs($variacionPorcentaje), 2); ?>%)</small>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-minus"></i>
                                                    Sin cambio
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Precio inicial</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($precio['nombre_usuario'] ?? 'N/A'); ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($precio['fecha_creacion'])); ?>
                                        </small>
                                    </td>
                                </tr>
                                <?php 
                                    $precioAnterior = $precio['precio'];
                                endforeach; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chart-line"></i>
                        <p>No hay histórico de precios registrados</p>
                        <button type="button" 
                                class="btn btn-primary" 
                                onclick="abrirModalPrecio(<?php echo $grano->id_grano; ?>, '<?php echo e($grano->nombre); ?>')">
                            <i class="fas fa-dollar-sign"></i>
                            Registrar Primer Precio
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- MODAL REGISTRAR PRECIO -->
<div id="modalPrecio" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="fas fa-dollar-sign"></i> Registrar Precio</h3>
            <button type="button" class="modal-close" onclick="cerrarModalPrecio()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Registrar precio para: <strong id="granoNombrePrecio"></strong></p>
            
            <form id="formRegistrarPrecio">
                <input type="hidden" id="grano_id_precio">
                
                <div class="form-group">
                    <label for="fecha_precio">
                        <i class="fas fa-calendar"></i>
                        Fecha
                        <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="fecha_precio" 
                        name="fecha" 
                        class="form-control" 
                        value="<?php echo date('Y-m-d'); ?>"
                        max="<?php echo date('Y-m-d'); ?>"
                        required>
                    <small>No se puede registrar precios futuros</small>
                </div>
                
                <div class="form-group">
                    <label for="precio_valor">
                        <i class="fas fa-dollar-sign"></i>
                        Precio (Bs)
                        <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="precio_valor" 
                        name="precio" 
                        class="form-control" 
                        step="0.01"
                        min="0.01"
                        placeholder="0.00"
                        required
                        autofocus>
                    <small>Precio por <?php echo e($grano->unidad_codigo ?? 'unidad'); ?></small>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="cerrarModalPrecio()">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="confirmarRegistroPrecio()">
                <i class="fas fa-save"></i>
                Registrar Precio
            </button>
        </div>
    </div>
</div>