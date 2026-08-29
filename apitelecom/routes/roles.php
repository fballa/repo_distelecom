<?php

require_once __DIR__ . '/../controllers/RolController.php';
require_once __DIR__ . '/../helpers/Response.php';

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

$controller = new RolController();

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (count($segments) >= 2 && $segments[0] === 'api' && $segments[1] === 'roles') {
    $id = isset($segments[2]) && is_numeric($segments[2]) ? (int) $segments[2] : null;

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
            if ($id !== null) {
                $controller->update($id);
                return;
            }
            break;

        case 'DELETE':
            if ($id !== null) {
                $controller->destroy($id);
                return;
            }
            break;
    }
}

Response::error('Ruta no encontrada.', 404);
