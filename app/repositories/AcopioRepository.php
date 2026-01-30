<?php
require_once __DIR__ . '/../models/Acopio.php';

class AcopioRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAllPaginated($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT 
                    a.*,
                    c.ci as cliente_ci,
                    c.nombres as cliente_nombres,
                    c.apellidos as cliente_apellidos,
                    u.nombre_usuario as usuario_nombre
                FROM acopios a
                INNER JOIN clientes c ON a.cliente_id = c.id_cliente
                LEFT JOIN usuarios u ON a.usuario_id = u.id_usuario
                WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (a.codigo LIKE :s1 OR c.nombres LIKE :s2 OR c.apellidos LIKE :s3 OR c.ci LIKE :s4)";
            $params['s1'] = '%' . $filters['search'] . '%';
            $params['s2'] = '%' . $filters['search'] . '%';
            $params['s3'] = '%' . $filters['search'] . '%';
            $params['s4'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND a.estado = :estado";
            $params['estado'] = $filters['estado'];
        }

        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND a.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND a.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }

        $sql .= " ORDER BY a.fecha DESC, a.id_acopio DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $results = $this->db->query($sql, $params);

        $acopios = [];
        foreach ($results as $row) {
            $acopios[] = new Acopio($row);
        }

        return $acopios;
    }

    public function findById($id) {
        $sql = "SELECT 
                    a.*,
                    c.ci as cliente_ci,
                    c.nombres as cliente_nombres,
                    c.apellidos as cliente_apellidos,
                    c.telefono as cliente_telefono,
                    c.comunidad as cliente_comunidad,
                    u.nombre_usuario as usuario_nombre
                FROM acopios a
                INNER JOIN clientes c ON a.cliente_id = c.id_cliente
                LEFT JOIN usuarios u ON a.usuario_id = u.id_usuario
                WHERE a.id_acopio = :id 
                LIMIT 1";
        
        $result = $this->db->queryOne($sql, ['id' => $id]);
        
        if ($result) {
            $acopio = new Acopio($result);
            $acopio->detalles = $this->getDetalles($id);
            return $acopio;
        }
        
        return null;
    }

    public function getDetalles($acopioId) {
        $sql = "SELECT 
                    ad.*,
                    g.nombre as grano_nombre,
                    u.codigo as unidad_codigo
                FROM acopios_detalle ad
                INNER JOIN granos g ON ad.grano_id = g.id_grano
                LEFT JOIN unidades_medida u ON g.unidad_id = u.id_unidad
                WHERE ad.acopio_id = :acopio_id
                ORDER BY ad.id_acopio_detalle ASC";
        
        return $this->db->query($sql, ['acopio_id' => $acopioId]);
    }

    public function existeCodigo($codigo) {
        $sql = "SELECT COUNT(*) as existe FROM acopios WHERE codigo = :codigo";
        $result = $this->db->queryOne($sql, ['codigo' => $codigo]);
        return $result['existe'] > 0;
    }

    public function create($data) {
        if (empty($data['codigo'])) {
            $data['codigo'] = $this->generarCodigo();
        }

        $sql = "INSERT INTO acopios (codigo, cliente_id, fecha, subtotal, total, observaciones, estado, usuario_id) 
                VALUES (:codigo, :cliente_id, :fecha, :subtotal, :total, :observaciones, :estado, :usuario_id)";

        return $this->db->insert($sql, $data);
    }

    public function createDetalle($data) {
        $sql = "INSERT INTO acopios_detalle (acopio_id, grano_id, cantidad, precio_unitario, subtotal) 
                VALUES (:acopio_id, :grano_id, :cantidad, :precio_unitario, :subtotal)";
        
        return $this->db->insert($sql, $data);
    }

    public function anular($id, $motivo) {
        $sql = "UPDATE acopios 
                SET estado = 'ANULADO',
                    motivo_anulacion = :motivo
                WHERE id_acopio = :id";
        
        return $this->db->execute($sql, ['id' => $id, 'motivo' => $motivo]);
    }

    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM acopios a INNER JOIN clientes c ON a.cliente_id = c.id_cliente WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (a.codigo LIKE :s1 OR c.nombres LIKE :s2 OR c.apellidos LIKE :s3 OR c.ci LIKE :s4)";
            $params['s1'] = '%' . $filters['search'] . '%';
            $params['s2'] = '%' . $filters['search'] . '%';
            $params['s3'] = '%' . $filters['search'] . '%';
            $params['s4'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND a.estado = :estado";
            $params['estado'] = $filters['estado'];
        }

        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND a.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND a.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }

        $result = $this->db->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function generarCodigo($numeroEspecifico = null) {
        if ($numeroEspecifico !== null) {
            $numero = intval($numeroEspecifico);
            if ($numero <= 0) {
                throw new Exception('El número de acopio debe ser mayor a 0');
            }
            return 'ACO' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        }

        $sql = "SELECT MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)) as ultimo FROM acopios WHERE codigo LIKE 'ACO%'";
        $result = $this->db->queryOne($sql);

        $ultimo = $result['ultimo'] ?? 0;
        $nuevo = $ultimo + 1;

        return 'ACO' . str_pad($nuevo, 6, '0', STR_PAD_LEFT);
    }

    public function obtenerProximoNumero() {
        $sql = "SELECT MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)) as ultimo FROM acopios WHERE codigo LIKE 'ACO%'";
        $result = $this->db->queryOne($sql);
        
        $ultimo = $result['ultimo'] ?? 0;
        return $ultimo + 1;
    }

    public function update($id, $data) {
        $sql = "UPDATE acopios 
                SET cliente_id = :cliente_id,
                    fecha = :fecha,
                    subtotal = :subtotal,
                    total = :total,
                    observaciones = :observaciones
                WHERE id_acopio = :id";
        
        $params = [
            'id' => $id,
            'cliente_id' => $data['cliente_id'],
            'fecha' => $data['fecha'],
            'subtotal' => $data['subtotal'],
            'total' => $data['total'],
            'observaciones' => $data['observaciones']
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    public function deleteDetalles($acopioId) {
        $sql = "DELETE FROM acopios_detalle WHERE acopio_id = :acopio_id";
        return $this->db->execute($sql, ['acopio_id' => $acopioId]);
    }
}