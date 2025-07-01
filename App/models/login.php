<?php
namespace App\Natys\Models;

use App\Natys\config\connect\Conexion;
use PDO;

class Login extends Conexion {
    public $usuario;
    public $clave;
    public $correo_usuario;

    public function __construct() {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    public function validarUsuario() {
        $query = "SELECT id, nombre_usuario, usuario, clave, rol FROM usuario 
                 WHERE usuario = :usuario AND estado = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario", $this->usuario);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verificarClave($claveAlmacenada, $claveIngresada) {
        // Comparación simple (mejoraría con password_hash() en producción)
        return $claveAlmacenada === $claveIngresada;
    }

    public function obtenerUsuarioPorCorreo() {
        $query = "SELECT id, usuario, correo_usuario FROM usuario 
                 WHERE correo_usuario = :correo AND estado = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":correo", $this->correo_usuario);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarClave($nuevaClave) {
        $query = "UPDATE usuario SET clave = :clave WHERE correo_usuario = :correo";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":clave", $nuevaClave);
        $stmt->bindParam(":correo", $this->correo_usuario);
        return $stmt->execute();
    }
}