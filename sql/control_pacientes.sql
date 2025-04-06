-- Base de datos
CREATE DATABASE IF NOT EXISTS control_pacientes;
USE control_pacientes;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    edad INT,
    sexo VARCHAR(20) DEFAULT 'Otro',
    rol VARCHAR(20) DEFAULT 'paciente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de enfermedades crónicas (catálogo)
CREATE TABLE IF NOT EXISTS enfermedades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT
);

-- Tabla para relación paciente-enfermedad
CREATE TABLE IF NOT EXISTS paciente_enfermedades (
    paciente_id INT,
    enfermedad_id INT,
    fecha_diagnostico DATE,
    PRIMARY KEY (paciente_id, enfermedad_id),
    FOREIGN KEY (paciente_id) REFERENCES usuarios(id),
    FOREIGN KEY (enfermedad_id) REFERENCES enfermedades(id)
);

-- Tabla de médicos (extensión de usuarios)
CREATE TABLE IF NOT EXISTS perfiles_medicos (
    usuario_id INT PRIMARY KEY,
    especialidad VARCHAR(100),
    licencia_medica VARCHAR(50) UNIQUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla de citas
CREATE TABLE IF NOT EXISTS citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT,
    medico_id INT,
    fecha_hora DATETIME,
    estado VARCHAR(20) DEFAULT 'pendiente',
    motivo TEXT,
    notas_medico TEXT,
    FOREIGN KEY (paciente_id) REFERENCES usuarios(id),
    FOREIGN KEY (medico_id) REFERENCES perfiles_medicos(usuario_id)
);

-- Tabla de tratamientos
CREATE TABLE IF NOT EXISTS tratamientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT,
    medico_id INT,
    nombre_tratamiento VARCHAR(255),
    descripcion TEXT,
    fecha_inicio DATE,
    fecha_fin DATE,
    estado VARCHAR(20) DEFAULT 'activo',
    FOREIGN KEY (paciente_id) REFERENCES usuarios(id),
    FOREIGN KEY (medico_id) REFERENCES perfiles_medicos(usuario_id)
);

-- Tabla de medicamentos
CREATE TABLE IF NOT EXISTS medicamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tratamiento_id INT,
    nombre_medicamento VARCHAR(100),
    dosis VARCHAR(50),
    frecuencia VARCHAR(100),
    via_administracion VARCHAR(50),
    FOREIGN KEY (tratamiento_id) REFERENCES tratamientos(id)
);

-- Tabla de registros de salud
CREATE TABLE IF NOT EXISTS registros_salud (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT,
    tipo_registro VARCHAR(50),
    valor VARCHAR(50),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notas TEXT,
    FOREIGN KEY (paciente_id) REFERENCES usuarios(id)
);

-- Tabla de notificaciones
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    tipo VARCHAR(20),
    mensaje TEXT,
    fecha_envio DATETIME,
    estado VARCHAR(20),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla de logs de acceso (para seguridad)
CREATE TABLE IF NOT EXISTS logs_acceso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(255),
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Insertar usuarios (4 pacientes y 4 médicos)
INSERT INTO usuarios (nombre, correo, contrasena, edad, sexo, rol) VALUES
('Ana Pérez', 'ana.perez@example.com', 'hash1', 30, 'Femenino', 'paciente'),
('Carlos Gómez', 'carlos.gomez@example.com', 'hash2', 45, 'Masculino', 'paciente'),
('Laura Rodríguez', 'laura.rodriguez@example.com', 'hash3', 28, 'Femenino', 'paciente'),
('Jorge Martínez', 'jorge.martinez@example.com', 'hash4', 35, 'Masculino', 'paciente'),
('Dr. Luis Hernández', 'luis.hernandez@example.com', 'hash5', 50, 'Masculino', 'medico'),
('Dra. Mariana López', 'mariana.lopez@example.com', 'hash6', 42, 'Femenino', 'medico'),
('Dr. Felipe Ramírez', 'felipe.ramirez@example.com', 'hash7', 39, 'Masculino', 'medico'),
('Dra. Sofía Castillo', 'sofia.castillo@example.com', 'hash8', 33, 'Femenino', 'medico');

-- Insertar perfiles médicos
INSERT INTO perfiles_medicos (usuario_id, especialidad, licencia_medica) VALUES
(5, 'Medicina General', 'LIC-1001'),
(6, 'Pediatría', 'LIC-1002'),
(7, 'Cardiología', 'LIC-1003'),
(8, 'Dermatología', 'LIC-1004');

-- Insertar enfermedades
INSERT INTO enfermedades (nombre, descripcion) VALUES
('Diabetes', 'Enfermedad metabólica que afecta el manejo de la glucosa.'),
('Hipertensión', 'Condición caracterizada por presión arterial alta.'),
('Asma', 'Enfermedad crónica que afecta las vías respiratorias.'),
('Artritis', 'Inflamación de las articulaciones que causa dolor y rigidez.');

-- Insertar relaciones entre pacientes y enfermedades
INSERT INTO paciente_enfermedades (paciente_id, enfermedad_id, fecha_diagnostico) VALUES
(1, 1, '2020-05-10'),
(2, 2, '2019-03-15'),
(3, 3, '2021-07-20'),
(4, 4, '2022-01-05');

-- Insertar registros de salud
INSERT INTO registros_salud (paciente_id, tipo_registro, valor, notas) VALUES
(1, 'presión', '120/80', 'Normal'),
(2, 'glucosa', '90 mg/dL', 'En rango'),
(3, 'colesterol', '180 mg/dL', 'Leve elevación'),
(4, 'peso', '70 kg', 'Dentro del rango ideal');

-- Insertar logs de acceso
INSERT INTO logs_acceso (usuario_id, accion, ip_address) VALUES
(1, 'Inicio de sesión', '192.168.1.10'),
(5, 'Actualización de perfil', '192.168.1.11'),
(2, 'Cambio de contraseña', '192.168.1.12'),
(6, 'Cierre de sesión', '192.168.1.13');

-- Insertar citas
INSERT INTO citas (paciente_id, medico_id, fecha_hora, motivo, notas_medico) VALUES
(1, 5, '2025-04-10 10:00:00', 'Chequeo general', 'Paciente estable.'),
(2, 6, '2025-04-11 14:30:00', 'Dolor abdominal', 'Se requiere examen de orina.'),
(3, 7, '2025-04-12 09:00:00', 'Revisión presión', 'Indicado monitoreo diario.'),
(4, 8, '2025-04-13 11:15:00', 'Irritación cutánea', 'Posible dermatitis.');

-- Insertar tratamientos
INSERT INTO tratamientos (paciente_id, medico_id, nombre_tratamiento, descripcion, fecha_inicio, fecha_fin) VALUES
(1, 5, 'Control Diabetes', 'Administración de insulina y dieta especial.', '2025-04-01', '2025-06-01'),
(2, 6, 'Reducción presión arterial', 'Uso de medicamentos antihipertensivos.', '2025-04-02', '2025-07-02'),
(3, 7, 'Tratamiento para asma', 'Uso de inhaladores.', '2025-04-03', '2025-06-03'),
(4, 8, 'Alivio artritis', 'Ejercicios y analgésicos.', '2025-04-04', '2025-07-04');

-- Insertar medicamentos (relacionados con los tratamientos anteriores)
INSERT INTO medicamentos (tratamiento_id, nombre_medicamento, dosis, frecuencia, via_administracion) VALUES
(1, 'Metformina', '500mg', 'Dos veces al día', 'Oral'),
(1, 'Insulina', '10 unidades', 'Diario', 'Inyección'),
(2, 'Lisinopril', '20mg', 'Una vez al día', 'Oral'),
(2, 'Amlodipino', '5mg', 'Una vez al día', 'Oral');

-- Insertar notificaciones
INSERT INTO notificaciones (usuario_id, tipo, mensaje, fecha_envio, estado) VALUES
(1, 'recordatorio', 'Su cita es mañana a las 10:00 AM', '2025-04-04 09:00:00', 'enviado'),
(5, 'alerta', 'Actualice sus datos de perfil', '2025-04-04 10:00:00', 'pendiente'),
(2, 'recordatorio', 'Su tratamiento inicia la próxima semana', '2025-04-04 11:00:00', 'enviado'),
(6, 'alerta', 'Revise resultados de laboratorio', '2025-04-04 12:00:00', 'pendiente');