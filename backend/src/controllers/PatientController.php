<?php

namespace App\Controllers;

use App\Models\Patient;
use App\Models\User;
use App\Utils\JwtHandler;
use App\Validation\Validator;
use App\Validation\Sanitizer;
use App\Middleware\AuthMiddleware;

class PatientController
{
    private $patientModel;
    private $validator;
    private $auth;

    public function __construct()
    {
        $this->patientModel = new Patient();
        $this->validator = new Validator();
        $this->auth = new AuthMiddleware();
    }

    /**
     * Obtener todos los pacientes con paginación
     * GET /api/pacientes?page=1&limit=10&search=
     */
    public function getAll()
    {
        $user = $this->auth->authenticate();
        if (!$user) {
            http_response_code(401);
            echo json_encode(AuthMiddleware::sendUnauthorized());
            return;
        }

        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 10);
        $search = Sanitizer::sanitizeString($_GET['search'] ?? '');

        $result = $this->patientModel->getAllPaginated($page, $limit, $search);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $result['data'],
            'pagination' => [
                'page' => $result['page'],
                'limit' => $result['limit'],
                'total' => $result['total'],
                'pages' => $result['pages']
            ]
        ]);
    }

    /**
     * Obtener paciente por ID
     * GET /api/pacientes/:id
     */
    public function getById($id)
    {
        $user = $this->auth->authenticate();
        if (!$user) {
            http_response_code(401);
            echo json_encode(AuthMiddleware::sendUnauthorized());
            return;
        }

        $patient = $this->patientModel->getById((int)$id);

        if (!$patient) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Paciente no encontrado'
            ]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $patient
        ]);
    }

    /**
     * Crear nuevo paciente
     * POST /api/pacientes
     */
    public function create()
    {
        $user = $this->auth->authenticate();
        if (!$user) {
            http_response_code(401);
            echo json_encode(AuthMiddleware::sendUnauthorized());
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Validate input
        $rules = [
            'tipo_documento_id' => 'required|numeric',
            'numero_documento' => 'required|documento',
            'nombre1' => 'required|min:2|max:100',
            'apellido1' => 'required|min:2|max:100',
            'genero_id' => 'required|numeric',
            'departamento_id' => 'required|numeric',
            'municipio_id' => 'required|numeric',
            'correo' => 'email|max:100',
            'telefono' => 'phone'
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

        // Verificar si el documento ya existe
        if ($this->patientModel->documentExists($data['numero_documento'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'El número de documento ya existe'
            ]);
            return;
        }

        // Sanitize data
        $data = Sanitizer::sanitizeArray($data);

        try {
            $patientId = $this->patientModel->create($data);

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Paciente creado correctamente',
                'data' => ['id' => $patientId]
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al crear paciente',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Actualizar paciente
     * PUT /api/patients/:id
     */
    public function update($id)
    {
        $user = $this->auth->authenticate();
        if (!$user) {
            http_response_code(401);
            echo json_encode(AuthMiddleware::sendUnauthorized());
            return;
        }

        $patient = $this->patientModel->getById((int)$id);
        if (!$patient) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Paciente no encontrado'
            ]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Validar entrada
        $rules = [
            'tipo_documento_id' => 'required|numeric',
            'numero_documento' => 'required|documento',
            'nombre1' => 'required|min:2|max:100',
            'apellido1' => 'required|min:2|max:100',
            'genero_id' => 'required|numeric',
            'departamento_id' => 'required|numeric',
            'municipio_id' => 'required|numeric',
            'correo' => 'email|max:100',
            'telefono' => 'phone'
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

        // Verificar si el documento ya existe (excluyendo paciente actual)
        if ($this->patientModel->documentExists($data['numero_documento'], (int)$id)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'El número de documento ya existe'
            ]);
            return;
        }

        // Desinfectar datos
        $data = Sanitizer::sanitizeArray($data);

        try {
            $this->patientModel->update((int)$id, $data);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Paciente actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar paciente',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Eliminar paciente
     * DELETE /api/patients/:id
     */
    public function delete($id)
    {
        $user = $this->auth->authenticate();
        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(AuthMiddleware::sendForbidden());
            return;
        }

        $patient = $this->patientModel->getById((int)$id);
        if (!$patient) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Paciente no encontrado'
            ]);
            return;
        }

        try {
            $this->patientModel->delete((int)$id);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Paciente eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar paciente',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener datos de búsqueda (tipos de documento, géneros, departamentos, municipios)
     * GET /api/patients/lookup/:type
     */
    public function getLookupData($type)
    {
        $user = $this->auth->authenticate();
        if (!$user) {
            http_response_code(401);
            echo json_encode(AuthMiddleware::sendUnauthorized());
            return;
        }

        $data = null;

        switch ($type) {
            case 'document-types':
                $data = $this->patientModel->getDocumentTypes();
                break;
            case 'genders':
                $data = $this->patientModel->getGenders();
                break;
            case 'departments':
                $data = $this->patientModel->getDepartments();
                break;
            case 'municipalities':
                $departamento_id = (int)($_GET['departamento_id'] ?? 0);
                if (!$departamento_id) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'departamento_id es requerido'
                    ]);
                    return;
                }
                $data = $this->patientModel->getMunicipalitiesByDepartment($departamento_id);
                break;
            default:
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Tipo de datos inválido'
                ]);
                return;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }
}
