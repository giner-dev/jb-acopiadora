<?php
require_once __DIR__ . '/../repositories/FacturaRepository.php';
require_once __DIR__ . '/../repositories/ProductoRepository.php';
require_once __DIR__ . '/../repositories/ClienteRepository.php';
require_once __DIR__ . '/../repositories/FacturaAdelantoRepository.php';

class FacturaService {
    private $facturaRepository;
    private $productoRepository;
    private $clienteRepository;
    private $adelantoRepository;
    private $db;

    public function __construct() {
        $this->facturaRepository = new FacturaRepository();
        $this->productoRepository = new ProductoRepository();
        $this->clienteRepository = new ClienteRepository();
        $this->adelantoRepository = new FacturaAdelantoRepository();
        $this->db = Database::getInstance();
    }

    public function obtenerTodos($filters = [], $page = 1, $perPage = 20) {
        return $this->facturaRepository->findAllPaginated($filters, $page, $perPage);
    }

    public function obtenerPorId($id, $pageAdelantos = 1, $perPageAdelantos = 10) {
        $factura = $this->facturaRepository->findById($id);
        
        if ($factura) {
            // Paginación de adelantos
            $factura->adelantos = $this->adelantoRepository->findByFacturaIdPaginated($id, $pageAdelantos, $perPageAdelantos);
            $factura->total_adelantos = $this->adelantoRepository->countByFacturaId($id);
            $factura->adelanto = $this->adelantoRepository->getSumaAdelantos($id);
        }
        
        return $factura;
    }

    public function crear($datos) {
        $this->db->beginTransaction();

        try {
            $errores = $this->validarDatos($datos);
            if (!empty($errores)) {
                $this->db->rollBack();
                return ['success' => false, 'errors' => $errores];
            }

            if (!empty($datos['codigo_manual'])) {
                $numeroManual = trim($datos['codigo_manual']);
                $numeroManual = preg_replace('/^FAC/i', '', $numeroManual);
                
                if (!is_numeric($numeroManual) || $numeroManual <= 0) {
                    $this->db->rollBack();
                    return ['success' => false, 'errors' => ['El número de factura debe ser un número positivo']];
                }
                
                $numero = intval($numeroManual);
                $longitudNumero = max(6, strlen((string)$numero));
                $codigo = 'FAC' . str_pad($numero, $longitudNumero, '0', STR_PAD_LEFT);
                
                if ($this->facturaRepository->existeCodigo($codigo)) {
                    $this->db->rollBack();
                    return ['success' => false, 'errors' => ['El código de factura ' . $codigo . ' ya existe']];
                }
            } else {
                $codigo = $this->facturaRepository->generarCodigo();
            }

            $validacionStock = $this->validarStock($datos['detalles']);
            if (!$validacionStock['success']) {
                $this->db->rollBack();
                return $validacionStock;
            }

            $subtotal = 0;
            foreach ($datos['detalles'] as $detalle) {
                $subtotal += $detalle['cantidad'] * $detalle['precio_unitario'];
            }

            $total = $subtotal;
            
            // CAMBIO CRÍTICO: El adelanto inicial ya NO se guarda aquí
            // Solo se guarda en la tabla facturas_adelantos si existe
            $adelantoInicial = isset($datos['adelanto_inicial']) ? floatval($datos['adelanto_inicial']) : 0;
            
            // CAMBIO: El saldo ahora es Total + Adelantos (no Total - Adelantos)
            $saldo = $total + $adelantoInicial;
            
            // CAMBIO: El adelanto en la tabla facturas siempre es 0 al crear
            // Se calcula dinámicamente desde facturas_adelantos
            $adelanto = 0;

            // Estado siempre PENDIENTE al crear
            $estado = 'PENDIENTE';

            $datosFactura = [
                'codigo' => $codigo,
                'cliente_id' => $datos['cliente_id'],
                'fecha' => $datos['fecha'],
                'subtotal' => $subtotal,
                'total' => $total,
                'adelanto' => $adelanto,
                'saldo' => $saldo,
                'estado' => $estado,
                'usuario_id' => authUserId()
            ];

            $facturaId = $this->facturaRepository->create($datosFactura);

            if (!$facturaId) {
                $this->db->rollBack();
                return ['success' => false, 'errors' => ['Error al crear la factura']];
            }

            // Insertar detalles de productos
            foreach ($datos['detalles'] as $detalle) {
                $datosDetalle = [
                    'factura_id' => $facturaId,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['cantidad'] * $detalle['precio_unitario']
                ];

                $this->facturaRepository->createDetalle($datosDetalle);
                $this->descontarStock($detalle['producto_id'], $detalle['cantidad']);
                $this->registrarMovimientoInventario($facturaId, $detalle);
            }

            // NUEVO: Registrar adelanto inicial si existe
            if ($adelantoInicial > 0) {
                $datosAdelanto = [
                    'factura_id' => $facturaId,
                    'monto' => $adelantoInicial,
                    'fecha' => $datos['fecha'],
                    'descripcion' => $datos['adelanto_descripcion'] ?? 'Adelanto inicial',
                    'usuario_id' => authUserId()
                ];
                
                $this->adelantoRepository->create($datosAdelanto);
            }

            // CAMBIO: Registrar en cuenta corriente con nueva lógica
            $this->registrarEnCuentaCorriente($facturaId, $datos['cliente_id'], $total, $adelantoInicial);

            // Recalcular totales después de todo
            $this->recalcularTotalesFactura($facturaId);

            $this->db->commit();

            logMessage("Factura creada: {$codigo} - Cliente ID: {$datos['cliente_id']}", 'info');

            return ['success' => true, 'id' => $facturaId, 'codigo' => $codigo];

        } catch (Exception $e) {
            $this->db->rollBack();
            logMessage("Error al crear factura: " . $e->getMessage(), 'error');
            return ['success' => false, 'errors' => ['Error al procesar la factura: ' . $e->getMessage()]];
        }
    }

    public function agregarAdelanto($facturaId, $datos) {
        $this->db->beginTransaction();
        
        try {
            $factura = $this->facturaRepository->findById($facturaId);
            
            if (!$factura) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Factura no encontrada'];
            }
            
            if ($factura->isAnulada()) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No se puede agregar adelanto a una factura anulada'];
            }
            
            if (empty($datos['monto']) || $datos['monto'] <= 0) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'El monto debe ser mayor a 0'];
            }
            
            $datosAdelanto = [
                'factura_id' => $facturaId,
                'monto' => $datos['monto'],
                'fecha' => $datos['fecha'] ?? date('Y-m-d'),
                'descripcion' => $datos['descripcion'] ?? '',
                'usuario_id' => authUserId()
            ];
            
            $adelantoId = $this->adelantoRepository->create($datosAdelanto);
            
            if (!$adelantoId) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Error al registrar adelanto'];
            }
            
            // Registrar en cuenta corriente
            $this->registrarAdelantoEnCuentaCorriente($facturaId, $factura->cliente_id, $datos['monto']);
            
            // Recalcular totales
            $this->recalcularTotalesFactura($facturaId);
            
            $this->db->commit();
            
            return ['success' => true, 'message' => 'Adelanto registrado correctamente', 'id' => $adelantoId];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            logMessage("Error al agregar adelanto: " . $e->getMessage(), 'error');
            return ['success' => false, 'message' => 'Error al procesar el adelanto: ' . $e->getMessage()];
        }
    }

    public function editarAdelanto($adelantoId, $datos) {
        $this->db->beginTransaction();
        
        try {
            $adelanto = $this->adelantoRepository->findById($adelantoId);
            
            if (!$adelanto) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Adelanto no encontrado'];
            }
            
            if ($adelanto->isEliminado()) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No se puede editar un adelanto eliminado'];
            }
            
            $factura = $this->facturaRepository->findById($adelanto->factura_id);
            
            if ($factura->isAnulada()) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No se puede editar adelanto de una factura anulada'];
            }
            
            if (empty($datos['monto']) || $datos['monto'] <= 0) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'El monto debe ser mayor a 0'];
            }
            
            $resultado = $this->adelantoRepository->update($adelantoId, $datos);
            
            if (!$resultado) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Error al actualizar adelanto'];
            }
            
            // Recalcular totales
            $this->recalcularTotalesFactura($adelanto->factura_id);
            
            $this->db->commit();
            
            return ['success' => true, 'message' => 'Adelanto actualizado correctamente'];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            logMessage("Error al editar adelanto: " . $e->getMessage(), 'error');
            return ['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()];
        }
    }

    public function eliminarAdelanto($adelantoId) {
        $this->db->beginTransaction();
        
        try {
            $adelanto = $this->adelantoRepository->findById($adelantoId);
            
            if (!$adelanto) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Adelanto no encontrado'];
            }
            
            $factura = $this->facturaRepository->findById($adelanto->factura_id);
            
            if ($factura->isAnulada()) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No se puede eliminar adelanto de una factura anulada'];
            }
            
            $resultado = $this->adelantoRepository->delete($adelantoId);
            
            if (!$resultado) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Error al eliminar adelanto'];
            }
            
            // Recalcular totales
            $this->recalcularTotalesFactura($adelanto->factura_id);
            
            $this->db->commit();
            
            return ['success' => true, 'message' => 'Adelanto eliminado correctamente'];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            logMessage("Error al eliminar adelanto: " . $e->getMessage(), 'error');
            return ['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()];
        }
    }

    private function recalcularTotalesFactura($facturaId) {
        $totalAdelantos = $this->adelantoRepository->getSumaAdelantos($facturaId);
        
        $factura = $this->facturaRepository->findById($facturaId);
        
        // NUEVA LÓGICA: Saldo = Total de productos + Total de adelantos
        $nuevoSaldo = $factura->total + $totalAdelantos;
        
        $sql = "UPDATE facturas 
                SET adelanto = :adelanto,
                    saldo = :saldo
                WHERE id_factura = :id";
        
        $params = [
            'id' => $facturaId,
            'adelanto' => $totalAdelantos,
            'saldo' => $nuevoSaldo
        ];
        
        $this->db->execute($sql, $params);
    }

    public function anular($id, $motivo) {
        $this->db->beginTransaction();
        
        try {
            $factura = $this->facturaRepository->findById($id);

            if (!$factura) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Factura no encontrada'];
            }

            if ($factura->isAnulada()) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'La factura ya está anulada'];
            }

            if (empty(trim($motivo))) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Debe especificar el motivo de anulación'];
            }

            foreach ($factura->detalles as $detalle) {
                $this->devolverStock($detalle['producto_id'], $detalle['cantidad']);
                $this->registrarMovimientoInventarioAnulacion($id, $detalle);
            }

            // Marcar adelantos como eliminados
            $this->adelantoRepository->deleteByFacturaId($id);

            $this->anularEnCuentaCorriente($id, $factura->cliente_id);

            $resultado = $this->facturaRepository->anular($id, $motivo);

            if ($resultado) {
                $this->db->commit();
                logMessage("Factura anulada: {$factura->codigo} - Motivo: $motivo", 'info');
                return ['success' => true, 'message' => 'Factura anulada correctamente'];
            }

            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error al anular la factura'];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            logMessage("Error al anular factura: " . $e->getMessage(), 'error');
            return ['success' => false, 'message' => 'Error al procesar la anulación: ' . $e->getMessage()];
        }
    }

    public function contarTotal($filters = []) {
        return $this->facturaRepository->count($filters);
    }

    public function obtenerProximoNumero() {
        return $this->facturaRepository->obtenerProximoNumero();
    }

    private function validarDatos($datos) {
        $errores = [];

        if (empty($datos['cliente_id'])) {
            $errores[] = 'Debe seleccionar un cliente';
        }

        if (empty($datos['fecha'])) {
            $errores[] = 'La fecha es obligatoria';
        }

        if (empty($datos['detalles']) || count($datos['detalles']) == 0) {
            $errores[] = 'Debe agregar al menos un producto';
        }

        if (isset($datos['adelanto_inicial']) && $datos['adelanto_inicial'] < 0) {
            $errores[] = 'El adelanto no puede ser negativo';
        }

        return $errores;
    }

    private function validarStock($detalles) {
        foreach ($detalles as $detalle) {
            $producto = $this->productoRepository->findById($detalle['producto_id']);

            if (!$producto) {
                return ['success' => false, 'errors' => ['Producto no encontrado']];
            }

            if ($producto->stock_ilimitado == 1) {
                continue;
            }

            if ($producto->stock_actual < $detalle['cantidad']) {
                return [
                    'success' => false, 
                    'errors' => ["Stock insuficiente para {$producto->nombre}. Stock actual: {$producto->stock_actual}"]
                ];
            }
        }

        return ['success' => true];
    }

    private function descontarStock($productoId, $cantidad) {
        $producto = $this->productoRepository->findById($productoId);
        
        if ($producto->stock_ilimitado == 1) {
            return;
        }

        $nuevoStock = $producto->stock_actual - $cantidad;
        $this->productoRepository->updateStock($productoId, $nuevoStock);
    }

    private function devolverStock($productoId, $cantidad) {
        $producto = $this->productoRepository->findById($productoId);

        if ($producto->stock_ilimitado == 1) {
            return;
        }

        $nuevoStock = $producto->stock_actual + $cantidad;
        $this->productoRepository->updateStock($productoId, $nuevoStock);
    }

    private function registrarMovimientoInventario($facturaId, $detalle) {
        $producto = $this->productoRepository->findById($detalle['producto_id']);
        
        $sql = "INSERT INTO movimiento_inventario 
                (producto_id, tipo, cantidad, saldo_anterior, saldo_actual, referencia_tipo, referencia_id, usuario_id) 
                VALUES (:producto_id, 'SALIDA_VENTA', :cantidad, :saldo_anterior, :saldo_actual, 'factura', :referencia_id, :usuario_id)";
        
        $params = [
            'producto_id' => $detalle['producto_id'],
            'cantidad' => $detalle['cantidad'],
            'saldo_anterior' => $producto->stock_actual,
            'saldo_actual' => $producto->stock_actual - $detalle['cantidad'],
            'referencia_id' => $facturaId,
            'usuario_id' => authUserId()
        ];
        
        $this->db->execute($sql, $params);
    }

    private function registrarMovimientoInventarioAnulacion($facturaId, $detalle) {
        $producto = $this->productoRepository->findById($detalle['producto_id']);
        
        $sql = "INSERT INTO movimiento_inventario 
                (producto_id, tipo, cantidad, saldo_anterior, saldo_actual, referencia_tipo, referencia_id, observaciones, usuario_id) 
                VALUES (:producto_id, 'DEVOLUCION_CLIENTE', :cantidad, :saldo_anterior, :saldo_actual, 'factura', :referencia_id, 'Anulación de factura', :usuario_id)";
        
        $params = [
            'producto_id' => $detalle['producto_id'],
            'cantidad' => $detalle['cantidad'],
            'saldo_anterior' => $producto->stock_actual,
            'saldo_actual' => $producto->stock_actual + $detalle['cantidad'],
            'referencia_id' => $facturaId,
            'usuario_id' => authUserId()
        ];
        
        $this->db->execute($sql, $params);
    }

    // NUEVA LÓGICA: Adelanto se registra como DEBE (cliente debe más a JB)
    private function registrarEnCuentaCorriente($facturaId, $clienteId, $total, $adelanto) {
        // Registrar DEBE por productos vendidos
        $sql = "INSERT INTO cuenta_corriente 
                (cliente_id, fecha, tipo_movimiento, referencia_tipo, referencia_id, descripcion, debe, haber, usuario_id) 
                VALUES (:cliente_id, NOW(), 'FACTURA', 'factura', :referencia_id, :descripcion, :debe, 0, :usuario_id)";
        
        $params = [
            'cliente_id' => $clienteId,
            'referencia_id' => $facturaId,
            'descripcion' => 'Factura de venta de insumos',
            'debe' => $total,
            'usuario_id' => authUserId()
        ];
        
        $this->db->execute($sql, $params);

        // CAMBIO CRÍTICO: Adelanto también es DEBE (JB paga al cliente)
        if ($adelanto > 0) {
            $sql = "INSERT INTO cuenta_corriente 
                    (cliente_id, fecha, tipo_movimiento, referencia_tipo, referencia_id, descripcion, debe, haber, usuario_id) 
                    VALUES (:cliente_id, NOW(), 'PAGO_JB', 'factura', :referencia_id, 'Adelanto pagado por JB', :debe, 0, :usuario_id)";
            
            $params = [
                'cliente_id' => $clienteId,
                'referencia_id' => $facturaId,
                'debe' => $adelanto,
                'usuario_id' => authUserId()
            ];
            
            $this->db->execute($sql, $params);
        }
    }

    private function registrarAdelantoEnCuentaCorriente($facturaId, $clienteId, $monto) {
        $sql = "INSERT INTO cuenta_corriente 
                (cliente_id, fecha, tipo_movimiento, referencia_tipo, referencia_id, descripcion, debe, haber, usuario_id) 
                VALUES (:cliente_id, NOW(), 'PAGO_JB', 'factura', :referencia_id, 'Adelanto adicional pagado por JB', :debe, 0, :usuario_id)";
        
        $params = [
            'cliente_id' => $clienteId,
            'referencia_id' => $facturaId,
            'debe' => $monto,
            'usuario_id' => authUserId()
        ];
        
        $this->db->execute($sql, $params);
    }

    private function anularEnCuentaCorriente($facturaId, $clienteId) {
        $sql = "DELETE FROM cuenta_corriente WHERE referencia_tipo = 'factura' AND referencia_id = :factura_id";
        $this->db->execute($sql, ['factura_id' => $facturaId]);

        $sql = "INSERT INTO cuenta_corriente 
                (cliente_id, fecha, tipo_movimiento, referencia_tipo, referencia_id, descripcion, debe, haber, usuario_id) 
                VALUES (:cliente_id, NOW(), 'AJUSTE', 'factura', :referencia_id, 'Anulación de factura', 0, 0, :usuario_id)";
        
        $params = [
            'cliente_id' => $clienteId,
            'referencia_id' => $facturaId,
            'usuario_id' => authUserId()
        ];
        
        $this->db->execute($sql, $params);
    }

    public function editar($id, $datos) {
        $this->db->beginTransaction();
    
        try {
            $facturaOriginal = $this->facturaRepository->findById($id);
    
            if (!$facturaOriginal) {
                $this->db->rollBack();
                return ['success' => false, 'errors' => ['Factura no encontrada']];
            }
    
            if ($facturaOriginal->isAnulada()) {
                $this->db->rollBack();
                return ['success' => false, 'errors' => ['No se puede editar una factura anulada']];
            }
    
            $errores = $this->validarDatos($datos);
            if (!empty($errores)) {
                $this->db->rollBack();
                return ['success' => false, 'errors' => $errores];
            }
    
            $validacionStock = $this->validarStock($datos['detalles']);
            if (!$validacionStock['success']) {
                $this->db->rollBack();
                return $validacionStock;
            }
    
            // Devolver stock de productos anteriores
            foreach ($facturaOriginal->detalles as $detalleAnterior) {
                $this->devolverStock($detalleAnterior['producto_id'], $detalleAnterior['cantidad']);
                
                $producto = $this->productoRepository->findById($detalleAnterior['producto_id']);
                
                $sql = "INSERT INTO movimiento_inventario 
                        (producto_id, tipo, cantidad, saldo_anterior, saldo_actual, referencia_tipo, referencia_id, observaciones, usuario_id) 
                        VALUES (:producto_id, 'AJUSTE_EDICION', :cantidad, :saldo_anterior, :saldo_actual, 'factura', :referencia_id, 'Devolución por edición de factura', :usuario_id)";
                
                $params = [
                    'producto_id' => $detalleAnterior['producto_id'],
                    'cantidad' => $detalleAnterior['cantidad'],
                    'saldo_anterior' => $producto->stock_actual - $detalleAnterior['cantidad'],
                    'saldo_actual' => $producto->stock_actual,
                    'referencia_id' => $id,
                    'usuario_id' => authUserId()
                ];
                
                $this->db->execute($sql, $params);
            }
    
            // Calcular nuevos totales
            $subtotal = 0;
            foreach ($datos['detalles'] as $detalle) {
                $subtotal += $detalle['cantidad'] * $detalle['precio_unitario'];
            }
    
            $total = $subtotal;
            
            // Los adelantos se manejan por separado, no aquí
            $totalAdelantos = $this->adelantoRepository->getSumaAdelantos($id);
            $saldo = $total + $totalAdelantos;
    
            $estado = 'PENDIENTE';
    
            // Actualizar factura
            $datosFactura = [
                'cliente_id' => $datos['cliente_id'],
                'fecha' => $datos['fecha'],
                'subtotal' => $subtotal,
                'total' => $total,
                'adelanto' => $totalAdelantos,
                'saldo' => $saldo,
                'estado' => $estado
            ];
    
            $this->facturaRepository->update($id, $datosFactura);
    
            // Eliminar detalles anteriores
            $this->facturaRepository->deleteDetalles($id);
    
            // Insertar nuevos detalles
            foreach ($datos['detalles'] as $detalle) {
                $datosDetalle = [
                    'factura_id' => $id,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['cantidad'] * $detalle['precio_unitario']
                ];
    
                $this->facturaRepository->createDetalle($datosDetalle);
                $this->descontarStock($detalle['producto_id'], $detalle['cantidad']);
                
                $producto = $this->productoRepository->findById($detalle['producto_id']);
                
                $sql = "INSERT INTO movimiento_inventario 
                        (producto_id, tipo, cantidad, saldo_anterior, saldo_actual, referencia_tipo, referencia_id, observaciones, usuario_id) 
                        VALUES (:producto_id, 'SALIDA_VENTA', :cantidad, :saldo_anterior, :saldo_actual, 'factura', :referencia_id, 'Venta por edición de factura', :usuario_id)";
                
                $params = [
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'saldo_anterior' => $producto->stock_actual + $detalle['cantidad'],
                    'saldo_actual' => $producto->stock_actual,
                    'referencia_id' => $id,
                    'usuario_id' => authUserId()
                ];
                
                $this->db->execute($sql, $params);
            }
    
            // Actualizar cuenta corriente
            $this->actualizarCuentaCorriente($id, $datos['cliente_id'], $total, $totalAdelantos, $facturaOriginal);
    
            $this->db->commit();
    
            logMessage("Factura editada: {$facturaOriginal->codigo} - Cliente ID: {$datos['cliente_id']}", 'info');
    
            return ['success' => true, 'id' => $id, 'codigo' => $facturaOriginal->codigo];
    
        } catch (Exception $e) {
            $this->db->rollBack();
            logMessage("Error al editar factura: " . $e->getMessage(), 'error');
            return ['success' => false, 'errors' => ['Error al procesar la edición: ' . $e->getMessage()]];
        }
    }
    
    private function actualizarCuentaCorriente($facturaId, $clienteId, $nuevoTotal, $totalAdelantos, $facturaOriginal) {
        // Eliminar registros anteriores de cuenta corriente
        $sql = "DELETE FROM cuenta_corriente WHERE referencia_tipo = 'factura' AND referencia_id = :factura_id";
        $this->db->execute($sql, ['factura_id' => $facturaId]);
    
        // Registrar nuevo DEBE por productos
        $sql = "INSERT INTO cuenta_corriente 
                (cliente_id, fecha, tipo_movimiento, referencia_tipo, referencia_id, descripcion, debe, haber, usuario_id) 
                VALUES (:cliente_id, NOW(), 'FACTURA', 'factura', :referencia_id, 'Factura de venta (editada)', :debe, 0, :usuario_id)";
        
        $params = [
            'cliente_id' => $clienteId,
            'referencia_id' => $facturaId,
            'debe' => $nuevoTotal,
            'usuario_id' => authUserId()
        ];
        
        $this->db->execute($sql, $params);
    
        // CAMBIO: Registrar adelantos como DEBE
        if ($totalAdelantos > 0) {
            $sql = "INSERT INTO cuenta_corriente 
                    (cliente_id, fecha, tipo_movimiento, referencia_tipo, referencia_id, descripcion, debe, haber, usuario_id) 
                    VALUES (:cliente_id, NOW(), 'PAGO_JB', 'factura', :referencia_id, 'Adelantos pagados por JB (editada)', :debe, 0, :usuario_id)";
            
            $params = [
                'cliente_id' => $clienteId,
                'referencia_id' => $facturaId,
                'debe' => $totalAdelantos,
                'usuario_id' => authUserId()
            ];
            
            $this->db->execute($sql, $params);
        }
    }
}