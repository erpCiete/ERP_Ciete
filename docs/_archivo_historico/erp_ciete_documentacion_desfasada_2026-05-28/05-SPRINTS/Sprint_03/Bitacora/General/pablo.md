# Bitacora GENERAL · Pablo

## General

### Objetivo de la semana

- Módulo de Trabajos (Obras) CRUD completo con parametrización MOEVE/REPSOL
- Infraestructura base de importación Excel/CSV
- Coordinar tracks BACK, FRONT y DOCS

## 2026-04-13

### Objetivo del dia

- Cerrar mejoras de seguridad y pulido de Sprint 02
- Planificar Sprint 03 completo
- Crear biblia de desarrollo

### Tareas realizadas

- Seguridad: rate limiting login mejorado (5 intentos/5 min, decay 300s)
- StatusController: filtrado role-based (admin 6 tarjetas, user 3 simplificadas)
- Status.jsx: renderizado condicional con prop isAdmin
- i18n fixes: eliminado "sol/luna", correos ejemplo a pablo@ciete.es
- Help.jsx: nota visibilidad admin/usuario en sección estado
- Manual ayuda: actualizado status section con "(solo admin)"
- Versión actualizada: v1.1.0 → v1.2.0
- Creada BIBLIA_DESARROLLO.md (guía completa para este y futuros sprints)
- Creado SPRINT_03_PLAN.md (plan detallado por tracks BACK/FRONT/DOCS)
- Actualizado Plan_Detallado_Sprints.md (inventario, módulos, stack)

### Archivos tocados

- app/Http/Controllers/StatusController.php (role-based + version bump)
- app/Http/Requests/Auth/LoginRequest.php (decay 300s)
- lang/es/auth.php, lang/en/auth.php (mensajes throttle)
- resources/js/Pages/Status.jsx (isAdmin prop)
- resources/js/Pages/Help.jsx (visibility info box)
- resources/js/i18n/locales/es.js, en.js (múltiples fixes)
- docs/BIBLIA_DESARROLLO.md (NUEVO)
- docs/05-SPRINTS/Sprint_03/SPRINT_03_PLAN.md (NUEVO)
- docs/05-SPRINTS/Plan_Detallado_Sprints.md (actualizado)

### Errores / bloqueos

| Hora | Error/Bloqueo              | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
| ---- | -------------------------- | ------------------------- | ------------- | ------------------------ |
| —    | Sin errores significativos | —                         | —             | —                        |

### Decisiones tomadas

- Version bump a v1.2.0 (SemVer minor: nuevas features seguridad + role-based status)
- Rate limit: 300s (5 min) es balance profesional entre seguridad y usabilidad
- Status page: usuarios ven 3 tarjetas (app versión, mail estado, mantenimiento) — suficiente para saber que el sistema funciona sin exponer infraestructura
- Importación Excel: usar phpoffice/phpspreadsheet directamente (más control que laravel-excel)
- Sprint 03 incluye inicio de importaciones (infraestructura + primer tipo) además de obras

### Pendiente para mañana

- Empezar TrabajoController CRUD
- Verificar que modelos de soporte (Contacto, Direccion, etc.) están completos
- Coordinar que FRONT empiece con Trabajos/Index.jsx

### Handoff

- Plan Sprint 03 listo. Equipo alineado. BACK y FRONT pueden empezar en paralelo.

## 2026-04-14

### Objetivo del dia

- Coordinar arranque paralelo de tracks BACK y FRONT del Sprint 03
- Revisar y mergear primeras entregas del equipo

### Tareas realizadas

- Revisión y merge de PRs del equipo:
    - PR #13 feature/S03-FRONT-TareaF04b-MiguelTaborda (tabla condicional)
    - PR #14 feature/F02-hook-DBascope (hook de navegación)
    - PR #15 feature/S03-FRONT-index (Trabajos/Index.jsx, TrabajosColumnas, BadgeTrabajo, hook useTrabajos)
    - PR #16 feature/B03-04-Eduardo (rutas API y web para Trabajos e Importaciones)
    - PR #17 feature/B03-01-AlexPuma (StoreTrabajoRequest, UpdateTrabajoRequest)
    - PR #18 back-dev merge
    - PR #19 front-dev merge (Trabajos/Index + AuthenticatedLayout sidebar + i18n)
- Coordinación con Eduardo (rutas), Alex (validación), Daniel B. (hook), Daniel L. (index), Miguel (tabla condicional)
- Gestión del tablero de proyecto en GitHub

### Archivos tocados

- No archivos propios. Rol de coordinación, revisión y merge.

### Errores / bloqueos

| Hora | Error/Bloqueo                                    | Impacto (Alto/Medio/Bajo) | Accion tomada      | Estado (Abierto/Cerrado) |
| ---- | ------------------------------------------------ | ------------------------- | ------------------ | ------------------------ |
| —    | Conflictos menores entre front-dev y feature PRs | Bajo                      | Resueltos en merge | Cerrado                  |

### Decisiones tomadas

- Mergear PRs en orden: primero back (rutas + validación), luego front (index + hooks)
- Daniel L. y Daniel B. trabajan en paralelo: Index.jsx y hook respectivamente
- front-dev se mergea después de que back-dev tenga las rutas

### Pendiente para mañana

- Carlos Puchol: TrabajoResource (B03-03)
- Chad: seeders demo (B03-07)
- Daniel B.: Trabajos/Form.jsx
- Revisar calidad del código entregado

### Handoff

- Index de trabajos funcional con tabla, filtros y paginación
- Rutas API y web registradas

## 2026-04-15

### Objetivo del dia

- Revisar entregas de back (Resource, seeders, Form Request)
- Coordinar front para Trabajos/Form.jsx

### Tareas realizadas

- Revisión y merge de PRs:
    - PR #20 feature/B03-03-CarlosPuchol (TrabajoResource con filtrado por contexto)
    - PR #22 feature/S03-FRONT-form-trabajos (Trabajos/Form.jsx con campos condicionales MOEVE/REPSOL)
- Chad: seeders demo ampliados (commit 090db75 — trabajos MOEVE y REPSOL para pruebas)
- Carlos Puchol: namespace fix en TrabajoResource (9d3ab5f)
- Daniel B.: formulario de creación de trabajos (0c347e5)

### Archivos tocados

- No archivos propios. Coordinación y revisión de PRs.

### Errores / bloqueos

| Hora | Error/Bloqueo                                                       | Impacto (Alto/Medio/Bajo) | Accion tomada                            | Estado (Abierto/Cerrado) |
| ---- | ------------------------------------------------------------------- | ------------------------- | ---------------------------------------- | ------------------------ |
| —    | Namespace incorrecto en TrabajoResource (App\Http\Resources vs Api) | Medio                     | Carlos Puchol corrigió en commit 9d3ab5f | Cerrado                  |

### Decisiones tomadas

- TrabajoResource actúa como "firewall de contexto": filtra campos MOEVE/REPSOL en la capa de serialización
- Form.jsx usa `usePage().props.auth.user.contexto_ids` para renderizado condicional

### Pendiente para mañana

- Eduardo: revisión TrabajoController + inicio ImportacionController
- Integrar seeders de Chad en back-dev
- Resolver bugs de navegación al listado de obras

### Handoff

- Resource listo. Form.jsx con campos condicionales. CRUD de trabajos casi completo.

## 2026-04-16

### Objetivo del dia

- Integrar back-dev con todos los fixes acumulados
- Coordinar resolución de errores 500 en navegación a obras

### Tareas realizadas

- Revisión y merge de PRs:
    - PR #21 feature/B03-02 (TrabajoController CRUD completo + TrabajoTest — Alex Puma, revisado por Eduardo)
    - PR #23 feature/B03-07-Chad (seeders demo ampliados)
    - PR #24 front-dev (Form.jsx + bitácora Daniel B.)
    - PR #26 back-dev (acumulado: controller, resource, seeders, bitácoras BACK)
- Eduardo: fix namespace TrabajoController (be96bf8), fix error 500 MissingValue en EstacionResource (9a9aacc)
- Daniel L.: resolución de problemas en ventanas de obras y formulario (31bb113)

### Archivos tocados

- No archivos propios. Coordinación, revisión y gestión de merges.

### Errores / bloqueos

| Hora | Error/Bloqueo                                           | Impacto (Alto/Medio/Bajo) | Accion tomada                                               | Estado (Abierto/Cerrado) |
| ---- | ------------------------------------------------------- | ------------------------- | ----------------------------------------------------------- | ------------------------ |
| —    | Error 500 al navegar a obras (MissingValue en Resource) | Alto                      | Eduardo refactorizó con `relationLoaded()` (commit 9a9aacc) | Cerrado                  |
| —    | Namespace incorrecto TrabajoController (web.php)        | Alto                      | Corregido por Eduardo: apuntar a Api\ (commit be96bf8)      | Cerrado                  |

### Decisiones tomadas

- Mergear back-dev con todo acumulado antes de los arreglos finales
- El error 500 en Resources se resuelve con `relationLoaded()` como patrón obligatorio
- Priorizar que el CRUD funcione de punta a punta antes de empezar importaciones

### Pendiente para mañana

- Eduardo: ExcelParserService + ImportacionController
- Arreglos finales y merge de hotfixes
- Daniel B.: bitácora final

### Handoff

- CRUD Trabajos funcional de punta a punta. 6 PRs mergeados en develop.

## 2026-04-17

### Objetivo del dia

- Cerrar Sprint 03: últimos arreglos, importaciones (Eduardo), bitácoras finales
- Evaluar qué queda fuera del sprint

### Tareas realizadas

- Revisión y merge de PRs finales:
    - PR #27 Arreglos (Eduardo: ImportacionController, ExcelParserService, StoreImportacionRequest, fix validación formularios)
    - PR #28 Arreglos (bitácoras Daniel L. y Eduardo, API contract Trabajos)
- Eduardo completó flujo importación: subida → preview → confirm con transacciones y ChunkReadFilter
- Daniel B.: bitácora actualizada (feature/bitacora-DanielBascope — no mergeada, pendiente)

### Archivos tocados

- No archivos propios. Coordinación y merge de últimos PRs.

### Errores / bloqueos

| Hora | Error/Bloqueo                                              | Impacto (Alto/Medio/Bajo) | Accion tomada                     | Estado (Abierto/Cerrado)  |
| ---- | ---------------------------------------------------------- | ------------------------- | --------------------------------- | ------------------------- |
| —    | Bitácora Daniel B. no se mergeó a develop (rama pendiente) | Bajo                      | Se deja para integrar post-sprint | Cerrado (integrado 04-19) |

### Decisiones tomadas

- Importación Excel queda como infraestructura implementada pero no se abre UI en sidebar todavía
- La integración final, tests reales y panel admin se harán post-sprint como tarea de cierre
- Daniel B. bitácora se integrará por cherry-pick
- Sprint 03 cierra con: CRUD Trabajos completo (back+front), importación backend, seeders demo

### Pendiente post-sprint

- Integrar bitácora Daniel B. desde feature/bitacora-DanielBascope
- Corregir TrabajoTest (roto: usa `assignRole()` que no existe, factory faltante)
- Dashboard con datos reales (actualmente closure inline)
- Panel Admin: CRUD usuarios, auditoría

### Handoff

- Sprint 03 cerrado. 10 PRs mergeados (#19–#28). CRUD Trabajos operativo. Importación backend lista.

## 2026-04-19 — Integración post-sprint

### Objetivo del dia

- Unificar ramas y cerrar trabajo pendiente de Sprint 03
- Implementar panel admin completo
- Preparar Sprint 04

### Tareas realizadas

- **FASE 1 — Unificación de ramas:**
    - Creada rama `chore/unificacion-sprint03-admin-sprint04` desde `origin/develop` (5823b80)
    - Verificado: `back-dev` y `front-dev` con 0 commits por delante de develop (todo ya mergeado)
    - Cherry-pick de bitácora Daniel B. desde `feature/bitacora-DanielBascope` (4ea4b6b → ba0f69a, diff vacío = idéntico)

- **FASE 2 — Cierre Sprint 03:**
    - Creado `DashboardController` invocable con datos reales (obras, pedidos, legalizaciones filtradas por contexto)
    - Creado `TrabajoFactory` (no existía — necesario para tests)
    - Reescrito `TrabajoTest`: setUp con ContextoCliente::factory (IDs explícitos 1,2), seeders reales, `roles()->attach()` en vez de `assignRole()`, Vite mock, assertions Inertia
    - 5/5 tests pasan: guest redirect, context isolation, validación MOEVE, cerrado protegido, admin override

- **FASE 3 — Panel Admin:**
    - `Admin\DashboardController`: stats reales (obras, pedidos, facturación, legalizaciones, clientes, estaciones) + actividad audit_log
    - `Admin\UserController`: CRUD completo (index con búsqueda/filtro/paginación, create, store, edit, update, toggle con autoprotección, audit)
    - `Admin/Users/Index.jsx`: tabla con búsqueda, filtro activo, badges roles/contexto, acciones toggle/editar
    - `Admin/Users/Form.jsx`: formulario crear/editar con selección de roles y contextos
    - `Admin/Audit/Index.jsx`: visor de audit_log con búsqueda y paginación
    - `Admin/Dashboard.jsx` actualizado: enlaces a nuevos módulos, módulos placeholder marcados
    - 9 rutas admin registradas en `routes/web.php`
    - i18n: secciones `adminUsers` + `adminAudit` en es.js y en.js

- **FASE 4 — Sprint 04:**
    - Creado `docs/05-SPRINTS/Sprint_04/SPRINT_04_PLAN.md` con plan Pedidos + Facturas

### Archivos tocados

- `app/Http/Controllers/DashboardController.php` (NUEVO — reemplaza closure inline)
- `app/Http/Controllers/Admin/DashboardController.php` (NUEVO)
- `app/Http/Controllers/Admin/UserController.php` (NUEVO)
- `database/factories/TrabajoFactory.php` (NUEVO)
- `resources/js/Pages/Admin/Dashboard.jsx` (editado — enlaces módulos)
- `resources/js/Pages/Admin/Users/Index.jsx` (NUEVO)
- `resources/js/Pages/Admin/Users/Form.jsx` (NUEVO)
- `resources/js/Pages/Admin/Audit/Index.jsx` (NUEVO)
- `resources/js/i18n/locales/es.js` (editado — secciones admin)
- `resources/js/i18n/locales/en.js` (editado — secciones admin)
- `routes/web.php` (editado — rutas admin)
- `app/Models/User.php` (editado — `getRouteKeyName()`)
- `tests/Feature/TrabajoTest.php` (reescrito)
- `docs/05-SPRINTS/Sprint_04/SPRINT_04_PLAN.md` (NUEVO)

### Errores / bloqueos

| Hora | Error/Bloqueo                                            | Impacto (Alto/Medio/Bajo) | Accion tomada                                                    | Estado (Abierto/Cerrado) |
| ---- | -------------------------------------------------------- | ------------------------- | ---------------------------------------------------------------- | ------------------------ |
| —    | TrabajoTest roto: `assignRole()` no existe en User model | Alto                      | Reemplazado por `roles()->attach()` + insert `usuario_contextos` | Cerrado                  |
| —    | TrabajoFactory no existía                                | Alto                      | Creado con campos reales de tabla trabajos                       | Cerrado                  |
| —    | Vite manifest no incluye Trabajos pages en testing       | Medio                     | Mock de Vite con HtmlString vacío                                | Cerrado                  |
| —    | 6 archivos M en working tree sin diff real (CRLF ghost)  | Bajo                      | `core.autocrlf=true` causa falsos M — se descartan               | Cerrado                  |

### Decisiones tomadas

- `back-dev` y `front-dev` no tenían nada nuevo: todo ya estaba en develop vía PRs. No hay nada que traer.
- Cherry-pick para bitácora Daniel B. en vez de merge (rama con 1 solo commit)
- Panel admin limitado a CRUD usuarios + auditoría. Módulos futuros marcados como "próximamente".
- Importación/exportación queda fuera del cierre — se arrastra a Sprint 04 como backlog
- TrabajoTest reescrito desde cero con pattern correcto: factory → seeder → attach → assert Inertia

### Commits realizados

| Hash      | Mensaje                                         | Archivos |
| --------- | ----------------------------------------------- | -------- |
| `ba0f69a` | docs: entrega bitácora Sprint 03 Daniel Bascope | 1        |
| `f0f16e6` | feat: Sprint 03 cierre + Panel Admin completo   | 13       |
| `7b39db5` | docs: Sprint 04 plan (Pedidos + Facturas)       | 1        |

### Handoff

- Rama `chore/unificacion-sprint03-admin-sprint04` lista para revisión
- 3 commits adelante de develop, 0 conflictos
- Working tree pendiente de limpieza final (6 diffs reales + 6 CRLF phantoms)

---

**Continuación 04-19 (tarde) — Avisos de pantalla de inicio editables desde Admin**

### Tareas realizadas (continuación)

- **Sistema de avisos editables para pantalla de inicio:**
    - Creado `Admin\NoticeController` con `update()` (valida + guarda) y `load()` estático
    - Almacenamiento en `storage/app/notices.json` — sin base de datos, bilingüe (es/en por cada mensaje)
    - Ruta `POST /admin/notices` protegida por `role:admin`
    - `Admin/Dashboard.jsx`: reemplazada tarjeta de contextos por editor de avisos con modo lectura/edición
    - Editor inline: inputs ES/EN por mensaje, añadir/eliminar (máx. 5 por categoría), guardar/cancelar
    - `Welcome.jsx`: ahora lee avisos desde prop `homeNotices` (backend) y selecciona idioma según locale activo
    - Ruta `/` actualizada para pasar `homeNotices` a Welcome via `NoticeController::load()`
    - i18n: keys `adminDashboard.notices.*` en es.js y en.js (título, categorías, placeholders, vacío, añadir)
    - 3 categorías: Avisos internos, Actualizaciones del sistema, Novedades de la empresa
    - Validación backend: array max:5 items, string max:300 chars, es y en obligatorios

### Archivos tocados (continuación)

- `app/Http/Controllers/Admin/NoticeController.php` (NUEVO)
- `app/Http/Controllers/Admin/DashboardController.php` (editado — pasa `homeNotices`)
- `resources/js/Pages/Admin/Dashboard.jsx` (editado — editor de avisos reemplaza tarjeta contextos)
- `resources/js/Pages/Welcome.jsx` (editado — lee avisos desde backend en vez de i18n estáticos)
- `resources/js/i18n/locales/es.js` (editado — keys notices)
- `resources/js/i18n/locales/en.js` (editado — keys notices)
- `routes/web.php` (editado — ruta notices + homeNotices en `/`)
- `storage/app/notices.json` (NUEVO — datos iniciales)
