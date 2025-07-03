<?php
require_once 'App/Helpers/auth_check.php';
use App\Natys\models\Movimiento;

$movimiento = new Movimiento();

$action = $_REQUEST['action'] ?? 'listar';

switch ($action) {
    case 'formNuevo':
        include 'app/views/movimiento/formulario.php';
        break;

    case 'formEditar':
        header('Content-Type: application/json');
        if (isset($_GET['num_movimiento'])) {
            $id = $_GET['num_movimiento'];
            $datos = $movimiento->obtenerMovimiento($id);
            
            if ($datos) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Datos del movimiento cargados',
                    'data' => [
                        'num_movimiento' => $datos['num_movimiento'],
                        'fecha' => $datos['fecha'],
                        'observaciones' => $datos['observaciones'],
                        'cod_producto' => $datos['cod_producto'] ?? '',
                        'cant_productos' => $datos['cant_productos'] ?? ''
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Movimiento no encontrado'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Falta el número de movimiento'
            ]);
        }
        exit();
        break;

    case 'obtenerProductos':
        header('Content-Type: application/json');
        $productos = $movimiento->listarProductos();
        echo json_encode(['success' => true, 'data' => $productos]);
        exit();
        break;

    case 'guardar':
        header('Content-Type: application/json');
        if (isset($_POST['fecha'], $_POST['observaciones'], $_POST['producto'], $_POST['cantidad'])) {
            $movimiento = new Movimiento();
            $movimiento->fecha = $_POST['fecha'];
            $movimiento->observaciones = $_POST['observaciones'];
            $movimiento->cod_producto = $_POST['producto'];
            $movimiento->cant_productos = $_POST['cantidad'];
            $movimiento->precio_venta = 0; // Puedes modificar esto para obtener el precio del producto

            if ($movimiento->guardar()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Movimiento guardado exitosamente',
                    'data' => [
                        'num_movimiento' => $movimiento->num_movimiento,
                        'fecha' => $movimiento->fecha,
                        'observaciones' => $movimiento->observaciones,
                        'estado' => 1
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar el movimiento']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
        }
        break;

    case 'actualizar':
        header('Content-Type: application/json');
        if (isset($_POST['num_movimiento'], $_POST['fecha'], $_POST['observaciones'], $_POST['producto'], $_POST['cantidad'])) {
            $movimiento = new Movimiento();
            $movimiento->num_movimiento = $_POST['num_movimiento'];
            $movimiento->fecha = $_POST['fecha'];
            $movimiento->observaciones = $_POST['observaciones'];
            $movimiento->cod_producto = $_POST['producto'];
            $movimiento->cant_productos = $_POST['cantidad'];
            $movimiento->precio_venta = 0; // Puedes modificar esto para obtener el precio del producto

            $resultado = $movimiento->actualizar();
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Actualizado exitosamente',
                    'data' => [
                        'num_movimiento' => $movimiento->num_movimiento,
                        'fecha' => $movimiento->fecha,
                        'observaciones' => $movimiento->observaciones
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
        if (isset($_POST['num_movimiento'])) {
            $movimiento->num_movimiento = $_POST['num_movimiento'];
            $resultado = $movimiento->eliminar();
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Eliminado exitosamente' : 'Error al eliminar'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta el número de movimiento']);
        }
        break;

    case 'restaurar':
        header('Content-Type: application/json');
        if (isset($_POST['num_movimiento'])) {
            $movimiento->num_movimiento = $_POST['num_movimiento'];
            $resultado = $movimiento->restaurar();
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Restaurado exitosamente' : 'Error al restaurar'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta el número de movimiento']);
        }
        break;

    case 'listarEliminados':
        header('Content-Type: application/json');
        $movimientos = $movimiento->listarEliminados();
        echo json_encode(['data' => $movimientos]);
        exit;
        break;

    case 'listar':
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            $movimientos = $movimiento->listar();
            echo json_encode(['data' => $movimientos]);
        } else {
            $movimientos = $movimiento->listar();
            include 'app/views/movimiento/listar.php';
        }
        break;
}