<?php

require_once __DIR__ . '/../models/Inventario.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/MovimientoInventario.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../config/Database.php';

class InventarioController
{
    public function index(): void
    {
        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 50;

        try {
            $db = Database::getConnection();
            $offset = ($pagina - 1) * $limite;

            $countStmt = $db->prepare('SELECT COUNT(*) FROM inventario i LEFT JOIN productos p ON p.id = i.producto_id WHERE p.deleted_at IS NULL');
            $countStmt->execute();
            $total = (int) $countStmt->fetchColumn();

            $sql = 'SELECT i.id, i.producto_id, p.nombre, p.sku, p.modelo, p.imagen_principal, i.stock_actual, i.stock_minimo, i.ubicacion, i.ultima_actualizacion, p.estado AS estado_producto FROM inventario i LEFT JOIN productos p ON p.id = i.producto_id WHERE p.deleted_at IS NULL ORDER BY i.ultima_actualizacion DESC LIMIT :limite OFFSET :offset';
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $data = $stmt->fetchAll();

            Response::success('Inventario listado correctamente.', ['total' => $total, 'pagina' => $pagina, 'limite' => $limite, 'data' => $data]);
        } catch (PDOException $e) {
            error_log('InventarioController::index error: ' . $e->getMessage());
            Response::error('No se pudo obtener el inventario.', 500);
        }
    }

    public function show(int $id): void
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT i.id, i.producto_id, p.nombre, p.sku, p.modelo, p.imagen_principal, i.stock_actual, i.stock_minimo, i.ubicacion, i.ultima_actualizacion, p.estado AS estado_producto FROM inventario i LEFT JOIN productos p ON p.id = i.producto_id WHERE i.id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            if (!$row) {
                Response::error('Registro de inventario no encontrado.', 404);
            }

            // obtener últimos movimientos
            $movs = MovimientoInventario::obtenerTodos(['producto_id' => $row['producto_id']], 1, 10);
            $row['movimientos'] = $movs['data'] ?? [];

            Response::success('Registro de inventario obtenido correctamente.', $row);
        } catch (PDOException $e) {
            error_log('InventarioController::show error: ' . $e->getMessage());
            Response::error('No se pudo obtener el registro de inventario.', 500);
        }
    }

    public function porProducto(int $productoId): void
    {
        // validar producto
        $producto = Producto::obtenerPorId($productoId);
        if (!$producto) {
            Response::error('Producto no encontrado.', 404);
        }

        $inv = Inventario::obtenerPorProducto($productoId);
        $movimientos = MovimientoInventario::obtenerTodos(['producto_id' => $productoId], 1, 20);

        $result = [
            'producto' => $producto,
            'inventario' => $inv,
            'movimientos' => $movimientos['data'] ?? [],
        ];

        Response::success('Inventario por producto obtenido correctamente.', $result);
    }

    public function buscar(): void
    {
        $texto = Validator::sanitizeString($_GET['texto'] ?? null);
        if (empty($texto)) {
            Response::error('El texto de búsqueda es obligatorio.', 422);
        }

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 50;
        $offset = ($pagina - 1) * $limite;

        try {
            $db = Database::getConnection();
            $like = '%' . $texto . '%';

            $countStmt = $db->prepare('SELECT COUNT(*) FROM productos p LEFT JOIN inventario i ON i.producto_id = p.id WHERE (p.nombre LIKE :t OR p.sku LIKE :t OR p.modelo LIKE :t OR p.codigo_barras LIKE :t) AND p.deleted_at IS NULL');
            $countStmt->execute([':t' => $like]);
            $total = (int) $countStmt->fetchColumn();

            $sql = 'SELECT i.id AS inventario_id, p.id AS producto_id, p.nombre, p.sku, p.modelo, i.stock_actual, i.stock_minimo, i.ubicacion, p.imagen_principal FROM productos p LEFT JOIN inventario i ON i.producto_id = p.id WHERE (p.nombre LIKE :t OR p.sku LIKE :t OR p.modelo LIKE :t OR p.codigo_barras LIKE :t) AND p.deleted_at IS NULL ORDER BY p.created_at DESC LIMIT :limite OFFSET :offset';
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':t', $like, PDO::PARAM_STR);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll();

            Response::success('Búsqueda de inventario realizada correctamente.', ['total' => $total, 'pagina' => $pagina, 'limite' => $limite, 'data' => $data]);
        } catch (PDOException $e) {
            error_log('InventarioController::buscar error: ' . $e->getMessage());
            Response::error('No se pudo realizar la búsqueda.', 500);
        }
    }

    public function stockBajo(): void
    {
        try {
            $db = Database::getConnection();
            $sql = 'SELECT i.id, i.producto_id, p.nombre, p.sku, i.stock_actual, i.stock_minimo, i.ubicacion FROM inventario i LEFT JOIN productos p ON p.id = i.producto_id WHERE i.stock_actual <= i.stock_minimo AND p.deleted_at IS NULL ORDER BY i.stock_actual ASC';
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll();
            Response::success('Productos con stock bajo obtenidos correctamente.', $data);
        } catch (PDOException $e) {
            error_log('InventarioController::stockBajo error: ' . $e->getMessage());
            Response::error('No se pudo obtener productos con stock bajo.', 500);
        }
    }

    public function agotados(): void
    {
        try {
            $db = Database::getConnection();
            $sql = 'SELECT i.id, i.producto_id, p.nombre, p.sku, i.stock_actual, i.ubicacion FROM inventario i LEFT JOIN productos p ON p.id = i.producto_id WHERE i.stock_actual = 0 AND p.deleted_at IS NULL ORDER BY p.nombre ASC';
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll();
            Response::success('Productos agotados obtenidos correctamente.', $data);
        } catch (PDOException $e) {
            error_log('InventarioController::agotados error: ' . $e->getMessage());
            Response::error('No se pudo obtener productos agotados.', 500);
        }
    }

    public function resumen(): void
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(DISTINCT i.producto_id) AS productos_con_inventario, SUM(CASE WHEN i.stock_actual <= i.stock_minimo THEN 1 ELSE 0 END) AS productos_stock_bajo, SUM(CASE WHEN i.stock_actual = 0 THEN 1 ELSE 0 END) AS productos_agotados, COALESCE(SUM(i.stock_actual),0) AS unidades_totales FROM inventario i LEFT JOIN productos p ON p.id = i.producto_id WHERE p.deleted_at IS NULL');
            $stmt->execute();
            $row = $stmt->fetch();
            Response::success('Resumen de inventario obtenido correctamente.', $row ?: []);
        } catch (PDOException $e) {
            error_log('InventarioController::resumen error: ' . $e->getMessage());
            Response::error('No se pudo obtener el resumen de inventario.', 500);
        }
    }

    public function entrada(): void
    {
        $payload = $this->getJsonInput();
        $data = [
            'producto_id' => isset($payload['producto_id']) ? (int) $payload['producto_id'] : null,
            'cantidad' => isset($payload['cantidad']) ? (int) $payload['cantidad'] : null,
            'motivo' => Validator::sanitizeString($payload['motivo'] ?? null),
            'usuario' => Validator::sanitizeString($payload['usuario'] ?? null),
        ];

        $errors = Validator::validate($data, [
            'producto_id' => 'required|integer',
            'cantidad' => 'required|integer',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if ($data['cantidad'] <= 0) {
            Response::error('La cantidad debe ser mayor que 0.', 422);
        }

        // verificar producto
        $producto = Producto::obtenerPorId($data['producto_id']);
        if (!$producto) {
            Response::error('Producto no encontrado.', 404);
        }

        if (isset($producto['estado']) && $producto['estado'] === 'Inactivo') {
            Response::error('El producto está inactivo y no puede recibir movimientos de inventario.', 409);
        }

        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            // obtener inventario existente
            $stmt = $db->prepare('SELECT stock_actual FROM inventario WHERE producto_id = :producto_id FOR UPDATE');
            $stmt->execute([':producto_id' => $data['producto_id']]);
            $row = $stmt->fetch();

            $stockAnterior = $row ? (int) $row['stock_actual'] : 0;
            $nuevoStock = $stockAnterior + $data['cantidad'];

            // actualizar inventario (insert o update)
            if ($row) {
                $u = $db->prepare('UPDATE inventario SET stock_actual = :stock_actual, ultima_actualizacion = NOW() WHERE producto_id = :producto_id');
                $ok = $u->execute([':stock_actual' => $nuevoStock, ':producto_id' => $data['producto_id']]);
            } else {
                $i = $db->prepare('INSERT INTO inventario (producto_id, stock_actual, stock_minimo, ubicacion, ultima_actualizacion) VALUES (:producto_id, :stock_actual, 0, NULL, NOW())');
                $ok = $i->execute([':producto_id' => $data['producto_id'], ':stock_actual' => $nuevoStock]);
            }

            if (!$ok) {
                $db->rollBack();
                Response::error('No fue posible actualizar el inventario.', 500);
            }

            // sincronizar productos.stock
            $pupd = $db->prepare('UPDATE productos SET stock = :stock WHERE id = :id');
            if (!$pupd->execute([':stock' => $nuevoStock, ':id' => $data['producto_id']])) {
                $db->rollBack();
                Response::error('No fue posible sincronizar el stock en productos.', 500);
            }

            // registrar movimiento
            $mov = MovimientoInventario::crear(['producto_id' => $data['producto_id'], 'tipo' => 'Entrada', 'cantidad' => $data['cantidad'], 'motivo' => $data['motivo'], 'usuario' => $data['usuario']]);
            if ($mov === null) {
                $db->rollBack();
                Response::error('No fue posible registrar el movimiento de inventario.', 500);
            }

            $db->commit();
            Response::success('Entrada registrada correctamente.', ['producto_id' => $data['producto_id'], 'nuevo_stock' => $nuevoStock], 201);
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('InventarioController::entrada error: ' . $e->getMessage());
            Response::error('No fue posible registrar la entrada de inventario.', 500);
        }
    }

    public function salida(): void
    {
        $payload = $this->getJsonInput();
        $data = [
            'producto_id' => isset($payload['producto_id']) ? (int) $payload['producto_id'] : null,
            'cantidad' => isset($payload['cantidad']) ? (int) $payload['cantidad'] : null,
            'motivo' => Validator::sanitizeString($payload['motivo'] ?? null),
            'usuario' => Validator::sanitizeString($payload['usuario'] ?? null),
        ];

        $errors = Validator::validate($data, [
            'producto_id' => 'required|integer',
            'cantidad' => 'required|integer',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if ($data['cantidad'] <= 0) {
            Response::error('La cantidad debe ser mayor que 0.', 422);
        }

        $producto = Producto::obtenerPorId($data['producto_id']);
        if (!$producto) {
            Response::error('Producto no encontrado.', 404);
        }

        if (isset($producto['estado']) && $producto['estado'] === 'Inactivo') {
            Response::error('El producto está inactivo y no puede recibir movimientos de inventario.', 409);
        }

        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            // lock inventario row
            $stmt = $db->prepare('SELECT stock_actual FROM inventario WHERE producto_id = :producto_id FOR UPDATE');
            $stmt->execute([':producto_id' => $data['producto_id']]);
            $row = $stmt->fetch();
            $stockAnterior = $row ? (int) $row['stock_actual'] : 0;

            if ($stockAnterior < $data['cantidad']) {
                $db->rollBack();
                Response::error('Stock insuficiente para realizar la salida.', 422);
            }

            $nuevoStock = $stockAnterior - $data['cantidad'];

            // actualizar inventario
            $u = $db->prepare('UPDATE inventario SET stock_actual = :stock_actual, ultima_actualizacion = NOW() WHERE producto_id = :producto_id');
            if (!$u->execute([':stock_actual' => $nuevoStock, ':producto_id' => $data['producto_id']])) {
                $db->rollBack();
                Response::error('No fue posible actualizar el inventario.', 500);
            }

            // sincronizar productos.stock
            $pupd = $db->prepare('UPDATE productos SET stock = :stock WHERE id = :id');
            if (!$pupd->execute([':stock' => $nuevoStock, ':id' => $data['producto_id']])) {
                $db->rollBack();
                Response::error('No fue posible sincronizar el stock en productos.', 500);
            }

            // registrar movimiento
            $mov = MovimientoInventario::crear(['producto_id' => $data['producto_id'], 'tipo' => 'Salida', 'cantidad' => $data['cantidad'], 'motivo' => $data['motivo'], 'usuario' => $data['usuario']]);
            if ($mov === null) {
                $db->rollBack();
                Response::error('No fue posible registrar el movimiento de inventario.', 500);
            }

            $db->commit();
            Response::success('Salida registrada correctamente.', ['producto_id' => $data['producto_id'], 'nuevo_stock' => $nuevoStock], 201);
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('InventarioController::salida error: ' . $e->getMessage());
            Response::error('No fue posible registrar la salida de inventario.', 500);
        }
    }

    public function ajuste(): void
    {
        $payload = $this->getJsonInput();
        $data = [
            'producto_id' => isset($payload['producto_id']) ? (int) $payload['producto_id'] : null,
            'cantidad' => isset($payload['cantidad']) ? (int) $payload['cantidad'] : null,
            'motivo' => Validator::sanitizeString($payload['motivo'] ?? null),
            'usuario' => Validator::sanitizeString($payload['usuario'] ?? null),
        ];

        $errors = Validator::validate($data, [
            'producto_id' => 'required|integer',
            'cantidad' => 'required|integer',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if ($data['cantidad'] < 0) {
            Response::error('La cantidad debe ser mayor o igual a 0.', 422);
        }

        $producto = Producto::obtenerPorId($data['producto_id']);
        if (!$producto) {
            Response::error('Producto no encontrado.', 404);
        }

        if (isset($producto['estado']) && $producto['estado'] === 'Inactivo') {
            Response::error('El producto está inactivo y no puede recibir movimientos de inventario.', 409);
        }

        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            // lock inventario row
            $stmt = $db->prepare('SELECT stock_actual FROM inventario WHERE producto_id = :producto_id FOR UPDATE');
            $stmt->execute([':producto_id' => $data['producto_id']]);
            $row = $stmt->fetch();
            $stockAnterior = $row ? (int) $row['stock_actual'] : 0;

            $nuevoStock = $data['cantidad']; // cantidad representa nuevo stock en ajuste
            $diferencia = $nuevoStock - $stockAnterior;

            // actualizar inventario (insert or update)
            if ($row) {
                $u = $db->prepare('UPDATE inventario SET stock_actual = :stock_actual, ultima_actualizacion = NOW() WHERE producto_id = :producto_id');
                $ok = $u->execute([':stock_actual' => $nuevoStock, ':producto_id' => $data['producto_id']]);
            } else {
                $i = $db->prepare('INSERT INTO inventario (producto_id, stock_actual, stock_minimo, ubicacion, ultima_actualizacion) VALUES (:producto_id, :stock_actual, 0, NULL, NOW())');
                $ok = $i->execute([':producto_id' => $data['producto_id'], ':stock_actual' => $nuevoStock]);
            }

            if (!$ok) {
                $db->rollBack();
                Response::error('No fue posible actualizar el inventario.', 500);
            }

            // sincronizar productos.stock
            $pupd = $db->prepare('UPDATE productos SET stock = :stock WHERE id = :id');
            if (!$pupd->execute([':stock' => $nuevoStock, ':id' => $data['producto_id']])) {
                $db->rollBack();
                Response::error('No fue posible sincronizar el stock en productos.', 500);
            }

            // registrar movimiento: cantidad positiva (mostrar diferencia absoluta)
            $cantidadMovimiento = abs($diferencia);
            $tipoMov = 'Ajuste';

            if ($cantidadMovimiento > 0) {
                $mov = MovimientoInventario::crear(['producto_id' => $data['producto_id'], 'tipo' => $tipoMov, 'cantidad' => $cantidadMovimiento, 'motivo' => $data['motivo'], 'usuario' => $data['usuario']]);
                if ($mov === null) {
                    $db->rollBack();
                    Response::error('No fue posible registrar el movimiento de ajuste.', 500);
                }
            }

            $db->commit();
            Response::success('Ajuste realizado correctamente.', ['producto_id' => $data['producto_id'], 'nuevo_stock' => $nuevoStock], 201);
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('InventarioController::ajuste error: ' . $e->getMessage());
            Response::error('No fue posible realizar el ajuste de inventario.', 500);
        }
    }

    private function getJsonInput(): array
    {
        $body = file_get_contents('php://input');
        $input = json_decode($body, true);
        if (!is_array($input)) {
            Response::error('Entrada JSON inválida.', 400);
        }
        return $input;
    }
}
