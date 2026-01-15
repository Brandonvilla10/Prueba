<?php

namespace Tests;

class PatientControllerTest
{
    private $pdo;

    public function setUp()
    {
        // Configurar conexión de base de datos de prueba
        $this->pdo = new \PDO(
            'mysql:host=localhost;dbname=hospital_db_test',
            'root',
            '',
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]
        );

        // Crear tablas de prueba
        $this->createTestTables();
    }

    private function createTestTables()
    {
        $this->pdo->exec('DROP TABLE IF EXISTS paciente');
        $this->pdo->exec('DROP TABLE IF EXISTS municipios');
        $this->pdo->exec('DROP TABLE IF EXISTS departamentos');
        $this->pdo->exec('DROP TABLE IF EXISTS genero');
        $this->pdo->exec('DROP TABLE IF EXISTS tipos_documento');
        $this->pdo->exec('DROP TABLE IF EXISTS users');

        // Crear tablas
        $this->pdo->exec(file_get_contents(__DIR__ . '/../../database/migrations/001_create_initial_tables.sql'));
    }

    public function testValidatorRequired()
    {
        $validator = new \App\Validation\Validator();
        
        $data = [
            'nombre' => ''
        ];

        $result = $validator->validate($data, ['nombre' => 'required']);
        
        assert($result === false, 'El validador debe fallar para campo requerido vacío');
        assert(count($validator->getErrors()) > 0, 'El validador debe tener errores');
    }

    public function testValidatorEmail()
    {
        $validator = new \App\Validation\Validator();
        
        $data = [
            'correo' => 'invalid-email'
        ];

        $result = $validator->validate($data, ['correo' => 'email']);
        
        assert($result === false, 'El validador debe fallar para correo inválido');
    }

    public function testValidatorValidEmail()
    {
        $validator = new \App\Validation\Validator();
        
        $data = [
            'correo' => 'test@example.com'
        ];

        $result = $validator->validate($data, ['correo' => 'email']);
        
        assert($result === true, 'El validador debe pasar para correo válido');
    }

    public function testValidatorDocument()
    {
        $validator = new \App\Validation\Validator();
        
        // Documento inválido (muy corto)
        $data = ['documento' => '123'];
        $result = $validator->validate($data, ['documento' => 'documento']);
        assert($result === false, 'El validador debe fallar para formato de documento inválido');

        // Documento válido
        $data = ['documento' => '12345678'];
        $result = $validator->validate($data, ['documento' => 'documento']);
        assert($result === true, 'El validador debe pasar para documento válido');
    }

    public function testValidatorPhone()
    {
        $validator = new \App\Validation\Validator();
        
        // Teléfono inválido (longitud incorrecta)
        $data = ['telefono' => '123456'];
        $result = $validator->validate($data, ['telefono' => 'phone']);
        assert($result === false, 'El validador debe fallar para formato de teléfono inválido');

        // Teléfono válido
        $data = ['telefono' => '3001234567'];
        $result = $validator->validate($data, ['telefono' => 'phone']);
        assert($result === true, 'El validador debe pasar para teléfono válido');
    }

    public function testSanitizerString()
    {
        $input = '<script>alert("XSS")</script>';
        $sanitized = \App\Validation\Sanitizer::sanitizeString($input);
        
        assert(strpos($sanitized, '<script>') === false, 'El desinfectante debe eliminar etiquetas de script');
        assert(strpos($sanitized, '&lt;script&gt;') !== false, 'El desinfectante debe codificar entidades HTML');
    }

    public function testSanitizerEmail()
    {
        $input = 'test@example.com';
        $sanitized = \App\Validation\Sanitizer::sanitizeEmail($input);
        
        assert($sanitized === 'test@example.com', 'El desinfectante debe preservar correo válido');
    }

    public function testJwtHandler()
    {
        $jwt = new \App\Utils\JwtHandler();
        
        $data = [
            'id' => 1,
            'username' => 'admin'
        ];

        // Generar token
        $token = $jwt->generateToken($data);
        assert(!empty($token), 'El manejador JWT debe generar token');

        // Verificar token
        $payload = $jwt->verifyToken($token);
        assert($payload !== false, 'El manejador JWT debe verificar token válido');
        assert($payload['id'] === 1, 'La carga JWT debe contener ID de usuario');
        assert($payload['username'] === 'admin', 'La carga JWT debe contener nombre de usuario');
    }

    public function testJwtHandlerExpiredToken()
    {
        $jwt = new \App\Utils\JwtHandler();
        
        // Esta prueba necesitaría simular tiempo o crear un token realmente expirado
        // Por ahora, solo probamos que token inválido retorna falso
        $invalidToken = 'invalid.token.here';
        $payload = $jwt->verifyToken($invalidToken);
        
        assert($payload === false, 'El manejador JWT debe rechazar token inválido');
    }

    public function runAllTests()
    {
        echo "Ejecutando Pruebas de Validador...\n";
        
        try {
            $this->testValidatorRequired();
            echo "✓ testValidatorRequired passed\n";
        } catch (\AssertionError $e) {
            echo "✗ testValidatorRequired failed: " . $e->getMessage() . "\n";
        }

        try {
            $this->testValidatorEmail();
            echo "✓ testValidatorEmail passed\n";
        } catch (\AssertionError $e) {
            echo "✗ testValidatorEmail failed: " . $e->getMessage() . "\n";
        }

        try {
            $this->testValidatorValidEmail();
            echo "✓ testValidatorValidEmail passed\n";
        } catch (\AssertionError $e) {
            echo "✗ testValidatorValidEmail failed: " . $e->getMessage() . "\n";
        }

        try {
            $this->testValidatorDocument();
            echo "✓ testValidatorDocument passed\n";
        } catch (\AssertionError $e) {
            echo "✗ testValidatorDocument failed: " . $e->getMessage() . "\n";
        }

        try {
            $this->testValidatorPhone();
            echo "✓ testValidatorPhone passed\n";
        } catch (\AssertionError $e) {
            echo "✗ testValidatorPhone failed: " . $e->getMessage() . "\n";
        }

        try {
            $this->testSanitizerString();
            echo "✓ testSanitizerString passed\n";
        } catch (\AssertionError $e) {
            echo "✗ testSanitizerString failed: " . $e->getMessage() . "\n";
        }

        try {
            $this->testSanitizerEmail();
            echo "✓ testSanitizerEmail passed\n";
        } catch (\AssertionError $e) {
            echo "✗ testSanitizerEmail failed: " . $e->getMessage() . "\n";
        }

        try {
            $this->testJwtHandler();
            echo "✓ testJwtHandler passed\n";
        } catch (\AssertionError $e) {
            echo "✗ testJwtHandler failed: " . $e->getMessage() . "\n";
        }

        try {
            $this->testJwtHandlerExpiredToken();
            echo "✓ testJwtHandlerExpiredToken passed\n";
        } catch (\AssertionError $e) {
            echo "✗ testJwtHandlerExpiredToken failed: " . $e->getMessage() . "\n";
        }
    }
}
