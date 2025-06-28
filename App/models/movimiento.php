<?php
namespace App\Natys\Models;

use App\Natys\config\connect\Conexion;
use PDO;

class Movimiento extends Conexion {
    public $num_movimiento;
    public $fecha;
    public $observaciones;
    public $estado;
    public $cod_producto;
    public $cant_productos;
    public $precio_venta;

    public function __construct() {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    public function guardar() {
        $this->conn->beginTransaction();
        
        try {
            // Insertar movimiento
            $query = "INSERT INTO movimiento_entrada (fecha, observaciones) 
                      VALUES (:fecha, :observaciones)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":fecha", $this->fecha);
            $stmt->bindParam(":observaciones", $this->observaciones);
            $stmt->execute();
            
            $this->num_movimiento = $this->conn->lastInsertId();
            
            // Insertar detalle del movimiento
            $queryDetalle = "INSERT INTO detalle_movimiento 
                             (num_movimiento, cod_producto, cant_productos, precio_venta) 
                             VALUES (:num_movimiento, :cod_producto, :cant_productos, :precio_venta)";
            $stmtDetalle = $this->conn->prepare($queryDetalle);
            $stmtDetalle->bindParam(":num_movimiento", $this->num_movimiento);
            $stmtDetalle->bindParam(":cod_producto", $this->cod_producto);
            $stmtDetalle->bindParam(":cant_productos", $this->cant_productos);
            $stmtDetalle->bindParam(":precio_venta", $this->precio_venta);
            $stmtDetalle->execute();
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error al guardar movimiento: " . $e->getMessage());
            return false;
        }
    }

    public function listar() {
        $query = "SELECT me.*, dm.cod_producto, dm.cant_productos, p.nombre as producto_nombre 
                  FROM movimiento_entrada me
                  LEFT JOIN detalle_movimiento dm ON me.num_movimiento = dm.num_movimiento
                  LEFT JOIN producto p ON dm.cod_producto = p.cod_producto
                  WHERE me.estado = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerMovimiento($id) {
        $query = "SELECT me.*, dm.cod_producto, dm.cant_productos 
                  FROM movimiento_entrada me
                  LEFT JOIN detalle_movimiento dm ON me.num_movimiento = dm.num_movimiento
                  WHERE me.num_movimiento = :num_movimiento LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":num_movimiento", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listarProductos() {
        $query = "SELECT cod_producto, nombre FROM producto WHERE estado = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizar() {
        $this->conn->beginTransaction();
        
        try {
            // Actualizar movimiento
            $query = "UPDATE movimiento_entrada SET 
                      fecha = :fecha, 
                      observaciones = :observaciones 
                      WHERE num_movimiento = :num_movimiento";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":fecha", $this->fecha);
            $stmt->bindParam(":observaciones", $this->observaciones);
            $stmt->bindParam(":num_movimiento", $this->num_movimiento);
            $stmt->execute();
            
            // Actualizar detalle del movimiento
            $queryDetalle = "UPDATE detalle_movimiento SET
                            cod_producto = :cod_producto,
                            cant_productos = :cant_productos,
                            precio_venta = :precio_venta
                            WHERE num_movimiento = :num_movimiento";
            $stmtDetalle = $this->conn->prepare($queryDetalle);
            $stmtDetalle->bindParam(":cod_producto", $this->cod_producto);
            $stmtDetalle->bindParam(":cant_productos", $this->cant_productos);
            $stmtDetalle->bindParam(":precio_venta", $this->precio_venta);
            $stmtDetalle->bindParam(":num_movimiento", $this->num_movimiento);
            $stmtDetalle->execute();
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error al actualizar movimiento: " . $e->getMessage());
            return false;
        }
    }

    public function eliminar() {
        $query = "UPDATE movimiento_entrada SET estado = 0 WHERE num_movimiento = :num_movimiento";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":num_movimiento", $this->num_movimiento);
        return $stmt->execute();
    }

    public function listarEliminados() {
        $query = "SELECT * FROM movimiento_entrada WHERE estado = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function restaurar() {
        $query = "UPDATE movimiento_entrada SET estado = 1 WHERE num_movimiento = :num_movimiento";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":num_movimiento", $this->num_movimiento);
        return $stmt->execute();
    }
}