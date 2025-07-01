<?php

use App\Natys\models\Perfil;

$perfil = new Perfil();

$action = $_REQUEST['action'] ?? 'listar';

switch ($action) {
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
            $perfil = new Perfil();
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
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            $perfiles = $perfil->listar();
            echo json_encode(['data' => $perfiles]);
        } else {
            $perfiles = $perfil->listar();
            include 'app/views/perfil/listar.php';
        }
        break;
}