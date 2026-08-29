<?php

require_once __DIR__ . '/../models/TbCarritoTemporalModel.php';
require_once __DIR__ . '/../controllers/PedidoController.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class TbCarritoTemporalController
{
    public function index(): void
    {
        $filtros = [
            'cliente_id' => $_GET['cliente_id'] ?? null,
            'phone' => $_GET['phone'] ?? $_GET['telefono'] ?? null,
            'estado' => $_GET['estado'] ?? null,
        ];

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 25;

        $result = TbCarritoTemporalModel::obtenerTodos($filtros, $pagina, $limite);
        Response::success('Carritos temporales obtenidos correctamente.', $result);
    }

    public function show(int $id): void
    {
        $carrito = TbCarritoTemporalModel::obtenerPorId($id);
        if (!$carrito) {
            Response::error('Carrito temporal no encontrado.', 404);
        }
        Response::success('Carrito temporal obtenido correctamente.', $carrito);
    }

    public function store(): void
    {
        $payload = $this->getJsonInput();

        $data = [
            'phone' => Validator::sanitizeString($payload['phone'] ?? null),
            'cliente_id' => isset($payload['cliente_id']) ? (int) $payload['cliente_id'] : null,
            'estado' => Validator::sanitizeString($payload['estado'] ?? null),
            'carrito_json' => $payload['carrito_json'] ?? null,
        ];

        $errors = Validator::validate($data, [
            'phone' => 'required|max:30',
            'cliente_id' => 'required|integer',
            'estado' => 'required|max:20',
            'carrito_json' => 'required',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        // validar JSON
        json_decode($data['carrito_json']);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('carrito_json debe ser JSON válido.', 422);
        }

        $id = TbCarritoTemporalModel::crear($data);
        if ($id === null) {
            Response::error('No se pudo crear el carrito temporal.', 500);
        }

        Response::success('Carrito temporal creado correctamente.', ['id' => $id], 201);
    }

    public function update(int $id): void
    {
        $carrito = TbCarritoTemporalModel::obtenerPorId($id);
        if (!$carrito) {
            Response::error('Carrito temporal no encontrado.', 404);
        }

        $payload = $this->getJsonInput();

        $data = [];
        if (array_key_exists('phone', $payload)) {
            $data['phone'] = Validator::sanitizeString($payload['phone']);
        }
        if (array_key_exists('cliente_id', $payload)) {
            $data['cliente_id'] = (int) $payload['cliente_id'];
        }
        if (array_key_exists('estado', $payload)) {
            $data['estado'] = Validator::sanitizeString($payload['estado']);
        }
        if (array_key_exists('carrito_json', $payload)) {
            $data['carrito_json'] = $payload['carrito_json'];
        }

        if (isset($data['carrito_json'])) {
            json_decode($data['carrito_json']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Response::error('carrito_json debe ser JSON válido.', 422);
            }
        }

        $errors = Validator::validate($data, [
            'phone' => 'nullable|max:30',
            'cliente_id' => 'nullable|integer',
            'estado' => 'nullable|max:20',
            'carrito_json' => 'nullable',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (!TbCarritoTemporalModel::actualizar($id, $data)) {
            Response::error('No se pudo actualizar el carrito temporal.', 500);
        }

        Response::success('Carrito temporal actualizado correctamente.', []);
    }

    public function destroy(int $id): void
    {
        $carrito = TbCarritoTemporalModel::obtenerPorId($id);
        if (!$carrito) {
            Response::error('Carrito temporal no encontrado.', 404);
        }

        if (!TbCarritoTemporalModel::eliminar($id)) {
            Response::error('No se pudo eliminar el carrito temporal.', 500);
        }

        Response::success('Carrito temporal eliminado correctamente.', []);
    }

    public function byPhone(string $phone): void
    {
        $phone = Validator::sanitizeString($phone);
        if (empty($phone)) {
            Response::error('Phone inválido.', 400);
        }

        $carrito = TbCarritoTemporalModel::buscarPorTelefono($phone);
        if (!$carrito) {
            Response::error('Carrito temporal no encontrado para ese teléfono.', 404);
        }

        Response::success('Carrito temporal obtenido correctamente.', $carrito);
    }

    public function full(int $id): void
    {
        $full = TbCarritoTemporalModel::obtenerFull($id);
        if (!$full) {
            Response::error('Carrito temporal no encontrado.', 404);
        }
        Response::success('Carrito temporal obtenido correctamente.', $full);
    }

    public function cambiarEstado(int $id): void
    {
        $carrito = TbCarritoTemporalModel::obtenerPorId($id);
        if (!$carrito) {
            Response::error('Carrito temporal no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $estado = Validator::sanitizeString($payload['estado'] ?? null);

        $errors = Validator::validate(['estado' => $estado], ['estado' => 'required|max:20']);
        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (!TbCarritoTemporalModel::actualizar($id, ['estado' => $estado])) {
            Response::error('No se pudo cambiar el estado del carrito temporal.', 500);
        }

        Response::success('Estado del carrito temporal actualizado correctamente.', []);
    }

    public function confirmarPedido(int $id): void
    {
        $pedidoController = new PedidoController();
        $pedidoController->fromTemporaryCart($id);
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
