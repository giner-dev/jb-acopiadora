<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/FacturaService.php';
require_once __DIR__ . '/../services/ClienteService.php';
require_once __DIR__ . '/../services/ProductoService.php';

// Clase personalizada para PDF con footer
class FacturaPDF extends TCPDF {
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

class FacturaController extends Controller {
    private $facturaService;
    private $clienteService;
    private $productoService;

    public function __construct() {
        parent::__construct();
        $this->facturaService = new FacturaService();
        $this->clienteService = new ClienteService();
        $this->productoService = new ProductoService();
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

        $facturas = $this->facturaService->obtenerTodos($filters, $page, $perPage);
        $totalFacturas = $this->facturaService->contarTotal($filters);
        $totalPendientes = $this->facturaService->contarTotal(['estado' => 'PENDIENTE']);
        $totalPagadas = $this->facturaService->contarTotal(['estado' => 'PAGADA']);
        $totalAnuladas = $this->facturaService->contarTotal(['estado' => 'ANULADA']);
        
        $totalPages = ceil($totalFacturas / $perPage);

        $this->render('facturas/index', [
            'title' => 'Gestión de Facturas',
            'facturas' => $facturas,
            'totalFacturas' => $totalFacturas,
            'totalPendientes' => $totalPendientes,
            'totalPagadas' => $totalPagadas,
            'totalAnuladas' => $totalAnuladas,
            'search' => $search,
            'estado' => $estado,
            'fecha_desde' => $fecha_desde,
            'fecha_hasta' => $fecha_hasta,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'module_css' => 'facturas',
            'module_js' => 'facturas'
        ]);
    }

    public function crear() {
        $this->requireAuth();

        $proximoNumero = $this->facturaService->obtenerProximoNumero();

        $this->render('facturas/crear', [
            'title' => 'Nueva Factura',
            'proximoNumero' => $proximoNumero,
            'module_css' => 'facturas',
            'module_js' => 'facturas'
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

    public function buscarProductos() {
        $this->requireAuth();
        header('Content-Type: application/json');

        $search = $this->getQuery('search', '');
        
        $filters = [];
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        $filters['estado'] = 'activo';

        $productos = $this->productoService->obtenerTodos($filters, 1, 20);
        
        $productosArray = [];
        foreach ($productos as $producto) {
            $productosArray[] = [
                'id_producto' => $producto->id_producto,
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'precio_venta' => $producto->precio_venta,
                'stock_actual' => $producto->stock_actual,
                'unidad_codigo' => $producto->unidad_codigo
            ];
        }

        echo json_encode(['productos' => $productosArray]);
        exit;
    }

    public function guardar() {
        $this->requireAuth();
        $this->validateMethod('POST');

        $token = $this->getPost('csrf_token');
        if (!verifyCsrfToken($token)) {
            $this->setError('Token de seguridad inválido');
            $this->redirect(url('facturas/crear'));
            return;
        }

        // IMPORTANTE: Obtener el JSON directamente de $_POST sin sanitización
        $detallesJson = isset($_POST['detalles_json']) ? $_POST['detalles_json'] : '';
        
        // Decodificar HTML entities si fueron codificadas
        $detallesJson = html_entity_decode($detallesJson, ENT_QUOTES, 'UTF-8');
        
        logMessage("DEBUG - JSON después de html_entity_decode: " . $detallesJson, 'info');
        logMessage("DEBUG - Longitud JSON: " . strlen($detallesJson), 'info');
        
        if (empty($detallesJson)) {
            logMessage("ERROR: detalles_json está vacío", 'error');
            $this->setError('No se recibieron productos. Por favor intente nuevamente.');
            $this->redirect(url('facturas/crear'));
            return;
        }
        
        // Intentar decodificar
        $detalles = json_decode($detallesJson, true);
        $jsonError = json_last_error();
        
        logMessage("DEBUG - Código de error JSON: " . $jsonError, 'info');
        logMessage("DEBUG - Mensaje de error JSON: " . json_last_error_msg(), 'info');
        logMessage("DEBUG - Array decodificado: " . print_r($detalles, true), 'info');
        
        if ($jsonError !== JSON_ERROR_NONE) {
            logMessage("ERROR JSON: " . json_last_error_msg(), 'error');
            logMessage("ERROR JSON - Detalles completos: " . $detallesJson, 'error');
            $this->setError('Error al decodificar los productos: ' . json_last_error_msg());
            $this->redirect(url('facturas/crear'));
            return;
        }

        if (empty($detalles) || !is_array($detalles) || count($detalles) === 0) {
            logMessage("ERROR: Detalles vacío o inválido después de decodificar", 'error');
            $this->setError('Debe agregar al menos un producto');
            $this->redirect(url('facturas/crear'));
            return;
        }

        $clienteId = $this->getPost('cliente_id');
        logMessage("DEBUG - Cliente ID: " . $clienteId, 'info');
        
        if (empty($clienteId)) {
            logMessage("ERROR: Cliente ID vacío", 'error');
            $this->setError('Debe seleccionar un cliente');
            $this->redirect(url('facturas/crear'));
            return;
        }

        $datos = [
            'codigo_manual' => $this->getPost('codigo_manual'),
            'cliente_id' => $clienteId,
            'fecha' => $this->getPost('fecha'),
            'adelanto' => $this->getPost('adelanto', 0),
            'detalles' => $detalles
        ];

        logMessage("DEBUG - Datos completos para crear: " . print_r($datos, true), 'info');

        $resultado = $this->facturaService->crear($datos);

        if ($resultado['success']) {
            $this->setSuccess('Factura creada correctamente: ' . $resultado['codigo']);
            $this->redirect(url('facturas/ver/' . $resultado['id']));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('facturas/crear'));
        }
    }

    public function ver($id) {
        $this->requireAuth();

        $factura = $this->facturaService->obtenerPorId($id);

        if (!$factura) {
            $this->setError('Factura no encontrada');
            $this->redirect(url('facturas'));
            return;
        }

        $this->render('facturas/ver', [
            'title' => 'Detalle de Factura',
            'factura' => $factura,
            'module_css' => 'facturas',
            'module_js' => 'facturas'
        ]);
    }

    public function anular($id) {
        $this->requireAuth();
        $this->validateMethod('POST');

        $token = $this->getPost('csrf_token');
        if (!verifyCsrfToken($token)) {
            $this->json(['success' => false, 'message' => 'Token inválido'], 400);
        }

        $motivo = $this->getPost('motivo');

        $resultado = $this->facturaService->anular($id, $motivo);

        $this->json($resultado);
    }

    public function imprimir($id) {
        $this->requireAuth();

        $factura = $this->facturaService->obtenerPorId($id);

        if (!$factura) {
            $this->setError('Factura no encontrada');
            $this->redirect(url('facturas'));
            return;
        }

        $this->render('facturas/imprimir', [
            'factura' => $factura,
            'module_css' => 'facturas'
        ]);
    }

    public function exportarPdf($id) {
        $this->requireAuth();
    
        require_once __DIR__ . '/../../vendor/autoload.php';
    
        $factura = $this->facturaService->obtenerPorId($id);
    
        if (!$factura) {
            $this->setError('Factura no encontrada');
            $this->redirect(url('facturas'));
            return;
        }
    
        $pdf = new FacturaPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
        // Configuración de MetaDatos
        $pdf->SetCreator('JB Acopiadora');
        $pdf->SetAuthor('JB Acopiadora');
        $pdf->SetTitle('Factura ' . $factura->codigo);
        $pdf->SetSubject('Factura de Venta');
    
        $pdf->setPrintHeader(false);
    
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
    
        $pdf->AddPage();

        $pdf->setCustomFooter('Generado el ' . date('d/m/Y H:i:s') . ' por ' . authUserFullName());
    
        // Logo en la esquina superior izquierda (ruta corregida)
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/jbacopiadora/public/assets/images/logo_jb_acopiadora.png';
        if (file_exists($logoPath)) {
            // Convertir a JPEG temporalmente
            $imagen = @imagecreatefrompng($logoPath);
            if ($imagen !== false) {
                $jpgTemp = sys_get_temp_dir() . '/logo_temp.jpg';
                imagejpeg($imagen, $jpgTemp, 90);

                // Obtener dimensiones del logo para centrarlo
                $anchoLogo = 80; // Ancho deseado en mm
                $anchoPagina = $pdf->getPageWidth();
                $xCentrado = ($anchoPagina - $anchoLogo) / 2;

                imagedestroy($imagen);
                $pdf->Image($jpgTemp, $xCentrado, 15, $anchoLogo, 0);
                @unlink($jpgTemp);
            }
        }
    
        // Espacio para el logo antes del título
        $pdf->Ln(15);
    
        // Título centrado    
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'FACTURA DE VENTA', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 6, $factura->codigo, 0, 1, 'C');
        $pdf->Ln(10);
    
        // Información del cliente
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(25, 6, 'Cliente:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $factura->getClienteNombreCompleto(), 0, 1);
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(25, 6, 'CI:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $factura->cliente_ci, 0, 1);
        
        // Comunidad y Fecha a la derecha
        $yInicio = $pdf->GetY();
        
        if (!empty($factura->cliente_comunidad)) {
            $pdf->SetXY(110, $yInicio - 12);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(25, 6, 'Comunidad:', 0, 0);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, $factura->cliente_comunidad, 0, 1);
        }
        
        $pdf->SetXY(110, $yInicio - 6);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(25, 6, 'Fecha:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, date('d/m/Y', strtotime($factura->fecha)), 0, 1);
        
        $pdf->SetXY(15, $yInicio);

        $pdf->Ln(5);
    
        // Tabla de productos - Encabezado
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(255, 208, 130);
        $pdf->Cell(15, 8, 'N°', 1, 0, 'C', true);
        $pdf->Cell(70, 8, 'Producto', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Cantidad', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'P. Unitario', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Subtotal', 1, 1, 'C', true);
    
        // Tabla de productos - Detalles
        $pdf->SetFont('helvetica', '', 9);
        $num = 1;
        foreach ($factura->detalles as $detalle) {
            $pdf->Cell(15, 6, $num++, 1, 0, 'C');
            $pdf->Cell(70, 6, $detalle['producto_nombre'], 1, 0, 'L');
            $pdf->Cell(25, 6, number_format($detalle['cantidad'], 2) . ' ' . ($detalle['unidad_codigo'] ?? ''), 1, 0, 'C');
            $pdf->Cell(30, 6, 'Bs ' . number_format($detalle['precio_unitario'], 2), 1, 0, 'R');
            $pdf->Cell(40, 6, 'Bs ' . number_format($detalle['subtotal'], 2), 1, 1, 'R');
        }
    
        $pdf->Ln(3);
    
        // Total
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(140, 7, 'TOTAL:', 0, 0, 'R');
        $pdf->Cell(40, 7, 'Bs ' . number_format($factura->total, 2), 1, 1, 'R');
    
        // Adelanto y saldo si existe
        if ($factura->adelanto > 0) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(140, 6, 'Adelanto:', 0, 0, 'R');
            $pdf->Cell(40, 6, 'Bs ' . number_format($factura->adelanto, 2), 1, 1, 'R');
    
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(140, 6, 'Saldo Pendiente:', 0, 0, 'R');
            $pdf->Cell(40, 6, 'Bs ' . number_format($factura->saldo, 2), 1, 1, 'R');
        }
    
        $pdf->Ln(10);
    
        $pdf->Output('Factura_' . $factura->codigo . '.pdf', 'I');
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

        $facturas = $this->facturaService->obtenerTodos($filters, 1, 10000);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Facturas');

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
        $sheet->setCellValue('F1', 'Adelanto');
        $sheet->setCellValue('G1', 'Saldo');
        $sheet->setCellValue('H1', 'Estado');

        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($facturas as $factura) {
            $sheet->setCellValue('A' . $row, $factura->codigo);
            $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($factura->fecha)));
            $sheet->setCellValue('C' . $row, $factura->getClienteNombreCompleto());
            $sheet->setCellValue('D' . $row, $factura->cliente_ci);
            $sheet->setCellValue('E' . $row, $factura->total);
            $sheet->setCellValue('F' . $row, $factura->adelanto);
            $sheet->setCellValue('G' . $row, $factura->saldo);
            $sheet->setCellValue('H' . $row, $factura->getEstadoTexto());
            $row++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('E2:G' . ($row - 1))->getNumberFormat()
            ->setFormatCode('#,##0.00');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Facturas_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}