<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/WhatsAppMensaje.php';

class WhatsAppConversacion
{
    public static function obtenerTodos(array $filtros, int $pagina, int $limite): array
    {
        $offset = ($pagina - 1) * $limite;
        $where = ['1 = 1'];
        $params = [];

        if (!empty($filtros['estado'])) {
            $where[] = 'estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['telefono'])) {
            $where[] = 'telefono LIKE :telefono';
            $params[':telefono'] = '%' . $filtros['telefono'] . '%';
        }

        if (!empty($filtros['nombre'])) {
            $where[] = 'nombre LIKE :nombre';
            $params[':nombre'] = '%' . $filtros['nombre'] . '%';
        }

        $whereSql = implode(' AND ', $where);

        try {
            $db = Database::getConnection();
            $countStmt = $db->prepare("SELECT COUNT(*) FROM whatsapp_conversaciones WHERE $whereSql");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT id, telefono, nombre, estado, created_at, updated_at, (SELECT COUNT(*) FROM whatsapp_mensajes WHERE conversacion_id = whatsapp_conversaciones.id) AS mensajes_count FROM whatsapp_conversaciones WHERE $whereSql ORDER BY created_at DESC LIMIT :limite OFFSET :offset";
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'total' => $total,
                'pagina' => $pagina,
                'limite' => $limite,
                'data' => $stmt->fetchAll(),
            ];
        } catch (PDOException $e) {
            error_log('WhatsAppConversacion::obtenerTodos error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, telefono, nombre, estado, created_at, updated_at FROM whatsapp_conversaciones WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $conversacion = $stmt->fetch();

            if (!$conversacion) {
                return null;
            }

            $conversacion['mensajes'] = WhatsAppMensaje::obtenerPorConversacion($id);
            return $conversacion;
        } catch (PDOException $e) {
            error_log('WhatsAppConversacion::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function obtenerPorTelefono(string $telefono): ?array
    {
        $telefono = trim($telefono);
        if ($telefono === '') {
            return null;
        }

        foreach (['phonenumber', 'telefono'] as $campo) {
            try {
                $db = Database::getConnection();
                $stmt = $db->prepare('SELECT id, telefono, nombre, estado, created_at, updated_at FROM whatsapp_conversaciones WHERE ' . $campo . ' = :telefono LIMIT 1');
                $stmt->execute([':telefono' => $telefono]);
                $conversacion = $stmt->fetch();

                if ($conversacion) {
                    return $conversacion;
                }
            } catch (PDOException $e) {
                $message = $e->getMessage();
                if (stripos($message, 'Unknown column') === false && stripos($message, 'doesn\'t exist') === false && stripos($message, '42S22') === false) {
                    error_log('WhatsAppConversacion::obtenerPorTelefono error: ' . $message);
                    return null;
                }
            }
        }

        return null;
    }

    public static function crearPorTelefono(array $data): ?int
    {
        $telefono = trim((string) ($data['phonenumber'] ?? $data['telefono'] ?? ''));
        if ($telefono === '') {
            return null;
        }

        $intentos = [
            [
                'sql' => 'INSERT INTO whatsapp_conversaciones (phonenumber, nombre, estado, created_at, updated_at) VALUES (:phonenumber, :nombre, :estado, NOW(), NOW())',
                'params' => [':phonenumber' => $telefono],
            ],
            [
                'sql' => 'INSERT INTO whatsapp_conversaciones (telefono, nombre, estado, created_at, updated_at) VALUES (:telefono, :nombre, :estado, NOW(), NOW())',
                'params' => [':telefono' => $telefono],
            ],
        ];

        foreach ($intentos as $intento) {
            try {
                $db = Database::getConnection();
                $stmt = $db->prepare($intento['sql']);
                $params = $intento['params'];
                $params[':nombre'] = $data['nombre'] ?? ('Cliente-' . ($data['phonenumber'] ?? $data['telefono'] ?? ''));
                $params[':estado'] = $data['estado'] ?? 'Abierta';
                $stmt->execute($params);
                return (int) $db->lastInsertId();
            } catch (PDOException $e) {
                $message = $e->getMessage();
                if (stripos($message, 'Unknown column') !== false || stripos($message, 'doesn\'t exist') !== false || stripos($message, '42S22') !== false) {
                    continue;
                }
                error_log('WhatsAppConversacion::crearPorTelefono error: ' . $message);
                return null;
            }
        }

        return null;
    }

    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO whatsapp_conversaciones (telefono, nombre, estado, created_at, updated_at) VALUES (:telefono, :nombre, :estado, NOW(), NOW())');
            $stmt->execute([
                ':telefono' => $data['telefono'],
                ':nombre' => $data['nombre'] ?? null,
                ':estado' => $data['estado'] ?? 'Abierta',
            ]);

            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('WhatsAppConversacion::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function actualizar(int $id, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $fields = [];
            $params = [':id' => $id];

            if (array_key_exists('nombre', $data)) {
                $fields[] = 'nombre = :nombre';
                $params[':nombre'] = $data['nombre'];
            }

            if (array_key_exists('estado', $data)) {
                $fields[] = 'estado = :estado';
                $params[':estado'] = $data['estado'];
            }

            if (empty($fields)) {
                return true;
            }

            $sql = 'UPDATE whatsapp_conversaciones SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
            $stmt = $db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('WhatsAppConversacion::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function cambiarEstado(int $id, string $estado): bool
    {
        if (!in_array($estado, ['Abierta', 'Cerrada'], true)) {
            return false;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE whatsapp_conversaciones SET estado = :estado, updated_at = NOW() WHERE id = :id');
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (PDOException $e) {
            error_log('WhatsAppConversacion::cambiarEstado error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $deleteMessages = $db->prepare('DELETE FROM whatsapp_mensajes WHERE conversacion_id = :id');
            $deleteMessages->execute([':id' => $id]);

            $stmt = $db->prepare('DELETE FROM whatsapp_conversaciones WHERE id = :id');
            $stmt->execute([':id' => $id]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('WhatsAppConversacion::eliminar error: ' . $e->getMessage());
            return false;
        }
    }
}
