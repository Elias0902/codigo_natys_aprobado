<?php
namespace App\Natys\Models;

use App\Natys\config\connect\Conexion;
use PDO;
use Exception;

class Cliente extends Conexion {
    private $ced_cliente; 
    private $nomcliente;
    private $correo; 
    private $telefono; 
    private $direccion; 
    private $estado; 
    protected $conn;
    private $errors = [];

    public function __construct() {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    public function setCedCliente($ced_cliente) {
        $this->ced_cliente = $ced_cliente;
        return $this;
    }

    public function setNomcliente($nomcliente) {
        $this->nomcliente = $nomcliente;
        return $this;
    }

    public function setCorreo($correo) {
        $this->correo = $correo;
        return $this;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
        return $this;
    }

    public function setDireccion($direccion) {
        $this->direccion = $direccion;
        return $this;
    }

    public function setData($data) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        return $this;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function clearErrors() {
        $this->errors = [];
    }

    // Validaciones
    public function validarCedula($cedula) {
        if (empty($cedula)) {
            $this->errors[] = 'La cédula es requerida';
            return false;
        }

        if (!preg_match('/^\d{7,9}$/', $cedula)) {
            $this->errors[] = 'La cédula debe contener entre 7 y 9 dígitos';
            return false;
        }

        return true;
    }

    public function validarNombre($nombre) {
        if (empty($nombre)) {
            $this->errors[] = 'El nombre completo es requerido';
            return false;
        }

        if (strlen($nombre) < 3 || strlen($nombre) > 100) {
            $this->errors[] = 'El nombre debe tener entre 3 y 100 caracteres';
            return false;
        }

        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,100}$/', $nombre)) {
            $this->errors[] = 'El nombre solo puede contener letras y espacios';
            return false;
        }

        return true;
    }

    public function validarTelefono($telefono) {
        if (empty($telefono)) {
            $this->errors[] = 'El teléfono es requerido';
            return false;
        }

        if (!preg_match('/(^04(12|14|16|24|26|22)\d{7}$)|(^02\d{9}$)/', $telefono)) {
            $this->errors[] = 'El formato del teléfono no es válido. Use 04xx1234567 (móvil) o 02xxxxxxxx (fijo)';
            return false;
        }

        return true;
    }

    public function validarCorreo($correo) {
        if (empty($correo)) {
            $this->errors[] = 'El correo electrónico es requerido';
            return false;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'El formato del correo electrónico no es válido';
            return false;
        }

        if (!preg_match('/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i', $correo)) {
            $this->errors[] = 'El formato del correo electrónico no es válido (ejemplo@dominio.com)';
            return false;
        }

        return true;
    }

    public function validarDireccion($direccion) {
        if (empty($direccion)) {
            $this->errors[] = 'La dirección es requerida';
            return false;
        }

        if (strlen($direccion) < 5 || strlen($direccion) > 255) {
            $this->errors[] = 'La dirección debe tener entre 5 y 255 caracteres';
            return false;
        }

        return true;
    }

    public function validarDatosCompletos($datos) {
        $this->clearErrors();

        $validaciones = [
            'cedula' => $this->validarCedula($datos['ced_cliente'] ?? ''),
            'nombre' => $this->validarNombre($datos['nomcliente'] ?? ''),
            'correo' => $this->validarCorreo($datos['correo'] ?? ''),
            'telefono' => $this->validarTelefono($datos['telefono'] ?? ''),
            'direccion' => $this->validarDireccion($datos['direccion'] ?? '')
        ];

        return empty($this->errors);
    }

    public function cedulaExiste($cedula, $cedulaOriginal = null) {
        $query = "SELECT COUNT(*) FROM cliente WHERE ced_cliente = :ced_cliente";
        
        if ($cedulaOriginal !== null && $cedula === $cedulaOriginal) {
            $query .= " AND ced_cliente != :ced_original";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ced_cliente", $cedula);
        
        if ($cedulaOriginal !== null && $cedula === $cedulaOriginal) {
            $stmt->bindParam(":ced_original", $cedulaOriginal);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function getGuardar($cedCliente, $nomcliente, $correo, $telefono, $direccion) {
        $datos = [
            'ced_cliente' => $cedCliente,
            'nomcliente' => $nomcliente,
            'correo' => $correo,
            'telefono' => $telefono,
            'direccion' => $direccion
        ];

        // Validar datos
        if (!$this->validarDatosCompletos($datos)) {
            return false;
        }

        // Verificar si la cédula ya existe
        if ($this->cedulaExiste($cedCliente)) {
            $this->errors[] = 'La cédula ' . $cedCliente . ' ya existe en el sistema';
            return false;
        }

        $this->ced_cliente = $cedCliente;
        $this->nomcliente = $nomcliente;
        $this->correo = $correo;
        $this->telefono = $telefono;
        $this->direccion = $direccion;
        
        try {
            return $this->guardar();
        } catch (Exception $e) {
            error_log("Error en getGuardar: " . $e->getMessage());
            $this->errors[] = 'Error al guardar en la base de datos: ' . $e->getMessage();
            return false;
        }
    }

    public function guardarCliente($data) {
        $this->setData($data);
        
        if (!$this->validarDatosCompletos($data)) {
            return false;
        }

        if ($this->cedulaExiste($this->ced_cliente)) {
            $this->errors[] = 'La cédula ' . $this->ced_cliente . ' ya existe en el sistema';
            return false;
        }

        return $this->guardar();
    }

    private function guardar() {
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

    public function getActualizar($cedCliente, $nomcliente, $correo, $telefono, $direccion) {
        $datos = [
            'ced_cliente' => $cedCliente,
            'nomcliente' => $nomcliente,
            'correo' => $correo,
            'telefono' => $telefono,
            'direccion' => $direccion
        ];

        // Validar datos
        if (!$this->validarDatosCompletos($datos)) {
            return false;
        }

        $this->ced_cliente = $cedCliente;
        $this->nomcliente = $nomcliente;
        $this->correo = $correo;
        $this->telefono = $telefono;
        $this->direccion = $direccion;
        
        try {
            return $this->actualizar();
        } catch (Exception $e) {
            error_log("Error en getActualizar: " . $e->getMessage());
            $this->errors[] = 'Error al actualizar en la base de datos: ' . $e->getMessage();
            return false;
        }
    }

    private function actualizar() {
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
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function eliminarCliente($cedula) {
        $query = "UPDATE cliente SET estado = 0 WHERE ced_cliente = :ced_cliente";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ced_cliente", $cedula);
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
    
    public function restaurarCliente($cedula) {
        $query = "UPDATE cliente SET estado = 1 WHERE ced_cliente = :ced_cliente";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ced_cliente", $cedula);
        return $stmt->execute();
    }

    public function contarClientes() {
        $query = "SELECT COUNT(*) as total FROM cliente WHERE estado = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    public function listarPorFechas($fechaInicio, $fechaFin) {
        $query = "SELECT * FROM cliente 
                  WHERE estado = 1 
                  AND DATE(created_at) BETWEEN :fechaInicio AND :fechaFin
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fechaInicio", $fechaInicio);
        $stmt->bindParam(":fechaFin", $fechaFin);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerClientesFiltrados($filtros) {
        $query = "SELECT * FROM cliente WHERE estado = 1";
        $params = [];

        if (!empty($filtros['cedulaCliente'])) {
            $query .= " AND ced_cliente LIKE :cedula";
            $params[':cedula'] = '%' . $filtros['cedulaCliente'] . '%';
        }

        if (!empty($filtros['nombreCliente'])) {
            $query .= " AND nomcliente LIKE :nombre";
            $params[':nombre'] = '%' . $filtros['nombreCliente'] . '%';
        }

        if (!empty($filtros['fechaInicio']) && !empty($filtros['fechaFin'])) {
            $query .= " AND DATE(created_at) BETWEEN :fechaInicio AND :fechaFin";
            $params[':fechaInicio'] = $filtros['fechaInicio'];
            $params[':fechaFin'] = $filtros['fechaFin'];
        }

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}