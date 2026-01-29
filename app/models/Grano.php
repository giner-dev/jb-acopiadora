<?php
class Grano {
    
    public $id_grano;
    public $nombre;
    public $unidad_id;
    public $descripcion;
    public $estado;
    public $fecha_creacion;
    public $fecha_actualizacion;
    
    public $unidad_codigo;
    public $unidad_nombre;
    public $precio_actual;
    public $fecha_precio;
    
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
            'id_grano' => $this->id_grano,
            'nombre' => $this->nombre,
            'unidad_id' => $this->unidad_id,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'fecha_creacion' => $this->fecha_creacion,
            'fecha_actualizacion' => $this->fecha_actualizacion
        ];
    }
    
    public function isActivo() {
        return $this->estado === 'activo';
    }
    
    public function tienePrecioActual() {
        return !empty($this->precio_actual) && !empty($this->fecha_precio);
    }
    
    public function getPrecioActualFormateado() {
        if (!$this->tienePrecioActual()) {
            return 'Sin precio';
        }
        return 'Bs ' . number_format($this->precio_actual, 2);
    }
    
    public function getPrecioVigencia() {
        if (!$this->tienePrecioActual()) {
            return 'No definido';
        }
        
        $fecha = new DateTime($this->fecha_precio);
        $hoy = new DateTime();
        
        if ($fecha->format('Y-m-d') === $hoy->format('Y-m-d')) {
            return 'Hoy';
        }
        
        $diff = $hoy->diff($fecha);
        
        if ($diff->days === 1) {
            return 'Hace 1 día';
        }
        
        return 'Hace ' . $diff->days . ' días';
    }
}