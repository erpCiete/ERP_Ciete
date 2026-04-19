# Sprint 04 — Semana 20–24 abril 2026

> **Versión inicial:** v1.3.0 → **Versión objetivo:** v1.4.0  
> **Foco principal:** Módulo de Pedidos — CRUD completo con líneas anidadas  
> **Foco secundario:** Módulo de Facturas — CRUD con doble factura REPSOL y numeración CCP MOEVE  
> **Arrastre Sprint 03:** Importación/exportación Excel — infraestructura backend ya implementada (controller+service+request), falta UI frontend y activación en sidebar

---

## Estado heredado de Sprint 03

### Completado en Sprint 03

- CRUD Trabajos completo (back + front + tests)
- Panel Admin: dashboard con stats, CRUD usuarios, visor auditoría
- Modelos Pedido, PedidoItem, Factura ya existen con relaciones y casts
- ImportacionController + ExcelParserService + StoreImportacionRequest implementados (backend)
- Rutas de importación registradas (`/importaciones/*` con `permission:importaciones.gestionar`)
- TrabajoTest: 5/5 pasan

### Pendiente arrastrado

- UI de importación (no se creó frontend en Sprint 03)
- Sidebar: enlaces Pedidos y Facturas sin activar
- Seeders de pedidos y facturas vacíos
- Panel Admin: módulos Pedidos, Facturación, Legalizaciones, Clientes, Estaciones marcados "próximamente"

---

## Resumen de la semana

| Día        | Backend                                             | Frontend                                      | Docs/General                      |
| ---------- | --------------------------------------------------- | --------------------------------------------- | --------------------------------- |
| **Lun 20** | PedidoController CRUD + StorePedidoRequest          | Pedidos/Index.jsx (tabla + filtros)           | Plan sprint, bitácora             |
| **Mar 21** | PedidoResource + PedidoFactory + seeders            | Pedidos/Form.jsx (líneas dinámicas)           | Bitácora diaria                   |
| **Mié 22** | FacturaController CRUD + StoreFacturaRequest        | Facturas/Index.jsx + Facturas/Form.jsx        | API contract pedidos/facturas     |
| **Jue 23** | FacturaResource + doble factura REPSOL + seeders    | Integración pedidos/facturas en Trabajos/Form | Bitácora diaria                   |
| **Vie 24** | Tests (PedidoTest + FacturaTest) + Sidebar + polish | Hook usePedidos + useFacturas + i18n          | Actualizar manual + cerrar sprint |

---

## TRACK BACKEND

### B04-01 · PedidoController CRUD (Lunes–Martes)

**Archivos a crear:**

| Archivo                                         | Descripción                                                    |
| ----------------------------------------------- | -------------------------------------------------------------- |
| `app/Http/Controllers/PedidoController.php`     | index, create, store, edit, update. Relación trabajo→N pedidos |
| `app/Http/Requests/Api/StorePedidoRequest.php`  | Validación con campos condicionales por contexto               |
| `app/Http/Requests/Api/UpdatePedidoRequest.php` | Mismas reglas adaptadas para update                            |
| `app/Http/Resources/Api/PedidoResource.php`     | Items anidados + campos condicionales por contexto             |
| `database/factories/PedidoFactory.php`          | Factory con estados para tests                                 |

**Archivos a editar:**

| Archivo                                | Cambios                                                             |
| -------------------------------------- | ------------------------------------------------------------------- |
| `routes/web.php`                       | Rutas `/pedidos` con `permission:pedidos.ver` y `pedidos.gestionar` |
| `database/seeders/DatosBaseSeeder.php` | 4+ pedidos demo con items vinculados a trabajos existentes          |

**Modelo Pedido** (ya existe `app/Models/Pedido.php`):

- `HasContext` trait ✅ aplicado
- Relaciones: `trabajo()` ✅, `items()` ✅, `facturas()` ✅ (many-to-many via `factura_pedidos`)
- Casts: fechas, decimales, booleans ✅

**Nota BD:** El modelo usa tabla `pedido_items` pero el SQL real tiene `pedidos_lineas` con PK `id_linea_pedido`. Verificar y alinear modelo con esquema real antes de implementar.

**Detalles de implementación:**

Controller `index()`:

- Eager load: `trabajo:id_trabajo,numero_trabajo`, `items`
- Filtros: `search` (numero_pedido, descripcion), `estado`, `id_trabajo`
- Paginación: 20 por página
- `ContextScope` se aplica automáticamente via `HasContext`
- Pasar `contextoIds` al frontend

Controller `create()` / `edit()`:

- Pasar catálogos: trabajos del contexto, tarifarios activos
- En edit: cargar items con líneas de tarifario

StorePedidoRequest — reglas:

```
Comunes:     id_trabajo (required|exists), numero_pedido (required|string), fecha_solicitud (date), estado
             items (array), items.*.descripcion_servicio, items.*.cantidad (numeric|min:0), items.*.precio_unitario (numeric|min:0)
REPSOL (2):  importe_solicitado (required|numeric), unidades_solicitadas (required|numeric)
```

PedidoResource — campos:

```
Siempre:     id_pedido, numero_pedido, estado, fecha_solicitud, fecha_recepcion, importe_pedido, trabajo, items[]
REPSOL (2):  importe_solicitado, unidades_solicitadas, unidades_pedido
```

Seeders:

```
Pedido 1: MOEVE, trabajo nº 1001, pedido P-1001-A, estado recibido, 2 items (líneas tarifario)
Pedido 2: MOEVE, trabajo nº 1002, pedido P-1002-A, estado pendiente, 1 item
Pedido 3: REPSOL, trabajo nº 3001, pedido P-3001-A, estado en_ejecucion, 3 items
Pedido 4: REPSOL, trabajo nº 3002, pedido P-3002-A, estado cerrado, 2 items
```

---

### B04-02 · FacturaController CRUD (Miércoles–Jueves)

**Archivos a crear:**

| Archivo                                          | Descripción                                                             |
| ------------------------------------------------ | ----------------------------------------------------------------------- |
| `app/Http/Controllers/FacturaController.php`     | CRUD. REPSOL soporta doble factura (orden 1/2). MOEVE exige factura_ccp |
| `app/Http/Requests/Api/StoreFacturaRequest.php`  | Validación condicional por contexto                                     |
| `app/Http/Requests/Api/UpdateFacturaRequest.php` | Mismas reglas adaptadas                                                 |
| `app/Http/Resources/Api/FacturaResource.php`     | Campos condicionales + relación pedidos                                 |
| `database/factories/FacturaFactory.php`          | Factory con estados para tests                                          |

**Archivos a editar:**

| Archivo                                | Cambios                                                                |
| -------------------------------------- | ---------------------------------------------------------------------- |
| `routes/web.php`                       | Rutas `/facturas` con `permission:facturas.ver` y `facturas.gestionar` |
| `database/seeders/DatosBaseSeeder.php` | 4+ facturas demo vinculadas a pedidos existentes                       |

**Modelo Factura** (ya existe `app/Models/Factura.php`):

- `HasContext` trait ✅ aplicado
- Relaciones: `trabajo()` ✅, `empresa()` ✅, `pedidos()` ✅ (many-to-many via `factura_pedidos`), `cobros()` ✅
- Campos clave MOEVE: `numero_factura_ccp`, `sociedad`
- Campos clave REPSOL: `orden_factura` (1=primera, 2=segunda), `autofactura`

**Detalles de implementación:**

StoreFacturaRequest — reglas:

```
Comunes:     id_trabajo (required|exists), numero_factura (required|string), fecha_emision (required|date)
             base_imponible (numeric), iva (numeric), total (numeric), estado
MOEVE (1):   numero_factura_ccp (required|string), sociedad (required|string)
REPSOL (2):  orden_factura (required|in:1,2), autofactura (boolean)
```

FacturaResource — campos:

```
Siempre:     id_factura, numero_factura, serie, fecha_emision, fecha_vencimiento, importe, base_imponible, iva, retencion, total, estado, trabajo, empresa, pedidos[]
MOEVE (1):   numero_factura_ccp, sociedad
REPSOL (2):  orden_factura, autofactura
```

**Lógica doble factura REPSOL:**

```
- Un trabajo REPSOL puede tener hasta 2 facturas (orden_factura = 1 o 2)
- La primera factura se emite al inicio del trabajo
- La segunda factura se emite al cierre
- Validar: unique constraint (id_trabajo, orden_factura) para contexto REPSOL
```

Seeders:

```
Factura 1: MOEVE, trabajo nº 1001, factura F-1001, factura_ccp CCP-2026-001, sociedad S100, estado emitida
Factura 2: MOEVE, trabajo nº 1002, factura F-1002, factura_ccp CCP-2026-002, sociedad S100, estado emitida
Factura 3: REPSOL, trabajo nº 3001, factura F-3001-1, orden_factura 1, estado cobrada
Factura 4: REPSOL, trabajo nº 3001, factura F-3001-2, orden_factura 2, estado emitida (doble factura)
```

---

### B04-03 · Tests (Viernes)

**Patrón a seguir:** Mismo que `TrabajoTest.php` del Sprint 03 — factory + seeders + Vite mock + assertInertia.

**Archivo:** `tests/Feature/PedidoTest.php`

| Test                               | Qué verifica                               |
| ---------------------------------- | ------------------------------------------ |
| `test_guest_cannot_access_pedidos` | Redirect a login                           |
| `test_context_isolation_pedidos`   | Gestor MOEVE no ve pedidos REPSOL          |
| `test_create_pedido_with_items`    | Store correcto con items anidados          |
| `test_update_pedido`               | Update + items actualizados                |
| `test_pedido_belongs_to_trabajo`   | Relación FK válida                         |
| `test_repsol_requires_importes`    | importe_solicitado obligatorio para REPSOL |

**Archivo:** `tests/Feature/FacturaTest.php`

| Test                                | Qué verifica                               |
| ----------------------------------- | ------------------------------------------ |
| `test_guest_cannot_access_facturas` | Redirect a login                           |
| `test_context_isolation_facturas`   | Aislamiento por contexto                   |
| `test_moeve_requires_factura_ccp`   | Validación condicional MOEVE               |
| `test_repsol_doble_factura`         | Orden 1 y 2 permitidas, 3 rechazada        |
| `test_repsol_unique_orden_factura`  | No duplicar orden_factura en mismo trabajo |

---

## TRACK FRONTEND

### F04-01 · Pedidos UI (Lunes–Martes)

| Archivo                                | Descripción                                                                 |
| -------------------------------------- | --------------------------------------------------------------------------- |
| `resources/js/Pages/Pedidos/Index.jsx` | Tabla con filtros por trabajo, estado, fecha. Paginación. Badge estado.     |
| `resources/js/Pages/Pedidos/Form.jsx`  | Formulario con N líneas dinámicas (código, descripción, qty, precio, total) |
| `resources/js/Hooks/usePedidos.jsx`    | Hook CRUD + navegación (seguir patrón de `useTrabajos.jsx`)                 |

**Referencia visual:** Seguir patrón de `Trabajos/Index.jsx` y `Trabajos/Form.jsx`.

**Tabla Index — columnas:**

| Columna            | Siempre | MOEVE | REPSOL |
| ------------------ | ------- | ----- | ------ |
| Nº pedido          | ✅      |       |        |
| Trabajo (nº)       | ✅      |       |        |
| Estado (badge)     | ✅      |       |        |
| Fecha solicitud    | ✅      |       |        |
| Importe pedido     | ✅      |       |        |
| Importe solicitado |         |       | ✅     |
| Uds. solicitadas   |         |       | ✅     |
| Acciones           | ✅      |       |        |

**Form — líneas dinámicas:**

```
Cada línea: [ código_servicio | descripción | cantidad | precio_unitario | total_linea ]
Botón "+ Añadir línea" / "Eliminar" por fila
Totales calculados en tiempo real
```

### F04-02 · Facturas UI (Miércoles–Jueves)

| Archivo                                 | Descripción                                                    |
| --------------------------------------- | -------------------------------------------------------------- |
| `resources/js/Pages/Facturas/Index.jsx` | Tabla. REPSOL muestra orden_factura. MOEVE muestra factura_ccp |
| `resources/js/Pages/Facturas/Form.jsx`  | Campos condicionales por contexto + selección de pedidos       |
| `resources/js/Hooks/useFacturas.jsx`    | Hook CRUD (seguir patrón de `useTrabajos.jsx`)                 |

**Tabla Index — columnas:**

| Columna        | Siempre | MOEVE | REPSOL |
| -------------- | ------- | ----- | ------ |
| Nº factura     | ✅      |       |        |
| Trabajo        | ✅      |       |        |
| Empresa        | ✅      |       |        |
| Fecha emisión  | ✅      |       |        |
| Total          | ✅      |       |        |
| Estado (badge) | ✅      |       |        |
| Nº factura CCP |         | ✅    |        |
| Sociedad       |         | ✅    |        |
| Orden factura  |         |       | ✅     |
| Autofactura    |         |       | ✅     |
| Acciones       | ✅      |       |        |

### F04-03 · Integración + i18n (Jueves–Viernes)

| Archivo                                        | Cambios                                                   |
| ---------------------------------------------- | --------------------------------------------------------- |
| `resources/js/Layouts/AuthenticatedLayout.jsx` | Sidebar: activar enlaces Pedidos + Facturas               |
| `resources/js/i18n/locales/es.js`              | Secciones `pedidos: { ... }` y `facturas: { ... }`        |
| `resources/js/i18n/locales/en.js`              | Traducciones en inglés                                    |
| `resources/js/Pages/Trabajos/Form.jsx`         | Añadir pestaña/sección con pedidos y facturas del trabajo |

---

## TAREAS ARRASTRADAS (Sprint 03 → Sprint 04)

> Estas tareas tienen backend implementado en Sprint 03 pero carecen de UI o activación.

| Tarea                              | Prioridad | Estado actual                                                                                                                                                                         |
| ---------------------------------- | --------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| UI importación Excel/CSV           | Media     | Backend listo: `ImportacionController` (5 endpoints), `ExcelParserService`, `StoreImportacionRequest`. Rutas registradas. Falta: páginas React (Importaciones/Index, Upload, Preview) |
| Primer tipo: `estaciones_moeve`    | Baja      | Depende de UI de importación. Mapeo parcial en `docs/03_API_ERP/Mapeo_Importacion_Excel.md`                                                                                           |
| Admin: activar módulos placeholder | Baja      | Dashboard admin tiene 5 módulos marcados "próximamente". Activar cuando existan las vistas                                                                                            |
| Admin: CRUD roles/permisos         | Baja      | No empezado. Panel admin base funciona. Se puede extender UserController                                                                                                              |

---

## DEFINICIÓN DE "HECHO" (DoD)

- [ ] CRUD pedidos funcional (backend + frontend + tests)
- [ ] CRUD facturas funcional (backend + frontend + tests)
- [ ] Doble factura REPSOL implementada y validada
- [ ] Numeración CCP MOEVE implementada
- [ ] Líneas anidadas en pedidos (crear/editar/eliminar)
- [ ] Tests pasan: PedidoTest + FacturaTest (patrón TrabajoTest)
- [ ] i18n completo (es + en) para pedidos y facturas
- [ ] Sidebar activado para Pedidos + Facturas
- [ ] Seeders con datos demo coherentes con seeders de trabajos
- [ ] Documentación API pedidos/facturas

---

## RIESGOS Y MITIGACIONES

| Riesgo                                                 | Probabilidad | Mitigación                                                           |
| ------------------------------------------------------ | ------------ | -------------------------------------------------------------------- |
| Desalineación modelo/BD (PedidoItem vs pedidos_lineas) | Alta         | Verificar y corregir modelo ANTES de empezar controller              |
| Complejidad de doble factura REPSOL                    | Media        | Implementar orden_factura como campo simple (1 o 2), unique compound |
| Líneas dinámicas en formulario React                   | Media        | Componente genérico ItemsTable reutilizable                          |
| Importación arrastrada no cabe en la semana            | Alta         | Se mantiene como backlog; no bloquea Pedidos/Facturas                |
| Tests frágiles si no se sigue patrón TrabajoTest       | Media        | Copiar setUp de TrabajoTest como plantilla para nuevos tests         |
