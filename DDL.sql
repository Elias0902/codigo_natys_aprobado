DROP DATABASE IF EXISTS natys;

CREATE DATABASE natys
CHARACTER SET utf8mb4
COLLATE utf8mb4_spanish_ci;

USE natys;


-- Tabla usuario
CREATE TABLE usuario (
  id INT NOT NULL AUTO_INCREMENT,
  correo_usuario VARCHAR(70) NOT NULL, UNIQUE,
  usuario VARCHAR(45) NOT NULL,
  clave VARCHAR(50) NOT NULL,
  rol VARCHAR(30) NOT NULL COMMENT 'Ej: admin, vendedor',
   estado TINYINT(4) NOT NULL DEFAULT 1 COMMENT '0=Inactivo, 1=Activo',
  PRIMARY KEY (id)
);

-- Inserción de usuario de ejemplo
INSERT INTO usuario (nombre_usuario, correo_usuario, usuario, clave, rol, estado)
VALUES ('elias123', 'elias123', 'elias123', 'admin', 1);


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

-- Tabla metodo
CREATE TABLE metodo (
  codigo VARCHAR(10) NOT NULL,
  detalle VARCHAR(50) NOT NULL,
  estado TINYINT(4) NOT NULL DEFAULT 1 COMMENT '0=Inactivo, 1=Activo',
  PRIMARY KEY (codigo)
);

-- Tabla producto
CREATE TABLE producto (
  cod_producto VARCHAR(20) NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  unidad VARCHAR(20) NOT NULL,
  estado TINYINT(4) NOT NULL DEFAULT 1 COMMENT '0=Inactivo, 1=Activo',
  PRIMARY KEY (cod_producto)
);

-- Tabla pago
CREATE TABLE pago (
  id_pago INT NOT NULL AUTO_INCREMENT,
  banco VARCHAR(50) NULL,
  referencia VARCHAR(50) NULL,
  fecha DATE NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  cod_metodo VARCHAR(10) NOT NULL,
  estado TINYINT(4) NOT NULL DEFAULT 1 COMMENT '0=Anulado, 1=Activo',
  PRIMARY KEY (id_pago),
  FOREIGN KEY (cod_metodo) REFERENCES metodo(codigo) ON DELETE CASCADE
);

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
