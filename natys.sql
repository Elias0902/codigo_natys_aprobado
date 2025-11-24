-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-11-2025 a las 18:08:35
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `natys`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `ced_cliente` varchar(20) NOT NULL,
  `nomcliente` varchar(100) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `direccion` text NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=Inactivo, 1=Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`ced_cliente`, `nomcliente`, `telefono`, `correo`, `direccion`, `estado`) VALUES
('12345678', 'María González', '04141234567', 'maria.gonzalez@email.com', 'Av. Principal, Edificio Las Flores, Apt 2B, Caracas', 1),
('23456789', 'Carlos Rodríguez', '04241234568', 'carlos.rodriguez@email.com', 'Calle 10 con Av. 5, Residencias Altamira, Local 3, Valencia', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_movimiento`
--

CREATE TABLE `detalle_movimiento` (
  `num_movimiento` int(11) NOT NULL,
  `cod_producto` varchar(20) NOT NULL,
  `cant_productos` int(11) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=Anulado, 1=Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `detalle_movimiento`
--

INSERT INTO `detalle_movimiento` (`num_movimiento`, `cod_producto`, `cant_productos`, `precio_venta`, `estado`) VALUES
(1, 'GAL001', 100, 0.80, 1),
(2, 'GAL002', 50, 1.20, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id_pedido` int(11) NOT NULL,
  `cod_producto` varchar(20) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=Anulado, 1=Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id_pedido`, `cod_producto`, `precio`, `cantidad`, `subtotal`, `estado`) VALUES
(1, 'GAL001', 0.80, 10, 8.00, 1),
(2, 'GAL002', 1.20, 5, 6.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodo`
--

CREATE TABLE `metodo` (
  `codigo` varchar(10) NOT NULL,
  `detalle` varchar(50) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=Inactivo, 1=Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `metodo`
--

INSERT INTO `metodo` (`codigo`, `detalle`, `estado`) VALUES
('EFECTIVO', 'Pago en efectivo', 1),
('PM', 'Pago móvil', 1),
('TARJETA', 'Tarjeta de crédito/débito', 1),
('TRANSFER', 'Transferencia bancaria', 1),
('ZELLE', 'Transferencia Zelle', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_entrada`
--

CREATE TABLE `movimiento_entrada` (
  `num_movimiento` int(11) NOT NULL,
  `fecha` date NOT NULL DEFAULT curdate(),
  `observaciones` varchar(20) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=Anulado, 1=Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `movimiento_entrada`
--

INSERT INTO `movimiento_entrada` (`num_movimiento`, `fecha`, `observaciones`, `estado`) VALUES
(1, '2025-11-15', 'Entrada inicial', 1),
(2, '2025-11-15', 'Entrada completada', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago`
--

CREATE TABLE `pago` (
  `id_pago` int(11) NOT NULL,
  `banco` varchar(50) DEFAULT NULL,
  `referencia` varchar(50) DEFAULT NULL,
  `fecha` date NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `cod_metodo` varchar(10) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=Anulado, 1=Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `pago`
--

INSERT INTO `pago` (`id_pago`, `banco`, `referencia`, `fecha`, `monto`, `cod_metodo`, `estado`) VALUES
(1, 'N/A', 'N/A', '2025-11-15', 8.00, 'EFECTIVO', 1),
(2, 'Banesco', 'PM123456', '2025-11-15', 6.00, 'PM', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `id_pedido` int(11) NOT NULL,
  `fecha` date NOT NULL DEFAULT curdate(),
  `total` decimal(10,2) NOT NULL,
  `cant_producto` int(11) NOT NULL,
  `ced_cliente` varchar(20) NOT NULL,
  `id_pago` int(11) DEFAULT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=Pendiente, 1=Pagado, 2=Anulado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`id_pedido`, `fecha`, `total`, `cant_producto`, `ced_cliente`, `id_pago`, `estado`) VALUES
(1, '2025-11-15', 8.00, 10, '12345678', 1, 1),
(2, '2025-11-15', 6.00, 5, '23456789', 2, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `cod_producto` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `unidad` varchar(20) NOT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=Inactivo, 1=Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`cod_producto`, `nombre`, `precio`, `unidad`, `imagen_url`, `descripcion`, `estado`) VALUES
('GAL001', 'Galletas Polvorosas', 0.80, 'unidad', NULL, 'Galletas tradicionales polvorosas', 1),
('GAL002', 'Galletas de Avena', 1.20, 'unidad', NULL, 'Galletas saludables de avena', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `correo_usuario` varchar(70) NOT NULL,
  `usuario` varchar(45) NOT NULL,
  `clave` text NOT NULL,
  `rol` varchar(30) NOT NULL COMMENT 'Ej: admin, vendedor',
  `estado` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=Inactivo, 1=Activo',
  `imagen_perfil` varchar(255) DEFAULT '/Natys/Assets/img/avatar.png',
  `imagen_perfil_blob` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `correo_usuario`, `usuario`, `clave`, `rol`, `estado`, `imagen_perfil`, `imagen_perfil_blob`) VALUES
(1, 'eliasarmas0902@gmail.com', 'admin', '$2y$10$dvrb/rpGZQ.QkDhfRCooXuUoGukjWOkc6lcd1xWs0D0PIeQqx7muS', 'superadmin', 1, '/Natys/Assets/img/usuarios/image_77d80071129effa74d038240a71284f7.png', NULL),
(2, 'vendedor@natys.com', 'vendedor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendedor', 1, '/Natys/Assets/img/avatar.png', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`ced_cliente`);

--
-- Indices de la tabla `detalle_movimiento`
--
ALTER TABLE `detalle_movimiento`
  ADD PRIMARY KEY (`num_movimiento`,`cod_producto`),
  ADD KEY `cod_producto` (`cod_producto`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id_pedido`,`cod_producto`),
  ADD KEY `cod_producto` (`cod_producto`);

--
-- Indices de la tabla `metodo`
--
ALTER TABLE `metodo`
  ADD PRIMARY KEY (`codigo`);

--
-- Indices de la tabla `movimiento_entrada`
--
ALTER TABLE `movimiento_entrada`
  ADD PRIMARY KEY (`num_movimiento`);

--
-- Indices de la tabla `pago`
--
ALTER TABLE `pago`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `cod_metodo` (`cod_metodo`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `ced_cliente` (`ced_cliente`),
  ADD KEY `id_pago` (`id_pago`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`cod_producto`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo_usuario` (`correo_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `movimiento_entrada`
--
ALTER TABLE `movimiento_entrada`
  MODIFY `num_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pago`
--
ALTER TABLE `pago`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_movimiento`
--
ALTER TABLE `detalle_movimiento`
  ADD CONSTRAINT `detalle_movimiento_ibfk_1` FOREIGN KEY (`num_movimiento`) REFERENCES `movimiento_entrada` (`num_movimiento`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_movimiento_ibfk_2` FOREIGN KEY (`cod_producto`) REFERENCES `producto` (`cod_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`cod_producto`) REFERENCES `producto` (`cod_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pago`
--
ALTER TABLE `pago`
  ADD CONSTRAINT `pago_ibfk_1` FOREIGN KEY (`cod_metodo`) REFERENCES `metodo` (`codigo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `pedido_ibfk_1` FOREIGN KEY (`ced_cliente`) REFERENCES `cliente` (`ced_cliente`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedido_ibfk_2` FOREIGN KEY (`id_pago`) REFERENCES `pago` (`id_pago`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
