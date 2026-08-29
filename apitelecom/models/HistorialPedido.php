<?php

require_once __DIR__ . '/../config/Database.php';

class HistorialPedido
{
    public static function registrar(int $pedidoId, int $estadoId, ?string $comentario = null, ?string $usuario = null): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO historial_pedidos (pedido_id, estado_id, comentario, usuario, created_at) VALUES (:pedido_id, :estado_id, :comentario, :usuario, NOW())');
            return $stmt->execute([
                ':pedido_id' => $pedidoId,
                ':estado_id' => $estadoId,
                ':comentario' => $comentario,
                ':usuario' => $usuario,
            ]);
        } catch (PDOException $e) {
            error_log('HistorialPedido::registrar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function obtenerPorPedido(int $pedidoId): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, pedido_id, estado_id, comentario, usuario, created_at FROM historial_pedidos WHERE pedido_id = :pedido_id ORDER BY created_at ASC');
            $stmt->execute([':pedido_id' => $pedidoId]);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('HistorialPedido::obtenerPorPedido error: ' . $e->getMessage());
            return [];
        }
    }
}
