<?php

require_once __DIR__ . '/../models/Oferta.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class OfertaController
{
    public function index(): void
    {
        $filtros = [
            'producto_id' => $_GET['producto_id'] ?? null,
            'estado' => $_GET['estado'] ?? null,
            'fecha_inicio_desde' => $_GET['fecha_inicio_desde'] ?? null,
            'fecha_inicio_hasta' => $_GET['fecha_inicio_hasta'] ?? null,
            'fecha_fin_desde' => $_GET['fecha_fin_desde'] ?? null,
            'fecha_fin_hasta' => $_GET['fecha_fin_hasta'] ?? null,
            'buscar' => $_GET['buscar'] ?? null,
        ];

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 20;

        $result = Oferta::obtenerTodos($filtros, $pagina, $limite);
        Response::success('Ofertas listadas correctamente.', $result);
    }

    public function show(int $id): void
    {
        $oferta = Oferta::obtenerPorId($id);

        if (!$oferta) {
            Response::error('Oferta no encontrada.', 404);
        }

        Response::success('Oferta obtenida correctamente.', $oferta);
    }

    public function store(): void
    {
        $payload = $this->getJsonInput();
        $data = $this->sanitizeOfertaData($payload);
        $errors = $this->validateOferta($data);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        $producto = Producto::obtenerPorId($data['producto_id']);
        if (!$producto) {
            Response::error('El producto no existe.', 422);
        }

        if (Oferta::existeOfertaParaProducto($data['producto_id'], null, $data['fecha_inicio'], $data['fecha_fin'])) {
            Response::error('Ya existe una oferta activa o programada para este producto en el mismo rango de fechas.', 409);
        }

        $ofertaId = Oferta::crear($data);

        if ($ofertaId === null) {
            Response::error('No se pudo crear la oferta.', 500);
        }

        Response::success('Oferta creada correctamente.', ['id' => $ofertaId], 201);
    }

    public function update(int $id): void
    {
        $ofertaExistente = Oferta::obtenerPorId($id);
        if (!$ofertaExistente) {
            Response::error('Oferta no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $data = $this->sanitizeOfertaData($payload);
        $errors = $this->validateOferta($data, $id);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        $producto = Producto::obtenerPorId($data['producto_id']);
        if (!$producto) {
            Response::error('El producto no existe.', 422);
        }

        if (Oferta::existeOfertaParaProducto($data['producto_id'], $id, $data['fecha_inicio'], $data['fecha_fin'])) {
            Response::error('Ya existe otra oferta activa o programada para este producto en el mismo rango de fechas.', 409);
        }

        $updated = Oferta::actualizar($id, $data);
        if (!$updated) {
            Response::error('No se pudo actualizar la oferta.', 500);
        }

        Response::success('Oferta actualizada correctamente.', []);
    }

    public function destroy(int $id): void
    {
        $ofertaExistente = Oferta::obtenerPorId($id);
        if (!$ofertaExistente) {
            Response::error('Oferta no encontrada.', 404);
        }

        $deleted = Oferta::eliminar($id);
        if (!$deleted) {
            Response::error('No se pudo finalizar la oferta.', 500);
        }

        Response::success('Oferta finalizada correctamente.', []);
    }

    public function cambiarEstado(int $id): void
    {
        $ofertaExistente = Oferta::obtenerPorId($id);
        if (!$ofertaExistente) {
            Response::error('Oferta no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $estado = Validator::sanitizeString($payload['estado'] ?? null);

        if (!in_array($estado, ['Activa', 'Finalizada', 'Programada'], true)) {
            Response::error('El estado debe ser Activa, Finalizada o Programada.', 422);
        }

        $updated = Oferta::cambiarEstado($id, $estado);
        if (!$updated) {
            Response::error('No se pudo cambiar el estado de la oferta.', 500);
        }

        Response::success('Estado de oferta actualizado correctamente.', []);
    }

    public function activas(): void
    {
        $items = Oferta::obtenerActivas();
        Response::success('Ofertas activas obtenidas correctamente.', ['data' => $items]);
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

    private function sanitizeOfertaData(array $payload): array
    {
        return [
            'producto_id' => isset($payload['producto_id']) ? (int) $payload['producto_id'] : null,
            'titulo' => Validator::sanitizeString($payload['titulo'] ?? null),
            'descripcion' => Validator::sanitizeString($payload['descripcion'] ?? null),
            'porcentaje' => isset($payload['porcentaje']) ? (float) $payload['porcentaje'] : null,
            'precio_oferta' => isset($payload['precio_oferta']) ? (float) $payload['precio_oferta'] : null,
            'fecha_inicio' => Validator::sanitizeString($payload['fecha_inicio'] ?? null),
            'fecha_fin' => Validator::sanitizeString($payload['fecha_fin'] ?? null),
            'estado' => Validator::sanitizeString($payload['estado'] ?? 'Activa'),
        ];
    }

    private function validateOferta(array $data, ?int $id = null): array
    {
        $rules = [
            'producto_id' => 'required|integer',
            'titulo' => 'nullable|max:150',
            'descripcion' => 'nullable',
            'porcentaje' => 'nullable|numeric',
            'precio_oferta' => 'nullable|numeric',
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
            'estado' => 'nullable|in:Activa,Finalizada,Programada',
        ];

        $errors = Validator::validate($data, $rules);

        if (!empty($data['porcentaje']) && ($data['porcentaje'] < 0 || $data['porcentaje'] > 100)) {
            $errors['porcentaje'] = 'El porcentaje debe estar entre 0 y 100.';
        }

        if (isset($data['precio_oferta']) && $data['precio_oferta'] < 0) {
            $errors['precio_oferta'] = 'El precio de oferta debe ser mayor o igual a 0.';
        }

        $inicio = $this->validarFecha($data['fecha_inicio']);
        $fin = $this->validarFecha($data['fecha_fin']);

        if (!$inicio) {
            $errors['fecha_inicio'] = 'La fecha de inicio debe tener formato YYYY-MM-DD.';
        }

        if (!$fin) {
            $errors['fecha_fin'] = 'La fecha de fin debe tener formato YYYY-MM-DD.';
        }

        if ($inicio && $fin && $inicio > $fin) {
            $errors['fecha_fin'] = 'La fecha de fin debe ser mayor o igual a la fecha de inicio.';
        }

        if ($data['porcentaje'] === null && $data['precio_oferta'] === null) {
            $errors['precio_oferta'] = 'Debe enviar precio_oferta o porcentaje.';
        }

        return $errors;
    }

    private function validarFecha(?string $fecha): ?DateTime
    {
        if ($fecha === null) {
            return null;
        }

        $date = DateTime::createFromFormat('Y-m-d', $fecha);
        return $date && $date->format('Y-m-d') === $fecha ? $date : null;
    }
}
