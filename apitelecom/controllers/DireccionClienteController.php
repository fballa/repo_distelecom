<?php

require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/DireccionCliente.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class DireccionClienteController
{
    public function index(int $clienteId): void
    {
        $clienteExistente = Cliente::obtenerPorId($clienteId);

        if (!$clienteExistente) {
            Response::error('Cliente no encontrado.', 404);
        }

        $direcciones = DireccionCliente::obtenerPorCliente($clienteId);
        Response::success('Direcciones listadas correctamente.', $direcciones);
    }

    public function show(int $id): void
    {
        $direccion = DireccionCliente::obtenerPorId($id);

        if (!$direccion) {
            Response::error('Dirección no encontrada.', 404);
        }

        Response::success('Dirección obtenida correctamente.', $direccion);
    }

    public function store(int $clienteId): void
    {
        $clienteExistente = Cliente::obtenerPorId($clienteId);

        if (!$clienteExistente) {
            Response::error('Cliente no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $data = $this->sanitizeDireccionData($payload);
        $data['cliente_id'] = $clienteId;

        $errors = $this->validateDireccion($data);
        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        $direccionId = DireccionCliente::crear($data);
        if ($direccionId === null) {
            Response::error('No se pudo crear la dirección.', 500);
        }

        Response::success('Dirección creada correctamente.', ['id' => $direccionId], 201);
    }

    public function update(int $id): void
    {
        $direccionExistente = DireccionCliente::obtenerPorId($id);

        if (!$direccionExistente) {
            Response::error('Dirección no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $data = $this->sanitizeDireccionData($payload, false);
        $errors = $this->validateDireccion($data, false);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (!DireccionCliente::actualizar($id, $data)) {
            Response::error('No se pudo actualizar la dirección.', 500);
        }

        Response::success('Dirección actualizada correctamente.', []);
    }

    public function destroy(int $id): void
    {
        $direccionExistente = DireccionCliente::obtenerPorId($id);

        if (!$direccionExistente) {
            Response::error('Dirección no encontrada.', 404);
        }

        if (!DireccionCliente::eliminar($id)) {
            Response::error('No se pudo eliminar la dirección.', 500);
        }

        Response::success('Dirección eliminada correctamente.', []);
    }

    public function marcarPrincipal(int $id): void
    {
        $direccionExistente = DireccionCliente::obtenerPorId($id);

        if (!$direccionExistente) {
            Response::error('Dirección no encontrada.', 404);
        }

        if (!DireccionCliente::marcarPrincipal($id)) {
            Response::error('No se pudo marcar la dirección como principal.', 500);
        }

        Response::success('Dirección marcada como principal correctamente.', []);
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

    private function sanitizeDireccionData(array $payload, bool $requireFields = true): array
    {
        return [
            'pais' => Validator::sanitizeString($payload['pais'] ?? null),
            'departamento' => Validator::sanitizeString($payload['departamento'] ?? null),
            'ciudad' => Validator::sanitizeString($payload['ciudad'] ?? null),
            'direccion' => Validator::sanitizeString($payload['direccion'] ?? null),
            'referencia' => Validator::sanitizeString($payload['referencia'] ?? null),
            'principal' => isset($payload['principal']) ? (int) $payload['principal'] : 0,
        ];
    }

    private function validateDireccion(array $data, bool $requireFields = true): array
    {
        $rules = [
            'pais' => 'nullable|max:80',
            'departamento' => 'nullable|max:80',
            'ciudad' => 'nullable|max:80',
            'direccion' => $requireFields ? 'required' : 'nullable',
            'referencia' => 'nullable',
            'principal' => 'nullable|boolean',
        ];

        return Validator::validate($data, $rules);
    }
}
