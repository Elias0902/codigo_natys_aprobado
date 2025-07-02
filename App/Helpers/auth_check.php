<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Acceso no autorizado', 'redirect' => 'index.php?url=login']);
        exit;
    } else {
        $_SESSION['error_login'] = 'Debes iniciar sesión para acceder a esta página';
        header('Location: index.php?url=login');
        exit;
    }
}