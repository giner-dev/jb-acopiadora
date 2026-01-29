<?php
require_once __DIR__ . '/../models/Factura.php';

class FacturaRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAllPaginated($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT 
                    f.*,
                    c.ci as cliente_ci,
                    c.nombres as cliente_nombres,
                    c.apellidos as cliente_apellidos,
                    u.nombre_usuario as usuario_nombre
                FROM facturas f
                INNER JOIN clientes c ON f.cliente_id = c.id_cliente
                LEFT JOIN usuarios u ON f.usuario_id = u.id_usuario
                WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            // Marcadores únicos para evitar el error SQLSTATE[HY093]
            $sql .= " AND (f.codigo LIKE :s1 OR c.nombres LIKE :s2 OR c.apellidos LIKE :s3 OR c.ci LIKE :s4)";
            $params['s1'] = '%' . $filters['search'] . '%';
            $params['s2'] = '%' . $filters['search'] . '%';
            $params['s3'] = '%' . $filters['search'] . '%';
            $params['s4'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND f.estado = :estado";
            $params['estado'] = $filters['estado'];
        }

        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND f.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND f.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }

        $sql .= " ORDER BY f.fecha DESC, f.id_factura DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $results = $this->db->query($sql, $params);

        $facturas = [];
        foreach ($results as $row) {
            $facturas[] = new Factura($row);
        }

        return $facturas;
    }

    public function findById($id) {
        $sql = "SELECT 
                    f.*,
                    c.ci as cliente_ci,
                    c.nombres as cliente_nombres,
                    c.apellidos as cliente_apellidos,
                    c.telefono as cliente_telefono,
                    c.comunidad as cliente_comunidad,
                    u.nombre_usuario as usuario_nombre
                FROM facturas f
                INNER JOIN clientes c ON f.cliente_id = c.id_cliente
                LEFT JOIN usuarios u ON f.usuario_id = u.id_usuario
                WHERE f.id_factura = :id 
                LIMIT 1";
        
        $result = $this->db->queryOne($sql, ['id' => $id]);
        
        if ($result) {
            $factura = new Factura($result);
            $factura->detalles = $this->getDetalles($id);
            return $factura;
        }
        
        return null;
    }

    public function getDetalles($facturaId) {
        $sql = "SELECT 
                    fd.*,
                    p.codigo as producto_codigo,
                    p.nombre as producto_nombre,
                    u.codigo as unidad_codigo
                FROM facturas_detalle fd
                INNER JOIN productos p ON fd.producto_id = p.id_producto
                LEFT JOIN unidades_medida u ON p.unidad_id = u.id_unidad
                WHERE fd.factura_id = :factura_id
                ORDER BY fd.id_factura_detalle ASC";
        
        return $this->db->query($sql, ['factura_id' => $facturaId]);
    }

    public function existeCodigo($codigo) {
        $sql = "SELECT COUNT(*) as existe FROM facturas WHERE codigo = :codigo";
        $result = $this->db->queryOne($sql, ['codigo' => $codigo]);
        return $result['existe'] > 0;
    }

    public function create($data) {
        // Si no viene código, generarlo
        if (empty($data['codigo'])) {
            $data['codigo'] = $this->generarCodigo();
        }

        $sql = "INSERT INTO facturas (codigo, cliente_id, fecha, subtotal, total, adelanto, saldo, estado, usuario_id) 
                VALUES (:codigo, :cliente_id, :fecha, :subtotal, :total, :adelanto, :saldo, :estado, :usuario_id)";

        return $this->db->insert($sql, $data);
    }

    public function createDetalle($data) {
        $sql = "INSERT INTO facturas_detalle (factura_id, producto_id, cantidad, precio_unitario, subtotal) 
                VALUES (:factura_id, :producto_id, :cantidad, :precio_unitario, :subtotal)";
        
        return $this->db->insert($sql, $data);
    }

    public function anular($id, $motivo) {
        $sql = "UPDATE facturas 
                SET estado = 'ANULADA',
                    motivo_anulacion = :motivo
                WHERE id_factura = :id";
        
        return $this->db->execute($sql, ['id' => $id, 'motivo' => $motivo]);
    }

    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM facturas f INNER JOIN clientes c ON f.cliente_id = c.id_cliente WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (f.codigo LIKE :s1 OR c.nombres LIKE :s2 OR c.apellidos LIKE :s3 OR c.ci LIKE :s4)";
            $params['s1'] = '%' . $filters['search'] . '%';
            $params['s2'] = '%' . $filters['search'] . '%';
            $params['s3'] = '%' . $filters['search'] . '%';
            $params['s4'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND f.estado = :estado";
            $params['estado'] = $filters['estado'];
        }

        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND f.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND f.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }

        $result = $this->db->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function generarCodigo($numeroEspecifico = null) {
        if ($numeroEspecifico !== null) {
            // Validar que sea numérico y positivo
            $numero = intval($numeroEspecifico);
            if ($numero <= 0) {
                throw new Exception('El número de factura debe ser mayor a 0');
            }
            return 'FAC' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        }

        // Generar automáticamente
        $sql = "SELECT MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)) as ultimo FROM facturas WHERE codigo LIKE 'FAC%'";
        $result = $this->db->queryOne($sql);

        $ultimo = $result['ultimo'] ?? 0;
        $nuevo = $ultimo + 1;

        return 'FAC' . str_pad($nuevo, 6, '0', STR_PAD_LEFT);
    }

    public function obtenerProximoNumero() {
        $sql = "SELECT MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)) as ultimo FROM facturas WHERE codigo LIKE 'FAC%'";
        $result = $this->db->queryOne($sql);
        
        $ultimo = $result['ultimo'] ?? 0;
        return $ultimo + 1;
    }

    public function update($id, $data) {
        $sql = "UPDATE facturas 
                SET cliente_id = :cliente_id,
                    fecha = :fecha,
                    subtotal = :subtotal,
                    total = :total,
                    adelanto = :adelanto,
                    saldo = :saldo,
                    estado = :estado
                WHERE id_factura = :id";

        $params = [
            'id' => $id,
            'cliente_id' => $data['cliente_id'],
            'fecha' => $data['fecha'],
            'subtotal' => $data['subtotal'],
            'total' => $data['total'],
            'adelanto' => $data['adelanto'],
            'saldo' => $data['saldo'],
            'estado' => $data['estado']
        ];

        return $this->db->execute($sql, $params);
    }

    public function deleteDetalles($facturaId) {
        $sql = "DELETE FROM facturas_detalle WHERE factura_id = :factura_id";
        return $this->db->execute($sql, ['factura_id' => $facturaId]);
    }
}