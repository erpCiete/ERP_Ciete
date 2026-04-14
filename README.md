<p align="center">
  <img src="public/favicon.svg" alt="ERP Ciete Logo" width="108">
</p>

<h1 align="center">ERP Ciete - Plataforma de gestion para ingenieria v1.2.0</h1>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4">
  <img src="https://img.shields.io/badge/Inertia.js-2-9553E9?style=for-the-badge" alt="Inertia.js 2">
  <img src="https://img.shields.io/badge/React-18-61DAFB?style=for-the-badge&logo=react&logoColor=111827" alt="React 18">
  <img src="https://img.shields.io/badge/TailwindCSS-3-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 3">
  <img src="https://img.shields.io/badge/Vite-6-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite 6">
</p>

ERP Ciete es una plataforma web orientada a la gestion operativa de empresas de ingenieria.  
El objetivo del producto es centralizar procesos internos, mejorar trazabilidad y reducir friccion entre operacion tecnica y administracion.

Este repositorio contiene el estado funcional del MVP (Sprint 1), listo para evolucionar por modulos.

## Resumen ejecutivo

- Producto: ERP web interno para gestion empresarial.
- Sector objetivo: ingenieria, proyectos tecnicos y servicios profesionales.
- Enfoque: control de acceso por roles/permisos, experiencia de uso moderna y base tecnica escalable.
- Estado actual: Sprint 1 completado con autenticacion, paneles operativos y base de permisos.

## Capacidades entregadas (MVP Sprint 1)

- Autenticacion de usuarios:
    - Inicio y cierre de sesion.
    - Recuperacion y restablecimiento de password.
- Control de acceso:
    - Middleware por rol (`role:admin`, `role:cierre`).
    - Middleware por permiso (`permission:trabajos.ver`).
- Espacios de trabajo:
    - Dashboard general de usuario autenticado.
    - Dashboard administrativo (`/admin`) para perfil administrador.
    - Panel de cierre (`/cierre`) para control de trabajos cerrados.
- Perfil de usuario:
    - Edicion de perfil.
    - Cambio de password.
    - Seleccion de avatar corporativo.
- Experiencia de usuario:
    - Interfaz bilingue (ES/EN).
    - Navegacion responsive para desktop y mobile.

## Arquitectura y tecnologia

- Backend: Laravel 12 (PHP 8.4+)
- Frontend: Inertia.js 2 + React 18
- UI: Tailwind CSS 3 + Vite 6
- Datos: SQLite en local (soporte para MySQL en entorno productivo)
- Modelo de usuarios: tabla `usuarios` con relacion a roles y permisos

## Valor para negocio

- Centralizacion de procesos en una sola plataforma.
- Reduccion de tareas manuales y dependencias de hojas dispersas.
- Base preparada para escalar por areas: proyectos, clientes, facturacion, reportes y operacion.
- Menor riesgo operativo gracias a permisos por funcionalidad.

## Seguridad y gobierno de acceso

- Sesiones autenticadas y gestion de credenciales bajo estandares Laravel.
- Passwords almacenados con hash seguro.
- Rutas protegidas por middleware de autenticacion, rol y permiso.
- Separacion clara entre experiencia de usuario estandar y administrativa.

## Puesta en marcha local

### Requisitos

- PHP 8.4 o superior
- Composer 2
- Node.js 18 o superior
- npm 10 o superior

### Instalacion

```bash
git clone https://github.com/erpCiete/ERP_Ciete.git
cd ERP_Ciete
composer install
npm install
cp .env.example .env
php artisan key:generate

```

comandos utiles:

```bash
php artisan config:clear
php artisan cache:clear
php artisan migrate:fresh --seed
php artisan optimize:clear

```

Si usas SQLite en local:

```bash
touch database/database.sqlite
php artisan migrate
```

## Ejecucion en desarrollo

```bash
composer run dev
```

Alternativa en terminales separadas:

```bash
php artisan serve
npm run dev
```

## Calidad tecnica

```bash
php artisan test
./vendor/bin/pint
```

## Estructura de alto nivel

- `app/` logica de negocio y controladores
- `routes/` rutas web y autenticacion
- `resources/js/` interfaz React (Inertia)
- `database/` migraciones y seeders
- `docs/` memoria tecnica y entregables por sprint

## Roadmap recomendado (post Sprint 1)

- Consolidacion de modulos de negocio (proyectos, clientes, documentos y reportes).
- Auditoria de actividad y trazabilidad por usuario.
- Integracion con procesos de aprobacion y flujos internos.
- Hardening de despliegue productivo y observabilidad.

## Contacto tecnico

ERP Ciete es desarrollado por ABACO para Ciete Ingenieros.  
Para continuidad funcional, revisar la documentacion en `docs/` y la memoria por sprint.
