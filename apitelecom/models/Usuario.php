<?php

require_once __DIR__ . '/../config/Database.php';

class Usuario
{
    public static function obtenerTodos(array $filtros, int $pagina, int $limite): array
    {
        $offset = ($pagina - 1) * $limite;
        $where = ['u.deleted_at IS NULL'];
        $params = [];

        if (!empty($filtros['nombre'])) {
            $where[] = '(u.nombre LIKE :nombre OR u.apellido LIKE :nombre)';
            $params[':nombre'] = '%' . $filtros['nombre'] . '%';
        }

        if (!empty($filtros['correo'])) {
            $where[] = 'u.correo LIKE :correo';
            $params[':correo'] = '%' . $filtros['correo'] . '%';
        }

        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $where[] = 'u.estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['rol_id'])) {
            $where[] = 'u.rol_id = :rol_id';
            $params[':rol_id'] = (int) $filtros['rol_id'];
        }

        $whereSql = implode(' AND ', $where);

        try {
            $db = Database::getConnection();
            $countStmt = $db->prepare("SELECT COUNT(*) FROM usuarios u WHERE $whereSql");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT u.id, u.rol_id, r.nombre AS rol_nombre, u.nombre, u.apellido, u.correo, u.telefono, u.estado, u.ultimo_login, u.created_at, u.updated_at FROM usuarios u LEFT JOIN roles r ON u.rol_id = r.id WHERE $whereSql ORDER BY u.created_at DESC LIMIT :limite OFFSET :offset";
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
                'data' => self::escapeData($stmt->fetchAll()),
            ];
        } catch (PDOException $e) {
            error_log('Usuario::obtenerTodos error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT u.id, u.rol_id, r.nombre AS rol_nombre, u.nombre, u.apellido, u.correo, u.telefono, u.estado, u.ultimo_login, u.created_at, u.updated_at FROM usuarios u LEFT JOIN roles r ON u.rol_id = r.id WHERE u.id = :id AND u.deleted_at IS NULL LIMIT 1');
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch();

            return $usuario ? self::escapeData($usuario) : null;
        } catch (PDOException $e) {
            error_log('Usuario::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function obtenerPorCorreo(string $correo): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT * FROM usuarios WHERE correo = :correo AND deleted_at IS NULL LIMIT 1');
            $stmt->execute([':correo' => $correo]);
            $usuario = $stmt->fetch();

            return $usuario ?: null;
        } catch (PDOException $e) {
            error_log('Usuario::obtenerPorCorreo error: ' . $e->getMessage());
            return null;
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            $estado = in_array($data['estado'] ?? '', ['Activo', 'Inactivo'], true) ? $data['estado'] : 'Activo';

            $stmt = $db->prepare('INSERT INTO usuarios (rol_id, nombre, apellido, correo, telefono, password, estado, ultimo_login, created_at) VALUES (:rol_id, :nombre, :apellido, :correo, :telefono, :password, :estado, :ultimo_login, NOW())');
            $stmt->execute([
                ':rol_id' => $data['rol_id'],
                ':nombre' => $data['nombre'],
                ':apellido' => $data['apellido'],
                ':correo' => $data['correo'],
                ':telefono' => $data['telefono'],
                ':password' => $passwordHash,
                ':estado' => $estado,
                ':ultimo_login' => null,
            ]);

            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Usuario::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function actualizar(int $id, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $fields = [];
            $params = [':id' => $id];

            if (array_key_exists('rol_id', $data)) {
                $fields[] = 'rol_id = :rol_id';
                $params[':rol_id'] = $data['rol_id'];
            }
            if (array_key_exists('nombre', $data)) {
                $fields[] = 'nombre = :nombre';
                $params[':nombre'] = $data['nombre'];
            }
            if (array_key_exists('apellido', $data)) {
                $fields[] = 'apellido = :apellido';
                $params[':apellido'] = $data['apellido'];
            }
            if (array_key_exists('correo', $data)) {
                $fields[] = 'correo = :correo';
                $params[':correo'] = $data['correo'];
            }
            if (array_key_exists('telefono', $data)) {
                $fields[] = 'telefono = :telefono';
                $params[':telefono'] = $data['telefono'];
            }
            if (array_key_exists('password', $data) && $data['password'] !== null) {
                $fields[] = 'password = :password';
                $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            if (array_key_exists('estado', $data)) {
                $fields[] = 'estado = :estado';
                $params[':estado'] = $data['estado'];
            }

            if (empty($fields)) {
                return true;
            }

            $sql = 'UPDATE usuarios SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id AND deleted_at IS NULL';
            $stmt = $db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Usuario::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE usuarios SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('Usuario::eliminar error: ' . $e->getMessage());
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
            $stmt = $db->prepare('UPDATE usuarios SET estado = :estado, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (PDOException $e) {
            error_log('Usuario::cambiarEstado error: ' . $e->getMessage());
            return false;
        }
    }

    public static function registrarLogin(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE usuarios SET ultimo_login = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('Usuario::registrarLogin error: ' . $e->getMessage());
            return false;
        }
    }

    public static function autenticar(string $correo, string $password): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT * FROM usuarios WHERE correo = :correo AND deleted_at IS NULL LIMIT 1');
            $stmt->execute([':correo' => $correo]);
            $usuario = $stmt->fetch();

            if (!$usuario || empty($usuario['password'])) {
                return null;
            }

            if (!password_verify($password, $usuario['password'])) {
                return null;
            }

            if (!self::registrarLogin((int) $usuario['id'])) {
                error_log('Usuario::autenticar warning: no se pudo actualizar ultimo_login para el usuario ' . $usuario['id']);
            }

            return self::obtenerPorId((int) $usuario['id']);
        } catch (PDOException $e) {
            error_log('Usuario::autenticar error: ' . $e->getMessage());
            return null;
        }
    }

    public static function existeCorreo(string $correo, ?int $idExcluir = null): bool
    {
        try {
            $sql = 'SELECT COUNT(*) FROM usuarios WHERE correo = :correo AND deleted_at IS NULL';
            if ($idExcluir !== null) {
                $sql .= ' AND id != :id';
            }

            $db = Database::getConnection();
            $stmt = $db->prepare($sql);
            $params = [':correo' => $correo];
            if ($idExcluir !== null) {
                $params[':id'] = $idExcluir;
            }

            $stmt->execute($params);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Usuario::existeCorreo error: ' . $e->getMessage());
            return false;
        }
    }

    private static function escapeData(array $data): array
    {
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        });

        return $data;
    }
}
