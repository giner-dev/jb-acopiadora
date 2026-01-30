<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/AcopioService.php';
require_once __DIR__ . '/../services/ClienteService.php';
require_once __DIR__ . '/../services/GranoService.php';

class AcopioPDF extends TCPDF {
    private $footerText = '';
    
    public function setCustomFooter($text) {
        $this->footerText = $text;
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, $this->footerText, 0, 0, 'R');
    }
}

class AcopioController extends Controller {
    private $acopioService;
    private $clienteService;
    private $granoService;

    public function __construct() {
        parent::__construct();
        $this->acopioService = new AcopioService();
        $this->clienteService = new ClienteService();
        $this->granoService = new GranoService();
    }

    public function index() {
        $this->requireAuth();

        $search = $this->getQuery('search', '');
        $estado = $this->getQuery('estado', '');
        $fecha_desde = $this->getQuery('fecha_desde', '');
        $fecha_hasta = $this->getQuery('fecha_hasta', '');
        $page = (int)$this->getQuery('page', 1);
        $perPage = 20;

        $filters = [];
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        if (!empty($estado)) {
            $filters['estado'] = $estado;
        }
        if (!empty($fecha_desde)) {
            $filters['fecha_desde'] = $fecha_desde;
        }
        if (!empty($fecha_hasta)) {
            $filters['fecha_hasta'] = $fecha_hasta;
        }

        $acopios = $this->acopioService->obtenerTodos($filters, $page, $perPage);
        $totalAcopios = $this->acopioService->contarTotal($filters);
        $totalActivos = $this->acopioService->contarTotal(['estado' => 'ACTIVO']);
        $totalAnulados = $this->acopioService->contarTotal(['estado' => 'ANULADO']);
        
        $totalPages = ceil($totalAcopios / $perPage);

        $this->render('acopios/index', [
            'title' => 'Gestión de Acopios',
            'acopios' => $acopios,
            'totalAcopios' => $totalAcopios,
            'totalActivos' => $totalActivos,
            'totalAnulados' => $totalAnulados,
            'search' => $search,
            'estado' => $estado,
            'fecha_desde' => $fecha_desde,
            'fecha_hasta' => $fecha_hasta,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'module_css' => 'acopios',
            'module_js' => 'acopios'
        ]);
    }

    public function crear() {
        $this->requireAuth();

        $proximoNumero = $this->acopioService->obtenerProximoNumero();

        $this->render('acopios/crear', [
            'title' => 'Nuevo Acopio',
            'proximoNumero' => $proximoNumero,
            'module_css' => 'acopios',
            'module_js' => 'acopios'
        ]);
    }

    public function buscarClientes() {
        $this->requireAuth();
        header('Content-Type: application/json');

        $search = $this->getQuery('search', '');
        
        $filters = [];
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        $filters['estado'] = 'activo';

        $clientes = $this->clienteService->obtenerTodos($filters, 1, 20);
        
        $clientesArray = [];
        foreach ($clientes as $cliente) {
            $clientesArray[] = [
                'id_cliente' => $cliente->id_cliente,
                'ci' => $cliente->ci,
                'nombres' => $cliente->nombres,
                'apellidos' => $cliente->apellidos,
                'comunidad' => $cliente->comunidad
            ];
        }

        echo json_encode(['clientes' => $clientesArray]);
        exit;
    }

    public function buscarGranos() {
        $this->requireAuth();
        header('Content-Type: application/json');

        $search = $this->getQuery('search', '');

        $filters = [];
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        $filters['estado'] = 'activo';

        $granos = $this->granoService->obtenerTodos($filters, 1, 20);

        $granosArray = [];
        foreach ($granos as $grano) {
            $granosArray[] = [
                'id_grano' => $grano->id_grano,
                'nombre' => $grano->nombre,
                'precio_actual' => $grano->precio_actual,
                'fecha_precio' => $grano->fecha_precio,
                'unidad_codigo' => $grano->unidad_codigo
            ];
        }

        echo json_encode(['granos' => $granosArray]);
        exit;
    }

    public function actualizarPrecioGrano() {
        $this->requireAuth();
        header('Content-Type: application/json');

        $granoId = $this->getPost('grano_id');
        $precio = $this->getPost('precio');
        $fecha = $this->getPost('fecha');

        if (empty($fecha)) {
            $fecha = date('Y-m-d');
        }

        $resultado = $this->granoService->registrarPrecio($granoId, $precio, $fecha);

        echo json_encode($resultado);
        exit;
    }

    public function guardar() {
        $this->requireAuth();
        $this->validateMethod('POST');

        $detallesJson = isset($_POST['detalles_json']) ? $_POST['detalles_json'] : '';
        $detallesJson = html_entity_decode($detallesJson, ENT_QUOTES, 'UTF-8');
        
        if (empty($detallesJson)) {
            $this->setError('No se recibieron granos. Por favor intente nuevamente.');
            $this->redirect(url('acopios/crear'));
            return;
        }
        
        $detalles = json_decode($detallesJson, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->setError('Error al decodificar los granos: ' . json_last_error_msg());
            $this->redirect(url('acopios/crear'));
            return;
        }

        if (empty($detalles) || !is_array($detalles) || count($detalles) === 0) {
            $this->setError('Debe agregar al menos un grano');
            $this->redirect(url('acopios/crear'));
            return;
        }

        $clienteId = $this->getPost('cliente_id');
        
        if (empty($clienteId)) {
            $this->setError('Debe seleccionar un cliente');
            $this->redirect(url('acopios/crear'));
            return;
        }

        $datos = [
            'codigo_manual' => $this->getPost('codigo_manual'),
            'cliente_id' => $clienteId,
            'fecha' => $this->getPost('fecha'),
            'observaciones' => $this->getPost('observaciones'),
            'detalles' => $detalles
        ];

        $resultado = $this->acopioService->crear($datos);

        if ($resultado['success']) {
            $this->setSuccess('Acopio creado correctamente: ' . $resultado['codigo']);
            $this->redirect(url('acopios/ver/' . $resultado['id']));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('acopios/crear'));
        }
    }

    public function ver($id) {
        $this->requireAuth();

        $acopio = $this->acopioService->obtenerPorId($id);

        if (!$acopio) {
            $this->setError('Acopio no encontrado');
            $this->redirect(url('acopios'));
            return;
        }

        $this->render('acopios/ver', [
            'title' => 'Detalle de Acopio',
            'acopio' => $acopio,
            'module_css' => 'acopios',
            'module_js' => 'acopios'
        ]);
    }

    public function anular($id) {
        $this->requireAuth();
        $this->validateMethod('POST');

        $motivo = $this->getPost('motivo');

        $resultado = $this->acopioService->anular($id, $motivo);

        $this->json($resultado);
    }

    public function imprimir($id) {
        $this->requireAuth();

        $acopio = $this->acopioService->obtenerPorId($id);

        if (!$acopio) {
            $this->setError('Acopio no encontrado');
            $this->redirect(url('acopios'));
            return;
        }

        $this->render('acopios/imprimir', [
            'acopio' => $acopio,
            'module_css' => 'acopios'
        ]);
    }

    public function exportarPdf($id) {
        $this->requireAuth();
    
        require_once __DIR__ . '/../../vendor/autoload.php';
    
        $acopio = $this->acopioService->obtenerPorId($id);
    
        if (!$acopio) {
            $this->setError('Acopio no encontrado');
            $this->redirect(url('acopios'));
            return;
        }
    
        $pdf = new AcopioPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
        $pdf->SetCreator('JB Acopiadora');
        $pdf->SetAuthor('JB Acopiadora');
        $pdf->SetTitle('Acopio ' . $acopio->codigo);
        $pdf->SetSubject('Comprobante de Acopio');
    
        $pdf->setPrintHeader(false);
    
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
    
        $pdf->AddPage();

        $pdf->setCustomFooter('Generado el ' . date('d/m/Y H:i:s') . ' por ' . authUserFullName());
    
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/jbacopiadora/public/assets/images/logo_jb_acopiadora.png';
        if (file_exists($logoPath)) {
            $imagen = @imagecreatefrompng($logoPath);
            if ($imagen !== false) {
                $jpgTemp = sys_get_temp_dir() . '/logo_temp.jpg';
                imagejpeg($imagen, $jpgTemp, 90);

                $anchoLogo = 80;
                $anchoPagina = $pdf->getPageWidth();
                $xCentrado = ($anchoPagina - $anchoLogo) / 2;

                imagedestroy($imagen);
                $pdf->Image($jpgTemp, $xCentrado, 15, $anchoLogo, 0);
                @unlink($jpgTemp);
            }
        }
    
        $pdf->Ln(15);
    
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'COMPROBANTE DE ACOPIO', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 6, $acopio->codigo, 0, 1, 'C');
        $pdf->Ln(10);
    
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(25, 6, 'Cliente:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $acopio->getClienteNombreCompleto(), 0, 1);
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(25, 6, 'CI:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $acopio->cliente_ci, 0, 1);
        
        $yInicio = $pdf->GetY();
        
        if (!empty($acopio->cliente_comunidad)) {
            $pdf->SetXY(110, $yInicio - 12);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(25, 6, 'Comunidad:', 0, 0);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, $acopio->cliente_comunidad, 0, 1);
        }
        
        $pdf->SetXY(110, $yInicio - 6);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(25, 6, 'Fecha:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, date('d/m/Y', strtotime($acopio->fecha)), 0, 1);
        
        $pdf->SetXY(15, $yInicio);

        $pdf->Ln(5);
    
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(255, 208, 130);
        $pdf->Cell(15, 8, 'N°', 1, 0, 'C', true);
        $pdf->Cell(70, 8, 'Grano', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Cantidad', 1, 0, 'C', true);
        $pdf->Cell(35, 8, 'P. Unitario', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Subtotal', 1, 1, 'C', true);
    
        $pdf->SetFont('helvetica', '', 9);
        $num = 1;
        foreach ($acopio->detalles as $detalle) {
            $pdf->Cell(15, 6, $num++, 1, 0, 'C');
            $pdf->Cell(70, 6, $detalle['grano_nombre'], 1, 0, 'L');
            $pdf->Cell(30, 6, number_format($detalle['cantidad'], 2) . ' ' . ($detalle['unidad_codigo'] ?? ''), 1, 0, 'C');
            $pdf->Cell(35, 6, 'Bs ' . number_format($detalle['precio_unitario'], 2), 1, 0, 'R');
            $pdf->Cell(30, 6, 'Bs ' . number_format($detalle['subtotal'], 2), 1, 1, 'R');
        }
    
        $pdf->Ln(3);
    
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(150, 7, 'TOTAL:', 0, 0, 'R');
        $pdf->Cell(30, 7, 'Bs ' . number_format($acopio->total, 2), 1, 1, 'R');
    
        if (!empty($acopio->observaciones)) {
            $pdf->Ln(10);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 6, 'Observaciones:', 0, 1);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->MultiCell(0, 5, $acopio->observaciones, 0, 'L');
        }
    
        $pdf->Ln(10);
    
        $pdf->Output('Acopio_' . $acopio->codigo . '.pdf', 'I');
    }

    public function exportarExcel() {
        $this->requireAuth();

        require_once __DIR__ . '/../../vendor/autoload.php';

        $search = $this->getQuery('search', '');
        $estado = $this->getQuery('estado', '');
        $fecha_desde = $this->getQuery('fecha_desde', '');
        $fecha_hasta = $this->getQuery('fecha_hasta', '');

        $filters = [];
        if (!empty($search)) $filters['search'] = $search;
        if (!empty($estado)) $filters['estado'] = $estado;
        if (!empty($fecha_desde)) $filters['fecha_desde'] = $fecha_desde;
        if (!empty($fecha_hasta)) $filters['fecha_hasta'] = $fecha_hasta;

        $acopios = $this->acopioService->obtenerTodos($filters, 1, 10000);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Acopios');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '433F4E']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFD082']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        $sheet->setCellValue('A1', 'Código');
        $sheet->setCellValue('B1', 'Fecha');
        $sheet->setCellValue('C1', 'Cliente');
        $sheet->setCellValue('D1', 'CI');
        $sheet->setCellValue('E1', 'Total');
        $sheet->setCellValue('F1', 'Estado');

        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($acopios as $acopio) {
            $sheet->setCellValue('A' . $row, $acopio->codigo);
            $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($acopio->fecha)));
            $sheet->setCellValue('C' . $row, $acopio->getClienteNombreCompleto());
            $sheet->setCellValue('D' . $row, $acopio->cliente_ci);
            $sheet->setCellValue('E' . $row, $acopio->total);
            $sheet->setCellValue('F' . $row, $acopio->getEstadoTexto());
            $row++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('E2:E' . ($row - 1))->getNumberFormat()
            ->setFormatCode('#,##0.00');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Acopios_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function editar($id) {
        $this->requireAuth();
    
        $acopio = $this->acopioService->obtenerPorId($id);
    
        if (!$acopio) {
            $this->setError('Acopio no encontrado');
            $this->redirect(url('acopios'));
            return;
        }
    
        if ($acopio->isAnulado()) {
            $this->setError('No se puede editar un acopio anulado');
            $this->redirect(url('acopios/ver/' . $id));
            return;
        }
    
        $this->render('acopios/editar', [
            'title' => 'Editar Acopio',
            'acopio' => $acopio,
            'module_css' => 'acopios',
            'module_js' => 'acopios'
        ]);
    }
    
    public function actualizar($id) {
        $this->requireAuth();
        $this->validateMethod('POST');
    
        $detallesJson = isset($_POST['detalles_json']) ? $_POST['detalles_json'] : '';
        $detallesJson = html_entity_decode($detallesJson, ENT_QUOTES, 'UTF-8');
        
        if (empty($detallesJson)) {
            $this->setError('No se recibieron granos. Por favor intente nuevamente.');
            $this->redirect(url('acopios/editar/' . $id));
            return;
        }
        
        $detalles = json_decode($detallesJson, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->setError('Error al decodificar los granos: ' . json_last_error_msg());
            $this->redirect(url('acopios/editar/' . $id));
            return;
        }
    
        if (empty($detalles) || !is_array($detalles) || count($detalles) === 0) {
            $this->setError('Debe agregar al menos un grano');
            $this->redirect(url('acopios/editar/' . $id));
            return;
        }
    
        $clienteId = $this->getPost('cliente_id');
        
        if (empty($clienteId)) {
            $this->setError('Debe seleccionar un cliente');
            $this->redirect(url('acopios/editar/' . $id));
            return;
        }
    
        $datos = [
            'cliente_id' => $clienteId,
            'fecha' => $this->getPost('fecha'),
            'observaciones' => $this->getPost('observaciones'),
            'detalles' => $detalles
        ];
    
        $resultado = $this->acopioService->editar($id, $datos);
    
        if ($resultado['success']) {
            $this->setSuccess('Acopio editado correctamente: ' . $resultado['codigo']);
            $this->redirect(url('acopios/ver/' . $resultado['id']));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('acopios/editar/' . $id));
        }
    }
}