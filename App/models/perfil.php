<?php
namespace App\Natys\Models;

use App\Natys\config\connect\Conexion;
use PDO;

class Perfil extends Conexion {
    public $id;
    public $correo_usuario;
    public $usuario;
    public $clave;
    public $rol;

    public function __construct() {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    public function listar() {
        $query = "SELECT id, correo_usuario, usuario, rol FROM usuario";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPerfil($id) {
        $query = "SELECT id, correo_usuario, usuario, rol FROM usuario WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar() {
        if (!empty($this->clave)) {
            $query = "UPDATE usuario SET  
                      correo_usuario = :correo_usuario, 
                      usuario = :usuario, 
                      clave = :clave, 
                      rol = :rol 
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":clave", $this->clave);
        } else {
            $query = "UPDATE usuario SET 
                      correo_usuario = :correo_usuario, 
                      usuario = :usuario, 
                      rol = :rol 
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->bindParam(":correo_usuario", $this->correo_usuario);
        $stmt->bindParam(":usuario", $this->usuario);
        $stmt->bindParam(":rol", $this->rol);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }
}