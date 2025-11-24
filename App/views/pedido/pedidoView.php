<?php
ob_start();
?>
<!DOCTYPE html>
<html lang="es" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - Natys</title>
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

        /* Ocultar controles DataTables en móvil */
        @media (max-width: 767.98px) {
            div.dataTables_length,
            div.dataTables_filter,
            div.dataTables_info,
            div.dataTables_paginate {
                display: none !important;
            }
        }

        /* Estilos para la tabla */
        .table {
            color: var(--text-color);
            --bs-table-bg: transparent;
            background-color: var(--card-bg) !important;
        }

        .table thead {
            background-color: var(--card-bg) !important;
            color: var(--text-color) !important;
        }

        .table tbody tr {
            background-color: var(--card-bg) !important;
            color: var(--text-color) !important;
        }

        .table th, .table td {
            border-color: var(--border-color);
            background-color: var(--card-bg) !important;
        }

        /* Estilos para tarjetas en móvil */
        .card {
            background-color: var(--card-bg) !important;
            color: var(--text-color) !important;
            border: 1px solid var(--border-color) !important;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .card .badge {
            color: white !important;
        }

        /* Estilos para todos los botones */
        .btn, .btn-group .btn, .btn-success, .btn-primary, .btn-warning, .btn-danger, .btn-info, .btn-secondary {
            background-color: #d31111 !important;
            border-color: #d31111 !important;
            color: white !important;
        }

        .btn:hover, .btn-group .btn:hover, .btn-success:hover, .btn-primary:hover, .btn-warning:hover, 
        .btn-danger:hover, .btn-info:hover, .btn-secondary:hover,
        .btn:focus, .btn-group .btn:focus, .btn-success:focus, .btn-primary:focus, .btn-warning:focus, 
        .btn-danger:focus, .btn-info:focus, .btn-secondary:focus {
            background-color: #b30e0e !important;
            border-color: #b30e0e !important;
            color: white !important;
            box-shadow: none !important;
        }
        
        .btn-success, .btn-success:hover, .btn-success:focus,
        .btn-success.dropdown-toggle, .btn-success.dropdown-toggle:hover, .btn-success.dropdown-toggle:focus {
            background-color: #d31111 !important;
            background-image: none !important;
            border: none !important;
            color: white !important;
            box-shadow: none !important;
        }
        
        .modal-content {
            background-color: #ffffff !important;
            border: 1px solid #dee2e6;
            opacity: 1 !important;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
            color: white !important;
            border-bottom: 1px solid #dee2e6;
            opacity: 1 !important;
        }

        .modal-body {
            background-color: #ffffff !important;
            color: #000000 !important;
            opacity: 1 !important;
        }
        
        .modal-footer {
            background-color: #ffffff !important;
            color: #000000 !important;
            opacity: 1 !important;
            border-top: 1px solid #dee2e6;
        }
        
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5) !important;
            opacity: 1 !important;
        }
        
        .modal-backdrop.show {
            opacity: 0.5 !important;
        }
        
        .modal-body p,
        .modal-body div,
        .modal-body label,
        .modal-body .form-label,
        .modal-body .text-muted {
            color: #000000 !important;
        }
        
        .modal-body .form-control,
        .modal-body .form-select {
            background-color: #ffffff !important;
            color: #000000 !important;
            border: 1px solid #ced4da !important;
        }

        .form-label {
            color: var(--text-color);
        }

        .form-control, .form-select, .form-control:focus, .form-select:focus {
            background-color: var(--input-bg);
            color: var(--input-text);
            border-color: var(--border-color);
        }

        .modal-body .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(204, 29, 29, 0.25);
        }

        /* Asegurar que el modal de detalles esté por encima de otros modales */
        #modalDetalle {
            z-index: 1080 !important;
        }
        
        .modal-backdrop.show {
            z-index: 1040 !important;
        }
        
        #modalDetalle.modal {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 1080 !important;
            overflow: auto;
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

        .btn-action {
            margin: 0 2px;
            transition: all 0.2s;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .productos-lista {
            font-size: 0.875rem;
            max-height: 100px;
            overflow-y: auto;
        }

        .producto-item {
            padding: 2px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .producto-item:last-child {
            border-bottom: none;
        }

        /* Estilos específicos para el dropdown */
        .dropdown-menu {
            z-index: 1060 !important;
        }
        
        .dropdown-item {
            cursor: pointer;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #d31111;
        }

        /* Estados de pedidos */
        .badge-estado-pendiente {
            background-color: #ffc107 !important;
            color: #000 !important;
        }

        .badge-estado-aprobado {
            background-color: #198754 !important;
            color: #fff !important;
        }

        .badge-estado-cancelado {
            background-color: #dc3545 !important;
            color: #fff !important;
        }

        .badge-estado-finalizado {
            background-color: #6c757d !important;
            color: #fff !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="mb-4" style="text-align: center;"><i class="fas fa-shopping-cart p-4"></i>Gestión de Pedidos</h1>
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2 mb-2 mb-md-0">
                <button type="button" class="btn btn-success me-2" id="btnNuevoPedido" style="background: linear-gradient(135deg, #d31111 0%, #c0392b 100%) !important; border: none !important; box-shadow: none !important;">
                    <i class="fas fa-plus-circle me-2"></i>Registrar Pedido
                </button>
                
                <!-- Dropdown de Reportes -->
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownReportes" data-bs-toggle="dropdown" aria-expanded="false" style="background: linear-gradient(135deg, #d31111 0%, #c0392b 100%) !important; border: none !important; box-shadow: none !important;">
                        <i class="fas fa-file-pdf me-2"></i>Reportes
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownReportes" style="z-index: 1060; background-color: #ffffff; border: 1px solid rgba(0,0,0,.15); border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,.175);">
                        <li><a class="dropdown-item reporte-option" href="#" data-tipo="fechas">
                            <i class="fas fa-calendar-alt me-2"></i>Reporte por Fechas
                        </a></li>
                        <li><a class="dropdown-item" href="index.php?url=pedido&action=reporte_pendientes" target="_blank">
                            <i class="fas fa-clock me-2"></i>Pedidos Pendientes
                        </a></li>
                        <li><a class="dropdown-item" href="index.php?url=pedido&action=reporte_completados" target="_blank">
                            <i class="fas fa-check-circle me-2"></i>Pedidos Completados
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="index.php?url=pedido&action=reporte_lista&estado=todos" target="_blank">
                            <i class="fas fa-list me-2"></i>Listado General
                        </a></li>
                    </ul>
                </div>
                
            </div>
            
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-warning" id="btnVerPendientes" style="background: linear-gradient(135deg, #d31111 0%, #c0392b 100%) !important; border: none !important; box-shadow: none !important;">
                    <i class="fas fa-clock me-2"></i>Ver Pendientes
                </button>

            </div>
        </div>

        <!-- Modal para Reporte por Fechas -->
        <div class="modal fade" id="modalReporteFechas" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header text-white" style="background-color: #d31111 !important;">
                        <h5 class="modal-title">Generar Reporte por Fechas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formReporteFechas">
                            <div class="mb-3">
                                <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="fechaInicio" name="fecha_inicio" required>
                            </div>
                            <div class="mb-3">
                                <label for="fechaFin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fechaFin" name="fecha_fin" required>
                            </div>
                            <div class="mb-3">
                                <label for="tipoEstado" class="form-label">Estado del Pedido</label>
                                <select class="form-select" id="tipoEstado" name="estado">
                                    <option value="todos">Todos los Estados</option>
                                    <option value="0">Pendientes</option>
                                    <option value="1">Completados</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="btnGenerarReporte">
                            <i class="fas fa-download me-2"></i>Generar Reporte
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tablaPedidos" class="table table-hover d-none d-md-table" style="margin: 0 auto; width: 100%;">
                <thead class="table-light">
                    <tr>
                        <th class="text-nowrap">ID</th>
                        <th class="text-nowrap">Fecha</th>
                        <th class="text-nowrap">Cliente</th>
                        <th class="text-nowrap">Total</th>
                        <th class="text-nowrap">Productos</th>
                        <th class="text-nowrap">Estado</th>
                        <th class="text-nowrap text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>

            <!-- Versión móvil -->
            <div class="d-md-none" id="pedidosMovil">
                <!-- Los pedidos se cargarán dinámicamente via JavaScript -->
            </div>
        </div>
        
        <br>
        <a href="index.php?url=home" class="btn btn-secondary">
            <i class="fas fa-home me-2"></i>Menú Principal
        </a>
    </div>

    <!-- Modal Detalle -->
    <div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Detalle del Pedido #<span id="pedidoId"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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

    <!-- Modal Formulario -->
    <div class="modal fade" id="modalFormulario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalFormularioTitulo">Registrar Pedido</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formPedido">
                        <input type="hidden" id="id_pedido" name="id_pedido">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="ced_cliente" class="form-label">Cliente *</label>
                                <select class="form-select" id="ced_cliente" name="ced_cliente">
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
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="productos-seleccionados">
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

    <!-- Modal Stock Insuficiente -->
    <div class="modal fade" id="modalStockInsuficiente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header text-white" style="background-color: #d31111 !important;">
                    <h5 class="modal-title">Stock Insuficiente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="mensajeStock"></span>
                    </div>
                    <div class="mt-3">
                        <p><strong>Stock disponible:</strong> <span id="stockDisponible"></span></p>
                        <p><strong>Cantidad solicitada:</strong> <span id="cantidadSolicitada"></span></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pendientes -->
    <div class="modal fade" id="modalPendientes" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header text-white" style="background-color: #d31111 !important;">
                    <h5 class="modal-title">Pedidos Pendientes de Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <!-- Versión desktop -->
                    <div class="table-responsive d-none d-md-block">
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
                            </tbody>
                        </table>
                    </div>

                    <!-- Versión móvil -->
                    <div class="d-md-none" id="pendientesMovil">
                        <!-- Los pedidos pendientes se cargarán dinámicamente via JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>



    <!-- Modal Agregar Producto -->
    <div class="modal fade" id="modalAgregarProducto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Agregar Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
                                            data-unidad="<?php echo $producto['unidad']; ?>"
                                            data-stock="<?php echo $producto['stock']; ?>">
                                        <?php echo htmlspecialchars($producto['nombre'] . ' - $' . number_format($producto['precio'], 2) . ' (' . $producto['unidad'] . ') - Stock: ' . $producto['stock']); ?>
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
    <script src="/Natys/Assets/js/theme.js"></script>

    <script>
        // Script para manejar el reporte por fechas
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar fechas por defecto (últimos 30 días) SOLO para el modal de fechas
            const fechaFin = new Date();
            const fechaInicio = new Date();
            fechaInicio.setDate(fechaInicio.getDate() - 30);
            
            // Solo establecer fechas si los elementos existen (en el modal)
            const fechaInicioElement = document.getElementById('fechaInicio');
            const fechaFinElement = document.getElementById('fechaFin');
            
            if (fechaInicioElement && fechaFinElement) {
                fechaInicioElement.value = fechaInicio.toISOString().split('T')[0];
                fechaFinElement.value = fechaFin.toISOString().split('T')[0];
            }
            
            // Inicializar dropdown de Bootstrap manualmente
            const dropdownElement = document.getElementById('dropdownReportes');
            if (dropdownElement) {
                new bootstrap.Dropdown(dropdownElement);
            }
            
            // Manejar clic en opción de reporte por fechas
            document.querySelectorAll('.reporte-option').forEach(function(element) {
                element.addEventListener('click', function(e) {
                    e.preventDefault();
                    const tipo = this.getAttribute('data-tipo');
                    if (tipo === 'fechas') {
                        const modal = new bootstrap.Modal(document.getElementById('modalReporteFechas'));
                        modal.show();
                    }
                });
            });
            
            // Manejar clic en el botón de generar reporte
            const btnGenerarReporte = document.getElementById('btnGenerarReporte');
            if (btnGenerarReporte) {
                btnGenerarReporte.addEventListener('click', function() {
                    const fechaInicio = document.getElementById('fechaInicio').value;
                    const fechaFin = document.getElementById('fechaFin').value;
                    const estado = document.getElementById('tipoEstado').value;
                    
                    if (!fechaInicio || !fechaFin) {
                        toastr.error('Por favor seleccione ambas fechas');
                        return;
                    }
                    
                    if (new Date(fechaInicio) > new Date(fechaFin)) {
                        toastr.error('La fecha de inicio no puede ser mayor a la fecha fin');
                        return;
                    }
                    
                    // Construir la URL del reporte
                    let url = `index.php?url=pedido&action=reporte_lista&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
                    
                    if (estado !== 'todos') {
                        url += `&estado=${estado}`;
                    }
                    
                    // Abrir el reporte en una nueva pestaña
                    window.open(url, '_blank');
                    
                    // Cerrar el modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalReporteFechas'));
                    if (modal) {
                        modal.hide();
                    }
                });
            }
        });
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
include 'Assets/layouts/base.php';
?>