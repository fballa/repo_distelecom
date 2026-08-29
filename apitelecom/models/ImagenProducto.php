<?php

require_once __DIR__ . '/../config/Database.php';

class ImagenProducto
{
    public static function guardarImagenes(int $productoId, array $imagenes): bool
    {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $eliminarStmt = $db->prepare('DELETE FROM imagenes_producto WHERE producto_id = :producto_id');
            $eliminarStmt->execute([':producto_id' => $productoId]);

            $insertStmt = $db->prepare('INSERT INTO imagenes_producto (producto_id, imagen, orden, created_at, updated_at) VALUES (:producto_id, :imagen, :orden, NOW(), NOW())');

            foreach ($imagenes as $imagen) {
                if (empty($imagen['imagen'])) {
                    continue;
                }

                $insertStmt->execute([
                    ':producto_id' => $productoId,
                    ':imagen' => trim((string) $imagen['imagen']),
                    ':orden' => isset($imagen['orden']) ? (int) $imagen['orden'] : 0,
                ]);
            }

            $db->commit();
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            error_log('ImagenProducto::guardarImagenes error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminarImagen(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('DELETE FROM imagenes_producto WHERE id = :id');
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('ImagenProducto::eliminarImagen error: ' . $e->getMessage());
            return false;
        }
    }

    public static function actualizarOrden(int $id, int $orden): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE imagenes_producto SET orden = :orden, updated_at = NOW() WHERE id = :id');
            return $stmt->execute([':orden' => $orden, ':id' => $id]);
        } catch (PDOException $e) {
            error_log('ImagenProducto::actualizarOrden error: ' . $e->getMessage());
            return false;
        }
    }

    public static function obtenerPorProducto(int $productoId): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, imagen, orden FROM imagenes_producto WHERE producto_id = :producto_id ORDER BY orden ASC, id ASC');
            $stmt->execute([':producto_id' => $productoId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('ImagenProducto::obtenerPorProducto error: ' . $e->getMessage());
            return [];
        }
    }
}
