<?php
namespace App\Natys\Models;

use App\Natys\config\connect\Conexion;
use PDO;
use PDOException;

class Pago extends Conexion {
    private $id_pago;
    private $banco;
    private $referencia;
    private $fecha;
    private $monto;
    private $cod_metodo;
    private $estado;
    protected $conn;

    public function __construct() {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    // Método para asignar datos de forma encapsulada
    public function setData($data) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function getGuardar($data) {
        $this->setData($data);
        return $this->guardar();
    }

    // Cambiar la visibilidad de guardar() a public
    public function guardar() {
        try {
            if (!$this->conn) {
                throw new PDOException("No hay conexión a la base de datos");
            }

            // 1. Verificar que el método de pago sea válido y esté activo
            $queryVerificar = "SELECT codigo FROM metodo WHERE codigo = :cod_metodo AND estado = 1";
            $stmtVerificar = $this->conn->prepare($queryVerificar);
            $stmtVerificar->bindParam(":cod_metodo", $this->cod_metodo);
            $stmtVerificar->execute();
            
            if ($stmtVerificar->rowCount() === 0) {
                throw new PDOException("El método de pago no existe o está inactivo");
            }

            // Normalizar referencia: si viene 'N/A' dejar 'Efectivo'
            if (is_string($this->referencia) && strtoupper(trim($this->referencia)) === 'N/A') {
                $this->referencia = 'Efectivo';
            }

            // 2. Insertar el pago en la base de datos
            $query = "INSERT INTO pago (banco, referencia, fecha, monto, cod_metodo, estado) 
                      VALUES (:banco, :referencia, :fecha, :monto, :cod_metodo, :estado)";
            
            $stmt = $this->conn->prepare($query);
            
            // Usamos las propiedades privadas que fueron asignadas a través de los setters
            $stmt->bindParam(":banco", $this->banco);
            $stmt->bindParam(":referencia", $this->referencia);
            $stmt->bindParam(":fecha", $this->fecha);
            $stmt->bindParam(":monto", $this->monto);
            $stmt->bindParam(":cod_metodo", $this->cod_metodo);
            $stmt->bindParam(":estado", $this->estado, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Si la inserción es exitosa, retorna el ID del nuevo pago
                return $this->conn->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en Pago::guardar(): " . $e->getMessage());
            // Re-lanzamos la excepción para que el controlador la pueda capturar
            throw $e;
        }
    }
    public function listar($estado = 1, $filtro = 'todos') {
        $query = "SELECT p.*, m.detalle as metodo_pago 
                  FROM pago p
                  LEFT JOIN metodo m ON p.cod_metodo = m.codigo
                  WHERE p.estado = :estado";
        
        if ($filtro === 'efectivo') {
            $query .= " AND p.cod_metodo = 'EFECTIVO'";
        } elseif ($filtro === 'otros') {
            $query .= " AND p.cod_metodo != 'EFECTIVO'";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $estado, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si una referencia de pago ya existe en la base de datos
     * 
     * @param string $referencia Número de referencia a verificar
     * @param int $excluirId ID del pago a excluir de la búsqueda (para actualizaciones)
     * @return bool True si la referencia ya existe, False en caso contrario
     */
    public function existeReferencia($referencia, $excluirId = null) {
        try {
            $query = "SELECT COUNT(*) as total FROM pago WHERE referencia = :referencia AND referencia != 'N/A' AND referencia != ''";
            $params = [':referencia' => $referencia];
            
            if ($excluirId !== null) {
                $query .= " AND id_pago != :excluir_id";
                $params[':excluir_id'] = $excluirId;
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return ($result && $result['total'] > 0);
        } catch (PDOException $e) {
            error_log("Error en Pago::existeReferencia(): " . $e->getMessage());
            throw $e;
        }
    }

    public function obtenerPago($id) {
        $query = "SELECT p.*, m.detalle as metodo_pago 
                 FROM pago p
                 LEFT JOIN metodo m ON p.cod_metodo = m.codigo
                 WHERE p.id_pago = :id_pago 
                 LIMIT 1";
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
        try {
            // Normalizar referencia si se pasó 'N/A'
            if (isset($this->referencia) && is_string($this->referencia) && strtoupper(trim($this->referencia)) === 'N/A') {
                $this->referencia = 'Efectivo';
            }

            $query = "UPDATE pago SET 
                      banco = :banco, 
                      referencia = :referencia, 
                      fecha = :fecha, 
                      monto = :monto, 
                      cod_metodo = :cod_metodo 
                      WHERE id_pago = :id_pago";
            $stmt = $this->conn->prepare($query);
            
            // Usar las propiedades privadas correctamente
            $stmt->bindParam(":banco", $this->banco);
            $stmt->bindParam(":referencia", $this->referencia);
            $stmt->bindParam(":fecha", $this->fecha);
            $stmt->bindParam(":monto", $this->monto);
            $stmt->bindParam(":cod_metodo", $this->cod_metodo);
            $stmt->bindParam(":id_pago", $this->id_pago, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new PDOException("Error en la actualización: " . ($errorInfo[2] ?? 'Error desconocido'));
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error en Pago::actualizar(): " . $e->getMessage());
            throw $e;
        }
    }
    public function eliminar() {
        try {
            $query = "UPDATE pago SET estado = 0 WHERE id_pago = :id_pago";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_pago", $this->id_pago);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Pago::eliminar(): " . $e->getMessage());
            throw $e;
        }
    }

    public function listarEliminados() {
        $query = "SELECT p.*, m.detalle as metodo_pago 
                  FROM pago p
                  LEFT JOIN metodo m ON p.cod_metodo = m.codigo
                  WHERE p.estado = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminarDefinitivamente($id_pago) {
        try {
            // Primero, verificamos si el pago existe
            $queryCheck = "SELECT id_pago FROM pago WHERE id_pago = :id_pago";
            $stmtCheck = $this->conn->prepare($queryCheck);
            $stmtCheck->bindParam(":id_pago", $id_pago, PDO::PARAM_INT);
            $stmtCheck->execute();
            
            if ($stmtCheck->rowCount() === 0) {
                throw new PDOException("El pago no existe o ya ha sido eliminado");
            }
            
            // Eliminamos el pago de forma permanente
            $query = "DELETE FROM pago WHERE id_pago = :id_pago";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_pago", $id_pago, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Pago::eliminarDefinitivamente(): " . $e->getMessage());
            throw $e;
        }
    }
    
    public function restaurar() {
        try {
            $query = "UPDATE pago SET estado = 1 WHERE id_pago = :id_pago";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_pago", $this->id_pago);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Pago::restaurar(): " . $e->getMessage());
            throw $e;
        }
    }

    public function obtenerDetallesCompletos($id_pago) {
        try {
            $queryPago = "SELECT p.*, m.detalle as metodo_pago 
                          FROM pago p 
                          LEFT JOIN metodo m ON p.cod_metodo = m.codigo 
                          WHERE p.id_pago = :id_pago";
            $stmtPago = $this->conn->prepare($queryPago);
            $stmtPago->bindParam(":id_pago", $id_pago);
            $stmtPago->execute();
            $pago = $stmtPago->fetch(PDO::FETCH_ASSOC);
            
            if (!$pago) {
                return false;
            }
            
            $queryPedido = "SELECT ped.*, c.nomcliente, c.ced_cliente as cedula, c.telefono, c.correo
                            FROM pedido ped
                            LEFT JOIN cliente c ON ped.ced_cliente = c.ced_cliente
                            WHERE ped.id_pago = :id_pago";
            $stmtPedido = $this->conn->prepare($queryPedido);
            $stmtPedido->bindParam(":id_pago", $id_pago);
            $stmtPedido->execute();
            $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);
            
            $productos = [];
            if ($pedido) {
                $queryProductos = "SELECT dp.*, p.nombre 
                                   FROM detalle_pedido dp 
                                   LEFT JOIN producto p ON dp.cod_producto = p.cod_producto 
                                   WHERE dp.id_pedido = :id_pedido";
                $stmtProductos = $this->conn->prepare($queryProductos);
                $stmtProductos->bindParam(":id_pedido", $pedido['id_pedido']);
                $stmtProductos->execute();
                $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return [
                'pago' => $pago,
                'pedido' => $pedido,
                'cliente' => $pedido ? [
                    'nombre' => $pedido['nomcliente'],
                    'cedula' => $pedido['cedula'],
                    'telefono' => $pedido['telefono'],
                    'correo' => $pedido['correo']
                ] : null,
                'productos' => $productos
            ];
        } catch (PDOException $e) {
            error_log("Error en Pago::obtenerDetallesCompletos(): " . $e->getMessage());
            throw $e;
        }
    }

    public function contarPagos() {
        $query = "SELECT COUNT(*) as total FROM pago WHERE estado = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function listarPorFechas($fechaInicio, $fechaFin, $metodo = 'todos') {
    $query = "SELECT p.*, m.detalle as metodo_pago 
              FROM pago p
              LEFT JOIN metodo m ON p.cod_metodo = m.codigo
              WHERE p.estado = 1 
              AND DATE(p.fecha) BETWEEN :fechaInicio AND :fechaFin";
    
    // Agregar filtro por método si no es "todos"
    if ($metodo !== 'todos') {
        $query .= " AND p.cod_metodo = :metodo";
    }
    
    $query .= " ORDER BY p.fecha DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":fechaInicio", $fechaInicio);
    $stmt->bindParam(":fechaFin", $fechaFin);
    
    if ($metodo !== 'todos') {
        $stmt->bindParam(":metodo", $metodo);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // Setters requeridos por el controlador
    public function setBanco($banco) {
        $this->banco = $banco;
    }
    public function setReferencia($referencia) {
        $this->referencia = $referencia;
    }
    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }
    public function setMonto($monto) {
        $this->monto = $monto;
    }
    public function setCodMetodo($cod_metodo) {
        $this->cod_metodo = $cod_metodo;
    }
    public function setEstado($estado) {
        $this->estado = $estado;
    }
    public function setIdPago($id_pago) {
        $this->id_pago = $id_pago;
    }
}