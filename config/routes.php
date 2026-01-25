<?php

$router->get('/', 'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');

// Ritas del login
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@doLogin');
$router->get('/logout', 'AuthController@logout');
$router->get('/cambiar-password', 'AuthController@showChangePassword');
$router->post('/cambiar-password', 'AuthController@doChangePassword');