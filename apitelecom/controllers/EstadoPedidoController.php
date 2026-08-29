<?php

require_once __DIR__ . '/../models/EstadoPedido.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class EstadoPedidoController
{
    public function index(): void
    {
        $filtros = [
            'estado' => $_GET['estado'] ?? null,
            'buscar' => $_GET['buscar'] ?? null,
        ];

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 20;

        $result = EstadoPedido::obtenerTodos($filtros, $pagina, $limite);
        Response::success('Estados de pedidos listados correctamente.', $result);
    }

    public function show(int $id): void
    {
        $estado = EstadoPedido::obtenerPorId($id);

        if (!$estado) {
            Response::error('Estado de pedido no encontrado.', 404);
        }

        Response::success('Estado de pedido obtenido correctamente.', $estado);
    }

    public function store(): void
    {
        $payload = $this->getJsonInput();
        $data = $this->sanitizeEstadoPedidoData($payload);
        $errors = $this->validateEstadoPedido($data);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (EstadoPedido::existeNombre($data['nombre'])) {
            Response::error('El nombre del estado ya existe.', 409);
        }

        $estadoId = EstadoPedido::crear($data);

        if ($estadoId === null) {
            Response::error('No se pudo crear el estado de pedido.', 500);
        }

        Response::success('Estado de pedido creado correctamente.', ['id' => $estadoId], 201);
    }

    public function update(int $id): void
    {
        $estadoExistente = EstadoPedido::obtenerPorId($id);

        if (!$estadoExistente) {
            Response::error('Estado de pedido no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $data = $this->sanitizeEstadoPedidoData($payload);
        $errors = $this->validateEstadoPedido($data, $id);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (EstadoPedido::existeNombre($data['nombre'], $id)) {
            Response::error('El nombre del estado ya existe.', 409);
        }

        $updated = EstadoPedido::actualizar($id, $data);

        if (!$updated) {
            Response::error('No se pudo actualizar el estado de pedido.', 500);
        }

        Response::success('Estado de pedido actualizado correctamente.', []);
    }

    public function cambiarEstado(int $id): void
    {
        $estadoExistente = EstadoPedido::obtenerPorId($id);

        if (!$estadoExistente) {
            Response::error('Estado de pedido no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $estado = $payload['estado'] ?? null;

        if (!in_array($estado, ['Activo', 'Inactivo'], true)) {
            Response::error('El estado debe ser Activo o Inactivo.', 422);
        }

        $updated = EstadoPedido::cambiarEstado($id, $estado);

        if (!$updated) {
            Response::error('No se pudo cambiar el estado del estado de pedido.', 500);
        }

        Response::success('Estado de pedido actualizado correctamente.', []);
    }

    public function destroy(int $id): void
    {
        $estadoExistente = EstadoPedido::obtenerPorId($id);

        if (!$estadoExistente) {
            Response::error('Estado de pedido no encontrado.', 404);
        }

        $deleted = EstadoPedido::eliminar($id);

        if (!$deleted) {
            Response::error('No se pudo eliminar el estado de pedido.', 500);
        }

        Response::success('Estado de pedido eliminado correctamente.', []);
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

    private function sanitizeEstadoPedidoData(array $payload): array
    {
        return [
            'nombre' => Validator::sanitizeString($payload['nombre'] ?? null),
            'descripcion' => Validator::sanitizeString($payload['descripcion'] ?? null),
            'orden' => isset($payload['orden']) ? (int) $payload['orden'] : 0,
            'estado' => Validator::sanitizeString($payload['estado'] ?? 'Activo'),
        ];
    }

    private function validateEstadoPedido(array $data, ?int $id = null): array
    {
        $rules = [
            'nombre' => 'required|max:120',
            'descripcion' => 'nullable',
            'orden' => 'nullable|integer',
            'estado' => 'nullable|in:Activo,Inactivo',
        ];

        $errors = Validator::validate($data, $rules);

        if (isset($data['orden']) && $data['orden'] < 0) {
            $errors['orden'] = 'El orden debe ser mayor o igual a 0.';
        }

        return $errors;
    }
}
