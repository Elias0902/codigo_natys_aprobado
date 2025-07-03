<?php
namespace App\Natys\Models;

use App\Natys\config\connect\Conexion;
use PDO;

class Pedido extends Conexion {
    public $id_pedido;
    public $fecha;
    public $total;
    public $cant_producto;
    public $ced_cliente;
<<<<<<< HEAD
    public $id_pago;
=======
>>>>>>> 76976821448ffa84dd34d6f8e93d11b37a9bd82f
    public $estado;

    public function __construct() {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    public function guardar($detalles = []) {
        try {
            $this->conn->beginTransaction();
            
<<<<<<< HEAD
            // Insertar pedido
            $query = "INSERT INTO pedido (fecha, total, cant_producto, ced_cliente, id_pago) 
                      VALUES (:fecha, :total, :cant_producto, :ced_cliente, :id_pago)";
=======
            $query = "INSERT INTO pedido (fecha, total, cant_producto, ced_cliente) 
                      VALUES (:fecha, :total, :cant_producto, :ced_cliente)";
>>>>>>> 76976821448ffa84dd34d6f8e93d11b37a9bd82f
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":fecha", $this->fecha);
            $stmt->bindParam(":total", $this->total);
            $stmt->bindParam(":cant_producto", $this->cant_producto);
            $stmt->bindParam(":ced_cliente", $this->ced_cliente);
<<<<<<< HEAD
            $stmt->bindParam(":id_pago", $this->id_pago);
=======
>>>>>>> 76976821448ffa84dd34d6f8e93d11b37a9bd82f
            $stmt->execute();
            
            $this->id_pedido = $this->conn->lastInsertId();
            
<<<<<<< HEAD
            // Insertar detalles
=======
>>>>>>> 76976821448ffa84dd34d6f8e93d11b37a9bd82f
            foreach ($detalles as $detalle) {
                $query = "INSERT INTO detalle_pedido (id_pedido, cod_producto, precio, cantidad, subtotal) 
                          VALUES (:id_pedido, :cod_producto, :precio, :cantidad, :subtotal)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":id_pedido", $this->id_pedido);
                $stmt->bindParam(":cod_producto", $detalle['cod_producto']);
                $stmt->bindParam(":precio", $detalle['precio']);
                $stmt->bindParam(":cantidad", $detalle['cantidad']);
                $stmt->bindParam(":subtotal", $detalle['subtotal']);
                $stmt->execute();
            }
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error al guardar pedido: " . $e->getMessage());
            return false;
        }
    }

    public function listar() {
<<<<<<< HEAD
        $query = "SELECT p.*, c.nomcliente, m.detalle as metodo_pago 
                  FROM pedido p
                  JOIN cliente c ON p.ced_cliente = c.ced_cliente
                  JOIN pago pg ON p.id_pago = pg.id_pago
                  JOIN metodo m ON pg.cod_metodo = m.codigo
=======
        $query = "SELECT p.*, c.nomcliente
                  FROM pedido p
                  JOIN cliente c ON p.ced_cliente = c.ced_cliente
>>>>>>> 76976821448ffa84dd34d6f8e93d11b37a9bd82f
                  WHERE p.estado = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPedido($id) {
<<<<<<< HEAD
        $query = "SELECT p.*, c.nomcliente, pg.banco, pg.referencia, pg.monto, pg.cod_metodo 
                  FROM pedido p
                  JOIN cliente c ON p.ced_cliente = c.ced_cliente
                  JOIN pago pg ON p.id_pago = pg.id_pago
=======
        $query = "SELECT p.*, c.nomcliente
                  FROM pedido p
                  JOIN cliente c ON p.ced_cliente = c.ced_cliente
>>>>>>> 76976821448ffa84dd34d6f8e93d11b37a9bd82f
                  WHERE p.id_pedido = :id_pedido LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_pedido", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerDetalles($id) {
        $query = "SELECT dp.*, pr.nombre as producto 
                  FROM detalle_pedido dp
                  JOIN producto pr ON dp.cod_producto = pr.cod_producto
                  WHERE dp.id_pedido = :id_pedido";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_pedido", $id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizar($detalles = []) {
        try {
            $this->conn->beginTransaction();
            
<<<<<<< HEAD
            // Actualizar pedido
=======
>>>>>>> 76976821448ffa84dd34d6f8e93d11b37a9bd82f
            $query = "UPDATE pedido SET 
                      fecha = :fecha, 
                      total = :total, 
                      cant_producto = :cant_producto, 
<<<<<<< HEAD
                      ced_cliente = :ced_cliente, 
                      id_pago = :id_pago
=======
                      ced_cliente = :ced_cliente
>>>>>>> 76976821448ffa84dd34d6f8e93d11b37a9bd82f
                      WHERE id_pedido = :id_pedido";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":fecha", $this->fecha);
            $stmt->bindParam(":total", $this->total);
            $stmt->bindParam(":cant_producto", $this->cant_producto);
            $stmt->bindParam(":ced_cliente", $this->ced_cliente);
<<<<<<< HEAD
            $stmt->bindParam(":id_pago", $this->id_pago);
            $stmt->bindParam(":id_pedido", $this->id_pedido);
            $stmt->execute();
            
            // Eliminar detalles antiguos
=======
            $stmt->bindParam(":id_pedido", $this->id_pedido);
            $stmt->execute();
            
>>>>>>> 76976821448ffa84dd34d6f8e93d11b37a9bd82f
            $query = "DELETE FROM detalle_pedido WHERE id_pedido = :id_pedido";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_pedido", $this->id_pedido);
            $stmt->execute();
            
<<<<<<< HEAD
            // Insertar nuevos detalles
=======
>>>>>>> 76976821448ffa84dd34d6f8e93d11b37a9bd82f
            foreach ($detalles as $detalle) {
                $query = "INSERT INTO detalle_pedido (id_pedido, cod_producto, precio, cantidad, subtotal) 
                          VALUES (:id_pedido, :cod_producto, :precio, :cantidad, :subtotal)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":id_pedido", $this->id_pedido);
                $stmt->bindParam(":cod_producto", $detalle['cod_producto']);
                $stmt->bindParam(":precio", $detalle['precio']);
                $stmt->bindParam(":cantidad", $detalle['cantidad']);
                $stmt->bindParam(":subtotal", $detalle['subtotal']);
                $stmt->execute();
            }
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error al actualizar pedido: " . $e->getMessage());
            return false;
        }
    }

    public function eliminar() {
        $query = "UPDATE pedido SET estado = 0 WHERE id_pedido = :id_pedido";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_pedido", $this->id_pedido);
        return $stmt->execute();
    }

    public function restaurar() {
        $query = "UPDATE pedido SET estado = 1 WHERE id_pedido = :id_pedido";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_pedido", $this->id_pedido);
        return $stmt->execute();
    }

    public function listarEliminados() {
<<<<<<< HEAD
        $query = "SELECT p.*, c.nomcliente, m.detalle as metodo_pago 
                  FROM pedido p
                  JOIN cliente c ON p.ced_cliente = c.ced_cliente
                  JOIN pago pg ON p.id_pago = pg.id_pago
                  JOIN metodo m ON pg.cod_metodo = m.codigo
=======
        $query = "SELECT p.*, c.nomcliente
                  FROM pedido p
                  JOIN cliente c ON p.ced_cliente = c.ced_cliente
>>>>>>> 76976821448ffa84dd34d6f8e93d11b37a9bd82f
                  WHERE p.estado = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}