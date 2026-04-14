# Biblia de Desarrollo — ERP Ciete

> **Documento vivo. Actualizar con cada sprint.**  
> **Versión actual:** v1.2.0 · **Última actualización:** 13/04/2026  
> **Stack:** Laravel 12 + Inertia.js + React 19 + MariaDB 10.4 + Tailwind CSS 4.2

---

## Índice

1. [Arquitectura del proyecto](#1-arquitectura-del-proyecto)
2. [Estructura de carpetas](#2-estructura-de-carpetas)
3. [Convenciones de código](#3-convenciones-de-código)
4. [Base de datos](#4-base-de-datos)
5. [Cómo crear un módulo nuevo (paso a paso)](#5-cómo-crear-un-módulo-nuevo)
6. [Sistema de contextos (multi-tenant)](#6-sistema-de-contextos)
7. [RBAC — Roles y permisos](#7-rbac--roles-y-permisos)
8. [Internacionalización (i18n)](#8-internacionalización-i18n)
9. [Tema y estilos](#9-tema-y-estilos)
10. [Testing](#10-testing)
11. [Comandos útiles](#11-comandos-útiles)
12. [Versionado](#12-versionado)
13. [Changelog](#13-changelog)
14. [Checklist de entrega por sprint](#14-checklist-de-entrega-por-sprint)

---

## 1. Arquitectura del proyecto

```
┌─────────────────────────────────────────────────────────┐
│                    NAVEGADOR                            │
│  React 19 + Inertia.js (SPA sin API explícita)          │
│  Pages/ · Layouts/ · Components/ · hooks/ · i18n/       │
├─────────────────────────────────────────────────────────┤
│                   INERTIA BRIDGE                        │
│  Pasa props PHP → React. No hay JSON API para web.      │
├─────────────────────────────────────────────────────────┤
│                LARAVEL 12 (Backend)                     │
│  Controllers → Requests → Resources → Models            │
│  Middleware: Auth, Role, Permission, Maintenance, Locale │
├─────────────────────────────────────────────────────────┤
│                 MariaDB 10.4                            │
│  33 tablas · Multi-contexto (id_contexto en cada tabla)  │
│  Relaciones FK compuestas (id_entidad + id_contexto)     │
└─────────────────────────────────────────────────────────┘
```

### Flujo de una petición web

1. Usuario navega a `/trabajos`
2. `routes/web.php` → middleware `auth`, `permission:trabajos.ver`
3. `TrabajoController@index` → consulta con `ContextScope` (filtra por contextos del usuario)
4. Retorna `Inertia::render('Trabajos/Index', ['trabajos' => TrabajoResource::collection(...)])`
5. React renderiza `resources/js/Pages/Trabajos/Index.jsx` con los datos como props

### Flujo de una petición API

1. Cliente externo → `POST /api/v1/auth/login` → obtiene token Sanctum
2. `GET /api/v1/clientes` con `Authorization: Bearer {token}`
3. `ClienteController@index` → misma lógica pero retorna JSON

---

## 2. Estructura de carpetas

```
app/
  Http/
    Controllers/            ← Un controller por módulo (web) + Api/ para REST
    Middleware/              ← Auth, Role, Permission, Maintenance, Locale
    Requests/               ← Form Requests con validación (Store + Update por módulo)
    Resources/              ← API Resources para serializar modelos
  Models/                   ← Eloquent models (1 por tabla principal)
    Scopes/ContextScope.php ← Scope global multi-contexto
  Notifications/            ← Email notifications (reset password)
  Providers/                ← AppServiceProvider
  Rules/                    ← Custom validation rules (CIF, CP, código estación)
  Traits/                   ← ApiResponse, HasContext, (futuro: Auditable)

resources/js/
  Pages/                    ← 1 carpeta por módulo: Index.jsx + Form.jsx (+ Show.jsx optional)
  Layouts/                  ← AuthenticatedLayout.jsx (sidebar + topbar)
  Components/               ← Componentes reutilizables (CieteMark, SupportDock, etc.)
  hooks/                    ← 1 hook por módulo: useModulo.js (CRUD + filtros)
  i18n/locales/             ← es.js + en.js (toda la traducción del ERP)
  validation/               ← formRules.js (validación frontend)

database/
  migrations/               ← Migraciones con fecha + número de orden
  seeders/                  ← DatabaseSeeder → llama a los demás por orden
  factories/                ← Factories para testing
  schema/                   ← Dumps SQL de referencia

tests/
  Feature/                  ← Tests de integración (HTTP requests)
  Unit/                     ← Tests unitarios (modelos, traits)

docs/
  05-SPRINTS/               ← Plan de sprints + bitácoras diarias por persona
  BIBLIA_DESARROLLO.md              ← ESTE ARCHIVO (referencia principal del proyecto)
  01_ORGANIZACION/
    01_Equipo_y_Roles.md
    02_Primeros_Pasos.md
  02_CLIENTE/
    01_Alcance_y_No_Alcance.md
    02_Requisitos_y_Acuerdos.md
  03_API_ERP/
    01_Lo_Que_Hace_la_API.md
    02_Lo_Que_No_Hace_la_API.md
    03_Brechas_y_Prioridades.md
    04_Analisis_Documentos_Excel.md   ← Análisis 13 excels fuente
    05_Plan_Reestructuracion_BBDD.md  ← Diseño de las 33 tablas
  04_DISENO_UI/
    01_Guia_Estilos_y_Capturas.md
    02_CAPTURAS_DISENO/
    03_Manual_Usuario.md              ← Manual de usuario integrado
  06_GIT/
    01_Instrucciones_y_Forma_de_Trabajo_Git.md
```

---

## 3. Convenciones de código

### Nomenclatura

| Elemento         | Convención                          | Ejemplo                                                                  |
| ---------------- | ----------------------------------- | ------------------------------------------------------------------------ |
| **Tablas BD**    | snake_case plural español           | `trabajos`, `estaciones_servicio`, `pedido_items`                        |
| **Primary keys** | `id_{tabla_singular}`               | `id_trabajo`, `id_pedido`, `id_factura`                                  |
| **Foreign keys** | `id_{tabla_referida_singular}`      | `id_empresa_cliente`, `id_estacion_servicio`                             |
| **Modelos**      | PascalCase singular español         | `Trabajo`, `EstacionServicio`, `PedidoItem`                              |
| **Controllers**  | PascalCase + Controller             | `TrabajoController`, `PedidoController`                                  |
| **Requests**     | Store/Update + Model + Request      | `StoreTrabajoRequest`, `UpdateTrabajoRequest`                            |
| **Resources**    | Model + Resource                    | `TrabajoResource`, `PedidoResource`                                      |
| **Rutas web**    | kebab-case español                  | `/trabajos`, `/trabajos/crear`, `/trabajos/{id}/editar`                  |
| **Rutas API**    | kebab-case inglés                   | `/api/v1/trabajos`                                                       |
| **Pages JSX**    | PascalCase en carpeta module        | `Pages/Trabajos/Index.jsx`, `Pages/Trabajos/Form.jsx`                    |
| **Hooks**        | camelCase con prefijo `use`         | `useTrabajos.js`                                                         |
| **i18n keys**    | camelCase anidado                   | `trabajos.fields.descripcion`, `trabajos.status.enCurso`                 |
| **Permisos**     | `modulo.accion`                     | `trabajos.ver`, `trabajos.crear`, `trabajos.editar`, `trabajos.eliminar` |
| **Migraciones**  | `YYYY_MM_DD_NNNNNN_descripcion.php` | `2026_03_24_000050_create_trabajos_operativa_tables.php`                 |

### Reglas generales

1. **Todo modelo con `id_contexto`** DEBE usar el trait `HasContext` (aplica `ContextScope` automáticamente).
2. **Todo controller web** retorna `Inertia::render()`. Nunca JSON directo en rutas web.
3. **Todo controller API** retorna `JsonResource` o `JsonResponse`. Nunca Inertia.
4. **Toda validación** va en Form Request, nunca en el controller.
5. **Toda serialización** va en Resource, nunca formato directo en el controller.
6. **Frontend:** Campos condicionales por contexto con `{contexto === 1 && <CamposMoeve />}`.
7. **Frontend:** Todos los textos visibles van por i18n. Nunca strings hardcoded.

---

## 4. Base de datos

### Diagrama de relaciones principal

```
contextos_cliente (CIETE=0, MOEVE=1, REPSOL=2)
  │
  ├── empresas ──────────── contactos ── contactos_empresas
  │     │                                       │
  │     ├── estaciones_servicio                  │
  │     │     ├── estaciones_moeve_ext           │
  │     │     └── estaciones_repsol_ext          │
  │     │                                        │
  │     └── contratos ── tarifarios ── tarifario_lineas
  │                                              │
  ├── trabajos ◄═══════════════════════════════════╝
  │     │
  │     ├── pedidos ── pedido_items
  │     │     │
  │     │     └── factura_pedidos (N:M)
  │     │            │
  │     ├── facturas ─┘
  │     │     └── cobros
  │     │
  │     ├── presupuestos ── presupuesto_lineas
  │     │
  │     └── legalizaciones ── legalizacion_contactos
  │           └── comentarios_legalizaciones
  │
  ├── usuarios ── usuario_contextos ── roles ── permisos
  │     └── mensajes_internos
  │
  ├── importaciones ── importacion_filas
  │
  └── audit_log
```

### 33 tablas organizadas

| Migración               | Tablas                                                                                                                |
| ----------------------- | --------------------------------------------------------------------------------------------------------------------- |
| `000001_framework`      | `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`                                              |
| `000002_tokens`         | `personal_access_tokens`                                                                                              |
| `000010_security`       | `roles`, `permisos`, `rol_permisos`, `contextos_cliente`                                                              |
| `000015_maestros`       | `tipos_documento`, `tipos_trabajo`, `unidades`                                                                        |
| `000020_empresas`       | `empresas`, `contactos`, `contactos_empresas`                                                                         |
| `000025_estaciones`     | `estaciones_servicio`, `estaciones_moeve_ext`, `estaciones_repsol_ext`, `contratos`, `tarifarios`, `tarifario_lineas` |
| `000030_users`          | `usuarios`, `usuario_contextos`, `sesiones_login`, `password_reset_tokens`                                            |
| `000035_tarifarios`     | (ya incluidos en 000025)                                                                                              |
| `000040_comunicacion`   | `direcciones`, `telefonos`, `emails`                                                                                  |
| `000050_trabajos`       | `trabajos`, `pedidos`, `pedido_items`, `facturas`, `factura_pedidos`, `cobros`                                        |
| `000055_presupuestos`   | `presupuestos`, `presupuesto_lineas`                                                                                  |
| `000060_legalizaciones` | `legalizaciones`, `legalizacion_contactos`, `comentarios_legalizaciones`                                              |
| `000070_importacion`    | `importaciones`, `importacion_filas`                                                                                  |
| `000080_audit`          | `audit_log`                                                                                                           |
| `04_13_mensajes`        | Columna `email_recuperacion` en usuarios + tabla `mensajes_internos`                                                  |

### Foreign keys compuestas

**Patrón clave:** Las FK que cruzan tablas con contexto usan doble columna `(id_entidad, id_contexto)` para garantizar aislamiento.

```php
$table->foreign(['id_empresa_cliente', 'id_contexto'], 'fk_trabajos_empresa_contexto')
    ->references(['id_empresa', 'id_contexto'])
    ->on('empresas');
```

### Tabla `trabajos` — campos por contexto

| Campo                | Común | MOEVE | REPSOL |
| -------------------- | ----- | ----- | ------ |
| numero_trabajo       | ✅    |       |        |
| descripcion_trabajo  | ✅    |       |        |
| fecha_encargo        | ✅    |       |        |
| fecha_terminacion    | ✅    |       |        |
| estado               | ✅    |       |        |
| id_empresa_cliente   | ✅    |       |        |
| id_estacion_servicio | ✅    |       |        |
| id_responsable_ciete | ✅    |       |        |
| responsable_cliente  | ✅    |       |        |
| observaciones        | ✅    |       |        |
| id_contrato          |       | ✅    |        |
| categoria            |       | ✅    |        |
| id_tipo_documento    |       |       | ✅     |
| id_tipo_trabajo      |       |       | ✅     |
| numero_aviso         |       |       | ✅     |
| orden_mantenimiento  |       |       | ✅     |
| zona                 |       |       | ✅     |

---

## 5. Cómo crear un módulo nuevo

**Ejemplo completo: módulo Trabajos (Sprint 03)**

### Paso 1: Backend — Controller

Crear `app/Http/Controllers/TrabajoController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrabajoRequest;
use App\Http\Requests\UpdateTrabajoRequest;
use App\Http\Resources\TrabajoResource;
use App\Models\Trabajo;
use Inertia\Inertia;

class TrabajoController extends Controller
{
    public function index()
    {
        $query = Trabajo::with(['empresa', 'estacion', 'tipoDocumento', 'tipoTrabajo'])
            ->orderByDesc('fecha_encargo');

        // Filtros de búsqueda
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('numero_trabajo', 'like', "%{$search}%")
                  ->orWhere('descripcion_trabajo', 'like', "%{$search}%");
            });
        }

        if ($estado = request('estado')) {
            $query->where('estado', $estado);
        }

        $trabajos = $query->paginate(20)->withQueryString();

        // Determinar columnas visibles según contexto
        $user = auth()->user();
        $contextoIds = $user->getAccessibleContextIds();

        return Inertia::render('Trabajos/Index', [
            'trabajos'    => TrabajoResource::collection($trabajos),
            'filters'     => request()->only(['search', 'estado']),
            'contextoIds' => $contextoIds,
        ]);
    }

    public function create()
    {
        return Inertia::render('Trabajos/Form', [
            // Catálogos para selects
            'empresas'        => /* query empresas del contexto */,
            'estaciones'      => /* query estaciones del contexto */,
            'tiposDocumento'  => /* solo para REPSOL */,
            'tiposTrabajo'    => /* solo para REPSOL */,
            'contratos'       => /* solo para MOEVE */,
        ]);
    }

    public function store(StoreTrabajoRequest $request)
    {
        $trabajo = Trabajo::create($request->validated());
        return redirect()->route('trabajos.index')
            ->with('success', __('messages.created'));
    }

    public function edit(Trabajo $trabajo)
    {
        // Verificar que el trabajo pertenece al contexto del usuario
        return Inertia::render('Trabajos/Form', [
            'trabajo'         => new TrabajoResource($trabajo),
            'empresas'        => /* ... */,
            'estaciones'      => /* ... */,
            // etc.
        ]);
    }

    public function update(UpdateTrabajoRequest $request, Trabajo $trabajo)
    {
        $trabajo->update($request->validated());
        return redirect()->route('trabajos.index')
            ->with('success', __('messages.updated'));
    }

    public function destroy(Trabajo $trabajo)
    {
        $trabajo->delete();
        return redirect()->route('trabajos.index')
            ->with('success', __('messages.deleted'));
    }
}
```

### Paso 2: Backend — Form Requests

Crear `app/Http/Requests/StoreTrabajoRequest.php`:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrabajoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('trabajos.crear');
    }

    public function rules(): array
    {
        $rules = [
            'numero_trabajo'       => ['required', 'integer'],
            'descripcion_trabajo'  => ['nullable', 'string', 'max:2000'],
            'id_empresa_cliente'   => ['required', 'exists:empresas,id_empresa'],
            'id_estacion_servicio' => ['nullable', 'exists:estaciones_servicio,id_estacion_servicio'],
            'fecha_encargo'        => ['nullable', 'date'],
            'fecha_terminacion'    => ['nullable', 'date', 'after_or_equal:fecha_encargo'],
            'estado'               => ['required', 'in:borrador,en_curso,terminado,cerrado,cancelado'],
            'observaciones'        => ['nullable', 'string', 'max:5000'],
        ];

        // Validación condicional por contexto
        $contextoId = $this->user()->id_contexto ?? null;

        if ($contextoId === 1) { // MOEVE
            $rules['id_contrato'] = ['required', 'exists:contratos,id_contrato'];
            $rules['categoria']   = ['nullable', 'string', 'max:100'];
        }

        if ($contextoId === 2) { // REPSOL
            $rules['id_tipo_documento'] = ['required', 'exists:tipos_documento,id_tipo_documento'];
            $rules['id_tipo_trabajo']   = ['required', 'exists:tipos_trabajo,id_tipo_trabajo'];
            $rules['numero_aviso']      = ['nullable', 'string', 'max:100'];
        }

        return $rules;
    }
}
```

### Paso 3: Backend — Resource

Crear `app/Http/Resources/TrabajoResource.php`:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrabajoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id_trabajo,
            'contexto'              => $this->id_contexto,
            'numero_trabajo'        => $this->numero_trabajo,
            'descripcion'           => $this->descripcion_trabajo,
            'estado'                => $this->estado,
            'fecha_encargo'         => $this->fecha_encargo?->toDateString(),
            'fecha_terminacion'     => $this->fecha_terminacion?->toDateString(),
            'cerrado'               => $this->cerrado,
            'bloqueado_cierre'      => $this->bloqueado_cierre,

            // Relaciones
            'empresa'               => $this->whenLoaded('empresa', fn() => [
                'id'     => $this->empresa->id_empresa,
                'nombre' => $this->empresa->nombre_comercial,
            ]),
            'estacion'              => $this->whenLoaded('estacion', fn() => [
                'id'     => $this->estacion->id_estacion_servicio,
                'codigo' => $this->estacion->codigo_estacion,
                'nombre' => $this->estacion->nombre,
            ]),

            // Campos MOEVE (contexto 1)
            'contrato'              => $this->when($this->id_contexto === 1, $this->id_contrato),
            'categoria'             => $this->when($this->id_contexto === 1, $this->categoria),

            // Campos REPSOL (contexto 2)
            'tipo_documento'        => $this->whenLoaded('tipoDocumento', fn() => [
                'id'     => $this->tipoDocumento->id_tipo_documento,
                'nombre' => $this->tipoDocumento->nombre,
            ]),
            'tipo_trabajo'          => $this->whenLoaded('tipoTrabajo', fn() => [
                'id'     => $this->tipoTrabajo->id_tipo_trabajo,
                'nombre' => $this->tipoTrabajo->nombre,
            ]),
            'numero_aviso'          => $this->when($this->id_contexto === 2, $this->numero_aviso),
            'orden_mantenimiento'   => $this->when($this->id_contexto === 2, $this->orden_mantenimiento),
        ];
    }
}
```

### Paso 4: Backend — Rutas

Editar `routes/web.php`:

```php
// Dentro del grupo auth + maintenance
Route::middleware('permission:trabajos.ver')->group(function () {
    Route::get('/trabajos', [TrabajoController::class, 'index'])->name('trabajos.index');
    Route::get('/trabajos/crear', [TrabajoController::class, 'create'])->name('trabajos.create');
    Route::post('/trabajos', [TrabajoController::class, 'store'])->name('trabajos.store')
        ->middleware('permission:trabajos.crear');
    Route::get('/trabajos/{trabajo}/editar', [TrabajoController::class, 'edit'])->name('trabajos.edit');
    Route::put('/trabajos/{trabajo}', [TrabajoController::class, 'update'])->name('trabajos.update')
        ->middleware('permission:trabajos.editar');
    Route::delete('/trabajos/{trabajo}', [TrabajoController::class, 'destroy'])->name('trabajos.destroy')
        ->middleware('permission:trabajos.eliminar');
});
```

### Paso 5: Frontend — Página Index

Crear `resources/js/Pages/Trabajos/Index.jsx`:

```jsx
import { Head, Link, router } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useI18n } from "@/i18n/useI18n";
import { Plus, Search, Edit2, Trash2 } from "lucide-react";

export default function Index({ trabajos, filters, contextoIds }) {
    const { t } = useI18n();

    // Columnas dinámicas según contexto
    const isMoeve = contextoIds.includes(1);
    const isRepsol = contextoIds.includes(2);

    const handleSearch = (value) => {
        router.get(
            route("trabajos.index"),
            { search: value },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    return (
        <AuthenticatedLayout header={t("trabajos.title")}>
            <Head title={t("trabajos.title")} />
            {/* Barra de búsqueda + botón crear */}
            {/* Tabla con columnas condicionales */}
            {/* Paginación */}
        </AuthenticatedLayout>
    );
}
```

### Paso 6: Frontend — Formulario

Crear `resources/js/Pages/Trabajos/Form.jsx`:

```jsx
import { useForm, Head, router } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useI18n } from "@/i18n/useI18n";

export default function Form({
    trabajo,
    empresas,
    estaciones,
    tiposDocumento,
    tiposTrabajo,
    contratos,
}) {
    const { t } = useI18n();
    const isEdit = !!trabajo;
    const contexto =
        trabajo?.contexto ?? usePage().props.auth.user.contexto_ids?.[0];

    const { data, setData, post, put, processing, errors } = useForm({
        numero_trabajo: trabajo?.numero_trabajo ?? "",
        descripcion_trabajo: trabajo?.descripcion ?? "",
        id_empresa_cliente: trabajo?.empresa?.id ?? "",
        id_estacion_servicio: trabajo?.estacion?.id ?? "",
        estado: trabajo?.estado ?? "borrador",
        fecha_encargo: trabajo?.fecha_encargo ?? "",
        fecha_terminacion: trabajo?.fecha_terminacion ?? "",
        // MOEVE
        ...(contexto === 1 && {
            id_contrato: trabajo?.contrato ?? "",
            categoria: trabajo?.categoria ?? "",
        }),
        // REPSOL
        ...(contexto === 2 && {
            id_tipo_documento: "",
            id_tipo_trabajo: "",
            numero_aviso: "",
        }),
    });

    const submit = (e) => {
        e.preventDefault();
        isEdit
            ? put(route("trabajos.update", trabajo.id))
            : post(route("trabajos.store"));
    };

    return (
        <AuthenticatedLayout
            header={isEdit ? t("trabajos.edit") : t("trabajos.create")}
        >
            <Head title={isEdit ? t("trabajos.edit") : t("trabajos.create")} />
            <form onSubmit={submit}>
                {/* Campos comunes */}
                {/* Campos MOEVE (contexto === 1) */}
                {contexto === 1 && (
                    <>{/* select contrato, input categoría */}</>
                )}
                {/* Campos REPSOL (contexto === 2) */}
                {contexto === 2 && (
                    <>
                        {/* select tipo_documento, tipo_trabajo, input numero_aviso */}
                    </>
                )}
            </form>
        </AuthenticatedLayout>
    );
}
```

### Paso 7: Frontend — Hook (opcional para API)

Crear `resources/js/hooks/useTrabajos.js`:

```js
import { router } from "@inertiajs/react";

export function useTrabajos() {
    const getTrabajos = (filters = {}) =>
        router.get(route("trabajos.index"), filters, { preserveState: true });

    const deleteTrabajo = (id) => router.delete(route("trabajos.destroy", id));

    return { getTrabajos, deleteTrabajo };
}
```

### Paso 8: Backend — Seeders (datos demo)

Editar `database/seeders/DatosBaseSeeder.php`:

```php
// Añadir al método run():
Trabajo::create([
    'id_contexto' => 1, // MOEVE
    'numero_trabajo' => 1001,
    'descripcion_trabajo' => 'Remodelación EESS Alcobendas',
    'id_empresa_cliente' => 2, // MOEVE SA
    'estado' => 'en_curso',
    'fecha_encargo' => '2026-01-15',
    'categoria' => 'A',
    'id_contrato' => 1,
]);
// ... más trabajos demo
```

### Paso 9: Backend — Tests

Crear `tests/Feature/TrabajoTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Trabajo;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TrabajoTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_trabajos(): void
    {
        $this->get('/trabajos')->assertRedirect('/login');
    }

    public function test_user_with_permission_can_list_trabajos(): void
    {
        $user = User::factory()->create();
        // Asignar permiso trabajos.ver
        $this->actingAs($user)
            ->get('/trabajos')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Trabajos/Index'));
    }

    public function test_context_isolation_works(): void
    {
        // Crear trabajo en contexto MOEVE
        // Login como gestor_repsol
        // Verificar que no ve el trabajo de MOEVE
    }
}
```

### Paso 10: i18n

Editar `resources/js/i18n/locales/es.js` y `en.js`:

```js
trabajos: {
    title: 'Trabajos',
    create: 'Nuevo trabajo',
    edit: 'Editar trabajo',
    fields: {
        numero: 'Nº trabajo',
        descripcion: 'Descripción',
        fechaEncargo: 'Fecha encargo',
        fechaTerminacion: 'Fecha terminación',
        estado: 'Estado',
        empresa: 'Empresa',
        estacion: 'Estación',
        responsable: 'Responsable',
        // MOEVE
        contrato: 'Contrato',
        categoria: 'Categoría',
        // REPSOL
        tipoDocumento: 'Tipo documento',
        tipoTrabajo: 'Tipo trabajo',
        numeroAviso: 'Nº aviso',
        ordenMantenimiento: 'Orden mantenimiento',
    },
    status: {
        borrador: 'Borrador',
        enCurso: 'En curso',
        terminado: 'Terminado',
        cerrado: 'Cerrado',
        cancelado: 'Cancelado',
    },
},
```

### Paso 11: Sidebar

Editar `resources/js/Layouts/AuthenticatedLayout.jsx`:

```jsx
// Cambiar href="#" por route('trabajos.index') en el enlace de Obras
```

### Resumen: archivos por módulo nuevo

| #   | Archivo                                       | Tipo     | Obligatorio           |
| --- | --------------------------------------------- | -------- | --------------------- |
| 1   | `app/Http/Controllers/{Modulo}Controller.php` | Backend  | ✅                    |
| 2   | `app/Http/Requests/Store{Modulo}Request.php`  | Backend  | ✅                    |
| 3   | `app/Http/Requests/Update{Modulo}Request.php` | Backend  | ✅                    |
| 4   | `app/Http/Resources/{Modulo}Resource.php`     | Backend  | ✅                    |
| 5   | `resources/js/Pages/{Modulos}/Index.jsx`      | Frontend | ✅                    |
| 6   | `resources/js/Pages/{Modulos}/Form.jsx`       | Frontend | ✅                    |
| 7   | `resources/js/hooks/use{Modulos}.js`          | Frontend | Opcional              |
| 8   | `tests/Feature/{Modulo}Test.php`              | Testing  | ✅                    |
| 9   | `routes/web.php` (editar)                     | Backend  | ✅                    |
| 10  | `routes/api.php` (editar)                     | Backend  | Si hay API            |
| 11  | `es.js` + `en.js` (editar)                    | Frontend | ✅                    |
| 12  | `AuthenticatedLayout.jsx` (editar)            | Frontend | Si hay enlace sidebar |
| 13  | `DatosBaseSeeder.php` (editar)                | Backend  | ✅                    |

---

## 6. Sistema de contextos

### Tabla `contextos_cliente`

| id_contexto | nombre | codigo |
| ----------- | ------ | ------ |
| 0           | CIETE  | CIETE  |
| 1           | MOEVE  | MOEVE  |
| 2           | REPSOL | REPSOL |

### Trait `HasContext`

```php
// En el modelo:
use App\Traits\HasContext;

class Trabajo extends Model
{
    use HasContext;
    // ContextScope se aplica automáticamente
}
```

El `ContextScope` añade `whereIn('id_contexto', $user->getAccessibleContextIds())` a todas las queries.

### Quién ve qué

| Rol           | Contextos accesibles   |
| ------------- | ---------------------- |
| admin         | TODOS (0, 1, 2)        |
| cierre        | TODOS (0, 1, 2)        |
| usuario       | 1 (MOEVE) + 2 (REPSOL) |
| gestor_moeve  | Solo 1 (MOEVE)         |
| gestor_repsol | Solo 2 (REPSOL)        |

### En el frontend

```jsx
// Obtener contextos del usuario autenticado
const { contexto_ids } = usePage().props.auth.user;
const isMoeve = contexto_ids.includes(1);
const isRepsol = contexto_ids.includes(2);

// Mostrar campos condicionales
{
    isMoeve && <CampoContrato />;
}
{
    isRepsol && <CampoTipoDocumento />;
}
```

---

## 7. RBAC — Roles y permisos

### 5 Roles

| Rol                | Slug            | Acceso                                       |
| ------------------ | --------------- | -------------------------------------------- |
| Administrador      | `admin`         | Todo. Panel admin. Mantenimiento. Broadcast. |
| Usuario            | `usuario`       | Módulos operativos (obras, pedidos, etc.)    |
| Responsable cierre | `cierre`        | Panel cierre + módulos operativos            |
| Gestor Moeve       | `gestor_moeve`  | Solo datos Moeve                             |
| Gestor Repsol      | `gestor_repsol` | Solo datos Repsol                            |

### 31 Permisos (slug pattern: `modulo.accion`)

**Módulos con permisos:** `usuarios`, `empresas_contactos`, `estaciones`, `trabajos`, `pedidos`, `facturas`, `cobros`, `legalizaciones`, `presupuestos`, `tarifarios`, `importaciones`, `informes`, `auditoria`

**Acciones:** `ver`, `crear`, `editar`, `eliminar`, `gestionar`

### Middleware

```php
// En routes/web.php:
Route::middleware('role:admin')->group(...);
Route::middleware('permission:trabajos.ver')->group(...);

// En Form Request:
public function authorize(): bool
{
    return $this->user()->hasPermission('trabajos.crear');
}
```

### Verificar en frontend

```jsx
const { permission_slugs } = usePage().props.auth.user;
const canCreate = permission_slugs.includes("trabajos.crear");
```

---

## 8. Internacionalización (i18n)

### Estructura

```
resources/js/i18n/
  locales/
    es.js    ← Español (idioma principal)
    en.js    ← Inglés
  useI18n.js ← Hook principal
```

### Uso

```jsx
import { useI18n } from "@/i18n/useI18n";

function MyComponent() {
    const { t, locale, setLocale } = useI18n();
    return <h1>{t("trabajos.title")}</h1>;
}
```

### Organización de claves

```js
export default {
    // Módulos de página
    trabajos: { title, create, edit, fields: {}, status: {} },
    pedidos: { ... },
    facturas: { ... },

    // Componentes compartidos
    common: { save, cancel, delete, search, filters, ... },
    messages: { created, updated, deleted, ... },
    validation: { required, maxLength, ... },

    // Páginas especiales
    statusPage: { ... },
    help: { sections: { ... }, faq: { ... } },
};
```

### Regla de oro

**Cada cambio en `es.js` DEBE tener su equivalente en `en.js`**. Mismo número de claves, misma estructura.

---

## 9. Tema y estilos

### Tokens CSS (variables)

El tema usa tokens semánticos definidos en CSS custom properties:

```
--color-primary          → Azul principal
--color-bg-main          → Fondo principal
--color-bg-card          → Fondo de tarjetas
--color-text-main        → Texto principal
--color-text-body        → Texto secundario
--color-text-hint        → Texto terciario
--color-border-base      → Bordes
--color-state-done-*     → Verde (operativo/éxito)
--color-state-pending-*  → Amarillo (warning)
--color-state-blocked-*  → Rojo (error)
```

### Colores de operador

| Operador | Color principal             | Uso                           |
| -------- | --------------------------- | ----------------------------- |
| MOEVE    | Azul (`text-blue-600`)      | Badges, bordes, fondos suaves |
| REPSOL   | Naranja (`text-orange-600`) | Badges, bordes, fondos suaves |
| CIETE    | Gris neutro                 | Fondo por defecto             |

### Tailwind CSS 4.2

```jsx
// Patrón de tarjeta estándar
<div className="rounded-xl border border-border-base bg-bg-card p-4 shadow-sm">

// Patrón de badge de estado
<span className="rounded-full bg-state-done-bg px-2 py-0.5 text-xs text-state-done-text">
    Operativo
</span>

// Patrón de campo de formulario
<input className="w-full rounded-lg border border-border-base bg-bg-main px-3 py-2 text-sm text-text-main
    placeholder:text-text-hint focus:border-primary focus:ring-1 focus:ring-primary" />
```

---

## 10. Testing

### Ejecutar tests

```bash
php artisan test                                    # Todos
php artisan test --filter=TrabajoTest               # Filtrar
php artisan test tests/Feature/TrabajoTest.php      # Archivo específico
php artisan test --parallel                         # Paralelo
```

### Patrón de test estándar

```php
class TrabajoTest extends TestCase
{
    use RefreshDatabase;

    // 1. Guest no puede acceder
    public function test_guest_cannot_access(): void { }

    // 2. Usuario sin permiso no puede acceder
    public function test_user_without_permission_denied(): void { }

    // 3. Usuario con permiso puede hacer CRUD
    public function test_user_with_permission_can_list(): void { }
    public function test_user_with_permission_can_create(): void { }
    public function test_user_with_permission_can_update(): void { }

    // 4. Aislamiento de contexto
    public function test_context_isolation(): void { }

    // 5. Validación condicional
    public function test_moeve_requires_contrato(): void { }
    public function test_repsol_requires_tipo_documento(): void { }
}
```

### Factories

```php
// database/factories/TrabajoFactory.php (crear si no existe)
class TrabajoFactory extends Factory
{
    protected $model = Trabajo::class;

    public function definition(): array
    {
        return [
            'id_contexto' => 1,
            'numero_trabajo' => $this->faker->unique()->numberBetween(1000, 9999),
            'descripcion_trabajo' => $this->faker->sentence(),
            'id_empresa_cliente' => Empresa::factory(),
            'estado' => 'borrador',
            'fecha_encargo' => $this->faker->date(),
        ];
    }
}
```

### Inventario de tests actual (v1.2.0 — 13/04/2026)

> **58 tests, 248 assertions — 100% passing**  
> Última ejecución: `php artisan test` → 17.50s

#### Unit tests (6 archivos, 17 tests)

| Archivo                                      | Tests | Qué cubre                                                                                                         |
| -------------------------------------------- | ----- | ----------------------------------------------------------------------------------------------------------------- |
| `tests/Unit/ExampleTest.php`                 | 1     | Smoke test (true is true)                                                                                         |
| `tests/Unit/Models/ContextoClienteTest.php`  | 2     | Tabla, PK, fillable, cast booleano `activo`                                                                       |
| `tests/Unit/Models/EmpresaTest.php`          | 4     | Tabla, PK, fillable, scope `clientes()`, scope `activas()`, relación `hasMany` estaciones                         |
| `tests/Unit/Models/EstacionServicioTest.php` | 3     | Tabla, PK, fillable, casts (decimal, date, boolean), relación `belongsTo` empresa                                 |
| `tests/Unit/Traits/ApiResponseTest.php`      | 3     | Trait `ApiResponse`: respuesta éxito, paginada, error — estructura JSON                                           |
| `tests/Unit/Traits/HasContextTest.php`       | 4     | Trait `HasContext` + `ContextScope`: registro scope, auto-inyección al crear, filtro por contexto, multi-contexto |

#### Feature tests (15 archivos, 41 tests)

| Archivo                                                 | Tests | Qué cubre                                                                                               |
| ------------------------------------------------------- | ----- | ------------------------------------------------------------------------------------------------------- |
| `tests/Feature/ExampleTest.php`                         | 1     | Smoke: `GET /` redirige a login                                                                         |
| `tests/Feature/Auth/AuthenticationTest.php`             | 4     | Login: render, éxito, password incorrecto, logout                                                       |
| `tests/Feature/Auth/LoginCsrfTest.php`                  | 1     | CSRF expirado → redirect a `/login` con mensaje flash (419)                                             |
| `tests/Feature/Auth/PasswordConfirmationTest.php`       | 1     | Ruta `password.confirm` deshabilitada (GET redirige, POST 405)                                          |
| `tests/Feature/Auth/PasswordUpdateTest.php`             | 2     | `PUT /password`: update OK + rechazo por password incorrecto                                            |
| `tests/Feature/Auth/RegistrationTest.php`               | 1     | Ruta `register` deshabilitada → redirect a `/login`                                                     |
| `tests/Feature/Auth/SeededUsersAuthenticationTest.php`  | 1     | Login de los 5 usuarios seed (admin, cesar, usuario, moeve, repsol)                                     |
| `tests/Feature/AdminDashboardTest.php`                  | 3     | `/admin`: guest redirect, no-admin 403, admin 200                                                       |
| `tests/Feature/ApiAuthTest.php`                         | 3     | API: login token, guest denied, `/me` autenticado                                                       |
| `tests/Feature/Api/ClientesEstacionesApiTest.php`       | 7     | CRUD `/api/v1/clientes` y `/api/v1/estaciones`: auth, permisos, contexto, validación CIF, código postal |
| `tests/Feature/ErrorPagesTest.php`                      | 4     | Páginas error: 403, 404 corporativo, guest redirect, preview `/_{code}`                                 |
| `tests/Feature/MaintenanceModeTest.php`                 | 6     | Mantenimiento: toggle on/off, no-admin bloqueado, admin bypass, estado compartido Inertia               |
| `tests/Feature/ProfileTest.php`                         | 3     | `/profile`: render, avatar update, validación avatar catálogo                                           |
| `tests/Feature/RoleModuleAccessTest.php`                | 3     | RBAC rutas: módulos compartidos, `/cierre` solo cierre, `/admin` solo admin                             |
| `tests/Feature/Seeders/UsuariosInicialesSeederTest.php` | 1     | Seeder: roles y permisos asignados correctamente a admin, cesar, usuario                                |

#### Cobertura por módulo

| Módulo                                | Estado test             | Archivos                                     |
| ------------------------------------- | ----------------------- | -------------------------------------------- |
| Auth (login/logout/password)          | ✅ Completo             | 6 archivos, 10 tests                         |
| Modelos (Empresa, Estación, Contexto) | ✅ Completo             | 3 archivos, 9 tests                          |
| Traits (ApiResponse, HasContext)      | ✅ Completo             | 2 archivos, 7 tests                          |
| API Clientes/Estaciones               | ✅ Completo             | 1 archivo, 7 tests                           |
| RBAC + roles + seeders                | ✅ Completo             | 3 archivos, 7 tests                          |
| Error pages                           | ✅ Completo             | 1 archivo, 4 tests                           |
| Mantenimiento                         | ✅ Completo             | 1 archivo, 6 tests                           |
| Perfil                                | ✅ Completo             | 1 archivo, 3 tests                           |
| API Auth (tokens)                     | ✅ Completo             | 1 archivo, 3 tests                           |
| Mensajes internos                     | ⏳ Pendiente Sprint 03+ | —                                            |
| Soporte técnico                       | ⏳ Pendiente Sprint 03+ | —                                            |
| Trabajos (Obras)                      | ⏳ Pendiente Sprint 03  | Planificado: `TrabajoTest.php` (10 tests)    |
| Importaciones                         | ⏳ Pendiente Sprint 03  | Planificado: `ImportacionTest.php` (5 tests) |

#### Factories disponibles

| Factory                       | Modelo             | Notas                                          |
| ----------------------------- | ------------------ | ---------------------------------------------- |
| `UserFactory.php`             | `User`             | Incluye `afterCreating` para contextos + roles |
| `EmpresaFactory.php`          | `Empresa`          | Con estados: `cliente()`, `proveedor()`        |
| `EstacionServicioFactory.php` | `EstacionServicio` | Con `id_empresa` FK                            |
| `ContextoClienteFactory.php`  | `ContextoCliente`  | CIETE, MOEVE, REPSOL                           |

---

## 11. Comandos útiles

### Desarrollo diario

```bash
# Arrancar todo
npm run dev                          # Vite dev server (HMR)
php artisan serve                    # Laravel dev server (si no XAMPP)

# Después de cambios backend
php artisan optimize:clear           # Limpiar todas las cachés
php artisan test                     # Verificar que no hay regresiones

# Después de cambios frontend
npm run build                        # Build producción

# Base de datos
php artisan migrate                  # Ejecutar migraciones pendientes
php artisan migrate:refresh --seed   # RESET completo + seed
php artisan db:seed                  # Solo seed (sin reset)

# Inspección
php artisan route:list               # Ver todas las rutas
php artisan model:show Trabajo       # Ver detalles de modelo
php artisan tinker                   # REPL interactivo
```

### Git workflow

```bash
# Rama principal de trabajo
git checkout versionDesplegada

# Antes de empezar
git pull origin versionDesplegada

# Commits
git add .
git commit -m "Sprint 03: TrabajoController CRUD + tests"

# Push
git push origin versionDesplegada
```

---

## 12. Versionado

### Esquema: Semantic Versioning (SemVer)

`vMAJOR.MINOR.PATCH`

| Tipo      | Cuándo incrementar                            | Ejemplo         |
| --------- | --------------------------------------------- | --------------- |
| **MAJOR** | Cambio incompatible (nueva BBDD, ruptura API) | v1.0.0 → v2.0.0 |
| **MINOR** | Nuevo módulo o funcionalidad significativa    | v1.1.0 → v1.2.0 |
| **PATCH** | Corrección de bugs, ajustes menores           | v1.2.0 → v1.2.1 |

### Dónde se define

**Único punto:** `app/Http/Controllers/StatusController.php` línea 56:

```php
'version' => 'v1.2.0',
```

### Historial de versiones

| Versión | Fecha      | Sprint     | Cambios principales                                                                                             |
| ------- | ---------- | ---------- | --------------------------------------------------------------------------------------------------------------- |
| v1.0.0  | 26/03/2026 | Sprint 01  | Arranque: auth, RBAC, layout, sidebar, error pages, tema                                                        |
| v1.1.0  | 10/04/2026 | Sprint 02  | Clientes CRUD, Estaciones CRUD, mantenimiento, recuperar password, mensajes, soporte, ayuda, estado del sistema |
| v1.2.0  | 13/04/2026 | Sprint 02+ | Seguridad rate-limit (5 intentos/5 min), estado role-based (admin vs user), i18n fixes, manual expandido        |

---

## 13. Changelog

### v1.2.0 — 13/04/2026

**Seguridad:**

- Rate limiting mejorado: 5 intentos de login por 5 minutos por email+IP (decay 300s).
- Mensajes de bloqueo temporal profesionales en ES/EN.
- Página de estado filtra datos sensibles de infraestructura para usuarios no-admin.

**Status page (role-based):**

- Admin: ve 6 tarjetas completas (BBDD driver+nombre, almacenamiento MB, correo driver, cola driver, app completa, mantenimiento).
- Usuario: ve 3 tarjetas simplificadas (app versión, correo estado, mantenimiento activo/no).
- Frontend: `Status.jsx` recibe prop `isAdmin` del controller.

**i18n:**

- Eliminadas referencias a icono "sol/luna"/"sun/moon" inexistente → "selector de tema".
- Correos de ejemplo cambiados a `pablo@ciete.es` (eliminados correos reales).
- FAQ actualizada con terminología correcta.

**Manual de ayuda:**

- Sección de estado actualizada con nota de visibilidad admin/usuario.
- Tarjetas del manual marcadas con "(solo admin)" donde corresponde.
- Info box añadido en Help.jsx para la sección de estado.

**Archivos modificados:**

- `app/Http/Controllers/StatusController.php`
- `app/Http/Requests/Auth/LoginRequest.php`
- `lang/es/auth.php`, `lang/en/auth.php`
- `resources/js/Pages/Status.jsx`
- `resources/js/Pages/Help.jsx`
- `resources/js/i18n/locales/es.js`, `en.js`

### v1.1.0 — 10/04/2026

**Módulos nuevos:**

- Clientes (Empresas): CRUD completo con validación CIF, contexto, búsqueda.
- Estaciones de servicio: CRUD completo con código estación, extensiones Moeve/Repsol.
- Modo mantenimiento: toggle admin, bypass admin, middleware.
- Recuperación de contraseña: flujo forgot → email → reset.
- Mensajes internos: inbox, enviados, archivados, broadcast.
- Soporte técnico: formulario + envío email.
- Estado del sistema: 6 tarjetas de salud.
- Manual de ayuda in-app: 10+ secciones, FAQ, i18n completo.
- SupportDock flotante: 5 botones de acceso rápido.

**Tests:** 58 tests, 248 assertions.

### v1.0.0 — 26/03/2026

**Infraestructura base:**

- Laravel 12 + Inertia.js + React 19 + MariaDB.
- Login/logout con seguimiento de sesiones.
- RBAC: 5 roles, 31 permisos, middleware.
- Multi-contexto: ContextScope, HasContext trait.
- 33 tablas, 29 modelos Eloquent.
- Layout responsive con sidebar y tema claro/oscuro.
- Error pages corporativas: 401, 403, 404, 419, 500, 503.
- i18n español/inglés completo.
- API REST v1 con Sanctum.

---

## 14. Checklist de entrega por sprint

### Antes de empezar el sprint

- [ ] Leer esta biblia y el plan detallado del sprint.
- [ ] Verificar que `php artisan test` pasa al 100%.
- [ ] Verificar que `npm run build` compila sin errores.
- [ ] `git pull` para tener la última versión.

### Durante el sprint (cada tarea)

- [ ] Crear archivos según el plan (controller, request, resource, pages, hook).
- [ ] Añadir traducciones en `es.js` Y `en.js`.
- [ ] Añadir rutas en `web.php` y/o `api.php`.
- [ ] Crear tests (mínimo: guest denied, permission check, CRUD, context isolation).
- [ ] Ejecutar `php artisan test` después de cada cambio significativo.
- [ ] Ejecutar `npm run build` para verificar compilación frontend.
- [ ] Rellenar la bitácora diaria en `docs/05-SPRINTS/Sprint_XX/Bitacora/`.

### Al terminar el sprint

- [ ] Todos los tests pasan.
- [ ] Build sin errores.
- [ ] Actualizar versión en `StatusController.php`.
- [ ] Actualizar este changelog.
- [ ] Actualizar `Plan_Detallado_Sprints.md` (marcar tareas completadas).
- [ ] `git commit` y `push`.
- [ ] Seed con datos demo que permitan probar la funcionalidad.

---

_Fin de la Biblia de Desarrollo. Mantener actualizada con cada sprint._
