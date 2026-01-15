<?php

namespace App\Models;

use App\Config\Database;

class Patient
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener todos los pacientes con paginación
     */
    public function getAllPaginated($page = 1, $limit = 10, $search = '')
    {
        $offset = ($page - 1) * $limit;
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        // Construir la consulta base
        $baseSelect = "SELECT p.id, p.numero_documento, p.nombre1, p.nombre2, 
                    p.apellido1, p.apellido2, p.correo, p.telefono,
                    p.estado, p.created_at,
                    td.nombre as tipo_documento,
                    g.nombre as genero,
                    d.nombre as departamento,
                    m.nombre as municipio
                FROM paciente p
                LEFT JOIN tipos_documento td ON p.tipo_documento_id = td.id
                LEFT JOIN genero g ON p.genero_id = g.id
                LEFT JOIN departamentos d ON p.departamento_id = d.id
                LEFT JOIN municipios m ON p.municipio_id = m.id";

        $whereClause = "";
        if (!empty($search)) {
            $whereClause = " WHERE (p.nombre1 LIKE ? OR p.nombre2 LIKE ? OR p.apellido1 LIKE ? OR p.apellido2 LIKE ? OR p.correo LIKE ? OR p.numero_documento LIKE ?)";
        }

        $query = $baseSelect . $whereClause . " ORDER BY p.created_at DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->getConnection()->prepare($query);
        
        if (!empty($search)) {
            $searchTerm = "%{$search}%";
            $stmt->bindParam(1, $searchTerm, \PDO::PARAM_STR);
            $stmt->bindParam(2, $searchTerm, \PDO::PARAM_STR);
            $stmt->bindParam(3, $searchTerm, \PDO::PARAM_STR);
            $stmt->bindParam(4, $searchTerm, \PDO::PARAM_STR);
            $stmt->bindParam(5, $searchTerm, \PDO::PARAM_STR);
            $stmt->bindParam(6, $searchTerm, \PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $patients = $stmt->fetchAll();

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM paciente p" . $whereClause;
        $countStmt = $this->db->getConnection()->prepare($countQuery);
        if (!empty($search)) {
            $searchTerm = "%{$search}%";
            $countStmt->bindParam(1, $searchTerm, \PDO::PARAM_STR);
            $countStmt->bindParam(2, $searchTerm, \PDO::PARAM_STR);
            $countStmt->bindParam(3, $searchTerm, \PDO::PARAM_STR);
            $countStmt->bindParam(4, $searchTerm, \PDO::PARAM_STR);
            $countStmt->bindParam(5, $searchTerm, \PDO::PARAM_STR);
            $countStmt->bindParam(6, $searchTerm, \PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = $countStmt->fetch()['total'];

        return [
            'data' => $patients,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }

    /**
     * Obtener paciente por ID
     */
    public function getById($id)
    {
        $query = "SELECT 
                    p.*, 
                    td.nombre as tipo_documento,
                    g.nombre as genero,
                    d.nombre as departamento,
                    m.nombre as municipio
                FROM paciente p
                LEFT JOIN tipos_documento td ON p.tipo_documento_id = td.id
                LEFT JOIN genero g ON p.genero_id = g.id
                LEFT JOIN departamentos d ON p.departamento_id = d.id
                LEFT JOIN municipios m ON p.municipio_id = m.id
                WHERE p.id = :id";

        return $this->db->fetch($query, [':id' => $id]);
    }

    /**
     * Crear nuevo paciente
     */
    public function create($data)
    {
        $query = "INSERT INTO paciente (
                    tipo_documento_id, numero_documento, nombre1, nombre2,
                    apellido1, apellido2, genero_id, departamento_id, 
                    municipio_id, correo, telefono, fecha_nacimiento, 
                    direccion, estado
                ) VALUES (
                    :tipo_documento_id, :numero_documento, :nombre1, :nombre2,
                    :apellido1, :apellido2, :genero_id, :departamento_id,
                    :municipio_id, :correo, :telefono, :fecha_nacimiento,
                    :direccion, :estado
                )";

        $params = [
            ':tipo_documento_id' => $data['tipo_documento_id'],
            ':numero_documento' => $data['numero_documento'],
            ':nombre1' => $data['nombre1'],
            ':nombre2' => $data['nombre2'] ?? null,
            ':apellido1' => $data['apellido1'],
            ':apellido2' => $data['apellido2'] ?? null,
            ':genero_id' => $data['genero_id'],
            ':departamento_id' => $data['departamento_id'],
            ':municipio_id' => $data['municipio_id'],
            ':correo' => $data['correo'] ?? null,
            ':telefono' => $data['telefono'] ?? null,
            ':fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            ':direccion' => $data['direccion'] ?? null,
            ':estado' => $data['estado'] ?? 'activo'
        ];

        $this->db->execute($query, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Actualizar paciente
     */
    public function update($id, $data)
    {
        $query = "UPDATE paciente SET 
                    tipo_documento_id = :tipo_documento_id,
                    numero_documento = :numero_documento,
                    nombre1 = :nombre1,
                    nombre2 = :nombre2,
                    apellido1 = :apellido1,
                    apellido2 = :apellido2,
                    genero_id = :genero_id,
                    departamento_id = :departamento_id,
                    municipio_id = :municipio_id,
                    correo = :correo,
                    telefono = :telefono,
                    fecha_nacimiento = :fecha_nacimiento,
                    direccion = :direccion,
                    estado = :estado
                WHERE id = :id";

        $params = [
            ':id' => $id,
            ':tipo_documento_id' => $data['tipo_documento_id'],
            ':numero_documento' => $data['numero_documento'],
            ':nombre1' => $data['nombre1'],
            ':nombre2' => $data['nombre2'] ?? null,
            ':apellido1' => $data['apellido1'],
            ':apellido2' => $data['apellido2'] ?? null,
            ':genero_id' => $data['genero_id'],
            ':departamento_id' => $data['departamento_id'],
            ':municipio_id' => $data['municipio_id'],
            ':correo' => $data['correo'] ?? null,
            ':telefono' => $data['telefono'] ?? null,
            ':fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            ':direccion' => $data['direccion'] ?? null,
            ':estado' => $data['estado'] ?? 'activo'
        ];

        $this->db->execute($query, $params);
        return true;
    }

    /**
     * Eliminar paciente
     */
    public function delete($id)
    {
        $query = "DELETE FROM paciente WHERE id = :id";
        $this->db->execute($query, [':id' => $id]);
        return true;
    }

    /**
     * Verificar si existe número de documento
     */
    public function documentExists($numero_documento, $excludeId = null)
    {
        $query = "SELECT id FROM paciente WHERE numero_documento = :numero_documento";
        $params = [':numero_documento' => $numero_documento];

        if ($excludeId) {
            $query .= " AND id != :id";
            $params[':id'] = $excludeId;
        }

        return $this->db->fetch($query, $params) !== false;
    }

    /**
     * Obtener todos los tipos de documento
     */
    public function getDocumentTypes()
    {
        $query = "SELECT id, nombre FROM tipos_documento ORDER BY nombre";
        return $this->db->fetchAll($query);
    }

    /**
     * Obtener todos los géneros
     */
    public function getGenders()
    {
        $query = "SELECT id, nombre FROM genero ORDER BY nombre";
        return $this->db->fetchAll($query);
    }

    /**
     * Obtener todos los departamentos
     */
    public function getDepartments()
    {
        $query = "SELECT id, nombre FROM departamentos ORDER BY nombre";
        return $this->db->fetchAll($query);
    }

    /**
     * Obtener municipios por departamento
     */
    public function getMunicipalitiesByDepartment($departamento_id)
    {
        $query = "SELECT id, nombre FROM municipios WHERE departamento_id = :departamento_id ORDER BY nombre";
        return $this->db->fetchAll($query, [':departamento_id' => $departamento_id]);
    }
}
