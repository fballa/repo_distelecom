<?php

require_once __DIR__ . '/../controllers/CategoriaController.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = preg_replace('#^/apitelecom#', '', $uri);
$uri = preg_replace('#^/index\.php#', '', $uri);
$uri = trim($uri, '/');
$segments = array_values(array_filter(explode('/', $uri)));

$controller = new CategoriaController();

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (count($segments) >= 2 && $segments[0] === 'api' && $segments[1] === 'categorias') {
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
            if ($action === 'reordenar') {
                $controller->reordenar();
                break;
            }
            if ($id === null) {
                $controller->store();
                break;
            }
            Response::error('Ruta no encontrada.', 404);
            break;

        case 'PUT':
            if ($id !== null && $action === null) {
                $controller->update($id);
                break;
            }
            Response::error('Ruta no encontrada.', 404);
            break;

        case 'PATCH':
            if ($id !== null && $action === 'estado') {
                $controller->cambiarEstado($id);
                break;
            }
            Response::error('Ruta no encontrada.', 404);
            break;

        case 'DELETE':
            if ($id !== null && $action === null) {
                $controller->destroy($id);
                break;
            }
            Response::error('Ruta no encontrada.', 404);
            break;

        default:
            Response::error('Método HTTP no soportado.', 405);
            break;
    }
}
