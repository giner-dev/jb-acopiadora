<?php

class CuentaCorriente {
    public $id_movimiento;
    public $cliente_id;
    public $fecha;
    public $tipo_movimiento;
    public $referencia_tipo;
    public $referencia_id;
    public $descripcion;
    public $debe;
    public $haber;
    public $saldo;
    public $usuario_id;
    public $fecha_creacion;
    
    public $cliente_ci;
    public $cliente_nombres;
    public $cliente_apellidos;
    public $cliente_comunidad;
    public $usuario_nombre;

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function getClienteNombreCompleto() {
        return trim(($this->cliente_nombres ?? '') . ' ' . ($this->cliente_apellidos ?? ''));
    }

    public function getTipoTexto() {
        $tipos = [
            'FACTURA' => 'Factura de Venta',
            'ACOPIO' => 'Acopio de Cosecha',
            'PAGO_CLIENTE' => 'Pago de Cliente',
            'PAGO_JB' => 'Pago de JB',
            'AJUSTE' => 'Ajuste',
            'INICIAL' => 'Saldo Inicial'
        ];
        
        return $tipos[$this->tipo_movimiento] ?? $this->tipo_movimiento;
    }

    public function getTipoBadgeClass() {
        $clases = [
            'FACTURA' => 'badge-danger',
            'ACOPIO' => 'badge-success',
            'PAGO_CLIENTE' => 'badge-primary',
            'PAGO_JB' => 'badge-warning',
            'AJUSTE' => 'badge-secondary',
            'INICIAL' => 'badge-info'
        ];
        
        return $clases[$this->tipo_movimiento] ?? 'badge-secondary';
    }

    public function getSaldoTexto() {
        $saldo = floatval($this->saldo);
        
        if ($saldo > 0) {
            return 'Cliente debe: Bs ' . number_format($saldo, 2);
        } elseif ($saldo < 0) {
            return 'JB debe: Bs ' . number_format(abs($saldo), 2);
        } else {
            return 'Saldo en Cero';
        }
    }

    public function getSaldoColor() {
        $saldo = floatval($this->saldo);
        
        if ($saldo > 0) {
            return 'text-danger';
        } elseif ($saldo < 0) {
            return 'text-success';
        } else {
            return 'text-muted';
        }
    }
}