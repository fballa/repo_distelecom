<?php

require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/PedidoDetalle.php';
require_once __DIR__ . '/../models/EstadoPedido.php';
require_once __DIR__ . '/../models/HistorialPedido.php';
require_once __DIR__ . '/../models/Pago.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/DireccionCliente.php';
require_once __DIR__ . '/../models/TbCarritoTemporalModel.php';
require_once __DIR__ . '/../models/WhatsappCarritosTemporalesItemsModel.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class PedidoController
{
    public function index(): void
    {
        $filtros = [
            'cliente_id' => $_GET['cliente_id'] ?? null,
            'estado' => $_GET['estado'] ?? null,
            'fecha_desde' => $_GET['fecha_desde'] ?? null,
            'fecha_hasta' => $_GET['fecha_hasta'] ?? null,
        ];

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 20;

        $result = Pedido::obtenerTodos($filtros, $pagina, $limite);
        Response::success('Pedidos listados correctamente.', $result);
    }

    public function show(int $id): void
    {
        $pedido = Pedido::obtenerPorId($id);

        if (!$pedido) {
            Response::error('Pedido no encontrado.', 404);
        }

        $pedido['detalles'] = PedidoDetalle::obtenerPorPedido($id);
        $pedido['pagos'] = Pago::obtenerPorPedido($id);
        $pedido['historial'] = HistorialPedido::obtenerPorPedido($id);
        $pedido['direcciones'] = !empty($pedido['cliente_id']) ? DireccionCliente::obtenerPorCliente((int) $pedido['cliente_id']) : [];

        Response::success('Pedido obtenido correctamente.', $pedido);
    }

    public function showByNumero(string $numero): void
    {
        if (trim($numero) === '') {
            Response::error('Número de pedido inválido.', 400);
        }

        $pedido = Pedido::obtenerPorNumero($numero);

        if (!$pedido) {
            Response::error('Pedido no encontrado.', 404);
        }

        $pedido['detalles'] = PedidoDetalle::obtenerPorPedido($pedido['id']);
        $pedido['pagos'] = Pago::obtenerPorPedido($pedido['id']);
        $pedido['historial'] = HistorialPedido::obtenerPorPedido($pedido['id']);
        $pedido['direcciones'] = !empty($pedido['cliente_id']) ? DireccionCliente::obtenerPorCliente((int) $pedido['cliente_id']) : [];

        Response::success('Pedido obtenido correctamente.', $pedido);
    }

    public function fromTemporaryCart(?int $carritoId = null): void
    {
        $payload = [];

        if ($carritoId === null) {
            $payload = $this->getJsonInput();
        }

        if ($carritoId !== null) {
            $payload['carrito_id'] = $carritoId;
        }

        $carritoId = isset($payload['carrito_id']) ? (int) $payload['carrito_id'] : 0;
        if ($carritoId <= 0) {
            Response::error('El carrito_id es obligatorio.', 422);
        }

        $carrito = TbCarritoTemporalModel::obtenerPorId($carritoId);
        if (!$carrito) {
            Response::error('Carrito temporal no encontrado', 404);
        }

        $estadoActual = isset($carrito['estado']) ? strtoupper(trim((string) $carrito['estado'])) : '';
        if (in_array($estadoActual, ['PROCESADO', 'FINALIZADO', 'COMPLETADO', 'CONFIRMADO', 'PAGADO', 'CERRADO', 'CANCELADO'], true)) {
            Response::error('El carrito temporal ya fue procesado.', 409);
        }

        $items = WhatsappCarritosTemporalesItemsModel::obtenerPorCarrito($carritoId);
        if (empty($items)) {
            Response::error('El carrito temporal no contiene productos', 422);
        }

        $detalles = [];
        $subtotal = 0.0;

        foreach ($items as $item) {
            $productoId = isset($item['producto_id']) ? (int) $item['producto_id'] : 0;
            $cantidad = isset($item['cantidad']) ? (int) $item['cantidad'] : 0;

            if ($productoId <= 0 || $cantidad <= 0) {
                Response::error('El carrito temporal contiene productos inválidos.', 422);
            }

            $producto = Producto::obtenerPorId($productoId);
            if (!$producto) {
                Response::error('El producto con ID ' . $productoId . ' no existe.', 422);
            }

            $precio = isset($producto['precio']) && is_numeric($producto['precio']) ? (float) $producto['precio'] : null;
            if ($precio === null) {
                Response::error('No se pudo determinar el precio del producto ' . $productoId . '.', 422);
            }

            $subtotalLinea = $cantidad * $precio;
            $detalles[] = [
                'producto_id' => $productoId,
                'cantidad' => $cantidad,
                'precio' => $precio,
                'subtotal' => $subtotalLinea,
            ];
            $subtotal += $subtotalLinea;
        }

        $clienteId = isset($carrito['cliente_id']) ? (int) $carrito['cliente_id'] : 0;
        if ($clienteId <= 0) {
            Response::error('Falta el cliente_id del carrito temporal.', 422);
        }

        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            $estadoPedido = null;
            $estadoStmt = $db->prepare('SELECT id, nombre FROM estado_pedidos WHERE nombre = :nombre LIMIT 1');
            $estadoStmt->execute([':nombre' => 'Pendiente']);
            $estadoPedido = $estadoStmt->fetch();

            if (!$estadoPedido) {
                $estadoStmt = $db->prepare('SELECT id, nombre FROM estado_pedidos ORDER BY id ASC LIMIT 1');
                $estadoStmt->execute();
                $estadoPedido = $estadoStmt->fetch();
            }

            if (!$estadoPedido) {
                $db->rollBack();
                Response::error('No existe un estado de pedido disponible para crear el pedido.', 422);
            }

            $numeroPedido = Pedido::generarNumero($db);
            $pedidoData = [
                'cliente_id' => $clienteId,
                'numero' => $numeroPedido,
                'subtotal' => $subtotal,
                'impuestos' => 0.0,
                'total' => $subtotal,
                'estado_id' => (int) $estadoPedido['id'],
            ];

            $pedidoId = Pedido::crear($pedidoData);
            if ($pedidoId === null) {
                $db->rollBack();
                Response::error('No fue posible crear el pedido', 500);
            }

            if (!PedidoDetalle::guardarDetalles($pedidoId, $detalles)) {
                $db->rollBack();
                Response::error('No fue posible crear el detalle del pedido.', 500);
            }

            $estadoCarrito = 'Procesado';
            if (!TbCarritoTemporalModel::actualizar($carritoId, ['estado' => $estadoCarrito])) {
                $db->rollBack();
                Response::error('No fue posible actualizar el estado del carrito temporal.', 500);
            }

            $db->commit();

            Response::success('Pedido creado correctamente.', ['pedido_id' => $pedidoId], 201);
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('PedidoController::fromTemporaryCart error: ' . $e->getMessage());
            Response::error('No fue posible crear el pedido', 500);
        }
    }

    public function store(): void
    {
        $payload = $this->getJsonInput();
        $data = $this->sanitizePedidoData($payload);

        if (empty($data['numero'])) {
            $data['numero'] = Pedido::generarNumero(Database::getConnection());
        }

        $errors = $this->validatePedido($data);
        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (Pedido::existeNumero($data['numero'])) {
            Response::error('El número de pedido ya existe.', 409);
        }

        $estado = EstadoPedido::obtenerPorId($data['estado_id']);
        if (!$estado) {
            Response::error('El estado de pedido no existe.', 422);
        }

        $pedidoId = Pedido::crear($data);
        if ($pedidoId === null) {
            Response::error('No se pudo crear el pedido.', 500);
        }

        if (!empty($data['detalles']) && !PedidoDetalle::guardarDetalles($pedidoId, $data['detalles'])) {
            Response::error('No se pudo guardar el detalle del pedido.', 500);
        }

        if (!empty($data['pagos'])) {
            foreach ($data['pagos'] as $pago) {
                if (Pago::crear($pedidoId, $pago) === null) {
                    Response::error('No se pudo registrar el pago del pedido.', 500);
                }
            }
        }

        HistorialPedido::registrar($pedidoId, $data['estado_id'], 'Pedido creado.', 'Sistema');

        Response::success('Pedido creado correctamente.', ['id' => $pedidoId, 'numero' => $data['numero']], 201);
    }

    public function update(int $id): void
    {
        $pedidoExistente = Pedido::obtenerPorId($id);

        if (!$pedidoExistente) {
            Response::error('Pedido no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $data = $this->sanitizePedidoData($payload, false);
        $errors = $this->validatePedido($data, false);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (isset($data['estado_id'])) {
            $estado = EstadoPedido::obtenerPorId($data['estado_id']);
            if (!$estado) {
                Response::error('El estado de pedido no existe.', 422);
            }
        }

        if (!Pedido::actualizar($id, $data)) {
            Response::error('No se pudo actualizar el pedido.', 500);
        }

        if (isset($data['detalles']) && !PedidoDetalle::guardarDetalles($id, $data['detalles'])) {
            Response::error('No se pudo actualizar el detalle del pedido.', 500);
        }

        if (isset($data['pagos'])) {
            foreach ($data['pagos'] as $pago) {
                if (Pago::crear($id, $pago) === null) {
                    Response::error('No se pudo registrar el pago del pedido.', 500);
                }
            }
        }

        Response::success('Pedido actualizado correctamente.', []);
    }

    public function destroy(int $id): void
    {
        $pedidoExistente = Pedido::obtenerPorId($id);

        if (!$pedidoExistente) {
            Response::error('Pedido no encontrado.', 404);
        }

        if (!Pedido::eliminar($id)) {
            Response::error('No se pudo eliminar el pedido.', 500);
        }

        Response::success('Pedido eliminado correctamente.', []);
    }

    public function cambiarEstado(int $id): void
    {
        $pedidoExistente = Pedido::obtenerPorId($id);

        if (!$pedidoExistente) {
            Response::error('Pedido no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $estadoId = $payload['estado_id'] ?? null;
        $comentario = Validator::sanitizeString($payload['comentario'] ?? null);

        if ($estadoId === null || !is_numeric($estadoId)) {
            Response::error('El estado_id es obligatorio y debe ser numérico.', 422);
        }

        $estado = EstadoPedido::obtenerPorId((int) $estadoId);
        if (!$estado) {
            Response::error('El estado de pedido no existe.', 422);
        }

        if (!Pedido::cambiarEstado($id, (int) $estadoId, $comentario, 'Sistema')) {
            Response::error('No se pudo cambiar el estado del pedido.', 500);
        }

        Response::success('Estado de pedido actualizado correctamente.', []);
    }

    public function registrarPago(int $id): void
    {
        $pedidoExistente = Pedido::obtenerPorId($id);

        if (!$pedidoExistente) {
            Response::error('Pedido no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $pago = $this->sanitizePagoData($payload);
        $errors = $this->validatePago($pago);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        $pagoId = Pago::crear($id, $pago);
        if ($pagoId === null) {
            Response::error('No se pudo registrar el pago.', 500);
        }

        Response::success('Pago registrado correctamente.', ['id' => $pagoId], 201);
    }

    public function obtenerPorCliente(int $clienteId): void
    {
        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 20;

        $result = Pedido::obtenerPorCliente((int) $clienteId, $pagina, $limite);
        Response::success('Pedidos del cliente listados correctamente.', $result);
    }

    public function historial(int $id): void
    {
        $pedidoExistente = Pedido::obtenerPorId($id);

        if (!$pedidoExistente) {
            Response::error('Pedido no encontrado.', 404);
        }

        $historial = HistorialPedido::obtenerPorPedido($id);
        Response::success('Historial de pedido obtenido correctamente.', $historial);
    }

    public function pagos(int $id): void
    {
        $pedidoExistente = Pedido::obtenerPorId($id);

        if (!$pedidoExistente) {
            Response::error('Pedido no encontrado.', 404);
        }

        $pagos = Pago::obtenerPorPedido($id);
        Response::success('Pagos del pedido obtenidos correctamente.', $pagos);
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

    private function sanitizePedidoData(array $payload, bool $requireFields = true): array
    {
        return [
            'cliente_id' => isset($payload['cliente_id']) ? (int) $payload['cliente_id'] : null,
            'numero' => Validator::sanitizeString($payload['numero'] ?? null),
            'subtotal' => isset($payload['subtotal']) ? (float) $payload['subtotal'] : null,
            'impuestos' => isset($payload['impuestos']) ? (float) $payload['impuestos'] : null,
            'total' => isset($payload['total']) ? (float) $payload['total'] : null,
            'estado_id' => isset($payload['estado_id']) ? (int) $payload['estado_id'] : null,
            'detalles' => isset($payload['detalles']) && is_array($payload['detalles']) ? $this->sanitizeDetalleData($payload['detalles']) : [],
            'pagos' => isset($payload['pagos']) && is_array($payload['pagos']) ? $this->sanitizePagosData($payload['pagos']) : [],
        ];
    }

    private function sanitizeDetalleData(array $detalles): array
    {
        $result = [];

        foreach ($detalles as $item) {
            $result[] = [
                'producto_id' => isset($item['producto_id']) ? (int) $item['producto_id'] : null,
                'cantidad' => isset($item['cantidad']) ? (int) $item['cantidad'] : 0,
                'precio' => isset($item['precio']) ? (float) $item['precio'] : 0,
            ];
        }

        return $result;
    }

    private function sanitizePagosData(array $pagos): array
    {
        $result = [];

        foreach ($pagos as $item) {
            $result[] = [
                'metodo' => Validator::sanitizeString($item['metodo'] ?? null),
                'monto' => isset($item['monto']) ? (float) $item['monto'] : 0,
                'referencia' => Validator::sanitizeString($item['referencia'] ?? null),
                'estado' => Validator::sanitizeString($item['estado'] ?? 'Pendiente'),
                'fecha_pago' => Validator::sanitizeString($item['fecha_pago'] ?? null),
            ];
        }

        return $result;
    }

    private function sanitizePagoData(array $payload): array
    {
        return [
            'metodo' => Validator::sanitizeString($payload['metodo'] ?? null),
            'monto' => isset($payload['monto']) ? (float) $payload['monto'] : null,
            'referencia' => Validator::sanitizeString($payload['referencia'] ?? null),
            'estado' => Validator::sanitizeString($payload['estado'] ?? 'Pendiente'),
            'fecha_pago' => Validator::sanitizeString($payload['fecha_pago'] ?? null),
        ];
    }

    private function validatePedido(array $data, bool $requireFields = true): array
    {
        $rules = [
            'cliente_id' => $requireFields ? 'required|integer' : 'nullable|integer',
            'numero' => $requireFields ? 'required|max:100' : 'nullable|max:100',
            'subtotal' => $requireFields ? 'required|numeric' : 'nullable|numeric',
            'impuestos' => $requireFields ? 'required|numeric' : 'nullable|numeric',
            'total' => $requireFields ? 'required|numeric' : 'nullable|numeric',
            'estado_id' => $requireFields ? 'required|integer' : 'nullable|integer',
            'detalles' => $requireFields ? 'required|array' : 'nullable|array',
            'pagos' => 'nullable|array',
        ];

        $errors = Validator::validate($data, $rules);

        if ($requireFields && empty($data['detalles'])) {
            $errors['detalles'] = 'El pedido debe incluir al menos un producto.';
        }

        if (!empty($data['detalles'])) {
            foreach ($data['detalles'] as $index => $item) {
                if (!isset($item['producto_id']) || !isset($item['cantidad']) || !isset($item['precio'])) {
                    $errors['detalles'] = 'Cada detalle debe incluir producto_id, cantidad y precio.';
                    break;
                }

                if (!is_numeric($item['producto_id']) || (int) $item['producto_id'] <= 0) {
                    $errors['detalles'] = 'producto_id debe ser un entero positivo.';
                    break;
                }

                if (!is_numeric($item['cantidad']) || (int) $item['cantidad'] <= 0) {
                    $errors['detalles'] = 'La cantidad debe ser mayor a 0.';
                    break;
                }

                if (!is_numeric($item['precio']) || (float) $item['precio'] < 0) {
                    $errors['detalles'] = 'El precio no puede ser negativo.';
                    break;
                }

                if (!Producto::obtenerPorId((int) $item['producto_id'])) {
                    $errors['detalles'] = "El producto con ID {$item['producto_id']} no existe.";
                    break;
                }
            }
        }

        return $errors;
    }

    private function validatePago(array $data): array
    {
        $rules = [
            'metodo' => 'required|max:100',
            'monto' => 'required|numeric',
            'referencia' => 'nullable|max:150',
            'estado' => 'nullable|max:50',
            'fecha_pago' => 'nullable|max:50',
        ];

        return Validator::validate($data, $rules);
    }
}
