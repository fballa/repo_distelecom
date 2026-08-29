<?php

require_once __DIR__ . '/../controllers/PedidoController.php';
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

$controller = new PedidoController();

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (count($segments) >= 2 && $segments[0] === 'api' && $segments[1] === 'pedidos') {
    if (isset($segments[2]) && $segments[2] === 'cliente' && isset($segments[3]) && is_numeric($segments[3])) {
        if ($method === 'GET') {
            $controller->obtenerPorCliente((int) $segments[3]);
            return;
        }

        Response::error('Método no permitido para pedidos por cliente.', 405);
        return;
    }

    if (isset($segments[2]) && $segments[2] === 'numero' && isset($segments[3]) && $method === 'GET') {
        $controller->showByNumero($segments[3]);
        return;
    }

    if (isset($segments[2]) && $segments[2] === 'from-temporary-cart' && $method === 'POST') {
        $controller->fromTemporaryCart();
        return;
    }

    $id = isset($segments[2]) && is_numeric($segments[2]) ? (int) $segments[2] : null;
    $action = $segments[3] ?? null;

    switch ($method) {
        case 'GET':
            if ($id === null) {
                $controller->index();
                break;
            }

            if ($action === 'historial') {
                $controller->historial($id);
                break;
            }

            if ($action === 'pagos') {
                $controller->pagos($id);
                break;
            }

            $controller->show($id);
            break;

        case 'POST':
            if ($id === null) {
                $controller->store();
                break;
            }

            if ($action === 'pagos') {
                $controller->registrarPago($id);
                break;
            }

            Response::error('Ruta no encontrada.', 404);
            break;

        case 'PUT':
        case 'PATCH':
            if ($id !== null && $action === null) {
                $controller->update($id);
                break;
            }

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
