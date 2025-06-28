<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras</title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="Assets/css/listar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="mb-4" style="text-align: center;">Carrito de Compras</h1>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Productos en tu carrito</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="pedidos" class="table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Precio</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los datos se cargarán mediante JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Resumen de Compra</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Productos:</span>
                            <span id="cantidad-productos">0</span>
                        </div>
                        
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Código de descuento">
                            <button class="btn btn-outline-secondary" type="button">Aplicar</button>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Subtotal:</strong>
                            <strong id="subtotal">$0.00</strong>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong id="total">$0.00</strong>
                        </div>
                        
                        <button class="btn btn-primary w-100 mb-2" id="btnPagar">
                            <i class="fas fa-credit-card me-2"></i>Proceder al Pago
                        </button>
                        
                        <button class="btn btn-outline-secondary w-100" id="btnSeguirComprando">
                            <i class="fas fa-arrow-left me-2"></i>Seguir Comprando
                        </button>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Métodos de Pago</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="metodoPago" id="transferencia" checked>
                            <label class="form-check-label" for="transferencia">
                                <i class="fas fa-university me-2"></i>Transferencia Bancaria
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="metodoPago" id="efectivo">
                            <label class="form-check-label" for="efectivo">
                                <i class="fas fa-money-bill-wave me-2"></i>Efectivo
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="metodoPago" id="tarjeta">
                            <label class="form-check-label" for="tarjeta">
                                <i class="fas fa-credit-card me-2"></i>Tarjeta de Crédito
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-between">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home me-2"></i>Menú Principal
            </a>
            
            <button type="button" class="btn btn-warning" id="btnToggleEstado">
                <i class="fas fa-history me-2"></i>Ver Historial de Pedidos
            </button>
        </div>
    </div>

    <!-- Modales (se mantienen igual) -->
    <!-- ... -->

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="Assets/js/pedido.js"></script>
</body>
</html>