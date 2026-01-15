<?php

namespace App\Utils;

use Exception;

class JwtHandler
{
    private $secret = 'your-secret-key-change-this-in-production';
    private $algorithm = 'HS256';
    private $expiration = 3600; // 1 hour

    public function __construct()
    {
        $this->secret = getenv('JWT_SECRET') ?: 'your-secret-key-change-this-in-production';
        $this->expiration = (int)(getenv('JWT_EXPIRATION') ?: 3600);
    }

    /**
     * Generar token JWT
     */
    public function generateToken($data)
    {
        $header = [
            'alg' => $this->algorithm,
            'typ' => 'JWT'
        ];

        $payload = array_merge($data, [
            'iat' => time(),
            'exp' => time() + $this->expiration
        ]);

        $header_encoded = $this->base64UrlEncode(json_encode($header));
        $payload_encoded = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", $this->secret, true);
        $signature_encoded = $this->base64UrlEncode($signature);

        return "$header_encoded.$payload_encoded.$signature_encoded";
    }

    /**
     * Verificar token JWT
     */
    public function verifyToken($token)
    {
        try {
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                throw new Exception('Invalid token format');
            }

            list($header_encoded, $payload_encoded, $signature_encoded) = $parts;

            // Verificar firma
            $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", $this->secret, true);
            $signature_encoded_expected = $this->base64UrlEncode($signature);

            if (!hash_equals($signature_encoded, $signature_encoded_expected)) {
                throw new Exception('Invalid token signature');
            }

            // Decodificar carga útil
            $payload = json_decode($this->base64UrlDecode($payload_encoded), true);

            // Verificar expiración
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                throw new Exception('Token expired');
            }

            return $payload;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Decodificar token sin verificación
     */
    public function decodeToken($token)
    {
        try {
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode($this->base64UrlDecode($parts[1]), true);
            return $payload;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Codificación Base64 URL
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodificación Base64 URL
     */
    private function base64UrlDecode($data)
    {
        $data = strtr($data, '-_', '+/');
        $data = str_pad($data, strlen($data) % 4, '=', STR_PAD_RIGHT);
        return base64_decode($data);
    }
}
