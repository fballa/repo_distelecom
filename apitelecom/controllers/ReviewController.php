<?php

require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class ReviewController
{
    public function index(): void
    {
        $filtros = [
            'producto_id' => isset($_GET['producto_id']) && is_numeric($_GET['producto_id']) ? (int) $_GET['producto_id'] : null,
            'estado' => $_GET['estado'] ?? null,
            'calificacion' => isset($_GET['calificacion']) && is_numeric($_GET['calificacion']) ? (int) $_GET['calificacion'] : null,
            'nombre' => $_GET['nombre'] ?? null,
        ];

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 20;

        $result = Review::obtenerTodos($filtros, $pagina, $limite);
        Response::success('Reseñas listadas correctamente.', $result);
    }

    public function show(int $id): void
    {
        $review = Review::obtenerPorId($id);

        if (!$review) {
            Response::error('Reseña no encontrada.', 404);
        }

        Response::success('Reseña obtenida correctamente.', $review);
    }

    public function store(): void
    {
        $payload = $this->getJsonInput();
        $data = $this->sanitizeReviewData($payload);
        $errors = $this->validateReview($data);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (!Review::existeProducto($data['producto_id'])) {
            Response::error('El producto seleccionado no existe.', 422);
        }

        $data['estado'] = $data['estado'] ?? 'Pendiente';

        $reviewId = Review::crear($data);

        if ($reviewId === null) {
            Response::error('No se pudo crear la reseña.', 500);
        }

        Response::success('Reseña creada correctamente. Está pendiente de aprobación.', ['id' => $reviewId], 201);
    }

    public function update(int $id): void
    {
        $reviewExistente = Review::obtenerPorId($id);

        if (!$reviewExistente) {
            Response::error('Reseña no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $data = $this->sanitizeReviewData($payload, false);
        $errors = $this->validateReview($data, false);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        $data['nombre'] = $data['nombre'] ?? $reviewExistente['nombre'];
        $data['correo'] = array_key_exists('correo', $data) ? $data['correo'] : $reviewExistente['correo'];
        $data['calificacion'] = $data['calificacion'] ?? $reviewExistente['calificacion'];
        $data['comentario'] = $data['comentario'] ?? $reviewExistente['comentario'];
        $data['estado'] = $data['estado'] ?? $reviewExistente['estado'];

        $updated = Review::actualizar($id, $data);

        if (!$updated) {
            Response::error('No se pudo actualizar la reseña.', 500);
        }

        Response::success('Reseña actualizada correctamente.', []);
    }

    public function destroy(int $id): void
    {
        if (!Review::existeReview($id)) {
            Response::error('Reseña no encontrada.', 404);
        }

        $deleted = Review::eliminar($id);

        if (!$deleted) {
            Response::error('No se pudo eliminar la reseña.', 500);
        }

        Response::success('Reseña eliminada correctamente.', []);
    }

    public function cambiarEstado(int $id): void
    {
        if (!Review::existeReview($id)) {
            Response::error('Reseña no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $estado = Validator::sanitizeString($payload['estado'] ?? null);

        $errors = Validator::validate(['estado' => $estado], ['estado' => 'required|in:Pendiente,Publicado,Oculto']);
        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        $updated = Review::cambiarEstado($id, $estado);

        if (!$updated) {
            Response::error('No se pudo cambiar el estado de la reseña.', 500);
        }

        Response::success('Estado de la reseña actualizado correctamente.', []);
    }

    public function porProducto(int $producto_id): void
    {
        if (!Review::existeProducto($producto_id)) {
            Response::error('El producto seleccionado no existe.', 404);
        }

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 10;

        $result = Review::obtenerPorProducto($producto_id, $pagina, $limite);
        Response::success('Reseñas del producto obtenidas correctamente.', $result);
    }

    public function promedio(int $producto_id): void
    {
        if (!Review::existeProducto($producto_id)) {
            Response::error('El producto seleccionado no existe.', 404);
        }

        $result = Review::obtenerPromedio($producto_id);
        Response::success('Promedio de calificaciones obtenido correctamente.', ['producto_id' => $producto_id, 'promedio' => $result['promedio'], 'total_resenas' => $result['total']]);
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

    private function sanitizeReviewData(array $payload, bool $requireFields = true): array
    {
        return [
            'producto_id' => isset($payload['producto_id']) ? (int) $payload['producto_id'] : null,
            'nombre' => Validator::sanitizeString($payload['nombre'] ?? null),
            'correo' => Validator::sanitizeString($payload['correo'] ?? null),
            'calificacion' => isset($payload['calificacion']) ? (int) $payload['calificacion'] : null,
            'comentario' => Validator::sanitizeString($payload['comentario'] ?? null),
            'estado' => Validator::sanitizeString($payload['estado'] ?? null),
        ];
    }

    private function validateReview(array $data, bool $requireFields = true): array
    {
        $rules = [
            'producto_id' => $requireFields ? 'required' : 'nullable',
            'nombre' => $requireFields ? 'required|max:120' : 'nullable|max:120',
            'correo' => 'nullable|email|max:150',
            'calificacion' => $requireFields ? 'required|integer' : 'nullable|integer',
            'comentario' => $requireFields ? 'required' : 'nullable',
            'estado' => $requireFields ? 'nullable|in:Pendiente,Publicado,Oculto' : 'nullable|in:Pendiente,Publicado,Oculto',
        ];

        $errors = Validator::validate($data, $rules);

        if (($requireFields && $data['calificacion'] !== null) || (!$requireFields && $data['calificacion'] !== null)) {
            if ($data['calificacion'] < 1 || $data['calificacion'] > 5) {
                $errors['calificacion'] = 'La calificación debe estar entre 1 y 5.';
            }
        }

        return $errors;
    }
}
