<?php
ob_start();
?>

<div class="container text-center py-5">
    <h1><i class="fas fa-exclamation-triangle text-danger"></i> 404</h1>
    <p class="lead">La página que buscas no existe.</p>
    <a href="?url=home" class="btn btn-primary mt-3">
        <i class="fas fa-home"></i> Volver al inicio
    </a>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title;
include '../Assets/layouts/base.php';