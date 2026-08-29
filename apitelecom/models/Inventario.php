<?php

require_once __DIR__ . '/../config/Database.php';

class Inventario
{
    public static function obtenerPorProducto(int $productoId): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT producto_id, stock_actual AS stock, stock_minimo, updated_at FROM inventario WHERE producto_id = :producto_id LIMIT 1');
            $stmt->execute([':producto_id' => $productoId]);
            $result = $stmt->fetch();

            if (!$result) {
                return ['stock' => null, 'stock_minimo' => null];
            }

            return $result;
        } catch (PDOException $e) {
            error_log('Inventario::obtenerPorProducto error: ' . $e->getMessage());
            return ['stock' => null, 'stock_minimo' => null];
        }
    }

    public static function actualizarStock(int $productoId, ?int $stock, ?int $stockMinimo): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT stock_actual, stock_minimo FROM inventario WHERE producto_id = :producto_id LIMIT 1');
            $stmt->execute([':producto_id' => $productoId]);
            $existing = $stmt->fetch();

            if ($existing) {
                $stockActual = $stock !== null ? $stock : $existing['stock_actual'];
                $stockMinimoActual = $stockMinimo !== null ? $stockMinimo : $existing['stock_minimo'];

                $stmt = $db->prepare('UPDATE inventario SET stock_actual = :stock_actual, stock_minimo = :stock_minimo, updated_at = NOW() WHERE producto_id = :producto_id');
                return $stmt->execute([
                    ':stock_actual' => $stockActual,
                    ':stock_minimo' => $stockMinimoActual,
                    ':producto_id' => $productoId,
                ]);
            }

            $stmt = $db->prepare('INSERT INTO inventario (producto_id, stock_actual, stock_minimo, created_at, updated_at) VALUES (:producto_id, :stock_actual, :stock_minimo, NOW(), NOW())');
            return $stmt->execute([
                ':producto_id' => $productoId,
                ':stock_actual' => $stock,
                ':stock_minimo' => $stockMinimo,
            ]);
        } catch (PDOException $e) {
            error_log('Inventario::actualizarStock error: ' . $e->getMessage());
            return false;
        }
    }
}
