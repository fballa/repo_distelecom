/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.20-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: jnilgqkw_dbdistelecom
-- ------------------------------------------------------
-- Server version	10.6.20-MariaDB-cll-lve

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `carrito_detalle`
--

DROP TABLE IF EXISTS `carrito_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carrito_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `carrito_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_carrito_detalle_producto` (`producto_id`),
  KEY `idx_carrito_detalle` (`carrito_id`),
  CONSTRAINT `fk_carrito_detalle_carrito` FOREIGN KEY (`carrito_id`) REFERENCES `carritos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_carrito_detalle_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrito_detalle`
--

LOCK TABLES `carrito_detalle` WRITE;
/*!40000 ALTER TABLE `carrito_detalle` DISABLE KEYS */;
INSERT INTO `carrito_detalle` VALUES (1,1,1,2,129.00,258.00,'2026-07-14 00:21:45'),(2,1,4,1,260.00,260.00,'2026-07-14 00:21:45'),(3,2,3,1,299.00,299.00,'2026-07-14 00:21:45'),(4,2,8,2,110.00,220.00,'2026-07-14 00:21:45'),(5,3,10,1,105.00,105.00,'2026-07-14 00:21:45');
/*!40000 ALTER TABLE `carrito_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carritos`
--

DROP TABLE IF EXISTS `carritos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carritos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) DEFAULT NULL,
  `token` varchar(120) NOT NULL,
  `estado` enum('Activo','Abandonado','Finalizado') DEFAULT 'Activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_carrito_cliente` (`cliente_id`),
  KEY `idx_carrito_token` (`token`),
  CONSTRAINT `fk_carrito_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carritos`
--

LOCK TABLES `carritos` WRITE;
/*!40000 ALTER TABLE `carritos` DISABLE KEYS */;
INSERT INTO `carritos` VALUES (1,1,'77ca37d8-7ef8-11f1-8bee-52540085eb85','Activo','2026-07-14 00:21:45','2026-07-14 00:21:45'),(2,2,'77ca3e6a-7ef8-11f1-8bee-52540085eb85','Activo','2026-07-14 00:21:45','2026-07-14 00:21:45'),(3,NULL,'77ca3f67-7ef8-11f1-8bee-52540085eb85','Activo','2026-07-14 00:21:45','2026-07-14 00:21:45');
/*!40000 ALTER TABLE `carritos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `slug` varchar(150) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,'CCTV','cctv',NULL,'https://misdemos.x10.mx/videos/distelecom/cam_wifi_cctv.png',NULL,0,'Activo','2026-07-13 23:41:32',NULL),(2,'Infraestructura de Red','infraestructura-red',NULL,'https://misdemos.x10.mx/videos/distelecom/cables_utp.png',NULL,0,'Activo','2026-07-13 23:41:32',NULL),(3,'Control de Acceso','control-acceso',NULL,'https://misdemos.x10.mx/videos/distelecom/terminales_biometricas.png',NULL,0,'Activo','2026-07-13 23:41:32',NULL),(4,'Telefonía IP','telefonia-ip',NULL,'https://misdemos.x10.mx/videos/distelecom/telefono_ip.png',NULL,0,'Activo','2026-07-13 23:41:32',NULL),(5,'POS','punto-de-venta',NULL,'https://misdemos.x10.mx/videos/distelecom/punto_de_venta.png',NULL,0,'Activo','2026-07-13 23:41:32',NULL),(6,'Accesorios','accesorios',NULL,'https://misdemos.x10.mx/videos/distelecom/conectores.png',NULL,0,'Activo','2026-07-13 23:41:32',NULL),(7,'Audio y Sonido Profesional','audio-y-sonido-profesional','Equipos de audio profesional para eventos, estudios de grabación y sonido en vivo. Incluye micrófonos, mezcladores, amplificadores y sistemas de sonido.','https://misdemos.x10.mx/videos/distelecom/categoria-audio.jpg','fa-music',4,'Activo','2026-07-16 05:11:57','2026-07-16 05:11:57');
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chatbot_conversaciones`
--

DROP TABLE IF EXISTS `chatbot_conversaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chatbot_conversaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(100) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `nombre` varchar(120) DEFAULT NULL,
  `estado` enum('Activa','Finalizada') DEFAULT 'Activa',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chat_uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatbot_conversaciones`
--

LOCK TABLES `chatbot_conversaciones` WRITE;
/*!40000 ALTER TABLE `chatbot_conversaciones` DISABLE KEYS */;
INSERT INTO `chatbot_conversaciones` VALUES (1,'205ab072-7ef9-11f1-8bee-52540085eb85','186.77.205.10','Visitante 1','Activa','2026-07-14 00:26:27','2026-07-14 00:26:27'),(2,'205ab26f-7ef9-11f1-8bee-52540085eb85','186.77.205.20','Visitante 2','Activa','2026-07-14 00:26:27','2026-07-14 00:26:27');
/*!40000 ALTER TABLE `chatbot_conversaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chatbot_mensajes`
--

DROP TABLE IF EXISTS `chatbot_mensajes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chatbot_mensajes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversacion_id` int(11) NOT NULL,
  `remitente` enum('Usuario','Bot') DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_chat_conversacion` (`conversacion_id`),
  CONSTRAINT `fk_chat_conversacion` FOREIGN KEY (`conversacion_id`) REFERENCES `chatbot_conversaciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatbot_mensajes`
--

LOCK TABLES `chatbot_mensajes` WRITE;
/*!40000 ALTER TABLE `chatbot_mensajes` DISABLE KEYS */;
INSERT INTO `chatbot_mensajes` VALUES (1,1,'Usuario','¿Dónde están ubicados?','2026-07-14 00:26:28'),(2,1,'Bot','Estamos ubicados en Edificio Delta, módulo 4B, Managua, Nicaragua.','2026-07-14 00:26:28'),(3,2,'Usuario','¿Tienen routers WiFi 7?','2026-07-14 00:26:28'),(4,2,'Bot','Sí, contamos con routers WiFi 6 y WiFi 7 para hogares y empresas.','2026-07-14 00:26:28');
/*!40000 ALTER TABLE `chatbot_mensajes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citas`
--

DROP TABLE IF EXISTS `citas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `citas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` int(11) DEFAULT NULL,
  `cliente_id` char(36) NOT NULL,
  `fecha_cita` datetime DEFAULT NULL,
  `hora_cita` time DEFAULT NULL,
  `tipo_cita` varchar(100) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `recordatorio_24h` tinyint(1) DEFAULT 0,
  `recordatorio_1h` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_citas_fecha` (`fecha_cita`),
  KEY `idx_citas_estado` (`estado`),
  KEY `idx_citas_cliente_id` (`cliente_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citas`
--

LOCK TABLES `citas` WRITE;
/*!40000 ALTER TABLE `citas` DISABLE KEYS */;
INSERT INTO `citas` VALUES (1,NULL,'1','2026-03-25 10:30:00','10:30:00','Limpieza dental','Confirmada','La paciente solicitó anestesia',1,0,'2026-03-19 03:47:33'),(2,NULL,'2','2026-03-20 10:30:00','11:30:00','Limpiezacaries','Confirmada','La paciente solicitó inspeccion dental',1,0,'2026-03-19 03:48:38'),(3,NULL,'3','2026-03-21 00:00:00','09:00:00','consulta','pendiente','consulta para obtener presupuesto',0,0,'2026-03-19 19:32:31'),(4,NULL,'3','2026-04-03 16:00:00','16:00:00','Consulta General','Confirmada','',1,1,'2026-03-21 02:25:31'),(5,NULL,'4','2026-04-03 12:00:00','12:00:00','Revisión general','Confirmada','Nueva cita para revisión general.',1,1,'2026-03-29 22:15:10'),(6,NULL,'6','2026-05-09 11:00:00','11:00:00','Examen visual completo','Confirmada','Cita para examen visual completo.',0,0,'2026-05-08 06:39:47');
/*!40000 ALTER TABLE `citas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `empresa` varchar(150) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `documento` varchar(50) DEFAULT NULL,
  `notas` text NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'5532d443-7ef6-11f1-8bee-52540085eb85','Carlos','Martinez','Constructora Delta','carlos@demo.com','88881111',NULL,'',NULL,'Activo','2026-07-14 00:06:28',NULL,NULL),(2,'5532d798-7ef6-11f1-8bee-52540085eb85','Ana','Lopez','Hotel Managua','ana@demo.com','88882222',NULL,'',NULL,'Activo','2026-07-14 00:06:28',NULL,NULL),(3,'5532d878-7ef6-11f1-8bee-52540085eb85','Luis','Gomez','Seguridad Total','luis@demo.com','88883333',NULL,'',NULL,'Activo','2026-07-14 00:06:28',NULL,NULL),(4,'5532d8c7-7ef6-11f1-8bee-52540085eb85','Maria','Perez','Farmacia Central','maria@demo.com','88884444',NULL,'',NULL,'Activo','2026-07-14 00:06:28',NULL,NULL),(5,'5532d8fb-7ef6-11f1-8bee-52540085eb85','Jorge','Rivas','Almacenes JR','jorge@demo.com','88885555',NULL,'',NULL,'Activo','2026-07-14 00:06:28',NULL,NULL),(6,'675cfe8d-1801-4aea-ba7a-d96a5b2458ac','Franklin','S.',NULL,'frankball4@gmail.com','50558883346',NULL,'',NULL,'Activo','2026-07-15 23:40:42',NULL,NULL),(7,'4d40a425-ca88-422a-90d1-a2a669f9c917','Carlos','Gómez','Comercial SA','carlos.gomez@example.com','3419876543','20311222334','','$2y$10$yqwep3VeJAf8l2RoV1bw/e/y7WzEZM6idaPFPG2Z7rogTE5tgfYUy','Activo','2026-07-15 23:51:30',NULL,NULL);
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracion_empresa`
--

DROP TABLE IF EXISTS `configuracion_empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuracion_empresa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_empresa` varchar(150) DEFAULT NULL,
  `slogan` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `whatsapp` varchar(50) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion_empresa`
--

LOCK TABLES `configuracion_empresa` WRITE;
/*!40000 ALTER TABLE `configuracion_empresa` DISABLE KEYS */;
INSERT INTO `configuracion_empresa` VALUES (1,'MI EMPRESA','Soluciones Integrales en Telecomunicaciones','MANAGUA, EDFICIO DELTA, 20 VRS ESTE.','50558883346','50558883346','MI EMPRESA','MI EMPRESA','','',NULL,'https://misdemos.x10.mx/videos/distelecom/logodistelcom.png','https://misdemos.x10.mx/videos/distelecom/logodistelcom.png','2026-07-14 00:26:28');
/*!40000 ALTER TABLE `configuracion_empresa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `direcciones_cliente`
--

DROP TABLE IF EXISTS `direcciones_cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `direcciones_cliente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `pais` varchar(80) DEFAULT NULL,
  `departamento` varchar(80) DEFAULT NULL,
  `ciudad` varchar(80) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `referencia` text DEFAULT NULL,
  `principal` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_direccion_cliente` (`cliente_id`),
  CONSTRAINT `fk_direccion_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `direcciones_cliente`
--

LOCK TABLES `direcciones_cliente` WRITE;
/*!40000 ALTER TABLE `direcciones_cliente` DISABLE KEYS */;
INSERT INTO `direcciones_cliente` VALUES (1,1,'Nicaragua','Managua','Managua','Carretera a Masaya','Casa Azul',1,'2026-07-14 00:06:28'),(2,2,'Nicaragua','Managua','Managua','Altamira','Frente al parque',1,'2026-07-14 00:06:28'),(3,3,'Nicaragua','León','León','Centro','Costado Oeste',1,'2026-07-14 00:06:28'),(4,4,'Nicaragua','Masaya','Masaya','Mercado viejo','Local 12',1,'2026-07-14 00:06:28'),(5,5,'Nicaragua','Granada','Granada','Calle La Calzada','Frente iglesia',1,'2026-07-14 00:06:28'),(6,6,'Nicaragua','esteli','esteli','carretera panamericana km 142','pago en puerta o contra entrega',0,'2026-07-15 23:40:42'),(7,6,'Nicaragua','esteli','esteki','carret. panamerica km 142','nada',0,'2026-07-15 23:45:17'),(8,7,'Nicaragua','esteli','esteli','Carretera panamericana, casa 123','Cerca del semáforo',1,'2026-07-15 23:53:36'),(9,6,'Nicaragua','esteli','esteli','2c. del parque central','casa color verde',1,'2026-07-16 00:14:15');
/*!40000 ALTER TABLE `direcciones_cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `especificaciones_producto`
--

DROP TABLE IF EXISTS `especificaciones_producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `especificaciones_producto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `resolucion` varchar(100) DEFAULT NULL,
  `tecnologia` varchar(100) DEFAULT NULL,
  `vision_nocturna` varchar(150) DEFAULT NULL,
  `alcance_ir` varchar(100) DEFAULT NULL,
  `audio` varchar(100) DEFAULT NULL,
  `angulo` varchar(100) DEFAULT NULL,
  `proteccion_ip` varchar(50) DEFAULT NULL,
  `proteccion_ik` varchar(50) DEFAULT NULL,
  `material` varchar(120) DEFAULT NULL,
  `wifi` varchar(80) DEFAULT NULL,
  `poe` varchar(80) DEFAULT NULL,
  `puertos` varchar(80) DEFAULT NULL,
  `categoria_cable` varchar(80) DEFAULT NULL,
  `velocidad` varchar(80) DEFAULT NULL,
  `alimentacion` varchar(120) DEFAULT NULL,
  `color` varchar(80) DEFAULT NULL,
  `capacidad` varchar(120) DEFAULT NULL,
  `canales` varchar(120) DEFAULT NULL,
  `compatibilidad` varchar(255) DEFAULT NULL,
  `otros` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_especificacion_producto` (`producto_id`),
  CONSTRAINT `fk_especificacion_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `especificaciones_producto`
--

LOCK TABLES `especificaciones_producto` WRITE;
/*!40000 ALTER TABLE `especificaciones_producto` DISABLE KEYS */;
INSERT INTO `especificaciones_producto` VALUES (1,1,'4MP','IP','ColorVu','30m','Si',NULL,'IP67',NULL,NULL,NULL,'Si',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ONVIF','Microfono integrado','2026-07-14 00:06:28'),(2,2,'4MP','WiFi','Smart Hybrid','20m','Bidireccional',NULL,'IP66',NULL,NULL,'WiFi 6',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Android/iOS','Deteccion IA','2026-07-14 00:06:28'),(3,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'WiFi 7',NULL,'4 LAN',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Doble banda','2026-07-14 00:06:28'),(4,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'PoE','24',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Administrable','2026-07-14 00:06:28'),(5,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Rack abatible 12U','2026-07-14 00:06:28'),(6,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Huella, rostro y tarjeta','2026-07-14 00:06:28'),(7,7,NULL,'IP',NULL,NULL,'Bidireccional',NULL,'IP65',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Compatible smartphone','2026-07-14 00:06:28'),(8,8,NULL,'VoIP',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Asterisk','HD Voice','2026-07-14 00:06:28'),(10,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Cat6',NULL,NULL,NULL,NULL,NULL,NULL,'100% cobre','2026-07-14 00:06:28');
/*!40000 ALTER TABLE `especificaciones_producto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estado_pedidos`
--

DROP TABLE IF EXISTS `estado_pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estado_pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estado_pedidos`
--

LOCK TABLES `estado_pedidos` WRITE;
/*!40000 ALTER TABLE `estado_pedidos` DISABLE KEYS */;
INSERT INTO `estado_pedidos` VALUES (1,'Pendiente','Pedido recibido',NULL,'2026-07-14 00:23:49'),(2,'Pagado','Pago confirmado','#10B981','2026-07-14 00:23:49'),(3,'Preparando','Preparando pedido','#3B82F6','2026-07-14 00:23:49'),(4,'Enviado','Pedido enviado','#8B5CF6','2026-07-14 00:23:49'),(5,'Entregado','Pedido entregado','#22C55E','2026-07-14 00:23:49'),(6,'Cancelado','Pedido cancelado','#EF4444','2026-07-14 00:23:49');
/*!40000 ALTER TABLE `estado_pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historial_pedidos`
--

DROP TABLE IF EXISTS `historial_pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historial_pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `estado_id` int(11) NOT NULL,
  `comentario` varchar(255) DEFAULT NULL,
  `usuario` varchar(120) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_historial_estado` (`estado_id`),
  KEY `idx_historial_pedido` (`pedido_id`),
  CONSTRAINT `fk_historial_estado` FOREIGN KEY (`estado_id`) REFERENCES `estado_pedidos` (`id`),
  CONSTRAINT `fk_historial_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historial_pedidos`
--

LOCK TABLES `historial_pedidos` WRITE;
/*!40000 ALTER TABLE `historial_pedidos` DISABLE KEYS */;
INSERT INTO `historial_pedidos` VALUES (1,1,1,'Pedido recibido','Administrador','2026-07-14 00:23:49'),(2,1,2,'Pago confirmado','Administrador','2026-07-14 00:23:49'),(3,1,3,'Pedido preparado','Administrador','2026-07-14 00:23:49'),(4,1,4,'Pedido enviado','Administrador','2026-07-14 00:23:49'),(5,1,5,'Pedido entregado','Administrador','2026-07-14 00:23:49'),(6,2,1,'Pedido recibido','Administrador','2026-07-14 00:23:49'),(7,2,2,'Pago confirmado','Administrador','2026-07-14 00:23:49'),(8,2,3,'Preparando envío','Administrador','2026-07-14 00:23:49'),(9,2,4,'Enviado por mensajería','Administrador','2026-07-14 00:23:49'),(10,3,1,'Pedido recibido','Administrador','2026-07-14 00:23:49'),(11,3,2,'Pago confirmado','Administrador','2026-07-14 00:23:49'),(12,5,1,'Pedido creado.','Sistema','2026-07-16 00:07:21'),(13,6,1,'Pedido creado.','Sistema','2026-07-16 00:14:15');
/*!40000 ALTER TABLE `historial_pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imagenes_producto`
--

DROP TABLE IF EXISTS `imagenes_producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `imagenes_producto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `orden` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_imagen_producto` (`producto_id`),
  CONSTRAINT `fk_imagen_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imagenes_producto`
--

LOCK TABLES `imagenes_producto` WRITE;
/*!40000 ALTER TABLE `imagenes_producto` DISABLE KEYS */;
INSERT INTO `imagenes_producto` VALUES (1,1,'https://misdemos.x10.mx/videos/distelecom/cama_sencilla_cctv.png',1,'2026-07-14 00:06:28'),(2,2,'https://misdemos.x10.mx/videos/distelecom/cam_wifi_cctv.png',1,'2026-07-14 00:06:28'),(3,3,'https://misdemos.x10.mx/videos/distelecom/routers.png',1,'2026-07-14 00:06:28'),(4,4,'https://misdemos.x10.mx/videos/distelecom/switches.png',1,'2026-07-14 00:06:28'),(5,5,'https://misdemos.x10.mx/videos/distelecom/gabinetes.png',1,'2026-07-14 00:06:28'),(6,6,'https://misdemos.x10.mx/videos/distelecom/terminales_biometricas.png',1,'2026-07-14 00:06:28'),(7,7,'https://misdemos.x10.mx/videos/distelecom/video_porteros.png',1,'2026-07-14 00:06:28'),(8,8,'https://misdemos.x10.mx/videos/distelecom/telefono_ip.png',1,'2026-07-14 00:06:28'),(9,9,'https://misdemos.x10.mx/videos/distelecom/punto_de_venta.png',1,'2026-07-14 00:06:28'),(10,10,'https://misdemos.x10.mx/videos/distelecom/cables_utp.png',1,'2026-07-14 00:06:28');
/*!40000 ALTER TABLE `imagenes_producto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventario`
--

DROP TABLE IF EXISTS `inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `stock_actual` int(11) NOT NULL DEFAULT 0,
  `stock_minimo` int(11) NOT NULL DEFAULT 0,
  `ubicacion` varchar(100) DEFAULT NULL,
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_inventario_producto` (`producto_id`),
  CONSTRAINT `fk_inventario_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventario`
--

LOCK TABLES `inventario` WRITE;
/*!40000 ALTER TABLE `inventario` DISABLE KEYS */;
INSERT INTO `inventario` VALUES (1,1,25,5,'Bodega A-01','2026-07-14 00:21:45'),(2,2,18,5,'Bodega A-02','2026-07-14 00:21:45'),(3,3,10,2,'Bodega B-01','2026-07-14 00:21:45'),(4,4,16,3,'Bodega B-02','2026-07-14 00:21:45'),(5,5,8,2,'Bodega C-01','2026-07-14 00:21:45'),(6,6,12,2,'Bodega C-02','2026-07-14 00:21:45'),(7,7,10,2,'Bodega C-03','2026-07-14 00:21:45'),(8,8,22,5,'Bodega D-01','2026-07-14 00:21:45'),(9,9,26,1,'Bodega D-02','2026-08-20 02:25:12'),(10,10,40,10,'Bodega E-01','2026-07-14 00:21:45');
/*!40000 ALTER TABLE `inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(120) DEFAULT NULL,
  `modulo` varchar(100) DEFAULT NULL,
  `accion` varchar(150) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` VALUES (1,'Administrador','Productos','Crear','Se creó el producto Cámara IP 4MP.','186.77.205.10','2026-07-14 00:26:28'),(2,'Administrador','Pedidos','Actualizar','Pedido DST-2026-000001 marcado como entregado.','186.77.205.10','2026-07-14 00:26:28'),(3,'Administrador','Configuración','Editar','Actualización de datos de la empresa.','186.77.205.10','2026-07-14 00:26:28');
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marcas`
--

DROP TABLE IF EXISTS `marcas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marcas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marcas`
--

LOCK TABLES `marcas` WRITE;
/*!40000 ALTER TABLE `marcas` DISABLE KEYS */;
INSERT INTO `marcas` VALUES (1,'Hikvision','https://misdemos.x10.mx/videos/distelecom/logo_marcas.png','Hikvision','Activo','2026-07-13 23:41:32'),(2,'Dahua edit','https://misdemos.x10.mx/videos/distelecom/logo_marcas.png','Dahua','Activo','2026-07-13 23:41:32'),(3,'TP-Link','https://misdemos.x10.mx/videos/distelecom/logo_marcas.png','TP-Link','Activo','2026-07-13 23:41:32'),(4,'Ubiquiti','https://misdemos.x10.mx/videos/distelecom/logo_marcas.png','Ubiquiti','Activo','2026-07-13 23:41:32'),(5,'Grandstream','https://misdemos.x10.mx/videos/distelecom/logo_marcas.png','Grandstream','Activo','2026-07-13 23:41:32'),(6,'Mikrotik','https://misdemos.x10.mx/videos/distelecom/logo_marcas.png','Mikrotik','Activo','2026-07-13 23:41:32'),(7,'ZKTeco','https://misdemos.x10.mx/videos/distelecom/logo_marcas.png','ZKTeco','Activo','2026-07-13 23:41:32'),(8,'Generico','https://misdemos.x10.mx/videos/distelecom/logo_marcas.png','Generico','Activo','2026-07-13 23:41:32');
/*!40000 ALTER TABLE `marcas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensajes_contacto`
--

DROP TABLE IF EXISTS `mensajes_contacto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mensajes_contacto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `empresa` varchar(150) DEFAULT NULL,
  `correo` varchar(150) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `asunto` varchar(150) DEFAULT NULL,
  `mensaje` text NOT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `respondido` tinyint(1) DEFAULT 0,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_contacto_leido` (`leido`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensajes_contacto`
--

LOCK TABLES `mensajes_contacto` WRITE;
/*!40000 ALTER TABLE `mensajes_contacto` DISABLE KEYS */;
INSERT INTO `mensajes_contacto` VALUES (1,'Carlos Martínez','Constructora Delta','carlos@demo.com','88881111','Cotización','Necesito una cotización para cámaras IP.',0,0,NULL,'2026-07-14 00:26:27'),(2,'Ana López','Hotel Managua','ana@demo.com','88882222','Información','Deseo conocer sus soluciones de fibra óptica.',0,0,NULL,'2026-07-14 00:26:27'),(3,'Luis Gómez','Seguridad Total','luis@demo.com','88883333','Proyecto','Necesito asesoría para un proyecto de CCTV.',0,0,NULL,'2026-07-14 00:26:27');
/*!40000 ALTER TABLE `mensajes_contacto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_inventario`
--

DROP TABLE IF EXISTS `movimientos_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movimientos_inventario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `tipo` enum('Entrada','Salida','Ajuste') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `usuario` varchar(120) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_movimiento_producto` (`producto_id`),
  CONSTRAINT `fk_movimiento_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_inventario`
--

LOCK TABLES `movimientos_inventario` WRITE;
/*!40000 ALTER TABLE `movimientos_inventario` DISABLE KEYS */;
INSERT INTO `movimientos_inventario` VALUES (1,1,'Entrada',25,'Carga inicial','Administrador','2026-07-14 00:21:45'),(2,2,'Entrada',18,'Carga inicial','Administrador','2026-07-14 00:21:45'),(3,3,'Entrada',10,'Carga inicial','Administrador','2026-07-14 00:21:45'),(4,4,'Entrada',16,'Carga inicial','Administrador','2026-07-14 00:21:45'),(5,5,'Entrada',8,'Carga inicial','Administrador','2026-07-14 00:21:45'),(6,6,'Entrada',12,'Carga inicial','Administrador','2026-07-14 00:21:45'),(7,7,'Entrada',10,'Carga inicial','Administrador','2026-07-14 00:21:45'),(8,8,'Entrada',22,'Carga inicial','Administrador','2026-07-14 00:21:45'),(9,9,'Entrada',6,'Carga inicial','Administrador','2026-07-14 00:21:45'),(10,10,'Entrada',40,'Carga inicial','Administrador','2026-07-14 00:21:45'),(11,9,'Entrada',20,'compra','Administrador','2026-08-20 02:25:12');
/*!40000 ALTER TABLE `movimientos_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletter`
--

DROP TABLE IF EXISTS `newsletter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsletter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `correo` varchar(150) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletter`
--

LOCK TABLES `newsletter` WRITE;
/*!40000 ALTER TABLE `newsletter` DISABLE KEYS */;
INSERT INTO `newsletter` VALUES (1,'cliente1@demo.com',1,'2026-07-14 00:26:28'),(2,'cliente2@demo.com',1,'2026-07-14 00:26:28'),(3,'cliente3@demo.com',1,'2026-07-14 00:26:28');
/*!40000 ALTER TABLE `newsletter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `novedades`
--

DROP TABLE IF EXISTS `novedades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `novedades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `titulo` varchar(200) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` enum('Publicado','Oculto') DEFAULT 'Publicado',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_novedad_producto` (`producto_id`),
  CONSTRAINT `fk_novedad_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `novedades`
--

LOCK TABLES `novedades` WRITE;
/*!40000 ALTER TABLE `novedades` DISABLE KEYS */;
INSERT INTO `novedades` VALUES (1,3,'Nuevo Router WiFi 7','Mayor velocidad y cobertura para empresas.','https://misdemos.x10.mx/videos/distelecom/routers.png','Publicado','2026-07-14 00:21:45'),(2,6,'Nuevo Control Biométrico','Mayor precisión en reconocimiento facial.','https://misdemos.x10.mx/videos/distelecom/terminales_biometricas.png','Publicado','2026-07-14 00:21:45'),(3,9,'Nuevo Kit POS','Ideal para comercios y supermercados.','https://misdemos.x10.mx/videos/distelecom/punto_de_venta.png','Publicado','2026-07-14 00:21:45'),(4,10,'Cable UTP Cat6 100% Cobre',NULL,NULL,'Publicado','2026-07-16 06:03:08');
/*!40000 ALTER TABLE `novedades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofertas`
--

DROP TABLE IF EXISTS `ofertas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofertas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `titulo` varchar(150) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `porcentaje` decimal(5,2) DEFAULT NULL,
  `precio_oferta` decimal(12,2) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('Activa','Finalizada','Programada') DEFAULT 'Activa',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_oferta_producto` (`producto_id`),
  CONSTRAINT `fk_oferta_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofertas`
--

LOCK TABLES `ofertas` WRITE;
/*!40000 ALTER TABLE `ofertas` DISABLE KEYS */;
INSERT INTO `ofertas` VALUES (1,1,'Oferta CCTV','Descuento especial en cámaras IP.',11.00,129.00,'2026-07-13','2026-08-12','Activa','2026-07-14 00:21:45'),(2,3,'editOferta Router WiFi 7','editPromoción por tiempo limitado.',37.50,200.00,'2026-07-13','2026-08-12','Activa','2026-07-14 00:21:45'),(3,4,'Oferta Switch PoE','Ideal para proyectos empresariales.',9.00,260.00,'2026-07-13','2026-08-12','Activa','2026-07-14 00:21:45'),(4,10,'Oferta especial CCTV','Descuento especial en cámaras IP para seguridad profesional.',-13.03,129.99,'2026-07-20','2026-08-20','Activa','2026-07-16 05:41:56'),(5,6,'fdfdf','dfdfdf',81.67,44.00,'2026-07-18','2026-07-22','Activa','2026-07-16 05:51:55');
/*!40000 ALTER TABLE `ofertas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos`
--

DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `metodo` enum('Transferencia','Efectivo','Tarjeta','PayPal','Otro') NOT NULL,
  `referencia` varchar(120) DEFAULT NULL,
  `monto` decimal(12,2) DEFAULT NULL,
  `estado` enum('Pendiente','Aprobado','Rechazado') DEFAULT 'Pendiente',
  `fecha_pago` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pago_pedido` (`pedido_id`),
  CONSTRAINT `fk_pago_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos`
--

LOCK TABLES `pagos` WRITE;
/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;
INSERT INTO `pagos` VALUES (1,1,'Transferencia','TRX-458712',452.35,'Aprobado','2026-07-13 16:23:49','2026-07-14 00:23:49'),(2,2,'Tarjeta','VISA-985421',578.85,'Aprobado','2026-07-13 16:23:49','2026-07-14 00:23:49'),(3,3,'Efectivo','CAJA-00125',258.00,'Aprobado','2026-07-13 16:23:49','2026-07-14 00:23:49'),(4,5,'Transferencia','TRX98765',1416.00,'Pendiente','2026-07-15 00:00:00','2026-07-16 00:07:21'),(5,6,'Efectivo',NULL,3001.50,'Pendiente','2026-07-15 00:00:00','2026-07-16 00:14:15');
/*!40000 ALTER TABLE `pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedido_detalle`
--

DROP TABLE IF EXISTS `pedido_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pedido_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_detalle_producto` (`producto_id`),
  KEY `idx_detalle_pedido` (`pedido_id`),
  CONSTRAINT `fk_detalle_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedido_detalle`
--

LOCK TABLES `pedido_detalle` WRITE;
/*!40000 ALTER TABLE `pedido_detalle` DISABLE KEYS */;
INSERT INTO `pedido_detalle` VALUES (1,1,1,1,129.00,129.00,'2026-07-14 00:23:49'),(2,1,4,1,260.00,260.00,'2026-07-14 00:23:49'),(3,2,3,1,299.00,299.00,'2026-07-14 00:23:49'),(4,2,8,2,110.00,220.00,'2026-07-14 00:23:49'),(5,3,6,1,220.00,220.00,'2026-07-14 00:23:49'),(6,5,5,1,1200.00,1200.00,'2026-07-16 00:07:21'),(7,5,8,2,110.00,220.00,'2026-07-16 00:07:21'),(8,6,8,1,0.00,0.00,'2026-07-16 00:14:15'),(9,6,9,2,0.00,0.00,'2026-07-16 00:14:15'),(10,6,6,4,0.00,0.00,'2026-07-16 00:14:15'),(11,6,4,2,0.00,0.00,'2026-07-16 00:14:15');
/*!40000 ALTER TABLE `pedido_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(30) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `direccion_id` int(11) DEFAULT NULL,
  `estado_id` int(11) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(12,2) DEFAULT 0.00,
  `impuestos` decimal(12,2) DEFAULT 0.00,
  `envio` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`),
  KEY `fk_pedido_direccion` (`direccion_id`),
  KEY `idx_pedido_numero` (`numero`),
  KEY `idx_pedido_cliente` (`cliente_id`),
  KEY `idx_pedido_estado` (`estado_id`),
  CONSTRAINT `fk_pedido_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pedido_direccion` FOREIGN KEY (`direccion_id`) REFERENCES `direcciones_cliente` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pedido_estado` FOREIGN KEY (`estado_id`) REFERENCES `estado_pedidos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos`
--

LOCK TABLES `pedidos` WRITE;
/*!40000 ALTER TABLE `pedidos` DISABLE KEYS */;
INSERT INTO `pedidos` VALUES (1,'DST-2026-000001',1,1,5,389.00,0.00,58.35,5.00,452.35,'Entregado correctamente.','2026-07-14 00:23:49',NULL),(2,'DST-2026-000002',2,2,4,519.00,20.00,74.85,5.00,578.85,'En tránsito.','2026-07-14 00:23:49',NULL),(3,'DST-2026-000003',3,3,2,220.00,0.00,33.00,5.00,258.00,'Pago confirmado.','2026-07-14 00:23:49',NULL),(4,'DST-2026-000004',6,NULL,1,1870.00,0.00,280.50,0.00,2150.50,NULL,'2026-07-15 23:45:17','2026-07-15 23:45:17'),(5,'DST-2026-000005',7,NULL,1,1200.00,0.00,216.00,0.00,1416.00,NULL,'2026-07-16 00:07:21','2026-07-16 00:07:21'),(6,'DST-2026-000006',6,NULL,1,2610.00,0.00,391.50,0.00,3001.50,NULL,'2026-07-16 00:14:15','2026-07-16 00:14:15');
/*!40000 ALTER TABLE `pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `categoria_id` int(11) NOT NULL,
  `marca_id` int(11) DEFAULT NULL,
  `sku` varchar(80) DEFAULT NULL,
  `codigo_barras` varchar(80) DEFAULT NULL,
  `nombre` varchar(200) NOT NULL,
  `slug` varchar(220) DEFAULT NULL,
  `modelo` varchar(150) DEFAULT NULL,
  `descripcion_corta` text DEFAULT NULL,
  `descripcion_larga` longtext DEFAULT NULL,
  `precio` decimal(12,2) DEFAULT NULL,
  `precio_oferta` decimal(12,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `stock_minimo` int(11) DEFAULT 0,
  `peso` decimal(8,2) DEFAULT NULL,
  `alto` decimal(8,2) DEFAULT NULL,
  `ancho` decimal(8,2) DEFAULT NULL,
  `profundidad` decimal(8,2) DEFAULT NULL,
  `garantia` varchar(100) DEFAULT NULL,
  `imagen_principal` varchar(255) DEFAULT NULL,
  `destacado` tinyint(1) DEFAULT 0,
  `nuevo` tinyint(1) DEFAULT 0,
  `oferta` tinyint(1) DEFAULT 0,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_producto_marca` (`marca_id`),
  KEY `idx_producto_nombre` (`nombre`),
  KEY `idx_producto_categoria` (`categoria_id`),
  KEY `idx_producto_sku` (`sku`),
  KEY `idx_producto_estado` (`estado`),
  CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  CONSTRAINT `fk_producto_marca` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'5537f918-7ef6-11f1-8bee-52540085eb85',1,1,'CCTV001',NULL,'Cedit amara IP 4MP ColorVu','cedit-amara-ip-4mp-colorvu','DS-2CD2047G2','Camara IP 4MP con ColorVu','Camara IP con audio integrado, vision nocturna a color, IP67.',145.00,129.00,25,5,NULL,NULL,NULL,NULL,'12 meses','https://misdemos.x10.mx/videos/distelecom/cama_sencilla_cctv.png',1,1,1,'Activo','Camara IP 4MP','editado Camara IP ColorVu profesional','2026-07-14 00:06:28','2026-07-16 05:00:42',NULL),(2,'5537fd80-7ef6-11f1-8bee-52540085eb85',1,1,'CCTV002',NULL,'Camara WiFi Inteligente','camara-wifi-inteligente','DS-WIFI2026','Camara WiFi con deteccion IA','Deteccion de personas, audio bidireccional y Smart Hybrid Light.',98.00,89.00,20,5,NULL,NULL,NULL,NULL,'12 meses','https://misdemos.x10.mx/videos/distelecom/cam_wifi_cctv.png',1,1,0,'Activo','Camara WiFi','Camara WiFi inteligente','2026-07-14 00:06:28',NULL,NULL),(3,'5537fee4-7ef6-11f1-8bee-52540085eb85',2,3,'NET001',NULL,'Router WiFi 7','router-wifi-7','AX9000','Router empresarial WiFi 7','Doble banda, alta velocidad y cobertura Mesh.',320.00,299.00,12,2,NULL,NULL,NULL,NULL,'24 meses','https://misdemos.x10.mx/videos/distelecom/routers.png',1,1,1,'Activo','Router WiFi 7','Router empresarial','2026-07-14 00:06:28',NULL,NULL),(4,'5537ffa1-7ef6-11f1-8bee-52540085eb85',2,3,'NET002',NULL,'Switch PoE 24 Puertos','switch-poe-24','TL-SG3428MP','Switch administrable PoE','Switch Gigabit administrable con PoE.',285.00,260.00,18,3,NULL,NULL,NULL,NULL,'24 meses','https://misdemos.x10.mx/videos/distelecom/switches.png',1,0,1,'Activo','Switch PoE','Switch administrable','2026-07-14 00:06:28',NULL,NULL),(5,'55380030-7ef6-11f1-8bee-52540085eb85',2,8,'NET003',NULL,'Gabinete Rack 12U','gabinete-rack-12u','GB12U','Gabinete abatible','Gabinete para equipos de telecomunicaciones.',185.00,175.00,8,2,NULL,NULL,NULL,NULL,'12 meses','https://misdemos.x10.mx/videos/distelecom/gabinetes.png',0,1,0,'Activo','Gabinete Rack','Gabinete telecomunicaciones','2026-07-14 00:06:28',NULL,NULL),(6,'55380165-7ef6-11f1-8bee-52540085eb85',3,7,'ACC001',NULL,'Terminal Biometrica','terminal-biometrica','ZK2026','Huella y reconocimiento facial','Control de acceso con huella, rostro y tarjetas.',240.00,220.00,15,3,NULL,NULL,NULL,NULL,'24 meses','https://misdemos.x10.mx/videos/distelecom/terminales_biometricas.png',1,0,0,'Activo','Terminal biometrica','Control de acceso','2026-07-14 00:06:28',NULL,NULL),(7,'55380204-7ef6-11f1-8bee-52540085eb85',3,7,'ACC002',NULL,'Videoportero IP','videoportero-ip','VP2026','Videoportero inteligente','Integracion con smartphones.',210.00,195.00,10,2,NULL,NULL,NULL,NULL,'24 meses','https://misdemos.x10.mx/videos/distelecom/video_porteros.png',0,1,0,'Activo','Videoportero','Videoportero IP','2026-07-14 00:06:28',NULL,NULL),(8,'55380293-7ef6-11f1-8bee-52540085eb85',4,5,'TEL001',NULL,'Telefono IP Empresarial','telefono-ip','GRP2612','Telefono IP HD','Telefono IP para centrales empresariales.',120.00,110.00,25,5,NULL,NULL,NULL,NULL,'24 meses','https://misdemos.x10.mx/videos/distelecom/telefono_ip.png',1,0,1,'Activo','Telefono IP','Telefonia IP','2026-07-14 00:06:28',NULL,NULL),(9,'55380329-7ef6-11f1-8bee-52540085eb85',5,8,'POS001',NULL,'Kit Punto de Venta','kit-pos','POS2026','Solucion POS completa','Monitor, impresora termica y lector.',580.00,550.00,26,1,NULL,NULL,NULL,NULL,'12 meses','https://misdemos.x10.mx/videos/distelecom/punto_de_venta.png',1,1,0,'Activo','Punto de Venta','Hardware POS','2026-07-14 00:06:28','2026-08-20 02:25:12',NULL),(10,'553803b0-7ef6-11f1-8bee-52540085eb85',2,8,'NET004',NULL,'Cable UTP Cat6 100% Cobre','cable-utp-cat6','CAT6','Cable para redes','Cable UTP categoria 6 para exterior e interior.',115.00,105.00,50,10,NULL,NULL,NULL,NULL,'12 meses','https://misdemos.x10.mx/videos/distelecom/cables_utp.png',0,0,0,'Activo','Cable UTP Cat6','Cable UTP 100% cobre','2026-07-14 00:06:28',NULL,NULL),(14,'28d77fef-a24d-4e82-a26b-c5e16a8d2e70',1,1,'CCTV2026-003','7501234567891','editado Camara IP 8MP ColorVu Pro','camara-ip-8mp-colorvu-pro','DS-2CD2087G2','editado Camara IP 8MP con tecnología ColorVu','Camara de seguridad IP con resolución 8MP, sensor de imagen avanzado, tecnología ColorVu para visión nocturna a color, audio bidireccional, resistencia IP67, compatible con ONVIF y PoE.',299.99,249.99,15,3,1.50,10.00,8.00,6.00,'24 meses','https://misdemos.x10.mx/videos/distelecom/terminales_biometricas.png',1,1,1,'Activo','Camara IP 8MP ColorVu Pro - Distelecom','Camara de seguridad IP 8MP con ColorVu y visión nocturna a color. Ideal para vigilancia profesional.','2026-07-16 04:35:58','2026-07-16 05:05:27',NULL);
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews_producto`
--

DROP TABLE IF EXISTS `reviews_producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews_producto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `calificacion` tinyint(4) NOT NULL,
  `comentario` text NOT NULL,
  `estado` enum('Pendiente','Publicado','Oculto') DEFAULT 'Pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_review_producto` (`producto_id`),
  KEY `idx_review_estado` (`estado`),
  CONSTRAINT `fk_review_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews_producto`
--

LOCK TABLES `reviews_producto` WRITE;
/*!40000 ALTER TABLE `reviews_producto` DISABLE KEYS */;
INSERT INTO `reviews_producto` VALUES (1,1,'Carlos Martínez','carlos@demo.com',5,'Excelente calidad de imagen y muy fácil de instalar.','Publicado','2026-07-15 22:01:56'),(2,1,'Ana López','ana@demo.com',4,'Buena cámara, la visión nocturna funciona muy bien.','Publicado','2026-07-15 22:01:56'),(3,2,'Luis Gómez','luis@demo.com',5,'El DVR funciona perfectamente y la configuración fue sencilla.','Publicado','2026-07-15 22:01:56'),(4,3,'María Pérez','maria@demo.com',4,'El router tiene muy buena cobertura WiFi.','Publicado','2026-07-15 22:01:56'),(5,4,'José Hernández','jose@demo.com',5,'Excelente switch PoE para cámaras IP.','Publicado','2026-07-15 22:01:56'),(6,5,'Pedro Ruiz','pedro@demo.com',4,'Buena relación calidad-precio.','Publicado','2026-07-15 22:01:56'),(7,6,'Laura Sánchez','laura@demo.com',5,'La terminal biométrica reconoce el rostro muy rápido.','Publicado','2026-07-15 22:01:56'),(8,7,'Miguel Torres','miguel@demo.com',5,'Muy buena calidad de construcción.','Pendiente','2026-07-15 22:01:56'),(9,8,'Andrea Castillo','andrea@demo.com',3,'Cumple su función, aunque esperaba mayor alcance.','Publicado','2026-07-15 22:01:56'),(10,9,'Sergio Ramírez','sergio@demo.com',5,'Excelente producto. Lo volvería a comprar.','Publicado','2026-07-15 22:01:56'),(11,6,'franklin','frankball4@gmail.com',5,'excelente producto. mejora mucho el control de acceso al edificio.','Pendiente','2026-07-15 22:26:21'),(12,3,'franklin','frankball4@gmail.com',5,'Excelente producto. Muy potente.','Publicado','2026-07-15 22:36:30');
/*!40000 ALTER TABLE `reviews_producto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrador','Acceso total','2026-07-13 23:41:32'),(2,'Ventas','Gestión comercial','2026-07-13 23:41:32'),(3,'Soporte','Soporte técnico','2026-07-13 23:41:32'),(4,'Bodega','Inventario','2026-07-13 23:41:32');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servicios`
--

DROP TABLE IF EXISTS `servicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servicios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL COMMENT 'Nombre del servicio',
  `slug` varchar(150) DEFAULT NULL COMMENT 'Slug para URL amigable',
  `descripcion` text NOT NULL COMMENT 'Descripción completa del servicio (incluye descripción resumida, áreas de aplicación y detalles)',
  `icono` varchar(100) DEFAULT NULL COMMENT 'Clase del icono (FontAwesome, Material, etc.)',
  `imagen` varchar(255) DEFAULT NULL COMMENT 'URL de la imagen representativa',
  `orden` int(11) DEFAULT 0 COMMENT 'Orden de visualización',
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de servicios ofrecidos por Distelecom';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servicios`
--

LOCK TABLES `servicios` WRITE;
/*!40000 ALTER TABLE `servicios` DISABLE KEYS */;
INSERT INTO `servicios` VALUES (13,'Instalación y Configuración update','instalacion-y-configuracion','Despliegue físico y puesta en marcha técnica de cableado estructurado, racks y dispositivos inteligentes (como WiFi 6/7 o cámaras ANPR).\n\nÁreas de Aplicación Principal: Redes de fibra óptica, radioenlaces privados, centrales IP y cámaras de seguridad.\n\nDetalles: Instalación y configuración de cableado estructurado, racks y dispositivos inteligentes para redes y seguridad.','fas fa-wrench','https://misdemos.x10.mx/videos/distelecom/logo_service.png',1,'Activo','2026-07-15 07:25:00','2026-07-16 20:36:54'),(14,'Mantenimiento Correctivo / Preventivo','mantenimiento-correctivo-preventivo',' Acciones técnicas para asegurar la operatividad mediante inspecciones periódicas y reparaciones rápidas de equipos de red y grabación.\r\n\r\nÁreas de Aplicación Principal: Sistemas de videovigilancia 24/7, infraestructuras de red críticas y controles de asistencia.\r\n\r\nDetalles: Mantenimiento preventivo y correctivo de equipos de red, grabadoras y sistemas de videovigilancia.','fa-tools','https://misdemos.x10.mx/videos/distelecom/logo_service.png',2,'Activo','2026-07-15 07:25:00',NULL),(15,'Consultoría y Gestión de Proyectos','consultoria-y-gestion-de-proyectos',' Asesoría especializada para diseñar soluciones escalables, gestionando desde materiales específicos hasta la planificación de redes Mesh.\r\n\r\nÁreas de Aplicación Principal: Industria química y energética, entornos marítimos/costeros y sistemas de vigilancia móvil.\r\n\r\nDetalles: Asesoría y gestión de proyectos de telecomunicaciones, diseño de redes escalables y planificación de infraestructura.','fa-chart-line','https://misdemos.x10.mx/videos/distelecom/logo_service.png',3,'Activo','2026-07-15 07:25:00',NULL),(16,'Soporte Técnico Especializado','soporte-tecnico-especializado',' Acompañamiento técnico enfocado en garantizar la conectividad confiable y la correcta integración de sistemas digitales.\r\n\r\nÁreas de Aplicación Principal: Equipos de punto de venta (POS), plataformas de comunicaciones unificadas y terminales biométricos.\r\n\r\nDetalles: Soporte técnico especializado para garantizar conectividad confiable e integración de sistemas digitales.','fa-headset','https://misdemos.x10.mx/videos/distelecom/logo_service.png',4,'Activo','2026-07-15 07:25:00',NULL),(17,'Automatización IA | Desarrollo de Software | Marketing Digital','auto_ia_software','Especialistas en automatización inteligente con IA, desarrollo de software personalizado y estrategias de marketing digital de alto impacto. Ayudamos a empresas a optimizar procesos, aumentar su eficiencia y acelerar su crecimiento mediante soluciones tecnológicas innovadoras y campañas que convierten.','fa-cog','https://misdemos.x10.mx/videos/distelecom/logo_service.png',1,'Activo','2026-07-16 20:42:18',NULL);
/*!40000 ALTER TABLE `servicios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbcarritotemporal`
--

DROP TABLE IF EXISTS `tbcarritotemporal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbcarritotemporal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `carrito_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`carrito_json`)),
  `created_at` date DEFAULT current_timestamp(),
  `updated_at` date DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbcarritotemporal`
--

LOCK TABLES `tbcarritotemporal` WRITE;
/*!40000 ALTER TABLE `tbcarritotemporal` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbcarritotemporal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rol_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `correo` varchar(150) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `ultimo_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`),
  KEY `fk_usuario_rol` (`rol_id`),
  CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,1,'Administrador','Distelecom','admin@distelecom.com','(505) 5888-3346','$2y$10$CambiarPorHashReal','Activo',NULL,'2026-07-13 23:41:32',NULL,NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_carritos_temporales_items`
--

DROP TABLE IF EXISTS `whatsapp_carritos_temporales_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `whatsapp_carritos_temporales_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `carrito_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(11,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_carritos_temporales_items`
--

LOCK TABLES `whatsapp_carritos_temporales_items` WRITE;
/*!40000 ALTER TABLE `whatsapp_carritos_temporales_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `whatsapp_carritos_temporales_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_conversaciones`
--

DROP TABLE IF EXISTS `whatsapp_conversaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `whatsapp_conversaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `telefono` varchar(30) DEFAULT NULL,
  `nombre` varchar(120) DEFAULT NULL,
  `estado` enum('Abierta','Cerrada') DEFAULT 'Abierta',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_conversaciones`
--

LOCK TABLES `whatsapp_conversaciones` WRITE;
/*!40000 ALTER TABLE `whatsapp_conversaciones` DISABLE KEYS */;
INSERT INTO `whatsapp_conversaciones` VALUES (1,'50588881111','Carlos Martínez','Abierta','2026-07-14 00:26:28','2026-07-14 00:26:28'),(2,'50588882222','Ana López','Abierta','2026-07-14 00:26:28','2026-07-14 00:26:28');
/*!40000 ALTER TABLE `whatsapp_conversaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_mensajes`
--

DROP TABLE IF EXISTS `whatsapp_mensajes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `whatsapp_mensajes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversacion_id` int(11) NOT NULL,
  `remitente` enum('Cliente','Asesor') DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_whatsapp` (`conversacion_id`),
  CONSTRAINT `fk_whatsapp` FOREIGN KEY (`conversacion_id`) REFERENCES `whatsapp_conversaciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_mensajes`
--

LOCK TABLES `whatsapp_mensajes` WRITE;
/*!40000 ALTER TABLE `whatsapp_mensajes` DISABLE KEYS */;
INSERT INTO `whatsapp_mensajes` VALUES (1,1,'Cliente','Hola, necesito una cotización.','2026-07-14 00:26:28'),(2,1,'Asesor','Con gusto. ¿Qué equipos necesita?','2026-07-14 00:26:28'),(3,2,'Cliente','¿Tienen cámaras ColorVu?','2026-07-14 00:26:28'),(4,2,'Asesor','Sí, disponemos de varios modelos de 2MP hasta 8MP.','2026-07-14 00:26:28');
/*!40000 ALTER TABLE `whatsapp_mensajes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'jnilgqkw_dbdistelecom'
--

--
-- Dumping routines for database 'jnilgqkw_dbdistelecom'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-28 14:55:15
