<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/ClienteService.php';

class ClienteController extends Controller {
    private $clienteService;

    public function __construct() {
        parent::__construct();
        $this->clienteService = new ClienteService();
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
    
        $clientes = $this->clienteService->obtenerTodos($filters, $page, $perPage);
        $totalClientes = $this->clienteService->contarTotal($filters);
        $totalActivos = $this->clienteService->contarTotal(['estado' => 'activo']);
        $totalInactivos = $this->clienteService->contarTotal(['estado' => 'inactivo']);
        
        $totalPages = ceil($totalClientes / $perPage);
    
        $this->render('clientes/index', [
            'title' => 'Gestión de Clientes',
            'clientes' => $clientes,
            'totalClientes' => $totalClientes,
            'totalActivos' => $totalActivos,
            'totalInactivos' => $totalInactivos,
            'search' => $search,
            'estado' => $estado,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage
        ]);
    }

    public function crear() {
        $this->requireAuth();

        $this->render('clientes/crear', [
            'title' => 'Nuevo Cliente'
        ]);
    }

    public function guardar() {
        $this->requireAuth();
        $this->validateMethod('POST');

        $token = $this->getPost('csrf_token');
        if (!verifyCsrfToken($token)) {
            $this->setError('Token de seguridad inválido');
            $this->redirect(url('clientes/crear'));
        }

        $datos = [
            'ci' => $this->getPost('ci'),
            'nombres' => $this->getPost('nombres'),
            'apellidos' => $this->getPost('apellidos'),
            'comunidad' => $this->getPost('comunidad'),
            'telefono' => $this->getPost('telefono')
        ];

        $resultado = $this->clienteService->crear($datos);

        if ($resultado['success']) {
            $this->setSuccess('Cliente registrado correctamente');
            $this->redirect(url('clientes'));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('clientes/crear'));
        }
    }

    public function editar($id) {
        $this->requireAuth();

        $cliente = $this->clienteService->obtenerPorId($id);

        if (!$cliente) {
            $this->setError('Cliente no encontrado');
            $this->redirect(url('clientes'));
            return;
        }

        $this->render('clientes/editar', [
            'title' => 'Editar Cliente',
            'cliente' => $cliente
        ]);
    }

    public function actualizar($id) {
        $this->requireAuth();
        $this->validateMethod('POST');

        $token = $this->getPost('csrf_token');
        if (!verifyCsrfToken($token)) {
            $this->setError('Token de seguridad inválido');
            $this->redirect(url('clientes/editar/' . $id));
        }

        $datos = [
            'ci' => $this->getPost('ci'),
            'nombres' => $this->getPost('nombres'),
            'apellidos' => $this->getPost('apellidos'),
            'comunidad' => $this->getPost('comunidad'),
            'telefono' => $this->getPost('telefono'),
            'estado' => $this->getPost('estado')
        ];

        $resultado = $this->clienteService->actualizar($id, $datos);

        if ($resultado['success']) {
            $this->setSuccess('Cliente actualizado correctamente');
            $this->redirect(url('clientes'));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('clientes/editar/' . $id));
        }
    }

    public function ver($id) {
        $this->requireAuth();

        $cliente = $this->clienteService->obtenerPorId($id);

        if (!$cliente) {
            $this->setError('Cliente no encontrado');
            $this->redirect(url('clientes'));
            return;
        }

        $saldo = $this->clienteService->obtenerSaldoCuentaCorriente($id);

        $this->render('clientes/ver', [
            'title' => 'Detalle del Cliente',
            'cliente' => $cliente,
            'saldo' => $saldo
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

        $resultado = $this->clienteService->cambiarEstado($id, $nuevoEstado);

        $this->json($resultado);
    }

    public function eliminar($id) {
        $this->requireAuth();
        $this->validateMethod('POST');

        $token = $this->getPost('csrf_token');
        if (!verifyCsrfToken($token)) {
            $this->json(['success' => false, 'message' => 'Token inválido'], 400);
        }

        $resultado = $this->clienteService->eliminar($id);

        $this->json($resultado);
    }
}