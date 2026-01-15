<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Controllers\UserController;
use App\Models\User;

class UserControllerTest extends TestCase
{
    private $userController;
    private $userModel;
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

        $this->createTestTables();
        $this->userController = new UserController();
        $this->userModel = new User();
    }

    private function createTestTables()
    {
        // Eliminar tabla existente
        $this->pdo->exec('DROP TABLE IF EXISTS users');

        // Crear tabla de usuarios
        $this->pdo->exec('
            CREATE TABLE users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(20) DEFAULT "user",
                is_active BOOLEAN DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ');

        // Insertar usuario de prueba
        $hashedPassword = password_hash('Test123456', PASSWORD_BCRYPT);
        $this->pdo->exec("
            INSERT INTO users (username, email, password, role, is_active) 
            VALUES ('testuser', 'test@example.com', '$hashedPassword', 'user', 1)
        ");
    }

    /**
     * @test
     */
    public function userCreationWithValidData()
    {
        $data = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'SecurePass123',
            'role' => 'user',
            'is_active' => 1
        ];

        $userId = $this->userModel->create($data);

        $this->assertIsInt($userId);
        $this->assertGreaterThan(0, $userId);

        $user = $this->userModel->getById($userId);
        $this->assertNotNull($user);
        $this->assertEquals('newuser', $user['username']);
        $this->assertEquals('newuser@example.com', $user['email']);
    }

    /**
     * @test
     */
    public function getUserByUsername()
    {
        $user = $this->userModel->getByUsername('testuser');

        $this->assertNotNull($user);
        $this->assertEquals('testuser', $user['username']);
        $this->assertEquals('test@example.com', $user['email']);
    }

    /**
     * @test
     */
    public function getUserByUsernameNotFound()
    {
        $user = $this->userModel->getByUsername('nonexistent');

        $this->assertNull($user);
    }

    /**
     * @test
     */
    public function getUserById()
    {
        // Obtener el usuario de prueba (debería tener ID 1)
        $user = $this->userModel->getById(1);

        $this->assertNotNull($user);
        $this->assertEquals('testuser', $user['username']);
    }

    /**
     * @test
     */
    public function getUserByIdNotFound()
    {
        $user = $this->userModel->getById(99999);

        $this->assertNull($user);
    }

    /**
     * @test
     */
    public function passwordVerificationCorrect()
    {
        $user = $this->userModel->getByUsername('testuser');
        $isValid = $this->userModel->verifyPassword('Test123456', $user['password']);

        $this->assertTrue($isValid);
    }

    /**
     * @test
     */
    public function passwordVerificationIncorrect()
    {
        $user = $this->userModel->getByUsername('testuser');
        $isValid = $this->userModel->verifyPassword('WrongPassword', $user['password']);

        $this->assertFalse($isValid);
    }

    /**
     * @test
     */
    public function passwordHashingIsSecure()
    {
        $plainPassword = 'MySecurePassword123';
        $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);

        // Verificar que el hash es diferente de la contraseña en texto plano
        $this->assertNotEquals($plainPassword, $hashedPassword);

        // Verificar que password_verify funciona
        $this->assertTrue(password_verify($plainPassword, $hashedPassword));
    }

    /**
     * @test
     */
    public function sqlInjectionProtection()
    {
        // Intentar inyección SQL en el nombre de usuario
        $maliciousInput = "' OR '1'='1";
        $user = $this->userModel->getByUsername($maliciousInput);

        // No debería encontrar un usuario
        $this->assertNull($user);
    }

    /**
     * @test
     */
    public function userWithSpecialCharactersInUsername()
    {
        $data = [
            'username' => 'user_test-123',
            'email' => 'special@example.com',
            'password' => 'SecurePass123',
            'role' => 'user'
        ];

        $userId = $this->userModel->create($data);
        $this->assertGreaterThan(0, $userId);

        $user = $this->userModel->getByUsername('user_test-123');
        $this->assertNotNull($user);
        $this->assertEquals('user_test-123', $user['username']);
    }

    /**
     * @test
     */
    public function userWithSpecialCharactersInEmail()
    {
        $data = [
            'username' => 'user_special_email',
            'email' => 'user+test@example.co.uk',
            'password' => 'SecurePass123',
            'role' => 'user'
        ];

        $userId = $this->userModel->create($data);
        $this->assertGreaterThan(0, $userId);

        $user = $this->userModel->getById($userId);
        $this->assertEquals('user+test@example.co.uk', $user['email']);
    }

    /**
     * @test
     */
    public function userDefaultRoleIsUser()
    {
        $data = [
            'username' => 'default_role_user',
            'email' => 'defaultrole@example.com',
            'password' => 'SecurePass123'
            // No se especifica role
        ];

        $userId = $this->userModel->create($data);
        $user = $this->userModel->getById($userId);

        $this->assertEquals('user', $user['role']);
    }

    /**
     * @test
     */
    public function userDefaultActiveIsTrue()
    {
        $data = [
            'username' => 'default_active_user',
            'email' => 'defaultactive@example.com',
            'password' => 'SecurePass123'
            // No se especifica is_active
        ];

        $userId = $this->userModel->create($data);
        $user = $this->userModel->getById($userId);

        $this->assertEquals(1, $user['is_active']);
    }

    /**
     * @test
     */
    public function userCanHaveAdminRole()
    {
        $data = [
            'username' => 'admin_user',
            'email' => 'admin@example.com',
            'password' => 'SecurePass123',
            'role' => 'admin',
            'is_active' => 1
        ];

        $userId = $this->userModel->create($data);
        $user = $this->userModel->getById($userId);

        $this->assertEquals('admin', $user['role']);
    }

    /**
     * @test
     */
    public function userCanBeDeactivated()
    {
        $data = [
            'username' => 'inactive_user',
            'email' => 'inactive@example.com',
            'password' => 'SecurePass123',
            'is_active' => 0
        ];

        $userId = $this->userModel->create($data);
        $user = $this->userModel->getById($userId);

        $this->assertEquals(0, $user['is_active']);
    }
}
