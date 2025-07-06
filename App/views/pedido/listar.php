<?php
ob_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Pedidos</title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="Assets/css/listar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="mb-4" style="text-align: center;">Gestión de Pedidos</h1>

        <div class="d-flex justify-content-between mb-3">
            <div>
                <button type="button" class="btn btn-success me-2" id="btnNuevoPedido">
                    <i class="fas fa-plus-circle me-2"></i>Nuevo Pedido
                </button>
                <button type="button" class="btn btn-warning" id="btnVerPendientes">
                    <i class="fas fa-clock me-2"></i>Ver Pendientes de Pago
                </button>
            </div>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-success filter-btn active" data-estado="1">Pagados</button>
                <button type="button" class="btn btn-secondary filter-btn" data-estado="all">Todos</button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tablaPedidos" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Productos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Los datos se cargarán dinámicamente via AJAX -->
                </tbody>
            </table>
        </div>

        <br>
        <a href="index.php?url=home" class="btn btn-secondary">
            <i class="fas fa-home me-2"></i>Menú Principal
        </a>
    </div>

    <!-- Modal para detalles del pedido -->
    <div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Detalle del Pedido #<span id="pedidoId"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Cliente:</strong> <span id="detalleCliente"></span></p>
                            <p><strong>Fecha:</strong> <span id="detalleFecha"></span></p>
                            <p><strong>Teléfono:</strong> <span id="detalleTelefono"></span></p>
                            <p><strong>Dirección:</strong> <span id="detalleDireccion"></span></p>
                        </div>
                        <div class="col-md-6 text-end">
                            <p><strong>Total:</strong> $<span id="detalleTotal">0.00</span></p>
                            <p><strong>Estado:</strong> <span id="detalleEstado" class="badge"></span></p>
                            <p><strong>Método de pago:</strong> <span id="detalleMetodoPago"></span></p>
                            <p><strong>Banco:</strong> <span id="detalleBanco"></span></p>
                            <p><strong>Referencia:</strong> <span id="detalleReferencia"></span></p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-end">Precio Unitario</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="detalleProductos">
                                <!-- Los productos se cargarán aquí -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end"><strong>$<span id="detalleTotalFinal">0.00</span></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para formulario de pedido -->
    <div class="modal fade" id="modalFormulario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalFormularioTitulo">Nuevo Pedido</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formPedido">
                        <input type="hidden" id="id_pedido" name="id_pedido">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="ced_cliente" class="form-label">Cliente *</label>
                                <select class="form-select" id="ced_cliente" name="ced_cliente" required>
                                    <option value="">Seleccione un cliente</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?php echo $cliente['ced_cliente']; ?>">
                                            <?php echo htmlspecialchars($cliente['nomcliente'] . ' - ' . $cliente['ced_cliente']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Productos *</label>
                            <div class="table-responsive">
                                <table class="table table-sm" id="tablaProductos">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Precio</th>
                                            <th>Cantidad</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="productos-seleccionados">
                                        <!-- Productos se agregarán aquí -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                            <td id="total-pedido-form">0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="btnAgregarProducto">
                                <i class="fas fa-plus me-1"></i>Agregar Producto
                            </button>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Guardar Pedido
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para pedidos pendientes -->
    <div class="modal fade" id="modalPendientes" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Pedidos Pendientes de Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="tablaPendientes" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Productos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar producto -->
    <div class="modal fade" id="modalAgregarProducto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Agregar Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formAgregarProducto">
                        <div class="mb-3">
                            <label for="cod_producto" class="form-label">Producto *</label>
                            <select class="form-select" id="cod_producto" name="cod_producto" required>
                                <option value="">Seleccione un producto</option>
                                <?php foreach ($productos as $producto): ?>
                                    <option value="<?php echo $producto['cod_producto']; ?>" 
                                            data-precio="<?php echo $producto['precio']; ?>"
                                            data-unidad="<?php echo $producto['unidad']; ?>">
                                        <?php echo htmlspecialchars($producto['nombre'] . ' - $' . number_format($producto['precio'], 2) . ' (' . $producto['unidad'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad *</label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" value="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio Unitario *</label>
                            <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0.01" required readonly>
                        </div>
                        <div class="mb-3">
                            <label for="subtotal" class="form-label">Subtotal</label>
                            <input type="text" class="form-control" id="subtotal" name="subtotal" readonly>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmarProducto">Agregar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="../Natys/Assets/js/pedido.js"></script>


</body>
</html>

<?php
$content = ob_get_clean();
include 'Assets/layouts/base.php';
