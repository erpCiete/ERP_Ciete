# B.1.1 - Ajustes visuales post-importación + auditoría de cobertura real de datos

Fecha: 2026-05-19  
Ámbito: local `abaco_ciete` (solo lectura de datos + ajustes visuales UI)

## 1. Objetivo

Cerrar una fase quirúrgica post-B.1 para:

1. corregir problemas visuales detectados con datos reales en UI;
2. auditar cobertura real de importación por archivo, avisos, ignorados y relaciones;
3. dejar evidencia clara de qué está cargado, qué está avisado/ignorado y qué requiere decisión CIETE antes de B.2.

No se ha ejecutado nueva importación, no se han modificado datos reales y no se ha tocado producción.

---

## 2. Ajustes visuales aplicados

### 2.1 Zona de cuenta/sidebar: "Mi perfil" para todos los roles

Problema detectado:

- `Mi perfil` estaba oculto en la zona de cuenta para el admin técnico (`can_access_admin_panel`), aunque sí aparecían correo y `Cerrar sesión`.

Cambio aplicado:

- Se elimina la condición que ocultaba `Mi perfil` al admin técnico en:
  - `resources/js/Layouts/AuthenticatedLayout.jsx`
  - `resources/js/Components/MobileSidebarDrawer.jsx`

Resultado:

- `Mi perfil` se muestra junto a correo y `Cerrar sesión` para todos los roles, incluido admin técnico.
- No se ha añadido `Perfil` a la navegación principal (`sidebar.js` permanece sin ese alta).

### 2.2 Avatar/logo en zona de cuenta

Problema detectado:

- El avatar/logo configurado en perfil no se renderizaba de forma consistente en la zona de cuenta.

Cambio aplicado:

- Nuevo componente reutilizable: `resources/js/Components/UserAccountAvatar.jsx`
- Comportamiento:
  - usa `auth.user.avatar_url` cuando existe;
  - fallback consistente a iniciales cuando no existe avatar;
  - `onError` evita imagen rota y fuerza fallback visual.

Integración:

- `resources/js/Layouts/AuthenticatedLayout.jsx`
- `resources/js/Components/MobileSidebarDrawer.jsx`

Resultado:

- El avatar/logo se muestra de forma consistente en sidebar desktop y drawer móvil.
- Sin icono roto cuando la imagen falla.

### 2.3 Tabla moderna de Trabajos: solapamiento columna `Nº` con `Descripción`

Problema detectado:

- Con valores largos de `numero_trabajo_operativo` (ejemplo: `865.66666666666697`) la columna `Nº` invadía visualmente la de `Descripción`.

Cambio aplicado en `resources/js/Pages/Trabajos/Index.jsx`:

- Cabecera `Nº` con ancho controlado: `w-[9rem] max-w-[9rem]`.
- Celda `Nº` con wrapper:
  - `block`
  - `max-w-full`
  - `overflow-hidden`
  - `text-ellipsis`
  - `whitespace-nowrap`
- Se añade `title={workNumber}` para ver valor completo en hover.
- `Descripción` reforzada para comportamiento estable:
  - `min-w-0`
  - padding independiente
  - `line-clamp-2 break-words`

Resultado:

- Se corrige el solapamiento visual.
- El valor original no se transforma ni redondea.
- `865.66666666666697` permanece intacto en dato; solo cambia su presentación en tabla.

---

## 3. Evidencia del caso `865.66666666666697`

Confirmaciones:

1. En base de datos:
   - `trabajos.numero_trabajo_operativo = "865.66666666666697"`
   - `trabajos.numero_trabajo = 865`
2. En Excel fuente real:
   - archivo: `C:/Users/kampe/Documents/Abaco/excelsactualizados/Repsol/03 Control Trabajos OBRAS REPSOL Z50.xlsx`
   - hoja: `AUTOFACTURACION`
   - fila detectada: 850
   - valor detectado en celda: `865.66666666666697`

Conclusión:

- Es dato de origen real de Excel, no un artefacto de la vista moderna.

---

## 4. Auditoría de cobertura real (solo lectura)

## 4.1 Importaciones operativas registradas

Estado global de importaciones operativas:

- importaciones operativas completadas: **11**
- `filas_con_error`: **0** en las 11
- mapeo auxiliar (`Mapeo_Moeve_Repsol_Envolvente.xlsx`): **no importado** como fuente operativa (decisión documentada)

Totales consolidados (`importaciones` ids 12..22):

- filas leídas: **53.055**
- filas importadas/actualizadas: **51.288**
- filas ignoradas: **1.767**
- filas con aviso: **3.725**
- filas con error: **0**

Nota de consistencia:

- En `audit_log` de la ejecución aparece `rows_with_warning=3726` por incluir además `file_without_context=1` del archivo auxiliar de mapeo (no operativo).

## 4.2 Cobertura por archivo

Leyenda columnas `Trabajos/Pedidos/Items/Facturas/Factura items`:

- `Mixto*`: el `resumen_json` del importador no desglosa por entidad en cada archivo de tipo `trabajos_pedidos_facturas`; sí confirma filas operativas procesadas por hoja.
- `N/A`: no aplica al tipo de archivo.

| Archivo | Contexto | Trabajos | Pedidos | Items | Facturas | Factura items | Avisos | Ignorados | Estado |
| --- | --- | --- | --- | --- | --- | --- | ---: | ---: | --- |
| 01 Control de Trabajos Moeve.xlsx | MOEVE | Mixto* | Mixto* | Mixto* | Mixto* | Mixto* | 1.278 | 1.187 | completado |
| 02 Listado EESS España y Portugal 16-03-26.xlsx | MOEVE | N/A | N/A | N/A | N/A | N/A | 2 | 2 | completado |
| 01 Control Trabajos DISEÑO REPSOL.xlsx | REPSOL | Mixto* | Mixto* | Mixto* | Mixto* | Mixto* | 104 | 77 | completado |
| 02 Control Trabajos EDIFICACIÓN.xlsx | REPSOL | Mixto* | Mixto* | Mixto* | Mixto* | Mixto* | 967 | 52 | completado |
| 03 Control Trabajos OBRAS REPSOL Z10.xlsx | REPSOL | Mixto* | Mixto* | Mixto* | Mixto* | Mixto* | 260 | 66 | completado |
| 03 Control Trabajos OBRAS REPSOL Z50.xlsx | REPSOL | Mixto* | Mixto* | Mixto* | Mixto* | Mixto* | 310 | 118 | completado |
| 05 Control Trabajos LICENCIAS REPSOL.xlsx | REPSOL | Mixto* | Mixto* | Mixto* | Mixto* | Mixto* | 84 | 53 | completado |
| 09 Control Trabajos FV REPSOL.xlsx | REPSOL | Mixto* | Mixto* | Mixto* | Mixto* | Mixto* | 142 | 53 | completado |
| 10 Control Trabajos ESTRUCTURAS Y VERTIDOS REPSOL.xlsx | REPSOL | Mixto* | Mixto* | Mixto* | Mixto* | Mixto* | 106 | 53 | completado |
| 12 Control Trabajos MTO REPSOL.xlsx | REPSOL | Mixto* | Mixto* | Mixto* | Mixto* | Mixto* | 144 | 53 | completado |
| 13 Control Trabajos PUNTOS DE RECARGA.xlsx | REPSOL | Mixto* | Mixto* | Mixto* | Mixto* | Mixto* | 328 | 53 | completado |

Cobertura de entidades (agregada de ejecución real, `audit_log`):

- trabajos: 11.614
- pedidos: 9.685
- pedido_items: 9.701
- facturas: 2.733
- factura_items: 9.194

## 4.3 Filas ignoradas y avisos: agrupación por código

Resumen por código (`importacion_filas`, ids 12..22):

| Código | Total | Resultado | Clasificación importador |
| --- | ---: | --- | --- |
| historical_invoice_without_items | 1.183 | ignored | do_not_invent |
| work_amount_without_order | 989 | warning | functional_decision |
| invoice_number_normalized | 969 | warning | technical_improvement |
| tariff_line_unusable | 552 | ignored | acceptable |
| work_missing_number | 26 | ignored | acceptable |
| unknown_columns (suma clasificaciones) | 36 | warning | acceptable / functional_decision / technical_improvement / do_not_invent |
| invoice_missing_number | 4 | ignored | acceptable |
| station_missing_code | 2 | ignored | acceptable |

Clasificación funcional B.1.1:

- `historical_invoice_without_items`: **decisión CIETE** (no inventar enlaces históricos).
- `work_amount_without_order`: **decisión CIETE** / **aviso de fuente**.
- `invoice_number_normalized`: **KO menor** (normalización técnica no bloqueante).
- `tariff_line_unusable`: **aceptable** en esta fase (líneas no operativas/usables).
- `unknown_columns`: **aviso de fuente** (36 columnas no operativas mapeadas como desconocidas).
- `work_missing_number`, `invoice_missing_number`, `station_missing_code`: **KO menor** a revisar con negocio/fuente.

## 4.4 Maestros reales

| Maestro | Conteo | Estado |
| --- | ---: | --- |
| contextos_cliente | 3 | OK (`MOEVE`, `REPSOL`, `OTROS`) |
| empresas | 2 | OK (1 MOEVE, 1 REPSOL) |
| estaciones_servicio | 6.584 | OK |
| contratos | 4 | OK |
| contrato_empresas_facturadoras | 4 | OK |
| tarifarios | 5 | OK |
| tarifario_lineas | 425 | OK |
| tipos_documento | 15 | OK |
| tipos_trabajo | 257 | OK |
| unidades | 4 | OK |

Contexto OTROS:

- `OTROS CLIENTES` está preparado como contexto (`id_contexto=3`), pero en esta carga no tiene empresa ni estaciones reales importadas.

## 4.5 Asignaciones trabajo/pedido/factura

Checks de relación:

- pedidos sin trabajo: 0
- pedidos sin items: 0
- pedido_items sin pedido: 0
- facturas sin items: 0
- factura_items sin factura: 0
- factura_items sin pedido_item: 0
- mezclas de contexto trabajo/pedido/factura/item: 0

Conclusión de relaciones principales:

- Las relaciones operativas clave están cargadas y coherentes en esta base real.

## 4.6 Numeraciones, códigos y nombres (incluye outliers)

Hallazgos:

- `numero_trabajo_operativo` con punto decimal: **459** registros (todos REPSOL).
- Longitud máxima detectada de `numero_trabajo_operativo`: **18** caracteres.
- Ejemplo crítico de visualización validado: `865.66666666666697` (origen Excel real).
- `facturas.numero_factura` normalizadas (`SIN_NUMERO-*`): **967**.
- Códigos estación duplicados en mismo contexto: **0**.
- Mismo código en contextos distintos: **29** (permitido por regla).

---

## 5. Integridad extendida

Resultado resumido:

- KO crítico nuevo: **0**
- KO mayor nuevo: **0**
- KO menor / avisos de fuente / decisiones funcionales: presentes (ver tabla)

Indicadores clave:

- trabajos sin estación: **1.356**
- trabajos sin descripción: **176**
- trabajos sin fecha: **2.650**
- trabajos con fecha imposible: **6**
- pedidos con importe negativo: **2**
- pedido_items con importe negativo: **2**
- facturas con importe negativo: **0**
- facturas sin número: **0**
- estaciones sin código: **0**
- estaciones sin nombre: **0**
- contratos sin sociedad facturadora: **0**
- tarifarios sin líneas: **4**
- líneas tarifarias sin precio positivo: **1**

Interpretación funcional:

- No aparecen huérfanos críticos ni cruces de contexto.
- Persisten avisos de calidad de origen y decisiones funcionales pendientes con CIETE.

---

## 6. Respuesta explícita de cobertura

### 6.1 Qué está cargado

- Sí, los conteos clave y relaciones principales están cargados y reconciliados.
- La base local real contiene el volumen esperado P1-12 en trabajos/pedidos/items/facturas.

### 6.2 Qué no está resuelto todavía

- No, no puede afirmarse que todas las columnas auxiliares estén resueltas (36 `unknown_columns`).
- No, no están cerrados todos los avisos funcionales (`work_amount_without_order`, facturas históricas sin ítems enlazables, normalizaciones).

### 6.3 Qué requiere validación con CIETE

1. tratamiento funcional de `work_amount_without_order` (989 casos);
2. política para facturas históricas sin enlace (`historical_invoice_without_items`, 1.183);
3. revisión de importes negativos MOEVE (2 pedidos/2 items);
4. revisión de fechas imposibles en origen (6 trabajos);
5. confirmación de campos decimales en numeración operativa REPSOL (incluido `865.66666666666697`).

### 6.4 Estado previo a B.2

- Estado B.1.1: **cerrada**.
- Estado de paso a B.2: **listo para B.2** (sin KO crítico/mayor nuevo en esta fase).

---

## 7. Validación técnica ejecutada

Suites ejecutadas:

- `ImportacionesAccessTest`: PASS
- `ClientesEstacionesApiTest`: PASS
- `MaestrosTest`: PASS
- `TrabajoTest`: PASS
- `PedidoTest`: PASS
- `FacturaTest`: PASS
- `RoleModuleAccessTest`: PASS
- `ExcelModeAccessTest`: PASS
- `ContextCreationGuardTest`: PASS

Build:

- `npm run build`: PASS
- warning no bloqueante: `[PLUGIN_TIMINGS]` en plugin `laravel`

---

## 8. Archivos tocados en B.1.1

Frontend/UI:

- `resources/js/Components/UserAccountAvatar.jsx` (nuevo)
- `resources/js/Layouts/AuthenticatedLayout.jsx`
- `resources/js/Components/MobileSidebarDrawer.jsx`
- `resources/js/Pages/Trabajos/Index.jsx`

Documentación:

- `docs/02_CLIENTE/B1_1_AJUSTES_VISUALES_Y_AUDITORIA_COBERTURA_DATOS_2026-05-19.md` (nuevo)
- `docs/02_CLIENTE/tareasComparar.md` (actualizado)
- `docs/02_CLIENTE/VALIDACION_OPERATIVA_KO_EVIDENCIAS_ERP_CIETE_2026-05-18.md` (actualizado)

---

## 9. Confirmaciones de control

- No se tocó producción.
- No se hizo commit.
- No se hizo push.
- No se reimportó.
- No se modificaron datos reales masivamente.
- No se modificaron importadores, migraciones, seeders ni permisos persistidos.


## 10. Referencia cruzada B.1.2

La continuidad de esta fase (revisión global visual de tablas, casos controlados B2, control de conflicto por celda y versión) queda documentada en:

- `docs/02_CLIENTE/B1_2_REVISION_GLOBAL_TABLAS_CASOS_DEMO_Y_VERSION_2026-05-19.md`
