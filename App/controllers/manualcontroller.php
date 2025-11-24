<?php
require_once 'App/Helpers/auth_check.php';

class ManualController {
    private $manualDir = 'Assets/manuales/';
    
    public function __construct() {
        
        if (!file_exists($this->manualDir)) {
            mkdir($this->manualDir, 0777, true);
        }
    }
    
    public function index() {
        
        $archivos = [];
        if (is_dir($this->manualDir)) {
            $archivos = array_diff(scandir($this->manualDir), array('.', '..'));
            $archivos = array_filter($archivos, function($archivo) {
                return strtolower(pathinfo($archivo, PATHINFO_EXTENSION)) === 'pdf';
            });
        }
        
        include 'app/views/manual/index.php';
    }
    
    public function subir() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
            $archivo = $_FILES['archivo'];
            $nombreArchivo = basename($archivo['name']);
            $rutaDestino = $this->manualDir . $nombreArchivo;
            
            
            $tipoArchivo = strtolower(pathinfo($rutaDestino, PATHINFO_EXTENSION));
            if ($tipoArchivo !== 'pdf') {
                $_SESSION['error'] = 'Solo se permiten archivos PDF.';
                header('Location: index.php?url=manual');
                exit;
            }
            
            
            if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                $_SESSION['mensaje'] = 'Archivo subido correctamente.';
            } else {
                $_SESSION['error'] = 'Error al subir el archivo.';
            }
            
            header('Location: index.php?url=manual');
            exit;
        }
    }
    
    public function eliminar($nombreArchivo) {
        $rutaArchivo = $this->manualDir . basename($nombreArchivo);
        
        if (file_exists($rutaArchivo) && unlink($rutaArchivo)) {
            $_SESSION['mensaje'] = 'Archivo eliminado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al eliminar el archivo.';
        }
        
        header('Location: index.php?url=manual');
        exit;
    }
}


$action = $_GET['action'] ?? 'index';
$manualController = new ManualController();

switch ($action) {
    case 'subir':
        $manualController->subir();
        break;
    case 'eliminar':
        if (isset($_GET['archivo'])) {
            $manualController->eliminar($_GET['archivo']);
        }
        break;
    case 'index':
    default:
        $manualController->index();
        break;
}
