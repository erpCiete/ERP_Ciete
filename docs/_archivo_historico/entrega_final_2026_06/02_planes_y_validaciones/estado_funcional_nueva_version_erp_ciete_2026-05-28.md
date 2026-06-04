# Estado funcional nueva versión ERP CIETE — 2026-05-28

## 1. Objetivo del documento

Este documento no es un plan nuevo ni una implementación nueva. Es una fotografía de control del estado real de la nueva versión del ERP CIETE a fecha 2026-05-28, cruzando lo pedido por negocio, lo reflejado en la documentación vigente y lo que realmente soporta el código actual.

Su objetivo es dejar trazable, revisable y utilizable:

- qué se pidió definitivamente,
- de dónde sale cada decisión,
- qué ya existía antes de esta nueva ola,
- qué se ha ajustado ahora,
- qué está completo, parcial, pendiente o bloqueado,
- qué archivos lo sostienen,
- qué validaciones lo respaldan,
- y qué queda pendiente antes de seguir con los siguientes bloques.

No sustituye al backlog maestro ni al plan técnico de 8 días. Los complementa como documento de control funcional antes de continuar implementando.

## 2. Fuentes usadas y jerarquía de autoridad

| Fuente | Ruta | Tipo | Autoridad | Observaciones |
| --- | --- | --- | --- | --- |
| Código real actual | `app/*`, `resources/js/*`, `routes/*`, `database/seeders/private/*` | código | 1 | Fuente principal para este documento. Si el código contradice documentación antigua, prevalece el código. |
| Plan técnico nueva versión | `docs/02_CLIENTE/plan_8_dias_nueva_version_erp_ciete.md` | documentación operativa vigente | 2 | Documento principal de intención y ejecución incremental para la nueva ola. |
| Backlog maestro vivo | `docs/02_CLIENTE/tareasComparar.md` | backlog / trazabilidad | 3 | Útil para estado histórico, validaciones ejecutadas y evidencias, pero mezcla fases anteriores con la nueva ola. |
| Reunión CIETE 2026-05-19 | documentada indirectamente en plan y backlog | fuente funcional de negocio | 4 | No se ha usado como archivo aislado aquí; se ha consumido a través del plan técnico y del backlog vivo. |
| Correos César 2026-05-20 | documentados indirectamente en plan y backlog | fuente funcional complementaria | 4 | No se ha localizado un archivo único independiente en el repositorio; su rastro operativo aparece integrado en el plan técnico. |
| Documentación histórica | `docs/02_CLIENTE/historicos/*`, `docs/05-SPRINTS/*`, `docs/07_REVISIONES_DOCUMENTALES/*`, otros históricos | contexto | 5 | Solo se usa como contexto. Si contradice código o plan vigente, debe considerarse posiblemente desfasada. |

## 3. Resumen ejecutivo

La nueva versión del ERP no parte de cero: el núcleo ya existe y el sistema real está más avanzado de lo que sugeriría una lectura superficial del backlog histórico. El módulo más maduro sigue siendo `Trabajos`, que ya actúa como eje operativo de hecho, con tabla compacta tipo Excel, edición inline, control de conflicto por campo, resumen económico y visibilidad del pedido principal. Facturación, contextos/roles, maestros críticos con activación/desactivación, estaciones y auditoría base también existen y no requieren rediseño estructural.

La nueva ola no consiste en “construir un ERP”, sino en alinear flujos y jerarquías operativas con lo pedido por CIETE. El desfase principal sigue estando en `Pedidos`, que todavía funciona demasiado como módulo paralelo, en `PedidoItem`, donde el selector sigue siendo básico y las cantidades continúan forzadas a entero, y en la exportación específica de Moeve, que no aparece implementada como PDF + CSV + cuadro ARIBA desde pedido.

El Bloque A se ha movido parcialmente: `Trabajos` ahora se presenta mejor como pantalla principal diaria y distingue con más claridad trabajos con pedido y sin pedido, pero todavía falta validación manual final y no se ha tocado la creación real de pedido desde trabajo. Ese salto pertenece al Bloque B y no debe adelantarse dentro de este documento.

También siguen parciales o pendientes la derivación de estados automáticos, el rebalanceo del panel de cierre a rol secundario de diagnóstico, la revisión fina del rol técnico frente a negocio, la extensión homogénea de auditoría/concurrencia a módulos críticos y la verificación funcional completa de la estrategia “Repsol tarifa única” sobre datos reales.

Lo que no debe tocarse todavía en esta fase documental es claro: no hay que abrir Bloque B ni siguientes en código, no hay que tocar importaciones, no hay que mover base de datos ni seeders privados, no hay que intentar resolver ya exportación Moeve, decimales, facturación avanzada ni permisos profundos. Antes de seguir implementando, toca revisar esta fotografía, validarla manualmente y usarla como base de control de la siguiente ejecución.

## 4. Mapa de decisiones definitivas

| Decisión funcional | Origen | Estado | Evidencia en código o documentación | Siguiente acción |
| --- | --- | --- | --- | --- |
| Trabajos como centro operativo | plan 8 días + código real | parcial | `TrabajoController@index`, `TrabajoResource`, `TrabajosExcelView.jsx`, `Trabajos/Index.jsx`, Bloque A ya ejecutado parcialmente | cerrar validación manual del Bloque A y usar `Trabajos` como punto de entrada antes de abrir Bloque B |
| Pedido desde trabajo | plan 8 días | pendiente | el plan lo fija como prioridad; `routes/web/operativa.php` mantiene todavía flujo propio de `Pedidos` | ejecutar Bloque B sin mezclar todavía líneas ni decimales |
| Pedido principal + pedidos extra como excepción | plan 8 días + código real | parcial | `TrabajoResource` expone pedido principal; `TrabajoController@index` carga `primerPedido`; sigue existiendo `pedidos_count` | exponer mejor la excepción múltiple en flujo futuro sin romper `hasMany` |
| Trabajo sin pedido permitido | código real + plan | completo | `TrabajoResource` soporta trabajo sin `primerPedido`; Bloque A muestra `Sin pedido` explícito | mantener compatibilidad en Bloque B |
| Líneas desde tarifario | código real + plan | parcial | `StorePedidoRequest`, `UpdatePedidoRequest`, `ItemsTable.jsx` y relación de líneas tarifarias existen; todavía hay entrada manual condicionada y selector básico | cerrar Bloque C |
| Selector por descripción/código | plan 8 días | pendiente | `ItemsTable.jsx` sigue usando `<select>` simple | implementar selector buscable en Bloque C |
| Cantidades/precios decimales | plan 8 días + código real | pendiente | BD y modelo admiten importes, pero `StorePedidoRequest` y `ItemsTable.jsx` siguen forzando `cantidad`/`unidades_solicitadas` a entero operativo | alinear frontend y backend en Bloque C |
| Exportación Moeve PDF + CSV + cuadro ARIBA | plan 8 días | pendiente | `FacturaController` solo exporta CSV de facturas; no aparece exportación de pedido Moeve; `pdfkit` está instalado pero no usado para este caso | ejecutar Bloque D |
| Repsol tarifa única | plan 8 días + importador real | parcial | `RepsolExcelImporter.php` importa sobre un contrato/tarifario único de estrategia `2023-2027`; falta validación funcional definitiva sobre datos reales | revisar datos reales y consolidar en Bloque C/F si hace falta |
| Estados automáticos | plan 8 días + código real | parcial | existen estados vivos y `ClosureDashboardService`; falta derivación progresiva trabajo/pedido económico | ejecutar Bloque E |
| Cancelados visibles al final | código real + plan | parcial | `TrabajoController@index` y `TrabajosExcelView.jsx` mantienen `cancelado` al final; no está homogeneizado todavía para toda la operativa nueva | replicar criterio donde falte |
| Fecha de terminación manual | código real + plan | completo | `TrabajoController` y requests soportan `fecha_terminacion` manual | mantener |
| Estaciones por código y nombre | código real + backlog | completo | módulo de estaciones ya prioriza código, nombre, municipio y provincia; vistas y API ya lo soportan | reutilizar patrón en operativa |
| Maestros sin borrado real | código real + backlog | completo | contratos, tarifarios, estaciones, clientes y sociedades operan con activación/desactivación o cancelación | mantener sin hard delete |
| Contexto distinto de rol | código real + plan | completo | `User.php`, `usuario_contextos`, roles y permisos separados; modelo ya vive así | mantener como restricción arquitectónica |
| Técnico no administrador de negocio | plan + código real | parcial | `User.php` ya separa capacidades técnicas, pero el naming y parte del imaginario siguen en torno a `admin` | revisar naming y fugas de permisos en Bloque F |
| Contabilidad controla facturas | código real + plan | parcial | permisos y flujo de facturas ya existen; el backlog refleja control contable mayoritario | revisar fugas y dejar la frontera cerrada en Bloque F |
| Dirección cierre/revisión | código real + plan | parcial | `ClosureDashboardService`, permisos de dirección y panel propio existen | mover el cierre a diagnóstico secundario y revisar reapertura en Bloque E/F |
| Auditoría/concurrencia por campo | código real + plan | parcial | `AuditLog`, `AuditLogger`, `useOptimisticField`, `ConflictDialog` muy claros en `Trabajos`; no homogéneos todavía en `Pedidos`/facturación | extender patrón en Bloque F |
| Panel de cierre como diagnóstico secundario | plan 8 días | pendiente | el panel existe como módulo propio y sigue visible como área específica | rebajar su protagonismo en Bloque E/G |
| Interfaz compacta tipo Excel | código real + plan | parcial | `Trabajos`, `Pedidos`, `Facturas`, `Estaciones` ya tienen modo Excel/compacto; falta consolidación final como estándar operativo | cerrar Bloque G |

## 5. Estado por bloque funcional

### Bloque A — Trabajos como centro operativo

**Qué se pidió**

- Consolidar `Trabajos` como pantalla operativa principal.
- Reducir la necesidad de arrancar la jornada desde módulos paralelos.
- Distinguir claramente trabajos con pedido y sin pedido.
- Mantener visible el pedido principal y el resumen económico.
- Preparar un hueco visual para “Crear pedido desde trabajo”, sin lógica todavía.

**Qué había antes**

- `TrabajoController@index` ya traía pedido principal, conteo de pedidos y agregados económicos.
- `TrabajoResource` ya exponía `numero_pedido_principal`, `id_pedido_principal`, `fecha_solicitud_pedido`, `importe_pedido_total`, `importe_solicitado_total` e `importe_facturado_total`.
- `TrabajosExcelView.jsx` ya tenía edición inline, `useOptimisticField` y `ConflictDialog`.
- La tabla ya ordenaba cancelados al final.
- El caso “sin pedido” seguía siendo menos explícito de lo deseable.

**Qué se hizo en Bloque A**

- Se reforzó visualmente la columna de pedido en `TrabajosExcelView.jsx`.
- Se cambió la lectura a `Pedido principal`.
- Se muestra `Sin pedido` de forma explícita cuando no existe pedido principal.
- Se añadió una ayuda visual discreta para el futuro flujo “Crear pedido desde trabajo”.
- Se ajustó la descripción del `ContextualPageHeader` en `Trabajos/Index.jsx`.
- Se añadió una nota compacta que refuerza que `Trabajos` es la vista operativa principal.

**Archivos modificados**

- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `resources/js/Pages/Trabajos/Index.jsx`
- `docs/02_CLIENTE/tareasComparar.md`

**Validaciones**

- `npm run build` en verde.
- Revisión estática de compatibilidad con inline edit, `ConflictDialog`, filtros y ordenación de cancelados.

**Estado actual**

- Parcial.

**Qué falta para cerrar el bloque al 100%**

- Validación manual final por contexto/rol en navegador.
- Confirmar visualmente que `Pedidos` ya no actúa de hecho como puerta principal recomendada.
- Decidir si hace falta un ajuste mínimo adicional de navegación sin tocar todavía la lógica del Bloque B.

### Bloque B — Pedido desde trabajo

**Qué se pide**

- Crear y gestionar el pedido desde `Trabajo`.
- Mantener `Trabajo -> hasMany(Pedido)` con pedido principal visible y pedidos extra como excepción.
- Evitar que `Pedidos` sea el flujo obligatorio de alta.

**Qué existe ya**

- El trabajo ya conoce su pedido principal.
- El detalle de trabajo ya tiene soporte de lectura para pedido principal y resumen económico.
- `Pedidos` sigue teniendo rutas y vistas propias en `routes/web/operativa.php`.

**Qué falta**

- Alta real de pedido desde trabajo.
- Reasignación o marcación clara del pedido principal.
- Presentación controlada de varios pedidos sin romper el flujo diario.

**Riesgos**

- Duplicar flujos entre `Trabajos` y `Pedidos`.
- Introducir lógica de pedido antes de cerrar la lectura operativa de `Trabajos`.

**Archivos probables**

- `routes/web/operativa.php`
- `app/Http/Controllers/Api/PedidoController.php`
- `resources/js/Pages/Trabajos/Index.jsx`
- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `resources/js/Pages/Pedidos/Form.jsx`
- `resources/js/navigation/sidebar.js`

**Criterios de aceptación**

- Un usuario operativo puede crear pedido desde trabajo.
- Un trabajo puede seguir existiendo sin pedido.
- Si hay varios pedidos, la UI deja claro cuál es el principal.
- `Pedidos` sigue existiendo, pero deja de ser la puerta principal.

### Bloque C — Líneas de pedido, tarifario y decimales

**Qué se pide**

- Que las líneas salgan del tarifario.
- Que el selector permita buscar por descripción y código.
- Que cantidad y precio acepten decimales extremo a extremo.

**Qué existe ya**

- `PedidoItem` ya está ligado a líneas tarifarias.
- `ItemsTable.jsx` ya muestra líneas y autocompleta parte del contenido.
- `precio_unitario` ya opera con precisión decimal visible en UI.

**Qué falta**

- Selector buscable real por descripción/código.
- Eliminar la restricción de enteros para `cantidad` y `unidades_solicitadas`.
- Verificar consistencia con cálculo económico y facturación parcial.

**Riesgos**

- Romper compatibilidad con validaciones actuales.
- Descuadrar importes si el recalculo no queda homogéneo.

**Archivos probables**

- `app/Http/Requests/Api/StorePedidoRequest.php`
- `app/Http/Requests/Api/UpdatePedidoRequest.php`
- `resources/js/Components/ui/ItemsTable.jsx`
- `resources/js/Pages/Pedidos/Form.jsx`
- `resources/js/Components/ui/PedidosExcelView.jsx`

**Criterios de aceptación**

- Selección rápida de línea por código o descripción.
- Cantidad y precio guardan decimales sin contradicción UI/backend.
- Totales de línea y pedido siguen cuadrando.

### Bloque D — Exportación Moeve PDF + CSV + cuadro ARIBA

**Qué se pide**

- Generar desde pedido la salida documental mínima Moeve: PDF + CSV + cuadro ARIBA.

**Qué existe ya**

- Exportación CSV de facturas.
- Dependencia `pdfkit` instalada.
- Infraestructura general de exportación parcial.

**Qué falta**

- Servicio específico de exportación de pedido Moeve.
- Plantilla PDF.
- CSV real de pedido.
- Cuadro ARIBA validado según el formato final requerido.

**Riesgos**

- Formato funcional no cerrado del todo.
- Intentar reutilizar exportaciones de facturas o cierre para un flujo que es distinto.

**Archivos probables**

- `app/Http/Controllers/Api/PedidoController.php`
- servicio nuevo de exportación de pedido Moeve
- plantilla PDF nueva
- `resources/js/Pages/Pedidos/*` o `Trabajos` para el acceso al botón

**Criterios de aceptación**

- Desde un pedido Moeve se obtiene la salida documental mínima completa.
- El contenido de contrato, estación, pedido y líneas es coherente.

### Bloque E — Estados automáticos, facturación y cierre

**Qué se pide**

- Derivar estados automáticos mínimos viables.
- Consolidar resumen económico trabajo/pedido/factura.
- Rebajar el cierre a función secundaria de diagnóstico y revisión.

**Qué existe ya**

- Estados vivos de trabajo ya normalizados.
- Facturación madura por `factura_items`.
- `ClosureDashboardService` ya calcula parte del estado económico.

**Qué falta**

- Derivación progresiva automática de estados de trabajo/pedido.
- Criterio claro de `pendiente_facturar`, `facturado`, `finalizado`.
- Reposicionar el panel de cierre en jerarquía funcional.

**Riesgos**

- Colisión con estados históricos.
- Cambios demasiado amplios en filtros y reporting.

**Archivos probables**

- `app/Models/Trabajo.php`
- `app/Models/Pedido.php`
- `app/Http/Controllers/Api/TrabajoController.php`
- `app/Http/Controllers/Api/PedidoController.php`
- `app/Http/Controllers/Api/FacturaController.php`
- `app/Services/ClosureDashboardService.php`
- `resources/js/Pages/Cierre/Dashboard.jsx`

**Criterios de aceptación**

- El estado visible refleja la realidad operativa/económica sin etiquetado manual arbitrario.
- El cierre sigue existiendo, pero deja de presentarse como centro operativo.

### Bloque F — Roles, técnico, maestros y auditoría

**Qué se pide**

- Mantener separados contexto y rol.
- Garantizar que técnico no actúa como administrador de negocio.
- Reforzar gobierno de maestros y auditoría.
- Extender concurrencia/auditoría a campos críticos fuera de `Trabajos`.

**Qué existe ya**

- Contexto y rol están separados en el modelo.
- El rol técnico ya está muy acotado en runtime.
- Los maestros críticos ya trabajan con activación/desactivación.
- La auditoría y el conflicto por campo ya están bien resueltos en `Trabajos`.

**Qué falta**

- Cerrar naming/confusión del `admin` técnico.
- Revisar fugas de permisos de negocio.
- Homogeneizar auditoría y, donde entre en plazo, concurrencia en `Pedidos` y facturación.

**Riesgos**

- Endurecer demasiado y romper accesos reales.
- Confundir documentación histórica con el modelo vigente de permisos.

**Archivos probables**

- `app/Models/User.php`
- seeders de permisos/roles si se aprueban en su bloque
- `app/Models/AuditLog.php`
- `app/Services/AuditLogger.php`
- controladores de maestros y de pedido/factura

**Criterios de aceptación**

- El técnico no puede operar negocio fuera de su frontera.
- Dirección y contabilidad conservan sus funciones.
- La auditoría de campos críticos queda ampliada o, como mínimo, claramente planificada y delimitada.

### Bloque G — Compactación visual, pruebas y documentación

**Qué se pide**

- Consolidar la interfaz compacta tipo Excel como estándar operativo.
- Ejecutar pruebas integrales por flujo y por rol.
- Cerrar documentación final de la nueva ola.

**Qué existe ya**

- Modo Excel/compacto ya desplegado en los módulos principales.
- El Bloque A ya refuerza `Trabajos` como entrada operativa.
- El backlog y el plan técnico ya existen.

**Qué falta**

- Ajuste final visual por densidad y jerarquía.
- Validación manual cruzada de bloques.
- Cierre documental final tras los bloques B-F.

**Riesgos**

- Convertir la compactación en rediseño estético.
- Dar por cerrada la ola sin una pasada manual real por contexto/rol.

**Archivos probables**

- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `resources/js/Components/ui/PedidosExcelView.jsx`
- `resources/js/Components/ui/FacturasExcelView.jsx`
- `resources/js/Components/ui/EstacionesExcelView.jsx`
- `resources/js/navigation/sidebar.js`
- `docs/02_CLIENTE/*`

**Criterios de aceptación**

- Flujo compacto, sin regresiones graves.
- Validación manual y técnica trazable.
- Documentación final coherente con el código real.

## 6. Listado funcional tipo checklist

| ID | Funcionalidad | Estado | Porcentaje orientativo de completitud | Evidencia | Pendiente exacto | Bloque asociado |
| --- | --- | --- | ---: | --- | --- | --- |
| F-01 | Trabajos como centro operativo | Parcial | 70% | `TrabajoController@index`, `TrabajoResource`, `TrabajosExcelView.jsx`, Bloque A | validación manual final y pequeño rebalanceo de navegación si hiciera falta | A |
| F-02 | Trabajo puede existir sin pedido | Completo | 95% | `TrabajoResource`, `TrabajosExcelView.jsx` | mantener compatibilidad en siguientes bloques | A/B |
| F-03 | Pedido principal visible en trabajo | Parcial | 75% | `numero_pedido_principal`, `id_pedido_principal`, columna `Pedido principal` | convertirlo en centro de acción, no solo de lectura | A/B |
| F-04 | Pedidos extra como excepción | Parcial | 60% | `pedidos_count`, relación `hasMany`, plan técnico | exponer mejor la excepción múltiple en UI | B |
| F-05 | Crear pedido desde trabajo | Pendiente | 30% | el plan lo define; el código actual aún usa `Pedidos` separado | alta real desde `Trabajos` | B |
| F-06 | Líneas salen del tarifario | Parcial | 75% | `PedidoItem`, requests, `ItemsTable.jsx` | reforzar selector y cerrar restricciones inconsistentes | C |
| F-07 | Selector por descripción/código | Pendiente | 25% | `ItemsTable.jsx` con `<select>` simple | selector buscable real | C |
| F-08 | Cantidades/precios decimales | Pendiente | 20% | requests fuerzan entero operativo en cantidad/unidades | alinear validación y UI a decimal | C |
| F-09 | Exportación Moeve PDF + CSV + ARIBA | Pendiente | 0% | ausencia de servicio/export dedicado | implementación completa | D |
| F-10 | Repsol tarifa única | Parcial | 65% | `RepsolExcelImporter.php`, plan técnico | validación funcional definitiva sobre datos reales | C/F |
| F-11 | Estados automáticos mínimos | Parcial | 35% | estados vivos, `ClosureDashboardService` | derivación real de trabajo/pedido económico | E |
| F-12 | Cancelados visibles al final | Parcial | 85% | `TrabajoController@index`, `TrabajosExcelView.jsx` | homogeneizar criterio en otros módulos si procede | A/E/G |
| F-13 | Fecha de terminación manual | Completo | 95% | soporte vivo en trabajo | mantener | E |
| F-14 | Estaciones por código y nombre | Completo | 90% | módulo estaciones ya refinado | reutilización homogénea en operativa | G |
| F-15 | Maestros sin borrado real | Completo | 90% | activación/desactivación consolidada | mantener disciplina operativa | F |
| F-16 | Contexto distinto de rol | Completo | 90% | modelo de usuario, contextos y permisos | mantener como invariante | F |
| F-17 | Técnico no administrador de negocio | Parcial | 70% | `User.php`, fronteras de admin técnico | limpiar naming y revisar fugas | F |
| F-18 | Contabilidad controla facturas | Parcial | 85% | módulo facturas y permisos actuales | revisión fina de permisos y superficies de edición | E/F |
| F-19 | Dirección cierre/revisión | Parcial | 65% | panel de cierre y permisos existentes | definir rol secundario y reapertura | E/F |
| F-20 | Auditoría/concurrencia por campo | Parcial | 60% | `AuditLog`, `AuditLogger`, `useOptimisticField`, `ConflictDialog` | extender patrón fuera de `Trabajos` | F |
| F-21 | Panel de cierre como diagnóstico secundario | Pendiente | 20% | existe panel propio, pero aún no rebajado | reposicionamiento funcional y visual | E/G |
| F-22 | Interfaz compacta tipo Excel | Parcial | 80% | vistas compactas en módulos principales | consolidación final y pruebas integrales | G |

## 7. Qué se ha hecho ya en esta nueva ola

- Se creó el plan técnico base en `docs/02_CLIENTE/plan_8_dias_nueva_version_erp_ciete.md`.
- Se refinó el plan con la sección `10.1 Orden real de ejecución aprobado`, separando bloques A-G.
- Se ejecutó parcialmente el Bloque A para reforzar `Trabajos` como centro operativo.
- Archivos tocados en Bloque A:
  - `resources/js/Components/ui/TrabajosExcelView.jsx`
  - `resources/js/Pages/Trabajos/Index.jsx`
  - `docs/02_CLIENTE/tareasComparar.md`
- Se ejecutó `npm run build` y quedó en verde en el cierre documental del Bloque A.
- Se actualizó el backlog vivo con la nota de ejecución parcial del Bloque A.
- El estado actual de esta nueva ola es:
  - plan técnico creado,
  - plan refinado por bloques,
  - Bloque A movido parcialmente,
  - Bloques B-G todavía sin ejecutar en código.

## 8. Qué queda pendiente por orden recomendado

1. **Cierre visual/manual del Bloque A si falta**
   Objetivo: validar en navegador que `Trabajos` ya funciona como entrada operativa principal y que `Sin pedido` / pedido principal se leen bien por contexto y rol.
   Archivos probables: `resources/js/Components/ui/TrabajosExcelView.jsx`, `resources/js/Pages/Trabajos/Index.jsx`, `resources/js/navigation/sidebar.js`.
   Riesgo: dar por cerrado el bloque sin validación visual real.
   Validación mínima: revisión manual Moeve/Repsol, filtros, inline edit, conflicto por campo, cancelados al final.

2. **Bloque B — Pedido desde trabajo**
   Objetivo: crear y gestionar pedido desde `Trabajo`, manteniendo trabajo sin pedido y el modelo de pedido principal.
   Archivos probables: `routes/web/operativa.php`, `app/Http/Controllers/Api/PedidoController.php`, `resources/js/Pages/Trabajos/Index.jsx`, `resources/js/Components/ui/TrabajosExcelView.jsx`, `resources/js/Pages/Pedidos/Form.jsx`.
   Riesgo: duplicar flujos entre `Trabajos` y `Pedidos`.
   Validación mínima: crear pedido desde trabajo, mantener trabajo sin pedido, distinguir pedido principal.

3. **Bloque C — Líneas, tarifario y decimales**
   Objetivo: cerrar selector buscable y decimales extremo a extremo.
   Archivos probables: `StorePedidoRequest.php`, `UpdatePedidoRequest.php`, `ItemsTable.jsx`, `Pedidos/Form.jsx`, `PedidosExcelView.jsx`.
   Riesgo: desajustes de cálculo económico o facturación parcial.
   Validación mínima: línea tarifaria buscable, cantidad decimal, precio decimal, totales correctos.

4. **Bloque D — Exportación Moeve**
   Objetivo: generar PDF + CSV + cuadro ARIBA desde pedido Moeve.
   Archivos probables: `PedidoController.php`, servicio nuevo de exportación, plantilla PDF, acceso UI desde pedido o trabajo.
   Riesgo: formato funcional todavía no totalmente cerrado.
   Validación mínima: caso de prueba real con salida documental coherente.

5. **Bloque E — Estados/facturación/cierre**
   Objetivo: derivar estados automáticos mínimos y rebajar el cierre a panel secundario.
   Archivos probables: `Trabajo.php`, `Pedido.php`, controladores de trabajo/pedido/factura, `ClosureDashboardService.php`, `Dashboard.jsx` de cierre.
   Riesgo: romper filtros/estados históricos.
   Validación mínima: escenarios sin pedido, pedido en preparación, pedido recibido, parcial, completo, finalizado.

6. **Bloque F — Roles/auditoría/maestros**
   Objetivo: endurecer frontera técnico/negocio y extender auditoría a puntos críticos.
   Archivos probables: `User.php`, permisos/roles, `AuditLogger`, controladores de maestros, pedido y factura.
   Riesgo: cerrar permisos de más y bloquear operativa.
   Validación mínima: matriz por rol/contexto y revisión de trazas de auditoría.

7. **Bloque G — Pruebas/documentación**
   Objetivo: consolidar visual compacto, ejecutar pruebas integrales y cerrar documentación final.
   Archivos probables: vistas Excel/compactas, `sidebar.js`, documentación `docs/02_CLIENTE/*`.
   Riesgo: convertir el cierre en rediseño o changelog infinito.
   Validación mínima: build, flujos manuales por rol, documentación final alineada con código.

## 9. Documentación posiblemente desfasada o peligrosa

| Documento | Motivo de posible desfase | Acción sugerida | Riesgo de uso incorrecto |
| --- | --- | --- | --- |
| `docs/07_REVISIONES_DOCUMENTALES/REVISION_DOCUMENTAL_02_05_2026.md` | Declara como fuente de verdad principal `DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`, jerarquía ya superada por el plan nuevo y el código real | mantener pero marcar como desfasado | alto: puede hacer que se implemente contra una síntesis antigua en lugar del plan vigente |
| `docs/02_CLIENTE/historicos/Analisis_Reunion_CIETE_Plan_2_Sprints_2026-05-04.md` | documento histórico de sprintado temprano | archivar como contexto histórico | medio |
| `docs/02_CLIENTE/historicos/Tareas_Pablo_01_02_Mayo_2026.md` | backlog temprano, previo a la nueva ola y al refinado por bloques | archivar como histórico | medio |
| `docs/05-SPRINTS/00_Sprints_General.md` y sprint docs relacionados | varios puntos siguen anclados a la síntesis funcional antigua y a una lectura por sprints previa a este plan | mantener con advertencia o revisar más adelante | medio-alto |
| `docs/03_API_ERP/historicos/05_Plan_Reestructuracion_BBDD.md` | puede empujar a rediseños estructurales incompatibles con la estrategia incremental actual | mantener como histórico, no usar para esta ola | alto |
| `docs/04_DISENO_UI/historicos/01_Guia_Estilos_y_Capturas.md` | guía visual histórica que puede no reflejar la prioridad actual de interfaz compacta tipo Excel | mantener como referencia visual histórica | medio |
| `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md` | no es inválido, pero ya no debe tratarse como verdad principal; hoy es una síntesis interpretativa secundaria | mantener, pero señalar su rol subordinado | alto si un agente la usa como fuente principal |
| `docs/02_CLIENTE/tareasComparar.md` | mezcla estado real con varias olas, porcentajes y cierres históricos; útil, pero no es documento limpio de nueva versión | mantener y seguir actualizando | medio: puede inducir a leer como plan único lo que es un backlog acumulativo |

## 10. Riesgos antes de seguir implementando

**Riesgos de código**

- `Pedidos` sigue siendo un flujo paralelo real; si Bloque B se hace rápido y sin criterio, puede duplicar comportamiento.
- `PedidoItem` sigue teniendo restricciones de entero en puntos críticos; tocar solo frontend rompería coherencia.
- El cierre sigue siendo módulo propio con peso funcional todavía visible.

**Riesgos de documentación**

- Hay demasiados documentos históricos que todavía hablan como si `DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md` fuera la fuente principal.
- `tareasComparar.md` sigue siendo imprescindible, pero ya es demasiado acumulativo para servir por sí solo como control limpio de esta nueva ola.

**Riesgos de datos/importaciones**

- Los seeders privados usan contextos `30/31` en puntos sensibles; eso puede entrar en conflicto con supuestos de demo/contexto `1/2/3`.
- Repsol tarifa única parece alineado técnicamente, pero sigue necesitando validación funcional real.
- La exportación Moeve no puede validarse solo por estructura; necesita formato real acordado.

**Riesgos de permisos**

- El rol técnico sigue arrastrando naming histórico alrededor de `admin`.
- Endurecer permisos sin matriz final puede romper flujos de dirección, contabilidad o soporte técnico.

**Riesgos de alcance**

- Intentar mezclar Bloque B con C o D aumentaría riesgo y reduciría trazabilidad.
- Tocar ahora importaciones, DB, exportaciones o cierres profundos rompería la estrategia incremental acordada.

**Riesgos de UI**

- Si la compactación se convierte en rediseño, se perderá tiempo y se pondrá en riesgo la productividad real.
- Falta aún una validación manual de `Trabajos` como centro operativo por contexto y rol.

## 11. Recomendación inmediata

Lo inmediato no es abrir código nuevo a ciegas, sino revisar manualmente este documento y contrastarlo con Pablo antes de seguir. Si esta fotografía queda aprobada, el siguiente paso correcto es ejecutar el Bloque B de forma aislada: pedido desde trabajo, sin mezclar todavía líneas, decimales, exportación Moeve ni lógica de cierre.

Si durante la revisión aparece una contradicción entre este documento, el plan técnico o el código real, debe corregirse primero la documentación de control y solo después tocar código. El orden correcto ahora es: validar la foto, aprobarla y entonces continuar implementación incremental.

## 12. Anexo — Archivos revisados

**Documentación**

- `docs/02_CLIENTE/plan_8_dias_nueva_version_erp_ciete.md`
- `docs/02_CLIENTE/tareasComparar.md`
- `docs/07_REVISIONES_DOCUMENTALES/REVISION_DOCUMENTAL_02_05_2026.md`
- `docs/02_CLIENTE/historicos/Analisis_Reunion_CIETE_Plan_2_Sprints_2026-05-04.md`
- `docs/02_CLIENTE/historicos/Tareas_Pablo_01_02_Mayo_2026.md`
- `docs/05-SPRINTS/00_Sprints_General.md`
- `docs/03_API_ERP/historicos/05_Plan_Reestructuracion_BBDD.md`
- `docs/04_DISENO_UI/historicos/01_Guia_Estilos_y_Capturas.md`
- `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`

**Frontend**

- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `resources/js/Pages/Trabajos/Index.jsx`
- `resources/js/navigation/sidebar.js`
- `resources/js/Components/ui/ItemsTable.jsx`

**Backend**

- `app/Http/Controllers/Api/TrabajoController.php`
- `app/Http/Resources/Api/TrabajoResource.php`
- `app/Http/Requests/Api/StorePedidoRequest.php`
- `app/Http/Controllers/Api/FacturaController.php`
- `app/Services/ClosureDashboardService.php`
- `app/Services/Importacion/RepsolExcelImporter.php`
- `app/Models/User.php`
- `routes/web/operativa.php`
- `database/seeders/private/CieteRealContratosTarifariosSeeder.php`

**Tests y validaciones referenciadas**

- Las validaciones y tests citados en este documento se toman de la evidencia ya registrada en `docs/02_CLIENTE/tareasComparar.md` y del cierre parcial del Bloque A.
- En esta consolidación no se ha ejecutado una batería nueva de tests ni se ha tocado código.
