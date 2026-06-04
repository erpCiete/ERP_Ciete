-- ============================================================
-- ERP CIETE - Muestra local controlada de flujo diario
-- Fecha: 2026-05-18
-- Objetivo: cubrir flujo Trabajo -> Pedido -> Items -> Factura -> Cierre
-- Alcance: muestra pequena y determinista (sin importacion masiva)
-- Reglas: no truncar, no borrar masivo, no estados legacy
-- ============================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

SET @now := NOW();
SET @marker := '[muestra-flujo-diario-2026-05-18]';

-- ============================================================
-- 1) Empresas base por contexto
-- ============================================================
INSERT INTO empresas (
    id_contexto,
    nombre,
    nombre_comercial,
    razon_social,
    cif,
    tipo_empresa,
    observaciones,
    activo,
    created_at,
    updated_at
)
SELECT
    c.id_contexto,
    seed.nombre,
    seed.nombre_comercial,
    seed.razon_social,
    seed.cif,
    seed.tipo_empresa,
    CONCAT(@marker, ' ', seed.observaciones),
    1,
    @now,
    @now
FROM (
    SELECT 'MOEVE' AS contexto, 'FD26 MOEVE CLIENTE' AS nombre, 'FD26 MOEVE CLIENTE' AS nombre_comercial, 'FD26 MOEVE CLIENTE SL' AS razon_social, 'A28003119' AS cif, 'cliente' AS tipo_empresa, 'Cliente operativo MOEVE' AS observaciones
    UNION ALL SELECT 'MOEVE', 'FD26 MOEVE FACTURADORA', 'FD26 MOEVE FACTURADORA', 'FD26 MOEVE FACTURADORA SL', 'B90000011', 'cliente', 'Sociedad facturadora valida MOEVE'
    UNION ALL SELECT 'MOEVE', 'FD26 MOEVE SIN CIF', 'FD26 MOEVE SIN CIF', 'FD26 MOEVE SIN CIF SL', NULL, 'cliente', 'Sociedad sin CIF para prueba controlada'
    UNION ALL SELECT 'REPSOL', 'FD26 REPSOL CLIENTE', 'FD26 REPSOL CLIENTE', 'FD26 REPSOL CLIENTE SA', 'A78374725', 'cliente', 'Cliente operativo REPSOL'
    UNION ALL SELECT 'REPSOL', 'FD26 REPSOL FACTURADORA', 'FD26 REPSOL FACTURADORA', 'FD26 REPSOL FACTURADORA SA', 'B90000022', 'cliente', 'Sociedad facturadora valida REPSOL'
    UNION ALL SELECT 'REPSOL', 'FD26 REPSOL SIN CIF', 'FD26 REPSOL SIN CIF', 'FD26 REPSOL SIN CIF SA', NULL, 'cliente', 'Sociedad sin CIF para prueba REPSOL'
    UNION ALL SELECT 'OTROS', 'FD26 OTROS CLIENTE', 'FD26 OTROS CLIENTE', 'FD26 OTROS CLIENTE SL', 'B90000033', 'cliente', 'Cliente operativo OTROS'
    UNION ALL SELECT 'OTROS', 'FD26 OTROS FACTURADORA', 'FD26 OTROS FACTURADORA', 'FD26 OTROS FACTURADORA SL', 'B90000044', 'cliente', 'Sociedad facturadora valida OTROS'
) AS seed
JOIN contextos_cliente c ON c.codigo = seed.contexto
ON DUPLICATE KEY UPDATE
    nombre_comercial = VALUES(nombre_comercial),
    razon_social = VALUES(razon_social),
    tipo_empresa = VALUES(tipo_empresa),
    observaciones = VALUES(observaciones),
    activo = VALUES(activo),
    updated_at = VALUES(updated_at);

-- ============================================================
-- 2) Estaciones base
-- ============================================================
INSERT INTO estaciones_servicio (
    id_contexto,
    id_empresa_cliente,
    codigo_estacion,
    nombre,
    direccion,
    codigo_postal,
    poblacion,
    provincia,
    pais,
    estado,
    observaciones,
    activo,
    created_at,
    updated_at
)
SELECT
    c.id_contexto,
    e.id_empresa,
    seed.codigo_estacion,
    seed.nombre_estacion,
    seed.direccion,
    seed.codigo_postal,
    seed.poblacion,
    seed.provincia,
    'Espana',
    'activa',
    CONCAT(@marker, ' ', seed.observaciones),
    1,
    @now,
    @now
FROM (
    SELECT 'MOEVE' AS contexto, 'FD26 MOEVE CLIENTE' AS empresa_cliente, 'FD26-MOE-001' AS codigo_estacion, 'FD26 Estacion Moeve Norte' AS nombre_estacion, 'Calle Norte 1' AS direccion, '28001' AS codigo_postal, 'Madrid' AS poblacion, 'Madrid' AS provincia, 'Estacion operativa MOEVE principal' AS observaciones
    UNION ALL SELECT 'MOEVE', 'FD26 MOEVE CLIENTE', 'FD26-MOE-002', 'FD26 Estacion Moeve Sur', 'Calle Sur 2', '28002', 'Madrid', 'Madrid', 'Estacion operativa MOEVE secundaria'
    UNION ALL SELECT 'REPSOL', 'FD26 REPSOL CLIENTE', 'FD26-REP-001', 'FD26 Estacion Repsol Centro', 'Avenida Centro 10', '41001', 'Sevilla', 'Sevilla', 'Estacion operativa REPSOL principal'
    UNION ALL SELECT 'REPSOL', 'FD26 REPSOL CLIENTE', 'FD26-REP-002', 'FD26 Estacion Repsol Este', 'Avenida Este 20', '46001', 'Valencia', 'Valencia', 'Estacion operativa REPSOL secundaria'
    UNION ALL SELECT 'OTROS', 'FD26 OTROS CLIENTE', 'FD26-OTR-001', 'FD26 Estacion Otros Base', 'Parque Industrial 3', '09001', 'Burgos', 'Burgos', 'Estacion operativa OTROS'
) AS seed
JOIN contextos_cliente c ON c.codigo = seed.contexto
JOIN empresas e ON e.id_contexto = c.id_contexto AND e.nombre = seed.empresa_cliente
ON DUPLICATE KEY UPDATE
    id_empresa_cliente = VALUES(id_empresa_cliente),
    nombre = VALUES(nombre),
    direccion = VALUES(direccion),
    codigo_postal = VALUES(codigo_postal),
    poblacion = VALUES(poblacion),
    provincia = VALUES(provincia),
    estado = VALUES(estado),
    observaciones = VALUES(observaciones),
    activo = VALUES(activo),
    updated_at = VALUES(updated_at);

-- ============================================================
-- 3) Contratos
-- ============================================================
INSERT INTO contratos (
    id_contexto,
    id_empresa_cliente,
    codigo_contrato,
    nombre,
    tipo,
    fecha_inicio,
    fecha_fin,
    estado,
    observaciones,
    activo,
    created_at,
    updated_at
)
SELECT
    c.id_contexto,
    e.id_empresa,
    seed.codigo_contrato,
    seed.nombre_contrato,
    'marco',
    '2026-01-01',
    NULL,
    'vigente',
    CONCAT(@marker, ' ', seed.observaciones),
    1,
    @now,
    @now
FROM (
    SELECT 'MOEVE' AS contexto, 'FD26 MOEVE CLIENTE' AS empresa_cliente, 'FD26-MOEVE-OK' AS codigo_contrato, 'FD26 Contrato MOEVE operativo' AS nombre_contrato, 'Contrato valido con sociedad y CIF' AS observaciones
    UNION ALL SELECT 'MOEVE', 'FD26 MOEVE CLIENTE', 'FD26-MOEVE-NOCIF', 'FD26 Contrato MOEVE sin sociedad valida', 'Contrato para prueba de aviso sociedad/CIF'
    UNION ALL SELECT 'REPSOL', 'FD26 REPSOL CLIENTE', 'FD26-REPSOL-OK', 'FD26 Contrato REPSOL operativo', 'Contrato valido con sociedad y CIF'
    UNION ALL SELECT 'REPSOL', 'FD26 REPSOL CLIENTE', 'FD26-REPSOL-NOSOC', 'FD26 Contrato REPSOL sin sociedad valida', 'Contrato para prueba de aviso sociedad/CIF'
    UNION ALL SELECT 'OTROS', 'FD26 OTROS CLIENTE', 'FD26-OTROS-OK', 'FD26 Contrato OTROS operativo', 'Contrato valido con sociedad y CIF'
) AS seed
JOIN contextos_cliente c ON c.codigo = seed.contexto
JOIN empresas e ON e.id_contexto = c.id_contexto AND e.nombre = seed.empresa_cliente
ON DUPLICATE KEY UPDATE
    id_empresa_cliente = VALUES(id_empresa_cliente),
    nombre = VALUES(nombre),
    tipo = VALUES(tipo),
    estado = VALUES(estado),
    observaciones = VALUES(observaciones),
    activo = VALUES(activo),
    updated_at = VALUES(updated_at);

-- ============================================================
-- 4) Tarifarios
-- ============================================================
INSERT INTO tarifarios (
    id_contexto,
    id_contrato,
    nombre,
    version,
    fecha_inicio_vigencia,
    fecha_fin_vigencia,
    factor_multiplicador,
    moneda,
    observaciones,
    activo,
    created_at,
    updated_at
)
SELECT
    c.id_contexto,
    ct.id_contrato,
    seed.nombre_tarifario,
    '2026-05',
    '2026-01-01',
    NULL,
    1.0000,
    'EUR',
    CONCAT(@marker, ' ', seed.observaciones),
    1,
    @now,
    @now
FROM (
    SELECT 'MOEVE' AS contexto, 'FD26-MOEVE-OK' AS codigo_contrato, 'FD26 Tarifario MOEVE OK' AS nombre_tarifario, 'Tarifario operativo MOEVE' AS observaciones
    UNION ALL SELECT 'MOEVE', 'FD26-MOEVE-NOCIF', 'FD26 Tarifario MOEVE NOCIF', 'Tarifario para contrato sin sociedad valida'
    UNION ALL SELECT 'REPSOL', 'FD26-REPSOL-OK', 'FD26 Tarifario REPSOL OK', 'Tarifario operativo REPSOL'
    UNION ALL SELECT 'REPSOL', 'FD26-REPSOL-NOSOC', 'FD26 Tarifario REPSOL NOSOC', 'Tarifario para contrato sin sociedad valida'
    UNION ALL SELECT 'OTROS', 'FD26-OTROS-OK', 'FD26 Tarifario OTROS OK', 'Tarifario operativo OTROS'
) AS seed
JOIN contextos_cliente c ON c.codigo = seed.contexto
JOIN contratos ct ON ct.id_contexto = c.id_contexto AND ct.codigo_contrato = seed.codigo_contrato
ON DUPLICATE KEY UPDATE
    id_contrato = VALUES(id_contrato),
    fecha_inicio_vigencia = VALUES(fecha_inicio_vigencia),
    factor_multiplicador = VALUES(factor_multiplicador),
    moneda = VALUES(moneda),
    observaciones = VALUES(observaciones),
    activo = VALUES(activo),
    updated_at = VALUES(updated_at);

-- ============================================================
-- 5) Lineas de tarifario
-- ============================================================
INSERT INTO tarifario_lineas (
    id_contexto,
    id_tarifario,
    codigo_tarifa,
    grupo,
    actuacion,
    descripcion,
    tarifa_anterior,
    tarifa_base,
    tarifa_aplicada,
    id_unidad,
    activo,
    created_at,
    updated_at
)
SELECT
    c.id_contexto,
    t.id_tarifario,
    seed.codigo_tarifa,
    seed.grupo,
    seed.actuacion,
    seed.descripcion,
    NULL,
    seed.tarifa_base,
    seed.tarifa_aplicada,
    NULL,
    1,
    @now,
    @now
FROM (
    SELECT 'MOEVE' AS contexto, 'FD26 Tarifario MOEVE OK' AS tarifario, 'MOE-OK-01' AS codigo_tarifa, 'Ingenieria' AS grupo, 'Estudio tecnico MOEVE' AS actuacion, 'Linea base MOEVE 1' AS descripcion, 1000.00 AS tarifa_base, 1000.00 AS tarifa_aplicada
    UNION ALL SELECT 'MOEVE', 'FD26 Tarifario MOEVE OK', 'MOE-OK-02', 'Ingenieria', 'Gestion administrativa MOEVE', 'Linea base MOEVE 2', 600.00, 600.00
    UNION ALL SELECT 'MOEVE', 'FD26 Tarifario MOEVE NOCIF', 'MOE-BAD-01', 'Control', 'Trabajo contrato sin sociedad valida', 'Linea para prueba controlada MOEVE', 500.00, 500.00
    UNION ALL SELECT 'REPSOL', 'FD26 Tarifario REPSOL OK', 'REP-OK-01', 'Diseno', 'Proyecto base REPSOL', 'Linea base REPSOL 1', 1200.00, 1200.00
    UNION ALL SELECT 'REPSOL', 'FD26 Tarifario REPSOL OK', 'REP-OK-02', 'Diseno', 'Proyecto complementario REPSOL', 'Linea base REPSOL 2', 800.00, 800.00
    UNION ALL SELECT 'REPSOL', 'FD26 Tarifario REPSOL NOSOC', 'REP-BAD-01', 'Diseno', 'Trabajo contrato sin sociedad valida', 'Linea para prueba controlada REPSOL', 450.00, 450.00
    UNION ALL SELECT 'OTROS', 'FD26 Tarifario OTROS OK', 'OTR-OK-01', 'General', 'Servicio tecnico OTROS', 'Linea base OTROS 1', 700.00, 700.00
    UNION ALL SELECT 'OTROS', 'FD26 Tarifario OTROS OK', 'OTR-OK-02', 'General', 'Servicio complementario OTROS', 'Linea base OTROS 2', 300.00, 300.00
) AS seed
JOIN contextos_cliente c ON c.codigo = seed.contexto
JOIN tarifarios t ON t.id_contexto = c.id_contexto AND t.nombre = seed.tarifario AND t.version = '2026-05'
ON DUPLICATE KEY UPDATE
    grupo = VALUES(grupo),
    actuacion = VALUES(actuacion),
    descripcion = VALUES(descripcion),
    tarifa_base = VALUES(tarifa_base),
    tarifa_aplicada = VALUES(tarifa_aplicada),
    activo = VALUES(activo),
    updated_at = VALUES(updated_at);

-- ============================================================
-- 6) Relacion contrato - sociedad facturadora
-- ============================================================
INSERT INTO contrato_empresas_facturadoras (
    id_contrato,
    id_empresa,
    id_contexto,
    activo,
    observaciones,
    created_at,
    updated_at
)
SELECT
    ct.id_contrato,
    ef.id_empresa,
    c.id_contexto,
    1,
    CONCAT(@marker, ' ', seed.observaciones),
    @now,
    @now
FROM (
    SELECT 'MOEVE' AS contexto, 'FD26-MOEVE-OK' AS codigo_contrato, 'FD26 MOEVE FACTURADORA' AS empresa_facturadora, 'Relacion valida contrato-sociedad MOEVE' AS observaciones
    UNION ALL SELECT 'REPSOL', 'FD26-REPSOL-OK', 'FD26 REPSOL FACTURADORA', 'Relacion valida contrato-sociedad REPSOL'
    UNION ALL SELECT 'REPSOL', 'FD26-REPSOL-NOSOC', 'FD26 REPSOL SIN CIF', 'Relacion con empresa sin CIF para prueba de aviso'
    UNION ALL SELECT 'OTROS', 'FD26-OTROS-OK', 'FD26 OTROS FACTURADORA', 'Relacion valida contrato-sociedad OTROS'
) AS seed
JOIN contextos_cliente c ON c.codigo = seed.contexto
JOIN contratos ct ON ct.id_contexto = c.id_contexto AND ct.codigo_contrato = seed.codigo_contrato
JOIN empresas ef ON ef.id_contexto = c.id_contexto AND ef.nombre = seed.empresa_facturadora
ON DUPLICATE KEY UPDATE
    activo = VALUES(activo),
    observaciones = VALUES(observaciones),
    updated_at = VALUES(updated_at);

-- ============================================================
-- 7) Casos de trabajo (14 casos)
-- ============================================================
DROP TEMPORARY TABLE IF EXISTS tmp_fd26_trabajos;
CREATE TEMPORARY TABLE tmp_fd26_trabajos (
    contexto_codigo VARCHAR(20) NOT NULL,
    numero_trabajo INT UNSIGNED NOT NULL,
    tipo_documento_codigo VARCHAR(30) NOT NULL,
    tipo_trabajo_codigo VARCHAR(80) NOT NULL,
    empresa_cliente_nombre VARCHAR(180) NOT NULL,
    estacion_codigo VARCHAR(80) NOT NULL,
    contrato_codigo VARCHAR(100) NOT NULL,
    tarifario_nombre VARCHAR(160) NOT NULL,
    estado_trabajo VARCHAR(30) NOT NULL,
    fecha_encargo DATE NULL,
    fecha_terminacion DATE NULL,
    descripcion_trabajo VARCHAR(255) NOT NULL,
    responsable_cliente VARCHAR(150) NULL,
    numero_aviso VARCHAR(100) NULL,
    orden_mantenimiento VARCHAR(100) NULL,
    PRIMARY KEY (contexto_codigo, tipo_documento_codigo, numero_trabajo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tmp_fd26_trabajos VALUES
('MOEVE', 910001, 'CONTROL_TRABAJOS', 'REFORMA',  'FD26 MOEVE CLIENTE',  'FD26-MOE-001', 'FD26-MOEVE-OK',    'FD26 Tarifario MOEVE OK',    'facturado',          '2026-04-01', '2026-04-05', 'MOEVE correcto con pedido y factura',                      'Cliente MOEVE A', 'AV-MOE-001', 'OM-MOE-001'),
('MOEVE', 910002, 'CONTROL_TRABAJOS', 'INDUSTRIA','FD26 MOEVE CLIENTE',  'FD26-MOE-002', 'FD26-MOEVE-OK',    'FD26 Tarifario MOEVE OK',    'pendiente_facturar', '2026-04-03', '2026-04-08', 'MOEVE con pedido pendiente de facturar',                   'Cliente MOEVE B', 'AV-MOE-002', 'OM-MOE-002'),
('MOEVE', 910003, 'CONTROL_TRABAJOS', 'REFORMA',  'FD26 MOEVE CLIENTE',  'FD26-MOE-001', 'FD26-MOEVE-NOCIF', 'FD26 Tarifario MOEVE NOCIF', 'terminado',          '2026-04-07', '2026-04-10', 'MOEVE con contrato sin sociedad/CIF valida',               'Cliente MOEVE C', 'AV-MOE-003', 'OM-MOE-003'),
('MOEVE', 910004, 'CONTROL_TRABAJOS', 'INDUSTRIA','FD26 MOEVE CLIENTE',  'FD26-MOE-002', 'FD26-MOEVE-OK',    'FD26 Tarifario MOEVE OK',    'terminado',          '2026-04-09', '2026-04-15', 'MOEVE terminado sin pedido ni factura',                    'Cliente MOEVE D', 'AV-MOE-004', 'OM-MOE-004'),
('MOEVE', 910005, 'CONTROL_TRABAJOS', 'REFORMA',  'FD26 MOEVE CLIENTE',  'FD26-MOE-001', 'FD26-MOEVE-OK',    'FD26 Tarifario MOEVE OK',    'en_curso',           '2026-04-12', NULL,         'MOEVE en curso para alta/edicion diaria',                  'Cliente MOEVE E', 'AV-MOE-005', 'OM-MOE-005'),

('REPSOL',920001, 'DISENO',           'REFORMA',  'FD26 REPSOL CLIENTE', 'FD26-REP-001', 'FD26-REPSOL-OK',   'FD26 Tarifario REPSOL OK',   'pendiente_facturar', '2026-04-02', '2026-04-11', 'REPSOL correcto con facturacion parcial',                  'Cliente REPSOL A','AV-REP-001', 'OM-REP-001'),
('REPSOL',920002, 'DISENO',           'NPV',      'FD26 REPSOL CLIENTE', 'FD26-REP-002', 'FD26-REPSOL-OK',   'FD26 Tarifario REPSOL OK',   'pendiente_facturar', '2026-04-05', '2026-04-12', 'REPSOL con pedido sin factura',                             'Cliente REPSOL B','AV-REP-002', 'OM-REP-002'),
('REPSOL',920003, 'DISENO',           'REFORMA',  'FD26 REPSOL CLIENTE', 'FD26-REP-001', 'FD26-REPSOL-NOSOC','FD26 Tarifario REPSOL NOSOC','en_curso',           '2026-04-06', NULL,         'REPSOL con contrato sin sociedad/CIF valida',              'Cliente REPSOL C','AV-REP-003', 'OM-REP-003'),
('REPSOL',920004, 'DISENO',           'NPV',      'FD26 REPSOL CLIENTE', 'FD26-REP-002', 'FD26-REPSOL-OK',   'FD26 Tarifario REPSOL OK',   'terminado',          '2026-04-08', '2026-04-16', 'REPSOL terminado sin pedido ni factura',                   'Cliente REPSOL D','AV-REP-004', 'OM-REP-004'),
('REPSOL',920005, 'DISENO',           'REFORMA',  'FD26 REPSOL CLIENTE', 'FD26-REP-001', 'FD26-REPSOL-OK',   'FD26 Tarifario REPSOL OK',   'finalizado',         '2026-04-10', '2026-04-20', 'REPSOL finalizado y facturado',                            'Cliente REPSOL E','AV-REP-005', 'OM-REP-005'),

('OTROS', 930001, 'OTROS',            'OBRA',     'FD26 OTROS CLIENTE',  'FD26-OTR-001', 'FD26-OTROS-OK',    'FD26 Tarifario OTROS OK',    'facturado',          '2026-04-04', '2026-04-09', 'OTROS correcto con pedido y factura',                      'Cliente OTROS A', 'AV-OTR-001', 'OM-OTR-001'),
('OTROS', 930002, 'OTROS',            'MTO',      'FD26 OTROS CLIENTE',  'FD26-OTR-001', 'FD26-OTROS-OK',    'FD26 Tarifario OTROS OK',    'terminado',          '2026-04-07', '2026-04-14', 'OTROS terminado sin factura',                               'Cliente OTROS B', 'AV-OTR-002', 'OM-OTR-002'),
('OTROS', 930003, 'OTROS',            'OBRA',     'FD26 OTROS CLIENTE',  'FD26-OTR-001', 'FD26-OTROS-OK',    'FD26 Tarifario OTROS OK',    'en_curso',           '2026-04-11', NULL,         'OTROS en curso con pedido pendiente',                       'Cliente OTROS C', 'AV-OTR-003', 'OM-OTR-003'),
('OTROS', 930004, 'OTROS',            'MTO',      'FD26 OTROS CLIENTE',  'FD26-OTR-001', 'FD26-OTROS-OK',    'FD26 Tarifario OTROS OK',    'cancelado',          '2026-04-13', NULL,         'OTROS cancelado sin pedido',                                'Cliente OTROS D', 'AV-OTR-004', 'OM-OTR-004');

INSERT INTO trabajos (
    id_contexto,
    id_empresa_cliente,
    id_estacion_servicio,
    id_tipo_documento,
    id_tipo_trabajo,
    id_contrato,
    id_tarifario,
    id_responsable_ciete,
    numero_trabajo,
    numero_trabajo_operativo,
    numero_estacion,
    zona,
    descripcion_trabajo,
    fecha_encargo,
    fecha_terminacion,
    observaciones,
    numero_aviso,
    orden_mantenimiento,
    categoria,
    responsable_cliente,
    estado,
    bloqueado_cierre,
    created_at,
    updated_at
)
SELECT
    c.id_contexto,
    ec.id_empresa,
    es.id_estacion_servicio,
    td.id_tipo_documento,
    tt.id_tipo_trabajo,
    ct.id_contrato,
    tf.id_tarifario,
    ux.id_usuario,
    seed.numero_trabajo,
    CONCAT('FD26-', seed.numero_trabajo),
    es.codigo_estacion,
    NULL,
    seed.descripcion_trabajo,
    seed.fecha_encargo,
    seed.fecha_terminacion,
    CONCAT(@marker, ' ', seed.descripcion_trabajo),
    seed.numero_aviso,
    seed.orden_mantenimiento,
    'FD26',
    seed.responsable_cliente,
    seed.estado_trabajo,
    0,
    @now,
    @now
FROM tmp_fd26_trabajos seed
JOIN contextos_cliente c ON c.codigo = seed.contexto_codigo
JOIN empresas ec ON ec.id_contexto = c.id_contexto AND ec.nombre = seed.empresa_cliente_nombre
JOIN estaciones_servicio es ON es.id_contexto = c.id_contexto AND es.codigo_estacion = seed.estacion_codigo
JOIN tipos_documento td ON td.id_contexto = c.id_contexto AND td.codigo = seed.tipo_documento_codigo
JOIN tipos_trabajo tt ON tt.id_contexto = c.id_contexto AND tt.id_tipo_documento = td.id_tipo_documento AND tt.codigo = seed.tipo_trabajo_codigo
JOIN contratos ct ON ct.id_contexto = c.id_contexto AND ct.codigo_contrato = seed.contrato_codigo
JOIN tarifarios tf ON tf.id_contexto = c.id_contexto AND tf.id_contrato = ct.id_contrato AND tf.nombre = seed.tarifario_nombre AND tf.version = '2026-05'
LEFT JOIN (
    SELECT id_contexto, MIN(id_usuario) AS id_usuario
    FROM usuarios
    WHERE activo = 1
    GROUP BY id_contexto
) ux ON ux.id_contexto = c.id_contexto
ON DUPLICATE KEY UPDATE
    id_empresa_cliente = VALUES(id_empresa_cliente),
    id_estacion_servicio = VALUES(id_estacion_servicio),
    id_tipo_trabajo = VALUES(id_tipo_trabajo),
    id_contrato = VALUES(id_contrato),
    id_tarifario = VALUES(id_tarifario),
    id_responsable_ciete = VALUES(id_responsable_ciete),
    numero_trabajo_operativo = VALUES(numero_trabajo_operativo),
    descripcion_trabajo = VALUES(descripcion_trabajo),
    fecha_encargo = VALUES(fecha_encargo),
    fecha_terminacion = VALUES(fecha_terminacion),
    observaciones = VALUES(observaciones),
    numero_aviso = VALUES(numero_aviso),
    orden_mantenimiento = VALUES(orden_mantenimiento),
    categoria = VALUES(categoria),
    responsable_cliente = VALUES(responsable_cliente),
    estado = VALUES(estado),
    updated_at = VALUES(updated_at);

-- ============================================================
-- 8) Pedidos y lineas
-- ============================================================
DROP TEMPORARY TABLE IF EXISTS tmp_fd26_pedidos;
CREATE TEMPORARY TABLE tmp_fd26_pedidos (
    contexto_codigo VARCHAR(20) NOT NULL,
    numero_trabajo INT UNSIGNED NOT NULL,
    tipo_documento_codigo VARCHAR(30) NOT NULL,
    numero_pedido VARCHAR(100) NOT NULL,
    fecha_solicitud DATE NULL,
    fecha_recepcion DATE NULL,
    estado_pedido VARCHAR(30) NOT NULL,
    PRIMARY KEY (contexto_codigo, numero_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tmp_fd26_pedidos VALUES
('MOEVE', 910001, 'CONTROL_TRABAJOS', 'P-FD26-MOE-001', '2026-04-01', '2026-04-05', 'facturado'),
('MOEVE', 910002, 'CONTROL_TRABAJOS', 'P-FD26-MOE-002', '2026-04-03', NULL,         'recibido'),
('MOEVE', 910003, 'CONTROL_TRABAJOS', 'P-FD26-MOE-003', '2026-04-07', NULL,         'solicitado'),
('REPSOL',920001, 'DISENO',           'P-FD26-REP-001', '2026-04-02', '2026-04-11', 'facturado_parcial'),
('REPSOL',920002, 'DISENO',           'P-FD26-REP-002', '2026-04-05', NULL,         'recibido'),
('REPSOL',920003, 'DISENO',           'P-FD26-REP-003', '2026-04-06', NULL,         'solicitado'),
('REPSOL',920005, 'DISENO',           'P-FD26-REP-005', '2026-04-10', '2026-04-20', 'facturado'),
('OTROS', 930001, 'OTROS',            'P-FD26-OTR-001', '2026-04-04', '2026-04-09', 'facturado'),
('OTROS', 930003, 'OTROS',            'P-FD26-OTR-003', '2026-04-11', NULL,         'recibido');

INSERT INTO pedidos (
    id_contexto,
    id_trabajo,
    id_tarifario,
    numero_pedido,
    fecha_solicitud,
    fecha_recepcion,
    importe_pedido,
    importe_solicitado,
    importe_facturado,
    unidades_pedido,
    unidades_solicitadas,
    estado,
    pedido_completo,
    tiene_mas_de_1_item,
    facturado_completo,
    observaciones,
    created_at,
    updated_at
)
SELECT
    c.id_contexto,
    tr.id_trabajo,
    tr.id_tarifario,
    seed.numero_pedido,
    seed.fecha_solicitud,
    seed.fecha_recepcion,
    0.00,
    0.00,
    0.00,
    0.000,
    0.000,
    seed.estado_pedido,
    0,
    0,
    0,
    CONCAT(@marker, ' Pedido ', seed.numero_pedido),
    @now,
    @now
FROM tmp_fd26_pedidos seed
JOIN contextos_cliente c ON c.codigo = seed.contexto_codigo
JOIN tipos_documento td ON td.id_contexto = c.id_contexto AND td.codigo = seed.tipo_documento_codigo
JOIN trabajos tr ON tr.id_contexto = c.id_contexto AND tr.id_tipo_documento = td.id_tipo_documento AND tr.numero_trabajo = seed.numero_trabajo
LEFT JOIN pedidos px ON px.id_contexto = c.id_contexto AND px.numero_pedido = seed.numero_pedido
WHERE px.id_pedido IS NULL;

DROP TEMPORARY TABLE IF EXISTS tmp_fd26_items;
CREATE TEMPORARY TABLE tmp_fd26_items (
    contexto_codigo VARCHAR(20) NOT NULL,
    numero_pedido VARCHAR(100) NOT NULL,
    codigo_tarifa VARCHAR(30) NOT NULL,
    descripcion_servicio VARCHAR(255) NOT NULL,
    cantidad DECIMAL(14,3) NOT NULL,
    precio_unitario DECIMAL(14,2) NOT NULL,
    PRIMARY KEY (contexto_codigo, numero_pedido, codigo_tarifa, descripcion_servicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tmp_fd26_items VALUES
('MOEVE', 'P-FD26-MOE-001', 'MOE-OK-01', 'Servicio principal MOEVE', 1.000, 1000.00),
('MOEVE', 'P-FD26-MOE-002', 'MOE-OK-02', 'Servicio pendiente MOEVE', 1.000, 600.00),
('MOEVE', 'P-FD26-MOE-003', 'MOE-BAD-01','Servicio contrato sin sociedad MOEVE', 1.000, 500.00),
('REPSOL','P-FD26-REP-001', 'REP-OK-01', 'Servicio parcial REPSOL tramo 1', 1.000, 1200.00),
('REPSOL','P-FD26-REP-001', 'REP-OK-02', 'Servicio parcial REPSOL tramo 2', 1.000, 800.00),
('REPSOL','P-FD26-REP-002', 'REP-OK-02', 'Servicio pendiente REPSOL', 1.000, 800.00),
('REPSOL','P-FD26-REP-003', 'REP-BAD-01','Servicio contrato sin sociedad REPSOL', 1.000, 450.00),
('REPSOL','P-FD26-REP-005', 'REP-OK-01', 'Servicio finalizado REPSOL', 1.000, 1200.00),
('OTROS', 'P-FD26-OTR-001', 'OTR-OK-01', 'Servicio principal OTROS', 1.000, 700.00),
('OTROS', 'P-FD26-OTR-003', 'OTR-OK-02', 'Servicio pendiente OTROS', 1.000, 300.00);

INSERT INTO pedido_items (
    id_contexto,
    id_pedido,
    id_tarifario_linea,
    codigo_servicio,
    numero_tarifa,
    descripcion_servicio,
    precio_unitario,
    cantidad,
    total_linea,
    created_at,
    updated_at
)
SELECT
    c.id_contexto,
    p.id_pedido,
    tl.id_tarifario_linea,
    seed.codigo_tarifa,
    seed.codigo_tarifa,
    seed.descripcion_servicio,
    seed.precio_unitario,
    seed.cantidad,
    ROUND(seed.precio_unitario * seed.cantidad, 2),
    @now,
    @now
FROM tmp_fd26_items seed
JOIN contextos_cliente c ON c.codigo = seed.contexto_codigo
JOIN pedidos p ON p.id_contexto = c.id_contexto AND p.numero_pedido = seed.numero_pedido
JOIN trabajos tr ON tr.id_trabajo = p.id_trabajo
JOIN tarifario_lineas tl ON tl.id_contexto = c.id_contexto AND tl.id_tarifario = tr.id_tarifario AND tl.codigo_tarifa = seed.codigo_tarifa
LEFT JOIN pedido_items pi
    ON pi.id_pedido = p.id_pedido
   AND pi.codigo_servicio = seed.codigo_tarifa
   AND pi.descripcion_servicio = seed.descripcion_servicio
WHERE pi.id_pedido_item IS NULL;

UPDATE pedidos p
JOIN (
    SELECT
        pi.id_pedido,
        SUM(pi.total_linea) AS total_pedido,
        SUM(pi.cantidad) AS total_unidades,
        COUNT(*) AS total_items
    FROM pedido_items pi
    GROUP BY pi.id_pedido
) agg ON agg.id_pedido = p.id_pedido
SET
    p.importe_pedido = ROUND(agg.total_pedido, 2),
    p.importe_solicitado = ROUND(agg.total_pedido, 2),
    p.unidades_pedido = agg.total_unidades,
    p.unidades_solicitadas = agg.total_unidades,
    p.tiene_mas_de_1_item = IF(agg.total_items > 1, 1, 0),
    p.updated_at = @now
WHERE p.observaciones LIKE CONCAT('%', @marker, '%');

-- ============================================================
-- 9) Facturas y factura_items
-- ============================================================
DROP TEMPORARY TABLE IF EXISTS tmp_fd26_facturas;
CREATE TEMPORARY TABLE tmp_fd26_facturas (
    contexto_codigo VARCHAR(20) NOT NULL,
    numero_factura VARCHAR(100) NOT NULL,
    numero_pedido VARCHAR(100) NOT NULL,
    empresa_facturadora_nombre VARCHAR(180) NOT NULL,
    estado_factura VARCHAR(30) NOT NULL,
    fecha_emision DATE NULL,
    orden_factura TINYINT UNSIGNED NOT NULL,
    PRIMARY KEY (contexto_codigo, numero_factura)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tmp_fd26_facturas VALUES
('MOEVE', 'F-FD26-MOE-001',     'P-FD26-MOE-001', 'FD26 MOEVE FACTURADORA',  'emitida', '2026-04-06', 1),
('REPSOL','F-FD26-REP-001-P1',  'P-FD26-REP-001', 'FD26 REPSOL FACTURADORA', 'emitida', '2026-04-12', 1),
('REPSOL','F-FD26-REP-005',     'P-FD26-REP-005', 'FD26 REPSOL FACTURADORA', 'enviada', '2026-04-21', 1),
('OTROS', 'F-FD26-OTR-001',     'P-FD26-OTR-001', 'FD26 OTROS FACTURADORA',  'emitida', '2026-04-10', 1);

INSERT INTO facturas (
    id_contexto,
    id_trabajo,
    id_contrato,
    id_empresa_cliente,
    id_empresa_facturadora,
    numero_factura,
    numero_factura_ccp,
    serie,
    orden_factura,
    fecha_solicitud,
    fecha_emision,
    fecha_vencimiento,
    importe,
    base_imponible,
    iva,
    retencion,
    total,
    estado,
    autofactura,
    sociedad,
    observaciones,
    created_at,
    updated_at
)
SELECT
    c.id_contexto,
    tr.id_trabajo,
    tr.id_contrato,
    tr.id_empresa_cliente,
    ef.id_empresa,
    seed.numero_factura,
    NULL,
    'FD26',
    seed.orden_factura,
    p.fecha_solicitud,
    seed.fecha_emision,
    NULL,
    0.00,
    0.00,
    0.00,
    0.00,
    0.00,
    seed.estado_factura,
    0,
    ef.nombre,
    CONCAT(@marker, ' Factura ', seed.numero_factura),
    @now,
    @now
FROM tmp_fd26_facturas seed
JOIN contextos_cliente c ON c.codigo = seed.contexto_codigo
JOIN pedidos p ON p.id_contexto = c.id_contexto AND p.numero_pedido = seed.numero_pedido
JOIN trabajos tr ON tr.id_trabajo = p.id_trabajo
JOIN empresas ef ON ef.id_contexto = c.id_contexto AND ef.nombre = seed.empresa_facturadora_nombre
ON DUPLICATE KEY UPDATE
    id_trabajo = VALUES(id_trabajo),
    id_contrato = VALUES(id_contrato),
    id_empresa_cliente = VALUES(id_empresa_cliente),
    orden_factura = VALUES(orden_factura),
    fecha_solicitud = VALUES(fecha_solicitud),
    fecha_emision = VALUES(fecha_emision),
    estado = VALUES(estado),
    sociedad = VALUES(sociedad),
    observaciones = VALUES(observaciones),
    updated_at = VALUES(updated_at);

DROP TEMPORARY TABLE IF EXISTS tmp_fd26_factura_items;
CREATE TEMPORARY TABLE tmp_fd26_factura_items (
    contexto_codigo VARCHAR(20) NOT NULL,
    numero_factura VARCHAR(100) NOT NULL,
    numero_pedido VARCHAR(100) NOT NULL,
    codigo_tarifa VARCHAR(30) NOT NULL,
    unidades_facturadas DECIMAL(10,3) NOT NULL,
    importe_facturado DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (contexto_codigo, numero_factura, numero_pedido, codigo_tarifa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tmp_fd26_factura_items VALUES
('MOEVE', 'F-FD26-MOE-001',    'P-FD26-MOE-001', 'MOE-OK-01', 1.000, 1000.00),
('REPSOL','F-FD26-REP-001-P1', 'P-FD26-REP-001', 'REP-OK-01', 1.000, 1200.00),
('REPSOL','F-FD26-REP-005',    'P-FD26-REP-005', 'REP-OK-01', 1.000, 1200.00),
('OTROS', 'F-FD26-OTR-001',    'P-FD26-OTR-001', 'OTR-OK-01', 1.000, 700.00);

INSERT INTO factura_items (
    id_factura,
    id_pedido_item,
    unidades_facturadas,
    importe_facturado,
    observaciones,
    created_at,
    updated_at
)
SELECT
    f.id_factura,
    pi.id_pedido_item,
    seed.unidades_facturadas,
    seed.importe_facturado,
    CONCAT(@marker, ' Facturacion item ', seed.codigo_tarifa),
    @now,
    @now
FROM tmp_fd26_factura_items seed
JOIN contextos_cliente c ON c.codigo = seed.contexto_codigo
JOIN facturas f ON f.id_contexto = c.id_contexto AND f.numero_factura = seed.numero_factura
JOIN pedidos p ON p.id_contexto = c.id_contexto AND p.numero_pedido = seed.numero_pedido
JOIN pedido_items pi ON pi.id_pedido = p.id_pedido AND pi.codigo_servicio = seed.codigo_tarifa
LEFT JOIN factura_items fi ON fi.id_factura = f.id_factura AND fi.id_pedido_item = pi.id_pedido_item
WHERE fi.id_factura_item IS NULL;

UPDATE facturas f
JOIN (
    SELECT
        fi.id_factura,
        ROUND(SUM(fi.importe_facturado), 2) AS total_base
    FROM factura_items fi
    GROUP BY fi.id_factura
) agg ON agg.id_factura = f.id_factura
SET
    f.importe = agg.total_base,
    f.base_imponible = agg.total_base,
    f.iva = ROUND(agg.total_base * 0.21, 2),
    f.total = ROUND(agg.total_base * 1.21, 2),
    f.updated_at = @now
WHERE f.observaciones LIKE CONCAT('%', @marker, '%');

UPDATE pedidos p
LEFT JOIN (
    SELECT
        pi.id_pedido,
        ROUND(SUM(fi.importe_facturado), 2) AS total_facturado
    FROM pedido_items pi
    LEFT JOIN factura_items fi ON fi.id_pedido_item = pi.id_pedido_item
    GROUP BY pi.id_pedido
) agg ON agg.id_pedido = p.id_pedido
SET
    p.importe_facturado = COALESCE(agg.total_facturado, 0.00),
    p.facturado_completo = IF(COALESCE(agg.total_facturado, 0.00) >= p.importe_pedido AND p.importe_pedido > 0, 1, 0),
    p.updated_at = @now
WHERE p.observaciones LIKE CONCAT('%', @marker, '%');

-- ============================================================
-- 10) Comprobaciones rapidas de cobertura
-- ============================================================
SELECT 'trabajos_fd26' AS check_name, COUNT(*) AS total
FROM trabajos
WHERE observaciones LIKE CONCAT('%', @marker, '%');

SELECT 'pedidos_fd26' AS check_name, COUNT(*) AS total
FROM pedidos
WHERE observaciones LIKE CONCAT('%', @marker, '%');

SELECT 'facturas_fd26' AS check_name, COUNT(*) AS total
FROM facturas
WHERE observaciones LIKE CONCAT('%', @marker, '%');

SELECT 'casos_cierre_terminado_sin_factura' AS check_name, COUNT(*) AS total
FROM trabajos t
WHERE t.observaciones LIKE CONCAT('%', @marker, '%')
  AND t.estado = 'terminado'
  AND NOT EXISTS (
      SELECT 1
      FROM pedidos p
      JOIN pedido_items pi ON pi.id_pedido = p.id_pedido
      JOIN factura_items fi ON fi.id_pedido_item = pi.id_pedido_item
      WHERE p.id_trabajo = t.id_trabajo
  );

SELECT 'casos_contrato_sin_sociedad_valida' AS check_name, COUNT(*) AS total
FROM contratos c
WHERE c.observaciones LIKE CONCAT('%', @marker, '%')
  AND c.activo = 1
  AND NOT EXISTS (
      SELECT 1
      FROM contrato_empresas_facturadoras cef
      JOIN empresas e ON e.id_empresa = cef.id_empresa AND e.id_contexto = cef.id_contexto
      WHERE cef.id_contrato = c.id_contrato
        AND cef.id_contexto = c.id_contexto
        AND cef.activo = 1
        AND e.activo = 1
        AND e.cif IS NOT NULL
        AND TRIM(e.cif) <> ''
  );

DROP TEMPORARY TABLE IF EXISTS tmp_fd26_trabajos;
DROP TEMPORARY TABLE IF EXISTS tmp_fd26_pedidos;
DROP TEMPORARY TABLE IF EXISTS tmp_fd26_items;
DROP TEMPORARY TABLE IF EXISTS tmp_fd26_facturas;
DROP TEMPORARY TABLE IF EXISTS tmp_fd26_factura_items;

-- Fin script
