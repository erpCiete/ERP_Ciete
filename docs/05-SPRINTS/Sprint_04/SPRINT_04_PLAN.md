# Sprint 04 — Semana 20–24 abril 2026

> **Versión inicial:** v1.3.0 → **Versión objetivo:** v1.4.0  
> **Foco principal:** Módulo de Pedidos — CRUD completo con líneas anidadas  
> **Foco secundario:** Módulo de Facturas v1 — CRUD con doble factura REPSOL y campos condicionales MOEVE  
> **Foco complementario:** Importación Excel/CSV v1 — flujo mínimo usable desde cero (subir, previsualizar, confirmar)

---

## Estado heredado de Sprint 03

### Completado en Sprint 03

- CRUD Trabajos completo (back + front + tests)
- Panel Admin: dashboard con stats, CRUD usuarios, visor auditoría
- Clientes y Estaciones: CRUD funcional (back + front + sidebar activo)
- Modelos Pedido, PedidoItem, Factura ya existen con relaciones, casts y trait `HasContext`
- Datos demo de pedidos (4), pedido_items (4), facturas (4) y factura_pedidos (4) existen en el dump SQL (`abaco_ciete.sql`), pero DatosBaseSeeder no los crea
- Sistema de avisos bilingüe funcional (editor admin + Welcome)
- Traducciones ES/EN para trabajos, admin y avisos
- Tests: 65 pass, 2 fail preexistentes en `TrabajoRequestTest` (no regresiones)

### Sobre la importación Excel — estado real

En Sprint 03 se escribió código backend para importación, pero **no es operativo**:

- `ImportacionController.php` existe (5 métodos: index, create, store, preview, confirm)
- `ExcelParserService.php` existe (parseo chunked con mapeo fijo a columnas A–E para trabajos)
- `StoreImportacionRequest.php` existe (valida archivo, mime, tamaño)
- Modelos `Importacion` e `ImportacionFila` existen
- 5 rutas registradas en `routes/web.php` bajo `permission:importaciones.gestionar`
- **PhpSpreadsheet NO está instalado** — no aparece en `composer.json` ni en `vendor/`. El servicio importa `PhpOffice\PhpSpreadsheet\IOFactory` pero la dependencia no existe. El código crashearía en runtime.
- **No hay frontend** — no existen `Importaciones/Index.jsx`, `Importaciones/Form.jsx` ni `Importaciones/Preview.jsx`
- **No hay enlace en el sidebar** — el módulo no es accesible desde la app
- El servicio solo contempla un tipo de importación (`trabajos`) con mapeo fijo a 5 columnas
- **Nunca se ha probado el flujo completo** — no hay tests ni evidencia de uso real

**Conclusión:** Hay código borrador, no hay funcionalidad operativa. Sprint 04 debe tratar la importación como un desarrollo desde base real, no como una continuación de algo ya hecho.

### Pendiente arrastrado

- Importación Excel: completar de verdad (ver estado real arriba)
- Sidebar: enlace Pedidos existe como placeholder (`href="#"`), Facturas NO está en el sidebar
- DatosBaseSeeder: no seedea pedidos ni facturas (los datos demo vienen solo del dump SQL)
- Panel Admin: módulos Pedidos, Facturación y Legalizaciones marcados "próximamente" (Clientes, Estaciones, Trabajos y Auditoría ya están activos)

---

## Resumen de la semana

| Día        | Backend                                            | Frontend                                       | Docs/General                      |
| ---------- | -------------------------------------------------- | ---------------------------------------------- | --------------------------------- |
| **Lun 20** | PedidoController CRUD + StorePedidoRequest         | Pedidos/Index.jsx (tabla + filtros)            | Plan sprint, bitácora             |
| **Mar 21** | PedidoResource + PedidoFactory + seeders           | Pedidos/Form.jsx (líneas dinámicas)            | Bitácora diaria                   |
| **Mié 22** | FacturaController CRUD + StoreFacturaRequest       | Facturas/Index.jsx + Facturas/Form.jsx         | API contract pedidos/facturas     |
| **Jue 23** | FacturaResource + doble factura REPSOL + seeders   | Sidebar + integración pedidos en Trabajos/Form | Bitácora diaria                   |
| **Vie 24** | Importación v1 (dependencia + revisión + frontend) | Importaciones/Upload.jsx + Preview.jsx + i18n  | Doc importación v1, cerrar sprint |

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
| `database/seeders/DatosBaseSeeder.php` | Añadir pedidos demo (actualmente solo existen en el dump SQL)       |

**Modelo Pedido** (ya existe `app/Models/Pedido.php`):

- `HasContext` trait ✅ aplicado
- Tabla: `pedidos`, PK: `id_pedido`
- Relaciones: `trabajo()` ✅, `items()` ✅, `facturas()` ✅ (many-to-many via `factura_pedidos`)
- Casts: fechas, decimales, booleans ✅

**Modelo PedidoItem** (ya existe `app/Models/PedidoItem.php`):

- Tabla: `pedido_items`, PK: `id_pedido_item` — coincide con el esquema real de BD (`abaco_ciete.sql`)
- Relaciones: `pedido()` ✅, `tarifarioLinea()` ✅
- Campos: `codigo_servicio`, `numero_tarifa`, `descripcion_servicio`, `precio_unitario`, `cantidad`, `total_linea`

**Nota BD:** El esquema base antiguo (`database/schema/erp_ciete_base.sql`) usa tabla `pedidos_lineas` con columnas diferentes. El esquema de producción (`abaco_ciete.sql`) usa `pedido_items` con PK `id_pedido_item`, que coincide exactamente con el modelo Eloquent. **No hay desalineación** — usar el esquema de producción como referencia.

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

**Datos demo existentes en BD** (referencia del dump SQL actual):

```
PED-M-001: MOEVE (ctx 1), trabajo 1, tarifario 1, estado facturado, importe 950.00, 1 item
PED-M-002: MOEVE (ctx 1), trabajo 2, tarifario 1, estado pendiente, importe 380.00, 1 item
PED-R-001: REPSOL (ctx 2), trabajo 3, tarifario 2, estado facturado, importe 980.00, 1 item
PED-R-002: REPSOL (ctx 2), trabajo 4, tarifario 2, estado pendiente, importe 400.00, 1 item
```

El seeder debe replicar estos datos o crear nuevos coherentes con los trabajos existentes.

---

### B04-02 · FacturaController CRUD (Miércoles–Jueves)

**Archivos a crear:**

| Archivo                                          | Descripción                                                           |
| ------------------------------------------------ | --------------------------------------------------------------------- |
| `app/Http/Controllers/FacturaController.php`     | CRUD. REPSOL soporta doble factura (orden 1/2). MOEVE usa factura_ccp |
| `app/Http/Requests/Api/StoreFacturaRequest.php`  | Validación condicional por contexto                                   |
| `app/Http/Requests/Api/UpdateFacturaRequest.php` | Mismas reglas adaptadas                                               |
| `app/Http/Resources/Api/FacturaResource.php`     | Campos condicionales + relación pedidos                               |
| `database/factories/FacturaFactory.php`          | Factory con estados para tests                                        |

**Archivos a editar:**

| Archivo                                | Cambios                                                                |
| -------------------------------------- | ---------------------------------------------------------------------- |
| `routes/web.php`                       | Rutas `/facturas` con `permission:facturas.ver` y `facturas.gestionar` |
| `database/seeders/DatosBaseSeeder.php` | Añadir facturas demo (actualmente solo existen en el dump SQL)         |

**Modelo Factura** (ya existe `app/Models/Factura.php`):

- `HasContext` trait ✅ aplicado
- Tabla: `facturas`, PK: `id_factura`
- Relaciones: `trabajo()` ✅, `empresa()` ✅, `pedidos()` ✅ (many-to-many via `factura_pedidos`), `cobros()` ✅
- Campos MOEVE: `numero_factura_ccp`, `sociedad`
- Campos REPSOL: `orden_factura` (1=primera, 2=segunda), `autofactura`
- Estados en BD: `pendiente`, `solicitada`, `emitida`, `enviada`, `cobrada_parcial`, `cobrada`, `vencida`, `anulada`

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
- Validar: unique compound (id_trabajo, orden_factura) para contexto REPSOL
```

**Datos demo existentes en BD** (referencia del dump SQL actual):

```
F-2026-001: MOEVE (ctx 1), trabajo 1, serie M, orden 1, estado emitida, total 1149.50, vinculada a PED-M-001
F-2026-002: MOEVE (ctx 1), trabajo 2, serie M, orden 1, estado pendiente, total 459.80, vinculada a PED-M-002
F-2026-003: REPSOL (ctx 2), trabajo 3, serie R, orden 1, estado cobrada, total 1185.80, vinculada a PED-R-001
F-2026-004: REPSOL (ctx 2), trabajo 4, serie R, orden 1, estado pendiente, total 484.00, vinculada a PED-R-002
```

La tabla pivot `factura_pedidos` ya tiene las 4 relaciones con campo `importe_aplicado`. El seeder debe replicar esta estructura.

---

### B04-03 · Importación Excel/CSV v1 (Viernes)

> **Contexto:** Existe código borrador en el backend pero no es operativo. Este bloque define el trabajo real necesario para tener un flujo mínimo usable.

**Paso 1 — Instalar dependencia:**

```bash
composer require phpoffice/phpspreadsheet
```

Sin esto, `ExcelParserService` falla al arrancar porque importa `PhpOffice\PhpSpreadsheet\IOFactory`.

**Paso 2 — Revisar y ajustar el backend existente:**

El código actual (`ImportacionController`, `ExcelParserService`, `StoreImportacionRequest`) fue escrito pero nunca ejecutado. Antes de construir frontend, validar:

- Que `ExcelParserService::parseFile()` funciona correctamente tras instalar PhpSpreadsheet
- Que los estados del modelo `Importacion` coinciden con lo que usa el controller (`pendiente` en código vs `subido` en enum de BD)
- Que el flujo store→preview→confirm funciona con un Excel real de prueba
- Que los modelos `Importacion` e `ImportacionFila` mapean correctamente a las tablas de BD

**Alcance v1 — mínimo usable:**

| Qué entra                                    | Qué NO entra                          |
| -------------------------------------------- | ------------------------------------- |
| Subir archivo Excel/CSV                      | Importación masiva de múltiples tipos |
| Previsualizar filas con validación visual    | Exportación a Excel                   |
| Confirmar importación de filas válidas       | Selección de tipo de importación      |
| Un solo tipo: importar trabajos              | Rollback parcial de importaciones     |
| Historial básico de importaciones realizadas | Mapeos configurables por usuario      |

**Archivos a crear (frontend):**

| Archivo                                        | Descripción                                           |
| ---------------------------------------------- | ----------------------------------------------------- |
| `resources/js/Pages/Importaciones/Index.jsx`   | Historial de importaciones con estado y fecha         |
| `resources/js/Pages/Importaciones/Upload.jsx`  | Formulario de subida (drag & drop o file input)       |
| `resources/js/Pages/Importaciones/Preview.jsx` | Tabla de previsualización con filas válidas/inválidas |

**Archivos a editar:**

| Archivo                                        | Cambios                                          |
| ---------------------------------------------- | ------------------------------------------------ |
| `composer.json`                                | Añadir `phpoffice/phpspreadsheet`                |
| `resources/js/Layouts/AuthenticatedLayout.jsx` | Enlace "Importaciones" en sidebar (si da tiempo) |
| `resources/js/i18n/locales/es.js`              | Sección `importaciones: { ... }`                 |
| `resources/js/i18n/locales/en.js`              | Traducciones en inglés                           |

**Rutas:** Ya registradas en `routes/web.php` bajo `permission:importaciones.gestionar`. No hace falta tocar rutas.

---

### B04-04 · Tests (Viernes)

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

**Nota:** Los tests de importación se dejan para Sprint 05 si el flujo v1 se estabiliza esta semana.

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

### F04-03 · Importación UI v1 (Viernes)

| Archivo                                        | Descripción                                                           |
| ---------------------------------------------- | --------------------------------------------------------------------- |
| `resources/js/Pages/Importaciones/Index.jsx`   | Historial de importaciones (estado, archivo, fecha, filas procesadas) |
| `resources/js/Pages/Importaciones/Upload.jsx`  | Formulario de subida con validación de tipo de archivo                |
| `resources/js/Pages/Importaciones/Preview.jsx` | Tabla de previsualización: filas válidas en verde, errores en rojo    |

**Nota:** El frontend debe ser funcional pero austero. No invertir más de 1 día. Si no da tiempo, se arrastra a Sprint 05 sin que bloquee pedidos ni facturas.

### F04-04 · Integración + i18n (Jueves–Viernes)

| Archivo                                        | Cambios                                                                                                                  |
| ---------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------ |
| `resources/js/Layouts/AuthenticatedLayout.jsx` | Sidebar: activar enlace Pedidos (ya existe como placeholder `href="#"`) + añadir enlace Facturas (no existe actualmente) |
| `resources/js/i18n/locales/es.js`              | Secciones `pedidos: { ... }`, `facturas: { ... }` e `importaciones: { ... }`                                             |
| `resources/js/i18n/locales/en.js`              | Traducciones en inglés                                                                                                   |
| `resources/js/Pages/Trabajos/Form.jsx`         | Añadir pestaña/sección con pedidos y facturas del trabajo                                                                |
| `resources/js/Pages/Admin/Dashboard.jsx`       | Activar enlaces de Pedidos y Facturación (actualmente `href="#"`)                                                        |

---

## DEFINICIÓN DE "HECHO" (DoD)

- [ ] CRUD pedidos funcional (backend + frontend + tests)
- [ ] CRUD facturas v1 funcional (backend + frontend + tests)
- [ ] Doble factura REPSOL implementada y validada
- [ ] Campos condicionales MOEVE (factura_ccp, sociedad) implementados
- [ ] Líneas anidadas en pedidos (crear/editar/eliminar dinámicamente)
- [ ] Tests pasan: PedidoTest + FacturaTest (patrón TrabajoTest)
- [ ] i18n completo (es + en) para pedidos y facturas
- [ ] Sidebar: enlace Pedidos activado + enlace Facturas añadido
- [ ] DatosBaseSeeder actualizado con datos demo de pedidos y facturas
- [ ] Documentación: contrato API pedidos/facturas
- [ ] Importación v1: PhpSpreadsheet instalado + frontend mínimo operativo (o documentado como arrastre a Sprint 05 si no da tiempo)

---

## RIESGOS Y MITIGACIONES

| Riesgo                                                   | Probabilidad | Mitigación                                                                           |
| -------------------------------------------------------- | ------------ | ------------------------------------------------------------------------------------ |
| Complejidad de doble factura REPSOL                      | Media        | Implementar `orden_factura` como campo simple (1 o 2), unique compound en validación |
| Líneas dinámicas en formulario React                     | Media        | Componente ItemsTable reutilizable; seguir patrón de `useTrabajos.jsx`               |
| PhpSpreadsheet causa conflictos de dependencia           | Baja         | Instalar temprano (lunes) y verificar que no rompe nada                              |
| Backend de importación falla al probarse por primera vez | Alta         | Reservar tiempo para debug antes de construir frontend. Probar con Excel real        |
| Importación v1 no cabe en el viernes                     | Alta         | Se acepta como arrastre a Sprint 05. No bloquea pedidos ni facturas                  |
| Tests frágiles si no se sigue patrón TrabajoTest         | Media        | Copiar setUp de TrabajoTest como plantilla para PedidoTest y FacturaTest             |
| Esquema base antiguo (`erp_ciete_base.sql`) genera dudas | Baja         | Usar siempre `abaco_ciete.sql` como referencia — es el esquema de producción real    |
