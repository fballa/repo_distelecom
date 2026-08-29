<?php

class Response
{
    public static function json(array $payload, int $status = 200): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        http_response_code($status);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success(string $message = 'Operación realizada correctamente.', array $data = [], int $status = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function error(string $message = 'Ha ocurrido un error.', int $status = 500): void
    {
        self::json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
