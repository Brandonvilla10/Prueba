<?php

namespace App\Config;

class Router
{
    private $routes = [];
    private $method;
    private $uri;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }

    public function put($path, $callback)
    {
        $this->routes['PUT'][$path] = $callback;
    }

    public function delete($path, $callback)
    {
        $this->routes['DELETE'][$path] = $callback;
    }

    // Procesar una ruta
    public function dispatch()
    {
        // Remover ruta base
        $basePath = '/Prueba/backend/api';
        if (strpos($this->uri, $basePath) === 0) {
            $path = substr($this->uri, strlen($basePath));
        } else {
            $path = $this->uri;
        }

        // Remover barra diagonal al final
        $path = rtrim($path, '/');

        // Buscar coincidencia exacta
        if (isset($this->routes[$this->method][$path])) {
            return call_user_func($this->routes[$this->method][$path]);
        }

        // Buscar coincidencia de patrón con parámetros
        foreach ($this->routes[$this->method] ?? [] as $route => $callback) {
            $pattern = $this->convertRouteToPattern($route);
            if (preg_match($pattern, $path, $matches)) {
                // Extraer solo los valores numéricos (no los nombres de grupo)
                $numericMatches = [];
                foreach ($matches as $key => $value) {
                    if (is_numeric($key) && $key > 0) {
                        $numericMatches[] = $value;
                    }
                }
                return call_user_func_array($callback, $numericMatches);
            }
        }

        // No encontrado
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Ruta no encontrada'
        ]);
    }

    // Convertir patrón de ruta a expresión regular
    private function convertRouteToPattern($route)
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<\1>[^/]+)', $route);
        return '#^' . $pattern . '$#';
    }
}
