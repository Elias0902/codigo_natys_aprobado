<?php
namespace App\Natys\config\connect;

use PDO;
use PDOException;

// Interfaz que obliga a implementar el método getConnection
interface ConexionInterface {
    public function getConnection(): PDO;
}

// Clase abstracta que implementa la interfaz
  class Conexion implements ConexionInterface {
    private $host = "localhost";
    private $db_name = "Natys";
    private $username = "root";
    private $password = "";
    public $conn;

    public function __construct() {
        // Establecer la conexión al crear una instancia
        $this->getConnection();
    }

    // Método para conectar a la base de datos
    public function getConnection(): PDO {
        try {
            // Crear nueva conexión usando PDO
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name}", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Mostrar mensaje si ocurre un error
            die('ERROR DE CONEXIÓN: No se ha podido conectar con la base de datos. ' . $e->getMessage());
        }

        // Retornar el objeto de conexión
        return $this->conn;
    }
}
?>
