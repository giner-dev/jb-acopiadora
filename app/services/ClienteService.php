<?php
require_once __DIR__ . '/../repositories/ClienteRepository.php';

class ClienteService {
    private $clienteRepository;

    public function __construct() {
        $this->clienteRepository = new ClienteRepository();
    }

    public function obtenerTodos($filters = [], $page = 1, $perPage = 20) {
        return $this->clienteRepository->findAllPaginated($filters, $page, $perPage);
    }

    public function obtenerPorId($id) {
        return $this->clienteRepository->findById($id);
    }

    public function crear($datos) {
        $errores = $this->validarDatos($datos);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        if ($this->clienteRepository->existeCI($datos['ci'])) {
            return ['success' => false, 'errors' => ['El CI ya está registrado']];
        }

        $datosInsert = [
            'ci' => $datos['ci'],
            'nombres' => $datos['nombres'],
            'apellidos' => $datos['apellidos'],
            'comunidad' => $datos['comunidad'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'estado' => 'activo'
        ];

        $id = $this->clienteRepository->create($datosInsert);

        if ($id) {
            logMessage("Cliente creado: ID $id - CI {$datos['ci']}", 'info');
            return ['success' => true, 'id' => $id];
        }

        return ['success' => false, 'errors' => ['Error al crear el cliente']];
    }

    public function actualizar($id, $datos) {
        $cliente = $this->clienteRepository->findById($id);
        if (!$cliente) {
            return ['success' => false, 'errors' => ['Cliente no encontrado']];
        }

        $errores = $this->validarDatos($datos, $id);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        if ($this->clienteRepository->existeCI($datos['ci'], $id)) {
            return ['success' => false, 'errors' => ['El CI ya está registrado en otro cliente']];
        }

        $datosUpdate = [
            'ci' => $datos['ci'],
            'nombres' => $datos['nombres'],
            'apellidos' => $datos['apellidos'],
            'comunidad' => $datos['comunidad'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'estado' => $datos['estado']
        ];

        $resultado = $this->clienteRepository->update($id, $datosUpdate);

        if ($resultado) {
            logMessage("Cliente actualizado: ID $id", 'info');
            return ['success' => true];
        }

        return ['success' => false, 'errors' => ['Error al actualizar el cliente']];
    }

    public function cambiarEstado($id, $nuevoEstado) {
        $cliente = $this->clienteRepository->findById($id);

        if (!$cliente) {
            return ['success' => false, 'message' => 'Cliente no encontrado'];
        }

        if ($cliente->estado === $nuevoEstado) {
            return ['success' => false, 'message' => 'El cliente ya tiene ese estado'];
        }

        $resultado = $this->clienteRepository->cambiarEstado($id, $nuevoEstado);

        if ($resultado) {
            logMessage("Estado cambiado para cliente ID $id: {$cliente->estado} -> $nuevoEstado", 'info');
            return ['success' => true, 'message' => 'Estado actualizado correctamente'];
        }

        return ['success' => false, 'message' => 'Error al cambiar el estado'];
    }

    public function eliminar($id) {
        $cliente = $this->clienteRepository->findById($id);

        if (!$cliente) {
            return ['success' => false, 'message' => 'Cliente no encontrado'];
        }

        $saldoCuenta = $this->clienteRepository->getSaldoCuentaCorriente($id);
        
        if ($saldoCuenta['debe'] > 0 || $saldoCuenta['haber'] > 0) {
            return [
                'success' => false, 
                'message' => 'No se puede eliminar un cliente con movimientos en cuenta corriente. Inactívelo en su lugar.'
            ];
        }

        $resultado = $this->clienteRepository->delete($id);

        if ($resultado) {
            logMessage("Cliente eliminado: ID $id - {$cliente->getNombreCompleto()}", 'info');
            return ['success' => true, 'message' => 'Cliente eliminado correctamente'];
        }

        return ['success' => false, 'message' => 'Error al eliminar el cliente'];
    }

    public function obtenerActivos() {
        return $this->clienteRepository->getActivos();
    }

    public function contarTotal($filters = []) {
        return $this->clienteRepository->count($filters);
    }

    public function obtenerSaldoCuentaCorriente($clienteId) {
        return $this->clienteRepository->getSaldoCuentaCorriente($clienteId);
    }

    private function validarDatos($datos, $excludeId = null) {
        $errores = [];

        if (empty($datos['ci'])) {
            $errores[] = 'El CI es obligatorio';
        } elseif (!preg_match('/^[0-9]+$/', $datos['ci'])) {
            $errores[] = 'El CI debe contener solo números';
        }

        if (empty($datos['nombres'])) {
            $errores[] = 'Los nombres son obligatorios';
        } elseif (strlen($datos['nombres']) < 2) {
            $errores[] = 'Los nombres deben tener al menos 2 caracteres';
        }

        if (empty($datos['apellidos'])) {
            $errores[] = 'Los apellidos son obligatorios';
        } elseif (strlen($datos['apellidos']) < 2) {
            $errores[] = 'Los apellidos deben tener al menos 2 caracteres';
        }

        if (!empty($datos['telefono']) && !preg_match('/^[0-9+\-\s()]+$/', $datos['telefono'])) {
            $errores[] = 'El teléfono contiene caracteres no válidos';
        }

        return $errores;
    }
}