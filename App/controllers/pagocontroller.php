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
                    'data' => $datos
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
            // Validar campos requeridos
            $camposRequeridos = [
                'banco' => 'Banco',
                'referencia' => 'Referencia',
                'fecha_pago' => 'Fecha de pago',
                'monto' => 'Monto',
                'cod_metodo' => 'Método de pago',
                'id_pedido' => 'ID del pedido'
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
            
            // Validaciones adicionales
            $fecha_pago = trim($_POST['fecha_pago']);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_pago)) {
                throw new Exception('Formato de fecha inválido. Use YYYY-MM-DD');
            }
            
            $monto = (float)$_POST['monto'];
            if ($monto <= 0) {
                throw new Exception('El monto debe ser mayor a cero');
            }
            
            $id_pedido = (int)$_POST['id_pedido'];
            $cod_metodo = trim($_POST['cod_metodo']);
            
            // Obtener conexión PDO
            $pdo = (new Pago())->getConnection();
            $pdo->beginTransaction();
            
            try {
                // Verificar que el pedido existe y no está pagado
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
                $pago->cod_metodo = $cod_metodo;
                $pago->estado = 1;
                
                // Guardar el pago
                $id_pago = $pago->guardar();
                if (!$id_pago) {
                    throw new Exception('Error al guardar el pago');
                }
                
                // Actualizar el pedido
                $query = "UPDATE pedido SET estado = 1, id_pago = :id_pago 
                          WHERE id_pedido = :id_pedido AND estado = 0";
                $stmt = $pdo->prepare($query);
                $stmt->bindValue(':id_pago', $id_pago, PDO::PARAM_INT);
                $stmt->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
                
                if (!$stmt->execute()) {
                    throw new Exception('Error al actualizar el pedido');
                }
                
                if ($stmt->rowCount() === 0) {
                    throw new Exception('No se pudo actualizar el pedido. Verifique su estado.');
                }
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Pago registrado correctamente',
                    'data' => [
                        'id_pago' => $id_pago,
                        'id_pedido' => $id_pedido
                    ]
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
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

    case 'actualizar':
        header('Content-Type: application/json');
        if (isset($_POST['id_pago'], $_POST['banco'], $_POST['referencia'], $_POST['fecha'], $_POST['monto'], $_POST['cod_metodo'])) {
            try {
                $pago = new Pago();
                $pago->id_pago = $_POST['id_pago'];
                $pago->banco = $_POST['banco'];
                $pago->referencia = $_POST['referencia'];
                $pago->fecha = $_POST['fecha'];
                $pago->monto = $_POST['monto'];
                $pago->cod_metodo = $_POST['cod_metodo'];

                if ($pago->actualizar()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Pago actualizado exitosamente'
                    ]);
                } else {
                    throw new Exception('Error al actualizar el pago');
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Faltan datos para actualizar'
            ]);
        }
        break;

    case 'eliminar':
        header('Content-Type: application/json');
        if (isset($_POST['id_pago'])) {
            try {
                $pago->id_pago = $_POST['id_pago'];
                if ($pago->eliminar()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Pago eliminado exitosamente'
                    ]);
                } else {
                    throw new Exception('Error al eliminar el pago');
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Falta el ID del pago'
            ]);
        }
        break;

    case 'restaurar':
        header('Content-Type: application/json');
        if (isset($_POST['id_pago'])) {
            try {
                $pago->id_pago = $_POST['id_pago'];
                if ($pago->restaurar()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Pago restaurado exitosamente'
                    ]);
                } else {
                    throw new Exception('Error al restaurar el pago');
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Falta el ID del pago'
            ]);
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
        
    case 'listar':
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            $pagos = $pago->listar();
            echo json_encode(['data' => $pagos]);
        } else {
            $pagos = $pago->listar();
            $pedidoModel = new \App\Natys\Models\Pedido();
            $pedidosPendientes = $pedidoModel->listar(0);
            include 'app/views/pago/listar.php';
        }
        break;
}