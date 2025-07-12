-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-07-2025 a las 07:03:50
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `proyecto_flota`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aseguradoras_soat`
--

CREATE TABLE `aseguradoras_soat` (
  `id_asegura` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `aseguradoras_soat`
--

INSERT INTO `aseguradoras_soat` (`id_asegura`, `nombre`) VALUES
(20, 'Aseguradora Solidaria de Colombia Ltda.'),
(21, 'AXA Colpatria Seguros S.A.'),
(22, 'La Equidad Seguros Generales'),
(23, 'Liberty Seguros S.A.'),
(24, 'La Previsora S.A. Compañía de Seguros'),
(25, 'Seguros Bolívar S.A.'),
(26, 'Seguros Mundial'),
(27, 'Seguros del Estado S.A.'),
(28, 'Seguros Generales Suramericana S.A.'),
(29, 'HDI Seguros Colombia S.A.'),
(30, 'Mapfre Seguros Generales de Colombia S.A.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_licencia`
--

CREATE TABLE `categoria_licencia` (
  `id_categoria` int(11) NOT NULL,
  `nombre_categoria` text NOT NULL,
  `id_servicio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria_licencia`
--

INSERT INTO `categoria_licencia` (`id_categoria`, `nombre_categoria`, `id_servicio`) VALUES
(1, 'A1 - Motocicletas hasta 125cc', 1),
(2, 'A2 - Motocicletas mayores a 125cc y vehículos similares', 1),
(3, 'B1 - Automóviles, camperos, camionetas y vans de servicio particular', 1),
(4, 'B2 - Camiones rígidos, buses y busetas de servicio particular', 1),
(5, 'B3 - Vehículos articulados de servicio particular', 1),
(6, 'C1 - Automóviles, camperos, camionetas y vans de servicio público', 2),
(7, 'C2 - Camiones rígidos, buses y busetas de servicio público', 2),
(8, 'C3 - Vehículos articulados de servicio público', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `centro_rtm`
--

CREATE TABLE `centro_rtm` (
  `id_centro` int(11) NOT NULL,
  `centro_revision` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `centro_rtm`
--

INSERT INTO `centro_rtm` (`id_centro`, `centro_revision`) VALUES
(1, 'DIAGNOSTICENTRO DEL NORTE MARIQUITA LTDA'),
(2, 'C.D.A. DIAGNOSTICAR'),
(3, 'IVESUR COLOMBIA - IBAGUE'),
(4, 'C.D.A. DIAGNOSTI-MOTOS ESPINAL'),
(5, 'CDA PEÑAS DEL RIO'),
(6, 'CDA MOTO CLUB AMBALA SAS'),
(7, 'CENTRO DE DIAGNOSTICO Y REVISIÓN DE VEHÍCULOS AUTOMOTORES “C'),
(8, 'CDA DEL TOLIMA'),
(9, 'CENTRO DE DIAGNOSTICO AUTOMOTRIZ TECNI MOTORS IBAGUE'),
(10, 'CENTRO DE DIAGNOSTICO AUTOMOTOR CDA BETANIA'),
(11, 'CENTRO DE DIAGNOSTICO AUTOMOTOR EL CARMEN S.A.S.'),
(12, 'CDA AUTOMOTOS DEL TOLIMA'),
(13, 'CDA MOTOS IBAGUE S.A.S'),
(14, 'CDA DEL CENTRO SAS'),
(15, 'CDA LA REVISION SAS'),
(16, 'CDA MOTOS DE LA SEXTA'),
(17, 'CENTRO DE DIAGNÓSTICO AUTOMOTRIZ DEL ESPINAL S.A.S.'),
(18, 'CDA REVIEXPRESS S.A.S'),
(19, 'CDA CEDITRANS S.A.'),
(20, 'CDA DIAGNOSTILISTO S.A.S.'),
(21, 'CDA DIAGNOSTI-CAR'),
(22, 'CDA TECNIMOTO AUTOS ESPINAL S.A.S');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clasificacion_trabajo`
--

CREATE TABLE `clasificacion_trabajo` (
  `id` int(11) NOT NULL,
  `Trabajo` varchar(255) NOT NULL,
  `Precio` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clasificacion_trabajo`
--

INSERT INTO `clasificacion_trabajo` (`id`, `Trabajo`, `Precio`) VALUES
(1, 'Aceite 5W-30 4L', 120000),
(2, 'Cambio de pastillas de freno', 150000),
(3, 'Alineación y balanceo', 80000),
(4, 'Revisión de suspensión', 100000),
(5, 'Cambio de batería', 200000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colores`
--

CREATE TABLE `colores` (
  `id_color` int(11) NOT NULL,
  `color` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `colores`
--

INSERT INTO `colores` (`id_color`, `color`) VALUES
(1, 'Rojo'),
(2, 'Azul'),
(3, 'Verde'),
(4, 'Negro'),
(5, 'Blanco'),
(6, 'Gris'),
(7, 'Amarillo'),
(8, 'Naranja'),
(9, 'Morado'),
(10, 'Café'),
(11, 'Plateado'),
(12, 'Dorado'),
(13, 'Beige'),
(14, 'Turquesa'),
(15, 'Vinotinto'),
(16, 'Fucsia'),
(17, 'Lila'),
(18, 'Azul Oscuro'),
(19, 'Verde Militar'),
(20, 'Blanco Perla');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contacto`
--

CREATE TABLE `contacto` (
  `id_mensa` int(11) NOT NULL,
  `nom` text NOT NULL,
  `apellido` text NOT NULL,
  `email` varchar(100) NOT NULL,
  `mensaje` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `correos_enviados_licencia`
--

CREATE TABLE `correos_enviados_licencia` (
  `id_correos` int(11) NOT NULL,
  `id_licencia` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `tipo_recordatorio` varchar(20) NOT NULL,
  `fecha_envio` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `correos_enviados_llantas`
--

CREATE TABLE `correos_enviados_llantas` (
  `id_correo` int(11) NOT NULL,
  `id_llantas` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `tipo_recordatorio` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `correos_enviados_mantenimiento`
--

CREATE TABLE `correos_enviados_mantenimiento` (
  `id_correo` int(11) NOT NULL,
  `id_mantenimiento` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `tipo_recordatorio` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `correos_enviados_pico_placa`
--

CREATE TABLE `correos_enviados_pico_placa` (
  `id_correos` int(11) NOT NULL,
  `placa` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `fecha_envio` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `correos_enviados_soat`
--

CREATE TABLE `correos_enviados_soat` (
  `id_correos` int(11) NOT NULL,
  `id_soat` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `tipo_recordatorio` varchar(20) NOT NULL,
  `fecha_envio` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `correos_enviados_tecno`
--

CREATE TABLE `correos_enviados_tecno` (
  `id_correo` int(11) NOT NULL,
  `id_rtm` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `tipo_recordatorio` varchar(20) NOT NULL,
  `fecha_envio` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id_empresa` int(11) NOT NULL,
  `nombre_empresa` varchar(255) NOT NULL,
  `nit` varchar(20) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('activa','inactiva') DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`id_empresa`, `nombre_empresa`, `nit`, `direccion`, `telefono`, `email`, `fecha_registro`, `estado`) VALUES
(1, 'FlotaX AGC', '900123456-1', 'Calle Principal 123', '3001234567', 'admin@flotaxagc.com', '2025-07-05 15:00:41', 'activa'),
(3, 'TeamTalks', '8123918249-123', 'Mza F casa 18 B/ Portal de los Tunjos ', '3133409045', 'jsebaslozano2006@gmail.com', '2025-07-09 01:46:45', 'activa'),
(4, 'SENA', '876543121', 'Bogota', '3209032929', 'sena@gmail.com', '2025-07-09 20:40:21', 'activa'),
(5, 'Mullen lowe Global', '87654324422', 'calle 80 90 12', '3102002000', 'mullenlowe@mulenloweglobal', '2025-07-09 20:41:46', 'activa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_soat`
--

CREATE TABLE `estado_soat` (
  `id_stado` int(11) NOT NULL,
  `soat_est` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_soat`
--

INSERT INTO `estado_soat` (`id_stado`, `soat_est`) VALUES
(1, 'Vigente'),
(2, 'Vencido');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_usuario`
--

CREATE TABLE `estado_usuario` (
  `id_estado` int(11) NOT NULL,
  `tipo_stade` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_usuario`
--

INSERT INTO `estado_usuario` (`id_estado`, `tipo_stade`) VALUES
(1, 'Activo'),
(2, 'Inactivo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_vehiculo`
--

CREATE TABLE `estado_vehiculo` (
  `id_estado` varchar(50) NOT NULL,
  `estado` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_vehiculo`
--

INSERT INTO `estado_vehiculo` (`id_estado`, `estado`) VALUES
('1', 'Activo'),
('10', 'En uso'),
('2', 'Inactivo'),
('3', 'Mantenimiento'),
('4', 'Revisión'),
('5', 'Retirado'),
('6', 'Accidentado'),
('7', 'Pendiente'),
('8', 'Disponible'),
('9', 'Bloqueado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `licencias`
--

CREATE TABLE `licencias` (
  `id_documento` varchar(20) NOT NULL,
  `id_licencia` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `fecha_expedicion` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `observaciones` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `licencias`
--
DELIMITER $$
CREATE TRIGGER `after_insert_licencia` AFTER INSERT ON `licencias` FOR EACH ROW BEGIN
  INSERT INTO licencia_log (
    documento_usuario,
    id_categoria,
    fecha_expedicion,
    fecha_vencimiento,
    id_servicio,
    observaciones
  ) VALUES (
    NEW.id_documento,
    NEW.id_categoria,
    NEW.fecha_expedicion,
    NEW.fecha_vencimiento,
    NEW.id_servicio,
    NEW.observaciones
  );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `licencia_log`
--

CREATE TABLE `licencia_log` (
  `id_log` int(11) NOT NULL,
  `documento_usuario` varchar(20) DEFAULT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `fecha_expedicion` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `id_servicio` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `llantas`
--

CREATE TABLE `llantas` (
  `id_llanta` int(11) NOT NULL,
  `placa` varchar(10) NOT NULL,
  `estado` varchar(50) NOT NULL,
  `ultimo_cambio` date DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `presion_llantas` decimal(4,1) DEFAULT NULL,
  `kilometraje_actual` int(11) DEFAULT NULL,
  `proximo_cambio_km` int(11) DEFAULT NULL,
  `proximo_cambio_fecha` date DEFAULT NULL,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `llantas`
--
DELIMITER $$
CREATE TRIGGER `after_insert_llantas` AFTER INSERT ON `llantas` FOR EACH ROW BEGIN
  DECLARE doc_usuario VARCHAR(20);

  -- Buscar el documento del usuario relacionado con la placa
  SELECT Documento INTO doc_usuario
  FROM vehiculos
  WHERE placa = NEW.placa
  LIMIT 1;

  -- Insertar en la tabla de historial
  INSERT INTO llantas_log (
    documento_usuario,
    placa_vehiculo,
    estado_llantas,
    ultimo_cambio,
    presion_llantas,
    kilometraje_actual,
    proximo_cambio_km,
    proximo_cambio_fecha,
    notas
  ) VALUES (
    doc_usuario,
    NEW.placa,
    NEW.estado,
    NEW.ultimo_cambio,
    NEW.presion_llantas,
    NEW.kilometraje_actual,
    NEW.proximo_cambio_km,
    NEW.proximo_cambio_fecha,
    NEW.notas
  );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `llantas_log`
--

CREATE TABLE `llantas_log` (
  `id_log` int(11) NOT NULL,
  `documento_usuario` varchar(20) DEFAULT NULL,
  `placa_vehiculo` varchar(10) DEFAULT NULL,
  `estado_llantas` varchar(20) DEFAULT NULL,
  `ultimo_cambio` date DEFAULT NULL,
  `presion_llantas` decimal(5,2) DEFAULT NULL,
  `kilometraje_actual` int(11) DEFAULT NULL,
  `proximo_cambio_km` int(11) DEFAULT NULL,
  `proximo_cambio_fecha` date DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_sistema`
--

CREATE TABLE `logs_sistema` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) DEFAULT NULL,
  `accion` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `logs_sistema`
--

INSERT INTO `logs_sistema` (`id`, `usuario`, `accion`, `descripcion`, `fecha`, `ip_address`) VALUES
(1, '1109491416', 'Licencia creada', 'Nueva licencia creada para SENA', '2025-07-05 14:26:46', '::1'),
(1109491416, 'Edwar Farid Gomez Sanchez', 'creada', 'Nueva licencia creada para SENA', '2025-07-05 16:04:15', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_accesos_superadmin`
--

CREATE TABLE `log_accesos_superadmin` (
  `id` int(11) NOT NULL,
  `documento` varchar(20) NOT NULL,
  `fecha_acceso` datetime NOT NULL,
  `ip_acceso` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_registros`
--

CREATE TABLE `log_registros` (
  `id_log` int(11) NOT NULL,
  `documento_usuario` varchar(20) DEFAULT NULL,
  `email_usuario` varchar(100) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `log_registros`
--

INSERT INTO `log_registros` (`id_log`, `documento_usuario`, `email_usuario`, `fecha_registro`, `descripcion`) VALUES
(16, '1109492105a', 'jsebaslozano2006@gmail.com', '2025-07-08 14:10:42', 'Nuevo Usuario registrado'),
(17, '65634846', 'magdys_2007@hotmail.com', '2025-07-08 21:30:20', 'Nuevo Usuario registrado'),
(18, '65634846', 'magdys_2007@hotmail.com', '2025-07-08 21:39:14', 'Nuevo Usuario registrado'),
(19, '1109490190', 'Milady2408@gmail.com', '2025-07-09 17:05:56', 'Nuevo Usuario registrado'),
(21, '222222222', 'edwardfaridg@gmail.com', '2025-07-10 23:25:05', 'Nuevo Usuario registrado'),
(22, '1109491416', 'edwarfaridg@gmail.com', '2025-07-11 23:33:36', 'Nuevo Usuario registrado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mantenimiento`
--

CREATE TABLE `mantenimiento` (
  `id_mantenimiento` int(11) NOT NULL,
  `placa` varchar(10) NOT NULL,
  `id_tipo_mantenimiento` varchar(50) NOT NULL,
  `fecha_programada` date NOT NULL,
  `fecha_realizada` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `kilometraje_actual` int(11) DEFAULT NULL,
  `proximo_cambio_km` int(11) DEFAULT NULL,
  `proximo_cambio_fecha` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mantenimiento`
--

INSERT INTO `mantenimiento` (`id_mantenimiento`, `placa`, `id_tipo_mantenimiento`, `fecha_programada`, `fecha_realizada`, `observaciones`, `kilometraje_actual`, `proximo_cambio_km`, `proximo_cambio_fecha`) VALUES
(15, 'EDW202', '2', '2025-07-11', '2025-07-11', 'eqd', 64534231, NULL, NULL);

--
-- Disparadores `mantenimiento`
--
DELIMITER $$
CREATE TRIGGER `after_insert_mantenimiento` AFTER INSERT ON `mantenimiento` FOR EACH ROW BEGIN
  DECLARE doc_usuario VARCHAR(20);

  -- Buscar el documento del usuario a partir de la placa
  SELECT Documento INTO doc_usuario
  FROM vehiculos
  WHERE placa = NEW.placa
  LIMIT 1;

  -- Insertar en el log
  INSERT INTO mantenimiento_log (
    documento_usuario,
    placa_vehiculo,
    id_tipo_mantenimiento,
    fecha_programada,
    fecha_realizada,
    kilometraje_actual,
    proximo_cambio_km,
    proximo_cambio_fecha,
    observaciones
  ) VALUES (
    doc_usuario,
    NEW.placa,
    NEW.id_tipo_mantenimiento,
    NEW.fecha_programada,
    NEW.fecha_realizada,
    NEW.kilometraje_actual,
    NEW.proximo_cambio_km,
    NEW.proximo_cambio_fecha,
    NEW.observaciones
  );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mantenimiento_log`
--

CREATE TABLE `mantenimiento_log` (
  `id_log` int(11) NOT NULL,
  `documento_usuario` varchar(20) DEFAULT NULL,
  `placa_vehiculo` varchar(10) DEFAULT NULL,
  `id_tipo_mantenimiento` int(11) DEFAULT NULL,
  `fecha_programada` date DEFAULT NULL,
  `fecha_realizada` date DEFAULT NULL,
  `kilometraje_actual` int(11) DEFAULT NULL,
  `proximo_cambio_km` int(11) DEFAULT NULL,
  `proximo_cambio_fecha` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mantenimiento_log`
--

INSERT INTO `mantenimiento_log` (`id_log`, `documento_usuario`, `placa_vehiculo`, `id_tipo_mantenimiento`, `fecha_programada`, `fecha_realizada`, `kilometraje_actual`, `proximo_cambio_km`, `proximo_cambio_fecha`, `observaciones`, `fecha_registro`) VALUES
(3, '1109490190', '222222', 2, '2025-07-11', '2025-07-10', 1234567890, NULL, NULL, '', '2025-07-10 21:14:18'),
(4, '1109490190', 'AAS293', 2, '2025-07-12', '2025-07-10', 23456789, 34567890, '0001-02-02', '3j1l2e', '2025-07-10 22:30:31'),
(5, '1109490190', 'EYU03F', 2, '2025-07-11', '2025-07-09', 8129, 921, '9299-02-02', '', '2025-07-10 22:51:56'),
(6, '1109490190', 'mmm234', 2, '2025-07-12', '2025-07-11', 12331, 23324, '2022-02-03', '2i12ik', '2025-07-10 22:58:42'),
(7, '1109490190', 'EDEDWD', 2, '2025-07-11', '2025-07-11', 99999, 9999, '2025-07-11', 'e13twyretyfkguhj', '2025-07-11 14:51:14'),
(8, '1109490190', 'EDW202', 2, '2025-07-11', '2025-07-11', 12345, 12345, '2025-07-11', 'qefasfd\r\n', '2025-07-11 15:53:49'),
(9, '1109490190', 'EDW202', 2, '2025-07-11', '2025-07-11', 64534231, NULL, NULL, 'eqd', '2025-07-11 22:19:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marca`
--

CREATE TABLE `marca` (
  `id_marca` int(11) NOT NULL,
  `nombre_marca` varchar(50) NOT NULL,
  `id_tipo_vehiculo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marca`
--

INSERT INTO `marca` (`id_marca`, `nombre_marca`, `id_tipo_vehiculo`) VALUES
(1, 'Chevrolet', 1),
(2, 'Toyota', 1),
(3, 'Mazda', 1),
(4, 'Kia', 1),
(5, 'Renault', 1),
(6, 'Hyundai', 1),
(7, 'Volkswagen', 1),
(8, 'Nissan', 1),
(9, 'Ford', 1),
(10, 'Honda', 1),
(11, 'Peugeot', 1),
(12, 'Fiat', 1),
(13, 'Skoda', 1),
(14, 'Subaru', 1),
(15, 'Lada', 1),
(16, 'AKT', 2),
(17, 'Yamaha', 2),
(18, 'Suzuki', 2),
(19, 'Honda', 2),
(20, 'Bajaj', 2),
(21, 'KTM', 2),
(22, 'TVS', 2),
(23, 'Royal Enfield', 2),
(24, 'Hero', 2),
(25, 'Benelli', 2),
(26, 'Harley-Davidson', 2),
(27, 'Aprilia', 2),
(28, 'Ducati', 2),
(29, 'BMW Motorrad', 2),
(30, 'Toyota', 3),
(31, 'Mazda', 3),
(32, 'Chevrolet', 3),
(33, 'Nissan', 3),
(34, 'Ford', 3),
(35, 'Hyundai', 3),
(36, 'Mitsubishi', 3),
(37, 'Volkswagen', 3),
(38, 'Jeep', 3),
(39, 'Kia', 3),
(40, 'Freightliner', 4),
(41, 'Kenworth', 4),
(42, 'Volvo', 4),
(43, 'Hino', 4),
(44, 'International', 4),
(45, 'Isuzu', 4),
(46, 'Mack', 4),
(47, 'Scania', 4),
(48, 'Mercedes-Benz', 4),
(49, 'MAN', 4),
(50, 'Mercedes-Benz', 5),
(51, 'Chevrolet', 5),
(52, 'Volkswagen', 5),
(53, 'Hino', 5),
(54, 'Renault', 5),
(55, 'Hyundai', 5),
(56, 'Toyota', 5),
(57, 'Nissan', 5),
(58, 'Volvo', 5),
(59, 'Scania', 5),
(60, 'Ford', 6),
(61, 'Toyota', 6),
(62, 'Chevrolet', 6),
(63, 'Nissan', 6),
(64, 'Mitsubishi', 6),
(65, 'Jeep', 7),
(66, 'Ford', 7),
(67, 'Toyota', 7),
(68, 'Hyundai', 7),
(69, 'Mazda', 7),
(70, 'Chevrolet', 7),
(71, 'Kia', 8),
(72, 'Hyundai', 8),
(73, 'Nissan', 8),
(74, 'Subaru', 8),
(75, 'Toyota', 8),
(76, 'Mazda', 8),
(77, 'Chevrolet', 9),
(78, 'Renault', 9),
(79, 'Hyundai', 9),
(80, 'Ford', 9),
(81, 'Toyota', 9),
(82, 'Kenworth', 10),
(83, 'Volvo', 10),
(84, 'Freightliner', 10),
(85, 'Scania', 10),
(86, 'International', 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `documento_usuario` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mensaje` text NOT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  `leido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `documento_usuario`, `mensaje`, `fecha`, `leido`) VALUES
(37, '987654321', 'ekkad', '2025-07-11 03:03:53', 1),
(38, '987654321', 'ekkad', '2025-07-11 03:03:56', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pico_placa`
--

CREATE TABLE `pico_placa` (
  `id_pico_placa` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `semestre` enum('1','2') NOT NULL,
  `dia` enum('Lunes','Martes','Miercoles','Jueves','Viernes') NOT NULL,
  `digitos_restringidos` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pico_placa`
--

INSERT INTO `pico_placa` (`id_pico_placa`, `anio`, `semestre`, `dia`, `digitos_restringidos`) VALUES
(3, 2025, '2', 'Lunes', '1,2'),
(4, 2025, '2', 'Martes', '3,4'),
(5, 2025, '2', 'Miercoles', '5,6'),
(6, 2025, '2', 'Jueves', '7,8'),
(7, 2025, '2', 'Viernes', '9,0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_vehiculos_log`
--

CREATE TABLE `registro_vehiculos_log` (
  `id_log` int(11) NOT NULL,
  `documento_usuario` varchar(20) DEFAULT NULL,
  `placa_vehiculo` varchar(10) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `tip_rol` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `tip_rol`) VALUES
(1, 'Administrador'),
(2, 'Usuario'),
(3, 'Superadmin');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios_licencias`
--

CREATE TABLE `servicios_licencias` (
  `id_servicio` int(11) NOT NULL,
  `nombre_servicios` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios_licencias`
--

INSERT INTO `servicios_licencias` (`id_servicio`, `nombre_servicios`) VALUES
(1, 'Particular'),
(2, 'Publico');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sistema_licencias`
--

CREATE TABLE `sistema_licencias` (
  `id` int(11) NOT NULL,
  `id_empresa` int(100) NOT NULL,
  `tipo_licencia` enum('basica','profesional','empresarial') DEFAULT 'basica',
  `fecha_inicio` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `max_usuarios` int(11) DEFAULT 10,
  `max_vehiculos` int(11) DEFAULT 50,
  `estado` enum('activa','vencida','suspendida') DEFAULT 'activa',
  `clave_licencia` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sistema_licencias`
--

INSERT INTO `sistema_licencias` (`id`, `id_empresa`, `tipo_licencia`, `fecha_inicio`, `fecha_vencimiento`, `max_usuarios`, `max_vehiculos`, `estado`, `clave_licencia`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(5, 3, 'basica', '2025-07-09', '2026-07-09', 5, 10, 'activa', 'FLOTAX-A2066D41', '2025-07-09 01:47:21', '2025-07-09 01:57:30'),
(6, 1, 'empresarial', '2025-07-24', '2026-08-09', 100, 500, 'activa', 'FLOTAX-049BAB6D', '2025-07-09 02:01:25', '2025-07-09 02:01:25'),
(7, 5, 'profesional', '2025-07-10', '2026-07-09', 20, 50, 'activa', 'FLOTAX-539D9021', '2025-07-09 20:43:00', '2025-07-09 20:43:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `soat`
--

CREATE TABLE `soat` (
  `id_soat` int(11) NOT NULL,
  `id_placa` varchar(10) NOT NULL,
  `fecha_expedicion` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `id_aseguradora` int(11) NOT NULL,
  `id_estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `soat`
--
DELIMITER $$
CREATE TRIGGER `after_insert_soat` AFTER INSERT ON `soat` FOR EACH ROW BEGIN
  DECLARE doc_usuario VARCHAR(20);

  -- Buscar el documento del usuario a partir de la placa
  SELECT Documento INTO doc_usuario
  FROM vehiculos
  WHERE placa = NEW.id_placa
  LIMIT 1;

  -- Insertar en la tabla log
  INSERT INTO soat_log (
    documento_usuario,
    placa_vehiculo,
    fecha_expedicion,
    fecha_vencimiento,
    id_aseguradora,
    estado
  ) VALUES (
    doc_usuario,
    NEW.id_placa,
    NEW.fecha_expedicion,
    NEW.fecha_vencimiento,
    NEW.id_aseguradora,
    NEW.id_estado
  );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `soat_log`
--

CREATE TABLE `soat_log` (
  `id_log` int(11) NOT NULL,
  `documento_usuario` varchar(20) DEFAULT NULL,
  `placa_vehiculo` varchar(10) DEFAULT NULL,
  `fecha_expedicion` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `id_aseguradora` int(11) DEFAULT NULL,
  `estado` int(11) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `soat_log`
--

INSERT INTO `soat_log` (`id_log`, `documento_usuario`, `placa_vehiculo`, `fecha_expedicion`, `fecha_vencimiento`, `id_aseguradora`, `estado`, `fecha_registro`) VALUES
(1, '1109491416', 'MOM202', '2025-07-02', '2026-07-02', 28, 1, '2025-07-03 02:13:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tecnomecanica`
--

CREATE TABLE `tecnomecanica` (
  `id_rtm` int(11) NOT NULL,
  `id_placa` varchar(11) NOT NULL,
  `id_centro_revision` int(11) NOT NULL,
  `fecha_expedicion` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `id_estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `tecnomecanica`
--
DELIMITER $$
CREATE TRIGGER `after_insert_tecnomecanica` AFTER INSERT ON `tecnomecanica` FOR EACH ROW BEGIN
  DECLARE doc_usuario VARCHAR(20);
  
  -- Obtener el documento del usuario desde la tabla vehiculos
  SELECT Documento INTO doc_usuario
  FROM vehiculos
  WHERE placa = NEW.id_placa
  LIMIT 1;

  -- Insertar en el log
  INSERT INTO tecnomecanica_log (
    documento_usuario,
    placa_vehiculo,
    centro_revision,
    fecha_expedicion,
    fecha_vencimiento,
    estado
  ) VALUES (
    doc_usuario,
    NEW.id_placa,
    NEW.id_centro_revision,
    NEW.fecha_expedicion,
    NEW.fecha_vencimiento,
    NEW.id_estado
  );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tecnomecanica_log`
--

CREATE TABLE `tecnomecanica_log` (
  `id_log` int(11) NOT NULL,
  `documento_usuario` varchar(20) DEFAULT NULL,
  `placa_vehiculo` varchar(10) DEFAULT NULL,
  `centro_revision` int(11) DEFAULT NULL,
  `fecha_expedicion` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `estado` int(11) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_documentacion`
--

CREATE TABLE `tipo_documentacion` (
  `id_tipo_documento` varchar(50) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_gasto`
--

CREATE TABLE `tipo_gasto` (
  `id_tipo_gasto` varchar(50) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_mantenimiento`
--

CREATE TABLE `tipo_mantenimiento` (
  `id_tipo_mantenimiento` varchar(50) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_mantenimiento`
--

INSERT INTO `tipo_mantenimiento` (`id_tipo_mantenimiento`, `descripcion`) VALUES
('1', 'Preventivo'),
('2', 'Correctivo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_vehiculo`
--

CREATE TABLE `tipo_vehiculo` (
  `id_tipo_vehiculo` int(11) NOT NULL,
  `vehiculo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_vehiculo`
--

INSERT INTO `tipo_vehiculo` (`id_tipo_vehiculo`, `vehiculo`) VALUES
(1, 'Automovil'),
(2, 'Motocicleta'),
(3, 'Camioneta'),
(4, 'Camión'),
(5, 'Bus'),
(6, 'Pickup'),
(7, 'SUV'),
(8, 'Crossover'),
(9, 'Van'),
(10, 'Tractomula');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `documento` varchar(20) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(500) NOT NULL,
  `telefono` bigint(20) NOT NULL,
  `id_estado_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expira` datetime DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `foto_perfil` varchar(255) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `nit_empresa` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`documento`, `nombre_completo`, `email`, `password`, `telefono`, `id_estado_usuario`, `id_rol`, `reset_token`, `reset_expira`, `joined_at`, `foto_perfil`, `fecha_nacimiento`, `nit_empresa`) VALUES
('1109490190', 'Francy Milady Sanchez', 'Milady2408@gmail.com', '$2y$10$WG9gASrzCZ0..gPjESBT2.KilbH0I873vBpKa3g8UsNPrN6LsbG6y', 3102331487, 1, 2, NULL, NULL, '2025-07-09 22:05:56', NULL, NULL, NULL),
('1109491416', 'Edwar Gomez', 'edwarfaridg@gmail.com', '$2y$12$cbNWVV1tq7/Ua8LvmmH49.9Qkg2vpgzR4YSP6gyLjb1unrobJ6oe6', 3102331488, 1, 3, NULL, NULL, '2025-07-12 05:03:29', '../usuario/css/img/perfil.jpg', NULL, NULL),
('987654321', 'admin', 'admin@gmail.com', '$2y$12$MlKgR3yaE/DWjGBn/AswkejpcchJvRbF3xJEPNRK68zQrJ41CU/O6', 3209790912, 1, 1, NULL, NULL, '2025-07-12 04:09:52', '../usuario/css/img/perfil.jpg', NULL, NULL);

--
-- Disparadores `usuarios`
--
DELIMITER $$
CREATE TRIGGER `after_insert_usuario` AFTER INSERT ON `usuarios` FOR EACH ROW BEGIN
    INSERT INTO log_registros(documento_usuario, email_usuario, descripcion)
    VALUES (NEW.documento, NEW.email, CONCAT('Nuevo Usuario registrado'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `placa` varchar(10) NOT NULL,
  `tipo_vehiculo` int(11) NOT NULL,
  `año` int(50) NOT NULL,
  `Documento` varchar(20) NOT NULL,
  `id_marca` varchar(50) NOT NULL,
  `modelo` varchar(50) NOT NULL,
  `id_color` int(10) NOT NULL,
  `kilometraje_actual` bigint(20) NOT NULL,
  `id_estado` varchar(50) NOT NULL,
  `fecha_registro` date NOT NULL,
  `foto_vehiculo` varchar(255) DEFAULT NULL COMMENT 'Ruta de la imagen del vehículo',
  `registrado_por` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`placa`, `tipo_vehiculo`, `año`, `Documento`, `id_marca`, `modelo`, `id_color`, `kilometraje_actual`, `id_estado`, `fecha_registro`, `foto_vehiculo`, `registrado_por`) VALUES
('EDW202', 1, 2020, '1109490190', '1', 'aveo', 5, 756453, '1', '2025-07-11', 'uploads/vehiculos/EDW202_1752263231.jpg', '987654321'),
('MAS203', 5, 2000, '1109490190', '20', '122', 6, 544444, '7', '2025-07-11', 'uploads/vehiculos/MAS203_1752289685.jpg', '987654321');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vigencia_categoria_servicio`
--

CREATE TABLE `vigencia_categoria_servicio` (
  `id_vigencia` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `edad_minima` int(11) NOT NULL,
  `vigencia_años` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vigencia_categoria_servicio`
--

INSERT INTO `vigencia_categoria_servicio` (`id_vigencia`, `id_categoria`, `id_servicio`, `edad_minima`, `vigencia_años`) VALUES
(1, 1, 1, 16, 10),
(2, 2, 1, 16, 10),
(3, 3, 1, 18, 10),
(4, 4, 1, 20, 5),
(5, 5, 1, 21, 3),
(6, 6, 2, 18, 10),
(7, 7, 2, 20, 5),
(8, 8, 2, 21, 3);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `aseguradoras_soat`
--
ALTER TABLE `aseguradoras_soat`
  ADD PRIMARY KEY (`id_asegura`);

--
-- Indices de la tabla `categoria_licencia`
--
ALTER TABLE `categoria_licencia`
  ADD PRIMARY KEY (`id_categoria`),
  ADD KEY `id_servicio` (`id_servicio`);

--
-- Indices de la tabla `centro_rtm`
--
ALTER TABLE `centro_rtm`
  ADD PRIMARY KEY (`id_centro`);

--
-- Indices de la tabla `clasificacion_trabajo`
--
ALTER TABLE `clasificacion_trabajo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `colores`
--
ALTER TABLE `colores`
  ADD PRIMARY KEY (`id_color`);

--
-- Indices de la tabla `contacto`
--
ALTER TABLE `contacto`
  ADD PRIMARY KEY (`id_mensa`);

--
-- Indices de la tabla `correos_enviados_licencia`
--
ALTER TABLE `correos_enviados_licencia`
  ADD PRIMARY KEY (`id_correos`),
  ADD KEY `id_licencia` (`id_licencia`);

--
-- Indices de la tabla `correos_enviados_llantas`
--
ALTER TABLE `correos_enviados_llantas`
  ADD PRIMARY KEY (`id_correo`),
  ADD KEY `id_llantas` (`id_llantas`);

--
-- Indices de la tabla `correos_enviados_mantenimiento`
--
ALTER TABLE `correos_enviados_mantenimiento`
  ADD PRIMARY KEY (`id_correo`),
  ADD KEY `id_mantenimiento` (`id_mantenimiento`);

--
-- Indices de la tabla `correos_enviados_pico_placa`
--
ALTER TABLE `correos_enviados_pico_placa`
  ADD PRIMARY KEY (`id_correos`),
  ADD KEY `placa` (`placa`);

--
-- Indices de la tabla `correos_enviados_soat`
--
ALTER TABLE `correos_enviados_soat`
  ADD PRIMARY KEY (`id_correos`),
  ADD KEY `id_soat` (`id_soat`);

--
-- Indices de la tabla `correos_enviados_tecno`
--
ALTER TABLE `correos_enviados_tecno`
  ADD PRIMARY KEY (`id_correo`),
  ADD KEY `id_rtm` (`id_rtm`) USING BTREE;

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id_empresa`),
  ADD UNIQUE KEY `nit` (`nit`);

--
-- Indices de la tabla `estado_soat`
--
ALTER TABLE `estado_soat`
  ADD PRIMARY KEY (`id_stado`);

--
-- Indices de la tabla `estado_usuario`
--
ALTER TABLE `estado_usuario`
  ADD PRIMARY KEY (`id_estado`);

--
-- Indices de la tabla `estado_vehiculo`
--
ALTER TABLE `estado_vehiculo`
  ADD PRIMARY KEY (`id_estado`);

--
-- Indices de la tabla `licencias`
--
ALTER TABLE `licencias`
  ADD PRIMARY KEY (`id_licencia`),
  ADD KEY `id_documento` (`id_documento`),
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `id_servicio` (`id_servicio`);

--
-- Indices de la tabla `licencia_log`
--
ALTER TABLE `licencia_log`
  ADD PRIMARY KEY (`id_log`);

--
-- Indices de la tabla `llantas`
--
ALTER TABLE `llantas`
  ADD PRIMARY KEY (`id_llanta`),
  ADD KEY `placa` (`placa`);

--
-- Indices de la tabla `llantas_log`
--
ALTER TABLE `llantas_log`
  ADD PRIMARY KEY (`id_log`);

--
-- Indices de la tabla `logs_sistema`
--
ALTER TABLE `logs_sistema`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `log_accesos_superadmin`
--
ALTER TABLE `log_accesos_superadmin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento` (`documento`),
  ADD KEY `fecha_acceso` (`fecha_acceso`);

--
-- Indices de la tabla `log_registros`
--
ALTER TABLE `log_registros`
  ADD PRIMARY KEY (`id_log`);

--
-- Indices de la tabla `mantenimiento`
--
ALTER TABLE `mantenimiento`
  ADD PRIMARY KEY (`id_mantenimiento`),
  ADD KEY `placa` (`placa`),
  ADD KEY `id_tipo_mantenimiento` (`id_tipo_mantenimiento`);

--
-- Indices de la tabla `mantenimiento_log`
--
ALTER TABLE `mantenimiento_log`
  ADD PRIMARY KEY (`id_log`);

--
-- Indices de la tabla `marca`
--
ALTER TABLE `marca`
  ADD PRIMARY KEY (`id_marca`),
  ADD KEY `fk_marca_tipo` (`id_tipo_vehiculo`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento_usuario` (`documento_usuario`);

--
-- Indices de la tabla `pico_placa`
--
ALTER TABLE `pico_placa`
  ADD PRIMARY KEY (`id_pico_placa`);

--
-- Indices de la tabla `registro_vehiculos_log`
--
ALTER TABLE `registro_vehiculos_log`
  ADD PRIMARY KEY (`id_log`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `servicios_licencias`
--
ALTER TABLE `servicios_licencias`
  ADD PRIMARY KEY (`id_servicio`);

--
-- Indices de la tabla `sistema_licencias`
--
ALTER TABLE `sistema_licencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave_licencia` (`clave_licencia`);

--
-- Indices de la tabla `soat`
--
ALTER TABLE `soat`
  ADD PRIMARY KEY (`id_soat`),
  ADD KEY `id_placa` (`id_placa`),
  ADD KEY `id_aseguradora` (`id_aseguradora`),
  ADD KEY `id-estado` (`id_estado`);

--
-- Indices de la tabla `soat_log`
--
ALTER TABLE `soat_log`
  ADD PRIMARY KEY (`id_log`);

--
-- Indices de la tabla `tecnomecanica`
--
ALTER TABLE `tecnomecanica`
  ADD PRIMARY KEY (`id_rtm`),
  ADD KEY `id_centro_revision` (`id_centro_revision`),
  ADD KEY `id_estado` (`id_estado`),
  ADD KEY `id_placa` (`id_placa`);

--
-- Indices de la tabla `tecnomecanica_log`
--
ALTER TABLE `tecnomecanica_log`
  ADD PRIMARY KEY (`id_log`);

--
-- Indices de la tabla `tipo_documentacion`
--
ALTER TABLE `tipo_documentacion`
  ADD PRIMARY KEY (`id_tipo_documento`);

--
-- Indices de la tabla `tipo_gasto`
--
ALTER TABLE `tipo_gasto`
  ADD PRIMARY KEY (`id_tipo_gasto`);

--
-- Indices de la tabla `tipo_mantenimiento`
--
ALTER TABLE `tipo_mantenimiento`
  ADD PRIMARY KEY (`id_tipo_mantenimiento`);

--
-- Indices de la tabla `tipo_vehiculo`
--
ALTER TABLE `tipo_vehiculo`
  ADD PRIMARY KEY (`id_tipo_vehiculo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`documento`),
  ADD KEY `id_estado_usuario` (`id_estado_usuario`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `fk_usuarios_empresa` (`nit_empresa`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`placa`),
  ADD KEY `id_marca` (`id_marca`),
  ADD KEY `id_estado` (`id_estado`),
  ADD KEY `Documento` (`Documento`),
  ADD KEY `tipo_vehiculo` (`tipo_vehiculo`),
  ADD KEY `id_color` (`id_color`);

--
-- Indices de la tabla `vigencia_categoria_servicio`
--
ALTER TABLE `vigencia_categoria_servicio`
  ADD PRIMARY KEY (`id_vigencia`),
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `id_servicio` (`id_servicio`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `aseguradoras_soat`
--
ALTER TABLE `aseguradoras_soat`
  MODIFY `id_asegura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `categoria_licencia`
--
ALTER TABLE `categoria_licencia`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `centro_rtm`
--
ALTER TABLE `centro_rtm`
  MODIFY `id_centro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `clasificacion_trabajo`
--
ALTER TABLE `clasificacion_trabajo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `colores`
--
ALTER TABLE `colores`
  MODIFY `id_color` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `contacto`
--
ALTER TABLE `contacto`
  MODIFY `id_mensa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `correos_enviados_licencia`
--
ALTER TABLE `correos_enviados_licencia`
  MODIFY `id_correos` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `correos_enviados_llantas`
--
ALTER TABLE `correos_enviados_llantas`
  MODIFY `id_correo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `correos_enviados_mantenimiento`
--
ALTER TABLE `correos_enviados_mantenimiento`
  MODIFY `id_correo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `correos_enviados_pico_placa`
--
ALTER TABLE `correos_enviados_pico_placa`
  MODIFY `id_correos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `correos_enviados_soat`
--
ALTER TABLE `correos_enviados_soat`
  MODIFY `id_correos` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `correos_enviados_tecno`
--
ALTER TABLE `correos_enviados_tecno`
  MODIFY `id_correo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `estado_soat`
--
ALTER TABLE `estado_soat`
  MODIFY `id_stado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `estado_usuario`
--
ALTER TABLE `estado_usuario`
  MODIFY `id_estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `licencias`
--
ALTER TABLE `licencias`
  MODIFY `id_licencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `licencia_log`
--
ALTER TABLE `licencia_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `llantas`
--
ALTER TABLE `llantas`
  MODIFY `id_llanta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `llantas_log`
--
ALTER TABLE `llantas_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `logs_sistema`
--
ALTER TABLE `logs_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1109491417;

--
-- AUTO_INCREMENT de la tabla `log_accesos_superadmin`
--
ALTER TABLE `log_accesos_superadmin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `log_registros`
--
ALTER TABLE `log_registros`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `mantenimiento`
--
ALTER TABLE `mantenimiento`
  MODIFY `id_mantenimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `mantenimiento_log`
--
ALTER TABLE `mantenimiento_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `marca`
--
ALTER TABLE `marca`
  MODIFY `id_marca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `pico_placa`
--
ALTER TABLE `pico_placa`
  MODIFY `id_pico_placa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `registro_vehiculos_log`
--
ALTER TABLE `registro_vehiculos_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `servicios_licencias`
--
ALTER TABLE `servicios_licencias`
  MODIFY `id_servicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `sistema_licencias`
--
ALTER TABLE `sistema_licencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `soat`
--
ALTER TABLE `soat`
  MODIFY `id_soat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `soat_log`
--
ALTER TABLE `soat_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tecnomecanica`
--
ALTER TABLE `tecnomecanica`
  MODIFY `id_rtm` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tecnomecanica_log`
--
ALTER TABLE `tecnomecanica_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `vigencia_categoria_servicio`
--
ALTER TABLE `vigencia_categoria_servicio`
  MODIFY `id_vigencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `categoria_licencia`
--
ALTER TABLE `categoria_licencia`
  ADD CONSTRAINT `categoria_licencia_ibfk_1` FOREIGN KEY (`id_servicio`) REFERENCES `servicios_licencias` (`id_servicio`);

--
-- Filtros para la tabla `correos_enviados_licencia`
--
ALTER TABLE `correos_enviados_licencia`
  ADD CONSTRAINT `correos_enviados_licencia_ibfk_1` FOREIGN KEY (`id_licencia`) REFERENCES `licencias` (`id_licencia`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `correos_enviados_llantas`
--
ALTER TABLE `correos_enviados_llantas`
  ADD CONSTRAINT `correos_enviados_llantas_ibfk_1` FOREIGN KEY (`id_llantas`) REFERENCES `llantas` (`id_llanta`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `correos_enviados_mantenimiento`
--
ALTER TABLE `correos_enviados_mantenimiento`
  ADD CONSTRAINT `correos_enviados_mantenimiento_ibfk_1` FOREIGN KEY (`id_mantenimiento`) REFERENCES `mantenimiento` (`id_mantenimiento`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `correos_enviados_pico_placa`
--
ALTER TABLE `correos_enviados_pico_placa`
  ADD CONSTRAINT `correos_enviados_pico_placa_ibfk_1` FOREIGN KEY (`placa`) REFERENCES `vehiculos` (`placa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `correos_enviados_soat`
--
ALTER TABLE `correos_enviados_soat`
  ADD CONSTRAINT `correos_enviados_soat_ibfk_1` FOREIGN KEY (`id_soat`) REFERENCES `soat` (`id_soat`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `correos_enviados_tecno`
--
ALTER TABLE `correos_enviados_tecno`
  ADD CONSTRAINT `correos_enviados_tecno_ibfk_1` FOREIGN KEY (`id_rtm`) REFERENCES `tecnomecanica` (`id_rtm`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `licencias`
--
ALTER TABLE `licencias`
  ADD CONSTRAINT `licencias_ibfk_1` FOREIGN KEY (`id_documento`) REFERENCES `usuarios` (`documento`),
  ADD CONSTRAINT `licencias_ibfk_2` FOREIGN KEY (`id_categoria`) REFERENCES `categoria_licencia` (`id_categoria`),
  ADD CONSTRAINT `licencias_ibfk_3` FOREIGN KEY (`id_servicio`) REFERENCES `servicios_licencias` (`id_servicio`);

--
-- Filtros para la tabla `llantas`
--
ALTER TABLE `llantas`
  ADD CONSTRAINT `llantas_ibfk_1` FOREIGN KEY (`placa`) REFERENCES `vehiculos` (`placa`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mantenimiento`
--
ALTER TABLE `mantenimiento`
  ADD CONSTRAINT `mantenimiento_ibfk_1` FOREIGN KEY (`placa`) REFERENCES `vehiculos` (`placa`),
  ADD CONSTRAINT `mantenimiento_ibfk_2` FOREIGN KEY (`id_tipo_mantenimiento`) REFERENCES `tipo_mantenimiento` (`id_tipo_mantenimiento`);

--
-- Filtros para la tabla `marca`
--
ALTER TABLE `marca`
  ADD CONSTRAINT `fk_marca_tipo` FOREIGN KEY (`id_tipo_vehiculo`) REFERENCES `tipo_vehiculo` (`id_tipo_vehiculo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`documento_usuario`) REFERENCES `usuarios` (`documento`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `soat`
--
ALTER TABLE `soat`
  ADD CONSTRAINT `soat_ibfk_1` FOREIGN KEY (`id_placa`) REFERENCES `vehiculos` (`placa`),
  ADD CONSTRAINT `soat_ibfk_2` FOREIGN KEY (`id_aseguradora`) REFERENCES `aseguradoras_soat` (`id_asegura`),
  ADD CONSTRAINT `soat_ibfk_3` FOREIGN KEY (`id_estado`) REFERENCES `estado_soat` (`id_stado`);

--
-- Filtros para la tabla `tecnomecanica`
--
ALTER TABLE `tecnomecanica`
  ADD CONSTRAINT `tecnomecanica_ibfk_1` FOREIGN KEY (`id_centro_revision`) REFERENCES `centro_rtm` (`id_centro`),
  ADD CONSTRAINT `tecnomecanica_ibfk_2` FOREIGN KEY (`id_estado`) REFERENCES `estado_soat` (`id_stado`),
  ADD CONSTRAINT `tecnomecanica_ibfk_3` FOREIGN KEY (`id_placa`) REFERENCES `vehiculos` (`placa`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_empresa` FOREIGN KEY (`nit_empresa`) REFERENCES `empresas` (`nit`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_estado_usuario`) REFERENCES `estado_usuario` (`id_estado`),
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

--
-- Filtros para la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD CONSTRAINT `fk_vehiculos_documento` FOREIGN KEY (`Documento`) REFERENCES `usuarios` (`documento`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `vehiculos_ibfk_3` FOREIGN KEY (`id_estado`) REFERENCES `estado_vehiculo` (`id_estado`),
  ADD CONSTRAINT `vehiculos_ibfk_4` FOREIGN KEY (`tipo_vehiculo`) REFERENCES `tipo_vehiculo` (`id_tipo_vehiculo`),
  ADD CONSTRAINT `vehiculos_ibfk_5` FOREIGN KEY (`id_color`) REFERENCES `colores` (`id_color`);

--
-- Filtros para la tabla `vigencia_categoria_servicio`
--
ALTER TABLE `vigencia_categoria_servicio`
  ADD CONSTRAINT `vigencia_categoria_servicio_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categoria_licencia` (`id_categoria`),
  ADD CONSTRAINT `vigencia_categoria_servicio_ibfk_2` FOREIGN KEY (`id_servicio`) REFERENCES `servicios_licencias` (`id_servicio`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
