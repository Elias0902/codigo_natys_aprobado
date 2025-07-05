<?php
// Iniciar el búfer de salida
ob_start();
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/Natys/Assets/css/sidebar.css">
    <style>
        :root {
            --primary-color: #cc1d1d;
            --sidebar-bg: #343a40;
            --sidebar-text: #e9ecef;
            --sidebar-hover: #495057;
            
            /* Tema claro fijo */
            --body-bg: #f8f9fa;
            --card-bg: #ffffff;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --border-color: #dee2e6;
            --table-bg: #ffffff;
            --table-text: #212529;
            --table-border: #dee2e6;
            --table-hover: rgba(0,0,0,.075);
        }
        
        body {
            background-color: var(--body-bg);
            color: var(--text-primary);
            transition: all 0.3s ease;
        }
        
        /* Tarjetas */
        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            transition: all 0.3s ease;
        }
        
        /* Tablas */
        .table {
            color: var(--table-text);
            background-color: var(--table-bg);
            border-color: var(--table-border);
        }
        
        .table th,
        .table td {
            border-color: var(--table-border);
        }
        
        .table-hover tbody tr:hover {
            background-color: var(--table-hover);
            color: var(--table-text);
        }
        
        /* Formularios */
        .form-control, .form-select {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        
        .form-control:focus, .form-select:focus {
            background-color: var(--card-bg);
            color: var(--text-primary);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(204, 29, 29, 0.25);
        }
        
        /* Modales */
        .modal-content {
            background-color: var(--card-bg);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        
        .modal-header, .modal-footer {
            border-color: var(--border-color);
        }
        
        .text-muted {
            color: var(--text-secondary) !important;
        }
        

    </style>
</head>
<body>

<style>
    
</style>

<!-- App/Views/partials/sidebar.php -->
<div class="sidebar bg-dark text-white" style="width: 250px; height: 100vh; position: fixed; left: 0; top: 0; overflow-y: auto;">
    <div class="sidebar-header p-3 text-center">
        <h4>Natys</h4>
        <hr class="bg-light">
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="index.php?url=perfil" class="nav-link text-warning <?= strpos($_SERVER['REQUEST_URI'], 'perfil') !== false ? 'active bg-secondary' : '' ?>">
                <i class="fas fa-user-circle me-2"></i>Perfil
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=cliente" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'cliente') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-user-tie me-2"></i>Clientes
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=pedido" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'pedido') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-clipboard-list me-2"></i>Pedidos
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=producto" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'producto') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-cookie-bite me-2"></i>Productos
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=pago" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'pago') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-credit-card me-2"></i>Pagos
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=movimiento" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'movimiento') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-random me-2"></i>Movimientos
            </a>
        </li>
        <!-- Botón de Cerrar Sesión -->
        <li class="nav-item mt-auto">
            <a href="index.php?url=login&action=cerrarSesion" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
            </a>
        </li>
    </ul>
</div>

<style>
    /* Assets/css/sidebar.css */
.sidebar {
    width: 250px;
    min-height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 1000;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.sidebar-header {
    padding: 1rem;
}

.sidebar .nav-link {
    padding: 0.75rem 1rem;
    margin: 0.15rem 0;
    border-radius: 0;
    transition: all 0.3s;
}

.sidebar .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    padding-left: 1.25rem;
}

.sidebar .nav-link.active {
    font-weight: bold;
    background-color: #0d6efd !important;
}

/* Estilo específico para el botón de cerrar sesión */
.sidebar .nav-link.text-danger:hover {
    background-color: rgba(220, 53, 69, 0.2);
}

.main-content {
    margin-left: 250px;
    width: calc(100% - 250px);
    padding: 20px;
    transition: all 0.3s;
    min-height: 100vh;
}

/* Estilos del body */
body {
    margin: 0;
    padding: 0;
    overflow-x: hidden;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}
</style>

</body>
</html>