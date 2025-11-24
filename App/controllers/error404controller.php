<?php
require_once 'App/Helpers/auth_check.php';
$action = $_REQUEST['action'] ?? 'index';


switch ($action) {
    case 'index':
        
        include 'app/views/error/error404.php';
        break;

    default:
        
        include 'app/views/home/home.php';
        break;
}