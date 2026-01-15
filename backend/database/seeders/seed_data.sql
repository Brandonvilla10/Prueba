-- Seed Departamentos (5 registros)
INSERT INTO departamentos (nombre) VALUES
('Cundinamarca'),
('Antioquia'),
('Atlántico'),
('Valle del Cauca'),
('Bolívar');

-- Seed Municipios (2 registros por departamento)
INSERT INTO municipios (departamento_id, nombre) VALUES
(1, 'Bogotá'),
(1, 'Zipaquirá'),
(2, 'Medellín'),
(2, 'Bello'),
(3, 'Barranquilla'),
(3, 'Soledad'),
(4, 'Cali'),
(4, 'Palmira'),
(5, 'Cartagena'),
(5, 'Turbaco');

-- Seed Tipos de Documento (2 registros)
INSERT INTO tipos_documento (nombre) VALUES
('Cédula de Ciudadanía'),
('Pasaporte');

-- Seed Genero (2 registros)
INSERT INTO genero (nombre) VALUES
('Masculino'),
('Femenino');

-- Seed Users (Admin user with password: 1234567890)
-- Password hash generated with password_hash('1234567890', PASSWORD_BCRYPT)
INSERT INTO users (username, email, password, role, is_active) VALUES
('admin', 'admin@hospital.com', '$2y$10$ZW9IluYbPmPMO5IaL10wl.sIx.6DF3vgJPT7beMTKe9/Ffju.Rd9S', 'admin', 1);

-- Seed Paciente (5 registros de prueba)
INSERT INTO paciente (tipo_documento_id, numero_documento, nombre1, nombre2, apellido1, apellido2, genero_id, departamento_id, municipio_id, correo, telefono, fecha_nacimiento, direccion, estado) VALUES
(1, '1001234567', 'Juan', 'Carlos', 'García', 'López', 1, 1, 1, 'juan.garcia@email.com', '3001234567', '1985-05-15', 'Carrera 7 #25-50, Bogotá', 'activo'),
(1, '1002345678', 'María', 'Isabel', 'Rodríguez', 'Martínez', 2, 1, 2, 'maria.rodriguez@email.com', '3002345678', '1990-08-22', 'Avenida Principal #10-20, Zipaquirá', 'activo'),
(2, '1003456789', 'Carlos', 'Alberto', 'Hernández', 'González', 1, 2, 3, 'carlos.hernandez@email.com', '3003456789', '1988-03-10', 'Calle 50 #15-30, Medellín', 'activo'),
(1, '1004567890', 'Alejandra', 'Margarita', 'Pérez', 'Sánchez', 2, 2, 4, 'alejandra.perez@email.com', '3004567890', '1992-12-05', 'Carrera 80 #40-50, Bello', 'activo'),
(1, '1005678901', 'Roberto', 'Manuel', 'López', 'Ramírez', 1, 3, 5, 'roberto.lopez@email.com', '3005678901', '1987-07-18', 'Avenida Paseo Bolivar #20-40, Barranquilla', 'activo');
