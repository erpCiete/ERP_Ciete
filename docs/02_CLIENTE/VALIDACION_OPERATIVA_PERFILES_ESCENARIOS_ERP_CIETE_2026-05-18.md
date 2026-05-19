# Validación Operativa ERP CIETE por Perfiles, Escenarios, Modos Visuales y Paginación (Fase A)

Fecha: 2026-05-18  
Ámbito: local FD26 / `abaco_ciete`  
Estado del corte: ejecución técnica y documental de validación operativa por perfiles.

## Objetivo

Validar el ERP como flujo real por perfil, en Ciete Moderno y Ciete Excel, con foco en aislamiento por contexto, ciclo trabajo->pedido->factura->cierre, permisos y paginación estándar de 10 registros por página.

## Reglas fijas de esta fase

- Pantalla inicial común: se mantiene la pantalla inicial actual del ERP para todos los roles.
- `TODOS`: prohibido como selector operativo visible.
- Creación contextual: solo desde contextos reales (MOEVE, REPSOL, OTROS CLIENTES/OTROS).
- Doble modo visual obligatorio en escenarios críticos: Moderno y Excel.
- Paginación estándar: 10 por página, conservando filtros, modo y contexto.
- Regla de acceso por perfiles: todos los roles entran por Inicio común y los permisos deben aplicarse en tres capas (menú/UI, ruta/controlador y test automático).

## Actualización Fase A.3 - administrador técnico

- `admin` pasa a ser administrador técnico: panel admin, usuarios, soporte, auditoría, mantenimiento, avisos e importaciones.
- `admin` deja de tener `dashboard`, `cierre` y mutación operativa por defecto.
- `director` conserva `dashboard`, `cierre`, usuarios, auditoría y gestión operativa, pero no panel admin técnico ni soporte global.
- La validación automática de este corte queda cerrada con:
    - `AdminAccessTest`
    - `AdminDashboardTest`
    - `AdminTechnicalMutationTest`
    - `MaintenanceModeTest`
    - `InternalCommunicationTest`
    - `ImportacionesAccessTest`
    - `ExcelModeAccessTest`
    - `RoleModuleAccessTest`
- Nota de datos: la base local actual auditada en modo read-only aún no refleja por completo la matriz nueva hasta ejecutar resincronización controlada de permisos/roles. El gap queda documentado en `docs/02_CLIENTE/MATRIZ_ADMIN_TECNICO_ERP_CIETE_2026-05-18.md`.

## Fuentes funcionales

- `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
- `docs/02_CLIENTE/AUDITORIA_ALINEACION_FUNCIONAL_ERP_CIETE_2026-05-17.md`
- `docs/02_CLIENTE/tareasComparar.md`

## Orden de ejecución aplicado

1. Inicio común y selector de contexto.
2. Trabajos (MOEVE/CEPSA, REPSOL si aplica, multicontexto).
3. Pedidos.
4. Facturas.
5. Cierre / Dirección.
6. Administración técnica.
7. Paginación transversal.

## Matriz operativa de validación

| Perfil                  | Modo visual: Ciete Moderno / Ciete Excel | Pantalla inicial        | Objetivo del usuario                               | Datos requeridos                                   | Puede crear               | Puede editar            | Puede ver                     | No debe ver/tocar                        | Contexto operativo usado          | Atasco probable                       | Mensaje esperado                            | Maestro correctivo                 | Resultado esperado                                       | Resultado real                                                         | OK/KO | Severidad            | Evidencia                                                               | Corrección propuesta                              |
| ----------------------- | ---------------------------------------- | ----------------------- | -------------------------------------------------- | -------------------------------------------------- | ------------------------- | ----------------------- | ----------------------------- | ---------------------------------------- | --------------------------------- | ------------------------------------- | ------------------------------------------- | ---------------------------------- | -------------------------------------------------------- | ---------------------------------------------------------------------- | ----- | -------------------- | ----------------------------------------------------------------------- | ------------------------------------------------- |
| Todos los perfiles      | Ambos                                    | Inicio actual ERP       | Entrar sin redirección por rol                     | Usuario activo, roles, contextos asignados         | N/A                       | N/A                     | Inicio común                  | Redirecciones por rol al entrar          | Contexto activo real              | Sesión legacy en `TODOS`              | Selección de contexto real disponible       | N/A                                | Inicio común para todos                                  | Implementado por middleware; no se alteró dashboard inicial            | OK    | menor                | `HandleInertiaRequests` comparte contexto activo real                   | Mantener smoke test de login por rol              |
| Todos los perfiles      | Ambos                                    | Inicio actual ERP       | Cambiar contexto operativo                         | Contextos permitidos del usuario                   | N/A                       | Selector activo         | Contextos reales              | Opción `TODOS` visible/seleccionable     | MOEVE/REPSOL/OTROS                | Intento de enviar `all` manualmente   | “Selecciona un contexto operativo real”     | N/A                                | Sin `TODOS` en selector ni en cambio de contexto         | Implementado: sin `TODOS` en payload y rechazo de `all` en controlador | OK    | crítico (controlado) | `HandleInertiaRequests`, `ActiveContextController`, `ContextSelector`   | Mantener test de rechazo `contexto=all`           |
| Ejecución MOEVE/CEPSA   | Moderno                                  | Inicio actual ERP       | Alta/edición de trabajo operativo                  | Estación, contrato/categoría, descripción, fechas  | Trabajo                   | Trabajo                 | Trabajos de su contexto       | Datos REPSOL                             | MOEVE/CEPSA                       | Falta contrato/estación               | Mensaje funcional de validación             | Maestros > Contratos / Estaciones  | Crear/editar sin mezclar contextos                       | Reglas de request y aislamiento en backend activas                     | OK    | mayor                | `TrabajoRequestTest`, `TrabajoTest`                                     | Completar pasada manual en FD26 con usuario real  |
| Ejecución MOEVE/CEPSA   | Excel                                    | Inicio actual ERP       | Operación rápida en tabla                          | Igual que moderno                                  | Trabajo                   | Celdas permitidas       | Catálogos de su contexto      | Cruce de pedidos/contextos               | MOEVE/CEPSA                       | Cambio de contexto con selects sucios | Mensaje de bloqueo/validación por campo     | Maestros según campo               | Operativa diaria sin mezcla                              | Edición por celda con guardas de contexto validada en tests            | OK    | mayor                | `TrabajoTest` (`patch-field`)                                           | Ampliar prueba E2E visual con FD26                |
| Ejecución REPSOL        | Moderno                                  | Inicio actual ERP       | Alta/edición con campos REPSOL                     | Aviso, tipo documento, tipo trabajo, pedido/tarifa | Trabajo                   | Trabajo                 | Solo REPSOL                   | Campos MOEVE como obligatorios en REPSOL | REPSOL                            | Faltan tipo documento/tipo trabajo    | Error de validación funcional               | Maestros > Tipos documento/trabajo | Flujo REPSOL propio                                      | Validado por reglas de `StoreTrabajoRequest`                           | OK    | mayor                | `TrabajoRequestTest`                                                    | Ejecutar checklist manual REPSOL con muestra FD26 |
| Ejecución REPSOL        | Excel                                    | Inicio actual ERP       | Operar flujo REPSOL en tabla                       | Idem                                               | Trabajo                   | Celdas permitidas       | Solo REPSOL                   | Mezcla con MOEVE                         | REPSOL                            | Selector/campos ambiguos              | Mensaje funcional contextual                | Maestros contextuales              | Sin mezcla de lógica                                     | Cobertura automática parcial; validación manual pendiente              | KO    | mayor                | Pendiente prueba manual guiada                                          | Ejecutar sesión manual perfil `repsol@ciete.es`   |
| Ejecución multicontexto | Moderno                                  | Inicio actual ERP       | Alternar contextos sin mezclar datos               | Contextos permitidos                               | Por contexto real         | Por contexto real       | Contexto activo               | Crear/operar desde `TODOS`               | MOEVE/REPSOL/OTROS                | Arrastre de datos al cambiar contexto | Feedback de contexto activo y validaciones  | Maestros por contexto              | Cambio seguro entre contextos reales                     | `TODOS` eliminado del selector; guardas de creación siguen activas     | OK    | crítico (controlado) | `ActiveContextController`, `ContextCreationGuardTest`                   | Añadir test UI de reset de selects cruzados       |
| Ejecución multicontexto | Excel                                    | Inicio actual ERP       | Alternar contextos y trabajar en tablas            | Contextos + catálogos                              | Por contexto real         | Por contexto real       | Datos del contexto actual     | Mezcla entre estaciones/pedidos/tarifas  | MOEVE/REPSOL/OTROS                | Datos cacheados de contexto previo    | Mensaje/estado de contexto visible          | N/A                                | No mezcla de datasets                                    | Cobertura backend OK; verificación manual UI pendiente                 | KO    | mayor                | Pendiente manual FD26                                                   | Ejecutar checklist de cambio de contexto en Excel |
| Contable                | Moderno                                  | Inicio actual ERP       | Facturar por items con control fiscal              | Pedido, items, sociedad/CIF, contrato/tarifa       | Factura                   | Factura no finalizada   | Datos económicos del contexto | Administración técnica                   | Contexto contable activo          | Sociedad/CIF no válida                | Error funcional indicando maestro           | Maestros > Sociedades facturadoras | Factura parcial/completa con bloqueos correctos          | Cobertura automática de reglas facturación presente                    | OK    | crítico (controlado) | `FacturaTest`                                                           | Completar recorrido manual parcial/completa       |
| Contable                | Excel                                    | Inicio actual ERP       | Facturar en vista tabla                            | Igual que moderno                                  | Factura                   | Factura                 | Económico por contexto        | Mezcla de contextos en items             | Contexto real                     | Items de otro contexto                | Bloqueo por validación de contexto/empresa  | Maestros contractuales             | Facturación consistente                                  | Cobertura automática parcial; manual pendiente                         | KO    | mayor                | Pendiente manual FD26                                                   | Prueba manual con facturas parciales en Excel     |
| Dirección / Cesar       | Moderno                                  | Inicio actual ERP       | Revisar cierre y no cobrado                        | Trabajos, pedidos, facturas, estados, trazabilidad | Revisiones/cierre         | Marcado revisión/cierre | Pendientes por cerrar/cobrar  | Pantallas técnicas como flujo principal  | Multi-contexto real (sin `TODOS`) | Trabajo bloqueado sin causa clara     | Motivo de bloqueo/checklist                 | N/A                                | Detectar hechos no cobrados y bloquear cierre incorrecto | Dashboard operativo activo; ahora con paginación 10                    | OK    | mayor                | `ClosureDashboardTest` + cambios en `Cierre/Dashboard.jsx`              | Revisar mensajes finos con Cesar en UAT           |
| Dirección / Cesar       | Excel                                    | Inicio actual ERP       | Revisar equivalentes de listado                    | Listados de trabajos/pedidos/facturas              | N/A                       | N/A                     | Listados equivalentes         | Falta equivalente directo cierre         | N/A                               | Vista no equivalente                  | “No aplica en este modo visual” documentado | N/A                                | Equivalencia explícita                                   | No aplica: cierre dedicado en panel dirección                          | OK    | menor                | Alcance funcional del módulo cierre                                     | Mantener trazabilidad en docs                     |
| Administración técnica  | Moderno                                  | Inicio actual ERP       | Gestión usuarios/roles/contextos/soporte/auditoría | Catálogos admin + filtros                          | Usuarios, avisos, soporte | Usuarios/roles/permisos | Logs y tickets                | Decisiones funcionales de negocio        | Contexto admin                    | Filtros largos sin paginar            | Navegación de páginas y conteo visible      | N/A                                | Operación admin sin degradar ERP operativo               | Paginación estandarizada a 10 en usuarios, auditoría y soporte         | OK    | mayor                | `Admin/UserController`, `AuditLogController`, `SupportTicketController` | Añadir test de paginación admin si no existe      |
| Administración técnica  | Excel                                    | Inicio actual ERP       | Vista equivalente solo si existe                   | N/A                                                | N/A                       | N/A                     | N/A                           | Flujo inexistente forzado                | N/A                               | No hay pantalla equivalente           | “No aplica en este modo visual”             | N/A                                | No forzar modo inexistente                               | No aplica (paneles admin dedicados)                                    | OK    | menor                | Alcance actual de vistas admin                                          | Sin cambios                                       |
| Paginación transversal  | Moderno                                  | Módulos con listados    | Operar volumen sin perder filtros/contexto         | Meta de paginación + filtros                       | N/A                       | N/A                     | X-Y de Z + página             | Saltos de contexto al paginar            | Contexto activo real              | Perder filtros al navegar             | Mantener filtros/contexto/modo              | N/A                                | 10 por página y navegación estable                       | Implementado en listados objetivo                                      | OK    | crítico (controlado) | Cambios en controladores y vistas                                       | Mantener pruebas por módulo                       |
| Paginación transversal  | Excel                                    | Vistas tabla operativas | Igual que moderno                                  | Igual                                              | N/A                       | N/A                     | Igual                         | Solo moderno paginado                    | Contexto activo real              | Sin controles en tabla Excel          | Controles visibles y estado de página       | N/A                                | Consistencia entre modos                                 | Implementado para listas principales; quedan validaciones manuales     | KO    | mayor                | Pendiente checklist visual FD26                                         | Ejecutar validación manual por perfil en Excel    |

## Checklist manual por perfil, escenario y modo visual

### 1) Inicio comun y selector de contexto

- [ ] Login `admin@ciete.es`: entra en pantalla inicial comun.
- [ ] Login `cesar@ciete.es`: entra en pantalla inicial comun.
- [ ] Login `contable@ciete.es`: entra en pantalla inicial comun.
- [ ] Login `moeve@ciete.es` y `repsol@ciete.es`: misma pantalla inicial comun.
- [ ] Selector de contexto: no aparece `TODOS`.
- [ ] Cambio de contexto solo entre contextos reales permitidos.

### 2) Ejecución MOEVE/CEPSA (Moderno + Excel)

- [ ] Crear trabajo en contexto MOEVE/CEPSA.
- [ ] Editar trabajo existente del mismo contexto.
- [ ] Ver solo estaciones/contratos del contexto.
- [ ] Intento de mezclar datos de REPSOL: bloqueado.
- [ ] Trabajo terminado sin pedido: estado visible y no cierre indebido.

### 3) Ejecución REPSOL (Moderno + Excel)

- [ ] Contexto REPSOL disponible (si aplica en instalacion).
- [ ] Alta con `id_tipo_documento` y `id_tipo_trabajo` obligatorios.
- [ ] No exige reglas MOEVE indebidamente.
- [ ] Pedido/tarifa en contexto correcto.
- [ ] Sin mezcla con MOEVE/CEPSA.

### 4) Ejecución multicontexto (Moderno + Excel)

- [ ] Cambiar entre contextos reales permitidos.
- [ ] Al cambiar contexto, no arrastrar estaciones/pedidos/tarifas/sociedades de otro contexto.
- [ ] No hay opción operativa `TODOS`.

### 5) Pedidos (Moderno + Excel)

- [ ] Alta pedido desde trabajo del contexto activo.
- [ ] Items desde línea de tarifa o carga permitida.
- [ ] Bloqueo de mezcla entre contextos/trabajos.
- [ ] Paginación 10 por página + filtros persistentes.

### 6) Facturas / Contable (Moderno + Excel)

- [ ] Factura parcial y completa por items.
- [ ] Bloqueo por sociedad/CIF inválida.
- [ ] Mensaje funcional orientado a maestro correctivo.
- [ ] Paginación 10 por página + filtros persistentes.

### 7) Cierre / Dirección (Moderno)

- [ ] Ver pendientes de cierre y estados.
- [ ] Intento de cerrar trabajo bloqueado sin checklist: bloqueado.
- [ ] Cierre de trabajo listo: permitido.
- [ ] Paginación 10 por página dentro del listado de cierre.

### 8) Administración técnica (Moderno)

- [ ] Usuarios: listado paginado 10.
- [ ] Auditoría: listado paginado 10.
- [ ] Soporte/tickets: listado paginado 10.
- [ ] Sin impacto sobre pantalla inicial común ni flujo operativo.

## Tests automáticos críticos (fase A)

- `AdminAccessTest`
- `AdminDashboardTest`
- `AdminTechnicalMutationTest`
- `MaintenanceModeTest`
- `InternalCommunicationTest`
- `ImportacionesAccessTest`
- `ExcelModeAccessTest`
- `RoleModuleAccessTest`
- `ContextCreationGuardTest`
- `TrabajoTest`
- `TrabajoRequestTest`
- `PedidoTest`
- `FacturaTest`
- `ClosureDashboardTest`
- `npm run build`

Resultado de ejecución en este corte:

- `AdminAccessTest`: PASS
- `AdminDashboardTest`: PASS
- `AdminTechnicalMutationTest`: PASS
- `MaintenanceModeTest`: PASS
- `InternalCommunicationTest`: PASS
- `ImportacionesAccessTest`: PASS
- `ExcelModeAccessTest`: PASS
- `RoleModuleAccessTest`: PASS (5 tests, 41 assertions)
- `ContextCreationGuardTest`: PASS (6 tests, 39 assertions)
- `TrabajoTest`: PASS (19 tests, 96 assertions)
- `TrabajoRequestTest`: PASS (6 tests, 19 assertions)
- `PedidoTest`: PASS (11 tests, 43 assertions)
- `FacturaTest`: PASS (20 tests, 74 assertions)
- `ClosureDashboardTest`: PASS (7 tests, 34 assertions)
- `npm run build`: PASS

Resumen adicional A.3:

- Subconjunto específico de acceso admin técnico: `47` tests PASS, `426` assertions PASS.

## Criterio de salida

- Objetivo para declarar bloque listo: **0 KO criticos**.
- KO mayores/menores solo aceptables si tienen workaround claro y registro con corrección propuesta.

## Ampliacion de control de acceso (cobertura global ERP)

- La validación por perfiles no se limita a Trabajos: cubre inicio, perfil, contexto, trabajos, pedidos, facturas, cierre, dashboard, clientes, estaciones, maestros, administración, auditoría, soporte, mensajes, avisos, mantenimiento e importaciones.
- La regla obligatoria queda fijada en tres capas: `menu/UI` + `ruta/controlador` + `test automatizado`.
- Ciete Moderno y Ciete Excel comparten la misma politica de acceso por backend; la vista no puede abrir modulos prohibidos por rol.
- En cualquier 403 funcional se muestra salida clara para el usuario: mensaje de permiso y boton `Volver al inicio`.
