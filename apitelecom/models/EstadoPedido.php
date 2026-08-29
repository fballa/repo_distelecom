<?php

require_once __DIR__ . '/../config/Database.php';

class EstadoPedido
{
    public static function obtenerTodos(array $filtros, int $pagina, int $limite): array
    {
        $offset = ($pagina - 1) * $limite;
        $where = ['1 = 1'];
        $params = [];

        if (!empty($filtros['buscar'])) {
            $where[] = '(nombre LIKE :buscar OR descripcion LIKE :buscar)';
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }

        $whereSql = implode(' AND ', $where);

        try {
            $db = Database::getConnection();
            $countStmt = $db->prepare("SELECT COUNT(*) FROM estado_pedidos WHERE $whereSql");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT id, nombre, descripcion, color, created_at FROM estado_pedidos WHERE $whereSql ORDER BY id ASC LIMIT :limite OFFSET :offset";
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
            error_log('EstadoPedido::obtenerTodos error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, nombre, descripcion, color, created_at FROM estado_pedidos WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $estado = $stmt->fetch();

            return $estado ?: null;
        } catch (PDOException $e) {
            error_log('EstadoPedido::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function obtenerPorNombre(string $nombre): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, nombre, descripcion, color, created_at FROM estado_pedidos WHERE nombre = :nombre LIMIT 1');
            $stmt->execute([':nombre' => $nombre]);
            $estado = $stmt->fetch();

            return $estado ?: null;
        } catch (PDOException $e) {
            error_log('EstadoPedido::obtenerPorNombre error: ' . $e->getMessage());
            return null;
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO estado_pedidos (nombre, descripcion, color, created_at) VALUES (:nombre, :descripcion, :color, NOW())');
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'],
                ':color' => $data['color'],
            ]);

            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('EstadoPedido::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function actualizar(int $id, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE estado_pedidos SET nombre = :nombre, descripcion = :descripcion, color = :color WHERE id = :id');
            return $stmt->execute([
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'],
                ':color' => $data['color'],
                ':id' => $id,
            ]);
        } catch (PDOException $e) {
            error_log('EstadoPedido::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('DELETE FROM estado_pedidos WHERE id = :id');
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('EstadoPedido::eliminar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function existeNombre(string $nombre, ?int $idExcluir = null): bool
    {
        try {
            $sql = 'SELECT COUNT(*) FROM estado_pedidos WHERE nombre = :nombre';
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
            error_log('EstadoPedido::existeNombre error: ' . $e->getMessage());
            return false;
        }
    }
}
