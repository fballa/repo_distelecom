<?php

require_once __DIR__ . '/../models/MovimientoInventario.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class MovimientoInventarioController
{
    public function index(): void
    {
        $filtros = [
            'producto_id' => isset($_GET['producto_id']) ? (int) $_GET['producto_id'] : null,
            'tipo' => isset($_GET['tipo']) ? Validator::sanitizeString($_GET['tipo']) : null,
        ];

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 50;

        $result = MovimientoInventario::obtenerTodos($filtros, $pagina, $limite);
        Response::success('Movimientos de inventario obtenidos correctamente.', $result);
    }

    public function recientes(): void
    {
        $data = MovimientoInventario::obtenerRecientes(10);
        Response::success('Movimientos recientes obtenidos correctamente.', $data);
    }
}
