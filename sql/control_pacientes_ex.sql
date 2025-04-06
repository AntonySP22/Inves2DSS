-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 05-04-2025 a las 20:50:18
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
CREATE DATABASE IF NOT EXISTS `control_pacientes` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `control_pacientes`;

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
  `estado` varchar(20) DEFAULT 'pendiente',
  `motivo` text,
  `notas_medico` text,
  PRIMARY KEY (`id`),
  KEY `paciente_id` (`paciente_id`),
  KEY `medico_id` (`medico_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id`, `paciente_id`, `medico_id`, `fecha_hora`, `estado`, `motivo`, `notas_medico`) VALUES
(1, 1, 5, '2025-04-10 10:00:00', 'pendiente', 'Chequeo general', 'Paciente estable.'),
(2, 2, 6, '2025-04-11 14:30:00', 'pendiente', 'Dolor abdominal', 'Se requiere examen de orina.'),
(3, 3, 7, '2025-04-12 09:00:00', 'pendiente', 'Revisión presión', 'Indicado monitoreo diario.'),
(4, 4, 8, '2025-04-13 11:15:00', 'pendiente', 'Irritación cutánea', 'Posible dermatitis.');

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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `enfermedades`
--

INSERT INTO `enfermedades` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Diabetes', 'Enfermedad metabólica que afecta el manejo de la glucosa.'),
(2, 'Hipertensión', 'Condición caracterizada por presión arterial alta.'),
(3, 'Asma', 'Enfermedad crónica que afecta las vías respiratorias.'),
(4, 'Artritis', 'Inflamación de las articulaciones que causa dolor y rigidez.');

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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `logs_acceso`
--

INSERT INTO `logs_acceso` (`id`, `usuario_id`, `accion`, `fecha_hora`, `ip_address`) VALUES
(1, 1, 'Inicio de sesión', '2025-04-05 20:46:27', '192.168.1.10'),
(2, 5, 'Actualización de perfil', '2025-04-05 20:46:27', '192.168.1.11'),
(3, 2, 'Cambio de contraseña', '2025-04-05 20:46:27', '192.168.1.12'),
(4, 6, 'Cierre de sesión', '2025-04-05 20:46:27', '192.168.1.13');

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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `medicamentos`
--

INSERT INTO `medicamentos` (`id`, `tratamiento_id`, `nombre_medicamento`, `dosis`, `frecuencia`, `via_administracion`) VALUES
(1, 1, 'Metformina', '500mg', 'Dos veces al día', 'Oral'),
(2, 1, 'Insulina', '10 unidades', 'Diario', 'Inyección'),
(3, 2, 'Lisinopril', '20mg', 'Una vez al día', 'Oral'),
(4, 2, 'Amlodipino', '5mg', 'Una vez al día', 'Oral');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE IF NOT EXISTS `notificaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `tipo` varchar(20) DEFAULT NULL,
  `mensaje` text,
  `fecha_envio` datetime DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `usuario_id`, `tipo`, `mensaje`, `fecha_envio`, `estado`) VALUES
(1, 1, 'recordatorio', 'Su cita es mañana a las 10:00 AM', '2025-04-04 09:00:00', 'enviado'),
(2, 5, 'alerta', 'Actualice sus datos de perfil', '2025-04-04 10:00:00', 'pendiente'),
(3, 2, 'recordatorio', 'Su tratamiento inicia la próxima semana', '2025-04-04 11:00:00', 'enviado'),
(4, 6, 'alerta', 'Revise resultados de laboratorio', '2025-04-04 12:00:00', 'pendiente');

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

--
-- Volcado de datos para la tabla `paciente_enfermedades`
--

INSERT INTO `paciente_enfermedades` (`paciente_id`, `enfermedad_id`, `fecha_diagnostico`) VALUES
(1, 1, '2020-05-10'),
(2, 2, '2019-03-15'),
(3, 3, '2021-07-20'),
(4, 4, '2022-01-05');

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

--
-- Volcado de datos para la tabla `perfiles_medicos`
--

INSERT INTO `perfiles_medicos` (`usuario_id`, `especialidad`, `licencia_medica`) VALUES
(5, 'Medicina General', 'LIC-1001'),
(6, 'Pediatría', 'LIC-1002'),
(7, 'Cardiología', 'LIC-1003'),
(8, 'Dermatología', 'LIC-1004');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registros_salud`
--

DROP TABLE IF EXISTS `registros_salud`;
CREATE TABLE IF NOT EXISTS `registros_salud` (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int DEFAULT NULL,
  `tipo_registro` varchar(50) DEFAULT NULL,
  `valor` varchar(50) DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `notas` text,
  PRIMARY KEY (`id`),
  KEY `paciente_id` (`paciente_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `registros_salud`
--

INSERT INTO `registros_salud` (`id`, `paciente_id`, `tipo_registro`, `valor`, `fecha_registro`, `notas`) VALUES
(1, 1, 'presión', '120/80', '2025-04-05 20:46:27', 'Normal'),
(2, 2, 'glucosa', '90 mg/dL', '2025-04-05 20:46:27', 'En rango'),
(3, 3, 'colesterol', '180 mg/dL', '2025-04-05 20:46:27', 'Leve elevación'),
(4, 4, 'peso', '70 kg', '2025-04-05 20:46:27', 'Dentro del rango ideal');

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
  `estado` varchar(20) DEFAULT 'activo',
  PRIMARY KEY (`id`),
  KEY `paciente_id` (`paciente_id`),
  KEY `medico_id` (`medico_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `tratamientos`
--

INSERT INTO `tratamientos` (`id`, `paciente_id`, `medico_id`, `nombre_tratamiento`, `descripcion`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
(1, 1, 5, 'Control Diabetes', 'Administración de insulina y dieta especial.', '2025-04-01', '2025-06-01', 'activo'),
(2, 2, 6, 'Reducción presión arterial', 'Uso de medicamentos antihipertensivos.', '2025-04-02', '2025-07-02', 'activo'),
(3, 3, 7, 'Tratamiento para asma', 'Uso de inhaladores.', '2025-04-03', '2025-06-03', 'activo'),
(4, 4, 8, 'Alivio artritis', 'Ejercicios y analgésicos.', '2025-04-04', '2025-07-04', 'activo');

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
  `sexo` varchar(20) DEFAULT 'Otro',
  `rol` varchar(20) DEFAULT 'paciente',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES
(1, 'Ana Pérez', 'ana.perez@example.com', 'hash1', 30, 'Femenino', 'paciente', '2025-04-05 20:46:27'),
(2, 'Carlos Gómez', 'carlos.gomez@example.com', 'hash2', 45, 'Masculino', 'paciente', '2025-04-05 20:46:27'),
(3, 'Laura Rodríguez', 'laura.rodriguez@example.com', 'hash3', 28, 'Femenino', 'paciente', '2025-04-05 20:46:27'),
(4, 'Jorge Martínez', 'jorge.martinez@example.com', 'hash4', 35, 'Masculino', 'paciente', '2025-04-05 20:46:27'),
(5, 'Dr. Luis Hernández', 'luis.hernandez@example.com', 'hash5', 50, 'Masculino', 'medico', '2025-04-05 20:46:27'),
(6, 'Dra. Mariana López', 'mariana.lopez@example.com', 'hash6', 42, 'Femenino', 'medico', '2025-04-05 20:46:27'),
(7, 'Dr. Felipe Ramírez', 'felipe.ramirez@example.com', 'hash7', 39, 'Masculino', 'medico', '2025-04-05 20:46:27'),
(8, 'Dra. Sofía Castillo', 'sofia.castillo@example.com', 'hash8', 33, 'Femenino', 'medico', '2025-04-05 20:46:27'),
(9, 'Elmer Antonio Cruz García', 'elmer06.cruz@gmail.com', '$2y$12$tpnowwdb1coTpiVQ2JWc5e4Ba4Z/PFFKHsJlZy3ywh8pmtZvd.SZi', 18, 'Masculino', 'admin', '2025-04-05 20:47:36');
--
-- Base de datos: `desafiodwf`
--
CREATE DATABASE IF NOT EXISTS `desafiodwf` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `desafiodwf`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamento`
--

DROP TABLE IF EXISTS `departamento`;
CREATE TABLE IF NOT EXISTS `departamento` (
  `id` bigint NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `nombred` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `departamento`
--

INSERT INTO `departamento` (`id`, `descripcion`, `nombred`) VALUES
(52, 'Departamento encargado de la gestión del talento humano.', 'Recursos Humanos'),
(54, 'Departamento encargado de la contabilidad y presupuesto.', 'Finanzas'),
(102, 'Departamento que gestiona los sistemas .', 'Post');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamento_seq`
--

DROP TABLE IF EXISTS `departamento_seq`;
CREATE TABLE IF NOT EXISTS `departamento_seq` (
  `next_val` bigint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `departamento_seq`
--

INSERT INTO `departamento_seq` (`next_val`) VALUES
(201);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado`
--

DROP TABLE IF EXISTS `empleado`;
CREATE TABLE IF NOT EXISTS `empleado` (
  `id` bigint NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `cargo` varchar(255) DEFAULT NULL,
  `fecha_contratacion` date NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `salario` double DEFAULT NULL,
  `departamento_id` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKhdjjhohpyjsfta5g6p8b8e00i` (`departamento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `empleado`
--

INSERT INTO `empleado` (`id`, `apellido`, `cargo`, `fecha_contratacion`, `nombre`, `salario`, `departamento_id`) VALUES
(55, 'López', 'Gerente de Finanzas', '2023-09-15', 'María', 2500.75, 54);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado_seq`
--

DROP TABLE IF EXISTS `empleado_seq`;
CREATE TABLE IF NOT EXISTS `empleado_seq` (
  `next_val` bigint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `empleado_seq`
--

INSERT INTO `empleado_seq` (`next_val`) VALUES
(201);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD CONSTRAINT `FKhdjjhohpyjsfta5g6p8b8e00i` FOREIGN KEY (`departamento_id`) REFERENCES `departamento` (`id`);
--
-- Base de datos: `libros`
--
CREATE DATABASE IF NOT EXISTS `libros` DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci;
USE `libros`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE IF NOT EXISTS `clientes` (
  `idcliente` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(25) NOT NULL,
  `apellido` varchar(25) NOT NULL,
  `direccion` varchar(50) NOT NULL,
  `ciudad` varchar(25) NOT NULL,
  PRIMARY KEY (`idcliente`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`idcliente`, `nombre`, `apellido`, `direccion`, `ciudad`) VALUES
(2, 'Hugo Adiel', 'Guerrero Peraza', 'Colonia La Cima 2', 'San Salvador'),
(3, 'Alvaro Alcides', 'Guandique', 'Colonia Chaparrastique', 'San Miguel'),
(4, 'Edgard Oswaldo', 'Rodas Grande', 'Colonia La Gloria', 'San Salvador'),
(5, 'Mauricio Otmar', 'Vásquez', 'San Jacinto', 'San Salvador'),
(7, 'Rigoberto Antonio', 'Leiva González', 'Urbanización Las Ceibas', 'San Salvador'),
(8, 'Carlos Mauricio', 'Bolaños Guerrero', 'Col. Santa Marta', 'San Salvador'),
(9, 'Ana Silvia', 'Durán Hernández', 'Col. Ciudad Victoria', 'San Jacinto'),
(10, 'Evelyn Lissette', 'Hernández Rivera', 'Col. Bosques del Río', 'Soyapango'),
(11, 'Delmy Jeannette', 'Fuentes', 'Col. Los Santos', 'Soyapango'),
(12, 'Julio Adalberto', 'Rivera', 'Col. Santa Marta', 'San Salvador'),
(13, 'Ricardo Ernesto', 'Elías Guandique', 'Reparto Morazán', 'Soyapango'),
(14, 'Salvador Ernesto', 'Cabrera Rodríguez', 'Col. Las Colinas', 'Santa Tecla'),
(15, 'Yesenia Xiomara', 'Martínez Oviedo', 'Residencial Los Conacastes', 'Soyapango'),
(16, 'Edgardo Alberto', 'Romero Masis', 'Col. Vista al Lago Pje. 3', 'Ilopango'),
(17, 'Blanca Iris', 'Cañas Abarca', '21ª calle poniente', 'San Salvador'),
(18, 'Claudia Verónica', 'Portillo Abrego', 'Col. San José', 'Soyapango'),
(19, 'Maura Verónica', 'Arévalo Mulato', 'Col. Las Magnolias', 'San Salvador'),
(20, 'Silvia Marina', 'Cisneros Murcia', 'Col. Vista Hermosa', 'San Salvador'),
(21, 'José Alexander', 'Aguirre Peña', 'Colonia La Pirámide', 'Ciudad Delgado'),
(22, 'Gloria Amar', 'Montoya', 'Colonia Jardines de Guadalupe', 'Nueva San Salvador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detallepedido`
--

DROP TABLE IF EXISTS `detallepedido`;
CREATE TABLE IF NOT EXISTS `detallepedido` (
  `idorden` int UNSIGNED NOT NULL,
  `isbn` char(18) NOT NULL,
  `cantidad` tinyint UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`idorden`,`isbn`),
  KEY `isbn` (`isbn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `detallepedido`
--

INSERT INTO `detallepedido` (`idorden`, `isbn`, `cantidad`) VALUES
(5, '978-84-205-3392-6', 2),
(5, '978-84-415-1569-7', 2),
(6, '978-84-205-3392-6', 2),
(6, '978-84-415-2121-6', 5),
(8, '978-84-481-3268-2', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--

DROP TABLE IF EXISTS `libros`;
CREATE TABLE IF NOT EXISTS `libros` (
  `isbn` char(18) NOT NULL,
  `autor` char(60) DEFAULT NULL,
  `titulo` char(76) DEFAULT NULL,
  `precio` float(4,2) DEFAULT NULL,
  PRIMARY KEY (`isbn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `libros`
--

INSERT INTO `libros` (`isbn`, `autor`, `titulo`, `precio`) VALUES
('978-60-776-8615-6', 'Ralph G. Schulz', 'Diseño web con CSS', 24.80),
('978-84-111-9999-0', 'Fernando M. Guardado', 'Creación de páginas web con DHTML', 31.35),
('978-84-205-3392-6', 'Paul McFedries', 'JavaScript Edición especial', 64.35),
('978-84-205-3477-0', 'Joseph Mayo', 'C# Al descubierto', 45.20),
('978-84-205-3618-2', 'Paul DuBois', 'Programación con MySQL', 99.99),
('978-84-413-1357-5', 'Ricardo Elías', 'El gran manual de programación web con PHP', 38.40),
('978-84-415-1569-7', 'Luke Welling', 'Desarrollo web con PHP y MySQL', 61.50),
('978-84-415-1845-2', 'John Coggeshall', 'La Biblia de PHP 5', 93.05),
('978-84-415-2121-6', 'James Foxall', 'El libro de Visual C# 2005', 50.25),
('978-84-415-2137-7', 'Andy Budd', 'CSS Manual avanzado', 52.45),
('978-84-415-2200-8', 'Lee Babin', 'Introducción a AJAX con PHP', 36.70),
('978-84-415-2217-6', 'Jason Cranford Teague', 'Programación CSS, DHTML y AJAX', 72.50),
('978-84-415-2311-1', 'Ellie Quigley', 'PHP y MySQL Práctico para programadores y diseñadores web', 52.30),
('978-84-415-2388-3', 'Danny Goodman', 'JavaScript, HTML5 y CSS', 68.20),
('978-84-415-2389-0', 'Michele E. Davis', 'PHP y MySQL', 37.60),
('978-84-415-2507-8', 'Baron Schawartz', 'MySQL Avanzado', 94.32),
('978-84-415-2514-6', 'Phil Ballard', 'Ajax, JavaScript y PHP', 49.25),
('978-84-415-2578-8', 'Rod Stephens', 'Fundamentos de diseño de bases de datos', 48.90),
('978-84-415-2595-3', 'Abraham Gutiérrez', 'PHP 5 a través de ejemplos', 35.15),
('978-84-415-2618-1', 'Luis Miguel Cabezas Granado', 'PHP 6 Manual Imprescindible', 62.31),
('978-84-415-2689-1', 'Matt Doyle', 'Fundamentos PHP práctico', 65.00),
('978-84-415-2958-8', 'W. Frank Ableson', 'Android Guía para Desarrolladores', 74.95),
('978-84-415-2961-8', 'Jeff Friesen', 'Java para Desarrollo Android', 90.60),
('978-84-415-3188-8', 'Richard Rodger', 'Desarrollo de Aplicaciones en la Nube para Dispositivos Móviles', 76.84),
('978-84-415-3397-4', 'John Resig', 'JavaScript Ninja', 59.79),
('978-84-481-3172-2', 'Thomas Powell', 'HTML 4 Manual de referencia', 75.45),
('978-84-481-3173-9', 'Herbert Schildt', 'Java 2 Manual de referencia', 73.40),
('978-84-481-3268-2', 'Thomas Powell', 'JavaScript Manual de referencia', 72.75),
('978-84-481-3931-5', 'P. S. Woods', 'Programación de Macromedia Flash MX', 45.25),
('978-84-481-9814-5', 'F. Javier Gil Rubio', 'Creación de sitios web con PHP 5', 36.90),
('978-84-832-2372-7', 'Tom Negrino', 'JavaScript & AJAX para diseño web', 27.50),
('978-84-832-2414-4', 'José Rafael García', 'JAVA SE6 & Swing', 26.95),
('978-97-015-1328-6', 'Maximilian Firtman', 'AJAX Web 2.0 para desarrolladores', 41.25),
('978-97-017-0343-4', 'Menachen Bazian', 'Visual FoxPro 6', 31.40),
('978-98-716-0929-1', 'Christian Cibelli', 'PHP Programación Web Avanzada para Profesionales', 42.60),
('awae2', 'Juan Perez', 'No se', 99.99);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes`
--

DROP TABLE IF EXISTS `ordenes`;
CREATE TABLE IF NOT EXISTS `ordenes` (
  `idorden` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `idcliente` int UNSIGNED NOT NULL,
  `costo` float(6,2) DEFAULT NULL,
  `fecha` date NOT NULL,
  PRIMARY KEY (`idorden`),
  KEY `idcliente` (`idcliente`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `ordenes`
--

INSERT INTO `ordenes` (`idorden`, `idcliente`, `costo`, `fecha`) VALUES
(1, 9, 72.50, '2007-12-16'),
(2, 4, 61.50, '2007-11-14'),
(3, 8, 31.30, '2007-10-07'),
(4, 12, 35.85, '2008-01-04'),
(5, 7, 22.50, '2008-04-11'),
(6, 2, 61.30, '2007-10-18'),
(7, 16, 75.45, '2007-10-16'),
(8, 14, 72.75, '2008-01-20'),
(9, 2, 75.45, '2008-01-14'),
(10, 5, 72.50, '2008-01-09'),
(11, 9, 45.20, '2007-11-28'),
(12, 9, 61.50, '2008-01-25'),
(13, 3, 72.75, '2007-12-27'),
(14, 10, 73.40, '2008-01-22'),
(15, 17, 29.75, '2007-10-30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resumenlibro`
--

DROP TABLE IF EXISTS `resumenlibro`;
CREATE TABLE IF NOT EXISTS `resumenlibro` (
  `isbn` char(18) NOT NULL,
  `resumen` text,
  PRIMARY KEY (`isbn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detallepedido`
--
ALTER TABLE `detallepedido`
  ADD CONSTRAINT `detallepedido_ibfk_3` FOREIGN KEY (`idorden`) REFERENCES `ordenes` (`idorden`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detallepedido_ibfk_4` FOREIGN KEY (`isbn`) REFERENCES `libros` (`isbn`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD CONSTRAINT `ordenes_ibfk_1` FOREIGN KEY (`idcliente`) REFERENCES `clientes` (`idcliente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `resumenlibro`
--
ALTER TABLE `resumenlibro`
  ADD CONSTRAINT `resumenlibro_ibfk_1` FOREIGN KEY (`isbn`) REFERENCES `libros` (`isbn`) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Base de datos: `peliculas`
--
CREATE DATABASE IF NOT EXISTS `peliculas` DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci;
USE `peliculas`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `director`
--

DROP TABLE IF EXISTS `director`;
CREATE TABLE IF NOT EXISTS `director` (
  `iddirector` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `nacionalidad` varchar(30) NOT NULL,
  PRIMARY KEY (`iddirector`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `director`
--

INSERT INTO `director` (`iddirector`, `nombre`, `nacionalidad`) VALUES
(1, 'Chris Columbus', 'Estadounidense'),
(2, 'Lee Daniels', 'Estadounidense'),
(3, 'Terry Gilliam', 'Estadounidense'),
(4, 'Richard LaGravenese', 'Estadounidense'),
(5, 'Eric Bress', 'Estadounidense'),
(6, 'Barry Sonnenfeld', 'Estadounidense'),
(7, 'Anne Fletcher', 'Estadounidense'),
(8, 'Frank Darabont', 'Franc'),
(9, 'Peter Jackson', 'Neozeland'),
(10, 'George Lucas', 'Estadounidense'),
(11, 'Manoj Nelliyattu Shyamalan', 'Indú'),
(12, 'Gabriele Muccino', 'Italiano'),
(13, 'Frank Coraci', 'Estadounidense'),
(14, 'Zack Snyder', 'Estadounidense'),
(15, 'Joss Whedon', 'Estadounidense');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `genero`
--

DROP TABLE IF EXISTS `genero`;
CREATE TABLE IF NOT EXISTS `genero` (
  `idgenero` int NOT NULL AUTO_INCREMENT,
  `generopelicula` varchar(30) NOT NULL,
  PRIMARY KEY (`idgenero`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `genero`
--

INSERT INTO `genero` (`idgenero`, `generopelicula`) VALUES
(1, 'Acción'),
(2, 'Drama'),
(3, 'Aventura'),
(4, 'Comedia Romántica'),
(5, 'Suspenso'),
(6, 'Musical'),
(7, 'Familiar'),
(8, 'Infantil'),
(9, 'Ciencia ficción');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pelicula`
--

DROP TABLE IF EXISTS `pelicula`;
CREATE TABLE IF NOT EXISTS `pelicula` (
  `idpelicula` int NOT NULL,
  `titulopelicula` varchar(120) NOT NULL,
  `descripcion` text NOT NULL,
  `imgpelicula` varchar(200) NOT NULL,
  `tituloOriginal` varchar(60) NOT NULL,
  `idgenero` int NOT NULL,
  `iddirector` int NOT NULL,
  `duracion` int NOT NULL,
  PRIMARY KEY (`idpelicula`),
  KEY `idgenero` (`idgenero`),
  KEY `peliculas_ibfk_2` (`iddirector`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `pelicula`
--

INSERT INTO `pelicula` (`idpelicula`, `titulopelicula`, `descripcion`, `imgpelicula`, `tituloOriginal`, `idgenero`, `iddirector`, `duracion`) VALUES
(1, 'Percy Jackson y el Ladrón del Rayo', 'La historia narra la vida de un estudiante que descubre ser hijo de Poseidón, a raíz de esto se ve envuelto en una carrera contra el tiempo para impedir que los dioses griegos inicien una guerra que tiene como campo de batalla el continente americano de hoy en día.', 'img/percy-jackson-y-el-ladron-del-rayo.jpg', 'Percy Jackson & the Olympians: The lightning thief ', 1, 1, 119),
(2, 'Los Vengadores 2 - La era de Ultrón', 'El destino del planeta pende de un hilo cuando Tony Stark intenta hacer funcionar un inactivo programa para mantener la paz. Las cosas le salen mal y los héroes más poderosos, incluyendo Iron Man, Capitán América, la Viuda Negra, Thor, el Increíble Hulk y Ojo de Halcón, se ven enfrentados a la prueba definitiva.\r\nCuando el villano Ultrón aparece, es tarea de Los Vengadores el detenerle antes de que lleve a cabo sus terribles planes para el mundo. Inesperadas alianzas y acción por doquier sientan las bases para una épica aventura global.\r\n', 'img/los-vengadores-la-era-de-ultron.jpg', 'Avengers: Age of Ultron', 1, 15, 141),
(3, 'Batman v Superman: El origen de la justicia', 'Superman se ha convertido en la figura más controvertida del mundo. Mientras que muchos siguen creyendo que es un emblema de esperanza, otro gran número de personas lo consideran una amenaza para la humanidad. Para el influyente Bruce Wayne, Superman es claramente un peligro para la sociedad, su poder resulta imprudente y alejado de la mano del gobierno. Por eso, ante el temor de las acciones que pueda llevar a cabo un superhéroe con unos poderes casi divinos, decide ponerse la máscara y la capa para poner a raya al superhéroe de Metrópolis.\r\nMientras que la opinión pública debate sobre el interrogante de cuál es realmente el héroe que necesitan, el Hombre de Acero y Batman, enfrentados entre sí, se sumergen en una contienda el uno contra el otro. La rivalidad entre ellos está alimentada por el rencor y la venganza, y nada puede disuadirlos de librar esta guerra. Hostigados por el multimillonario Lex Luthor, Batman y Superman se ven las caras en una lucha sin precedentes.', 'img/batman-v-superman-el-origen-de-la-justicia.jpg', 'Batman v Superman: Dawn of Justice', 1, 14, 151),
(4, 'PD. Te Amo', 'La vida de Holly (Hilary Swank) se ve truncada cuando su marido, Gerry (Gerard Butler), muere. Incapaz de salir adelante por sí misma, su madre y sus amigos intentan animarla. Un día, después de su 30 cumpleaños, Holly recibe una carta de Gerry animándola a salir, a divertirse, a seguir adelante. Cada mes recibirá una carta firmada con un \"Posdata: Te amo\", que le devolverán las ganas de vivir.', 'img/post-data-te-amo.jpg', 'P.S. I love you', 4, 4, 115),
(5, 'Efecto mariposa', 'Evan Treborn, un joven que se está esforzando por superar unos dolorosos recuerdos de su infancia, descubre una técnica que le permite viajar atrás en el tiempo y ocupar su cuerpo de niño para poder cambiar el curso de su dolorosa historia. Sin embargo también descubre que cualquier mínimo cambio en el pasado altera enormemente su futuro.', 'img/efecto-mariposa.jpg', 'The Butterfly Effect', 5, 5, 100),
(6, 'Vacaciones en familia', 'Un ejecutivo preocupado por no perderse unas vacaciones con su familia decide llevarlos a vacacionar al mismo lugar donde tendrá una importante reunión de trabajo, pero sin decírselos', 'img/vacaciones-en-familia.jpg', 'RV', 7, 6, 98),
(7, 'La propuesta', 'Una poderosa editora llamada Margaret (Sandra Bullock) al enfrentarse ante la posibilidad de ser deportada a su país de origen, Canadá, decide comprometerse con su asistente Andrew (Ryan Reynolds) con el propósito de evitarlo', 'img/la-propuesta.jpg', 'The proposal', 4, 7, 108),
(8, 'Milagros inesperados', 'La película narra la vida de Paul Edgecomb (Tom Hanks), quien siendo un anciano de 108 años, cuenta su historia como oficial de la Milla Verde, una penitenciaría del estado de Luisiana, durante la década de 1930. Edgecomb cuenta que entre sus presos tuvo un personaje con poderes sobrenaturales, capaz de sanar a personas.', 'img/la-milla-verde.jpg', 'The Green Mile', 2, 8, 189),
(9, 'El Señor de los anillos: La comunidad del anillo', 'En la Tierra Media, el Señor Oscuro Sauron creó los Grandes Anillos de Poder, forjados por los herreros Elfos. Tres para los reyes Elfos, siete para los Señores Enanos, y nueve para los Hombres Mortales. Secretamente, Sauron también forjó un anillo maestro, el Anillo Único, que contiene en sí el poder para esclavizar a toda la Tierra Media. Con la ayuda de un grupo de amigos y de valientes aliados, Frodo emprende un peligroso viaje con la misión de destruir el Anillo Único. Pero el Señor Oscuro Sauron, quien creara el Anillo, envía a sus servidores para perseguir al grupo. Si Sauron lograra recuperar el Anillo, sería el final de la Tierra Media.', 'img/senor-de-los-anillos-comunidad-del-anillo.jpg', 'The Lord of The Rings: The fellowship of the ring', 3, 9, 178),
(10, 'La Guerra de las Galaxias: Episodio I - La amenaza fantasma', 'La historia se sitúa temporalmente 32 años antes de la batalla de Yavin. Narra los sucesos de la batalla de Naboo y se muestra cómo el senador Palpatine empieza su gran conspiración para llegar a ser Emperador de toda la galaxia. En ella podemos ver al, entonces, joven Anakin Skywalker de nueve años de edad libre de todo rastro del Lado Oscuro, que vive como esclavo con su madre en Tatooine. Allí conoce a un maestro Jedi llamado Qui-Gon Jinn, quien escapando con la reina de Naboo, nota en el pequeño Skywalker una poderosa fluctuación de la Fuerza.', 'img/episodio-i-la-amenaza-fantasma.jpg', 'Star Wars: Episode I - The Phantom Menace', 9, 10, 136),
(11, 'El sexto sentido', 'El psicólogo infantil Malcolm Crowe (Bruce Willis), vive con el horrible recuerdo de un paciente al cual trató erróneamente y a quien condujo a la desgracia intentando ayudarle. En su b&uacute;squeda de redención se fija en Cole (Haley Joel Osment), un niño de 9 años extraño e introvertido que necesita un tratamiento inminente. Decidido a compensar su pasado error, Malcolm tratará de acercarse a él y ayudarle, y poco a poco, irá ganándose su confianza. Será entonces cuando el pequeño Cole exprese por primera vez el escalofriante secreto que le atormenta: posee un don que le permite ver y escuchar a los espíritus atormentados que deambulan por el mundo, invisibles e intangibles. Estos fantasmas parecen querer algo de Cole, algún tipo de ayuda, algo que quieren que haga por ellos, y el niño vive el día a día horrorizado de ellos y de sí mismo. Según sus propias palabras, esos espíritus son gente muerta que cree estar viva a&uacute;n, envuelta en una ilusi&oacute;n, y su incomunicaci&oacute;n les causa un profundo sufrimiento. Malcolm, desconcertado por esa descripci&oacute;n, har&aacute; cuanto est&eacute; en su mano para hallar una cura.', 'img/sexto-sentido.jpg', 'The Sixth Sense', 5, 8, 107),
(12, 'En busca de la felicidad', 'Chris Gardner es un padre de familia que lucha por sobrevivir. A pesar de sus valientes intentos para mantener a la familia a flote, la madre de su hijo de cinco años Christopher comienza a derrumbarse a causa de la tensión constante de la presión económica; incapaz de soportarlo, en contra de sus sentimientos, decide marcharse. Chris comienza tenazmente a buscar un trabajo mejor pagado empleando todas las técticas comerciales que conoce. Consigue unas prácticas en una prestigiosa corredora de bolsa y, a pesar de no percibir ningún salario, acepta con la esperanza de finalizar el plan de estudios con un trabajo y un futuro prometedor. Sin colchón económico alguno, pronto echan a Chris y a su hijo del piso en el que viven y se ven obligados a vivir en centros de acogida, estaciones de autobús, cuartos de baño o allí donde encuentren refugio para pasar la noche.', 'img/en-busca-de-la-felicidad.jpg', 'Pursuit of happyness', 2, 12, 117),
(13, 'Click', 'Michael Newman (Adam Sandler) está casado con la bella Donna (Kate Beckinsale) y tienen dos hijos fantásticos, Ben (Joseph Castanon) y Samantha (Tatum McCann). Pero no puede verlos mucho porque dedica muchas duras y largas horas a su empresa arquitectónica con la débil esperanza de que su desagradecido jefe (David Hasselhoff) reconozca algún día su inestimable contribución y le convierta en socio.', 'img/click.jpg', 'Click', 7, 13, 107);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pelicula`
--
ALTER TABLE `pelicula`
  ADD CONSTRAINT `pelicula_ibfk_1` FOREIGN KEY (`idgenero`) REFERENCES `genero` (`idgenero`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pelicula_ibfk_2` FOREIGN KEY (`iddirector`) REFERENCES `director` (`iddirector`) ON UPDATE CASCADE;
--
-- Base de datos: `prueba`
--
CREATE DATABASE IF NOT EXISTS `prueba` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `prueba`;
--
-- Base de datos: `rest_api_demo`
--
CREATE DATABASE IF NOT EXISTS `rest_api_demo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `rest_api_demo`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_status` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`user_id`, `username`, `user_email`, `user_status`) VALUES
(1, 'bob', 'bob@mail.com', 0),
(2, 'john', 'john@mail.com', 1),
(3, 'mark', 'mark@mail.com', 0),
(4, 'ville', 'ville@mail.com', 0);
--
-- Base de datos: `tienda`
--
CREATE DATABASE IF NOT EXISTS `tienda` DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci;
USE `tienda`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `idusuario` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) DEFAULT NULL,
  `apellido` varchar(25) DEFAULT NULL,
  `codigo` varchar(32) DEFAULT NULL,
  `edad` int DEFAULT NULL,
  `genero` varchar(1) DEFAULT NULL,
  `ciudad` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`idusuario`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idusuario`, `nombre`, `apellido`, `codigo`, `edad`, `genero`, `ciudad`) VALUES
(5, 'Mario Alberto', 'Cáceres', 'addb47291ee169f330801ce73520', 23, 'M', 'San Miguel'),
(7, 'Lucía Carolina', 'Duarte', '7c4a8d09ca3762af61e59520943d', 28, 'F', 'San Salvador'),
(8, 'Julio Amilcar', 'Durán Umaña', '912ec803b2ce49e4a541068d495a', 21, 'M', 'San Vicente'),
(9, 'Diana Lissette', 'Benítez Contreras', 'd41d8cd98f00b204e9800998ecf8', 23, 'F', 'Santo Tomás'),
(11, 'Erika  María', 'Landaverde Castro', '25d55ad283aa400af464c76d713c', 32, 'M', 'San Salvador'),
(12, 'Luis Alexander', 'Díaz Muñoz', '25d55ad283aa400af464c76d713c', 25, 'M', 'Soyapango'),
(13, 'Marcos Antonio', 'Villalta Cortez', '25d55ad283aa400af464c76d713c', 25, 'M', 'Ahuachapán'),
(14, 'Julian Alberto', 'Alvarado Ruíz', 'e10adc3949ba59abbe56e057f20f', 36, 'M', 'Mejicanos'),
(15, 'Alexander Ismael', 'Contreras', 'e10adc3949ba59abbe56e057f20f', 24, 'M', 'San Salvador'),
(16, 'Mónica Lucía', 'Arévalo Chinchilla', 'e10adc3949ba59abbe56e057f20f883e', 31, 'F', 'Santa Tecla'),
(17, 'Lilian Adrina', 'Cortéz Montalvo', 'e10adc3949ba59abbe56e057f20f883e', 28, 'F', 'San Vicente');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
