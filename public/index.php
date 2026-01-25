<?php

ob_start();

// Usamos realpath para obtener rutas absolutas limpias
define('ROOT_PATH', realpath(dirname(__DIR__))); 
define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'app');
define('PUBLIC_PATH', __DIR__);

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. CARGA ÚNICA DE COMPOSER
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

// 2. Cargamos las variables de entorno
loadEnv();

date_default_timezone_set('America/La_Paz');

$sessionLifetime = env('SESSION_LIFETIME', 120);

ini_set('session.gc_maxlifetime', $sessionLifetime * 60);
ini_set('session.cookie_lifetime', $sessionLifetime * 60);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_only_cookies', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['LAST_ACTIVITY'])) {
    $inactiveTime = time() - $_SESSION['LAST_ACTIVITY'];
    
    if ($inactiveTime > ($sessionLifetime * 60)) {
        session_unset();
        session_destroy();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $currentUrl = $_SERVER['REQUEST_URI'];
        if (strpos($currentUrl, 'login') === false) {
            header('Location: ' . url('login') . '?expired=1');
            exit();
        }
    }
}

if (isset($_SESSION['usuario_id'])) {
    $_SESSION['LAST_ACTIVITY'] = time();
}

/** 
 * require_once ROOT_PATH . '/core/Database.php';
 * require_once ROOT_PATH . '/core/Model.php';
 * require_once ROOT_PATH . '/core/View.php';
 * require_once ROOT_PATH . '/core/Controller.php';
 * require_once ROOT_PATH . '/core/Router.php';
*/

try {
    $db = Database::getInstance();
    
} catch (Exception $e) {
    die("<h1>Error del Sistema</h1><p>No se pudo conectar a la base de datos. Contacte al administrador.</p>");
}

$router = new Router();

require_once ROOT_PATH . '/config/routes.php';

try {
    $router->resolve();
} catch (Exception $e) {
    logMessage("Error en ruta: " . $e->getMessage(), 'error');
    
    if (env('APP_ENV') === 'development') {
        echo "<h1>Error en el sistema</h1>";
        echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
        echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } else {
        echo "<h1>Error</h1><p>Ocurrió un error en el sistema. Por favor, intente más tarde.</p>";
    }
}

ob_end_flush();