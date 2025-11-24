<?php
require_once 'App/Helpers/auth_check.php';

use App\Natys\Models\Cliente;
use App\Natys\Helpers\ReportePDF;

$clienteModel = new Cliente();

$action = $_REQUEST['action'] ?? 'listar';

switch ($action) {
    case 'formNuevo':
        echo '
        <div class="container-fluid p-0">
            <form id="formCliente" class="needs-validation" novalidate>
                <input type="hidden" name="original_cedula" id="original_cedula" value="">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ced_cliente" class="form-label">
                            <i class="fas fa-id-card me-1"></i>Cédula *
                        </label>
                        <div class="position-relative">
                            <input type="text" 
                                   class="form-control" 
                                   id="ced_cliente" 
                                   name="ced_cliente" 
                                   placeholder="Ingrese la cédula (7, 8 o 9 dígitos)"
                                   pattern="\d{7,9}"
                                   minlength="7"
                                   maxlength="9"
                                   required>
                            <div class="valid-feedback"></div>
                            <div class="invalid-feedback">La cédula debe contener entre 7 y 9 dígitos</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="nomcliente" class="form-label">
                            <i class="fas fa-user me-1"></i>Nombre Completo *
                        </label>
                        <div class="position-relative">
                            <input type="text" 
                                   class="form-control" 
                                   id="nomcliente" 
                                   name="nomcliente" 
                                   placeholder="Ingrese el nombre completo"
                                   pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s\'+"
                                   minlength="3"
                                   maxlength="100"
                                   oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s\']/g, \'\')"
                                   required>
                            <div class="valid-feedback"></div>
                            <div class="invalid-feedback">Solo se permiten letras, espacios y acentos (mín. 3 caracteres)</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="correo" class="form-label">
                            <i class="fas fa-envelope me-1"></i>Correo Electrónico *
                        </label>
                        <div class="position-relative">
                            <input type="email" 
                                   class="form-control" 
                                   id="correo" 
                                   name="correo" 
                                   placeholder="ejemplo@correo.com"
                                   pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                                   required>
                            <div class="valid-feedback"></div>
                            <div class="invalid-feedback">Por favor ingrese un correo electrónico válido (ejemplo@dominio.com)</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="telefono" class="form-label">
                            <i class="fas fa-phone me-1"></i>Teléfono *
                        </label>
                        <div class="position-relative">
                            <input type="tel" 
                                   class="form-control" 
                                   id="telefono" 
                                   name="telefono" 
                                   placeholder="Ej: 04141234567 o 02121234567"
                                   pattern="(^04(12|14|16|24|26|22)\d{7}$)|(^02\d{9}$)"
                                   maxlength="11"
                                   required>
                            <div class="valid-feedback"></div>
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle me-1"></i> El teléfono es obligatorio y solo permite números
                            </div>
                            <div class="invalid-feedback invalid-feedback-pattern" style="display: none;">
                                <i class="fas fa-exclamation-circle me-1"></i> Formato válido: 04xx1234567 (móvil) o 02121234567 (fijo)
                            </div>
                            <small class="form-text text-muted">
                                Móvil: 0412, 0414, 0416, 0422, 0424, 0426 + 7 dígitos<br>
                                Fijo: 02 + código de área (1-4 dígitos) + número local
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="direccion" class="form-label">
                        <i class="fas fa-map-marker-alt me-1"></i>Dirección *
                    </label>
                    <div class="position-relative">
                        <textarea class="form-control" 
                                  id="direccion" 
                                  name="direccion" 
                                  rows="3" 
                                  placeholder="Ingrese la dirección (mínimo 5 caracteres)"
                                  minlength="5"
                                  maxlength="255"
                                  required></textarea>
                        <div class="valid-feedback">
                        </div>
                        <div class="invalid-feedback">
                            <i class="fas fa-times-circle"></i> Por favor ingrese una dirección (mínimo 5 caracteres)
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Guardar Cliente
                    </button>
                </div>
            </form>
        </div>';
        break;

    case 'formActualizar':
        if (isset($_GET['ced_cliente'])) {
            $cedula = $_GET['ced_cliente'];
            $datos = $clienteModel->obtenerCliente($cedula);
            
            if ($datos) {
                echo '
                <div class="container-fluid p-0">
                    <form id="formCliente" class="needs-validation" novalidate>
                        <input type="hidden" name="original_cedula" id="original_cedula" value="' . htmlspecialchars($datos['ced_cliente']) . '">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ced_cliente" class="form-label">
                                    <i class="fas fa-id-card me-1"></i>Cédula *
                                </label>
                                <div class="position-relative">
                                    <input type="text" 
                                           class="form-control" 
                                           id="ced_cliente" 
                                           name="ced_cliente" 
                                           value="' . htmlspecialchars($datos['ced_cliente']) . '"
                                           placeholder="Ingrese la cédula (7, 8 o 9 dígitos)"
                                           pattern="\d{7,9}"
                                           minlength="7"
                                           maxlength="9"
                                           readonly
                                           required>
                                    <div class="valid-feedback"></div>
                                    <div class="invalid-feedback">La cédula debe contener entre 7 y 9 dígitos</div>
                                </div>
                                <small class="text-muted">La cédula no se puede modificar</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="nomcliente" class="form-label">
                                    <i class="fas fa-user me-1"></i>Nombre Completo *
                                </label>
                                <div class="position-relative">
                                    <input type="text" 
                                           class="form-control" 
                                           id="nomcliente" 
                                           name="nomcliente" 
                                           value="' . htmlspecialchars($datos['nomcliente']) . '" 
                                           placeholder="Ingrese el nombre completo"
                                           pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s\']+$"
                                           minlength="3"
                                           maxlength="100"
                                           oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s\']/g, \'\')"
                                           required>
                                    <div class="valid-feedback"></div>
                                    <div class="invalid-feedback">Solo se permiten letras, espacios y acentos (mín. 3 caracteres)</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="correo" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Correo Electrónico *
                                </label>
                                <div class="position-relative">
                                    <input type="email" 
                                           class="form-control" 
                                           id="correo" 
                                           name="correo" 
                                           value="' . htmlspecialchars($datos['correo']) . '"
                                           placeholder="ejemplo@correo.com"
                                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                                           required>
                                    <div class="valid-feedback"></div>
                                    <div class="invalid-feedback">Por favor ingrese un correo electrónico válido (ejemplo@dominio.com)</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">
                                    <i class="fas fa-phone me-1"></i>Teléfono *
                                </label>
                                <div class="position-relative">
                                    <input type="tel" 
                                           class="form-control" 
                                           id="telefono" 
                                           name="telefono" 
                                           value="' . htmlspecialchars($datos['telefono']) . '"
                                           placeholder="Ej: 04141234567 o 02121234567"
                                           pattern="(^04(12|14|16|24|26|22)\d{7}$)|(^02\d{9}$)"
                                           maxlength="11"
                                           required>
                                    <div class="valid-feedback"></div>
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle me-1"></i> El teléfono es obligatorio
                                    </div>
                                    <div class="invalid-feedback invalid-feedback-pattern" style="display: none;">
                                        <i class="fas fa-exclamation-circle me-1"></i> Formato válido: 04xx1234567 (móvil) o 02121234567 (fijo)
                                    </div>
                                    <small class="form-text text-muted">
                                        Móvil: 0412, 0414, 0416, 0422, 0424, 0426 + 7 dígitos<br>
                                        Fijo: 02 + código de área (1-4 dígitos) + número local
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="direccion" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Dirección *
                            </label>
                            <div class="position-relative">
                                <textarea class="form-control" 
                                          id="direccion" 
                                          name="direccion" 
                                          rows="3" 
                                          placeholder="Ingrese la dirección (mínimo 5 caracteres)"
                                          minlength="5"
                                          maxlength="255"
                                          required>' . htmlspecialchars($datos['direccion']) . '</textarea>
                                <div class="valid-feedback"></div>
                                <div class="invalid-feedback">
                                    <i class="fas fa-times-circle"></i> Por favor ingrese una dirección (mínimo 5 caracteres)
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Actualizar Cliente
                            </button>
                        </div>
                    </form>
                </div>';
            } else {
                echo '<div class="alert alert-danger">Cliente no encontrado</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Falta la cédula del cliente</div>';
        }
        break;

    case 'guardar':
        header('Content-Type: application/json');
        
        // Verificar que todos los campos requeridos estén presentes
        $required_fields = ['ced_cliente', 'nomcliente', 'correo', 'telefono', 'direccion'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Faltan los siguientes campos requeridos: ' . implode(', ', $missing_fields)
            ]);
            exit;
        }
        
        try {
            // Asignar valores
            $cedula = trim($_POST['ced_cliente']);
            $nombre = trim($_POST['nomcliente']);
            $correo = trim($_POST['correo']);
            $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
            $direccion = trim($_POST['direccion']);
            
            // Verificar si la cédula ya existe
            if ($clienteModel->setCedCliente($cedula)->exists()) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'La cédula ' . $cedula . ' ya existe en el sistema'
                ]);
                exit;
            }
            
            // Guardar cliente
            if ($clienteModel->getGuardar($cedula, $nombre, $correo, $telefono, $direccion)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cliente guardado exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error al guardar el cliente en la base de datos'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
        break;

    case 'actualizar':
        header('Content-Type: application/json');
        
        // Verificar campos requeridos
        $required_fields = ['original_cedula', 'ced_cliente', 'nomcliente', 'correo', 'telefono', 'direccion'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Faltan los siguientes campos requeridos: ' . implode(', ', $missing_fields)
            ]);
            exit;
        }
        
        try {
            $cedula_original = trim($_POST['original_cedula']);
            $cedula = trim($_POST['ced_cliente']);
            $nombre = trim($_POST['nomcliente']);
            $correo = trim($_POST['correo']);
            $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
            $direccion = trim($_POST['direccion']);
            
            // Verificar que la cédula no haya cambiado
            if ($cedula_original !== $cedula) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'No se permite modificar la cédula del cliente'
                ]);
                exit;
            }
            
            // Actualizar cliente
            if ($clienteModel->getActualizar($cedula, $nombre, $correo, $telefono, $direccion)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cliente actualizado exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error al actualizar el cliente en la base de datos'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
        break;

    case 'eliminar':
        header('Content-Type: application/json');
        if (isset($_POST['cedula'])) {
            // Verificar si el cliente existe
            $cliente = $clienteModel->obtenerCliente($_POST['cedula']);
            if ($cliente) {
                // Usar el método público eliminarCliente
                $resultado = $clienteModel->eliminarCliente($_POST['cedula']);
                echo json_encode([
                    'success' => $resultado,
                    'message' => $resultado ? 'Cliente eliminado exitosamente' : 'Error al eliminar el cliente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta la cédula del cliente']);
        }
        break;

    case 'generarReporte':
        // Obtener parámetros de filtrado
        $filtros = [
            'fechaInicio' => $_GET['fechaInicio'] ?? null,
            'fechaFin' => $_GET['fechaFin'] ?? null,
            'nombreCliente' => $_GET['nombreCliente'] ?? null,
            'cedulaCliente' => $_GET['cedulaCliente'] ?? null,
            'tipoReporte' => $_GET['tipoReporte'] ?? 'lista',
            'mostrarTodos' => isset($_GET['mostrarTodos']) && ($_GET['mostrarTodos'] === '1' || $_GET['mostrarTodos'] === 'on')
        ];

        // Obtener todos los clientes si está marcado mostrarTodos
        if ($filtros['mostrarTodos']) {
            $clientes = $clienteModel->listar();
        } else {
            // Si no, aplicar filtros
            $clientes = $clienteModel->obtenerClientesFiltrados($filtros);
        }

        // Incluir la clase ReportePDF
        require_once 'App/Helpers/ReportePDF.php';
        
        // Crear instancia del reporte
        $pdf = new ReportePDF();
        $pdf->AliasNbPages();
        $pdf->AddPage('L'); // Orientación horizontal
        
        // Configurar fuente y colores
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0);
        
        // Título según el tipo de reporte
        $pdf->SetFont('Arial', 'B', 16);
        if ($filtros['tipoReporte'] === 'contactos') {
            $pdf->Cell(0, 10, 'Directorio de Contactos de Clientes', 0, 1, 'C');
            
            // Encabezados para directorio de contactos
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(211, 17, 17); // Rojo Natys
            $pdf->SetTextColor(255);
            $pdf->Cell(30, 10, 'Cédula', 1, 0, 'C', true);
            $pdf->Cell(70, 10, 'Nombre', 1, 0, 'C', true);
            $pdf->Cell(60, 10, 'Correo', 1, 0, 'C', true);
            $pdf->Cell(40, 10, 'Teléfono', 1, 1, 'C', true);
            
            // Datos para directorio de contactos
            $pdf->SetFont('Arial', '', 9);
            $pdf->SetTextColor(0);
            $fill = false;
            
            foreach ($clientes as $cliente) {
                $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
                $pdf->Cell(30, 10, $cliente['ced_cliente'], 1, 0, 'C', $fill);
                $pdf->Cell(70, 10, $cliente['nomcliente'], 1, 0, 'L', $fill);
                $pdf->Cell(60, 10, $cliente['correo'] ?? '-', 1, 0, 'L', $fill);
                $pdf->Cell(40, 10, $cliente['telefono'] ?? '-', 1, 1, 'C', $fill);
                $fill = !$fill;
            }
        } else {
            // Reporte de lista de clientes (por defecto)
            $pdf->Cell(0, 10, 'Listado de Clientes', 0, 1, 'C');
            
            // Encabezados para lista de clientes
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(211, 17, 17); // Rojo Natys
            $pdf->SetTextColor(255);
            $pdf->Cell(30, 10, 'Cédula', 1, 0, 'C', true);
            $pdf->Cell(70, 10, 'Nombre', 1, 0, 'C', true);
            $pdf->Cell(60, 10, 'Correo', 1, 0, 'C', true);
            $pdf->Cell(40, 10, 'Teléfono', 1, 0, 'C', true);
            $pdf->Cell(80, 10, 'Dirección', 1, 1, 'C', true);
            
            // Datos para lista de clientes
            $pdf->SetFont('Arial', '', 9);
            $pdf->SetTextColor(0);
            $fill = false;
            
            foreach ($clientes as $cliente) {
                $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
                $pdf->Cell(30, 10, $cliente['ced_cliente'], 1, 0, 'C', $fill);
                $pdf->Cell(70, 10, $cliente['nomcliente'], 1, 0, 'L', $fill);
                $pdf->Cell(60, 10, $cliente['correo'] ?? '-', 1, 0, 'L', $fill);
                $pdf->Cell(40, 10, $cliente['telefono'] ?? '-', 1, 0, 'C', $fill);
                $pdf->Cell(80, 10, $cliente['direccion'] ?? '-', 1, 1, 'L', $fill);
                $fill = !$fill;
            }
        }
        
        // Pie de página
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Generado el ' . date('d/m/Y H:i:s'), 0, 0, 'R');
        
        // Nombre del archivo según el tipo de reporte
        $nombreArchivo = $filtros['tipoReporte'] === 'contactos' 
            ? 'Directorio_Contactos_Clientes_' . date('Ymd_His') . '.pdf'
            : 'Listado_Clientes_' . date('Ymd_His') . '.pdf';
        
        // Salida del PDF
        $pdf->Output('I', $nombreArchivo);
        exit;
        
    case 'restaurar':
        header('Content-Type: application/json');
        if (isset($_POST['cedula'])) {
            // Obtener el cliente primero
            $cliente = $clienteModel->obtenerCliente($_POST['cedula']);
            if ($cliente) {
                // Usar el método público restaurar del modelo
                $resultado = $clienteModel->restaurarCliente($_POST['cedula']);
                
                echo json_encode([
                    'success' => $resultado,
                    'message' => $resultado ? 'Cliente restaurado exitosamente' : 'Error al restaurar el cliente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta la cédula']);
        }
        break;

    case 'listarEliminados':
        header('Content-Type: application/json');
        $clientes = $clienteModel->listarEliminados();
        echo json_encode(['data' => $clientes]);
        break;

    case 'detalles':
        header('Content-Type: application/json');
        if (isset($_GET['ced_cliente'])) {
            $cedula = $_GET['ced_cliente'];
            error_log("Buscando detalles para el cliente con cédula: " . $cedula);
            
            $cliente = $clienteModel->obtenerCliente($cedula);
            
            // Obtener el historial de pedidos del cliente
            $pedidos = [];
            if ($cliente) {
                error_log("Cliente encontrado: " . print_r($cliente, true));
                
                // Incluir el modelo de Pedido
                require_once 'App/models/pedido.php';
                $pedidoModel = new App\Natys\Models\Pedido();
                $pedidos = $pedidoModel->obtenerPorCliente($cedula);
                
                error_log("Pedidos encontrados: " . print_r($pedidos, true));
                
                // Formatear los datos de los pedidos
                $pedidos = array_map(function($pedido) {
                    $pedidoFormateado = [
                        'id_pedido' => $pedido['id_pedido'],
                        'fecha' => $pedido['fecha_formateada'],
                        'total' => number_format($pedido['total'], 2, ',', '.'),
                        'estado' => $pedido['estado_texto'],
                        'estado_codigo' => $pedido['estado'],
                        'cant_productos' => $pedido['cant_producto']
                    ];
                    
                    error_log("Pedido formateado: " . print_r($pedidoFormateado, true));
                    return $pedidoFormateado;
                }, $pedidos);
            }

            if ($cliente) {
                $response = [
                    'success' => true,
                    'data' => [
                        'cliente' => $cliente,
                        'pedidos' => $pedidos // Usamos los pedidos reales obtenidos del modelo
                    ]
                ];

                error_log("Respuesta JSON: " . json_encode($response, JSON_PRETTY_PRINT));
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
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
        break;

    case 'reporte_lista':
        $pdf = new ReportePDF();
        $pdf->setTitulo('LISTADO DE CLIENTES');
        $pdf->AddPage();
        
        $clientes = $clienteModel->listar();
        
        $pdf->agregarResumen([
            'Fecha de Generación' => date('d/m/Y'),
            'Total de Clientes' => count($clientes),
        ]);
        
        $headers = ['Cédula', 'Nombre', 'Correo', 'Teléfono', 'Dirección'];
        $widths = [25, 45, 50, 30, 40];
        $data = [];
        
        foreach ($clientes as $cliente) {
            $direccion = !empty($cliente['direccion']) ? 
                substr($cliente['direccion'], 0, 24) . '  ...' : 
                'Sin dirección';
            
            $data[] = [
                $cliente['ced_cliente'],
                $cliente['nomcliente'],
                $cliente['correo'],
                $cliente['telefono'],
                $direccion
            ];
        }
        
        $pdf->crearTabla($headers, $data, $widths);
        $pdf->Output('I', 'Listado_Clientes_' . date('Y-m-d') . '.pdf');
        break;
        
    case 'reporte_contactos':
        $pdf = new ReportePDF();
        $pdf->setTitulo('DIRECTORIO DE CONTACTOS');
        $pdf->setSubtitulo('Información de contacto de clientes');
        $pdf->AddPage();
        
        $clientes = $clienteModel->listar();
        
        $pdf->agregarResumen([
            'Total de Contactos' => count($clientes)
        ]);
        
        $headers = ['Nombre', 'Teléfono', 'Correo Electrónico'];
        $widths = [70, 40, 80];
        $data = [];
        
        foreach ($clientes as $cliente) {
            $data[] = [
                $cliente['nomcliente'],
                $cliente['telefono'],
                $cliente['correo']
            ];
        }
        
        $pdf->crearTabla($headers, $data, $widths);
        $pdf->Output('I', 'Directorio_Contactos_' . date('Y-m-d') . '.pdf');
        break;

    case 'listar':
    default:
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            
            // Determinar qué clientes cargar según el filtro
            $filtro = $_GET['filtro'] ?? 'activos';
            
            if ($filtro === 'inactivos') {
                $clientes = $clienteModel->listarEliminados();
            } else {
                $clientes = $clienteModel->listar();
            }
            
            echo json_encode([
                'success' => true,
                'data' => $clientes,
                'filtro' => $filtro
            ]);
            exit;
        } else {
            $mostrandoEliminados = isset($_GET['mostrarEliminados']) && $_GET['mostrarEliminados'] === 'true';
            
            if ($mostrandoEliminados) {
                $clientes = $clienteModel->listarEliminados();
            } else {
                $clientes = $clienteModel->listar();
            }

            // Incluir la vista con los datos
            include 'app/views/cliente/clienteView.php';
        }
        break;
}