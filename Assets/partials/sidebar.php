<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Clientes</title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="Assets/css/listar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- En la sección head -->
    <link rel="stylesheet" href="Assets/css/sidebar.css">
</head>
<body>

<!-- App/Views/partials/sidebar.php -->
<div class="sidebar bg-dark text-white" style="width: 250px; min-height: 100vh; position: fixed;">
    <div class="sidebar-header p-3 text-center">
        <h4>Natys System</h4>
        <hr class="bg-light">
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="index.php?url=cliente&action=listar" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'cliente') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-users me-2"></i>Clientes
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=pedido&action=listar" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'pedido') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-shopping-cart me-2"></i>Pedidos
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=producto&action=listar" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'producto') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-boxes me-2"></i>Productos
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=pago&action=listar" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'pago') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-money-bill-wave me-2"></i>Pagos
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?url=movimiento&action=listar" class="nav-link text-white <?= strpos($_SERVER['REQUEST_URI'], 'movimiento') !== false ? 'active bg-primary' : '' ?>">
                <i class="fas fa-exchange-alt me-2"></i>Movimientos
            </a>
        </li>
    </ul>
</div>

</body>
</html>