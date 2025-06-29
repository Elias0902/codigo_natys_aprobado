<?php
use App\Natys\models\Pedido;
use App\Natys\models\Cliente;
use App\Natys\models\Producto;

$pedido = new Pedido();
$cliente = new Cliente();
$producto = new Producto();

$action = $_REQUEST['action'] ?? 'listar';

switch ($action) {
    case 'formNuevo':
        $clientes = $cliente->listar();
        $productos = $producto->listar();
        
        echo generarFormularioPedido($clientes, $productos);
        break;

    case 'formEditar':
        header('Content-Type: application/json');
        if (isset($_GET['id_pedido'])) {
            $id_pedido = $_GET['id_pedido'];
            $datos = $pedido->obtenerPedido($id_pedido);
            $detalles = $pedido->obtenerDetalles($id_pedido);
            
            if ($datos) {
                $detalles = array_map(function($detalle) {
                    return [
                        'cod_producto' => $detalle['cod_producto'],
                        'producto' => $detalle['producto'],
                        'precio' => (float)$detalle['precio'],
                        'cantidad' => (int)$detalle['cantidad'],
                        'subtotal' => (float)$detalle['subtotal']
                    ];
                }, $detalles);

                echo json_encode([
                    'success' => true,
                    'message' => 'Datos del pedido cargados',
                    'data' => [
                        'pedido' => $datos,
                        'detalles' => $detalles
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Pedido no encontrado'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Falta el ID del pedido'
            ]);
        }
        exit();
        break;

    case 'guardar':
        header('Content-Type: application/json');
        try {
            if (!isset($_POST['fecha'], $_POST['cliente'], $_POST['detalles'], $_POST['total'])) {
                throw new Exception('Faltan datos requeridos: fecha, cliente, detalles o total');
            }

            $detallesArray = json_decode($_POST['detalles'], true);
            if (!is_array($detallesArray) || empty($detallesArray)) {
                throw new Exception('Debe agregar al menos un producto');
            }

            $pedido->fecha = $_POST['fecha'];
            $pedido->total = (float)$_POST['total'];
            $pedido->cant_producto = array_sum(array_column($detallesArray, 'cantidad'));
            $pedido->ced_cliente = $_POST['cliente'];

            if ($pedido->guardar($detallesArray)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Pedido guardado exitosamente',
                    'data' => [
                        'id_pedido' => $pedido->id_pedido
                    ]
                ]);
            } else {
                throw new Exception('Error al guardar el pedido');
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        break;

    case 'actualizar':
        header('Content-Type: application/json');
        try {
            if (!isset($_POST['id_pedido'], $_POST['fecha'], $_POST['cliente'], $_POST['detalles'], $_POST['total'])) {
                throw new Exception('Faltan datos requeridos');
            }

            $pedidoExistente = $pedido->obtenerPedido($_POST['id_pedido']);
            if (!$pedidoExistente) {
                throw new Exception('Pedido no encontrado');
            }

            $detallesArray = json_decode($_POST['detalles'], true);
            if (!is_array($detallesArray) || empty($detallesArray)) {
                throw new Exception('Debe agregar al menos un producto');
            }

            $pedido->id_pedido = $_POST['id_pedido'];
            $pedido->fecha = $_POST['fecha'];
            $pedido->total = (float)$_POST['total'];
            $pedido->cant_producto = array_sum(array_column($detallesArray, 'cantidad'));
            $pedido->ced_cliente = $_POST['cliente'];

            if ($pedido->actualizar($detallesArray)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Pedido actualizado exitosamente'
                ]);
            } else {
                throw new Exception('Error al actualizar el pedido');
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        break;

    case 'eliminar':
        header('Content-Type: application/json');
        if (isset($_POST['id_pedido'])) {
            $pedido->id_pedido = $_POST['id_pedido'];
            $resultado = $pedido->eliminar();
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Eliminado exitosamente' : 'Error al eliminar'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta el ID del pedido']);
        }
        break;

    case 'restaurar':
        header('Content-Type: application/json');
        if (isset($_POST['id_pedido'])) {
            $pedido->id_pedido = $_POST['id_pedido'];
            $resultado = $pedido->restaurar();
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Restaurado exitosamente' : 'Error al restaurar'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta el ID del pedido']);
        }
        break;

    case 'detalle':
        if (isset($_GET['id_pedido'])) {
            $detalles = $pedido->obtenerDetalles($_GET['id_pedido']);
            include 'app/views/pedido/detalle.php';
        }
        break;

    case 'listarEliminados':
        header('Content-Type: application/json');
        $pedidos = $pedido->listarEliminados();
        echo json_encode(['data' => $pedidos]);
        exit;
        break;

    case 'listar':
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            $pedidos = $pedido->listar();
            echo json_encode(['data' => $pedidos]);
        } else {
            $pedidos = $pedido->listar();
            include 'app/views/pedido/listar.php';
        }
        break;
}

function generarFormularioPedido($clientes, $productos) {
    ob_start();
    ?>
    <div class="modal-body">
        <form id="formPedido" method="post">
            <input type="hidden" id="id_pedido" name="id_pedido" value="">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="fechaPedido" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="fechaPedido" name="fecha" required>
                </div>
                
                <div class="col-md-6">
                    <label for="clienteSelect" class="form-label">Cliente</label>
<select class="form-select" id="ClienteSelect" style="width: 60%;">
    <option value="">Seleccione un Cliente</option>
    <?php foreach ($clientes as $cliente): ?>
        <option value="<?= htmlspecialchars($cliente['ced_cliente']) ?>" 
                data-nombre="<?= htmlspecialchars($cliente['nomcliente']) ?>">
            <?= htmlspecialchars($cliente['nomcliente']) ?>
        </option>
    <?php endforeach; ?>
</select>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Productos</label>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tablaProductos">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="detallesProductos">
                                <!-- Detalles de productos se agregarán aquí -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td colspan="2">
                                        <input type="text" class="form-control" id="total" name="total" readonly>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-2">
                        <select class="form-select" id="productoSelect" style="width: 60%;">
                            <option value="">Seleccione un producto</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?= htmlspecialchars($producto['cod_producto']) ?>" 
                                        data-precio="<?= (float)$producto['precio'] ?>"
                                        data-nombre="<?= htmlspecialchars($producto['nombre']) ?>">
                                    <?= htmlspecialchars($producto['nombre']) ?> - $<?= number_format($producto['precio'], 2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <input type="number" class="form-control" id="cantidadProducto" min="1" value="1" style="width: 15%;">
                        
                        <button type="button" class="btn btn-primary" id="btnAgregarProducto">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                    </div>
                </div>
            </div>
            
            <input type="hidden" id="detalles" name="detalles" value="">
        </form>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="formPedido" class="btn btn-primary">Guardar</button>
    </div>

    <script>
    $(document).ready(function() {
        const detalles = [];
        
        $('#btnAgregarProducto').click(function() {
            const productoSelect = $('#productoSelect');
            const productoOption = productoSelect.find('option:selected');
            
            if (!productoOption.val()) {
                toastr.error('Seleccione un producto válido');
                return;
            }

            const precio = parseFloat(productoOption.data('precio')) || 0;
            const cantidad = parseInt($('#cantidadProducto').val()) || 0;
            
            if (cantidad < 1) {
                toastr.error('La cantidad debe ser al menos 1');
                return;
            }

            const subtotal = precio * cantidad;
            
            detalles.push({
                cod_producto: productoOption.val(),
                producto: productoOption.data('nombre'),
                precio: precio,
                cantidad: cantidad,
                subtotal: subtotal
            });
            
            actualizarTablaProductos();
            
            productoSelect.val('').trigger('change');
            $('#cantidadProducto').val(1);
        });
        
        function actualizarTablaProductos() {
            const tbody = $('#detallesProductos');
            tbody.empty();
            
            let total = 0;
            
            detalles.forEach((detalle, index) => {
                const precio = parseFloat(detalle.precio) || 0;
                const cantidad = parseInt(detalle.cantidad) || 0;
                const subtotal = parseFloat(detalle.subtotal) || 0;
                
                total += subtotal;
                
                tbody.append(`
                    <tr>
                        <td>${detalle.producto}</td>
                        <td>$${precio.toFixed(2)}</td>
                        <td>${cantidad}</td>
                        <td>$${subtotal.toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger btnEliminarProducto" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
            
            $('#total').val(total.toFixed(2));
            $('#detalles').val(JSON.stringify(detalles));
        }
        
        $(document).on('click', '.btnEliminarProducto', function() {
            const index = $(this).data('index');
            detalles.splice(index, 1);
            actualizarTablaProductos();
        });
    });
    </script>
    <?php
    return ob_get_clean();
}