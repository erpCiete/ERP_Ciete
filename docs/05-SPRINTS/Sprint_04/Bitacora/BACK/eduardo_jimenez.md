# Bitacora BACK · Eduardo Jimenez

## General
### Objetivo de la semana
- Implementar los módulos de Pedidos y Facturas con aislamiento de contexto estricto (MOEVE/REPSOL) e iniciar la importación v1.

## 2026-04-20
### Objetivo del dia
- Iniciar la Tarea B04-01 (API CRUD de Pedidos).
- Configurar la estructura de rutas protegidas y volcar la data demo inicial en BBDD para no bloquear al Front.

### Tareas realizadas
- Refactorización del archivo de rutas de la API para separar permisos de lectura (`pedidos.ver`) y escritura (`pedidos.gestionar`) en el nuevo recurso de pedidos.
- Actualización de seeders para inyectar 4 pedidos demo (2 MOEVE, 2 REPSOL), sus líneas anidadas (`pedido_items`) y 2 facturas condicionales. 

### Archivos tocados
- `routes/api.php`
- `database/seeders/DatosBaseSeeder.php`

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| 10:15 | Permiso genérico `pedidos.ver` en todo el `apiResource` permitía escritura no autorizada. | Alto | Segregación de rutas `match`, `post` y `delete` bajo el middleware `pedidos.gestionar`. | Cerrado |
| 11:30 | Archivo de controlador creado con extensión `.Php`, lo cual provocaría "Class not found" en el servidor Linux de producción. | Alto | Renombrado inmediato a `PedidoController.php`. | Cerrado |

### Decisiones tomadas
- Se utilizó lógica `upsert` en el `DatosBaseSeeder.php` basando las llaves en los IDs fijos extraídos del dump de producción (`abaco_ciete.sql`) para mantener coherencia y evitar colisiones al ejecutar `migrate:fresh --seed`.
- Se aplicó RBAC explícito a nivel de enrutamiento API antes de inyectar las políticas en los FormRequests.

### Pendiente para mañana
- Añadir en PermisosSeeder.php nuevos permisos: pedidos.ver, pedidos.gestionar, facturas.ver, facturas.gestionar
- Preparar la inyección de la lógica condicional en la Tarea B04-02 (CRUD Facturas).

### Handoff
- Las rutas `/api/v1/pedidos` ya están disponibles en backend. Frontend ya puede ir preparando las llamadas Axios u hooks requeridos. Carga de datos base disponible tras un `migrate:fresh --seed`.

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
