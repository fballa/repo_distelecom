<?php

require_once __DIR__ . '/../models/Novedad.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class NovedadController
{
    public function index(): void
    {
        $filtros = [
            'producto_id' => $_GET['producto_id'] ?? null,
            'estado' => $_GET['estado'] ?? null,
            'buscar' => $_GET['buscar'] ?? null,
        ];

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 20;

        $result = Novedad::obtenerTodos($filtros, $pagina, $limite);
        Response::success('Novedades listadas correctamente.', $result);
    }

    public function show(int $id): void
    {
        $novedad = Novedad::obtenerPorId($id);
        if (!$novedad) {
            Response::error('Novedad no encontrada.', 404);
        }

        Response::success('Novedad obtenida correctamente.', $novedad);
    }

    public function store(): void
    {
        $payload = $this->getJsonInput();
        $data = $this->sanitizeNovedadData($payload);
        $errors = $this->validateNovedad($data);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        $producto = Producto::obtenerPorId($data['producto_id']);
        if (!$producto) {
            Response::error('El producto no existe.', 422);
        }

        if (empty($data['titulo'])) {
            $data['titulo'] = $producto['nombre'];
        }

        $novedadId = Novedad::crear($data);
        if ($novedadId === null) {
            Response::error('No se pudo crear la novedad.', 500);
        }

        Response::success('Novedad creada correctamente.', ['id' => $novedadId], 201);
    }

    public function update(int $id): void
    {
        $novedadExistente = Novedad::obtenerPorId($id);
        if (!$novedadExistente) {
            Response::error('Novedad no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $data = $this->sanitizeNovedadData($payload);
        $errors = $this->validateNovedad($data, $id);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        $producto = Producto::obtenerPorId($data['producto_id']);
        if (!$producto) {
            Response::error('El producto no existe.', 422);
        }

        if (empty($data['titulo'])) {
            $data['titulo'] = $producto['nombre'];
        }

        $updated = Novedad::actualizar($id, $data);
        if (!$updated) {
            Response::error('No se pudo actualizar la novedad.', 500);
        }

        Response::success('Novedad actualizada correctamente.', []);
    }

    public function destroy(int $id): void
    {
        $novedadExistente = Novedad::obtenerPorId($id);
        if (!$novedadExistente) {
            Response::error('Novedad no encontrada.', 404);
        }

        $deleted = Novedad::eliminar($id);
        if (!$deleted) {
            Response::error('No se pudo ocultar la novedad.', 500);
        }

        Response::success('Novedad ocultada correctamente.', []);
    }

    public function cambiarEstado(int $id): void
    {
        $novedadExistente = Novedad::obtenerPorId($id);
        if (!$novedadExistente) {
            Response::error('Novedad no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $estado = Validator::sanitizeString($payload['estado'] ?? null);

        if (!in_array($estado, ['Publicado', 'Oculto'], true)) {
            Response::error('El estado debe ser Publicado u Oculto.', 422);
        }

        $updated = Novedad::cambiarEstado($id, $estado);
        if (!$updated) {
            Response::error('No se pudo cambiar el estado de la novedad.', 500);
        }

        Response::success('Estado de novedad actualizado correctamente.', []);
    }

    public function publicadas(): void
    {
        $items = Novedad::obtenerPublicadas();
        Response::success('Novedades publicadas obtenidas correctamente.', ['data' => $items]);
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

    private function sanitizeNovedadData(array $payload): array
    {
        return [
            'producto_id' => isset($payload['producto_id']) ? (int) $payload['producto_id'] : null,
            'titulo' => Validator::sanitizeString($payload['titulo'] ?? null),
            'descripcion' => Validator::sanitizeString($payload['descripcion'] ?? null),
            'imagen' => Validator::sanitizeUrl($payload['imagen'] ?? null),
            'estado' => Validator::sanitizeString($payload['estado'] ?? 'Publicado'),
        ];
    }

    private function validateNovedad(array $data, ?int $id = null): array
    {
        $rules = [
            'producto_id' => 'required|integer',
            'titulo' => 'nullable|max:200',
            'descripcion' => 'nullable',
            'imagen' => 'nullable|url',
            'estado' => 'nullable|in:Publicado,Oculto',
        ];

        return Validator::validate($data, $rules);
    }
}
