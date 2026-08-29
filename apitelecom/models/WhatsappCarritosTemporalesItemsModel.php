<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Producto.php';

class WhatsappCarritosTemporalesItemsModel
{
    public static function obtenerTodos(): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, carrito_id, producto_id, cantidad, precio FROM whatsapp_carritos_temporales_items');
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('WhatsappCarritosTemporalesItemsModel::obtenerTodos error: ' . $e->getMessage());
            return [];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, carrito_id, producto_id, cantidad, precio FROM whatsapp_carritos_temporales_items WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }

            // Agregar información de producto si existe
            $producto = Producto::obtenerPorId((int) $row['producto_id']);
            if ($producto) {
                $row['producto_nombre'] = $producto['nombre'] ?? null;
                $row['producto_sku'] = $producto['sku'] ?? null;
            }

            return $row;
        } catch (PDOException $e) {
            error_log('WhatsappCarritosTemporalesItemsModel::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO whatsapp_carritos_temporales_items (carrito_id, producto_id, cantidad, precio) VALUES (:carrito_id, :producto_id, :cantidad, :precio)');
            $stmt->execute([
                ':carrito_id' => (int) $data['carrito_id'],
                ':producto_id' => (int) $data['producto_id'],
                ':cantidad' => (int) $data['cantidad'],
                ':precio' => isset($data['precio']) ? (float) $data['precio'] : 0.00,
            ]);
            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('WhatsappCarritosTemporalesItemsModel::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function actualizar(int $id, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $fields = [];
            $params = [':id' => $id];

            if (array_key_exists('carrito_id', $data)) {
                $fields[] = 'carrito_id = :carrito_id';
                $params[':carrito_id'] = (int) $data['carrito_id'];
            }
            if (array_key_exists('producto_id', $data)) {
                $fields[] = 'producto_id = :producto_id';
                $params[':producto_id'] = (int) $data['producto_id'];
            }
            if (array_key_exists('cantidad', $data)) {
                $fields[] = 'cantidad = :cantidad';
                $params[':cantidad'] = (int) $data['cantidad'];
            }
            if (array_key_exists('precio', $data)) {
                $fields[] = 'precio = :precio';
                $params[':precio'] = (float) $data['precio'];
            }

            if (empty($fields)) {
                return true;
            }

            $sql = 'UPDATE whatsapp_carritos_temporales_items SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $stmt = $db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('WhatsappCarritosTemporalesItemsModel::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('DELETE FROM whatsapp_carritos_temporales_items WHERE id = :id');
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('WhatsappCarritosTemporalesItemsModel::eliminar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function obtenerPorCarrito(int $carrito_id): array
    {
        try {
            $db = Database::getConnection();
            $sql = 'SELECT i.id, i.carrito_id, i.producto_id, i.cantidad, i.precio, p.nombre AS producto_nombre, p.sku AS producto_sku FROM whatsapp_carritos_temporales_items i LEFT JOIN productos p ON p.id = i.producto_id WHERE i.carrito_id = :carrito_id';
            $stmt = $db->prepare($sql);
            $stmt->execute([':carrito_id' => $carrito_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('WhatsappCarritosTemporalesItemsModel::obtenerPorCarrito error: ' . $e->getMessage());
            return [];
        }
    }
}
