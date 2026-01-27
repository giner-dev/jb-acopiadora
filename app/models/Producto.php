<?php

class Producto {
    
    public $id_producto;
    public $codigo;
    public $nombre;
    public $categoria_id;
    public $unidad_id;
    public $precio_venta;
    public $stock_actual;
    public $stock_minimo;
    public $estado;
    public $fecha_creacion;
    public $fecha_actualizacion;
    
    public $categoria_nombre;
    public $unidad_codigo;
    public $unidad_nombre;
    
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
            'id_producto' => $this->id_producto,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'categoria_id' => $this->categoria_id,
            'unidad_id' => $this->unidad_id,
            'precio_venta' => $this->precio_venta,
            'stock_actual' => $this->stock_actual,
            'stock_minimo' => $this->stock_minimo,
            'estado' => $this->estado,
            'fecha_creacion' => $this->fecha_creacion,
            'fecha_actualizacion' => $this->fecha_actualizacion
        ];
    }
    
    public function isActivo() {
        return $this->estado === 'activo';
    }
    
    public function tieneBajoStock() {
        return $this->stock_actual <= $this->stock_minimo;
    }
    
    public function sinStock() {
        return $this->stock_actual <= 0;
    }
    
    public function getStockStatus() {
        if ($this->sinStock()) {
            return 'sin_stock';
        } elseif ($this->tieneBajoStock()) {
            return 'bajo_stock';
        }
        return 'normal';
    }
}