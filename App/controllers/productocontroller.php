<?php

use App\Natys\Models\Producto;

$producto = new Producto();

$action = $_REQUEST['action'] ?? 'listar';

switch ($action) {
    case 'formNuevo':
        include 'app/views/producto/formulario.php';
        break;

    case 'formEditar':
        header('Content-Type: application/json');
        if (isset($_GET['cod_producto'])) {
            $codigo = $_GET['cod_producto'];
            $datos = $producto->obtenerProducto($codigo);
            
            if ($datos) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Datos del producto cargados',
                    'data' => [
                        'cod_producto' => $datos['cod_producto'],
                        'nombre' => $datos['nombre'],
                        'precio' => $datos['precio'],
                        'unidad' => $datos['unidad']
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Falta el código del producto'
            ]);
        }
        exit();
        break;

    case 'guardar':
        header('Content-Type: application/json');
        if (isset($_POST['cod_producto'], $_POST['nombre'], $_POST['precio'], $_POST['unidad'])) {
            $producto = new Producto();
            $producto->cod_producto = $_POST['cod_producto'];
            $producto->nombre = $_POST['nombre'];
            $producto->precio = $_POST['precio'];
            $producto->unidad = $_POST['unidad'];

            if ($producto->exists()) {
                echo json_encode(['success' => false, 'message' => 'El código de producto ya existe']);
                exit;
            }

            if ($producto->guardar()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto guardado exitosamente',
                    'data' => [
                        'cod_producto' => $producto->cod_producto,
                        'nombre' => $producto->nombre,
                        'precio' => $producto->precio,
                        'unidad' => $producto->unidad,
                        'estado' => 1
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar el producto']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
        }
        break;

    case 'actualizar':
        header('Content-Type: application/json');
        if (isset($_POST['original_codigo'], $_POST['cod_producto'], $_POST['nombre'], $_POST['precio'], $_POST['unidad'])) {
            if ($_POST['original_codigo'] !== $_POST['cod_producto']) {
                echo json_encode(['success' => false, 'message' => 'No se permite cambiar el código del producto']);
                break;
            }

            $producto = new Producto();
            $producto->cod_producto = $_POST['cod_producto'];
            $producto->nombre = $_POST['nombre'];
            $producto->precio = $_POST['precio'];
            $producto->unidad = $_POST['unidad'];

            $resultado = $producto->actualizar();
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto actualizado exitosamente',
                    'data' => [
                        'cod_producto' => $producto->cod_producto,
                        'nombre' => $producto->nombre,
                        'precio' => $producto->precio,
                        'unidad' => $producto->unidad
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el producto']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Faltan datos para actualizar']);
        }
        break;

    case 'eliminar':
        header('Content-Type: application/json');
        if (isset($_POST['codigo'])) {
            $producto->cod_producto = $_POST['codigo'];
            $resultado = $producto->eliminar();
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Producto eliminado exitosamente' : 'Error al eliminar el producto'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta el código del producto']);
        }
        break;

    case 'restaurar':
        header('Content-Type: application/json');
        if (isset($_POST['codigo'])) {
            $producto->cod_producto = $_POST['codigo'];
            $resultado = $producto->restaurar();
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Producto restaurado exitosamente' : 'Error al restaurar el producto'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta el código del producto']);
        }
        break;

    case 'listarEliminados':
        header('Content-Type: application/json');
        $productos = $producto->listarEliminados();
        echo json_encode(['data' => $productos]);
        exit;
        break;

case 'listar':
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        $productos = $producto->listar();
        echo json_encode([
            'data' => $productos // Asegúrate que esto es un array numérico
        ]);
    } else {
        $productos = $producto->listar();
        include 'app/views/producto/listar.php';
    }
    break;
}