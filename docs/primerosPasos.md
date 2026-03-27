# Creación del proyecto base

## Objetivo

Dejar preparado el proyecto base sobre el que trabajará el equipo 1, usando Laravel 12 con React y Vite, para que todos puedan partir de una misma estructura técnica desde el inicio.

## Pasos realizados

### 1. Creación del proyecto Laravel

Se creó el proyecto base con Laravel 12 mediante Composer:

```
composer create-project laravel/laravel:^12.0 practicaAbacoProyectoCiete
```

### 2. Instalación de Breeze

Se añadió Laravel Breeze como sistema base de arranque para integrar autenticación y estructura inicial compatible con React:

```
composer require laravel/breeze --dev
```

### 3. Instalación de Breeze con React

Se generó la estructura base del frontend con React:

```
php artisan breeze:install react
```

### 4. Instalación de dependencias del frontend

Se instalaron las dependencias necesarias de Node para compilar y ejecutar la parte frontend con Vite:

```
npm install
```

### 5. Configuración inicial de Laravel

Se generó la clave de aplicación y se dejó preparado el archivo de entorno:

```
php artisan key:generate
```

### 6. Configuración de base de datos

Antes de ejecutar las migraciones hay que crear la base de datos en XAMPP (phpMyAdmin).

Nombre actual configurado en `.env`:

- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=abaco_ciete`

Por tanto, en phpMyAdmin hay que crear una base de datos llamada `abaco_ciete`.

Si usáis otro nombre, actualizad `DB_DATABASE` en `.env` para que coincida exactamente con el nombre creado en XAMPP.

Se configuró el resto de valores de conexión en `.env` para conectar el proyecto con MySQL.

### 7. Ejecución de migraciones base

Se ejecutaron las migraciones iniciales de Laravel para comprobar que la conexión con base de datos funciona correctamente:

```
php artisan migrate
```

Con estas migraciones se crea automáticamente el usuario administrador para el panel:

- email: `admin@admin.es`
- password: `admin1234*`
- role: `admin`

Importante: el login de Laravel usa la tabla `users`.  
La tabla `usuarios` del archivo `database/schema/erp_ciete_base.sql` pertenece al esquema ERP y no sustituye a `users` para autenticación web.

### 8. Comprobación del arranque del proyecto

Se comprobó que el proyecto arranca correctamente tanto en backend como en frontend:

```
php artisan serve
npm run dev
```
