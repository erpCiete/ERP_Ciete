-- ============================================================
-- ERP CIETE - Muestra local controlada B2 validacion flujo diario
-- Fecha: 2026-05-19
-- Alcance: solo entorno local abaco_ciete, reversible con script delete
-- Marca: [muestra-b2-validacion-2026-05-19]
-- ============================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

START TRANSACTION;

SET @now := NOW();
SET @marker := '[muestra-b2-validacion-2026-05-19]';

SET @ctx_otros := (
    SELECT id_contexto
    FROM contextos_cliente
    WHERE codigo = 'OTROS'
    LIMIT 1
);

SET @doc_otros := (
    SELECT id_tipo_documento
    FROM tipos_documento
    WHERE id_contexto = @ctx_otros
      AND activo = 1
    ORDER BY id_tipo_documento
    LIMIT 1
);

SET @tt_obra := (
    SELECT id_tipo_trabajo
    FROM tipos_trabajo
    WHERE id_contexto = @ctx_otros
      AND activo = 1
      AND codigo = 'OBRA'
    ORDER BY id_tipo_trabajo
    LIMIT 1
);

SET @tt_mto := (
    SELECT id_tipo_trabajo
    FROM tipos_trabajo
    WHERE id_contexto = @ctx_otros
      AND activo = 1
      AND codigo = 'MTO'
    ORDER BY id_tipo_trabajo
    LIMIT 1
);

SET @tt_fallback := (
    SELECT id_tipo_trabajo
    FROM tipos_trabajo
    WHERE id_contexto = @ctx_otros
      AND activo = 1
    ORDER BY id_tipo_trabajo
    LIMIT 1
);

SET @id_tipo_trabajo_obra := COALESCE(@tt_obra, @tt_fallback);
SET @id_tipo_trabajo_mto := COALESCE(@tt_mto, @tt_fallback);

SET @id_responsable := (
    SELECT id_usuario
    FROM usuarios
    WHERE activo = 1
    ORDER BY (id_contexto = @ctx_otros) DESC, id_usuario
    LIMIT 1
);

SET @id_unidad_ud := (
    SELECT id_unidad
    FROM unidades
    WHERE abreviatura = 'ud'
    LIMIT 1
);

-- ------------------------------------------------------------
-- Empresas OTROS de muestra
-- ------------------------------------------------------------
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
    @ctx_otros,
    seed.nombre,
    seed.nombre,
    seed.razon_social,
    seed.cif,
    'cliente',
    CONCAT(@marker, ' ', seed.obs),
    1,
    @now,
    @now
FROM (
    SELECT 'B2 OTROS CLIENTE' AS nombre, 'B2 OTROS CLIENTE SL' AS razon_social, 'B90000119' AS cif, 'Cliente OTROS para pruebas B2' AS obs
    UNION ALL
    SELECT 'B2 OTROS FACTURADORA OK', 'B2 OTROS FACTURADORA OK SL', 'B90000120', 'Sociedad facturadora OTROS valida para pruebas B2'
    UNION ALL
    SELECT 'B2 OTROS FACTURADORA SIN CIF', 'B2 OTROS FACTURADORA SIN CIF SL', NULL, 'Sociedad OTROS sin CIF para caso de bloqueo contable'
) AS seed
WHERE @ctx_otros IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM empresas e
      WHERE e.id_contexto = @ctx_otros
        AND e.nombre = seed.nombre
  );

SET @empresa_cliente_otros := (
    SELECT id_empresa
    FROM empresas
    WHERE id_contexto = @ctx_otros
      AND nombre = 'B2 OTROS CLIENTE'
    LIMIT 1
);

SET @empresa_fact_ok := (
    SELECT id_empresa
    FROM empresas
    WHERE id_contexto = @ctx_otros
      AND nombre = 'B2 OTROS FACTURADORA OK'
    LIMIT 1
);

SET @empresa_fact_sin_cif := (
    SELECT id_empresa
    FROM empresas
    WHERE id_contexto = @ctx_otros
      AND nombre = 'B2 OTROS FACTURADORA SIN CIF'
    LIMIT 1
);

-- ------------------------------------------------------------
-- Estacion OTROS de muestra
-- ------------------------------------------------------------
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
    @ctx_otros,
    @empresa_cliente_otros,
    'B2-OTR-001',
    'B2 Estacion OTROS Validacion',
    'Calle B2 OTROS 1',
    '28080',
    'Madrid',
    'Madrid',
    'Espana',
    'activa',
    CONCAT(@marker, ' Estacion para pruebas B2 OTROS.'),
    1,
    @now,
    @now
WHERE @ctx_otros IS NOT NULL
  AND @empresa_cliente_otros IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM estaciones_servicio es
      WHERE es.id_contexto = @ctx_otros
        AND es.codigo_estacion = 'B2-OTR-001'
  );

SET @estacion_otros := (
    SELECT id_estacion_servicio
    FROM estaciones_servicio
    WHERE id_contexto = @ctx_otros
      AND codigo_estacion = 'B2-OTR-001'
    LIMIT 1
);

-- ------------------------------------------------------------
-- Contratos y tarifarios OTROS de muestra
-- ------------------------------------------------------------
INSERT INTO contratos (
    id_contexto,
    id_empresa_cliente,
    codigo_contrato,
    nombre,
    tipo,
    fecha_inicio,
    estado,
    observaciones,
    activo,
    created_at,
    updated_at
)
SELECT
    @ctx_otros,
    @empresa_cliente_otros,
    seed.codigo,
    seed.nombre,
    'marco',
    '2026-01-01',
    'vigente',
    CONCAT(@marker, ' ', seed.obs),
    1,
    @now,
    @now
FROM (
    SELECT 'B2-OTR-OK' AS codigo, 'B2 Contrato OTROS OK' AS nombre, 'Contrato OTROS completo para flujo valido.' AS obs
    UNION ALL
    SELECT 'B2-OTR-NOSOC', 'B2 Contrato OTROS sin sociedad', 'Contrato OTROS sin sociedad facturadora asociada.'
    UNION ALL
    SELECT 'B2-OTR-NOCIF', 'B2 Contrato OTROS con sociedad sin CIF', 'Contrato OTROS con sociedad sin CIF para bloqueo contable.'
) AS seed
WHERE @ctx_otros IS NOT NULL
  AND @empresa_cliente_otros IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM contratos c
      WHERE c.id_contexto = @ctx_otros
        AND c.codigo_contrato = seed.codigo
  );

SET @contrato_otros_ok := (
    SELECT id_contrato FROM contratos WHERE id_contexto = @ctx_otros AND codigo_contrato = 'B2-OTR-OK' LIMIT 1
);
SET @contrato_otros_nosoc := (
    SELECT id_contrato FROM contratos WHERE id_contexto = @ctx_otros AND codigo_contrato = 'B2-OTR-NOSOC' LIMIT 1
);
SET @contrato_otros_nocif := (
    SELECT id_contrato FROM contratos WHERE id_contexto = @ctx_otros AND codigo_contrato = 'B2-OTR-NOCIF' LIMIT 1
);

INSERT INTO tarifarios (
    id_contexto,
    id_contrato,
    nombre,
    version,
    fecha_inicio_vigencia,
    factor_multiplicador,
    moneda,
    observaciones,
    activo,
    created_at,
    updated_at
)
SELECT
    @ctx_otros,
    seed.id_contrato,
    seed.nombre,
    '2026-05-B2',
    '2026-01-01',
    1.0000,
    'EUR',
    CONCAT(@marker, ' ', seed.obs),
    1,
    @now,
    @now
FROM (
    SELECT @contrato_otros_ok AS id_contrato, 'B2 Tarifario OTROS OK' AS nombre, 'Tarifario OTROS completo.' AS obs
    UNION ALL
    SELECT @contrato_otros_nosoc, 'B2 Tarifario OTROS NOSOC', 'Tarifario OTROS sin sociedad asociada.'
    UNION ALL
    SELECT @contrato_otros_nocif, 'B2 Tarifario OTROS NOCIF', 'Tarifario OTROS con sociedad sin CIF.'
) AS seed
WHERE seed.id_contrato IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM tarifarios t
      WHERE t.id_contrato = seed.id_contrato
        AND t.nombre = seed.nombre
        AND COALESCE(t.version, '') = '2026-05-B2'
  );

SET @tarifario_otros_ok := (
    SELECT id_tarifario FROM tarifarios WHERE id_contrato = @contrato_otros_ok AND nombre = 'B2 Tarifario OTROS OK' AND COALESCE(version,'') = '2026-05-B2' LIMIT 1
);
SET @tarifario_otros_nosoc := (
    SELECT id_tarifario FROM tarifarios WHERE id_contrato = @contrato_otros_nosoc AND nombre = 'B2 Tarifario OTROS NOSOC' AND COALESCE(version,'') = '2026-05-B2' LIMIT 1
);
SET @tarifario_otros_nocif := (
    SELECT id_tarifario FROM tarifarios WHERE id_contrato = @contrato_otros_nocif AND nombre = 'B2 Tarifario OTROS NOCIF' AND COALESCE(version,'') = '2026-05-B2' LIMIT 1
);

INSERT INTO tarifario_lineas (
    id_contexto,
    id_tarifario,
    codigo_tarifa,
    grupo,
    actuacion,
    descripcion,
    tarifa_base,
    tarifa_aplicada,
    id_unidad,
    activo,
    created_at,
    updated_at
)
SELECT
    @ctx_otros,
    seed.id_tarifario,
    seed.codigo_tarifa,
    'B2-VALIDACION',
    seed.actuacion,
    CONCAT(@marker, ' ', seed.descripcion),
    seed.precio,
    seed.precio,
    @id_unidad_ud,
    1,
    @now,
    @now
FROM (
    SELECT @tarifario_otros_ok AS id_tarifario, 'B2-OTR-SVC-OK' AS codigo_tarifa, 'Servicio OTROS completo' AS actuacion, 'Linea para flujo completo OTROS.' AS descripcion, 250.00 AS precio
    UNION ALL
    SELECT @tarifario_otros_nosoc, 'B2-OTR-SVC-NOSOC', 'Servicio OTROS sin sociedad', 'Linea para bloqueo por falta de sociedad.', 180.00
    UNION ALL
    SELECT @tarifario_otros_nocif, 'B2-OTR-SVC-NOCIF', 'Servicio OTROS sociedad sin CIF', 'Linea para bloqueo por CIF ausente.', 195.00
) AS seed
WHERE seed.id_tarifario IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM tarifario_lineas tl
      WHERE tl.id_tarifario = seed.id_tarifario
        AND tl.codigo_tarifa = seed.codigo_tarifa
  );

SET @linea_otros_ok := (
    SELECT id_tarifario_linea FROM tarifario_lineas WHERE id_tarifario = @tarifario_otros_ok AND codigo_tarifa = 'B2-OTR-SVC-OK' LIMIT 1
);
SET @linea_otros_nosoc := (
    SELECT id_tarifario_linea FROM tarifario_lineas WHERE id_tarifario = @tarifario_otros_nosoc AND codigo_tarifa = 'B2-OTR-SVC-NOSOC' LIMIT 1
);
SET @linea_otros_nocif := (
    SELECT id_tarifario_linea FROM tarifario_lineas WHERE id_tarifario = @tarifario_otros_nocif AND codigo_tarifa = 'B2-OTR-SVC-NOCIF' LIMIT 1
);

-- Relacion contrato-sociedad: OK y NOCIF. El contrato NOSOC se deja sin relacion adrede.
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
    seed.id_contrato,
    seed.id_empresa,
    @ctx_otros,
    1,
    CONCAT(@marker, ' ', seed.obs),
    @now,
    @now
FROM (
    SELECT @contrato_otros_ok AS id_contrato, @empresa_fact_ok AS id_empresa, 'Relacion valida OTROS OK.' AS obs
    UNION ALL
    SELECT @contrato_otros_nocif, @empresa_fact_sin_cif, 'Relacion OTROS con CIF ausente para bloqueo.'
) AS seed
WHERE seed.id_contrato IS NOT NULL
  AND seed.id_empresa IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM contrato_empresas_facturadoras cef
      WHERE cef.id_contrato = seed.id_contrato
        AND cef.id_empresa = seed.id_empresa
  );

-- ------------------------------------------------------------
-- Trabajos de muestra OTROS
-- ------------------------------------------------------------
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
    @ctx_otros,
    @empresa_cliente_otros,
    @estacion_otros,
    @doc_otros,
    seed.id_tipo_trabajo,
    seed.id_contrato,
    seed.id_tarifario,
    @id_responsable,
    seed.numero_trabajo,
    seed.numero_operativo,
    'B2-OTR-001',
    seed.descripcion,
    seed.fecha_encargo,
    seed.fecha_terminacion,
    CONCAT(@marker, ' ', seed.observaciones),
    seed.numero_aviso,
    seed.orden_mantenimiento,
    'B2',
    'Validacion B2',
    seed.estado,
    0,
    @now,
    @now
FROM (
    SELECT 980001 AS numero_trabajo, 'B2-OTR-980001' AS numero_operativo, @id_tipo_trabajo_obra AS id_tipo_trabajo, @contrato_otros_ok AS id_contrato, @tarifario_otros_ok AS id_tarifario, 'OTROS completo: trabajo->pedido->factura' AS descripcion, '2026-05-18' AS fecha_encargo, '2026-05-19' AS fecha_terminacion, 'Caso 10 OTROS completo minimo.' AS observaciones, 'B2-OTR-AV-001' AS numero_aviso, 'B2-OTR-OM-001' AS orden_mantenimiento, 'facturado' AS estado
    UNION ALL
    SELECT 980002, 'B2-OTR-980002', @id_tipo_trabajo_mto, @contrato_otros_nosoc, @tarifario_otros_nosoc, 'OTROS sin sociedad asociada para facturacion', '2026-05-18', '2026-05-19', 'Caso 11 OTROS sin maestro suficiente (sin sociedad).', 'B2-OTR-AV-002', 'B2-OTR-OM-002', 'terminado'
    UNION ALL
    SELECT 980003, 'B2-OTR-980003', @id_tipo_trabajo_obra, @contrato_otros_ok, @tarifario_otros_ok, 'OTROS editable para conflicto por celda', '2026-05-19', NULL, 'Caso 19 conflicto celda (usar dos usuarios).', 'B2-OTR-AV-003', 'B2-OTR-OM-003', 'en_curso'
    UNION ALL
    SELECT 980004, 'B2-OTR-980004', @id_tipo_trabajo_mto, @contrato_otros_nocif, @tarifario_otros_nocif, 'OTROS con sociedad sin CIF para bloqueo contable', '2026-05-18', '2026-05-19', 'Caso 12 bloqueo contable sin CIF.', 'B2-OTR-AV-004', 'B2-OTR-OM-004', 'terminado'
) AS seed
WHERE @ctx_otros IS NOT NULL
  AND @empresa_cliente_otros IS NOT NULL
  AND @estacion_otros IS NOT NULL
  AND @doc_otros IS NOT NULL
  AND seed.id_tipo_trabajo IS NOT NULL
  AND seed.id_contrato IS NOT NULL
  AND seed.id_tarifario IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM trabajos t
      WHERE t.id_contexto = @ctx_otros
        AND t.numero_trabajo = seed.numero_trabajo
  );

SET @trab_otr_ok := (
    SELECT id_trabajo FROM trabajos WHERE id_contexto = @ctx_otros AND numero_trabajo = 980001 LIMIT 1
);
SET @trab_otr_nosoc := (
    SELECT id_trabajo FROM trabajos WHERE id_contexto = @ctx_otros AND numero_trabajo = 980002 LIMIT 1
);
SET @trab_otr_conflict := (
    SELECT id_trabajo FROM trabajos WHERE id_contexto = @ctx_otros AND numero_trabajo = 980003 LIMIT 1
);
SET @trab_otr_nocif := (
    SELECT id_trabajo FROM trabajos WHERE id_contexto = @ctx_otros AND numero_trabajo = 980004 LIMIT 1
);

-- ------------------------------------------------------------
-- Pedidos e items de muestra OTROS
-- ------------------------------------------------------------
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
    @ctx_otros,
    seed.id_trabajo,
    seed.id_tarifario,
    seed.numero_pedido,
    seed.fecha_solicitud,
    seed.fecha_recepcion,
    0.00,
    0.00,
    0.00,
    1.000,
    1.000,
    seed.estado,
    0,
    0,
    0,
    CONCAT(@marker, ' Pedido ', seed.numero_pedido),
    @now,
    @now
FROM (
    SELECT @trab_otr_ok AS id_trabajo, @tarifario_otros_ok AS id_tarifario, 'B2-OTR-PED-001' AS numero_pedido, '2026-05-18' AS fecha_solicitud, '2026-05-19' AS fecha_recepcion, 'facturado' AS estado
    UNION ALL
    SELECT @trab_otr_nosoc, @tarifario_otros_nosoc, 'B2-OTR-PED-002', '2026-05-18', NULL, 'recibido'
    UNION ALL
    SELECT @trab_otr_nocif, @tarifario_otros_nocif, 'B2-OTR-PED-003', '2026-05-18', NULL, 'recibido'
) AS seed
WHERE seed.id_trabajo IS NOT NULL
  AND seed.id_tarifario IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM pedidos p
      WHERE p.id_contexto = @ctx_otros
        AND p.numero_pedido = seed.numero_pedido
  );

SET @ped_otr_ok := (
    SELECT id_pedido FROM pedidos WHERE id_contexto = @ctx_otros AND numero_pedido = 'B2-OTR-PED-001' LIMIT 1
);
SET @ped_otr_nosoc := (
    SELECT id_pedido FROM pedidos WHERE id_contexto = @ctx_otros AND numero_pedido = 'B2-OTR-PED-002' LIMIT 1
);
SET @ped_otr_nocif := (
    SELECT id_pedido FROM pedidos WHERE id_contexto = @ctx_otros AND numero_pedido = 'B2-OTR-PED-003' LIMIT 1
);

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
    @ctx_otros,
    seed.id_pedido,
    seed.id_tarifario_linea,
    seed.codigo_servicio,
    seed.codigo_servicio,
    seed.descripcion,
    seed.precio,
    1.000,
    seed.precio,
    @now,
    @now
FROM (
    SELECT @ped_otr_ok AS id_pedido, @linea_otros_ok AS id_tarifario_linea, 'B2-OTR-SVC-OK' AS codigo_servicio, 'Item OTROS completo' AS descripcion, 250.00 AS precio
    UNION ALL
    SELECT @ped_otr_nosoc, @linea_otros_nosoc, 'B2-OTR-SVC-NOSOC', 'Item OTROS sin sociedad', 180.00
    UNION ALL
    SELECT @ped_otr_nocif, @linea_otros_nocif, 'B2-OTR-SVC-NOCIF', 'Item OTROS sociedad sin CIF', 195.00
) AS seed
WHERE seed.id_pedido IS NOT NULL
  AND seed.id_tarifario_linea IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM pedido_items pi
      WHERE pi.id_pedido = seed.id_pedido
        AND pi.codigo_servicio = seed.codigo_servicio
  );

UPDATE pedidos p
JOIN (
    SELECT id_pedido, SUM(total_linea) AS total_linea, SUM(cantidad) AS total_unidades, COUNT(*) AS total_items
    FROM pedido_items
    GROUP BY id_pedido
) agg ON agg.id_pedido = p.id_pedido
SET
    p.importe_pedido = ROUND(agg.total_linea, 2),
    p.importe_solicitado = ROUND(agg.total_linea, 2),
    p.unidades_pedido = agg.total_unidades,
    p.unidades_solicitadas = agg.total_unidades,
    p.tiene_mas_de_1_item = IF(agg.total_items > 1, 1, 0),
    p.updated_at = @now
WHERE p.id_contexto = @ctx_otros
  AND p.numero_pedido LIKE 'B2-OTR-PED-%';

-- ------------------------------------------------------------
-- Factura completa para caso OTROS OK
-- ------------------------------------------------------------
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
    @ctx_otros,
    @trab_otr_ok,
    @contrato_otros_ok,
    @empresa_cliente_otros,
    @empresa_fact_ok,
    'B2-OTR-FAC-001',
    'B2-OTR-CCP-001',
    'B2',
    1,
    '2026-05-19',
    '2026-05-19',
    '2026-06-19',
    250.00,
    250.00,
    0.00,
    0.00,
    250.00,
    'emitida',
    0,
    'B2 OTROS SOCIEDAD OK',
    CONCAT(@marker, ' Factura completa OTROS caso OK.'),
    @now,
    @now
WHERE @trab_otr_ok IS NOT NULL
  AND @contrato_otros_ok IS NOT NULL
  AND @empresa_cliente_otros IS NOT NULL
  AND @empresa_fact_ok IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM facturas f
      WHERE f.id_contexto = @ctx_otros
        AND f.numero_factura = 'B2-OTR-FAC-001'
  );

SET @fac_otr_ok := (
    SELECT id_factura FROM facturas WHERE id_contexto = @ctx_otros AND numero_factura = 'B2-OTR-FAC-001' LIMIT 1
);

SET @ped_item_otr_ok := (
    SELECT pi.id_pedido_item
    FROM pedido_items pi
    JOIN pedidos p ON p.id_pedido = pi.id_pedido
    WHERE p.id_contexto = @ctx_otros
      AND p.numero_pedido = 'B2-OTR-PED-001'
      AND pi.codigo_servicio = 'B2-OTR-SVC-OK'
    LIMIT 1
);

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
    @fac_otr_ok,
    @ped_item_otr_ok,
    1.000,
    250.00,
    CONCAT(@marker, ' Factura item OTROS OK.'),
    @now,
    @now
WHERE @fac_otr_ok IS NOT NULL
  AND @ped_item_otr_ok IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM factura_items fi
      WHERE fi.id_factura = @fac_otr_ok
        AND fi.id_pedido_item = @ped_item_otr_ok
  );

UPDATE pedidos
SET
    importe_facturado = importe_pedido,
    facturado_completo = 1,
    pedido_completo = 1,
    estado = 'facturado',
    updated_at = @now
WHERE id_contexto = @ctx_otros
  AND numero_pedido = 'B2-OTR-PED-001';

UPDATE trabajos
SET
    estado = 'facturado',
    updated_at = @now
WHERE id_contexto = @ctx_otros
  AND numero_trabajo = 980001;

COMMIT;
