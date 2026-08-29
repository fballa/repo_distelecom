<?php

require_once __DIR__ . '/../controllers/ChatbotController.php';

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

$controller = new ChatbotController();

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (count($segments) >= 2 && $segments[0] === 'api' && $segments[1] === 'chatbot') {
    $id = isset($segments[2]) && is_numeric($segments[2]) ? (int) $segments[2] : null;
    $action = $segments[3] ?? null;

    if ($method === 'POST' && isset($segments[2]) && $segments[2] === 'iniciar') {
        $controller->iniciar();
        return;
    }

    switch ($method) {
        case 'GET':
            if ($id === null) {
                $controller->index();
                return;
            }
            if ($action === 'mensajes') {
                $controller->mensajes($id);
                return;
            }
            $controller->show($id);
            return;

        case 'POST':
            if ($id === null) {
                $controller->store();
                return;
            }
            if ($action === 'mensajes') {
                $controller->enviarMensaje($id);
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
            if ($id === null && isset($segments[2]) && $segments[2] === 'mensajes' && isset($segments[3]) && is_numeric($segments[3])) {
                $controller->eliminarMensaje((int) $segments[3]);
                return;
            }
            break;
    }
}

Response::error('Ruta no encontrada.', 404);
