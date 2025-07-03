<?php
require_once 'App/Helpers/auth_check.php';
use App\Natys\Models\Perfil;

$perfil = new Perfil();

$action = $_REQUEST['action'] ?? 'miperfil';

switch ($action) {
    case 'miperfil':
        include 'app/views/perfil/perfil.php';
        break;
        
    case 'cambiarClave':
        header('Content-Type: application/json');
        
        if (empty($_POST['clave_actual']) || empty($_POST['nueva_clave']) || empty($_POST['confirmar_clave'])) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
            exit;
        }
        
        if ($_POST['nueva_clave'] !== $_POST['confirmar_clave']) {
            echo json_encode(['success' => false, 'message' => 'Las nuevas contraseñas no coinciden']);
            exit;
        }
        
        $resultado = $perfil->cambiarClave(
            $_SESSION['usuario']['id'],
            $_POST['clave_actual'],
            $_POST['nueva_clave']
        );
        
        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
        }
        break;
        
    case 'formEditar':
        header('Content-Type: application/json');
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $datos = $perfil->obtenerPerfil($id);
            
            if ($datos) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Datos del perfil cargados',
                    'data' => [
                        'id' => $datos['id'],
                        'correo_usuario' => $datos['correo_usuario'],
                        'usuario' => $datos['usuario'],
                        'rol' => $datos['rol']
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Perfil no encontrado'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Falta el ID del usuario'
            ]);
        }
        exit();
        break;

    case 'actualizar':
        header('Content-Type: application/json');
        if (isset($_POST['id'], $_POST['correo_usuario'], $_POST['usuario'], $_POST['rol'])) {
            $perfil->id = $_POST['id'];
            $perfil->correo_usuario = $_POST['correo_usuario'];
            $perfil->usuario = $_POST['usuario'];
            $perfil->rol = $_POST['rol'];

            // Solo actualizar contraseña si se proporcionó una nueva
            if (!empty($_POST['clave'])) {
                $perfil->clave = $_POST['clave'];
            }

            $resultado = $perfil->actualizar();
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Perfil actualizado exitosamente',
                    'data' => [
                        'id' => $perfil->id,
                        'correo_usuario' => $perfil->correo_usuario,
                        'usuario' => $perfil->usuario,
                        'rol' => $perfil->rol
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Faltan datos para actualizar']);
        }
        break;

    case 'listar':
        // Verificar si el usuario tiene permiso (admin o superadmin)
        if (!in_array($_SESSION['usuario']['rol'], ['admin', 'superadmin'])) {
            header('Location: index.php?url=perfil&action=miperfil');
            exit;
        }
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            $perfiles = $perfil->listar();
            echo json_encode(['data' => $perfiles]);
        } else {
            $perfiles = $perfil->listar();
            include 'app/views/perfil/listar.php';
        }
        break;
        
    default:
        header('Location: index.php?url=perfil&action=miperfil');
        break;
}