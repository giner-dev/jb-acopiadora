<?php
require_once __DIR__ . '/../models/Producto.php';

class ProductoRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAllPaginated($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT p.*, c.nombre as categoria_nombre, u.codigo as unidad_codigo, u.nombre as unidad_nombre
                FROM productos p
                LEFT JOIN categorias_producto c ON p.categoria_id = c.id_categoria
                LEFT JOIN unidades_medida u ON p.unidad_id = u.id_unidad
                WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (p.codigo LIKE :s1 OR p.nombre LIKE :s2)";
            $params['s1'] = '%' . $filters['search'] . '%';
            $params['s2'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND p.estado = :estado";
            $params['estado'] = $filters['estado'];
        }

        if (!empty($filters['categoria_id'])) {
            $sql .= " AND p.categoria_id = :categoria_id";
            $params['categoria_id'] = $filters['categoria_id'];
        }

        if (isset($filters['bajo_stock']) && $filters['bajo_stock'] == '1') {
            $sql .= " AND p.stock_actual <= p.stock_minimo";
        }

        $sql .= " ORDER BY p.fecha_creacion DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $results = $this->db->query($sql, $params);
        $productos = [];
        foreach ($results as $row) { $productos[] = new Producto($row); }
        return $productos;
    }

    public function findById($id) {
        $sql = "SELECT p.*, c.nombre as categoria_nombre, u.codigo as unidad_codigo, u.nombre as unidad_nombre
                FROM productos p
                LEFT JOIN categorias_producto c ON p.categoria_id = c.id_categoria
                LEFT JOIN unidades_medida u ON p.unidad_id = u.id_unidad
                WHERE p.id_producto = :id LIMIT 1";

        $result = $this->db->queryOne($sql, ['id' => $id]);
        return $result ? new Producto($result) : null;
    }

    public function findByCodigo($codigo, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT * FROM productos WHERE codigo = :codigo AND id_producto != :id LIMIT 1";
            $result = $this->db->queryOne($sql, ['codigo' => $codigo, 'id' => $excludeId]);
        } else {
            $sql = "SELECT * FROM productos WHERE codigo = :codigo LIMIT 1";
            $result = $this->db->queryOne($sql, ['codigo' => $codigo]);
        }
        
        if ($result) {
            return new Producto($result);
        }
        
        return null;
    }

    public function create($data) {
        $sql = "INSERT INTO productos (codigo, nombre, categoria_id, unidad_id, precio_venta, stock_actual, stock_minimo, estado) 
                VALUES (:codigo, :nombre, :categoria_id, :unidad_id, :precio_venta, :stock_actual, :stock_minimo, :estado)";
        
        return $this->db->insert($sql, $data);
    }

    public function update($id, $data) {
        $sql = "UPDATE productos 
                SET codigo = :codigo,
                    nombre = :nombre,
                    categoria_id = :categoria_id,
                    unidad_id = :unidad_id,
                    precio_venta = :precio_venta,
                    stock_minimo = :stock_minimo,
                    estado = :estado
                WHERE id_producto = :id";
        
        $data['id'] = $id;
        return $this->db->execute($sql, $data);
    }

    public function updateStock($id, $nuevoStock) {
        $sql = "UPDATE productos SET stock_actual = :stock WHERE id_producto = :id";
        return $this->db->execute($sql, ['id' => $id, 'stock' => $nuevoStock]);
    }

    public function cambiarEstado($id, $nuevoEstado) {
        $sql = "UPDATE productos SET estado = :estado WHERE id_producto = :id";
        return $this->db->execute($sql, ['id' => $id, 'estado' => $nuevoEstado]);
    }

    public function delete($id) {
        $sql = "DELETE FROM productos WHERE id_producto = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM productos WHERE 1=1";
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND (codigo LIKE :s1 OR nombre LIKE :s2)";
            $params['s1'] = '%' . $filters['search'] . '%';
            $params['s2'] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['estado'])) {
            $sql .= " AND estado = :estado";
            $params['estado'] = $filters['estado'];
        }
        
        if (!empty($filters['categoria_id'])) {
            $sql .= " AND categoria_id = :categoria_id";
            $params['categoria_id'] = $filters['categoria_id'];
        }
        
        if (isset($filters['bajo_stock']) && $filters['bajo_stock'] == '1') {
            $sql .= " AND stock_actual <= stock_minimo";
        }
        
        $result = $this->db->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function getActivos() {
        $sql = "SELECT 
                    p.*,
                    c.nombre as categoria_nombre,
                    u.codigo as unidad_codigo,
                    u.nombre as unidad_nombre
                FROM productos p
                LEFT JOIN categorias_producto c ON p.categoria_id = c.id_categoria
                LEFT JOIN unidades_medida u ON p.unidad_id = u.id_unidad
                WHERE p.estado = 'activo' 
                ORDER BY p.nombre ASC";
        
        $results = $this->db->query($sql);
        
        $productos = [];
        foreach ($results as $row) {
            $productos[] = new Producto($row);
        }
        
        return $productos;
    }

    public function existeCodigo($codigo, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as total FROM productos WHERE codigo = :codigo AND id_producto != :id";
            $result = $this->db->queryOne($sql, ['codigo' => $codigo, 'id' => $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM productos WHERE codigo = :codigo";
            $result = $this->db->queryOne($sql, ['codigo' => $codigo]);
        }
        
        return $result['total'] > 0;
    }

    public function tieneMovimientos($id) {
        $sql = "SELECT COUNT(*) as total FROM movimiento_inventario WHERE producto_id = :id";
        $result = $this->db->queryOne($sql, ['id' => $id]);
        return $result['total'] > 0;
    }

    public function getAllCategorias() {
        $sql = "SELECT * FROM categorias_producto WHERE estado = 'activo' ORDER BY id_categoria ASC";
        return $this->db->query($sql);
    }

    public function getAllUnidades() {
        $sql = "SELECT * FROM unidades_medida WHERE estado = 'activo' ORDER BY id_unidad ASC";
        return $this->db->query($sql);
    }
}