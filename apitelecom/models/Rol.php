<?php

require_once __DIR__ . '/../config/Database.php';

class Rol
{
    public static function obtenerTodos(array $filtros, int $pagina, int $limite): array
    {
        $offset = ($pagina - 1) * $limite;
        $where = ['1 = 1'];
        $params = [];

        if (!empty($filtros['nombre'])) {
            $where[] = 'nombre LIKE :nombre';
            $params[':nombre'] = '%' . $filtros['nombre'] . '%';
        }

        if (!empty($filtros['descripcion'])) {
            $where[] = 'descripcion LIKE :descripcion';
            $params[':descripcion'] = '%' . $filtros['descripcion'] . '%';
        }

        $whereSql = implode(' AND ', $where);

        try {
            $db = Database::getConnection();
            $countStmt = $db->prepare("SELECT COUNT(*) FROM roles WHERE $whereSql");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT id, nombre, descripcion, created_at FROM roles WHERE $whereSql ORDER BY nombre ASC LIMIT :limite OFFSET :offset";
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
                'data' => self::escapeData($stmt->fetchAll()),
            ];
        } catch (PDOException $e) {
            error_log('Rol::obtenerTodos error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, nombre, descripcion, created_at FROM roles WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $rol = $stmt->fetch();

            return $rol ? self::escapeData($rol) : null;
        } catch (PDOException $e) {
            error_log('Rol::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO roles (nombre, descripcion, created_at) VALUES (:nombre, :descripcion, NOW())');
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'],
            ]);

            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Rol::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function actualizar(int $id, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE roles SET nombre = :nombre, descripcion = :descripcion WHERE id = :id');
            return $stmt->execute([
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'],
                ':id' => $id,
            ]);
        } catch (PDOException $e) {
            error_log('Rol::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        if (self::estaEnUso($id)) {
            return false;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('DELETE FROM roles WHERE id = :id');
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('Rol::eliminar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function existeNombre(string $nombre, ?int $idExcluir = null): bool
    {
        try {
            $sql = 'SELECT COUNT(*) FROM roles WHERE nombre = :nombre';
            if ($idExcluir !== null) {
                $sql .= ' AND id != :id';
            }

            $db = Database::getConnection();
            $stmt = $db->prepare($sql);
            $params = [':nombre' => $nombre];
            if ($idExcluir !== null) {
                $params[':id'] = $idExcluir;
            }

            $stmt->execute($params);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Rol::existeNombre error: ' . $e->getMessage());
            return false;
        }
    }

    public static function estaEnUso(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(*) FROM usuarios WHERE rol_id = :rol_id AND deleted_at IS NULL');
            $stmt->execute([':rol_id' => $id]);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Rol::estaEnUso error: ' . $e->getMessage());
            return false;
        }
    }

    private static function escapeData(array $data): array
    {
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        });

        return $data;
    }
}
