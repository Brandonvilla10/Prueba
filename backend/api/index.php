<?php

// Configurar manejo de errores
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Punto de entrada de API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Cargador automático
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Router;
use App\Controllers\PatientController;
use App\Controllers\UserController;

try {
    $router = new Router();

    // Rutas de autenticación
    $router->post('/auth/login', function() {
        (new UserController())->login();
    });

    $router->post('/auth/verify', function() {
        (new UserController())->verifyToken();
    });

    $router->get('/auth/user', function() {
        (new UserController())->getCurrentUser();
    });

    $router->post('/auth/logout', function() {
        (new UserController())->logout();
    });

    // Rutas de pacientes
    $router->get('/patients', function() {
        (new PatientController())->getAll();
    });

    $router->get('/patients/{id}', function($id) {
        (new PatientController())->getById($id);
    });

    $router->post('/patients', function() {
        (new PatientController())->create();
    });

    $router->put('/patients/{id}', function($id) {
        (new PatientController())->update($id);
    });

    $router->delete('/patients/{id}', function($id) {
        (new PatientController())->delete($id);
    });

    // Rutas de datos de búsqueda
    $router->get('/lookup/{type}', function($type) {
        (new PatientController())->getLookupData($type);
    });

    $router->dispatch();

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage(),
        'trace' => defined('DEBUG_MODE') ? $e->getTraceAsString() : null
    ]);
}
