<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    // Instancia única de la clase (patrón singleton)
    private static $instance = null;
    private $connection = null;
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset = 'utf8mb4';

    // Constructor privado - no se puede crear instancias directamente
    private function __construct()
    {
        // Cargar configuración de variables de entorno o usar valores por defecto
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'hospital_db';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';

        $this->connect();
    }

    // Conectar a la base de datos usando PDO
    private function connect()
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            
            $this->connection = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            throw new PDOException('Error de conexión a base de datos: ' . $e->getMessage());
        }
    }

    // Obtener la única instancia de la clase
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Obtener la conexión PDO
    public function getConnection()
    {
        return $this->connection;
    }

    // Ejecutar una consulta preparada
    public function execute($query, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new PDOException('Error al ejecutar consulta: ' . $e->getMessage());
        }
    }

    // Obtener todos los resultados
    public function fetchAll($query, $params = [])
    {
        $stmt = $this->execute($query, $params);
        return $stmt->fetchAll();
    }

    // Obtener un solo resultado
    public function fetch($query, $params = [])
    {
        $stmt = $this->execute($query, $params);
        return $stmt->fetch();
    }

    // Obtener el ID del último registro insertado
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    // Iniciar transacción
    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    // Confirmar transacción
    public function commit()
    {
        $this->connection->commit();
    }

    // Revertir transacción
    public function rollback()
    {
        $this->connection->rollback();
    }

    // Cerrar conexión
    public function closeConnection()
    {
        $this->connection = null;
    }
}
