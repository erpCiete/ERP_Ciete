# Bitacora BACK · Alex Puma

## General
### Objetivo de la semana
- 

## 2026-04-20
### Objetivo del dia
Desarrollar el CRUD backend para el módulo de Pedidos, exponiendo los endpoints vía API REST y configurando las rutas web para Inertia.

### Tareas realizadas
Creación de la Factory para el modelo Pedido para facilitar la generación de datos de prueba.

Configuración y protección de rutas web (Inertia) para las vistas del CRUD de Pedidos (index, create, edit).

Registro del recurso API (apiResource) en las rutas protegidas para conectar con el PedidoController.

Análisis preventivo del seeder de la base de datos para evitar colisiones de datos.

### Archivos tocados
database/factories/PedidoFactory.php (Nuevo)

routes/web.php (Modificado - HOT FILE)

routes/api.php (Modificado - HOT FILE)

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| 12:40 | Riesgo de sobrescritura/corrupción de datos demo en el DatosBaseSeeder (HOT FILE). |Medio| Se analizó el código existente y se detectó que ya había upserts deterministas de pedidos vinculados a trabajos. Se canceló la inyección de datos aleatorios. | Cerrado |

### Decisiones tomadas
No modificar DatosBaseSeeder.php: Se decidió mantener el seeder intacto para preservar la integridad de los datos de demostración preexistentes (los cuales ya incluyen pedidos e ítems enlazados correctamente a trabajos, facturas y cobros), evitando ensuciar la base de datos con registros aleatorios de la Factory.

### Pendiente para mañana
Iniciar el desarrollo del frontend (Vistas Inertia para el CRUD de Pedidos) consumiendo los endpoints y rutas creadas hoy. (Correspondiente a las tareas bloqueadas, ej: F04-01).

Realizar pruebas de integración para asegurar que la carga anidada (PedidoItem) funciona correctamente en los nuevos endpoints.

### Handoff
El backend del módulo de Pedidos está funcional y las rutas están expuestas.

Las rutas requieren el permiso pedidos.ver.

Aviso para QA/Frontend: No es necesario ejecutar un nuevo migrate:fresh --seed con fábricas nuevas; el seeder actual ya levanta 4 pedidos de demostración perfectamente relacionados para empezar a probar las vistas desde el primer minuto.

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
