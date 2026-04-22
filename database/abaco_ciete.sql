-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-04-2026 a las 15:34:53
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `abaco_ciete`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_log`
--

CREATE TABLE `audit_log` (
  `id_audit` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED DEFAULT NULL,
  `id_usuario` bigint(20) UNSIGNED DEFAULT NULL,
  `accion` enum('crear','editar','eliminar','cerrar','reabrir','importar') NOT NULL,
  `tabla` varchar(80) NOT NULL,
  `registro_id` bigint(20) UNSIGNED NOT NULL,
  `datos_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_anteriores`)),
  `datos_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_nuevos`)),
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cobros`
--

CREATE TABLE `cobros` (
  `id_cobro` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_factura` bigint(20) UNSIGNED NOT NULL,
  `id_usuario_registro` bigint(20) UNSIGNED DEFAULT NULL,
  `fecha_cobro` date NOT NULL,
  `importe` decimal(14,2) NOT NULL,
  `metodo_cobro` enum('transferencia','giro','efectivo','confirming','otro') NOT NULL DEFAULT 'transferencia',
  `referencia` varchar(120) DEFAULT NULL,
  `estado` enum('pendiente','recibido','conciliado','devuelto') NOT NULL DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cobros`
--

INSERT INTO `cobros` (`id_cobro`, `id_contexto`, `id_factura`, `id_usuario_registro`, `fecha_cobro`, `importe`, `metodo_cobro`, `referencia`, `estado`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, '2026-03-15', 1149.50, 'transferencia', 'TRF-MOEVE-2026-001', 'pendiente', NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 2, 3, NULL, '2026-03-20', 1185.80, 'transferencia', 'TRF-REPSOL-2026-001', 'recibido', NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios_legalizaciones`
--

CREATE TABLE `comentarios_legalizaciones` (
  `id_comentario_legalizacion` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_legalizacion` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` bigint(20) UNSIGNED DEFAULT NULL,
  `fecha_comentario` datetime NOT NULL DEFAULT current_timestamp(),
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `comentarios_legalizaciones`
--

INSERT INTO `comentarios_legalizaciones` (`id_comentario_legalizacion`, `id_contexto`, `id_legalizacion`, `id_usuario`, `fecha_comentario`, `comentario`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, '2026-02-12 09:30:00', 'Documentacion presentada en el Ayuntamiento de Sevilla.', '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 1, 2, NULL, '2026-03-12 11:00:00', 'Pendiente de informe tecnico municipal para aprobacion.', '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 2, 3, NULL, '2026-03-18 16:45:00', 'Expediente resuelto favorablemente. Licencia concedida.', '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 2, 4, NULL, '2026-03-05 10:15:00', 'Inicio de tramitacion de licencia de actividad ante la Comunidad.', '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactos`
--

CREATE TABLE `contactos` (
  `id_contacto` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(150) DEFAULT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `cargo_general` varchar(150) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `contactos`
--

INSERT INTO `contactos` (`id_contacto`, `nombre`, `apellidos`, `dni`, `cargo_general`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', 'Sistema', NULL, 'Administrador ERP', NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 'Carlos', 'Martinez Gil', '12345678A', 'Director Tecnico', NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 'Ana', 'Lopez Ruiz', '23456789B', 'Responsable de Obras', NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 'Pedro', 'Garcia Navarro', '34567890C', 'Director Tecnico', NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(5, 'Laura', 'Sanchez Vega', '45678901D', 'Gestora de Proyectos', NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactos_empresas`
--

CREATE TABLE `contactos_empresas` (
  `id_contacto_empresa` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_empresa` bigint(20) UNSIGNED NOT NULL,
  `id_contacto` bigint(20) UNSIGNED NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `contactos_empresas`
--

INSERT INTO `contactos_empresas` (`id_contacto_empresa`, `id_contexto`, `id_empresa`, `id_contacto`, `puesto`, `categoria`, `es_responsable_principal`, `recibe_avisos`, `recibe_presupuestos`, `recibe_facturas`, `es_usuario`, `activo`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 3, 3, 1, 'Administrador ERP', 'interno', 1, 1, 1, 1, 1, 1, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 1, 1, 2, 'Director Tecnico MOEVE', 'cliente', 1, 1, 1, 0, 0, 1, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 1, 1, 3, 'Responsable Obras MOEVE', 'cliente', 0, 1, 0, 0, 0, 1, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 2, 2, 4, 'Director Tecnico REPSOL', 'cliente', 1, 1, 1, 1, 0, 1, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(5, 2, 2, 5, 'Gestora Proyectos REPSOL', 'cliente', 0, 1, 0, 0, 0, 1, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contextos_cliente`
--

CREATE TABLE `contextos_cliente` (
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `contextos_cliente`
--

INSERT INTO `contextos_cliente` (`id_contexto`, `nombre`, `codigo`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'MOEVE', 'MOEVE', 'Contexto operativo MOEVE', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 'REPSOL', 'REPSOL', 'Contexto operativo REPSOL', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 'OTRO', 'OTRO', 'Contexto general u otros clientes', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contratos`
--

CREATE TABLE `contratos` (
  `id_contrato` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_empresa_cliente` bigint(20) UNSIGNED NOT NULL,
  `codigo_contrato` varchar(100) NOT NULL,
  `nombre` varchar(180) DEFAULT NULL,
  `tipo` enum('marco','directo','otro') NOT NULL DEFAULT 'marco',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('vigente','expirado','cancelado') NOT NULL DEFAULT 'vigente',
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `contratos`
--

INSERT INTO `contratos` (`id_contrato`, `id_contexto`, `id_empresa_cliente`, `codigo_contrato`, `nombre`, `tipo`, `fecha_inicio`, `fecha_fin`, `estado`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'CTR-MOEVE-2026', 'Contrato Marco MOEVE 2026', 'marco', '2026-01-01', '2026-12-31', 'vigente', NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 2, 2, 'CTR-REPSOL-2026', 'Contrato Marco REPSOL 2026', 'marco', '2026-01-01', '2026-12-31', 'vigente', NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones`
--

CREATE TABLE `direcciones` (
  `id_direccion` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_empresa` bigint(20) UNSIGNED DEFAULT NULL,
  `id_contacto` bigint(20) UNSIGNED DEFAULT NULL,
  `id_contacto_empresa` bigint(20) UNSIGNED DEFAULT NULL,
  `id_usuario` bigint(20) UNSIGNED DEFAULT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `direcciones`
--

INSERT INTO `direcciones` (`id_direccion`, `id_contexto`, `id_empresa`, `id_contacto`, `id_contacto_empresa`, `id_usuario`, `tipo`, `linea1`, `linea2`, `codigo_postal`, `localidad`, `provincia`, `pais`, `es_principal`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL, NULL, 'principal', 'Paseo de la Castellana 259A', 'Torre Cepsa, Planta 30', '28046', 'Madrid', 'Madrid', 'Espana', 1, 'Sede central MOEVE', '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 1, 1, NULL, NULL, NULL, 'delegacion', 'Avenida de la Palmera 19', NULL, '41012', 'Sevilla', 'Sevilla', 'Espana', 0, 'Delegacion Sur MOEVE', '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 2, 2, NULL, NULL, NULL, 'principal', 'Calle Mendez Alvaro 44', 'Campus Repsol', '28045', 'Madrid', 'Madrid', 'Espana', 1, 'Sede central REPSOL', '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 2, 2, NULL, NULL, NULL, 'delegacion', 'Poligono Industrial Tarragona', 'Complejo Quimico', '43006', 'Tarragona', 'Tarragona', 'Espana', 0, 'Delegacion Tarragona REPSOL', '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `emails`
--

CREATE TABLE `emails` (
  `id_email` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_empresa` bigint(20) UNSIGNED DEFAULT NULL,
  `id_contacto` bigint(20) UNSIGNED DEFAULT NULL,
  `id_contacto_empresa` bigint(20) UNSIGNED DEFAULT NULL,
  `id_usuario` bigint(20) UNSIGNED DEFAULT NULL,
  `email` varchar(180) NOT NULL,
  `tipo` enum('personal','profesional','facturacion','avisos','tecnico','otro') NOT NULL DEFAULT 'profesional',
  `es_principal` tinyint(1) NOT NULL DEFAULT 0,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `emails`
--

INSERT INTO `emails` (`id_email`, `id_contexto`, `id_empresa`, `id_contacto`, `id_contacto_empresa`, `id_usuario`, `email`, `tipo`, `es_principal`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL, NULL, 'info@moeve.es', 'profesional', 1, 'Email corporativo MOEVE', '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 1, NULL, NULL, 2, NULL, 'carlos.martinez@moeve.es', 'profesional', 1, 'Email Carlos director tecnico', '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 2, 2, NULL, NULL, NULL, 'info@repsol.es', 'profesional', 1, 'Email corporativo REPSOL', '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 2, NULL, NULL, 4, NULL, 'pedro.garcia@repsol.es', 'profesional', 1, 'Email Pedro director tecnico', '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id_empresa` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `empresa_padre_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nombre` varchar(180) NOT NULL,
  `nombre_comercial` varchar(180) DEFAULT NULL,
  `razon_social` varchar(220) DEFAULT NULL,
  `cif` varchar(20) DEFAULT NULL,
  `tipo_empresa` enum('cliente','proveedor','cliente_proveedor','interna','otra') NOT NULL DEFAULT 'cliente',
  `web` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`id_empresa`, `id_contexto`, `empresa_padre_id`, `nombre`, `nombre_comercial`, `razon_social`, `cif`, `tipo_empresa`, `web`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'MOEVE', 'MOEVE', 'Moeve Energy S.A.', 'A28003119', 'cliente', 'https://www.moeve.es', NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 2, NULL, 'REPSOL', 'REPSOL', 'Repsol S.A.', 'A78374725', 'cliente', 'https://www.repsol.es', NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 3, NULL, 'CIETE INGENIEROS SA', 'Ciete', 'Ciete Ingenieros S.A.', 'A12345678', 'interna', 'https://www.ciete.es', NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estaciones_moeve_ext`
--

CREATE TABLE `estaciones_moeve_ext` (
  `id_estacion_servicio` bigint(20) UNSIGNED NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estaciones_moeve_ext`
--

INSERT INTO `estaciones_moeve_ext` (`id_estacion_servicio`, `n_margenes`, `tecnico_gestion`, `telefono_tecnico`, `email_tecnico`, `responsable_gestor`, `telefono_gestor`, `telefono_oficina`, `sede_email`, `vinculo_1`, `vinculo_2`, `f_alta_modificacion`, `cod_retailgas`, `cod_sociedad`, `sociedad`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Tecnico MOEVE', '600000001', 'tecnico.moeve@demo.local', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estaciones_repsol_ext`
--

CREATE TABLE `estaciones_repsol_ext` (
  `id_estacion_servicio` bigint(20) UNSIGNED NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estaciones_repsol_ext`
--

INSERT INTO `estaciones_repsol_ext` (`id_estacion_servicio`, `codigo_solred`, `litros_21`, `gnas_95_21`, `gnas_98_21`, `gasoleo_a_21`, `eplus10_21`, `glp_21`, `adblue_21`, `cliente_nombre`, `nom_encargado`, `nom_gerente`, `tfno_instalacion`, `fax_instalacion`, `tfno_movil_gerente`, `tfno_movil_encargado`, `margen`, `provincial`, `created_at`, `updated_at`) VALUES
(2, 'SOLRED-0001', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estaciones_servicio`
--

CREATE TABLE `estaciones_servicio` (
  `id_estacion_servicio` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_empresa_cliente` bigint(20) UNSIGNED NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estaciones_servicio`
--

INSERT INTO `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion`, `nombre`, `direccion`, `codigo_postal`, `poblacion`, `provincia`, `pais`, `latitud_wgs84`, `longitud_wgs84`, `estado`, `f_baja`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'MOEVE-EST-001', 'Estacion MOEVE Demo 01', 'Calle Energia 1', '41001', 'Sevilla', 'Sevilla', 'Espana', 37.38910000, -5.98450000, 'Activa', NULL, NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 2, 2, 'REPSOL-EST-001', 'Estacion REPSOL Demo 01', 'Avenida Industria 2', '28001', 'Madrid', 'Madrid', 'Espana', 40.41680000, -3.70380000, 'Activa', NULL, NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id_factura` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_trabajo` bigint(20) UNSIGNED NOT NULL,
  `id_empresa_cliente` bigint(20) UNSIGNED NOT NULL,
  `numero_factura` varchar(100) DEFAULT NULL,
  `numero_factura_ccp` varchar(100) DEFAULT NULL,
  `serie` varchar(20) DEFAULT NULL,
  `orden_factura` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `fecha_solicitud` date DEFAULT NULL,
  `fecha_emision` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `importe` decimal(14,2) NOT NULL DEFAULT 0.00,
  `base_imponible` decimal(14,2) DEFAULT NULL,
  `iva` decimal(14,2) DEFAULT NULL,
  `retencion` decimal(14,2) DEFAULT NULL,
  `total` decimal(14,2) DEFAULT NULL,
  `estado` enum('pendiente','solicitada','emitida','enviada','cobrada_parcial','cobrada','vencida','anulada') NOT NULL DEFAULT 'pendiente',
  `autofactura` tinyint(1) NOT NULL DEFAULT 0,
  `sociedad` varchar(180) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id_factura`, `id_contexto`, `id_trabajo`, `id_empresa_cliente`, `numero_factura`, `numero_factura_ccp`, `serie`, `orden_factura`, `fecha_solicitud`, `fecha_emision`, `fecha_vencimiento`, `importe`, `base_imponible`, `iva`, `retencion`, `total`, `estado`, `autofactura`, `sociedad`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'F-2026-001', NULL, 'M', 1, '2026-02-15', '2026-02-20', '2026-04-20', 950.00, 950.00, 199.50, NULL, 1149.50, 'emitida', 0, NULL, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 1, 2, 1, 'F-2026-002', NULL, 'M', 1, '2026-03-15', NULL, NULL, 380.00, 380.00, 79.80, NULL, 459.80, 'pendiente', 0, NULL, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 2, 3, 2, 'F-2026-003', NULL, 'R', 1, '2026-02-01', '2026-02-10', '2026-04-10', 980.00, 980.00, 205.80, NULL, 1185.80, 'cobrada', 0, NULL, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 2, 4, 2, 'F-2026-004', NULL, 'R', 1, '2026-03-10', NULL, NULL, 400.00, 400.00, 84.00, NULL, 484.00, 'pendiente', 0, NULL, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura_pedidos`
--

CREATE TABLE `factura_pedidos` (
  `id_factura_pedido` bigint(20) UNSIGNED NOT NULL,
  `id_factura` bigint(20) UNSIGNED NOT NULL,
  `id_pedido` bigint(20) UNSIGNED NOT NULL,
  `importe_aplicado` decimal(14,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `factura_pedidos`
--

INSERT INTO `factura_pedidos` (`id_factura_pedido`, `id_factura`, `id_pedido`, `importe_aplicado`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 950.00, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 2, 2, 380.00, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 3, 3, 980.00, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 4, 4, 400.00, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `importaciones`
--

CREATE TABLE `importaciones` (
  `id_importacion` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` bigint(20) UNSIGNED NOT NULL,
  `tipo` enum('estaciones','trabajos','tarifario','facturas') NOT NULL,
  `archivo_original` varchar(255) NOT NULL,
  `total_filas` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `filas_importadas` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `filas_con_error` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `filas_duplicadas` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `estado` enum('subido','validando','validado','importando','completado','fallido') NOT NULL DEFAULT 'subido',
  `version_importacion` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `importacion_filas`
--

CREATE TABLE `importacion_filas` (
  `id_importacion_fila` bigint(20) UNSIGNED NOT NULL,
  `id_importacion` bigint(20) UNSIGNED NOT NULL,
  `numero_fila` int(10) UNSIGNED NOT NULL,
  `datos_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`datos_json`)),
  `estado` enum('pendiente','valido','error','duplicado','importado') NOT NULL DEFAULT 'pendiente',
  `mensaje_error` text DEFAULT NULL,
  `id_registro_destino` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo_registro_destino` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

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
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `legalizaciones`
--

CREATE TABLE `legalizaciones` (
  `id_legalizacion` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_trabajo` bigint(20) UNSIGNED NOT NULL,
  `id_usuario_responsable` bigint(20) UNSIGNED DEFAULT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `legalizaciones`
--

INSERT INTO `legalizaciones` (`id_legalizacion`, `id_contexto`, `id_trabajo`, `id_usuario_responsable`, `tipo_legalizacion`, `numero_expediente`, `organismo`, `estado`, `descripcion_seleccionable`, `descripcion_libre`, `fecha_inicio`, `fecha_limite`, `fecha_resolucion`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'Licencia de apertura', 'EXP-M-001', 'Ayuntamiento de Sevilla', 'en_tramite', 'Tramitacion licencia apertura estacion', NULL, '2026-02-10', '2026-06-10', NULL, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 1, 2, NULL, 'Licencia de obras', 'EXP-M-002', 'Ayuntamiento de Sevilla', 'pendiente', 'Licencia obra menor marquesina', NULL, NULL, '2026-07-01', NULL, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 2, 3, NULL, 'Licencia de apertura', 'EXP-R-001', 'Ayuntamiento de Madrid', 'resuelta', 'Tramitacion licencia apertura estacion', NULL, '2026-01-20', '2026-05-20', '2026-03-18', NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 2, 4, NULL, 'Licencia de actividad', 'EXP-R-002', 'Com. Autonoma de Madrid', 'pendiente', 'Tramitacion licencia actividad', NULL, NULL, '2026-08-01', NULL, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `legalizaciones_contactos`
--

CREATE TABLE `legalizaciones_contactos` (
  `id_legalizacion_contacto` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_legalizacion` bigint(20) UNSIGNED NOT NULL,
  `id_contacto_empresa` bigint(20) UNSIGNED NOT NULL,
  `rol_en_legalizacion` varchar(150) DEFAULT NULL,
  `principal` tinyint(1) NOT NULL DEFAULT 0,
  `observaciones` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `legalizaciones_contactos`
--

INSERT INTO `legalizaciones_contactos` (`id_legalizacion_contacto`, `id_contexto`, `id_legalizacion`, `id_contacto_empresa`, `rol_en_legalizacion`, `principal`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 'Responsable tecnico', 1, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 1, 2, 3, 'Coordinadora obras', 1, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 2, 3, 4, 'Responsable tecnico', 1, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 2, 4, 5, 'Gestora tramites', 1, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes_internos`
--

CREATE TABLE `mensajes_internos` (
  `id_mensaje` bigint(20) UNSIGNED NOT NULL,
  `id_remitente` bigint(20) UNSIGNED NOT NULL,
  `id_destinatario` bigint(20) UNSIGNED DEFAULT NULL,
  `asunto` varchar(255) NOT NULL,
  `cuerpo` text NOT NULL,
  `prioridad` enum('normal','alta','urgente') NOT NULL DEFAULT 'normal',
  `es_aviso_sistema` tinyint(1) NOT NULL DEFAULT 0,
  `leido_at` datetime DEFAULT NULL,
  `archivado` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_03_24_000001_create_framework_support_tables', 1),
(2, '2026_03_24_000002_create_personal_access_tokens_table', 1),
(3, '2026_03_24_000010_create_security_core_tables', 1),
(4, '2026_03_24_000015_create_maestros_tables', 1),
(5, '2026_03_24_000020_create_empresas_contactos_base_tables', 1),
(6, '2026_03_24_000025_create_estaciones_tables', 1),
(7, '2026_03_24_000030_create_security_users_tables', 1),
(8, '2026_03_24_000035_create_tarifarios_tables', 1),
(9, '2026_03_24_000040_create_comunicacion_operativa_base_tables', 1),
(10, '2026_03_24_000050_create_trabajos_operativa_tables', 1),
(11, '2026_03_24_000055_create_presupuestos_tables', 1),
(12, '2026_03_24_000060_create_legalizaciones_tables', 1),
(13, '2026_03_24_000070_create_importacion_tables', 1),
(14, '2026_03_24_000080_create_audit_log_table', 1),
(15, '2026_04_13_000001_add_email_recuperacion_and_create_mensajes_tables', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_trabajo` bigint(20) UNSIGNED NOT NULL,
  `id_tarifario` bigint(20) UNSIGNED DEFAULT NULL,
  `numero_pedido` varchar(100) NOT NULL,
  `fecha_solicitud` date DEFAULT NULL,
  `fecha_recepcion` date DEFAULT NULL,
  `importe_pedido` decimal(14,2) NOT NULL DEFAULT 0.00,
  `importe_solicitado` decimal(14,2) DEFAULT NULL,
  `importe_facturado` decimal(14,2) DEFAULT NULL,
  `unidades_pedido` decimal(14,3) NOT NULL DEFAULT 1.000,
  `unidades_solicitadas` decimal(14,3) DEFAULT NULL,
  `estado` enum('pendiente','solicitado','recibido','en_ejecucion','facturado_parcial','facturado','cerrado','anulado') NOT NULL DEFAULT 'pendiente',
  `pedido_completo` tinyint(1) DEFAULT NULL,
  `tiene_mas_de_1_item` tinyint(1) DEFAULT NULL,
  `facturado_completo` tinyint(1) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido`, `fecha_solicitud`, `fecha_recepcion`, `importe_pedido`, `importe_solicitado`, `importe_facturado`, `unidades_pedido`, `unidades_solicitadas`, `estado`, `pedido_completo`, `tiene_mas_de_1_item`, `facturado_completo`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'PED-M-001', '2026-02-05', '2026-02-10', 1149.50, 1149.50, 1149.50, 1.000, 1.000, 'cerrado', 1, 0, 1, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 1, 2, 1, 'PED-M-002', '2026-03-12', NULL, 459.80, 459.80, 0.00, 1.000, 1.000, 'pendiente', 1, 0, 0, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 2, 3, 2, 'PED-R-001', '2026-01-20', '2026-01-25', 1185.80, 1185.80, 1185.80, 1.000, 1.000, 'cerrado', 1, 0, 1, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 2, 4, 2, 'PED-R-002', '2026-03-05', NULL, 484.00, 0.00, 0.00, 1.000, 0.000, 'pendiente', 0, 0, 0, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_items`
--

CREATE TABLE `pedido_items` (
  `id_pedido_item` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_pedido` bigint(20) UNSIGNED NOT NULL,
  `id_tarifario_linea` bigint(20) UNSIGNED DEFAULT NULL,
  `codigo_servicio` varchar(30) DEFAULT NULL,
  `numero_tarifa` varchar(30) DEFAULT NULL,
  `descripcion_servicio` varchar(255) DEFAULT NULL,
  `precio_unitario` decimal(14,2) NOT NULL DEFAULT 0.00,
  `cantidad` decimal(14,3) NOT NULL DEFAULT 1.000,
  `total_linea` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedido_items`
--

INSERT INTO `pedido_items` (`id_pedido_item`, `id_contexto`, `id_pedido`, `id_tarifario_linea`, `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'T001', NULL, 'Inspeccion tecnica inicial', 95.00, 10.000, 950.00, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 1, 2, 2, 'T002', NULL, 'Mantenimiento preventivo', 38.00, 10.000, 380.00, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 2, 3, 4, 'T001', NULL, 'Inspeccion tecnica inicial', 98.00, 10.000, 980.00, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 2, 4, 5, 'T002', NULL, 'Mantenimiento preventivo', 40.00, 10.000, 400.00, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id_permiso` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id_permiso`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Ver usuarios', 'usuarios.ver', 'Consulta de usuarios', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 'Gestionar usuarios', 'usuarios.gestionar', 'Alta, baja y edicion de usuarios', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 'Gestionar roles', 'roles.gestionar', 'Gestion de roles y permisos', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 'Ver trabajos', 'trabajos.ver', 'Consulta de trabajos', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(5, 'Crear trabajos', 'trabajos.crear', 'Creacion de trabajos', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(6, 'Editar trabajos', 'trabajos.editar', 'Edicion de trabajos', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(7, 'Cerrar trabajos', 'trabajos.cerrar', 'Cierre de trabajos', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(8, 'Reabrir trabajos', 'trabajos.reabrir', 'Reapertura de trabajos', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(9, 'Editar trabajos cerrados', 'trabajos_cerrados.editar', 'Edicion de trabajos cerrados', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(10, 'Reabrir trabajos cerrados', 'trabajos_cerrados.reabrir', 'Reapertura de trabajos cerrados', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(11, 'Ver presupuestos', 'presupuestos.ver', 'Consulta de presupuestos', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(12, 'Gestionar presupuestos', 'presupuestos.gestionar', 'Creacion y edicion de presupuestos', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(13, 'Ver pedidos', 'pedidos.ver', 'Consulta de pedidos', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(14, 'Gestionar pedidos', 'pedidos.gestionar', 'Creacion y edicion de pedidos', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(15, 'Ver facturas', 'facturas.ver', 'Consulta de facturas', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(16, 'Gestionar facturas', 'facturas.gestionar', 'Creacion y edicion de facturas', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(17, 'Gestionar cobros', 'cobros.gestionar', 'Registro y conciliacion de cobros', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(18, 'Ver legalizaciones', 'legalizaciones.ver', 'Consulta de legalizaciones', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(19, 'Gestionar legalizaciones', 'legalizaciones.gestionar', 'Gestion de legalizaciones', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(20, 'Ver estaciones', 'estaciones.ver', 'Consulta de estaciones de servicio', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(21, 'Gestionar estaciones', 'estaciones.gestionar', 'Gestion de estaciones', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(22, 'Ver tarifarios', 'tarifarios.ver', 'Consulta de tarifarios', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(23, 'Gestionar tarifarios', 'tarifarios.gestionar', 'Gestion de tarifarios', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(24, 'Gestionar empresas y contactos', 'empresas_contactos.gestionar', 'Gestion de empresas y contactos', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(25, 'Ver reportes', 'reportes.ver', 'Consulta de reportes', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(26, 'Ver importaciones', 'importaciones.ver', 'Consulta del historial de importaciones', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(27, 'Ejecutar importaciones', 'importaciones.ejecutar', 'Ejecutar importaciones de datos', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(28, 'Ver auditoria', 'auditoria.ver', 'Consulta del log de auditoria', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(29, 'Gestionar configuracion', 'config.gestionar', 'Gestion de maestros y configuracion del sistema', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(30, 'Ver contratos', 'contratos.ver', 'Consulta de contratos', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(31, 'Gestionar contratos', 'contratos.gestionar', 'Gestion de contratos', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuestos`
--

CREATE TABLE `presupuestos` (
  `id_presupuesto` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_trabajo` bigint(20) UNSIGNED NOT NULL,
  `id_empresa_cliente` bigint(20) UNSIGNED NOT NULL,
  `id_contacto_empresa_cliente` bigint(20) UNSIGNED DEFAULT NULL,
  `id_estacion_servicio` bigint(20) UNSIGNED DEFAULT NULL,
  `id_tarifario` bigint(20) UNSIGNED DEFAULT NULL,
  `id_usuario_responsable` bigint(20) UNSIGNED DEFAULT NULL,
  `id_usuario_cierre` bigint(20) UNSIGNED DEFAULT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `presupuestos`
--

INSERT INTO `presupuestos` (`id_presupuesto`, `id_contexto`, `id_trabajo`, `id_empresa_cliente`, `id_contacto_empresa_cliente`, `id_estacion_servicio`, `id_tarifario`, `id_usuario_responsable`, `id_usuario_cierre`, `codigo_presupuesto`, `nombre_presupuesto`, `estado`, `fecha_emision`, `fecha_validez`, `base_imponible`, `iva`, `retencion`, `total`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 2, 1, 1, NULL, NULL, 'PRES-M-001', 'Presupuesto NPV Moeve Sevilla', 'aprobado', '2026-02-03', '2026-05-03', 1140.00, 239.40, 0.00, 1379.40, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 1, 2, 1, 3, 1, 1, NULL, NULL, 'PRES-M-002', 'Presupuesto Reforma Moeve Sevilla', 'borrador', '2026-03-11', '2026-06-11', 844.00, 177.24, 0.00, 1021.24, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 2, 3, 2, 4, 2, 2, NULL, NULL, 'PRES-R-001', 'Presupuesto Diseno NPV Repsol Madrid', 'enviado', '2026-01-18', '2026-04-18', 1180.00, 247.80, 0.00, 1427.80, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 2, 4, 2, 5, 2, 2, NULL, NULL, 'PRES-R-002', 'Presupuesto Mto Repsol Madrid', 'borrador', '2026-03-03', '2026-06-03', 835.00, 175.35, 0.00, 1010.35, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_lineas`
--

CREATE TABLE `presupuesto_lineas` (
  `id_linea_presupuesto` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_presupuesto` bigint(20) UNSIGNED NOT NULL,
  `id_tarifario_linea` bigint(20) UNSIGNED DEFAULT NULL,
  `orden` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `concepto_seleccionable` varchar(255) DEFAULT NULL,
  `concepto_libre` text DEFAULT NULL,
  `cantidad` decimal(14,3) NOT NULL DEFAULT 1.000,
  `precio_unitario` decimal(14,2) NOT NULL DEFAULT 0.00,
  `iva_porcentaje` decimal(5,2) NOT NULL DEFAULT 21.00,
  `total_linea` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `presupuesto_lineas`
--

INSERT INTO `presupuesto_lineas` (`id_linea_presupuesto`, `id_contexto`, `id_presupuesto`, `id_tarifario_linea`, `orden`, `concepto_seleccionable`, `concepto_libre`, `cantidad`, `precio_unitario`, `iva_porcentaje`, `total_linea`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 'Inspeccion tecnica inicial', NULL, 10.000, 95.00, 21.00, 950.00, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 1, 1, 2, 2, 'Mantenimiento preventivo', NULL, 5.000, 38.00, 21.00, 190.00, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 1, 2, 3, 1, 'Adecuacion de instalaciones', NULL, 20.000, 27.00, 21.00, 540.00, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 1, 2, 2, 2, 'Mantenimiento preventivo', NULL, 8.000, 38.00, 21.00, 304.00, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(5, 2, 3, 4, 1, 'Inspeccion tecnica inicial', NULL, 10.000, 98.00, 21.00, 980.00, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(6, 2, 3, 5, 2, 'Mantenimiento preventivo', NULL, 5.000, 40.00, 21.00, 200.00, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(7, 2, 4, 6, 1, 'Adecuacion de instalaciones', NULL, 15.000, 29.00, 21.00, 435.00, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(8, 2, 4, 5, 2, 'Mantenimiento preventivo', NULL, 10.000, 40.00, 21.00, 400.00, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`, `slug`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin', 'Acceso total al ERP', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 'usuario', 'usuario', 'Gestion operativa ambos clientes', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 'cierre', 'cierre', 'Control de cierre de trabajos', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 'gestor_moeve', 'gestor_moeve', 'Gestion operativa exclusiva MOEVE', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(5, 'gestor_repsol', 'gestor_repsol', 'Gestion operativa exclusiva REPSOL', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_permisos`
--

CREATE TABLE `rol_permisos` (
  `id_rol_permiso` bigint(20) UNSIGNED NOT NULL,
  `id_rol` bigint(20) UNSIGNED NOT NULL,
  `id_permiso` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rol_permisos`
--

INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`, `created_at`) VALUES
(1, 1, 28, '2026-04-21 11:27:01'),
(2, 1, 17, '2026-04-21 11:27:01'),
(3, 1, 29, '2026-04-21 11:27:01'),
(4, 1, 31, '2026-04-21 11:27:01'),
(5, 1, 30, '2026-04-21 11:27:01'),
(6, 1, 24, '2026-04-21 11:27:01'),
(7, 1, 21, '2026-04-21 11:27:01'),
(8, 1, 20, '2026-04-21 11:27:01'),
(9, 1, 16, '2026-04-21 11:27:01'),
(10, 1, 15, '2026-04-21 11:27:01'),
(11, 1, 27, '2026-04-21 11:27:01'),
(12, 1, 26, '2026-04-21 11:27:01'),
(13, 1, 19, '2026-04-21 11:27:01'),
(14, 1, 18, '2026-04-21 11:27:01'),
(15, 1, 14, '2026-04-21 11:27:01'),
(16, 1, 13, '2026-04-21 11:27:01'),
(17, 1, 12, '2026-04-21 11:27:01'),
(18, 1, 11, '2026-04-21 11:27:01'),
(19, 1, 25, '2026-04-21 11:27:01'),
(20, 1, 3, '2026-04-21 11:27:01'),
(21, 1, 23, '2026-04-21 11:27:01'),
(22, 1, 22, '2026-04-21 11:27:01'),
(23, 1, 9, '2026-04-21 11:27:01'),
(24, 1, 10, '2026-04-21 11:27:01'),
(25, 1, 7, '2026-04-21 11:27:01'),
(26, 1, 5, '2026-04-21 11:27:01'),
(27, 1, 6, '2026-04-21 11:27:01'),
(28, 1, 8, '2026-04-21 11:27:01'),
(29, 1, 4, '2026-04-21 11:27:01'),
(30, 1, 2, '2026-04-21 11:27:01'),
(31, 1, 1, '2026-04-21 11:27:01'),
(32, 2, 4, '2026-04-21 11:27:01'),
(33, 2, 5, '2026-04-21 11:27:01'),
(34, 2, 6, '2026-04-21 11:27:01'),
(35, 2, 7, '2026-04-21 11:27:01'),
(36, 2, 8, '2026-04-21 11:27:01'),
(37, 2, 11, '2026-04-21 11:27:01'),
(38, 2, 12, '2026-04-21 11:27:01'),
(39, 2, 13, '2026-04-21 11:27:01'),
(40, 2, 14, '2026-04-21 11:27:01'),
(41, 2, 15, '2026-04-21 11:27:01'),
(42, 2, 16, '2026-04-21 11:27:01'),
(43, 2, 17, '2026-04-21 11:27:01'),
(44, 2, 18, '2026-04-21 11:27:01'),
(45, 2, 19, '2026-04-21 11:27:01'),
(46, 2, 20, '2026-04-21 11:27:01'),
(47, 2, 21, '2026-04-21 11:27:01'),
(48, 2, 22, '2026-04-21 11:27:01'),
(49, 2, 23, '2026-04-21 11:27:01'),
(50, 2, 30, '2026-04-21 11:27:01'),
(51, 2, 31, '2026-04-21 11:27:01'),
(52, 2, 24, '2026-04-21 11:27:01'),
(53, 2, 26, '2026-04-21 11:27:01'),
(54, 2, 27, '2026-04-21 11:27:01'),
(55, 2, 25, '2026-04-21 11:27:01'),
(56, 3, 4, '2026-04-21 11:27:01'),
(57, 3, 7, '2026-04-21 11:27:01'),
(58, 3, 8, '2026-04-21 11:27:01'),
(59, 3, 9, '2026-04-21 11:27:01'),
(60, 3, 10, '2026-04-21 11:27:01'),
(61, 3, 13, '2026-04-21 11:27:01'),
(62, 3, 18, '2026-04-21 11:27:01'),
(63, 3, 20, '2026-04-21 11:27:01'),
(64, 3, 21, '2026-04-21 11:27:01'),
(65, 3, 24, '2026-04-21 11:27:01'),
(66, 3, 25, '2026-04-21 11:27:01'),
(67, 4, 4, '2026-04-21 11:27:01'),
(68, 4, 5, '2026-04-21 11:27:01'),
(69, 4, 6, '2026-04-21 11:27:01'),
(70, 4, 7, '2026-04-21 11:27:01'),
(71, 4, 8, '2026-04-21 11:27:01'),
(72, 4, 11, '2026-04-21 11:27:01'),
(73, 4, 12, '2026-04-21 11:27:01'),
(74, 4, 13, '2026-04-21 11:27:01'),
(75, 4, 14, '2026-04-21 11:27:01'),
(76, 4, 15, '2026-04-21 11:27:01'),
(77, 4, 16, '2026-04-21 11:27:01'),
(78, 4, 17, '2026-04-21 11:27:01'),
(79, 4, 18, '2026-04-21 11:27:01'),
(80, 4, 19, '2026-04-21 11:27:01'),
(81, 4, 20, '2026-04-21 11:27:01'),
(82, 4, 21, '2026-04-21 11:27:01'),
(83, 4, 22, '2026-04-21 11:27:01'),
(84, 4, 23, '2026-04-21 11:27:01'),
(85, 4, 30, '2026-04-21 11:27:01'),
(86, 4, 31, '2026-04-21 11:27:01'),
(87, 4, 24, '2026-04-21 11:27:01'),
(88, 4, 26, '2026-04-21 11:27:01'),
(89, 4, 27, '2026-04-21 11:27:01'),
(90, 4, 25, '2026-04-21 11:27:01'),
(91, 5, 4, '2026-04-21 11:27:01'),
(92, 5, 5, '2026-04-21 11:27:01'),
(93, 5, 6, '2026-04-21 11:27:01'),
(94, 5, 7, '2026-04-21 11:27:01'),
(95, 5, 8, '2026-04-21 11:27:01'),
(96, 5, 11, '2026-04-21 11:27:01'),
(97, 5, 12, '2026-04-21 11:27:01'),
(98, 5, 13, '2026-04-21 11:27:01'),
(99, 5, 14, '2026-04-21 11:27:01'),
(100, 5, 15, '2026-04-21 11:27:01'),
(101, 5, 16, '2026-04-21 11:27:01'),
(102, 5, 17, '2026-04-21 11:27:01'),
(103, 5, 18, '2026-04-21 11:27:01'),
(104, 5, 19, '2026-04-21 11:27:01'),
(105, 5, 20, '2026-04-21 11:27:01'),
(106, 5, 21, '2026-04-21 11:27:01'),
(107, 5, 22, '2026-04-21 11:27:01'),
(108, 5, 23, '2026-04-21 11:27:01'),
(109, 5, 30, '2026-04-21 11:27:01'),
(110, 5, 31, '2026-04-21 11:27:01'),
(111, 5, 24, '2026-04-21 11:27:01'),
(112, 5, 26, '2026-04-21 11:27:01'),
(113, 5, 27, '2026-04-21 11:27:01'),
(114, 5, 25, '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_login`
--

CREATE TABLE `sesiones_login` (
  `id_sesion` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED DEFAULT NULL,
  `fecha_hora_login` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_hora_logout` datetime DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifarios`
--

CREATE TABLE `tarifarios` (
  `id_tarifario` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_contrato` bigint(20) UNSIGNED DEFAULT NULL,
  `nombre` varchar(160) NOT NULL,
  `version` varchar(40) DEFAULT NULL,
  `fecha_inicio_vigencia` date DEFAULT NULL,
  `fecha_fin_vigencia` date DEFAULT NULL,
  `factor_multiplicador` decimal(6,4) NOT NULL DEFAULT 1.0000,
  `moneda` char(3) NOT NULL DEFAULT 'EUR',
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tarifarios`
--

INSERT INTO `tarifarios` (`id_tarifario`, `id_contexto`, `id_contrato`, `nombre`, `version`, `fecha_inicio_vigencia`, `fecha_fin_vigencia`, `factor_multiplicador`, `moneda`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Tarifario Base MOEVE', '2026.1', '2026-01-01', NULL, 1.0000, 'EUR', NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 2, 2, 'Tarifario Base REPSOL', '2026.1', '2026-01-01', NULL, 1.0000, 'EUR', NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifario_lineas`
--

CREATE TABLE `tarifario_lineas` (
  `id_tarifario_linea` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_tarifario` bigint(20) UNSIGNED NOT NULL,
  `codigo_tarifa` varchar(30) NOT NULL,
  `grupo` varchar(120) DEFAULT NULL,
  `actuacion` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tarifa_anterior` decimal(14,2) DEFAULT NULL,
  `tarifa_base` decimal(14,2) NOT NULL,
  `tarifa_aplicada` decimal(14,2) NOT NULL,
  `id_unidad` bigint(20) UNSIGNED DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tarifario_lineas`
--

INSERT INTO `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`, `id_tarifario`, `codigo_tarifa`, `grupo`, `actuacion`, `descripcion`, `tarifa_anterior`, `tarifa_base`, `tarifa_aplicada`, `id_unidad`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'T001', 'Inspeccion', 'Inspeccion tecnica inicial', NULL, NULL, 95.00, 95.00, 2, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 1, 1, 'T002', 'Mantenimiento', 'Mantenimiento preventivo', NULL, NULL, 38.00, 38.00, 2, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 1, 1, 'T003', 'Obra civil', 'Adecuacion de instalaciones', NULL, NULL, 27.00, 27.00, 3, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 2, 2, 'T001', 'Inspeccion', 'Inspeccion tecnica inicial', NULL, NULL, 98.00, 98.00, 2, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(5, 2, 2, 'T002', 'Mantenimiento', 'Mantenimiento preventivo', NULL, NULL, 40.00, 40.00, 2, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(6, 2, 2, 'T003', 'Obra civil', 'Adecuacion de instalaciones', NULL, NULL, 29.00, 29.00, 3, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `telefonos`
--

CREATE TABLE `telefonos` (
  `id_telefono` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_empresa` bigint(20) UNSIGNED DEFAULT NULL,
  `id_contacto` bigint(20) UNSIGNED DEFAULT NULL,
  `id_contacto_empresa` bigint(20) UNSIGNED DEFAULT NULL,
  `id_usuario` bigint(20) UNSIGNED DEFAULT NULL,
  `prefijo` varchar(10) DEFAULT NULL,
  `numero` varchar(30) NOT NULL,
  `tipo` enum('fijo','movil','oficina','personal','urgencias','otro') NOT NULL DEFAULT 'movil',
  `es_principal` tinyint(1) NOT NULL DEFAULT 0,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `telefonos`
--

INSERT INTO `telefonos` (`id_telefono`, `id_contexto`, `id_empresa`, `id_contacto`, `id_contacto_empresa`, `id_usuario`, `prefijo`, `numero`, `tipo`, `es_principal`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL, NULL, '+34', '913456789', 'oficina', 1, 'Centralita MOEVE', '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 1, NULL, 2, NULL, NULL, '+34', '625001001', 'movil', 1, 'Movil Carlos Martinez', '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 2, 2, NULL, NULL, NULL, '+34', '917654321', 'oficina', 1, 'Centralita REPSOL', '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 2, NULL, 4, NULL, NULL, '+34', '625002001', 'movil', 1, 'Movil Pedro Garcia', '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_documento`
--

CREATE TABLE `tipos_documento` (
  `id_tipo_documento` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `tiene_doble_factura` tinyint(1) NOT NULL DEFAULT 0,
  `tiene_orden_mto` tinyint(1) NOT NULL DEFAULT 0,
  `tiene_num_tarifa` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipos_documento`
--

INSERT INTO `tipos_documento` (`id_tipo_documento`, `id_contexto`, `codigo`, `nombre`, `tiene_doble_factura`, `tiene_orden_mto`, `tiene_num_tarifa`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 'CONTROL_TRABAJOS', 'Control de Trabajos Moeve', 0, 0, 0, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 2, 'DISENO', 'Diseno Repsol', 0, 0, 1, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 2, 'EDIFICACION', 'Edificacion Repsol', 1, 0, 1, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 2, 'OBRAS', 'Obras Repsol', 1, 0, 1, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(5, 2, 'LICENCIAS', 'Licencias Repsol', 0, 0, 1, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(6, 2, 'FV', 'Fotovoltaica Repsol', 0, 0, 1, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(7, 2, 'ESTRUCTURAS', 'Estructuras y Vertidos Repsol', 1, 0, 1, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(8, 2, 'MTO', 'Mantenimiento Repsol', 1, 1, 1, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(9, 2, 'PUNTOS_RECARGA', 'Puntos de Recarga Repsol', 0, 0, 1, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_trabajo`
--

CREATE TABLE `tipos_trabajo` (
  `id_tipo_trabajo` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_tipo_documento` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(80) NOT NULL,
  `nombre` varchar(180) NOT NULL,
  `responsable_ciete_defecto` varchar(150) DEFAULT NULL,
  `responsable_cliente_defecto` varchar(150) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipos_trabajo`
--

INSERT INTO `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`, `id_tipo_documento`, `codigo`, `nombre`, `responsable_ciete_defecto`, `responsable_cliente_defecto`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'NPV', 'Nueva Propuesta de Valor', NULL, NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 1, 1, 'REFORMA', 'Reforma General', NULL, NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 1, 1, 'INDUSTRIA', 'Industria', NULL, NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 2, 2, 'NPV', 'Nueva Propuesta de Valor', NULL, NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(5, 2, 2, 'REFORMA', 'Reforma General', NULL, NULL, 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajos`
--

CREATE TABLE `trabajos` (
  `id_trabajo` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_empresa_cliente` bigint(20) UNSIGNED NOT NULL,
  `id_estacion_servicio` bigint(20) UNSIGNED DEFAULT NULL,
  `id_tipo_documento` bigint(20) UNSIGNED DEFAULT NULL,
  `id_tipo_trabajo` bigint(20) UNSIGNED DEFAULT NULL,
  `id_contrato` bigint(20) UNSIGNED DEFAULT NULL,
  `id_tarifario` bigint(20) UNSIGNED DEFAULT NULL,
  `id_responsable_ciete` bigint(20) UNSIGNED DEFAULT NULL,
  `id_usuario_cierre` bigint(20) UNSIGNED DEFAULT NULL,
  `numero_trabajo` int(10) UNSIGNED NOT NULL,
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
  `estado` enum('borrador','en_curso','terminado','cerrado','cancelado') NOT NULL DEFAULT 'borrador',
  `cerrado` tinyint(1) NOT NULL DEFAULT 0,
  `bloqueado_cierre` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_cierre` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `trabajos`
--

INSERT INTO `trabajos` (`id_trabajo`, `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_tipo_documento`, `id_tipo_trabajo`, `id_contrato`, `id_tarifario`, `id_responsable_ciete`, `id_usuario_cierre`, `numero_trabajo`, `numero_estacion`, `zona`, `descripcion_trabajo`, `fecha_encargo`, `fecha_terminacion`, `observaciones`, `numero_aviso`, `orden_mantenimiento`, `categoria`, `responsable_cliente`, `estado`, `cerrado`, `bloqueado_cierre`, `fecha_cierre`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 1, 1, 1, NULL, NULL, 1001, 'MOEVE-EST-001', 'SUR', 'NPV Estacion MOEVE Sevilla — nueva propuesta de valor', '2026-02-01', NULL, NULL, NULL, NULL, NULL, 'Carlos Martinez', 'en_curso', 0, 0, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 1, 1, 1, 1, 2, 1, 1, NULL, NULL, 1002, 'MOEVE-EST-001', 'SUR', 'Reforma general marquesina Estacion MOEVE Sevilla', '2026-03-10', NULL, NULL, NULL, NULL, NULL, 'Ana Lopez', 'borrador', 0, 0, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 2, 2, 2, 2, 4, 2, 2, NULL, NULL, 2001, 'REPSOL-EST-001', 'CENTRO', 'Diseno NPV Estacion REPSOL Madrid — nueva imagen', '2026-01-15', '2026-03-20', NULL, NULL, NULL, NULL, 'Pedro Garcia', 'terminado', 0, 0, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 2, 2, 2, 8, NULL, 2, 2, NULL, NULL, 2002, 'REPSOL-EST-001', 'CENTRO', 'Mantenimiento preventivo instalaciones REPSOL Madrid', '2026-03-01', NULL, NULL, 'AV-2026-0045', 'OM-2026-0012', NULL, 'Laura Sanchez', 'en_curso', 0, 0, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(5, 1, 1, 1, 1, 3, 1, 1, NULL, NULL, 1003, 'MOEVE-EST-001', 'SUR', 'Industria MOEVE Sevilla — adecuacion tecnica de instalaciones', '2026-01-20', '2026-02-18', 'Trabajo demo finalizado para pruebas de columnas de fecha de terminacion.', 'AV-MOEVE-0103', NULL, 'Industria', 'Carlos Martinez', 'terminado', 0, 0, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(6, 1, 1, 1, 1, 2, 1, 1, NULL, NULL, 1004, 'MOEVE-EST-001', 'SUR', 'Reforma cerrada MOEVE Sevilla — trabajo demo para cierre bloqueado', '2025-12-10', '2026-01-12', 'Trabajo demo cerrado y bloqueado.', 'AV-MOEVE-0104', NULL, 'Reforma', 'Ana Lopez', 'cerrado', 1, 1, '2026-01-15 09:00:00', '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(7, 2, 2, 2, 3, NULL, 2, 2, NULL, NULL, 2003, 'REPSOL-EST-001', 'CENTRO', 'Edificacion REPSOL Madrid — obra demo pendiente de inicio', '2026-04-01', NULL, 'Trabajo demo en borrador para validar columnas vacias.', NULL, NULL, 'Edificacion', 'Pedro Garcia', 'borrador', 0, 0, NULL, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(8, 2, 2, 2, 8, NULL, 2, 2, NULL, NULL, 2004, 'REPSOL-EST-001', 'CENTRO', 'Mantenimiento REPSOL Madrid — trabajo demo cerrado con OM', '2026-02-05', '2026-02-28', 'Demo REPSOL con orden de mantenimiento y cierre.', 'AV-2026-0088', 'OM-2026-0044', 'Mantenimiento', 'Laura Sanchez', 'cerrado', 1, 0, '2026-03-02 12:30:00', '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unidades`
--

CREATE TABLE `unidades` (
  `id_unidad` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `abreviatura` varchar(10) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `unidades`
--

INSERT INTO `unidades` (`id_unidad`, `nombre`, `abreviatura`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Unidad', 'ud', 'Unidad general', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(2, 'Hora', 'h', 'Hora de trabajo', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(3, 'Metro cuadrado', 'm2', 'Superficie', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01'),
(4, 'Metro lineal', 'ml', 'Longitud', 1, '2026-04-21 11:27:01', '2026-04-21 11:27:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_contacto_empresa` bigint(20) UNSIGNED DEFAULT NULL,
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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_contextos`
--

CREATE TABLE `usuario_contextos` (
  `id_usuario_contexto` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` bigint(20) UNSIGNED NOT NULL,
  `id_contexto` bigint(20) UNSIGNED NOT NULL,
  `es_contexto_principal` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_roles`
--

CREATE TABLE `usuario_roles` (
  `id_usuario_rol` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` bigint(20) UNSIGNED NOT NULL,
  `id_rol` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id_audit`),
  ADD KEY `idx_audit_tabla_registro` (`tabla`,`registro_id`),
  ADD KEY `idx_audit_usuario` (`id_usuario`),
  ADD KEY `idx_audit_fecha` (`created_at`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cobros`
--
ALTER TABLE `cobros`
  ADD PRIMARY KEY (`id_cobro`),
  ADD KEY `idx_cobros_contexto` (`id_contexto`),
  ADD KEY `idx_cobros_factura_contexto` (`id_factura`,`id_contexto`),
  ADD KEY `fk_cobros_usuario_registro` (`id_usuario_registro`);

--
-- Indices de la tabla `comentarios_legalizaciones`
--
ALTER TABLE `comentarios_legalizaciones`
  ADD PRIMARY KEY (`id_comentario_legalizacion`),
  ADD KEY `idx_comentarios_legalizaciones_contexto` (`id_contexto`),
  ADD KEY `idx_comentarios_legalizaciones_legalizacion_contexto` (`id_legalizacion`,`id_contexto`),
  ADD KEY `idx_comentarios_legalizaciones_usuario_contexto` (`id_usuario`,`id_contexto`);

--
-- Indices de la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD PRIMARY KEY (`id_contacto`),
  ADD UNIQUE KEY `uq_contactos_dni` (`dni`);

--
-- Indices de la tabla `contactos_empresas`
--
ALTER TABLE `contactos_empresas`
  ADD PRIMARY KEY (`id_contacto_empresa`),
  ADD UNIQUE KEY `uq_contactos_empresas_contexto_empresa_contacto` (`id_contexto`,`id_empresa`,`id_contacto`),
  ADD KEY `idx_contactos_empresas_contexto` (`id_contexto`),
  ADD KEY `idx_contactos_empresas_contacto` (`id_contacto`),
  ADD KEY `idx_contactos_empresas_id_contexto` (`id_contacto_empresa`,`id_contexto`),
  ADD KEY `idx_contactos_empresas_empresa_contexto` (`id_empresa`,`id_contexto`);

--
-- Indices de la tabla `contextos_cliente`
--
ALTER TABLE `contextos_cliente`
  ADD PRIMARY KEY (`id_contexto`),
  ADD UNIQUE KEY `uq_contextos_cliente_nombre` (`nombre`),
  ADD UNIQUE KEY `uq_contextos_cliente_codigo` (`codigo`);

--
-- Indices de la tabla `contratos`
--
ALTER TABLE `contratos`
  ADD PRIMARY KEY (`id_contrato`),
  ADD UNIQUE KEY `uq_contratos_ctx_codigo` (`id_contexto`,`codigo_contrato`),
  ADD KEY `idx_contratos_contexto` (`id_contexto`),
  ADD KEY `idx_contratos_id_contexto` (`id_contrato`,`id_contexto`),
  ADD KEY `idx_contratos_empresa_contexto` (`id_empresa_cliente`,`id_contexto`);

--
-- Indices de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD PRIMARY KEY (`id_direccion`),
  ADD KEY `idx_direcciones_contexto` (`id_contexto`),
  ADD KEY `idx_direcciones_empresa_contexto` (`id_empresa`,`id_contexto`),
  ADD KEY `idx_direcciones_contacto` (`id_contacto`),
  ADD KEY `idx_direcciones_contacto_empresa_contexto` (`id_contacto_empresa`,`id_contexto`),
  ADD KEY `idx_direcciones_usuario_contexto` (`id_usuario`,`id_contexto`);

--
-- Indices de la tabla `emails`
--
ALTER TABLE `emails`
  ADD PRIMARY KEY (`id_email`),
  ADD KEY `idx_emails_contexto` (`id_contexto`),
  ADD KEY `idx_emails_empresa_contexto` (`id_empresa`,`id_contexto`),
  ADD KEY `idx_emails_contacto` (`id_contacto`),
  ADD KEY `idx_emails_contacto_empresa_contexto` (`id_contacto_empresa`,`id_contexto`),
  ADD KEY `idx_emails_usuario_contexto` (`id_usuario`,`id_contexto`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id_empresa`),
  ADD UNIQUE KEY `uq_empresas_contexto_nombre` (`id_contexto`,`nombre`),
  ADD UNIQUE KEY `uq_empresas_contexto_cif` (`id_contexto`,`cif`),
  ADD KEY `idx_empresas_contexto` (`id_contexto`),
  ADD KEY `idx_empresas_id_contexto` (`id_empresa`,`id_contexto`),
  ADD KEY `idx_empresas_padre_contexto` (`empresa_padre_id`,`id_contexto`);

--
-- Indices de la tabla `estaciones_moeve_ext`
--
ALTER TABLE `estaciones_moeve_ext`
  ADD PRIMARY KEY (`id_estacion_servicio`);

--
-- Indices de la tabla `estaciones_repsol_ext`
--
ALTER TABLE `estaciones_repsol_ext`
  ADD PRIMARY KEY (`id_estacion_servicio`);

--
-- Indices de la tabla `estaciones_servicio`
--
ALTER TABLE `estaciones_servicio`
  ADD PRIMARY KEY (`id_estacion_servicio`),
  ADD UNIQUE KEY `uq_estaciones_ctx_codigo` (`id_contexto`,`codigo_estacion`),
  ADD KEY `idx_estaciones_contexto` (`id_contexto`),
  ADD KEY `idx_estaciones_id_contexto` (`id_estacion_servicio`,`id_contexto`),
  ADD KEY `idx_estaciones_empresa_contexto` (`id_empresa_cliente`,`id_contexto`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id_factura`),
  ADD KEY `idx_facturas_contexto` (`id_contexto`),
  ADD KEY `idx_facturas_id_contexto` (`id_factura`,`id_contexto`),
  ADD KEY `idx_facturas_trabajo_contexto` (`id_trabajo`,`id_contexto`),
  ADD KEY `idx_facturas_empresa_contexto` (`id_empresa_cliente`,`id_contexto`),
  ADD KEY `idx_facturas_numero_contexto` (`numero_factura`,`id_contexto`),
  ADD KEY `idx_facturas_estado_contexto` (`estado`,`id_contexto`),
  ADD KEY `idx_facturas_fecha_contexto` (`fecha_emision`,`id_contexto`);

--
-- Indices de la tabla `factura_pedidos`
--
ALTER TABLE `factura_pedidos`
  ADD PRIMARY KEY (`id_factura_pedido`),
  ADD UNIQUE KEY `uq_factura_pedido` (`id_factura`,`id_pedido`),
  ADD KEY `fk_factura_pedidos_pedido` (`id_pedido`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `importaciones`
--
ALTER TABLE `importaciones`
  ADD PRIMARY KEY (`id_importacion`),
  ADD KEY `idx_importaciones_contexto` (`id_contexto`),
  ADD KEY `idx_importaciones_usuario` (`id_usuario`);

--
-- Indices de la tabla `importacion_filas`
--
ALTER TABLE `importacion_filas`
  ADD PRIMARY KEY (`id_importacion_fila`),
  ADD KEY `idx_import_filas_importacion_estado` (`id_importacion`,`estado`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `legalizaciones`
--
ALTER TABLE `legalizaciones`
  ADD PRIMARY KEY (`id_legalizacion`),
  ADD UNIQUE KEY `uq_legalizaciones_contexto_num_expediente` (`id_contexto`,`numero_expediente`),
  ADD KEY `idx_legalizaciones_contexto` (`id_contexto`),
  ADD KEY `idx_legalizaciones_id_contexto` (`id_legalizacion`,`id_contexto`),
  ADD KEY `idx_legalizaciones_trabajo_contexto` (`id_trabajo`,`id_contexto`),
  ADD KEY `idx_legalizaciones_usuario_contexto` (`id_usuario_responsable`,`id_contexto`);

--
-- Indices de la tabla `legalizaciones_contactos`
--
ALTER TABLE `legalizaciones_contactos`
  ADD PRIMARY KEY (`id_legalizacion_contacto`),
  ADD UNIQUE KEY `uq_legalizaciones_contactos` (`id_contexto`,`id_legalizacion`,`id_contacto_empresa`),
  ADD KEY `idx_legalizaciones_contactos_contexto` (`id_contexto`),
  ADD KEY `idx_legalizaciones_contactos_legalizacion_contexto` (`id_legalizacion`,`id_contexto`),
  ADD KEY `idx_legalizaciones_contactos_contacto_contexto` (`id_contacto_empresa`,`id_contexto`);

--
-- Indices de la tabla `mensajes_internos`
--
ALTER TABLE `mensajes_internos`
  ADD PRIMARY KEY (`id_mensaje`),
  ADD KEY `idx_mensajes_remitente` (`id_remitente`),
  ADD KEY `idx_mensajes_destinatario` (`id_destinatario`),
  ADD KEY `idx_mensajes_leido` (`leido_at`),
  ADD KEY `idx_mensajes_fecha` (`created_at`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `idx_pedidos_contexto` (`id_contexto`),
  ADD KEY `idx_pedidos_id_contexto` (`id_pedido`,`id_contexto`),
  ADD KEY `idx_pedidos_trabajo_contexto` (`id_trabajo`,`id_contexto`),
  ADD KEY `idx_pedidos_numero_contexto` (`numero_pedido`,`id_contexto`),
  ADD KEY `idx_pedidos_estado_contexto` (`estado`,`id_contexto`),
  ADD KEY `idx_pedidos_tarifario_contexto` (`id_tarifario`,`id_contexto`);

--
-- Indices de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD PRIMARY KEY (`id_pedido_item`),
  ADD KEY `idx_pedido_items_contexto` (`id_contexto`),
  ADD KEY `idx_pedido_items_pedido_contexto` (`id_pedido`,`id_contexto`),
  ADD KEY `idx_pedido_items_tarifa_contexto` (`id_tarifario_linea`,`id_contexto`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id_permiso`),
  ADD UNIQUE KEY `uq_permisos_slug` (`slug`);

--
-- Indices de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indices de la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  ADD PRIMARY KEY (`id_presupuesto`),
  ADD UNIQUE KEY `uq_presupuestos_contexto_codigo` (`id_contexto`,`codigo_presupuesto`),
  ADD KEY `idx_presupuestos_contexto` (`id_contexto`),
  ADD KEY `idx_presupuestos_id_contexto` (`id_presupuesto`,`id_contexto`),
  ADD KEY `idx_presupuestos_trabajo_contexto` (`id_trabajo`,`id_contexto`),
  ADD KEY `idx_presupuestos_empresa_contexto` (`id_empresa_cliente`,`id_contexto`),
  ADD KEY `idx_presupuestos_estacion_contexto` (`id_estacion_servicio`,`id_contexto`),
  ADD KEY `idx_presupuestos_tarifario_contexto` (`id_tarifario`,`id_contexto`),
  ADD KEY `fk_presupuestos_contacto_contexto` (`id_contacto_empresa_cliente`,`id_contexto`),
  ADD KEY `fk_presupuestos_usuario_responsable` (`id_usuario_responsable`),
  ADD KEY `fk_presupuestos_usuario_cierre` (`id_usuario_cierre`);

--
-- Indices de la tabla `presupuesto_lineas`
--
ALTER TABLE `presupuesto_lineas`
  ADD PRIMARY KEY (`id_linea_presupuesto`),
  ADD UNIQUE KEY `uq_presupuesto_lineas_contexto_presup_orden` (`id_contexto`,`id_presupuesto`,`orden`),
  ADD KEY `idx_presupuesto_lineas_contexto` (`id_contexto`),
  ADD KEY `idx_presupuesto_lineas_presup_contexto` (`id_presupuesto`,`id_contexto`),
  ADD KEY `idx_presupuesto_lineas_tarifa_contexto` (`id_tarifario_linea`,`id_contexto`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `uq_roles_nombre` (`nombre`),
  ADD UNIQUE KEY `uq_roles_slug` (`slug`);

--
-- Indices de la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD PRIMARY KEY (`id_rol_permiso`),
  ADD UNIQUE KEY `uq_rol_permisos` (`id_rol`,`id_permiso`),
  ADD KEY `idx_rol_permisos_permiso` (`id_permiso`);

--
-- Indices de la tabla `sesiones_login`
--
ALTER TABLE `sesiones_login`
  ADD PRIMARY KEY (`id_sesion`),
  ADD KEY `idx_sesiones_login_usuario` (`id_usuario`),
  ADD KEY `idx_sesiones_login_contexto` (`id_contexto`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `tarifarios`
--
ALTER TABLE `tarifarios`
  ADD PRIMARY KEY (`id_tarifario`),
  ADD UNIQUE KEY `uq_tarifarios_ctx_nombre_version` (`id_contexto`,`nombre`,`version`),
  ADD KEY `idx_tarifarios_contexto` (`id_contexto`),
  ADD KEY `idx_tarifarios_id_contexto` (`id_tarifario`,`id_contexto`),
  ADD KEY `idx_tarifarios_contrato_contexto` (`id_contrato`,`id_contexto`);

--
-- Indices de la tabla `tarifario_lineas`
--
ALTER TABLE `tarifario_lineas`
  ADD PRIMARY KEY (`id_tarifario_linea`),
  ADD UNIQUE KEY `uq_tarifa_linea_ctx_tarif_codigo` (`id_contexto`,`id_tarifario`,`codigo_tarifa`),
  ADD KEY `idx_tarifario_lineas_contexto` (`id_contexto`),
  ADD KEY `idx_tarifario_lineas_id_contexto` (`id_tarifario_linea`,`id_contexto`),
  ADD KEY `idx_tarifario_lineas_tarifario_contexto` (`id_tarifario`,`id_contexto`),
  ADD KEY `fk_tarifario_lineas_unidad` (`id_unidad`);

--
-- Indices de la tabla `telefonos`
--
ALTER TABLE `telefonos`
  ADD PRIMARY KEY (`id_telefono`),
  ADD KEY `idx_telefonos_contexto` (`id_contexto`),
  ADD KEY `idx_telefonos_empresa_contexto` (`id_empresa`,`id_contexto`),
  ADD KEY `idx_telefonos_contacto` (`id_contacto`),
  ADD KEY `idx_telefonos_contacto_empresa_contexto` (`id_contacto_empresa`,`id_contexto`),
  ADD KEY `idx_telefonos_usuario_contexto` (`id_usuario`,`id_contexto`);

--
-- Indices de la tabla `tipos_documento`
--
ALTER TABLE `tipos_documento`
  ADD PRIMARY KEY (`id_tipo_documento`),
  ADD UNIQUE KEY `uq_tipo_doc_contexto_codigo` (`id_contexto`,`codigo`),
  ADD KEY `idx_tipos_documento_contexto` (`id_contexto`),
  ADD KEY `idx_tipos_documento_id_contexto` (`id_tipo_documento`,`id_contexto`);

--
-- Indices de la tabla `tipos_trabajo`
--
ALTER TABLE `tipos_trabajo`
  ADD PRIMARY KEY (`id_tipo_trabajo`),
  ADD UNIQUE KEY `uq_tipo_trabajo_ctx_doc_codigo` (`id_contexto`,`id_tipo_documento`,`codigo`),
  ADD KEY `idx_tipos_trabajo_contexto` (`id_contexto`),
  ADD KEY `idx_tipos_trabajo_tipo_doc_contexto` (`id_tipo_documento`,`id_contexto`),
  ADD KEY `idx_tipos_trabajo_id_contexto` (`id_tipo_trabajo`,`id_contexto`);

--
-- Indices de la tabla `trabajos`
--
ALTER TABLE `trabajos`
  ADD PRIMARY KEY (`id_trabajo`),
  ADD UNIQUE KEY `uq_trabajos_ctx_tipodoc_numero` (`id_contexto`,`id_tipo_documento`,`numero_trabajo`),
  ADD KEY `idx_trabajos_contexto` (`id_contexto`),
  ADD KEY `idx_trabajos_id_contexto` (`id_trabajo`,`id_contexto`),
  ADD KEY `idx_trabajos_empresa_contexto` (`id_empresa_cliente`,`id_contexto`),
  ADD KEY `idx_trabajos_estacion_contexto` (`id_estacion_servicio`,`id_contexto`),
  ADD KEY `idx_trabajos_tipo_doc_contexto` (`id_tipo_documento`,`id_contexto`),
  ADD KEY `idx_trabajos_tipo_trab_contexto` (`id_tipo_trabajo`,`id_contexto`),
  ADD KEY `idx_trabajos_contrato_contexto` (`id_contrato`,`id_contexto`),
  ADD KEY `idx_trabajos_tarifario_contexto` (`id_tarifario`,`id_contexto`),
  ADD KEY `idx_trabajos_estado_contexto` (`estado`,`id_contexto`),
  ADD KEY `idx_trabajos_responsable` (`id_responsable_ciete`),
  ADD KEY `idx_trabajos_fecha_encargo_contexto` (`fecha_encargo`,`id_contexto`),
  ADD KEY `fk_trabajos_usuario_cierre` (`id_usuario_cierre`);

--
-- Indices de la tabla `unidades`
--
ALTER TABLE `unidades`
  ADD PRIMARY KEY (`id_unidad`),
  ADD UNIQUE KEY `uq_unidades_abreviatura` (`abreviatura`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `uq_usuarios_contexto_nombre_usuario` (`id_contexto`,`nombre_usuario`),
  ADD UNIQUE KEY `uq_usuarios_email` (`email`),
  ADD KEY `idx_usuarios_contexto` (`id_contexto`),
  ADD KEY `idx_usuarios_id_contexto` (`id_usuario`,`id_contexto`),
  ADD KEY `idx_usuarios_contacto_empresa_contexto` (`id_contacto_empresa`,`id_contexto`);

--
-- Indices de la tabla `usuario_contextos`
--
ALTER TABLE `usuario_contextos`
  ADD PRIMARY KEY (`id_usuario_contexto`),
  ADD UNIQUE KEY `uq_usuario_contexto` (`id_usuario`,`id_contexto`),
  ADD KEY `idx_usuario_contextos_contexto` (`id_contexto`);

--
-- Indices de la tabla `usuario_roles`
--
ALTER TABLE `usuario_roles`
  ADD PRIMARY KEY (`id_usuario_rol`),
  ADD UNIQUE KEY `uq_usuario_roles_usuario_rol` (`id_usuario`,`id_rol`),
  ADD KEY `idx_usuario_roles_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id_audit` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cobros`
--
ALTER TABLE `cobros`
  MODIFY `id_cobro` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `comentarios_legalizaciones`
--
ALTER TABLE `comentarios_legalizaciones`
  MODIFY `id_comentario_legalizacion` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id_contacto` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `contactos_empresas`
--
ALTER TABLE `contactos_empresas`
  MODIFY `id_contacto_empresa` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `contextos_cliente`
--
ALTER TABLE `contextos_cliente`
  MODIFY `id_contexto` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `contratos`
--
ALTER TABLE `contratos`
  MODIFY `id_contrato` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  MODIFY `id_direccion` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `emails`
--
ALTER TABLE `emails`
  MODIFY `id_email` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id_empresa` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `estaciones_servicio`
--
ALTER TABLE `estaciones_servicio`
  MODIFY `id_estacion_servicio` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id_factura` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `factura_pedidos`
--
ALTER TABLE `factura_pedidos`
  MODIFY `id_factura_pedido` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `importaciones`
--
ALTER TABLE `importaciones`
  MODIFY `id_importacion` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `importacion_filas`
--
ALTER TABLE `importacion_filas`
  MODIFY `id_importacion_fila` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `legalizaciones`
--
ALTER TABLE `legalizaciones`
  MODIFY `id_legalizacion` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `legalizaciones_contactos`
--
ALTER TABLE `legalizaciones_contactos`
  MODIFY `id_legalizacion_contacto` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `mensajes_internos`
--
ALTER TABLE `mensajes_internos`
  MODIFY `id_mensaje` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  MODIFY `id_pedido_item` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permiso` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  MODIFY `id_presupuesto` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `presupuesto_lineas`
--
ALTER TABLE `presupuesto_lineas`
  MODIFY `id_linea_presupuesto` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  MODIFY `id_rol_permiso` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT de la tabla `sesiones_login`
--
ALTER TABLE `sesiones_login`
  MODIFY `id_sesion` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tarifarios`
--
ALTER TABLE `tarifarios`
  MODIFY `id_tarifario` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tarifario_lineas`
--
ALTER TABLE `tarifario_lineas`
  MODIFY `id_tarifario_linea` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `telefonos`
--
ALTER TABLE `telefonos`
  MODIFY `id_telefono` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tipos_documento`
--
ALTER TABLE `tipos_documento`
  MODIFY `id_tipo_documento` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `tipos_trabajo`
--
ALTER TABLE `tipos_trabajo`
  MODIFY `id_tipo_trabajo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `trabajos`
--
ALTER TABLE `trabajos`
  MODIFY `id_trabajo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `unidades`
--
ALTER TABLE `unidades`
  MODIFY `id_unidad` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario_contextos`
--
ALTER TABLE `usuario_contextos`
  MODIFY `id_usuario_contexto` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario_roles`
--
ALTER TABLE `usuario_roles`
  MODIFY `id_usuario_rol` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cobros`
--
ALTER TABLE `cobros`
  ADD CONSTRAINT `fk_cobros_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cobros_factura_contexto` FOREIGN KEY (`id_factura`,`id_contexto`) REFERENCES `facturas` (`id_factura`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cobros_usuario_registro` FOREIGN KEY (`id_usuario_registro`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `comentarios_legalizaciones`
--
ALTER TABLE `comentarios_legalizaciones`
  ADD CONSTRAINT `fk_comentarios_legalizaciones_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comentarios_legalizaciones_legalizacion_contexto` FOREIGN KEY (`id_legalizacion`,`id_contexto`) REFERENCES `legalizaciones` (`id_legalizacion`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comentarios_legalizaciones_usuario_contexto` FOREIGN KEY (`id_usuario`,`id_contexto`) REFERENCES `usuarios` (`id_usuario`, `id_contexto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `contactos_empresas`
--
ALTER TABLE `contactos_empresas`
  ADD CONSTRAINT `fk_contactos_empresas_contacto` FOREIGN KEY (`id_contacto`) REFERENCES `contactos` (`id_contacto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_contactos_empresas_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_contactos_empresas_empresa_contexto` FOREIGN KEY (`id_empresa`,`id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `contratos`
--
ALTER TABLE `contratos`
  ADD CONSTRAINT `fk_contratos_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_contratos_empresa_contexto` FOREIGN KEY (`id_empresa_cliente`,`id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD CONSTRAINT `fk_direcciones_contacto` FOREIGN KEY (`id_contacto`) REFERENCES `contactos` (`id_contacto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_direcciones_contacto_empresa_contexto` FOREIGN KEY (`id_contacto_empresa`,`id_contexto`) REFERENCES `contactos_empresas` (`id_contacto_empresa`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_direcciones_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_direcciones_empresa_contexto` FOREIGN KEY (`id_empresa`,`id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_direcciones_usuario_contexto` FOREIGN KEY (`id_usuario`,`id_contexto`) REFERENCES `usuarios` (`id_usuario`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `emails`
--
ALTER TABLE `emails`
  ADD CONSTRAINT `fk_emails_contacto` FOREIGN KEY (`id_contacto`) REFERENCES `contactos` (`id_contacto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_emails_contacto_empresa_contexto` FOREIGN KEY (`id_contacto_empresa`,`id_contexto`) REFERENCES `contactos_empresas` (`id_contacto_empresa`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_emails_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_emails_empresa_contexto` FOREIGN KEY (`id_empresa`,`id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_emails_usuario_contexto` FOREIGN KEY (`id_usuario`,`id_contexto`) REFERENCES `usuarios` (`id_usuario`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD CONSTRAINT `fk_empresas_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_empresas_padre_contexto` FOREIGN KEY (`empresa_padre_id`,`id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `estaciones_moeve_ext`
--
ALTER TABLE `estaciones_moeve_ext`
  ADD CONSTRAINT `fk_estaciones_moeve_ext_estacion` FOREIGN KEY (`id_estacion_servicio`) REFERENCES `estaciones_servicio` (`id_estacion_servicio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `estaciones_repsol_ext`
--
ALTER TABLE `estaciones_repsol_ext`
  ADD CONSTRAINT `fk_estaciones_repsol_ext_estacion` FOREIGN KEY (`id_estacion_servicio`) REFERENCES `estaciones_servicio` (`id_estacion_servicio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `estaciones_servicio`
--
ALTER TABLE `estaciones_servicio`
  ADD CONSTRAINT `fk_estaciones_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_estaciones_empresa_contexto` FOREIGN KEY (`id_empresa_cliente`,`id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `fk_facturas_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_facturas_empresa_contexto` FOREIGN KEY (`id_empresa_cliente`,`id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_facturas_trabajo_contexto` FOREIGN KEY (`id_trabajo`,`id_contexto`) REFERENCES `trabajos` (`id_trabajo`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `factura_pedidos`
--
ALTER TABLE `factura_pedidos`
  ADD CONSTRAINT `fk_factura_pedidos_factura` FOREIGN KEY (`id_factura`) REFERENCES `facturas` (`id_factura`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_pedidos_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `importaciones`
--
ALTER TABLE `importaciones`
  ADD CONSTRAINT `fk_importaciones_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_importaciones_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `importacion_filas`
--
ALTER TABLE `importacion_filas`
  ADD CONSTRAINT `fk_import_filas_importacion` FOREIGN KEY (`id_importacion`) REFERENCES `importaciones` (`id_importacion`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `legalizaciones`
--
ALTER TABLE `legalizaciones`
  ADD CONSTRAINT `fk_legalizaciones_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_legalizaciones_trabajo_contexto` FOREIGN KEY (`id_trabajo`,`id_contexto`) REFERENCES `trabajos` (`id_trabajo`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_legalizaciones_usuario_responsable` FOREIGN KEY (`id_usuario_responsable`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `legalizaciones_contactos`
--
ALTER TABLE `legalizaciones_contactos`
  ADD CONSTRAINT `fk_legalizaciones_contactos_contacto_contexto` FOREIGN KEY (`id_contacto_empresa`,`id_contexto`) REFERENCES `contactos_empresas` (`id_contacto_empresa`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_legalizaciones_contactos_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_legalizaciones_contactos_legalizacion_contexto` FOREIGN KEY (`id_legalizacion`,`id_contexto`) REFERENCES `legalizaciones` (`id_legalizacion`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `mensajes_internos`
--
ALTER TABLE `mensajes_internos`
  ADD CONSTRAINT `fk_mensajes_destinatario` FOREIGN KEY (`id_destinatario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mensajes_remitente` FOREIGN KEY (`id_remitente`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedidos_tarifario_contexto` FOREIGN KEY (`id_tarifario`,`id_contexto`) REFERENCES `tarifarios` (`id_tarifario`, `id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedidos_trabajo_contexto` FOREIGN KEY (`id_trabajo`,`id_contexto`) REFERENCES `trabajos` (`id_trabajo`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD CONSTRAINT `fk_pedido_items_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedido_items_pedido_contexto` FOREIGN KEY (`id_pedido`,`id_contexto`) REFERENCES `pedidos` (`id_pedido`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedido_items_tarifa_contexto` FOREIGN KEY (`id_tarifario_linea`,`id_contexto`) REFERENCES `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  ADD CONSTRAINT `fk_presupuestos_contacto_contexto` FOREIGN KEY (`id_contacto_empresa_cliente`,`id_contexto`) REFERENCES `contactos_empresas` (`id_contacto_empresa`, `id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_presupuestos_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_presupuestos_empresa_contexto` FOREIGN KEY (`id_empresa_cliente`,`id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_presupuestos_estacion_contexto` FOREIGN KEY (`id_estacion_servicio`,`id_contexto`) REFERENCES `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_presupuestos_tarifario_contexto` FOREIGN KEY (`id_tarifario`,`id_contexto`) REFERENCES `tarifarios` (`id_tarifario`, `id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_presupuestos_trabajo_contexto` FOREIGN KEY (`id_trabajo`,`id_contexto`) REFERENCES `trabajos` (`id_trabajo`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_presupuestos_usuario_cierre` FOREIGN KEY (`id_usuario_cierre`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_presupuestos_usuario_responsable` FOREIGN KEY (`id_usuario_responsable`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `presupuesto_lineas`
--
ALTER TABLE `presupuesto_lineas`
  ADD CONSTRAINT `fk_presupuesto_lineas_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_presupuesto_lineas_presup_contexto` FOREIGN KEY (`id_presupuesto`,`id_contexto`) REFERENCES `presupuestos` (`id_presupuesto`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_presupuesto_lineas_tarifa_contexto` FOREIGN KEY (`id_tarifario_linea`,`id_contexto`) REFERENCES `tarifario_lineas` (`id_tarifario_linea`, `id_contexto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD CONSTRAINT `fk_rol_permisos_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rol_permisos_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `sesiones_login`
--
ALTER TABLE `sesiones_login`
  ADD CONSTRAINT `fk_sesiones_login_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sesiones_login_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tarifarios`
--
ALTER TABLE `tarifarios`
  ADD CONSTRAINT `fk_tarifarios_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tarifarios_contrato_contexto` FOREIGN KEY (`id_contrato`,`id_contexto`) REFERENCES `contratos` (`id_contrato`, `id_contexto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tarifario_lineas`
--
ALTER TABLE `tarifario_lineas`
  ADD CONSTRAINT `fk_tarifario_lineas_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tarifario_lineas_tarifario_contexto` FOREIGN KEY (`id_tarifario`,`id_contexto`) REFERENCES `tarifarios` (`id_tarifario`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tarifario_lineas_unidad` FOREIGN KEY (`id_unidad`) REFERENCES `unidades` (`id_unidad`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `telefonos`
--
ALTER TABLE `telefonos`
  ADD CONSTRAINT `fk_telefonos_contacto` FOREIGN KEY (`id_contacto`) REFERENCES `contactos` (`id_contacto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_telefonos_contacto_empresa_contexto` FOREIGN KEY (`id_contacto_empresa`,`id_contexto`) REFERENCES `contactos_empresas` (`id_contacto_empresa`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_telefonos_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_telefonos_empresa_contexto` FOREIGN KEY (`id_empresa`,`id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_telefonos_usuario_contexto` FOREIGN KEY (`id_usuario`,`id_contexto`) REFERENCES `usuarios` (`id_usuario`, `id_contexto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tipos_documento`
--
ALTER TABLE `tipos_documento`
  ADD CONSTRAINT `fk_tipos_documento_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tipos_trabajo`
--
ALTER TABLE `tipos_trabajo`
  ADD CONSTRAINT `fk_tipos_trabajo_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tipos_trabajo_tipo_doc_contexto` FOREIGN KEY (`id_tipo_documento`,`id_contexto`) REFERENCES `tipos_documento` (`id_tipo_documento`, `id_contexto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `trabajos`
--
ALTER TABLE `trabajos`
  ADD CONSTRAINT `fk_trabajos_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_trabajos_contrato_contexto` FOREIGN KEY (`id_contrato`,`id_contexto`) REFERENCES `contratos` (`id_contrato`, `id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_trabajos_empresa_contexto` FOREIGN KEY (`id_empresa_cliente`,`id_contexto`) REFERENCES `empresas` (`id_empresa`, `id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_trabajos_estacion_contexto` FOREIGN KEY (`id_estacion_servicio`,`id_contexto`) REFERENCES `estaciones_servicio` (`id_estacion_servicio`, `id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_trabajos_responsable_ciete` FOREIGN KEY (`id_responsable_ciete`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_trabajos_tarifario_contexto` FOREIGN KEY (`id_tarifario`,`id_contexto`) REFERENCES `tarifarios` (`id_tarifario`, `id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_trabajos_tipo_doc_contexto` FOREIGN KEY (`id_tipo_documento`,`id_contexto`) REFERENCES `tipos_documento` (`id_tipo_documento`, `id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_trabajos_tipo_trab_contexto` FOREIGN KEY (`id_tipo_trabajo`,`id_contexto`) REFERENCES `tipos_trabajo` (`id_tipo_trabajo`, `id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_trabajos_usuario_cierre` FOREIGN KEY (`id_usuario_cierre`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_contacto_empresa_contexto` FOREIGN KEY (`id_contacto_empresa`,`id_contexto`) REFERENCES `contactos_empresas` (`id_contacto_empresa`, `id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuarios_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario_contextos`
--
ALTER TABLE `usuario_contextos`
  ADD CONSTRAINT `fk_usuario_contextos_contexto` FOREIGN KEY (`id_contexto`) REFERENCES `contextos_cliente` (`id_contexto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuario_contextos_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario_roles`
--
ALTER TABLE `usuario_roles`
  ADD CONSTRAINT `fk_usuario_roles_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuario_roles_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
