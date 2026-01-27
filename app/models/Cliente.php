<?php

class Cliente {
    
    public $id_cliente;
    public $ci;
    public $nombres;
    public $apellidos;
    public $comunidad;
    public $telefono;
    public $estado;
    public $fecha_creacion;
    
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
            'id_cliente' => $this->id_cliente,
            'ci' => $this->ci,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'comunidad' => $this->comunidad,
            'telefono' => $this->telefono,
            'estado' => $this->estado,
            'fecha_creacion' => $this->fecha_creacion
        ];
    }
    
    public function getNombreCompleto() {
        return trim($this->nombres . ' ' . $this->apellidos);
    }
    
    public function isActivo() {
        return $this->estado === 'activo';
    }
}