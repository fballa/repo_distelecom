<?php

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../models/Dashboard.php';

class DashboardController
{
    public function index(): void
    {
        $fechaDesde = Validator::sanitizeString($_GET['fecha_desde'] ?? null);
        $fechaHasta = Validator::sanitizeString($_GET['fecha_hasta'] ?? null);
        $result = Dashboard::obtenerResumen($fechaDesde, $fechaHasta);
        Response::success('Resumen del dashboard obtenido correctamente.', $result);
    }

    public function ventas(): void
    {
        $fechaDesde = Validator::sanitizeString($_GET['fecha_desde'] ?? null);
        $fechaHasta = Validator::sanitizeString($_GET['fecha_hasta'] ?? null);
        $result = Dashboard::obtenerVentas($fechaDesde, $fechaHasta);
        Response::success('Ventas del dashboard obtenidas correctamente.', $result);
    }

    public function cards(): void
    {
        $result = Dashboard::obtenerCards();
        Response::success('Cards del dashboard obtenidas correctamente.', $result);
    }

    public function pedidosPendientes(): void
    {
        $result = Dashboard::obtenerPedidosPendientes();
        Response::success('Pedidos pendientes obtenidos correctamente.', $result);
    }

    public function inventario(): void
    {
        $result = Dashboard::obtenerInventario();
        Response::success('Inventario del dashboard obtenido correctamente.', $result);
    }

    public function productosMasVendidos(): void
    {
        $result = Dashboard::obtenerProductosMasVendidos();
        Response::success('Productos más vendidos obtenidos correctamente.', $result);
    }

    public function clientesNuevos(): void
    {
        $fechaDesde = Validator::sanitizeString($_GET['fecha_desde'] ?? null);
        $fechaHasta = Validator::sanitizeString($_GET['fecha_hasta'] ?? null);
        $result = Dashboard::obtenerClientesNuevos($fechaDesde, $fechaHasta, 10);
        Response::success('Clientes nuevos obtenidos correctamente.', $result);
    }

    public function chatbot(): void
    {
        $result = Dashboard::obtenerChatbot();
        Response::success('Resumen de chatbot obtenido correctamente.', $result);
    }

    public function whatsapp(): void
    {
        $result = Dashboard::obtenerWhatsApp();
        Response::success('Resumen de WhatsApp obtenido correctamente.', $result);
    }

    public function reviews(): void
    {
        $result = Dashboard::obtenerReviews();
        Response::success('Resumen de reseñas obtenido correctamente.', $result);
    }

    public function pedidosRecientes(): void
    {
        $result = Dashboard::obtenerPedidosRecientes();
        Response::success('Pedidos recientes obtenidos correctamente.', $result);
    }

    public function actividad(): void
    {
        $result = Dashboard::obtenerActividad();
        Response::success('Actividad del dashboard obtenida correctamente.', $result);
    }
}
