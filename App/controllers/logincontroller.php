<?php
use App\Natys\Models\Login;

session_start();

$login = new Login();

$action = $_REQUEST['action'] ?? 'mostrarFormulario';

switch ($action) {
    case 'autenticar':
    if (empty($_POST['usuario']) || empty($_POST['clave'])) {

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Usuario y contraseña son requeridos']);
            exit;
        } else {
            $_SESSION['error_login'] = 'Usuario y contraseña son requeridos';
            header('Location: index.php?url=login&action=mostrarFormulario');
            exit;
        }
    }

    $login->usuario = $_POST['usuario'];
    $login->clave = $_POST['clave'];

    $usuario = $login->validarUsuario();

    if ($usuario && $login->verificarClave($usuario['clave'], $login->clave)) {
        $_SESSION['usuario'] = [
            'id' => $usuario['id'],
            'usuario' => $usuario['usuario'],
            'rol' => $usuario['rol']
        ];

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'redirect' => 'index.php?url=home']);
        } else {
            header('Location: index.php?url=home');
        }
        exit;
    } else {

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos']);
        } else {
            $_SESSION['error_login'] = 'Usuario o contraseña incorrectos';
            header('Location: index.php?url=login&action=mostrarFormulario');
        }
        exit;
    }
    break;

    case 'mostrarFormulario':
        include 'app/views/login/formulario.php';
        break;

    case 'mostrarRecuperar':
        include 'app/views/login/recuperar.php';
        break;

    case 'solicitarRecuperacion':
        header('Content-Type: application/json');
        
        if (empty($_POST['correo'])) {
            echo json_encode(['success' => false, 'message' => 'El correo es requerido']);
            exit;
        }

        $login->correo_usuario = $_POST['correo'];
        $usuario = $login->obtenerUsuarioPorCorreo();

        if ($usuario) {

            echo json_encode([
                'success' => true, 
                'message' => 'Se ha enviado un enlace de recuperación a tu correo',
                'usuario' => $usuario['usuario'] 
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Correo no registrado']);
        }
        break;

    case 'cambiarClave':
        header('Content-Type: application/json');
        
        if (empty($_POST['correo']) || empty($_POST['clave']) || empty($_POST['confirmar_clave'])) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
            exit;
        }

        if ($_POST['clave'] !== $_POST['confirmar_clave']) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
            exit;
        }

        $login->correo_usuario = $_POST['correo'];
        $resultado = $login->actualizarClave($_POST['clave']);

        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña']);
        }
        break;

    case 'cerrarSesion':
        session_destroy();
        header('Location: index.php?url=login&action=mostrarFormulario');
        exit;
        break;

    default:
        include 'app/views/login/formulario.php';
        break;
}