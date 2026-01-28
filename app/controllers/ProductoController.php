<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/ProductoService.php';

class ProductoController extends Controller {
    private $productoService;

    public function __construct() {
        parent::__construct();
        $this->productoService = new ProductoService();
    }

    public function index() {
        $this->requireAuth();

        $search = $this->getQuery('search', '');
        $estado = $this->getQuery('estado', '');
        $categoria_id = $this->getQuery('categoria_id', '');
        $bajo_stock = $this->getQuery('bajo_stock', '');
        $page = (int)$this->getQuery('page', 1);
        $perPage = 20;

        $filters = [];
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        if (!empty($estado)) {
            $filters['estado'] = $estado;
        }
        if (!empty($categoria_id)) {
            $filters['categoria_id'] = $categoria_id;
        }
        if ($bajo_stock === '1') {
            $filters['bajo_stock'] = '1';
        }

        $productos = $this->productoService->obtenerTodos($filters, $page, $perPage);
        $totalProductos = $this->productoService->contarTotal($filters);
        $totalActivos = $this->productoService->contarTotal(['estado' => 'activo']);
        $totalInactivos = $this->productoService->contarTotal(['estado' => 'inactivo']);
        $totalBajoStock = $this->productoService->contarTotal(['bajo_stock' => '1']);
        $categorias = $this->productoService->obtenerCategorias();
        
        $totalPages = ceil($totalProductos / $perPage);

        $this->render('productos/index', [
            'title' => 'Gestión de Productos',
            'productos' => $productos,
            'totalProductos' => $totalProductos,
            'totalActivos' => $totalActivos,
            'totalInactivos' => $totalInactivos,
            'totalBajoStock' => $totalBajoStock,
            'categorias' => $categorias,
            'search' => $search,
            'estado' => $estado,
            'categoria_id' => $categoria_id,
            'bajo_stock' => $bajo_stock,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'module_css' => 'productos',
            'module_js' => 'productos'
        ]);
    }

    public function crear() {
        $this->requireAuth();

        $categorias = $this->productoService->obtenerCategorias();
        $unidades = $this->productoService->obtenerUnidades();
        $codigoSugerido = $this->productoService->generarCodigoAutomatico();

        $this->render('productos/crear', [
            'title' => 'Nuevo Producto',
            'categorias' => $categorias,
            'unidades' => $unidades,
            'codigoSugerido' => $codigoSugerido,
            'module_css' => 'productos',
            'module_js' => 'productos'
        ]);
    }

    public function guardar() {
        $this->requireAuth();
        $this->validateMethod('POST');

        $token = $this->getPost('csrf_token');
        if (!verifyCsrfToken($token)) {
            $this->setError('Token de seguridad inválido');
            $this->redirect(url('productos/crear'));
        }

        $datos = [
            'codigo' => $this->getPost('codigo'),
            'nombre' => $this->getPost('nombre'),
            'categoria_id' => $this->getPost('categoria_id'),
            'unidad_id' => $this->getPost('unidad_id'),
            'precio_venta' => $this->getPost('precio_venta'),
            'stock_actual' => $this->getPost('stock_actual'),
            'stock_minimo' => $this->getPost('stock_minimo'),
            'stock_ilimitado' => $this->getPost('stock_ilimitado')
        ];
        $resultado = $this->productoService->crear($datos);

        if ($resultado['success']) {
            $this->setSuccess('Producto registrado correctamente');
            $this->redirect(url('productos'));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('productos/crear'));
        }
    }

    public function editar($id) {
        $this->requireAuth();

        $producto = $this->productoService->obtenerPorId($id);

        if (!$producto) {
            $this->setError('Producto no encontrado');
            $this->redirect(url('productos'));
            return;
        }

        $categorias = $this->productoService->obtenerCategorias();
        $unidades = $this->productoService->obtenerUnidades();

        $this->render('productos/editar', [
            'title' => 'Editar Producto',
            'producto' => $producto,
            'categorias' => $categorias,
            'unidades' => $unidades,
            'module_css' => 'productos',
            'module_js' => 'productos'
        ]);
    }

    public function actualizar($id) {
        $this->requireAuth();
        $this->validateMethod('POST');

        $token = $this->getPost('csrf_token');
        if (!verifyCsrfToken($token)) {
            $this->setError('Token de seguridad inválido');
            $this->redirect(url('productos/editar/' . $id));
        }

        $datos = [
            'codigo' => $this->getPost('codigo'),
            'nombre' => $this->getPost('nombre'),
            'categoria_id' => $this->getPost('categoria_id'),
            'unidad_id' => $this->getPost('unidad_id'),
            'precio_venta' => $this->getPost('precio_venta'),
            'stock_actual' => $this->getPost('stock_actual'),
            'stock_minimo' => $this->getPost('stock_minimo'),
            'stock_ilimitado' => $this->getPost('stock_ilimitado'),
            'estado' => $this->getPost('estado')
        ];

        $resultado = $this->productoService->actualizar($id, $datos);

        if ($resultado['success']) {
            $this->setSuccess('Producto actualizado correctamente');
            $this->redirect(url('productos'));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('productos/editar/' . $id));
        }
    }

    public function ver($id) {
        $this->requireAuth();

        $producto = $this->productoService->obtenerPorId($id);

        if (!$producto) {
            $this->setError('Producto no encontrado');
            $this->redirect(url('productos'));
            return;
        }

        $this->render('productos/ver', [
            'title' => 'Detalle del Producto',
            'producto' => $producto,
            'module_css' => 'productos',
            'module_js' => 'productos'
        ]);
    }

    public function cambiarEstado($id) {
        $this->requireAuth();
        $this->validateMethod('POST');

        $token = $this->getPost('csrf_token');
        if (!verifyCsrfToken($token)) {
            $this->json(['success' => false, 'message' => 'Token inválido'], 400);
        }

        $nuevoEstado = $this->getPost('estado');

        $resultado = $this->productoService->cambiarEstado($id, $nuevoEstado);

        $this->json($resultado);
    }

    public function eliminar($id) {
        $this->requireAuth();
        $this->validateMethod('POST');

        $token = $this->getPost('csrf_token');
        if (!verifyCsrfToken($token)) {
            $this->json(['success' => false, 'message' => 'Token inválido'], 400);
        }

        $resultado = $this->productoService->eliminar($id);

        $this->json($resultado);
    }
}