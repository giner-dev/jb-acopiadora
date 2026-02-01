<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/CuentaCorrienteService.php';

class CuentaCorrientePDF extends TCPDF {
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

class CuentaCorrienteController extends Controller {
    private $cuentaCorrienteService;

    public function __construct() {
        parent::__construct();
        $this->cuentaCorrienteService = new CuentaCorrienteService();
    }

    public function index() {
        $this->requireAuth();

        $search = $this->getQuery('search', '');
        $cliente_id = $this->getQuery('cliente_id', '');
        $tipo_movimiento = $this->getQuery('tipo_movimiento', '');
        $fecha_desde = $this->getQuery('fecha_desde', '');
        $fecha_hasta = $this->getQuery('fecha_hasta', '');
        $page = (int)$this->getQuery('page', 1);
        $perPage = 20;

        $filters = [];
        if (!empty($search)) $filters['search'] = $search;
        if (!empty($cliente_id)) $filters['cliente_id'] = $cliente_id;
        if (!empty($tipo_movimiento)) $filters['tipo_movimiento'] = $tipo_movimiento;
        if (!empty($fecha_desde)) $filters['fecha_desde'] = $fecha_desde;
        if (!empty($fecha_hasta)) $filters['fecha_hasta'] = $fecha_hasta;

        $movimientos = $this->cuentaCorrienteService->obtenerTodos($filters, $page, $perPage);
        $totalMovimientos = $this->cuentaCorrienteService->contarTotal($filters);
        $totales = $this->cuentaCorrienteService->obtenerTotales($filters);
        
        $totalPages = ceil($totalMovimientos / $perPage);

        $clienteSeleccionado = null;
        if (!empty($cliente_id)) {
            $clienteSeleccionado = $this->cuentaCorrienteService->obtenerSaldoPorCliente($cliente_id);
        }

        $this->render('cuenta-corriente/index', [
            'title' => 'Cuenta Corriente',
            'movimientos' => $movimientos,
            'totalMovimientos' => $totalMovimientos,
            'totales' => $totales,
            'clienteSeleccionado' => $clienteSeleccionado,
            'search' => $search,
            'cliente_id' => $cliente_id,
            'tipo_movimiento' => $tipo_movimiento,
            'fecha_desde' => $fecha_desde,
            'fecha_hasta' => $fecha_hasta,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'module_css' => 'cuenta-corriente',
            'module_js' => 'cuenta-corriente'
        ]);
    }

    public function clientes() {
        $this->requireAuth();

        $clientesConSaldo = $this->cuentaCorrienteService->obtenerClientesConSaldo();

        $this->render('cuenta-corriente/clientes', [
            'title' => 'Clientes - Cuenta Corriente',
            'clientes' => $clientesConSaldo,
            'module_css' => 'cuenta-corriente',
            'module_js' => 'cuenta-corriente'
        ]);
    }

    public function verCliente($id) {
        $this->requireAuth();

        $cliente = $this->cuentaCorrienteService->obtenerSaldoPorCliente($id);
        
        if (!$cliente) {
            $this->setError('Cliente no encontrado');
            $this->redirect(url('cuenta-corriente/clientes'));
            return;
        }

        $movimientos = $this->cuentaCorrienteService->obtenerMovimientosPorCliente($id);

        $this->render('cuenta-corriente/ver-cliente', [
            'title' => 'Estado de Cuenta',
            'cliente' => $cliente,
            'movimientos' => $movimientos,
            'module_css' => 'cuenta-corriente',
            'module_js' => 'cuenta-corriente'
        ]);
    }

    public function exportarPdfCliente($id) {
        $this->requireAuth();
    
        require_once __DIR__ . '/../../vendor/autoload.php';
    
        $cliente = $this->cuentaCorrienteService->obtenerSaldoPorCliente($id);
        
        if (!$cliente) {
            $this->setError('Cliente no encontrado');
            $this->redirect(url('cuenta-corriente/clientes'));
            return;
        }

        $movimientos = $this->cuentaCorrienteService->obtenerMovimientosPorCliente($id);
    
        $pdf = new CuentaCorrientePDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
        $pdf->SetCreator('JB Acopiadora');
        $pdf->SetAuthor('JB Acopiadora');
        $pdf->SetTitle('Estado de Cuenta - ' . $cliente['nombres'] . ' ' . $cliente['apellidos']);
        $pdf->SetSubject('Estado de Cuenta Corriente');
    
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage();
        $pdf->setCustomFooter('Generado el ' . date('d/m/Y H:i:s') . ' por ' . authUserFullName());
    
        $logoPath = __DIR__ . '/../../public/assets/images/logo_jb_acopiadora.png';
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
        $pdf->Cell(0, 8, 'ESTADO DE CUENTA CORRIENTE', 0, 1, 'C');
        $pdf->Ln(10);
    
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(25, 6, 'Cliente:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $cliente['nombres'] . ' ' . $cliente['apellidos'], 0, 1);
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(25, 6, 'CI:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $cliente['ci'], 0, 1);

        if (!empty($cliente['comunidad'])) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(25, 6, 'Comunidad:', 0, 0);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, $cliente['comunidad'], 0, 1);
        }
        
        $pdf->Ln(5);
        
        $saldo = floatval($cliente['saldo']);
        if ($saldo > 0) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetTextColor(220, 53, 69);
            $pdf->Cell(0, 8, 'CLIENTE DEBE: Bs ' . number_format($saldo, 2), 0, 1, 'C');
        } elseif ($saldo < 0) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetTextColor(40, 167, 69);
            $pdf->Cell(0, 8, 'JB DEBE: Bs ' . number_format(abs($saldo), 2), 0, 1, 'C');
        } else {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetTextColor(108, 117, 125);
            $pdf->Cell(0, 8, 'SALDO EN CERO', 0, 1, 'C');
        }
        
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(5);
    
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(255, 208, 130);
        $pdf->Cell(22, 7, 'Fecha', 1, 0, 'C', true);
        $pdf->Cell(50, 7, 'Tipo', 1, 0, 'C', true);
        $pdf->Cell(28, 7, 'Debe', 1, 0, 'C', true);
        $pdf->Cell(28, 7, 'Haber', 1, 0, 'C', true);
        $pdf->Cell(28, 7, 'Saldo', 1, 1, 'C', true);
    
        $pdf->SetFont('helvetica', '', 8);
        $saldoAcumulado = 0;

        // Invertir para mostrar más antiguo primero
        $movOrden = array_reverse($movimientos);
        
        foreach ($movOrden as $mov) {
            $debe = floatval($mov->debe);
            $haber = floatval($mov->haber);
            $saldoAcumulado += ($debe - $haber);
            
            $pdf->Cell(22, 6, date('d/m/Y', strtotime($mov->fecha)), 1, 0, 'C');
            $pdf->Cell(50, 6, $mov->getTipoTexto(), 1, 0, 'L');
            $pdf->Cell(28, 6, $debe > 0 ? 'Bs ' . number_format($debe, 2) : '-', 1, 0, 'R');
            $pdf->Cell(28, 6, $haber > 0 ? 'Bs ' . number_format($haber, 2) : '-', 1, 0, 'R');
            $pdf->Cell(28, 6, 'Bs ' . number_format($saldoAcumulado, 2), 1, 1, 'R');
        }
    
        $pdf->Output('Estado_Cuenta_' . $cliente['ci'] . '.pdf', 'I');
    }

    public function exportarExcel() {
        $this->requireAuth();

        require_once __DIR__ . '/../../vendor/autoload.php';

        $search = $this->getQuery('search', '');
        $cliente_id = $this->getQuery('cliente_id', '');
        $tipo_movimiento = $this->getQuery('tipo_movimiento', '');
        $fecha_desde = $this->getQuery('fecha_desde', '');
        $fecha_hasta = $this->getQuery('fecha_hasta', '');

        $filters = [];
        if (!empty($search)) $filters['search'] = $search;
        if (!empty($cliente_id)) $filters['cliente_id'] = $cliente_id;
        if (!empty($tipo_movimiento)) $filters['tipo_movimiento'] = $tipo_movimiento;
        if (!empty($fecha_desde)) $filters['fecha_desde'] = $fecha_desde;
        if (!empty($fecha_hasta)) $filters['fecha_hasta'] = $fecha_hasta;

        $movimientos = $this->cuentaCorrienteService->obtenerTodos($filters, 1, 10000);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Cuenta Corriente');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '433F4E']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFD082']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        $sheet->setCellValue('A1', 'Fecha');
        $sheet->setCellValue('B1', 'Cliente');
        $sheet->setCellValue('C1', 'CI');
        $sheet->setCellValue('D1', 'Tipo');
        $sheet->setCellValue('E1', 'Descripción');
        $sheet->setCellValue('F1', 'Debe');
        $sheet->setCellValue('G1', 'Haber');
        $sheet->setCellValue('H1', 'Saldo');

        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        $row = 2;
        $saldoAcumulado = 0;
        
        foreach ($movimientos as $mov) {
            $debe = floatval($mov->debe);
            $haber = floatval($mov->haber);
            $saldoAcumulado += ($debe - $haber);
            
            $sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($mov->fecha)));
            $sheet->setCellValue('B' . $row, $mov->getClienteNombreCompleto());
            $sheet->setCellValue('C' . $row, $mov->cliente_ci);
            $sheet->setCellValue('D' . $row, $mov->getTipoTexto());
            $sheet->setCellValue('E' . $row, $mov->descripcion);
            $sheet->setCellValue('F' . $row, $debe);
            $sheet->setCellValue('G' . $row, $haber);
            $sheet->setCellValue('H' . $row, $saldoAcumulado);
            $row++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('F2:H' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0.00');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Cuenta_Corriente_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}