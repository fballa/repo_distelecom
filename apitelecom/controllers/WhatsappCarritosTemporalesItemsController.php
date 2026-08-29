<?php

require_once __DIR__ . '/../models/WhatsappCarritosTemporalesItemsModel.php';
require_once __DIR__ . '/../models/TbCarritoTemporalModel.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class WhatsappCarritosTemporalesItemsController
{
    public function index(): void
    {
        $data = WhatsappCarritosTemporalesItemsModel::obtenerTodos();
        Response::success('Items de carritos obtenidos correctamente.', $data);
    }

    public function show(int $id): void
    {
        $item = WhatsappCarritosTemporalesItemsModel::obtenerPorId($id);
        if (!$item) {
            Response::error('Item no encontrado.', 404);
        }
        Response::success('Item obtenido correctamente.', $item);
    }

    public function store(): void
    {
        $payload = $this->getJsonInput();
        $data = [
            'carrito_id' => isset($payload['carrito_id']) ? (int) $payload['carrito_id'] : null,
            'producto_id' => isset($payload['producto_id']) ? (int) $payload['producto_id'] : null,
            'cantidad' => isset($payload['cantidad']) ? (int) $payload['cantidad'] : null,
            'precio' => isset($payload['precio']) && $payload['precio'] !== '' ? (float) $payload['precio'] : null,
        ];

        if ($data['precio'] === null && isset($payload['producto_id'])) {
            $producto = Producto::obtenerPorId((int) $payload['producto_id']);
            if ($producto && isset($producto['precio']) && is_numeric($producto['precio'])) {
                $data['precio'] = (float) $producto['precio'];
            }
        }

        $errors = Validator::validate($data, [
            'carrito_id' => 'required|integer',
            'producto_id' => 'required|integer',
            'cantidad' => 'required|integer',
            'precio' => 'required|numeric',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if ($data['cantidad'] <= 0) {
            Response::error('La cantidad debe ser un entero positivo.', 422);
        }

        if ($data['precio'] < 0) {
            Response::error('El precio no puede ser negativo.', 422);
        }

        // verificar existencia del carrito
        $carrito = TbCarritoTemporalModel::obtenerPorId($data['carrito_id']);
        if (!$carrito) {
            Response::error('Carrito temporal no encontrado.', 404);
        }

        $id = WhatsappCarritosTemporalesItemsModel::crear($data);
        if ($id === null) {
            Response::error('No se pudo agregar el item al carrito.', 500);
        }

        Response::success('Item agregado correctamente.', ['id' => $id], 201);
    }

    public function update(int $id): void
    {
        $item = WhatsappCarritosTemporalesItemsModel::obtenerPorId($id);
        if (!$item) {
            Response::error('Item no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $data = [];
        if (array_key_exists('carrito_id', $payload)) {
            $data['carrito_id'] = (int) $payload['carrito_id'];
        }
        if (array_key_exists('producto_id', $payload)) {
            $data['producto_id'] = (int) $payload['producto_id'];
        }
        if (array_key_exists('cantidad', $payload)) {
            $data['cantidad'] = (int) $payload['cantidad'];
        }
        if (array_key_exists('precio', $payload)) {
            $data['precio'] = isset($payload['precio']) && $payload['precio'] !== '' ? (float) $payload['precio'] : null;
        }

        $errors = Validator::validate($data, [
            'carrito_id' => 'nullable|integer',
            'producto_id' => 'nullable|integer',
            'cantidad' => 'nullable|integer',
            'precio' => 'nullable|numeric',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (isset($data['cantidad']) && $data['cantidad'] <= 0) {
            Response::error('La cantidad debe ser un entero positivo.', 422);
        }

        if (isset($data['precio']) && $data['precio'] !== null && $data['precio'] < 0) {
            Response::error('El precio no puede ser negativo.', 422);
        }

        if (!WhatsappCarritosTemporalesItemsModel::actualizar($id, $data)) {
            Response::error('No se pudo actualizar el item.', 500);
        }

        Response::success('Item actualizado correctamente.', []);
    }

    public function destroy(int $id): void
    {
        $item = WhatsappCarritosTemporalesItemsModel::obtenerPorId($id);
        if (!$item) {
            Response::error('Item no encontrado.', 404);
        }

        if (!WhatsappCarritosTemporalesItemsModel::eliminar($id)) {
            Response::error('No se pudo eliminar el item.', 500);
        }

        Response::success('Item eliminado correctamente.', []);
    }

    public function porCarrito(int $carrito_id): void
    {
        $items = WhatsappCarritosTemporalesItemsModel::obtenerPorCarrito($carrito_id);
        Response::success('Items del carrito obtenidos correctamente.', $items);
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
