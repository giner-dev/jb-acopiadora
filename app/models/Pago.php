<?php

class Pago {
    public $id_pago;
    public $codigo;
    public $cliente_id;
    public $fecha;
    public $tipo;
    public $metodo_pago;
    public $monto;
    public $referencia_operacion;
    public $concepto;
    public $estado;
    public $usuario_id;
    public $fecha_creacion;

    // Campos de join
    public $cliente_nombres;
    public $cliente_apellidos;
    public $cliente_ci;
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
        return $this->tipo === 'PAGO_JB' ? 'Pago de JB a Cliente' : 'Pago de Cliente a JB';
    }

    public function getTipoBadgeClass() {
        return $this->tipo === 'PAGO_JB' ? 'badge-warning' : 'badge-primary';
    }

    public function getMetodoPagoTexto() {
        $metodos = [
            'EFECTIVO'      => 'Efectivo',
            'TRANSFERENCIA' => 'Transferencia',
            'CHEQUE'        => 'Cheque',
            'DEPOSITO'      => 'Depósito',
            'OTRO'          => 'Otro'
        ];
        return $metodos[$this->metodo_pago] ?? $this->metodo_pago;
    }

    public function isAnulado() {
        return $this->estado === 'ANULADO';
    }

    public function isCompletado() {
        return $this->estado === 'COMPLETADO';
    }
}