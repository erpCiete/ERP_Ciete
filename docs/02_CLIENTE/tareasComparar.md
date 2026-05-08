# Tareas restantes ERP CIETE

> **Documento vivo.**  
> Este documento es el backlog maestro vigente de tareas restantes del ERP CIETE.  
> Fuente funcional principal: `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`  
> Última auditoría: **2026-05-07**, ERP al **86,2%**, Excel MOEVE/REPSOL actualizados importados en local/demo y gobierno mínimo de Maestros cerrado.  
> Responsable funcional: Pablo Sevillano.  
> Uso del documento: backlog maestro de fase actual.  
> No usar como contrato técnico detallado ni como memoria histórica.

Este documento es el único listado vivo de tareas pendientes. No sustituye a la Biblia de desarrollo, al contrato API ni al historial de decisiones: los resume solo cuando hace falta para ordenar trabajo real.

## 0. Regla de actualización

Este documento debe actualizarse al cerrar cada P0/P1 relevante.

Cada tarea cerrada debe moverse a la sección "Tareas cerradas" con una nota breve de qué se hizo y qué archivos principales se tocaron.

No se deben añadir tareas nuevas sin indicar prioridad, área, criterio de aceptación y motivo.

## 1. Estado general del ERP

El ERP CIETE está aproximadamente al **86,2%** de avance real.

La base de datos demo ya queda alineada con el modelo vivo crítico y se ha validado con importación controlada de los Excel actualizados de MOEVE y REPSOL. No es todavía una base final cerrada por negocio, pero el esquema local sembrado ya no conserva contradicciones funcionales en trabajos, pedidos ni facturación: los estados legacy quedan fuera del enum vivo, las columnas antiguas de cierre de trabajos se retiran y `factura_pedidos` desaparece del esquema demo.

El bloqueo principal de facturación por ítems queda cerrado funcionalmente. La relación principal ya es `factura -> factura_items -> pedido_items -> pedidos -> trabajos` y las exportaciones ya no reconstruyen detalle desde `factura_pedidos` ni desde `facturas.id_trabajo`. La validación sociedad/CIF permitida por contrato/tarifa queda implementada y validada (P0-02 cerrada). El bloqueo uniforme desde TODOS y OTROS CLIENTES como contexto real completo quedan cerrados funcionalmente; el código demo de OTROS CLIENTES se normaliza a `OTROS`. Los estados reales de trabajos quedan alineados con CIETE: las altas usan `en_curso`, `terminado` registra fecha si procede y `finalizado` sustituye al cierre antiguo en el flujo vivo. Permisos/rutas mutables y seeders quedan alineados por acción, sin alias legacy vivos en `User::hasPermission()`. La política anti hard delete del flujo vivo queda cerrada funcionalmente (P1-01 cerrada). El módulo de estaciones ya prioriza código, nombre, municipio y provincia tanto en Ciete Excel como en Ciete Moderno, con dirección y baja relegadas a detalle secundario (P1-04 cerrada). La auditoría operativa de maestros vivos queda reforzada en clientes y estaciones, mientras `interface_mode` deja de generar eventos nuevos y los logs visuales legacy siguen fuera del filtro operativo por defecto. Las facturas ya son exportables en listado y en detalle individual por CSV respetando `factura_items`, permisos y contexto. La revisión responsive final de Ciete Excel y Ciete Moderno queda cerrada con ajustes de shell, topbar/contexto, filtros, tablas con scroll interno, formularios y modales sin rediseñar el ERP. P1-12 confirma que el ERP puede cargar volumen real desde los Excel actualizados de MOEVE/REPSOL en local/demo, con 11.614 trabajos, 9.685 pedidos, 9.701 `pedido_items`, 2.733 facturas y 9.194 `factura_items` tras `migrate:fresh --seed` e importación con `--commit`. El gobierno mínimo de Maestros queda separado de la operativa diaria: contratos, sociedades facturadoras permitidas, tarifarios y líneas de tarifa ya tienen CRUD mínimo con permisos, contexto, auditoría y desactivación sin borrado físico. El riesgo principal pendiente queda en depurar avisos/filas ignoradas de Excel, completar datos maestros reales definitivos, carga real de OTROS CLIENTES, exportaciones complejas XLSX/PDF y módulos posteriores fuera del flujo crítico.

Están bastante avanzados:

- Trabajo como entidad central.
- Contexto activo en backend.
- Selector/indicador de contexto.
- Estaciones con código, municipio/provincia y contexto, ya pulidas en Excel/Moderno.
- Ciete Excel de trabajos.
- Observaciones en modal.
- Control optimista por campo en trabajos.
- Auditoría visible y filtrable.
- Documentación funcional viva.

Los grandes pendientes son:

- Depurar con CIETE las filas con aviso/ignoradas de los Excel reales, especialmente trabajos con importe sin número de pedido y facturas MOEVE históricas no enlazadas a `factura_items`.
- Preparar carga/gobierno de datos reales de OTROS CLIENTES si aparece fuente propia.
- Completar datos maestros reales en el nuevo panel: contratos, sociedades facturadoras, tarifarios, líneas, usuarios y roles.

## 2. Porcentaje de avance actual

| Área | Peso | Avance interno | Ponderado |
|---|---:|---:|---:|
| BD/modelo negocio | 20% | 83% | 16,6 |
| Contextos/roles/permisos | 15% | 93% | 14,0 |
| Trabajos/estaciones | 15% | 88% | 13,2 |
| Pedidos/ítems | 10% | 80% | 8,0 |
| Facturación | 15% | 85% | 12,8 |
| Ciete Excel/Moderno | 10% | 82% | 8,2 |
| Auditoría | 5% | 90% | 4,5 |
| Documentación | 5% | 87% | 4,4 |
| Rendimiento/seguridad/legacy | 5% | 90% | 4,5 |

**Total actual estimado:** 86,2%.

La subida es deliberadamente moderada: refleja el cierre funcional de P1-09/P1-10, la limpieza estructural real de P1-11, la importación real controlada de P1-12 y el panel separado de Maestros con contratos, sociedades facturadoras permitidas y tarifarios gobernables desde la aplicación. No se infla más porque quedan filas/columnas de Excel que requieren decisión funcional, datos maestros reales definitivos, OTROS CLIENTES real y exportaciones complejas XLSX/PDF.

## 2.1 Orden recomendado de ejecución P0

Tras cerrar `pedido_items` estables, facturación por `factura_items`, validación sociedad/CIF, contextos reales, estados de trabajos, permisos por acción, exportaciones CSV de facturas, revisión de rendimiento crítica, responsive final, limpieza estructural de legacy demo, importación real controlada de Excel MOEVE/REPSOL y gobierno mínimo de Maestros, el siguiente bloqueo funcional es depurar con CIETE los avisos de carga y preparar la muestra manual/controlada de 50 registros usando maestros reales sin reintroducir compatibilidad antigua.

Orden recomendado:

1. **P0-03 — Actualizar `pedido_items` sin borrar/recrear.** Cerrada.
2. **P0-01 — Facturación real por `factura_items`.** Cerrada.
3. **P0-02 — Validar contrato/tarifa/sociedad/CIF al facturar.** Cerrada.
4. **P0-04 — Bloqueo uniforme de creación desde TODOS.** Cerrada.
5. **P0-05 — OTROS CLIENTES como contexto real completo.** Cerrada.
6. **P0-06 — Estados reales de trabajos y eliminación de legacy visible.** Cerrada.
7. **P0-07 — Permisos/rutas mutables y seeders.** Cerrada.

Motivo: los ítems ya son estables, la factura ya consume `factura_items`, la coherencia fiscal sociedad/CIF queda cerrada, TODOS/OTROS funcionan según decisión funcional, los estados vivos de trabajos ya no dependen de `borrador`/`cerrado` y las rutas mutables usan permisos de acción.

## 3. P0 - Bloqueantes para dejar la fase actual operativa

### Tarea P0-01 - Facturación real por `factura_items`

**Estado:** cerrada  
**Área:** facturas  
**Prioridad:** P0  
**Bloquea demo:** sí  
**Bloquea ERP completo:** sí

**Qué se decidió:**  
La relación funcional principal debe ser:

`factura -> factura_items -> pedido_items -> pedidos -> trabajos`

Una factura puede agrupar ítems de varios pedidos y trabajos, siempre compatibles por contrato/tarifa.

**Estado real actual:**  
El flujo funcional principal ya usa `factura_items`. Tras P1-11, `facturas.id_trabajo` queda solo como cabecera derivada/auxiliar para filtros y ordenación, y `factura_pedidos` se elimina del esquema demo y del flujo vivo.

**Qué se hizo:**

- Crear y actualizar facturas seleccionando `pedido_items`.
- Guardar, actualizar y eliminar líneas en `factura_items` conservando `id_factura_item` cuando la línea ya existe.
- Permitir facturación parcial y bloquear sobre-facturación de importe/unidades pendientes.
- Derivar trabajos y pedidos desde `factura_items`.
- Exponer `items`, `importe_asignado`, `diferencia`, `estado_cuadre`, trabajos y pedidos en `FacturaResource`.
- Retirada posterior por P1-11 de `factura_pedidos` como fallback/compatibilidad demo.
- Registrar auditoría de alta/actualización de factura y alta/modificación/eliminación de `factura_items`.

**Archivos principales modificados:**

- `app/Http/Controllers/Api/FacturaController.php`
- `app/Http/Requests/Api/StoreFacturaRequest.php`
- `app/Http/Requests/Api/UpdateFacturaRequest.php`
- `app/Http/Resources/Api/FacturaResource.php`
- `app/Models/Pedido.php`
- `routes/web.php`
- `resources/js/Pages/Facturas/Form.jsx`
- `resources/js/Pages/Facturas/Index.jsx`
- `resources/js/Components/ui/FacturasExcelView.jsx`
- `resources/js/Components/ui/BadgeFactura.jsx`
- `resources/js/i18n/locales/es.js`
- `resources/js/i18n/locales/en.js`
- `tests/Feature/FacturaTest.php`

**Validaciones ejecutadas:**

- `php -l app/Http/Controllers/Api/FacturaController.php`
- `php -l app/Http/Requests/Api/StoreFacturaRequest.php`
- `php -l app/Http/Requests/Api/UpdateFacturaRequest.php`
- `php -l app/Http/Resources/Api/FacturaResource.php`
- `php -l app/Models/Factura.php`
- `php -l app/Models/FacturaItem.php`
- `php -l app/Models/PedidoItem.php`
- `php -l app/Models/Pedido.php`
- `php artisan route:list`
- `php artisan test --filter=FacturaTest`
- `npm.cmd run build` fuera del sandbox tras EPERM del binario nativo de Tailwind.

**Criterio de aceptación:**

- Se puede crear una factura seleccionando `pedido_items`.
- Una factura puede agrupar ítems de varios pedidos y trabajos.
- `id_trabajo` no es obligatorio.
- `factura_pedidos` no es relación funcional principal.
- Se calcula importe asignado, diferencia y estado de cuadre.
- Se registra auditoría.

**Riesgos:**  
P0-02 queda integrada: la sociedad/CIF se valida por contrato/tarifa mediante `contrato_empresas_facturadoras`. Riesgo pendiente: mantener datos reales de sociedades permitidas y un CRUD/gobierno de esa relación.

### Tarea P0-02 - Validar contrato/tarifa/sociedad/CIF al facturar

**Estado:** cerrada  
**Área:** facturas / contratos / tarifas / empresas  
**Prioridad:** P0  
**Bloquea demo:** sí  
**Bloquea ERP completo:** sí

**Qué se decidió:**  
CIETE confirmó que una factura agrupa ítems siempre bajo contrato/tarifa compatible. La factura debe emitirse contra una sociedad/CIF permitida para ese contrato/tarifa/grupo.

**Qué se hizo:**

- Creada migración aditiva `contrato_empresas_facturadoras` (pivot contrato↔empresa con `activo`, `id_contexto`, `observaciones`).
- Creado modelo Eloquent `ContratoEmpresaFacturadora` con relaciones `contrato()`, `empresa()`, `contexto()`.
- Añadida relación `empresasFacturadoras()` (BelongsToMany) en `Contrato`.
- Añadida relación `contratosPermitidos()` (BelongsToMany) en `Empresa`.
- Implementado `validateEmpresaFacturadora()` en `FacturaController`: en facturas por `factura_items` exige sociedad/CIF, valida empresa activa, CIF informado, contexto coherente y relación activa en `contrato_empresas_facturadoras` para el contrato/contexto derivado de los ítems.
- Llamada en `store()` y `update()`.
- Enriquecido `FacturaResource` con sociedad facturadora, CIF, `sociedad_cif_validada`, contrato y tarifarios derivados.
- Ajustada closure `$buildEmpresasFacturadoras` en `routes/web.php`: agrupa sociedades activas por contexto y contratos permitidos, sin fallback permisivo para el flujo por ítems.
- Prop `empresasFacturadoras` añadida a rutas `facturas.create` y `facturas.edit`.
- `Form.jsx` actualizado: selector de sociedad/CIF obligatorio para facturas por ítems, filtro de sociedades por contratos de las líneas seleccionadas, filtro de ítems por sociedad seleccionada y aviso si no hay sociedades permitidas.
- Listados Ciete Moderno y Ciete Excel muestran sociedad facturadora/CIF con fallback legacy a empresa cliente.
- Seeder `ContratoEmpresasFacturadorasSeeder` creado con datos MOEVE (contrato=1, empresa=1) y REPSOL (contrato=2, empresa=2).
- `DatabaseSeeder` llama al seeder de sociedades permitidas tras los datos base.
- Crear factura desde TODOS queda bloqueado en backend y en ruta/formulario web de facturas; editar desde TODOS se limita a facturas de contextos accesibles.

**Archivos principales modificados:**

- `database/migrations/2026_05_05_000130_create_contrato_empresas_facturadoras.php` *(nuevo)*
- `app/Models/ContratoEmpresaFacturadora.php` *(nuevo)*
- `database/seeders/ContratoEmpresasFacturadorasSeeder.php` *(nuevo)*
- `app/Models/Contrato.php`
- `app/Models/Empresa.php`
- `app/Http/Controllers/Api/FacturaController.php`
- `app/Http/Resources/Api/FacturaResource.php`
- `app/Http/Requests/Api/StoreFacturaRequest.php`
- `app/Http/Requests/Api/UpdateFacturaRequest.php`
- `routes/web.php`
- `database/seeders/DatabaseSeeder.php`
- `resources/js/Pages/Facturas/Form.jsx`
- `resources/js/Pages/Facturas/Index.jsx`
- `resources/js/Components/ui/FacturasExcelView.jsx`
- `tests/Feature/FacturaTest.php`

**Validaciones ejecutadas:**

- `php -l` en controlador, requests, resource, modelos, migración, seeders y `FacturaTest`.
- `php artisan migrate:status` confirma `contrato_empresas_facturadoras` aplicada.
- `php artisan route:list`.
- `php artisan test --filter=FacturaTest` (12 tests, 52 assertions).
- `npm.cmd run build` fuera del sandbox tras EPERM del binario nativo de Tailwind/Rolldown.

**Criterio de aceptación:**

- No se puede seleccionar una sociedad/CIF no permitida para el contrato/tarifa.
- El frontend filtra sociedades según contexto, contratos seleccionados y pivot `contrato_empresas_facturadoras`.
- El backend valida aunque el frontend falle.
- Las facturas nuevas por ítems no se guardan sin sociedad/CIF.
- No se permite sociedad sin CIF, sociedad inactiva, sociedad de otro contexto o sociedad no activa en la pivot.
- OTROS CLIENTES funciona con su propia sociedad permitida cuando tiene contrato/sociedad configurados.
- Facturas legacy sin `factura_items` no se bloquean por el campo nullable.

**Riesgos residuales:**

- Hace falta gobierno/CRUD para mantener `contrato_empresas_facturadoras` sin depender de seeders.
- El seeder base cubre MOEVE y REPSOL; OTROS CLIENTES queda preparado y validado en tests, pero requiere contratos y sociedades reales en datos maestros.
- Si un pedido_item histórico no tiene contrato/tarifa asociado, el backend no permite emitir factura nueva por ítems hasta corregir ese dato.

### Tarea P0-03 - Actualizar `pedido_items` sin borrar/recrear

**Estado:** cerrada  
**Área:** pedidos / pedido_items  
**Prioridad:** P0  
**Bloquea demo:** sí  
**Bloquea ERP completo:** sí

**Qué se decidió:**  
`pedido_item` es la unidad económica y facturable real. Sus IDs deben conservarse porque `factura_items` dependerá de ellos.

**Estado real actual:**  
`PedidoController::syncItems()` actualiza ítems existentes por `id_pedido_item`, crea solo líneas nuevas y no borra físicamente las líneas ya vinculadas a `factura_items`.

**Qué se hizo:**

- Actualizar ítems existentes por ID.
- Crear solo los ítems nuevos.
- Eliminar únicamente ítems omitidos que no estén referenciados por `factura_items`.
- Bloquear la eliminación física de ítems ya facturados y devolver aviso claro.
- Ajustar requests, resource y formulario para conservar y enviar `id_pedido_item`.
- Mantener auditoría del pedido con resumen de sincronización de ítems.

**Validaciones ejecutadas:**

- `php -l` en controlador, requests, resource, rutas web y test de pedidos.
- `php artisan test --filter=PedidoTest`.
- `php artisan route:list`.
- `npm.cmd run build` fuera del sandbox tras bloqueo local de PowerShell/EPERM.

**Archivos principales modificados:**

- `app/Http/Controllers/Api/PedidoController.php`
- `app/Http/Requests/Api/StorePedidoRequest.php`
- `app/Http/Requests/Api/UpdatePedidoRequest.php`
- `app/Http/Resources/Api/PedidoResource.php`
- `resources/js/Pages/Pedidos/Form.jsx`
- `resources/js/Components/ui/ItemsTable.jsx`
- `routes/web.php`
- `tests/Feature/PedidoTest.php`

**Criterio de aceptación:**

- Ítems existentes se actualizan por ID.
- Ítems facturados no se borran.
- Si se intenta borrar un ítem ya facturado, se bloquea o se marca como inactivo/anulado.
- `PedidoResource` expone columnas reales.

**Riesgos:**  
Riesgo pendiente: mantener gobierno de datos para sociedades permitidas y completar OTROS CLIENTES con contratos/sociedades reales.

### Tarea P0-04 - Bloqueo uniforme de creación desde TODOS

**Estado:** cerrada  
**Área:** contexto / requests / frontend  
**Prioridad:** P0  
**Bloquea demo:** sí  
**Bloquea ERP completo:** sí

**Qué se decidió:**  
TODOS es vista global operativa. Desde TODOS se puede ver, filtrar y editar registros existentes según permisos. Desde TODOS no se crean registros nuevos.

**Estado real actual:**  
El bloqueo queda centralizado con `ContextGuard`. Los `StoreRequest` contextuales de trabajos, pedidos, facturas, estaciones, clientes e importaciones bloquean alta desde TODOS con mensaje claro. `HasContext` ya no asigna contexto por defecto cuando el contexto activo es TODOS porque `ContextGuard::activeContextIdForCreate()` devuelve `null` en ese caso y los stores fallan antes de crear.

**Qué se hizo:**

- Se consolidó `ContextGuard::canCreateInActiveContext()` como barrera backend para altas contextuales.
- Se bloquean altas desde TODOS en API y rutas/formularios web de trabajos, pedidos, facturas, estaciones, clientes e importaciones.
- El mensaje operativo pide seleccionar MOEVE, REPSOL u OTROS CLIENTES.
- Se añadieron pruebas que fuerzan creación desde TODOS por API y comprueban el 403.
- Se validó que editar desde TODOS sigue funcionando para registros existentes de contextos accesibles.

**Archivos principales modificados:**

- `app/Support/ContextGuard.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Http/Controllers/ActiveContextController.php`
- `app/Http/Controllers/ImportacionController.php`
- `app/Http/Requests/Api/StoreFacturaRequest.php`
- `app/Http/Requests/Api/StoreTrabajoRequest.php`
- `app/Http/Requests/Api/StoreImportacionRequest.php`
- `app/Http/Requests/Api/UpdateTrabajoRequest.php`
- `app/Http/Requests/Api/UpdateFacturaRequest.php`
- `database/seeders/ContextosClienteSeeder.php`
- `resources/js/Pages/Trabajos/Form.jsx`
- `resources/js/Pages/Pedidos/Form.jsx`
- `resources/js/Pages/Facturas/Form.jsx`
- `resources/js/Components/ui/ClientesExcelView.jsx`
- `resources/js/i18n/locales/es.js`
- `resources/js/i18n/locales/en.js`
- `tests/Feature/ContextCreationGuardTest.php`

**Validaciones ejecutadas:**

- `php -l` en controladores, middleware, trait, requests contextuales, rutas, seeder y test de contexto.
- `php artisan route:list`.
- `php artisan test --filter=ContextCreationGuardTest` (4 tests, 30 assertions).
- `php artisan test --filter=TrabajoTest` (12 tests, 65 assertions).
- `php artisan test --filter=PedidoTest` (10 tests, 38 assertions).
- `php artisan test --filter=FacturaTest` (12 tests, 52 assertions).
- `npm.cmd run build` fuera del sandbox tras EPERM del binario nativo de Tailwind/Rolldown.
- `php artisan test --filter=EstacionesTest` intentado: el riesgo recurrente del Excel fuente queda cerrado al apuntar `DemoCieteOperativaSeeder` a `docs/02_CLIENTE/materiales/Contrato 772 MOEVE - Tarifario.xlsx`.

**Criterio de aceptación:**

- Ningún `store` contextual permite crear desde TODOS.
- El mensaje pide seleccionar MOEVE, REPSOL u OTROS CLIENTES.
- Editar desde TODOS funciona si el registro pertenece a un contexto permitido.

**Riesgos:**  
P0-07 queda cerrada: las rutas mutables ya separan permisos por acción. Riesgo pendiente: limpiar alias legacy de permisos cuando los roles reales estén consolidados en datos productivos.

### Tarea P0-05 - OTROS CLIENTES como contexto real completo

**Estado:** cerrada  
**Área:** contexto / trabajos / pedidos / facturas / estaciones  
**Prioridad:** P0  
**Bloquea demo:** sí  
**Bloquea ERP completo:** sí

**Qué se decidió:**  
OTROS CLIENTES es contexto real. Puede crear, editar y operar como MOEVE y REPSOL, ajustando campos específicos. No es TODOS.

**Estado real actual:**  
OTROS CLIENTES queda tratado como contexto real, no como TODOS. El código visible se normaliza a OTROS CLIENTES aunque el código interno histórico siga siendo `OTRO`. Las validaciones específicas se resuelven por identidad de contexto (`moeve`, `repsol`, `otros`) y no obligan campos MOEVE/REPSOL cuando el contexto es OTROS CLIENTES.

**Qué se hizo:**

- `ContextGuard` resuelve `workspace_key`, nombre visible y detección MOEVE/REPSOL/OTROS desde `codigo`/`nombre`.
- El selector/topbar recibe OTROS CLIENTES como nombre visible.
- El seeder de contextos conserva compatibilidad de código `OTRO`, pero actualiza el nombre a OTROS CLIENTES.
- Los formularios de trabajos, pedidos y facturas muestran OTROS CLIENTES de forma explícita.
- Las validaciones de trabajos y facturas ya no dependen solo de IDs 1/2; OTROS no hereda CCP/orden/contrato/tipo documental salvo que aplique.
- Se validó que OTROS CLIENTES puede crear cliente, estación, trabajo y pedido; facturación OTROS con sociedad permitida sigue cubierta por `FacturaTest`.

**Archivos principales modificados:**

- `app/Support/ContextGuard.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Http/Controllers/ActiveContextController.php`
- `app/Http/Requests/Api/StoreTrabajoRequest.php`
- `app/Http/Requests/Api/UpdateTrabajoRequest.php`
- `app/Http/Requests/Api/StoreFacturaRequest.php`
- `app/Http/Requests/Api/UpdateFacturaRequest.php`
- `database/seeders/ContextosClienteSeeder.php`
- `resources/js/Pages/Trabajos/Form.jsx`
- `resources/js/Pages/Pedidos/Form.jsx`
- `resources/js/Pages/Facturas/Form.jsx`
- `resources/js/Components/ui/ClientesExcelView.jsx`
- `resources/js/i18n/locales/es.js`
- `resources/js/i18n/locales/en.js`
- `tests/Feature/ContextCreationGuardTest.php`

**Validaciones ejecutadas:**

- `php -l` en los archivos PHP modificados.
- `php artisan route:list`.
- `php artisan test --filter=ContextCreationGuardTest`.
- `php artisan test --filter=FacturaTest`.
- `php artisan test --filter=TrabajoTest`.
- `php artisan test --filter=PedidoTest`.
- `npm.cmd run build` fuera del sandbox tras EPERM.

**Criterio de aceptación:**

- OTROS CLIENTES aparece como contexto real.
- Se puede crear trabajo/pedido/factura/estación desde OTROS si hay permisos.
- Se guardan campos aplicables sin forzar lógica Moeve/Repsol.
- TODOS sigue siendo vista global, no contexto real.

**Riesgos:**  
OTROS CLIENTES está cerrado como contexto real de aplicación. Siguen pendientes datos maestros reales de OTROS para contratos, sociedades permitidas y estaciones productivas; eso pertenece a gobierno de datos/P0-02 operativo, no al motor de contexto.

### Tarea P0-06 - Estados reales de trabajos y eliminación de legacy visible

**Estado:** cerrada  
**Área:** trabajos / cierre / UI  
**Prioridad:** P0  
**Bloquea demo:** sí  
**Bloquea ERP completo:** sí

**Qué se decidió:**  
Los estados funcionales actuales son:

- `en_curso`
- `terminado`
- `pendiente_facturar`
- `facturado`
- `finalizado`
- `cancelado`

`terminado` significa ejecución hecha. `finalizado` significa terminado y económicamente resuelto/facturado/cuadrado.

**Estado real actual:**  
El flujo vivo de trabajos queda alineado con los estados funcionales CIETE. Las altas crean `en_curso`, los requests ya no aceptan `borrador`/`cerrado` como estados nuevos, Ciete Moderno y Ciete Excel ofrecen solo estados vivos y el panel de cierre finaliza con `finalizado`.

**Qué se hizo:**

- `StoreTrabajoRequest` normaliza altas sin estado a `en_curso` y bloquea estados legacy.
- `UpdateTrabajoRequest` y edición optimista solo admiten `en_curso`, `terminado`, `pendiente_facturar`, `facturado`, `finalizado` y `cancelado`.
- `TrabajoController` registra `fecha_terminacion` al marcar `terminado` si estaba vacía.
- `TrabajoResource` mantiene compatibilidad de lectura con `borrador`/`cerrado` legacy, pero expone estado visual funcional.
- `ClosureDashboardService` usa `finalizado`, calcula economía también desde `factura_items` y deja legalizaciones como información no bloqueante.
- Ciete Moderno, Ciete Excel, badges y traducciones dejan de ofrecer `borrador`/`cerrado` como opciones vivas.

**Archivos principales modificados:**

- `app/Http/Requests/Api/StoreTrabajoRequest.php`
- `app/Http/Requests/Api/UpdateTrabajoRequest.php`
- `app/Http/Controllers/Api/TrabajoController.php`
- `app/Http/Resources/Api/TrabajoResource.php`
- `resources/js/Pages/Trabajos/Form.jsx`
- `resources/js/Pages/Trabajos/Index.jsx`
- `app/Services/ClosureDashboardService.php`
- `resources/js/Pages/Cierre/Dashboard.jsx`
- `resources/js/Components/ui/BadgeTrabajo.jsx`
- `resources/js/i18n/locales/es.js`
- `resources/js/i18n/locales/en.js`
- `tests/Feature/TrabajoTest.php`
- `tests/Feature/ClosureDashboardTest.php`
- `tests/Feature/Api/TrabajoRequestTest.php`

**Criterio de aceptación:**

- Crear trabajo usa `en_curso`.
- `borrador` no aparece en UI viva.
- `cerrado` no se usa como estado funcional principal.
- Cancelados visibles al final.
- Marcar terminado registra fecha si aplica.
- Finalizado depende de ejecución y economía resuelta.

**Validaciones ejecutadas:**

- `php -l` en modelo, controller, requests, resource, `ClosureDashboardService`, `routes/web.php` y `routes/api.php`.
- `php artisan route:list`.
- `php artisan test --filter=TrabajoTest`.
- `php artisan test --filter=ClosureDashboardTest`.
- `php artisan test --filter=TrabajoRequestTest`.
- `php artisan test --filter=ContextCreationGuardTest`.
- `php artisan test --filter=PedidoTest`.
- `php artisan test --filter=FacturaTest`.
- `php artisan test --filter=EstacionesTest`.
- `npm.cmd run build` fuera del sandbox tras EPERM del binario nativo de Tailwind/Rolldown.

**Riesgos:**  
Riesgo histórico original resuelto por P1-11 en la demo local: `borrador`/`cerrado` salen del enum vivo de trabajos y las columnas legacy `trabajos.cerrado`, `fecha_cierre` e `id_usuario_cierre` se retiran del esquema demo. Queda solo deuda documental/histórica y módulos P2 no activos.

### Tarea P0-07 - Permisos/rutas mutables y seeders

**Estado:** cerrada  
**Área:** permisos / rutas / seguridad  
**Prioridad:** P0  
**Bloquea demo:** sí  
**Bloquea ERP completo:** sí

**Qué se decidió:**  
GET debe usar permisos de ver. POST debe usar crear. PUT/PATCH debe usar editar. DELETE no debe borrar físicamente si hay histórico. Dirección y admin técnico deben estar diferenciados.

**Estado real actual:**  
Las rutas mutables principales quedan protegidas con permisos por acción. GET usa permisos de ver, POST crear, PUT/PATCH editar y DELETE permisos específicos. Los slugs usados quedan sembrados o cubiertos por alias compatibles para roles existentes.

**Qué se hizo:**

- Separar middleware por verbo/acción en rutas web y API de trabajos, pedidos, facturas, clientes, estaciones, usuarios, auditoría e importaciones.
- Añadir permisos sembrados para crear/editar/eliminar/cambiar estado/exportar/limpiar donde el código ya los usaba o los necesitaba.
- Mantener compatibilidad con slugs legacy como `pedidos.gestionar`, `facturas.gestionar`, `estaciones.gestionar` y `empresas_contactos.gestionar` mediante alias en `User::hasPermission()`.
- Alinear roles funcionales: Dirección conserva gestión operativa, usuarios y auditoría; ejecución no gestiona usuarios ni auditoría; contabilidad opera facturas; admin técnico mantiene soporte.
- Sustituir hard delete de entidades principales por cancelación/anulación/desactivación cuando hay histórico: trabajos, pedidos, facturas, estaciones y clientes.
- Ajustar sidebar, listados y vistas Excel para no mostrar acciones de crear/editar/eliminar si falta el permiso correspondiente.

**Archivos principales modificados:**

- `routes/api.php`
- `routes/web.php`
- `app/Models/User.php`
- `database/seeders/PermisosSeeder.php`
- `database/seeders/RolPermisosSeeder.php`
- `app/Support/TrabajoPermission.php`
- `app/Http/Controllers/Api/TrabajoController.php`
- `app/Http/Controllers/Api/PedidoController.php`
- `app/Http/Controllers/Api/FacturaController.php`
- `app/Http/Controllers/Api/EstacionController.php`
- `app/Http/Controllers/Api/ClienteController.php`
- `app/Http/Controllers/AuditLogController.php`
- `resources/js/navigation/sidebar.js`
- `resources/js/Pages/Pedidos/Index.jsx`
- `resources/js/Pages/Facturas/Index.jsx`
- `resources/js/Pages/Clientes/Index.jsx`
- `resources/js/Pages/Estaciones/Index.jsx`
- `resources/js/Components/ui/*ExcelView.jsx`
- `tests/Feature/PermissionRoutesTest.php`
- `tests/Feature/PedidoTest.php`
- `tests/Feature/Api/ClientesEstacionesApiTest.php`

**Validaciones ejecutadas:**

- `php -l` en rutas, seeders, `User`, controladores API tocados, `AuditLogController`, `TrabajoPermission` y tests modificados.
- `php artisan route:list`.
- `php artisan test --filter=PermissionRoutesTest`.
- `php artisan test --filter=ContextCreationGuardTest`.
- `php artisan test --filter=TrabajoTest`.
- `php artisan test --filter=PedidoTest`.
- `php artisan test --filter=FacturaTest`.
- `php artisan test --filter=EstacionesTest`.
- `php artisan test --filter=ClosureDashboardTest`.
- `php artisan test --filter=ClientesEstacionesApiTest`.
- `php artisan test --filter=AuditLogTest`.
- `npm.cmd run build` fuera del sandbox tras EPERM del binario nativo de Tailwind/Rolldown.

**Criterio de aceptación:**

- GET usa permisos de ver.
- POST usa crear.
- PUT/PATCH usa editar.
- DELETE no borra físico si hay histórico.
- Slugs usados existen en seeders.
- Dirección y admin técnico quedan diferenciados.

**Riesgos:**  
Quedan alias de compatibilidad para permisos legacy hasta limpiar roles reales en datos productivos. Falta una pantalla de gobierno de roles/permisos; por ahora la matriz defendible vive en seeders. Importaciones conserva compatibilidad entre `importaciones.ejecutar` y `importaciones.confirmar` para no romper flujos actuales.

## 4. P1 - Necesario antes de demo/entrega seria

### P1-03 - Pulir vista moderna de trabajos para no arrastrar estados legacy

**Estado:** cerrada con evidencia específica  
**Área:** Ciete Moderno  
**Auditoría específica 2026-05-06:**  

- `Trabajos/Form.jsx` crea con `estado = en_curso` por defecto y el backend refuerza ese default en `StoreTrabajoRequest` y `TrabajoController::prepareTrabajoStateData()`.
- `Trabajos/Form.jsx`, `Trabajos/Index.jsx` y `TrabajosExcelView.jsx` solo ofrecen estados vivos: `en_curso`, `terminado`, `pendiente_facturar`, `facturado`, `finalizado` y `cancelado`.
- `cancelado` sigue visible y filtrable tanto en Ciete Moderno como en Ciete Excel.
- `TrabajoResource` traduce `borrador` -> `en_curso` y `cerrado` -> `finalizado` solo para lectura/compatibilidad, mientras `BadgeTrabajo` puede mostrar legacy de forma explícita sin reabrirlo como opción operativa.
- `StoreTrabajoRequest` y `UpdateTrabajoRequest` validan únicamente `Trabajo::ESTADOS_FUNCIONALES`; `TrabajoRequestTest` rechaza `borrador` y `cerrado` en altas.

**Motivo del cierre:**  
La vista moderna de trabajos ya no empuja al usuario a usar estados legacy, mantiene compatibilidad visual para datos históricos y queda alineada con Ciete Excel sin contradicción funcional nueva.

## 5. P2 - Posterior, no bloqueante

| ID | Tarea | Motivo |
|---|---|---|
| P2-01 | Importación Excel avanzada / UI de avisos | La carga real MOEVE/REPSOL queda validada por P1-12; queda automatizar detalle de errores, reintentos parciales y decisiones funcionales por columna/fila. |
| P2-02 | Legalizaciones completas | Módulo posterior; no debe bloquear cierre operativo actual. |
| P2-03 | Presupuestos/hoja de pedido | Fuera de alcance inmediato confirmado. |
| P2-04 | Cobros | Otro departamento; el ERP CIETE termina en factura. |
| P2-05 | Actualización automática de estaciones | Riesgo operativo alto; primero mantener maestro manual estable. |
| P2-06 | Costes/imputación avanzada | No pertenece al flujo principal actual. |
| P2-07 | Limpieza profunda de legacy no crítico | Solo para módulos no activos, documentación histórica o snapshots SQL antiguos que no alimentan el flujo vivo. |

## 6. P3 - Futuro o mejoras

| ID | Mejora | Motivo |
|---|---|---|
| P3-01 | Dashboards ejecutivos avanzados | Explotación posterior de datos ya consolidados. |
| P3-02 | Estadísticas históricas | Valor analítico cuando haya datos estables. |
| P3-03 | Automatizaciones | Reducir trabajo manual tras cerrar flujo base. |
| P3-04 | Integraciones externas | Fase futura, no necesaria para demo. |
| P3-05 | Virtualización avanzada de tablas | Solo si los listados crecen mucho. |
| P3-06 | Exportaciones complejas | Posterior al listado plano y detalle individual. |

## 7. Legacy identificado

| Legacy | Qué es | Qué hacemos ahora | Qué haremos después |
|---|---|---|---|
| `facturas.id_trabajo` | Cabecera antigua factura-trabajo | Mantener solo como campo derivado/auxiliar para filtros y ordenación; no es relación funcional ni fallback de exportación | Retirar cuando las vistas/importaciones ya no necesiten cabecera derivada |
| `factura_pedidos` | Pivote antiguo factura-pedido | Eliminado del esquema demo y del flujo vivo por P1-11 | No reintroducir; usar siempre `factura_items` |
| `borrador` | Estado antiguo | Eliminado de trabajos/pedidos vivos, seeders y UI; solo queda en migraciones correctivas y tests negativos que lo rechazan | No reintroducir |
| `cerrado` | Cierre antiguo | Eliminado como estado vivo de trabajos/pedidos y de exportaciones; solo queda en migraciones correctivas, tests negativos y textos históricos/P2 | No reintroducir; usar `finalizado` |
| `trabajos.cerrado`, `fecha_cierre`, `id_usuario_cierre` | Cierre antiguo de trabajos | Columnas retiradas del esquema demo y de la lógica viva por P1-11 | No reintroducir salvo migración histórica expresamente justificada |
| Estados factura tipo `cobrada`, `vencida`, `cobrada_parcial` | Cobro fuera de alcance | No exponer | Revisar si futuro incluye cobros |
| `cobros` | Módulo posterior | Ocultar flujo crítico | Fase futura |
| `presupuestos` | Módulo posterior con columnas/estados propios todavía antiguos | Fuera alcance inmediato; no forma parte del flujo vivo cerrado por P1-11 | Fase futura/P2 antes de activar presupuestos |
| `legalizaciones` | Módulo posterior | No bloquear demo | Fase futura |
| `CEPSA` | Nombre histórico | Solo queda como referencia explicativa/documental; demo y flujo vivo usan MOEVE | Mapear si aparece en Excel real de entrada |
| `OTRO` | Código interno antiguo de OTROS CLIENTES | Normalizado a `OTROS` en seeders/factories demo; `ContextGuard` sigue resolviendo por nombre/código de forma robusta | Mantener `OTROS CLIENTES` como nombre visible |
| `obras/proyectos` | Nombres antiguos | Mantener históricos | Usar trabajos en vivo |

## 8. Tareas cerradas

### Gobierno mínimo de Maestros: contratos, sociedades facturadoras y tarifarios

**Estado:** cerrada con CRUD mínimo separado  
**Área:** datos maestros / contratos / tarifarios / permisos / auditoría  

**Qué se cerró:**

- Nueva entrada de navegación **Maestros**, visible para perfiles con `maestros.ver`.
- Pantalla índice de Maestros separada de la operativa diaria, con accesos a empresas/clientes, estaciones, contratos, sociedades facturadoras permitidas, tarifarios, líneas de tarifario y catálogos auxiliares.
- Clientes y estaciones se reutilizan desde sus módulos existentes; no se duplican pantallas ni lógica.
- CRUD mínimo vivo para contratos, sociedades facturadoras permitidas, tarifarios y líneas de tarifario.
- Creación bloqueada desde TODOS para maestros contextuales; para crear hay que seleccionar MOEVE, REPSOL u OTROS CLIENTES.
- Validaciones de contexto cruzado: empresa, contrato, sociedad, tarifario y línea deben pertenecer al contexto activo real.
- Sociedades facturadoras permitidas siguen usando `contrato_empresas_facturadoras` como fuente de verdad para facturación y exigen empresa con CIF informado.
- Unicidad defendida para contrato por contexto, sociedad por contrato/empresa/contexto, tarifario por nombre+versión/contexto y código de tarifa por tarifario/contexto.
- Desactivación sin hard delete para contratos, sociedades permitidas, tarifarios y líneas de tarifario, con confirmación visual.
- Auditoría operativa para altas, ediciones y desactivaciones de contratos, sociedades permitidas, tarifarios y líneas de tarifario.
- No se crean trabajos, pedidos, `pedido_items`, facturas ni `factura_items` desde Maestros.

**Archivos principales tocados:**

- `app/Http/Controllers/MaestroController.php`
- `app/Http/Controllers/ContratoController.php`
- `app/Http/Controllers/ContratoEmpresaFacturadoraController.php`
- `app/Http/Controllers/TarifarioController.php`
- `app/Http/Controllers/TarifarioLineaController.php`
- `routes/web.php`
- `database/seeders/PermisosSeeder.php`
- `database/seeders/RolPermisosSeeder.php`
- `resources/js/navigation/sidebar.js`
- `resources/js/i18n/locales/es.js`
- `resources/js/i18n/locales/en.js`
- `resources/js/Pages/Maestros/Index.jsx`
- `resources/js/Pages/Contratos/Index.jsx`
- `resources/js/Pages/Contratos/Form.jsx`
- `resources/js/Pages/SociedadesFacturadoras/Index.jsx`
- `resources/js/Pages/Tarifarios/Index.jsx`
- `resources/js/Pages/Tarifarios/Form.jsx`
- `resources/js/Pages/Tarifarios/Lineas.jsx`
- `resources/js/Pages/Tarifarios/LineaForm.jsx`
- `tests/Feature/MaestrosTest.php`
- `docs/02_CLIENTE/tareasComparar.md`

**Validaciones ejecutadas:**

- `php -l` en controladores de Maestros, `routes/web.php`, seeders de permisos/roles y `MaestrosTest`.
- `php artisan route:list`.
- `php artisan migrate` (sin migraciones pendientes).
- `php artisan test --filter=MaestrosTest`.
- `php artisan test --filter=PermissionRoutesTest`.
- `php artisan test --filter=ContextCreationGuardTest`.
- `php artisan test --filter=FacturaTest`.
- `npm.cmd run build` pasó fuera del sandbox tras el EPERM conocido de Tailwind/Rolldown dentro del sandbox.

**Riesgos residuales:**

- Catálogos auxiliares (`unidades`, tipos de documento y tipos de trabajo) quedan centralizados visualmente como lectura/gestión mínima pendiente, sin CRUD completo en esta tarea.
- Falta cargar datos maestros reales definitivos para OTROS CLIENTES y depurar los avisos de Excel antes del cierre operativo de esta fase.
- Nota 2026-05-08: se ha preparado/corregido el SQL manual de muestra operativa de 50 casos en `database/manual/2026_05_07_insert_muestra_operativa_50_casos.sql`. No se ha ejecutado todavía. Queda pendiente ejecución controlada y validación posterior contra la base.
- No se ejecutó `migrate:fresh`, `migrate:refresh` ni `db:wipe` por alcance explícito de la tarea.

**Porcentaje:**  
Sube `BD/modelo negocio` a **83%**, `Contextos/roles/permisos` a **93%**, `Ciete Excel/Moderno` a **82%** y `Documentación` a **87%**. Total ponderado vigente: **86,2%**.

### P1-12 - Importación real controlada desde Excel actualizados MOEVE / REPSOL

**Estado:** cerrada con importación real controlada  
**Área:** importaciones / datos reales / validación integral  

**Comprobación previa de entorno:**

- `php artisan env` confirmó `local`.
- `.env` confirmó `APP_ENV=local`, `APP_DEBUG=true`, `DB_CONNECTION=mysql`, `DB_DATABASE=abaco_ciete`, `DB_USERNAME=root`.
- `php artisan migrate:status` se ejecutó antes del reset y mostró las migraciones aplicadas.
- Con entorno local/demo verificado, se ejecutó `php artisan migrate:fresh --seed` y después la importación real con `--commit`.

**Carpetas revisadas:**

- Solicitadas: `excelsactualizados/Moeve` y `excelsactualizados/Repsol`.
- Ruta real equivalente encontrada en el proyecto: `docs/Abaco/excelsactualizados/Moeve/Moeve` y `docs/Abaco/excelsactualizados/Repsol/Repsol`.
- Material complementario revisado: `docs/02_CLIENTE/materiales/Contrato 772 MOEVE - Tarifario.xlsx`.

**Excel encontrados e inventariados:**

- `docs/Abaco/excelsactualizados/Mapeo_Moeve_Repsol_Envolvente.xlsx`: hoja `Hoja1`, documento auxiliar de mapeo, no importable como contexto operativo.
- MOEVE:
  - `01 Control de Trabajos Moeve.xlsx`: hojas `Trabajos`, `Hoja2`, `FACTURAS EMITIDAS`, `Control`, `Hoja1`.
  - `02 Listado EESS España y Portugal 16-03-26.xlsx`: hojas `España 16-03-26`, `España 25-08-2025`, `España y Portugal 26-06-2024`, `Hoja1`, `Hoja3`.
  - `Contrato 772 MOEVE - Tarifario.xlsx`: hoja `TARIFARIO`.
- REPSOL:
  - `01 Control Trabajos DISEÑO REPSOL.xlsx`: `AUTOFACTURACION`, `ALFONSO PCN`, `Rangos`, `LISTADO EESS`, `TARIFA 23-27`.
  - `02 Control Trabajos EDIFICACIÓN.xlsx`: `AUTOFACTURACION`, `Rangos`, `LISTADO EESS`, `TARIFA 23-27`.
  - `03 Control Trabajos OBRAS REPSOL Z10.xlsx`: `AUTOFACTURACION REPSOL`, `OTROS`, `Rangos`, `LISTADO EESS`, `TARIFA 23-27`.
  - `03 Control Trabajos OBRAS REPSOL Z50.xlsx`: `Rangos`, `AUTOFACTURACION`, `LISTADO EESS`, `TARIFA 23-27`, `Adjud. 2023-2027`.
  - `05 Control Trabajos LICENCIAS REPSOL.xlsx`, `09 Control Trabajos FV REPSOL.xlsx`, `10 Control Trabajos ESTRUCTURAS Y VERTIDOS REPSOL.xlsx`, `12 Control Trabajos MTO REPSOL.xlsx`, `13 Control Trabajos PUNTOS DE RECARGA.xlsx`: hojas operativas `AUTOFACTURACION`, `LISTADO EESS`, `TARIFA 23-27` y hojas auxiliares `Rangos`/vacías según archivo.

**Mapeo aplicado:**

- MOEVE: `ES`/`CONCESIÓN` a estación, nombre/localidad/provincia/dirección/GPS a `estaciones_servicio`, `Nº` a trabajo, `Nº PEDIDO` e importes a `pedidos`/`pedido_items`, `Nº Factura`/`FACTURADO Solo Esther` a `facturas`/`factura_items`, `Contrato` a contrato/tarifario y `FACTURAS EMITIDAS` solo como enriquecimiento de cabecera si la factura ya existe por items.
- REPSOL: `Nº ES`/`C.EMP` a estación, `CODIGO SOLRED` y volumen a extensión REPSOL, `Nº AVISO`/`ORDEN MANTEN.` a cabecera de trabajo, `TIPO DE TRABAJO` a tipo vivo, `CÓDIGO SERVICIO`/`Número Tarifa (con punto)` a línea tarifaria, `Nº PEDIDO` a pedido, importes/unidades a `pedido_items`, `FACTURA` o `NUMERO 1ª/2ª FACTURA` a facturas con `factura_items`.
- CEPSA no se usa como contexto vivo; si aparece en fuente antigua se conserva solo como dato histórico/documental y se opera bajo MOEVE.
- `Mapeo_Moeve_Repsol_Envolvente.xlsx` queda clasificado como auxiliar/desconocido y no crea datos operativos.

**Resultado de importación real en local/demo:**

- Comando dry-run: `php artisan ciete:import-excels-actualizados --path=excelsactualizados --dry-run`.
- Reset demo: `php artisan migrate:fresh --seed`.
- Comando commit: `php artisan ciete:import-excels-actualizados --path=excelsactualizados --commit`.
- Filas leídas por el importador: **53.055**.
- Filas importadas/actualizadas: **51.288**.
- Filas ignoradas: **1.767**.
- Filas con aviso: **3.170**.
- Filas con error fatal: **0**.
- Archivos importables procesados: **11**; documento auxiliar de mapeo inventariado pero no importado.

**Datos finales en base local tras importación:**

- Contextos: 3 (`MOEVE`, `REPSOL`, `OTROS`).
- Empresas/clientes: 3.
- Estaciones: **6.540** total; MOEVE **3.237**, REPSOL **3.303**.
- Contratos: 4.
- Tarifarios: 7.
- Líneas tarifarias: 427.
- Trabajos: **11.614**; MOEVE **6.685**, REPSOL **4.929**.
- Pedidos: **9.685**; MOEVE **6.147**, REPSOL **3.538**.
- `pedido_items`: **9.701**.
- Facturas: **2.733**; MOEVE **1.502**, REPSOL **1.231**.
- `factura_items`: **9.194**.
- `contrato_empresas_facturadoras`: 4.
- Registros de importación: 11.

**Integridad validada después del commit:**

- 0 trabajos con `borrador`.
- 0 trabajos con `cerrado`.
- 0 pedidos con `borrador`.
- 0 pedidos con `cerrado`.
- `factura_pedidos` no existe.
- 0 facturas sin `factura_items`.
- 0 `factura_items` sin `pedido_item`.
- 0 `pedido_items` sin pedido.
- 0 pedidos sin trabajo.
- 0 trabajos sin contexto.
- 0 estaciones sin contexto.
- 0 estaciones duplicadas por código dentro del mismo contexto.
- 0 sociedades facturadoras sin CIF.
- 0 facturas con items sin sociedad/CIF válida por contrato.

**Problemas reales encontrados:**

- El Excel de mapeo envolvente contiene correspondencia/ayuda, pero no filas operativas importables.
- Hay filas MOEVE con importe pero sin número de pedido; se importa el trabajo si es seguro y se avisa sin crear `pedido_item`.
- Hay facturas históricas MOEVE en `FACTURAS EMITIDAS` que no enlazan con trabajos/pedidos actuales por `factura_items`; quedan como aviso y no se inventan líneas legacy.
- REPSOL contiene categorías/textos muy largos en celdas que alimentan campos cortos; el importador normaliza códigos cortos estables con hash y trunca cabeceras operativas a límites de esquema sin cambiar estructura.
- La hoja `AUTOFACTURACION` de EDIFICACIÓN declara un rango usado enorme en Excel, pero el lector streaming solo procesa filas con celdas reales.
- Columnas sin destino claro quedan documentadas como desconocidas, por ejemplo `TEO`, totales de hojas auxiliares, columnas técnicas de mantenimiento/licencias y datos de contacto extendidos no mapeados al flujo vivo.

**Archivos principales tocados:**

- `app/Services/Importacion/ExcelInventoryService.php`
- `app/Services/Importacion/ExcelHeaderNormalizer.php`
- `app/Services/Importacion/XlsxWorkbookReader.php`
- `app/Services/Importacion/CieteExcelImportService.php`
- `app/Services/Importacion/MoeveExcelImporter.php`
- `app/Services/Importacion/RepsolExcelImporter.php`
- `routes/console.php`
- `app/Models/Importacion.php`
- `app/Models/ImportacionFila.php`
- `app/Http/Controllers/ImportacionController.php`
- `resources/js/Pages/Importaciones/Index.jsx`
- `tests/Feature/ExcelImportTest.php`
- `docs/02_CLIENTE/tareasComparar.md`

**Validaciones ejecutadas:**

- Entorno: `php artisan env`, revisión de `.env`, `php artisan migrate:status`.
- Sintaxis: `php -l routes/web.php`, `php -l routes/api.php`, `php -l routes/console.php`, `php -l app/Http/Controllers/ImportacionController.php`, `php -l app/Http/Requests/Api/StoreImportacionRequest.php`, `php -l` de todos los servicios de importación nuevos.
- Rutas: `php artisan route:list`.
- Reset: `php artisan migrate:fresh --seed`.
- Importación: `php artisan ciete:import-excels-actualizados --path=excelsactualizados --dry-run` y `php artisan ciete:import-excels-actualizados --path=excelsactualizados --commit`.
- Tests filtrados: `ContextCreationGuardTest`, `PermissionRoutesTest`, `TrabajoTest`, `TrabajoRequestTest`, `PedidoTest`, `FacturaTest`, `FacturaExportTest`, `EstacionesTest`, `ClientesEstacionesApiTest`, `ClosureDashboardTest`, `AuditLogTest`, `Importacion`, `ExcelImport`.
- Suite completa: `php artisan test` (167 tests, 785 assertions).
- Frontend: `npm.cmd run build` pasó fuera del sandbox tras EPERM conocido de Tailwind/Rolldown dentro del sandbox.
- Calidad: `git diff --check` sigue fallando solo por trailing whitespace en documentación histórica/viva preexistente fuera de alcance; no se corrige por la regla de no tocar documentación histórica.

**Porcentaje:**  
Sube `BD/modelo negocio` a **80%**, `Trabajos/estaciones` a **88%**, `Pedidos/ítems` a **80%**, `Facturación` a **85%**, `Documentación` a **86%** y `Rendimiento/seguridad/legacy` a **90%**. El total ponderado queda en **85,1%**.

**Motivo del cierre:**
La importación real ya es ejecutable, reversible por reset local y validada con volumen MOEVE/REPSOL. El ERP funciona sin reintroducir legacy crítico: las facturas importadas tienen `factura_items`, los pedidos tienen `pedido_items`, las relaciones conservan contexto y la integridad fiscal sociedad/CIF pasa.

**Riesgos residuales:**
Falta decisión funcional sobre las filas ignoradas/con aviso, columnas sin destino claro y facturas históricas MOEVE no enlazadas. OTROS CLIENTES no tiene Excel propio cargado en esta tarea. Conviene una pantalla posterior de detalle de errores/avisos por archivo/hoja/fila y completar datos reales desde Maestros antes de entrega final.

### P1-11 - Limpieza estructural de legacy, reseteo demo alineado y preparación para datos reales

**Estado:** cerrada con limpieza estructural  
**Área:** base de datos / seeders / tests / legacy  

**Comprobación previa de entorno:**

- `php artisan env` confirmó `local`.
- `.env` confirmó `APP_ENV=local`, `APP_DEBUG=true`, `DB_CONNECTION=mysql`, `DB_DATABASE=abaco_ciete`, `DB_USERNAME=root`.
- `php artisan migrate:status` se ejecutó antes del reset y confirmó el estado migrado de la base demo.
- Con entorno local/demo verificado, se ejecutó `php artisan migrate:fresh --seed` sobre datos demo.

**Qué se limpió:**

- Estados legacy de trabajos: `borrador` y `cerrado` salen del enum vivo, de seeders, factories, recursos y UI. Los tests solo los conservan como casos negativos que deben ser rechazados.
- Columnas antiguas de cierre de trabajos: se retiran `trabajos.cerrado`, `trabajos.fecha_cierre` e `trabajos.id_usuario_cierre`; el flujo vivo usa `estado = finalizado` y `fecha_terminacion` cuando aplica.
- Facturación legacy: se elimina `factura_pedidos`; las exportaciones de factura ya no inventan líneas desde pivote antiguo ni desde `facturas.id_trabajo`.
- `facturas.id_trabajo` queda solo como cabecera derivada/auxiliar para filtros y ordenación, no como relación funcional principal.
- Permisos legacy: se eliminan alias vivos de `User::hasPermission()` y los slugs `*.gestionar`/`trabajos.cerrar` quedan solo en limpieza idempotente del seeder.
- Contexto OTROS CLIENTES: el código demo se normaliza a `OTROS`; el nombre visible sigue siendo `OTROS CLIENTES`.
- Seeders/factories demo se alinean con facturas por `factura_items`, pedidos por `pedido_items`, sociedades permitidas por contrato y usuarios/roles por permisos de acción.
- Tests que defendían compatibilidad demo antigua se reescriben para defender el modelo actual.

**Legacy mantenido con motivo concreto:**

- `facturas.id_trabajo`: auxiliar derivado para filtros/ordenación y compatibilidad de cabecera; no se usa para reconstruir detalle ni exportación.
- `presupuestos`: módulo P2 fuera del flujo vivo actual; conserva su propio estado `borrador` y cierre interno hasta que se active esa fase.
- Referencias `borrador`/`cerrado` en migraciones correctivas y tests negativos: necesarias para migrar/rechazar legacy, no para operar.
- `CEPSA`: solo referencia histórica/explicativa de MOEVE en documentación o ayuda; no se usa como contexto demo vivo.

**Archivos principales tocados:**

- `database/migrations/2026_03_24_000050_create_trabajos_operativa_tables.php`
- `database/migrations/2026_05_02_165419_add_pendiente_facturar_to_trabajos_estado_enum.php`
- `database/migrations/2026_05_02_180000_add_borrador_and_cancelado_to_pedidos_estado_enum.php`
- `database/migrations/2026_05_06_000140_cleanup_demo_legacy_schema.php`
- `app/Models/Trabajo.php`, `app/Models/Factura.php`, `app/Models/Pedido.php`, `app/Models/User.php`
- `app/Http/Controllers/Api/TrabajoController.php`, `app/Http/Controllers/Api/FacturaController.php`, `app/Http/Controllers/DashboardController.php`, `app/Http/Controllers/Admin/DashboardController.php`, `app/Http/Controllers/ImportacionController.php`
- `app/Services/ClosureDashboardService.php`, `app/Support/TrabajoPermission.php`
- `app/Http/Resources/Api/TrabajoResource.php`, `app/Http/Resources/Api/FacturaResource.php`
- `app/Http/Requests/Api/StoreTrabajoRequest.php`, `app/Http/Requests/Api/UpdateTrabajoRequest.php`, `app/Http/Requests/Api/StoreFacturaRequest.php`, `app/Http/Requests/Api/UpdateFacturaRequest.php`, `app/Http/Requests/Api/StorePedidoRequest.php`, `app/Http/Requests/Api/UpdatePedidoRequest.php`
- `database/seeders/PermisosSeeder.php`, `database/seeders/RolPermisosSeeder.php`, `database/seeders/ContextosClienteSeeder.php`, `database/seeders/DemoCieteOperativaSeeder.php`, `database/seeders/CatalogoBaseSeeder.php`
- `database/factories/TrabajoFactory.php`, `database/factories/PedidoFactory.php`, `database/factories/UserFactory.php`
- `resources/js/Pages/Trabajos/Form.jsx`, `resources/js/Pages/Trabajos/Index.jsx`, `resources/js/Pages/Pedidos/Index.jsx`, `resources/js/Pages/Pedidos/Form.jsx`, `resources/js/Pages/Facturas/Index.jsx`, `resources/js/Pages/Facturas/Form.jsx`, `resources/js/Pages/Cierre/Dashboard.jsx`, `resources/js/Pages/Dashboard.jsx`, `resources/js/Pages/Help.jsx`
- `resources/js/Components/ui/BadgeTrabajo.jsx`, `resources/js/Components/ui/BadgePedidos.jsx`, `resources/js/Components/ui/BadgeFactura.jsx`, `resources/js/Components/ui/BadgeEstado.jsx`, `resources/js/Components/ui/FacturasExcelView.jsx`
- `resources/js/i18n/locales/es.js`, `resources/js/i18n/locales/en.js`
- `routes/web.php`
- Tests de `tests/Feature` relacionados con contexto, permisos, trabajos, pedidos, facturas, exportaciones, cierre, roles y estaciones/clientes.

**Validaciones ejecutadas:**

- Seguridad previa: `php artisan env`, revisión de `.env`, `php artisan migrate:status`.
- Sintaxis: `php -l routes/web.php`, `php -l routes/api.php`, `php -l app/Models/Trabajo.php`, `php -l app/Models/Factura.php`, `php -l app/Models/User.php`, `php -l app/Http/Controllers/Api/TrabajoController.php`, `php -l app/Http/Controllers/Api/FacturaController.php`, `php -l app/Services/ClosureDashboardService.php`, `php -l app/Support/TrabajoPermission.php`.
- Rutas: `php artisan route:list`.
- Reset demo: `php artisan migrate:fresh --seed`.
- Verificación de datos demo: 0 trabajos con `borrador`/`cerrado`, sin columnas `trabajos.cerrado`/`fecha_cierre`/`id_usuario_cierre`, sin tabla `factura_pedidos`, 0 pedidos con `borrador`/`cerrado` y 0 facturas demo sin `factura_items`.
- Tests filtrados: `ContextCreationGuardTest`, `PermissionRoutesTest`, `TrabajoTest`, `TrabajoRequestTest`, `PedidoTest`, `FacturaTest`, `FacturaExportTest`, `EstacionesTest`, `ClientesEstacionesApiTest`, `ClosureDashboardTest`, `AuditLogTest`, `RoleModuleAccessTest`.
- Suite completa: `php artisan test` (162 tests, 753 assertions).
- Frontend: `npm.cmd run build` pasó fuera del sandbox tras el EPERM conocido de Tailwind/Rolldown dentro del sandbox.
- `rg` de legacy revisado: las referencias restantes quedan acotadas a migraciones correctivas, tests negativos, limpieza idempotente de permisos y módulos P2/históricos.
- `git diff --check` detecta trailing whitespace preexistente en documentación histórica fuera de alcance; no se corrige por la regla de no tocar documentación histórica.

**Porcentaje:**  
Sube `BD/modelo negocio` a **76%**, `Contextos/roles/permisos` a **92%**, `Trabajos/estaciones` a **83%**, `Pedidos/ítems` a **73%**, `Facturación` a **81%**, `Documentación` a **84%** y `Rendimiento/seguridad/legacy` a **89%**. Total ponderado: **82,1%**.

**Motivo del cierre:**
La demo ya puede reconstruirse desde cero con seeders coherentes con el modelo vivo, sin relaciones legacy críticas ni estados antiguos en trabajos/pedidos/facturas. La siguiente fase debe cargar datos reales, no seguir protegiendo datos demo antiguos.

**Riesgos residuales:**
`facturas.id_trabajo` sigue como cabecera derivada y los módulos P2 (`presupuestos`, `cobros`, legalizaciones completas) mantienen deuda propia fuera del flujo crítico. Las instantáneas SQL antiguas o documentación histórica pueden conservar nombres legacy, pero no alimentan el flujo vivo validado.

### P1-10 - Revisión responsive final de Ciete Excel y Ciete Moderno

**Estado:** cerrada  
**Área:** frontend / layout / UX operativa  

**Qué se revisó:**

- Shell autenticado: sidebar fijo, topbar, selector de contexto, preferencias compactas y comportamiento en portátil/móvil.
- Ciete Excel y Ciete Moderno de trabajos, pedidos, facturas, estaciones, cierre y auditoría.
- Formularios principales de trabajos, pedidos, facturas y estaciones con foco en apilado, errores y acciones finales.
- Modales operativos y tablas densas con scroll horizontal interno controlado.
- Reauditoría de la pasada de Copilot GPT 5.4: árbol de trabajo, diff frontend, rutas reales de componentes, clases Tailwind v4, JSX, build y tests.
- Rutas equivalentes confirmadas: no existen `tailwind.config.js`, `resources/js/Components/ui/AppSidebar.jsx`, `resources/js/Components/ui/Topbar.jsx`, `resources/js/Components/ui/ContextSelector.jsx` ni `resources/js/Components/ui/VisualStyleSelector.jsx`; los componentes vivos son `resources/js/Layouts/AuthenticatedLayout.jsx`, `resources/js/Components/TopNavbar.jsx`, `resources/js/Components/ContextSelector.jsx`, `resources/js/Components/VisualStyleSelector.jsx` y configuración Tailwind 4 en `resources/css/app.css` + `vite.config.js`.

**Qué se corrigió:**

- La topbar de escritorio deja de forzar una sola línea en portátil: navegación secundaria y selectores compactos ahora pueden refluír sin ocultar el contexto activo.
- Los selectores compactos de estilo/contexto ya envuelven correctamente y no empujan el layout fuera de pantalla.
- El drawer móvil de preferencias vuelve a exponer el selector Ciete Excel/Ciete Moderno; antes en móvil solo estaban contexto, tema e idioma.
- El modo visual se inicializa desde `auth.user.interface_mode` y se sincroniza con el provider de tema, evitando que localStorage muestre un modo distinto al que renderizan las páginas.
- Las vistas principales de trabajos, pedidos, facturas, clientes, estaciones y auditoría consumen el modo visual sincronizado; se elimina la referencia obsoleta a `visualStyle === 'operative'`.
- Los modales limitan mejor su ancho útil en móvil y mantienen scroll interno dentro del panel.
- Facturas, trabajos y pedidos mejoran contadores, paginaciones y grupos de acciones para evitar aplastamientos en anchos intermedios.
- Facturas editar/crear ajusta mejor los bloques de importes y la zona de `pedido_items`, manteniendo tabla interna y panel lateral utilizables en portátil.
- Estaciones en vista moderna relaja la rejilla de tres columnas en portátil para que código, nombre, municipio/provincia y acciones no se pisen.
- Auditoría reorganiza la barra de filtros y acciones para evitar una fila `xl` excesivamente comprimida y mantiene exportaciones/limpieza accesibles.
- El panel de cierre evita forzar un ancho mínimo agresivo en el bloque superior y mantiene la tabla grande con scroll interno contenido.
- `ItemsTable` reduce el ancho mínimo exigido para que el formulario de pedidos sea más usable en pantallas estrechas sin romper IDs ni edición.

**Archivos principales tocados:**  
`resources/js/Layouts/AuthenticatedLayout.jsx`, `resources/js/Components/TopNavbar.jsx`, `resources/js/Components/GlobalPreferenceSelectors.jsx`, `resources/js/Components/ContextSelector.jsx`, `resources/js/Components/VisualStyleSelector.jsx`, `resources/js/Components/MobilePreferencesDrawer.jsx`, `resources/js/Components/Modal.jsx`, `resources/js/Components/ui/ItemsTable.jsx`, `resources/js/theme/index.js`, `resources/js/app.jsx`, `resources/js/Pages/Trabajos/Index.jsx`, `resources/js/Pages/Pedidos/Index.jsx`, `resources/js/Pages/Facturas/Index.jsx`, `resources/js/Pages/Facturas/Form.jsx`, `resources/js/Pages/Clientes/Index.jsx`, `resources/js/Pages/Estaciones/Index.jsx`, `resources/js/Pages/Cierre/Dashboard.jsx`, `resources/js/Pages/AuditLog/Index.jsx`, `resources/css/app.css`, `resources/css/variables.css`.

**Validaciones ejecutadas:**  
`php -l routes/web.php`, `php -l routes/api.php`, `php artisan route:list`, `php artisan test --filter=ContextCreationGuardTest`, `php artisan test --filter=PermissionRoutesTest`, `php artisan test --filter=TrabajoTest`, `php artisan test --filter=PedidoTest`, `php artisan test --filter=FacturaTest`, `php artisan test --filter=FacturaExportTest`, `php artisan test --filter=EstacionesTest`, `php artisan test --filter=ClientesEstacionesApiTest`, `php artisan test --filter=ClosureDashboardTest`, `php artisan test --filter=AuditLogTest`, `npm.cmd run build`, `git diff --check`.

**Diagnóstico de “32 problems found” / “1 problem found”:**  
No se encontró un registro persistido de esos mensajes en el repo. La build dentro del sandbox falló por `EPERM` al cargar el binario nativo de Tailwind/Rolldown (`@tailwindcss/oxide`), y al reintentar fuera del sandbox pasó correctamente. Con build limpia, `git diff --check` limpio y tests verdes, esos mensajes quedan clasificados como ruido del entorno/editor o falsos positivos no bloqueantes; el único problema real detectado fue la desalineación del selector visual móvil/provider descrita arriba.

**Motivo del cierre:**
La shell mantiene visible el contexto activo, no se detectan regresiones funcionales, el build pasa fuera del sandbox tras el EPERM conocido y las pantallas operativas principales quedan usables en escritorio, portátil y móvil manteniendo tablas densas con scroll interno cuando procede, sin convertir Ciete Excel en cards ni rediseñar Ciete Moderno.

**Porcentaje:**  
En el cierre original de P1-10 se mantuvo `Ciete Excel/Moderno` en **80%** y el ERP en **78,1%**. Tras P1-11, el porcentaje global vigente queda recalculado a **82,1%** por limpieza estructural de legacy, no por cambios responsive adicionales.

**Riesgos residuales:**
La tarea cierra el responsive operativo de esta fase, pero sigue siendo recomendable una pasada visual con datos productivos densos y dispositivos reales antes de entrega final para ajustar microdensidad tipográfica o prioridades de columna si creciera el volumen.

### P1-09 - Revisión rendimiento tras cerrar facturación

**Estado:** cerrada  
**Área:** rendimiento / facturación / cierre  

**Qué se revisó:**

- Listado, detalle y exportación de facturas.
- `FacturaResource` y sus relaciones derivadas por `factura_items`.
- Ciete Excel/Moderno de facturas a nivel de payload recibido.
- Pedidos con `pedido_items` y trabajos con agregados de pedidos.
- Panel de cierre/finalización con cálculo económico desde `factura_items`.
- Auditoría: el índice ya pagina y precarga usuario/contexto; se deja como riesgo la exportación XLSX completa si el volumen crece.
- Estaciones/clientes: no se detectó carga crítica nueva en esta tarea.

**Qué se optimizó:**

- Se eliminó el N+1 evidente de `FacturaResource::sociedadCifValidada()` en listados: `Factura::scopeWithSociedadCifValidada()` precalcula la validación sociedad/CIF con `selectSub` sobre `contrato_empresas_facturadoras`.
- El listado API y la ruta web de facturas usan esa bandera precalculada, evitando una consulta `exists()` por factura renderizada.
- La exportación CSV de listado de facturas deja de cargar toda la colección antes de escribir el archivo: ahora cuenta una vez para auditoría y escribe por chunks de 200 manteniendo eager loading y seguridad de contexto/permisos.
- `ClosureDashboardService` mantiene la lógica funcional `finalizado` y legalizaciones no bloqueantes, pero carga columnas mínimas en pedidos, `pedido_items`, `factura_items`, facturas legacy y legalizaciones para reducir memoria/payload.
- No se añadieron índices ni migraciones: no se detectó una necesidad clara que justificara tocar estructura.

**Archivos principales tocados:**  
`app/Models/Factura.php`, `app/Http/Resources/Api/FacturaResource.php`, `app/Http/Controllers/Api/FacturaController.php`, `app/Services/ClosureDashboardService.php`, `routes/web.php`, `docs/02_CLIENTE/tareasComparar.md`.

**Validaciones ejecutadas:**  
`php -l` en controlador/resource/modelos/rutas/cierre/auditoría, `php artisan route:list`, `FacturaExportTest`, `FacturaTest`, `PedidoTest`, `TrabajoTest`, `PermissionRoutesTest`, `ContextCreationGuardTest` y `npm.cmd run build` (primer intento con EPERM Tailwind/Rolldown en sandbox; reintento fuera del sandbox correcto).

**Riesgos residuales:**  
La exportación CSV ya trabaja por chunks, pero XLSX/PDF complejos siguen fuera de alcance. La exportación XLSX de Auditoría conserva carga en memoria por la librería de hoja de cálculo. No se hizo auditoría de índices con EXPLAIN sobre volumen real porque la BD actual es demo y no hay datos productivos.

### P1-08 - Exportación individual de factura con detalle

**Estado:** cerrada  
**Área:** facturas  

**Qué se hizo:**

- Se añadió exportación CSV descargable de factura individual en `GET /api/v1/facturas/{factura}/export`, protegida por `facturas.exportar`.
- La cabecera exporta número, contexto, sociedad facturadora, CIF, cliente, fechas, estado, total, importe asignado, diferencia, estado de cuadre, pedidos, trabajos, contrato y tarifarios.
- El detalle exporta líneas desde `factura_items` con `id_factura_item`, `id_pedido_item`, código servicio, número tarifa, descripción, unidades, importe, observaciones, pedido, trabajo, estación, contrato/tarifa y origen.
- Tras P1-11, el fallback de lectura legacy queda retirado: una factura sin `factura_items` exporta cabecera sin inventar líneas desde `factura_pedidos` ni desde `id_trabajo`.
- Se añadió acción visible en ficha de factura y listado, solo para usuarios con permiso de exportar.

**Archivos principales tocados:**  
`FacturaController`, `Factura`, `routes/api.php`, `routes/web.php`, `Facturas/Index.jsx`, `Facturas/Form.jsx`, `FacturasExcelView.jsx`, locales ES/EN, seeders/permisos y `FacturaExportTest`.

**Validaciones ejecutadas:**  
`php -l` en controlador/resource/modelos/rutas, `php artisan route:list`, `FacturaExportTest`, `FacturaTest`, `PermissionRoutesTest`, `ContextCreationGuardTest` y `npm.cmd run build` (primer intento con EPERM Tailwind/Rolldown en sandbox; reintento fuera del sandbox correcto).

### P1-07 - Exportación de listado de facturas filtrado/seleccionado

**Estado:** cerrada  
**Área:** facturas  

**Qué se hizo:**

- Se añadió exportación CSV descargable de listado en `GET /api/v1/facturas/export`, protegida por `facturas.exportar`.
- La exportación respeta filtros vivos (`search`, `estado`, trabajo, empresa cliente y fechas), contexto activo y vista TODOS con contextos accesibles.
- Se soporta selección manual mediante `ids`, siempre revalidada en backend por contexto/permisos.
- El CSV incluye número de factura, contexto, cliente, sociedad facturadora, CIF, fecha emisión, vencimiento, estado, total, importe asignado, diferencia, estado de cuadre, pedidos, trabajos, contrato y tarifarios.
- Se registra auditoría operativa de la exportación y se añadió botón “Exportar listado” y “Exportar seleccionadas” en Ciete Moderno/Ciete Excel.

**Archivos principales tocados:**  
`FacturaController`, `Factura`, `routes/api.php`, `routes/web.php`, `User`, `PermisosSeeder`, `RolPermisosSeeder`, `Facturas/Index.jsx`, `FacturasExcelView.jsx`, locales ES/EN, `PermissionRoutesTest` y `FacturaExportTest`.

**Validaciones ejecutadas:**  
`php -l` en controlador/resource/modelos/rutas, `php artisan route:list`, `FacturaExportTest`, `FacturaTest`, `PermissionRoutesTest`, `ContextCreationGuardTest` y `npm.cmd run build` (primer intento con EPERM Tailwind/Rolldown en sandbox; reintento fuera del sandbox correcto).

### P1-06 - Dejar de auditar preferencias visuales como evento operativo real

**Estado:** cerrada  
**Área:** auditoría / perfil  
**Prioridad:** P1

**Qué se cerró:**

- `ProfileController::updatePreferences()` deja de escribir nuevos registros de auditoría por cambios de `interface_mode`.
- La vista de Auditoría mantiene por defecto `solo_datos = true` y se valida con test que un log legacy de preferencia visual no aparece en la actividad operativa.
- `LocaleController` sigue sin auditar cambios de idioma y el tema visual sigue resolviéndose en frontend, así que no entran como actividad operativa nueva.
- No se borran logs antiguos: si existen trazas legacy de perfil/preferencias, quedan fuera del filtro operativo por defecto.

**Archivos principales modificados:**

- `app/Http/Controllers/ProfileController.php`
- `tests/Feature/AuditLogTest.php`
- `docs/02_CLIENTE/tareasComparar.md`

**Validaciones ejecutadas:**

- `php -l app/Http/Controllers/AuditLogController.php`
- `php -l app/Http/Controllers/ProfileController.php`
- `php artisan route:list`
- `php artisan test --filter=AuditLogTest`
- `npm.cmd run build` falló dentro del sandbox por EPERM del binario nativo Tailwind/Rolldown y pasó fuera del sandbox.

**Riesgos residuales:**

- `ActiveContextController` sigue dejando traza técnica de cambio de contexto, pero permanece fuera del filtro operativo por defecto y no es una preferencia visual.
- Si en el futuro se quisieran conservar preferencias UX para soporte técnico, convendrá separarlas explícitamente en un canal técnico distinto de la Auditoría operativa.

### P1-05 - Auditoría de maestros operativos: clientes, estaciones, contratos, tarifas

**Estado:** cerrada  
**Área:** auditoría  
**Prioridad:** P1

**Qué se cerró:**

- Clientes/empresas ya auditan alta, edición/cambio de estado y desactivación en `ClienteController`, con usuario, contexto, módulo, entidad, valores antes/después y descripción comprensible.
- Estaciones ya mantenían alta, edición/cambio de estado y desactivación auditadas; se revalidó con pruebas de feature para dejar la cobertura explícita.
- La vista operativa de Auditoría sigue filtrando por defecto solo módulos y acciones de negocio, evitando saturación con ruido técnico o cosmético.
- Se revisó `contratos`, `tarifarios`, `tarifario_lineas` y `contrato_empresas_facturadoras`: en el cierre original de esta tarea no tenían CRUD vivo propio. El gobierno mínimo posterior de Maestros ya añade CRUD y auditoría para esos modelos.

**Archivos principales modificados:**

- `app/Http/Controllers/Api/ClienteController.php`
- `tests/Feature/Api/ClientesEstacionesApiTest.php`
- `tests/Feature/AuditLogTest.php`
- `docs/02_CLIENTE/tareasComparar.md`

**Validaciones ejecutadas:**

- `php -l app/Http/Controllers/AuditLogController.php`
- `php -l app/Http/Controllers/Api/ClienteController.php`
- `php -l app/Http/Controllers/Api/EstacionController.php`
- `php artisan route:list`
- `php artisan test --filter=ClientesEstacionesApiTest`
- `php artisan test --filter=EstacionesTest`
- `php artisan test --filter=ContextCreationGuardTest`
- `php artisan test --filter=AuditLogTest`
- `npm.cmd run build` falló dentro del sandbox por EPERM del binario nativo Tailwind/Rolldown y pasó fuera del sandbox.

**Riesgos residuales:**

- La auditoría completa de `contratos`, `tarifarios`, `tarifario_lineas` y `contrato_empresas_facturadoras` queda cubierta después por el panel mínimo de Maestros. Siguen pendientes CRUDs más ricos de catálogos auxiliares si negocio los activa.
- La limpieza profunda de logs legacy antiguos sigue fuera de alcance; el objetivo cerrado aquí es que no contaminen la vista operativa desde ahora.

### P1-02 - Alinear `ClosureDashboardService` con `finalizado`, no `cerrado`, y legalizaciones no bloqueantes

**Estado:** cerrada  
**Área:** cierre / dirección  
**Prioridad:** P1

**Qué se cerró:**

- `ClosureDashboardService` decide la finalización viva por `estado = finalizado` y deja `cerrado`/`fecha_cierre` solo como compatibilidad de lectura para históricos legacy.
- `readyToClose` desaparece como concepto principal del panel y del checklist vivo; el flujo usa `readyToFinalize`.
- El payload del panel deja de exponer `fechaCierre` como dato principal y pasa a `fechaFinalizacion` con `fechaFinalizacionLegacy` separada cuando existe histórico legacy.
- `TrabajoPermission` protege trabajos `finalizado` aunque `cerrado` sea `false/null`.
- `TrabajoController` deja de construir el bloqueo de edición sobre `trabajo->cerrado` y delega en la protección funcional/legacy centralizada.
- Las legalizaciones siguen siendo informativas y no bloqueantes en esta fase.
- La economía del panel prioriza `pedidos.items.facturaItems` y hace fallback legacy solo cuando no existen líneas facturadas.

**Archivos principales modificados:**

- `app/Models/Trabajo.php`
- `app/Services/ClosureDashboardService.php`
- `app/Support/TrabajoPermission.php`
- `app/Http/Controllers/Api/TrabajoController.php`
- `app/Http/Controllers/ClosureDashboardController.php`
- `resources/js/Pages/Cierre/Dashboard.jsx`
- `resources/js/i18n/locales/es.js`
- `resources/js/i18n/locales/en.js`
- `tests/Feature/ClosureDashboardTest.php`
- `tests/Feature/TrabajoTest.php`

**Validaciones ejecutadas:**

- `php -l app/Services/ClosureDashboardService.php`
- `php -l app/Http/Controllers/ClosureDashboardController.php`
- `php -l app/Support/TrabajoPermission.php`
- `php -l app/Http/Controllers/Api/TrabajoController.php`
- `php -l app/Models/Trabajo.php`
- `php -l app/Http/Resources/Api/TrabajoResource.php`
- `php artisan route:list`
- `php artisan test --filter=ClosureDashboardTest`
- `php artisan test --filter=TrabajoTest`
- `php artisan test --filter=TrabajoRequestTest`
- `php artisan test --filter=FacturaTest`
- `npm.cmd run build` falló dentro del sandbox por `EPERM` del binario nativo de Tailwind/Rolldown y pasó al reintentar fuera del sandbox.

**Motivo del cierre:**
La lógica viva del panel de cierre ya no depende de `trabajos.cerrado` para finalizar. Tras P1-11, la compatibilidad de lectura de columnas legacy de trabajos queda retirada de la demo y el panel opera solo con `finalizado`, legalizaciones no bloqueantes, prioridad de `factura_items` y protección funcional de `finalizado`.

### P1-01 - Sustituir hard delete por baja/anulación/soft delete operativo en entidades con histórico

**Estado:** cerrada  
**Área:** histórico / datos  
**Prioridad:** P1

**Qué se revisó y cerró:**

- Se auditó el uso real de `delete()`/`destroy` en backend, rutas, tests y frontend.
- Las entidades principales del flujo vivo quedan consolidadas sin hard delete peligroso:
  - trabajos: cancelar,
  - pedidos: cancelar,
  - facturas: anular,
  - estaciones: desactivar,
  - clientes/empresas: desactivar.
- Se añadió auditoría explícita de desactivación para estaciones y clientes/empresas.
- La UI deja de vender estas acciones como “Eliminar” y las nombra según el comportamiento real: cancelar, anular o desactivar.
- La limpieza de Auditoría sigue siendo controlada por Dirección y deja rastro propio fuera de la operación eliminada.

**Hard deletes revisados que siguen permitidos y por qué:**

- `pedido_items`: solo se borran físicamente al editar un pedido cuando la línea omitida **no** tiene `factura_items`.
- `factura_items`: solo se borran físicamente al editar una factura para quitar líneas de esa factura; la operación queda auditada y no borra `pedido_items`.
- `audit_log`: la limpieza controlada sigue siendo borrado físico intencional, restringido y trazado.
- Ficheros temporales de importación: borrado físico permitido porque no son dato maestro ni histórico funcional.
- Seeders demo/tests y migraciones de normalización: los deletes físicos quedan limitados a idempotencia técnica y no al flujo vivo del ERP.

**Entidades secundarias críticas revisadas:**

- `contratos`, `tarifarios`, `tarifario_lineas` y `contrato_empresas_facturadoras` ya disponen de `activo`/`estado` y no tienen ruta viva de borrado físico en esta fase.
- `usuarios` ya operan por activación/desactivación; no hay destroy funcional.
- `roles`, `permisos`, `usuario_contextos` y `rol_permisos` no tienen borrado vivo de negocio; los deletes detectados quedan en seeders/sincronización técnica.
- `mensajes`, `soporte`, `legalizaciones`, `cobros`, `presupuestos` e `importaciones` no exponen un flujo vivo de hard delete peligroso en la fase actual.

**Archivos principales modificados:**

- `app/Http/Controllers/Api/ClienteController.php`
- `app/Http/Controllers/Api/EstacionController.php`
- `app/Http/Controllers/AuditLogController.php`
- `resources/js/Pages/Trabajos/Index.jsx`
- `resources/js/Pages/Pedidos/Index.jsx`
- `resources/js/Pages/Facturas/Index.jsx`
- `resources/js/Pages/Clientes/Index.jsx`
- `resources/js/Pages/Estaciones/Index.jsx`
- `resources/js/i18n/locales/es.js`
- `resources/js/i18n/locales/en.js`
- `tests/Feature/TrabajoTest.php`
- `tests/Feature/PedidoTest.php`
- `tests/Feature/FacturaTest.php`
- `tests/Feature/Api/ClientesEstacionesApiTest.php`
- `tests/Feature/AuditLogTest.php`

**Migraciones aditivas creadas:**  
No ha hecho falta crear migraciones: se reutilizan `activo`, `estado` y `f_baja` ya existentes.

**Validaciones ejecutadas:**

- `php -l routes/api.php`
- `php -l routes/web.php`
- `php -l app/Models/User.php`
- `php -l app/Http/Controllers/Api/TrabajoController.php`
- `php -l app/Http/Controllers/Api/PedidoController.php`
- `php -l app/Http/Controllers/Api/FacturaController.php`
- `php -l app/Http/Controllers/Api/EstacionController.php`
- `php -l app/Http/Controllers/Api/ClienteController.php`
- `php -l app/Http/Controllers/AuditLogController.php`
- `php -l tests/Feature/TrabajoTest.php`
- `php -l tests/Feature/PedidoTest.php`
- `php -l tests/Feature/FacturaTest.php`
- `php -l tests/Feature/Api/ClientesEstacionesApiTest.php`
- `php -l tests/Feature/AuditLogTest.php`
- `php artisan route:list`
- `php artisan test --filter=PermissionRoutesTest`
- `php artisan test --filter=ContextCreationGuardTest`
- `php artisan test --filter=TrabajoTest`
- `php artisan test --filter=PedidoTest`
- `php artisan test --filter=FacturaTest`
- `php artisan test --filter=EstacionesTest`
- `php artisan test --filter=ClientesEstacionesApiTest`
- `php artisan test --filter=AuditLogTest`
- `npm.cmd run build` falló dentro del sandbox por EPERM del binario nativo Tailwind/Rolldown y pasó fuera del sandbox.

**Riesgos residuales:**

- El gobierno mínimo posterior de Maestros cubre ciclo de vida de contratos, tarifarios, líneas y pivot contrato-sociedad; quedan pendientes catálogos auxiliares más ricos y datos reales definitivos.
- La limpieza profunda de datos demo críticos queda cerrada por P1-11; fuera de P1-01 queda solo legacy técnico/documental o módulos P2.
- Exportaciones CSV quedan cerradas por P1-07/P1-08; otras P1/P2 pendientes no cambian con este cierre.

### P1-04 - Pulir estaciones: código, nombre, municipio, provincia; dirección y baja como detalle secundario

**Estado:** cerrada  
**Área:** estaciones  
**Prioridad:** P1

**Qué se cerró:**

- El listado Ciete Excel de estaciones prioriza ya, en este orden, código, nombre, municipio, provincia, cliente/contexto y estado discreto.
- La vista moderna deja de comportarse como tabla genérica y muestra el código como dato principal, con nombre, municipio y provincia en primer nivel; dirección y fecha de baja quedan en segundo nivel.
- Se añadió filtro operativo por estado activa/desactivada y se consolidó la búsqueda por código, nombre, municipio, provincia y cliente/contexto.
- `EstacionResource` expone alias funcionales claros (`municipio`, `fecha_baja`, `contexto`) sin tocar la estructura de base de datos.
- La validación del código de estación queda alineada con CIETE: puede repetirse entre contextos distintos, pero no dentro del mismo cliente/contexto.
- Se confirma auditoría de creación, edición/cambio de estado y desactivación de estación.

**Archivos principales modificados:**

- `app/Models/EstacionServicio.php`
- `app/Http/Controllers/Api/EstacionController.php`
- `app/Http/Requests/Api/EstacionStoreRequest.php`
- `app/Http/Requests/Api/EstacionUpdateRequest.php`
- `app/Http/Resources/Api/EstacionResource.php`
- `resources/js/Pages/Estaciones/Index.jsx`
- `resources/js/Pages/Estaciones/Form.jsx`
- `resources/js/Components/ui/EstacionesExcelView.jsx`
- `resources/js/i18n/locales/es.js`
- `resources/js/i18n/locales/en.js`
- `tests/Feature/Api/ClientesEstacionesApiTest.php`

**Migraciones aditivas creadas:**  
No ha hecho falta crear migraciones: se reutilizan `poblacion`, `provincia`, `activo` y `f_baja`.

**Validaciones ejecutadas:**

- `php -l app/Models/EstacionServicio.php`
- `php -l app/Http/Controllers/Api/EstacionController.php`
- `php -l app/Http/Requests/Api/EstacionStoreRequest.php`
- `php -l app/Http/Requests/Api/EstacionUpdateRequest.php`
- `php -l app/Http/Resources/Api/EstacionResource.php`
- `php -l routes/web.php`
- `php -l routes/api.php`
- `php -l tests/Feature/Api/ClientesEstacionesApiTest.php`
- `php artisan route:list`
- `php artisan test --filter=EstacionesTest`
- `php artisan test --filter=ClientesEstacionesApiTest`
- `php artisan test --filter=ContextCreationGuardTest`
- `php artisan test --filter=PermissionRoutesTest`
- `npm.cmd run build` falló dentro del sandbox por EPERM del binario nativo Tailwind/Rolldown y pasó fuera del sandbox.

**Riesgos residuales:**

- La mejora cierra el módulo de estaciones en esta fase y queda integrada en la revisión responsive global ya cerrada en P1-10.
- La actualización automática de estaciones sigue fuera de alcance y permanece en P2.

| Tarea | Estado | Nota |
|---|---|---|
| Documentación estructural limpia | cerrado | La estructura documental viva ya está organizada. |
| Fuente de verdad funcional creada | cerrado | `DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`. |
| Historial de decisiones creado | cerrado | `HISTORIAL_DECISIONES_ERP_CIETE.md`. |
| Raíz de `docs/` limpia | cerrado | Solo documentos de entrada general y carpetas. |
| Contexto activo backend en sesión | cerrado/parcial | Base correcta; queda revisar métodos de creación contextuales. |
| Selector/indicador de contexto | cerrado | Usuario monocontexto con indicador; multicontexto con selector. |
| Auditoría visible para Dirección/admin | cerrado | Visible, filtrable y validada con export/limpieza operativas sin contaminar la vista por defecto con preferencias visuales nuevas. |
| Auditoría operativa filtrable | cerrado | El filtro por defecto muestra actividad operativa real; clientes y estaciones auditados en flujo vivo, logs legacy visuales fuera de la vista operativa. |
| Ciete Excel de trabajos | parcial avanzado | Tabla densa, columnas clave, edición por campo y nueva fila. |
| Observaciones en modal | cerrado | Implementado en Ciete Excel de trabajos. |
| Control optimista por campo en trabajos | cerrado/parcial | Implementado en trabajos; no extendido a todos los módulos. |
| Filtros de estaciones por municipio/provincia/código | cerrado | La API y las vistas Ciete Excel/Ciete Moderno ya buscan y filtran por código, municipio, provincia y estado operativo con cliente/contexto visible; validado con `ClientesEstacionesApiTest` y build. |
| P0-03 - `pedido_items` estables por ID | cerrado | `syncItems()` actualiza por `id_pedido_item`, crea nuevos y conserva facturados; archivos principales: `PedidoController`, requests/resource de pedidos, `Pedidos/Form.jsx`, `ItemsTable.jsx`; validado con `php -l`, `PedidoTest`, `route:list` y `npm.cmd run build`; sociedades permitidas quedan gobernables desde Maestros. |
| P0-01 - Facturación real por `factura_items` | cerrado | Facturas crean/actualizan `factura_items`, conservan IDs de líneas existentes, calculan asignado/diferencia/cuadre y bloquean sobre-facturación; archivos principales: `FacturaController`, requests/resource de facturas, `Pedido`, rutas web, `Facturas/Form.jsx`, `Facturas/Index.jsx`, `FacturasExcelView.jsx`, `FacturaTest`; validado con `php -l`, `FacturaTest`, `route:list` y `npm.cmd run build`; integrado con P0-02 para sociedad/CIF permitida. |
| P0-02 - Sociedad/CIF validada por contrato/tarifa | cerrado | Se endureció la validación por `contrato_empresas_facturadoras`: sociedad/CIF obligatoria en facturas por ítems, CIF informado, contexto correcto y pivot activa; frontend filtra sociedades/ítems compatibles; archivos principales: `FacturaController`, requests/resource de facturas, `routes/web.php`, `Facturas/Form.jsx`, `Facturas/Index.jsx`, `FacturasExcelView.jsx`, `DatabaseSeeder`, `FacturaTest`; validado con `php -l`, `migrate:status`, `route:list`, `FacturaTest` y `npm.cmd run build`; la pivot ya tiene gobierno mínimo desde Maestros y queda pendiente cargar datos reales de OTROS CLIENTES. |
| P0-04 - Bloqueo uniforme de creación desde TODOS | cerrado | `ContextGuard` bloquea altas desde TODOS y mantiene edición de existentes si el contexto real es accesible; cubre trabajos, pedidos, facturas, estaciones, clientes e importaciones; archivos principales: `ContextGuard`, middleware Inertia, `ActiveContextController`, requests de trabajos/facturas/importaciones, formularios y `ContextCreationGuardTest`; validado con `php -l`, `route:list`, `ContextCreationGuardTest`, `TrabajoTest`, `PedidoTest`, `FacturaTest` y `npm.cmd run build`; `EstacionesTest` queda bloqueado por Excel fuente ausente, no por contexto. |
| P0-05 - OTROS CLIENTES contexto real completo | cerrado | OTROS CLIENTES se presenta como contexto real; tras P1-11 el código demo queda normalizado a `OTROS`; puede crear cliente, estación, trabajo, pedido y factura si tiene contrato/sociedad permitida; las validaciones MOEVE/REPSOL no se aplican automáticamente a OTROS; archivos principales: `ContextGuard`, middleware Inertia, requests de trabajos/facturas, `ContextosClienteSeeder`, formularios y locales; validado con tests de contexto/factura/trabajo/pedido y build; riesgo pendiente: cargar datos maestros reales de OTROS. |
| P0-06 - Estados reales de trabajos y eliminación de legacy visible | cerrado | Altas de trabajos en `en_curso`, requests/controlador bloquean `borrador`/`cerrado` como estados nuevos, marcado `terminado` rellena `fecha_terminacion`, `ClosureDashboardService` finaliza con `finalizado` y legalizaciones informativas; tras P1-11 los enums/seeders/demo ya no conservan esos estados ni las columnas antiguas de cierre de trabajos; archivos principales: `TrabajoController`, requests/resource/modelo de trabajos, `ClosureDashboardService`, `Trabajos/Form.jsx`, `Trabajos/Index.jsx`, `TrabajosExcelView.jsx`, `Cierre/Dashboard.jsx`, locales y tests; validado con `php -l`, `route:list`, `TrabajoTest`, `ClosureDashboardTest`, `TrabajoRequestTest`, `ContextCreationGuardTest`, `PedidoTest`, `FacturaTest`, `EstacionesTest` y `npm.cmd run build`; riesgo pendiente: solo documentación histórica o módulos P2. |
| P0-07 - Permisos/rutas mutables y seeders | cerrado | Rutas web/API separadas por acción (`ver`, `crear`, `editar`, `eliminar/cancelar/anular`), permisos faltantes sembrados, roles funcionales alineados y hard delete mitigado en trabajos, pedidos, facturas, estaciones y clientes; tras P1-11 se eliminan alias legacy vivos en `User::hasPermission()`; archivos principales: `routes/api.php`, `routes/web.php`, `User`, `PermisosSeeder`, `RolPermisosSeeder`, controladores API, sidebar/listados Excel y `PermissionRoutesTest`; validado con `php -l`, `route:list`, tests de permisos/contexto/trabajos/pedidos/facturas/estaciones/cierre/clientes/auditoría y `npm.cmd run build`; riesgo pendiente: CRUD/gobierno de roles-permisos reales. |
| Estabilización de validaciones recurrentes | cerrado | Se corrigió la ruta del Excel `Contrato 772 MOEVE - Tarifario.xlsx` desde la raíz antigua de `docs/` a `docs/02_CLIENTE/materiales/`; se alineó `EstacionesTest` con los permisos sembrados (`ejecucion_moeve` puede ver estaciones pero no gestionarlas) y con el usuario real `contable@ciete.es`; se validó con `php -l`, `route:list`, `EstacionesTest`, `ContextCreationGuardTest`, `TrabajoTest`, `PedidoTest`, `FacturaTest` y `npm.cmd run build` fuera del sandbox tras EPERM; archivos principales: `DemoCieteOperativaSeeder`, `ContratosBaseSeeder`, `tests/Feature/EstacionesTest.php`, `docs/02_CLIENTE/materiales/README.md`; no sube porcentaje porque estabiliza entorno/tests sin añadir funcionalidad ERP. |
| `factura_items` creado estructuralmente | cerrado | Estructura y flujo funcional cerrados por P0-01; validación fiscal cerrada por P0-02. |
| `interface_mode` creado estructuralmente | cerrado | Existe como preferencia UX, pero ya no genera eventos nuevos de Auditoría operativa; los logs legacy visuales quedan ocultos por defecto. |

## 9. Tareas descartadas o fuera de alcance

| Tarea | Decisión |
|---|---|
| Cobros en fase actual | Fuera de alcance. El ERP CIETE termina en factura. |
| Presupuestos en fase actual | Fuera de alcance inmediato. |
| Legalizaciones completas en fase actual | No bloquear flujo crítico. |
| Importación Excel avanzada antes de cerrar facturación | La carga real controlada queda cerrada por P1-12; queda como P2 la automatización avanzada/UI de avisos. |
| Actualización automática de estaciones | P2. |
| Borrado físico de estaciones | Descartado por histórico. |
| Reapertura normal de trabajos finalizados | Descartada como flujo normal; si aparece algo nuevo, se crea otro trabajo. |

## 10. Checklist final de aceptación

- [x] Base de datos sin contradicciones funcionales críticas.
- [x] Facturas creadas por `factura_items`.
- [x] Sociedad/CIF validada por contrato/tarifa.
- [x] `pedido_items` no se borran/recrean si tienen facturación.
- [x] TODOS no crea registros.
- [x] TODOS edita registros existentes si hay permisos.
- [x] OTROS CLIENTES opera como contexto real.
- [x] Estados de trabajos alineados con CIETE.
- [x] Sin `borrador` visible en UI viva.
- [x] Sin `cerrado` como flujo principal.
- [x] No hard delete en estaciones.
- [x] Estaciones priorizan código, nombre, municipio y provincia en el listado vivo.
- [x] Entidades con histórico usan cancelar/anular/desactivar en el flujo vivo.
- [x] Permisos por acción revisados.
- [x] Auditoría útil para Dirección.
- [x] Facturas exportables en listado.
- [x] Factura individual exportable con detalle.
- [x] Consultas críticas de facturación/cierre revisadas sin N+1 evidente.
- [x] Importación real controlada MOEVE/REPSOL validada en local/demo.
- [x] Ciete Excel usable.
- [x] Ciete Moderno limpio.
- [x] Documentación viva alineada.
- [x] Legacy documentado y no usado para lógica nueva.
