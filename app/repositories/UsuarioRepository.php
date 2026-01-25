<?php
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByUsername($username) {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                INNER JOIN roles r ON u.rol_id = r.id_rol 
                WHERE u.nombre_usuario = :username 
                AND u.estado = 'activo'
                LIMIT 1";
        
        $result = $this->db->queryOne($sql, ['username' => $username]);

        if ($result) {
            return new Usuario($result);
        }

        return null;
    }

    public function findById($id) {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                INNER JOIN roles r ON u.rol_id = r.id_rol 
                WHERE u.id_usuario = :id 
                AND u.estado = 'activo'
                LIMIT 1";
        
        $result = $this->db->queryOne($sql, ['id' => $id]);
        
        if ($result) {
            return new Usuario($result);
        }
        
        return null;
    }

    public function findAll() {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                INNER JOIN roles r ON u.rol_id = r.id_rol 
                ORDER BY u.fecha_creacion DESC";
        
        $results = $this->db->query($sql);
        
        $usuarios = [];
        foreach ($results as $row) {
            $usuarios[] = new Usuario($row);
        }
        
        return $usuarios;
    }

    public function findActivos() {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                INNER JOIN roles r ON u.rol_id = r.id_rol 
                WHERE u.estado = 'activo' 
                ORDER BY u.nombres ASC";
        
        $results = $this->db->query($sql);
        
        $usuarios = [];
        foreach ($results as $row) {
            $usuarios[] = new Usuario($row);
        }
        
        return $usuarios;
    }

    public function create($data) {
        $sql = "INSERT INTO usuarios (rol_id, nombre_usuario, contrasenia, nombres, apellidos, correo, estado) 
                VALUES (:rol_id, :nombre_usuario, :contrasenia, :nombres, :apellidos, :correo, :estado)";
        
        return $this->db->insert($sql, $data);
    }

    public function update($id, $data) {
        $sql = "UPDATE usuarios 
                SET rol_id = :rol_id, 
                    nombre_usuario = :nombre_usuario, 
                    nombres = :nombres, 
                    apellidos = :apellidos, 
                    correo = :correo, 
                    estado = :estado 
                WHERE id_usuario = :id";
        
        $data['id'] = $id;
        return $this->db->execute($sql, $data);
    }

    public function updatePassword($id, $hashedPassword) {
        $sql = "UPDATE usuarios SET contrasenia = :password WHERE id_usuario = :id";
        return $this->db->execute($sql, ['id' => $id, 'password' => $hashedPassword]);
    }

    public function updateLastAccess($id) {
        $sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    public function cambiarEstado($id, $nuevoEstado) {
        $sql = "UPDATE usuarios SET estado = :estado WHERE id_usuario = :id";
        return $this->db->execute($sql, ['id' => $id, 'estado' => $nuevoEstado]);
    }

    public function existeUsername($username, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE nombre_usuario = :username AND id_usuario != :id";
            $result = $this->db->queryOne($sql, ['username' => $username, 'id' => $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE nombre_usuario = :username";
            $result = $this->db->queryOne($sql, ['username' => $username]);
        }
        
        return $result['total'] > 0;
    }

    public function existeCorreo($correo, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE correo = :correo AND id_usuario != :id";
            $result = $this->db->queryOne($sql, ['correo' => $correo, 'id' => $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE correo = :correo";
            $result = $this->db->queryOne($sql, ['correo' => $correo]);
        }
        
        return $result['total'] > 0;
    }

    public function count() {
        $sql = "SELECT COUNT(*) as total FROM usuarios";
        $result = $this->db->queryOne($sql);
        return $result['total'];
    }

    public function findByRol($rolId) {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                INNER JOIN roles r ON u.rol_id = r.id_rol 
                WHERE u.rol_id = :rolId 
                ORDER BY u.nombres ASC";
        
        $results = $this->db->query($sql, ['rolId' => $rolId]);
        
        $usuarios = [];
        foreach ($results as $row) {
            $usuarios[] = new Usuario($row);
        }
        
        return $usuarios;
    }
}