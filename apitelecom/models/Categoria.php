<?php

require_once __DIR__ . '/../config/Database.php';

class Categoria
{
    public static function obtenerTodos(array $filtros, int $pagina, int $limite): array
    {
        $offset = ($pagina - 1) * $limite;
        $where = ['1 = 1'];
        $params = [];

        if (!empty($filtros['estado']) && in_array($filtros['estado'], ['Activo', 'Inactivo'], true)) {
            $where[] = 'c.estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['buscar'])) {
            $where[] = '(c.nombre LIKE :buscar OR c.slug LIKE :buscar)';
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }

        $whereSql = implode(' AND ', $where);

        try {
            $db = Database::getConnection();
            $countStmt = $db->prepare("SELECT COUNT(*) FROM categorias c WHERE $whereSql");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT c.id, c.nombre, c.slug, c.descripcion, c.imagen, c.icono, c.orden, c.estado, c.created_at, c.updated_at,
                           (SELECT COUNT(*) FROM productos p WHERE p.categoria_id = c.id) AS productos
                    FROM categorias c
                    WHERE $whereSql
                    ORDER BY c.orden ASC, c.nombre ASC
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
            error_log('Categoria::obtenerTodos error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, nombre, slug, descripcion, imagen, icono, orden, estado, created_at, updated_at FROM categorias WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $categoria = $stmt->fetch();

            if (!$categoria) {
                return null;
            }

            $categoria['productos'] = self::contarProductos($id);
            return self::escapeData($categoria);
        } catch (PDOException $e) {
            error_log('Categoria::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function existeId(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(*) FROM categorias WHERE id = :id');
            $stmt->execute([':id' => $id]);

            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Categoria::existeId error: ' . $e->getMessage());
            return false;
        }
    }

    public static function obtenerPorSlug(string $slug): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, nombre, slug, descripcion, imagen, icono, orden, estado, created_at, updated_at FROM categorias WHERE slug = :slug LIMIT 1');
            $stmt->execute([':slug' => $slug]);
            $categoria = $stmt->fetch();

            return $categoria ? self::escapeData($categoria) : null;
        } catch (PDOException $e) {
            error_log('Categoria::obtenerPorSlug error: ' . $e->getMessage());
            return null;
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO categorias (nombre, slug, descripcion, imagen, icono, orden, estado, created_at, updated_at) VALUES (:nombre, :slug, :descripcion, :imagen, :icono, :orden, :estado, NOW(), NOW())');
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':slug' => $data['slug'],
                ':descripcion' => $data['descripcion'],
                ':imagen' => $data['imagen'],
                ':icono' => $data['icono'],
                ':orden' => $data['orden'],
                ':estado' => $data['estado'],
            ]);

            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Categoria::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function actualizar(int $id, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE categorias SET nombre = :nombre, slug = :slug, descripcion = :descripcion, imagen = :imagen, icono = :icono, orden = :orden, estado = :estado, updated_at = NOW() WHERE id = :id');
            return $stmt->execute([
                ':nombre' => $data['nombre'],
                ':slug' => $data['slug'],
                ':descripcion' => $data['descripcion'],
                ':imagen' => $data['imagen'],
                ':icono' => $data['icono'],
                ':orden' => $data['orden'],
                ':estado' => $data['estado'],
                ':id' => $id,
            ]);
        } catch (PDOException $e) {
            error_log('Categoria::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('DELETE FROM categorias WHERE id = :id');
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('Categoria::eliminar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function cambiarEstado(int $id, string $estado): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE categorias SET estado = :estado, updated_at = NOW() WHERE id = :id');
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (PDOException $e) {
            error_log('Categoria::cambiarEstado error: ' . $e->getMessage());
            return false;
        }
    }

    public static function actualizarOrden(int $id, int $nuevoOrden): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE categorias SET orden = :orden, updated_at = NOW() WHERE id = :id');
            return $stmt->execute([':orden' => $nuevoOrden, ':id' => $id]);
        } catch (PDOException $e) {
            error_log('Categoria::actualizarOrden error: ' . $e->getMessage());
            return false;
        }
    }

    public static function existeSlug(string $slug, ?int $idExcluir = null): bool
    {
        try {
            $sql = 'SELECT COUNT(*) FROM categorias WHERE slug = :slug';
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
            error_log('Categoria::existeSlug error: ' . $e->getMessage());
            return false;
        }
    }

    public static function contarProductos(int $id): int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(*) FROM productos WHERE categoria_id = :id');
            $stmt->execute([':id' => $id]);

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Categoria::contarProductos error: ' . $e->getMessage());
            return 0;
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
