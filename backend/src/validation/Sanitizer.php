<?php

namespace App\Validation;

class Sanitizer
{
    /**
     * Desinfectar entrada de texto - eliminar ataques XSS
     */
    public static function sanitizeString($input)
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Desinfectar correo electrónico
     */
    public static function sanitizeEmail($input)
    {
        return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Desinfectar entrada numérica
     */
    public static function sanitizeNumber($input)
    {
        return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Desinfectar nombre de archivo
     */
    public static function sanitizeFileName($filename)
    {
        $filename = trim($filename);
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        return $filename;
    }

    /**
     * Desinfectar SQL - prevenir inyecciones (usar consultas preparadas en su lugar)
     */
    public static function sanitizeSql($input)
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $input);
    }

    /**
     * Desinfectar matriz recursivamente
     */
    public static function sanitizeArray($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::sanitizeArray($value);
            } else {
                $data[$key] = self::sanitizeString($value);
            }
        }
        return $data;
    }
}
