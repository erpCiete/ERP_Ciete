# Tareas restantes ERP CIETE

> **Documento vivo.**  
> Este documento es el backlog maestro vigente de tareas restantes del ERP CIETE.  
> Fuente funcional principal: `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`  
> Traducción operativa vigente: `docs/02_CLIENTE/AUDITORIA_ALINEACION_FUNCIONAL_ERP_CIETE_2026-05-17.md`  
> Síntesis interpretativa secundaria: `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`  
> Última auditoría: **2026-05-17**, auditoría de alineación funcional basada en reunión/transcripción; avance operativo estimado mantenido en **86,2%** hasta cerrar la primera ola correctiva.  
> **2026-05-19:** Creada documentación de apoyo para presentación práctica v2.1.0. Tres documentos en `docs/02_CLIENTE/`: tutorial práctico, guion de demostración y resumen ejecutivo imprimible.  
> Responsable funcional: Pablo Sevillano.  
> Uso del documento: backlog maestro de fase actual.  
> No usar como contrato técnico detallado ni como memoria histórica.

Este documento es el único listado vivo de tareas pendientes. No sustituye a la Biblia de desarrollo, al contrato API ni al historial de decisiones: los resume solo cuando hace falta para ordenar trabajo real.

## 0. Regla de actualización

Este documento debe actualizarse al cerrar cada P0/P1 relevante.

Cada tarea cerrada debe moverse a la sección "Tareas cerradas" con una nota breve de qué se hizo y qué archivos principales se tocaron.

No se deben añadir tareas nuevas sin indicar prioridad, área, criterio de aceptación y motivo.

## 0a. Orden de autoridad funcional vigente

1. `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
2. `docs/02_CLIENTE/AUDITORIA_ALINEACION_FUNCIONAL_ERP_CIETE_2026-05-17.md`
3. `docs/02_CLIENTE/tareasComparar.md`
4. `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md` como síntesis interpretativa secundaria.
5. Notas históricas de reuniones anteriores con CIETE.
6. Excel reales de MOEVE/REPSOL.
7. Base de datos actual y código existente.
8. Propuestas técnicas previas.

Si una interpretación técnica entra en conflicto con la reunión/transcripción, prevalece la reunión salvo que quede documentado como duda pendiente de decisión.

## 0b. Auditoría de alineación funcional 2026-05-17

Documento asociado:

- `docs/02_CLIENTE/AUDITORIA_ALINEACION_FUNCIONAL_ERP_CIETE_2026-05-17.md`

Resultado ejecutivo:

- El modelo vivo sigue siendo compatible con gran parte de lo pedido por CIETE; el eje funcional correcto sigue siendo trabajo -> pedidos/item -> facturas.
- El principal desfase no es rehacer estructura por defecto, sino cerrar huecos entre reunión y operativa real en cuatro frentes: gobierno de maestros, validaciones compartidas, flujo UI en cascada y reconciliación/importación segura.
- El síntoma visible actual más representativo es la lista vacía de Sociedad/CIF: el modelo ya soporta la regla funcional, pero cuando falta maestro o pivot la UI no siempre explica causa y corrección.
- La primera ola priorizada queda fijada en: auditoría de completitud de maestros por contexto, diagnósticos accionables en operativa y cascadas funcionales en trabajos/pedidos/tarifarios/facturas.

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
- Extender a trabajos, pedidos y tarifarios el patrón de diagnóstico funcional cuando falte maestro o haya combinación inválida de contexto/contrato/tarifa.
- Aplicar de forma controlada la matriz A.3 de `admin técnico` sobre la base local actual/post-importación, resincronizando `roles/permisos/rol_permisos/usuario_roles` sin tocar datos operativos y reauditando `admin@ciete.es`, `cesar@ciete.es` y `contable@ciete.es`.

## 2.2 Primera ola priorizada tras la auditoría 2026-05-17

Orden recomendado:

1. **P1-13 — Alineación funcional reunión -> ERP -> backlog.** Cerrada con matriz y backlog actualizado.
2. **P1-14 — Auditoría de completitud de maestros por contexto.** Cerrada sobre muestra local/demo; repetición real bloqueada por fuente Excel no disponible.
3. **P1-15 — Diagnóstico accionable de maestro faltante y listas vacías en operativa.** En curso con primer bloque funcional implementado sobre testing/muestra controlada.
4. **P1-16 — Cascadas funcionales en trabajos, pedidos y tarifarios.** En curso con primer bloque de cascadas implementado sobre testing/muestra controlada.

Motivo: antes de endurecer más reglas o tocar BBDD, hay que garantizar que el ERP explica al usuario por qué no puede operar, que los maestros realmente existen por contexto y que los formularios no permiten combinaciones que contradigan el flujo hablado con CIETE. La repetición P1-14 sobre base real post-importación sigue pendiente por falta de Excel en la ruta esperada, pero no bloquea el primer bloque práctico P1-15/P1-16 orientado al flujo diario sobre muestra controlada.

## 2. Porcentaje de avance actual

| Área                         | Peso | Avance interno | Ponderado |
| ---------------------------- | ---: | -------------: | --------: |
| BD/modelo negocio            |  20% |            83% |      16,6 |
| Contextos/roles/permisos     |  15% |            93% |      14,0 |
| Trabajos/estaciones          |  15% |            88% |      13,2 |
| Pedidos/ítems                |  10% |            80% |       8,0 |
| Facturación                  |  15% |            85% |      12,8 |
| Ciete Excel/Moderno          |  10% |            82% |       8,2 |
| Auditoría                    |   5% |            90% |       4,5 |
| Documentación                |   5% |            87% |       4,4 |
| Rendimiento/seguridad/legacy |   5% |            90% |       4,5 |

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

- `database/migrations/2026_05_05_000130_create_contrato_empresas_facturadoras.php` _(nuevo)_
- `app/Models/ContratoEmpresaFacturadora.php` _(nuevo)_
- `database/seeders/ContratoEmpresasFacturadorasSeeder.php` _(nuevo)_
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

### P1-13 - Alineación funcional integral basada en reunión/transcripción

**Estado:** cerrada con auditoría y matriz  
**Área:** documentación viva / backlog / gobierno funcional  
**Prioridad:** P1

**Motivo:**

La documentación viva seguía sobredimensionando el papel de la síntesis de decisiones anterior. Desde la auditoría 2026-05-17 se fija que la reunión/transcripción es la autoridad funcional principal y que cualquier síntesis documental queda subordinada como interpretación operativa secundaria.

**Qué se hizo:**

- Crear matriz de alineación funcional por módulo en `docs/02_CLIENTE/AUDITORIA_ALINEACION_FUNCIONAL_ERP_CIETE_2026-05-17.md`.
- Actualizar el backlog maestro con el nuevo orden de autoridad funcional.
- Alinear `docs/README.md`, `docs/BIBLIA_DESARROLLO.md`, `docs/02_CLIENTE/README.md` y la cabecera de `DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md` para que no contradigan la reunión.

**Criterio de aceptación:**

- La reunión/transcripción queda declarada como fuente funcional principal en la documentación viva y backlog.
- Existe una matriz reunión -> ERP actual -> gaps -> primera corrección por módulo.
- El backlog maestro refleja la primera ola derivada de esa auditoría.

### P1-14 - Auditoría de completitud de maestros por contexto

**Estado:** cerrada sobre muestra local/demo; repetición sobre base post-importación real bloqueada por dry-run sin Excel  
**Área:** maestros / contextos / contratos / sociedades / estaciones / OTROS CLIENTES  
**Prioridad:** P1

**Motivo:**

Antes de endurecer formularios y validaciones hay que saber exactamente qué falta en el dato maestro real por contexto: contratos activos sin sociedad/CIF permitida, empresas activas sin CIF, contextos sin tarifarios operativos, estaciones incompletas, tipos insuficientes y viabilidad real de OTROS CLIENTES.

**Criterio de aceptación:**

- Existe un gap report por contexto con conteos y ejemplos de contratos activos sin sociedad permitida.
- Se listan empresas activas sin CIF y maestros críticos incompletos.
- Se identifica si el bloqueo está en dato maestro, validación, permisos, flujo UI o importación.
- OTROS CLIENTES queda clasificado como listo, incompleto o bloqueado por dato real.

**Evidencia especifica 2026-05-17:**

- Informe emitido en `docs/02_CLIENTE/AUDITORIA_COMPLETITUD_MAESTROS_P1-14_2026-05-17.md`.
- Resultado real de la muestra local `abaco_ciete`:
    - MOEVE: 2/2 contratos activos bloqueados para facturar por sociedades activas sin CIF valido.
    - REPSOL: 1/1 contrato activo bloqueado por ausencia de pivot contrato-sociedad facturadora.
    - OTROS CLIENTES: clasificado como listo en dato maestro base para `Trabajo -> Pedido -> Factura`.
- Contratos, tarifarios, lineas, unidades, estaciones y tipos quedan completos en la muestra auditada; el gap dominante es `contrato_empresas_facturadoras + CIF`.
- Validaciones ejecutadas: consultas read-only via bootstrap Laravel, revisión de `routes/web.php`, `FacturaController`, `StoreTrabajoRequest`, `StorePedidoRequest`, `StoreFacturaRequest` y comprobación de drift `trabajos.activo` en la BD local.

**Reconciliacion P1-12 vs P1-14 2026-05-18:**

- Comprobacion solo lectura sobre `APP_ENV=local`, `DB_CONNECTION=mysql`, `DB_DATABASE=abaco_ciete`: 3 contextos, 50 trabajos, 40 pedidos, 40 `pedido_items`, 18 facturas, 20 `factura_items` y 0 importaciones registradas.
- El desglose actual por contexto coincide con la muestra de P1-14: MOEVE 20 trabajos/17 pedidos/17 `pedido_items`, REPSOL 20/17/17 y OTROS CLIENTES 10/6/6.
- Estos datos no coinciden con P1-12 post-importación real: 11.614 trabajos, 9.685 pedidos, 9.701 `pedido_items`, 2.733 facturas, 9.194 `factura_items` y 11 importaciones registradas.
- Conclusión: P1-14 auditó una base local/demo reducida, no la base post-importación real. Su diagnóstico funcional se conserva como válido para esa muestra, pero sus números no deben usarse como verdad final de negocio hasta repetir la auditoría sobre la base post-importación real.

**Intento de repeticion real 2026-05-18:**

- Informe de bloqueo emitido en `docs/02_CLIENTE/AUDITORIA_COMPLETITUD_MAESTROS_P1-14_REAL_POST_IMPORTACION_2026-05-18.md`.
- Entorno validado antes del reset: `APP_ENV=local`, `DB_CONNECTION=mysql`, `DB_DATABASE=abaco_ciete`; backup local creado en `database/backups/abaco_ciete_demo_reducida_pre_p1_14_real_20260518_002707.sql`.
- Se ejecutó `migrate:fresh --seed`; el dry-run de `ciete:import-excels-actualizados --path=excelsactualizados --dry-run` devolvió `filas_con_error=1` y `No se encontraron Excel .xlsx para importar`.
- No se ejecutó `--commit`; la base local final queda sembrada pero no post-importación real: 3 contextos, 0 trabajos, 0 pedidos, 0 `pedido_items`, 0 facturas, 0 `factura_items` y 0 importaciones.
- P1-14 real post-importación queda pendiente hasta restaurar/ubicar los Excel reales o cargar una base local que coincida con P1-12. No bloquea el trabajo práctico P1-15/P1-16 sobre muestra controlada orientado al flujo diario. No se sube porcentaje global.

### P1-15 - Diagnostico accionable de maestro faltante y listas vacias en operativa

**Estado:** en curso con primer bloque funcional implementado  
**Área:** facturas / maestros / UX operativa  
**Prioridad:** P1

**Motivo:**

CIETE necesita que un listado vacio explique que maestro falta y como corregirlo. El caso visible actual es Sociedad/CIF en facturas, pero el mismo patron debe cubrir trabajos, pedidos y tarifarios cuando falten estaciones, trabajos, contratos o combinaciones permitidas.

**Qué ya esta arrancado:**

- Diagnostico visible en `Maestros/Index` para contratos activos sin sociedad facturadora valida y empresas activas sin CIF.
- Mensajes accionables en facturas moderno/Excel cuando no hay sociedades disponibles para el contrato/contexto seleccionado.
- Primer bloque practico: diagnosticos accionables en trabajos cuando faltan estaciones/contratos/tipos, diagnosticos en pedidos cuando no hay trabajos o lineas de tarifa, mensaje de items/sociedad en facturas y avisos de contratos en tarifarios.
- Subtarea vinculada (P1-15A/P1-16A): correccion de navegacion inicial y cierre de acceso por rol (todos entran por Inicio comun, `contable` sin acceso a `Trabajos`, bloqueo por menu+ruta+test y paridad Moderno/Excel).
- Ampliación transversal de acceso (P1-15B): validación de permisos y navegación extendida desde Trabajos a todas las pantallas ERP (admin, dirección, maestros, auditoría, soporte, mensajes, clientes, estaciones, pedidos, facturas, importaciones), con matriz global por pantalla y salida funcional de 403 con botón a Inicio.

**Ejecución local 2026-05-18 (flujo diario controlado):**

- Backup previo obligatorio creado: `database/backups/abaco_ciete_pre_muestra_flujo_diario_20260518_092239.sql`.
- Muestra local cargada desde `database/manual/2026_05_18_insert_muestra_flujo_diario_controlado.sql`.
- Cobertura cargada en `abaco_ciete`: 14 trabajos, 9 pedidos, 10 `pedido_items`, 4 facturas, 4 `factura_items`.
- Casos controlados activos: MOEVE correcto y con contrato sin sociedad/CIF valida; REPSOL correcto y con contrato/sociedad no valida; OTROS correcto; terminado sin factura; pedido sin factura; factura parcial (`P-FD26-REP-001` total 2000 vs facturado 1200).

**Archivos tocados en el primer bloque:**

- `resources/js/Pages/Trabajos/Form.jsx`
- `resources/js/Pages/Pedidos/Form.jsx`
- `resources/js/Pages/Facturas/Form.jsx`
- `resources/js/Pages/Tarifarios/Form.jsx`
- `resources/js/Components/ui/ItemsTable.jsx`
- `app/Http/Controllers/MaestroController.php`
- `app/Http/Controllers/TarifarioController.php`
- `app/Http/Requests/Api/StorePedidoRequest.php`
- `app/Http/Requests/Api/UpdatePedidoRequest.php`
- `routes/web.php`

**Validaciones ejecutadas en el primer bloque:**

- `php -l app/Http/Controllers/MaestroController.php`
- `php -l app/Http/Controllers/TarifarioController.php`
- `php -l app/Http/Requests/Api/StorePedidoRequest.php`
- `php -l app/Http/Requests/Api/UpdatePedidoRequest.php`
- `php artisan route:list`
- `php artisan test --filter=TrabajoTest`
- `php artisan test --filter=PedidoTest`
- `php artisan test --filter=FacturaTest`
- `php artisan test --filter=MaestrosTest`
- `npm run build`

**Nota de validación:**

- `route:list` y `npm run build` en verde.
- `php -l` en verde para `MaestroController`, `TarifarioController`, `StorePedidoRequest`, `UpdatePedidoRequest`.
- Tests filtrados ejecutados con PHP XAMPP (`TrabajoTest`, `PedidoTest`, `FacturaTest`, `MaestrosTest`) con fallo técnico de entorno de testing en `abaco_ciete_testing` (tablas de migración/constraints en estado inconsistente), no atribuible a un error funcional puntual del bloque cargado en `abaco_ciete`.
- Checklist manual diaria pendiente de validación en UI con Cesar sobre la muestra local cargada.
- No se sube porcentaje global en este punto.

**Criterio de aceptación:**

- Un usuario nunca se queda con un selector vacio sin explicacion funcional.
- La pantalla indica si falta pivot contrato-sociedad, CIF, contexto o maestro activo.
- Existe acceso directo al módulo maestro que corrige el problema cuando el usuario tenga permiso.
- El patron se replica al menos en trabajos, pedidos y tarifarios si el bloqueo es equivalente.

### P1-16 - Cascadas funcionales en trabajos, pedidos y tarifarios

**Estado:** en curso con primer bloque de cascadas implementado  
**Área:** trabajos / pedidos / tarifarios / validación frontend-backend  
**Prioridad:** P1

**Motivo:**

La reunion deja claro que el eje del ERP es el trabajo y que pedido/factura pueden llegar en momentos distintos, pero siempre dentro de contexto, contrato/tarifa y estaciones bien separadas. Hoy todavia existen formularios que permiten combinaciones poco guiadas o insuficientemente explicadas.

**Criterio de aceptación:**

- Trabajos obliga y limpia correctamente `Contexto -> Estacion/Contrato/Tipos`.
- Pedidos obliga y limpia correctamente `Contexto -> Trabajo -> lineas/tarifa`.
- Tarifarios obliga y limpia correctamente `Contexto -> Contrato -> Tarifario`.
- Cambiar un nivel superior limpia los inferiores incompatibles y lo explica.
- El backend rechaza igualmente combinaciones cruzadas aunque fallen los filtros de UI.

**Primer bloque implementado:**

- Trabajos explica la falta de estaciones, contratos MOEVE y catalogos REPSOL antes de bloquear al usuario.
- Pedidos filtra lineas de tarifa por contexto/trabajo/contrato o tarifario y limpia items al cambiar contexto o trabajo.
- Items de pedido seleccionan línea tarifaria y rellenan código, descripción y precio.
- Facturas filtra items por trabajo seleccionado y limpia items/sociedad al cambiar trabajo.
- Tarifarios muestra contexto activo y diagnostica ausencia de contratos activos.
- Backend de pedidos rechaza lineas tarifarias de otro contexto, contrato o tarifario.
- Backend de pedidos ahora exige `id_tarifario_linea` cuando existen lineas aplicables para el trabajo; la entrada manual queda solo para casos sin lineas disponibles.
  `ItemsTable` bloquea edición manual de descripción/precio cuando hay líneas tarifarias operativas y guía a seleccionar línea.

**Validación manual pendiente:**

- Trabajos MOEVE: crear/editar, avisos de contrato/estacion y rechazo de combinaciones absurdas.
- Trabajos REPSOL: tipos documento/tipos trabajo y aviso claro si falta catalogo.
- Pedidos: elegir trabajo, comprobar filtro de líneas, cambiar trabajo y verificar limpieza de items, seleccionar tarifa y validar código/descripción/precio.
- Facturas: elegir trabajo, comprobar filtro de items, cambiar trabajo y verificar limpieza de items/sociedad, revisar mensaje de sociedad/CIF.
- Tarifarios: revisar contexto activo y aviso sin contratos activos.
- Maestros: revisar diagnosticos de estaciones, contratos, tarifas/lineas y catalogos.

**Seguimiento de cierre del bloque (2026-05-18):**

- Estado: en curso / parcialmente cerrado en local para P1-15 y P1-16.
- Muestra local de flujo diario cargada y util para pruebas operativas.
- Validación automática parcial (lint/rutas/build ok; tests bloqueados por entorno `abaco_ciete_testing`).
- Checklist funcional manual aun pendiente de cierre final en UI.

**Nota de prioridad vigente:**

La prioridad actual es flujo diario funcional en local. Exportaciones, XLSX/PDF, dashboards e importación masiva quedan fuera de esta fase inmediata.

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

| ID    | Tarea                                     | Motivo                                                                                                                                                     |
| ----- | ----------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- |
| P2-01 | Importación Excel avanzada / UI de avisos | La carga real MOEVE/REPSOL queda validada por P1-12; queda automatizar detalle de errores, reintentos parciales y decisiones funcionales por columna/fila. |
| P2-02 | Legalizaciones completas                  | Módulo posterior; no debe bloquear cierre operativo actual.                                                                                                |
| P2-03 | Presupuestos/hoja de pedido               | Fuera de alcance inmediato confirmado.                                                                                                                     |
| P2-04 | Cobros                                    | Otro departamento; el ERP CIETE termina en factura.                                                                                                        |
| P2-05 | Actualización automática de estaciones    | Riesgo operativo alto; primero mantener maestro manual estable.                                                                                            |
| P2-06 | Costes/imputación avanzada                | No pertenece al flujo principal actual.                                                                                                                    |
| P2-07 | Limpieza profunda de legacy no crítico    | Solo para módulos no activos, documentación histórica o snapshots SQL antiguos que no alimentan el flujo vivo.                                             |

## 6. P3 - Futuro o mejoras

| ID    | Mejora                            | Motivo                                           |
| ----- | --------------------------------- | ------------------------------------------------ |
| P3-01 | Dashboards ejecutivos avanzados   | Explotación posterior de datos ya consolidados.  |
| P3-02 | Estadísticas históricas           | Valor analítico cuando haya datos estables.      |
| P3-03 | Automatizaciones                  | Reducir trabajo manual tras cerrar flujo base.   |
| P3-04 | Integraciones externas            | Fase futura, no necesaria para demo.             |
| P3-05 | Virtualización avanzada de tablas | Solo si los listados crecen mucho.               |
| P3-06 | Exportaciones complejas           | Posterior al listado plano y detalle individual. |

## 7. Legacy identificado

| Legacy                                                       | Qué es                                                         | Qué hacemos ahora                                                                                                                                | Qué haremos después                                                       |
| ------------------------------------------------------------ | -------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------- |
| `facturas.id_trabajo`                                        | Cabecera antigua factura-trabajo                               | Mantener solo como campo derivado/auxiliar para filtros y ordenación; no es relación funcional ni fallback de exportación                        | Retirar cuando las vistas/importaciones ya no necesiten cabecera derivada |
| `factura_pedidos`                                            | Pivote antiguo factura-pedido                                  | Eliminado del esquema demo y del flujo vivo por P1-11                                                                                            | No reintroducir; usar siempre `factura_items`                             |
| `borrador`                                                   | Estado antiguo                                                 | Eliminado de trabajos/pedidos vivos, seeders y UI; solo queda en migraciones correctivas y tests negativos que lo rechazan                       | No reintroducir                                                           |
| `cerrado`                                                    | Cierre antiguo                                                 | Eliminado como estado vivo de trabajos/pedidos y de exportaciones; solo queda en migraciones correctivas, tests negativos y textos históricos/P2 | No reintroducir; usar `finalizado`                                        |
| `trabajos.cerrado`, `fecha_cierre`, `id_usuario_cierre`      | Cierre antiguo de trabajos                                     | Columnas retiradas del esquema demo y de la lógica viva por P1-11                                                                                | No reintroducir salvo migración histórica expresamente justificada        |
| Estados factura tipo `cobrada`, `vencida`, `cobrada_parcial` | Cobro fuera de alcance                                         | No exponer                                                                                                                                       | Revisar si futuro incluye cobros                                          |
| `cobros`                                                     | Módulo posterior                                               | Ocultar flujo crítico                                                                                                                            | Fase futura                                                               |
| `presupuestos`                                               | Módulo posterior con columnas/estados propios todavía antiguos | Fuera alcance inmediato; no forma parte del flujo vivo cerrado por P1-11                                                                         | Fase futura/P2 antes de activar presupuestos                              |
| `legalizaciones`                                             | Módulo posterior                                               | No bloquear demo                                                                                                                                 | Fase futura                                                               |
| `CEPSA`                                                      | Nombre histórico                                               | Solo queda como referencia explicativa/documental; demo y flujo vivo usan MOEVE                                                                  | Mapear si aparece en Excel real de entrada                                |
| `OTRO`                                                       | Código interno antiguo de OTROS CLIENTES                       | Normalizado a `OTROS` en seeders/factories demo; `ContextGuard` sigue resolviendo por nombre/código de forma robusta                             | Mantener `OTROS CLIENTES` como nombre visible                             |
| `obras/proyectos`                                            | Nombres antiguos                                               | Mantener históricos                                                                                                                              | Usar trabajos en vivo                                                     |

## 8. Tareas cerradas

### /ayuda — Segmentación en 3 categorías funcionales (Admin / Dirección-Cierre / Resto)

**Estado:** cerrada
**Área:** frontend / UX / visibilidad por perfil
**Fecha:** 2026-05-19

**Qué se hizo:**

- Eliminado el modelo de 5 categorías de audiencia (`admin`, `direccion`, `ejecucion`, `contable`, `todos`) y sustituido por 3 categorías funcionales claras: `HELP_CAT_ADMIN = 'admin'`, `HELP_CAT_DIRECCION = 'direccion'`, `HELP_CAT_RESTO = 'resto'`.
- Añadida función `getHelpCategory(user)` que normaliza `auth.user` a una única categoría: Admin si `can_access_admin_panel` o slug `admin`; Dirección si `is_director`, `can_access_direction_panel` o slugs `director`/`direccion`; Resto en cualquier otro caso (ejecución, contable, etc.).
- `SECTION_AUDIENCE` actualizado: valores `'ejecucion'` y `'contable'` reemplazados por `'resto'`; `audit` corregida a `['admin','direccion']` (eliminado `'contable'` que no estaba validado funcionalmente).
- Lógica de `visibleSections` simplificada: Admin hace cortocircuito devolviendo todas las secciones; el resto filtra por `allowed.includes(category)`.
- Fila de 4 badges independientes eliminada y sustituida por un banner de categoría: un único `RoleBadge` + texto explicativo del alcance del manual, situado entre el bloque de título y el buscador.
- Sección `roles` ES reescrita: elimina "El ERP tiene seis roles" y los bullet points con "Ejección" (typo); introduce 3 subsecciones con prop `body` para Admin, Dirección/Cierre y Resto de usuarios.
- Sección `roles` EN actualizada con la misma estructura de 3 categorías (Admin, Direction/Closure, General users).
- Build `npm run build` validado sin errores (2.04s).

**Archivos tocados:**

- `resources/js/Pages/Help.jsx`

---

### /ayuda — Segmentación por roles reales del ERP

**Estado:** cerrada
**Área:** frontend / UX / visibilidad por rol
**Fecha:** 2026-05-19

**Qué se hizo:**

- Inspección completa de `auth.user` en `HandleInertiaRequests`: confirmados flags `can_access_admin_panel`, `is_director`, `can_access_direction_panel`, `is_execution`, `is_execution_moeve`, `is_execution_repsol`, `is_accounting`, `can_view_audit`, `can_manage_imports`, `can_access_closure`.
- Revisión de `sidebar.js` para confirmar qué pantallas ve realmente cada rol (ejecución sí ve facturas; maestros solo dirección; cierre solo dirección; importaciones solo admin).
- Añadido `SECTION_AUDIENCE` — mapa de visibilidad por sección (keyed por `section.id`) con categorías: `'admin'`, `'direccion'`, `'ejecucion'`, `'contable'`, `'todos'`.
- Secciones restringidas: `maestros` → `['admin','direccion']`; `closure` → `['admin','direccion']`; `admin` → `['admin']`; `audit` → `['admin','direccion','contable']`; `imports` → `['admin']`. El resto: `todos`.
- Reescrito `export default function Help()`: se calcula `userAudiences` desde los flags reales de `auth.user`. Admin recibe todas las categorías. No-admin recibe solo las que corresponden a su rol.
- Añadido paso `visibleSections` antes de la búsqueda. El buscador opera exclusivamente sobre secciones visibles al usuario — no quedan anclas ni resultados de búsqueda sobre contenido no permitido.
- Badges de rol actualizados: Admin, Dirección, Ejecución, Contabilidad (no más badge genérico "Usuario" para roles reconocidos).
- Build `npm run build` ejecutado y validado sin errores (1.78s, 2991 módulos).

**Archivos tocados:**

- `resources/js/Pages/Help.jsx`

---

### /ayuda — Reconstrucción completa de la página de ayuda

**Estado:** cerrada
**Área:** frontend / documentación operativa / UX
**Fecha:** 2026-05-28

**Qué se hizo:**

- Auditoría completa de módulos reales confirmados en código (Trabajos, Pedidos, Facturas, Tarifarios, Contratos, Maestros, Cierre, Mensajes, Soporte, Admin, Auditoría, Importaciones, Estaciones, Clientes, SociedadesFacturadoras).
- Nueva sección `daily_flow` (flujo diario recomendado) añadida en ES y EN.
- Nueva sección `tarifarios` (tarifarios y contratos, cascada contrato→tarifario→líneas) en ES y EN.
- Nueva sección `maestros` (diagnóstico funcional, panel de alertas, antes de crear) en ES.
- Nueva sección `states_diagnostics` (estados de trabajos, facturas, tickets de soporte; diagnóstico de Maestros; cascada tarifaria; limpieza automática en facturas) en ES y EN.
- Sección `errors` renombrada a `best_practices` con contenido actualizado en ES y EN.
- Sección `roles` actualizada con los 6 roles reales: Administrador, Ejecución, Ejecución MOEVE, Ejecución REPSOL, Dirección, Contabilidad.
- Sección `works` actualizada con los 6 estados reales del trabajo.
- Sección `orders` actualizada: subsección tarifario y cascada explicando filteredTarifarioLineas.
- Sección `invoices` actualizada: warnings sobre cambio de trabajo (handleTrabajoChange).
- Sección `support` actualizada: estados de ticket, cuándo usar soporte.
- Sección `faq` reescrita con 9 preguntas prácticas por módulo y rol en ES; 8 en EN.
- Build `npm run build` ejecutado y validado sin errores.

**Archivos tocados:**

- `resources/js/Pages/Help.jsx`

---

### Fase A.3 - Reajuste del administrador técnico y separación de responsabilidades

**Estado:** cerrada en código, seeders, UX y tests; pendiente solo resincronización controlada de datos persistidos locales
**Área:** permisos / rutas / navegación / administración técnica

**Qué se cerró:**

- `admin` deja de comportarse como superusuario funcional y pasa a ser un administrador técnico.
- Se crean/usan capacidades técnicas explícitas para panel admin, soporte, auditoría, mantenimiento, avisos e importaciones.
- `dashboard` y `cierre` quedan exclusivos de dirección.
- La navegacion deja de inferir privilegios desde `is_admin` y pasa a flags compartidos de Inertia.
- El panel admin se reorienta a soporte técnico: usuarios, soporte, auditoría, mantenimiento, avisos, importaciones y estado del sistema.
- La sidebar del admin técnico queda limpia y deja de mezclar dirección, datos operativos y acciones que no le corresponden.
- La lectura operativa principal pasa a modo solo lectura visible: se ocultan acciones de editar/cancelar cuando no existe permiso efectivo y se muestra aviso de soporte técnico.
- `Estado del sistema` se rehace en doble capa: diagnóstico técnico rico para admin y resumen útil no sensible para el resto.
- Se separa visualmente `Auditoría técnica` de `Registro de actividad operativa` en navegación y encabezados.
- El runtime blinda al rol `admin` frente a slugs legacy de mutacion operativa y le inyecta las capacidades tecnicas efectivas aunque la base local antigua aun no este resincronizada.
- Se añade cobertura automática específica para frontera admin técnico, mutación operativa, mantenimiento, avisos, importaciones y paridad Excel/Moderno.
- Se corrige un defecto real en importaciones para MariaDB: ya no se reutiliza una query paginada dentro de un `IN` subquery.

**Archivos principales tocados:**

- `database/seeders/RolesSeeder.php`
- `database/seeders/PermisosSeeder.php`
- `database/seeders/RolPermisosSeeder.php`
- `app/Models/User.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Http/Middleware/CheckMaintenanceMode.php`
- `app/Http/Middleware/AuditAccessMiddleware.php`
- `app/Http/Controllers/Admin/DashboardController.php`
- `app/Http/Controllers/Admin/UserController.php`
- `app/Http/Controllers/MaintenanceController.php`
- `app/Http/Controllers/MessageController.php`
- `app/Http/Controllers/StatusController.php`
- `app/Http/Controllers/ImportacionController.php`
- `routes/web.php`
- `resources/js/Components/OperationalReadOnlyNotice.jsx`
- `resources/js/navigation/sidebar.js`
- `resources/js/Pages/Admin/Dashboard.jsx`
- `resources/js/Pages/Admin/Users/Index.jsx`
- `resources/js/Pages/Admin/Users/Form.jsx`
- `resources/js/Pages/AuditLog/Index.jsx`
- `resources/js/Pages/Dashboard.jsx`
- `resources/js/Pages/Messages/Index.jsx`
- `resources/js/Pages/Status.jsx`
- `resources/js/Pages/Trabajos/Index.jsx`
- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `resources/js/Pages/Pedidos/Index.jsx`
- `resources/js/Pages/Facturas/Index.jsx`
- `resources/js/Pages/Clientes/Index.jsx`
- `resources/js/Pages/Estaciones/Index.jsx`
- `tests/Feature/AdminAccessTest.php`
- `tests/Feature/AdminDashboardTest.php`
- `tests/Feature/AdminTechnicalMutationTest.php`
- `tests/Feature/MaintenanceModeTest.php`
- `tests/Feature/InternalCommunicationTest.php`
- `tests/Feature/ImportacionesAccessTest.php`
- `tests/Feature/ExcelModeAccessTest.php`
- `tests/Feature/RoleModuleAccessTest.php`
- `docs/02_CLIENTE/MATRIZ_ADMIN_TECNICO_ERP_CIETE_2026-05-18.md`

**Validaciones ejecutadas:**

- `npm run build`
- `php artisan test --filter="AdminAccessTest|RoleModuleAccessTest|PermissionRoutesTest|MaintenanceModeTest|InternalCommunicationTest|ImportacionesAccessTest|ExcelModeAccessTest|ContextCreationGuardTest|AdminTechnicalMutationTest|AdminDashboardTest"`

**Riesgo residual:**

- La base local actual auditada en modo read-only no refleja todavía por completo la matriz A.3 en tablas persistidas. El admin técnico ya queda alineado en runtime por código, pero sigue pendiente una resincronización controlada de permisos/roles para dejar consistentes también dirección/contable y la persistencia local, sin tocar datos operativos.

### Fase A.5 - Corrección de fallos residuales, archivos en rojo y barrido global previo a Fase B

**Estado:** cerrada con validación completa en local  
**Área:** requests API / validación técnica / documentación viva / suite final

**Qué se cerró:**

- Se revisaron los `FormRequest` API marcados en rojo en VS Code y se confirmó que el problema era de tipado estático, no de sintaxis.
- `BaseApiRequest` añade `currentUser(): ?User` y se sustituyen accesos genéricos en `StoreFacturaRequest`, `UpdateFacturaRequest`, `StorePedidoRequest` y `UpdatePedidoRequest`, dejando esos archivos sin errores de editor.
- `FacturaController` y `PedidoController` se ajustan para eliminar falsos positivos de tipado y mantener mensajes visibles consistentes.
- `TrabajoTest` se realinea con la matriz A.3: los casos positivos sobre trabajos finalizados pasan a dirección y la frontera de `admin técnico` queda preservada en `AdminTechnicalMutationTest`.
- Se completa el barrido global de residuos de depuración; no quedan `console.log`, `debugger`, `dd`, `dump`, `var_dump` ni `print_r` activos en archivos vivos del flujo actual.
- Se corrigen restos de texto visible sin tildes en documentación viva, exportaciones CSV, validaciones API y vistas React.

**Validaciones ejecutadas:**

- `php -l` sobre rutas, requests, reglas, servicios y controladores tocados en A.5: PASS.
- `TrabajoTest`, `AdminTechnicalMutationTest`, `RoleModuleAccessTest`, `PermissionRoutesTest`, `ContextCreationGuardTest`, `AdminAccessTest`, `FacturaTest` y `PedidoTest`: PASS.
- Ajustes adicionales descubiertos durante la validación (`FacturaExportTest`, `MaestrosTest`, `ExcelImportTest` y `ClientesEstacionesApiTest`): PASS.
- `npm run build`: PASS.
- `php artisan test`: PASS (`95` tests, `411` assertions).

**Riesgo residual:**

- No queda KO técnico abierto en código/tests del bloque A.5. Se mantiene como deuda separada la resincronización controlada de permisos/roles persistidos de la base local ya registrada en A.3.

### Fase A.6 - Revisión de `jsconfig` y normalización de campos numéricos previa a Fase B

**Estado:** implementada y validada en el slice afectado  
**Área:** frontend React / formularios operativos / alias editor VS Code

**Qué se cerró:**

- `jsconfig.json` mantiene `baseUrl` porque el proyecto sigue usando masivamente alias `@/*` y `ziggy-js`; se añade `ignoreDeprecations: "6.0"` para eliminar el aviso de VS Code sin romper resolución de imports.
- La auditoría numérica confirma como **texto** y no como `number` los identificadores operativos y fiscales: `numero_pedido`, `numero_factura`, `numero_factura_ccp`, `numero_trabajo_operativo`, `numero_aviso`, `codigo_estacion`, `codigo_postal`, `cif` y teléfonos.
- `numero_trabajo` se normaliza como **entero real en payload** en los dos flujos de alta revisados: formulario manual de trabajos y alta rápida Ciete Excel.
- `cantidad` en líneas de pedido vuelve a **entero operativo** (`step=1` en UI y validación entera en request) porque la evidencia real auditada no justifica decimales por defecto: los seeders reales usan `1.000` y las líneas tarifarias reales revisadas llegan con `id_unidad = NULL`.
- `unidades_solicitadas` vuelve a **entero operativo** (`step=1` en UI y validación entera en request) por el mismo motivo: hoy el formulario no conoce unidad/tarifa y el dato real revisado solo muestra `0.000` / `1.000`.
- La edición inline de facturas diferencia correctamente decimal frente a entero: `total` usa precisión `0.01`, mientras `orden_factura` usa `step=1`, `min=1` y se envía como entero.
- `factura_items.unidades_facturadas` **no** se endurece a entero: `FacturaTest` sigue cubriendo facturación parcial válida con `0.6`, `0.5`, `0.4` y `0.75`, así que ese decimal sí tiene evidencia funcional actual.
- La revisión de clientes, estaciones, tarifarios y sociedades facturadoras no detecta necesidad de convertir códigos ni referencias visibles a tipos numéricos.
- Matriz funcional detallada de A.6-R: `docs/02_CLIENTE/MATRIZ_TIPOS_NUMERICOS_CODIGOS_A6-R_2026-05-18.md`.

**Archivos principales tocados:**

- `jsconfig.json`
- `resources/js/Pages/Trabajos/Form.jsx`
- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `resources/js/Components/ui/ItemsTable.jsx`
- `resources/js/Pages/Pedidos/Form.jsx`
- `resources/js/Components/ui/PedidosExcelView.jsx`
- `resources/js/Components/ui/FacturasExcelView.jsx`

**Validaciones ejecutadas:**

- `get_errors jsconfig.json`: PASS.
- `php -l routes/web.php`: PASS.
- `php -l routes/api.php`: PASS.
- `npm run build`: PASS.
- `php artisan test tests/Feature/TrabajoTest.php tests/Feature/Api/TrabajoRequestTest.php tests/Feature/PedidoTest.php tests/Feature/FacturaTest.php tests/Feature/Api/ClientesEstacionesApiTest.php tests/Feature/MaestrosTest.php tests/Feature/AdminTechnicalMutationTest.php tests/Feature/RoleModuleAccessTest.php tests/Feature/ContextCreationGuardTest.php`: PASS (`90` tests, `519` assertions).

**Riesgo residual:**

- La Fase A.7 cerró los fallos residuales de suite completa sin reabrir A.6-R: el portal sigue sin registro público ni confirmación pública de contraseña, `admin` mantiene frontera técnica sin acceso a `/cierre` ni a creación de estaciones, y el 404 corporativo autenticado conserva salida por `homeUrl`.

### Fase A.7 - Cierre de suite completa antes de Fase B

**Estado:** cerrada y validada  
**Área:** suite completa / auth endurecida / fallback web / cierre / estaciones / error pages

**Diagnóstico exacto:**

- `PasswordConfirmationTest` y `RegistrationTest`: expectativa antigua; las rutas públicas siguen deshabilitadas, pero el fallback web devolvía `404` directo para invitados GET en lugar de redirigir a `/login`.
- `ErrorPagesTest`: una aserción de `404` seguía esperando `accessDenied.backHomeLabel`, aunque esa prop solo corresponde al caso `403`; la salida clara en `404` ya existe por `homeUrl` y el copy interno de `Error.jsx`.
- `ClosureDashboardTest`: expectativa antigua; `admin` técnico ya no puede entrar en `/cierre`, mientras `director` sí debe poder hacerlo.
- `EstacionesTest`: expectativa antigua; `admin` técnico conserva lectura operativa, pero la creación web de estaciones corresponde a perfiles funcionales autorizados como `director`.

**Corrección aplicada:**

- `routes/web.php`: el `Route::fallback` ahora redirige a login solo a invitados en `GET`; para no-`GET` mantiene `404`, y para autenticados `GET` devuelve la página corporativa `Error` con `homeUrl`.
- `tests/Feature/Auth/PasswordConfirmationTest.php`: se conserva la ausencia de ruta pública, con `GET` redirigido a `/login` y `POST` sin ruta (`405`).
- `tests/Feature/ClosureDashboardTest.php`: se realinea la cobertura para que `admin` sea negativo y `director` positivo en `/cierre`.
- `tests/Feature/ErrorPagesTest.php`: el `404` valida `component/status/homeUrl`; el `403` valida además `accessDenied.backHomeLabel`.
- `tests/Feature/EstacionesTest.php`: el positivo de creación pasa a `director` y se añade negativo explícito para `admin` técnico.

**Validación ejecutada:**

- `tests/Feature/Auth/PasswordConfirmationTest.php`: PASS.
- `tests/Feature/Auth/RegistrationTest.php`: PASS.
- `tests/Feature/ClosureDashboardTest.php`: PASS.
- `tests/Feature/ErrorPagesTest.php`: PASS.
- `tests/Feature/EstacionesTest.php`: PASS.
- Batería conjunta de los cinco grupos: PASS (`20` tests).
- Suite completa `php artisan test`: PASS (`218` tests, `1286` assertions).
- `npm run build`: PASS.

**Impacto en reglas funcionales:**

- No se activa registro público.
- No se reactiva confirmación pública de contraseña.
- No se devuelve a `admin` capacidad funcional sobre cierre o creación de estaciones.
- Fase B ya no queda bloqueada por la suite completa.

### Fase A.8 - Modularización controlada de rutas web antes de Fase B

**Estado:** cerrada y validada  
**Área:** arquitectura de rutas Laravel / mantenimiento / diagnóstico editor

**Motivo:**

- `routes/web.php` había crecido hasta mezclar inicio, contexto, administración, dirección, operativa, maestros, soporte, comunicaciones, importaciones, estado, fallback y closures auxiliares de facturación.
- El archivo grande hacía más difícil auditar permisos/rutas y favorecía falsos positivos de VS Code/Intelephense por helpers dinámicos de Laravel y closures internas.
- La fase no añade funcionalidad: solo mueve rutas conservando URLs, names, middlewares, controladores, props Inertia y lógica vigente.

**Estructura nueva:**

- `routes/web.php` queda como índice de carga.
- `routes/web/public.php`: `locale.update`, `/`, perfil y ayuda.
- `routes/web/contexto.php`: cambio de contexto activo.
- `routes/web/operativa.php`: trabajos, pedidos, facturas y closures auxiliares de facturación.
- `routes/web/maestros.php`: clientes, estaciones, contratos, sociedades facturadoras, tarifarios y líneas.
- `routes/web/direccion.php`: dashboard de dirección y cierre.
- `routes/web/admin.php`: panel admin, mantenimiento, avisos admin, usuarios, soporte admin y auditoría.
- `routes/web/soporte.php`: soporte de usuario.
- `routes/web/comunicaciones.php`: mensajes internos y broadcast.
- `routes/web/importaciones.php`: vistas y acciones de importación.
- `routes/web/estado.php`: estado del sistema.
- `routes/web/fallback.php`: preview local/testing de errores y fallback final.

**Decisiones conservadoras:**

- `auth.php` se mantiene cargado desde `routes/web.php`.
- `fallback.php` se carga al final.
- El fallback conserva el comportamiento de A.7: invitado + GET desconocido redirige a login, autenticado + GET desconocido devuelve 404 corporativo y no-GET desconocido mantiene 404.
- Se cambia `auth()->check()` por `Auth::check()` en `routes/web/fallback.php` para evitar el falso positivo del helper dinámico.
- Las closures auxiliares grandes de facturación no se convierten en servicios en esta fase: se mueven junto a Facturas en `routes/web/operativa.php` para no refactorizar lógica de negocio.

**Validación ejecutada:**

- `php -l routes/web.php`: PASS antes y después.
- `php -l` sobre todos los módulos `routes/web/*.php`: PASS.
- `php artisan route:list > storage/app/route-list-before-a8.txt`.
- `php artisan route:list > storage/app/route-list-after-a8.txt`.
- Comparación `diff` antes/después: sin diferencias; rutas desaparecidas `0`, names cambiados `0`, salida `route:list` mantiene `137` líneas y `133` rutas mostradas.
- Tests focalizados de fallback/auth/accesos/admin: PASS.
- Tests focalizados de trabajos, pedidos, facturas, clientes/estaciones, maestros, cierre y estaciones: PASS.
- Suite completa `php artisan test`: PASS (`218` tests, `1286` assertions).
- `npm run build`: PASS; solo warning de timing del plugin `laravel`.

**Diagnóstico editor:**

- `routes/web.php` queda sin closures ni llamadas a helpers Laravel dinámicos; solo requiere módulos.
- `routes/web/fallback.php` ya no usa `auth()->check()`.
- No hay CLI de Intelephense disponible en este entorno, por lo que la confirmación se limita a revisión estática y validación PHP/tests.
- Si quedase algún aviso residual, debería estar concentrado en closures Laravel legítimas dentro de `routes/web/operativa.php`, no en el índice `web.php`.

**Impacto funcional:**

- No cambian URLs públicas.
- No cambian nombres de ruta.
- No cambian permisos ni middlewares funcionales.
- No cambia base de datos, seeders, importaciones, exportaciones, producción, Git/GitHub ni ramas.
- No se hizo commit.

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

| Tarea                                                              | Estado           | Nota                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| ------------------------------------------------------------------ | ---------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Documentación estructural limpia                                   | cerrado          | La estructura documental viva ya está organizada.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
| Sintesis interpretativa secundaria creada                          | cerrado          | `DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
| Historial de decisiones creado                                     | cerrado          | `HISTORIAL_DECISIONES_ERP_CIETE.md`.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| Raíz de `docs/` limpia                                             | cerrado          | Solo documentos de entrada general y carpetas.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| Contexto activo backend en sesión                                  | cerrado/parcial  | Base correcta; queda revisar métodos de creación contextuales.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| Selector/indicador de contexto                                     | cerrado          | Usuario monocontexto con indicador; multicontexto con selector.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| Auditoría visible para Dirección/admin                             | cerrado          | Visible, filtrable y validada con export/limpieza operativas sin contaminar la vista por defecto con preferencias visuales nuevas.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
| Auditoría operativa filtrable                                      | cerrado          | El filtro por defecto muestra actividad operativa real; clientes y estaciones auditados en flujo vivo, logs legacy visuales fuera de la vista operativa.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| Ciete Excel de trabajos                                            | parcial avanzado | Tabla densa, columnas clave, edición por campo y nueva fila.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| Observaciones en modal                                             | cerrado          | Implementado en Ciete Excel de trabajos.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| Control optimista por campo en trabajos                            | cerrado/parcial  | Implementado en trabajos; no extendido a todos los módulos.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |
| Filtros de estaciones por municipio/provincia/código               | cerrado          | La API y las vistas Ciete Excel/Ciete Moderno ya buscan y filtran por código, municipio, provincia y estado operativo con cliente/contexto visible; validado con `ClientesEstacionesApiTest` y build.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                |
| P0-03 - `pedido_items` estables por ID                             | cerrado          | `syncItems()` actualiza por `id_pedido_item`, crea nuevos y conserva facturados; archivos principales: `PedidoController`, requests/resource de pedidos, `Pedidos/Form.jsx`, `ItemsTable.jsx`; validado con `php -l`, `PedidoTest`, `route:list` y `npm.cmd run build`; sociedades permitidas quedan gobernables desde Maestros.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| P0-01 - Facturación real por `factura_items`                       | cerrado          | Facturas crean/actualizan `factura_items`, conservan IDs de líneas existentes, calculan asignado/diferencia/cuadre y bloquean sobre-facturación; archivos principales: `FacturaController`, requests/resource de facturas, `Pedido`, rutas web, `Facturas/Form.jsx`, `Facturas/Index.jsx`, `FacturasExcelView.jsx`, `FacturaTest`; validado con `php -l`, `FacturaTest`, `route:list` y `npm.cmd run build`; integrado con P0-02 para sociedad/CIF permitida.                                                                                                                                                                                                                                                                                                                                                                                        |
| P0-02 - Sociedad/CIF validada por contrato/tarifa                  | cerrado          | Se endureció la validación por `contrato_empresas_facturadoras`: sociedad/CIF obligatoria en facturas por ítems, CIF informado, contexto correcto y pivot activa; frontend filtra sociedades/ítems compatibles; archivos principales: `FacturaController`, requests/resource de facturas, `routes/web.php`, `Facturas/Form.jsx`, `Facturas/Index.jsx`, `FacturasExcelView.jsx`, `DatabaseSeeder`, `FacturaTest`; validado con `php -l`, `migrate:status`, `route:list`, `FacturaTest` y `npm.cmd run build`; la pivot ya tiene gobierno mínimo desde Maestros y queda pendiente cargar datos reales de OTROS CLIENTES.                                                                                                                                                                                                                               |
| P0-04 - Bloqueo uniforme de creación desde TODOS                   | cerrado          | `ContextGuard` bloquea altas desde TODOS y mantiene edición de existentes si el contexto real es accesible; cubre trabajos, pedidos, facturas, estaciones, clientes e importaciones; archivos principales: `ContextGuard`, middleware Inertia, `ActiveContextController`, requests de trabajos/facturas/importaciones, formularios y `ContextCreationGuardTest`; validado con `php -l`, `route:list`, `ContextCreationGuardTest`, `TrabajoTest`, `PedidoTest`, `FacturaTest` y `npm.cmd run build`; `EstacionesTest` queda bloqueado por Excel fuente ausente, no por contexto.                                                                                                                                                                                                                                                                      |
| P0-05 - OTROS CLIENTES contexto real completo                      | cerrado          | OTROS CLIENTES se presenta como contexto real; tras P1-11 el código demo queda normalizado a `OTROS`; puede crear cliente, estación, trabajo, pedido y factura si tiene contrato/sociedad permitida; las validaciones MOEVE/REPSOL no se aplican automáticamente a OTROS; archivos principales: `ContextGuard`, middleware Inertia, requests de trabajos/facturas, `ContextosClienteSeeder`, formularios y locales; validado con tests de contexto/factura/trabajo/pedido y build; riesgo pendiente: cargar datos maestros reales de OTROS.                                                                                                                                                                                                                                                                                                          |
| P0-06 - Estados reales de trabajos y eliminación de legacy visible | cerrado          | Altas de trabajos en `en_curso`, requests/controlador bloquean `borrador`/`cerrado` como estados nuevos, marcado `terminado` rellena `fecha_terminacion`, `ClosureDashboardService` finaliza con `finalizado` y legalizaciones informativas; tras P1-11 los enums/seeders/demo ya no conservan esos estados ni las columnas antiguas de cierre de trabajos; archivos principales: `TrabajoController`, requests/resource/modelo de trabajos, `ClosureDashboardService`, `Trabajos/Form.jsx`, `Trabajos/Index.jsx`, `TrabajosExcelView.jsx`, `Cierre/Dashboard.jsx`, locales y tests; validado con `php -l`, `route:list`, `TrabajoTest`, `ClosureDashboardTest`, `TrabajoRequestTest`, `ContextCreationGuardTest`, `PedidoTest`, `FacturaTest`, `EstacionesTest` y `npm.cmd run build`; riesgo pendiente: solo documentación histórica o módulos P2. |
| P0-07 - Permisos/rutas mutables y seeders                          | cerrado          | Rutas web/API separadas por acción (`ver`, `crear`, `editar`, `eliminar/cancelar/anular`), permisos faltantes sembrados, roles funcionales alineados y hard delete mitigado en trabajos, pedidos, facturas, estaciones y clientes; tras P1-11 se eliminan alias legacy vivos en `User::hasPermission()`; archivos principales: `routes/api.php`, `routes/web.php`, `User`, `PermisosSeeder`, `RolPermisosSeeder`, controladores API, sidebar/listados Excel y `PermissionRoutesTest`; validado con `php -l`, `route:list`, tests de permisos/contexto/trabajos/pedidos/facturas/estaciones/cierre/clientes/auditoría y `npm.cmd run build`; riesgo pendiente: CRUD/gobierno de roles-permisos reales.                                                                                                                                                |
| Estabilización de validaciones recurrentes                         | cerrado          | Se corrigió la ruta del Excel `Contrato 772 MOEVE - Tarifario.xlsx` desde la raíz antigua de `docs/` a `docs/02_CLIENTE/materiales/`; se alineó `EstacionesTest` con los permisos sembrados (`ejecucion_moeve` puede ver estaciones pero no gestionarlas) y con el usuario real `contable@ciete.es`; se validó con `php -l`, `route:list`, `EstacionesTest`, `ContextCreationGuardTest`, `TrabajoTest`, `PedidoTest`, `FacturaTest` y `npm.cmd run build` fuera del sandbox tras EPERM; archivos principales: `DemoCieteOperativaSeeder`, `ContratosBaseSeeder`, `tests/Feature/EstacionesTest.php`, `docs/02_CLIENTE/materiales/README.md`; no sube porcentaje porque estabiliza entorno/tests sin añadir funcionalidad ERP.                                                                                                                        |
| `factura_items` creado estructuralmente                            | cerrado          | Estructura y flujo funcional cerrados por P0-01; validación fiscal cerrada por P0-02.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                |
| `interface_mode` creado estructuralmente                           | cerrado          | Existe como preferencia UX, pero ya no genera eventos nuevos de Auditoría operativa; los logs legacy visuales quedan ocultos por defecto.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            |

## 9. Tareas descartadas o fuera de alcance

| Tarea                                                  | Decisión                                                                                                 |
| ------------------------------------------------------ | -------------------------------------------------------------------------------------------------------- |
| Cobros en fase actual                                  | Fuera de alcance. El ERP CIETE termina en factura.                                                       |
| Presupuestos en fase actual                            | Fuera de alcance inmediato.                                                                              |
| Legalizaciones completas en fase actual                | No bloquear flujo crítico.                                                                               |
| Importación Excel avanzada antes de cerrar facturación | La carga real controlada queda cerrada por P1-12; queda como P2 la automatización avanzada/UI de avisos. |
| Actualización automática de estaciones                 | P2.                                                                                                      |
| Borrado físico de estaciones                           | Descartado por histórico.                                                                                |
| Reapertura normal de trabajos finalizados              | Descartada como flujo normal; si aparece algo nuevo, se crea otro trabajo.                               |

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

### Fase B.1 - Carga real controlada desde Excel a `abaco_ciete`

**Estado:** B.1 completada sobre base local real  
**Fecha ejecucion:** 2026-05-19  
**Documento de evidencia:** `docs/02_CLIENTE/FASE_B1_IMPORTACION_REAL_EXCEL_2026-05-18.md`

- Entorno confirmado: `APP_ENV=local`, `DB_CONNECTION=mysql`, `DB_DATABASE=abaco_ciete`, `DB_USERNAME=root`.
- Backup previo creado con `mysqldump.exe`: `database/backups/abaco_ciete_pre_fase_b_import_real_20260519_032311.sql` (138.838 bytes).
- Fuente real usada: `C:/Users/kampe/Documents/Abaco/excelsactualizados`.
- Excel inventariados: 12; importaciones operativas registradas: 11. `Mapeo_Moeve_Repsol_Envolvente.xlsx` queda como auxiliar no importable.
- Se detecto contaminacion FD26/demo (`FD26 MOEVE CLIENTE` con CIF `A28003119`) que bloqueaba el dry-run por clave unica. Tras backup se limpio solo dato operativo/demo, preservando contextos, usuarios, roles y permisos; no se ejecuto `migrate:fresh`.
- Dry-run final: 53.055 filas leidas, 51.288 importadas/actualizadas, 1.767 ignoradas, 3.726 con aviso, 0 errores.
- Commit real: PASS con los mismos conteos y 0 errores fatales.
- Reconciliacion P1-12: 11.614 trabajos, 9.685 pedidos, 9.701 `pedido_items`, 2.733 facturas, 9.194 `factura_items` y 11 importaciones registradas.
- Integridad basica: 0 huerfanos criticos, 0 contextos mezclados, 0 estados desconocidos contra enums reales. Quedan avisos de calidad de datos: 1.356 trabajos sin estacion, 2 pedidos/items negativos MOEVE, 6 fechas imposibles en trabajos y 4 en pedidos.
- Validacion tecnica minima: `ImportacionesAccessTest`, `ClientesEstacionesApiTest`, `MaestrosTest`, `TrabajoTest`, `PedidoTest`, `FacturaTest` PASS; `npm run build` PASS con warning no bloqueante de timing del plugin `laravel`.
- No se tocaron produccion, GitHub, ramas, exportaciones ni Fase B.2; no se hizo commit.

**Siguiente paso:** pasar a B.2 para validacion funcional completa sobre base real cargada, llevando como insumos los avisos de calidad de datos documentados.

### Fase B.1.1 - Ajuste visual Trabajos + auditoría de cobertura real (post-importación)

**Estado:** cerrada (lista para B.2)  
**Fecha ejecución:** 2026-05-19  
**Documento de evidencia:** `docs/02_CLIENTE/B1_1_AJUSTES_VISUALES_Y_AUDITORIA_COBERTURA_DATOS_2026-05-19.md`

**Alcance ejecutado:**

- Ajuste visual de zona de cuenta/sidebar: `Mi perfil` visible para todos los roles (incluido admin técnico) sin reintroducir `Perfil` en `sidebar.js`.
- Integración de avatar de cuenta con fallback sin imagen rota en desktop y móvil.
- Corrección visual de tabla moderna de trabajos para evitar solapamiento `Nº` vs `Descripción` con valores largos (ej. `865.66666666666697`) sin alterar datos.
- Auditoría read-only completa sobre base real: `importaciones`, `importacion_filas`, maestros, relaciones y controles de integridad extendida.

**Archivos principales tocados:**

- `resources/js/Components/UserAccountAvatar.jsx` (nuevo)
- `resources/js/Layouts/AuthenticatedLayout.jsx`
- `resources/js/Components/MobileSidebarDrawer.jsx`
- `resources/js/Pages/Trabajos/Index.jsx`
- `docs/02_CLIENTE/B1_1_AJUSTES_VISUALES_Y_AUDITORIA_COBERTURA_DATOS_2026-05-19.md` (nuevo)
- `docs/02_CLIENTE/tareasComparar.md`
- `docs/02_CLIENTE/VALIDACION_OPERATIVA_KO_EVIDENCIAS_ERP_CIETE_2026-05-18.md`

**Validación ejecutada:**

- PASS: `ImportacionesAccessTest`, `ClientesEstacionesApiTest`, `MaestrosTest`, `TrabajoTest`, `PedidoTest`, `FacturaTest`, `RoleModuleAccessTest`, `ExcelModeAccessTest`, `ContextCreationGuardTest`.
- PASS: `npm run build` (warning no bloqueante de timings en plugin `laravel`).

**Conclusión funcional B.1.1:**

- Sí, conteos clave y relaciones principales están cargados y coherentes.
- No, no se puede afirmar que todas las columnas auxiliares/avisos estén resueltos; quedan decisiones CIETE sobre avisos de fuente.
- Sin KO crítico/mayor nuevo en esta fase; queda **estado previo a B.2: listo**.

**Nota de porcentaje global:**

- No se actualiza porcentaje global en esta fase; B.1.1 consolida evidencia y ajustes visuales post-importación.

### Fase B.1.2 - Revisión global de tablas, casos B2 y versión (2026-05-19)

**Estado:** cerrada - lista para B.2  
**Documento de evidencia:** `docs/02_CLIENTE/B1_2_REVISION_GLOBAL_TABLAS_CASOS_DEMO_Y_VERSION_2026-05-19.md`

Resumen de ejecución:

- Revisión visual global con datos reales en listados principales (trabajos, pedidos, facturas y maestros).
- Corrección quirúrgica del caso activo en Facturas (`Nº FACTURA` largo invadiendo `FECHA`) en modo moderno y modo Excel.
- Refuerzo preventivo en Pedidos moderno para referencias largas.
- Revisión/completado del control de conflicto de edición por celda en Trabajos Excel con mensaje específico de "modificado recientemente" y preservación de borrador.
- Preparación de muestra local reversible B2 solo para OTROS (contexto sin datos reales importados) con marca:
    - `[muestra-b2-validacion-2026-05-19]`
- Scripts creados:
    - `database/manual/2026_05_19_insert_muestra_b2_validacion_flujo_diario.sql`
    - `database/manual/2026_05_19_delete_muestra_b2_validacion_flujo_diario.sql`
- Versión visible en código activo: `ERP CIETE v2.1.0`. (B.1.2 documentó `v2.1.0-rc1` internamente; B.2 confirma que el código activo ya está en `v2.1.0` sin sufijo.)

Validación técnica ejecutada:

- `php -l` en PHP tocados: PASS.
- Tests objetivo: PASS (`TrabajoTest`, `PedidoTest`, `FacturaTest`, `ClientesEstacionesApiTest`, `MaestrosTest`, `RoleModuleAccessTest`, `ExcelModeAccessTest`, `ContextCreationGuardTest`, `AdminTechnicalMutationTest`).
- `npm run build`: PASS.

Nota técnica de ejecución:

- Se detectó estado inconsistente inicial en `abaco_ciete_testing` por colisión de migraciones al lanzar filtros de test en paralelo.
- Se saneó exclusivamente la DB de pruebas (`DROP/CREATE abaco_ciete_testing`) y se repitieron los filtros fallidos.
- `abaco_ciete` real no se alteró en ese saneamiento.

Criterio de estado:

- Sin KO crítico/mayor funcional nuevo en B.1.2.
- Quedan avisos de fuente ya heredados (B.1/B.1.1) para validación funcional en B.2.

### Fase B.2 - Validación funcional completa post-importación (2026-05-19)

**Estado:** validado técnicamente con KOs menores (pendiente cierre visual manual en navegador)  
**Documento de evidencia:** `docs/02_CLIENTE/FASE_B2_VALIDACION_FUNCIONAL_COMPLETA_POST_IMPORTACION_2026-05-19.md`

Resumen B.2:

- `artisan test` completo: PASS (`218` tests, `1286` assertions).
- `npm run build`: PASS (warning no bloqueante de timings plugin `laravel`).
- `route:list`: PASS (`133` rutas).
- Batería focalizada solicitada (11 filtros): PASS.
- Cobertura adicional por rol/módulo (admin, dirección, contable, ejecución, error pages, soporte/comunicaciones, mantenimiento, auditoría): PASS.
- Casos B2 OTROS verificados en base local con marca `[muestra-b2-validacion-2026-05-19]`.
- Sin KO crítico/mayor nuevo; persisten avisos de fuente heredados de B.1/B.1.1.

Notas de ejecución:

- Se detectó inestabilidad puntual de `abaco_ciete_testing` al ejecutar tests de forma paralela (colisiones de migraciones/FK en entorno de pruebas).
- Mitigación aplicada: reset exclusivo de DB de pruebas y reejecución secuencial; sin impacto en `abaco_ciete` real.

Propuesta:

- Base técnicamente validada. Pendiente pasada visual manual final (B.2.1) para cierre formal. Versión vigente: `ERP CIETE v2.1.0`; sin cambio de versión hasta completar B.2.1.

**B.2.1 COMPLETADA (2026-05-19):** `v2.1.0 validada con KOs menores`. KO mayor de paginación corregido (Trabajos/Pedidos/Facturas). KOs menores: badge "VERSIÓN 2.0" en hero (cosmético). Todos los módulos y roles validados visualmente.

**B.2.2 COMPLETADA (2026-05-19):** Badge `badgeVersion` → `'v2.1.0'` en `es.js`/`en.js` (KO menor B.2.1 cerrado). Componente `PaginationControls` creado y aplicado en 14 archivos de listado (Primera/Anterior/Siguiente/Última + "Ir a página"). Migración `home_notices` ejecutada, seeder con 10 mensajes bilingües v2.1.0 cargados, `HomeNoticeAdminTest` (4 tests) PASS. Build PASS. **`v2.1.0 validada`.**

### Mensajes de inicio administrables (2026-05-19)

**Estado:** implementado y validado. Migración ejecutada en B.2.2; seeder actualizado con 10 mensajes v2.1.0; `HomeNoticeAdminTest` (4 tests, 40 aserciones) PASS.

**Alcance cerrado:**

- Los mensajes de la pantalla de inicio dejan de depender del JSON editable y pasan a tabla `home_notices`.
- Se prepara contenido bilingüe realista para Inicio: mensaje destacado de reunión CIETE, 3 avisos internos no destacados, 3 actualizaciones del sistema y 3 novedades de empresa.
- El Inicio solo lee mensajes activos y no expone creación/edición.
- La administración permite crear, editar, activar/desactivar, categorizar y marcar/quitar destacado.
- Al marcar un mensaje como destacado, el backend desmarca cualquier destacado anterior.
- Las operaciones administrativas relevantes se registran en `audit_log` mediante `AuditLogger`.

**Archivos tocados:**

- `database/migrations/2026_05_19_000170_create_home_notices_table.php`
- `app/Models/HomeNotice.php`
- `database/seeders/HomeNoticeSeeder.php`
- `database/seeders/DatabaseSeeder.php`
- `app/Support/HomeNoticeCatalog.php`
- `app/Http/Controllers/Admin/NoticeController.php`
- `app/Http/Controllers/Admin/DashboardController.php`
- `routes/web/admin.php`
- `routes/web/public.php`
- `resources/js/Pages/Admin/Dashboard.jsx`
- `resources/js/Pages/Welcome.jsx`
- `resources/js/Components/WelcomeHome/InstitutionalHero.jsx`
- `resources/js/Components/WelcomeHome/InstitutionalInfoBlocks.jsx`
- `resources/js/i18n/locales/es.js`
- `resources/js/i18n/locales/en.js`
- `tests/Feature/HomeNoticeAdminTest.php`
- `docs/02_CLIENTE/tareasComparar.md`

**Validaciones ejecutadas:**

- `npm run build`: PASS. Warning no bloqueante de timings en plugin `laravel`.
- `php artisan test --filter=HomeNoticeAdminTest`: PASS (4 tests, 40 aserciones) — ejecutado en B.2.2 con PHP disponible.
- `git diff --check`: no limpio por trailing whitespace preexistente en este documento; no se normaliza para evitar cambios documentales ajenos al alcance.

**Nota funcional:**

Los mensajes de inicio se gestionan desde administración, dentro del bloque "Mensajes de la pantalla de inicio". El dashboard de inicio queda como superficie de lectura y no contiene textos hardcodeados ni acciones de edición.

**Porcentaje de avance:**

No se recalcula ni se sube el porcentaje global en esta entrada: aunque el CRUD administrativo y el build son verificables, queda pendiente ejecutar `php artisan test` completo en un entorno con PHP disponible.
