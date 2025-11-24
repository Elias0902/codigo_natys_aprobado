<?php
session_start();

if (isset($_SESSION['usuario'])) {
    $inactive = 300; 
    $warning_time = 60; 
    
    $session_life = time() - ($_SESSION['last_activity'] ?? 0);

    if ($session_life > ($inactive - $warning_time)) {
        $remaining = $inactive - $session_life;
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'Tu sesión expirará en '.$remaining.' segundos por inactividad',
                'timeout_warning' => true,
                'remaining' => $remaining
            ]);
            exit;
        }
    }
    
    if ($session_life > $inactive) {
        session_unset();
        session_destroy();
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Sesión expirada por inactividad',
                'redirect' => 'index.php?url=user&type=login&Reason=Expulsion-Por-inactividad',
                'timeout' => true
            ]);
            exit;
        } else {
            $_SESSION['error_login'] = 'Tu sesión ha expirado por inactividad';
            header('Location: index.php?url=user&type=login&Reason=Expulsion-Por-inactividad');
            exit;
        }
    }
}

$_SESSION['last_activity'] = time();

if (!isset($_SESSION['usuario'])) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Acceso no autorizado', 
            'redirect' => 'index.php?url=user&type=login'
        ]);
        exit;
    } else {
        $_SESSION['error_login'] = 'Debes iniciar sesión para acceder a esta página';
        header('Location: index.php?url=user&type=login');
        exit;
    }
}


function verificarRol($rolesPermitidos) {
    if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], $rolesPermitidos)) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'No tienes permisos para esta acción'
            ]);
            exit;
        } else {
            $_SESSION['error_login'] = 'No tienes permisos para acceder a esta página';
            header('Location: index.php?url=home');
            exit;
        }
    }
}