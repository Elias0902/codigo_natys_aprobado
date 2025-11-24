<?php
namespace App\Natys\Models;

use App\Natys\config\connect\Conexion;
use PDO;
use Exception;

class Pedido extends Conexion {
    private $id_pedido;
    private $fecha;
    private $total;
    private $cant_producto;
    private $ced_cliente;
    private $id_pago;
    private $estado;
    protected $conn;

    public function __construct() {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    // Método para asignar datos de forma encapsulada
    public function setData($data) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function getGuardar($data) {
        $this->setData($data);
        return $this->guardar();
    }

    private function guardar() {
        try {
            $query = "INSERT INTO pedido (fecha, total, cant_producto, ced_cliente, estado) 
                     VALUES (:fecha, :total, :cant_producto, :ced_cliente, :estado)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":fecha", $this->fecha);
            $stmt->bindParam(":total", $this->total);
            $stmt->bindParam(":cant_producto", $this->cant_producto);
            $stmt->bindParam(":ced_cliente", $this->ced_cliente);
            $stmt->bindParam(":estado", $this->estado, \PDO::PARAM_INT);
            if ($stmt->execute()) {
                $this->id_pedido = $this->conn->lastInsertId();
                return $this->id_pedido;
            }
            return false;
        } catch (\Exception $e) {
            error_log("Error en Pedido::guardar(): " . $e->getMessage());
            return false;
        }
    }

    public function actualizar() {
        try {
            $query = "UPDATE pedido SET 
                     fecha = :fecha, 
                     total = :total, 
                     cant_producto = :cant_producto, 
                     ced_cliente = :ced_cliente,
                     id_pago = :id_pago,
                     estado = :estado
                     WHERE id_pedido = :id_pedido";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":fecha", $this->fecha);
            $stmt->bindParam(":total", $this->total);
            $stmt->bindParam(":cant_producto", $this->cant_producto);
            $stmt->bindParam(":ced_cliente", $this->ced_cliente);
            $stmt->bindParam(":id_pago", $this->id_pago);
            $stmt->bindParam(":estado", $this->estado, PDO::PARAM_INT);
            $stmt->bindParam(":id_pedido", $this->id_pedido, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en Pedido::actualizar(): " . $e->getMessage());
            return false;
        }
    }

    public function asignarPago($id_pedido, $id_pago) {
        try {
            $query = "UPDATE pedido SET id_pago = :id_pago WHERE id_pedido = :id_pedido";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_pago", $id_pago, PDO::PARAM_INT);
            $stmt->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en asignarPago: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPorCliente($cedula_cliente) {
        try {
            error_log("Iniciando obtenerPorCliente para cédula: " . $cedula_cliente);
            
            // Verificar la conexión
            if (!$this->conn) {
                error_log("Error: No hay conexión a la base de datos");
                return [];
            }
            
            $query = "SELECT p.*, 
                             DATE_FORMAT(p.fecha, '%d/%m/%Y %h:%i %p') as fecha_formateada,
                             CASE 
                                 WHEN p.estado = 0 THEN 'Pendiente'
                                 WHEN p.estado = 1 THEN 'Completado'
                                 WHEN p.estado = 2 THEN 'Cancelado'
                                 ELSE 'Desconocido'
                             END as estado_texto
                      FROM pedido p 
                      WHERE p.ced_cliente = :cedula_cliente 
                      ORDER BY p.fecha DESC";
                      
            error_log("Consulta SQL: " . $query);
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                $error = $this->conn->errorInfo();
                error_log("Error al preparar la consulta: " . print_r($error, true));
                return [];
            }
            
            $stmt->bindParam(":cedula_cliente", $cedula_cliente, PDO::PARAM_STR);
            
            if (!$stmt->execute()) {
                $error = $stmt->errorInfo();
                error_log("Error al ejecutar la consulta: " . print_r($error, true));
                return [];
            }
            
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Resultados de la consulta: " . print_r($resultados, true));
            
            return $resultados;
        } catch (Exception $e) {
            error_log("Excepción en obtenerPorCliente: " . $e->getMessage());
            error_log("Trace: " . $e->getTraceAsString());
            return [];
        }
    }

    public function obtener($id_pedido) {
        try {
            $query = "SELECT * FROM pedido WHERE id_pedido = :id_pedido LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
            $stmt->execute();
            
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($pedido) {
                $this->id_pedido = $pedido['id_pedido'];
                $this->fecha = $pedido['fecha'];
                $this->total = $pedido['total'];
                $this->cant_producto = $pedido['cant_producto'];
                $this->ced_cliente = $pedido['ced_cliente'];
                $this->id_pago = $pedido['id_pago'];
                $this->estado = $pedido['estado'];
            }
            
            return $pedido;
        } catch (Exception $e) {
            error_log("Error en Pedido::obtener(): " . $e->getMessage());
            return false;
        }
    }

    public function listar($estado = null) {
        $query = "SELECT p.id_pedido, p.fecha, p.total, p.cant_producto, 
                 c.nomcliente, c.ced_cliente, p.estado,
                 (SELECT pr.nombre 
                  FROM detalle_pedido dp 
                  JOIN producto pr ON dp.cod_producto = pr.cod_producto 
                  WHERE dp.id_pedido = p.id_pedido 
                  LIMIT 1) as nombre_producto
                 FROM pedido p
                 JOIN cliente c ON p.ced_cliente = c.ced_cliente
                 WHERE (:estado IS NULL OR p.estado = :estado)
                 ORDER BY p.fecha DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $estado, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function listarPorFechas($fechaInicio, $fechaFin, $estado = null) {
        $query = "SELECT p.id_pedido, p.fecha, p.total, p.cant_producto, 
                 c.nomcliente, c.ced_cliente, p.estado,
                 (SELECT pr.nombre 
                  FROM detalle_pedido dp 
                  JOIN producto pr ON dp.cod_producto = pr.cod_producto 
                  WHERE dp.id_pedido = p.id_pedido 
                  LIMIT 1) as nombre_producto
                 FROM pedido p
                 JOIN cliente c ON p.ced_cliente = c.ced_cliente
                 WHERE DATE(p.fecha) BETWEEN :fechaInicio AND :fechaFin
                 AND (:estado IS NULL OR p.estado = :estado)
                 ORDER BY p.fecha DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fechaInicio", $fechaInicio);
        $stmt->bindParam(":fechaFin", $fechaFin);
        $stmt->bindParam(":estado", $estado, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearPedido($ced_cliente, $productos, $total, $cant_producto) {
        $this->conn->beginTransaction();
        try {
            
            $queryCliente = "SELECT COUNT(*) FROM cliente WHERE ced_cliente = :ced_cliente AND estado = 1";
            $stmtCliente = $this->conn->prepare($queryCliente);
            $stmtCliente->bindParam(":ced_cliente", $ced_cliente);
            $stmtCliente->execute();
            
            if ($stmtCliente->fetchColumn() == 0) {
                throw new Exception('El cliente especificado no existe o está inactivo');
            }

            
            $productoModel = new \App\Natys\Models\Producto();
            foreach ($productos as $index => $prod) {
                $stockActual = $productoModel->obtenerStockProducto($prod['cod_producto']);
                if ($prod['cantidad'] > $stockActual) {
                    throw new Exception('Stock insuficiente para el producto ' . $prod['cod_producto'] . '. Stock disponible: ' . $stockActual . ', solicitado: ' . $prod['cantidad']);
                }

                $queryProducto = "SELECT COUNT(*) FROM producto WHERE cod_producto = :cod_producto AND estado = 1";
                $stmtProducto = $this->conn->prepare($queryProducto);
                $stmtProducto->bindParam(":cod_producto", $prod['cod_producto']);
                $stmtProducto->execute();
                
                if ($stmtProducto->fetchColumn() == 0) {
                    throw new Exception('El producto ' . $prod['cod_producto'] . ' no existe o está inactivo');
                }
            }

            
            $queryPedido = "INSERT INTO pedido (fecha, total, cant_producto, ced_cliente, estado)
                           VALUES (CURDATE(), :total, :cant_producto, :ced_cliente, 0)";
            $stmtPedido = $this->conn->prepare($queryPedido);
            $stmtPedido->bindParam(":total", $total);
            $stmtPedido->bindParam(":cant_producto", $cant_producto);
            $stmtPedido->bindParam(":ced_cliente", $ced_cliente);
            
            if (!$stmtPedido->execute()) {
                $error = $stmtPedido->errorInfo();
                throw new Exception('Error al crear el pedido: ' . ($error[2] ?? 'Error desconocido'));
            }
            $id_pedido = $this->conn->lastInsertId();

            
            $observacion = 'Salida por pedido #' . $id_pedido;
            $queryMovimientoEntrada = "INSERT INTO movimiento_entrada (fecha, observaciones, estado) 
                                     VALUES (CURDATE(), :observacion, 1)";
            $stmtMovimientoEntrada = $this->conn->prepare($queryMovimientoEntrada);
            $stmtMovimientoEntrada->bindParam(":observacion", $observacion);
            
            if (!$stmtMovimientoEntrada->execute()) {
                $error = $stmtMovimientoEntrada->errorInfo();
                throw new Exception('Error al crear el movimiento de inventario: ' . ($error[2] ?? 'Error desconocido'));
            }
            $num_movimiento = $this->conn->lastInsertId();

            
            foreach ($productos as $index => $prod) {
                
                $queryDetalle = "INSERT INTO detalle_pedido 
                                (id_pedido, cod_producto, precio, cantidad, subtotal)
                                VALUES (:id_pedido, :cod_producto, :precio, :cantidad, :subtotal)";
                $stmtDetalle = $this->conn->prepare($queryDetalle);
                $stmtDetalle->bindParam(":id_pedido", $id_pedido);
                $stmtDetalle->bindParam(":cod_producto", $prod['cod_producto']);
                $stmtDetalle->bindParam(":precio", $prod['precio']);
                $stmtDetalle->bindParam(":cantidad", $prod['cantidad']);
                $stmtDetalle->bindParam(":subtotal", $prod['subtotal']);
                
                if (!$stmtDetalle->execute()) {
                    $error = $stmtDetalle->errorInfo();
                    throw new Exception('Error al agregar el producto ' . $prod['cod_producto'] . ': ' . ($error[2] ?? 'Error desconocido'));
                }

                
                
                $cantidadNegativa = -1 * $prod['cantidad'];
                $queryMovimiento = "INSERT INTO detalle_movimiento 
                                  (num_movimiento, cod_producto, cant_productos, precio_venta, estado)
                                  VALUES (:num_movimiento, :cod_producto, :cantidad, :precio, 1)";
                $stmtMovimiento = $this->conn->prepare($queryMovimiento);
                
                $stmtMovimiento->bindParam(":num_movimiento", $num_movimiento);
                $stmtMovimiento->bindParam(":cod_producto", $prod['cod_producto']);
                $stmtMovimiento->bindParam(":cantidad", $cantidadNegativa);
                $stmtMovimiento->bindParam(":precio", $prod['precio']);
                
                if (!$stmtMovimiento->execute()) {
                    $error = $stmtMovimiento->errorInfo();
                    throw new Exception('Error al actualizar el stock para el producto ' . $prod['cod_producto'] . ': ' . ($error[2] ?? 'Error desconocido'));
                }
            }

            $this->conn->commit();
            return $id_pedido;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error al crear pedido: " . $e->getMessage());
            throw $e;
        }
    }

    public function obtenerPedidosPendientes() {
        try {
            $query = "SELECT p.id_pedido, p.fecha, p.total, p.cant_producto, 
                     c.nomcliente, c.ced_cliente
                     FROM pedido p
                     JOIN cliente c ON p.ced_cliente = c.ced_cliente
                     WHERE p.estado = 0
                     ORDER BY p.fecha ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            
            foreach ($pedidos as &$pedido) {
                $queryProductos = "SELECT dp.cod_producto, pr.nombre, dp.cantidad, dp.precio, dp.subtotal
                                 FROM detalle_pedido dp
                                 JOIN producto pr ON dp.cod_producto = pr.cod_producto
                                 WHERE dp.id_pedido = :id_pedido";
                
                $stmtProductos = $this->conn->prepare($queryProductos);
                $stmtProductos->bindParam(":id_pedido", $pedido['id_pedido']);
                $stmtProductos->execute();
                
                $pedido['productos'] = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $pedidos;
            
        } catch (Exception $e) {
            error_log("Error al obtener pedidos pendientes: " . $e->getMessage());
            return false;
        }
    }

    public function marcarComoPagado($id_pedido) {
        $this->conn->beginTransaction();
        try {
            
            $queryDetalle = "SELECT dp.cod_producto, dp.cantidad, dp.precio
                             FROM detalle_pedido dp
                             WHERE dp.id_pedido = :id_pedido";
            $stmtDetalle = $this->conn->prepare($queryDetalle);
            $stmtDetalle->bindParam(":id_pedido", $id_pedido);
            $stmtDetalle->execute();
            $detalles = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

            if (empty($detalles)) {
                throw new Exception('No se encontraron detalles para el pedido');
            }

            
            $productoModel = new \App\Natys\Models\Producto();
            foreach ($detalles as $detalle) {
                $stockActual = $productoModel->obtenerStockProducto($detalle['cod_producto']);
                if ($detalle['cantidad'] > $stockActual) {
                    throw new Exception('Stock insuficiente para el producto ' . $detalle['cod_producto'] . '. Stock actual: ' . $stockActual);
                }
            }

            
            $queryUpdate = "UPDATE pedido SET estado = 1 WHERE id_pedido = :id_pedido";
            $stmtUpdate = $this->conn->prepare($queryUpdate);
            $stmtUpdate->bindParam(":id_pedido", $id_pedido);
            if (!$stmtUpdate->execute()) {
                throw new Exception('Error al marcar el pedido como pagado');
            }

            
            $observaciones = "Pedido #" . $id_pedido;
            $querySalida = "INSERT INTO movimiento_salida (fecha, observaciones, estado) VALUES (CURDATE(), :observaciones, 1)";
            $stmtSalida = $this->conn->prepare($querySalida);
            $stmtSalida->bindParam(":observaciones", $observaciones);
            if (!$stmtSalida->execute()) {
                throw new Exception('Error al crear movimiento de salida');
            }
            $num_salida = $this->conn->lastInsertId();

            
            $queryDetalleSalida = "INSERT INTO detalle_salida (num_salida, cod_producto, cant_productos, precio_venta, estado)
                                   VALUES (:num_salida, :cod_producto, :cant_productos, :precio_venta, 1)";
            $stmtDetalleSalida = $this->conn->prepare($queryDetalleSalida);

            foreach ($detalles as $detalle) {
                $stmtDetalleSalida->bindParam(":num_salida", $num_salida);
                $stmtDetalleSalida->bindParam(":cod_producto", $detalle['cod_producto']);
                $stmtDetalleSalida->bindParam(":cant_productos", $detalle['cantidad']);
                $stmtDetalleSalida->bindParam(":precio_venta", $detalle['precio']);
                if (!$stmtDetalleSalida->execute()) {
                    throw new Exception('Error al agregar detalle de salida para producto ' . $detalle['cod_producto']);
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error al marcar como pagado: " . $e->getMessage());
            throw $e; 
        }
    }

    public function actualizarPedido($id_pedido, $ced_cliente, $productos, $total, $cant_producto) {
        $this->conn->beginTransaction();
        try {
            
            $queryPedido = "SELECT id_pedido, id_pago, estado FROM pedido WHERE id_pedido = :id_pedido";
            $stmtPedido = $this->conn->prepare($queryPedido);
            $stmtPedido->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
            $stmtPedido->execute();
            
            if ($stmtPedido->rowCount() === 0) {
                throw new Exception('El pedido especificado no existe');
            }
            
            $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);

            if ($pedido['estado'] != 0) {
                throw new Exception('Solo se pueden editar pedidos pendientes de pago');
            }
            
            
            $queryCliente = "SELECT COUNT(*) FROM cliente WHERE ced_cliente = :ced_cliente AND estado = 1";
            $stmtCliente = $this->conn->prepare($queryCliente);
            $stmtCliente->bindParam(":ced_cliente", $ced_cliente);
            $stmtCliente->execute();
            
            if ($stmtCliente->fetchColumn() == 0) {
                throw new Exception('El cliente especificado no existe o está inactivo');
            }

            
            $productoModel = new \App\Natys\Models\Producto();
            foreach ($productos as $index => $producto) {
                $stockActual = $productoModel->obtenerStockProducto($producto['cod_producto']);
                if ($producto['cantidad'] > $stockActual) {
                    throw new Exception('Stock insuficiente para el producto ' . $producto['cod_producto'] . '. Stock disponible: ' . $stockActual . ', solicitado: ' . $producto['cantidad']);
                }

                
                $queryProducto = "SELECT COUNT(*) FROM producto WHERE cod_producto = :cod_producto AND estado = 1";
                $stmtProducto = $this->conn->prepare($queryProducto);
                $stmtProducto->bindParam(":cod_producto", $producto['cod_producto']);
                $stmtProducto->execute();
                
                if ($stmtProducto->fetchColumn() == 0) {
                    throw new Exception('El producto ' . $producto['cod_producto'] . ' no existe o está inactivo');
                }
            }
            
            
            $query = "UPDATE pedido SET
                     ced_cliente = :ced_cliente,
                     total = :total,
                     cant_producto = :cant_producto
                     WHERE id_pedido = :id_pedido";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":ced_cliente", $ced_cliente);
            $stmt->bindParam(":total", $total, PDO::PARAM_STR);
            $stmt->bindParam(":cant_producto", $cant_producto, PDO::PARAM_INT);
            $stmt->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $error = $stmt->errorInfo();
                throw new Exception('Error al actualizar el pedido: ' . ($error[2] ?? 'Error desconocido'));
            }
            
            
            $queryDelete = "DELETE FROM detalle_pedido WHERE id_pedido = :id_pedido";
            $stmtDelete = $this->conn->prepare($queryDelete);
            $stmtDelete->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
            
            if (!$stmtDelete->execute()) {
                $error = $stmtDelete->errorInfo();
                throw new Exception('Error al eliminar productos anteriores: ' . ($error[2] ?? 'Error desconocido'));
            }
            
            
            $queryInsert = "INSERT INTO detalle_pedido
                          (id_pedido, cod_producto, precio, cantidad, subtotal)
                          VALUES (:id_pedido, :cod_producto, :precio, :cantidad, :subtotal)";
            
            $stmtInsert = $this->conn->prepare($queryInsert);
            $productosInsertados = 0;
            
            foreach ($productos as $producto) {
                $subtotal = $producto['precio'] * $producto['cantidad'];
                
                $stmtInsert->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
                $stmtInsert->bindParam(":cod_producto", $producto['cod_producto']);
                $stmtInsert->bindParam(":precio", $producto['precio'], PDO::PARAM_STR);
                $stmtInsert->bindParam(":cantidad", $producto['cantidad'], PDO::PARAM_INT);
                $stmtInsert->bindParam(":subtotal", $subtotal, PDO::PARAM_STR);
                
                if (!$stmtInsert->execute()) {
                    $error = $stmtInsert->errorInfo();
                    throw new Exception('Error al actualizar los productos del pedido: ' . ($error[2] ?? 'Error desconocido'));
                }
                
                $productosInsertados++;
            }
            
            if ($productosInsertados === 0) {
                throw new Exception('Debe incluir al menos un producto en el pedido');
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error al actualizar pedido #$id_pedido: " . $e->getMessage());
            throw $e;
        }
    }

    public function obtenerDetalle($id_pedido) {
        try {
            
            $queryPedido = "SELECT 
                p.*, 
                c.ced_cliente,
                c.nomcliente as nombre_cliente,
                c.telefono as telefono_cliente,
                c.direccion as direccion_cliente,
                c.correo as email_cliente,
                pa.monto as monto_pagado,
                m.detalle as metodo_pago,
                pa.fecha as fecha_pago,
                pa.referencia as referencia_pago
            FROM pedido p 
            JOIN cliente c ON p.ced_cliente = c.ced_cliente 
            LEFT JOIN pago pa ON p.id_pago = pa.id_pago
            LEFT JOIN metodo m ON pa.cod_metodo = m.codigo
            WHERE p.id_pedido = :id_pedido";
            
            $stmtPedido = $this->conn->prepare($queryPedido);
            $stmtPedido->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
            $stmtPedido->execute();
            
            if ($stmtPedido->rowCount() === 0) {
                return null;
            }
            
            $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);
            
            
            $queryDetalle = "SELECT 
                d.*, 
                pr.nombre as nombre_producto, 
                pr.precio as precio_unitario,
                (d.cantidad * d.precio) as subtotal
            FROM detalle_pedido d 
            JOIN producto pr ON d.cod_producto = pr.cod_producto 
            WHERE d.id_pedido = :id_pedido";
            
            $stmtDetalle = $this->conn->prepare($queryDetalle);
            $stmtDetalle->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
            $stmtDetalle->execute();
            $detalles = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
            
            
            $subtotal = 0;
            $impuestos = 0;
            $total = 0;
            
            foreach ($detalles as &$detalle) {
                $detalle['subtotal'] = $detalle['cantidad'] * $detalle['precio'];
                $subtotal += $detalle['subtotal'];
            }
            
            $total = $subtotal + $impuestos;
            
            
            $fecha_creacion = new \DateTime($pedido['fecha']);
            $fecha_pago = !empty($pedido['fecha_pago']) ? new \DateTime($pedido['fecha_pago']) : null;
            
            
            $resultado = [
                'pedido' => [
                    'id_pedido' => (int)$pedido['id_pedido'],
                    'fecha_creacion' => $fecha_creacion->format('Y-m-d'),
                    'fecha_creacion_formatted' => $fecha_creacion->format('d/m/Y'),
                    'total' => (float)$pedido['total'],
                    'total_formatted' => number_format($pedido['total'], 2, ',', '.'),
                    'cant_producto' => (int)$pedido['cant_producto'],
                    'estado' => (int)$pedido['estado'],
                    'estado_texto' => $pedido['estado'] == 1 ? 'Pagado' : ($pedido['estado'] == 2 ? 'Cancelado' : 'Pendiente de pago'),
                    'id_pago' => $pedido['id_pago'] ? (int)$pedido['id_pago'] : null,
                    'metodo_pago' => $pedido['metodo_pago'] ?? 'No especificado',
                    'referencia_pago' => $pedido['referencia_pago'] ?? 'N/A',
                    'fecha_pago' => $fecha_pago ? $fecha_pago->format('Y-m-d H:i:s') : null,
                    'fecha_pago_formatted' => $fecha_pago ? $fecha_pago->format('d/m/Y H:i') : 'Pendiente'
                ],
                'cliente' => [
                    'cedula' => $pedido['ced_cliente'],
                    'nombre' => $pedido['nombre_cliente'],
                    'nombre_completo' => $pedido['nombre_cliente'] ?? '',
                    'telefono' => $pedido['telefono_cliente'] ?? 'No especificado',
                    'direccion' => $pedido['direccion_cliente'] ?? 'No especificada',
                    'email' => $pedido['email_cliente'] ?? 'No especificado'
                ],
                'detalles' => array_map(function($detalle) {
                    return [
                        'cod_producto' => $detalle['cod_producto'],
                        'nombre_producto' => $detalle['nombre_producto'],
                        'cantidad' => (int)$detalle['cantidad'],
                        'precio_unitario' => (float)$detalle['precio_unitario'],
                        'precio_unitario_formatted' => number_format($detalle['precio_unitario'], 2, ',', '.'),
                        'subtotal' => (float)$detalle['subtotal'],
                        'subtotal_formatted' => number_format($detalle['subtotal'], 2, ',', '.')
                    ];
                }, $detalles),
                'resumen' => [
                    'subtotal' => (float)$subtotal,
                    'subtotal_formatted' => number_format($subtotal, 2, ',', '.'),
                    'impuestos' => (float)$impuestos,
                    'impuestos_formatted' => number_format($impuestos, 2, ',', '.'),
                    'total' => (float)$total,
                    'total_formatted' => number_format($total, 2, ',', '.'),
                    'total_pagado' => (float)($pedido['monto_pagado'] ?? 0),
                    'total_pagado_formatted' => isset($pedido['monto_pagado']) ? number_format($pedido['monto_pagado'], 2, ',', '.') : '0,00',
                    'pendiente_pago' => (float)($total - ($pedido['monto_pagado'] ?? 0)),
                    'pendiente_pago_formatted' => number_format($total - ($pedido['monto_pagado'] ?? 0), 2, ',', '.')
                ]
            ];
            return $resultado;
            
        } catch (\Exception $e) {
            error_log("Error en obtenerDetalle: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }
    }



    // Eliminar pedido aprobado
    public function eliminarPedido($id_pedido) {
        try {
            // Verificar que el pedido existe y está aprobado
            $queryPedido = "SELECT estado FROM pedido WHERE id_pedido = :id_pedido";
            $stmtPedido = $this->conn->prepare($queryPedido);
            $stmtPedido->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
            $stmtPedido->execute();

            if ($stmtPedido->rowCount() === 0) {
                throw new Exception('El pedido especificado no existe');
            }

            $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);

            if ($pedido['estado'] != 1) {
                throw new Exception('Solo se pueden eliminar pedidos aprobados');
            }

            // ELIMINAR completamente el pedido y sus detalles
            $queryDeleteDetalles = "DELETE FROM detalle_pedido WHERE id_pedido = :id_pedido";
            $stmtDeleteDetalles = $this->conn->prepare($queryDeleteDetalles);
            $stmtDeleteDetalles->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);

            if (!$stmtDeleteDetalles->execute()) {
                throw new Exception('Error al eliminar detalles del pedido');
            }

            $queryDeletePedido = "DELETE FROM pedido WHERE id_pedido = :id_pedido";
            $stmtDeletePedido = $this->conn->prepare($queryDeletePedido);
            $stmtDeletePedido->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);

            if (!$stmtDeletePedido->execute()) {
                throw new Exception('Error al eliminar el pedido');
            }

            return true;

        } catch (Exception $e) {
            error_log("Error al eliminar pedido #$id_pedido: " . $e->getMessage());
            throw $e;
        }
    }

    public function contarPedidosPendientes() {
        $query = "SELECT COUNT(*) as total FROM pedido WHERE estado = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function contarPedidosActivos() {
        $query = "SELECT COUNT(*) as total FROM pedido WHERE estado = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function contarPedidosCompletados() {
        $query = "SELECT COUNT(*) as total FROM pedido WHERE estado = 1"; 
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    public function obtenerUltimosPedidos($limite = 5) {
        $query = "SELECT p.id_pedido, p.fecha, p.total, p.estado, c.nomcliente 
                 FROM pedido p 
                 JOIN cliente c ON p.ced_cliente = c.ced_cliente 
                 ORDER BY p.fecha DESC 
                 LIMIT :limite";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":limite", (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEstadisticasMensuales() {
        $estadisticas = [];
        
        
        for ($i = 11; $i >= 0; $i--) {
            $fecha = date('Y-m', strtotime("-$i months"));
            $estadisticas[$fecha] = [
                'total_pedidos' => 0,
                'total_ventas' => 0
            ];
        }
        
        
        $query = "SELECT 
                    DATE_FORMAT(fecha, '%Y-%m') as mes,
                    COUNT(*) as total_pedidos,
                    COALESCE(SUM(total), 0) as total_ventas
                  FROM pedido
                  WHERE (estado = 1 OR estado = 2) AND fecha >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                  GROUP BY mes
                  ORDER BY mes ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        
        foreach ($resultados as $fila) {
            if (isset($estadisticas[$fila['mes']])) {
                $estadisticas[$fila['mes']] = [
                    'total_pedidos' => (int)$fila['total_pedidos'],
                    'total_ventas' => (float)$fila['total_ventas']
                ];
            }
        }
        
        return $estadisticas;
    }
}