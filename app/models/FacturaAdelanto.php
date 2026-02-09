<?php
class FacturaAdelanto {
    
    public $id_factura_adelanto;
    public $factura_id;
    public $monto;
    public $fecha;
    public $descripcion;
    public $estado;
    public $usuario_id;
    public $fecha_creacion;
    public $fecha_actualizacion;
    
    public $usuario_nombre;
    
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
            'id_factura_adelanto' => $this->id_factura_adelanto,
            'factura_id' => $this->factura_id,
            'monto' => $this->monto,
            'fecha' => $this->fecha,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'usuario_id' => $this->usuario_id
        ];
    }
    
    public function isActivo() {
        return $this->estado === 'activo';
    }
    
    public function isEliminado() {
        return $this->estado === 'eliminado';
    }
}