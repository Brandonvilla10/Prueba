<?php

namespace App\Validation;

class Validator
{
    private $errors = [];

    public function validate($data, $rules)
    {
        foreach ($rules as $field => $fieldRules) {
            $rules_array = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            
            foreach ($rules_array as $rule) {
                $this->applyRule($field, $data[$field] ?? null, $rule);
            }
        }
        
        return empty($this->errors);
    }

    private function applyRule($field, $value, $rule)
    {
        $rule_parts = explode(':', $rule);
        $rule_name = $rule_parts[0];
        $rule_param = $rule_parts[1] ?? null;

        switch ($rule_name) {
            case 'required':
                if (empty($value)) {
                    $this->errors[$field][] = "$field es requerido";
                }
                break;
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "$field debe ser un correo válido";
                }
                break;
            case 'min':
                if (!empty($value) && strlen($value) < (int)$rule_param) {
                    $this->errors[$field][] = "$field debe tener al menos $rule_param caracteres";
                }
                break;
            case 'max':
                if (!empty($value) && strlen($value) > (int)$rule_param) {
                    $this->errors[$field][] = "$field no puede exceder $rule_param caracteres";
                }
                break;
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$field][] = "$field debe ser numérico";
                }
                break;
            case 'phone':
                if (!empty($value) && !preg_match('/^[0-9]{10}$/', $value)) {
                    $this->errors[$field][] = "$field debe ser un teléfono válido (10 dígitos)";
                }
                break;
            case 'documento':
                if (!empty($value) && !preg_match('/^[0-9]{7,11}$/', $value)) {
                    $this->errors[$field][] = "$field debe ser un documento válido";
                }
                break;
            case 'date':
                if (!empty($value) && !$this->isValidDate($value)) {
                    $this->errors[$field][] = "$field debe ser una fecha válida (YYYY-MM-DD)";
                }
                break;
        }
    }

    private function isValidDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function getFirstError()
    {
        foreach ($this->errors as $field => $messages) {
            return $messages[0] ?? null;
        }
        return null;
    }
}
