<?php

require_once __DIR__ . '/../config/Database.php';

class Pago
{
    public static function crear(int $pedidoId, array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO pagos (pedido_id, metodo, monto, referencia, estado, fecha_pago, created_at) VALUES (:pedido_id, :metodo, :monto, :referencia, :estado, :fecha_pago, NOW())');
            $stmt->execute([
                ':pedido_id' => $pedidoId,
                ':metodo' => $data['metodo'],
                ':monto' => $data['monto'],
                ':referencia' => $data['referencia'],
                ':estado' => $data['estado'],
                ':fecha_pago' => $data['fecha_pago'],
            ]);

            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Pago::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function obtenerPorPedido(int $pedidoId): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, pedido_id, metodo, monto, referencia, estado, fecha_pago, created_at FROM pagos WHERE pedido_id = :pedido_id ORDER BY created_at DESC');
            $stmt->execute([':pedido_id' => $pedidoId]);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Pago::obtenerPorPedido error: ' . $e->getMessage());
            return [];
        }
    }
}
