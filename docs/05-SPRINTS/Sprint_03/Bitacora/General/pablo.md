# Bitacora GENERAL · Pablo

## General

### Objetivo de la semana

- Módulo de Trabajos (Obras) CRUD completo con parametrización MOEVE/REPSOL
- Infraestructura base de importación Excel/CSV
- Coordinar tracks BACK, FRONT y DOCS

## 2026-04-13

### Objetivo del dia

- Cerrar mejoras de seguridad y pulido de Sprint 02
- Planificar Sprint 03 completo
- Crear biblia de desarrollo

### Tareas realizadas

- Seguridad: rate limiting login mejorado (5 intentos/5 min, decay 300s)
- StatusController: filtrado role-based (admin 6 tarjetas, user 3 simplificadas)
- Status.jsx: renderizado condicional con prop isAdmin
- i18n fixes: eliminado "sol/luna", correos ejemplo a pablo@ciete.es
- Help.jsx: nota visibilidad admin/usuario en sección estado
- Manual ayuda: actualizado status section con "(solo admin)"
- Versión actualizada: v1.1.0 → v1.2.0
- Creada BIBLIA_DESARROLLO.md (guía completa para este y futuros sprints)
- Creado SPRINT_03_PLAN.md (plan detallado por tracks BACK/FRONT/DOCS)
- Actualizado Plan_Detallado_Sprints.md (inventario, módulos, stack)

### Archivos tocados

- app/Http/Controllers/StatusController.php (role-based + version bump)
- app/Http/Requests/Auth/LoginRequest.php (decay 300s)
- lang/es/auth.php, lang/en/auth.php (mensajes throttle)
- resources/js/Pages/Status.jsx (isAdmin prop)
- resources/js/Pages/Help.jsx (visibility info box)
- resources/js/i18n/locales/es.js, en.js (múltiples fixes)
- docs/BIBLIA_DESARROLLO.md (NUEVO)
- docs/05-SPRINTS/Sprint_03/SPRINT_03_PLAN.md (NUEVO)
- docs/05-SPRINTS/Plan_Detallado_Sprints.md (actualizado)

### Errores / bloqueos

| Hora | Error/Bloqueo              | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
| ---- | -------------------------- | ------------------------- | ------------- | ------------------------ |
| —    | Sin errores significativos | —                         | —             | —                        |

### Decisiones tomadas

- Version bump a v1.2.0 (SemVer minor: nuevas features seguridad + role-based status)
- Rate limit: 300s (5 min) es balance profesional entre seguridad y usabilidad
- Status page: usuarios ven 3 tarjetas (app versión, mail estado, mantenimiento) — suficiente para saber que el sistema funciona sin exponer infraestructura
- Importación Excel: usar phpoffice/phpspreadsheet directamente (más control que laravel-excel)
- Sprint 03 incluye inicio de importaciones (infraestructura + primer tipo) además de obras

### Pendiente para mañana

- Empezar TrabajoController CRUD
- Verificar que modelos de soporte (Contacto, Direccion, etc.) están completos
- Coordinar que FRONT empiece con Trabajos/Index.jsx

### Handoff

-

## 2026-04-15

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

## 2026-04-16

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

## 2026-04-17

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
