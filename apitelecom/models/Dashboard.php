<?php

require_once __DIR__ . '/../config/Database.php';

class Dashboard
{
    public static function obtenerResumen(?string $fechaDesde = null, ?string $fechaHasta = null): array
    {
        return [
            'ventas' => self::obtenerVentas($fechaDesde, $fechaHasta),
            'cards' => self::obtenerCards(),
            'pedidos_pendientes' => self::obtenerPedidosPendientes(),
            'inventario' => self::obtenerInventario(),
            'productos_mas_vendidos' => self::obtenerProductosMasVendidos(10),
            'clientes_nuevos' => self::obtenerClientesNuevos($fechaDesde, $fechaHasta, 10),
            'chatbot' => self::obtenerChatbot(),
            'whatsapp' => self::obtenerWhatsApp(),
            'reviews' => self::obtenerReviews(),
            'pedidos_recientes' => self::obtenerPedidosRecientes(10),
            'actividad' => self::obtenerActividad(15),
        ];
    }

    public static function obtenerVentas(?string $fechaDesde = null, ?string $fechaHasta = null): array
    {
        try {
            $db = Database::getConnection();
            $where = ['1 = 1'];
            $params = [];

            if ($fechaDesde !== null) {
                $where[] = 'p.created_at >= :fecha_desde';
                $params[':fecha_desde'] = $fechaDesde;
            }

            if ($fechaHasta !== null) {
                $where[] = 'p.created_at <= :fecha_hasta';
                $params[':fecha_hasta'] = $fechaHasta;
            }

            $whereSql = implode(' AND ', $where);
            $stmt = $db->prepare("SELECT COUNT(*) AS pedidos, COALESCE(SUM(p.subtotal), 0) AS subtotal, COALESCE(SUM(p.impuestos), 0) AS impuestos, COALESCE(SUM(p.total), 0) AS total, SUM(CASE WHEN ep.nombre = 'Pendiente' THEN 1 ELSE 0 END) AS pedidos_pendientes FROM pedidos p LEFT JOIN estado_pedidos ep ON p.estado_id = ep.id WHERE $whereSql");
            $stmt->execute($params);
            $result = $stmt->fetch();

            return [
                'pedidos' => (int) $result['pedidos'],
                'subtotal' => (float) $result['subtotal'],
                'impuestos' => (float) $result['impuestos'],
                'total' => (float) $result['total'],
                'pendientes' => (int) $result['pedidos_pendientes'],
            ];
        } catch (PDOException $e) {
            error_log('Dashboard::obtenerVentas error: ' . $e->getMessage());
            return ['pedidos' => 0, 'subtotal' => 0.0, 'impuestos' => 0.0, 'total' => 0.0, 'pendientes' => 0];
        }
    }

    public static function obtenerCards(): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->query('SELECT
                (SELECT COUNT(*) FROM pedidos) AS pedidos_totales,
                COALESCE((SELECT SUM(total) FROM pedidos), 0) AS ventas_totales,
                (SELECT COUNT(*) FROM clientes WHERE deleted_at IS NULL) AS clientes_activos,
                (SELECT COUNT(*) FROM productos WHERE deleted_at IS NULL) AS productos_disponibles,
                (SELECT COUNT(*) FROM chatbot_conversaciones) AS chatbot_conversaciones,
                (SELECT COUNT(*) FROM whatsapp_conversaciones) AS whatsapp_conversaciones,
                (SELECT COUNT(*) FROM reviews_producto WHERE estado = "Pendiente") AS reseñas_pendientes
            ');

            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Dashboard::obtenerCards error: ' . $e->getMessage());
            return [
                'pedidos_totales' => 0,
                'ventas_totales' => 0.0,
                'clientes_activos' => 0,
                'productos_disponibles' => 0,
                'chatbot_conversaciones' => 0,
                'whatsapp_conversaciones' => 0,
                'reseñas_pendientes' => 0,
            ];
        }
    }

    public static function obtenerPedidosPendientes(int $limite = 20): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT p.id, p.numero, p.subtotal, p.impuestos, p.total, ep.nombre AS estado, p.created_at, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido FROM pedidos p LEFT JOIN estado_pedidos ep ON p.estado_id = ep.id LEFT JOIN clientes c ON p.cliente_id = c.id WHERE ep.nombre = :estado ORDER BY p.created_at DESC LIMIT :limite');
            $stmt->bindValue(':estado', 'Pendiente', PDO::PARAM_STR);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Dashboard::obtenerPedidosPendientes error: ' . $e->getMessage());
            return [];
        }
    }

    public static function obtenerInventario(int $limite = 20): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT p.id AS producto_id, p.nombre AS producto_nombre, p.sku, i.stock_actual AS stock, i.stock_minimo, p.imagen_principal FROM inventario i JOIN productos p ON i.producto_id = p.id WHERE p.deleted_at IS NULL AND i.stock_actual <= i.stock_minimo ORDER BY i.stock_actual ASC, p.nombre ASC LIMIT :limite');
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Dashboard::obtenerInventario error: ' . $e->getMessage());
            return [];
        }
    }

    public static function obtenerProductosMasVendidos(int $limite = 10): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT pd.producto_id, p.nombre AS producto_nombre, p.imagen_principal, SUM(pd.cantidad) AS cantidad_vendida, SUM(pd.subtotal) AS total_vendido FROM pedido_detalle pd JOIN productos p ON pd.producto_id = p.id GROUP BY pd.producto_id ORDER BY cantidad_vendida DESC LIMIT :limite');
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Dashboard::obtenerProductosMasVendidos error: ' . $e->getMessage());
            return [];
        }
    }

    public static function obtenerClientesNuevos(?string $fechaDesde = null, ?string $fechaHasta = null, int $limite = 10): array
    {
        try {
            $db = Database::getConnection();
            $where = ['deleted_at IS NULL'];
            $params = [];

            if ($fechaDesde !== null) {
                $where[] = 'created_at >= :fecha_desde';
                $params[':fecha_desde'] = $fechaDesde;
            }

            if ($fechaHasta !== null) {
                $where[] = 'created_at <= :fecha_hasta';
                $params[':fecha_hasta'] = $fechaHasta;
            }

            if ($fechaDesde === null && $fechaHasta === null) {
                $where[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
            }

            $whereSql = implode(' AND ', $where);
            $sql = "SELECT id, nombre, apellido, empresa, correo, telefono, documento, created_at FROM clientes WHERE $whereSql ORDER BY created_at DESC LIMIT :limite";
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Dashboard::obtenerClientesNuevos error: ' . $e->getMessage());
            return [];
        }
    }

    public static function obtenerChatbot(int $limite = 10): array
    {
        try {
            $db = Database::getConnection();
            $summaryStmt = $db->query('SELECT COUNT(*) AS total, SUM(CASE WHEN estado = "Activa" THEN 1 ELSE 0 END) AS activas, SUM(CASE WHEN estado = "Finalizada" THEN 1 ELSE 0 END) AS finalizadas FROM chatbot_conversaciones');
            $summary = $summaryStmt->fetch();

            $listStmt = $db->prepare('SELECT id, uuid, ip, nombre, estado, created_at, (SELECT COUNT(*) FROM chatbot_mensajes WHERE conversacion_id = chatbot_conversaciones.id) AS mensajes_count FROM chatbot_conversaciones ORDER BY created_at DESC LIMIT :limite');
            $listStmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $listStmt->execute();

            return [
                'summary' => $summary,
                'recientes' => $listStmt->fetchAll(),
            ];
        } catch (PDOException $e) {
            error_log('Dashboard::obtenerChatbot error: ' . $e->getMessage());
            return ['summary' => ['total' => 0, 'activas' => 0, 'finalizadas' => 0], 'recientes' => []];
        }
    }

    public static function obtenerWhatsApp(int $limite = 10): array
    {
        try {
            $db = Database::getConnection();
            $summaryStmt = $db->query('SELECT COUNT(*) AS total, SUM(CASE WHEN estado = "Abierta" THEN 1 ELSE 0 END) AS abiertas, SUM(CASE WHEN estado = "Cerrada" THEN 1 ELSE 0 END) AS cerradas FROM whatsapp_conversaciones');
            $summary = $summaryStmt->fetch();

            $listStmt = $db->prepare('SELECT id, telefono, nombre, estado, created_at, (SELECT COUNT(*) FROM whatsapp_mensajes WHERE conversacion_id = whatsapp_conversaciones.id) AS mensajes_count FROM whatsapp_conversaciones ORDER BY created_at DESC LIMIT :limite');
            $listStmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $listStmt->execute();

            return [
                'summary' => $summary,
                'recientes' => $listStmt->fetchAll(),
            ];
        } catch (PDOException $e) {
            error_log('Dashboard::obtenerWhatsApp error: ' . $e->getMessage());
            return ['summary' => ['total' => 0, 'abiertas' => 0, 'cerradas' => 0], 'recientes' => []];
        }
    }

    public static function obtenerReviews(int $limite = 10): array
    {
        try {
            $db = Database::getConnection();
            $summaryStmt = $db->query('SELECT COUNT(*) AS total, SUM(CASE WHEN estado = "Pendiente" THEN 1 ELSE 0 END) AS pendientes, COALESCE(AVG(calificacion), 0) AS promedio FROM reviews_producto');
            $summary = $summaryStmt->fetch();

            $listStmt = $db->prepare('SELECT id, producto_id, nombre, correo, calificacion, comentario, estado, created_at FROM reviews_producto ORDER BY created_at DESC LIMIT :limite');
            $listStmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $listStmt->execute();

            return [
                'summary' => $summary,
                'recientes' => $listStmt->fetchAll(),
            ];
        } catch (PDOException $e) {
            error_log('Dashboard::obtenerReviews error: ' . $e->getMessage());
            return ['summary' => ['total' => 0, 'pendientes' => 0, 'promedio' => 0.0], 'recientes' => []];
        }
    }

    public static function obtenerPedidosRecientes(int $limite = 10): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT p.id, p.numero, p.subtotal, p.impuestos, p.total, ep.nombre AS estado, p.created_at, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido FROM pedidos p LEFT JOIN estado_pedidos ep ON p.estado_id = ep.id LEFT JOIN clientes c ON p.cliente_id = c.id ORDER BY p.created_at DESC LIMIT :limite');
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Dashboard::obtenerPedidosRecientes error: ' . $e->getMessage());
            return [];
        }
    }

    public static function obtenerActividad(int $limite = 15): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT hp.id, hp.pedido_id, COALESCE(p.numero, "") AS pedido_numero, ep.nombre AS estado, hp.comentario, hp.usuario, hp.created_at FROM historial_pedidos hp LEFT JOIN pedidos p ON hp.pedido_id = p.id LEFT JOIN estado_pedidos ep ON hp.estado_id = ep.id ORDER BY hp.created_at DESC LIMIT :limite');
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Dashboard::obtenerActividad error: ' . $e->getMessage());
            return [];
        }
    }
}
