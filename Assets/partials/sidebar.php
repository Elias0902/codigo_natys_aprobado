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
    <script>
        // Verificar el estado del sidebar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            const toggleBtn = document.querySelector('.sidebar-toggle');
            
            // Obtener el estado guardado o usar 'collapsed' por defecto
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            
            // Aplicar el estado inicial
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.remove('expanded');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.add('expanded');
            }
            
            // Manejar el clic en el botón de toggle
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                    
                    // Guardar el estado
                    const isNowCollapsed = sidebar.classList.contains('collapsed');
                    localStorage.setItem('sidebarCollapsed', isNowCollapsed);
                });
            }
        });
    </script>
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
<div class="sidebar bg-dark text-white">
    <div class="sidebar-header p-3 d-flex justify-content-between align-items-center">
        <h4 class="sidebar-title m-0">Natys</h4>
        <button class="btn btn-sm btn-outline-light sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    <hr class="bg-light m-0">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="index.php?url=perfil" class="nav-link text-warning <?= strpos($_SERVER['REQUEST_URI'], 'perfil') !== false ? 'active bg-secondary' : '' ?>">
                <i class="fas fa-user-circle"></i>
                <span class="ms-2">Perfil</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=cliente" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'cliente') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-user-tie"></i>
                <span class="ms-2">Clientes</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=pedido" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'pedido') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-clipboard-list"></i>
                <span class="ms-2">Pedidos</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=producto" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'producto') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-cookie-bite"></i>
                <span class="ms-2">Productos</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=pago" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'pago') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-credit-card"></i>
                <span class="ms-2">Pagos</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=movimiento" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'movimiento') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-random"></i>
                <span class="ms-2">Movimientos</span>
            </a>
        </li>
        <!-- Botón de Cerrar Sesión -->
        <li class="nav-item mt-auto">
            <a href="index.php?url=login&action=cerrarSesion" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span class="ms-2">Cerrar Sesión</span>
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
    transition: all 0.3s ease;
    overflow-x: hidden;
}

/* Estilo cuando el sidebar está colapsado */
.sidebar.collapsed {
    width: 70px;
}

.sidebar.collapsed .sidebar-title,
.sidebar.collapsed .nav-link span {
    display: none !important;
}

.sidebar.collapsed .nav-link {
    text-align: center;
    padding: 0.75rem 0.5rem !important;
    justify-content: center;
}

.sidebar.collapsed .nav-link i {
    margin: 0 !important;
    font-size: 1.2rem;
    display: inline-block;
}

.sidebar-toggle {
    transition: all 0.3s ease;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border-radius: 4px;
    border: 1px solid rgba(255,255,255,0.2);
}

.sidebar.collapsed .sidebar-toggle {
    margin: 0 auto;
}

.sidebar-header {
    padding: 1rem;
}

.sidebar .nav-link {
    padding: 0.75rem 1rem;
    margin: 0.15rem 0;
    border-radius: 0;
    transition: all 0.3s ease;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: flex;
    align-items: center;
}

.sidebar .nav-link i {
    width: 20px;
    text-align: center;
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

/* Los estilos de .main-content han sido movidos a layout.php */

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