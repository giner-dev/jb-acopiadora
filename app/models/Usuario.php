<?php

class Usuario {
    
    public $id_usuario;
    public $rol_id;
    public $nombre_usuario;
    public $contrasenia;
    public $nombres;
    public $apellidos;
    public $correo;
    public $estado;
    public $fecha_creacion;
    public $ultimo_acceso;
    
    public $rol_nombre;
    
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
            'id_usuario' => $this->id_usuario,
            'rol_id' => $this->rol_id,
            'nombre_usuario' => $this->nombre_usuario,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'correo' => $this->correo,
            'estado' => $this->estado,
            'fecha_creacion' => $this->fecha_creacion,
            'ultimo_acceso' => $this->ultimo_acceso,
            'rol_nombre' => $this->rol_nombre
        ];
    }
    
    public function getNombreCompleto() {
        return trim($this->nombres . ' ' . $this->apellidos);
    }
    
    public function isActivo() {
        return $this->estado === 'activo';
    }
    
    public function verificarPassword($password) {
        return password_verify($password, $this->contrasenia);
    }
    
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}