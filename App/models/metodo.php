<?php
namespace App\Natys\Models;

use App\Natys\config\connect\Conexion;
use PDO;

class Metodo extends Conexion {
    public $codigo;
    public $detalle;
    public $estado;

    public function __construct() {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    public function listar() {
        $query = "SELECT * FROM metodo WHERE estado = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($codigo) {
        $query = "SELECT * FROM metodo WHERE codigo = :codigo";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":codigo", $codigo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar() {
        $query = "INSERT INTO metodo (codigo, detalle, estado) 
                  VALUES (:codigo, :detalle, :estado)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":codigo", $this->codigo);
        $stmt->bindParam(":detalle", $this->detalle);
        $stmt->bindParam(":estado", $this->estado);
        return $stmt->execute();
    }

    public function actualizar() {
        $query = "UPDATE metodo SET 
                  detalle = :detalle,
                  estado = :estado
                  WHERE codigo = :codigo";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":codigo", $this->codigo);
        $stmt->bindParam(":detalle", $this->detalle);
        $stmt->bindParam(":estado", $this->estado);
        return $stmt->execute();
    }

    public function eliminar() {
        $query = "UPDATE metodo SET estado = 0 WHERE codigo = :codigo";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":codigo", $this->codigo);
        return $stmt->execute();
    }

    public function restaurar() {
        $query = "UPDATE metodo SET estado = 1 WHERE codigo = :codigo";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":codigo", $this->codigo);
        return $stmt->execute();
    }
}