# Bloque 6 — Roles, maestros y auditoría

## Resultado

- Bloque 6 abierto de forma controlada tras auditoría rápida de Bloque 5 sin fallo funcional bloqueante.
- No se reabren reglas de estados, líneas de pedido, exportación Moeve ni facturación funcional.
- Se corrigen huecos de permisos finos y de trazabilidad en cierre, y se refuerza el aviso operativo al desactivar maestros críticos.

## Auditoría previa Bloque 5

- `TrabajoStateService` seguía siendo el origen correcto para estado derivado, facturación y snapshot de cierre.
- No se detectó lógica de frontend inventando estados; `Trabajos` consume `estado`, `estado_facturacion` y `cierre_secundario` desde backend.
- Se detectó una duplicación pequeña y segura:
  - la noción de "trabajo terminado" estaba repetida entre `TrabajoStateService` y `ClosureDashboardService`.
- Se detectó un hueco real de trazabilidad:
  - el cierre secundario cambiaba `bloqueado_cierre` y `estado=finalizado` sin escribir en `audit_log`.
- Se detectó un hueco real de permisos finos:
  - `patchField` permitía por backend editar campos maestros legacy de estación desde `Trabajos` con solo `trabajos.editar`.

## Cambios aplicados

### Estados/cierre

- `TrabajoStateService` expone `isTrabajoFinished()` y `ClosureDashboardService` deja de mantener su propia regla duplicada.
- No cambian las reglas funcionales de derivación:
  - `en_curso`, `terminado`, `pendiente_facturar`, `facturado`, `finalizado`, `cancelado` se conservan.

### Auditoría

- `ClosureDashboardService` ahora audita:
  - cambios de `bloqueado_cierre` durante revisión de checklist;
  - paso a `finalizado` desde panel de cierre.
- Se reutiliza `AuditLogger`; no se crea sistema paralelo.

### Roles/permisos

- `TrabajoController@patchField` exige permisos finos adicionales cuando corresponde:
  - cambio manual de estado: `trabajos.marcar_terminado` o `trabajos.cambiar_estado` según caso;
  - edición de campos maestros legacy de estación: `estaciones.editar`;
  - reasignación de pedido a trabajo: `pedidos.editar`.
- `TrabajoController@update` también valida permiso fino si se intenta cambiar manualmente el estado desde ficha completa.

### Maestros

- `Contratos` muestra aviso explícito si se desactiva un contrato ya usado por:
  - trabajos;
  - facturas;
  - tarifarios;
  - sociedades asociadas.
- `Tarifarios` muestra aviso explícito si se desactiva un tarifario ya usado por:
  - líneas;
  - trabajos;
  - pedidos.
- Los mensajes de éxito backend también aclaran cuando la desactivación conserva histórico asociado.

## Deuda técnica detectada

- `TrabajoController@patchField` conserva ramas legacy no expuestas hoy en la UI para campos de estación y reasignación de pedido.
  - Ya quedan protegidas por permiso fino, pero siguen siendo una superficie técnica heredada.
- `ClosureDashboardController` no audita por sí mismo.
  - La trazabilidad ya queda cubierta desde `ClosureDashboardService`, que ahora es el punto efectivo de mutación.
- Catálogos `TipoDocumento` y `TipoTrabajo` siguen en modo lectura/soporte.
  - No existe todavía un módulo dedicado de mantenimiento con permisos propios; no se abre en este bloque.

## Archivos tocados

- `app/Services/TrabajoStateService.php`
- `app/Services/ClosureDashboardService.php`
- `app/Http/Controllers/Api/TrabajoController.php`
- `app/Http/Controllers/ContratoController.php`
- `app/Http/Controllers/TarifarioController.php`
- `resources/js/Pages/Contratos/Index.jsx`
- `resources/js/Pages/Tarifarios/Index.jsx`
- `tests/Feature/ClosureDashboardTest.php`
- `tests/Feature/TrabajoTest.php`

## Riesgos residuales

- No se ejecuta validación visual manual de navegador en esta sesión.
- Los avisos reforzados de maestros se han cerrado en `Contratos` y `Tarifarios`, que son los maestros más críticos del flujo principal.
- Empresas/estaciones ya usaban desactivación lógica y auditoría, pero su preaviso visual sigue siendo más genérico que en contratos/tarifarios.
