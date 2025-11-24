<?php
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Natys</title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --secondary-color: #858796;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding-bottom: 60px; /* Espacio para navegación móvil */
        }
        
        .page-title {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1rem;
            padding-top: 15px;
            font-size: 1.5rem;
        }
        
        /* Estilos mejorados para las tarjetas de estadísticas */
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: none;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.8rem 1.5rem rgba(0, 0, 0, 0.15);
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin: 15px 0 10px;
            text-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .stat-card .stat-label {
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0;
        }
        
        .stat-card .stat-icon {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 3rem;
            opacity: 0.2;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover .stat-icon {
            transform: scale(1.1);
            opacity: 0.3;
        }
        
        /* Estilos para los tooltips */
        .tooltip {
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .tooltip-inner {
            max-width: 250px;
            padding: 0.5rem 1rem;
        }
        
        /* Estilos específicos para cada tarjeta */
        .bg-primary { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important; }
        .bg-success { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%) !important; }
        .bg-warning { background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%) !important; }
        .bg-info { background: linear-gradient(135deg, #36b9cc 0%, #258391 100%) !important; }
        .bg-danger { background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%) !important; }
        
        .card-primary {
            border-left: 4px solid var(--primary-color);
        }
        
        .card-success {
            border-left: 4px solid var(--success-color);
        }
        
        .card-info {
            border-left: 4px solid var(--info-color);
        }
        
        .card-warning {
            border-left: 4px solid var(--warning-color);
        }
        
        .card-danger {
            border-left: 4px solid var(--danger-color);
        }
        
        .text-primary { color: var(--primary-color) !important; }
        .text-success { color: var(--success-color) !important; }
        .text-info { color: var(--info-color) !important; }
        .text-warning { color: var(--warning-color) !important; }
        .text-danger { color: var(--danger-color) !important; }
        
        .chart-container {
            position: relative;
            height: 220px;
        }
        
        .badge-pending {
            background-color: #f8f9fc;
            color: var(--dark-color);
            border: 1px solid #d1d3e2;
        }
        
        .badge-paid {
            background-color: var(--success-color);
            color: white;
        }
        
        .badge-cancelled {
            background-color: var(--danger-color);
            color: white;
        }
        
        .dashboard-section {
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e3e6f0;
        }
        
        /* Navegación móvil - Eliminada para usar solo el menú de hamburguesa */
        
        /* Contenedor fijo para la gráfica */
        .chart-container {
            position: relative;
            height: 200px;
            width: 100%;
            margin: 20px 0;
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        
        /* Tarjetas de actividad */
        .activity-card {
            border-radius: 8px;
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .activity-desc {
            font-size: 0.85rem;
            color: var(--secondary-color);
        }
        
        /* Estilos para los mini contadores */
        .mini-stat {
            background: white;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            height: 100%;
        }
        
        .mini-stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
            min-height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .counter-value {
            display: inline-block;
            min-width: 20px;
        }
        
        .mini-stat-label {
            font-size: 0.8rem;
            color: var(--secondary-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Quick actions */
        .quick-action {
            background: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        
        .quick-action:active {
            transform: scale(0.95);
        }
        
        .quick-action-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 1.2rem;
        }
        
        .quick-action-title {
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        /* Estadísticas rápidas */
        .mini-stat {
            background: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .mini-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .mini-stat-label {
            font-size: 0.75rem;
            color: var(--secondary-color);
            text-transform: uppercase;
        }
        
        @media (max-width: 768px) {
            .container-fluid {
                padding-left: 15px;
                padding-right: 15px;
            }
            
            .page-title {
                font-size: 1.4rem;
                padding-top: 10px;
            }
            
            .stat-value {
                font-size: 1.3rem;
            }
            
            .chart-container {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-3">
        <h1 class="page-title">Dashboard - Resumen General</h1>
        
        <div class="row dashboard-section">
            <!-- Tarjeta de Clientes -->
            <div class="col-6 col-md-3 mb-4">
            <a href="index.php?url=cliente" style="text-decoration: none;">  
            <div class="stat-card bg-primary position-relative">
                    <div class="stat-value"><?= $totalClientes ?></div>
                    <div class="stat-label">
                        Clientes
                        <i class="fas fa-info-circle ms-2" data-bs-toggle="tooltip" title="<?= $infoClientes ?>"></i>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                </a> 
            </div>
            
            <!-- Tarjeta de Pedidos -->
            <div class="col-6 col-md-3 mb-4">
            <a href="index.php?url=pedido" style="text-decoration: none;">  
                <div class="stat-card bg-success position-relative">
                    <div class="stat-value"><?= $totalPedidos + $pedidosCompletados ?></div>
                    <div class="stat-label">
                        Pedidos
                        <i class="fas fa-info-circle ms-2" data-bs-toggle="tooltip" title="<?= $infoPedidos ?>"></i>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
                </a>
            </div>
            
            <!-- Tarjeta de Productos -->
            <div class="col-6 col-md-3 mb-4">
            <a href="index.php?url=producto" style="text-decoration: none;">  
                <div class="stat-card bg-warning position-relative">
                    <div class="stat-value"><?= $totalProductos ?></div>
                    <div class="stat-label">
                        Productos
                        <i class="fas fa-info-circle ms-2" data-bs-toggle="tooltip" title="<?= $infoProductos ?>"></i>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
                </a>
            </div>
            
            <!-- Tarjeta de Pagos -->
            <div class="col-6 col-md-3 mb-4">
            <a href="index.php?url=pago" style="text-decoration: none;">  
                <div class="stat-card bg-info position-relative">
                    <div class="stat-value"><?= $totalPagos ?></div>
                    <div class="stat-label">
                        Pagos
                        <i class="fas fa-info-circle ms-2" data-bs-toggle="tooltip" title="<?= $infoPagos ?>"></i>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
               
            </a>
            </div>
        </div>
        

        
        <!-- Mensajes de depuración -->
        <div class="d-none">
            <p>Debug - Pedidos Pendientes: <?= $pedidosPendientes ?></p>
            <p>Debug - Pedidos Completados: <?= $pedidosCompletados ?></p>
            <p>Debug - Pagos Pendientes: <?= $pagosPendientes ?></p>
            <p>Debug - Productos Bajo Stock: <?= $productosBajoStock ?></p>
        </div>
        
        <!-- Sección de Gráficas -->
        <div class="row dashboard-section">
            <!-- Gráfica de Estado de Pedidos -->
            <div class="col-12 col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Estado de Pedidos</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="pedidosChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gráfica de Tendencias Mensuales -->
            <div class="col-12 col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Tendencias Mensuales</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="tendenciasChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas Rápidas -->
        <div class="row dashboard-section">
            <div class="col-12">
                <h6 class="section-title">Resumen de Estado</h6>
                <div class="row">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="mini-stat position-relative">
                            <div class="mini-stat-value text-primary"><span class="counter-value"><?= $pedidosPendientes ?></span></div>
                            <div class="mini-stat-label">
                                Pedidos Pendientes
                                <i class="fas fa-info-circle ms-2" data-bs-toggle="tooltip" title="<?= $infoPedidosPendientes ?>"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="mini-stat position-relative">
                            <div class="mini-stat-value text-success"><span class="counter-value"><?= $pedidosCompletados ?></span></div>
                            <div class="mini-stat-label">
                                Pedidos Completados
                                <i class="fas fa-info-circle ms-2" data-bs-toggle="tooltip" title="Pedidos que han sido pagados y completados"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="mini-stat position-relative">
                            <div class="mini-stat-value text-danger"><span class="counter-value"><?= $productosBajoStock ?></span></div>
                            <div class="mini-stat-label">
                                Productos Bajo Stock
                                <i class="fas fa-info-circle ms-2" data-bs-toggle="tooltip" title="<?= $infoProductosBajoStock ?>"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="mini-stat position-relative">
                            <div class="mini-stat-value text-info"><span class="counter-value">$<?= number_format($totalVentas, 2) ?></span></div>
                            <div class="mini-stat-label">
                                Ventas Totales
                                <i class="fas fa-info-circle ms-2" data-bs-toggle="tooltip" title="Total de ventas en los últimos 12 meses"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        

    </div>

    <!-- Menú móvil eliminado para usar solo el menú de hamburguesa -->

    <!-- Scripts necesarios -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Inicialización cuando el DOM esté completamente cargado
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover',
                    placement: 'top',
                    container: 'body',
                    animation: true,
                    delay: { "show": 100, "hide": 50 }
                });
            });
            
            // Actualizar el contador de pedidos para incluir los completados
            const totalPedidos = <?= $totalPedidos + $pedidosCompletados ?>;
            const pedidosElement = document.querySelector('.stat-card.bg-success .stat-value');
            if (pedidosElement) {
                pedidosElement.textContent = totalPedidos;
            }
            
            // Gráfico de Estado de Pedidos
            const pedidosCtx = document.getElementById('pedidosChart').getContext('2d');
            const completados = <?= $pedidosCompletados ?? 0 ?>;
            const pendientes = <?= $pedidosPendientes ?? 0 ?>;
            const anulados = <?= $pedidosAnulados ?? 0 ?>;
            
            // Calcular total y porcentajes
            const total = completados + pendientes + anulados;
            const porcentajeCompletados = total > 0 ? Math.round((completados / total) * 100) : 0;
            const porcentajePendientes = total > 0 ? Math.round((pendientes / total) * 100) : 0;
            const porcentajeAnulados = total > 0 ? 100 - porcentajeCompletados - porcentajePendientes : 0;
            
            // Gráfico de Estado de Pedidos
            new Chart(pedidosCtx, {
                type: 'doughnut',
                data: {
                    labels: [
                        `Completados (${porcentajeCompletados}%)`,
                        `Pendientes (${porcentajePendientes}%)`,
                        `Anulados (${porcentajeAnulados}%)`
                    ],
                    datasets: [{
                        data: [completados, pendientes, anulados],
                        backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    cutout: '70%',
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    size: 12
                                }
                            }
                        }
                    },
                    maintainAspectRatio: false,
                    responsive: true,
                    layout: {
                        padding: 10
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
            
            // Gráfico de Tendencias Mensuales
            const tendenciasCtx = document.getElementById('tendenciasChart').getContext('2d');
            const meses = <?= json_encode($meses) ?>;
            const pedidosPorMes = <?= json_encode($pedidosPorMes) ?>;
            const ventasPorMes = <?= json_encode($ventasPorMes) ?>;
            
            // Crear un array de colores para las barras
            const coloresBarras = Array(pedidosPorMes.length).fill('#4e73df').map((color, index) => 
                `hsl(${210 + (index * 30) % 150}, 70%, 60%)`
            );
            
            new Chart(tendenciasCtx, {
                type: 'bar',
                data: {
                    labels: meses,
                    datasets: [
                        {
                            label: 'Pedidos',
                            data: pedidosPorMes,
                            backgroundColor: coloresBarras,
                            borderColor: coloresBarras.map(c => c.replace('0.8', '1')),
                            borderWidth: 1,
                            borderRadius: 4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Ventas ($)',
                            data: ventasPorMes,
                            type: 'line',
                            borderColor: '#1cc88a',
                            backgroundColor: 'rgba(28, 200, 138, 0.1)',
                            borderWidth: 2,
                            pointBackgroundColor: '#1cc88a',
                            pointBorderColor: '#fff',
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: '#1cc88a',
                            pointHoverBorderColor: '#fff',
                            pointHitRadius: 10,
                            pointBorderWidth: 2,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Cantidad de Pedidos'
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Ventas ($)'
                            },
                            grid: {
                                drawOnChartArea: false
                            },
                            // Configuración para mostrar valores en formato de moneda
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label === 'Ventas ($)') {
                                        return `${label}: $${context.raw.toLocaleString()}`;
                                    }
                                    return `${label}: ${context.raw}`;
                                }
                            }
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        });
        
        // Forzar la actualización de tooltips cuando cambia el tamaño de la ventana
        window.addEventListener('resize', function() {
            const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltips.forEach(function(tooltip) {
                const instance = bootstrap.Tooltip.getInstance(tooltip);
                if (instance) {
                    instance.hide();
                    instance.dispose();
                }
                new bootstrap.Tooltip(tooltip, {
                    trigger: 'hover',
                    placement: 'top',
                    container: 'body'
                });
            });
        });
    </script>
</body>
</html>
<?php
$content = ob_get_clean();
include 'Assets/layouts/base.php';