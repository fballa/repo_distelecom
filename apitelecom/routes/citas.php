<?php
require_once __DIR__ . '/../controllers/CitaController.php';

$segments = array_values(array_filter(explode('/', $uri)));
$controller = new CitaController();

if (count($segments) >= 2 && $segments[0] === 'api' && $segments[1] === 'citas') {
    // Endpoint para recordatorios
    if (isset($segments[2]) && $segments[2] === 'recordatorios' && $method === 'GET') {
        $controller->recordatorios();
        return;
    }

    // Endpoint para citas por cliente
    if (isset($segments[2]) && $segments[2] === 'cliente' && isset($segments[3]) && $method === 'GET') {
        $controller->porCliente($segments[3]);
        return;
    }

    $id = isset($segments[2]) && is_numeric($segments[2]) ? (int) $segments[2] : null;
    $action = $segments[3] ?? null;

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
            if ($id !== null && $action === 'recordatorio') {
                $controller->marcarRecordatorio($id);
                return;
            }
            break;
        case 'DELETE':
            if ($id !== null && $action === null) {
                $controller->destroy($id);
                return;
            }
            break;
    }
}
Response::error('Ruta no encontrada.', 404);
