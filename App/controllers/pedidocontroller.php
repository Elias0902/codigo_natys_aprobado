<?php
// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DEBUG_MODE', true);

require_once 'App/Helpers/auth_check.php';
use App\Natys\models\Pedido;
use App\Natys\models\Cliente;
use App\Natys\models\Producto;

$pedido = new Pedido();
$cliente = new Cliente();
$producto = new Producto();

$action = $_REQUEST['action'] ?? 'listar';

// Obtener clientes y productos activos para los formularios
$clientesActivos = $cliente->listar();
$productosActivos = $producto->listar();

// Pasar las variables a la vista
$clientes = $clientesActivos;
$productos = $productosActivos;

switch ($action) {
    case 'formNuevo':
        include 'app/views/pedido/formulario.php';
        break;

    case 'formEditar':
    case 'verDetalle':
        header('Content-Type: application/json');
        try {
            if (!isset($_GET['id_pedido'])) {
                throw new Exception('Falta el ID del pedido');
            }
            
            $pedidoData = $pedido->obtenerDetalle($_GET['id_pedido']);
            
            if (!$pedidoData) {
                throw new Exception('Pedido no encontrado');
            }
            
            $productosForm = [];
            if (isset($pedidoData['detalle']) && is_array($pedidoData['detalle'])) {
                $productosForm = array_map(function($item) {
                    return [
                        'cod_producto' => $item['cod_producto'],
                        'nombre' => $item['nombre_producto'],
                        'precio' => $item['precio'],
                        'cantidad' => $item['cantidad'],
                        'subtotal' => $item['subtotal']
                    ];
                }, $pedidoData['detalle']);
            }

            $response = [
                'success' => true,
                'data' => [
                    'id_pedido' => $pedidoData['pedido']['id_pedido'],
                    'ced_cliente' => $pedidoData['pedido']['ced_cliente'],
                    'fecha' => $pedidoData['pedido']['fecha'],
                    'productos' => $productosForm,
                    'modo_lectura' => ($_GET['action'] === 'verDetalle') // Indicar si es modo solo lectura
                ]
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Error al cargar el pedido: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'guardar':
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Error desconocido'];
        
        try {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error en el formato de los datos: ' . json_last_error_msg());
            }
            
            if (!isset($data['ced_cliente'], $data['productos'], $data['total'], $data['cant_producto'])) {
                throw new Exception('Faltan datos requeridos en la solicitud');
            }

            if (empty($data['productos'])) {
                throw new Exception('Debe agregar al menos un producto al pedido');
            }

            foreach ($data['productos'] as $index => $producto) {
                if (!isset($producto['cod_producto'], $producto['cantidad'], $producto['precio'], $producto['subtotal'])) {
                    throw new Exception('Producto en la posición ' . ($index + 1) . ' no tiene todos los campos requeridos');
                }
            }

            $id_pedido = $pedido->crearPedido(
                $data['ced_cliente'],
                $data['productos'],
                $data['total'],
                $data['cant_producto']
            );

            if ($id_pedido) {
                $response = [
                    'success' => true,
                    'message' => 'Pedido creado exitosamente (por pagar)',
                    'id_pedido' => $id_pedido
                ];
            } else {
                throw new Exception('No se pudo crear el pedido. Verifique los datos e intente nuevamente.');
            }
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
        
        if (ob_get_level() > 0) {
            ob_clean();
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;

    case 'actualizar':
        header('Content-Type: application/json');
        session_start(); // Asegurar que la sesión esté iniciada
        
        try {
            // Verificar si el usuario está autenticado
            if (!isset($_SESSION['usuario_id'])) {
                throw new Exception('Sesión expirada. Por favor, inicie sesión nuevamente.');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id_pedido'], $data['ced_cliente'], $data['productos'], $data['total'], $data['cant_producto'])) {
                throw new Exception('Faltan datos requeridos para la actualización');
            }
            
            // Validar que haya al menos un producto
            if (empty($data['productos']) || !is_array($data['productos'])) {
                throw new Exception('Debe incluir al menos un producto en el pedido');
            }
            
            // Validar cada producto
            foreach ($data['productos'] as $index => $producto) {
                if (!isset($producto['cod_producto'], $producto['cantidad'], $producto['precio'])) {
                    throw new Exception('Producto en la posición ' . ($index + 1) . ' no tiene todos los campos requeridos');
                }
                
                // Asegurar que los valores numéricos sean correctos
                $data['productos'][$index]['cantidad'] = (int)$producto['cantidad'];
                $data['productos'][$index]['precio'] = (float)$producto['precio'];
                $data['productos'][$index]['subtotal'] = $data['productos'][$index]['cantidad'] * $data['productos'][$index]['precio'];
            }
            
            // Recalcular el total por si acaso
            $data['total'] = array_reduce($data['productos'], function($sum, $item) {
                return $sum + ($item['precio'] * $item['cantidad']);
            }, 0);
            
            // Actualizar el pedido
            $resultado = $pedido->actualizarPedido(
                $data['id_pedido'],
                $data['ced_cliente'],
                $data['productos'],
                $data['total'],
                $data['cant_producto']
            );

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Pedido actualizado exitosamente',
                    'data' => [
                        'id_pedido' => $data['id_pedido'],
                        'total' => $data['total'],
                        'cant_productos' => count($data['productos'])
                    ]
                ]);
            } else {
                throw new Exception('No se pudo actualizar el pedido. Verifique los datos e intente nuevamente.');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => DEBUG_MODE ? $e->getTraceAsString() : null
            ]);
        }
        break;

    case 'marcarPagado':
        header('Content-Type: application/json');
        if (isset($_POST['id_pedido'])) {
            $resultado = $pedido->marcarComoPagado($_POST['id_pedido']);
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Pedido marcado como pagado' : 'Error al actualizar'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta ID del pedido']);
        }
        break;

    case 'obtenerDetalle':
        header('Content-Type: application/json');
        try {
            if (!isset($_GET['id'])) {
                throw new Exception('Falta el ID del pedido');
            }
            
            $idPedido = $_GET['id'];
            
            if (!is_numeric($idPedido)) {
                throw new Exception('ID de pedido inválido');
            }
            
            $pedidoData = $pedido->obtenerDetalle($idPedido);
            
            if ($pedidoData === null) {
                throw new Exception('No se encontró el pedido con ID: ' . $idPedido);
            }
            
            // Preparar la respuesta
            $response = [
                'success' => true,
                'data' => $pedidoData
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Error al obtener el detalle del pedido: ' . $e->getMessage(),
                'trace' => DEBUG_MODE ? $e->getTraceAsString() : null
            ], JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'eliminar':
        header('Content-Type: application/json');
        if (isset($_POST['id_pedido'])) {
            try {
                $result = $pedido->eliminar($_POST['id_pedido']);
                echo json_encode([
                    'success' => $result,
                    'message' => $result ? 'Pedido eliminado correctamente' : 'No se pudo eliminar el pedido'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al eliminar el pedido: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta el ID del pedido']);
        }
        break;

    case 'listar':
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            // Filtrar por estado si se envía
            $estado = $_GET['estado'] ?? null;
            $pedidos = $pedido->listar($estado);
            echo json_encode(['data' => $pedidos]);
        } else {
            $pedidos = $pedido->listar(0); // Por defecto mostrar solo por pagar
            include 'app/views/pedido/listar.php';
        }
        break;

    case 'obtenerProductos':
        header('Content-Type: application/json');
        $productos = $producto->listar();
        echo json_encode(['data' => $productos]);
        break;

    default:
        include 'app/views/pedido/listar.php';
        break;
}