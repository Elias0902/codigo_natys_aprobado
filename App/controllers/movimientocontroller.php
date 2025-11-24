<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'App/Helpers/auth_check.php';
use App\Natys\Models\Movimiento;
use App\Natys\Models\Producto;
use App\Natys\Helpers\ReportePDF;

$movimientoModel = new Movimiento();
$action = $_REQUEST['action'] ?? 'listar';

function sendJsonResponse($success, $message = '', $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    $response = ['success' => $success];
    if ($message) $response['message'] = $message;
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response);
    exit();
}

try {
    switch ($action) {
        case 'guardar':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                sendJsonResponse(false, 'Método no permitido', [], 405);
            }
            
            $required = ['fecha', 'producto', 'cantidad'];
            $missing = array_diff($required, array_keys($_POST));
            
            if (!empty($missing)) {
                sendJsonResponse(false, 'Faltan campos requeridos: ' . implode(', ', $missing), [], 400);
            }
            
            if (!is_numeric($_POST['cantidad']) || $_POST['cantidad'] <= 0) {
                sendJsonResponse(false, 'La cantidad debe ser un número mayor a cero', [], 400);
            }
            
            $precio = $movimientoModel->obtenerPrecioProducto($_POST['producto']);
            if ($precio === false) {
                sendJsonResponse(false, 'No se pudo obtener el precio del producto', [], 500);
            }
        
            if ($movimientoModel->getGuardar(
                $_POST['fecha'],
                $_POST['observaciones'] ?? '',
                $_POST['producto'],
                (int)$_POST['cantidad'],
                $precio
            )) {
                $productoModel = new Producto();
                $productoModel->actualizarEstadosStock();
                sendJsonResponse(true, 'Movimiento registrado exitosamente');
            } else {
                sendJsonResponse(false, 'Error al guardar el movimiento', [], 500);
            }
            break;

        case 'editar':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                sendJsonResponse(false, 'Método no permitido', [], 405);
            }
            
            $required = ['num_movimiento', 'fecha', 'producto', 'cantidad'];
            $missing = array_diff($required, array_keys($_POST));
            
            if (!empty($missing)) {
                sendJsonResponse(false, 'Faltan campos requeridos: ' . implode(', ', $missing), [], 400);
            }
            
            if (!is_numeric($_POST['cantidad']) || $_POST['cantidad'] <= 0) {
                sendJsonResponse(false, 'La cantidad debe ser un número mayor a cero', [], 400);
            }
            
            $precio = $movimientoModel->obtenerPrecioProducto($_POST['producto']);
            if ($precio === false) {
                sendJsonResponse(false, 'No se pudo obtener el precio del producto', [], 500);
            }
        
            if ($movimientoModel->getActualizar(
                $_POST['num_movimiento'],
                $_POST['fecha'],
                $_POST['observaciones'] ?? '',
                $_POST['producto'],
                (int)$_POST['cantidad'],
                $precio
            )) {
                $productoModel = new Producto();
                $productoModel->actualizarEstadosStock();
                sendJsonResponse(true, 'Movimiento actualizado exitosamente');
            } else {
                sendJsonResponse(false, 'Error al editar el movimiento', [], 500);
            }
            break;
            
        case 'eliminar':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['num_movimiento'])) {
                sendJsonResponse(false, 'Solicitud inválida', [], 400);
            }
            
            if ($movimientoModel->getEliminar($_POST['num_movimiento'])) {
                $productoModel = new Producto();
                $productoModel->actualizarEstadosStock();
                sendJsonResponse(true, 'Movimiento eliminado exitosamente');
            } else {
                sendJsonResponse(false, 'Error al eliminar el movimiento', [], 500);
            }
            break;
            

            
        case 'obtenerMovimiento':
            if (empty($_GET['num_movimiento'])) {
                sendJsonResponse(false, 'ID de movimiento no proporcionado', [], 400);
            }

            $id = $_GET['num_movimiento'];
            $movimiento = $movimientoModel->obtenerMovimiento($id);

            if ($movimiento) {
                sendJsonResponse(true, '', $movimiento);
            } else {
                sendJsonResponse(false, 'Movimiento no encontrado', [], 404);
            }
            break;

        case 'detalles':
            if (empty($_GET['id'])) {
                echo '<div class="alert alert-danger">ID de movimiento no proporcionado</div>';
                exit;
            }

            $id = $_GET['id'];
            $movimiento = $movimientoModel->obtenerMovimiento($id);

            if (!$movimiento) {
                echo '<div class="alert alert-warning">Movimiento no encontrado</div>';
                exit;
            }

            // Renderizar vista de detalles
            echo '<div class="container-fluid">';
            echo '<div class="row g-3">';

            // Información del Movimiento
            echo '<div class="col-12">';
            echo '<div class="card" style="border-color: #d31111;">';
            echo '<div class="card-header text-white" style="background-color: #d31111 !important;">';
            echo '<h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Información del Movimiento</h5>';
            echo '</div>';
            echo '<div class="card-body">';
            echo '<div class="row g-3">';
            echo '<div class="col-md-4"><strong>N° Movimiento:</strong><br>' . htmlspecialchars($movimiento['num_movimiento']) . '</div>';
            echo '<div class="col-md-4"><strong>Fecha:</strong><br>' . date('d/m/Y', strtotime($movimiento['fecha'])) . '</div>';
            echo '<div class="col-md-4"><strong>Estado:</strong><br>' . ($movimiento['estado'] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>') . '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';

            // Información del Producto
            echo '<div class="col-12">';
            echo '<div class="card" style="border-color: #d31111;">';
            echo '<div class="card-header text-white" style="background-color: #d31111 !important;">';
            echo '<h5 class="card-title mb-0"><i class="fas fa-box me-2"></i>Información del Producto</h5>';
            echo '</div>';
            echo '<div class="card-body">';
            echo '<div class="row g-3">';
            echo '<div class="col-md-6"><strong>Código:</strong><br>' . htmlspecialchars($movimiento['cod_producto']) . '</div>';
            echo '<div class="col-md-6"><strong>Nombre:</strong><br>' . htmlspecialchars($movimiento['producto_nombre'] ?? 'N/A') . '</div>';
            echo '<div class="col-md-4"><strong>Cantidad:</strong><br>' . number_format($movimiento['cant_productos'], 2) . '</div>';
            echo '<div class="col-md-4"><strong>Precio Unitario:</strong><br>$' . number_format($movimiento['precio_venta'], 2) . '</div>';
            echo '<div class="col-md-4"><strong>Valor Total:</strong><br>$' . number_format($movimiento['cant_productos'] * $movimiento['precio_venta'], 2) . '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';

            // Observaciones
            echo '<div class="col-12">';
            echo '<div class="card" style="border-color: #d31111;">';
            echo '<div class="card-header text-white" style="background-color: #d31111 !important;">';
            echo '<h5 class="card-title mb-0"><i class="fas fa-comment me-2"></i>Observaciones</h5>';
            echo '</div>';
            echo '<div class="card-body">';
            echo '<div class="bg-light p-3 rounded">';
            echo !empty($movimiento['observaciones']) ? nl2br(htmlspecialchars($movimiento['observaciones'])) : '<em class="text-muted">Sin observaciones</em>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';

            echo '</div>';
            echo '</div>';
            break;

        case 'formularioEditar':
            if (empty($_GET['id'])) {
                echo '<div class="alert alert-danger">ID de movimiento no proporcionado</div>';
                exit;
            }

            $id = $_GET['id'];
            $movimiento = $movimientoModel->obtenerMovimiento($id);
            $productos = $movimientoModel->listarTodosProductos();

            if (!$movimiento) {
                echo '<div class="alert alert-warning">Movimiento no encontrado</div>';
                exit;
            }

            // Renderizar formulario de edición
            echo '<form id="formEditarMovimiento" class="needs-validation" novalidate>';
            echo '<div class="row">';
            echo '<div class="col-md-6 mb-3 position-relative">';
            echo '<label for="edit_fecha" class="form-label"><i class="fas fa-calendar me-1"></i>Fecha *</label>';
            echo '<input type="date" class="form-control" id="edit_fecha" name="fecha" value="' . htmlspecialchars($movimiento['fecha']) . '" required>';
            echo '<div class="valid-feedback"><i class="fas fa-check-circle me-2"></i>Campo válido</div>';
            echo '<div class="invalid-feedback"><i class="fas fa-exclamation-circle me-2"></i>Por favor seleccione una fecha</div>';
            echo '</div>';

            echo '<div class="col-md-6 mb-3 position-relative">';
            echo '<label for="edit_producto" class="form-label"><i class="fas fa-box me-1"></i>Producto *</label>';
            echo '<select class="form-select" id="edit_producto" name="producto" required>';
            echo '<option value="">Seleccione un producto</option>';
            foreach ($productos as $producto) {
                $selected = ($producto['cod_producto'] == $movimiento['cod_producto']) ? 'selected' : '';
                $disabled = ($producto['estado'] == 0) ? 'disabled' : '';
                $text = htmlspecialchars($producto['nombre']);
                if ($producto['estado'] == 0) $text .= ' (Inactivo)';
                echo '<option value="' . htmlspecialchars($producto['cod_producto']) . '" ' . $selected . ' ' . $disabled . '>' . $text . '</option>';
            }
            echo '</select>';
            echo '<div class="valid-feedback"><i class="fas fa-check-circle me-2"></i>Campo válido</div>';
            echo '<div class="invalid-feedback"><i class="fas fa-exclamation-circle me-2"></i>Por favor seleccione un producto</div>';
            echo '</div>';
            echo '</div>';

            echo '<div class="row">';
            echo '<div class="col-md-6 mb-3 position-relative">';
            echo '<label for="edit_cantidad" class="form-label"><i class="fas fa-hashtag me-1"></i>Cantidad *</label>';
            echo '<input type="number" class="form-control" id="edit_cantidad" name="cantidad" value="' . htmlspecialchars($movimiento['cant_productos']) . '" min="1" required>';
            echo '<div class="valid-feedback"><i class="fas fa-check-circle me-2"></i>Campo válido</div>';
            echo '<div class="invalid-feedback"><i class="fas fa-exclamation-circle me-2"></i>Por favor ingrese una cantidad válida</div>';
            echo '</div>';

            echo '<div class="col-md-6 mb-3 position-relative">';
            echo '<label for="edit_precio" class="form-label"><i class="fas fa-dollar-sign me-1"></i>Precio Unitario</label>';
            echo '<input type="number" class="form-control" id="edit_precio" name="precio" value="' . htmlspecialchars($movimiento['precio_venta']) . '" step="0.01" readonly style="background-color: #f8f9fa;">';
            echo '<div class="form-text">El precio se cargará automáticamente al seleccionar el producto</div>';
            echo '<div class="valid-feedback"><i class="fas fa-check-circle me-2"></i>Campo válido</div>';
            echo '<div class="invalid-feedback"><i class="fas fa-exclamation-circle me-2"></i>Por favor ingrese el precio</div>';
            echo '</div>';
            echo '</div>';

            echo '<div class="row">';
            echo '<div class="col-12 mb-3 position-relative">';
            echo '<label for="edit_observaciones" class="form-label"><i class="fas fa-comment me-1"></i>Observaciones</label>';
            echo '<textarea class="form-control" id="edit_observaciones" name="observaciones" rows="2">' . htmlspecialchars($movimiento['observaciones'] ?? '') . '</textarea>';
            echo '<div class="valid-feedback"><i class="fas fa-check-circle me-2"></i>Campo válido</div>';
            echo '<div class="invalid-feedback"><i class="fas fa-exclamation-circle me-2"></i>Las observaciones deben tener al menos 3 caracteres</div>';
            echo '</div>';
            echo '</div>';

            echo '<input type="hidden" name="num_movimiento" value="' . htmlspecialchars($movimiento['num_movimiento']) . '">';

            echo '<div class="alert alert-info mt-3">';
            echo '<i class="fas fa-info-circle me-2"></i>';
            echo '<strong>Nota:</strong> Los campos marcados con * son obligatorios.';
            echo '</div>';
            echo '</form>';

            echo '<div class="modal-footer">';
            echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Cancelar</button>';
            echo '<button type="button" class="btn btn-success" id="actualizarMovimiento"><i class="fas fa-save me-1"></i>Actualizar Movimiento</button>';
            echo '</div>';
            break;
            
        case 'datatables':
            $start = $_POST['start'] ?? 0;
            $length = $_POST['length'] ?? 10;
            $search = $_POST['search']['value'] ?? '';
            $mostrarHistorial = isset($_POST['mostrarHistorial']) && $_POST['mostrarHistorial'] == 1;

            $data = $movimientoModel->listarParaDataTables($start, $length, $search, $mostrarHistorial);
            $total = $movimientoModel->contarTotalMovimientos($mostrarHistorial);
            $filtrados = $movimientoModel->contarMovimientosFiltrados($search, $mostrarHistorial);

            header('Content-Type: application/json');
            echo json_encode([
                'draw' => intval($_POST['draw'] ?? 1),
                'recordsTotal' => $total,
                'recordsFiltered' => $filtrados,
                'data' => $data
            ]);
            exit();
            break;
            
        case 'listar_historial':
            $historial = $movimientoModel->listarHistorial();
            sendJsonResponse(true, '', $historial);
            break;
            
        case 'obtenerPrecio':
            if (empty($_POST['cod_producto'])) {
                sendJsonResponse(false, 'Código de producto no proporcionado', [], 400);
            }
            $precio = $movimientoModel->obtenerPrecioProducto($_POST['cod_producto']);
            if ($precio !== false) {
                sendJsonResponse(true, '', ['precio' => $precio]);
            } else {
                sendJsonResponse(false, 'No se pudo obtener el precio', [], 404);
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
                header('Location: index.php?url=movimiento&action=listar');
                exit;
            }
            
            $pdf = new ReportePDF();
            $pdf->setTitulo('HISTORIAL DE MOVIMIENTOS');
            $pdf->setSubtitulo('Registro de entradas y salidas de inventario');
            $pdf->AddPage();
            
            $movimientos = $movimientoModel->listar();
            
            $totalMovimientos = count($movimientos);
            $entradas = 0;
            $salidas = 0;
            
            foreach ($movimientos as $mov) {
                if ($mov['cant_productos'] > 0) {
                    $entradas += $mov['cant_productos'];
                } else {
                    $salidas += abs($mov['cant_productos']);
                }
            }
            
            $pdf->agregarResumen([
                'Total de Movimientos' => $totalMovimientos,
                'Total Entradas' => number_format($entradas, 2),
                'Total Salidas' => number_format($salidas, 2)
            ]);
            
            $headers = ['Num.', 'Fecha', 'Producto', 'Cantidad', 'Tipo', 'Observaciones'];
            $widths = [20, 25, 50, 25, 25, 45];
            $data = [];
            
            foreach ($movimientos as $mov) {
                $tipo = $mov['cant_productos'] > 0 ? 'Entrada' : 'Salida';
                $cantidad = abs($mov['cant_productos']);
                $observaciones = !empty($mov['observaciones']) ? 
                    substr($mov['observaciones'], 0, 30) . '...' : 
                    'Sin observaciones';
                
                $data[] = [
                    $mov['num_movimiento'],
                    date('d/m/Y', strtotime($mov['fecha'])),
                    $mov['producto_nombre'] ?? 'N/A',
                    number_format($cantidad, 2),
                    $tipo,
                    $observaciones
                ];
            }
            
            $pdf->crearTabla($headers, $data, $widths);
            $pdf->Output('I', 'Historial_Movimientos_' . date('Y-m-d') . '.pdf');
            break;
            
        case 'reporte_entradas':
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
                header('Location: index.php?url=movimiento&action=listar');
                exit;
            }
            
            $pdf = new ReportePDF();
            $pdf->setTitulo('ENTRADAS DE INVENTARIO');
            $pdf->setSubtitulo('Registro de productos ingresados al inventario');
            $pdf->AddPage();
            
            $movimientos = $movimientoModel->listar();
            
            $entradas = array_filter($movimientos, function($m) {
                return $m['cant_productos'] > 0;
            });
            
            $totalCantidad = 0;
            $valorTotal = 0;
            
            foreach ($entradas as $entrada) {
                $totalCantidad += $entrada['cant_productos'];
                $valorTotal += $entrada['cant_productos'] * $entrada['precio_venta'];
            }
            
            $pdf->agregarResumen([
                'Total de Entradas' => count($entradas),
                'Cantidad Total' => number_format($totalCantidad, 2),
                'Valor Total' => '$' . number_format($valorTotal, 2)
            ]);
            
            if (count($entradas) > 0) {
                $headers = ['Num.', 'Fecha', 'Producto', 'Cantidad', 'Precio', 'Valor'];
                $widths = [20, 25, 55, 25, 30, 35];
                $data = [];
                
                foreach ($entradas as $entrada) {
                    $valor = $entrada['cant_productos'] * $entrada['precio_venta'];
                    $data[] = [
                        $entrada['num_movimiento'],
                        date('d/m/Y', strtotime($entrada['fecha'])),
                        $entrada['producto_nombre'] ?? 'N/A',
                        number_format($entrada['cant_productos'], 2),
                        '$' . number_format($entrada['precio_venta'], 2),
                        '$' . number_format($valor, 2)
                    ];
                }
                
                $pdf->crearTabla($headers, $data, $widths);
            } else {
                $pdf->SetFont('Arial', 'I', 10);
                $pdf->Cell(0, 10, 'No hay entradas de inventario registradas', 0, 1, 'C');
            }
            
            $pdf->Output('I', 'Entradas_Inventario_' . date('Y-m-d') . '.pdf');
            break;
            
        case 'reporte_salidas':
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
                header('Location: index.php?url=movimiento&action=listar');
                exit;
            }
            
            $pdf = new ReportePDF();
            $pdf->setTitulo('SALIDAS DE INVENTARIO');
            $pdf->setSubtitulo('Registro de productos retirados del inventario');
            $pdf->AddPage();
            
            $movimientos = $movimientoModel->listar();
            
            $salidas = array_filter($movimientos, function($m) {
                return isset($m['cant_productos']) && $m['cant_productos'] < 0;
            });
            
            $salidas = array_values($salidas);
            
            $totalCantidad = 0;
            $valorTotal = 0;
            
            foreach ($salidas as $salida) {
                $cantidad = abs($salida['cant_productos']);
                $totalCantidad += $cantidad;
                $valorTotal += $cantidad * ($salida['precio_venta'] ?? 0);
            }
            
            $pdf->agregarResumen([
                'Total de Salidas' => count($salidas),
                'Cantidad Total' => number_format($totalCantidad, 2),
                'Valor Total' => '$' . number_format($valorTotal, 2)
            ]);
            
            if (count($salidas) > 0) {
                $headers = ['Num.', 'Fecha', 'Producto', 'Cantidad', 'Precio', 'Valor'];
                $widths = [20, 25, 55, 25, 30, 35];
                $data = [];
                
                foreach ($salidas as $salida) {
                    $cantidad = abs($salida['cant_productos']);
                    $precio = $salida['precio_venta'] ?? 0;
                    $valor = $cantidad * $precio;
                    
                    $data[] = [
                        $salida['num_movimiento'] ?? 'N/A',
                        isset($salida['fecha']) ? date('d/m/Y', strtotime($salida['fecha'])) : 'N/A',
                        $salida['producto_nombre'] ?? 'N/A',
                        number_format($cantidad, 2),
                        '$' . number_format($precio, 2),
                        '$' . number_format($valor, 2)
                    ];
                }
                
                $pdf->crearTabla($headers, $data, $widths);
            } else {
                $pdf->SetFont('Arial', 'I', 10);
                $pdf->Cell(0, 10, 'No hay salidas de inventario registradas', 0, 1, 'C');
            }
            
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            $pdf->Output('I', 'Salidas_Inventario_' . date('Y-m-d') . '.pdf');
            break;

        case 'reporte_por_fechas':
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
                header('Location: index.php?url=movimiento&action=listar');
                exit;
            }
            
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;
            
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
            $titulo = 'REPORTE DE MOVIMIENTOS POR FECHAS';
            $subtitulo = 'Todos los movimientos registrados';
            
            if ($filtroFechas) {
                $subtitulo = "Del $fechaInicio al $fechaFin";
            }
            
            $pdf->setTitulo($titulo);
            $pdf->setSubtitulo($subtitulo);
            $pdf->AddPage();
            
            if ($filtroFechas) {
                $movimientos = $movimientoModel->listarPorFechas($fechaInicio, $fechaFin);
            } else {
                $movimientos = $movimientoModel->listar();
            }
            
            $totalMovimientos = count($movimientos);
            $entradas = 0;
            $salidas = 0;
            $valorEntradas = 0;
            $valorSalidas = 0;
            
            foreach ($movimientos as $mov) {
                if ($mov['cant_productos'] > 0) {
                    $entradas += $mov['cant_productos'];
                    $valorEntradas += $mov['cant_productos'] * ($mov['precio_venta'] ?? 0);
                } else {
                    $cantidad = abs($mov['cant_productos']);
                    $salidas += $cantidad;
                    $valorSalidas += $cantidad * ($mov['precio_venta'] ?? 0);
                }
            }
            
            $resumen = [
                'Total de Movimientos' => $totalMovimientos,
                'Total Entradas' => number_format($entradas, 2) . ' unidades',
                'Total Salidas' => number_format($salidas, 2) . ' unidades',
                'Valor Entradas' => '$' . number_format($valorEntradas, 2),
                'Valor Salidas' => '$' . number_format($valorSalidas, 2)
            ];
            
            if ($filtroFechas) {
                $resumen = array_merge(['Período' => "$fechaInicio al $fechaFin"], $resumen);
            }
            
            $pdf->agregarResumen($resumen);
            
            if (count($movimientos) > 0) {
                $headers = ['Num.', 'Fecha', 'Producto', 'Cantidad', 'Tipo', 'Precio', 'Valor'];
                $widths = [15, 20, 45, 20, 20, 25, 30];
                $data = [];
                
                foreach ($movimientos as $mov) {
                    $tipo = $mov['cant_productos'] > 0 ? 'Entrada' : 'Salida';
                    $cantidad = abs($mov['cant_productos']);
                    $precio = $mov['precio_venta'] ?? 0;
                    $valor = $cantidad * $precio;
                    
                    $data[] = [
                        $mov['num_movimiento'] ?? 'N/A',
                        date('d/m/Y', strtotime($mov['fecha'])),
                        $mov['producto_nombre'] ?? 'N/A',
                        number_format($cantidad, 2),
                        $tipo,
                        '$' . number_format($precio, 2),
                        '$' . number_format($valor, 2)
                    ];
                }
                
                $pdf->crearTabla($headers, $data, $widths);
            } else {
                $pdf->SetFont('Arial', 'I', 10);
                $pdf->Cell(0, 10, 'No hay movimientos registrados en el período seleccionado', 0, 1, 'C');
            }
            
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            $pdf->Output('I', 'Movimientos_Por_Fechas_' . date('Y-m-d') . '.pdf');
            break;
            
        case 'reporte_kardex':
            while (ob_get_level()) {
                ob_end_clean();
            }

            error_reporting(0);

            // Actualizar actividad de sesión para evitar timeout durante generación del reporte
            $_SESSION['last_activity'] = time();

            if (!in_array($_SESSION['usuario']['rol'], ['admin', 'superadmin'])) {
                $_SESSION['error'] = 'No tienes permisos para generar reportes';
                header('Location: index.php?url=movimiento&action=listar');
                exit;
            }

            if (!isset($_GET['cod_producto'])) {
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['error' => 'Código de producto requerido']);
                exit;
            }

            $cod_producto = $_GET['cod_producto'];
            $kardex = $movimientoModel->obtenerKardex($cod_producto);
            
            if (!$kardex || count($kardex) == 0) {
                $pdf = new ReportePDF();
                $pdf->setTitulo('KARDEX DE PRODUCTO');
                $pdf->setSubtitulo('Producto: ' . $cod_producto);
                $pdf->AddPage();

                $pdf->agregarSeccion('Información del Producto');
                $pdf->agregarResumen([
                    'Código' => $cod_producto,
                    'Nombre' => 'Producto no encontrado',
                    'Total de Movimientos' => 0
                ]);

                $pdf->SetFont('Arial', 'I', 12);
                $pdf->Cell(0, 20, 'No hay movimientos registrados para este producto', 0, 1, 'C');

                while (ob_get_level()) {
                    ob_end_clean();
                }

                $pdf->Output('I', 'Kardex_' . $cod_producto . '_' . date('Y-m-d') . '.pdf');
                exit;
            }
            
            $pdf = new ReportePDF();
            $pdf->setTitulo('KARDEX DE PRODUCTO');
            $pdf->setSubtitulo('Producto: ' . ($kardex[0]['producto_nombre'] ?? $cod_producto));
            $pdf->AddPage();
            
            $pdf->agregarSeccion('Información del Producto');
            $pdf->agregarResumen([
                'Código' => $cod_producto,
                'Nombre' => $kardex[0]['producto_nombre'] ?? 'N/A',
                'Total de Movimientos' => count($kardex)
            ]);
            
            $headers = ['Fecha', 'Tipo', 'Entrada', 'Salida', 'Saldo', 'Observaciones'];
            $widths = [25, 25, 25, 25, 25, 65];
            $data = [];
            
            $saldo = 0;
            foreach ($kardex as $mov) {
                $entrada = $mov['cant_productos'] > 0 ? number_format($mov['cant_productos'], 2) : '-';
                $salida = $mov['cant_productos'] < 0 ? number_format(abs($mov['cant_productos']), 2) : '-';
                $saldo += $mov['cant_productos'];
                
                $tipo = $mov['cant_productos'] > 0 ? 'Entrada' : 'Salida';
                $obs = !empty($mov['observaciones']) ? 
                    substr($mov['observaciones'], 0, 40) . '...' : 
                    '-';
                
                $data[] = [
                    date('d/m/Y', strtotime($mov['fecha'])),
                    $tipo,
                    $entrada,
                    $salida,
                    number_format($saldo, 2),
                    $obs
                ];
            }
            
            $pdf->crearTabla($headers, $data, $widths);
            
            $pdf->Ln(5);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(125, 10, 'SALDO ACTUAL:', 0, 0, 'R');
            $pdf->Cell(65, 10, number_format($saldo, 2) . ' unidades', 0, 1, 'R');
            
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            $pdf->Output('I', 'Kardex_' . $cod_producto . '_' . date('Y-m-d') . '.pdf');
            break;

        case 'entradas_por_fechas':
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
                header('Location: index.php?url=movimiento&action=listar');
                exit;
            }
            
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;
            
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
            $titulo = 'ENTRADAS DE INVENTARIO POR FECHAS';
            $subtitulo = 'Registro de productos ingresados al inventario';
            
            if ($filtroFechas) {
                $subtitulo = "Del $fechaInicio al $fechaFin";
            }
            
            $pdf->setTitulo($titulo);
            $pdf->setSubtitulo($subtitulo);
            $pdf->AddPage();
            
            if ($filtroFechas) {
                $movimientos = $movimientoModel->listarPorFechas($fechaInicio, $fechaFin);
            } else {
                $movimientos = $movimientoModel->listar();
            }
            
            $entradas = array_filter($movimientos, function($m) {
                return $m['cant_productos'] > 0;
            });
            
            $entradas = array_values($entradas);
            
            $totalCantidad = 0;
            $valorTotal = 0;
            
            foreach ($entradas as $entrada) {
                $totalCantidad += $entrada['cant_productos'];
                $valorTotal += $entrada['cant_productos'] * ($entrada['precio_venta'] ?? 0);
            }
            
            $resumen = [
                'Total de Entradas' => count($entradas),
                'Cantidad Total' => number_format($totalCantidad, 2),
                'Valor Total' => '$' . number_format($valorTotal, 2)
            ];
            
            if ($filtroFechas) {
                $resumen = array_merge(['Período' => "$fechaInicio al $fechaFin"], $resumen);
            }
            
            $pdf->agregarResumen($resumen);
            
            if (count($entradas) > 0) {
                $headers = ['Num.', 'Fecha', 'Producto', 'Cantidad', 'Precio', 'Valor'];
                $widths = [20, 25, 55, 25, 30, 35];
                $data = [];
                
                foreach ($entradas as $entrada) {
                    $valor = $entrada['cant_productos'] * ($entrada['precio_venta'] ?? 0);
                    $data[] = [
                        $entrada['num_movimiento'] ?? 'N/A',
                        date('d/m/Y', strtotime($entrada['fecha'])),
                        $entrada['producto_nombre'] ?? 'N/A',
                        number_format($entrada['cant_productos'], 2),
                        '$' . number_format($entrada['precio_venta'] ?? 0, 2),
                        '$' . number_format($valor, 2)
                    ];
                }
                
                $pdf->crearTabla($headers, $data, $widths);
            } else {
                $pdf->SetFont('Arial', 'I', 10);
                $pdf->Cell(0, 10, 'No hay entradas de inventario registradas en el período seleccionado', 0, 1, 'C');
            }
            
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            $pdf->Output('I', 'Entradas_Por_Fechas_' . date('Y-m-d') . '.pdf');
            break;

        case 'salidas_por_fechas':
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
                header('Location: index.php?url=movimiento&action=listar');
                exit;
            }
            
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;
            
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
            $titulo = 'SALIDAS DE INVENTARIO POR FECHAS';
            $subtitulo = 'Registro de productos retirados del inventario';
            
            if ($filtroFechas) {
                $subtitulo = "Del $fechaInicio al $fechaFin";
            }
            
            $pdf->setTitulo($titulo);
            $pdf->setSubtitulo($subtitulo);
            $pdf->AddPage();
            
            if ($filtroFechas) {
                $movimientos = $movimientoModel->listarPorFechas($fechaInicio, $fechaFin);
            } else {
                $movimientos = $movimientoModel->listar();
            }
            
            $salidas = array_filter($movimientos, function($m) {
                return isset($m['cant_productos']) && $m['cant_productos'] < 0;
            });
            
            $salidas = array_values($salidas);
            
            $totalCantidad = 0;
            $valorTotal = 0;
            
            foreach ($salidas as $salida) {
                $cantidad = abs($salida['cant_productos']);
                $totalCantidad += $cantidad;
                $valorTotal += $cantidad * ($salida['precio_venta'] ?? 0);
            }
            
            $resumen = [
                'Total de Salidas' => count($salidas),
                'Cantidad Total' => number_format($totalCantidad, 2),
                'Valor Total' => '$' . number_format($valorTotal, 2)
            ];
            
            if ($filtroFechas) {
                $resumen = array_merge(['Período' => "$fechaInicio al $fechaFin"], $resumen);
            }
            
            $pdf->agregarResumen($resumen);
            
            if (count($salidas) > 0) {
                $headers = ['Num.', 'Fecha', 'Producto', 'Cantidad', 'Precio', 'Valor'];
                $widths = [20, 25, 55, 25, 30, 35];
                $data = [];
                
                foreach ($salidas as $salida) {
                    $cantidad = abs($salida['cant_productos']);
                    $precio = $salida['precio_venta'] ?? 0;
                    $valor = $cantidad * $precio;
                    
                    $data[] = [
                        $salida['num_movimiento'] ?? 'N/A',
                        date('d/m/Y', strtotime($salida['fecha'])),
                        $salida['producto_nombre'] ?? 'N/A',
                        number_format($cantidad, 2),
                        '$' . number_format($precio, 2),
                        '$' . number_format($valor, 2)
                    ];
                }
                
                $pdf->crearTabla($headers, $data, $widths);
            } else {
                $pdf->SetFont('Arial', 'I', 10);
                $pdf->Cell(0, 10, 'No hay salidas de inventario registradas en el período seleccionado', 0, 1, 'C');
            }
            
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            $pdf->Output('I', 'Salidas_Por_Fechas_' . date('Y-m-d') . '.pdf');
            break;

        case 'obtenerKardex':
            if (empty($_GET['cod_producto'])) {
                sendJsonResponse(false, 'Código de producto no proporcionado', [], 400);
            }
            $cod_producto = $_GET['cod_producto'];
            $kardex = $movimientoModel->obtenerKardex($cod_producto);
            if ($kardex !== false) {
                sendJsonResponse(true, '', $kardex);
            } else {
                sendJsonResponse(false, 'No se pudo obtener el kardex', [], 404);
            }
            break;

        case 'listar':
        default:
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                $movimientos = $movimientoModel->listar();
                sendJsonResponse(true, '', $movimientos);
            } else {
                $productos = $movimientoModel->listarTodosProductos();
                require_once 'App/views/movimiento/movimientoView.php';
            }
            break;
    }
} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    $message = $e->getMessage() ?: 'Error interno del servidor';
    
    if ($code >= 500) {
        error_log(sprintf(
            'Error en %s: %s\n%s',
            $action,
            $message,
            $e->getTraceAsString()
        ));
    }
    
    sendJsonResponse(false, $message, [], $code);
}