<?php
namespace App\Natys\Models;

use App\Natys\Config\Connect\Conexion;
use PDO;
use PDOException;

class Producto extends Conexion {
    private $cod_producto;
    private $nombre;
    private $precio;
    private $unidad;
    private $imagen_url;
    private $descripcion;
    private $estado;
    protected $conn;

    public function __construct() {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    // ==================== MÉTODOS PÚBLICOS DE ENCAPSULAMIENTO ====================

    public function guardarProducto($cod_producto, $nombre, $precio, $unidad, $imagen_url, $descripcion) {
        $this->cod_producto = $cod_producto;
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->unidad = $unidad;
        $this->imagen_url = $imagen_url;
        $this->descripcion = $descripcion;
        return $this->guardar();
    }

    public function actualizarProducto($cod_producto, $nombre, $precio, $unidad, $imagen_url, $descripcion) {
        $this->cod_producto = $cod_producto;
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->unidad = $unidad;
        $this->imagen_url = $imagen_url;
        $this->descripcion = $descripcion;
        return $this->actualizar();
    }

    public function eliminarProducto($cod_producto) {
        $this->cod_producto = $cod_producto;
        return $this->eliminar();
    }

    public function obtenerProducto($codigo) {
        $this->cod_producto = $codigo;
        return $this->obtener();
    }

    public function listarProductos($searchTerm = '', $minPrice = null, $maxPrice = null) {
        return $this->listar($searchTerm, $minPrice, $maxPrice);
    }

    public function listarProductosPaginados($search = '', $start = 0, $length = 10) {
        return $this->listarConPaginacion($search, $start, $length);
    }

    public function contarProductosTotales() {
        return $this->contarTotal();
    }

    public function contarProductosFiltrados($search) {
        return $this->contarFiltrados($search);
    }

    public function listarProductosPorFechas($fechaInicio, $fechaFin, $estado = null) {
        return $this->listarPorFechas($fechaInicio, $fechaFin, $estado);
    }

    public function listarProductosEliminados() {
        return $this->listarEliminados();
    }

    public function restaurarProducto($cod_producto) {
        $this->cod_producto = $cod_producto;
        return $this->restaurar();
    }

    public function contarTotalProductos() {
        return $this->contarProductos();
    }

    public function contarProductosBajoStock() {
        return $this->contarBajoStock();
    }

    public function activarProducto($cod_producto) {
        $this->cod_producto = $cod_producto;
        return $this->activar();
    }

    public function obtenerStockProducto($cod_producto) {
        return $this->obtenerStock($cod_producto);
    }

    public function actualizarEstadosStock() {
        return $this->actualizarEstadoPorStock();
    }

    public function verificarExistenciaProducto($cod_producto) {
        $this->cod_producto = $cod_producto;
        return $this->exists();
    }

    // ==================== MÉTODOS PRIVADOS DE BASE DE DATOS ====================

    private function guardar() {
        try {
            $query = "INSERT INTO producto (cod_producto, nombre, precio, unidad, imagen_url, descripcion, estado) 
                      VALUES (:cod_producto, :nombre, :precio, :unidad, :imagen_url, :descripcion, 0)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cod_producto", $this->cod_producto);
            $stmt->bindParam(":nombre", $this->nombre);
            $stmt->bindParam(":precio", $this->precio);
            $stmt->bindParam(":unidad", $this->unidad);
            $stmt->bindParam(":imagen_url", $this->imagen_url);
            $stmt->bindParam(":descripcion", $this->descripcion);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al guardar producto: " . $e->getMessage());
            return false;
        }
    }

    private function listar($searchTerm = '', $minPrice = null, $maxPrice = null) {
        try {
            $query = "SELECT p.*, 
                             COALESCE(SUM(CASE WHEN dm.estado = 1 THEN dm.cant_productos ELSE 0 END), 0) as stock
                      FROM producto p
                      LEFT JOIN detalle_movimiento dm ON p.cod_producto = dm.cod_producto
                      WHERE p.estado = 1";
            
            $params = [];
            
            if (!empty($searchTerm)) {
                $query .= " AND (p.nombre LIKE :searchTerm OR p.cod_producto LIKE :searchTerm)";
                $params[':searchTerm'] = "%$searchTerm%";
            }
            
            if ($minPrice !== null) {
                $query .= " AND p.precio >= :minPrice";
                $params[':minPrice'] = $minPrice;
            }
            
            if ($maxPrice !== null) {
                $query .= " AND p.precio <= :maxPrice";
                $params[':maxPrice'] = $maxPrice;
            }
            
            $query .= " GROUP BY p.cod_producto";
            
            if (empty($searchTerm) && $minPrice === null && $maxPrice === null) {
                $query .= " HAVING stock > 0";
            } else if (!empty($searchTerm)) {
                $query .= " HAVING stock >= 0";
            }
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $paramType = is_float($value) ? PDO::PARAM_STR : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $paramType);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al listar productos: " . $e->getMessage());
            return [];
        }
    }

    private function listarConPaginacion($search = '', $start = 0, $length = 10) {
        try {
            $query = "SELECT p.*, 
                             COALESCE(SUM(CASE WHEN dm.estado = 1 THEN dm.cant_productos ELSE 0 END), 0) as stock
                      FROM producto p
                      LEFT JOIN detalle_movimiento dm ON p.cod_producto = dm.cod_producto
                      WHERE p.estado = 1";
            
            $params = [];
            
            if (!empty($search)) {
                $query .= " AND (p.nombre LIKE :search OR p.cod_producto LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            $query .= " GROUP BY p.cod_producto";
            $query .= " ORDER BY p.cod_producto DESC";
            $query .= " LIMIT :start, :length";
            
            $stmt = $this->conn->prepare($query);
            
            if (!empty($search)) {
                $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
            }
            
            $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
            $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al listar productos paginados: " . $e->getMessage());
            return [];
        }
    }

    private function contarTotal() {
        try {
            $query = "SELECT COUNT(*) as total FROM producto WHERE estado = 1";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Error al contar productos totales: " . $e->getMessage());
            return 0;
        }
    }

    private function contarFiltrados($search) {
        try {
            $query = "SELECT COUNT(DISTINCT p.cod_producto) as total 
                      FROM producto p
                      LEFT JOIN detalle_movimiento dm ON p.cod_producto = dm.cod_producto
                      WHERE p.estado = 1 
                      AND (p.nombre LIKE :search OR p.cod_producto LIKE :search)
                      GROUP BY p.cod_producto";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error al contar productos filtrados: " . $e->getMessage());
            return 0;
        }
    }

    private function obtener() {
        try {
            $query = "SELECT p.*, 
                             COALESCE(SUM(CASE WHEN dm.estado = 1 THEN dm.cant_productos ELSE 0 END), 0) as stock
                      FROM producto p
                      LEFT JOIN detalle_movimiento dm ON p.cod_producto = dm.cod_producto
                      WHERE p.cod_producto = :cod_producto 
                      GROUP BY p.cod_producto
                      LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cod_producto", $this->cod_producto);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener producto: " . $e->getMessage());
            return null;
        }
    }

    private function actualizar() {
        try {
            $query = "UPDATE producto SET 
                      nombre = :nombre, 
                      precio = :precio, 
                      unidad = :unidad,
                      imagen_url = :imagen_url,
                      descripcion = :descripcion 
                      WHERE cod_producto = :cod_producto";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":nombre", $this->nombre);
            $stmt->bindParam(":precio", $this->precio);
            $stmt->bindParam(":unidad", $this->unidad);
            $stmt->bindParam(":imagen_url", $this->imagen_url);
            $stmt->bindParam(":descripcion", $this->descripcion);
            $stmt->bindParam(":cod_producto", $this->cod_producto);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar producto: " . $e->getMessage());
            return false;
        }
    }

    private function eliminar() {
        try {
            $stockActual = $this->obtenerStock($this->cod_producto);
            
            if ($stockActual > 0) {
                $this->conn->beginTransaction();
                
                $queryMov = "INSERT INTO movimiento_entrada (fecha, observaciones, estado) 
                            VALUES (NOW(), 'Salida por eliminación de producto', 1)";
                $stmtMov = $this->conn->prepare($queryMov);
                $stmtMov->execute();
                $numMovimiento = $this->conn->lastInsertId();
                
                $queryDetalle = "INSERT INTO detalle_movimiento 
                                (num_movimiento, cod_producto, cant_productos, precio_venta, estado) 
                                VALUES (:num_movimiento, :cod_producto, -:cantidad, 0, 1)";
                $stmtDetalle = $this->conn->prepare($queryDetalle);
                $stmtDetalle->execute([
                    ':num_movimiento' => $numMovimiento,
                    ':cod_producto' => $this->cod_producto,
                    ':cantidad' => $stockActual
                ]);
                
                $this->actualizarEstadoPorStock();
                
                $this->conn->commit();
                return true;
                
            } else {
                $this->actualizarEstadoPorStock();
            }
            
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error al eliminar producto: " . $e->getMessage());
            return false;
        }
    }

    private function exists() {
        try {
            $query = "SELECT COUNT(*) FROM producto WHERE cod_producto = :cod_producto";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cod_producto", $this->cod_producto);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error al verificar existencia de producto: " . $e->getMessage());
            return false;
        }
    }

    private function listarPorFechas($fechaInicio, $fechaFin, $estado = null) {
        try {
            $query = "SELECT p.*, 
                     COALESCE(SUM(CASE WHEN dm.estado = 1 THEN dm.cant_productos ELSE 0 END), 0) as stock,
                     (SELECT COUNT(*) FROM detalle_pedido dp 
                      JOIN pedido ped ON dp.id_pedido = ped.id_pedido 
                      WHERE dp.cod_producto = p.cod_producto 
                      AND DATE(ped.fecha) BETWEEN :fechaInicio AND :fechaFin) as total_veces_vendido,
                     (SELECT COALESCE(SUM(dp.cantidad), 0) FROM detalle_pedido dp 
                      JOIN pedido ped ON dp.id_pedido = ped.id_pedido 
                      WHERE dp.cod_producto = p.cod_producto 
                      AND ped.estado = 1 
                      AND DATE(ped.fecha) BETWEEN :fechaInicio2 AND :fechaFin2) as cantidad_vendida
                     FROM producto p 
                     LEFT JOIN detalle_movimiento dm ON p.cod_producto = dm.cod_producto
                     WHERE 1=1";
            
            $params = [
                ':fechaInicio' => $fechaInicio,
                ':fechaFin' => $fechaFin,
                ':fechaInicio2' => $fechaInicio,
                ':fechaFin2' => $fechaFin
            ];
            
            if ($estado !== null) {
                $query .= " AND p.estado = :estado";
                $params[':estado'] = $estado;
            }
            
            $query .= " GROUP BY p.cod_producto";
            $query .= " ORDER BY p.nombre ASC";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al listar productos por fechas: " . $e->getMessage());
            return [];
        }
    }

    private function listarEliminados() {
        try {
            $this->actualizarEstadoPorStock();
            
            $query = "SELECT 
                        p.*,
                        COALESCE(
                            (SELECT SUM(cant_productos) 
                             FROM detalle_movimiento 
                             WHERE cod_producto = p.cod_producto 
                             AND estado = 1 
                             GROUP BY cod_producto),
                            0
                        ) as stock
                      FROM producto p
                      WHERE p.estado = 0 OR 
                            NOT EXISTS (
                                SELECT 1 
                                FROM detalle_movimiento dm 
                                WHERE dm.cod_producto = p.cod_producto 
                                AND dm.estado = 1
                                GROUP BY dm.cod_producto
                                HAVING SUM(dm.cant_productos) > 0
                            )
                      ORDER BY p.estado, p.nombre";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al listar productos eliminados: " . $e->getMessage());
            return [];
        }
    }

    private function restaurar() {
        try {
            $query = "UPDATE producto SET estado = 1 WHERE cod_producto = :cod_producto";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cod_producto", $this->cod_producto);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al restaurar producto: " . $e->getMessage());
            return false;
        }
    }

    private function contarProductos() {
        try {
            $query = "SELECT COUNT(*) as total FROM producto WHERE estado = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error al contar productos: " . $e->getMessage());
            return 0;
        }
    }

    private function contarBajoStock() {
        try {
            $query = "SELECT COUNT(*) as total FROM producto WHERE estado = 0";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error al contar productos bajo stock: " . $e->getMessage());
            return 0;
        }
    }

    private function activar() {
        try {
            $query = "UPDATE producto SET estado = 1 WHERE cod_producto = :cod_producto";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cod_producto", $this->cod_producto);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al activar producto: " . $e->getMessage());
            return false;
        }
    }

    private function obtenerStock($cod_producto) {
        try {
            $query = "SELECT COALESCE(SUM(CASE WHEN dm.estado = 1 THEN dm.cant_productos ELSE 0 END), 0) as stock
                      FROM detalle_movimiento dm
                      WHERE dm.cod_producto = :cod_producto";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cod_producto", $cod_producto);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['stock'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error al obtener stock: " . $e->getMessage());
            return 0;
        }
    }

    private function actualizarEstadoPorStock() {
        try {
            $queryActivos = "UPDATE producto p
                           SET estado = 1
                           WHERE EXISTS (
                               SELECT 1 FROM detalle_movimiento dm
                               WHERE dm.cod_producto = p.cod_producto
                               AND dm.estado = 1
                               GROUP BY dm.cod_producto
                               HAVING SUM(dm.cant_productos) > 0
                           )";
            $stmtActivos = $this->conn->prepare($queryActivos);
            $stmtActivos->execute();
            
            $queryInactivos = "UPDATE producto p
                             SET estado = 0
                             WHERE NOT EXISTS (
                                 SELECT 1 FROM detalle_movimiento dm
                                 WHERE dm.cod_producto = p.cod_producto
                                 AND dm.estado = 1
                                 GROUP BY dm.cod_producto
                                 HAVING SUM(dm.cant_productos) > 0
                             )";
            $stmtInactivos = $this->conn->prepare($queryInactivos);
            $stmtInactivos->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Error al actualizar estado de productos por stock: " . $e->getMessage());
            return false;
        }
    }
}