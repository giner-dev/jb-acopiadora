<?php

class View{
    private $viewsPath;
    private $layout = 'main';
    private $sharedData = [];
    private $sections = [];
    private $currentSection = null;
    
    public function __construct(){
        $this->viewsPath = APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
    }
    
    public function render($viewPath, $data = [], $layout = null){
        $layout = $layout !== null ? $layout : $this->layout;
        $data = array_merge($this->sharedData, $data);
        extract($data);
        
        ob_start();
        
        $viewFile = $this->viewsPath . str_replace('.', DIRECTORY_SEPARATOR, $viewPath) . '.php';
        
        if(!file_exists($viewFile)){
            throw new Exception("Vista no encontrada: $viewFile");
        }
        
        include $viewFile;
        
        $content = ob_get_clean();
        
        if ($layout) {
            $this->renderWithLayout($content, $layout, $data);
        } else {
            echo $content;
        }
    }
    
    private function renderWithLayout($content, $layout, $data) {
        extract($data);
        
        $layoutFile = $this->viewsPath . 'layouts' . DIRECTORY_SEPARATOR . $layout . '.php';
        
        if (!file_exists($layoutFile)) {
            throw new Exception("Layout no encontrado en la ruta: $layoutFile");    
        }
        
        include $layoutFile;
    }
    
    public function setLayout($layout) {
        $this->layout = $layout;
    }
    
    public function share($key, $value) {
        $this->sharedData[$key] = $value;
    }
    
    public function partial($partialPath, $data = []) {
        extract($data);
        
        $partialFile = $this->viewsPath . str_replace('.', DIRECTORY_SEPARATOR, $partialPath) . '.php';
        
        if (!file_exists($partialFile)) {
            throw new Exception("Parcial no encontrado: $partialFile");
        }
        
        include $partialFile;
    }
    
    public function exists($viewPath) {
        $viewFile = $this->viewsPath . str_replace('.', DIRECTORY_SEPARATOR, $viewPath) . '.php';
        return file_exists($viewFile);
    }
    
    public function section($name) {
        if (isset($this->sections[$name])) {
            echo $this->sections[$name];
        }
    }
    
    public function startSection($name) {
        $this->currentSection = $name;
        ob_start();
    }
    
    public function endSection() {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }
}

if (!function_exists('view')) {
    function view($viewPath, $data = []) {
        $view = new View();
        $view->render($viewPath, $data);
    }
}