<?php

require_once __DIR__ . '/../models/Rol.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class RolController
{
    public function index(): void
    {
        $filtros = [
            'nombre' => $_GET['nombre'] ?? null,
            'descripcion' => $_GET['descripcion'] ?? null,
        ];

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 20;

        $result = Rol::obtenerTodos($filtros, $pagina, $limite);
        Response::success('Roles listados correctamente.', $result);
    }

    public function show(int $id): void
    {
        $rol = Rol::obtenerPorId($id);

        if (!$rol) {
            Response::error('Rol no encontrado.', 404);
        }

        Response::success('Rol obtenido correctamente.', $rol);
    }

    public function store(): void
    {
        $payload = $this->getJsonInput();
        $data = [
            'nombre' => Validator::sanitizeString($payload['nombre'] ?? null),
            'descripcion' => Validator::sanitizeString($payload['descripcion'] ?? null),
        ];

        $errors = Validator::validate($data, [
            'nombre' => 'required|max:50',
            'descripcion' => 'nullable|max:255',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (Rol::existeNombre($data['nombre'])) {
            Response::error('Ya existe un rol con ese nombre.', 409);
        }

        $rolId = Rol::crear($data);
        if ($rolId === null) {
            Response::error('No se pudo crear el rol.', 500);
        }

        Response::success('Rol creado correctamente.', ['id' => $rolId], 201);
    }

    public function update(int $id): void
    {
        $rolExistente = Rol::obtenerPorId($id);

        if (!$rolExistente) {
            Response::error('Rol no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $data = [
            'nombre' => Validator::sanitizeString($payload['nombre'] ?? null),
            'descripcion' => Validator::sanitizeString($payload['descripcion'] ?? null),
        ];

        $errors = Validator::validate($data, [
            'nombre' => 'nullable|max:50',
            'descripcion' => 'nullable|max:255',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (!empty($data['nombre']) && Rol::existeNombre($data['nombre'], $id)) {
            Response::error('Ya existe un rol con ese nombre.', 409);
        }

        $data['nombre'] = $data['nombre'] ?? $rolExistente['nombre'];
        $data['descripcion'] = array_key_exists('descripcion', $data) ? $data['descripcion'] : $rolExistente['descripcion'];

        if (!Rol::actualizar($id, $data)) {
            Response::error('No se pudo actualizar el rol.', 500);
        }

        Response::success('Rol actualizado correctamente.', []);
    }

    public function destroy(int $id): void
    {
        $rolExistente = Rol::obtenerPorId($id);

        if (!$rolExistente) {
            Response::error('Rol no encontrado.', 404);
        }

        if (Rol::estaEnUso($id)) {
            Response::error('No se puede eliminar el rol porque está asignado a uno o más usuarios.', 409);
        }

        if (!Rol::eliminar($id)) {
            Response::error('No se pudo eliminar el rol.', 500);
        }

        Response::success('Rol eliminado correctamente.', []);
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
