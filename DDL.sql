DROP DATABASE IF EXISTS natys;

CREATE DATABASE natys
CHARACTER SET utf8mb4
COLLATE utf8mb4_spanish_ci;

USE natys;

-- Tabla usuario
CREATE TABLE usuario (
  id INT NOT NULL AUTO_INCREMENT,
  usuario VARCHAR(45) NOT NULL,
  correo_usuario VARCHAR(50) NOT NULL,
  clave VARCHAR(50) NOT NULL,
  rol VARCHAR(30) NOT NULL COMMENT 'Ej: admin, vendedor',
  estado TINYINT(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY (correo_usuario)
);

-- Inserción de usuarios de ejemplo
INSERT INTO usuario (usuario, correo_usuario, clave, rol, estado) VALUES 
('elias123', 'elias123', 'elias123', 'admin', 1),
('Sluzzen', 'santiagoloyo2005@gmail.com', 'Sluzzen', 'vendedor', 1);

-- Tabla cliente
CREATE TABLE cliente (
  ced_cliente VARCHAR(20) NOT NULL,
  nomcliente VARCHAR(100) NOT NULL,
  telefono VARCHAR(15) NOT NULL,
  correo VARCHAR(100) NOT NULL,
  direccion TEXT NOT NULL,
  estado TINYINT(4) NOT NULL DEFAULT 1 COMMENT '0=Inactivo, 1=Activo',
  PRIMARY KEY (ced_cliente)
);

-- Inserción de cliente de ejemplo
INSERT INTO cliente (ced_cliente, nomcliente, telefono, correo, direccion, estado) VALUES 
('30675738', 'Santiago Loyo', '04245797722', 'santiagoloyo2005@gmail.com', 'Club Hipico las trinitarias', 1);

-- Tabla metodo
CREATE TABLE metodo (
  codigo VARCHAR(10) NOT NULL,
  detalle VARCHAR(50) NOT NULL,
  estado TINYINT(4) NOT NULL DEFAULT 1 COMMENT '0=Inactivo, 1=Activo',
  PRIMARY KEY (codigo)
);

-- Inserción de métodos de pago
INSERT INTO metodo (codigo, detalle, estado) VALUES 
('12', 'Efectivo', 1);

-- Tabla producto
CREATE TABLE producto (
  cod_producto VARCHAR(20) NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  unidad VARCHAR(20) NOT NULL,
  estado TINYINT(4) NOT NULL DEFAULT 1 COMMENT '0=Inactivo, 1=Activo',
  PRIMARY KEY (cod_producto)
);

-- Inserción de productos
INSERT INTO producto (cod_producto, nombre, precio, unidad, estado) VALUES 
('JAL232', 'PAN', 50.00, 'kg', 1),
('JAL2324', 'Galletas Polvorosas', 10.00, 'unidad', 1);

-- Tabla pago
CREATE TABLE pago (
  id_pago INT NOT NULL AUTO_INCREMENT,
  banco VARCHAR(50) DEFAULT NULL,
  referencia VARCHAR(50) DEFAULT NULL,
  fecha DATE NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  cod_metodo VARCHAR(10) NOT NULL,
  estado TINYINT(4) NOT NULL DEFAULT 1 COMMENT '0=Anulado, 1=Activo',
  PRIMARY KEY (id_pago),
  FOREIGN KEY (cod_metodo) REFERENCES metodo(codigo) ON DELETE CASCADE
);

-- Inserción de pagos de ejemplo
INSERT INTO pago (id_pago, banco, referencia, fecha, monto, cod_metodo, estado) VALUES 
(1, 'Banesco', '3243', '2025-06-28', 10.00, '12', 0);

-- Tabla pedido
CREATE TABLE pedido (
  id_pedido INT NOT NULL AUTO_INCREMENT,
  fecha DATE NOT NULL DEFAULT CURDATE(),
  total DECIMAL(10,2) NOT NULL,
  cant_producto INT NOT NULL,
  ced_cliente VARCHAR(20) NOT NULL,
  id_pago INT NOT NULL,
  estado TINYINT(4) NOT NULL DEFAULT 1 COMMENT '0=Anulado, 1=Activo',
  PRIMARY KEY (id_pedido),
  FOREIGN KEY (ced_cliente) REFERENCES cliente(ced_cliente) ON DELETE CASCADE,
  FOREIGN KEY (id_pago) REFERENCES pago(id_pago) ON DELETE CASCADE
);

-- Tabla detalle_pedido
CREATE TABLE detalle_pedido (
  id_pedido INT NOT NULL,
  cod_producto VARCHAR(20) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  cantidad INT NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  estado TINYINT(4) NOT NULL DEFAULT 1 COMMENT '0=Anulado, 1=Activo',
  PRIMARY KEY (id_pedido, cod_producto),
  FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido) ON DELETE CASCADE,
  FOREIGN KEY (cod_producto) REFERENCES producto(cod_producto) ON DELETE CASCADE
);

-- Tabla movimiento_entrada
CREATE TABLE movimiento_entrada (
  num_movimiento INT NOT NULL AUTO_INCREMENT,
  fecha DATE NOT NULL DEFAULT CURDATE(),
  observaciones VARCHAR(20) NOT NULL,
  estado TINYINT(4) NOT NULL DEFAULT 1 COMMENT '0=Anulado, 1=Activo',
  PRIMARY KEY (num_movimiento)
);

-- Tabla detalle_movimiento
CREATE TABLE detalle_movimiento (
  num_movimiento INT NOT NULL,
  cod_producto VARCHAR(20) NOT NULL,
  cant_productos INT NOT NULL,
  precio_venta DECIMAL(10,2) NOT NULL,
  estado TINYINT(4) NOT NULL DEFAULT 1 COMMENT '0=Anulado, 1=Activo',
  PRIMARY KEY (num_movimiento, cod_producto),
  FOREIGN KEY (num_movimiento) REFERENCES movimiento_entrada(num_movimiento) ON DELETE CASCADE,
  FOREIGN KEY (cod_producto) REFERENCES producto(cod_producto) ON DELETE CASCADE
);

-- Configuración de autoincrementos
ALTER TABLE movimiento_entrada AUTO_INCREMENT = 1;
ALTER TABLE pago AUTO_INCREMENT = 2;
ALTER TABLE pedido AUTO_INCREMENT = 1;
ALTER TABLE usuario AUTO_INCREMENT = 4;
