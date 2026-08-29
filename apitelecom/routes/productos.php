<?php

require_once __DIR__ . '/../controllers/ProductoController.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = preg_replace('#^/apitelecom#', '', $uri);
$uri = preg_replace('#^/index\.php#', '', $uri);
$uri = trim($uri, '/');
$segments = array_values(array_filter(explode('/', $uri)));

$controller = new ProductoController();

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (count($segments) >= 2 && $segments[0] === 'api' && $segments[1] === 'productos') {
    $id = isset($segments[2]) && is_numeric($segments[2]) ? (int) $segments[2] : null;
    $action = $segments[3] ?? null;

    switch ($method) {
        case 'GET':
            if ($id === null) {
                $controller->index();
                break;
            }

            $controller->show($id);
            break;

        case 'POST':
            if ($id === null) {
                $controller->store();
            }
            Response::error('Ruta no encontrada.', 404);
            break;

        case 'PUT':
            if ($id !== null && $action === null) {
                $controller->update($id);
            }

            if ($id !== null && $action === 'imagenes') {
                $controller->actualizarImagenes($id);
            }

            if ($id !== null && $action === 'especificacion') {
                $controller->actualizarEspecificacion($id);
            }

            Response::error('Ruta no encontrada.', 404);
            break;

        case 'PATCH':
            if ($id !== null && $action === 'inventario') {
                $controller->actualizarInventario($id);
            }

            if ($id !== null && $action === 'estado') {
                $controller->cambiarEstado($id);
            }

            if ($id !== null && $action === 'destacado') {
                $controller->cambiarDestacado($id);
            }

            if ($id !== null && $action === 'nuevo') {
                $controller->cambiarNuevo($id);
            }

            if ($id !== null && $action === 'oferta') {
                $controller->cambiarOferta($id);
            }

            Response::error('Ruta no encontrada.', 404);
            break;

        case 'DELETE':
            if ($id !== null && $action === null) {
                $controller->destroy($id);
            }
            Response::error('Ruta no encontrada.', 404);
            break;

        default:
            Response::error('Método HTTP no soportado.', 405);
            break;
    }
}
