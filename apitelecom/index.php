<?php

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) === 0;
    }
}

error_reporting(E_ALL & ~E_NOTICE);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

require_once __DIR__ . '/helpers/Response.php';
require_once __DIR__ . '/helpers/Validator.php';
require_once __DIR__ . '/helpers/UploadHelper.php';
require_once __DIR__ . '/config/Database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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

if (count($segments) < 2 || $segments[0] !== 'api') {
    Response::error('Ruta no encontrada.', 404);
}

$modulo = $segments[1];

$routeMap = [
    'productos'       => __DIR__ . '/routes/productos.php',
    'categorias'      => __DIR__ . '/routes/categorias.php',
    'ofertas'         => __DIR__ . '/routes/ofertas.php',
    'novedades'       => __DIR__ . '/routes/novedades.php',
    'pedidos'         => __DIR__ . '/routes/pedidos.php',
    'estados-pedidos' => __DIR__ . '/routes/estados-pedidos.php',
    'clientes'        => __DIR__ . '/routes/clientes.php',
    'direcciones'     => __DIR__ . '/routes/direcciones.php',
    'configuracion'   => __DIR__ . '/routes/configuracion.php',
    'whatsapp'        => __DIR__ . '/routes/whatsapp.php',
    'chatbot'         => __DIR__ . '/routes/chatbot.php',
    'marcas'          => __DIR__ . '/routes/marcas.php',
    'servicios'       => __DIR__ . '/routes/servicios.php',
    'usuarios'        => __DIR__ . '/routes/usuarios.php',
    'roles'           => __DIR__ . '/routes/roles.php',
    'reviews'         => __DIR__ . '/routes/reviews.php',
    'dashboard'       => __DIR__ . '/routes/dashboard.php',
    'citas'           => __DIR__ . '/routes/citas.php',
    'inventario'      => __DIR__ . '/routes/inventario.php',
    'movimientos-inventario' => __DIR__ . '/routes/movimientosInventario.php',
];

if (!isset($routeMap[$modulo])) {
    Response::error('Ruta no encontrada.', 404);
}

require_once $routeMap[$modulo];