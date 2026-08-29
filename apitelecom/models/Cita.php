<?php

require_once __DIR__ . '/../config/Database.php';

class Cita
{
    public static function obtenerTodos(array $filtros, int $pagina, int $limite): array
    {
        $offset = ($pagina - 1) * $limite;
        $where = ['1=1'];
        $params = [];

        if (!empty($filtros['cliente_id'])) {
            $where[] = 'citas.cliente_id = :cliente_id';
            $params[':cliente_id'] = $filtros['cliente_id'];
        }

        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $where[] = 'citas.estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['tipo_cita'])) {
            $where[] = 'citas.tipo_cita = :tipo_cita';
            $params[':tipo_cita'] = $filtros['tipo_cita'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $where[] = 'citas.fecha_cita >= :fecha_desde';
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $where[] = 'citas.fecha_cita <= :fecha_hasta';
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        // Filtrar por teléfono del cliente (tabla clientes)
        if (!empty($filtros['telefono'])) {
            $where[] = 'cl.telefono LIKE :telefono';
            $params[':telefono'] = '%' . $filtros['telefono'] . '%';
        }

        if (!empty($filtros['buscar'])) {
            $where[] = '(citas.cliente_id LIKE :buscar OR citas.notas LIKE :buscar)';
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }

        $whereSql = implode(' AND ', $where);

        try {
            $db = Database::getConnection();

            // Count total (usar LEFT JOIN con clientes para permitir filtro por telefono)
            $countSql = "SELECT COUNT(*) FROM citas LEFT JOIN clientes cl ON cl.uuid = citas.cliente_id WHERE $whereSql";
            $countStmt = $db->prepare($countSql);
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $countStmt->execute();
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT
                        citas.id,
                        citas.phone,
                        citas.cliente_id,
                        CONCAT(COALESCE(cl.nombre, ''), ' ', COALESCE(cl.apellido, '')) AS cliente_nombre,
                        cl.correo AS cliente_correo,
                        cl.telefono AS cliente_telefono,
                        citas.fecha_cita,
                        citas.hora_cita,
                        citas.tipo_cita,
                        citas.estado,
                        citas.notas,
                        citas.recordatorio_24h,
                        citas.recordatorio_1h,
                        citas.fecha_creacion
                    FROM citas
                    LEFT JOIN clientes cl ON cl.id = citas.cliente_id
                    WHERE $whereSql
                    ORDER BY citas.fecha_cita ASC
                    LIMIT :limite OFFSET :offset";

            $stmt = $db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $data = $stmt->fetchAll();

            return [
                'total' => $total,
                'pagina' => $pagina,
                'limite' => $limite,
                'data' => $data,
            ];
        } catch (PDOException $e) {
            error_log('Cita::obtenerTodos error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $sql = "SELECT
                        citas.id,
                        citas.phone,
                        citas.cliente_id,
                        CONCAT(COALESCE(cl.nombre, ''), ' ', COALESCE(cl.apellido, '')) AS cliente_nombre,
                        cl.correo AS cliente_correo,
                        cl.telefono AS cliente_telefono,
                        citas.fecha_cita,
                        citas.hora_cita,
                        citas.tipo_cita,
                        citas.estado,
                        citas.notas,
                        citas.recordatorio_24h,
                        citas.recordatorio_1h,
                        citas.fecha_creacion
                    FROM citas
                    LEFT JOIN clientes cl ON cl.id = citas.cliente_id
                    WHERE citas.id = :id
                    LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $cita = $stmt->fetch();

            return $cita ?: null;
        } catch (PDOException $e) {
            error_log('Cita::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function obtenerPorCliente(string $cliente_id, int $pagina, int $limite): array
    {
        $offset = ($pagina - 1) * $limite;
        try {
            $db = Database::getConnection();
            $countStmt = $db->prepare('SELECT COUNT(*) FROM citas WHERE cliente_id = :cliente_id');
            $countStmt->execute([':cliente_id' => $cliente_id]);
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT
                        citas.id,
                        citas.phone,
                        citas.cliente_id,
                        CONCAT(COALESCE(cl.nombre, ''), ' ', COALESCE(cl.apellido, '')) AS cliente_nombre,
                        cl.correo AS cliente_correo,
                        cl.telefono AS cliente_telefono,
                        citas.fecha_cita,
                        citas.hora_cita,
                        citas.tipo_cita,
                        citas.estado,
                        citas.notas,
                        citas.recordatorio_24h,
                        citas.recordatorio_1h,
                        citas.fecha_creacion
                    FROM citas
                    LEFT JOIN clientes cl ON cl.id = citas.cliente_id
                    WHERE citas.cliente_id = :cliente_id
                    ORDER BY citas.fecha_cita ASC
                    LIMIT :limite OFFSET :offset";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':cliente_id', $cliente_id, PDO::PARAM_STR);
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
            error_log('Cita::obtenerPorCliente error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();

            // Validar existencia de cliente
            if (!self::existeCliente($data['cliente_id'])) {
                return null;
            }

            $estado = isset($data['estado']) && in_array($data['estado'], ['Pendiente', 'Confirmada', 'Cancelada', 'Completada'], true) ? $data['estado'] : 'Pendiente';
            $recordatorio_24h = isset($data['recordatorio_24h']) ? (int) $data['recordatorio_24h'] : 0;
            $recordatorio_1h = isset($data['recordatorio_1h']) ? (int) $data['recordatorio_1h'] : 0;

            $stmt = $db->prepare('INSERT INTO citas (phone, cliente_id, fecha_cita, hora_cita, tipo_cita, estado, notas, recordatorio_24h, recordatorio_1h, fecha_creacion) VALUES (:phone, :cliente_id, :fecha_cita, :hora_cita, :tipo_cita, :estado, :notas, :recordatorio_24h, :recordatorio_1h, NOW())');

            $stmt->execute([
                ':phone' => $data['phone'] ?? null,
                ':cliente_id' => $data['cliente_id'],
                ':fecha_cita' => $data['fecha_cita'] ?? null,
                ':hora_cita' => $data['hora_cita'] ?? null,
                ':tipo_cita' => $data['tipo_cita'] ?? null,
                ':estado' => $estado,
                ':notas' => $data['notas'] ?? null,
                ':recordatorio_24h' => $recordatorio_24h,
                ':recordatorio_1h' => $recordatorio_1h,
            ]);

            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Cita::crear error: ' . $e->getMessage());
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
                if (!self::existeCliente($data['cliente_id'])) {
                    return false;
                }
                $fields[] = 'cliente_id = :cliente_id';
                $params[':cliente_id'] = $data['cliente_id'];
            }
            if (array_key_exists('fecha_cita', $data)) {
                $fields[] = 'fecha_cita = :fecha_cita';
                $params[':fecha_cita'] = $data['fecha_cita'];
            }
            if (array_key_exists('hora_cita', $data)) {
                $fields[] = 'hora_cita = :hora_cita';
                $params[':hora_cita'] = $data['hora_cita'];
            }
            if (array_key_exists('tipo_cita', $data)) {
                $fields[] = 'tipo_cita = :tipo_cita';
                $params[':tipo_cita'] = $data['tipo_cita'];
            }
            if (array_key_exists('estado', $data)) {
                $fields[] = 'estado = :estado';
                $params[':estado'] = $data['estado'];
            }
            if (array_key_exists('notas', $data)) {
                $fields[] = 'notas = :notas';
                $params[':notas'] = $data['notas'];
            }
            if (array_key_exists('recordatorio_24h', $data)) {
                $fields[] = 'recordatorio_24h = :recordatorio_24h';
                $params[':recordatorio_24h'] = (int) $data['recordatorio_24h'];
            }
            if (array_key_exists('recordatorio_1h', $data)) {
                $fields[] = 'recordatorio_1h = :recordatorio_1h';
                $params[':recordatorio_1h'] = (int) $data['recordatorio_1h'];
            }

            if (empty($fields)) {
                return true;
            }

            $sql = 'UPDATE citas SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $stmt = $db->prepare($sql);

            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Cita::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE citas SET estado = 'Cancelada' WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('Cita::eliminar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function cambiarEstado(int $id, string $estado): bool
    {
        if (!in_array($estado, ['Pendiente', 'Confirmada', 'Cancelada', 'Completada'], true)) {
            return false;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE citas SET estado = :estado WHERE id = :id');
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (PDOException $e) {
            error_log('Cita::cambiarEstado error: ' . $e->getMessage());
            return false;
        }
    }

    public static function existeCliente(string $cliente_id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(*) FROM clientes WHERE id = :uuid AND deleted_at IS NULL');
            $stmt->execute([':uuid' => $cliente_id]);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Cita::existeCliente error: ' . $e->getMessage());
            return false;
        }
    }

    public static function obtenerRecordatorios(): array
    {
        try {
            $db = Database::getConnection();
            $sql = "SELECT
                        c.id AS cita_id,
                        CONCAT(COALESCE(cl.nombre, ''), ' ', COALESCE(cl.apellido, '')) AS cliente_nombre,
                        cl.telefono AS cliente_telefono,
                        CONCAT(c.fecha_cita, ' ', c.hora_cita) AS fecha_hora_completa,
                        TIMESTAMPDIFF(HOUR, NOW(), CONCAT(c.fecha_cita, ' ', c.hora_cita)) AS horas_restantes,
                        TIMESTAMPDIFF(MINUTE, NOW(), CONCAT(c.fecha_cita, ' ', c.hora_cita)) AS minutos_restantes,
                        c.tipo_cita,
                        c.estado,
                        c.recordatorio_24h,
                        c.recordatorio_1h,
                        c.notas
                    FROM citas c
                    INNER JOIN clientes cl ON c.cliente_id = cl.id
                    WHERE
                        CONCAT(c.fecha_cita, ' ', c.hora_cita) > NOW()
                        AND (
                            (TIMESTAMPDIFF(HOUR, NOW(), CONCAT(c.fecha_cita, ' ', c.hora_cita)) <= 24 AND c.recordatorio_24h = 0)
                            OR
                            (TIMESTAMPDIFF(MINUTE, NOW(), CONCAT(c.fecha_cita, ' ', c.hora_cita)) <= 60 AND c.recordatorio_1h = 0)
                        )
                        AND LOWER(c.estado) IN ('pendiente','confirmada')
                    ORDER BY CONCAT(c.fecha_cita, ' ', c.hora_cita) ASC";

            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Cita::obtenerRecordatorios error: ' . $e->getMessage());
            return [];
        }
    }

    public static function marcarRecordatorio(int $id, string $tipo): bool
    {
        if (!in_array($tipo, ['24h', '1h'], true)) {
            return false;
        }

        $field = $tipo === '24h' ? 'recordatorio_24h' : 'recordatorio_1h';

        try {
            $db = Database::getConnection();
            $sql = "UPDATE citas SET {$field} = 1 WHERE id = :id";
            $stmt = $db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('Cita::marcarRecordatorio error: ' . $e->getMessage());
            return false;
        }
    }
}
