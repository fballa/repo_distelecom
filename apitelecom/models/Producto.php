<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/ImagenProducto.php';
require_once __DIR__ . '/EspecificacionProducto.php';
require_once __DIR__ . '/Inventario.php';

class Producto
{
    public static function obtenerTodos(array $filtros, int $pagina, int $limite): array
    {
        $offset = ($pagina - 1) * $limite;
        $where = ['p.deleted_at IS NULL'];
        $params = [];

        if (!empty($filtros['categoria_id'])) {
            $where[] = 'p.categoria_id = :categoria_id';
            $params[':categoria_id'] = (int) $filtros['categoria_id'];
        }

        if (!empty($filtros['marca_id'])) {
            $where[] = 'p.marca_id = :marca_id';
            $params[':marca_id'] = (int) $filtros['marca_id'];
        }

        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $where[] = 'p.estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }

        if (isset($filtros['destacado']) && $filtros['destacado'] !== '') {
            $where[] = 'p.destacado = :destacado';
            $params[':destacado'] = (int) $filtros['destacado'];
        }

        if (isset($filtros['nuevo']) && $filtros['nuevo'] !== '') {
            $where[] = 'p.nuevo = :nuevo';
            $params[':nuevo'] = (int) $filtros['nuevo'];
        }

        if (isset($filtros['oferta']) && $filtros['oferta'] !== '') {
            $where[] = 'p.oferta = :oferta';
            $params[':oferta'] = (int) $filtros['oferta'];
        }

        if (isset($filtros['buscar']) && trim((string) $filtros['buscar']) !== '') {
            $buscar = '%' . trim((string) $filtros['buscar']) . '%';
            $where[] = '(LOWER(p.nombre) LIKE LOWER(:buscar_nombre) OR LOWER(p.sku) LIKE LOWER(:buscar_sku) OR LOWER(p.modelo) LIKE LOWER(:buscar_modelo))';
            $params[':buscar_nombre'] = $buscar;
            $params[':buscar_sku'] = $buscar;
            $params[':buscar_modelo'] = $buscar;
        }

        $whereSql = implode(' AND ', $where);

        try {
            $db = Database::getConnection();
            $countStmt = $db->prepare("SELECT COUNT(*) FROM productos p WHERE $whereSql");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $sql = "SELECT p.id, p.uuid, p.categoria_id, c.nombre AS categoria, p.marca_id, m.nombre AS marca, p.sku, p.codigo_barras, p.nombre, p.slug, p.modelo, p.descripcion_corta, p.descripcion_larga, p.precio, p.precio_oferta, p.stock, p.stock_minimo, p.peso, p.alto, p.ancho, p.profundidad, p.garantia, p.imagen_principal, p.destacado, p.nuevo, p.oferta, p.estado, p.seo_title, p.seo_description, p.created_at, p.updated_at
                    FROM productos p
                    LEFT JOIN categorias c ON p.categoria_id = c.id
                    LEFT JOIN marcas m ON p.marca_id = m.id
                    WHERE $whereSql
                    ORDER BY p.created_at DESC
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
            error_log('Producto::obtenerTodos error: ' . $e->getMessage());
            return ['total' => 0, 'pagina' => $pagina, 'limite' => $limite,'data' => []];
        }
    }

    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT p.*, c.nombre AS categoria, m.nombre AS marca FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id LEFT JOIN marcas m ON p.marca_id = m.id WHERE p.id = :id AND p.deleted_at IS NULL LIMIT 1');
            $stmt->execute([':id' => $id]);
            $producto = $stmt->fetch();

            if (!$producto) {
                return null;
            }

            $producto['imagenes'] = ImagenProducto::obtenerPorProducto($id);
            $producto['especificaciones'] = EspecificacionProducto::obtenerPorProducto($id);
            $producto['inventario'] = Inventario::obtenerPorProducto($id);

            return $producto;
        } catch (PDOException $e) {
            error_log('Producto::obtenerPorId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            if (empty($data['uuid'])) {
                $data['uuid'] = self::generarUuid();
            }

            if (empty($data['slug']) && !empty($data['nombre'])) {
                $data['slug'] = self::generarSlug($data['nombre']);
            }

            $stmt = $db->prepare('INSERT INTO productos (uuid, categoria_id, marca_id, sku, codigo_barras, nombre, slug, modelo, descripcion_corta, descripcion_larga, precio, precio_oferta, stock, stock_minimo, peso, alto, ancho, profundidad, garantia, imagen_principal, destacado, nuevo, oferta, estado, seo_title, seo_description, created_at, updated_at) VALUES (:uuid, :categoria_id, :marca_id, :sku, :codigo_barras, :nombre, :slug, :modelo, :descripcion_corta, :descripcion_larga, :precio, :precio_oferta, :stock, :stock_minimo, :peso, :alto, :ancho, :profundidad, :garantia, :imagen_principal, :destacado, :nuevo, :oferta, :estado, :seo_title, :seo_description, NOW(), NOW())');
            $stmt->execute([
                ':uuid' => $data['uuid'],
                ':categoria_id' => $data['categoria_id'],
                ':marca_id' => $data['marca_id'],
                ':sku' => $data['sku'],
                ':codigo_barras' => $data['codigo_barras'],
                ':nombre' => $data['nombre'],
                ':slug' => $data['slug'],
                ':modelo' => $data['modelo'],
                ':descripcion_corta' => $data['descripcion_corta'],
                ':descripcion_larga' => $data['descripcion_larga'],
                ':precio' => $data['precio'],
                ':precio_oferta' => $data['precio_oferta'],
                ':stock' => $data['stock'],
                ':stock_minimo' => $data['stock_minimo'],
                ':peso' => $data['peso'],
                ':alto' => $data['alto'],
                ':ancho' => $data['ancho'],
                ':profundidad' => $data['profundidad'],
                ':garantia' => $data['garantia'],
                ':imagen_principal' => $data['imagen_principal'],
                ':destacado' => $data['destacado'],
                ':nuevo' => $data['nuevo'],
                ':oferta' => $data['oferta'],
                ':estado' => $data['estado'],
                ':seo_title' => $data['seo_title'],
                ':seo_description' => $data['seo_description'],
            ]);

            $productoId = (int) $db->lastInsertId();

            if (!empty($data['imagenes'])) {
                ImagenProducto::guardarImagenes($productoId, $data['imagenes']);
            }

            if (!empty($data['especificaciones'])) {
                EspecificacionProducto::guardarEspecificacion($productoId, $data['especificaciones']);
            }

            if (array_key_exists('inventario', $data) && is_array($data['inventario'])) {
                $inventario = $data['inventario'];
                $stock = isset($inventario['stock_actual']) ? (int) $inventario['stock_actual'] : $data['stock'];
                $stockMinimo = isset($inventario['stock_minimo']) ? (int) $inventario['stock_minimo'] : $data['stock_minimo'];
                Inventario::actualizarStock($productoId, $stock, $stockMinimo);
            } elseif (isset($data['stock']) || isset($data['stock_minimo'])) {
                Inventario::actualizarStock($productoId, $data['stock'], $data['stock_minimo']);
            }

            $db->commit();
            return $productoId;
        } catch (PDOException $e) {
            $db->rollBack();
            error_log('Producto::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function actualizar(int $id, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $updatableColumns = [
                'categoria_id',
                'marca_id',
                'sku',
                'codigo_barras',
                'nombre',
                'slug',
                'modelo',
                'descripcion_corta',
                'descripcion_larga',
                'precio',
                'precio_oferta',
                'stock',
                'stock_minimo',
                'peso',
                'alto',
                'ancho',
                'profundidad',
                'garantia',
                'imagen_principal',
                'destacado',
                'nuevo',
                'oferta',
                'estado',
                'seo_title',
                'seo_description',
            ];

            $fields = [];
            $params = [':id' => $id];

            foreach ($updatableColumns as $column) {
                if (array_key_exists($column, $data)) {
                    $fields[] = "$column = :$column";
                    $params[':' . $column] = $data[$column];
                }
            }

            if (!empty($fields)) {
                $sql = sprintf('UPDATE productos SET %s, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL', implode(', ', $fields));
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
            }

            if (array_key_exists('imagenes', $data)) {
                ImagenProducto::guardarImagenes($id, $data['imagenes']);
            }

            if (array_key_exists('especificaciones', $data)) {
                EspecificacionProducto::actualizarEspecificacion($id, $data['especificaciones']);
            }

            if (array_key_exists('inventario', $data) && is_array($data['inventario'])) {
                $inventario = $data['inventario'];
                $stock = isset($inventario['stock_actual']) ? (int) $inventario['stock_actual'] : null;
                $stockMinimo = isset($inventario['stock_minimo']) ? (int) $inventario['stock_minimo'] : null;
                Inventario::actualizarStock($id, $stock, $stockMinimo);
            } elseif (array_key_exists('stock', $data) || array_key_exists('stock_minimo', $data)) {
                Inventario::actualizarStock($id, $data['stock'] ?? null, $data['stock_minimo'] ?? null);
            }

            $db->commit();
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            error_log('Producto::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function eliminar(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE productos SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('Producto::eliminar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function cambiarEstado(int $id, string $estado): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE productos SET estado = :estado, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (PDOException $e) {
            error_log('Producto::cambiarEstado error: ' . $e->getMessage());
            return false;
        }
    }

    public static function cambiarDestacado(int $id, int $valor): bool
    {
        return self::cambiarFlag($id, 'destacado', $valor);
    }

    public static function cambiarNuevo(int $id, int $valor): bool
    {
        return self::cambiarFlag($id, 'nuevo', $valor);
    }

    public static function cambiarOferta(int $id, int $valor): bool
    {
        return self::cambiarFlag($id, 'oferta', $valor);
    }

    public static function actualizarImagenes(int $id, array $imagenes): bool
    {
        return ImagenProducto::guardarImagenes($id, $imagenes);
    }

    public static function actualizarEspecificacion(int $id, array $especificaciones): bool
    {
        return EspecificacionProducto::actualizarEspecificacion($id, $especificaciones);
    }

    public static function actualizarInventario(int $id, ?int $stock, ?int $stockMinimo): bool
    {
        return Inventario::actualizarStock($id, $stock, $stockMinimo);
    }

    public static function existeSku(string $sku, ?int $idExcluir = null): bool
    {
        try {
            if (trim($sku) === '') {
                return false;
            }

            $db = Database::getConnection();
            $sql = 'SELECT COUNT(*) FROM productos WHERE sku = :sku AND deleted_at IS NULL';
            if ($idExcluir !== null) {
                $sql .= ' AND id != :id';
            }

            $stmt = $db->prepare($sql);
            $params = [':sku' => $sku];
            if ($idExcluir !== null) {
                $params[':id'] = $idExcluir;
            }
            $stmt->execute($params);

            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Producto::existeSku error: ' . $e->getMessage());
            return false;
        }
    }

    public static function existeSlug(string $slug, ?int $idExcluir = null): bool
    {
        try {
            if (trim($slug) === '') {
                return false;
            }

            $db = Database::getConnection();
            $sql = 'SELECT COUNT(*) FROM productos WHERE slug = :slug AND deleted_at IS NULL';
            if ($idExcluir !== null) {
                $sql .= ' AND id != :id';
            }

            $stmt = $db->prepare($sql);
            $params = [':slug' => $slug];
            if ($idExcluir !== null) {
                $params[':id'] = $idExcluir;
            }
            $stmt->execute($params);

            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Producto::existeSlug error: ' . $e->getMessage());
            return false;
        }
    }

    private static function cambiarFlag(int $id, string $campo, int $valor): bool
    {
        try {
            $db = Database::getConnection();
            $sql = sprintf('UPDATE productos SET %s = :valor, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL', $campo);
            $stmt = $db->prepare($sql);
            return $stmt->execute([':valor' => $valor, ':id' => $id]);
        } catch (PDOException $e) {
            error_log('Producto::cambiarFlag error: ' . $e->getMessage());
            return false;
        }
    }

    private static function generarSlug(string $nombre): string
    {
        $slug = strtolower(trim($nombre));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }

    private static function generarUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
