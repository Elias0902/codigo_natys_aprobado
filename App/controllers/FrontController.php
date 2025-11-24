<?php

    namespace App\Natys\controllers;
    class FrontController {

        private $dir;
        private $controllers;        
        private $url;

        public function __construct() {
            if (isset($_REQUEST["url"]) && !empty($_REQUEST["url"])) {
                $this->url = strtolower($_REQUEST["url"]);
                $this->dir = 'App/controllers/';
                $this->controllers = 'controller.php';
                $this->getURL();

            } else {
                
                echo "Por Favor Espere, estamos redirigiendo....";
                
                die("<script>location='?url=user&type=login'</script>");
            }
        }

        private function getURL() {

            if(file_exists($this->dir.$this->url.$this->controllers)) {
                require_once($this->dir.$this->url.$this->controllers);
            
} else {

    echo "Error 404: controlador no encontrado";
    die("<script>'</script>");
}
        }

    }

?>