# Instalación local de ERP CIETE

Esta guía permite levantar el proyecto con una base demo completa o con una instalación limpia.

## Requisitos

| Herramienta | Versión mínima | Comprobación |
| --- | --- | --- |
| PHP | 8.2 | `php -v` |
| Composer | 2.x | `composer --version` |
| Node.js | 20.19 o 22.12 | `node --version` |
| npm | 10.x | `npm --version` |
| MySQL / MariaDB | MySQL 8 o compatible | `mysql --version` |

XAMPP y Laragon son válidos. En Windows, añade al `PATH` los directorios de PHP y MySQL para poder ejecutar los comandos desde cualquier terminal:

```text
C:\xampp\php
C:\xampp\mysql\bin
```

## 1. Obtener el proyecto

```bash
git clone https://github.com/erpCiete/ERP_Ciete.git
cd ERP_Ciete
```

También puedes copiar o descomprimir el proyecto en el directorio de trabajo del servidor local.

## 2. Instalar dependencias

```bash
composer install
npm ci
```

Usa `npm install` únicamente cuando necesites actualizar dependencias o modificar `package-lock.json`.

## 3. Configurar el entorno

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

Configura MySQL y el correo local en `.env`:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=abaco_ciete
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=log
```

No subas el archivo `.env` al repositorio.

## 4. Preparar la base de datos

Elige una de las siguientes opciones.

### Opción A: demo completa recomendada

El volcado incluye la estructura, los usuarios iniciales y datos demo de MOEVE, REPSOL y OTROS CLIENTES. También crea la base `abaco_ciete`.

```bash
mysql -u root < database/schema/abaco_ciete_v220_2026-06-04.sql
php artisan optimize:clear
```

Si MySQL solicita contraseña:

```bash
mysql -u root -p < database/schema/abaco_ciete_v220_2026-06-04.sql
```

### Opción B: instalación limpia

Crea la base:

```sql
CREATE DATABASE abaco_ciete
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Ejecuta todas las migraciones y el seeder principal:

```bash
php artisan migrate --seed
```

`DatabaseSeeder` instala contextos, roles, permisos, catálogos, usuarios iniciales y avisos. Esta opción no crea trabajos, pedidos ni facturas demo.

## 5. Iniciar la aplicación

La forma más sencilla es:

```bash
composer run dev
```

Este comando inicia:

- Laravel en `http://127.0.0.1:8000`.
- El proceso de cola.
- Vite para compilar el frontend durante el desarrollo.

También puedes usar dos terminales:

```bash
php artisan serve
```

```bash
npm run dev
```

## Usuarios iniciales

| Rol | Correo | Contraseña |
| --- | --- | --- |
| Administración técnica | `admin@ciete.es` | `Admin1234!` |
| Dirección | `cesar@ciete.es` | `Cesar1234!` |
| Ejecución general | `usuario@ciete.es` | `Usuario1234!` |
| Ejecución MOEVE | `moeve@ciete.es` | `Moeve1234!` |
| Ejecución REPSOL | `repsol@ciete.es` | `Repsol1234!` |
| Contabilidad | `contable@ciete.es` | `Contable1234!` |

Cambia estas credenciales antes de cualquier despliegue real.

## Tests

La suite está configurada para usar una base independiente llamada `abaco_ciete_testing`.

Créala una vez:

```sql
CREATE DATABASE abaco_ciete_testing
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Ejecuta la suite:

```bash
php artisan test
```

No configures los tests para usar `abaco_ciete`, porque podrían eliminar o modificar sus datos. Tampoco ejecutes varias suites simultáneamente sobre `abaco_ciete_testing`.

## Comandos útiles

```bash
# Build para producción
npm run build

# Ver rutas disponibles
php artisan route:list

# Ver migraciones
php artisan migrate:status

# Limpiar cachés
php artisan optimize:clear

# Formatear PHP
./vendor/bin/pint
```

## Problemas frecuentes

### `php`, `composer` o `mysql` no se reconocen

Añade sus directorios al `PATH` o ejecuta directamente los binarios de XAMPP.

### Error por ausencia de clave de aplicación

```bash
php artisan key:generate
```

### Error de conexión a MySQL

Comprueba que MySQL está iniciado y revisa todas las variables `DB_*` de `.env`.

### Vite no encuentra dependencias

```bash
npm ci
npm run build
```

### La aplicación sigue usando configuración anterior

```bash
php artisan optimize:clear
```

### Los correos fallan en local

Configura `MAIL_MAILER=log`. Laravel guardará los mensajes en `storage/logs/laravel.log`.

## Advertencias

- No uses `php artisan migrate:fresh` sobre una base cuyos datos necesites conservar.
- No ejecutes los scripts de `database/manual/` en producción.
- Los backups locales de `database/backups/` no se versionan.
- Los valores ARIBA de la demo deben sustituirse antes de un uso real.
