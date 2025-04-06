-- Tabla: citas
CREATE TABLE `citas` (
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

INSERT INTO `citas` (`id`, `paciente_id`, `medico_id`, `fecha_hora`, `estado`, `motivo`, `notas_medico`) VALUES ('1', '1', '5', '2025-04-10 10:00:00', 'pendiente', 'Chequeo general', 'Paciente estable.');
INSERT INTO `citas` (`id`, `paciente_id`, `medico_id`, `fecha_hora`, `estado`, `motivo`, `notas_medico`) VALUES ('2', '2', '6', '2025-04-11 14:30:00', 'pendiente', 'Dolor abdominal', 'Se requiere examen de orina.');
INSERT INTO `citas` (`id`, `paciente_id`, `medico_id`, `fecha_hora`, `estado`, `motivo`, `notas_medico`) VALUES ('3', '3', '7', '2025-04-12 09:00:00', 'pendiente', 'Revisión presión', 'Indicado monitoreo diario.');
INSERT INTO `citas` (`id`, `paciente_id`, `medico_id`, `fecha_hora`, `estado`, `motivo`, `notas_medico`) VALUES ('4', '4', '8', '2025-04-13 11:15:00', 'pendiente', 'Irritación cutánea', 'Posible dermatitis.');

-- Tabla: configuracion
CREATE TABLE `configuracion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_clinica` varchar(100) NOT NULL DEFAULT 'Clínica Blanca Maravilla',
  `horario_atencion` varchar(100) NOT NULL DEFAULT 'Lunes a Viernes, 8:00 AM - 6:00 PM',
  `notificaciones_email` tinyint(1) NOT NULL DEFAULT '1',
  `modo_mantenimiento` tinyint(1) NOT NULL DEFAULT '0',
  `max_citas_dia` int NOT NULL DEFAULT '20',
  `tema_color` varchar(20) NOT NULL DEFAULT 'principal',
  `logo` varchar(100) NOT NULL DEFAULT 'logo-default.png',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `configuracion` (`id`, `nombre_clinica`, `horario_atencion`, `notificaciones_email`, `modo_mantenimiento`, `max_citas_dia`, `tema_color`, `logo`) VALUES ('1', 'Clínica Blanca Maravilla', 'Lunes a Viernes, 8:00 AM - 6:00 PM', '1', '0', '20', 'principal', 'logo-default.png');

-- Tabla: enfermedades
CREATE TABLE `enfermedades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `enfermedades` (`id`, `nombre`, `descripcion`) VALUES ('1', 'Diabetes', 'Enfermedad metabólica que afecta el manejo de la glucosa.');
INSERT INTO `enfermedades` (`id`, `nombre`, `descripcion`) VALUES ('2', 'Hipertensión', 'Condición caracterizada por presión arterial alta.');
INSERT INTO `enfermedades` (`id`, `nombre`, `descripcion`) VALUES ('3', 'Asma', 'Enfermedad crónica que afecta las vías respiratorias.');
INSERT INTO `enfermedades` (`id`, `nombre`, `descripcion`) VALUES ('4', 'Artritis', 'Inflamación de las articulaciones que causa dolor y rigidez.');

-- Tabla: logs_acceso
CREATE TABLE `logs_acceso` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `accion` varchar(255) DEFAULT NULL,
  `fecha_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `logs_acceso` (`id`, `usuario_id`, `accion`, `fecha_hora`, `ip_address`) VALUES ('1', '1', 'Inicio de sesión', '2025-04-05 14:50:41', '192.168.1.10');
INSERT INTO `logs_acceso` (`id`, `usuario_id`, `accion`, `fecha_hora`, `ip_address`) VALUES ('2', '5', 'Actualización de perfil', '2025-04-05 14:50:41', '192.168.1.11');
INSERT INTO `logs_acceso` (`id`, `usuario_id`, `accion`, `fecha_hora`, `ip_address`) VALUES ('3', '2', 'Cambio de contraseña', '2025-04-05 14:50:41', '192.168.1.12');
INSERT INTO `logs_acceso` (`id`, `usuario_id`, `accion`, `fecha_hora`, `ip_address`) VALUES ('4', '6', 'Cierre de sesión', '2025-04-05 14:50:41', '192.168.1.13');

-- Tabla: medicamentos
CREATE TABLE `medicamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tratamiento_id` int DEFAULT NULL,
  `nombre_medicamento` varchar(100) DEFAULT NULL,
  `dosis` varchar(50) DEFAULT NULL,
  `frecuencia` varchar(100) DEFAULT NULL,
  `via_administracion` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tratamiento_id` (`tratamiento_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `medicamentos` (`id`, `tratamiento_id`, `nombre_medicamento`, `dosis`, `frecuencia`, `via_administracion`) VALUES ('1', '1', 'Metformina', '500mg', 'Dos veces al día', 'Oral');
INSERT INTO `medicamentos` (`id`, `tratamiento_id`, `nombre_medicamento`, `dosis`, `frecuencia`, `via_administracion`) VALUES ('2', '1', 'Insulina', '10 unidades', 'Diario', 'Inyección');
INSERT INTO `medicamentos` (`id`, `tratamiento_id`, `nombre_medicamento`, `dosis`, `frecuencia`, `via_administracion`) VALUES ('3', '2', 'Lisinopril', '20mg', 'Una vez al día', 'Oral');
INSERT INTO `medicamentos` (`id`, `tratamiento_id`, `nombre_medicamento`, `dosis`, `frecuencia`, `via_administracion`) VALUES ('4', '2', 'Amlodipino', '5mg', 'Una vez al día', 'Oral');

-- Tabla: notificaciones
CREATE TABLE `notificaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `tipo` varchar(20) DEFAULT NULL,
  `mensaje` text,
  `fecha_envio` datetime DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `notificaciones` (`id`, `usuario_id`, `tipo`, `mensaje`, `fecha_envio`, `estado`) VALUES ('1', '1', 'recordatorio', 'Su cita es mañana a las 10:00 AM', '2025-04-04 09:00:00', 'enviado');
INSERT INTO `notificaciones` (`id`, `usuario_id`, `tipo`, `mensaje`, `fecha_envio`, `estado`) VALUES ('2', '5', 'alerta', 'Actualice sus datos de perfil', '2025-04-04 10:00:00', 'pendiente');
INSERT INTO `notificaciones` (`id`, `usuario_id`, `tipo`, `mensaje`, `fecha_envio`, `estado`) VALUES ('3', '2', 'recordatorio', 'Su tratamiento inicia la próxima semana', '2025-04-04 11:00:00', 'enviado');
INSERT INTO `notificaciones` (`id`, `usuario_id`, `tipo`, `mensaje`, `fecha_envio`, `estado`) VALUES ('4', '6', 'alerta', 'Revise resultados de laboratorio', '2025-04-04 12:00:00', 'pendiente');

-- Tabla: paciente_enfermedades
CREATE TABLE `paciente_enfermedades` (
  `paciente_id` int NOT NULL,
  `enfermedad_id` int NOT NULL,
  `fecha_diagnostico` date DEFAULT NULL,
  PRIMARY KEY (`paciente_id`,`enfermedad_id`),
  KEY `enfermedad_id` (`enfermedad_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `paciente_enfermedades` (`paciente_id`, `enfermedad_id`, `fecha_diagnostico`) VALUES ('1', '1', '2020-05-10');
INSERT INTO `paciente_enfermedades` (`paciente_id`, `enfermedad_id`, `fecha_diagnostico`) VALUES ('2', '2', '2019-03-15');
INSERT INTO `paciente_enfermedades` (`paciente_id`, `enfermedad_id`, `fecha_diagnostico`) VALUES ('3', '3', '2021-07-20');
INSERT INTO `paciente_enfermedades` (`paciente_id`, `enfermedad_id`, `fecha_diagnostico`) VALUES ('4', '4', '2022-01-05');

-- Tabla: perfiles_medicos
CREATE TABLE `perfiles_medicos` (
  `usuario_id` int NOT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `licencia_medica` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`usuario_id`),
  UNIQUE KEY `licencia_medica` (`licencia_medica`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `perfiles_medicos` (`usuario_id`, `especialidad`, `licencia_medica`) VALUES ('5', 'Medicina General', 'LIC-1001');
INSERT INTO `perfiles_medicos` (`usuario_id`, `especialidad`, `licencia_medica`) VALUES ('6', 'Pediatría', 'LIC-1002');
INSERT INTO `perfiles_medicos` (`usuario_id`, `especialidad`, `licencia_medica`) VALUES ('7', 'Cardiología', 'LIC-1003');
INSERT INTO `perfiles_medicos` (`usuario_id`, `especialidad`, `licencia_medica`) VALUES ('8', 'Dermatología', 'LIC-1004');

-- Tabla: registros_salud
CREATE TABLE `registros_salud` (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int DEFAULT NULL,
  `tipo_registro` varchar(50) DEFAULT NULL,
  `valor` varchar(50) DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `notas` text,
  PRIMARY KEY (`id`),
  KEY `paciente_id` (`paciente_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `registros_salud` (`id`, `paciente_id`, `tipo_registro`, `valor`, `fecha_registro`, `notas`) VALUES ('1', '1', 'presión', '120/80', '2025-04-05 14:50:41', 'Normal');
INSERT INTO `registros_salud` (`id`, `paciente_id`, `tipo_registro`, `valor`, `fecha_registro`, `notas`) VALUES ('2', '2', 'glucosa', '90 mg/dL', '2025-04-05 14:50:41', 'En rango');
INSERT INTO `registros_salud` (`id`, `paciente_id`, `tipo_registro`, `valor`, `fecha_registro`, `notas`) VALUES ('3', '3', 'colesterol', '180 mg/dL', '2025-04-05 14:50:41', 'Leve elevación');
INSERT INTO `registros_salud` (`id`, `paciente_id`, `tipo_registro`, `valor`, `fecha_registro`, `notas`) VALUES ('4', '4', 'peso', '70 kg', '2025-04-05 14:50:41', 'Dentro del rango ideal');

-- Tabla: tratamientos
CREATE TABLE `tratamientos` (
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

INSERT INTO `tratamientos` (`id`, `paciente_id`, `medico_id`, `nombre_tratamiento`, `descripcion`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES ('1', '1', '5', 'Control Diabetes', 'Administración de insulina y dieta especial.', '2025-04-01', '2025-06-01', 'activo');
INSERT INTO `tratamientos` (`id`, `paciente_id`, `medico_id`, `nombre_tratamiento`, `descripcion`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES ('2', '2', '6', 'Reducción presión arterial', 'Uso de medicamentos antihipertensivos.', '2025-04-02', '2025-07-02', 'activo');
INSERT INTO `tratamientos` (`id`, `paciente_id`, `medico_id`, `nombre_tratamiento`, `descripcion`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES ('3', '3', '7', 'Tratamiento para asma', 'Uso de inhaladores.', '2025-04-03', '2025-06-03', 'activo');
INSERT INTO `tratamientos` (`id`, `paciente_id`, `medico_id`, `nombre_tratamiento`, `descripcion`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES ('4', '4', '8', 'Alivio artritis', 'Ejercicios y analgésicos.', '2025-04-04', '2025-07-04', 'activo');

-- Tabla: usuarios
CREATE TABLE `usuarios` (
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
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('1', 'Ana Pérez', 'ana.perez@example.com', 'hash1', '30', 'Femenino', 'paciente', '2025-04-05 14:50:41');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('2', 'Carlos Gómez', 'carlos.gomez@example.com', 'hash2', '45', 'Masculino', 'paciente', '2025-04-05 14:50:41');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('3', 'Laura Rodríguez', 'laura.rodriguez@example.com', 'hash3', '28', 'Femenino', 'paciente', '2025-04-05 14:50:41');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('4', 'Jorge Martínez', 'jorge.martinez@example.com', 'hash4', '35', 'Masculino', 'paciente', '2025-04-05 14:50:41');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('5', 'Dr. Luis Hernández', 'luis.hernandez@example.com', 'hash5', '50', 'Masculino', 'medico', '2025-04-05 14:50:41');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('6', 'Dra. Mariana López', 'mariana.lopez@example.com', 'hash6', '42', 'Femenino', 'medico', '2025-04-05 14:50:41');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('7', 'Dr. Felipe Ramírez', 'felipe.ramirez@example.com', 'hash7', '39', 'Masculino', 'medico', '2025-04-05 14:50:41');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('8', 'Dra. Sofía Castillo', 'sofia.castillo@example.com', 'hash8', '33', 'Femenino', 'medico', '2025-04-05 14:50:41');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('9', 'Elmer Antonio Cruz García', 'cg240032@alumno.udb.edu.sv', '$2y$12$w2EZu863NVl94wskqMLQ2.POaBPeOywmQM1G4zIwTxOTC8lY7HJ2m', '0', 'Masculino', 'paciente', '2025-04-05 22:05:42');
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `edad`, `sexo`, `rol`, `fecha_registro`) VALUES ('10', 'Juan', 'panchito@gmail.com', '$2y$12$oZStYDxTa.deUe2pbeHqF.zmZ8COMVNtGmUDtHdGz85u39kwG2P1W', '18', 'Masculino', 'admin', '2025-04-05 22:06:51');

