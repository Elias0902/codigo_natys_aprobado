<?php
require_once 'App/Helpers/auth_check.php';
use App\Natys\Models\Producto;
use App\Natys\Helpers\FileUploadTrait;
use App\Natys\Helpers\RegexValidationTrait;
use App\Natys\Helpers\ReportePDF;

$productoModel = new Producto();

class ProductoControllerHelper {
    use FileUploadTrait, RegexValidationTrait;
}

$uploadHelper = new ProductoControllerHelper();
$validationHelper = new ProductoControllerHelper();

$action = $_REQUEST['action'] ?? 'listar';

switch ($action) {
    case 'formNuevo':
        echo '
        <div class="container-fluid p-0">
            <form id="formProducto" class="needs-validation" novalidate enctype="multipart/form-data">
                <input type="hidden" name="original_codigo" id="original_codigo" value="">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cod_producto" class="form-label">
                            <i class="fas fa-barcode me-1"></i>Código del Producto *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="cod_producto" 
                               name="cod_producto" 
                               placeholder="Ingrese el código del producto"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese el código del producto
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">
                            <i class="fas fa-box me-1"></i>Nombre del Producto *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="nombre" 
                               name="nombre" 
                               placeholder="Ingrese el nombre del producto"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese el nombre del producto
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="precio" class="form-label">
                            <i class="fas fa-tag me-1"></i>Precio *
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="precio" 
                               name="precio" 
                               placeholder="Ingrese el precio"
                               step="0.01"
                               min="0"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese un precio válido
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="unidad" class="form-label">
                            <i class="fas fa-balance-scale me-1"></i>Unidad de Medida *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="unidad" 
                               name="unidad" 
                               placeholder="Ej: kg, litros, unidades"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese la unidad de medida
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="imagen" class="form-label">
                        <i class="fas fa-image me-1"></i>Imagen del Producto
                    </label>
                    <input type="file" 
                           class="form-control" 
                           id="imagen" 
                           name="imagen" 
                           accept="image/*">
                    <div class="form-text">Formatos aceptados: JPG, PNG, GIF. Tamaño máximo: 3MB.</div>
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">
                        <i class="fas fa-align-left me-1"></i>Descripción
                    </label>
                    <textarea class="form-control" 
                              id="descripcion" 
                              name="descripcion" 
                              rows="3" 
                              placeholder="Ingrese una descripción del producto"></textarea>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Guardar Producto
                    </button>
                </div>
            </form>
        </div>';
        break;

    case 'formActualizar':
        if (isset($_GET['cod_producto'])) {
            $codigo = $_GET['cod_producto'];
            $datos = $productoModel->obtenerProducto($codigo);
            
            if ($datos) {
                echo '
                <div class="container-fluid p-0">
                    <form id="formProducto" class="needs-validation" novalidate enctype="multipart/form-data">
                        <input type="hidden" name="original_codigo" id="original_codigo" value="' . htmlspecialchars($datos['cod_producto']) . '">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cod_producto" class="form-label">
                                    <i class="fas fa-barcode me-1"></i>Código del Producto *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="cod_producto" 
                                       name="cod_producto" 
                                       value="' . htmlspecialchars($datos['cod_producto']) . '"
                                       readonly
                                       required>
                                <div class="invalid-feedback">
                                    Por favor ingrese el código del producto
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">
                                    <i class="fas fa-box me-1"></i>Nombre del Producto *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="' . htmlspecialchars($datos['nombre']) . '"
                                       required>
                                <div class="invalid-feedback">
                                    Por favor ingrese el nombre del producto
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="precio" class="form-label">
                                    <i class="fas fa-tag me-1"></i>Precio *
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="precio" 
                                       name="precio" 
                                       value="' . htmlspecialchars($datos['precio']) . '"
                                       step="0.01"
                                       min="0"
                                       required>
                                <div class="invalid-feedback">
                                    Por favor ingrese un precio válido
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="unidad" class="form-label">
                                    <i class="fas fa-balance-scale me-1"></i>Unidad de Medida *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="unidad" 
                                       name="unidad" 
                                       value="' . htmlspecialchars($datos['unidad']) . '"
                                       required>
                                <div class="invalid-feedback">
                                    Por favor ingrese la unidad de medida
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="imagen" class="form-label">
                                <i class="fas fa-image me-1"></i>Imagen del Producto
                            </label>
                            <input type="file" 
                                   class="form-control" 
                                   id="imagen" 
                                   name="imagen" 
                                   accept="image/*">
                            <div class="form-text">Deje vacío para mantener la imagen actual.</div>';
                
                if (!empty($datos['imagen_url'])) {
                    echo '<div class="mt-2">
                            <img src="' . htmlspecialchars($datos['imagen_url']) . '" height="100" class="img-thumbnail">
                          </div>';
                }
                
                echo '</div>
                        
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Descripción
                            </label>
                            <textarea class="form-control" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="3">' . htmlspecialchars($datos['descripcion'] ?? '') . '</textarea>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Actualizar Producto
                            </button>
                        </div>
                    </form>
                </div>';
            } else {
                echo '<div class="alert alert-danger">Producto no encontrado</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Falta el código del producto</div>';
        }
        break;

    case 'guardar':
        header('Content-Type: application/json');
        if (isset($_POST['cod_producto'], $_POST['nombre'], $_POST['precio'], $_POST['unidad'])) {
            
            if (!$validationHelper->validarCodigoProducto($_POST['cod_producto'])) {
                echo json_encode(['success' => false, 'message' => 'Formato de código de producto inválido. Solo se permiten letras, números, guiones y guiones bajos, máximo 20 caracteres.']);
                exit;
            }

            if (!$validationHelper->validarPrecio($_POST['precio'])) {
                echo json_encode(['success' => false, 'message' => 'Formato de precio inválido. Solo se permiten números y punto decimal.']);
                exit;
            }

            $imagen_url = '';
            if (!empty($_FILES['imagen']['name'])) {
                $resultado = $uploadHelper->uploadImageWithValidation($_FILES['imagen'], 'product');
                if ($resultado['success']) {
                    $imagen_url = $resultado['ruta'];
                } else {
                    echo json_encode(['success' => false, 'message' => $resultado['message']]);
                    exit;
                }
            }

            // USANDO EL NUEVO MÉTODO ENCAPSULADO
            if ($productoModel->verificarExistenciaProducto($_POST['cod_producto'])) {
                echo json_encode(['success' => false, 'message' => 'El código de producto ya existe']);
                exit;
            }

            $resultado = $productoModel->guardarProducto(
                $_POST['cod_producto'],
                $_POST['nombre'],
                $_POST['precio'],
                $_POST['unidad'],
                $imagen_url,
                $_POST['descripcion'] ?? ''
            );

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto guardado exitosamente. Recuerde que el producto estará inactivo hasta que se registre un movimiento de entrada.'
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

            if (!$validationHelper->validarPrecio($_POST['precio'])) {
                echo json_encode(['success' => false, 'message' => 'Formato de precio inválido. Solo se permiten números y punto decimal.']);
                exit;
            }

            $productoActual = $productoModel->obtenerProducto($_POST['cod_producto']);
            $imagen_url = $productoActual['imagen_url'] ?? '';

            if (!empty($_FILES['imagen']['name'])) {
                $resultado = $uploadHelper->uploadImageWithValidation($_FILES['imagen'], 'product');
                if ($resultado['success']) {
                    $imagen_url = $resultado['ruta'];
                    
                    if (!empty($productoActual['imagen_url'])) {
                        $uploadHelper->deleteFile($productoActual['imagen_url']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => $resultado['message']]);
                    exit;
                }
            }

            // USANDO EL NUEVO MÉTODO ENCAPSULADO
            $resultado = $productoModel->actualizarProducto(
                $_POST['cod_producto'],
                $_POST['nombre'],
                $_POST['precio'],
                $_POST['unidad'],
                $imagen_url,
                $_POST['descripcion'] ?? ''
            );

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto actualizado exitosamente'
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
            // USANDO EL NUEVO MÉTODO ENCAPSULADO
            $resultado = $productoModel->eliminarProducto($_POST['codigo']);
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Stock del producto vaciado exitosamente' : 'Error al vaciar el stock del producto'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta el código del producto']);
        }
        break;

    case 'listarEliminados':
        header('Content-Type: application/json');
        try {
            // USANDO EL NUEVO MÉTODO ENCAPSULADO
            $productos = $productoModel->listarProductosEliminados();
            
            $productos = array_map(function($producto) {
                $producto['stock'] = (float)$producto['stock'];
                return $producto;
            }, $productos);
            
            echo json_encode([
                'success' => true,
                'data' => $productos
            ]);
        } catch (Exception $e) {
            error_log('Error en listarEliminados: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener productos inactivos: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        break;

    case 'listarAjax':
        header('Content-Type: application/json');
        $search = $_GET['search']['value'] ?? '';
        $draw = intval($_GET['draw'] ?? 1);
        $start = intval($_GET['start'] ?? 0);
        $length = intval($_GET['length'] ?? 10);
        
        // USANDO LOS NUEVOS MÉTODOS ENCAPSULADOS
        $totalRecords = $productoModel->contarProductosTotales();
        $totalFiltered = $totalRecords;
        
        $productos = $productoModel->listarProductosPaginados($search, $start, $length);
        
        if (!empty($search)) {
            $totalFiltered = $productoModel->contarProductosFiltrados($search);
        }
        
        $data = [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $productos
        ];
        
        echo json_encode($data);
        break;

    case 'listar':
    default:
        $searchTerm = $_GET['search'] ?? '';
        $minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? floatval($_GET['min_price']) : null;
        $maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? floatval($_GET['max_price']) : null;
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            // USANDO EL NUEVO MÉTODO ENCAPSULADO
            $productos = $productoModel->listarProductos($searchTerm, $minPrice, $maxPrice);
            echo json_encode(['data' => $productos]);
        } else {
            include 'App/views/producto/productoView.php';
        }
        break;

    case 'inventario':
        $pdf = new ReportePDF();
        $pdf->setTitulo('REPORTE DE INVENTARIO');
        $pdf->setSubtitulo('Listado de productos con stock disponible');
        $pdf->AddPage();
        
        // USANDO EL NUEVO MÉTODO ENCAPSULADO
        $productos = $productoModel->listarProductos();
        
        $totalProductos = count($productos);
        $valorTotal = 0;
        $stockTotal = 0;
        
        foreach ($productos as $producto) {
            $valorTotal += $producto['precio'] * $producto['stock'];
            $stockTotal += $producto['stock'];
        }
        
        $pdf->agregarResumen([
            'Total de Productos' => $totalProductos,
            'Stock Total' => number_format($stockTotal, 2),
            'Valor Total del Inventario' => '$' . number_format($valorTotal, 2)
        ]);
        
        $headers = ['Código', 'Producto', 'Precio', 'Unidad', 'Stock', 'Valor Total'];
        $widths = [30, 60, 25, 25, 20, 30];
        $data = [];
        
        foreach ($productos as $producto) {
            $valorProducto = $producto['precio'] * $producto['stock'];
            $data[] = [
                $producto['cod_producto'],
                $producto['nombre'],
                '$' . number_format($producto['precio'], 2),
                $producto['unidad'],
                number_format($producto['stock'], 2),
                '$' . number_format($valorProducto, 2)
            ];
        }
        
        $pdf->crearTabla($headers, $data, $widths);
        
        $pdf->Output('I', 'Reporte_Inventario_' . date('Y-m-d') . '.pdf');
        break;
        
    case 'productos':
        $pdf = new ReportePDF();
        $pdf->setTitulo('LISTADO DE PRODUCTOS');
        $pdf->setSubtitulo('Catálogo completo de productos');
        $pdf->AddPage();
        
        // USANDO EL NUEVO MÉTODO ENCAPSULADO
        $productos = $productoModel->listarProductos();
        
        $pdf->agregarResumen([
            'Total de Productos' => count($productos),
            'Fecha de Generación' => date('d/m/Y')
        ]);
        
        $headers = ['Código', 'Nombre', 'Precio', 'Unidad', 'Descripción'];
        $widths = [30, 50, 25, 25, 60];
        $data = [];
        
        foreach ($productos as $producto) {
            $descripcion = !empty($producto['descripcion']) ? 
                substr($producto['descripcion'], 0, 40) . '...' : 
                'Sin descripción';
            
            $data[] = [
                $producto['cod_producto'],
                $producto['nombre'],
                '$' . number_format($producto['precio'], 2),
                $producto['unidad'],
                $descripcion
            ];
        }
        
        $pdf->crearTabla($headers, $data, $widths);
        
        $pdf->Output('I', 'Listado_Productos_' . date('Y-m-d') . '.pdf');
        break;
        
    case 'bajo_stock':
        $pdf = new ReportePDF();
        $pdf->setTitulo('REPORTE DE PRODUCTOS CON BAJO STOCK');
        $pdf->setSubtitulo('Productos que requieren reabastecimiento');
        $pdf->AddPage();
        
        // USANDO EL NUEVO MÉTODO ENCAPSULADO
        $productos = $productoModel->listarProductos();
        
        $productosBajoStock = array_filter($productos, function($p) {
            return $p['stock'] < 10;
        });
        
        $pdf->agregarResumen([
            'Productos con Bajo Stock' => count($productosBajoStock),
            'Nivel de Alerta' => 'Menos de 10 unidades'
        ]);
        
        if (count($productosBajoStock) > 0) {
            $headers = ['Código', 'Producto', 'Stock Actual', 'Precio', 'Estado'];
            $widths = [30, 70, 30, 30, 30];
            $data = [];
            
            foreach ($productosBajoStock as $producto) {
                $estado = $producto['stock'] == 0 ? 'AGOTADO' : 'BAJO';
                $data[] = [
                    $producto['cod_producto'],
                    $producto['nombre'],
                    number_format($producto['stock'], 2),
                    '$' . number_format($producto['precio'], 2),
                    $estado
                ];
            }
            
            $pdf->crearTabla($headers, $data, $widths);
        } else {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 10, 'No hay productos con bajo stock', 0, 1, 'C');
        }
        
        $pdf->Output('I', 'Productos_Bajo_Stock_' . date('Y-m-d') . '.pdf');
        break;

    case 'fuera_stock':
        $pdf = new ReportePDF();
        $pdf->setTitulo('REPORTE DE PRODUCTOS FUERA DE STOCK');
        $pdf->setSubtitulo('Productos inactivos sin stock disponible');
        $pdf->AddPage();

        // USANDO EL NUEVO MÉTODO ENCAPSULADO
        $productos = $productoModel->listarProductosEliminados();

        $productosFueraStock = array_filter($productos, function($p) {
            return $p['estado'] == 0 && ($p['stock'] == 0 || $p['stock'] == null);
        });
        $totalProductos = count($productosFueraStock);
        $valorPotencial = 0;
        
        foreach ($productosFueraStock as $producto) {
            $valorPotencial += $producto['precio'] * 10;
        }
        
        $pdf->agregarResumen([
            'Productos Fuera de Stock' => $totalProductos,
            'Valor Potencial Perdido' => '$' . number_format($valorPotencial, 2),
            'Fecha de Generación' => date('d/m/Y H:i:s')
        ]);
        
        if (count($productosFueraStock) > 0) {
            $headers = ['Código', 'Producto', 'Precio Unitario', 'Unidad', 'Stock', 'Valor Potencial'];
            $widths = [25, 60, 30, 20, 20, 35];
            $data = [];
            
            foreach ($productosFueraStock as $producto) {
                $valorPotencialProducto = $producto['precio'] * 10;
                $data[] = [
                    $producto['cod_producto'],
                    $producto['nombre'],
                    '$' . number_format($producto['precio'], 2),
                    $producto['unidad'],
                    '0.00',
                    '$' . number_format($valorPotencialProducto, 2)
                ];
            }

            $pdf->crearTabla($headers, $data, $widths);

            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 10, 'Nota: El valor potencial se calcula considerando 10 unidades por producto.', 0, 1);
        } else {
            $pdf->SetFont('Arial', 'I', 12);
            $pdf->Cell(0, 10, 'No hay productos fuera de stock', 0, 1, 'C');
        }
        
        $pdf->Output('I', 'Productos_Fuera_Stock_' . date('Y-m-d') . '.pdf');
        break;

    case 'restaurar':
        header('Content-Type: application/json');
        if (isset($_POST['codigo'])) {
            // USANDO EL NUEVO MÉTODO ENCAPSULADO
            $resultado = $productoModel->restaurarProducto($_POST['codigo']);
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Producto restaurado exitosamente' : 'Error al restaurar el producto'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta el código del producto']);
        }
        break;

    case 'obtenerStock':
        header('Content-Type: application/json');
        if (isset($_GET['cod_producto'])) {
            // USANDO EL NUEVO MÉTODO ENCAPSULADO
            $stock = $productoModel->obtenerStockProducto($_GET['cod_producto']);
            echo json_encode([
                'success' => true,
                'stock' => $stock
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta el código del producto']);
        }
        break;
}