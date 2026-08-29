<?php

require_once __DIR__ . '/../controllers/OfertaController.php';

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

$controller = new OfertaController();

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (count($segments) >= 2 && $segments[0] === 'api' && $segments[1] === 'ofertas') {
    $id = isset($segments[2]) && is_numeric($segments[2]) ? (int) $segments[2] : null;
    $action = $segments[3] ?? null;

    switch ($method) {
        case 'GET':
            if ($action === 'activas') {
                $controller->activas();
                break;
            }
            if ($id === null) {
                $controller->index();
                break;
            }
            $controller->show($id);
            break;

        case 'POST':
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

        case 'DELETE':
            if ($id !== null && $action === null) {
                $controller->destroy($id);
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

        default:
            Response::error('Método HTTP no soportado.', 405);
            break;
    }
}
