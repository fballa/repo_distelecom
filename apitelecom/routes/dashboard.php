<?php

require_once __DIR__ . '/../controllers/DashboardController.php';
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

$controller = new DashboardController();

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (count($segments) >= 2 && $segments[0] === 'api' && $segments[1] === 'dashboard') {
    if ($method === 'GET') {
        $endpoint = $segments[2] ?? null;

        switch ($endpoint) {
            case null:
                $controller->index();
                return;
            case 'ventas':
                $controller->ventas();
                return;
            case 'cards':
                $controller->cards();
                return;
            case 'pedidos-pendientes':
                $controller->pedidosPendientes();
                return;
            case 'inventario':
                $controller->inventario();
                return;
            case 'productos-mas-vendidos':
                $controller->productosMasVendidos();
                return;
            case 'clientes-nuevos':
                $controller->clientesNuevos();
                return;
            case 'chatbot':
                $controller->chatbot();
                return;
            case 'whatsapp':
                $controller->whatsapp();
                return;
            case 'reviews':
                $controller->reviews();
                return;
            case 'pedidos-recientes':
                $controller->pedidosRecientes();
                return;
            case 'actividad':
                $controller->actividad();
                return;
        }
    }

    Response::error('Ruta no encontrada.', 404);
}
