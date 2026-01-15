<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class PatientControllerTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
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

    /**
     * @test
     */
    public function validatorRequired()
    {
        $validator = new \App\Validation\Validator();
        
        $data = [
            'nombre' => ''
        ];

        $result = $validator->validate($data, ['nombre' => 'required']);
        
        $this->assertFalse($result, 'El validador debe fallar para campo requerido vacío');
        $this->assertNotEmpty($validator->getErrors(), 'El validador debe tener errores');
    }

    /**
     * @test
     */
    public function validatorEmail()
    {
        $validator = new \App\Validation\Validator();
        
        $data = [
            'correo' => 'invalid-email'
        ];

        $result = $validator->validate($data, ['correo' => 'email']);
        
        $this->assertFalse($result, 'El validador debe fallar para correo inválido');
    }

    /**
     * @test
     */
    public function validatorValidEmail()
    {
        $validator = new \App\Validation\Validator();
        
        $data = [
            'correo' => 'test@example.com'
        ];

        $result = $validator->validate($data, ['correo' => 'email']);
        
        $this->assertTrue($result, 'El validador debe pasar para correo válido');
    }

    /**
     * @test
     */
    public function validatorDocument()
    {
        $validator = new \App\Validation\Validator();
        
        // Documento inválido (muy corto)
        $data = ['documento' => '123'];
        $result = $validator->validate($data, ['documento' => 'documento']);
        $this->assertFalse($result, 'El validador debe fallar para formato de documento inválido');

        // Documento válido
        $data = ['documento' => '12345678'];
        $result = $validator->validate($data, ['documento' => 'documento']);
        $this->assertTrue($result, 'El validador debe pasar para documento válido');
    }

    /**
     * @test
     */
    public function validatorPhone()
    {
        $validator = new \App\Validation\Validator();
        
        // Teléfono inválido (longitud incorrecta)
        $data = ['telefono' => '123456'];
        $result = $validator->validate($data, ['telefono' => 'phone']);
        $this->assertFalse($result, 'El validador debe fallar para formato de teléfono inválido');

        // Teléfono válido
        $data = ['telefono' => '3001234567'];
        $result = $validator->validate($data, ['telefono' => 'phone']);
        $this->assertTrue($result, 'El validador debe pasar para teléfono válido');
    }

    /**
     * @test
     */
    public function sanitizerString()
    {
        $input = '<script>alert("XSS")</script>';
        $sanitized = \App\Validation\Sanitizer::sanitizeString($input);
        
        $this->assertStringNotContainsString('<script>', $sanitized, 'El desinfectante debe eliminar etiquetas de script');
        $this->assertStringContainsString('&lt;script&gt;', $sanitized, 'El desinfectante debe codificar entidades HTML');
    }

    /**
     * @test
     */
    public function sanitizerEmail()
    {
        $input = 'test@example.com';
        $sanitized = \App\Validation\Sanitizer::sanitizeEmail($input);
        
        $this->assertEquals('test@example.com', $sanitized, 'El desinfectante debe preservar correo válido');
    }

    /**
     * @test
     */
    public function jwtHandler()
    {
        $jwt = new \App\Utils\JwtHandler();
        
        $data = [
            'id' => 1,
            'username' => 'admin'
        ];

        // Generar token
        $token = $jwt->generateToken($data);
        $this->assertNotEmpty($token, 'El manejador JWT debe generar token');

        // Verificar token
        $payload = $jwt->verifyToken($token);
        $this->assertNotFalse($payload, 'El manejador JWT debe verificar token válido');
        $this->assertEquals(1, $payload['id'], 'La carga JWT debe contener ID de usuario');
        $this->assertEquals('admin', $payload['username'], 'La carga JWT debe contener nombre de usuario');
    }

    /**
     * @test
     */
    public function jwtHandlerExpiredToken()
    {
        $jwt = new \App\Utils\JwtHandler();
        
        // Esta prueba necesitaría simular tiempo o crear un token realmente expirado
        // Por ahora, solo probamos que token inválido retorna falso
        $invalidToken = 'invalid.token.here';
        $payload = $jwt->verifyToken($invalidToken);
        
        $this->assertFalse($payload, 'El manejador JWT debe rechazar token inválido');
    }
}
