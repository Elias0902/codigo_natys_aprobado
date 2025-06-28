<?php
namespace App\Natys\Models;

use App\Natys\Config\Connect\Conexion;
use PDO;

class Producto extends Conexion {
    public $cod_producto; 
    public $nombre;
    public $precio; 
    public $unidad; 
    public $estado; 

    public function __construct() {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    public function guardar() {
        $query = "INSERT INTO producto (cod_producto, nombre, precio, unidad) 
                  VALUES (:cod_producto, :nombre, :precio, :unidad)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cod_producto", $this->cod_producto);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":unidad", $this->unidad);
        return $stmt->execute();
    }

    public function listar() {
        $query = "SELECT * FROM producto WHERE estado = 1"; 
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProducto($codigo) {
        $query = "SELECT * FROM producto WHERE cod_producto = :cod_producto LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cod_producto", $codigo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar() {
        $query = "UPDATE producto SET 
                  nombre = :nombre, 
                  precio = :precio, 
                  unidad = :unidad 
                  WHERE cod_producto = :cod_producto";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":unidad", $this->unidad);
        $stmt->bindParam(":cod_producto", $this->cod_producto);
        return $stmt->execute();
    }

    public function eliminar() {
        $query = "UPDATE producto SET estado = 0 WHERE cod_producto = :cod_producto";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cod_producto", $this->cod_producto);
        return $stmt->execute();
    }

    public function exists() {
        $query = "SELECT COUNT(*) FROM producto WHERE cod_producto = :cod_producto";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cod_producto", $this->cod_producto);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function listarEliminados() {
        $query = "SELECT * FROM producto WHERE estado = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function restaurar() {
        $query = "UPDATE producto SET estado = 1 WHERE cod_producto = :cod_producto";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cod_producto", $this->cod_producto);
        return $stmt->execute();
    }
}