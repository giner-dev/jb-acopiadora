<?php
require_once __DIR__ . '/../models/Grano.php';

class GranoRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAllPaginated($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT 
                    g.*,
                    u.codigo as unidad_codigo,
                    u.nombre as unidad_nombre,
                    (SELECT pg.precio 
                     FROM precio_grano pg 
                     WHERE pg.grano_id = g.id_grano 
                     ORDER BY pg.fecha DESC 
                     LIMIT 1) as precio_actual,
                    (SELECT pg.fecha 
                     FROM precio_grano pg 
                     WHERE pg.grano_id = g.id_grano 
                     ORDER BY pg.fecha DESC 
                     LIMIT 1) as fecha_precio
                FROM granos g
                LEFT JOIN unidades_medida u ON g.unidad_id = u.id_unidad
                WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND g.nombre LIKE :search";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND g.estado = :estado";
            $params['estado'] = $filters['estado'];
        }

        $sql .= " ORDER BY g.nombre ASC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $results = $this->db->query($sql, $params);
        $granos = [];
        foreach ($results as $row) {
            $granos[] = new Grano($row);
        }
        return $granos;
    }

    public function findById($id) {
        $sql = "SELECT 
                    g.*,
                    u.codigo as unidad_codigo,
                    u.nombre as unidad_nombre,
                    (SELECT pg.precio 
                     FROM precio_grano pg 
                     WHERE pg.grano_id = g.id_grano 
                     ORDER BY pg.fecha DESC 
                     LIMIT 1) as precio_actual,
                    (SELECT pg.fecha 
                     FROM precio_grano pg 
                     WHERE pg.grano_id = g.id_grano 
                     ORDER BY pg.fecha DESC 
                     LIMIT 1) as fecha_precio
                FROM granos g
                LEFT JOIN unidades_medida u ON g.unidad_id = u.id_unidad
                WHERE g.id_grano = :id 
                LIMIT 1";

        $result = $this->db->queryOne($sql, ['id' => $id]);
        return $result ? new Grano($result) : null;
    }

    public function create($data) {
        $sql = "INSERT INTO granos (nombre, unidad_id, descripcion, estado) 
                VALUES (:nombre, :unidad_id, :descripcion, :estado)";
        
        return $this->db->insert($sql, $data);
    }

    public function update($id, $data) {
        $sql = "UPDATE granos 
                SET nombre = :nombre,
                    unidad_id = :unidad_id,
                    descripcion = :descripcion,
                    estado = :estado
                WHERE id_grano = :id";
        
        $data['id'] = $id;
        return $this->db->execute($sql, $data);
    }

    public function delete($id) {
        $sql = "DELETE FROM granos WHERE id_grano = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    public function cambiarEstado($id, $nuevoEstado) {
        $sql = "UPDATE granos SET estado = :estado WHERE id_grano = :id";
        return $this->db->execute($sql, ['id' => $id, 'estado' => $nuevoEstado]);
    }

    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM granos WHERE 1=1";
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND nombre LIKE :search";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['estado'])) {
            $sql .= " AND estado = :estado";
            $params['estado'] = $filters['estado'];
        }
        
        $result = $this->db->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function getActivos() {
        $sql = "SELECT 
                    g.*,
                    u.codigo as unidad_codigo,
                    u.nombre as unidad_nombre,
                    (SELECT pg.precio 
                     FROM precio_grano pg 
                     WHERE pg.grano_id = g.id_grano 
                     ORDER BY pg.fecha DESC 
                     LIMIT 1) as precio_actual
                FROM granos g
                LEFT JOIN unidades_medida u ON g.unidad_id = u.id_unidad
                WHERE g.estado = 'activo' 
                ORDER BY g.nombre ASC";
        
        $results = $this->db->query($sql);
        
        $granos = [];
        foreach ($results as $row) {
            $granos[] = new Grano($row);
        }
        
        return $granos;
    }

    public function existeNombre($nombre, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as total FROM granos WHERE nombre = :nombre AND id_grano != :id";
            $result = $this->db->queryOne($sql, ['nombre' => $nombre, 'id' => $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM granos WHERE nombre = :nombre";
            $result = $this->db->queryOne($sql, ['nombre' => $nombre]);
        }
        
        return $result['total'] > 0;
    }

    public function tieneAcopios($id) {
        $sql = "SELECT COUNT(*) as total FROM acopios_detalle WHERE grano_id = :id";
        $result = $this->db->queryOne($sql, ['id' => $id]);
        return $result['total'] > 0;
    }

    public function getAllUnidades() {
        $sql = "SELECT * FROM unidades_medida WHERE estado = 'activo' ORDER BY nombre ASC";
        return $this->db->query($sql);
    }

    public function getPrecioActual($granoId) {
        $sql = "SELECT precio, fecha 
                FROM precio_grano 
                WHERE grano_id = :grano_id 
                ORDER BY fecha DESC 
                LIMIT 1";
        
        return $this->db->queryOne($sql, ['grano_id' => $granoId]);
    }

    public function getHistorialPrecios($granoId, $limit = 30) {
        $sql = "SELECT 
                    pg.*,
                    u.nombre_usuario
                FROM precio_grano pg
                LEFT JOIN usuarios u ON pg.usuario_id = u.id_usuario
                WHERE pg.grano_id = :grano_id
                ORDER BY pg.fecha DESC
                LIMIT :limit";
        
        return $this->db->query($sql, [
            'grano_id' => $granoId,
            'limit' => $limit
        ]);
    }

    public function registrarPrecio($granoId, $precio, $fecha, $usuarioId) {
        $sql = "INSERT INTO precio_grano (grano_id, precio, fecha, usuario_id) 
                VALUES (:grano_id, :precio, :fecha, :usuario_id)
                ON DUPLICATE KEY UPDATE 
                precio = :precio_update,
                usuario_id = :usuario_id_update";
        
        return $this->db->execute($sql, [
            'grano_id' => $granoId,
            'precio' => $precio,
            'fecha' => $fecha,
            'usuario_id' => $usuarioId,
            'precio_update' => $precio,
            'usuario_id_update' => $usuarioId
        ]);
    }
}