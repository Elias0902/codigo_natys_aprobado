<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Clientes</title>
    <!-- En la sección head -->
    <link rel="stylesheet" href="Assets/css/sidebar.css">
</head>
<body>

<style>
    
</style>

<!-- App/Views/partials/sidebar.php -->
<div class="sidebar bg-dark text-white" style="width: 250px; min-height: 100vh; position: fixed;">
    <div class="sidebar-header p-3 text-center">
        <h4>Natys</h4>
        <hr class="bg-light">
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="index.php?url=perfil" class="nav-link text-warning <?= strpos($_SERVER['REQUEST_URI'], 'perfil') !== false ? 'active bg-secondary' : '' ?>">
                <i class="fas fa-user me-2"></i>Perfil
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=cliente" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'cliente') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-users me-2"></i>Clientes
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=pedido" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'pedido') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-shopping-cart me-2"></i>Pedidos
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=producto" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'producto') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-boxes me-2"></i>Productos
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=pago" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'pago') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-money-bill-wave me-2"></i>Pagos
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=movimiento" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'movimiento') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-exchange-alt me-2"></i>Movimientos
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
}

/* Eliminar márgenes y padding por defecto del body */
body {
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}
</style>

</body>
</html>