<?php
require_once __DIR__ . '/../models/Cliente.php';

class ClienteRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAllPaginated($filters = [], $page = 1, $perPage = 20) {
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT * FROM clientes WHERE 1=1";
    $params = [];

    if (!empty($filters['search'])) {
        // Usamos marcadores únicos para cada campo o nos aseguramos de que el driver lo soporte
        $sql .= " AND (ci LIKE :s1 OR nombres LIKE :s2 OR apellidos LIKE :s3 OR comunidad LIKE :s4)";
        $params['s1'] = '%' . $filters['search'] . '%';
        $params['s2'] = '%' . $filters['search'] . '%';
        $params['s3'] = '%' . $filters['search'] . '%';
        $params['s4'] = '%' . $filters['search'] . '%';
    }

    if (!empty($filters['estado'])) {
        $sql .= " AND estado = :estado";
        $params['estado'] = $filters['estado'];
    }

    // Al igual que con los productos, concatenamos LIMIT y OFFSET para evitar conflictos de tipos en PDO
    $sql .= " ORDER BY fecha_creacion DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

    // Ahora enviamos la consulta limpia a tu clase Database
    $results = $this->db->query($sql, $params);

    $clientes = [];
    foreach ($results as $row) {
        $clientes[] = new Cliente($row);
    }

    return $clientes;
}

    public function findById($id) {
        $sql = "SELECT * FROM clientes WHERE id_cliente = :id LIMIT 1";
        $result = $this->db->queryOne($sql, ['id' => $id]);
        
        if ($result) {
            return new Cliente($result);
        }
        
        return null;
    }

    public function findByCI($ci, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT * FROM clientes WHERE ci = :ci AND id_cliente != :id LIMIT 1";
            $result = $this->db->queryOne($sql, ['ci' => $ci, 'id' => $excludeId]);
        } else {
            $sql = "SELECT * FROM clientes WHERE ci = :ci LIMIT 1";
            $result = $this->db->queryOne($sql, ['ci' => $ci]);
        }
        
        if ($result) {
            return new Cliente($result);
        }
        
        return null;
    }

    public function create($data) {
        $sql = "INSERT INTO clientes (ci, nombres, apellidos, comunidad, telefono, estado) 
                VALUES (:ci, :nombres, :apellidos, :comunidad, :telefono, :estado)";
        
        return $this->db->insert($sql, $data);
    }

    public function update($id, $data) {
        $sql = "UPDATE clientes 
                SET ci = :ci, 
                    nombres = :nombres, 
                    apellidos = :apellidos, 
                    comunidad = :comunidad, 
                    telefono = :telefono, 
                    estado = :estado 
                WHERE id_cliente = :id";
        
        $data['id'] = $id;
        return $this->db->execute($sql, $data);
    }

    public function cambiarEstado($id, $nuevoEstado) {
        $sql = "UPDATE clientes SET estado = :estado WHERE id_cliente = :id";
        return $this->db->execute($sql, ['id' => $id, 'estado' => $nuevoEstado]);
    }

    public function delete($id) {
        $sql = "DELETE FROM clientes WHERE id_cliente = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM clientes WHERE 1=1";
        $params = [];
            
        if (!empty($filters['estado'])) {
            $sql .= " AND estado = :estado";
            $params['estado'] = $filters['estado'];
        }
        
        $result = $this->db->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function getActivos() {
        $sql = "SELECT * FROM clientes WHERE estado = 'activo' ORDER BY nombres ASC";
        $results = $this->db->query($sql);
        
        $clientes = [];
        foreach ($results as $row) {
            $clientes[] = new Cliente($row);
        }
        
        return $clientes;
    }

    public function existeCI($ci, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as total FROM clientes WHERE ci = :ci AND id_cliente != :id";
            $result = $this->db->queryOne($sql, ['ci' => $ci, 'id' => $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM clientes WHERE ci = :ci";
            $result = $this->db->queryOne($sql, ['ci' => $ci]);
        }
        
        return $result['total'] > 0;
    }

    public function getSaldoCuentaCorriente($clienteId) {
        $sql = "SELECT 
                    COALESCE(SUM(debe), 0) as total_debe,
                    COALESCE(SUM(haber), 0) as total_haber
                FROM cuenta_corriente 
                WHERE cliente_id = :cliente_id";

        $result = $this->db->queryOne($sql, ['cliente_id' => $clienteId]);

        if ($result) {
            $debe = (float)$result['total_debe'];
            $haber = (float)$result['total_haber'];

            // En cuentas corrientes agrícolas, habitualmente Saldo = Haber - Debe
            $saldo = $haber - $debe;

            return [
                'debe' => $debe,
                'haber' => $haber,
                'saldo' => $saldo
            ];
        }

        return [
            'debe' => 0,
            'haber' => 0,
            'saldo' => 0
        ];
    }
}