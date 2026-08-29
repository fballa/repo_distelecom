<?php

require_once __DIR__ . '/../controllers/ServicioController.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$basePath = rtrim(str_replace('\\', '/', dirname($scriptPath)), '/');

if ($basePath !== '' && $basePath !== '/' && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
}

$uri = trim($uri, '/');
$segments = array_values(array_filter(explode('/', $uri)));

if (isset($segments[0]) && $segments[0] === 'apitelecom') {
    array_shift($segments);
}
if (isset($segments[0]) && $segments[0] === 'index.php') {
    array_shift($segments);
}

$controller = new ServicioController();

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (count($segments) >= 2 && $segments[0] === 'api' && $segments[1] === 'servicios') {
    if (isset($segments[2]) && $segments[2] === 'activos' && $method === 'GET') {
        $controller->activos();
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
