# Fase B.1.2 - Revisión global de tablas reales, casos controlados B2 y versión ERP

Fecha: 2026-05-19  
Ámbito: local `abaco_ciete` (sin producción, sin commit)

## 1. Objetivo de B.1.2

1. Revisar visualmente tablas/listados con datos reales para evitar solapamientos.
2. Corregir de forma quirúrgica el caso activo en Facturas (`Nº FACTURA` vs `FECHA`).
3. Preparar escenarios controlados para validación diaria previa a B.2, evitando contaminar datos reales sin trazabilidad.
4. Revisar y completar control de edición por celda con conflicto reciente.
5. Fijar versión visible del ERP en estado pre-B.2.

## 2. Seguridad previa y entorno

- `APP_ENV=local` confirmado.
- Base de trabajo: `abaco_ciete`.
- Inventario Git ejecutado (worktree ya venía sucio; no reset/clean/checkout).
- Backup previo a inserción de muestra B2:  
  `database/backups/abaco_ciete_pre_b1_2_casos_validacion_20260519_070149.sql`  
  Método: `mysqldump.exe`.

## 3. Revisión global visual de tablas/listados

Revisión estática sobre componentes de tabla y outliers reales de datos:

- Trabajos (moderno/excel): estable desde B.1.1 (`Nº` con truncado y `title`).
- Pedidos (moderno): reforzado ancho/truncado en `Nº pedido` y ancho fijo en `Fecha`.
- Facturas (moderno/excel): corregido solapamiento `Nº FACTURA` con `FECHA`.
- Clientes / Estaciones / Contratos / Sociedades / Tarifarios / Líneas: sin KO visual crítico nuevo detectado por estructura de columnas.
- Administración, Cierre, Soporte, Mensajes, Importaciones, Estado: sin KO crítico nuevo detectado en revisión estructural de tablas/cards.

### Hallazgos visuales relevantes

| Módulo | Hallazgo | Severidad | Estado |
| --- | --- | --- | --- |
| Trabajos moderno | Valor largo en `Nº` podía comer `Descripción` (ya mitigado en B.1.1) | KO menor | validado mantenido |
| Facturas moderno | `Nº FACTURA` largo (`SIN_NUMERO-*`) invadía visualmente `FECHA` | KO menor | corregido B.1.2 |
| Facturas Excel | Celdas de texto largo sin truncado consistente en modo no edición | KO menor | corregido B.1.2 |
| Pedidos moderno | Riesgo de desbordamiento en `Nº pedido` largo | aviso preventivo | mitigado B.1.2 |

## 4. Correcciones aplicadas

## 4.1 Facturas moderno (`resources/js/Pages/Facturas/Index.jsx`)

- Tabla con `table-fixed` y `min-w` controlado.
- Columna `Nº FACTURA` con ancho explícito (`w-[16rem] max-w-[16rem]`).
- Render de valor con `overflow-hidden`, `text-ellipsis`, `whitespace-nowrap` y `title`.
- Columna `FECHA` con ancho propio (`w-32`) para evitar invasión.

## 4.2 Facturas Excel (`resources/js/Components/ui/FacturasExcelView.jsx`)

- `EditableTextCell` normalizado para truncado seguro en modo lectura y botón editable.
- Ajuste de anchuras:
  - `Nº Factura`: mayor ancho operativo.
  - `Fecha`: ancho fijo propio.
- Resultado: textos largos (`SIN_NUMERO-REPSOL-...`) no rompen columna vecina.

## 4.3 Pedidos moderno (`resources/js/Pages/Pedidos/Index.jsx`)

- Tabla a `table-fixed` con `min-w` operativo.
- `Nº pedido` con ancho y truncado consistentes + `title`.
- `Fecha` con ancho fijo para separación estable.

## 4.4 Control de conflicto por celda (Trabajos Excel)

- Backend `PATCH` de trabajos (`TrabajoController`) amplía respuesta de conflicto:
  - `modificado_recientemente` (boolean).
  - mensaje específico cuando la última modificación es de otro usuario en la última hora:
    - "Este campo fue modificado recientemente por otro usuario."
- Frontend (`useOptimisticField` + `TrabajosExcelView`) consume el flag y muestra diálogo contextual.
- Se mantiene comportamiento exigido:
  - no guarda en conflicto sin confirmación;
  - conserva borrador del usuario (`myValue`);
  - permite recargar valor servidor o mantener cambio propio con confirmación.

## 5. Caso facturas `SIN_NUMERO-*`

- Ejemplo real confirmado: `SIN_NUMERO-REPSOL-23096-2026-03-01`.
- El dato **no se modifica** en SQL ni en importación.
- Solo se corrige presentación visual (ancho + truncado + `title`).

## 6. Auditoría de cobertura real importada (read-only)

## 6.1 Importaciones registradas

- Importaciones operativas completadas: **11**.
- `filas_con_error`: **0** en las 11.
- Archivo auxiliar de mapeo (`Mapeo_Moeve_Repsol_Envolvente.xlsx`) no se importa (fuente auxiliar).

| ID | Archivo | Contexto | Estado | Leídas | Importadas | Ignoradas | Avisos | Errores |
| ---: | --- | --- | --- | ---: | ---: | ---: | ---: | ---: |
| 12 | 01 Control de Trabajos Moeve.xlsx | MOEVE | completado | 7873 | 6686 | 1187 | 1278 | 0 |
| 13 | 02 Listado EESS España y Portugal 16-03-26.xlsx | MOEVE | completado | 6855 | 6853 | 2 | 2 | 0 |
| 14 | 01 Control Trabajos DISEÑO REPSOL.xlsx | REPSOL | completado | 3884 | 3807 | 77 | 104 | 0 |
| 15 | 02 Control Trabajos EDIFICACIÓN.xlsx | REPSOL | completado | 4529 | 4477 | 52 | 967 | 0 |
| 16 | 03 Control Trabajos OBRAS REPSOL Z10.xlsx | REPSOL | completado | 5636 | 5570 | 66 | 260 | 0 |
| 17 | 03 Control Trabajos OBRAS REPSOL Z50.xlsx | REPSOL | completado | 4832 | 4714 | 118 | 310 | 0 |
| 18 | 05 Control Trabajos LICENCIAS REPSOL.xlsx | REPSOL | completado | 3721 | 3668 | 53 | 84 | 0 |
| 19 | 09 Control Trabajos FV REPSOL.xlsx | REPSOL | completado | 3885 | 3832 | 53 | 142 | 0 |
| 20 | 10 Control Trabajos ESTRUCTURAS Y VERTIDOS REPSOL.xlsx | REPSOL | completado | 3747 | 3694 | 53 | 106 | 0 |
| 21 | 12 Control Trabajos MTO REPSOL.xlsx | REPSOL | completado | 3877 | 3824 | 53 | 144 | 0 |
| 22 | 13 Control Trabajos PUNTOS DE RECARGA.xlsx | REPSOL | completado | 4216 | 4163 | 53 | 328 | 0 |

## 6.2 Cobertura por archivo (disponible en metadata actual)

Nota técnica: `importaciones.resumen_json` no persiste conteo exacto por entidad (`trabajos/pedidos/items/facturas/factura_items`) por archivo; la traza detallada por fila solo guarda `warning/ignored`.  
Por ello, la cobertura por archivo se documenta con `leídas/importadas/ignoradas/avisos` + tipo de resumen.

| Archivo | Contexto | Trabajos | Pedidos | Items | Facturas | Factura items | Avisos | Ignorados | Estado |
| --- | --- | --- | --- | --- | --- | --- | ---: | ---: | --- |
| 01 Control de Trabajos Moeve.xlsx | MOEVE | mixto (no desglosado por archivo) | mixto | mixto | mixto | mixto | 1278 | 1187 | completado |
| 02 Listado EESS España y Portugal 16-03-26.xlsx | MOEVE | N/A (estaciones) | N/A | N/A | N/A | N/A | 2 | 2 | completado |
| 01 Control Trabajos DISEÑO REPSOL.xlsx | REPSOL | mixto (no desglosado por archivo) | mixto | mixto | mixto | mixto | 104 | 77 | completado |
| 02 Control Trabajos EDIFICACIÓN.xlsx | REPSOL | mixto (no desglosado por archivo) | mixto | mixto | mixto | mixto | 967 | 52 | completado |
| 03 Control Trabajos OBRAS REPSOL Z10.xlsx | REPSOL | mixto (no desglosado por archivo) | mixto | mixto | mixto | mixto | 260 | 66 | completado |
| 03 Control Trabajos OBRAS REPSOL Z50.xlsx | REPSOL | mixto (no desglosado por archivo) | mixto | mixto | mixto | mixto | 310 | 118 | completado |
| 05 Control Trabajos LICENCIAS REPSOL.xlsx | REPSOL | mixto (no desglosado por archivo) | mixto | mixto | mixto | mixto | 84 | 53 | completado |
| 09 Control Trabajos FV REPSOL.xlsx | REPSOL | mixto (no desglosado por archivo) | mixto | mixto | mixto | mixto | 142 | 53 | completado |
| 10 Control Trabajos ESTRUCTURAS Y VERTIDOS REPSOL.xlsx | REPSOL | mixto (no desglosado por archivo) | mixto | mixto | mixto | mixto | 106 | 53 | completado |
| 12 Control Trabajos MTO REPSOL.xlsx | REPSOL | mixto (no desglosado por archivo) | mixto | mixto | mixto | mixto | 144 | 53 | completado |
| 13 Control Trabajos PUNTOS DE RECARGA.xlsx | REPSOL | mixto (no desglosado por archivo) | mixto | mixto | mixto | mixto | 328 | 53 | completado |

## 6.3 Ignorados y avisos agregados (`importacion_filas`)

| Código | Clasificación | Resultado | Total | Lectura B.1.2 |
| --- | --- | --- | ---: | --- |
| historical_invoice_without_items | do_not_invent | ignored | 1183 | decisión CIETE |
| work_amount_without_order | functional_decision | warning | 989 | decisión CIETE |
| invoice_number_normalized | technical_improvement | warning | 969 | KO menor técnico |
| tariff_line_unusable | acceptable | ignored | 552 | aceptable (hojas tarifa no operativas) |
| work_missing_number | acceptable | ignored | 26 | aceptable |
| unknown_columns | acceptable / functional_decision / technical_improvement / do_not_invent | warning | 36 | aviso de fuente |
| invoice_missing_number | acceptable | ignored | 4 | aceptable |
| station_missing_code | acceptable | ignored | 2 | aviso menor |

## 6.4 Maestros esperados vs observados

Base real post-importación (antes de insertar muestra B2):

- empresas = 2
- estaciones = 6584
- contratos = 4
- contrato_empresas_facturadoras = 4
- tarifarios = 5
- tarifario_lineas = 425
- tipos_documento = 15
- tipos_trabajo = 257
- unidades = 4

Estado OTROS en base real: contexto preparado, sin empresa ni estaciones importadas.

Delta por muestra B2 local (`[muestra-b2-validacion-2026-05-19]`):

- empresas: +3 (OTROS)
- estaciones: +1 (OTROS)
- contratos: +3 (OTROS)
- contrato_empresas_facturadoras: +2
- tarifarios: +3
- tarifario_lineas: +3
- trabajos: +4
- pedidos: +3
- pedido_items: +3
- facturas: +1
- factura_items: +1

## 6.5 Relaciones trabajo/pedido/factura (coherencia)

Controles principales:

- pedidos sin trabajo: 0
- pedido_items sin pedido: 0
- facturas sin items: 0
- factura_items sin factura: 0
- factura_items sin pedido_item: 0
- mezclas de contexto trabajo/pedido/factura/item: 0

## 6.6 Outliers reales visibles (sin modificar datos)

- `SIN_NUMERO-*` en facturas: 967 (969 avisos de normalización).
- `numero_trabajo_operativo` con fracción larga (>=4 decimales): 459 (incluye `865.66666666666697`).
- trabajos sin estación: 1356.
- pedidos negativos: 2 (`400381870`, `400376007`).
- trabajos con fecha imposible: 6.

## 7. Matriz de escenarios diarios (real vs muestra)

| Escenario | Existe real | ID real | Se crea muestra | Código muestra | Resultado esperado |
| --- | --- | --- | --- | --- | --- |
| 1. MOEVE correcto completo | Sí | trabajo 18275 / pedido 15839 / factura 4242 | No | — | Flujo completo válido |
| 2. MOEVE terminado sin pedido | Sí | trabajo 18119 | No | — | Pendiente en cierre |
| 3. MOEVE pedido sin factura | Sí | pedido 15847 | No | — | Pendiente de facturar |
| 4. MOEVE factura parcial | Sí | pedido 15704 | No | — | Diferencia visible |
| 5. MOEVE sociedad/CIF no válida | No (en real) | — | No | — | Cubierto por caso OTROS NOCIF/NOSOC |
| 6. REPSOL correcto completo | Sí | trabajo 23105 / pedido 19385 / factura 5473 | No | — | Flujo completo válido |
| 7. REPSOL terminado sin factura | Sí | trabajo 23243 | No | — | Pendiente de cierre |
| 8. REPSOL dato raro controlado | Sí | trabajo 21460 (`865.666...`) | No | — | UI estable con truncado |
| 9. REPSOL fecha problemática | Sí | trabajo 18530 (y otros) | No | — | Diagnóstico de calidad |
| 10. OTROS correcto mínimo | No (real) | — | Sí | B2-OTR-980001 | Flujo completo OTROS |
| 11. OTROS sin maestro suficiente | No (real) | — | Sí | B2-OTR-980002 (contrato NOSOC) | Bloqueo por falta sociedad |
| 12. Contable bloqueado sin sociedad/CIF | No (real) | — | Sí | B2-OTR-980004 (NOCIF) | Bloqueo de facturación |
| 13. Dirección cierre bloqueado checklist | Sí (real) | varios terminados sin factura | No | — | Mantener pendiente |
| 14. Dirección cierre permitido | Sí (real) | varios facturados/finalizados | No | — | Permitir cierre |
| 15. Ejecución MOEVE no ve REPSOL | Sí | validable con perfiles reales | No | — | Aislamiento correcto |
| 16. Ejecución REPSOL no ve MOEVE | Sí | validable con perfiles reales | No | — | Aislamiento correcto |
| 17. Multicontexto sin arrastre | Sí | validable con `cesar@ciete.es` | No | — | Sin mezcla de catálogos |
| 18. Admin técnico solo técnico/lectura | Sí | validable con `admin@ciete.es` | No | — | Sin mutación operativa |
| 19. Conflicto de edición por celda | Parcial (infra) | — | Sí | B2-OTR-980003 | Aviso + borrador + confirmar sobrescritura |
| 20. Importe negativo real existente | Sí | pedidos 10861 y 10988 | No | — | Caso de control de calidad |

## 8. Scripts de muestra B2

- Inserción: `database/manual/2026_05_19_insert_muestra_b2_validacion_flujo_diario.sql`
- Limpieza: `database/manual/2026_05_19_delete_muestra_b2_validacion_flujo_diario.sql`
- Marca de trazabilidad: `[muestra-b2-validacion-2026-05-19]`

Validación de inserción ejecutada:

- empresas: 3
- estaciones: 1
- contratos: 3
- contrato_empresas_facturadoras: 2
- tarifarios: 3
- tarifario_lineas: 3
- trabajos: 4
- pedidos: 3
- pedido_items: 3
- facturas: 1
- factura_items: 1

## 9. Estado del control de edición por celda

Implementado en slice Trabajos Excel:

- Detección por `updated_at` (optimistic locking).
- Respuesta 409 con:
  - campo,
  - valor actual,
  - valor intentado,
  - fecha actual (`updated_at_actual`),
  - usuario de modificación,
  - flag `modificado_recientemente`.
- Mensaje específico cuando el cambio fue en última hora por otro usuario.
- Frontend mantiene borrador y no guarda automáticamente en conflicto.

Pendiente fuera de alcance B.1.2:

- Extender el mismo patrón a pedidos/facturas inline si CIETE lo prioriza en B.2.

## 10. Versión ERP fijada

Versión visible/documental aplicada en esta fase:

- `v2.1.0-rc1`

Sitios actualizados:

- `app/Http/Controllers/StatusController.php`
- `resources/js/i18n/locales/es.js`
- `resources/js/i18n/locales/en.js`

Criterio propuesto para subir a `v2.1.0-demo`:

1. ejecutar B.2 completa sin KO crítico/mayor funcional;
2. validar escenarios diarios con perfiles reales;
3. cerrar decisiones CIETE sobre avisos de fuente prioritarios.

## 11. Validación técnica ejecutada

PHP lint (archivos PHP tocados):

- `app/Http/Controllers/Api/TrabajoController.php`: PASS
- `app/Http/Controllers/StatusController.php`: PASS

Tests ejecutados:

- `TrabajoTest`: PASS
- `PedidoTest`: PASS (tras reset de `abaco_ciete_testing`)
- `FacturaTest`: PASS (tras reset de `abaco_ciete_testing`)
- `ClientesEstacionesApiTest`: PASS
- `MaestrosTest`: PASS
- `RoleModuleAccessTest`: PASS
- `ExcelModeAccessTest`: PASS
- `ContextCreationGuardTest`: PASS
- `AdminTechnicalMutationTest`: PASS

Nota de ejecución:

- Se detectó estado inconsistente inicial en `abaco_ciete_testing` por colisión de migraciones de pruebas al ejecutar filtros en paralelo.
- Se saneó solo DB de pruebas (`DROP/CREATE abaco_ciete_testing`) y se repitieron filtros fallidos.
- `abaco_ciete` real no se tocó en ese saneamiento.

Build:

- `npm run build`: PASS

## 12. Archivos tocados en B.1.2

Código:

- `resources/js/Pages/Facturas/Index.jsx`
- `resources/js/Components/ui/FacturasExcelView.jsx`
- `resources/js/Pages/Pedidos/Index.jsx`
- `app/Http/Controllers/Api/TrabajoController.php`
- `resources/js/Hooks/useOptimisticField.js`
- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `app/Http/Controllers/StatusController.php`
- `resources/js/i18n/locales/es.js`
- `resources/js/i18n/locales/en.js`

Datos/scripts locales:

- `database/manual/2026_05_19_insert_muestra_b2_validacion_flujo_diario.sql` (nuevo)
- `database/manual/2026_05_19_delete_muestra_b2_validacion_flujo_diario.sql` (nuevo)

Documentación:

- `docs/02_CLIENTE/B1_2_REVISION_GLOBAL_TABLAS_CASOS_DEMO_Y_VERSION_2026-05-19.md` (nuevo)

## 13. Estado final B.1.2

- Sin KO crítico nuevo.
- Sin KO mayor funcional nuevo.
- Ajustes visuales y control de conflicto por celda implementados en alcance.
- Casos B2 OTROS creados con trazabilidad y script reversible.

**Estado B.1.2: listo para B.2.**

## 14. Confirmaciones

- No se tocó producción.
- No se hizo commit.
- No se hizo push.
- No se reimportaron Excel reales.
- No se modificaron datos reales masivamente.
- Los casos B2 se insertaron solo en local, marcados y reversibles.
