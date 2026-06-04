---
estado: borrador_operativo
fecha: 2026-05-27
origen: reunión CIETE 2026-05-19 + correos César 2026-05-20 + auditoría código ERP
objetivo: planificar la nueva versión del ERP CIETE en 8 días sin ejecutar implementación todavía
---

# Plan Técnico ERP CIETE - Auditoría y ejecución incremental en 8 días

## 1. Resumen ejecutivo

El ERP ya tiene una base funcional real y bastante más madura de lo que aparenta: existen módulos de Trabajos, Pedidos, Facturas, Tarifarios, Contratos, Estaciones, Roles/Contextos, Auditoría, Cierre e Importaciones. El módulo más sólido hoy es Trabajos, con vista tipo Excel, edición inline y control de concurrencia por campo; también existe separación real entre contexto y rol, y los maestros críticos ya trabajan con activación/desactivación.

La brecha principal frente al documento funcional no es “falta de ERP”, sino de alineación operativa. Pedidos sigue funcionando demasiado como módulo paralelo, la creación desde trabajo no es el flujo dominante, las líneas de pedido todavía fuerzan cantidades enteras aunque base de datos y modelo ya admiten decimales, los estados siguen demasiado manuales y la exportación específica Moeve PDF + CSV + cuadro ARIBA no aparece implementada. La recomendación es una ejecución incremental, apoyándose en lo existente y evitando reestructuras grandes o cambios destructivos de base de datos.

## 2. Mapa de módulos encontrados

| Módulo                   | Archivos principales encontrados                                                                                   | Estado actual              | Observaciones                                                                                                |
| ------------------------ | ------------------------------------------------------------------------------------------------------------------ | -------------------------- | ------------------------------------------------------------------------------------------------------------ |
| Trabajos                 | TrabajoController.php, Trabajo.php, resources/js/Pages/Trabajos/\*, TrabajosExcelView.jsx                          | Maduro                     | Centro funcional más avanzado; ya tiene inline edit, orden de cancelados y conflicto por campo.              |
| Pedidos                  | Api/PedidoController.php, Pedido.php, PedidoItem.php, resources/js/Pages/Pedidos/\*, ItemsTable.jsx                | Operativo pero desalineado | Existe relación real con trabajo y líneas tarifarias, pero el flujo sigue separado de trabajo.               |
| Líneas de pedido         | PedidoItem.php, StorePedidoRequest.php, UpdatePedidoRequest.php, ItemsTable.jsx                                    | Incompleto                 | Autocompletado básico sí; búsqueda por descripción/código y decimales completos no.                          |
| Facturas                 | Api/FacturaController.php, Factura.php, FacturaItem.php, resources/js/Pages/Facturas/\*                            | Maduro                     | Soporta parcialidad por factura_items; contabilidad ya tiene base clara.                                     |
| Tarifarios/Contratos     | Tarifario*.php, Contrato*.php, tarifario_lineas                                                                    | Maduro                     | Hay maestros reales, activos/inactivos y validación contextual.                                              |
| Estaciones               | EstacionController.php, EstacionServicio.php, EstacionMoeveExt.php, EstacionRepsolExt.php, EstacionesExcelView.jsx | Maduro                     | Búsqueda y segregación por contexto bastante alineadas.                                                      |
| Roles/Contextos/Permisos | User.php, Role.php, Permission.php, UsuarioContexto.php, seeders de seguridad                                      | Maduro                     | Contexto y rol están separados; buen punto de partida.                                                       |
| Auditoría                | AuditLog.php, AuditLogger.php, AuditLogController.php                                                              | Parcialmente maduro        | Ya hay auditoría rica; concurrencia por campo está clara en Trabajos pero no homogénea en todos los módulos. |
| Cierre                   | ClosureDashboardController.php, ClosureDashboardService.php, resources/js/Pages/Cierre/Dashboard.jsx               | Funcional                  | Existe como panel propio; debe pasar a diagnóstico secundario.                                               |
| Importaciones            | ImportacionController.php, CieteExcelImportService.php, MoeveExcelImporter.php, RepsolExcelImporter.php            | Funcional con riesgo       | Importa maestros y operativa; hay riesgo de coherencia por contextos privados 30/31.                         |
| Exportaciones            | CSV de facturas y cierre; dependencia pdfkit instalada                                                             | Incompleto                 | No aparece la exportación Moeve de pedido con PDF + CSV + cuadro ARIBA.                                      |
| Layout/UI compacta       | TrabajosExcelView.jsx, PedidosExcelView.jsx, FacturasExcelView.jsx, EstacionesExcelView.jsx                        | Bastante alineado          | Ya existe modo Excel; falta consolidarlo como estándar y compactar flujos.                                   |

## 3. Comparativa funcional contra el documento

| Requisito funcional                                     | Estado en código                                      | Archivos implicados                                                               | Riesgo | Acción recomendada                                                                 |
| ------------------------------------------------------- | ----------------------------------------------------- | --------------------------------------------------------------------------------- | ------ | ---------------------------------------------------------------------------------- |
| Trabajos como centro operativo                          | Existe pero incompleto                                | TrabajoController, TrabajosExcelView, sidebar.js                                  | Medio  | Mantener Trabajos como punto central y absorber acciones de pedido desde ahí.      |
| Pedido creado/asignado desde trabajo                    | Existe parcial                                        | TrabajoController::patchField, Pedidos/Form.jsx, routes/web/operativa.php         | Alto   | Prioridad P0: flujo nativo “crear pedido desde trabajo” y reasignación controlada. |
| Un trabajo puede existir sin pedido                     | Existe y está alineado                                | trabajos + relación pedidos                                                       | Bajo   | Conservar.                                                                         |
| 1 trabajo -> 1 pedido principal, con excepción múltiple | Existe y está alineado técnicamente                   | Trabajo::pedidos(), primerPedido(), pedidos table                                 | Medio  | Exponer un pedido principal visual sin perder hasMany.                             |
| Líneas salen del tarifario                              | Existe y está alineado                                | StorePedidoRequest, UpdatePedidoRequest, ItemsTable.jsx                           | Bajo   | Mantener; reforzar UX.                                                             |
| Buscar línea por descripción y código                   | Existe pero incompleto                                | ItemsTable.jsx, Pedidos/Form.jsx                                                  | Medio  | Sustituir `<select>` por selector buscable.                                        |
| Cantidad y precio con decimales                         | Existe pero contradice la última decisión             | StorePedidoRequest.php, UpdatePedidoRequest.php, Pedidos/Form.jsx, ItemsTable.jsx | Alto   | P0: alinear validación backend/frontend con los decimales ya soportados en BD.     |
| Estados automáticos cuando sea posible                  | Existe pero incompleto                                | Trabajo.php, Pedido.php, controladores y vistas                                   | Alto   | Mover estado a lógica derivada progresiva; empezar por trabajo/pedido económico.   |
| Cancelados visibles al final                            | Existe y está alineado en Trabajos                    | TrabajoController@index, TrabajosExcelView.jsx                                    | Bajo   | Replicar criterio en Pedidos/Facturas si falta homogeneidad.                       |
| Fecha de terminación manual                             | Existe y está alineado                                | Trabajo + formularios                                                             | Bajo   | Mantener.                                                                          |
| Auditoría/cambio por campo                              | Existe pero parcial                                   | AuditLog, AuditLogger, TrabajoController::patchField, useOptimisticField.js       | Medio  | Extender el patrón a Pedidos y campos críticos de facturación.                     |
| Estaciones por código y nombre                          | Existe y está alineado                                | EstacionController, EstacionesExcelView.jsx                                       | Bajo   | Mantener y reutilizar en Trabajos.                                                 |
| Maestros críticos sin borrado real                      | Existe y está alineado                                | controladores de estaciones, contratos, tarifarios, sociedades                    | Bajo   | Mantener; usar desactivación consistente.                                          |
| Contexto y rol no son lo mismo                          | Existe y está alineado                                | User.php, usuario_contextos, usuario_roles                                        | Bajo   | Mantener como restricción arquitectónica.                                          |
| Técnico no administrador de negocio                     | Existe pero con naming confuso                        | User::isTechnicalAdmin(), RolesSeeder                                             | Medio  | Mantener permisos técnicos separados; revisar naming de admin.                     |
| Contabilidad controla facturas                          | Existe y está alineado en gran parte                  | RolPermisosSeeder, FacturaController                                              | Bajo   | Mantener; revisar solo fugas de permisos.                                          |
| Dirección puede revisar/cerrar/reabrir/maestros         | Existe parcial                                        | ClosureDashboard\*, permisos director                                             | Medio  | Mantener cierre y definir reapertura explícita que hoy no aparece localizada.      |
| Moeve PDF + CSV + cuadro ARIBA desde pedido             | No existe                                             | PedidoController, vistas de pedido, servicios de exportación                      | Alto   | P0 de día 5: servicio dedicado y botón desde pedido.                               |
| Repsol con tarifa única                                 | Existe y está razonablemente alineado                 | RepsolExcelImporter.php, maestros de tarifario                                    | Medio  | Validar datos reales importados y evitar duplicidades.                             |
| Interfaz compacta tipo Excel                            | Existe y está bastante alineado                       | vistas Excel de módulos                                                           | Bajo   | Consolidar como estándar por defecto en operativa.                                 |
| Panel de cierre como secundario                         | Existe pero contradice parcialmente la decisión final | ClosureDashboardController, sidebar.js                                            | Medio  | Reposicionarlo como diagnóstico y no como centro de operación.                     |

## 4. Riesgos técnicos detectados

Base de datos: no parece necesario rediseño grande, pero sí migraciones incrementales si se introducen campos derivados o referencias de exportación; el mayor riesgo real es la incoherencia de contextos en seeders privados (30/31 frente a 1/2/3).

Backend: Pedidos y Facturas mezclan capa web y API de forma irregular; eso no rompe hoy, pero complica unificar flujo desde trabajo.

Frontend: hay buen modo Excel, pero el flujo sigue fragmentado entre módulos; el mayor gap está en selector de líneas, estados y creación de pedido desde trabajo.

Permisos: la separación contexto/rol está bien planteada, pero el rol técnico vive bajo slug admin, lo que puede inducir sobrepermisos o confusión funcional.

Importaciones: Repsol parece trabajar ya con tarifa única a nivel importador, pero debe validarse contra datos reales antes de unificar definitivamente los maestros.

Exportaciones: no se ha localizado una implementación reusable para la salida Moeve; esto obliga a diseño específico y validación de formato real.

Experiencia visual: la base compacta existe, pero si se fuerza una remaquetación grande se puede romper productividad; debe ser ajuste fino, no rediseño.

Despliegue: el riesgo mayor es alterar validaciones o estados y afectar datos reales; por eso convienen cambios reversibles, feature-by-feature y sin migraciones destructivas.

## 5. Decisiones ambiguas o pendientes de confirmar

- Si la numeración operativa de trabajo debe pasar de manual a autogenerada por contexto/prefijo en esta iteración de 8 días o quedar solo preparada.
- Si el precio unitario de la línea puede editarlo cualquier perfil operativo o solo dirección/contabilidad/perfiles autorizados.
- Si la “solicitud” debe modelarse solo con importes/fechas dentro de pedido o si necesita entidad propia en una fase posterior.
- Si la reapertura debe vivir en el panel de cierre, en el detalle del trabajo o en ambos; el flujo explícito no aparece localizado en código.
- Si los contextos privados reales 30/31 son definitivos de producción o una herencia temporal de seed/import; esto condiciona importaciones y validación de permisos.

## 6. Arquitectura recomendada antes de programar

Trabajos: mantener Trabajo como agregado operativo principal. Debe concentrar estación, clasificación, responsables, fechas, estado operativo y resumen económico.

Pedidos: mantener relación Trabajo -> hasMany(Pedido), pero exponer visualmente un pedido principal y pedidos extra como excepción. El alta/edición debe arrancar desde trabajo, no desde navegación paralela.

Líneas: conservar PedidoItem ligado a TarifarioLinea; el selector debe ser buscable y autocompletar código, descripción y precio. Cantidad y precio deben usar decimales ya soportados por la BD.

Facturas: conservar Factura + FacturaItem como módulo controlado por contabilidad. El resumen mostrado en trabajo debe ser de consulta; la edición sigue aislada por permisos.

Tarifarios: mantener maestros actuales y tratar Repsol como tarifa única activa. Cualquier ajuste debe ser por activación/desactivación y versionado, no por borrado.

Estaciones: seguir separadas por contexto y con extensiones Moeve/Repsol; la búsqueda debe ser reutilizable en todos los formularios operativos.

Maestros: no reestructurar en grande; reforzar solo reglas de activación/desactivación y restricciones por rol.

Roles/contextos: mantener el modelo actual; el ajuste necesario es de nombrado y endurecimiento de permisos, no de rediseño.

Auditoría: estandarizar el patrón ya existente en Trabajos para campos críticos de Pedido y, si entra en plazo, para importes/estado de Factura.

Exportaciones: encapsular la exportación Moeve en un servicio dedicado invocado desde pedido. No mezclarla con exportaciones de cierre o CSV de facturas.

## 7. Plan de trabajo en 8 días

### Día 1

Objetivo del día: auditoría técnica cerrada, inventario definitivo y decisiones de implementación bloqueadas.

Módulos afectados: arquitectura transversal, permisos, rutas, modelos, importaciones.

Archivos probables a tocar: ninguno en ejecución real; en implementación posterior, docs/02_CLIENTE/tareasComparar.md, routes/web/operativa.php, sidebar.js, requests y controladores clave.

Tareas concretas: validar inventario módulo a módulo; fijar el flujo dominante Trabajo -> Pedido -> Líneas -> Factura; decidir qué cambios son solo frontend, solo backend o mixtos; aislar riesgos P0.

Validaciones: mapa de rutas, relaciones de modelos, estados soportados, permisos por rol y coherencia de importaciones.

Criterios de aceptación: informe de brechas cerrado; backlog P0/P1/P2 decidido; sin decisiones de arquitectura abiertas para días 2-4.

Riesgos: arrastrar supuestos de seeders privados o estados manuales sin documentar.

Qué NO tocar ese día: lógica de negocio, migraciones, exportaciones, compactación visual fina.

Actualización obligatoria: registrar en docs/02_CLIENTE/tareasComparar.md estado de auditoría, archivos revisados, validaciones y porcentaje solo si los criterios del día son verificables.

### Día 2

Objetivo del día: consolidar Trabajos como centro operativo real.

Módulos afectados: Trabajos, navegación, filtros, estaciones.

Archivos probables a tocar: TrabajoController.php, TrabajosExcelView.jsx, resources/js/navigation/sidebar.js.

Tareas concretas: priorizar acciones de pedido dentro de trabajo; reforzar filtros útiles; revisar ordenación y visibilidad; preparar la UI para pedido principal y excepciones.

Validaciones: alta/edición de trabajo, búsqueda de estación, filtros por contexto/estado/responsable, cancelados al final.

Criterios de aceptación: un usuario operativo puede resolver su jornada desde Trabajos sin depender de entrar primero en Pedidos.

Riesgos: romper edición inline o rendimiento de la tabla.

Qué NO tocar ese día: facturación, exportación Moeve, migraciones de líneas.

Actualización obligatoria: anotar tareas ejecutadas, archivos tocados, evidencias de validación y avance.

### Día 3

Objetivo del día: pedido creado y gestionado desde trabajo.

Módulos afectados: Trabajos, Pedidos, rutas operativas.

Archivos probables a tocar: routes/web/operativa.php, Api/PedidoController.php, TrabajosExcelView.jsx, Pedidos/Form.jsx.

Tareas concretas: crear flujo “crear pedido desde trabajo”; fijar pedido principal; permitir pedido extra sin romper hasMany; reducir protagonismo del módulo Pedidos en la navegación operativa.

Validaciones: crear trabajo sin pedido, crear pedido desde trabajo, reasignar pedido principal, mantener visibilidad de pedidos ya existentes.

Criterios de aceptación: el alta de pedido no exige navegar a un CRUD independiente salvo casos excepcionales.

Riesgos: duplicidad temporal entre flujo antiguo y nuevo.

Qué NO tocar ese día: selector de líneas avanzado, exportaciones, estados automáticos complejos.

Actualización obligatoria: documentar flujo final elegido y compatibilidad con pedidos históricos.

### Día 4

Objetivo del día: líneas de pedido, tarifario y decimales alineados con negocio.

Módulos afectados: Pedidos, PedidoItem, Tarifarios.

Archivos probables a tocar: StorePedidoRequest.php, UpdatePedidoRequest.php, ItemsTable.jsx, Pedidos/Form.jsx, PedidosExcelView.jsx.

Tareas concretas: permitir decimales en cantidad y unidades; reemplazar selector simple por búsqueda por descripción y código; mantener autocompletado de código/precio; recalcular totales de línea y pedido.

Validaciones: alta y edición de líneas con decimales; filtro por descripción/código; bloqueo de líneas fuera del tarifario/contrato.

Criterios de aceptación: una línea tarifaria puede seleccionarse rápido y guardar cantidades/precios decimales sin contradicción entre UI y backend.

Riesgos: inconsistencias con importaciones o facturación parcial si no se recalculan bien los importes.

Qué NO tocar ese día: generación documental Moeve, permisos de facturación.

Actualización obligatoria: dejar constancia de validaciones con líneas decimales y cálculo correcto.

### Día 5

Objetivo del día: exportación Moeve PDF + CSV + cuadro ARIBA desde pedido.

Módulos afectados: Pedidos, exportación documental, auditoría.

Archivos probables a tocar: Api/PedidoController.php, servicio nuevo de exportación de pedido Moeve, vista/plantilla PDF, Trabajos/Pedidos UI para botón de exportación.

Tareas concretas: generar exportación desde pedido; mapear datos de trabajo, estación, contrato y líneas; producir PDF, CSV y cuadro copiable; registrar auditoría de exportación.

Validaciones: caso La Senyera con datos del ejemplo; coherencia de importes, códigos y contrato; descarga o preview correcta.

Criterios de aceptación: desde un pedido Moeve se obtiene la salida documental mínima requerida para el correo operativo.

Riesgos: formato real de CSV/ARIBA no totalmente cerrado; dependencia de contenido exacto del ejemplo.

Qué NO tocar ese día: reglas avanzadas de facturación o cierre.

Actualización obligatoria: documentar formato implementado, limitaciones y evidencias del caso de prueba.

### Día 6

Objetivo del día: facturación y estados automáticos mínimos viables.

Módulos afectados: Facturas, Trabajos, Pedidos.

Archivos probables a tocar: FacturaController.php, TrabajoController.php, PedidoController.php, recursos/vistas de trabajos y facturas.

Tareas concretas: consolidar resumen pedido/solicitado/facturado; derivar estados automáticos prioritarios; mantener edición de facturas en contabilidad; exponer solo consulta donde corresponda.

Validaciones: sin pedido, pedido en preparación, recibido, facturado parcial, facturado completo, terminado, cancelado.

Criterios de aceptación: el estado visible del trabajo refleja razonablemente la realidad operativa y económica sin depender de etiquetado manual arbitrario.

Riesgos: choque con estados históricos guardados y con filtros existentes.

Qué NO tocar ese día: compactación visual global o redefinición completa del cierre.

Actualización obligatoria: registrar reglas de estado aplicadas y evidencia de escenarios cubiertos.

### Día 7

Objetivo del día: roles/contextos, perfil técnico, maestros y auditoría.

Módulos afectados: seguridad, maestros, auditoría, cierre.

Archivos probables a tocar: User.php, seeders de roles/permisos si aplica, controladores de maestros, AuditLogger y flujos de pedido/factura.

Tareas concretas: endurecer separación entre técnico y negocio; revisar permisos de contabilidad/dirección; homogeneizar bajas lógicas; extender auditoría o conflicto por campo a puntos críticos que entren en plazo.

Validaciones: matriz de permisos por rol/contexto; edición protegida de maestros; consulta de facturas por ejecución si procede; cierre/revisión por dirección.

Criterios de aceptación: ningún perfil operativo puede tocar maestros críticos o facturación fuera de su ámbito; dirección y contabilidad conservan sus capacidades.

Riesgos: romper accesos actuales por exceso de endurecimiento.

Qué NO tocar ese día: rediseño visual global o importaciones masivas.

Actualización obligatoria: dejar trazabilidad de permisos revisados, matrices y pruebas por usuario/rol.

### Día 8

Objetivo del día: compactación visual, pruebas integrales, cierre funcional y documentación.

Módulos afectados: vistas operativas, cierre, documentación.

Archivos probables a tocar: TrabajosExcelView.jsx, PedidosExcelView.jsx, FacturasExcelView.jsx, EstacionesExcelView.jsx, ClosureDashboard\*, docs/02_CLIENTE/tareasComparar.md.

Tareas concretas: ajustar densidad visual y filtros; rebajar el panel de cierre a diagnóstico secundario; ejecutar pruebas de flujos Moeve/Repsol; cerrar backlog ejecutado y documentar pendientes.

Validaciones: jornada completa por rol; trabajo sin pedido; trabajo con pedido Moeve y exportación; flujo Repsol con tarifa única; facturación parcial; cancelación visible al final.

Criterios de aceptación: versión compacta, operativa y verificable sin regresión grave en módulos existentes.

Riesgos: convertir la compactación en rediseño estético y perder tiempo de negocio.

Qué NO tocar ese día: cambios de esquema no imprescindibles o refactors internos amplios.

Actualización obligatoria: cierre del backlog maestro con tareas, archivos, validaciones y porcentaje final verificable.

## 8. Backlog técnico priorizado

P0 obligatorio: pedido desde trabajo; decimales en líneas/pedido; selector de líneas por descripción/código; exportación Moeve desde pedido; estados automáticos mínimos de trabajo/pedido; revisión de permisos críticos.

P1 importante: reposicionar Pedidos y Cierre en navegación/flujo; extender auditoría/concurrencia a pedido; revisar reapertura explícita; consolidar resumen económico en trabajo.

P2 posterior: numeración automática por contexto/prefijo si no entra segura en 8 días; homogeneización total de estados históricos; mejoras extra de reporting y exportaciones adicionales.

Deuda técnica: mezcla web/API en Pedidos y Facturas; naming del rol técnico como admin; posibles duplicidades de tarifa/importación; diferencias entre seeders demo y privados.

Tareas bloqueadas por duda funcional: exactitud del cuadro ARIBA final, política de edición de precios, decisión final sobre numeración automática y confirmación de contextos reales de producción.

## 9. Checklist de validación final

- Trabajos es la pantalla operativa principal y resuelve la mayor parte del flujo diario.
- Un trabajo puede existir y seguir operativo sin pedido.
- El pedido puede crearse desde trabajo sin saltar a un flujo paralelo obligatorio.
- La relación técnica permite varios pedidos por trabajo, pero la UI muestra un pedido principal claro.
- Las líneas del pedido salen del tarifario.
- El selector de línea permite buscar por descripción y código.
- Cantidad y precio aceptan decimales de extremo a extremo.
- Cancelados/anulados siguen visibles y quedan al final.
- La fecha de terminación puede fijarse manualmente.
- El sistema registra auditoría suficiente en campos críticos.
- Existe aviso de conflicto o, como mínimo, plan implementable y validado para concurrencia en módulos críticos.
- Estaciones se buscan por código y nombre y siguen separadas por contexto.
- Maestros críticos no se borran físicamente.
- Contexto y rol siguen separados.
- Técnico no actúa como administrador de negocio.
- Contabilidad controla facturas.
- Dirección puede revisar y cerrar; la reapertura queda implementada o documentada como pendiente explícita.
- Moeve genera PDF + CSV + cuadro ARIBA desde pedido.
- Repsol opera con tarifa única sin duplicidades funcionales.
- La interfaz operativa queda compacta, útil y sin regresiones graves.

## 10.1 Orden real de ejecución aprobado

### Bloque A — Trabajos como centro operativo

Objetivo: consolidar `Trabajos` como pantalla operativa principal del ERP y reducir la necesidad de iniciar la jornada desde módulos paralelos.

Archivos probables:

- `app/Http/Controllers/Api/TrabajoController.php`
- `app/Http/Resources/Api/TrabajoResource.php`
- `resources/js/Pages/Trabajos/Index.jsx`
- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `resources/js/navigation/sidebar.js`

Tareas:

- priorizar la navegación de ejecución hacia `Trabajos`
- reforzar en la tabla la visibilidad de pedido/no pedido y del resumen económico
- mantener visible el pedido principal sin romper trabajos sin pedido ni trabajos con varios pedidos
- preparar el hueco visual para “Crear pedido desde trabajo” sin implementar todavía la lógica completa

Validaciones:

- usuario de ejecución Moeve y Repsol entra en `Trabajos` como punto natural de inicio
- filtros por estación, responsable, estado y contexto siguen funcionando
- edición inline y control de conflicto por campo no se rompen
- cancelados siguen visibles al final

Criterios de aceptación:

- `Trabajos` queda claramente como pantalla principal de trabajo diario
- la navegación no empuja al usuario a empezar por `Pedidos`
- desde `Trabajos` se identifica si hay pedido asociado o no

Qué no tocar:

- lógica completa de creación de pedido
- líneas de pedido
- decimales
- exportación Moeve
- facturación avanzada

### Bloque B — Pedido desde trabajo

Objetivo: hacer que el pedido nazca o se gestione desde el contexto del trabajo, manteniendo compatibilidad con pedidos históricos y con la relación múltiple técnica.

Archivos probables:

- `routes/web/operativa.php`
- `app/Http/Controllers/Api/PedidoController.php`
- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `resources/js/Pages/Pedidos/Form.jsx`

Tareas:

- crear flujo “crear pedido desde trabajo”
- fijar visualmente un pedido principal
- mantener pedidos extra como excepción
- reducir protagonismo del módulo `Pedidos` como entrada operativa principal

Validaciones:

- crear trabajo sin pedido
- crear pedido desde trabajo
- reasignar pedido principal sin romper relaciones existentes
- mantener acceso a pedidos ya existentes

Criterios de aceptación:

- el alta de pedido no exige navegar a un CRUD paralelo salvo casos excepcionales
- la UI sigue soportando varios pedidos por trabajo sin volver complejo el flujo diario

Qué no tocar:

- selector buscable de líneas
- decimales
- exportación Moeve
- lógica avanzada de estados

### Bloque C — Líneas de pedido, tarifario y decimales

Objetivo: alinear las líneas de pedido con el tarifario real y permitir captura operativa con búsqueda útil y soporte decimal.

Archivos probables:

- `app/Http/Requests/Api/StorePedidoRequest.php`
- `app/Http/Requests/Api/UpdatePedidoRequest.php`
- `resources/js/Components/ui/ItemsTable.jsx`
- `resources/js/Pages/Pedidos/Form.jsx`
- `resources/js/Components/ui/PedidosExcelView.jsx`

Tareas:

- reemplazar el selector simple por búsqueda por descripción y código
- mantener autocompletado de código, descripción y precio
- permitir cantidades y precios decimales de extremo a extremo
- recalcular totales de línea y pedido sin contradicción entre UI y backend

Validaciones:

- alta y edición de líneas con decimales
- búsqueda por código y descripción
- bloqueo de líneas fuera del tarifario/contrato/contexto

Criterios de aceptación:

- una línea tarifaria se localiza rápido y se guarda correctamente
- backend y frontend aceptan los mismos formatos numéricos

Qué no tocar:

- exportación documental
- permisos de facturación
- rediseño de la estructura de pedidos

### Bloque D — Exportación Moeve PDF + CSV + cuadro ARIBA

Objetivo: generar desde pedido la salida documental mínima necesaria para el flujo operativo Moeve.

Archivos probables:

- `app/Http/Controllers/Api/PedidoController.php`
- servicio nuevo de exportación de pedido Moeve
- vista o plantilla PDF
- UI de `Trabajos` y/o `Pedidos` para el botón de exportación

Tareas:

- mapear trabajo, estación, contrato y líneas del pedido
- generar PDF
- generar CSV
- generar cuadro ARIBA copiable
- registrar auditoría de exportación si procede

Validaciones:

- caso de prueba La Senyera
- coherencia de importes, códigos, estación y contrato
- descarga o preview correcta

Criterios de aceptación:

- desde un pedido Moeve se obtiene la salida documental mínima requerida para correo operativo

Qué no tocar:

- facturación avanzada
- cierre
- importaciones

### Bloque E — Estados automáticos, facturación y cierre

Objetivo: derivar estados operativos y económicos mínimos desde datos reales y consolidar el resumen pedido/solicitado/facturado sin desplazar a contabilidad de su ámbito.

Archivos probables:

- `app/Http/Controllers/Api/TrabajoController.php`
- `app/Http/Controllers/Api/PedidoController.php`
- `app/Http/Controllers/Api/FacturaController.php`
- modelos y vistas de trabajos/pedidos/facturas
- `ClosureDashboard*` para reposicionamiento funcional, si aplica

Tareas:

- consolidar resumen económico en trabajo
- derivar estados automáticos prioritarios
- mantener facturación editable solo donde corresponda
- revisar que `Cierre` quede como diagnóstico secundario y no centro operativo

Validaciones:

- trabajo sin pedido
- pedido en preparación
- pedido recibido
- facturado parcial
- facturado completo
- terminado
- cancelado

Criterios de aceptación:

- el estado visible refleja razonablemente la situación operativa y económica
- contabilidad mantiene control sobre facturas
- `Cierre` no sustituye el trabajo diario en `Trabajos`

Qué no tocar:

- rediseño visual global
- cambios destructivos de estados históricos
- eliminación del panel de cierre

### Bloque F — Roles, técnico, maestros y auditoría

Objetivo: endurecer la separación entre negocio y soporte técnico, proteger maestros críticos y extender la trazabilidad donde más valor aporta.

Archivos probables:

- `app/Models/User.php`
- seeders de roles/permisos si aplica
- controladores de maestros
- `AuditLogger`
- flujos de pedido/factura y, si entra en alcance, de cierre

Tareas:

- revisar límites del perfil técnico frente a negocio
- confirmar permisos de dirección y contabilidad
- mantener desactivación en maestros en lugar de borrado físico
- extender auditoría o conflicto por campo a puntos críticos viables

Validaciones:

- matriz de permisos por rol/contexto
- edición protegida de maestros
- consulta/edición de facturas según rol
- cierre y revisión por dirección

Criterios de aceptación:

- ningún perfil operativo toca maestros o facturación fuera de su ámbito
- dirección y contabilidad conservan su operativa
- la auditoría cubre mejor los puntos críticos del flujo

Qué no tocar:

- importaciones masivas
- seeders privados sin revisión
- refactor de seguridad completo

### Bloque G — Compactación visual, pruebas y documentación

Objetivo: cerrar la ola con una interfaz operativa compacta, pruebas funcionales por rol y documentación viva del estado final.

Archivos probables:

- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `resources/js/Components/ui/PedidosExcelView.jsx`
- `resources/js/Components/ui/FacturasExcelView.jsx`
- `resources/js/Components/ui/EstacionesExcelView.jsx`
- `ClosureDashboard*`
- `docs/02_CLIENTE/tareasComparar.md`

Tareas:

- ajustar densidad visual y filtros
- rebajar el peso operativo del panel de cierre
- ejecutar pruebas manuales de flujos Moeve y Repsol
- documentar backlog cerrado, pendientes y riesgos residuales

Validaciones:

- jornada completa por rol
- trabajo sin pedido
- trabajo con pedido Moeve y exportación
- flujo Repsol con tarifa única
- facturación parcial
- cancelación visible al final

Criterios de aceptación:

- la interfaz queda compacta y usable sin regresiones graves
- la documentación refleja qué quedó hecho, qué quedó pendiente y cómo validarlo

Qué no tocar:

- refactors internos grandes
- cambios de esquema no imprescindibles
- rediseño estético ajeno al flujo operativo

## 10. Recomendación final

Lo primero que haría es fijar dos invariantes y no moverme de ahí: Trabajo como agregado operativo central y PedidoItem como línea tarifaria decimal. Es donde más valor de negocio se gana y donde menos riesgo estructural hay, porque el repositorio ya soporta casi todo lo necesario y el problema actual es de alineación funcional, no de ausencia de cimientos.

El segundo foco sería aislar lo que sí es P0 y hoy no existe o contradice negocio: creación de pedido desde trabajo, decimales sin fricción, exportación Moeve y reglas mínimas de estado. Todo lo demás debe entrar solo si no rompe compatibilidad ni obliga a reestructuras grandes. La ejecución correcta en 8 días es incremental, reversible y apoyada cada día en actualización obligatoria de docs/02_CLIENTE/tareasComparar.md.
