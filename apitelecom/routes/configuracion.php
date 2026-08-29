<?php

require_once __DIR__ . '/../controllers/ConfiguracionController.php';

$uri = $_SERVER['REQUEST_URI'];
$uri = str_replace('/apitelecom', '', $uri);
$uri = str_replace('/index.php', '', $uri);
$uri = strtok($uri, '?');
$uri = rtrim($uri, '/');

$controller = new ConfiguracionController();

if ($uri === '/api/configuracion' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller->obtenerConfiguracion();
    return;
}

if ($uri === '/api/configuracion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->crearConfiguracion();
    return;
}

if ($uri === '/api/configuracion' && $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $controller->actualizarConfiguracion();
    return;
}

if ($uri === '/api/configuracion/logo' && $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $controller->actualizarLogo();
    return;
}

if ($uri === '/api/configuracion/favicon' && $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $controller->actualizarFavicon();
    return;
}

Response::error('Ruta no encontrada.', 404);
