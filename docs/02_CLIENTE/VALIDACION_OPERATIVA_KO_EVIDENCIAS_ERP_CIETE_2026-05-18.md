# Registro KO y Evidencias - Validación Operativa ERP CIETE (Fase A)

Fecha: 2026-05-18  
Ámbito: local FD26 / `abaco_ciete`

## Criterio de severidad

- crítico: rompe flujo diario, aislamiento o control económico.
- mayor: no rompe completamente, pero bloquea/ralentiza operativa normal.
- menor: mejora de UX/texto/maquetación no bloqueante.

## Registro

| KO detectado                                                                                | Perfil afectado                      | Escenario afectado                                          | Modo visual afectado: Moderno / Excel / Ambos | Severidad: crítico / mayor / menor | Evidencia breve                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            | Impacto funcional                                                                                                | Causa raiz probable                                                                                      | Archivo o módulo probable                                                                                                                                                                                             | Corrección propuesta                                                                                           | Estado: pendiente / corregido / validado |
| ------------------------------------------------------------------------------------------- | ------------------------------------ | ----------------------------------------------------------- | --------------------------------------------- | ---------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------- | ---------------------------------------- |
| `TODOS` aparecía como opción de selector                                                    | Todos                                | Inicio común / selector de contexto                         | Ambos                                         | crítico                            | Middleware de Inertia agregaba opción `all` + `workspace_key=todos`                                                                                                                                                                                                                                                                                                                                                                                                                                        | Riesgo de operar en vista global no permitida                                                                    | Lógica legacy de contexto global en payload frontend                                                     | `app/Http/Middleware/HandleInertiaRequests.php`                                                                                                                                                                       | Eliminar `TODOS` de `available_contexts` y normalizar sesión legacy a contexto real                            | corregido                                |
| Cambio manual de contexto a `all` posible por endpoint                                      | Multicontexto / Admin                | Cambio de contexto                                          | Ambos                                         | crítico                            | `contexto.activo.update` aceptaba `all` si era multicontexto                                                                                                                                                                                                                                                                                                                                                                                                                                               | Permitía volver a vista global prohibida                                                                         | Validación de controlador sin veto explícito a `all`                                                     | `app/Http/Controllers/ActiveContextController.php`                                                                                                                                                                    | Rechazar `all/todos/todo` con mensaje funcional                                                                | corregido                                |
| Selector frontend permitía `TODOS` por defecto                                              | Todos                                | Barra superior de contexto                                  | Ambos                                         | crítico                            | `ContextSelector` tenía `allowAllContexts=true` por defecto                                                                                                                                                                                                                                                                                                                                                                                                                                                | Exponía selector no permitido                                                                                    | Configuración por defecto permisiva                                                                      | `resources/js/Components/ContextSelector.jsx`                                                                                                                                                                         | Cambiar default a no permitir `TODOS`                                                                          | corregido                                |
| Usuario `contable` redirigido a `Trabajos` tras login y acceso indebido a módulo `Trabajos` | Contable                             | Inicio común / control de accesos por rol                   | Ambos                                         | crítico                            | Detectado en Fase A: login podía acabar en `/trabajos` por `redirect()->intended(...)`; además el rol `contable` mantenía `trabajos.ver` en seed y la ruta aceptaba acceso por permiso. Corregido: login forzado a `/`, `contable` sin `trabajos.ver`, rutas de `trabajos` con bloqueo explícito `forbid_role:contable` (web/api). Validado con `LoginRedirectTest`, `RoleModuleAccessTest`, `ExcelModeAccessTest`.                                                                                        | Riesgo de romper inicio común y de mezclar operativa contable con módulo de ejecución                            | Redirección post-login basada en intended + matriz de permisos no alineada para contable                 | `AuthenticatedSessionController`, `RolPermisosSeeder`, `routes/web.php`, `routes/api.php`, `ForbidRoleMiddleware`, tests de acceso/login                                                                              | Forzar redirección a Inicio común y bloquear Trabajos para contable en UI+ruta+tests                           | validado                                 |
| Control de acceso parcial: validación centrada en Trabajos y no en toda la superficie ERP   | Todos los perfiles                   | Cobertura global de pantallas/rutas                         | Ambos                                         | crítico                            | Fase inicial cubría login + trabajos, pero faltaba auditar `dashboard`, `maestros`, `admin`, `mensajes/avisos`, `soporte`, `clientes`, `estaciones`, `pedidos`, `facturas`, `auditoría`, `importaciones` y variantes de modo visual. Corregido: inventario completo, matriz global rol x pantalla, ajustes de middleware/rol, ampliación de tests por rol y vista de acceso denegado con salida a Inicio.                                                                                                  | Riesgo de huecos de acceso por URL directa fuera de Trabajos                                                     | Cobertura inicial incompleta de rutas y ausencia de matriz global por pantalla                           | `routes/web.php`, `resources/js/navigation/sidebar.js`, `resources/js/Pages/Error.jsx`, `bootstrap/app.php`, `tests/Feature/*AccessTest.php`, `docs/02_CLIENTE/MATRIZ_ACCESOS_ROLES_ERP_CIETE_2026-05-18.md`          | Auditar y cerrar acceso por pantalla en todo el ERP con regla menú+ruta+test                                   | validado                                 |
| Listado Clientes sin paginación operativa 10                                                | Contable / Admin / Ejecución         | Clientes                                                    | Ambos                                         | mayor                              | Consumía `per_page=50` y sin controles prev/sig                                                                                                                                                                                                                                                                                                                                                                                                                                                            | Scroll largo, pérdida de control en volumen                                                                      | Parámetro de API y UI sin estándar común                                                                 | `resources/js/Pages/Clientes/Index.jsx`, `Api/ClienteController`                                                                                                                                                      | `per_page=10`, estado de página, resumen X-Y-Z, anterior/siguiente                                             | corregido                                |
| Listado Estaciones sin paginación operativa 10                                              | Ejecución / Admin                    | Estaciones                                                  | Ambos                                         | mayor                              | Consumía `per_page=50` y sin controles prev/sig                                                                                                                                                                                                                                                                                                                                                                                                                                                            | Operativa lenta con volumen                                                                                      | Igual que clientes                                                                                       | `resources/js/Pages/Estaciones/Index.jsx`, `Api/EstacionController`                                                                                                                                                   | `per_page=10`, estado de página, resumen X-Y-Z, anterior/siguiente                                             | corregido                                |
| Maestros (sociedades/tarifarios/líneas) sin control visible de página                       | Contable / Dirección / Admin         | Maestros económicos                                         | Moderno                                       | mayor                              | Backend paginaba pero UI no mostraba navegación                                                                                                                                                                                                                                                                                                                                                                                                                                                            | No accesibles páginas >1                                                                                         | Falta de bloque de paginación en vistas                                                                  | `resources/js/Pages/SociedadesFacturadoras/Index.jsx`, `Tarifarios/Index.jsx`, `Tarifarios/Lineas.jsx`                                                                                                                | Añadir paginación y contador de registros                                                                      | corregido                                |
| Cierre dirección sin paginación en listado principal                                        | Dirección / Cesar                    | Cierre                                                      | Moderno                                       | mayor                              | Renderizaba todos los trabajos filtrados en una sola vista                                                                                                                                                                                                                                                                                                                                                                                                                                                 | Fatiga visual y riesgo de omisión en volumen                                                                     | Lista client-side sin corte por página                                                                   | `resources/js/Pages/Cierre/Dashboard.jsx`                                                                                                                                                                             | Paginación local 10 por página + resumen X-Y-Z                                                                 | corregido                                |
| Soporte/tickets sin navegación de páginas visible                                           | Administración técnica               | Soporte                                                     | Moderno                                       | mayor                              | Backend paginado, UI sin botones de página                                                                                                                                                                                                                                                                                                                                                                                                                                                                 | Dificulta gestión de histórico                                                                                   | Falta bloque de paginación en vista                                                                      | `resources/js/Pages/Admin/Support/Index.jsx`                                                                                                                                                                          | Añadir anterior/siguiente + resumen X-Y-Z                                                                      | corregido                                |
| Validación manual REPSOL en Ciete Excel pendiente en FD26                                   | Ejecución REPSOL                     | Trabajos/Pedidos/Facturas REPSOL                            | Excel                                         | mayor                              | Validado en navegador local FD26 con `repsol@ciete.es` (modo ciete*excel, contexto 2). Badge "Repsol" visible, 5 trabajos FD26-REP-*, estaciones solo FD26-REP-001/002, pedidos P-FD26-REP-\_, tipos documental/trabajo propios de REPSOL, sin datos MOEVE/CEPSA, sin opción TODOS, paginación correcta (5<10, una página).                                                                                                                                                                                | Sin riesgo residual detectado. Flujo REPSOL Excel operativo y aislado.                                           | Pasada manual completada 2026-05-18 con usuario real                                                     | `TrabajosExcelView.jsx`, `WorkspaceContextIndicator.jsx`, `ContextSelector.jsx`                                                                                                                                       | Sin corrección necesaria. Flujo válido.                                                                        | validado                                 |
| Validación manual multicontexto en Ciete Excel pendiente en FD26                            | Multicontexto                        | Cambio de contexto y no mezcla                              | Excel                                         | mayor                              | Validado en navegador local FD26 con `cesar@ciete.es` (modo ciete_excel, contextos [1,2,3]). Ciclo completo: OTROS→MOEVE→REPSOL. En cada cambio: badge de contexto correcto, trabajos/estaciones/pedidos exclusivos del contexto, sin arrastre de datos. Selector muestra 3 contextos reales sin TODOS. Payload `contexto=all` rechazado con HTTP 422.                                                                                                                                                     | Sin riesgo residual detectado. Multicontexto Excel operativo sin mezcla.                                         | Pasada manual completada 2026-05-18 con usuario real                                                     | `ContextSelector.jsx`, `ActiveContextController.php`, `TrabajosExcelView.jsx`                                                                                                                                         | Sin corrección necesaria. Flujo válido.                                                                        | validado                                 |
| La base local actual todavía no refleja la matriz A.3 del administrador técnico             | Admin técnico / Dirección / Contable | Admin, dirección, mantenimiento, avisos y lectura operativa | Ambos                                         | mayor                              | Auditoría read-only 2026-05-18 sobre la base actual: `admin@ciete.es` no tiene `admin.panel.ver`, `mantenimiento.gestionar` ni `avisos.gestionar`; `cesar@ciete.es` no tiene `avisos.gestionar`; `contable@ciete.es` conserva `trabajos.ver`. Tras el ajuste final A.3, el runtime ya blinda al rol `admin` con permisos técnicos efectivos y sin mutación operativa aunque arrastre slugs legacy, pero la persistencia local sigue sin resincronizar para dirección/contable y para coherencia de tablas. | Riesgo de incoherencia entre tablas persistidas y comportamiento efectivo si no se resincronizan permisos/roles. | La fase A.3 no debía tocar la base real/post-importación; solo se cambiaron código, seeders, UI y tests. | `database/seeders/PermisosSeeder.php`, `database/seeders/RolPermisosSeeder.php`, `database/seeders/UsuariosInicialesSeeder.php`, `app/Models/User.php`, datos persistidos `roles/permisos/rol_permisos/usuario_roles` | Ejecutar resincronización controlada de permisos/roles sobre la base local actual y reauditar usuarios reales. | pendiente                                |

## Resumen de severidad (corte actual)

- KO críticos pendientes: 0
- KO mayores pendientes: 1
- KO menores pendientes: 0

## Actualización A.5 (cierre residual)

- Requests API marcados en rojo por VS Code: corregidos mediante tipado explícito de usuario en `BaseApiRequest` y su consumo en facturas/pedidos.
- `TrabajoTest` y la suite completa quedan en verde; las expectativas antiguas que atribuían mutación operativa a `admin` se realinean con la matriz A.3 y dirección.
- El barrido residual no detecta residuos activos de depuración en archivos vivos; solo se corrigieron textos visibles y aserciones de test desfasadas por esa normalización.
- El estado global del registro no cambia: KO críticos 0 y el único KO mayor heredado sigue siendo la resincronización de permisos/roles persistidos.

## Actualización A.6 (jsconfig y campos numéricos)

- `jsconfig.json` queda limpio en VS Code al añadir `ignoreDeprecations: "6.0"` y mantener `baseUrl` por dependencia real de `@/*` y `ziggy-js`.
- La auditoría de campos confirma que referencias operativas/fiscales siguen siendo texto: `numero_pedido`, `numero_factura`, `numero_factura_ccp`, `numero_trabajo_operativo`, `numero_aviso`, `codigo_estacion`, `codigo_postal`, `cif` y teléfonos.
- Correcciones aplicadas en frontend:
    - `numero_trabajo` se envía como entero en `Trabajos/Form.jsx` y `TrabajosExcelView.jsx`.
    - `cantidad` de líneas de pedido queda en `step=1` y se valida como entero por defecto en el slice operativo revisado.
    - `unidades_solicitadas` queda en `step=1` y se valida como entero por defecto en formularios manuales / Excel revisados.
    - importes rápidos de pedidos usan `step=0.01`.
    - edición inline de facturas separa `total` decimal de `orden_factura` entero.
- `factura_items.unidades_facturadas` se mantiene decimal porque `FacturaTest` sigue cubriendo facturación parcial válida con valores `0.6`, `0.5`, `0.4` y `0.75`.
- Validación del slice A.6-R: batería focalizada PASS (`32` tests) y `npm run build` PASS; los 6 fallos ajenos detectados en suite completa se cierran en A.7 sin reabrir la decisión funcional de numeración.
- Matriz funcional detallada: `docs/02_CLIENTE/MATRIZ_TIPOS_NUMERICOS_CODIGOS_A6-R_2026-05-18.md`.

## Actualización A.7 (cierre de suite completa antes de Fase B)

- Diagnóstico exacto de fallos residuales:
    - `PasswordConfirmationTest` y `RegistrationTest`: tests heredados contra un portal ya endurecido sin registro público ni confirmación pública de contraseña; el desajuste real estaba en el `Route::fallback` web para invitados GET.
    - `ClosureDashboardTest` y `EstacionesTest`: expectativas antiguas que seguían tratando a `admin` como rol funcional en superficies ya reservadas a dirección/perfiles autorizados.
    - `ErrorPagesTest`: el `404` seguía esperando `accessDenied.backHomeLabel`, aunque esa prop solo corresponde al `403`; la salida clara de `404` ya se resuelve por `homeUrl` + `Error.jsx`.
- Corrección aplicada:
    - `routes/web.php`: fallback web alineado con el portal protegido; invitados en GET desconocido van a login, autenticados en GET desconocido reciben 404 corporativo, no-GET desconocido mantiene 404.
    - `tests/Feature/Auth/PasswordConfirmationTest.php`, `tests/Feature/ClosureDashboardTest.php`, `tests/Feature/ErrorPagesTest.php`, `tests/Feature/EstacionesTest.php`: expectativas realineadas con la política funcional vigente.
    - `RegistrationTest` queda validado sin activar registro público: la ruta sigue ausente y el invitado vuelve a login.
- Validación A.7:
    - 5 grupos aislados: PASS (`20` tests).
    - `php artisan test`: PASS (`218` tests, `1286` assertions).
    - `npm run build`: PASS.
- Estado global tras A.7:
    - KO críticos: 0.
    - KO mayores pendientes: solo resincronización de permisos/roles persistidos en la base local actual, fuera de este cierre de código.
    - Fase B ya no queda bloqueada por la suite completa.

## Actualización A.8 (modularización controlada de rutas web antes de Fase B)

- Motivo: `routes/web.php` mezclaba demasiados bloques funcionales y favorecía falsos positivos de editor en helpers dinámicos/closures; la fase se limita a ordenar arquitectura de rutas.
- Nueva estructura:
    - `routes/web.php` queda como índice.
    - `routes/web/public.php`, `contexto.php`, `operativa.php`, `maestros.php`, `direccion.php`, `admin.php`, `soporte.php`, `comunicaciones.php`, `importaciones.php`, `estado.php` y `fallback.php`.
- Garantía funcional:
    - No cambian URLs, names, controladores, permisos, middlewares funcionales, props Inertia ni lógica de negocio.
    - `auth.php` se mantiene cargado desde `routes/web.php`.
    - `fallback.php` queda al final.
- Fallback:
    - Se mueve a `routes/web/fallback.php`.
    - Se conserva la política A.7: invitado + GET desconocido a login, autenticado + GET desconocido a 404 corporativo, no-GET desconocido a 404.
    - `auth()->check()` se sustituye por `Auth::check()` para evitar el falso positivo de Intelephense sin cambiar comportamiento.
- Closures auxiliares:
    - Las closures grandes de facturación se mueven junto a Facturas en `routes/web/operativa.php`.
    - No se convierten en servicios para no introducir refactor funcional en una fase de orden.
- Comparación de rutas:
    - `storage/app/route-list-before-a8.txt` y `storage/app/route-list-after-a8.txt` quedan sin diferencias.
    - Rutas desaparecidas: 0.
    - Names cambiados: 0.
    - Salida `route:list`: 137 líneas y 133 rutas mostradas antes/después.
- Validación:
    - `php -l routes/web.php`: PASS.
    - `php -l routes/web/*.php`: PASS.
    - Tests focalizados de fallback/auth/accesos/admin: PASS.
    - Tests focalizados de módulos principales: PASS.
    - `php artisan test`: PASS (`218` tests, `1286` assertions).
    - `npm run build`: PASS, con warning no bloqueante de timing del plugin `laravel`.
- Diagnóstico editor:
    - `routes/web.php` ya no contiene closures ni helpers dinámicos.
    - `routes/web/fallback.php` usa facade explícita `Auth`.
    - No hay CLI de Intelephense disponible en este entorno; cualquier aviso residual esperable quedaría limitado a closures legítimas de Laravel en `routes/web/operativa.php`.
- Estado global tras A.8:
    - KO críticos: 0.
    - KO mayores pendientes: se mantiene solo la resincronización de permisos/roles persistidos de la base local actual.
    - No se tocó base de datos, seeders, importaciones, exportaciones, producción, ramas ni Git/GitHub; no se hizo commit.

## Condición para declarar bloque listo

- Mantener KO críticos en 0.
- Cerrar los KO mayores pendientes con validación manual FD26 y actualizar este registro a `validado`.
- Para A.3, el admin técnico ya queda alineado en ejecución por código; aún así hay que resincronizar la base local actual si se quiere que los usuarios persistidos y las tablas de permisos reflejen exactamente la matriz nueva.

**BLOQUE TÉCNICO LISTO / DATOS PERSISTIDOS PENDIENTES** — Código, rutas, UI, runtime efectivo y tests de Fase A.3 quedaron validados el 2026-05-18. KO críticos: 0. KO mayores pendientes: 1, limitado a la resincronización de permisos/roles en la base local actual.

## Fase B.1 - Incidencias de calidad de datos tras importacion real

**Fecha ejecucion:** 2026-05-19  
**Documento de evidencia:** `docs/02_CLIENTE/FASE_B1_IMPORTACION_REAL_EXCEL_2026-05-18.md`  
**Estado:** KO funcional critico 0; avisos de calidad de datos pendientes de revision en B.2/negocio.

La base local `abaco_ciete` queda cargada y reconciliada contra P1-12, pero la integridad extendida detecta datos de origen que no deben corregirse masivamente sin autorizacion:

| Evidencia                                      |       Conteo | Clasificacion                    | Accion recomendada                                                                      |
| ---------------------------------------------- | -----------: | -------------------------------- | --------------------------------------------------------------------------------------- |
| Trabajos sin estacion                          |        1.356 | Aviso de calidad de fuente       | Revisar en B.2 si son trabajos historicos sin EESS o si falta mapeo/codigo de estacion. |
| Pedidos con importes negativos                 |            2 | KO de dato a validar con negocio | Confirmar si son abonos/regularizaciones o errores de origen.                           |
| `pedido_items` con importes negativos          |            2 | KO de dato a validar con negocio | Mismo origen que los pedidos negativos MOEVE.                                           |
| Fechas imposibles en trabajos                  |            6 | KO de dato de origen             | Revisar anos `0202`, `0205` y `2525` detectados en REPSOL.                              |
| Fechas imposibles en pedidos                   |            4 | KO de dato de origen             | Revisar fechas heredadas desde trabajo/pedido REPSOL.                                   |
| Facturas historicas MOEVE sin items enlazables | 1.183 avisos | No inventar                      | Mantener sin enlace hasta decision expresa de CIETE.                                    |
| Trabajos con importe sin numero de pedido      |   989 avisos | Decision funcional               | Confirmar si deben quedar como trabajo sin pedido o si existe otra fuente documental.   |

Comprobaciones sin KO:

- 0 trabajos sin contexto.
- 0 pedidos sin trabajo.
- 0 `pedido_items` sin pedido.
- 0 facturas sin items.
- 0 `factura_items` sin factura o sin `pedido_item`.
- 0 estaciones sin contexto.
- 0 contratos/tarifarios/lineas huerfanos.
- 0 codigos de estacion duplicados dentro del mismo contexto.
- 0 estados desconocidos en trabajos, pedidos o facturas contra los enums reales.
- 0 mezclas de contexto entre trabajo, pedido, factura y factura items.

Estos avisos no bloquean B.1 porque la importacion real esta cargada, los conteos clave P1-12 coinciden y no hay errores fatales ni huerfanos criticos. Deben usarse como entrada de B.2 y de la revision funcional con CIETE.

## Fase B.1.1 - KO/Avisos post-importación real (auditoría de cobertura)

**Fecha:** 2026-05-19  
**Documento de soporte:** `docs/02_CLIENTE/B1_1_AJUSTES_VISUALES_Y_AUDITORIA_COBERTURA_DATOS_2026-05-19.md`

Resumen de hallazgos reales (sin reimportación y sin corrección masiva de datos):

| Evidencia                                                             |                              Conteo | Clasificación B.1.1                        | Estado                                       |
| --------------------------------------------------------------------- | ----------------------------------: | ------------------------------------------ | -------------------------------------------- |
| `unknown_columns` en hojas auxiliares/no operativas                   |                                  36 | aviso de fuente                            | pendiente CIETE                              |
| `historical_invoice_without_items`                                    |                               1.183 | decisión CIETE (no inventar enlaces)       | pendiente CIETE                              |
| `work_amount_without_order`                                           |                                 989 | decisión funcional CIETE                   | pendiente CIETE                              |
| `invoice_number_normalized` (`SIN_NUMERO-*`)                          | 969 avisos / 967 facturas afectadas | KO menor técnico                           | pendiente revisión funcional                 |
| trabajos sin estación                                                 |                               1.356 | aviso de calidad de fuente                 | pendiente revisión funcional                 |
| pedidos con importe negativo                                          |                                   2 | KO de dato (origen)                        | pendiente validación negocio                 |
| `pedido_items` con importe negativo                                   |                                   2 | KO de dato (origen)                        | pendiente validación negocio                 |
| trabajos con fechas imposibles                                        |                                   6 | KO de dato (origen)                        | pendiente validación negocio                 |
| números operativos con decimal (`numero_trabajo_operativo` con punto) |                                 459 | aviso de fuente / revisión de presentación | mitigado en UI, pendiente criterio funcional |

Ajuste visual asociado cerrado:

- El solapamiento de columna `Nº` en vista moderna de trabajos queda corregido con ancho controlado, truncado y `title`.
- El valor `865.66666666666697` **no se modifica** en base de datos; solo se limita visualmente.

Estado de severidad tras B.1.1:

- KO críticos pendientes: 0
- KO mayores nuevos en B.1.1: 0
- KO menores / avisos / decisiones funcionales: abiertos para B.2 y revisión con CIETE

## Fase B.1.2 - KO/Avisos post-revisión global visual y preparación B2

**Fecha:** 2026-05-19  
**Documento de soporte:** `docs/02_CLIENTE/B1_2_REVISION_GLOBAL_TABLAS_CASOS_DEMO_Y_VERSION_2026-05-19.md`

| Evidencia                                                              |                                                            Conteo | Clasificación B.1.2    | Estado                             |
| ---------------------------------------------------------------------- | ----------------------------------------------------------------: | ---------------------- | ---------------------------------- |
| Facturas moderno: `Nº FACTURA` largo invadía `FECHA`                   | 1 caso visual reproducible con múltiples registros `SIN_NUMERO-*` | KO menor UX            | corregido                          |
| Facturas Excel: celdas largas sin truncado consistente en modo lectura |                                                     patrón visual | KO menor UX            | corregido                          |
| Pedidos moderno: riesgo de desborde visual en `Nº pedido` largo        |                                                 patrón preventivo | aviso UX               | mitigado                           |
| Conflicto por celda: faltaba diferenciar "modificado recientemente"    |                                                               n/a | mejora funcional menor | corregido                          |
| Datos reales OTROS inexistentes para validación diaria                 |                                contexto completo sin datos reales | aviso de cobertura     | mitigado con muestra reversible B2 |

Incidencias técnicas de validación (no funcionales sobre `abaco_ciete`):

- Durante la ejecución inicial de tests por filtros en paralelo se produjo colisión de migraciones en `abaco_ciete_testing`.
- Acción aplicada: saneamiento exclusivo de DB de pruebas (`DROP/CREATE abaco_ciete_testing`) y repetición de filtros fallidos.
- Resultado final de batería objetivo B.1.2: PASS.

Estado de severidad tras B.1.2:

- KO críticos pendientes: 0
- KO mayores funcionales nuevos: 0
- KO menores UX: cerrados en esta fase
- Avisos de fuente/datos heredados (B.1/B.1.1): continúan abiertos para B.2 y decisión CIETE

## Fase B.2 - Resultado de validación funcional completa post-importación

**Fecha:** 2026-05-19  
**Documento de soporte:** `docs/02_CLIENTE/FASE_B2_VALIDACION_FUNCIONAL_COMPLETA_POST_IMPORTACION_2026-05-19.md`

| Evidencia                                                                                                        | Clasificación                    | Estado                             |
| ---------------------------------------------------------------------------------------------------------------- | -------------------------------- | ---------------------------------- |
| Suite automática completa (`artisan test`) PASS                                                                  | validación técnica               | cerrado                            |
| Build frontend (`npm run build`) PASS                                                                            | validación técnica               | cerrado                            |
| Batería focalizada B.2 PASS                                                                                      | validación técnica               | cerrado                            |
| Roles/accesos/permisos/contexto validados por tests dedicados                                                    | control funcional                | cerrado                            |
| Casos B2 OTROS (`980001`..`980004`) presentes y trazables                                                        | cobertura de escenarios          | cerrado                            |
| Validación visual manual directa por navegador (desktop/portátil/móvil) no ejecutada en esta sesión CLI          | KO menor de proceso              | pendiente                          |
| Avisos de calidad de datos heredados (negativos, fechas imposibles, `SIN_NUMERO-*`, `work_amount_without_order`) | aviso de fuente / decisión CIETE | pendiente B.2 funcional de negocio |

Resumen severidad tras B.2:

- KO críticos: 0
- KO mayores: 0
- KO menores abiertos: cierre visual manual en navegador + avisos heredados de calidad de fuente

Conclusión B.2:

- El ERP queda validado técnicamente para operación local post-importación real, sin bloqueos críticos/mayores nuevos.
- Se recomienda cierre manual visual final por roles y viewports antes de declarar estado demo definitivo.

## Fase B.2.1 — Pasada visual manual final y cierre v2.1.0

**Fecha:** 2026-05-19  
**Documento de soporte:** `docs/02_CLIENTE/FASE_B2_VALIDACION_FUNCIONAL_COMPLETA_POST_IMPORTACION_2026-05-19.md` (sección 12 — B.2.1)

| Evidencia                                                                                                                                                      | Clasificación             | Estado                                                                                                               |
| -------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------- | -------------------------------------------------------------------------------------------------------------------- |
| Paginación Trabajos/Pedidos/Facturas Excel no renderizaba (frontend leía `meta.pagination.*` en lugar de `meta.*` plano)                                       | KO mayor                  | **RESUELTO** — `Trabajos/Index.jsx`, `Pedidos/Index.jsx`, `Facturas/Index.jsx` corregidos; build PASS; 33 tests PASS |
| Badge "VERSIÓN 2.0" en card hero (es.js/en.js `badgeVersion` line 198) — debería ser v2.1.0                                                                    | KO menor cosmético        | **RESUELTO en B.2.2** — `es.js`/`en.js` `badgeVersion` → `'v2.1.0'`; build PASS                                      |
| Doble sesión (conflicto `updated_at` stale) no simulada manualmente                                                                                            | KO menor de proceso       | cubierto por test automático `patch_field_returns_conflict_when_updated_at_is_stale`                                 |
| Viewport móvil/tablet no testeable por herramienta                                                                                                             | KO menor de proceso       | validado por análisis CSS — `MobileSidebarDrawer` implementado, layout responsive correcto                           |
| Login, Inicio, Panel admin, Estado del sistema                                                                                                                 | validación visual desktop | PASS                                                                                                                 |
| Trabajos Excel (moeve: 6685 trabajos, Pág. 1/669) + Siguiente (Pág. 2)                                                                                         | validación visual desktop | PASS                                                                                                                 |
| Pedidos Excel (moeve: Pág. 1/615)                                                                                                                              | validación visual desktop | PASS                                                                                                                 |
| Facturas (director: 1 registro OTROS CLIENTES)                                                                                                                 | validación visual desktop | PASS                                                                                                                 |
| Panel cierre + KPIs, Maestros + diagnóstico (director)                                                                                                         | validación visual desktop | PASS                                                                                                                 |
| Importaciones, Soporte/panel (admin)                                                                                                                           | validación visual desktop | PASS                                                                                                                 |
| Trabajos moderno (admin, 4 filas SOLO LECTURA)                                                                                                                 | validación visual desktop | PASS                                                                                                                 |
| Error 403, Error 404                                                                                                                                           | validación visual desktop | PASS                                                                                                                 |
| Roles: admin-técnico (sin acceso operativo), ejecucion-moeve (contexto aislado), director (panel+maestros), contable (403 en /trabajos, solo Pedidos+Facturas) | aislamiento de roles      | PASS                                                                                                                 |

Resumen severidad tras B.2.1:

- KO críticos: 0
- KO mayores: 0 (el único KO mayor encontrado fue corregido)
- KO menores: 2 (badge versión cosmético, doble sesión por test automático)

**ESTADO DECLARADO: `ERP CIETE v2.1.0 — validada con KOs menores`**

## Fase B.2.2 — Paginación rápida, mensajes de inicio y badge corregido (2026-05-19)

**Documento de soporte:** `docs/02_CLIENTE/FASE_B2_VALIDACION_FUNCIONAL_COMPLETA_POST_IMPORTACION_2026-05-19.md` (sección 12 — B.2.2)

| Evidencia                                                                                                  | Clasificación              | Estado                                                                                                                                                                      |
| ---------------------------------------------------------------------------------------------------------- | -------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Badge "VERSIÓN 2.0" cosmético en card hero                                                                 | KO menor cosmético (B.2.1) | **RESUELTO** — `badgeVersion` en `es.js`/`en.js` → `'v2.1.0'`; build PASS                                                                                                   |
| Paginación en listados solo tiene Anterior/Siguiente — sin acceso a primera/última página ni "Ir a página" | UX mejora                  | **IMPLEMENTADO** — componente `PaginationControls` creado; aplicado en 14 archivos (Trabajos, Pedidos, Facturas, Clientes, Estaciones, Maestros, Soporte, AuditLog, Cierre) |
| `home_notices` tabla sin migrar, seeder con mensajes genéricos                                             | Funcionalidad de inicio    | **COMPLETADO** — migración ejecutada, seeder actualizado con 10 mensajes v2.1.0 bilingües, 4 tests PASS                                                                     |

Resumen severidad tras B.2.2:

- KO críticos: 0
- KO mayores: 0
- KO menores: 1 (doble sesión manual — cubierto por test automático)

**ESTADO DECLARADO: `ERP CIETE v2.1.0 — validada`**
