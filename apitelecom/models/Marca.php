<?php

require_once __DIR__ . '/../config/Database.php';

class Marca
{
    public static function obtenerTodos(array $filtros, int $pagina, int $limite): array
    {
        $offset = ($pagina - 1) * $limite;
        $where = ['1 = 1'];
        $params = [];

        if (!empty($filtros['estado']) && in_array($filtros['estado'], ['Activo', 'Inactivo'], true)) {
            $where[] = 'estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['nombre'])) {
            $where[] = 'nombre LIKE :nombre';
            $params[':nombre'] = '%' . $filtros['nombre'] . '%';
        }

        $whereSql = implode(' AND ', $where);

        try {
            $db = Database::getConnection();
            $countStmt = $db->prepare("SELECT COUNT(*) FROM marcas WHERE $whereSql");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT id, nombre, logo, descripcion, estado, created_at FROM marcas WHERE $whereSql ORDER BY nombre ASC LIMIT :limite OFFSET :offset";
            $stmt = $db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }

            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll();

            return [
                'total' => $total,
                'pagina' => $pagina,
                'limite' => $limite,
                'data' => self::escapeData($items),
            ];
        } catch (PDOException $e) {
            error_log('Marca::obtenerTodos error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, nombre, logo, descripcion, estado, created_at FROM marcas WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $marca = $stmt->fetch();

            return $marca ? self::escapeData($marca) : null;
        } catch (PDOException $e) {
            error_log('Marca::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function existeId(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(*) FROM marcas WHERE id = :id');
            $stmt->execute([':id' => $id]);

            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Marca::existeId error: ' . $e->getMessage());
            return false;
        }
    }

    public static function obtenerActivas(): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, nombre, logo FROM marcas WHERE estado = :estado ORDER BY nombre ASC');
            $stmt->execute([':estado' => 'Activo']);
            $marcas = $stmt->fetchAll();

            return self::escapeData($marcas);
        } catch (PDOException $e) {
            error_log('Marca::obtenerActivas error: ' . $e->getMessage());
            return [];
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO marcas (nombre, logo, descripcion, estado, created_at) VALUES (:nombre, :logo, :descripcion, :estado, NOW())');
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':logo' => $data['logo'],
                ':descripcion' => $data['descripcion'],
                ':estado' => $data['estado'],
            ]);

            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Marca::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function actualizar(int $id, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE marcas SET nombre = :nombre, logo = :logo, descripcion = :descripcion, estado = :estado WHERE id = :id');
            return $stmt->execute([
                ':nombre' => $data['nombre'],
                ':logo' => $data['logo'],
                ':descripcion' => $data['descripcion'],
                ':estado' => $data['estado'],
                ':id' => $id,
            ]);
        } catch (PDOException $e) {
            error_log('Marca::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE marcas SET estado = :estado WHERE id = :id');
            return $stmt->execute([':estado' => 'Inactivo', ':id' => $id]);
        } catch (PDOException $e) {
            error_log('Marca::eliminar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function cambiarEstado(int $id, string $estado): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE marcas SET estado = :estado WHERE id = :id');
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (PDOException $e) {
            error_log('Marca::cambiarEstado error: ' . $e->getMessage());
            return false;
        }
    }

    public static function existeNombre(string $nombre, ?int $idExcluir = null): bool
    {
        try {
            $sql = 'SELECT COUNT(*) FROM marcas WHERE nombre = :nombre';
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
            error_log('Marca::existeNombre error: ' . $e->getMessage());
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
