<?php
class Acopio {
    
    public $id_acopio;
    public $codigo;
    public $cliente_id;
    public $fecha;
    public $subtotal;
    public $total;
    public $observaciones;
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
            'id_acopio' => $this->id_acopio,
            'codigo' => $this->codigo,
            'cliente_id' => $this->cliente_id,
            'fecha' => $this->fecha,
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'observaciones' => $this->observaciones,
            'estado' => $this->estado,
            'usuario_id' => $this->usuario_id
        ];
    }
    
    public function getClienteNombreCompleto() {
        return trim($this->cliente_nombres . ' ' . $this->cliente_apellidos);
    }
    
    public function isAnulado() {
        return $this->estado === 'ANULADO';
    }
    
    public function isActivo() {
        return $this->estado === 'ACTIVO';
    }
    
    public function getEstadoTexto() {
        switch ($this->estado) {
            case 'ACTIVO':
                return 'Activo';
            case 'ANULADO':
                return 'Anulado';
            default:
                return 'Desconocido';
        }
    }
    
    public function getEstadoBadgeClass() {
        switch ($this->estado) {
            case 'ACTIVO':
                return 'badge-success';
            case 'ANULADO':
                return 'badge-danger';
            default:
                return 'badge-secondary';
        }
    }
}