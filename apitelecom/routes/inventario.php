<?php

require_once __DIR__ . '/../controllers/InventarioController.php';
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

$controller = new InventarioController();

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (count($segments) >= 2 && $segments[0] === 'api' && $segments[1] === 'inventario') {
    $id = isset($segments[2]) && is_numeric($segments[2]) ? (int) $segments[2] : null;
    $action = $segments[3] ?? null;

    switch ($method) {
        case 'GET':
            // Support /api/inventario/producto/{producto_id}
            if ($id === null && isset($segments[2]) && $segments[2] === 'producto') {
                $productoId = isset($segments[3]) && is_numeric($segments[3]) ? (int) $segments[3] : null;
                if ($productoId === null) {
                    Response::error('Producto ID inválido o no especificado.', 422);
                    break;
                }
                $controller->porProducto($productoId);
                break;
            }

            if ($id === null) {
                // /api/inventario or special actions
                if (isset($segments[2]) && $segments[2] === 'buscar') {
                    $controller->buscar();
                    break;
                }

                if (isset($segments[2]) && $segments[2] === 'stock-bajo') {
                    $controller->stockBajo();
                    break;
                }

                if (isset($segments[2]) && $segments[2] === 'agotados') {
                    $controller->agotados();
                    break;
                }

                if (isset($segments[2]) && $segments[2] === 'resumen') {
                    $controller->resumen();
                    break;
                }

                $controller->index();
                break;
            }

            if ($action === 'full' || $action === null) {
                // GET /api/inventario/{id}
                $controller->show($id);
                break;
            }

            if ($action === 'producto') {
                // /api/inventario/{id}/producto -> treat id as producto_id
                $controller->porProducto($id);
                break;
            }

            Response::error('Ruta no encontrada.', 404);
            break;

        case 'POST':
            if ($id === null && isset($segments[2]) && $segments[2] === 'entrada') {
                $controller->entrada();
                break;
            }
            if ($id === null && isset($segments[2]) && $segments[2] === 'salida') {
                $controller->salida();
                break;
            }
            if ($id === null && isset($segments[2]) && $segments[2] === 'ajuste') {
                $controller->ajuste();
                break;
            }

            Response::error('Ruta no encontrada.', 404);
            break;

        default:
            Response::error('Método HTTP no soportado.', 405);
            break;
    }
}
