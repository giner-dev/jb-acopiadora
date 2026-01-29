<?php
require_once __DIR__ . '/../repositories/FacturaRepository.php';
require_once __DIR__ . '/../repositories/ProductoRepository.php';
require_once __DIR__ . '/../repositories/ClienteRepository.php';

class FacturaService {
    private $facturaRepository;
    private $productoRepository;
    private $clienteRepository;
    private $db;

    public function __construct() {
        $this->facturaRepository = new FacturaRepository();
        $this->productoRepository = new ProductoRepository();
        $this->clienteRepository = new ClienteRepository();
        $this->db = Database::getInstance();
    }

    public function obtenerTodos($filters = [], $page = 1, $perPage = 20) {
        return $this->facturaRepository->findAllPaginated($filters, $page, $perPage);
    }

    public function obtenerPorId($id) {
        return $this->facturaRepository->findById($id);
    }

    public function crear($datos) {
        $this->db->beginTransaction();

        try {
            $errores = $this->validarDatos($datos);
            if (!empty($errores)) {
                $this->db->rollBack();
                return ['success' => false, 'errors' => $errores];
            }

            // AGREGAR: Validar código si viene uno manual
            if (!empty($datos['codigo_manual'])) {
                $numeroManual = trim($datos['codigo_manual']);
                
                // Remover "FAC" si el usuario lo escribió
                $numeroManual = preg_replace('/^FAC/i', '', $numeroManual);
                
                // Validar que sea numérico
                if (!is_numeric($numeroManual) || $numeroManual <= 0) {
                    $this->db->rollBack();
                    return ['success' => false, 'errors' => ['El número de factura debe ser un número positivo']];
                }
                
                // Convertir a entero y generar código
                $numero = intval($numeroManual);
                
                // Generar código con padding (mínimo 6 dígitos, pero puede ser más)
                $longitudNumero = max(6, strlen((string)$numero));
                $codigo = 'FAC' . str_pad($numero, $longitudNumero, '0', STR_PAD_LEFT);
                
                // Validar que no exista
                if ($this->facturaRepository->existeCodigo($codigo)) {
                    $this->db->rollBack();
                    return ['success' => false, 'errors' => ['El código de factura ' . $codigo . ' ya existe']];
                }
            } else {
                // Generar automáticamente
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
            $adelanto = isset($datos['adelanto']) ? floatval($datos['adelanto']) : 0;
            $saldo = $total - $adelanto;

            $estado = 'PENDIENTE';
            if ($adelanto >= $total) {
                $estado = 'PAGADA';
                $saldo = 0;
            } elseif ($adelanto > 0) {
                $estado = 'PAGO_PARCIAL';
            }

            $datosFactura = [
                'codigo' => $codigo, // USAR el código validado
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

            $this->registrarEnCuentaCorriente($facturaId, $datos['cliente_id'], $total, $adelanto);

            $this->db->commit();

            logMessage("Factura creada: {$codigo} - Cliente ID: {$datos['cliente_id']}", 'info');

            return ['success' => true, 'id' => $facturaId, 'codigo' => $codigo];

        } catch (Exception $e) {
            $this->db->rollBack();
            logMessage("Error al crear factura: " . $e->getMessage(), 'error');
            return ['success' => false, 'errors' => ['Error al procesar la factura: ' . $e->getMessage()]];
        }
    }

    public function obtenerProximoNumero() {
        return $this->facturaRepository->obtenerProximoNumero();
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

        if (isset($datos['adelanto']) && $datos['adelanto'] < 0) {
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

    private function registrarEnCuentaCorriente($facturaId, $clienteId, $total, $adelanto) {
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

        if ($adelanto > 0) {
            $sql = "INSERT INTO cuenta_corriente 
                    (cliente_id, fecha, tipo_movimiento, referencia_tipo, referencia_id, descripcion, debe, haber, usuario_id) 
                    VALUES (:cliente_id, NOW(), 'PAGO_CLIENTE', 'factura', :referencia_id, 'Adelanto en factura', 0, :haber, :usuario_id)";
            
            $params = [
                'cliente_id' => $clienteId,
                'referencia_id' => $facturaId,
                'haber' => $adelanto,
                'usuario_id' => authUserId()
            ];
            
            $this->db->execute($sql, $params);
        }
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
    
            // PASO 1: Devolver stock de productos anteriores
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
    
            // PASO 2: Calcular nuevos totales
            $subtotal = 0;
            foreach ($datos['detalles'] as $detalle) {
                $subtotal += $detalle['cantidad'] * $detalle['precio_unitario'];
            }
    
            $total = $subtotal;
            $adelanto = isset($datos['adelanto']) ? floatval($datos['adelanto']) : $facturaOriginal->adelanto;
            $saldo = $total - $adelanto;
    
            $estado = 'PENDIENTE';
            if ($adelanto >= $total) {
                $estado = 'PAGADA';
                $saldo = 0;
            } elseif ($adelanto > 0) {
                $estado = 'PAGO_PARCIAL';
            }
    
            // PASO 3: Actualizar factura
            $datosFactura = [
                'cliente_id' => $datos['cliente_id'],
                'fecha' => $datos['fecha'],
                'subtotal' => $subtotal,
                'total' => $total,
                'adelanto' => $adelanto,
                'saldo' => $saldo,
                'estado' => $estado
            ];
    
            $this->facturaRepository->update($id, $datosFactura);
    
            // PASO 4: Eliminar detalles anteriores
            $this->facturaRepository->deleteDetalles($id);
    
            // PASO 5: Insertar nuevos detalles y descontar stock
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
    
            // PASO 6: Actualizar cuenta corriente
            $this->actualizarCuentaCorriente($id, $datos['cliente_id'], $total, $adelanto, $facturaOriginal);
    
            $this->db->commit();
    
            logMessage("Factura editada: {$facturaOriginal->codigo} - Cliente ID: {$datos['cliente_id']}", 'info');
    
            return ['success' => true, 'id' => $id, 'codigo' => $facturaOriginal->codigo];
    
        } catch (Exception $e) {
            $this->db->rollBack();
            logMessage("Error al editar factura: " . $e->getMessage(), 'error');
            return ['success' => false, 'errors' => ['Error al procesar la edición: ' . $e->getMessage()]];
        }
    }
    
    private function actualizarCuentaCorriente($facturaId, $clienteId, $nuevoTotal, $nuevoAdelanto, $facturaOriginal) {
        // Eliminar registros anteriores de cuenta corriente
        $sql = "DELETE FROM cuenta_corriente WHERE referencia_tipo = 'factura' AND referencia_id = :factura_id";
        $this->db->execute($sql, ['factura_id' => $facturaId]);
    
        // Registrar nuevo DEBE (total de la factura)
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
    
        // Registrar nuevo HABER si hay adelanto
        if ($nuevoAdelanto > 0) {
            $sql = "INSERT INTO cuenta_corriente 
                    (cliente_id, fecha, tipo_movimiento, referencia_tipo, referencia_id, descripcion, debe, haber, usuario_id) 
                    VALUES (:cliente_id, NOW(), 'PAGO_CLIENTE', 'factura', :referencia_id, 'Adelanto en factura (editada)', 0, :haber, :usuario_id)";
            
            $params = [
                'cliente_id' => $clienteId,
                'referencia_id' => $facturaId,
                'haber' => $nuevoAdelanto,
                'usuario_id' => authUserId()
            ];
            
            $this->db->execute($sql, $params);
        }
    }
}