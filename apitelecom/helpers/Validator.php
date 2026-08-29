<?php

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) === 0;
    }
}

class Validator
{
    public static function sanitizeString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return htmlspecialchars(strip_tags(trim((string) $value)), ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeUrl($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return filter_var($value, FILTER_VALIDATE_URL) ? $value : null;
    }

    public static function sanitizeArray(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $result[$key] = self::sanitizeString($value);
                continue;
            }

            if (is_array($value)) {
                $result[$key] = self::sanitizeArray($value);
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    public static function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleSet) {
            $value = array_key_exists($field, $data) ? $data[$field] : null;
            $rulesArray = explode('|', $ruleSet);
            $nullable = in_array('nullable', $rulesArray, true);
            $required = in_array('required', $rulesArray, true);

            if ($required && ($value === null || $value === '')) {
                $errors[$field] = 'El campo ' . $field . ' es obligatorio.';
                continue;
            }

            if ($value === null || $value === '') {
                if ($nullable || !$required) {
                    continue;
                }
            }

            foreach ($rulesArray as $rule) {
                if ($rule === 'nullable' || $rule === 'required') {
                    continue;
                }

                if ($rule === 'integer' && !filter_var($value, FILTER_VALIDATE_INT) && $value !== 0 && $value !== '0') {
                    $errors[$field] = 'El campo ' . $field . ' debe ser un número entero.';
                    break;
                }

                if ($rule === 'numeric' && !is_numeric($value)) {
                    $errors[$field] = 'El campo ' . $field . ' debe ser numérico.';
                    break;
                }

                if ($rule === 'boolean' && !self::isBoolean($value)) {
                    $errors[$field] = 'El campo ' . $field . ' debe ser 0 o 1.';
                    break;
                }

                if ($rule === 'url' && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $errors[$field] = 'El campo ' . $field . ' debe contener una URL válida.';
                    break;
                }

                if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'El campo ' . $field . ' debe contener un correo electrónico válido.';
                    break;
                }

                if ($rule === 'date' && !self::isValidDate($value, 'Y-m-d')) {
                    $errors[$field] = 'El campo ' . $field . ' debe tener formato YYYY-MM-DD.';
                    break;
                }

                if ($rule === 'array' && !is_array($value)) {
                    $errors[$field] = 'El campo ' . $field . ' debe ser un arreglo.';
                    break;
                }

                if ($rule === 'object' && !is_array($value) && !is_object($value)) {
                    $errors[$field] = 'El campo ' . $field . ' debe ser un objeto.';
                    break;
                }

                if (str_starts_with($rule, 'max:')) {
                    [$_, $max] = explode(':', $rule, 2);
                    if (is_string($value) && mb_strlen($value) > (int) $max) {
                        $errors[$field] = 'El campo ' . $field . ' no debe superar los ' . $max . ' caracteres.';
                        break;
                    }
                }

                if (str_starts_with($rule, 'min:')) {
                    [$_, $min] = explode(':', $rule, 2);
                    if (is_string($value) && mb_strlen($value) < (int) $min) {
                        $errors[$field] = 'El campo ' . $field . ' debe tener al menos ' . $min . ' caracteres.';
                        break;
                    }
                }

                if (str_starts_with($rule, 'in:')) {
                    [$_, $options] = explode(':', $rule, 2);
                    $allowed = explode(',', $options);
                    if (!in_array($value, $allowed, true)) {
                        $errors[$field] = 'El campo ' . $field . ' tiene un valor inválido.';
                        break;
                    }
                }
            }
        }

        return $errors;
    }

    private static function isBoolean($value): bool
    {
        if (is_bool($value)) {
            return true;
        }

        if (is_int($value) && ($value === 0 || $value === 1)) {
            return true;
        }

        if (is_string($value)) {
            $normalized = strtolower($value);
            return in_array($normalized, ['0', '1', 'true', 'false'], true);
        }

        return false;
    }

    private static function isValidDate($value, string $format): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $dateTime = DateTime::createFromFormat($format, $value);
        return $dateTime && $dateTime->format($format) === $value;
    }
}
