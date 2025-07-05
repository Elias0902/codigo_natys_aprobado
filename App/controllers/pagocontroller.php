<?php
require_once 'App/Helpers/auth_check.php';
use App\Natys\models\Pago;

$pago = new Pago();

$action = $_REQUEST['action'] ?? 'listar';

switch ($action) {
    case 'formNuevo':
        include 'app/views/pago/formulario.php';
        break;

    case 'formEditar':
        header('Content-Type: application/json');
        if (isset($_GET['id_pago'])) {
            $id = $_GET['id_pago'];
            $datos = $pago->obtenerPago($id);
            
            if ($datos) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Datos del pago cargados',
                    'data' => [
                        'id_pago' => $datos['id_pago'],
                        'banco' => $datos['banco'],
                        'referencia' => $datos['referencia'],
                        'fecha' => $datos['fecha'],
                        'monto' => $datos['monto'],
                        'cod_metodo' => $datos['cod_metodo']
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Pago no encontrado'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Falta el ID del pago'
            ]);
        }
        exit();
        break;

    case 'guardar':
        header('Content-Type: application/json');
        
        try {
            // Validar que todos los campos requeridos estén presentes
            $camposRequeridos = [
                'banco' => 'Banco',
                'referencia' => 'Referencia',
                'fecha_pago' => 'Fecha de pago',
                'monto' => 'Monto',
                'cod_metodo' => 'Método de pago',
                'id_pedido' => 'ID del pedido'
            ];
            
            $errores = [];
            
            // Validar campos requeridos
            foreach ($camposRequeridos as $campo => $nombre) {
                if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
                    $errores[] = $nombre;
                }
            }
            
            if (!empty($errores)) {
                throw new Exception('Faltan campos requeridos: ' . implode(', ', $errores));
            }
            
            // Validar formato de fecha
            $fecha_pago = trim($_POST['fecha_pago']);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_pago)) {
                throw new Exception('El formato de la fecha de pago no es válido. Use YYYY-MM-DD');
            }
            
            // Validar monto
            $monto = (float)$_POST['monto'];
            if ($monto <= 0) {
                throw new Exception('El monto debe ser mayor a cero');
            }
            
            $pdo = (new Pago())->getConnection();
            
            // Iniciar una única transacción
            $pdo->beginTransaction();
            
            try {
                // Verificar que el pedido existe y no está pagado
                $id_pedido = (int)$_POST['id_pedido'];
                $pedidoModel = new \App\Natys\Models\Pedido();
                $pedido = $pedidoModel->obtenerDetalle($id_pedido);
                
                if (!$pedido) {
                    throw new Exception('El pedido especificado no existe');
                }
                
                if ($pedido['pedido']['estado'] == 1) {
                    throw new Exception('Este pedido ya ha sido pagado anteriormente');
                }
                
                // Crear el pago
                $pago = new Pago();
                $pago->banco = trim($_POST['banco']);
                $pago->referencia = trim($_POST['referencia']);
                $pago->fecha = $fecha_pago;
                $pago->monto = $monto;
                $pago->cod_metodo = trim($_POST['cod_metodo']);
                $pago->estado = 1; // Pago activo
                
                // Guardar el pago
                if (!$pago->guardar()) {
                    throw new Exception('Error al guardar el pago');
                }
                
                $id_pago = $pdo->lastInsertId();
                
                // Actualizar el pedido con el ID del pago y marcarlo como pagado (estado = 1)
                $query = "UPDATE pedido SET estado = 1, id_pago = :id_pago WHERE id_pedido = :id_pedido";
                $stmt = $pdo->prepare($query);
                $stmt->bindValue(':id_pago', $id_pago, PDO::PARAM_INT);
                $stmt->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
                
                if (!$stmt->execute()) {
                    $error = $stmt->errorInfo();
                    throw new Exception('Error al actualizar el estado del pedido: ' . ($error[2] ?? 'Error desconocido'));
                }
                
                // Confirmar la transacción
                $pdo->commit();
                
                $response = [
                    'success' => true,
                    'message' => 'Pago del pedido procesado exitosamente',
                    'data' => [
                        'id_pago' => $id_pago,
                        'id_pedido' => $id_pedido,
                        'banco' => $pago->banco,
                        'referencia' => $pago->referencia,
                        'fecha' => $pago->fecha,
                        'monto' => $pago->monto,
                        'cod_metodo' => $pago->cod_metodo,
                        'estado' => 1
                    ]
                ];
                
                echo json_encode($response);
                exit;
                
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $e; // Relanzar la excepción para que sea manejada por el bloque catch externo
            }
            
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            error_log("Error en pago/guardar: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        break;

    case 'actualizar':
        header('Content-Type: application/json');
        if (isset($_POST['id_pago'], $_POST['banco'], $_POST['referencia'], $_POST['fecha'], $_POST['monto'], $_POST['cod_metodo'])) {
            $pago = new Pago();
            $pago->id_pago = $_POST['id_pago'];
            $pago->banco = $_POST['banco'];
            $pago->referencia = $_POST['referencia'];
            $pago->fecha = $_POST['fecha'];
            $pago->monto = $_POST['monto'];
            $pago->cod_metodo = $_POST['cod_metodo'];

            $resultado = $pago->actualizar();
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Pago actualizado exitosamente',
                    'data' => [
                        'id_pago' => $pago->id_pago,
                        'banco' => $pago->banco,
                        'referencia' => $pago->referencia,
                        'fecha' => $pago->fecha,
                        'monto' => $pago->monto,
                        'cod_metodo' => $pago->cod_metodo
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el pago']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Faltan datos para actualizar']);
        }
        break;

    case 'eliminar':
        header('Content-Type: application/json');
        if (isset($_POST['id_pago'])) {
            $pago->id_pago = $_POST['id_pago'];
            $resultado = $pago->eliminar();
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Pago eliminado exitosamente' : 'Error al eliminar el pago'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta el ID del pago']);
        }
        break;

    case 'restaurar':
        header('Content-Type: application/json');
        if (isset($_POST['id_pago'])) {
            $pago->id_pago = $_POST['id_pago'];
            $resultado = $pago->restaurar();
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Pago restaurado exitosamente' : 'Error al restaurar el pago'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta el ID del pago']);
        }
        break;

    case 'listarEliminados':
        header('Content-Type: application/json');
        $pagos = $pago->listarEliminados();
        echo json_encode(['data' => $pagos]);
        exit;
        break;

    case 'listarMetodos':
        header('Content-Type: application/json');
        $metodos = $pago->obtenerMetodosPagoActivos();
        echo json_encode([
            'success' => true,
            'message' => 'Métodos de pago cargados',
            'data' => $metodos
        ]);
        exit();
        break;
        
    case 'procesarPagoPedido':
        header('Content-Type: application/json');
        $pdo = null;
        
        try {
            // Validar datos requeridos
            $camposRequeridos = [
                'id_pedido' => 'ID del pedido',
                'monto' => 'Monto del pago',
                'fecha' => 'Fecha del pago',
                'cod_metodo' => 'Método de pago',
                'referencia' => 'Número de referencia'
            ];
            
            $errores = [];
            foreach ($camposRequeridos as $campo => $nombre) {
                if (empty($_POST[$campo])) {
                    $errores[] = $nombre;
                }
            }
            
            if (!empty($errores)) {
                throw new Exception('Faltan campos requeridos: ' . implode(', ', $errores));
            }
            
            // Obtener el pedido
            $pedidoModel = new \App\Natys\Models\Pedido();
            $pedido = $pedidoModel->obtenerDetalle($_POST['id_pedido']);
            
            if (!$pedido || !isset($pedido['pedido'])) {
                throw new Exception('El pedido especificado no existe');
            }
            
            $pedido = $pedido['pedido'];
            
            // Verificar si el pedido ya está pagado
            if ($pedido['estado'] == 1) {
                throw new Exception('Este pedido ya ha sido pagado anteriormente');
            }
            
            // Validar monto
            $monto = (float)$_POST['monto'];
            if ($monto <= 0) {
                throw new Exception('El monto debe ser mayor a cero');
            }
            
            // Crear el pago
            $pago = new Pago();
            $pago->banco = $_POST['banco'] ?? null;
            $pago->referencia = trim($_POST['referencia']);
            $pago->fecha = $_POST['fecha'];
            $pago->monto = $monto;
            $pago->cod_metodo = $_POST['cod_metodo'];
            $pago->estado = 1; // Pago activo
            
            // Obtener conexión PDO
            $pdo = $pago->getConnection();
            
            // Iniciar transacción
            $pdo->beginTransaction();
            
            try {
                // 1. Guardar el pago
                if (!$pago->guardar()) {
                    throw new Exception('Error al guardar el registro del pago');
                }
                
                $id_pago = $pdo->lastInsertId();
                
                // 2. Actualizar el pedido con el ID del pago y cambiar su estado a pagado
                $query = "UPDATE pedido SET estado = 1, id_pago = :id_pago WHERE id_pedido = :id_pedido AND estado = 0";
                $stmt = $pdo->prepare($query);
                $stmt->bindValue(':id_pago', $id_pago, PDO::PARAM_INT);
                $stmt->bindValue(':id_pedido', $_POST['id_pedido'], PDO::PARAM_INT);
                
                if (!$stmt->execute()) {
                    throw new Exception('Error al actualizar el estado del pedido');
                }
                
                // Verificar si se actualizó alguna fila
                if ($stmt->rowCount() === 0) {
                    throw new Exception('No se pudo actualizar el pedido. Puede que ya haya sido pagado o no exista.');
                }
                
                // Todo salió bien, confirmar la transacción
                $pdo->commit();
                
                // Obtener los datos actualizados del pedido
                $pedidoActualizado = $pedidoModel->obtenerDetalle($_POST['id_pedido']);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Pago procesado exitosamente',
                    'data' => [
                        'id_pago' => $id_pago,
                        'id_pedido' => $_POST['id_pedido'],
                        'pedido' => $pedidoActualizado
                    ]
                ]);
                
            } catch (Exception $e) {
                // Algo salió mal, deshacer la transacción
                if ($pdo && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $e;
            }
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        break;

    case 'listar':
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            $pagos = $pago->listar();
            echo json_encode(['data' => $pagos]);
        } else {
            $pagos = $pago->listar();
            // Obtener pedidos pendientes de pago
            $pedidoModel = new \App\Natys\Models\Pedido();
            $pedidosPendientes = $pedidoModel->listar(0); // Estado 0 = pendiente de pago
            include 'app/views/pago/listar.php';
        }
        break;
}