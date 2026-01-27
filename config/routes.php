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