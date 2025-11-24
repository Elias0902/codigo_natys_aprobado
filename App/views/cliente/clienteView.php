<?php
ob_start();
// Asegurarse de que la variable mostrandoEliminados esté definida
$mostrandoEliminados = isset($_GET['mostrarEliminados']) && $_GET['mostrarEliminados'] === 'true';
?>
<!DOCTYPE html>
<html lang="es" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Natys</title>
    <link rel="icon" href="/Natys/Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="/Natys/Assets/css/themes.css">
    <style>
        body {
            padding-bottom: 70px;
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        @media (max-width: 767.98px) {
            div.dataTables_length,
            div.dataTables_filter,
            div.dataTables_info,
            div.dataTables_paginate {
                display: none !important;
            }
        }

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

        .btn, .btn-group .btn, .btn-success, .btn-warning, .btn-danger, .btn-info, .btn-secondary, .btn-primary {
            background-color: #d31111 !important;
            border-color: #d31111 !important;
            color: white !important;
        }

        .btn:hover, .btn-group .btn:hover, .btn-success:hover, .btn-warning:hover, 
        .btn-danger:hover, .btn-info:hover, .btn-secondary:hover, .btn-primary:hover {
            background-color: #b30e0e !important;
            border-color: #b30e0e !important;
            color: white !important;
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

        .btn-success, .btn-success:hover, .btn-success:focus, 
        .btn-success.dropdown-toggle, .btn-success.dropdown-toggle:hover, .btn-success.dropdown-toggle:focus {
            background-color: #d31111 !important;
            background-image: none !important;
            border: none !important;
            color: white !important;
            box-shadow: none !important;
        }

        .btn-success:hover, .btn-success:focus {
            background-color: #b30e0e !important;
            border-color: #b30e0e !important;
            color: white !important;
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
/* Estilos para loading de DataTable */
.dataTables_processing {
    background: var(--primary-color) !important;
    color: white !important;
    border-radius: 5px;
    padding: 10px 20px !important;
    font-weight: bold;
}

/* Estilos para mensajes de vacío */
.dataTables_empty {
    text-align: center;
    padding: 40px !important;
    color: #6c757d;
}

.dataTables_empty i {
    font-size: 2rem;
    margin-bottom: 10px;
    display: block;
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
            display: block;
        }
        
        .is-invalid + .invalid-feedback,
        .was-validated :invalid ~ .invalid-feedback {
            display: block;
        }
        
        /* Posicionamiento de íconos */
        .input-group-valid {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            width: 100%;
        }
        
        .input-group-valid .form-control {
            padding-right: 2.5rem;
        }
        
        .input-group-valid .input-group-text {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            z-index: 5;
            display: flex;
            align-items: center;
            padding: 0 0.75rem;
            background: transparent;
            border: none;
        }
        
        .input-group-valid .valid-feedback-icon {
            color: #198754;
            display: none;
        }
        
        .input-group-valid .is-valid + .input-group-text .valid-feedback-icon,
        .was-validated .input-group-valid .form-control:valid + .input-group-text .valid-feedback-icon {
            display: inline-block;
        }
        
        .form-control.is-valid:focus, .was-validated .form-control:valid:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
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

        .btn-action {
            margin: 0 2px;
            transition: all 0.2s;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="mb-4" style="text-align: center;"><i class="fas fa-user-tie p-4"></i>Gestión de Clientes</h1>
        
        <div class="d-flex justify-content-between mb-3">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success" id="btnNuevoCliente" style="background-color: #d31111 !important; border: none !important; box-shadow: none !important;">
                    <i class="fas fa-plus-circle me-2"></i>Registrar Cliente
                </button>
                
                <!-- Dropdown de Reportes CORREGIDO -->
                <div class="btn-group">
                    <button type="button" id="reportesDropdownClientes" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-pdf me-2"></i> Reportes
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item text-dark" href="index.php?url=cliente&action=reporte_lista" target="_blank">
                            <i class="fas fa-list me-2 text-dark"></i><span class="text-dark"> Listado de Clientes</span>
                        </a></li>
                        <li><a class="dropdown-item text-dark" href="index.php?url=cliente&action=reporte_contactos" target="_blank">
                            <i class="fas fa-address-book me-2 text-dark"></i><span class="text-dark"> Directorio de Contactos</span>
                        </a></li>
                    </ul>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="button" class="btn <?php echo $mostrandoEliminados ? 'btn-info' : 'btn-warning'; ?>" id="btnToggleEstado">
                    <i class="fas <?php echo $mostrandoEliminados ? 'fa-user-check' : 'fa-trash-restore'; ?> me-2"></i>
                    <?php echo $mostrandoEliminados ? 'Mostrar Activos' : 'Mostrar Eliminados'; ?>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="clientes" class="table table-hover d-none d-md-table" style="margin: 0 auto; width: 100%;">
                <thead class="table-light">
                    <tr>
                        <th class="text-nowrap">Cédula</th>
                        <th class="text-nowrap">Nombre</th>
                        <th class="text-nowrap">Correo</th>
                        <th class="text-nowrap">Teléfono</th>
                        <th class="text-nowrap">Dirección</th>
                        <th class="text-nowrap">Estado</th>
                        <th class="text-nowrap text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td class="align-middle">
                                <?php echo htmlspecialchars($cliente['ced_cliente']); ?>
                            </td>
                            <td class="align-middle"><?php echo htmlspecialchars($cliente['nomcliente']); ?></td>
                            <td class="align-middle">
                                <?php echo htmlspecialchars($cliente['correo']); ?>
                            </td>
                            <td class="align-middle">
                                <?php echo htmlspecialchars($cliente['telefono']); ?>
                            </td>
                            <td class="align-middle"><?php echo htmlspecialchars($cliente['direccion']); ?></td>
                            <td class="align-middle">
                                <span class="badge bg-<?php echo $cliente['estado'] == 1 ? 'success' : 'secondary'; ?>">
                                    <?php echo $cliente['estado'] == 1 ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td class="align-middle text-end">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-info detalles" data-cedula="<?php echo $cliente['ced_cliente']; ?>" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($cliente['estado'] == 1): ?>
                                        <!-- Cliente activo: Mostrar actualizar y eliminar -->
                                        <button class="btn btn-sm btn-primary actualizar" data-cedula="<?php echo $cliente['ced_cliente']; ?>" title="Actualizar cliente">
                                        <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger eliminar" data-cedula="<?php echo $cliente['ced_cliente']; ?>" title="Eliminar cliente">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    <?php else: ?>
                                        <!-- Cliente inactivo: Mostrar solo restaurar -->
                                        <button class="btn btn-sm btn-warning restaurar" data-cedula="<?php echo $cliente['ced_cliente']; ?>" title="Restaurar cliente">
                                            <i class="fas fa-undo"></i> Restaurar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Versión móvil -->
            <div class="d-md-none">
                <?php foreach ($clientes as $cliente): ?>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">
                                    <?php echo htmlspecialchars($cliente['nomcliente']); ?>
                                </h5>
                                <span class="badge bg-<?php echo $cliente['estado'] == 1 ? 'success' : 'secondary'; ?>">
                                    <?php echo $cliente['estado'] == 1 ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </div>
                            
                            <div class="mb-2">
                                <div class="text-muted small">Cédula</div>
                                <div><?php echo htmlspecialchars($cliente['ced_cliente']); ?></div>
                            </div>
                            
                            <div class="mb-2">
                                <div class="text-muted small">Correo</div>
                                <a href="mailto:<?php echo htmlspecialchars($cliente['correo']); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($cliente['correo']); ?>
                                </a>
                            </div>
                            
                            <div class="mb-2">
                                <div class="text-muted small">Teléfono</div>
                                <a href="tel:<?php echo htmlspecialchars($cliente['telefono']); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($cliente['telefono']); ?>
                                </a>
                            </div>
                            
                            <div class="mb-3">
                                <div class="text-muted small">Dirección</div>
                                <div><?php echo htmlspecialchars($cliente['direccion']); ?></div>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn btn-sm btn-info detalles" data-cedula="<?php echo $cliente['ced_cliente']; ?>" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($cliente['estado'] == 1): ?>
                                    <!-- Cliente activo: Mostrar actualizar y eliminar -->
                                    <button class="btn btn-sm btn-primary actualizar" data-cedula="<?php echo $cliente['ced_cliente']; ?>" title="Actualizar cliente">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger eliminar" data-cedula="<?php echo $cliente['ced_cliente']; ?>" title="Eliminar cliente">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                <?php else: ?>
                                    <!-- Cliente inactivo: Mostrar solo restaurar -->
                                    <button class="btn btn-sm btn-warning restaurar" data-cedula="<?php echo $cliente['ced_cliente']; ?>" title="Restaurar cliente">
                                        <i class="fas fa-undo"></i> Restaurar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <br>
        <a href="index.php?url=home" class="btn btn-secondary">
            <i class="fas fa-home me-2"></i>Menú Principal
        </a>
    </div>

    <!-- Modal Eliminar -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalEliminarLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-times fa-3x text-danger mb-3"></i>
                    </div>
                    <p class="fs-5 mb-3">¿Está seguro de eliminar este cliente?</p>
                    <p class="text-muted">
                        Esta acción cambiará el estado del cliente a inactivo.<br>
                        Podrá restaurarlo posteriormente si es necesario.
                    </p>
                    <input type="hidden" id="cedulaEliminar">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmarEliminar">
                        <i class="fas fa-trash-alt me-1"></i>Eliminar Cliente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Restaurar -->
    <div class="modal fade" id="modalRestaurar" tabindex="-1" aria-labelledby="modalRestaurarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="modalRestaurarLabel">
                        <i class="fas fa-undo me-2"></i>
                        Confirmar Restauración
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-check fa-3x text-warning mb-3"></i>
                    </div>
                    <p class="fs-5 mb-3">¿Está seguro de restaurar este cliente?</p>
                    <p class="text-muted">
                        Esta acción activará nuevamente el cliente<br>
                        y estará disponible para realizar operaciones.
                    </p>
                    <input type="hidden" id="cedulaRestaurar">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" id="confirmarRestaurar">
                        <i class="fas fa-undo me-1"></i> Restaurar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalles Cliente -->
    <div class="modal fade" id="modalDetallesCliente" tabindex="-1" aria-labelledby="modalDetallesClienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalDetallesClienteLabel">
                        <i class="fas fa-eye me-2"></i>Detalles del Cliente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6 d-flex">
                            <div class="card border-info flex-fill">
                                <div class="card-body d-flex flex-column">
                                    <div class="text-center mb-3">
                                        <i class="fas fa-user-circle fa-4x text-info"></i>
                                    </div>
                                    <h5 class="card-title text-info text-center mb-3">
                                        <i class="fas fa-id-card me-2"></i>Información Personal
                                    </h5>
                                    <div class="flex-grow-1">
                                        <p class="mb-2"><strong>Cédula:</strong> <span id="detalle-cedula"></span></p>
                                        <p class="mb-2"><strong>Nombre:</strong> <span id="detalle-nombre"></span></p>
                                        <p class="mb-2"><strong>Estado:</strong> <span id="detalle-estado" class="badge bg-success"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex">
                            <div class="card border-info flex-fill">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title text-info text-center mb-3">
                                        <i class="fas fa-address-book me-2"></i>Información de Contacto
                                    </h5>
                                    <div class="flex-grow-1">
                                        <p class="mb-2">
                                            <i class="fas fa-envelope me-2"></i>
                                            <strong>Correo:</strong> <span id="detalle-correo"></span>
                                        </p>
                                        <p class="mb-2">
                                            <i class="fas fa-phone me-2"></i>
                                            <strong>Teléfono:</strong> <span id="detalle-telefono"></span>
                                        </p>
                                        <p class="mb-3">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            <strong>Dirección:</strong>
                                        </p>
                                        <p id="detalle-direccion" class="ms-4 mb-0"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <div class="card border-info">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="fas fa-history me-2"></i>Historial de Pedidos
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0" id="tabla-pedidos">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Fecha</th>
                                                    <th>Productos</th>
                                                    <th>Total</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody id="detalle-pedidos">
                                                <!-- Los pedidos se cargarán aquí dinámicamente -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="sin-pedidos" class="text-center py-4 text-muted d-none">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p class="mb-0">El cliente no tiene pedidos registrados</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Actualizar -->
    <div class="modal fade" id="modalActualizar" tabindex="-1" aria-labelledby="modalActualizarLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalActualizarLabel">
                        <i class="fas fa-sync-alt"></i>
                        Actualizar Cliente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contenidoActualizar">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando información del cliente...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo -->
    <div class="modal fade" id="modalNuevo" tabindex="-1" aria-labelledby="modalNuevoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #d31111; color: white;">
                    <h5 class="modal-title" id="modalNuevoLabel">
                        <i class="fas fa-user-plus me-2"></i>
                        Registrar Cliente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contenidoNuevo">
                    <div class="text-center py-4">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Preparando formulario...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<!-- Script personalizado separado -->
<script src="/Natys/Assets/js/cliente.js"></script>
    <!-- Scripts del tema -->
    <script src="/Natys/Assets/js/theme.js"></script>
</body>
</html>

<?php
$content = ob_get_clean();
include 'Assets/layouts/base.php';
?>