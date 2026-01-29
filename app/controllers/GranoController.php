
<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/GranoService.php';

class GranoController extends Controller {
    private $granoService;

    public function __construct() {
        parent::__construct();
        $this->granoService = new GranoService();
    }

    public function index() {
        $this->requireAuth();

        $search = $this->getQuery('search', '');
        $estado = $this->getQuery('estado', '');
        $page = (int)$this->getQuery('page', 1);
        $perPage = 20;

        $filters = [];
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        if (!empty($estado)) {
            $filters['estado'] = $estado;
        }

        $granos = $this->granoService->obtenerTodos($filters, $page, $perPage);
        $totalGranos = $this->granoService->contarTotal($filters);
        $totalActivos = $this->granoService->contarTotal(['estado' => 'activo']);
        $totalInactivos = $this->granoService->contarTotal(['estado' => 'inactivo']);
        
        $totalPages = ceil($totalGranos / $perPage);

        $this->render('granos/index', [
            'title' => 'Gestión de Granos',
            'granos' => $granos,
            'totalGranos' => $totalGranos,
            'totalActivos' => $totalActivos,
            'totalInactivos' => $totalInactivos,
            'search' => $search,
            'estado' => $estado,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'module_css' => 'granos',
            'module_js' => 'granos'
        ]);
    }

    public function crear() {
        $this->requireAuth();

        $unidades = $this->granoService->obtenerUnidades();

        $this->render('granos/crear', [
            'title' => 'Nuevo Grano',
            'unidades' => $unidades,
            'module_css' => 'granos',
            'module_js' => 'granos'
        ]);
    }

    public function guardar() {
        $this->requireAuth();
        $this->validateMethod('POST');

        $token = $this->getPost('csrf_token');
        if (!verifyCsrfToken($token)) {
            $this->setError('Token de seguridad inválido');
            $this->redirect(url('granos/crear'));
        }

        $datos = [
            'nombre' => $this->getPost('nombre'),
            'unidad_id' => $this->getPost('unidad_id'),
            'descripcion' => $this->getPost('descripcion')
        ];

        $resultado = $this->granoService->crear($datos);

        if ($resultado['success']) {
            $this->setSuccess('Grano registrado correctamente');
            $this->redirect(url('granos'));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('granos/crear'));
        }
    }

    public function editar($id) {
        $this->requireAuth();

        $grano = $this->granoService->obtenerPorId($id);

        if (!$grano) {
            $this->setError('Grano no encontrado');
            $this->redirect(url('granos'));
            return;
        }

        $unidades = $this->granoService->obtenerUnidades();

        $this->render('granos/editar', [
            'title' => 'Editar Grano',
            'grano' => $grano,
            'unidades' => $unidades,
            'module_css' => 'granos',
            'module_js' => 'granos'
        ]);
    }

    public function actualizar($id) {
        $this->requireAuth();
        $this->validateMethod('POST');

        $token = $this->getPost('csrf_token');
        if (!verifyCsrfToken($token)) {
            $this->setError('Token de seguridad inválido');
            $this->redirect(url('granos/editar/' . $id));
        }

        $datos = [
            'nombre' => $this->getPost('nombre'),
            'unidad_id' => $this->getPost('unidad_id'),
            'descripcion' => $this->getPost('descripcion'),
            'estado' => $this->getPost('estado')
        ];

        $resultado = $this->granoService->actualizar($id, $datos);

        if ($resultado['success']) {
            $this->setSuccess('Grano actualizado correctamente');
            $this->redirect(url('granos'));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('granos/editar/' . $id));
        }
    }

    public function ver($id) {
        $this->requireAuth();

        $grano = $this->granoService->obtenerPorId($id);

        if (!$grano) {
            $this->setError('Grano no encontrado');
            $this->redirect(url('granos'));
            return;
        }

        $historialPrecios = $this->granoService->obtenerHistorialPrecios($id);

        $this->render('granos/ver', [
            'title' => 'Detalle del Grano',
            'grano' => $grano,
            'historialPrecios' => $historialPrecios,
            'module_css' => 'granos',
            'module_js' => 'granos'
        ]);
    }

    public function precios($id) {
        $this->requireAuth();

        $grano = $this->granoService->obtenerPorId($id);

        if (!$grano) {
            $this->setError('Grano no encontrado');
            $this->redirect(url('granos'));
            return;
        }

        $historialPrecios = $this->granoService->obtenerHistorialPrecios($id, 100);

        $this->render('granos/precios', [
            'title' => 'Histórico de Precios',
            'grano' => $grano,
            'historialPrecios' => $historialPrecios,
            'module_css' => 'granos',
            'module_js' => 'granos'
        ]);
    }

    public function registrarPrecio($id) {
        $this->requireAuth();
        $this->validateMethod('POST');

        $token = $this->getPost('csrf_token');
        if (!verifyCsrfToken($token)) {
            $this->json(['success' => false, 'message' => 'Token inválido'], 400);
        }

        $precio = $this->getPost('precio');
        $fecha = $this->getPost('fecha');

        $resultado = $this->granoService->registrarPrecio($id, $precio, $fecha);

        $this->json($resultado);
    }

    public function cambiarEstado($id) {
        $this->requireAuth();
        $this->validateMethod('POST');

        $token = $this->getPost('csrf_token');
        if (!verifyCsrfToken($token)) {
            $this->json(['success' => false, 'message' => 'Token inválido'], 400);
        }

        $nuevoEstado = $this->getPost('estado');

        $resultado = $this->granoService->cambiarEstado($id, $nuevoEstado);

        $this->json($resultado);
    }

    public function eliminar($id) {
        $this->requireAuth();
        $this->validateMethod('POST');

        $token = $this->getPost('csrf_token');
        if (!verifyCsrfToken($token)) {
            $this->json(['success' => false, 'message' => 'Token inválido'], 400);
        }

        $resultado = $this->granoService->eliminar($id);

        $this->json($resultado);
    }
}