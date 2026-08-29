<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/WhatsappCarritosTemporalesItemsModel.php';

class TbCarritoTemporalModel
{
    public static function obtenerTodos(array $filtros, int $pagina, int $limite): array
    {
        $offset = ($pagina - 1) * $limite;
        $where = ['1=1'];
        $params = [];

        if (!empty($filtros['cliente_id'])) {
            $where[] = 'cliente_id = :cliente_id';
            $params[':cliente_id'] = (int) $filtros['cliente_id'];
        }

        if (!empty($filtros['phone'])) {
            $where[] = 'phone LIKE :phone';
            $params[':phone'] = '%' . $filtros['phone'] . '%';
        }

        if (!empty($filtros['estado'])) {
            $where[] = 'estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }

        $whereSql = implode(' AND ', $where);

        try {
            $db = Database::getConnection();

            $countSql = "SELECT COUNT(*) FROM tbcarritotemporal WHERE $whereSql";
            $countStmt = $db->prepare($countSql);
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT id, phone, cliente_id, estado, carrito_json, created_at, updated_at FROM tbcarritotemporal WHERE $whereSql ORDER BY updated_at DESC LIMIT :limite OFFSET :offset";
            $stmt = $db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }

            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $data = $stmt->fetchAll();

            return ['total' => $total, 'pagina' => $pagina, 'limite' => $limite, 'data' => $data];
        } catch (PDOException $e) {
            error_log('TbCarritoTemporalModel::obtenerTodos error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, phone, cliente_id, estado, carrito_json, created_at, updated_at FROM tbcarritotemporal WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (PDOException $e) {
            error_log('TbCarritoTemporalModel::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO tbcarritotemporal (phone, cliente_id, estado, carrito_json, created_at, updated_at) VALUES (:phone, :cliente_id, :estado, :carrito_json, NOW(), NOW())');
            $stmt->execute([
                ':phone' => $data['phone'],
                ':cliente_id' => (int) $data['cliente_id'],
                ':estado' => $data['estado'],
                ':carrito_json' => $data['carrito_json'],
            ]);
            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('TbCarritoTemporalModel::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function actualizar(int $id, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $fields = [];
            $params = [':id' => $id];

            if (array_key_exists('phone', $data)) {
                $fields[] = 'phone = :phone';
                $params[':phone'] = $data['phone'];
            }
            if (array_key_exists('cliente_id', $data)) {
                $fields[] = 'cliente_id = :cliente_id';
                $params[':cliente_id'] = (int) $data['cliente_id'];
            }
            if (array_key_exists('estado', $data)) {
                $fields[] = 'estado = :estado';
                $params[':estado'] = $data['estado'];
            }
            if (array_key_exists('carrito_json', $data)) {
                $fields[] = 'carrito_json = :carrito_json';
                $params[':carrito_json'] = $data['carrito_json'];
            }

            if (empty($fields)) {
                return true;
            }

            $sql = 'UPDATE tbcarritotemporal SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
            $stmt = $db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('TbCarritoTemporalModel::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            // eliminar items relacionados
            $delItems = $db->prepare('DELETE FROM whatsapp_carritos_temporales_items WHERE carrito_id = :id');
            $delItems->execute([':id' => $id]);

            $stmt = $db->prepare('DELETE FROM tbcarritotemporal WHERE id = :id');
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('TbCarritoTemporalModel::eliminar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function buscarPorTelefono(string $phone): ?array
    {
        try {
            $db = Database::getConnection();
            // Intentar encontrar carrito con estado activo (valor común: ACTIVO)
            $stmt = $db->prepare("SELECT id, phone, cliente_id, estado, carrito_json, created_at, updated_at FROM tbcarritotemporal WHERE phone = :phone AND (estado = 'ACTIVO' OR estado = 'Activo') ORDER BY updated_at DESC LIMIT 1");
            $stmt->execute([':phone' => $phone]);
            $row = $stmt->fetch();
            if ($row) {
                return $row;
            }

            // fallback: retornar el más reciente para el teléfono
            $stmt2 = $db->prepare('SELECT id, phone, cliente_id, estado, carrito_json, created_at, updated_at FROM tbcarritotemporal WHERE phone = :phone ORDER BY updated_at DESC LIMIT 1');
            $stmt2->execute([':phone' => $phone]);
            $row2 = $stmt2->fetch();
            return $row2 ?: null;
        } catch (PDOException $e) {
            error_log('TbCarritoTemporalModel::buscarPorTelefono error: ' . $e->getMessage());
            return null;
        }
    }

    public static function obtenerFull(int $id): ?array
    {
        $carrito = self::obtenerPorId($id);
        if (!$carrito) {
            return null;
        }

        $items = WhatsappCarritosTemporalesItemsModel::obtenerPorCarrito($id);
        $carrito['items'] = $items;
        return $carrito;
    }
}
