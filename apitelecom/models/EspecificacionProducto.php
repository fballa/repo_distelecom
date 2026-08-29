<?php

require_once __DIR__ . '/../config/Database.php';

class EspecificacionProducto
{
    public static function guardarEspecificacion(int $productoId, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $eliminarStmt = $db->prepare('DELETE FROM especificaciones_producto WHERE producto_id = :producto_id');
            $eliminarStmt->execute([':producto_id' => $productoId]);

            if (empty($data)) {
                $db->commit();
                return true;
            }

            $columns = array_keys($data);
            $placeholders = [];
            $params = [':producto_id' => $productoId];

            foreach ($columns as $column) {
                $placeholders[] = sprintf('%s = :%s', $column, $column);
                $params[':' . $column] = trim((string) $data[$column]);
            }

            $sql = sprintf('INSERT INTO especificaciones_producto (producto_id, %s, created_at, updated_at) VALUES (:producto_id, %s, NOW(), NOW())', implode(', ', $columns), implode(', ', array_map(fn($col) => ':' . $col, $columns)));
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            $db->commit();
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            error_log('EspecificacionProducto::guardarEspecificacion error: ' . $e->getMessage());
            return false;
        }
    }

    public static function actualizarEspecificacion(int $productoId, array $data): bool
    {
        return self::guardarEspecificacion($productoId, $data);
    }

    public static function obtenerPorProducto(int $productoId): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT * FROM especificaciones_producto WHERE producto_id = :producto_id LIMIT 1');
            $stmt->execute([':producto_id' => $productoId]);
            $result = $stmt->fetch();

            if (!$result) {
                return [];
            }

            unset($result['id'], $result['producto_id'], $result['created_at'], $result['updated_at']);
            return $result;
        } catch (PDOException $e) {
            error_log('EspecificacionProducto::obtenerPorProducto error: ' . $e->getMessage());
            return [];
        }
    }
}
