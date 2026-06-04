-- ============================================================
-- ERP CIETE — Dump completo base de datos demo
-- Versión: v2.2.0
-- Fecha: 2026-06-04
-- Base: abaco_ciete (MariaDB 10.4 / MySQL 8 compatible)
-- ============================================================
--
-- Incluye: estructura + datos demo MOEVE, REPSOL y OTROS.
-- Usuarios demo: cesar@ciete.es, contable@ciete.es, admin@ciete.es,
--                usuario@ciete.es, moeve@ciete.es (contraseñas en docs/00_ENTREGA_FINAL/01_INSTALACION_LOCAL.md)
--
-- Cómo restaurar:
--   mysql -u root < database/schema/abaco_ciete_v220_2026-06-04.sql
--
-- Alternativa (migraciones limpias desde cero):
--   php artisan migrate
--   php artisan db:seed
-- ============================================================
--
-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: abaco_ciete
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
-- Current Database: `abaco_ciete`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `abaco_ciete` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `abaco_ciete`;

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
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` VALUES (1,1,6,'cambiar_contexto','usuarios','contexto','App\\Models\\User',6,6,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-17 20:49:48'),(2,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-18 05:52:47'),(3,1,2,'actualizar','trabajos','trabajos','App\\Models\\Trabajo',5,5,'id_estacion_servicio','2','1',NULL,NULL,'El usuario modifico la estacion del trabajo 910005.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-18 05:53:28'),(4,1,2,'actualizar','trabajos','trabajos','App\\Models\\Trabajo',5,5,'id_estacion_servicio','1','2',NULL,NULL,'El usuario modifico la estacion del trabajo 910005.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-18 05:53:31'),(5,1,2,'actualizar','trabajos','trabajos','App\\Models\\Trabajo',5,5,'id_estacion_servicio','2','1',NULL,NULL,'El usuario modifico la estacion del trabajo 910005.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-18 05:53:34'),(6,1,2,'actualizar','trabajos','trabajos','App\\Models\\Trabajo',5,5,'id_estacion_servicio','1','2',NULL,NULL,'El usuario modifico la estacion del trabajo 910005.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-18 05:53:36'),(7,1,2,'actualizar','pedidos','pedidos','App\\Models\\Pedido',1,1,'id_trabajo','1','5',NULL,NULL,'El usuario reasigno el pedido P-FD26-MOE-001 al trabajo 910005 desde la vista Excel.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-18 05:55:21'),(8,2,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','1','2','{\"contexto\":1}','{\"contexto\":2}','Cambio de contexto activo: MOEVE -> REPSOL.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-18 06:00:14'),(9,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','2','1','{\"contexto\":2}','{\"contexto\":1}','Cambio de contexto activo: REPSOL -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-18 06:00:30'),(10,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','::1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36','2026-05-18 15:05:22'),(11,2,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','1','2','{\"contexto\":1}','{\"contexto\":2}','Cambio de contexto activo: MOEVE -> REPSOL.','::1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36','2026-05-18 15:05:53'),(12,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','2','1','{\"contexto\":2}','{\"contexto\":1}','Cambio de contexto activo: REPSOL -> MOEVE.','::1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36','2026-05-18 15:17:28'),(13,3,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','1','3','{\"contexto\":1}','{\"contexto\":3}','Cambio de contexto activo: MOEVE -> OTROS CLIENTES.','::1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36','2026-05-18 15:17:29'),(14,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','::1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36','2026-05-18 15:17:30'),(15,2,1,'cambiar_contexto','usuarios','contexto','App\\Models\\User',1,1,'id_contexto','3','2','{\"contexto\":3}','{\"contexto\":2}','Cambio de contexto activo: OTROS CLIENTES -> REPSOL.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-18 19:11:55'),(16,2,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','3','2','{\"contexto\":3}','{\"contexto\":2}','Cambio de contexto activo: OTROS CLIENTES -> REPSOL.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-18 19:13:22'),(17,2,2,'actualizar','trabajos','trabajos','App\\Models\\Trabajo',12,12,'id_tipo_trabajo','5','4',NULL,NULL,'El usuario modifico la categorizacion REPSOL del trabajo 920003.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-18 19:15:04'),(18,2,2,'actualizar','trabajos','trabajos','App\\Models\\Trabajo',12,12,'id_tipo_trabajo','4','5',NULL,NULL,'El usuario modifico la categorizacion REPSOL del trabajo 920003.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-18 19:15:06'),(20,NULL,1,'importar','importaciones','importaciones','App\\Services\\Importacion\\CieteExcelImportService',NULL,NULL,NULL,NULL,'P1-13',NULL,'{\"files\":[\"C:/Users/kampe/Documents/Abaco/excelsactualizados/Mapeo_Moeve_Repsol_Envolvente.xlsx\",\"C:/Users/kampe/Documents/Abaco/excelsactualizados/Moeve/01 Control de Trabajos Moeve.xlsx\",\"C:/Users/kampe/Documents/Abaco/excelsactualizados/Moeve/02 Listado EESS España y Portugal 16-03-26.xlsx\",\"C:/Users/kampe/Documents/Abaco/excelsactualizados/Repsol/01 Control Trabajos DISEÑO REPSOL.xlsx\",\"C:/Users/kampe/Documents/Abaco/excelsactualizados/Repsol/02 Control Trabajos EDIFICACIÓN.xlsx\",\"C:/Users/kampe/Documents/Abaco/excelsactualizados/Repsol/03 Control Trabajos OBRAS REPSOL Z10.xlsx\",\"C:/Users/kampe/Documents/Abaco/excelsactualizados/Repsol/03 Control Trabajos OBRAS REPSOL Z50.xlsx\",\"C:/Users/kampe/Documents/Abaco/excelsactualizados/Repsol/05 Control Trabajos LICENCIAS REPSOL.xlsx\",\"C:/Users/kampe/Documents/Abaco/excelsactualizados/Repsol/09 Control Trabajos FV REPSOL.xlsx\",\"C:/Users/kampe/Documents/Abaco/excelsactualizados/Repsol/10 Control Trabajos ESTRUCTURAS Y VERTIDOS REPSOL.xlsx\",\"C:/Users/kampe/Documents/Abaco/excelsactualizados/Repsol/12 Control Trabajos MTO REPSOL.xlsx\",\"C:/Users/kampe/Documents/Abaco/excelsactualizados/Repsol/13 Control Trabajos PUNTOS DE RECARGA.xlsx\"],\"rows_seen\":53055,\"rows_imported\":51288,\"rows_ignored\":1767,\"rows_with_warning\":3726,\"rows_with_error\":0,\"imported\":{\"contextos\":0,\"empresas\":2,\"estaciones\":6584,\"contratos\":4,\"tarifarios\":5,\"tarifario_lineas\":425,\"trabajos\":11614,\"pedidos\":9685,\"pedido_items\":9701,\"facturas\":2733,\"factura_items\":9194,\"contrato_empresas_facturadoras\":4},\"files_count\":12,\"top_warning_codes\":{\"historical_invoice_without_items\":158,\"work_amount_without_order\":91,\"file_without_context\":1},\"top_warning_classes\":{\"do_not_invent\":158,\"functional_decision\":91,\"acceptable\":1},\"top_error_codes\":[]}','[p1-12-excel-real] Importación real controlada desde Excel actualizados.',NULL,NULL,NULL,'2026-05-18 23:35:09'),(21,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36','2026-05-18 23:36:59'),(22,2,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','1','2','{\"contexto\":1}','{\"contexto\":2}','Cambio de contexto activo: MOEVE -> REPSOL.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36','2026-05-18 23:38:23'),(23,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','2','1','{\"contexto\":2}','{\"contexto\":1}','Cambio de contexto activo: REPSOL -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36','2026-05-18 23:41:23'),(24,3,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','1','3','{\"contexto\":1}','{\"contexto\":3}','Cambio de contexto activo: MOEVE -> OTROS CLIENTES.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36','2026-05-18 23:41:37'),(25,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36','2026-05-18 23:41:43'),(26,1,6,'cambiar_contexto','usuarios','contexto','App\\Models\\User',6,6,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 00:02:39'),(27,2,6,'cambiar_contexto','usuarios','contexto','App\\Models\\User',6,6,'id_contexto','1','2','{\"contexto\":1}','{\"contexto\":2}','Cambio de contexto activo: MOEVE -> REPSOL.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 00:03:00'),(28,2,6,'cambiar_contexto','usuarios','contexto','App\\Models\\User',6,6,'id_contexto','3','2','{\"contexto\":3}','{\"contexto\":2}','Cambio de contexto activo: OTROS CLIENTES -> REPSOL.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 03:04:35'),(29,2,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','3','2','{\"contexto\":3}','{\"contexto\":2}','Cambio de contexto activo: OTROS CLIENTES -> REPSOL.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 03:19:52'),(30,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 17:13:12'),(31,1,2,'actualizar','pedidos','pedidos','App\\Models\\Pedido',15829,15829,'id_trabajo','18253','18258',NULL,NULL,'El usuario reasigno el pedido 600034631 al trabajo 6629 desde la vista Excel.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 17:15:06'),(32,1,2,'actualizar','pedidos','pedidos','App\\Models\\Pedido',15832,15832,'id_trabajo','18259','18258',NULL,NULL,'El usuario reasigno el pedido 300152230 al trabajo 6629 desde la vista Excel.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-28 17:15:17'),(33,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-29 05:09:46'),(34,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-01 05:48:44'),(35,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-01 11:50:33'),(36,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-02 06:21:24'),(37,2,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','1','2','{\"contexto\":1}','{\"contexto\":2}','Cambio de contexto activo: MOEVE -> REPSOL.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-02 07:30:19'),(38,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-02 12:50:37'),(39,2,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','1','2','{\"contexto\":1}','{\"contexto\":2}','Cambio de contexto activo: MOEVE -> REPSOL.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-02 12:50:45'),(40,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','2','1','{\"contexto\":2}','{\"contexto\":1}','Cambio de contexto activo: REPSOL -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-02 13:37:54'),(41,1,2,'actualizar','tarifarios','maestros.tarifarios','App\\Models\\Tarifario',36,36,'es_predeterminado','0','1','{\"id_tarifario\":36,\"id_contexto\":1,\"id_contrato\":28,\"nombre\":\"Tarifario 772 MOEVE alternativo\",\"version\":\"2026-demo-alt\",\"fecha_inicio_vigencia\":null,\"fecha_fin_vigencia\":null,\"factor_multiplicador\":\"1.0000\",\"moneda\":\"EUR\",\"observaciones\":\"[demo-integral-reducida-2026-06-02] Tarifario MOEVE alternativo para validar selector.\",\"es_predeterminado\":false,\"activo\":true}','{\"id_tarifario\":36,\"id_contexto\":1,\"id_contrato\":28,\"nombre\":\"Tarifario 772 MOEVE alternativo\",\"version\":\"2026-demo-alt\",\"fecha_inicio_vigencia\":null,\"fecha_fin_vigencia\":null,\"factor_multiplicador\":\"1.0000\",\"moneda\":\"EUR\",\"observaciones\":\"[demo-integral-reducida-2026-06-02] Tarifario MOEVE alternativo para validar selector.\",\"es_predeterminado\":true,\"activo\":true}','Tarifario marcado como predeterminado para el contrato.. Campos modificados: es_predeterminado.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-02 13:48:10'),(42,1,2,'actualizar','tarifarios','maestros.tarifarios','App\\Models\\Tarifario',35,35,'es_predeterminado','0','1','{\"id_tarifario\":35,\"id_contexto\":1,\"id_contrato\":28,\"nombre\":\"Tarifario 772 MOEVE\",\"version\":\"2026-demo\",\"fecha_inicio_vigencia\":null,\"fecha_fin_vigencia\":null,\"factor_multiplicador\":\"1.0000\",\"moneda\":\"EUR\",\"observaciones\":\"[demo-integral-reducida-2026-06-02] Tarifario MOEVE principal.\",\"es_predeterminado\":false,\"activo\":true}','{\"id_tarifario\":35,\"id_contexto\":1,\"id_contrato\":28,\"nombre\":\"Tarifario 772 MOEVE\",\"version\":\"2026-demo\",\"fecha_inicio_vigencia\":null,\"fecha_fin_vigencia\":null,\"factor_multiplicador\":\"1.0000\",\"moneda\":\"EUR\",\"observaciones\":\"[demo-integral-reducida-2026-06-02] Tarifario MOEVE principal.\",\"es_predeterminado\":true,\"activo\":true}','Tarifario marcado como predeterminado para el contrato.. Campos modificados: es_predeterminado.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-02 13:48:11'),(43,1,2,'actualizar','tarifarios','maestros.tarifarios','App\\Models\\Tarifario',36,36,'es_predeterminado','0','1','{\"id_tarifario\":36,\"id_contexto\":1,\"id_contrato\":28,\"nombre\":\"Tarifario 772 MOEVE alternativo\",\"version\":\"2026-demo-alt\",\"fecha_inicio_vigencia\":null,\"fecha_fin_vigencia\":null,\"factor_multiplicador\":\"1.0000\",\"moneda\":\"EUR\",\"observaciones\":\"[demo-integral-reducida-2026-06-02] Tarifario MOEVE alternativo para validar selector.\",\"es_predeterminado\":false,\"activo\":true}','{\"id_tarifario\":36,\"id_contexto\":1,\"id_contrato\":28,\"nombre\":\"Tarifario 772 MOEVE alternativo\",\"version\":\"2026-demo-alt\",\"fecha_inicio_vigencia\":null,\"fecha_fin_vigencia\":null,\"factor_multiplicador\":\"1.0000\",\"moneda\":\"EUR\",\"observaciones\":\"[demo-integral-reducida-2026-06-02] Tarifario MOEVE alternativo para validar selector.\",\"es_predeterminado\":true,\"activo\":true}','Tarifario marcado como predeterminado para el contrato.. Campos modificados: es_predeterminado.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-02 13:48:22'),(44,1,2,'actualizar','tarifarios','maestros.tarifarios','App\\Models\\Tarifario',35,35,'es_predeterminado','0','1','{\"id_tarifario\":35,\"id_contexto\":1,\"id_contrato\":28,\"nombre\":\"Tarifario 772 MOEVE\",\"version\":\"2026-demo\",\"fecha_inicio_vigencia\":null,\"fecha_fin_vigencia\":null,\"factor_multiplicador\":\"1.0000\",\"moneda\":\"EUR\",\"observaciones\":\"[demo-integral-reducida-2026-06-02] Tarifario MOEVE principal.\",\"es_predeterminado\":false,\"activo\":true}','{\"id_tarifario\":35,\"id_contexto\":1,\"id_contrato\":28,\"nombre\":\"Tarifario 772 MOEVE\",\"version\":\"2026-demo\",\"fecha_inicio_vigencia\":null,\"fecha_fin_vigencia\":null,\"factor_multiplicador\":\"1.0000\",\"moneda\":\"EUR\",\"observaciones\":\"[demo-integral-reducida-2026-06-02] Tarifario MOEVE principal.\",\"es_predeterminado\":true,\"activo\":true}','Tarifario marcado como predeterminado para el contrato.. Campos modificados: es_predeterminado.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-02 13:48:31'),(45,2,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','1','2','{\"contexto\":1}','{\"contexto\":2}','Cambio de contexto activo: MOEVE -> REPSOL.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-02 13:52:25'),(46,2,2,'actualizar','tarifarios','maestros.tarifarios','App\\Models\\Tarifario',38,38,'es_predeterminado','0','1','{\"id_tarifario\":38,\"id_contexto\":2,\"id_contrato\":29,\"nombre\":\"TARIFA 23-27 REPSOL alternativa\",\"version\":\"2023-2027-demo-alt\",\"fecha_inicio_vigencia\":null,\"fecha_fin_vigencia\":null,\"factor_multiplicador\":\"1.0000\",\"moneda\":\"EUR\",\"observaciones\":\"[demo-integral-reducida-2026-06-02] Tarifario REPSOL alternativo para validar selector.\",\"es_predeterminado\":false,\"activo\":true}','{\"id_tarifario\":38,\"id_contexto\":2,\"id_contrato\":29,\"nombre\":\"TARIFA 23-27 REPSOL alternativa\",\"version\":\"2023-2027-demo-alt\",\"fecha_inicio_vigencia\":null,\"fecha_fin_vigencia\":null,\"factor_multiplicador\":\"1.0000\",\"moneda\":\"EUR\",\"observaciones\":\"[demo-integral-reducida-2026-06-02] Tarifario REPSOL alternativo para validar selector.\",\"es_predeterminado\":true,\"activo\":true}','Tarifario marcado como predeterminado para el contrato.. Campos modificados: es_predeterminado.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-02 13:52:33'),(47,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.123.0 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36','2026-06-04 06:28:33'),(48,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 08:57:40'),(49,1,2,'cambiar_estado','trabajos','cierre','App\\Models\\Trabajo',23296,23296,'estado','facturado','finalizado','{\"id_trabajo\":23296,\"id_contexto\":1,\"id_contrato\":28,\"id_tarifario\":35,\"fecha_terminacion\":\"2026-05-22T00:00:00.000000Z\",\"estado\":\"facturado\",\"bloqueado_cierre\":false}','{\"id_trabajo\":23296,\"id_contexto\":1,\"id_contrato\":28,\"id_tarifario\":35,\"fecha_terminacion\":\"2026-05-22T00:00:00.000000Z\",\"estado\":\"finalizado\",\"bloqueado_cierre\":false}','Trabajo finalizado desde panel de cierre.',NULL,NULL,'','2026-06-04 09:01:41'),(50,2,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','1','2','{\"contexto\":1}','{\"contexto\":2}','Cambio de contexto activo: MOEVE -> REPSOL.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 09:03:27'),(51,1,3,'cambiar_contexto','usuarios','contexto','App\\Models\\User',3,3,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 09:06:37'),(52,1,3,'crear','trabajos','trabajos','App\\Models\\Trabajo',23308,23308,'id_trabajo',NULL,'23308',NULL,'{\"id_trabajo\":23308,\"id_contexto\":1,\"id_estacion_servicio\":7014,\"id_tipo_documento\":null,\"id_tipo_trabajo\":null,\"id_contrato\":28,\"id_tarifario\":35,\"id_responsable_ciete\":null,\"numero_trabajo\":610007,\"numero_trabajo_operativo\":\"MOE-610007\",\"descripcion_trabajo\":\"QA-UI-20260604-MOE Trabajo validaci\\u00f3n Playwright\",\"fecha_encargo\":\"2026-06-04T00:00:00.000000Z\",\"fecha_terminacion\":null,\"observaciones\":null,\"numero_aviso\":null,\"estado\":\"en_curso\"}','Alta de trabajo.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 09:08:59'),(53,1,3,'crear','pedidos','pedidos','App\\Models\\Pedido',19417,19417,'id_pedido',NULL,'19417',NULL,'{\"id_pedido\":19417,\"id_contexto\":1,\"id_trabajo\":23308,\"id_tarifario\":35,\"numero_pedido\":\"QA-UI-20260604-PED-MOE\",\"fecha_solicitud\":\"2026-06-04T00:00:00.000000Z\",\"fecha_recepcion\":null,\"importe_pedido\":\"2525.00\",\"importe_solicitado\":\"2525.00\",\"importe_facturado\":\"0.00\",\"unidades_pedido\":\"3.500\",\"unidades_solicitadas\":\"3.500\",\"estado\":\"pendiente\",\"pedido_completo\":true,\"tiene_mas_de_1_item\":true,\"facturado_completo\":false,\"observaciones\":null}','Alta de pedido.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 09:12:45'),(54,1,3,'cambiar_contexto','usuarios','contexto','App\\Models\\User',3,3,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 12:43:20'),(55,1,3,'actualizar','trabajos','trabajos','App\\Models\\Trabajo',23308,23308,'observaciones',NULL,'QA-USUARIO-PRIMER-GUARDADO','{\"id_trabajo\":23308,\"id_contexto\":1,\"id_estacion_servicio\":7014,\"id_tipo_documento\":null,\"id_tipo_trabajo\":null,\"id_contrato\":28,\"id_tarifario\":35,\"id_responsable_ciete\":null,\"numero_trabajo\":610007,\"numero_trabajo_operativo\":\"MOE-610007\",\"descripcion_trabajo\":\"QA-UI-20260604-MOE Trabajo validaci\\u00f3n Playwright\",\"fecha_encargo\":\"2026-06-04T00:00:00.000000Z\",\"fecha_terminacion\":null,\"observaciones\":null,\"numero_aviso\":null,\"estado\":\"en_curso\"}','{\"id_trabajo\":23308,\"id_contexto\":1,\"id_estacion_servicio\":7014,\"id_tipo_documento\":null,\"id_tipo_trabajo\":null,\"id_contrato\":28,\"id_tarifario\":35,\"id_responsable_ciete\":null,\"numero_trabajo\":610007,\"numero_trabajo_operativo\":\"MOE-610007\",\"descripcion_trabajo\":\"QA-UI-20260604-MOE Trabajo validaci\\u00f3n Playwright\",\"fecha_encargo\":\"2026-06-04T00:00:00.000000Z\",\"fecha_terminacion\":null,\"observaciones\":\"QA-USUARIO-PRIMER-GUARDADO\",\"numero_aviso\":null,\"estado\":\"en_curso\"}','Actualización de trabajo. Campos modificados: observaciones.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 12:44:18'),(56,1,4,'actualizar','trabajos','trabajos','App\\Models\\Trabajo',23308,23308,'observaciones','QA-USUARIO-PRIMER-GUARDADO','QA-MOEVE-SEGUNDO-GUARDADO-POR-OTRO-USUARIO','{\"id_trabajo\":23308,\"id_contexto\":1,\"id_estacion_servicio\":7014,\"id_tipo_documento\":null,\"id_tipo_trabajo\":null,\"id_contrato\":28,\"id_tarifario\":35,\"id_responsable_ciete\":null,\"numero_trabajo\":610007,\"numero_trabajo_operativo\":\"MOE-610007\",\"descripcion_trabajo\":\"QA-UI-20260604-MOE Trabajo validaci\\u00f3n Playwright\",\"fecha_encargo\":\"2026-06-04T00:00:00.000000Z\",\"fecha_terminacion\":null,\"observaciones\":\"QA-USUARIO-PRIMER-GUARDADO\",\"numero_aviso\":null,\"estado\":\"en_curso\"}','{\"id_trabajo\":23308,\"id_contexto\":1,\"id_estacion_servicio\":7014,\"id_tipo_documento\":null,\"id_tipo_trabajo\":null,\"id_contrato\":28,\"id_tarifario\":35,\"id_responsable_ciete\":null,\"numero_trabajo\":610007,\"numero_trabajo_operativo\":\"MOE-610007\",\"descripcion_trabajo\":\"QA-UI-20260604-MOE Trabajo validaci\\u00f3n Playwright\",\"fecha_encargo\":\"2026-06-04T00:00:00.000000Z\",\"fecha_terminacion\":null,\"observaciones\":\"QA-MOEVE-SEGUNDO-GUARDADO-POR-OTRO-USUARIO\",\"numero_aviso\":null,\"estado\":\"en_curso\"}','Actualización de trabajo. Campos modificados: observaciones.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 12:45:46'),(57,1,3,'cambiar_contexto','usuarios','contexto','App\\Models\\User',3,3,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 12:46:23'),(58,1,3,'actualizar','trabajos','trabajos','App\\Models\\Trabajo',23308,23308,'observaciones','QA-MOEVE-SEGUNDO-GUARDADO-POR-OTRO-USUARIO','QA-USUARIO-INTENTO-SOBRESCRIBIR-CONFLICTO','{\"id_trabajo\":23308,\"id_contexto\":1,\"id_estacion_servicio\":7014,\"id_tipo_documento\":null,\"id_tipo_trabajo\":null,\"id_contrato\":28,\"id_tarifario\":35,\"id_responsable_ciete\":null,\"numero_trabajo\":610007,\"numero_trabajo_operativo\":\"MOE-610007\",\"descripcion_trabajo\":\"QA-UI-20260604-MOE Trabajo validaci\\u00f3n Playwright\",\"fecha_encargo\":\"2026-06-04T00:00:00.000000Z\",\"fecha_terminacion\":null,\"observaciones\":\"QA-MOEVE-SEGUNDO-GUARDADO-POR-OTRO-USUARIO\",\"numero_aviso\":null,\"estado\":\"en_curso\"}','{\"id_trabajo\":23308,\"id_contexto\":1,\"id_estacion_servicio\":7014,\"id_tipo_documento\":null,\"id_tipo_trabajo\":null,\"id_contrato\":28,\"id_tarifario\":35,\"id_responsable_ciete\":null,\"numero_trabajo\":610007,\"numero_trabajo_operativo\":\"MOE-610007\",\"descripcion_trabajo\":\"QA-UI-20260604-MOE Trabajo validaci\\u00f3n Playwright\",\"fecha_encargo\":\"2026-06-04T00:00:00.000000Z\",\"fecha_terminacion\":null,\"observaciones\":\"QA-USUARIO-INTENTO-SOBRESCRIBIR-CONFLICTO\",\"numero_aviso\":null,\"estado\":\"en_curso\"}','Actualización de trabajo. Campos modificados: observaciones.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 12:46:56'),(59,1,3,'actualizar','trabajos','trabajos','App\\Models\\Trabajo',23308,23308,'observaciones','QA-USUARIO-INTENTO-SOBRESCRIBIR-CONFLICTO','QA-USUARIO-INTENTO-MODAL-INLINE',NULL,NULL,'El usuario modifico las observaciones del trabajo 610007.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 12:49:42'),(60,1,4,'actualizar','trabajos','trabajos','App\\Models\\Trabajo',23308,23308,'observaciones','QA-USUARIO-INTENTO-MODAL-INLINE','QA-MOEVE-MODAL-ULTIMO-GUARDADO',NULL,NULL,'El usuario modifico las observaciones del trabajo 610007.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 12:51:40'),(61,1,3,'cambiar_contexto','usuarios','contexto','App\\Models\\User',3,3,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 12:52:09'),(62,1,3,'actualizar','trabajos','trabajos','App\\Models\\Trabajo',23308,23308,'observaciones','QA-MOEVE-MODAL-ULTIMO-GUARDADO','QA-USUARIO-SOBRESCRIBIENDO-TEXTO-DE-MOEVE',NULL,NULL,'El usuario modifico las observaciones del trabajo 610007.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 12:53:13'),(63,1,6,'cambiar_contexto','usuarios','contexto','App\\Models\\User',6,6,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 12:55:06'),(64,1,6,'crear','factura_items','facturas','App\\Models\\FacturaItem',18408,18408,'id_factura_item',NULL,'18408',NULL,'{\"id_factura_item\":18408,\"id_factura\":5486,\"id_pedido_item\":19472,\"unidades_facturadas\":\"1.000\",\"importe_facturado\":\"790.00\",\"observaciones\":null}','Crear de linea de factura 18408 en factura 5486.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 12:58:24'),(65,1,6,'crear','factura_items','facturas','App\\Models\\FacturaItem',18409,18409,'id_factura_item',NULL,'18409',NULL,'{\"id_factura_item\":18409,\"id_factura\":5486,\"id_pedido_item\":19470,\"unidades_facturadas\":\"1.000\",\"importe_facturado\":\"570.00\",\"observaciones\":null}','Crear de linea de factura 18409 en factura 5486.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 12:58:24'),(66,1,6,'crear','facturas','facturas','App\\Models\\Factura',5486,5486,'id_factura',NULL,'5486',NULL,'{\"id_factura\":5486,\"id_contexto\":1,\"id_trabajo\":23293,\"id_contrato\":28,\"id_empresa_cliente\":20,\"id_empresa_facturadora\":20,\"numero_factura\":\"QA-FAC-20260604-PARCIAL\",\"numero_factura_ccp\":\"QA-CCP-20260604\",\"serie\":null,\"orden_factura\":1,\"fecha_solicitud\":null,\"fecha_emision\":\"2026-06-04T00:00:00.000000Z\",\"fecha_vencimiento\":null,\"importe\":\"1360.00\",\"base_imponible\":\"1360.00\",\"iva\":\"0.00\",\"retencion\":\"0.00\",\"total\":\"1360.00\",\"estado\":\"pendiente\",\"autofactura\":false,\"sociedad\":\"MOEVE ES\",\"observaciones\":null}','Alta de factura. Items: 2 creados. Importe asignado: 1360.00.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 12:58:24'),(67,1,6,'cambiar_estado','facturas','facturas','App\\Models\\Factura',5486,5486,'estado','pendiente','anulada','{\"id_factura\":5486,\"id_contexto\":1,\"id_trabajo\":23293,\"id_contrato\":28,\"id_empresa_cliente\":20,\"id_empresa_facturadora\":20,\"numero_factura\":\"QA-FAC-20260604-PARCIAL\",\"numero_factura_ccp\":\"QA-CCP-20260604\",\"serie\":null,\"orden_factura\":1,\"fecha_solicitud\":null,\"fecha_emision\":\"2026-06-04T00:00:00.000000Z\",\"fecha_vencimiento\":null,\"importe\":\"1360.00\",\"base_imponible\":\"1360.00\",\"iva\":\"0.00\",\"retencion\":\"0.00\",\"total\":\"1360.00\",\"estado\":\"pendiente\",\"autofactura\":false,\"sociedad\":\"MOEVE ES\",\"observaciones\":null}','{\"id_factura\":5486,\"id_contexto\":1,\"id_trabajo\":23293,\"id_contrato\":28,\"id_empresa_cliente\":20,\"id_empresa_facturadora\":20,\"numero_factura\":\"QA-FAC-20260604-PARCIAL\",\"numero_factura_ccp\":\"QA-CCP-20260604\",\"serie\":null,\"orden_factura\":1,\"fecha_solicitud\":null,\"fecha_emision\":\"2026-06-04T00:00:00.000000Z\",\"fecha_vencimiento\":null,\"importe\":\"1360.00\",\"base_imponible\":\"1360.00\",\"iva\":\"0.00\",\"retencion\":\"0.00\",\"total\":\"1360.00\",\"estado\":\"anulada\",\"autofactura\":false,\"sociedad\":\"MOEVE ES\",\"observaciones\":null}','Factura anulada desde accion de eliminacion restringida.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 13:00:05'),(68,1,1,'cambiar_contexto','usuarios','contexto','App\\Models\\User',1,1,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 13:05:00'),(69,1,4,'exportar','pedidos','pedidos','App\\Models\\Pedido',19411,19411,NULL,NULL,NULL,NULL,'{\"id_pedido\":19411,\"numero_pedido\":\"DEMO-MOE-LA-SENYERA\",\"formato\":\"HTML imprimible Moeve\"}','Exportacion HTML imprimible Moeve del pedido DEMO-MOE-LA-SENYERA.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 13:08:46'),(70,1,4,'exportar','pedidos','pedidos','App\\Models\\Pedido',19411,19411,NULL,NULL,NULL,NULL,'{\"id_pedido\":19411,\"numero_pedido\":\"DEMO-MOE-LA-SENYERA\",\"formato\":\"CSV Moeve\"}','Exportacion CSV Moeve del pedido DEMO-MOE-LA-SENYERA.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 13:09:25'),(71,1,4,'exportar','pedidos','pedidos','App\\Models\\Pedido',19411,19411,NULL,NULL,NULL,NULL,'{\"id_pedido\":19411,\"numero_pedido\":\"DEMO-MOE-LA-SENYERA\",\"formato\":\"Cuadro ARIBA\"}','Exportacion Cuadro ARIBA del pedido DEMO-MOE-LA-SENYERA.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 13:09:46'),(72,1,4,'exportar','pedidos','pedidos','App\\Models\\Pedido',19411,19411,NULL,NULL,NULL,NULL,'{\"id_pedido\":19411,\"numero_pedido\":\"DEMO-MOE-LA-SENYERA\",\"formato\":\"CSV Moeve\"}','Exportacion CSV Moeve del pedido DEMO-MOE-LA-SENYERA.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 13:31:18'),(73,1,1,'cambiar_contexto','usuarios','contexto','App\\Models\\User',1,1,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 13:37:46'),(74,1,4,'actualizar','trabajos','trabajos','App\\Models\\Trabajo',23308,23308,'observaciones','QA-USUARIO-SOBRESCRIBIENDO-TEXTO-DE-MOEVE','QA-MOEVE-GUARDADO-PARA-TEST-CONCURRENCIA',NULL,NULL,'El usuario modifico las observaciones del trabajo 610007.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 13:41:35'),(75,1,3,'cambiar_contexto','usuarios','contexto','App\\Models\\User',3,3,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 13:43:36'),(76,1,2,'cambiar_contexto','usuarios','contexto','App\\Models\\User',2,2,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 14:29:42'),(77,1,2,'actualizar','contratos','maestros.contratos','App\\Models\\Contrato',28,28,NULL,NULL,NULL,'{\"id_contrato\":28,\"id_contexto\":1,\"id_empresa_cliente\":20,\"codigo_contrato\":\"772\",\"nombre\":\"Contrato 772 MOEVE\",\"tipo\":\"marco\",\"fecha_inicio\":null,\"fecha_fin\":null,\"estado\":\"vigente\",\"observaciones\":\"[demo-integral-reducida-2026-06-02] Contrato principal MOEVE.\",\"activo\":true}','{\"id_contrato\":28,\"id_contexto\":1,\"id_empresa_cliente\":20,\"codigo_contrato\":\"772\",\"nombre\":\"Contrato 772 MOEVE\",\"tipo\":\"marco\",\"fecha_inicio\":null,\"fecha_fin\":null,\"estado\":\"vigente\",\"observaciones\":\"[demo-integral-reducida-2026-06-02] Contrato principal MOEVE.\",\"activo\":true}','Actualizacion de contrato maestro sin cambios persistidos.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 14:30:40'),(78,1,2,'exportar','pedidos','pedidos','App\\Models\\Pedido',19411,19411,NULL,NULL,NULL,NULL,'{\"id_pedido\":19411,\"numero_pedido\":\"DEMO-MOE-LA-SENYERA\",\"formato\":\"Cuadro ARIBA\"}','Exportacion Cuadro ARIBA del pedido DEMO-MOE-LA-SENYERA.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 14:30:54'),(79,1,2,'actualizar','contratos','maestros.contratos','App\\Models\\Contrato',28,28,NULL,NULL,NULL,'{\"id_contrato\":28,\"id_contexto\":1,\"id_empresa_cliente\":20,\"codigo_contrato\":\"772\",\"nombre\":\"Contrato 772 MOEVE\",\"tipo\":\"marco\",\"fecha_inicio\":null,\"fecha_fin\":null,\"estado\":\"vigente\",\"observaciones\":\"[demo-integral-reducida-2026-06-02] Contrato principal MOEVE.\",\"activo\":true}','{\"id_contrato\":28,\"id_contexto\":1,\"id_empresa_cliente\":20,\"codigo_contrato\":\"772\",\"nombre\":\"Contrato 772 MOEVE\",\"tipo\":\"marco\",\"fecha_inicio\":null,\"fecha_fin\":null,\"estado\":\"vigente\",\"observaciones\":\"[demo-integral-reducida-2026-06-02] Contrato principal MOEVE.\",\"activo\":true}','Actualizacion de contrato maestro sin cambios persistidos.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 14:33:36'),(80,1,2,'exportar','pedidos','pedidos','App\\Models\\Pedido',19411,19411,NULL,NULL,NULL,NULL,'{\"id_pedido\":19411,\"numero_pedido\":\"DEMO-MOE-LA-SENYERA\",\"formato\":\"Cuadro ARIBA\"}','Exportacion Cuadro ARIBA del pedido DEMO-MOE-LA-SENYERA.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 14:33:59'),(81,3,6,'exportar','facturas','facturas','facturas',NULL,NULL,NULL,NULL,NULL,NULL,'{\"ids\":[],\"filtros\":[]}','Exportacion CSV de 0 factura(s) filtradas.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 14:46:33'),(82,1,6,'cambiar_contexto','usuarios','contexto','App\\Models\\User',6,6,'id_contexto','3','1','{\"contexto\":3}','{\"contexto\":1}','Cambio de contexto activo: OTROS CLIENTES -> MOEVE.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 14:46:54'),(83,1,6,'exportar','facturas','facturas','facturas',NULL,NULL,NULL,NULL,NULL,NULL,'{\"ids\":[],\"filtros\":[]}','Exportacion CSV de 2 factura(s) filtradas.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 14:47:12'),(84,2,3,'cambiar_contexto','usuarios','contexto','App\\Models\\User',3,3,'id_contexto','3','2','{\"contexto\":3}','{\"contexto\":2}','Cambio de contexto activo: OTROS CLIENTES -> REPSOL.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 14:48:13'),(85,2,3,'cambiar_contexto','usuarios','contexto','App\\Models\\User',3,3,'id_contexto','3','2','{\"contexto\":3}','{\"contexto\":2}','Cambio de contexto activo: OTROS CLIENTES -> REPSOL.','127.0.0.1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-04 18:46:40');
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
INSERT INTO `contextos_cliente` VALUES (1,'MOEVE','MOEVE','Contexto operativo importación Excel P1-12',1,'2026-05-17 20:28:07','2026-05-18 23:29:47'),(2,'REPSOL','REPSOL','Contexto operativo importación Excel P1-12',1,'2026-05-17 20:28:07','2026-05-18 23:29:47'),(3,'OTROS CLIENTES','OTROS','Contexto operativo importación Excel P1-12',1,'2026-05-17 20:28:07','2026-05-18 23:29:47');
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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contrato_empresas_facturadoras`
--

LOCK TABLES `contrato_empresas_facturadoras` WRITE;
/*!40000 ALTER TABLE `contrato_empresas_facturadoras` DISABLE KEYS */;
INSERT INTO `contrato_empresas_facturadoras` VALUES (27,28,20,1,1,'[demo-integral-reducida-2026-06-02] Sociedad/CIF MOEVE valida.','2026-06-02 15:42:26','2026-06-02 15:42:26'),(28,29,21,2,1,'[demo-integral-reducida-2026-06-02] Sociedad/CIF REPSOL valida.','2026-06-02 15:42:26','2026-06-02 15:42:26'),(29,30,28,3,1,'[demo-integral-reducida-2026-06-02] Sociedad/CIF OTROS demo valida.','2026-06-02 15:42:26','2026-06-02 15:42:26');
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
  `ariba_cta_mayor` varchar(120) DEFAULT NULL,
  `ariba_propuesta_opex` varchar(120) DEFAULT NULL,
  `ariba_accion_gasto` varchar(120) DEFAULT NULL,
  `ariba_nombre_proveedor` varchar(200) DEFAULT NULL,
  `ariba_sociedad` varchar(120) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contratos`
--

LOCK TABLES `contratos` WRITE;
/*!40000 ALTER TABLE `contratos` DISABLE KEYS */;
INSERT INTO `contratos` VALUES (28,1,20,'772','Contrato 772 MOEVE','marco',NULL,NULL,'vigente','[demo-integral-reducida-2026-06-02] Contrato principal MOEVE.','6330001','OPEX-MOEVE-2026','AC-MOEVE-2026','CIETE INGENIEROS S.A.','MOEVE Energy S.A.',1,'2026-06-02 15:42:26','2026-06-04 14:33:36'),(29,2,21,'REPSOL-2023-2027','Tarifa Repsol 2023-2027','marco',NULL,NULL,'vigente','[demo-integral-reducida-2026-06-02] Contrato principal REPSOL.',NULL,NULL,NULL,NULL,NULL,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(30,3,28,'OTROS-DEMO','Contrato OTROS DEMO','directo',NULL,NULL,'vigente','[demo-integral-reducida-2026-06-02] Contrato OTROS demo.',NULL,NULL,NULL,NULL,NULL,1,'2026-06-02 15:42:26','2026-06-02 15:42:26');
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresas`
--

LOCK TABLES `empresas` WRITE;
/*!40000 ALTER TABLE `empresas` DISABLE KEYS */;
INSERT INTO `empresas` VALUES (20,1,NULL,'MOEVE','MOEVE','Moeve Energy S.A.','A28003119','cliente_proveedor',NULL,'[p1-12-excel-real] Empresa cliente base para importación real.',1,'2026-05-18 23:29:47','2026-06-02 15:42:26'),(21,2,NULL,'REPSOL','REPSOL','Repsol S.A.','A78374725','cliente_proveedor',NULL,'[p1-12-excel-real] Empresa cliente base para importación real.',1,'2026-05-18 23:29:47','2026-06-02 15:42:26'),(28,3,NULL,'OTROS DEMO','OTROS DEMO','OTROS DEMO, S.L.','B90000999','cliente_proveedor',NULL,'[demo-integral-reducida-2026-06-02] Empresa cliente y sociedad facturadora demo.',1,'2026-06-02 15:42:26','2026-06-02 15:42:26');
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
INSERT INTO `estaciones_moeve_ext` VALUES (6592,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:47','2026-05-18 23:32:08'),(6593,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:47','2026-05-18 23:32:05'),(6594,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:47','2026-05-18 23:32:07'),(6595,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:47','2026-05-18 23:32:08'),(6596,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:47','2026-05-18 23:32:04'),(6597,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:47','2026-05-18 23:32:05'),(6598,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:47','2026-05-18 23:29:47'),(6599,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:47','2026-05-18 23:32:08'),(6600,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:48','2026-05-18 23:32:07'),(6601,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:48','2026-05-18 23:32:03'),(6602,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:48','2026-05-18 23:32:06'),(6603,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:48','2026-05-18 23:32:07'),(6604,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:48','2026-05-18 23:32:07'),(6605,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:48','2026-05-18 23:32:07'),(6606,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:48','2026-05-18 23:32:04'),(6607,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:48','2026-05-18 23:32:07'),(6608,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:48','2026-05-18 23:32:07'),(6609,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:29:48','2026-05-18 23:32:10'),(7014,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:30:01','2026-05-18 23:32:09'),(7015,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:30:01','2026-05-18 23:32:10');
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
INSERT INTO `estaciones_repsol_ext` VALUES (9829,'183103373',1630597,NULL,NULL,NULL,NULL,NULL,NULL,'COMBUSTIBLES GUTIERREZ SL','JACINTO MORA PALOMO','CESAR GUTIERREZ DE MIGUEL','925462318',NULL,NULL,NULL,'I','BAÑON PEDRERA, JOSE ANTONIO','2026-05-18 23:32:16','2026-05-18 23:35:03'),(9830,'183056902',2754419,NULL,NULL,NULL,NULL,NULL,NULL,'ELAGAS SL',NULL,'ALFREDO DIEZ MIRALLES','966744012',NULL,NULL,NULL,'D','JIMENEZ MARTINEZ, JUAN JOSE','2026-05-18 23:32:16','2026-05-18 23:33:47'),(9831,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:32:16','2026-05-18 23:32:16'),(9832,'183019017',728611,NULL,NULL,NULL,NULL,NULL,NULL,'CAMPSA ESTACIONES DE SERVICIO SA','GIL SÁNCHEZ , FRANCISCO JESÚS','DAVID BALDOMIR ORGUEIRA','981701300',NULL,NULL,NULL,'D','ESMORIS ANDRADE, ISABEL','2026-05-18 23:32:16','2026-05-18 23:34:16'),(9833,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:32:16','2026-05-18 23:32:16'),(9834,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(9835,'183113091',2645476,NULL,NULL,NULL,NULL,NULL,NULL,'CAMPSA ESTACIONES DE SERVICIO SA','ESTRUCH CARRÉ, MANEL','JOSEFA TEJERO HERENCIA','973243161',NULL,NULL,NULL,'I','ARMENGOL AUMEDES, MARTA','2026-05-18 23:32:17','2026-05-18 23:33:52'),(9836,'183011709',2008316,NULL,NULL,NULL,NULL,NULL,NULL,'HOTEL OVERA S.L.','YESICA NAVARRO','TOMAS NAVARRO ORTEGA-DOMINGO NAVARRO ORTEGA','950134970',NULL,NULL,NULL,'D','VIDAL LOPEZ, MANUEL','2026-05-18 23:32:17','2026-05-18 23:35:00'),(9837,'183054337',2084922,NULL,NULL,NULL,NULL,NULL,NULL,'ESTACIO SERVEI VIURA-GINESTA, S.L.','PERE BUSQUETS CANALETA','DOLORES BERENGUER AULEMS','938472113',NULL,NULL,NULL,'I','SOLE NIUBO, JORDI','2026-05-18 23:32:17','2026-05-18 23:32:55'),(9838,'183001296',813466,NULL,NULL,NULL,NULL,NULL,NULL,'ESTACION DE SERVICIO VALCARCE SL','PATRICIA TEIJON','JOSE MANUEL FERNANDEZ MILHEIRO','667753977',NULL,NULL,NULL,'D','FERNANDEZ RUIZ, PABLO ANDRES','2026-05-18 23:32:17','2026-05-18 23:32:24'),(9839,'183056886',2779053,NULL,NULL,NULL,NULL,NULL,NULL,'CAMPSA ESTACIONES DE SERVICIO SA','DIAZ ALVAREZ, SANDRA','CANDIDO VAZQUEZ CARRILES','985634210',NULL,NULL,NULL,'D','DIAZ GAY, PABLO','2026-05-18 23:32:17','2026-05-18 23:32:57'),(9840,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(9841,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(9842,'183108539',8744063,NULL,NULL,NULL,NULL,NULL,NULL,'CAMPSA ESTACIONES DE SERVICIO SA','BELTRÁN CALVO, ISAAC','JOSE ANGEL DIAZ CESPEDOSA','876768834',NULL,NULL,NULL,'I','SANCHEZ POLO, JESUS ANGEL','2026-05-18 23:32:17','2026-05-18 23:33:28'),(9843,'183111459',924747,NULL,NULL,NULL,NULL,NULL,NULL,'BORRAZ SL','Jon Castaños Guantes','Virginia Castaños Guantes','699914292',NULL,NULL,NULL,'I','COLOMA RUIZ, DIEGO','2026-05-18 23:32:17','2026-05-18 23:33:28'),(9844,'183111442',1150648,NULL,NULL,NULL,NULL,NULL,NULL,'CALVO WALIÑO SL','Ignacio Calvo Waliño','Ignacio Calvo Waliño','924777072',NULL,NULL,NULL,'I','SANCHEZ MILLA, ISIDORO','2026-05-18 23:32:17','2026-05-18 23:33:28'),(9845,'AÑADIDA A LA LISTA POR CIETE',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:32:17','2026-05-18 23:33:01'),(9846,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(9847,'183135045',940752,NULL,NULL,NULL,NULL,NULL,NULL,'CAMPSA ESTACIONES DE SERVICIO SA','MARTINEZ FRAILE, JOSE MARIA','Mª NIEVES NAVARRO CONCEPCION','962129347',NULL,NULL,NULL,'D','SOLER ASSUCENA, JOSE CARLOS','2026-05-18 23:32:17','2026-05-18 23:32:37'),(9848,'183105956',2825650,NULL,NULL,NULL,NULL,NULL,NULL,'DISTRIBUCION GASOLEOS GARCIA-CAMACHO, SL','Natalia Garcia-Camacho Banda','francisco Jose Garcia-Camacho Leal','628388338',NULL,NULL,NULL,'D','SANCHEZ MILLA, ISIDORO','2026-05-18 23:32:17','2026-05-18 23:35:03');
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
) ENGINE=InnoDB AUTO_INCREMENT=13185 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estaciones_servicio`
--

LOCK TABLES `estaciones_servicio` WRITE;
/*!40000 ALTER TABLE `estaciones_servicio` DISABLE KEYS */;
INSERT INTO `estaciones_servicio` VALUES (6592,1,20,'20673','VIRGISA',NULL,NULL,'ALCORCON','MADRID','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:47','2026-05-18 23:32:08'),(6593,1,20,'11496','VIRGEN DE LOS MILAGROS',NULL,NULL,'PALOS DE LA FRONTERA','HUELVA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:47','2026-05-18 23:32:05'),(6594,1,20,'17161','VIRGEN DE LA AURORA',NULL,NULL,'MONTILLA','CORDOBA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:47','2026-05-18 23:32:07'),(6595,1,20,'31592','VILLECA I',NULL,NULL,'LEGANES','MADRID','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:47','2026-05-18 23:32:08'),(6596,1,20,'11205','VILLECA II',NULL,NULL,'LEGANES','MADRID','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:47','2026-05-18 23:32:04'),(6597,1,20,'11624','VIRGEN DE CONSOLACION',NULL,NULL,'ALCALA DE GUADAIRA','SEVILLA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:47','2026-05-18 23:32:05'),(6598,1,20,'31691','Villacastín II',NULL,NULL,'Villacastín','Segovia','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:47','2026-05-18 23:29:47'),(6599,1,20,'20927','VILLABONA',NULL,NULL,'VILLABONA','GUIPUZCOA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:47','2026-05-18 23:32:08'),(6600,1,20,'17157','VILABOA',NULL,NULL,'VILABOA','PONTEVEDRA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:48','2026-05-18 23:32:07'),(6601,1,20,'1356','VENTAS',NULL,NULL,'MADRID','MADRID','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:48','2026-05-18 23:32:03'),(6602,1,20,'14008','VELILLA DE SAN ANTONIO',NULL,NULL,'VELILLA DE SAN ANTONIO','MADRID','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:48','2026-05-18 23:32:06'),(6603,1,20,'17381','LA VEGUILLA II',NULL,NULL,'SAHAGUN DE CAMPOS','LEON','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:48','2026-05-18 23:32:07'),(6604,1,20,'17380','LA VEGUILLA I',NULL,NULL,'SAHAGUN DE CAMPOS','LEON','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:48','2026-05-18 23:32:07'),(6605,1,20,'17714','VALDICIO',NULL,NULL,'MALAGA','MALAGA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:48','2026-05-18 23:32:07'),(6606,1,20,'7055','VALDEVILLA',NULL,NULL,'SEGOVIA','SEGOVIA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:48','2026-05-18 23:32:04'),(6607,1,20,'17018','TRUJILLO-II',NULL,NULL,'TRUJILLO','CACERES','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:48','2026-05-18 23:32:07'),(6608,1,20,'17017','TRUJILLO-I',NULL,NULL,'TRUJILLO','CACERES','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:48','2026-05-18 23:32:07'),(6609,1,20,'34040','TRUJILLO',NULL,NULL,'TRUJILLO','CACERES','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:29:48','2026-05-18 23:32:10'),(7014,1,20,'33450','LA SENYERA I',NULL,NULL,'CUART DE POBLET','VALENCIA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:30:01','2026-05-18 23:32:09'),(7015,1,20,'37190','LA SENYERA II',NULL,NULL,'CUART DE POBLET','VALENCIA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:30:01','2026-05-18 23:32:10'),(9829,2,21,'96322','CRED A. DE LA MIEL M.I.',NULL,NULL,'TORREMOLINOS','MALAGA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:16','2026-05-18 23:33:52'),(9830,2,21,'7630','E.S. ZOTAJO Y GAS S.L.',NULL,NULL,'LORA DEL RIO','SEVILLA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:16','2026-05-18 23:33:47'),(9831,2,21,'NPV','NPV MONCADA',NULL,NULL,'MONCADA','VALENCIA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:16','2026-05-18 23:33:55'),(9832,2,21,'15451','CRED LA TALAIA',NULL,NULL,'TEIA','BARCELONA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:16','2026-05-18 23:32:16'),(9833,2,21,'15452','Cred Teia',NULL,NULL,'TEIA','BARCELONA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:16','2026-05-18 23:34:09'),(9834,2,21,'0','E.S. CAMPUS REPSOL',NULL,NULL,'MADRID','MADRID','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:17','2026-05-18 23:34:49'),(9835,2,21,'96764','CRED PTO STA.MARIA -M.I.',NULL,NULL,'PUERTO DE SANTA MARIA, EL','CADIZ','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:17','2026-05-18 23:33:52'),(9836,2,21,'33126','ES MYKE 22 SL',NULL,NULL,'MANISES','VALENCIA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(9837,2,21,'4953','E.S. AGUILAR',NULL,NULL,'MORELLA','CASTELLON','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(9838,2,21,'5838','E.S. EL EMPERADOR',NULL,NULL,'JARAIZ DE LA VERA','CACERES','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(9839,2,21,'15643','E.S. TOGOROSA, S.L.',NULL,NULL,'ABEJAR','SORIA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(9840,2,21,'95895','OCTANOS',NULL,NULL,'DOS HERMANAS','SEVILLA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(9841,2,21,'98669','PUNTO OCTANOS',NULL,NULL,'DOS HERMANAS','SEVILLA','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(9842,2,21,'96555','E.S. AREA DE SERVICIO LOS CERRILLOS',NULL,NULL,'PERALEDA DE LA MATA','CACERES','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(9843,2,21,'96671','CRED AREA Sº LA ATALAYA M.D.',NULL,NULL,'VILLAVICIOSA DE ODON','MADRID','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(9844,2,21,'96670','CRED AREA Sº LA ATALAYA M.I.',NULL,NULL,'VILLAVICIOSA DE ODON','MADRID','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(9845,2,21,'92286','FOOTWORK',NULL,NULL,'ELCHE','ALICANTE','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:17','2026-05-18 23:32:54'),(9846,2,21,'95507','FLORIN SUVIGAS',NULL,NULL,'CHICLANA DE LA FRONTERA','CADIZ','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(9847,2,21,'97142','CRED IBERUM',NULL,NULL,'ILLESCAS','TOLEDO','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(9848,2,21,'96381','E.S. PROYECTO INSERTIUM S.L.',NULL,NULL,'COSLADA','MADRID','Espana',NULL,NULL,NULL,NULL,'[p1-12-excel-real] Estación importada/actualizada desde Excel real.',1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(13183,3,28,'OTR-001','OTROS DEMO CENTRO','Calle Demo 1','28001','MADRID','MADRID','Espana',NULL,NULL,'activa',NULL,'[demo-integral-reducida-2026-06-02] Estacion OTROS demo.',1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(13184,3,28,'OTR-002','OTROS DEMO NORTE','Calle Demo 2','48001','BILBAO','BIZKAIA','Espana',NULL,NULL,'activa',NULL,'[demo-integral-reducida-2026-06-02] Estacion OTROS demo.',1,'2026-06-02 15:42:26','2026-06-02 15:42:26');
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
) ENGINE=InnoDB AUTO_INCREMENT=18410 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `factura_items`
--

LOCK TABLES `factura_items` WRITE;
/*!40000 ALTER TABLE `factura_items` DISABLE KEYS */;
INSERT INTO `factura_items` VALUES (18406,5484,19466,1.000,510.00,'[demo-integral-reducida-2026-06-02] Facturacion controlada 165023','2026-06-02 15:42:26','2026-06-02 15:42:26'),(18407,5485,19478,1.000,200.00,'[demo-integral-reducida-2026-06-02] Facturacion controlada 3012704','2026-06-02 15:42:26','2026-06-02 15:42:26'),(18408,5486,19472,1.000,790.00,NULL,'2026-06-04 12:58:24','2026-06-04 12:58:24'),(18409,5486,19470,1.000,570.00,NULL,'2026-06-04 12:58:24','2026-06-04 12:58:24');
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
) ENGINE=InnoDB AUTO_INCREMENT=5487 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facturas`
--

LOCK TABLES `facturas` WRITE;
/*!40000 ALTER TABLE `facturas` DISABLE KEYS */;
INSERT INTO `facturas` VALUES (5484,1,23296,28,20,20,'DEMO-FAC-MOE-CIERRE',NULL,'DEMO',1,'2026-05-22','2026-05-24',NULL,510.00,510.00,107.10,NULL,617.10,'emitida',0,'MOEVE','[demo-integral-reducida-2026-06-02] Factura DEMO-FAC-MOE-CIERRE','2026-06-02 15:42:26','2026-06-02 15:42:26'),(5485,2,23303,29,21,21,'DEMO-FAC-REP-PARCIAL',NULL,'DEMO',1,'2026-05-23','2026-05-25',NULL,200.00,200.00,42.00,NULL,242.00,'emitida',0,'REPSOL','[demo-integral-reducida-2026-06-02] Factura DEMO-FAC-REP-PARCIAL','2026-06-02 15:42:26','2026-06-02 15:42:26'),(5486,1,23293,28,20,20,'QA-FAC-20260604-PARCIAL','QA-CCP-20260604',NULL,1,NULL,'2026-06-04',NULL,1360.00,1360.00,0.00,0.00,1360.00,'anulada',0,'MOEVE ES',NULL,'2026-06-04 12:58:24','2026-06-04 13:00:05');
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
-- Table structure for table `home_notices`
--

DROP TABLE IF EXISTS `home_notices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `home_notices` (
  `id_home_notice` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(40) NOT NULL,
  `title_es` varchar(180) NOT NULL,
  `body_es` text NOT NULL,
  `title_en` varchar(180) NOT NULL,
  `body_en` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_home_notice`),
  KEY `idx_home_notices_category_active` (`category`,`is_active`),
  KEY `idx_home_notices_featured_active` (`is_featured`,`is_active`),
  KEY `idx_home_notices_starts_at` (`starts_at`),
  KEY `idx_home_notices_ends_at` (`ends_at`),
  KEY `fk_home_notices_created_by` (`created_by`),
  KEY `fk_home_notices_updated_by` (`updated_by`),
  CONSTRAINT `fk_home_notices_created_by` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_home_notices_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `home_notices`
--

LOCK TABLES `home_notices` WRITE;
/*!40000 ALTER TABLE `home_notices` DISABLE KEYS */;
INSERT INTO `home_notices` VALUES (1,'internal_notice','Demo CIETE v2.1.0 preparada para revisión','La versión v2.1.0 queda preparada con datos reales importados, validación técnica completada y casos de prueba para revisar el flujo diario de trabajo.','CIETE v2.1.0 demo ready for review','Version v2.1.0 is ready with real imported data, completed technical validation and test cases for reviewing the daily workflow.',0,0,NULL,NULL,1,1,'2026-05-19 04:32:14','2026-06-04 19:43:57'),(2,'internal_notice','Revisión operativa con datos reales','La base local trabaja ya con datos reales de MOEVE y REPSOL. Si detectas importes, fechas o referencias extrañas, revísalos como avisos de origen antes de corregirlos.','Operational review with real data','The local database now uses real MOEVE and REPSOL data. If you detect unusual amounts, dates or references, review them as source-data warnings before correcting anything.',0,0,NULL,NULL,1,1,'2026-05-19 04:32:14','2026-06-04 19:43:57'),(3,'internal_notice','Contextos reales activos','El selector de contexto permite MOEVE, REPSOL y OTROS CLIENTES. La exportación PDF/CSV/ARIBA es exclusiva de MOEVE. Repsol trabaja con tarifa única vigente.','Real contexts active','The context selector allows MOEVE, REPSOL and OTHER CLIENTS. PDF/CSV/ARIBA export is exclusive to MOEVE. Repsol uses a single active tariff.',0,0,NULL,NULL,1,1,'2026-05-19 04:32:14','2026-06-04 19:43:57'),(4,'internal_notice','Uso del modo Excel','El modo Ciete Excel está pensado para trabajo diario con tablas densas. Usa filtros, paginación e Ir a página para moverte rápido por grandes volúmenes.','Using Excel mode','Ciete Excel mode is designed for daily work with dense tables. Use filters, pagination and Go to page to move quickly through large datasets.',0,0,NULL,NULL,1,1,'2026-05-19 04:32:14','2026-06-04 19:43:57'),(5,'system_update','Importación real completada','Se han cargado 11 Excel operativos con trabajos, pedidos, ítems y facturas reales. Los conteos principales están reconciliados con la referencia P1-12.','Real import completed','11 operational Excel files have been loaded with real jobs, orders, items and invoices. The main counts are reconciled with the P1-12 reference.',0,0,NULL,NULL,1,1,'2026-05-19 04:32:14','2026-06-04 19:43:57'),(6,'system_update','Paginación mejorada','Los listados principales incorporan navegación rápida con primera página, última página e Ir a página para reducir tiempos de trabajo.','Improved pagination','Main listings now include faster navigation with first page, last page and Go to page to reduce working time.',0,0,NULL,NULL,1,1,'2026-05-19 04:32:14','2026-06-04 19:43:57'),(7,'system_update','Control de edición por celda','Si otro usuario modificó un campo en los últimos 60 minutos, el sistema muestra aviso con el valor anterior, el nuevo, quién lo cambió y cuándo. Tu borrador se conserva hasta que decides.','Cell-level edit conflict control','If another user changed a field in the last 60 minutes, the system shows a notice with the previous and new value, who changed it and when. Your draft is preserved until you decide.',1,0,NULL,NULL,1,1,'2026-05-19 04:32:14','2026-06-04 19:36:53'),(8,'company_news','Versión v2.1.0 disponible','La versión v2.1.0 queda disponible para validación operativa local, con roles, permisos, contextos y datos reales cargados.','Version v2.1.0 available','Version v2.1.0 is available for local operational validation, with roles, permissions, contexts and real data loaded.',0,0,NULL,NULL,1,1,'2026-05-19 04:32:14','2026-06-04 19:43:57'),(9,'company_news','Panel técnico más limpio','El administrador técnico cuenta con un panel más ordenado para usuarios, soporte, auditoría técnica, mantenimiento, avisos y estado del sistema.','Cleaner technical panel','The technical administrator now has a cleaner panel for users, support, technical audit, maintenance, notices and system status.',0,0,NULL,NULL,1,1,'2026-05-19 04:32:14','2026-06-04 19:43:57'),(10,'company_news','Validación por roles preparada','El ERP está preparado para probar el día a día de Dirección, Contabilidad, Ejecución MOEVE, Ejecución REPSOL, multicontexto y administración técnica.','Role-based validation ready','The ERP is ready to test daily work for Management, Accounting, MOEVE Execution, REPSOL Execution, multicontext users and technical administration.',0,0,NULL,NULL,1,1,'2026-05-19 04:32:14','2026-06-04 19:43:57'),(11,'internal_notice','ERP Ciete v2.2.0 listo para demo con CIETE','La versión v2.2.0 incluye exportación MOEVE completa (PDF, CSV, ARIBA), parametrización de campos ARIBA desde maestros, modal de preparación de correo Moeve, protección de exportaciones por contexto y tarifa Repsol alineada con negocio.','ERP Ciete v2.2.0 ready for CIETE demo','Version v2.2.0 includes full MOEVE export (PDF, CSV, ARIBA), ARIBA field configuration from masters, Moeve email preparation modal, export protection by context and Repsol tariff aligned with business rules.',1,1,NULL,NULL,1,1,'2026-06-04 19:36:53','2026-06-04 19:36:53'),(12,'internal_notice','Datos reales MOEVE y REPSOL activos','La base local trabaja con datos reales de MOEVE y REPSOL. Si detectas importes, fechas o referencias extrañas, revísalos como avisos de origen antes de corregirlos.','Real MOEVE and REPSOL data active','The local database uses real MOEVE and REPSOL data. If you detect unusual amounts, dates or references, review them as source-data warnings before correcting anything.',1,0,NULL,NULL,1,1,'2026-06-04 19:36:53','2026-06-04 19:36:53'),(13,'internal_notice','Exportación MOEVE: campos ARIBA configurados','Los campos ARIBA del contrato 772 MOEVE están parametrizados en Maestros → Contratos. Antes de demo con CIETE, César debe revisar y actualizar Cta. de Mayor, Propuesta de Inversión y Acción de gasto con los valores reales de Moeve.','MOEVE export: ARIBA fields configured','The ARIBA fields for contract 772 MOEVE are configured in Masters → Contracts. Before the CIETE demo, César must review and update Cta. de Mayor, Propuesta de Inversión and Acción de gasto with the real Moeve values.',1,0,NULL,NULL,1,1,'2026-06-04 19:36:53','2026-06-04 19:36:53'),(14,'system_update','Exportación MOEVE operativa','El pedido MOEVE genera PDF imprimible, CSV con formato ARIBA y cuadro de tramitación. Desde la ficha del pedido: botón \"Preparar correo Moeve\" para asunto, cuerpo y checklist de envío.','MOEVE export operational','MOEVE orders generate printable PDF, ARIBA-format CSV and processing form. From the order detail: \"Prepare Moeve email\" button for subject, body and sending checklist.',1,0,NULL,NULL,1,1,'2026-06-04 19:36:53','2026-06-04 19:36:53'),(15,'system_update','Cierre y facturación mejorados','El panel de cierre de Dirección distingue trabajos listos para finalizar, bloqueados e incidentes. Las facturas pueden ser parciales, con anulación conservando trazabilidad.','Improved closure and billing','The Management closure panel distinguishes jobs ready to close, blocked and with incidents. Invoices can be partial, with cancellation keeping full traceability.',1,0,NULL,NULL,1,1,'2026-06-04 19:36:53','2026-06-04 19:36:53'),(16,'company_news','Versión v2.2.0 disponible','La versión v2.2.0 cierra el ciclo de desarrollo principal: exportación MOEVE completa, protección de exportaciones por contexto, tarifa Repsol alineada, documentación de entrega y limpieza de proyecto.','Version v2.2.0 available','Version v2.2.0 closes the main development cycle: full MOEVE export, context-based export protection, Repsol tariff aligned, delivery documentation and project cleanup.',1,0,NULL,NULL,1,1,'2026-06-04 19:36:53','2026-06-04 19:36:53'),(17,'company_news','Tarifa Repsol alineada con negocio','Repsol trabaja con tarifa única vigente (Adjud. 2023-2027). El tarifario alternativo de demo queda inactivo. La exportación PDF/CSV/ARIBA es específica de Moeve y no aplica a Repsol.','Repsol tariff aligned with business','Repsol uses a single active tariff (Adjud. 2023-2027). The demo alternative tariff is now inactive. PDF/CSV/ARIBA export is specific to Moeve and does not apply to Repsol.',1,0,NULL,NULL,1,1,'2026-06-04 19:36:53','2026-06-04 19:36:53'),(18,'company_news','Documentación de entrega lista','La carpeta docs/00_ENTREGA_FINAL contiene 17 documentos: instalación, arquitectura, guía por roles, flujo operativo, exportación Moeve, importación, testing, despliegue y pendientes.','Delivery documentation ready','The docs/00_ENTREGA_FINAL folder contains 17 documents: installation, architecture, role guide, operational flow, Moeve export, import, testing, deployment and pending items.',1,0,NULL,NULL,1,1,'2026-06-04 19:36:53','2026-06-04 19:36:53');
/*!40000 ALTER TABLE `home_notices` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7523 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026_03_24_000001_create_framework_support_tables',1),(2,'2026_03_24_000002_create_personal_access_tokens_table',1),(3,'2026_03_24_000010_create_security_core_tables',1),(4,'2026_03_24_000015_create_maestros_tables',1),(5,'2026_03_24_000020_create_empresas_contactos_base_tables',1),(6,'2026_03_24_000025_create_estaciones_tables',1),(7,'2026_03_24_000030_create_security_users_tables',1),(8,'2026_03_24_000035_create_tarifarios_tables',1),(9,'2026_03_24_000040_create_comunicacion_operativa_base_tables',1),(10,'2026_03_24_000050_create_trabajos_operativa_tables',1),(11,'2026_03_24_000055_create_presupuestos_tables',1),(12,'2026_03_24_000060_create_legalizaciones_tables',1),(13,'2026_03_24_000070_create_importacion_tables',1),(14,'2026_03_24_000080_create_audit_log_table',1),(15,'2026_04_13_000001_add_email_recuperacion_and_create_mensajes_tables',1),(16,'2026_04_24_000090_create_support_tables_and_extend_messages',1),(17,'2026_04_24_000100_normalize_erp_role_catalog',1),(18,'2026_04_30_000110_align_audit_log_structure',1),(19,'2026_05_02_000120_create_factura_items_and_update_facturas',1),(20,'2026_05_02_000125_add_interface_mode_to_usuarios',1),(21,'2026_05_02_165419_add_pendiente_facturar_to_trabajos_estado_enum',1),(22,'2026_05_02_170426_drop_personal_access_tokens_table',1),(23,'2026_05_02_180000_add_borrador_and_cancelado_to_pedidos_estado_enum',1),(24,'2026_05_05_000130_create_contrato_empresas_facturadoras',1),(25,'2026_05_06_000140_cleanup_demo_legacy_schema',1),(26,'2026_05_07_000150_add_import_warning_metadata',1),(27,'2026_05_07_000160_align_ciete_final_schema_rules',1),(28,'2026_05_19_000170_create_home_notices_table',2),(29,'2026_06_01_000140_add_es_predeterminado_to_tarifarios_table',3),(30,'2026_06_04_000001_add_ariba_fields_to_contratos',4),(31,'2026_06_04_000002_add_ariba_sociedad_to_contratos',5);
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
INSERT INTO `password_reset_tokens` VALUES ('cesar@ciete.es','$2y$12$w1j4gvNMJqGEDV0vFBE6suS9CXYwvbO.bu9K5DfYaJRr5SiMc22Ny','2026-06-04 13:29:15');
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
) ENGINE=InnoDB AUTO_INCREMENT=19483 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedido_items`
--

LOCK TABLES `pedido_items` WRITE;
/*!40000 ALTER TABLE `pedido_items` DISABLE KEYS */;
INSERT INTO `pedido_items` VALUES (19466,1,19410,973,'165023','165023','TOMA DE DATOS SIMPLE',510.00,1.000,510.00,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(19467,1,19411,973,'165023','165023','TOMA DE DATOS SIMPLE',510.00,3.000,1530.00,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(19468,1,19411,975,'165027','165027','PROYECTO OFICIAL. MEDIO',1250.00,1.000,1250.00,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(19469,1,19411,976,'165031','165031','INGENIERIA DE DETALLE. MEDIO',2000.00,1.000,2000.00,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(19470,1,19411,979,'165042','165042','ESTUDIO OBTENCION CERTIF. COMP URBANIST',570.00,1.000,570.00,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(19471,1,19411,972,'165052','165052','SERVICIO DIRECCION DE PROYECTO',40.00,40.000,1600.00,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(19472,1,19411,977,'181825','181825','CFO PROYECTO COMPLEJO',790.00,1.000,790.00,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(19473,1,19411,974,'181871','181871','PJT LEGA. ACTI./INST. PJT CMPT INSTALA.',2550.00,1.000,2550.00,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(19474,1,19411,978,'181874','181874','OBT. AYTO DE LICENCIA PJT CMPT INSTALA.',1825.00,2.000,3650.00,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(19475,1,19412,975,'165027','165027','PROYECTO OFICIAL. MEDIO',1250.00,1.000,1250.00,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(19476,3,19413,995,'OTR-001','OTR-001','Visita tecnica OTROS',180.00,1.000,180.00,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(19477,2,19414,981,'3012744','3012744','Implantacion lavados',460.79,1.000,460.79,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(19478,2,19415,982,'3012704','3012704','Alternativa con edificio no normalizado',493.40,1.000,493.40,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(19479,2,19415,983,'3012796','3012796','Director de proyecto',45.99,2.000,91.98,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(19480,2,19416,980,'3012735','3012735','Estudio de implantacion / reforma',1123.54,1.000,1123.54,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(19481,1,19417,973,'165023','165023','TOMA DE DATOS SIMPLE',510.00,2.500,1275.00,'2026-06-04 09:12:45','2026-06-04 09:12:45'),(19482,1,19417,975,'165027','165027','PROYECTO OFICIAL. MEDIO',1250.00,1.000,1250.00,'2026-06-04 09:12:45','2026-06-04 09:12:45');
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
) ENGINE=InnoDB AUTO_INCREMENT=19418 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos`
--

LOCK TABLES `pedidos` WRITE;
/*!40000 ALTER TABLE `pedidos` DISABLE KEYS */;
INSERT INTO `pedidos` VALUES (19410,1,23296,35,'DEMO-MOE-CIERRE','2026-05-22','2026-05-23',510.00,510.00,510.00,1.000,1.000,'facturado',1,0,1,'[demo-integral-reducida-2026-06-02] Pedido DEMO-MOE-CIERRE','2026-06-02 15:42:26','2026-06-02 15:42:26'),(19411,1,23293,35,'DEMO-MOE-LA-SENYERA','2026-05-20','2026-05-21',13940.00,13940.00,0.00,50.000,50.000,'recibido',1,1,0,'[demo-integral-reducida-2026-06-02] Pedido DEMO-MOE-LA-SENYERA','2026-06-02 15:42:26','2026-06-04 13:00:05'),(19412,1,23297,35,'DEMO-MOE-PENDIENTE','2026-05-23',NULL,1250.00,1250.00,0.00,1.000,1.000,'solicitado',0,0,0,'[demo-integral-reducida-2026-06-02] Pedido DEMO-MOE-PENDIENTE','2026-06-02 15:42:26','2026-06-02 15:42:26'),(19413,3,23305,39,'DEMO-OTR-001','2026-05-20',NULL,180.00,180.00,0.00,1.000,1.000,'solicitado',0,0,0,'[demo-integral-reducida-2026-06-02] Pedido DEMO-OTR-001','2026-06-02 15:42:26','2026-06-02 15:42:26'),(19414,2,23302,37,'DEMO-REP-LINEAS','2026-05-22','2026-05-23',460.79,460.79,0.00,1.000,1.000,'recibido',1,0,0,'[demo-integral-reducida-2026-06-02] Pedido DEMO-REP-LINEAS','2026-06-02 15:42:26','2026-06-02 15:42:26'),(19415,2,23303,37,'DEMO-REP-PARCIAL','2026-05-23','2026-05-24',585.38,585.38,200.00,3.000,3.000,'facturado_parcial',1,1,0,'[demo-integral-reducida-2026-06-02] Pedido DEMO-REP-PARCIAL','2026-06-02 15:42:26','2026-06-02 15:42:26'),(19416,2,23299,37,'DEMO-REP-PENDIENTE','2026-05-20',NULL,1123.54,1123.54,0.00,1.000,1.000,'solicitado',0,0,0,'[demo-integral-reducida-2026-06-02] Pedido DEMO-REP-PENDIENTE','2026-06-02 15:42:26','2026-06-02 15:42:26'),(19417,1,23308,35,'QA-UI-20260604-PED-MOE','2026-06-04',NULL,2525.00,2525.00,0.00,3.500,3.500,'pendiente',1,1,0,NULL,'2026-06-04 09:12:45','2026-06-04 09:12:45');
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
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones_login`
--

LOCK TABLES `sesiones_login` WRITE;
/*!40000 ALTER TABLE `sesiones_login` DISABLE KEYS */;
INSERT INTO `sesiones_login` VALUES (1,6,3,'2026-05-17 22:49:35',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-17 20:49:35','2026-05-17 20:49:35'),(2,6,3,'2026-05-18 06:54:11',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-18 04:54:11','2026-05-18 04:54:11'),(3,2,3,'2026-05-18 07:52:34',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-18 05:52:34','2026-05-18 05:52:34'),(4,5,2,'2026-05-18 17:00:52','2026-05-18 17:02:11','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-05-18 15:00:52','2026-05-18 15:02:11'),(5,2,3,'2026-05-18 17:02:25',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-05-18 15:02:25','2026-05-18 15:02:25'),(6,6,3,'2026-05-18 17:21:38','2026-05-18 18:22:03','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-18 15:21:38','2026-05-18 16:22:03'),(7,6,3,'2026-05-18 18:22:15',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-18 16:22:15','2026-05-18 16:22:15'),(8,1,3,'2026-05-18 20:34:29',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-18 18:34:29','2026-05-18 18:34:29'),(9,2,3,'2026-05-18 21:12:49',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-18 19:12:49','2026-05-18 19:12:49'),(10,1,3,'2026-05-18 23:59:19','2026-05-19 00:00:18','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-05-18 21:59:19','2026-05-18 22:00:18'),(11,2,3,'2026-05-19 00:00:33','2026-05-19 00:05:41','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-05-18 22:00:33','2026-05-18 22:05:41'),(12,6,3,'2026-05-19 00:05:59','2026-05-19 00:06:24','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-05-18 22:05:59','2026-05-18 22:06:24'),(13,4,1,'2026-05-19 00:06:41','2026-05-19 00:07:27','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-05-18 22:06:41','2026-05-18 22:07:27'),(14,5,2,'2026-05-19 00:07:49','2026-05-19 00:08:37','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-05-18 22:07:49','2026-05-18 22:08:37'),(15,3,3,'2026-05-19 00:09:00',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-05-18 22:09:00','2026-05-18 22:09:00'),(16,2,3,'2026-05-19 01:29:33',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-05-18 23:29:33','2026-05-18 23:29:33'),(17,6,3,'2026-05-19 02:02:28',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-19 00:02:28','2026-05-19 00:02:28'),(18,2,3,'2026-05-19 05:03:22',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-19 03:03:22','2026-05-19 03:03:22'),(19,6,3,'2026-05-19 05:04:15',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-19 03:04:15','2026-05-19 03:04:15'),(20,1,3,'2026-05-19 05:34:42',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-05-19 03:34:42','2026-05-19 03:34:42'),(21,1,3,'2026-05-19 05:53:56','2026-05-19 05:56:00','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-05-19 03:53:56','2026-05-19 03:56:00'),(22,4,1,'2026-05-19 05:56:09','2026-05-19 06:08:33','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-05-19 03:56:09','2026-05-19 04:08:33'),(23,2,3,'2026-05-19 06:08:34','2026-05-19 06:11:08','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-05-19 04:08:34','2026-05-19 04:11:08'),(24,1,3,'2026-05-19 06:11:08','2026-05-19 06:14:11','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-05-19 04:11:08','2026-05-19 04:14:11'),(25,4,1,'2026-05-19 06:14:12','2026-05-19 06:16:25','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-05-19 04:14:12','2026-05-19 04:16:25'),(26,6,3,'2026-05-19 06:16:26',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-05-19 04:16:26','2026-05-19 04:16:26'),(27,2,3,'2026-05-28 19:13:01',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-28 17:13:01','2026-05-28 17:13:01'),(28,2,3,'2026-05-29 07:08:59',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-29 05:08:59','2026-05-29 05:08:59'),(29,2,3,'2026-06-01 07:47:09',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-01 05:47:09','2026-06-01 05:47:09'),(30,1,3,'2026-06-01 07:54:12',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.122.1 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-06-01 05:54:12','2026-06-01 05:54:12'),(31,2,3,'2026-06-01 13:50:15',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-01 11:50:15','2026-06-01 11:50:15'),(32,2,3,'2026-06-01 14:14:34',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.122.1 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',NULL,'2026-06-01 12:14:34','2026-06-01 12:14:34'),(33,2,3,'2026-06-02 08:13:54',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-02 06:13:54','2026-06-02 06:13:54'),(34,2,3,'2026-06-02 14:50:32',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-02 12:50:32','2026-06-02 12:50:32'),(35,2,3,'2026-06-04 07:49:01',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.123.0 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36',NULL,'2026-06-04 05:49:01','2026-06-04 05:49:01'),(36,6,3,'2026-06-04 08:02:27','2026-06-04 08:21:11','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 06:02:27','2026-06-04 06:21:11'),(37,2,3,'2026-06-04 08:21:18',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 06:21:18','2026-06-04 06:21:18'),(38,2,3,'2026-06-04 10:56:11','2026-06-04 11:05:10','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 08:56:11','2026-06-04 09:05:10'),(39,3,3,'2026-06-04 11:05:32',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 09:05:32','2026-06-04 09:05:32'),(40,3,3,'2026-06-04 14:42:22','2026-06-04 14:44:51','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 12:42:22','2026-06-04 12:44:51'),(41,4,1,'2026-06-04 14:45:08','2026-06-04 14:45:56','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 12:45:08','2026-06-04 12:45:56'),(42,3,3,'2026-06-04 14:46:09','2026-06-04 14:49:59','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 12:46:09','2026-06-04 12:49:59'),(43,4,1,'2026-06-04 14:50:34','2026-06-04 14:51:47','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 12:50:34','2026-06-04 12:51:47'),(44,3,3,'2026-06-04 14:51:59','2026-06-04 14:53:53','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 12:51:59','2026-06-04 12:53:53'),(45,6,3,'2026-06-04 14:54:07','2026-06-04 15:00:49','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 12:54:07','2026-06-04 13:00:49'),(46,1,3,'2026-06-04 15:01:22','2026-06-04 15:07:16','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 13:01:22','2026-06-04 13:07:16'),(47,4,1,'2026-06-04 15:07:51','2026-06-04 15:12:16','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 13:07:51','2026-06-04 13:12:16'),(48,4,1,'2026-06-04 15:30:34','2026-06-04 15:32:28','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 13:30:34','2026-06-04 13:32:28'),(49,1,3,'2026-06-04 15:33:02','2026-06-04 15:39:00','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 13:33:02','2026-06-04 13:39:00'),(50,4,1,'2026-06-04 15:39:33','2026-06-04 15:42:32','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 13:39:33','2026-06-04 13:42:32'),(51,3,3,'2026-06-04 15:43:20','2026-06-04 16:27:57','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 13:43:20','2026-06-04 14:27:57'),(52,2,3,'2026-06-04 16:19:21',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 14:19:21','2026-06-04 14:19:21'),(53,2,3,'2026-06-04 16:28:45','2026-06-04 16:44:55','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 14:28:45','2026-06-04 14:44:55'),(54,6,3,'2026-06-04 16:46:09','2026-06-04 16:47:29','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 14:46:09','2026-06-04 14:47:29'),(55,3,3,'2026-06-04 16:48:01','2026-06-04 16:53:59','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 14:48:01','2026-06-04 14:53:59'),(56,1,3,'2026-06-04 16:55:01',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 14:55:01','2026-06-04 14:55:01'),(57,4,1,'2026-06-04 20:43:20','2026-06-04 20:44:28','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 18:43:20','2026-06-04 18:44:28'),(58,3,3,'2026-06-04 20:46:09',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 18:46:09','2026-06-04 18:46:09'),(59,2,3,'2026-06-04 21:40:54',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-04 19:40:54','2026-06-04 19:40:54');
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
INSERT INTO `sessions` VALUES ('A4726ajvz2JEHwotPvO0JWx5JCnL1YRk50HJPFan',2,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','YTo4OntzOjY6Il90b2tlbiI7czo0MDoiNWVtT014TEdBMDk5OEt6WmU1Y09IUDVRYXpvVWRDYm1UN2VPN01lMyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo2OiJsb2NhbGUiO3M6MjoiZXMiO3M6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjIxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAiO3M6NToicm91dGUiO3M6NToiaW5kZXgiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO3M6MTU6InNlc2lvbl9sb2dpbl9pZCI7aTo1OTtzOjU6ImNpZXRlIjthOjE6e3M6MTQ6ImFjdGl2ZV9jb250ZXh0IjtpOjM7fX0=',1780609480),('HZGyefXZAjg8aa77icUY8UHBkGuxP5uXqjEv1ACz',3,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','YTo4OntzOjY6Il90b2tlbiI7czo0MDoiMmF1SThNeHZ6VUU3QjJOQU15d1dEOFBaazY1aVAzSVQ0c1hveU9FUyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwIjt9czo2OiJsb2NhbGUiO3M6MjoiZXMiO3M6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7czoxNToic2VzaW9uX2xvZ2luX2lkIjtpOjU4O3M6NToiY2lldGUiO2E6MTp7czoxNDoiYWN0aXZlX2NvbnRleHQiO2k6Mjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo1OiJpbmRleCI7fX0=',1780609452);
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
) ENGINE=InnoDB AUTO_INCREMENT=1005 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarifario_lineas`
--

LOCK TABLES `tarifario_lineas` WRITE;
/*!40000 ALTER TABLE `tarifario_lineas` DISABLE KEYS */;
INSERT INTO `tarifario_lineas` VALUES (972,1,35,'165052',NULL,'SERVICIO DIRECCION DE PROYECTO','[demo-integral-reducida-2026-06-02] Linea real La Senyera.',NULL,40.00,40.00,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(973,1,35,'165023',NULL,'TOMA DE DATOS SIMPLE','[demo-integral-reducida-2026-06-02] Linea real La Senyera.',NULL,510.00,510.00,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(974,1,35,'181871',NULL,'PJT LEGA. ACTI./INST. PJT CMPT INSTALA.','[demo-integral-reducida-2026-06-02] Linea real La Senyera.',NULL,2550.00,2550.00,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(975,1,35,'165027',NULL,'PROYECTO OFICIAL. MEDIO','[demo-integral-reducida-2026-06-02] Linea real La Senyera.',NULL,1250.00,1250.00,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(976,1,35,'165031',NULL,'INGENIERIA DE DETALLE. MEDIO','[demo-integral-reducida-2026-06-02] Linea real La Senyera.',NULL,2000.00,2000.00,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(977,1,35,'181825',NULL,'CFO PROYECTO COMPLEJO','[demo-integral-reducida-2026-06-02] Linea real La Senyera.',NULL,790.00,790.00,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(978,1,35,'181874',NULL,'OBT. AYTO DE LICENCIA PJT CMPT INSTALA.','[demo-integral-reducida-2026-06-02] Linea real La Senyera.',NULL,1825.00,1825.00,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(979,1,35,'165042',NULL,'ESTUDIO OBTENCION CERTIF. COMP URBANIST','[demo-integral-reducida-2026-06-02] Linea real La Senyera.',NULL,570.00,570.00,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(980,2,37,'3012735',NULL,'Estudio de implantación / reforma. Incluye toma de datos','[p1-12-excel-real] Línea tarifaria detectada en Excel actualizado.',NULL,1123.54,1123.54,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(981,2,37,'3012744',NULL,'Implantación lavados','[p1-12-excel-real] Línea tarifaria detectada en Excel actualizado.',NULL,460.79,460.79,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(982,2,37,'3012704',NULL,'Alternativa con edificio no normalizado','[p1-12-excel-real] Línea tarifaria detectada en Excel actualizado.',NULL,493.40,493.40,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(983,2,37,'3012796',NULL,'Director de proyecto','[p1-12-excel-real] Línea tarifaria detectada en Excel actualizado.',NULL,45.99,45.99,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(984,2,37,'3012799','67.4','Delineante proyectista de CAD','[p1-12-excel-real] Línea tarifaria detectada en Excel actualizado.',NULL,22.09,22.09,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(985,2,37,'3012638',NULL,'Directror de Proyecto','[p1-12-excel-real] Línea tarifaria detectada en Excel actualizado.',NULL,45.99,45.99,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(986,2,37,'3012641',NULL,'h delineante (plano áreas)','[p1-12-excel-real] Línea tarifaria detectada en Excel actualizado.',NULL,22.09,22.09,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(987,2,37,'3012673',NULL,'Informe para obtención certificado compatibilidad urbanística','[p1-12-excel-real] Línea tarifaria detectada en Excel actualizado.',NULL,734.84,734.84,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(988,2,37,'3012703',NULL,'Implantación edificio no normalizado:','[p1-12-excel-real] Línea tarifaria detectada en Excel actualizado.',NULL,1584.14,1584.14,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(989,2,37,'3012705',NULL,'Alzados edificio no normalizado','[p1-12-excel-real] Línea tarifaria detectada en Excel actualizado.',NULL,277.30,277.30,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(990,2,37,'3018095',NULL,'Infografías','[p1-12-excel-real] Línea tarifaria detectada en Excel actualizado.',NULL,908.39,908.39,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(991,2,37,'3012797',NULL,'Titulado Superior','[p1-12-excel-real] Línea tarifaria detectada en Excel actualizado.',NULL,37.20,37.20,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(995,3,39,'OTR-001',NULL,'Visita tecnica OTROS','[demo-integral-reducida-2026-06-02] Linea demo OTROS.',NULL,180.00,180.00,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(996,3,39,'OTR-002',NULL,'Informe tecnico OTROS','[demo-integral-reducida-2026-06-02] Linea demo OTROS.',NULL,350.00,350.00,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(997,3,39,'OTR-003',NULL,'Gestion administrativa OTROS','[demo-integral-reducida-2026-06-02] Linea demo OTROS.',NULL,120.00,120.00,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(998,3,39,'OTR-004',NULL,'Direccion de obra OTROS','[demo-integral-reducida-2026-06-02] Linea demo OTROS.',NULL,650.00,650.00,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(999,1,36,'165052',NULL,'SERVICIO DIRECCION DE PROYECTO','[demo-integral-reducida-2026-06-02] Linea alternativa MOEVE para selector.',NULL,40.00,40.00,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(1000,1,36,'165023',NULL,'TOMA DE DATOS SIMPLE','[demo-integral-reducida-2026-06-02] Linea alternativa MOEVE para selector.',NULL,510.00,510.00,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(1001,3,40,'OTR-001',NULL,'Visita tecnica OTROS','[demo-integral-reducida-2026-06-02] Linea alternativa OTROS para selector.',NULL,180.00,180.00,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(1002,3,40,'OTR-002',NULL,'Informe tecnico OTROS','[demo-integral-reducida-2026-06-02] Linea alternativa OTROS para selector.',NULL,350.00,350.00,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(1003,2,38,'3012735',NULL,'Estudio de implantación / reforma. Incluye toma de datos','[demo-integral-reducida-2026-06-02] Linea alternativa REPSOL para selector.',NULL,1123.54,1123.54,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(1004,2,38,'3012744',NULL,'Implantación lavados','[demo-integral-reducida-2026-06-02] Linea alternativa REPSOL para selector.',NULL,460.79,460.79,1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26');
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
  `es_predeterminado` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_tarifario`),
  UNIQUE KEY `uq_tarifarios_ctx_nombre_version` (`id_contexto`,`nombre`,`version`),
  KEY `idx_tarifarios_contexto` (`id_contexto`),
  KEY `idx_tarifarios_id_contexto` (`id_tarifario`,`id_contexto`),
  KEY `idx_tarifarios_contrato_contexto` (`id_contrato`,`id_contexto`),
  KEY `idx_tarifarios_contrato_predeterminado` (`id_contrato`,`es_predeterminado`),
  CONSTRAINT `fk_tarifarios_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_tarifarios_contrato_contexto` FOREIGN KEY (`id_contrato`, `id_contexto`) REFERENCES `contratos` (`id_contrato`, `id_contexto`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarifarios`
--

LOCK TABLES `tarifarios` WRITE;
/*!40000 ALTER TABLE `tarifarios` DISABLE KEYS */;
INSERT INTO `tarifarios` VALUES (35,1,28,'Tarifario 772 MOEVE','2026-demo',NULL,NULL,1.0000,'EUR','[demo-integral-reducida-2026-06-02] Tarifario MOEVE principal.',1,1,'2026-06-02 15:42:26','2026-06-02 13:48:31'),(36,1,28,'Tarifario 772 MOEVE alternativo','2026-demo-alt',NULL,NULL,1.0000,'EUR','[demo-integral-reducida-2026-06-02] Tarifario MOEVE alternativo para validar selector.',0,1,'2026-06-02 15:42:26','2026-06-02 13:48:31'),(37,2,29,'TARIFA 23-27 REPSOL','2023-2027',NULL,NULL,1.0000,'EUR','[demo-integral-reducida-2026-06-02] Tarifario REPSOL principal.',1,1,'2026-06-02 15:42:26','2026-06-02 13:52:33'),(38,2,29,'TARIFA 23-27 REPSOL alternativa','2023-2027-demo-alt',NULL,NULL,1.0000,'EUR','[demo-integral-reducida-2026-06-02] Tarifario REPSOL alternativo para validar selector.',0,0,'2026-06-02 15:42:26','2026-06-02 13:52:33'),(39,3,30,'Tarifario OTROS DEMO','2026-demo',NULL,NULL,1.0000,'EUR','[demo-integral-reducida-2026-06-02] Tarifario OTROS demo.',1,1,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(40,3,30,'Tarifario OTROS DEMO alternativo','2026-demo-alt',NULL,NULL,1.0000,'EUR','[demo-integral-reducida-2026-06-02] Tarifario OTROS alternativo para validar selector.',0,1,'2026-06-02 15:42:26','2026-06-02 15:42:26');
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipos_documento`
--

LOCK TABLES `tipos_documento` WRITE;
/*!40000 ALTER TABLE `tipos_documento` DISABLE KEYS */;
INSERT INTO `tipos_documento` VALUES (1,1,'CONTROL_TRABAJOS','Control de Trabajos Moeve',0,0,0,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(2,2,'DISENO','Diseno REPSOL',0,1,1,1,'2026-05-17 20:28:07','2026-05-18 23:32:16'),(3,2,'EDIFICACION','Edificacion REPSOL',1,1,1,1,'2026-05-17 20:28:07','2026-05-18 23:32:38'),(4,2,'OBRAS','Obras Repsol',1,0,1,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(5,2,'LICENCIAS','Licencias REPSOL',0,1,1,1,'2026-05-17 20:28:07','2026-05-18 23:33:54'),(6,2,'FV','Fotovoltaica Repsol',0,0,1,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(7,2,'ESTRUCTURAS','Estructuras y Vertidos Repsol',1,0,1,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(8,2,'MTO','Mantenimiento REPSOL',0,1,1,1,'2026-05-17 20:28:07','2026-05-18 23:34:32'),(9,2,'PUNTOS_RECARGA','Puntos de recarga REPSOL',0,1,1,1,'2026-05-17 20:28:07','2026-05-18 23:34:46'),(10,3,'OTROS','Trabajo otros clientes',0,0,0,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(16,1,'MOEVE_CONTROL','Control trabajos MOEVE',0,0,0,1,'2026-05-18 23:29:47','2026-05-18 23:29:47'),(17,2,'OBRAS_Z10','Obras REPSOL Z10',1,1,1,1,'2026-05-18 23:33:02','2026-05-18 23:33:02'),(18,2,'OBRAS_Z50','Obras REPSOL Z50',1,1,1,1,'2026-05-18 23:33:29','2026-05-18 23:33:29'),(19,2,'FOTOVOLTAICA','Fotovoltaica REPSOL',0,1,1,1,'2026-05-18 23:34:06','2026-05-18 23:34:06'),(20,2,'ESTRUCTURAS_VERTIDOS','Estructuras y vertidos REPSOL',0,1,1,1,'2026-05-18 23:34:21','2026-05-18 23:34:21');
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
) ENGINE=InnoDB AUTO_INCREMENT=508 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipos_trabajo`
--

LOCK TABLES `tipos_trabajo` WRITE;
/*!40000 ALTER TABLE `tipos_trabajo` DISABLE KEYS */;
INSERT INTO `tipos_trabajo` VALUES (1,1,1,'NPV','Nueva Propuesta de Valor',NULL,NULL,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(2,1,1,'REFORMA','Reforma General',NULL,NULL,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(3,1,1,'INDUSTRIA','Industria',NULL,NULL,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(4,2,2,'NPV','NPV',NULL,NULL,1,'2026-05-17 20:28:07','2026-05-18 23:32:16'),(5,2,2,'REFORMA','REFORMA',NULL,NULL,1,'2026-05-17 20:28:07','2026-05-18 23:32:16'),(6,3,10,'OBRA','Obra otros clientes',NULL,NULL,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(7,3,10,'MTO','Mantenimiento otros clientes',NULL,NULL,1,'2026-05-17 20:28:07','2026-05-17 20:28:07'),(258,1,16,'OTROS','Otros',NULL,NULL,1,'2026-05-18 23:29:47','2026-05-18 23:29:47'),(259,1,16,'SONDAS','Sondas',NULL,NULL,1,'2026-05-18 23:29:47','2026-05-18 23:29:47'),(260,1,16,'ESTIMACION','Estimación',NULL,NULL,1,'2026-05-18 23:29:47','2026-05-18 23:29:47'),(261,1,16,'IMAGEN','Imagen',NULL,NULL,1,'2026-05-18 23:29:47','2026-05-18 23:29:47'),(262,1,16,'OBRAS','Obras',NULL,NULL,1,'2026-05-18 23:29:47','2026-05-18 23:29:47'),(263,1,16,'TIENDAS','Tiendas',NULL,NULL,1,'2026-05-18 23:29:47','2026-05-18 23:29:47'),(264,1,16,'CUBIERTA','Cubierta',NULL,NULL,1,'2026-05-18 23:29:48','2026-05-18 23:29:48'),(265,1,16,'LEGALIZACIONES','Legalizaciones',NULL,NULL,1,'2026-05-18 23:29:48','2026-05-18 23:29:48'),(266,1,16,'AD_BLUE','Ad-Blue',NULL,NULL,1,'2026-05-18 23:29:48','2026-05-18 23:31:39'),(267,1,16,'TOPOGRAFICO','Topográfico',NULL,NULL,1,'2026-05-18 23:29:48','2026-05-18 23:29:48'),(268,1,16,'SYS','SyS',NULL,NULL,1,'2026-05-18 23:29:49','2026-05-18 23:29:49'),(269,1,16,'GLP','GLP',NULL,NULL,1,'2026-05-18 23:29:49','2026-05-18 23:29:49'),(270,1,16,'LAVADOS','Lavados',NULL,NULL,1,'2026-05-18 23:29:49','2026-05-18 23:29:49'),(271,1,16,'GNV','GNV',NULL,NULL,1,'2026-05-18 23:29:50','2026-05-18 23:29:50'),(272,1,16,'MANTENIMIENTO','Mantenimiento',NULL,NULL,1,'2026-05-18 23:29:50','2026-05-18 23:29:50'),(273,1,16,'URBANISMO','Urbanismo',NULL,NULL,1,'2026-05-18 23:29:51','2026-05-18 23:29:51'),(274,1,16,'RODAJE','Rodaje',NULL,NULL,1,'2026-05-18 23:29:51','2026-05-18 23:29:51'),(275,1,16,'DESGASIFICACION','Desgasificación',NULL,NULL,1,'2026-05-18 23:29:58','2026-05-18 23:29:58'),(276,1,16,'ACOMETIDA_AGUA','Acometida Agua',NULL,NULL,1,'2026-05-18 23:29:59','2026-05-18 23:29:59'),(277,1,16,'CARGADORES','Cargadores',NULL,NULL,1,'2026-05-18 23:30:01','2026-05-18 23:30:01'),(278,1,16,'GASOCENTRO','Gasocentro',NULL,NULL,1,'2026-05-18 23:30:02','2026-05-18 23:30:02'),(279,1,16,'VERTIDOS','Vertidos',NULL,NULL,1,'2026-05-18 23:30:02','2026-05-18 23:30:02'),(280,1,16,'DESATENDIDA','Desatendida',NULL,NULL,1,'2026-05-18 23:30:19','2026-05-18 23:30:19'),(281,1,16,'CONCURSO','Concurso',NULL,NULL,1,'2026-05-18 23:30:23','2026-05-18 23:30:23'),(282,1,16,'FOTOVOLTAICA','Fotovoltaica',NULL,NULL,1,'2026-05-18 23:30:38','2026-05-18 23:30:38'),(283,1,16,'SILENCE','Silence',NULL,NULL,1,'2026-05-18 23:31:04','2026-05-18 23:31:04'),(284,2,2,'REFORMA_GENERAL','REFORMA GENERAL',NULL,NULL,1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(285,2,2,'ESTUDIO_INFORME','ESTUDIO/INFORME',NULL,NULL,1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(286,2,2,'FICHA_TECNICA','FICHA TÉCNICA',NULL,NULL,1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(287,2,2,'NORMALIZADO','NORMALIZADO',NULL,NULL,1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(288,2,2,'MOVILIDAD_ELECTRICA','MOVILIDAD ELÉCTRICA',NULL,NULL,1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(289,2,2,'SOLUCION_ALTERNATIVA_S_4cf7709','Solución alternativa sin alzados ES edif normalizado',NULL,NULL,1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(290,2,2,'PATOLOGIA','PATOLOGÍA',NULL,NULL,1,'2026-05-18 23:32:17','2026-05-18 23:32:17'),(291,2,2,'CAMBIO_DE_MOBILIARIO_E_cc4e750','Cambio de mobiliario (Estudio de implantación / reforma. Incluye toma de datos)',NULL,NULL,1,'2026-05-18 23:32:18','2026-05-18 23:32:18'),(292,2,2,'EDIFICIO_ESTUDIO_IMPLA_5cd540d','Edificio (Estudio implantación / reforma. Incluye toma de datos)',NULL,NULL,1,'2026-05-18 23:32:18','2026-05-18 23:32:18'),(293,2,2,'ESTUDIO_IMPLANTACION_PISTA','Estudio implantación pista',NULL,NULL,1,'2026-05-18 23:32:18','2026-05-18 23:32:18'),(294,2,2,'CONCURSO','CONCURSO',NULL,NULL,1,'2026-05-18 23:32:18','2026-05-18 23:32:18'),(295,2,2,'VISITA_A_OBRA_DE_TECNI_1c4baaf','Visita a obra de técnico>250 km',NULL,NULL,1,'2026-05-18 23:32:18','2026-05-18 23:32:22'),(296,2,2,'PROYECTO_CONSTRUCTIVO_DE_PISTA','Proyecto constructivo de pista',NULL,NULL,1,'2026-05-18 23:32:18','2026-05-18 23:32:18'),(297,2,2,'TOMA_DE_DATOS','Toma de datos',NULL,NULL,1,'2026-05-18 23:32:18','2026-05-18 23:32:18'),(298,2,2,'PROYECTO_BASICO_PETICI_c87c2c3','Proyecto básico petición licencias E+P+L',NULL,NULL,1,'2026-05-18 23:32:18','2026-05-18 23:32:18'),(299,2,2,'PROYECTO_DE_LEGALIZACI_40ba49d','Proyecto de legalización de actividad',NULL,NULL,1,'2026-05-18 23:32:18','2026-05-18 23:32:18'),(300,2,2,'DIRECTOR_DE_PROYECTO','Director de proyecto',NULL,NULL,1,'2026-05-18 23:32:19','2026-05-18 23:32:19'),(301,2,2,'TOMA_DE_DATOS_PENINSULA','Toma de datos península',NULL,NULL,1,'2026-05-18 23:32:19','2026-05-18 23:32:19'),(302,2,2,'TOPOGRAFICO_ES_SIN_EDIFICIO','Topográfico ES sin edificio',NULL,NULL,1,'2026-05-18 23:32:19','2026-05-18 23:32:19'),(303,2,2,'IMPLANTACION_LAVADOS','Implantación lavados',NULL,NULL,1,'2026-05-18 23:32:19','2026-05-18 23:32:19'),(304,2,2,'PROYECTO_BASICO_PISTA','Proyecto básico pista',NULL,NULL,1,'2026-05-18 23:32:19','2026-05-18 23:32:19'),(305,2,2,'LEVANTAMIENTO_TOPOGRAF_60e9fd7','Levantamiento topográfico (incluido edificio)',NULL,NULL,1,'2026-05-18 23:32:19','2026-05-18 23:32:19'),(306,2,2,'ALZADOS_EDIFICIO_NO_NO_c459532','Alzados edificio no normalizado',NULL,NULL,1,'2026-05-18 23:32:19','2026-05-18 23:32:19'),(307,2,2,'IMPL_LAVADO_PISTA','Impl lavado + pista',NULL,NULL,1,'2026-05-18 23:32:19','2026-05-18 23:32:19'),(308,2,2,'SOLUCION_ALTERNATIVA_I_b002a24','Solución alternativa Impl lavado + pista',NULL,NULL,1,'2026-05-18 23:32:19','2026-05-18 23:32:19'),(309,2,2,'PROYECTO_CONSTRUCTIVO','Proyecto constructivo',NULL,NULL,1,'2026-05-18 23:32:19','2026-05-18 23:32:19'),(310,2,2,'PISTA','PISTA',NULL,NULL,1,'2026-05-18 23:32:19','2026-05-18 23:32:19'),(311,2,2,'DELIMITACION_DE_PARCELA','Delimitación de parcela',NULL,NULL,1,'2026-05-18 23:32:20','2026-05-18 23:32:20'),(312,2,2,'MEMORIA_AMBIENTAL','Memoria ambiental',NULL,NULL,1,'2026-05-18 23:32:20','2026-05-18 23:32:20'),(313,2,2,'IMPLANTACION_LAVADOS_PISTA','Implantación lavados + pista',NULL,NULL,1,'2026-05-18 23:32:20','2026-05-18 23:32:21'),(314,2,2,'ESTUDIO_IMPLANTACION_S_3ed12e3','Estudio implantación sin alzados edificio normalizado',NULL,NULL,1,'2026-05-18 23:32:20','2026-05-18 23:32:20'),(315,2,2,'IMPLANTACION_LAVADO_EDIFICIO','Implantación Lavado + edificio',NULL,NULL,1,'2026-05-18 23:32:20','2026-05-18 23:32:20'),(316,2,2,'SOLUCION_ALTERNATIVA_S_c0c0786','Solución alternativa sin alzados',NULL,NULL,1,'2026-05-18 23:32:20','2026-05-18 23:32:20'),(317,2,2,'ALTERNATIVA_IMPLANTACI_1de1210','Alternativa Implantación lavados+pista',NULL,NULL,1,'2026-05-18 23:32:21','2026-05-18 23:32:21'),(318,2,2,'TOMA_DE_DATOS_BALEARES','Toma de datos Baleares',NULL,NULL,1,'2026-05-18 23:32:21','2026-05-18 23:32:21'),(319,2,2,'DELINEANTE_PROYECTISTA_DE_CAD','Delineante proyectista de CAD',NULL,NULL,1,'2026-05-18 23:32:21','2026-05-18 23:32:21'),(320,2,2,'ESTUDIO_DE_IMPLANTACION_PISTA','Estudio de implantación Pista',NULL,NULL,1,'2026-05-18 23:32:21','2026-05-18 23:32:21'),(321,2,2,'GESTION_CON_ORGANISMO_20a04a7','Gestión con organismo con visita',NULL,NULL,1,'2026-05-18 23:32:21','2026-05-18 23:32:21'),(322,2,2,'ESTUDIO_DE_REMODELACIO_40b3001','Estudio de remodelación sin alzados E+P',NULL,NULL,1,'2026-05-18 23:32:22','2026-05-18 23:32:22'),(323,2,2,'ESTUDIO_DE_IMPLANTACIO_773791b','Estudio de implantación sin alzados edifi normalizado',NULL,NULL,1,'2026-05-18 23:32:22','2026-05-18 23:32:22'),(324,2,2,'ALTERNATIVA_IMPLANTACI_7520b3b','Alternativa implantación ES sin alzados edif normalizado',NULL,NULL,1,'2026-05-18 23:32:22','2026-05-18 23:32:22'),(325,2,2,'IMPLANTACION_EDIFICIO_4940099','Implantación edificio + pista + lavados',NULL,NULL,1,'2026-05-18 23:32:22','2026-05-18 23:32:22'),(326,2,2,'GESTION_CON_ORGANISMO_7360b29','Gestión con organismo sin visita',NULL,NULL,1,'2026-05-18 23:32:22','2026-05-18 23:32:22'),(327,2,2,'ESTUDIO_DE_IMPLANTACION','Estudio de implantación',NULL,NULL,1,'2026-05-18 23:32:22','2026-05-18 23:32:22'),(328,2,2,'PROYECTO_E_LEGALIZACIO_59fa5e5','Proyecto e legalización de actividad',NULL,NULL,1,'2026-05-18 23:32:22','2026-05-18 23:32:22'),(329,2,2,'OBTENCION_LICENCIA_DE_APERTURA','Obtención licencia de apertura',NULL,NULL,1,'2026-05-18 23:32:22','2026-05-18 23:32:22'),(330,2,2,'ESTUDIO_IMPACTO_ACUSTICO','Estudio impacto acústico',NULL,NULL,1,'2026-05-18 23:32:22','2026-05-18 23:32:22'),(331,2,2,'GESTIONES_CON_ORGANISM_eed305a','Gestiones con organismo sin visita',NULL,NULL,1,'2026-05-18 23:32:22','2026-05-18 23:32:22'),(332,2,3,'STARBUCKS','STARBUCKS',NULL,NULL,1,'2026-05-18 23:32:38','2026-05-18 23:32:38'),(333,2,3,'STOP_GO','STOP&GO',NULL,NULL,1,'2026-05-18 23:32:38','2026-05-18 23:32:38'),(334,2,3,'STOP_GO_MINI','STOP&GO MINI',NULL,NULL,1,'2026-05-18 23:32:38','2026-05-18 23:32:38'),(335,2,3,'OBRAS_MENORES','OBRAS MENORES',NULL,NULL,1,'2026-05-18 23:32:39','2026-05-18 23:32:39'),(336,2,3,'NORMALIZACION','NORMALIZACIÓN',NULL,NULL,1,'2026-05-18 23:32:39','2026-05-18 23:32:39'),(337,2,3,'ITE','ITE',NULL,NULL,1,'2026-05-18 23:32:42','2026-05-18 23:32:42'),(338,2,3,'EDIFICIO_LAVADO','EDIFICIO + LAVADO',NULL,NULL,1,'2026-05-18 23:32:42','2026-05-18 23:32:42'),(339,2,3,'CAMBIO_DE_SUELO','CAMBIO DE SUELO',NULL,NULL,1,'2026-05-18 23:32:42','2026-05-18 23:32:42'),(340,2,3,'ESTACION_FUTURA','ESTACIÓN FUTURA',NULL,NULL,1,'2026-05-18 23:32:42','2026-05-18 23:32:42'),(341,2,3,'PISTA','PISTA',NULL,NULL,1,'2026-05-18 23:32:43','2026-05-18 23:32:43'),(342,2,3,'MINI_ASEOS','mini + aseos',NULL,NULL,1,'2026-05-18 23:32:44','2026-05-18 23:32:44'),(343,2,3,'RESTAURADORES_REVISION_OBRA','RESTAURADORES (revisión obra)',NULL,NULL,1,'2026-05-18 23:32:44','2026-05-18 23:32:44'),(344,2,3,'GESTIONES_CON_ORGANISM_b62f64e','Gestiones con Organismos. Sin visita.',NULL,NULL,1,'2026-05-18 23:32:44','2026-05-18 23:32:44'),(345,2,3,'SPRINT','SPRINT',NULL,NULL,1,'2026-05-18 23:32:45','2026-05-18 23:32:45'),(346,2,3,'REBRANDING','REBRANDING',NULL,NULL,1,'2026-05-18 23:32:46','2026-05-18 23:32:46'),(347,2,3,'OTROS','OTROS',NULL,NULL,1,'2026-05-18 23:32:49','2026-05-18 23:32:49'),(348,2,3,'DELINEANTE_PROYECTISTA_DE_CAD','Delineante proyectista de CAD',NULL,NULL,1,'2026-05-18 23:32:50','2026-05-18 23:32:50'),(349,2,3,'ESTUDIO_DE_IMPLANTACIO_287932e','Estudio de implantación / reforma (i/ presupuesto por partidas y mediciones para contratación de obra) . Inversión>30K€.',NULL,NULL,1,'2026-05-18 23:32:52','2026-05-18 23:32:52'),(350,2,3,'TRAMITACION_DE_ACTOS_C_18e5c31','Tramitación de actos comunicados o declaraciones responsables',NULL,NULL,1,'2026-05-18 23:32:52','2026-05-18 23:32:52'),(351,2,3,'SIN_CATEGORIA','Sin categoría',NULL,NULL,1,'2026-05-18 23:32:54','2026-05-18 23:32:54'),(352,2,3,'PROYECTO_BASICO_PARA_P_a712b05','Proyecto Básico para petición de licencias',NULL,NULL,1,'2026-05-18 23:32:54','2026-05-18 23:32:54'),(353,2,3,'COORDINACION_DE_SEGURIDAD','Coordinación de Seguridad',NULL,NULL,1,'2026-05-18 23:32:54','2026-05-18 23:32:54'),(354,2,17,'SENALIZACION','Señalización',NULL,NULL,1,'2026-05-18 23:33:02','2026-05-18 23:33:02'),(355,2,17,'LEGALIZACIONES','Legalizaciones',NULL,NULL,1,'2026-05-18 23:33:02','2026-05-18 23:33:02'),(356,2,17,'MARQUESINA','Marquesina',NULL,NULL,1,'2026-05-18 23:33:02','2026-05-18 23:33:02'),(357,2,17,'LAVADOS_NO_KLIN','Lavados NO Klin',NULL,NULL,1,'2026-05-18 23:33:02','2026-05-18 23:33:02'),(358,2,17,'DESMANTELAMIENTO_DEMOLICIONES','Desmantelamiento/Demoliciones',NULL,NULL,1,'2026-05-18 23:33:02','2026-05-18 23:33:02'),(359,2,17,'SANEAMIENTO','Saneamiento',NULL,NULL,1,'2026-05-18 23:33:02','2026-05-18 23:33:02'),(360,2,17,'ASPIRACIONES','Aspiraciones',NULL,NULL,1,'2026-05-18 23:33:02','2026-05-18 23:33:02'),(361,2,17,'REGISTROS','Registros',NULL,NULL,1,'2026-05-18 23:33:02','2026-05-18 23:33:02'),(362,2,17,'REFORMA_PISTA','Reforma pista',NULL,NULL,1,'2026-05-18 23:33:02','2026-05-18 23:33:02'),(363,2,17,'PISTA','Pista',NULL,NULL,1,'2026-05-18 23:33:03','2026-05-18 23:33:03'),(364,2,17,'CARRIL_ACCESO','Carril acceso',NULL,NULL,1,'2026-05-18 23:33:03','2026-05-18 23:33:03'),(365,2,17,'OTROS','Otros',NULL,NULL,1,'2026-05-18 23:33:03','2026-05-18 23:33:03'),(366,2,17,'LICENCIAS','Licencias',NULL,NULL,1,'2026-05-18 23:33:03','2026-05-18 23:33:03'),(367,2,17,'LAVADO','Lavado',NULL,NULL,1,'2026-05-18 23:33:04','2026-05-18 23:33:04'),(368,2,17,'DESMANTELAMIENTO','Desmantelamiento',NULL,NULL,1,'2026-05-18 23:33:04','2026-05-18 23:33:04'),(369,2,17,'TIENDA','Tienda',NULL,NULL,1,'2026-05-18 23:33:13','2026-05-18 23:33:13'),(370,2,17,'N_A','#N/A',NULL,NULL,1,'2026-05-18 23:33:15','2026-05-18 23:33:15'),(371,2,18,'OTROS','Otros',NULL,NULL,1,'2026-05-18 23:33:29','2026-05-18 23:33:29'),(372,2,18,'PISTA','Pista',NULL,NULL,1,'2026-05-18 23:33:30','2026-05-18 23:33:30'),(373,2,18,'TIENDA','Tienda',NULL,NULL,1,'2026-05-18 23:33:30','2026-05-18 23:33:30'),(374,2,18,'NPV','NPV',NULL,NULL,1,'2026-05-18 23:33:30','2026-05-18 23:33:30'),(375,2,18,'LAVADO','Lavado',NULL,NULL,1,'2026-05-18 23:33:30','2026-05-18 23:33:30'),(376,2,18,'MARQUESINA','Marquesina',NULL,NULL,1,'2026-05-18 23:33:30','2026-05-18 23:33:30'),(377,2,18,'ADBLUE','Adblue',NULL,NULL,1,'2026-05-18 23:33:31','2026-05-18 23:33:31'),(378,2,18,'DESMANTELAMIENTO_DEMOLICIONES','Desmantelamiento/Demoliciones',NULL,NULL,1,'2026-05-18 23:33:31','2026-05-18 23:33:31'),(379,2,18,'LICENCIAS','Licencias',NULL,NULL,1,'2026-05-18 23:33:31','2026-05-18 23:33:31'),(380,2,18,'SANEAMIENTO','Saneamiento',NULL,NULL,1,'2026-05-18 23:33:31','2026-05-18 23:33:31'),(381,2,18,'REFORMA_PISTA','Reforma pista',NULL,NULL,1,'2026-05-18 23:33:31','2026-05-18 23:33:31'),(382,2,18,'SENALIZACION','Señalización',NULL,NULL,1,'2026-05-18 23:33:31','2026-05-18 23:33:31'),(383,2,18,'CAMBIO_TITULAR','Cambio titular',NULL,NULL,1,'2026-05-18 23:33:31','2026-05-18 23:33:31'),(384,2,18,'DESMANTELAMIENTO','Desmantelamiento',NULL,NULL,1,'2026-05-18 23:33:33','2026-05-18 23:33:33'),(385,2,18,'N_A','#N/A',NULL,NULL,1,'2026-05-18 23:33:40','2026-05-18 23:33:40'),(386,2,18,'INFORME_PARA_OBTENCION_7e96894','Informe para obtención certificado compatibilidad urbanística',NULL,NULL,1,'2026-05-18 23:33:40','2026-05-18 23:33:40'),(387,2,18,'DELINEANTE_PROYECTISTA_DE_CAD','Delineante proyectista de CAD',NULL,NULL,1,'2026-05-18 23:33:40','2026-05-18 23:33:40'),(388,2,18,'LICENCIA','licencia',NULL,NULL,1,'2026-05-18 23:33:40','2026-05-18 23:33:40'),(389,2,18,'FICHA_TECNICA_PENINSULA','Ficha técnica (Península)',NULL,NULL,1,'2026-05-18 23:33:40','2026-05-18 23:33:40'),(390,2,18,'EDIFICIO','Edificio',NULL,NULL,1,'2026-05-18 23:33:41','2026-05-18 23:33:41'),(391,2,18,'CSS','css',NULL,NULL,1,'2026-05-18 23:33:42','2026-05-18 23:33:42'),(392,2,18,'LEGALIZACION_CONSULTAR_d08d6a8','legalizacion consultar si es comunicación o pues',NULL,NULL,1,'2026-05-18 23:33:42','2026-05-18 23:33:42'),(393,2,18,'IMPLANTACION','implantacion',NULL,NULL,1,'2026-05-18 23:33:42','2026-05-18 23:33:42'),(394,2,18,'TRAMITACION_DE_ACTOS_C_18e5c31','Tramitación de actos comunicados o declaraciones responsables',NULL,NULL,1,'2026-05-18 23:33:42','2026-05-18 23:33:42'),(395,2,18,'VISITA_A_OBRA_DE_TENICO_250_KM','Visita a Obra de ténico (>250 Km)',NULL,NULL,1,'2026-05-18 23:33:44','2026-05-18 23:33:44'),(396,2,18,'ESTUDIO_DE_IMPLANTACIO_e8732c2','Estudio de implantación / reforma (i/ presupuesto por partidas y mediciones para contratación de obra). Incluye toma de ',NULL,NULL,1,'2026-05-18 23:33:44','2026-05-18 23:33:44'),(397,2,18,'TOMA_DE_DATOS','toma de datos',NULL,NULL,1,'2026-05-18 23:33:44','2026-05-18 23:33:44'),(398,2,5,'LICENCIAS','LICENCIAS',NULL,NULL,1,'2026-05-18 23:33:54','2026-05-18 23:33:54'),(399,2,5,'MONOPOSTE','MONOPOSTE',NULL,NULL,1,'2026-05-18 23:33:55','2026-05-18 23:33:55'),(400,2,19,'ESTUDIO_SOMBRAS','ESTUDIO SOMBRAS',NULL,NULL,1,'2026-05-18 23:34:06','2026-05-18 23:34:06'),(401,2,19,'REVISION_ESTRUCTURAL','REVISIÓN ESTRUCTURAL',NULL,NULL,1,'2026-05-18 23:34:06','2026-05-18 23:34:06'),(402,2,19,'PROYECTO_FV','Proyecto FV',NULL,NULL,1,'2026-05-18 23:34:06','2026-05-18 23:34:10'),(403,2,19,'FOTOVOLTAICA','FOTOVOLTAICA',NULL,NULL,1,'2026-05-18 23:34:06','2026-05-18 23:34:06'),(404,2,19,'LEGALIZACION_INSTALACI_f7e5df9','Legalización instalación fotovoltaica colectiva',NULL,NULL,1,'2026-05-18 23:34:06','2026-05-18 23:34:06'),(405,2,19,'MEMORIA_GESTION','Memoria gestión',NULL,NULL,1,'2026-05-18 23:34:06','2026-05-18 23:34:06'),(406,2,19,'AUTOCONSUMO','Autoconsumo',NULL,NULL,1,'2026-05-18 23:34:06','2026-05-18 23:34:06'),(407,2,19,'DIRECCION_FACULTATIVA_250_KM','DIRECCIÓN FACULTATIVA <250 km',NULL,NULL,1,'2026-05-18 23:34:06','2026-05-18 23:34:08'),(408,2,19,'COORDINACION_SYS_250_KM','COORDINACIÓN SyS < 250 km',NULL,NULL,1,'2026-05-18 23:34:06','2026-05-18 23:34:08'),(409,2,19,'GESTION_ORGANISMO','Gestión organismo',NULL,NULL,1,'2026-05-18 23:34:06','2026-05-18 23:34:06'),(410,2,19,'LEGALIZACION_INSTALACI_323e622','Legalización instalación fotovoltaica autoconsumo',NULL,NULL,1,'2026-05-18 23:34:06','2026-05-18 23:34:06'),(411,2,19,'ESTUDIO_MARQUESINA','ESTUDIO MARQUESINA',NULL,NULL,1,'2026-05-18 23:34:06','2026-05-18 23:34:06'),(412,2,19,'PROYECTO_ELECTRICO','PROYECTO ELÉCTRICO',NULL,NULL,1,'2026-05-18 23:34:06','2026-05-18 23:34:06'),(413,2,19,'VISITA_OBRA_250_KM','VISITA OBRA > 250 km',NULL,NULL,1,'2026-05-18 23:34:07','2026-05-18 23:34:12'),(414,2,19,'PROYECTO','Proyecto',NULL,NULL,1,'2026-05-18 23:34:07','2026-05-18 23:34:07'),(415,2,19,'LEGALIZACION','Legalización',NULL,NULL,1,'2026-05-18 23:34:07','2026-05-18 23:34:07'),(416,2,19,'DF','DF',NULL,NULL,1,'2026-05-18 23:34:07','2026-05-18 23:34:07'),(417,2,19,'CSS','CSS',NULL,NULL,1,'2026-05-18 23:34:07','2026-05-18 23:34:07'),(418,2,19,'TITULADO_SUPERIOR','titulado superior',NULL,NULL,1,'2026-05-18 23:34:08','2026-05-18 23:34:08'),(419,2,19,'SIN_CATEGORIA','Sin categoría',NULL,NULL,1,'2026-05-18 23:34:08','2026-05-18 23:34:08'),(420,2,19,'GESTION_CNIA_SIN_VISITA','GESTIÓN CÑÍA SIN VISITA',NULL,NULL,1,'2026-05-18 23:34:08','2026-05-18 23:34:08'),(421,2,19,'PROYECTO_REFUERZO','PROYECTO REFUERZO',NULL,NULL,1,'2026-05-18 23:34:09','2026-05-18 23:34:09'),(422,2,19,'GESTION','Gestión',NULL,NULL,1,'2026-05-18 23:34:09','2026-05-18 23:34:09'),(423,2,19,'CTAS','CTAs',NULL,NULL,1,'2026-05-18 23:34:09','2026-05-18 23:34:09'),(424,2,19,'VARIOS','VARIOS',NULL,NULL,1,'2026-05-18 23:34:10','2026-05-18 23:34:10'),(425,2,19,'MARQUESINA','Marquesina',NULL,NULL,1,'2026-05-18 23:34:11','2026-05-18 23:34:11'),(426,2,19,'INFORME_MARQUESINA','INFORME MARQUESINA',NULL,NULL,1,'2026-05-18 23:34:11','2026-05-18 23:34:11'),(427,2,20,'PATOLOGIA','PATOLOGÍA',NULL,NULL,1,'2026-05-18 23:34:21','2026-05-18 23:34:21'),(428,2,20,'REVISION_MONOPOSTE','REVISIÓN MONOPOSTE',NULL,NULL,1,'2026-05-18 23:34:21','2026-05-18 23:34:21'),(429,2,20,'DOCUMENTACION_OBRA','DOCUMENTACIÓN OBRA',NULL,NULL,1,'2026-05-18 23:34:22','2026-05-18 23:34:22'),(430,2,20,'PROYECTO_REFUERZO_DE_M_92e82dc','PROYECTO REFUERZO DE MARQUESINA',NULL,NULL,1,'2026-05-18 23:34:22','2026-05-18 23:34:22'),(431,2,20,'INFORME_MARQUESINA','INFORME MARQUESINA',NULL,NULL,1,'2026-05-18 23:34:22','2026-05-18 23:34:22'),(432,2,20,'NUEVA_MARQUESINA','NUEVA MARQUESINA',NULL,NULL,1,'2026-05-18 23:34:22','2026-05-18 23:34:22'),(433,2,20,'TOPOGRAFICO','TOPOGRÁFICO',NULL,NULL,1,'2026-05-18 23:34:22','2026-05-18 23:34:22'),(434,2,20,'REVISION_ESTRUCTURAL','REVISIÓN ESTRUCTURAL',NULL,NULL,1,'2026-05-18 23:34:22','2026-05-18 23:34:22'),(435,2,20,'INSPECCION_EN_DETALLE_a904579','INSPECCIÓN EN DETALLE ESTRUCTURA',NULL,NULL,1,'2026-05-18 23:34:22','2026-05-18 23:34:22'),(436,2,20,'GESTIONES_PREVIAS_LICENCIAS','GESTIONES PREVIAS LICENCIAS',NULL,NULL,1,'2026-05-18 23:34:22','2026-05-18 23:34:22'),(437,2,20,'GESTION_CON_ORGANISMOS_16bf967','Gestion con organismos sin visita',NULL,NULL,1,'2026-05-18 23:34:22','2026-05-18 23:34:22'),(438,2,20,'ESTUDIO_SOBRE_ESTADO_D_a0793be','Estudio sobre estado de marquesina existente',NULL,NULL,1,'2026-05-18 23:34:22','2026-05-18 23:34:22'),(439,2,20,'DIRECTOR_DE_PROYECTO','Director de proyecto',NULL,NULL,1,'2026-05-18 23:34:23','2026-05-18 23:34:23'),(440,2,20,'TRAMITACION_DE_ACTOS_C_18e5c31','Tramitación de actos comunicados o declaraciones responsables',NULL,NULL,1,'2026-05-18 23:34:23','2026-05-18 23:34:23'),(441,2,20,'TOMA_DE_DATOS_SIN_APOR_a142070','Toma de datos (sin aporte de medios auxiliares) Península',NULL,NULL,1,'2026-05-18 23:34:23','2026-05-18 23:34:23'),(442,2,20,'OBTENCION_DE_LICENCIAS_65c182e','Obtención de licencias de mayores o lega',NULL,NULL,1,'2026-05-18 23:34:23','2026-05-18 23:34:23'),(443,2,20,'DELINEANTE_PROYECTISTA_DE_CAD','Delineante proyectista de CAD',NULL,NULL,1,'2026-05-18 23:34:24','2026-05-18 23:34:24'),(444,2,20,'DIRECCION_FACULTATIVA_34475b3','Dirección facultativa de las obras',NULL,NULL,1,'2026-05-18 23:34:24','2026-05-18 23:34:24'),(445,2,20,'COORDINACION_DE_SEGURIDAD','Coordinación de Seguridad',NULL,NULL,1,'2026-05-18 23:34:24','2026-05-18 23:34:24'),(446,2,20,'VISITA_A_OBRA_DE_TECNI_1c4baaf','Visita a obra de técnico (< 250 Km)',NULL,NULL,1,'2026-05-18 23:34:24','2026-05-18 23:34:24'),(447,2,20,'OBTENCION_DE_LICENCIAS_ffe4548','Obtención de licencias de obras menores o legalización (modificación no sustancial)',NULL,NULL,1,'2026-05-18 23:34:24','2026-05-18 23:34:24'),(448,2,20,'GESTION_CON_ORGANISMO_1e84ceb','Gestión con Organismo con visita (Península)',NULL,NULL,1,'2026-05-18 23:34:24','2026-05-18 23:34:24'),(449,2,20,'COORDINACION_DE_SEGURI_91e54d8','Coordinación de Seguridad (<250 Km)',NULL,NULL,1,'2026-05-18 23:34:24','2026-05-18 23:34:24'),(450,2,20,'VISITA_A_OBRA_DE_TENICO_250_KM','Visita a Obra de ténico (>250 Km)',NULL,NULL,1,'2026-05-18 23:34:24','2026-05-18 23:34:24'),(451,2,20,'PROYECTO_DE_MARQUESINA','Proyecto de marquesina',NULL,NULL,1,'2026-05-18 23:34:24','2026-05-18 23:34:24'),(452,2,20,'DIRECCION_FACULTATIVA_cafcee9','Dirección facultativa de las obras (<250 Km)',NULL,NULL,1,'2026-05-18 23:34:24','2026-05-18 23:34:24'),(453,2,20,'SIN_CATEGORIA','Sin categoría',NULL,NULL,1,'2026-05-18 23:34:25','2026-05-18 23:34:25'),(454,2,8,'INDUSTRIA','Industria',NULL,NULL,1,'2026-05-18 23:34:32','2026-05-18 23:34:37'),(455,2,8,'RBT','RBT',NULL,NULL,1,'2026-05-18 23:34:32','2026-05-18 23:34:32'),(456,2,8,'IP04','IP04',NULL,NULL,1,'2026-05-18 23:34:33','2026-05-18 23:34:33'),(457,2,8,'ACTUACIONES_MENORES','ACTUACIONES MENORES',NULL,NULL,1,'2026-05-18 23:34:33','2026-05-18 23:34:33'),(458,2,8,'COMPRESOR','COMPRESOR',NULL,NULL,1,'2026-05-18 23:34:33','2026-05-18 23:34:33'),(459,2,8,'INCENDIOS','INCENDIOS',NULL,NULL,1,'2026-05-18 23:34:35','2026-05-18 23:34:35'),(460,2,8,'AREAS_CLASIFICADAS','ÁREAS CLASIFICADAS',NULL,NULL,1,'2026-05-18 23:34:35','2026-05-18 23:34:35'),(461,2,8,'LAVADOS','LAVADOS',NULL,NULL,1,'2026-05-18 23:34:35','2026-05-18 23:34:35'),(462,2,8,'CSS','CSS',NULL,NULL,1,'2026-05-18 23:34:36','2026-05-18 23:34:36'),(463,2,8,'HORA_TITULADO_MEDIO','Hora titulado medio',NULL,NULL,1,'2026-05-18 23:34:36','2026-05-18 23:34:36'),(464,2,8,'AASS','AASS',NULL,NULL,1,'2026-05-18 23:34:36','2026-05-18 23:34:36'),(465,2,8,'CT','CT',NULL,NULL,1,'2026-05-18 23:34:37','2026-05-18 23:34:37'),(466,2,8,'ADECUACIONES','ADECUACIONES',NULL,NULL,1,'2026-05-18 23:34:37','2026-05-18 23:34:37'),(467,2,8,'AS','AS',NULL,NULL,1,'2026-05-18 23:34:38','2026-05-18 23:34:38'),(468,2,8,'DESATENDIDA','desatendida',NULL,NULL,1,'2026-05-18 23:34:38','2026-05-18 23:34:38'),(469,2,8,'CENTRO_TRANSFORMACION','CENTRO TRANSFORMACIÓN',NULL,NULL,1,'2026-05-18 23:34:38','2026-05-18 23:34:38'),(470,2,9,'LEGALIZACION_PUNTOS_DE_8a4a525','Legalización puntos de recarga en BT',NULL,NULL,1,'2026-05-18 23:34:46','2026-05-18 23:34:46'),(471,2,9,'MOV_ELECTRICA_IMPLANTA_24aeec6','Mov. Electrica: Implantación hasta 150kw',NULL,NULL,1,'2026-05-18 23:34:46','2026-05-18 23:34:52'),(472,2,9,'DIRECCION_FACULTATIVA_cab5dec','Dirección facultativa obras PDR B.T.',NULL,NULL,1,'2026-05-18 23:34:47','2026-05-18 23:34:47'),(473,2,9,'COORDINACION_DE_SEGURI_e6bb9d3','Coordinación de Seguridad PDR BT',NULL,NULL,1,'2026-05-18 23:34:47','2026-05-18 23:34:47'),(474,2,9,'ELABORACION_DE_VALORAC_091b05f','Elaboración de valoración de la propuesta sin matriz de valoración',NULL,NULL,1,'2026-05-18 23:34:47','2026-05-18 23:34:47'),(475,2,9,'ELABORACION_DE_PROYECT_5c6f854','Elaboración de proyecto técnico (BT)',NULL,NULL,1,'2026-05-18 23:34:47','2026-05-18 23:34:47'),(476,2,9,'GESTION_CIAS_AMPLIACIO_663c293','Gestión Cías: Ampliación de potencia',NULL,NULL,1,'2026-05-18 23:34:47','2026-05-18 23:34:47'),(477,2,9,'DIRECTROR_DE_PROYECTO','Directror de Proyecto',NULL,NULL,1,'2026-05-18 23:34:47','2026-05-18 23:34:47'),(478,2,9,'SOLICITUD_DE_NUEVO_SUMINISTRO','Solicitud de nuevo suministro',NULL,NULL,1,'2026-05-18 23:34:47','2026-05-18 23:34:47'),(479,2,9,'ELABORACION_DE_VALORAC_d845ae9','Elaboración de valoración de la propuesta a partir de matriz de valoración (BT)',NULL,NULL,1,'2026-05-18 23:34:47','2026-05-18 23:34:47'),(480,2,9,'MOV_ELECTRICA_IMPLT_15_f8f4470','Mov. Electrica: Implt + 150kw Varios PdR',NULL,NULL,1,'2026-05-18 23:34:47','2026-05-18 23:34:47'),(481,2,9,'GESTIONES_CON_ORGANISM_b62f64e','Gestiones con Organismos. Sin visita',NULL,NULL,1,'2026-05-18 23:34:47','2026-05-18 23:34:47'),(482,2,9,'OBTENCION_DE_LICENCIAS_ffe4548','Obtención de licencias de obras menores o legalización (modificación no sustancial)',NULL,NULL,1,'2026-05-18 23:34:48','2026-05-18 23:34:48'),(483,2,9,'AMPLIACION_DE_POTENCIA','Ampliación de potencia',NULL,NULL,1,'2026-05-18 23:34:48','2026-05-18 23:34:48'),(484,2,9,'VALORACION_PROPUESTA_A_7f2b27e','Valoración propuesta a partir matriz BT',NULL,NULL,1,'2026-05-18 23:34:49','2026-05-18 23:34:49'),(485,2,9,'TOMA_DE_DATOS_SIN_MED_36780b4','Toma de datos (sin med aux) Península',NULL,NULL,1,'2026-05-18 23:34:49','2026-05-18 23:34:49'),(486,2,9,'PROY_EXTENSION_RED_LIN_04c7617','Proy extensión red línea aérea_MT',NULL,NULL,1,'2026-05-18 23:34:49','2026-05-18 23:34:49'),(487,2,9,'TITULADO_SUPERIOR','Titulado superior',NULL,NULL,1,'2026-05-18 23:34:49','2026-05-18 23:34:49'),(488,2,9,'VIGILANCIA_JORNADA_COMPLETA','Vigilancia: Jornada completa',NULL,NULL,1,'2026-05-18 23:34:49','2026-05-18 23:34:49'),(489,2,9,'LEGALIZACION_PUNTOS_DE_6421cc0','Legalización puntos de recarga en MT/AT',NULL,NULL,1,'2026-05-18 23:34:49','2026-05-18 23:34:49'),(490,2,9,'D_FACULTATIVA_OBRAS_RE_1a0adf9','D.facultativa obras remodelacion L-P',NULL,NULL,1,'2026-05-18 23:34:49','2026-05-18 23:34:49'),(491,2,9,'COORDINACION_SEGURIDAD_2b48d0d','Coordinacion seguridad obras remod L-P',NULL,NULL,1,'2026-05-18 23:34:49','2026-05-18 23:34:49'),(492,2,9,'PROYECTO_DE_LEGALIZACI_1b71871','Proyecto de legalizacion de la actividad',NULL,NULL,1,'2026-05-18 23:34:49','2026-05-18 23:34:49'),(493,2,9,'GESTION_ANTE_ORGANISMO_59decfc','Gestión ante Organismos sin seguimiento',NULL,NULL,1,'2026-05-18 23:34:49','2026-05-18 23:34:49'),(494,2,9,'GESTION_BONIFC_FISCALE_284d2f2','Gestión bonifc. fiscales, ayudas y subv.',NULL,NULL,1,'2026-05-18 23:34:49','2026-05-18 23:34:49'),(495,2,9,'TRAMITACION_DE_ACTOS_C_18e5c31','Tramitación de actos comunicados o declaraciones responsables',NULL,NULL,1,'2026-05-18 23:34:50','2026-05-18 23:34:50'),(496,2,9,'OBTENCION_DE_LICENCIAS_9471896','Obtención de licencias de obras mayores, legalización o tramitación proyecto impacto ambiental',NULL,NULL,1,'2026-05-18 23:34:50','2026-05-18 23:34:50'),(497,2,9,'VISITA_A_OBRA_DE_TENICO_250_KM','Visita a Obra de ténico (<250 Km)',NULL,NULL,1,'2026-05-18 23:34:51','2026-05-18 23:34:51'),(498,2,9,'INCREMENTO_MENSUAL_D_F_6f35d97','Incremento mensual D. Facultativa o Coordinación de Seguridad',NULL,NULL,1,'2026-05-18 23:34:51','2026-05-18 23:34:51'),(499,2,9,'OBTENCION_DE_LICENCIA_06240a6','Obtención de licencia de funcionamiento o licencia de apertura',NULL,NULL,1,'2026-05-18 23:34:51','2026-05-18 23:34:51'),(500,2,9,'PROYECTOS_CON_INCLUSIO_c1684c3','Proyectos con inclusión de baterías',NULL,NULL,1,'2026-05-18 23:34:51','2026-05-18 23:34:51'),(501,2,9,'PROYECTO_TECNICO_DE_CE_aa2e387','Proyecto técnico de centro de seccionamiento (MT)',NULL,NULL,1,'2026-05-18 23:34:51','2026-05-18 23:34:51'),(502,2,9,'ACTUALIZA_BAJA_REG_IND_407ddc1','Actualiza./baja Reg. Ind. U.S/Pto./bases',NULL,NULL,1,'2026-05-18 23:34:52','2026-05-18 23:34:52'),(503,2,9,'GESTIONES','Gestiones ??',NULL,NULL,1,'2026-05-18 23:34:52','2026-05-18 23:34:52'),(504,2,9,'IMPLANTACION_A_PARTIR_7cbee8a','Implantación a partir de 150 kW (varios PdR)',NULL,NULL,1,'2026-05-18 23:34:55','2026-05-18 23:34:55'),(505,2,9,'PROYECTO_ELECTRICO_DE_8900d9f','Proyecto eléctrico de extensión de red por línea subterránea (MT)',NULL,NULL,1,'2026-05-18 23:34:55','2026-05-18 23:34:55'),(506,2,9,'GESTION_DE_BONIFICACIO_8610484','Gestión de bonificaciones fiscales, ayudas y subvenciones',NULL,NULL,1,'2026-05-18 23:34:56','2026-05-18 23:34:56'),(507,2,9,'SIN_CATEGORIA','Sin categoría',NULL,NULL,1,'2026-05-18 23:34:56','2026-05-18 23:34:56');
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
) ENGINE=InnoDB AUTO_INCREMENT=23309 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trabajos`
--

LOCK TABLES `trabajos` WRITE;
/*!40000 ALTER TABLE `trabajos` DISABLE KEYS */;
INSERT INTO `trabajos` VALUES (23293,1,20,7014,1,2,28,35,4,610001,'DEMO-610001','33450',NULL,'La Senyera I - pedido exportable PDF CSV ARIBA','2026-05-06','2026-05-20','[demo-integral-reducida-2026-06-02] La Senyera I - pedido exportable PDF CSV ARIBA',NULL,NULL,'DEMO','Cesar Garcia','pendiente_facturar',0,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(23294,1,20,7014,1,1,28,35,4,610002,'DEMO-610002','33450',NULL,'Alta manual MOEVE sin pedido','2026-05-10',NULL,'[demo-integral-reducida-2026-06-02] Alta manual MOEVE sin pedido',NULL,NULL,'DEMO','Equipo MOEVE','en_curso',0,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(23295,1,20,6592,1,3,28,35,4,610003,'DEMO-610003','20673',NULL,'MOEVE terminado sin pedido','2026-05-11','2026-05-21','[demo-integral-reducida-2026-06-02] MOEVE terminado sin pedido',NULL,NULL,'DEMO','Equipo MOEVE','terminado',0,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(23296,1,20,6592,1,2,28,35,4,610004,'DEMO-610004','20673',NULL,'MOEVE facturado completo listo para cierre','2026-05-12','2026-05-22','[demo-integral-reducida-2026-06-02] MOEVE facturado completo listo para cierre',NULL,NULL,'DEMO','Equipo MOEVE','finalizado',0,'2026-06-02 15:42:26','2026-06-04 09:01:41'),(23297,1,20,7014,1,1,28,35,4,610005,'DEMO-610005','33450',NULL,'MOEVE pedido pendiente de facturar','2026-05-13','2026-05-23','[demo-integral-reducida-2026-06-02] MOEVE pedido pendiente de facturar',NULL,NULL,'DEMO','Equipo MOEVE','pendiente_facturar',0,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(23298,1,20,6592,1,3,28,35,4,610006,'DEMO-610006','20673',NULL,'MOEVE cancelado visible al final','2026-05-14',NULL,'[demo-integral-reducida-2026-06-02] MOEVE cancelado visible al final',NULL,NULL,'DEMO','Equipo MOEVE','cancelado',0,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(23299,2,21,9829,2,5,29,37,5,620001,'DEMO-620001','96322',NULL,'REPSOL pedido pendiente de facturar','2026-05-06','2026-05-20','[demo-integral-reducida-2026-06-02] REPSOL pedido pendiente de facturar',NULL,NULL,'DEMO','Equipo REPSOL','pendiente_facturar',0,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(23300,2,21,9830,2,4,29,37,5,620002,'DEMO-620002','7630',NULL,'REPSOL alta manual sin pedido','2026-05-08',NULL,'[demo-integral-reducida-2026-06-02] REPSOL alta manual sin pedido',NULL,NULL,'DEMO','Equipo REPSOL','en_curso',0,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(23301,2,21,9829,2,5,29,37,5,620003,'DEMO-620003','96322',NULL,'REPSOL terminado sin pedido','2026-05-09','2026-05-21','[demo-integral-reducida-2026-06-02] REPSOL terminado sin pedido',NULL,NULL,'DEMO','Equipo REPSOL','terminado',0,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(23302,2,21,9830,2,4,29,37,5,620004,'DEMO-620004','7630',NULL,'REPSOL pedido para validar lineas','2026-05-10','2026-05-22','[demo-integral-reducida-2026-06-02] REPSOL pedido para validar lineas',NULL,NULL,'DEMO','Equipo REPSOL','pendiente_facturar',0,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(23303,2,21,9829,2,5,29,37,5,620005,'DEMO-620005','96322',NULL,'REPSOL facturacion parcial','2026-05-11','2026-05-23','[demo-integral-reducida-2026-06-02] REPSOL facturacion parcial',NULL,NULL,'DEMO','Equipo REPSOL','pendiente_facturar',0,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(23304,2,21,9830,2,4,29,37,5,620006,'DEMO-620006','7630',NULL,'REPSOL cancelado visible al final','2026-05-12',NULL,'[demo-integral-reducida-2026-06-02] REPSOL cancelado visible al final',NULL,NULL,'DEMO','Equipo REPSOL','cancelado',0,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(23305,3,28,13183,10,6,30,39,1,630001,'DEMO-630001','OTR-001',NULL,'OTROS pedido demo con lineas','2026-05-07','2026-05-20','[demo-integral-reducida-2026-06-02] OTROS pedido demo con lineas',NULL,NULL,'DEMO','Cliente OTROS','pendiente_facturar',0,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(23306,3,28,13184,10,7,30,39,1,630002,'DEMO-630002','OTR-002',NULL,'OTROS alta manual sin pedido','2026-05-09',NULL,'[demo-integral-reducida-2026-06-02] OTROS alta manual sin pedido',NULL,NULL,'DEMO','Cliente OTROS','en_curso',0,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(23307,3,28,13183,10,6,30,39,1,630003,'DEMO-630003','OTR-001',NULL,'OTROS terminado sin pedido','2026-05-12','2026-05-22','[demo-integral-reducida-2026-06-02] OTROS terminado sin pedido',NULL,NULL,'DEMO','Cliente OTROS','terminado',0,'2026-06-02 15:42:26','2026-06-02 15:42:26'),(23308,1,20,7014,NULL,NULL,28,35,NULL,610007,'MOE-610007',NULL,NULL,'QA-UI-20260604-MOE Trabajo validación Playwright','2026-06-04',NULL,'QA-MOEVE-GUARDADO-PARA-TEST-CONCURRENCIA',NULL,NULL,NULL,NULL,'en_curso',0,'2026-06-04 09:08:59','2026-06-04 13:41:35');
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
INSERT INTO `usuarios` VALUES (1,3,NULL,'Administrador','ERP CIETE','admin','admin@ciete.es',NULL,'2026-05-17 22:28:07','$2y$12$7MVKYzq16yNkJaaO2vZcUe66.7I/qJgoca8t5bVFjeY2dImdFzgOy',NULL,'avatar-ciete-logo',NULL,'2026-06-04 16:55:01',1,'ciete_excel','2026-05-17 20:28:07','2026-06-04 14:55:01'),(2,3,NULL,'Cesar','CIETE','cesar','cesar@ciete.es',NULL,'2026-05-17 22:28:07','$2y$12$cdAvzv9SkBAi/f8BcKWJZODDYyBBXM3c04FamZgzO4c8tMwY81AfK',NULL,'avatar-ciete-logo',NULL,'2026-06-04 21:40:54',1,'ciete_excel','2026-05-17 20:28:08','2026-06-04 19:40:54'),(3,3,NULL,'Usuario','Ejecucion','usuario','usuario@ciete.es',NULL,'2026-05-17 22:28:08','$2y$12$wGjVklEkf2zqKChK9B4ijOQlMhVJth/vQLo4zYdVfW8/ygxi5KMlW',NULL,'avatar-ciete-logo',NULL,'2026-06-04 20:46:09',1,'ciete_excel','2026-05-17 20:28:08','2026-06-04 18:46:09'),(4,1,NULL,'Ejecucion','Moeve','moeve','moeve@ciete.es',NULL,'2026-05-17 22:28:08','$2y$12$c6DpQB2dr0qOdXLLVZ83gOc8gccjPDUetASBzk6g.Oyxxk3sbRH8y',NULL,'avatar-ciete-logo',NULL,'2026-06-04 20:43:20',1,'ciete_excel','2026-05-17 20:28:08','2026-06-04 18:43:20'),(5,2,NULL,'Ejecucion','Repsol','repsol','repsol@ciete.es',NULL,'2026-05-17 22:28:08','$2y$12$/uSEhKL40BOvLjx8mwLvo.vQAYliZ66G.utQUNJXZvMxr4FDnPXi6',NULL,'avatar-ciete-logo',NULL,'2026-05-19 00:07:49',1,'ciete_excel','2026-05-17 20:28:08','2026-05-18 22:07:49'),(6,3,NULL,'Usuario','Contable','contable','contable@ciete.es',NULL,'2026-05-17 22:28:08','$2y$12$1WQ5z947S6RyZR7qSMSHie2k58h1cqAl4mkxrNwOD5Ci.GpQw2akC',NULL,'avatar-ciete-logo',NULL,'2026-06-04 16:46:09',1,'ciete_moderno','2026-05-17 20:28:09','2026-06-04 14:46:09');
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

-- Dump completed on 2026-06-04 23:49:23
