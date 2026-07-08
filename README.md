<div align="center">

# ERP CIETE

Plataforma interna para trabajos de ingeniería, pedidos, facturación, cierres y exportaciones operativas.

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![React](https://img.shields.io/badge/React-19-61DAFB?style=for-the-badge&logo=react&logoColor=111827)](https://react.dev/)
[![Inertia](https://img.shields.io/badge/Inertia.js-3-9553E9?style=for-the-badge)](https://inertiajs.com/)
[![Status](https://img.shields.io/badge/status-interno-lightgrey?style=for-the-badge)](#estado)

</div>

## Tabla de contenidos

- [Sobre el proyecto](#sobre-el-proyecto)
- [Módulos](#modulos)
- [Stack](#stack)
- [Inicio rápido](#inicio-rapido)
- [Documentación](#documentacion)
- [Seguridad](#seguridad)
- [Estado](#estado)

## Sobre el proyecto

ERP CIETE centraliza la operativa de Ciete Ingenieros S.A. para trabajos, pedidos, facturas, cierres y exportaciones específicas.

Repositorio:

- https://github.com/kampexiii/ERP_Ciete

## Modulos

- Gestión de trabajos.
- Pedidos y facturación.
- Cierres operativos.
- Exportaciones.
- Roles y permisos.
- Trazabilidad de acciones.

## Stack

| Área | Tecnología |
| --- | --- |
| Backend | Laravel 12 |
| Frontend | Inertia, React |
| UI | Tailwind CSS |
| Datos | MySQL/MariaDB |
| Build | Vite |

## Inicio rapido

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```

## Documentacion

La documentación de entrega vive en `docs/00_ENTREGA_FINAL/`.

## Seguridad

- Proyecto con contexto de cliente: revisar antes de hacerlo público.
- No subir `.env`, credenciales ni datos reales.
- GitHub ha avisado de vulnerabilidades Dependabot pendientes de revisión.

## Estado

Rama activa: `Desplegadav2`.
