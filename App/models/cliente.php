<?php
namespace App\Natys\Models;

use App\Natys\config\connect\Conexion;
use PDO;

class Cliente extends Conexion {
    public $ced_cliente; 
    public $nomcliente;
    public $correo; 
    public $telefono; 
    public $direccion; 
    public $estado; 

         public function __construct() {
            parent::__construct();

            $this->conn = $this->getConnection();
        }


    public function guardar() {
        $query = "INSERT INTO cliente (ced_cliente, nomcliente, correo, telefono, direccion) 
                  VALUES (:ced_cliente, :nomcliente, :correo, :telefono, :direccion)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ced_cliente", $this->ced_cliente);
        $stmt->bindParam(":nomcliente", $this->nomcliente);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":direccion", $this->direccion);
        return $stmt->execute();
    }

    public function listar() {
    $query = "SELECT * FROM cliente WHERE estado = 1"; 
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCliente($cedula) {
    $query = "SELECT * FROM cliente WHERE ced_cliente = :ced_cliente LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":ced_cliente", $cedula);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log(print_r($result, true));
    
    return $result;
}

    public function actualizar() {
        $query = "UPDATE cliente SET 
                  nomcliente = :nomcliente, 
                  correo = :correo, 
                  telefono = :telefono, 
                  direccion = :direccion 
                  WHERE ced_cliente = :ced_cliente";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nomcliente", $this->nomcliente);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":direccion", $this->direccion);
        $stmt->bindParam(":ced_cliente", $this->ced_cliente);
        return $stmt->execute();
    }

    public function eliminar() {
        $query = "UPDATE cliente SET estado = 0 WHERE ced_cliente = :ced_cliente";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ced_cliente", $this->ced_cliente);
        return $stmt->execute();
    }

    public function exists() {
        $query = "SELECT COUNT(*) FROM cliente WHERE ced_cliente = :ced_cliente";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ced_cliente", $this->ced_cliente);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function listarEliminados() {
        $query = "SELECT * FROM cliente WHERE estado = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function restaurar() {
        $query = "UPDATE cliente SET estado = 1 WHERE ced_cliente = :ced_cliente";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ced_cliente", $this->ced_cliente);
        return $stmt->execute();
    }
}