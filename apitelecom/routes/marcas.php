<?php

require_once __DIR__ . '/../controllers/MarcaController.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = preg_replace('#^/apitelecom#', '', $uri);
$uri = preg_replace('#^/index\.php#', '', $uri);
$uri = trim($uri, '/');
$segments = array_values(array_filter(explode('/', $uri)));

$controller = new MarcaController();

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (count($segments) >= 2 && $segments[0] === 'api' && $segments[1] === 'marcas') {
    if (isset($segments[2]) && $segments[2] === 'activas' && $method === 'GET') {
        $controller->activas();
        return;
    }

    $id = isset($segments[2]) && is_numeric($segments[2]) ? (int) $segments[2] : null;
    $action = $segments[3] ?? null;

    switch ($method) {
        case 'GET':
            if ($id === null) {
                $controller->index();
                return;
            }
            $controller->show($id);
            return;

        case 'POST':
            if ($id === null) {
                $controller->store();
                return;
            }
            break;

        case 'PUT':
            if ($id !== null && $action === null) {
                $controller->update($id);
                return;
            }
            break;

        case 'PATCH':
            if ($id !== null && $action === 'estado') {
                $controller->cambiarEstado($id);
                return;
            }
            break;

        case 'DELETE':
            if ($id !== null && $action === null) {
                $controller->destroy($id);
                return;
            }
            break;

        default:
            Response::error('Método HTTP no soportado.', 405);
            return;
    }
}

Response::error('Ruta no encontrada.', 404);
