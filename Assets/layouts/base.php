<!-- App/Views/layouts/base.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'SISTEMA DE GALLETAS NATYS' ?></title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <!-- Bootstrap CSS local -->
    <link href="/Natys/Assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome local -->
    <link href="/Natys/Assets/fontawesome/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS local -->
    <link href="/Natys/Assets/DataTables/datatables.min.css" rel="stylesheet">
    <!-- Toastr CSS local -->
    <link href="/Natys/Assets/toastr/toastr.min.css" rel="stylesheet">
    
    <!-- Estilos adicionales específicos de la vista -->
    <?php if (isset($css)): ?>
        <link rel="stylesheet" href="<?= $css ?>">
    <?php endif; ?>
</head>
<body>
    <div class="d-flex">
        <?php include '../Natys/Assets/partials/sidebar.php'; ?>
        
        <div class="main-content" style="margin-left: 250px; width: calc(100% - 250px); padding: 20px;">
            <?= $content ?>
        </div>
    </div>

    <!-- jQuery local -->
    <script src="/Natys/Assets/jquery/jquery.min.js"></script>
    <!-- Bootstrap JS local -->
    <script src="/Natys/Assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS local -->
    <script src="/Natys/Assets/DataTables/datatables.min.js"></script>
    <!-- Toastr JS local -->
    <script src="/Natys/Assets/toastr/toastr.min.js"></script>
    <!-- Bootbox JS local -->
    <script src="/Natys/Assets/bootbox/bootbox.min.js"></script>
    
    <!-- Scripts adicionales específicos de la vista -->
    <?php if (isset($js)): ?>
        <script src="<?= $js ?>"></script>
    <?php endif; ?>
</body>
</html>