<?php
// Habilitar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cargar el autoloader de Composer si existe
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

// Cargar archivos necesarios
require_once __DIR__ . '/../config/connect/Conexion.php';
require_once __DIR__ . '/../Helpers/auth_check.php';

// Importar las clases necesarias
use App\Natys\Models\Pago;
use App\Natys\Models\Pedido;
use App\Natys\Helpers\ReportePDF;
use App\Natys\Config\Connect\Conexion;

// Función para enviar respuesta JSON estandarizada
function sendJsonResponse($success, $message = '', $data = null, $statusCode = 200) {
    // Limpiar cualquier salida previa
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Establecer las cabeceras apropiadas
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    
    // Asegurarse de que los datos sean compatibles con JSON
    $response = [
        'success' => (bool)$success,
        'message' => (string)$message,
        'data' => $data
    ];
    
    // Convertir a JSON y manejar errores
    $jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    if ($jsonResponse === false) {
        // Si hay un error al codificar, enviar un mensaje de error genérico
        $errorResponse = [
            'success' => false,
            'message' => 'Error al procesar la respuesta',
            'data' => null
        ];
        echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        echo $jsonResponse;
    }
    
    exit();
}
// Asegurarse de que no haya salida antes de las cabeceras
if (ob_get_level()) {
    ob_clean();
}

// Iniciar el buffer de salida
ob_start();

// Manejo de errores personalizado
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Limpiar cualquier salida previa
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Registrar el error para depuración
    error_log("Error [$errno] $errstr en $errfile en la línea $errline");
    
    // Enviar respuesta de error en formato JSON
    sendJsonResponse(false, 'Error interno del servidor. Por favor, intente nuevamente.', null, 500);
});

// Manejar excepciones no capturadas
set_exception_handler(function($e) {
    // Limpiar cualquier salida previa
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Registrar la excepción
    error_log("Excepción no capturada: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
    
    // Enviar respuesta de error en formato JSON
    sendJsonResponse(false, 'Se produjo un error inesperado. Por favor, contacte al administrador.', null, 500);
});

// Inicializar la clase Pago
try {
    $pago = new Pago();
    $action = $_GET['action'] ?? 'listar'; // Usar $_GET en lugar de $_REQUEST para mayor seguridad

    switch ($action) {
    // ... (otros cases como formEditar, listar, etc. se mantienen igual)

    case 'guardar':
        if (ob_get_level()) ob_clean();

        try {
            $id_pedido = filter_input(INPUT_POST, 'id_pedido', FILTER_VALIDATE_INT);
            $monto = filter_input(INPUT_POST, 'monto', FILTER_VALIDATE_FLOAT);
            $cod_metodo = strtoupper(trim($_POST['cod_metodo'] ?? ''));
            // Si no se envía fecha_pago, usar la fecha actual
            $fecha_pago = trim($_POST['fecha_pago'] ?? '');
            if (empty($fecha_pago)) {
                $fecha_pago = date('Y-m-d');
            }

            // Validación de campos básicos
            if (!$id_pedido || !$monto || empty($cod_metodo) || empty($fecha_pago)) {
                sendJsonResponse(false, 'Faltan campos requeridos.', null, 400);
            }
            if ($monto <= 0) {
                sendJsonResponse(false, 'El monto debe ser mayor a cero.', null, 400);
            }

            // Lógica para campos que dependen del método de pago
            $banco = 'N/A';
            // Usar 'Efectivo' en lugar de 'N/A' por legibilidad cuando corresponda
            $referencia = 'Efectivo';
            $esEfectivo = in_array($cod_metodo, ['EFECTIVO', 'ZELLE']);

            if (!$esEfectivo) {
                $banco = trim($_POST['banco'] ?? '');
                $referencia = trim($_POST['referencia'] ?? '');
                if (empty($banco) || empty($referencia)) {
                    sendJsonResponse(false, 'Para este método de pago, el banco y la referencia son obligatorios.', null, 400);
                }
            }

            // Iniciar transacción para asegurar la integridad de los datos
            $pdo = (new Conexion())->getConnection();
            $pdo->beginTransaction();
            
            try {
                // 1. Verificar el estado del pedido
                $pedidoModel = new Pedido();
                $pedido = $pedidoModel->obtenerDetalle($id_pedido);
                
                if (!$pedido) {
                    throw new Exception('El pedido no existe.');
                }
                if ($pedido['pedido']['estado'] == 1) { // 1 = Pagado
                    throw new Exception('Este pedido ya fue pagado anteriormente.');
                }
                
                // 1.1 Validar que la referencia no exista (solo para métodos que no son efectivo)
                if (!$esEfectivo && !empty($referencia)) {
                    $pagoModel = new Pago();
                    if ($pagoModel->existeReferencia($referencia)) {
                        throw new Exception('El número de referencia ya ha sido utilizado en otro pago. Por favor, verifique e intente nuevamente.');
                    }
                }
                
                // 2. Crear y guardar el pago usando los setters
                $pago = new Pago();
                $pago->setBanco($banco);
                $pago->setReferencia($referencia);
                $pago->setFecha($fecha_pago);
                $pago->setMonto($monto);
                $pago->setCodMetodo($cod_metodo);
                $pago->setEstado(1); // 1 = Activo

                $id_pago = $pago->guardar();
                if (!$id_pago) {
                    throw new Exception('Error al intentar guardar el registro del pago.');
                }
                
                // 3. Actualizar el estado del pedido y asociar el ID del pago
                $queryUpdatePedido = "UPDATE pedido SET estado = 1, id_pago = :id_pago WHERE id_pedido = :id_pedido";
                $stmtUpdate = $pdo->prepare($queryUpdatePedido);
                $stmtUpdate->bindParam(':id_pago', $id_pago, PDO::PARAM_INT);
                $stmtUpdate->bindParam(':id_pedido', $id_pedido, PDO::PARAM_INT);
                
                // ¡¡ESTA LÍNEA FALTABA!! Ejecutar la actualización
                if (!$stmtUpdate->execute()) {
                    throw new Exception('Error al actualizar el estado del pedido.');
                }

                // 4. Si todo salió bien, confirmar la transacción
                $pdo->commit();
                
                sendJsonResponse(true, 'Pago registrado y pedido actualizado exitosamente.', ['id_pago' => $id_pago]);

            } catch (Exception $e) {
                // Si algo falla, revertir todos los cambios
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('Error en transacción de pago: ' . $e->getMessage());
                sendJsonResponse(false, 'Error en la transacción: ' . $e->getMessage(), ['error' => $e->getMessage()], 500);
            }
            
        } catch (Exception $e) {
            sendJsonResponse(false, $e->getMessage(), null, 400);
        }
        break;

case 'actualizar':
    header('Content-Type: application/json');
    try {
        $id_pago = filter_input(INPUT_POST, 'id_pago', FILTER_VALIDATE_INT);
        $cod_metodo = strtoupper(trim($_POST['cod_metodo'] ?? ''));
        
        // Validación robusta
        if (!$id_pago || empty($_POST['fecha']) || empty($_POST['monto']) || empty($cod_metodo)) {
            throw new Exception('Faltan datos para actualizar.');
        }
        
        // Determinar banco y referencia según el método
        $esMetodoSimple = in_array($cod_metodo, ['EFECTIVO', 'ZELLE']);
        $banco = $esMetodoSimple ? 'N/A' : trim($_POST['banco'] ?? '');
        $referencia = $esMetodoSimple ? 'Efectivo' : trim($_POST['referencia'] ?? '');

        // Validar campos para métodos que no son efectivo/Zelle
        if (!$esMetodoSimple && (empty($banco) || empty($referencia))) {
            throw new Exception('Banco y referencia son requeridos para este método de pago.');
        }
        
        // Validar monto
        $monto = floatval($_POST['monto']);
        if ($monto <= 0) {
            throw new Exception('El monto debe ser mayor a cero.');
        }
        
        // Crear el objeto y usar los setters para asignar valores
        $pago = new Pago();
        $pago->setIdPago($id_pago);
        $pago->setBanco($banco);
        $pago->setReferencia($referencia);
        $pago->setFecha($_POST['fecha']);
        $pago->setMonto($monto);
        $pago->setCodMetodo($cod_metodo);

        if ($pago->actualizar()) {
            sendJsonResponse(true, 'Pago actualizado exitosamente.');
        } else {
            throw new Exception('No se pudo actualizar el pago en la base de datos.');
        }
    } catch (Exception $e) {
        error_log("Error al actualizar pago: " . $e->getMessage());
        sendJsonResponse(false, $e->getMessage(), null, 500);
    }
    break;

case 'formEditar':
    header('Content-Type: application/json');
    try {
        if (!isset($_GET['id_pago'])) {
            throw new Exception('Falta el ID del pago');
        }
        
        $id_pago = $_GET['id_pago'];
        $pagoData = $pago->obtenerPago($id_pago);
        
        if (!$pagoData) {
            throw new Exception('Pago no encontrado');
        }
        
        sendJsonResponse(true, 'Datos del pago cargados', $pagoData);
        
    } catch (Exception $e) {
        sendJsonResponse(false, $e->getMessage(), null, 500);
    }
    break;

    case 'eliminar':
        if (ob_get_level()) ob_clean();

        try {
            $id_pago = filter_input(INPUT_POST, 'id_pago', FILTER_VALIDATE_INT);

            if (!$id_pago) {
                sendJsonResponse(false, 'ID de pago inválido.', null, 400);
            }

            // Usar el setter para asignar el ID
            $pago->setIdPago($id_pago);

            if ($pago->eliminar()) {
                sendJsonResponse(true, 'Pago eliminado exitosamente.');
            } else {
                sendJsonResponse(false, 'Error al eliminar el pago.', null, 500);
            }

        } catch (Exception $e) {
            error_log('Error al eliminar pago: ' . $e->getMessage());
            sendJsonResponse(false, 'Error al procesar la eliminación: ' . $e->getMessage(), null, 500);
        }
        break;
        
    case 'eliminarDefinitivamente':
        header('Content-Type: application/json');
        if (isset($_POST['id_pago'])) {
            try {
                if ($pago->eliminarDefinitivamente($_POST['id_pago'])) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Pago eliminado permanentemente exitosamente'
                    ]);
                } else {
                    throw new Exception('Error al eliminar el pago permanentemente');
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
        // Forzar salida JSON limpia sin contaminación de HTML/echo previos
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        try {
            // Usar el modelo Metodo directamente para listar métodos activos
            $metodoModel = new \App\Natys\Models\Metodo();
            $metodos = $metodoModel->listar();

            echo json_encode([
                'success' => true,
                'message' => 'Métodos de pago cargados',
                'data' => $metodos
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al listar métodos: ' . $e->getMessage(),
                'data' => []
            ], JSON_UNESCAPED_UNICODE);
        }
        exit();
        break;

    case 'obtenerDetalles':
        header('Content-Type: application/json');
        if (isset($_GET['id'])) {
            try {
                $id_pago = $_GET['id'];
                $detalles = $pago->obtenerDetallesCompletos($id_pago);
                
                if ($detalles) {
                    // Asegurar sesión y añadir usuario que realizó la acción (fallback a 'Sistema')
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $usuarioAccion = 'Sistema';
                    if (isset($_SESSION['usuario'])) {
                        if (!empty($_SESSION['usuario']['nombre'])) {
                            $usuarioAccion = $_SESSION['usuario']['nombre'];
                        } elseif (!empty($_SESSION['usuario']['usuario'])) {
                            $usuarioAccion = $_SESSION['usuario']['usuario'];
                        }
                    }
                    $detalles['usuario_accion'] = $usuarioAccion;
                    // Fecha de registro del evento (usar fecha del pago si existe o la hora actual)
                    $detalles['fecha_registro'] = $detalles['pago']['fecha'] ?? date('Y-m-d H:i:s');

                    echo json_encode([
                        'success' => true,
                        'message' => 'Detalles del pago cargados',
                        'data' => $detalles
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No se encontraron detalles para este pago'
                    ]);
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
        exit();
        break;
        
    case 'obtenerPedidosPendientes':
        header('Content-Type: application/json');
        try {
            $pedidoModel = new \App\Natys\Models\Pedido();
            $pedidos = $pedidoModel->obtenerPedidosPendientes();
            
            if ($pedidos === false) {
                throw new Exception('Error al obtener los pedidos pendientes');
            }
            
            echo json_encode([
                'success' => true,
                'data' => $pedidos
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
        break;
        
    case 'listar':
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            $estado = isset($_GET['estado']) ? intval($_GET['estado']) : 1;
            $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
            $pagos = $pago->listar($estado, $filtro);
            echo json_encode(['data' => $pagos]);
        } else {
            $pagos = $pago->listar(1);
            $pedidoModel = new \App\Natys\Models\Pedido();
            $pedidosPendientes = $pedidoModel->listar(0);
            include 'app/views/pago/pagoView.php';
        }
        exit();
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
        header('Location: index.php?url=pago&action=listar');
        exit;
    }
    
    $fechaInicio = $_GET['fecha_inicio'] ?? null;
    $fechaFin = $_GET['fecha_fin'] ?? null;
    $metodo = $_GET['metodo'] ?? 'todos'; // Nuevo parámetro
    
    $filtroFechas = !empty($fechaInicio) && !empty($fechaFin);
    
    if ($filtroFechas) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
            $filtroFechas = false;
        } else if (strtotime($fechaInicio) > strtotime($fechaFin)) {
            $temp = $fechaInicio;
            $fechaInicio = $fechaFin;
            $fechaFin = $temp;
        }
    }
    
    $pdf = new ReportePDF();
    $titulo = 'LISTADO DE PAGOS';
    $subtitulo = 'Todos los pagos registrados';
    
    if ($filtroFechas) {
        $subtitulo = "Del $fechaInicio al $fechaFin";
        
        // Agregar información del método al subtítulo si no es "todos"
        if ($metodo !== 'todos') {
            // Obtener el nombre del método seleccionado
            $metodoModel = new \App\Natys\Models\Metodo();
            $metodoData = $metodoModel->obtener($metodo);
            $nombreMetodo = $metodoData ? $metodoData['detalle'] : $metodo;
            $subtitulo .= " - Método: $nombreMetodo";
        }
    }
    
    $pdf->setTitulo($titulo);
    $pdf->setSubtitulo($subtitulo);
    $pdf->AddPage();
    
    if ($filtroFechas) {
        $pagos = $pago->listarPorFechas($fechaInicio, $fechaFin, $metodo);
    } else {
        // Para el caso sin filtro de fechas, mantener el comportamiento original
        $pagos = $pago->listar(1, 'todos');
    }
    
    $totalPagos = count($pagos);
    $montoTotal = 0;
    $efectivo = 0;
    $transferencias = 0;
    
    foreach ($pagos as $pagoItem) {
        $montoTotal += $pagoItem['monto'];
        if ($pagoItem['cod_metodo'] == 'EFECTIVO') {
            $efectivo += $pagoItem['monto'];
        } else {
            $transferencias += $pagoItem['monto'];
        }
    }
    
    $resumen = [
        'Total de Pagos' => $totalPagos,
        'Monto Total' => '$' . number_format($montoTotal, 2),
        'Efectivo' => '$' . number_format($efectivo, 2),
        'Transferencias/Otros' => '$' . number_format($transferencias, 2)
    ];
    
    if ($filtroFechas) {
        $resumen = array_merge(['Período' => "$fechaInicio al $fechaFin"], $resumen);
    }
    
    $pdf->agregarResumen($resumen);
    
    $headers = ['ID', 'Fecha', 'Banco', 'Referencia', 'Método', 'Monto'];
    $widths = [15, 25, 45, 35, 35, 35];
    $data = [];
    
    foreach ($pagos as $pagoItem) {
        $data[] = [
            $pagoItem['id_pago'],
            date('d/m/Y', strtotime($pagoItem['fecha'])),
            $pagoItem['banco'] ?? 'N/A',
            $pagoItem['referencia'] ?? 'N/A',
            $pagoItem['metodo_pago'],
            '$' . number_format($pagoItem['monto'], 2)
        ];
    }
    
    $pdf->crearTabla($headers, $data, $widths);
    $pdf->Output('I', 'Listado_Pagos_' . date('Y-m-d') . '.pdf');
    break;
            
    case 'reporte_efectivo':
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
            header('Location: index.php?url=pago&action=listar');
            exit;
        }
        
        $pdf = new ReportePDF();
        $pdf->setTitulo('PAGOS EN EFECTIVO');
        $pdf->setSubtitulo('Registro de pagos recibidos en efectivo');
        $pdf->AddPage();
        
        $pagos = $pago->listar(1, 'efectivo');
        
        $montoTotal = 0;
        foreach ($pagos as $pagoItem) {
            $montoTotal += $pagoItem['monto'];
        }
        
        $pdf->agregarResumen([
            'Total de Pagos en Efectivo' => count($pagos),
            'Monto Total' => '$' . number_format($montoTotal, 2)
        ]);
        
        if (count($pagos) > 0) {
            $headers = ['ID', 'Fecha', 'Referencia', 'Monto'];
            $widths = [30, 50, 60, 50];
            $data = [];
            
            foreach ($pagos as $pagoItem) {
                $data[] = [
                    $pagoItem['id_pago'],
                    date('d/m/Y', strtotime($pagoItem['fecha'])),
                    $pagoItem['referencia'] ?? 'Divisa',
                    '$' . number_format($pagoItem['monto'], 2)
                ];
            }
            
            $pdf->crearTabla($headers, $data, $widths);
        } else {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 10, 'No hay pagos en efectivo registrados', 0, 1, 'C');
        }
        
        $pdf->Output('I', 'Pagos_Efectivo_' . date('Y-m-d') . '.pdf');
        break;
        
    case 'reporte_transferencias':
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
            header('Location: index.php?url=pago&action=listar');
            exit;
        }
        
        $pdf = new ReportePDF();
        $pdf->setTitulo('TRANSFERENCIAS Y OTROS PAGOS');
        $pdf->setSubtitulo('Registro de pagos electrónicos');
        $pdf->AddPage();
        
        $pagos = $pago->listar(1, 'otros');
        
        $montoTotal = 0;
        foreach ($pagos as $pagoItem) {
            $montoTotal += $pagoItem['monto'];
        }
        
        $pdf->agregarResumen([
            'Total de Transferencias' => count($pagos),
            'Monto Total' => '$' . number_format($montoTotal, 2)
        ]);
        
        if (count($pagos) > 0) {
            $headers = ['ID', 'Fecha', 'Banco', 'Referencia', 'Método', 'Monto'];
            $widths = [15, 25, 40, 35, 35, 40];
            $data = [];
            
            foreach ($pagos as $pagoItem) {
                $data[] = [
                    $pagoItem['id_pago'],
                    date('d/m/Y', strtotime($pagoItem['fecha'])),
                    $pagoItem['banco'] ?? 'N/A',
                    $pagoItem['referencia'],
                    $pagoItem['metodo_pago'],
                    '$' . number_format($pagoItem['monto'], 2)
                ];
            }
            
            $pdf->crearTabla($headers, $data, $widths);
        } else {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 10, 'No hay transferencias registradas', 0, 1, 'C');
        }
        
        $pdf->Output('I', 'Transferencias_' . date('Y-m-d') . '.pdf');
        break;
        
    case 'reporte_comprobante':
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    error_reporting(0);
    
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
        header('Location: index.php?url=pago&action=listar');
        exit;
    }
    
    if (!isset($_GET['id'])) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'ID de pago requerido']);
        exit;
    }
    
    $id_pago = $_GET['id'];
    
    $detallesPago = $pago->obtenerDetallesCompletos($id_pago);
    
    if (!$detallesPago) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Pago no encontrado']);
        exit;
    }
    
    $pdf = new ReportePDF();
    
    // CONFIGURACIÓN ESPECIAL PARA CARACTERES - AGREGAR ESTO
    if (method_exists($pdf, 'SetFont')) {
        $pdf->SetFont('Arial', '', 12);
    }
    
    // USAR CARACTERES SIN ACENTOS PARA EVITAR PROBLEMAS
    $pdf->setTitulo('COMPROBANTE DE PAGO');
    $pdf->setSubtitulo('Recibo de pago #' . $id_pago);
    $pdf->AddPage();
    
    if ($detallesPago['cliente']) {
        // CAMBIAR TÍTULOS PARA EVITAR ACENTOS
        $pdf->agregarSeccion('INFORMACION DEL CLIENTE');
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(80, 80, 80);
        
        $infoCliente = [
            'Nombre' => $detallesPago['cliente']['nombre'] ?? 'No disponible',
            'Cedula/RIF' => $detallesPago['cliente']['cedula'] ?? 'No disponible',
            'Telefono' => $detallesPago['cliente']['telefono'] ?? 'No disponible',
            'Correo' => $detallesPago['cliente']['correo'] ?? 'No disponible' // Quitar "Electrónico"
        ];
        
        foreach ($infoCliente as $label => $value) {
            $pdf->SetX(15);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(70, 6, $label . ':', 0, 0, 'L');
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(0, 6, $value, 0, 1, 'L');
            $pdf->SetTextColor(80, 80, 80);
        }
        
        $pdf->Ln(5);
    }

    // CAMBIAR TÍTULOS PARA EVITAR ACENTOS
    $pdf->agregarSeccion('INFORMACION DEL PAGO');
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(80, 80, 80);
    
    $infoPago = [
        'Numero de Comprobante' => $id_pago,
        'Fecha del Pago' => date('d/m/Y', strtotime($detallesPago['pago']['fecha'])),
        'Metodo de Pago' => $detallesPago['pago']['metodo_pago'], // Quitar acento en "Método"
        'Banco' => $detallesPago['pago']['banco'] ?? 'N/A',
        'Referencia' => $detallesPago['pago']['referencia'] ?? 'N/A',
        'Monto Pagado' => '$' . number_format($detallesPago['pago']['monto'], 2)
    ];
    
    foreach ($infoPago as $label => $value) {
        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(70, 6, $label . ':', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 6, $value, 0, 1, 'L');
        $pdf->SetTextColor(80, 80, 80);
    }
    $pdf->Ln(5);
    
    if ($detallesPago['productos'] && count($detallesPago['productos']) > 0) {
        // CAMBIAR TÍTULO PARA EVITAR ACENTOS
        $pdf->agregarSeccion('DETALLE DE PRODUCTOS');
        
        $headers = ['Producto', 'Cantidad', 'Precio Unitario', 'Subtotal'];
        $widths = [80, 30, 40, 40];
        $data = [];
        
        $totalProductos = 0;
        foreach ($detallesPago['productos'] as $producto) {
            $nombre = $producto['nombre'] ?? 'Producto ' . ($producto['cod_producto'] ?? 'N/A');
            $cantidad = $producto['cantidad'] ?? 0;
            $precioUnitario = $producto['precio_unitario'] ?? $producto['precio'] ?? $producto['precio_venta'] ?? 0;
            $subtotal = $producto['subtotal'] ?? $producto['total'] ?? ($cantidad * $precioUnitario);
            
            $data[] = [
                $nombre,
                $cantidad,
                '$' . number_format($precioUnitario, 2),
                '$' . number_format($subtotal, 2)
            ];
            $totalProductos += $subtotal;
        }
        
        $pdf->crearTabla($headers, $data, $widths);
        
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(150, 8, 'Total Productos:', 0, 0, 'R');
        $pdf->Cell(40, 8, '$' . number_format($totalProductos, 2), 0, 1, 'R');
        
        $pdf->Ln(5);
    }
    
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFillColor(204, 29, 29);
    $pdf->Cell(0, 15, 'MONTO TOTAL PAGADO: $' . number_format($detallesPago['pago']['monto'], 2), 1, 1, 'C', true);
    
    $pdf->Ln(8);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->MultiCell(0, 4, 'Este comprobante certifica que el pago ha sido procesado exitosamente. Para cualquier consulta, contacte a atencion al cliente.', 0, 'C');
    
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(128, 128, 128);
    $pdf->Cell(0, 5, 'Comprobante generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    $pdf->Cell(0, 5, 'Sistema Natys - Todos los derechos reservados', 0, 1, 'C');
    
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    $pdf->Output('I', 'Comprobante_Pago_' . $id_pago . '.pdf');
    break;

    default:
        $pagos = $pago->listar(1);
        $pedidoModel = new \App\Natys\Models\Pedido();
        $pedidosPendientes = $pedidoModel->listar(0);
        include 'app/views/pago/pagoView.php';
        break;
}

} catch (\Exception $e) {
    // Manejar cualquier excepción no capturada
    sendJsonResponse(false, 'Error al procesar la solicitud: ' . $e->getMessage(), null, 500);
}

// Limpiar el buffer de salida y enviar la respuesta
if (ob_get_level()) {
    ob_end_flush();
    exit();
}