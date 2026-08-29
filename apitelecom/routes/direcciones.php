<?php

require_once __DIR__ . '/../controllers/DireccionClienteController.php';

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

$controller = new DireccionClienteController();

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (count($segments) >= 2 && $segments[0] === 'api') {
    if ($segments[1] === 'clientes' && isset($segments[2]) && is_numeric($segments[2]) && isset($segments[3]) && $segments[3] === 'direcciones') {
        $clienteId = (int) $segments[2];
        switch ($method) {
            case 'GET':
                $controller->index($clienteId);
                break;
            case 'POST':
                $controller->store($clienteId);
                break;
            default:
                Response::error('Método HTTP no soportado.', 405);
                break;
        }
        return;
    }

    if ($segments[1] === 'direcciones' && isset($segments[2]) && is_numeric($segments[2])) {
        $id = (int) $segments[2];
        $action = $segments[3] ?? null;

        switch ($method) {
            case 'GET':
                $controller->show($id);
                return;
            case 'PUT':
                $controller->update($id);
                return;
            case 'DELETE':
                $controller->destroy($id);
                return;
            case 'PATCH':
                if ($action === 'principal') {
                    $controller->marcarPrincipal($id);
                    return;
                }
                Response::error('Ruta no encontrada.', 404);
                return;
            default:
                Response::error('Método HTTP no soportado.', 405);
                return;
        }
    }

    Response::error('Ruta no encontrada.', 404);
}
