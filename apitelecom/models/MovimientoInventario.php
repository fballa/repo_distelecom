<?php

require_once __DIR__ . '/../config/Database.php';

class MovimientoInventario
{
    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO movimientos_inventario (producto_id, tipo, cantidad, motivo, usuario, created_at) VALUES (:producto_id, :tipo, :cantidad, :motivo, :usuario, NOW())');
            $stmt->execute([
                ':producto_id' => (int) $data['producto_id'],
                ':tipo' => $data['tipo'],
                ':cantidad' => (int) $data['cantidad'],
                ':motivo' => $data['motivo'] ?? null,
                ':usuario' => $data['usuario'] ?? null,
            ]);
            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('MovimientoInventario::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function obtenerTodos(array $filtros = [], int $pagina = 1, int $limite = 50): array
    {
        $offset = ($pagina - 1) * $limite;
        $where = ['1=1'];
        $params = [];

        if (!empty($filtros['producto_id'])) {
            $where[] = 'm.producto_id = :producto_id';
            $params[':producto_id'] = (int) $filtros['producto_id'];
        }

        if (!empty($filtros['tipo'])) {
            $where[] = 'm.tipo = :tipo';
            $params[':tipo'] = $filtros['tipo'];
        }

        $whereSql = implode(' AND ', $where);

        try {
            $db = Database::getConnection();
            $countStmt = $db->prepare("SELECT COUNT(*) FROM movimientos_inventario m WHERE $whereSql");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT m.id, m.producto_id, p.nombre AS producto, p.sku, m.tipo, m.cantidad, m.motivo, m.usuario, m.created_at FROM movimientos_inventario m LEFT JOIN productos p ON p.id = m.producto_id WHERE $whereSql ORDER BY m.created_at DESC LIMIT :limite OFFSET :offset";
            $stmt = $db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return ['total' => $total, 'pagina' => $pagina, 'limite' => $limite, 'data' => $stmt->fetchAll()];
        } catch (PDOException $e) {
            error_log('MovimientoInventario::obtenerTodos error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function obtenerRecientes(int $limit = 10): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT m.id, m.producto_id, p.nombre AS producto, p.sku, m.tipo, m.cantidad, m.motivo, m.usuario, m.created_at FROM movimientos_inventario m LEFT JOIN productos p ON p.id = m.producto_id ORDER BY m.created_at DESC LIMIT :lim');
            $stmt->bindValue(':lim', (int) $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('MovimientoInventario::obtenerRecientes error: ' . $e->getMessage());
            return [];
        }
    }
}
