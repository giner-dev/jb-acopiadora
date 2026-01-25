<?php

class Router {
    private $routes = [];
    private $currentRoute;
    private $currentMethod;
    
    public function __construct() {
        $this->currentRoute = $this->getRoute();
        $this->currentMethod = $_SERVER['REQUEST_METHOD'];
    }
    
    private function getRoute() {
        $route = $_SERVER['REQUEST_URI'];
        
        $position = strpos($route, '?');
        if ($position !== false) {
            $route = substr($route, 0, $position);
        }
        
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/') {
            $route = str_replace($scriptName, '', $route);
        }
        
        $route = '/' . trim($route, '/');
        
        return $route;
    }
    
    public function get($route, $handler) {
        $this->addRoute('GET', $route, $handler);
    }
    
    public function post($route, $handler) {
        $this->addRoute('POST', $route, $handler);
    }
    
    public function put($route, $handler) {
        $this->addRoute('PUT', $route, $handler);
    }
    
    public function delete($route, $handler) {
        $this->addRoute('DELETE', $route, $handler);
    }
    
    private function addRoute($method, $route, $handler) {
        $route = preg_replace('/{([a-zA-Z0-9_]+)}/', '([0-9]+)', $route);
        
        $this->routes[$method][$route] = $handler;
    }
    
    public function resolve() {
        $method = $this->currentMethod;
        $route = $this->currentRoute;
        
        if (!isset($this->routes[$method])) {
            $this->notFound();
            return;
        }
        
        foreach ($this->routes[$method] as $registeredRoute => $handler) {
            $pattern = '#^' . $registeredRoute . '$#';
            
            if (preg_match($pattern, $route, $matches)) {
                array_shift($matches);
                
                $this->executeHandler($handler, $matches);
                return;
            }
        }
        
        $this->notFound();
    }
    
    private function executeHandler($handler, $params = []) {
        $parts = explode('@', $handler);
        
        if (count($parts) !== 2) {
            throw new Exception("Handler inválido: $handler");
        }
        
        $controllerName = $parts[0];
        $methodName = $parts[1];
        
        $controllerFile = basePath('app/controllers/' . $controllerName . '.php');
        
        if (!file_exists($controllerFile)) {
            throw new Exception("Controlador no encontrado: $controllerFile");
        }
        
        require_once $controllerFile;
        
        if (!class_exists($controllerName)) {
            throw new Exception("Clase controlador no encontrada: $controllerName");
        }
        
        $controller = new $controllerName();
        
        if (!method_exists($controller, $methodName)) {
            throw new Exception("Método no encontrado: $controllerName::$methodName");
        }
        
        call_user_func_array([$controller, $methodName], $params);
    }
    
    private function notFound() {
        http_response_code(404);
        
        $view404 = basePath('app/views/errors/404.php');
        
        if (file_exists($view404)) {
            require_once $view404;
        } else {
            echo "<h1>404 - Página no encontrada</h1>";
            echo "<p>La ruta <strong>{$this->currentRoute}</strong> no existe.</p>";
        }
        exit();
    }
    
    public function getCurrentRoute() {
        return $this->currentRoute;
    }
    
    public function getCurrentMethod() {
        return $this->currentMethod;
    }
}