<?php

namespace App\Models;

use App\Config\Database;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener usuario por nombre de usuario
     */
    public function getByUsername($username)
    {
        $query = "SELECT * FROM users WHERE username = :username";
        return $this->db->fetch($query, [':username' => $username]);
    }

    /**
     * Obtener usuario por ID
     */
    public function getById($id)
    {
        $query = "SELECT id, username, email, role, is_active FROM users WHERE id = :id";
        return $this->db->fetch($query, [':id' => $id]);
    }

    /**
     * Crear nuevo usuario
     */
    public function create($data)
    {
        $query = "INSERT INTO users (username, email, password, role, is_active) 
                VALUES (:username, :email, :password, :role, :is_active)";

        $params = [
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_BCRYPT),
            ':role' => $data['role'] ?? 'user',
            ':is_active' => $data['is_active'] ?? 1
        ];

        $this->db->execute($query, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Verificar contraseña
     */
    public function verifyPassword($plainPassword, $hashedPassword)
    {
        return password_verify($plainPassword, $hashedPassword);
    }
}
