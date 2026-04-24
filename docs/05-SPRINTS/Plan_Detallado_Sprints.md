# Plan detallado de sprints — ERP Ciete

> **Fecha:** 13 de abril de 2026 (actualizado: 13/04/2026 fin de jornada)  
> **Versión ERP:** v1.4.2  
> **Coordinación general:** Pablo Sevillano  
> **Stack:** Laravel 12 + Inertia.js + React 19 + MariaDB 10.4  
> **Horario:** Lunes a viernes, 09:00–14:00 (desarrollo) · 15:00–18:00 (formación)  
> **Repositorio:** `erpCiete/ERP_Ciete` · Rama de trabajo: `versionDesplegada`

### Leyenda de colores

- <span style="color:#FF6B00">**MOEVE**</span> — Color naranja. Campos, lógica y datos específicos del cliente Moeve.
- <span style="color:#DC2626">**REPSOL**</span> — Color rojo. Campos, lógica y datos específicos del cliente Repsol.
- **CIETE** — Datos internos del contexto Ciete (sin color específico).

---

## Estado actual detallado — 13/04/2026

### Infraestructura entregada (Sprint 01 + 02 + mejoras 13/04)

**Autenticación y sesiones:**

- Login/logout funcional con tracking en `sesiones_login`.
- Recuperación de contraseña funcional: notificación en español (`App\Notifications\ResetPasswordNotification`), flujo completo forgot → email → reset.
- **Protección contra fuerza bruta:** 5 intentos máximo por cada 5 minutos por combinación email+IP. Bloqueo temporal con mensaje profesional (300s decay).
- Requiere configurar SMTP real en `.env` (actualmente `MAIL_MAILER=log`). Ejemplo en `.env.example` para Gmail SMTP.

**RBAC (5 roles, 31 permisos):**

- Roles: `admin`, `usuario`, `cierre`, `gestor_moeve`, `gestor_repsol`.
- Middleware: `RoleMiddleware`, `PermissionMiddleware` registrados en `bootstrap/app.php`.
- Permisos verificables desde frontend via `auth.user.permission_slugs`.

**Multi-contexto:**

- `ContextScope` (trait `HasContext`) filtra por `whereIn(id_contexto, user.getAccessibleContextIds())`.
- Tabla pivot `usuario_contextos` define qué contextos ve cada usuario.
- admin/cierre → todos los contextos. <span style="color:#FF6B00">**gestor_moeve**</span> → solo contexto 1. <span style="color:#DC2626">**gestor_repsol**</span> → solo contexto 2. usuario → contextos 1+2.

**Base de datos:**

- 15 migraciones → 33 tablas.
- 29 modelos Eloquent creados (muchos sin controlador aún).
- Tablas con datos seed: `contextos_cliente` (3), `empresas` (3), `estaciones_servicio` (2), `contactos` (5), `tipos_documento` (9), `tipos_trabajo` (9), `unidades` (4), `usuarios` (5), `roles` (5), `permisos` (31).

**Módulos con CRUD completo:**

| Módulo     | Controller           | Resource           | Request                                          | Pages                                          | Hook            | Tests |
| ---------- | -------------------- | ------------------ | ------------------------------------------------ | ---------------------------------------------- | --------------- | ----- |
| Clientes   | `ClienteController`  | `ClienteResource`  | `StoreClienteRequest` / `UpdateClienteRequest`   | `Clientes/Index.jsx` + `Clientes/Form.jsx`     | `useClientes`   | ✅    |
| Estaciones | `EstacionController` | `EstacionResource` | `StoreEstacionRequest` / `UpdateEstacionRequest` | `Estaciones/Index.jsx` + `Estaciones/Form.jsx` | `useEstaciones` | ✅    |
| Perfil     | `ProfileController`  | —                  | —                                                | `Profile/Edit.jsx` (avatar + password)         | —               | ✅    |

**Modo mantenimiento:**

- `MaintenanceController` + `CheckMaintenanceMode` middleware.
- Botón funcional en `Admin/Dashboard.jsx`. Toggle via `POST /admin/maintenance`.
- Usuarios no-admin ven la página `Maintenance.jsx` durante mantenimiento.
- 6 tests en `MaintenanceModeTest.php`.

**Páginas de soporte (implementadas 13/04):**

- `StatusController` — estado del sistema con **filtrado por rol**: admin ve 6 tarjetas completas (BBDD, almacenamiento, correo, cola, app, mantenimiento), usuario normal ve 3 simplificadas (app versión, correo estado, mantenimiento).
- `MessageController` — mensajes internos: bandeja de entrada, enviados, archivados. Admin puede hacer broadcast a todos.
- `SupportController` — formulario de soporte técnico que envía email.
- `Help.jsx` — manual de usuario integrado con 10+ secciones, FAQ, i18n completo ES/EN.

**Seguridad (mejorada 13/04):**

- Login rate limiting: 5 intentos / 5 minutos por email+IP (decay 300s en `LoginRequest.php`).
- Mensajes de bloqueo profesionales en ambos idiomas.
- Página de estado filtra datos sensibles (driver BBDD, nombre BBDD, espacio disco, etc.) para usuarios no-admin.

**Frontend:**

- Layout: `AuthenticatedLayout.jsx` con sidebar de 4 grupos (General, Maestros, Operaciones, Informes).
- i18n: `es.js` + `en.js`, hook `useI18n`. Ejemplos corregidos (pablo@ciete.es). Sin referencias a iconos inexistentes.
- Error pages: 401, 403, 404, 419, 500, 503 con branding corporativo.
- Tema claro/oscuro persistente.
- SupportDock flotante: 5 botones (Web Ciete, Mensajes, Ayuda, Soporte, Estado).

**Tests: 58 tests, 248 assertions, todos pasando.**

### Archivos existentes — inventario completo

```
app/
  Http/
    Controllers/
      Auth/AuthenticatedSessionController.php    ← login/logout
      Auth/PasswordResetLinkController.php       ← forgot password
      Auth/NewPasswordController.php             ← reset password
      Auth/PasswordController.php                ← change password (auth)
      ClienteController.php                      ← CRUD empresas
      EstacionController.php                     ← CRUD estaciones
      LocaleController.php                       ← cambio idioma
      MaintenanceController.php                  ← toggle mantenimiento
      MessageController.php                      ← mensajes internos (inbox/sent/archived)
      ProfileController.php                      ← perfil usuario
      StatusController.php                       ← estado del sistema (role-based)
      SupportController.php                      ← soporte técnico (envío email)
    Middleware/
      CheckMaintenanceMode.php                   ← bloqueo por mantenimiento
      HandleInertiaRequests.php                  ← props compartidas Inertia
      PermissionMiddleware.php                   ← verificar permisos
      RoleMiddleware.php                         ← verificar rol
      SetLocaleFromSession.php                   ← idioma en sesión
    Requests/
      Auth/LoginRequest.php
      StoreClienteRequest.php / UpdateClienteRequest.php
      StoreEstacionRequest.php / UpdateEstacionRequest.php
    Resources/
      ClienteResource.php
      EstacionResource.php
  Models/
    ContextoCliente.php, Empresa.php, EstacionServicio.php
    Permission.php, Role.php, User.php
    + 22 modelos más (Trabajo, Pedido, Factura, etc.) — SIN controlador
  Notifications/
    ResetPasswordNotification.php                ← email reset en español
  Traits/
    ApiResponse.php, HasContext.php
  Rules/
    ValidSpanishPostalCode.php, ValidSpanishTaxId.php, ValidStationCode.php

resources/js/
  Pages/
    Auth/Login.jsx, ForgotPassword.jsx, ResetPassword.jsx
    Admin/Dashboard.jsx                          ← panel admin (datos placeholder)
    Cierre/Dashboard.jsx                         ← panel cierre (datos hardcoded)
    Clientes/Index.jsx, Form.jsx                 ← CRUD funcional
    Estaciones/Index.jsx, Form.jsx               ← CRUD funcional
    Messages/Index.jsx, Show.jsx                 ← mensajes internos
    Dashboard.jsx                                ← panel empleado (datos vacíos)
    Help.jsx                                     ← manual de usuario integrado
    Maintenance.jsx                              ← página mantenimiento
    Status.jsx                                   ← estado del sistema (role-based)
    Support.jsx                                  ← soporte técnico
    Welcome.jsx, Error.jsx
    Profile/Edit.jsx
  Layouts/AuthenticatedLayout.jsx
  i18n/locales/es.js, en.js
  hooks/useClientes.js, useEstaciones.js
  validation/formRules.js
  Components/CieteMark.jsx, GlobalPreferenceSelectors.jsx, SupportDock.jsx

routes/
  web.php    ← rutas Inertia (auth, clientes, estaciones, admin, cierre, maintenance)
  api.php    ← endpoints API (clientes, estaciones)
  auth.php   ← login, logout, forgot/reset password

tests/
  Unit/
    ExampleTest.php                              ← smoke test
    Models/ContextoClienteTest.php               ← tabla, PK, fillable, cast booleano
    Models/EmpresaTest.php                       ← fillable, scopes clientes/activas, hasMany
    Models/EstacionServicioTest.php              ← fillable, casts decimal/date/bool, belongsTo
    Traits/ApiResponseTest.php                   ← success/paginated/error JSON structure
    Traits/HasContextTest.php                    ← ContextScope, auto-inject, multi-contexto
  Feature/
    Auth/AuthenticationTest.php                  ← login render, success, bad password, logout
    Auth/LoginCsrfTest.php                       ← CSRF 419 → redirect con mensaje
    Auth/PasswordConfirmationTest.php            ← ruta deshabilitada
    Auth/PasswordUpdateTest.php                  ← PUT /password OK + rechazo
    Auth/RegistrationTest.php                    ← ruta deshabilitada
    Auth/SeededUsersAuthenticationTest.php        ← 5 usuarios seed login/verify/logout
    AdminDashboardTest.php                       ← guest redirect, no-admin 403, admin 200
    ApiAuthTest.php                              ← API login token, guest denied, /me
    Api/ClientesEstacionesApiTest.php            ← CRUD + permisos + contexto + validación CIF
    ErrorPagesTest.php                           ← 403, 404 corporativo, preview errores
    ExampleTest.php                              ← smoke GET / → login
    MaintenanceModeTest.php                      ← toggle on/off, RBAC, bypass admin, Inertia
    ProfileTest.php                              ← render, avatar update, validación catálogo
    RoleModuleAccessTest.php                     ← módulos compartidos, cierre-only, admin-only
    Seeders/UsuariosInicialesSeederTest.php      ← roles y permisos asignados correctamente

  → 21 archivos, 58 tests, 248 assertions — 100% passing (13/04/2026)
```

### Placeholders pendientes (enlaces `href="#"` en sidebar)

| Enlace             | Ubicación en sidebar | Requiere                               |
| ------------------ | -------------------- | -------------------------------------- |
| **Obras**          | Operaciones          | Sprint 03: backend + frontend completo |
| **Pedidos**        | Operaciones          | Sprint 04: backend + frontend completo |
| **Legalizaciones** | Operaciones          | Sprint 05: backend + frontend completo |
| **Informes**       | Informes             | Sprint 07: backend + frontend completo |

### Dashboards con datos placeholder

| Dashboard      | Archivo                | Estado                                                                             |
| -------------- | ---------------------- | ---------------------------------------------------------------------------------- |
| Panel empleado | `Dashboard.jsx`        | Props vacíos (`obras=[]`, `pedidos=[]`, `legalizaciones=[]`)                       |
| Panel admin    | `Admin/Dashboard.jsx`  | Props vacíos (`stats={}`, `users=[]`, `activity=[]`). Mantenimiento: **funcional** |
| Panel cierre   | `Cierre/Dashboard.jsx` | Datos **completamente hardcoded** (6 trabajos demo, checklist, flujo)              |

---

## Criterio de asignación

- Las tareas se asignan a **equipo backend** o **equipo frontend**.
- Pablo Sevillano coordina ambos equipos y realiza tareas de coordinación.
- Cada tarea indica los **archivos a crear o editar** y los **archivos calientes** (tocados por múltiple tareas).

### Convención de archivos calientes 🔥

Un archivo caliente es uno que será editado por varias tareas del mismo sprint. **Coordinar para evitar conflictos Git.**

---

## Sprint 03 · 13/04/2026 → 17/04/2026

**Foco: Módulo de Trabajos (Obras) — el corazón operativo del ERP**

La tabla `trabajos` es la entidad central. Todo (pedidos, facturas, legalizaciones) depende de ella. Este sprint debe entregar CRUD completo con parametrización por contexto (<span style="color:#FF6B00">**MOEVE**</span> vs <span style="color:#DC2626">**REPSOL**</span>).

### Archivos a CREAR

| Archivo                                      | Equipo   | Descripción                                                                                                                                                                                                      |
| -------------------------------------------- | -------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `app/Http/Controllers/TrabajoController.php` | Backend  | CRUD: index, store, show, update, destroy. Inyecta columnas visibles y filtros según contexto                                                                                                                    |
| `app/Http/Requests/StoreTrabajoRequest.php`  | Backend  | Validación condicional: <span style="color:#FF6B00">**MOEVE**</span> exige contrato+categoría. <span style="color:#DC2626">**REPSOL**</span> exige tipo_documento+tipo_trabajo                                   |
| `app/Http/Requests/UpdateTrabajoRequest.php` | Backend  | Igual que Store con reglas de update                                                                                                                                                                             |
| `app/Http/Resources/TrabajoResource.php`     | Backend  | Serializa campos según contexto: <span style="color:#FF6B00">**MOEVE**</span> → contrato, categoría, factura_ccp. <span style="color:#DC2626">**REPSOL**</span> → tipo_documento, tipo_trabajo, aviso, orden_mto |
| `resources/js/Pages/Trabajos/Index.jsx`      | Frontend | Tabla con columnas dinámicas recibidas del backend. Filtros, búsqueda, paginación                                                                                                                                |
| `resources/js/Pages/Trabajos/Form.jsx`       | Frontend | Campos comunes + condicionales: `{contexto === 1 && <Campos` <span style="color:#FF6B00">**Moeve**</span>`/>}` + `{contexto === 2 && <Campos` <span style="color:#DC2626">**Repsol**</span>`/>}`                 |
| `resources/js/hooks/useTrabajos.js`          | Frontend | Hook CRUD: getTrabajos, getTrabajo, createTrabajo, updateTrabajo, deleteTrabajo                                                                                                                                  |
| `tests/Feature/TrabajoTest.php`              | Backend  | Tests: CRUD, permisos, aislamiento contexto, validación condicional                                                                                                                                              |

### Archivos a EDITAR 🔥

| Archivo                                        | Equipo   | Cambios                                                                                                                                                | Caliente |
| ---------------------------------------------- | -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------ | -------- |
| `routes/web.php`                               | Backend  | Añadir rutas `/trabajos`, `/trabajos/crear`, `/trabajos/{id}/editar` con middleware `permission:trabajos.ver/crear/editar`                             | 🔥 Sí    |
| `routes/api.php`                               | Backend  | Añadir `apiResource('trabajos', TrabajoController)` con permisos                                                                                       | 🔥 Sí    |
| `resources/js/Layouts/AuthenticatedLayout.jsx` | Frontend | Cambiar `href="#"` → `route('trabajos.index')` en enlace Obras                                                                                         | 🔥 Sí    |
| `resources/js/i18n/locales/es.js`              | Frontend | Añadir sección `trabajos: { ... }` con traducciones de columnas, campos, estados, filtros                                                              | 🔥 Sí    |
| `resources/js/i18n/locales/en.js`              | Frontend | Ídem en inglés                                                                                                                                         | 🔥 Sí    |
| `resources/js/Pages/Dashboard.jsx`             | Frontend | Pasar props reales de obras asignadas desde el controlador                                                                                             |          |
| `routes/web.php`                               | Backend  | Dashboard: pasar trabajos del usuario autenticado como props                                                                                           | 🔥 Sí    |
| `database/seeders/DatosBaseSeeder.php`         | Backend  | Añadir seed de 4+ trabajos demo (2 <span style="color:#FF6B00">**MOEVE**</span>, 2 <span style="color:#DC2626">**REPSOL**</span>) con estados variados |          |

### Modelos faltantes a crear

| Archivo                          | Campos clave                                |
| -------------------------------- | ------------------------------------------- |
| `app/Models/Contacto.php`        | nombre, apellidos, cargo, email, telefono   |
| `app/Models/ContactoEmpresa.php` | id_contacto + id_empresa (pivot)            |
| `app/Models/Direccion.php`       | via, numero, cp, localidad, provincia, ccaa |
| `app/Models/Telefono.php`        | numero, tipo, principal                     |
| `app/Models/Email.php`           | email, tipo, principal                      |
| `app/Models/Unidad.php`          | codigo, nombre, descripcion                 |

### Lógica de contexto en Trabajos

**Campos comunes (siempre visibles):**
estado, id_estacion_servicio, responsable, descripcion, fecha_encargo, fecha_terminacion_real, importe_trabajo, observaciones

**Campos** <span style="color:#FF6B00">**MOEVE**</span> **(contexto 1):**
contrato, categoria, numero_factura_ccp, sociedad

**Campos** <span style="color:#DC2626">**REPSOL**</span> **(contexto 2):**
tipo_documento, tipo_trabajo, numero_aviso, numero_orden_mto, orden_factura (1ª o 2ª), autofactura

### Documentación de esta semana

- [ ] Actualizar `docs/04_DISENO_UI/03_Manual_Usuario.md` con sección de Trabajos (cuando esté listo).
- [ ] Crear contrato API JSON de ejemplo para Trabajos (request/response <span style="color:#FF6B00">**MOEVE**</span> y <span style="color:#DC2626">**REPSOL**</span>).

### Coordinación (Pablo Sevillano)

- Definir contrato API de trabajos (JSON de ejemplo).
- Revisar columnas visibles por contexto contra Excel Z10 y Control Trabajos <span style="color:#FF6B00">**Moeve**</span>.
- Validar parametrización por contexto end-to-end.
- Asegurar que las 2 tareas que tocan `web.php` se coordinan para evitar conflictos.

---

## Sprint 04 · 20/04/2026 → 24/04/2026

**Foco: Pedidos + Facturación base**

### Archivos a CREAR

| Archivo                                      | Equipo   | Descripción                                                                                                                                                        |
| -------------------------------------------- | -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `app/Http/Controllers/PedidoController.php`  | Backend  | CRUD pedidos. Relación trabajo→N pedidos. Incluye items anidados                                                                                                   |
| `app/Http/Controllers/FacturaController.php` | Backend  | CRUD facturas. Soporte doble factura (<span style="color:#DC2626">**REPSOL**</span>). Doble numeración (<span style="color:#FF6B00">**MOEVE**</span>: factura_ccp) |
| `app/Http/Requests/StorePedidoRequest.php`   | Backend  | Valida numero_pedido, importes. <span style="color:#DC2626">**REPSOL**</span>: importe_solicitado, uds_solicitadas                                                 |
| `app/Http/Requests/UpdatePedidoRequest.php`  | Backend  |                                                                                                                                                                    |
| `app/Http/Requests/StoreFacturaRequest.php`  | Backend  | <span style="color:#FF6B00">**MOEVE**</span>: exige factura_ccp. <span style="color:#DC2626">**REPSOL**</span>: permite orden_factura 1 o 2                        |
| `app/Http/Requests/UpdateFacturaRequest.php` | Backend  |                                                                                                                                                                    |
| `app/Http/Resources/PedidoResource.php`      | Backend  | Items anidados + campos condicionales por contexto                                                                                                                 |
| `app/Http/Resources/FacturaResource.php`     | Backend  | <span style="color:#FF6B00">**MOEVE**</span>: numero_factura_ccp + sociedad. <span style="color:#DC2626">**REPSOL**</span>: orden_factura + autofactura            |
| `resources/js/Pages/Pedidos/Index.jsx`       | Frontend | Tabla con filtros por trabajo, estado, fecha                                                                                                                       |
| `resources/js/Pages/Pedidos/Form.jsx`        | Frontend | Formulario con N items dinámicos (código tarifa, descripción, cantidad, precio, total)                                                                             |
| `resources/js/Pages/Facturas/Index.jsx`      | Frontend | Doble factura para <span style="color:#DC2626">**REPSOL**</span>, doble numeración para <span style="color:#FF6B00">**MOEVE**</span>                               |
| `resources/js/Pages/Facturas/Form.jsx`       | Frontend | Campos condicionales por contexto                                                                                                                                  |
| `resources/js/hooks/usePedidos.js`           | Frontend | Hook CRUD                                                                                                                                                          |
| `resources/js/hooks/useFacturas.js`          | Frontend | Hook CRUD                                                                                                                                                          |
| `tests/Feature/PedidoTest.php`               | Backend  | CRUD + relación con trabajo + contexto                                                                                                                             |
| `tests/Feature/FacturaTest.php`              | Backend  | CRUD + doble factura + contexto                                                                                                                                    |

### Archivos a EDITAR 🔥

| Archivo                                        | Equipo   | Cambios                                                                     | Caliente |
| ---------------------------------------------- | -------- | --------------------------------------------------------------------------- | -------- |
| `routes/web.php`                               | Backend  | Rutas de pedidos y facturas                                                 | 🔥 Sí    |
| `routes/api.php`                               | Backend  | API resources para pedidos y facturas + endpoint factura-pedido N:M         | 🔥 Sí    |
| `resources/js/Layouts/AuthenticatedLayout.jsx` | Frontend | Cambiar `href="#"` → `route('pedidos.index')`                               | 🔥 Sí    |
| `resources/js/i18n/locales/es.js`              | Frontend | Secciones `pedidos: { ... }` y `facturas: { ... }`                          | 🔥 Sí    |
| `resources/js/i18n/locales/en.js`              | Frontend | Ídem inglés                                                                 | 🔥 Sí    |
| `resources/js/Pages/Trabajos/Form.jsx`         | Frontend | Añadir pestaña/sección de pedidos y facturas dentro del detalle de trabajo  |          |
| `database/seeders/DatosBaseSeeder.php`         | Backend  | Seed: 4+ pedidos (con items) + 4+ facturas vinculadas a trabajos existentes |          |

### Documentación de esta semana

- [ ] Documentar endpoints API de pedidos y facturas (request/response).
- [ ] Actualizar manual de usuario con sección Pedidos + Facturas.

---

## Sprint 05 · 27/04/2026 → 30/04/2026

**Foco: Legalizaciones + Comentarios 1:N**

### Archivos a CREAR

| Archivo                                                     | Equipo   | Descripción                                                             |
| ----------------------------------------------------------- | -------- | ----------------------------------------------------------------------- |
| `app/Http/Controllers/LegalizacionController.php`           | Backend  | CRUD legalizaciones. Relación trabajo→N legalizaciones                  |
| `app/Http/Controllers/ComentarioLegalizacionController.php` | Backend  | CRUD anidado: `POST /legalizaciones/{id}/comentarios`                   |
| `app/Http/Requests/StoreLegalizacionRequest.php`            | Backend  | tipo_legalizacion, organismo, estado, fechas coherentes                 |
| `app/Http/Resources/LegalizacionResource.php`               | Backend  | Contactos y comentarios anidados                                        |
| `resources/js/Pages/Legalizaciones/Index.jsx`               | Frontend | Tabla con estado, tipo, organismo, trabajo vinculado                    |
| `resources/js/Pages/Legalizaciones/Form.jsx`                | Frontend | Datos + asignación contactos                                            |
| `resources/js/Components/ComentariosList.jsx`               | Frontend | Componente reutilizable: lista cronológica, edición inline, autor+fecha |
| `resources/js/hooks/useLegalizaciones.js`                   | Frontend | Hook CRUD + comentarios                                                 |
| `tests/Feature/LegalizacionTest.php`                        | Backend  | CRUD + relación + permisos + contexto                                   |

### Archivos a EDITAR 🔥

| Archivo                                        | Equipo   | Cambios                                              | Caliente |
| ---------------------------------------------- | -------- | ---------------------------------------------------- | -------- |
| `routes/web.php`                               | Backend  | Rutas legalizaciones                                 | 🔥 Sí    |
| `routes/api.php`                               | Backend  | API resource legalizaciones + comentarios nested     | 🔥 Sí    |
| `resources/js/Layouts/AuthenticatedLayout.jsx` | Frontend | Cambiar `href="#"` → `route('legalizaciones.index')` | 🔥 Sí    |
| `resources/js/i18n/locales/es.js`              | Frontend | Sección `legalizaciones: { ... }`                    | 🔥 Sí    |
| `resources/js/i18n/locales/en.js`              | Frontend | Ídem inglés                                          | 🔥 Sí    |
| `resources/js/Pages/Trabajos/Form.jsx`         | Frontend | Pestaña legalizaciones dentro del detalle de trabajo |          |
| `database/seeders/DatosBaseSeeder.php`         | Backend  | Seed legalizaciones + comentarios                    |          |

### Documentación de esta semana

- [ ] Endpoints API de legalizaciones y comentarios.
- [ ] Actualizar manual de usuario.

---

## Sprint 06 · 04/05/2026 → 08/05/2026

**Foco: Control de cierre + Cobros + Presupuestos**

### Archivos a CREAR

| Archivo                                                  | Equipo   | Descripción                                                                                            |
| -------------------------------------------------------- | -------- | ------------------------------------------------------------------------------------------------------ |
| `app/Http/Controllers/CierreController.php`              | Backend  | `POST /trabajos/{id}/cerrar` y `POST /trabajos/{id}/reabrir`. Registra fecha_cierre, id_usuario_cierre |
| `app/Http/Controllers/CobroController.php`               | Backend  | CRUD cobros vinculados a factura                                                                       |
| `app/Http/Controllers/PresupuestoController.php`         | Backend  | CRUD presupuestos con líneas anidadas                                                                  |
| `app/Http/Requests/StoreCobroRequest.php`                | Backend  |                                                                                                        |
| `app/Http/Requests/StorePresupuestoRequest.php`          | Backend  |                                                                                                        |
| `app/Http/Resources/CobroResource.php`                   | Backend  |                                                                                                        |
| `app/Http/Resources/PresupuestoResource.php`             | Backend  | Con líneas anidadas                                                                                    |
| `resources/js/Pages/Cobros/Index.jsx` + `Form.jsx`       | Frontend | CRUD cobros                                                                                            |
| `resources/js/Pages/Presupuestos/Index.jsx` + `Form.jsx` | Frontend | CRUD con líneas dinámicas                                                                              |
| `resources/js/hooks/useCobros.js`                        | Frontend | Hook CRUD                                                                                              |
| `resources/js/hooks/usePresupuestos.js`                  | Frontend | Hook CRUD                                                                                              |
| `tests/Feature/CierreTest.php`                           | Backend  | Flujo: crear → cerrar → editar (403) → reabrir → editar (OK)                                           |
| `tests/Feature/CobroTest.php`                            | Backend  |                                                                                                        |
| `tests/Feature/PresupuestoTest.php`                      | Backend  |                                                                                                        |

### Archivos a EDITAR 🔥

| Archivo                                      | Equipo   | Cambios                                                                                          | Caliente   |
| -------------------------------------------- | -------- | ------------------------------------------------------------------------------------------------ | ---------- |
| `app/Http/Controllers/TrabajoController.php` | Backend  | Añadir middleware de bloqueo: si `bloqueado_cierre=true` → 403 en update/destroy                 | 🔥 Sí      |
| `resources/js/Pages/Cierre/Dashboard.jsx`    | Frontend | **Reemplazo total**: sustituir 6 trabajos hardcoded por datos reales del backend                 | 🔥 CRITICO |
| `routes/web.php`                             | Backend  | Ruta cierre: pasar props reales. Rutas cobros, presupuestos                                      | 🔥 Sí      |
| `routes/api.php`                             | Backend  | Endpoints cierre/reapertura + cobros + presupuestos                                              | 🔥 Sí      |
| `resources/js/Pages/Trabajos/Form.jsx`       | Frontend | Botón cerrar/reabrir + indicador visual de trabajo cerrado + formulario deshabilitado si cerrado |            |
| `resources/js/i18n/locales/es.js`            | Frontend | Secciones cobros + presupuestos + mensajes de cierre                                             | 🔥 Sí      |
| `resources/js/i18n/locales/en.js`            | Frontend | Ídem                                                                                             | 🔥 Sí      |

### Documentación de esta semana

- [ ] Documentar flujo de cierre (diagrama de estados).
- [ ] Actualizar manual de usuario con sección Cierre.
- [ ] Comenzar sección de la memoria del proyecto: "Flujo de cierre de trabajos".

---

## Sprint 07 · 11/05/2026 → 15/05/2026

**Foco: Informes + Tarifarios + Contratos + Dashboards reales**

### Archivos a CREAR

| Archivo                                                | Equipo   | Descripción                                                                                             |
| ------------------------------------------------------ | -------- | ------------------------------------------------------------------------------------------------------- |
| `app/Http/Controllers/TarifarioController.php`         | Backend  | CRUD tarifarios + líneas anidadas. Importación 370 líneas <span style="color:#DC2626">**REPSOL**</span> |
| `app/Http/Controllers/ContratoController.php`          | Backend  | CRUD contratos. Tipos: marco, directo, otro. Vinculado a empresa + tarifario                            |
| `app/Http/Controllers/InformeController.php`           | Backend  | Agregaciones: trabajos por estado/tipo/responsable. Facturación acumulada. Exportación CSV/XLSX         |
| `app/Http/Resources/TarifarioResource.php`             | Backend  | Con líneas anidadas                                                                                     |
| `app/Http/Resources/ContratoResource.php`              | Backend  |                                                                                                         |
| `resources/js/Pages/Tarifarios/Index.jsx` + `Form.jsx` | Frontend | Vista de tarifario con 370 líneas. Búsqueda dentro del tarifario                                        |
| `resources/js/Pages/Contratos/Index.jsx` + `Form.jsx`  | Frontend | CRUD contratos                                                                                          |
| `resources/js/Pages/Informes/Index.jsx`                | Frontend | Filtros por contexto/fecha/estado. Gráficos barras/donut. Botón exportar                                |
| `resources/js/hooks/useTarifarios.js`                  | Frontend | Hook CRUD                                                                                               |
| `resources/js/hooks/useContratos.js`                   | Frontend | Hook CRUD                                                                                               |
| `tests/Feature/TarifarioTest.php`                      | Backend  |                                                                                                         |
| `tests/Feature/ContratoTest.php`                       | Backend  |                                                                                                         |
| `tests/Feature/InformeTest.php`                        | Backend  |                                                                                                         |

### Archivos a EDITAR 🔥

| Archivo                                        | Equipo   | Cambios                                                             | Caliente   |
| ---------------------------------------------- | -------- | ------------------------------------------------------------------- | ---------- |
| `resources/js/Layouts/AuthenticatedLayout.jsx` | Frontend | Activar enlace Informes → `route('informes.index')`                 | 🔥 Sí      |
| `resources/js/Pages/Dashboard.jsx`             | Frontend | **Integrar métricas reales** desde el backend (tarjetas con datos)  | 🔥 CRITICO |
| `resources/js/Pages/Admin/Dashboard.jsx`       | Frontend | **Integrar datos reales**: stats, users, activity                   | 🔥 CRITICO |
| `routes/web.php`                               | Backend  | Dashboard: inyectar métricas. Rutas tarifarios, contratos, informes | 🔥 Sí      |
| `routes/api.php`                               | Backend  | API tarifarios, contratos, informes, exportación                    | 🔥 Sí      |
| `resources/js/i18n/locales/es.js`              | Frontend | Secciones tarifarios, contratos, informes                           | 🔥 Sí      |
| `resources/js/i18n/locales/en.js`              | Frontend | Ídem                                                                | 🔥 Sí      |

### Documentación de esta semana

- [ ] Documentar endpoints de informes y exportación.
- [ ] Actualizar manual con Informes, Tarifarios, Contratos.
- [ ] Memoria del proyecto: añadir sección "Módulos operativos entregados".

---

## Sprint 08 · 18/05/2026 → 22/05/2026

**Foco: Importación de Excel + Auditoría + Extensiones de estaciones**

### Archivos a CREAR

| Archivo                                          | Equipo   | Descripción                                                                                                                   |
| ------------------------------------------------ | -------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `app/Http/Controllers/ImportacionController.php` | Backend  | Subida Excel → staging (`importacion_filas`). Validación. Previsualización. Confirmación                                      |
| `app/Services/ImportMoeveService.php`            | Backend  | Parseo Excel <span style="color:#FF6B00">**MOEVE**</span> (~3.583 filas). Mapeo: concesion→codigo_estacion, GPS, técnicos     |
| `app/Services/ImportRepsolService.php`           | Backend  | Parseo Excel <span style="color:#DC2626">**REPSOL**</span> (~3.275 filas). Mapeo: C.EMP→codigo_estacion, SOLRED, combustibles |
| `app/Services/ImportTarifarioService.php`        | Backend  | Parseo tarifa <span style="color:#DC2626">**REPSOL**</span> 23-27 (370 líneas) → `tarifario_lineas`                           |
| `app/Traits/Auditable.php`                       | Backend  | Trait que registra cambios en `audit_log`: crear, editar, eliminar, cerrar, reabrir                                           |
| `resources/js/Pages/Importaciones/Index.jsx`     | Frontend | Historial de importaciones: estado, fecha, resumen                                                                            |
| `resources/js/Pages/Importaciones/Upload.jsx`    | Frontend | Formulario subida: selector archivo + tipo + progress bar + vista previa validación                                           |
| `tests/Feature/ImportacionTest.php`              | Backend  | Flujo: subir → validar → previsualizar → confirmar                                                                            |
| `tests/Feature/AuditLogTest.php`                 | Backend  | Registros de auditoría automáticos                                                                                            |

### Archivos a EDITAR 🔥

| Archivo                                    | Equipo   | Cambios                                                                                                                                                                                                                              | Caliente   |
| ------------------------------------------ | -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ---------- |
| `app/Http/Resources/EstacionResource.php`  | Backend  | Añadir campos extensión: <span style="color:#FF6B00">**MOEVE**</span> (14 campos: tecnico_gestion, tecnico_obra, zona...) y <span style="color:#DC2626">**REPSOL**</span> (17 campos: codigo_solred, combustibles, tipo_estacion...) | 🔥 CRITICO |
| `resources/js/Pages/Estaciones/Form.jsx`   | Frontend | Sección de campos extra según contexto                                                                                                                                                                                               | 🔥 CRITICO |
| `resources/js/Pages/Estaciones/Index.jsx`  | Frontend | Columnas adaptativas según contexto                                                                                                                                                                                                  |            |
| `app/Models/Trabajo.php` (y otros modelos) | Backend  | Añadir `use Auditable` trait                                                                                                                                                                                                         |            |
| `routes/web.php`                           | Backend  | Rutas importación                                                                                                                                                                                                                    | 🔥 Sí      |
| `routes/api.php`                           | Backend  | API importación                                                                                                                                                                                                                      | 🔥 Sí      |
| `resources/js/i18n/locales/es.js`          | Frontend | Sección importaciones + campos extensión estaciones                                                                                                                                                                                  | 🔥 Sí      |
| `resources/js/i18n/locales/en.js`          | Frontend | Ídem                                                                                                                                                                                                                                 | 🔥 Sí      |

### Documentación de esta semana

- [ ] Documentar proceso de importación Excel (guía paso a paso).
- [ ] Documentar mapeo de campos Excel → DB para <span style="color:#FF6B00">**MOEVE**</span> y <span style="color:#DC2626">**REPSOL**</span>.
- [ ] Memoria del proyecto: sección "Estrategia de importación".

---

## Sprint 09 · 25/05/2026 → 29/05/2026

**Foco: QA completo, cierre de bugs, pulido funcional**

### Tareas (ambos equipos)

| ID    | Tarea                                                                                                                                                                                                                                                | Archivos afectados                                                              |
| ----- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------- |
| QA-01 | **Test end-to-end**: crear trabajo → pedido → factura → cobro → cierre (flujo <span style="color:#FF6B00">**MOEVE**</span> y <span style="color:#DC2626">**REPSOL**</span> separados)                                                                | Todos los controllers + pages                                                   |
| QA-02 | **Test de contextos**: admin ve todo, <span style="color:#FF6B00">**gestor_moeve**</span> solo <span style="color:#FF6B00">**Moeve**</span>, <span style="color:#DC2626">**gestor_repsol**</span> solo <span style="color:#DC2626">**Repsol**</span> | Tests existentes + nuevos                                                       |
| QA-03 | **Cierre de bugs** de sprints anteriores                                                                                                                                                                                                             | Según bugs reportados                                                           |
| QA-04 | **UX**: skeleton loading, empty states, toasts de éxito/error                                                                                                                                                                                        | Todos los `Index.jsx` + `Form.jsx`                                              |
| QA-05 | **Responsive**: verificar tablet/móvil en todas las páginas                                                                                                                                                                                          | `AuthenticatedLayout.jsx` + todas las pages                                     |
| QA-06 | **Performance**: paginación backend para > 50 registros                                                                                                                                                                                              | Controllers con `->paginate()`                                                  |
| QA-07 | **Cobertura tests ≥ 80%** en controllers y modelos                                                                                                                                                                                                   | `phpunit.xml`, todos los tests                                                  |
| QA-08 | **Validación traducciones**: sin claves pendientes en `es.js`/`en.js`                                                                                                                                                                                | Ambos archivos i18n                                                             |
| QA-09 | **Páginas de ayuda in-app** accesibles desde el dashboard, con contenido filtrado por rol (admin ve instrucciones admin, cierre ve instrucciones cierre)                                                                                             | Crear `Pages/Help/Admin.jsx`, `Pages/Help/Cierre.jsx`, `Pages/Help/General.jsx` |

### Documentación de esta semana

- [ ] Actualizar manual de usuario completo con todos los módulos implementados.
- [ ] Documentar bugs encontrados y resueltos.
- [ ] Memoria del proyecto: sección "Control de calidad".

---

## Sprint 10 · 01/06/2026 → 05/06/2026

**Foco: Entrega técnica + documentación + demo**

### Tareas

| ID     | Tarea                                                                     | Entregable                                           |
| ------ | ------------------------------------------------------------------------- | ---------------------------------------------------- |
| DOC-01 | **Manual de usuario** final y completo                                    | `docs/04_DISENO_UI/03_Manual_Usuario.md` actualizado |
| DOC-02 | **Documentación técnica**: README, estructura, despliegue                 | `README.md` actualizado, `docs/01_ORGANIZACION/`     |
| DOC-03 | **API documentation**: todos endpoints, request/response, permisos        | `docs/03_API_ERP/`                                   |
| DOC-04 | **Guión de demo**: script paso a paso para presentar el ERP               | `docs/demo_script.md`                                |
| DOC-05 | **Seed datos realistas**: trabajos, pedidos, facturas con fechas variadas | `database/seeders/DemoSeeder.php`                    |
| DOC-06 | **Verificación servidor** 130.110.232.84 con datos demo                   | Despliegue verificado                                |
| DOC-07 | **Memoria del proyecto**: sprints, decisiones, alcance                    | `docs/MemoriaProyecto/`                              |

---

## Sprint 11 · 08/06/2026 → 09/06/2026

**Foco: Remate final**

| Tarea                 | Entregable                            |
| --------------------- | ------------------------------------- |
| Últimos bugs críticos | Issues vaciadas                       |
| Ensayo de demo        | Simulación completa presentación      |
| Freeze de código      | Rama `main` estable                   |
| Entrega formal        | Repositorio + documentación + accesos |

---

## Mapa de archivos calientes por sprint

Estos archivos se editan en **múltiples sprints**. Requieren coordinación especial:

| Archivo                   | Sprint 03 | Sprint 04 | Sprint 05 | Sprint 06 | Sprint 07 | Sprint 08 |
| ------------------------- | --------- | --------- | --------- | --------- | --------- | --------- |
| `routes/web.php`          | ✏️        | ✏️        | ✏️        | ✏️        | ✏️        | ✏️        |
| `routes/api.php`          | ✏️        | ✏️        | ✏️        | ✏️        | ✏️        | ✏️        |
| `AuthenticatedLayout.jsx` | ✏️        | ✏️        | ✏️        | —         | ✏️        | —         |
| `es.js`                   | ✏️        | ✏️        | ✏️        | ✏️        | ✏️        | ✏️        |
| `en.js`                   | ✏️        | ✏️        | ✏️        | ✏️        | ✏️        | ✏️        |
| `DatosBaseSeeder.php`     | ✏️        | ✏️        | ✏️        | —         | —         | —         |
| `Dashboard.jsx`           | ✏️        | —         | —         | —         | ✏️        | —         |
| `Admin/Dashboard.jsx`     | —         | —         | —         | —         | ✏️        | —         |
| `Cierre/Dashboard.jsx`    | —         | —         | —         | ✏️        | —         | —         |
| `Trabajos/Form.jsx`       | ✏️(crear) | ✏️        | ✏️        | ✏️        | —         | —         |
| `EstacionResource.php`    | —         | —         | —         | —         | —         | ✏️        |
| `Estaciones/Form.jsx`     | —         | —         | —         | —         | —         | ✏️        |

**Recomendación**: Cada equipo edita sus archivos calientes al final de su bloque de tareas del sprint, y se hace merge coordinado antes de pasar al siguiente sprint.

---

## Resumen de módulos por sprint

| Módulo                                | Sprint | Contexto                                                                                     | Backend   | Frontend  | Tests     |
| ------------------------------------- | ------ | -------------------------------------------------------------------------------------------- | --------- | --------- | --------- |
| Auth + RBAC + Contextos               | 01 ✅  | Todos                                                                                        | ✅        | ✅        | ✅        |
| Clientes (Empresas)                   | 02 ✅  | Todos                                                                                        | ✅        | ✅        | ✅        |
| Estaciones de servicio                | 02 ✅  | Todos                                                                                        | ✅        | ✅        | ✅        |
| Modo mantenimiento                    | 02 ✅  | Admin                                                                                        | ✅        | ✅        | ✅        |
| Recuperar contraseña                  | 02 ✅  | Todos                                                                                        | ✅        | ✅        | —         |
| Mensajes internos                     | 02 ✅  | Todos                                                                                        | ✅        | ✅        | —         |
| Estado del sistema                    | 02 ✅  | Todos (role-based)                                                                           | ✅        | ✅        | —         |
| Soporte técnico                       | 02 ✅  | Todos                                                                                        | ✅        | ✅        | —         |
| Manual de ayuda in-app                | 02 ✅  | Todos                                                                                        | —         | ✅        | —         |
| Seguridad rate-limit                  | 02 ✅  | Todos                                                                                        | ✅        | —         | —         |
| **Trabajos**                          | **03** | <span style="color:#FF6B00">**MOEVE**</span> + <span style="color:#DC2626">**REPSOL**</span> | pendiente | pendiente | pendiente |
| **Pedidos + Facturas**                | **04** | <span style="color:#FF6B00">**MOEVE**</span> + <span style="color:#DC2626">**REPSOL**</span> | pendiente | pendiente | pendiente |
| **Legalizaciones**                    | **05** | Todos                                                                                        | pendiente | pendiente | pendiente |
| **Cierre + Cobros + Presupuestos**    | **06** | Todos                                                                                        | pendiente | pendiente | pendiente |
| **Informes + Tarifarios + Contratos** | **07** | <span style="color:#FF6B00">**MOEVE**</span> + <span style="color:#DC2626">**REPSOL**</span> | pendiente | pendiente | pendiente |
| **Importación Excel + Auditoría**     | **08** | <span style="color:#FF6B00">**MOEVE**</span> + <span style="color:#DC2626">**REPSOL**</span> | pendiente | pendiente | pendiente |
| QA + Pulido + Ayuda in-app            | 09     | —                                                                                            | —         | —         | —         |
| Documentación + Demo                  | 10     | —                                                                                            | —         | —         | —         |
| Entrega                               | 11     | —                                                                                            | —         | —         | —         |

---

## Requisitos funcionales trazados

| RF    | Requisito                                                                                                                       | Sprint(s)                                           |
| ----- | ------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------- |
| RF-01 | Separación total <span style="color:#DC2626">**Repsol**</span>/<span style="color:#FF6B00">**Moeve**</span> (datos y reporting) | 01 ✅ (ContextScope) + 03 (columnas parametrizadas) |
| RF-02 | Gestión completa del ciclo de obra                                                                                              | 03 + 04 + 06                                        |
| RF-03 | Relación obra-pedido-factura con coherencia                                                                                     | 04                                                  |
| RF-04 | Registro de fecha de encargo y terminación real                                                                                 | 03                                                  |
| RF-05 | Bloqueo de trabajos cerrados por rol/permiso                                                                                    | 06                                                  |
| RF-06 | Control de legalizaciones 1:N por obra                                                                                          | 05                                                  |
| RF-07 | Listado y buscador de estaciones por cliente                                                                                    | 02 ✅                                               |
| RF-08 | Control de tarifarios asociados                                                                                                 | 07                                                  |
| RF-09 | Informes por cliente, estado, tipo y empleado                                                                                   | 07                                                  |
| RF-10 | Trabajo multiusuario sin bloqueo de archivos                                                                                    | 01 ✅ (web, no Excel)                               |
| RF-11 | Trazabilidad de cambios sobre información crítica                                                                               | 08 (audit_log)                                      |
| RF-12 | Capacidad con varios miles de registros/año                                                                                     | 08 (importación) + 09 (performance)                 |
