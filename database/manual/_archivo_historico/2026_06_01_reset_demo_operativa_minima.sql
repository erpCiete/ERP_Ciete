-- ============================================================
-- ERP CIETE - Reset local/demo de operativa minima
-- Fecha: 2026-06-01
-- Uso exclusivo: entorno local/desarrollo con backup SQL previo
-- ============================================================
--
-- Ejecucion esperada:
-- mysql --database=abaco_ciete \
--   --init-command="SET @ciete_reset_demo_local_confirmed=1; SET @ciete_reset_demo_backup_confirmed=1" \
--   < database/manual/2026_06_01_reset_demo_operativa_minima.sql
--
-- Conserva maestros, importaciones y audit_log. El backup previo
-- permite recuperar cualquier dato operativo si fuese necesario.

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DROP PROCEDURE IF EXISTS ciete_assert_reset_demo_local;

DELIMITER $$

CREATE PROCEDURE ciete_assert_reset_demo_local()
BEGIN
    IF DATABASE() <> 'abaco_ciete' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Reset cancelado: la base activa no es abaco_ciete.';
    END IF;

    IF COALESCE(@ciete_reset_demo_local_confirmed, 0) <> 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Reset cancelado: falta confirmar entorno local/demo.';
    END IF;

    IF COALESCE(@ciete_reset_demo_backup_confirmed, 0) <> 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Reset cancelado: falta confirmar backup SQL previo.';
    END IF;
END$$

DELIMITER ;

CALL ciete_assert_reset_demo_local();
DROP PROCEDURE ciete_assert_reset_demo_local;

START TRANSACTION;

-- Dependencias operativas de trabajos, pedidos y facturas.
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

-- Regla local/demo: un unico tarifario predeterminado activo por contrato.
-- Se conserva el tarifario activo mas reciente, salvo MOEVE 772, donde se
-- prioriza el tarifario versionado especifico usado por la fuente real.
UPDATE tarifarios
SET es_predeterminado = FALSE;

UPDATE tarifarios target
JOIN (
    SELECT
        t.id_contrato,
        COALESCE(
            MAX(CASE
                WHEN c.codigo = 'MOEVE'
                    AND co.codigo_contrato = '772'
                    AND t.nombre = 'Tarifario 772 MOEVE'
                    AND t.activo = TRUE
                THEN t.id_tarifario
            END),
            MAX(CASE WHEN t.activo = TRUE THEN t.id_tarifario END)
        ) AS id_tarifario_predeterminado
    FROM tarifarios t
    JOIN contratos co ON co.id_contrato = t.id_contrato
    JOIN contextos_cliente c ON c.id_contexto = t.id_contexto
    GROUP BY t.id_contrato
) selected
    ON selected.id_tarifario_predeterminado = target.id_tarifario
SET target.es_predeterminado = TRUE;

-- Base minima MOEVE: lineas reales del caso La Senyera para contrato 772.
-- Fuentes:
-- - docs/02_CLIENTE/materiales/Contrato 772 MOEVE - Tarifario.xlsx
-- - docs/00_FUENTES_CLIENTE/moeve_solicitud_pedidos_2026-05-20/
--   20260506_33450_La Senyera I_Pedido Ciete (1).csv
SET @ctx_moeve := (
    SELECT id_contexto
    FROM contextos_cliente
    WHERE codigo = 'MOEVE'
    LIMIT 1
);

SET @contrato_moeve_772 := (
    SELECT id_contrato
    FROM contratos
    WHERE id_contexto = @ctx_moeve
      AND codigo_contrato = '772'
      AND activo = TRUE
    LIMIT 1
);

SET @tarifario_moeve_772 := (
    SELECT id_tarifario
    FROM tarifarios
    WHERE id_contexto = @ctx_moeve
      AND id_contrato = @contrato_moeve_772
      AND activo = TRUE
    ORDER BY
        CASE WHEN nombre = 'Tarifario 772 MOEVE' THEN 0 ELSE 1 END,
        id_tarifario DESC
    LIMIT 1
);

INSERT INTO tarifario_lineas (
    id_contexto,
    id_tarifario,
    codigo_tarifa,
    actuacion,
    descripcion,
    tarifa_base,
    tarifa_aplicada,
    activo,
    created_at,
    updated_at
)
SELECT
    @ctx_moeve,
    @tarifario_moeve_772,
    source.codigo_tarifa,
    source.actuacion,
    '[demo-controlado] Linea real minima La Senyera para validacion manual/importacion.',
    source.importe,
    source.importe,
    TRUE,
    NOW(),
    NOW()
FROM (
    SELECT '165052' AS codigo_tarifa, 'SERVICIO DIRECCION DE PROYECTO' AS actuacion, 40.00 AS importe
    UNION ALL SELECT '165023', 'TOMA DE DATOS SIMPLE', 510.00
    UNION ALL SELECT '181871', 'PJT LEGA. ACTI./INST. PJT CMPT INSTALA.', 2550.00
    UNION ALL SELECT '165027', 'PROYECTO OFICIAL. MEDIO', 1250.00
    UNION ALL SELECT '165031', 'INGENIERIA DE DETALLE. MEDIO', 2000.00
    UNION ALL SELECT '181825', 'CFO PROYECTO COMPLEJO', 790.00
    UNION ALL SELECT '181874', 'OBT. AYTO DE LICENCIA PJT CMPT INSTALA.', 1825.00
    UNION ALL SELECT '165042', 'ESTUDIO OBTENCION CERTIF. COMP URBANIST', 570.00
) source
WHERE @tarifario_moeve_772 IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM tarifario_lineas existing
      WHERE existing.id_contexto = @ctx_moeve
        AND existing.id_tarifario = @tarifario_moeve_772
        AND existing.codigo_tarifa = source.codigo_tarifa
  );

COMMIT;

-- Diagnostico posterior visible en consola.
SELECT 'trabajos' AS tabla, COUNT(*) AS total FROM trabajos
UNION ALL SELECT 'pedidos', COUNT(*) FROM pedidos
UNION ALL SELECT 'pedido_items', COUNT(*) FROM pedido_items
UNION ALL SELECT 'facturas', COUNT(*) FROM facturas
UNION ALL SELECT 'factura_items', COUNT(*) FROM factura_items
UNION ALL SELECT 'tarifarios_predeterminados', COUNT(*) FROM tarifarios WHERE es_predeterminado = TRUE
UNION ALL SELECT 'lineas_moeve_772', COUNT(*) FROM tarifario_lineas WHERE id_tarifario = @tarifario_moeve_772;

