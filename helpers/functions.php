<?php

function loadEnv(){
    $envFile = __DIR__. '/../.env';

    if(!file_exists($envFile)){
        die("ERROR: El archivo .env no existe o no se encuentra");
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach($lines as $line){
        if(strpos(trim($line), '#') === 0 || empty(trim($line))){
            continue;
        }

        list($key, $value) = explode('=', $line, 2);

        $key = trim($key);
        $value = trim($value);
        $value = trim($value, '"\'');

        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

function env($key, $default=null){
    if(isset($_ENV[$key])){
        return $_ENV[$key];
    }

    $value = getenv($key);
    if($value !== false){
        return $value;
    }

    return $default;
}

function redirect($url){
    header("Location: " . $url);
    exit();
}

function url($path = '') {
    $baseUrl = env('APP_URL', 'http://localhost');
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

function asset($path = '') {
    return url('assets/' . ltrim($path, '/'));
}

function basePath($path = '') {
    return ROOT_PATH . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
}

function e($string){
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function isAuthenticated() {
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['LAST_ACTIVITY'])) {
        return false;
    }
    
    $sessionLifetime = env('SESSION_LIFETIME', 120);
    $inactiveTime = time() - $_SESSION['LAST_ACTIVITY'];
    
    if ($inactiveTime > ($sessionLifetime * 60)) {
        session_unset();
        session_destroy();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return false;
    }
    
    $_SESSION['LAST_ACTIVITY'] = time();
    
    return true;
}

function authUserId() {
    return $_SESSION['usuario_id'] ?? null;
}

function authUserName() {
    return $_SESSION['usuario_nombre'] ?? null;
}

function authUserFullName() {
    return $_SESSION['usuario_nombre_completo'] ?? null;
}

function authUserRole() {
    return $_SESSION['usuario_rol'] ?? null;
}

function hasRole($roles) {
    if (!isAuthenticated()) {
        return false;
    }
    
    $userRole = authUserRole();
    
    if (is_array($roles)) {
        return in_array($userRole, $roles);
    }
    
    return $userRole === $roles;
}

function requireAuth() {
    if (!isAuthenticated()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        if (isset($_SESSION['LAST_ACTIVITY'])) {
            $sessionLifetime = env('SESSION_LIFETIME', 120);
            $inactiveTime = time() - $_SESSION['LAST_ACTIVITY'];
            
            if ($inactiveTime > ($sessionLifetime * 60)) {
                session_unset();
                session_destroy();
                redirect(url('login') . '?expired=1');
            }
        }
        
        redirect(url('login'));
    }
}

function requireRole($roles) {
    requireAuth();
    
    if (!hasRole($roles)) {
        redirect(url('sin-permisos'));
    }
}

function formatDate($date) {
    if (empty($date) || $date === '0000-00-00') {
        return '-';
    }
    
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date('d/m/Y', $timestamp);
}

function formatDateTime($datetime) {
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
        return '-';
    }
    
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    return date('d/m/Y H:i', $timestamp);
}

function formatMoney($amount, $showSymbol = true) {
    if ($amount === null) {
        $amount = 0;
    }
    
    $formatted = number_format((float)$amount, 2, '.', ',');
    return $showSymbol ? 'Bs ' . $formatted : $formatted;
}

function csrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function flash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

function setFlash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

function required($value) {
    return !empty(trim($value));
}

function validEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function minLength($value, $min) {
    return strlen($value) >= $min;
}

function maxLength($value, $max) {
    return strlen($value) <= $max;
}

function sanitize($string) {
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}

function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

function dump($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

function logMessage($message, $level = 'info') {
    $logFile = basePath('storage/logs/app.log');
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

function getSessionTimeLeft() {
    if (!isset($_SESSION['LAST_ACTIVITY'])) {
        return 0;
    }
    
    $sessionLifetime = env('SESSION_LIFETIME', 120);
    $inactiveTime = time() - $_SESSION['LAST_ACTIVITY'];
    $timeLeft = ($sessionLifetime * 60) - $inactiveTime;
    
    return max(0, floor($timeLeft / 60));
}

function renewSession() {
    if (isset($_SESSION['LAST_ACTIVITY'])) {
        $_SESSION['LAST_ACTIVITY'] = time();
    }
}

function forceLogout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    session_unset();
    session_destroy();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function hasPermission($modulo, $accion = 'ver') {
    if (!isAuthenticated()) {
        return false;
    }
    
    if (hasRole('Administrador')) {
        return true;
    }
    
    if (!isset($_SESSION['usuario_permisos'])) {
        return false;
    }
    
    $permisos = $_SESSION['usuario_permisos'];
    
    if (!isset($permisos[$modulo])) {
        return false;
    }
    
    if ($permisos[$modulo] === 'all') {
        return true;
    }
    
    if (is_array($permisos[$modulo])) {
        return in_array($accion, $permisos[$modulo]);
    }
    
    return false;
}

function requirePermission($modulo, $accion = 'ver') {
    requireAuth();
    
    if (!hasPermission($modulo, $accion)) {
        redirect(url('sin-permisos'));
    }
}