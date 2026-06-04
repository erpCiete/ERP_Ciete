# Bitacora BACK · Alex Puma

## General
### Objetivo de la semana
- 

## 2026-04-13
### Objetivo del dia
- 

### Tareas realizadas
- 

### Archivos tocados
- 

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
|  |  |  |  |  |

### Decisiones tomadas
- 

### Pendiente para mañana
- 

### Handoff
- 

## 2026-04-14
### Objetivo del dia
- Blindar la entrada de datos a la API asegurando que las validaciones cambian dinámicamente según la operadora del usuario autenticado (Moeve vs Repsol).

### Tareas realizadas
- [B03-01] Implementación de `StoreTrabajoRequest.php` y `UpdateTrabajoRequest.php`.
- Inyección de lógica condicional en el método `rules()` utilizando `Rule::requiredIf()` leyendo el `$this->user()->id_contexto`.
- Configuración del método `prepareForValidation()` para la sanitización de inputs (trim) antes de su evaluación.

### Archivos tocados
- `app/Http/Requests/StoreTrabajoRequest.php`
- `app/Http/Requests/UpdateTrabajoRequest.php`

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| 11:30 | Dificultad para aplicar reglas de unicidad complejas (un número de trabajo debe ser único, pero solo dentro de su propio contexto). | Alto | Añadir un callback a la regla `unique` de Laravel para forzar un `where('id_contexto', ...)` en la comprobación. | Cerrado |

### Decisiones tomadas
- Se decidió centralizar todos los mensajes de error de estos FormRequests utilizando i18n para facilitar la traducción futura que requiere el Frontend.

### Pendiente para mañana
- Inyectar estos FormRequests en el nuevo `TrabajoController` y armar el CRUD completo con pruebas automáticas.

### Handoff
- Notificado a Edu (Tech Lead) que la barrera de validación condicional ya está funcional.

## 2026-04-15
### Objetivo del dia
- Desplegar la lógica de negocio en el Controlador de Trabajos y someter la protección de contexto a pruebas de estrés mediante PHPUnit.

### Tareas realizadas
- [B03-02] Construcción de `TrabajoController.php` (métodos index, create, store, edit, update, destroy).
- Integración de validación de seguridad de negocio: Bloqueo explícito de acciones `update` y `destroy` si la obra tiene el campo `cerrado = true` (excepción hecha para administradores).
- Implementación de Eager Loading (`with`) preventivo para evitar problemas N+1 en las relaciones `empresa`, `estacion`, `contrato`, `tipoDocumento`.
- [B03-08] Desarrollo inicial de la suite de pruebas `TrabajoTest.php`.

### Archivos tocados
- `app/Http/Controllers/TrabajoController.php`
- `tests/Feature/TrabajoTest.php`

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| 10:15 | Asegurar que Inertia recibe la variable de entorno de contexto para renderizar la tabla dinámica. | Medio | Inyectar `$request->user()->getAccessibleContextIds()` directamente en las props de `Inertia::render()`. | Cerrado |
| 13:00 | Los tests de `edit` daban un 404 en lugar de 403 al simular un usuario de Repsol accediendo a un ID de Moeve. | Bajo | Confirmado que esto es el comportamiento ESPERADO del trait `HasContext` actuando a nivel de Global Scope en Eloquent. Validado como correcto. | Cerrado |

### Decisiones tomadas
- Delegar completamente el filtrado de registros de la base de datos al Global Scope `ContextScope`. El controlador queda totalmente limpio sin ninguna cláusula `where('id_contexto', ...)` manual.

### Pendiente para mañana
- Dar apoyo en infraestructura base o resolver incidencias del Frontend (Daniel) si el contrato JSON generado por el Controller causa roturas.

### Handoff
- Controller finalizado, los tests pasan al 100%. Eduardo puede proceder a proteger las rutas y conectar el Front.
## 2026-04-16
### Objetivo del dia
- 

### Tareas realizadas
- 

### Archivos tocados
- 

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
|  |  |  |  |  |

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
|---|---|---|---|---|
|  |  |  |  |  |

### Decisiones tomadas
- 

### Pendiente para mañana
- 

### Handoff
- 
