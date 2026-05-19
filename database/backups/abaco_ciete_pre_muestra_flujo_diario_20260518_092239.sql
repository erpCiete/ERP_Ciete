-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: abaco_ciete
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_log` (
  `id_audit` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned DEFAULT NULL,
  `id_usuario` bigint(20) unsigned DEFAULT NULL,
  `accion` enum('crear','editar','actualizar','eliminar','cerrar','reabrir','activar','desactivar','cambiar_estado','importar','exportar','limpiar_logs','cambiar_contexto') NOT NULL,
  `tabla` varchar(80) NOT NULL,
  `modulo` varchar(80) DEFAULT NULL,
  `entity_type` varchar(120) DEFAULT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  `registro_id` bigint(20) unsigned DEFAULT NULL,
  `campo` varchar(120) DEFAULT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_nuevo` text DEFAULT NULL,
  `datos_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_anteriores`)),
  `datos_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_nuevos`)),
  `descripcion` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_audit`),
  KEY `idx_audit_tabla_registro` (`tabla`,`registro_id`),
  KEY `idx_audit_usuario` (`id_usuario`),
  KEY `idx_audit_contexto` (`id_contexto`),
  KEY `idx_audit_accion` (`accion`),
  KEY `idx_audit_fecha` (`created_at`),
  KEY `idx_audit_modulo` (`modulo`),
  KEY `idx_audit_entity` (`entity_type`,`entity_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` VALUES (1,1,6,'cambiar_contexto','usuarios','contexto','App\\Models\\User',6,6,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-17 20:49:48');
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cobros`
--

DROP TABLE IF EXISTS `cobros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cobros` (
  `id_cobro` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_factura` bigint(20) unsigned NOT NULL,
  `id_usuario_registro` bigint(20) unsigned DEFAULT NULL,
  `fecha_cobro` date NOT NULL,
  `importe` decimal(14,2) NOT NULL,
  `metodo_cobro` enum('transferencia','giro','efectivo','confirming','otro') NOT NULL DEFAULT 'transferencia',
  `referencia` varchar(120) DEFAULT NULL,
  `estado` enum('pendiente','recibido','conciliado','devuelto') NOT NULL DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_cobro`),
  KEY `idx_cobros_contexto` (`id_contexto`),
  KEY `idx_cobros_factura_contexto` (`id_factura`,`id_contexto`),
  KEY `fk_cobros_usuario_registro` (`id_usuario_registro`),
  CONSTRAINT `fk_cobros_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cobros_factura_contexto` FOREIGN KEY (`id_factura`, `id_contexto`) REFERENCES `facturas` (`id_factura`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cobros_usuario_registro` FOREIGN KEY (`id_usuario_registro`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cobros`
--

LOCK TABLES `cobros` WRITE;
/*!40000 ALTER TABLE `cobros` DISABLE KEYS */;
/*!40000 ALTER TABLE `cobros` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comentarios_legalizaciones`
--

DROP TABLE IF EXISTS `comentarios_legalizaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comentarios_legalizaciones` (
  `id_comentario_legalizacion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_legalizacion` bigint(20) unsigned NOT NULL,
  `id_usuario` bigint(20) unsigned DEFAULT NULL,
  `fecha_comentario` datetime NOT NULL DEFAULT current_timestamp(),
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_comentario_legalizacion`),
  KEY `idx_comentarios_legalizaciones_contexto` (`id_contexto`),
  KEY `idx_comentarios_legalizaciones_legalizacion_contexto` (`id_legalizacion`,`id_contexto`),
  KEY `idx_comentarios_legalizaciones_usuario_contexto` (`id_usuario`,`id_contexto`),
  CONSTRAINT `fk_comentarios_legalizaciones_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_comentarios_legalizaciones_legalizacion_contexto` FOREIGN KEY (`id_legalizacion`, `id_contexto`) REFERENCES `legalizaciones` (`id_legalizacion`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comentarios_legalizaciones_usuario_contexto` FOREIGN KEY (`id_usuario`, `id_contexto`) REFERENCES `usuarios` (`id_usuario`, `id_contexto`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comentarios_legalizaciones`
--

LOCK TABLES `comentarios_legalizaciones` WRITE;
/*!40000 ALTER TABLE `comentarios_legalizaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `comentarios_legalizaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comentarios_soporte`
--

DROP TABLE IF EXISTS `comentarios_soporte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comentarios_soporte` (
  `id_comentario_soporte` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_solicitud_soporte` bigint(20) unsigned NOT NULL,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `tipo_autor` varchar(20) NOT NULL DEFAULT 'user',
  `mensaje` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_comentario_soporte`),
  KEY `idx_comentarios_soporte_ticket` (`id_solicitud_soporte`),
  KEY `idx_comentarios_soporte_usuario` (`id_usuario`),
  KEY `idx_comentarios_soporte_fecha` (`created_at`),
  CONSTRAINT `fk_comentarios_soporte_ticket` FOREIGN KEY (`id_solicitud_soporte`) REFERENCES `solicitudes_soporte` (`id_solicitud_soporte`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comentarios_soporte_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comentarios_soporte`
--

LOCK TABLES `comentarios_soporte` WRITE;
/*!40000 ALTER TABLE `comentarios_soporte` DISABLE KEYS */;
/*!40000 ALTER TABLE `comentarios_soporte` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contactos`
--

DROP TABLE IF EXISTS `contactos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contactos` (
  `id_contacto` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(150) DEFAULT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `cargo_general` varchar(150) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_contacto`),
  UNIQUE KEY `uq_contactos_dni` (`dni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactos`
--

LOCK TABLES `contactos` WRITE;
/*!40000 ALTER TABLE `contactos` DISABLE KEYS */;
/*!40000 ALTER TABLE `contactos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contactos_empresas`
--

DROP TABLE IF EXISTS `contactos_empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contactos_empresas` (
  `id_contacto_empresa` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_empresa` bigint(20) unsigned NOT NULL,
  `id_contacto` bigint(20) unsigned NOT NULL,
  `puesto` varchar(150) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `es_responsable_principal` tinyint(1) NOT NULL DEFAULT 0,
  `recibe_avisos` tinyint(1) NOT NULL DEFAULT 0,
  `recibe_presupuestos` tinyint(1) NOT NULL DEFAULT 0,
  `recibe_facturas` tinyint(1) NOT NULL DEFAULT 0,
  `es_usuario` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_contacto_empresa`),
  UNIQUE KEY `uq_contactos_empresas_contexto_empresa_contacto` (`id_contexto`,`id_empresa`,`id_contacto`),
  KEY `idx_contactos_empresas_contexto` (`id_contexto`),
  KEY `idx_contactos_empresas_contacto` (`id_contacto`),
  KEY `idx_contactos_empresas_id_contexto` (`id_contacto_empresa`,`id_contexto`),
  KEY `idx_contactos_empresas_empresa_contexto` (`id_empresa`,`id_contexto`),
  CONSTRAINT `fk_contactos_empresas_contacto` FOREIGN KEY (`id_contacto`) REFERENCES `contactos` (`id_contacto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_contactos_empresas_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_contactos_empresas_empresa_contexto` FOREIGN KEY (`id_empresa`, `id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactos_empresas`
--

LOCK TABLES `contactos_empresas` WRITE;
/*!40000 ALTER TABLE `contactos_empresas` DISABLE KEYS */;
/*!40000 ALTER TABLE `contactos_empresas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contextos_cliente`
--

DROP TABLE IF EXISTS `contextos_cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contextos_cliente` (
  `id_contexto` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_contexto`),
  UNIQUE KEY `uq_contextos_cliente_nombre` (`nombre`),
  UNIQUE KEY `uq_contextos_cliente_codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contextos_cliente`
--

LOCK TABLES `contextos_cliente` WRITE;
/*!40000 ALTER TABLE `contextos_cliente` DISABLE KEYS */;
INSERT INTO `contextos_cliente` VALUES (1,'MOEVE','MOEVE','Contexto operativo MOEVE',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(2,'REPSOL','REPSOL','Contexto operativo REPSOL',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(3,'OTROS CLIENTES','OTROS','Contexto operativo OTROS CLIENTES',1,'2026-05-17 20:28:07','2026-05-17 20:28:07');
/*!40000 ALTER TABLE `contextos_cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contrato_empresas_facturadoras`
--

DROP TABLE IF EXISTS `contrato_empresas_facturadoras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contrato_empresas_facturadoras` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contrato` bigint(20) unsigned NOT NULL,
  `id_empresa` bigint(20) unsigned NOT NULL,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cef_contrato_empresa_contexto` (`id_contrato`,`id_empresa`,`id_contexto`),
  KEY `idx_cef_contrato` (`id_contrato`),
  KEY `idx_cef_empresa_contexto` (`id_empresa`,`id_contexto`),
  KEY `idx_cef_contexto` (`id_contexto`),
  CONSTRAINT `fk_cef_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cef_contrato` FOREIGN KEY (`id_contrato`) REFERENCES `contratos` (`id_contrato`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cef_empresa_contexto` FOREIGN KEY (`id_empresa`, `id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contrato_empresas_facturadoras`
--

LOCK TABLES `contrato_empresas_facturadoras` WRITE;
/*!40000 ALTER TABLE `contrato_empresas_facturadoras` DISABLE KEYS */;
/*!40000 ALTER TABLE `contrato_empresas_facturadoras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contratos`
--

DROP TABLE IF EXISTS `contratos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contratos` (
  `id_contrato` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_empresa_cliente` bigint(20) unsigned NOT NULL,
  `codigo_contrato` varchar(100) NOT NULL,
  `nombre` varchar(180) DEFAULT NULL,
  `tipo` enum('marco','directo','otro') NOT NULL DEFAULT 'marco',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('vigente','expirado','cancelado') NOT NULL DEFAULT 'vigente',
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_contrato`),
  UNIQUE KEY `uq_contratos_ctx_codigo` (`id_contexto`,`codigo_contrato`),
  KEY `idx_contratos_contexto` (`id_contexto`),
  KEY `idx_contratos_id_contexto` (`id_contrato`,`id_contexto`),
  KEY `idx_contratos_empresa_contexto` (`id_empresa_cliente`,`id_contexto`),
  CONSTRAINT `fk_contratos_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_contratos_empresa_contexto` FOREIGN KEY (`id_empresa_cliente`, `id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contratos`
--

LOCK TABLES `contratos` WRITE;
/*!40000 ALTER TABLE `contratos` DISABLE KEYS */;
/*!40000 ALTER TABLE `contratos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `direcciones`
--

DROP TABLE IF EXISTS `direcciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `direcciones` (
  `id_direccion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_empresa` bigint(20) unsigned DEFAULT NULL,
  `id_contacto` bigint(20) unsigned DEFAULT NULL,
  `id_contacto_empresa` bigint(20) unsigned DEFAULT NULL,
  `id_usuario` bigint(20) unsigned DEFAULT NULL,
  `tipo` enum('fiscal','social','principal','obra','facturacion','delegacion','otra') NOT NULL DEFAULT 'principal',
  `linea1` varchar(255) NOT NULL,
  `linea2` varchar(255) DEFAULT NULL,
  `codigo_postal` varchar(20) DEFAULT NULL,
  `localidad` varchar(120) DEFAULT NULL,
  `provincia` varchar(120) DEFAULT NULL,
  `pais` varchar(120) NOT NULL DEFAULT 'Espana',
  `es_principal` tinyint(1) NOT NULL DEFAULT 0,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_direccion`),
  KEY `idx_direcciones_contexto` (`id_contexto`),
  KEY `idx_direcciones_empresa_contexto` (`id_empresa`,`id_contexto`),
  KEY `idx_direcciones_contacto` (`id_contacto`),
  KEY `idx_direcciones_contacto_empresa_contexto` (`id_contacto_empresa`,`id_contexto`),
  KEY `idx_direcciones_usuario_contexto` (`id_usuario`,`id_contexto`),
  CONSTRAINT `fk_direcciones_contacto` FOREIGN KEY (`id_contacto`) REFERENCES `contactos` (`id_contacto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_direcciones_contacto_empresa_contexto` FOREIGN KEY (`id_contacto_empresa`, `id_contexto`) REFERENCES `contactos_empresas` (`id_contacto_empresa`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_direcciones_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_direcciones_empresa_contexto` FOREIGN KEY (`id_empresa`, `id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_direcciones_usuario_contexto` FOREIGN KEY (`id_usuario`, `id_contexto`) REFERENCES `usuarios` (`id_usuario`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `direcciones`
--

LOCK TABLES `direcciones` WRITE;
/*!40000 ALTER TABLE `direcciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `direcciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emails`
--

DROP TABLE IF EXISTS `emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emails` (
  `id_email` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_empresa` bigint(20) unsigned DEFAULT NULL,
  `id_contacto` bigint(20) unsigned DEFAULT NULL,
  `id_contacto_empresa` bigint(20) unsigned DEFAULT NULL,
  `id_usuario` bigint(20) unsigned DEFAULT NULL,
  `email` varchar(180) NOT NULL,
  `tipo` enum('personal','profesional','facturacion','avisos','tecnico','otro') NOT NULL DEFAULT 'profesional',
  `es_principal` tinyint(1) NOT NULL DEFAULT 0,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_email`),
  KEY `idx_emails_contexto` (`id_contexto`),
  KEY `idx_emails_empresa_contexto` (`id_empresa`,`id_contexto`),
  KEY `idx_emails_contacto` (`id_contacto`),
  KEY `idx_emails_contacto_empresa_contexto` (`id_contacto_empresa`,`id_contexto`),
  KEY `idx_emails_usuario_contexto` (`id_usuario`,`id_contexto`),
  CONSTRAINT `fk_emails_contacto` FOREIGN KEY (`id_contacto`) REFERENCES `contactos` (`id_contacto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_emails_contacto_empresa_contexto` FOREIGN KEY (`id_contacto_empresa`, `id_contexto`) REFERENCES `contactos_empresas` (`id_contacto_empresa`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_emails_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_emails_empresa_contexto` FOREIGN KEY (`id_empresa`, `id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_emails_usuario_contexto` FOREIGN KEY (`id_usuario`, `id_contexto`) REFERENCES `usuarios` (`id_usuario`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emails`
--

LOCK TABLES `emails` WRITE;
/*!40000 ALTER TABLE `emails` DISABLE KEYS */;
/*!40000 ALTER TABLE `emails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empresas`
--

DROP TABLE IF EXISTS `empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `empresas` (
  `id_empresa` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `empresa_padre_id` bigint(20) unsigned DEFAULT NULL,
  `nombre` varchar(180) NOT NULL,
  `nombre_comercial` varchar(180) DEFAULT NULL,
  `razon_social` varchar(220) DEFAULT NULL,
  `cif` varchar(20) DEFAULT NULL,
  `tipo_empresa` enum('cliente','proveedor','cliente_proveedor','interna','otra') NOT NULL DEFAULT 'cliente',
  `web` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_empresa`),
  UNIQUE KEY `uq_empresas_contexto_nombre` (`id_contexto`,`nombre`),
  UNIQUE KEY `uq_empresas_contexto_cif` (`id_contexto`,`cif`),
  KEY `idx_empresas_contexto` (`id_contexto`),
  KEY `idx_empresas_id_contexto` (`id_empresa`,`id_contexto`),
  KEY `idx_empresas_padre_contexto` (`empresa_padre_id`,`id_contexto`),
  CONSTRAINT `fk_empresas_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_empresas_padre_contexto` FOREIGN KEY (`empresa_padre_id`, `id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresas`
--

LOCK TABLES `empresas` WRITE;
/*!40000 ALTER TABLE `empresas` DISABLE KEYS */;
/*!40000 ALTER TABLE `empresas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estaciones_moeve_ext`
--

DROP TABLE IF EXISTS `estaciones_moeve_ext`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estaciones_moeve_ext` (
  `id_estacion_servicio` bigint(20) unsigned NOT NULL,
  `n_margenes` varchar(20) DEFAULT NULL,
  `tecnico_gestion` varchar(150) DEFAULT NULL,
  `telefono_tecnico` varchar(30) DEFAULT NULL,
  `email_tecnico` varchar(180) DEFAULT NULL,
  `responsable_gestor` varchar(150) DEFAULT NULL,
  `telefono_gestor` varchar(30) DEFAULT NULL,
  `telefono_oficina` varchar(30) DEFAULT NULL,
  `sede_email` varchar(180) DEFAULT NULL,
  `vinculo_1` varchar(255) DEFAULT NULL,
  `vinculo_2` varchar(255) DEFAULT NULL,
  `f_alta_modificacion` date DEFAULT NULL,
  `cod_retailgas` varchar(80) DEFAULT NULL,
  `cod_sociedad` varchar(80) DEFAULT NULL,
  `sociedad` varchar(180) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_estacion_servicio`),
  CONSTRAINT `fk_estaciones_moeve_ext_estacion` FOREIGN KEY (`id_estacion_servicio`) REFERENCES `estaciones_servicio` (`id_estacion_servicio`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estaciones_moeve_ext`
--

LOCK TABLES `estaciones_moeve_ext` WRITE;
/*!40000 ALTER TABLE `estaciones_moeve_ext` DISABLE KEYS */;
/*!40000 ALTER TABLE `estaciones_moeve_ext` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estaciones_repsol_ext`
--

DROP TABLE IF EXISTS `estaciones_repsol_ext`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estaciones_repsol_ext` (
  `id_estacion_servicio` bigint(20) unsigned NOT NULL,
  `codigo_solred` varchar(80) DEFAULT NULL,
  `litros_21` decimal(14,0) DEFAULT NULL,
  `gnas_95_21` decimal(14,0) DEFAULT NULL,
  `gnas_98_21` decimal(14,0) DEFAULT NULL,
  `gasoleo_a_21` decimal(14,0) DEFAULT NULL,
  `eplus10_21` decimal(14,0) DEFAULT NULL,
  `glp_21` decimal(14,0) DEFAULT NULL,
  `adblue_21` decimal(14,0) DEFAULT NULL,
  `cliente_nombre` varchar(180) DEFAULT NULL,
  `nom_encargado` varchar(150) DEFAULT NULL,
  `nom_gerente` varchar(150) DEFAULT NULL,
  `tfno_instalacion` varchar(30) DEFAULT NULL,
  `fax_instalacion` varchar(30) DEFAULT NULL,
  `tfno_movil_gerente` varchar(30) DEFAULT NULL,
  `tfno_movil_encargado` varchar(30) DEFAULT NULL,
  `margen` char(1) DEFAULT NULL,
  `provincial` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_estacion_servicio`),
  CONSTRAINT `fk_estaciones_repsol_ext_estacion` FOREIGN KEY (`id_estacion_servicio`) REFERENCES `estaciones_servicio` (`id_estacion_servicio`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estaciones_repsol_ext`
--

LOCK TABLES `estaciones_repsol_ext` WRITE;
/*!40000 ALTER TABLE `estaciones_repsol_ext` DISABLE KEYS */;
/*!40000 ALTER TABLE `estaciones_repsol_ext` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estaciones_servicio`
--

DROP TABLE IF EXISTS `estaciones_servicio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estaciones_servicio` (
  `id_estacion_servicio` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_empresa_cliente` bigint(20) unsigned NOT NULL,
  `codigo_estacion` varchar(80) NOT NULL,
  `nombre` varchar(180) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `codigo_postal` varchar(20) DEFAULT NULL,
  `poblacion` varchar(120) DEFAULT NULL,
  `provincia` varchar(120) DEFAULT NULL,
  `pais` varchar(120) NOT NULL DEFAULT 'Espana',
  `latitud_wgs84` decimal(11,8) DEFAULT NULL,
  `longitud_wgs84` decimal(11,8) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `f_baja` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_estacion_servicio`),
  UNIQUE KEY `uq_estaciones_ctx_codigo` (`id_contexto`,`codigo_estacion`),
  KEY `idx_estaciones_contexto` (`id_contexto`),
  KEY `idx_estaciones_id_contexto` (`id_estacion_servicio`,`id_contexto`),
  KEY `idx_estaciones_empresa_contexto` (`id_empresa_cliente`,`id_contexto`),
  CONSTRAINT `fk_estaciones_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_estaciones_empresa_contexto` FOREIGN KEY (`id_empresa_cliente`, `id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estaciones_servicio`
--

LOCK TABLES `estaciones_servicio` WRITE;
/*!40000 ALTER TABLE `estaciones_servicio` DISABLE KEYS */;
/*!40000 ALTER TABLE `estaciones_servicio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `factura_items`
--

DROP TABLE IF EXISTS `factura_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `factura_items` (
  `id_factura_item` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_factura` bigint(20) unsigned NOT NULL,
  `id_pedido_item` bigint(20) unsigned NOT NULL,
  `unidades_facturadas` decimal(10,3) DEFAULT NULL,
  `importe_facturado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_factura_item`),
  KEY `idx_factura_items_factura` (`id_factura`),
  KEY `idx_factura_items_pedido_item` (`id_pedido_item`),
  CONSTRAINT `fk_factura_items_factura` FOREIGN KEY (`id_factura`) REFERENCES `facturas` (`id_factura`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_factura_items_pedido_item` FOREIGN KEY (`id_pedido_item`) REFERENCES `pedido_items` (`id_pedido_item`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `factura_items`
--

LOCK TABLES `factura_items` WRITE;
/*!40000 ALTER TABLE `factura_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `factura_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facturas`
--

DROP TABLE IF EXISTS `facturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `facturas` (
  `id_factura` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_trabajo` bigint(20) unsigned DEFAULT NULL,
  `id_contrato` bigint(20) unsigned DEFAULT NULL,
  `id_empresa_cliente` bigint(20) unsigned NOT NULL,
  `id_empresa_facturadora` bigint(20) unsigned DEFAULT NULL,
  `numero_factura` varchar(100) DEFAULT NULL,
  `numero_factura_ccp` varchar(100) DEFAULT NULL,
  `serie` varchar(20) DEFAULT NULL,
  `orden_factura` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `fecha_solicitud` date DEFAULT NULL,
  `fecha_emision` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `importe` decimal(14,2) NOT NULL DEFAULT 0.00,
  `base_imponible` decimal(14,2) DEFAULT NULL,
  `iva` decimal(14,2) DEFAULT NULL,
  `retencion` decimal(14,2) DEFAULT NULL,
  `total` decimal(14,2) DEFAULT NULL,
  `estado` enum('pendiente','solicitada','emitida','enviada','anulada') NOT NULL DEFAULT 'pendiente',
  `autofactura` tinyint(1) NOT NULL DEFAULT 0,
  `sociedad` varchar(180) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_factura`),
  UNIQUE KEY `uq_facturas_ctx_facturadora_numero` (`id_contexto`,`id_empresa_facturadora`,`numero_factura`),
  KEY `idx_facturas_contexto` (`id_contexto`),
  KEY `idx_facturas_id_contexto` (`id_factura`,`id_contexto`),
  KEY `idx_facturas_trabajo_contexto` (`id_trabajo`,`id_contexto`),
  KEY `idx_facturas_contrato_contexto` (`id_contrato`,`id_contexto`),
  KEY `idx_facturas_empresa_contexto` (`id_empresa_cliente`,`id_contexto`),
  KEY `idx_facturas_empresa_facturadora_contexto` (`id_empresa_facturadora`,`id_contexto`),
  KEY `idx_facturas_numero_contexto` (`numero_factura`,`id_contexto`),
  KEY `idx_facturas_estado_contexto` (`estado`,`id_contexto`),
  KEY `idx_facturas_fecha_contexto` (`fecha_emision`,`id_contexto`),
  CONSTRAINT `fk_facturas_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_facturas_contrato_contexto` FOREIGN KEY (`id_contrato`, `id_contexto`) REFERENCES `contratos` (`id_contrato`, `id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_facturas_empresa_contexto` FOREIGN KEY (`id_empresa_cliente`, `id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_facturas_empresa_facturadora_contexto` FOREIGN KEY (`id_empresa_facturadora`, `id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_facturas_trabajo_contexto` FOREIGN KEY (`id_trabajo`, `id_contexto`) REFERENCES `trabajos` (`id_trabajo`, `id_contexto`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facturas`
--

LOCK TABLES `facturas` WRITE;
/*!40000 ALTER TABLE `facturas` DISABLE KEYS */;
/*!40000 ALTER TABLE `facturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `importacion_filas`
--

DROP TABLE IF EXISTS `importacion_filas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `importacion_filas` (
  `id_importacion_fila` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_importacion` bigint(20) unsigned NOT NULL,
  `archivo_origen` varchar(255) DEFAULT NULL,
  `hoja_origen` varchar(255) DEFAULT NULL,
  `numero_fila` int(10) unsigned NOT NULL,
  `datos_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`datos_json`)),
  `estado` enum('pendiente','valido','error','duplicado','importado') NOT NULL DEFAULT 'pendiente',
  `resultado` varchar(20) DEFAULT NULL,
  `tipo_fila` varchar(50) DEFAULT NULL,
  `severidad` varchar(20) DEFAULT NULL,
  `codigo` varchar(100) DEFAULT NULL,
  `clasificacion` varchar(40) DEFAULT NULL,
  `mensaje_error` text DEFAULT NULL,
  `decision_sugerida` text DEFAULT NULL,
  `id_registro_destino` bigint(20) unsigned DEFAULT NULL,
  `tipo_registro_destino` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_importacion_fila`),
  KEY `idx_import_filas_importacion_estado` (`id_importacion`,`estado`),
  KEY `idx_import_filas_importacion_severidad` (`id_importacion`,`severidad`),
  KEY `idx_import_filas_importacion_clasificacion` (`id_importacion`,`clasificacion`),
  KEY `idx_import_filas_archivo_hoja` (`archivo_origen`,`hoja_origen`),
  CONSTRAINT `fk_import_filas_importacion` FOREIGN KEY (`id_importacion`) REFERENCES `importaciones` (`id_importacion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `importacion_filas`
--

LOCK TABLES `importacion_filas` WRITE;
/*!40000 ALTER TABLE `importacion_filas` DISABLE KEYS */;
/*!40000 ALTER TABLE `importacion_filas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `importaciones`
--

DROP TABLE IF EXISTS `importaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `importaciones` (
  `id_importacion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `tipo` enum('estaciones','trabajos','tarifario','facturas') NOT NULL,
  `archivo_original` varchar(255) NOT NULL,
  `total_filas` int(10) unsigned NOT NULL DEFAULT 0,
  `filas_importadas` int(10) unsigned NOT NULL DEFAULT 0,
  `filas_ignoradas` int(10) unsigned NOT NULL DEFAULT 0,
  `filas_con_error` int(10) unsigned NOT NULL DEFAULT 0,
  `filas_con_aviso` int(10) unsigned NOT NULL DEFAULT 0,
  `filas_duplicadas` int(10) unsigned NOT NULL DEFAULT 0,
  `estado` enum('subido','validando','validado','importando','completado','fallido') NOT NULL DEFAULT 'subido',
  `version_importacion` int(10) unsigned NOT NULL DEFAULT 1,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `resumen_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`resumen_json`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_importacion`),
  KEY `idx_importaciones_contexto` (`id_contexto`),
  KEY `idx_importaciones_usuario` (`id_usuario`),
  CONSTRAINT `fk_importaciones_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_importaciones_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `importaciones`
--

LOCK TABLES `importaciones` WRITE;
/*!40000 ALTER TABLE `importaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `importaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `legalizaciones`
--

DROP TABLE IF EXISTS `legalizaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `legalizaciones` (
  `id_legalizacion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_trabajo` bigint(20) unsigned NOT NULL,
  `id_usuario_responsable` bigint(20) unsigned DEFAULT NULL,
  `tipo_legalizacion` varchar(150) NOT NULL,
  `numero_expediente` varchar(120) DEFAULT NULL,
  `organismo` varchar(180) DEFAULT NULL,
  `estado` enum('pendiente','en_tramite','resuelta','cancelada') NOT NULL DEFAULT 'pendiente',
  `descripcion_seleccionable` varchar(255) DEFAULT NULL,
  `descripcion_libre` text DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_limite` date DEFAULT NULL,
  `fecha_resolucion` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_legalizacion`),
  UNIQUE KEY `uq_legalizaciones_contexto_num_expediente` (`id_contexto`,`numero_expediente`),
  KEY `idx_legalizaciones_contexto` (`id_contexto`),
  KEY `idx_legalizaciones_id_contexto` (`id_legalizacion`,`id_contexto`),
  KEY `idx_legalizaciones_trabajo_contexto` (`id_trabajo`,`id_contexto`),
  KEY `idx_legalizaciones_usuario_contexto` (`id_usuario_responsable`,`id_contexto`),
  CONSTRAINT `fk_legalizaciones_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_legalizaciones_trabajo_contexto` FOREIGN KEY (`id_trabajo`, `id_contexto`) REFERENCES `trabajos` (`id_trabajo`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_legalizaciones_usuario_responsable` FOREIGN KEY (`id_usuario_responsable`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `legalizaciones`
--

LOCK TABLES `legalizaciones` WRITE;
/*!40000 ALTER TABLE `legalizaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `legalizaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `legalizaciones_contactos`
--

DROP TABLE IF EXISTS `legalizaciones_contactos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `legalizaciones_contactos` (
  `id_legalizacion_contacto` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_legalizacion` bigint(20) unsigned NOT NULL,
  `id_contacto_empresa` bigint(20) unsigned NOT NULL,
  `rol_en_legalizacion` varchar(150) DEFAULT NULL,
  `principal` tinyint(1) NOT NULL DEFAULT 0,
  `observaciones` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_legalizacion_contacto`),
  UNIQUE KEY `uq_legalizaciones_contactos` (`id_contexto`,`id_legalizacion`,`id_contacto_empresa`),
  KEY `idx_legalizaciones_contactos_contexto` (`id_contexto`),
  KEY `idx_legalizaciones_contactos_legalizacion_contexto` (`id_legalizacion`,`id_contexto`),
  KEY `idx_legalizaciones_contactos_contacto_contexto` (`id_contacto_empresa`,`id_contexto`),
  CONSTRAINT `fk_legalizaciones_contactos_contacto_contexto` FOREIGN KEY (`id_contacto_empresa`, `id_contexto`) REFERENCES `contactos_empresas` (`id_contacto_empresa`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_legalizaciones_contactos_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_legalizaciones_contactos_legalizacion_contexto` FOREIGN KEY (`id_legalizacion`, `id_contexto`) REFERENCES `legalizaciones` (`id_legalizacion`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `legalizaciones_contactos`
--

LOCK TABLES `legalizaciones_contactos` WRITE;
/*!40000 ALTER TABLE `legalizaciones_contactos` DISABLE KEYS */;
/*!40000 ALTER TABLE `legalizaciones_contactos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensajes_internos`
--

DROP TABLE IF EXISTS `mensajes_internos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mensajes_internos` (
  `id_mensaje` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_remitente` bigint(20) unsigned NOT NULL,
  `id_destinatario` bigint(20) unsigned DEFAULT NULL,
  `id_mensaje_padre` bigint(20) unsigned DEFAULT NULL,
  `id_solicitud_soporte` bigint(20) unsigned DEFAULT NULL,
  `tipo_remitente` varchar(20) NOT NULL DEFAULT 'user',
  `asunto` varchar(255) NOT NULL,
  `cuerpo` text NOT NULL,
  `prioridad` enum('normal','alta','urgente') NOT NULL DEFAULT 'normal',
  `es_aviso_sistema` tinyint(1) NOT NULL DEFAULT 0,
  `leido_at` datetime DEFAULT NULL,
  `archivado` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_mensaje`),
  KEY `idx_mensajes_remitente` (`id_remitente`),
  KEY `idx_mensajes_destinatario` (`id_destinatario`),
  KEY `idx_mensajes_leido` (`leido_at`),
  KEY `idx_mensajes_fecha` (`created_at`),
  KEY `idx_mensajes_tipo_remitente` (`tipo_remitente`),
  KEY `idx_mensajes_padre` (`id_mensaje_padre`),
  KEY `idx_mensajes_soporte` (`id_solicitud_soporte`),
  CONSTRAINT `fk_mensajes_destinatario` FOREIGN KEY (`id_destinatario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mensajes_padre` FOREIGN KEY (`id_mensaje_padre`) REFERENCES `mensajes_internos` (`id_mensaje`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_mensajes_remitente` FOREIGN KEY (`id_remitente`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mensajes_solicitud_soporte` FOREIGN KEY (`id_solicitud_soporte`) REFERENCES `solicitudes_soporte` (`id_solicitud_soporte`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensajes_internos`
--

LOCK TABLES `mensajes_internos` WRITE;
/*!40000 ALTER TABLE `mensajes_internos` DISABLE KEYS */;
/*!40000 ALTER TABLE `mensajes_internos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026_03_24_000001_create_framework_support_tables',1),(2,'2026_03_24_000002_create_personal_access_tokens_table',1),(3,'2026_03_24_000010_create_security_core_tables',1),(4,'2026_03_24_000015_create_maestros_tables',1),(5,'2026_03_24_000020_create_empresas_contactos_base_tables',1),(6,'2026_03_24_000025_create_estaciones_tables',1),(7,'2026_03_24_000030_create_security_users_tables',1),(8,'2026_03_24_000035_create_tarifarios_tables',1),(9,'2026_03_24_000040_create_comunicacion_operativa_base_tables',1),(10,'2026_03_24_000050_create_trabajos_operativa_tables',1),(11,'2026_03_24_000055_create_presupuestos_tables',1),(12,'2026_03_24_000060_create_legalizaciones_tables',1),(13,'2026_03_24_000070_create_importacion_tables',1),(14,'2026_03_24_000080_create_audit_log_table',1),(15,'2026_04_13_000001_add_email_recuperacion_and_create_mensajes_tables',1),(16,'2026_04_24_000090_create_support_tables_and_extend_messages',1),(17,'2026_04_24_000100_normalize_erp_role_catalog',1),(18,'2026_04_30_000110_align_audit_log_structure',1),(19,'2026_05_02_000120_create_factura_items_and_update_facturas',1),(20,'2026_05_02_000125_add_interface_mode_to_usuarios',1),(21,'2026_05_02_165419_add_pendiente_facturar_to_trabajos_estado_enum',1),(22,'2026_05_02_170426_drop_personal_access_tokens_table',1),(23,'2026_05_02_180000_add_borrador_and_cancelado_to_pedidos_estado_enum',1),(24,'2026_05_05_000130_create_contrato_empresas_facturadoras',1),(25,'2026_05_06_000140_cleanup_demo_legacy_schema',1),(26,'2026_05_07_000150_add_import_warning_metadata',1),(27,'2026_05_07_000160_align_ciete_final_schema_rules',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedido_items`
--

DROP TABLE IF EXISTS `pedido_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pedido_items` (
  `id_pedido_item` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_pedido` bigint(20) unsigned NOT NULL,
  `id_tarifario_linea` bigint(20) unsigned DEFAULT NULL,
  `codigo_servicio` varchar(30) DEFAULT NULL,
  `numero_tarifa` varchar(30) DEFAULT NULL,
  `descripcion_servicio` varchar(255) DEFAULT NULL,
  `precio_unitario` decimal(14,2) NOT NULL DEFAULT 0.00,
  `cantidad` decimal(14,3) NOT NULL DEFAULT 1.000,
  `total_linea` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_pedido_item`),
  KEY `idx_pedido_items_contexto` (`id_contexto`),
  KEY `idx_pedido_items_pedido_contexto` (`id_pedido`,`id_contexto`),
  KEY `idx_pedido_items_tarifa_contexto` (`id_tarifario_linea`,`id_contexto`),
  CONSTRAINT `fk_pedido_items_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pedido_items_pedido_contexto` FOREIGN KEY (`id_pedido`, `id_contexto`) REFERENCES `pedidos` (`id_pedido`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pedido_items_tarifa_contexto` FOREIGN KEY (`id_tarifario_linea`, `id_contexto`) REFERENCES `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedido_items`
--

LOCK TABLES `pedido_items` WRITE;
/*!40000 ALTER TABLE `pedido_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `pedido_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pedidos` (
  `id_pedido` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_trabajo` bigint(20) unsigned NOT NULL,
  `id_tarifario` bigint(20) unsigned DEFAULT NULL,
  `numero_pedido` varchar(100) NOT NULL,
  `fecha_solicitud` date DEFAULT NULL,
  `fecha_recepcion` date DEFAULT NULL,
  `importe_pedido` decimal(14,2) NOT NULL DEFAULT 0.00,
  `importe_solicitado` decimal(14,2) DEFAULT NULL,
  `importe_facturado` decimal(14,2) DEFAULT NULL,
  `unidades_pedido` decimal(14,3) NOT NULL DEFAULT 1.000,
  `unidades_solicitadas` decimal(14,3) DEFAULT NULL,
  `estado` enum('pendiente','solicitado','recibido','en_ejecucion','facturado_parcial','facturado','cancelado','anulado') NOT NULL DEFAULT 'pendiente',
  `pedido_completo` tinyint(1) DEFAULT NULL,
  `tiene_mas_de_1_item` tinyint(1) DEFAULT NULL,
  `facturado_completo` tinyint(1) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_pedido`),
  KEY `idx_pedidos_contexto` (`id_contexto`),
  KEY `idx_pedidos_id_contexto` (`id_pedido`,`id_contexto`),
  KEY `idx_pedidos_trabajo_contexto` (`id_trabajo`,`id_contexto`),
  KEY `idx_pedidos_numero_contexto` (`numero_pedido`,`id_contexto`),
  KEY `idx_pedidos_estado_contexto` (`estado`,`id_contexto`),
  KEY `idx_pedidos_tarifario_contexto` (`id_tarifario`,`id_contexto`),
  CONSTRAINT `fk_pedidos_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pedidos_tarifario_contexto` FOREIGN KEY (`id_tarifario`, `id_contexto`) REFERENCES `tarifarios` (`id_tarifario`, `id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pedidos_trabajo_contexto` FOREIGN KEY (`id_trabajo`, `id_contexto`) REFERENCES `trabajos` (`id_trabajo`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos`
--

LOCK TABLES `pedidos` WRITE;
/*!40000 ALTER TABLE `pedidos` DISABLE KEYS */;
/*!40000 ALTER TABLE `pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permisos`
--

DROP TABLE IF EXISTS `permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permisos` (
  `id_permiso` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_permiso`),
  UNIQUE KEY `uq_permisos_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
INSERT INTO `permisos` VALUES (1,'Ver usuarios','usuarios.ver','Consulta de usuarios',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(2,'Gestionar usuarios','usuarios.gestionar','Alta, baja y edicion de usuarios',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(3,'Gestionar roles','roles.gestionar','Gestion de roles y permisos',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(4,'Ver trabajos','trabajos.ver','Consulta de trabajos',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(5,'Crear trabajos','trabajos.crear','Creacion de trabajos',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(6,'Editar trabajos','trabajos.editar','Edicion de trabajos',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(7,'Finalizar trabajos','trabajos.finalizar','Finalizacion funcional de trabajos',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(11,'Ver presupuestos','presupuestos.ver','Consulta de presupuestos',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(12,'Gestionar presupuestos','presupuestos.gestionar','Creacion y edicion de presupuestos',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(13,'Ver pedidos','pedidos.ver','Consulta de pedidos',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(15,'Ver facturas','facturas.ver','Consulta de facturas',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(17,'Gestionar cobros','cobros.gestionar','Registro y conciliacion de cobros',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(18,'Ver legalizaciones','legalizaciones.ver','Consulta de legalizaciones',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(19,'Gestionar legalizaciones','legalizaciones.gestionar','Gestion de legalizaciones',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(20,'Ver estaciones','estaciones.ver','Consulta de estaciones de servicio',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(22,'Ver tarifarios','tarifarios.ver','Consulta de tarifarios',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(23,'Gestionar tarifarios','tarifarios.gestionar','Gestion de tarifarios',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(25,'Ver reportes','reportes.ver','Consulta de reportes',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(26,'Ver importaciones','importaciones.ver','Consulta del historial de importaciones',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(27,'Ejecutar importaciones','importaciones.ejecutar','Ejecutar importaciones de datos',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(28,'Ver auditoria','auditoria.ver','Consulta del log de auditoria',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(29,'Gestionar configuracion','config.gestionar','Gestion de maestros y configuracion del sistema',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(30,'Ver contratos','contratos.ver','Consulta de contratos',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(31,'Gestionar contratos','contratos.gestionar','Gestion de contratos',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(32,'Crear usuarios','usuarios.crear','Alta de usuarios',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(33,'Editar usuarios','usuarios.editar','Edicion, activacion y baja logica de usuarios',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(34,'Eliminar trabajos','trabajos.eliminar','Cancelacion o eliminacion restringida de trabajos',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(35,'Editar trabajo finalizado','trabajos.editar_finalizado','Edicion excepcional de trabajos finalizados',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(36,'Cambiar estado de trabajos','trabajos.cambiar_estado','Cambio de estado operativo de trabajos',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(37,'Marcar trabajos terminados','trabajos.marcar_terminado','Marcado tecnico de trabajos como terminados',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(38,'Crear pedidos','pedidos.crear','Alta de pedidos',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(39,'Editar pedidos','pedidos.editar','Edicion de pedidos',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(40,'Eliminar pedidos','pedidos.eliminar','Cancelacion o eliminacion restringida de pedidos',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(41,'Crear facturas','facturas.crear','Alta de facturas',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(42,'Editar facturas','facturas.editar','Edicion de facturas',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(43,'Eliminar facturas','facturas.eliminar','Anulacion o eliminacion restringida de facturas',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(44,'Crear estaciones','estaciones.crear','Alta de estaciones',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(45,'Editar estaciones','estaciones.editar','Edicion de estaciones',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(46,'Eliminar estaciones','estaciones.eliminar','Desactivacion o eliminacion restringida de estaciones',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(47,'Ver clientes','clientes.ver','Consulta de clientes y empresas contextuales',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(48,'Crear clientes','clientes.crear','Alta de clientes y empresas contextuales',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(49,'Editar clientes','clientes.editar','Edicion de clientes y empresas contextuales',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(50,'Eliminar clientes','clientes.eliminar','Desactivacion o eliminacion restringida de clientes',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(51,'Exportar auditoria','auditoria.exportar','Exportacion del registro de auditoria',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(52,'Limpiar auditoria','auditoria.limpiar','Limpieza controlada del registro de auditoria',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(53,'Confirmar importaciones','importaciones.confirmar','Confirmacion de importaciones revisadas',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(54,'Gestionar soporte','soporte.gestionar','Gestion tecnica de solicitudes de soporte',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(55,'Exportar facturas','facturas.exportar','Exportacion de listados y detalle de facturas',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(56,'Ver maestros','maestros.ver','Acceso al panel separado de datos maestros',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(57,'Gestionar maestros','maestros.gestionar','Gestion global de datos maestros',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(58,'Crear contratos','contratos.crear','Alta de contratos maestros',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(59,'Editar contratos','contratos.editar','Edicion de contratos maestros',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(60,'Eliminar contratos','contratos.eliminar','Desactivacion de contratos maestros',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(61,'Ver sociedades facturadoras permitidas','sociedades_facturadoras.ver','Consulta de sociedades facturadoras permitidas por contrato',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(62,'Crear sociedades facturadoras permitidas','sociedades_facturadoras.crear','Asignacion de sociedades facturadoras a contratos',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(63,'Editar sociedades facturadoras permitidas','sociedades_facturadoras.editar','Edicion de sociedades facturadoras permitidas',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(64,'Eliminar sociedades facturadoras permitidas','sociedades_facturadoras.eliminar','Desactivacion de sociedades facturadoras permitidas',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(65,'Crear tarifarios','tarifarios.crear','Alta de tarifarios maestros',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(66,'Editar tarifarios','tarifarios.editar','Edicion de tarifarios maestros',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(67,'Eliminar tarifarios','tarifarios.eliminar','Desactivacion de tarifarios maestros',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(68,'Ver lineas de tarifario','tarifario_lineas.ver','Consulta de lineas de tarifario',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(69,'Crear lineas de tarifario','tarifario_lineas.crear','Alta de lineas de tarifario',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(70,'Editar lineas de tarifario','tarifario_lineas.editar','Edicion de lineas de tarifario',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(71,'Eliminar lineas de tarifario','tarifario_lineas.eliminar','Desactivacion de lineas de tarifario',1,'2026-05-17 20:28:07','2026-05-17 20:28:07');
/*!40000 ALTER TABLE `permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `presupuesto_lineas`
--

DROP TABLE IF EXISTS `presupuesto_lineas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `presupuesto_lineas` (
  `id_linea_presupuesto` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_presupuesto` bigint(20) unsigned NOT NULL,
  `id_tarifario_linea` bigint(20) unsigned DEFAULT NULL,
  `orden` int(10) unsigned NOT NULL DEFAULT 1,
  `concepto_seleccionable` varchar(255) DEFAULT NULL,
  `concepto_libre` text DEFAULT NULL,
  `cantidad` decimal(14,3) NOT NULL DEFAULT 1.000,
  `precio_unitario` decimal(14,2) NOT NULL DEFAULT 0.00,
  `iva_porcentaje` decimal(5,2) NOT NULL DEFAULT 21.00,
  `total_linea` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_linea_presupuesto`),
  UNIQUE KEY `uq_presupuesto_lineas_contexto_presup_orden` (`id_contexto`,`id_presupuesto`,`orden`),
  KEY `idx_presupuesto_lineas_contexto` (`id_contexto`),
  KEY `idx_presupuesto_lineas_presup_contexto` (`id_presupuesto`,`id_contexto`),
  KEY `idx_presupuesto_lineas_tarifa_contexto` (`id_tarifario_linea`,`id_contexto`),
  CONSTRAINT `fk_presupuesto_lineas_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_presupuesto_lineas_presup_contexto` FOREIGN KEY (`id_presupuesto`, `id_contexto`) REFERENCES `presupuestos` (`id_presupuesto`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_presupuesto_lineas_tarifa_contexto` FOREIGN KEY (`id_tarifario_linea`, `id_contexto`) REFERENCES `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `presupuesto_lineas`
--

LOCK TABLES `presupuesto_lineas` WRITE;
/*!40000 ALTER TABLE `presupuesto_lineas` DISABLE KEYS */;
/*!40000 ALTER TABLE `presupuesto_lineas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `presupuestos`
--

DROP TABLE IF EXISTS `presupuestos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `presupuestos` (
  `id_presupuesto` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_trabajo` bigint(20) unsigned NOT NULL,
  `id_empresa_cliente` bigint(20) unsigned NOT NULL,
  `id_contacto_empresa_cliente` bigint(20) unsigned DEFAULT NULL,
  `id_estacion_servicio` bigint(20) unsigned DEFAULT NULL,
  `id_tarifario` bigint(20) unsigned DEFAULT NULL,
  `id_usuario_responsable` bigint(20) unsigned DEFAULT NULL,
  `id_usuario_cierre` bigint(20) unsigned DEFAULT NULL,
  `codigo_presupuesto` varchar(60) NOT NULL,
  `nombre_presupuesto` varchar(200) DEFAULT NULL,
  `estado` enum('borrador','enviado','aprobado','rechazado','anulado') NOT NULL DEFAULT 'borrador',
  `fecha_emision` date DEFAULT NULL,
  `fecha_validez` date DEFAULT NULL,
  `base_imponible` decimal(14,2) NOT NULL DEFAULT 0.00,
  `iva` decimal(14,2) NOT NULL DEFAULT 0.00,
  `retencion` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_presupuesto`),
  UNIQUE KEY `uq_presupuestos_contexto_codigo` (`id_contexto`,`codigo_presupuesto`),
  KEY `idx_presupuestos_contexto` (`id_contexto`),
  KEY `idx_presupuestos_id_contexto` (`id_presupuesto`,`id_contexto`),
  KEY `idx_presupuestos_trabajo_contexto` (`id_trabajo`,`id_contexto`),
  KEY `idx_presupuestos_empresa_contexto` (`id_empresa_cliente`,`id_contexto`),
  KEY `idx_presupuestos_estacion_contexto` (`id_estacion_servicio`,`id_contexto`),
  KEY `idx_presupuestos_tarifario_contexto` (`id_tarifario`,`id_contexto`),
  KEY `fk_presupuestos_contacto_contexto` (`id_contacto_empresa_cliente`,`id_contexto`),
  KEY `fk_presupuestos_usuario_responsable` (`id_usuario_responsable`),
  KEY `fk_presupuestos_usuario_cierre` (`id_usuario_cierre`),
  CONSTRAINT `fk_presupuestos_contacto_contexto` FOREIGN KEY (`id_contacto_empresa_cliente`, `id_contexto`) REFERENCES `contactos_empresas` (`id_contacto_empresa`, `id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_presupuestos_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_presupuestos_empresa_contexto` FOREIGN KEY (`id_empresa_cliente`, `id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_presupuestos_estacion_contexto` FOREIGN KEY (`id_estacion_servicio`, `id_contexto`) REFERENCES `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_presupuestos_tarifario_contexto` FOREIGN KEY (`id_tarifario`, `id_contexto`) REFERENCES `tarifarios` (`id_tarifario`, `id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_presupuestos_trabajo_contexto` FOREIGN KEY (`id_trabajo`, `id_contexto`) REFERENCES `trabajos` (`id_trabajo`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_presupuestos_usuario_cierre` FOREIGN KEY (`id_usuario_cierre`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_presupuestos_usuario_responsable` FOREIGN KEY (`id_usuario_responsable`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `presupuestos`
--

LOCK TABLES `presupuestos` WRITE;
/*!40000 ALTER TABLE `presupuestos` DISABLE KEYS */;
/*!40000 ALTER TABLE `presupuestos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol_permisos`
--

DROP TABLE IF EXISTS `rol_permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rol_permisos` (
  `id_rol_permiso` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_rol` bigint(20) unsigned NOT NULL,
  `id_permiso` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_rol_permiso`),
  UNIQUE KEY `uq_rol_permisos` (`id_rol`,`id_permiso`),
  KEY `idx_rol_permisos_permiso` (`id_permiso`),
  CONSTRAINT `fk_rol_permisos_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rol_permisos_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol_permisos`
--

LOCK TABLES `rol_permisos` WRITE;
/*!40000 ALTER TABLE `rol_permisos` DISABLE KEYS */;
INSERT INTO `rol_permisos` VALUES (1,1,51,'2026-05-17 20:28:07'),(2,1,52,'2026-05-17 20:28:07'),(3,1,28,'2026-05-17 20:28:07'),(4,1,48,'2026-05-17 20:28:07'),(5,1,49,'2026-05-17 20:28:07'),(6,1,50,'2026-05-17 20:28:07'),(7,1,47,'2026-05-17 20:28:07'),(8,1,17,'2026-05-17 20:28:07'),(9,1,29,'2026-05-17 20:28:07'),(10,1,58,'2026-05-17 20:28:07'),(11,1,59,'2026-05-17 20:28:07'),(12,1,60,'2026-05-17 20:28:07'),(13,1,31,'2026-05-17 20:28:07'),(14,1,30,'2026-05-17 20:28:07'),(15,1,44,'2026-05-17 20:28:07'),(16,1,45,'2026-05-17 20:28:07'),(17,1,46,'2026-05-17 20:28:07'),(18,1,20,'2026-05-17 20:28:07'),(19,1,41,'2026-05-17 20:28:07'),(20,1,42,'2026-05-17 20:28:07'),(21,1,43,'2026-05-17 20:28:07'),(22,1,55,'2026-05-17 20:28:07'),(23,1,15,'2026-05-17 20:28:07'),(24,1,53,'2026-05-17 20:28:07'),(25,1,27,'2026-05-17 20:28:07'),(26,1,26,'2026-05-17 20:28:07'),(27,1,19,'2026-05-17 20:28:07'),(28,1,18,'2026-05-17 20:28:07'),(29,1,57,'2026-05-17 20:28:07'),(30,1,56,'2026-05-17 20:28:07'),(31,1,38,'2026-05-17 20:28:07'),(32,1,39,'2026-05-17 20:28:07'),(33,1,40,'2026-05-17 20:28:07'),(34,1,13,'2026-05-17 20:28:07'),(35,1,12,'2026-05-17 20:28:07'),(36,1,11,'2026-05-17 20:28:07'),(37,1,25,'2026-05-17 20:28:07'),(38,1,3,'2026-05-17 20:28:07'),(39,1,62,'2026-05-17 20:28:07'),(40,1,63,'2026-05-17 20:28:07'),(41,1,64,'2026-05-17 20:28:07'),(42,1,61,'2026-05-17 20:28:07'),(43,1,54,'2026-05-17 20:28:07'),(44,1,69,'2026-05-17 20:28:07'),(45,1,70,'2026-05-17 20:28:07'),(46,1,71,'2026-05-17 20:28:07'),(47,1,68,'2026-05-17 20:28:07'),(48,1,65,'2026-05-17 20:28:07'),(49,1,66,'2026-05-17 20:28:07'),(50,1,67,'2026-05-17 20:28:07'),(51,1,23,'2026-05-17 20:28:07'),(52,1,22,'2026-05-17 20:28:07'),(53,1,36,'2026-05-17 20:28:07'),(54,1,5,'2026-05-17 20:28:07'),(55,1,6,'2026-05-17 20:28:07'),(56,1,35,'2026-05-17 20:28:07'),(57,1,34,'2026-05-17 20:28:07'),(58,1,7,'2026-05-17 20:28:07'),(59,1,37,'2026-05-17 20:28:07'),(60,1,4,'2026-05-17 20:28:07'),(61,1,32,'2026-05-17 20:28:07'),(62,1,33,'2026-05-17 20:28:07'),(63,1,2,'2026-05-17 20:28:07'),(64,1,1,'2026-05-17 20:28:07'),(65,2,4,'2026-05-17 20:28:07'),(66,2,5,'2026-05-17 20:28:07'),(67,2,6,'2026-05-17 20:28:07'),(68,2,36,'2026-05-17 20:28:07'),(69,2,37,'2026-05-17 20:28:07'),(70,2,11,'2026-05-17 20:28:07'),(71,2,12,'2026-05-17 20:28:07'),(72,2,20,'2026-05-17 20:28:07'),(73,2,47,'2026-05-17 20:28:07'),(74,2,13,'2026-05-17 20:28:07'),(75,2,38,'2026-05-17 20:28:07'),(76,2,39,'2026-05-17 20:28:07'),(77,3,1,'2026-05-17 20:28:07'),(78,3,32,'2026-05-17 20:28:07'),(79,3,33,'2026-05-17 20:28:07'),(80,3,2,'2026-05-17 20:28:07'),(81,3,4,'2026-05-17 20:28:07'),(82,3,5,'2026-05-17 20:28:07'),(83,3,6,'2026-05-17 20:28:07'),(84,3,7,'2026-05-17 20:28:07'),(85,3,34,'2026-05-17 20:28:07'),(86,3,35,'2026-05-17 20:28:07'),(87,3,36,'2026-05-17 20:28:07'),(88,3,37,'2026-05-17 20:28:07'),(89,3,13,'2026-05-17 20:28:07'),(90,3,38,'2026-05-17 20:28:07'),(91,3,39,'2026-05-17 20:28:07'),(92,3,40,'2026-05-17 20:28:07'),(93,3,15,'2026-05-17 20:28:07'),(94,3,41,'2026-05-17 20:28:07'),(95,3,42,'2026-05-17 20:28:07'),(96,3,43,'2026-05-17 20:28:07'),(97,3,55,'2026-05-17 20:28:07'),(98,3,20,'2026-05-17 20:28:07'),(99,3,44,'2026-05-17 20:28:07'),(100,3,45,'2026-05-17 20:28:07'),(101,3,46,'2026-05-17 20:28:07'),(102,3,47,'2026-05-17 20:28:07'),(103,3,48,'2026-05-17 20:28:07'),(104,3,49,'2026-05-17 20:28:07'),(105,3,50,'2026-05-17 20:28:07'),(106,3,56,'2026-05-17 20:28:07'),(107,3,57,'2026-05-17 20:28:07'),(108,3,30,'2026-05-17 20:28:07'),(109,3,58,'2026-05-17 20:28:07'),(110,3,59,'2026-05-17 20:28:07'),(111,3,60,'2026-05-17 20:28:07'),(112,3,61,'2026-05-17 20:28:07'),(113,3,62,'2026-05-17 20:28:07'),(114,3,63,'2026-05-17 20:28:07'),(115,3,64,'2026-05-17 20:28:07'),(116,3,22,'2026-05-17 20:28:07'),(117,3,65,'2026-05-17 20:28:07'),(118,3,66,'2026-05-17 20:28:07'),(119,3,67,'2026-05-17 20:28:07'),(120,3,68,'2026-05-17 20:28:07'),(121,3,69,'2026-05-17 20:28:07'),(122,3,70,'2026-05-17 20:28:07'),(123,3,71,'2026-05-17 20:28:07'),(124,3,28,'2026-05-17 20:28:07'),(125,3,51,'2026-05-17 20:28:07'),(126,3,52,'2026-05-17 20:28:07'),(127,4,4,'2026-05-17 20:28:07'),(128,4,5,'2026-05-17 20:28:07'),(129,4,6,'2026-05-17 20:28:07'),(130,4,36,'2026-05-17 20:28:07'),(131,4,37,'2026-05-17 20:28:07'),(132,4,11,'2026-05-17 20:28:07'),(133,4,12,'2026-05-17 20:28:07'),(134,4,20,'2026-05-17 20:28:07'),(135,4,47,'2026-05-17 20:28:07'),(136,4,13,'2026-05-17 20:28:07'),(137,4,38,'2026-05-17 20:28:07'),(138,4,39,'2026-05-17 20:28:07'),(139,5,4,'2026-05-17 20:28:07'),(140,5,5,'2026-05-17 20:28:07'),(141,5,6,'2026-05-17 20:28:07'),(142,5,36,'2026-05-17 20:28:07'),(143,5,37,'2026-05-17 20:28:07'),(144,5,11,'2026-05-17 20:28:07'),(145,5,12,'2026-05-17 20:28:07'),(146,5,20,'2026-05-17 20:28:07'),(147,5,47,'2026-05-17 20:28:07'),(148,5,13,'2026-05-17 20:28:07'),(149,5,38,'2026-05-17 20:28:07'),(150,5,39,'2026-05-17 20:28:07'),(151,6,4,'2026-05-17 20:28:07'),(152,6,13,'2026-05-17 20:28:07'),(153,6,15,'2026-05-17 20:28:07'),(154,6,41,'2026-05-17 20:28:07'),(155,6,42,'2026-05-17 20:28:07'),(156,6,43,'2026-05-17 20:28:07'),(157,6,55,'2026-05-17 20:28:07'),(158,6,30,'2026-05-17 20:28:07'),(159,6,61,'2026-05-17 20:28:07'),(160,6,22,'2026-05-17 20:28:07'),(161,6,68,'2026-05-17 20:28:07');
/*!40000 ALTER TABLE `rol_permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id_rol` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `uq_roles_nombre` (`nombre`),
  UNIQUE KEY `uq_roles_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Admin','admin','Acceso total al ERP',1,'2026-05-17 20:28:06','2026-05-17 20:28:06'),(2,'Ejecución','ejecucion','Perfil operativo general con acceso a los contextos asignados',1,'2026-05-17 20:28:06','2026-05-17 20:28:07'),(3,'Dirección','director','Supervisión global, revisión y control de cierre operativo',1,'2026-05-17 20:28:06','2026-05-17 20:28:06'),(4,'Ejecución Moeve','ejecucion_moeve','Perfil operativo restringido al contexto Moeve/Cepsa',1,'2026-05-17 20:28:06','2026-05-17 20:28:06'),(5,'Ejecución Repsol','ejecucion_repsol','Perfil operativo restringido al contexto Repsol',1,'2026-05-17 20:28:06','2026-05-17 20:28:06'),(6,'Contabilidad','contable','Perfil económico para pedidos y facturas',1,'2026-05-17 20:28:06','2026-05-17 20:28:07');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesiones_login`
--

DROP TABLE IF EXISTS `sesiones_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesiones_login` (
  `id_sesion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `id_contexto` bigint(20) unsigned DEFAULT NULL,
  `fecha_hora_login` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_hora_logout` datetime DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_sesion`),
  KEY `idx_sesiones_login_usuario` (`id_usuario`),
  KEY `idx_sesiones_login_contexto` (`id_contexto`),
  CONSTRAINT `fk_sesiones_login_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_sesiones_login_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones_login`
--

LOCK TABLES `sesiones_login` WRITE;
/*!40000 ALTER TABLE `sesiones_login` DISABLE KEYS */;
INSERT INTO `sesiones_login` VALUES (1,6,3,'2026-05-17 22:49:35',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-17 20:49:35','2026-05-17 20:49:35'),(2,6,3,'2026-05-18 06:54:11',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-18 04:54:11','2026-05-18 04:54:11');
/*!40000 ALTER TABLE `sesiones_login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('cYj7MXm19rA3YAhA85wRZOUGkBqVmCUyRk0wmrR5',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoia01nd09YTk5OSzZMNmlPaGZPVFJZYjE0TWdxVm5uemRqWFBvaVBwQiI7czo2OiJsb2NhbGUiO3M6MjoiZXMiO3M6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779058300),('kK41qfYuB5GkoatAWMHCmMp0iddO9RnROmAIWALQ',6,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','YTo4OntzOjY6Il90b2tlbiI7czo0MDoicWxuSUZrV3RMRUpCazRIbXpYc1F6N09heFdteHVISkY4Szh2c2ZyciI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjMwOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZmFjdHVyYXMiO3M6NToicm91dGUiO3M6MTQ6ImZhY3R1cmFzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo2OiJsb2NhbGUiO3M6MjoiZXMiO3M6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjY7czoxNToic2VzaW9uX2xvZ2luX2lkIjtpOjE7czo1OiJjaWV0ZSI7YToxOntzOjE0OiJhY3RpdmVfY29udGV4dCI7aToxO319',1779058300),('RxM8r6XTNl6tUS2fCYHNCWEOPinG7qtjMbVHg2CT',6,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','YTo4OntzOjY6Il90b2tlbiI7czo0MDoiTDJ2cmtLRjdNclRnbk1ZTTZmUndEZU9hUGpsRnlXS0xqbjU0THRmTyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjMwOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZmFjdHVyYXMiO3M6NToicm91dGUiO3M6MTQ6ImZhY3R1cmFzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo2OiJsb2NhbGUiO3M6MjoiZXMiO3M6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjY7czoxNToic2VzaW9uX2xvZ2luX2lkIjtpOjI7czo1OiJjaWV0ZSI7YToxOntzOjE0OiJhY3RpdmVfY29udGV4dCI7aTozO319',1779087627),('TnJNBKqcJ9jxV1cQJys1XeagMHoZvXfhsnWcs7VQ',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoianBwellwVDJlUHZxZkpPQWpiSjlOSW10QlVvakZOOE9ya0JiSUxibyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NjoibG9jYWxlIjtzOjI6ImVzIjt9',1779087227);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitudes_soporte`
--

DROP TABLE IF EXISTS `solicitudes_soporte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `solicitudes_soporte` (
  `id_solicitud_soporte` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario_solicitante` bigint(20) unsigned NOT NULL,
  `id_usuario_asignado` bigint(20) unsigned DEFAULT NULL,
  `tema` varchar(100) NOT NULL,
  `asunto` varchar(255) NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'pending',
  `prioridad` varchar(20) NOT NULL DEFAULT 'normal',
  `ultimo_mensaje_at` datetime DEFAULT NULL,
  `resuelta_at` datetime DEFAULT NULL,
  `archivada_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_solicitud_soporte`),
  KEY `idx_soporte_solicitante` (`id_usuario_solicitante`),
  KEY `idx_soporte_asignado` (`id_usuario_asignado`),
  KEY `idx_soporte_estado` (`estado`),
  KEY `idx_soporte_prioridad` (`prioridad`),
  KEY `idx_soporte_ultimo_mensaje` (`ultimo_mensaje_at`),
  CONSTRAINT `fk_soporte_asignado` FOREIGN KEY (`id_usuario_asignado`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_soporte_solicitante` FOREIGN KEY (`id_usuario_solicitante`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitudes_soporte`
--

LOCK TABLES `solicitudes_soporte` WRITE;
/*!40000 ALTER TABLE `solicitudes_soporte` DISABLE KEYS */;
/*!40000 ALTER TABLE `solicitudes_soporte` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tarifario_lineas`
--

DROP TABLE IF EXISTS `tarifario_lineas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tarifario_lineas` (
  `id_tarifario_linea` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_tarifario` bigint(20) unsigned NOT NULL,
  `codigo_tarifa` varchar(30) NOT NULL,
  `grupo` varchar(120) DEFAULT NULL,
  `actuacion` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tarifa_anterior` decimal(14,2) DEFAULT NULL,
  `tarifa_base` decimal(14,2) NOT NULL,
  `tarifa_aplicada` decimal(14,2) NOT NULL,
  `id_unidad` bigint(20) unsigned DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_tarifario_linea`),
  UNIQUE KEY `uq_tarifa_linea_ctx_tarif_codigo` (`id_contexto`,`id_tarifario`,`codigo_tarifa`),
  KEY `idx_tarifario_lineas_contexto` (`id_contexto`),
  KEY `idx_tarifario_lineas_id_contexto` (`id_tarifario_linea`,`id_contexto`),
  KEY `idx_tarifario_lineas_tarifario_contexto` (`id_tarifario`,`id_contexto`),
  KEY `fk_tarifario_lineas_unidad` (`id_unidad`),
  CONSTRAINT `fk_tarifario_lineas_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_tarifario_lineas_tarifario_contexto` FOREIGN KEY (`id_tarifario`, `id_contexto`) REFERENCES `tarifarios` (`id_tarifario`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tarifario_lineas_unidad` FOREIGN KEY (`id_unidad`) REFERENCES `unidades` (`id_unidad`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarifario_lineas`
--

LOCK TABLES `tarifario_lineas` WRITE;
/*!40000 ALTER TABLE `tarifario_lineas` DISABLE KEYS */;
/*!40000 ALTER TABLE `tarifario_lineas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tarifarios`
--

DROP TABLE IF EXISTS `tarifarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tarifarios` (
  `id_tarifario` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_contrato` bigint(20) unsigned DEFAULT NULL,
  `nombre` varchar(160) NOT NULL,
  `version` varchar(40) DEFAULT NULL,
  `fecha_inicio_vigencia` date DEFAULT NULL,
  `fecha_fin_vigencia` date DEFAULT NULL,
  `factor_multiplicador` decimal(6,4) NOT NULL DEFAULT 1.0000,
  `moneda` char(3) NOT NULL DEFAULT 'EUR',
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_tarifario`),
  UNIQUE KEY `uq_tarifarios_ctx_nombre_version` (`id_contexto`,`nombre`,`version`),
  KEY `idx_tarifarios_contexto` (`id_contexto`),
  KEY `idx_tarifarios_id_contexto` (`id_tarifario`,`id_contexto`),
  KEY `idx_tarifarios_contrato_contexto` (`id_contrato`,`id_contexto`),
  CONSTRAINT `fk_tarifarios_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_tarifarios_contrato_contexto` FOREIGN KEY (`id_contrato`, `id_contexto`) REFERENCES `contratos` (`id_contrato`, `id_contexto`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarifarios`
--

LOCK TABLES `tarifarios` WRITE;
/*!40000 ALTER TABLE `tarifarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `tarifarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telefonos`
--

DROP TABLE IF EXISTS `telefonos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `telefonos` (
  `id_telefono` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_empresa` bigint(20) unsigned DEFAULT NULL,
  `id_contacto` bigint(20) unsigned DEFAULT NULL,
  `id_contacto_empresa` bigint(20) unsigned DEFAULT NULL,
  `id_usuario` bigint(20) unsigned DEFAULT NULL,
  `prefijo` varchar(10) DEFAULT NULL,
  `numero` varchar(30) NOT NULL,
  `tipo` enum('fijo','movil','oficina','personal','urgencias','otro') NOT NULL DEFAULT 'movil',
  `es_principal` tinyint(1) NOT NULL DEFAULT 0,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_telefono`),
  KEY `idx_telefonos_contexto` (`id_contexto`),
  KEY `idx_telefonos_empresa_contexto` (`id_empresa`,`id_contexto`),
  KEY `idx_telefonos_contacto` (`id_contacto`),
  KEY `idx_telefonos_contacto_empresa_contexto` (`id_contacto_empresa`,`id_contexto`),
  KEY `idx_telefonos_usuario_contexto` (`id_usuario`,`id_contexto`),
  CONSTRAINT `fk_telefonos_contacto` FOREIGN KEY (`id_contacto`) REFERENCES `contactos` (`id_contacto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_telefonos_contacto_empresa_contexto` FOREIGN KEY (`id_contacto_empresa`, `id_contexto`) REFERENCES `contactos_empresas` (`id_contacto_empresa`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_telefonos_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_telefonos_empresa_contexto` FOREIGN KEY (`id_empresa`, `id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_telefonos_usuario_contexto` FOREIGN KEY (`id_usuario`, `id_contexto`) REFERENCES `usuarios` (`id_usuario`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telefonos`
--

LOCK TABLES `telefonos` WRITE;
/*!40000 ALTER TABLE `telefonos` DISABLE KEYS */;
/*!40000 ALTER TABLE `telefonos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipos_documento`
--

DROP TABLE IF EXISTS `tipos_documento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipos_documento` (
  `id_tipo_documento` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `tiene_doble_factura` tinyint(1) NOT NULL DEFAULT 0,
  `tiene_orden_mto` tinyint(1) NOT NULL DEFAULT 0,
  `tiene_num_tarifa` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_documento`),
  UNIQUE KEY `uq_tipo_doc_contexto_codigo` (`id_contexto`,`codigo`),
  KEY `idx_tipos_documento_contexto` (`id_contexto`),
  KEY `idx_tipos_documento_id_contexto` (`id_tipo_documento`,`id_contexto`),
  CONSTRAINT `fk_tipos_documento_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipos_documento`
--

LOCK TABLES `tipos_documento` WRITE;
/*!40000 ALTER TABLE `tipos_documento` DISABLE KEYS */;
INSERT INTO `tipos_documento` VALUES (1,1,'CONTROL_TRABAJOS','Control de Trabajos Moeve',0,0,0,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(2,2,'DISENO','Diseno Repsol',0,0,1,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(3,2,'EDIFICACION','Edificacion Repsol',1,0,1,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(4,2,'OBRAS','Obras Repsol',1,0,1,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(5,2,'LICENCIAS','Licencias Repsol',0,0,1,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(6,2,'FV','Fotovoltaica Repsol',0,0,1,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(7,2,'ESTRUCTURAS','Estructuras y Vertidos Repsol',1,0,1,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(8,2,'MTO','Mantenimiento Repsol',1,1,1,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(9,2,'PUNTOS_RECARGA','Puntos de Recarga Repsol',0,0,1,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(10,3,'OTROS','Trabajo otros clientes',0,0,0,1,'2026-05-17 20:28:07','2026-05-17 20:28:07');
/*!40000 ALTER TABLE `tipos_documento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipos_trabajo`
--

DROP TABLE IF EXISTS `tipos_trabajo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipos_trabajo` (
  `id_tipo_trabajo` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_tipo_documento` bigint(20) unsigned NOT NULL,
  `codigo` varchar(80) NOT NULL,
  `nombre` varchar(180) NOT NULL,
  `responsable_ciete_defecto` varchar(150) DEFAULT NULL,
  `responsable_cliente_defecto` varchar(150) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_trabajo`),
  UNIQUE KEY `uq_tipo_trabajo_ctx_doc_codigo` (`id_contexto`,`id_tipo_documento`,`codigo`),
  KEY `idx_tipos_trabajo_contexto` (`id_contexto`),
  KEY `idx_tipos_trabajo_tipo_doc_contexto` (`id_tipo_documento`,`id_contexto`),
  KEY `idx_tipos_trabajo_id_contexto` (`id_tipo_trabajo`,`id_contexto`),
  CONSTRAINT `fk_tipos_trabajo_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_tipos_trabajo_tipo_doc_contexto` FOREIGN KEY (`id_tipo_documento`, `id_contexto`) REFERENCES `tipos_documento` (`id_tipo_documento`, `id_contexto`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipos_trabajo`
--

LOCK TABLES `tipos_trabajo` WRITE;
/*!40000 ALTER TABLE `tipos_trabajo` DISABLE KEYS */;
INSERT INTO `tipos_trabajo` VALUES (1,1,1,'NPV','Nueva Propuesta de Valor',NULL,NULL,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(2,1,1,'REFORMA','Reforma General',NULL,NULL,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(3,1,1,'INDUSTRIA','Industria',NULL,NULL,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(4,2,2,'NPV','Nueva Propuesta de Valor',NULL,NULL,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(5,2,2,'REFORMA','Reforma General',NULL,NULL,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(6,3,10,'OBRA','Obra otros clientes',NULL,NULL,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(7,3,10,'MTO','Mantenimiento otros clientes',NULL,NULL,1,'2026-05-17 20:28:07','2026-05-17 20:28:07');
/*!40000 ALTER TABLE `tipos_trabajo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trabajos`
--

DROP TABLE IF EXISTS `trabajos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trabajos` (
  `id_trabajo` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_empresa_cliente` bigint(20) unsigned NOT NULL,
  `id_estacion_servicio` bigint(20) unsigned DEFAULT NULL,
  `id_tipo_documento` bigint(20) unsigned DEFAULT NULL,
  `id_tipo_trabajo` bigint(20) unsigned DEFAULT NULL,
  `id_contrato` bigint(20) unsigned DEFAULT NULL,
  `id_tarifario` bigint(20) unsigned DEFAULT NULL,
  `id_responsable_ciete` bigint(20) unsigned DEFAULT NULL,
  `numero_trabajo` int(10) unsigned NOT NULL,
  `numero_trabajo_operativo` varchar(100) DEFAULT NULL,
  `numero_estacion` varchar(30) DEFAULT NULL,
  `zona` varchar(10) DEFAULT NULL,
  `descripcion_trabajo` text DEFAULT NULL,
  `fecha_encargo` date DEFAULT NULL,
  `fecha_terminacion` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `numero_aviso` varchar(100) DEFAULT NULL,
  `orden_mantenimiento` varchar(100) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `responsable_cliente` varchar(150) DEFAULT NULL,
  `estado` enum('en_curso','terminado','pendiente_facturar','facturado','finalizado','cancelado') NOT NULL DEFAULT 'en_curso',
  `bloqueado_cierre` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_trabajo`),
  UNIQUE KEY `uq_trabajos_ctx_tipodoc_numero` (`id_contexto`,`id_tipo_documento`,`numero_trabajo`),
  KEY `idx_trabajos_contexto` (`id_contexto`),
  KEY `idx_trabajos_id_contexto` (`id_trabajo`,`id_contexto`),
  KEY `idx_trabajos_empresa_contexto` (`id_empresa_cliente`,`id_contexto`),
  KEY `idx_trabajos_estacion_contexto` (`id_estacion_servicio`,`id_contexto`),
  KEY `idx_trabajos_tipo_doc_contexto` (`id_tipo_documento`,`id_contexto`),
  KEY `idx_trabajos_tipo_trab_contexto` (`id_tipo_trabajo`,`id_contexto`),
  KEY `idx_trabajos_contrato_contexto` (`id_contrato`,`id_contexto`),
  KEY `idx_trabajos_tarifario_contexto` (`id_tarifario`,`id_contexto`),
  KEY `idx_trabajos_contexto_numero_operativo` (`id_contexto`,`numero_trabajo_operativo`),
  KEY `idx_trabajos_estado_contexto` (`estado`,`id_contexto`),
  KEY `idx_trabajos_responsable` (`id_responsable_ciete`),
  KEY `idx_trabajos_fecha_encargo_contexto` (`fecha_encargo`,`id_contexto`),
  CONSTRAINT `fk_trabajos_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_trabajos_contrato_contexto` FOREIGN KEY (`id_contrato`, `id_contexto`) REFERENCES `contratos` (`id_contrato`, `id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_trabajos_empresa_contexto` FOREIGN KEY (`id_empresa_cliente`, `id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_trabajos_estacion_contexto` FOREIGN KEY (`id_estacion_servicio`, `id_contexto`) REFERENCES `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_trabajos_responsable_ciete` FOREIGN KEY (`id_responsable_ciete`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_trabajos_tarifario_contexto` FOREIGN KEY (`id_tarifario`, `id_contexto`) REFERENCES `tarifarios` (`id_tarifario`, `id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_trabajos_tipo_doc_contexto` FOREIGN KEY (`id_tipo_documento`, `id_contexto`) REFERENCES `tipos_documento` (`id_tipo_documento`, `id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_trabajos_tipo_trab_contexto` FOREIGN KEY (`id_tipo_trabajo`, `id_contexto`) REFERENCES `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trabajos`
--

LOCK TABLES `trabajos` WRITE;
/*!40000 ALTER TABLE `trabajos` DISABLE KEYS */;
/*!40000 ALTER TABLE `trabajos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unidades`
--

DROP TABLE IF EXISTS `unidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unidades` (
  `id_unidad` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) NOT NULL,
  `abreviatura` varchar(10) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_unidad`),
  UNIQUE KEY `uq_unidades_abreviatura` (`abreviatura`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unidades`
--

LOCK TABLES `unidades` WRITE;
/*!40000 ALTER TABLE `unidades` DISABLE KEYS */;
INSERT INTO `unidades` VALUES (1,'Unidad','ud','Unidad general',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(2,'Hora','h','Hora de trabajo',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(3,'Metro cuadrado','m2','Superficie',1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(4,'Metro lineal','ml','Longitud',1,'2026-05-17 20:28:07','2026-05-17 20:28:07');
/*!40000 ALTER TABLE `unidades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_contextos`
--

DROP TABLE IF EXISTS `usuario_contextos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario_contextos` (
  `id_usuario_contexto` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `es_contexto_principal` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario_contexto`),
  UNIQUE KEY `uq_usuario_contexto` (`id_usuario`,`id_contexto`),
  KEY `idx_usuario_contextos_contexto` (`id_contexto`),
  CONSTRAINT `fk_usuario_contextos_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_usuario_contextos_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_contextos`
--

LOCK TABLES `usuario_contextos` WRITE;
/*!40000 ALTER TABLE `usuario_contextos` DISABLE KEYS */;
INSERT INTO `usuario_contextos` VALUES (1,1,1,0,1,'2026-05-17 22:28:07'),(2,1,2,0,1,'2026-05-17 22:28:07'),(3,1,3,1,1,'2026-05-17 22:28:07'),(4,2,1,0,1,'2026-05-17 22:28:08'),(5,2,2,0,1,'2026-05-17 22:28:08'),(6,2,3,1,1,'2026-05-17 22:28:08'),(7,3,1,0,1,'2026-05-17 22:28:08'),(8,3,2,0,1,'2026-05-17 22:28:08'),(9,3,3,1,1,'2026-05-17 22:28:08'),(10,4,1,1,1,'2026-05-17 22:28:08'),(11,5,2,1,1,'2026-05-17 22:28:08'),(12,6,1,0,1,'2026-05-17 22:28:09'),(13,6,2,0,1,'2026-05-17 22:28:09'),(14,6,3,1,1,'2026-05-17 22:28:09');
/*!40000 ALTER TABLE `usuario_contextos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_roles`
--

DROP TABLE IF EXISTS `usuario_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario_roles` (
  `id_usuario_rol` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `id_rol` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario_rol`),
  UNIQUE KEY `uq_usuario_roles_usuario_rol` (`id_usuario`,`id_rol`),
  KEY `idx_usuario_roles_rol` (`id_rol`),
  CONSTRAINT `fk_usuario_roles_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_usuario_roles_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_roles`
--

LOCK TABLES `usuario_roles` WRITE;
/*!40000 ALTER TABLE `usuario_roles` DISABLE KEYS */;
INSERT INTO `usuario_roles` VALUES (1,1,1,'2026-05-17 22:28:07'),(2,2,3,'2026-05-17 22:28:08'),(3,3,2,'2026-05-17 22:28:08'),(4,4,4,'2026-05-17 22:28:08'),(5,5,5,'2026-05-17 22:28:08'),(6,6,6,'2026-05-17 22:28:09');
/*!40000 ALTER TABLE `usuario_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id_usuario` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contexto` bigint(20) unsigned NOT NULL,
  `id_contacto_empresa` bigint(20) unsigned DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(150) DEFAULT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `email` varchar(180) NOT NULL,
  `email_recuperacion` varchar(180) DEFAULT NULL,
  `email_verificado_at` datetime DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `avatar_key` varchar(32) NOT NULL DEFAULT 'avatar-ciete-logo',
  `remember_token` varchar(100) DEFAULT NULL,
  `ultimo_login_at` datetime DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `interface_mode` enum('ciete_excel','ciete_moderno') NOT NULL DEFAULT 'ciete_moderno',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uq_usuarios_contexto_nombre_usuario` (`id_contexto`,`nombre_usuario`),
  UNIQUE KEY `uq_usuarios_email` (`email`),
  KEY `idx_usuarios_contexto` (`id_contexto`),
  KEY `idx_usuarios_id_contexto` (`id_usuario`,`id_contexto`),
  KEY `idx_usuarios_contacto_empresa_contexto` (`id_contacto_empresa`,`id_contexto`),
  CONSTRAINT `fk_usuarios_contacto_empresa_contexto` FOREIGN KEY (`id_contacto_empresa`, `id_contexto`) REFERENCES `contactos_empresas` (`id_contacto_empresa`, `id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_usuarios_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,3,NULL,'Administrador','ERP CIETE','admin','admin@ciete.es',NULL,'2026-05-17 22:28:07','$2y$12$7MVKYzq16yNkJaaO2vZcUe66.7I/qJgoca8t5bVFjeY2dImdFzgOy',NULL,'avatar-ciete-logo',NULL,NULL,1,'ciete_moderno','2026-05-17 20:28:07','2026-05-17 20:28:07'),(2,3,NULL,'Cesar','CIETE','cesar','cesar@ciete.es',NULL,'2026-05-17 22:28:07','$2y$12$cdAvzv9SkBAi/f8BcKWJZODDYyBBXM3c04FamZgzO4c8tMwY81AfK',NULL,'avatar-ciete-logo',NULL,NULL,1,'ciete_excel','2026-05-17 20:28:08','2026-05-17 20:28:08'),(3,3,NULL,'Usuario','Ejecucion','usuario','usuario@ciete.es',NULL,'2026-05-17 22:28:08','$2y$12$wGjVklEkf2zqKChK9B4ijOQlMhVJth/vQLo4zYdVfW8/ygxi5KMlW',NULL,'avatar-ciete-logo',NULL,NULL,1,'ciete_excel','2026-05-17 20:28:08','2026-05-17 20:28:08'),(4,1,NULL,'Ejecucion','Moeve','moeve','moeve@ciete.es',NULL,'2026-05-17 22:28:08','$2y$12$c6DpQB2dr0qOdXLLVZ83gOc8gccjPDUetASBzk6g.Oyxxk3sbRH8y',NULL,'avatar-ciete-logo',NULL,NULL,1,'ciete_excel','2026-05-17 20:28:08','2026-05-17 20:28:08'),(5,2,NULL,'Ejecucion','Repsol','repsol','repsol@ciete.es',NULL,'2026-05-17 22:28:08','$2y$12$/uSEhKL40BOvLjx8mwLvo.vQAYliZ66G.utQUNJXZvMxr4FDnPXi6',NULL,'avatar-ciete-logo',NULL,NULL,1,'ciete_excel','2026-05-17 20:28:08','2026-05-17 20:28:08'),(6,3,NULL,'Usuario','Contable','contable','contable@ciete.es',NULL,'2026-05-17 22:28:08','$2y$12$1WQ5z947S6RyZR7qSMSHie2k58h1cqAl4mkxrNwOD5Ci.GpQw2akC',NULL,'avatar-ciete-logo',NULL,'2026-05-18 06:54:11',1,'ciete_excel','2026-05-17 20:28:09','2026-05-18 04:54:11');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'abaco_ciete'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-18  9:22:52
