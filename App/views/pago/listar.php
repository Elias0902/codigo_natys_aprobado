<?php
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Pagos</title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="Assets/css/listar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="mb-4" style="text-align: center;">Gestión de Pagos</h1>
        
        <div class="d-flex justify-content-between mb-3">
            <h2>Listado de Pagos</h2>
            <button type="button" class="btn btn-success" id="btnNuevoPago" data-bs-toggle="modal" data-bs-target="#modalSeleccionarPedido">
                <i class="fas fa-plus-circle me-2"></i>Registrar Pago
            </button>
            <button type="button" class="btn btn-warning" id="btnToggleEstado">
                <i class="fas fa-trash-restore me-2"></i>Mostrar Inactivos
            </button>
        </div>

        <div class="table-responsive" style="text-align:center;">
            <table id="pagos" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Banco</th>
                        <th>Referencia</th>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Datos cargados por DataTables -->
                </tbody>
            </table>
        </div>
        
        <br>
        <a href="index.php?url=home" class="btn btn-secondary">
            <i class="fas fa-home me-2"></i>Menú Principal
        </a>
    </div>

    <!-- Modal para seleccionar pedido -->
    <div class="modal fade" id="modalSeleccionarPedido" tabindex="-1" aria-labelledby="modalSeleccionarPedidoLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalSeleccionarPedidoLabel">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Pedidos Pendientes de Pago
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($pedidosPendientes)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID Pedido</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Productos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pedidosPendientes as $pedido): ?>
                                        <tr>
                                            <td>#<?= htmlspecialchars($pedido['id_pedido']) ?></td>
                                            <td><?= htmlspecialchars($pedido['nomcliente'] . ' (' . $pedido['ced_cliente'] . ')') ?></td>
                                            <td><?= date('d/m/Y', strtotime($pedido['fecha'])) ?></td>
                                            <td>$<?= number_format($pedido['total'], 2, ',', '.') ?></td>
                                            <td><?= $pedido['cant_producto'] ?> producto(s)</td>
                                            <td>
                                                <button class="btn btn-sm btn-success btn-seleccionar-pedido" 
                                                        data-id-pedido="<?= $pedido['id_pedido'] ?>"
                                                        data-total="<?= $pedido['total'] ?>"
                                                        data-cliente="<?= htmlspecialchars($pedido['nomcliente'] . ' (' . $pedido['ced_cliente'] . ')') ?>"
                                                        data-fecha="<?= date('d/m/Y', strtotime($pedido['fecha'])) ?>">
                                                    <i class="fas fa-check me-1"></i> Seleccionar
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i> No hay pedidos pendientes de pago en este momento.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para nuevo pago -->
    <div class="modal fade" id="modalNuevoPago" tabindex="-1" aria-labelledby="modalNuevoPagoLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalNuevoPagoLabel">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Procesar Pago
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Información del pedido -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Detalles del Pedido</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Pedido #:</strong> <span id="pedido-numero"></span></p>
                                    <p class="mb-1"><strong>Cliente:</strong> <span id="pedido-cliente"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Fecha:</strong> <span id="pedido-fecha"></span></p>
                                    <p class="mb-1"><strong>Total a pagar:</strong> $<span id="pedido-total">0.00</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de pago -->
                    <form id="formPago" class="needs-validation" novalidate>
                        <input type="hidden" name="id_pedido" id="id_pedido">
                        
                        <div class="mb-3">
                            <label for="banco" class="form-label">Banco <span class="text-danger">*</span></label>
                            <select class="form-select" id="banco" name="banco" required>
                                <option value="">Seleccione un banco</option>
                                <option value="Bancaribe">Bancaribe</option>
                                <option value="Banesco">Banesco</option>
                                <option value="Mercantil">Mercantil</option>
                                <option value="Venezuela">Banco de Venezuela</option>
                                <option value="BOD">BOD</option>
                                <option value="Otro">Otro</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor seleccione un banco.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="referencia" class="form-label">Número de Referencia <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="referencia" name="referencia" required 
                                   placeholder="Ingrese el número de referencia del pago">
                            <div class="invalid-feedback">
                                Por favor ingrese el número de referencia.
                            </div>
                        </div>

                        <div class="mb-3">
                         <label for="monto" class="form-label">Monto Pagado <span class="text-danger">*</span></label>
                        <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" id="monto" name="monto" 
                             step="0.01" min="0.01" required readonly>
                    <div class="invalid-feedback">
                     Por favor ingrese el monto pagado.
                        </div>
                    </div>
                </div>

                        <div class="mb-3">
                            <label for="cod_metodo" class="form-label">Método de Pago <span class="text-danger">*</span></label>
                            <select class="form-select" id="cod_metodo" name="cod_metodo" required>
                                <option value="" selected disabled>Seleccione un método de pago</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor seleccione un método de pago.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="fecha_pago" class="form-label">Fecha del Pago <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" required>
                            <div class="invalid-feedback">
                                Por favor ingrese la fecha del pago.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notas" class="form-label">Notas (Opcional)</label>
                            <textarea class="form-control" id="notas" name="notas" rows="2" 
                                     placeholder="Ingrese cualquier nota adicional sobre el pago"></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="button" class="btn btn-secondary me-md-2" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Guardar Pago
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar pago -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalEditarLabel">
                        <i class="fas fa-edit me-2"></i>
                        Editar Pago
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contenidoEditar">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando información del pago...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    <script src="../Natys/Assets/js/pago.js"></script>
</body>
</html>
<?php
$content = ob_get_clean();
include 'Assets/layouts/base.php';