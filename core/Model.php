<?php

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function all($orderBy = null) {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        return $this->db->query($sql);
    }
    
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        return $this->db->queryOne($sql, [$id]);
    }
    
    public function where($conditions, $orderBy = null) {
        $sql = "SELECT * FROM {$this->table} WHERE ";
        
        $whereClauses = [];
        $params = [];
        
        foreach ($conditions as $field => $value) {
            $whereClauses[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $sql .= implode(' AND ', $whereClauses);
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        return $this->db->query($sql, $params);
    }
    
    public function first($conditions) {
        $sql = "SELECT * FROM {$this->table} WHERE ";
        
        $whereClauses = [];
        $params = [];
        
        foreach ($conditions as $field => $value) {
            $whereClauses[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $sql .= implode(' AND ', $whereClauses) . " LIMIT 1";
        
        return $this->db->queryOne($sql, $params);
    }
    
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE ";
            
            $whereClauses = [];
            $params = [];
            
            foreach ($conditions as $field => $value) {
                $whereClauses[] = "{$field} = ?";
                $params[] = $value;
            }
            
            $sql .= implode(' AND ', $whereClauses);
            
            $result = $this->db->queryOne($sql, $params);
        } else {
            $result = $this->db->queryOne($sql);
        }
        
        return $result['total'] ?? 0;
    }
    
    public function exists($conditions) {
        return $this->count($conditions) > 0;
    }
}