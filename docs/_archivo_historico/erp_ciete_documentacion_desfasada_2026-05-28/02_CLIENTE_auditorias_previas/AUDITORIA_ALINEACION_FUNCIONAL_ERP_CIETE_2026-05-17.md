# Auditoria de alineacion funcional ERP CIETE 2026-05-17

> **Documento vivo.**  
> Fuente funcional principal: `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`  
> Documentos de interpretacion: `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`, `docs/02_CLIENTE/HISTORIAL_DECISIONES_ERP_CIETE.md`  
> Backlog maestro vinculado: `docs/02_CLIENTE/tareasComparar.md`

## 1. Objetivo

Cruzar lo hablado por CIETE en la reunion/transcripcion con el ERP actual para identificar:

- que ya soporta el modelo vivo,
- que falta realmente,
- que esta mal interpretado,
- y que debe corregirse primero sin rehacer el sistema por defecto.

## 2. Resumen ejecutivo

La reunion confirma que el ERP no gira alrededor de la factura, sino del trabajo. El trabajo puede empezar sin pedido, el pedido puede llegar antes, durante o despues, y la factura agrupa items completos o parciales bajo contrato/tarifa compatible. La mayor preocupacion operativa de CIETE es hacer trabajos y no cobrarlos; por eso cierre, facturacion y trazabilidad deben apoyar ese objetivo sin mezclar clientes, estaciones ni contextos.

El ERP actual ya soporta gran parte de ese modelo: trabajos, pedidos, `pedido_items`, facturas por `factura_items`, contexto activo, permisos por accion, importacion segura con preview y cierre orientado a `finalizado`. El mayor desfase no es un fallo estructural unico, sino una suma de huecos entre el modelo soportado y la experiencia operativa real:

- maestros incompletos o poco visibles,
- listas vacias sin diagnostico funcional,
- cascadas UI no estandarizadas en todos los modulos,
- validaciones compartidas dispersas,
- y reconciliacion pendiente de legacy/importaciones.

## 3. Matriz de alineacion por modulo

| Modulo                 | Reunión / transcripción                                                                                                                                      | ERP actual                                                                                                                      | Gap o mala interpretación                                                                                                                          | Primera corrección recomendada                                                                              |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------- |
| Trabajos               | El eje es el trabajo. Puede existir sin pedido. Se termina tecnicamente antes de facturarse y debe quedar trazado/bloqueable.                                | CRUD vivo, estados funcionales, cierre y contexto activos en `TrabajoController`, `Trabajos/Form.jsx`, `TrabajosExcelView.jsx`. | Faltan cascadas y mensajes mas claros al combinar contexto, estacion, contrato y tipos; la transicion de estados sigue muy repartida.              | Endurecer `Contexto -> Estacion/Contrato/Tipos`, limpiar dependencias y concentrar reglas de estado/cierre. |
| Pedidos                | Llegan antes/durante/despues del trabajo. Un pedido pertenece a un trabajo y sus items son la unidad economica real.                                         | Formulario y API vivos en `Pedidos/Form.jsx` y `PedidoController`; `pedido_items` ya son estables.                              | La UI todavia no fuerza de forma uniforme la cascada `Contexto -> Trabajo -> lineas/tarifa` ni explica bien incompatibilidades.                    | Añadir cascadas, limpieza de niveles inferiores y diagnostico de incompatibilidad trabajo/tarifa/item.      |
| Facturas               | Agrupan items completos o parciales; no son el eje, pero si el punto de control para no dejar trabajos sin cobrar.                                           | Flujo moderno y Excel vivos; `factura_items` como modelo principal; validacion de sociedad/CIF existente.                       | Cuando falta maestro el usuario puede quedarse sin opciones si no se explica el motivo; parte del guidance acaba en UI pero no esta estandarizado. | Mantener la piramide y extender el diagnostico accionable de maestro faltante.                              |
| Estaciones             | Bien separadas por cliente/contexto. Codigo como identificador operativo. No borrar historico.                                                               | Modulo vivo con contexto, activo/baja y filtros.                                                                                | Falta reforzar algunas validaciones cruzadas desde trabajos y procesos de actualizacion controlada por contexto.                                   | Consolidar validacion estacion-contexto-cliente y preparar auditoria de calidad de estacionario real.       |
| Tarifarios / contratos | Contrato ligado a tarifario. El precio manda la parte economica. No deben aparecer contratos/tarifas fuera de contexto ni caducados para nuevas operaciones. | CRUD minimo vivo en Maestros y relaciones modelo existentes.                                                                    | El formulario de tarifarios sigue corto y no estandariza cascada/diagnostico por contexto y contrato.                                              | Forzar `Contexto -> Contrato -> Tarifario` y ocultar mejor contratos no operativos.                         |
| Sociedades / CIF       | La factura necesita sociedad/CIF permitida para contrato/tarifa/contexto. Si falta, debe saberse por que.                                                    | Pivot `contrato_empresas_facturadoras` vivo, CRUD maestro y validacion backend en facturas.                                     | La gobernanza existe pero no siempre es visible desde la operativa; el usuario necesita mejor explicacion cuando la lista queda vacia.             | Hacer central el modulo de Sociedades Facturadoras y propagar diagnosticos desde pantallas consumidoras.    |
| Usuarios / permisos    | Debe existir control real de usuarios, permisos y trazabilidad. No todos deben ver ni tocar lo mismo.                                                        | Roles, permisos, contexto y auditoria operativa vivos.                                                                          | Falta un mapa mas claro de permisos/impacto por modulo y revisar puntos de bypass por contexto.                                                    | Auditoria transversal de permisos/contexto y ampliar visibilidad funcional de reglas criticas.              |
| Cierre                 | El cierre funcional real depende de trabajo terminado y facturacion controlada. No debe cerrarse sin control economico.                                      | `ClosureDashboardService` y `Cierre/Dashboard.jsx` ya operan con `finalizado`.                                                  | Hay que formalizar mejor que informa, alerta o bloquea dentro del cierre, especialmente con legalizaciones y economia.                             | Clarificar checklist de cierre y endurecer transiciones autorizadas.                                        |
| Legalizaciones         | Deben integrarse como seguimiento real, no texto perdido. No son el eje del flujo critico, pero deben informar cierre y seguimiento.                         | Modelos y algunas integraciones de lectura existen; no hay modulo vivo completo.                                                | Actualmente quedan demasiado laterales para la importancia de seguimiento que sugiere la reunion.                                                  | Integrarlas primero como seguimiento estructurado en cierre/auditoria antes de abrir un modulo completo.    |
| Importaciones          | Deben ser staging/preview seguro, no carga ciega. El usuario tiene que entender avisos, revisar y confirmar.                                                 | Flujo upload -> preview -> confirm vivo en `ImportacionController` y servicios de importacion.                                  | Sigue pendiente clasificar mejor avisos/ignorados y reconciliar datos reales/legacy antes de endurecer reglas finales.                             | Priorizar gap report de avisos reales y clasificar que es corregible, legacy o decision pendiente.          |
| Informes / auditoria   | Trazabilidad real, control de quien toca que y soporte a direccion para no perder trabajos no cobrados.                                                      | Auditoria viva, exportaciones CSV y paneles existentes.                                                                         | Falta alinear mejor auditoria, cierre y maestros como circuito de control operativo unico.                                                         | Revisar trazabilidad cruzada trabajo-pedido-factura-cierre-maestro y limpiar huecos de contexto.            |

## 4. Gap report por modulo

### Trabajos

- **Existe hoy:** `app/Http/Controllers/Api/TrabajoController.php`, `resources/js/Pages/Trabajos/Form.jsx`, `resources/js/Components/ui/TrabajosExcelView.jsx`.
- **Alineado con reunion:** trabajo como eje operativo, estados vivos, bloqueo de creacion desde `TODOS`, trazabilidad basica.
- **Gap:** cascadas y diagnosticos de contexto/estacion/contrato/tipos todavia no son uniformes.
- **Tipo de correccion:** flujo UI + validacion backend compartida.

### Pedidos

- **Existe hoy:** `app/Http/Controllers/Api/PedidoController.php`, `resources/js/Pages/Pedidos/Form.jsx`, `pedido_items` estables.
- **Alineado con reunion:** pedido ligado a trabajo e items como unidad economica.
- **Gap:** la UI todavia no guia suficientemente la combinacion trabajo/tarifa/item.
- **Tipo de correccion:** flujo UI + validacion.

### Facturas

- **Existe hoy:** `app/Http/Controllers/Api/FacturaController.php`, `resources/js/Pages/Facturas/Form.jsx`, `resources/js/Components/ui/FacturasExcelView.jsx`.
- **Alineado con reunion:** `factura_items`, parcialidad, sociedad/CIF permitida, exportacion, control economico.
- **Gap:** falta diagnostico uniforme cuando el maestro no permite operar; parte ya esta arrancada en la primera ola.
- **Tipo de correccion:** dato maestro + UX + servicios de validacion compartidos.

### Estaciones

- **Existe hoy:** `app/Http/Controllers/Api/EstacionController.php`, `resources/js/Pages/Estaciones/*`.
- **Alineado con reunion:** separacion por contexto, identificacion por codigo, historico sin hard delete.
- **Gap:** control cruzado con trabajos/importacion y auditoria de calidad del maestro real.
- **Tipo de correccion:** dato maestro + validacion + importacion.

### Tarifarios / contratos

- **Existe hoy:** controladores y formularios en `app/Http/Controllers/ContratoController.php`, `TarifarioController.php`, `resources/js/Pages/Tarifarios/*`.
- **Alineado con reunion:** contrato como contenedor economico y tariff lines vivas.
- **Gap:** falta cascada fuerte por contexto/contrato y guidance sobre lo que ya no debe usarse en nuevas operaciones.
- **Tipo de correccion:** flujo UI + validacion + maestro.

### Sociedades / CIF

- **Existe hoy:** `app/Http/Controllers/ContratoEmpresaFacturadoraController.php`, `resources/js/Pages/SociedadesFacturadoras/Index.jsx`, pivot y relaciones modelo.
- **Alineado con reunion:** sociedad/CIF permitida por contrato/tarifa/contexto.
- **Gap:** visibilidad insuficiente desde operativa y datos maestros incompletos en algunos contextos/contratos.
- **Tipo de correccion:** dato maestro + UX + backlog de completitud.

### Usuarios / permisos

- **Existe hoy:** usuarios, roles, permisos y contexto en backend/web.
- **Alineado con reunion:** control de accesos y trazabilidad operativa.
- **Gap:** falta revisar transversalmente bypasses y claridad funcional de quien puede tocar que en cada modulo.
- **Tipo de correccion:** permisos + auditoria + documentacion operativa.

### Cierre

- **Existe hoy:** `app/Services/ClosureDashboardService.php`, `resources/js/Pages/Cierre/Dashboard.jsx`.
- **Alineado con reunion:** cierre orientado a `finalizado`, control de trabajo terminado y economia.
- **Gap:** mayor formalizacion de checklist, bloqueos autorizados y relacion con legalizaciones/auditoria.
- **Tipo de correccion:** cierre + servicios de dominio + permisos.

### Legalizaciones

- **Existe hoy:** modelos y lectura parcial en cierre/dashboard.
- **Alineado con reunion:** se reconocen como seguimiento real, no como texto suelto.
- **Gap:** aun no existe una integracion operativa proporcional a lo pedido; hoy son demasiado laterales.
- **Tipo de correccion:** seguimiento estructurado primero; modulo vivo completo despues.

### Importaciones

- **Existe hoy:** `app/Http/Controllers/ImportacionController.php`, `app/Services/Importacion/*`, preview y confirmacion reales.
- **Alineado con reunion:** staging seguro, no carga ciega.
- **Gap:** avisos/ignorados y reconciliacion legacy siguen siendo el mayor bloqueo para usar datos reales con tranquilidad.
- **Tipo de correccion:** importacion + clasificacion funcional + gap report.

### Informes / auditoria

- **Existe hoy:** `AuditLogController`, exportaciones CSV, paneles y listados.
- **Alineado con reunion:** trazabilidad y control operativo.
- **Gap:** hay que conectar mejor el circuito trabajo -> pedido -> factura -> cierre -> auditoria para la preocupacion clave de no dejar trabajos sin cobrar.
- **Tipo de correccion:** auditoria + informes operativos + criterios de seguimiento.

## 5. Plan por olas de implementacion

### Ola 0 — Alineacion funcional y autoridad documental

- Fijar reunion/transcripcion como fuente funcional primaria.
- Crear matriz de alineacion por modulo.
- Actualizar backlog maestro y documentacion viva para que no contradigan ese orden.

### Ola 1 — Maestros + diagnostico funcional + cascadas UI

- Auditoria de completitud de maestros por contexto.
- Diagnostico accionable en listas vacias y bloqueos de operativa.
- Cascadas funcionales en trabajos, pedidos, tarifarios y consolidacion de facturas.

### Ola 2 — Validaciones compartidas + cierre + permisos/contexto

- Extraer reglas comunes de combinacion contexto/contrato/tarifa/empresa/trabajo/pedido/item.
- Formalizar checklist y transiciones autorizadas de cierre.
- Revisar puntos de bypass de contexto y endurecer trazabilidad/permiso efectivo.

### Ola 3 — Reconciliacion de importaciones, legacy y seguimiento ampliado

- Clasificar avisos/ignorados/importaciones legacy.
- Determinar que datos son reconciliables, solo lectura o duda funcional pendiente.
- Integrar mejor legalizaciones y auditoria con seguimiento operativo real.

## 6. Primera ola priorizada

### Cambio 1 — Auditoria de completitud de maestros por contexto

- **Objetivo:** saber si cada contexto puede operar sin huecos de contrato, CIF, sociedad, tarifario, estacion y tipos.
- **Criterio de aceptación:** existe informe por contexto con contratos activos sin sociedad/CIF, empresas sin CIF, tarifarios insuficientes y estado de OTROS CLIENTES.
- **Tipo de problema a corregir:** dato maestro.

### Cambio 2 — Diagnostico accionable cuando una lista queda vacia

- **Objetivo:** que un usuario no se quede sin opciones sin saber por que.
- **Criterio de aceptación:** facturas, trabajos, pedidos y tarifarios explican si falta maestro, validacion o contexto, y enlazan a la correccion si procede.
- **Tipo de problema a corregir:** flujo UI + dato maestro.

### Cambio 3 — Cascadas funcionales en formularios vivos

- **Objetivo:** evitar combinaciones contradictorias con el flujo real hablado con CIETE.
- **Criterio de aceptación:** cambiar contexto limpia dependencias inferiores; cambiar contrato o trabajo limpia lo incompatible; el backend rechaza igualmente combinaciones cruzadas.
- **Tipo de problema a corregir:** flujo UI + validacion.

### Cambio 4 — Revisión transversal de cierre, permisos y trazabilidad

- **Objetivo:** asegurar que trabajos terminados/finalizados no se manipulan fuera de perfiles autorizados y que el circuito economico queda trazado.
- **Criterio de aceptación:** reglas de cierre explicitas, permisos/contexto revisados y huecos de trazabilidad identificados.
- **Tipo de problema a corregir:** permisos + cierre + auditoria.

## 7. Validaciones manuales y automaticas necesarias

### Manuales

1. Crear/editar desde contexto real y desde `TODOS` en maestros, trabajos, pedidos y facturas.
2. Forzar un contrato sin sociedad permitida y comprobar que la UI explique el problema y el camino de correccion.
3. Cambiar contexto/contrato/trabajo en formularios y confirmar que las dependencias inferiores se limpian correctamente.
4. Validar un trabajo terminado pero aun no facturado, y confirmar que cierre y seguimiento lo reflejan sin cerrarlo indebidamente.
5. Revisar una importacion con avisos para verificar que sigue siendo staging/preview seguro y no carga ciega.

### Automaticas

- `php artisan test tests/Feature/FacturaTest.php`
- `php artisan test tests/Feature/PedidoTest.php`
- `php artisan test tests/Feature/TrabajoTest.php`
- `php artisan test tests/Feature/ImportacionesAccessTest.php`
- `php artisan test tests/Feature/AuditLogTest.php`
- `npm run build`

## 8. Decision operativa resultante

- No se recomienda rehacer el ERP por defecto.
- El modelo actual ya soporta la mayor parte de la regla funcional.
- La prioridad es cerrar primero dato maestro, diagnostico funcional y cascadas UI antes de ampliar estructura o endurecer importaciones legacy.
- Cualquier implementacion nueva debe justificarse contra la reunion/transcripcion antes que contra inercias del codigo actual.
