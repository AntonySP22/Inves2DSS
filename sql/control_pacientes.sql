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


---- Datos para enfermedades y medicamentos
INSERT INTO medicamentos (tratamiento_id, nombre_medicamento, dosis, frecuencia, via_administracion) VALUES
(1, 'Metformina', '850 mg', 'Cada 8 horas', 'Oral'),
(1, 'Insulina Glargina', '20 unidades', 'Una vez al día', 'Subcutánea'),
(1, 'Losartán', '50 mg', 'Cada 24 horas', 'Oral'),
(2, 'Salbutamol', '100 mcg', 'Cada 6 horas (si es necesario)', 'Inhalación'),
(2, 'Budesónida', '200 mcg', 'Dos veces al día', 'Inhalación'),
(3, 'Ibuprofeno', '400 mg', 'Cada 8 horas (con comida)', 'Oral');


INSERT INTO enfermedades (nombre, descripcion) VALUES
('Diabetes Tipo 1', 'Deficiencia de insulina por destrucción de células beta del páncreas.'),
('Diabetes Tipo 2', 'Resistencia a la insulina con producción deficiente.'),
('Hipertensión Arterial', 'Presión arterial sistólica ≥140 mmHg o diastólica ≥90 mmHg.'),
('Asma', 'Inflamación crónica de las vías respiratorias.'),
('Artritis Reumatoide', 'Enfermedad autoinmune que afecta articulaciones.'),
('EPOC', 'Enfermedad pulmonar obstructiva crónica.');