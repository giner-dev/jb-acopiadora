<?php

class Controller{
    protected $view;

    public function __construct(){
        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }

        $this->view = new View();
    }

    protected function render($viewPath, $data = []){
        $this->view->render($viewPath, $data);
    }

    protected function json($data, $statusCode = 200){
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    protected function redirect($url) {
        redirect($url);
    }

    protected function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        redirect($referer);
    }

    protected function validateMethod($method) {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            throw new Exception("Método no permitido. Se esperaba: $method");
        }
        return true;
    }

    protected function requireAuth() {
        requireAuth();
    }

    protected function requireRole($roles) {
        requireRole($roles);
    }

    protected function requirePermission($modulo, $accion = 'ver') {
        requirePermission($modulo, $accion);
    }

    protected function hasPermission($modulo, $accion = 'ver') {
        return hasPermission($modulo, $accion);
    }

    protected function getPost($key, $default = null) {
        if (isset($_POST[$key])) {
            return sanitize($_POST[$key]);
        }
        return $default;
    }

    protected function getQuery($key, $default = null) {
        if (isset($_GET[$key])) {
            return sanitize($_GET[$key]);
        }
        return $default;
    }

    protected function getAllPost() {
        $data = [];
        foreach ($_POST as $key => $value) {
            $data[$key] = sanitize($value);
        }
        return $data;
    }

    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $rulesArray = explode('|', $rule);
            
            foreach ($rulesArray as $singleRule) {
                $params = explode(':', $singleRule);
                $ruleName = $params[0];
                $ruleValue = $params[1] ?? null;
                
                $value = $data[$field] ?? '';
                
                switch ($ruleName) {
                    case 'required':
                        if (!required($value)) {
                            $errors[$field][] = "El campo $field es obligatorio";
                        }
                        break;
                        
                    case 'email':
                        if (!empty($value) && !validEmail($value)) {
                            $errors[$field][] = "El campo $field debe ser un email válido";
                        }
                        break;
                        
                    case 'min':
                        if (!minLength($value, $ruleValue)) {
                            $errors[$field][] = "El campo $field debe tener mínimo $ruleValue caracteres";
                        }
                        break;
                        
                    case 'max':
                        if (!maxLength($value, $ruleValue)) {
                            $errors[$field][] = "El campo $field debe tener máximo $ruleValue caracteres";
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }

    protected function setSuccess($message) {
        setFlash('success', $message);
    }

    protected function setError($message) {
        setFlash('error', $message);
    }

    protected function setWarning($message) {
        setFlash('warning', $message);
    }

    protected function setInfo($message) {
        setFlash('info', $message);
    }
}