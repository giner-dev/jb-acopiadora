<div class="page-header">
    <div>
        <h1><i class="fas fa-chart-bar"></i> Reportes</h1>
        <p>Análisis y seguimiento del sistema</p>
    </div>
</div>

<div class="rpt-menu-grid">
    <!-- CUENTA CORRIENTE -->
    <div class="rpt-menu-seccion">
        <div class="rpt-menu-seccion-titulo">
            <i class="fas fa-balance-scale"></i> Cuenta Corriente
        </div>
        <a href="<?php echo url('reportes/clientes-deudores'); ?>" class="rpt-menu-card rpt-card-deudor">
            <div class="rpt-menu-card-icon"><i class="fas fa-arrow-down"></i></div>
            <div class="rpt-menu-card-info">
                <h3>Clientes Deudores</h3>
                <p>Clientes que deben dinero a JB</p>
            </div>
        </a>
        <a href="<?php echo url('reportes/clientes-acreedores'); ?>" class="rpt-menu-card rpt-card-acreedor">
            <div class="rpt-menu-card-icon"><i class="fas fa-arrow-up"></i></div>
            <div class="rpt-menu-card-info">
                <h3>Clientes Acreedores</h3>
                <p>Clientes que JB les debe dinero</p>
            </div>
        </a>
        <a href="<?php echo url('reportes/rentabilidad-por-cliente'); ?>" class="rpt-menu-card rpt-card-rentabilidad">
            <div class="rpt-menu-card-icon"><i class="fas fa-chart-line"></i></div>
            <div class="rpt-menu-card-info">
                <h3>Rentabilidad por Cliente</h3>
                <p>Ventas vs Acopios por cliente</p>
            </div>
        </a>
    </div>

    <!-- ACTIVIDAD COMERCIAL -->
    <div class="rpt-menu-seccion">
        <div class="rpt-menu-seccion-titulo">
            <i class="fas fa-exchange-alt"></i> Actividad Comercial
        </div>
        <a href="<?php echo url('reportes/ventas-por-periodo'); ?>" class="rpt-menu-card rpt-card-ventas">
            <div class="rpt-menu-card-icon"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="rpt-menu-card-info">
                <h3>Ventas por Período</h3>
                <p>Facturas y totales por período</p>
            </div>
        </a>
        <a href="<?php echo url('reportes/acopios-por-periodo'); ?>" class="rpt-menu-card rpt-card-acopios">
            <div class="rpt-menu-card-icon"><i class="fas fa-wheat-awn"></i></div>
            <div class="rpt-menu-card-info">
                <h3>Acopios por Período</h3>
                <p>Cosechas recibidas por período</p>
            </div>
        </a>
        <a href="<?php echo url('reportes/productos-mas-vendidos'); ?>" class="rpt-menu-card rpt-card-productos">
            <div class="rpt-menu-card-icon"><i class="fas fa-boxes"></i></div>
            <div class="rpt-menu-card-info">
                <h3>Productos Más Vendidos</h3>
                <p>Ranking de productos por ventas</p>
            </div>
        </a>
        <a href="<?php echo url('reportes/granos-mas-acopiados'); ?>" class="rpt-menu-card rpt-card-granos">
            <div class="rpt-menu-card-icon"><i class="fas fa-leaf"></i></div>
            <div class="rpt-menu-card-info">
                <h3>Granos Más Acopiados</h3>
                <p>Ranking de granos por cantidad</p>
            </div>
        </a>
    </div>

    <!-- INVENTARIO -->
    <div class="rpt-menu-seccion">
        <div class="rpt-menu-seccion-titulo">
            <i class="fas fa-warehouse"></i> Inventario
        </div>
        <a href="<?php echo url('reportes/estado-inventario'); ?>" class="rpt-menu-card rpt-card-inventario">
            <div class="rpt-menu-card-icon"><i class="fas fa-cubes"></i></div>
            <div class="rpt-menu-card-info">
                <h3>Estado de Inventario</h3>
                <p>Stock actual de todos los productos</p>
            </div>
        </a>
        <a href="<?php echo url('reportes/movimientos-inventario'); ?>" class="rpt-menu-card rpt-card-movimientos">
            <div class="rpt-menu-card-icon"><i class="fas fa-arrows-alt"></i></div>
            <div class="rpt-menu-card-info">
                <h3>Movimientos de Inventario</h3>
                <p>Historial de entradas y salidas</p>
            </div>
        </a>
    </div>
</div>