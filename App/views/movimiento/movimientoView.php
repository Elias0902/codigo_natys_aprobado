<?php
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Movimientos de Entrada</title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            padding-bottom: 70px;
            background-color: #f8f9fa;
        }
        @media (max-width: 767.98px) {
            div.dataTables_length,
            div.dataTables_filter,
            div.dataTables_info,
            div.dataTables_paginate {
                display: none !important;
            }
        }
        .btn-actions { margin: 0 2px; }
        .table-responsive { overflow-x: auto; }
        .badge { font-size: 0.85em; }
        .stock-alert { color: #d31111; font-weight: bold; }
        .product-inactive { color: #6c757d; font-style: italic; }
        .filter-btn { background-color: #d31111 !important; border-color: #d31111 !important; color: white !important; }
        .filter-btn:hover { background-color: #b30e0e !important; border-color: #b30e0e !important; }
        .filter-btn.active { font-weight: bold; background-color: #b30e0e !important; border-color: #b30e0e !important; }
        /* Estilos para movimientos de salida */
        .movimiento-salida {
            border-left: 4px solid #d31111 !important;
            background-color: rgba(211, 17, 17, 0.05) !important;
        }
        
        .movimiento-salida .card-title {
            color: #d31111 !important;
        }
        
        .movimiento-salida .card-body {
            border-left: 3px solid #d31111;
        }
        
        .btn, .btn-success, .btn-primary, .btn-outline-primary, .btn-danger, .btn-warning, .btn-info, .btn-secondary {
            background-color: #d31111 !important;
            border-color: #d31111 !important;
            color: white !important;
        }
        .btn:hover, .btn-success:hover, .btn-primary:hover, .btn-outline-primary:hover, 
        .btn-danger:hover, .btn-warning:hover, .btn-info:hover, .btn-secondary:hover {
            background-color: #b30e0e !important;
            border-color: #b30e0e !important;
            color: white !important;
        }
        .card {
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
        }
        .card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        .card .badge {
            font-size: 0.9em;
        }
        .btn-action {
            margin: 0 2px;
            transition: all 0.2s;
            border-radius: 0.375rem;
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .page-header {
            background: linear-gradient(135deg, #d31111 0%, #b30e0e 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0.5rem;
        }
        .table th {
            background-color: #343a40;
            color: white;
            border-color: #454d55;
        }
        .modal-header {
            background: linear-gradient(135deg, #d31111 0%, #b30e0e 100%);
            color: white;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #d31111 0%, #b30e0e 100%);
        }
        .footer-custom {
            background: linear-gradient(135deg, #d31111 0%, #b30e0e 100%);
            color: white;
            padding: 1rem 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        /* Estilos específicos para el dropdown de reportes */
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

        /* Estilos para validación */
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

        /* Posicionamiento de íconos */
        .position-relative {
            position: relative;
        }

        .position-relative .form-control {
            padding-right: 2.5rem;
        }

        /* Estilos para mensajes de validación tipo tostada */
        .valid-feedback, .invalid-feedback {
            display: none;
            position: absolute;
            left: 0;
            width: 100%;
            z-index: 10;
            margin-top: 0.5rem;
            animation: slideDown 0.3s ease-out;
        }

        .valid-feedback {
            color: #0f5132;
            background-color: #d1e7dd;
            border: 1px solid #badbcc;
            border-radius: 0.375rem;
            padding: 0.5rem 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            font-size: 0.875rem;
        }

        .invalid-feedback {
            color: #842029;
            background-color: #f8d7da;
            border: 1px solid #f5c2c7;
            border-radius: 0.375rem;
            padding: 0.5rem 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        /* Animación para los mensajes */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Ajustar posición de los contenedores de feedback */
        .position-relative {
            margin-bottom: 1.5rem;
        }

        .is-valid + .valid-feedback,
        .was-validated :valid ~ .valid-feedback {
            display: flex;
        }

        .is-invalid + .invalid-feedback,
        .was-validated :invalid ~ .invalid-feedback {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="mb-4" style="text-align: center;"><i class="fas fa-credit-card p-4"></i>Gestión de Movimiento De Entrada</h1>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2 mb-2 mb-md-0">
            <button type="button" class="btn shadow-sm" id="btnNuevoMovimiento">
                    <i class="fas fa-plus-circle me-2"></i>Registrar Movimiento
                </button>
                
                <!-- Dropdown para Reportes -->
                <div class="dropdown">
                        <button class="btn dropdown-toggle shadow-sm" type="button" id="dropdownReportesButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-file-pdf me-2"></i>Reportes
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownReportesButton">
                            <li><a class="dropdown-item" href="index.php?url=movimiento&action=reporte_lista" target="_blank">
                                <i class="fas fa-list me-2"></i>Historial Completo
                            </a></li>
                            <li><a class="dropdown-item" href="index.php?url=movimiento&action=reporte_entradas" target="_blank">
                                <i class="fas fa-arrow-down me-2"></i>Entradas de Inventario
                            </a></li>
                            <li><a class="dropdown-item" href="index.php?url=movimiento&action=reporte_salidas" target="_blank">
                                <i class="fas fa-arrow-up me-2"></i>Salidas de Inventario
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalReportesFechas">
                                <i class="fas fa-calendar-alt me-2"></i>Reportes por Fechas
                            </a></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalKardexProducto">
                                <i class="fas fa-list-alt me-2"></i>Kardex por Producto
                            </a></li>
                        </ul>
                </div>
            </div>
                    
            <div class="btn-group filtros-pago" role="group" aria-label="Filtros de pago">
                        <button type="button" class="btn btn-outline-primary active" id="btnActivos">
                            <i class="fas fa-list me-2"></i>Movimientos Activos
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="btnHistorial">
                            <i class="fas fa-history me-2"></i>Historial
                        </button>
            </div>
        </div>


<!-- DataTable -->
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="movimientos" class="table table-striped table-bordered table-hover d-none d-md-table">
                <thead class="table-dark">
                    <tr>
                        <th>N° Movimiento</th>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Cantidad / Stock</th> <!-- Cambiado el título -->
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>

            <div id="movimientosMobile" class="d-md-none">
            </div>
        </div>
    </div>
</div>
            
            <br>
            <div class="text-center">
                <a href="index.php?url=home" class="btn btn-secondary btn-lg">
                    <i class="fas fa-home me-2"></i>Menú Principal
                </a>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header text-white">
                    <h5 class="modal-title" id="modalEliminarLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-exchange-alt fa-3x text-danger mb-3"></i>
                    </div>
                    <p class="fs-5 mb-3">¿Está seguro de eliminar este movimiento?</p>
                    <p class="text-muted">
                        Esta acción cambiará el estado del movimiento a inactivo.<br>
                        Podrá verlo en el historial posteriormente.
                    </p>
                    <input type="hidden" id="idEliminar">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmarEliminar">
                        <i class="fas fa-trash-alt me-1"></i>Eliminar Movimiento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalles -->
    <div class="modal fade" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalDetallesLabel">
                        <i class="fas fa-info-circle me-2"></i>
                        Detalles del Movimiento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contenidoDetalles">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalEditarLabel">
                        <i class="fas fa-sync-alt"></i>
                        Editar Movimiento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contenidoEditar">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNuevo" tabindex="-1" aria-labelledby="modalNuevoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header text-white" style="background-color: #d31111 !important;">
                    <h5 class="modal-title" id="modalNuevoLabel">
                        <i class="fas fa-plus-circle me-2"></i>
                        Nuevo Movimiento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoMovimiento" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3 position-relative">
                                <label for="fecha" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Fecha *
                                </label>
                                <input type="date" class="form-control" id="fecha" name="fecha" required>
                                <div class="valid-feedback">
                                    <i class="fas fa-check-circle me-2"></i>Campo válido
                                </div>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-2"></i>Por favor seleccione una fecha
                                </div>
                            </div>

                            <div class="col-md-6 mb-3 position-relative">
                                <label for="producto" class="form-label">
                                    <i class="fas fa-box me-1"></i>Producto *
                                </label>
                                <select class="form-select" id="producto" name="producto" required>
                                    <option value="">Seleccione un producto</option>
                                    <?php foreach ($productos as $producto): ?>
                                        <option value="<?php echo $producto['cod_producto']; ?>"
                                            <?php echo (isset($producto['estado']) && $producto['estado'] == 0) ? 'class="text-muted"' : ''; ?>>
                                            <?php echo htmlspecialchars($producto['nombre']); ?>
                                            <?php if (isset($producto['estado']) && $producto['estado'] == 0): ?>
                                                (Inactivo - Se activará al guardar)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="valid-feedback">
                                    <i class="fas fa-check-circle me-2"></i>Campo válido
                                </div>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-2"></i>Por favor seleccione un producto
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 position-relative">
                                <label for="cantidad" class="form-label">
                                    <i class="fas fa-hashtag me-1"></i>Cantidad *
                                </label>
                                <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
                                <div class="valid-feedback">
                                    <i class="fas fa-check-circle me-2"></i>Campo válido
                                </div>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-2"></i>Por favor ingrese una cantidad válida
                                </div>
                            </div>

                            <div class="col-md-6 mb-3 position-relative">
                                <label for="precio" class="form-label">
                                    <i class="fas fa-dollar-sign me-1"></i>Precio Unitario
                                </label>
                                <input type="number" class="form-control" id="precio" name="precio" step="0.01" readonly style="background-color: #f8f9fa;">
                                <div class="form-text">El precio se cargará automáticamente al seleccionar el producto</div>
                                <div class="valid-feedback">
                                    <i class="fas fa-check-circle me-2"></i>Campo válido
                                </div>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-2"></i>Por favor ingrese el precio
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3 position-relative">
                                <label for="observaciones" class="form-label">
                                    <i class="fas fa-comment me-1"></i>Observaciones
                                </label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="2"></textarea>
                                <div class="valid-feedback">
                                    <i class="fas fa-check-circle me-2"></i>Campo válido
                                </div>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-2"></i>Las observaciones deben tener al menos 3 caracteres
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Nota:</strong> Los campos marcados con * son obligatorios.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-success" id="guardarMovimiento">
                        <i class="fas fa-save me-1"></i>Guardar Movimiento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Reportes por Fechas -->
    <div class="modal fade" id="modalReportesFechas" tabindex="-1" aria-labelledby="modalReportesFechasLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalReportesFechasLabel">
                        <i class="fas fa-calendar-alt me-2"></i>Reportes por Fechas
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formReportesFechas">
                        <div class="mb-3">
                            <label for="tipo_reporte" class="form-label">Tipo de Reporte</label>
                            <select class="form-select" id="tipo_reporte" required>
                                <option value="">Seleccione un tipo de reporte</option>
                                <option value="reporte_por_fechas">Movimientos Generales</option>
                                <option value="entradas_por_fechas">Entradas de Movimientos</option>
                                <option value="salidas_por_fechas">Salidas de Movimientos</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor seleccione un tipo de reporte
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_inicio_reporte" class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio_reporte" required>
                            <div class="invalid-feedback">
                                Por favor ingrese la fecha de inicio
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_fin_reporte" class="form-label">Fecha Final</label>
                            <input type="date" class="form-control" id="fecha_fin_reporte" required readonly style="background-color: #f8f9fa;">
                            <div class="form-text">La fecha final siempre será la fecha actual</div>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Nota:</strong> Seleccione el tipo de reporte y el rango de fechas para generar el reporte correspondiente.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="btnGenerarReporteFechas">
                        <i class="fas fa-file-pdf me-2"></i>Generar Reporte
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Kardex Producto -->
    <div class="modal fade" id="modalKardexProducto" tabindex="-1" aria-labelledby="modalKardexProductoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalKardexProductoLabel">
                        <i class="fas fa-list-alt me-2"></i>Generar Kardex de Producto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formKardex">
                        <div class="mb-3">
                            <label for="cod_producto_kardex" class="form-label">Código del Producto</label>
                            <input type="text" class="form-control" id="cod_producto_kardex" required>
                            <div class="invalid-feedback">
                                Por favor ingrese un código de producto válido
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="btnGenerarKardex">
                        <i class="fas fa-file-pdf me-2"></i>Generar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="/Natys/Assets/js/movimiento.js"></script>
</body>
</html>
<?php
$content = ob_get_clean();
include 'Assets/layouts/base.php';
?>