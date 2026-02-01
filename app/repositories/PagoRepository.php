<?php
require_once __DIR__ . '/../models/Pago.php';

class PagoRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById($id) {
        $sql = "SELECT 
                    p.*,
                    c.nombres as cliente_nombres,
                    c.apellidos as cliente_apellidos,
                    c.ci as cliente_ci,
                    c.comunidad as cliente_comunidad,
                    u.nombre_usuario as usuario_nombre
                FROM pagos p
                INNER JOIN clientes c ON p.cliente_id = c.id_cliente
                LEFT JOIN usuarios u ON p.usuario_id = u.id_usuario
                WHERE p.id_pago = :id";

        $row = $this->db->queryOne($sql, ['id' => $id]);
        return $row ? new Pago($row) : null;
    }

    public function findAllPaginated($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT 
                    p.*,
                    c.nombres as cliente_nombres,
                    c.apellidos as cliente_apellidos,
                    c.ci as cliente_ci,
                    c.comunidad as cliente_comunidad,
                    u.nombre_usuario as usuario_nombre
                FROM pagos p
                INNER JOIN clientes c ON p.cliente_id = c.id_cliente
                LEFT JOIN usuarios u ON p.usuario_id = u.id_usuario
                WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (c.nombres LIKE :s1 OR c.apellidos LIKE :s2 OR c.ci LIKE :s3 OR p.codigo LIKE :s4)";
            $params['s1'] = '%' . $filters['search'] . '%';
            $params['s2'] = '%' . $filters['search'] . '%';
            $params['s3'] = '%' . $filters['search'] . '%';
            $params['s4'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['cliente_id'])) {
            $sql .= " AND p.cliente_id = :cliente_id";
            $params['cliente_id'] = $filters['cliente_id'];
        }

        if (!empty($filters['tipo'])) {
            $sql .= " AND p.tipo = :tipo";
            $params['tipo'] = $filters['tipo'];
        }

        if (!empty($filters['metodo_pago'])) {
            $sql .= " AND p.metodo_pago = :metodo_pago";
            $params['metodo_pago'] = $filters['metodo_pago'];
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND p.estado = :estado";
            $params['estado'] = $filters['estado'];
        }

        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND p.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND p.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }

        $sql .= " ORDER BY p.fecha DESC, p.id_pago DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $results = $this->db->query($sql, $params);
        $pagos = [];
        foreach ($results as $row) {
            $pagos[] = new Pago($row);
        }
        return $pagos;
    }

    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total 
                FROM pagos p
                INNER JOIN clientes c ON p.cliente_id = c.id_cliente
                WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (c.nombres LIKE :s1 OR c.apellidos LIKE :s2 OR c.ci LIKE :s3 OR p.codigo LIKE :s4)";
            $params['s1'] = '%' . $filters['search'] . '%';
            $params['s2'] = '%' . $filters['search'] . '%';
            $params['s3'] = '%' . $filters['search'] . '%';
            $params['s4'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['cliente_id'])) {
            $sql .= " AND p.cliente_id = :cliente_id";
            $params['cliente_id'] = $filters['cliente_id'];
        }

        if (!empty($filters['tipo'])) {
            $sql .= " AND p.tipo = :tipo";
            $params['tipo'] = $filters['tipo'];
        }

        if (!empty($filters['metodo_pago'])) {
            $sql .= " AND p.metodo_pago = :metodo_pago";
            $params['metodo_pago'] = $filters['metodo_pago'];
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND p.estado = :estado";
            $params['estado'] = $filters['estado'];
        }

        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND p.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND p.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }

        $result = $this->db->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function create($data) {
        $sql = "INSERT INTO pagos 
                (codigo, cliente_id, fecha, tipo, metodo_pago, monto, referencia_operacion, concepto, estado, usuario_id)
                VALUES (:codigo, :cliente_id, :fecha, :tipo, :metodo_pago, :monto, :referencia_operacion, :concepto, :estado, :usuario_id)";

        return $this->db->insert($sql, $data);
    }

    public function anular($id) {
        $sql = "UPDATE pagos SET estado = 'ANULADO' WHERE id_pago = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    public function generarCodigo() {
        $sql = "SELECT codigo FROM pagos ORDER BY id_pago DESC LIMIT 1";
        $row = $this->db->queryOne($sql);

        if ($row && !empty($row['codigo'])) {
            $numero = (int)substr($row['codigo'], 3) + 1;
        } else {
            $numero = 1;
        }

        return 'PAG' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }

    public function getTotales($filters = []) {
        $sql = "SELECT 
                    COUNT(*) as total_pagos,
                    SUM(CASE WHEN p.estado = 'COMPLETADO' THEN monto ELSE 0 END) as total_monto,
                    SUM(CASE WHEN p.tipo = 'PAGO_CLIENTE' AND p.estado = 'COMPLETADO' THEN monto ELSE 0 END) as total_cobrados,
                    SUM(CASE WHEN p.tipo = 'PAGO_JB' AND p.estado = 'COMPLETADO' THEN monto ELSE 0 END) as total_pagados
                FROM pagos p
                INNER JOIN clientes c ON p.cliente_id = c.id_cliente
                WHERE 1=1";
        $params = [];

        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND p.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND p.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }

        return $this->db->queryOne($sql, $params);
    }
}