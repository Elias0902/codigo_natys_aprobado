<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DEBUG_MODE', true);

require_once 'App/Helpers/auth_check.php';
use App\Natys\Models\Pedido;
use App\Natys\Models\Cliente;
use App\Natys\Models\Producto;
use App\Natys\Helpers\ReportePDF;

$pedido = new Pedido();
$cliente = new Cliente();
$producto = new Producto();

$action = $_REQUEST['action'] ?? 'listar';

$clientesActivos = $cliente->listar();
$productosActivos = $producto->listarProductos();

$clientes = $clientesActivos;
$productos = $productosActivos;

switch ($action) {
    case 'formNuevo':
        include 'app/views/pedido/formulario.php';
        break;

    case 'verDetalle':
        header('Content-Type: application/json');
        try {
            if (!isset($_GET['id_pedido'])) {
                throw new Exception('Falta el ID del pedido');
            }
            
            $idPedido = $_GET['id_pedido'];
            $pedidoData = $pedido->obtenerDetalle($idPedido);
            
            if (!$pedidoData) {
                throw new Exception('Pedido no encontrado');
            }
            
            $response = [
                'success' => true,
                'data' => [
                    'pedido' => [
                        'id_pedido' => $pedidoData['pedido']['id_pedido'],
                        'fecha' => $pedidoData['pedido']['fecha_creacion_formatted'],
                        'total' => $pedidoData['pedido']['total'],
                        'estado' => $pedidoData['pedido']['estado']
                    ],
                    'cliente' => [
                        'ced_cliente' => $pedidoData['cliente']['cedula'],
                        'nomcliente' => $pedidoData['cliente']['nombre_completo'],
                        'telefono' => $pedidoData['cliente']['telefono'],
                        'direccion' => $pedidoData['cliente']['direccion']
                    ],
                    'productos' => array_map(function($item) {
                        return [
                            'nombre' => $item['nombre_producto'],
                            'precio' => $item['precio_unitario'],
                            'cantidad' => $item['cantidad'],
                            'subtotal' => $item['subtotal']
                        ];
                    }, $pedidoData['detalles'])
                ]
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Error al cargar el pedido: ' . $e->getMessage()
            ]);
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

    case 'editar':
        header('Content-Type: application/json');

        try {

            if (!isset($_SESSION['usuario'])) {
                throw new Exception('Sesión expirada. Por favor, inicie sesión nuevamente.');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id_pedido'], $data['ced_cliente'], $data['productos'], $data['total'], $data['cant_producto'])) {
                throw new Exception('Faltan datos requeridos para la actualización');
            }
            
            
            if (empty($data['productos']) || !is_array($data['productos'])) {
                throw new Exception('Debe incluir al menos un producto en el pedido');
            }
            
            
            foreach ($data['productos'] as $index => $producto) {
                if (!isset($producto['cod_producto'], $producto['cantidad'], $producto['precio'])) {
                    throw new Exception('Producto en la posición ' . ($index + 1) . ' no tiene todos los campos requeridos');
                }
                
                
                $data['productos'][$index]['cantidad'] = (int)$producto['cantidad'];
                $data['productos'][$index]['precio'] = (float)$producto['precio'];
                $data['productos'][$index]['subtotal'] = $data['productos'][$index]['cantidad'] * $data['productos'][$index]['precio'];
            }
            
            
            $data['total'] = array_reduce($data['productos'], function($sum, $item) {
                return $sum + ($item['precio'] * $item['cantidad']);
            }, 0);
            
            
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
                throw new Exception('No se pudo editar el pedido. Verifique los datos e intente nuevamente.');
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



    // Eliminar pedido aprobado
    case 'eliminar':
        header('Content-Type: application/json');
        try {
            if (!isset($_POST['id_pedido'])) {
                throw new Exception('Falta el ID del pedido');
            }

            $idPedido = $_POST['id_pedido'];
            $resultado = $pedido->eliminarPedido($idPedido);

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Pedido aprobado eliminado exitosamente'
                ]);
            } else {
                throw new Exception('No se pudo eliminar el pedido aprobado');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        break;

    case 'marcarPagado':
        header('Content-Type: application/json');
        if (isset($_POST['id_pedido'])) {
            try {
                $resultado = $pedido->marcarComoPagado($_POST['id_pedido']);
                echo json_encode([
                    'success' => $resultado,
                    'message' => $resultado ? 'Pedido marcado como pagado' : 'Error al marcar como pagado'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
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
            
            
            $response = [
                'success' => true,
                'data' => $pedidoData
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Error al obtener el detalle del pedido: ' . $e->getMessage()
            ]);
        }
        break;

    case 'listarPendientes':
        header('Content-Type: application/json');
        try {
            $pedidos = $pedido->listar(0); 
            
            
            $response = [
                'success' => true,
                'data' => $pedidos
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al listar los pedidos pendientes: ' . $e->getMessage()
            ]);
        }
        break;

    case 'listar':
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                  || (isset($_GET['ajax']) && $_GET['ajax'] == '1');
        if ($isAjax) {
            header('Content-Type: application/json');

            $estado = $_GET['estado'] ?? null;
            $pedidos = $pedido->listar($estado);
            echo json_encode(['data' => $pedidos], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            $pedidos = $pedido->listar();
            include 'app/views/pedido/pedidoView.php';
        }
        break;

    case 'obtenerProductos':
        header('Content-Type: application/json');
        $productos = $producto->listarProductos();


        foreach ($productos as &$prod) {
            $stock = $producto->obtenerStockProducto($prod['cod_producto']);
            $prod['stock_disponible'] = $stock;
        }

        echo json_encode(['data' => $productos]);
        break;

    case 'eliminar':
        header('Content-Type: application/json');
        try {
            if (!isset($_POST['id_pedido'])) {
                throw new Exception('Falta el ID del pedido');
            }
            $idPedido = $_POST['id_pedido'];
            
            // Verificar el estado del pedido para determinar qué acción tomar
            $pedidoData = $pedido->obtener($idPedido);
            
            if (!$pedidoData) {
                throw new Exception('Pedido no encontrado');
            }
            
            if ($pedidoData['estado'] == 1) {
                // Pedido aprobado - eliminar directamente
                $resultado = $pedido->eliminarPedido($idPedido);
                $mensaje = 'Pedido aprobado eliminado exitosamente';
            } else {
                throw new Exception('No se puede realizar la acción sobre este pedido');
            }
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => $mensaje
                ]);
            } else {
                throw new Exception('No se pudo completar la acción');
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        break;

        
    case 'reporte_lista':
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            $_SESSION['error_login'] = 'Debes iniciar sesión para acceder a esta página';
            header('Location: index.php?url=user&type=login');
            exit;
        }

        if (!in_array($_SESSION['usuario']['rol'], ['admin', 'superadmin'])) {
            $_SESSION['error'] = 'No tienes permisos para generar reportes';
            header('Location: index.php?url=pedido&action=listar');
            exit;
        }
        
        // Asegurarse de que las fechas tengan el formato correcto
        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin = $_GET['fecha_fin'] ?? null;
        $estado = $_GET['estado'] ?? 'todos';
        
        // Si no hay fechas, obtener los últimos 30 días
        if (!$fechaInicio || !$fechaFin) {
            $fechaFin = date('Y-m-d');
            $fechaInicio = date('Y-m-d', strtotime('-30 days'));
        }

        // Convertir el estado a entero si no es 'todos'
        $estado = $estado !== 'todos' ? (int)$estado : null;
        
        $esReportePorFechas = !empty($fechaInicio) && !empty($fechaFin);
        
        $pdf = new ReportePDF();
        
        if ($esReportePorFechas) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
                $titulo = 'LISTADO GENERAL DE PEDIDOS';
                $subtitulo = 'Todos los pedidos registrados';
                $pedidos = $pedido->listar($estado);
            } else {
                if (strtotime($fechaInicio) > strtotime($fechaFin)) {
                    $temp = $fechaInicio;
                    $fechaInicio = $fechaFin;
                    $fechaFin = $temp;
                }
                
                $titulo = 'LISTADO DE PEDIDOS';
                $subtitulo = "Del $fechaInicio al $fechaFin";
                
                if ($estado === 0) {
                    $titulo = 'PEDIDOS PENDIENTES';
                    $subtitulo = "Pendientes del $fechaInicio al $fechaFin";
                } elseif ($estado === 1) {
                    $titulo = 'PEDIDOS COMPLETADOS';
                    $subtitulo = "Completados del $fechaInicio al $fechaFin";
                }
                
                try {
                    if (method_exists($pedido, 'listarPorFechas')) {
                        $pedidos = $pedido->listarPorFechas($fechaInicio, $fechaFin, $estado);
                    } else {
                        // Si el método no existe, filtrar manualmente
                        $todosPedidos = $pedido->listar($estado);
                        $pedidos = array_filter($todosPedidos, function($pedidoItem) use ($fechaInicio, $fechaFin) {
                            $fechaPedido = date('Y-m-d', strtotime($pedidoItem['fecha']));
                            return $fechaPedido >= $fechaInicio && $fechaPedido <= $fechaFin;
                        });
                        $pedidos = array_values($pedidos); // Reindexar el array
                    }
                } catch (Exception $e) {
                    error_log("Error al obtener pedidos por fechas: " . $e->getMessage());
                    $todosPedidos = $pedido->listar($estado);
                    $pedidos = [];
                    
                    foreach ($todosPedidos as $pedidoItem) {
                        $fechaPedido = $pedidoItem['fecha'];
                        if ($fechaPedido >= $fechaInicio && $fechaPedido <= $fechaFin) {
                            $pedidos[] = $pedidoItem;
                        }
                    }
                }
            }
        } else {
            $titulo = 'LISTADO GENERAL DE PEDIDOS';
            $subtitulo = 'Todos los pedidos registrados';
            $pedidos = $pedido->listar($estado);
            
            if ($estado === 0) {
                $titulo = 'PEDIDOS PENDIENTES';
                $subtitulo = 'Todos los pedidos pendientes';
            } elseif ($estado === 1) {
                $titulo = 'PEDIDOS COMPLETADOS';
                $subtitulo = 'Todos los pedidos completados';
            }
        }
        
        $pdf->setTitulo($titulo);
        $pdf->setSubtitulo($subtitulo);
        $pdf->AddPage();
        
        $totalPedidos = count($pedidos);
        $montoTotal = 0;
        $pendientes = 0;
        $completados = 0;
        
        foreach ($pedidos as $pedidoItem) {
            $montoTotal += $pedidoItem['total'];
            if ($pedidoItem['estado'] == 0) $pendientes++;
            if ($pedidoItem['estado'] == 1) $completados++;
        }
        
        $resumen = [
            'Total de Pedidos' => $totalPedidos,
            'Pedidos Pendientes' => $pendientes,
            'Pedidos Completados' => $completados,
            'Monto Total' => '$' . number_format($montoTotal, 2)
        ];
        
        if ($esReportePorFechas && !empty($fechaInicio) && !empty($fechaFin)) {
            $resumen = array_merge(['Período' => "$fechaInicio al $fechaFin"], $resumen);
        }
        
        $pdf->agregarResumen($resumen);
        
        if (count($pedidos) > 0) {
            $headers = ['ID', 'Fecha', 'Cliente', 'Productos', 'Total', 'Estado'];
            $widths = [15, 25, 60, 25, 30, 35];
            $data = [];
            
            foreach ($pedidos as $pedidoItem) {
                $estadoTexto = $pedidoItem['estado'] == 0 ? 'Pendiente' : 'Completado';
                $data[] = [
                    $pedidoItem['id_pedido'],
                    date('d/m/Y', strtotime($pedidoItem['fecha'])),
                    $pedidoItem['nomcliente'],
                    $pedidoItem['cant_producto'],
                    '$' . number_format($pedidoItem['total'], 2),
                    $estadoTexto
                ];
            }
            
            $pdf->crearTabla($headers, $data, $widths);
        } else {
            $pdf->SetFont('Arial', 'I', 12);
            $pdf->Cell(0, 10, 'No hay pedidos en el período seleccionado', 0, 1, 'C');
        }
        
        $nombreArchivo = $esReportePorFechas ? 
            'Pedidos_Por_Fechas_' . date('Y-m-d') . '.pdf' : 
            'Pedidos_Generales_' . date('Y-m-d') . '.pdf';
        
        $pdf->Output('I', $nombreArchivo);
        break;
        
    case 'reporte_pendientes':
        if (!isset($_SESSION['usuario'])) {
            $_SESSION['error_login'] = 'Debes iniciar sesión para acceder a esta página';
            header('Location: index.php?url=user&type=login');
            exit;
        }

        if (!in_array($_SESSION['usuario']['rol'], ['admin', 'superadmin'])) {
            $_SESSION['error'] = 'No tienes permisos para generar reportes';
            header('Location: index.php?url=pedido&action=listar');
            exit;
        }

        $pdf = new ReportePDF();
        $pdf->setTitulo('PEDIDOS PENDIENTES');
        $pdf->setSubtitulo('Pedidos que requieren atención');
        $pdf->AddPage();
        
        $pedidos = $pedido->listar(0);
        
        $montoTotal = 0;
        foreach ($pedidos as $pedidoItem) {
            $montoTotal += $pedidoItem['total'];
        }
        
        $pdf->agregarResumen([
            'Pedidos Pendientes' => count($pedidos),
            'Monto Total Pendiente' => '$' . number_format($montoTotal, 2)
        ]);
        
        if (count($pedidos) > 0) {
            $headers = ['ID', 'Fecha', 'Cliente', 'Cédula', 'Total'];
            $widths = [20, 30, 70, 30, 40];
            $data = [];
            
            foreach ($pedidos as $pedidoItem) {
                $data[] = [
                    $pedidoItem['id_pedido'],
                    date('d/m/Y', strtotime($pedidoItem['fecha'])),
                    $pedidoItem['nomcliente'],
                    $pedidoItem['ced_cliente'],
                    '$' . number_format($pedidoItem['total'], 2)
                ];
            }
            
            $pdf->crearTabla($headers, $data, $widths);
        } else {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 10, 'No hay pedidos pendientes', 0, 1, 'C');
        }
        
        $pdf->Output('I', 'Pedidos_Pendientes_' . date('Y-m-d') . '.pdf');
        break;
        
    case 'reporte_completados':
        if (!isset($_SESSION['usuario'])) {
            $_SESSION['error_login'] = 'Debes iniciar sesión para acceder a esta página';
            header('Location: index.php?url=user&type=login');
            exit;
        }

        if (!in_array($_SESSION['usuario']['rol'], ['admin', 'superadmin'])) {
            $_SESSION['error'] = 'No tienes permisos para generar reportes';
            header('Location: index.php?url=pedido&action=listar');
            exit;
        }

        $pdf = new ReportePDF();
        $pdf->setTitulo('PEDIDOS COMPLETADOS');
        $pdf->setSubtitulo('Historial de pedidos finalizados');
        $pdf->AddPage();
        
        $pedidos = $pedido->listar(1);
        
        $montoTotal = 0;
        foreach ($pedidos as $pedidoItem) {
            $montoTotal += $pedidoItem['total'];
        }
        
        $pdf->agregarResumen([
            'Pedidos Completados' => count($pedidos),
            'Monto Total' => '$' . number_format($montoTotal, 2)
        ]);
        
        if (count($pedidos) > 0) {
            $headers = ['ID', 'Fecha', 'Cliente', 'Productos', 'Total'];
            $widths = [20, 30, 70, 30, 40];
            $data = [];
            
            foreach ($pedidos as $pedidoItem) {
                $data[] = [
                    $pedidoItem['id_pedido'],
                    date('d/m/Y', strtotime($pedidoItem['fecha'])),
                    $pedidoItem['nomcliente'],
                    $pedidoItem['cant_producto'],
                    '$' . number_format($pedidoItem['total'], 2)
                ];
            }
            
            $pdf->crearTabla($headers, $data, $widths);
        } else {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 10, 'No hay pedidos completados', 0, 1, 'C');
        }
        
        $pdf->Output('I', 'Pedidos_Completados_' . date('Y-m-d') . '.pdf');
        break;
        
    case 'reporte_detalle':
        if (!isset($_SESSION['usuario'])) {
            $_SESSION['error_login'] = 'Debes iniciar sesión para acceder a esta página';
            header('Location: index.php?url=user&type=login');
            exit;
        }

        if (!in_array($_SESSION['usuario']['rol'], ['admin', 'superadmin'])) {
            $_SESSION['error'] = 'No tienes permisos para generar reportes';
            header('Location: index.php?url=pedido&action=listar');
            exit;
        }

        if (!isset($_GET['id'])) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'ID de pedido requerido']);
            exit;
        }
        
        $id_pedido = $_GET['id'];
        $detalle = $pedido->obtenerDetalle($id_pedido);
        
        if (!$detalle) {
            header('HTTP/1.1 404 Not Found');
            echo json_encode(['error' => 'Pedido no encontrado']);
            exit;
        }
        
        $pdf = new ReportePDF();
        $pdf->setTitulo('DETALLE DE PEDIDO #' . $id_pedido);
        $pdf->setSubtitulo('Información completa del pedido');
        $pdf->AddPage();
        
        $pdf->agregarSeccion('Información del Pedido');
        $pdf->agregarResumen([
            'Número de Pedido' => $id_pedido,
            'Fecha' => date('d/m/Y', strtotime($detalle['pedido']['fecha_creacion'])),
            'Cliente' => $detalle['cliente']['nombre_completo'],
            'Cédula' => $detalle['cliente']['cedula'],
            'Estado' => $detalle['pedido']['estado_texto']
        ]);
        
        $pdf->agregarSeccion('Productos del Pedido');
        
        $headers = ['Producto', 'Precio Unit.', 'Cantidad', 'Subtotal'];
        $widths = [80, 30, 30, 40];
        $data = [];
        
        foreach ($detalle['detalles'] as $productoItem) {
            $data[] = [
                $productoItem['nombre_producto'],
                '$' . number_format($productoItem['precio_unitario'], 2),
                $productoItem['cantidad'],
                '$' . number_format($productoItem['subtotal'], 2)
            ];
        }
        
        $pdf->crearTabla($headers, $data, $widths);
        
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(140, 10, 'TOTAL:', 0, 0, 'R');
        $pdf->Cell(40, 10, '$' . number_format($detalle['pedido']['total'], 2), 0, 1, 'R');
        
        $pdf->Output('I', 'Detalle_Pedido_' . $id_pedido . '.pdf');
        break;

    default:
        include 'app/views/pedido/pedidoView.php';
        break;
}