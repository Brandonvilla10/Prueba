<?php

namespace App\Controllers;

use App\Models\User;
use App\Utils\JwtHandler;
use App\Validation\Validator;
use App\Validation\Sanitizer;
use App\Middleware\AuthMiddleware;

class UserController
{
    private $userModel;
    private $jwtHandler;
    private $validator;
    private $auth;

    public function __construct()
    {
        $this->userModel = new User();
        $this->jwtHandler = new JwtHandler();
        $this->validator = new Validator();
        $this->auth = new AuthMiddleware();
    }

    /**
     * Iniciar sesión
     * POST /api/auth/login
     */
    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validar entrada
        $rules = [
            'username' => 'required|min:3',
            'password' => 'required|min:8'
        ];

        if (!$this->validator->validate($data, $rules)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $this->validator->getErrors()
            ]);
            return;
        }

        $user = $this->userModel->getByUsername($data['username']);

        if (!$user || !$this->userModel->verifyPassword($data['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos'
            ]);
            return;
        }

        if (!$user['is_active']) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Usuario desactivado'
            ]);
            return;
        }

        // Generar token JWT
        $token = $this->jwtHandler->generateToken([
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Login exitoso',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    }

    /**
     * Verificar token
     * POST /api/auth/verify
     */
    public function verifyToken()
    {
        $user = $this->auth->authenticate();

        if (!$user) {
            http_response_code(401);
            echo json_encode(AuthMiddleware::sendUnauthorized());
            return;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Obtener información del usuario actual
     * GET /api/auth/user
     */
    public function getCurrentUser()
    {
        $user = $this->auth->authenticate();

        if (!$user) {
            http_response_code(401);
            echo json_encode(AuthMiddleware::sendUnauthorized());
            return;
        }

        $userData = $this->userModel->getById($user['id']);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $userData
        ]);
    }

    /**
     * Cerrar sesión
     * POST /api/auth/logout
     */
    public function logout()
    {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Logout exitoso'
        ]);
    }
}
