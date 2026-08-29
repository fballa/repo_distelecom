<?php

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) === 0;
    }
}

require_once __DIR__ . '/../controllers/WhatsAppController.php';

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

$controller = new WhatsAppController();

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (count($segments) >= 2 && $segments[0] === 'api' && $segments[1] === 'whatsapp') {
    if (isset($segments[2]) && $segments[2] === 'mensajes' && $method === 'POST') {
        $controller->guardarMensajePorTelefono();
        return;
    }

    $id = isset($segments[2]) && is_numeric($segments[2]) ? (int) $segments[2] : null;
    $action = $segments[3] ?? null;

    switch ($method) {
        case 'GET':
            // /api/whatsapp/carts and /api/whatsapp/cart-items and subroutes
            if (isset($segments[2]) && $segments[2] === 'carts') {
                require_once __DIR__ . '/../controllers/TbCarritoTemporalController.php';
                $cartController = new TbCarritoTemporalController();
                $cartAction = $segments[3] ?? null;

                // /api/whatsapp/carts/by-phone/{phone}
                if ($cartAction === 'by-phone' && isset($segments[4])) {
                    $cartController->byPhone($segments[4]);
                    return;
                }

                $cartId = isset($segments[3]) && is_numeric($segments[3]) ? (int) $segments[3] : null;
                $cartAction = $segments[4] ?? null;

                if ($cartId === null) {
                    $cartController->index();
                    return;
                }

                if ($cartAction === 'items') {
                    // GET /api/whatsapp/carts/{carrito_id}/items
                    require_once __DIR__ . '/../controllers/WhatsappCarritosTemporalesItemsController.php';
                    $itemsController = new WhatsappCarritosTemporalesItemsController();
                    $itemsController->porCarrito($cartId);
                    return;
                }

                if ($cartAction === 'full') {
                    // GET /api/whatsapp/carts/{id}/full
                    $cartController->full($cartId);
                    return;
                }

                $cartController->show($cartId);
                return;
            }

            if (isset($segments[2]) && $segments[2] === 'cart-items') {
                require_once __DIR__ . '/../controllers/WhatsappCarritosTemporalesItemsController.php';
                $itemsController = new WhatsappCarritosTemporalesItemsController();
                $itemId = isset($segments[3]) && is_numeric($segments[3]) ? (int) $segments[3] : null;
                if ($itemId === null) {
                    $itemsController->index();
                    return;
                }
                $itemsController->show($itemId);
                return;
            }

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
            if (isset($segments[2]) && $segments[2] === 'carts') {
                require_once __DIR__ . '/../controllers/TbCarritoTemporalController.php';
                $cartController = new TbCarritoTemporalController();

                if (isset($segments[3]) && is_numeric($segments[3]) && isset($segments[4]) && $segments[4] === 'confirmar-pedido') {
                    $cartController->confirmarPedido((int) $segments[3]);
                    return;
                }

                $cartController->store();
                return;
            }

            if (isset($segments[2]) && $segments[2] === 'cart-items') {
                require_once __DIR__ . '/../controllers/WhatsappCarritosTemporalesItemsController.php';
                $itemsController = new WhatsappCarritosTemporalesItemsController();
                $itemsController->store();
                return;
            }

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
            if (isset($segments[2]) && $segments[2] === 'carts' && isset($segments[3]) && is_numeric($segments[3])) {
                require_once __DIR__ . '/../controllers/TbCarritoTemporalController.php';
                $cartController = new TbCarritoTemporalController();
                $cartController->update((int) $segments[3]);
                return;
            }

            if (isset($segments[2]) && $segments[2] === 'cart-items' && isset($segments[3]) && is_numeric($segments[3])) {
                require_once __DIR__ . '/../controllers/WhatsappCarritosTemporalesItemsController.php';
                $itemsController = new WhatsappCarritosTemporalesItemsController();
                $itemsController->update((int) $segments[3]);
                return;
            }

            if ($id !== null && $action === null) {
                $controller->update($id);
                return;
            }
            break;

        case 'PATCH':
            if (isset($segments[2]) && $segments[2] === 'carts' && isset($segments[3]) && is_numeric($segments[3])) {
                // PATCH /api/whatsapp/carts/{id}/estado or /recordatorio
                $sub = $segments[4] ?? null;
                require_once __DIR__ . '/../controllers/TbCarritoTemporalController.php';
                $cartController = new TbCarritoTemporalController();
                if ($sub === 'estado') {
                    $cartController->cambiarEstado((int) $segments[3]);
                    return;
                }
                if ($sub === 'recordatorio') {
                    // no-op here; items controller handles marking but it's for carts in earlier module
                }
            }

            if (isset($segments[2]) && $segments[2] === 'cart-items' && isset($segments[3]) && is_numeric($segments[3])) {
                // PATCH could be used for partial update; route to update
                require_once __DIR__ . '/../controllers/WhatsappCarritosTemporalesItemsController.php';
                $itemsController = new WhatsappCarritosTemporalesItemsController();
                $itemsController->update((int) $segments[3]);
                return;
            }

            if ($id !== null && $action === 'estado') {
                $controller->cambiarEstado($id);
                return;
            }
            break;

        case 'DELETE':
            if (isset($segments[2]) && $segments[2] === 'carts' && isset($segments[3]) && is_numeric($segments[3])) {
                require_once __DIR__ . '/../controllers/TbCarritoTemporalController.php';
                $cartController = new TbCarritoTemporalController();
                $cartController->destroy((int) $segments[3]);
                return;
            }

            if (isset($segments[2]) && $segments[2] === 'cart-items' && isset($segments[3]) && is_numeric($segments[3])) {
                require_once __DIR__ . '/../controllers/WhatsappCarritosTemporalesItemsController.php';
                $itemsController = new WhatsappCarritosTemporalesItemsController();
                $itemsController->destroy((int) $segments[3]);
                return;
            }

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
