<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/ChatbotMensaje.php';

class ChatbotConversacion
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

        if (!empty($filtros['ip'])) {
            $where[] = 'ip LIKE :ip';
            $params[':ip'] = '%' . $filtros['ip'] . '%';
        }

        if (!empty($filtros['nombre'])) {
            $where[] = 'nombre LIKE :nombre';
            $params[':nombre'] = '%' . $filtros['nombre'] . '%';
        }

        $whereSql = implode(' AND ', $where);

        try {
            $db = Database::getConnection();
            $countStmt = $db->prepare("SELECT COUNT(*) FROM chatbot_conversaciones WHERE $whereSql");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT id, uuid, ip, nombre, estado, created_at, updated_at, (SELECT COUNT(*) FROM chatbot_mensajes WHERE conversacion_id = chatbot_conversaciones.id) AS mensajes_count FROM chatbot_conversaciones WHERE $whereSql ORDER BY created_at DESC LIMIT :limite OFFSET :offset";
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
            error_log('ChatbotConversacion::obtenerTodos error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, uuid, ip, nombre, estado, created_at, updated_at FROM chatbot_conversaciones WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $conversacion = $stmt->fetch();

            if (!$conversacion) {
                return null;
            }

            $conversacion['mensajes'] = ChatbotMensaje::obtenerPorConversacion($id);
            return $conversacion;
        } catch (PDOException $e) {
            error_log('ChatbotConversacion::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function obtenerPorUuid(string $uuid): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, uuid, ip, nombre, estado, created_at, updated_at FROM chatbot_conversaciones WHERE uuid = :uuid LIMIT 1');
            $stmt->execute([':uuid' => $uuid]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('ChatbotConversacion::obtenerPorUuid error: ' . $e->getMessage());
            return null;
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $uuid = $data['uuid'] ?? self::generarUuid();
            $stmt = $db->prepare('INSERT INTO chatbot_conversaciones (uuid, ip, nombre, estado, created_at, updated_at) VALUES (:uuid, :ip, :nombre, :estado, NOW(), NOW())');
            $stmt->execute([
                ':uuid' => $uuid,
                ':ip' => $data['ip'] ?? null,
                ':nombre' => $data['nombre'] ?? null,
                ':estado' => $data['estado'] ?? 'Activa',
            ]);

            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('ChatbotConversacion::crear error: ' . $e->getMessage());
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

            $sql = 'UPDATE chatbot_conversaciones SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
            $stmt = $db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('ChatbotConversacion::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function cambiarEstado(int $id, string $estado): bool
    {
        if (!in_array($estado, ['Activa', 'Finalizada'], true)) {
            return false;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE chatbot_conversaciones SET estado = :estado, updated_at = NOW() WHERE id = :id');
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (PDOException $e) {
            error_log('ChatbotConversacion::cambiarEstado error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $deleteMessages = $db->prepare('DELETE FROM chatbot_mensajes WHERE conversacion_id = :id');
            $deleteMessages->execute([':id' => $id]);

            $stmt = $db->prepare('DELETE FROM chatbot_conversaciones WHERE id = :id');
            $stmt->execute([':id' => $id]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('ChatbotConversacion::eliminar error: ' . $e->getMessage());
            return false;
        }
    }

    private static function generarUuid(): string
    {
        $data = random_bytes(16);
        return sprintf('%08s-%04s-%04x-%04x-%12s',
            bin2hex(substr($data, 0, 4)),
            bin2hex(substr($data, 4, 2)),
            (hexdec(bin2hex(substr($data, 6, 2))) & 0x0fff) | 0x4000,
            (hexdec(bin2hex(substr($data, 8, 2))) & 0x3fff) | 0x8000,
            bin2hex(substr($data, 10, 6))
        );
    }
}
