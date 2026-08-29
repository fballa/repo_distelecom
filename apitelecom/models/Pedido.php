<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/EstadoPedido.php';
require_once __DIR__ . '/HistorialPedido.php';

class Pedido
{
    public static function obtenerTodos(array $filtros, int $pagina, int $limite): array
    {
        $offset = ($pagina - 1) * $limite;
        $where = ['1 = 1'];
        $params = [];

        if (!empty($filtros['cliente_id'])) {
            $where[] = 'p.cliente_id = :cliente_id';
            $params[':cliente_id'] = (int) $filtros['cliente_id'];
        }

        if (!empty($filtros['estado'])) {
            $where[] = 'ep.nombre = :estado';
            $params[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $where[] = 'p.created_at >= :fecha_desde';
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $where[] = 'p.created_at <= :fecha_hasta';
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $whereSql = implode(' AND ', $where);

        try {
            $db = Database::getConnection();
            $countStmt = $db->prepare("SELECT COUNT(*) FROM pedidos p LEFT JOIN estado_pedidos ep ON p.estado_id = ep.id WHERE $whereSql");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT p.id, p.cliente_id, COALESCE(NULLIF(CONCAT_WS(' ', c.nombre, c.apellido), ''), 'no encontrado') AS cliente_nombre, p.numero, p.subtotal, p.impuestos, p.total, p.estado_id, ep.nombre AS estado, p.created_at, p.updated_at FROM pedidos p LEFT JOIN estado_pedidos ep ON p.estado_id = ep.id LEFT JOIN clientes c ON p.cliente_id = c.id WHERE $whereSql ORDER BY p.created_at DESC LIMIT :limite OFFSET :offset";
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
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
            error_log('Pedido::obtenerTodos error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT p.id, p.cliente_id, COALESCE(NULLIF(CONCAT_WS(" ", c.nombre, c.apellido), ""), "no encontrado") AS cliente_nombre, c.telefono AS cliente_telefono, c.correo AS cliente_correo, p.numero, p.subtotal, p.impuestos, p.total, p.estado_id, ep.nombre AS estado, p.created_at, p.updated_at FROM pedidos p LEFT JOIN estado_pedidos ep ON p.estado_id = ep.id LEFT JOIN clientes c ON p.cliente_id = c.id WHERE p.id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $pedido = $stmt->fetch();

            return $pedido ?: null;
        } catch (PDOException $e) {
            error_log('Pedido::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function obtenerPorNumero(string $numero): ?array
    {
        if (trim($numero) === '') {
            return null;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id FROM pedidos WHERE numero = :numero LIMIT 1');
            $stmt->execute([':numero' => $numero]);
            $id = $stmt->fetchColumn();

            if (!$id) {
                return null;
            }

            return self::obtenerPorId((int) $id);
        } catch (PDOException $e) {
            error_log('Pedido::obtenerPorNumero error: ' . $e->getMessage());
            return null;
        }
    }

    public static function obtenerPorCliente(int $clienteId, int $pagina, int $limite): array
    {
        $offset = ($pagina - 1) * $limite;

        try {
            $db = Database::getConnection();
            $countStmt = $db->prepare('SELECT COUNT(*) FROM pedidos WHERE cliente_id = :cliente_id');
            $countStmt->execute([':cliente_id' => $clienteId]);
            $total = (int) $countStmt->fetchColumn();

            $sql = 'SELECT p.id, p.cliente_id, COALESCE(NULLIF(CONCAT_WS(" ", c.nombre, c.apellido), ""), "no encontrado") AS cliente_nombre, p.numero, p.subtotal, p.impuestos, p.total, p.estado_id, ep.nombre AS estado, p.created_at, p.updated_at FROM pedidos p LEFT JOIN estado_pedidos ep ON p.estado_id = ep.id LEFT JOIN clientes c ON p.cliente_id = c.id WHERE p.cliente_id = :cliente_id ORDER BY p.created_at DESC LIMIT :limite OFFSET :offset';
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
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
            error_log('Pedido::obtenerPorCliente error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO pedidos (cliente_id, numero, subtotal, impuestos, total, estado_id, created_at, updated_at) VALUES (:cliente_id, :numero, :subtotal, :impuestos, :total, :estado_id, NOW(), NOW())');
            $stmt->execute([
                ':cliente_id' => $data['cliente_id'],
                ':numero' => $data['numero'],
                ':subtotal' => $data['subtotal'],
                ':impuestos' => $data['impuestos'],
                ':total' => $data['total'],
                ':estado_id' => $data['estado_id'],
            ]);

            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Pedido::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function actualizar(int $id, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $fields = [];
            $params = [':id' => $id];

            if (array_key_exists('cliente_id', $data)) {
                $fields[] = 'cliente_id = :cliente_id';
                $params[':cliente_id'] = $data['cliente_id'];
            }
            if (array_key_exists('numero', $data)) {
                $fields[] = 'numero = :numero';
                $params[':numero'] = $data['numero'];
            }
            if (array_key_exists('subtotal', $data)) {
                $fields[] = 'subtotal = :subtotal';
                $params[':subtotal'] = $data['subtotal'];
            }
            if (array_key_exists('impuestos', $data)) {
                $fields[] = 'impuestos = :impuestos';
                $params[':impuestos'] = $data['impuestos'];
            }
            if (array_key_exists('total', $data)) {
                $fields[] = 'total = :total';
                $params[':total'] = $data['total'];
            }
            if (array_key_exists('estado_id', $data)) {
                $fields[] = 'estado_id = :estado_id';
                $params[':estado_id'] = $data['estado_id'];
            }

            if (empty($fields)) {
                return true;
            }

            $sql = 'UPDATE pedidos SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
            $stmt = $db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Pedido::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $stmtDetalle = $db->prepare('DELETE FROM pedido_detalle WHERE pedido_id = :pedido_id');
            $stmtDetalle->execute([':pedido_id' => $id]);

            $stmtPagos = $db->prepare('DELETE FROM pagos WHERE pedido_id = :pedido_id');
            $stmtPagos->execute([':pedido_id' => $id]);

            $stmtHistorial = $db->prepare('DELETE FROM historial_pedidos WHERE pedido_id = :pedido_id');
            $stmtHistorial->execute([':pedido_id' => $id]);

            $stmtPedido = $db->prepare('DELETE FROM pedidos WHERE id = :id');
            $deleted = $stmtPedido->execute([':id' => $id]);

            if (!$deleted) {
                $db->rollBack();
                return false;
            }

            $db->commit();
            return true;
        } catch (PDOException $e) {
            error_log('Pedido::eliminar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function cambiarEstado(int $id, int $estadoId, ?string $comentario = null, ?string $usuario = null): bool
    {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $stmt = $db->prepare('UPDATE pedidos SET estado_id = :estado_id, updated_at = NOW() WHERE id = :id');
            $updated = $stmt->execute([':estado_id' => $estadoId, ':id' => $id]);

            if (!$updated) {
                $db->rollBack();
                return false;
            }

            if (!HistorialPedido::registrar($id, $estadoId, $comentario, $usuario)) {
                $db->rollBack();
                return false;
            }

            $db->commit();
            return true;
        } catch (PDOException $e) {
            error_log('Pedido::cambiarEstado error: ' . $e->getMessage());
            return false;
        }
    }

    public static function generarNumero(PDO $db): string
    {
        $year = date('Y');
        $prefix = sprintf('DST-%s-', $year);
        $stmt = $db->prepare('SELECT numero FROM pedidos WHERE numero LIKE :prefix ORDER BY numero DESC LIMIT 1');
        $stmt->execute([':prefix' => $prefix . '%']);
        $ultimo = $stmt->fetchColumn();

        $secuencia = 1;
        if ($ultimo !== false) {
            $parts = explode('-', $ultimo);
            $lastSequence = end($parts);
            if (is_numeric($lastSequence)) {
                $secuencia = (int) $lastSequence + 1;
            }
        }

        return sprintf('DST-%s-%06d', $year, $secuencia);
    }

    public static function existeNumero(string $numero): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(*) FROM pedidos WHERE numero = :numero');
            $stmt->execute([':numero' => $numero]);

            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Pedido::existeNumero error: ' . $e->getMessage());
            return false;
        }
    }
}
