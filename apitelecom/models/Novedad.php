<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Producto.php';

class Novedad
{
    public static function obtenerTodos(array $filtros, int $pagina, int $limite): array
    {
        $offset = ($pagina - 1) * $limite;
        $where = ['1 = 1'];
        $params = [];

        if (!empty($filtros['producto_id'])) {
            $where[] = 'n.producto_id = :producto_id';
            $params[':producto_id'] = (int) $filtros['producto_id'];
        }

        if (!empty($filtros['estado'])) {
            $where[] = 'n.estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['buscar'])) {
            $where[] = '(n.titulo LIKE :buscar OR n.descripcion LIKE :buscar)';
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }

        $whereSql = implode(' AND ', $where);

        try {
            $db = Database::getConnection();
            $countStmt = $db->prepare("SELECT COUNT(*) FROM novedades n WHERE $whereSql");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT n.id, n.producto_id, n.titulo, n.descripcion, n.imagen, n.estado, n.created_at, p.nombre AS producto_nombre, p.imagen_principal AS producto_imagen, p.precio AS producto_precio
                    FROM novedades n
                    LEFT JOIN productos p ON n.producto_id = p.id
                    WHERE $whereSql
                    ORDER BY n.created_at DESC
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
            error_log('Novedad::obtenerTodos error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT n.*, p.nombre AS producto_nombre, p.imagen_principal AS producto_imagen, p.precio AS producto_precio FROM novedades n LEFT JOIN productos p ON n.producto_id = p.id WHERE n.id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $novedad = $stmt->fetch();

            return $novedad ? self::escapeData($novedad) : null;
        } catch (PDOException $e) {
            error_log('Novedad::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function obtenerPublicadas(): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT n.id, n.producto_id, n.titulo, n.descripcion, n.imagen, n.estado, n.created_at, p.nombre AS producto_nombre, p.imagen_principal AS producto_imagen, p.precio AS producto_precio FROM novedades n LEFT JOIN productos p ON n.producto_id = p.id WHERE n.estado = :estado ORDER BY n.created_at DESC');
            $stmt->execute([':estado' => 'Publicado']);

            return self::escapeData($stmt->fetchAll());
        } catch (PDOException $e) {
            error_log('Novedad::obtenerPublicadas error: ' . $e->getMessage());
            return [];
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $producto = Producto::obtenerPorId($data['producto_id']);
            if (!$producto) {
                return null;
            }

            if (empty($data['titulo'])) {
                $data['titulo'] = $producto['nombre'];
            }

            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO novedades (producto_id, titulo, descripcion, imagen, estado, created_at) VALUES (:producto_id, :titulo, :descripcion, :imagen, :estado, NOW())');
            $stmt->execute([
                ':producto_id' => $data['producto_id'],
                ':titulo' => $data['titulo'],
                ':descripcion' => $data['descripcion'],
                ':imagen' => $data['imagen'],
                ':estado' => $data['estado'],
            ]);

            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Novedad::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function actualizar(int $id, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE novedades SET producto_id = :producto_id, titulo = :titulo, descripcion = :descripcion, imagen = :imagen, estado = :estado WHERE id = :id');
            return $stmt->execute([
                ':producto_id' => $data['producto_id'],
                ':titulo' => $data['titulo'],
                ':descripcion' => $data['descripcion'],
                ':imagen' => $data['imagen'],
                ':estado' => $data['estado'],
                ':id' => $id,
            ]);
        } catch (PDOException $e) {
            error_log('Novedad::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE novedades SET estado = :estado WHERE id = :id');
            return $stmt->execute([':estado' => 'Oculto', ':id' => $id]);
        } catch (PDOException $e) {
            error_log('Novedad::eliminar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function cambiarEstado(int $id, string $estado): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE novedades SET estado = :estado WHERE id = :id');
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (PDOException $e) {
            error_log('Novedad::cambiarEstado error: ' . $e->getMessage());
            return false;
        }
    }

    public static function existeNovedadParaProducto(int $productoId, ?int $idExcluir = null): bool
    {
        try {
            $sql = 'SELECT COUNT(*) FROM novedades WHERE producto_id = :producto_id';
            $params = [':producto_id' => $productoId];
            if ($idExcluir !== null) {
                $sql .= ' AND id != :id';
                $params[':id'] = $idExcluir;
            }
            $db = Database::getConnection();
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Novedad::existeNovedadParaProducto error: ' . $e->getMessage());
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
