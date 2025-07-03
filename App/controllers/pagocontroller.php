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
        if (isset($_POST['banco'], $_POST['referencia'], $_POST['fecha'], $_POST['monto'], $_POST['cod_metodo'])) {
            $pago = new Pago();
            $pago->banco = $_POST['banco'];
            $pago->referencia = $_POST['referencia'];
            $pago->fecha = $_POST['fecha'];
            $pago->monto = $_POST['monto'];
            $pago->cod_metodo = $_POST['cod_metodo'];

            if ($pago->guardar()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Pago guardado exitosamente',
                    'data' => [
                        'banco' => $pago->banco,
                        'referencia' => $pago->referencia,
                        'fecha' => $pago->fecha,
                        'monto' => $pago->monto,
                        'cod_metodo' => $pago->cod_metodo,
                        'estado' => 1
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar el pago']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
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

    case 'listar':
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            $pagos = $pago->listar();
            echo json_encode(['data' => $pagos]);
        } else {
            $pagos = $pago->listar();
            include 'app/views/pago/listar.php';
        }
        break;
}