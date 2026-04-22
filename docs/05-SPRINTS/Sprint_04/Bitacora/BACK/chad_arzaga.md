# Bitacora BACK · Chad Arzaga

## General
### Objetivo de la semana
- 

## 2026-04-20
### Objetivo del dia
- Implementar los 4 archivos del CRUD API de Pedidos y comprobar su encaje con la base de datos real.

### Tareas realizadas
- Creación de `app/Http/Controllers/Api/PedidoController.php` para el CRUD de Pedidos.
- Implementación de `index`, `store`, `show`, `update` y `destroy` en el controlador.
- Carga de ítems anidados del pedido (`PedidoItem`) en las respuestas.
- Uso de contexto en la lógica del controlador para mantener coherencia con la estructura del proyecto.
- Creación de `app/Http/Requests/Api/StorePedidoRequest.php` con validaciones para alta de pedidos.
- Creación de `app/Http/Requests/Api/UpdatePedidoRequest.php` con validaciones para edición de pedidos.
- Validación de campos principales del pedido y de líneas anidadas (`items`).
- Creación de `app/Http/Resources/Api/PedidoResource.php` para normalizar la salida API del pedido.
- Comprobación del `PedidoResource` contra la base de datos real para asegurar que los campos encajan con `pedidos` y `pedidos_lineas`.

### Archivos tocados
- `app/Http/Controllers/Api/PedidoController.php`
- `app/Http/Requests/Api/StorePedidoRequest.php`
- `app/Http/Requests/Api/UpdatePedidoRequest.php`
- `app/Http/Resources/Api/PedidoResource.php`

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
|  | Los 4 archivos de Pedidos no existían en los fuentes | Alto | Se implementaron desde cero siguiendo la estructura real del módulo | Cerrado |
|  | Posible desajuste entre modelos antiguos y el esquema actual de `pedidos` / `pedidos_lineas` | Alto | Se adaptó la implementación a la base de datos real y se validó el resource contra SQL | Abierto |
|  | Riesgo con serialización de fechas si el modelo no tiene casts correctos | Medio | Se revisó el `PedidoResource` y se dejó identificado para ajuste si falla en ejecución | Abierto |

### Decisiones tomadas
- Implementar los 4 archivos tomando como referencia la base de datos real y no nombres heredados de una estructura antigua.
- Dejar los ítems del pedido anidados dentro del resource.
- Mantener la validación de líneas (`items`) dentro de los requests del pedido.

### Pendiente para mañana
- Revisar si `Pedido.php` y `PedidoItem.php` necesitan ajuste para encajar completamente con esta implementación.
- Verificar en ejecución real los casts de fechas y relaciones.

### Handoff
- Quedan creados los 4 archivos base del CRUD API de Pedidos.
- El `PedidoResource` ya se contrastó con la BBDD y sus campos encajan con las tablas reales.
- El siguiente punto a revisar es la alineación completa de modelos si aparece error al probarlo.

## 2026-04-21
### Objetivo del dia
- Implementar el CRUD API de Facturas (B04-02): factory, rutas API, rutas web y corrección del seeder.

### Tareas realizadas
- Revisión del ZIP del proyecto para identificar el estado real de los archivos de Facturas antes de realizar cambios.
- Confirmación de que `FacturaController.php`, `StoreFacturaRequest.php`, `UpdateFacturaRequest.php` y `FacturaResource.php` ya existían y estaban implementados correctamente por otro compañero.
- Creación de `database/factories/FacturaFactory.php` desde cero con estados (`pendiente`, `emitida`, `cobrada`) y variantes por contexto (`moeve()`, `repsol()`).
- Registro de las rutas API de Facturas en `routes/api.php` con separación de permisos `facturas.ver` y `facturas.gestionar`, siguiendo el mismo patrón aplicado en Pedidos.
- Registro de las rutas Inertia del módulo de Facturas en `routes/web.php` (`index`, `crear`, `editar`) bajo el middleware `permission:facturas.ver`.
- Detección y corrección del bloque duplicado de Facturas en `DatosBaseSeeder.php`: el bloque del Sprint 04 usaba columnas inexistentes (`id_proyecto`, `id_empresa`) en lugar de las correctas (`id_trabajo`, `id_empresa_cliente`). Se reescribió el bloque con los datos y columnas correctas alineadas con la migración real.

### Archivos tocados
- `database/factories/FacturaFactory.php` (nuevo)
- `routes/api.php`
- `routes/web.php`
- `database/seeders/DatosBaseSeeder.php`

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
|  | El bloque Sprint 04 del seeder usaba columnas `id_proyecto` e `id_empresa` que no existen en la tabla `facturas` | Alto | Se reescribió el bloque usando las columnas reales de la migración | Cerrado |

### Decisiones tomadas
- No tocar los 4 archivos principales (`Controller`, `Requests`, `Resource`) ya que estaban correctamente implementados y modificarlos sin necesidad podría generar conflictos con el trabajo del compañero.
- Aplicar el mismo patrón de separación de permisos de lectura/gestión que ya se utilizó en Pedidos, en lugar de un único `apiResource` con un solo middleware.
- Registrar las rutas web de Facturas aunque el frontend aún no exista, para no bloquear las tareas `F04-02` que dependen de esta tarea.

### Pendiente para mañana
- Verificar en ejecución real que las rutas API de Facturas responden correctamente con un usuario autenticado.
- Confirmar con el compañero responsable que el enum de `estado` en los Requests coincide con el de la migración.

### Handoff
- Los 4 archivos principales del CRUD API de Facturas ya estaban implementados y no se han modificado.
- Se han añadido factory, rutas API y rutas web, completando todos los entregables de `B04-02` excepto la corrección del enum de `estado` en los Requests, que queda pendiente del compañero responsable.
- El seeder queda limpio y alineado con la estructura real de la base de datos.

## 2026-04-22
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

## 2026-04-23
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

## 2026-04-24
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