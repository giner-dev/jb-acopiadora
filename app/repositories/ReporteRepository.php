<?php

class ReporteRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // ============================================
    // RF-08.1: Clientes con saldo deudor
    // Saldo positivo significa que el cliente debe a JB
    // ============================================
    public function getClientesDeudores($page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT 
                    c.id_cliente,
                    c.ci,
                    c.nombres,
                    c.apellidos,
                    c.comunidad,
                    (SUM(cc.debe) - SUM(cc.haber)) as saldo,
                    COUNT(DISTINCT CASE WHEN cc.tipo_movimiento = 'FACTURA' THEN cc.referencia_id END) as total_facturas,
                    COUNT(DISTINCT CASE WHEN cc.tipo_movimiento = 'ACOPIO' THEN cc.referencia_id END) as total_acopios,
                    MAX(cc.fecha) as ultimo_movimiento
                FROM clientes c
                INNER JOIN cuenta_corriente cc ON c.id_cliente = cc.cliente_id
                WHERE c.estado = 'activo'
                GROUP BY c.id_cliente, c.ci, c.nombres, c.apellidos, c.comunidad
                HAVING (SUM(cc.debe) - SUM(cc.haber)) > 0
                ORDER BY saldo DESC
                LIMIT :perPage OFFSET :offset";

        return $this->db->query($sql, ['perPage' => $perPage, 'offset' => $offset]);
    }

    public function countClientesDeudores() {
        $sql = "SELECT COUNT(*) as total FROM (
                    SELECT c.id_cliente
                    FROM clientes c
                    INNER JOIN cuenta_corriente cc ON c.id_cliente = cc.cliente_id
                    WHERE c.estado = 'activo'
                    GROUP BY c.id_cliente
                    HAVING (SUM(cc.debe) - SUM(cc.haber)) > 0
                ) sub";

        $result = $this->db->queryOne($sql);
        return $result['total'] ?? 0;
    }

    public function getTotalesDeudores() {
        $sql = "SELECT 
                    COUNT(*) as cantidad_clientes,
                    SUM(saldo) as monto_total
                FROM (
                    SELECT c.id_cliente, (SUM(cc.debe) - SUM(cc.haber)) as saldo
                    FROM clientes c
                    INNER JOIN cuenta_corriente cc ON c.id_cliente = cc.cliente_id
                    WHERE c.estado = 'activo'
                    GROUP BY c.id_cliente
                    HAVING (SUM(cc.debe) - SUM(cc.haber)) > 0
                ) sub";

        return $this->db->queryOne($sql);
    }

    // ============================================
    // RF-08.2: Clientes con saldo acreedor
    // Saldo negativo significa que JB debe al cliente
    // ============================================
    public function getClientesAcreedores($page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT 
                    c.id_cliente,
                    c.ci,
                    c.nombres,
                    c.apellidos,
                    c.comunidad,
                    ABS(SUM(cc.debe) - SUM(cc.haber)) as saldo,
                    COUNT(DISTINCT CASE WHEN cc.tipo_movimiento = 'FACTURA' THEN cc.referencia_id END) as total_facturas,
                    COUNT(DISTINCT CASE WHEN cc.tipo_movimiento = 'ACOPIO' THEN cc.referencia_id END) as total_acopios,
                    MAX(cc.fecha) as ultimo_movimiento
                FROM clientes c
                INNER JOIN cuenta_corriente cc ON c.id_cliente = cc.cliente_id
                WHERE c.estado = 'activo'
                GROUP BY c.id_cliente, c.ci, c.nombres, c.apellidos, c.comunidad
                HAVING (SUM(cc.debe) - SUM(cc.haber)) < 0
                ORDER BY saldo DESC
                LIMIT :perPage OFFSET :offset";

        return $this->db->query($sql, ['perPage' => $perPage, 'offset' => $offset]);
    }

    public function countClientesAcreedores() {
        $sql = "SELECT COUNT(*) as total FROM (
                    SELECT c.id_cliente
                    FROM clientes c
                    INNER JOIN cuenta_corriente cc ON c.id_cliente = cc.cliente_id
                    WHERE c.estado = 'activo'
                    GROUP BY c.id_cliente
                    HAVING (SUM(cc.debe) - SUM(cc.haber)) < 0
                ) sub";

        $result = $this->db->queryOne($sql);
        return $result['total'] ?? 0;
    }

    public function getTotalesAcreedores() {
        $sql = "SELECT 
                    COUNT(*) as cantidad_clientes,
                    SUM(saldo) as monto_total
                FROM (
                    SELECT c.id_cliente, ABS(SUM(cc.debe) - SUM(cc.haber)) as saldo
                    FROM clientes c
                    INNER JOIN cuenta_corriente cc ON c.id_cliente = cc.cliente_id
                    WHERE c.estado = 'activo'
                    GROUP BY c.id_cliente
                    HAVING (SUM(cc.debe) - SUM(cc.haber)) < 0
                ) sub";

        return $this->db->queryOne($sql);
    }

    // ============================================
    // RF-08.3: Ventas por período
    // ============================================
    public function getVentasPorPeriodo($fechaDesde, $fechaHasta, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT 
                    f.id_factura,
                    f.codigo,
                    f.fecha,
                    c.ci as cliente_ci,
                    c.nombres as cliente_nombres,
                    c.apellidos as cliente_apellidos,
                    f.subtotal,
                    f.total,
                    f.estado,
                    COUNT(fd.id_factura_detalle) as cantidad_productos
                FROM facturas f
                INNER JOIN clientes c ON f.cliente_id = c.id_cliente
                INNER JOIN facturas_detalle fd ON f.id_factura = fd.factura_id
                WHERE f.fecha BETWEEN :fecha_desde AND :fecha_hasta
                  AND f.estado != 'ANULADA'
                GROUP BY f.id_factura, f.codigo, f.fecha, c.ci, c.nombres, c.apellidos, f.subtotal, f.total, f.estado
                ORDER BY f.fecha DESC
                LIMIT :perPage OFFSET :offset";

        return $this->db->query($sql, [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'perPage'     => $perPage,
            'offset'      => $offset
        ]);
    }

    public function countVentasPorPeriodo($fechaDesde, $fechaHasta) {
        $sql = "SELECT COUNT(DISTINCT f.id_factura) as total
                FROM facturas f
                WHERE f.fecha BETWEEN :fecha_desde AND :fecha_hasta
                  AND f.estado != 'ANULADA'";

        $result = $this->db->queryOne($sql, [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta
        ]);
        return $result['total'] ?? 0;
    }

    public function getTotalesVentas($fechaDesde, $fechaHasta) {
        $sql = "SELECT 
                    COUNT(*) as total_facturas,
                    SUM(total) as monto_total,
                    AVG(total) as promedio_factura,
                    MAX(total) as factura_maxima,
                    MIN(total) as factura_minima
                FROM facturas
                WHERE fecha BETWEEN :fecha_desde AND :fecha_hasta
                  AND estado != 'ANULADA'";

        return $this->db->queryOne($sql, [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta
        ]);
    }

    public function getVentasPorMes($fechaDesde, $fechaHasta) {
        $sql = "SELECT 
                    DATE_FORMAT(fecha, '%Y-%m') as mes,
                    COUNT(*) as cantidad_facturas,
                    SUM(total) as monto_total
                FROM facturas
                WHERE fecha BETWEEN :fecha_desde AND :fecha_hasta
                  AND estado != 'ANULADA'
                GROUP BY DATE_FORMAT(fecha, '%Y-%m')
                ORDER BY mes ASC";

        return $this->db->query($sql, [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta
        ]);
    }

    // ============================================
    // RF-08.4: Acopios por período
    // ============================================
    public function getAcopiosPorPeriodo($fechaDesde, $fechaHasta, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT 
                    a.id_acopio,
                    a.codigo,
                    a.fecha,
                    c.ci as cliente_ci,
                    c.nombres as cliente_nombres,
                    c.apellidos as cliente_apellidos,
                    a.subtotal,
                    a.total,
                    a.estado,
                    COUNT(ad.id_acopio_detalle) as cantidad_granos
                FROM acopios a
                INNER JOIN clientes c ON a.cliente_id = c.id_cliente
                INNER JOIN acopios_detalle ad ON a.id_acopio = ad.acopio_id
                WHERE a.fecha BETWEEN :fecha_desde AND :fecha_hasta
                  AND a.estado != 'anulado'
                GROUP BY a.id_acopio, a.codigo, a.fecha, c.ci, c.nombres, c.apellidos, a.subtotal, a.total, a.estado
                ORDER BY a.fecha DESC
                LIMIT :perPage OFFSET :offset";

        return $this->db->query($sql, [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'perPage'     => $perPage,
            'offset'      => $offset
        ]);
    }

    public function countAcopiosPorPeriodo($fechaDesde, $fechaHasta) {
        $sql = "SELECT COUNT(DISTINCT a.id_acopio) as total
                FROM acopios a
                WHERE a.fecha BETWEEN :fecha_desde AND :fecha_hasta
                  AND a.estado != 'anulado'";

        $result = $this->db->queryOne($sql, [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta
        ]);
        return $result['total'] ?? 0;
    }

    public function getTotalesAcopios($fechaDesde, $fechaHasta) {
        $sql = "SELECT 
                    COUNT(*) as total_acopios,
                    SUM(total) as monto_total,
                    AVG(total) as promedio_acopio,
                    MAX(total) as acopio_maximo,
                    MIN(total) as acopio_minimo
                FROM acopios
                WHERE fecha BETWEEN :fecha_desde AND :fecha_hasta
                  AND estado != 'anulado'";

        return $this->db->queryOne($sql, [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta
        ]);
    }

    public function getAcopiosPorMes($fechaDesde, $fechaHasta) {
        $sql = "SELECT 
                    DATE_FORMAT(fecha, '%Y-%m') as mes,
                    COUNT(*) as cantidad_acopios,
                    SUM(total) as monto_total
                FROM acopios
                WHERE fecha BETWEEN :fecha_desde AND :fecha_hasta
                  AND estado != 'anulado'
                GROUP BY DATE_FORMAT(fecha, '%Y-%m')
                ORDER BY mes ASC";

        return $this->db->query($sql, [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta
        ]);
    }

    // ============================================
    // RF-08.5: Productos más vendidos
    // ============================================
    public function getProductosMasVendidos($fechaDesde, $fechaHasta, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT 
                    p.id_producto,
                    p.nombre as producto_nombre,
                    p.codigo as producto_codigo,
                    um.nombre as unidad,
                    SUM(fd.cantidad) as total_cantidad,
                    SUM(fd.subtotal) as total_monto,
                    COUNT(DISTINCT fd.factura_id) as cantidad_facturas,
                    (SUM(fd.subtotal) / NULLIF(SUM(fd.cantidad), 0)) as precio_promedio
                FROM facturas_detalle fd
                INNER JOIN facturas f ON fd.factura_id = f.id_factura
                INNER JOIN productos p ON fd.producto_id = p.id_producto
                LEFT JOIN unidades_medida um ON p.unidad_id = um.id_unidad
                WHERE f.fecha BETWEEN :fecha_desde AND :fecha_hasta
                  AND f.estado != 'ANULADA'
                GROUP BY p.id_producto, p.nombre, p.codigo, um.nombre
                ORDER BY total_cantidad DESC
                LIMIT :perPage OFFSET :offset";

        return $this->db->query($sql, [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'perPage'     => $perPage,
            'offset'      => $offset
        ]);
    }

    public function countProductosMasVendidos($fechaDesde, $fechaHasta) {
        $sql = "SELECT COUNT(DISTINCT fd.producto_id) as total
                FROM facturas_detalle fd
                INNER JOIN facturas f ON fd.factura_id = f.id_factura
                WHERE f.fecha BETWEEN :fecha_desde AND :fecha_hasta
                  AND f.estado != 'ANULADA'";

        $result = $this->db->queryOne($sql, [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta
        ]);
        return $result['total'] ?? 0;
    }

    // ============================================
    // RF-08.6: Granos más acopiados
    // ============================================
    public function getGranosMasAcopiados($fechaDesde, $fechaHasta, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT 
                    g.id_grano,
                    g.nombre as grano_nombre,
                    um.nombre as unidad,
                    SUM(ad.cantidad) as total_cantidad,
                    SUM(ad.subtotal) as total_monto,
                    COUNT(DISTINCT ad.acopio_id) as cantidad_acopios,
                    (SUM(ad.subtotal) / NULLIF(SUM(ad.cantidad), 0)) as precio_promedio
                FROM acopios_detalle ad
                INNER JOIN acopios a ON ad.acopio_id = a.id_acopio
                INNER JOIN granos g ON ad.grano_id = g.id_grano
                LEFT JOIN unidades_medida um ON g.unidad_id = um.id_unidad
                WHERE a.fecha BETWEEN :fecha_desde AND :fecha_hasta
                  AND a.estado != 'anulado'
                GROUP BY g.id_grano, g.nombre, um.nombre
                ORDER BY total_cantidad DESC
                LIMIT :perPage OFFSET :offset";

        return $this->db->query($sql, [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'perPage'     => $perPage,
            'offset'      => $offset
        ]);
    }

    public function countGranosMasAcopiados($fechaDesde, $fechaHasta) {
        $sql = "SELECT COUNT(DISTINCT ad.grano_id) as total
                FROM acopios_detalle ad
                INNER JOIN acopios a ON ad.acopio_id = a.id_acopio
                WHERE a.fecha BETWEEN :fecha_desde AND :fecha_hasta
                  AND a.estado != 'anulado'";

        $result = $this->db->queryOne($sql, [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta
        ]);
        return $result['total'] ?? 0;
    }

    // ============================================
    // RF-08.7: Rentabilidad por cliente
    // Rentabilidad = lo que el cliente compró (facturas) - lo que entregó (acopios)
    // Positivo = ganancia para JB
    // Negativo = JB pagó más por cosechas de lo que cobró en ventas
    // ============================================
    public function getRentabilidadPorCliente($fechaDesde, $fechaHasta, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT 
                    c.id_cliente,
                    c.ci,
                    c.nombres,
                    c.apellidos,
                    c.comunidad,
                    COALESCE(ventas.total_ventas, 0) as total_ventas,
                    COALESCE(acopios.total_acopios, 0) as total_acopios,
                    (COALESCE(ventas.total_ventas, 0) - COALESCE(acopios.total_acopios, 0)) as rentabilidad
                FROM clientes c
                LEFT JOIN (
                    SELECT cliente_id, SUM(total) as total_ventas
                    FROM facturas
                    WHERE fecha BETWEEN :fecha_desde1 AND :fecha_hasta1
                      AND estado != 'ANULADA'
                    GROUP BY cliente_id
                ) ventas ON c.id_cliente = ventas.cliente_id
                LEFT JOIN (
                    SELECT cliente_id, SUM(total) as total_acopios
                    FROM acopios
                    WHERE fecha BETWEEN :fecha_desde2 AND :fecha_hasta2
                      AND estado != 'anulado'
                    GROUP BY cliente_id
                ) acopios ON c.id_cliente = acopios.cliente_id
                WHERE c.estado = 'activo'
                  AND (ventas.total_ventas IS NOT NULL OR acopios.total_acopios IS NOT NULL)
                ORDER BY rentabilidad DESC
                LIMIT :perPage OFFSET :offset";

        return $this->db->query($sql, [
            'fecha_desde1' => $fechaDesde,
            'fecha_hasta1' => $fechaHasta,
            'fecha_desde2' => $fechaDesde,
            'fecha_hasta2' => $fechaHasta,
            'perPage'      => $perPage,
            'offset'       => $offset
        ]);
    }

    public function countRentabilidadPorCliente($fechaDesde, $fechaHasta) {
        $sql = "SELECT COUNT(*) as total FROM (
                    SELECT c.id_cliente
                    FROM clientes c
                    LEFT JOIN (
                        SELECT cliente_id FROM facturas
                        WHERE fecha BETWEEN :fecha_desde1 AND :fecha_hasta1 AND estado != 'ANULADA'
                        GROUP BY cliente_id
                    ) ventas ON c.id_cliente = ventas.cliente_id
                    LEFT JOIN (
                        SELECT cliente_id FROM acopios
                        WHERE fecha BETWEEN :fecha_desde2 AND :fecha_hasta2 AND estado != 'anulado'
                        GROUP BY cliente_id
                    ) acopios ON c.id_cliente = acopios.cliente_id
                    WHERE c.estado = 'activo'
                      AND (ventas.cliente_id IS NOT NULL OR acopios.cliente_id IS NOT NULL)
                ) sub";

        return $this->db->queryOne($sql, [
            'fecha_desde1' => $fechaDesde,
            'fecha_hasta1' => $fechaHasta,
            'fecha_desde2' => $fechaDesde,
            'fecha_hasta2' => $fechaHasta
        ])['total'] ?? 0;
    }

    public function getTotalesRentabilidad($fechaDesde, $fechaHasta) {
        $sql = "SELECT 
                    SUM(COALESCE(ventas.total_ventas, 0)) as total_ventas,
                    SUM(COALESCE(acopios.total_acopios, 0)) as total_acopios,
                    SUM(COALESCE(ventas.total_ventas, 0) - COALESCE(acopios.total_acopios, 0)) as rentabilidad_total
                FROM clientes c
                LEFT JOIN (
                    SELECT cliente_id, SUM(total) as total_ventas
                    FROM facturas
                    WHERE fecha BETWEEN :fecha_desde1 AND :fecha_hasta1 AND estado != 'ANULADA'
                    GROUP BY cliente_id
                ) ventas ON c.id_cliente = ventas.cliente_id
                LEFT JOIN (
                    SELECT cliente_id, SUM(total) as total_acopios
                    FROM acopios
                    WHERE fecha BETWEEN :fecha_desde2 AND :fecha_hasta2 AND estado != 'anulado'
                    GROUP BY cliente_id
                ) acopios ON c.id_cliente = acopios.cliente_id
                WHERE c.estado = 'activo'
                  AND (ventas.total_ventas IS NOT NULL OR acopios.total_acopios IS NOT NULL)";

        return $this->db->queryOne($sql, [
            'fecha_desde1' => $fechaDesde,
            'fecha_hasta1' => $fechaHasta,
            'fecha_desde2' => $fechaDesde,
            'fecha_hasta2' => $fechaHasta
        ]);
    }

    // ============================================
    // RF-08.8: Estado de inventario actual
    // ============================================
    public function getEstadoInventario($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT 
                    p.id_producto,
                    p.codigo,
                    p.nombre,
                    cp.nombre as categoria,
                    um.nombre as unidad,
                    p.precio_venta,
                    p.stock_actual,
                    p.stock_minimo,
                    p.stock_ilimitado,
                    CASE 
                        WHEN p.stock_ilimitado = 1 THEN 'ilimitado'
                        WHEN p.stock_actual <= 0 THEN 'sin_stock'
                        WHEN p.stock_actual <= p.stock_minimo THEN 'bajo'
                        ELSE 'normal'
                    END as estado_stock,
                    (p.stock_actual * p.precio_venta) as valor_inventario
                FROM productos p
                LEFT JOIN categorias_producto cp ON p.categoria_id = cp.id_categoria
                LEFT JOIN unidades_medida um ON p.unidad_id = um.id_unidad
                WHERE p.estado = 'activo'";
        $params = [];

        if (!empty($filters['categoria_id'])) {
            $sql .= " AND p.categoria_id = :categoria_id";
            $params['categoria_id'] = $filters['categoria_id'];
        }

        if (!empty($filters['estado_stock'])) {
            switch ($filters['estado_stock']) {
                case 'sin_stock':
                    $sql .= " AND p.stock_ilimitado = 0 AND p.stock_actual <= 0";
                    break;
                case 'bajo':
                    $sql .= " AND p.stock_ilimitado = 0 AND p.stock_actual > 0 AND p.stock_actual <= p.stock_minimo";
                    break;
                case 'normal':
                    $sql .= " AND p.stock_ilimitado = 0 AND p.stock_actual > p.stock_minimo";
                    break;
                case 'ilimitado':
                    $sql .= " AND p.stock_ilimitado = 1";
                    break;
            }
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.nombre LIKE :s1 OR p.codigo LIKE :s2)";
            $params['s1'] = '%' . $filters['search'] . '%';
            $params['s2'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY estado_stock ASC, p.nombre ASC LIMIT :perPage OFFSET :offset";
        $params['perPage'] = $perPage;
        $params['offset']  = $offset;

        return $this->db->query($sql, $params);
    }

    public function countEstadoInventario($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM productos p
                LEFT JOIN categorias_producto cp ON p.categoria_id = cp.id_categoria
                WHERE p.estado = 'activo'";
        $params = [];

        if (!empty($filters['categoria_id'])) {
            $sql .= " AND p.categoria_id = :categoria_id";
            $params['categoria_id'] = $filters['categoria_id'];
        }

        if (!empty($filters['estado_stock'])) {
            switch ($filters['estado_stock']) {
                case 'sin_stock':
                    $sql .= " AND p.stock_ilimitado = 0 AND p.stock_actual <= 0";
                    break;
                case 'bajo':
                    $sql .= " AND p.stock_ilimitado = 0 AND p.stock_actual > 0 AND p.stock_actual <= p.stock_minimo";
                    break;
                case 'normal':
                    $sql .= " AND p.stock_ilimitado = 0 AND p.stock_actual > p.stock_minimo";
                    break;
                case 'ilimitado':
                    $sql .= " AND p.stock_ilimitado = 1";
                    break;
            }
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.nombre LIKE :s1 OR p.codigo LIKE :s2)";
            $params['s1'] = '%' . $filters['search'] . '%';
            $params['s2'] = '%' . $filters['search'] . '%';
        }

        $result = $this->db->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function getTotalesInventario() {
        $sql = "SELECT 
                    COUNT(*) as total_productos,
                    SUM(CASE WHEN stock_ilimitado = 0 AND stock_actual <= 0 THEN 1 ELSE 0 END) as sin_stock,
                    SUM(CASE WHEN stock_ilimitado = 0 AND stock_actual > 0 AND stock_actual <= stock_minimo THEN 1 ELSE 0 END) as stock_bajo,
                    SUM(CASE WHEN stock_ilimitado = 1 THEN 1 ELSE 0 END) as ilimitados,
                    SUM(CASE WHEN stock_ilimitado = 0 THEN stock_actual * precio_venta ELSE 0 END) as valor_total
                FROM productos
                WHERE estado = 'activo'";

        return $this->db->queryOne($sql);
    }

    public function getCategorias() {
        $sql = "SELECT id_categoria, nombre FROM categorias_producto WHERE estado = 'activo' ORDER BY nombre ASC";
        return $this->db->query($sql);
    }

    // ============================================
    // RF-08.9: Movimientos de inventario por período
    // ============================================
    public function getMovimientosInventario($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT 
                    mi.id_movimiento,
                    mi.fecha,
                    p.nombre as producto_nombre,
                    p.codigo as producto_codigo,
                    um.nombre as unidad,
                    mi.tipo,
                    mi.cantidad,
                    mi.saldo_anterior,
                    mi.saldo_actual,
                    mi.referencia_tipo,
                    mi.referencia_id,
                    mi.observaciones,
                    u.nombre_usuario
                FROM movimiento_inventario mi
                INNER JOIN productos p ON mi.producto_id = p.id_producto
                LEFT JOIN unidades_medida um ON p.unidad_id = um.id_unidad
                LEFT JOIN usuarios u ON mi.usuario_id = u.id_usuario
                WHERE 1=1";
        $params = [];

        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND mi.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND mi.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }

        if (!empty($filters['producto_id'])) {
            $sql .= " AND mi.producto_id = :producto_id";
            $params['producto_id'] = $filters['producto_id'];
        }

        if (!empty($filters['tipo'])) {
            $sql .= " AND mi.tipo = :tipo";
            $params['tipo'] = $filters['tipo'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.nombre LIKE :s1 OR p.codigo LIKE :s2)";
            $params['s1'] = '%' . $filters['search'] . '%';
            $params['s2'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY mi.fecha DESC, mi.id_movimiento DESC LIMIT :perPage OFFSET :offset";
        $params['perPage'] = $perPage;
        $params['offset']  = $offset;

        return $this->db->query($sql, $params);
    }

    public function countMovimientosInventario($filters = []) {
        $sql = "SELECT COUNT(*) as total
                FROM movimiento_inventario mi
                INNER JOIN productos p ON mi.producto_id = p.id_producto
                WHERE 1=1";
        $params = [];

        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND mi.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND mi.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }

        if (!empty($filters['producto_id'])) {
            $sql .= " AND mi.producto_id = :producto_id";
            $params['producto_id'] = $filters['producto_id'];
        }

        if (!empty($filters['tipo'])) {
            $sql .= " AND mi.tipo = :tipo";
            $params['tipo'] = $filters['tipo'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.nombre LIKE :s1 OR p.codigo LIKE :s2)";
            $params['s1'] = '%' . $filters['search'] . '%';
            $params['s2'] = '%' . $filters['search'] . '%';
        }

        $result = $this->db->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function getTotalesMovimientos($filters = []) {
        $sql = "SELECT 
                    COUNT(*) as total_movimientos,
                    SUM(CASE WHEN tipo IN ('ENTRADA_COMPRA', 'DEVOLUCION_CLIENTE') THEN cantidad ELSE 0 END) as total_entradas,
                    SUM(CASE WHEN tipo IN ('SALIDA_VENTA', 'DEVOLUCION_PROVEEDOR', 'MERMA_DESPERDICIO', 'CONSUMO_INTERNO') THEN cantidad ELSE 0 END) as total_salidas
                FROM movimiento_inventario mi
                INNER JOIN productos p ON mi.producto_id = p.id_producto
                WHERE 1=1";
        $params = [];

        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND mi.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND mi.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }

        return $this->db->queryOne($sql, $params);
    }
}