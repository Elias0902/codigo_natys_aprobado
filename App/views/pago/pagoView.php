<?php
ob_start();
?>
<!DOCTYPE html>
<html lang="es" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pagos - Natys</title>
    <link rel="icon" href="/Natys/Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="/Natys/Assets/css/themes.css">
    <style>
        body {
            padding-bottom: 70px;
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        @media (max-width: 768px) {
            .table-responsive { 
                display: none; 
            }
            .pagos-mobile-list { 
                display: block; 
            }
            .filtros-desktop {
                display: none !important;
            }
            .filtros-mobile {
                display: flex !important;
            }
        }
        
        @media (min-width: 769px) {
            .pagos-mobile-list { 
                display: none; 
            }
            .filtros-mobile {
                display: none !important;
            }
            .filtros-desktop {
                display: flex !important;
            }
        }

        .pago-card {
            border: 1px solid var(--border-color) !important;
            border-radius: 8px;
            margin-bottom: 1rem;
            box-shadow: var(--shadow);
            padding: 1rem;
            background: var(--card-bg) !important;
            color: var(--text-color) !important;
            transition: all 0.3s ease;
        }

        .pago-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .pago-card .badge { 
            font-size: 0.9em; 
        }

        .pago-card .btn-group { 
            margin-top: 0.5rem; 
        }

        .pago-card .text-muted { 
            color: var(--text-muted) !important; 
        }

        .table {
            color: var(--text-color);
            --bs-table-bg: transparent;
        }

        .table th, .table td {
            border-color: var(--border-color);
            background-color: var(--card-bg);
        }

        .table-responsive {
            overflow-x: auto;
        }

        /* ESTILOS MEJORADOS PARA TOASTS - CON CIERRE AUTOMÁTICO */
        .toast {
            opacity: 1 !important;
            visibility: visible !important;
            background-color: transparent !important;
            border: none !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        }

        .toast-success {
            background: #28a745 !important;
        }

        .toast-error {
            background: #dc3545 !important;
        }

        .toast-warning {
            background: #ffc107 !important;
            color: black !important;
        }

        .toast-info {
            background: #17a2b8 !important;
        }

        /* Asegurar que los toasts se muestren correctamente */
        #toast-container > div {
            opacity: 1 !important;
            -ms-filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=100) !important;
            filter: alpha(opacity=100) !important;
        }

        /* Animación de desvanecimiento al cerrar */
        .toast.fadeOut {
            opacity: 0 !important;
            transition: opacity 0.5s ease-in-out !important;
        }

        .form-control.is-valid, .was-validated .form-control:valid {
            padding-right: 2.5rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23198754' viewBox='0 0 16 16'%3E%3Cpath d='M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            border-color: #198754;
        }

        .form-control.is-invalid, .was-validated .form-control:invalid {
            padding-right: 2.5rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23d31111' viewBox='0 0 16 16'%3E%3Cpath d='M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            border-color: #d31111;
        }

        .valid-feedback, .invalid-feedback {
            display: none;
            margin-top: 0.25rem;
            font-size: 0.875em;
        }

        .valid-feedback {
            color: #198754;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .is-valid + .valid-feedback,
        .was-validated :valid ~ .valid-feedback {
            display: flex;
        }

        .is-invalid + .invalid-feedback,
        .was-validated :invalid ~ .invalid-feedback {
            display: block;
        }

        /* Campos que se ocultan para efectivo */
        .banco-field, .referencia-field {
            transition: all 0.3s ease;
        }

        .btn-actions {
            margin: 0 2px;
            transition: all 0.3s ease;
            background-color: #d31111 !important;
            border-color: #d31111 !important;
            color: white !important;
        }

        .btn-actions:hover {
            transform: scale(1.1);
            background-color: #b30e0e !important;
            border-color: #b30e0e !important;
            color: white !important;
        }
        
        .btn, .btn-success, .btn-primary, .btn-warning, .btn-danger, .btn-info, .btn-secondary, .btn-outline-primary {
            background-color: #d31111 !important;
            border-color: #d31111 !important;
            color: white !important;
            transition: all 0.3s ease;
        }
        
        .btn:hover, .btn-success:hover, .btn-primary:hover, .btn-warning:hover, 
        .btn-danger:hover, .btn-info:hover, .btn-secondary:hover, .btn-outline-primary:hover {
            background-color: #b30e0e !important;
            border-color: #b30e0e !important;
            color: white !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .modal-content {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            color: #212529;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .modal-header {
            background: linear-gradient(135deg, #d31111 0%, #c0392b 100%);
            color: white;
            border-bottom: none;
            border-radius: 10px 10px 0 0;
        }

        .modal-body, .modal-footer {
            background-color: #ffffff;
            color: #212529;
        }

        .modal-footer {
            border-top: 1px solid #dee2e6;
        }

        .modal-title, .modal-body, .modal-footer {
            color: #212529 !important;
        }

        .form-label {
            color: var(--text-color);
            font-weight: 600;
        }

        .form-control, .form-select, .form-control:focus, .form-select:focus {
            background-color: var(--input-bg);
            color: var(--input-text);
            border-color: var(--border-color);
            border-radius: 8px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(204, 29, 29, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            color: white;
        }

        .btn-primary:hover, .btn-primary:focus {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #8c0b0b 100%);
            color: white;
            box-shadow: var(--shadow);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
            border: none !important;
            color: white !important;
            box-shadow: none !important;
        }

        .btn-success:hover, .btn-success:focus {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #8c0b0b 100%) !important;
            color: white !important;
            box-shadow: var(--shadow) !important;
        }
        
        .pago-card .btn-group .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        
        .pago-card .btn-group .btn:hover {
            background-color: var(--primary-dark);
            color: white;
        }

        /* Estilos para el dropdown de reportes */


        .dropdown-menu {
            background-color: white;
            border: 1px solid #d31111;
            box-shadow: 0 4px 8px rgba(211, 17, 17, 0.2);
        }
        .dropdown-item {
            color: #333;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }
        .dropdown-item:hover {
            background-color: #f8d7da;
            color: #d31111;
        }
        .dropdown-divider {
            border-color: #d31111;
        }
        /* Filtros móviles */
        .filtros-mobile {
            display: none;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .filtro-btn-mobile {
            width: 100%;
            text-align: left;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            background: var(--card-bg);
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .filtro-btn-mobile.active {
            background: linear-gradient(135deg, #d31111 0%, #c0392b 100%);
            color: white;
            border-color: #d31111;
        }

        .filtro-btn-mobile:hover {
            background: linear-gradient(135deg, #d31111 0%, #c0392b 100%);
            color: white;
        }

        @media (max-width: 768px) {
            .filtros-pago {
                flex-direction: column;
                width: 100%;
            }
            
            .filtros-pago .btn {
                border-radius: 0.375rem !important;
                margin-bottom: 0.5rem;
                width: 100%;
            }
            
            .filtros-pago .btn:last-child {
                margin-bottom: 0;
            }
        }

        /* Animaciones suaves */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4 fade-in">
        <h1 class="mb-4" style="text-align: center;"><i class="fas fa-credit-card p-4"></i>Gestión de Pagos</h1>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2 mb-2 mb-md-0">
                <button type="button" class="btn btn-danger pulse" id="btnNuevoPago" data-bs-toggle="modal" data-bs-target="#modalSeleccionarPedido" style="background: linear-gradient(135deg, #d31111 0%, #c0392b 100%) !important; border: none !important; box-shadow: none !important;">
                    <i class="fas fa-plus-circle me-2"></i>Registrar Pago
                </button>
                
                <!-- Dropdown para Reportes -->
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownReportes" data-bs-toggle="dropdown" aria-expanded="false" style="background: linear-gradient(135deg, #d31111 0%, #c0392b 100%) !important; border: none !important; box-shadow: none !important;">
                        <i class="fas fa-file-pdf me-2"></i>Reportes
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownReportes" style="z-index: 1060;">
                        <li>
                            <a class="dropdown-item" href="index.php?url=pago&action=reporte_lista" data-action="reporte_lista" target="_blank">
                                <i class="fas fa-list me-2"></i>Listado General
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="index.php?url=pago&action=reporte_efectivo" data-action="reporte_efectivo" target="_blank">
                                <i class="fas fa-money-bill-wave me-2"></i>Pagos en Efectivo
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="index.php?url=pago&action=reporte_transferencias" data-action="reporte_transferencias" target="_blank">
                                <i class="fas fa-exchange-alt me-2"></i>Transferencias
                            </a>
                        </li>
                     <li><hr class="dropdown-divider"></li>
                     <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalComprobante"><i class="fas fa-receipt me-2"></i>Comprobante Individual</a></li>
                     <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalReporteFechas"><i class="fas fa-calendar-alt me-2"></i>Reporte por Fechas</a></li>
                 </ul>
                </div>
            </div>
            
            <!-- Filtros Desktop -->
            <div class="btn-group filtros-pago filtros-desktop" role="group" aria-label="Filtros de pago">
                <button type="button" class="btn btn-outline-primary active filtro-btn" data-filtro="todos" style="background-color: #d31111 !important; border-color: #d31111 !important; color: white !important;">
                    <i class="fas fa-list me-2"></i>Todos los Pagos
                </button>
                <button type="button" class="btn btn-outline-success filtro-btn" data-filtro="efectivo" style="background-color: #d31111 !important; border-color: #d31111 !important; color: white !important;">
                    <i class="fas fa-money-bill-wave me-2"></i>Pagos en Efectivo
                </button>
                <button type="button" class="btn btn-outline-info filtro-btn" data-filtro="otros" style="background-color: #d31111 !important; border-color: #d31111 !important; color: white !important;">
                    <i class="fas fa-credit-card me-2"></i>Otros Métodos
                </button>
            </div>
        </div>

        <!-- Filtros Mobile -->
        <div class="filtros-mobile">
            <button type="button" class="filtro-btn-mobile active" data-filtro="todos">
                <i class="fas fa-list me-2"></i>Todos los Pagos
            </button>
            <button type="button" class="filtro-btn-mobile" data-filtro="efectivo">
                <i class="fas fa-money-bill-wave me-2"></i>Pagos en Efectivo
            </button>
            <button type="button" class="filtro-btn-mobile" data-filtro="otros">
                <i class="fas fa-credit-card me-2"></i>Otros Métodos
            </button>
        </div>

        <div class="table-responsive mt-0">
            <table id="pagos" class="table table-striped table-bordered">
                <thead class="table-dark">
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
                </tbody>
            </table>
        </div>
        
        <!-- Vista Móvil -->
        <div class="pagos-mobile-list mt-2">
            <!-- Los pagos se cargarán dinámicamente aquí -->
        </div>
        
        <br>
        <a href="index.php?url=home" class="btn btn-secondary">
            <i class="fas fa-home me-2"></i>Menú Principal
        </a>
    </div>

    <!-- Modal para Seleccionar Pedido -->
    <div class="modal fade" id="modalSeleccionarPedido" tabindex="-1" aria-labelledby="modalSeleccionarPedidoLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #d31111 0%, #c0392b 100%); color: white;">
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
                                <tbody id="tbody-pedidos-pendientes">
                                    <?php foreach ($pedidosPendientes as $pedido): ?>
                                        <tr>
                                            <td>#<?= htmlspecialchars($pedido['id_pedido']) ?></td>
                                            <td><?= htmlspecialchars($pedido['nomcliente'] . ' (' . $pedido['ced_cliente'] . ')') ?></td>
                                            <td><?= date('d/m/Y', strtotime($pedido['fecha'])) ?></td>
                                            <td>$<?= number_format($pedido['total'], 2, ',', '.') ?></td>
                                            <td><?= $pedido['cant_producto'] ?> producto(s)</td>
                                            <td>
                                                <button class="btn btn-sm btn-seleccionar-pedido" style="background: linear-gradient(135deg, #d31111 0%, #c0392b 100%) !important; border: none !important; box-shadow: none !important;" 
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

    <!-- Modal para Nuevo Pago -->
    <div class="modal fade" id="modalNuevoPago" tabindex="-1" aria-labelledby="modalNuevoPagoLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #d31111 0%, #c0392b 100%); color: white;">
                    <h5 class="modal-title" id="modalNuevoPagoLabel">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Registrar Pago
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2" style="color:#ffffff"></i>Detalles del Pedido</h6>
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

                    <form id="formPago" class="needs-validation" novalidate>
                        <input type="hidden" name="id_pedido" id="id_pedido">

                        <div class="mb-3">
                            <label for="cod_metodo" class="form-label">Método de Pago <span class="text-danger">*</span></label>
                            <select class="form-select" id="cod_metodo" name="cod_metodo" required>
                                <option value="" selected disabled>Seleccione un método de pago</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor seleccione un método de pago.
                            </div>
                        </div>
                        
                        <div class="mb-3 banco-field">
                            <label for="banco" class="form-label">Banco</label>
                            <select class="form-select" id="banco" name="banco">
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

                        <div class="mb-3 referencia-field">
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
                            <button type="submit" class="btn btn-success" style="background: linear-gradient(135deg, #d31111 0%, #c0392b 100%) !important; border: none !important; box-shadow: none !important;">
                                <i class="fas fa-save me-1"></i> Guardar Pago
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Detalles de Pago -->
    <div class="modal fade" id="modalDetallesPago" tabindex="-1" aria-labelledby="modalDetallesPagoLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalDetallesPagoLabel">
                        <i class="fas fa-info-circle me-2"></i>
                        Detalles del Pago
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Información del Pago</h6>
                            <p><strong>ID del Pago:</strong> <span id="detalle-id-pago"></span></p>
                            <p><strong>Fecha:</strong> <span id="detalle-fecha"></span></p>
                            <p><strong>Monto:</strong> $<span id="detalle-monto"></span></p>
                            <p><strong>Método:</strong> <span id="detalle-metodo"></span></p>
                            <p><strong>Banco:</strong> <span id="detalle-banco"></span></p>
                            <p><strong>Referencia:</strong> <span id="detalle-referencia"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">Información del Pedido</h6>
                            <p><strong>ID del Pedido:</strong> <span id="detalle-id-pedido"></span></p>
                            <p><strong>Cliente:</strong> <span id="detalle-cliente"></span></p>
                            <p><strong>Fecha del Pedido:</strong> <span id="detalle-fecha-pedido"></span></p>
                            <p><strong>Total del Pedido:</strong> $<span id="detalle-total-pedido"></span></p>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="fw-bold">Productos del Pedido</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="detalle-productos">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="fw-bold">Historial del Pago</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Acción</th>
                                        <th>Fecha/Hora</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody id="detalle-historial">
                                    <!-- El historial se carga dinámicamente vía AJAX y JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Actualizar Pago -->
    <div class="modal fade" id="modalEditarPago" tabindex="-1" aria-labelledby="modalEditarPagoLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #d31111 0%, #c0392b 100%) !important;">
                    <h5 class="modal-title" id="modalEditarPagoLabel">
                        <i class="fas fa-sync-alt"></i>
                        Actualizar Pago
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarPago" class="needs-validation" novalidate>
                        <input type="hidden" id="editar_id_pago" name="id_pago">
                        
                        <div class="mb-3">
                            <label for="editar_cod_metodo" class="form-label">Método de Pago <span class="text-danger">*</span></label>
                            <select class="form-select" id="editar_cod_metodo" name="cod_metodo" required>
                                <option value="" disabled>Seleccione un método de pago</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor seleccione un método de pago.
                            </div>
                        </div>
                        
                        <div class="mb-3 editar-banco-field">
                            <label for="editar_banco" class="form-label">Banco <span class="text-danger">*</span></label>
                            <select class="form-select" id="editar_banco" name="banco" required>
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

                        <div class="mb-3 editar-referencia-field">
                            <label for="editar_referencia" class="form-label">Número de Referencia <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editar_referencia" name="referencia" required
                                   placeholder="Ingrese el número de referencia del pago">
                            <div class="invalid-feedback">
                                Por favor ingrese el número de referencia.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="editar_monto" class="form-label">Monto Pagado <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="editar_monto" name="monto" 
                                       step="0.01" min="0.01" required>
                                <div class="invalid-feedback">
                                    Por favor ingrese el monto pagado.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="editar_fecha" class="form-label">Fecha del Pago <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="editar_fecha" name="fecha" required>
                            <div class="invalid-feedback">
                                Por favor ingrese la fecha del pago.
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="button" class="btn btn-secondary me-md-2" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-save me-1"></i> Actualizar Pago
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Comprobante -->
    <div class="modal fade" id="modalComprobante" tabindex="-1" aria-labelledby="modalComprobanteLabel" aria-hidden="true">
       <div class="modal-dialog">
           <div class="modal-content">
               <div class="modal-header bg-primary text-white">
                   <h5 class="modal-title" id="modalComprobanteLabel">
                       <i class="fas fa-receipt me-2"></i>Generar Comprobante
                   </h5>
                   <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
               </div>
               <div class="modal-body">
                   <form id="formComprobante">
                       <div class="mb-3">
                           <label for="id_pago_comprobante" class="form-label">Número de Pago</label>
                           <input type="number" class="form-control" id="id_pago_comprobante" required>
                           <div class="invalid-feedback">
                               Por favor ingrese un número de pago válido
                           </div>
                       </div>
                   </form>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                       <i class="fas fa-times me-2"></i>Cancelar
                   </button>
                   <button type="button" class="btn btn-primary" id="btnGenerarComprobante">
                       <i class="fas fa-file-pdf me-2"></i>Generar
                   </button>
               </div>
           </div>
       </div>
   </div>

   <!-- Modal para Reporte por Fechas -->
   <div class="modal fade" id="modalReporteFechas" tabindex="-1" aria-labelledby="modalReporteFechasLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalReporteFechasLabel">
                    <i class="fas fa-calendar-alt me-2"></i>Reporte por Rango de Fechas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formReporteFechas">
                    <div class="mb-3">
                        <label for="fechaInicio" class="form-label">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="fechaInicio" required>
                        <div class="invalid-feedback">
                            Por favor seleccione una fecha de inicio
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="fechaFin" class="form-label">Fecha de Fin</label>
                        <input type="date" class="form-control" id="fechaFin" required>
                        <div class="invalid-feedback">
                            Por favor seleccione una fecha de fin
                        </div>
                    </div>
                    <!-- AGREGAR ESTE NUEVO CAMPO -->
                    <div class="mb-3">
                        <label for="filtroMetodo" class="form-label">Método de Pago</label>
                        <select class="form-select" id="filtroMetodo">
                            <option value="todos">Todos los métodos</option>
                            <!-- Las opciones se cargarán dinámicamente -->
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btnGenerarReporteFechas">
                    <i class="fas fa-file-pdf me-1"></i> Generar Reporte
                </button>
            </div>
        </div>
    </div>
</div>

    <!-- jQuery primero, luego Popper.js, luego Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <!-- SweetAlert2 para confirmaciones más atractivas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Scripts personalizados -->
    <script src="/Natys/Assets/js/theme.js"></script>
    <script src="/Natys/Assets/js/pago.js"></script>
    
    <!-- Inicialización de tooltips y popovers -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Inicializar popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Inicializar menús desplegables
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
    });
    </script>
   
</body>
</html>
<?php
$content = ob_get_clean();
include 'Assets/layouts/base.php';
?>