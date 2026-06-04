# Bitacora BACK · Eduardo Jimenez

## General
### Objetivo de la semana
- Configurar el enrutamiento base, gestionar el repositorio (Pull Requests, merges, ramas, tablero de proyecto, resolución de conflictos) y garantizar la calidad del código mediante la revisión y refactorización del trabajo del equipo, culminando con la implementación del sistema de importación masiva de Trabajos.

## 2026-04-13
### Objetivo del dia
- Implementar rutas de API y web para módulos de Trabajos e Importaciones y establecer la gestión del repositorio.

### Tareas realizadas
- Gestión continua del repositorio en GitHub: control de Pull Requests, merges, gestión de ramas, actualización del tablero del proyecto y corrección de errores.
- [Tarea B03-04] Configurar endpoints de API v1 para autenticación (login, logout, me)
- [Tarea B03-04] Implementar rutas CRUD de clientes con middleware de permisos
- [Tarea B03-04] Implementar rutas CRUD de estaciones con middleware de permisos
- [Tarea B03-04] Crear endpoints API para módulo de Trabajos (apiResource) con protección de permisos
- [Tarea B03-04] Configurar rutas web para módulo de Trabajos con acceso por permisos específicos (ver, crear, editar, eliminar)
- [Tarea B03-04] Implementar rutas web para módulo de Importaciones (subir, procesar, preview, confirmar)
- [Tarea B03-04] Integrar middleware de autenticación y autorización en todas las rutas protegidas

### Archivos tocados
- `/routes/api.php` - Rutas de API v1 completas
- `/routes/web.php` - Rutas web con módulos de Trabajos e Importaciones

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| - | Sin bloqueos reportados | - | - | - |

### Decisiones tomadas
- Usar apiResource para endpoint de Trabajos (sigue convención RESTful)
- Separar permisos granulares para Trabajos (ver como lectura, crear/editar/eliminar como acciones específicas)
- Mantener consistencia entre rutas API y web en estructura y nomenclatura
- Implementar middleware de permisos en nivel de grupo de rutas para mejor mantenibilidad

### Pendiente para mañana
- Revisión de código de la tarea B03-03.
- Gestión continua del repositorio.

### Handoff
- Estructura de rutas lista para implementar lógica en controllers
- API siguiendo patrón v1 con namespace separado


## 2026-04-14
### Objetivo del dia
- Asegurar la calidad y correcta arquitectura del serializador de datos (TrabajoResource).

### Tareas realizadas
- Gestión continua del repositorio en GitHub: control de Pull Requests, merges, gestión de ramas, actualización del tablero del proyecto y corrección de errores.
- Revisión y refactorización completa del código de la tarea B03-03 (realizada por Carlos Puchol): Crear TrabajoResource para serializar campos.
- Implementación de reglas de negocio en la capa de Resource para asegurar el aislamiento de contexto (Moeve vs Repsol).

### Archivos tocados
- `app/Http/Resources/Api/TrabajoResource.php`
- `app/Http/Resources/Api/ContratoResource.php`
- `app/Http/Resources/Api/TipoDocumentoResource.php`
- `app/Http/Resources/Api/TipoTrabajoResource.php`

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| - | Rutas de importación incorrectas (Namespace). | Medio | Corrección de namespaces de `App\Http\Resources` a `App\Http\Resources\Api`. | Cerrado |
| - | Error 500 por MissingValue en `EstacionResource`. | Alto | Refactorización para usar `$this->relationLoaded()` y evitar el pánico de PHP. | Cerrado |

### Decisiones tomadas
- El `TrabajoResource` actuará como un "firewall" de contexto para evitar enviar datos irrelevantes al frontend.

### Pendiente para mañana
- Revisión de la lógica de controladores (B03-02).
- Mantenimiento del repositorio.

### Handoff
- Resources listos y seguros. El frontend recibirá JSON estandarizado y filtrado.


## 2026-04-15
### Objetivo del dia
- Consolidar la lógica de negocio y el CRUD en el controlador principal.

### Tareas realizadas
- Gestión continua del repositorio en GitHub: control de Pull Requests, merges, gestión de ramas, actualización del tablero del proyecto y corrección de errores.
- Revisión y refactorización completa del código de la tarea B03-02 (realizada por Alex Puma): Desarrollar TrabajoController con CRUD completo.

### Archivos tocados
- `app/Http/Controllers/Api/TrabajoController.php`

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| - | Error de Namespace en `web.php` al llamar a `TrabajoController`. | Alto | Actualizar el `use` en `web.php` para apuntar a la carpeta `Api/`. | Cerrado |

### Decisiones tomadas
- Mantener el controlador centrado en peticiones de Inertia (`Inertia::render`), delegando la lógica de serialización a los Resources.

### Pendiente para mañana
- Iniciar el desarrollo del servicio de importación Excel (B03-05).

### Handoff
- CRUD de Trabajos completado y conectado con Inertia.js.


## 2026-04-16
### Objetivo del dia
- Desarrollar la base del servicio de lectura de archivos Excel para la importación masiva.

### Tareas realizadas
- Gestión continua del repositorio en GitHub: control de Pull Requests, merges, gestión de ramas, actualización del tablero del proyecto y corrección de errores.
- [Tarea B03-05] Desarrollar ExcelParserService base.
- Implementación de filtro de lectura por *chunks* (trozos) usando PhpSpreadsheet.
- Desarrollo de lógica para parseo nativo de fechas de Excel (`Date::excelToDateTimeObject`).

### Archivos tocados
- `app/Services/ExcelParserService.php`

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| - | Posible saturación de RAM con Excels grandes. | Alto | Implementación de `ChunkReadFilter` y `$spreadsheet->disconnectWorksheets()` para limpiar la memoria periódicamente. | Cerrado |

### Decisiones tomadas
- El `ExcelParserService` se limitará a extraer y limpiar datos (responsabilidad única), sin hacer consultas a la base de datos (se delega al controlador).
- Leer solo datos sin estilos (`setReadDataOnly(true)`) para optimizar rendimiento.

### Pendiente para mañana
- Desarrollar el controlador que orquestará la subida y confirmación (B03-06).

### Handoff
- Servicio base del Parser finalizado. Listo para integrarse con la lógica de negocio.


## 2026-04-17
### Objetivo del dia
- Finalizar el flujo completo de importación masiva.

### Tareas realizadas
- Gestión continua del repositorio en GitHub: control de Pull Requests, merges, gestión de ramas, actualización del tablero del proyecto y corrección de errores.
- [Tarea B03-06] Desarrollar ImportacionController y validación.
- Creación de flujo en 3 pasos: Subida (Staging) -> Preview -> Confirmación (Transaccional).
- Implementación de validación estricta de archivos con FormRequest.

### Archivos tocados
- `app/Http/Controllers/ImportacionController.php`
- `app/Http/Requests/StoreImportacionRequest.php`

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| - | Inconsistencia entre validación de Request y BD. | Medio | Modificación de `StoreImportacionRequest` para aceptar solo xlsx y csv, ajustando los mensajes de error. | Cerrado |

### Decisiones tomadas
- Utilizar una tabla intermedia de *staging* (`importaciones` e `importacion_filas`) para almacenar los datos temporales en formato JSON en lugar de usar sesiones o caché.
- Utilizar transacciones de base de datos (`DB::beginTransaction()`) para garantizar la atomicidad en la subida y confirmación.

### Pendiente para mañana
- Finalizar sprint y preparar release v1.3.

### Handoff
- Módulo de importación completado y funcional.