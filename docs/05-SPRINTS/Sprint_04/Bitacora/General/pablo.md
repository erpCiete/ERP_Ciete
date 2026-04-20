# Bitacora GENERAL · Pablo

## General

### Objetivo de la semana

- Módulo de Pedidos: modelos, controladores, formularios MOEVE/REPSOL
- Módulo de Facturas: estructura base y lógica de doble factura REPSOL
- Coordinar tracks BACK, FRONT y cerrar arrastres de Sprint 03

## 2026-04-19 — Preparación pre-sprint

### Objetivo del dia

- Cerrar arrastres del Sprint 03 (panel admin, avisos, limpieza)
- Preparar infraestructura para Sprint 04

### Tareas realizadas

- Sistema de avisos editables para pantalla de inicio:
    - `Admin\NoticeController`: guardar/leer avisos bilingües (es/en) en JSON
    - Editor inline en Panel Admin (reemplaza tarjeta de contextos)
    - Welcome.jsx lee avisos desde backend con selección automática de idioma
    - Validación: max 5 items por categoría, max 300 chars, ambos idiomas obligatorios
- Ruta `POST /admin/notices` protegida por role:admin
- i18n: keys `adminDashboard.notices.*` en ambos idiomas
- Actualizado `SPRINT_04_PLAN.md` con campos reales de modelos Pedido/PedidoItem/Factura
- Version bump completado: v1.2.0 → v1.3.0
- Sprint badge actualizado: Sprint 01 → Sprint 03

### Archivos tocados

- `app/Http/Controllers/Admin/NoticeController.php` (NUEVO)
- `app/Http/Controllers/Admin/DashboardController.php` (editado)
- `resources/js/Pages/Admin/Dashboard.jsx` (editado)
- `resources/js/Pages/Welcome.jsx` (editado)
- `resources/js/i18n/locales/es.js` (editado)
- `resources/js/i18n/locales/en.js` (editado)
- `routes/web.php` (editado)
- `storage/app/notices.json` (NUEVO)

### Errores / bloqueos

| Hora | Error/Bloqueo              | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
| ---- | -------------------------- | ------------------------- | ------------- | ------------------------ |
| —    | Sin errores significativos | —                         | —             | —                        |

### Decisiones tomadas

- Avisos guardados en JSON (storage/app/notices.json) — sin tabla en BD, suficiente para el volumen esperado
- Cada aviso es bilingüe (es/en obligatorio) para soportar cambio de idioma sin pérdida
- Máximo 5 items por categoría para mantener la pantalla limpia
- Se eliminó la tarjeta de contextos del admin dashboard (sustituida por editor de avisos)

### Pendiente para mañana

- Empezar PedidoController CRUD (B04-01)
- Crear PedidoResource con filtrado de campos por contexto
- Coordinar front para Pedidos/Index.jsx

### Handoff

- Sprint 04 arranca con avisos editables ya funcionales y plan detallado listo

## 2026-04-20

### Objetivo del dia

-

### Tareas realizadas

-

### Archivos tocados

-

### Errores / bloqueos

| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
| ---- | ------------- | ------------------------- | ------------- | ------------------------ |
|      |               |                           |               |                          |

### Decisiones tomadas

-

### Pendiente para mañana

-

### Handoff

-

## 2026-04-23

### Objetivo del dia

-

### Tareas realizadas

-

### Archivos tocados

-

### Errores / bloqueos

| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
| ---- | ------------- | ------------------------- | ------------- | ------------------------ |
|      |               |                           |               |                          |

### Decisiones tomadas

-

### Pendiente para mañana

-

### Handoff

-

## 2026-04-24

### Objetivo del dia

-

### Tareas realizadas

-

### Archivos tocados

-

### Errores / bloqueos

| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
| ---- | ------------- | ------------------------- | ------------- | ------------------------ |
|      |               |                           |               |                          |

### Decisiones tomadas

-

### Pendiente para mañana

-

### Handoff

-
