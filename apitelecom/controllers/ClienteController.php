<?php

require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class ClienteController
{
    public function index(): void
    {
        $filtros = [
            'nombre' => $_GET['nombre'] ?? null,
            'correo' => $_GET['correo'] ?? null,
            'estado' => $_GET['estado'] ?? null,
        ];

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 20;

        $result = Cliente::obtenerTodos($filtros, $pagina, $limite);
        Response::success('Clientes listados correctamente.', $result);
    }

    public function show(int $id): void
    {
        $cliente = Cliente::obtenerPorId($id);

        if (!$cliente) {
            Response::error('Cliente no encontrado.', 404);
        }

        Response::success('Cliente obtenido correctamente.', $cliente);
    }

    public function obtenerPorTelefono(): void
    {
        $telefono = $_GET['telefono'] ?? null;
        if ($telefono === null || trim((string) $telefono) === '') {
            Response::error('El parámetro telefono es obligatorio.', 422);
        }

        $telefono = trim((string) $telefono);
        $telefono = preg_replace('/\s+/', '', $telefono);
        $telefono = preg_replace('/[^0-9+]/', '', $telefono);

        if ($telefono === '' || mb_strlen($telefono) < 6 || mb_strlen($telefono) > 20) {
            Response::error('El parámetro telefono tiene un formato inválido.', 422);
        }

        if (substr_count($telefono, '+') > 1 || preg_match('/\+.*\+/', $telefono)) {
            Response::error('El parámetro telefono tiene un formato inválido.', 422);
        }

        if (!preg_match('/^\+?[0-9]+$/', $telefono)) {
            Response::error('El parámetro telefono tiene un formato inválido.', 422);
        }

        $cliente = Cliente::obtenerPorTelefono($telefono);
        if (!$cliente) {
            Response::error('No se encontró un cliente con ese número de teléfono.', 404);
        }

        Response::success('Cliente encontrado correctamente.', $cliente);
    }

    public function store(): void
    {
        $payload = $this->getJsonInput();
        $data = $this->sanitizeClienteData($payload);
        $errors = $this->validateCliente($data);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (Cliente::existeCorreo($data['correo'])) {
            Response::error('El correo ya existe.', 409);
        }

        $clienteId = Cliente::crear($data);

        if ($clienteId === null) {
            Response::error('No se pudo crear el cliente.', 500);
        }

        Response::success('Cliente creado correctamente.', ['id' => $clienteId], 201);
    }

    public function update(int $id): void
    {
        $clienteExistente = Cliente::obtenerPorId($id);

        if (!$clienteExistente) {
            Response::error('Cliente no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $data = $this->sanitizeClienteData($payload, false);
        $errors = $this->validateCliente($data, false);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (isset($data['correo']) && Cliente::existeCorreo($data['correo'], $id)) {
            Response::error('El correo ya existe.', 409);
        }

        if (!Cliente::actualizar($id, $data)) {
            Response::error('No se pudo actualizar el cliente.', 500);
        }

        Response::success('Cliente actualizado correctamente.', []);
    }

    public function destroy(int $id): void
    {
        $clienteExistente = Cliente::obtenerPorId($id);

        if (!$clienteExistente) {
            Response::error('Cliente no encontrado.', 404);
        }

        if (!Cliente::eliminar($id)) {
            Response::error('No se pudo eliminar el cliente.', 500);
        }

        Response::success('Cliente eliminado correctamente.', []);
    }

    public function cambiarEstado(int $id): void
    {
        $clienteExistente = Cliente::obtenerPorId($id);

        if (!$clienteExistente) {
            Response::error('Cliente no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $estado = Validator::sanitizeString($payload['estado'] ?? null);

        $errors = Validator::validate(['estado' => $estado], ['estado' => 'required|in:Activo,Inactivo']);
        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (!Cliente::cambiarEstado($id, $estado)) {
            Response::error('No se pudo cambiar el estado del cliente.', 500);
        }

        Response::success('Estado del cliente actualizado correctamente.', []);
    }

    public function login(): void
    {
        $payload = $this->getJsonInput();
        $data = [
            'correo' => Validator::sanitizeString($payload['correo'] ?? null),
            'password' => Validator::sanitizeString($payload['password'] ?? null),
        ];

        $errors = Validator::validate($data, [
            'correo' => 'required|email|max:150',
            'password' => 'required|min:6',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        $cliente = Cliente::autenticar($data['correo'], $data['password']);

        if (!$cliente) {
            Response::error('Correo o contraseña incorrectos.', 401);
        }

        unset($cliente['password']);
        Response::success('Inicio de sesión exitoso.', ['cliente' => $cliente]);
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

    private function sanitizeClienteData(array $payload, bool $requireFields = true): array
    {
        return [
            'nombre' => Validator::sanitizeString($payload['nombre'] ?? null),
            'apellido' => Validator::sanitizeString($payload['apellido'] ?? null),
            'empresa' => Validator::sanitizeString($payload['empresa'] ?? null),
            'correo' => Validator::sanitizeString($payload['correo'] ?? null),
            'telefono' => Validator::sanitizeString($payload['telefono'] ?? null),
            'documento' => Validator::sanitizeString($payload['documento'] ?? null),
            'password' => isset($payload['password']) ? Validator::sanitizeString($payload['password']) : null,
            'estado' => Validator::sanitizeString($payload['estado'] ?? null),
        ];
    }

    private function validateCliente(array $data, bool $requireFields = true): array
    {
        $rules = [
            'nombre' => $requireFields ? 'required|max:100' : 'nullable|max:100',
            'apellido' => 'nullable|max:100',
            'empresa' => 'nullable|max:150',
            'correo' => $requireFields ? 'required|email|max:150' : 'nullable|email|max:150',
            'telefono' => 'nullable|max:30',
            'documento' => 'nullable|max:50',
            'password' => $requireFields ? 'nullable|min:6' : 'nullable|min:6',
            'estado' => 'nullable|in:Activo,Inactivo',
        ];

        return Validator::validate($data, $rules);
    }
}
