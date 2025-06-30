<?php
// Obtener la acción solicitada, con valor por defecto 'index'
$action = $_REQUEST['action'] ?? 'index';

// Manejar las diferentes acciones
switch ($action) {
    case 'index':
        // Incluir la vista principal
        include 'app/views/error/error404.php';
        break;
        
    // Puedes agregar más casos según necesites
    // case 'otra-accion':
    //     include 'app/views/otra-vista.php';
    //     break;
        
    default:
        // Manejar acción no reconocida (opcional)
        include 'app/views/home/home.php';
        break;
}