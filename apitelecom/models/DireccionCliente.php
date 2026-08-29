<?php

require_once __DIR__ . '/../config/Database.php';

class DireccionCliente
{
    public static function obtenerPorCliente(int $clienteId): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT dc.id, dc.cliente_id, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido, dc.pais, dc.departamento, dc.ciudad, dc.direccion, dc.referencia, dc.principal, dc.created_at FROM direcciones_cliente dc LEFT JOIN clientes c ON dc.cliente_id = c.id WHERE dc.cliente_id = :cliente_id');
            $stmt->execute([':cliente_id' => $clienteId]);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('DireccionCliente::obtenerPorCliente error: ' . $e->getMessage());
            return [];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, cliente_id, pais, departamento, ciudad, direccion, referencia, principal, created_at FROM direcciones_cliente WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $direccion = $stmt->fetch();

            return $direccion ?: null;
        } catch (PDOException $e) {
            error_log('DireccionCliente::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function obtenerPrincipal(int $clienteId): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, cliente_id, pais, departamento, ciudad, direccion, referencia, principal, created_at FROM direcciones_cliente WHERE cliente_id = :cliente_id AND principal = 1 LIMIT 1');
            $stmt->execute([':cliente_id' => $clienteId]);
            $direccion = $stmt->fetch();

            return $direccion ?: null;
        } catch (PDOException $e) {
            error_log('DireccionCliente::obtenerPrincipal error: ' . $e->getMessage());
            return null;
        }
    }

    public static function crear(array $data): ?int
    {
        $db = null;

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $principal = !empty($data['principal']) ? 1 : 0;
            if ($principal === 1) {
                $resetStmt = $db->prepare('UPDATE direcciones_cliente SET principal = 0 WHERE cliente_id = :cliente_id');
                $resetStmt->execute([':cliente_id' => $data['cliente_id']]);
            }

            $stmt = $db->prepare('INSERT INTO direcciones_cliente (cliente_id, pais, departamento, ciudad, direccion, referencia, principal, created_at) VALUES (:cliente_id, :pais, :departamento, :ciudad, :direccion, :referencia, :principal, NOW())');
            $stmt->execute([
                ':cliente_id' => $data['cliente_id'],
                ':pais' => $data['pais'],
                ':departamento' => $data['departamento'],
                ':ciudad' => $data['ciudad'],
                ':direccion' => $data['direccion'],
                ':referencia' => $data['referencia'],
                ':principal' => $principal,
            ]);

            $direccionId = (int) $db->lastInsertId();
            $db->commit();

            return $direccionId;
        } catch (PDOException $e) {
            error_log('DireccionCliente::crear error: ' . $e->getMessage());
            if ($db instanceof PDO && $db->inTransaction()) {
                $db->rollBack();
            }
            return null;
        }
    }

    public static function actualizar(int $id, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $direccionExistente = self::obtenerPorId($id);

            if (!$direccionExistente) {
                return false;
            }

            $db->beginTransaction();
            $fields = [];
            $params = [':id' => $id];

            if (array_key_exists('pais', $data)) {
                $fields[] = 'pais = :pais';
                $params[':pais'] = $data['pais'];
            }
            if (array_key_exists('departamento', $data)) {
                $fields[] = 'departamento = :departamento';
                $params[':departamento'] = $data['departamento'];
            }
            if (array_key_exists('ciudad', $data)) {
                $fields[] = 'ciudad = :ciudad';
                $params[':ciudad'] = $data['ciudad'];
            }
            if (array_key_exists('direccion', $data)) {
                $fields[] = 'direccion = :direccion';
                $params[':direccion'] = $data['direccion'];
            }
            if (array_key_exists('referencia', $data)) {
                $fields[] = 'referencia = :referencia';
                $params[':referencia'] = $data['referencia'];
            }
            if (array_key_exists('principal', $data)) {
                $principal = !empty($data['principal']) ? 1 : 0;
                if ($principal === 1) {
                    $resetStmt = $db->prepare('UPDATE direcciones_cliente SET principal = 0 WHERE cliente_id = :cliente_id');
                    $resetStmt->execute([':cliente_id' => $direccionExistente['cliente_id']]);
                }
                $fields[] = 'principal = :principal';
                $params[':principal'] = $principal;
            }

            if (!empty($fields)) {
                $sql = 'UPDATE direcciones_cliente SET ' . implode(', ', $fields) . ' WHERE id = :id';
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
            }

            $db->commit();
            return true;
        } catch (PDOException $e) {
            error_log('DireccionCliente::actualizar error: ' . $e->getMessage());
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('DELETE FROM direcciones_cliente WHERE id = :id');
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('DireccionCliente::eliminar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function marcarPrincipal(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $direccion = self::obtenerPorId($id);

            if (!$direccion) {
                return false;
            }

            $db->beginTransaction();
            $resetStmt = $db->prepare('UPDATE direcciones_cliente SET principal = 0 WHERE cliente_id = :cliente_id');
            $resetStmt->execute([':cliente_id' => $direccion['cliente_id']]);

            $stmt = $db->prepare('UPDATE direcciones_cliente SET principal = 1 WHERE id = :id');
            $stmt->execute([':id' => $id]);

            $db->commit();
            return true;
        } catch (PDOException $e) {
            error_log('DireccionCliente::marcarPrincipal error: ' . $e->getMessage());
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            return false;
        }
    }
}
