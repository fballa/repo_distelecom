<?php

require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class CategoriaController
{
    public function index(): void
    {
        $filtros = [
            'estado' => $_GET['estado'] ?? null,
            'buscar' => $_GET['buscar'] ?? null,
        ];

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 20;

        $result = Categoria::obtenerTodos($filtros, $pagina, $limite);
        Response::success('Categorías listadas correctamente.', $result);
    }

    public function show(int $id): void
    {
        $categoria = Categoria::obtenerPorId($id);

        if (!$categoria) {
            Response::error('Categoría no encontrada.', 404);
        }

        Response::success('Categoría obtenida correctamente.', $categoria);
    }

    public function store(): void
    {
        $payload = $this->getJsonInput();
        $data = $this->sanitizeCategoriaData($payload);
        $errors = $this->validateCategoria($data);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (empty($data['slug'])) {
            $data['slug'] = $this->generarSlug($data['nombre']);
        }

        if (Categoria::existeSlug($data['slug'])) {
            Response::error('El slug ya existe.', 409);
        }

        $categoriaId = Categoria::crear($data);

        if ($categoriaId === null) {
            Response::error('No se pudo crear la categoría.', 500);
        }

        Response::success('Categoría creada correctamente.', ['id' => $categoriaId], 201);
    }

    public function update(int $id): void
    {
        $categoriaExistente = Categoria::obtenerPorId($id);

        if (!$categoriaExistente) {
            Response::error('Categoría no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $data = $this->sanitizeCategoriaData($payload);
        $errors = $this->validateCategoria($data, $id);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (empty($data['slug'])) {
            $data['slug'] = $this->generarSlug($data['nombre']);
        }

        if (Categoria::existeSlug($data['slug'], $id)) {
            Response::error('El slug ya existe.', 409);
        }

        $updated = Categoria::actualizar($id, $data);

        if (!$updated) {
            Response::error('No se pudo actualizar la categoría.', 500);
        }

        Response::success('Categoría actualizada correctamente.', []);
    }

    public function destroy(int $id): void
    {
        $categoriaExistente = Categoria::obtenerPorId($id);

        if (!$categoriaExistente) {
            Response::error('Categoría no encontrada.', 404);
        }

        $productos = Categoria::contarProductos($id);
        if ($productos > 0) {
            Response::error('No se puede eliminar la categoría porque tiene productos asociados.', 409);
        }

        $deleted = Categoria::eliminar($id);

        if (!$deleted) {
            Response::error('No se pudo eliminar la categoría.', 500);
        }

        Response::success('Categoría eliminada correctamente.', []);
    }

    public function cambiarEstado(int $id): void
    {
        $categoriaExistente = Categoria::obtenerPorId($id);

        if (!$categoriaExistente) {
            Response::error('Categoría no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $estado = $payload['estado'] ?? null;

        if (!in_array($estado, ['Activo', 'Inactivo'], true)) {
            Response::error('El estado debe ser Activo o Inactivo.', 422);
        }

        $updated = Categoria::cambiarEstado($id, $estado);

        if (!$updated) {
            Response::error('No se pudo cambiar el estado de la categoría.', 500);
        }

        Response::success('Estado de categoría actualizado correctamente.', []);
    }

    public function reordenar(): void
    {
        $payload = $this->getJsonInput();
        $items = $payload['categorias'] ?? null;

        if (!is_array($items)) {
            Response::error('El campo categorias debe ser un arreglo.', 422);
        }

        foreach ($items as $item) {
            if (!isset($item['id']) || !isset($item['orden']) || !is_numeric($item['orden'])) {
                Response::error('Cada categoría debe contener id y orden válidos.', 422);
            }

            $id = (int) $item['id'];
            $orden = (int) $item['orden'];

            if ($orden < 0) {
                Response::error('El orden debe ser mayor o igual a 0.', 422);
            }

            if (!Categoria::actualizarOrden($id, $orden)) {
                Response::error('No se pudo reordenar todas las categorías.', 500);
            }
        }

        Response::success('Categorías reordenadas correctamente.', []);
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

    private function sanitizeCategoriaData(array $payload): array
    {
        return [
            'nombre' => Validator::sanitizeString($payload['nombre'] ?? null),
            'slug' => Validator::sanitizeString($payload['slug'] ?? null),
            'descripcion' => Validator::sanitizeString($payload['descripcion'] ?? null),
            'imagen' => Validator::sanitizeUrl($payload['imagen'] ?? null),
            'icono' => Validator::sanitizeString($payload['icono'] ?? null),
            'orden' => isset($payload['orden']) ? (int) $payload['orden'] : 0,
            'estado' => Validator::sanitizeString($payload['estado'] ?? 'Activo'),
        ];
    }

    private function validateCategoria(array $data, ?int $id = null): array
    {
        $rules = [
            'nombre' => 'required|max:120',
            'slug' => 'nullable|max:150',
            'descripcion' => 'nullable',
            'imagen' => 'nullable|url',
            'icono' => 'nullable|max:100',
            'orden' => 'nullable|integer',
            'estado' => 'nullable|in:Activo,Inactivo',
        ];

        $errors = Validator::validate($data, $rules);

        if (isset($data['orden']) && $data['orden'] < 0) {
            $errors['orden'] = 'El orden debe ser mayor o igual a 0.';
        }

        return $errors;
    }

    private function generarSlug(string $nombre): string
    {
        $slug = strtolower(trim($nombre));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);

        return trim($slug, '-');
    }
}
