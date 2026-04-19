# Sprint 04 — Semana 20–24 abril 2026

> **Versión inicial:** v1.3.0 → **Versión objetivo:** v1.4.0  
> **Foco principal:** Módulo de Pedidos — CRUD completo con items anidados  
> **Foco secundario:** Módulo de Facturas — CRUD con doble factura REPSOL y doble numeración MOEVE  
> **Arrastre Sprint 03:** Importación/exportación Excel (infraestructura) — excluida de Sprint 03, se replanifica aquí como tarea de baja prioridad

---

## Resumen de la semana

| Día        | Backend                                                | Frontend                                     | Docs/General                          |
| ---------- | ------------------------------------------------------ | -------------------------------------------- | ------------------------------------- |
| **Lun 20** | PedidoController CRUD + StorePedidoRequest             | Pedidos/Index.jsx (tabla + filtros)          | Plan sprint, bitácora                 |
| **Mar 21** | PedidoResource + seeders pedidos + items               | Pedidos/Form.jsx (items dinámicos)           | Bitácora diaria                       |
| **Mié 22** | FacturaController CRUD + StoreFacturaRequest           | Facturas/Index.jsx + Facturas/Form.jsx       | API contract pedidos/facturas         |
| **Jue 23** | FacturaResource + doble factura REPSOL + seeders       | Integración pedidos/facturas en Trabajos/Form | Bitácora diaria                      |
| **Vie 24** | Tests (PedidoTest + FacturaTest) + Sidebar + polish    | Hook usePedidos + useFacturas                | Actualizar manual + cerrar sprint     |

---

## TRACK BACKEND

### B04-01 · PedidoController CRUD (Lunes–Martes)

**Archivos a crear:**

| Archivo                                      | Descripción                                                         |
| -------------------------------------------- | ------------------------------------------------------------------- |
| `app/Http/Controllers/PedidoController.php`  | index, create, store, edit, update. Relación trabajo→N pedidos      |
| `app/Http/Requests/StorePedidoRequest.php`   | numero_pedido, importes. REPSOL: importe_solicitado, uds_solicitadas |
| `app/Http/Requests/UpdatePedidoRequest.php`  | Mismas reglas adaptadas para update                                 |
| `app/Http/Resources/PedidoResource.php`      | Items anidados + campos condicionales por contexto                  |

**Archivos a editar:**

| Archivo                                | Cambios                                                            |
| -------------------------------------- | ------------------------------------------------------------------ |
| `routes/web.php`                       | Rutas `/pedidos` con `permission:pedidos.ver` y `pedidos.gestionar` |
| `database/seeders/DatosBaseSeeder.php` | 4+ pedidos demo con items vinculados a trabajos existentes          |

**Modelo Pedido** (ya existe `app/Models/Pedido.php`):
- Verificar relaciones: `trabajo()`, `items()`, `facturas()`
- Verificar `HasContext` trait aplicado

**Modelo PedidoItem** (ya existe `app/Models/PedidoItem.php`):
- Verificar relaciones: `pedido()`, `tarifarioLinea()`

---

### B04-02 · FacturaController CRUD (Miércoles–Jueves)

**Archivos a crear:**

| Archivo                                       | Descripción                                                                          |
| --------------------------------------------- | ------------------------------------------------------------------------------------ |
| `app/Http/Controllers/FacturaController.php`  | CRUD. REPSOL soporta doble factura. MOEVE exige factura_ccp                          |
| `app/Http/Requests/StoreFacturaRequest.php`   | MOEVE: exige factura_ccp. REPSOL: permite orden_factura 1 o 2                        |
| `app/Http/Requests/UpdateFacturaRequest.php`  | Mismas reglas adaptadas                                                              |
| `app/Http/Resources/FacturaResource.php`      | MOEVE: numero_factura_ccp + sociedad. REPSOL: orden_factura + autofactura            |

**Archivos a editar:**

| Archivo                                | Cambios                                                                |
| -------------------------------------- | ---------------------------------------------------------------------- |
| `routes/web.php`                       | Rutas `/facturas` con `permission:facturas.ver` y `facturas.gestionar` |
| `database/seeders/DatosBaseSeeder.php` | 4+ facturas demo vinculadas a pedidos existentes                       |

---

### B04-03 · Tests (Viernes)

**Archivo:** `tests/Feature/PedidoTest.php`

| Test                                     | Qué verifica                                         |
| ---------------------------------------- | ---------------------------------------------------- |
| `test_guest_cannot_access_pedidos`       | Redirect a login                                     |
| `test_context_isolation_pedidos`         | Gestor MOEVE no ve pedidos REPSOL                    |
| `test_create_pedido_with_items`          | Store correcto con items anidados                    |
| `test_update_pedido`                     | Update correcto                                      |
| `test_pedido_belongs_to_trabajo`         | Relación FK válida                                   |

**Archivo:** `tests/Feature/FacturaTest.php`

| Test                                     | Qué verifica                                         |
| ---------------------------------------- | ---------------------------------------------------- |
| `test_guest_cannot_access_facturas`      | Redirect a login                                     |
| `test_context_isolation_facturas`        | Aislamiento por contexto                             |
| `test_moeve_requires_factura_ccp`        | Validación condicional MOEVE                         |
| `test_repsol_doble_factura`              | Orden 1 y 2 permitidas                               |

---

## TRACK FRONTEND

### F04-01 · Pedidos UI (Lunes–Martes)

| Archivo                              | Descripción                                                           |
| ------------------------------------ | --------------------------------------------------------------------- |
| `resources/js/Pages/Pedidos/Index.jsx` | Tabla con filtros por trabajo, estado, fecha. Paginación              |
| `resources/js/Pages/Pedidos/Form.jsx`  | Formulario con N items dinámicos (código tarifa, desc, qty, precio)   |
| `resources/js/hooks/usePedidos.js`     | Hook CRUD + navegación                                                |

### F04-02 · Facturas UI (Miércoles–Jueves)

| Archivo                                | Descripción                                                                |
| -------------------------------------- | -------------------------------------------------------------------------- |
| `resources/js/Pages/Facturas/Index.jsx` | Tabla. REPSOL muestra orden_factura. MOEVE muestra factura_ccp            |
| `resources/js/Pages/Facturas/Form.jsx`  | Campos condicionales por contexto                                          |
| `resources/js/hooks/useFacturas.js`     | Hook CRUD                                                                  |

### F04-03 · Integración + i18n (Jueves–Viernes)

| Archivo                                          | Cambios                                                          |
| ------------------------------------------------ | ---------------------------------------------------------------- |
| `resources/js/Layouts/AuthenticatedLayout.jsx`   | Sidebar: activar enlaces Pedidos + Facturas                      |
| `resources/js/i18n/locales/es.js`                | Secciones `pedidos: { ... }` y `facturas: { ... }`              |
| `resources/js/i18n/locales/en.js`                | Traducciones en inglés                                           |
| `resources/js/Pages/Trabajos/Form.jsx`           | Añadir pestaña/sección con pedidos y facturas del trabajo        |

---

## TAREAS ARRASTRADAS (Sprint 03 → Sprint 04)

> Estas tareas se excluyen del Sprint 03 por priorización. Se incluyen aquí como backlog de baja prioridad.

| Tarea                                      | Prioridad | Notas                                                                   |
| ------------------------------------------ | --------- | ----------------------------------------------------------------------- |
| Infraestructura importación Excel/CSV      | Baja      | ImportacionController, ExcelParserService, flujo subida→preview→confirm |
| Primer tipo: `estaciones_moeve`            | Baja      | Depende de infraestructura de importación                               |
| Mapeo campos Excel → BD (documento)        | Baja      | Doc existe: `docs/03_API_ERP/Mapeo_Importacion_Excel.md`               |
| Mejorar Admin panel: roles/permisos CRUD   | Baja      | Panel admin base ya implementado (Sprint 03)                            |

---

## DEFINICIÓN DE "HECHO" (DoD)

- [ ] CRUD pedidos funcional (backend + frontend + tests)
- [ ] CRUD facturas funcional (backend + frontend + tests)
- [ ] Doble factura REPSOL implementada
- [ ] Doble numeración MOEVE (factura_ccp) implementada
- [ ] Items anidados en pedidos funcionan
- [ ] Tests pasan: PedidoTest + FacturaTest
- [ ] i18n completo (es + en) para pedidos y facturas
- [ ] Sidebar activado para Pedidos + Facturas
- [ ] Seeders con datos demo
- [ ] Documentación API pedidos/facturas

---

## RIESGOS Y MITIGACIONES

| Riesgo                                              | Probabilidad | Mitigación                                                    |
| --------------------------------------------------- | ------------ | ------------------------------------------------------------- |
| Complejidad de doble factura REPSOL                 | Media        | Implementar orden_factura como campo simple (1 o 2)           |
| Items anidados en formulario React difíciles        | Media        | Componente genérico ItemsTable con useFieldArray              |
| Importación arrastrada no cabe en la semana         | Alta         | Se mantiene como backlog, no bloquea Sprint 04                |
