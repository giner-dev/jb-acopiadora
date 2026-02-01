<?php
require_once __DIR__ . '/../models/CuentaCorriente.php';

class CuentaCorrienteRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAllPaginated($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT 
                    cc.*,
                    c.ci as cliente_ci,
                    c.nombres as cliente_nombres,
                    c.apellidos as cliente_apellidos,
                    c.comunidad as cliente_comunidad,
                    u.nombre_usuario as usuario_nombre
                FROM cuenta_corriente cc
                INNER JOIN clientes c ON cc.cliente_id = c.id_cliente
                LEFT JOIN usuarios u ON cc.usuario_id = u.id_usuario
                WHERE 1=1";
        $params = [];

        if (!empty($filters['cliente_id'])) {
            $sql .= " AND cc.cliente_id = :cliente_id";
            $params['cliente_id'] = $filters['cliente_id'];
        }

        if (!empty($filters['tipo_movimiento'])) {
            $sql .= " AND cc.tipo_movimiento = :tipo_movimiento";
            $params['tipo_movimiento'] = $filters['tipo_movimiento'];
        }

        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND cc.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND cc.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (c.nombres LIKE :s1 OR c.apellidos LIKE :s2 OR c.ci LIKE :s3)";
            $params['s1'] = '%' . $filters['search'] . '%';
            $params['s2'] = '%' . $filters['search'] . '%';
            $params['s3'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY cc.fecha DESC, cc.id_cuenta_corriente DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $results = $this->db->query($sql, $params);

        $movimientos = [];
        foreach ($results as $row) {
            $movimientos[] = new CuentaCorriente($row);
        }

        return $movimientos;
    }

    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM cuenta_corriente cc 
                INNER JOIN clientes c ON cc.cliente_id = c.id_cliente 
                WHERE 1=1";
        $params = [];

        if (!empty($filters['cliente_id'])) {
            $sql .= " AND cc.cliente_id = :cliente_id";
            $params['cliente_id'] = $filters['cliente_id'];
        }

        if (!empty($filters['tipo_movimiento'])) {
            $sql .= " AND cc.tipo_movimiento = :tipo_movimiento";
            $params['tipo_movimiento'] = $filters['tipo_movimiento'];
        }

        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND cc.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND cc.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (c.nombres LIKE :s1 OR c.apellidos LIKE :s2 OR c.ci LIKE :s3)";
            $params['s1'] = '%' . $filters['search'] . '%';
            $params['s2'] = '%' . $filters['search'] . '%';
            $params['s3'] = '%' . $filters['search'] . '%';
        }

        $result = $this->db->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function getSaldoPorCliente($clienteId) {
        $sql = "SELECT 
                    c.id_cliente,
                    c.ci,
                    c.nombres,
                    c.apellidos,
                    c.comunidad,
                    COALESCE((
                        SELECT SUM(debe) - SUM(haber)
                        FROM cuenta_corriente
                        WHERE cliente_id = c.id_cliente
                    ), 0) as saldo
                FROM clientes c
                WHERE c.id_cliente = :cliente_id
                LIMIT 1";
        
        return $this->db->queryOne($sql, ['cliente_id' => $clienteId]);
    }

    public function getClientesConSaldo() {
        $sql = "SELECT 
                    c.id_cliente,
                    c.ci,
                    c.nombres,
                    c.apellidos,
                    c.comunidad,
                    COALESCE((
                        SELECT SUM(debe) - SUM(haber)
                        FROM cuenta_corriente
                        WHERE cliente_id = c.id_cliente
                    ), 0) as saldo
                FROM clientes c
                WHERE c.estado = 'activo'
                HAVING saldo != 0
                ORDER BY saldo DESC";
        
        return $this->db->query($sql);
    }

    public function getClientesDeudores() {
        $sql = "SELECT 
                    c.id_cliente,
                    c.ci,
                    c.nombres,
                    c.apellidos,
                    c.comunidad,
                    COALESCE((
                        SELECT SUM(debe) - SUM(haber)
                        FROM cuenta_corriente
                        WHERE cliente_id = c.id_cliente
                    ), 0) as saldo
                FROM clientes c
                WHERE c.estado = 'activo'
                HAVING saldo > 0
                ORDER BY saldo DESC";
        
        return $this->db->query($sql);
    }

    public function getClientesAcreedores() {
        $sql = "SELECT 
                    c.id_cliente,
                    c.ci,
                    c.nombres,
                    c.apellidos,
                    c.comunidad,
                    COALESCE((
                        SELECT SUM(debe) - SUM(haber)
                        FROM cuenta_corriente
                        WHERE cliente_id = c.id_cliente
                    ), 0) as saldo
                FROM clientes c
                WHERE c.estado = 'activo'
                HAVING saldo < 0
                ORDER BY saldo ASC";
        
        return $this->db->query($sql);
    }

    public function getMovimientosPorCliente($clienteId) {
        $sql = "SELECT 
                    cc.*,
                    u.nombre_usuario as usuario_nombre
                FROM cuenta_corriente cc
                LEFT JOIN usuarios u ON cc.usuario_id = u.id_usuario
                WHERE cc.cliente_id = :cliente_id
                ORDER BY cc.fecha DESC, cc.id_cuenta_corriente DESC";
        
        $results = $this->db->query($sql, ['cliente_id' => $clienteId]);

        $movimientos = [];
        foreach ($results as $row) {
            $movimientos[] = new CuentaCorriente($row);
        }

        return $movimientos;
    }

    public function create($data) {
        $sql = "INSERT INTO cuenta_corriente 
                (cliente_id, fecha, tipo_movimiento, referencia_tipo, referencia_id, descripcion, debe, haber, usuario_id) 
                VALUES (:cliente_id, :fecha, :tipo_movimiento, :referencia_tipo, :referencia_id, :descripcion, :debe, :haber, :usuario_id)";
        
        return $this->db->insert($sql, $data);
    }

    public function getTotales($filters = []) {
        $sql = "SELECT 
                    SUM(debe) as total_debe,
                    SUM(haber) as total_haber,
                    (SUM(debe) - SUM(haber)) as saldo_total
                FROM cuenta_corriente cc
                INNER JOIN clientes c ON cc.cliente_id = c.id_cliente
                WHERE 1=1";
        $params = [];

        if (!empty($filters['cliente_id'])) {
            $sql .= " AND cc.cliente_id = :cliente_id";
            $params['cliente_id'] = $filters['cliente_id'];
        }

        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND cc.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND cc.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }

        return $this->db->queryOne($sql, $params);
    }
}