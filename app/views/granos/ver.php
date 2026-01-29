<div class="page-header">
    <div>
        <h1><i class="fas fa-wheat-awn"></i> Detalle del Grano</h1>
        <p>Información completa del grano</p>
    </div>
    <div>
        <button type="button" 
                class="btn btn-success" 
                onclick="abrirModalPrecio(<?php echo $grano->id_grano; ?>, '<?php echo e($grano->nombre); ?>')">
            <i class="fas fa-dollar-sign"></i>
            Registrar Precio
        </button>
        <a href="<?php echo url('granos/editar/' . $grano->id_grano); ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i>
            Editar
        </a>
        <a href="<?php echo url('granos'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Información del Grano</h2>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-tag"></i>
                            Nombre:
                        </span>
                        <span class="detail-value"><?php echo e($grano->nombre); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-balance-scale"></i>
                            Unidad de Medida:
                        </span>
                        <span class="detail-value">
                            <?php echo e($grano->unidad_nombre ?? 'Sin unidad'); ?>
                            <?php if ($grano->unidad_codigo): ?>
                                (<?php echo e($grano->unidad_codigo); ?>)
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-toggle-on"></i>
                            Estado:
                        </span>
                        <span class="detail-value">
                            <?php if ($grano->estado === 'activo'): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactivo</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-calendar"></i>
                            Fecha de Registro:
                        </span>
                        <span class="detail-value"><?php echo formatDate($grano->fecha_creacion); ?></span>
                    </div>
                    
                    <?php if (!empty($grano->descripcion)): ?>
                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <span class="detail-label">
                            <i class="fas fa-align-left"></i>
                            Descripción:
                        </span>
                        <span class="detail-value"><?php echo nl2br(e($grano->descripcion)); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
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
                            Registrado: <?php echo formatDate($grano->fecha_precio); ?>
                        </div>
                        <div class="precio-vigencia">
                            <i class="fas fa-clock"></i>
                            <?php echo $grano->getPrecioVigencia(); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        No hay precio registrado para este grano
                    </div>
                <?php endif; ?>
                
                <button type="button" 
                        class="btn btn-success btn-block mt-3" 
                        onclick="abrirModalPrecio(<?php echo $grano->id_grano; ?>, '<?php echo e($grano->nombre); ?>')">
                    <i class="fas fa-dollar-sign"></i>
                    Actualizar Precio
                </button>
                
                <a href="<?php echo url('granos/precios/' . $grano->id_grano); ?>" 
                   class="btn btn-info btn-block">
                    <i class="fas fa-chart-line"></i>
                    Ver Histórico Completo
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-history"></i> Histórico de Precios (Últimos 30 días)</h2>
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
                            <td><?php echo formatDate($precio['fecha']); ?></td>
                            <td><strong><?php echo formatMoney($precio['precio']); ?></strong></td>
                            <td>
                                <?php if ($variacion !== null): ?>
                                    <?php if ($variacion > 0): ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-arrow-up"></i>
                                            +<?php echo formatMoney(abs($variacion)); ?>
                                            (<?php echo number_format(abs($variacionPorcentaje), 2); ?>%)
                                        </span>
                                    <?php elseif ($variacion < 0): ?>
                                        <span class="badge badge-danger">
                                            <i class="fas fa-arrow-down"></i>
                                            -<?php echo formatMoney(abs($variacion)); ?>
                                            (<?php echo number_format(abs($variacionPorcentaje), 2); ?>%)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-minus"></i>
                                            Sin cambio
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($precio['nombre_usuario'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php 
                            $precioAnterior = $precio['precio'];
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state-small">
                <i class="fas fa-chart-line"></i>
                <p>No hay histórico de precios registrados</p>
            </div>
        <?php endif; ?>
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
                        required>
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