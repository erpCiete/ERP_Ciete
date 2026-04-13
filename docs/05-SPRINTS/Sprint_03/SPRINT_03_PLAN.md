# Sprint 03 — Semana 13–17 abril 2026

> **Versión inicial:** v1.2.0 → **Versión objetivo:** v1.3.0  
> **Foco principal:** Módulo de Trabajos (Obras) — CRUD completo con parametrización MOEVE/REPSOL  
> **Foco secundario:** Preparación de importación Excel/CSV (infraestructura + primer flujo)

---

## Resumen de la semana

| Día        | Backend                                              | Frontend                                    | Docs/General                      |
| ---------- | ---------------------------------------------------- | ------------------------------------------- | --------------------------------- |
| **Lun 13** | ✅ Seguridad rate-limit, StatusController role-based | ✅ Status.jsx, Help.jsx, i18n fixes         | ✅ Biblia desarrollo, plan sprint |
| **Mar 14** | TrabajoController + StoreRequest + UpdateRequest     | Trabajos/Index.jsx (tabla + filtros)        | Bitácoras diarias                 |
| **Mié 15** | TrabajoResource + rutas + seeders trabajos demo      | Trabajos/Form.jsx (campos condicionales)    | API contract JSON trabajos        |
| **Jue 16** | ImportacionController base + servicio de parseo      | Importaciones/Upload.jsx (subida + preview) | Mapeo campos Excel → DB           |
| **Vie 17** | Tests completos + Dashboard props reales             | Sidebar activar Obras + polish              | Actualizar manual + versión       |

---

## TRACK BACKEND

### B-01 · TrabajoController CRUD (Martes 14)

**Archivos a crear:**

| Archivo                                      | Descripción                                                                            |
| -------------------------------------------- | -------------------------------------------------------------------------------------- |
| `app/Http/Controllers/TrabajoController.php` | index, create, store, edit, update, destroy                                            |
| `app/Http/Requests/StoreTrabajoRequest.php`  | Validación condicional: MOEVE exige contrato. REPSOL exige tipo_documento+tipo_trabajo |
| `app/Http/Requests/UpdateTrabajoRequest.php` | Mismas reglas que Store adaptadas para update                                          |
| `app/Http/Resources/TrabajoResource.php`     | Serialización con campos condicionales por contexto                                    |

**Archivos a editar:**

| Archivo                                | Cambios                                                                          |
| -------------------------------------- | -------------------------------------------------------------------------------- |
| `routes/web.php`                       | Rutas: `/trabajos`, `/trabajos/crear`, `/trabajos/{trabajo}/editar` con permisos |
| `database/seeders/DatosBaseSeeder.php` | Añadir 4+ trabajos demo (2 MOEVE, 2 REPSOL) con estados variados                 |

**Detalles de implementación:**

Controller `index()`:

- Eager load: `empresa`, `estacion`, `tipoDocumento`, `tipoTrabajo`
- Filtros: `search` (numero_trabajo, descripcion), `estado`, `fecha_desde`, `fecha_hasta`
- Paginación: 20 por página
- `ContextScope` se aplica automáticamente via `HasContext` trait
- Pasar `contextoIds` al frontend para renderizado condicional de columnas

Controller `create()` / `edit()`:

- Pasar catálogos: empresas, estaciones, contratos (MOEVE), tiposDocumento (REPSOL), tiposTrabajo (REPSOL)
- Filtrar catálogos por contexto del usuario

StoreTrabajoRequest — reglas:

```
Comunes:     numero_trabajo (required|integer), descripcion_trabajo, id_empresa_cliente, estado, fecha_encargo, fecha_terminacion
MOEVE (1):   id_contrato (required), categoria
REPSOL (2):  id_tipo_documento (required), id_tipo_trabajo (required), numero_aviso, orden_mantenimiento
```

TrabajoResource — campos condicionales:

```
Siempre:     id, numero_trabajo, descripcion, estado, fecha_encargo, fecha_terminacion, cerrado, empresa, estacion
MOEVE (1):   contrato, categoria
REPSOL (2):  tipo_documento, tipo_trabajo, numero_aviso, orden_mantenimiento, zona
```

Seeders:

```
Trabajo 1: MOEVE, nº 1001, "Remodelación EESS Alcobendas", en_curso, contrato A
Trabajo 2: MOEVE, nº 1002, "Ampliación marquesina Madrid Norte", borrador, contrato A
Trabajo 3: REPSOL, nº 3001, "Reforma interior EESS Valencia", en_curso, tipo_doc Z10
Trabajo 4: REPSOL, nº 3002, "Instalación punto de recarga Sevilla", terminado, tipo_doc Z50
```

---

### B-02 · Modelos de soporte faltantes (Martes 14)

**Archivos a crear (si no existen ya como modelos vacíos):**

| Modelo                           | Relación con Trabajo                   |
| -------------------------------- | -------------------------------------- |
| `app/Models/Contacto.php`        | Via contactos_empresas                 |
| `app/Models/ContactoEmpresa.php` | Pivot empresa-contacto                 |
| `app/Models/Direccion.php`       | Polimórfico (empresa/contacto/usuario) |
| `app/Models/Telefono.php`        | Polimórfico (empresa/contacto/usuario) |
| `app/Models/Email.php`           | Polimórfico (empresa/contacto/usuario) |
| `app/Models/Unidad.php`          | Catálogo de unidades de medida         |

> **Nota:** Estos modelos ya pueden existir. Verificar antes de crear. Si existen, solo confirmar que los fillable y relaciones son correctos.

---

### B-03 · ImportacionController base (Jueves 16)

**Archivos a crear:**

| Archivo                                          | Descripción                                                                                                                      |
| ------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------- |
| `app/Http/Controllers/ImportacionController.php` | index (historial), create (formulario subida), store (procesar archivo), preview (vista previa), confirm (confirmar importación) |
| `app/Http/Requests/StoreImportacionRequest.php`  | Validación: archivo (xlsx,csv,xls), tipo (estaciones_moeve, estaciones_repsol, trabajos, tarifario), max 10MB                    |
| `app/Http/Resources/ImportacionResource.php`     | Serialización del historial                                                                                                      |
| `app/Services/ExcelParserService.php`            | Servicio base: leer cabeceras, detectar tipo, parsear filas a staging                                                            |

**Archivos a editar:**

| Archivo          | Cambios                                                       |
| ---------------- | ------------------------------------------------------------- |
| `routes/web.php` | Rutas `/importaciones` con permission:importaciones.gestionar |
| `composer.json`  | Añadir `maatwebsite/excel` o `phpoffice/phpspreadsheet`       |

**Flujo de importación (3 fases):**

```
1. SUBIDA  → Usuario sube archivo → se guarda en storage/app/imports/
                                   → se crea registro en tabla `importaciones` (estado: 'procesando')
                                   → se parsean cabeceras → redirect a preview

2. PREVIEW → Se muestran las primeras 10 filas parseadas
           → Se muestra mapeo automático columna-Excel → campo-BD
           → Se muestran errores de validación por fila
           → Usuario confirma o cancela

3. CONFIRM → Se procesan todas las filas
           → Filas OK → insert/update en tabla destino
           → Filas con error → se marcan en importacion_filas con motivo
           → Se actualiza importación (filas_ok, filas_error, estado: 'completada' o 'con_errores')
```

**Tipos de importación previstos:**

| Tipo                | Fuente                                 | Tabla destino                               | Columnas clave                              |
| ------------------- | -------------------------------------- | ------------------------------------------- | ------------------------------------------- |
| `estaciones_moeve`  | "02 Listado EESS España Portugal"      | estaciones_servicio + estaciones_moeve_ext  | concesion→codigo_estacion, GPS, técnicos    |
| `estaciones_repsol` | Control Trabajos Repsol (pestaña EESS) | estaciones_servicio + estaciones_repsol_ext | C.EMP→codigo_estacion, SOLRED, combustibles |
| `trabajos_repsol`   | "03 Control Trabajos OBRAS Z10/Z50"    | trabajos + pedidos                          | nº trabajo, estación, estado, pedidos       |
| `tarifario_repsol`  | Tarifa 23-27 (370 líneas)              | tarifario_lineas                            | código, grupo, actuación, tarifa_base       |

> **Sprint 03 objetivo:** Implementar la infraestructura genérica + primer tipo (`estaciones_moeve`). Los demás tipos se desarrollan en sprints siguientes.

---

### B-04 · Tests (Viernes 17)

**Archivo:** `tests/Feature/TrabajoTest.php`

| Test                                           | Qué verifica                             |
| ---------------------------------------------- | ---------------------------------------- |
| `test_guest_cannot_access_trabajos`            | Redirect a login                         |
| `test_user_without_permission_denied`          | 403                                      |
| `test_user_with_permission_can_list_trabajos`  | 200 + componente Trabajos/Index          |
| `test_user_with_permission_can_create_trabajo` | Redirect + registro en BD                |
| `test_user_with_permission_can_update_trabajo` | Redirect + dato actualizado              |
| `test_moeve_requires_contrato`                 | Error 422 si falta contrato              |
| `test_repsol_requires_tipo_documento`          | Error 422 si falta tipo_documento        |
| `test_context_isolation_moeve_repsol`          | Gestor Moeve no ve trabajos Repsol       |
| `test_search_filter_works`                     | Búsqueda por numero/descripcion funciona |
| `test_estado_filter_works`                     | Filtro por estado funciona               |

**Archivo:** `tests/Feature/ImportacionTest.php`

| Test                                     | Qué verifica                        |
| ---------------------------------------- | ----------------------------------- |
| `test_guest_cannot_access_importaciones` | Redirect                            |
| `test_user_without_permission_denied`    | 403                                 |
| `test_user_can_upload_excel`             | Archivo se guarda, registro se crea |
| `test_invalid_file_type_rejected`        | Solo xlsx, csv, xls                 |
| `test_preview_shows_parsed_rows`         | Respuesta contiene filas parseadas  |

---

### B-05 · Dashboard con datos reales (Viernes 17)

**Archivo a editar:** `routes/web.php` (ruta dashboard)

```php
Route::get('/dashboard', function () {
    $user = auth()->user();
    $contextIds = $user->getAccessibleContextIds();

    return Inertia::render('Dashboard', [
        'obras_en_curso' => Trabajo::whereIn('id_contexto', $contextIds)
            ->where('estado', 'en_curso')->count(),
        'obras_recientes' => TrabajoResource::collection(
            Trabajo::whereIn('id_contexto', $contextIds)
                ->latest('updated_at')->take(5)->get()
        ),
    ]);
})->name('dashboard');
```

---

## TRACK FRONTEND

### F-01 · Trabajos/Index.jsx (Martes 14 – Miércoles 15)

**Archivo:** `resources/js/Pages/Trabajos/Index.jsx`

**Diseño:**

- Barra superior: título "Trabajos" + botón "+ Nuevo trabajo" (si tiene permiso crear)
- Barra de filtros: input búsqueda + select estado + rango fechas
- Tabla responsive con columnas dinámicas según contexto:

| Columna                    | Siempre | MOEVE | REPSOL |
| -------------------------- | ------- | ----- | ------ |
| Nº                         | ✅      |       |        |
| Descripción                | ✅      |       |        |
| Estado (badge color)       | ✅      |       |        |
| Empresa                    | ✅      |       |        |
| Estación                   | ✅      |       |        |
| Fecha encargo              | ✅      |       |        |
| Contrato                   |         | ✅    |        |
| Categoría                  |         | ✅    |        |
| Tipo documento             |         |       | ✅     |
| Tipo trabajo               |         |       | ✅     |
| Nº aviso                   |         |       | ✅     |
| Acciones (editar/eliminar) | ✅      |       |        |

- Paginación inferior
- Empty state si no hay trabajos

**Referencia visual:** Seguir el patrón de `Clientes/Index.jsx` y `Estaciones/Index.jsx`.

---

### F-02 · Trabajos/Form.jsx (Miércoles 15)

**Archivo:** `resources/js/Pages/Trabajos/Form.jsx`

**Diseño:**

- Formulario en tarjeta con secciones:
    1. **Datos principales:** nº trabajo, descripción, empresa (select), estación (select), estado (select), responsable
    2. **Fechas:** fecha encargo (datepicker), fecha terminación (datepicker)
    3. **Campos MOEVE** (solo si contexto incluye 1): contrato (select), categoría (input)
    4. **Campos REPSOL** (solo si contexto incluye 2): tipo documento (select), tipo trabajo (select), nº aviso, orden mantenimiento
    5. **Observaciones:** textarea

**Lógica:**

```jsx
const contexto = usePage().props.auth.user.contexto_ids;
const isMoeve = contexto.includes(1);
const isRepsol = contexto.includes(2);
```

Usar `useForm` de Inertia para submit. Reutilizar componentes de input de Clientes/Form.jsx.

---

### F-03 · Importaciones/Upload.jsx (Jueves 16)

**Archivo:** `resources/js/Pages/Importaciones/Upload.jsx`

**Diseño:**

1. **Selector de tipo:** radio buttons (Estaciones Moeve, Estaciones Repsol, Trabajos, Tarifario)
2. **Zona de drop:** drag & drop + botón "Seleccionar archivo". Acepta .xlsx, .csv, .xls (<10MB)
3. **Progress bar** durante subida
4. **Vista previa:** tabla con 10 filas parseadas + mapeo columnas (2 columnas: "Columna Excel" → "Campo BD")
5. **Indicadores:** ✅ filas válidas, ❌ filas con error (con motivo)
6. **Botones:** "Confirmar importación" (verde) + "Cancelar" (gris)

---

### F-04 · Sidebar + Dashboard (Viernes 17)

**Archivos a editar:**

| Archivo                   | Cambios                                                          |
| ------------------------- | ---------------------------------------------------------------- |
| `AuthenticatedLayout.jsx` | Cambiar `href="#"` → `route('trabajos.index')` en enlace "Obras" |
| `Dashboard.jsx`           | Mostrar tarjeta "Obras en curso: N" + lista de 5 obras recientes |

---

### F-05 · i18n (durante toda la semana)

**Archivos a editar:** `es.js` + `en.js`

Secciones nuevas a añadir:

```js
trabajos: {
    title: 'Trabajos',
    create: 'Nuevo trabajo',
    edit: 'Editar trabajo',
    list: 'Listado de trabajos',
    fields: {
        numero: 'Nº trabajo',
        descripcion: 'Descripción del trabajo',
        fechaEncargo: 'Fecha de encargo',
        fechaTerminacion: 'Fecha de terminación',
        estado: 'Estado',
        empresa: 'Empresa cliente',
        estacion: 'Estación de servicio',
        responsableCiete: 'Responsable CIETE',
        responsableCliente: 'Responsable del cliente',
        observaciones: 'Observaciones',
        // MOEVE
        contrato: 'Contrato',
        categoria: 'Categoría',
        // REPSOL
        tipoDocumento: 'Tipo de documento',
        tipoTrabajo: 'Tipo de trabajo',
        numeroAviso: 'Nº de aviso',
        ordenMantenimiento: 'Orden de mantenimiento',
        zona: 'Zona',
    },
    status: {
        borrador: 'Borrador',
        enCurso: 'En curso',
        terminado: 'Terminado',
        cerrado: 'Cerrado',
        cancelado: 'Cancelado',
    },
    filters: {
        searchPlaceholder: 'Buscar por número o descripción...',
        allStatuses: 'Todos los estados',
        dateFrom: 'Desde',
        dateTo: 'Hasta',
    },
    empty: 'No hay trabajos registrados.',
    confirmDelete: '¿Eliminar este trabajo? Esta acción no se puede deshacer.',
},

importaciones: {
    title: 'Importación de datos',
    upload: 'Subir archivo',
    selectType: 'Selecciona el tipo de importación',
    types: {
        estacionesMoeve: 'Estaciones MOEVE',
        estacionesRepsol: 'Estaciones REPSOL',
        trabajos: 'Trabajos',
        tarifario: 'Tarifario',
    },
    dropzone: 'Arrastra un archivo o haz clic para seleccionar',
    formats: 'Formatos: .xlsx, .csv, .xls (máx. 10 MB)',
    preview: 'Vista previa',
    columns: {
        excel: 'Columna Excel',
        db: 'Campo base de datos',
    },
    rows: {
        valid: 'Filas válidas',
        invalid: 'Filas con errores',
        total: 'Total de filas',
    },
    confirm: 'Confirmar importación',
    cancel: 'Cancelar',
    processing: 'Procesando...',
    completed: 'Importación completada',
    withErrors: 'Importación completada con errores',
    history: 'Historial de importaciones',
},
```

---

## TRACK DOCUMENTACIÓN

### D-01 · Bitácoras diarias (todos los días)

Todos rellenan su bitácora en `docs/05-SPRINTS/Sprint_03/Bitacora/`:

- **BACK/**: alex_puma.md, carlos_puchol.md, chad_arzaga.md, eduardo_jimenez.md, miguel_taborda.md
- **FRONT/**: carlos_daniel_garrido.md, daniel_bascope.md, daniel_lopez.md, jhon_sebastian_becerra.md, jimmy_tasmonte.md
- **General/**: pablo.md

### D-02 · Contrato API de Trabajos (Miércoles 15)

Crear `docs/03_API_ERP/Trabajos_API_Contract.md`:

```
GET /trabajos → lista paginada con filtros
POST /trabajos → crear (body JSON con validación condicional)
GET /trabajos/{id}/editar → form data + catálogos
PUT /trabajos/{id} → actualizar
DELETE /trabajos/{id} → eliminar (soft? no)

Ejemplo request MOEVE:
{ "numero_trabajo": 1003, "id_empresa_cliente": 2, "id_contrato": 1, "categoria": "B", ... }

Ejemplo request REPSOL:
{ "numero_trabajo": 3003, "id_empresa_cliente": 3, "id_tipo_documento": 3, "id_tipo_trabajo": 5, "numero_aviso": "AV-2026-001", ... }
```

### D-03 · Mapeo Excel → BD (Jueves 16)

Crear `docs/03_API_ERP/Mapeo_Importacion_Excel.md`:

Documentar el mapeo de columnas de cada Excel fuente hacia las tablas de la BD. Basarse en `docs/03_API_ERP/04_Analisis_Documentos_Excel.md`.

### D-04 · Actualizar manual de usuario (Viernes 17)

Editar `es.js` / `en.js` sección `help.sections`:

- Añadir sección `obras` con descripción del módulo, campos, estados, flujo.
- Añadir sección `importaciones` con descripción básica.

### D-05 · Actualizar versión y changelog (Viernes 17)

- `StatusController.php`: `v1.2.0` → `v1.3.0`
- `docs/BIBLIA_DESARROLLO.md`: añadir entrada en changelog (se mantiene en raíz de docs/)
- `docs/05-SPRINTS/Plan_Detallado_Sprints.md`: marcar Sprint 03 como completado

---

## Criterios de aceptación (Definition of Done)

### Obligatorio para cerrar el sprint:

- [ ] `TrabajoController` con CRUD completo funcionando
- [ ] Campos condicionales MOEVE/REPSOL en formulario y listado
- [ ] ContextScope aísla trabajos por contexto correctamente
- [ ] Seeders con 4+ trabajos demo (2 MOEVE, 2 REPSOL)
- [ ] Enlace "Obras" funcional en sidebar
- [ ] Dashboard con al menos "obras en curso" real
- [ ] ImportacionController base con subida de archivo
- [ ] Tests: ≥ 10 nuevos (TrabajoTest + ImportacionTest)
- [ ] `php artisan test` → 100% passing
- [ ] `npm run build` → sin errores
- [ ] i18n: sección `trabajos` + `importaciones` en ES y EN
- [ ] Bitácoras diarias rellenadas

### Nice to have (si da tiempo):

- [ ] Importación Estaciones Moeve funcional end-to-end
- [ ] Dashboard admin con métricas reales
- [ ] Export CSV desde listado de trabajos
- [ ] Filtro avanzado por fecha en Index.jsx

---

## Riesgos y mitigaciones

| Riesgo                                       | Impacto | Mitigación                                                                      |
| -------------------------------------------- | ------- | ------------------------------------------------------------------------------- |
| Conflictos en `web.php` (múltiple backend)   | Alto    | Cada persona añade sus rutas en bloque separado. Merge al final del día         |
| Conflictos en `es.js`/`en.js`                | Alto    | Una persona añade las secciones base al inicio; los demás solo editan contenido |
| Modelo Trabajo no tiene todos los campos     | Medio   | Ya verificado: migración 000050 tiene todos los campos. Solo falta controller   |
| `phpspreadsheet` no instalado                | Medio   | Instalar el jueves antes de crear ImportacionController                         |
| Campos condicionales complejos en formulario | Medio   | Seguir patrón de Estaciones/Form.jsx que ya maneja extensiones por contexto     |

---

## Dependencias del paquete de importación

```bash
# Instalar antes del jueves:
composer require phpoffice/phpspreadsheet
# O alternativa:
composer require maatwebsite/excel
```

Recomendación: usar `phpoffice/phpspreadsheet` directamente (más control, menos magia) sobre Laravel Excel.

---

_Sprint 03 diseñado el 13/04/2026 por Pablo Sevillano._
