<?php

require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Marca.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class ProductoController
{
    public function index(): void
    {
        $filtros = [
            'categoria_id' => isset($_GET['categoria_id']) && is_numeric($_GET['categoria_id']) ? (int) $_GET['categoria_id'] : null,
            'marca_id' => isset($_GET['marca_id']) && is_numeric($_GET['marca_id']) ? (int) $_GET['marca_id'] : null,
            'estado' => $_GET['estado'] ?? null,
            'destacado' => isset($_GET['destacado']) ? (int) $_GET['destacado'] : null,
            'nuevo' => isset($_GET['nuevo']) ? (int) $_GET['nuevo'] : null,
            'oferta' => isset($_GET['oferta']) ? (int) $_GET['oferta'] : null,
            'buscar' => $_GET['buscar'] ?? null,
        ];

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 20;

        $result = Producto::obtenerTodos($filtros, $pagina, $limite);
        Response::success('Productos listados correctamente.', $result);
    }

    public function show(int $id): void
    {
        $producto = Producto::obtenerPorId($id);

        if (!$producto) {
            Response::error('Producto no encontrado.', 404);
        }

        Response::success('Producto obtenido correctamente.', $producto);
    }

    public function store(): void
    {
        $payload = $this->getJsonInput();
        $data = $this->sanitizeProductoData($payload, true);
        $errors = $this->validateProducto($data, true);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (Producto::existeSku($data['sku'])) {
            Response::error('Ya existe un producto con ese SKU.', 409);
        }

        if (!empty($data['slug']) && Producto::existeSlug($data['slug'])) {
            Response::error('Ya existe un producto con ese slug.', 409);
        }

        if (!Categoria::existeId($data['categoria_id'])) {
            Response::error('La categoría no existe.', 422);
        }

        if (!Marca::existeId($data['marca_id'])) {
            Response::error('La marca no existe.', 422);
        }

        $productoId = Producto::crear($data);

        if ($productoId === null) {
            Response::error('No se pudo crear el producto.', 500);
        }

        Response::success('Producto creado correctamente.', ['id' => $productoId], 201);
    }

    public function update(int $id): void
    {
        $productoExistente = Producto::obtenerPorId($id);

        if (!$productoExistente) {
            Response::error('Producto no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $data = $this->sanitizeProductoData($payload, false);
        $errors = $this->validateProducto($data, false);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (!empty($data['sku']) && Producto::existeSku($data['sku'], $id)) {
            Response::error('Ya existe otro producto con ese SKU.', 409);
        }

        if (!empty($data['slug']) && Producto::existeSlug($data['slug'], $id)) {
            Response::error('Ya existe otro producto con ese slug.', 409);
        }

        if (isset($data['categoria_id']) && !Categoria::existeId($data['categoria_id'])) {
            Response::error('La categoría no existe.', 422);
        }

        if (isset($data['marca_id']) && !Marca::existeId($data['marca_id'])) {
            Response::error('La marca no existe.', 422);
        }

        if (empty($data['slug']) && !empty($data['nombre'])) {
            $data['slug'] = $this->generarSlug($data['nombre']);
        }

        $updated = Producto::actualizar($id, $data);

        if (!$updated) {
            Response::error('No se pudo actualizar el producto.', 500);
        }

        Response::success('Producto actualizado correctamente.', []);
    }

    public function destroy(int $id): void
    {
        $productoExistente = Producto::obtenerPorId($id);

        if (!$productoExistente) {
            Response::error('Producto no encontrado.', 404);
        }

        $deleted = Producto::eliminar($id);

        if (!$deleted) {
            Response::error('No se pudo eliminar el producto.', 500);
        }

        Response::success('Producto eliminado correctamente.', []);
    }

    public function actualizarImagenes(int $id): void
    {
        $productoExistente = Producto::obtenerPorId($id);

        if (!$productoExistente) {
            Response::error('Producto no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $imagenes = $payload['imagenes'] ?? null;

        if (!is_array($imagenes)) {
            Response::error('El campo imágenes debe ser un arreglo.', 422);
        }

        $imagenes = $this->sanitizeImagenes($imagenes);
        $updated = Producto::actualizarImagenes($id, $imagenes);

        if (!$updated) {
            Response::error('No se pudieron actualizar las imágenes del producto.', 500);
        }

        Response::success('Imágenes del producto actualizadas correctamente.', []);
    }

    public function actualizarEspecificacion(int $id): void
    {
        $productoExistente = Producto::obtenerPorId($id);

        if (!$productoExistente) {
            Response::error('Producto no encontrado.', 404);
        }

        $payload = $this->getJsonInput();

        if (!is_array($payload)) {
            Response::error('La especificación debe ser un objeto JSON.', 422);
        }

        $especificaciones = Validator::sanitizeArray($payload);
        $updated = Producto::actualizarEspecificacion($id, $especificaciones);

        if (!$updated) {
            Response::error('No se pudieron actualizar las especificaciones del producto.', 500);
        }

        Response::success('Especificaciones del producto actualizadas correctamente.', []);
    }

    public function actualizarInventario(int $id): void
    {
        $productoExistente = Producto::obtenerPorId($id);

        if (!$productoExistente) {
            Response::error('Producto no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $stock = isset($payload['stock']) ? $this->sanitizeInteger($payload['stock']) : null;
        $stockMinimo = isset($payload['stock_minimo']) ? $this->sanitizeInteger($payload['stock_minimo']) : null;

        if ($stock === null && $stockMinimo === null) {
            Response::error('Debe enviar stock o stock_minimo.', 422);
        }

        $updated = Producto::actualizarInventario($id, $stock, $stockMinimo);

        if (!$updated) {
            Response::error('No se pudo actualizar el inventario del producto.', 500);
        }

        Response::success('Inventario del producto actualizado correctamente.', []);
    }

    public function cambiarEstado(int $id): void
    {
        $productoExistente = Producto::obtenerPorId($id);

        if (!$productoExistente) {
            Response::error('Producto no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $estado = Validator::sanitizeString($payload['estado'] ?? null);

        $errors = Validator::validate(['estado' => $estado], ['estado' => 'required|in:Activo,Inactivo']);
        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        $updated = Producto::cambiarEstado($id, $estado);

        if (!$updated) {
            Response::error('No se pudo cambiar el estado del producto.', 500);
        }

        Response::success('Estado del producto actualizado correctamente.', []);
    }

    public function cambiarDestacado(int $id): void
    {
        $this->cambiarFlag($id, 'destacado');
    }

    public function cambiarNuevo(int $id): void
    {
        $this->cambiarFlag($id, 'nuevo');
    }

    public function cambiarOferta(int $id): void
    {
        $this->cambiarFlag($id, 'oferta');
    }

    private function cambiarFlag(int $id, string $campo): void
    {
        $productoExistente = Producto::obtenerPorId($id);

        if (!$productoExistente) {
            Response::error('Producto no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $valor = $payload[$campo] ?? ($payload['valor'] ?? null);
        $valor = $this->sanitizeBoolean($valor);

        if ($valor === null) {
            Response::error('El campo ' . $campo . ' debe ser 0 o 1.', 422);
        }

        $updated = false;

        switch ($campo) {
            case 'destacado':
                $updated = Producto::cambiarDestacado($id, $valor);
                break;
            case 'nuevo':
                $updated = Producto::cambiarNuevo($id, $valor);
                break;
            case 'oferta':
                $updated = Producto::cambiarOferta($id, $valor);
                break;
            default:
                Response::error('Campo inválido para actualización.', 400);
        }

        if (!$updated) {
            Response::error('No se pudo actualizar el campo ' . $campo . ' del producto.', 500);
        }

        Response::success('Producto actualizado correctamente.', []);
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

    private function sanitizeProductoData(array $payload, bool $requireFields = true): array
    {
        $data = [
            'categoria_id' => isset($payload['categoria_id']) && is_numeric($payload['categoria_id']) ? (int) $payload['categoria_id'] : null,
            'marca_id' => isset($payload['marca_id']) && is_numeric($payload['marca_id']) ? (int) $payload['marca_id'] : null,
            'sku' => Validator::sanitizeString($payload['sku'] ?? null),
            'codigo_barras' => Validator::sanitizeString($payload['codigo_barras'] ?? null),
            'nombre' => Validator::sanitizeString($payload['nombre'] ?? null),
            'slug' => Validator::sanitizeString($payload['slug'] ?? null),
            'modelo' => Validator::sanitizeString($payload['modelo'] ?? null),
            'descripcion_corta' => Validator::sanitizeString($payload['descripcion_corta'] ?? null),
            'descripcion_larga' => Validator::sanitizeString($payload['descripcion_larga'] ?? null),
            'precio' => isset($payload['precio']) ? (float) $payload['precio'] : null,
            'precio_oferta' => isset($payload['precio_oferta']) ? (float) $payload['precio_oferta'] : null,
            'stock' => isset($payload['stock']) ? (int) $payload['stock'] : null,
            'stock_minimo' => isset($payload['stock_minimo']) ? (int) $payload['stock_minimo'] : null,
            'peso' => isset($payload['peso']) ? (float) $payload['peso'] : null,
            'alto' => isset($payload['alto']) ? (float) $payload['alto'] : null,
            'ancho' => isset($payload['ancho']) ? (float) $payload['ancho'] : null,
            'profundidad' => isset($payload['profundidad']) ? (float) $payload['profundidad'] : null,
            'garantia' => Validator::sanitizeString($payload['garantia'] ?? null),
            'imagen_principal' => Validator::sanitizeUrl($payload['imagen_principal'] ?? null),
            'destacado' => isset($payload['destacado']) ? $this->sanitizeBoolean($payload['destacado']) : null,
            'nuevo' => isset($payload['nuevo']) ? $this->sanitizeBoolean($payload['nuevo']) : null,
            'oferta' => isset($payload['oferta']) ? $this->sanitizeBoolean($payload['oferta']) : null,
            'estado' => Validator::sanitizeString($payload['estado'] ?? null),
            'seo_title' => Validator::sanitizeString($payload['seo_title'] ?? null),
            'seo_description' => Validator::sanitizeString($payload['seo_description'] ?? null),
        ];

        if (!$requireFields) {
            foreach ($data as $key => $value) {
                if ($value === null && !array_key_exists($key, $payload)) {
                    unset($data[$key]);
                }
            }
        }

        if (isset($payload['imagenes']) && is_array($payload['imagenes'])) {
            $data['imagenes'] = $this->sanitizeImagenes($payload['imagenes']);
        }

        if (isset($payload['especificaciones']) && is_array($payload['especificaciones'])) {
            $data['especificaciones'] = Validator::sanitizeArray($payload['especificaciones']);
        }

        if (isset($payload['inventario']) && is_array($payload['inventario'])) {
            $data['inventario'] = [
                'stock_actual' => isset($payload['inventario']['stock_actual']) ? (int) $payload['inventario']['stock_actual'] : null,
                'stock_minimo' => isset($payload['inventario']['stock_minimo']) ? (int) $payload['inventario']['stock_minimo'] : null,
            ];
        }

        return $data;
    }

    private function validateProducto(array $data, bool $requireFields = true): array
    {
        $rules = [
            'categoria_id' => $requireFields ? 'required|integer' : 'nullable|integer',
            'marca_id' => $requireFields ? 'required|integer' : 'nullable|integer',
            'sku' => $requireFields ? 'required|max:120' : 'nullable|max:120',
            'codigo_barras' => 'nullable|max:120',
            'nombre' => $requireFields ? 'required|max:255' : 'nullable|max:255',
            'slug' => 'nullable|max:255',
            'modelo' => 'nullable|max:120',
            'descripcion_corta' => 'nullable|max:500',
            'descripcion_larga' => 'nullable',
            'precio' => $requireFields ? 'required|numeric' : 'nullable|numeric',
            'precio_oferta' => 'nullable|numeric',
            'stock' => 'nullable|integer',
            'stock_minimo' => 'nullable|integer',
            'peso' => 'nullable|numeric',
            'alto' => 'nullable|numeric',
            'ancho' => 'nullable|numeric',
            'profundidad' => 'nullable|numeric',
            'garantia' => 'nullable|max:120',
            'imagen_principal' => 'nullable|url',
            'destacado' => 'nullable|boolean',
            'nuevo' => 'nullable|boolean',
            'oferta' => 'nullable|boolean',
            'estado' => $requireFields ? 'required|in:Activo,Inactivo' : 'nullable|in:Activo,Inactivo',
            'seo_title' => 'nullable|max:255',
            'seo_description' => 'nullable|max:500',
            'imagenes' => 'nullable|array',
            'especificaciones' => 'nullable|array',
            'inventario' => 'nullable|array',
        ];

        return Validator::validate($data, $rules);
    }

    private function sanitizeImagenes(array $imagenes): array
    {
        $result = [];
        foreach ($imagenes as $imagen) {
            if (!is_array($imagen)) {
                continue;
            }

            $result[] = [
                'imagen' => Validator::sanitizeUrl($imagen['imagen'] ?? null),
                'orden' => isset($imagen['orden']) ? (int) $imagen['orden'] : 0,
            ];
        }

        return $result;
    }

    private function sanitizeBoolean($value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_numeric($value)) {
            return ((int) $value) === 1 ? 1 : 0;
        }

        $value = strtolower(trim((string) $value));
        if (in_array($value, ['1', 'true', 'yes'], true)) {
            return 1;
        }

        if (in_array($value, ['0', 'false', 'no'], true)) {
            return 0;
        }

        return null;
    }

    private function sanitizeInteger($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private function generarSlug(string $nombre): string
    {
        $slug = strtolower(trim($nombre));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }
}
