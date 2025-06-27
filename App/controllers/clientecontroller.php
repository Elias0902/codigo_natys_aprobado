<?php

use App\Natys\models\cliente;

$cliente = new cliente();

$action = $_REQUEST['action'] ?? 'listar';

switch ($action) {
    case 'formNuevo':
        include 'app/views/cliente/formulario.php';
        break;

case 'formEditar':
    header('Content-Type: application/json');
    if (isset($_GET['ced_cliente'])) {
        $cedula = $_GET['ced_cliente'];
        $datos = $cliente->obtenerCliente($cedula);
        
        if ($datos) {
            echo json_encode([
                'success' => true,
                'message' => 'Datos del cliente cargados',
                'data' => [
                    'ced_cliente' => $datos['ced_cliente'],
                    'nomcliente' => $datos['nomcliente'],
                    'correo' => $datos['correo'],
                    'telefono' => $datos['telefono'],
                    'direccion' => $datos['direccion']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Falta la cédula del cliente'
        ]);
    }
    exit();
    break;

    case 'guardar':
    error_log(print_r($_POST, true)); 
    header('Content-Type: application/json');
    if (isset($_POST['ced_cliente'], $_POST['nomcliente'], $_POST['correo'], $_POST['telefono'], $_POST['direccion'])) {
        $cliente = new cliente();
        $cliente->ced_cliente = $_POST['ced_cliente'];
        $cliente->nomcliente = $_POST['nomcliente'];
        $cliente->correo = $_POST['correo'];
        $cliente->telefono = $_POST['telefono'];
        $cliente->direccion = $_POST['direccion'];

        if ($cliente->exists()) {
            echo json_encode(['success' => false, 'message' => 'La cédula ya existe']);
            exit;
        }

        if ($cliente->guardar()) {
            echo json_encode([
                'success' => true,
                'message' => 'Cliente guardado exitosamente',
                'data' => [
                    'ced_cliente' => $cliente->ced_cliente,
                    'nomcliente' => $cliente->nomcliente,
                    'correo' => $cliente->correo,
                    'telefono' => $cliente->telefono,
                    'direccion' => $cliente->direccion,
                    'estado' => 1
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el cliente']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    }
    break;

    case 'actualizar':
    header('Content-Type: application/json');
    if (isset($_POST['original_cedula'], $_POST['ced_cliente'], $_POST['nomcliente'], $_POST['correo'], $_POST['telefono'], $_POST['direccion'])) {
        if ($_POST['original_cedula'] !== $_POST['ced_cliente']) {
            echo json_encode(['success' => false, 'message' => 'No se permite cambiar la cédula']);
            break;
        }

        $cliente = new cliente();
        // Asignar los valores POST al objeto cliente
        $cliente->ced_cliente = $_POST['ced_cliente'];
        $cliente->nomcliente = $_POST['nomcliente'];
        $cliente->correo = $_POST['correo'];
        $cliente->telefono = $_POST['telefono'];
        $cliente->direccion = $_POST['direccion'];

        $resultado = $cliente->actualizar();
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Actualizado exitosamente',
                'data' => [
                    'ced_cliente' => $cliente->ced_cliente,
                    'nomcliente' => $cliente->nomcliente,
                    'correo' => $cliente->correo,
                    'telefono' => $cliente->telefono,
                    'direccion' => $cliente->direccion
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Faltan datos para actualizar']);
    }
    break;

    case 'eliminar':
        header('Content-Type: application/json');
        if (isset($_POST['cedula'])) {
            $cliente->ced_cliente = $_POST['cedula'];
            $resultado = $cliente->eliminar();
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Eliminado exitosamente' : 'Error al eliminar'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta la cédula']);
        }
        break;

    case 'restaurar':
        header('Content-Type: application/json');
        if (isset($_POST['cedula'])) {
            $cliente->ced_cliente = $_POST['cedula'];
            $resultado = $cliente->restaurar();
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Restaurado exitosamente' : 'Error al restaurar'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta la cédula']);
        }
        break;

    case 'listarEliminados':
    header('Content-Type: application/json');
    $clientes = $cliente->listarEliminados();
    echo json_encode(['data' => $clientes]);
    exit;
    break;

case 'listar':
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        $clientes = $cliente->listar();
        echo json_encode(['data' => $clientes]);
    } else {
        $clientes = $cliente->listar();
        include 'app/views/cliente/listar.php';
    }
    break;
    
}