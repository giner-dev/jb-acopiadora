<?php
require_once __DIR__ . '/../repositories/AcopioRepository.php';
require_once __DIR__ . '/../repositories/GranoRepository.php';
require_once __DIR__ . '/../repositories/ClienteRepository.php';

class AcopioService {
    private $acopioRepository;
    private $granoRepository;
    private $clienteRepository;
    private $db;

    public function __construct() {
        $this->acopioRepository = new AcopioRepository();
        $this->granoRepository = new GranoRepository();
        $this->clienteRepository = new ClienteRepository();
        $this->db = Database::getInstance();
    }

    public function obtenerTodos($filters = [], $page = 1, $perPage = 20) {
        return $this->acopioRepository->findAllPaginated($filters, $page, $perPage);
    }

    public function obtenerPorId($id) {
        return $this->acopioRepository->findById($id);
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
                $numeroManual = preg_replace('/^ACO/i', '', $numeroManual);
                
                if (!is_numeric($numeroManual) || $numeroManual <= 0) {
                    $this->db->rollBack();
                    return ['success' => false, 'errors' => ['El número de acopio debe ser un número positivo']];
                }
                
                $numero = intval($numeroManual);
                $longitudNumero = max(6, strlen((string)$numero));
                $codigo = 'ACO' . str_pad($numero, $longitudNumero, '0', STR_PAD_LEFT);
                
                if ($this->acopioRepository->existeCodigo($codigo)) {
                    $this->db->rollBack();
                    return ['success' => false, 'errors' => ['El código de acopio ' . $codigo . ' ya existe']];
                }
            } else {
                $codigo = $this->acopioRepository->generarCodigo();
            }

            $subtotal = 0;
            foreach ($datos['detalles'] as $detalle) {
                $subtotal += $detalle['cantidad'] * $detalle['precio_unitario'];
            }

            $total = $subtotal;

            $datosAcopio = [
                'codigo' => $codigo,
                'cliente_id' => $datos['cliente_id'],
                'fecha' => $datos['fecha'],
                'subtotal' => $subtotal,
                'total' => $total,
                'observaciones' => $datos['observaciones'] ?? null,
                'estado' => 'ACTIVO',
                'usuario_id' => authUserId()
            ];

            $acopioId = $this->acopioRepository->create($datosAcopio);

            if (!$acopioId) {
                $this->db->rollBack();
                return ['success' => false, 'errors' => ['Error al crear el acopio']];
            }

            foreach ($datos['detalles'] as $detalle) {
                $datosDetalle = [
                    'acopio_id' => $acopioId,
                    'grano_id' => $detalle['grano_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['cantidad'] * $detalle['precio_unitario']
                ];

                $this->acopioRepository->createDetalle($datosDetalle);
            }

            $this->registrarEnCuentaCorriente($acopioId, $datos['cliente_id'], $total);

            $this->db->commit();

            logMessage("Acopio creado: {$codigo} - Cliente ID: {$datos['cliente_id']}", 'info');

            return ['success' => true, 'id' => $acopioId, 'codigo' => $codigo];

        } catch (Exception $e) {
            $this->db->rollBack();
            logMessage("Error al crear acopio: " . $e->getMessage(), 'error');
            return ['success' => false, 'errors' => ['Error al procesar el acopio: ' . $e->getMessage()]];
        }
    }

    public function obtenerProximoNumero() {
        return $this->acopioRepository->obtenerProximoNumero();
    }

    public function anular($id, $motivo) {
        $this->db->beginTransaction();
        
        try {
            $acopio = $this->acopioRepository->findById($id);

            if (!$acopio) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Acopio no encontrado'];
            }

            if ($acopio->isAnulado()) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'El acopio ya está anulado'];
            }

            if (empty(trim($motivo))) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Debe especificar el motivo de anulación'];
            }

            $this->anularEnCuentaCorriente($id, $acopio->cliente_id);

            $resultado = $this->acopioRepository->anular($id, $motivo);

            if ($resultado) {
                $this->db->commit();
                logMessage("Acopio anulado: {$acopio->codigo} - Motivo: $motivo", 'info');
                return ['success' => true, 'message' => 'Acopio anulado correctamente'];
            }

            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error al anular el acopio'];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            logMessage("Error al anular acopio: " . $e->getMessage(), 'error');
            return ['success' => false, 'message' => 'Error al procesar la anulación: ' . $e->getMessage()];
        }
    }

    public function contarTotal($filters = []) {
        return $this->acopioRepository->count($filters);
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
            $errores[] = 'Debe agregar al menos un grano';
        }

        return $errores;
    }

    private function registrarEnCuentaCorriente($acopioId, $clienteId, $total) {
        $sql = "INSERT INTO cuenta_corriente 
                (cliente_id, fecha, tipo_movimiento, referencia_tipo, referencia_id, descripcion, debe, haber, usuario_id) 
                VALUES (:cliente_id, NOW(), 'ACOPIO', 'acopio', :referencia_id, :descripcion, 0, :haber, :usuario_id)";
        
        $params = [
            'cliente_id' => $clienteId,
            'referencia_id' => $acopioId,
            'descripcion' => 'Acopio de cosecha',
            'haber' => $total,
            'usuario_id' => authUserId()
        ];
        
        $this->db->execute($sql, $params);
    }

    private function anularEnCuentaCorriente($acopioId, $clienteId) {
        $sql = "DELETE FROM cuenta_corriente WHERE referencia_tipo = 'acopio' AND referencia_id = :acopio_id";
        $this->db->execute($sql, ['acopio_id' => $acopioId]);

        $sql = "INSERT INTO cuenta_corriente 
                (cliente_id, fecha, tipo_movimiento, referencia_tipo, referencia_id, descripcion, debe, haber, usuario_id) 
                VALUES (:cliente_id, NOW(), 'AJUSTE', 'acopio', :referencia_id, 'Anulación de acopio', 0, 0, :usuario_id)";
        
        $params = [
            'cliente_id' => $clienteId,
            'referencia_id' => $acopioId,
            'usuario_id' => authUserId()
        ];
        
        $this->db->execute($sql, $params);
    }

    public function editar($id, $datos) {
        $this->db->beginTransaction();
    
        try {
            $acopioOriginal = $this->acopioRepository->findById($id);
    
            if (!$acopioOriginal) {
                $this->db->rollBack();
                return ['success' => false, 'errors' => ['Acopio no encontrado']];
            }
    
            if ($acopioOriginal->isAnulado()) {
                $this->db->rollBack();
                return ['success' => false, 'errors' => ['No se puede editar un acopio anulado']];
            }
    
            $errores = $this->validarDatos($datos);
            if (!empty($errores)) {
                $this->db->rollBack();
                return ['success' => false, 'errors' => $errores];
            }
    
            $subtotal = 0;
            foreach ($datos['detalles'] as $detalle) {
                $subtotal += $detalle['cantidad'] * $detalle['precio_unitario'];
            }
    
            $total = $subtotal;
    
            $datosAcopio = [
                'cliente_id' => $datos['cliente_id'],
                'fecha' => $datos['fecha'],
                'subtotal' => $subtotal,
                'total' => $total,
                'observaciones' => $datos['observaciones'] ?? null
            ];
    
            $this->acopioRepository->update($id, $datosAcopio);
    
            $this->acopioRepository->deleteDetalles($id);
    
            foreach ($datos['detalles'] as $detalle) {
                $datosDetalle = [
                    'acopio_id' => $id,
                    'grano_id' => $detalle['grano_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['cantidad'] * $detalle['precio_unitario']
                ];
    
                $this->acopioRepository->createDetalle($datosDetalle);
            }
    
            $this->actualizarCuentaCorriente($id, $datos['cliente_id'], $total, $acopioOriginal);
    
            $this->db->commit();
    
            logMessage("Acopio editado: {$acopioOriginal->codigo} - Cliente ID: {$datos['cliente_id']}", 'info');
    
            return ['success' => true, 'id' => $id, 'codigo' => $acopioOriginal->codigo];
    
        } catch (Exception $e) {
            $this->db->rollBack();
            logMessage("Error al editar acopio: " . $e->getMessage(), 'error');
            return ['success' => false, 'errors' => ['Error al procesar la edición: ' . $e->getMessage()]];
        }
    }
    
    private function actualizarCuentaCorriente($acopioId, $clienteId, $nuevoTotal, $acopioOriginal) {
        $sql = "DELETE FROM cuenta_corriente WHERE referencia_tipo = 'acopio' AND referencia_id = :acopio_id";
        $this->db->execute($sql, ['acopio_id' => $acopioId]);
    
        $sql = "INSERT INTO cuenta_corriente 
                (cliente_id, fecha, tipo_movimiento, referencia_tipo, referencia_id, descripcion, debe, haber, usuario_id) 
                VALUES (:cliente_id, NOW(), 'ACOPIO', 'acopio', :referencia_id, 'Acopio de cosecha (editado)', 0, :haber, :usuario_id)";
        
        $params = [
            'cliente_id' => $clienteId,
            'referencia_id' => $acopioId,
            'haber' => $nuevoTotal,
            'usuario_id' => authUserId()
        ];
        
        $this->db->execute($sql, $params);
    }
}