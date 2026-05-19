-- abaco_ciete demo reducida pre P1-14 real
-- generated 2026-05-17T22:27:44+00:00
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `audit_log`;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `audit_log` (`id_audit`, `id_contexto`, `id_usuario`, `accion`, `tabla`, `modulo`, `entity_type`, `entity_id`, `registro_id`, `campo`, `valor_anterior`, `valor_nuevo`, `datos_anteriores`, `datos_nuevos`, `descripcion`, `ip`, `ip_address`, `user_agent`, `created_at`) VALUES ('1', '2', '2', 'cambiar_contexto', 'usuarios', 'contexto', 'App\\Models\\User', '2', '2', 'id_contexto', '3', '2', '{\"contexto\":3}', '{\"contexto\":2}', 'Cambio de contexto activo: OTROS CLIENTES -> REPSOL.', '127.0.0.1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-11 07:27:03');
INSERT INTO `audit_log` (`id_audit`, `id_contexto`, `id_usuario`, `accion`, `tabla`, `modulo`, `entity_type`, `entity_id`, `registro_id`, `campo`, `valor_anterior`, `valor_nuevo`, `datos_anteriores`, `datos_nuevos`, `descripcion`, `ip`, `ip_address`, `user_agent`, `created_at`) VALUES ('2', '3', '2', 'actualizar', 'pedidos', 'pedidos', 'App\\Models\\Pedido', '40', '40', 'id_trabajo', '45', '42', NULL, NULL, 'El usuario reasigno el pedido OTR-DEMO-2026-0010 al trabajo 90009 desde la vista Excel.', '127.0.0.1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 12:22:44');
INSERT INTO `audit_log` (`id_audit`, `id_contexto`, `id_usuario`, `accion`, `tabla`, `modulo`, `entity_type`, `entity_id`, `registro_id`, `campo`, `valor_anterior`, `valor_nuevo`, `datos_anteriores`, `datos_nuevos`, `descripcion`, `ip`, `ip_address`, `user_agent`, `created_at`) VALUES ('3', '3', '2', 'cambiar_contexto', 'usuarios', 'contexto', 'App\\Models\\User', '2', '2', 'id_contexto', '3', 'all', '{\"contexto\":3}', '{\"contexto\":\"all\"}', 'Cambio de contexto activo: OTROS CLIENTES -> TODOS.', '127.0.0.1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 12:34:20');
INSERT INTO `audit_log` (`id_audit`, `id_contexto`, `id_usuario`, `accion`, `tabla`, `modulo`, `entity_type`, `entity_id`, `registro_id`, `campo`, `valor_anterior`, `valor_nuevo`, `datos_anteriores`, `datos_nuevos`, `descripcion`, `ip`, `ip_address`, `user_agent`, `created_at`) VALUES ('4', '1', '2', 'cambiar_contexto', 'usuarios', 'contexto', 'App\\Models\\User', '2', '2', 'id_contexto', '3', '1', '{\"contexto\":3}', '{\"contexto\":1}', 'Cambio de contexto activo: OTROS CLIENTES -> MOEVE.', '127.0.0.1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 14:44:22');
INSERT INTO `audit_log` (`id_audit`, `id_contexto`, `id_usuario`, `accion`, `tabla`, `modulo`, `entity_type`, `entity_id`, `registro_id`, `campo`, `valor_anterior`, `valor_nuevo`, `datos_anteriores`, `datos_nuevos`, `descripcion`, `ip`, `ip_address`, `user_agent`, `created_at`) VALUES ('5', '1', '6', 'cambiar_contexto', 'usuarios', 'contexto', 'App\\Models\\User', '6', '6', 'id_contexto', '3', '1', '{\"contexto\":3}', '{\"contexto\":1}', 'Cambio de contexto activo: OTROS CLIENTES -> MOEVE.', '127.0.0.1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 14:44:28');
INSERT INTO `audit_log` (`id_audit`, `id_contexto`, `id_usuario`, `accion`, `tabla`, `modulo`, `entity_type`, `entity_id`, `registro_id`, `campo`, `valor_anterior`, `valor_nuevo`, `datos_anteriores`, `datos_nuevos`, `descripcion`, `ip`, `ip_address`, `user_agent`, `created_at`) VALUES ('6', '1', '6', 'cambiar_contexto', 'usuarios', 'contexto', 'App\\Models\\User', '6', '6', 'id_contexto', '3', '1', '{\"contexto\":3}', '{\"contexto\":1}', 'Cambio de contexto activo: OTROS CLIENTES -> MOEVE.', '127.0.0.1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 20:47:39');

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `cobros`;
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


DROP TABLE IF EXISTS `comentarios_legalizaciones`;
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


DROP TABLE IF EXISTS `comentarios_soporte`;
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


DROP TABLE IF EXISTS `contactos`;
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


DROP TABLE IF EXISTS `contactos_empresas`;
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


DROP TABLE IF EXISTS `contextos_cliente`;
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

INSERT INTO `contextos_cliente` (`id_contexto`, `nombre`, `codigo`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('1', 'MOEVE', 'MOEVE', 'Contexto operativo MOEVE', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `contextos_cliente` (`id_contexto`, `nombre`, `codigo`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('2', 'REPSOL', 'REPSOL', 'Contexto operativo REPSOL', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `contextos_cliente` (`id_contexto`, `nombre`, `codigo`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('3', 'OTROS CLIENTES', 'OTROS', 'Contexto operativo OTROS CLIENTES', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');

DROP TABLE IF EXISTS `contrato_empresas_facturadoras`;
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `contrato_empresas_facturadoras` (`id`, `id_contrato`, `id_empresa`, `id_contexto`, `activo`, `observaciones`, `created_at`, `updated_at`) VALUES ('1', '1', '16', '1', '1', '[muestra-operativa-50] Relación permitida para la muestra; CIF solo si existe en la fuente.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `contrato_empresas_facturadoras` (`id`, `id_contrato`, `id_empresa`, `id_contexto`, `activo`, `observaciones`, `created_at`, `updated_at`) VALUES ('2', '1', '17', '1', '1', '[muestra-operativa-50] Relación permitida para la muestra; CIF solo si existe en la fuente.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `contrato_empresas_facturadoras` (`id`, `id_contrato`, `id_empresa`, `id_contexto`, `activo`, `observaciones`, `created_at`, `updated_at`) VALUES ('3', '2', '18', '1', '1', '[muestra-operativa-50] Relación permitida para la muestra; CIF solo si existe en la fuente.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `contrato_empresas_facturadoras` (`id`, `id_contrato`, `id_empresa`, `id_contexto`, `activo`, `observaciones`, `created_at`, `updated_at`) VALUES ('4', '2', '16', '1', '1', '[muestra-operativa-50] Relación permitida para la muestra; CIF solo si existe en la fuente.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `contrato_empresas_facturadoras` (`id`, `id_contrato`, `id_empresa`, `id_contexto`, `activo`, `observaciones`, `created_at`, `updated_at`) VALUES ('5', '4', '3', '3', '1', '[muestra-operativa-50] Relación permitida para la muestra; CIF solo si existe en la fuente.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `contrato_empresas_facturadoras` (`id`, `id_contrato`, `id_empresa`, `id_contexto`, `activo`, `observaciones`, `created_at`, `updated_at`) VALUES ('6', '4', '7', '3', '1', '[muestra-operativa-50] Relación permitida para la muestra; CIF solo si existe en la fuente.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `contrato_empresas_facturadoras` (`id`, `id_contrato`, `id_empresa`, `id_contexto`, `activo`, `observaciones`, `created_at`, `updated_at`) VALUES ('7', '4', '10', '3', '1', '[muestra-operativa-50] Relación permitida para la muestra; CIF solo si existe en la fuente.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `contrato_empresas_facturadoras` (`id`, `id_contrato`, `id_empresa`, `id_contexto`, `activo`, `observaciones`, `created_at`, `updated_at`) VALUES ('8', '4', '12', '3', '1', '[muestra-operativa-50] Relación permitida para la muestra; CIF solo si existe en la fuente.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');

DROP TABLE IF EXISTS `contratos`;
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `contratos` (`id_contrato`, `id_contexto`, `id_empresa_cliente`, `codigo_contrato`, `nombre`, `tipo`, `fecha_inicio`, `fecha_fin`, `estado`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('1', '1', '1', 'MOEVE-686', 'Contrato Moeve 686', 'marco', '2015-01-01', NULL, 'vigente', '[muestra-operativa-50] Contrato preparado para la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `contratos` (`id_contrato`, `id_contexto`, `id_empresa_cliente`, `codigo_contrato`, `nombre`, `tipo`, `fecha_inicio`, `fecha_fin`, `estado`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('2', '1', '1', 'MOEVE-772', 'Contrato Moeve 772', 'marco', '2024-01-01', NULL, 'vigente', '[muestra-operativa-50] Contrato preparado para la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `contratos` (`id_contrato`, `id_contexto`, `id_empresa_cliente`, `codigo_contrato`, `nombre`, `tipo`, `fecha_inicio`, `fecha_fin`, `estado`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('3', '2', '2', 'REPSOL-MARCO-2024-2027', 'Contrato marco Repsol trabajos Excel 2024-2027', 'marco', '2023-01-01', NULL, 'vigente', '[muestra-operativa-50] Contrato preparado para la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `contratos` (`id_contrato`, `id_contexto`, `id_empresa_cliente`, `codigo_contrato`, `nombre`, `tipo`, `fecha_inicio`, `fecha_fin`, `estado`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('4', '3', '3', 'OTROS-DEMO-2026', 'Contrato demo otros clientes 2026', 'marco', '2026-01-01', NULL, 'vigente', '[muestra-operativa-50] Contrato preparado para la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');

DROP TABLE IF EXISTS `direcciones`;
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


DROP TABLE IF EXISTS `emails`;
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


DROP TABLE IF EXISTS `empresas`;
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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `empresas` (`id_empresa`, `id_contexto`, `empresa_padre_id`, `nombre`, `nombre_comercial`, `razon_social`, `cif`, `tipo_empresa`, `web`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('1', '1', NULL, 'MOEVE', 'MOEVE', 'MOEVE', 'A28003119', 'cliente', NULL, '[muestra-operativa-50] Empresa cliente usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `empresas` (`id_empresa`, `id_contexto`, `empresa_padre_id`, `nombre`, `nombre_comercial`, `razon_social`, `cif`, `tipo_empresa`, `web`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('2', '2', NULL, 'REPSOL', 'REPSOL S.A.', 'REPSOL S.A.', 'A78374725', 'cliente', NULL, '[muestra-operativa-50] Empresa cliente usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `empresas` (`id_empresa`, `id_contexto`, `empresa_padre_id`, `nombre`, `nombre_comercial`, `razon_social`, `cif`, `tipo_empresa`, `web`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('3', '3', NULL, 'Cliente Demo Industrial Norte', 'Cliente Demo Industrial Norte', 'Cliente Demo Industrial Norte', 'B90000001', 'cliente', NULL, '[muestra-operativa-50] Empresa cliente usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `empresas` (`id_empresa`, `id_contexto`, `empresa_padre_id`, `nombre`, `nombre_comercial`, `razon_social`, `cif`, `tipo_empresa`, `web`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('4', '3', NULL, 'Cliente Demo Retail Sur', 'Cliente Demo Retail Sur', 'Cliente Demo Retail Sur', 'B90000002', 'cliente', NULL, '[muestra-operativa-50] Empresa cliente usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `empresas` (`id_empresa`, `id_contexto`, `empresa_padre_id`, `nombre`, `nombre_comercial`, `razon_social`, `cif`, `tipo_empresa`, `web`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('5', '3', NULL, 'Cliente Demo Urbanismo Centro', 'Cliente Demo Urbanismo Centro', 'Cliente Demo Urbanismo Centro', 'B90000003', 'cliente', NULL, '[muestra-operativa-50] Empresa cliente usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `empresas` (`id_empresa`, `id_contexto`, `empresa_padre_id`, `nombre`, `nombre_comercial`, `razon_social`, `cif`, `tipo_empresa`, `web`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('6', '3', NULL, 'Cliente Demo Energía Levante', 'Cliente Demo Energía Levante', 'Cliente Demo Energía Levante', 'B90000004', 'cliente', NULL, '[muestra-operativa-50] Empresa cliente usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `empresas` (`id_empresa`, `id_contexto`, `empresa_padre_id`, `nombre`, `nombre_comercial`, `razon_social`, `cif`, `tipo_empresa`, `web`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('7', '3', NULL, 'Cliente Demo Logística Oeste', 'Cliente Demo Logística Oeste', 'Cliente Demo Logística Oeste', 'B90000005', 'cliente', NULL, '[muestra-operativa-50] Empresa cliente usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `empresas` (`id_empresa`, `id_contexto`, `empresa_padre_id`, `nombre`, `nombre_comercial`, `razon_social`, `cif`, `tipo_empresa`, `web`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('8', '3', NULL, 'Cliente Demo Administración Local', 'Cliente Demo Administración Local', 'Cliente Demo Administración Local', 'B90000006', 'cliente', NULL, '[muestra-operativa-50] Empresa cliente usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `empresas` (`id_empresa`, `id_contexto`, `empresa_padre_id`, `nombre`, `nombre_comercial`, `razon_social`, `cif`, `tipo_empresa`, `web`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('9', '3', NULL, 'Cliente Demo Patrimonial', 'Cliente Demo Patrimonial', 'Cliente Demo Patrimonial', 'B90000007', 'cliente', NULL, '[muestra-operativa-50] Empresa cliente usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `empresas` (`id_empresa`, `id_contexto`, `empresa_padre_id`, `nombre`, `nombre_comercial`, `razon_social`, `cif`, `tipo_empresa`, `web`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('10', '3', NULL, 'Cliente Demo Instalaciones', 'Cliente Demo Instalaciones', 'Cliente Demo Instalaciones', 'B90000008', 'cliente', NULL, '[muestra-operativa-50] Empresa cliente usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `empresas` (`id_empresa`, `id_contexto`, `empresa_padre_id`, `nombre`, `nombre_comercial`, `razon_social`, `cif`, `tipo_empresa`, `web`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('11', '3', NULL, 'Cliente Demo Consultoría Técnica', 'Cliente Demo Consultoría Técnica', 'Cliente Demo Consultoría Técnica', 'B90000009', 'cliente', NULL, '[muestra-operativa-50] Empresa cliente usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `empresas` (`id_empresa`, `id_contexto`, `empresa_padre_id`, `nombre`, `nombre_comercial`, `razon_social`, `cif`, `tipo_empresa`, `web`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('12', '3', NULL, 'Cliente Demo Infraestructura', 'Cliente Demo Infraestructura', 'Cliente Demo Infraestructura', 'B90000010', 'cliente', NULL, '[muestra-operativa-50] Empresa cliente usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `empresas` (`id_empresa`, `id_contexto`, `empresa_padre_id`, `nombre`, `nombre_comercial`, `razon_social`, `cif`, `tipo_empresa`, `web`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('16', '1', NULL, 'CCP', 'CCP', 'CCP', NULL, 'otra', NULL, '[muestra-operativa-50] Sociedad facturadora usada por la muestra; CIF solo si consta en la fuente.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `empresas` (`id_empresa`, `id_contexto`, `empresa_padre_id`, `nombre`, `nombre_comercial`, `razon_social`, `cif`, `tipo_empresa`, `web`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('17', '1', NULL, 'CEPSA', 'CEPSA', 'CEPSA', NULL, 'otra', NULL, '[muestra-operativa-50] Sociedad facturadora usada por la muestra; CIF solo si consta en la fuente.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `empresas` (`id_empresa`, `id_contexto`, `empresa_padre_id`, `nombre`, `nombre_comercial`, `razon_social`, `cif`, `tipo_empresa`, `web`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('18', '1', NULL, 'MV', 'MV', 'MV', NULL, 'otra', NULL, '[muestra-operativa-50] Sociedad facturadora usada por la muestra; CIF solo si consta en la fuente.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');

DROP TABLE IF EXISTS `estaciones_moeve_ext`;
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


DROP TABLE IF EXISTS `estaciones_repsol_ext`;
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


DROP TABLE IF EXISTS `estaciones_servicio`;
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
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('1', '1', '1', '11496', 'Virgen de los Milagros', NULL, NULL, 'Palos de la Frontera', 'Huelva', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('2', '1', '1', '11660', 'Villarejo I', NULL, NULL, 'Villarejo de Salvanés', 'Madrid', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('3', '1', '1', '11853', 'La Salobreja', NULL, NULL, 'Jaén', 'Jaén', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('4', '1', '1', '12848', 'Sobreira', NULL, NULL, 'Sobreira', 'Orense', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('5', '1', '1', '15208', 'Veracruz (Murcia)', NULL, NULL, 'Caravaca de la Cruz', 'Murcia', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('6', '1', '1', '15947', 'Boadilla', NULL, NULL, 'Boadilla del Monte', 'Madrid', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('7', '1', '1', '17157', 'Vilaboa', NULL, NULL, 'Vilaboa', 'Pontevedra', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('8', '1', '1', '19511', 'Araia', NULL, NULL, 'San Román de San Millán', 'Álava', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('9', '1', '1', '20673', 'Virgisa', NULL, NULL, 'Alcorcón', 'Madrid', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('10', '1', '1', '31527', 'S’Agaró', NULL, NULL, 'Sant Feliu de Guíxols', 'Girona', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('11', '1', '1', '31536', 'Ganosa', NULL, NULL, 'Madrid', 'Madrid', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('12', '1', '1', '31592', 'Villeca I', NULL, NULL, 'Leganés', 'Madrid', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('13', '1', '1', '31626', 'San Simón', NULL, NULL, 'Vilaboa', 'Pontevedra', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('14', '1', '1', '33093', 'Aluche', NULL, NULL, 'Madrid', 'Madrid', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('15', '1', '1', '33103', 'Ntra. Sra. de la Nieves', NULL, NULL, 'Arcos de la Frontera', 'Cádiz', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('16', '1', '1', '33467', 'Don Benito', NULL, NULL, 'Don Benito', 'Badajoz', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('17', '1', '1', '33979', 'La Pinada', NULL, NULL, 'Sagunto', 'Valencia', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('18', '1', '1', '7523', 'Brunete', NULL, NULL, 'Madrid', 'Madrid', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('19', '1', '1', '77402', 'San Miguel de Abona', NULL, NULL, 'San Miguel de Abona', 'Tenerife', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('20', '2', '2', '11301', 'CRED SAN ADRIAN', NULL, NULL, 'San Adrián', 'Navarra', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('21', '2', '2', '12141', 'CRED POLIGONO ANTEQUERA', NULL, NULL, 'Antequera', 'Málaga', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('22', '2', '2', '13096', 'CRED CARMONA - AUTOVIA', NULL, NULL, 'Carmona', 'Sevilla', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('23', '2', '2', '15981', 'E.S. G DE RECURSOS ENERGETICOS GUIL', NULL, NULL, 'Ubrique', 'Cádiz', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('24', '2', '2', '31076', 'CRED LA BARROSA', NULL, NULL, 'Chiclana de la Frontera', 'Cádiz', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('25', '2', '2', '33567', 'E.S. JUMCON, S. L.', NULL, NULL, 'Plasencia', 'Cáceres', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('26', '2', '2', '33785', 'SURTIMOVIL ALMURADIE', NULL, NULL, 'Almuradiel', 'Ciudad Real', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('27', '2', '2', '34136', 'GESDEGAS,S.L.', NULL, NULL, 'Mejorada del Campo', 'Madrid', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('28', '2', '2', '6737', 'ROLO S.L.', NULL, NULL, 'Coria', 'Cáceres', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('29', '2', '2', '7022', 'CRED LOECHES', NULL, NULL, 'Loeches', 'Madrid', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('30', '2', '2', '7630', 'E.S. ZOTAJO Y GAS S.L.', NULL, NULL, 'Lora del Río', 'Sevilla', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('31', '2', '2', '94957', 'ES COMBUSTIBLES TORREVIEJA SL', NULL, NULL, 'Torrevieja', 'Alicante', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('32', '2', '2', '96060', 'CRED GALAPAGAR', NULL, NULL, 'Galapagar', 'Madrid', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('33', '2', '2', '96163', 'CDAD.TNPTE.PONIENTE', NULL, NULL, 'Mojonera, La', 'Almería', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('34', '2', '2', '96322', 'CRED A. DE LA MIEL M.I.', NULL, NULL, 'Torremolinos', 'Málaga', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('35', '2', '2', '96795', 'GPV-PAU VALLECAS-139', NULL, NULL, 'Madrid', 'Madrid', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('36', '2', '2', '97120', 'NPV MONCADA', NULL, NULL, 'Moncada', 'Valencia', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('37', '2', '2', 'NPV-MONCADA', 'MONCADA', NULL, NULL, 'Moncada', 'Valencia', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('38', '2', '2', 'NUDO-ABRONIGAL', 'NUDO ABROÑIGAL', NULL, NULL, 'Madrid', 'Madrid', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('39', '3', '3', 'OTR-NORTE-01', 'Planta Norte 01', NULL, NULL, 'Burgos', 'Burgos', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('40', '3', '4', 'OTR-SUR-01', 'Retail Sur 01', NULL, NULL, 'Sevilla', 'Sevilla', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('41', '3', '5', 'OTR-CENTRO-01', 'Parcela Centro 01', NULL, NULL, 'Madrid', 'Madrid', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('42', '3', '6', 'OTR-LEVANTE-01', 'Energía Levante 01', NULL, NULL, 'Valencia', 'Valencia', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('43', '3', '7', 'OTR-OESTE-01', 'Nave Logística Oeste', NULL, NULL, 'Badajoz', 'Badajoz', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('44', '3', '8', 'OTR-ADM-01', 'Edificio Municipal Demo', NULL, NULL, 'Toledo', 'Toledo', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('45', '3', '9', 'OTR-PAT-01', 'Inmueble Patrimonial 01', NULL, NULL, 'Granada', 'Granada', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('46', '3', '10', 'OTR-INS-01', 'Instalación Demo 01', NULL, NULL, 'Zaragoza', 'Zaragoza', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('47', '3', '11', 'OTR-CON-01', 'Oficina Técnica Demo', NULL, NULL, 'Málaga', 'Málaga', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('48', '3', '12', 'OTR-INF-01', 'Infraestructura Demo 01', NULL, NULL, 'A Coruña', 'A Coruña', 'Espana', NULL, NULL, 'Activa', NULL, '[muestra-operativa-50] Estación usada por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');

DROP TABLE IF EXISTS `factura_items`;
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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('1', '2', '1', '1.000', '1315.00', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('2', '7', '2', '1.000', '850.00', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('3', '3', '3', '1.000', '2750.00', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('4', '1', '4', '1.000', '1485.00', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('5', '2', '5', '1.000', '530.00', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('6', '4', '6', '1.000', '1430.00', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('7', '11', '7', '1.000', '340.00', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('8', '5', '8', '1.000', '1014.23', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('9', '6', '9', '1.000', '1014.23', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('10', '6', '10', '1.000', '1014.23', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('11', '8', '11', '1.000', '4715.00', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('12', '10', '12', '1.000', '7015.00', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('13', '14', '13', '1.000', '800.00', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('14', '9', '14', '1.000', '1150.00', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('15', '13', '16', '1.000', '1650.00', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('16', '12', '17', '1.000', '10255.00', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('17', '15', '35', '1.000', '1250.00', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('18', '16', '37', '1.000', '2420.00', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('19', '17', '39', '1.000', '980.00', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `factura_items` (`id_factura_item`, `id_factura`, `id_pedido_item`, `unidades_facturadas`, `importe_facturado`, `observaciones`, `created_at`, `updated_at`) VALUES ('20', '18', '40', '1.000', '1650.00', '[muestra-operativa-50] Relación real factura_item -> pedido_item.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');

DROP TABLE IF EXISTS `facturas`;
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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('1', '1', '6', '1', '1', '16', 'CCP-88725300021', '88725300021', 'CCP', '1', NULL, '2016-06-30', NULL, '1485.00', '1485.00', NULL, NULL, '1485.00', 'emitida', '0', 'CCP', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('2', '1', '1', '1', '1', '16', 'CCP-88725300029', '88725300029', 'CCP', '1', NULL, '2016-08-31', NULL, '1845.00', '1845.00', NULL, NULL, '1845.00', 'emitida', '0', 'CCP', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('3', '1', '2', '1', '1', '16', 'CCP-88725300042', '88725300042', 'CCP', '1', NULL, '2016-12-15', NULL, '2750.00', '2750.00', NULL, NULL, '2750.00', 'emitida', '0', 'CCP', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('4', '1', '3', '1', '1', '16', 'CCP-88725300085', '88725300085', 'CCP', '1', NULL, '2017-09-15', NULL, '1430.00', '1430.00', NULL, NULL, '1430.00', 'emitida', '0', 'CCP', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('5', '1', '15', '2', '1', '16', 'CCP-88725300634', '88725300634', 'CCP', '1', NULL, '2024-10-31', NULL, '1014.23', '1014.23', NULL, NULL, '1014.23', 'emitida', '0', 'CCP', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('6', '1', '16', '2', '1', '16', 'CCP-88725300645', '88725300645', 'CCP', '1', NULL, '2024-12-16', NULL, '2028.46', '2028.46', NULL, NULL, '2028.46', 'emitida', '0', 'CCP', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('7', '1', '5', '1', '1', '17', 'CEPSA-Factura-Ciete-41-17', NULL, 'CEPSA', '1', NULL, '2017-04-18', NULL, '850.00', '850.00', NULL, NULL, '850.00', 'emitida', '0', 'CEPSA', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('8', '1', '9', '2', '1', '18', 'MV-10240000887253000666', '10240000887253000666', 'MV', '1', NULL, '2025-03-17', NULL, '4715.00', '4715.00', NULL, NULL, '4715.00', 'emitida', '0', 'MV', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('9', '1', '11', '2', '1', '18', 'MV-10240000887253000685', '10240000887253000685', 'MV', '1', NULL, '2025-05-16', NULL, '1150.00', '1150.00', NULL, NULL, '1150.00', 'emitida', '0', 'MV', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('10', '1', '18', '2', '1', '18', 'MV-10240000887253000705', '10240000887253000705', 'MV', '1', NULL, '2025-09-15', NULL, '7015.00', '7015.00', NULL, NULL, '7015.00', 'emitida', '0', 'MV', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('11', '1', '8', '2', '1', '18', 'MV-10240000887253000727', '10240000887253000727', 'MV', '1', NULL, '2025-12-15', NULL, '340.00', '340.00', NULL, NULL, '340.00', 'emitida', '0', 'MV', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('12', '1', '13', '2', '1', '18', 'MV-10240000887253000734', '10240000887253000734', 'MV', '1', NULL, '2026-01-31', NULL, '10255.00', '10255.00', NULL, NULL, '10255.00', 'emitida', '0', 'MV', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('13', '1', '12', '2', '1', '18', 'MV-10240000887253000742', '10240000887253000742', 'MV', '1', NULL, '2026-02-27', NULL, '1650.00', '1650.00', NULL, NULL, '1650.00', 'emitida', '0', 'MV', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('14', '1', '10', '2', '1', '18', 'MV-10240000887253000745', '10240000887253000745', 'MV', '1', NULL, '2026-03-16', NULL, '800.00', '800.00', NULL, NULL, '800.00', 'emitida', '0', 'MV', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('15', '3', '44', '4', '3', '3', 'OTR-F-2026-001', NULL, 'OTR', '1', '2026-04-15', '2026-04-15', NULL, '1250.00', '1250.00', NULL, NULL, '1250.00', 'emitida', '0', 'Cliente Demo Industrial Norte', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('16', '3', '47', '4', '7', '7', 'OTR-F-2026-005', NULL, 'OTR', '1', '2026-04-25', '2026-04-25', NULL, '2420.00', '2420.00', NULL, NULL, '2420.00', 'enviada', '0', 'Cliente Demo Logística Oeste', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('17', '3', '46', '4', '10', '10', 'OTR-F-2026-008', NULL, 'OTR', '1', '2026-04-22', '2026-04-22', NULL, '980.00', '980.00', NULL, NULL, '980.00', 'emitida', '0', 'Cliente Demo Instalaciones', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES ('18', '3', '45', '4', '12', '12', 'OTR-F-2026-010', NULL, 'OTR', '1', '2026-05-02', '2026-05-02', NULL, '1650.00', '1650.00', NULL, NULL, '1650.00', 'enviada', '0', 'Cliente Demo Infraestructura', '[muestra-operativa-50] Cabecera auxiliar derivada de factura_items.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');

DROP TABLE IF EXISTS `failed_jobs`;
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


DROP TABLE IF EXISTS `importacion_filas`;
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


DROP TABLE IF EXISTS `importaciones`;
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


DROP TABLE IF EXISTS `job_batches`;
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


DROP TABLE IF EXISTS `jobs`;
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


DROP TABLE IF EXISTS `legalizaciones`;
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


DROP TABLE IF EXISTS `legalizaciones_contactos`;
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


DROP TABLE IF EXISTS `mensajes_internos`;
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


DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('1', '2026_03_24_000001_create_framework_support_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('2', '2026_03_24_000002_create_personal_access_tokens_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('3', '2026_03_24_000010_create_security_core_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('4', '2026_03_24_000015_create_maestros_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('5', '2026_03_24_000020_create_empresas_contactos_base_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('6', '2026_03_24_000025_create_estaciones_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('7', '2026_03_24_000030_create_security_users_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('8', '2026_03_24_000035_create_tarifarios_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('9', '2026_03_24_000040_create_comunicacion_operativa_base_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('10', '2026_03_24_000050_create_trabajos_operativa_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('11', '2026_03_24_000055_create_presupuestos_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('12', '2026_03_24_000060_create_legalizaciones_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('13', '2026_03_24_000070_create_importacion_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('14', '2026_03_24_000080_create_audit_log_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('15', '2026_04_13_000001_add_email_recuperacion_and_create_mensajes_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('16', '2026_04_24_000090_create_support_tables_and_extend_messages', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('17', '2026_04_24_000100_normalize_erp_role_catalog', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('18', '2026_04_30_000110_align_audit_log_structure', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('19', '2026_05_02_000120_create_factura_items_and_update_facturas', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('20', '2026_05_02_000125_add_interface_mode_to_usuarios', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('21', '2026_05_02_165419_add_pendiente_facturar_to_trabajos_estado_enum', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('22', '2026_05_02_170426_drop_personal_access_tokens_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('23', '2026_05_02_180000_add_borrador_and_cancelado_to_pedidos_estado_enum', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('24', '2026_05_05_000130_create_contrato_empresas_facturadoras', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('25', '2026_05_06_000140_cleanup_demo_legacy_schema', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('26', '2026_05_07_000150_add_import_warning_metadata', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('27', '2026_05_07_000160_align_ciete_final_schema_rules', '1');

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `pedido_items`;
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
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('1', '1', '1', '4', 'MOEVE-OTROS', NULL, '\"Toma\" ES. Cambio tit', '1315.00', '1.000', '1315.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('2', '1', '2', '4', 'MOEVE-OTROS', NULL, 'Modificación de registro industrial por cambio de AS', '850.00', '1.000', '850.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('3', '1', '3', '2', 'MOEVE-ESTIMACION', NULL, 'Implantación valorada', '2750.00', '1.000', '2750.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('4', '1', '4', '4', 'MOEVE-OTROS', NULL, '\"Toma\" ES. Cambio tit', '1485.00', '1.000', '1485.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('5', '1', '5', '1', 'MOEVE-CUBIERTA', NULL, 'Informe cubiertas', '530.00', '1.000', '530.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('6', '1', '6', '3', 'MOEVE-IMAGEN', NULL, 'Cambio de imagen', '1430.00', '1.000', '1430.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('7', '1', '7', '6', 'MOEVE-FOTOVOLTAICA', NULL, 'Fotovoltaica - Obra - Bloque 24 Visita extra', '340.00', '1.000', '340.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('8', '1', '8', '11', 'MOEVE-TIENDAS', NULL, 'Córner R´spiro', '1014.23', '1.000', '1014.23', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('9', '1', '9', '11', 'MOEVE-TIENDAS', NULL, 'Córner R´spiro', '1014.23', '1.000', '1014.23', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('10', '1', '10', '11', 'MOEVE-TIENDAS', NULL, 'Córner R´spiro', '1014.23', '1.000', '1014.23', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('11', '1', '11', '7', 'MOEVE-IMAGEN', NULL, 'Cambio de Imagen', '4715.00', '1.000', '4715.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('12', '1', '12', '11', 'MOEVE-TIENDAS', NULL, 'Reforma tienda - Fases I y II', '7015.00', '1.000', '7015.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('13', '1', '13', '9', 'MOEVE-MANTENIMIENTO', NULL, 'Mantenimiento - BT Desfavorable', '800.00', '1.000', '800.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('14', '1', '14', '9', 'MOEVE-MANTENIMIENTO', NULL, 'Mantenimiento - Rejillas', '1150.00', '1.000', '1150.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('15', '1', '15', '11', 'MOEVE-TIENDAS', NULL, 'Reforma interior - Fase obra', '0.00', '1.000', '0.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('16', '1', '16', '9', 'MOEVE-MANTENIMIENTO', NULL, 'Mantenimiento - Pavimento', '1650.00', '1.000', '1650.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('17', '1', '17', '8', 'MOEVE-LEGALIZACIONES', NULL, 'Legalización', '10255.00', '1.000', '10255.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('18', '2', '18', '19', '3012735', '19.4.1', 'Estudio de implantación reforma', '1123.54', '1.000', '1123.54', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('19', '2', '19', '20', '3012744', NULL, 'Estudio implantación lavados KLIN', '460.79', '1.000', '460.79', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('20', '2', '20', '17', '3012704', NULL, 'Alternativa implantación ES sin alzados edif no normalizado', '493.40', '1.000', '493.40', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('21', '2', '21', '16', '3012673', NULL, 'Certificado de compatibilidad urbanística', '734.84', '1.000', '734.84', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('22', '2', '22', '25', '3046492', NULL, 'Tramitación de actos comunicados o declaraciones responsables', '276.15', '1.000', '276.15', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('23', '2', '23', '18', '3012735', NULL, 'Estudio de implantación / reforma. Incluye toma de datos', '1123.54', '1.000', '1123.54', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('24', '2', '24', '14', '3012637', NULL, 'Visita a Obra de técnico (<250 Km)', '293.46', '1.000', '293.46', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('25', '2', '25', '27', '3052267', NULL, 'Tramitación actos comunicados o declarac respons', '276.15', '1.000', '276.15', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('26', '2', '26', '13', '3012494', NULL, 'Obtención de licencias de obras mayores o lega', '836.68', '1.000', '836.68', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('27', '2', '27', '15', '3012652', NULL, 'Obtención de licencias de obras mayores o lega', '836.68', '1.000', '836.68', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('28', '2', '28', '29', '3061002', NULL, 'Estudio de sombras e implantación de paneles en 2 posiciones', '142.28', '1.000', '142.28', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('29', '2', '29', '24', '3012797', NULL, 'Titulado Superior', '37.20', '30.000', '1115.89', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('30', '2', '30', '22', '3012753', NULL, 'Actualización registro industrial', '716.48', '1.000', '716.48', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('31', '2', '31', '12', '3012492', NULL, 'Gestiones con Organismos. Sin visita (pedir doc a Industria)', '109.77', '1.000', '109.77', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('32', '2', '32', '28', '3052267', '1.7', 'Tramitación de actos comunicados o declaraciones responsables', '276.15', '1.000', '276.15', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('33', '2', '33', '21', '3012747', '19.7.1', 'Estudio de implantación / reforma con mediciones', '453.33', '1.000', '453.33', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('34', '2', '34', '26', '3052254', NULL, 'Legalización puntos de recarga en BT', '451.90', '1.000', '451.90', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('35', '3', '35', '35', 'OTR-ING-01', NULL, 'Informe técnico industrial', '1250.00', '1.000', '1250.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('36', '3', '36', '41', 'OTR-URB-01', NULL, 'Consulta urbanística', '680.00', '1.000', '680.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('37', '3', '37', '38', 'OTR-LOG-01', NULL, 'Proyecto básico de nave', '2420.00', '1.000', '2420.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('38', '3', '38', '40', 'OTR-PAT-01', NULL, 'Informe de estado general', '540.00', '1.000', '540.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('39', '3', '39', '36', 'OTR-INS-01', NULL, 'Legalización eléctrica', '980.00', '1.000', '980.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES ('40', '3', '40', '34', 'OTR-INF-01', NULL, 'Dirección facultativa', '1650.00', '1.000', '1650.00', '2026-05-08 04:00:59', '2026-05-08 04:00:59');

DROP TABLE IF EXISTS `pedidos`;
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
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('1', '1', '4', '1', '400243862', '2016-07-26', '2016-07-26', '1315.00', '1315.00', '1315.00', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('2', '1', '5', '1', '400190035', '2015-11-24', '2015-11-24', '850.00', '850.00', '850.00', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('3', '1', '2', '1', '400262083', '2016-11-28', '2016-11-28', '2750.00', '2750.00', '2750.00', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('4', '1', '6', '1', '400233671', '2016-06-03', '2016-06-03', '1485.00', '1485.00', '1485.00', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('5', '1', '1', '1', '400243490', '2016-04-27', '2016-04-27', '530.00', '530.00', '530.00', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('6', '1', '3', '1', '400239164', '2017-08-24', '2017-08-24', '1430.00', '1430.00', '1430.00', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('7', '1', '8', '2', '300109709', '2024-07-08', '2024-07-08', '340.00', '340.00', '340.00', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('8', '1', '15', '2', '300095780', '2024-06-19', '2024-06-19', '1014.23', '1014.23', '1014.23', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('9', '1', '16', '2', '300101590', '2024-07-15', '2024-07-15', '1014.23', '1014.23', '1014.23', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('10', '1', '17', '2', '300108063', '2024-10-10', '2024-10-10', '1014.23', '1014.23', '1014.23', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('11', '1', '9', '2', '300108684', '2024-12-02', '2024-12-02', '4715.00', '4715.00', '4715.00', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('12', '1', '18', '2', '300115486', '2025-01-09', '2025-01-09', '7015.00', '7015.00', '7015.00', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('13', '1', '10', '2', '7240363874', '2025-02-18', '2025-02-18', '800.00', '800.00', '800.00', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('14', '1', '11', '2', '7240331513', '2025-03-18', '2025-03-18', '1150.00', '1150.00', '1150.00', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('15', '1', '19', '2', '300120873', '2025-03-18', '2025-03-18', '0.00', NULL, NULL, '1.000', NULL, 'anulado', '1', '0', '0', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('16', '1', '12', '2', '7240361416', '2025-11-21', '2025-11-21', '1650.00', '1650.00', '1650.00', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('17', '1', '13', '2', '300142576', '2025-11-21', '2025-11-21', '10255.00', '10255.00', '10255.00', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('18', '2', '21', '3', '7200033371', '2024-01-30', '2023-12-04', '1123.54', '1123.54', '1123.54', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('19', '2', '22', '3', '7200033370', '2024-01-30', '2023-12-15', '460.79', '460.79', '460.79', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('20', '2', '23', '3', '7200033375', '2024-01-30', '2023-12-19', '493.40', '493.40', '493.40', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('21', '2', '24', '3', '7200033663', '2024-03-19', '2023-10-30', '734.84', '734.84', '734.84', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('22', '2', '25', '3', '7300576913', '2024-05-23', '2023-12-11', '276.15', '276.15', '276.15', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('23', '2', '26', '3', '7200036301', '2024-12-03', '2024-05-08', '1123.54', NULL, NULL, '1.000', NULL, 'anulado', '1', '0', '0', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('24', '2', '27', '3', '7300594140', NULL, '2024-11-14', '293.46', NULL, NULL, '1.000', NULL, 'anulado', '1', '0', '0', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('25', '2', '35', '3', '7200034983', '2024-07-22', '2024-02-20', '276.15', '276.15', '276.15', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('26', '2', '28', '3', '7300614004', '2025-02-18', '2025-02-18', '836.68', '836.68', '836.68', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('27', '2', '36', '3', '7200038943', '2025-06-12', '2024-02-20', '836.68', NULL, NULL, '1.000', NULL, 'solicitado', '1', '0', '0', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('28', '2', '29', '3', '7200034666', NULL, '2023-01-22', '142.28', '142.28', '142.28', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('29', '2', '31', '3', '7200034009', '2024-04-02', '2023-12-11', '1115.89', '1115.89', '1115.89', '30.000', '30.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('30', '2', '37', '3', '7300564645', '2024-02-15', '2023-12-21', '716.48', '716.48', '716.48', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('31', '2', '38', '3', '7300581067', '2024-01-03', '2024-01-15', '109.77', '109.77', '109.77', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('32', '2', '32', '3', '7300639881', '2025-06-27', '2024-09-02', '276.15', '276.15', '276.15', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('33', '2', '33', '3', '7200038950', NULL, '2024-05-14', '453.33', '453.33', '453.33', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('34', '2', '39', '3', '7300634850', NULL, NULL, '451.90', '451.90', '451.90', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('35', '3', '44', '4', 'OTR-DEMO-2026-0001', '2026-04-01', '2026-04-01', '1250.00', '1250.00', '1250.00', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('36', '3', '50', '4', 'OTR-DEMO-2026-0003', '2026-04-03', '2026-04-03', '680.00', NULL, NULL, '1.000', NULL, 'recibido', '1', '0', '0', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('37', '3', '47', '4', 'OTR-DEMO-2026-0005', '2026-04-05', '2026-04-05', '2420.00', '2420.00', '2420.00', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('38', '3', '48', '4', 'OTR-DEMO-2026-0007', '2026-04-07', '2026-04-07', '540.00', NULL, NULL, '1.000', NULL, 'recibido', '1', '0', '0', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('39', '3', '46', '4', 'OTR-DEMO-2026-0008', '2026-04-08', '2026-04-08', '980.00', '980.00', '980.00', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES ('40', '3', '42', '4', 'OTR-DEMO-2026-0010', '2026-04-10', '2026-04-10', '1650.00', '1650.00', '1650.00', '1.000', '1.000', 'facturado', '1', '0', '1', '[muestra-operativa-50] Pedido creado desde staging controlado.', '2026-05-08 04:00:59', '2026-05-17 12:22:44');

DROP TABLE IF EXISTS `permisos`;
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

INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('1', 'Ver usuarios', 'usuarios.ver', 'Consulta de usuarios', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('2', 'Gestionar usuarios', 'usuarios.gestionar', 'Alta, baja y edicion de usuarios', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('3', 'Gestionar roles', 'roles.gestionar', 'Gestion de roles y permisos', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('4', 'Ver trabajos', 'trabajos.ver', 'Consulta de trabajos', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('5', 'Crear trabajos', 'trabajos.crear', 'Creacion de trabajos', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('6', 'Editar trabajos', 'trabajos.editar', 'Edicion de trabajos', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('7', 'Finalizar trabajos', 'trabajos.finalizar', 'Finalizacion funcional de trabajos', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('11', 'Ver presupuestos', 'presupuestos.ver', 'Consulta de presupuestos', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('12', 'Gestionar presupuestos', 'presupuestos.gestionar', 'Creacion y edicion de presupuestos', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('13', 'Ver pedidos', 'pedidos.ver', 'Consulta de pedidos', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('15', 'Ver facturas', 'facturas.ver', 'Consulta de facturas', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('17', 'Gestionar cobros', 'cobros.gestionar', 'Registro y conciliacion de cobros', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('18', 'Ver legalizaciones', 'legalizaciones.ver', 'Consulta de legalizaciones', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('19', 'Gestionar legalizaciones', 'legalizaciones.gestionar', 'Gestion de legalizaciones', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('20', 'Ver estaciones', 'estaciones.ver', 'Consulta de estaciones de servicio', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('22', 'Ver tarifarios', 'tarifarios.ver', 'Consulta de tarifarios', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('23', 'Gestionar tarifarios', 'tarifarios.gestionar', 'Gestion de tarifarios', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('25', 'Ver reportes', 'reportes.ver', 'Consulta de reportes', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('26', 'Ver importaciones', 'importaciones.ver', 'Consulta del historial de importaciones', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('27', 'Ejecutar importaciones', 'importaciones.ejecutar', 'Ejecutar importaciones de datos', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('28', 'Ver auditoria', 'auditoria.ver', 'Consulta del log de auditoria', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('29', 'Gestionar configuracion', 'config.gestionar', 'Gestion de maestros y configuracion del sistema', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('30', 'Ver contratos', 'contratos.ver', 'Consulta de contratos', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('31', 'Gestionar contratos', 'contratos.gestionar', 'Gestion de contratos', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('32', 'Crear usuarios', 'usuarios.crear', 'Alta de usuarios', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('33', 'Editar usuarios', 'usuarios.editar', 'Edicion, activacion y baja logica de usuarios', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('34', 'Eliminar trabajos', 'trabajos.eliminar', 'Cancelacion o eliminacion restringida de trabajos', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('35', 'Editar trabajo finalizado', 'trabajos.editar_finalizado', 'Edicion excepcional de trabajos finalizados', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('36', 'Cambiar estado de trabajos', 'trabajos.cambiar_estado', 'Cambio de estado operativo de trabajos', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('37', 'Marcar trabajos terminados', 'trabajos.marcar_terminado', 'Marcado tecnico de trabajos como terminados', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('38', 'Crear pedidos', 'pedidos.crear', 'Alta de pedidos', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('39', 'Editar pedidos', 'pedidos.editar', 'Edicion de pedidos', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('40', 'Eliminar pedidos', 'pedidos.eliminar', 'Cancelacion o eliminacion restringida de pedidos', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('41', 'Crear facturas', 'facturas.crear', 'Alta de facturas', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('42', 'Editar facturas', 'facturas.editar', 'Edicion de facturas', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('43', 'Eliminar facturas', 'facturas.eliminar', 'Anulacion o eliminacion restringida de facturas', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('44', 'Crear estaciones', 'estaciones.crear', 'Alta de estaciones', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('45', 'Editar estaciones', 'estaciones.editar', 'Edicion de estaciones', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('46', 'Eliminar estaciones', 'estaciones.eliminar', 'Desactivacion o eliminacion restringida de estaciones', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('47', 'Ver clientes', 'clientes.ver', 'Consulta de clientes y empresas contextuales', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('48', 'Crear clientes', 'clientes.crear', 'Alta de clientes y empresas contextuales', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('49', 'Editar clientes', 'clientes.editar', 'Edicion de clientes y empresas contextuales', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('50', 'Eliminar clientes', 'clientes.eliminar', 'Desactivacion o eliminacion restringida de clientes', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('51', 'Exportar auditoria', 'auditoria.exportar', 'Exportacion del registro de auditoria', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('52', 'Limpiar auditoria', 'auditoria.limpiar', 'Limpieza controlada del registro de auditoria', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('53', 'Confirmar importaciones', 'importaciones.confirmar', 'Confirmacion de importaciones revisadas', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('54', 'Gestionar soporte', 'soporte.gestionar', 'Gestion tecnica de solicitudes de soporte', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('55', 'Exportar facturas', 'facturas.exportar', 'Exportacion de listados y detalle de facturas', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('56', 'Ver maestros', 'maestros.ver', 'Acceso al panel separado de datos maestros', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('57', 'Gestionar maestros', 'maestros.gestionar', 'Gestion global de datos maestros', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('58', 'Crear contratos', 'contratos.crear', 'Alta de contratos maestros', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('59', 'Editar contratos', 'contratos.editar', 'Edicion de contratos maestros', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('60', 'Eliminar contratos', 'contratos.eliminar', 'Desactivacion de contratos maestros', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('61', 'Ver sociedades facturadoras permitidas', 'sociedades_facturadoras.ver', 'Consulta de sociedades facturadoras permitidas por contrato', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('62', 'Crear sociedades facturadoras permitidas', 'sociedades_facturadoras.crear', 'Asignacion de sociedades facturadoras a contratos', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('63', 'Editar sociedades facturadoras permitidas', 'sociedades_facturadoras.editar', 'Edicion de sociedades facturadoras permitidas', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('64', 'Eliminar sociedades facturadoras permitidas', 'sociedades_facturadoras.eliminar', 'Desactivacion de sociedades facturadoras permitidas', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('65', 'Crear tarifarios', 'tarifarios.crear', 'Alta de tarifarios maestros', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('66', 'Editar tarifarios', 'tarifarios.editar', 'Edicion de tarifarios maestros', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('67', 'Eliminar tarifarios', 'tarifarios.eliminar', 'Desactivacion de tarifarios maestros', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('68', 'Ver lineas de tarifario', 'tarifario_lineas.ver', 'Consulta de lineas de tarifario', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('69', 'Crear lineas de tarifario', 'tarifario_lineas.crear', 'Alta de lineas de tarifario', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('70', 'Editar lineas de tarifario', 'tarifario_lineas.editar', 'Edicion de lineas de tarifario', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('71', 'Eliminar lineas de tarifario', 'tarifario_lineas.eliminar', 'Desactivacion de lineas de tarifario', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');

DROP TABLE IF EXISTS `presupuesto_lineas`;
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


DROP TABLE IF EXISTS `presupuestos`;
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


DROP TABLE IF EXISTS `rol_permisos`;
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

INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('1', '1', '51', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('2', '1', '52', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('3', '1', '28', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('4', '1', '48', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('5', '1', '49', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('6', '1', '50', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('7', '1', '47', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('8', '1', '17', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('9', '1', '29', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('10', '1', '58', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('11', '1', '59', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('12', '1', '60', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('13', '1', '31', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('14', '1', '30', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('15', '1', '44', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('16', '1', '45', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('17', '1', '46', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('18', '1', '20', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('19', '1', '41', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('20', '1', '42', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('21', '1', '43', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('22', '1', '55', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('23', '1', '15', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('24', '1', '53', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('25', '1', '27', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('26', '1', '26', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('27', '1', '19', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('28', '1', '18', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('29', '1', '57', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('30', '1', '56', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('31', '1', '38', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('32', '1', '39', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('33', '1', '40', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('34', '1', '13', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('35', '1', '12', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('36', '1', '11', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('37', '1', '25', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('38', '1', '3', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('39', '1', '62', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('40', '1', '63', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('41', '1', '64', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('42', '1', '61', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('43', '1', '54', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('44', '1', '69', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('45', '1', '70', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('46', '1', '71', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('47', '1', '68', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('48', '1', '65', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('49', '1', '66', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('50', '1', '67', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('51', '1', '23', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('52', '1', '22', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('53', '1', '36', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('54', '1', '5', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('55', '1', '6', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('56', '1', '35', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('57', '1', '34', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('58', '1', '7', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('59', '1', '37', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('60', '1', '4', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('61', '1', '32', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('62', '1', '33', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('63', '1', '2', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('64', '1', '1', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('65', '2', '4', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('66', '2', '5', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('67', '2', '6', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('68', '2', '36', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('69', '2', '37', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('70', '2', '11', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('71', '2', '12', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('72', '2', '20', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('73', '2', '47', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('74', '2', '13', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('75', '2', '38', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('76', '2', '39', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('77', '3', '1', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('78', '3', '32', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('79', '3', '33', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('80', '3', '2', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('81', '3', '4', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('82', '3', '5', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('83', '3', '6', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('84', '3', '7', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('85', '3', '34', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('86', '3', '35', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('87', '3', '36', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('88', '3', '37', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('89', '3', '13', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('90', '3', '38', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('91', '3', '39', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('92', '3', '40', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('93', '3', '15', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('94', '3', '41', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('95', '3', '42', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('96', '3', '43', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('97', '3', '55', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('98', '3', '20', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('99', '3', '44', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('100', '3', '45', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('101', '3', '46', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('102', '3', '47', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('103', '3', '48', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('104', '3', '49', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('105', '3', '50', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('106', '3', '56', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('107', '3', '57', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('108', '3', '30', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('109', '3', '58', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('110', '3', '59', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('111', '3', '60', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('112', '3', '61', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('113', '3', '62', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('114', '3', '63', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('115', '3', '64', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('116', '3', '22', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('117', '3', '65', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('118', '3', '66', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('119', '3', '67', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('120', '3', '68', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('121', '3', '69', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('122', '3', '70', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('123', '3', '71', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('124', '3', '28', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('125', '3', '51', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('126', '3', '52', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('127', '4', '4', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('128', '4', '5', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('129', '4', '6', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('130', '4', '36', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('131', '4', '37', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('132', '4', '11', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('133', '4', '12', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('134', '4', '20', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('135', '4', '47', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('136', '4', '13', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('137', '4', '38', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('138', '4', '39', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('139', '5', '4', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('140', '5', '5', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('141', '5', '6', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('142', '5', '36', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('143', '5', '37', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('144', '5', '11', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('145', '5', '12', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('146', '5', '20', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('147', '5', '47', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('148', '5', '13', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('149', '5', '38', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('150', '5', '39', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('151', '6', '4', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('152', '6', '13', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('153', '6', '15', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('154', '6', '41', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('155', '6', '42', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('156', '6', '43', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('157', '6', '55', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('158', '6', '30', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('159', '6', '61', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('160', '6', '22', '2026-05-08 02:00:57');
INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES ('161', '6', '68', '2026-05-08 02:00:57');

DROP TABLE IF EXISTS `roles`;
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

INSERT INTO `roles` (`id_rol`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('1', 'Admin', 'admin', 'Acceso total al ERP', '1', '2026-05-08 02:00:56', '2026-05-08 02:00:56');
INSERT INTO `roles` (`id_rol`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('2', 'Ejecución', 'ejecucion', 'Perfil operativo general con acceso a los contextos asignados', '1', '2026-05-08 02:00:56', '2026-05-08 02:00:57');
INSERT INTO `roles` (`id_rol`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('3', 'Dirección', 'director', 'Supervisión global, revisión y control de cierre operativo', '1', '2026-05-08 02:00:56', '2026-05-08 02:00:56');
INSERT INTO `roles` (`id_rol`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('4', 'Ejecución Moeve', 'ejecucion_moeve', 'Perfil operativo restringido al contexto Moeve/Cepsa', '1', '2026-05-08 02:00:56', '2026-05-08 02:00:56');
INSERT INTO `roles` (`id_rol`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('5', 'Ejecución Repsol', 'ejecucion_repsol', 'Perfil operativo restringido al contexto Repsol', '1', '2026-05-08 02:00:56', '2026-05-08 02:00:56');
INSERT INTO `roles` (`id_rol`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('6', 'Contabilidad', 'contable', 'Perfil económico para pedidos y facturas', '1', '2026-05-08 02:00:56', '2026-05-08 02:00:57');

DROP TABLE IF EXISTS `sesiones_login`;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sesiones_login` (`id_sesion`, `id_usuario`, `id_contexto`, `fecha_hora_login`, `fecha_hora_logout`, `ip`, `user_agent`, `observaciones`, `created_at`, `updated_at`) VALUES ('1', '2', '3', '2026-05-08 02:01:42', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, '2026-05-08 02:01:42', '2026-05-08 02:01:42');
INSERT INTO `sesiones_login` (`id_sesion`, `id_usuario`, `id_contexto`, `fecha_hora_login`, `fecha_hora_logout`, `ip`, `user_agent`, `observaciones`, `created_at`, `updated_at`) VALUES ('2', '2', '3', '2026-05-11 07:22:11', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, '2026-05-11 07:22:11', '2026-05-11 07:22:11');
INSERT INTO `sesiones_login` (`id_sesion`, `id_usuario`, `id_contexto`, `fecha_hora_login`, `fecha_hora_logout`, `ip`, `user_agent`, `observaciones`, `created_at`, `updated_at`) VALUES ('3', '2', '3', '2026-05-17 11:56:25', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-17 11:56:25', '2026-05-17 11:56:25');
INSERT INTO `sesiones_login` (`id_sesion`, `id_usuario`, `id_contexto`, `fecha_hora_login`, `fecha_hora_logout`, `ip`, `user_agent`, `observaciones`, `created_at`, `updated_at`) VALUES ('4', '6', '3', '2026-05-17 12:36:25', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-17 12:36:25', '2026-05-17 12:36:25');
INSERT INTO `sesiones_login` (`id_sesion`, `id_usuario`, `id_contexto`, `fecha_hora_login`, `fecha_hora_logout`, `ip`, `user_agent`, `observaciones`, `created_at`, `updated_at`) VALUES ('5', '2', '3', '2026-05-17 14:44:11', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-17 14:44:11', '2026-05-17 14:44:11');
INSERT INTO `sesiones_login` (`id_sesion`, `id_usuario`, `id_contexto`, `fecha_hora_login`, `fecha_hora_logout`, `ip`, `user_agent`, `observaciones`, `created_at`, `updated_at`) VALUES ('6', '6', '3', '2026-05-17 19:08:04', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-17 19:08:04', '2026-05-17 19:08:04');

DROP TABLE IF EXISTS `sessions`;
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

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('CiPJMFpja1SyzrcFdsqsawOfgM8zrQA0rSDWqNfa', '2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiZ2V4OFBLTHoxNUwyak5rZHNicEN6enhvWE9rUjVBcndEelNmQWVQWiI7czo2OiJsb2NhbGUiO3M6MjoiZXMiO3M6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM2OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vdXN1YXJpb3MiO3M6NToicm91dGUiO3M6MTc6ImFkbWluLnVzZXJzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MjtzOjE1OiJzZXNpb25fbG9naW5faWQiO2k6MztzOjU6ImNpZXRlIjthOjE6e3M6MTQ6ImFjdGl2ZV9jb250ZXh0IjtzOjM6ImFsbCI7fX0=', '1779021838');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('cYj7MXm19rA3YAhA85wRZOUGkBqVmCUyRk0wmrR5', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSXVhSzQzYjZoRGY4R2E5S1Q0OWR1MmNtQU95UGtMbzN5N2l6ODliNyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMDoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2ZhY3R1cmFzIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NjoibG9jYWxlIjtzOjI6ImVzIjt9', '1779056782');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('dPjUJx5Capnbj8z95HiqKuboPlB85E8zcfEc5RNq', '6', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiQnJWRmZDbGlIMU5CV0pSUjh5SGRyQ0J2M3U3NzduRU5ucjhIQmp1ZyI7czozOiJ1cmwiO2E6MDp7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NjoibG9jYWxlIjtzOjI6ImVzIjtzOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo2O3M6MTU6InNlc2lvbl9sb2dpbl9pZCI7aTo2O3M6NToiY2lldGUiO2E6MTp7czoxNDoiYWN0aXZlX2NvbnRleHQiO2k6MTt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9mYWN0dXJhcyI7czo1OiJyb3V0ZSI7czoxNDoiZmFjdHVyYXMuaW5kZXgiO319', '1779056782');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('jE67hrHlJgwHwxkGiSK1NmkJJzBYK4EBDu2JbvRL', '2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiY0tjb3N1WnFDeU1vNzRuWURtMEZGYzBScHJYUHFCQUVrdDR2bmExSiI7czozOiJ1cmwiO2E6MDp7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NjoibG9jYWxlIjtzOjI6ImVzIjtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czozMDoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2ZhY3R1cmFzIjtzOjU6InJvdXRlIjtzOjE0OiJmYWN0dXJhcy5pbmRleCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7czoxNToic2VzaW9uX2xvZ2luX2lkIjtpOjU7czo1OiJjaWV0ZSI7YToxOntzOjE0OiJhY3RpdmVfY29udGV4dCI7aToxO319', '1779029654');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('kLsmpKW7b0O4y3MhSN5zxeuX2dEzYck5IitZMTSm', '6', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiS3Z4NmtkOWVidW82T3FKeFRzRTdnVDRKNktjYUpQQ01VM21GNXNFTyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjMwOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZmFjdHVyYXMiO3M6NToicm91dGUiO3M6MTQ6ImZhY3R1cmFzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo2OiJsb2NhbGUiO3M6MjoiZXMiO3M6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjY7czoxNToic2VzaW9uX2xvZ2luX2lkIjtpOjQ7czo1OiJjaWV0ZSI7YToxOntzOjE0OiJhY3RpdmVfY29udGV4dCI7aToxO319', '1779030195');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('kwVDlJS1Wy43W13ia3aWK5tNFNg11vNX1ulmulLo', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoib2RoV3d1MnRSbGVNMHpxNXk2dE80ZnZKQU9TR0dmM1ZFNFEwZXdIRiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NjoibG9jYWxlIjtzOjI6ImVzIjt9', '1779018968');

DROP TABLE IF EXISTS `solicitudes_soporte`;
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


DROP TABLE IF EXISTS `tarifario_lineas`;
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
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('1', '1', '1', 'MOEVE-CUBIERTA', 'Cubierta', 'Informe cubiertas', 'Informe cubiertas', NULL, '530.00', '530.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('2', '1', '1', 'MOEVE-ESTIMACION', 'Estimación', 'Implantación valorada', 'Implantación valorada', NULL, '2750.00', '2750.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('3', '1', '1', 'MOEVE-IMAGEN', 'Imagen', 'Cambio de imagen', 'Cambio de imagen', NULL, '1430.00', '1430.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('4', '1', '1', 'MOEVE-OTROS', 'Otros', '\"Toma\" ES. Cambio tit', '\"Toma\" ES. Cambio tit', NULL, '1485.00', '1485.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('5', '1', '1', 'MOEVE-SYS', 'SyS', 'Supervisión y CSS mensual', 'Supervisión y CSS mensual', NULL, '0.00', '0.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('6', '1', '2', 'MOEVE-FOTOVOLTAICA', 'Fotovoltaica', 'Fotovoltaica - Obra - Bloque 24 Visita extra', 'Fotovoltaica - Obra - Bloque 24 Visita extra', NULL, '340.00', '340.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('7', '1', '2', 'MOEVE-IMAGEN', 'Imagen', 'Cambio de Imagen', 'Cambio de Imagen', NULL, '4715.00', '4715.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('8', '1', '2', 'MOEVE-LEGALIZACIONES', 'Legalizaciones', 'Legalización', 'Legalización', NULL, '10255.00', '10255.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('9', '1', '2', 'MOEVE-MANTENIMIENTO', 'Mantenimiento', 'Mantenimiento - BT Desfavorable', 'Mantenimiento - BT Desfavorable', NULL, '1650.00', '1650.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('10', '1', '2', 'MOEVE-SONDAS', 'Sondas', 'Sondas CODO PUES', 'Sondas CODO PUES', NULL, '0.00', '0.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('11', '1', '2', 'MOEVE-TIENDAS', 'Tiendas', 'Córner R´spiro', 'Córner R´spiro', NULL, '7015.00', '7015.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('12', '2', '3', '3012492', 'Mantenimiento', 'Gestiones con Organismos. Sin visita', 'Gestiones con Organismos. Sin visita (pedir doc a Industria)', NULL, '109.77', '109.77', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('13', '2', '3', '3012494', 'Licencias', 'Obtención de licencias de obras mayores o lega', 'Obtención de licencias de obras mayores o lega', NULL, '836.68', '836.68', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('14', '2', '3', '3012637', 'Edificación', 'Visita a Obra de técnico (<250 Km)', 'Visita a Obra de técnico (<250 Km)', NULL, '293.46', '293.46', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('15', '2', '3', '3012652', 'Licencias', 'Obtención de licencias de obras mayores o lega', 'Obtención de licencias de obras mayores o lega', NULL, '836.68', '836.68', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('16', '2', '3', '3012673', 'Diseño', 'Certificado de compatibilidad urbanística', 'Certificado de compatibilidad urbanística', NULL, '734.84', '734.84', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('17', '2', '3', '3012704', 'Diseño', 'Alternativa implantación ES sin alzados edif no normalizado', 'Alternativa implantación ES sin alzados edif no normalizado', NULL, '493.40', '493.40', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('18', '2', '3', '3012735', 'Edificación', 'Estudio de implantación / reforma. Incluye toma de datos', 'Estudio de implantación / reforma. Incluye toma de datos', NULL, '1123.54', '1123.54', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('19', '2', '3', '3012735-19.4.1', 'Diseño', '19.4.1 Estudio de implantación reforma', 'Estudio de implantación reforma', NULL, '1123.54', '1123.54', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('20', '2', '3', '3012744', 'Diseño', 'Estudio implantación lavados KLIN', 'Estudio implantación lavados KLIN', NULL, '460.79', '460.79', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('21', '2', '3', '3012747-19.7.1', 'Obras', 'Estudio de implantación / reforma con mediciones', 'Estudio de implantación / reforma con mediciones', NULL, '453.33', '453.33', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('22', '2', '3', '3012753', 'Mantenimiento', 'Actualización registro industrial', 'Actualización registro industrial', NULL, '716.48', '716.48', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('23', '2', '3', '3012753-43.2', 'Obras', 'E.S.', 'E.S.', NULL, '716.48', '716.48', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('24', '2', '3', '3012797', 'Estructuras', 'Titulado Superior', 'Titulado Superior', NULL, '37.20', '37.20', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('25', '2', '3', '3046492', 'Edificación', 'Tramitación de actos comunicados o declaraciones responsables', 'Tramitación de actos comunicados o declaraciones responsables', NULL, '276.15', '276.15', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('26', '2', '3', '3052254', 'Puntos de recarga', 'Legalización puntos de recarga en BT', 'Legalización puntos de recarga en BT', NULL, '451.90', '451.90', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('27', '2', '3', '3052267', 'Licencias', 'Tramitación actos comunicados o declarac respons', 'Tramitación actos comunicados o declarac respons', NULL, '276.15', '276.15', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('28', '2', '3', '3052267-1.7', 'Obras', 'Tramitación de actos comunicados o declaraciones responsables', 'Tramitación de actos comunicados o declaraciones responsables', NULL, '276.15', '276.15', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('29', '2', '3', '3061002', 'Fotovoltaica', 'Estudio de sombras e implantación de paneles en 2 posiciones', 'Estudio de sombras e implantación de paneles en 2 posiciones', NULL, '142.28', '142.28', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('30', '2', '3', '3064341', 'Puntos de recarga', 'Mov. Electrica: Implantación hasta 150kw', 'Mov. Electrica: Implantación hasta 150kw', NULL, '88.93', '88.93', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('31', '2', '3', 'REPSOL-FV-SOMBRAS', 'Fotovoltaica', 'Estudio de sombras e implantación de paneles en 2 posiciones', 'Estudio de sombras e implantación de paneles en 2 posiciones', NULL, '142.28', '142.28', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('32', '3', '4', 'OTR-CONS-01', 'Consultoría', 'Asistencia técnica puntual', 'Asistencia técnica puntual', NULL, '0.00', '0.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('33', '3', '4', 'OTR-ENE-01', 'Energía', 'Estudio de ahorro energético', 'Estudio de ahorro energético', NULL, '0.00', '0.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('34', '3', '4', 'OTR-INF-01', 'Infraestructura', 'Dirección facultativa', 'Dirección facultativa', NULL, '1650.00', '1650.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('35', '3', '4', 'OTR-ING-01', 'Ingeniería', 'Informe técnico industrial', 'Informe técnico industrial', NULL, '1250.00', '1250.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('36', '3', '4', 'OTR-INS-01', 'Instalaciones', 'Legalización eléctrica', 'Legalización eléctrica', NULL, '980.00', '980.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('37', '3', '4', 'OTR-LIC-01', 'Licencias', 'Licencia de actividad', 'Licencia de actividad', NULL, '0.00', '0.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('38', '3', '4', 'OTR-LOG-01', 'Logística', 'Proyecto básico de nave', 'Proyecto básico de nave', NULL, '2420.00', '2420.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('39', '3', '4', 'OTR-MTO-01', 'Mantenimiento', 'Revisión eléctrica básica', 'Revisión eléctrica básica', NULL, '0.00', '0.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('40', '3', '4', 'OTR-PAT-01', 'Patrimonial', 'Informe de estado general', 'Informe de estado general', NULL, '540.00', '540.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES ('41', '3', '4', 'OTR-URB-01', 'Urbanismo', 'Consulta urbanística', 'Consulta urbanística', NULL, '680.00', '680.00', '1', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');

DROP TABLE IF EXISTS `tarifarios`;
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tarifarios` (`id_tarifario`, `id_contexto`, `id_contrato`, `nombre`, `version`, `fecha_inicio_vigencia`, `fecha_fin_vigencia`, `factor_multiplicador`, `moneda`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('1', '1', '1', 'Tarifario Moeve contrato 686', 'excel-real', '2023-01-01', NULL, '1.0000', 'EUR', '[muestra-operativa-50] Tarifario usado por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifarios` (`id_tarifario`, `id_contexto`, `id_contrato`, `nombre`, `version`, `fecha_inicio_vigencia`, `fecha_fin_vigencia`, `factor_multiplicador`, `moneda`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('2', '1', '2', 'Tarifario Moeve contrato 772', 'excel-real', '2023-01-01', NULL, '1.0000', 'EUR', '[muestra-operativa-50] Tarifario usado por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifarios` (`id_tarifario`, `id_contexto`, `id_contrato`, `nombre`, `version`, `fecha_inicio_vigencia`, `fecha_fin_vigencia`, `factor_multiplicador`, `moneda`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('3', '2', '3', 'Tarifario Repsol 2023-2027', 'excel-real', '2023-01-01', NULL, '1.0000', 'EUR', '[muestra-operativa-50] Tarifario usado por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tarifarios` (`id_tarifario`, `id_contexto`, `id_contrato`, `nombre`, `version`, `fecha_inicio_vigencia`, `fecha_fin_vigencia`, `factor_multiplicador`, `moneda`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES ('4', '3', '4', 'Tarifario otros demo 2026', 'demo', '2026-01-01', NULL, '1.0000', 'EUR', '[muestra-operativa-50] Tarifario usado por la muestra.', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');

DROP TABLE IF EXISTS `telefonos`;
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


DROP TABLE IF EXISTS `tipos_documento`;
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tipos_documento` (`id_tipo_documento`, `id_contexto`, `codigo`, `nombre`, `tiene_doble_factura`, `tiene_orden_mto`, `tiene_num_tarifa`, `activo`, `created_at`, `updated_at`) VALUES ('1', '1', 'CONTROL_TRABAJOS', 'Control de Trabajos Moeve', '0', '0', '0', '1', '2026-05-08 02:00:57', '2026-05-08 04:00:59');
INSERT INTO `tipos_documento` (`id_tipo_documento`, `id_contexto`, `codigo`, `nombre`, `tiene_doble_factura`, `tiene_orden_mto`, `tiene_num_tarifa`, `activo`, `created_at`, `updated_at`) VALUES ('2', '2', 'DISENO', 'Diseño Repsol', '0', '0', '1', '1', '2026-05-08 02:00:57', '2026-05-08 04:00:59');
INSERT INTO `tipos_documento` (`id_tipo_documento`, `id_contexto`, `codigo`, `nombre`, `tiene_doble_factura`, `tiene_orden_mto`, `tiene_num_tarifa`, `activo`, `created_at`, `updated_at`) VALUES ('3', '2', 'EDIFICACION', 'Edificación Repsol', '1', '0', '1', '1', '2026-05-08 02:00:57', '2026-05-08 04:00:59');
INSERT INTO `tipos_documento` (`id_tipo_documento`, `id_contexto`, `codigo`, `nombre`, `tiene_doble_factura`, `tiene_orden_mto`, `tiene_num_tarifa`, `activo`, `created_at`, `updated_at`) VALUES ('4', '2', 'OBRAS', 'Obras Repsol', '1', '0', '1', '1', '2026-05-08 02:00:57', '2026-05-08 04:00:59');
INSERT INTO `tipos_documento` (`id_tipo_documento`, `id_contexto`, `codigo`, `nombre`, `tiene_doble_factura`, `tiene_orden_mto`, `tiene_num_tarifa`, `activo`, `created_at`, `updated_at`) VALUES ('5', '2', 'LICENCIAS', 'Licencias Repsol', '0', '0', '1', '1', '2026-05-08 02:00:57', '2026-05-08 04:00:59');
INSERT INTO `tipos_documento` (`id_tipo_documento`, `id_contexto`, `codigo`, `nombre`, `tiene_doble_factura`, `tiene_orden_mto`, `tiene_num_tarifa`, `activo`, `created_at`, `updated_at`) VALUES ('6', '2', 'FV', 'Fotovoltaica Repsol', '0', '0', '1', '1', '2026-05-08 02:00:57', '2026-05-08 04:00:59');
INSERT INTO `tipos_documento` (`id_tipo_documento`, `id_contexto`, `codigo`, `nombre`, `tiene_doble_factura`, `tiene_orden_mto`, `tiene_num_tarifa`, `activo`, `created_at`, `updated_at`) VALUES ('7', '2', 'ESTRUCTURAS', 'Estructuras y Vertidos Repsol', '1', '0', '1', '1', '2026-05-08 02:00:57', '2026-05-08 04:00:59');
INSERT INTO `tipos_documento` (`id_tipo_documento`, `id_contexto`, `codigo`, `nombre`, `tiene_doble_factura`, `tiene_orden_mto`, `tiene_num_tarifa`, `activo`, `created_at`, `updated_at`) VALUES ('8', '2', 'MTO', 'Mantenimiento Repsol', '1', '1', '1', '1', '2026-05-08 02:00:57', '2026-05-08 04:00:59');
INSERT INTO `tipos_documento` (`id_tipo_documento`, `id_contexto`, `codigo`, `nombre`, `tiene_doble_factura`, `tiene_orden_mto`, `tiene_num_tarifa`, `activo`, `created_at`, `updated_at`) VALUES ('9', '2', 'PUNTOS_RECARGA', 'Puntos de Recarga Repsol', '0', '0', '1', '1', '2026-05-08 02:00:57', '2026-05-08 04:00:59');
INSERT INTO `tipos_documento` (`id_tipo_documento`, `id_contexto`, `codigo`, `nombre`, `tiene_doble_factura`, `tiene_orden_mto`, `tiene_num_tarifa`, `activo`, `created_at`, `updated_at`) VALUES ('10', '3', 'OTROS', 'Trabajo otros clientes', '0', '0', '0', '1', '2026-05-08 02:00:57', '2026-05-08 04:00:59');

DROP TABLE IF EXISTS `tipos_trabajo`;
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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('1', '1', '1', 'NPV', 'Nueva Propuesta de Valor', NULL, NULL, '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('2', '1', '1', 'REFORMA', 'Reforma General', NULL, NULL, '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('3', '1', '1', 'INDUSTRIA', 'Industria', NULL, NULL, '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('4', '2', '2', 'NPV', 'Nueva Propuesta de Valor', 'GABRIELA GARCÍA', 'YOLANDA SEGOVIA', '1', '2026-05-08 02:00:57', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('5', '2', '2', 'REFORMA', 'Reforma', 'GABRIELA GARCÍA', 'MARI CARMEN', '1', '2026-05-08 02:00:57', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('6', '3', '10', 'OBRA', 'Obra otros clientes', NULL, NULL, '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('7', '3', '10', 'MTO', 'Mantenimiento otros clientes', NULL, NULL, '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('8', '1', '1', 'OTROS', 'Otros', 'César', 'Luis Alzola Würth', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('9', '1', '1', 'ESTIMACION', 'Estimación', 'Gabriela', 'Sara Esteban', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('10', '1', '1', 'CUBIERTA', 'Cubierta', 'Almudena', 'Laura Martínez', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('11', '1', '1', 'IMAGEN', 'Imagen', 'Almudena', 'Nuria Admetlla', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('12', '1', '1', 'SYS', 'Seguridad y salud', 'César', 'Carlos García Masip', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('13', '1', '1', 'FOTOVOLTAICA', 'Fotovoltaica', 'Amaya', 'María Marín', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('14', '1', '1', 'TIENDAS', 'Tiendas', 'José Vergara', 'Giovanna Reyes Buendia', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('15', '1', '1', 'MANTENIMIENTO', 'Mantenimiento', 'César', 'Jorge Antolín', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('16', '1', '1', 'LEGALIZACIONES', 'Legalizaciones', 'César', 'Beatriz Llueca', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('17', '1', '1', 'SONDAS', 'Sondas', 'Amaya', 'Luis Alzola Würth', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('18', '2', '2', 'REFORMA_GENERAL', 'Reforma general', 'GABRIELA GARCÍA', 'GONZALO PANIAGUA', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('19', '2', '3', 'STOPGO_MINI', 'Stop&Go Mini', 'JUAN CARLOS', 'BLAS DOMINGUEZ', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('20', '2', '3', 'STOPGO', 'Stop&Go', 'CARMEN LÓPEZ', 'VIRGINIA TRUJILLO', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('21', '2', '3', 'RESTAURADORES', 'Restauradores', 'LOURDES REDONDO', 'PACO ALBALA', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('22', '2', '4', 'OTROS', 'Otros', 'Juan Izquierdo', 'Alberto Menacho', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('23', '2', '5', 'LICENCIAS', 'Licencias', 'ÓSCAR GARCÍA', 'YOLANDA SEGOVIA', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('24', '2', '6', 'ESTUDIO_SOMBRAS', 'Estudio sombras', 'ALVARO RIO', 'BORJA DOMÍNGUEZ', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('25', '2', '7', 'PATOLOGIA', 'Patología', 'ALVARO RIO', 'MAURO ARIZNAVARRETA', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('26', '2', '8', 'INDUSTRIA', 'Industria', 'ISABEL GONZÁLEZ', 'ARMANDO GONZÁLEZ', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('27', '2', '9', 'PUNTOS_RECARGA', 'Puntos de recarga', NULL, NULL, '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('28', '3', '10', 'INFORME_TECNICO', 'Informe técnico', 'Amaya', 'Responsable demo patrimonial', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('29', '3', '10', 'MANTENIMIENTO', 'Mantenimiento', 'Amaya', 'Responsable demo sur', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('30', '3', '10', 'URBANISMO', 'Urbanismo', 'César', 'Responsable demo centro', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('31', '3', '10', 'ENERGIA', 'Energía', 'Almudena', 'Responsable demo levante', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('32', '3', '10', 'PROYECTO_BASICO', 'Proyecto básico', 'José Vergara', 'Responsable demo oeste', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('33', '3', '10', 'LICENCIAS', 'Licencias', 'César', 'Responsable demo administración', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('34', '3', '10', 'LEGALIZACIONES', 'Legalizaciones', 'César', 'Responsable demo instalaciones', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('35', '3', '10', 'CONSULTORIA', 'Consultoría', 'Almudena', 'Responsable demo consultoría', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES ('36', '3', '10', 'DIRECCION_FACULTATIVA', 'Dirección facultativa', 'José Vergara', 'Responsable demo infraestructura', '1', '2026-05-08 04:00:59', '2026-05-08 04:00:59');

DROP TABLE IF EXISTS `trabajos`;
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
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('1', '1', '1', '7', '1', '10', '1', '1', '4', '15', NULL, '17157', NULL, 'Informe cubiertas', '2016-04-27', '2016-08-31', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-CUBIERTA; observacion_fuente=Excel Nº 15. Factura compartida con Nº 1 en la muestra.', NULL, NULL, 'Cubierta', 'Laura Martínez', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('2', '1', '1', '12', '1', '9', '1', '1', '4', '4', NULL, '31592', NULL, 'Implantación valorada', '2016-11-28', '2016-12-15', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-ESTIMACION; observacion_fuente=Excel Nº 4. Contrato 686.', NULL, NULL, 'Estimación', 'Sara Esteban', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('3', '1', '1', '4', '1', '11', '1', '1', '4', '68', NULL, '12848', NULL, 'Cambio de imagen', '2017-08-24', '2017-09-15', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-IMAGEN; observacion_fuente=Excel Nº 68.', NULL, NULL, 'Imagen', 'Nuria Admetlla', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('4', '1', '1', '9', '1', '8', '1', '1', '4', '1', NULL, '20673', NULL, '\"Toma\" ES. Cambio tit', '2016-07-26', '2016-08-31', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-OTROS; observacion_fuente=Excel Nº 1. Contrato 686.', NULL, NULL, 'Otros', 'Roberto Álvarez', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('5', '1', '1', '1', '1', '8', '1', '1', '2', '2', NULL, '11496', NULL, 'Modificación de registro industrial por cambio de AS', '2015-11-24', '2017-04-18', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-OTROS; observacion_fuente=Excel Nº 2. Observación: pedido real con facturación histórica.', NULL, NULL, 'Otros', 'Luis Alzola Würth', 'finalizado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('6', '1', '1', '12', '1', '8', '1', '1', '4', '7', NULL, '31592', NULL, '\"Toma\" ES. Cambio tit', '2016-06-03', '2016-06-30', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-OTROS; observacion_fuente=Excel Nº 7. Misma estación que Nº 4, pedido distinto.', NULL, NULL, 'Otros', 'Roberto Álvarez', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('7', '1', '1', '19', '1', '12', '1', '1', '2', '82', 'Vicente San Martín', '77402', NULL, 'Supervisión y CSS mensual', '2016-01-27', '2016-01-27', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-SYS; observacion_fuente=Excel Nº 82. Sin pedido ni factura en la fila fuente.', NULL, NULL, 'SyS', 'Carlos García Masip', 'cancelado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('8', '1', '1', '3', '1', '13', '2', '2', '4', '5472', NULL, '11853', NULL, 'Fotovoltaica - Obra - Bloque 24 Visita extra', '2024-07-08', '2024-07-08', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-FOTOVOLTAICA; observacion_fuente=Excel Nº 5472. Contrato 772.', NULL, NULL, 'Fotovoltaica', 'María Marín', 'finalizado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('9', '1', '1', '11', '1', '11', '2', '2', '4', '5815', NULL, '31536', NULL, 'Cambio de Imagen', '2024-12-02', '2025-04-29', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-IMAGEN; observacion_fuente=Excel Nº 5815.', NULL, NULL, 'Imagen', 'Nuria Admetlla', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('10', '1', '1', '6', '1', '15', '2', '2', '2', '5997', 'PPVV-159470-04853', '15947', NULL, 'Mantenimiento - BT Desfavorable', '2025-02-18', '2026-01-26', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-MANTENIMIENTO; observacion_fuente=Excel Nº 5997. Número operativo PPVV-159470-04853.', NULL, NULL, 'Mantenimiento', 'Rita Bañuelos', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('11', '1', '1', '14', '1', '15', '2', '2', '2', '6078', 'PPVV-330930-00812', '33093', NULL, 'Mantenimiento - Rejillas', '2025-03-18', '2025-04-01', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-MANTENIMIENTO; observacion_fuente=Excel Nº 6078. Número operativo PPVV-330930-00812.', NULL, NULL, 'Mantenimiento', 'Jorge Antolín', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('12', '1', '1', '2', '1', '15', '2', '2', '2', '6440', 'PPVV-116600-04778', '11660', NULL, 'Mantenimiento - Pavimento', '2025-11-21', '2026-02-20', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-MANTENIMIENTO; observacion_fuente=Excel Nº 6440. Número operativo PPVV-116600-04778.', NULL, NULL, 'Mantenimiento', 'Jorge Antolín', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('13', '1', '1', '5', '1', '16', '2', '2', '2', '6441', NULL, '15208', NULL, 'Legalización', '2025-11-21', '2025-11-21', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-LEGALIZACIONES; observacion_fuente=Excel Nº 6441.', NULL, NULL, 'Legalizaciones', 'Beatriz Llueca', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('14', '1', '1', '18', '1', '15', '2', '2', '2', '6445', 'PPVV-075230-06241', '7523', NULL, 'Mantenimiento - BT Desfavorable', '2025-11-25', NULL, '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-MANTENIMIENTO; observacion_fuente=Excel Nº 6445. Sin pedido; número operativo PPVV-075230-06241.', NULL, NULL, 'Mantenimiento', 'Rita Bañuelos', 'en_curso', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('15', '1', '1', '10', '1', '14', '2', '2', '4', '5495', NULL, '31527', NULL, 'Córner R´spiro', '2024-06-19', '2024-10-31', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-TIENDAS; observacion_fuente=Excel Nº 5495.', NULL, NULL, 'Tiendas', 'Jorge Ángel Castaño', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('16', '1', '1', '16', '1', '14', '2', '2', '4', '5521', NULL, '33467', NULL, 'Córner R´spiro', '2024-07-15', '2024-12-16', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-TIENDAS; observacion_fuente=Excel Nº 5521.', NULL, NULL, 'Tiendas', 'Jorge Ángel Castaño', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('17', '1', '1', '17', '1', '14', '2', '2', '4', '5643', NULL, '33979', NULL, 'Córner R´spiro', '2024-10-10', '2024-12-16', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-TIENDAS; observacion_fuente=Excel Nº 5643. Factura compartida con Nº 5521.', NULL, NULL, 'Tiendas', 'Jorge Ángel Castaño', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('18', '1', '1', '13', '1', '14', '2', '2', '4', '5886', NULL, '31626', NULL, 'Reforma tienda - Fases I y II', '2025-01-09', '2025-06-05', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-TIENDAS; observacion_fuente=Excel Nº 5886.', NULL, NULL, 'Tiendas', 'Jorge Ángel Castaño', 'finalizado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('19', '1', '1', '8', '1', '14', '2', '2', '4', '6080', NULL, '19511', NULL, 'Reforma interior - Fase obra', '2025-03-18', '2025-05-08', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-TIENDAS; observacion_fuente=Excel Nº 6080. Obra cancelada; pedido real con importe 0.', NULL, NULL, 'Tiendas', 'Giovanna Reyes Buendia', 'cancelado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('20', '1', '1', '15', '1', '17', '2', '2', '4', '6444', NULL, '33103', NULL, 'Sondas CODO PUES', '2025-11-25', NULL, '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx; hoja=Trabajos; tarifa=MOEVE-SONDAS; observacion_fuente=Excel Nº 6444. Sin número de pedido; falta justificante.', NULL, NULL, 'Sondas', 'Luis Alzola Würth', 'pendiente_facturar', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('21', '2', '2', '34', '2', '5', '3', '3', '5', '1', NULL, '96322', NULL, 'Propuesta terraza, pipican, juegos infantiles, reforma edificio', '2023-12-04', '2023-12-13', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/01 Control Trabajos DISEÑO REPSOL.xlsx; hoja=DISEÑO; tarifa=3012735-19.4.1; observacion_fuente=Diseño Nº 1. Aviso 140123365; orden 40047241.', '140123365', '40047241', 'REFORMA', 'YOLANDA SEGOVIA', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('22', '2', '2', '30', '2', '5', '3', '3', '5', '2', NULL, '7630', NULL, 'Puente y caseta en lugar de box y caseta actual. Modif aspiradoras', '2023-12-15', '2023-12-26', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/01 Control Trabajos DISEÑO REPSOL.xlsx; hoja=DISEÑO; tarifa=3012744; observacion_fuente=Diseño Nº 2. Aviso 140123227; orden 40047420.', '140123227', '40047420', 'REFORMA', 'MARI CARMEN', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('23', '2', '2', '37', '2', '4', '3', '3', '5', '3', NULL, 'NPV-MONCADA', NULL, 'Nueva implantación por reducción de parcela', '2023-12-19', '2024-01-18', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/01 Control Trabajos DISEÑO REPSOL.xlsx; hoja=DISEÑO; tarifa=3012704; observacion_fuente=Diseño Nº 3. Estación NPV MONCADA.', '140065740', '40022561', 'NPV', 'YOLANDA SEGOVIA', 'finalizado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('24', '2', '2', '38', '2', '18', '3', '3', '5', '8', NULL, 'NUDO-ABRONIGAL', NULL, 'Proyecto estación del futuro', '2023-10-30', '2024-01-18', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/01 Control Trabajos DISEÑO REPSOL.xlsx; hoja=DISEÑO; tarifa=3012673; observacion_fuente=Diseño Nº 8. Código de estación normalizado como NUDO-ABRONIGAL.', '140126134', '40047580', 'REFORMA GENERAL', 'GONZALO PANIAGUA', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('25', '2', '2', '31', '3', '19', '3', '3', '5', '17', NULL, '94957', NULL, 'ACTOS COMUNICADOS O DR', '2023-12-11', '2024-01-25', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/02 Control Trabajos EDIFICACIÓN.xlsx; hoja=EDIFICACIÓN; tarifa=3046492; observacion_fuente=Edificación Nº 17.', '140123476', '60258006', 'STOP&GO MINI', 'BLAS DOMINGUEZ', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('26', '2', '2', '29', '3', '20', '3', '3', '5', '181', NULL, '7022', NULL, 'TOMA DE DATOS + IMPL. EDIFICIO', '2024-05-08', NULL, '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/02 Control Trabajos EDIFICACIÓN.xlsx; hoja=EDIFICACIÓN; tarifa=3012735; observacion_fuente=Edificación Nº 181. Trabajo cancelado; no se genera factura.', '140127382', '40049720', 'STOP&GO', 'VIRGINIA TRUJILLO', 'cancelado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('27', '2', '2', '23', '3', '21', '3', '3', '5', '315', NULL, '15981', NULL, 'VISITA OBRA TÉCNICO < 250Km', '2024-11-14', NULL, '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/02 Control Trabajos EDIFICACIÓN.xlsx; hoja=EDIFICACIÓN; tarifa=3012637; observacion_fuente=Edificación Nº 315. Trabajo cancelado; sin factura.', NULL, '60271829', 'RESTAURADORES', 'PACO ALBALA', 'cancelado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('28', '2', '2', '24', '5', '23', '3', '3', '5', '51', NULL, '31076', NULL, 'LICENCIA DE OBRA MARQUESINA', '2025-02-18', '2025-02-18', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/05 Control Trabajos LICENCIAS REPSOL.xlsx; hoja=LICENCIAS; tarifa=3012494; observacion_fuente=Licencias Nº 51.', '140120925', '60251063', 'LICENCIAS', 'NOELIA GARCÍA', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('29', '2', '2', '25', '6', '24', '3', '3', '5', '1', NULL, '33567', NULL, 'Estudio de sombras e implantación de paneles en 2 posiciones', '2023-01-22', '2024-01-23', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/09 Control Trabajos FV REPSOL.xlsx; hoja=FV; tarifa=3061002; observacion_fuente=FV Nº 1.', NULL, '40047622', 'ESTUDIO SOMBRAS', 'BORJA DOMÍNGUEZ', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('30', '2', '2', '27', '6', '24', '3', '3', '5', '2', NULL, '34136', NULL, 'Estudio de sombras e implantación de paneles en 2 posiciones', '2023-01-22', '2024-01-23', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/09 Control Trabajos FV REPSOL.xlsx; hoja=FV; tarifa=REPSOL-FV-SOMBRAS; observacion_fuente=FV Nº 2. Sin número de pedido.', NULL, NULL, 'ESTUDIO SOMBRAS', 'BORJA DOMÍNGUEZ', 'pendiente_facturar', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('31', '2', '2', '28', '7', '25', '3', '3', '5', '1', NULL, '6737', NULL, 'Titulado Superior', '2023-12-11', '2024-01-16', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/10 Control Trabajos ESTRUCTURAS Y VERTIDOS REPSOL.xlsx; hoja=ESTRUCTURAS; tarifa=3012797; observacion_fuente=Estructuras Nº 1.', '140123203', '40047480', 'PATOLOGÍA', 'MAURO ARIZNAVARRETA', 'finalizado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('32', '2', '2', '21', '4', '22', '3', '3', '5', '1', NULL, '12141', NULL, 'Saneamiento (dr de solo pista)', '2024-09-02', '2024-04-24', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/03 Control Trabajos OBRAS REPSOL Z50.xlsx; hoja=OBRAS Z50; tarifa=3052267-1.7; observacion_fuente=Obras Nº 1.', '140130533', '60272693', 'Otros', 'Francisco López Toro', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('33', '2', '2', '33', '4', '22', '3', '3', '5', '12', NULL, '96163', NULL, 'Talud', '2024-05-14', '2025-06-13', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/03 Control Trabajos OBRAS REPSOL Z50.xlsx; hoja=OBRAS Z50; tarifa=3012747-19.7.1; observacion_fuente=Obras Nº 12.', '140131234', '40052042', 'Otros', 'Francisco López Toro', 'finalizado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('34', '2', '2', '22', '4', '22', '3', '3', '5', '13', NULL, '13096', NULL, 'Saneamiento - vertidos', '2024-05-14', '2025-04-14', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/03 Control Trabajos OBRAS REPSOL Z50.xlsx; hoja=OBRAS Z50; tarifa=3012753-43.2; observacion_fuente=Obras Nº 13. Sin número de pedido.', '140127218', NULL, 'Otros', 'Alberto Menacho', 'pendiente_facturar', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('35', '2', '2', '37', '5', '23', '3', '3', '5', '33', NULL, 'NPV-MONCADA', NULL, 'SOLICITUD CCU', '2024-02-20', '2024-02-21', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/05 Control Trabajos LICENCIAS REPSOL.xlsx; hoja=LICENCIAS; tarifa=3052267; observacion_fuente=Licencias Nº 33.', '140065740', '40022561', 'LICENCIAS', 'MAURO ARIZNAVARRETA', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('36', '2', '2', '36', '5', '23', '3', '3', '5', '79', NULL, '97120', NULL, 'LICENCIAS NPV', '2024-02-20', '2025-05-20', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/05 Control Trabajos LICENCIAS REPSOL.xlsx; hoja=LICENCIAS; tarifa=3012652; observacion_fuente=Licencias Nº 79. Pedido anulado; no se crea factura.', '140131771', '40022561', 'LICENCIAS', 'YOLANDA SEGOVIA', 'pendiente_facturar', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('37', '2', '2', '20', '8', '26', '3', '3', '5', '1', NULL, '11301', NULL, 'Baja de tanque', '2023-12-21', '2024-02-16', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/12 Control Trabajos MTO REPSOL.xlsx; hoja=MTO; tarifa=3012753; observacion_fuente=MTO Nº 1.', '140123500', '50623115', 'INDUSTRIA', 'ELENA GONZÁLEZ', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('38', '2', '2', '32', '8', '26', '3', '3', '5', '2', NULL, '96060', NULL, 'Legalización ES', '2024-01-15', '2023-01-16', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/12 Control Trabajos MTO REPSOL.xlsx; hoja=MTO; tarifa=3012492; observacion_fuente=MTO Nº 2. Pedido referido literalmente en la fuente.', 'pedido por mail 25/01/24', '60267295', 'INDUSTRIA', 'ARMANDO GONZÁLEZ', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('39', '2', '2', '26', '9', '27', '3', '3', '5', '1', NULL, '33785', NULL, 'Legalización puntos de recarga en BT', NULL, '2000-01-01', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/13 Control Trabajos PUNTOS DE RECARGA.xlsx; hoja=PUNTOS_RECARGA; tarifa=3052254; observacion_fuente=Puntos de recarga Nº 1. La fecha 2000-01-01 viene de la fuente.', NULL, '60204249', 'PUNTOS_RECARGA', NULL, 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('40', '2', '2', '35', '9', '27', '3', '3', '5', '6', NULL, '96795', NULL, 'Mov. Electrica: Implantación hasta 150kw', NULL, '2000-01-06', '[muestra-operativa-50] fuente=docs/Abaco/excelsactualizados/Repsol/Repsol/13 Control Trabajos PUNTOS DE RECARGA.xlsx; hoja=PUNTOS_RECARGA; tarifa=3064341; observacion_fuente=Puntos de recarga Nº 6. Sin número de pedido.', NULL, NULL, 'PUNTOS_RECARGA', NULL, 'pendiente_facturar', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('41', '3', '8', '44', '10', '33', '4', '4', '2', '90006', NULL, 'OTR-ADM-01', NULL, 'Licencia de actividad local municipal', '2026-04-06', '2026-04-06', '[muestra-operativa-50] fuente=docs/02_CLIENTE/tareasComparar.md; hoja=demo-controlado; tarifa=OTR-LIC-01; observacion_fuente=[demo-controlado-otros] Cancelado sin pedido.', NULL, NULL, 'Otros', 'Responsable demo administración', 'cancelado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('42', '3', '11', '47', '10', '35', '4', '4', '3', '90009', NULL, 'OTR-CON-01', NULL, 'Asistencia técnica puntual', '2026-04-09', NULL, '[muestra-operativa-50] fuente=docs/02_CLIENTE/tareasComparar.md; hoja=demo-controlado; tarifa=OTR-CONS-01; observacion_fuente=[demo-controlado-otros] En curso sin pedido.', NULL, NULL, 'Otros', 'Responsable demo consultoría', 'en_curso', '0', '2026-05-08 04:00:59', '2026-05-17 12:22:44');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('43', '3', '6', '42', '10', '31', '4', '4', '3', '90004', NULL, 'OTR-LEVANTE-01', NULL, 'Estudio de ahorro energético', '2026-04-04', '2026-04-18', '[muestra-operativa-50] fuente=docs/02_CLIENTE/tareasComparar.md; hoja=demo-controlado; tarifa=OTR-ENE-01; observacion_fuente=[demo-controlado-otros] Terminado sin pedido.', NULL, NULL, 'Otros', 'Responsable demo levante', 'terminado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('44', '3', '3', '39', '10', '28', '4', '4', '2', '90001', NULL, 'OTR-NORTE-01', NULL, 'Informe técnico industrial para adecuación de instalación', '2026-04-01', '2026-04-12', '[muestra-operativa-50] fuente=docs/02_CLIENTE/tareasComparar.md; hoja=demo-controlado; tarifa=OTR-ING-01; observacion_fuente=[demo-controlado-otros] Caso creado al no existir fuente real separada para OTROS.', NULL, NULL, 'Otros', 'Responsable demo norte', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('45', '3', '12', '48', '10', '36', '4', '4', '3', '90010', NULL, 'OTR-INF-01', NULL, 'Dirección facultativa de actuación menor', '2026-04-10', '2026-04-30', '[muestra-operativa-50] fuente=docs/02_CLIENTE/tareasComparar.md; hoja=demo-controlado; tarifa=OTR-INF-01; observacion_fuente=[demo-controlado-otros] Pedido y factura enviada demo.', NULL, NULL, 'Otros', 'Responsable demo infraestructura', 'finalizado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('46', '3', '10', '46', '10', '34', '4', '4', '2', '90008', NULL, 'OTR-INS-01', NULL, 'Legalización eléctrica de instalación', '2026-04-08', '2026-04-20', '[muestra-operativa-50] fuente=docs/02_CLIENTE/tareasComparar.md; hoja=demo-controlado; tarifa=OTR-INS-01; observacion_fuente=[demo-controlado-otros] Pedido y factura emitida demo.', NULL, NULL, 'Otros', 'Responsable demo instalaciones', 'facturado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('47', '3', '7', '43', '10', '32', '4', '4', '3', '90005', NULL, 'OTR-OESTE-01', NULL, 'Proyecto básico de nave logística', '2026-04-05', '2026-04-22', '[muestra-operativa-50] fuente=docs/02_CLIENTE/tareasComparar.md; hoja=demo-controlado; tarifa=OTR-LOG-01; observacion_fuente=[demo-controlado-otros] Pedido y factura enviada demo.', NULL, NULL, 'Otros', 'Responsable demo oeste', 'finalizado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('48', '3', '9', '45', '10', '28', '4', '4', '3', '90007', NULL, 'OTR-PAT-01', NULL, 'Informe de estado general del inmueble', '2026-04-07', '2026-04-14', '[muestra-operativa-50] fuente=docs/02_CLIENTE/tareasComparar.md; hoja=demo-controlado; tarifa=OTR-PAT-01; observacion_fuente=[demo-controlado-otros] Pedido demo sin factura.', NULL, NULL, 'Otros', 'Responsable demo patrimonial', 'terminado', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('49', '3', '4', '40', '10', '29', '4', '4', '3', '90002', NULL, 'OTR-SUR-01', NULL, 'Revisión eléctrica básica de local comercial', '2026-04-02', NULL, '[muestra-operativa-50] fuente=docs/02_CLIENTE/tareasComparar.md; hoja=demo-controlado; tarifa=OTR-MTO-01; observacion_fuente=[demo-controlado-otros] Trabajo en curso sin pedido.', NULL, NULL, 'Otros', 'Responsable demo sur', 'en_curso', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');
INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `numero_trabajo`, `numero_trabajo_operativo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `bloqueado_cierre`, `created_at`, `updated_at`) VALUES ('50', '3', '5', '41', '10', '30', '4', '4', '2', '90003', NULL, 'OTR-CENTRO-01', NULL, 'Consulta urbanística para reforma menor', '2026-04-03', '2026-04-09', '[muestra-operativa-50] fuente=docs/02_CLIENTE/tareasComparar.md; hoja=demo-controlado; tarifa=OTR-URB-01; observacion_fuente=[demo-controlado-otros] Pedido demo sin factura.', NULL, NULL, 'Otros', 'Responsable demo centro', 'pendiente_facturar', '0', '2026-05-08 04:00:59', '2026-05-08 04:00:59');

DROP TABLE IF EXISTS `unidades`;
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

INSERT INTO `unidades` (`id_unidad`, `nombre`, `abreviatura`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('1', 'Unidad', 'ud', 'Unidad general', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `unidades` (`id_unidad`, `nombre`, `abreviatura`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('2', 'Hora', 'h', 'Hora de trabajo', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `unidades` (`id_unidad`, `nombre`, `abreviatura`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('3', 'Metro cuadrado', 'm2', 'Superficie', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');
INSERT INTO `unidades` (`id_unidad`, `nombre`, `abreviatura`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES ('4', 'Metro lineal', 'ml', 'Longitud', '1', '2026-05-08 02:00:57', '2026-05-08 02:00:57');

DROP TABLE IF EXISTS `usuario_contextos`;
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

INSERT INTO `usuario_contextos` (`id_usuario_contexto`, `id_usuario`, `id_contexto`, `es_contexto_principal`, `activo`, `created_at`) VALUES ('1', '1', '1', '0', '1', '2026-05-08 04:00:58');
INSERT INTO `usuario_contextos` (`id_usuario_contexto`, `id_usuario`, `id_contexto`, `es_contexto_principal`, `activo`, `created_at`) VALUES ('2', '1', '2', '0', '1', '2026-05-08 04:00:58');
INSERT INTO `usuario_contextos` (`id_usuario_contexto`, `id_usuario`, `id_contexto`, `es_contexto_principal`, `activo`, `created_at`) VALUES ('3', '1', '3', '1', '1', '2026-05-08 04:00:58');
INSERT INTO `usuario_contextos` (`id_usuario_contexto`, `id_usuario`, `id_contexto`, `es_contexto_principal`, `activo`, `created_at`) VALUES ('4', '2', '1', '0', '1', '2026-05-08 04:00:58');
INSERT INTO `usuario_contextos` (`id_usuario_contexto`, `id_usuario`, `id_contexto`, `es_contexto_principal`, `activo`, `created_at`) VALUES ('5', '2', '2', '0', '1', '2026-05-08 04:00:58');
INSERT INTO `usuario_contextos` (`id_usuario_contexto`, `id_usuario`, `id_contexto`, `es_contexto_principal`, `activo`, `created_at`) VALUES ('6', '2', '3', '1', '1', '2026-05-08 04:00:58');
INSERT INTO `usuario_contextos` (`id_usuario_contexto`, `id_usuario`, `id_contexto`, `es_contexto_principal`, `activo`, `created_at`) VALUES ('7', '3', '1', '0', '1', '2026-05-08 04:00:58');
INSERT INTO `usuario_contextos` (`id_usuario_contexto`, `id_usuario`, `id_contexto`, `es_contexto_principal`, `activo`, `created_at`) VALUES ('8', '3', '2', '0', '1', '2026-05-08 04:00:58');
INSERT INTO `usuario_contextos` (`id_usuario_contexto`, `id_usuario`, `id_contexto`, `es_contexto_principal`, `activo`, `created_at`) VALUES ('9', '3', '3', '1', '1', '2026-05-08 04:00:58');
INSERT INTO `usuario_contextos` (`id_usuario_contexto`, `id_usuario`, `id_contexto`, `es_contexto_principal`, `activo`, `created_at`) VALUES ('10', '4', '1', '1', '1', '2026-05-08 04:00:58');
INSERT INTO `usuario_contextos` (`id_usuario_contexto`, `id_usuario`, `id_contexto`, `es_contexto_principal`, `activo`, `created_at`) VALUES ('11', '5', '2', '1', '1', '2026-05-08 04:00:59');
INSERT INTO `usuario_contextos` (`id_usuario_contexto`, `id_usuario`, `id_contexto`, `es_contexto_principal`, `activo`, `created_at`) VALUES ('12', '6', '1', '0', '1', '2026-05-08 04:00:59');
INSERT INTO `usuario_contextos` (`id_usuario_contexto`, `id_usuario`, `id_contexto`, `es_contexto_principal`, `activo`, `created_at`) VALUES ('13', '6', '2', '0', '1', '2026-05-08 04:00:59');
INSERT INTO `usuario_contextos` (`id_usuario_contexto`, `id_usuario`, `id_contexto`, `es_contexto_principal`, `activo`, `created_at`) VALUES ('14', '6', '3', '1', '1', '2026-05-08 04:00:59');

DROP TABLE IF EXISTS `usuario_roles`;
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

INSERT INTO `usuario_roles` (`id_usuario_rol`, `id_usuario`, `id_rol`, `created_at`) VALUES ('1', '1', '1', '2026-05-08 04:00:58');
INSERT INTO `usuario_roles` (`id_usuario_rol`, `id_usuario`, `id_rol`, `created_at`) VALUES ('2', '2', '3', '2026-05-08 04:00:58');
INSERT INTO `usuario_roles` (`id_usuario_rol`, `id_usuario`, `id_rol`, `created_at`) VALUES ('3', '3', '2', '2026-05-08 04:00:58');
INSERT INTO `usuario_roles` (`id_usuario_rol`, `id_usuario`, `id_rol`, `created_at`) VALUES ('4', '4', '4', '2026-05-08 04:00:58');
INSERT INTO `usuario_roles` (`id_usuario_rol`, `id_usuario`, `id_rol`, `created_at`) VALUES ('5', '5', '5', '2026-05-08 04:00:58');
INSERT INTO `usuario_roles` (`id_usuario_rol`, `id_usuario`, `id_rol`, `created_at`) VALUES ('6', '6', '6', '2026-05-08 04:00:59');

DROP TABLE IF EXISTS `usuarios`;
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

INSERT INTO `usuarios` (`id_usuario`, `id_contexto`, `id_contacto_empresa`, `nombre`, `apellidos`, `nombre_usuario`, `email`, `email_recuperacion`, `email_verificado_at`, `password`, `telefono`, `avatar_key`, `remember_token`, `ultimo_login_at`, `activo`, `interface_mode`, `created_at`, `updated_at`) VALUES ('1', '3', NULL, 'Administrador', 'ERP CIETE', 'admin', 'admin@ciete.es', NULL, '2026-05-08 02:00:57', '$2y$12$w7yamofORUWXqdbL74ZaJOebfzqsNXxB40FCl9aYaa4ulSPJfEVv.', NULL, 'avatar-ciete-logo', NULL, NULL, '1', 'ciete_moderno', '2026-05-08 02:00:58', '2026-05-08 02:00:58');
INSERT INTO `usuarios` (`id_usuario`, `id_contexto`, `id_contacto_empresa`, `nombre`, `apellidos`, `nombre_usuario`, `email`, `email_recuperacion`, `email_verificado_at`, `password`, `telefono`, `avatar_key`, `remember_token`, `ultimo_login_at`, `activo`, `interface_mode`, `created_at`, `updated_at`) VALUES ('2', '3', NULL, 'Cesar', 'CIETE', 'cesar', 'cesar@ciete.es', NULL, '2026-05-08 02:00:58', '$2y$12$DyCuBtxMKkE3TLu55nKgZehJRlX6QqvFp1ENQaRjz9aSd2vWUlVcu', NULL, 'avatar-ciete-logo', NULL, '2026-05-17 14:44:11', '1', 'ciete_excel', '2026-05-08 02:00:58', '2026-05-17 14:44:11');
INSERT INTO `usuarios` (`id_usuario`, `id_contexto`, `id_contacto_empresa`, `nombre`, `apellidos`, `nombre_usuario`, `email`, `email_recuperacion`, `email_verificado_at`, `password`, `telefono`, `avatar_key`, `remember_token`, `ultimo_login_at`, `activo`, `interface_mode`, `created_at`, `updated_at`) VALUES ('3', '3', NULL, 'Usuario', 'Ejecucion', 'usuario', 'usuario@ciete.es', NULL, '2026-05-08 02:00:58', '$2y$12$GufyVQKxyjaVRZgDVMi0FOJHqtnyeyqe7zmI9viChv.gfzxvZpOYG', NULL, 'avatar-ciete-logo', NULL, NULL, '1', 'ciete_excel', '2026-05-08 02:00:58', '2026-05-08 02:00:58');
INSERT INTO `usuarios` (`id_usuario`, `id_contexto`, `id_contacto_empresa`, `nombre`, `apellidos`, `nombre_usuario`, `email`, `email_recuperacion`, `email_verificado_at`, `password`, `telefono`, `avatar_key`, `remember_token`, `ultimo_login_at`, `activo`, `interface_mode`, `created_at`, `updated_at`) VALUES ('4', '1', NULL, 'Ejecucion', 'Moeve', 'moeve', 'moeve@ciete.es', NULL, '2026-05-08 02:00:58', '$2y$12$ekO1d5pgGmKhocRZecMAKevfmAL6Bd7aTfFkXw9f81hrGHtSfnXK6', NULL, 'avatar-ciete-logo', NULL, NULL, '1', 'ciete_excel', '2026-05-08 02:00:58', '2026-05-08 02:00:58');
INSERT INTO `usuarios` (`id_usuario`, `id_contexto`, `id_contacto_empresa`, `nombre`, `apellidos`, `nombre_usuario`, `email`, `email_recuperacion`, `email_verificado_at`, `password`, `telefono`, `avatar_key`, `remember_token`, `ultimo_login_at`, `activo`, `interface_mode`, `created_at`, `updated_at`) VALUES ('5', '2', NULL, 'Ejecucion', 'Repsol', 'repsol', 'repsol@ciete.es', NULL, '2026-05-08 02:00:58', '$2y$12$TWznk6q0Jg5CDaOnG4dVzOJEiD4XM.HgAX4t6D3W8r08ebmyuo1Y2', NULL, 'avatar-ciete-logo', NULL, NULL, '1', 'ciete_excel', '2026-05-08 02:00:58', '2026-05-08 02:00:58');
INSERT INTO `usuarios` (`id_usuario`, `id_contexto`, `id_contacto_empresa`, `nombre`, `apellidos`, `nombre_usuario`, `email`, `email_recuperacion`, `email_verificado_at`, `password`, `telefono`, `avatar_key`, `remember_token`, `ultimo_login_at`, `activo`, `interface_mode`, `created_at`, `updated_at`) VALUES ('6', '3', NULL, 'Usuario', 'Contable', 'contable', 'contable@ciete.es', NULL, '2026-05-08 02:00:59', '$2y$12$UHzDl1kNMICLLdbAbT45O..ANnLIycDhgniYsGgQLMto.BVFzsovq', NULL, 'avatar-ciete-logo', NULL, '2026-05-17 19:08:04', '1', 'ciete_excel', '2026-05-08 02:00:59', '2026-05-17 19:08:04');

SET FOREIGN_KEY_CHECKS=1;
