# Referencia Rápida

## Credenciales de Prueba

Usuario: admin
Contraseña: 1234567890

## Endpoints de API

### Autenticación

POST /api/auth/login
```json
{
  "username": "admin",
  "password": "1234567890"
}
```

GET /api/auth/verify
Requiere token en header Authorization: Bearer {token}

### Pacientes

GET /api/pacientes?page=1&limit=10&search=
Obtener lista de pacientes con paginación y búsqueda

GET /api/pacientes/:id
Obtener paciente por ID

POST /api/pacientes
Crear nuevo paciente
```json
{
  "tipo_documento_id": 1,
  "numero_documento": "12345678",
  "nombre1": "Juan",
  "apellido1": "Pérez",
  "genero_id": 1,
  "departamento_id": 1,
  "municipio_id": 1,
  "correo": "juan@example.com",
  "telefono": "3001234567"
}
```

PUT /api/pacientes/:id
Actualizar paciente (mismo formato que POST)

DELETE /api/pacientes/:id
Eliminar paciente (solo administrador)

### Datos de Búsqueda

GET /api/pacientes/lookup/document-types
GET /api/pacientes/lookup/genders
GET /api/pacientes/lookup/departments
GET /api/pacientes/lookup/municipalities?departamento_id=1

## Estructura de Base de Datos

7 tablas relacionadas:
- departamentos (5 registros)
- municipios (10 registros)
- tipos_documento (4 tipos)
- genero (2 géneros)
- usuarios (1 usuario administrador)
- pacientes (5 registros de prueba)
- audit_log (para auditoría)

## Instalación Rápida

1. Crear base de datos: hospital_db
2. Ejecutar: database/migrations/001_create_initial_tables.sql
3. Ejecutar: database/seeders/seed_data.sql
4. Acceder a: http://localhost/Prueba

## Estructura de Carpetas

```
/Prueba
  /backend
    /api
      index.php (punto de entrada)
    /src
      /config (Database, Router)
      /controllers (Patient, User)
      /models (Patient, User)
      /middleware (Auth)
      /utils (JWT)
      /validation (Validator, Sanitizer)
    /database
      /migrations (schema)
      /seeders (datos de prueba)
    /tests
  /frontend
    index.html
    /css (style.css)
    /js (config, auth, patient)
```

## Características de Seguridad

- Autenticación con JWT
- Validación en servidor y cliente
- Protección contra SQL injection (prepared statements)
- Protección contra XSS (sanitización)
- Contraseñas con bcrypt
- Headers CORS configurados
- Roles de usuario (admin/usuario)

## Comandos Útiles

Importar datos de prueba:
```bash
mysql -u root hospital_db < backend/database/migrations/001_create_initial_tables.sql
mysql -u root hospital_db < backend/database/seeders/seed_data.sql
```

Ejecutar pruebas:
```bash
php backend/tests/PatientControllerTest.php
```

## Solución de Problemas

### Error 404 en rutas
- Verificar que mod_rewrite está habilitado
- Verificar la ruta en .htaccess
- Verificar permisos de archivo

### Error de conexión a BD
- Verificar credentials en Database.php
- Verificar que MySQL está corriendo
- Verificar que la base de datos existe

### Token JWT expirado
- Regenerar token con login
- Token vence en 3600 segundos (1 hora)

### Errores de validación
- Revisar los campos requeridos en el formulario
- Verificar formato de documento
- Verificar rango de números de teléfono
