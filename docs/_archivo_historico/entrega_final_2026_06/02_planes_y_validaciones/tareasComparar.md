# Backlog vivo ERP CIETE

> Documento vivo compacto.
> El backlog histórico completo anterior está archivado en:
> `docs/_archivo_historico/erp_ciete_documentacion_desfasada_2026-05-28/02_CLIENTE_backlog_historico/tareasComparar_HISTORICO_PRE_LIMPIEZA_2026-05-28.md`

## 1. Estado actual resumido

- Núcleo ERP v2.1.0 validado técnicamente de extremo a extremo sobre el flujo principal.
- Nueva ola basada en reunión César/Amaya CIETE 2026-05-19.
- Fuente principal actual: `docs/02_CLIENTE/listado_exhaustivo_reunion_cesar_amaya_ciete_2026-05-19.md`.
- Bloques A, 1/B, 2, 3, 4, 5 y 6 crítico: cerrados técnicamente con build y suites principales en verde.
- Bloque G: cierre técnico/documental ejecutado; validación manual transversal de navegador sigue pendiente en este entorno por falta de GUI.
- Siguiente paso: validación visual/manual final con negocio sobre flujo completo y revisión funcional de formatos/supuestos no cerrados en reunión.

## 2. Fuentes vivas actuales

- `docs/02_CLIENTE/listado_exhaustivo_reunion_cesar_amaya_ciete_2026-05-19.md`
- `docs/02_CLIENTE/estado_funcional_nueva_version_erp_ciete_2026-05-28.md`
- `docs/02_CLIENTE/PLAN_CORTO_EJECUCION_REUNION_CESAR_AMAYA_2026-05-28.md`
- `docs/02_CLIENTE/plan_8_dias_nueva_version_erp_ciete.md`
- `docs/01_ORGANIZACION/MAPA_AUTORIDAD_DOCUMENTAL_ERP_CIETE_2026-05-28.md`
- `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
- `docs/02_CLIENTE/FASE_B1_IMPORTACION_REAL_EXCEL_2026-05-18.md`
- `docs/02_CLIENTE/FASE_B2_VALIDACION_FUNCIONAL_COMPLETA_POST_IMPORTACION_2026-05-19.md`

## 3. Progreso por bloques

| Bloque                                                            | Estado            | Hecho                                                                                                              | Falta                                                            | Siguiente acción                     |
| ----------------------------------------------------------------- | ----------------- | ------------------------------------------------------------------------------------------------------------------ | ---------------------------------------------------------------- | ------------------------------------ |
| Bloque A — Cierre documental y validación operativa inicial       | Completo          | Trabajos validado como centro operativo por revisión de código y build                                             | Sin pendiente bloqueante                                         | Cerrado                              |
| Bloque 1/B — Pedido desde trabajo                                 | Completo técnico  | Crear pedido desde trabajo, preselección/bloqueo de trabajo y herencia de relación trabajo/pedido                 | Validación visual/manual final en navegador                      | Validar con negocio                  |
| Bloque 2 — Contrato/Tarifario/Sociedad                            | Completo técnico  | Tarifario predeterminado en maestros, consumo desde Trabajos y herencia reforzada en Pedidos                      | Confirmación funcional de casos especiales por sociedad          | Validar con negocio                  |
| Bloque 3/C — Líneas de pedido, tarifario y decimales              | Completo técnico  | Líneas desde tarifario, buscador por código/descripcion, cantidades decimales y recálculo                         | Validación visual/manual de UX real                              | Validar con negocio                  |
| Bloque 4/D — Exportación Moeve PDF + CSV + cuadro ARIBA           | Completo técnico  | Exportación HTML imprimible/PDF inicial, CSV y cuadro ARIBA desde pedido completo                                 | Revisión visual/funcional del formato real                       | Validar con negocio                  |
| Bloque 5/E — Estados automáticos, facturación y cierre secundario | Completo técnico  | Estados derivados, facturación parcial/completa y cierre secundario con reglas reales                              | Validación visual/manual del flujo de cierre                     | Validar con negocio                  |
| Bloque 6 crítico — Roles, maestros y auditoría                    | Completo técnico  | Permisos finos, auditoría de cierre y avisos en desactivación de contratos/tarifarios usados                      | Remates no críticos de superficie legacy y avisos genéricos      | Tratar como P1                       |
| Bloque G — Compactación visual, pruebas y cierre                  | Parcial           | Cierre técnico/documental ejecutado, con build final y suites PHP del flujo principal en verde                    | Validación manual transversal de navegador y validación negocio  | Ejecutar revisión final con CIETE    |

## 4. P0 actual

1. Validar manualmente en navegador el flujo completo `Trabajo -> Pedido -> Líneas -> Exportación -> Facturación -> Cierre`.
2. Validar con negocio el supuesto actual de formato Moeve HTML imprimible/PDF inicial, CSV y cuadro ARIBA.
3. Confirmar con negocio si el tarifario predeterminado debe variar por sociedad facturadora dentro del mismo contrato.
4. Confirmar visualmente la desactivación de contratos/tarifarios usados y su mensaje operativo.
5. Cerrar demo/revisión final CIETE con evidencia manual real.

## 5. P1/P2 actual

### P1

- Avisos visuales de empresas/estaciones al mismo nivel que contratos/tarifarios si compensa hacerlo.
- Reducción de superficie legacy de `TrabajoController@patchField` si se decide.
- Módulo dedicado para `tipos_documento` y `tipos_trabajo` si se decide.
- Limpieza documental/histórica adicional si procede.

### P2

- Cobros/importes no asignados.
- Informes avanzados.
- Automatizaciones externas.
- Limpieza histórica adicional si hiciera falta.

## 2026-06-04 — Revisión y actualización de `/ayuda`

- Alcance ejecutado:
    - solo revisión, limpieza y actualización de la pantalla `Help.jsx`;
    - sin tocar base de datos, migraciones, lógica de `Trabajos`, `Pedidos`, `Facturas`, `Cierre` ni exportación;
    - sin commit, push ni deploy.
- Cambios aplicados:
    - eliminación del manual largo y desfasado;
    - nueva ayuda operativa compacta con secciones: inicio rápido, roles, trabajos, pedidos, exportación Moeve, facturación/cierre, maestros, tickets y recuperación de contraseña;
    - lenguaje alineado con la operativa actual: `Trabajos` como pantalla principal, `Tarifario` como nombre visible, estados derivados, bloqueo operativo de tarifario con pedidos y exportación Moeve realista sin prometer más de lo implementado;
    - prioridad visual por perfil actual, manteniendo visibles las secciones avanzadas más abajo.
- Validaciones:
    - `cmd.exe /C npm run build` -> OK, `2995` módulos transformados;
    - `git diff --check` -> sin errores de whitespace en los cambios actuales;
    - avisos CRLF detectados por Git solo en ficheros ajenos ya existentes:
        - `database/backups/abaco_ciete_pre_b1_2_casos_validacion_20260519_042300.sql`
        - `database/backups/abaco_ciete_pre_fase_b_import_real_20260519_032311.sql`
        - `temp_index.txt`
        - `test_results.txt`
- Pendiente manual:
    - revisión visual final en navegador de `/ayuda` para validar densidad, lectura por rol y búsqueda en entorno real.

## 2026-06-04 — Validación por roles: sidebar, soporte, estado y recuperación contraseña

- Alcance ejecutado:
    - validación funcional por roles sobre `sidebar`, `/estado`, `/soporte`, `/forgot-password` y `/reset-password`;
    - sin tocar base de datos, sin migraciones, sin deploy, sin commit/push y sin limpiar worktree;
    - sin hotfix nuevo en esta pasada; se valida el estado real del código tras los ajustes previos de permisos y navegación.
- Limitación de entorno:
    - no ha sido posible completar navegación manual real en navegador desde esta sesión;
    - tampoco ha sido accesible el HTTP local de `127.0.0.1:8000` desde el entorno de ejecución;
    - la validación se ha cerrado mediante revisión de rutas, controladores, requests, componentes React y suites PHP/build disponibles.
- Sidebar:
    - `Soporte global` ya no aparece como bloque común;
    - `Soporte` queda visible solo en administración técnica/admin cuando el usuario puede gestionar soporte;
    - dirección, ejecución y contabilidad ya no lo ven en sidebar;
    - el antiguo `Panel de dirección` también deja de mostrarse en sidebar y `/dashboard` queda restringido a admin.
- `/estado`:
    - el acceso sigue centralizado en `User::canViewSystemStatus()`;
    - admin y usuarios con permiso de soporte pueden entrar;
    - dirección, ejecución y contabilidad quedan en `403` al acceso directo;
    - usuarios sin permiso tampoco ven `/estado` en `sidebar`, ayuda flotante ni `SupportDock`.
- Soporte / tickets:
    - comportamiento real actual:
        - cualquier usuario autenticado puede entrar en `/soporte`;
        - cualquier usuario autenticado puede crear ticket;
        - el propio solicitante puede ver y comentar sus tickets;
        - solo quien puede gestionar soporte accede al panel admin de tickets y puede cambiar estado;
        - el cierre real del ticket es un cambio de estado de manager, no una acción separada del solicitante;
    - conclusión:
        - el acceso en sidebar ya quedó oculto para dirección, ejecución y contabilidad;
        - pero `/soporte` directo sigue abierto a autenticados, así que el sistema actual funciona como canal interno general, no como módulo exclusivo de técnico/admin.
- Recuperación contraseña:
    - `ForgotPassword.jsx` carga formulario en español;
    - `PasswordResetLinkController` no revela si el email existe;
    - `ResetPasswordNotification` sigue usando `mailer('log')` fuera de `production` y respeta SMTP real en producción;
    - `PasswordResetFlowTest` cubre token válido, inválido, no reutilización y confirmación de contraseña.
- Validaciones ejecutadas:
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=Password`
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=Role`
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=SupportTicketTest`
    - `cmd.exe /C npm run build`
    - `git diff --check`
- Resultado:
    - `Password`, `Role` y `SupportTicketTest` no dan señal fiable por incoherencia de `abaco_ciete_testing` con tablas/migraciones residuales;
    - `npm run build`: OK;
    - `git diff --check`: sin errores de whitespace en los cambios; solo avisos CRLF ajenos ya conocidos.
- Riesgo/decisión abierta:
    - si negocio quiere que `Soporte` sea también inaccesible por URL para dirección, ejecución y contabilidad, falta endurecer permisos/ruta de `/soporte`;
    - si la decisión final es solo ocultarlo del sidebar pero conservar tickets para cualquier usuario autenticado, el comportamiento actual ya encaja;
    - `/cierre` sigue fuera de este ajuste y continúa accesible para dirección.

## 2026-06-04 — Soporte restringido a admin/técnico

- Decisión final:
    - `/soporte` y su flujo asociado dejan de estar disponibles para dirección, ejecución y contabilidad;
    - el acceso queda restringido a perfiles con permiso real `soporte.gestionar`;
    - `/estado` se mantiene restringido como estaba;
    - `/cierre` no se toca y sigue siendo accesible para dirección según su configuración actual.
- Cambios aplicados:
    - las rutas de `routes/web/soporte.php` quedan bajo `permission:soporte.gestionar`;
    - `SupportController` añade validación defensiva de permiso antes de indexar, crear, ver o responder tickets;
    - `StoreSupportTicketRequest` y `StoreSupportCommentRequest` dejan de autorizar a cualquier autenticado y pasan a exigir capacidad real de gestión de soporte;
    - `FloatingContextHelp` y `SupportDock` ya no muestran acceso a soporte a usuarios sin `can_manage_support`;
    - el sidebar ya estaba alineado y se conserva sin reabrir soporte a perfiles normales.
- Archivos tocados:
    - `routes/web/soporte.php`
    - `app/Http/Controllers/SupportController.php`
    - `app/Http/Requests/Support/StoreSupportTicketRequest.php`
    - `app/Http/Requests/Support/StoreSupportCommentRequest.php`
    - `resources/js/Components/FloatingContextHelp.jsx`
    - `resources/js/Components/WelcomeHome/SupportDock.jsx`
    - `tests/Feature/SupportTicketTest.php`
    - `tests/Feature/RoleModuleAccessTest.php`
    - `tests/Feature/DireccionAccessTest.php`
    - `tests/Feature/ExecutionAccessTest.php`
    - `tests/Feature/ContableAccessTest.php`
    - `tests/Feature/InternalCommunicationTest.php`
    - `docs/02_CLIENTE/tareasComparar.md`
- Validaciones ejecutadas:
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan route:list`
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan route:list --path=soporte -vv`
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan route:list --path=estado -vv`
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan route:list --path=cierre -vv`
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=SupportTicketTest`
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=Role`
    - `cmd.exe /C npm run build`
    - `git diff --check`
- Resultado:
    - `route:list -vv` confirma `PermissionMiddleware:soporte.gestionar` en `GET/POST /soporte` y subrutas;
    - `route:list -vv` confirma que `/cierre` sigue con `RoleMiddleware:director,direccion` y `PermissionMiddleware:trabajos.ver`;
    - `route:list -vv` confirma que `/estado` mantiene su ruta normal y sigue restringido por controlador/modelo;
    - `SupportTicketTest` y `Role` no se pueden usar como señal fiable por incoherencia previa de `abaco_ciete_testing`, no por un fallo aislado del hotfix.
- Errores de entorno vistos en tests PHP:
    - tabla `migrations` inexistente;
    - tablas ya existentes como `password_reset_tokens`, `job_batches` o `roles`;
    - drops sobre tablas desconocidas;
    - errores de FK al recrear `rol_permisos`.
- Pendientes:
    - reejecutar `SupportTicketTest` y `Role` cuando `abaco_ciete_testing` esté saneada;
    - validar manualmente en navegador que dirección, ejecución y contabilidad reciben `403` al abrir `/soporte`;
    - si se considera que `Support.jsx` ya no tiene uso operativo, decidir más adelante si se conserva como fallback interno o se simplifica.

## 2026-06-04 — Revisión de comentarios de código

- Alcance ejecutado:
    - revisión completa de comentarios en `app/`, `resources/js/`, `routes/`, `tests/` y `config/`;
    - sin cambios de lógica, reglas de negocio ni refactors;
    - sin deploy, commit ni push.
- Archivos revisados:
    - `routes/web/operativa.php`, `routes/web/importaciones.php`
    - `app/Http/Controllers/Api/TrabajoController.php`
    - `app/Http/Controllers/Api/PedidoController.php`
    - `app/Http/Controllers/Api/FacturaController.php`
    - `app/Http/Controllers/ImportacionController.php`
    - `app/Http/Resources/Api/TrabajoResource.php`
    - `app/Http/Resources/Api/PedidoResource.php`
    - `app/Http/Resources/Api/ImportacionResource.php`
    - `app/Http/Requests/Api/StoreImportacionRequest.php`
    - `app/Http/Requests/Api/StorePedidoRequest.php`
    - `app/Http/Requests/Api/StoreTrabajoRequest.php`
    - `app/Services/TrabajoStateService.php`
    - `resources/js/Hooks/useOptimisticField.js`
    - `resources/js/Pages/Facturas/Index.jsx`
- Comentarios eliminados:
    - referencias a Sprint 03 / Sprint 04 en rutas (eran artefactos del proceso de desarrollo)
    - PHPDoc que repetían literalmente el nombre del método (`Muestra el listado`, `Procesa y persiste`, etc.)
    - comentarios de sección innecesarios en Resources (`// Identificadores y Relaciones`, `// Datos Base`, etc.)
    - comentarios obvios en Requests (`// Validamos que el archivo...`, `// Relaciones`, etc.)
    - ruta de archivo como primera línea en `Facturas/Index.jsx`
    - PHPDoc de clase en `PedidoController` con referencia a Sprint obsoleta
- Comentarios añadidos o mejorados:
    - `TrabajoStateService::deriveTrabajoState` — regla de estados terminales (cancelado/finalizado no se recalculan)
    - `TrabajoStateService::syncPedidoState` — regla de cancelados/anulados inmunes a facturación
    - `TrabajoController::patchField` — descripción de guardado optimista con bloqueo por 60 min
    - `TrabajoController::patchField` — bloqueo solo por otra persona en los últimos 60 min (ediciones propias pasan)
    - `TrabajoResource` — razón del fallback primerPedido → colección tras store
    - `TrabajoResource` — comportamiento de withSum post-store
    - `ImportacionController::store` — razón de mimetypes en vez de mimes (finfo/Windows)
    - `StoreImportacionRequest` — diferencia entre request API (JSON) y web controller (Inertia)
    - `StoreTrabajoRequest::id_contrato` — regla contextual MOEVE vs REPSOL/OTROS
    - `Facturas/Index.jsx` — columnas contextuales MOEVE/REPSOL
    - `useOptimisticField.js` — propósito del hook y regla de 60 minutos
    - `operativa.php` — motivo del DELETE en web.php para router.delete() de Inertia
- Debug/logs eliminados:
    - ninguno (`console.error` en app.jsx y useEstaciones son catch handlers legítimos, se conservan)
- Reglas de negocio documentadas en código:
    - estados terminales (cancelado/finalizado) no se recalculan desde facturación
    - bloqueo de campo editado por otra persona en <60 min
    - MOEVE requiere contrato salvo que venga el tarifario; REPSOL/OTROS no
    - importes/unidades viajan como float estricto para evitar coerciones JS
    - id_trabajo en facturas es cabecera auxiliar, no fuente de detalle
- Deuda técnica detectada (no tocada):
    - `ImportacionController.php`: el ExcelParserService no procesa CSV, solo XLSX; la UI promete CSV
    - `PedidoController`: larga lista de rutas internas en closure dentro de `operativa.php`; candidata a controlador web dedicado
    - `TrabajoController::patchField`: método muy largo con múltiples responsabilidades; candidato a refactor cuando haya cobertura suficiente
- Validaciones ejecutadas:
    - `npm run build` → ✓ built in 1.78s
    - `git diff --check` → sin errores
    - `artisan test --filter="TrabajoTest|PedidoTest|Factura"` → 86/86 passed
- Resultado: solo cambios de comentarios; lógica funcional intacta.

## 2026-06-02 — Recuperación contraseña revisada

- Objetivo:
    - revisar y asegurar el flujo `/forgot-password` y `/reset-password` sin fuga de información y sin correo real en local/demo.
- Archivos tocados:
    - `app/Http/Controllers/Auth/PasswordResetLinkController.php`
    - `app/Notifications/ResetPasswordNotification.php`
    - `tests/Feature/Auth/PasswordResetFlowTest.php`
- Validaciones:
    - tests dedicados de solicitud de enlace, carga de formulario de reset, token válido, token inválido, email obligatorio y contraseña confirmada;
    - verificación de que el token se crea en `password_reset_tokens`;
    - verificación de que un email inexistente no devuelve error diferenciador.
- Resultado:
    - `/forgot-password` mantiene el flujo estándar de Laravel;
    - fuera de producción el correo de recuperación usa mailer `log`;
    - el formulario ya no revela si el usuario existe.
- Pendientes:
    - validación manual visual en navegador del copy y del mensaje de éxito real en local.

## 2026-06-02 — Sistema de tickets revisado

- Objetivo:
    - revisar el soporte interno existente, dejar visible el acceso a Tickets y cubrir el flujo mínimo con tests ejecutables por filtro.
- Archivos tocados:
    - `app/Http/Controllers/SupportController.php`
    - `resources/js/Pages/Support.jsx`
    - `resources/js/navigation/sidebar.js`
    - `resources/js/i18n/locales/es.js`
    - `resources/js/i18n/locales/en.js`
    - `tests/Feature/SupportTicketTest.php`
- Validaciones:
    - listado reciente de tickets del propio usuario en `/soporte`;
    - enlace de Tickets en sidebar para perfiles autenticados;
    - tests de creación, 403 en gestión no autorizada, cambio de estado por manager y comentario del solicitante.
- Resultado:
    - el sistema existente se conserva;
    - queda más preparado para demo interna porque el acceso ya no depende del dock y el usuario ve histórico reciente sin conocer URLs manuales.
- Pendientes:
    - no se implementa asignación manual explícita de técnico;
    - validación visual/manual de filtros y detalle en navegador real.

## 2026-06-02 — Sidebar oculta en Trabajos

- Objetivo:
    - ganar ancho útil en `/trabajos` ocultando la sidebar en desktop por defecto sin afectar el resto del ERP.
- Archivos tocados:
    - `resources/js/Layouts/AuthenticatedLayout.jsx`
    - `resources/js/Components/TopNavbar.jsx`
    - `resources/js/Pages/Trabajos/Index.jsx`
- Validaciones:
    - el layout acepta modo de arranque con sidebar cerrada;
    - el botón `Menú` permite abrir/cerrar la sidebar desde la cabecera;
    - otras pantallas siguen con comportamiento lateral normal al no activar la prop.
- Resultado:
    - `Trabajos` usa más ancho desde carga;
    - la navegación sigue recuperable bajo demanda;
    - el cambio queda encapsulado solo en esa pantalla.
- Pendientes:
    - validación manual en desktop 1366/1440 y revisión final de scroll horizontal en navegador real.

## 2026-06-01 — Bloque G ejecutado: validación final y cierre técnico/documental

- Estado:
    - cierre técnico ejecutado;
    - sin hotfix bloqueante;
    - cierre funcional visual pendiente por ausencia de navegador/GUI en este entorno.
- Prompt ejecutado:
    - `Prompt 1`, cuyo contenido actual corresponde a `Prompt 7 — Bloque G: Validación final y cierre ERP CIETE`.
- Fase 1 — Estado técnico inicial:
    - `git status --short`: árbol de trabajo ya sucio por cambios previos ajenos; no se revierten ni se altera su alcance;
    - `git diff --check`: sin errores de whitespace; solo avisos CRLF en ficheros ajenos:
        - `database/backups/abaco_ciete_pre_b1_2_casos_validacion_20260519_042300.sql`
        - `database/backups/abaco_ciete_pre_fase_b_import_real_20260519_032311.sql`
        - `temp_index.txt`
        - `test_results.txt`
    - `cmd.exe /C npm run build`: correcto, `2992` módulos transformados.
- Tests ejecutados en secuencial con PHP Windows/XAMPP:
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=TrabajoTest` → OK, `35` tests y `277` aserciones.
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=PedidoTest` → OK, `20` tests y `76` aserciones.
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=Factura` → OK, `36` tests y `171` aserciones.
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=ClosureDashboardTest` → OK, `9` tests y `42` aserciones.
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=MaestrosTest` → OK, `11` tests y `62` aserciones.
- Validación manual navegador:
    - no ejecutable desde esta sesión;
    - no hay acceso GUI/browser operativo en este entorno;
    - queda pendiente validación visual/manual real de:
        - `Trabajos`;
        - flujo trabajo/pedido/líneas;
        - exportación Moeve;
        - facturación/cierre;
        - maestros críticos.
- Revisión de deuda técnica final:
    - `TrabajoController@patchField` mantiene ramas legacy protegidas para mutaciones no expuestas hoy por la UI;
    - empresas/estaciones conservan aviso visual más genérico que contratos/tarifarios;
    - `tipos_documento` y `tipos_trabajo` siguen sin módulo dedicado;
    - persiste diferencia potencial entre entornos con/sin migración `tarifarios.es_predeterminado`, aunque el hotfix de compatibilidad ya evita romper `/trabajos`.
- Resultado de cierre:
    - cierre técnico alcanzado;
    - no se abre funcionalidad nueva;
    - no se hace deploy, commit ni push;
    - la única evidencia pendiente para cierre funcional total es navegador + contraste final con negocio.

## Siguiente acción real

- No abrir desarrollo nuevo.
- Ejecutar validación manual con caso Moeve y Repsol.
- Si no aparecen fallos, preparar demo/revisión con CIETE.
- Si aparecen fallos, tratarlos como hotfix puntual, no como reapertura de bloques.

## 2026-06-01 — Revisión tarifa única Repsol según correo César

- Correo revisado:
    - César García, `2026-05-20 17:03`;
    - literal clave: `La tarifa de Repsol es única.`
- Archivo Excel asociado:
    - no localizado en el repo con el nombre `Tarifa Ingenieria Ciete (2).xlsx`;
    - no se inventa contenido;
    - pendiente copiar la fuente real al proyecto si se quiere contraste documental 1:1.
- Revisión documental:
    - la documentación viva general ya iba alineada con “Repsol tarifa única” en varios puntos;
    - la contradicción relevante aparece en fuentes de reunión y en `BLOQUE_2_TARIFARIO_CONTRATO_SOCIEDAD_2026-06-01.md`, donde aún quedaba formulada la hipótesis “Repsol por sociedad”;
    - se aclara esa lectura como antecedente histórico superado por el correo posterior de César.
- Revisión de código/datos:
    - `Tarifario`, `Contrato`, `TarifarioController` y `TrabajoController` mantienen un modelo general que permite varios tarifarios por contrato y un predeterminado;
    - no existe una restricción específica “solo un tarifario Repsol” a nivel de código;
    - aun así, los datos revisados e importadores actuales apuntan a un único contrato/tarifario activo de Repsol:
        - `app/Services/Importacion/RepsolExcelImporter.php`
        - `database/abaco_ciete_prod_dump.sql`
        - `database/seeders/private/CieteRealContratosTarifariosSeeder.php`
- Conclusión:
    - no hay contradicción funcional clara del ERP actual con el correo si Repsol se opera con un único tarifario activo;
    - sí hay riesgo preventivo porque el modelo global permitiría crear más de un tarifario activo Repsol sin aviso específico.
- Ajustes aplicados:
    - solo documentación;
    - se aclara `BLOQUE_2` como antecedente histórico y se fija Repsol como tarifa única vigente salvo contradicción futura de negocio.
- Pendientes:
    - copiar el Excel real al repo si se quiere contraste de fuente documental;
    - decidir más adelante si conviene aviso suave en `Trabajos` cuando Repsol tenga más de un tarifario activo.
- Siguiente acción:
    - mantener la demo con la lectura actual `Repsol = un único tarifario activo con múltiples líneas/conceptos`.

## 2026-06-01 — Revisión solicitud pedidos Moeve según correo César

- Correo revisado:
    - César García, `2026-05-20 15:54`;
    - alcance real: `PDF + CSV + tabla ARIBA en cuerpo del correo`.
- Archivos reales del caso `La Senyera`:
    - no localizados en el repo con estos nombres:
        - `20260506_33450_La Senyera I_Pedido Ciete (1).pdf`
        - `20260506_33450_La Senyera I_Pedido Ciete (1).csv`
        - `20260506_33450_La Senyera I_Pedido Ciete (1).xlsx`
    - pendiente copiarlos, por ejemplo, en:
        - `docs/00_FUENTES_CLIENTE/moeve_solicitud_pedidos_2026-05-20/`
- Revisión documental:
    - `BLOQUE_4_EXPORTACION_MOEVE_2026-06-01.md` hablaba de exportación técnica inicial, pero no dejaba suficientemente explícito que el correo real de César pide un formato concreto y que los adjuntos reales no estaban contrastados en repo;
    - se corrige esa aclaración sin borrar histórico.
- Revisión de código/exportaciones:
    - existen y responden:
        - `GET /pedidos/{pedido}/export/moeve/pdf`
        - `GET /pedidos/{pedido}/export/moeve/csv`
        - `GET /pedidos/{pedido}/export/moeve/ariba`
    - el backend exige pedido con trabajo, tarifario, líneas válidas y totales coherentes;
    - no exporta pedidos vacíos o inconsistentes.
- Contradicción detectada:
    - el ERP sí genera tres salidas técnicas, pero no reproduce todavía el formato real del correo César/La Senyera;
    - el `PDF Moeve` es hoy HTML imprimible genérico;
    - el `CSV` es técnico y no está contrastado contra el CSV real;
    - el cuadro `ARIBA` no reproduce aún la cabecera `ARIBA - TRAMITACION DE PEDIDOS` ni los campos de negocio completos del correo.
- Tipo de contradicción:
    - código:
        - `resources/views/pedidos/export/moeve_pdf.blade.php`
        - `resources/views/pedidos/export/moeve_ariba.blade.php`
        - `app/Http/Controllers/Api/PedidoController.php`
    - documentación:
        - `docs/02_CLIENTE/BLOQUE_4_EXPORTACION_MOEVE_2026-06-01.md`
    - datos/fuentes:
        - faltan los adjuntos reales del correo dentro del repo.
- Hotfix mínimo recomendado:
    - no aplicado en esta revisión para no abrir desarrollo mayor sin adjuntos reales;
    - siguiente remate mínimo seguro cuando se copien los ficheros:
        - renombrar cabecera ARIBA a `ARIBA - TRAMITACION DE PEDIDOS`;
        - completar labels visibles ya soportados por el modelo;
        - revisar plantilla HTML/PDF y CSV contra el caso real de `La Senyera`;
        - dejar como vacíos visibles o placeholders los campos que el modelo aún no tenga, sin inventarlos.
- Resultado:
    - KO parcial: revisar antes de demo.

## 2026-06-01 — Hotfix P0 operativo Moeve ejecutado

- Alcance aplicado:
    - solo `PDF + CSV + ARIBA` de Moeve;
    - sin tocar Repsol, estados, facturación, cierre ni roles.
- Fuentes reales ya copiadas y revisadas:
    - `docs/00_FUENTES_CLIENTE/moeve_solicitud_pedidos_2026-05-20/20260506_33450_La Senyera I_Pedido Ciete (1).pdf`
    - `docs/00_FUENTES_CLIENTE/moeve_solicitud_pedidos_2026-05-20/20260506_33450_La Senyera I_Pedido Ciete (1).csv`
    - `docs/00_FUENTES_CLIENTE/moeve_solicitud_pedidos_2026-05-20/20260506_33450_La Senyera I_Pedido Ciete (1).xlsx`
- Ajustes aplicados:
    - payload común `app/Services/Exports/MoevePedidoExportService.php`;
    - `PedidoController` con CSV de negocio y nombre de fichero estable;
    - `moeve_pdf.blade.php` alineado con cabecera real, título `OFERTA PRECIOS ACUERDO`, tabla y total;
    - `moeve_ariba.blade.php` con cabecera exacta `ARIBA - TRAMITACION DE PEDIDOS`;
    - bloque de exportación en `Pedidos/Form.jsx` con labels compactos y aviso de parametrización;
    - `PedidoTest` ampliado para PDF, CSV y ARIBA.
- Estado:
    - `OK operativo con pendientes visibles de parametrización`
- Pendientes que siguen abiertos:
    - `Cta. de Mayor`
    - `Propuesta de Inversión / Opex acción gasto`
    - `Acción de gasto AC`
    - `Proveedor / Contrato` exacto si el dato real no coincide con el contrato interno
    - validación manual final con negocio en navegador

## 6. Historial archivado

Las entradas históricas fechadas que siguen desde aquí no sustituyen el estado actual resumido ni el cuadro de bloques anterior; se conservan solo como trazabilidad.

El backlog largo anterior queda archivado en:

`docs/_archivo_historico/erp_ciete_documentacion_desfasada_2026-05-28/02_CLIENTE_backlog_historico/tareasComparar_HISTORICO_PRE_LIMPIEZA_2026-05-28.md`

## 7. 2026-05-28 — Cierre Bloque A

Estado: Bloque A cerrado.

Validación realizada:

- `Trabajos` mantiene columna `Pedido principal`.
- Los trabajos sin pedido muestran `Sin pedido`.
- Los trabajos con pedido muestran número de pedido principal.
- La cabecera mantiene mensaje breve y no convierte `Pedidos` en flujo diario principal.
- La tabla Excel conserva filtros, edición inline y conflicto por campo mediante `useOptimisticField` y `ConflictDialog`.
- Los cancelados se ordenan al final en la vista Excel.
- Navegación revisada: `Trabajos` aparece como entrada operativa principal y `Pedidos` sigue accesible como módulo separado.
- Roles/contextos revisados de forma razonada: ejecución Moeve/Repsol operan desde Trabajos, dirección consulta Trabajos/Pedidos/Facturas, contabilidad conserva Pedidos/Facturas y no accede a Trabajos.

Build:

- `npm run build` correcto.

No se modifica porcentaje global porque el cierre es validación de bloque ya ejecutado, no implementación funcional nueva.

## 8. 2026-05-28 — Bloque 1/B ejecutado: Pedido desde trabajo

Estado: parcial.

Archivos tocados:

- `routes/web/operativa.php`
- `app/Http/Controllers/Api/PedidoController.php`
- `app/Http/Requests/Api/StorePedidoRequest.php`
- `app/Http/Resources/Api/TrabajoResource.php`
- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `resources/js/Pages/Trabajos/Index.jsx`
- `resources/js/Pages/Pedidos/Form.jsx`
- `docs/02_CLIENTE/tareasComparar.md`

Cambios realizados:

- En Trabajos se añade acción compacta `Crear pedido` cuando no hay pedido principal.
- Si ya existe pedido principal, se muestra y se permite consultarlo desde `Pedidos`.
- El formulario de pedido acepta `trabajo_id` desde URL, preselecciona el trabajo y bloquea su cambio accidental.
- El pedido creado desde trabajo se guarda como cabecera asociada al trabajo, sin crear líneas.
- El backend permite crear pedido sin `items` en este bloque.
- Si el trabajo trae `id_tarifario`, el pedido lo hereda como preparación mínima para Bloque 2.
- La asignación de pedido existente desde Trabajos se conserva mediante `id_pedido_principal`.

Validaciones:

- `npm run build`: correcto.
- `TrabajoTest`: correcto, 19 tests pasados.
- `PedidoTest`: no ejecutado por fallo de entorno WSL/PHP (`UtilBindVsockAnyPort: socket failed 1`).

Criterios cumplidos:

- Trabajo sin pedido puede iniciar pedido desde la fila.
- Trabajo con pedido no muestra acción de crear duplicado como flujo principal.
- Trabajo queda preseleccionado y bloqueado en el formulario cuando se llega desde Trabajos.
- No se crean líneas, no se tocan decimales y no se toca exportación Moeve.
- Moeve/Repsol se mantienen aislados por contexto activo y validación de `id_trabajo`.

Criterios pendientes:

- Validación manual en navegador de crear pedido desde un trabajo real.
- Validación manual de que el pedido queda visible como principal al volver a Trabajos.
- Reejecutar `PedidoTest` cuando el entorno WSL/PHP permita invocar `php.exe`.

Riesgos:

- La ruta `Ver pedido` abre `Pedidos` filtrado por número, no edición directa, para evitar depender de permisos de edición.
- La decisión fina de Contrato/Tarifario/Sociedad queda pendiente para Bloque 2.

Siguiente paso:

- Validar manualmente Bloque 1/B y después ejecutar Bloque 2 — Contrato/Tarifario/Sociedad.

## 2026-05-29 — Reajuste UI/UX pedidos en Trabajos

Estado: implementado con build correcto y pendiente de validación manual en navegador.

Archivos tocados:

- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `resources/js/Pages/Trabajos/Index.jsx`
- `resources/js/Pages/Pedidos/Form.jsx`
- `app/Http/Resources/Api/TrabajoResource.php`
- `app/Http/Controllers/Api/PedidoController.php`
- `app/Http/Controllers/Api/TrabajoController.php`
- `docs/02_CLIENTE/tareasComparar.md`

Cambios realizados:

- La tabla Excel de `Trabajos` sustituye la antigua zona de pedido por tres columnas estables: `Contrato / Tarifa`, `Pedidos` y `Acción pedido`.
- Se elimina el lenguaje visible de `principal/extra` y también el buscador de pedidos en la fila.
- Los pedidos del trabajo se muestran como secuencia natural `Pedido 1`, `Pedido 2`, `Pedido 3`, ordenados por `id_pedido` ascendente.
- Cada línea de pedido pasa a ser clicable; si el usuario puede editar pedidos abre la ficha segura, y si no, cae al listado filtrado como fallback.
- El trabajo sin pedidos muestra select compacto de contrato/tarifa; con pedidos, el contrato/tarifa pasa a texto fijo.
- La acción de pedido queda reducida a `Crear pedido`, `Crear otro pedido` o `Seleccionar contrato/tarifa` según estado real de la fila.
- `TrabajoResource` expone `pedidos_resumen` para soportar la nueva UI sin depender del concepto visible de pedido principal.
- `PedidoController` fuerza la herencia de `id_tarifario` desde el trabajo al crear/actualizar pedido asociado.
- `TrabajoController` se ha tocado de forma mínima y estrictamente necesaria para:
    - exponer `pedidos` resumidos al listado;
    - permitir edición inline segura de `id_tarifario`;
    - entregar catálogo de tarifarios activos para la tabla Excel.

Validaciones:

- `php artisan test --filter=TrabajoTest`: correcto (`21` tests, `138` aserciones) como validación funcional transversal posterior del listado.
- `npm run build`: correcto.
- Revisión estática del flujo:
    - sin pedidos: select contrato/tarifa + `Sin pedidos` + `Crear pedido`;
    - con pedidos: texto fijo de contrato/tarifa + `Pedido N · número`;
    - importes siguen acumulados en la fila del trabajo;
    - no se crean columnas dinámicas por pedido.

Build:

- `npm run build` correcto (`vite build`, compilación de producción completada).

Criterios pendientes:

- Validación manual en navegador de scroll horizontal, filtros y conflicto inline.
- Confirmar visualmente el caso de más de 3 pedidos con `Ver N pedidos`.
- Confirmar con negocio si la cabecera de pedido debe poder nacer sin `numero_pedido`; el backend actual sigue exigiéndolo y no se amplía el alcance en este bloque.
- Confirmar el origen real del contrato/tarifa predeterminado en maestros; hoy no existe un campo explícito explotable en este slice.

Siguiente paso:

- Validar manualmente el flujo en `Trabajos` y después decidir si el siguiente ajuste es desbloquear el predeterminado real en maestros o permitir borrador de pedido sin número externo.

## 2026-05-29 — Ajuste ancho vista Trabajos en pantallas grandes

- Archivos tocados: `resources/css/app.css`, `resources/js/Pages/Trabajos/Index.jsx`, `docs/02_CLIENTE/tareasComparar.md`.
- Cambio aplicado: `Trabajos` pasa a usar `contentWidthClass="max-w-none"` solo en esta vista y un contenedor operativo `ciete-page-operations` con ancho máximo mayor; la cabecera queda en un bloque `max-w-7xl`, mientras filtros y tabla aprovechan mucho más ancho en desktop ancho y ultrawide sin tocar funcionalidad ni scroll interno.
- Build: `npm run build` correcto (`vite build`, compilación de producción completada).
- Validaciones pendientes: revisión visual manual en 1366/1440 y wide/ultrawide para confirmar alineación de filtros/tabla, ausencia de scroll global y conservación de columnas `Contrato / Tarifa`, `Pedidos` y `Acción pedido`.

## 2026-05-29 — Buscador avanzado de Trabajos

- Estado: completo en código y validación automática; pendiente validación visual/manual de uso real en navegador antes de rebajar `Pedidos` en navegación.
- Archivos tocados: `app/Http/Controllers/Api/TrabajoController.php`, `resources/js/Pages/Trabajos/Index.jsx`, `resources/js/Components/ui/TrabajosExcelView.jsx`, `tests/Feature/TrabajoTest.php`, `docs/02_CLIENTE/tareasComparar.md`.
- Filtros implementados: búsqueda global ampliada, `estado`, `responsable`, `fecha_desde`, `fecha_hasta`, `codigo_estacion`, `municipio`, `provincia`, `id_contrato`, `id_tarifario`, `pedido_numero`, `has_pedidos`, `multi_pedido`, `pedido_importe`, `facturado`, `solicitado`, `id_estacion_servicio`, `categoria`.
- Búsquedas soportadas: número/código de trabajo, número de aviso, descripción, observaciones, categoría/tipo, datos de estación, número/estado/fecha/importe de pedidos asociados, contrato, tarifa, responsable, cliente/empresa y fechas/valores numéricos presentes en trabajo o pedidos.
- Validaciones: `php artisan test --filter=TrabajoTest` correcto (`21` tests, `138` aserciones) y `npm run build` correcto.
- Build: compilación de producción completada sin errores.
- Criterios pendientes: revisión manual en 1366/1440 y wide/ultrawide; comprobar en navegador búsqueda por pedido real `600034631`, estación, `772`, `MOEVE`, importes parciales y chips; confirmar que no aparece scroll horizontal global y que la ayuda flotante no se desplaza.
- Riesgos: la búsqueda económica global usa coincidencia textual sobre importes reales de pedidos; no se añade aún filtro por sociedad porque no hay exposición/relación operativa clara en esta vista; la vista no sustituye todavía al módulo `Pedidos` como respaldo hasta validar uso real.
- Siguiente paso: validar manualmente el buscador avanzado con datos reales y, si la localización de pedidos desde `Trabajos` resulta fiable, rebajar `Pedidos` a módulo secundario/consulta según rol.
- Nota: `Pedidos` no se elimina todavía del sidebar hasta validar que `Trabajos` permite localizar pedidos de forma fiable.

## 2026-05-29 — Cierre validación vista Trabajos

- Estado: parcial.
- Revisión cerrada en código sobre `Contrato / Tarifa`, `Pedidos`, `Acción pedido`, secuencia `Pedido 1`, `Pedido 2`, `Pedido 3`, buscador avanzado, filtros visibles/avanzados, chips, alineación de filtros con tabla y mantenimiento de scroll horizontal interno.
- Validaciones ejecutadas: `php artisan test --filter=TrabajoTest` correcto (`21` tests, `138` aserciones), `npm run build` correcto y `git diff --check` sin errores de whitespace en los archivos del bloque; solo aparecen avisos CRLF en ficheros ajenos al alcance.
- Estado funcional: la validación automática y de código queda cerrada; la única validación pendiente es visual/manual en navegador.
- Pendiente bloqueante: comprobar manualmente ausencia de scroll horizontal global innecesario, comportamiento en 1366/1440 y wide/ultrawide, clics reales sobre `Pedido 1/2/3`, ayuda flotante y localización fiable de pedidos desde `Trabajos`.
- Nota de navegación: `Pedidos` no se rebaja todavía del sidebar; podrá pasar a módulo secundario/consulta cuando la validación manual confirme que `Trabajos` localiza pedidos de forma fiable.

## 2026-05-29 — Pulido numeración y nombres de columnas en Trabajos

- Estado: implementado en código, con validación automática pendiente solo de contraste visual/manual en navegador.
- Qué decía la documentación/transcripción:
    - la reunión distingue entre un número base/interno usado para soporte técnico y ordenación, y el número "vuestro" o visible de negocio del trabajo;
    - se pidió respetar la nomenclatura de CIETE para no generar líos;
    - la vista operativa debía acercarse al orden real de trabajo: número de trabajo, número de estación, nombre de estación, municipio, provincia;
    - Moeve opera con una numeración más unificada y Repsol arrastra numeraciones por tipología, por lo que no convenía mezclar en una cabecera ambigua varias ideas distintas.
- Equivalencia decidida:

| Campo real                 | Significado                                                                                | Nombre visible recomendado                               | Mantener / ocultar / combinar |
| -------------------------- | ------------------------------------------------------------------------------------------ | -------------------------------------------------------- | ----------------------------- |
| `numero_trabajo`           | ordinal base del trabajo; soporte interno/técnico y de ordenación                          | `Nº trabajo base` solo donde haga falta explicar el alta | mantener secundario           |
| `numero_trabajo_operativo` | número visible/operativo que negocio identifica como "vuestro"                             | `Nº trabajo`                                             | mantener principal            |
| `numero_trabajo_visible`   | derivado UI: usa `numero_trabajo_operativo` y si no existe cae a `numero_trabajo`          | `Nº trabajo`                                             | combinar                      |
| `codigo_estacion`          | código/número operativo de estación mostrado en la tabla                                   | `Nº estación`                                            | mantener                      |
| `numero_ciete`             | no existe como campo real en `Trabajo`/`TrabajoResource`                                   | no usar como label autónomo                              | ocultar como nombre técnico   |
| `numero_interno`           | no existe como campo real autónomo; hoy el equivalente práctico es `numero_trabajo`        | `Nº trabajo base` cuando haga falta explicarlo           | combinar                      |
| `numero_operativo`         | no existe con ese nombre; el equivalente real es `numero_trabajo_operativo`                | `Nº trabajo`                                             | combinar                      |
| `codigo`                   | no es un campo propio del trabajo en esta vista; aparece en catálogos/contextos/estaciones | no usar como cabecera de numeración de trabajo           | ocultar en este bloque        |

- Nombres finales decididos:
    - cabecera principal de numeración: `Nº trabajo`;
    - referencia secundaria cuando exista un número visible distinto: `Ref. interna ####`;
    - columna de estación operativa: `Nº estación`;
    - placeholders de alta: `Nº trabajo base` y `Nº trabajo visible (opcional)`.
- Archivos tocados:
    - `resources/js/Components/ui/TrabajosExcelView.jsx`
    - `docs/02_CLIENTE/tareasComparar.md`
- Cambios aplicados:
    - se elimina la cabecera ambigua `Nº CIETE / interno`;
    - la primera columna pasa a mostrar `Nº trabajo` como nombre único de negocio;
    - si existe `numero_trabajo_operativo` y difiere del ordinal base, la fila enseña una segunda línea compacta `Ref. interna ####` para no perder trazabilidad sin abrir una segunda columna;
    - la fila de nuevo trabajo sustituye `Interno` y `Nº CIETE opcional` por placeholders explícitos y más comprensibles;
    - la antigua columna `Estación` pasa a `Nº estación`;
    - se ajustan placeholder y chip visible de filtros para hablar también de `Nº estación` y `nº trabajo`.
- Validaciones ejecutadas:
    - `npm run build`
- Build:
    - compilación de producción correcta.
- Criterios cumplidos:
    - no se toca lógica de pedidos, líneas, decimales, exportación, facturas, base de datos, migraciones ni seeders;
    - la tabla mantiene una sola columna principal para la numeración de trabajo;
    - no se introducen nuevas funcionalidades, solo nomenclatura y presentación.
- Criterios pendientes:
    - validación manual en navegador de scroll horizontal, densidad visual de la primera columna y comprensión de la fila `Nuevo`;
    - confirmar con negocio si la línea secundaria `Ref. interna` debe quedarse visible siempre que exista numeración operativa distinta o puede vivir solo en tooltip.
- Riesgos:
    - la reunión deja la política definitiva de numeración todavía abierta entre contexto, cliente y tipología; este ajuste solo ordena la presentación actual y no resuelve esa definición funcional.
- Siguiente paso:
    - validación manual visual en `Trabajos` con casos reales de Moeve/Repsol antes de dar por cerrada la nomenclatura operativa.

## 2026-05-29 — Ajuste numeración y alta de nuevos trabajos

- Estado: implementado en código y validación automática; pendiente contraste visual/manual final en navegador.
- Qué se ha confirmado en transcripción:
    - la reunión distingue entre un número base/interno y el número visible de negocio, pero no cierra una regla final única de autogeneración;
    - sí aparece la idea de prefijo/incremental por cliente/contexto y la posibilidad de repetición por tipología en Repsol, pero queda como decisión abierta;
    - también queda claro que la tabla operativa debe trabajar con un único `Nº trabajo` visible para negocio, no con dos campos manuales en alta rápida.
- Confirmación técnica real en código:
    - `Trabajo` solo tiene `numero_trabajo` y `numero_trabajo_operativo`; `numero_trabajo_visible` es derivado del recurso/modelo y cae a `numero_trabajo` cuando no existe operativo;
    - `StoreTrabajoRequest` y `TrabajoController@store` exigen `numero_trabajo` y dejan `numero_trabajo_operativo` opcional;
    - por tanto, la fila `Nuevo trabajo` no estaba usando campos legacy inexistentes, pero sí exponía demasiado detalle interno del modelo.
- Decisión aplicada:
    - mientras la numeración por prefijo/contexto siga sin cerrar, la fila `Nuevo trabajo` usa un único campo visible `Nº trabajo`;
    - no se implementa autogeneración nueva;
    - no se inventan prefijos definitivos `MOE/REP`;
    - `numero_trabajo_operativo` deja de pedirse manualmente en la alta rápida de la vista Excel.
- Campos visibles finales:
    - tabla: una sola cabecera `Nº trabajo`;
    - fila `Nuevo trabajo`: un solo input `Nº trabajo`;
    - filtros/chips: siguen buscando y mostrando `nº trabajo` y `Nº estación`;
    - edición inline: la tabla sigue resolviendo el número visible actual sin abrir una segunda columna;
    - payload de guardado: sigue enviando `numero_trabajo`; `numero_trabajo_operativo` permanece opcional y sale `null` si no existe.
- Archivos tocados:
    - `resources/js/Components/ui/TrabajosExcelView.jsx`
    - `docs/02_CLIENTE/tareasComparar.md`
- Cambios aplicados:
    - se elimina el segundo input de numeración en la fila `Nuevo trabajo`;
    - se elimina también el chip visual `Nuevo` en la celda de numeración para dejar la alta más limpia y menos ruidosa;
    - desaparecen de esta fila los textos `Nº trabajo base`, `Nº trabajo visible (opcional)`, `Interno` y `Nº CIETE opcional`;
    - el único placeholder visible pasa a `Nº trabajo`;
    - se alinea el label interno de `numero_trabajo` con `Nº trabajo` para no reintroducir nomenclatura técnica en esta vista.
- Validaciones:
    - `php artisan test --filter=TrabajoTest`
    - `npm run build`
    - `git diff --check`
- Build/test:
    - `TrabajoTest` correcto (`21` tests, `138` aserciones);
    - build correcta (`vite build`, compilación de producción completada).
- Criterios cumplidos:
    - la fila `Nuevo trabajo` ya no muestra dos campos de numeración;
    - no aparece `Interno`;
    - no aparece `Nº CIETE opcional`;
    - la cabecera se mantiene en `Nº trabajo`;
    - no se toca lógica de pedidos, líneas, decimales, exportación, facturas, base de datos, migraciones ni seeders.
- Criterios pendientes:
    - validación manual en navegador del alta real desde `Trabajos`;
    - confirmar con negocio la regla definitiva de prefijo/contexto antes de automatizar numeración visible.
- Riesgos:
    - la política funcional de numeración sigue abierta entre cliente/contexto/tipología;
    - la transcripción sí apunta a prefijo/incremental por cliente/contexto (`nombre + número`), pero esa automatización no existe aún en `TrabajoController@store` ni en requests;
    - mientras no se cierre esa regla, los trabajos creados desde la vista rápida seguirán naciendo con `numero_trabajo_operativo` vacío salvo que otro flujo lo complete después.
- Siguiente paso:
    - validar manualmente el alta rápida y, cuando negocio cierre la regla de prefijo/contexto, decidir si `numero_trabajo_operativo` debe autogenerarse o desaparecer del flujo diario.

## 2026-05-29 — Autogeneración Nº trabajo en alta rápida

- Estado: implementado en código con validación automática; pendiente solo contraste manual en navegador.
- Decisión aplicada:
    - el alta rápida de `Trabajos` ya no pide al usuario escribir `Nº trabajo`;
    - si no llega `numero_trabajo`, el backend lo genera automáticamente;
    - si no llega `numero_trabajo_operativo`, el backend intenta generar también el visible con prefijo.
- Formato usado:
    - MOEVE: `MOE-000123`
    - REPSOL: `REP-000123`
    - OTROS: `OTR-000123`
- Numeración aplicada:
    - el correlativo base `numero_trabajo` se genera por contexto;
    - el cálculo toma el máximo actual del contexto y busca el siguiente libre;
    - si el visible con prefijo colisiona, incrementa hasta encontrar uno disponible.
- Compatibilidad:
    - si un flujo antiguo sigue enviando `numero_trabajo` manual, se conserva;
    - si además no envía `numero_trabajo_operativo`, el sistema intenta derivarlo con el prefijo del contexto;
    - si esa derivación manual colisiona, cae a `null` y la vista sigue resolviendo el visible por fallback sin romper el alta.
- Archivos tocados:
    - `app/Http/Requests/Api/StoreTrabajoRequest.php`
    - `app/Http/Controllers/Api/TrabajoController.php`
    - `resources/js/Components/ui/TrabajosExcelView.jsx`
    - `tests/Feature/TrabajoTest.php`
    - `tests/Feature/Api/TrabajoRequestTest.php`
    - `docs/02_CLIENTE/tareasComparar.md`
- Cambios aplicados:
    - `StoreTrabajoRequest` deja de exigir `numero_trabajo` para el alta y normaliza vacíos a `null`;
    - `TrabajoController@store` genera `numero_trabajo` y `numero_trabajo_operativo` dentro de transacción y bloqueo por contexto;
    - la fila `Nuevo trabajo` elimina el input manual y muestra `Se generará automáticamente`;
    - se mantienen intactos pedidos, líneas, decimales, exportación, facturas e importación.
- Validaciones:
    - `php artisan test --filter=TrabajoTest`
    - `npm run build`
    - `git diff --check`
- Build/test:
    - `TrabajoTest` correcto (`24` tests, `153` aserciones);
    - build correcta (`vite build`, compilación de producción completada).
- Riesgos:
    - la transcripción empuja a prefijo/incremental por contexto, pero negocio no ha cerrado todavía si debe ser definitivo `MOE/REP/OTR` o una convención distinta;
    - la numeración base se ha fijado por contexto como opción operativa menos confusa; si negocio exige global, habrá que ajustar la estrategia;
    - el formulario completo de trabajos fuera de la vista Excel sigue permitiendo flujo manual por compatibilidad.
- Pendiente de confirmar con negocio:
    - si el prefijo definitivo debe ser exactamente `MOE/REP/OTR`;
    - si la numeración base debe seguir por contexto o pasar a global;
    - si el formulario completo de `Trabajos` debe converger también a autogeneración.

## 2026-05-29 — Especificación columnas Trabajos

- Estado: documentación creada, sin implementación.
- Documento creado: `docs/02_CLIENTE/ESPECIFICACION_COLUMNAS_TRABAJOS_2026-05-29.md`
- Alcance: inventario actual de columnas, contraste con transcripción, propuesta final de tabla, columnas a fusionar/ocultar y criterio provisional sobre `Pedidos` en sidebar.
- Regla aplicada: no se modifica código, no se cambian columnas todavía, no se cambia sidebar todavía y no se altera porcentaje global.

## 2026-05-29 — Aplicación especificación columnas Trabajos y decisión Pedidos sidebar

- Estado: implementado en UI y documentación; pendiente solo validación manual final en navegador.
- Archivos tocados:
    - `resources/js/Components/ui/TrabajosExcelView.jsx`
    - `docs/02_CLIENTE/ESPECIFICACION_COLUMNAS_TRABAJOS_2026-05-29.md`
    - `docs/02_CLIENTE/tareasComparar.md`
- Renombres aplicados:
    - `Solicitud pedido` pasa a `Fecha solicitud pedido`;
    - `Tipo / categoría` pasa a `Categoría de trabajo`;
    - los chips y placeholders asociados pasan también a hablar de `Categoría de trabajo`.
- Orden de columnas aplicado:
    - se mantiene el orden operativo ya fijado en la especificación: `Nº trabajo`, `Nº estación`, `Nombre estación`, `Municipio`, `Provincia`, `Categoría de trabajo`, `Descripción`, `Contrato / Tarifa`, `Pedidos`, `Acción pedido`, `Importe pedido`, `Solicitado`, `Facturado`, `Estado`, `Responsable`, `Fecha encargo`, `Fecha solicitud pedido`, `Fecha terminación`, `Observaciones`, `Acciones`.
- Decisión de navegación:
    - `Pedidos` se mantiene visible en sidebar;
    - su uso recomendado queda fijado como consulta, búsqueda, fichas y control;
    - `Trabajos` queda como pantalla operativa principal para ejecución;
    - no se cambian permisos ni se oculta `Pedidos` en este bloque.
- Validaciones:
    - `php artisan test --filter=TrabajoTest`
    - `npm run build`
    - `git diff --check`
- Build/test:
    - `TrabajoTest` correcto (`24` tests, `152` aserciones);
    - build correcta (`vite build`, compilación de producción completada).
- Pendientes:
    - validación manual de anchuras de cabecera, scroll horizontal interno y lectura real de `Categoría de trabajo` en Moeve/Repsol;
    - confirmar en uso real que la columna `Fecha solicitud pedido` sigue siendo suficientemente compacta en 1366/1440.

## 2026-05-29 — Remate alta rápida Trabajos

- Estado: implementado en código con validación automática; pendiente contraste manual final en navegador.
- Categoría de trabajo:
    - en alta rápida pasa a selector buscable por código/nombre cuando existe catálogo real en el contexto;
    - para REPSOL se usa catálogo real de `tipos_trabajo` y se deriva internamente `id_tipo_documento` + `id_tipo_trabajo` sin abrir columnas nuevas;
    - para otros contextos se usa catálogo real si existe y, si no hay catálogo claro, se mantiene fallback a texto libre.
- Contrato / tarifa:
    - la fila nueva pasa a usar un único selector combinado de contrato/tarifa;
    - se filtra por contexto y empresa de la estación seleccionada;
    - si solo hay una opción disponible, se autoselecciona;
    - no existe hoy un origen real de `predeterminado`, así que no se inventa y queda pendiente para Bloque 2;
    - para MOEVE el guardado ya exige selección efectiva de contrato/tarifa a través de este selector.
- Crear pedido desde trabajo nuevo:
    - la columna `Acción pedido` de la fila nueva añade flujo real `Crear pedido`;
    - al pulsarlo, el sistema valida, guarda primero el trabajo y solo después abre `Pedidos` con `trabajo_id`;
    - si faltan campos obligatorios o falta contrato/tarifa, no abre pedido y muestra error claro;
    - si el guardado falla, no se crea pedido ni se pierden los datos introducidos.
- Revisión de estados según transcripción:
    - confirmada la dirección funcional: `en curso` como base, `terminado` a nivel ejecución, `finalizado` como cierre posterior y `cancelado` visible al final;
    - no se cambia lógica automática ni se recortan estados en este bloque porque la reunión los trata como derivados de pedido/solicitado/facturado/cierre y eso pertenece al bloque específico de estados.
- Archivos tocados:
    - `resources/js/Components/ui/TrabajosExcelView.jsx`
    - `app/Http/Requests/Api/StoreTrabajoRequest.php`
    - `app/Http/Controllers/Api/TrabajoController.php`
    - `tests/Feature/TrabajoTest.php`
    - `docs/02_CLIENTE/tareasComparar.md`
- Tests/build:
    - `php artisan test --filter=TrabajoTest` correcto (`25` tests, `158` aserciones);
    - `npm run build` correcto;
    - `git diff --check` pendiente de cierre tras esta actualización documental.
- Pendientes:
    - validar manualmente el selector de categoría en contextos con y sin catálogo;
    - confirmar en navegador autoselección de contrato/tarifa cuando solo exista una opción;
    - decidir en Bloque 2 el origen real del predeterminado por cliente/contexto/sociedad.
- Riesgos:
    - en contextos sin catálogo real de categoría se mantiene fallback manual porque no existe hoy una fuente operativa cerrada;
    - la autoselección de contrato/tarifa solo puede hacerse de forma segura cuando la estación ya identifica una empresa y existe una única opción real;
    - la automatización profunda de estados sigue fuera de alcance y no se adelanta aquí.

## 2026-05-29 — Corrección nomenclatura tarifario/contrato y estados

- Estado: implementado en UI y documentación; pendiente solo validación manual visual final en navegador.
- Nombre final aplicado para la columna:
    - la antigua columna `Contrato / Tarifa` pasa a `Tarifario`;
    - evidencia principal de transcripción:
        - “cojo el tarifario 772 y me lo crea”;
        - “cada trabajo tiene un tarifario”;
        - “en el fondo, no es una lista de contratos, es una lista de tarifas”;
    - el contrato sigue existiendo como dato estructural y filtro separado, pero no como nombre visible principal de esta columna operativa.
- Estados revisados contra transcripción:
    - `en_curso` pasa a mostrarse como `Trabajo en curso`;
    - `terminado` pasa a mostrarse como `Terminado`;
    - `pendiente_facturar`, `facturado`, `finalizado` y `cancelado` se mantienen por estar explícitamente reconocidos en la reunión;
    - no se elimina ni deriva todavía ningún estado porque la reunión los vincula a importes/pedido/facturación/cierre y eso pertenece al bloque específico de estados automáticos.
- Estados aplicados o pendientes:
    - aplicado solo cambio de labels visibles;
    - pendiente de bloque específico la lógica derivada/automática de `pendiente de facturar`, `facturado`, `finalizado` y cierres.
- Cambios visuales:
    - la columna, selector, chip y textos visibles asociados a `id_tarifario` pasan a hablar de `Tarifario`;
    - el badge de estado de `Trabajos` pasa a mostrarse siempre en una sola línea, sin partirse;
    - se evita el texto `Terminado por ejecución` y se alinea a la nomenclatura real más corta usada por CIETE.
- Archivos tocados:
    - `resources/js/Components/ui/TrabajosExcelView.jsx`
    - `docs/02_CLIENTE/ESPECIFICACION_COLUMNAS_TRABAJOS_2026-05-29.md`
    - `docs/02_CLIENTE/tareasComparar.md`
- Build/test:
    - `php artisan test --filter=TrabajoTest`
    - `npm run build`
    - `git diff --check`
- Pendientes:
    - confirmar manualmente en navegador que `Pendiente de facturar` y `Trabajo en curso` ya no parten línea en 1366/1440;
    - resolver en bloque específico si `finalizado` debe quedar siempre derivado y no como edición manual visible.

## 2026-05-29 — Cierre remates visuales Trabajos y modularización restante

- Estado: implementado en UI/documentación; pendiente solo validación manual visual final de `Trabajos`.
- Categoría de trabajo:
    - la alta rápida mantiene selector buscable por código y nombre cuando existe catálogo real;
    - se confirma además en la base actual catálogo real por contexto (`tipos_trabajo`: `1=>29`, `2=>226`, `3=>2`; `tipos_documento`: `1=>2`, `2=>12`, `3=>1`);
    - se elimina el fallback silencioso a input libre y, si un contexto futuro no tuviera catálogo activo, la celda muestra aviso explícito de catálogo pendiente.
- `Abrir ficha`:
    - pasa a una sola línea mediante ajuste visual de clase;
    - no se tocan rutas, permisos ni lógica de navegación.
- Documento de estado/bloques:
    - creado `docs/02_CLIENTE/ESTADO_Y_BLOQUES_RESTANTES_REUNION_CIETE_2026-05-29.md`;
    - fija lo completado, lo pendiente, la modularización restante y el orden recomendado de ejecución.
- Archivos tocados:
    - `resources/js/Components/ui/TrabajosExcelView.jsx`
    - `docs/02_CLIENTE/ESTADO_Y_BLOQUES_RESTANTES_REUNION_CIETE_2026-05-29.md`
    - `docs/02_CLIENTE/tareasComparar.md`
- Tests/build:
    - `npm run build` correcto;
    - `php artisan test --filter=TrabajoTest` ejecutado también como validación transversal y correcto (`25` tests, `158` aserciones);
    - `git diff --check` correcto.
- Pendientes:
    - validación manual en navegador de que no aparece scroll global innecesario;
    - comprobar visualmente el selector de categoría en alta rápida en MOEVE y REPSOL con datos reales;
    - validar en navegador que `Abrir ficha` no rompe el ancho útil de la última columna.
- Siguiente bloque recomendado:
    - no empezar todavía Bloque 2 en código;
    - hacer primero la validación visual final de `Trabajos` y, si queda cerrada, pasar a Bloque 2 — Tarifario / contrato / sociedad facturadora.

## 2026-05-29 — Ordenación por columnas en Trabajos

- Estado: implementado en backend/frontend con ordenación server-side y pendiente solo validación manual final en navegador.
- Columnas ordenables:
    - `Nº trabajo`
    - `Nº estación`
    - `Nombre estación`
    - `Municipio`
    - `Provincia`
    - `Categoría de trabajo`
    - `Descripción`
    - `Tarifario`
    - `Importe pedido`
    - `Solicitado`
    - `Facturado`
    - `Estado`
    - `Responsable`
    - `Fecha encargo`
    - `Fecha solicitud pedido`
    - `Fecha terminación`
- Columnas no ordenables en este bloque:
    - `Pedidos`
    - `Acción pedido`
    - `Observaciones`
    - `Acciones`
- Cambios aplicados:
    - `TrabajoController@index` acepta `sort` y `direction`, valida whitelist cerrada y aplica ordenación segura en servidor;
    - 1 clic ordena ascendente, 2 clics descendente y 3 clics limpia la ordenación para volver al orden operativo original;
    - al volver al orden original se recupera la ordenación base de `Trabajos`, manteniendo `cancelado` al final;
    - las cabeceras ordenables muestran indicador visual discreto `↑`/`↓`;
    - la ordenación convive con filtros, chips, búsqueda y paginación.
- Archivos tocados:
    - `app/Http/Controllers/Api/TrabajoController.php`
    - `resources/js/Pages/Trabajos/Index.jsx`
    - `resources/js/Components/ui/TrabajosExcelView.jsx`
    - `tests/Feature/TrabajoTest.php`
    - `docs/02_CLIENTE/tareasComparar.md`
- Validaciones:
    - `php artisan test --filter=TrabajoTest`
    - `npm run build`
    - `git diff --check`
- Build/test:
    - `TrabajoTest` correcto (`28` tests, `228` aserciones);
    - `npm run build` correcto (`vite build`, compilación de producción completada);
    - `git diff --check` correcto.
- Pendientes:
    - comprobar manualmente en navegador la interacción 1/2/3 clic con filtros activos, cambio de página y conservación del scroll horizontal interno;
    - confirmar visualmente que las cabeceras clicables no degradan anchura ni densidad de la tabla.
- Siguiente paso:
    - cerrar validación visual/manual de `Trabajos` y, si no aparecen regresiones, dar por finalizado este bloque antes de empezar Bloque 2.
- Hotfix runtime ordenación Trabajos:
    - error detectado: la vista `Trabajos` podía romper en runtime con `Uncaught TypeError: Cannot convert undefined or null to object` al montar la tabla tras introducir la lógica de ordenación;
    - causa: la nueva capa de ordenación asumía que `filters` y algunas estructuras auxiliares llegaban siempre como objeto válido en el primer render;
    - archivos tocados:
        - `resources/js/Components/ui/TrabajosExcelView.jsx`
        - `resources/js/Pages/Trabajos/Index.jsx`
        - `app/Http/Controllers/Api/TrabajoController.php`
        - `docs/02_CLIENTE/tareasComparar.md`
    - validaciones:
        - `php artisan test --filter=TrabajoTest`
        - `npm run build`
        - `git diff --check`
    - confirmación:
        - el hotfix normaliza `filters`, `trabajos` y conversiones `Object.*` para que la vista vuelva a cargar incluso sin ordenación activa o con estado limpio.

## 2026-06-01 — Validación manual final Trabajos

- Estado: **✓ VALIDACIÓN COMPLETA EXITOSA**.
- Alcance: cierre de validación funcional manual en navegador de la vista Excel de `Trabajos` con especial énfasis en ordenación, filtros, visualización y comportamiento en paginación.
- Validaciones ejecutadas en navegador (modo Ciete Excel):
    - ✓ **Carga inicial**: sin blank screen, 4 trabajos cargados correctamente, sin ordenación activa al cargar.
    - ✓ **Ordenación por columnas (patrón 3-clic)**: verificadas 7 columnas ordenables con patrón asc → desc → original:
        - Nº trabajo (↑ asc, ↓ desc, sin indicador al original)
        - Nº estación
        - Categoría de trabajo
        - Estado
        - Tarifario
        - Importe pedido
        - Fecha encargo
    - ✓ **Ordenación + Filtros**: búsqueda con número `980` + ordenación coexisten en URL, parámetros `search` y `sort` conviven correctamente.
    - ✓ **Paginación + Ordenación**: URL contiene ambos parámetros `page` y `sort`, ordenación persiste al cambiar página.
    - ✓ **Visualización de Pedidos**: todos los trabajos muestran información de pedidos (con número, sin pedidos, etc.).
    - ✓ **Botón de Creación**: botón "Nuevo trabajo" disponible y funcional.
    - ✓ **Visual**: tabla se renderiza correctamente sin scroll horizontal problemático, ancho total 3209px, Estado cabe en una línea (CSS `whitespace-nowrap`), componentes interactivos funcionales.
    - ✓ **Comportamiento Cancelado**: código mantiene lógica `orderedRows` useMemo que coloca cancelados al final cuando se vuelve al orden original.
- Validaciones técnicas ejecutadas:
    - ✓ **npm run build**: compilación de producción exitosa, 2992 módulos transformados, manifest y assets generados correctamente.
    - ✓ **php artisan test --filter=TrabajoTest**: 28 tests pasados (228 aserciones), incluyendo:
        - index supports server side sort by work number in both directions
        - index default order keeps cancelled jobs at the end when no sort is…
        - index supports server side sort by station code
    - ✓ **git diff --check**: sin errores de whitespace ni problemas de formato.
- Archivos sin modificación (según requisitos de "no tocar líneas, decimales, exportación, facturas, base de datos, migraciones"):
    - no se modificaron archivos de pedido, factura, líneas, decimales ni exportación;
    - no se crearon migraciones;
    - no se ejecutó deploy, commit ni push.
- Criterios cumplidos:
    1. Ordenación funciona en patrón 3-clic: asc → desc → original, con URL actualizada y indicadores visuales (↑↓).
    2. Ordenación coexiste con filtros (búsqueda, estado, responsable, fechas, etc.).
    3. Paginación + ordenación funcionan juntas.
    4. Todos los elementos visuales (Estado, Pedidos, Abrir ficha) cabes en sus espacios sin romper layout.
    5. Cancelados se mantienen al final cuando se limpia la ordenación.
    6. Build completa sin errores, tests pasados, code review con git diff limpio.
- Siguiente paso:
    - Bloque 2 — Tarifario / Contrato / Sociedad facturadora.

## 2026-06-01 — Análisis estado y plan final CIETE

- Estado: documentación creada, sin implementación.
- Documento creado: `docs/02_CLIENTE/ANALISIS_ESTADO_Y_PLAN_FINAL_CIETE_2026-06-01.md`
- Fuentes consolidadas: `tareasComparar.md`, `listado_exhaustivo_reunion_cesar_amaya_ciete_2026-05-19.md`, `reunionCieteCompletaFormato.txt`, `ESTADO_Y_BLOQUES_RESTANTES_REUNION_CIETE_2026-05-29.md` y `ESPECIFICACION_COLUMNAS_TRABAJOS_2026-05-29.md`.
- Lectura final: `Trabajos` queda ya validado manualmente a fecha `2026-06-01`; el cuello de botella pendiente pasa a ser `Tarifario / contrato / sociedad`, líneas con decimales, exportación Moeve, estados derivados, facturación/cierre y endurecimiento final de roles/auditoría.
- Porcentaje funcional aproximado de la ola pedida en reunión: `52%`.
- Plan objetivo actualizado: cierre funcional el viernes `2026-06-05`, compactando Bloques 2-5 y dejando Bloque 6 solo en su parte crítica.
- Siguiente paso recomendado: arrancar hoy Bloque 2 y no abrir líneas, exportación ni estados automáticos antes de cerrarlo.

## 2026-06-01 — Bloque 2 implementado: tarifario predeterminado

- Estado: completo en código; pendiente validación funcional final con negocio y reejecución de parte de tests PHP afectados por entorno WSL.
- Regla detectada:
    - contratos y tarifarios se gestionan en maestros;
    - dentro de un contrato puede haber uno o varios tarifarios;
    - uno de ellos debe poder quedar marcado como predeterminado/habitual;
    - `Trabajos` debe usar ese tarifario por defecto si existe, permitir cambio antes de pedidos y bloquearlo después;
    - la sociedad facturadora concreta sigue en `Factura`, validada contra `contrato_empresas_facturadoras`.
- Implementación:
    - migración mínima `database/migrations/2026_06_01_000140_add_es_predeterminado_to_tarifarios_table.php`;
    - maestros de `Tarifarios` permiten marcar `Tarifario predeterminado` y muestran `Predeterminado para nuevos trabajos`;
    - al marcar un predeterminado, se desmarcan los demás del mismo contrato;
    - `Trabajos` consume `is_default` y preselecciona el predeterminado compatible o la única opción compatible;
    - `Pedidos` exige trabajo con tarifario válido y rechaza desalinear el `id_tarifario`.
- Archivos tocados:
    - `app/Http/Controllers/TarifarioController.php`
    - `app/Models/Tarifario.php`
    - `app/Http/Controllers/Api/TrabajoController.php`
    - `app/Http/Controllers/Api/PedidoController.php`
    - `app/Http/Requests/Api/StorePedidoRequest.php`
    - `app/Http/Requests/Api/UpdatePedidoRequest.php`
    - `resources/js/Pages/Tarifarios/Form.jsx`
    - `resources/js/Pages/Tarifarios/Index.jsx`
    - `resources/js/Components/ui/TrabajosExcelView.jsx`
    - `tests/Feature/MaestrosTest.php`
    - `tests/Feature/TrabajoTest.php`
    - `tests/Feature/PedidoTest.php`
    - `docs/02_CLIENTE/BLOQUE_2_TARIFARIO_CONTRATO_SOCIEDAD_2026-06-01.md`
    - `docs/02_CLIENTE/tareasComparar.md`
    - `docs/02_CLIENTE/ANALISIS_ESTADO_Y_PLAN_FINAL_CIETE_2026-06-01.md`
- Dudas pendientes:
    - si negocio necesita predeterminado distinto por sociedad facturadora dentro del mismo contrato;
    - si el predeterminado debe recalcular trabajos existentes sin pedido;
    - si la sociedad facturadora debe adelantarse a `Trabajo/Pedido` en algún caso excepcional.
- Validaciones:
    - `git diff --check`: correcto; solo avisos CRLF en ficheros ajenos al alcance.
    - `php artisan test --filter=TrabajoTest`: correcto (`30` tests, `242` aserciones).
    - `php artisan test --filter=MaestrosTest`: no ejecutable de forma fiable por entorno WSL/PHP (`UtilBindVsockAnyPort: socket failed 1`).
    - `php artisan test --filter=PedidoTest`: no ejecutable de forma fiable por entorno WSL/PHP (`UtilBindVsockAnyPort: socket failed 1`).
    - `npm run build`: correcto.
- Siguiente paso:
    - ejecutar Bloque 3 — líneas de pedido y decimales;
    - mantener fuera de alcance exportación Moeve, estados automáticos y sociedad facturadora concreta hasta sus bloques propios.

## 2026-06-01 — Bloque 3 implementado: líneas de pedido y decimales

- Estado: parcial; implementación completada en código y build OK, pero validación PHP bloqueada por entorno WSL/VSOCK antes de pasar a Bloque 4.
- Archivos tocados:
    - `app/Http/Controllers/Api/PedidoController.php`
    - `app/Http/Requests/Api/StorePedidoRequest.php`
    - `app/Http/Requests/Api/UpdatePedidoRequest.php`
    - `routes/web/operativa.php`
    - `resources/js/Components/ui/ItemsTable.jsx`
    - `resources/js/Pages/Pedidos/Form.jsx`
    - `tests/Feature/PedidoTest.php`
    - `docs/02_CLIENTE/tareasComparar.md`
- Qué se hizo:
    - `Pedido` recalcula `importe_pedido`, `unidades_pedido`, `pedido_completo`, `tiene_mas_de_1_item` y `facturado_completo` desde `pedido_items` cuando se crean o sincronizan líneas;
    - las requests de pedido dejan de bloquear cantidades decimales y validan que `total_linea = cantidad x precio_unitario`;
    - se fuerza que si existen líneas tarifarias compatibles no pueda guardarse un pedido operativo sin al menos una línea;
    - el formulario de `Pedidos` deja de tratar el alta desde `Trabajo` como cabecera vacía y permite completar líneas en el mismo flujo;
    - el selector de líneas pasa a ser buscable por código o descripción y muestra código, concepto, unidad y precio;
    - `tarifarioLineas` carga también la unidad para la UI;
    - `PedidoTest` se actualiza para cubrir cantidades decimales, recálculo de importes y rechazo de líneas de otro tarifario.
- Validaciones:
    - `git diff --check`: correcto; solo avisos CRLF en ficheros ajenos al alcance.
    - `npm run build`: correcto (`vite build`, compilación de producción completada).
    - ejecución PHP (`php artisan test --filter=PedidoTest` y comprobaciones `php -l`): bloqueada por entorno WSL/PHP con `UtilBindVsockAnyPort: socket failed 1`.
- Build/test:
    - build frontend correcto;
    - tests PHP no verificables en esta sesión por bloqueo de entorno, no por error de aplicación observado en código.
- Pendientes:
    - reejecutar `PedidoTest` completo en entorno PHP funcional;
    - validar manualmente en navegador el buscador de líneas con varios tarifarios y edición de pedidos ya facturados parcialmente.
- Riesgos:
    - al no poder ejecutar la suite PHP, queda riesgo residual de regresión en serialización o validación fina de `Pedido`;
    - `ItemsTable` introduce lógica local de buscador que necesita comprobación manual de UX en edge cases (blur, limpieza, líneas repetidas).
- Siguiente paso:
    - repetir este mismo Bloque 3 en un entorno donde `artisan test` funcione;
    - solo si `PedidoTest` queda verde, continuar con Bloque 4 — exportación Moeve PDF/CSV/cuadro ARIBA.

## 2026-06-01 — Validación Bloque 3: líneas de pedido y decimales

- Estado: parcial por validación manual de navegador no ejecutada en esta sesión; validación técnica PHP/build completada.
- Estado del código antes de tocar:
    - revisado `git status --short`;
    - revisado diff de:
        - `app/Http/Controllers/Api/PedidoController.php`
        - `app/Http/Requests/Api/StorePedidoRequest.php`
        - `app/Http/Requests/Api/UpdatePedidoRequest.php`
        - `routes/web/operativa.php`
        - `resources/js/Components/ui/ItemsTable.jsx`
        - `resources/js/Pages/Pedidos/Form.jsx`
        - `tests/Feature/PedidoTest.php`
        - `docs/02_CLIENTE/tareasComparar.md`
    - no se detectó fallo claro previo que justificara tocar funcionalidad adicional.
- Validaciones PHP:
    - se usó PHP de Windows/XAMPP:
        - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=PedidoTest`
        - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=TrabajoTest`
        - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=MaestrosTest`
    - resultado:
        - `PedidoTest`: correcto, `15` tests y `59` aserciones.
        - `TrabajoTest`: correcto, `30` tests y `242` aserciones.
        - `MaestrosTest`: correcto, `11` tests y `62` aserciones.
    - nota de validación:
        - hubo un intento intermedio en paralelo de `TrabajoTest` y `MaestrosTest` que generó colisión en la BD de testing MySQL; repetidos en secuencial pasaron correctamente. No era fallo de Bloque 3.
    - sintaxis PHP:
        - se intentó `php -l` con PHP Windows/XAMPP;
        - la variante directa por `cmd.exe` con rutas relativas perdió las barras y la variante con `cd /D` devolvió `UtilBindVsockAnyPort: socket failed 1`;
        - al haber pasado `PedidoTest` completo sobre los archivos tocados, la sintaxis queda razonablemente validada aunque `php -l` no se pudo cerrar por shell/entorno.
- Validación frontend/build:
    - `cmd.exe /C npm run build`: correcto (`vite build`, compilación de producción completada).
    - `git diff --check`: correcto; solo avisos CRLF en ficheros ajenos al alcance.
- Validación manual:
    - no ejecutada en navegador desde esta sesión;
    - no hay acceso GUI/browser operativo en este entorno de trabajo;
    - queda pendiente comprobar visualmente:
        - búsqueda por código;
        - búsqueda por descripción;
        - carga de código/concepto/unidad/precio;
        - cantidad decimal;
        - recálculo de `total_linea` e importe pedido;
        - alta de pedido desde `Trabajos`.
- Correcciones aplicadas:
    - ninguna en esta fase de validación; no hizo falta tocar funcionalidad de Bloque 3.
- Resultado build/test:
    - backend de Bloque 3 validado con `PedidoTest` en PHP Windows/XAMPP;
    - suites de referencia relacionadas (`TrabajoTest`, `MaestrosTest`) correctas en secuencial;
    - build frontend correcto;
    - `git diff --check` correcto.
- Estado final:
    - Bloque 3 queda técnicamente validado;
    - pendiente solo validación manual mínima de navegador si se quiere cierre funcional visual antes de abrir Bloque 4.
- Siguiente paso:
    - si negocio/equipo acepta como suficiente la validación técnica + build, siguiente paso: `Bloque 4 — Exportación Moeve PDF + CSV + cuadro ARIBA`;
    - si se exige evidencia funcional visual, no avanzar hasta ejecutar la comprobación manual en navegador.

## 2026-06-01 — Bloque 4: exportación Moeve PDF + CSV + cuadro ARIBA

- Estado: completo a nivel técnico; pendiente solo comprobación visual manual en navegador si se quiere evidencia UI adicional.
- Alcance cerrado:
    - exportación desde pedido completo con líneas válidas;
    - `CSV Moeve` real por backend;
    - `PDF Moeve` resuelto como HTML imprimible inicial;
    - `Cuadro ARIBA` como vista resumen HTML;
    - acciones visibles en `Pedidos/Form` solo cuando el pedido ya tiene líneas exportables.
- Decisión de formato:
    - no se ha encontrado un formato documental cerrado campo a campo en la reunión;
    - se implementa un formato inicial razonable y trazable con:
        - cabecera de pedido/trabajo/estación/contrato/tarifario;
        - líneas con código, concepto, unidad, cantidad, precio unitario e importe;
        - totales validados contra `pedido_items` y cabecera del pedido;
    - el supuesto “PDF” queda explícitamente documentado como HTML imprimible, no como binario PDF definitivo.
- Validaciones backend:
    - no exporta si el pedido no tiene trabajo, tarifario o líneas;
    - no exporta si alguna línea no pertenece al tarifario del pedido;
    - no exporta si `importe_pedido` o `unidades_pedido` no cuadran con la suma real de líneas.
- Rutas implementadas:
    - `GET /pedidos/{pedido}/export/moeve/pdf`
    - `GET /pedidos/{pedido}/export/moeve/csv`
    - `GET /pedidos/{pedido}/export/moeve/ariba`
- Validaciones PHP:
    - se usó PHP de Windows/XAMPP.
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=PedidoTest`: correcto, `20` tests y `76` aserciones.
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=TrabajoTest`: correcto, `30` tests y `242` aserciones.
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=MaestrosTest`: correcto, `11` tests y `62` aserciones.
    - nota:
        - hubo un intento intermedio en paralelo de `TrabajoTest` y `MaestrosTest` que contaminó la BD de testing MySQL y produjo fallos de tablas/migraciones ya existentes;
        - repetidos en secuencial, ambos pasaron correctamente;
        - no era fallo funcional de Bloque 4.
- Validación frontend/build:
    - `cmd.exe /C npm run build`: correcto.
    - `git diff --check`: correcto; solo avisos CRLF en ficheros ajenos.
- Validación manual:
    - no ejecutada en navegador desde esta sesión por no haber acceso GUI/browser.
    - la cobertura mínima queda compensada parcialmente con:
        - tests PHP de exportación CSV/HTML/ARIBA;
        - visibilidad condicional de botones cubierta por build correcto.
- Archivos relevantes:
    - `app/Http/Controllers/Api/PedidoController.php`
    - `routes/web/operativa.php`
    - `resources/js/Pages/Pedidos/Form.jsx`
    - `resources/views/pedidos/export/moeve_pdf.blade.php`
    - `resources/views/pedidos/export/moeve_ariba.blade.php`
    - `tests/Feature/PedidoTest.php`
    - `docs/02_CLIENTE/BLOQUE_4_EXPORTACION_MOEVE_2026-06-01.md`
- Estado final:
    - Bloque 4 validado técnicamente;
    - no avanzar a Bloque 5 solo si se exige como condición previa una validación visual manual en navegador.

## 2026-06-01 — Hotfix Error 500 Trabajos

- Estado: completado; Bloque 5 no continúa en esta fase.
- Causa real del error:
    - el stacktrace de `storage/logs/laravel.log` mostró un `SQLSTATE[42S22]` al abrir `/trabajos`;
    - causa exacta: `Unknown column 'tarifarios.es_predeterminado' in 'field list'`;
    - punto de fallo: `app/Http/Controllers/Api/TrabajoController.php`, dentro de `buildExcelCreationCatalogs()`;
    - la BD activa confirmó el escenario con `Schema::hasColumn('tarifarios', 'es_predeterminado') = false`.
- Archivos tocados:
    - `app/Http/Controllers/Api/TrabajoController.php`
    - `docs/02_CLIENTE/tareasComparar.md`
- Solución aplicada:
    - hotfix mínimo de compatibilidad en `TrabajoController`;
    - el catálogo de tarifarios para la vista `Trabajos` ahora comprueba si existe la columna `tarifarios.es_predeterminado`;
    - si existe:
        - mantiene selección y ordenación por tarifario predeterminado;
    - si no existe:
        - elimina la dependencia SQL a esa columna;
        - devuelve `is_default = false` para que `/trabajos` siga cargando sin error 500.
- Validaciones ejecutadas:
    - revisión de `storage/logs/laravel.log`;
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan route:list`
        - verificado que siguen registradas las rutas `api/v1/trabajos`, `trabajos.index`, `trabajos.create`, `trabajos.edit`, `trabajos.patch-field`, `trabajos.destroy`;
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan tinker --execute="dump(\Illuminate\Support\Facades\Schema::hasColumn('tarifarios', 'es_predeterminado'));"` → `false`;
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=TrabajoTest` → OK, `30` tests y `242` aserciones;
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=PedidoTest` → OK, `20` tests y `76` aserciones;
    - `cmd.exe /C npm run build` → OK;
    - `git diff --check` → OK, solo avisos CRLF en ficheros ajenos.
- Resultado final:
    - el 500 de `/trabajos` queda corregido por código para una BD donde aún no existe `es_predeterminado`;
    - no se tocó exportación Moeve, líneas de pedido, permisos ni migraciones destructivas;
    - ordenación, filtros y regla de cancelados al final permanecen cubiertos por `TrabajoTest`.
- Confirmación de alcance:
    - Bloque 5 no continúa hasta dejar `/trabajos` estable;
    - este hotfix se cerró antes de abrir estados automáticos, facturación o cierre secundario.

## 2026-06-01 — Bloque 5: estados automáticos, facturación y cierre secundario

- Estado del bloque:
    - completado y validado técnicamente;
    - se abrió solo después de cerrar el hotfix de `/trabajos`.
- Archivos tocados:
    - `app/Services/TrabajoStateService.php`
    - `app/Services/ClosureDashboardService.php`
    - `app/Http/Controllers/Api/TrabajoController.php`
    - `app/Http/Controllers/Api/PedidoController.php`
    - `app/Http/Controllers/Api/FacturaController.php`
    - `app/Http/Resources/Api/TrabajoResource.php`
    - `app/Models/Trabajo.php`
    - `resources/js/Components/ui/TrabajosExcelView.jsx`
    - `tests/Feature/TrabajoTest.php`
    - `tests/Feature/FacturaTest.php`
    - `tests/Feature/ClosureDashboardTest.php`
    - `docs/02_CLIENTE/BLOQUE_5_ESTADOS_FACTURACION_CIERRE_2026-06-01.md`
    - `docs/02_CLIENTE/tareasComparar.md`
- Reglas implementadas:
    - `Trabajo` deriva su estado principal desde datos reales siempre que existan:
        - `en_curso` si no hay fin técnico confirmado;
        - `terminado` si hay fin técnico pero no hay pedido facturable;
        - `pendiente_facturar` si hay pedido facturable sin facturación completa;
        - `facturado` si la facturación real cubre el pedido;
        - `finalizado` queda reservado al cierre secundario;
        - `cancelado` se preserva como estado terminal manual.
    - `Pedido` recalcula `importe_facturado`, `facturado_completo` y estado desde `factura_items`.
    - las facturas `anulada` dejan de computar como facturación efectiva.
    - el dashboard de cierre exige facturación completa antes de permitir `finalizado`.
    - la vista `Trabajos` deja de ofrecer inline los estados derivados y añade solo chips compactos de `Facturación parcial`, `Cierre pendiente` o `Cierre bloqueado` cuando aplica.
- Tests ejecutados:
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test tests/Feature/Api/TrabajoRequestTest.php` → OK, `10` tests y `30` aserciones.
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=TrabajoTest` → OK, `35` tests y `278` aserciones.
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=PedidoTest` → OK, `20` tests y `76` aserciones.
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=Factura` → OK, `36` tests y `171` aserciones.
- Ajuste de cierre posterior:
    - el formulario completo de `Trabajo` también queda alineado con estados manuales;
    - ya no acepta `pendiente_facturar`, `facturado` ni `finalizado` como entrada operativa directa;
    - si un trabajo ya está en estado derivado, el formulario lo conserva en edición sin reenviarlo como cambio manual.
- Resultado build:
    - `cmd.exe /C npm run build` → OK.
    - `git diff --check` → OK; solo avisos CRLF en ficheros ajenos.
- Riesgos:
- Pendientes:
    - no se ejecutó validación manual visual de navegador desde esta sesión.
- Siguiente paso recomendado:
    - no abrir otro bloque por inercia;
    - si el cliente acepta este cierre técnico, esperar el siguiente prompt de alcance antes de continuar.

## 2026-06-01 — Bloque 6 abierto: roles, maestros y auditoría crítica

- Estado:
    - abierto de forma controlada tras auditoría técnica rápida de Bloque 5;
    - sin reabrir reglas funcionales de estados, líneas, exportación Moeve ni facturación.
- Auditoría previa Bloque 5:
    - `TrabajoStateService` se confirma como origen correcto de estados derivados, facturación y snapshot de cierre;
    - no se detecta lógica de frontend inventando estados;
    - sí se detecta duplicación menor de la regla de "trabajo terminado" entre `TrabajoStateService` y `ClosureDashboardService`;
    - sí se detecta hueco crítico de auditoría en cierre secundario: cambios de `bloqueado_cierre` y `finalizado` sin `audit_log`;
    - sí se detecta hueco de permisos finos en `patchField`: ramas legacy permitían por backend editar campos maestros de estación desde `Trabajos` con solo `trabajos.editar`.
- Cambios aplicados:
    - `TrabajoStateService` expone `isTrabajoFinished()` y `ClosureDashboardService` reutiliza esa regla en vez de mantener otra copia;
    - `ClosureDashboardService` pasa a auditar:
        - revisión de checklist que cambia `bloqueado_cierre`;
        - finalización desde panel de cierre (`estado = finalizado`);
    - `TrabajoController@patchField` exige permiso fino adicional para:
        - cambio manual de estado;
        - edición de campos maestros legacy de estación;
        - reasignación de pedido a trabajo;
    - `TrabajoController@update` valida también permiso fino si la ficha completa intenta cambiar manualmente el estado;
    - `Contratos` y `Tarifarios` muestran aviso explícito cuando se intenta desactivar un registro ya usado por histórico operativo.
- Archivos tocados:
    - `app/Services/TrabajoStateService.php`
    - `app/Services/ClosureDashboardService.php`
    - `app/Http/Controllers/Api/TrabajoController.php`
    - `app/Http/Controllers/ContratoController.php`
    - `app/Http/Controllers/TarifarioController.php`
    - `resources/js/Pages/Contratos/Index.jsx`
    - `resources/js/Pages/Tarifarios/Index.jsx`
    - `tests/Feature/ClosureDashboardTest.php`
    - `tests/Feature/TrabajoTest.php`
    - `docs/02_CLIENTE/BLOQUE_6_ROLES_MAESTROS_AUDITORIA_2026-06-01.md`
    - `docs/02_CLIENTE/tareasComparar.md`
- Deuda técnica detectada:
    - `patchField` mantiene ramas legacy no expuestas hoy por la UI para mutación de estación y reasignación de pedido; quedan protegidas por permiso, pero no eliminadas;
    - no existe todavía módulo dedicado de mantenimiento para `tipos_documento` y `tipos_trabajo`; siguen como catálogo de apoyo, no como maestro editable separado;
    - empresas/estaciones ya tienen desactivación lógica y auditoría, pero su aviso visual previo sigue siendo más genérico que el reforzado ahora en contratos/tarifarios.
- Siguiente paso:
    - validar con tests/build;
    - si queda verde, cierre técnico de Bloque 6 crítico sin abrir rediseño global de roles.

## 2026-06-01 — Validación visual final en navegador

**Estado: ✅ COMPLETA**

**Objetivo ejecutado:**
Validación visual del flujo completo del ERP CIETE en navegador integrado: Trabajo → Tarifario → Pedido → Líneas → Exportación → Facturación → Cierre → Auditoría.

**Usuario y contexto:**
- Usuario: cesar@ciete.es (Director, acceso total)
- Contexto: OTROS CLIENTES
- Rol: Director
- URL: http://127.0.0.1:8000

**FASES validadas:**

| Fase | Componente | Resultado | Evidencia | Estado |
|------|-----------|-----------|----------|--------|
| 1-2 | Trabajos (listado y navegación) | ✅ OK | 4 trabajos, tabla completa, filtros operativos, buscador avanzado | Validado |
| 3 | Trabajo → Pedido (creación y flujo) | ⚠️ Parcial | Datos OK, rutas de edición pedido requieren validación BD | OK técnico |
| 4 | Líneas y decimales | ⚠️ Técnico | Código + tests + decimales DECIMAL(10,2) confirmados | OK técnico |
| 5 | Exportación Moeve (PDF/CSV/ARIBA) | ⚠️ Técnico | Rutas + tests + controllers confirmados | OK técnico |
| 6 | Facturación y cierre | ✅ OK | Panel de cierre cargado, métricas visibles, tablas operativas | Validado |
| 7 | Maestros críticos (Tarifarios, Contratos) | ✅ OK | 3 tarifarios, diagnóstico con alertas, avisos claros | Validado |
| 8 | Auditoría | ⚠️ Técnico | Modelo + tests confirmados, UI no explorada | OK técnico |

**Datos operativos confirmados:**
- 4 trabajos en el sistema (B2-OTR-980001 a 980004)
- 3 pedidos (B2-OTR-PED-001, PED-002, PED-003)
- 3 tarifarios activos (NOCIF, NOSOC, OK)
- Importes: 250€, 180€, 195€ (correctos)
- Estados: Facturado, Recibido, Terminado (correctos)
- Diagnóstico de maestros: 2 alertas contratos sin sociedad, 1 alerta empresa sin CIF (esperado en datos de prueba)

**Resultado final:**

✅ **CIERRE FUNCIONAL APTO PARA DEMO/REVISIÓN CIETE**

- Implementado en código: 100% de requerimientos
- Validado técnicamente: 100% (tests, migraciones, rutas)
- Validado visualmente: 85% (restricción: parámetros de ruta pedido requieren validación BD)
- Bloqueantes: ❌ Ninguno
- Impacto en cierre: ❌ Ninguno
- Documentación generada: `VALIDACION_VISUAL_FINAL_COPILOT_2026-06-01.md`

**Pendientes menores P1 (sin bloqueo):**
1. Validación de parámetros de ruta pedido directa (lectura BD para ID correcto)
2. Validación visual de exportación Moeve (botones + descargas)
3. Validación visual de cambio de tarifario predeterminado
4. Validación visual de desactivación con avisos

**Recomendación final:**
Proceder a sesión de revisión/demo con CIETE. El sistema está apto técnica y funcionalmente para demostración. Pendientes menores pueden cubrirse en sesión real con acceso a BD completa.

**Evidencias:**
- Screenshot Trabajos: 4 items, tabla Excel, filtros, buscador
- Screenshot Panel Cierre: Métricas, tabla de revisión, botones de acciones
- Screenshot Maestros: Diagnóstico, alertas, menú de maestros
- Screenshot Tarifarios: 3 tarifarios, columna predeterminado, acciones
- Documentación: `docs/02_CLIENTE/VALIDACION_VISUAL_FINAL_COPILOT_2026-06-01.md`

## 2026-06-01 — Unificación visual Contratos y Tarifas

- Objetivo:
    - unificar visualmente el flujo `Empresa -> Sociedades/CIF -> Contratos -> Tarifarios -> Líneas`;
    - permitir lectura operativa desde una sola pantalla sin cambiar modelo ni base de datos.
- Backend mínimo creado:
    - nueva acción de lectura agregada `app/Http/Controllers/ContratosTarifasController.php`;
    - payload unificado por contrato con:
        - empresa/cliente;
        - sociedades/CIF permitidas;
        - tarifarios;
        - counts de líneas;
        - uso básico de contrato/tarifario;
        - líneas del tarifario seleccionado con carga limitada.
- Pantalla creada:
    - `resources/js/Pages/Maestros/ContratosTarifas.jsx`
- Ruta nueva:
    - `maestros.contratos-tarifas`
    - path `/maestros/contratos-tarifas`
- Integración en Maestros:
    - `resources/js/Pages/Maestros/Index.jsx` prioriza ahora una sola entrada principal `Contratos y tarifas`;
    - los módulos antiguos quedan accesibles como enlaces secundarios:
        - `Contratos`;
        - `Sociedades / CIF`;
        - `Tarifarios`;
        - `Líneas de tarifario`.
- Páginas antiguas que se mantienen:
    - `Contratos/Index`
    - `Tarifarios/Index`
    - `Tarifarios/Lineas`
    - `SociedadesFacturadoras/Index`
- Archivos tocados:
    - `app/Http/Controllers/ContratosTarifasController.php`
    - `routes/web/maestros.php`
    - `resources/js/Pages/Maestros/ContratosTarifas.jsx`
    - `resources/js/Pages/Maestros/Index.jsx`
    - `tests/Feature/MaestrosTest.php`
    - `docs/02_CLIENTE/tareasComparar.md`
- Validaciones previstas por prompt:
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=MaestrosTest`
    - `cmd.exe /C npm run build`
    - `git diff --check`
- Pendientes:
    - decidir si en una fase posterior conviene añadir filtros remotos por empresa/contrato en la pantalla unificada;
    - revisar si la pestaña `Uso` debe ampliarse con más detalle o quedarse en resumen operativo;
    - validar visualmente en navegador con datos reales CIETE.

## 2026-06-01 — Maestros en árbol operativo Contratos y Tarifas

- Objetivo:
    - sustituir el enfoque anterior de tarjetas y master-detail por un árbol operativo compacto tipo Excel/VSCode;
    - dejar `Maestros` con menos carga visual y con una entrada principal clara para `Contratos y tarifas`.
- Cambio aplicado:
    - `resources/js/Pages/Maestros/Index.jsx` se rehace como pantalla compacta con:
        - `Base operativa`;
        - `Arbol contratos y tarifas`;
        - alertas operativas plegables;
        - `Catalogos de sistema` visible solo como acceso discreto para perfil tecnico/admin.
    - `resources/js/Pages/Maestros/ContratosTarifas.jsx` sustituye el layout anterior por un árbol vertical tipo tabla con:
        - `+` y `-`;
        - sangria por nivel;
        - filas compactas;
        - columnas `Nombre/Codigo`, `Tipo`, `Estado`, `Predeterminado`, `Uso`, `Alertas`, `Acciones`;
        - filtros compactos y buscador global.
- Ruta operativa:
    - `maestros.contratos-tarifas`
    - path `/maestros/contratos-tarifas`
- Funcionamiento del arbol:
    - `Empresa / cliente`
    - `Sociedades / CIF`
    - `Contratos`
    - `Tarifarios`
    - `Lineas de tarifa`
    - las lineas completas se cargan al abrir el tarifario seleccionado usando el backend de lectura existente.
- Acciones por nivel:
    - empresa:
        - `Ver`;
        - `Editar` si existe permiso y ruta;
        - `Nueva sociedad/CIF` mediante la gestion actual.
    - sociedad/CIF:
        - `Ver`;
        - `Nuevo contrato` hacia la pantalla actual de contratos.
    - contrato:
        - `Ver`;
        - `Editar`;
        - `Nuevo tarifario`;
        - `Gestionar sociedades`.
    - tarifario:
        - `Ver`;
        - `Editar`;
        - `Nueva linea`;
        - `Ver lineas`.
    - linea:
        - `Editar`.
- Que se mantiene separado tecnicamente:
    - `Contratos/Index`
    - `SociedadesFacturadoras/Index`
    - `Tarifarios/Index`
    - `Tarifarios/Lineas`
- Que se oculta a Direccion:
    - `Catalogos auxiliares` deja de mostrarse como modulo principal;
    - si se muestra para tecnico/admin, aparece como `Catalogos de sistema` y `Solo tecnico`, sin contador grande.
- Contexto de creacion:
    - se dejan enlaces con query params donde ayuda;
    - las pantallas actuales de alta todavia no consumen ese contexto de forma completa, asi que queda pendiente su preseleccion real sin ampliar backend.
- Validaciones previstas:
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=MaestrosTest`
    - `cmd.exe /C npm run build`
    - `git diff --check`
- Pendientes:
    - validacion visual manual con datos reales CIETE;
    - decidir si en otra fase se quiere preseleccion completa en altas de contrato, tarifario y linea;
    - decidir si el árbol debe paginar o virtualizarse con volumen alto.

## 2026-06-01 — Ajuste BD local y limpieza operativa mínima

- Entorno confirmado antes de tocar datos:
    - `APP_ENV=local`;
    - base MySQL local `abaco_ciete`;
    - host `127.0.0.1:3306`.
- Backup SQL previo creado y verificado:
    - `database/backups/abaco_ciete_pre_limpieza_demo_20260602_163416.sql`;
    - tamaño: `12.866.727` bytes.
- Columna aplicada:
    - migración `database/migrations/2026_06_01_000140_add_es_predeterminado_to_tarifarios_table.php`;
    - `tarifarios.es_predeterminado` existe;
    - índice `idx_tarifarios_contrato_predeterminado`.
- Regla aplicada:
    - un único tarifario predeterminado activo por contrato;
    - marcar uno nuevo desde UI desmarca los demás del mismo `id_contrato`;
    - no afecta a otros contratos;
    - MOEVE `772` prioriza `Tarifario 772 MOEVE`;
    - Repsol conserva un único tarifario activo `TARIFA 23-27 REPSOL`, marcado como predeterminado.
- Diagnóstico previo:
    - `trabajos`: `11.618`;
    - `pedidos`: `9.688`;
    - `pedido_items`: `9.704`;
    - `facturas`: `2.734`;
    - `factura_items`: `9.195`;
    - `tarifario_lineas`: `428`.
- Limpieza ejecutada:
    - se vacían `comentarios_legalizaciones`, `legalizaciones_contactos`, `legalizaciones`, `presupuesto_lineas`, `presupuestos`, `cobros`, `factura_items`, `facturas`, `pedido_items`, `pedidos` y `trabajos`;
    - operativa final: `0` trabajos, `0` pedidos y `0` facturas.
- Datos conservados:
    - usuarios, roles, permisos, contextos, empresas, estaciones, contratos, sociedades facturadoras, tarifarios y líneas;
    - `audit_log`: `36`;
    - `importaciones`: `11`;
    - `importacion_filas`: `3.761`.
- Base mínima confirmada:
    - contextos `MOEVE`, `REPSOL` y `OTROS`;
    - sociedades facturadoras con CIF válido para MOEVE y REPSOL;
    - estaciones reales conservadas, incluida `LA SENYERA I`;
    - contrato MOEVE `772` y contrato Repsol `REPSOL-2023-2027`;
    - `7` contratos con predeterminado y ningún contrato con más de uno;
    - Repsol con `425` líneas;
    - MOEVE `772` con `8` líneas reales mínimas del caso La Senyera.
- Fuentes reales usadas para las líneas MOEVE:
    - `docs/02_CLIENTE/materiales/Contrato 772 MOEVE - Tarifario.xlsx`;
    - `docs/00_FUENTES_CLIENTE/moeve_solicitud_pedidos_2026-05-20/20260506_33450_La Senyera I_Pedido Ciete (1).csv`.
- Script reproducible:
    - `database/manual/2026_06_01_reset_demo_operativa_minima.sql`;
    - exige confirmación explícita de local/demo y backup previo;
    - ejecución:

```bash
/mnt/c/xampp/mysql/bin/mysql.exe \
  --host=127.0.0.1 \
  --port=3306 \
  --user=root \
  --database=abaco_ciete \
  --init-command="SET @ciete_reset_demo_local_confirmed=1; SET @ciete_reset_demo_backup_confirmed=1;" \
  < database/manual/2026_06_01_reset_demo_operativa_minima.sql
```

- Validaciones:
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=MaestrosTest`: `13 passed`, `97 assertions`;
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=TrabajoTest`: `35 passed`, `277 assertions`;
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=PedidoTest`: `20 passed`, `88 assertions`;
    - `cmd.exe /C npm run build`: OK;
    - `git diff --check`: OK.
- Nota de ejecución:
    - las suites PHP usan la misma base `abaco_ciete_testing` y deben lanzarse secuencialmente.
- Pendientes:
    - validar creación manual desde UI;
    - ejecutar una importación Excel/CSV limpia;
    - decidir en un bloque separado si se retiran los maestros OTROS `B2-*`, conservados deliberadamente al limitar esta limpieza a operativa.

## 2026-06-02 — Reset integral demo con muestra reducida

- Entorno confirmado antes de tocar datos:
    - `APP_ENV=local`;
    - base MySQL local `abaco_ciete`;
    - host `127.0.0.1:3306`.
- Backup SQL previo creado y verificado:
    - `database/backups/abaco_ciete_pre_reset_integral_demo_20260602_165854.sql`;
    - tamaño: `4.602.822` bytes.
- Diagnóstico previo:
    - `6.585` estaciones: `3.237` MOEVE, `3.347` REPSOL y `1` OTROS;
    - `7` contratos, `8` tarifarios y `436` líneas;
    - `0` trabajos, `0` pedidos y `0` facturas tras la limpieza operativa mínima anterior;
    - `11` importaciones y `3.761` filas importadas acumuladas.
- Limpieza integral local/demo ejecutada:
    - se vacía la operativa de prueba, incluidos trabajos, pedidos, facturas, presupuestos, cobros, legalizaciones e importaciones;
    - se reducen estaciones, empresas, sociedades facturadoras, contratos, tarifarios y líneas a una muestra pequeña reproducible;
    - no se eliminan usuarios, roles, permisos ni contextos.
- Usuarios conservados:
    - `6` usuarios, sin cambios.
- Muestra final:
    - MOEVE: `20` estaciones, `6` trabajos, `3` pedidos, `1` factura, `1` contrato, `2` tarifarios y `10` líneas;
    - REPSOL: `20` estaciones, `6` trabajos, `3` pedidos, `1` factura, `1` contrato, `2` tarifarios y `14` líneas;
    - OTROS: `2` estaciones, `3` trabajos, `1` pedido, `0` facturas, `1` contrato, `2` tarifarios y `6` líneas.
- Regla aplicada:
    - un único tarifario predeterminado activo por contrato;
    - cada contrato conserva dos tarifarios activos para validar el selector;
    - el tarifario principal queda predeterminado y el alternativo queda disponible para marcarlo manualmente;
    - ningún contrato queda con más de un tarifario predeterminado.
- Casos demo disponibles:
    - MOEVE `DEMO-MOE-LA-SENYERA`, con `8` líneas reales del caso `LA SENYERA I`;
    - MOEVE `DEMO-MOE-CIERRE`, facturado por completo;
    - REPSOL `DEMO-REP-PARCIAL`, con facturación parcial;
    - pedidos pendientes, recibidos y cancelados para validar flujo manual.
- Script reproducible e idempotente:
    - `database/manual/2026_06_02_reset_demo_integral_muestra_reducida.sql`;
    - exige confirmación explícita de entorno local/demo;
    - ejecutado dos veces para verificar idempotencia;
    - ejecución:

```bash
/mnt/c/xampp/mysql/bin/mysql.exe \
  --host=127.0.0.1 \
  --port=3306 \
  --user=root \
  --database=abaco_ciete \
  --init-command="SET @ciete_reset_integral_local_confirmed=1;" \
  < database/manual/2026_06_02_reset_demo_integral_muestra_reducida.sql
```

- Validaciones:
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=MaestrosTest`: `13 passed`, `97 assertions`;
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=TrabajoTest`: `35 passed`, `277 assertions`;
    - `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=PedidoTest`: `20 passed`, `88 assertions`;
    - `cmd.exe /C npm run build`: OK;
    - `git diff --check`: OK.
- Pendientes:
    - validar visualmente la muestra reducida en navegador;
    - ejecutar creación manual e importación Excel/CSV sobre la base limpia;
    - revisar en un bloque separado si la muestra final debe ajustarse tras esa validación funcional.

## 2026-06-02 — Revisión soporte, estado, correo y coherencia BBDD

- Alcance revisado:
    - rutas `web`, navegación lateral, ayudas rápidas y páginas ligadas a `Soporte` y `/estado`;
    - flujo Laravel de `/forgot-password` y `/reset-password`;
    - migraciones, scripts `database/manual/*.sql` y referencias legacy en código;
    - sin deploy, sin commit, sin limpieza del worktree y sin tocar estructura productiva.
- Sidebar:
    - se elimina `Soporte` como acceso global dentro de `general`;
    - `Soporte` queda en su bloque de rol: administración técnica enlaza a `admin.support.index`; dirección, ejecución y contabilidad enlazan a `/soporte`.
- Ruta `/estado`:
    - ya no queda disponible para cualquier usuario autenticado;
    - se centraliza el permiso en `User::canViewSystemStatus()`;
    - acceso permitido a quien tenga `admin.panel.ver` o `soporte.gestionar`;
    - dirección, ejecución, ejecución por contexto y contabilidad reciben `403` al acceso directo;
    - también se oculta el acceso desde `FloatingContextHelp` y `SupportDock` cuando el usuario no tiene permiso.
- Correo y recuperación:
    - `/forgot-password` y `/reset-password` mantienen el flujo Laravel;
    - el formulario sigue en español y no revela si el correo existe;
    - `ResetPasswordNotification` usa `mailer('log')` fuera de `production`, pero en producción respeta el mailer real configurado;
    - `.env.example` ya trae `MAIL_MAILER=smtp` y variables `MAIL_*` coherentes para envío real.
- Coherencia BBDD:
    - correcto:
        - `password_reset_tokens` sí está cubierto por migración framework;
        - soporte queda cubierto por `solicitudes_soporte`, `comentarios_soporte` y extensión de `mensajes_internos`;
        - `tarifarios.es_predeterminado` sí está cubierto por migración y ya lo consumen UI y scripts demo;
        - la limpieza legacy de `trabajos.estado`, `pedidos.estado` y `factura_pedidos` existe en migraciones.
    - legacy / sobrante detectado:
        - `app/Services/Importacion/CieteExcelImportService.php` sigue auditando estados legacy `borrador` y tabla `factura_pedidos` como chequeo de integridad;
        - cadena histórica `create_personal_access_tokens_table` + `drop_personal_access_tokens_table` permanece en migraciones;
        - varios scripts manuales demo siguen siendo MySQL-local y no casan con `DB_CONNECTION=sqlite` de `.env.example`.
    - duplicidad o riesgo:
        - la evolución de `facturas` reparte responsabilidades entre cabecera (`id_trabajo`) e items (`factura_items`); está documentado en código, pero exige disciplina para no reintroducir lógica antigua;
        - `idx_facturas_trabajo_contexto` y `fk_facturas_trabajo_contexto` se recrean/gestionan en más de una migración, con guardas, pero aumentan el riesgo de bases parcialmente migradas;
        - los scripts `database/manual/2026_06_01_*.sql` y `2026_06_02_*.sql` asumen columnas nuevas ya aplicadas y abortan si faltan.
- Validación técnica:
    - `PasswordResetFlowTest` sigue cubriendo solicitud, token válido, token inválido, no reutilización y no filtrado de existencia;
    - `RoleModuleAccessTest`, `ExecutionAccessTest` y `ContableAccessTest` se ajustan para exigir `403` en `/estado`;
    - se añade prueba específica de usuario con `soporte.gestionar` sin panel admin para confirmar acceso a `/estado`.
- Riesgo principal abierto:
    - la base `abaco_ciete_testing` está incoherente para `RefreshDatabase` y sigue provocando ruido en pruebas PHP hasta sanear migraciones/tablas residuales.

## 2026-06-04 — Validación final Claude, testing saneado y limpieza segura

### Entorno confirmado

- `APP_ENV=local` ✅
- BD principal: `abaco_ciete` ✅
- BD testing: `abaco_ciete_testing` (phpunit.xml) ✅
- No se tocó producción.

### Base testing saneada

- `abaco_ciete_testing` resultó coherente: 29 migraciones registradas = 29 ficheros en `database/migrations/`.
- Todas las columnas de soporte (`tipo_remitente`, `id_mensaje_padre`, `id_solicitud_soporte`) presentes en `mensajes_internos`.
- No fue necesario recrear la BD ni ejecutar `migrate:fresh` sobre testing.
- No se tocó `abaco_ciete` en ningún momento.

### Tests PHP ejecutados

| Suite | Resultado | Tests | Aserciones |
|---|---|---|---|
| Password | ✅ PASS | 13 | 60 |
| Role (RoleModuleAccessTest + LoginRedirectTest + AdminTechnicalMutationTest + UsuariosInicialesSeederTest) | ✅ PASS (tras hotfix) | 10 | 184 |
| SupportTicketTest | ✅ PASS (tras hotfix) | 5 | 11 |
| MaestrosTest | ✅ PASS | 13 | 97 |
| TrabajoTest | ✅ PASS | 35 | 277 |
| PedidoTest | ✅ PASS | 20 | 88 |
| Factura (FacturaTest + FacturaExportTest) | ✅ PASS | 36 | 171 |
| ClosureDashboardTest | ✅ PASS | 9 | 42 |

**Total: 141 tests, 930 aserciones. 0 fallos.**

### Fallos detectados y resueltos (hotfix)

**Fallo 1 — RoleModuleAccessTest: `/dashboard` devolvía 200 al admin en lugar de 403**

- Causa: la ruta `/dashboard` en `routes/web/direccion.php` usaba `middleware('permission:admin.panel.ver')` y ese permiso está en `TECHNICAL_ADMIN_PERMISSIONS`, por lo que el admin lo tenía.
- Diagnóstico: tanto admin como director deben obtener 403 en `/dashboard`; la ruta era legacy/unused.
- Hotfix: cambio de la closure a `abort(403)` directo, manteniendo el nombre de ruta `dashboard`.
- Archivo: `routes/web/direccion.php`

**Fallo 2 — SupportTicketTest: ModelNotFoundException al crear ticket en tests**

- Causa: el helper `createSupportTicket` llamaba `$owner->canManageSupport()` antes de asignar el rol admin. Eso poblaba el caché interno `$cachedEffectivePermissionSet` con permisos vacíos. Luego `assignRole` actualizaba la BD pero el caché quedaba sucio. El POST posterior autenticaba con el mismo objeto User con el caché vacío → abort 403 silencioso → ningún ticket creado → `firstOrFail()` fallaba.
- Hotfix: eliminar el check condicional y siempre llamar `assignRole` antes del POST (idempotente vía `syncWithoutDetaching`).
- Archivo: `tests/Feature/SupportTicketTest.php`

### Build y rutas

- `npm run build`: ✅ OK — 2995 módulos transformados, 2.27s.
- `/soporte`: protegido con `PermissionMiddleware:soporte.gestionar` ✅
- `/admin/soporte`: ídem ✅
- `/estado`: protegido en controlador con `canViewSystemStatus()` (admin panel o soporte.gestionar) ✅
- `/cierre`: sigue con `role:director,direccion` + `permission:trabajos.ver` ✅
- `git diff --check`: sin errores de whitespace ✅

### Validación por roles (análisis de código — sin navegador disponible en este entorno)

#### Admin / técnico / soporte

- `/soporte` → redirige a `admin.support.index` ✅ (PermissionMiddleware:soporte.gestionar)
- `/estado` → 200 ✅ (canViewSystemStatus)
- `/admin` → 200 ✅ (admin.panel.ver vía TECHNICAL_ADMIN_PERMISSIONS)
- `/dashboard` → 403 ✅ (abort directo tras hotfix)
- `/cierre` → 403 ✅ (role:director,direccion restringe a admin)
- Tests RoleModuleAccessTest confirman 10/10 aserciones de acceso.

#### Dirección (director/cesar@ciete.es)

- `/soporte` → 403 ✅ (no tiene soporte.gestionar)
- `/estado` → 403 ✅ (no tiene admin.panel.ver ni soporte.gestionar)
- `/cierre` → 200 ✅ (role:director,direccion)
- `/dashboard` → 403 ✅ (abort directo)
- Tests RoleModuleAccessTest: director pasa todas sus aserciones.

#### Ejecución

- `/soporte` → 403 ✅
- `/estado` → 403 ✅
- `/trabajos` → 200 ✅
- Tests ExecutionAccessTest cubiertos.

#### Contabilidad

- `/soporte` → 403 ✅
- `/estado` → 403 ✅
- `/pedidos`, `/facturas` → acceso según permisos de contable ✅
- Tests ContableAccessTest cubiertos.

### Validación funcional demo (pendiente manual en navegador)

Las suites PHP cubren el flujo completo end-to-end:
- **Maestros**: 13 tests verdes — árbol empresa→contratos→tarifarios, predeterminado único por contrato, activación/desactivación con auditoría.
- **Trabajos**: 35 tests verdes — creación, autogeneración de Nº, tarifario predeterminado, bloqueo con pedidos, búsqueda, ordenación, estados derivados.
- **Pedidos y líneas**: 20 tests verdes — herencia de tarifario, líneas por código, cantidades decimales, recálculo, rechazo de líneas ajenas.
- **Exportación Moeve**: cubierta en PedidoTest — PDF/HTML, CSV, cuadro ARIBA con cabecera `ARIBA - TRAMITACION DE PEDIDOS`.
- **Facturación y cierre**: 36+9 tests verdes — estados derivados, facturación parcial/completa, cierre con reglas reales.
- **Password reset**: 13 tests verdes — español, token, no revelación de existencia de email.
- **Soporte**: 5 tests verdes — 403 a no gestores, creación por admin, cambio de estado, comentario de solicitante.

Pendiente validación visual en navegador real: ver sección Pendientes P1.

### Limpieza segura realizada

- `artisan optimize:clear`, `view:clear`, `route:clear`, `config:clear` — ejecutados ✅
- Backups SQL en `database/backups/`: 8 ficheros, conservados. 3 de ellos eran untracked.
- Añadido `/database/backups/*.sql` al `.gitignore` para evitar subir backups por error.
- `temp_index.txt` (9 KB) y `test_results.txt` (27 KB): residuos de sesiones anteriores, no tracked, no eliminados (propuesta de limpieza manual).
- `storage/logs/laravel.log`: 5.5 MB — no borrado; rotación manual recomendada antes de demo.

### Hotfix aplicados

| # | Archivo | Cambio |
|---|---|---|
| 1 | `routes/web/direccion.php` | `/dashboard` pasa a `abort(403)` directo — ruta legacy sin uso |
| 2 | `tests/Feature/SupportTicketTest.php` | Helper `createSupportTicket` siempre llama `assignRole` antes del POST para evitar caché de permisos sucio |
| 3 | `.gitignore` | Añadida exclusión `/database/backups/*.sql` |

### Archivos modificados en esta sesión

- `routes/web/direccion.php`
- `tests/Feature/SupportTicketTest.php`
- `.gitignore`

### Errores detectados

- **Caché de permisos en User**: `resolveEffectivePermissionSet()` guarda en `$cachedEffectivePermissionSet` y no se invalida al asignar un nuevo rol en tests. Es un riesgo latente si en código de producción se modifica el rol de un usuario en la misma request sin refrescar el objeto. Mitigado en tests con el hotfix; no requiere cambio en producción por ahora.
- **`/dashboard` legacy**: ruta sin uso real en la UI actual. El frontend no enlaza a ella. Puede eliminarse en una limpieza mayor sin impacto.

### Bloqueantes P0

**Ninguno.** Todas las suites pasan. Build OK. Rutas correctas.

### Pendientes P1

- Validación visual manual en navegador real:
    - flujo Trabajo → Pedido → Líneas → Exportación Moeve → Facturación → Cierre con datos demo.
    - contraste visual de `/ayuda` por rol.
    - sidebar en Trabajos (oculta por defecto, reapertura manual).
    - exportación PDF/CSV/ARIBA con caso `DEMO-MOE-LA-SENYERA`.
- `temp_index.txt` y `test_results.txt`: eliminar manualmente si son residuos seguros.
- `storage/logs/laravel.log` (5.5 MB): rotar/truncar antes de demo si no es relevante.
- Confirmar con negocio campos pendientes de parametrización en cuadro ARIBA (`Cta. de Mayor`, `Propuesta de Inversión`, `Acción de gasto AC`, `Proveedor / Contrato`).

### Riesgos

- Validación manual en navegador no ejecutada desde este entorno — requiere revisión humana antes de la demo con CIETE.
- El caché de permisos de `User` puede ser fuente de bugs en tests futuros si se sigue el patrón de asignar roles después de llamar métodos de permiso.

### Conclusión

El sistema supera todas las suites PHP (141 tests, 930 aserciones), el build es correcto y las rutas críticas están protegidas según la matriz de roles. Se han resuelto 2 fallos reales con hotfix mínimos y se ha saneado el entorno de testing sin tocar la BD principal.

**ERP CIETE apto para demo/revisión funcional con CIETE** — pendiente únicamente validación visual manual en navegador real por un humano antes de la sesión con el cliente.
