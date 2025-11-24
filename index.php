<?php
    // Configurar la zona horaria
    date_default_timezone_set('America/Caracas');

    require 'vendor/autoload.php';

    use App\Natys\controllers\FrontController;

    $frontController = new FrontController();

    // También configurar la zona horaria para la sesión
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Actualizar la hora de última actividad con la zona horaria correcta
    if (isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    }
?>