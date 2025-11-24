<?php
require_once 'App/Helpers/auth_check.php';
use App\Natys\Models\Cliente;
use App\Natys\Models\Pedido;
use App\Natys\Models\Producto;
use App\Natys\Models\Pago;


$totalClientes = 0;
$totalPedidos = 0;
$totalProductos = 0;
$productosBajoStock = 0;
$totalPagos = 0;
$pagosPendientes = 0;
$pedidosPendientes = 0;
$pedidosCompletados = 0;
$ultimosPedidos = [];

$action = $_REQUEST['action'] ?? 'index';


try {
    switch ($action) {
        case 'index':
            $cliente = new Cliente();
            $pedido = new Pedido();
            $producto = new Producto();
            $pago = new Pago();
            
            
            $totalClientes = $cliente->contarClientes();
            $totalPedidos = $pedido->contarPedidosActivos();
            $totalProductos = $producto->contarProductosTotales();
            $productosBajoStock = $producto->contarProductosBajoStock();
            
            
            $totalPagos = $pago->contarPagos();
            $pedidosPendientes = $pedido->contarPedidosPendientes();
            
            $pagosPendientes = $pedidosPendientes;
            $pedidosCompletados = $pedido->contarPedidosCompletados();
            $ultimosPedidos = $pedido->obtenerUltimosPedidos(5);
            
            
            $estadisticasMensuales = $pedido->obtenerEstadisticasMensuales();
            $meses = [];
            $pedidosPorMes = [];
            $ventasPorMes = [];
            
            
            for ($i = 5; $i >= 0; $i--) {
                $fecha = date('Y-m', strtotime("-$i months"));
                $meses[] = date('M Y', strtotime($fecha));
                $pedidosPorMes[] = $estadisticasMensuales[$fecha]['total_pedidos'] ?? 0;
                $ventasPorMes[] = $estadisticasMensuales[$fecha]['total_ventas'] ?? 0;
            }
            
            
            $totalVentas = 0;
            foreach ($estadisticasMensuales as $mes) {
                $totalVentas += $mes['total_ventas'] ?? 0;
            }
            
            
            $datosVista = [
                'totalClientes' => $totalClientes,
                'totalPedidos' => $totalPedidos + $pedidosCompletados,
                'totalProductos' => $totalProductos,
                'totalPagos' => $totalPagos,
                'pedidosPendientes' => $pedidosPendientes,
                'pedidosCompletados' => $pedidosCompletados,
                'productosBajoStock' => $productosBajoStock,
                'totalVentas' => $totalVentas,
                'meses' => $meses,
                'pedidosPorMes' => $pedidosPorMes,
                'ventasPorMes' => $ventasPorMes,
                'infoClientes' => "Clientes activos en el sistema",
                'infoPedidos' => "Total de pedidos activos (completados y pendientes)",
                'infoProductos' => "Total de productos activos en inventario",
                'infoPagos' => "Total de pagos registrados y confirmados",
                'infoPedidosPendientes' => "Pedidos solicitados que están pendientes de pago",
                'infoProductosBajoStock' => "Productos con stock por debajo del nivel mínimo"
            ];
            
            
            extract($datosVista);
            $infoPedidosPendientes = "Pedidos solicitados que están pendientes de pago";
            $infoProductosBajoStock = "Productos con stock por debajo del nivel mínimo";
            
            if (!file_exists('App/views/home/home.php')) {
                throw new Exception('La vista no existe: App/views/home/home.php');
            }
            
            include 'App/views/home/home.php';
            break;
            
        default:
            if (!file_exists('app/views/error/404.php')) {
                throw new Exception('La vista de error 404 no existe');
            }
            include 'app/views/error/404.php';
            break;
    }
} catch (Exception $e) {
    
    error_log('Error en homecontroller: ' . $e->getMessage());
    
    
    die('Ha ocurrido un error al cargar la página. Por favor, inténtalo de nuevo más tarde.');
}