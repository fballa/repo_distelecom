<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Producto.php';

class Oferta
{
    public static function obtenerTodos(array $filtros, int $pagina, int $limite): array
    {
        $offset = ($pagina - 1) * $limite;
        $where = ['1 = 1'];
        $params = [];

        if (!empty($filtros['producto_id'])) {
            $where[] = 'o.producto_id = :producto_id';
            $params[':producto_id'] = (int) $filtros['producto_id'];
        }

        if (!empty($filtros['estado'])) {
            $where[] = 'o.estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['fecha_inicio_desde'])) {
            $where[] = 'o.fecha_inicio >= :fecha_inicio_desde';
            $params[':fecha_inicio_desde'] = $filtros['fecha_inicio_desde'];
        }

        if (!empty($filtros['fecha_inicio_hasta'])) {
            $where[] = 'o.fecha_inicio <= :fecha_inicio_hasta';
            $params[':fecha_inicio_hasta'] = $filtros['fecha_inicio_hasta'];
        }

        if (!empty($filtros['fecha_fin_desde'])) {
            $where[] = 'o.fecha_fin >= :fecha_fin_desde';
            $params[':fecha_fin_desde'] = $filtros['fecha_fin_desde'];
        }

        if (!empty($filtros['fecha_fin_hasta'])) {
            $where[] = 'o.fecha_fin <= :fecha_fin_hasta';
            $params[':fecha_fin_hasta'] = $filtros['fecha_fin_hasta'];
        }

        if (!empty($filtros['buscar'])) {
            $where[] = '(o.titulo LIKE :buscar OR o.descripcion LIKE :buscar)';
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }

        $whereSql = implode(' AND ', $where);

        try {
            $db = Database::getConnection();
            $countStmt = $db->prepare("SELECT COUNT(*) FROM ofertas o WHERE $whereSql");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT o.id, o.producto_id, o.titulo, o.descripcion, o.porcentaje, o.precio_oferta, o.fecha_inicio, o.fecha_fin, o.estado, o.created_at,
                           p.nombre AS producto_nombre, p.imagen_principal AS producto_imagen, p.precio AS producto_precio
                    FROM ofertas o
                    LEFT JOIN productos p ON o.producto_id = p.id
                    WHERE $whereSql
                    ORDER BY o.created_at DESC
                    LIMIT :limite OFFSET :offset";

            $stmt = $db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll();

            return [
                'total' => $total,
                'pagina' => $pagina,
                'limite' => $limite,
                'data' => self::escapeData($items),
            ];
        } catch (PDOException $e) {
            error_log('Oferta::obtenerTodos error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite, 'data' => []];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT o.*, p.nombre AS producto_nombre, p.imagen_principal AS producto_imagen, p.precio AS producto_precio FROM ofertas o LEFT JOIN productos p ON o.producto_id = p.id WHERE o.id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $oferta = $stmt->fetch();

            return $oferta ? self::escapeData($oferta) : null;
        } catch (PDOException $e) {
            error_log('Oferta::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function obtenerActivas(): array
    {
        $hoy = date('Y-m-d');

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT o.id, o.producto_id, o.titulo, o.descripcion, o.porcentaje, o.precio_oferta, o.fecha_inicio, o.fecha_fin, o.estado, o.created_at, p.nombre AS producto_nombre, p.imagen_principal AS producto_imagen, p.precio AS producto_precio FROM ofertas o LEFT JOIN productos p ON o.producto_id = p.id WHERE o.estado = :estado AND o.fecha_inicio <= :hoy AND o.fecha_fin >= :hoy ORDER BY o.fecha_inicio ASC');
            $stmt->execute([':estado' => 'Activa', ':hoy' => $hoy]);

            return self::escapeData($stmt->fetchAll());
        } catch (PDOException $e) {
            error_log('Oferta::obtenerActivas error: ' . $e->getMessage());
            return [];
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $producto = Producto::obtenerPorId($data['producto_id']);
            if (!$producto) {
                return null;
            }

            if ($data['precio_oferta'] === null && $data['porcentaje'] !== null && $producto['precio'] > 0) {
                $data['precio_oferta'] = round($producto['precio'] * (1 - $data['porcentaje'] / 100), 2);
            }

            if ($data['precio_oferta'] !== null && $producto['precio'] > 0) {
                $data['porcentaje'] = round((1 - ($data['precio_oferta'] / $producto['precio'])) * 100, 2);
            }

            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO ofertas (producto_id, titulo, descripcion, porcentaje, precio_oferta, fecha_inicio, fecha_fin, estado, created_at) VALUES (:producto_id, :titulo, :descripcion, :porcentaje, :precio_oferta, :fecha_inicio, :fecha_fin, :estado, NOW())');
            $stmt->execute([
                ':producto_id' => $data['producto_id'],
                ':titulo' => $data['titulo'],
                ':descripcion' => $data['descripcion'],
                ':porcentaje' => $data['porcentaje'],
                ':precio_oferta' => $data['precio_oferta'],
                ':fecha_inicio' => $data['fecha_inicio'],
                ':fecha_fin' => $data['fecha_fin'],
                ':estado' => $data['estado'],
            ]);

            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Oferta::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function actualizar(int $id, array $data): bool
    {
        try {
            $producto = Producto::obtenerPorId($data['producto_id']);
            if (!$producto) {
                return false;
            }

            if ($data['precio_oferta'] === null && $data['porcentaje'] !== null && $producto['precio'] > 0) {
                $data['precio_oferta'] = round($producto['precio'] * (1 - $data['porcentaje'] / 100), 2);
            }

            if ($data['precio_oferta'] !== null && $producto['precio'] > 0) {
                $data['porcentaje'] = round((1 - ($data['precio_oferta'] / $producto['precio'])) * 100, 2);
            }

            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE ofertas SET producto_id = :producto_id, titulo = :titulo, descripcion = :descripcion, porcentaje = :porcentaje, precio_oferta = :precio_oferta, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, estado = :estado WHERE id = :id');
            return $stmt->execute([
                ':producto_id' => $data['producto_id'],
                ':titulo' => $data['titulo'],
                ':descripcion' => $data['descripcion'],
                ':porcentaje' => $data['porcentaje'],
                ':precio_oferta' => $data['precio_oferta'],
                ':fecha_inicio' => $data['fecha_inicio'],
                ':fecha_fin' => $data['fecha_fin'],
                ':estado' => $data['estado'],
                ':id' => $id,
            ]);
        } catch (PDOException $e) {
            error_log('Oferta::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE ofertas SET estado = :estado WHERE id = :id');
            return $stmt->execute([':estado' => 'Finalizada', ':id' => $id]);
        } catch (PDOException $e) {
            error_log('Oferta::eliminar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function cambiarEstado(int $id, string $estado): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE ofertas SET estado = :estado WHERE id = :id');
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (PDOException $e) {
            error_log('Oferta::cambiarEstado error: ' . $e->getMessage());
            return false;
        }
    }

    public static function existeOfertaParaProducto(int $productoId, ?int $idExcluir = null, ?string $fechaInicio = null, ?string $fechaFin = null): bool
    {
        try {
            $sql = 'SELECT COUNT(*) FROM ofertas WHERE producto_id = :producto_id AND estado IN ("Activa", "Programada")';
            $params = [':producto_id' => $productoId];

            if ($fechaInicio !== null && $fechaFin !== null) {
                $sql .= ' AND fecha_inicio <= :fecha_fin AND fecha_fin >= :fecha_inicio';
                $params[':fecha_fin'] = $fechaFin;
                $params[':fecha_inicio'] = $fechaInicio;
            }

            if ($idExcluir !== null) {
                $sql .= ' AND id != :id';
                $params[':id'] = $idExcluir;
            }

            $db = Database::getConnection();
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Oferta::existeOfertaParaProducto error: ' . $e->getMessage());
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
