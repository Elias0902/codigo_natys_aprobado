<?php
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de Usuario - Natys</title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --dark-color: #5a5c69;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .page-title {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            padding-top: 15px;
            font-size: 1.8rem;
        }
        
        .manual-container {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 2rem;
        }
        
        .btn-download {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-download:hover {
            background-color: #2e59d9;
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .pdf-viewer {
            width: 100%;
            height: 800px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-top: 1.5rem;
        }
        
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        @media (max-width: 768px) {
            .pdf-viewer {
                height: 600px;
            }
            
            .header-section {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="manual-container">
            <div class="header-section">
                <div>
                    <h1 class="page-title mb-0">
                        <i class="fas fa-book me-2"></i>Manual de Usuario
                    </h1>
                    <p class="text-muted mb-0">Consulta el manual completo del sistema</p>
                </div>
                <div>
                    <a href="/Natys/Assets/manuales/manualUsuario.pdf" 
                       download 
                       class="btn-download">
                        <i class="fas fa-download me-2"></i>Descargar Manual
                    </a>
                </div>
            </div>
            
            <!-- Visor de PDF -->
            <div class="pdf-container">
                <embed 
                    src="/Natys/Assets/manuales/manualUsuario.pdf" 
                    type="application/pdf" 
                    class="pdf-viewer"
                    title="Manual de Usuario">
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$content = ob_get_clean();
include 'Assets/layouts/base.php';
?>
