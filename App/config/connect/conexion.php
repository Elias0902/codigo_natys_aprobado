<?php
namespace App\Natys\config\connect;

use PDO;
use PDOException;


interface ConexionInterface {
    public function getConnection(): PDO;
}


  class Conexion implements ConexionInterface {
    private $host = "localhost";
    private $db_name = "Natys";
    private $username = "root";
    private $password = "";
    protected $conn;

    public function __construct() {
        
        $this->getConnection();
    }

    
    public function getConnection(): PDO {
        try {
            
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name}", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            
            die('ERROR DE CONEXIÓN: No se ha podido conectar con la base de datos. ' . $e->getMessage());
        }

        
        return $this->conn;
    }
}
?>
