<?php

namespace App\Middleware;

use App\Utils\JwtHandler;

class AuthMiddleware
{
    private $jwtHandler;

    public function __construct()
    {
        $this->jwtHandler = new JwtHandler();
    }

    /**
     * Verificar si la solicitud tiene un token JWT válido
     */
    public function authenticate()
    {
        $authorization = null;

        // 1. Intentar obtener el header Authorization de diferentes formas
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        }

        // 2. Si no se encontró con getallheaders(), intentar con $_SERVER
        if (!$authorization) {
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $authorization = $_SERVER['HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authorization = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_BEARER'])) {
                $authorization = 'Bearer ' . $_SERVER['PHP_AUTH_BEARER'];
            }
        }

        // 3. Si aún no hay token, intentar desde query parameter (fallback para FastCGI)
        $token = null;
        if ($authorization) {
            $token = trim(preg_replace('/Bearer\s+/i', '', $authorization));
        } elseif (isset($_GET['token'])) {
            $token = $_GET['token'];
        } else {
            // 4. Intentar obtener del body (para POST/PUT/DELETE)
            $input = json_decode(file_get_contents('php://input'), true);
            if (is_array($input) && isset($input['_token'])) {
                $token = $input['_token'];
            }
        }

        if (empty($token)) {
            return false;
        }

        $payload = $this->jwtHandler->verifyToken($token);

        if (!$payload) {
            return false;
        }

        return $payload;
    }

    /**
     * Enviar respuesta no autorizada
     */
    public static function sendUnauthorized()
    {
        http_response_code(401);
        return [
            'success' => false,
            'message' => 'No autorizado. Token requerido.'
        ];
    }

    /**
     * Enviar respuesta prohibida
     */
    public static function sendForbidden()
    {
        http_response_code(403);
        return [
            'success' => false,
            'message' => 'Acceso denegado.'
        ];
    }
}
