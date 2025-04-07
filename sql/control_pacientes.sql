-- phpMyAdmin SQL Dump
-- version 5.3.0-dev+20221005.cd15c26e1f
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-04-2025 a las 03:18:25
-- Versión del servidor: 10.4.24-MariaDB
-- Versión de PHP: 8.1.4

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

CREATE TABLE `citas` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) DEFAULT NULL,
  `medico_id` int(11) DEFAULT NULL,
  `fecha_hora` datetime DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'pendiente',
  `motivo` text DEFAULT NULL,
  `notas_medico` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id`, `paciente_id`, `medico_id`, `fecha_hora`, `estado`, `motivo`, `notas_medico`) VALUES
(1, 1, 5, '2025-04-10 10:00:00', 'pendiente', 'Chequeo general', 'Paciente estable.'),
(2, 2, 6, '2025-04-11 14:30:00', 'pendiente', 'Dolor abdominal', 'Se requiere examen de orina.'),
(3, 3, 7, '2025-04-12 09:00:00', 'pendiente', 'Revisión presión', 'Indicado monitoreo diario.'),
(4, 9, 8, '2025-04-13 11:15:00', 'pendiente', 'Irritación cutánea', 'Posible dermatitis.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `enfermedades`
--

CREATE TABLE `enfermedades` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
-- Estructura de tabla para la tabla `google_calendar_auth`
--

CREATE TABLE `google_calendar_auth` (
  `usuario_id` int(11) NOT NULL,
  `is_authorized` tinyint(1) DEFAULT 0,
  `auth_date` datetime DEFAULT NULL,
  `access_token` text DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_acceso`
--

CREATE TABLE `logs_acceso` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(255) DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `logs_acceso`
--

INSERT INTO `logs_acceso` (`id`, `usuario_id`, `accion`, `fecha_hora`, `ip_address`) VALUES
(1, 1, 'Inicio de sesión', '2025-04-06 06:08:04', '192.168.1.10'),
(2, 5, 'Actualización de perfil', '2025-04-06 06:08:04', '192.168.1.11'),
(3, 2, 'Cambio de contraseña', '2025-04-06 06:08:04', '192.168.1.12'),
(4, 6, 'Cierre de sesión', '2025-04-06 06:08:04', '192.168.1.13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicamentos`
--

CREATE TABLE `medicamentos` (
  `id` int(11) NOT NULL,
  `tratamiento_id` int(11) DEFAULT NULL,
  `nombre_medicamento` varchar(100) DEFAULT NULL,
  `dosis` varchar(50) DEFAULT NULL,
  `frecuencia` varchar(100) DEFAULT NULL,
  `via_administracion` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo` varchar(20) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `fecha_envio` datetime DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE `paciente_enfermedades` (
  `paciente_id` int(11) NOT NULL,
  `enfermedad_id` int(11) NOT NULL,
  `fecha_diagnostico` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE `perfiles_medicos` (
  `usuario_id` int(11) NOT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `licencia_medica` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `perfiles_medicos`
--

INSERT INTO `perfiles_medicos` (`usuario_id`, `especialidad`, `licencia_medica`) VALUES
(5, 'Medicina General', 'LIC-1001'),
(6, 'Pediatría', 'LIC-1002'),
(7, 'Cardiología', 'LIC-1003'),
(8, 'Dermatología', 'LIC-1004'),
(10, 'Odontologo', 'LIC-1005');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registros_salud`
--

CREATE TABLE `registros_salud` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) DEFAULT NULL,
  `tipo_registro` varchar(50) DEFAULT NULL,
  `valor` varchar(50) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `registros_salud`
--

INSERT INTO `registros_salud` (`id`, `paciente_id`, `tipo_registro`, `valor`, `fecha_registro`, `notas`) VALUES
(1, 1, 'presión', '120/80', '2025-04-06 06:08:04', 'Normal'),
(2, 2, 'glucosa', '90 mg/dL', '2025-04-06 06:08:04', 'En rango'),
(3, 3, 'colesterol', '180 mg/dL', '2025-04-06 06:08:04', 'Leve elevación'),
(4, 4, 'peso', '70 kg', '2025-04-06 06:08:04', 'Dentro del rango ideal');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tratamientos`
--

CREATE TABLE `tratamientos` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) DEFAULT NULL,
  `medico_id` int(11) DEFAULT NULL,
  `nombre_tratamiento` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `edad` int(11) DEFAULT NULL,
  `sexo` varchar(20) DEFAULT 'Otro',
  `rol` varchar(20) DEFAULT 'paciente',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES
(1, 'Ana Pérez', 'ana.perez@example.com', 'hash1', 30, 'Femenino', 'paciente', '2025-04-06 06:08:04'),
(2, 'Carlos Gómez', 'carlos.gomez@example.com', 'hash2', 45, 'Masculino', 'paciente', '2025-04-06 06:08:04'),
(3, 'Laura Rodríguez', 'laura.rodriguez@example.com', 'hash3', 28, 'Femenino', 'paciente', '2025-04-06 06:08:04'),
(4, 'Jorge Martínez', 'jorge.martinez@example.com', 'hash4', 35, 'Masculino', 'paciente', '2025-04-06 06:08:04'),
(5, 'Dr. Luis Hernández', 'luis.hernandez@example.com', 'hash5', 50, 'Masculino', 'medico', '2025-04-06 06:08:04'),
(6, 'Dra. Mariana López', 'mariana.lopez@example.com', 'hash6', 42, 'Femenino', 'medico', '2025-04-06 06:08:04'),
(7, 'Dr. Felipe Ramírez', 'felipe.ramirez@example.com', 'hash7', 39, 'Masculino', 'medico', '2025-04-06 06:08:04'),
(8, 'Dra. Sofía Castillo', 'sofia.castillo@example.com', 'hash8', 33, 'Femenino', 'medico', '2025-04-06 06:08:04'),
(9, 'Adan Ruano', 'adanruano@gmail.com', '$2y$10$8XyGAz9xFv0cNDK0BnvJieJuf4NiZMFoxRNtrZdFiwbQoVr0b9m42', 19, 'Masculino', 'paciente', '2025-04-06 06:11:28'),
(10, 'Ruano', 'adanjose27@gmail.com', '$2y$10$aOsBAICTt9DcPBejzZgnuuRBt7u5a9jYRb8MYf6MOXG1ZJ5P6LIaq', 19, 'Masculino', 'medico', '2025-04-06 06:17:19');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `medico_id` (`medico_id`);

--
-- Indices de la tabla `enfermedades`
--
ALTER TABLE `enfermedades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `google_calendar_auth`
--
ALTER TABLE `google_calendar_auth`
  ADD PRIMARY KEY (`usuario_id`);

--
-- Indices de la tabla `logs_acceso`
--
ALTER TABLE `logs_acceso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `medicamentos`
--
ALTER TABLE `medicamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tratamiento_id` (`tratamiento_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `paciente_enfermedades`
--
ALTER TABLE `paciente_enfermedades`
  ADD PRIMARY KEY (`paciente_id`,`enfermedad_id`),
  ADD KEY `enfermedad_id` (`enfermedad_id`);

--
-- Indices de la tabla `perfiles_medicos`
--
ALTER TABLE `perfiles_medicos`
  ADD PRIMARY KEY (`usuario_id`),
  ADD UNIQUE KEY `licencia_medica` (`licencia_medica`);

--
-- Indices de la tabla `registros_salud`
--
ALTER TABLE `registros_salud`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`);

--
-- Indices de la tabla `tratamientos`
--
ALTER TABLE `tratamientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `medico_id` (`medico_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `enfermedades`
--
ALTER TABLE `enfermedades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `logs_acceso`
--
ALTER TABLE `logs_acceso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `medicamentos`
--
ALTER TABLE `medicamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `registros_salud`
--
ALTER TABLE `registros_salud`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tratamientos`
--
ALTER TABLE `tratamientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`medico_id`) REFERENCES `perfiles_medicos` (`usuario_id`);

--
-- Filtros para la tabla `google_calendar_auth`
--
ALTER TABLE `google_calendar_auth`
  ADD CONSTRAINT `google_calendar_auth_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `logs_acceso`
--
ALTER TABLE `logs_acceso`
  ADD CONSTRAINT `logs_acceso_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `medicamentos`
--
ALTER TABLE `medicamentos`
  ADD CONSTRAINT `medicamentos_ibfk_1` FOREIGN KEY (`tratamiento_id`) REFERENCES `tratamientos` (`id`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `paciente_enfermedades`
--
ALTER TABLE `paciente_enfermedades`
  ADD CONSTRAINT `paciente_enfermedades_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `paciente_enfermedades_ibfk_2` FOREIGN KEY (`enfermedad_id`) REFERENCES `enfermedades` (`id`);

--
-- Filtros para la tabla `perfiles_medicos`
--
ALTER TABLE `perfiles_medicos`
  ADD CONSTRAINT `perfiles_medicos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `registros_salud`
--
ALTER TABLE `registros_salud`
  ADD CONSTRAINT `registros_salud_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `tratamientos`
--
ALTER TABLE `tratamientos`
  ADD CONSTRAINT `tratamientos_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `tratamientos_ibfk_2` FOREIGN KEY (`medico_id`) REFERENCES `perfiles_medicos` (`usuario_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
