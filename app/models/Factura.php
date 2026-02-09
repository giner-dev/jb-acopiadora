<?php

class Factura {
    
    public $id_factura;
    public $codigo;
    public $cliente_id;
    public $fecha;
    public $subtotal;
    public $total;
    public $adelanto;
    public $saldo;
    public $estado;
    public $motivo_anulacion;
    public $usuario_id;
    public $fecha_creacion;
    public $fecha_actualizacion;
    
    public $cliente_ci;
    public $cliente_nombres;
    public $cliente_apellidos;
    public $cliente_telefono;
    public $cliente_comunidad;
    public $usuario_nombre;
    
    public $detalles = [];
    public $adelantos = [];
    public $total_adelantos = 0;
    
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }
    
    public function hydrate($data) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    
    public function toArray() {
        return [
            'id_factura' => $this->id_factura,
            'codigo' => $this->codigo,
            'cliente_id' => $this->cliente_id,
            'fecha' => $this->fecha,
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'adelanto' => $this->adelanto,
            'saldo' => $this->saldo,
            'estado' => $this->estado,
            'motivo_anulacion' => $this->motivo_anulacion,
            'usuario_id' => $this->usuario_id
        ];
    }
    
    public function getClienteNombreCompleto() {
        return trim($this->cliente_nombres . ' ' . $this->cliente_apellidos);
    }
    
    public function isPendiente() {
        return $this->estado === 'PENDIENTE';
    }
    
    public function isPagada() {
        return $this->estado === 'PAGADA';
    }
    
    public function isAnulada() {
        return $this->estado === 'ANULADA';
    }
    
    public function tieneSaldoPendiente() {
        return $this->saldo > 0;
    }
    
    public function getPorcentajePagado() {
        if ($this->total == 0) return 0;
        return (($this->total - $this->saldo) / $this->total) * 100;
    }
    
    public function getEstadoBadgeClass() {
        switch ($this->estado) {
            case 'PAGADA':
                return 'badge-success';
            case 'PENDIENTE':
                return 'badge-warning';
            case 'PAGO_PARCIAL':
                return 'badge-info';
            case 'ANULADA':
                return 'badge-danger';
            case 'VENCIDA':
                return 'badge-danger';
            default:
                return 'badge-secondary';
        }
    }
    
    public function getEstadoTexto() {
        switch ($this->estado) {
            case 'PAGADA':
                return 'Pagada';
            case 'PENDIENTE':
                return 'Pendiente';
            case 'PAGO_PARCIAL':
                return 'Pago Parcial';
            case 'ANULADA':
                return 'Anulada';
            case 'VENCIDA':
                return 'Vencida';
            default:
                return $this->estado;
        }
    }
}