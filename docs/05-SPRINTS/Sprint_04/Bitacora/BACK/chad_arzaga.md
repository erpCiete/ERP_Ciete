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