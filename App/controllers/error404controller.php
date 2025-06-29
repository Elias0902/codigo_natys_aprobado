<?php
namespace App\Natys\controllers;

class Error404Controller {
    public function __construct() {
        http_response_code(404);
        $this->loadView('errors/404', [
            'title' => 'Página no encontrada'
        ]);
    }

    private function loadView($viewPath, $data = []) {
        extract($data);
        
        ob_start();
        
        $viewFile = dirname(__DIR__, 2) . '/app/views/' . $viewPath . '.php';
        
        if(file_exists($viewFile)) {
            require $viewFile;
        } else {
            die("Error: Vista de error no encontrada");
        }
        
        $content = ob_get_clean();
        
        $layoutFile = dirname(__DIR__, 2) . '/app/views/layouts/base.php';
        if(file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }
}