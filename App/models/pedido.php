<?php
namespace App\Natys\Models;

use App\Natys\config\connect\Conexion;
use PDO;
use Exception;

class Pedido extends Conexion {
    public function __construct() {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    public function listar($estado = null) {
        $query = "SELECT p.id_pedido, p.fecha, p.total, p.cant_producto, 
                 c.nomcliente, c.ced_cliente, p.estado
                 FROM pedido p
                 JOIN cliente c ON p.ced_cliente = c.ced_cliente
                 WHERE (:estado IS NULL OR p.estado = :estado)
                 ORDER BY p.fecha DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $estado, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearPedido($ced_cliente, $productos, $total, $cant_producto) {
        $this->conn->beginTransaction();
        try {
            // 1. Validar que el cliente existe
            $queryCliente = "SELECT COUNT(*) FROM cliente WHERE ced_cliente = :ced_cliente AND estado = 1";
            $stmtCliente = $this->conn->prepare($queryCliente);
            $stmtCliente->bindParam(":ced_cliente", $ced_cliente);
            $stmtCliente->execute();
            
            if ($stmtCliente->fetchColumn() == 0) {
                throw new Exception('El cliente especificado no existe o está inactivo');
            }

            // 2. Obtener método de pago por defecto (EFECTIVO)
            $cod_metodo = 'EFECTIVO';
            
            $queryMetodo = "SELECT COUNT(*) FROM metodo WHERE codigo = :cod_metodo AND estado = 1";
            $stmtMetodo = $this->conn->prepare($queryMetodo);
            $stmtMetodo->bindParam(":cod_metodo", $cod_metodo);
            $stmtMetodo->execute();
            
            if ($stmtMetodo->fetchColumn() == 0) {
                throw new Exception('El método de pago especificado no existe o está inactivo');
            }

            // 3. Crear el pago
            $queryPago = "INSERT INTO pago (fecha, monto, cod_metodo, estado) 
                          VALUES (CURDATE(), :monto, :cod_metodo, 1)";
            $stmtPago = $this->conn->prepare($queryPago);
            $stmtPago->bindParam(":monto", $total);
            $stmtPago->bindParam(":cod_metodo", $cod_metodo);
            
            if (!$stmtPago->execute()) {
                $error = $stmtPago->errorInfo();
                throw new Exception('Error al crear el pago: ' . ($error[2] ?? 'Error desconocido'));
            }
            $id_pago = $this->conn->lastInsertId();

            // 4. Crear el pedido con estado 0 (por pagar)
            $queryPedido = "INSERT INTO pedido (fecha, total, cant_producto, ced_cliente, id_pago, estado)
                           VALUES (CURDATE(), :total, :cant_producto, :ced_cliente, :id_pago, 0)";
            $stmtPedido = $this->conn->prepare($queryPedido);
            $stmtPedido->bindParam(":total", $total);
            $stmtPedido->bindParam(":cant_producto", $cant_producto);
            $stmtPedido->bindParam(":ced_cliente", $ced_cliente);
            $stmtPedido->bindParam(":id_pago", $id_pago);
            
            if (!$stmtPedido->execute()) {
                $error = $stmtPedido->errorInfo();
                throw new Exception('Error al crear el pedido: ' . ($error[2] ?? 'Error desconocido'));
            }
            $id_pedido = $this->conn->lastInsertId();

            // 5. Agregar productos al detalle
            foreach ($productos as $index => $prod) {
                $queryProducto = "SELECT COUNT(*) FROM producto WHERE cod_producto = :cod_producto AND estado = 1";
                $stmtProducto = $this->conn->prepare($queryProducto);
                $stmtProducto->bindParam(":cod_producto", $prod['cod_producto']);
                $stmtProducto->execute();
                
                if ($stmtProducto->fetchColumn() == 0) {
                    throw new Exception('El producto ' . $prod['cod_producto'] . ' no existe o está inactivo');
                }

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
            }

            $this->conn->commit();
            return $id_pedido;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error al crear pedido: " . $e->getMessage());
            throw $e;
        }
    }

    public function marcarComoPagado($id_pedido) {
        try {
            $query = "UPDATE pedido SET estado = 1 WHERE id_pedido = :id_pedido";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_pedido", $id_pedido);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al marcar como pagado: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarPedido($id_pedido, $ced_cliente, $productos, $total, $cant_producto) {
        $this->conn->beginTransaction();
        try {
            // 1. Validar que el pedido existe
            $queryPedido = "SELECT id_pedido, id_pago FROM pedido WHERE id_pedido = :id_pedido";
            $stmtPedido = $this->conn->prepare($queryPedido);
            $stmtPedido->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
            $stmtPedido->execute();
            
            if ($stmtPedido->rowCount() === 0) {
                throw new Exception('El pedido especificado no existe');
            }
            
            $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);
            $id_pago = $pedido['id_pago'];

            // 2. Validar que el cliente existe
            $queryCliente = "SELECT COUNT(*) FROM cliente WHERE ced_cliente = :ced_cliente AND estado = 1";
            $stmtCliente = $this->conn->prepare($queryCliente);
            $stmtCliente->bindParam(":ced_cliente", $ced_cliente);
            $stmtCliente->execute();
            
            if ($stmtCliente->fetchColumn() == 0) {
                throw new Exception('El cliente especificado no existe o está inactivo');
            }

            // 3. Actualizar el pago
            $queryActualizarPago = "UPDATE pago SET monto = :monto WHERE id_pago = :id_pago";
            $stmtPago = $this->conn->prepare($queryActualizarPago);
            $stmtPago->bindParam(":monto", $total);
            $stmtPago->bindParam(":id_pago", $id_pago);
            
            if ($pedido['estado'] != 0) {
                throw new Exception('Solo se pueden editar pedidos pendientes de pago');
            }
            
            // 2. Verificar que el cliente existe
            $queryCliente = "SELECT COUNT(*) FROM cliente WHERE ced_cliente = :ced_cliente AND estado = 1";
            $stmtCliente = $this->conn->prepare($queryCliente);
            $stmtCliente->bindParam(":ced_cliente", $ced_cliente);
            $stmtCliente->execute();
            
            if ($stmtCliente->fetchColumn() == 0) {
                throw new Exception('El cliente especificado no existe o está inactivo');
            }
            
            // 3. Actualizar información básica del pedido
            $query = "UPDATE pedido SET 
                     ced_cliente = :ced_cliente,
                     total = :total,
                     cant_producto = :cant_producto,
                     fecha_actualizacion = NOW()
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
            
            // 4. Eliminar productos antiguos
            $queryDelete = "DELETE FROM detalle_pedido WHERE id_pedido = :id_pedido";
            $stmtDelete = $this->conn->prepare($queryDelete);
            $stmtDelete->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
            
            if (!$stmtDelete->execute()) {
                $error = $stmtDelete->errorInfo();
                throw new Exception('Error al eliminar productos anteriores: ' . ($error[2] ?? 'Error desconocido'));
            }
            
            // 5. Insertar nuevos productos
            $queryInsert = "INSERT INTO detalle_pedido 
                          (id_pedido, cod_producto, precio, cantidad, subtotal)
                          VALUES (:id_pedido, :cod_producto, :precio, :cantidad, :subtotal)";
            
            $stmtInsert = $this->conn->prepare($queryInsert);
            $productosInsertados = 0;
            
            foreach ($productos as $producto) {
                // Validar que el producto existe y está activo
                $queryProducto = "SELECT COUNT(*) FROM producto WHERE cod_producto = :cod_producto AND estado = 1";
                $stmtProducto = $this->conn->prepare($queryProducto);
                $stmtProducto->bindParam(":cod_producto", $producto['cod_producto']);
                $stmtProducto->execute();
                
                if ($stmtProducto->fetchColumn() == 0) {
                    throw new Exception('El producto ' . $producto['cod_producto'] . ' no existe o está inactivo');
                }
                
                // Calcular subtotal
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
            
            // Verificar que se hayan insertado productos
            if ($productosInsertados === 0) {
                throw new Exception('Debe incluir al menos un producto en el pedido');
            }
            
            // 6. Actualizar el total en la tabla de pagos
            $queryPago = "UPDATE pago p 
                         JOIN pedido pe ON p.id_pago = pe.id_pago 
                         SET p.monto = :total 
                         WHERE pe.id_pedido = :id_pedido";
            
            $stmtPago = $this->conn->prepare($queryPago);
            $stmtPago->bindParam(":total", $total, PDO::PARAM_STR);
            $stmtPago->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
            
            if (!$stmtPago->execute()) {
                $error = $stmtPago->errorInfo();
                throw new Exception('Error al actualizar el pago: ' . ($error[2] ?? 'Error desconocido'));
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error al actualizar pedido #$id_pedido: " . $e->getMessage());
            throw $e; // Relanzar la excepción para manejarla en el controlador
        }    
    }

    public function obtenerDetalle($id_pedido) {
        try {
            // 1. Obtener información básica del pedido
            $queryPedido = "SELECT p.*, c.nomcliente as nombre_cliente 
                          FROM pedido p 
                          JOIN cliente c ON p.ced_cliente = c.ced_cliente 
                          WHERE p.id_pedido = :id_pedido";
            
            $stmtPedido = $this->conn->prepare($queryPedido);
            $stmtPedido->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
            $stmtPedido->execute();
            
            if ($stmtPedido->rowCount() === 0) {
                return null;
            }
            
            $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);
            
            // 2. Obtener los detalles del pedido
            $queryDetalle = "SELECT d.*, pr.nombre as nombre_producto, pr.precio 
                           FROM detalle_pedido d 
                           JOIN producto pr ON d.cod_producto = pr.cod_producto 
                           WHERE d.id_pedido = :id_pedido";
            
            $stmtDetalle = $this->conn->prepare($queryDetalle);
            $stmtDetalle->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
            $stmtDetalle->execute();
            $detalles = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
            
            // 3. Calcular totales
            $total = 0;
            foreach ($detalles as &$detalle) {
                $detalle['subtotal'] = $detalle['cantidad'] * $detalle['precio'];
                $total += $detalle['subtotal'];
            }
            
            // 4. Estructurar la respuesta
            return [
                'pedido' => array_merge($pedido, [
                    'total' => $total,
                    'metodo_pago' => 'No especificado',
                    'referencia' => 'N/A',
                    'banco' => 'N/A'
                ]),
                'detalle' => $detalles
            ];
        } catch (\Exception $e) {
            error_log("Error en obtenerDetalle: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }
    }

    public function eliminar($id_pedido) {
        $this->conn->beginTransaction();
        try {
            // Primero eliminamos los detalles
            $queryDeleteDetalle = "DELETE FROM detalle_pedido WHERE id_pedido = :id_pedido";
            $stmtDetalle = $this->conn->prepare($queryDeleteDetalle);
            $stmtDetalle->bindParam(":id_pedido", $id_pedido);
            $stmtDetalle->execute();
            
            // Luego eliminamos el pedido
            $queryDeletePedido = "DELETE FROM pedido WHERE id_pedido = :id_pedido";
            $stmtPedido = $this->conn->prepare($queryDeletePedido);
            $stmtPedido->bindParam(":id_pedido", $id_pedido);
            $stmtPedido->execute();
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error al eliminar pedido: " . $e->getMessage());
            return false;
        }
    }
}