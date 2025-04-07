-- Tabla: citas
CREATE TABLE `citas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paciente_id` int(11) DEFAULT NULL,
  `medico_id` int(11) DEFAULT NULL,
  `fecha_hora` datetime DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'pendiente',
  `motivo` text DEFAULT NULL,
  `notas_medico` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `paciente_id` (`paciente_id`),
  KEY `medico_id` (`medico_id`),
  CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`medico_id`) REFERENCES `perfiles_medicos` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `citas` (`id`, `paciente_id`, `medico_id`, `fecha_hora`, `estado`, `motivo`, `notas_medico`) VALUES ('1', '1', '5', '2025-04-10 10:00:00', 'pendiente', 'Chequeo general', 'Paciente estable.');
INSERT INTO `citas` (`id`, `paciente_id`, `medico_id`, `fecha_hora`, `estado`, `motivo`, `notas_medico`) VALUES ('2', '2', '6', '2025-04-11 14:30:00', 'pendiente', 'Dolor abdominal', 'Se requiere examen de orina.');
INSERT INTO `citas` (`id`, `paciente_id`, `medico_id`, `fecha_hora`, `estado`, `motivo`, `notas_medico`) VALUES ('3', '3', '7', '2025-04-12 09:00:00', 'pendiente', 'Revisión presión', 'Indicado monitoreo diario.');
INSERT INTO `citas` (`id`, `paciente_id`, `medico_id`, `fecha_hora`, `estado`, `motivo`, `notas_medico`) VALUES ('4', '4', '8', '2025-04-13 11:15:00', 'completada', 'Irritación cutánea', 'Posible dermatitis.');

-- Tabla: configuracion
CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_clinica` varchar(100) NOT NULL DEFAULT 'Clínica Blanca Maravilla',
  `horario_atencion` varchar(100) NOT NULL DEFAULT 'Lunes a Viernes, 8:00 AM - 6:00 PM',
  `notificaciones_email` tinyint(1) NOT NULL DEFAULT 1,
  `modo_mantenimiento` tinyint(1) NOT NULL DEFAULT 0,
  `max_citas_dia` int(11) NOT NULL DEFAULT 20,
  `tema_color` varchar(20) NOT NULL DEFAULT 'principal',
  `logo` varchar(100) NOT NULL DEFAULT 'logo-default.png',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `configuracion` (`id`, `nombre_clinica`, `horario_atencion`, `notificaciones_email`, `modo_mantenimiento`, `max_citas_dia`, `tema_color`, `logo`) VALUES ('1', 'Clínica Blanca Maravilla', 'Lunes a Viernes, 8:00 AM - 6:00 PM', '1', '0', '20', 'verde', 'logo-default.png');

-- Tabla: enfermedades
CREATE TABLE `enfermedades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `enfermedades` (`id`, `nombre`, `descripcion`) VALUES ('1', 'Diabetes', 'Enfermedad metabólica que afecta el manejo de la glucosa.');
INSERT INTO `enfermedades` (`id`, `nombre`, `descripcion`) VALUES ('2', 'Hipertensión', 'Condición caracterizada por presión arterial alta.');
INSERT INTO `enfermedades` (`id`, `nombre`, `descripcion`) VALUES ('3', 'Asma', 'Enfermedad crónica que afecta las vías respiratorias.');
INSERT INTO `enfermedades` (`id`, `nombre`, `descripcion`) VALUES ('4', 'Artritis', 'Inflamación de las articulaciones que causa dolor y rigidez.');

-- Tabla: logs_acceso
CREATE TABLE `logs_acceso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(255) DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `logs_acceso_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `logs_acceso` (`id`, `usuario_id`, `accion`, `fecha_hora`, `ip_address`) VALUES ('1', '1', 'Inicio de sesión', '2025-04-05 14:47:15', '192.168.1.10');
INSERT INTO `logs_acceso` (`id`, `usuario_id`, `accion`, `fecha_hora`, `ip_address`) VALUES ('2', '5', 'Actualización de perfil', '2025-04-05 14:47:15', '192.168.1.11');
INSERT INTO `logs_acceso` (`id`, `usuario_id`, `accion`, `fecha_hora`, `ip_address`) VALUES ('3', '2', 'Cambio de contraseña', '2025-04-05 14:47:15', '192.168.1.12');
INSERT INTO `logs_acceso` (`id`, `usuario_id`, `accion`, `fecha_hora`, `ip_address`) VALUES ('4', '6', 'Cierre de sesión', '2025-04-05 14:47:15', '192.168.1.13');

-- Tabla: medicamentos
CREATE TABLE `medicamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tratamiento_id` int(11) DEFAULT NULL,
  `nombre_medicamento` varchar(100) DEFAULT NULL,
  `dosis` varchar(50) DEFAULT NULL,
  `frecuencia` varchar(100) DEFAULT NULL,
  `via_administracion` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tratamiento_id` (`tratamiento_id`),
  CONSTRAINT `medicamentos_ibfk_1` FOREIGN KEY (`tratamiento_id`) REFERENCES `tratamientos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `medicamentos` (`id`, `tratamiento_id`, `nombre_medicamento`, `dosis`, `frecuencia`, `via_administracion`) VALUES ('1', '1', 'Metformina', '500mg', 'Dos veces al día', 'Oral');
INSERT INTO `medicamentos` (`id`, `tratamiento_id`, `nombre_medicamento`, `dosis`, `frecuencia`, `via_administracion`) VALUES ('2', '1', 'Insulina', '10 unidades', 'Diario', 'Inyección');
INSERT INTO `medicamentos` (`id`, `tratamiento_id`, `nombre_medicamento`, `dosis`, `frecuencia`, `via_administracion`) VALUES ('3', '2', 'Lisinopril', '20mg', 'Una vez al día', 'Oral');
INSERT INTO `medicamentos` (`id`, `tratamiento_id`, `nombre_medicamento`, `dosis`, `frecuencia`, `via_administracion`) VALUES ('4', '2', 'Amlodipino', '5mg', 'Una vez al día', 'Oral');

-- Tabla: notificaciones
CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo` varchar(20) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `fecha_envio` datetime DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notificaciones` (`id`, `usuario_id`, `tipo`, `mensaje`, `fecha_envio`, `estado`) VALUES ('1', '1', 'recordatorio', 'Su cita es mañana a las 10:00 AM', '2025-04-04 09:00:00', 'enviado');
INSERT INTO `notificaciones` (`id`, `usuario_id`, `tipo`, `mensaje`, `fecha_envio`, `estado`) VALUES ('2', '5', 'alerta', 'Actualice sus datos de perfil', '2025-04-04 10:00:00', 'pendiente');
INSERT INTO `notificaciones` (`id`, `usuario_id`, `tipo`, `mensaje`, `fecha_envio`, `estado`) VALUES ('3', '2', 'recordatorio', 'Su tratamiento inicia la próxima semana', '2025-04-04 11:00:00', 'enviado');
INSERT INTO `notificaciones` (`id`, `usuario_id`, `tipo`, `mensaje`, `fecha_envio`, `estado`) VALUES ('4', '6', 'alerta', 'Revise resultados de laboratorio', '2025-04-04 12:00:00', 'pendiente');

-- Tabla: paciente_enfermedades
CREATE TABLE `paciente_enfermedades` (
  `paciente_id` int(11) NOT NULL,
  `enfermedad_id` int(11) NOT NULL,
  `fecha_diagnostico` date DEFAULT NULL,
  PRIMARY KEY (`paciente_id`,`enfermedad_id`),
  KEY `enfermedad_id` (`enfermedad_id`),
  CONSTRAINT `paciente_enfermedades_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `paciente_enfermedades_ibfk_2` FOREIGN KEY (`enfermedad_id`) REFERENCES `enfermedades` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `paciente_enfermedades` (`paciente_id`, `enfermedad_id`, `fecha_diagnostico`) VALUES ('1', '1', '2020-05-10');
INSERT INTO `paciente_enfermedades` (`paciente_id`, `enfermedad_id`, `fecha_diagnostico`) VALUES ('2', '2', '2019-03-15');
INSERT INTO `paciente_enfermedades` (`paciente_id`, `enfermedad_id`, `fecha_diagnostico`) VALUES ('3', '3', '2021-07-20');
INSERT INTO `paciente_enfermedades` (`paciente_id`, `enfermedad_id`, `fecha_diagnostico`) VALUES ('4', '4', '2022-01-05');

-- Tabla: perfiles_medicos
CREATE TABLE `perfiles_medicos` (
  `usuario_id` int(11) NOT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `licencia_medica` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`usuario_id`),
  UNIQUE KEY `licencia_medica` (`licencia_medica`),
  CONSTRAINT `perfiles_medicos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `perfiles_medicos` (`usuario_id`, `especialidad`, `licencia_medica`) VALUES ('5', 'Medicina General', 'LIC-1001');
INSERT INTO `perfiles_medicos` (`usuario_id`, `especialidad`, `licencia_medica`) VALUES ('6', 'Pediatría', 'LIC-1002');
INSERT INTO `perfiles_medicos` (`usuario_id`, `especialidad`, `licencia_medica`) VALUES ('7', 'Cardiología', 'LIC-1003');
INSERT INTO `perfiles_medicos` (`usuario_id`, `especialidad`, `licencia_medica`) VALUES ('8', 'Dermatología', 'LIC-1004');

-- Tabla: registros_salud
CREATE TABLE `registros_salud` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paciente_id` int(11) DEFAULT NULL,
  `tipo_registro` varchar(50) DEFAULT NULL,
  `valor` varchar(50) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `notas` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `paciente_id` (`paciente_id`),
  CONSTRAINT `registros_salud_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `registros_salud` (`id`, `paciente_id`, `tipo_registro`, `valor`, `fecha_registro`, `notas`) VALUES ('1', '1', 'presión', '120/80', '2025-04-05 14:47:15', 'Normal');
INSERT INTO `registros_salud` (`id`, `paciente_id`, `tipo_registro`, `valor`, `fecha_registro`, `notas`) VALUES ('2', '2', 'glucosa', '90 mg/dL', '2025-04-05 14:47:15', 'En rango');
INSERT INTO `registros_salud` (`id`, `paciente_id`, `tipo_registro`, `valor`, `fecha_registro`, `notas`) VALUES ('3', '3', 'colesterol', '180 mg/dL', '2025-04-05 14:47:15', 'Leve elevación');
INSERT INTO `registros_salud` (`id`, `paciente_id`, `tipo_registro`, `valor`, `fecha_registro`, `notas`) VALUES ('4', '4', 'peso', '70 kg', '2025-04-05 14:47:15', 'Dentro del rango ideal');

-- Tabla: tratamientos
CREATE TABLE `tratamientos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paciente_id` int(11) DEFAULT NULL,
  `medico_id` int(11) DEFAULT NULL,
  `nombre_tratamiento` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'activo',
  PRIMARY KEY (`id`),
  KEY `paciente_id` (`paciente_id`),
  KEY `medico_id` (`medico_id`),
  CONSTRAINT `tratamientos_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `tratamientos_ibfk_2` FOREIGN KEY (`medico_id`) REFERENCES `perfiles_medicos` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tratamientos` (`id`, `paciente_id`, `medico_id`, `nombre_tratamiento`, `descripcion`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES ('1', '1', '5', 'Control Diabetes', 'Administración de insulina y dieta especial.', '2025-04-01', '2025-06-01', 'activo');
INSERT INTO `tratamientos` (`id`, `paciente_id`, `medico_id`, `nombre_tratamiento`, `descripcion`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES ('2', '2', '6', 'Reducción presión arterial', 'Uso de medicamentos antihipertensivos.', '2025-04-02', '2025-07-02', 'activo');
INSERT INTO `tratamientos` (`id`, `paciente_id`, `medico_id`, `nombre_tratamiento`, `descripcion`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES ('3', '3', '7', 'Tratamiento para asma', 'Uso de inhaladores.', '2025-04-03', '2025-06-03', 'activo');
INSERT INTO `tratamientos` (`id`, `paciente_id`, `medico_id`, `nombre_tratamiento`, `descripcion`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES ('4', '4', '8', 'Alivio artritis', 'Ejercicios y analgésicos.', '2025-04-04', '2025-07-04', 'activo');

-- Tabla: usuarios
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `edad` int(11) DEFAULT NULL,
  `sexo` varchar(20) DEFAULT 'Otro',
  `rol` varchar(20) DEFAULT 'paciente',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('1', 'Ana Pérez', 'ana.perez@example.com', 'hash1', '30', 'Femenino', 'paciente', '2025-04-05 14:47:15');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('2', 'Carlos Gómez', 'carlos.gomez@example.com', 'hash2', '45', 'Masculino', 'paciente', '2025-04-05 14:47:15');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('3', 'Laura Rodríguez', 'laura.rodriguez@example.com', 'hash3', '28', 'Femenino', 'paciente', '2025-04-05 14:47:15');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('4', 'Jorge Martínez', 'jorge.martinez@example.com', 'hash4', '35', 'Masculino', 'paciente', '2025-04-05 14:47:15');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('5', 'Dr. Luis Hernández', 'luis.hernandez@example.com', 'hash5', '50', 'Masculino', 'medico', '2025-04-05 14:47:15');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('6', 'Dra. Mariana López', 'mariana.lopez@example.com', 'hash6', '42', 'Femenino', 'medico', '2025-04-05 14:47:15');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('7', 'Dr. Felipe Ramírez', 'felipe.ramirez@example.com', 'hash7', '39', 'Masculino', 'medico', '2025-04-05 14:47:15');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('8', 'Dra. Sofía Castillo', 'sofia.castillo@example.com', 'hash8', '33', 'Femenino', 'medico', '2025-04-05 14:47:15');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('9', 'Blanca', 'blanca@gmail.com', '$2y$10$LrSdYKYXweYVdubBy.lUwuXDT0ncce5ebu/JYwuTzLJFuF2fgwloO', '19', 'Femenino', 'admin', '2025-04-05 14:49:55');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('10', 'Blanca Maravilla', 'blancamaravilla@gmail.com', '$2y$10$WSfhQ8zg.aH43Q1mtwQrz.hCXmZNE2w6xCOL3PVicKA0PXuEkQnFK', '19', 'Femenino', 'medico', '2025-04-05 14:53:22');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('11', 'Silvia', 'silvia@gmail.com', '$2y$10$1CnlpBTzPmj5WYfJSylBiugXv7h/8Y4PjGpGJXPOLFNgYHwVEidTW', '35', 'Femenino', 'paciente', '2025-04-05 14:54:17');

