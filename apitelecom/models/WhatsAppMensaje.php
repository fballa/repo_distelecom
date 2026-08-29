<?php

require_once __DIR__ . '/../config/Database.php';

class WhatsAppMensaje
{
    public static function obtenerPorConversacion(int $conversacion_id): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, conversacion_id, remitente, mensaje, created_at FROM whatsapp_mensajes WHERE conversacion_id = :conversacion_id ORDER BY created_at ASC');
            $stmt->execute([':conversacion_id' => $conversacion_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('WhatsAppMensaje::obtenerPorConversacion error: ' . $e->getMessage());
            return [];
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO whatsapp_mensajes (conversacion_id, remitente, mensaje, created_at) VALUES (:conversacion_id, :remitente, :mensaje, NOW())');
            $stmt->execute([
                ':conversacion_id' => $data['conversacion_id'],
                ':remitente' => $data['remitente'],
                ':mensaje' => $data['mensaje'],
            ]);

            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('WhatsAppMensaje::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('DELETE FROM whatsapp_mensajes WHERE id = :id');
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('WhatsAppMensaje::eliminar error: ' . $e->getMessage());
            return false;
        }
    }
}
