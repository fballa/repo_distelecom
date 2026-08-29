<?php

require_once __DIR__ . '/../config/Database.php';

class PedidoDetalle
{
    public static function guardarDetalles(int $pedidoId, array $productos): bool
    {
        try {
            $db = Database::getConnection();
            $deleteStmt = $db->prepare('DELETE FROM pedido_detalle WHERE pedido_id = :pedido_id');
            $deleteStmt->execute([':pedido_id' => $pedidoId]);

            $stmt = $db->prepare('INSERT INTO pedido_detalle (pedido_id, producto_id, cantidad, precio, subtotal, created_at) VALUES (:pedido_id, :producto_id, :cantidad, :precio, :subtotal, NOW())');

            foreach ($productos as $item) {
                $subtotal = $item['cantidad'] * $item['precio'];
                $stmt->execute([
                    ':pedido_id' => $pedidoId,
                    ':producto_id' => $item['producto_id'],
                    ':cantidad' => $item['cantidad'],
                    ':precio' => $item['precio'],
                    ':subtotal' => $subtotal,
                ]);
            }

            return true;
        } catch (PDOException $e) {
            error_log('PedidoDetalle::guardarDetalles error: ' . $e->getMessage());
            return false;
        }
    }

    public static function obtenerPorPedido(int $pedidoId): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT pd.id, pd.pedido_id, pd.producto_id, p.nombre AS producto_nombre, p.imagen_principal, pd.cantidad, CASE WHEN pd.precio IS NULL OR pd.precio = 0 THEN p.precio ELSE pd.precio END AS precio, CASE WHEN pd.subtotal IS NULL OR pd.subtotal = 0 THEN pd.cantidad * CASE WHEN pd.precio IS NULL OR pd.precio = 0 THEN p.precio ELSE pd.precio END ELSE pd.subtotal END AS subtotal, pd.created_at FROM pedido_detalle pd LEFT JOIN productos p ON pd.producto_id = p.id WHERE pd.pedido_id = :pedido_id');
            $stmt->execute([':pedido_id' => $pedidoId]);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('PedidoDetalle::obtenerPorPedido error: ' . $e->getMessage());
            return [];
        }
    }

    public static function eliminarPorPedido(int $pedidoId): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('DELETE FROM pedido_detalle WHERE pedido_id = :pedido_id');
            return $stmt->execute([':pedido_id' => $pedidoId]);
        } catch (PDOException $e) {
            error_log('PedidoDetalle::eliminarPorPedido error: ' . $e->getMessage());
            return false;
        }
    }
}
