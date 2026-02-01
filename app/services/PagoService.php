<?php
require_once __DIR__ . '/../repositories/PagoRepository.php';
require_once __DIR__ . '/../repositories/CuentaCorrienteRepository.php';

class PagoService {
    private $pagoRepository;
    private $cuentaCorrienteRepository;

    public function __construct() {
        $this->pagoRepository = new PagoRepository();
        $this->cuentaCorrienteRepository = new CuentaCorrienteRepository();
    }

    public function obtenerTodos($filters = [], $page = 1, $perPage = 20) {
        return $this->pagoRepository->findAllPaginated($filters, $page, $perPage);
    }

    public function contarTotal($filters = []) {
        return $this->pagoRepository->count($filters);
    }

    public function obtenerPorId($id) {
        return $this->pagoRepository->findById($id);
    }

    public function obtenerTotales($filters = []) {
        return $this->pagoRepository->getTotales($filters);
    }

    public function generarCodigo() {
        return $this->pagoRepository->generarCodigo();
    }

    public function obtenerSaldoCliente($clienteId) {
        return $this->cuentaCorrienteRepository->getSaldoPorCliente($clienteId);
    }

    /**
     * Registra un pago y automáticamente crea el movimiento en cuenta corriente.
     * 
     * Lógica de validación:
     * - PAGO_CLIENTE: solo cuando saldo > 0 (cliente debe a JB), monto no puede superar la deuda
     * - PAGO_JB: solo cuando saldo < 0 (JB debe al cliente), monto no puede superar lo que debe JB
     */
    public function crear($datos) {
        $errores = $this->validar($datos);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        // Obtener saldo actual del cliente
        $saldoCliente = $this->cuentaCorrienteRepository->getSaldoPorCliente($datos['cliente_id']);
        $saldo = floatval($saldoCliente['saldo'] ?? 0);
        $monto = floatval($datos['monto']);

        // Validar que el tipo de pago corresponde al saldo actual
        if ($datos['tipo'] === 'PAGO_CLIENTE') {
            if ($saldo <= 0) {
                return ['success' => false, 'errors' => ['El cliente no tiene deuda pendiente con JB']];
            }
            if ($monto > $saldo) {
                return ['success' => false, 'errors' => ['El monto (Bs ' . number_format($monto, 2) . ') excede la deuda del cliente (Bs ' . number_format($saldo, 2) . ')']];
            }
        } else {
            // PAGO_JB
            if ($saldo >= 0) {
                return ['success' => false, 'errors' => ['JB no tiene deuda pendiente con este cliente']];
            }
            $deudaJB = abs($saldo);
            if ($monto > $deudaJB) {
                return ['success' => false, 'errors' => ['El monto (Bs ' . number_format($monto, 2) . ') excede la deuda de JB (Bs ' . number_format($deudaJB, 2) . ')']];
            }
        }

        // Generar código
        $datos['codigo'] = $this->pagoRepository->generarCodigo();
        $datos['estado'] = 'COMPLETADO';
        $datos['usuario_id'] = authUserId();

        // Insertar pago
        $idPago = $this->pagoRepository->create($datos);

        if (!$idPago) {
            return ['success' => false, 'errors' => ['Error al registrar el pago']];
        }

        // RF-07.4: Crear movimiento automático en cuenta corriente
        $this->crearMovimientoCuentaCorriente($idPago, $datos);

        return ['success' => true, 'id' => $idPago, 'codigo' => $datos['codigo']];
    }

    /**
     * Anula un pago y revierte el movimiento en cuenta corriente.
     * 
     * Para revertir: si el pago original fue HABER, se crea un DEBE por el mismo monto, y viceversa.
     */
    public function anular($id) {
        $pago = $this->pagoRepository->findById($id);

        if (!$pago) {
            return ['success' => false, 'errors' => ['Pago no encontrado']];
        }

        if ($pago->isAnulado()) {
            return ['success' => false, 'errors' => ['Este pago ya fue anulado']];
        }

        // Anular el pago en la tabla pagos
        $this->pagoRepository->anular($id);

        // Revertir movimiento en cuenta corriente
        $this->revertirMovimientoCuentaCorriente($pago);

        return ['success' => true];
    }

    private function validar($datos) {
        $errores = [];

        if (empty($datos['cliente_id'])) {
            $errores[] = 'Debe seleccionar un cliente';
        }

        if (empty($datos['tipo']) || !in_array($datos['tipo'], ['PAGO_JB', 'PAGO_CLIENTE'])) {
            $errores[] = 'Tipo de pago inválido';
        }

        if (empty($datos['metodo_pago']) || !in_array($datos['metodo_pago'], ['EFECTIVO', 'TRANSFERENCIA', 'CHEQUE', 'DEPOSITO', 'OTRO'])) {
            $errores[] = 'Método de pago inválido';
        }

        if (empty($datos['monto']) || floatval($datos['monto']) <= 0) {
            $errores[] = 'El monto debe ser mayor a 0';
        }

        if (empty($datos['fecha'])) {
            $errores[] = 'La fecha es obligatoria';
        }

        return $errores;
    }

    /**
     * Crea el movimiento correspondiente en cuenta_corriente.
     * 
     * PAGO_CLIENTE (cliente paga a JB): se registra como HABER, porque reduce la deuda del cliente.
     * PAGO_JB (JB paga al cliente): se registra como DEBE, porque reduce la deuda de JB.
     * 
     * Esto puede sonar contraintuitivo. La lógica es:
     * - Saldo = SUM(debe) - SUM(haber)
     * - Saldo positivo = cliente debe
     * - Para reducir un saldo positivo, necesito aumentar el haber -> PAGO_CLIENTE va a haber
     * - Para reducir un saldo negativo, necesito aumentar el debe -> PAGO_JB va a debe
     */
    private function crearMovimientoCuentaCorriente($idPago, $datos) {
        $descripcion = $datos['tipo'] === 'PAGO_CLIENTE'
            ? 'Pago recibido de cliente'
            : 'Pago realizado a cliente';

        if (!empty($datos['concepto'])) {
            $descripcion .= ' - ' . $datos['concepto'];
        }

        $movimiento = [
            'cliente_id'        => $datos['cliente_id'],
            'fecha'             => $datos['fecha'],
            'tipo_movimiento'   => $datos['tipo'],
            'referencia_tipo'   => 'pago',
            'referencia_id'     => $idPago,
            'descripcion'       => $descripcion,
            'debe'              => $datos['tipo'] === 'PAGO_JB' ? $datos['monto'] : 0,
            'haber'             => $datos['tipo'] === 'PAGO_CLIENTE' ? $datos['monto'] : 0,
            'usuario_id'        => authUserId()
        ];

        $this->cuentaCorrienteRepository->create($movimiento);
    }

    /**
     * Revierte un movimiento al anular un pago.
     * 
     * Si el pago original creó un HABER, el reversa crea un DEBE por el mismo monto.
     * Si el pago original creó un DEBE, el reversa crea un HABER por el mismo monto.
     */
    private function revertirMovimientoCuentaCorriente($pago) {
        $descripcion = 'Reversa por anulación de pago ' . $pago->codigo;

        $movimiento = [
            'cliente_id'        => $pago->cliente_id,
            'fecha'             => date('Y-m-d'),
            'tipo_movimiento'   => 'AJUSTE',
            'referencia_tipo'   => 'pago_anulado',
            'referencia_id'     => $pago->id_pago,
            'descripcion'       => $descripcion,
            'debe'              => $pago->tipo === 'PAGO_CLIENTE' ? $pago->monto : 0,
            'haber'             => $pago->tipo === 'PAGO_JB' ? $pago->monto : 0,
            'usuario_id'        => authUserId()
        ];

        $this->cuentaCorrienteRepository->create($movimiento);
    }
}