<?php

$router->get('/', 'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');

// Rutas del login
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@doLogin');
$router->get('/logout', 'AuthController@logout');
$router->get('/cambiar-password', 'AuthController@showChangePassword');
$router->post('/cambiar-password', 'AuthController@doChangePassword');

// Rutas del cliente
$router->get('/clientes', 'ClienteController@index');
$router->get('/clientes/crear', 'ClienteController@crear');
$router->post('/clientes/guardar', 'ClienteController@guardar');
$router->get('/clientes/editar/{id}', 'ClienteController@editar');
$router->post('/clientes/actualizar/{id}', 'ClienteController@actualizar');
$router->get('/clientes/ver/{id}', 'ClienteController@ver');
$router->post('/clientes/cambiar-estado/{id}', 'ClienteController@cambiarEstado');
$router->post('/clientes/eliminar/{id}', 'ClienteController@eliminar');

// Rutas de los productos
$router->get('/productos', 'ProductoController@index');
$router->get('/productos/crear', 'ProductoController@crear');
$router->post('/productos/guardar', 'ProductoController@guardar');
$router->get('/productos/editar/{id}', 'ProductoController@editar');
$router->post('/productos/actualizar/{id}', 'ProductoController@actualizar');
$router->get('/productos/ver/{id}', 'ProductoController@ver');
$router->post('/productos/cambiar-estado/{id}', 'ProductoController@cambiarEstado');
$router->post('/productos/eliminar/{id}', 'ProductoController@eliminar');

// Ruras de las facturas
$router->get('/facturas', 'FacturaController@index');
$router->get('/facturas/crear', 'FacturaController@crear');
$router->get('/facturas/buscar-clientes', 'FacturaController@buscarClientes');
$router->get('/facturas/buscar-productos', 'FacturaController@buscarProductos');
$router->post('/facturas/guardar', 'FacturaController@guardar');
$router->get('/facturas/ver/{id}', 'FacturaController@ver');
$router->post('/facturas/anular/{id}', 'FacturaController@anular');
$router->get('/facturas/imprimir/{id}', 'FacturaController@imprimir');
$router->get('/facturas/pdf/{id}', 'FacturaController@exportarPdf');
$router->get('/facturas/excel', 'FacturaController@exportarExcel');
$router->get('/facturas/editar/{id}', 'FacturaController@editar');
$router->post('/facturas/actualizar/{id}', 'FacturaController@actualizar');
// Adelantos de facturas
$router->post('/facturas/{id}/agregar-adelanto', 'FacturaController@agregarAdelanto');
$router->post('/facturas/adelanto/editar/{id}', 'FacturaController@editarAdelanto');
$router->post('/facturas/adelanto/eliminar/{id}', 'FacturaController@eliminarAdelanto');

// Granos
$router->get('/granos', 'GranoController@index');
$router->get('/granos/crear', 'GranoController@crear');
$router->post('/granos/guardar', 'GranoController@guardar');
$router->get('/granos/editar/{id}', 'GranoController@editar');
$router->post('/granos/actualizar/{id}', 'GranoController@actualizar');
$router->get('/granos/ver/{id}', 'GranoController@ver');
$router->get('/granos/precios/{id}', 'GranoController@precios');
$router->post('/granos/registrar-precio/{id}', 'GranoController@registrarPrecio');
$router->post('/granos/cambiar-estado/{id}', 'GranoController@cambiarEstado');
$router->post('/granos/eliminar/{id}', 'GranoController@eliminar');

// Acopios
$router->get('/acopios', 'AcopioController@index');
$router->get('/acopios/crear', 'AcopioController@crear');
$router->get('/acopios/buscar-clientes', 'AcopioController@buscarClientes');
$router->get('/acopios/buscar-granos', 'AcopioController@buscarGranos');
$router->post('/acopios/actualizar-precio-grano', 'AcopioController@actualizarPrecioGrano');
$router->post('/acopios/guardar', 'AcopioController@guardar');
$router->get('/acopios/ver/{id}', 'AcopioController@ver');
$router->post('/acopios/anular/{id}', 'AcopioController@anular');
$router->get('/acopios/imprimir/{id}', 'AcopioController@imprimir');
$router->get('/acopios/pdf/{id}', 'AcopioController@exportarPdf');
$router->get('/acopios/excel', 'AcopioController@exportarExcel');
$router->get('/acopios/editar/{id}', 'AcopioController@editar');
$router->post('/acopios/actualizar/{id}', 'AcopioController@actualizar');

// Cuenta Corriente
$router->get('/cuenta-corriente', 'CuentaCorrienteController@index');
$router->get('/cuenta-corriente/clientes', 'CuentaCorrienteController@clientes');
$router->get('/cuenta-corriente/ver-cliente/{id}', 'CuentaCorrienteController@verCliente');
$router->get('/cuenta-corriente/pdf/{id}', 'CuentaCorrienteController@exportarPdfCliente');
$router->get('/cuenta-corriente/excel', 'CuentaCorrienteController@exportarExcel');

// Pagos
$router->get('/pagos', 'PagoController@index');
$router->get('/pagos/crear', 'PagoController@crear');
$router->post('/pagos/guardar', 'PagoController@guardar');
$router->get('/pagos/ver/{id}', 'PagoController@ver');
$router->get('/pagos/anular/{id}', 'PagoController@anular');
$router->get('/pagos/recibo/{id}', 'PagoController@recibo');
$router->get('/pagos/saldo-cliente', 'PagoController@saldoCliente');
$router->get('/pagos/buscar-clientes', 'PagoController@buscarClientes');

// Reportes
$router->get('/reportes', 'ReporteController@index');
$router->get('/reportes/clientes-deudores', 'ReporteController@clientesDeudores');
$router->get('/reportes/clientes-acreedores', 'ReporteController@clientesAcreedores');
$router->get('/reportes/ventas-por-periodo', 'ReporteController@ventasPorPeriodo');
$router->get('/reportes/acopios-por-periodo', 'ReporteController@acopiosPorPeriodo');
$router->get('/reportes/productos-mas-vendidos', 'ReporteController@productosMasVendidos');
$router->get('/reportes/granos-mas-acopiados', 'ReporteController@granosMasAcopiados');
$router->get('/reportes/rentabilidad-por-cliente', 'ReporteController@rentabilidadPorCliente');
$router->get('/reportes/estado-inventario', 'ReporteController@estadoInventario');
$router->get('/reportes/movimientos-inventario', 'ReporteController@movimientosInventario');