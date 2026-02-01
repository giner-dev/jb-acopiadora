<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/PagoService.php';

class PagoPDF extends TCPDF {
    private $footerText = '';

    public function setCustomFooter($text) {
        $this->footerText = $text;
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, $this->footerText, 0, 0, 'R');
    }
}

class PagoController extends Controller {
    private $pagoService;

    public function __construct() {
        parent::__construct();
        $this->pagoService = new PagoService();
    }

    public function index() {
        $this->requireAuth();

        $search          = $this->getQuery('search', '');
        $tipo            = $this->getQuery('tipo', '');
        $metodo_pago     = $this->getQuery('metodo_pago', '');
        $estado          = $this->getQuery('estado', '');
        $fecha_desde     = $this->getQuery('fecha_desde', '');
        $fecha_hasta     = $this->getQuery('fecha_hasta', '');
        $page            = (int)$this->getQuery('page', 1);
        $perPage         = 20;

        $filters = [];
        if (!empty($search))      $filters['search']      = $search;
        if (!empty($tipo))        $filters['tipo']        = $tipo;
        if (!empty($metodo_pago)) $filters['metodo_pago'] = $metodo_pago;
        if (!empty($estado))      $filters['estado']      = $estado;
        if (!empty($fecha_desde)) $filters['fecha_desde'] = $fecha_desde;
        if (!empty($fecha_hasta)) $filters['fecha_hasta'] = $fecha_hasta;

        $pagos          = $this->pagoService->obtenerTodos($filters, $page, $perPage);
        $totalPagos     = $this->pagoService->contarTotal($filters);
        $totales        = $this->pagoService->obtenerTotales($filters);
        $totalPages     = ceil($totalPagos / $perPage);

        $this->render('pagos/index', [
            'title'          => 'Pagos',
            'pagos'          => $pagos,
            'totalPagos'     => $totalPagos,
            'totales'        => $totales,
            'search'         => $search,
            'tipo'           => $tipo,
            'metodo_pago'    => $metodo_pago,
            'estado'         => $estado,
            'fecha_desde'    => $fecha_desde,
            'fecha_hasta'    => $fecha_hasta,
            'page'           => $page,
            'totalPages'     => $totalPages,
            'perPage'        => $perPage,
            'module_css'     => 'pagos',
            'module_js'      => 'pagos'
        ]);
    }

    public function crear() {
        $this->requireAuth();

        // Si viene con cliente_id desde URL (por ejemplo desde cuenta corriente)
        $clienteId = $this->getQuery('cliente_id', '');
        $clienteNombre = '';
        $saldoCliente = null;

        if (!empty($clienteId)) {
            $saldoCliente = $this->pagoService->obtenerSaldoCliente($clienteId);
            if ($saldoCliente) {
                $clienteNombre = $saldoCliente['nombres'] . ' ' . $saldoCliente['apellidos'] . ' - CI: ' . $saldoCliente['ci'];
            }
        }

        $this->render('pagos/crear', [
            'title'                   => 'Registrar Pago',
            'clientePreseleccionado'  => $clienteId,
            'clienteNombre'           => $clienteNombre,
            'saldoCliente'            => $saldoCliente,
            'codigoSugerido'          => $this->pagoService->generarCodigo(),
            'module_css'              => 'pagos',
            'module_js'               => 'pagos'
        ]);
    }

    public function guardar() {
        $this->requireAuth();
        $this->validateMethod('POST');

        $datos = [
            'cliente_id'          => $this->getPost('cliente_id'),
            'fecha'               => $this->getPost('fecha'),
            'tipo'                => $this->getPost('tipo'),
            'metodo_pago'         => $this->getPost('metodo_pago'),
            'monto'               => $this->getPost('monto'),
            'referencia_operacion'=> $this->getPost('referencia_operacion'),
            'concepto'            => $this->getPost('concepto')
        ];

        $resultado = $this->pagoService->crear($datos);

        if ($resultado['success']) {
            $this->setSuccess('Pago registrado correctamente. Código: ' . $resultado['codigo']);
            $this->redirect(url('pagos/ver/' . $resultado['id']));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('pagos/crear'));
        }
    }

    public function ver($id) {
        $this->requireAuth();

        $pago = $this->pagoService->obtenerPorId($id);

        if (!$pago) {
            $this->setError('Pago no encontrado');
            $this->redirect(url('pagos'));
            return;
        }

        // Obtener saldo actual del cliente para mostrar contexto
        $saldoCliente = $this->pagoService->obtenerSaldoCliente($pago->cliente_id);

        $this->render('pagos/ver', [
            'title'        => 'Detalle de Pago - ' . $pago->codigo,
            'pago'         => $pago,
            'saldoCliente' => $saldoCliente,
            'module_css'   => 'pagos',
            'module_js'    => 'pagos'
        ]);
    }

    public function anular($id) {
        $this->requireAuth();

        $pago = $this->pagoService->obtenerPorId($id);

        if (!$pago) {
            $this->setError('Pago no encontrado');
            $this->redirect(url('pagos'));
            return;
        }

        if ($pago->isAnulado()) {
            $this->setError('Este pago ya fue anulado');
            $this->redirect(url('pagos/ver/' . $id));
            return;
        }

        $resultado = $this->pagoService->anular($id);

        if ($resultado['success']) {
            $this->setSuccess('Pago anulado correctamente. Se revirtió el movimiento en cuenta corriente.');
            $this->redirect(url('pagos/ver/' . $id));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('pagos/ver/' . $id));
        }
    }

    /**
     * RF-07.3: Genera el recibo PDF de un pago individual.
     * Este es un comprobante de un solo pago, no el historial del cliente.
     */
    public function recibo($id) {
        $this->requireAuth();

        require_once __DIR__ . '/../../vendor/autoload.php';

        $pago = $this->pagoService->obtenerPorId($id);

        if (!$pago) {
            $this->setError('Pago no encontrado');
            $this->redirect(url('pagos'));
            return;
        }

        $pdf = new PagoPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('JB Acopiadora');
        $pdf->SetAuthor('JB Acopiadora');
        $pdf->SetTitle('Recibo de Pago - ' . $pago->codigo);
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage();
        $pdf->setCustomFooter('Generado el ' . date('d/m/Y H:i:s') . ' por ' . authUserFullName());

        // Logo
        $logoPath = __DIR__ . '/../../public/assets/images/logo_jb_acopiadora.png';
        if (file_exists($logoPath)) {
            $imagen = @imagecreatefrompng($logoPath);
            if ($imagen !== false) {
                $jpgTemp = sys_get_temp_dir() . '/logo_temp_pago.jpg';
                imagejpeg($imagen, $jpgTemp, 90);
                $anchoLogo = 50;
                $pdf->Image($jpgTemp, 20, 5, $anchoLogo, 0);
                imagedestroy($imagen);
                @unlink($jpgTemp);
            }
        }

        // Título del recibo
        $pdf->SetXY(75, 10);
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor(67, 63, 78);
        $pdf->Cell(0, 10, 'RECIBO DE PAGO', 0, 1, 'L');

        $pdf->SetXY(75, 20);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(108, 117, 125);
        $pdf->Cell(0, 6, 'Código: ' . $pago->codigo, 0, 1, 'L');

        $pdf->Ln(8);

        // Línea separadora
        $pdf->SetDrawColor(255, 208, 130);
        $pdf->SetLineWidth(0.8);
        $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Ln(6);

        // Estado del pago
        if ($pago->isAnulado()) {
            $pdf->SetFont('helvetica', 'B', 13);
            $pdf->SetTextColor(220, 53, 69);
            $pdf->Cell(0, 7, '*** ESTE PAGO FUE ANULADO ***', 0, 1, 'C');
            $pdf->Ln(4);
        }

        // Datos del pago en dos columnas
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(67, 63, 78);

        // Columna izquierda
        $pdf->SetXY(20, $pdf->GetY());
        $pdf->Cell(45, 7, 'Cliente:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(0, 7, $pago->getClienteNombreCompleto(), 0, 1);

        $pdf->SetXY(20, $pdf->GetY());
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(67, 63, 78);
        $pdf->Cell(45, 7, 'CI:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(0, 7, $pago->cliente_ci, 0, 1);

        if (!empty($pago->cliente_comunidad)) {
            $pdf->SetXY(20, $pdf->GetY());
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetTextColor(67, 63, 78);
            $pdf->Cell(45, 7, 'Comunidad:', 0, 0);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(60, 60, 60);
            $pdf->Cell(0, 7, $pago->cliente_comunidad, 0, 1);
        }

        $pdf->Ln(3);

        $pdf->SetXY(20, $pdf->GetY());
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(67, 63, 78);
        $pdf->Cell(45, 7, 'Fecha:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(0, 7, date('d/m/Y', strtotime($pago->fecha)), 0, 1);

        $pdf->SetXY(20, $pdf->GetY());
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(67, 63, 78);
        $pdf->Cell(45, 7, 'Tipo:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(0, 7, $pago->getTipoTexto(), 0, 1);

        $pdf->SetXY(20, $pdf->GetY());
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(67, 63, 78);
        $pdf->Cell(45, 7, 'Método de Pago:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(0, 7, $pago->getMetodoPagoTexto(), 0, 1);

        if (!empty($pago->referencia_operacion)) {
            $pdf->SetXY(20, $pdf->GetY());
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetTextColor(67, 63, 78);
            $pdf->Cell(45, 7, 'Referencia:', 0, 0);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(60, 60, 60);
            $pdf->Cell(0, 7, $pago->referencia_operacion, 0, 1);
        }

        if (!empty($pago->concepto)) {
            $pdf->SetXY(20, $pdf->GetY());
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetTextColor(67, 63, 78);
            $pdf->Cell(45, 7, 'Concepto:', 0, 0);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(60, 60, 60);
            $pdf->Cell(0, 7, $pago->concepto, 0, 1);
        }

        $pdf->Ln(5);

        // Línea separadora
        $pdf->SetDrawColor(255, 208, 130);
        $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Ln(5);

        // Monto destacado
        $pdf->SetFillColor(255, 208, 130);
        $pdf->SetDrawColor(67, 63, 78);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(67, 63, 78);
        $rectY = $pdf->GetY();
        $pdf->Cell(170, 18, '', 1, 1, 'C', true);
        $pdf->SetXY(20, $rectY + 2);
        $pdf->Cell(170, 7, 'MONTO', 0, 1, 'C');
        $pdf->SetXY(20, $rectY + 9);
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->Cell(170, 7, 'Bs ' . number_format($pago->monto, 2), 0, 1, 'C');

        $pdf->Ln(8);

        // Firma
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(25, $pdf->GetY() + 25, 85, $pdf->GetY() + 25);
        $pdf->Line(105, $pdf->GetY() + 25, 165, $pdf->GetY() + 25);

        $pdf->SetXY(25, $pdf->GetY() + 27);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(60, 5, 'Firma Cliente', 0, 0, 'C');
        $pdf->SetXY(105, $pdf->GetY());
        $pdf->Cell(60, 5, 'Firma Responsable JB', 0, 0, 'C');

        $pdf->Output('Recibo_' . $pago->codigo . '.pdf', 'I');
    }

    /**
     * Endpoint AJAX para obtener el saldo actual de un cliente.
     * Lo usa el formulario de crear pago para mostrar información en tiempo real.
     */
    public function saldoCliente() {
        $this->requireAuth();
        header('Content-Type: application/json');

        $clienteId = $this->getQuery('cliente_id', '');

        if (empty($clienteId)) {
            echo json_encode(['error' => 'Cliente no especificado']);
            exit;
        }

        $saldo = $this->pagoService->obtenerSaldoCliente($clienteId);

        if (!$saldo) {
            echo json_encode(['error' => 'Cliente no encontrado']);
            exit;
        }

        echo json_encode([
            'cliente_id' => $saldo['id_cliente'],
            'nombres'    => $saldo['nombres'],
            'apellidos'  => $saldo['apellidos'],
            'ci'         => $saldo['ci'],
            'saldo'      => floatval($saldo['saldo'])
        ]);
        exit;
    }

    /**
     * Endpoint AJAX para buscar clientes en el modal.
     * Retorna clientes con su saldo actual.
     */
    public function buscarClientes() {
        $this->requireAuth();
        header('Content-Type: application/json');

        $search  = $this->getQuery('search', '');
        $page    = (int)$this->getQuery('page', 1);
        $perPage = 10;

        $db = Database::getInstance();

        $sql = "SELECT 
                    c.id_cliente, c.ci, c.nombres, c.apellidos, c.comunidad,
                    COALESCE((
                        SELECT SUM(debe) - SUM(haber)
                        FROM cuenta_corriente
                        WHERE cliente_id = c.id_cliente
                    ), 0) as saldo
                FROM clientes c
                WHERE c.estado = 'activo'";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (c.nombres LIKE :s1 OR c.apellidos LIKE :s2 OR c.ci LIKE :s3)";
            $params['s1'] = '%' . $search . '%';
            $params['s2'] = '%' . $search . '%';
            $params['s3'] = '%' . $search . '%';
        }

        // Count total
        $countSql = str_replace(
            "SELECT \n                    c.id_cliente, c.ci, c.nombres, c.apellidos, c.comunidad,\n                    COALESCE((\n                        SELECT SUM(debe) - SUM(haber)\n                        FROM cuenta_corriente\n                        WHERE cliente_id = c.id_cliente\n                    ), 0) as saldo\n                FROM",
            "SELECT COUNT(*) as total FROM",
            $sql
        );

        $countResult = $db->queryOne($countSql, $params);
        $total = $countResult['total'] ?? 0;
        $totalPages = ceil($total / $perPage);

        // Paginar
        $offset = ($page - 1) * $perPage;
        $sql .= " ORDER BY c.nombres ASC LIMIT " . $perPage . " OFFSET " . $offset;

        $clientes = $db->query($sql, $params);

        echo json_encode([
            'clientes'   => $clientes,
            'page'       => $page,
            'totalPages' => $totalPages,
            'total'      => $total
        ]);
        exit;
    }
}