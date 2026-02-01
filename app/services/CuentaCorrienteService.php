<?php
require_once __DIR__ . '/../repositories/CuentaCorrienteRepository.php';
require_once __DIR__ . '/../repositories/ClienteRepository.php';

class CuentaCorrienteService {
    private $cuentaCorrienteRepository;
    private $clienteRepository;

    public function __construct() {
        $this->cuentaCorrienteRepository = new CuentaCorrienteRepository();
        $this->clienteRepository = new ClienteRepository();
    }

    public function obtenerTodos($filters = [], $page = 1, $perPage = 20) {
        return $this->cuentaCorrienteRepository->findAllPaginated($filters, $page, $perPage);
    }

    public function contarTotal($filters = []) {
        return $this->cuentaCorrienteRepository->count($filters);
    }

    public function obtenerSaldoPorCliente($clienteId) {
        return $this->cuentaCorrienteRepository->getSaldoPorCliente($clienteId);
    }

    public function obtenerClientesConSaldo() {
        return $this->cuentaCorrienteRepository->getClientesConSaldo();
    }

    public function obtenerClientesDeudores() {
        return $this->cuentaCorrienteRepository->getClientesDeudores();
    }

    public function obtenerClientesAcreedores() {
        return $this->cuentaCorrienteRepository->getClientesAcreedores();
    }

    public function obtenerMovimientosPorCliente($clienteId) {
        return $this->cuentaCorrienteRepository->getMovimientosPorCliente($clienteId);
    }

    public function obtenerTotales($filters = []) {
        return $this->cuentaCorrienteRepository->getTotales($filters);
    }
}