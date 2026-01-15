# Guía de Instalación

## Requisitos Previos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache con mod_rewrite habilitado
- Navegador web moderno

## Paso 1: Habilitar mod_rewrite en Apache

Linux/Mac:
sudo a2enmod rewrite
sudo systemctl restart apache2

Windows (XAMPP):
- Abrir httpd.conf en xampp/apache/conf/
- Buscar la línea: #LoadModule rewrite_module modules/mod_rewrite.so
- Eliminar el # al inicio
- Reiniciar Apache

## Paso 2: Crear la Base de Datos

mysql -u root -e "CREATE DATABASE hospital_db CHARACTER SET utf8mb4;"

o usando cliente MySQL:

CREATE DATABASE hospital_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hospital_db;

## Paso 3: Importar la Estructura de Tablas

mysql -u root hospital_db < backend/database/migrations/001_create_initial_tables.sql

## Paso 4: Importar Datos de Prueba

mysql -u root hospital_db < backend/database/seeders/seed_data.sql

Esto cargará:
- 5 departamentos
- 10 municipios (2 por departamento)
- 2 tipos de documento
- 2 géneros
- 1 usuario administrador
- 5 pacientes de prueba

## Paso 5: Configurar el Backend

Editar: backend/src/config/Database.php

Buscar la función connect() y configurar:
- host: localhost
- db_name: hospital_db
- username: root
- password: (vacío si no tiene contraseña)

Ejemplo:
```php
private function connect()
{
    $this->host = 'localhost';
    $this->db_name = 'hospital_db';
    $this->username = 'root';
    $this->password = '';
```

## Paso 6: Colocar el Proyecto en la Carpeta Web

Windows XAMPP:
C:\xampp\htdocs\Prueba\

Linux Apache:
/var/www/html/Prueba/

## Paso 7: Permisos de Archivo (Linux/Mac)

chmod -R 755 Prueba/backend
chmod -R 755 Prueba/frontend
chmod 644 Prueba/backend/api/.htaccess

## Paso 8: Verificar la Instalación

Abrir navegador:
http://localhost/Prueba/frontend/index.html

Ingresar:
Usuario: admin
Contraseña: 1234567890

Debe cargar la interfaz de pacientes sin errores.

