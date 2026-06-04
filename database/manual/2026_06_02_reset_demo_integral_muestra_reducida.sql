-- ============================================================
-- ERP CIETE - Reset integral demo con muestra reducida
-- Fecha: 2026-06-02
-- Uso exclusivo: entorno LOCAL/DEMO abaco_ciete
-- ============================================================
--
-- No toca usuarios, roles, permisos, autenticacion ni migraciones.
-- Limpia operativa e importaciones demo, reduce maestros masivos y
-- recrea una muestra pequena e idempotente por codigos naturales.
--
-- Ejecucion esperada:
-- mysql --database=abaco_ciete \
--   --init-command="SET @ciete_reset_integral_local_confirmed=1" \
--   < database/manual/2026_06_02_reset_demo_integral_muestra_reducida.sql

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DROP PROCEDURE IF EXISTS ciete_assert_reset_integral_local;

DELIMITER $$

CREATE PROCEDURE ciete_assert_reset_integral_local()
BEGIN
    IF DATABASE() <> 'abaco_ciete' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Reset cancelado: la base activa no es abaco_ciete.';
    END IF;

    IF COALESCE(@ciete_reset_integral_local_confirmed, 0) <> 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Reset cancelado: falta confirmar entorno local/demo.';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'tarifarios'
          AND COLUMN_NAME = 'es_predeterminado'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Reset cancelado: falta tarifarios.es_predeterminado.';
    END IF;
END$$

DELIMITER ;

CALL ciete_assert_reset_integral_local();
DROP PROCEDURE ciete_assert_reset_integral_local;

SET @now := NOW();
SET @marker := '[demo-integral-reducida-2026-06-02]';

SET @ctx_moeve := (SELECT id_contexto FROM contextos_cliente WHERE codigo = 'MOEVE' LIMIT 1);
SET @ctx_repsol := (SELECT id_contexto FROM contextos_cliente WHERE codigo = 'REPSOL' LIMIT 1);
SET @ctx_otros := (SELECT id_contexto FROM contextos_cliente WHERE codigo = 'OTROS' LIMIT 1);

START TRANSACTION;

-- ============================================================
-- 1) Conservar seleccion real reducida antes de limpiar maestros
-- ============================================================
DROP TEMPORARY TABLE IF EXISTS tmp_keep_estaciones;
CREATE TEMPORARY TABLE tmp_keep_estaciones (
    id_estacion_servicio BIGINT UNSIGNED PRIMARY KEY
) ENGINE=InnoDB;

INSERT INTO tmp_keep_estaciones (id_estacion_servicio)
SELECT selected.id_estacion_servicio
FROM (
    SELECT es.id_estacion_servicio
    FROM estaciones_servicio es
    WHERE es.id_contexto = @ctx_moeve
      AND es.activo = TRUE
      AND es.codigo_estacion <> 'ES334500'
    ORDER BY
        CASE
            WHEN es.codigo_estacion = '33450' THEN 0
            WHEN es.codigo_estacion = '37190' THEN 1
            ELSE 2
        END,
        es.id_estacion_servicio
    LIMIT 20
) selected;

INSERT INTO tmp_keep_estaciones (id_estacion_servicio)
SELECT selected.id_estacion_servicio
FROM (
    SELECT es.id_estacion_servicio
    FROM estaciones_servicio es
    WHERE es.id_contexto = @ctx_repsol
      AND es.activo = TRUE
    ORDER BY es.id_estacion_servicio
    LIMIT 20
) selected;

DROP TEMPORARY TABLE IF EXISTS tmp_repsol_lineas;
CREATE TEMPORARY TABLE tmp_repsol_lineas AS
SELECT
    tl.codigo_tarifa,
    tl.grupo,
    tl.actuacion,
    tl.descripcion,
    tl.tarifa_anterior,
    tl.tarifa_base,
    tl.tarifa_aplicada,
    tl.id_unidad,
    tl.activo
FROM tarifario_lineas tl
JOIN tarifarios t ON t.id_tarifario = tl.id_tarifario
WHERE t.id_contexto = @ctx_repsol
  AND t.activo = TRUE
ORDER BY tl.id_tarifario_linea
LIMIT 12;

-- ============================================================
-- 2) Limpiar operativa e importaciones demo acumuladas
-- ============================================================
DELETE FROM comentarios_legalizaciones;
DELETE FROM legalizaciones_contactos;
DELETE FROM legalizaciones;
DELETE FROM presupuesto_lineas;
DELETE FROM presupuestos;
DELETE FROM cobros;
DELETE FROM factura_items;
DELETE FROM facturas;
DELETE FROM pedido_items;
DELETE FROM pedidos;
DELETE FROM trabajos;
DELETE FROM importacion_filas;
DELETE FROM importaciones;

-- ============================================================
-- 3) Reducir maestros masivos sin tocar seguridad
-- ============================================================
DELETE FROM contrato_empresas_facturadoras;
DELETE FROM tarifario_lineas;
DELETE FROM tarifarios;
DELETE FROM contratos;

DELETE es
FROM estaciones_servicio es
LEFT JOIN tmp_keep_estaciones keep_station
    ON keep_station.id_estacion_servicio = es.id_estacion_servicio
WHERE keep_station.id_estacion_servicio IS NULL;

UPDATE empresas
SET empresa_padre_id = NULL
WHERE empresa_padre_id IS NOT NULL;

DELETE em
FROM empresas em
LEFT JOIN contextos_cliente c ON c.id_contexto = em.id_contexto
WHERE c.codigo = 'OTROS'
  AND NOT EXISTS (
      SELECT 1
      FROM contactos_empresas ce
      WHERE ce.id_empresa = em.id_empresa
  )
  AND NOT EXISTS (
      SELECT 1
      FROM usuarios u
      WHERE u.id_contacto_empresa IS NOT NULL
        AND EXISTS (
            SELECT 1
            FROM contactos_empresas ce
            WHERE ce.id_contacto_empresa = u.id_contacto_empresa
              AND ce.id_empresa = em.id_empresa
        )
  );

-- ============================================================
-- 4) Empresas cliente/sociedad y estaciones OTROS
-- ============================================================
UPDATE empresas
SET
    tipo_empresa = 'cliente_proveedor',
    activo = TRUE,
    updated_at = @now
WHERE id_contexto = @ctx_moeve
  AND nombre = 'MOEVE';

UPDATE empresas
SET
    tipo_empresa = 'cliente_proveedor',
    activo = TRUE,
    updated_at = @now
WHERE id_contexto = @ctx_repsol
  AND nombre = 'REPSOL';

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
) VALUES (
    @ctx_otros,
    'OTROS DEMO',
    'OTROS DEMO',
    'OTROS DEMO, S.L.',
    'B90000999',
    'cliente_proveedor',
    CONCAT(@marker, ' Empresa cliente y sociedad facturadora demo.'),
    TRUE,
    @now,
    @now
)
ON DUPLICATE KEY UPDATE
    nombre_comercial = VALUES(nombre_comercial),
    razon_social = VALUES(razon_social),
    cif = VALUES(cif),
    tipo_empresa = VALUES(tipo_empresa),
    observaciones = VALUES(observaciones),
    activo = VALUES(activo),
    updated_at = VALUES(updated_at);

SET @empresa_moeve := (
    SELECT id_empresa FROM empresas
    WHERE id_contexto = @ctx_moeve AND nombre = 'MOEVE'
    LIMIT 1
);
SET @empresa_repsol := (
    SELECT id_empresa FROM empresas
    WHERE id_contexto = @ctx_repsol AND nombre = 'REPSOL'
    LIMIT 1
);
SET @empresa_otros := (
    SELECT id_empresa FROM empresas
    WHERE id_contexto = @ctx_otros AND nombre = 'OTROS DEMO'
    LIMIT 1
);

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
) VALUES
(@ctx_otros, @empresa_otros, 'OTR-001', 'OTROS DEMO CENTRO', 'Calle Demo 1', '28001', 'MADRID', 'MADRID', 'Espana', 'activa', CONCAT(@marker, ' Estacion OTROS demo.'), TRUE, @now, @now),
(@ctx_otros, @empresa_otros, 'OTR-002', 'OTROS DEMO NORTE', 'Calle Demo 2', '48001', 'BILBAO', 'BIZKAIA', 'Espana', 'activa', CONCAT(@marker, ' Estacion OTROS demo.'), TRUE, @now, @now)
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
-- 5) Contratos, sociedades facturadoras y tarifarios
-- ============================================================
INSERT INTO contratos (
    id_contexto,
    id_empresa_cliente,
    codigo_contrato,
    nombre,
    tipo,
    estado,
    observaciones,
    activo,
    created_at,
    updated_at
) VALUES
(@ctx_moeve, @empresa_moeve, '772', 'Contrato 772 MOEVE', 'marco', 'vigente', CONCAT(@marker, ' Contrato principal MOEVE.'), TRUE, @now, @now),
(@ctx_repsol, @empresa_repsol, 'REPSOL-2023-2027', 'Tarifa Repsol 2023-2027', 'marco', 'vigente', CONCAT(@marker, ' Contrato principal REPSOL.'), TRUE, @now, @now),
(@ctx_otros, @empresa_otros, 'OTROS-DEMO', 'Contrato OTROS DEMO', 'directo', 'vigente', CONCAT(@marker, ' Contrato OTROS demo.'), TRUE, @now, @now)
ON DUPLICATE KEY UPDATE
    id_empresa_cliente = VALUES(id_empresa_cliente),
    nombre = VALUES(nombre),
    tipo = VALUES(tipo),
    estado = VALUES(estado),
    observaciones = VALUES(observaciones),
    activo = VALUES(activo),
    updated_at = VALUES(updated_at);

SET @contrato_moeve := (
    SELECT id_contrato FROM contratos
    WHERE id_contexto = @ctx_moeve AND codigo_contrato = '772'
    LIMIT 1
);
SET @contrato_repsol := (
    SELECT id_contrato FROM contratos
    WHERE id_contexto = @ctx_repsol AND codigo_contrato = 'REPSOL-2023-2027'
    LIMIT 1
);
SET @contrato_otros := (
    SELECT id_contrato FROM contratos
    WHERE id_contexto = @ctx_otros AND codigo_contrato = 'OTROS-DEMO'
    LIMIT 1
);

INSERT INTO contrato_empresas_facturadoras (
    id_contrato,
    id_empresa,
    id_contexto,
    activo,
    observaciones,
    created_at,
    updated_at
) VALUES
(@contrato_moeve, @empresa_moeve, @ctx_moeve, TRUE, CONCAT(@marker, ' Sociedad/CIF MOEVE valida.'), @now, @now),
(@contrato_repsol, @empresa_repsol, @ctx_repsol, TRUE, CONCAT(@marker, ' Sociedad/CIF REPSOL valida.'), @now, @now),
(@contrato_otros, @empresa_otros, @ctx_otros, TRUE, CONCAT(@marker, ' Sociedad/CIF OTROS demo valida.'), @now, @now)
ON DUPLICATE KEY UPDATE
    activo = VALUES(activo),
    observaciones = VALUES(observaciones),
    updated_at = VALUES(updated_at);

INSERT INTO tarifarios (
    id_contexto,
    id_contrato,
    nombre,
    version,
    factor_multiplicador,
    moneda,
    observaciones,
    es_predeterminado,
    activo,
    created_at,
    updated_at
) VALUES
(@ctx_moeve, @contrato_moeve, 'Tarifario 772 MOEVE', '2026-demo', 1.0000, 'EUR', CONCAT(@marker, ' Tarifario MOEVE principal.'), TRUE, TRUE, @now, @now),
(@ctx_moeve, @contrato_moeve, 'Tarifario 772 MOEVE alternativo', '2026-demo-alt', 1.0000, 'EUR', CONCAT(@marker, ' Tarifario MOEVE alternativo para validar selector.'), FALSE, TRUE, @now, @now),
(@ctx_repsol, @contrato_repsol, 'TARIFA 23-27 REPSOL', '2023-2027', 1.0000, 'EUR', CONCAT(@marker, ' Tarifario REPSOL principal.'), TRUE, TRUE, @now, @now),
-- Repsol = tarifa única vigente (confirmado por negocio). El alternativo queda inactivo.
(@ctx_repsol, @contrato_repsol, 'TARIFA 23-27 REPSOL alternativa', '2023-2027-demo-alt', 1.0000, 'EUR', CONCAT(@marker, ' Tarifario REPSOL demo-alt. Inactivo: Repsol define tarifa unica vigente.'), FALSE, FALSE, @now, @now),
(@ctx_otros, @contrato_otros, 'Tarifario OTROS DEMO', '2026-demo', 1.0000, 'EUR', CONCAT(@marker, ' Tarifario OTROS demo.'), TRUE, TRUE, @now, @now),
(@ctx_otros, @contrato_otros, 'Tarifario OTROS DEMO alternativo', '2026-demo-alt', 1.0000, 'EUR', CONCAT(@marker, ' Tarifario OTROS alternativo para validar selector.'), FALSE, TRUE, @now, @now)
ON DUPLICATE KEY UPDATE
    id_contrato = VALUES(id_contrato),
    factor_multiplicador = VALUES(factor_multiplicador),
    moneda = VALUES(moneda),
    observaciones = VALUES(observaciones),
    es_predeterminado = VALUES(es_predeterminado),
    activo = VALUES(activo),
    updated_at = VALUES(updated_at);

SET @tarifario_moeve := (
    SELECT id_tarifario FROM tarifarios
    WHERE id_contexto = @ctx_moeve
      AND id_contrato = @contrato_moeve
      AND nombre = 'Tarifario 772 MOEVE'
    LIMIT 1
);
SET @tarifario_repsol := (
    SELECT id_tarifario FROM tarifarios
    WHERE id_contexto = @ctx_repsol
      AND id_contrato = @contrato_repsol
      AND nombre = 'TARIFA 23-27 REPSOL'
    LIMIT 1
);
SET @tarifario_moeve_alt := (
    SELECT id_tarifario FROM tarifarios
    WHERE id_contexto = @ctx_moeve
      AND id_contrato = @contrato_moeve
      AND nombre = 'Tarifario 772 MOEVE alternativo'
    LIMIT 1
);
SET @tarifario_repsol_alt := (
    SELECT id_tarifario FROM tarifarios
    WHERE id_contexto = @ctx_repsol
      AND id_contrato = @contrato_repsol
      AND nombre = 'TARIFA 23-27 REPSOL alternativa'
    LIMIT 1
);
SET @tarifario_otros := (
    SELECT id_tarifario FROM tarifarios
    WHERE id_contexto = @ctx_otros
      AND id_contrato = @contrato_otros
      AND nombre = 'Tarifario OTROS DEMO'
    LIMIT 1
);
SET @tarifario_otros_alt := (
    SELECT id_tarifario FROM tarifarios
    WHERE id_contexto = @ctx_otros
      AND id_contrato = @contrato_otros
      AND nombre = 'Tarifario OTROS DEMO alternativo'
    LIMIT 1
);

-- ============================================================
-- 6) Lineas reducidas: principales y alternativas para selector
-- ============================================================
INSERT INTO tarifario_lineas (
    id_contexto,
    id_tarifario,
    codigo_tarifa,
    actuacion,
    descripcion,
    tarifa_base,
    tarifa_aplicada,
    id_unidad,
    activo,
    created_at,
    updated_at
) VALUES
(@ctx_moeve, @tarifario_moeve, '165052', 'SERVICIO DIRECCION DE PROYECTO', CONCAT(@marker, ' Linea real La Senyera.'), 40.00, 40.00, 1, TRUE, @now, @now),
(@ctx_moeve, @tarifario_moeve, '165023', 'TOMA DE DATOS SIMPLE', CONCAT(@marker, ' Linea real La Senyera.'), 510.00, 510.00, 1, TRUE, @now, @now),
(@ctx_moeve, @tarifario_moeve, '181871', 'PJT LEGA. ACTI./INST. PJT CMPT INSTALA.', CONCAT(@marker, ' Linea real La Senyera.'), 2550.00, 2550.00, 1, TRUE, @now, @now),
(@ctx_moeve, @tarifario_moeve, '165027', 'PROYECTO OFICIAL. MEDIO', CONCAT(@marker, ' Linea real La Senyera.'), 1250.00, 1250.00, 1, TRUE, @now, @now),
(@ctx_moeve, @tarifario_moeve, '165031', 'INGENIERIA DE DETALLE. MEDIO', CONCAT(@marker, ' Linea real La Senyera.'), 2000.00, 2000.00, 1, TRUE, @now, @now),
(@ctx_moeve, @tarifario_moeve, '181825', 'CFO PROYECTO COMPLEJO', CONCAT(@marker, ' Linea real La Senyera.'), 790.00, 790.00, 1, TRUE, @now, @now),
(@ctx_moeve, @tarifario_moeve, '181874', 'OBT. AYTO DE LICENCIA PJT CMPT INSTALA.', CONCAT(@marker, ' Linea real La Senyera.'), 1825.00, 1825.00, 1, TRUE, @now, @now),
(@ctx_moeve, @tarifario_moeve, '165042', 'ESTUDIO OBTENCION CERTIF. COMP URBANIST', CONCAT(@marker, ' Linea real La Senyera.'), 570.00, 570.00, 1, TRUE, @now, @now);

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
    @ctx_repsol,
    @tarifario_repsol,
    source.codigo_tarifa,
    source.grupo,
    source.actuacion,
    source.descripcion,
    source.tarifa_anterior,
    source.tarifa_base,
    source.tarifa_aplicada,
    source.id_unidad,
    source.activo,
    @now,
    @now
FROM tmp_repsol_lineas source;

INSERT INTO tarifario_lineas (
    id_contexto,
    id_tarifario,
    codigo_tarifa,
    actuacion,
    descripcion,
    tarifa_base,
    tarifa_aplicada,
    id_unidad,
    activo,
    created_at,
    updated_at
) VALUES
(@ctx_otros, @tarifario_otros, 'OTR-001', 'Visita tecnica OTROS', CONCAT(@marker, ' Linea demo OTROS.'), 180.00, 180.00, 1, TRUE, @now, @now),
(@ctx_otros, @tarifario_otros, 'OTR-002', 'Informe tecnico OTROS', CONCAT(@marker, ' Linea demo OTROS.'), 350.00, 350.00, 1, TRUE, @now, @now),
(@ctx_otros, @tarifario_otros, 'OTR-003', 'Gestion administrativa OTROS', CONCAT(@marker, ' Linea demo OTROS.'), 120.00, 120.00, 1, TRUE, @now, @now),
(@ctx_otros, @tarifario_otros, 'OTR-004', 'Direccion de obra OTROS', CONCAT(@marker, ' Linea demo OTROS.'), 650.00, 650.00, 1, TRUE, @now, @now);

INSERT INTO tarifario_lineas (
    id_contexto,
    id_tarifario,
    codigo_tarifa,
    actuacion,
    descripcion,
    tarifa_base,
    tarifa_aplicada,
    id_unidad,
    activo,
    created_at,
    updated_at
) VALUES
(@ctx_moeve, @tarifario_moeve_alt, '165052', 'SERVICIO DIRECCION DE PROYECTO', CONCAT(@marker, ' Linea alternativa MOEVE para selector.'), 40.00, 40.00, 1, TRUE, @now, @now),
(@ctx_moeve, @tarifario_moeve_alt, '165023', 'TOMA DE DATOS SIMPLE', CONCAT(@marker, ' Linea alternativa MOEVE para selector.'), 510.00, 510.00, 1, TRUE, @now, @now),
(@ctx_otros, @tarifario_otros_alt, 'OTR-001', 'Visita tecnica OTROS', CONCAT(@marker, ' Linea alternativa OTROS para selector.'), 180.00, 180.00, 1, TRUE, @now, @now),
(@ctx_otros, @tarifario_otros_alt, 'OTR-002', 'Informe tecnico OTROS', CONCAT(@marker, ' Linea alternativa OTROS para selector.'), 350.00, 350.00, 1, TRUE, @now, @now);

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
    @ctx_repsol,
    @tarifario_repsol_alt,
    source.codigo_tarifa,
    source.grupo,
    source.actuacion,
    CONCAT(@marker, ' Linea alternativa REPSOL para selector.'),
    source.tarifa_anterior,
    source.tarifa_base,
    source.tarifa_aplicada,
    source.id_unidad,
    source.activo,
    @now,
    @now
FROM tmp_repsol_lineas source
LIMIT 2;

-- Defensa adicional: maximo un predeterminado por contrato.
UPDATE tarifarios t
JOIN (
    SELECT id_contrato, MAX(id_tarifario) AS id_tarifario_predeterminado
    FROM tarifarios
    WHERE activo = TRUE
      AND es_predeterminado = TRUE
    GROUP BY id_contrato
) selected ON selected.id_contrato = t.id_contrato
SET t.es_predeterminado = (t.id_tarifario = selected.id_tarifario_predeterminado);

-- ============================================================
-- 7) Trabajos demo: con y sin pedido, facturacion y cierre
-- ============================================================
DROP TEMPORARY TABLE IF EXISTS tmp_demo_trabajos;
CREATE TEMPORARY TABLE tmp_demo_trabajos (
    contexto_codigo VARCHAR(20) NOT NULL,
    numero_trabajo INT UNSIGNED NOT NULL,
    tipo_documento_codigo VARCHAR(30) NOT NULL,
    tipo_trabajo_codigo VARCHAR(80) NOT NULL,
    estacion_codigo VARCHAR(80) NOT NULL,
    contrato_codigo VARCHAR(100) NOT NULL,
    tarifario_nombre VARCHAR(160) NOT NULL,
    estado_trabajo VARCHAR(30) NOT NULL,
    fecha_encargo DATE NULL,
    fecha_terminacion DATE NULL,
    descripcion_trabajo VARCHAR(255) NOT NULL,
    responsable_cliente VARCHAR(150) NULL,
    PRIMARY KEY (contexto_codigo, tipo_documento_codigo, numero_trabajo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @moeve_estacion_2 := (
    SELECT codigo_estacion FROM estaciones_servicio
    WHERE id_contexto = @ctx_moeve AND codigo_estacion <> '33450'
    ORDER BY id_estacion_servicio
    LIMIT 1
);
SET @repsol_estacion_1 := (
    SELECT codigo_estacion FROM estaciones_servicio
    WHERE id_contexto = @ctx_repsol
    ORDER BY id_estacion_servicio
    LIMIT 1
);
SET @repsol_estacion_2 := (
    SELECT codigo_estacion FROM estaciones_servicio
    WHERE id_contexto = @ctx_repsol
    ORDER BY id_estacion_servicio
    LIMIT 1 OFFSET 1
);

INSERT INTO tmp_demo_trabajos VALUES
('MOEVE', 610001, 'CONTROL_TRABAJOS', 'REFORMA',   '33450',            '772',                'Tarifario 772 MOEVE', 'terminado',          '2026-05-06', '2026-05-20', 'La Senyera I - pedido exportable PDF CSV ARIBA', 'Cesar Garcia'),
('MOEVE', 610002, 'CONTROL_TRABAJOS', 'NPV',       '33450',            '772',                'Tarifario 772 MOEVE', 'en_curso',           '2026-05-10', NULL,         'Alta manual MOEVE sin pedido',                   'Equipo MOEVE'),
('MOEVE', 610003, 'CONTROL_TRABAJOS', 'INDUSTRIA', @moeve_estacion_2, '772',                'Tarifario 772 MOEVE', 'terminado',          '2026-05-11', '2026-05-21', 'MOEVE terminado sin pedido',                     'Equipo MOEVE'),
('MOEVE', 610004, 'CONTROL_TRABAJOS', 'REFORMA',   @moeve_estacion_2, '772',                'Tarifario 772 MOEVE', 'facturado',          '2026-05-12', '2026-05-22', 'MOEVE facturado completo listo para cierre',     'Equipo MOEVE'),
('MOEVE', 610005, 'CONTROL_TRABAJOS', 'NPV',       '33450',            '772',                'Tarifario 772 MOEVE', 'pendiente_facturar', '2026-05-13', '2026-05-23', 'MOEVE pedido pendiente de facturar',              'Equipo MOEVE'),
('MOEVE', 610006, 'CONTROL_TRABAJOS', 'INDUSTRIA', @moeve_estacion_2, '772',                'Tarifario 772 MOEVE', 'cancelado',          '2026-05-14', NULL,         'MOEVE cancelado visible al final',                'Equipo MOEVE'),
('REPSOL',620001, 'DISENO',           'REFORMA',   @repsol_estacion_1, 'REPSOL-2023-2027', 'TARIFA 23-27 REPSOL', 'pendiente_facturar', '2026-05-06', '2026-05-20', 'REPSOL pedido pendiente de facturar',             'Equipo REPSOL'),
('REPSOL',620002, 'DISENO',           'NPV',       @repsol_estacion_2, 'REPSOL-2023-2027', 'TARIFA 23-27 REPSOL', 'en_curso',           '2026-05-08', NULL,         'REPSOL alta manual sin pedido',                   'Equipo REPSOL'),
('REPSOL',620003, 'DISENO',           'REFORMA',   @repsol_estacion_1, 'REPSOL-2023-2027', 'TARIFA 23-27 REPSOL', 'terminado',          '2026-05-09', '2026-05-21', 'REPSOL terminado sin pedido',                     'Equipo REPSOL'),
('REPSOL',620004, 'DISENO',           'NPV',       @repsol_estacion_2, 'REPSOL-2023-2027', 'TARIFA 23-27 REPSOL', 'pendiente_facturar', '2026-05-10', '2026-05-22', 'REPSOL pedido para validar lineas',                'Equipo REPSOL'),
('REPSOL',620005, 'DISENO',           'REFORMA',   @repsol_estacion_1, 'REPSOL-2023-2027', 'TARIFA 23-27 REPSOL', 'pendiente_facturar', '2026-05-11', '2026-05-23', 'REPSOL facturacion parcial',                       'Equipo REPSOL'),
('REPSOL',620006, 'DISENO',           'NPV',       @repsol_estacion_2, 'REPSOL-2023-2027', 'TARIFA 23-27 REPSOL', 'cancelado',          '2026-05-12', NULL,         'REPSOL cancelado visible al final',               'Equipo REPSOL'),
('OTROS', 630001, 'OTROS',            'OBRA',      'OTR-001',          'OTROS-DEMO',         'Tarifario OTROS DEMO', 'pendiente_facturar', '2026-05-07', '2026-05-20', 'OTROS pedido demo con lineas',                     'Cliente OTROS'),
('OTROS', 630002, 'OTROS',            'MTO',       'OTR-002',          'OTROS-DEMO',         'Tarifario OTROS DEMO', 'en_curso',           '2026-05-09', NULL,         'OTROS alta manual sin pedido',                    'Cliente OTROS'),
('OTROS', 630003, 'OTROS',            'OBRA',      'OTR-001',          'OTROS-DEMO',         'Tarifario OTROS DEMO', 'terminado',          '2026-05-12', '2026-05-22', 'OTROS terminado sin pedido',                      'Cliente OTROS');

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
    descripcion_trabajo,
    fecha_encargo,
    fecha_terminacion,
    observaciones,
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
    CONCAT('DEMO-', seed.numero_trabajo),
    es.codigo_estacion,
    seed.descripcion_trabajo,
    seed.fecha_encargo,
    seed.fecha_terminacion,
    CONCAT(@marker, ' ', seed.descripcion_trabajo),
    'DEMO',
    seed.responsable_cliente,
    seed.estado_trabajo,
    FALSE,
    @now,
    @now
FROM tmp_demo_trabajos seed
JOIN contextos_cliente c ON c.codigo = seed.contexto_codigo
JOIN empresas ec ON ec.id_contexto = c.id_contexto
    AND ec.nombre = CASE seed.contexto_codigo
        WHEN 'MOEVE' THEN 'MOEVE'
        WHEN 'REPSOL' THEN 'REPSOL'
        ELSE 'OTROS DEMO'
    END
JOIN estaciones_servicio es
    ON es.id_contexto = c.id_contexto
   AND es.codigo_estacion = seed.estacion_codigo
JOIN tipos_documento td
    ON td.id_contexto = c.id_contexto
   AND td.codigo = seed.tipo_documento_codigo
JOIN tipos_trabajo tt
    ON tt.id_contexto = c.id_contexto
   AND tt.id_tipo_documento = td.id_tipo_documento
   AND tt.codigo = seed.tipo_trabajo_codigo
JOIN contratos ct
    ON ct.id_contexto = c.id_contexto
   AND ct.codigo_contrato = seed.contrato_codigo
JOIN tarifarios tf
    ON tf.id_contexto = c.id_contexto
   AND tf.id_contrato = ct.id_contrato
   AND tf.nombre = seed.tarifario_nombre
LEFT JOIN (
    SELECT id_contexto, MIN(id_usuario) AS id_usuario
    FROM usuarios
    WHERE activo = TRUE
    GROUP BY id_contexto
) ux ON ux.id_contexto = c.id_contexto;

-- ============================================================
-- 8) Pedidos y lineas demo
-- ============================================================
DROP TEMPORARY TABLE IF EXISTS tmp_demo_pedidos;
CREATE TEMPORARY TABLE tmp_demo_pedidos (
    contexto_codigo VARCHAR(20) NOT NULL,
    numero_trabajo INT UNSIGNED NOT NULL,
    tipo_documento_codigo VARCHAR(30) NOT NULL,
    numero_pedido VARCHAR(100) NOT NULL,
    fecha_solicitud DATE NULL,
    fecha_recepcion DATE NULL,
    PRIMARY KEY (contexto_codigo, numero_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tmp_demo_pedidos VALUES
('MOEVE', 610001, 'CONTROL_TRABAJOS', 'DEMO-MOE-LA-SENYERA', '2026-05-20', '2026-05-21'),
('MOEVE', 610004, 'CONTROL_TRABAJOS', 'DEMO-MOE-CIERRE',     '2026-05-22', '2026-05-23'),
('MOEVE', 610005, 'CONTROL_TRABAJOS', 'DEMO-MOE-PENDIENTE',  '2026-05-23', NULL),
('REPSOL',620001, 'DISENO',           'DEMO-REP-PENDIENTE',  '2026-05-20', NULL),
('REPSOL',620004, 'DISENO',           'DEMO-REP-LINEAS',     '2026-05-22', '2026-05-23'),
('REPSOL',620005, 'DISENO',           'DEMO-REP-PARCIAL',    '2026-05-23', '2026-05-24'),
('OTROS', 630001, 'OTROS',            'DEMO-OTR-001',        '2026-05-20', NULL);

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
    IF(seed.fecha_recepcion IS NULL, 'solicitado', 'recibido'),
    IF(seed.fecha_recepcion IS NULL, FALSE, TRUE),
    FALSE,
    FALSE,
    CONCAT(@marker, ' Pedido ', seed.numero_pedido),
    @now,
    @now
FROM tmp_demo_pedidos seed
JOIN contextos_cliente c ON c.codigo = seed.contexto_codigo
JOIN tipos_documento td
    ON td.id_contexto = c.id_contexto
   AND td.codigo = seed.tipo_documento_codigo
JOIN trabajos tr
    ON tr.id_contexto = c.id_contexto
   AND tr.id_tipo_documento = td.id_tipo_documento
   AND tr.numero_trabajo = seed.numero_trabajo;

DROP TEMPORARY TABLE IF EXISTS tmp_demo_items;
CREATE TEMPORARY TABLE tmp_demo_items (
    contexto_codigo VARCHAR(20) NOT NULL,
    numero_pedido VARCHAR(100) NOT NULL,
    codigo_tarifa VARCHAR(30) NOT NULL,
    descripcion_servicio VARCHAR(255) NOT NULL,
    cantidad DECIMAL(14,3) NOT NULL,
    precio_unitario DECIMAL(14,2) NOT NULL,
    PRIMARY KEY (contexto_codigo, numero_pedido, codigo_tarifa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tmp_demo_items VALUES
('MOEVE', 'DEMO-MOE-LA-SENYERA', '165052', 'SERVICIO DIRECCION DE PROYECTO', 40.000, 40.00),
('MOEVE', 'DEMO-MOE-LA-SENYERA', '165023', 'TOMA DE DATOS SIMPLE', 3.000, 510.00),
('MOEVE', 'DEMO-MOE-LA-SENYERA', '181871', 'PJT LEGA. ACTI./INST. PJT CMPT INSTALA.', 1.000, 2550.00),
('MOEVE', 'DEMO-MOE-LA-SENYERA', '165027', 'PROYECTO OFICIAL. MEDIO', 1.000, 1250.00),
('MOEVE', 'DEMO-MOE-LA-SENYERA', '165031', 'INGENIERIA DE DETALLE. MEDIO', 1.000, 2000.00),
('MOEVE', 'DEMO-MOE-LA-SENYERA', '181825', 'CFO PROYECTO COMPLEJO', 1.000, 790.00),
('MOEVE', 'DEMO-MOE-LA-SENYERA', '181874', 'OBT. AYTO DE LICENCIA PJT CMPT INSTALA.', 2.000, 1825.00),
('MOEVE', 'DEMO-MOE-LA-SENYERA', '165042', 'ESTUDIO OBTENCION CERTIF. COMP URBANIST', 1.000, 570.00),
('MOEVE', 'DEMO-MOE-CIERRE',     '165023', 'TOMA DE DATOS SIMPLE', 1.000, 510.00),
('MOEVE', 'DEMO-MOE-PENDIENTE',  '165027', 'PROYECTO OFICIAL. MEDIO', 1.000, 1250.00),
('REPSOL','DEMO-REP-PENDIENTE',  '3012735', 'Estudio de implantacion / reforma', 1.000, 1123.54),
('REPSOL','DEMO-REP-LINEAS',     '3012744', 'Implantacion lavados', 1.000, 460.79),
('REPSOL','DEMO-REP-PARCIAL',    '3012704', 'Alternativa con edificio no normalizado', 1.000, 493.40),
('REPSOL','DEMO-REP-PARCIAL',    '3012796', 'Director de proyecto', 2.000, 45.99),
('OTROS', 'DEMO-OTR-001',        'OTR-001', 'Visita tecnica OTROS', 1.000, 180.00);

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
FROM tmp_demo_items seed
JOIN contextos_cliente c ON c.codigo = seed.contexto_codigo
JOIN pedidos p
    ON p.id_contexto = c.id_contexto
   AND p.numero_pedido = seed.numero_pedido
JOIN tarifario_lineas tl
    ON tl.id_contexto = c.id_contexto
   AND tl.id_tarifario = p.id_tarifario
   AND tl.codigo_tarifa = seed.codigo_tarifa;

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
    p.tiene_mas_de_1_item = IF(agg.total_items > 1, TRUE, FALSE),
    p.updated_at = @now;

-- ============================================================
-- 9) Facturacion minima: una completa y una parcial
-- ============================================================
INSERT INTO facturas (
    id_contexto,
    id_trabajo,
    id_contrato,
    id_empresa_cliente,
    id_empresa_facturadora,
    numero_factura,
    serie,
    orden_factura,
    fecha_solicitud,
    fecha_emision,
    importe,
    base_imponible,
    iva,
    total,
    estado,
    autofactura,
    sociedad,
    observaciones,
    created_at,
    updated_at
)
SELECT
    p.id_contexto,
    tr.id_trabajo,
    tr.id_contrato,
    tr.id_empresa_cliente,
    CASE p.id_contexto
        WHEN @ctx_moeve THEN @empresa_moeve
        WHEN @ctx_repsol THEN @empresa_repsol
        ELSE @empresa_otros
    END,
    seed.numero_factura,
    'DEMO',
    1,
    p.fecha_solicitud,
    seed.fecha_emision,
    0.00,
    0.00,
    0.00,
    0.00,
    'emitida',
    FALSE,
    seed.sociedad,
    CONCAT(@marker, ' Factura ', seed.numero_factura),
    @now,
    @now
FROM (
    SELECT 'DEMO-MOE-CIERRE' AS numero_pedido, 'DEMO-FAC-MOE-CIERRE' AS numero_factura, '2026-05-24' AS fecha_emision, 'MOEVE' AS sociedad
    UNION ALL
    SELECT 'DEMO-REP-PARCIAL', 'DEMO-FAC-REP-PARCIAL', '2026-05-25', 'REPSOL'
) seed
JOIN pedidos p ON p.numero_pedido = seed.numero_pedido
JOIN trabajos tr ON tr.id_trabajo = p.id_trabajo;

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
    CONCAT(@marker, ' Facturacion controlada ', pi.codigo_servicio),
    @now,
    @now
FROM (
    SELECT 'DEMO-FAC-MOE-CIERRE' AS numero_factura, '165023' AS codigo_tarifa, 1.000 AS unidades_facturadas, 510.00 AS importe_facturado
    UNION ALL
    SELECT 'DEMO-FAC-REP-PARCIAL', '3012704', 1.000, 200.00
) seed
JOIN facturas f ON f.numero_factura = seed.numero_factura
JOIN pedidos p ON p.id_trabajo = f.id_trabajo
JOIN pedido_items pi
    ON pi.id_pedido = p.id_pedido
   AND pi.codigo_servicio = seed.codigo_tarifa;

UPDATE facturas f
JOIN (
    SELECT
        fi.id_factura,
        ROUND(SUM(fi.importe_facturado), 2) AS base_total
    FROM factura_items fi
    GROUP BY fi.id_factura
) agg ON agg.id_factura = f.id_factura
SET
    f.importe = agg.base_total,
    f.base_imponible = agg.base_total,
    f.iva = ROUND(agg.base_total * 0.21, 2),
    f.total = ROUND(agg.base_total * 1.21, 2),
    f.updated_at = @now;

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
    p.facturado_completo = IF(COALESCE(agg.total_facturado, 0.00) >= p.importe_pedido AND p.importe_pedido > 0, TRUE, FALSE),
    p.estado = CASE
        WHEN COALESCE(agg.total_facturado, 0.00) >= p.importe_pedido AND p.importe_pedido > 0 THEN 'facturado'
        WHEN COALESCE(agg.total_facturado, 0.00) > 0 THEN 'facturado_parcial'
        WHEN p.fecha_recepcion IS NOT NULL THEN 'recibido'
        ELSE 'solicitado'
    END,
    p.updated_at = @now;

UPDATE trabajos tr
LEFT JOIN (
    SELECT
        p.id_trabajo,
        COUNT(*) AS pedidos_activos,
        SUM(CASE WHEN p.facturado_completo = TRUE THEN 1 ELSE 0 END) AS pedidos_facturados
    FROM pedidos p
    WHERE p.estado NOT IN ('cancelado', 'anulado')
    GROUP BY p.id_trabajo
) agg ON agg.id_trabajo = tr.id_trabajo
SET tr.estado = CASE
    WHEN tr.estado = 'cancelado' THEN 'cancelado'
    WHEN tr.fecha_terminacion IS NULL THEN 'en_curso'
    WHEN COALESCE(agg.pedidos_activos, 0) = 0 THEN 'terminado'
    WHEN agg.pedidos_facturados = agg.pedidos_activos THEN 'facturado'
    ELSE 'pendiente_facturar'
END;

COMMIT;

-- ============================================================
-- 10) Diagnostico posterior visible en consola
-- ============================================================
SELECT 'usuarios' AS tabla, COUNT(*) AS total FROM usuarios
UNION ALL SELECT 'empresas', COUNT(*) FROM empresas
UNION ALL SELECT 'estaciones_servicio', COUNT(*) FROM estaciones_servicio
UNION ALL SELECT 'contratos', COUNT(*) FROM contratos
UNION ALL SELECT 'tarifarios', COUNT(*) FROM tarifarios
UNION ALL SELECT 'tarifario_lineas', COUNT(*) FROM tarifario_lineas
UNION ALL SELECT 'trabajos', COUNT(*) FROM trabajos
UNION ALL SELECT 'pedidos', COUNT(*) FROM pedidos
UNION ALL SELECT 'pedido_items', COUNT(*) FROM pedido_items
UNION ALL SELECT 'facturas', COUNT(*) FROM facturas
UNION ALL SELECT 'factura_items', COUNT(*) FROM factura_items
UNION ALL SELECT 'importaciones', COUNT(*) FROM importaciones
UNION ALL SELECT 'importacion_filas', COUNT(*) FROM importacion_filas;

SELECT
    c.codigo AS contexto,
    COUNT(DISTINCT es.id_estacion_servicio) AS estaciones,
    COUNT(DISTINCT tr.id_trabajo) AS trabajos,
    COUNT(DISTINCT p.id_pedido) AS pedidos,
    COUNT(DISTINCT f.id_factura) AS facturas
FROM contextos_cliente c
LEFT JOIN estaciones_servicio es ON es.id_contexto = c.id_contexto
LEFT JOIN trabajos tr ON tr.id_contexto = c.id_contexto
LEFT JOIN pedidos p ON p.id_contexto = c.id_contexto
LEFT JOIN facturas f ON f.id_contexto = c.id_contexto
GROUP BY c.codigo
ORDER BY c.codigo;

SELECT
    c.codigo AS contexto,
    (SELECT COUNT(*) FROM contratos co WHERE co.id_contexto = c.id_contexto) AS contratos,
    (SELECT COUNT(*) FROM tarifarios t WHERE t.id_contexto = c.id_contexto) AS tarifarios,
    (SELECT COUNT(*) FROM tarifarios t WHERE t.id_contexto = c.id_contexto AND t.activo = TRUE) AS tarifarios_activos,
    (SELECT COUNT(*) FROM tarifarios t WHERE t.id_contexto = c.id_contexto AND t.es_predeterminado = TRUE) AS predeterminados,
    (SELECT COUNT(*) FROM tarifario_lineas tl WHERE tl.id_contexto = c.id_contexto) AS lineas
FROM contextos_cliente c
ORDER BY c.codigo;
