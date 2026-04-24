<p align="center">
  <img src="public/favicon.svg" alt="ERP Ciete Logo" width="108">
</p>

<h1 align="center">ERP Ciete — Plataforma de gestión para ingeniería</h1>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Inertia.js-2/3-9553E9?style=for-the-badge" alt="Inertia.js">
  <img src="https://img.shields.io/badge/React-19-61DAFB?style=for-the-badge&logo=react&logoColor=111827" alt="React 19">
  <img src="https://img.shields.io/badge/TailwindCSS-4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/Vite-8-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite 8">
</p>

ERP interno para gestión operativa de empresas de ingeniería. Centraliza control de trabajos, clientes, estaciones, importaciones y administración de usuarios bajo un sistema de roles, permisos y contextos (MOEVE / REPSOL).

Desarrollado por ABACO para Ciete Ingenieros.

## Estado del proyecto

| Dato          | Valor                                                  |
| ------------- | ------------------------------------------------------ |
| Versión       | v1.4.2                                                 |
| Sprint actual | Sprint 03 cerrado — Sprint 04 en preparación           |
| Tests         | 65 pass (2 fail preexistentes en `TrabajoRequestTest`) |
| Build         | Vite 8 — OK                                            |

## Stack técnico

| Capa          | Tecnología      | Versión (composer/package.json) |
| ------------- | --------------- | ------------------------------- |
| Backend       | Laravel         | `^12.0` (runtime 12.54.1)       |
| PHP           | PHP             | `^8.2` (runtime 8.4.12)         |
| Adapter SSR   | Inertia Laravel | `^2.0`                          |
| Frontend SPA  | Inertia React   | `^3.0.3`                        |
| UI framework  | React           | `^19.2.5`                       |
| CSS           | Tailwind CSS    | `^4.2.2`                        |
| Bundler       | Vite            | `^8.0.8`                        |
| Auth          | Laravel Sanctum | sesiones cookie                 |
| BD local      | SQLite          | —                               |
| BD producción | MySQL           | —                               |

## Módulos funcionales

### Operativos (usuario autenticado)

- **Trabajos** — CRUD completo con aislamiento por contexto. Permisos: `trabajos.ver`, `trabajos.crear`, `trabajos.editar`, `trabajos.eliminar`.
- **Clientes** — CRUD web + API REST (`/api/v1/clientes`).
- **Estaciones de servicio** — CRUD web + API REST (`/api/v1/estaciones`). Modelos diferenciados por contexto (MOEVE/REPSOL + extensiones).
- **Importaciones** — Carga de datos desde Excel con `ExcelParserService`.
- **Dashboard usuario** — Panel post-login con resumen operativo.

### Administración (`/admin`, rol `admin`)

- **Dashboard admin** — Estadísticas generales, listado de usuarios, actividad reciente (auditoría).
- **Gestión de usuarios** — Crear, editar, activar/desactivar usuarios. Asignación de roles y contextos.
- **Auditoría** — Registro de actividad por usuario (`AuditLog`).
- **Modo mantenimiento** — Toggle desde panel admin.
- **Avisos del sistema** — Editor bilingüe (ES/EN) de avisos, actualizaciones y noticias. Se muestran en la pantalla de inicio según el idioma del usuario. Persistencia en `storage/app/notices.json`.

### Transversales

- **Autenticación** — Login, logout, recuperación y cambio de contraseña.
- **Roles y permisos** — `admin`, `usuario`, `cierre`, `gestor_moeve`, `gestor_repsol`. Middleware `role:` y `permission:`.
- **Contextos** — MOEVE (id=1) y REPSOL (id=2). Aislamiento de datos por contexto del usuario.
- **i18n** — Interfaz bilingüe ES/EN con hook `useI18n`. Archivos en `resources/js/i18n/locales/`.
- **Perfil** — Edición de datos, cambio de password, selección de avatar corporativo.
- **Pantalla de inicio** — Welcome institucional con avisos dinámicos por idioma.

## Estructura del proyecto

```
app/
  Http/
    Controllers/
      Admin/              # DashboardController, UserController, NoticeController, MaintenanceController
      Api/                # TrabajoController, ClienteController, EstacionController...
    Middleware/            # HandleInertiaRequests, EnsureMaintenanceMode, CheckRole, CheckPermission
    Requests/             # Validaciones (Store/Update para cada recurso)
    Resources/            # API Resources (Trabajo, Contrato, Estacion, TipoTrabajo...)
  Models/                 # Eloquent: User, Trabajo, Contrato, Empresa, EstacionServicio, AuditLog...
  Services/               # ExcelParserService
  Traits/                 # Reutilizables
resources/js/
  Pages/                  # Inertia pages (Welcome, Dashboard, Admin/*, Trabajos/*, Clientes/*, Estaciones/*)
  Components/ui/          # BadgeTrabajo, TrabajosColumnas...
  Hooks/                  # useTrabajos, useEstaciones, useI18n, useTheme...
  Layouts/                # AuthenticatedLayout
  i18n/locales/           # es.js, en.js
routes/
  web.php                 # Rutas web (Inertia) + admin
  api.php                 # API REST v1
  auth.php                # Rutas de autenticación
database/
  migrations/             # Esquema de BD
  seeders/                # DatosBaseSeeder (roles, permisos, contextos, usuarios iniciales)
  factories/              # TrabajoFactory
docs/
  01_ORGANIZACION/        # Normas de equipo y flujo Git
  02_CLIENTE/             # Requisitos y contexto de negocio
  03_API_ERP/             # Contratos API y mapeos
  04_DISENO_UI/           # Guías de diseño
  05-SPRINTS/             # Entregables y bitácoras por sprint
tests/
  Feature/                # TrabajoTest, Auth, Seeder, ErrorPages, Maintenance, RoleAccess...
```

## Puesta en marcha local

### Requisitos

- PHP 8.2 o superior
- Composer 2
- Node.js 18 o superior
- npm 10 o superior

### Instalación

```bash
git clone https://github.com/erpCiete/ERP_Ciete.git
cd ERP_Ciete
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Con SQLite (por defecto en local):

```bash
php artisan migrate --seed
```

### Ejecución

```bash
composer run dev
```

O en terminales separadas:

```bash
php artisan serve
npm run dev
```

### Comandos útiles

```bash
php artisan test                    # Tests (65 pass)
php artisan migrate:fresh --seed    # Resetear BD con datos base
php artisan optimize:clear          # Limpiar cachés
./vendor/bin/pint                   # Formateo de código PHP
npm run build                       # Build de producción
```

## Seeders

`DatosBaseSeeder` carga:

- Roles: admin, usuario, cierre, gestor_moeve, gestor_repsol
- Permisos por módulo (trabajos, clientes, estaciones, importaciones, admin)
- Contextos: MOEVE, REPSOL
- Usuarios iniciales con asignación de rol y contexto

## Flujo Git

| Rama                    | Propósito                                                        |
| ----------------------- | ---------------------------------------------------------------- |
| `main`                  | Producción estable. Solo recibe merges de `develop` mediante PR. |
| `develop`               | Integración. Recibe features cerradas.                           |
| `versionDesplegada`     | Snapshot del último despliegue real.                             |
| `feature/*`, `chore/*`  | Ramas de trabajo. Se abren desde `develop`.                      |
| `back-dev`, `front-dev` | Ramas de integración por equipo (back/front).                    |

## Documentación técnica

La documentación vive en `docs/` y se organiza por sprint:

- `docs/01_ORGANIZACION/` — Normas, flujo Git, convenciones
- `docs/02_CLIENTE/` — Requisitos de negocio
- `docs/03_API_ERP/` — Contratos API (Trabajos, importaciones)
- `docs/04_DISENO_UI/` — Guías de interfaz y tokens de diseño
- `docs/05-SPRINTS/` — Entregables y bitácoras por sprint

Cada sprint tiene su carpeta con bitácoras por persona en `Bitacora/General/`, `Bitacora/BACK/` o `Bitacora/FRONT/`.

## Contacto

Desarrollado por ABACO para Ciete Ingenieros.  
Para continuidad: revisar `docs/` y las bitácoras del sprint correspondiente.
