<p align="center">
  <img src="public/favicon.svg" alt="Logotipo de ERP Ciete" width="108">
</p>

<h1 align="center">ERP Ciete</h1>

<p align="center">
  Plataforma interna para gestionar trabajos de ingeniería, pedidos, facturas, cierres y exportaciones operativas.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.54-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 12.54">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.2 o superior">
  <img src="https://img.shields.io/badge/Inertia.js-3-9553E9?style=flat-square" alt="Inertia.js 3">
  <img src="https://img.shields.io/badge/React-19.2-61DAFB?style=flat-square&logo=react&logoColor=111827" alt="React 19.2">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4.2-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4.2">
  <img src="https://img.shields.io/badge/Vite-8.0-646CFF?style=flat-square&logo=vite&logoColor=white" alt="Vite 8.0">
</p>

ERP Ciete centraliza la operativa de Ciete Ingenieros S.A. para los contextos **MOEVE**, **REPSOL** y **OTROS CLIENTES**. La aplicación cubre el ciclo completo desde el alta de un trabajo hasta su facturación y cierre, con permisos por rol, trazabilidad y exportaciones específicas para MOEVE y ARIBA.

Desarrollado por ABACO para Ciete Ingenieros S.A.

## Estado del proyecto

| Dato | Valor |
| --- | --- |
| Versión funcional | `v2.2.0` |
| Estado | En revisión funcional |
| Build verificado | `npm run build` correcto |
| Tests verificados | 270 correctos, 3 fallidos, 1679 aserciones |
| Documentación actualizada | 8 de junio de 2026 |

La fuente de verdad documental está en [`docs/00_ENTREGA_FINAL/`](docs/00_ENTREGA_FINAL/). Para una primera lectura, consulta [`00_LÉEME_PRIMERO.md`](docs/00_ENTREGA_FINAL/00_LÉEME_PRIMERO.md).

Fallos conocidos de la última ejecución completa:

- `AdminAccessTest`: el acceso de administración a `/dashboard` devuelve `403` en lugar de `200`.
- `ContextCreationGuardTest`: la creación de un pedido de OTROS CLIENTES exige un tarifario válido.
- `ImportacionesAccessTest`: falta `resources/js/Pages/Importaciones/Form.jsx` en el manifest de Vite.

## Funcionalidades principales

- Gestión de trabajos por contexto, contrato, estación, responsable y estado.
- Pedidos con líneas de tarifario y bloqueo de tarifa cuando comienza la operativa.
- Facturación, control de importes y panel de cierre para Dirección.
- Maestros de clientes, estaciones, contratos, tarifarios y sociedades facturadoras.
- Exportaciones MOEVE en PDF, CSV y cuadro ARIBA.
- Importación controlada de Excel y CSV.
- Usuarios, roles, permisos, auditoría, avisos, soporte y estado del sistema.
- Interfaz en español e inglés. Ciete Excel es el modo por defecto; el usuario puede cambiar a Ciete Moderno desde `/profile` y conservar esa preferencia.

## Requisitos

| Herramienta | Versión |
| --- | --- |
| PHP | `8.2+` |
| Composer | `2.x` |
| Node.js | `^20.19.0` o `>=22.12.0` |
| npm | `10+` |
| MySQL / MariaDB | MySQL `8+` o MariaDB compatible |

Composer comprobará las extensiones PHP necesarias durante la instalación. En XAMPP deben estar disponibles, entre otras, `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `zip` y `gd`.

> En Windows con XAMPP, añade `C:\xampp\php` y `C:\xampp\mysql\bin` al `PATH` para poder ejecutar `php` y `mysql` desde cualquier terminal.

## Instalación rápida con datos demo

Esta es la opción recomendada para revisar todas las funciones con información de ejemplo.

### 1. Descargar e instalar dependencias

```bash
git clone https://github.com/erpCiete/ERP_Ciete.git
cd ERP_Ciete

composer install
npm ci
```

### 2. Crear el archivo de entorno

En Linux, macOS, Git Bash o WSL:

```bash
cp .env.example .env
php artisan key:generate
```

En PowerShell:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Configura estas variables en `.env`:

```env
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=abaco_ciete
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=log
```

### 3. Restaurar la base de datos demo

El volcado crea la base `abaco_ciete` e incluye estructura, catálogos, usuarios y datos demo.

```bash
mysql -u root < database/schema/abaco_ciete_v220_2026-06-04.sql
php artisan migrate
php artisan optimize:clear
```

Si el usuario de MySQL tiene contraseña:

```bash
mysql -u root -p < database/schema/abaco_ciete_v220_2026-06-04.sql
php artisan migrate
```

### 4. Iniciar la aplicación

```bash
composer run dev
```

Este comando inicia Laravel, la cola y Vite. Abre **http://127.0.0.1:8000**.

## Instalación limpia

Utiliza esta alternativa cuando necesites una base sin trabajos, pedidos ni facturas demo.

```sql
CREATE DATABASE abaco_ciete
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Después de configurar `.env`:

```bash
php artisan migrate --seed
composer run dev
```

`DatabaseSeeder` crea los contextos, roles, permisos, catálogos, usuarios iniciales y avisos del sistema.

## Usuarios iniciales

| Rol | Correo | Contraseña |
| --- | --- | --- |
| Administración técnica | `admin@ciete.es` | `Admin1234!` |
| Dirección | `cesar@ciete.es` | `Cesar1234!` |
| Ejecución general | `usuario@ciete.es` | `Usuario1234!` |
| Ejecución MOEVE | `moeve@ciete.es` | `Moeve1234!` |
| Ejecución REPSOL | `repsol@ciete.es` | `Repsol1234!` |
| Contabilidad | `contable@ciete.es` | `Contable1234!` |

Estas credenciales son exclusivamente para entornos locales y demo. Deben sustituirse antes de cualquier despliegue real.

## Flujo funcional

1. El usuario accede con un rol y un contexto activo.
2. Crea o consulta un trabajo asociado a cliente, estación, contrato y tarifario.
3. Genera pedidos y añade líneas del tarifario.
4. Registra facturas vinculadas a los pedidos.
5. Dirección revisa y cierra los trabajos completados.
6. Cuando corresponde, se generan exportaciones MOEVE, CSV o ARIBA.

Los estados del trabajo se calculan a partir de su operativa. No deben modificarse directamente salvo en los flujos expresamente habilitados.

## Comandos habituales

```bash
# Entorno de desarrollo: Laravel + cola + Vite
composer run dev

# Servidor y frontend en terminales separadas
php artisan serve
npm run dev

# Build de producción
npm run build

# Ejecutar migraciones pendientes
php artisan migrate

# Limpiar todas las cachés de Laravel
php artisan optimize:clear

# Formatear PHP
./vendor/bin/pint
```

### Tests

La suite usa una base MySQL independiente llamada `abaco_ciete_testing`. Créala una vez antes de ejecutar los tests:

```sql
CREATE DATABASE abaco_ciete_testing
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan test
```

No apuntes `phpunit.xml` a una base con datos que necesites conservar ni ejecutes varias suites simultáneamente sobre `abaco_ciete_testing`.

## Estructura del repositorio

```text
app/
├── Http/                 Controladores, middleware, requests y resources
├── Models/               Modelos Eloquent
└── Services/             Lógica de negocio, importaciones y exportaciones
database/
├── migrations/           Fuente de verdad del esquema
├── seeders/              Datos mínimos de instalación
├── schema/               Volcado completo de la demo
└── manual/               Scripts locales; no ejecutar en producción
resources/
├── js/                   Aplicación React + Inertia
└── css/                  Estilos Tailwind CSS
routes/
├── web/                  Rutas web separadas por módulo
└── api.php               API REST
tests/                    Tests unitarios y funcionales
docs/
├── 00_ENTREGA_FINAL/     Documentación vigente
├── 00_FUENTES_CLIENTE/   Fuentes originales
└── _archivo_historico/   Trazabilidad; no usar como guía actual
```

## Documentación

| Necesidad | Documento |
| --- | --- |
| Empezar y entender el estado actual | [`00_LÉEME_PRIMERO.md`](docs/00_ENTREGA_FINAL/00_LÉEME_PRIMERO.md) |
| Instalación detallada | [`01_INSTALACIÓN_LOCAL.md`](docs/00_ENTREGA_FINAL/01_INSTALACIÓN_LOCAL.md) |
| Resumen y estado funcional | [`02_RESUMEN_PROYECTO_Y_ESTADO.md`](docs/00_ENTREGA_FINAL/02_RESUMEN_PROYECTO_Y_ESTADO.md) |
| Arquitectura técnica | [`04_ARQUITECTURA_TÉCNICA.md`](docs/00_ENTREGA_FINAL/04_ARQUITECTURA_TÉCNICA.md) |
| Base de datos | [`05_BASE_DATOS_DECISIONES.md`](docs/00_ENTREGA_FINAL/05_BASE_DATOS_DECISIONES.md) |
| Uso por roles | [`06_GUÍA_USO_POR_ROLES.md`](docs/00_ENTREGA_FINAL/06_GUÍA_USO_POR_ROLES.md) |
| Flujo de trabajos a cierre | [`08_TRABAJOS_PEDIDOS_FACTURAS_CIERRE.md`](docs/00_ENTREGA_FINAL/08_TRABAJOS_PEDIDOS_FACTURAS_CIERRE.md) |
| Exportaciones MOEVE y ARIBA | [`09_EXPORTACIÓN_MOEVE_ARIBA_CORREO.md`](docs/00_ENTREGA_FINAL/09_EXPORTACIÓN_MOEVE_ARIBA_CORREO.md) |
| Tests y validación | [`13_TESTING_VALIDACIÓN.md`](docs/00_ENTREGA_FINAL/13_TESTING_VALIDACIÓN.md) |
| Despliegue | [`14_DESPLIEGUE.md`](docs/00_ENTREGA_FINAL/14_DESPLIEGUE.md) |
| Pendientes y riesgos | [`15_PENDIENTES_Y_RIESGOS.md`](docs/00_ENTREGA_FINAL/15_PENDIENTES_Y_RIESGOS.md) |
| Consulta operativa rápida | [`16_GUÍA_RÁPIDA.md`](docs/00_ENTREGA_FINAL/16_GUÍA_RÁPIDA.md) |

## Reglas de seguridad y datos

- No subas `.env`, credenciales, backups locales ni datos reales del cliente.
- No ejecutes `migrate:fresh` sobre `abaco_ciete` si necesitas conservar su contenido.
- No ejecutes los scripts de `database/manual/` en producción.
- Cambia los usuarios y contraseñas iniciales antes de desplegar.
- Configura los campos ARIBA reales del contrato antes de generar documentación definitiva.

## Problemas frecuentes

**`php`, `composer` o `mysql` no se reconocen**

Añade sus directorios al `PATH` o ejecuta los binarios desde la instalación de XAMPP.

**Falta `APP_KEY`**

```bash
php artisan key:generate
```

**La interfaz no carga estilos o JavaScript**

```bash
npm ci
npm run build
php artisan optimize:clear
```

**Error de conexión a MySQL**

Comprueba que MySQL está iniciado y revisa `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en `.env`.

**Los correos fallan en local**

Usa `MAIL_MAILER=log`. Los mensajes quedarán registrados en `storage/logs/laravel.log`.

<!-- ORGANIZACION-PROFESIONAL-20260708 -->
## Organización profesional

- Carpeta canónica local: `ERP-CIETE`.
- Remoto actual: `kampexiii/ERP_Ciete`.
- Rama activa local: `Desplegadav2`.
- Prioridad: conservar documentación funcional, estado de tests y notas de entrega sin exponer datos sensibles.
- Próximo paso recomendado: separar claramente documentación pública de documentación interna de cliente.
