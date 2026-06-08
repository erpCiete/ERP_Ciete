# Arquitectura técnica ERP CIETE

## Visión general

```
Browser (React 19)
    ↕ Inertia.js (SPA sin API REST para navegación)
Laravel 12 (PHP 8.4)
    ↕ Eloquent ORM
MySQL 8
```

El frontend es una SPA con React gestionada por Inertia.js. Las mutaciones de datos van por API REST (`/api/v1/`) usando axios. Las vistas Inertia cargan los datos iniciales desde el servidor directamente como props.

---

## Estructura de carpetas relevante

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/             # Controladores REST (JSON) y también Inertia (trabajos)
│   │   ├── Auth/            # Auth estándar Laravel
│   │   ├── Admin/           # Panel admin técnico
│   │   └── *.php            # Controladores web varios
│   ├── Middleware/
│   │   └── HandleInertiaRequests.php  # Props globales compartidas (auth, flash, contexto)
│   └── Requests/Api/        # Form requests con validación por módulo
├── Models/                  # Eloquent models con relaciones y traits
├── Services/
│   ├── TrabajoStateService.php       # Calcula estados derivados
│   ├── ClosureDashboardService.php   # Panel de cierre
│   ├── AuditLogger.php               # Registro de auditoría
│   └── Exports/
│       └── MoevePedidoExportService.php  # PDF, CSV, ARIBA
└── Support/
    ├── ContextGuard.php    # Seguridad de contexto activo
    └── TrabajoPermission.php  # Permisos específicos de trabajos

resources/js/
├── Pages/                  # Páginas Inertia (Trabajos, Pedidos, Facturas...)
├── Components/ui/          # TrabajosExcelView.jsx — tabla inline principal
├── Hooks/
│   ├── useOptimisticField.js  # Guardado optimista con detección de conflicto
│   └── useEstaciones.jsx
└── navigation/sidebar.js   # Definición de sidebar por rol

routes/web/
├── operativa.php    # Trabajos, pedidos, facturas
├── maestros.php     # Contratos, estaciones, tarifarios
├── admin.php        # Panel admin
├── soporte.php      # Solo admin técnico
└── estado.php       # Sistema técnico
```

---

## Flujo de una request típica

### Vista (GET)
```
Browser → Route → Middleware (auth, maintenance, permission) → Controller → Inertia::render('Pagina', props) → React renderiza
```

### Mutación (PATCH/POST)
```
React → axios.patch('/api/v1/trabajos/{id}/campo') → API Controller → Form Request (validación) → Model → DB → JsonResponse
```

---

## Sistema de contextos

Cada usuario tiene un **contexto activo** (MOEVE / REPSOL / OTROS CLIENTES). Este contexto determina:

- Qué datos ve en listados (los modelos tienen un global scope por contexto)
- En qué contexto puede crear registros
- Qué tarifarios y contratos están disponibles

El contexto se cambia desde `/profile`. No es configurable por el admin para cada usuario — cada usuario lo gestiona.

---

## Roles y permisos

El sistema usa dos capas:

**Roles** → controlan qué pantallas/menús aparecen:
- `director` / `direccion`
- `ejecucion`, `ejecucion_moeve`, `ejecucion_repsol`
- `contable`
- `admin` (admin técnico)

**Permisos** → controlan acceso granular a rutas y acciones:
- `trabajos.ver`, `trabajos.crear`, `trabajos.editar`, `trabajos.eliminar`
- `pedidos.*`, `facturas.*`, `estaciones.*`, `contratos.*`, `tarifarios.*`
- `importaciones.*`, `soporte.gestionar`, `admin.panel.ver`

Los permisos se asignan en `RolPermisosSeeder` y se pueden gestionar desde `/admin/usuarios`.

---

## Concurrencia optimista

El endpoint `PATCH /api/v1/trabajos/{id}/campo` implementa guardado optimista:

1. El cliente envía el campo, el nuevo valor y el `updated_at` que tenía cuando cargó la página.
2. El servidor consulta siempre la última auditoría del mismo trabajo y campo normalizado, por otro usuario, creada en los últimos 60 minutos. El `updated_at` obsoleto se mantiene como señal adicional, pero no es requisito para detectar el conflicto.
3. Si existe conflicto, devuelve **HTTP 409** con `conflict_audit_id`, campo, usuario, fecha, valor anterior, valor actual, valor intentado y `updated_at_actual`.
4. El frontend muestra el ConflictDialog como modal global de primer plano. La modal conserva el texto intentado en un campo editable y ofrece Cancelar o Guardar cambios.
5. Guardar cambios reintenta el guardado con el valor actual de la modal y el `conflict_audit_id`. Si otra persona editó después, se muestra un nuevo conflicto.

---

## Auditoría

Todos los cambios se registran en la tabla `audit_logs` con:
- `tabla`, `registro_id`, `campo`
- `valor_anterior`, `valor_nuevo`
- `id_usuario`, `id_contexto`
- `accion` (crear, actualizar, cambiar_estado, etc.)

Accesible desde `/registro-actividad` (solo Dirección y Admin).

---

## Exportación Moeve

Ver [09_EXPORTACIÓN_MOEVE_ARIBA_CORREO.md](09_EXPORTACIÓN_MOEVE_ARIBA_CORREO.md).

El servicio `MoevePedidoExportService` construye el resumen de exportación leyendo el pedido y sus relaciones. Los datos ARIBA (Cta. de Mayor, Propuesta de Inversión, Acción de gasto, Sociedad, Proveedor) se leen del contrato asociado al trabajo.

---

## Modo mantenimiento

El admin puede activar el modo mantenimiento desde `/admin`. Bloquea el acceso a todos los usuarios excepto los que pueden gestionar mantenimiento. Los cambios se aplican inmediatamente.
