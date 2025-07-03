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

    /**
     * Valida las credenciales del usuario
     * @return array|false Datos del usuario o false si no existe
     */
    public function validarUsuario() {
        $query = "SELECT id, usuario, clave, rol, correo_usuario 
                 FROM usuario 
                 WHERE usuario = :usuario AND estado = 1 
                 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario", $this->usuario, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si la contraseña coincide
     * @param string $claveAlmacenada Contraseña almacenada en la base de datos
     * @param string $claveIngresada Contraseña ingresada por el usuario
     * @return bool True si coinciden, false si no
     */
    public function verificarClave($claveAlmacenada, $claveIngresada) {
        // En un sistema real debería usarse password_verify() con contraseñas hasheadas
        return $claveAlmacenada === $claveIngresada;
    }

    /**
     * Obtiene un usuario por su correo electrónico
     * @return array|false Datos del usuario o false si no existe
     */
    public function obtenerUsuarioPorCorreo() {
        $query = "SELECT id, usuario, correo_usuario 
                 FROM usuario 
                 WHERE correo_usuario = :correo AND estado = 1 
                 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":correo", $this->correo_usuario, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza la contraseña de un usuario
     * @param string $nuevaClave Nueva contraseña
     * @return bool True si se actualizó correctamente, false si hubo error
     */
    public function actualizarClave($nuevaClave) {
        $query = "UPDATE usuario 
                 SET clave = :clave 
                 WHERE correo_usuario = :correo AND estado = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":clave", $nuevaClave, PDO::PARAM_STR);
        $stmt->bindParam(":correo", $this->correo_usuario, PDO::PARAM_STR);
        return $stmt->execute();
    }
}