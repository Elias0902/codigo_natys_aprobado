<?php
namespace App\Natys\Models;

use App\Natys\config\connect\Conexion;
use PDO;

class Pago extends Conexion {
    public $id_pago;
    public $banco;
    public $referencia;
    public $fecha;
    public $monto;
    public $cod_metodo;
    public $estado;

    public function __construct() {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    public function guardar() {
        $query = "INSERT INTO pago (banco, referencia, fecha, monto, cod_metodo) 
                  VALUES (:banco, :referencia, :fecha, :monto, :cod_metodo)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":banco", $this->banco);
        $stmt->bindParam(":referencia", $this->referencia);
        $stmt->bindParam(":fecha", $this->fecha);
        $stmt->bindParam(":monto", $this->monto);
        $stmt->bindParam(":cod_metodo", $this->cod_metodo);
        return $stmt->execute();
    }

    public function listar() {
        $query = "SELECT p.*, m.detalle as metodo_pago 
                  FROM pago p
                  JOIN metodo m ON p.cod_metodo = m.codigo
                  WHERE p.estado = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPago($id) {
        $query = "SELECT * FROM pago WHERE id_pago = :id_pago LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_pago", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

public function obtenerMetodosPagoActivos() {
    $query = "SELECT codigo, detalle FROM metodo WHERE estado = 1";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function actualizar() {
        $query = "UPDATE pago SET 
                  banco = :banco, 
                  referencia = :referencia, 
                  fecha = :fecha, 
                  monto = :monto, 
                  cod_metodo = :cod_metodo 
                  WHERE id_pago = :id_pago";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":banco", $this->banco);
        $stmt->bindParam(":referencia", $this->referencia);
        $stmt->bindParam(":fecha", $this->fecha);
        $stmt->bindParam(":monto", $this->monto);
        $stmt->bindParam(":cod_metodo", $this->cod_metodo);
        $stmt->bindParam(":id_pago", $this->id_pago);
        return $stmt->execute();
    }

    public function eliminar() {
        $query = "UPDATE pago SET estado = 0 WHERE id_pago = :id_pago";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_pago", $this->id_pago);
        return $stmt->execute();
    }

    public function listarEliminados() {
        $query = "SELECT p.*, m.detalle as metodo_pago 
                  FROM pago p
                  JOIN metodo m ON p.cod_metodo = m.codigo
                  WHERE p.estado = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function restaurar() {
        $query = "UPDATE pago SET estado = 1 WHERE id_pago = :id_pago";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_pago", $this->id_pago);
        return $stmt->execute();
    }

    public function obtenerMetodosPago() {
        $query = "SELECT * FROM metodo WHERE estado = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}