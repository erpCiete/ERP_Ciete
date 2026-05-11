-- ==========================================================
-- ERP CIETE - muestra operativa manual/controlada de 50 casos.
-- Fecha de preparacion/correccion: 2026-05-08.
--
-- Fuentes revisadas:
-- - docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx
-- - docs/Abaco/excelsactualizados/Repsol/Repsol/01 Control Trabajos DISEÑO REPSOL.xlsx
-- - docs/Abaco/excelsactualizados/Repsol/Repsol/02 Control Trabajos EDIFICACIÓN.xlsx
-- - docs/Abaco/excelsactualizados/Repsol/Repsol/03 Control Trabajos OBRAS REPSOL Z50.xlsx
-- - docs/Abaco/excelsactualizados/Repsol/Repsol/05 Control Trabajos LICENCIAS REPSOL.xlsx
-- - docs/Abaco/excelsactualizados/Repsol/Repsol/09 Control Trabajos FV REPSOL.xlsx
-- - docs/Abaco/excelsactualizados/Repsol/Repsol/10 Control Trabajos ESTRUCTURAS Y VERTIDOS REPSOL.xlsx
-- - docs/Abaco/excelsactualizados/Repsol/Repsol/12 Control Trabajos MTO REPSOL.xlsx
-- - docs/Abaco/excelsactualizados/Repsol/Repsol/13 Control Trabajos PUNTOS DE RECARGA.xlsx
-- - docs/02_CLIENTE/tareasComparar.md
--
-- Notas:
-- - Este script no modifica estructura ni crea tablas permanentes.
-- - La verdad de facturacion queda en facturas -> factura_items -> pedido_items -> pedidos -> trabajos.
-- - facturas.id_trabajo se rellena solo como cabecera auxiliar derivada.
-- - Si no hay numero de pedido, el trabajo se crea sin pedido, sin item y sin factura.
-- - Si no existe pedido_item enlazable, no se crea factura.
-- - OTROS CLIENTES se prepara como muestra controlada al no existir fuente separada cerrada.
-- ==========================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

SET @now := NOW();
SET @marker := _utf8mb4'[muestra-operativa-50]' COLLATE utf8mb4_unicode_ci;
SET @marker_like := CONCAT('%', @marker, '%');

-- ==========================================================
-- 1. Variables y staging temporal
-- ==========================================================

DROP TEMPORARY TABLE IF EXISTS tmp_muestra_operativa_50;
CREATE TEMPORARY TABLE tmp_muestra_operativa_50 (
    id_tmp INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    contexto_codigo VARCHAR(20) NOT NULL,
    fuente_documental VARCHAR(255) NOT NULL,
    hoja_fuente VARCHAR(120) NULL,
    cliente_nombre VARCHAR(180) NOT NULL,
    cliente_cif VARCHAR(20) NULL,
    estacion_codigo VARCHAR(80) NOT NULL,
    estacion_nombre VARCHAR(180) NOT NULL,
    estacion_poblacion VARCHAR(120) NULL,
    estacion_provincia VARCHAR(120) NULL,
    contrato_codigo VARCHAR(100) NOT NULL,
    contrato_nombre VARCHAR(180) NULL,
    sociedad_facturadora VARCHAR(180) NULL,
    sociedad_cif VARCHAR(20) NULL,
    tarifario_nombre VARCHAR(160) NOT NULL,
    tarifario_version VARCHAR(40) NULL,
    tarifa_codigo VARCHAR(30) NOT NULL,
    tarifa_grupo VARCHAR(120) NULL,
    tarifa_actuacion VARCHAR(255) NOT NULL,
    tipo_documento_codigo VARCHAR(30) NOT NULL,
    tipo_documento_nombre VARCHAR(120) NOT NULL,
    tipo_trabajo_codigo VARCHAR(80) NOT NULL,
    tipo_trabajo_nombre VARCHAR(180) NOT NULL,
    numero_trabajo INT UNSIGNED NOT NULL,
    numero_operativo VARCHAR(100) NULL,
    numero_aviso VARCHAR(100) NULL,
    orden_mantenimiento VARCHAR(100) NULL,
    descripcion_trabajo TEXT NULL,
    fecha_encargo DATE NULL,
    fecha_terminacion DATE NULL,
    responsable_ciete_nombre VARCHAR(150) NULL,
    responsable_ciete_email VARCHAR(180) NULL,
    responsable_cliente VARCHAR(150) NULL,
    categoria VARCHAR(100) NULL,
    estado_trabajo VARCHAR(30) NOT NULL,
    numero_pedido VARCHAR(100) NULL,
    fecha_solicitud_pedido DATE NULL,
    importe_pedido DECIMAL(14,2) NULL,
    pedido_estado VARCHAR(30) NULL,
    codigo_servicio VARCHAR(30) NULL,
    numero_tarifa VARCHAR(30) NULL,
    descripcion_servicio VARCHAR(255) NULL,
    precio_unitario DECIMAL(14,2) NULL,
    cantidad DECIMAL(14,3) NULL,
    total_linea DECIMAL(14,2) NULL,
    numero_factura VARCHAR(100) NULL,
    numero_factura_ccp VARCHAR(100) NULL,
    serie_factura VARCHAR(20) NULL,
    fecha_solicitud_factura DATE NULL,
    fecha_emision_factura DATE NULL,
    factura_estado VARCHAR(30) NULL,
    factura_importe DECIMAL(14,2) NULL,
    observaciones TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tmp_muestra_operativa_50 (
    contexto_codigo, fuente_documental, hoja_fuente, cliente_nombre, cliente_cif,
    estacion_codigo, estacion_nombre, estacion_poblacion, estacion_provincia,
    contrato_codigo, contrato_nombre, sociedad_facturadora, sociedad_cif,
    tarifario_nombre, tarifario_version, tarifa_codigo, tarifa_grupo, tarifa_actuacion,
    tipo_documento_codigo, tipo_documento_nombre, tipo_trabajo_codigo, tipo_trabajo_nombre,
    numero_trabajo, numero_operativo, numero_aviso, orden_mantenimiento, descripcion_trabajo,
    fecha_encargo, fecha_terminacion, responsable_ciete_nombre, responsable_ciete_email,
    responsable_cliente, categoria, estado_trabajo, numero_pedido, fecha_solicitud_pedido,
    importe_pedido, pedido_estado, codigo_servicio, numero_tarifa, descripcion_servicio,
    precio_unitario, cantidad, total_linea, numero_factura, numero_factura_ccp, serie_factura,
    fecha_solicitud_factura, fecha_emision_factura, factura_estado, factura_importe, observaciones
)
SELECT
    seed_rows.contexto_codigo,
    seed_rows.fuente_documental,
    seed_rows.hoja_fuente,
    seed_rows.cliente_nombre,
    seed_rows.cliente_cif,
    seed_rows.estacion_codigo,
    seed_rows.estacion_nombre,
    seed_rows.estacion_poblacion,
    seed_rows.estacion_provincia,
    seed_rows.contrato_codigo,
    seed_rows.contrato_nombre,
    seed_rows.sociedad_facturadora,
    seed_rows.sociedad_cif,
    seed_rows.tarifario_nombre,
    seed_rows.tarifario_version,
    seed_rows.tarifa_codigo,
    seed_rows.tarifa_grupo,
    seed_rows.tarifa_actuacion,
    seed_rows.tipo_documento_codigo,
    seed_rows.tipo_documento_nombre,
    seed_rows.tipo_trabajo_codigo,
    seed_rows.tipo_trabajo_nombre,
    seed_rows.numero_trabajo,
    seed_rows.numero_operativo,
    seed_rows.numero_aviso,
    seed_rows.orden_mantenimiento,
    seed_rows.descripcion_trabajo,
    seed_rows.fecha_encargo,
    seed_rows.fecha_terminacion,
    seed_rows.responsable_ciete_nombre,
    seed_rows.responsable_ciete_email,
    seed_rows.responsable_cliente,
    seed_rows.categoria,
    seed_rows.estado_trabajo,
    seed_rows.numero_pedido,
    seed_rows.fecha_solicitud_pedido,
    seed_rows.importe_pedido,
    seed_rows.pedido_estado,
    seed_rows.codigo_servicio,
    seed_rows.numero_tarifa,
    seed_rows.descripcion_servicio,
    seed_rows.precio_unitario,
    seed_rows.cantidad,
    seed_rows.total_linea,
    seed_rows.numero_factura,
    seed_rows.numero_factura_ccp,
    seed_rows.serie_factura,
    seed_rows.fecha_solicitud_factura,
    seed_rows.fecha_emision_factura,
    seed_rows.factura_estado,
    seed_rows.factura_importe,
    seed_rows.observaciones
FROM (
    SELECT
        'MOEVE' AS contexto_codigo,
        'docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx' AS fuente_documental,
        'Trabajos' AS hoja_fuente,
        'MOEVE' AS cliente_nombre,
        'A28003119' AS cliente_cif,
        '20673' AS estacion_codigo,
        'Virgisa' AS estacion_nombre,
        'Alcorcón' AS estacion_poblacion,
        'Madrid' AS estacion_provincia,
        'MOEVE-686' AS contrato_codigo,
        'Contrato Moeve 686' AS contrato_nombre,
        'CCP' AS sociedad_facturadora,
        NULL AS sociedad_cif,
        'Tarifario Moeve contrato 686' AS tarifario_nombre,
        'excel-real' AS tarifario_version,
        'MOEVE-OTROS' AS tarifa_codigo,
        'Otros' AS tarifa_grupo,
        '"Toma" ES. Cambio tit' AS tarifa_actuacion,
        'CONTROL_TRABAJOS' AS tipo_documento_codigo,
        'Control de Trabajos Moeve' AS tipo_documento_nombre,
        'OTROS' AS tipo_trabajo_codigo,
        'Otros' AS tipo_trabajo_nombre,
        1 AS numero_trabajo,
        NULL AS numero_operativo,
        NULL AS numero_aviso,
        NULL AS orden_mantenimiento,
        '"Toma" ES. Cambio tit' AS descripcion_trabajo,
        '2016-07-26' AS fecha_encargo,
        '2016-08-31' AS fecha_terminacion,
        'Amaya' AS responsable_ciete_nombre,
        'moeve@ciete.es' AS responsable_ciete_email,
        'Roberto Álvarez' AS responsable_cliente,
        'Otros' AS categoria,
        'facturado' AS estado_trabajo,
        '400243862' AS numero_pedido,
        '2016-07-26' AS fecha_solicitud_pedido,
        1315.00 AS importe_pedido,
        'facturado' AS pedido_estado,
        'MOEVE-OTROS' AS codigo_servicio,
        NULL AS numero_tarifa,
        '"Toma" ES. Cambio tit' AS descripcion_servicio,
        1315.00 AS precio_unitario,
        1 AS cantidad,
        1315.00 AS total_linea,
        'CCP-88725300029' AS numero_factura,
        '88725300029' AS numero_factura_ccp,
        'CCP' AS serie_factura,
        NULL AS fecha_solicitud_factura,
        '2016-08-31' AS fecha_emision_factura,
        'emitida' AS factura_estado,
        1315.00 AS factura_importe,
        'Excel Nº 1. Contrato 686.' AS observaciones
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','11496','Virgen de los Milagros','Palos de la Frontera','Huelva','MOEVE-686','Contrato Moeve 686','CEPSA',NULL,'Tarifario Moeve contrato 686','excel-real','MOEVE-OTROS','Otros','Modificación de registro industrial por cambio de AS','CONTROL_TRABAJOS','Control de Trabajos Moeve','OTROS','Otros',2,NULL,NULL,NULL,'Modificación de registro industrial por cambio de AS','2015-11-24','2017-04-18','César','cesar@ciete.es','Luis Alzola Würth','Otros','finalizado','400190035','2015-11-24',850.00,'facturado','MOEVE-OTROS',NULL,'Modificación de registro industrial por cambio de AS',850.00,1,850.00,'CEPSA-Factura-Ciete-41-17',NULL,'CEPSA',NULL,'2017-04-18','emitida',850.00,'Excel Nº 2. Observación: pedido real con facturación histórica.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','31592','Villeca I','Leganés','Madrid','MOEVE-686','Contrato Moeve 686','CCP',NULL,'Tarifario Moeve contrato 686','excel-real','MOEVE-ESTIMACION','Estimación','Implantación valorada','CONTROL_TRABAJOS','Control de Trabajos Moeve','ESTIMACION','Estimación',4,NULL,NULL,NULL,'Implantación valorada','2016-11-28','2016-12-15','Gabriela','moeve@ciete.es','Sara Esteban','Estimación','facturado','400262083','2016-11-28',2750.00,'facturado','MOEVE-ESTIMACION',NULL,'Implantación valorada',2750.00,1,2750.00,'CCP-88725300042','88725300042','CCP',NULL,'2016-12-15','emitida',2750.00,'Excel Nº 4. Contrato 686.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','31592','Villeca I','Leganés','Madrid','MOEVE-686','Contrato Moeve 686','CCP',NULL,'Tarifario Moeve contrato 686','excel-real','MOEVE-OTROS','Otros','"Toma" ES. Cambio tit','CONTROL_TRABAJOS','Control de Trabajos Moeve','OTROS','Otros',7,NULL,NULL,NULL,'"Toma" ES. Cambio tit','2016-06-03','2016-06-30','Amaya','moeve@ciete.es','Roberto Álvarez','Otros','facturado','400233671','2016-06-03',1485.00,'facturado','MOEVE-OTROS',NULL,'"Toma" ES. Cambio tit',1485.00,1,1485.00,'CCP-88725300021','88725300021','CCP',NULL,'2016-06-30','emitida',1485.00,'Excel Nº 7. Misma estación que Nº 4, pedido distinto.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','17157','Vilaboa','Vilaboa','Pontevedra','MOEVE-686','Contrato Moeve 686','CCP',NULL,'Tarifario Moeve contrato 686','excel-real','MOEVE-CUBIERTA','Cubierta','Informe cubiertas','CONTROL_TRABAJOS','Control de Trabajos Moeve','CUBIERTA','Cubierta',15,NULL,NULL,NULL,'Informe cubiertas','2016-04-27','2016-08-31','Almudena','moeve@ciete.es','Laura Martínez','Cubierta','facturado','400243490','2016-04-27',530.00,'facturado','MOEVE-CUBIERTA',NULL,'Informe cubiertas',530.00,1,530.00,'CCP-88725300029','88725300029','CCP',NULL,'2016-08-31','emitida',530.00,'Excel Nº 15. Factura compartida con Nº 1 en la muestra.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','12848','Sobreira','Sobreira','Orense','MOEVE-686','Contrato Moeve 686','CCP',NULL,'Tarifario Moeve contrato 686','excel-real','MOEVE-IMAGEN','Imagen','Cambio de imagen','CONTROL_TRABAJOS','Control de Trabajos Moeve','IMAGEN','Imagen',68,NULL,NULL,NULL,'Cambio de imagen','2017-08-24','2017-09-15','Almudena','moeve@ciete.es','Nuria Admetlla','Imagen','facturado','400239164','2017-08-24',1430.00,'facturado','MOEVE-IMAGEN',NULL,'Cambio de imagen',1430.00,1,1430.00,'CCP-88725300085','88725300085','CCP',NULL,'2017-09-15','emitida',1430.00,'Excel Nº 68.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','77402','San Miguel de Abona','San Miguel de Abona','Tenerife','MOEVE-686','Contrato Moeve 686',NULL,NULL,'Tarifario Moeve contrato 686','excel-real','MOEVE-SYS','SyS','Supervisión y CSS mensual','CONTROL_TRABAJOS','Control de Trabajos Moeve','SYS','Seguridad y salud',82,'Vicente San Martín',NULL,NULL,'Supervisión y CSS mensual','2016-01-27','2016-01-27','César','cesar@ciete.es','Carlos García Masip','SyS','cancelado',NULL,NULL,NULL,NULL,'MOEVE-SYS',NULL,'Supervisión y CSS mensual',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Excel Nº 82. Sin pedido ni factura en la fila fuente.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','11853','La Salobreja','Jaén','Jaén','MOEVE-772','Contrato Moeve 772','MV',NULL,'Tarifario Moeve contrato 772','excel-real','MOEVE-FOTOVOLTAICA','Fotovoltaica','Fotovoltaica - Obra - Bloque 24 Visita extra','CONTROL_TRABAJOS','Control de Trabajos Moeve','FOTOVOLTAICA','Fotovoltaica',5472,NULL,NULL,NULL,'Fotovoltaica - Obra - Bloque 24 Visita extra','2024-07-08','2024-07-08','Amaya','moeve@ciete.es','María Marín','Fotovoltaica','finalizado','300109709','2024-07-08',340.00,'facturado','MOEVE-FOTOVOLTAICA',NULL,'Fotovoltaica - Obra - Bloque 24 Visita extra',340.00,1,340.00,'MV-10240000887253000727','10240000887253000727','MV',NULL,'2025-12-15','emitida',340.00,'Excel Nº 5472. Contrato 772.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','31527','S’Agaró','Sant Feliu de Guíxols','Girona','MOEVE-772','Contrato Moeve 772','CCP',NULL,'Tarifario Moeve contrato 772','excel-real','MOEVE-TIENDAS','Tiendas','Córner R´spiro','CONTROL_TRABAJOS','Control de Trabajos Moeve','TIENDAS','Tiendas',5495,NULL,NULL,NULL,'Córner R´spiro','2024-06-19','2024-10-31','José Vergara','moeve@ciete.es','Jorge Ángel Castaño','Tiendas','facturado','300095780','2024-06-19',1014.23,'facturado','MOEVE-TIENDAS',NULL,'Córner R´spiro',1014.23,1,1014.23,'CCP-88725300634','88725300634','CCP',NULL,'2024-10-31','emitida',1014.23,'Excel Nº 5495.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','33467','Don Benito','Don Benito','Badajoz','MOEVE-772','Contrato Moeve 772','CCP',NULL,'Tarifario Moeve contrato 772','excel-real','MOEVE-TIENDAS','Tiendas','Córner R´spiro','CONTROL_TRABAJOS','Control de Trabajos Moeve','TIENDAS','Tiendas',5521,NULL,NULL,NULL,'Córner R´spiro','2024-07-15','2024-12-16','José Vergara','moeve@ciete.es','Jorge Ángel Castaño','Tiendas','facturado','300101590','2024-07-15',1014.23,'facturado','MOEVE-TIENDAS',NULL,'Córner R´spiro',1014.23,1,1014.23,'CCP-88725300645','88725300645','CCP',NULL,'2024-12-16','emitida',1014.23,'Excel Nº 5521.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','33979','La Pinada','Sagunto','Valencia','MOEVE-772','Contrato Moeve 772','CCP',NULL,'Tarifario Moeve contrato 772','excel-real','MOEVE-TIENDAS','Tiendas','Córner R´spiro','CONTROL_TRABAJOS','Control de Trabajos Moeve','TIENDAS','Tiendas',5643,NULL,NULL,NULL,'Córner R´spiro','2024-10-10','2024-12-16','José Vergara','moeve@ciete.es','Jorge Ángel Castaño','Tiendas','facturado','300108063','2024-10-10',1014.23,'facturado','MOEVE-TIENDAS',NULL,'Córner R´spiro',1014.23,1,1014.23,'CCP-88725300645','88725300645','CCP',NULL,'2024-12-16','emitida',1014.23,'Excel Nº 5643. Factura compartida con Nº 5521.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','31536','Ganosa','Madrid','Madrid','MOEVE-772','Contrato Moeve 772','MV',NULL,'Tarifario Moeve contrato 772','excel-real','MOEVE-IMAGEN','Imagen','Cambio de Imagen','CONTROL_TRABAJOS','Control de Trabajos Moeve','IMAGEN','Imagen',5815,NULL,NULL,NULL,'Cambio de Imagen','2024-12-02','2025-04-29','Almudena','moeve@ciete.es','Nuria Admetlla','Imagen','facturado','300108684','2024-12-02',4715.00,'facturado','MOEVE-IMAGEN',NULL,'Cambio de Imagen',4715.00,1,4715.00,'MV-10240000887253000666','10240000887253000666','MV',NULL,'2025-03-17','emitida',4715.00,'Excel Nº 5815.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','31626','San Simón','Vilaboa','Pontevedra','MOEVE-772','Contrato Moeve 772','MV',NULL,'Tarifario Moeve contrato 772','excel-real','MOEVE-TIENDAS','Tiendas','Reforma tienda - Fases I y II','CONTROL_TRABAJOS','Control de Trabajos Moeve','TIENDAS','Tiendas',5886,NULL,NULL,NULL,'Reforma tienda - Fases I y II','2025-01-09','2025-06-05','José Vergara','moeve@ciete.es','Jorge Ángel Castaño','Tiendas','finalizado','300115486','2025-01-09',7015.00,'facturado','MOEVE-TIENDAS',NULL,'Reforma tienda - Fases I y II',7015.00,1,7015.00,'MV-10240000887253000705','10240000887253000705','MV',NULL,'2025-09-15','emitida',7015.00,'Excel Nº 5886.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','15947','Boadilla','Boadilla del Monte','Madrid','MOEVE-772','Contrato Moeve 772','MV',NULL,'Tarifario Moeve contrato 772','excel-real','MOEVE-MANTENIMIENTO','Mantenimiento','Mantenimiento - BT Desfavorable','CONTROL_TRABAJOS','Control de Trabajos Moeve','MANTENIMIENTO','Mantenimiento',5997,'PPVV-159470-04853',NULL,NULL,'Mantenimiento - BT Desfavorable','2025-02-18','2026-01-26','César','cesar@ciete.es','Rita Bañuelos','Mantenimiento','facturado','7240363874','2025-02-18',800.00,'facturado','MOEVE-MANTENIMIENTO',NULL,'Mantenimiento - BT Desfavorable',800.00,1,800.00,'MV-10240000887253000745','10240000887253000745','MV',NULL,'2026-03-16','emitida',800.00,'Excel Nº 5997. Número operativo PPVV-159470-04853.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','33093','Aluche','Madrid','Madrid','MOEVE-772','Contrato Moeve 772','MV',NULL,'Tarifario Moeve contrato 772','excel-real','MOEVE-MANTENIMIENTO','Mantenimiento','Mantenimiento - Rejillas','CONTROL_TRABAJOS','Control de Trabajos Moeve','MANTENIMIENTO','Mantenimiento',6078,'PPVV-330930-00812',NULL,NULL,'Mantenimiento - Rejillas','2025-03-18','2025-04-01','César','cesar@ciete.es','Jorge Antolín','Mantenimiento','facturado','7240331513','2025-03-18',1150.00,'facturado','MOEVE-MANTENIMIENTO',NULL,'Mantenimiento - Rejillas',1150.00,1,1150.00,'MV-10240000887253000685','10240000887253000685','MV',NULL,'2025-05-16','emitida',1150.00,'Excel Nº 6078. Número operativo PPVV-330930-00812.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','19511','Araia','San Román de San Millán','Álava','MOEVE-772','Contrato Moeve 772',NULL,NULL,'Tarifario Moeve contrato 772','excel-real','MOEVE-TIENDAS','Tiendas','Reforma interior - Fase obra','CONTROL_TRABAJOS','Control de Trabajos Moeve','TIENDAS','Tiendas',6080,NULL,NULL,NULL,'Reforma interior - Fase obra','2025-03-18','2025-05-08','José Vergara','moeve@ciete.es','Giovanna Reyes Buendia','Tiendas','cancelado','300120873','2025-03-18',0.00,'anulado','MOEVE-TIENDAS',NULL,'Reforma interior - Fase obra',0.00,1,0.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Excel Nº 6080. Obra cancelada; pedido real con importe 0.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','11660','Villarejo I','Villarejo de Salvanés','Madrid','MOEVE-772','Contrato Moeve 772','MV',NULL,'Tarifario Moeve contrato 772','excel-real','MOEVE-MANTENIMIENTO','Mantenimiento','Mantenimiento - Pavimento','CONTROL_TRABAJOS','Control de Trabajos Moeve','MANTENIMIENTO','Mantenimiento',6440,'PPVV-116600-04778',NULL,NULL,'Mantenimiento - Pavimento','2025-11-21','2026-02-20','César','cesar@ciete.es','Jorge Antolín','Mantenimiento','facturado','7240361416','2025-11-21',1650.00,'facturado','MOEVE-MANTENIMIENTO',NULL,'Mantenimiento - Pavimento',1650.00,1,1650.00,'MV-10240000887253000742','10240000887253000742','MV',NULL,'2026-02-27','emitida',1650.00,'Excel Nº 6440. Número operativo PPVV-116600-04778.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','15208','Veracruz (Murcia)','Caravaca de la Cruz','Murcia','MOEVE-772','Contrato Moeve 772','MV',NULL,'Tarifario Moeve contrato 772','excel-real','MOEVE-LEGALIZACIONES','Legalizaciones','Legalización','CONTROL_TRABAJOS','Control de Trabajos Moeve','LEGALIZACIONES','Legalizaciones',6441,NULL,NULL,NULL,'Legalización','2025-11-21','2025-11-21','César','cesar@ciete.es','Beatriz Llueca','Legalizaciones','facturado','300142576','2025-11-21',10255.00,'facturado','MOEVE-LEGALIZACIONES',NULL,'Legalización',10255.00,1,10255.00,'MV-10240000887253000734','10240000887253000734','MV',NULL,'2026-01-31','emitida',10255.00,'Excel Nº 6441.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','33103','Ntra. Sra. de la Nieves','Arcos de la Frontera','Cádiz','MOEVE-772','Contrato Moeve 772',NULL,NULL,'Tarifario Moeve contrato 772','excel-real','MOEVE-SONDAS','Sondas','Sondas CODO PUES','CONTROL_TRABAJOS','Control de Trabajos Moeve','SONDAS','Sondas',6444,NULL,NULL,NULL,'Sondas CODO PUES','2025-11-25',NULL,'Amaya','moeve@ciete.es','Luis Alzola Würth','Sondas','pendiente_facturar',NULL,NULL,NULL,NULL,'MOEVE-SONDAS',NULL,'Sondas CODO PUES',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Excel Nº 6444. Sin número de pedido; falta justificante.'
    UNION ALL SELECT
        'MOEVE','docs/Abaco/excelsactualizados/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx','Trabajos','MOEVE','A28003119','7523','Brunete','Madrid','Madrid','MOEVE-772','Contrato Moeve 772',NULL,NULL,'Tarifario Moeve contrato 772','excel-real','MOEVE-MANTENIMIENTO','Mantenimiento','Mantenimiento - BT Desfavorable','CONTROL_TRABAJOS','Control de Trabajos Moeve','MANTENIMIENTO','Mantenimiento',6445,'PPVV-075230-06241',NULL,NULL,'Mantenimiento - BT Desfavorable','2025-11-25',NULL,'César','cesar@ciete.es','Rita Bañuelos','Mantenimiento','en_curso',NULL,NULL,NULL,NULL,'MOEVE-MANTENIMIENTO',NULL,'Mantenimiento - BT Desfavorable',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Excel Nº 6445. Sin pedido; número operativo PPVV-075230-06241.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/01 Control Trabajos DISEÑO REPSOL.xlsx','DISEÑO','REPSOL','A78374725','96322','CRED A. DE LA MIEL M.I.','Torremolinos','Málaga','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027','REPSOL S.A.','A78374725','Tarifario Repsol 2023-2027','excel-real','3012735-19.4.1','Diseño','19.4.1 Estudio de implantación reforma','DISENO','Diseño Repsol','REFORMA','Reforma',1,NULL,'140123365','40047241','Propuesta terraza, pipican, juegos infantiles, reforma edificio','2023-12-04','2023-12-13','GABRIELA GARCÍA','repsol@ciete.es','YOLANDA SEGOVIA','REFORMA','facturado','7200033371','2024-01-30',1123.54,'facturado','3012735','19.4.1','Estudio de implantación reforma',1123.54,1,1123.54,'R24109',NULL,'R','2024-06-01','2024-06-01','emitida',1123.54,'Diseño Nº 1. Aviso 140123365; orden 40047241.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/01 Control Trabajos DISEÑO REPSOL.xlsx','DISEÑO','REPSOL','A78374725','7630','E.S. ZOTAJO Y GAS S.L.','Lora del Río','Sevilla','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027','REPSOL S.A.','A78374725','Tarifario Repsol 2023-2027','excel-real','3012744','Diseño','Estudio implantación lavados KLIN','DISENO','Diseño Repsol','REFORMA','Reforma',2,NULL,'140123227','40047420','Puente y caseta en lugar de box y caseta actual. Modif aspiradoras','2023-12-15','2023-12-26','GABRIELA GARCÍA','repsol@ciete.es','MARI CARMEN','REFORMA','facturado','7200033370','2024-01-30',460.79,'facturado','3012744',NULL,'Estudio implantación lavados KLIN',460.79,1,460.79,'R24047',NULL,'R','2024-03-01','2024-03-01','emitida',460.79,'Diseño Nº 2. Aviso 140123227; orden 40047420.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/01 Control Trabajos DISEÑO REPSOL.xlsx','DISEÑO','REPSOL','A78374725','NPV-MONCADA','MONCADA','Moncada','Valencia','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027','REPSOL S.A.','A78374725','Tarifario Repsol 2023-2027','excel-real','3012704','Diseño','Alternativa implantación ES sin alzados edif no normalizado','DISENO','Diseño Repsol','NPV','Nueva Propuesta de Valor',3,NULL,'140065740','40022561','Nueva implantación por reducción de parcela','2023-12-19','2024-01-18','GABRIELA GARCÍA','repsol@ciete.es','YOLANDA SEGOVIA','NPV','finalizado','7200033375','2024-01-30',493.40,'facturado','3012704',NULL,'Alternativa implantación ES sin alzados edif no normalizado',493.40,1,493.40,'R24046',NULL,'R','2024-02-01','2024-02-01','emitida',493.40,'Diseño Nº 3. Estación NPV MONCADA.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/01 Control Trabajos DISEÑO REPSOL.xlsx','DISEÑO','REPSOL','A78374725','NUDO-ABRONIGAL','NUDO ABROÑIGAL','Madrid','Madrid','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027','REPSOL S.A.','A78374725','Tarifario Repsol 2023-2027','excel-real','3012673','Diseño','Certificado de compatibilidad urbanística','DISENO','Diseño Repsol','REFORMA_GENERAL','Reforma general',8,NULL,'140126134','40047580','Proyecto estación del futuro','2023-10-30','2024-01-18','GABRIELA GARCÍA','repsol@ciete.es','GONZALO PANIAGUA','REFORMA GENERAL','facturado','7200033663','2024-03-19',734.84,'facturado','3012673',NULL,'Certificado de compatibilidad urbanística',734.84,1,734.84,'R24256',NULL,'R','2024-06-01','2024-06-01','emitida',734.84,'Diseño Nº 8. Código de estación normalizado como NUDO-ABRONIGAL.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/02 Control Trabajos EDIFICACIÓN.xlsx','EDIFICACIÓN','REPSOL','A78374725','94957','ES COMBUSTIBLES TORREVIEJA SL','Torrevieja','Alicante','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027','REPSOL S.A.','A78374725','Tarifario Repsol 2023-2027','excel-real','3046492','Edificación','Tramitación de actos comunicados o declaraciones responsables','EDIFICACION','Edificación Repsol','STOPGO_MINI','Stop&Go Mini',17,NULL,'140123476','60258006','ACTOS COMUNICADOS O DR','2023-12-11','2024-01-25','JUAN CARLOS','repsol@ciete.es','BLAS DOMINGUEZ','STOP&GO MINI','facturado','7300576913','2024-05-23',276.15,'facturado','3046492',NULL,'Tramitación de actos comunicados o declaraciones responsables',276.15,1,276.15,'R24138',NULL,'R','2024-06-01','2024-06-01','emitida',276.15,'Edificación Nº 17.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/02 Control Trabajos EDIFICACIÓN.xlsx','EDIFICACIÓN','REPSOL','A78374725','7022','CRED LOECHES','Loeches','Madrid','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027',NULL,NULL,'Tarifario Repsol 2023-2027','excel-real','3012735','Edificación','Estudio de implantación / reforma. Incluye toma de datos','EDIFICACION','Edificación Repsol','STOPGO','Stop&Go',181,NULL,'140127382','40049720','TOMA DE DATOS + IMPL. EDIFICIO','2024-05-08',NULL,'CARMEN LÓPEZ','repsol@ciete.es','VIRGINIA TRUJILLO','STOP&GO','cancelado','7200036301','2024-12-03',1123.54,'anulado','3012735',NULL,'Estudio de implantación / reforma. Incluye toma de datos',1123.54,1,1123.54,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Edificación Nº 181. Trabajo cancelado; no se genera factura.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/02 Control Trabajos EDIFICACIÓN.xlsx','EDIFICACIÓN','REPSOL','A78374725','15981','E.S. G DE RECURSOS ENERGETICOS GUIL','Ubrique','Cádiz','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027',NULL,NULL,'Tarifario Repsol 2023-2027','excel-real','3012637','Edificación','Visita a Obra de técnico (<250 Km)','EDIFICACION','Edificación Repsol','RESTAURADORES','Restauradores',315,NULL,NULL,'60271829','VISITA OBRA TÉCNICO < 250Km','2024-11-14',NULL,'LOURDES REDONDO','repsol@ciete.es','PACO ALBALA','RESTAURADORES','cancelado','7300594140',NULL,293.46,'anulado','3012637',NULL,'Visita a Obra de técnico (<250 Km)',293.46,1,293.46,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Edificación Nº 315. Trabajo cancelado; sin factura.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/03 Control Trabajos OBRAS REPSOL Z50.xlsx','OBRAS Z50','REPSOL','A78374725','12141','CRED POLIGONO ANTEQUERA','Antequera','Málaga','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027','REPSOL S.A.','A78374725','Tarifario Repsol 2023-2027','excel-real','3052267-1.7','Obras','Tramitación de actos comunicados o declaraciones responsables','OBRAS','Obras Repsol','OTROS','Otros',1,NULL,'140130533','60272693','Saneamiento (dr de solo pista)','2024-09-02','2024-04-24','Jorge Albalá','repsol@ciete.es','Francisco López Toro','Otros','facturado','7300639881','2025-06-27',276.15,'facturado','3052267','1.7','Tramitación de actos comunicados o declaraciones responsables',276.15,1,276.15,'R25172',NULL,'R','2025-08-01','2025-08-01','emitida',276.15,'Obras Nº 1.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/03 Control Trabajos OBRAS REPSOL Z50.xlsx','OBRAS Z50','REPSOL','A78374725','96163','CDAD.TNPTE.PONIENTE','Mojonera, La','Almería','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027','REPSOL S.A.','A78374725','Tarifario Repsol 2023-2027','excel-real','3012747-19.7.1','Obras','Estudio de implantación / reforma con mediciones','OBRAS','Obras Repsol','OTROS','Otros',12,NULL,'140131234','40052042','Talud','2024-05-14','2025-06-13','Jorge Albalá','repsol@ciete.es','Francisco López Toro','Otros','finalizado','7200038950',NULL,453.33,'facturado','3012747','19.7.1','Estudio de implantación / reforma con mediciones',453.33,1,453.33,'R25113',NULL,'R','2025-06-01','2025-06-01','emitida',453.33,'Obras Nº 12.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/03 Control Trabajos OBRAS REPSOL Z50.xlsx','OBRAS Z50','REPSOL','A78374725','13096','CRED CARMONA - AUTOVIA','Carmona','Sevilla','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027',NULL,NULL,'Tarifario Repsol 2023-2027','excel-real','3012753-43.2','Obras','E.S.','OBRAS','Obras Repsol','OTROS','Otros',13,NULL,'140127218',NULL,'Saneamiento - vertidos','2024-05-14','2025-04-14','Juan Izquierdo','repsol@ciete.es','Alberto Menacho','Otros','pendiente_facturar',NULL,NULL,NULL,NULL,'3012753','43.2','E.S.',716.48,1,716.48,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Obras Nº 13. Sin número de pedido.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/05 Control Trabajos LICENCIAS REPSOL.xlsx','LICENCIAS','REPSOL','A78374725','NPV-MONCADA','NPV MONCADA','Moncada','Valencia','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027','REPSOL S.A.','A78374725','Tarifario Repsol 2023-2027','excel-real','3052267','Licencias','Tramitación actos comunicados o declarac respons','LICENCIAS','Licencias Repsol','LICENCIAS','Licencias',33,NULL,'140065740','40022561','SOLICITUD CCU','2024-02-20','2024-02-21','ÓSCAR GARCÍA','repsol@ciete.es','MAURO ARIZNAVARRETA','LICENCIAS','facturado','7200034983','2024-07-22',276.15,'facturado','3052267',NULL,'Tramitación actos comunicados o declarac respons',276.15,1,276.15,'R24185',NULL,'R','2024-08-01','2024-08-01','emitida',276.15,'Licencias Nº 33.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/05 Control Trabajos LICENCIAS REPSOL.xlsx','LICENCIAS','REPSOL','A78374725','31076','CRED LA BARROSA','Chiclana de la Frontera','Cádiz','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027','REPSOL S.A.','A78374725','Tarifario Repsol 2023-2027','excel-real','3012494','Licencias','Obtención de licencias de obras mayores o lega','LICENCIAS','Licencias Repsol','LICENCIAS','Licencias',51,NULL,'140120925','60251063','LICENCIA DE OBRA MARQUESINA','2025-02-18','2025-02-18','ÓSCAR GARCÍA','repsol@ciete.es','NOELIA GARCÍA','LICENCIAS','facturado','7300614004','2025-02-18',836.68,'facturado','3012494',NULL,'Obtención de licencias de obras mayores o lega',836.68,1,836.68,'R25050',NULL,'R','2025-03-01','2025-03-01','emitida',836.68,'Licencias Nº 51.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/05 Control Trabajos LICENCIAS REPSOL.xlsx','LICENCIAS','REPSOL','A78374725','97120','NPV MONCADA','Moncada','Valencia','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027',NULL,NULL,'Tarifario Repsol 2023-2027','excel-real','3012652','Licencias','Obtención de licencias de obras mayores o lega','LICENCIAS','Licencias Repsol','LICENCIAS','Licencias',79,NULL,'140131771','40022561','LICENCIAS NPV','2024-02-20','2025-05-20','ÓSCAR GARCÍA','repsol@ciete.es','YOLANDA SEGOVIA','LICENCIAS','pendiente_facturar','7200038943','2025-06-12',836.68,'solicitado','3012652',NULL,'Obtención de licencias de obras mayores o lega',836.68,1,836.68,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Licencias Nº 79. Pedido anulado; no se crea factura.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/09 Control Trabajos FV REPSOL.xlsx','FV','REPSOL','A78374725','33567','E.S. JUMCON, S. L.','Plasencia','Cáceres','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027','REPSOL S.A.','A78374725','Tarifario Repsol 2023-2027','excel-real','3061002','Fotovoltaica','Estudio de sombras e implantación de paneles en 2 posiciones','FV','Fotovoltaica Repsol','ESTUDIO_SOMBRAS','Estudio sombras',1,NULL,NULL,'40047622','Estudio de sombras e implantación de paneles en 2 posiciones','2023-01-22','2024-01-23','ISABEL GONZÁLEZ','repsol@ciete.es','BORJA DOMÍNGUEZ','ESTUDIO SOMBRAS','facturado','7200034666',NULL,142.28,'facturado','3061002',NULL,'Estudio de sombras e implantación de paneles en 2 posiciones',142.28,1,142.28,'R24136',NULL,'R','2024-07-01','2024-07-01','emitida',142.28,'FV Nº 1.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/09 Control Trabajos FV REPSOL.xlsx','FV','REPSOL','A78374725','34136','GESDEGAS,S.L.','Mejorada del Campo','Madrid','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027',NULL,NULL,'Tarifario Repsol 2023-2027','excel-real','REPSOL-FV-SOMBRAS','Fotovoltaica','Estudio de sombras e implantación de paneles en 2 posiciones','FV','Fotovoltaica Repsol','ESTUDIO_SOMBRAS','Estudio sombras',2,NULL,NULL,NULL,'Estudio de sombras e implantación de paneles en 2 posiciones','2023-01-22','2024-01-23','ALVARO RIO','repsol@ciete.es','BORJA DOMÍNGUEZ','ESTUDIO SOMBRAS','pendiente_facturar',NULL,NULL,NULL,NULL,'REPSOL-FV-SOMBRAS',NULL,'Estudio de sombras e implantación de paneles en 2 posiciones',142.28,1,142.28,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'FV Nº 2. Sin número de pedido.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/10 Control Trabajos ESTRUCTURAS Y VERTIDOS REPSOL.xlsx','ESTRUCTURAS','REPSOL','A78374725','6737','ROLO S.L.','Coria','Cáceres','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027','REPSOL S.A.','A78374725','Tarifario Repsol 2023-2027','excel-real','3012797','Estructuras','Titulado Superior','ESTRUCTURAS','Estructuras y Vertidos Repsol','PATOLOGIA','Patología',1,NULL,'140123203','40047480','Titulado Superior','2023-12-11','2024-01-16','ALVARO RIO','repsol@ciete.es','MAURO ARIZNAVARRETA','PATOLOGÍA','finalizado','7200034009','2024-04-02',1115.89,'facturado','3012797',NULL,'Titulado Superior',37.20,30,1115.89,'R24084',NULL,'R','2024-05-01','2024-05-01','emitida',1115.89,'Estructuras Nº 1.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/12 Control Trabajos MTO REPSOL.xlsx','MTO','REPSOL','A78374725','11301','CRED SAN ADRIAN','San Adrián','Navarra','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027','REPSOL S.A.','A78374725','Tarifario Repsol 2023-2027','excel-real','3012753','Mantenimiento','Actualización registro industrial','MTO','Mantenimiento Repsol','INDUSTRIA','Industria',1,NULL,'140123500','50623115','Baja de tanque','2023-12-21','2024-02-16','ISABEL GONZÁLEZ','repsol@ciete.es','ELENA GONZÁLEZ','INDUSTRIA','facturado','7300564645','2024-02-15',716.48,'facturado','3012753',NULL,'Actualización registro industrial',716.48,1,716.48,'R24110',NULL,'R','2024-05-01','2024-05-01','emitida',716.48,'MTO Nº 1.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/12 Control Trabajos MTO REPSOL.xlsx','MTO','REPSOL','A78374725','96060','CRED GALAPAGAR','Galapagar','Madrid','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027','REPSOL S.A.','A78374725','Tarifario Repsol 2023-2027','excel-real','3012492','Mantenimiento','Gestiones con Organismos. Sin visita','MTO','Mantenimiento Repsol','INDUSTRIA','Industria',2,NULL,'pedido por mail 25/01/24','60267295','Legalización ES','2024-01-15','2023-01-16','ISABEL GONZÁLEZ','repsol@ciete.es','ARMANDO GONZÁLEZ','INDUSTRIA','facturado','7300581067','2024-01-03',109.77,'facturado','3012492',NULL,'Gestiones con Organismos. Sin visita (pedir doc a Industria)',109.77,1,109.77,'R240150',NULL,'R','2024-07-01','2024-07-01','emitida',109.77,'MTO Nº 2. Pedido referido literalmente en la fuente.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/13 Control Trabajos PUNTOS DE RECARGA.xlsx','PUNTOS_RECARGA','REPSOL','A78374725','33785','SURTIMOVIL ALMURADIE','Almuradiel','Ciudad Real','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027','REPSOL S.A.','A78374725','Tarifario Repsol 2023-2027','excel-real','3052254','Puntos de recarga','Legalización puntos de recarga en BT','PUNTOS_RECARGA','Puntos de Recarga Repsol','PUNTOS_RECARGA','Puntos de recarga',1,NULL,NULL,'60204249','Legalización puntos de recarga en BT',NULL,'2000-01-01',NULL,'repsol@ciete.es',NULL,'PUNTOS_RECARGA','facturado','7300634850',NULL,451.90,'facturado','3052254',NULL,'Legalización puntos de recarga en BT',451.90,1,451.90,'R25129',NULL,'R','2025-07-01','2025-07-01','emitida',451.90,'Puntos de recarga Nº 1. La fecha 2000-01-01 viene de la fuente.'
    UNION ALL SELECT
        'REPSOL','docs/Abaco/excelsactualizados/Repsol/Repsol/13 Control Trabajos PUNTOS DE RECARGA.xlsx','PUNTOS_RECARGA','REPSOL','A78374725','96795','GPV-PAU VALLECAS-139','Madrid','Madrid','REPSOL-MARCO-2024-2027','Contrato marco Repsol trabajos Excel 2024-2027',NULL,NULL,'Tarifario Repsol 2023-2027','excel-real','3064341','Puntos de recarga','Mov. Electrica: Implantación hasta 150kw','PUNTOS_RECARGA','Puntos de Recarga Repsol','PUNTOS_RECARGA','Puntos de recarga',6,NULL,NULL,NULL,'Mov. Electrica: Implantación hasta 150kw',NULL,'2000-01-06',NULL,'repsol@ciete.es',NULL,'PUNTOS_RECARGA','pendiente_facturar',NULL,NULL,NULL,NULL,'3064341',NULL,'Mov. Electrica: Implantación hasta 150kw',88.93,3,266.79,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Puntos de recarga Nº 6. Sin número de pedido.'
    UNION ALL SELECT
        'OTROS','docs/02_CLIENTE/tareasComparar.md','demo-controlado','Cliente Demo Industrial Norte','B90000001','OTR-NORTE-01','Planta Norte 01','Burgos','Burgos','OTROS-DEMO-2026','Contrato demo otros clientes 2026','Cliente Demo Industrial Norte','B90000001','Tarifario otros demo 2026','demo','OTR-ING-01','Ingeniería','Informe técnico industrial','OTROS','Trabajo otros clientes','INFORME_TECNICO','Informe técnico',90001,NULL,NULL,NULL,'Informe técnico industrial para adecuación de instalación','2026-04-01','2026-04-12','César','cesar@ciete.es','Responsable demo norte','Otros','facturado','OTR-DEMO-2026-0001','2026-04-01',1250.00,'facturado','OTR-ING-01',NULL,'Informe técnico industrial',1250.00,1,1250.00,'OTR-F-2026-001',NULL,'OTR','2026-04-15','2026-04-15','emitida',1250.00,'[demo-controlado-otros] Caso creado al no existir fuente real separada para OTROS.'
    UNION ALL SELECT
        'OTROS','docs/02_CLIENTE/tareasComparar.md','demo-controlado','Cliente Demo Retail Sur','B90000002','OTR-SUR-01','Retail Sur 01','Sevilla','Sevilla','OTROS-DEMO-2026','Contrato demo otros clientes 2026',NULL,NULL,'Tarifario otros demo 2026','demo','OTR-MTO-01','Mantenimiento','Revisión eléctrica básica','OTROS','Trabajo otros clientes','MANTENIMIENTO','Mantenimiento',90002,NULL,NULL,NULL,'Revisión eléctrica básica de local comercial','2026-04-02',NULL,'Amaya','usuario@ciete.es','Responsable demo sur','Otros','en_curso',NULL,NULL,NULL,NULL,'OTR-MTO-01',NULL,'Revisión eléctrica básica',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'[demo-controlado-otros] Trabajo en curso sin pedido.'
    UNION ALL SELECT
        'OTROS','docs/02_CLIENTE/tareasComparar.md','demo-controlado','Cliente Demo Urbanismo Centro','B90000003','OTR-CENTRO-01','Parcela Centro 01','Madrid','Madrid','OTROS-DEMO-2026','Contrato demo otros clientes 2026',NULL,NULL,'Tarifario otros demo 2026','demo','OTR-URB-01','Urbanismo','Consulta urbanística','OTROS','Trabajo otros clientes','URBANISMO','Urbanismo',90003,NULL,NULL,NULL,'Consulta urbanística para reforma menor','2026-04-03','2026-04-09','César','cesar@ciete.es','Responsable demo centro','Otros','pendiente_facturar','OTR-DEMO-2026-0003','2026-04-03',680.00,'recibido','OTR-URB-01',NULL,'Consulta urbanística',680.00,1,680.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'[demo-controlado-otros] Pedido demo sin factura.'
    UNION ALL SELECT
        'OTROS','docs/02_CLIENTE/tareasComparar.md','demo-controlado','Cliente Demo Energía Levante','B90000004','OTR-LEVANTE-01','Energía Levante 01','Valencia','Valencia','OTROS-DEMO-2026','Contrato demo otros clientes 2026',NULL,NULL,'Tarifario otros demo 2026','demo','OTR-ENE-01','Energía','Estudio de ahorro energético','OTROS','Trabajo otros clientes','ENERGIA','Energía',90004,NULL,NULL,NULL,'Estudio de ahorro energético','2026-04-04','2026-04-18','Almudena','usuario@ciete.es','Responsable demo levante','Otros','terminado',NULL,NULL,NULL,NULL,'OTR-ENE-01',NULL,'Estudio de ahorro energético',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'[demo-controlado-otros] Terminado sin pedido.'
    UNION ALL SELECT
        'OTROS','docs/02_CLIENTE/tareasComparar.md','demo-controlado','Cliente Demo Logística Oeste','B90000005','OTR-OESTE-01','Nave Logística Oeste','Badajoz','Badajoz','OTROS-DEMO-2026','Contrato demo otros clientes 2026','Cliente Demo Logística Oeste','B90000005','Tarifario otros demo 2026','demo','OTR-LOG-01','Logística','Proyecto básico de nave','OTROS','Trabajo otros clientes','PROYECTO_BASICO','Proyecto básico',90005,NULL,NULL,NULL,'Proyecto básico de nave logística','2026-04-05','2026-04-22','José Vergara','usuario@ciete.es','Responsable demo oeste','Otros','finalizado','OTR-DEMO-2026-0005','2026-04-05',2420.00,'facturado','OTR-LOG-01',NULL,'Proyecto básico de nave',2420.00,1,2420.00,'OTR-F-2026-005',NULL,'OTR','2026-04-25','2026-04-25','enviada',2420.00,'[demo-controlado-otros] Pedido y factura enviada demo.'
    UNION ALL SELECT
        'OTROS','docs/02_CLIENTE/tareasComparar.md','demo-controlado','Cliente Demo Administración Local','B90000006','OTR-ADM-01','Edificio Municipal Demo','Toledo','Toledo','OTROS-DEMO-2026','Contrato demo otros clientes 2026',NULL,NULL,'Tarifario otros demo 2026','demo','OTR-LIC-01','Licencias','Licencia de actividad','OTROS','Trabajo otros clientes','LICENCIAS','Licencias',90006,NULL,NULL,NULL,'Licencia de actividad local municipal','2026-04-06','2026-04-06','César','cesar@ciete.es','Responsable demo administración','Otros','cancelado',NULL,NULL,NULL,NULL,'OTR-LIC-01',NULL,'Licencia de actividad',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'[demo-controlado-otros] Cancelado sin pedido.'
    UNION ALL SELECT
        'OTROS','docs/02_CLIENTE/tareasComparar.md','demo-controlado','Cliente Demo Patrimonial','B90000007','OTR-PAT-01','Inmueble Patrimonial 01','Granada','Granada','OTROS-DEMO-2026','Contrato demo otros clientes 2026',NULL,NULL,'Tarifario otros demo 2026','demo','OTR-PAT-01','Patrimonial','Informe de estado general','OTROS','Trabajo otros clientes','INFORME_TECNICO','Informe técnico',90007,NULL,NULL,NULL,'Informe de estado general del inmueble','2026-04-07','2026-04-14','Amaya','usuario@ciete.es','Responsable demo patrimonial','Otros','terminado','OTR-DEMO-2026-0007','2026-04-07',540.00,'recibido','OTR-PAT-01',NULL,'Informe de estado general',540.00,1,540.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'[demo-controlado-otros] Pedido demo sin factura.'
    UNION ALL SELECT
        'OTROS','docs/02_CLIENTE/tareasComparar.md','demo-controlado','Cliente Demo Instalaciones','B90000008','OTR-INS-01','Instalación Demo 01','Zaragoza','Zaragoza','OTROS-DEMO-2026','Contrato demo otros clientes 2026','Cliente Demo Instalaciones','B90000008','Tarifario otros demo 2026','demo','OTR-INS-01','Instalaciones','Legalización eléctrica','OTROS','Trabajo otros clientes','LEGALIZACIONES','Legalizaciones',90008,NULL,NULL,NULL,'Legalización eléctrica de instalación','2026-04-08','2026-04-20','César','cesar@ciete.es','Responsable demo instalaciones','Otros','facturado','OTR-DEMO-2026-0008','2026-04-08',980.00,'facturado','OTR-INS-01',NULL,'Legalización eléctrica',980.00,1,980.00,'OTR-F-2026-008',NULL,'OTR','2026-04-22','2026-04-22','emitida',980.00,'[demo-controlado-otros] Pedido y factura emitida demo.'
    UNION ALL SELECT
        'OTROS','docs/02_CLIENTE/tareasComparar.md','demo-controlado','Cliente Demo Consultoría Técnica','B90000009','OTR-CON-01','Oficina Técnica Demo','Málaga','Málaga','OTROS-DEMO-2026','Contrato demo otros clientes 2026',NULL,NULL,'Tarifario otros demo 2026','demo','OTR-CONS-01','Consultoría','Asistencia técnica puntual','OTROS','Trabajo otros clientes','CONSULTORIA','Consultoría',90009,NULL,NULL,NULL,'Asistencia técnica puntual','2026-04-09',NULL,'Almudena','usuario@ciete.es','Responsable demo consultoría','Otros','en_curso',NULL,NULL,NULL,NULL,'OTR-CONS-01',NULL,'Asistencia técnica puntual',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'[demo-controlado-otros] En curso sin pedido.'
    UNION ALL SELECT
        'OTROS','docs/02_CLIENTE/tareasComparar.md','demo-controlado','Cliente Demo Infraestructura','B90000010','OTR-INF-01','Infraestructura Demo 01','A Coruña','A Coruña','OTROS-DEMO-2026','Contrato demo otros clientes 2026','Cliente Demo Infraestructura','B90000010','Tarifario otros demo 2026','demo','OTR-INF-01','Infraestructura','Dirección facultativa','OTROS','Trabajo otros clientes','DIRECCION_FACULTATIVA','Dirección facultativa',90010,NULL,NULL,NULL,'Dirección facultativa de actuación menor','2026-04-10','2026-04-30','José Vergara','usuario@ciete.es','Responsable demo infraestructura','Otros','finalizado','OTR-DEMO-2026-0010','2026-04-10',1650.00,'facturado','OTR-INF-01',NULL,'Dirección facultativa',1650.00,1,1650.00,'OTR-F-2026-010',NULL,'OTR','2026-05-02','2026-05-02','enviada',1650.00,'[demo-controlado-otros] Pedido y factura enviada demo.'
) AS seed_rows;

-- ==========================================================
-- 2. Maestros mínimos
-- ==========================================================

INSERT INTO empresas (
    id_contexto, nombre, nombre_comercial, razon_social, cif, tipo_empresa, observaciones, activo, created_at, updated_at
)
SELECT DISTINCT
    c.id_contexto,
    m.cliente_nombre,
    m.cliente_nombre,
    m.cliente_nombre,
    m.cliente_cif,
    'cliente',
    CONCAT(@marker, ' Empresa cliente usada por la muestra.'),
    1,
    @now,
    @now
FROM tmp_muestra_operativa_50 m
JOIN contextos_cliente c ON c.codigo = m.contexto_codigo
ON DUPLICATE KEY UPDATE
    nombre_comercial = VALUES(nombre_comercial),
    razon_social = VALUES(razon_social),
    cif = COALESCE(empresas.cif, VALUES(cif)),
    tipo_empresa = VALUES(tipo_empresa),
    observaciones = IF(COALESCE(empresas.observaciones, '') COLLATE utf8mb4_unicode_ci LIKE @marker_like COLLATE utf8mb4_unicode_ci, empresas.observaciones, CONCAT(TRIM(COALESCE(empresas.observaciones, '')), ' ', VALUES(observaciones))),
    activo = 1,
    updated_at = @now;

INSERT INTO empresas (
    id_contexto, nombre, nombre_comercial, razon_social, cif, tipo_empresa, observaciones, activo, created_at, updated_at
)
SELECT DISTINCT
    c.id_contexto,
    m.sociedad_facturadora,
    m.sociedad_facturadora,
    m.sociedad_facturadora,
    m.sociedad_cif,
    'otra',
    CONCAT(@marker, ' Sociedad facturadora usada por la muestra; CIF solo si consta en la fuente.'),
    1,
    @now,
    @now
FROM tmp_muestra_operativa_50 m
JOIN contextos_cliente c ON c.codigo = m.contexto_codigo
WHERE NULLIF(TRIM(m.sociedad_facturadora), '') IS NOT NULL
ON DUPLICATE KEY UPDATE
    nombre_comercial = VALUES(nombre_comercial),
    razon_social = VALUES(razon_social),
    cif = COALESCE(empresas.cif, VALUES(cif)),
    observaciones = IF(COALESCE(empresas.observaciones, '') COLLATE utf8mb4_unicode_ci LIKE @marker_like COLLATE utf8mb4_unicode_ci, empresas.observaciones, CONCAT(TRIM(COALESCE(empresas.observaciones, '')), ' ', VALUES(observaciones))),
    activo = 1,
    updated_at = @now;

INSERT INTO estaciones_servicio (
    id_contexto, id_empresa_cliente, codigo_estacion, nombre, poblacion, provincia, pais, estado, observaciones, activo, created_at, updated_at
)
SELECT
    c.id_contexto,
    e.id_empresa,
    m.estacion_codigo,
    MIN(m.estacion_nombre),
    MIN(m.estacion_poblacion),
    MIN(m.estacion_provincia),
    'Espana',
    'Activa',
    CONCAT(@marker, ' Estación usada por la muestra.'),
    1,
    @now,
    @now
FROM tmp_muestra_operativa_50 m
JOIN contextos_cliente c ON c.codigo = m.contexto_codigo
JOIN empresas e ON e.id_contexto = c.id_contexto AND e.nombre = m.cliente_nombre
GROUP BY c.id_contexto, e.id_empresa, m.estacion_codigo
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    poblacion = VALUES(poblacion),
    provincia = VALUES(provincia),
    estado = VALUES(estado),
    observaciones = IF(COALESCE(estaciones_servicio.observaciones, '') COLLATE utf8mb4_unicode_ci LIKE @marker_like COLLATE utf8mb4_unicode_ci, estaciones_servicio.observaciones, CONCAT(TRIM(COALESCE(estaciones_servicio.observaciones, '')), ' ', VALUES(observaciones))),
    activo = 1,
    updated_at = @now;

INSERT INTO tipos_documento (
    id_contexto, codigo, nombre, tiene_doble_factura, tiene_orden_mto, tiene_num_tarifa, activo, created_at, updated_at
)
SELECT DISTINCT
    c.id_contexto,
    m.tipo_documento_codigo,
    m.tipo_documento_nombre,
    CASE WHEN m.tipo_documento_codigo IN ('EDIFICACION', 'OBRAS', 'ESTRUCTURAS', 'MTO') THEN 1 ELSE 0 END,
    CASE WHEN m.tipo_documento_codigo = 'MTO' THEN 1 ELSE 0 END,
    CASE WHEN m.contexto_codigo = 'REPSOL' THEN 1 ELSE 0 END,
    1,
    @now,
    @now
FROM tmp_muestra_operativa_50 m
JOIN contextos_cliente c ON c.codigo = m.contexto_codigo
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    tiene_doble_factura = VALUES(tiene_doble_factura),
    tiene_orden_mto = VALUES(tiene_orden_mto),
    tiene_num_tarifa = VALUES(tiene_num_tarifa),
    activo = 1,
    updated_at = @now;

INSERT INTO tipos_trabajo (
    id_contexto, id_tipo_documento, codigo, nombre, responsable_ciete_defecto, responsable_cliente_defecto, activo, created_at, updated_at
)
SELECT DISTINCT
    c.id_contexto,
    td.id_tipo_documento,
    m.tipo_trabajo_codigo,
    m.tipo_trabajo_nombre,
    m.responsable_ciete_nombre,
    m.responsable_cliente,
    1,
    @now,
    @now
FROM tmp_muestra_operativa_50 m
JOIN contextos_cliente c ON c.codigo = m.contexto_codigo
JOIN tipos_documento td ON td.id_contexto = c.id_contexto AND td.codigo = m.tipo_documento_codigo
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    responsable_ciete_defecto = COALESCE(VALUES(responsable_ciete_defecto), tipos_trabajo.responsable_ciete_defecto),
    responsable_cliente_defecto = COALESCE(VALUES(responsable_cliente_defecto), tipos_trabajo.responsable_cliente_defecto),
    activo = 1,
    updated_at = @now;

INSERT INTO contratos (
    id_contexto, id_empresa_cliente, codigo_contrato, nombre, tipo, fecha_inicio, estado, observaciones, activo, created_at, updated_at
)
SELECT DISTINCT
    c.id_contexto,
    e.id_empresa,
    m.contrato_codigo,
    m.contrato_nombre,
    'marco',
    CASE
        WHEN m.contexto_codigo = 'MOEVE' AND m.contrato_codigo = 'MOEVE-686' THEN '2015-01-01'
        WHEN m.contexto_codigo = 'MOEVE' THEN '2024-01-01'
        WHEN m.contexto_codigo = 'REPSOL' THEN '2023-01-01'
        ELSE '2026-01-01'
    END,
    'vigente',
    CONCAT(@marker, ' Contrato preparado para la muestra.'),
    1,
    @now,
    @now
FROM tmp_muestra_operativa_50 m
JOIN contextos_cliente c ON c.codigo = m.contexto_codigo
JOIN empresas e ON e.id_contexto = c.id_contexto AND e.nombre = m.cliente_nombre
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    tipo = VALUES(tipo),
    estado = VALUES(estado),
    observaciones = IF(COALESCE(contratos.observaciones, '') COLLATE utf8mb4_unicode_ci LIKE @marker_like COLLATE utf8mb4_unicode_ci, contratos.observaciones, CONCAT(TRIM(COALESCE(contratos.observaciones, '')), ' ', VALUES(observaciones))),
    activo = 1,
    updated_at = @now;

INSERT INTO tarifarios (
    id_contexto, id_contrato, nombre, version, fecha_inicio_vigencia, factor_multiplicador, moneda, observaciones, activo, created_at, updated_at
)
SELECT DISTINCT
    c.id_contexto,
    ct.id_contrato,
    m.tarifario_nombre,
    m.tarifario_version,
    CASE WHEN m.contexto_codigo = 'OTROS' THEN '2026-01-01' ELSE '2023-01-01' END,
    1.0000,
    'EUR',
    CONCAT(@marker, ' Tarifario usado por la muestra.'),
    1,
    @now,
    @now
FROM tmp_muestra_operativa_50 m
JOIN contextos_cliente c ON c.codigo = m.contexto_codigo
JOIN contratos ct ON ct.id_contexto = c.id_contexto AND ct.codigo_contrato = m.contrato_codigo
ON DUPLICATE KEY UPDATE
    id_contrato = VALUES(id_contrato),
    factor_multiplicador = VALUES(factor_multiplicador),
    moneda = VALUES(moneda),
    observaciones = IF(COALESCE(tarifarios.observaciones, '') COLLATE utf8mb4_unicode_ci LIKE @marker_like COLLATE utf8mb4_unicode_ci, tarifarios.observaciones, CONCAT(TRIM(COALESCE(tarifarios.observaciones, '')), ' ', VALUES(observaciones))),
    activo = 1,
    updated_at = @now;

INSERT INTO tarifario_lineas (
    id_contexto, id_tarifario, codigo_tarifa, grupo, actuacion, descripcion, tarifa_anterior, tarifa_base, tarifa_aplicada, id_unidad, activo, created_at, updated_at
)
SELECT
    c.id_contexto,
    tf.id_tarifario,
    m.tarifa_codigo,
    MIN(m.tarifa_grupo),
    MIN(m.tarifa_actuacion),
    MIN(m.descripcion_servicio),
    NULL,
    COALESCE(MAX(m.precio_unitario), MAX(m.total_linea), 0.00),
    COALESCE(MAX(m.precio_unitario), MAX(m.total_linea), 0.00),
    MAX(u.id_unidad),
    1,
    @now,
    @now
FROM tmp_muestra_operativa_50 m
JOIN contextos_cliente c ON c.codigo = m.contexto_codigo
JOIN tarifarios tf ON tf.id_contexto = c.id_contexto
    AND tf.nombre = m.tarifario_nombre
    AND ((tf.version <=> m.tarifario_version) OR (tf.version IS NULL AND m.tarifario_version IS NULL))
LEFT JOIN unidades u ON u.abreviatura = 'ud'
GROUP BY c.id_contexto, tf.id_tarifario, m.tarifa_codigo
ON DUPLICATE KEY UPDATE
    grupo = VALUES(grupo),
    actuacion = VALUES(actuacion),
    descripcion = VALUES(descripcion),
    tarifa_base = VALUES(tarifa_base),
    tarifa_aplicada = VALUES(tarifa_aplicada),
    id_unidad = VALUES(id_unidad),
    activo = 1,
    updated_at = @now;

INSERT INTO contrato_empresas_facturadoras (
    id_contrato, id_empresa, id_contexto, activo, observaciones, created_at, updated_at
)
SELECT DISTINCT
    ct.id_contrato,
    ef.id_empresa,
    c.id_contexto,
    1,
    CONCAT(@marker, ' Relación permitida para la muestra; CIF solo si existe en la fuente.'),
    @now,
    @now
FROM tmp_muestra_operativa_50 m
JOIN contextos_cliente c ON c.codigo = m.contexto_codigo
JOIN contratos ct ON ct.id_contexto = c.id_contexto AND ct.codigo_contrato = m.contrato_codigo
JOIN empresas ef ON ef.id_contexto = c.id_contexto AND ef.nombre = m.sociedad_facturadora
WHERE NULLIF(TRIM(m.sociedad_facturadora), '') IS NOT NULL
ON DUPLICATE KEY UPDATE
    activo = 1,
    observaciones = IF(COALESCE(contrato_empresas_facturadoras.observaciones, '') COLLATE utf8mb4_unicode_ci LIKE @marker_like COLLATE utf8mb4_unicode_ci, contrato_empresas_facturadoras.observaciones, CONCAT(TRIM(COALESCE(contrato_empresas_facturadoras.observaciones, '')), ' ', VALUES(observaciones))),
    updated_at = @now;

-- ==========================================================
-- 3. Trabajos
-- ==========================================================

INSERT INTO trabajos (
    id_contexto, id_empresa_cliente, id_estacion_servicio, id_tipo_documento, id_tipo_trabajo,
    id_contrato, id_tarifario, id_responsable_ciete, numero_trabajo, numero_trabajo_operativo,
    numero_estacion, zona, descripcion_trabajo, fecha_encargo, fecha_terminacion, observaciones,
    numero_aviso, orden_mantenimiento, categoria, responsable_cliente, estado, bloqueado_cierre,
    created_at, updated_at
)
SELECT
    c.id_contexto,
    e.id_empresa,
    es.id_estacion_servicio,
    td.id_tipo_documento,
    tt.id_tipo_trabajo,
    ct.id_contrato,
    tf.id_tarifario,
    u.id_usuario,
    m.numero_trabajo,
    m.numero_operativo,
    m.estacion_codigo,
    NULL,
    m.descripcion_trabajo,
    m.fecha_encargo,
    m.fecha_terminacion,
    CONCAT(
        @marker,
        ' fuente=', m.fuente_documental,
        '; hoja=', COALESCE(m.hoja_fuente, '-'),
        '; tarifa=', m.tarifa_codigo,
        '; observacion_fuente=', COALESCE(m.observaciones, '')
    ),
    m.numero_aviso,
    m.orden_mantenimiento,
    m.categoria,
    m.responsable_cliente,
    m.estado_trabajo,
    0,
    @now,
    @now
FROM tmp_muestra_operativa_50 m
JOIN contextos_cliente c ON c.codigo = m.contexto_codigo
JOIN empresas e ON e.id_contexto = c.id_contexto AND e.nombre = m.cliente_nombre
JOIN estaciones_servicio es ON es.id_contexto = c.id_contexto AND es.codigo_estacion = m.estacion_codigo
JOIN tipos_documento td ON td.id_contexto = c.id_contexto AND td.codigo = m.tipo_documento_codigo
JOIN tipos_trabajo tt ON tt.id_contexto = c.id_contexto AND tt.id_tipo_documento = td.id_tipo_documento AND tt.codigo = m.tipo_trabajo_codigo
JOIN contratos ct ON ct.id_contexto = c.id_contexto AND ct.codigo_contrato = m.contrato_codigo
JOIN tarifarios tf ON tf.id_contexto = c.id_contexto
    AND tf.nombre = m.tarifario_nombre
    AND ((tf.version <=> m.tarifario_version) OR (tf.version IS NULL AND m.tarifario_version IS NULL))
LEFT JOIN usuarios u ON u.email = m.responsable_ciete_email
WHERE NOT EXISTS (
    SELECT 1
    FROM trabajos tx
    WHERE tx.id_contexto = c.id_contexto
      AND tx.id_tipo_documento = td.id_tipo_documento
      AND tx.numero_trabajo = m.numero_trabajo
);

DROP TEMPORARY TABLE IF EXISTS tmp_muestra_trabajos_map;
CREATE TEMPORARY TABLE tmp_muestra_trabajos_map AS
SELECT
    m.id_tmp,
    c.id_contexto,
    td.id_tipo_documento,
    t.id_trabajo,
    t.id_contrato,
    t.id_tarifario
FROM tmp_muestra_operativa_50 m
JOIN contextos_cliente c ON c.codigo = m.contexto_codigo
JOIN tipos_documento td ON td.id_contexto = c.id_contexto AND td.codigo = m.tipo_documento_codigo
JOIN trabajos t ON t.id_contexto = c.id_contexto AND t.id_tipo_documento = td.id_tipo_documento AND t.numero_trabajo = m.numero_trabajo;

-- ==========================================================
-- 4. Pedidos
-- ==========================================================

INSERT INTO pedidos (
    id_contexto, id_trabajo, id_tarifario, numero_pedido, fecha_solicitud, fecha_recepcion,
    importe_pedido, importe_solicitado, importe_facturado, unidades_pedido, unidades_solicitadas,
    estado, pedido_completo, tiene_mas_de_1_item, facturado_completo, observaciones, created_at, updated_at
)
SELECT
    tm.id_contexto,
    tm.id_trabajo,
    tm.id_tarifario,
    m.numero_pedido,
    m.fecha_solicitud_pedido,
    m.fecha_encargo,
    COALESCE(m.importe_pedido, 0.00),
    CASE WHEN NULLIF(TRIM(m.numero_factura), '') IS NULL THEN NULL ELSE COALESCE(m.factura_importe, m.total_linea, m.importe_pedido, 0.00) END,
    CASE WHEN NULLIF(TRIM(m.numero_factura), '') IS NULL THEN NULL ELSE COALESCE(m.factura_importe, m.total_linea, m.importe_pedido, 0.00) END,
    COALESCE(m.cantidad, 1.000),
    CASE WHEN NULLIF(TRIM(m.numero_factura), '') IS NULL THEN NULL ELSE COALESCE(m.cantidad, 1.000) END,
    COALESCE(m.pedido_estado, CASE WHEN NULLIF(TRIM(m.numero_factura), '') IS NULL THEN 'recibido' ELSE 'facturado' END),
    CASE WHEN m.total_linea IS NULL THEN NULL ELSE 1 END,
    0,
    CASE WHEN NULLIF(TRIM(m.numero_factura), '') IS NULL THEN 0 ELSE 1 END,
    CONCAT(@marker, ' Pedido creado desde staging controlado.'),
    @now,
    @now
FROM tmp_muestra_operativa_50 m
JOIN tmp_muestra_trabajos_map tm ON tm.id_tmp = m.id_tmp
WHERE NULLIF(TRIM(m.numero_pedido), '') IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM pedidos p
      WHERE p.id_contexto = tm.id_contexto
        AND p.id_trabajo = tm.id_trabajo
        AND p.numero_pedido = m.numero_pedido
  );

DROP TEMPORARY TABLE IF EXISTS tmp_muestra_pedidos_map;
CREATE TEMPORARY TABLE tmp_muestra_pedidos_map AS
SELECT
    m.id_tmp,
    p.id_pedido,
    tm.id_contexto,
    tm.id_trabajo,
    tm.id_contrato,
    tm.id_tarifario
FROM tmp_muestra_operativa_50 m
JOIN tmp_muestra_trabajos_map tm ON tm.id_tmp = m.id_tmp
JOIN pedidos p ON p.id_contexto = tm.id_contexto AND p.id_trabajo = tm.id_trabajo AND p.numero_pedido = m.numero_pedido
WHERE NULLIF(TRIM(m.numero_pedido), '') IS NOT NULL;

-- ==========================================================
-- 5. Pedido items
-- ==========================================================

INSERT INTO pedido_items (
    id_contexto, id_pedido, id_tarifario_linea, codigo_servicio, numero_tarifa,
    descripcion_servicio, precio_unitario, cantidad, total_linea, created_at, updated_at
)
SELECT
    pm.id_contexto,
    pm.id_pedido,
    tl.id_tarifario_linea,
    m.codigo_servicio,
    m.numero_tarifa,
    m.descripcion_servicio,
    COALESCE(m.precio_unitario, m.total_linea, m.importe_pedido, 0.00),
    COALESCE(m.cantidad, 1.000),
    COALESCE(m.total_linea, m.importe_pedido, 0.00),
    @now,
    @now
FROM tmp_muestra_operativa_50 m
JOIN tmp_muestra_pedidos_map pm ON pm.id_tmp = m.id_tmp
LEFT JOIN tarifario_lineas tl ON tl.id_contexto = pm.id_contexto AND tl.id_tarifario = pm.id_tarifario AND tl.codigo_tarifa = m.tarifa_codigo
WHERE NOT EXISTS (
    SELECT 1
    FROM pedido_items pi
    WHERE pi.id_contexto = pm.id_contexto
      AND pi.id_pedido = pm.id_pedido
      AND COALESCE(pi.codigo_servicio, '') = COALESCE(m.codigo_servicio, '')
      AND COALESCE(pi.numero_tarifa, '') = COALESCE(m.numero_tarifa, '')
      AND COALESCE(pi.descripcion_servicio, '') = COALESCE(m.descripcion_servicio, '')
      AND pi.total_linea = COALESCE(m.total_linea, m.importe_pedido, 0.00)
);

DROP TEMPORARY TABLE IF EXISTS tmp_muestra_pedido_items_map;
CREATE TEMPORARY TABLE tmp_muestra_pedido_items_map AS
SELECT
    m.id_tmp,
    pm.id_contexto,
    pm.id_trabajo,
    pm.id_contrato,
    pm.id_tarifario,
    pm.id_pedido,
    pi.id_pedido_item
FROM tmp_muestra_operativa_50 m
JOIN tmp_muestra_pedidos_map pm ON pm.id_tmp = m.id_tmp
JOIN pedido_items pi ON pi.id_contexto = pm.id_contexto
    AND pi.id_pedido = pm.id_pedido
    AND COALESCE(pi.codigo_servicio, '') = COALESCE(m.codigo_servicio, '')
    AND COALESCE(pi.numero_tarifa, '') = COALESCE(m.numero_tarifa, '')
    AND COALESCE(pi.descripcion_servicio, '') = COALESCE(m.descripcion_servicio, '')
    AND pi.total_linea = COALESCE(m.total_linea, m.importe_pedido, 0.00);

-- ==========================================================
-- 6. Facturas elegibles
-- ==========================================================

DROP TEMPORARY TABLE IF EXISTS tmp_muestra_facturas_validas;
CREATE TEMPORARY TABLE tmp_muestra_facturas_validas AS
SELECT
    m.id_tmp,
    pim.id_contexto,
    pim.id_trabajo,
    pim.id_contrato,
    ecli.id_empresa AS id_empresa_cliente,
    ef.id_empresa AS id_empresa_facturadora,
    pim.id_pedido_item,
    m.numero_factura,
    m.numero_factura_ccp,
    m.serie_factura,
    m.fecha_solicitud_factura,
    m.fecha_emision_factura,
    CASE
        WHEN m.factura_estado IN ('pendiente', 'solicitada', 'emitida', 'enviada', 'anulada') THEN m.factura_estado
        ELSE 'emitida'
    END AS factura_estado,
    COALESCE(m.factura_importe, m.total_linea, m.importe_pedido, 0.00) AS importe_facturado,
    COALESCE(m.cantidad, 1.000) AS unidades_facturadas,
    m.sociedad_facturadora
FROM tmp_muestra_operativa_50 m
JOIN tmp_muestra_pedido_items_map pim ON pim.id_tmp = m.id_tmp
JOIN contextos_cliente c ON c.id_contexto = pim.id_contexto
JOIN empresas ecli ON ecli.id_contexto = c.id_contexto AND ecli.nombre = m.cliente_nombre
JOIN empresas ef ON ef.id_contexto = c.id_contexto AND ef.nombre = m.sociedad_facturadora
JOIN contrato_empresas_facturadoras cef ON cef.id_contexto = c.id_contexto
    AND cef.id_contrato = pim.id_contrato
    AND cef.id_empresa = ef.id_empresa
    AND cef.activo = 1
WHERE NULLIF(TRIM(m.numero_factura), '') IS NOT NULL;

-- ==========================================================
-- 7. Facturas
-- ==========================================================

INSERT INTO facturas (
    id_contexto, id_trabajo, id_contrato, id_empresa_cliente, id_empresa_facturadora,
    numero_factura, numero_factura_ccp, serie, orden_factura, fecha_solicitud, fecha_emision,
    importe, base_imponible, iva, retencion, total, estado, autofactura, sociedad,
    observaciones, created_at, updated_at
)
SELECT
    tv.id_contexto,
    MIN(tv.id_trabajo),
    MIN(tv.id_contrato),
    MIN(tv.id_empresa_cliente),
    tv.id_empresa_facturadora,
    tv.numero_factura,
    MAX(tv.numero_factura_ccp),
    MAX(tv.serie_factura),
    1,
    MIN(tv.fecha_solicitud_factura),
    MIN(tv.fecha_emision_factura),
    ROUND(SUM(tv.importe_facturado), 2),
    ROUND(SUM(tv.importe_facturado), 2),
    NULL,
    NULL,
    ROUND(SUM(tv.importe_facturado), 2),
    COALESCE(MAX(tv.factura_estado), 'emitida'),
    0,
    MAX(tv.sociedad_facturadora),
    CONCAT(@marker, ' Cabecera auxiliar derivada de factura_items.'),
    @now,
    @now
FROM tmp_muestra_facturas_validas tv
GROUP BY tv.id_contexto, tv.id_empresa_facturadora, tv.numero_factura
HAVING COUNT(DISTINCT tv.id_contrato) = 1
   AND NOT EXISTS (
       SELECT 1
       FROM facturas f
       WHERE f.id_contexto = tv.id_contexto
         AND f.id_empresa_facturadora = tv.id_empresa_facturadora
         AND f.numero_factura = tv.numero_factura
   );

-- ==========================================================
-- 8. Factura items
-- ==========================================================

INSERT INTO factura_items (
    id_factura, id_pedido_item, unidades_facturadas, importe_facturado, observaciones, created_at, updated_at
)
SELECT
    f.id_factura,
    tv.id_pedido_item,
    tv.unidades_facturadas,
    tv.importe_facturado,
    CONCAT(@marker, ' Relación real factura_item -> pedido_item.'),
    @now,
    @now
FROM tmp_muestra_facturas_validas tv
JOIN facturas f ON f.id_contexto = tv.id_contexto
    AND f.id_empresa_facturadora = tv.id_empresa_facturadora
    AND f.numero_factura = tv.numero_factura
WHERE NOT EXISTS (
    SELECT 1
    FROM factura_items fi
    WHERE fi.id_factura = f.id_factura
      AND fi.id_pedido_item = tv.id_pedido_item
);

-- ==========================================================
-- 9. Comprobaciones finales
-- ==========================================================

SELECT 'total_trabajos_por_contexto' AS check_name, c.codigo AS contexto, COUNT(*) AS total
FROM trabajos t
JOIN contextos_cliente c ON c.id_contexto = t.id_contexto
WHERE COALESCE(t.observaciones, '') COLLATE utf8mb4_unicode_ci LIKE @marker_like COLLATE utf8mb4_unicode_ci
GROUP BY c.codigo
ORDER BY c.codigo;

SELECT 'total_cancelados_por_contexto' AS check_name, c.codigo AS contexto, COUNT(*) AS total_cancelados
FROM trabajos t
JOIN contextos_cliente c ON c.id_contexto = t.id_contexto
WHERE COALESCE(t.observaciones, '') COLLATE utf8mb4_unicode_ci LIKE @marker_like COLLATE utf8mb4_unicode_ci
  AND t.estado = 'cancelado'
GROUP BY c.codigo
ORDER BY c.codigo;

SELECT 'trabajos_sin_pedido' AS check_name, c.codigo AS contexto, t.numero_trabajo, t.numero_estacion, t.descripcion_trabajo, t.estado
FROM trabajos t
JOIN contextos_cliente c ON c.id_contexto = t.id_contexto
LEFT JOIN pedidos p ON p.id_contexto = t.id_contexto AND p.id_trabajo = t.id_trabajo
WHERE COALESCE(t.observaciones, '') COLLATE utf8mb4_unicode_ci LIKE @marker_like COLLATE utf8mb4_unicode_ci
  AND p.id_pedido IS NULL
ORDER BY c.codigo, t.numero_trabajo;

SELECT 'pedidos_sin_items' AS check_name, c.codigo AS contexto, p.numero_pedido, t.numero_trabajo
FROM pedidos p
JOIN trabajos t ON t.id_contexto = p.id_contexto AND t.id_trabajo = p.id_trabajo
JOIN contextos_cliente c ON c.id_contexto = p.id_contexto
LEFT JOIN pedido_items pi ON pi.id_contexto = p.id_contexto AND pi.id_pedido = p.id_pedido
WHERE COALESCE(t.observaciones, '') COLLATE utf8mb4_unicode_ci LIKE @marker_like COLLATE utf8mb4_unicode_ci
  AND pi.id_pedido_item IS NULL
ORDER BY c.codigo, p.numero_pedido;

SELECT 'facturas_sin_factura_items' AS check_name, c.codigo AS contexto, f.id_factura, f.numero_factura
FROM facturas f
JOIN contextos_cliente c ON c.id_contexto = f.id_contexto
LEFT JOIN factura_items fi ON fi.id_factura = f.id_factura
WHERE COALESCE(f.observaciones, '') COLLATE utf8mb4_unicode_ci LIKE @marker_like COLLATE utf8mb4_unicode_ci
  AND fi.id_factura_item IS NULL
ORDER BY c.codigo, f.numero_factura;

SELECT 'factura_items_sin_pedido_item' AS check_name, c.codigo AS contexto, fi.id_factura_item, f.numero_factura
FROM factura_items fi
JOIN facturas f ON f.id_factura = fi.id_factura
JOIN contextos_cliente c ON c.id_contexto = f.id_contexto
LEFT JOIN pedido_items pi ON pi.id_pedido_item = fi.id_pedido_item
WHERE COALESCE(fi.observaciones, '') COLLATE utf8mb4_unicode_ci LIKE @marker_like COLLATE utf8mb4_unicode_ci
  AND pi.id_pedido_item IS NULL
ORDER BY c.codigo, fi.id_factura_item;

SELECT 'facturas_con_estados_no_permitidos' AS check_name, c.codigo AS contexto, f.id_factura, f.numero_factura, f.estado
FROM facturas f
JOIN contextos_cliente c ON c.id_contexto = f.id_contexto
WHERE COALESCE(f.observaciones, '') COLLATE utf8mb4_unicode_ci LIKE @marker_like COLLATE utf8mb4_unicode_ci
  AND f.estado NOT IN ('pendiente', 'solicitada', 'emitida', 'enviada', 'anulada')
ORDER BY c.codigo, f.id_factura;

SELECT 'facturas_emitidas_enviadas_sin_numero' AS check_name, c.codigo AS contexto, f.id_factura, f.estado
FROM facturas f
JOIN contextos_cliente c ON c.id_contexto = f.id_contexto
WHERE COALESCE(f.observaciones, '') COLLATE utf8mb4_unicode_ci LIKE @marker_like COLLATE utf8mb4_unicode_ci
  AND f.estado IN ('emitida', 'enviada')
  AND NULLIF(TRIM(COALESCE(f.numero_factura, '')), '') IS NULL
ORDER BY c.codigo, f.id_factura;

SELECT 'sociedades_facturadoras_no_permitidas_por_contrato' AS check_name, c.codigo AS contexto, f.numero_factura, f.id_empresa_facturadora, ct.codigo_contrato
FROM facturas f
JOIN factura_items fi ON fi.id_factura = f.id_factura
JOIN pedido_items pi ON pi.id_pedido_item = fi.id_pedido_item
JOIN pedidos p ON p.id_pedido = pi.id_pedido
JOIN trabajos t ON t.id_trabajo = p.id_trabajo
JOIN contratos ct ON ct.id_contrato = t.id_contrato
JOIN contextos_cliente c ON c.id_contexto = f.id_contexto
LEFT JOIN contrato_empresas_facturadoras cef ON cef.id_contexto = f.id_contexto
    AND cef.id_contrato = t.id_contrato
    AND cef.id_empresa = f.id_empresa_facturadora
    AND cef.activo = 1
WHERE COALESCE(f.observaciones, '') COLLATE utf8mb4_unicode_ci LIKE @marker_like COLLATE utf8mb4_unicode_ci
  AND cef.id IS NULL
GROUP BY c.codigo, f.numero_factura, f.id_empresa_facturadora, ct.codigo_contrato
ORDER BY c.codigo, f.numero_factura;

SELECT 'duplicados_factura_por_contexto_facturadora_numero' AS check_name, c.codigo AS contexto, f.id_empresa_facturadora, f.numero_factura, COUNT(*) AS total
FROM facturas f
JOIN contextos_cliente c ON c.id_contexto = f.id_contexto
WHERE COALESCE(f.observaciones, '') COLLATE utf8mb4_unicode_ci LIKE @marker_like COLLATE utf8mb4_unicode_ci
  AND NULLIF(TRIM(COALESCE(f.numero_factura, '')), '') IS NOT NULL
GROUP BY c.codigo, f.id_empresa_facturadora, f.numero_factura
HAVING COUNT(*) > 1
ORDER BY c.codigo, f.id_empresa_facturadora, f.numero_factura;

SELECT 'trabajos_con_estado_fuera_catalogo' AS check_name, c.codigo AS contexto, t.numero_trabajo, t.estado
FROM trabajos t
JOIN contextos_cliente c ON c.id_contexto = t.id_contexto
WHERE COALESCE(t.observaciones, '') COLLATE utf8mb4_unicode_ci LIKE @marker_like COLLATE utf8mb4_unicode_ci
  AND t.estado NOT IN ('en_curso', 'terminado', 'pendiente_facturar', 'facturado', 'finalizado', 'cancelado')
ORDER BY c.codigo, t.numero_trabajo;

SELECT 'facturas_descartadas_por_falta_de_sociedad_coherente' AS check_name, m.contexto_codigo AS contexto, m.numero_trabajo, m.numero_pedido, m.numero_factura, m.sociedad_facturadora
FROM tmp_muestra_operativa_50 m
LEFT JOIN tmp_muestra_facturas_validas tv ON tv.id_tmp = m.id_tmp
WHERE NULLIF(TRIM(COALESCE(m.numero_factura, '')), '') IS NOT NULL
  AND tv.id_tmp IS NULL
ORDER BY m.contexto_codigo, m.numero_trabajo;
