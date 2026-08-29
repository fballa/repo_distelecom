<?php

require_once __DIR__ . '/../models/Servicio.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class ServicioController
{
    public function index(): void
    {
        $filtros = [
            'nombre' => $_GET['nombre'] ?? null,
            'estado' => $_GET['estado'] ?? null,
        ];

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 20;

        $result = Servicio::obtenerTodos($filtros, $pagina, $limite);
        Response::success('Servicios listados correctamente.', $result);
    }

    public function show(int $id): void
    {
        $servicio = Servicio::obtenerPorId($id);

        if (!$servicio) {
            Response::error('Servicio no encontrado.', 404);
        }

        Response::success('Servicio obtenido correctamente.', $servicio);
    }

    public function store(): void
    {
        $payload = $this->getJsonInput();
        $data = $this->sanitizeServicioData($payload);
        $errors = $this->validateServicio($data);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (Servicio::existeNombre($data['nombre'])) {
            Response::error('Ya existe un servicio con ese nombre.', 409);
        }

        if (empty($data['slug'])) {
            $data['slug'] = $this->generarSlug($data['nombre']);
        }

        if (!empty($data['slug']) && Servicio::existeSlug($data['slug'])) {
            Response::error('Ya existe un servicio con ese slug.', 409);
        }

        $servicioId = Servicio::crear($data);

        if ($servicioId === null) {
            Response::error('No se pudo crear el servicio.', 500);
        }

        Response::success('Servicio creado correctamente.', ['id' => $servicioId], 201);
    }

    public function update(int $id): void
    {
        $servicioExistente = Servicio::obtenerPorId($id);

        if (!$servicioExistente) {
            Response::error('Servicio no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $data = $this->sanitizeServicioData($payload, false);
        $errors = $this->validateServicio($data, false);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (!empty($data['nombre']) && Servicio::existeNombre($data['nombre'], $id)) {
            Response::error('Ya existe un servicio con ese nombre.', 409);
        }

        if (!empty($data['slug']) && Servicio::existeSlug($data['slug'], $id)) {
            Response::error('Ya existe un servicio con ese slug.', 409);
        }

        $data['nombre'] = $data['nombre'] ?? $servicioExistente['nombre'];
        $data['slug'] = $data['slug'] ?? $servicioExistente['slug'];
        $data['descripcion'] = $data['descripcion'] ?? $servicioExistente['descripcion'];
        $data['icono'] = array_key_exists('icono', $data) ? $data['icono'] : $servicioExistente['icono'];
        $data['imagen'] = array_key_exists('imagen', $data) ? $data['imagen'] : $servicioExistente['imagen'];
        $data['orden'] = $data['orden'] ?? $servicioExistente['orden'];
        $data['estado'] = $data['estado'] ?? $servicioExistente['estado'];

        $updated = Servicio::actualizar($id, $data);

        if (!$updated) {
            Response::error('No se pudo actualizar el servicio.', 500);
        }

        Response::success('Servicio actualizado correctamente.', []);
    }

    public function destroy(int $id): void
    {
        $servicioExistente = Servicio::obtenerPorId($id);

        if (!$servicioExistente) {
            Response::error('Servicio no encontrado.', 404);
        }

        $deleted = Servicio::eliminar($id);

        if (!$deleted) {
            Response::error('No se pudo eliminar el servicio.', 500);
        }

        Response::success('Servicio eliminado correctamente.', []);
    }

    public function cambiarEstado(int $id): void
    {
        $servicioExistente = Servicio::obtenerPorId($id);

        if (!$servicioExistente) {
            Response::error('Servicio no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $estado = Validator::sanitizeString($payload['estado'] ?? null);

        $errors = Validator::validate(['estado' => $estado], ['estado' => 'required|in:Activo,Inactivo']);
        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        $updated = Servicio::cambiarEstado($id, $estado);

        if (!$updated) {
            Response::error('No se pudo cambiar el estado del servicio.', 500);
        }

        Response::success('Estado del servicio actualizado correctamente.', []);
    }

    public function activos(): void
    {
        $servicios = Servicio::obtenerActivos();
        Response::success('Servicios activos obtenidos correctamente.', ['data' => $servicios]);
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

    private function sanitizeServicioData(array $payload, bool $requireFields = true): array
    {
        return [
            'nombre' => Validator::sanitizeString($payload['nombre'] ?? null),
            'slug' => Validator::sanitizeString($payload['slug'] ?? null),
            'descripcion' => Validator::sanitizeString($payload['descripcion'] ?? null),
            'icono' => Validator::sanitizeString($payload['icono'] ?? null),
            'imagen' => Validator::sanitizeUrl($payload['imagen'] ?? null),
            'orden' => isset($payload['orden']) ? (int) $payload['orden'] : null,
            'estado' => Validator::sanitizeString($payload['estado'] ?? null),
        ];
    }

    private function validateServicio(array $data, bool $requireFields = true): array
    {
        $rules = [
            'nombre' => $requireFields ? 'required|max:120' : 'nullable|max:120',
            'slug' => $requireFields ? 'nullable|max:150' : 'nullable|max:150',
            'descripcion' => $requireFields ? 'required' : 'nullable',
            'icono' => 'nullable|max:100',
            'imagen' => 'nullable|url',
            'orden' => 'nullable|integer',
            'estado' => 'nullable|in:Activo,Inactivo',
        ];

        return Validator::validate($data, $rules);
    }

    private function generarSlug(string $nombre): string
    {
        $slug = strtolower(trim($nombre));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }
}
