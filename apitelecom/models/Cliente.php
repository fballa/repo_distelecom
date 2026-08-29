<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/DireccionCliente.php';

class Cliente
{
    public static function obtenerTodos(array $filtros, int $pagina, int $limite): array
    {
        $offset = ($pagina - 1) * $limite;
        $where = ['c.deleted_at IS NULL'];
        $params = [];

        if (!empty($filtros['nombre'])) {
            $where[] = '(c.nombre LIKE :nombre OR c.apellido LIKE :nombre)';
            $params[':nombre'] = '%' . $filtros['nombre'] . '%';
        }

        if (!empty($filtros['correo'])) {
            $where[] = 'c.correo LIKE :correo';
            $params[':correo'] = '%' . $filtros['correo'] . '%';
        }

        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $where[] = 'c.estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }

        $whereSql = implode(' AND ', $where);

        try {
            $db = Database::getConnection();
            $countStmt = $db->prepare("SELECT COUNT(*) FROM clientes c WHERE $whereSql");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT c.id, c.uuid, c.nombre, c.apellido, c.empresa, c.correo, c.telefono, c.documento, c.estado, c.created_at, c.updated_at, 
                    (SELECT COUNT(*) FROM pedidos pd WHERE pd.cliente_id = c.id) AS pedidos_count
                    FROM clientes c
                    WHERE $whereSql
                    ORDER BY c.created_at DESC
                    LIMIT :limite OFFSET :offset";

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
            error_log('Cliente::obtenerTodos error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, uuid, nombre, apellido, empresa, correo, telefono, documento, estado, created_at, updated_at FROM clientes WHERE id = :id AND deleted_at IS NULL LIMIT 1');
            $stmt->execute([':id' => $id]);
            $cliente = $stmt->fetch();

            if (!$cliente) {
                return null;
            }

            $cliente['direcciones'] = DireccionCliente::obtenerPorCliente($id);
            return $cliente;
        } catch (PDOException $e) {
            error_log('Cliente::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function obtenerPorCorreo(string $correo): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT * FROM clientes WHERE correo = :correo AND deleted_at IS NULL LIMIT 1');
            $stmt->execute([':correo' => $correo]);
            $cliente = $stmt->fetch();

            return $cliente ?: null;
        } catch (PDOException $e) {
            error_log('Cliente::obtenerPorCorreo error: ' . $e->getMessage());
            return null;
        }
    }

    public static function obtenerPorTelefono(string $telefono): ?array
    {
        $telefono = trim($telefono);
        if ($telefono === '') {
            return null;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, uuid, nombre, apellido, empresa, correo, telefono, documento, estado, created_at, updated_at FROM clientes WHERE telefono = :telefono AND deleted_at IS NULL LIMIT 1');
            $stmt->execute([':telefono' => $telefono]);
            $cliente = $stmt->fetch();

            if (!$cliente) {
                return null;
            }

            $cliente['direcciones'] = DireccionCliente::obtenerPorCliente((int) $cliente['id']);
            return $cliente;
        } catch (PDOException $e) {
            error_log('Cliente::obtenerPorTelefono error: ' . $e->getMessage());
            return null;
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $uuid = !empty($data['uuid']) ? $data['uuid'] : self::generarUuid();
            $estado = in_array($data['estado'] ?? '', ['Activo', 'Inactivo'], true) ? $data['estado'] : 'Activo';
            $password = null;

            if (!empty($data['password'])) {
                $password = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            $stmt = $db->prepare('INSERT INTO clientes (uuid, nombre, apellido, empresa, correo, telefono, documento, password, estado, created_at) VALUES (:uuid, :nombre, :apellido, :empresa, :correo, :telefono, :documento, :password, :estado, NOW())');
            $stmt->execute([
                ':uuid' => $uuid,
                ':nombre' => $data['nombre'],
                ':apellido' => $data['apellido'],
                ':empresa' => $data['empresa'],
                ':correo' => $data['correo'],
                ':telefono' => $data['telefono'],
                ':documento' => $data['documento'],
                ':password' => $password,
                ':estado' => $estado,
            ]);

            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Cliente::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function actualizar(int $id, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $fields = [];
            $params = [':id' => $id];

            if (array_key_exists('nombre', $data)) {
                $fields[] = 'nombre = :nombre';
                $params[':nombre'] = $data['nombre'];
            }
            if (array_key_exists('apellido', $data)) {
                $fields[] = 'apellido = :apellido';
                $params[':apellido'] = $data['apellido'];
            }
            if (array_key_exists('empresa', $data)) {
                $fields[] = 'empresa = :empresa';
                $params[':empresa'] = $data['empresa'];
            }
            if (array_key_exists('correo', $data)) {
                $fields[] = 'correo = :correo';
                $params[':correo'] = $data['correo'];
            }
            if (array_key_exists('telefono', $data)) {
                $fields[] = 'telefono = :telefono';
                $params[':telefono'] = $data['telefono'];
            }
            if (array_key_exists('documento', $data)) {
                $fields[] = 'documento = :documento';
                $params[':documento'] = $data['documento'];
            }
            if (array_key_exists('password', $data)) {
                $fields[] = 'password = :password';
                $params[':password'] = !empty($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : null;
            }
            if (array_key_exists('estado', $data)) {
                $fields[] = 'estado = :estado';
                $params[':estado'] = $data['estado'];
            }

            if (empty($fields)) {
                return true;
            }

            $sql = 'UPDATE clientes SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id AND deleted_at IS NULL';
            $stmt = $db->prepare($sql);

            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Cliente::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE clientes SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('Cliente::eliminar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function cambiarEstado(int $id, string $estado): bool
    {
        if (!in_array($estado, ['Activo', 'Inactivo'], true)) {
            return false;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE clientes SET estado = :estado, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (PDOException $e) {
            error_log('Cliente::cambiarEstado error: ' . $e->getMessage());
            return false;
        }
    }

    public static function autenticar(string $correo, string $password): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT * FROM clientes WHERE correo = :correo AND deleted_at IS NULL LIMIT 1');
            $stmt->execute([':correo' => $correo]);
            $cliente = $stmt->fetch();

            if (!$cliente || empty($cliente['password'])) {
                return null;
            }

            if (!password_verify($password, $cliente['password'])) {
                return null;
            }

            unset($cliente['password']);
            return $cliente;
        } catch (PDOException $e) {
            error_log('Cliente::autenticar error: ' . $e->getMessage());
            return null;
        }
    }

    public static function existeCorreo(string $correo, ?int $idExcluir = null): bool
    {
        try {
            $db = Database::getConnection();
            $sql = 'SELECT COUNT(*) FROM clientes WHERE correo = :correo AND deleted_at IS NULL';
            if ($idExcluir !== null) {
                $sql .= ' AND id != :id_excluir';
            }

            $stmt = $db->prepare($sql);
            $params = [':correo' => $correo];
            if ($idExcluir !== null) {
                $params[':id_excluir'] = $idExcluir;
            }
            $stmt->execute($params);

            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Cliente::existeCorreo error: ' . $e->getMessage());
            return false;
        }
    }

    public static function generarUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
