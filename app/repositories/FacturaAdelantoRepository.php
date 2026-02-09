<?php
require_once __DIR__ . '/../models/FacturaAdelanto.php';

class FacturaAdelantoRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByFacturaId($facturaId, $soloActivos = true) {
        $sql = "SELECT 
                    fa.*,
                    u.nombre_usuario as usuario_nombre
                FROM facturas_adelantos fa
                LEFT JOIN usuarios u ON fa.usuario_id = u.id_usuario
                WHERE fa.factura_id = :factura_id";
        
        if ($soloActivos) {
            $sql .= " AND fa.estado = 'activo'";
        }
        
        $sql .= " ORDER BY fa.fecha ASC, fa.fecha_creacion ASC";
        
        $results = $this->db->query($sql, ['factura_id' => $facturaId]);
        
        $adelantos = [];
        foreach ($results as $row) {
            $adelantos[] = new FacturaAdelanto($row);
        }
        
        return $adelantos;
    }


    public function findByFacturaIdPaginated($facturaId, $page = 1, $perPage = 10, $soloActivos = true) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT 
                    fa.*,
                    u.nombre_usuario as usuario_nombre
                FROM facturas_adelantos fa
                LEFT JOIN usuarios u ON fa.usuario_id = u.id_usuario
                WHERE fa.factura_id = :factura_id";
        
        if ($soloActivos) {
            $sql .= " AND fa.estado = 'activo'";
        }
        
        $sql .= " ORDER BY fa.fecha DESC, fa.fecha_creacion DESC 
                  LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
        
        $results = $this->db->query($sql, ['factura_id' => $facturaId]);
        
        $adelantos = [];
        foreach ($results as $row) {
            $adelantos[] = new FacturaAdelanto($row);
        }
        
        return $adelantos;
    }
    
    public function countByFacturaId($facturaId, $soloActivos = true) {
        $sql = "SELECT COUNT(*) as total 
                FROM facturas_adelantos 
                WHERE factura_id = :factura_id";
        
        if ($soloActivos) {
            $sql .= " AND estado = 'activo'";
        }
        
        $result = $this->db->queryOne($sql, ['factura_id' => $facturaId]);
        return $result['total'] ?? 0;
    }

    public function findById($id) {
        $sql = "SELECT 
                    fa.*,
                    u.nombre_usuario as usuario_nombre
                FROM facturas_adelantos fa
                LEFT JOIN usuarios u ON fa.usuario_id = u.id_usuario
                WHERE fa.id_factura_adelanto = :id 
                LIMIT 1";
        
        $result = $this->db->queryOne($sql, ['id' => $id]);
        
        if ($result) {
            return new FacturaAdelanto($result);
        }
        
        return null;
    }

    public function getSumaAdelantos($facturaId) {
        $sql = "SELECT COALESCE(SUM(monto), 0) as total 
                FROM facturas_adelantos 
                WHERE factura_id = :factura_id 
                AND estado = 'activo'";
        
        $result = $this->db->queryOne($sql, ['factura_id' => $facturaId]);
        return $result['total'] ?? 0;
    }

    public function create($data) {
        $sql = "INSERT INTO facturas_adelantos (factura_id, monto, fecha, descripcion, usuario_id) 
                VALUES (:factura_id, :monto, :fecha, :descripcion, :usuario_id)";
        
        return $this->db->insert($sql, $data);
    }

    public function update($id, $data) {
        $sql = "UPDATE facturas_adelantos 
                SET monto = :monto,
                    fecha = :fecha,
                    descripcion = :descripcion
                WHERE id_factura_adelanto = :id";
        
        $params = [
            'id' => $id,
            'monto' => $data['monto'],
            'fecha' => $data['fecha'],
            'descripcion' => $data['descripcion']
        ];
        
        return $this->db->execute($sql, $params);
    }

    public function delete($id) {
        $sql = "UPDATE facturas_adelantos 
                SET estado = 'eliminado'
                WHERE id_factura_adelanto = :id";
        
        return $this->db->execute($sql, ['id' => $id]);
    }

    public function deleteByFacturaId($facturaId) {
        $sql = "UPDATE facturas_adelantos 
                SET estado = 'eliminado'
                WHERE factura_id = :factura_id";
        
        return $this->db->execute($sql, ['factura_id' => $facturaId]);
    }
}