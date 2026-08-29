<?php

class Database
{
    private static string $host = 'localhost';
    private static string $name = '';
    private static string $user = '';
    private static string $password = '';
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = getenv('DB_HOST') ?: self::$host;
        $name = getenv('DB_NAME') ?: self::$name;
        $user = getenv('DB_USER') ?: self::$user;
        $password = getenv('DB_PASSWORD') ?: self::$password;
        $charset = 'utf8mb4';

        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $host, $name, $charset);

        try {
            self::$connection = new PDO(
                $dsn,
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw $e;
        }

        return self::$connection;
    }
}
