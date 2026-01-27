<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/DashboardService.php';

class DashboardController extends Controller {
    private $dashboardService;
    
    public function __construct() {
        parent::__construct();
        $this->dashboardService = new DashboardService();
    }
    
    public function index() {
        $this->requireAuth();
        
        $estadisticas = $this->dashboardService->obtenerEstadisticasGenerales();
        $productosBajoStock = $this->dashboardService->obtenerProductosBajoStock();
        $ultimasFacturas = $this->dashboardService->obtenerUltimasFacturas();
        $ultimosAcopios = $this->dashboardService->obtenerUltimosAcopios();
        $clientesConDeuda = $this->dashboardService->obtenerClientesConDeuda();
        $movimientosRecientes = $this->dashboardService->obtenerMovimientosRecientes();
        
        $this->render('dashboard/index', [
            'title' => 'Dashboard',
            'user_name' => authUserFullName(),
            'user_role' => authUserRole(),
            'estadisticas' => $estadisticas,
            'productosBajoStock' => $productosBajoStock,
            'ultimasFacturas' => $ultimasFacturas,
            'ultimosAcopios' => $ultimosAcopios,
            'clientesConDeuda' => $clientesConDeuda,
            'movimientosRecientes' => $movimientosRecientes,
            'module_css' => 'dashboard',
            'module_js' => 'dashboard'
        ]);
    }
}