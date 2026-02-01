<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../repositories/ReporteRepository.php';

class ReporteController extends Controller {
    private $reporteRepository;

    public function __construct() {
        parent::__construct();
        $this->reporteRepository = new ReporteRepository();
    }

    // Página principal de reportes (menú)
    public function index() {
        $this->requireAuth();

        $this->render('reportes/index', [
            'title'      => 'Reportes',
            'module_css' => 'reportes',
            'module_js'  => 'reportes'
        ]);
    }

    // ============================================
    // RF-08.1
    // ============================================
    public function clientesDeudores() {
        $this->requireAuth();

        $page    = (int)$this->getQuery('page', 1);
        $perPage = 20;

        $clientes   = $this->reporteRepository->getClientesDeudores($page, $perPage);
        $total      = $this->reporteRepository->countClientesDeudores();
        $totales    = $this->reporteRepository->getTotalesDeudores();
        $totalPages = ceil($total / $perPage);

        $this->render('reportes/clientes-deudores', [
            'title'      => 'Clientes Deudores',
            'clientes'   => $clientes,
            'total'      => $total,
            'totales'    => $totales,
            'page'       => $page,
            'totalPages' => $totalPages,
            'perPage'    => $perPage,
            'module_css' => 'reportes',
            'module_js'  => 'reportes'
        ]);
    }

    // ============================================
    // RF-08.2
    // ============================================
    public function clientesAcreedores() {
        $this->requireAuth();

        $page    = (int)$this->getQuery('page', 1);
        $perPage = 20;

        $clientes   = $this->reporteRepository->getClientesAcreedores($page, $perPage);
        $total      = $this->reporteRepository->countClientesAcreedores();
        $totales    = $this->reporteRepository->getTotalesAcreedores();
        $totalPages = ceil($total / $perPage);

        $this->render('reportes/clientes-acreedores', [
            'title'      => 'Clientes Acreedores',
            'clientes'   => $clientes,
            'total'      => $total,
            'totales'    => $totales,
            'page'       => $page,
            'totalPages' => $totalPages,
            'perPage'    => $perPage,
            'module_css' => 'reportes',
            'module_js'  => 'reportes'
        ]);
    }

    // ============================================
    // RF-08.3
    // ============================================
    public function ventasPorPeriodo() {
        $this->requireAuth();

        $fechaDesde = $this->getQuery('fecha_desde', date('Y-m-01'));
        $fechaHasta = $this->getQuery('fecha_hasta', date('Y-m-d'));
        $page       = (int)$this->getQuery('page', 1);
        $perPage    = 20;

        $ventas     = $this->reporteRepository->getVentasPorPeriodo($fechaDesde, $fechaHasta, $page, $perPage);
        $total      = $this->reporteRepository->countVentasPorPeriodo($fechaDesde, $fechaHasta);
        $totales    = $this->reporteRepository->getTotalesVentas($fechaDesde, $fechaHasta);
        $ventasMes  = $this->reporteRepository->getVentasPorMes($fechaDesde, $fechaHasta);
        $totalPages = ceil($total / $perPage);

        $this->render('reportes/ventas-por-periodo', [
            'title'       => 'Ventas por Período',
            'ventas'      => $ventas,
            'total'       => $total,
            'totales'     => $totales,
            'ventasMes'   => $ventasMes,
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'page'        => $page,
            'totalPages'  => $totalPages,
            'perPage'     => $perPage,
            'module_css'  => 'reportes',
            'module_js'   => 'reportes'
        ]);
    }

    // ============================================
    // RF-08.4
    // ============================================
    public function acopiosPorPeriodo() {
        $this->requireAuth();

        $fechaDesde = $this->getQuery('fecha_desde', date('Y-m-01'));
        $fechaHasta = $this->getQuery('fecha_hasta', date('Y-m-d'));
        $page       = (int)$this->getQuery('page', 1);
        $perPage    = 20;

        $acopios    = $this->reporteRepository->getAcopiosPorPeriodo($fechaDesde, $fechaHasta, $page, $perPage);
        $total      = $this->reporteRepository->countAcopiosPorPeriodo($fechaDesde, $fechaHasta);
        $totales    = $this->reporteRepository->getTotalesAcopios($fechaDesde, $fechaHasta);
        $acopiosMes = $this->reporteRepository->getAcopiosPorMes($fechaDesde, $fechaHasta);
        $totalPages = ceil($total / $perPage);

        $this->render('reportes/acopios-por-periodo', [
            'title'       => 'Acopios por Período',
            'acopios'     => $acopios,
            'total'       => $total,
            'totales'     => $totales,
            'acopiosMes'  => $acopiosMes,
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'page'        => $page,
            'totalPages'  => $totalPages,
            'perPage'     => $perPage,
            'module_css'  => 'reportes',
            'module_js'   => 'reportes'
        ]);
    }

    // ============================================
    // RF-08.5
    // ============================================
    public function productosMasVendidos() {
        $this->requireAuth();

        $fechaDesde = $this->getQuery('fecha_desde', date('Y-m-01'));
        $fechaHasta = $this->getQuery('fecha_hasta', date('Y-m-d'));
        $page       = (int)$this->getQuery('page', 1);
        $perPage    = 20;

        $productos  = $this->reporteRepository->getProductosMasVendidos($fechaDesde, $fechaHasta, $page, $perPage);
        $total      = $this->reporteRepository->countProductosMasVendidos($fechaDesde, $fechaHasta);
        $totalPages = ceil($total / $perPage);

        $this->render('reportes/productos-mas-vendidos', [
            'title'       => 'Productos Más Vendidos',
            'productos'   => $productos,
            'total'       => $total,
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'page'        => $page,
            'totalPages'  => $totalPages,
            'perPage'     => $perPage,
            'module_css'  => 'reportes',
            'module_js'   => 'reportes'
        ]);
    }

    // ============================================
    // RF-08.6
    // ============================================
    public function granosMasAcopiados() {
        $this->requireAuth();

        $fechaDesde = $this->getQuery('fecha_desde', date('Y-m-01'));
        $fechaHasta = $this->getQuery('fecha_hasta', date('Y-m-d'));
        $page       = (int)$this->getQuery('page', 1);
        $perPage    = 20;

        $granos     = $this->reporteRepository->getGranosMasAcopiados($fechaDesde, $fechaHasta, $page, $perPage);
        $total      = $this->reporteRepository->countGranosMasAcopiados($fechaDesde, $fechaHasta);
        $totalPages = ceil($total / $perPage);

        $this->render('reportes/granos-mas-acopiados', [
            'title'       => 'Granos Más Acopiados',
            'granos'      => $granos,
            'total'       => $total,
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'page'        => $page,
            'totalPages'  => $totalPages,
            'perPage'     => $perPage,
            'module_css'  => 'reportes',
            'module_js'   => 'reportes'
        ]);
    }

    // ============================================
    // RF-08.7
    // ============================================
    public function rentabilidadPorCliente() {
        $this->requireAuth();

        $fechaDesde = $this->getQuery('fecha_desde', date('Y-m-01'));
        $fechaHasta = $this->getQuery('fecha_hasta', date('Y-m-d'));
        $page       = (int)$this->getQuery('page', 1);
        $perPage    = 20;

        $clientes   = $this->reporteRepository->getRentabilidadPorCliente($fechaDesde, $fechaHasta, $page, $perPage);
        $total      = $this->reporteRepository->countRentabilidadPorCliente($fechaDesde, $fechaHasta);
        $totales    = $this->reporteRepository->getTotalesRentabilidad($fechaDesde, $fechaHasta);
        $totalPages = ceil($total / $perPage);

        $this->render('reportes/rentabilidad-por-cliente', [
            'title'       => 'Rentabilidad por Cliente',
            'clientes'    => $clientes,
            'total'       => $total,
            'totales'     => $totales,
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'page'        => $page,
            'totalPages'  => $totalPages,
            'perPage'     => $perPage,
            'module_css'  => 'reportes',
            'module_js'   => 'reportes'
        ]);
    }

    // ============================================
    // RF-08.8
    // ============================================
    public function estadoInventario() {
        $this->requireAuth();

        $search      = $this->getQuery('search', '');
        $categoria   = $this->getQuery('categoria_id', '');
        $estadoStock = $this->getQuery('estado_stock', '');
        $page        = (int)$this->getQuery('page', 1);
        $perPage     = 20;

        $filters = [];
        if (!empty($search))      $filters['search']      = $search;
        if (!empty($categoria))   $filters['categoria_id'] = $categoria;
        if (!empty($estadoStock)) $filters['estado_stock'] = $estadoStock;

        $productos  = $this->reporteRepository->getEstadoInventario($filters, $page, $perPage);
        $total      = $this->reporteRepository->countEstadoInventario($filters);
        $totales    = $this->reporteRepository->getTotalesInventario();
        $categorias = $this->reporteRepository->getCategorias();
        $totalPages = ceil($total / $perPage);

        $this->render('reportes/estado-inventario', [
            'title'       => 'Estado de Inventario',
            'productos'   => $productos,
            'total'       => $total,
            'totales'     => $totales,
            'categorias'  => $categorias,
            'search'      => $search,
            'categoria_id'=> $categoria,
            'estado_stock'=> $estadoStock,
            'page'        => $page,
            'totalPages'  => $totalPages,
            'perPage'     => $perPage,
            'module_css'  => 'reportes',
            'module_js'   => 'reportes'
        ]);
    }

    // ============================================
    // RF-08.9
    // ============================================
    public function movimientosInventario() {
        $this->requireAuth();

        $search      = $this->getQuery('search', '');
        $tipo        = $this->getQuery('tipo', '');
        $fechaDesde  = $this->getQuery('fecha_desde', date('Y-m-01'));
        $fechaHasta  = $this->getQuery('fecha_hasta', date('Y-m-d'));
        $page        = (int)$this->getQuery('page', 1);
        $perPage     = 20;

        $filters = [];
        if (!empty($search))     $filters['search']     = $search;
        if (!empty($tipo))       $filters['tipo']       = $tipo;
        if (!empty($fechaDesde)) $filters['fecha_desde'] = $fechaDesde;
        if (!empty($fechaHasta)) $filters['fecha_hasta'] = $fechaHasta;

        $movimientos = $this->reporteRepository->getMovimientosInventario($filters, $page, $perPage);
        $total       = $this->reporteRepository->countMovimientosInventario($filters);
        $totales     = $this->reporteRepository->getTotalesMovimientos($filters);
        $totalPages  = ceil($total / $perPage);

        $this->render('reportes/movimientos-inventario', [
            'title'       => 'Movimientos de Inventario',
            'movimientos' => $movimientos,
            'total'       => $total,
            'totales'     => $totales,
            'search'      => $search,
            'tipo'        => $tipo,
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'page'        => $page,
            'totalPages'  => $totalPages,
            'perPage'     => $perPage,
            'module_css'  => 'reportes',
            'module_js'   => 'reportes'
        ]);
    }
}