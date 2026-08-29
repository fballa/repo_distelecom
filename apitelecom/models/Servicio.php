<?php

require_once __DIR__ . '/../config/Database.php';

class Servicio
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

            $countStmt = $db->prepare("SELECT COUNT(*) FROM servicios WHERE $whereSql");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT id, nombre, slug, descripcion, icono, imagen, orden, estado, created_at, updated_at
                    FROM servicios
                    WHERE $whereSql
                    ORDER BY orden ASC, nombre ASC
                    LIMIT :limite OFFSET :offset";

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
            error_log('Servicio::obtenerTodos error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, nombre, slug, descripcion, icono, imagen, orden, estado, created_at, updated_at FROM servicios WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $servicio = $stmt->fetch();

            return $servicio ? self::escapeData($servicio) : null;
        } catch (PDOException $e) {
            error_log('Servicio::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function obtenerActivos(): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, nombre, slug, descripcion, icono, imagen, orden, estado, created_at, updated_at FROM servicios WHERE estado = :estado ORDER BY orden ASC');
            $stmt->execute([':estado' => 'Activo']);
            $items = $stmt->fetchAll();

            return self::escapeData($items);
        } catch (PDOException $e) {
            error_log('Servicio::obtenerActivos error: ' . $e->getMessage());
            return [];
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO servicios (nombre, slug, descripcion, icono, imagen, orden, estado, created_at) VALUES (:nombre, :slug, :descripcion, :icono, :imagen, :orden, :estado, NOW())');
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':slug' => $data['slug'],
                ':descripcion' => $data['descripcion'],
                ':icono' => $data['icono'],
                ':imagen' => $data['imagen'],
                ':orden' => $data['orden'],
                ':estado' => $data['estado'],
            ]);
            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Servicio::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function actualizar(int $id, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE servicios SET nombre = :nombre, slug = :slug, descripcion = :descripcion, icono = :icono, imagen = :imagen, orden = :orden, estado = :estado, updated_at = NOW() WHERE id = :id');
            return $stmt->execute([
                ':nombre' => $data['nombre'],
                ':slug' => $data['slug'],
                ':descripcion' => $data['descripcion'],
                ':icono' => $data['icono'],
                ':imagen' => $data['imagen'],
                ':orden' => $data['orden'],
                ':estado' => $data['estado'],
                ':id' => $id,
            ]);
        } catch (PDOException $e) {
            error_log('Servicio::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE servicios SET estado = :estado, updated_at = NOW() WHERE id = :id');
            return $stmt->execute([':estado' => 'Inactivo', ':id' => $id]);
        } catch (PDOException $e) {
            error_log('Servicio::eliminar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function cambiarEstado(int $id, string $estado): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE servicios SET estado = :estado, updated_at = NOW() WHERE id = :id');
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (PDOException $e) {
            error_log('Servicio::cambiarEstado error: ' . $e->getMessage());
            return false;
        }
    }

    public static function existeNombre(string $nombre, ?int $idExcluir = null): bool
    {
        try {
            $sql = 'SELECT COUNT(*) FROM servicios WHERE nombre = :nombre';
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
            error_log('Servicio::existeNombre error: ' . $e->getMessage());
            return false;
        }
    }

    public static function existeSlug(string $slug, ?int $idExcluir = null): bool
    {
        try {
            $sql = 'SELECT COUNT(*) FROM servicios WHERE slug = :slug';
            if ($idExcluir !== null) {
                $sql .= ' AND id != :id';
            }

            $db = Database::getConnection();
            $stmt = $db->prepare($sql);
            $params = [':slug' => $slug];

            if ($idExcluir !== null) {
                $params[':id'] = $idExcluir;
            }

            $stmt->execute($params);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Servicio::existeSlug error: ' . $e->getMessage());
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
