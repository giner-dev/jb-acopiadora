<?php

class DashboardRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getTotalClientes() {
        $sql = "SELECT COUNT(*) as total FROM clientes WHERE estado = 'activo'";
        $result = $this->db->queryOne($sql);
        return $result['total'] ?? 0;
    }

    public function getTotalProductos() {
        $sql = "SELECT COUNT(*) as total FROM productos WHERE estado = 'activo'";
        $result = $this->db->queryOne($sql);
        return $result['total'] ?? 0;
    }

    public function getTotalFacturasPendientes() {
        $sql = "SELECT COUNT(*) as total 
                FROM facturas 
                WHERE estado IN ('PENDIENTE', 'PAGO_PARCIAL')";
        $result = $this->db->queryOne($sql);
        return $result['total'] ?? 0;
    }

    public function getMontoFacturasPendientes() {
        $sql = "SELECT COALESCE(SUM(saldo), 0) as total 
                FROM facturas 
                WHERE estado IN ('PENDIENTE', 'PAGO_PARCIAL')";
        $result = $this->db->queryOne($sql);
        return $result['total'] ?? 0;
    }

    public function getSaldoTotalCuentaCorriente() {
        $sql = "SELECT 
                    COALESCE(SUM(CASE WHEN tipo_movimiento IN ('FACTURA', 'NOTA_DEBITO', 'AJUSTE') THEN debe ELSE 0 END), 0) as total_debe,
                    COALESCE(SUM(CASE WHEN tipo_movimiento IN ('ACOPIO', 'PAGO_CLIENTE', 'NOTA_CREDITO', 'AJUSTE') THEN haber ELSE 0 END), 0) as total_haber
                FROM cuenta_corriente";
        
        $result = $this->db->queryOne($sql);
        
        if ($result) {
            $debe = (float)$result['total_debe'];
            $haber = (float)$result['total_haber'];
            return $haber - $debe;
        }
        
        return 0;
    }

    public function getProductosBajoStock() {
        $sql = "SELECT 
                    p.id_producto,
                    p.nombre,
                    p.stock_actual,
                    p.stock_minimo,
                    u.nombre as unidad
                FROM productos p
                LEFT JOIN unidades_medida u ON p.unidad_id = u.id_unidad
                WHERE p.estado = 'activo' 
                AND p.stock_actual <= p.stock_minimo
                ORDER BY (p.stock_minimo - p.stock_actual) DESC
                LIMIT 5";
        
        return $this->db->query($sql);
    }

    public function getUltimasFacturas($limit = 5) {
        $sql = "SELECT 
                    f.id_factura,
                    f.codigo,
                    f.fecha,
                    f.total,
                    f.saldo,
                    f.estado,
                    c.nombres,
                    c.apellidos
                FROM facturas f
                INNER JOIN clientes c ON f.cliente_id = c.id_cliente
                WHERE f.estado IN ('PENDIENTE', 'PAGO_PARCIAL')
                ORDER BY f.fecha DESC
                LIMIT :limit";
        
        return $this->db->query($sql, ['limit' => $limit]);
    }

    public function getUltimosAcopios($limit = 5) {
        $sql = "SELECT 
                    a.id_acopio,
                    a.codigo,
                    a.fecha,
                    a.total,
                    c.nombres,
                    c.apellidos
                FROM acopios a
                INNER JOIN clientes c ON a.cliente_id = c.id_cliente
                ORDER BY a.fecha DESC
                LIMIT :limit";
        
        return $this->db->query($sql, ['limit' => $limit]);
    }

    public function getClientesConDeuda() {
        $sql = "SELECT 
                    c.id_cliente,
                    c.nombres,
                    c.apellidos,
                    c.ci,
                    COALESCE(SUM(CASE WHEN cc.tipo_movimiento IN ('FACTURA', 'NOTA_DEBITO', 'AJUSTE') THEN cc.debe ELSE 0 END), 0) as total_debe,
                    COALESCE(SUM(CASE WHEN cc.tipo_movimiento IN ('ACOPIO', 'PAGO_CLIENTE', 'NOTA_CREDITO', 'AJUSTE') THEN cc.haber ELSE 0 END), 0) as total_haber
                FROM clientes c
                LEFT JOIN cuenta_corriente cc ON c.id_cliente = cc.cliente_id
                WHERE c.estado = 'activo'
                GROUP BY c.id_cliente, c.nombres, c.apellidos, c.ci
                HAVING (SUM(CASE WHEN cc.tipo_movimiento IN ('ACOPIO', 'PAGO_CLIENTE', 'NOTA_CREDITO', 'AJUSTE') THEN cc.haber ELSE 0 END) - 
                        SUM(CASE WHEN cc.tipo_movimiento IN ('FACTURA', 'NOTA_DEBITO', 'AJUSTE') THEN cc.debe ELSE 0 END)) < 0
                ORDER BY (SUM(CASE WHEN cc.tipo_movimiento IN ('ACOPIO', 'PAGO_CLIENTE', 'NOTA_CREDITO', 'AJUSTE') THEN cc.haber ELSE 0 END) - 
                          SUM(CASE WHEN cc.tipo_movimiento IN ('FACTURA', 'NOTA_DEBITO', 'AJUSTE') THEN cc.debe ELSE 0 END)) ASC
                LIMIT 5";
        
        return $this->db->query($sql);
    }

    public function getMovimientosRecientes($limit = 10) {
        $sql = "SELECT 
                    cc.fecha,
                    cc.tipo_movimiento,
                    cc.descripcion,
                    cc.debe,
                    cc.haber,
                    c.nombres,
                    c.apellidos
                FROM cuenta_corriente cc
                INNER JOIN clientes c ON cc.cliente_id = c.id_cliente
                ORDER BY cc.fecha DESC
                LIMIT :limit";
        
        return $this->db->query($sql, ['limit' => $limit]);
    }
}