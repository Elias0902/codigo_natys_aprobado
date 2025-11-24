<?php
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Natys</title>
    <link rel="icon" href="../Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="/Natys/Assets/css/responsive-tables.css">
    
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
        :root {
            --primary-color: #d31111;
            --secondary-color: #333;
            --accent-color: #f8f9fa;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-custom {
            background-color: var(--secondary-color);
        }
        
        .navbar-custom .navbar-brand {
            color: white;
            font-weight: bold;
        }
        
        .product-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 10px;
            overflow: hidden;
            border: none;
            margin-bottom: 20px;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .product-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        
        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }
        
        .product-price {
            color: #d31111;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .badge.bg-secondary {
            background-color: #d31111 !important;
        }
        
        .modal-header {
            background-color: #d31111 !important;
            background-image: none !important;
        }
        
        .btn-primary, .btn-primary-custom, .btn-success, .btn-warning, .btn-danger, .btn-info, .btn-secondary {
            background-color: #d31111 !important;
            border-color: #d31111 !important;
            background-image: none !important;
            color: white !important;
        }
        
        .btn-primary:hover, .btn-primary-custom:hover, .btn-success:hover, .btn-warning:hover, 
        .btn-danger:hover, .btn-info:hover, .btn-secondary:hover {
            background-color: #b30e0e !important;
            border-color: #b30e0e !important;
            color: white !important;
        }

        
        
        .section-title {
            border-left: 4px solid var(--primary-color);
            padding-left: 10px;
            margin: 20px 0;
        }
        
        .product-description {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            height: 72px;
        }
        
        .action-buttons .btn {
            margin-right: 5px;
        }
        
        .loading-spinner {
            display: none;
            width: 40px;
            height: 40px;
            margin: 20px auto;
        }
        
        .stock-info {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .stock-low {
            color: #d31111;
            font-weight: bold;
        }
        
        .stock-ok {
            color: #198754;
        }

        .stock-out {
            color: #d31111;
            font-weight: bold;
        }

        /* Estilos para validación */
        .is-invalid {
            border-color: #d31111 !important;
        }

        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #d31111;
        }

        .was-validated .form-control:invalid,
        .form-control.is-invalid {
            border-color: #d31111;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23d31111'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6.4.4.4-.4'/%3e%3cpath d='M6 7v1'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .valid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #198754;
        }

        .was-validated .form-control:valid,
        .form-control.is-valid {
            border-color: #198754;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        /* Estilos para el dropdown de reportes */
        .dropdown-menu .dropdown-header {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6c757d;
            padding: 0.5rem 1rem;
        }
        
        .reporte-item {
            cursor: pointer;
        }
        
        .dropdown-item:focus, .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #16181b;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4 text-center"><i class="fas fa-cookie-bite p-4"></i>Gestión de Productos</h1>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
            <div class="d-flex gap-2 mb-2 mb-md-0">
                <button class="btn btn-success me-2" id="btnNuevoProducto" style="background: linear-gradient(135deg, #d31111 0%, #c0392b 100%) !important; border: none !important; box-shadow: none !important;">
                    <i class="fas fa-plus-circle me-2"></i> Registrar Producto
                </button>
                
                <!-- Dropdown para Reportes -->
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownReportes" data-bs-toggle="dropdown" aria-expanded="false" style="background: linear-gradient(135deg, #d31111 0%, #c0392b 100%) !important; border: none !important; box-shadow: none !important;">
                        <i class="fas fa-file-pdf me-2"></i>Reportes
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="btnReportesProducto" style="z-index: 1060; background-color: #ffffff; border: 1px solid rgba(0,0,0,.15); border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,.175);">
                        <li><a class="dropdown-item" href="index.php?url=producto&action=inventario" target="_blank">
                            <i class="fas fa-warehouse me-2"></i> Inventario Completo
                        </a></li>
                        <li><a class="dropdown-item" href="index.php?url=producto&action=productos" target="_blank">
                            <i class="fas fa-list me-2"></i> Lista de Productos
                        </a></li>
                        <li><a class="dropdown-item" href="index.php?url=producto&action=bajo_stock" target="_blank">
                            <i class="fas fa-exclamation-triangle me-2"></i> Productos Bajo Stock
                        </a></li>
                        <li><a class="dropdown-item" href="index.php?url=producto&action=fuera_stock" target="_blank">
    <i class="fas fa-times-circle me-2"></i> Productos Fuera de Stock
</a></li>
                    </ul>
                </div>

            </div>
            
            <div class="d-flex gap-2">
                <button class="btn btn-warning" id="btnVerEliminados" style="background: linear-gradient(135deg, #d31111 0%, #c0392b 100%) !important; border: none !important; box-shadow: none !important;">
                    <i class="fas fa-trash-restore me-2"></i> Ver Inactivos
                </button>
            </div>
        </div>

        <!-- Filtros de búsqueda -->
        <div class="row mb-4 g-3">
            <!-- Búsqueda por texto -->
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchProduct" class="form-control" placeholder="Buscar por nombre o código...">
                    <button class="btn btn-outline-secondary" type="button" id="btnClearSearch" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Filtro por rango de precios -->
            <div class="col-md-6">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <label class="col-form-label">Precio:</label>
                    </div>
                    <div class="col">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" id="minPrice" class="form-control" placeholder="Mínimo" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="col-auto text-center">
                        <span>a</span>
                    </div>
                    <div class="col">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" id="maxPrice" class="form-control" placeholder="Máximo" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-secondary" type="button" id="btnClearPriceFilter">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Botón para aplicar filtros -->
            <div class="col-md-2">
                <button class="btn btn-primary w-100" id="btnApplyFilters">
                    <i class="fas fa-filter me-2"></i>Filtrar
                </button>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div class="loading-spinner spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>

        <!-- Product Grid -->
        <div class="row" id="product-grid">
            <!-- Product Cards will be loaded here -->
        </div>
    </div>

    <!-- Product Detail Modal -->
    <div class="modal fade" id="productDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="productDetailModalLabel"><i class="fas fa-info-circle me-2"></i>Detalles del Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img id="modal-product-image" 
                                 src="http://localhost/Natys/Assets/img/crash.png" 
                                 class="img-fluid rounded" 
                                 alt="Imagen no disponible"
                                 onerror="this.onerror=null; this.src='http://localhost/Natys/Assets/img/crash.png'"
                                 loading="lazy">
                        </div>
                        <div class="col-md-6">
                            <h3 id="modal-product-name"></h3>
                            <div class="d-flex justify-content-between align-items-center my-2">
                                <span class="product-price" id="modal-product-price"></span>
                                <span class="badge bg-secondary" id="modal-product-status"></span>
                            </div>
                            <p id="modal-product-code" class="text-muted"></p>
                            <p id="modal-product-unit" class="text-muted"></p>
                            <div class="d-flex justify-content-between align-items-center my-2">
                                <span class="stock-info" id="modal-product-stock"></span>
                            </div>
                            <div id="modal-product-description"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <div class="modal fade" id="productFormModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="productFormModalLabel">
                        <i class="fas fa-box me-2"></i> Registrar Producto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="form-container">
                    <!-- El formulario se cargará aquí mediante AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar el producto <strong id="delete-product-name"></strong>?</p>
                    <p class="text-muted">Esta acción cambiará el estado del producto a inactivo. Podrá restaurarlo posteriormente si es necesario.</p>
                    <input type="hidden" id="delete-product-id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmDelete">Eliminar Producto</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Reportes -->
    <div class="modal fade" id="reportesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-file-pdf me-2"></i>Generar Reportes
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="reportesModalBody">
                    <!-- El contenido de reportes se cargará aquí mediante AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="/Natys/Assets/js/producto.js"></script>
    
    <!-- Initialize Bootstrap dropdowns -->
    <script>
        // Enable Bootstrap dropdowns
        document.addEventListener('DOMContentLoaded', function() {
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