<?php
namespace App\Natys\Models;

use App\Natys\config\connect\Conexion;
use PDO;
use PDOException;

class Movimiento extends Conexion {
    private $num_movimiento;
    private $fecha;
    private $observaciones;
    private $estado;
    private $cod_producto;
    private $cant_productos;
    private $precio_venta;
    protected $conn;

    public function __construct() {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    public function getGuardar($fecha, $observaciones, $cod_producto, $cant_productos, $precio_venta) {
        $this->fecha = $fecha;
        $this->observaciones = $observaciones;
        $this->cod_producto = $cod_producto;
        $this->cant_productos = $cant_productos;
        $this->precio_venta = $precio_venta;
        return $this->guardar();
    }

    public function getActualizar($num_movimiento, $fecha, $observaciones, $cod_producto, $cant_productos, $precio_venta) {
        $this->num_movimiento = $num_movimiento;
        $this->fecha = $fecha;
        $this->observaciones = $observaciones;
        $this->cod_producto = $cod_producto;
        $this->cant_productos = $cant_productos;
        $this->precio_venta = $precio_venta;
        return $this->actualizar();
    }

    public function getEliminar($num_movimiento) {
        $this->num_movimiento = $num_movimiento;
        return $this->eliminar();
    }

    public function getActivar($num_movimiento) {
        $this->num_movimiento = $num_movimiento;
        return $this->activar();
    }

    public function getFinalizar($num_movimiento) {
        $this->num_movimiento = $num_movimiento;
        return $this->finalizar();
    }

    private function guardar() {
        $this->conn->beginTransaction();
        
        try {
            if (empty($this->fecha) || empty($this->cod_producto) || empty($this->cant_productos)) {
                throw new PDOException("Datos incompletos para guardar el movimiento");
            }

            $query = "INSERT INTO movimiento_entrada (fecha, observaciones) 
                      VALUES (:fecha, :observaciones)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':fecha' => $this->fecha,
                ':observaciones' => $this->observaciones ?? ''
            ]);
            
            $this->num_movimiento = $this->conn->lastInsertId();
            
            $queryDetalle = "INSERT INTO detalle_movimiento 
                           (num_movimiento, cod_producto, cant_productos, precio_venta) 
                           VALUES (:num_movimiento, :cod_producto, :cant_productos, :precio_venta)";
            $stmtDetalle = $this->conn->prepare($queryDetalle);
            $stmtDetalle->execute([
                ':num_movimiento' => $this->num_movimiento,
                ':cod_producto' => $this->cod_producto,
                ':cant_productos' => $this->cant_productos,
                ':precio_venta' => $this->precio_venta
            ]);
            
            $queryCheck = "SELECT estado FROM producto WHERE cod_producto = ?";
            $stmtCheck = $this->conn->prepare($queryCheck);
            $stmtCheck->execute([$this->cod_producto]);
            $producto = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($producto && $producto['estado'] == 0) {
                $queryActivar = "UPDATE producto SET estado = 1 WHERE cod_producto = ?";
                $stmtActivar = $this->conn->prepare($queryActivar);
                $stmtActivar->execute([$this->cod_producto]);
            }
            
            $this->conn->commit();
            return true;
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error en Movimiento::guardar(): " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }

    private function actualizar() {
        $this->conn->beginTransaction();
        
        try {
            $query = "UPDATE movimiento_entrada SET 
                      fecha = :fecha, 
                      observaciones = :observaciones 
                      WHERE num_movimiento = :num_movimiento";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":fecha", $this->fecha);
            $stmt->bindParam(":observaciones", $this->observaciones);
            $stmt->bindParam(":num_movimiento", $this->num_movimiento);
            $stmt->execute();
            
            $queryDetalle = "UPDATE detalle_movimiento SET
                            cod_producto = :cod_producto,
                            cant_productos = :cant_productos,
                            precio_venta = :precio_venta
                            WHERE num_movimiento = :num_movimiento";
            $stmtDetalle = $this->conn->prepare($queryDetalle);
            $stmtDetalle->bindParam(":cod_producto", $this->cod_producto);
            $stmtDetalle->bindParam(":cant_productos", $this->cant_productos);
            $stmtDetalle->bindParam(":precio_venta", $this->precio_venta);
            $stmtDetalle->bindParam(":num_movimiento", $this->num_movimiento);
            $stmtDetalle->execute();
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error al actualizar movimiento: " . $e->getMessage());
            return false;
        }
    }

    private function eliminar() {
        $this->conn->beginTransaction();

        try {
            // Vaciar la cantidad del movimiento (siempre a 0)
            $queryDetalle = "UPDATE detalle_movimiento SET cant_productos = 0 WHERE num_movimiento = :num_movimiento";
            $stmtDetalle = $this->conn->prepare($queryDetalle);
            $stmtDetalle->bindParam(":num_movimiento", $this->num_movimiento);
            $stmtDetalle->execute();

            // Finalizar el movimiento (estado = 0)
            $query = "UPDATE movimiento_entrada SET estado = 0 WHERE num_movimiento = :num_movimiento";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":num_movimiento", $this->num_movimiento);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error en eliminar: " . $e->getMessage());
            return false;
        }
    }

    private function activar() {
        try {
            $query = "UPDATE movimiento_entrada SET estado = 1 WHERE num_movimiento = :num_movimiento";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":num_movimiento", $this->num_movimiento);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en activar: " . $e->getMessage());
            return false;
        }
    }

    private function finalizar() {
        $this->conn->beginTransaction();

        try {
            // Vaciar la cantidad del movimiento
            $queryDetalle = "UPDATE detalle_movimiento SET cant_productos = 0 WHERE num_movimiento = :num_movimiento";
            $stmtDetalle = $this->conn->prepare($queryDetalle);
            $stmtDetalle->bindParam(":num_movimiento", $this->num_movimiento);
            $stmtDetalle->execute();

            // Finalizar el movimiento (estado = 0)
            $query = "UPDATE movimiento_entrada SET estado = 0 WHERE num_movimiento = :num_movimiento";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":num_movimiento", $this->num_movimiento);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error en finalizar: " . $e->getMessage());
            return false;
        }
    }

    public function listar() {
        try {
            $query = "SELECT me.*, dm.cod_producto, dm.cant_productos, dm.precio_venta, p.nombre as producto_nombre, p.estado as producto_estado
                      FROM movimiento_entrada me
                      LEFT JOIN detalle_movimiento dm ON me.num_movimiento = dm.num_movimiento
                      LEFT JOIN producto p ON dm.cod_producto = p.cod_producto
                      WHERE me.estado = 1
                      ORDER BY me.num_movimiento DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listar: " . $e->getMessage());
            return [];
        }
    }
    
    public function listarHistorial() {
        try {
            $query = "SELECT me.*, dm.cod_producto, dm.cant_productos, dm.precio_venta, p.nombre as producto_nombre, p.estado as producto_estado
                      FROM movimiento_entrada me
                      LEFT JOIN detalle_movimiento dm ON me.num_movimiento = dm.num_movimiento
                      LEFT JOIN producto p ON dm.cod_producto = p.cod_producto
                      WHERE me.estado = 0
                      ORDER BY me.num_movimiento DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarHistorial: " . $e->getMessage());
            return [];
        }
    }
    
    public function listarParaDataTables($start, $length, $search, $mostrarHistorial = false) {
        try {
            // Primero obtenemos el total de registros sin filtros
            $totalQuery = "SELECT COUNT(*) as total FROM movimiento_entrada WHERE estado = :estado";
            $totalStmt = $this->conn->prepare($totalQuery);
            $totalStmt->execute([':estado' => $mostrarHistorial ? 0 : 1]);
            $totalRecords = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Construimos la consulta principal con cálculo de stock actual
            $query = "SELECT 
                        me.*, 
                        dm.cod_producto, 
                        dm.cant_productos, 
                        dm.precio_venta,
                        p.nombre as producto_nombre, 
                        p.estado as producto_estado,
                        (
                            SELECT COALESCE(SUM(dm2.cant_productos), 0)
                            FROM detalle_movimiento dm2
                            JOIN movimiento_entrada me2 ON dm2.num_movimiento = me2.num_movimiento
                            WHERE dm2.cod_producto = dm.cod_producto 
                            AND me2.estado = 1
                        ) as stock_actual
                      FROM movimiento_entrada me
                      LEFT JOIN detalle_movimiento dm ON me.num_movimiento = dm.num_movimiento
                      LEFT JOIN producto p ON dm.cod_producto = p.cod_producto
                      WHERE me.estado = :estado";
            
            $params = [':estado' => $mostrarHistorial ? 0 : 1];
            
            // Si es la vista de activos, solo mostrar movimientos de entrada (cantidad positiva)
            if (!$mostrarHistorial) {
                $query .= " AND dm.cant_productos > 0";
            }
            $searchApplied = false;
            
            // Aplicar filtro de búsqueda si se proporciona
            $search = trim($search);
            if (!empty($search)) {
                $query .= " AND (
                    me.num_movimiento LIKE :search OR 
                    DATE_FORMAT(me.fecha, '%d/%m/%Y') LIKE :search_date OR
                    p.nombre LIKE :search_name OR
                    dm.cod_producto LIKE :search_code OR
                    me.observaciones LIKE :search_obs
                )";
                $searchParam = "%$search%";
                $params[':search'] = $searchParam;
                $params[':search_date'] = $searchParam;
                $params[':search_name'] = $searchParam;
                $params[':search_code'] = $searchParam;
                $params[':search_obs'] = $searchParam;
                $searchApplied = true;
            }
            
            // Ordenar y paginar
            $query .= " ORDER BY me.num_movimiento DESC LIMIT :start, :length";
            
            // Preparar y ejecutar la consulta
            $stmt = $this->conn->prepare($query);
            
            // Vincular parámetros
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            // Vincular parámetros de paginación
            $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
            $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
            
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Si no hay resultados con la búsqueda, intentar sin filtros
            if (empty($result) && $searchApplied) {
                $query = "SELECT 
                            me.*, 
                            dm.cod_producto, 
                            dm.cant_productos, 
                            dm.precio_venta,
                            p.nombre as producto_nombre, 
                            p.estado as producto_estado,
                            (
                                SELECT COALESCE(SUM(dm2.cant_productos), 0)
                                FROM detalle_movimiento dm2
                                JOIN movimiento_entrada me2 ON dm2.num_movimiento = me2.num_movimiento
                                WHERE dm2.cod_producto = dm.cod_producto 
                                AND me2.estado = 1
                            ) as stock_actual
                          FROM movimiento_entrada me
                          LEFT JOIN detalle_movimiento dm ON me.num_movimiento = dm.num_movimiento
                          LEFT JOIN producto p ON dm.cod_producto = p.cod_producto
                          WHERE me.estado = :estado
                          ORDER BY me.num_movimiento DESC LIMIT :start, :length";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':estado', $mostrarHistorial ? 0 : 1);
                $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
                $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error en listarParaDataTables: " . $e->getMessage());
            return [];
        }
    }
    
    public function contarTotalMovimientos($mostrarHistorial = false) {
        try {
            $query = "SELECT COUNT(*) as total FROM movimiento_entrada WHERE estado = :estado";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':estado' => $mostrarHistorial ? 0 : 1]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Error en contarTotalMovimientos: " . $e->getMessage());
            return 0;
        }
    }
    
    public function contarMovimientosFiltrados($search, $mostrarHistorial = false) {
        try {
            $query = "SELECT COUNT(*) as total 
                      FROM movimiento_entrada me
                      LEFT JOIN detalle_movimiento dm ON me.num_movimiento = dm.num_movimiento
                      LEFT JOIN producto p ON dm.cod_producto = p.cod_producto
                      WHERE me.estado = " . ($mostrarHistorial ? "0" : "1");
            
            $params = [];
            
            if (!empty($search)) {
                $query .= " AND (
                    me.num_movimiento LIKE :search OR 
                    me.fecha LIKE :search_date OR
                    p.nombre LIKE :search_name OR
                    dm.cod_producto LIKE :search_code OR
                    me.observaciones LIKE :search_obs
                )";
                $searchParam = "%$search%";
                $params[':search'] = $searchParam;
                $params[':search_date'] = $searchParam;
                $params[':search_name'] = $searchParam;
                $params[':search_code'] = $searchParam;
                $params[':search_obs'] = $searchParam;
            }
            
            $stmt = $this->conn->prepare($query);
            
            if (!empty($search)) {
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
            
        } catch (PDOException $e) {
            error_log("Error en contarMovimientosFiltrados: " . $e->getMessage());
            return 0;
        }
    }

    public function obtenerMovimiento($id) {
        try {
            $query = "SELECT me.*, dm.cod_producto, dm.cant_productos, dm.precio_venta 
                      FROM movimiento_entrada me
                      LEFT JOIN detalle_movimiento dm ON me.num_movimiento = dm.num_movimiento
                      WHERE me.num_movimiento = :num_movimiento LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":num_movimiento", $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerMovimiento: " . $e->getMessage());
            return false;
        }
    }

    public function listarProductos() {
        try {
            $query = "SELECT cod_producto, nombre, estado FROM producto ORDER BY nombre";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarProductos: " . $e->getMessage());
            return [];
        }
    }

    public function listarProductosActivos() {
        try {
            $query = "SELECT cod_producto, nombre FROM producto WHERE estado = 1 ORDER BY nombre";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarProductosActivos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPrecioProducto($cod_producto) {
        try {
            $query = "SELECT precio FROM producto WHERE cod_producto = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$cod_producto]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result === false) {
                error_log("Producto no encontrado: " . $cod_producto);
                return false;
            }
            
            return (float)$result['precio'];
            
        } catch (PDOException $e) {
            error_log("Error en obtenerPrecioProducto: " . $e->getMessage() . "\nCódigo: " . $e->getCode());
            return false;
        }
    }
    
    public function listarTodosProductos() {
        try {
            $query = "SELECT cod_producto, nombre, estado FROM producto ORDER BY nombre";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarTodosProductos: " . $e->getMessage());
            return [];
        }
    }
    
    private function verificarStockDisponible($cod_producto, $cantidad) {
        try {
            $query = "SELECT COALESCE(SUM(CASE WHEN dm.estado = 1 THEN dm.cant_productos ELSE 0 END), 0) as stock
                      FROM detalle_movimiento dm
                      WHERE dm.cod_producto = :cod_producto";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cod_producto", $cod_producto);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stock = $result['stock'] ?? 0;
            
            return $stock >= $cantidad;
        } catch (PDOException $e) {
            error_log("Error en verificarStockDisponible: " . $e->getMessage());
            return false;
        }
    }
    
    public function listarPorFechas($fechaInicio, $fechaFin) {
        try {
            $query = "SELECT me.*, dm.cod_producto, dm.cant_productos, dm.precio_venta, p.nombre as producto_nombre 
                      FROM movimiento_entrada me
                      LEFT JOIN detalle_movimiento dm ON me.num_movimiento = dm.num_movimiento
                      LEFT JOIN producto p ON dm.cod_producto = p.cod_producto
                      WHERE me.estado = 1 
                      AND DATE(me.fecha) BETWEEN :fechaInicio AND :fechaFin
                      ORDER BY me.fecha DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":fechaInicio", $fechaInicio);
            $stmt->bindParam(":fechaFin", $fechaFin);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarPorFechas: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerKardex($cod_producto) {
        try {
            $query = "SELECT 
                        me.fecha, 
                        me.observaciones, 
                        dm.cant_productos, 
                        dm.precio_venta, 
                        p.nombre as producto_nombre,
                        (
                            SELECT COALESCE(SUM(dm2.cant_productos), 0)
                            FROM detalle_movimiento dm2
                            JOIN movimiento_entrada me2 ON dm2.num_movimiento = me2.num_movimiento
                            WHERE dm2.cod_producto = dm.cod_producto 
                            AND me2.estado = 1
                            AND me2.num_movimiento <= me.num_movimiento
                        ) as saldo_actual
                      FROM movimiento_entrada me
                      LEFT JOIN detalle_movimiento dm ON me.num_movimiento = dm.num_movimiento
                      LEFT JOIN producto p ON dm.cod_producto = p.cod_producto
                      WHERE dm.cod_producto = :cod_producto
                      AND me.estado = 1
                      ORDER BY me.fecha, me.num_movimiento";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cod_producto", $cod_producto);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerKardex: " . $e->getMessage());
            return [];
        }
    }
}