<?php
require_once 'App/Helpers/auth_check.php';
$action = $_REQUEST['action'] ?? 'index';

// Manejar las diferentes acciones
switch ($action) {
    case 'index':
        // Incluir la vista principal
        include 'app/views/home/home.php';
        break;
        
    // Puedes agregar más casos según necesites
    // case 'otra-accion':
    //     include 'app/views/otra-vista.php';
    //     break;
        
    default:
        // Manejar acción no reconocida (opcional)
        include 'app/views/404.php';
        break;
}