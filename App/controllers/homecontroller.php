<?php
// Al inicio del archivo listar.php
ob_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="row">
        <div class="col-md-12 text-center">
            <h1>NATYS</h1>
            <p class="lead"></p>
            
            <div class="mt-4">
                <a href="?url=cliente&type=listar" class="btn btn-primary me-2">
                    <i class="fas fa-users"></i> Clientes
                </a>
                <a href="?url=producto&type=listar" class="btn btn-success me-2">
                    <i class="fas fa-boxes"></i> Productos
                </a>
                <a href="?url=pedido&type=listar" class="btn btn-warning me-2">
                    <i class="fas fa-shopping-cart"></i> Pedidos
                </a>
                <a href="?url=pago&type=listar" class="btn btn-danger">
                    <i class="fas fa-money-bill-wave"></i> Pagos
                </a>
                <a href="?url=movimiento&type=listar" class="btn btn-info me-2">
                    <i class="fas fa-arrow-down"></i> Movimientos Entrada
                </a>
            </div>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Al final del archivo listar.php
$content = ob_get_clean();

include 'Assets/layouts/base.php';