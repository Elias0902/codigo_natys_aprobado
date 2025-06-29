<?php
// Al inicio del archivo listar.php
ob_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenida</title>
</head>
<body class="pagina">
<div class="flavor-header mb-4">
    <!-- Contenedor flexible para logo izquierdo y títulos centrados -->
    <div class="d-flex align-items-center justify-content-center position-relative">
        <!-- Logo alineado a la izquierda -->
        <div class="position-absolute" style="left: 0;">
            <img src="../Natys/Assets/img/Natys.png" 
                 class="rounded" 
                 alt="Logo Natys"
                 style="width: 150px; height: auto;">
        </div>
        
        <!-- Títulos centrados -->
        <div class="text-center">
            <h1 class="display-5 font-weight-bold text-primary mb-1">Sabores</h1>
            <h2 class="display-6 font-weight-bold text-secondary">Para todos los gustos</h2>
        </div>
    </div>
</div>

    <!-- Carrusel de sabores -->
    <div id="flavorCarousel" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
            <!-- Primer slide activo -->
            <div class="carousel-item active">
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <div class="flavor-card">
                            <img src="../Natys/Assets/img/img1.png" class="d-block w-100 rounded" alt="Chocolate CliccaKu">
                            <div class="flavor-info">
                                <h3 class="flavor-brand">Sabor a</h3>
                                <h4 class="flavor-name">Coco</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="flavor-card">
                            <img src="../Natys/Assets/img/img2.png" class="d-block w-100 rounded" alt="Fresa Natiy">
                            <div class="flavor-info">
                                <h3 class="flavor-brand">Sabor a</h3>
                                <h4 class="flavor-name">Choco King</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="flavor-card">
                            <img src="../Natys/Assets/img/img3.png" class="d-block w-100 rounded" alt="Vainilla CliccaKu">
                            <div class="flavor-info">
                                <h3 class="flavor-brand">Sabor a</h3>
                                <h4 class="flavor-name">Polvorosa</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </a>
    </div>
</div>


  <!-- Sección de testimonios -->
    <div class="row bg-light py-5 rounded mb-5">
        <div class="col-12 text-center mb-4">
            <h2 class="display-5">Lo que dicen nuestros clientes</h2>
        </div>
        <div class="col-md-4">
            <div class="testimonial p-4">
                <div class="stars mb-2">★★★★★</div>
                <p>"La mejor galleta que he probado en mi vida. ¡Siempre vuelvo por más!"</p>
                <div class="font-weight-bold">- María G.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="testimonial p-4">
                <div class="stars mb-2">★★★★☆</div>
                <p>"La variedad de sabores es increíble. Mis hijos lo aman."</p>
                <div class="font-weight-bold">- Carlos R.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="testimonial p-4">
                <div class="stars mb-2">★★★★★</div>
                <p>"Calidad premium a precios accesibles. Mi lugar favorito."</p>
                <div class="font-weight-bold">- Laura M.</div>
            </div>
        </div>
    </div>
    
    <!-- Llamado a la acción -->
    <div class="row bg-primary text-white p-5 rounded mb-5">
        <div class="col-md-8">
            <h3>¿Listo para probar una experiencia galleta única?</h3>
            <p class="lead">Visítanos hoy y disfruta de nuestro 10% de descuento en tu primer pedido.</p>
        </div>
        <div class="col-md-4 text-right d-flex align-items-center justify-content-end">
        </div>
    </div>
</div>




<style>
    .flavor-carousel-container {
        padding: 3rem 0;
        background: linear-gradient(to bottom, #fff5f5, #ffffff);
    }
    
    .flavor-card {
        position: relative;
        margin: 15px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        border-radius: 15px;
        overflow: hidden;
        transition: transform 0.3s ease;
    }
    
    .flavor-card:hover {
        transform: translateY(-10px);
    }
    
    .flavor-info {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 15px;
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
    }
    
    .flavor-brand {
        font-size: 1.2rem;
        font-weight: bold;
        margin-bottom: 0.3rem;
        color: #ff9a9e;
    }
    
    .flavor-name {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 0;
    }
    
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: #ff6b81;
        border-radius: 50%;
        padding: 20px;
    }

</style>
<?php
// Al final del archivo listar.php
$content = ob_get_clean();

include 'Assets/layouts/base.php';