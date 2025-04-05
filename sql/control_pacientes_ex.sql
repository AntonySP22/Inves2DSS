-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 05-04-2025 a las 19:54:09
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.1.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `control_pacientes`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

DROP TABLE IF EXISTS `citas`;
CREATE TABLE IF NOT EXISTS `citas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int DEFAULT NULL,
  `medico_id` int DEFAULT NULL,
  `fecha_hora` datetime DEFAULT NULL,
  `estado` enum('pendiente','confirmada','cancelada','completada') DEFAULT 'pendiente',
  `motivo` text,
  `notas_medico` text,
  PRIMARY KEY (`id`),
  KEY `paciente_id` (`paciente_id`),
  KEY `medico_id` (`medico_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `enfermedades`
--

DROP TABLE IF EXISTS `enfermedades`;
CREATE TABLE IF NOT EXISTS `enfermedades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `enfermedades`
--

INSERT INTO `enfermedades` (`id`, `nombre`, `descripcion`) VALUES
(10, 'Asma', 'Inflamación crónica de las vías respiratorias.'),
(9, 'Hipertensión Arterial', 'Presión arterial sistólica ≥140 mmHg o diastólica ≥90 mmHg.'),
(8, 'Diabetes Tipo 2', 'Resistencia a la insulina con producción deficiente.'),
(7, 'Diabetes Tipo 1', 'Deficiencia de insulina por destrucción de células beta del páncreas.'),
(11, 'Artritis Reumatoide', 'Enfermedad autoinmune que afecta articulaciones.'),
(12, 'EPOC', 'Enfermedad pulmonar obstructiva crónica.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_acceso`
--

DROP TABLE IF EXISTS `logs_acceso`;
CREATE TABLE IF NOT EXISTS `logs_acceso` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `accion` varchar(255) DEFAULT NULL,
  `fecha_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicamentos`
--

DROP TABLE IF EXISTS `medicamentos`;
CREATE TABLE IF NOT EXISTS `medicamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tratamiento_id` int DEFAULT NULL,
  `nombre_medicamento` varchar(100) DEFAULT NULL,
  `dosis` varchar(50) DEFAULT NULL,
  `frecuencia` varchar(100) DEFAULT NULL,
  `via_administracion` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tratamiento_id` (`tratamiento_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `medicamentos`
--

INSERT INTO `medicamentos` (`id`, `tratamiento_id`, `nombre_medicamento`, `dosis`, `frecuencia`, `via_administracion`) VALUES
(10, 2, 'Salbutamol', '100 mcg', 'Cada 6 horas (si es necesario)', 'Inhalación'),
(9, 1, 'Losartán', '50 mg', 'Cada 24 horas', 'Oral'),
(8, 1, 'Insulina Glargina', '20 unidades', 'Una vez al día', 'Subcutánea'),
(7, 1, 'Metformina', '850 mg', 'Cada 8 horas', 'Oral'),
(11, 2, 'Budesónida', '200 mcg', 'Dos veces al día', 'Inhalación'),
(12, 3, 'Ibuprofeno', '400 mg', 'Cada 8 horas (con comida)', 'Oral');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE IF NOT EXISTS `notificaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `tipo` enum('cita','medicamento','general') DEFAULT NULL,
  `mensaje` text,
  `fecha_envio` datetime DEFAULT NULL,
  `estado` enum('pendiente','enviado','leido') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paciente_enfermedades`
--

DROP TABLE IF EXISTS `paciente_enfermedades`;
CREATE TABLE IF NOT EXISTS `paciente_enfermedades` (
  `paciente_id` int NOT NULL,
  `enfermedad_id` int NOT NULL,
  `fecha_diagnostico` date DEFAULT NULL,
  PRIMARY KEY (`paciente_id`,`enfermedad_id`),
  KEY `enfermedad_id` (`enfermedad_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfiles_medicos`
--

DROP TABLE IF EXISTS `perfiles_medicos`;
CREATE TABLE IF NOT EXISTS `perfiles_medicos` (
  `usuario_id` int NOT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `licencia_medica` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`usuario_id`),
  UNIQUE KEY `licencia_medica` (`licencia_medica`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registros_salud`
--

DROP TABLE IF EXISTS `registros_salud`;
CREATE TABLE IF NOT EXISTS `registros_salud` (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int DEFAULT NULL,
  `tipo_registro` enum('presion_arterial','glucosa','peso','otros') DEFAULT NULL,
  `valor` varchar(50) DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `notas` text,
  PRIMARY KEY (`id`),
  KEY `paciente_id` (`paciente_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tratamientos`
--

DROP TABLE IF EXISTS `tratamientos`;
CREATE TABLE IF NOT EXISTS `tratamientos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int DEFAULT NULL,
  `medico_id` int DEFAULT NULL,
  `nombre_tratamiento` varchar(255) DEFAULT NULL,
  `descripcion` text,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('activo','completado','suspendido') DEFAULT 'activo',
  PRIMARY KEY (`id`),
  KEY `paciente_id` (`paciente_id`),
  KEY `medico_id` (`medico_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `edad` int DEFAULT NULL,
  `sexo` enum('Masculino','Femenino','Otro') DEFAULT 'Otro',
  `rol` enum('paciente','medico','admin') DEFAULT 'paciente',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES
(1, 'Elmer Antonio Cruz García', 'elmer06.cruz@gmail.com', '$2y$12$DYysV6.Ye.PjWa4JzN3dkOVJU5MwbtCdWk9dDxGVqrWiysamrz0gq', 18, 'Masculino', 'admin', '2025-04-04 22:32:37'),
(2, 'Elmer Antonio Cruz García', 'elmer06@gmail.cok', '$2y$12$clfSV8coNXaa33EXEVXWeOwGk4qus/Z/evHaS.QULlcNCxNOXcppK', 18, 'Masculino', 'medico', '2025-04-04 22:49:30'),
(3, 'Pancho', 'pancho@gmail.com', '$2y$12$dEQanQP3DVN6plJTUkD0CeH86073hI/iQ8ImVzFfFKyycpTF3j2xi', 18, 'Masculino', 'medico', '2025-04-04 23:11:28'),
(4, 'Pedro', 'pedro@gmai.com', '$2y$12$.QAs/qSCJzOUyCGulnW7qeLyl671ZAzIxjUp7AiL5beikBetZcbpy', 18, 'Masculino', 'paciente', '2025-04-04 23:24:20'),
(5, 'juan', 'juan@gmail.com', '$2y$12$ult/r/B0tq4.AHLhWuLRme/Z1RKDlWPY2lMgwUguCHdAmizqWxYiu', 18, 'Masculino', 'paciente', '2025-04-04 23:31:01');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
