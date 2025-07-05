<?php
// Iniciar el búfer de salida para capturar el contenido de las vistas
ob_start();
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Natys - Panel de Control' ?></title>
    
    <!-- Hojas de estilo -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/Natys/Assets/css/sidebar.css">
    
    <!-- Estilos adicionales específicos de la página -->
    <?php if (isset($styles)): ?>
        <?php foreach ($styles as $style): ?>
            <link rel="stylesheet" href="<?= $style ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <style>
        /* Estilos generales */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        /* Transiciones suaves */
        a, button, .btn {
            transition: all 0.3s ease;
        }
        
        /* Ajustes para el contenido principal */
        .main-content {
            padding: 20px;
            transition: all 0.3s ease;
            margin-left: 250px; /* Asegura que el contenido no se oculte detrás del sidebar */
            width: calc(100% - 250px); /* Ajusta el ancho para que no se desborde */
        }
        
        /* Estilos para móviles */
        @media (max-width: 992px) {
            .main-content {
                padding: 15px;
                margin-left: 0 !important;
                width: 100% !important;
            }
            
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="d-flex">
    <!-- Incluir el sidebar -->
    <?php include dirname(__DIR__, 2) . '\Assets\partials\sidebar.php'; ?>
    
    <!-- Contenido principal -->
    <main class="main-content" id="main-content">
        <?php 
        // Mostrar mensajes de éxito/error si existen
        if (isset($_SESSION['success_message'])): 
        ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <!-- Aquí se incluirá el contenido específico de cada vista -->
        <?= $content ?? '' ?>
    </main>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/Natys/Assets/js/mobile-menu.js"></script>
    
    <!-- Scripts adicionales específicos de la página -->
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
    // Función para cambiar entre temas
    function setTheme(theme) {
        const html = document.documentElement;
        const themeToggle = document.getElementById('themeToggle');
        
        if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.setAttribute('data-bs-theme', 'dark');
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            localStorage.setItem('theme', 'dark');
        } else {
            html.setAttribute('data-bs-theme', 'light');
            themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            localStorage.setItem('theme', 'light');
        }
    }
    
    // Aplicar el tema guardado o el del sistema
    document.addEventListener('DOMContentLoaded', function() {
        // Manejar el cambio de tema
        const themeToggle = document.getElementById('themeToggle');
        
        // Cargar tema guardado o usar el del sistema
        const savedTheme = localStorage.getItem('theme') || 'system';
        setTheme(savedTheme);
        
        // Alternar tema al hacer clic en el botón
        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            setTheme(currentTheme === 'dark' ? 'light' : 'dark');
        });
        
        // Escuchar cambios en la preferencia del sistema
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (localStorage.getItem('theme') === 'system') {
                setTheme('system');
            }
        });
        
        // Inicializar tooltips de Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Inicializar popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    });
    </script>
</body>
</html>
