-- ============================================================
-- ERP CIETE - Limpieza de muestra local B2 validacion flujo diario
-- Fecha: 2026-05-19
-- Marca: [muestra-b2-validacion-2026-05-19]
-- ============================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

START TRANSACTION;

SET @marker := '[muestra-b2-validacion-2026-05-19]';
SET @ctx_otros := (
    SELECT id_contexto
    FROM contextos_cliente
    WHERE codigo = 'OTROS'
    LIMIT 1
);

-- ------------------------------------------------------------
-- Eliminar dependencias facturacion/pedidos/trabajos
-- ------------------------------------------------------------
DELETE fi
FROM factura_items fi
JOIN facturas f ON f.id_factura = fi.id_factura
WHERE f.id_contexto = @ctx_otros
  AND (
      f.numero_factura LIKE 'B2-OTR-FAC-%'
      OR f.observaciones LIKE CONCAT('%', @marker, '%')
  );

DELETE FROM facturas
WHERE id_contexto = @ctx_otros
  AND (
      numero_factura LIKE 'B2-OTR-FAC-%'
      OR observaciones LIKE CONCAT('%', @marker, '%')
  );

DELETE pi
FROM pedido_items pi
JOIN pedidos p ON p.id_pedido = pi.id_pedido
WHERE p.id_contexto = @ctx_otros
  AND (
      p.numero_pedido LIKE 'B2-OTR-PED-%'
      OR p.observaciones LIKE CONCAT('%', @marker, '%')
  );

DELETE FROM pedidos
WHERE id_contexto = @ctx_otros
  AND (
      numero_pedido LIKE 'B2-OTR-PED-%'
      OR observaciones LIKE CONCAT('%', @marker, '%')
  );

DELETE FROM trabajos
WHERE id_contexto = @ctx_otros
  AND (
      numero_trabajo_operativo LIKE 'B2-OTR-%'
      OR numero_aviso LIKE 'B2-OTR-AV-%'
      OR orden_mantenimiento LIKE 'B2-OTR-OM-%'
      OR observaciones LIKE CONCAT('%', @marker, '%')
  );

-- ------------------------------------------------------------
-- Eliminar maestros de muestra (orden FK seguro)
-- ------------------------------------------------------------
DELETE FROM contrato_empresas_facturadoras
WHERE id_contexto = @ctx_otros
  AND observaciones LIKE CONCAT('%', @marker, '%');

DELETE FROM tarifario_lineas
WHERE id_contexto = @ctx_otros
  AND (
      codigo_tarifa LIKE 'B2-OTR-SVC-%'
      OR descripcion LIKE CONCAT('%', @marker, '%')
  );

DELETE FROM tarifarios
WHERE id_contexto = @ctx_otros
  AND (
      nombre LIKE 'B2 Tarifario OTROS %'
      OR observaciones LIKE CONCAT('%', @marker, '%')
  );

DELETE FROM contratos
WHERE id_contexto = @ctx_otros
  AND (
      codigo_contrato LIKE 'B2-OTR-%'
      OR observaciones LIKE CONCAT('%', @marker, '%')
  );

DELETE FROM estaciones_servicio
WHERE id_contexto = @ctx_otros
  AND (
      codigo_estacion LIKE 'B2-OTR-%'
      OR observaciones LIKE CONCAT('%', @marker, '%')
  );

DELETE FROM empresas
WHERE id_contexto = @ctx_otros
  AND (
      nombre LIKE 'B2 OTROS %'
      OR observaciones LIKE CONCAT('%', @marker, '%')
  );

COMMIT;
