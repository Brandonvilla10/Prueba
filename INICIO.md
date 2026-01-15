# Sistema de Gestión de Pacientes

Aplicación para gestionar registros de pacientes con API REST en PHP y frontend en JavaScript.

## Inicio Rápido

Credenciales:
Usuario: admin
Contraseña: 1234567890

## Instalación

1. Crear base de datos
mysql -u root -e "CREATE DATABASE hospital_db CHARACTER SET utf8mb4;"

2. Importar estructura
mysql -u root hospital_db < backend/database/migrations/001_create_initial_tables.sql

3. Importar datos de prueba
mysql -u root hospital_db < backend/database/seeders/seed_data.sql

o

Ingresar a la interfaz grafica y colocar CREATE DATABASE hospital_db CHARACTER SET utf8mb4;

Luegon ingresar a la base de datos y colocar las sentencias sql de las tablas y los seeders que estan en estas rutas

- backend/database/migrations/001_create_initial_tables.sql

- backend/database/seeders/seed_data.sql

4. Configurar base de datos
Editar: backend/src/config/Database.php
- host = localhost
- db_name = hospital_db
- username = root
- password = (dejar vacío si no tiene contraseña)

5. Acceder a la aplicación
http://localhost/Prueba/frontend/index.html

## Estructura del Proyecto

backend/
- api/: Punto de entrada de la API
- src/: Código PHP (controladores, modelos, validación)
- database/: Migraciones y datos de prueba
- tests/: Pruebas unitarias

frontend/
- index.html: Interfaz principal
- js/: JavaScript (autenticación, pacientes, configuración)
- css/: Estilos

## Características

API REST:
- 14 endpoints para operaciones CRUD
- Autenticación con JWT
- Validación de datos
- Protección contra inyección SQL

Interfaz:
- Bootstrap 5 responsivo
- Formularios modales
- Búsqueda de pacientes
- Paginación
- Notificaciones

Base de datos:
- 7 tablas normalizadas
- Relaciones con claves foráneas
- Índices para búsquedas
- Datos de prueba

## Documentación

INSTALACION.md: Instalación paso a paso
Referencia_Rapida.md: Comandos y soluciones rápidas
