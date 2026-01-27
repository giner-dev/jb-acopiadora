<?php
require_once __DIR__ . '/../repositories/DashboardRepository.php';

class DashboardService {
    private $dashboardRepository;

    public function __construct() {
        $this->dashboardRepository = new DashboardRepository();
    }

    public function obtenerEstadisticasGenerales() {
        return [
            'total_clientes' => $this->dashboardRepository->getTotalClientes(),
            'total_productos' => $this->dashboardRepository->getTotalProductos(),
            'facturas_pendientes' => $this->dashboardRepository->getTotalFacturasPendientes(),
            'monto_facturas_pendientes' => $this->dashboardRepository->getMontoFacturasPendientes(),
            'saldo_total' => $this->dashboardRepository->getSaldoTotalCuentaCorriente()
        ];
    }

    public function obtenerProductosBajoStock() {
        return $this->dashboardRepository->getProductosBajoStock();
    }

    public function obtenerUltimasFacturas($limit = 5) {
        return $this->dashboardRepository->getUltimasFacturas($limit);
    }

    public function obtenerUltimosAcopios($limit = 5) {
        return $this->dashboardRepository->getUltimosAcopios($limit);
    }

    public function obtenerClientesConDeuda() {
        $clientes = $this->dashboardRepository->getClientesConDeuda();
        
        foreach ($clientes as &$cliente) {
            $cliente['saldo'] = $cliente['total_haber'] - $cliente['total_debe'];
        }
        
        return $clientes;
    }

    public function obtenerMovimientosRecientes($limit = 10) {
        return $this->dashboardRepository->getMovimientosRecientes($limit);
    }
}