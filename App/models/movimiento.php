<?php
namespace App\Natys\Models;

use App\Natys\config\connect\Conexion;
use PDO;

class Movimiento extends Conexion {
    public $num_movimiento;
    public $fecha;
    public $observaciones;
    public $estado;

    public function __construct() {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    public function guardar() {
        $query = "INSERT INTO movimiento_entrada (fecha, observaciones) 
                  VALUES (:fecha, :observaciones)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha", $this->fecha);
        $stmt->bindParam(":observaciones", $this->observaciones);
        return $stmt->execute();
    }

    public function listar() {
        $query = "SELECT * FROM movimiento_entrada WHERE estado = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerMovimiento($id) {
        $query = "SELECT * FROM movimiento_entrada WHERE num_movimiento = :num_movimiento LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":num_movimiento", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar() {
        $query = "UPDATE movimiento_entrada SET 
                  fecha = :fecha, 
                  observaciones = :observaciones 
                  WHERE num_movimiento = :num_movimiento";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha", $this->fecha);
        $stmt->bindParam(":observaciones", $this->observaciones);
        $stmt->bindParam(":num_movimiento", $this->num_movimiento);
        return $stmt->execute();
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