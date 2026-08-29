<?php

require_once __DIR__ . '/../models/Marca.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class MarcaController
{
    public function index(): void
    {
        $filtros = [
            'nombre' => $_GET['nombre'] ?? null,
            'estado' => $_GET['estado'] ?? null,
        ];

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 20;

        $result = Marca::obtenerTodos($filtros, $pagina, $limite);
        Response::success('Marcas listadas correctamente.', $result);
    }

    public function show(int $id): void
    {
        $marca = Marca::obtenerPorId($id);

        if (!$marca) {
            Response::error('Marca no encontrada.', 404);
        }

        Response::success('Marca obtenida correctamente.', $marca);
    }

    public function store(): void
    {
        $payload = $this->getJsonInput();
        $data = $this->sanitizeMarcaData($payload);
        $errors = $this->validateMarca($data);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (Marca::existeNombre($data['nombre'])) {
            Response::error('Ya existe una marca con ese nombre.', 409);
        }

        $marcaId = Marca::crear($data);

        if ($marcaId === null) {
            Response::error('No se pudo crear la marca.', 500);
        }

        Response::success('Marca creada correctamente.', ['id' => $marcaId], 201);
    }

    public function update(int $id): void
    {
        $marcaExistente = Marca::obtenerPorId($id);

        if (!$marcaExistente) {
            Response::error('Marca no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $data = $this->sanitizeMarcaData($payload, false);
        $errors = $this->validateMarca($data, false);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (!empty($data['nombre']) && Marca::existeNombre($data['nombre'], $id)) {
            Response::error('Ya existe una marca con ese nombre.', 409);
        }

        $data['nombre'] = $data['nombre'] ?? $marcaExistente['nombre'];
        $data['logo'] = array_key_exists('logo', $data) ? $data['logo'] : $marcaExistente['logo'];
        $data['descripcion'] = array_key_exists('descripcion', $data) ? $data['descripcion'] : $marcaExistente['descripcion'];
        $data['estado'] = $data['estado'] ?? $marcaExistente['estado'];

        $updated = Marca::actualizar($id, $data);

        if (!$updated) {
            Response::error('No se pudo actualizar la marca.', 500);
        }

        Response::success('Marca actualizada correctamente.', []);
    }

    public function destroy(int $id): void
    {
        $marcaExistente = Marca::obtenerPorId($id);

        if (!$marcaExistente) {
            Response::error('Marca no encontrada.', 404);
        }

        $deleted = Marca::eliminar($id);

        if (!$deleted) {
            Response::error('No se pudo eliminar la marca.', 500);
        }

        Response::success('Marca eliminada correctamente.', []);
    }

    public function cambiarEstado(int $id): void
    {
        $marcaExistente = Marca::obtenerPorId($id);

        if (!$marcaExistente) {
            Response::error('Marca no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $estado = Validator::sanitizeString($payload['estado'] ?? null);

        $errors = Validator::validate(['estado' => $estado], ['estado' => 'required|in:Activo,Inactivo']);
        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        $updated = Marca::cambiarEstado($id, $estado);

        if (!$updated) {
            Response::error('No se pudo cambiar el estado de la marca.', 500);
        }

        Response::success('Estado de la marca actualizado correctamente.', []);
    }

    public function activas(): void
    {
        $marcas = Marca::obtenerActivas();
        Response::success('Marcas activas obtenidas correctamente.', ['data' => $marcas]);
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

    private function sanitizeMarcaData(array $payload, bool $requireFields = true): array
    {
        return [
            'nombre' => Validator::sanitizeString($payload['nombre'] ?? null),
            'logo' => Validator::sanitizeUrl($payload['logo'] ?? null),
            'descripcion' => Validator::sanitizeString($payload['descripcion'] ?? null),
            'estado' => Validator::sanitizeString($payload['estado'] ?? null),
        ];
    }

    private function validateMarca(array $data, bool $requireFields = true): array
    {
        $rules = [
            'nombre' => $requireFields ? 'required|max:120' : 'nullable|max:120',
            'logo' => 'nullable|url',
            'descripcion' => 'nullable',
            'estado' => 'nullable|in:Activo,Inactivo',
        ];

        return Validator::validate($data, $rules);
    }
}
