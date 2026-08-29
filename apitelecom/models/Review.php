<?php

require_once __DIR__ . '/../config/Database.php';

class Review
{
    public static function obtenerTodos(array $filtros, int $pagina, int $limite): array
    {
        $offset = ($pagina - 1) * $limite;
        $where = ['1 = 1'];
        $params = [];

        if (!empty($filtros['producto_id'])) {
            $where[] = 'r.producto_id = :producto_id';
            $params[':producto_id'] = (int) $filtros['producto_id'];
        }

        if (!empty($filtros['estado']) && in_array($filtros['estado'], ['Pendiente', 'Publicado', 'Oculto'], true)) {
            $where[] = 'r.estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['calificacion'])) {
            $where[] = 'r.calificacion = :calificacion';
            $params[':calificacion'] = (int) $filtros['calificacion'];
        }

        if (!empty($filtros['nombre'])) {
            $where[] = 'r.nombre LIKE :nombre';
            $params[':nombre'] = '%' . $filtros['nombre'] . '%';
        }

        $whereSql = implode(' AND ', $where);

        try {
            $db = Database::getConnection();

            $countStmt = $db->prepare("SELECT COUNT(*) FROM reviews_producto r JOIN productos p ON r.producto_id = p.id WHERE $whereSql");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT r.id, r.producto_id, p.nombre AS producto_nombre, p.imagen_principal AS producto_imagen, r.nombre, r.correo, r.calificacion, r.comentario, r.estado, r.created_at
                    FROM reviews_producto r
                    JOIN productos p ON r.producto_id = p.id
                    WHERE $whereSql
                    ORDER BY r.created_at DESC
                    LIMIT :limite OFFSET :offset";

            $stmt = $db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
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
            error_log('Review::obtenerTodos error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT r.id, r.producto_id, p.nombre AS producto_nombre, p.imagen_principal AS producto_imagen, r.nombre, r.correo, r.calificacion, r.comentario, r.estado, r.created_at FROM reviews_producto r JOIN productos p ON r.producto_id = p.id WHERE r.id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $review = $stmt->fetch();

            return $review ? self::escapeData($review) : null;
        } catch (PDOException $e) {
            error_log('Review::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function obtenerPorProducto(int $producto_id, int $pagina, int $limite): array
    {
        $offset = ($pagina - 1) * $limite;

        try {
            $db = Database::getConnection();

            $countStmt = $db->prepare('SELECT COUNT(*) FROM reviews_producto WHERE producto_id = :producto_id AND estado = :estado');
            $countStmt->execute([':producto_id' => $producto_id, ':estado' => 'Publicado']);
            $total = (int) $countStmt->fetchColumn();

            $stmt = $db->prepare('SELECT id, nombre, calificacion, comentario, created_at FROM reviews_producto WHERE producto_id = :producto_id AND estado = :estado ORDER BY created_at DESC LIMIT :limite OFFSET :offset');
            $stmt->bindValue(':producto_id', $producto_id, PDO::PARAM_INT);
            $stmt->bindValue(':estado', 'Publicado', PDO::PARAM_STR);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $items = $stmt->fetchAll();
            return ['total' => $total, 'pagina' => $pagina, 'limite' => $limite, 'data' => self::escapeData($items)];
        } catch (PDOException $e) {
            error_log('Review::obtenerPorProducto error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function obtenerPromedio(int $producto_id): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(*) AS total, AVG(calificacion) AS promedio FROM reviews_producto WHERE producto_id = :producto_id AND estado = :estado');
            $stmt->execute([':producto_id' => $producto_id, ':estado' => 'Publicado']);
            $result = $stmt->fetch();

            $total = isset($result['total']) ? (int) $result['total'] : 0;
            $promedio = isset($result['promedio']) ? round((float) $result['promedio'], 2) : 0.0;

            return ['promedio' => $promedio, 'total' => $total];
        } catch (PDOException $e) {
            error_log('Review::obtenerPromedio error: ' . $e->getMessage());
            return ['promedio' => 0.0, 'total' => 0];
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO reviews_producto (producto_id, nombre, correo, calificacion, comentario, estado) VALUES (:producto_id, :nombre, :correo, :calificacion, :comentario, :estado)');
            $stmt->execute([
                ':producto_id' => $data['producto_id'],
                ':nombre' => $data['nombre'],
                ':correo' => $data['correo'],
                ':calificacion' => $data['calificacion'],
                ':comentario' => $data['comentario'],
                ':estado' => $data['estado'],
            ]);

            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Review::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function actualizar(int $id, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE reviews_producto SET nombre = :nombre, correo = :correo, calificacion = :calificacion, comentario = :comentario, estado = :estado WHERE id = :id');
            return $stmt->execute([
                ':nombre' => $data['nombre'],
                ':correo' => $data['correo'],
                ':calificacion' => $data['calificacion'],
                ':comentario' => $data['comentario'],
                ':estado' => $data['estado'],
                ':id' => $id,
            ]);
        } catch (PDOException $e) {
            error_log('Review::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('DELETE FROM reviews_producto WHERE id = :id');
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('Review::eliminar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function cambiarEstado(int $id, string $estado): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE reviews_producto SET estado = :estado WHERE id = :id');
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (PDOException $e) {
            error_log('Review::cambiarEstado error: ' . $e->getMessage());
            return false;
        }
    }

    public static function existeReview(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(*) FROM reviews_producto WHERE id = :id');
            $stmt->execute([':id' => $id]);

            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Review::existeReview error: ' . $e->getMessage());
            return false;
        }
    }

    public static function existeProducto(int $producto_id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(*) FROM productos WHERE id = :id AND deleted_at IS NULL');
            $stmt->execute([':id' => $producto_id]);

            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Review::existeProducto error: ' . $e->getMessage());
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
